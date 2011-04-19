<?php

/**
 * @file controllers/grid/users/reviewerSelect/ReviewerSelectGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSelectGridHandler
 * @ingroup controllers_grid_users_reviewerSelect
 *
 * @brief Handle reviewer selector grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import submissionContributor grid specific classes
import('controllers.grid.users.reviewerSelect.ReviewerSelectGridCellProvider');
import('controllers.grid.users.reviewerSelect.ReviewerSelectGridRow');

class ReviewerSelectGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function ReviewerSelectGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid'));
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
		parent::initialize($request);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_EDITOR));
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Columns
		$cellProvider = new ReviewerSelectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'select',
				'',
				null,
				'controllers/grid/users/reviewerSelect/reviewerSelectRadioButton.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'done',
				'common.done',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'avg',
				'editor.review.days',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'last',
				'editor.submissions.lastAssigned',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'active',
				'common.active',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'interests',
				'user.interests',
				null,
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
	 * @return ReviewerSelectGridRow
	 */
	function &getRowInstance() {
		$row = new ReviewerSelectGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve the submissionContributors associated with this monograph to be displayed in the grid
		$done_min = $filter['done_min'];
		$done_max = $filter['done_max'];
		$avg_min = $filter['avg_min'];
		$avg_max = $filter['avg_max'];
		$last_min = $filter['last_min'];
		$last_max = $filter['last_max'];
		$active_min = $filter['active_min'];
		$active_max = $filter['active_max'];
		$interests = $filter['interests'];

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$data =& $seriesEditorSubmissionDao->getFilteredReviewers($monograph->getPressId(), $done_min, $done_max, $avg_min, $avg_max,
					$last_min, $last_max, $active_min, $active_max, $interests, $monograph->getId(), $monograph->getCurrentRound());
		return $data;
	}

	/**
	 * @see GridHandler::getFilterSelectionData()
	 * @return array Filter selection data.
	 */
	function getFilterSelectionData($request) {
		// Get the values searched for
		$doneMin = (int) $request->getUserVar('done_min');
		$doneMax = (int) $request->getUserVar('done_max');
		$avgMin = (int) $request->getUserVar('avg_min');
		$avgMax = (int) $request->getUserVar('avg_max');
		$lastMin = (int) $request->getUserVar('last_min');
		$lastMax = (int) $request->getUserVar('last_max');
		$activeMin = (int) $request->getUserVar('active_min');
		$activeMax = (int) $request->getUserVar('active_max');
		$interests = $request->getUserVar('interestSearchKeywords');
		if(isset($interests) && is_array($interests)) {
			$interests = array_map('urldecode', $interests); // The interests are coming in encoded -- Decode them for DB storage
		} else {
			$interests = array();
		}

		return $filterSelectionData = array(
			'done_min' => $doneMin,
			'done_max' => $doneMax,
			'avg_min' => $avgMin,
			'avg_max' => $avgMax,
			'last_min' => $lastMin,
			'last_max' => $lastMax,
			'active_min' => $activeMin,
			'active_max' => $activeMax,
			'interests' => $interests
		);
	}

	/**
	 * @see GridHandler::getFilterForm()
	 */
	function getFilterForm() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		import('controllers.grid.users.reviewerSelect.form.AdvancedSearchReviewerFilterForm');
		$filterForm = new AdvancedSearchReviewerFilterForm($monograph, null);
		return $filterForm;
	}
}

?>
