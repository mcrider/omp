<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

import('classes.security.Role');
import('lib.pkp.classes.security.UserGroupAssignment');

class RoleDAO extends DAO {
	/** @var $userDao The User DAO to return User objects when necessary **/
	var $userDao;

	/**
	 * Constructor.
	 */
	function RoleDAO() {
		parent::DAO();
		$this->userDao =& DAORegistry::getDAO('UserDAO');
	}

	/**
	 * Retrieve a list of users in a specified role.
	 * @param $roleId int optional (can leave as null to get all users in press)
	 * @param $pressId int optional, include users only in this press
	 * @param $searchType int optional, which field to search
	 * @param $search string optional, string to match
	 * @param $searchMatch string optional, type of match ('is' vs. 'contains' vs. 'startsWith')
	 * @param $dbResultRange object DBRangeInfo object describing range of results to return
	 * @return array matching Users
	 */
	function &getUsersByRoleId($roleId = null, $pressId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
		$users = array();

		$paramArray = array('interest');
		if (isset($roleId)) $paramArray[] = (int) $roleId;
		if (isset($pressId)) $paramArray[] = (int) $pressId;
		// For security / resource usage reasons, a role or press ID
		// must be specified. Don't allow calls supplying neither.
		if ($pressId === null && $roleId === null) return null;

		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 'cves.setting_value'
		);

		if (!empty($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (!empty($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND u.user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
				$paramArray[] = $search . '%';
				break;
		}

		$searchSql .= ' ORDER BY u.last_name, u.first_name'; // FIXME Add "sort field" parameter?

		$result =& $this->retrieveRange(
			'SELECT DISTINCT u.* FROM users AS u LEFT JOIN controlled_vocabs cv ON (cv.assoc_id = u.user_id AND cv.symbolic = ?)
			LEFT JOIN controlled_vocab_entries cve ON (cve.controlled_vocab_id = cv.controlled_vocab_id)
			LEFT JOIN controlled_vocab_entry_settings cves ON (cves.controlled_vocab_entry_id = cve.controlled_vocab_entry_id),
			user_groups AS ug, user_user_groups AS uug
			WHERE ug.user_group_id = uug.user_group_id AND u.user_id = uug.user_id' . (isset($roleId) ? ' AND ug.role_id = ?' : '') . (isset($pressId) ? ' AND ug.press_id = ?' : '') . ' ' . $searchSql,
			$paramArray,
			$dbResultRange
		);

		$returner = new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
		return $returner;
	}

   	/**
	 * Validation check to see if a user belongs to any group that has a given role
	 * DEPRECATE: keeping around because HandlerValidatorRoles in pkp-lib uses
	 * until we port user groups to OxS
	 * @param $pressId
	 * @param $userId
	 * @param $roleId
	 * @return bool
	 */
	function roleExists($pressId, $userId, $roleId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->userHasRole($pressId, $userId, $roleId);
	}

	/**
	 * Validation check to see if a user belongs to any group that has a given role
	 * @param $pressId
	 * @param $userId
	 * @param $roleId
	 * @return bool
	 */
	function userHasRole($pressId, $userId, $roleId) {
		$result =& $this->retrieve(
			'SELECT count(*) FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id
			WHERE ug.press_id = ? AND uug.user_id = ? AND ug.role_id = ?',
			array((int) $pressId, (int) $userId, (int) $roleId)
		);

		// > 0 because user could belong to more than one user group with this role
		$returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Return an array of role objects corresponding to the roles a given user has
	 * @param $userId
	 * @param $pressId
	 * @return array of Roles
	 */
	function getByUserId($userId, $pressId = null) {
		$params = array($userId);
		if ($pressId) $params[] = $pressId;
		$result =& $this->retrieve('SELECT DISTINCT ug.role_id
									FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id
									WHERE uug.user_id = ?' . ($pressId?' AND ug.press_id = ?':''),
									$params);

		$roles = array();
		while ( !$result->EOF ) {
			$roles[] =& new Role($result->fields[0]);
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $roles;
	}

	/**
	 * Retrieve the number of users with a given role associated with the specified press.
	 * @param $pressId int
	 * @param $roleId int
	 * @return int
	 */
	function getPressUsersRoleCount($pressId, $roleId) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		return $userGroupDao->getPressUsersCount($pressId, null, $roleId);
	}

	/**
	 * Get a role's ID based on its path.
	 * @param $rolePath string
	 * @return int
	 */
	function getRoleIdFromPath($rolePath) {
		switch ($rolePath) {
			case 'admin':
				return ROLE_ID_SITE_ADMIN;
			case 'manager':
				return ROLE_ID_PRESS_MANAGER;
			case 'author':
				return ROLE_ID_AUTHOR;
			case 'editor':
				return ROLE_ID_EDITOR;
			case 'seriesEditor':
				return ROLE_ID_SERIES_EDITOR;
			case 'reviewer':
				return ROLE_ID_REVIEWER;
			case 'designer':
				return ROLE_ID_DESIGNER;
			case 'copyeditor':
				return ROLE_ID_COPYEDITOR;
			case 'proofreader':
				return ROLE_ID_PROOFREADER;
			case 'productionEditor':
				return ROLE_ID_PRODUCTION_EDITOR;
			case 'reader':
				return ROLE_ID_READER;
			case 'director':
				return ROLE_ID_DIRECTOR;
			case 'indexer':
				return ROLE_ID_INDEXER;
			default:
				return null;
		}
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'username': return 'u.username';
			case 'name': return 'u.last_name';
			case 'email': return 'u.email';
			default: return null;
		}
	}
}

?>
