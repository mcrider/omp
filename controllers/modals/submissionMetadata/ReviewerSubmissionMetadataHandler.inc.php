<?php

/**
 * @file controllers/modals/submissionMetadata/ReviewerSubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionMetadataHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Display submission metadata to reviewers.
 */

import('classes.controllers.modals.submissionMetadata.SubmissionMetadataHandler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class ReviewerSubmissionMetadataHandler extends SubmissionMetadataHandler {
	/**
	 * Constructor.
	 */
	function ReviewerSubmissionMetadataHandler() {
		parent::SubmissionMetadataHandler();
		$this->addRoleAssignment(array(ROLE_ID_REVIEWER), array('fetch'));
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
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see classes/controllers/modals/submissionMetadata/SubmissionMetadataHandler::fetch()
	 */
	function fetch($args, &$request) {
		$press =& $request->getPress();
		// FIXME: Need to be able to get/set if a review is blind or not, see #6403.
		$isBlindReview = false;
		$params = array('readOnly' => true, 'anonymous' => $isBlindReview);

		return parent::fetch($args, $request, $params);
	}
}

?>
