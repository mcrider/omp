<?php

/**
 * @file controllers/grid/files/finalDraftFiles/AuthorFinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorFinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Handle the final draft files grid
 *  This grid is for authors and press assistants; It only allows reading of final draft files, not writing (uploading/deleting)
 */

import('controllers.grid.files.finalDraftFiles.FinalDraftFilesGridHandler');

class AuthorFinalDraftFilesGridHandler extends FinalDraftFilesGridHandler {
	/**
	 * Constructor
	 */
	function AuthorFinalDraftFilesGridHandler() {
		parent::FinalDraftFilesGridHandler();

		$this->addRoleAssignment(array(ROLE_ID_REVIEWER,  ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER), array());
		$this->addRoleAssignment(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, array('fetchGrid', 'downloadFile', 'downloadAllFiles'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}