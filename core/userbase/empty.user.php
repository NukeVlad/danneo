<?php
/**
 * File:        /core/userbase/empty.user.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class userapi
 */
class userapi
{
	public $usermain = array
	(
		'logged'    => 0,
		'userid'    => 0,
		'gid'       => 0,
		'uname'     => '',
		'umail'     => '',
		'regdate'   => '',
		'lastvisit' => '',
		'phone'     => '',
		'city'      => '',
		'skype'     => '',
		'www'       => '',
		'newmsg'    => 0,
		'newmsgnr'  => 0,
		'avatar'    => ''
	);

	function __construct($db, $logged = false)
	{
		return $this->usermain;
	}

	function associat($arr)
	{
		return $arr = array();
	}

	function checkpwd($passw)
	{
		global $config;
		return (preparse($passw, THIS_STRLEN) < $config['user']['minpass'] OR preparse($passw, THIS_STRLEN) > $config['user']['maxpass'] OR ! preg_match('/^[a-zA-Z0-9]+$/D', $passw)) ? 0 : 1;
	}

	function checklogin($login)
	{
		global $config;
		return (preparse($login, THIS_STRLEN) < $config['user']['minname'] OR preparse($login, THIS_STRLEN) > $config['user']['maxname'] OR ! preg_match('/^[\p{L}\p{Nd}]+$/u', $login)) ? 0 : 1;
	}

    function messagelast($limit = 10, $target = '_blank'){}
}
