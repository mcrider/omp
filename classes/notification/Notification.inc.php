<?php

/**
 * @file classes/notification/Notification.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Notification
 * @ingroup notification
 * @see NotificationDAO
 * @brief OMP subclass for Notifications (defines OMP-specific types and icons).
 */


/** Notification associative types. */
define('NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED', 	0x1000001);
define('NOTIFICATION_TYPE_METADATA_MODIFIED', 		0x1000002);
define('NOTIFICATION_TYPE_REVIEWER_COMMENT', 		0x1000003);
// FIXME: #6792 Removed all the notification types because they were still not used. Bring back as necessary.

import('lib.pkp.classes.notification.PKPNotification');

class Notification extends PKPNotification {

	/**
	 * Constructor.
	 */
	function Notification() {
		parent::PKPNotification();
	}

	/**
	 * @param $request
	 * @return string
	 */
	function getUrl($request) {
		$baseUrl = $request->getBaseUrl();
		$assocType = $this->getAssocType();
		switch ($assocType) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				break;
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				break;
			default:
				return parent::getUrl($request);
		}
	}

	/**
	 * return the path to the icon for this type
	 * FIXME: #6792 move these to CSS and in a the template figure out the iconography. Or set a status here or something.
	 * FIXME: #6792 also remove unused notifications types.
	 * @return string
	 */
	function getIconLocation() {
		$baseUrl = Request::getBaseUrl() . '/lib/pkp/templates/images/icons/';
		switch ($this->getAssocType()) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
				return $baseUrl . 'page_new.gif';
				break;
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
			case NOTIFICATION_TYPE_GALLEY_MODIFIED:
				return $baseUrl . 'edit.gif';
				break;
			case NOTIFICATION_TYPE_SUBMISSION_COMMENT:
			case NOTIFICATION_TYPE_LAYOUT_COMMENT:
			case NOTIFICATION_TYPE_COPYEDIT_COMMENT:
			case NOTIFICATION_TYPE_PROOFREAD_COMMENT:
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
			case NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT:
			case NOTIFICATION_TYPE_USER_COMMENT:
				return $baseUrl . 'comment_new.gif';
				break;
			case NOTIFICATION_TYPE_PUBLISHED_MONOGRAPH:
				return $baseUrl . 'list_world.gif';
				break;
			case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
				return $baseUrl . 'note_new.gif';
				break;
			default:
				return $baseUrl . 'page_alert.gif';
		}
	}

	/**
	 * Static function to send an email to a mailing list user regarding signup or a lost password
	 * @param $email string
	 * @param $password string the user's password
	 * @param $template string The mail template to use
	 */
	function sendMailingListEmail($email, $password, $template) {
		import('classes.mail.MailTemplate');
		$press = Request::getPress();
		$site = Request::getSite();

		$params = array(
			'password' => $password,
			'siteTitle' => $press->getLocalizedTitle(),
			'unsubscribeLink' => Request::url(null, 'notification', 'unsubscribeMailList')
		);

		if ($template == 'NOTIFICATION_MAILLIST_WELCOME') {
			$keyHash = md5($password);
			$confirmLink = Request::url(null, 'notification', 'confirmMailListSubscription', array($keyHash, $email));
			$params["confirmLink"] = $confirmLink;
		}

		$mail = new MailTemplate($template);
		$mail->setFrom($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
		$mail->assignParams($params);
		$mail->addRecipient($email);
		$mail->send();
	}

	// Private helper method
	function _initialize() {
		if ($this->_initialized) return true;

		parent::_initialize();
		$type = $this->getType();
		assert(isset($type));
		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
				assert($this->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($this->getAssocId()));
				$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
				$monograph =& $monographDao->getMonograph($this->getAssocId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				$this->setTitle($title);
				$this->setContent(__('notification.type.monographSubmitted'), $title);
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				break;
		}
	}
}

?>
