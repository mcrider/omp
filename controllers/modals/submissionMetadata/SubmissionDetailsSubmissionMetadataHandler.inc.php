<?php

/**
 * @file controllers/modals/submissionMetadata/SubmissionDetailsSubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDetailsSubmissionMetadataHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle requests for non-reviewers to see a submission's metadata.
 */

import('classes.controllers.modals.submissionMetadata.SubmissionMetadataHandler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class SubmissionDetailsSubmissionMetadataHandler extends SubmissionMetadataHandler {

	/**
	 * Constructor.
	 */
	function SubmissionDetailsSubmissionMetadataHandler() {
		parent::SubmissionMetadataHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'saveForm'));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public methods.
	//
	/**
	 * @see classes/controllers/modals/submissionMetadata/SubmissionMetadataHandler::fetch()
	 */
	function fetch($args, &$request) {

		$params = array('readOnly' => (boolean) $request->getUserVar('readOnly'));

		return parent::fetch($args, $request, $params);
	}
}

?>
