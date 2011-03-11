<?php
/**
 * @file controllers/api/preparedEmails/linkAction/ResetEmailLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResetEmailLinkAction
 * @ingroup controllers_api_preparedEmails_linkAction
 *
 * @brief Delete a prepared email to the system.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ResetEmailLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $emailKey string
	 */
	function ResetEmailLinkAction(&$request, $emailKey) {
		// Instantiate the confirmation modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$confirmationModal = new ConfirmationModal(
			__('manager.emails.reset.message'), null,
			$router->url($request, null, 'api.preparedEmails.PreparedEmailsApiHandler',
				'resetEmail', null, array('emailKey' => $emailKey)));

		// Configure the file link action.
		parent::LinkAction('deleteFile', $confirmationModal,
				__('manager.emails.reset'), 'delete');
	}


}