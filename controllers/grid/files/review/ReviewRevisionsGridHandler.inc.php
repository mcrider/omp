<?php

/**
 * @file controllers/grid/files/ReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_revisions
 *
 * @brief Display the file revisions authors have uploaded
 */

// import submission files grid specific classes
import('controllers.grid.files.review.ReviewFilesGridHandler');

class ReviewRevisionsGridHandler extends ReviewFilesGridHandler {
	/**
	 * Constructor
	 */
	function ReviewRevisionsGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = false, $canManage = false) {
		parent::ReviewFilesGridHandler($canAdd, $isSelectable, $canDownloadAll, $canManage);

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		$this->setTitle('editor.monograph.revisions');
		$round = $this->getRound();
		$reviewType = $this->getReviewType();

		// Basic grid configuration
		$monograph =& $this->getMonograph();

		// Load monograph files.
		$this->loadMonographFiles();

		$cellProvider = new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		// Load additional translation components
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Get IDs of selected files
		// Grab the files that are the same as in the current review, but with later revisions
		$reviewRoundDAO =& DAORegistry::getDAO('ReviewRoundDAO');
		$selectedFiles =& $reviewRoundDAO->getRevisionsOfCurrentReviewFiles($monograph->getId(), $round);
		$selectedFileIds = array();
		foreach($selectedFiles as $selectedFile) {
			$selectedFileIds[] = $selectedFile->getFileId() . "-" . $selectedFile->getRevision();
		}
		$this->setSelectedFileIds($selectedFileIds);


		$this->addColumn(new GridColumn('type', 'common.type', null, 'controllers/grid/gridCell.tpl', $cellProvider));
	}

	//
	// Protected methods
	//

	/**
	 * Select the files to load in the grid
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$monograph =& $this->getMonograph();
		// Grab the files that are currently set for the review
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$monographFiles =& $reviewRoundDao->getRevisionsOfCurrentReviewFiles($monograph->getId(), $this->getRound());

		$this->setData($monographFiles);
	}


}