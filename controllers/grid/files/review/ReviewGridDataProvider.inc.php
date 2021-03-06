<?php

/**
 * @file controllers/grid/files/review/ReviewGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class ReviewGridDataProvider extends SubmissionFilesGridDataProvider {
	/** @var integer */
	var $_round;

	/** @var $_viewableOnly boolean */
	var $_viewableOnly;


	/**
	 * Constructor
	 */
	function ReviewGridDataProvider($fileStageId, $viewableOnly = false) {
		$this->_viewableOnly = $viewableOnly;
		parent::SubmissionFilesGridDataProvider($fileStageId);
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// FIXME: Need to authorize review round, see #6200.
		// Get the review round from the request
		$round = $request->getUserVar('round');
		$this->setRound((int)$request->getUserVar('round'));

		return parent::getAuthorizationPolicy($request, $args, $roleAssignments);
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(parent::getRequestArgs(), array('round' => $this->getRound()));
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData() {
		// Get all review files assigned to this submission.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getRevisionsByReviewRound(
			$monograph->getId(), $this->_getStageId(), $this->getRound(), $this->_getFileStage()
		);
		$data = $this->prepareSubmissionFileData($monographFiles, $this->_viewableOnly);

		return $data;
	}

	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getSelectAction()
	 */
	function &getSelectAction($request) {
		import('controllers.grid.files.fileList.linkAction.SelectReviewFilesLinkAction');
		$monograph =& $this->getMonograph();
		$selectAction = new SelectReviewFilesLinkAction(
			&$request, $monograph->getId(), $this->_getStageId(), $this->getRound(),
			__('editor.monograph.review.manageReviewFiles')
		);
		return $selectAction;
	}

	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddFileLinkAction(
			$request, $monograph->getId(), $this->_getStageId(),
			$this->getUploaderRoles(), $this->_getFileStage(),
			null, null, $this->getRound()
		);
		return $addFileAction;
	}

	/**
	 * Get the review round number.
	 * @return integer
	 */
	function getRound() {
		return $this->_round;
	}

	/**
	 * Set the review round number.
	 * @param $round integer
	 */
	function setRound($round) {
		$this->_round = $round;
	}
}

?>
