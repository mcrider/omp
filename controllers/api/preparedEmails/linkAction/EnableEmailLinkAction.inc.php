<?php
/**
 * @file controllers/api/preparedEmails/linkAction/EnableEmailLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DeleteEmailLinkAction
 * @ingroup controllers_api_preparedEmails_linkAction
 *
 * @brief Enable a previously disabled prepared email.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class EnableEmailLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $emailKey string
	 */
	function EnableEmailLinkAction(&$request, $emailKey) {
		// Instantiate the confirmation modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$confirmationModal = new ConfirmationModal(
			__('manager.emails.enable.message'), null,
			$router->url($request, null, 'api.preparedEmails.PreparedEmailsApiHandler',
				'enableEmail', null, array('emailKey' => $emailKey)));

		// Configure the file link action.
		parent::LinkAction('disableEmail', $confirmationModal, __('manager.emails.enable'), 'enable');
	}


}