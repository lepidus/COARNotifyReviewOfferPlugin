<?php

namespace APP\plugins\generic\coarNotifyReviewOffer\controllers\grid;

use PKP\controllers\grid\GridCellProvider;
use PKP\controllers\grid\GridHandler;
use APP\core\Application;
use APP\notification\NotificationManager;
use PKP\linkAction\request\AjaxAction;
use PKP\linkAction\LinkAction;

class CoarNotifyReviewOfferGridCellProvider extends GridCellProvider {

    //
    // Template methods from GridCellProvider
    //

    var $_submissionId;

    function setSubmissionId($submissionId) {
        $this->_submissionId = $submissionId;
    }

    function getSubmissionId() {
        return $this->_submissionId;
    }

    /**
     * Extracts variables for a given column from a data element
     * so that they may be assigned to template before rendering.
     *
     * @copydoc GridCellProvider::getTemplateVarsFromRowColumn()
     */
    function getTemplateVarsFromRowColumn($row, $column) {
        $item = $row->getData();
        switch ($column->getId()) {
            case 'reviewService':
                return array('label' => $item['serviceUrl']);
            case 'sendReviewOnPublication':
                return array('selected' => $item['isSelected']);
            default:
                break;
        }
        return parent::getTemplateVarsFromRowColumn($row, $column);
    }

    function notification($type, $message) {
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            Application::get()->getRequest()->getUser()->getId(),
            $type,
            ['contents' => __($message)]
        );
    }

    /**
     * Get cell actions associated with this row/column combination
     *
     * @copydoc GridCellProvider::getCellActions()
     */
    function getCellActions($request, $row, $column, $position = GridHandler::GRID_ACTION_POSITION_DEFAULT) {
        $reviewService = $row->getData();
        $columnId = $column->getId();
        $router = $request->getRouter();
        $operation = $reviewService['isSelected'] ? 'unassignService' : 'assignService';

        $actionArgs = [
            "serviceUrl" => $reviewService,
            "submissionId" => $this->getSubmissionId()
        ];

        $actionUrl = $router->url($request, null, null, $operation, null, $actionArgs);

        $actionRequest = new AjaxAction($actionUrl);
        switch ($columnId) {
            case 'sendReviewOnPublication':
                return [
                    new LinkAction(
                        $operation,
                        $actionRequest,
                        __("plugins.generic.coarNotifyReviewOffer.reviewOfferPreferencesToggle"),
                        null
                    )
                ];
        }

        return parent::getCellActions($request, $row, $column, $position);
    }
}
