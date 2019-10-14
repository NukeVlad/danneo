<?php
/**
 * File:        /mod/article/load.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные
 */
global $config, $tm, $db, $basepref, $usermain, $pa, $id, $fid, $ds;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * ID
 */
$id = preparse($id, THIS_INT);
$fid = preparse($fid, THIS_INT);

$valid = $db->query
			(
				"SELECT id, files, facc, fgroups FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
			);

$item = $db->fetchrow($valid);

/**
 * Страницы не существует
 */
if ($db->numrows($valid) == 0)
{
	$tm->error($lang['noexit_page_title'], 1, 0, 1, 0);
}

/**
 * Ограничение доступа
 */
if ( ! empty($item['files']) AND $item['facc'] == 'user')
{
	if ( ! defined('USER_LOGGED'))
	{
		$tm->noaccessprint();
	}
	if (defined('GROUP_ACT') AND ! empty($item['fgroups']))
	{
		$group = Json::decode($item['fgroups']);
		if ( ! isset($group[$usermain['gid']]))
		{
			$tm->norightprint();
		}
	}
}

/**
 * Массив файлов
 */
$fs = Json::decode($item['files']);

/**
 * Текущий файл
 */
$filename = realpath($fs[$fid]['path']);

/**
 * Ошибка, файл не существует
 */
if ( ! file_exists($filename))
{
	$tm->error($lang['down_na_title'], $lang['down_broken_link'], 0, 1, 0);
}

/**
 * Новое имя файла
 */
$newname = ($config['filenames'] == 'yes') ? $ds : NULL;

/**
 * Читаем и отдаем файл
 */
$file = new Files();
$file->download($filename, $newname);
