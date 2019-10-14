<?php
/**
 * File:        /mod/user/login.php
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
		$passw, $lostmail, $captcha, $id, $newpass, $redirect, $redirectcpu;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект, регистрация отключена или пользователь уже вошёл
 */
if ( ! defined('REGTYPE') OR defined('USER_LOGGED'))
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метки
 */
 $legaltodo = ( ! defined('USER_DANNEO')) ? array('index', 'check') : array('index', 'check', 'lost', 'send', 'relost');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * ------------- */
if ($to == 'index')
{
	$ins = array();

	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' - '.$lang['enter_profile']);
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].' - '.$lang['enter_profile'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array($global['modname'], $lang['enter_profile']);

	$ins['redirect'] = (defined('HTTP_REFERERS')) ? HTTP_REFERERS : $ro->seo('index.php?dn='.WORKMOD);
	if (defined('SEOURL')) {
		define('REDIRECTCPU', TRUE);
	}
	define('REDIRECT', $ins['redirect']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Форма авторизации, в шаблон
	 */
	$tm->parseprint(array
		(
			'post_url'  => $ro->seo('index.php?dn='.WORKMOD),
			'only_user' => $lang['only_user'],
			'login'     => $lang['login'],
			'reglink'   => $ro->seo('index.php?dn=user&amp;re=register'),
			'registr'   => $lang['registr'],
			'linklost'	=> $ro->seo('index.php?dn=user&amp;re=login&amp;to=lost'),
			'send_pass' => $lang['send_pass'],
			'pass'      => $lang['pass'],
			'maxname'   => $conf['maxname'],
			'maxpass'   => $conf['maxpass'],
			'send_pass' => $lang['send_pass'],
			'enter'     => $lang['enter'],
			'redirect'  => (defined('REDIRECT') ? '<input name="redirect" value="'.REDIRECT.'" type="hidden" />'.(defined('REDIRECTCPU') ? '<input name="redirectcpu" value="1" type="hidden" />' : '') : '')
		),
		$tm->create('mod/user/form.login'));

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка check
 * -------------- */
if ($to == 'check')
{
	/**
	 * Проверка имени
	 */
	$badlogin = explode('|', $conf['badname']);
	if ($userapi->checklogin($login) == 0 OR  in_array($login, $badlogin))
	{
		$bad_login = ( ! empty($reglogin) AND in_array($reglogin, $badlogin)) ? '<li>'.$lang['not_login_reg'].'</li>' : '';
		$bad_colsymbol = this_text(array
			(
				"minname" => $conf['minname'],
				"maxname" => $conf['maxname']
			),
			$lang['bad_login']);

		$error_mess = $lang['possible_reason'].'
		<ol>
			'.$bad_login.'
			<li>'.$bad_colsymbol.'</li>
			<li>'.$lang['bad_login_symbol'].'</li>
		</ol>';

		$tm->error($error_mess, $lang['isset_error'], 0);
	}

	/**
	 * Проверка пароля
	 */
	if ($userapi->checkpwd($passw) == 0)
	{
		$tm->error(
			this_text(array("minpass" => $conf['minpass'], "maxpass" => $conf['maxpass']), $lang['pass_hint'])
		);
	}

	/**
	 * Проверка на бан
	 */
	$userapi->login($login, $passw);
	if (defined("THIS_BANNED"))
	{
		$tm->error($lang['level_ban']);
	}

	/**
	 * Редирект после авторизации
	 */
	if (defined('HTTP_REFERERS')) {
		$standart = HTTP_REFERERS;
	} else {
		$standart = $ro->seo('index.php?dn='.WORKMOD);
	}
	if ( ! empty($redirect))
	{
		define('REDIRECT', $redirect);
		$standart = $redirect;
		if (preparse($redirectcpu, THIS_INT) == 1)
		{
			define('REDIRECTCPU', TRUE);
		}
	}

	/**
	 * Ошибка, форма авторизации
	 */
	if (
		preparse($userapi->usermain['logged'], THIS_INT) == 0 AND
		preparse($userapi->usermain['userid'], THIS_INT) == 0
	) {
		$tm->noaccessprint();
	}

	// Редирект
	redirect($standart);
	exit();
}

/**
 * Метка lost
 * ------------- */
if ($to == 'lost')
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
	$global['insert']['breadcrumb'] = array($lang['your_profile'], $lang['rest_pass']);

	/**
	 * Форма восстановления пароля
	 */
	$tm->header();
	$tm->parseprint(array
		(
			'rest_pass_hint' => $lang['rest_pass_hint'],
			'e_mail'         => $lang['e_mail'],
			'send_pass'      => $lang['email_send']
		),
		$tm->create('mod/'.WORKMOD.'/form.lost'));
	$tm->footer();
}

/**
 * Метка send
 * -------------- */
if ($to == 'send')
{
	/**
	 * Проверка E-Mail на валидность
	 */
	if (verify_mail($lostmail) == 0)
	{
		$tm->error($lang['bad_mail']);
	}

	/**
	 * Получение данных по E-Mail
	 */
	$lostitem = $db->query("SELECT userid, uname, umail, blocked FROM ".$basepref."_user WHERE umail = '".$db->escape($lostmail)."' AND active = '1'");

	/**
	 * Проверка E-Mail на валидность
	 */
	if ($db->numrows($lostitem) == 0)
	{
		$tm->error(
			this_text(array("lost_mail" => $lostmail), $lang['no_mail'])
		);
	}

	/**
	 * Проверка E-Mail на бан
	 */
	$lostmain = $db->fetchassoc($lostitem);
	if($lostmain['blocked'] == 1)
	{
		$tm->error(
			this_text(array("lost_mail" => $lostmail), $lang['ban_mail'])
		);
	}
	else
	{
		$newpass = '';
		$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < $conf['maxpass']; $i ++)
		{
			$newpass.= substr($chars, (mt_rand() % mb_strlen($chars)), 1);
		}

		// Обновляем пароль
		$db->query("UPDATE ".$basepref."_user SET newpass = '".$newpass."' WHERE umail = '".$db->escape($lostmail)."'");

		// Данные для отправки
		$nplink = $ro->seo('index.php?dn=user&re=login&to=relost&id='.$lostmain['userid'].'&newpass='.$newpass);
		$subject = $lang['up_pass_subject'].' - '.$config['site'];
		$message = this_text(array
					(
						"br"       => "\r\n",
						"uname"    => $lostmain['uname'],
						"npass"    => $newpass,
						"nplink"   => $nplink,
						"site_url" => SITE_URL
					),
					$lang['up_pass_msgtext']);

		// Отправка
		send_mail($lostmail, $subject, $message, $config['site'], 'robot_'.$lostmail);

		// Сообщение
		$tm->message($lang['lost_pass_send'], $sitem['mod_name'], 0);
	}
}

/**
 * Метка relost
 * --------------- */
if ($to == 'relost')
{
	$id = preparse($id, THIS_INT);
	$newpass = substr($newpass, 0, $conf['maxpass']);

	if ($id > 0 AND $userapi->checkpwd($newpass) == 1)
	{
		$lostitem = $db->query
						(
							"SELECT userid, newpass FROM ".$basepref."_user WHERE userid = '".$id."'
							 AND newpass = '".$db->escape($newpass)."'
							 AND active = '1'
							 AND blocked = '0'"
						);

		$lostmain = $db->fetchassoc($lostitem);
		if ($db->numrows($lostitem) == 1)
		{
			$newpass = preparse($lostmain['newpass'], THIS_MD_5);
			$db->query("UPDATE ".$basepref."_user SET upass = '".$newpass."', newpass = '' WHERE userid = '".$lostmain['userid']."'");

			// Сообщение, ОК
			$tm->message($api->siteuni($lang['up_new_pass']), $sitem['mod_name'], 0);
		}
		else
		{
			// NO
			$tm->error($lang['bad_link_up_pass']);
		}
	}
	else
	{
		// NO
		$tm->error($lang['bad_link_up_pass']);
	}
}
