<?php

/**
 * @file classes/controllers/grid/users/ReviewerGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve labels for submission contributors
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class ReviewerGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewerGridCellProvider() {
		parent::DataObjectGridCellProvider();
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
			case 'name':
				return array('label' => $element->getFullName());
			case 'userGroupId':
				return array('label' => $element->getLocalizedUserGroupName());
			case 'email':
				return parent::getTemplateVarsFromRowColumn($row, $column);
			case 'principalContact':
				return array('isPrincipalContact' => $element->getPrimaryContact());
		}
	}
}