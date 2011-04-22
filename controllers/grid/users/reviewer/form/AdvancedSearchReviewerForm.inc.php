<?php

/**
 * @file controllers/grid/users/reviewer/form/AdvancedSearchReviewerForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedSearchReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for an advanced search and for adding a reviewer to a submission.
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class AdvancedSearchReviewerForm extends ReviewerForm {
	/**
	 * Constructor.
	 */
	function AdvancedSearchReviewerForm($monograph, $reviewAssignmentId) {
		parent::ReviewerForm($monograph, $reviewAssignmentId);
		$this->setTemplate('controllers/grid/users/reviewer/form/advancedSearchReviewerForm.tpl');

		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'editor.review.mustSelect'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		$this->readUserVars(array('reviewerId'));
	}
}

?>
