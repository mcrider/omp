<?php

/**
 * @file classes/submission/editor/EditorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorAction
 * @ingroup submission
 *
 * @brief EditorAction class.
 */

// $Id$


import('classes.submission.seriesEditor.SeriesEditorAction');

class EditorAction extends SeriesEditorAction {
	/**
	 * Actions.
	 */

	/**
	 * Assigns an series editor to a submission.
	 * @param $monographId int
	 * @param $seriesEditorId int
	 * @return boolean true iff ready for redirect
	 */
	function assignEditor($monographId, $seriesEditorId, $isEditor = false) {
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$user =& Request::getUser();
		$press =& Request::getPress();

		$editorSubmission =& $editorSubmissionDao->getByMonographId($monographId);
		$seriesEditor =& $userDao->getUser($seriesEditorId);
		if (!isset($seriesEditor)) return true;

		if ($user->getId() === $seriesEditorId) {
			$editAssignment = new EditAssignment();
			$editAssignment->setMonographId($monographId);
			$editAssignment->setCanEdit(1);
			$editAssignment->setCanReview(1);

			// Make the selected editor the new editor
			$editAssignment->setEditorId($seriesEditorId);
			$editAssignment->setDateNotified(Core::getCurrentDate());
			$editAssignment->setDateUnderway(null);

			$editAssignments =& $editorSubmission->getEditAssignments();
			array_push($editAssignments, $editAssignment);
			$editorSubmission->setEditAssignments($editAssignments);

			$editorSubmissionDao->updateObject($editorSubmission);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, MONOGRAPH_LOG_EDITOR_ASSIGN, MONOGRAPH_LOG_TYPE_EDITOR, $seriesEditorId, 'log.editor.editorAssigned', array('editorName' => $seriesEditor->getFullName(), 'monographId' => $monographId));
			return true;
		} 
	}

}

?>
