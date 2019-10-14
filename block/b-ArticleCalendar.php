<?php
/**
 * File:        /block/b-ArticleCalendar.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $conf, $lang, $ro, $ye, $mo, $da;

$bc = null;
$lang['block_article_cal'] = isset($lang['block_article_cal']) ? $lang['block_article_cal'] : 'Article calendar';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_article_cal'],
	'mod' => array(
		'lang'    => 'block_mods',
		'form'    => 'text',
		'value'   => 'article',
		'default' => 'article'
	)
);

if (defined('SETTING'))
{
	return $bs;
}

/**
 * Получаем настройки
 */
if (
	isset($config['bsarray']) AND
	is_array($config['bsarray']) AND
	isset($config['mod'][$config['bsarray']['mod']])
) {
	$bs = $config['bsarray'];
	$calinq = "SELECT id, public FROM ".$basepref."_".$bs['mod']." WHERE act = 'yes' AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."')";

	$blockcal = new Calendar;
	$blockcal->CreateCalendar();
	$blockcal->OutputCalendar($bs['mod'], $calinq);

	return $blockcal->out;
}
else
{
	$bc .= $lang['data_not'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
