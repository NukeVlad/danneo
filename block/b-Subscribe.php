<?php
/**
 * File:        /block/b-Subscribe.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $lang, $config;

$bc = null;
$lang['block_subscribe'] = isset($lang['block_subscribe']) ? $lang['block_subscribe'] : 'Subscribe';

if (defined('SETTING'))
{
	return $bs = array('blockname' => $lang['block_subscribe']);
}

$tm->unmanule['captcha'] = ($config['captcha'] == 'yes' AND defined('REMOTE_ADDRS')) ? 'yes' : 'no';
$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';

$ins['template'] = $tm->parsein($tm->create('mod/subscribe/form.block'));

$control = $cid = null;

if ($config['control'] == 'yes')
{
	if (isset($config['controls']) AND is_array($config['controls']))
	{
		$controlarray = $config['controls'];
	}
	else
	{
		$i = 0;
		$ci = $db->query("SELECT * FROM ".$basepref."_control", $config['cachetime']);

		while ($cm = $db->fetchassoc($ci, $config['cache']))
		{
			$controlarray[$i] = array('cid'=>$cm['cid'], 'issue'=>$cm['issue']);
			$i ++;
		}
	}

	$r = @rand(0, sizeof($controlarray) - 1);
	$control = @$controlarray[$r]['issue'];
	$cid = @$controlarray[$r]['cid'];
}

if (isset($config['mod']['subscribe']))
{
	$bc.= $tm->parse(array
		(
			'post_url'              => $ro->seo('index.php?dn=subscribe'),
			'subscribe_your_name'   => $lang['subscribe_your_name'],
			'subscribe_your_mail'   => $lang['subscribe_your_mail'],
			'subscribe_your_format' => $lang['subscribe_your_format'],
			'subscribe_button'      => $lang['subscribe_button'],
			'all_refresh'           => $lang['all_refresh'],
			'control_word'          => $lang['control_word'],
			'captcha'               => $lang['all_captcha'],
			'control'               => $control,
			'cid'                   => $cid
		),
		$ins['template']);

}
else
{
	$bc .= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
