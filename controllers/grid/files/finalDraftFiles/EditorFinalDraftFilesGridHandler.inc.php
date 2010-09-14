<?php

/**
 * @file controllers/grid/files/finalDraftFiles/EditorFinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorFinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Handle the final draft files grid
 *  This grid is for Series Editor and Press Manager roles -- It allows manipulation of the files in the grid
 */

import('controllers.grid.files.finalDraftFiles.FinalDraftFilesGridHandler');

class EditorFinalDraftFilesGridHandler extends FinalDraftFilesGridHandler {

	/**
	 * Constructor
	 */
	function EditorFinalDraftFilesGridHandler() {
		parent::FinalDraftFilesGridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles', 'manageFinalDraftFiles',	'uploadFinalDraftFile', 'updateFinalDraftFiles'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}
}