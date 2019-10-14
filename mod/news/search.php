<?php
/**
 * File:        /mod/news/search.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $sea, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * ID
 */
$id = preparse($id, THIS_INT);

/**
 * Массивы
 */
$obj = array('seaid' => 0, 'seaword' => '');
$ins = array();

/**
 * Поиск запрещен, редирект
 */
if ($conf['search'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Удаляем устаревшие результаты поиска
 */
$db->query("DELETE FROM ".$basepref."_".WORKMOD."_search WHERE seatime < '".(NEWTIME - $config['searchtime'])."'");

/**
 * Ошибка, если слово меньше или больше количества разрешенных символов
 */
if (
	preparse($sea, THIS_STRLEN) < $config['searchmin'] AND $id == 0 OR
	preparse($sea, THIS_STRLEN) > $config['searchmax'] AND $id == 0
) {
	$tm->error($lang['search_error']);
}

/**
 * Проверки
 */
if ($id > 0)
{
	$obj = $db->fetchassoc($db->query("SELECT seaid, seaword FROM ".$basepref."_".WORKMOD."_search WHERE seaid = '".$id."' AND seaip = '".$db->escape(REMOTE_ADDRS)."'"));
}
else
{
	// Проверяем на флуд
	$ins['flood'] = $db->fetchassoc
						(
							$db->query
							(
								"SELECT COUNT(seaid) AS total FROM ".$basepref."_".WORKMOD."_search
								 WHERE seatime > '".(NEWTIME - $config['searchflood'])."'
								 AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
							)
						);

	// Слишком частые запросы
	if ($ins['flood']['total'] > 0)
	{
		$tm->error($lang['search_flood']);
	}

	// Сохраняем в таблицу данные запроса
	$db->query
		(
			"INSERT INTO ".$basepref."_".WORKMOD."_search VALUES (
			 NULL,
			 '".$db->escape($sea)."',
			 '".$db->escape(REMOTE_ADDRS)."',
			 '".NEWTIME."'
			 )"
		);

	$obj = array(
		'seaid'	=> $db->insertid(),
		'seaword'	=> preparse($sea, THIS_ADD_SLASH)
	);
}

/**
 * Поиск
 * --------- */
if (empty($obj['seaword']) AND $obj['seaid'] == 0)
{
	// Совпадений не найдено
	$tm->message($lang['following_no_found']);
}
else
{
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$sf = $config['searchcol'] * ($p - 1);

	// Количество совпадений
	$ins['count'] = $db->fetchassoc
						(
							$db->query
							(
								"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE (
								 title LIKE '%".$db->escape($obj['seaword'])."%' OR
								 textshort LIKE '%".$db->escape($obj['seaword'])."%' OR
								 textmore LIKE '%".$db->escape($obj['seaword'])."%'
								 )
								 AND act = 'yes'
								 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
								 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
							)
						);

	/**
	 * Ошибка листинга страниц
	 */
	$nums = ceil($ins['count']['total'] / $config['searchcol']);
	if ($p > $nums AND $p != 1)
	{
		$tm->noexistprint();
	}

	/**
	 * Поиск в основной таблице
	 */
	$inq = $db->query
			(
				"SELECT id, catid, public, stpublic, unpublic, cpu, title, textshort, author,
				 image_thumb, image_align, image_alt, comments, hits, tags, rating, totalrating
				 FROM ".$basepref."_".WORKMOD." WHERE (
				 title LIKE '%".$db->escape($obj['seaword'])."%' OR
				 textshort LIKE '%".$db->escape($obj['seaword'])."%' OR
				 textmore LIKE '%".$db->escape($obj['seaword'])."%'
				 )
				 AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY id DESC LIMIT ".$sf.", ".$config['searchcol']
			);

	$inqs = $db->query("SELECT catid, catcpu, catname, icon FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchassoc($inqs, $config['cache']))
	{
		$area[$c['catid']] = $c;
	}

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['search_in_section'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['search_count'].' — '.$ins['count']['total']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	if ($ins['count']['total'] > 0)
	{
		/**
		 * Листинг, формирование постраничной разбивки
		 */
		$ins['pages'] = null;
		if ($ins['count']['total'] > $config['searchcol'])
		{
			$ins['pages'] = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $api->pages('', '', 'index', WORKMOD.'&amp;re=search&amp;id='.$obj['seaid'], $config['searchcol'], $p, $ins['count']['total'])
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
		$tm->unmanule['title'] = $tm->unmanule['desc'] = 'no';

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

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			// Переменные
			$ins['cat'] = $ins['icon'] = $ins['image'] = $ins['author'] = '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($area[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$area[$item['catid']]['catcpu'] : '';

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
			if (isset($area[$item['catid']]['catname']))
			{
				if ( ! empty($area[$item['catid']]['icon']) AND $conf['iconcat'] == 'yes')
				{
					$ins['icon'] = $tm->parse(array(
											'icon'  => $area[$item['catid']]['icon'],
											'alt'   => $api->siteuni($area[$item['catid']]['catname'])
										),
										$tm->manuale['icon']);
				}

				$ins['cat'] = $tm->parse(array(
										'caturl'  => $ins['caturl'],
										'catname' => $api->siteuni($area[$item['catid']]['catname'])
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
			$ins['content'][] = $tm->parse(array
				(
					'icon'		=> $ins['icon'],
					'cat'		=> $ins['cat'],
					'date'		=> $ins['public'],
					'title'		=> $tm->wordlight($obj['seaword'], $api->siteuni($item['title'])),
					'text'		=> $tm->wordlight($obj['seaword'], $api->siteuni($item['textshort'])),
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
					'read'		=> $lang['in_detail'],
					'tags'		=> '' // not
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
				'pages'   => $ins['pages'],
				'search'  => $tm->search($conf['search'], WORKMOD, 1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/search'))
		);
	}
	else
	{
		// Совпадений не найдено
		$tm->message($lang['following_no_found'], 0, 0, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
