<?php
/**
 * File:        /core/includes/users.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $config;

/**
 * USERS
 */
if (isset($config['mod']['user']))
{
	if (isset($config['user']['regtype']) AND $config['user']['regtype'] == 'yes')
	{
		// Groups
		if ($config['user']['groupact'] == 'yes')
		{
			if (isset($config['group']) AND is_array($config['group']))
			{
				$group = $config['group'];
			} else {
				$inq = $db->query("SELECT * FROM ".$basepref."_user_group",$config['cachetime']);
				while ($item = $db->fetchassoc($inq, $config['cache']))
				{
					$group[$item['gid']] = $item;
				}
			}
		}

		// Integration
		if ($config['userbase'] == 'danneo')
		{
			require_once(DNDIR.'core/userbase/danneo/danneo.user.php');
		}
		else
		{
			$config['editpass'] = 'no';
			require_once(DNDIR.'core/userbase/'.$config['userbase'].'/danneo.user.php');
		}
	}
	else
	{
		require_once(DNDIR.'core/userbase/empty.user.php');
	}

	/**
	 * Init User Api
	 */
	$userapi = new userapi($db, TRUE);
	$usermain = $userapi->usermain;

	/**
	 * Define User Constant
	 */
	if (
		preparse($usermain['logged'], THIS_INT) == 1 AND
		preparse($usermain['userid'], THIS_INT) > 0
	) {
		define('USER_LOGGED', TRUE);
	}

	if ($config['userbase'] == 'danneo')
	{
		define('USER_DANNEO', TRUE);
	}

	if ($config['user']['regtype'] == 'yes')
	{
		define('REGTYPE', TRUE);
	}

	if ($config['user']['groupact'] == 'yes')
	{
		define('GROUP_ACT', TRUE);
	}

	/**
	 * Users mod active
	 */
	define('USER', TRUE);
}
