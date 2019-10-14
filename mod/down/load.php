<?php
/**
 * File:        /mod/down/load.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.4
 * @copyright   (c) 2005-2017 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные
 */
global $config, $tm, $db, $basepref, $usermain, $ds;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Файл доп. функций
 */
require_once(DNDIR.'mod/'.WORKMOD.'/mod.function.php');

/**
 * old
 */
$old = preparse((NEWTIME - ($conf['time'] + 1)), THIS_INT);

/**
 * DELETE
 */
$db->query("DELETE FROM ".$basepref."_".WORKMOD."_sess WHERE sesstime < '".$old."'");

/**
 * ds
 */
$ds = (preparse($ds, THIS_SYMNUM) == 0) ? substr($ds, 0, 32) : 0;

/**
 * lt
 */
$lt = preparse((NEWTIME - $conf['time']), THIS_INT);

/**
 * check
 */
$check = $db->fetchrow($db->query("SELECT sessid, id FROM ".$basepref."_".WORKMOD."_sess WHERE sessid = '".$ds."' AND sessip = '".REMOTE_ADDRS."' AND sesstime > '".$lt."'"));

if ($check['sessid'] AND $check['id'])
{
	$valid = $db->query
				(
					"SELECT a.*, b.access, b.groups AS catgroups FROM ".$basepref."_".WORKMOD." AS a
					 LEFT JOIN ".$basepref."_".WORKMOD."_cat AS b ON (a.catid = b.catid)
					 WHERE a.id = '".$check['id']."' AND a.act = 'yes'
					 AND (a.stpublic = 0 OR a.stpublic < '".NEWTIME."')
					 AND (a.unpublic = 0 OR a.unpublic > '".NEWTIME."')"
				);

	$item = $db->fetchrow($valid);

	/**
	 * Страницы не существует
	 */
	if ($db->numrows($valid) == 0)
	{
		$tm->noexistprint();
	}

	/**
	 * Ограничение доступа
	 */
	if($item['access'] == 'user' OR $item['acc'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($item['groups']))
		{
			$group = Json::decode($item['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
		if (defined('GROUP_ACT') AND ! empty($item['catgroups']))
		{
			$group = Json::decode($item['catgroups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	$count = $db->fetchrow($db->query("SELECT COUNT(sessid) AS total FROM ".$basepref."_".WORKMOD."_sess WHERE id = '".$item['id']."'"));

	/**
	 * Ограничение скачиваний с одного IP-адреса
	 */
	if ( ! defined("REMOTE_ADDRS") OR $count['total'] > $conf['simult'])
	{
		$tm->error($lang['down_na_text'], $lang['down_na_title']);
	}

	$db->query("UPDATE ".$basepref."_".WORKMOD." SET trans = trans + 1, lastdown = '".NEWTIME."' WHERE id = '".$item['id']."'");

	$parse_file = parse_url($item['file']);
	if (
		! array_key_exists('scheme', $parse_file) OR
		  array_key_exists('scheme', $parse_file) AND $parse_file['host'] == SITE_HOST
	) {
		$locurl = trim($parse_file['path'], '/');

		if ( ! file_exists($locurl))
		{
			$tm->error($lang['down_na_title'], $lang['down_broken_link'], 0, 1, 0);
		}

		$obj = array();
		$obj['filesize'] = filesize($locurl);
		$obj['filepath'] = pathinfo($locurl);
		$obj['filename'] = (isset($obj['filepath']['basename'])) ? $obj['filepath']['basename'] : $locurl;
		$obj['fileext'] = mb_substr(strrchr($obj['filename'], '.'), 1);
		$obj['filetype'] = conttype($obj['fileext']);
		$obj['readsize'] = 1024 * 1024;

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: private", FALSE);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: ".$obj['filetype']."");
		header("Content-Disposition: attachment; filename=".$obj['filename']."");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$obj['filesize']);

		$obj['filefopen'] = fopen($locurl, 'rb');

		if ($obj['filefopen'] == TRUE)
		{
			while ( ! feof($obj['filefopen']) AND connection_status() == 0)
			{
				$buffer = fread($obj['filefopen'], $obj['readsize']);
				echo $buffer;
				flush();
			}
		}

		return fclose($obj['filefopen']);
		exit;
	}
	else
	{
		redirect($item['file']);
	}
}
else
{
	/**
	 * Страницы не существует | устарела сессия
	 */
	$tm->noexistprint();
}
