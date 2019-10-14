<?php
/**
 * File:        /mod/faq/add.php
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
global	$dn, $to, $db, $basepref, $config, $lang, $usermain, $tm, $global,
		$catid, $captcha, $author, $email, $question, $cid, $respon;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * ID
 */
$catid = preparse($catid, THIS_INT);
$cid = preparse($cid, THIS_INT);

/**
 * Редирект, добавление отключено
 */
if ($conf['addit'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $lang['faq_add_quest'];
$global['insert']['breadcrumb'] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a> <i>&#187;</i> '.$lang['faq_add_quest'];

/**
 * Доступ
 */
if($conf['adduse'] == 'user')
{
	if ( ! defined('USER_LOGGED'))
	{
		$tm->noaccessprint();
	}
}

/**
 * Отключить проверки, для списка пользователей
 */
noprotectspam(1);

/**
 * Проверка секретного кода
 */
if ($config['captcha'] == 'yes')
{
	if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
	{
		$tm->error($lang['bad_captcha'], 0);
	}
}

/**
 * Проверка контрольного вопроса
 */
check_quest($cid, $respon, 0, 0);

/**
 * Проверка на флуд
 */
$checktime = $db->fetchrow
				(
					$db->query
					(
						"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."_new
						 WHERE fip = '".REMOTE_ADDRS."' AND public >= '".(NEWTIME - $conf['addtime'])."'"
					)
				);

if ($checktime['total'] > 0)
{
	$tm->error($lang['faq_flood'], 0, 0);
}

/**
 * Проверка E-Mail
 */
if (verify_mail($email) == 0)
{
	$tm->error($lang['bad_mail'], 0);
}

/**
 * Проверка имени
 */
if (
	preparse($author, THIS_STRLEN) < $conf['minname'] OR
	preparse($author, THIS_STRLEN) > $conf['maxname']
) {
		$bad_name = this_text(array(
							'minname' => $conf['minname'],
							'maxname' => $conf['maxname']
						),
						$lang['faq_error_name']);

		$tm->error($bad_name, 0);
}

/**
 * Проверка текста сообщения
 */
if (
	preparse($question, THIS_STRLEN) < $conf['minsymbol'] OR
	preparse($question, THIS_STRLEN) > $conf['maxsymbol']
) {
	$bad_question = this_text(array(
							'minname' => $conf['minsymbol'],
							'maxname' => $conf['maxsymbol']
						),
						$lang['faq_error_text']);

	$tm->error($bad_question, 0);
}

/**
 * Добавляем в базу
 */
$inq = $db->query
		(
			"INSERT INTO ".$basepref."_".WORKMOD."_new VALUES (
			 NULL,
			 '".$catid."',
			 '".NEWTIME."',
			 '".$db->escape($author)."',
			 '".$db->escape($email)."',
			 '".$db->escape($question)."',
			 '',
			 '".$db->escape(REMOTE_ADDRS)."'
			 )"
		);

/**
 * Оправка письма
 */
if ($inq)
{
	if ($conf['sendmail'] == 'yes')
	{
		$mail = ( ! empty($cat['catmail'])) ? $cat['catmail'] : $conf['email'];
		$subject = $lang['faq_question']." - ".$config['site'];
		$message = $lang['author']." - ".$author."\r\n";
		$message.= "E-Mail - ".$email."\r\n";
		$message.= $lang['faq_question']." - ".$question."\r\n";

		send_mail($conf['email'], $subject, $message, $config['site']." <robot.".$config['site_mail'].">");
	}

	// Ok
	$tm->message($lang['faq_success'], 0);
}
else
{
	// Bad
	$tm->error($lang['system_error'], 0);
}
