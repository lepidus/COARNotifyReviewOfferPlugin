<?php

namespace APP\plugins\generic\coarNotifyReviewOffer\controllers\grid;

use PKP\controllers\grid\GridHandler;
use PKP\controllers\grid\GridColumn;
use APP\core\Application;
use PKP\security\Role;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\db\DAORegistry;
use PKP\db\DAO;
use APP\notification\NotificationManager;
use APP\notification\Notification;
use PKP\core\JSONMessage;
use APP\plugins\generic\coarNotifyReviewOffer\controllers\grid\CoarNotifyReviewOfferGridRow;
use APP\plugins\generic\coarNotifyReviewOffer\controllers\grid\CoarNotifyReviewOfferGridCellProvider;

class CoarReviewOfferGridHandler extends GridHandler {
    private $plugin;

    /** @var boolean */
    var $_readOnly;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT, Role::ROLE_ID_AUTHOR],
            [
                'fetchGrid',
                'fetchRow',
                'assignService',
                'unassignService',
            ]
        );
    }

    /**
     * Set the CoarNotifyReviewOfferPlugin plugin.
     * @param $plugin CoarNotifyReviewOfferPlugin
     */
    public function setPlugin($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Get the submission associated with this grid.
     * @return Submission
     */
    function getSubmission() {
        return $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
    }

    /**
     * Get whether or not this grid should be 'read only'
     * @return boolean
     */
    function getReadOnly() {
        return $this->_readOnly;
    }

    /**
     * Set the boolean for 'read only' status
     * @param boolean
     */
    function setReadOnly($readOnly) {
        $this->_readOnly = $readOnly;
    }

    /**
     * @copydoc PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments) {
        $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    function getSlashlessString($input): string {
        return str_replace('/', '-', $input);
    }

    /**
     * @copydoc Gridhandler::initialize()
     */
    public function initialize($request, $args = null) {
        parent::initialize($request, $args);

        $submissionId = $this->getSubmission()->getId();

        $this->setTitle('plugins.generic.coarNotifyReviewOffer.preferences');
        $this->setEmptyRowText('plugins.generic.coarNotifyReviewOffer.noServices');

        // Columns
        $cellProvider = new CoarNotifyReviewOfferGridCellProvider();
        $cellProvider->setSubmissionId($submissionId);

        $this->addColumn(new GridColumn(
            'reviewService',
            'plugins.generic.coarNotifyReviewOffer.reviewService',
            null,
            'controllers/grid/gridCell.tpl',
            $cellProvider
        ));

        $this->addColumn(new GridColumn(
            'sendReviewOnPublication',
            'plugins.generic.coarNotifyReviewOffer.sendReviewOffer',
            null,
            'controllers/grid/common/cell/selectStatusCell.tpl',
            $cellProvider
        ));
    }

    protected function loadData($request, $filter)
    {
        $submission = $this->getSubmission();
        $reviewOfferPreferenceDao = DAORegistry::getDAO('ReviewOfferPreferenceDAO');

        $currentlySelectedReviewOfferServices = array_map(function($prefResult) {
            return $prefResult->getData('serviceUrl');
        }, $reviewOfferPreferenceDao->getBySubmissionId($submission->getId()));

        $reviewServiceList = $this->plugin->getReviewServiceList();

        foreach ($reviewServiceList as $serviceUrl => $inboxUrl) {
            $isSelected = in_array($serviceUrl, $currentlySelectedReviewOfferServices);

            $gridData[$this->getSlashlessString($serviceUrl)] = [
                'serviceUrl' =>  $serviceUrl,
                'isSelected' => $isSelected,
            ];
        }

        return $gridData;
    }

    //
    // Overridden methods from GridHandler
    //
    /**
     * @copydoc Gridhandler::getRowInstance()
     */
    function getRowInstance() {
        return new CoarNotifyReviewOfferGridRow($this->getReadOnly());
    }

    /**
     * @copydoc GridHandler::getJSHandler()
     */
    public function getJSHandler() {
        return '$.pkp.plugins.generic.coarNotifyReviewOffer.CoarReviewOfferGridHandler';
    }

    /**
     * @param $user User
     * @return boolean
     */
    function canAdminister($user) {
        return true;
    }

    function sendNotification($type, $params) {
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            Application::get()->getRequest()->getUser()->getId(),
            $type,
            $params,
        );
    }

    /**
     * Assign service from review offer preferences.
     * @param $args array
     * @param $request PKPRequest
     */
    function assignService($args, $request) {
        if (!$request->checkCSRF()) return new JSONMessage(false);

        $submission = $this->getSubmission();
        $submissionId = $submission->getId();
        $serviceUrl = $args['serviceUrl'][0];

        $reviewOfferPreferenceDao = DAORegistry::getDAO('ReviewOfferPreferenceDAO');
        $reviewOfferPreference = $reviewOfferPreferenceDao->newDataObject();
        $reviewOfferPreference->setSubmissionId($submissionId);
        $reviewOfferPreference->setServiceUrl($serviceUrl);
        $reviewOfferPreference->setIsSent(false);

        $reviewOfferPreferenceDao->insertObject($reviewOfferPreference);

        $this->sendNotification(
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('plugins.generic.coarNotifyReviewOffer.reviewOfferPreferencesUpdated')],
        );

        $json = DAO::getDataChangedEvent($submissionId);
        $json->setGlobalEvent('plugin:coarNotifyReviewOffer:serviceAssigned', ['submissionId' => $submissionId]);
        return $json;
    }

    /**
     * Unassign service from review offer preferences.
     * @param $args array
     * @param $request PKPRequest
     */
    function unassignService($args, $request) {
        if (!$request->checkCSRF()) return new JSONMessage(false);
        $submission = $this->getSubmission();
        $submissionId = $submission->getId();
        $serviceUrl = $args['serviceUrl'][0];

        $reviewOfferPreferenceDao = DAORegistry::getDAO('ReviewOfferPreferenceDAO');
        $reviewOfferPreferenceDao->deleteByServiceUrlAndSubmissionId($serviceUrl, $submissionId);

        $this->sendNotification(
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('plugins.generic.coarNotifyReviewOffer.reviewOfferPreferencesUpdated')],
        );
        $json = DAO::getDataChangedEvent($submissionId);
        $json->setGlobalEvent('plugin:coarNotifyReviewOffer:serviceUnassigned', ['submissionId' => $submissionId]);
        return $json;
    }
}
