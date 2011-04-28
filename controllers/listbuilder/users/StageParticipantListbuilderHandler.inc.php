<?php

/**
 * @file controllers/listbuilder/users/StageParticipantListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding participants to a stage.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class StageParticipantListbuilderHandler extends ListbuilderHandler {
	/** @var integer The user group ID that we'll filter stage participants on **/
	var $_userGroupId;

	/**
	 * Constructor
	 */
	function StageParticipantListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'fetchRow', 'fetchOptions', 'addItem', 'deleteItems')
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get the authorized workflow stage.
	 * @return integer
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	/**
	 * Set the user group id
	 * @param $userGroupId int
	 */
	function setUserGroupId($userGroupId) {
		$this->_userGroupId = $userGroupId;
	}

	/**
	 * Get the user group id
	 * @return int
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
	}

		//
	// Overridden parent class functions
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId(),
			'userGroupId' => $this->getUserGroupId()
		);
	}

	/**
	 * @see ListbuilderHandler::getOptions
	 * @param $request PKPRequest
	 */
	function getOptions(&$request) {
		// Retrieve all users that belong to the current user group
		// FIXME #6000: If user group is in the series editor role, only allow it
		// if the series editor is assigned to the monograph's series.

		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$monograph =& $this->getMonograph();
		$userFactory = $stageAssignmentDao->getUsersNotAssignedToStage($monograph->getId(), $this->getStageId(), $this->getUserGroupId());

		$users = array();
		if($userFactory) {
			foreach($userFactory as $user) {
				$users[(int)$user->getId()] = $user->getFullName();
				unset($user);
			}
		}

		return array($users);
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 */
	function loadList(&$request) {
		// Retrieve the participants associated with the current group, monograph, and stage.
		$userGroupId = $this->getUserGroupId();
		$monograph =& $this->getMonograph();
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$users = $stageAssignmentDao->getUsersBySubmissionAndStageId($monograph->getId(), $this->getStageId(), $userGroupId);

		$items = array();
		if(isset($users)) {
			foreach($users as $item) {
				$id = $item->getId();
				$items[$id] = array('name' => $item->getFullName());
			}
		}
		$this->setGridDataElements($items);
	}


	//
	// Implement protected template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int)$request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic configuration.
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);

		$this->setUserGroupId((int) $request->getUserVar('userGroupId'));

		$this->loadList($request);

		// Configure listbuilder column.
		$this->addColumn(new ListbuilderGridColumn($this, 'name', 'common.name'));

	}

	/**
	 * Create a new data element from a request. This is used to format
	 * new rows prior to their insertion.
	 * @param $request PKPRequest
	 * @param $elementId int
	 * @return object
	 */
	function &getDataElementFromRequest(&$request, &$elementId) {
		$options = $this->getOptions($request);

		$nameIndex = $request->getUserVar('name');
		assert($nameIndex == '' || isset($options[0][$nameIndex]));
		$newItem = array(
			'name' => $nameIndex == ''?'':$options[0][$nameIndex]
		);

		return $newItem;
	}

	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry($entry) {
		// Make sure the item doesn't already exist.
		$userId = (int)$this->getAddedItemId($args);
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$userGroupId = (int)$request->getUserVar('userGroupId');

		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		if($stageAssignmentDao->stageAssignmentExists($monographId, $this->getStageId(), $userGroupId, $userId)) {
			// Warn the user that the item has been added before.
			return false;
		}

		// Create a new stage assignment.
		$stageAssignmentDao->build($monographId, $this->getStageId(), $userGroupId, $userId);
		return true;
	}

	/**
	 * Delete an entry.
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteEntry($rowId) {
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignmentDao->deleteByAll($monograph->getId(), $this->getStageId(), $this->getUserGroupId(), $rowId);

		return true;
	}

}
?>
