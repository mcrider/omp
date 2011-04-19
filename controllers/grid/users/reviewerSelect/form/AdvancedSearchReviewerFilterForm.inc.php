<?php

/**
 * @file controllers/grid/users/reviewer/form/AdvancedSearchReviewerFilterForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedSearchReviewerFilterForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for an advanced search and for adding a reviewer to a submission.
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class AdvancedSearchReviewerFilterForm extends ReviewerForm {
	/**
	 * Constructor.
	 */
	function AdvancedSearchReviewerFilterForm($monograph, $reviewAssignmentId) {
		parent::ReviewerForm($monograph, $reviewAssignmentId);
		$this->setTemplate('controllers/grid/users/reviewer/form/advancedSearchReviewerFilterForm.tpl');

		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'editor.review.mustSelect'));
	}

	/**
	 * @see ReviewerForm::initData()
	 */
	function initData($args, &$request) {
		$monograph =& $this->getMonograph();

		$interestDao =& DAORegistry::getDAO('InterestDAO');
		$this->setData('existingInterests', $interestDao->getAllUniqueInterests());

		$seriesEditorSubmissionDAO =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewerValues = $seriesEditorSubmissionDAO->getAnonymousReviewerStatistics();
		$this->setData('reviewerValues', $reviewerValues);

		return parent::initData($args, $request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		// Fixme: need to read filter input vars?
	}
}

?>
