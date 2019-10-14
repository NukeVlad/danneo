<?php
/**
 * File:        /mod/catalog/maker.php
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
global $to, $db, $basepref, $config, $lang, $usermain, $tm, $global, $cpu, $id, $p;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * REDIRECT
 */
if ($conf['maker'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метки
 */
$legaltodo = array('index', 'page');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * index
 */
if ($to == 'index')
{
	$ins = $area = array();

	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom']);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript']) : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['maker']);

	/**
	 * Вывод на страницу, шапка
	 */
    $tm->header();

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_maker ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['total'] = $db->numrows($inq, $config['cache']);
	while ($c = $db->fetchrow($inq, $config['cache']))
	{
		$area[$c['makid']] = $c;
	}

	/**
	 * Шаблон, производители
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/maker.standart'));

	// количество колонок
	$config['makercol'] = 3;

	foreach ($area as $k => $v)
	{
		$ins['cpu'] = (defined('SEOURL') AND $v['cpu']) ? '&amp;cpu='.$v['cpu'] : '';
		$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=maker&amp;to=page&amp;id='.$k.$ins['cpu']);
		$ins['title'] = $api->siteuni($v['makname']);
		$ins['icon'] = ( ! empty($v['icon'])) ? '<img src="'.SITE_URL.'/'.$v['icon'].'" alt="'.$ins['title'].'" />' : '';

		$ins['makernew'][] = $tm->parse(array
								(
									'icon'		=> $ins['icon'],
									'makname'	=> $ins['title'],
									'url'		=> $ins['url']
								),
								$tm->manuale['maker']);
	}

	// Вывод
	$ins['maker'] = $tm->tableprint($ins['makernew'], $config['makercol'], 0);

	$tm->parseprint(array
		(
			'title' => $lang['maker'],
			'maker' => $ins['maker']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * pa
 */
if ($to == 'page')
{
	$ins = array();
	$id = preparse($id, THIS_INT);

	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_maker WHERE cpu = '".$db->escape($cpu)."'");
		$v = 0;
	}
	else
	{
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_maker WHERE makid = '".$db->escape($id)."'");
		$v = 1;
	}
	$item = $db->fetchrow($valid);

	/**
	 * Страницы не существует
	 */
	if ($db->numrows($valid) == 0)
	{
		$tm->noexistprint();
	}
	if ( ! empty($item['cpu']) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}

	/**
	 * Свой TITLE
	 */
	if ( ! empty($item['makcustom'])) {
		define('CUSTOM', $api->siteuni($item['makcustom']));
	} else {
		$global['title'] = preparse($item['makname'], THIS_TRIM).' - '.$lang['maker'];
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = ( ! empty($item['keywords'])) ? $item['keywords'] : '';
	$global['descript'] = ( ! empty($item['descript'])) ? $item['descript'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&re=maker').'">'.$lang['maker'].'</a>', $api->siteuni($item['makname']));

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	$adds = $item['adress'].$item['phone'].$item['site'];

	$tm->unmanule['adress'] = ( ! empty($item['adress'])) ? 'yes' : 'no';
	$tm->unmanule['phone']  = ( ! empty($item['phone'])) ? 'yes' : 'no';
	$tm->unmanule['site']   = ( ! empty($item['site'])) ? 'yes' : 'no';
	$tm->unmanule['adds']   = ( ! empty($adds)) ? 'yes' : 'no';

	if (strpos($item['site'],'http://') === FALSE) {
		$item['site'] = 'http://'.$item['site'];
	}
	$ins['site'] = ( ! empty($item['site'])) ? '<a href="'.$item['site'].'">'.$api->siteuni($item['makname']).'</a>' : '';

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/maker.open'));

	$ins['icon'] = ( ! empty($item['icon'])) ? $tm->parse(array('img' => $item['icon']), $tm->manuale['icon']) : '';

	/**
	 * Вывод в шаблон
	 */
	$tm->parseprint(array
		(
			'icon'      => $ins['icon'],
			'title'     => $api->siteuni($item['makname']),
			'text'      => $api->siteuni($item['makdesc']),
			'langadress'=> $lang['adress'],
			'adress'    => $item['adress'],
			'langphone' => $lang['phone'],
			'phone'     => $item['phone'],
			'langsite'  => $lang['site'],
			'site'      => $ins['site']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
