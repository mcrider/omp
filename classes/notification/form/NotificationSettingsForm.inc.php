<?php
/**
 * @defgroup notification_form
 */

/**
 * @file classes/notification/form/NotificationSettingsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationSettingsForm
 * @ingroup notification_form
 *
 * @brief Form to edit notification settings.
 */


import('lib.pkp.classes.notification.form.PKPNotificationSettingsForm');

class NotificationSettingsForm extends PKPNotificationSettingsForm {
	/**
	 * Constructor.
	 */
	function NotificationSettingsForm() {
		parent::PKPNotificationSettingsForm();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array('notificationMonographSubmitted',
				'notificationMetadataModified',
				'notificationReviewerComment',
				'emailNotificationMonographSubmitted',
				'emailNotificationMetadataModified',
				'emailNotificationReviewerComment')
		);
	}

	/**
	 * Save site settings.
	 */
	function execute(&$request) {
		$user = $request->getUser();
		$userId = $user->getId();
		$press =& $request->getPress();

		// Blocked notification settings
		$blockedNotifications = array();
		if(!$this->getData('notificationMonographSubmitted')) $blockedNotifications[] = NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED;
		if(!$this->getData('notificationMetadataModified')) $blockedNotifications[] = NOTIFICATION_TYPE_METADATA_MODIFIED;
		if(!$this->getData('notificationReviewerComment')) $blockedNotifications[] = NOTIFICATION_TYPE_REVIEWER_COMMENT;

		// Email settings
		$emailSettings = array();
		if($this->getData('emailNotificationMonographSubmitted')) $emailSettings[] = NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED;
		if($this->getData('emailNotificationMetadataModified')) $emailSettings[] = NOTIFICATION_TYPE_METADATA_MODIFIED;
		if($this->getData('emailNotificationReviewerComment')) $emailSettings[] = NOTIFICATION_TYPE_REVIEWER_COMMENT;


		$notificationSettingsDao =& DAORegistry::getDAO('NotificationSettingsDAO');
		$notificationSettingsDao->updateBlockedNotificationTypes($blockedNotifications, $userId, $press->getId());
		$notificationSettingsDao->updateNotificationEmailSettings($emailSettings, $userId, $press->getId());

		return true;
	}


}

?>
