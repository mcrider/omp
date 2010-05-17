<?php

/**
 * @file controllers/grid/users/submissionContributor/ReviewerGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridRow
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief SubmissionContributor grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewerGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function ReviewerGridRow() {
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph id from the request
		$monographId = $request->getUserVar('monographId');
		assert(is_numeric($monographId));

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monographId,
				'submissionContributorId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'editSubmissionContributor',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editSubmissionContributor', null, $actionArgs),
					'grid.action.edit',
					'edit'
				)
			);
			$this->addAction(
				new GridAction(
					'deleteSubmissionContributor',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteSubmissionContributor', null, $actionArgs),
					'grid.action.delete',
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}