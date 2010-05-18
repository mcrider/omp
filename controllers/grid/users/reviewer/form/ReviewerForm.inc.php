<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewerForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerForm
 * @ingroup controllers_grid_reviewer__form
 *
 * @brief Form for adding a reviewer to a submission
 */

import('lib.pkp.classes.form.Form');

class ReviewerForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;
	
	/** The reviewer associated with the review assignment **/
	var $_reviewerId;

	/**
	 * Constructor.
	 */
	function ReviewerForm($monographId, $reviewerId) {
		parent::Form('controllers/grid/users/reviewer/form/reviewerForm.tpl');
		$this->_monographId = (int) $monographId;
		$this->_reviewerId = (int) $reviewerId;
		
		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'author.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'responseDueDate', 'required', 'author.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'reviewDueDate', 'required', 'author.submit.form.authorRequiredFields'));
		
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the MonographId
	 * @return int monographId
	 */
	function getMonographId() {
		return $this->_monographId;
	}	
	
	/**
	 * Get the ReviewerId
	 * @return int reviewerId
	 */
	function getReviewerId() {
		return $this->_reviewerId;
	}

	//
	// Template methods from Form
	//
	/**
	* Initialize form data from the associated submissionContributor.
	* @param $submissionContributor Reviewer
	*/
	function initData() {

		// FIXME: Set date values to defaults
		$this->_data = array(
			'responseDueDate' => '',
			'reviewDueDate' => '',
			);
		
	}

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('monographId', $this->getMonographId());

		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('reviewerId',
								'personalMessage',
								'responseDueDate',
								'reviewDueDate'));
	}

	/**
	 * Save review assignment
	 */
	function execute() {
		// FIXME: Create a review assignment
		
	}
}

?>
