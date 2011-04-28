<?php

/**
 * @defgroup manager
 */

/**
 * @file classes/manager/MonographParticipantManager.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographParticipantManager
 * @ingroup monograph
 *
 * @brief Class for managing users associated with a monograph, including:
 * 		StageAssignments, ReviewAssignments, Authors, and Signoffs.
 */


class MonographParticipantManager {

	/**
	 * Get Users assigned to this monograph (for a given stage, if specified).
	 * @param $monographId int
	 * @param $stage int (optional)
	 * @return array (array of userGroupId => array(userIds))
	 */
	function getParticipantUsersByMonographId($monographId, $stageId = null) {
		$userIds = array();

		// Get all the stage assignments
		$stageAssignmentDAO =& DAORegistry::getDAO('StageAssignmentDAO');
		$users =& $stageAssignmentDAO->getUsersBySubmissionAndStageId($monographId, $stageId);
		while ($user =& $users->next()) {
//			$userIds[$stageAssignment->getUserGroupId()][] = $stageAssignment->getUserId();
			unset($user);
		}

		// Get all the Signoffs
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffFactory =& $signoffDao->getAllBySymbolic(
			'SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null
		);


		// Get all the Reviewers
		// How do we determine the UserGroupId?

		// Get all the Authors

		$userDao =& DAORegistry::getDAO('UserDAO');

	}
}

?>
