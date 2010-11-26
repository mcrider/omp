<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for adding/editing a submission file
 */

import('lib.pkp.classes.form.Form');

class SubmissionFilesUploadForm extends Form {
	/** The id of the file being edited */
	var $_fileId;

	/** The id of the monograph being edited */
	var $_monographId;

	/** The stage of the file being uploaded (i.e., the 'type') */
	var $_fileStage;

	/** Whether we are uploading a revision */
	var $_isRevision;

	/**
	 * Constructor.
	 */
	function SubmissionFilesUploadForm($fileId = null, $monographId, $fileStage = MONOGRAPH_FILE_USE_CASE_SUBMISSION, $isRevision = false) {
		$this->_fileId = $fileId;
		$this->_monographId = $monographId;
		$this->_fileStage = $fileStage;
		$this->_isRevision = $isRevision;

		parent::Form('controllers/grid/files/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->_data['monographId'] = $this->_monographId;
		if (isset($this->_fileId) ) {
			$this->_data['fileId'] = $this->_fileId;

			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($this->_fileId);
			$this->_data['monographFileName'] = $monographFile->getOriginalFileName();
			$this->_data['currentFileType'] = $monographFile->getGenreId();
		}

		$context =& $request->getContext();
		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$genres = $genreDao->getEnabledByPressId($context->getId());

		$genreList = array();
		while($genre =& $genres->next()){
			$genreId = $genre->getId();
			$genreList[$genreId] = $genre->getLocalizedName();
			unset($genre);
		}

		// Assign monograph files to template to display in revision drop-down menu
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($this->_monographId);
		$monographFileOptions = array();
		foreach ($monographFiles as $monographFile) {
			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
			if ($monographFile->getRevision() > 1) $fileName .= ' (' . $monographFile->getRevision() . ')'; // Add revision number to label
			$monographFileOptions[$monographFile->getFileId()] = $fileName;
		}
		$this->_data['monographFileOptions'] =& $monographFileOptions;

		$this->_data['genres'] = $genreList;
		$this->_data['fileStage'] = $this->_fileStage;
		$this->_data['isRevision'] = $this->_isRevision;
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'genreId'));
	}

	/**
	 * Check if the uploaded file has a similar name to existing files (i.e., a possible revision)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int MonographFile Id
	 */
	function checkForRevision($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		if ($monographFileManager->uploadedFileExists('submissionFile')) {
			$fileName = $monographFileManager->getUploadedFileName('submissionFile');

			// Check similarity of filename against existing filenames
			return $this->_checkForSimilarFilenames($fileName, $monographId);
		}
	}

	/**
	 * Upload the submission file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int
	 */
	function uploadFile($args, &$request) {
		$monographId = $this->_monographId;
		$fileId = $this->_fileId;
		$fileStage = $this->_fileStage;
		assert(!empty($fileStage));
		$genreId = (int)$this->getData('genreId');

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		if($fileStage == MONOGRAPH_FILE_USE_CASE_COPYEDIT) {
			$uploadedFile = 'copyeditingFile';
			// The user is uploading a copyedited version of an existing file
			// Load the existing file to get the genreId
		} else {
			$uploadedFile = 'submissionFile';
		}

		if ($monographFileManager->uploadedFileExists($uploadedFile)) {
			$submissionFileId = $monographFileManager->uploadMonographFile($uploadedFile, $fileStage, $fileId, $genreId);

			if (!empty($genreId)) {
				$genreDao =& DAORegistry::getDAO('GenreDAO');
				$genre =& $genreDao->getById($genreId);

				// If we're uploading artwork, put an entry in the monograph_artwork_files table
				if ($genre->getCategory() == GENRE_CATEGORY_ARTWORK && isset($submissionFileId)) {
					$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
					$artworkFile =& $artworkFileDao->newDataObject();
					$artworkFile->setFileId($submissionFileId);
					$artworkFile->setMonographId($monographId);
					$artworkFileDao->insertObject($artworkFile);
				}
			}
		}

		return isset($submissionFileId) ? $submissionFileId : false;
	}

	/**
	 * Check the filename against existing files in the submission
	 * A criterion of 70% of characters matching is used to determine the return value
	 * @param $fileName string
	 * @param $monographId int
	 * @return int MonographFile Id
	 */
	function _checkForSimilarFilenames($fileName, $monographId) {
		$criterion = 70;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId);

		$matchedFileId = null;
		foreach ($monographFiles as $monographFile) {
			$matchedChars = similar_text($fileName, $monographFile->getOriginalFileName(), &$p);
			if($p > $criterion) {
				$matchedFileId = $monographFile->getFileId();
				$criterion = $p; // Reset criterion to this comparison's precentage to see if there are better matches
			}
		}

		return $matchedFileId;
	}
}

?>
