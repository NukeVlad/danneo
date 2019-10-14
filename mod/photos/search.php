<?php
/**
 * File:        /mod/photos/search.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $sea, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Файл доп. функций
 */
require_once(DNDIR.'mod/'.WORKMOD.'/mod.function.php');

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
 * Ошибка, количество символов
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
	$obj = $db->fetchrow($db->query("SELECT seaid, seaword FROM ".$basepref."_".WORKMOD."_search WHERE seaid = '".$id."' AND seaip = '".REMOTE_ADDRS."'"));
}
else
{
	/**
	 * Проверяем на флуд
	 */
	$ins['flood'] = $db->fetchrow
						(
							$db->query
							(
								"SELECT COUNT(seaid) AS total FROM ".$basepref."_".WORKMOD."_search
								 WHERE seatime > '".(NEWTIME - $config['searchflood'])."'
								 AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
							)
						);
	if ($ins['flood']['total'] > 0)
	{
		$tm->error($lang['search_flood']);
	}

	/**
	 * Сохраняем данные запроса
	 */
	$db->query("INSERT INTO ".$basepref."_".WORKMOD."_search VALUES (NULL, '".$db->escape($sea)."', '".REMOTE_ADDRS."', '".NEWTIME."')");

	$obj = array(
				'seaid'   => $db->insertid(),
				'seaword' => preparse($sea, THIS_ADD_SLASH)
			);
}

/**
 * Поиск
 * --------- */
if (empty($obj['seaword']) AND $obj['seaid'] == 0)
{
	$tm->message($lang['following_no_found']);
}
else
{
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$sf = $config['searchcol'] * ($p - 1);

	/**
	 * Совпадений
	 */
	$ins['count'] = $db->fetchrow
						(
							$db->query
							(
								"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE (
								 title LIKE '%".$db->escape($obj['seaword'])."%' OR
								 text LIKE '%".$db->escape($obj['seaword'])."%'
								 )
								 AND act = 'yes'"
							)
						);

	/**
	 * Поиск в основной таблице
	 */
	$inq = $db->query
			(
				"SELECT catid, id, cpu, public, stpublic, unpublic, title, image_thumb, image_alt, comments, hits, tags, totalrating, rating, author
				 FROM ".$basepref."_".WORKMOD." WHERE (
				 title LIKE '%".$db->escape($obj['seaword'])."%' OR
				 text LIKE '%".$db->escape($obj['seaword'])."%'
				 )
				 AND act = 'yes' ORDER BY id DESC LIMIT ".$sf.", ".$config['searchcol']
			);

	/**
	 * Категории
	 */
	$inqs = $db->query("SELECT catid, catcpu, catname, icon FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchrow($inqs, $config['cache']))
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
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($area[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$area[$item['catid']]['catcpu'] : '';

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
		// Данные отсутствуют
		$tm->message($lang['following_no_found'], 0, 0, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
