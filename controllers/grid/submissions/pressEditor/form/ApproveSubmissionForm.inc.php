<?php

/**
 * @file controllers/grid/submissions/pressEditor/form/ApproveSubmissionForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApproveSubmissionForm
 * @ingroup controllers_grid_submissions
 *
 * @brief Form for approving a submission
 */

import('lib.pkp.classes.form.Form');

class ApproveSubmissionForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ApproveSubmissionForm($monographId) {
		parent::Form('controllers/grid/submissions/pressEditor/approveAndReview.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);

		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('personalMessage', 'selectedFiles'));
	}

	/**
	 * Save submissionContributor
	 */
	function execute(&$args, &$request) {
		// Fixme: How do we handle assigning other editors?
		$user =& $request->getUser();
		
		// 1. Accept review
		import('classes.submission.editor.EditorAction');
		EditorAction::assignEditor($this->_monographId, $user->getId());

		// FIXME: 2. Get selected files and put in DB somehow
		
		
		// 3. Send Personal message to author
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($editorSubmission, 'EDITOR_APPROVE');
		if ($email->isEnabled()) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($this->_monographId);
			
			$authorDao =& DAORegistry::getDAO('AuthorDAO');
			$authors = $authorDao->getAuthorsByMonographId($this->_monographId);
	
			while($author =& $authors->next()) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
				unset($author);
			}			

			$paramArray = array(
				'authorName' => $monograph->getAuthorString(),
				'personalNote' => $this->getData('personalNote'),
				'editorialContactSignature' => $user->getContactSignature()
			);
			$email->send();
		}
	}
}

?>
