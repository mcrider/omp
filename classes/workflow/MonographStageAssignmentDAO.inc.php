<?php

/**
 * @file classes/workflow/MonographStageAssignmentDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographStageAssignmentDAO
 * @ingroup workflow
 *
 * @brief Class for managing user to publication stage assignments
 */

import('classes.workflow.MonographStageAssignment');

class MonographStageAssignmentDAO extends DAO {

	/**
	 * Get a monograph stage assignment by its ID
	 * @param $monographId int
	 * @param $stageId int Gets the current stage if null
 	 */
	function &getById($id) {
		$result =& $this->retrieve('SELECT * FROM monograph_stage_assignments msa WHERE msa.monograph_stage_assignment_id = ?', $id);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get all user's assignments of a particular monograph's stage
	 * @param $monographId int
	 * @param $stageId int Gets the current stage if null
 	 */
	function &getByMonographId($monographId, $stageId = null, $userGroupId = null) {
		$sql = 'SELECT * FROM monograph_stage_assignments msa WHERE msa.publication_stage_id = ? AND msa.monograph_id = ?';
		if (!$stageId) {
			$stageId = $this->getCurrentStageIdByMonographId($monographId);
		}
		$params = array($stageId, $monographId);

		if ($userGroupId) {
			$sql .= ' AND user_group_id = ?';
			$params[] = $userGroupId;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the monograph's current workflow stage ID (defined in Application)
	 * @param $monographId int
	 */
	function getCurrentStageIdByMonographId($monographId) {
		if ($monographId === null) {
			$returner = null;
			return $returner;
		}

		$result =& $this->retrieve(
			'SELECT MAX(publication_stage_id) AS current_stage FROM monograph_stage_assignments a WHERE monograph_id = ?',
			$monographId
		);

		if ($result->RecordCount() == 0) {
			$returner = null;
		} else {
			$row = $result->FetchRow();
			$returner = $row['current_stage'];
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get all users assigned to a particular workflow stage
	 * @param $monographId int
	 * @param $stageId int optional
	 * @param $userGroupId int optional
 	 */
	function &getUsersByMonographId($monographId, $stageId = null, $userGroupId = null) {
		$sql = 'SELECT u.* FROM users u, monograph_stage_assignments msa WHERE u.user_id = msa.user_id AND msa.monograph_id = ?';
		$params = array($monographId);

		if ($stageId) {
			$sql .= ' AND stage_id = ?';
			$params[] = $stageId;
		}

		if ($userGroupId) {
			$sql .= ' AND user_group_id = ?';
			$params[] = $userGroupId;
		}

		$result =& $this->retrieve($sql, $params);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$returner = new DAOResultFactory($result, $userDao, '_returnUserFromRow');
		return $returner;
	}

	/**
	 * Add a user to a workflow stage
	 * @param $monographId int
	 * @param $userId
	 * @param $stageId int
	 */
	function assignUserToStage($monographId, $stageId, $userId, $userGroupId) {
		return $this->update('INSERT INTO monograph_stage_assignments (monograph_id, publication_stage_id, user_id, user_group_id)
							  VALUES (?, ?, ?, ?)',
							  array($monographId, $stageId, $userId, $userGroupId));
	}

	/**
	 * Check if user is assigned to a monograph stage
	 * @param int $monographId
	 * @param int $stageId
	 * @param int $userId
	 * @param int $userGroupId optional
	 * @return boolean
	 */
	function assignmentExists($monographId, $stageId, $userId, $userGroupId = null) {
		$sql = 'SELECT COUNT(*) FROM monograph_stage_assignments WHERE monograph_id = ? AND publication_stage_id = ? AND user_id = ?';
		$params = array($monographId, $stageId, $userId);

		if ($userGroupId) {
			$sql .= ' AND user_group_id = ?';
			$params[] = $userGroupId;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Remove a user from a workflow stage
	 * @param $monographId int
	 * @param $userId
	 * @param $stageId int
	 */
	function removeUserFromStage($monographId, $userId, $stageId) {
		return $this->update('DELETE FROM monograph_stage_assignments
							  WHERE monograph_id = ? AND user_id = ? AND publication_stage_id = ?',
							  array($monographId, $userId, $stageId));
	}

	/**
	 * Delete a specific monograph stage assignment
	 * @param $id int
	 */
	function deleteById($id) {
		return $this->update('DELETE FROM monograph_stage_assignments
							  WHERE monograph_stage_assignment_id = ?',
							  array($id));
	}

	/**
	 * Delete all stage assignments for a monograph
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		return $this->update('DELETE FROM monograph_stage_assignments
							  WHERE monograph_id = ?',
							  array($monographId));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return MonographStageAssignment
	 */
	 function &newDataObject() {
		return new MonographStageAssignment();
	}

	/**
	 * Insert a new MonographStageAssignment.
	 * @param $monographStageAssignment MonographStageAssignment
	 */
	function insertObject(&$monographStageAssignment) {
		$this->update(
			'INSERT INTO monograph_stage_assignments
				(monograph_id, publication_stage_id, user_id, user_group_id)
				VALUES
				(?, ?, ?, ?)',
			array(
				$monographStageAssignment->getMonographId(),
				$monographStageAssignment->getPublicationStageId(),
				$monographStageAssignment->getUserId(),
				$monographStageAssignment->getUserGroupId(),
			)
		);
		$monographStageAssignment->setId($this->getInsertId('monograph_stage_assignments', 'monograph_stage_assignment_id'));

		return $monographStageAssignment->getId();
	}

	/**
	 * Update an existing MonographGalley.
	 * @param $galley MonographGalley
	 */
	function updateObject(&$monographStageAssignment) {
		return $this->update(
			'UPDATE monograph_stage_assignments
				SET
					monograph_id = ?,
					publication_stage_id = ?,
					user_id = ?,
					user_group_id = ?
				WHERE monograph_stage_assignment_id = ?',
			array(
				$monographStageAssignment->getMonographId(),
				$monographStageAssignment->getPublicationStageId(),
				$monographStageAssignment->getUserId(),
				$monographStageAssignment->getUserGroupId(),
				$monographStageAssignment->getMonographStageAssignmentId()
			)
		);
	}

	/**
	 * Internal function to return a MonographStageAssignment object from a row.
	 * @param $row array
	 * @return MonographStageAssignment
	 */
	function &_fromRow(&$row) {
		$monographStageAssignment =& $this->newDataObject();
		$monographStageAssignment->setAssignmentId($row['monograph_stage_assignment_id']);
		$monographStageAssignment->setMonographId($row['monograph_id']);
		$monographStageAssignment->setUserId($row['user_id']);
		$monographStageAssignment->setRoleId($row['user_group_id']);
		$monographStageAssignment->setStageId($row['publication_stage_id']);
		return $monographStageAssignment;
	}
}

?>
