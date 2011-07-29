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
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		$type = $this->getType();
		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				return $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'submission', $this->getAssocId());
				break;
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				break;
			default:
				return parent::getUrl($request);
		}
	}

	/**
	 * Return a CSS class containing the icon of this notification type
	 * @return string
	 */
	function getIconClass() {
		switch ($this->getType()) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED: return 'notifyIconNewPage'; break;
			case NOTIFICATION_TYPE_METADATA_MODIFIED: return 'notifyIconEdit'; break;
			case NOTIFICATION_TYPE_REVIEWER_COMMENT: return 'notifyIconNewComment'; break;
			default: return parent::getIconClass();
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
				$this->setContents(__('notification.type.monographSubmitted', array('param' => $title)));
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				break;
				assert($this->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT && is_numeric($this->getAssocId()));
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($this->getAssocId());
				$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
				$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				$this->setTitle($title);
				$this->setContents(__('notification.type.reviewerComment', array('param' => $title)));
		}
	}
}

?>
