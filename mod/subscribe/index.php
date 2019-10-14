<?php
/**
 * File:        /mod/subscribe/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Глобальные
 */
global	$dn, $to, $db, $basepref, $global, $config, $lang, $usermain, $tm, $ro,
		$subname, $submail, $captcha, $respon, $id, $sa;

/**
 * Метки
 */
$legaltodo = array('index', 'check', 'act', 'unsub', 'uncheck', 'del');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * -------------- */
if ($to == 'index')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom']);
	} else {
		$global['title'] = $global['modname'];
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = (defined('CUSTOM')) ? CUSTOM : $global['modname'];
	if ( ! empty($config['mod'][WORKMOD]['map'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['map']);
	} elseif ( ! empty($config['mod'][WORKMOD]['descript'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['descript']);
	}

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['insert']['breadcrumb'] = $global['modname'];

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	$ins = array();
	$ins['old'] = NEWTIME - 604800;
	$db->query("DELETE FROM ".$basepref."_subscribe_users WHERE subactive = '0' AND regtime < ".$ins['old']);

	/**
	 * Переключатели
	 */
	$tm->unmanule['captcha'] = ($config['captcha']=='yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
	$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';

    $control = $cid = '';

	/**
	 * Контрольный вопрос
	 */
	$control = send_quest();

	/**
	 * Форма, в шаблон
	 */
	$tm->parseprint(array
		(
			'post_url'              => $ro->seo('index.php?dn='.WORKMOD),
			'subtitle'              => $global['modname'],
			'subscribe_your_name'   => $lang['email_name'],
			'subscribe_your_mail'   => $lang['e_mail'],
			'subscribe_your_format' => $lang['subscribe_your_format'],
			'subscribe_button'      => $lang['subscribe_button'],
			'all_refresh'           => $lang['all_refresh'],
			'control_word'          => $lang['control_word'],
			'captcha'               => $lang['all_captcha'],
			'help_captcha'          => $lang['help_captcha'],
			'help_control'          => $lang['help_control'],
			'not_empty'             => $lang['all_not_empty'],
			'select'                => $lang['all_select'],
			'control'               => $control['quest'],
			'cid'                   => $control['cid']
		),
		$tm->parsein($tm->create('mod/'.WORKMOD.'/form')));

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
	$ins = array();

	$ins['format'] = (preparse($subformat, THIS_INT) == 1) ? 1 : 0;
	$ins['login'] = explode('|', $config['user']['badname']);

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
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['subscribe_act_title'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['subscribe_act_title']);

	/**
	 * Проверка captcha
	 */
	if ($config['captcha'] == 'yes')
	{
		if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
		{
			$tm->error($lang['bad_captcha']);
		}
	}

	/**
	 * Проверка контрольного вопроса
	 */
	check_quest($cid, $respon);

	/**
	 * Проверка запрещенных имен
	 */
	if ($userapi->checklogin($subname) == 0 OR in_array($subname, $ins['login']))
	{
		$ins['plus'] = ( ! empty($subname) AND in_array($subname, $ins['login'])) ? '<b>'.$lang['not_login_reg'].'</b><br />' : '';
		$bad_login = this_text(array(
			"minname" => $config['minname'],
			"maxname" => $config['maxname']
		), $lang['bad_login']);

		$tm->error($ins['plus'].$bad_login.'<br />'.$lang['bad_login_symbol']);
	}

	/**
	 * Проверка E-Mail
	 */
	if (verify_mail($submail) == 0)
	{
		$tm->error($lang['bad_mail']);
	}

	if ($db->tables($WORKMOD."_user"))
	{
		$ins['usermail'] = $db->numrows($db->query("SELECT userid FROM ".$basepref."_user WHERE umail = '".$db->escape($submail)."'"));
	}

	$ins['subsmail'] = $db->numrows($db->query("SELECT subuserid FROM ".$basepref."_subscribe_users WHERE submail = '".$db->escape($submail)."'"));
	$ins['mailnoact'] = $db->numrows($db->query("SELECT subuserid FROM ".$basepref."_subscribe_users WHERE subactive= '0' AND submail = '".$db->escape($submail)."'"));

	/**
	 * E-Mail уже подписан
	 */
	if ($ins['mailnoact'] == 1)
	{
		$tm->error($lang['subscribe_mail_noact'], 1, 0, 1);
	}

	/**
	 * E-Mail уже подписан
	 */
	if ((isset($ins['usermail']) AND $ins['usermail'] > 0) OR $ins['subsmail'] > 0)
	{
		$tm->error($lang['subscribe_error_mail'], 1, 0, 1);
	}

	/**
	 * Код активации
	 */
	$sa = verify_code();

	$db->query
		(
			"INSERT INTO ".$basepref."_subscribe_users VALUES (
			 NULL,
			 '".$db->escape($subname)."',
			 '".$db->escape($submail)."',
			 '".$ins['format']."',
			 '".$sa."',
			 '0',
			 '".NEWTIME."'
			 )"
		);

	/**
	 * Данные для отправки
	 */
	$from = $config['site']." <".$config['site_mail'].">";
	$ins['new'] = $db->insertid();
	$ins['link'] = $ro->seo('index.php?dn=subscribe&to=act&id='.$ins['new'].'&sa='.$sa);
	$ins['subject'] = $lang['subscribe_act_subject']." - ".$config['site'];
	$ins['message'] = this_text(array
						(
							"br"      => "\r\n",
							"subname" => $subname,
							"submail" => $submail,
							"sublink" => $ins['link'],
							"site"    => $config['site']
						),
						$lang['subscribe_act_msgtext']);

	/**
	 * Отправка
	 */
	send_mail($submail, $ins['subject'], $ins['message'], $from);

	/**
	 * Сообщение ОК
	 */
	$tm->message($lang['congr_subscribe_text'], $lang['subscribe_demand_ok'], 0);
}

/**
 * Метка act
 * ------------ */
if ($to == 'act')
{
	$id = preparse($id, THIS_INT);

	$sa = (preparse($sa, THIS_SYMNUM) == 0) ? $db->escape(substr($sa, 0, 11)) : 0;
	$inq = $db->query("SELECT * FROM ".$basepref."_subscribe_users WHERE subuserid = '".$id."' AND subcode = '".$sa."'");

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
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['subscribe_act_title'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['subscribe_act_title']);

	/**
	 * Ошибка
	 */
	if ($db->numrows($inq) == 0)
	{
		$tm->error($lang['subscribe_link_bad'], 1, 0, 0);
	}

	/**
	 * Активируем подписчика
	 */
	$db->query("UPDATE ".$basepref."_subscribe_users SET subcode = '', subactive = '1' WHERE subuserid = '".$id."'");

	/**
	 * Сообщение ОК
	 */
	$tm->message($lang['subscribe_act_text'], $lang['subscribe_confirm'], 0);
}

/**
 * Метка unsub
 * ------------ */
if ($to == 'unsub')
{
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
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['unsubscribe'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['unsubscribe']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	$ins = array();
	$ins['old'] = NEWTIME - 604800;
	$db->query("DELETE FROM ".$basepref."_subscribe_users WHERE subactive = '0' AND regtime < ".$ins['old']);

	/**
	 * Форма, в шаблон
	 */
	$tm->parseprint(array
		(
			'title' => $lang['unsubscribe'],
			'post_url' => $ro->seo('index.php?dn='.WORKMOD),
			'unsubscribe'	=> $lang['email_send']
		),
		$tm->parsein($tm->create('mod/'.WORKMOD.'/unsub')));

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка uncheck
 * -------------- */
if ($to == 'uncheck')
{
	$ins = array();

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
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['unsubscribe'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['unsubscribe']);

	/**
	 * Проверка E-Mail
	 */
	if (verify_mail($submail) == 0)
	{
		$tm->error($lang['bad_mail']);
	}

	$ingsub = $db->fetchassoc($db->query("SELECT * FROM ".$basepref."_subscribe_users WHERE submail = '".$db->escape($submail)."'"));

	/**
	 * E-Mail уже подписан
	 */
	if (empty($ingsub['submail']))
	{
		$tm->error($lang['email_not_subscribe'], 1, 0, 1);
	}

	/**
	 * Код активации
	 */
	$sa = verify_code();

	$db->query("UPDATE ".$basepref."_subscribe_users SET subcode = '".$sa."' WHERE submail = '".$db->escape($submail)."'");

	/**
	 * Данные для отправки
	 */
	$from = $config['site']." <".$config['site_mail'].">";
	$ins['link'] = $ro->seo('index.php?dn=subscribe&to=del&id='.$ingsub['subuserid'].'&sa='.$sa);
	$ins['subject'] = $lang['subscribe_opt_out']." - ".$config['site'];
	$ins['message'] = str_replace(array("<br />", "{link}", "{site}"), array("\r\n", $ins['link'], $config['site']), $lang['unsubscribe_msg']);

	/**
	 * Отправка
	 */
	send_mail($submail, $ins['subject'], $ins['message'], $from);

	/**
	 * Сообщение ОК
	 */
	$tm->message($lang['congr_unsubscribe_msg'], $lang['subscribe_demand_ok'], 0);
}

/**
 * Метка del
 * ------------ */
if ($to == 'del')
{
	$id = preparse($id, THIS_INT);

	$sa = (preparse($sa, THIS_SYMNUM) == 0) ? $db->escape(substr($sa, 0, 11)) : 0;
	$inq = $db->query("SELECT * FROM ".$basepref."_subscribe_users WHERE subuserid = '".$id."' AND subcode = '".$sa."'");

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
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['unsubscribe'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['unsubscribe']);

	/**
	 * Ошибка
	 */
	if ($db->numrows($inq) == 0)
	{
		$tm->error($lang['subscribe_link_bad'], 1, 0, 0);
	}

	/**
	 * Удаляем подписчика
	 */
	$db->query("DELETE FROM ".$basepref."_subscribe_users WHERE subuserid = '".$id."'");

	/**
	 * Сообщение ОК
	 */
	$tm->message($lang['subscribe_email_remove'], $lang['subscribe_demand_ok'], 0);
}
