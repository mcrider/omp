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

class ManageFinalDraftFilesForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ManageFinalDraftFilesForm($monographId) {
		parent::Form('controllers/grid/files/finalDraftFiles/manageFinalDraftFiles.tpl');
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Template methods from Form
	//
	/**
	 * Initialize variables
	 */
	function initData(&$args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($this->_monographId);

		$this->setData('monographId', $this->_monographId);
		$this->setData('reviewType', $monograph->getCurrentReviewType());
		$this->setData('round', $monograph->getCurrentRound());
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('reviewType', 'round', 'selectedFiles'));
	}

	/**
	 * Save submissionContributor
	 */
	function &execute(&$args, &$request) {
		$reviewType = (integer)$this->getData('reviewType');
		$round = (integer)$this->getData('round');

		$selectedFiles = $this->getData('selectedFiles');
		$filesWithRevisions = array();
		if (!empty($selectedFiles)) {
			foreach ($selectedFiles as $selectedFile) {
				$filesWithRevisions[] = explode("-", $selectedFile);
			}
		}
		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $filesWithRevisions);

		// Return the files that are currently set for the review
		$finalDraftFilesByRound =& $reviewAssignmentDAO->getFinalDraftFilesByRound($this->_monographId);
		if (isset($finalDraftFilesByRound[$reviewType][$round])) {
			return $finalDraftFilesByRound[$reviewType][$round];
		} else {
			$noFiles = array();
			return $noFiles;
		}
	}
}

?>
