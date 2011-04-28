<?php

/**
 * @file classes/stageAssignment/StageAssignmentDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageAssignmentDAO
 * @ingroup stageAssignment
 * @see StageAssignment
 *
 * @brief Operations for retrieving and modifying StageAssignment objects.
 */

import('classes.stageAssignment.StageAssignment');

class StageAssignmentDAO extends DAO {
	/**
	 * Retrieve StageAssignments by submission and stage IDs.
	 * @param $submissionId int
	 * @param $stageId int (optional)
	 * @param $userGroupId int (optional)
	 * @param $userId int (optional)
	 * @return DAOResultFactory StageAssignment
	 */
	function getBySubmissionAndStageId($submissionId, $stageId = null, $userGroupId = null, $userId = null) {
		return $this->_getByIds($submissionId, $stageId, $userGroupId, $userId);
	}

	/**
	 * Retrieve StageAssignments by submission and stage IDs.
	 * @param $submissionId int
	 * @param $stageId int (optional)
	 * @param $userGroupId int (optional)
	 * @return DAOResultFactory StageAssignment
	 */
	function getUsersBySubmissionAndStageId($submissionId, $stageId = null, $userGroupId = null) {
		return $this->_getUsersByIds($submissionId, $stageId, $userGroupId);
	}

	/**
	 * Fetch a stageAssignment by symbolic info, building it if needed.
	 * @param $submissionId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @param $userId int
	 * @return StageAssignment
	 */
	function build($submissionId, $stageId, $userGroupId, $userId) {

		// If one exists, fetch and return.
		$stageAssignment = $this->_getByIds($submissionId, $stageId, $userGroupId, $userId);
		if ($stageAssignment) return $stageAssignment;

		// Otherwise, build one.
		unset($stageAssignment);
		$stageAssignment = $this->newDataObject();
		$stageAssignment->setSubmissionId($submissionId);
		$stageAssignment->setStageId($stageId);
		$stageAssignment->setUserGroupId($userGroupId);
		$stageAssignment->setUserId($userId);
		$this->insertObject($stageAssignment);
		return $stageAssignment;
	}

	/**
	 * Determine if a stageAssignment exists
	 * @param $submissionId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @param $userId int
	 * @return boolean
	 */
	function stageAssignmentExists($submissionId, $stageId, $userGroupId, $userId) {
		$stageAssignment = $this->_getByIds($submissionId, $stageId, $userGroupId, $userId);
		return ($stageAssignment)?true:false;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return StageAssignmentEntry
	 */
	function newDataObject() {
		return new StageAssignment();
	}

	/**
	 * Internal function to return an StageAssignment object from a row.
	 * @param $row array
	 * @return StageAssignment
	 */
	function _fromRow(&$row) {
		$stageAssignment = $this->newDataObject();

		$stageAssignment->setMonographId($row['submission_id']);
		$stageAssignment->setStageId($row['stage_id']);
		$stageAssignment->setUserId($row['user_id']);
		$stageAssignment->setUserGroupId($row['user_group_id']);
		$stageAssignment->setDateAssigned($row['date_assigned']);

		return $stageAssignment;
	}

	/**
	 * Insert a new StageAssignment.
	 * @param $stageAssignment StageAssignment
	 * @return bool
	 */
	function insertObject(&$stageAssignment) {
		return $this->update(
				sprintf('INSERT INTO stageAssignments
				(submission_id, stage_id, user_group_id, user_id, date_assigned)
				VALUES
				(?, ?, ?, ?, %s)',
				$this->datetimeToDB(Core::getCurrentDate())),
			array(
				$stageAssignment->getSubmisisonId(),
				$stageAssignment->getStageId(),
				$this->nullOrInt($stageAssignment->getUserGroupId()),
				$this->nullOrInt($stageAssignment->getUserId())
			)
		);
	}

	/**
	 * Delete a StageAssignment.
	 * @param $stageAssignment StageAssignment
	 * @return int
	 */
	function deleteObject($stageAssignment) {
		return $this->deleteByAll(
				$stageAssignment->getSubmissionId(),
				$stageAssignment->getStageId(),
				$stageAssignment->getUserGroupId(),
				$stageAssignment->getUserId()
			);
	}

	/**
	 * Delete a stageAssignment by matching on all fields.
	 * @param $submissionId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @param $userId int
	 * @return boolean
	 */
	function deleteByAll($submissionId, $stageId, $userGroupId, $userId) {
		return $this->update('DELETE FROM stage_assignments
					WHERE submissionId = ?
						AND stage_id = ?
						AND user_group_id = ?
						AND user_id = ?',
				array((int) $submissionId, (int) $stageId, (int) $userGroupId, (int) $userId));
	}

	/**
	 * Delete a stageAssignment by matching on all fields.
	 * @param $submissionId int
	 * @return boolean
	 */
	function deleteBySubmissionId($submissionId) {
		return $this->update('DELETE FROM stage_assignments
					WHERE submissionId = ?', (int) $submissionId);
	}

	/**
	 * Retrieve a set of users not assigned to a given submission stage as a user group
	 * @param $submissionId int
	 * @param $stageId int
	 * @param $userGroupId int
	 * @return object DAOResultFactory
	 */
	function getUsersNotAssignedToStage($submissionId, $stageId, $userGroupId) {
		$params = array((int) $submissionId, (int) $stageId, (int) $userGroupId);

		$result =& $this->retrieve(
			'SELECT u.*
			FROM users u
			LEFT JOIN user_user_groups uug ON (u.user_id = uug.user_id)
			LEFT JOIN stage_assignments s ON (s.user_id = uug.user_id AND s.submission_id = ? AND s.stage_id = ?)
			WHERE uug.user_group_id = ?
				AND s.user_group_id IS NULL',
			$params);

		$returner = null;
		if ($result->RecordCount() == 1 && count($params) == 3) {
			// If all parameters were specified, then seeking only one assignment.
			$returner =& $userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
			$result->Close();
		} elseif ($result->RecordCount() != 0) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			while (!$result->EOF) {
				$returner[] =& $userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
				$result->moveNext();
			}
			$result->Close();
			unset($result);
		}
		return $returner;
	}

	/**
	 * Retrieve a stageAssignment by submission and stage IDs.
	 * Private method that holds most of the work.
	 * serves two purposes: returns a single assignment or returns a factory,
	 * depending on the calling context.
	 * @param $submissionId int
	 * @param $stageId int optional
	 * @param $userGroupId int optional
	 * @param $userId int optional
	 * @return StageAssignment
	 */
	function _getByIds($submissionId, $stageId = null, $userGroupId = null, $userId = null) {
		$params = array((int) $submissionId);
		if (isset($stageId)) $params[] = (int) $stageId;
		if (isset($userGroupId)) $params[] = (int) $userGroupId;
		if (isset($userId)) $params[] = (int) $userId;

		$result =& $this->retrieve(
			'SELECT * FROM stage_assignments
			WHERE submission_id = ?' .
			(isset($stageId) ? ' AND stage_id = ?' : '') .
			(isset($userGroupId) ? ' AND user_group_id = ?' : '') .
			(isset($userId)?' AND user_id = ? ' : ''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() == 1 && count($params) == 4) {
			// If all parameters were specified, then seeking only one assignment.
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
			$result->Close();
		} elseif ($result->RecordCount() != 0) {
			// In any other case, return a list of all assignments
			while (!$result->EOF) {
				$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
				$result->moveNext();
			}
			$result->Close();
			unset($result);
		}
		return $returner;
	}

	/**
	 * Retrieve a user by submission and stage IDs.
	 * Private method because it serves two purposes: returns a single assignment
	 * or returns a factory, depending on the calling context.
	 * @param $submissionId int
	 * @param $stageId int optional
	 * @param $userGroupId int optional
	 * @param $userId int optional
	 * @return object DAOResultFactory
	 */
	function _getUsersByIds($submissionId, $stageId = null, $userGroupId = null, $userId = null) {
		$params = array((int) $submissionId);
		if (isset($stageId)) $params[] = (int) $stageId;
		if (isset($userGroupId)) $params[] = (int) $userGroupId;
		if (isset($userId)) $params[] = (int) $userId;

		$result =& $this->retrieve(
			'SELECT u.*
			FROM stage_assignments s
			INNER JOIN users u ON (u.user_id = s.user_id)
			WHERE submission_id = ?' .
			(isset($stageId) ? ' AND stage_id = ?' : '') .
			(isset($userGroupId) ? ' AND user_group_id = ?':'') .
			(isset($userId)?' AND user_id = ? ' : ''),
			$params);

		$returner = null;
		if ($result->RecordCount() == 1 && count($params) == 4) {
			// If all parameters were specified, then seeking only one assignment.
			$returner =& $userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
			$result->Close();
		} elseif ($result->RecordCount() != 0) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			while (!$result->EOF) {
				$returner[] =& $userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
				$result->moveNext();
			}
			$result->Close();
			unset($result);
		}
		return $returner;
	}
}

?>
