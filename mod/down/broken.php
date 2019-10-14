<?php
/**
 * File:        /mod/down/broken.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.4
 * @copyright   (c) 2005-2017 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные
 */
global $dn, $to, $db, $basepref, $config, $lang, $usermain, $tm, $sitem, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

$id = preparse($id, THIS_INT);

/**
 * Обработка, если все корректно
 --------------------------------- */
if ($conf['broken'] == 'yes' AND $id > 0)
{
	$ins = array();

	$valid = $db->query
				(
					"SELECT a.id, a.cpu, a.acc, a.groups, b.access, b.catcpu, b.groups AS catgroups
					 FROM ".$basepref."_".WORKMOD." AS a
					 LEFT JOIN ".$basepref."_".WORKMOD."_cat AS b ON (a.catid = b.catid)
					 WHERE a.id = '".$id."' AND a.act = 'yes'"
				);

	$item = $db->fetchassoc($valid);

	/**
	 * Ошибка, страницы не существует
	 */
	if ($db->numrows($valid) == 0) {
		$tm->noexistprint();
	}

	/**
	 * Ограничение доступа
	 */
	if($item['access'] == 'user' OR $item['acc'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($item['groups']))
		{
			$group = Json::decode($item['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
		if (defined('GROUP_ACT') AND ! empty($item['catgroups']))
		{
			$group = Json::decode($item['catgroups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	$colsbrok = $conf['colbroken']; // Количество сообщений
	$pasttime = NEWTIME - $conf['brokentime']; // 86400 - сутки

	$todaybroken = $db->query("SELECT brokid FROM ".$basepref."_".WORKMOD."_broken WHERE brokip = '".REMOTE_ADDRS."' AND broktime >= '".$pasttime."'");

	/**
	 * Ошибка, превышено количество сообщений
	 */
	if ($db->numrows($todaybroken) >= $colsbrok)
	{
		$tm->error($lang['message_limit'], $lang['senk_title'], 0, 1, 0);
	}

	/**
	 * Не более одного сообщения с одного IP-адреса
	 */
	$timebroken = $db->query
					(
						"SELECT brokid FROM ".$basepref."_".WORKMOD."_broken
						 WHERE brokip = '".REMOTE_ADDRS."'
						 AND id = '".$item['id']."'
						 AND broktime >= '".$pasttime."'"
					);

	/**
	 * Ошибка, Вы уже сообщали
	 */
	if ($db->numrows($timebroken) > 0)
	{
		$tm->error($lang['senk_old'], $lang['senk_title'], 0, 1, 0);
	}

	/**
	 * Если отправлять E-Mail
	 */
	if ($conf['brokenmail'] == 'yes')
	{
		$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
		$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

		$subject = $lang['dbroken_subject'].' - '.$config['site'];
		$blink = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])."\n";
		$message = this_text(array
					(
						"br"    => "\r\n",
						"blink" => $blink,
					),
					$lang['dbroken_msgtext']);

		send_mail($config['site_mail'], $subject, $message, $config['site']." <robot.".$config['site_mail'].">");
	}

	/**
	 * Запись в базу
	 */
	$db->query("INSERT INTO ".$basepref."_".WORKMOD."_broken VALUES (NULL, '".$item['id']."', '".REMOTE_ADDRS."', '".NEWTIME."')");

	/**
	 * Сообщение, ОК
	 */
	$tm->message($lang['senk_message'], $lang['senk_title']);

}
else
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}
