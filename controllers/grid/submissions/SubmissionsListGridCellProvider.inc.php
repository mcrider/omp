<?php

/**
 * @file classes/controllers/grid/submissions/SubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridCellProvider
 * @ingroup controllers_grid_submissionsList
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SubmissionsListGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionsListGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}


	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 */
	function getCellState(&$row, &$column) {
		return '';
	}
	
	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of GridAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$monograph =& $row->getData();
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $row->getGridId(),
			'monographId' => $monograph->getId(),
			'reviewType' => $monograph->getCurrentReviewType(),
			'round' => $monograph->getCurrentRound()
		);

		if($column->getId() == 'title') {
			$action =& new GridAction(
				'submissionInfo',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_NOTHING,
				$router->url($request, null, null, 'getSubmissionInformation', null, $actionArgs),
				$monograph->getLocalizedTitle()
			);
			return array($action);
		}

		return array();
		
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'dateAssigned':
				$dateAssigned = $element->getDateAssigned();
				if ( empty($dateAssigned) ) $dateAssigned = '--';
				return array('label' => $dateAssigned);
				break;
			case 'dateDue':
				$dateDue = $element->getDateAssigned();
				if ( empty($dateDue) ) $dateDue = '--';
				return array('label' => $dateDue);
				break;
			case 'reviewRound':
				$reviewRound = $element->getRound();
				if ( empty($reviewRound) ) $reviewRound = '--';
				return array('label' => $reviewRound);
				break;
		}
	}
}