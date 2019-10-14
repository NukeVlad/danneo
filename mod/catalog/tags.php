<?php
/**
 * File:        /mod/catalog/tags.php
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
 * Редирект, теги отключены
 */
if ($conf['tags'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Файл доп. функций
 */
require_once(DNDIR.'mod/'.WORKMOD.'/mod.function.php');

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
 */
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
		$tags.= $key['tagword'].', ';
	}
	if ( ! empty($tags)) {
		$tags = str_word(mb_strtolower($tags), 95, null);
		$global['keywords'] = chop(rtrim($tags), ',');
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
										'icon'    => $icon,
										'tagurl'  => $tagurl,
										'tagname' => $item['tagword'],
										'desc'    => $desc
									),
									$tm->manuale['rows']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['catcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array(
				'tagprint'	=> $ins['output'],
				'search'	=> catalog_search()
			),
			$ins['template']);
	}
	else
	{
		$tm->message($lang['data_not'], 0, 0, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * tag
 */
if ($to == 'tag')
{
	$id = preparse($id, THIS_INT);
	$obj = $tags = array();
	$ins = array
		(
			'discount'	=> null,
			'priceold'	=> null,
			'ajaxadd'	=> null,
			'image'		=> null,
			'icon'		=> null,
			'cat'		=> null,
			'tax'		=> null,
			'buying'	=> null,
			'buyinfo'	=> null,
			'tags'		=> null,
			'recinfo'	=> null
		);

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

		$ins['cpu'] = '';
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

		// Валюта
		if (isset($config['arrcur'][$config['viewcur']]))
		{
			$cur = $config['arrcur'][$config['viewcur']];
		}
		else
		{
			$cur = array
			(
				'value'			=> 1,
				'title'			=> '',
				'symbol_left'	=> '',
				'symbol_right'	=> '',
				'decimal'		=> 2,
				'decimalpoint'	=> '.',
				'thousandpoint'	=> ','
			);
		}

		/**
		 * Управление
		 */
		$tm->unmanule['buy'] = ($conf['buy'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['date'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['link'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['rating'] = ($conf['rating'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['review'] = ($conf['resact'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['desc'] = ( ! empty($tagdesc)) ? 'yes' : 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'cat' => null,
				'tax' => null,
				'buy' => null,
				'icon' => null,
				'tags' => null,
				'thumb' => null,
				'buyinfo' => null,
				'recinfo' => null,
				'price' => null,
				'priceold' => null,
				'discount' => null
			);

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($c = $db->fetchrow($inqs, $config['cache']))
		{
			$obj[$c['catid']] = $c;
		}

		$ins['content'] = array();
		while ($item = $db->fetchrow($inq))
		{
			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu']= (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Акция
			if ( ! empty($item['buyinfo']) AND $item['actinfo'] == 'yes')
			{
				$ins['buyinfo'] = $tm->parse(array(
										'stock'   => $lang['stock']
									),
									$tm->manuale['buyinfo']);
			}

			// Рекомендуем
			if ($item['rec'] == 1)
			{
				$ins['recinfo'] = $tm->parse(array(
										'mess' => $lang['recommended']
									),
									$tm->manuale['recinfo']);
			}

			// Теги
			if ($conf['tags'] == 'yes' AND $conf['buy'] == 'no')
			{
				$ins['temptags'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/tags'));
				$tagword = null;
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

			// Цена
			if ($item['price'] > 0)
			{
				$price = $cur['symbol_left'].formats($item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$ins['price'] = $tm->parse(array('price' => $price), $tm->manuale['price']);
			}
			else
			{
				$ins['price'] = $tm->parse(array('price' => $lang['agreed_price']), $tm->manuale['agreed']);
			}

			// Старая цена
			if ((int)$item['priceold'] > 0 AND $item['price'] < $item['priceold'])
			{
				$ins['old'] = $cur['symbol_left'].formats($item['priceold'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				if ($item['tax'] > 0)
				{
					$intax = Json::decode($conf['taxes']);
					$priceold = $item['priceold'];
					$priceold += ($item['priceold'] / 100) * $intax[$item['tax']]['tax'];
					$ins['old'] = $cur['symbol_left'].formats($priceold, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				}
				$ins['priceold'] = $tm->parse(array
										(
											'priceold' => $ins['old'],
											'langpriceold' => $lang['old_price']
										),
										$tm->manuale['priceold']);
			}

			// Скидка в %
			if ($item['price'] < $item['priceold'])
			{
				$count = number_format(($item['priceold'] - $item['price']) / ($item['priceold'] / 100), 1);
				$ins['discount'] = $tm->parse(array
										(
											'percent' => $count
										),
										$tm->manuale['discount']);
			}

			// Налог ндс
			if ($item['tax'] > 0)
			{
				$intax = Json::decode($conf['taxes']);
				$ins['tax'] = $tm->parse(array
								(
									'tax' => $intax[$item['tax']]['title'],
									'langtax' => $lang['tax'],
									'langinc' => $lang['included']
								),
								$tm->manuale['tax']);
				$item['price'] += ($item['price'] / 100) * $intax[$item['tax']]['tax'];
				$taxprice = $cur['symbol_left'].formats($item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$ins['price'] = $tm->parse(array('price' => $taxprice), $tm->manuale['price']);
			}

			// Доступ к покупкам
			$access = 1;
			if ($item['acc'] == 'user')
			{
				$access = (defined('USER_LOGGED')) ? 1 : 0;
				if (defined('GROUP_ACT') AND ! empty($item['groups']))
				{
					$group = Json::decode($item['groups']);
					$access = ( ! isset($group[$usermain['gid']])) ? 0 : 1;
				}
			}

			// Ajax add
			if ($config['ajax'] == 'yes')
			{
				$ins['ajaxadd'] = $tm->parse(array(), $tm->manuale['ajaxadd']);
			}

			// Блок покупки
			if ($conf['buy'] == 'yes' AND $access == 1)
			{
				$ins['buying'] = $tm->parse(array('mods' => WORKMOD), $tm->manuale['buy']);
			}

			// В корзину
			$ins['buylink'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=add&amp;id='.$item['id']);

			// Кол. отзывов
			$ins['review'] = ($conf['resact'] == 'yes') ? $item['reviews'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			$ins['content'][] = $tm->parse(array
				(
					'icon'			=> $ins['icon'],
					'cat'			=> $ins['cat'],
					'title'			=> $api->siteuni($item['title']),
					'text'			=> $api->siteuni($item['textshort']),
					'date'			=> $ins['public'],
					'image'			=> $ins['image'],
					'url'			=> $ins['url'],
					'mods'			=> WORKMOD,
					'read'			=> $lang['catalog_info'],
					'tags'			=> $ins['tags'],
					'buyinfo'		=> $ins['buyinfo'],
					'recinfo'		=> $ins['recinfo'],
					'langrate'		=> $lang['all_rating'],
					'rating'		=> $ins['rate'],
					'titlerate'		=> $ins['title_rate'],
					'review'		=> $ins['review'],
					'langreview'	=> $lang['response'],
					// покупки
					'buying'		=> $ins['buying'],
					'buylink'		=> $ins['buylink'],
					'post_url'		=> $ro->seo('index.php?dn='.WORKMOD),
					'langstore'		=> $lang['storehouse'],
					'store'			=> ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
					'ajaxadd'		=> $ins['ajaxadd'],
					'price'			=> $ins['price'],
					'langprice'		=> $lang['price'],
					'langbasket'	=> $lang['add_basket'],
					'discount'		=> $ins['discount'],
					'priceold'		=> $ins['priceold'],
					'count'			=> $item['amountmin'],
					'tax'			=> $ins['tax'],
					'id'			=> $item['id']
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
				'descript'	=> $tagdesc,
				'content'	=> $ins['output'],
				'pages'		=> $ins['pages'],
				'search'	=> catalog_search(1)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/tag'))
		);
	}
	else
	{
		$tm->message($lang['data_not'], 0, 1, 1);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
