<?php
/**
 * File:        /mod/user/lost.php
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
global	$dn, $to, $db, $basepref, $config, $lang, $usermain, $userapi, $tm, $sitem, $login,
		$passw, $lostmail, $captcha, $id, $code, $redirect, $redirectcpu;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект, регистрация отключена или пользователь уже вошёл
 */
if (defined('USER_LOGGED') OR ! defined('USER_DANNEO') OR ! defined('REGTYPE'))
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метки login.php
 */
$legaltodo = array('index', 'lost', 'send', 'relost');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 *--------------*/
if ($to == 'index')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' - '.$lang['rest_pass']);
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].' - '.$lang['rest_pass'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['rest_pass']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Форма, в шаблон
	 */
	$tm->parseprint(array
		(
			'post_url'       => $ro->seo('index.php?dn='.WORKMOD),
			'rest_pass_hint' => $lang['rest_pass_hint'],
			'e_mail'         => $lang['e_mail'],
			'send_pass'      => $lang['email_send']
		),
		$tm->create('mod/'.WORKMOD.'/form.lost'));

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка check
 */
if ($to == 'send')
{
	$global['title'] = $global['modname'].' - '.$lang['rest_pass'];
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['rest_pass']);

	if (verify_mail($lostmail) == 0)
	{
		$tm->error($lang['bad_mail'], 0, 0);
	}

	$inq = $db->query("SELECT userid, uname, umail, blocked FROM ".$basepref."_user WHERE umail = '".$db->escape($lostmail)."' AND active = '1'");

	if ($db->numrows($inq) == 0)
	{
		$tm->error(this_text(array("lost_mail" => $lostmail), $lang['no_mail']), 0 ,0);
	}

	$item = $db->fetchassoc($inq);
	if($item['blocked'] == 1)
	{
		$tm->error(this_text(array("lost_mail" => $lostmail), $lang['ban_mail']), 0 ,0);
	}
	else
	{
		$newpass = null;
		$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < $conf['maxpass']; $i ++) {
			$newpass.= substr($chars, (mt_rand() % mb_strlen($chars)), 1);
		}

		$newpass_code = verify_code();
		$db->query("UPDATE ".$basepref."_user SET newpass = '".$newpass."', activate = '".$newpass_code."' WHERE umail = '".$db->escape($lostmail)."'");

		// Данные для отправки
		$nplink = $ro->seo('index.php?dn=user&re=lost&to=relost&id='.$item['userid'].'&code='.$newpass_code);
		$subject = $lang['up_pass_subject'].' - '.$config['site'];
		$message = this_text(array
					(
						"br"       => "\r\n",
						"uname"    => $item['uname'],
						"npass"    => $newpass,
						"nplink"   => $nplink,
						"site"     => $config['site'],
						"site_url" => SITE_URL
					),
					$lang['up_pass_msgtext']);

		// Отправка
		send_mail($lostmail, $subject, $message, $config['site']." <".$config['site_mail'].">");

		// Сообщение, ОК
		$tm->message($lang['lost_pass_send'], 0, 0, 0);
	}
}

/**
 * Метка relost
 */
if ($to == 'relost')
{
	$id = preparse($id, THIS_INT);
	$code = (preparse($code, THIS_SYMNUM, 1) == 0) ? substr($code, 0, 11) : FALSE;

	$global['title'] = $global['modname'].' - '.$lang['rest_pass'];
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['rest_pass']);

	if ($id > 0 AND $code == TRUE)
	{
		$inq = $db->query("SELECT userid, newpass, activate FROM ".$basepref."_user WHERE userid = '".$id."' AND activate = '".$db->escape($code)."' AND active = '1' AND blocked = '0'");
		if ($db->numrows($inq) == 1)
		{
			$item = $db->fetchassoc($inq);
			$newpass = preparse($item['newpass'], THIS_MD_5);
			$db->query("UPDATE ".$basepref."_user SET upass = '".$newpass."', newpass = '', activate = '' WHERE userid = '".$item['userid']."'");

			// Сообщение, ОК
			$message = str_replace('{login}', $ro->seo('index.php?dn=user&re=login'), $api->siteuni($lang['up_new_pass']));
			$tm->message($message, 0, 0);
		}
		else
		{
			$tm->error($lang['bad_link_up_pass'], 0, 0);
		}
	}
	else
	{
		$tm->error($lang['bad_link_up_pass'], 0, 0);
	}
}
