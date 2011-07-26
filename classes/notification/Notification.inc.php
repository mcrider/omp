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

}

?>
