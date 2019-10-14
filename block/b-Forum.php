<?php
/**
 * File:        /block/b-Forum.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $api, $ro, $lang, $userapi;

$bc = null;
$lang['block_forum'] = isset($lang['block_forum']) ? $lang['block_forum'] : 'Forum';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_forum'],
	'col' => array(
		'lang'    => 'col_last',
		'form'    => 'text',
		'value'   => 10,
		'default' => 10
	),
	'wrap' => array(
		'lang'		=> 'title_count',
		'form'		=> 'text',
		'value'		=> 50,
		'default'	=> 50
	),
	'order' => array(
		'lang'    => 'all_sorting',
		'form'    => 'select',
		'value'   => array('desc'=>'all_desc','asc'=>'all_acs'),
		'default' => 'desc'
	),
	'target' => array(
		'lang'    => 'Target',
		'form'    => 'select',
		'value'   => array(''=>'—','_blank'=>'_blank','_self'=>'_self','_parent'=>'_parent','_top'=>'_top'),
		'default' => ''
	),
	'author' => array(
		'lang'    => 'author',
		'form'    => 'checkbox',
		'value'   => 'yes',
		'default' => 'yes'
	),
	'replie' => array(
		'lang'    => 'of_replies',
		'form'    => 'checkbox',
		'value'   => 'yes',
		'default' => 'yes'
	),
	'hits' => array(
		'lang'    => 'all_hits',
		'form'    => 'checkbox',
		'value'   => 'yes',
		'default' => 'yes'
	),
	'last' => array(
		'lang'    => 'last_replies',
		'form'    => 'checkbox',
		'value'   => 'yes',
		'default' => 'yes'
	),
	'time' => array(
		'lang'    => 'show_time',
		'form'    => 'checkbox',
		'value'   => 'yes',
		'default' => 'no'
	)
);

if (defined('SETTING'))
{
	return $bs;
}

if (isset($config['bsarray']) AND is_array($config['bsarray']))
{
	$bs = $config['bsarray'];
}

/**
 * Вывод
 */
if(method_exists($userapi, 'messagelast') AND ! defined('USER_DANNEO'))
{
	return $userapi->messagelast($bs);
}
else
{
	return $lang['data_not'];
}
