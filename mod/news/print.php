<?php
/**
 * File:        /mod/news/print.php
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

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект, если печать запрещена
 */
if ($conf['print'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Массивы
 */
$obj = $ins = $area = array();

/**
 * id
 */
$id = preparse($id, THIS_INT);

/**
 * valid
 */
$valid = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
			);

/**
 * Страницы не существует
 */
if ($db->numrows($valid) == 0)
{
	$tm->noexistprint();
}

/**
 * Категории
 */
$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
while ($c = $db->fetchassoc($inq, $config['cache']))
{
	$area[$c['catid']] = $c;
}
$item = $db->fetchassoc($valid);

/**
 * Данные категории
 */
if (isset($area[$item['catid']]))
{
	$obj = $area[$item['catid']];
}
else
{
	$obj = array
			(
				'catid'		=> '',
				'parentid'	=> '',
				'catcpu'	=> '',
				'catname'	=> '',
				'icon'		=> '',
				'access'	=> '',
				'groups'	=> ''
			);
}
$api->catcache = $area;

/**
 * Ограничение доступа
 */
if ($obj['access'] == 'user' OR $item['acc'] == 'user')
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
	if (defined('GROUP_ACT') AND ! empty($obj['groups']))
	{
		$group = Json::decode($obj['groups']);
		if ( ! isset($group[$usermain['gid']]))
		{
			$tm->norightprint();
		}
	}
}

/**
 * Обновляем счетчик просмотров страницы
 */
$db->query("UPDATE ".$basepref."_".WORKMOD." SET hits = hits + 1 WHERE id='".$item['id']."'");

/**
 * Header
 */
header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
header('Content-Type: text/html; charset='.$config['langcharset'].'');
header('X-Powered-By: CMS Danneo '.$config['version'].'');

/**
 * Мета данные
 */
$ins['site_title'] = $api->siteuni($config['site']);
$ins['site_title'].= ( ! empty($global['modname'])) ? ' | '.$api->siteuni($global['modname']) : '';
$ins['site_title'].= ( ! empty($obj['catname'])) ? ' | '.$obj['catname'] : '';
$ins['site_title'].= ( ! empty($item['title'])) ? ' | '.preparse($item['title'], THIS_TRIM) : '';
$ins['keywords']   = ( ! empty($item['keywords'])) ? $api->sitedp($item['keywords']) : '';
$ins['descript']   = ( ! empty($item['descript'])) ? $api->sitedp($item['descript']) : '';
$ins['canonical']  = ( ! empty($ins['url'])) ? $ins['url'] : '';

$tm->manuale['thumb'] = $ins['image'] = null;
$tm->unmanule['date'] = 'yes';

// Шаблон
$ins['template'] = $tm->parsein($tm->create('print'));

// Вводное изображение
if ( ! empty($item['image_thumb']))
{
	$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
	$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

	$ins['image'] = $tm->parse(array(
							'float' => $ins['float'],
							'thumb' => $item['image_thumb'],
							'alt'   => $ins['alt']
						),
						$tm->manuale['thumb']);
}

// Содержимое
$ins['text'] = $item['textshort'].$item['textmore'];

// Изображения по тексту
if ( ! empty($item['images']))
{
	$im = Json::decode($item['images']);
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

// Категория
$ins['cat'] = ($item['catid'] > 0) ? $area[$item['catid']]['catname'].' &raquo; ' : '';

// cpu
$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

// Ссылка на страницу
$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

// Дата
$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

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
		'copytext'     => $lang['all_rights_reserved'],
		'print_notice' => $lang['print_notice'],
		'title'        => $api->siteuni($item['title']),
		'date'         => $ins['public'],
		'image'        => $ins['image'],
		'text'         => $api->siteuni($ins['text']),
		'url'          => $ins['url'],
		// non
		'cat'          => $ins['cat'],
		'copy'         => $lang['print_copy'],
		'print_name'   => $lang['print_name'].' , '.$lang['all_data'],
		'print_button' => $lang['print_button']
	),
	$ins['template']);
