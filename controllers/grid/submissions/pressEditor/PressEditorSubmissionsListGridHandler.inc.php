<?php

/**
 * @file controllers/grid/submissions/pressEditor/PressEditorSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressEditorSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Handle press editor submissions list grid requests.
 */

// import grid base classes
import('controllers.grid.submissions.SubmissionsListGridHandler');

// import specific grid classes
import('controllers.grid.submissions.pressEditor.PressEditorSubmissionsListGridCellProvider');
import('controllers.grid.submissions.pressEditor.PressEditorSubmissionsListGridRow');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class PressEditorSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function PressEditorSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->roleId = ROLE_ID_EDITOR;

		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_EDITOR)));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return PressEditorSubmissionsListGridRow
	*/
	function &getRowInstance() {
		$row = new PressEditorSubmissionsListGridRow();
		return $row;
	}

	//
	// Getters/Setters
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('editorAction', 'showApprove', 'saveApprove', 'showDecline', 'saveDecline', 'moreInfo'));
	}

	//
	// Overridden methods from SubmissionListGridHandler
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR));

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		$this->setData($this->_getSubmissions($request, $user->getId(), $press->getId()));

		// change the first column cell template to allow for actions
		$titleColumn =& $this->getColumn('title');
		$titleColumn->setTemplate('controllers/grid/gridCellInSpan.tpl');

		// Add author-specific columns
		$emptyColumnActions = array();
		$cellProvider = new PressEditorSubmissionsListGridCellProvider();

		$session =& $request->getSession();
		$actingAsUserGroupId = $session->getSessionVar('userGroupId');
		// FIXME: bug 5321 : need to implement acting as in session
		$actingAsUserGroupId = 437;
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

		// Add one column for each of the Author user groups
		$authorUserGroups =& $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR);
		while (!$authorUserGroups->eof()) {
			$authorUserGroup =& $authorUserGroups->next();
			$this->addColumn(
				new GridColumn(
					$authorUserGroup->getId(),
					null,
					$authorUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);
			unset($authorUserGroup);
		}

	}

	//
	// Public SubmissionsList Grid Actions
	//
	/**
	 * Delete a submission
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSubmission(&$args, &$request) {
		//FIXME: Implement

		return false;
	}
	
	/**
	 * Show the submission approval modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function showApprove(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.ApproveSubmissionForm');
		$approveForm = new ApproveSubmissionForm($monographId);

		if ($approveForm->isLocaleResubmit()) {
			$approveForm->readInputData();
		} else {
			$approveForm->initData($args, $request);
		}

		$json = new JSON('true', $approveForm->fetch($request));
		return $json->getString();
	}
	
	/**
	 * Save the submission approval modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveApprove(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		
		import('controllers.grid.submissions.pressEditor.form.ApproveSubmissionForm');
		$approveForm = new ApproveSubmissionForm($monographId);
		
		if ($approveForm->validate()) {
			$approveForm->execute($args, $request);
			$router =& $request->getRouter();

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}
	
	/**
	 * Show the submission decline modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function showDecline(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.DeclineSubmissionForm');
		$declineForm = new DeclineSubmissionForm($monographId);

		if ($declineForm->isLocaleResubmit()) {
			$declineForm->readInputData();
		} else {
			$declineForm->initData($args, $request);
		}

		$json = new JSON('true', $declineForm->fetch($request));
		return $json->getString();
	}
	
	/**
	 * Save the submission decline modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveDecline(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		
		import('controllers.grid.submissions.pressEditor.form.DeclineSubmissionForm');
		$declineForm = new DeclineSubmissionForm($monographId);
		
		if ($declineForm->validate()) {
			$declineForm->execute($args, $request);
			$router =& $request->getRouter();

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}
	
	/**
	 * Show the information center for this submission
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function moreInfo(&$args, &$request) {
		import('pages.informationCenter.informationCenterHandler');
		$informationCenter =& new InformationCenterHandler();
		
		return $informationCenter->viewInformationCenter($args, $request);
	}

	//
	// Private helper functions
	//
	function _getSubmissions(&$request, $userId, $pressId) {
		//$rangeInfo =& Handler::getRangeInfo('submissions');
		$page = $request->getUserVar('status');
		switch($page) {
			case 'submissionsInReview':
				$functionName = 'getInReview';
				$this->setTitle('common.queue.long.submissionsInReview');
			case 'submissionsInEditing':
				$functionName = 'getInEditing';
				$this->setTitle('common.queue.long.submissionsInEditing');
				break;
			case 'submissionsArchives':
				$functionName = 'getArchives';
				$this->setTitle('common.queue.long.submissionsArchives');
				break;
			default:
				$functionName = 'getUnassigned';
				$this->setTitle('common.queue.long.submissionsUnassigned');
				break;
		}

		// TODO: nulls represent search options which have not yet been implemented
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$submissions =& $editorSubmissionDao->$functionName($pressId, 0, FILTER_EDITOR_ALL);

		$data = array();
		while($submission =& $submissions->next()) {
			$submissionId = $submission->getId();
			$data[$submissionId] = $submission;
			unset($submision);
		}

		return $data;
	}

	//
	// Validator
	//
	function validate($requiredContexts, $request) {
		return parent::validate($requiredContexts, $request);
	}
}