<?php

/**
 * @file controllers/grid/files/submission/AuthorSubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests on the author's submission details pages.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.submission.SubmissionDetailsFilesGridHandler');

class AuthorSubmissionDetailsFilesGridHandler extends SubmissionDetailsFilesGridHandler {
	/**
	 * Constructor
	 */
	function AuthorSubmissionDetailsFilesGridHandler() {
		parent::SubmissionDetailsFilesGridHandler(false, false, false, true);
	}

}