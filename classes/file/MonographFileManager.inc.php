<?php

/**
 * @file classes/file/MonographFileManager.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileManager
 * @ingroup file
 *
 * @brief Class defining operations for monograph file management.
 *
 * Monograph directory structure:
 * [monograph id]/note
 * [monograph id]/public
 * [monograph id]/submission
 * [monograph id]/submission/original
 * [monograph id]/submission/review
 * [monograph id]/submission/editor
 * [monograph id]/submission/copyedit
 * [monograph id]/submission/layout
 * [monograph id]/attachment
 */


import('lib.pkp.classes.file.FileManager');

class MonographFileManager extends FileManager {

	/** @var string the path to location of the files */
	var $_filesDir;

	/** @var int the ID of the associated monograph */
	var $_monographId;

	/**
	 * Constructor.
	 * Create a manager for handling monograph file uploads.
	 * @param $monographId int
	 */
	function MonographFileManager($monographId) {
		$this->_monographId = $monographId;
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the monograph id.
	 * @return integer
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the files directory.
	 * @return string
	 */
	function getFilesDir() {
		if (empty($this->_filesDir)) {
			$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
			$monograph =& $monographDao->getMonograph($this->getMonographId());
			assert(is_a($monograph, 'Monograph'));
			$pressId = $monograph->getPressId();
			$this->_filesDir = $monograph->getFilePath();
		}
		return $this->_filesDir;
	}


	//
	// Public methods
	//
	/**
	 * Upload a monograph file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $useCase int genre (e.g. Manuscript, Appendix, etc.)
	 * @param $fileId int
	 * @param $genreId int
	 * @return int file ID, is false if failure
	 */
	function uploadMonographFile($fileName, $useCase = MONOGRAPH_FILE_USE_CASE_SUBMISSION, $fileId = null, $genreId = null) {
		return $this->_handleUpload($fileName, $useCase, $fileId, $genreId);
	}

	/**
	 * Upload a file to the review file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadReviewFile($fileName, $fileId = null, $reviewId = null) {
		$assocType = $reviewId ? ASSOC_TYPE_REVIEW_ASSIGNMENT : null;
		return $this->_handleUpload($fileName, MONOGRAPH_FILE_USE_CASE_REVIEW, $fileId, null, $reviewId, $assocType);
	}

	/**
	 * Upload a copyedited file to the copyedit file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadCopyeditResponseFile($fileName, $fileId = null) {
		return $this->_handleUpload($fileName, MONOGRAPH_FILE_USE_CASE_COPYEDIT_RESPONSE, $fileId);
	}

	/**
	 * Retrieve file information by file ID.
	 * @return MonographFile
	 */
	function &getFile($fileId, $revision = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monographFile =& $monographFileDao->getMonographFile($fileId, $revision, $this->getMonographId());
		return $monographFile;
	}

	/**
	 * Read a file's contents.
	 * @param $output boolean output the file's contents instead of returning a string
	 * @return boolean
	 */
	function readFile($fileId, $revision = null, $output = false) {
		$monographFile =& $this->getFile($fileId, $revision);
		if (isset($monographFile)) {
			return parent::readFile($monographFile->getFilePath(), $output);
		} else {
			return false;
		}
	}

	/**
	 * Delete a file by ID.
	 * If no revision is specified, all revisions of the file are deleted.
	 * @param $fileId int
	 * @param $revision int (optional)
	 * @return int number of files removed
	 */
	function deleteFile($fileId, $revision = null) {
		// Identify the files to be deleted.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$monographFiles = array();
		if (isset($revision)) {
			// Delete only a single revision of a file.
			$monographFileRevision =& $monographFileDao->getMonographFile($fileId, $revision);
			if (isset($monographFileRevision)) {
				$monographFiles[] = $monographFileRevision;
			}
		} else {
			// Delete all revisions of a file.
			$monographFiles =& $monographFileDao->getMonographFileRevisions($fileId, null, false);
		}

		// Delete the files on the file system.
		foreach ($monographFiles as $monographFile) {
			parent::deleteFile($monographFile->getFilePath());
		}

		// Delete the files in the database.
		$monographFileDao->deleteMonographFileById($fileId, $revision);

		// Return the number of deleted files.
		return count($monographFiles);
	}

	/**
	 * Delete the entire tree of files belonging to a monograph.
	 */
	function deleteMonographTree() {
		parent::rmtree($this->getFilesDir());
	}

	/**
	 * Download a file.
	 * @param $fileId int the file id of the file to download
	 * @param $revision int the revision of the file to download
	 * @param $inline print file as inline instead of attachment, optional
	 * @return boolean
	 */
	function downloadFile($fileId, $revision = null, $inline = false) {
		$returner = false;
		$monographFile =& $this->getFile($fileId, $revision);
		if (isset($monographFile)) {
			// Make sure that the file belongs to the monograph.
			if ($monographFile->getMonographId() != $this->getMonographId()) fatalError('Invalid file id!');

			// Mark the file as viewed by this user.
			$sessionManager =& SessionManager::getManager();
			$session =& $sessionManager->getUserSession();
			$user =& $session->getUser();
			$viewsDao =& DAORegistry::getDAO('ViewsDAO');
			$viewsDao->recordView(ASSOC_TYPE_MONOGRAPH_FILE, $fileId, $user->getId());

			// Send the file to the user.
			$filePath = $monographFile->getFilePath();
			$mediaType = $monographFile->getFileType();
			$returner = parent::downloadFile($filePath, $mediaType, $inline);
		}

		return $returner;
	}

	/**
	 * Download all monograph files as an archive
	 * @param $monographFiles ArrayItemIterator
	 * @return boolean
	 */
	function downloadFilesArchive(&$monographFiles = null) {
		if(!isset($monographFiles)) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($this->getMonographId());
		}

		$filePaths = array();
		while ($monographFile =& $monographFiles->next()) {
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePath = str_replace($this->getFilesDir(), '', $monographFile->getFilePath());
			// Add files to be archived to array
			$filePaths[] = escapeshellarg($filePath);
		}

		// Create the archive and download the file
		$archivePath = $this->getFilesDir() . "monograph_" . $this->getMonographId() . "_files.tar.gz";
		$tarCommand = "tar czf ". $archivePath . " -C \"" . $this->getFilesDir() . "\" " . implode(" ", $filePaths);
		exec($tarCommand);
		if (file_exists($archivePath)) {
			parent::downloadFile($archivePath);
			return true;
		} else return false;
	}

	/**
	 * View a file inline (variant of downloadFile).
	 * @see MonographFileManager::downloadFile
	 */
	function viewFile($fileId, $revision = null) {
		$this->downloadFile($fileId, $revision, true);
	}

	/**
	 * Return path associated with a useCase code.
	 * @param $useCase string
	 * @return string
	 */
	function useCaseToPath($useCase) {
		switch ($useCase) {
			case MONOGRAPH_FILE_USE_CASE_PUBLIC: return 'public';
			case MONOGRAPH_FILE_USE_CASE_SUBMISSION: return 'submission';
			case MONOGRAPH_FILE_USE_CASE_NOTE: return 'note';
			case MONOGRAPH_FILE_USE_CASE_REVIEW: return 'submission/review';
			case MONOGRAPH_FILE_USE_CASE_FINAL: return 'submission/final';
			case MONOGRAPH_FILE_USE_CASE_FAIR_COPY: return 'submission/fairCopy';
			case MONOGRAPH_FILE_USE_CASE_EDITOR: return 'submission/editor';
			case MONOGRAPH_FILE_USE_CASE_COPYEDIT: return 'submission/copyedit';
			case MONOGRAPH_FILE_USE_CASE_PRODUCTION: return 'submission/production';
			case MONOGRAPH_FILE_USE_CASE_GALLEY: return 'submission/galleys';
			case MONOGRAPH_FILE_USE_CASE_LAYOUT: return 'submission/layout';
			case MONOGRAPH_FILE_USE_CASE_ATTACHMENT: default: return 'attachment';
		}
	}

	/**
	 * Copy a temporary file to a monograph file.
	 * @param $temporaryFile MonographFile
	 * @param $useCase integer
	 * @param $assocId integer
	 * @param $assocType integer
	 * @return integer the file ID (false if upload failed)
	 */
	function temporaryFileToMonographFile(&$temporaryFile, $useCase, $assocId, $assocType) {
		// Instantiate and pre-populate the new target monograph file.
		$monographFile =& $this->_instantiateMonographFile(null, $useCase, null, $assocId, $assocType);

		// Transfer data from the temporary file to the monograph file.
		$monographFile->setFileType($temporaryFile->getFileType());
		$monographFile->setOriginalFileName($temporaryFile->getOriginalFileName());

		// Copy the temporary file to it's final destination and persist
		// its metadata to the database.
		return $this->_persistFile($temporaryFile->getFilePath(), $monographFile, true);
	}


	//
	// Private helper methods
	//
	/**
	 * Upload the file and add it to the database.
	 * @param $fileName string index into the $_FILES array
	 * @param $type int identifying type (i.e. MONOGRAPH_FILE_USE_CASE_*)
	 * @param $fileId int ID of an existing file to update
	 * @param $genreId int foreign key into MONOGRAPH_FILE_USE_CASE_types table (e.g. manuscript, etc.)
	 * @param $assocType int
	 * @param $assocId int
	 * @return int the file ID (false if upload failed)
	 */
	function _handleUpload($fileName, $useCase, $fileId = null, $genreId = null, $assocId = null, $assocType = null) {
		// Instantiate and pre-populate a new monograph file object.
		$monographFile = $this->_instantiateMonographFile($fileId, $useCase, $genreId, $assocId, $assocType);

		// Retrieve file information from the uploaded file.
		assert(isset($_FILES[$fileName]));
		$monographFile->setFileType($_FILES[$fileName]['type']);
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName($_FILES[$fileName]['name']));

		// Set the uploader's userGroupId
		$sessionMgr =& SessionManager::getManager();
		$session =& $sessionMgr->getUserSession();
		$monographFile->setUserGroupId($session->getActingAsUserGroupId());

		// Copy the uploaded file to its final destination and
		// persist its meta-data to the database.
		return $this->_persistFile($fileName, $monographFile);
	}

	/**
	 * Routine to instantiate and pre-populate a new monograph file.
	 * @param $fileId integer
	 * @param $useCase integer
	 * @param $assocId integer
	 * @param $assocType integer
	 * @return MonographFile
	 */
	function &_instantiateMonographFile($fileId, $useCase, $genreId, $assocId, $assocType) {
		// Instantiate a new monograph file.
		$monographFile = new MonographFile();
		$monographFile->setMonographId($this->getMonographId());

		// Do we create a new file or a new revision of an existing file?
		if ($fileId) {
			// Create a new revision of the file with the existing file id.
			$monographFile->setFileId($fileId);
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile->setRevision($monographFileDao->getRevisionNumber($fileId)+1);
		} else {
			// Create the first revision of a new file.
			$monographFile->setRevision(1);
		}

		// Set a preliminary file name and file size.
		$monographFile->setFileName('unknown');
		$monographFile->setFileSize(0);

		// Set the file use case.
		$monographFile->setUseCase($useCase);

		// Set the monograph genre (if given).
		if(isset($genreId)) {
			$monographFile->setGenreId($genreId);
		}

		// Set modification dates to the current system date.
		$monographFile->setDateUploaded(Core::getCurrentDate());
		$monographFile->setDateModified(Core::getCurrentDate());

		// Is the monograph file associated to another entity?
		if(isset($assocId)) {
			assert(isset($assocType));
			$monographFile->setAssocType($assocType);
			$monographFile->setAssocId($assocId);
		}

		// Return the pre-populated monograph file.
		return $monographFile;
	}

	/**
	 * Copies the file to it's final destination and persists
	 * the file meta-data to the database.
	 * @param $sourceFile string the path to the file to be copied
	 * @param $monographFile MonographFile the file metadata
	 * @param $copyOnly boolean set to true if the file has not been uploaded
	 *  but already exists on the file system.
	 * @return integer the id of the file
	 */
	function _persistFile($sourceFile, $monographFile, $copyOnly = false) {
		// Persist the file meta-data (without the file name) and generate a file id.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		if (!$monographFileDao->insertMonographFile($monographFile)) return false;

		// Generate and set a file name (requires the monograph id
		// that we generated when inserting the monograph data).
		$this->_generateAndPopulateFileName($monographFile);

		// Determine the final destination of the file (requires
		// the name we just generated).
		$targetFile = $monographFile->getFilePath();

		// If the "copy only" flag is set then copy the file from its
		// current place to the target destination. Otherwise upload
		// the file to the target folder.
		if (!(($copyOnly && $this->copyFile($sourceFile, $targetFile))
				|| $this->uploadFile($sourceFile, $targetFile))) {
			// If the copy/upload operation fails then remove
			// the already inserted meta-data.
			$monographFileDao->deleteMonographFile($monographFile);
			return false;
		}

		// Determine and set the file size of the target file.
		$monographFile->setFileSize(filesize($targetFile));

		// Update the monograph with the file name and file size.
		$monographFileDao->updateMonographFile($monographFile);

		// Return the file id.
		return $monographFile->getFileId();
	}

	/**
	 * Generate a unique filename for a monograph file. Sets the filename
	 * field in the monographFile to the generated value.
	 * @param $monographFile MonographFile the monograph to generate a filename for
	 */
	function _generateAndPopulateFileName(&$monographFile) {
		// If the file has a file genre set then start the
		// file name with human readable genre information.
		$genreId = $monographFile->getGenreId();
		if ($genreId) {
			$primaryLocale = Locale::getPrimaryLocale();
			$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
			$genre =& $genreDao->getById($genreId);
			assert(is_a($genre, 'Genre'));
			$fileName = $genre->getDesignation($primaryLocale).'_'.date('Ymd').'-'.$genre->getName($primaryLocale).'-';
		}

		// Make the file name unique across all files and file revisions.
		$extension = $this->parseFileExtension($monographFile->getOriginalFileName());
		$fileName .= $monographFile->getMonographId().'-'.$monographFile->getFileId().'-'.$monographFile->getRevision().'-'.$monographFile->getUseCase().'.'.$extension;

		// Populate the monograph file with the generated file name.
		$monographFile->setFileName($fileName);
	}
}

?>