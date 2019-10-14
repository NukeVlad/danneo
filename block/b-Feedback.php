<?php
/**
 * File:        /block/b-Feedback.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $lang, $config, $db, $ro, $basepref, $dn, $usermain;

$bc = null;
$lang['feedback'] = isset($lang['feedback']) ? $lang['feedback'] : 'Feedback';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['feedback'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'contact',
		'default'	=>	'contact'
	),
	'attach' => array(
		'lang'		=> 'mail_attach',
		'form'		=> 'select',
		'value'		=> array('yes' => 'all_yes', 'no' => 'all_no'),
		'default'	=> 'no'
	),
	'to_email'	=> array(
		'lang'		=> 'input_email_hint',
		'form'		=> 'text',
		'hint'		=> 'to_mail_hint',
		'value'		=> '',
		'default'	=> ''
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

	// Управление
	$tm->unmanule['captcha'] = ($config['captcha']=='yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
	$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['attach'] = ($config['mail_attach'] == 'yes' AND $bs['attach'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['to'] = ( ! empty($bs['to_email'])) ? 'yes' : 'no';

	// Контрольный вопрос
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
				$controlarray[$i] = array('cid' => $cm['cid'], 'issue'=>$cm['issue']);
				$i ++;
			}
		}

		$r = @rand(0, sizeof($controlarray) - 1);
		$control = @$controlarray[$r]['issue'];
		$bcid = @$controlarray[$r]['cid'];
	}
	else
	{
		$control = $bcid = NULL;
	}

	$uname = isset($usermain['uname']) ? $usermain['uname'] : '';
	$umail = isset($usermain['uname']) ? $usermain['uname'] : '';

	// Форма
	$bc.= $tm->parse(array
			(
				'post_url'     => $ro->seo('index.php?dn=contact'),
				'email_name'   => $lang['email_name'],
				'email'        => $lang['e_mail'],
				'email_text'   => $lang['email_text'],
				'mail_hint'    => $lang['mail_hint'],
				'email_org'    => $lang['mail_org'],
				'email_phone'  => $lang['mail_phone'],
				'email_file'   => this_text(array('num' => $config['mail_file_col']), $lang['mail_file']),
				'file_help'    => $lang['mail_file_help'],
				'uname'        => $uname,
				'umail'        => $umail,
				'all_refresh'  => $lang['all_refresh'],
				'captcha'      => $lang['all_captcha'],
				'help_captcha' => $lang['help_captcha'],
				'control_word' => $lang['control_word'],
				'help_control' => $lang['help_control'],
				'not_empty'    => $lang['all_not_empty'],
				'control'      => $control,
				'cid'          => $bcid,
				'to_email'     => $bs['to_email'],
				'email_send'   => $lang['email_send']
			),
			$tm->parsein($tm->create('mod/contact/form.block')));
}
else
{
	$bc.= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $bc;
