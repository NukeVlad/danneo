<?php
/**
 * File:        /mod/article/tags.php
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
global $to, $db, $basepref, $config, $lang, $usermain, $tm, $global, $ccpu, $cpu, $id, $p;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Теги запрещены, редирект
 */
if ($conf['tags'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метки
 */
$legaltodo = array('index', 'tag');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * index
 * --------- */
if ($to == 'index')
{
	$ins = array();
	$tags = null;

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

	// Keywords
	$inq_key = $db->query("SELECT tagword FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
	while ($key = $db->fetchassoc($inq_key, $config['cache'])) {
		$tags.= $key['tagword'].' ';
	}
	if ( ! empty($tags)) {
		$global['keywords'] = $api->seokeywords(mb_strtolower($tags), 5, 35);
	} else {
		$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';
	}

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['public_tags'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['all_tags']);

	/**
	 * Вывод на страницу
	 */
	$tm->header();

	/**
	 * Все теги
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag ORDER BY tagrating DESC", $config['cachetime'], WORKMOD);

	if ($db->numrows($inq, $config['cache']) > 0)
	{
		$tm->manuale['rows'] = null;

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tag.index'));

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq, $config['cache']))
		{
			$cpu = (defined('SEOURL') AND $item['tagcpu']) ? '&amp;cpu='.$item['tagcpu'] : '';
			$tagurl = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$item['tagid'].$cpu);
			$desc = ( ! empty($item['descript'])) ? $item['descript'] : '';
			$icon = ( ! empty($item['icon'])) ? '<a href="'.$tagurl.'"><img src="'.SITE_URL.'/'.$item['icon'].'" alt="'.$item['tagword'].'" /></a>' : '';

			// Содержимое
			$ins['content'][] = $tm->parse(array
									(
										'icon' => $icon,
										'tagurl' => $tagurl,
										'tagname' => $item['tagword'],
										'desc' => $desc
									),
									$tm->manuale['rows']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['catcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array(
				'tagprint' => $ins['output']
			),
			$ins['template']);
	}
	else
	{
		$tm->message($lang['data_not'], 0, 0, 1);
	}

	/**
	 * Форма поиска
	 */
	$tm->search($conf['search'], WORKMOD);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка tag
 * ------------ */
if ($to == 'tag')
{
	$id = preparse($id, THIS_INT);

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.$lang['page_one'].'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Все теги
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);

	$obj = $ins = array();
	$tags = array('cpu' => $ins, 'id' => $ins);

	/**
	 * Данные тега
	 */
	while ($item = $db->fetchassoc($inq, $config['cache']))
	{
		$tags['cpu'][$item['tagcpu']] = array
			(
				'id'       => $item['tagid'],
				'word'     => $item['tagword'],
				'custom'   => $item['custom'],
				'descript' => $item['descript'],
				'keywords' => $item['keywords'],
				'tagdesc'  => $item['tagdesc']
			);

		$tags['id'][$item['tagid']] = array
			(
				'id'       => $item['tagid'],
				'word'     => $item['tagword'],
				'custom'   => $item['custom'],
				'descript' => $item['descript'],
				'keywords' => $item['keywords'],
				'tagdesc'  => $item['tagdesc']
			);
	}

	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$ia = (isset($tags['cpu'][$cpu]) AND ! empty($tags['cpu'][$cpu])) ? 1 : 0;
		$id = ($ia == 1) ? $tags['cpu'][$cpu]['id'] : 0;

		$tagword = ($ia == 1) ? $tags['cpu'][$cpu]['word'] : '';
		$custom = ($ia == 1) ? $tags['cpu'][$cpu]['custom'] : '';
		$descript = ($ia == 1) ? $tags['cpu'][$cpu]['descript'] : '';
		$keywords = ($ia == 1) ? $tags['cpu'][$cpu]['keywords'] : '';
		$tagdesc = ($ia == 1) ? $tags['cpu'][$cpu]['tagdesc'] : '';

		$ins['cpu'] = ($ia == 1) ? '&amp;cpu='.$cpu : '';
	}
	else
	{
		$ia = (isset($tags['id'][$id]) AND ! empty($tags['id'][$id])) ? 1 : 0;

		$tagword = ($ia == 1) ? $tags['id'][$id]['word'] : '';
		$custom = ($ia == 1) ? $tags['id'][$id]['custom'] : '';
		$descript = ($ia == 1) ? $tags['id'][$id]['descript'] : '';
		$keywords = ($ia == 1) ? $tags['id'][$id]['keywords'] : '';
		$tagdesc = ($ia == 1) ? $tags['id'][$id]['tagdesc'] : '';

		$ins['cpu'] = null;
	}

	/**
	 * Ошибка страницы
	 */
	if ($ia == 0) {
		$tm->noexistprint();
	}

	/**
	 * Свой TITLE
	 */
	if (isset($custom) AND ! empty($custom)) {
		define('CUSTOM', $custom.$seopage);
	} else {
		$global['title'] = $tagword.$seopage;
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($keywords, THIS_EMPTY) == 0) ? $api->siteuni($keywords) : '';
	$global['descript'] = (preparse($descript, THIS_EMPTY) == 0) ? $api->siteuni($descript.$seopage) : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $tagword;
	$global['insert']['breadcrumb'] = array
		(
			'<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>',
			'<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=tags').'">'.$lang['all_tags'].'</a>',
			$tagword
		);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Обновляем рейтинг тега
	 */
	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL')) {
		$db->query("UPDATE ".$basepref."_".WORKMOD."_tag SET tagrating = tagrating + 1 WHERE tagcpu = '".$cpu."'");
	} else {
		$db->query("UPDATE ".$basepref."_".WORKMOD."_tag SET tagrating = tagrating + 1 WHERE tagid = '".$id."'");
	}

	/**
	 * Категории
	 */
	$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchassoc($inqs, $config['cache']))
	{
		$obj[$c['catid']] = $c;
	}

	/**
	 * Все публикации с тегом
	 */
	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD."
				 WHERE tags regexp '[[:<:]](".$id.")[[:>:]]'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY public DESC LIMIT ".$s.", ".$conf['pagcol']
			);

	$count = $db->fetchassoc
				(
					$db->query
						(
							"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."
							 WHERE tags regexp '[[:<:]](".$id.")[[:>:]]'
							 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
							 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
						)
				);

	if ($count['total'] > 0)
	{
		/**
		 * Листинг, формирование постраничной разбивки
		 */
		$ins['pages'] = null;
		if ($count['total'] > $conf['pagcol'])
		{
			$ins['pages'] = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $api->pages('', '', 'index', WORKMOD.'&amp;re=tags&amp;to=tag&amp;id='.$id.$ins['cpu'], $conf['pagcol'], $p, $count['total'])
									),
									$tm->manuale['pagesout']);
		}

		/**
		 * Переключатели
		 */
		$tm->unmanule['date'] = $conf['date'];
		$tm->unmanule['rating'] = $conf['rating'];
		$tm->unmanule['comment'] = $conf['comact'];
		$tm->unmanule['desc'] = empty($tagdesc) ? 'no' : 'yes';

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

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'descript' => $tagdesc,
				'content'  => $ins['output'],
				'pages'	   => $ins['pages'],
				'search'   => $tm->search($conf['search'], WORKMOD, 1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/tag'))
		);
	}
	else
	{
		// Данные отсутствуют
		$tm->message($lang['data_not'], 0, 0, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
    $tm->footer();
}
