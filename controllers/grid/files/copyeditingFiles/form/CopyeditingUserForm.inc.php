<?php

/**
 * @file controllers/grid/files/finalDraftFiles/form/ManageFinalDraftFilesForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFinalDraftFilesForm
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.classes.form.Form');

class CopyeditingUserForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function CopyeditingUserForm($monographId) {
		parent::Form('controllers/grid/files/copyediting/addCopyeditingUser.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorLocale($this, 'userId', 'required', 'editor.monograph.copyediting.form.userRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'selected-listbuilder-files-copyeditingfileslistbuilder', 'required', 'editor.monograph.copyediting.form.fileRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'personalMessage', 'required', 'editor.monograph.copyediting.form.messageRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//
	/**
	 * Initialize variables
	 */
	function initData(&$args, &$request) {
		$this->setData('monographId', $this->_monographId);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('userId', 'selected-listbuilder-files-copyeditingfileslistbuilder', 'personalMessage'));
	}

	/**
	 * Assign user to copyedit the selected files
	 */
	function execute() {
		$monographId = $this->_monographId;
		$userId = $this->getData('userId');

		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$userDao =& DAORegistry::getDAO('UserDAO'); /* @var $monographDao MonographDAO */
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */

		$monograph =& $monographDao->getMonograph($monographId);
		if($this->getData('selected-listbuilder-files-copyeditingfileslistbuilder')) {
			$selectedFiles = $this->getData('selected-listbuilder-files-copyeditingfileslistbuilder');
		} else {
			$selectedFiles = array();
		}

		// Build copyediting signoff for each file
		foreach ($selectedFiles as $selectedFileId) {
			$signoffDao->build('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, $selectedFileId, $userId);
		}

		// Send the message to the user
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, $emailKey, null, true);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->send();
	}
}

?>
