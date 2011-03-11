<?php
/**
 * @file controllers/api/preparedEmails/linkAction/DeleteEmailLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DeleteEmailLinkAction
 * @ingroup controllers_api_preparedEmails_linkAction
 *
 * @brief Delete a prepared email.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class DeleteEmailLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $emailKey string
	 */
	function DeleteEmailLinkAction(&$request, $emailKey) {
		// Instantiate the confirmation modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$confirmationModal = new ConfirmationModal(
			__('manager.emails.confirmDelete'), null,
			$router->url($request, null, 'api.preparedEmails.PreparedEmailsApiHandler',
				'deleteCustomEmail', null, array('emailKey' => $emailKey)));

		// Configure the file link action.
		parent::LinkAction('deleteEmail', $confirmationModal,
				__('common.delete'), 'disable');
	}


}