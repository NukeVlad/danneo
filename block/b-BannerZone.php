<?php
/**
 * File:        /block/b-BannerZone.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $lang, $api, $ro, $config;

$bc = '';

/**
 * Массив зон (только для настроек в панели)
 */
$banzon = array();
if (isset($admb) AND $admb)
{
	$inqs = $db->query("SELECT * FROM ".$basepref."_banners_zone");
	while ($link = $db->fetchrow($inqs))
	{
		$banzon[$link['banzonid']] = $link['banzonname'];
	}
}

/**
 * Настройки
 */
$bs = array
(
	'blockname' => $lang['block_banner'],
	'sort' => array
		(
			'lang'    => 'banner_zone',
			'form'    => 'select',
			'value'   => $banzon,
			'default' => '1'
		)
);

if (defined('SETTING'))
{
	return $bs;
}

/**
 * Получаем настройки
 */
if (isset($config['bsarray']) AND is_array($config['bsarray']) AND $config['banner'] == 'yes')
{
	$bs = $config['bsarray'];

	if (isset($config['bannerzone']) AND ! empty($config['bannerzone']))
	{
		$bc .= '{'.$config['bannerzone'][$bs['sort']]['code'].'}';
	}
	else
	{
		$inq = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_banners_zone WHERE banzonid = '".$bs['sort']."'"));
		$bc .= '{'.$inq['banzoncode'].'}';
	}
}
else
{
	$bc .= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
