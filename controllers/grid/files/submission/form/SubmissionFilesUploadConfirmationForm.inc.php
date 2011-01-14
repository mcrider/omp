<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesUploadConfirmationForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadConfirmationForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for adding/editing a submission file
 */


import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadConfirmationForm extends SubmissionFilesUploadBaseForm {
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadConfirmationForm(&$request, $monographId, $fileStage, $revisedFileId = null, $uploadedFile = null) {
		// Initialize class.
		parent::SubmissionFilesUploadBaseForm($request,
				'controllers/grid/files/submissionFiles/form/fileUploadConfirmationForm.tpl',
				$monographId, $fileStage, false, $revisedFileId);

		if (is_a($uploadedFile, 'MonographFile')) {
			$this->setData('uploadedFile', $uploadedFile);
		}
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('uploadedFileId'));
		return parent::readInputData();
	}

	/**
	 * @see Form::execute()
	 * @return MonographFile if successful, otherwise null
	 */
	function &execute() {
		// Retrieve the file ids of the revised and the uploaded files.
		$revisedFileId = $this->getRevisedFileId();
		$uploadedFileId = (int)$this->getData('uploadedFileId');
		if (!($revisedFileId && $uploadedFileId)) fatalError('Invalid file ids!');
		if ($revisedFileId == $uploadedFileId) fatalError('The revised file id and the uploaded file id cannot be the same!');

		// Assign the new file as the latest revision of the old file.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographId = $this->getData('monographId');
		$uploadedFile =& $submissionFileDao->setAsLatestRevision($revisedFileId, $uploadedFileId, $monographId, $this->getFileStage());

		return $uploadedFile;
	}
}

?>
