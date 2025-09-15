<?php

namespace APP\plugins\generic\coarNotifyReviewOffer;

use PKP\form\Form;
use APP\template\TemplateManager;
use APP\plugins\generic\coarNotifyReviewOffer\CoarNotifyReviewOfferPlugin;

class CoarNotifyReviewOfferSettingsForm extends Form {

    /** @var int Associated context ID */
    private $contextId;

    /** @var CoarNotifyReviewOfferPlugin Registration notification plugin */
    private $plugin;

    /**
     * Constructor
     * @param $plugin CoarNotifyReviewOfferPlugin Registration notification plugin
     * @param $contextId int Context ID
     */
    public function __construct(CoarNotifyReviewOfferPlugin $plugin, $contextId) {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData() {
        $originName = $this->plugin->getSetting($this->contextId, 'originName');
        $this->setData('originName', $originName);
        $originHomeUrl = $this->plugin->getSetting($this->contextId, 'originHomeUrl');
        $this->setData('originHomeUrl', $originHomeUrl);
        $originInboxUrl = $this->plugin->getSetting($this->contextId, 'originInboxUrl');
        $this->setData('originInboxUrl', $originInboxUrl);

        $reviewServiceList = $this->plugin->getSetting($this->contextId, 'reviewServiceList');
        $this->setData('homeUrl', is_array($reviewServiceList) ? array_keys($reviewServiceList) : []);
        $this->setData('inboxUrl', is_array($reviewServiceList) ? array_values($reviewServiceList) : []);

        parent::initData();
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(['homeUrl', 'inboxUrl']);
        $homeUrls = $this->getData('homeUrl');
        $inboxUrls = $this->getData('inboxUrl');
        foreach($inboxUrls as $i => $inboxUrl) {
            //clean empty entries
            if(empty($inboxUrl) && empty($homeUrls[$i])){
                unset($inboxUrls[$i]);
                unset($homeUrls[$i]);
            }
        }
        $this->setData('homeUrl', array_values($homeUrls));
        $this->setData('inboxUrl', array_values($inboxUrls));

        $this->readUserVars(['originName']);
        $originName = $this->getData('originName');
        $this->setData('originName', $originName);

        $this->readUserVars(['originHomeUrl']);
        $originHomeUrl = $this->getData('originHomeUrl');
        $this->setData('originHomeUrl', $originHomeUrl);

        $this->readUserVars(['originInboxUrl']);
        $originInboxUrl = $this->getData('originInboxUrl');
        $this->setData('originInboxUrl', $originInboxUrl);

        parent::readInputData();
    }

    /**
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false) {
        $templateManager = TemplateManager::getManager($request);
        $templateManager->assign('pluginName', $this->plugin->getName());
        $templateManager->addJavaScript(
            'CoarNotifyReviewOfferSettingsFormHandler',
            $request->getBaseUrl() . '/' . $this->plugin->getPluginPath() . '/js/CoarNotifyReviewOfferSettingsFormHandler.js',
            [
                'priority' => TemplateManager::STYLE_SEQUENCE_CORE,
                'contexts' => 'CoarNotifyReviewOfferSettingsForm'
            ]
        );
        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs) {
        $this->plugin->updateSetting($this->contextId, 'reviewServiceList', array_combine($this->getData('homeUrl'), $this->getData('inboxUrl')), 'object');

        $this->plugin->updateSetting($this->contextId, 'originName', $this->getData('originName'), 'string');
        $this->plugin->updateSetting($this->contextId, 'originHomeUrl', $this->getData('originHomeUrl'), 'string');
        $this->plugin->updateSetting($this->contextId, 'originInboxUrl', $this->getData('originInboxUrl'), 'string');
        return parent::execute(...$functionArgs);
    }
}