<?php
/**
 * File:        /mod/pages/load.php
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
global $config, $lang, $tm, $db, $basepref, $usermain, $pa, $id, $fid, $ds;

/**
 * Константы, Рабочий мод
 */
define('WORKMOD', isset($pa) ? $pa : 'pages');
define('DIRMOD', isset($pa) ? DNDIR.'cache/pages/'.WORKMOD : DNDIR.'cache/pages');

/**
 * Файл страницы
 */
$page = DIRMOD.'/'.WORKMOD.'.'.$id.'.php';

/**
 * Страницы не существует
 */
if ( ! file_exists($page) OR ! isset($id) OR empty($id))
{
	$tm->noexistprint();
}

/**
 * Данные страницы
 */
$data = include($page);

/**
 * Свой TITLE
 */
if ( ! empty($data['customs'])) {
	define('CUSTOM', $api->siteuni($data['customs']));
} else {
	$global['title'] = preparse($data['title'], THIS_TRIM);
}

/**
 * Мета данные
 */
$global['keywords'] = (preparse($data['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($data['keywords']) : $api->seokeywords($data['title'].' '.$data['textshort'].' '.$data['textmore'], 5, 35);
$global['descript'] = (preparse($data['descript'], THIS_EMPTY) == 0) ? $api->siteuni($data['descript']) : '';

/**
 * Меню, хлебные крошки
 */
if (WORKMOD == 'pages') {
 $global['insert']['current'] = $data['title'];
 $global['insert']['breadcrumb'] = array($data['title'], $lang['down_file']);
} else {
	$global['insert']['current'] = $data['title'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn=pages&pa='.WORKMOD).'">'.$global['modname'].'</a>', $data['title'], $lang['down_file']);
}

/**
 * Ограничение доступа
 */
if (isset($config['mod']['user']))
{
	if ( ! empty($data['files']) AND $data['facc'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint(0, $lang['enter']);
		}
		if (defined('GROUP_ACT') AND ! empty($data['fgroups']))
		{
			$group = Json::decode($data['fgroups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}
}

/**
 * Массив файлов
 */
$fs = Json::decode($data['files']);

/**
 * Текущий файл
 */
$filename = realpath($fs[$fid]['path']);

/**
 * Ошибка, файл не существует
 */
if ( ! file_exists($filename))
{
	$tm->noexistprint();
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
