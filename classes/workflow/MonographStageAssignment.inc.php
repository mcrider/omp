<?php

/**
 * @file classes/user/MonographStageAssignment.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographStageAssignment
 * @ingroup workflow
 * @see MonographStageAssignmentDAO
 *
 * @brief Basic class describing user to publication stage assignments
 */

class MonographStageAssignment extends DataObject {

	/**
	 * Constructor.
	 */
	function MonographStageAssignment() {
		parent::DataObject();
	}

	/**
	* Set the assignment id
	* @param $pressId int
	*/
	function setMonographStageAssignmentId($monographStageAssignmentId) {
		$this->setData('monographStageAssignmentId', $monographStageAssignmentId);
	}

	/**
	* Get the assignment id
	* @return int
	*/
	function getMonographStageAssignmentId() {
		return $this->getData('monographStageAssignmentId');
	}

	/**
	* Set the monographId id
	* @param $pressId int
	*/
	function setMonographId(&$monographId) {
		$this->setData('monographId', $monographId);
	}

	/**
	* Get the monographId id
	* @return int
	*/
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	* Set the workflow stage id
	* @param $stageId int
	*/
	function setPublicationStageId(&$stageId) {
		$this->setData('publicationStageId', $publicationStageId);
	}

	/**
	* Get the workflow stage id
	* @return int
	*/
	function getPublicationStageId() {
		return $this->getData('publicationStageId');
	}

	/**
	* Set the assigned user's id
	* @param $stageId int
	*/
	function setUserId($userId) {
		$this->setData('userId', $userId);
	}

	/**
	* Get the assigned user's id
	* @return int
	*/
	function getUserId() {
		return $this->getData('userId');
	}

	/**
	 * Get the user object associated with this assignment
	 * @return PKPUser
	 */
	function getUser() {
		$userDao =& DAORegistry::getDAO('UserDAO');
		return $userDao->getUser($this->getData('userId'));
	}

	/**
	* Set the user's group ID within the assignment
	* @param $userGroupId int
	*/
	function setUserGroupId($userGroupId) {
		$this->setData('userGroupId', $userGroupId);
	}

	/**
	* Get the user's group ID within the assignment
	* @return int
	*/
	function getUserGroupId() {
		return $this->getData('userGroupId');
	}
}

?>
