<?php
/**
 * @file classes/security/authorization/internal/WorkflowStageRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowStageRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the given workflow stage is valid.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class WorkflowStageRequiredPolicy extends AuthorizationPolicy {

	/** @var integer */
	var $_stageId;

	/**
	 * Constructor
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function WorkflowStageRequiredPolicy($stageId) {
		parent::AuthorizationPolicy();
		$this->_stageId = $stageId;
	}


	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Check the stage id.
		if ($this->_stageId < 1 || $this->_stageId > 5) return AUTHORIZATION_DENY;

		// Save the workflow stage to the authorization context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE, $this->_stageId);
		return AUTHORIZATION_PERMIT;
	}
}

?>
