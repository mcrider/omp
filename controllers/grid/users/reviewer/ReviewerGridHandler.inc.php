<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridHandler
 * @ingroup controllers_grid_reviewer
 *
 * @brief Handle reviewer grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import reviewer grid specific classes
import('controllers.grid.users.reviewer.ReviewerGridCellProvider');
import('controllers.grid.users.reviewer.ReviewerGridRow');

class ReviewerGridHandler extends GridHandler {
	/** @var Monograph */
	var $_submission;

	/**
	 * Constructor
	 */
	function ReviewerGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addReviewer', 'editReviewer', 'updateReviewer', 'deleteReviewer', 'getReviewerAutocomplete'));
	}

	/**
	 * Get the monograph associated with this reviewer grid.
	 * @return Monograph
	 */
	function &getSubmission() {
		return $this->_submission;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * Validate that ...
	 * fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// FIXME: implement validation
		// Retrieve and validate the monograph id
		$monographId =& $request->getUserVar('monographId');
		if (!is_numeric($monographId)) return false;

		// Retrieve the submission associated with this reviewers grid
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);

		// Monograph and editor validation
		if (!is_a($seriesEditorSubmission, 'SeriesEditorSubmission')) return false;

		// Validation successful
		$this->_submission =& $seriesEditorSubmission;
		return true;
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_EDITOR));

		// Basic grid configuration
		$this->setTitle('user.role.reviewers');

		// Get the monograph
		$submission =& $this->getSubmission();
		assert(is_a($submission, 'Monograph'));
		$monographId = $submission->getId();

		// Get the review round currently being looked at
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');

		// Get the existing review assignments for this monograph
		$reviewAssignments =& $submission->getReviewAssignments($reviewType, $round);

		$this->setData($data);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monographId,
							'reviewType' => $reviewType,
							'round' => $round);
		$this->addAction(
			new GridAction(
				'addReviewer',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addReviewer', null, $actionArgs),
				'grid.action.addReviewer'
			)
		);

		// Columns
		$cellProvider = new ReviewerGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'user.name',
				null,
				'controllers/grid/gridCellInSpan.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'reviewer',
				'user.role.reviewer',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		// submitter userGroupId
		$userGroupId = $submission->getUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$submitterUserGroup =& $userGroupDao->getById($userGroupId);
		$this->addColumn(
			new GridColumn(
				'submitter',
				null,
				$submitterUserGroup->getLocalizedName(),
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return ReviewerGridRow
	 */
	function &getRowInstance() {
		// Return a reviewer row
		$row = new ReviewerGridRow();
		return $row;
	}


	//
	// Public Reviewer Grid Actions
	//
	/**
	 * An action to manually add a new reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addReviewer(&$args, &$request) {
		// Calling editReviewer() with an empty row id will add
		// a new reviewer.
		return $this->editReviewer($args, $request);
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the reviewer to be updated
		$reviewerId = $request->getUserVar('reviewerId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($monographId, $reviewerId);
		$reviewerForm->initData();
		$reviewerForm->display($request);
		
		$json = new JSON('true', $reviewerForm->fetch($request));
		return $json->getString();
	}

	/**
	* Get potential reviewers for editor's reviewer selection autocomplete.
	*/
	function getReviewerAutocomplete(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$press =& $request->getPress();
		$seriesEditorSubmissionDAO =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		
		// Get items to populate possible items list with
		$reviewers =& $seriesEditorSubmissionDAO->getReviewersNotAssignedToMonograph($press->getId(), $monographId);
		$reviewers =& $reviewers->toArray();
		
		$itemList = array();
		foreach ($reviewers as $i => $reviewer) {
			$itemList[] = array('id' => $reviewer->getId(),
								 'name' => $reviewer->getFullName(),
								 'abbrev' => $reviewer->getUsername()
								);
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
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the reviewer to be updated
		$reviewerId = $request->getUserVar('reviewerId');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$reviewer =& $authorDao->getAuthor($reviewerId);

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($monographId, $reviewer);
		$reviewerForm->readInputData();
		if ($reviewerForm->validate()) {
			$authorId = $reviewerForm->execute();

			if(!isset($reviewer)) {
				// This is a new contributor
				$reviewer =& $authorDao->getAuthor($authorId);
			}

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($authorId);
			$row->setData($reviewer);
			$row->initialize($request);

			// Render the row into a JSON response
			if($reviewer->getPrimaryContact()) {
				$additionalAttributes = array('script' => 'updateItem(\'remove\', \'isPrimaryContact\')');
				$json = new JSON('true', $this->_renderRowInternally($request, $row), 'true', null, $additionalAttributes);
			} else {
				$json = new JSON('true', $this->_renderRowInternally($request, $row));
			}
		} else {
			$json = new JSON('false', Locale::translate('author.submit.errorUpdatingReviewer'));
		}
		return $json->getString();
	}

	/**
	 * Delete a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the reviewer to be deleted
		$reviewerId = $request->getUserVar('reviewerId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($reviewerId, $monographId);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('author.submit.errorDeletingReviewer'));
		}
		return $json->getString();
	}
}