<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesArtworkMetadataForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesArtworkMetadataForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for editing artwork file metadata.
 */

import('controllers.grid.files.submissionFiles.form.SubmissionFilesMetadataForm');

class SubmissionFilesArtworkMetadataForm extends SubmissionFilesMetadataForm {
	/**
	 * Constructor.
	 */
	function SubmissionFilesArtworkMetadataForm($fileId, $signoffId = null) {
		parent::SubmissionFilesMetadataForm($fileId, $signoffId, 'controllers/grid/files/submissionFiles/form/artworkMetadataForm.tpl');
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fileId', $this->_fileId);
		$templateMgr->assign('signoffId', $this->_signoffId);

		//$templateMgr->assign('monographId', $this->_monographId);
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
		$artworkFile =& $artworkFileDao->getByFileId($this->_fileId);
		$templateMgr->assign_by_ref('artworkFile', $artworkFile);

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($this->_fileId);
		$templateMgr->assign_by_ref('monographFile', $monographFile);
		$templateMgr->assign_by_ref('monographId', $monographFile->getMonographId());

		// artwork can be grouped by monograph chapter
		if ($artworkFile) {
			$chapterDao =& DAORegistry::getDAO('ChapterDAO');
			$chapters =& $chapterDao->getChapters($artworkFile->getMonographId());
			$chapterOptions = array();
			if($chapters) {
				while($chapter =& $chapters->next()) {
					$chapterId = $chapter->getId();
					$chapterOptions[$chapterId] = $chapter->getLocalizedTitle();
					unset($chapter);
				}
			}
			$templateMgr->assign_by_ref('selectedChapter', $artworkFile->getChapterId());
		} else {
			$chapters = null;
		}

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $this->_fileId);
		$templateMgr->assign('note', $notes->next());

		$templateMgr->assign_by_ref('chapterOptions', $chapterOptions);

		return parent::fetch($request);
	}

	/**
	 * Initialize form data.
	 */
	function initData($args, &$request) {
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');
		$artworkFile =& $artworkFileDao->getByFileId($this->_fileId);
		$this->_data['artworkFile'] =& $artworkFile;

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($this->_fileId);
		$this->_data['$monographFile'] =& $monographFile;

		// grid related data
		$this->_data['monographId'] = $monographFile->getMonographId();
		$this->_data['fileId'] = $this->_fileId;
		$this->_data['artworkFileId'] = isset($args['artworkFileId']) ? $args['artworkFileId'] : null;
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'name', 'artwork', 'artworkFile', 'artworkCaption', 'artworkCredit', 'artworkCopyrightOwner', 'artworkCopyrightOwnerContact', 'artworkPermissionTerms', 'monographId',
			'artworkContact', 'artworkPlacement', 'artworkOtherPlacement', 'artworkChapterId', 'artworkPlacementType', 'note'
		));
		$this->readUserVars(array('artworkFileId'));
	}

	/**
	 * Save settings.
	 * @see Form::execute()
	 */
	function execute() {
		$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO');

		// manage artwork permissions file
		$monographId = $this->getData('monographId');

		$artworkFile =& $artworkFileDao->getByFileId($this->_fileId);

		$artworkFile->setName($this->getData('name'), Locale::getLocale());
		$artworkFile->setFileId($this->_fileId);
		$artworkFile->setMonographId($monographId);
		//
		// FIXME: Should caption, credit, or any other fields be localized?
		//
		$artworkFile->setCaption($this->getData('artworkCaption'));
		$artworkFile->setCredit($this->getData('artworkCredit'));
		$artworkFile->setCopyrightOwner($this->getData('artworkCopyrightOwner'));
		$artworkFile->setCopyrightOwnerContactDetails($this->getData('artworkCopyrightOwnerContact'));
		$artworkFile->setPermissionTerms($this->getData('artworkPermissionTerms'));
		$artworkFile->setContactAuthor($this->getData('artworkContact'));
		$artworkFile->setChapterId(null);
		$artworkFile->setPlacement($this->getData('artworkPlacement'));

		$artworkFileDao->updateObject($artworkFile);

		// Save the note if it exists
		if ($this->getData('note')) {
			$noteDao =& DAORegistry::getDAO('NoteDAO');
			$note = $noteDao->newDataObject();
			$press =& Request::getPress();
			$user =& Request::getUser();

			$note->setContextId($press->getId());
			$note->setUserId($user->getId());
			$note->setContents($this->getData('note'));
			$note->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
			$note->setAssocId($this->_fileId);

		 	$noteDao->insertObject($note);
		}

		return $artworkFile->getId();
	}

}

?>