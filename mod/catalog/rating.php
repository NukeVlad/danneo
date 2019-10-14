<?php
/**
 * File:        /mod/catalog/rating.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $ro, $api, $global, $id, $rate;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

$id = preparse($id, THIS_INT);
$rate = preparse($rate, THIS_INT);

/**
 * Ошибка, доступ закрыт
 */
if (
	$conf['rating'] == 'no' OR
	$conf['rateuse'] == 'user' AND
	! defined('USER_LOGGED')
) {
	$tm->error($lang['no_rights'], $lang['access_title'], 0, 1, 0);
}

/**
 * Ошибка, не выбрана оценка
 */
if ($rate == 0)
{
	$tm->error($lang['rating_error_title'], 1, 0, 1, 0);
}

/**
 * Обработка
 ------------- */
if ($conf['rating'] == 'yes' AND $id > 0 AND $rate > 0 AND $rate < 6)
{
	$valid = $db->query
				(
					"SELECT
					 a.catid, a.id, a.cpu, a.acc, a.groups,
					 b.catcpu, b.access, b.groups AS catgroups
					 FROM ".$basepref."_".WORKMOD." AS a
					 LEFT JOIN ".$basepref."_".WORKMOD."_cat AS b ON (a.catid = b.catid)
					 WHERE a.id = '".$id."' AND a.act = 'yes'"
				);

	$item = $db->fetchassoc($valid);

	/**
	 * Ошибка, страницы не существует
	 */
	if ($db->numrows($valid) == 0)
	{
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

	$db->query("DELETE FROM ".$basepref."_rating WHERE file = '".WORKMOD."' AND ratingtime < '".(NEWTIME - ($conf['ratetime'] + 1))."'");

	$timerating = $db->query
					(
						"SELECT ratingid FROM ".$basepref."_rating WHERE (
						 file = '".WORKMOD."'
						 AND id = '".$item['id']."'
						 AND ratingip = '".REMOTE_ADDRS."'
						 AND ratingtime >= '".(NEWTIME - $conf['ratetime'])."'
						 )"
					);

	/**
	 * Ошибка, уже голосовали
	 */
	if ($db->numrows($timerating) > 0)
	{
		$tm->message($lang['rating_re'], 1, 1, 0);
	}

	$db->query("INSERT INTO ".$basepref."_rating VALUES (NULL, '".WORKMOD."', '".$item['id']."', '".REMOTE_ADDRS."', '".NEWTIME."')");
	$db->query("UPDATE ".$basepref."_".WORKMOD." SET rating = rating + 1, totalrating = totalrating + ".$rate." WHERE id = '".$item['id']."'");

	/**
	 * Редирект, ОК
	 */
	$cpu    = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
	$catcpu = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

	$redirect = $ro->seo('index.php?dn='.WORKMOD.$catcpu.'&amp;to=page&amp;id='.$item['id'].$cpu);
	$message  = $lang['rating_senks'].' <a href="'.SITE_URL.'">'.$config['site'].'</a>.';

	$tm->redirectprint($lang['inf_mess'], $config['redirtime'], $message, $redirect);
}
else
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}
