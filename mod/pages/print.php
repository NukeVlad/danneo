<?php
/**
 * File:        /mod/pages/print.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $id;
global $tm, $ro, $to, $pa, $id, $cpu, $p, $config, $global, $api;

/**
 * Константы, Рабочий мод
 */
define('WORKMOD', isset($pa) ? $pa : 'pages');
define('DIRMOD', (isset($pa) AND $pa != 'pages') ? DNDIR.'cache/pages/'.WORKMOD : DNDIR.'cache/pages');

/**
 * Редирект, если печать запрещена
 */
if ($config['pages']['print'] == 'no')
{
	redirect($ro->seo('index.php?dn=pages&amp;pa='.WORKMOD));
}

/**
 * Массивы
 */
$ins = $data = array();

/**
 * ID страницы
 */
$id = preparse($id, THIS_INT);

/**
 * Файл страницы
 */
$page = DIRMOD.'/'.WORKMOD.'.'.$id.'.php';

/**
 * Страницы не существует
 */
if ( ! file_exists($page) OR ! isset($id) OR empty($id))
{
	//$tm->noexistprint();
}

/**
 * Массив с данными страницы
 */
$data = include ($page);

/**
 * Ограничение доступа
 */
if($data['acc'] == 'user')
{
	if ( ! defined('USER_LOGGED'))
	{
		$tm->noaccessprint();
	}
	if (defined('GROUP_ACT') AND ! empty($data['groups']))
	{
		$group = Json::decode($data['groups']);
		if ( ! isset($group[$usermain['gid']]))
		{
			$tm->norightprint();
		}
	}
}

/**
 * Header
 */
header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
header('Content-Type: text/html; charset='.$config['langcharset'].'');
header('X-Powered-By: DANNEO CMS '.$config['version'].'');

/**
 * Мета данные
 */
$ins['site_title'] = $api->siteuni($config['site']);
$ins['site_title'].= ( ! empty($global['modname'])) ? ' | '.$api->siteuni($global['modname']) : '';
$ins['site_title'].= ( ! empty($obj['catname'])) ? ' | '.$obj['catname'] : '';
$ins['site_title'].= ( ! empty($data['title'])) ? ' | '.preparse($data['title'], THIS_TRIM) : '';
$ins['keywords']   = ( ! empty($data['keywords'])) ? $api->sitedp($data['keywords']) : '';
$ins['descript']   = ( ! empty($data['descript'])) ? $api->sitedp($data['descript']) : '';
$ins['canonical']  = ( ! empty($ins['url'])) ? $ins['url'] : '';

/**
 * Переключатели
 */
$tm->unmanule['date'] = ($config['pages']['date'] == 'yes') ? 'yes' : 'no';

/**
 * Вложенные шаблоны
 */
$tm->manuale = array
	(
		'files' => null,
		'social' => null
	);

/**
 * Переменные
 */
$ins['image'] = $ins['mod'] = null;

/**
 * Шаблон
 */
$ins['template'] = $tm->parsein($tm->create('print'));

/**
 * Мод
 */
if ($data['mod'] != 'pages')
{
	$set = $name = array();
	$set = Json::decode($config['pages']['mods']);
	foreach($set as $v)
	{
		$name[$v['mod']] = $v['name'];
	}
	$ins['mod'] = $tm->parse(array(
						'mod_url'  => $ro->seo('index.php?dn=pages&amp;pa='.WORKMOD),
						'mod_name' => $api->siteuni($name[$data['mod']])
					),
					$tm->manuale['mod']);
}

/**
 * Изображение
 */
if ( ! empty($data['image_thumb']))
{
	$ins['float'] = ($data['image_align'] == 'left') ? 'imgleft' : 'imgright';
	$ins['alt']   = ( ! empty($data['image_alt'])) ? $api->siteuni($data['image_alt']) : '';

	$ins['image'] = $tm->parse(array(
							'float' => $ins['float'],
							'thumb' => $data['image_thumb'],
							'alt'   => $ins['alt']
						),
						$tm->manuale['thumb']);
}

/**
 * Ссылка на страницу
 */
$ins['url'] = $ro->seo('index.php?dn=pages'.((WORKMOD != 'pages') ? '&amp;pa='.WORKMOD : '').'&amp;cpu='.$data['cpu']);

/**
 * Содержимое
 */
$ins['text'] = $data['textshort'].$data['textmore'];

/**
 * Изображения по тексту
 */
if ( ! empty($data['images']))
{
	$im = Json::decode($data['images']);
	if (is_array($im))
	{
		foreach ($im as $k => $v)
		{
			$float = 'imgtext-'.$v['align'];
			$alt = ( ! empty($v['alt'])) ? $api->siteuni($v['alt']) : '';
			$img = $tm->parse(array(
						'float' => $float,
						'thumb' => $v['thumb'],
						'alt'   => $alt
						),
						$tm->manuale['thumb']);

			$ins['text'] = $tm->parse(array('img'.$k => $img), $ins['text']);
		}
	}
}

/**
 * Вывод в шаблон
 */
$tm->parseprint(array
	(
		// head
		'langcode'     => $config['langcode'],
		'langcharset'  => $config['langcharset'],
		'site'         => $config['site'],
		'version'      => $config['version'],
		'keywords'     => $ins['keywords'],
		'descript'     => $ins['descript'],
		'canonical'    => $ins['url'],
		'site_title'   => $ins['site_title'],
		'site_url'     => SITE_URL,
		'site_temp'    => SITE_TEMP,
		// body
		'cat'          => $ins['mod'],
		'copytext'     => $lang['all_rights_reserved'],
		'print_notice' => $lang['print_notice'],
		'title'        => $api->siteuni($data['title']),
		'date'         => $data['public'],
		'image'        => $ins['image'],
		'text'         => $api->siteuni($ins['text']),
		'url'          => $ins['url'],
		// non
		'copy'         => $lang['print_copy'],
		'print_name'   => $lang['print_name'].' , '.$lang['all_data'],
		'print_button' => $lang['print_button']
	),
	$ins['template']);
