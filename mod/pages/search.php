<?php
/**
 * File:        /mod/pages/search.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Глобальные
 */
global	$db, $basepref, $config, $lang, $usermain, $tm, $ro, $api, $global, $sea, $id;

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
 * Редирект, поиск запрещен
 */
if ($config['pages']['search'] == 'no')
{
	redirect(defined('HTTP_REFERERS') ? HTTP_REFERERS : SITE_URL);
}

/**
 * Удаляем устаревшие результаты поиска
 */
$db->query("DELETE FROM ".$basepref."_pages_search WHERE seatime < '".(NEWTIME - $config['searchtime'])."'");

/**
 * Ошибка, если слово меньше или больше
 */
if (preparse($sea, THIS_STRLEN) < $config['searchmin'] AND $id == 0 OR preparse($sea, THIS_STRLEN) > $config['searchmax'] AND $id == 0)
{
	$tm->error($lang['search_error']);
}

/**
 * Проверки
 */
if ($id > 0)
{
	$obj = $db->fetchassoc
			(
				$db->query
				(
					"SELECT seaid, seaword FROM ".$basepref."_pages_search
					 WHERE seaid = '".$id."'
					 AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
				)
			);
}
else
{
	// Проверяем на флуд
	$ins['flood'] = $db->fetchassoc
						(
							$db->query
							(
								"SELECT COUNT(seaid) AS total FROM ".$basepref."_pages_search
								 WHERE seatime > '".(NEWTIME - $config['searchflood'])."'
								 AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
							)
						);

	// Ошибка, слишком частые запросы
	if ($ins['flood']['total'] > 0)
	{
		$tm->error($lang['search_flood']);
	}

	// Сохраняем в таблицу данные запроса
	$db->query
	(
		"INSERT INTO ".$basepref."_pages_search VALUES (
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
	$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$sf = $config['searchcol'] * ($p - 1);

	// Количество совпадений
	$ins['count'] = $db->fetchassoc
						(
							$db->query
							(
								"SELECT COUNT(paid) AS total FROM ".$basepref."_pages WHERE (
								 title LIKE '%".$db->escape($obj['seaword'])."%' OR
								 textshort LIKE '%".$db->escape($obj['seaword'])."%' OR
								 textmore LIKE '%".$db->escape($obj['seaword'])."%'
								 )
								 AND act = 'yes'"
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
				"SELECT paid, mods, cpu, public, title, textshort, image_thumb, image_align, image_alt FROM ".$basepref."_pages WHERE (
				 title LIKE '%".$db->escape($obj['seaword'])."%' OR
				 textshort LIKE '%".$db->escape($obj['seaword'])."%' OR
				 textmore LIKE '%".$db->escape($obj['seaword'])."%'
				 )
				 AND act = 'yes'
				 ORDER BY paid DESC LIMIT ".$sf.", ".$config['searchcol']
			);

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['search_in_section'];
	$global['insert']['breadcrumb'] = array($global['modname'], $lang['search_count'].' — '.$ins['count']['total']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Массив с именами модов
	 */
	$page_mod = Json::decode($config['pages']['mods']);
	foreach($page_mod as $v)
	{
		$name_mod[$v['mod']] = $v['name'];
	}

	if ($ins['count']['total'] > 0)
	{
		/**
		 * Переключатели
		 */
		$tm->unmanule['date'] = ($config['pages']['date'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['desc'] = $tm->unmanule['link'] = 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'mod' => null,
				'thumb' => null,
				'pagesout' => null
			);

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
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			$ins['image'] = $ins['mod'] = NULL;

			// Ссылка
			$ins['url'] = $ro->seo('index.php?dn=pages'.(($item['mods'] != 'pages') ? '&amp;pa='.$item['mods'] : '').'&amp;cpu='.$item['cpu']);

			// Мод
			if ($item['mods'] != 'pages')
			{
				$set = $name = array();
				$set = Json::decode($config['pages']['mods']);
				foreach($set as $v)
				{
					$name[$v['mod']] = $v['name'];
				}
				$ins['mod'] = $tm->parse(array(
									'mod_url' => $ro->seo('index.php?dn=pages&amp;pa='.$item['mods']),
									'mod_name' => $api->siteuni($name[$item['mods']])
								),
								$tm->manuale['mod']);
			}

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

			// Вывод в шаблон
			$ins['content'][] = $tm->parse(array
				(
					'mod'   => $ins['mod'],
					'date'  => $item['public'],
					'title' => $tm->wordlight($obj['seaword'], $api->siteuni($item['title'])),
					'text'  => $tm->wordlight($obj['seaword'], $api->siteuni($item['textshort'])),
					'image' => $ins['image'],
					'url'   => $ins['url'],
					'read'  => $lang['all_read']
				),
				$ins['template']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $config[WORKMOD]['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'content' => $ins['output'],
				'pages'	  => $ins['pages'],
				'search'  => $tm->search($config[WORKMOD]['search'], 'pages', 1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/search'))
		);
	}
	else
	{
		$tm->message($lang['following_no_found'], 0, 0, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
