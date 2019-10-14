<?php
/**
 * File:        /mod/catalog/agreement.php
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
global $to, $db, $basepref, $config, $lang, $usermain, $tm, $global;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

$ins = array();

/**
 * Редирект, добавление отключено
 */
if ($conf['buy'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $lang['agreement'];
$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['agreement']);

/**
 * Вывод на страницу, шапка
 */
$tm->header();

$item = $db->fetchrow($db->query("SELECT setval FROM ".$basepref."_settings WHERE setname = 'agreement'"));
$ins['text'] = $api->siteuni($item['setval']);
$ins['domain'] = parse_url(SITE_URL, PHP_URL_HOST);

$ins['text'] = this_text(array
					(
						'site'   => $config['site'],
						'domain' => $ins['domain']
					),
					$ins['text']);

$tm->parseprint(array('text' => $ins['text']), $tm->create('mod/'.WORKMOD.'/agreement'));

/**
 * Вывод на страницу, подвал
 */
$tm->footer();
