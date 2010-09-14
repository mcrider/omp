<?php

/**
 * @file controllers/grid/files/editorReviewFileSelection/ReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFilesGridHandler
 * @ingroup controllers_grid_files_reviewFiles
 *
 * @brief Handle the editor review file selection grid (selects which files to send to review)
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

class ReviewFilesGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/** Boolean flag if user can upload file to grid **/
	var $_canUpload;

	/** Boolean flag for showing role columns **/
	var $_showRoleColumns;

	/**
	 * Constructor
	 */
	function ReviewFilesGridHandler() {
		parent::GridHandler();

	}

	//
	// Getters/Setters
	//
	/**
	 * Set the selectable flag
	 * @param $isSelectable bool
	 */
	function setIsSelectable($isSelectable) {
		$this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return bool
	 */
	function getIsSelectable() {
		return $this->_isSelectable;
	}

	/**
	 * Set the canUpload flag
	 * @param $canUpload bool
	 */
	function setCanUpload($canUpload) {
		$this->_canUpload = $canUpload;
	}

	/**
	 * Get the canUpload flag
	 * @return bool
	 */
	function getCanUpload() {
		return $this->_canUpload;
	}

	/**
	 * Set the show role columns flag
	 * @param $showRoleColumns bool
	 */
	function setShowRoleColumns($showRoleColumns) {
		$this->_showRoleColumns = $showRoleColumns;
	}

	/**
	 * Get the show role columns flag
	 * @return bool
	 */
	function getShowRoleColumns() {
		return $this->_showRoleColumns;
	}


	//
	// Implement template methods from PKPHandler
	//

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$monographId = (integer)$request->getUserVar('monographId');
		$this->setId('reviewFiles');
		$this->setTitle('reviewer.monograph.reviewFiles');

		// Set the Is Selectable boolean flag
		$isSelectable = (boolean)$request->getUserVar('isSelectable');
		$this->setIsSelectable($isSelectable);

		// Set the Can upload boolean flag
		$canUpload = (boolean)$request->getUserVar('canUpload');
		$this->setCanUpload($canUpload);

		// Set the show role columns boolean flag
		$showRoleColumns = (boolean)$request->getUserVar('showRoleColumns');
		$this->setShowRoleColumns($showRoleColumns);

		$reviewType = (int)$request->getUserVar('reviewType');
		$round = (int)$request->getUserVar('round');

		// Check if the user can add files to the round
		$canAdd = (boolean)$request->getUserVar('canAdd');

		// Grab the files that are currently set for the review
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$selectedFiles =& $reviewAssignmentDAO->getReviewFilesByRound($monographId);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Do different initialization if this is a selectable grid or if its a display only version of the grid.
		if ( $isSelectable ) {
			// Load a different grid template
			$this->setTemplate('controllers/grid/files/reviewFiles/grid.tpl');

			// Set the files to all the available files to allow selection.
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($monographId);
			$this->setData($monographFiles);
			$this->setId('reviewFilesSelect'); // Need a unique ID since the 'manage review files' modal is in the same namespace as the 'view review files' modal

			// Set the already selected elements of the grid
			$templateMgr =& TemplateManager::getManager();
			$selectedRevisions =& $reviewAssignmentDAO->getReviewFilesAndRevisionsByRound($monographId, $round, true);
			$templateMgr->assign('selectedFileIds', $selectedRevisions);
		} else {
			// set the grid data to be only the files that have already been selected
			$data = isset($selectedFiles[$reviewType][$round]) ? $selectedFiles[$reviewType][$round] : array();
			$this->setData($data);
		}

		// Test whether the tar binary is available for the export to work, if so, add grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if (isset($this->_data) && !empty($tarBinary) && file_exists($tarBinary)) {
			$this->addAction(
				new LinkAction(
					'downloadAll',
					LINK_ACTION_MODE_LINK,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, null, 'downloadAllFiles', null, array('monographId' => $monographId)),
					'submission.files.downloadAll',
					null,
					'getPackage'
				)
			);
		}

		if ($canAdd) {
			$this->addAction(
				new LinkAction(
					'manageReviewFiles',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'manageReviewFiles', null, array('monographId' => $monographId)),
					'editor.submissionArchive.manageReviewFiles',
					null,
					'add'
				)
			);
		}

		if ($canUpload) {
			$this->addAction(
				new LinkAction(
					'uploadReviewFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, 'grid.files.submissionFiles.SubmissionReviewFilesGridHandler', 'addFile', null, array('monographId' => $monographId)),
					'editor.submissionArchive.uploadFile',
					null,
					'add'
				)
			);
		}

		import('controllers.grid.files.reviewFiles.ReviewFilesGridCellProvider');
		$cellProvider =& new ReviewFilesGridCellProvider();
		// Columns
		if ($this->getIsSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/files/reviewFiles/gridRowSelectInput.tpl',
				$cellProvider)
			);
		}

		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);

		// either show the role columns or show the file type
		if ( $this->getShowRoleColumns() ) {
			$session =& $request->getSession();
			$actingAsUserGroupId = $session->getActingAsUserGroupId();
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

			// add a column for the role the user is acting as
			$this->addColumn(
				new GridColumn(
					$actingAsUserGroupId,
					null,
					$actingAsUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);

			// Add another column for the submitter's role
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($monographId);
			$uploaderUserGroup =& $userGroupDao->getById($monograph->getUserGroupId());
			$this->addColumn(
				new GridColumn(
					$uploaderUserGroup->getId(),
					null,
					$uploaderUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);
		} else {
			$this->addColumn(new GridColumn('type',
				'common.type',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider)
			);
		}
	}

	//
	// Public methods
	//
	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function downloadFile(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFile($fileId);
	}

	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function downloadAllFiles(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFilesArchive($this->_data);
	}

	/**
	 * Add a file that the Press Editor did not initally add to the review
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function manageReviewFiles(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.reviewFiles.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monographId);

		$manageReviewFilesForm->initData($args, $request);
		$json = new JSON('true', $manageReviewFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Allow the editor to upload a new file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function uploadReviewFile(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.reviewFiles.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monographId);

		$manageReviewFilesForm->initData($args, $request);
		$json = new JSON('true', $manageReviewFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save 'manage review files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function updateReviewFiles(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.reviewFiles.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monographId);

		$manageReviewFilesForm->readInputData();

		if ($manageReviewFilesForm->validate()) {
			$selectedFiles =& $manageReviewFilesForm->execute($args, $request);

			// Re-render the grid with the updated files
			$this->setData($selectedFiles);
			$this->initialize($request);

			// Pass to modal.js to reload the grid with the new content
			$gridBodyParts = $this->_renderGridBodyPartsInternally($request);
			if (count($gridBodyParts) == 0) {
				// The following should usually be returned from a
				// template also so we remain view agnostic. But as this
				// is easy to migrate and we want to avoid the additional
				// processing overhead, let's just return plain HTML.
				$renderedGridRows = '<tbody> </tbody>';
			} else {
				assert(count($gridBodyParts) == 1);
				$renderedGridRows = $gridBodyParts[0];
			}
			$json = new JSON('true', $renderedGridRows);
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}
}