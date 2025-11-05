<?php
/**
 * @file plugins/generic/coarNotifyReviewOffer/controllers/CoarNotifyProxyHandler.php
 *
 * Copyright (c) --
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class CoarNotifyProxyHandler
 * @ingroup plugins_generic_coarNotifyReviewOffer
 * @brief Handler class for COAR Notify proxy requests to avoid CORS issues.
 */

namespace APP\plugins\generic\coarNotifyReviewOffer\controllers;

use APP\handler\Handler;
use PKP\core\JSONMessage;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\Role;
use APP\core\Application;

class CoarNotifyProxyHandler extends Handler {
    private $plugin;

    public function __construct() {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT, Role::ROLE_ID_AUTHOR],
            ['sendNotification']
        );
    }

    public function authorize($request, &$args, $roleAssignments) {
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    public function setPlugin($plugin) {
        $this->plugin = $plugin;
    }

    public function sendNotification($args, $request) {
        if (!$request->checkCSRF()) return new JSONMessage(false);
        
        return;
    }
}

?>