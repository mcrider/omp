<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorAction
 * @ingroup submission
 *
 * @brief SeriesEditorAction class.
 */



import('classes.submission.common.Action');

class SeriesEditorAction extends Action {

	/**
	 * Constructor.
	 */
	function SeriesEditorAction() {
		parent::Action();
	}

	//
	// Actions.
	//
	/**
	 * Records an editor's submission decision.
	 * @param $request PKPRequest
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision integer
	 */
	function recordDecision($request, $seriesEditorSubmission, $decision) {
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');

		$editorAssigned = $stageAssignmentDao->editorAssignedToSubmission(
			$seriesEditorSubmission->getId(),
			$seriesEditorSubmission->getStageId()
		);

		if ( !$editorAssigned ) return;

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& $request->getUser();
		$editorDecision = array(
			'editDecisionId' => null,
			'editorId' => $user->getId(),
			'decision' => $decision,
			'dateDecided' => date(Core::getCurrentDate())
		);

		if (!HookRegistry::call('SeriesEditorAction::recordDecision', array(&$seriesEditorSubmission, $editorDecision))) {
			$seriesEditorSubmission->setStatus(STATUS_QUEUED);
			$seriesEditorSubmission->stampStatusModified();
			$seriesEditorSubmission->addDecision(
				$editorDecision,
				$seriesEditorSubmission->getStageId(),
				$seriesEditorSubmission->getCurrentRound()
			);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// Add log.
			$decisions = SeriesEditorSubmission::getEditorDecisionOptions();
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR));
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_EDITOR_DECISION, 'log.editor.decision', array('editorName' => $user->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'decision' => Locale::translate($decisions[$decision])));
		}
	}

	/**
	 * Assign the default participants to a workflow stage.
	 * @param $monograph Monograph
	 * @param $stageId int
	 */
	function assignDefaultStageParticipants(&$monograph, $stageId) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		// Managerial roles are skipped -- They have access by default and
		//  are assigned for informational purposes only

		// Series editor roles are skipped -- They are assigned by PM roles
		//  or by other series editors

		// Press roles -- For each press role user group assigned to this
		//  stage in setup, iff there is only one user for the group,
		//  automatically assign the user to the stage
		// But skip authors and reviewers, since these are very monograph specific
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$submissionStageGroups =& $userGroupDao->getUserGroupsByStage($monograph->getPressId(), $stageId, true, true);
		while ($userGroup =& $submissionStageGroups->next()) {
			$users =& $userGroupDao->getUsersById($userGroup->getId());
			if($users->getCount() == 1) {
				$user =& $users->next();
				$stageAssignmentDao->build($monograph->getId(), $stageId, $userGroup->getId(), $user->getId());
			}
		}

		// Author roles -- Assign only the submitter
		// FIXME #6001: Should the author groups be assigned here as well? As which user group?

		// Reviewer roles -- Do nothing. Reviewers are not included in the stage participant list, they
		// are administered via review assignments.
	}

	/**
	 * Increment a monograph's workflow stage.
	 * @param $monograph Monograph
	 * @param $newStage integer One of the WORKFLOW_STAGE_* constants.
	 */
	function incrementWorkflowStage(&$monograph, $newStage) {
		// Change the monograph's workflow stage.
		$monograph->setStageId($newStage);
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monographDao->updateMonograph($monograph);

		// Assign the default users to the next workflow stage.
		$this->assignDefaultStageParticipants($monograph, $newStage);
	}

	/**
	 * Assigns a reviewer to a submission.
	 * @param $request PKPRequest
	 * @param $seriesEditorSubmission object
	 * @param $reviewerId int
	 * @param $stageId int
	 * @param $round int optional
	 * @param $reviewDueDate datetime optional
	 * @param $responseDueDate datetime optional
	 */
	function addReviewer($request, $seriesEditorSubmission, $reviewerId, $stageId, $round = null, $reviewDueDate = null, $responseDueDate = null) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $request->getUser();

		$reviewer =& $userDao->getUser($reviewerId);

		// Check to see if the requested reviewer is not already
		// assigned to review this monograph.
		if ($round == null) {
			$round = $seriesEditorSubmission->getCurrentRound();
		}
		$assigned = $seriesEditorSubmissionDao->reviewerExists($seriesEditorSubmission->getId(), $reviewerId, $stageId, $round);

		// Only add the reviewer if he has not already
		// been assigned to review this monograph.
		if (!$assigned && isset($reviewer) && !HookRegistry::call('SeriesEditorAction::addReviewer', array(&$seriesEditorSubmission, $reviewerId))) {
			$reviewAssignment = new ReviewAssignment();
			$reviewAssignment->setSubmissionId($seriesEditorSubmission->getId());
			$reviewAssignment->setReviewerId($reviewerId);
			$reviewAssignment->setDateAssigned(Core::getCurrentDate());
			$reviewAssignment->setStageId($stageId);
			$reviewAssignment->setRound($round);

			$reviewAssignmentDao->insertObject($reviewAssignment);

			// Assign review form automatically if needed
			$pressId = $seriesEditorSubmission->getPressId();
			$seriesDao =& DAORegistry::getDAO('SeriesDAO');
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

			$submissionId = $seriesEditorSubmission->getId();
			$series =& $seriesDao->getById($submissionId, $pressId);

			$seriesEditorSubmission->addReviewAssignment($reviewAssignment, $stageId, $round);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			$reviewAssignment = $reviewAssignmentDao->getReviewAssignment(
				$seriesEditorSubmission->getId(),
				$reviewerId,
				$round,
				$stageId
			);

			$press =& $request->getPress();
			$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$settings =& $settingsDao->getPressSettings($press->getId());
			if (isset($reviewDueDate)) $this->setDueDate($request, $seriesEditorSubmission, $reviewAssignment->getId(), $reviewDueDate);
			if (isset($responseDueDate)) $this->setResponseDueDate($seriesEditorSubmission->getId(), $reviewAssignment->getId(), $responseDueDate);

			// Add log
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_REVIEW_ASSIGN, 'log.review.reviewerAssigned', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'stageId' => $stageId, 'round' => $round));
		}
	}

	/**
	 * Clears a review assignment from a submission.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 */
	function clearReview($request, $submissionId, $reviewId) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($submissionId);
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId() && !HookRegistry::call('SeriesEditorAction::clearReview', array(&$seriesEditorSubmission, $reviewAssignment))) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
			if (!isset($reviewer)) return false;
			$seriesEditorSubmission->removeReviewAssignment($reviewId);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// FIXME: Need to change the state of the current review round back to "pending reviewer" when
			// the last assignment was removed, see #6401.

			// Add log
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_REVIEW_CLEAR, 'log.review.reviewCleared', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'stageId' => $reviewAssignment->getStageId(), 'round' => $reviewAssignment->getRound()));

			return true;
		} else return false;
	}

	/**
	 * Sets the due date for a review assignment.
	 * @param $request PKPRequest
	 * @param $monograph Object
	 * @param $reviewId int
	 * @param $dueDate string
	 * @param $numWeeks int
	 * @param $logEntry boolean
	 */
	function setDueDate($request, $monograph, $reviewId, $dueDate = null, $numWeeks = null, $logEntry = false) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monograph->getId() && !HookRegistry::call('SeriesEditorAction::setDueDate', array(&$reviewAssignment, &$reviewer, &$dueDate, &$numWeeks))) {
			$today = getDate();
			$todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
			if ($dueDate != null) {
				$dueDateParts = explode('-', $dueDate);

				// Ensure that the specified due date is today or after today's date.
				if ($todayTimestamp <= strtotime($dueDate)) {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[1], $dueDateParts[2], $dueDateParts[0])));
				} else {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $todayTimestamp));
				}
			} else {
				// Add the equivilant of $numWeeks weeks, measured in seconds, to $todaysTimestamp.
				$numWeeks = max((int) $numWeeks, 2);
				$newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
				$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $newDueDateTimestamp));
			}

			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);

			if ($logEntry) {
				// Add log
				import('classes.log.MonographLog');
				import('classes.log.MonographEventLogEntry');
				MonographLog::logEvent(
					$request,
					$monograph,
					MONOGRAPH_LOG_REVIEW_SET_DUE_DATE,
					'log.review.reviewDueDateSet',
					array(
						'reviewerName' => $reviewer->getFullName(),
						'dueDate' => strftime(
							Config::getVar('general', 'date_format_short'),
							strtotime($reviewAssignment->getDateDue())
						),
						'monographId' => $monograph->getId(),
						'stageId' => $reviewAssignment->getStageId(),
						'round' => $reviewAssignment->getRound()
					)
				);
			}
		}
	}

	/**
	 * Sets the due date for a reviewer to respond to a review request.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $dueDate string
	 */
	function setResponseDueDate($monographId, $reviewId, $dueDate = null, $numWeeks = null) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monographId && !HookRegistry::call('SeriesEditorAction::setResponseDueDate', array(&$reviewAssignment, &$reviewer, &$dueDate, &$numWeeks))) {
			$today = getDate();
			$todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
			if ($dueDate != null) {
				$dueDateParts = explode('-', $dueDate);

				// Ensure that the specified due date is today or after today's date.
				if ($todayTimestamp <= strtotime($dueDate)) {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[1], $dueDateParts[2], $dueDateParts[0])));
				} else {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $todayTimestamp));
				}
			} else {
				// Add the equivilant of $numWeeks weeks, measured in seconds, to $todaysTimestamp.
				$numWeeks = max((int) $numWeeks, 2);
				$newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
				$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $newDueDateTimestamp));
			}

			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);
		}
	}

	/**
	 * Get the text of all peer reviews for a submission
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @return string
	 */
	function getPeerReviews($seriesEditorSubmission) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION));

		$body = '';
		$textSeparator = "------------------------------------------------------";
		foreach ($reviewAssignments as $reviewAssignment) {
			// If the reviewer has completed the assignment, then import the review.
			if ($reviewAssignment->getDateCompleted() != null && !$reviewAssignment->getCancelled()) {
				// Get the comments associated with this review assignment
				$monographComments =& $monographCommentDao->getMonographComments($seriesEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $reviewAssignment->getId());

				if($monographComments) {
					$body .= "\n\n$textSeparator\n";
					$body .= Locale::translate('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n";
					if (is_array($monographComments)) {
						foreach ($monographComments as $comment) {
							// If the comment is viewable by the author, then add the comment.
							if ($comment->getViewable()) {
								$body .= String::html2text($comment->getComments()) . "\n\n";
							}
						}
					}
					$body .= "$textSeparator\n\n";
				}
				if ($reviewFormId = $reviewAssignment->getReviewFormId()) {
					$reviewId = $reviewAssignment->getId();


					$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
					if(!$monographComments) {
						$body .= "$textSeparator\n";

						$body .= Locale::translate('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n\n";
					}
					foreach ($reviewFormElements as $reviewFormElement) {
						$body .= String::html2text($reviewFormElement->getLocalizedQuestion()) . ": \n";
						$reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());

						if ($reviewFormResponse) {
							$possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
							if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes())) {
								if ($reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES) {
									foreach ($reviewFormResponse->getValue() as $value) {
										$body .= "\t" . String::htmltext($possibleResponses[$value-1]['content']) . "\n";
									}
								} else {
									$body .= "\t" . String::html2text($possibleResponses[$reviewFormResponse->getValue()-1]['content']) . "\n";
								}
								$body .= "\n";
							} else {
								$body .= "\t" . String::html2text($reviewFormResponse->getValue()) . "\n\n";
							}
						}

					}
					$body .= "$textSeparator\n\n";

				}


			}
		}

		return $body;
	}
}

?>
