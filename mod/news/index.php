<?php
/**
 * File:        /mod/news/index.php
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
global	$db, $basepref, $config, $lang, $usermain, $ro, $tm, $cm, $api, $userapi,
		$global, $to, $p, $id, $ye, $mo, $da, $ccpu, $cpu;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Метки
 */
$legaltodo = array('index', 'cat', 'page', 'dat');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * -------------- */
if ($to == 'index')
{
	$obj = $ins = $tc = array();

	$ins = array
		(
			'last' => null,
			'pages' => null,
			'nocat' => null,
			'category' => null
		);

	$posts = FALSE;

	/**
	 * Публикаций без категории
	 */
	$total = $db->fetchassoc
	(
		$db->query
				(
					"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE catid = 0 AND act = 'yes'
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
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].$seopage);
	} else {
		$global['title'] = $global['modname'].$seopage;
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript'].$seopage) : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['insert']['breadcrumb'] = $global['modname'];

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Листинг, формирование постраничной разбивки
	 */
	if ($total['total'] > $conf['pagcol'])
	{
		$ins['pages'] = $tm->parse(array
							(
								'text' => $lang['all_pages'],
								'pages' => $api->pages('', '', 'index', WORKMOD.'&amp;to=index', $conf['pagcol'], $p, $total['total'])
							),
							$tm->manuale['pagesout']);
	}

	/**
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['cats'] = $db->numrows($inq, $config['cache']);
	while ($c = $db->fetchassoc($inq, $config['cache']))
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
						'lang_total' => $lang['public_count'],
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
	$tm->unmanule['link'] = $tm->unmanule['info'] = 'yes';
	$tm->unmanule['desc'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? 'yes' : 'no';

    /**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat' => null,
			'icon' => null,
			'tags' => null,
			'thumb' => null,
			'author' => null
		);

	/**
	 * Описание раздела
	 */
	$ins['map'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? $config['mod'][WORKMOD]['map'] : '';

	/**
	 * Шаблоны
	 */
	$ins['standart'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));
	$ins['section'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/index.section'));

	$inq = $db->query
			(
				"SELECT id, catid, public, stpublic, unpublic, cpu, title, textshort, author,
				 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
				 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND catid <> '0'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY public DESC LIMIT ".$conf['pagmain']
			);

	/**
	 * Все теги в массив
	 */
	if ($db->numrows($inq) > 0 OR $total['total'] > 0)
	{
		// Все теги
		$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
		while ($t = $db->fetchassoc($taginq, $config['cache']))
		{
			$tc[$t['tagid']] = $t;
		}
	}

	/**
	 * Последние публикации
	 */
	if ($db->numrows($inq) > 0)
	{
		$posts = TRUE;

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			$ins['cat'] = $ins['icon'] = $ins['image'] = $ins['tags'] = $tagword = $ins['author'] = '';

			// Теги
			if ($conf['tags'] == 'yes')
			{
				$ins['temptags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
						$tagword .= $tm->parse(array(
													'tag_url'  => $tag_url,
													'tag_word' => $tc[$k]['tagword'],
													'tag_desc' => $tc[$k]['descript']
												),
												$tm->manuale['tags']);
					}
				}

				if (isset($tc[$k]) AND ! empty($key))
				{
					$ins['tags'] = $tm->parse(array
										(
											'tags'		=> $tagword,
											'langtags'	=> $lang['all_tags']
										),
										$ins['temptags']);
				}
			}

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $item['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Категория
			if ($conf['linkcat'] == 'yes' AND isset($obj[$item['catid']]['catname']{0}))
			{

				$ins['cat'] = $tm->parse(array(
										'caturl'  => $ins['caturl'],
										'catname' => $api->siteuni($obj[$item['catid']]['catname'])
									),
									$tm->manuale['cat']);
			}

			// Иконка категории
			if ($conf['iconcat'] == 'yes' AND ! empty($obj[$item['catid']]['icon']))
			{
				$ins['icon'] = $tm->parse(array(
										'icon'  => $obj[$item['catid']]['icon'],
										'alt'   => $api->siteuni($obj[$item['catid']]['catname'])
									),
									$tm->manuale['icon']);
			}

			// Автор
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				if (isset($config['mod']['user']))
				{
					$udata = $userapi->userdata('uname', $author);
					if ( ! empty($udata))
					{
						$author = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$udata['uname'].'</a>';
					}
				}
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Кол. комментариев
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Содержимое
			$ins['content'][] = $tm->parse(array
									(
										'icon'		=> $ins['icon'],
										'cat'		=> $ins['cat'],
										'date'		=> $ins['public'],
										'title'		=> $api->siteuni($item['title']),
										'text'		=> $api->siteuni($item['textshort']),
										'image'		=> $ins['image'],
										'author'	=> $ins['author'],
										'comment'	=> $lang['comment_total'],
										'count'		=> $ins['count'],
										'langhits'	=> $lang['all_hits'],
										'hits'		=> $item['hits'],
										'langrate'	=> $lang['all_rating'],
										'rating'	=> $ins['rate'],
										'titlerate'	=> $ins['title_rate'],
										'url'		=> $ins['url'],
										'tags'		=> $ins['tags'],
										'read'		=> $lang['in_detail']
									),
									$ins['standart']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		// Вывод, последние публикации
		$ins['last'] = $tm->parse(array
			(
				'title' => $lang['public_last'],
				'content' => $ins['output']
			),
			$ins['section']);
	}

	/**
	 * Публикации без категории
	 */
	if ($total['total'] > 0)
	{
		$posts = TRUE;
		$inqs = $db->query
				(
					"SELECT id, catid, public, stpublic, unpublic, cpu, title, textshort, author,
					 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
					 FROM ".$basepref."_".WORKMOD." WHERE catid = '0' AND act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY public DESC LIMIT ".$s.", ".$conf['pagcol']
				);

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inqs))
		{
			$ins['cat'] = $ins['icon'] = $ins['image'] = $ins['tags'] = $tagword = $ins['author'] = '';

			// Теги
			if ($conf['tags'] == 'yes')
			{
				$ins['temptags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
						$tagword .= $tm->parse(array(
													'tag_url'  => $tag_url,
													'tag_word' => $tc[$k]['tagword'],
													'tag_desc' => $tc[$k]['descript']
												),
												$tm->manuale['tags']);
					}
				}

				if (isset($tc[$k]) AND ! empty($key))
				{
					$ins['tags'] = $tm->parse(array
										(
											'tags'		=> $tagword,
											'langtags'	=> $lang['all_tags']
										),
										$ins['temptags']);
				}
			}

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $item['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Автор
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				if (isset($config['mod']['user']))
				{
					$udata = $userapi->userdata('uname', $author);
					if ( ! empty($udata))
					{
						$author = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$udata['uname'].'</a>';
					}
				}
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Кол. комментариев
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Содержимое
			$ins['content'][] = $tm->parse(array
									(
										'icon'		=> '', // not
										'cat'		=> '', // not
										'title'		=> $api->siteuni($item['title']),
										'date'		=> $ins['public'],
										'text'		=> str_word($api->siteuni($item['textshort']), 300),
										'image'		=> $ins['image'],
										'author'	=> $ins['author'],
										'comment'	=> $lang['comment_total'],
										'count'		=> $ins['count'],
										'langhits'	=> $lang['all_hits'],
										'hits'		=> $item['hits'],
										'langrate'	=> $lang['all_rating'],
										'titlerate'	=> $ins['title_rate'],
										'rating'	=> $ins['rate'],
										'url'		=> $ins['url'],
										'tags'		=> $ins['tags'],
										'read'		=> $lang['in_detail']
									),
									$ins['standart']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		// Вывод, публикации без категории
		$ins['nocat'] = $tm->parse(array
			(
				'title' => $lang['public_nocat'],
				'content' => $ins['output']
			),
			$ins['section']);
	}

	$tm->unmanule['posts'] = ($posts) ? 'no' : 'yes';

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'category'	=> $ins['category'],
			'descript'	=> $ins['map'],
			'last'		=> $ins['last'],
			'nocat'		=> $ins['nocat'],
			'pages'		=> $ins['pages'],
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
	$id = preparse($id, THIS_INT);
	$obj = $ins = $menu = $area = $tc = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Категории
	 */
	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD."_cat
				 ORDER BY posit ASC", $config['cachetime'], WORKMOD
			);

	$ins['total'] = $db->numrows($inq, $config['cache']);

	while ($c = $db->fetchassoc($inq, $config['cache']))
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
	}
	else
	{
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

	$total = $db->fetchassoc
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
	 * Свой TITLE
	 */
	if (isset($obj['catcustom']) AND ! empty($obj['catcustom'])) {
		define('CUSTOM', $api->siteuni($obj['catcustom'].$seopage));
	} else {
		$global['title'] = $api->siteuni($obj['catname'].$seopage);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($obj['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($obj['keywords']) : '';
	$global['descript'] = (preparse($obj['descript'], THIS_EMPTY) == 0) ? $api->siteuni($obj['descript'].$seopage) : '';

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
	 * Сортировки
	 */
	$ins['order'] = array('asc', 'desc');
	$ins['sort'] = array('public', 'id', 'title', 'hits');
	$order = ($obj['ord'] AND in_array($obj['ord'], $ins['order'])) ? $obj['ord'] : 'asc';
	$sort = ($obj['sort'] AND in_array($obj['sort'], $ins['sort'])) ? $obj['sort'] : 'id';

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
		$ins['tempcat'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat'));
		$api->printsitecat($obj['catid']);

		if ( ! empty($api->print))
		{
			$stat = $db->fetchassoc
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
					'cd'         => $lang['cat_desc'],
					'lang_icon'	 => $lang['all_icon'],
					'lang_col'   => $lang['all_col'],
					'lang_total' => $lang['public_count'],
					'lang_cat'   => $lang['all_cats'],
					'lang_hits'	 => $lang['all_hits'],
					'catprint'   => $catprint,
					'total'      => $stat['total'],
					'hits'       => ( ! empty($stat['hits'])) ? $stat['hits'] : 0,
					'cats'       => $ins['total']
				),
				$ins['tempcat']);
		}
	}

	/**
	 * Новости
	 */
	$inq = $db->query
			(
				"SELECT id, catid, public, stpublic, unpublic, cpu, title, subtitle, textshort, author,
				 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
				 FROM ".$basepref."_".WORKMOD."
				 WHERE catid IN (".$obj['catid'].$whe.") AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY catid, ".$sort." ".$order." LIMIT ".$s.", ".$conf['pagcol']
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
		$tm->unmanule['comment'] = $conf['comact'];
		$tm->unmanule['rating'] = $conf['rating'];
		$tm->unmanule['link'] = $tm->unmanule['info'] = 'yes';
		$tm->unmanule['desc'] = (preparse($menu[$obj['catid']]['catdesc'], THIS_EMPTY) == 0) ? 'yes' : 'no';
		$tm->unmanule['subtitle'] = (preparse($menu[$obj['catid']]['subtitle'], THIS_EMPTY) == 0) ? 'yes' : 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'cat' => null,
				'icon' => null,
				'tags' => null,
				'thumb' => null,
				'author' => null
			);

		/**
		 * Все теги
		 */
		if ($conf['tags'] == 'yes')
		{
			$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
			while ($t = $db->fetchassoc($taginq, $config['cache']))
			{
				$tc[$t['tagid']] = $t;
			}
		}

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			$ins['cat'] = $ins['icon'] = $ins['image'] = $ins['tags'] = $tagword = $ins['author'] = '';

			// Теги
			if ($conf['tags'] == 'yes')
			{
				// Шаблон
				$ins['temp_tags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
						$tagword .= $tm->parse(array(
													'tag_url'  => $tag_url,
													'tag_word' => $tc[$k]['tagword'],
													'tag_desc' => $tc[$k]['descript']
												),
												$tm->manuale['tags']);
					}
				}

				if (isset($tc[$k]) AND ! empty($key))
				{
					$ins['tags'] = $tm->parse(array
										(
											'tags'		=> $tagword,
											'langtags'	=> $lang['all_tags']
										),
										$ins['temp_tags']);
				}
			}

			// CPU
			$ins['cpu']    = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($menu[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$menu[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $item['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Категория
			if (isset($menu[$item['catid']]['catname']) AND $item['catid'] != $obj['catid'])
			{
				if ( ! empty($menu[$item['catid']]['icon']) AND $conf['iconcat'] == 'yes')
				{
					$ins['icon'] = $tm->parse(array(
											'icon'  => $menu[$item['catid']]['icon'],
											'alt'   => $api->siteuni($menu[$item['catid']]['catname'])
										),
										$tm->manuale['icon']);
				}

				$ins['cat'] = $tm->parse(array(
										'caturl'  => $ins['caturl'],
										'catname' => $api->siteuni($menu[$item['catid']]['catname'])
									),
									$tm->manuale['cat']);
			}

			// Автор
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				if (isset($config['mod']['user']))
				{
					$udata = $userapi->userdata('uname', $author);
					if ( ! empty($udata))
					{
						$author = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$udata['uname'].'</a>';
					}
				}
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Кол. комментариев
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Вывод
			$ins['content'][] = $tm->parse(array
									(
										'icon'		=> $ins['icon'],
										'cat'		=> $ins['cat'],
										'date'		=> $ins['public'],
										'title'		=> $api->siteuni($item['title']),
										'text'		=> $api->siteuni($item['textshort']),
										'image'		=> $ins['image'],
										'author'	=> $ins['author'],
										'comment'	=> $lang['comment_total'],
										'count'		=> $ins['count'],
										'langhits'	=> $lang['all_hits'],
										'hits'		=> $item['hits'],
										'langrate'	=> $lang['all_rating'],
										'rating'	=> $ins['rate'],
										'titlerate'	=> $ins['title_rate'],
										'url'		=> $ins['url'],
										'tags'		=> $ins['tags'],
										'read'		=> $lang['in_detail']
									),
									$ins['template']);
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
 * Метка page
 * ----------- */
if ($to == 'page')
{
	$id = preparse($id, THIS_INT);
	$obj = $ins = $area = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);

	/**
	 * Запрос с учетом ЧПУ
	 */
	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$v = 0;
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query
					(
						"SELECT * FROM ".$basepref."_".WORKMOD." WHERE cpu = '".$db->escape($cpu)."' AND act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
					);
	}
	else
	{
		$v = 1;
		$valid = $db->query
					(
						"SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
					);
	}
	$item = $db->fetchassoc($valid);

	/**
	 * Все категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchassoc($inq, $config['cache']))
	{
		$area[$c['catid']] = $c;
	}
	$ins['catcpu'] = (defined('SEOURL') AND $item['catid'] > 0) ? $area[$item['catid']]['catcpu'] : '';

	/**
	 * Страницы не существует
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
	 * Данные категории, или пустой массив
	 */
	if (isset($area[$item['catid']]))
	{
		$obj = $area[$item['catid']];
	}
	else
	{
		$obj = array
			(
				'catid'		=> '',
				'parentid'	=> '',
				'catcpu'	=> '',
				'ctname'	=> '',
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
		$global['title'] = preparse($item['title'], THIS_TRIM);
		$global['title'].= ( ! empty($obj['catname'])) ? ' - '.$obj['catname'].$seopage : '';
	}

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($item['title'])) ? $api->siteuni($item['title']) : '';
	$global['og_desc'] = ( ! empty($item['textshort'])) ? $api->siteuni($item['descript']) : $api->siteuni($item['textshort']);
	$global['og_image'] = ( ! empty($item['image_thumb'])) ? SITE_URL.'/'.$item['image_thumb'] : '';

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($item['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($item['keywords']) : '';
	$global['descript'] = (preparse($item['descript'], THIS_EMPTY) == 0) ? $api->siteuni($item['descript'].$seopage) : '';

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
	 * Переменные
	 */
	$ins = array(
		'author'	=> '',
		'image'		=> '',
		'tags'		=> '',
		'langtags'	=> '',
		'notice'	=> '',
		'rec'		=> '',
		'search'	=> '',
		'icon'		=> '',
		'cat'		=> '',
		'media'		=> '',
		'listname'	=> '',
		'listdesc'	=> '',
		'listtext'	=> '',
		'social'	=> '',
		'tagword'	=> '',
		'srows'		=> '',
		'formrate'	=> '',
		'valrate'	=> '',
		'rating'	=> '',
		'rate'		=> 0,
		// comment
		'comment'	=> '',
		'comform'	=> '',
		'ajaxbox'	=> ''
	);

	// CPU
	$ins['cpu']    = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
	$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

	// URL
	$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
	$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

	// Печать
	$ins['print_url']  = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=print&amp;id='.$item['id']);

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
	 * Переключатели
	 */
	$tm->unmanule['date'] = $conf['date'];
	$tm->unmanule['print'] = $conf['print'];
	$tm->unmanule['rating'] = $conf['rating'];
	$tm->unmanule['social'] = $config['social_bookmark'];
	$tm->unmanule['author'] = ( ! empty($item['author']) AND $conf['author'] == 'yes') ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat' => null,
			'icon' => null,
			'tags' => null,
			'rows' => null,
			'media' => null,
			'social' => null,
			'author' => null,
			'ajaxbox' => null,
			'valrate' => null,
			'formajax' => null,
			'formrate' => null
		);

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
	 * Медиа-презентация
	 */
	if (isset($config['mod']['media']))
	{
		if ($item['listid'] > 0 AND $p == 1 OR $item['listid'] > 0 AND $p == 0)
		{
			$list = $db->fetchassoc
					(
						$db->query
						(
							"SELECT catid, listname, listdesc, listtext, listcol, access, groups
							 FROM ".$basepref."_media_cat
							 WHERE catid = '".$item['listid']."' AND act = 'yes'", $config['cachetime'], "media"
						),
						$config['cache']
					);

			// Ограничение доступа к презентации
			$laccess = TRUE;
			if ($list['access'] == 'user')
			{
				if ( ! defined('USER_LOGGED'))
				{
					$laccess = FALSE;
				}
				if (defined('GROUP_ACT') AND ! empty($list['groups']))
				{
					$group = Json::decode($list['groups']);
					if ( ! isset($group[$usermain['gid']]))
					{
						$laccess = FALSE;
					}
				}
			}

			if (is_array($list) AND $laccess)
			{
				// Шаблон
				$ins['tempmedia'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/media'));

				$mediaing = $db->query
							(
								"SELECT * FROM ".$basepref."_media
								 WHERE catid = '".$list['catid']."' AND act = 'yes'
								 ORDER BY posit ASC", $config['cachetime'], "media"
							);

				if ($db->numrows($mediaing, $config['cache']) > 0)
				{
					while ($media = $db->fetchassoc($mediaing, $config['cache']))
					{
						$media_alt = ( ! empty($media['image_alt'])) ? $api->siteuni($media['image_alt']) : '';
						$media_url = ( ! empty($media['image'])) ? SITE_URL.'/'.$media['image'] :  $ro->seo('index.php?dn=media&amp;to=video&amp;id='.$media['id']);
						$ins['mediain'][]= $tm->parse(array
											(
												'alt'   => $media_alt,
												'url'   => $media_url,
												'thumb' => $media['image_thumb'],
												'title' => $api->siteuni($media['title']),
												'text'  => $api->siteuni($media['text'])
											),
											$tm->manuale['media']);
					}

					// media
					$mediain = $tm->tableprint($ins['mediain'], $list['listcol']);
				}

				$ins['listdesc'] = ( ! empty($list['listdesc'])) ? $api->siteuni($list['listdesc']) : '';
				$ins['listtext'] = ( ! empty($list['listtext'])) ? $api->siteuni($list['listtext']) : '';

				// Вывод
				$ins['media'] = $tm->parse(array
									(
										'listname' => $api->siteuni($list['listname']),
										'listdesc' => $ins['listdesc'],
										'listtext' => $ins['listtext'],
										'mediain'  => $mediain
									),
									$ins['tempmedia']);
			}
		}
	}

	/**
	 * Теги
	 */
	if ($conf['tags'] == 'yes')
	{
		$ins['temp_tags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

		$tc = array();
		$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
		while ($t = $db->fetchassoc($taginq, $config['cache']))
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
								'tag_word' => $tc[$k]['tagword'],
								'tag_desc' => $tc[$k]['descript']
							),
							$tm->manuale['tags']);
			}
		}
		if (isset($tc[$k]) AND ! empty($key))
		{
			$ins['tags'] = $tm->parse(array
								(
									'tags' => $ins['tagword'],
									'langtags'	=> $lang['all_tags']
								),
								$ins['temp_tags']);
		}
	}

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/read'));

	/**
	 * Содержимое
	 */
	$ins['textshort'] = $api->siteuni($item['textshort']);
	$ins['textmore']  = $api->siteuni($item['textmore']);

	// Категория
	if (isset($obj['catname']{0}) AND $conf['linkcat'] == 'yes')
	{
		if ( ! empty($obj['icon']) AND $conf['iconcat'] == 'yes')
		{
			$ins['icon'] = $tm->parse(array(
									'icon'  => $obj['icon'],
									'alt'   => $api->siteuni($obj['catname'])
								),
								$tm->manuale['icon']);
		}
		$ins['cat'] = $tm->parse(array(
								'caturl'  => $ins['caturl'],
								'catname' => $api->siteuni($obj['catname'])
							),
							$tm->manuale['cat']);
	}

	/**
	 * Вводное изображение
	 */
	$tm->unmanule['image'] = ( ! empty($item['image'])) ? 'yes' : 'no';
	$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
	$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

	$ins['temp_thumb'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/thumb'));

	if ( ! empty($item['image_thumb']))
	{
		$ins['image'] = $tm->parse(array
							(
								'float' => $ins['float'],
								'thumb' => $item['image_thumb'],
								'image' => $item['image'],
								'alt'   => $ins['alt']
							),
							$ins['temp_thumb']);
	}

	/**
	 * Сообщения для пользователей
	 */
	if ( ! empty($item['textnotice']))
	{
		$tm->unmanule['notice'] = defined('USER_LOGGED') ? 'yes' : 'no';
		$ins['notice'] = $tm->parse(array
							(
								'guest' => $lang['block_user_view'],
								'user' => $api->siteuni($item['textnotice'])
							),
							$tm->parsein($tm->create('mod/'.WORKMOD.'/notice')));
	}

	/**
	 * Рекомендуемые
	 */
	if ($conf['rec'] == 'yes')
	{
		$inq = $db->query
				(
					"SELECT catid, id, cpu, public, stpublic, unpublic, title, rating FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'
					 AND catid = '".$item['catid']."'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 AND id <> '".$item['id']."'
					 ORDER BY rating DESC, public DESC LIMIT ".$conf['lastrec']
				);

		if ($db->numrows($inq) > 0)
		{
			// Шаблон
			$ins['temprec']= $tm->parsein($tm->create('mod/'.WORKMOD.'/rec'));

			while ($anyitem = $db->fetchassoc($inq))
			{
				$anycpu = (defined('SEOURL') AND $anyitem['cpu']) ? '&amp;cpu='.$anyitem['cpu'] : '';
				$anypublic = ($anyitem['stpublic'] > 0) ? $anyitem['stpublic'] : $anyitem['public'];
				$ins['rec'].= $tm->parse(array
								(
									'title' => $api->siteuni($anyitem['title']),
									'link'  => $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$anyitem['id'].$anycpu),
									'date'  => $anypublic
								),
								$tm->manuale['rows']);
			}

			// Вывод
			$ins['rec'] = $tm->parse(array
							(
								'rectitle' => $lang['all_recommend'],
								'recprint' => $ins['rec']
							),
							$ins['temprec']);
		}
	}

	/**
	 * Перелинковка
	 */
 	if ($config['anchor'] == 'yes' AND $config['mod'][WORKMOD]['seo'] == 'yes')
	{
		$array_links = DNDIR.'cache/cache.seo.php';
		if (file_exists($array_links))
		{
			include($array_links);
			if (! empty($seo) AND isset($seo[WORKMOD]))
			{
				foreach ($seo[WORKMOD] as $val)
				{
					$seolink = seo_link($val['link']);
					if (isset($seolink))
					{
						$ins['textshort'] = preg_replace
												(
													'/([^\<\>])'.$val['word'].'(?![^<]*>)(?=\W|$)/um',
													' <a href="'.$seolink.'" title="'.$val['title'].'">'.$val['word'].'</a>',
													$ins['textshort'],
													$val['count'],
													$done
												);
						$ins['textmore'] = preg_replace
												(
													'/([^\<\>])'.$val['word'].'(?![^<]*>)(?=\W|$)/um',
													' <a href="'.$seolink.'" title="'.$val['title'].'">'.$val['word'].'</a>',
													$ins['textmore'],
													$val['count'] - $done
												);
					}
				}
			}
		}
	}

	/**
	 * Изображения по тексту
	 */
	if ( ! empty($item['images']))
	{
		$im = Json::decode($item['images']);
		if (is_array($im))
		{
			foreach ($im as $k => $v)
			{
				$ins['float'] = 'imgtext-'.$v['align'];
				$ins['alt']   = ( ! empty($v['alt'])) ? $api->siteuni($v['alt']) : '';

				$tm->unmanule['image'] = ( ! empty($v['image'])) ? 'yes' : 'no';
				$ins['temp_thumb'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/thumb'));

				if ( ! empty($v['thumb']))
				{
					$ins['img'] = $tm->parse(array
						(
							'float' => $ins['float'],
							'thumb' => $v['thumb'],
							'image' => $v['image'],
							'alt'   => $ins['alt']
						),
						$ins['temp_thumb']);
				}

				// Содержимое
				$ins['textmore'] = $tm->parse(array('img'.$k => $ins['img']), $ins['textmore']);
			}
		}
	}

	/**
	 * Социальные закладки
	 */
	if ($config['social_bookmark'] == 'yes')
	{
		// Шаблон
		$ins['tempsocial']= $tm->parsein($tm->create('mod/'.WORKMOD.'/social'));

		$l = Json::decode($config['social']);
		if (is_array($l))
		{
			foreach ($l as $k => $v)
			{
				$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
				$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

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
			// Вывод
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
	 * Поиск
	 */
	$ins['search'] = $tm->search($conf['search'], WORKMOD, 1);

	/**
	 * Дата
	 */
	$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

	/**
	 * Рейтинг
	 */
	$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
	$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

	/**
	 * Подзаголовок
	 */
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'icon'			=> $ins['icon'],
			'cat'			=> $ins['cat'],
			'link'			=> $ins['url'],
			'title'			=> $api->siteuni($item['title']),
			'subtitle'		=> $ins['subtitle'],
			'date'			=> $ins['public'],
			'public'		=> $lang['all_data'],
			'langauthor'	=> $lang['author'],
			'author'		=> $ins['author'],
			'hits'			=> $lang['all_hits'],
			'counts'		=> $item['hits'],
			'image'			=> $ins['image'],
			'textshort'		=> $ins['textshort'],
			'textmore'		=> $ins['textmore'],
			'textnotice'	=> $ins['notice'],
			'print_url'		=> $ins['print_url'],
			'print'			=> $lang['print_link'],
			'social'		=> $ins['social'],
			'tags'			=> $ins['tags'],
			'recommend'		=> $ins['rec'],
			'search'		=> $ins['search'],
			'media'			=> $ins['media'],
			// comment
			'comment'		=> $ins['comment'],
			'comform'		=> $ins['comform'],
			'ajaxbox'		=> $ins['ajaxbox'],
			// rating
			'ratings'		=> $ins['rate'],
			'rating'		=> $ins['rating'],
			'titlerate'		=> $ins['title_rate'],
			'langrate'		=> $lang['all_rating']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка dat
 --------------*/
if ($to == 'dat')
{
	$obj = $ins = array();

	$ye = substr(preparse($ye, THIS_INT), 0, 4);
	$mo = substr(preparse($mo, THIS_INT), 0, 2);
	$da = substr(preparse($da, THIS_INT), 0, 2);
	$ye = ($ye < 2000 OR $ye > NEWYEAR) ? NEWYEAR : $ye;
	$mo = ($mo < 1 OR $mo > 12) ? preparse(NEWMONT, THIS_INT) : $mo;
	$da = ($da < 0 OR $da > 31) ? NEWDAY : $da;
	$dim = cal_days_in_month(CAL_GREGORIAN, $mo, $ye);

	if ($da > $dim)
	{
		$da = $dim;
	}

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	// Начальное время
	$start = (mktime(0, 0, 0, $mo, $da, $ye)) ? mktime(0, 0, 0, $mo, $da, $ye) : TODAY;
	if ($da == 0)
	{
		$start = (mktime(0, 0, 0, $mo, 1, $ye));
	}

	// Конечное время
	$end = ($da == 0) ? ($start + date("t", mktime(0, 0, 0, $mo, 12, $ye))*86400) : ($start + 86399);

	// Текущий месяц
	$month = ($da == 0) ? $langdate[strtolower(date('F', mktime(0, 0, 0, $mo, 1, 1)))] : $langdate[$api->month(date('F', mktime(0, 0, 0, $mo, 1, 1)), 1)];

	/**
	 * Всего за период
	 */
	$total = $db->fetchassoc
				(
					$db->query
					(
						"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
						 AND public >= '".$start."' AND public <= '".$end."'"
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
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].', '.(($da == 0) ? '' : $da).' '.$month.' '.$ye.$seopage);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript'].', '.(($da == 0) ? '' : $da).' '.$month.' '.$ye.$seopage) : '';

	/**
	 * Заголовок, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', (($da == 0) ? '' : $da).' '.$month.' '.$ye);

	if ($total['total'] > 0)
	{
		/**
		 * Вывод на страницу, шапка
		 */
		$tm->header();

		/**
		 * Новости за период
		 */
		$inq = $db->query
				(
					"SELECT id, catid, public, stpublic, unpublic, cpu, title, textshort, author,
					 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
					 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 AND public >= '".$start."' AND public <= '".$end."'
					 ORDER BY public DESC LIMIT ".$s.", ".$conf['pagcol']
				);

		/**
		 * Категории
		 */
		$cinq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($c = $db->fetchassoc($cinq, $config['cache']))
		{
			$obj[$c['catid']] = $c;
		}

		/**
		 * Листинг страниц, функция
		 */
		$ins['pages'] = null;
		if ($total['total'] > $conf['pagcol'])
		{
			$ins['pagesview'] = $api->pages
									(
										WORKMOD."	WHERE act = 'yes'
										AND (stpublic = 0 OR stpublic < '".NEWTIME."')
										AND (unpublic = 0 OR unpublic > '".NEWTIME."')
										AND public >= '".$start."' AND public <= '".$end."'",
										'id', 'index', WORKMOD.'&amp;to=dat&amp;ye='.$ye.'&amp;mo='.$mo.'&amp;da='.$da, $conf['pagcol'], $p, $total['total']
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
		$tm->unmanule['link'] = $tm->unmanule['info'] = 'yes';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'cat' => null,
				'icon' => null,
				'tags' => null,
				'thumb' => null,
				'author' => null
			);

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		/**
		 * Теги, массив
		 */
		if ($conf['tags'] == 'yes')
		{
			$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
			while ($t = $db->fetchassoc($taginq, $config['cache']))
			{
				$tc[$t['tagid']] = $t;
			}
		}

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			// Переменные
			$ins['cat'] = $ins['icon'] = $ins['image'] = $ins['tags'] = $tagword = $ins['author'] = '';

			// Теги
			if ($conf['tags'] == 'yes')
			{
				// Шаблон
				$ins['temp_tags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));

				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
						$tagword .= $tm->parse(array(
													'tag_url'  => $tag_url,
													'tag_word' => $tc[$k]['tagword'],
													'tag_desc' => $tc[$k]['descript']
												),
												$tm->manuale['tags']);
					}
				}

				if (isset($tc[$k]) AND ! empty($key))
				{
					$ins['tags'] = $tm->parse(array
										(
											'tags'		=> $tagword,
											'langtags'	=> $lang['all_tags']
										),
										$ins['temp_tags']);
				}
			}

			// CPU
			$ins['cpu']   = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu']= (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu']))  ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $item['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Категория
			if (isset($obj[$item['catid']]['catname']))
			{
				if ( ! empty($obj[$item['catid']]['icon']) AND $conf['iconcat'] == 'yes')
				{
					$ins['icon'] = $tm->parse(array(
											'icon'  => $obj[$item['catid']]['icon'],
											'alt'   => $api->siteuni($obj[$item['catid']]['catname'])
										),
										$tm->manuale['icon']);
				}

				$ins['cat'] = $tm->parse(array(
										'caturl'  => $ins['caturl'],
										'catname' => $api->siteuni($obj[$item['catid']]['catname'])
									),
									$tm->manuale['cat']);
			}

			// Автор
			if ( ! empty($item['author']) AND $conf['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				if (isset($config['mod']['user']))
				{
					$udata = $userapi->userdata('uname', $author);
					if ( ! empty($udata))
					{
						$author = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$udata['uname'].'</a>';
					}
				}
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Кол. комментариев
			$ins['count'] = ($conf['comact'] == 'yes') ? $item['comments'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Содержимое
			$ins['content'][]= $tm->parse(array
								(
									'icon'		=> $ins['icon'],
									'cat'		=> $ins['cat'],
									'date'		=> $ins['public'],
									'title'		=> $api->siteuni($item['title']),
									'text'		=> $api->siteuni($item['textshort']),
									'image'		=> $ins['image'],
									'author'	=> $ins['author'],
									'comment'	=> $lang['comment_total'],
									'count'		=> $ins['count'],
									'langhits'	=> $lang['all_hits'],
									'hits'		=> $item['hits'],
									'langrate'	=> $lang['all_rating'],
									'titlerate'	=> $ins['title_rate'],
									'rating'	=> $ins['rate'],
									'url'		=> $ins['url'],
									'tags'		=> $ins['tags'],
									'read'		=> $lang['in_detail']
								),
								$ins['template']);
		}

		/**
		 * Разбивка
		 */
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'content' => $ins['output'],
				'pages'	  => $ins['pages'],
				'search'  => $tm->search($conf['search'], WORKMOD, 1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/date'))
		);

		/**
		 * Вывод на страницу, подвал
		 */
		$tm->footer();
	}
	else
	{
		/**
		 * Публикаций не найдено
		 */
		$tm->error($lang['empty_data'], 0, 0);
	}
}
