<?php
/**
 * @file pages/dashboard/DashboardHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DashboardHandler
 * @ingroup pages_dashboard
 *
 * @brief Handle requests for user's dashboard.
 */

import('handler.Handler');

class DashboardHandler extends Handler {
	/**
	 * Constructor
	 */
	function DashboardHandler() {
		parent::Handler();
	}

	/**
	 * Display about index page.
	 */
	function index(&$request, $args) {
		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		$templateMgr->assign('isSiteAdmin', Validation::isSiteAdmin());

		if($press =& Request::getContext()) {
			$user = Request::getUser();
			$userId = $user->getId();

			// Check which roles the user has
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			if($roleDao->userHasRole($press->getId(), $userId, ROLE_ID_PRESS_MANAGER)) {
				$templateMgr->assign('isManager', true);
				$managerUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_PRESS_MANAGER);
				$templateMgr->assign('managerId', $managerUserGroup->getId());
			}

			if($roleDao->userHasRole($press->getId(), $userId, ROLE_ID_SERIES_EDITOR)) {
				$templateMgr->assign('isSeriesEditor', true);
				$seriesEditorUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_SERIES_EDITOR);
				$templateMgr->assign('seriesEditorId', $seriesEditorUserGroup->getId());

			}

			if($roleDao->userHasRole($press->getId(), $userId, ROLE_ID_AUTHOR)) {
				$templateMgr->assign('isAuthor', true);
				$authorUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);
				$templateMgr->assign('authorId', $authorUserGroup->getId());

			}

			if($roleDao->userHasRole($press->getId(), $userId, ROLE_ID_REVIEWER)) {
				$templateMgr->assign('isReviewer', true);
				$reviewerUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_REVIEWER);
				$templateMgr->assign('reviewerId', $reviewerUserGroup->getId());
			}

			if($roleDao->userHasRole($press->getId(), $userId, ROLE_ID_PRESS_ASSISTANT)) {
				$templateMgr->assign('isPressAssistant', true);
				$assistantUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_PRESS_ASSISTANT);
				$templateMgr->assign('assistantId', $assistantUserGroup->getId());
			}
		} else {
			// At the site level
			$templateMgr->assign('atSiteLevel', true);

			$pressDao =& DAORegistry::getDAO('PressDAO');
			$presses =& $pressDao->getPresses();
			$templateMgr->assign_by_ref('presses', $presses);
		}


		$templateMgr->display('dashboard/index.tpl');
	}


	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_OMP_ADMIN, LOCALE_COMPONENT_PKP_ADMIN, LOCALE_COMPONENT_PKP_MANAGER));

		if ($subclass) $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'dashboard'), 'dashboard.dashboard')));
	}
}

?>
