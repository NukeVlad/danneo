<?php
/**
 * File:        /mod/map/index.php
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
global $db, $basepref, $api, $tm, $ro, $config, $global;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Моды с категориями
 */
$tables_cat = array();
$inq = $db->query("SHOW TABLES");
if ($db->numrows($inq) > 0)
{
	while ($item = $db->fetchrow($inq))
	{
		$table = str_replace($basepref.'_', '', $item[0]);
		$cat = strpos($table, 'cat', 1);
		if ($cat !== false)
		{
			$tables_cat[] = str_replace('_cat', '', $table);
		}
	}
}

$ins = array();

/**
 * Моды / Страницы
 */
$objpage = array();
if (isset($config['mod']['pages']))
{
	$mods = Json::decode($config['pages']['mods']);
	foreach($mods as $v)
	{
		$objpage[] = $v['mod'];
	}
}

/**
 * Массив модов для карты
 */
$config['map'] = array();
$mapinq = $db->query("SELECT id, file FROM ".$basepref."_mods WHERE actmap = 'yes' ORDER BY id");
while ($mapitem = $db->fetchassoc($mapinq))
{
	$config['map'][] = $mapitem['file'];
}

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

/**
 * Только активные моды
 */
$ins['map'] = NULL;
if (preparse($config['mod'], THIS_ARRAY) == 1)
{
	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat' => null,
			'link' => null,
			'title' => null,
			'subcat' => null
	);

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/read'));

	foreach ($config['mod'] as $k => $v)
	{
		if (in_array($k, $config['map']))
		{
			$ins['link'] = $ins['title'] = '';

			// Категории
			if (in_array($k, $tables_cat))
			{
				$cats = $indent = NULL; $catcache = array();
				$inq = $db->query("SELECT * FROM ".$basepref."_".$k."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
				if ($db->numrows($inq, $config['cache']) > 0)
				{
					while($item = $db->fetchassoc($inq, $config['cache'])) {
						if (isset($item['parentid'])) {
							$catcache[$item['parentid']][$item['catid']] = $item;
						} else {
							$catcache[0][$item['catid']] = $item;
						}
					}

					foreach ($catcache[0] as $cat)
					{
						$cpu = (defined('SEOURL') AND $cat['catcpu']) ? '&amp;ccpu='.$cat['catcpu'] : '';
						$catname = isset($cat['listname']) ? $cat['listname'] : $cat['catname'];
						$cats.= $tm->parse(array(
												'url_cat'  => $ro->seo('index.php?dn='.$k.'&amp;to=cat&amp;id='.$cat['catid'].$cpu),
												'name_cat' => $api->siteuni($catname)
											),
											$tm->manuale['cat']);

						if (isset($catcache[$cat['catid']]))
						{
							$depth = 0;
							foreach ($catcache[$cat['catid']] as $subcat)
							{
								$subcpu = (defined('SEOURL') AND $subcat['catcpu']) ? '&amp;ccpu='.$subcat['catcpu'] : '';
								$cats.= $tm->parse(array(
												'url_subcat'  => $ro->seo('index.php?dn='.$k.'&amp;to=cat&amp;id='.$subcat['catid'].$subcpu),
												'name_subcat' => $api->siteuni($subcat['catname']),
												'total' => $subcat['total']
											),
											$tm->manuale['subcat']);
								$depth ++;
							}
						}
					}

					$ins['link'].= $cats;
				}
			}

			if ($k == 'poll')
			{
				$inq = $db->query("SELECT id, cpu, title FROM ".$basepref."_poll WHERE act = 'yes' AND finish > '".TODAY."' ORDER BY start DESC");
				if ($db->numrows($inq) > 0)
				{
					while ($item = $db->fetchassoc($inq))
					{
						$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
						$ins['link'].= $tm->parse(array(
												'url_link'  => $ro->seo('index.php?dn=poll&amp;to=page&amp;id='.$item['id'].$ins['cpu']),
												'name_link' => $api->siteuni($item['title'])
											),
											$tm->manuale['link']);
					}
				}
			}

			if (in_array($k, $objpage))
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_pages WHERE mods = '".$k."' AND act = 'yes' ORDER BY paid DESC");
				if ($db->numrows($inq) > 0)
				{
					while ($item = $db->fetchassoc($inq))
					{
						$ins['pa'] = ($item['mods'] != 'pages') ? '&amp;pa='.$item['mods'] : '';
						$ins['link'].= $tm->parse(array(
												'url_link'  => $ro->seo('index.php?dn=pages'.$ins['pa'].'&amp;cpu='.$item['cpu']),
												'name_link' => $api->siteuni($item['title'])
											),
											$tm->manuale['link']);
					}
				}
				if ($k == 'pages') {
					$ins['title'].= $api->siteuni($v['name']);
				} else {
					$ins['title'].= $tm->parse(array(
											'url_mod'  => $ro->seo('index.php?dn=pages&amp;pa='.$k),
											'name_mod' => $api->siteuni($v['name'])
										),
										$tm->manuale['title']);
				}
			}

			if ( ! in_array($k, $objpage))
			{
				$ins['title'].= $tm->parse(array(
											'url_mod'  => $ro->seo('index.php?dn='.$k),
											'name_mod' => $api->siteuni($v['name'])
										),
										$tm->manuale['title']);
			}

			$ins['map'].= $tm->parse(array
				(
					'title'	 => $ins['title'],
					'modmap' => $v['map'],
					'link'   => $ins['link']

				),
				$ins['template']);
		}
	}
}

$mod_name = (defined('CUSTOM')) ? CUSTOM : $global['modname'];

$tm->parseprint(array(
		'title'	  => $mod_name,
		'readmap' => $ins['map']
),
$tm->create('mod/'.WORKMOD.'/index'));

/**
 * Вывод на страницу, подвал
 */
$tm->footer();
