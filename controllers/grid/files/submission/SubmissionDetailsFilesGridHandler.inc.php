<?php

/**
 * @file controllers/grid/files/submission/SubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Base handler for the submission stage grids.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.SubmissionFilesGridHandler');

class SubmissionDetailsFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 * @param $canAdd boolean Whether to show the 'add files' grid action
	 */
	function SubmissionDetailsFilesGridHandler($canAdd = true, $revisionOnly = false, $isSelectable = false, $canDownloadAll = false) {
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_SUBMISSION, $canAdd, $revisionOnly, $isSelectable, $canDownloadAll);

		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'finishFileSubmission', 'addFile', 'displayFileUploadForm', 'uploadFile', 'confirmRevision',
						'editMetadata', 'saveMetadata', 'downloadFile', 'downloadAllFiles', 'deleteFile'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $additionalActionArgs = array()) {
		// Basic grid configuration
		$this->setTitle('submission.submit.submissionFiles');

		// Load monograph files.
		$this->loadMonographFiles();

		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider, $additionalActionArgs);

		$this->addColumn(new GridColumn('fileType',	'common.fileType', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}
}