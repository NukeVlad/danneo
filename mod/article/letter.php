<?php
/**
 * File:        /mod/article/letter.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $p, $sym;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Массивы литер
 */
$lre = $api->letters(0);
$lto = $api->letters(1);

/**
 * ID литеры
 */
$letid = (isset($lre[$sym]) AND $lre[$sym] > 0) ? $lre[$sym] : 0;

/**
 * Литерация, если включена
 * -------------------------- */
if ($config[WORKMOD]['letter'] == 'yes' AND $letid > 0)
{
	$ins = array();
	$letid = preparse($letid, THIS_INT);

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.$lang['page_one'].'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $config[WORKMOD]['pagcol'] * ($p - 1);

	/**
	 * Количество статей
	 */
	$ins['count'] = $db->fetchrow
						(
							$db->query
							(
								"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."
								 WHERE letid = '".$letid."' AND act = 'yes'
								 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
								 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
							)
						);

	/**
	 * Ошибка листинга страниц, с литерацией
	 */
	$nums = ceil($ins['count']['total'] / $config[WORKMOD]['pagcol']);
	if ($p > $nums AND $p != 1)
	{
		$tm->noexistprint();
	}

	/**
	 * Литера
	 */
	$ins['sym'] = $lto[$letid];

	/**
	 * Свой TITLE
	 */
	define('CUSTOM', $global['modname'].', '.$lang['all_letter'].' - '.$ins['sym'][0].$seopage);

	/**
	 * Мета данные
	 */
	$global['descript'] = $config['mod'][WORKMOD]['map'].', '.$lang['all_letter'].' - '.$ins['sym'][0].$seopage;

	/**
	 * Меню, хлебные крошки, с учетом литеры
	 */
	$global['insert']['current'] = $lang['all_letter'].' - '.$ins['sym'][0];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['all_letter'].' – '.$ins['sym'][0]);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Рубрикатор с литерами
	 */
	foreach ($lto as $k => $v)
	{
		$class = ($k == $letid) ? ' class="active"' : '';

		if ($k <= 27) {
			$latin[] = '<a'.$class.' href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=letter&amp;sym='.$v[1]).'" title="'.$lang['all_letter'].' - '.$v[0].'">'.$v[0].'</a>';
		} else {
			$other[] = '<a'.$class.' href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=letter&amp;sym='.$v[1]).'" title="'.$lang['all_letter'].' - '.$v[0].'">'.$v[0].'</a>';
		}
	}
	$tm->parseprint(array
		(
			'latin' => implode('', $latin),
			'other' => ($config['langcode'] != 'en') ? implode('', $other) : ''
		),
		$tm->create('mod/'.WORKMOD.'/letter'));

	/**
	 * Вывод статей
	 */
	if ($ins['count']['total'] > 0)
	{
		/**
		 * Листинг, формирование постраничной разбивки
		 */
		$ins['pages'] = null;
		if ($ins['count']['total'] > 0 AND $ins['count']['total'] > $config[WORKMOD]['pagcol'])
		{
			$ins['pages'] = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $api->pages('', '', 'index', WORKMOD.'&amp;re=letter&amp;sym='.$ins['sym'][1], $config[WORKMOD]['pagcol'], $p, $ins['count']['total'])
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
	$tm->unmanule['desc'] = $tm->unmanule['title'] = 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat'	=> null,
			'icon'  => null,
			'tags'  => null,
			'thumb'	=> null,
			'author'=> null
		);

		$inq = $db->query
				(
					"SELECT catid, id, public, stpublic, unpublic, cpu, title, author, textshort,
					 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
					 FROM ".$basepref."_".WORKMOD."
					 WHERE letid = '".$letid."' AND act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY id DESC LIMIT ".$s.", ".$config[WORKMOD]['pagcol']
				);

		$inqs = $db->query("SELECT catid, catcpu, catname FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($c = $db->fetchrow($inqs, $config['cache']))
		{
			$obj[$c['catid']] = $c;
		}

		/**
		 * Теги в массив
		 */
		$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
		while ($t = $db->fetchassoc($taginq, $config['cache']))
		{
			$tc[$t['tagid']] = $t;
		}

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$ins['content'] = array();
		while ($item = $db->fetchrow($inq))
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
			if ($conf['linkcat'] == 'yes' AND isset($obj[$item['catid']]['catname']))
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

			// Кол. комментариев
			$ins['count'] = ($config[WORKMOD]['comact'] == 'yes') ? $item['comments'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

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
					'titlerate'	=> $ins['title_rate'],
					'rating'	=> $ins['rate'],
					'url'		=> $ins['url'],
					'tags'		=> $ins['tags'],
					'read'		=> $lang['in_detail']
				),
				$ins['template']);
		}

		// Разбивка
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
			$tm->parsein($tm->create('mod/'.WORKMOD.'/letter.index'))
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
else
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}
