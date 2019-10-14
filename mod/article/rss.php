<?php
/**
 * File:        /mod/article/rss.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $ro, $api, $global, $ya;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * RSS запрещен, редирект
 */
if ($conf['rss'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Not found 404
 */
if (isset($ya) AND $ya != $conf['rsskey'])
{
	$tm->noexistprint();
}

/**
 * RSS yandex
 */
$global['full'] = (isset($ya) AND $ya == $conf['rsskey']) ? 1 : 0;
$ai = array();
$inq = $db->query
		(
			"SELECT a.id, a.catid, a.public, a.stpublic, a.unpublic, a.cpu, a.title, a.textshort, a.textmore, a.image_thumb, a.image_alt, a.act, b.catname, b.catcpu, b.rss
			 FROM ".$basepref."_".WORKMOD." AS a
			 LEFT JOIN ".$basepref."_".WORKMOD."_cat AS b ON (a.catid = b.catid)
			 WHERE ((a.act = 'yes' AND b.rss = 'yes') OR (a.act = 'yes' AND a.catid = 0))
			 AND (a.stpublic = 0 OR a.stpublic < '".NEWTIME."')
			 AND (a.unpublic = 0 OR a.unpublic > '".NEWTIME."')
			 ORDER BY a.id DESC LIMIT ".$conf['rsslast']
		);

$i = 0; $last = '';
while ($item = $db->fetchassoc($inq))
{
	$ai[$item['id']]['pubdate'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

	$cpu = ( ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
	$catcpu = ( ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';
	$ai[$item['id']]['link'] = $ro->seo('index.php?dn='.WORKMOD.$catcpu.'&amp;to=page&amp;id='.$item['id'].$cpu, 1);

	if ($i == 0) {
		$last = $ai[$item['id']]['pubdate'];
	}

	$ai[$item['id']]['title'] = $api->siteuni(strip_tags($item['title']));
	$ai[$item['id']]['description'] = $api->siteuni(strip_tags($item['textshort']));

	if ( ! empty($item['image_thumb']) AND file_exists(DNDIR.'/'.$item['image_thumb']))
	{
		if ($length = filesize(DNDIR.'/'.$item['image_thumb']))
		{
			if ($s = getimagesize(DNDIR.'/'.$item['image_thumb']))
			{
				if (
					$s['mime'] == 'image/jpeg' OR
					$s['mime'] == 'image/gif' OR
					$s['mime'] == 'image/png'
				) {
					$ai[$item['id']]['enclosure'] = array
						(
							'url'    => SITE_URL.'/'.$item['image_thumb'],
							'length' => $length,
							'type'   => $s['mime']
						);
				}
			}
		}
	}

	if ($global['full'])
	{
		$ai[$item['id']]['yandex'] = $api->siteuni(strip_tags($item['textshort'].' '.$item['textmore']));
	}
	$i++;
}

$rss = new RSS();

$link = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=rss', 1);
$rss->headers(
	$api->sitedp($config['site'].' '.$global['modname']),
	$api->sitedp($config['site_descript']),
	$link,
	$last
);

$rss->additem($ai);
$rss->closers();

echo $rss->out;
