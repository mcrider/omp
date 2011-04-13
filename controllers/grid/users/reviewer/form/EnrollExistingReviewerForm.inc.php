<?php

/**
 * @file controllers/grid/users/reviewer/form/EnrollExistingReviewerForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EnrollExistingReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for enrolling an existing reviewer and adding them to a submission.
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class EnrollExistingReviewerForm extends ReviewerForm {
	/**
	 * Constructor.
	 */
	function EnrollExistingReviewerForm($monograph, $reviewAssignmentId) {
		parent::ReviewerForm('controllers/grid/users/reviewer/form/enrollExistingReviewerForm.tpl', $monograph, $reviewAssignmentId);

		$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'user.profile.form.usergroupRequired'));
	}


	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		$this->readUserVars(array('userId', 'userGroupId'));
	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($this->getMonographId());
		$press =& $request->getPress();

		// FIXME: Bug #6199
		$reviewType = $this->getData('reviewType');
		$round = $this->getData('round');
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');

		$selectionType = (int) $this->getData('selectionType');
		if($selectionType == REVIEWER_SELECT_CREATE) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user = new User();

			$user->setFirstName($this->getData('firstname'));
			$user->setMiddleName($this->getData('middlename'));
			$user->setLastName($this->getData('lastname'));
			$user->setEmail($this->getData('email'));

			$authDao =& DAORegistry::getDAO('AuthSourceDAO');
			$auth =& $authDao->getDefaultPlugin();
			$user->setAuthId($auth?$auth->getAuthId():0);

			$user->setUsername($this->getData('username'));
			$password = Validation::generatePassword();

			if (isset($auth)) {
				$user->setPassword($password);
				// FIXME Check result and handle failures
				$auth->doCreateUser($user);
				$user->setAuthId($auth->authId);
				$user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword())); // Used for PW reset hash only
			} else {
				$user->setPassword(Validation::encryptCredentials($this->getData('username'), $password));
			}

			$user->setDateRegistered(Core::getCurrentDate());
			$reviewerId = $userDao->insertUser($user);

			// Add reviewing interests to interests table
			import('lib.pkp.classes.user.InterestManager');
			$interestManager = new InterestManager();
			$interestManager->insertInterests($userId, $this->getData('interestsKeywords'), $this->getData('interests'));

			// Assign the selected user group ID to the user
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$userGroupId = (int) $this->getData('userGroupId');
			$userGroupDao->assignUserToGroup($reviewerId, $userGroupId);

			if ($this->getData('sendNotify')) {
				// Send welcome email to user
				import('classes.mail.MailTemplate');
				$mail = new MailTemplate('REVIEWER_REGISTER');
				$mail->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
				$mail->assignParams(array('username' => $this->getData('username'), 'password' => $password, 'userFullName' => $user->getFullName()));
				$mail->addRecipient($user->getEmail(), $user->getFullName());
				$mail->send();
			}
		} elseif($selectionType == REVIEWER_SELECT_ENROLL) {
			// Assign a reviewer user group to an existing non-reviewer
			$userId = $this->getData('userId');
			$userGroupId = $this->getData('userGroupId');

			$userGroupId = $this->getData('userGroupId');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$userGroupDao->assignUserToGroup($userId, $userGroupId);
			// Set the reviewerId to the userId to return to the grid
			$reviewerId = $userId;
		} else {
			$reviewerId = $this->getData('reviewerId');
		}

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->addReviewer($submission, $reviewerId, $reviewType, $round, $reviewDueDate, $responseDueDate);

		// Get the reviewAssignment object now that it has been added
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($submission->getId(), $reviewerId, $round, $reviewType);
		$reviewAssignment->setDateNotified(Core::getCurrentDate());
		$reviewAssignment->setCancelled(0);
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);

		// Update the review round status if this is the first reviewer added
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentReviewRound =& $reviewRoundDao->build($this->getMonographId(), $submission->getCurrentReviewType(), $submission->getCurrentRound());
		if ($currentReviewRound->getStatus() == REVIEW_ROUND_STATUS_PENDING_REVIEWERS) {
			$currentReviewRound->setStatus(REVIEW_ROUND_STATUS_PENDING_REVIEWS);
			$reviewRoundDao->updateObject($currentReviewRound);
		}

		return $reviewAssignment;
	}
}

?>
