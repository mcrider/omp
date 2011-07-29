<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author monograph submission.
 */


import('classes.submission.form.SubmissionSubmitForm');

class SubmissionSubmitStep3Form extends SubmissionSubmitForm {

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($press, $monograph) {
		parent::SubmissionSubmitForm($press, $monograph, 3);

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'submission.submit.form.titleRequired'));
		// Validates that at least one author has been added (note that authors are in grid, so Form does not
		// directly see the authors value (there is no "authors" input. Hence the $ignore parameter.
		$this->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'submission.submit.form.authorRequired',
						// The first parameter is ignored. This
						create_function('$ignore, $monograph', 'return count($monograph->getAuthors()) > 0;'),
							array($monograph)));
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		if (isset($this->monograph)) {
			$monograph =& $this->monograph;
			$this->_data = array(
				'title' => $monograph->getTitle(null), // Localized
				'abstract' => $monograph->getAbstract(null), // Localized
				'discipline' => $monograph->getDiscipline(null), // Localized
				'subjectClass' => $monograph->getSubjectClass(null), // Localized
				'subject' => $monograph->getSubject(null), // Localized
				'coverageGeo' => $monograph->getCoverageGeo(null), // Localized
				'coverageChron' => $monograph->getCoverageChron(null), // Localized
				'coverageSample' => $monograph->getCoverageSample(null), // Localized
				'type' => $monograph->getType(null), // Localized
				'language' => $monograph->getLanguage(),
				'sponsor' => $monograph->getSponsor(null), // Localized
				'series' => $seriesDao->getById($monograph->getSeriesId()),
				'citations' => $monograph->getCitations()
			);

		}
		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'title',
				'abstract',
				'disciplinesKeywords',
				'keywordKeywords',
				'agenciesKeywords',
			)
		);

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getById($this->monograph->getSeriesId(), $this->monograph->getPressId());
	}

	/**
	 * Display the form
	 */
	function display($request) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditedVolume', $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME);

		return parent::display($request);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abstract');
	}

	/**
	 * Save changes to monograph.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the monograph ID
	 */
	function execute($args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Update monograph
		$monograph =& $this->monograph;
		$monograph->setTitle($this->getData('title'), null); // Localized
		$monograph->setAbstract($this->getData('abstract'), null); // Localized

		if(is_array($this->getData('agenciesKeywords'))) $monograph->setSupportingAgencies(implode(", ", $this->getData('agenciesKeywords')), null); // Localized
		if(is_array($this->getData('disciplinesKeywords'))) $monograph->setDiscipline(implode(", ", $this->getData('disciplinesKeywords')), null); // Localized
		if(is_array($this->getData('keywordKeywords'))) $monograph->setSubject(implode(", ",$this->getData('keywordKeywords')), null); // Localized

		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->setDateSubmitted(Core::getCurrentDate());
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress(0);
		}

		// Assign the default users to the submission workflow stage
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->assignDefaultStageParticipants($monograph, WORKFLOW_STAGE_ID_SUBMISSION);

		// Save the monograph
		$monographDao->updateMonograph($monograph);

		//
		// Send a notification to associated users
		//

		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */

		// Get the managers and editors.
		$pressManagers = $roleDao->getUsersByRoleId(ROLE_ID_PRESS_MANAGER);
		$editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR);

		$pressManagersArray = $pressManagers->toAssociativeArray();
		$editorsArray = $editors->toAssociativeArray();

		$allUsers = array_unique(array_merge(
									 array_keys($pressManagersArray),
									 array_keys($editorsArray)
								 ));

		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		foreach ($allUsers as $userId) {
			$notification = new Notification();
			$notification->setUserId($userId);
			$notification->setType(NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED);
			$notification->setContextId((int) $monograph->getPressId());
			$notification->setLevel(NOTIFICATION_LEVEL_NORMAL);
			$notification->setAssocType(ASSOC_TYPE_MONOGRAPH);
			$notification->setAssocId((int) $monograph->getId());

			$notificationDao->insertNotification($notification);
			unset($notification);
		}

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK', null, null, null, false);
		$press =& $request->getPress();

		$router =& $request->getRouter();
		if ($mail->isEnabled()) {
			$user = $monograph->getUser();
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			$mail->bccAssignedEditors($monograph->getId());
			$mail->bccAssignedSeriesEditors($monograph->getId());

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'index', $monograph->getId())
			));
			$mail->send($request);
		}

		// Resequence the authors (this ensures a primary contact).
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authorDao->resequenceAuthors($monograph->getId());

		return $this->monographId;
	}
}

?>
