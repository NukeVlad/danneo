<?php
/**
 * File:        /mod/catalog/ajax.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $id, $rate;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Ошибка, доступ закрыт
 */
if (
	$conf['rating'] == 'no' OR
	$conf['rateuse'] == 'user' AND ! defined('USER_LOGGED')
) {
	exit($lang['access_text']);
}

/**
 * Метки
 */
$legaltodo = array('index', 'rating');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Заголовки
 */
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Content-Type: text/plain; charset=".$config['langcharset']."");

/**
 * Метка index
 * ------------- */
if ($to == 'index') {
	echo '&nbsp;';
}

/**
 * Метка rating
 * -------------- */
if ($to == 'rating')
{
	$id = preparse($id, THIS_INT);
	$rate = preparse($rate, THIS_INT);

	/**
	 * Если рейтинг разрешен, id товара и оценка корректны
	 */
	if ($conf['rating'] == 'yes' AND $id > 0 AND $rate > 0 AND $rate < 6)
	{
		$valid = $db->query
					(
						"SELECT a.id, a.rating, a.acc, a.totalrating, a.groups, b.access, b.groups AS catgroups
						 FROM ".$basepref."_".WORKMOD." AS a LEFT JOIN ".$basepref."_".WORKMOD."_cat
						 AS b ON (a.catid = b.catid)
						 WHERE a.id = '".$id."' AND a.act = 'yes'"
					);

		if ($db->numrows($valid) == 1)
		{
			$item = $db->fetchassoc($valid);
			$db->query("DELETE FROM ".$basepref."_rating WHERE file = '".WORKMOD."' AND ratingtime < '".(NEWTIME - ($conf['ratetime'] + 1))."'");

			// Если доступ запрещен, запрещаем выставление рейтинга
			if (
				$item['access'] == 'user' AND defined('USER_LOGGED') OR
				$item['acc'] == 'user' AND defined('USER_LOGGED') OR
				$item['acc'] == 'all' OR $item['access'] == 'all'
			) {
				$itemrate = $db->query
								(
									"SELECT ratingid FROM ".$basepref."_rating WHERE (
									 file = '".WORKMOD."'
									 AND id = '".$item['id']."'
									 AND ratingip = '".REMOTE_ADDRS."'
									 AND ratingtime >= '".(NEWTIME - $conf['ratetime'])."')"
								);

				if (defined('GROUP_ACT') AND ! empty($item['groups']))
				{
					$group = Json::decode($item['groups']);
					if ( ! isset($group[$usermain['gid']])) {
						exit($lang['access_text']);
					}
				}

				if (defined('GROUP_ACT') AND ! empty($item['catgroups']))
				{
					$group = Json::decode($item['catgroups']);
					if ( ! isset($group[$usermain['gid']])) {
						exit($lang['access_text']);
					}
				}

				if ($db->numrows($itemrate) == 0)
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_rating VALUES (
							 NULL,
							 '".WORKMOD."',
							 '".$item['id']."',
							 '".REMOTE_ADDRS."',
							 '".NEWTIME."'
							 )"
						);

					$db->query
						(
							"UPDATE ".$basepref."_".WORKMOD."
							 SET rating = rating + 1, totalrating = totalrating + ".$rate."
							 WHERE id = '".$item['id']."'"
						);

					$item['rating'] = $item['rating'] + 1;
					$item['totalrating'] = $item['totalrating'] + $rate;

					$rate = round($item['totalrating'] / $item['rating']);

					// Вывод
					$tm->parseprint(array
						(
							'imgrate'     => $rate,
							'titlerate'	  => $lang['rate_'.$rate],
							'rating'      => $item['rating'],
							'totalrating' => $item['totalrating'],
							'countrating' => $lang['rate_'.$rate]
						),
						$tm->create('mod/'.WORKMOD.'/rating.ajax'));
				}
			}
		}
	}
}
exit();
