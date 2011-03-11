<?php
/**
 * @file controllers/api/preparedEmails/linkAction/ResetAllEmailsLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResetAllEmailsLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief Reset to default the prepared emails for the current press.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ResetAllEmailsLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function ResetAllEmailsLinkAction(&$request) {
		// Instantiate the confirmation modal.
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$confirmationModal = new ConfirmationModal(
			__('manager.emails.resetAll.message'), null,
			$dispatcher->url($request, ROUTE_COMPONENT, null,
				'api.preparedEmails.PreparedEmailsApiHandler', 'resetAllEmails')
		);

		// Configure the file link action.
		parent::LinkAction('deleteFile', $confirmationModal,
				__('manager.emails.resetAll'), 'delete');
	}
}