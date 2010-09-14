<?php

/**
 * @file controllers/grid/files/CopyeditingFiles/CopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_CopyeditingFiles
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import copyediting grid specific classes
import('controllers.grid.files.copyeditingFiles.CopyeditingFilesGridCategoryRow');
import('controllers.grid.files.copyeditingFiles.CopyeditingFilesGridCellProvider');

class CopyeditingFilesGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridHandler() {
		parent::CategoryGridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'addUser', 'getCopyeditUserAutocomplete', 'downloadFile', 'downloadAllFiles'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
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
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();
		$this->setId('copyeditingFiles');
		$this->setTitle('submission.copyediting');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Grab the copyediting files to display as categories
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId, 'submission/copyediting');
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);

		// Grid actions
		// Action to add a file -- Adds a category row for the file
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'uploadFile',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, 'grid.files.submissionFiles.CopyeditingSubmissionFilesGridHandler', 'addFile', null, array('monographId' => $monographId, 'fileStage' => 'submission/copyediting')),
				'editor.monograph.fairCopy.addFile',
				null,
				'add'
			)
		);
		// Action to add a user -- Adds the user as a subcategory to the files selected in its modal
		$this->addAction(
			new LinkAction(
				'addUser',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_REPLACE,
				$router->url($request, null, null, 'addUser', null, array('monographId' => $monographId)),
				'editor.monograph.copyediting.addUser',
				null,
				'add'
			)
		);

		// Grid Columns
		$cellProvider =& new CopyeditingFilesGridCellProvider();
		$session =& $request->getSession();
		$actingAsUserGroupId = $session->getActingAsUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

		// Add a column for the file's label
		$this->addColumn(
			new GridColumn(
				'name',
				'common.file',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Add a column for the files type (e.g. Manuscript)
		$this->addColumn(
			new GridColumn(
				'type',
				'common.type',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Add a column for the role the user is acting as
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
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return CopyeditingFilesGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row =& new CopyeditingFilesGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 * @param $monographFile MonographFile
	 * @return signoff
	 */
	function getCategoryData(&$monographFile) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId());
		return $signoffs;
	}

	//
	// Public methods
	//

	/**
	 * Adds a user to a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function addUser(&$args, &$request) {
		// Identify the monograph being worked on
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.grid.files.copyeditingFiles.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monographId);
		if ($copyeditingUserForm->isLocaleResubmit()) {
			$copyeditingUserForm->readInputData();
		} else {
			$copyeditingUserForm->initData(&$args, &$request);
		}

		$json = new JSON('true', $copyeditingUserForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the form for adding a user to a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveAddUser(&$args, &$request) {
		// Identify the monograph being worked on
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.grid.files.copyeditingFiles.form.CopyeditingUserForm');
		$copyeditingUserForm = new CopyeditingUserForm($monographId);
		$copyeditingUserForm->readInputData();
		if ($copyeditingUserForm->validate()) {
			$copyeditingUserForm->execute();

			$signoffDao =& DAORegistry::getDAO('SignoffDAO');
			$data =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, $monograph->getCurrentStageId());

			$this->setData($data);
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
			$json = new JSON('false', Locale::translate('submission.submit.errorUpdatingStageParticipant'));
		}
		return $json->getString();
	}

	/**
	 * Get users for copyediting autocomplete.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function getCopyeditUserAutocomplete(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		// Retrieve the users for the autocomplete control: Any author or press assistant user assigned to this stage
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$stageUsers =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, WORKFLOW_STAGE_ID_EDITING);

		$itemList = array();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		while($stageUser =& $stageUsers->next()) {
			$userGroup =& $userGroupDao->getById($stageUser->getUserGroupId());
			// Disallow if the user's user group is a reviewer role
			if ($userGroup->getRoleId() != ROLE_ID_REVIEWER) {
				$user =& $userDao->getUser($stageUser->getUserId());
				$itemList[] = array('id' => $user->getUserId(),
									'name' => $user->getFullName(),
								 	'abbrev' => $userGroup->getLocalizedName());
			}
		}

		import('lib.pkp.classes.core.JSON');
		$sourceJson = new JSON('true', null, 'false', 'local');
		$sourceContent = array();
		foreach ($itemList as $i => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $item['id']
		   );
			$itemJson = new JSON('true', '', 'false', null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
	}

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

}