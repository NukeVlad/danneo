<?php
/**
 * File:        /mod/user/register.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $userapi, $global, $reglogin, $regpassw,
		$regpasswconfir, $regmailconfir, $regmail, $id, $code, $cid, $respon;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект,
 * пользователь уже вошёл, регистрация отключена
 */
if (defined('USER_LOGGED') OR ! defined('USER_DANNEO') OR ! defined('REGTYPE'))
{
    redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метки register.php
 */
$legaltodo = array('index', 'check', 'act');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 */
if ($to == 'index')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' - '.$lang['registr']);
	}
	else
	{
		$global['title'] = $lang['registr'];
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].' - '.$lang['registr'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['welcome'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['registr']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Переключатели
	 */
    $tm->unmanule['captcha'] = ($config['captcha']=='yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
    $tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale['rows'] = null;

	/**
	 * Переменные
	 */
    $control = $cid = $fields = null;

	/**
	 * Шаблон
	 */
	$template = $tm->parsein($tm->create('mod/'.WORKMOD.'/form.reg'));

	/**
	 * Доп.поля
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes' AND registr = 'yes' ORDER BY posit");

	if ($db->numrows($inq) > 0)
	{
		while ($item = $db->fetchassoc($inq))
		{
			if ($item['fieldtype'] != 'apart')
			{
				$val = '';
				$name = 'fields['.$item['fieldname'].']';
				$required = ($item['requires'] == 'yes') ? ' required' : '';
				$un = ($item['requires'] == 'yes') ? 'required' : 'norequired';

				if ($item['fieldtype'] == 'text')
				{
					$val.= '<input class="width" type="text" id="'.$name.'" name="'.$name.'" maxlength="'.$item['maxlen'].'" value=""'.$required.'>';
				}
				if ($item['fieldtype'] == 'textarea')
				{
					$val.= '<textarea class="width" cols="30" rows="10" id="'.$name.'" name="'.$name.'"'.$required.'></textarea>';
				}
				if ($item['fieldtype'] == 'radio')
				{
					$list = Json::decode($item['fieldlist']);
					foreach ($list as $k => $v) {
						$val.= '<input type="radio" id="'.$name.'" name="'.$name.'" value="'.$k.'">'.$v.'<br />';
					}
				}
				if ($item['fieldtype'] == 'select')
				{
					$list = Json::decode($item['fieldlist']);
					$val.= '<select name="'.$name.'" id="'.$name.'" >';
					foreach($list as $k => $v) {
						$val.= '<option value="'.$k .'">'.$v.'</option>';
					}
					$val.= '</select>';
				}
				if ($item['fieldtype'] == 'date')
				{
					$val.= '<select name="'.$name.'[day]">';
					for ($i = 1; $i < 32; $i ++) {
						$val.= '<option value="'.$i.'">'.$i.'</option>';
					}
					$val.= '</select>&nbsp;';
					$val.= '<select name="'.$name.'[month]">';
					for ($i = 1; $i < 13; $i ++) {
                        $val.= '<option value="'.$i.'">'.$i.'</option>';
					}
					$val.= '</select>&nbsp;';
					$val.= '<select name="'.$name.'[year]">';
					for($i = 1928; $i < (NEWYEAR + 1); $i ++) {
						$val.= '<option value="'.$i.'">'.$i.'</option>';
					}
					$val.= '</select>';
				}
				$fields.= $tm->parse(array(
										'name'     => $item['name'],
										'key'      => $item['fieldname'],
										'field'    => $val,
										'required' => $tm->manuale[$un]
										),
										$tm->manuale['rows']);
			}
		}
	}

	/**
	 * Контрольный вопрос
	 */
	$control = send_quest();

	/**
	 * Форма, в шаблон
	 */
	$tm->parseprint(array
		(
			'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
			'all_to_write' => $lang['all_to_write'],
			'login'        => $lang['login'],
			'login_hint'   => $lang['login_hint'],
			'pass'         => $lang['pass'],
			're_pass'      => $lang['re_pass'],
			'pass_hint'    => $lang['pass_hint'],
			'e_mail'       => $lang['e_mail'],
			're_e_mail'    => $lang['re_e_mail'],
			'mail_hint'    => $lang['mail_hint'],
			'maxname'      => $conf['maxname'],
			'maxpass'      => $conf['maxpass'],
			'minpass'      => $conf['minpass'],
			'minname'      => $conf['minname'],
			'send_pass'    => $lang['send_pass'],
			'all_refresh'  => $lang['all_refresh'],
			'control_word' => $lang['control_word'],
			'captcha'      => $lang['all_captcha'],
			'help_captcha' => $lang['help_captcha'],
			'help_control' => $lang['help_control'],
			'not_empty'    => $lang['all_not_empty'],
			'user_reg'     => $lang['user_register'],
			'control'      => $control['quest'],
			'cid'          => $control['cid'],
			'fields'       => $fields,
			'further'      => $lang['further']
		),
		$template);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка check
 */
if ($to == 'check')
{
	$global['title'] = $global['modname'].' - '.$lang['registr'];
	$global['insert']['current'] = $lang['admin_nu_subject'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn=user&amp;re=register').'">'.$lang['registr'].'</a>');

	/**
	 * captcha
	 */
	if ($config['captcha'] == 'yes')
	{
		if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
		{
			$tm->error($lang['bad_captcha'], 0, 0);
		}
	}

	/**
	 * control
	 */
	$cid = preparse($cid, THIS_INT);
	if ($config['control'] == 'yes')
	{
		$valid = $db->fetchassoc($db->query("SELECT * FROM ".$basepref."_control WHERE cid = '".$cid."'"));

		if ($valid['cid'] > 0 AND $valid['response'] != $respon OR empty($valid))
		{
			$tm->error($lang['bad_control'], 0, 0);
		}
	}

	/**
	 * Проверка имени
	 */
	$badlogin = explode('|', $conf['badname']);
	if (
		verify_name($reglogin) == 0 OR
		verify_name($reglogin) == 1 AND
		in_array($reglogin, $badlogin)
	) {
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
	 * Проверка имени среди уже существующих
	 */
	if ($db->numrows($db->query("SELECT uname FROM ".$basepref."_user WHERE uname = '".$db->escape($reglogin)."'")) > 0)
	{
		$tm->error($lang['bad_login_user'], 0, 0);
	}

	/**
	 * Проверка пароля
	 */
	if (verify_pwd($regpassw) == 0)
	{
		$bad_pass = this_text(array
			(
				"minpass" => $conf['minpass'],
				"maxpass" => $conf['maxpass']
			),
			$lang['pass_hint']);

		$tm->error($bad_pass, 0, 0);
	}

	/**
	 * Проверка валидности e-mail
	 */
	if (verify_mail($regmail) == 0)
	{
		$tm->error($lang['bad_mail'], 0, 0);
	}

	/**
	 * Проверка e-mail среди уже существующих
	 */
	if ($db->numrows($db->query("SELECT umail FROM ".$basepref."_user WHERE umail = '".$db->escape($regmail)."'")) > 0)
	{
		$tm->error($lang['bad_mail_user'], 0, 0);
	}

	/**
	 * Поля
	 */
	$inqure = $db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes' AND registr = 'yes'");

	$checkfield = $newfield = array();

	if ($db->numrows($inqure) > 0)
	{
		$error = 0;
		$list = '';

		while ($item = $db->fetchassoc($inqure)) {
			$checkfield[$item['fieldid']] = $item;
		}

		foreach($checkfield as $k => $v)
		{
			if (isset($fields[$v['fieldname']]))
			{
				if ($v['fieldtype'] == 'text')
				{
					if ($v['method'] == 'text')
					{
						if ($v['requires'] == 'yes')
						{
							$newfield[$v['fieldid']] = (mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\pL\pNd\s\.(),!?]+$/u', $fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							if ( ! $newfield[$v['fieldid']]) {
								$error = 1;
								$list.= $v['name'].', ';
							}
						}
						else
						{
							$newfield[$v['fieldid']] = $api->siteuni($fields[$v['fieldname']]);
						}
					}
					if ($v['method'] == 'email')
					{
						$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}
					if ($v['method'] == 'number')
					{
						$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\d]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}
					if($v['method'] == 'phone')
					{
						$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[0-9\s\.\+()-]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}
				}

				if ($v['fieldtype'] == 'textarea')
				{
					if ($v['requires'] == 'yes')
					{
						$newfield[$v['fieldid']] = (strlen($fields[$v['fieldname']]) < $v['minlen'] OR strlen($fields[$v['fieldname']]) > $v['maxlen']) ? '' : $api->siteuni($fields[$v['fieldname']]);
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}
					else
					{
						$newfield[$v['fieldid']] = $api->siteuni($fields[$v['fieldname']]);
					}
				}

				if ($v['fieldtype'] == 'select' OR $v['fieldtype'] == 'radio')
				{
					$newfield[$v['fieldid']] = $fields[$v['fieldname']];
				}

				if ($v['fieldtype']=='date')
				{
					$date = array();
					$date['d'] = (preparse($fields[$v['fieldname']]['day'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['day'],THIS_INT) > 31) ? 1 : preparse($fields[$v['fieldname']]['day'],THIS_INT);
					$date['m'] = (preparse($fields[$v['fieldname']]['month'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['month'],THIS_INT) > 12) ? 1 : preparse($fields[$v['fieldname']]['month'],THIS_INT);
					$date['y'] = (preparse($fields[$v['fieldname']]['year'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['year'],THIS_INT) > NEWYEAR) ? NEWYEAR : preparse($fields[$v['fieldname']]['year'],THIS_INT);
					$newfield[$v['fieldid']] = Json::encode($date);
				}
			}
			else
			{
				$newfield[$v['fieldid']] = '';
			}
		}

		if ($error)
		{
			$tm->error($lang['bad_fields'].'<br />'.mb_substr($list, 0, -2), 0, 0, 0);
		}
	}

	/**
	 * Создание нового пользователя
	 */
	$passw = preparse(trim($regpassw), THIS_MD_5);
	$activate = verify_code();
	$limit = NEWTIME + 604800;
	$deltime = date("d.m.Y", $limit);
	$login = preparse($reglogin, THIS_TRIM);
	$mail = preparse($regmail, THIS_TRIM);
	$insert = (sizeof($newfield) > 0) ? Json::encode($newfield) : '';
	$inq = $db->query
			(
				"INSERT INTO ".$basepref."_user VALUES (
				 NULL,
				 ".intval($conf['groupdef']).",
				 '".$db->escape($login)."',
				 '".$passw."',
				 '".$db->escape($mail)."',
				 '".NEWTIME."',
				 '0',
				 '',
				 '',
				 '',
				 '',
				 '',
				 '".$activate."',
				 '0',
				 '0',
				 '',
				 '".$insert."',
				 0,
				 0
				 )"
			);
	$userid = $db->insertid();

	/**
	 * Без активации
	 */
	if ($conf['regact'] == 'no')
	{
		$cookie = serialize(array($userid, $passw, $login));
		$urlpath = parse_url(SITE_URL);
		$cookpath = (isset($urlpath['path']) AND ! empty($urlpath['path'])) ? $urlpath['path'].'/' : DNROOT;
		setcookie(USERCOOKIE, $cookie, NEWTIME + $config['cookexpire'], $cookpath);

		$ainq = $db->query("UPDATE ".$basepref."_user SET lastvisit = '".NEWTIME."', activate = '', active = '1' WHERE userid = '".$userid."'");

		if ($conf['regmail'] == 'yes')
		{
			$subject = $lang['admin_nu_subject']." - ".$config['site'];
			$message = this_text(array
						(
							'br'		=> "\r\n",
							'ulogin'	=> $login,
							'umail'	=> $mail,
							'site_url'	=> SITE_URL
						),
						$lang['admin_nu_msgtext']);

			send_mail($config['site_mail'], $subject, $message, $config['site']." <".$config['site_mail'].">");
		}

		if ($inq AND $ainq)
		{
			$tm->message($lang['act_user_text'].' <a href="'.SITE_URL.'">'.$config['site'].'</a>.', 0, 0);
		}
		else
		{
			$tm->error($lang['unknown_error'], 0, 0);
		}
	}
	else
	{
		$alink = $ro->seo("index.php?dn=user&re=register&to=act&id=".$userid."&code=".$activate, 1)."\n\n";
		$subject = $lang['admin_nu_subject']." - ".$config['site'];

		$message = this_text(array
					(
						"br"       => "\r\n",
						"rtime"    => $deltime,
						"ulogin"   => $login,
						"upass"    => $regpassw,
						"umail"    => $mail,
						"alink"    => $alink,
						"site"     => $config['site'],
						"site_url" => SITE_URL
					),
					$lang['registr_act_msgtext']);

		// Send
		send_mail($mail, $subject, $message, $config['site']." <".$config['site_mail'].">");

		// ОК
		$tm->message($lang['congr_user_text'].' <a href="'.SITE_URL.'">'.$config['site'].'</a>.', 0, 0);
	}
}

/**
 * Метка check
 */
if ($to == 'act')
{
	$id = preparse($id, THIS_INT);
	$code = (preparse($code, THIS_SYMNUM, 1) == 0) ? substr($code, 0, 11) : FALSE;

	if ($id == 0 OR $code == FALSE)
	{
		redirect($ro->seo('index.php?dn='.WORKMOD));
	}

	$global['title'] = $global['modname'].' - '.$lang['registr'];
	$global['insert']['current'] = $lang['admin_nu_subject'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn=user&amp;re=login').'">'.$lang['enter_profile'].'</a>');

	$inq = $db->query("SELECT * FROM ".$basepref."_user WHERE userid = '".$db->escape($id)."' AND activate = '".$db->escape($code)."'");

	if ($db->numrows($inq) == 1)
	{
		$item = $db->fetchassoc($inq);
		$db->query("UPDATE ".$basepref."_user SET lastvisit = '".NEWTIME."', activate = '', active = '1' WHERE userid = '".$item['userid']."'");

		if ($conf['regmail'] == 'yes')
		{
			$subject = $lang['admin_nu_subject']." - ".$config['site'];
			$message = this_text(array
						(
							"br"       => "\r\n",
							"ulogin"   => $item['uname'],
							"umail"    => $item['umail'],
							"site"     => $config['site'],
							"site_url" => SITE_URL
						),
						$lang['admin_nu_msgtext']);

			send_mail($config['site_mail'], $subject, $message, $config['site']." <".$config['site_mail'].">");
		}

		// ОК
		$tm->message($lang['act_user_text'], 0, 0);

		// Редирект
		$tm->redirectprint($lang['act_user_title'], 5, $lang['act_user_text'], $ro->seo('index.php?dn='.WORKMOD));
	}
	else
	{
		// NO
		$tm->error($lang['bad_act_key'], 0, 0, 0);
	}
}
