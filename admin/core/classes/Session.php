<?php
/**
 * File:        /admin/core/classes/Session.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Session
 */
class Session
{
	/**
	 * @access public
	 * @type string
	 */
	public $hash = '';
	public $skin = '';
	public $icon = '';

	function __construct()
	{
	}

	/**
	 * Функция проверки и обновления времени последнего визита
	 */
	function adlast($adlog, $adpwd)
	{
		global $db, $basepref;

		$outlast = '';
		$adlog = preparse_dn($adlog);
		$adpwd = preparse_dn($adpwd);
		$pwd = preparse($adpwd, THIS_MD_5);
		$inquiry = $db->fetchrow($db->query("SELECT admid, admid, adpwd, adlast FROM ".$basepref."_admin WHERE adlog = '".$db->escape($adlog)."'"));
		if ($inquiry['adpwd'] == $pwd AND isset($inquiry['admid']))
		{
			$lastsess = $db->fetchrow($db->query("SELECT MAX(lastactivity) as last FROM ".$basepref."_admin_sess WHERE admid = '".$inquiry['admid']."' ORDER BY lastactivity DESC"));
			if ( ! empty($lastsess['last']) AND $lastsess['last'] >= $inquiry['adlast'])
			{
				$outlast = $db->query("UPDATE ".$basepref."_admin SET adlast='".$lastsess['last']."' WHERE admid = '".$inquiry['admid']."'");
			}
		}
		return $outlast;
	}

	/**
	 * Функция обновления сессии
	 */
	function update($hash)
	{
		global $db, $basepref;

		$no_exit = 0;
		if ($hash != '' AND strlen($hash) == 32)
		{
			$hash = preparse($hash, THIS_TRIM);
			$last = NEWTIME - LIFE_ADMIN;
			$db->query("DELETE FROM ".$basepref."_admin_sess WHERE lastactivity < '".$last."'");
			$sess = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_admin_sess WHERE hash = '".$db->escape($hash)."' AND lastactivity >= '".$last."' AND ipadd = '".THIS_REALIP."'"));
			if($sess['hash'] != '')
			{
				$this->hash = $sess['hash'];
				$db->query("UPDATE ".$basepref."_admin_sess SET lastactivity = '".NEWTIME."' WHERE hash = '".$this->hash."'");
				if (isset($_COOKIE[ACOOKIE]))
				{
					list($sess_adm, $sess_log, $sess_pwd) = unserialize($_COOKIE[ACOOKIE]);
					$sessadm = preparse($sess_adm, THIS_INT);
					$sesslog = preparse_dn($sess_log);
					$sesspwd = preparse_dn($sess_pwd);
					$inquiry = $db->fetchrow($db->query("SELECT admid, adpwd FROM ".$basepref."_admin WHERE adlog = '".$sesslog."'"));
				}
				else
				{
					return 1;
				}
				if (isset($inquiry['admid']))
				{
					$admins = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'apanel' AND setname = 'apanelset'"));
					$label = Json::decode($admins['setval']);
					if (is_array($label) AND key_exists($inquiry['admid'], $label))
					{
						$this->skin = $label[$inquiry['admid']]['skin'];
						$this->icon = $label[$inquiry['admid']]['icon'];
					}
					else
					{
						$this->skin = SKIN_DEF;
						$this->icon = 'yes';
					}
				}
				else
				{
					$this->skin = SKIN_DEF;
					$this->icon = 'yes';
				}
				$pwd = md5($inquiry['adpwd'].SALT_ADMIN);
				if ($pwd == $sesspwd AND isset($inquiry['admid']))
				{
					$print_cookie = serialize(array($inquiry['admid'], $sesslog, $sesspwd));
					setcookie(ACOOKIE, $print_cookie, NEWTIME + LIFE_ADMIN, ADMPATH.'/', '', 0, 1);
				}
			}
			else
			{
				$no_exit = 1;
			}
		}
		else
		{
			$no_exit = 1;
		}
		return $no_exit;
	}

	/**
	 * Функция создания сессии
	 */
	function create($id)
	{
		global $db, $basepref;

		$id = preparse($id, THIS_INT);
		$this->hash = md5(uniqid(microtime()));
		$db->query
			(
				"INSERT INTO ".$basepref."_admin_sess (hash, admid, ipadd, starttime, lastactivity) VALUES (
				 '".$this->hash."',
				 '".$id."',
				 '".THIS_REALIP."',
				 '".NEWTIME."',
				 '".NEWTIME."'
				 )"
			);
	}

	/**
	 * Функция проверки сессии
	 */
	function check($adlog, $adpwd)
	{
		global $db, $basepref, $realip;

		$adlog = preparse_dn($adlog);
		$adpwd = preparse_dn($adpwd);
		$pwd = preparse($adpwd,THIS_MD_5);
		$inquiry = $db->fetchrow($db->query("SELECT admid, adpwd FROM ".$basepref."_admin WHERE adlog = '".$db->escape($adlog)."'"));
		if ($inquiry['adpwd'] == $pwd AND isset($inquiry['admid']))
		{
			$sesspwd = md5($pwd.SALT_ADMIN);
			$print_cookie = serialize(array($inquiry['admid'], $adlog, $sesspwd));
			setcookie(ACOOKIE, $print_cookie, NEWTIME + LIFE_ADMIN, ADMPATH.'/', '', 0, 1);
			$this->create($inquiry['admid']);
			$falsead = 0;
		}
		else
		{
			$falsead = 1;
		}
		return $falsead;
	}
}
