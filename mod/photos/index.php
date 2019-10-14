<?php
/**
 * File:        /mod/photos/index.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $to, $p, $id, $ccpu, $cpu;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Файл доп. функций
 */
require_once(DNDIR.'mod/'.WORKMOD.'/mod.function.php');

/**
 * Метки
 */
$legaltodo = array('index', 'cat', 'page');

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
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript']) : '';

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

	$obj = $ins = $area = array();

	$ins = array
		(
			'category' => null,
			'new'      => null,
			'popular'  => null,
			'random'   => null,
			'toprate'  => null
		);

	$posts = FALSE;

	/**
	 * Категории
	 */

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['cats'] = $db->numrows($inq, $config['cache']);
	while ($c = $db->fetchrow($inq, $config['cache']))
	{
		$area[$c['parentid']][$c['catid']] = $obj[$c['catid']] = $c;
	}

	if ($conf['catmain'] == 'yes')
	{
		if ( ! empty($area))
		{
			$api->subcatcache = $area;
			$ins['tempcat'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat'));
			$api->printsitecat(0);
			if ( ! empty($api->print))
			{
				$stat = $db->fetchassoc
							(
								$db->query
								(
									"SELECT COUNT(id) AS total, SUM(hits) AS hits FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'
									 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
									 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
								)
							);

				$catprint = $tm->tableprint($api->print, $conf['catcol']);

				$ins['category'] = $tm->parse(array
					(
						'cd'         => $lang['cat_desc'],
						'lang_icon'	 => $lang['all_icon'],
						'lang_col'   => $lang['all_col'],
						'lang_total' => $lang['all_photo'],
						'lang_cat'   => $lang['all_cats'],
						'lang_hits'  => $lang['all_hits'],
						'catprint'   => $catprint,
						'total'      => $stat['total'],
						'hits'       => ( ! empty($stat['hits'])) ? $stat['hits'] : 0,
						'cats'       => $ins['cats']
					),
					$ins['tempcat']);
			}
		}
	}

    /**
	 * Переключатели
	 */
	$tm->unmanule['date'] = $conf['date'];
	$tm->unmanule['rating'] = $conf['rating'];
	$tm->unmanule['comment'] = $conf['comact'];
	$tm->unmanule['desc'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale['author'] = null;

	/**
	 * Описание раздела
	 */
	$ins['map'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? $config['mod'][WORKMOD]['map'] : '';

	/**
	 * Шаблоны
	 */
	$ins['section'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/index.section'));
	$ins['thumb'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/thumb'));

	/**
	 * Новые
	 */
	$inq = $db->query
			(
				"SELECT id, catid, cpu, public, stpublic, title, image_thumb, image_alt, act, rating, totalrating, hits, comments, author
				 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY id DESC LIMIT ".$conf['mainlast']
			);

	if ($db->numrows($inq) > 0)
	{
		$posts = TRUE;
		$ins['content'] = array();
		while ($item = $db->fetchrow($inq))
		{
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			$ins['alt'] = ($item['image_alt']) ? $api->siteuni($item['image_alt']) : '';
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Автор
			$ins['author'] = null;
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			$ins['content'][] = $tm->parse(array
									(
										'title'		=> $api->siteuni($item['title']),
										'date'		=> $ins['public'],
										'thumb'		=> $item['image_thumb'],
										'alt'		=> $ins['alt'],
										'url'		=> $ins['url'],
										'langrate'	=> $lang['all_rating'],
										'titlerate'	=> $ins['title_rate'],
										'rating'	=> $ins['rate'],
										'langhits'	=> $lang['all_hits'],
										'hits'		=> $item['hits'],
										'comment'	=> $lang['comment_total'],
										'count'		=> $ins['count'],
										'author'	=> $ins['author']
									),
									$ins['thumb']);
		}

		$thumb = $tm->tableprint($ins['content'], $conf['maincol']);

		// Вывод, Новые
		$ins['new'] = $tm->parse(array
			(
				'title' => $lang['all_new'],
				'thumb' => $thumb
			),
			$ins['section']);
	}

	/**
	 * Популярные
	 */
	if ($conf['popular'] == 'yes')
	{
		$inq = $db->query
				(
					"SELECT id, catid, cpu, public, stpublic, title, image_thumb, image_alt, act, rating, totalrating, hits, comments, author
					 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY hits DESC LIMIT ".$conf['mainlast']
				);

		if ($db->numrows($inq) > 0)
		{
			$posts = TRUE;
			$ins['content'] = array();
			while ($item = $db->fetchrow($inq))
			{
				$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
				$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

				$ins['alt'] = ($item['image_alt']) ? $api->siteuni($item['image_alt']) : '';
				$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

				$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
				$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

				// Рейтинг
				$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
				$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

				// Автор
				$ins['author'] = null;
				if ( ! empty($item['author']) AND $conf['author'] == 'yes')
				{
					$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
					$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
				}

				$ins['content'][] = $tm->parse(array
										(
											'title'		=> $api->siteuni($item['title']),
											'date'		=> $ins['public'],
											'thumb'		=> $item['image_thumb'],
											'alt'		=> $ins['alt'],
											'url'		=> $ins['url'],
											'langrate'	=> $lang['all_rating'],
											'titlerate'	=> $ins['title_rate'],
											'rating'	=> $ins['rate'],
											'langhits'	=> $lang['all_hits'],
											'hits'		=> $item['hits'],
											'comment'	=> $lang['comment_total'],
											'count'		=> $ins['count'],
											'author'	=> $ins['author']
										),
										$ins['thumb']);
			}

			$thumb = $tm->tableprint($ins['content'], $conf['maincol']);

			// Вывод, Популярные
			$ins['popular'] = $tm->parse(array
				(
					'title' => $lang['photos_pop'],
					'thumb' => $thumb
				),
				$ins['section']);
		}
	}

	/**
	 * Случайные
	 */
	if ($conf['random'] == 'yes')
	{
		$inq = $db->query
				(
					"SELECT id, catid, cpu, public, stpublic, title, image_thumb, image_alt, act, rating, totalrating, hits, comments, author
					 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY MD5(RAND()) DESC LIMIT ".$conf['mainlast']
				);

		if ($db->numrows($inq) > 0)
		{
			$posts = TRUE;
			$ins['content'] = array();
			while ($item = $db->fetchrow($inq))
			{
				$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
				$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

				$ins['alt'] = ($item['image_alt']) ? $api->siteuni($item['image_alt']) : '';
				$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

				$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
				$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

				// Рейтинг
				$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
				$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

				// Автор
				$ins['author'] = null;
				if ( ! empty($item['author']) AND $conf['author'] == 'yes')
				{
					$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
					$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
				}

				$ins['content'][] = $tm->parse(array
										(
											'title'		=> $api->siteuni($item['title']),
											'date'		=> $ins['public'],
											'thumb'		=> $item['image_thumb'],
											'alt'		=> $ins['alt'],
											'url'		=> $ins['url'],
											'langrate'	=> $lang['all_rating'],
											'titlerate'	=> $ins['title_rate'],
											'rating'	=> $ins['rate'],
											'langhits'	=> $lang['all_hits'],
											'hits'		=> $item['hits'],
											'comment'	=> $lang['comment_total'],
											'count'		=> $ins['count'],
											'author'	=> $ins['author']
										),
										$ins['thumb']);
			}

			$thumb = $tm->tableprint($ins['content'], $conf['maincol']);

			// Вывод, Случайные
			$ins['random'] = $tm->parse(array
				(
					'title' => $lang['photos_random'],
					'thumb' => $thumb
				),
				$ins['section']);
		}
	}

	/**
	 * Топ лучших
	 */
	if ($conf['toprate'] == 'yes')
	{
		$inq = $db->query
				(
					"SELECT id, catid, cpu, public, stpublic, title, image_thumb, image_alt, act, rating, totalrating, hits, comments, author
					 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY rating DESC LIMIT ".$conf['mainlast']
				);

		if ($db->numrows($inq) > 0)
		{
			$posts = TRUE;
			$ins['content'] = array();
			while ($item = $db->fetchrow($inq))
			{
				$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
				$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

				$ins['alt'] = ($item['image_alt']) ? $api->siteuni($item['image_alt']) : '';
				$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

				$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
				$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

				// Рейтинг
				$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
				$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

				// Автор
				$ins['author'] = null;
				if ( ! empty($item['author']) AND $conf['author'] == 'yes')
				{
					$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
					$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
				}

				$ins['content'][] = $tm->parse(array
										(
											'title'		=> $api->siteuni($item['title']),
											'date'		=> $ins['public'],
											'thumb'		=> $item['image_thumb'],
											'alt'		=> $ins['alt'],
											'url'		=> $ins['url'],
											'langrate'	=> $lang['all_rating'],
											'titlerate'	=> $ins['title_rate'],
											'rating'	=> $ins['rate'],
											'langhits'	=> $lang['all_hits'],
											'hits'		=> $item['hits'],
											'comment'	=> $lang['comment_total'],
											'count'		=> $ins['count'],
											'author'	=> $ins['author']
										),
										$ins['thumb']);
			}

			$thumb = $tm->tableprint($ins['content'], $conf['maincol']);

			// Вывод, Лучшие
			$ins['toprate'] = $tm->parse(array
				(
					'title' => $lang['photos_best'],
					'thumb' => $thumb
				),
				$ins['section']);
		}
	}

		$tm->unmanule['posts'] = ($posts) ? 'no' : 'yes';

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'descript'	=> $ins['map'],
				'category'	=> $ins['category'],
				'new'		=> $ins['new'],
				'popular'	=> $ins['popular'],
				'random'	=> $ins['random'],
				'toprate'	=> $ins['toprate'],
				'noposts'	=> $lang['no_posts'],
				'search'	=> ($posts) ? $tm->search($conf['search'], WORKMOD, 1) : ''
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/index'))
		);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка cat
 * ------------ */
if ($to == 'cat')
{
	$obj = $ins = $menu = $area = array();
	$id = preparse($id, THIS_INT);

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['total'] = $db->numrows($inq, $config['cache']);
	while ($c = $db->fetchrow($inq, $config['cache']))
	{
		$area[$c['parentid']][$c['catid']] = $menu[$c['catid']] = $obj['id'][$c['catid']] = $obj['ccpu'][$c['catcpu']] = $c;
	}

	if ( ! empty($ccpu) AND preparse($ccpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$ccpu = preparse($ccpu, THIS_TRIM, 0, 255);
		$ins['catcpu'] = '&amp;ccpu='.$ccpu;
		$ins['valid'] = (isset($obj['ccpu'][$ccpu]) ? 1 : 0);
		$obj = ($ins['valid'] == 1) ? $obj['ccpu'][$ccpu] : 'empty';
		$v = 0;
	} else {
		$ins['catcpu'] = '';
		$ins['valid'] = (isset($obj['id'][$id]) ? 1 : 0);
		$obj = ($ins['valid'] == 1) ? $obj['id'][$id] : 'empty';
		$v = 1;
	}

	/**
	 * Страница не существует
	 */
	if ($ins['valid'] == 0 OR $obj == 'empty')
	{
		$tm->noexistprint();
	}
	elseif ( ! isset($ccpu) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}

	$in = $api->findsubcat($area, $obj['catid']);
	$whe = (is_array($in) AND sizeof($in) > 0) ? ','.implode(',', $in) : '';
	$total = $db->fetchrow
				(
					$db->query
					(
						"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."
						 WHERE catid IN (".$obj['catid'].$whe.") AND act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
					)
				);

	/**
	 * Ошибка листинга
	 */
	$nums = ceil($total['total'] / $conf['pagcol']);
	if ($p > $nums AND $p != 1)
	{
		$tm->noexistprint();
	}

	/**
	 * Сортировки
	 */
	$ins['order'] = array('asc', 'desc');
	$ins['sort'] = array('public', 'id', 'title', 'hits');
	$order = ($obj['ord'] AND in_array($obj['ord'], $ins['order'])) ? $obj['ord'] : 'asc';
	$sort = ($obj['sort'] AND in_array($obj['sort'], $ins['sort'])) ? $obj['sort'] : 'id';

	/**
	 * Свой TITLE
	 */
	if (isset($obj['catcustom']) AND ! empty($obj['catcustom'])) {
		define('CUSTOM', $api->siteuni($obj['catcustom']));
	} else {
		$global['title'] = $api->siteuni($obj['catname']);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($obj['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($obj['keywords']) : '';
	$global['descript'] = (preparse($obj['descript'], THIS_EMPTY) == 0) ? $api->siteuni($obj['descript']) : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = (defined('CUSTOM')) ? $api->siteuni($obj['catcustom']) : $api->siteuni($obj['catname']);
	if ( ! empty($obj['catdesc'])) {
		$global['og_desc'] = $api->siteuni($obj['catdesc']);
	} elseif ( ! empty($obj['descript'])) {
		$global['og_desc'] = $api->siteuni($obj['descript']);
	}
	$global['og_image'] = ( ! empty($obj['icon'])) ? SITE_URL.'/'.$obj['icon'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$api->catcache = $menu;
	$global['insert']['current'] = $api->siteuni($obj['catname']);
	$global['insert']['breadcrumb'] = $api->sitecat($obj['catid']);

	/**
	 * Ограничение доступа
	 */
	if ($obj['access'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($obj['groups']))
		{
			$group = Json::decode($obj['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Категории
	 */
	$ins['category'] = null;
	if ( ! empty($area))
	{
		$api->subcatcache = $area;
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat'));
		$api->printsitecat($obj['catid']);
		if ( ! empty($api->print))
		{
			$stat = $db->fetchrow
						(
							$db->query
							(
								"SELECT COUNT(id) AS total, SUM(hits) AS hits FROM ".$basepref."_".WORKMOD." WHERE catid IN (".$obj['catid'].$whe.") AND act = 'yes'
								 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
								 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
							)
						);

			$ins['total'] = sizeof($in);
			$catprint = $tm->tableprint($api->print, $conf['catcol']);

			$ins['category'] = $tm->parse(array
				(
					'cd'		=> $lang['cat_desc'],
					'lang_icon'	=> $lang['all_icon'],
					'lang_col'	=> $lang['all_col'],
					'lang_total'=> $lang['all_photo'],
					'lang_cat'	=> $lang['all_cats'],
					'lang_hits'	=> $lang['all_hits'],
					'catprint'	=> $catprint,
					'total'		=> $stat['total'],
					'hits'		=> ( ! empty($stat['hits'])) ? $stat['hits'] : 0,
					'cats'		=> $ins['total'],
					'search'	=> $tm->search($conf['search'], WORKMOD, 1)
				),
				$ins['template']);
		}
	}

	$inq = $db->query
			(
				"SELECT catid, id, public, stpublic, cpu, title, image_thumb, image_alt, hits, totalrating, rating, comments, author
				 FROM ".$basepref."_".WORKMOD."
				 WHERE catid IN (".$obj['catid'].$whe.") AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY ".$sort." ".$order." LIMIT ".$s.", ".$conf['pagcol']
			);

	if ($db->numrows($inq) > 0)
	{
		/**
		 * Листинг страниц, функция
		 */
		$ins['pages'] = null;
		if ($obj['total'] > $conf['pagcol'])
		{
			$ins['pagesview'] = $api->pages
									(
										WORKMOD." WHERE catid IN (".$obj['catid'].$whe.") AND act = 'yes'
										AND (stpublic = 0 OR stpublic < '".NEWTIME."')
										AND (unpublic = 0 OR unpublic > '".NEWTIME."')",
										'id', 'index', WORKMOD.'&amp;to=cat&amp;id='.$obj['catid'].$ins['catcpu'], $conf['pagcol'], $p
									);
			$ins['pages'] = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $ins['pagesview']
									),
									$tm->manuale['pagesout']);
		}

		/**
		 * Переключатели
		 */
		$tm->unmanule['date'] = $conf['date'];
		$tm->unmanule['rating'] = $conf['rating'];
		$tm->unmanule['comment'] = $conf['comact'];
		$tm->unmanule['desc'] = (preparse($menu[$obj['catid']]['catdesc'], THIS_EMPTY) == 0) ? 'yes' : 'no';
		$tm->unmanule['subtitle'] = (preparse($menu[$obj['catid']]['subtitle'], THIS_EMPTY) == 0) ? 'yes' : 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale['author'] = null;

		/**
		 * Шаблон
		 */
		$ins['thumb'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/thumb'));

		$ins['content'] = array();
		while ($item = $db->fetchrow($inq))
		{
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$menu[$item['catid']]['catcpu'] : '';

			$ins['alt'] = ($item['image_alt']) ? $api->siteuni($item['image_alt']) : '';
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Автор
			$ins['author'] = null;
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			$ins['content'][] = $tm->parse(array
										(
											'title'		=> $api->siteuni($item['title']),
											'date'		=> $ins['public'],
											'thumb'		=> $item['image_thumb'],
											'alt'		=> $ins['alt'],
											'url'		=> $ins['url'],
											'langrate'	=> $lang['all_rating'],
											'titlerate'	=> $ins['title_rate'],
											'rating'	=> $ins['rate'],
											'langhits'	=> $lang['all_hits'],
											'hits'		=> $item['hits'],
											'comment'	=> $lang['comment_total'],
											'count'		=> $ins['count'],
											'author'	=> $ins['author']
										),
										$ins['thumb']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		// Описание категории
		$ins['catdesc'] =  (preparse($menu[$obj['catid']]['catdesc'], THIS_EMPTY) == 0) ? $menu[$obj['catid']]['catdesc'] : '';

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'category'	=> $ins['category'],
				'catdesc'	=> $ins['catdesc'],
				'title'		=> $api->siteuni($obj['catname']),
				'subtitle'	=> $api->siteuni($obj['subtitle']),
				'content'	=> $ins['output'],
				'pages'		=> $ins['pages'],
				'search'	=> $tm->search($conf['search'], WORKMOD, 1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/cat.index'))
		);

	}
	else
	{
		// Данные отсутствуют
		$tm->message($lang['data_not'], 0, 1, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка pa
 * -------------- */
if ($to == 'page')
{
	$obj = $ins = $area = $cap = $nav = array();
	$id = preparse($id, THIS_INT);

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);

	/**
	 * Переменные
	 */
	$ins = array(
		'author'	=> '',
		'tags'		=> '',
		'tagword'	=> '',
		'langtags'	=> '',
		'rate'		=> 0,
		'social'	=> '',
		'rating'	=> '',
		'valrate'	=> '',
		'formrate'	=> '',
		'prev'		=> '',
		'view'		=> '',
		'next'		=> '',
		'size'		=> '',
		'video'		=> '',
		'srows'		=> '',
		// comment
		'comment'	=> '',
		'comform'	=> '',
		'ajaxbox'	=> ''
	);

	/**
	 * Запрос, ЧПУ или без
	 */
	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE cpu = '".$db->escape($cpu)."' AND act = 'yes'");
		$v = 0;
	}
	else
	{
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes'");
		$v = 1;
	}

	$item = $db->fetchrow($valid);

	/**
	 * Получение категорий
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchrow($inq,$config['cache']))
	{
		$area[$c['catid']] = $c;
	}
	$ins['catcpu'] = (defined('SEOURL') AND $item['catid'] > 0) ? $area[$item['catid']]['catcpu'] : '';

    /**
     * Ошибка страницы
     */
	if ($db->numrows($valid) == 0)
	{
		$tm->noexistprint();
	}
	elseif ( ! empty($item['cpu']) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}
	elseif ( ! isset($ccpu) AND $ins['catcpu'] OR isset($ccpu) AND $ins['catcpu'] != $ccpu)
	{
		$tm->noexistprint();
	}

	/**
	 * Ошибка листинга комментариев
	 */
	$p = preparse($p, THIS_INT);
	if ($conf['comact'] == 'yes')
	{
		$lp = (isset($p)) ? FALSE : TRUE;
		$p = ($p <= 1) ? 1 : $p;
		$nums = ceil($item['comments'] / $config['compage']);
		if ($p > $nums AND $p != 1) {
			$tm->noexistprint();
		}
	}
	else
	{
		if ($p > 0) {
			$tm->noexistprint();
		} else {
			$p = 1;
		}
	}

    /**
     * Данные категории
     */
	if (isset($area[$item['catid']])) {
		$obj = $area[$item['catid']];
	} else {
		$obj = array
		(
			'catid'		=> '',
			'parentid'	=> '',
			'catcpu'	=> '',
			'catname'	=> '',
			'icon'		=> '',
			'access'	=> '',
			'groups'	=> ''
		);
	}

	/**
	 * Обновляем количество просмотров
	 */
	$db->query("UPDATE ".$basepref."_".WORKMOD." SET hits = hits + 1 WHERE id = '".$item['id']."'");

	/**
	 * Свой TITLE
	 */
	if (isset($item['customs']) AND ! empty($item['customs'])) {
		define('CUSTOM', $api->siteuni($item['customs'].$seopage));
	} else {
		$global['title'] = preparse($item['title'],THIS_TRIM);
		$global['title'].= (empty($obj['catname'])) ? '' : ' - '.$obj['catname'].$seopage;
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (empty($item['keywords'])) ? $api->seokeywords($item['title'].' '.$item['text'], 5, 35) : $item['keywords'];
	$global['descript'] = (empty($item['descript'])) ? '' : $item['descript'].$seopage;

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($item['title'])) ? $api->siteuni($item['title']) : '';
	$global['og_desc'] = ( ! empty($item['text'])) ? $api->siteuni($item['descript']) : $api->siteuni($item['text']);
	$global['og_image'] = ( ! empty($item['image_thumb'])) ? SITE_URL.'/'.$item['image_thumb'] : '';

	/**
	 * Меню, хлебные крошки, с учетом категории
	 */
	if ($item['catid'] > 0) {
		$api->catcache = $area;
		$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
		$global['insert']['breadcrumb'] = $api->sitecat($item['catid']);
	} else {
		$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
		$global['insert']['breadcrumb'] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>';
	}

	/**
	 * Ограничение доступа
	 */
	if($obj['access'] == 'user' OR $item['acc'] == 'user')
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
		if (defined('GROUP_ACT') AND ! empty($obj['groups']))
		{
			$group = Json::decode($obj['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Сортировки
	 */
	$ins['order'] = array('asc', 'desc');
	$ins['sort'] = array('public', 'id', 'title', 'hits');
	$order = (isset($obj['ord']) AND in_array($obj['ord'], $ins['order'])) ? $obj['ord'] : 'asc';
	$sort = (isset($obj['sort']) AND in_array($obj['sort'], $ins['sort'])) ? $obj['sort'] : 'id';

	/**
	 * CPU
	 */
	$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
	$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

	/**
	 * Комментарии
	 */
	if ($conf['comact'] == 'yes')
	{
		$cm = new Comment(WORKMOD);

		// Вывод
		if ($item['comments'] > 0)
		{
			$ins['comment'] = $cm->comment($item['id'], $item['comments'], $ins['cpu'], $ins['catcpu'], $p);
		}

		// Новые посты ajax
		$ins['ajaxbox'] = $tm->parse(array('empty' => 'empty'), $tm->manuale['ajaxbox']);

		// Форма
		$ins['comform'] = $cm->comform($item['id'], $item['title']);
	}

	/**
	 * Управление
	 */
	$tm->unmanule['lightbox'] = 'no';
	$tm->unmanule['rec'] = $conf['rec'];
	$tm->unmanule['text'] = ( ! empty($item['text'])) ? 'yes' : 'no';
	$tm->unmanule['image'] = ( ! empty($item['image'])) ? 'yes' : 'no';
	$tm->unmanule['video'] = ( ! empty($item['video'])) ? 'yes' : 'no';
	$tm->unmanule['comment'] = ($conf['comact'] == 'yes' AND $item['comments'] > 0) ? 'yes' : 'no';
	$tm->unmanule['author'] = ( ! empty($item['author']) AND $conf['author'] == 'yes') ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale['author'] = null;

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'tags' => null,
			'author' => null,
			'social' => null,
			'valrate' => null,
			'formajax' => null,
			'formrate' => null
		);

	/**
	 * Кнопки управления
	 */
	$pag = $db->query
				(
					"SELECT id, cpu, image_thumb, image_alt FROM ".$basepref."_".WORKMOD."
					 WHERE catid = '".$item['catid']."' AND act = 'yes'
					 ORDER BY ".$sort." ".$order." LIMIT ".$conf['lastrec']
				);
	$ins['total'] = $db->numrows($pag);

	$act = $in = 1;
	if ($ins['total'] > 0)
	{
		while ($items = $db->fetchassoc($pag))
		{
			$cap[$in] = $items;
			if ($items['id'] == $item['id']) {
				$act = $in;
			}
			$in ++;
		}

		// View
		foreach ($cap as $k => $v)
		{
			if (is_array($v) AND isset($v['image_thumb']))
			{
				$tm->unmanule['ccurrent'] = ($item['id'] == $v['id']) ? 'no' : 'yes';
				$cpu = (defined('SEOURL') AND $v['cpu']) ? "&amp;cpu=".$v['cpu'] : "";
				$ins['view'].= $tm->parse(array
									(
										'url' => $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$v['id'].$cpu),
										'img' => $v['image_thumb'],
										'alt' => $v['image_alt']
									),
									$tm->parsein($tm->create('mod/'.WORKMOD.'/view')));
			}
		}

		// Prev
		$tm->unmanule['prev'] = (isset($cap[$act - 1])) ? 'yes' : 'no';
		$cpu = (defined('SEOURL') AND isset($cap[$act - 1]['cpu'])) ? '&amp;cpu='.$cap[$act - 1]['cpu'] : '';
		$prev = ( ! empty($cap[$act - 1])) ? $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$cap[$act - 1]['id'].$cpu) : '';
		$ins['prev'] = $tm->parse(array
							(
								'url' => $prev,
								'goback' => $lang['all_prev']
							),
							$tm->parsein($tm->create('mod/'.WORKMOD.'/prev')));

		// Next
		$tm->unmanule['next'] = (isset($cap[$act + 1])) ? 'yes' : 'no';
		$cpu = (defined('SEOURL') AND isset($cap[$act + 1]['cpu'])) ? '&amp;cpu='.$cap[$act + 1]['cpu'] : '';
		$next = ( ! empty($cap[$act + 1])) ? $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$cap[$act + 1]['id'].$cpu) : '';
		$ins['next'] = $tm->parse(array
							(
								'url' => $next,
								'forward' => $lang['all_next']
							),
							$tm->parsein($tm->create('mod/'.WORKMOD.'/next')));
	}

	/**
	 * Видео
	 */
	if ( ! empty($item['video']))
	{
		$ins['video'] = (substr($item['video'], 0, 7) == 'http://') ? $item['video'] : DNROOT.$item['video'];
	}

	/**
	 * Рейтинг
	 */
	if ($conf['rating'] == 'yes')
	{
		// Шаблон
		$ins['temp_rating'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/rating'));

		$ruser = $db->numrows
						(
							$db->query
							(
								"SELECT ratingid FROM ".$basepref."_rating WHERE (
								 file = '".WORKMOD."'
								 AND id = '".$item['id']."'
								 AND ratingip = '".REMOTE_ADDRS."'
								 AND ratingtime >= '".(NEWTIME - $conf['ratetime'])."'
								)"
							)
						);

		$ruser = ($ruser > 0) ? FALSE : TRUE;
		$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
		$ins['wrate'] = intval((100 / 5) * $ins['rate']);

		$ins['valrate'] = $tm->parse(array
								(
									'imgrate'   => $ins['rate'],
									'titlerate' => ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].'']
								),
								$tm->manuale['valrate']);

		if (
			$conf['rateuse'] == 'all' OR
			$conf['rateuse'] == 'user' AND
			defined('USER_LOGGED')
		) {
			if ($config['ajax'] == 'yes')
			{
				if ($ruser)
				{
					$ins['valrate'] = $tm->parse(array
											(
												'mod'    => WORKMOD,
												'rate_1' => $lang['rate_1'],
												'rate_2' => $lang['rate_2'],
												'rate_3' => $lang['rate_3'],
												'rate_4' => $lang['rate_4'],
												'rate_5' => $lang['rate_5'],
												'width'  => $ins['wrate'],
												'id'     => $item['id']
											),
											$tm->manuale['formajax']);
				}
			}
			else
			{
				if ($ruser)
				{
					$ins['formrate'] = $tm->parse(array
											(
												'post_url' => $ro->seo('index.php?dn='.WORKMOD),
												'rate_but' => $lang['rate_button'],
												'choose'   => $lang['choose'],
												'rate_1'   => $lang['rate_1'],
												'rate_2'   => $lang['rate_2'],
												'rate_3'   => $lang['rate_3'],
												'rate_4'   => $lang['rate_4'],
												'rate_5'   => $lang['rate_5'],
												'width'    => $ins['wrate'],
												'id'       => $item['id']
											),
											$tm->manuale['formrate']);
				}
			}
		}

		// Вывод
		$ins['rating'] = $tm->parse(array
							(
								'valrate'		=> $ins['valrate'],
								'formrate'		=> $ins['formrate'],
								'rating'		=> $item['rating'],
								'totalrating'	=> $item['totalrating'],
								'langrate'		=> $lang['all_rating'],
								'waitup'		=> $lang['wait_up'],
								'countrating'	=> $lang['rate_'.$ins['rate']]
							),
							$ins['temp_rating']);
	}

	/**
	 * Размер изображения для Lightbox
	 */
	if (
		! empty($item['image']) AND
		file_exists(DNDIR.$item['image']) AND
		$size = getimagesize(DNDIR.$item['image'])
	) {
		if ($size[0] > $conf['lightbox'])
		{
			$tm->unmanule['lightbox'] = 'yes';

			$height = intval(($conf['lightbox'] / $size[0]) * $size[1]);
			$width  = intval(($height / $size[1]) * $size[0]);
			$ins['size'] = ' height="'.$height.'" width="'.$width.'"';
		}
	}

	// Теги
	if ($conf['tags'] == 'yes')
	{
		$ins['temp_tags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

		$tc = array();
		$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
		while ($t = $db->fetchrow($taginq, $config['cache']))
		{
			$tc[$t['tagid']] = $t;
		}

		$key = explode(',', $item['tags']);
		foreach ($key as $k)
		{
			if (isset($tc[$k]))
			{
				$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
				$tag_url = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
				$ins['tagword'] .= $tm->parse(array(
								'tag_url'  => $tag_url,
								'tag_word' => $tc[$k]['tagword']
							),
							$tm->manuale['tags']);
			}
		}
		if (isset($tc[$k]) AND ! empty($key))
		{
			$ins['tags'] = $tm->parse(array
								(
									'tags' => chop(trim($ins['tagword']), ','),
									'langtags'	=> $lang['all_tags']
								),
								$ins['temp_tags']);
		}
	}

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/open'));

	/**
	 * Социальные закладки
	 */
	if ($config['social_bookmark'] == 'yes')
	{
		$ins['tempsocial']= $tm->parsein($tm->create('mod/'.WORKMOD.'/social'));
		$l = Json::decode($config['social']);
		if (is_array($l))
		{
			foreach ($l as $k => $v)
			{
				$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
				$url = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'], true);
				$url = urlencode(stripslashes($url));
				$title = urlencode(stripslashes($item['title']));
				$link = str_replace(array('{link}', '{title}'), array($url, $title), $v['link']);
				if ($v['act'] == 'yes')
				{
					$ins['srows'] .= $tm->parse(array
											(
												'link' => $link,
												'icon' => $v['icon'],
												'alt'  => $v['alt']
											),
											$tm->manuale['social']);
				}
			}
			$ins['social'] = $tm->parse(array('socialrows' => $ins['srows']), $ins['tempsocial']);
		}
	}

	/**
	 * Автор
	 */
	if ( ! empty($item['author']))
	{
		$ins['author'] = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
		if (isset($config['mod']['user']))
		{
			$udata = $userapi->userdata('uname', $ins['author']);
			if ( ! empty($udata))
			{
				$ins['author'] = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$ins['author'].'</a>';
			}
		}
	}

	/**
	 * Ссылка на страницу
	 */
	$ins['link'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'], 1);

	/**
	 * Категория
	 */
	$ins['cat'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

	/**
	 * Дата
	 */
	$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

	/**
	 * Подзаголовок
	 */
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'title'			=> $api->siteuni($item['title']),
			'subtitle'		=> $ins['subtitle'],
			'text'			=> $api->siteuni($item['text']),
			'date'			=> $ins['public'],
			'prev'			=> $ins['prev'],
			'view'			=> $ins['view'],
			'next'			=> $ins['next'],
			'alt'			=> $api->siteuni($item['image_alt']),
			'social'		=> $ins['social'],
			'langauthor'	=> $lang['author'],
			'author'		=> $ins['author'],
			'rating'		=> $ins['rating'],
			'langhits'		=> $lang['all_hits'],
			'langname'		=> $lang['all_name'],
			'langcat'		=> $lang['all_cat_one'],
			'linkcat'		=> $ins['cat'],
			'catname'		=> $obj['catname'],
			'descript'		=> $lang['descript'],
			'langtime'		=> $lang['all_data'],
			'bookmark'		=> $lang['social_bookmark'],
			'details'		=> $lang['order_detail'],
			'shareit'		=> $lang['to_share'],
			'hits'			=> $item['hits'],
			'link'			=> $ins['link'],
			'thumb'			=> SITE_URL.'/'.$item['image'],
			'image'			=> $item['image'],
			'size'			=> $ins['size'],
			'video'			=> $ins['video'],
			'directlink'	=> $lang['direct_link'],
			'htmlcode'		=> $lang['html_code'],
			'bbcode'		=> $lang['bb_code'],
			'tags'			=> $ins['tags'],
			// comment,
			'comtotal'		=> $lang['comment_total'],
			'count'			=> $item['comments'],
			'comment'		=> $ins['comment'],
			'comform'		=> $ins['comform'],
			'ajaxbox'		=> $ins['ajaxbox'],
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
