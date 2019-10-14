<?php
/**
 * File:        /mod/catalog/index.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $ccpu, $cpu, $to, $p, $id, $selective;

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
$legaltodo = array('index', 'cat', 'page', 'send');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 */
if ($to == 'index')
{
	$obj = $ins = $tc = array();
	$ins = array
		(
			'last'     => null,
			'pages'    => null,
			'nocat'    => null,
			'category' => null
		);

	$posts = FALSE;

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Товаров без категории
	 */
	$total = $db->fetchassoc
				(
					$db->query
					(
						"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE catid = '0' AND act = 'yes'
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
	$obj = $area = array();

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
						'cd'          => $lang['cat_desc'],
						'lang_icon'   => $lang['all_icon'],
						'lang_col'    => $lang['all_col'],
						'lang_total'  => $lang['all_goods'],
						'lang_cat'    => $lang['all_cats'],
						'lang_hits'   => $lang['all_hits'],
						'catprint'    => $catprint,
						'total'       => $stat['total'],
						'hits'        => ( ! empty($stat['hits'])) ? $stat['hits'] : 0,
						'cats'        => $ins['cats']
					),
					$ins['tempcat']);
			}
		}
	}

	/**
	 * Управление
	 */
	$tm->unmanule['buy'] = ($conf['buy'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['date'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
	$tm->unmanule['link'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
	$tm->unmanule['rating'] = ($conf['rating'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
	$tm->unmanule['review'] = ($conf['resact'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
	$tm->unmanule['desc'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? 'yes' : 'no';

    /**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat'      => null,
			'tax'      => null,
			'buy'      => null,
			'icon'     => null,
			'tags'     => null,
			'thumb'    => null,
			'buyinfo'  => null,
			'recinfo'  => null,
			'price'    => null,
			'agreed'   => null,
			'priceold' => null,
			'discount' => null
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

	$inqs = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD." WHERE catid <> '0' AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY id DESC LIMIT ".$conf['pagmain']
			);

	/**
	 * Все теги в массив
	 */
	if ($db->numrows($inqs) > 0 OR $total['total'] > 0)
	{
		// Все теги
		$taginq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_tag", $config['cachetime'], WORKMOD);
		while ($t = $db->fetchassoc($taginq, $config['cache']))
		{
			$tc[$t['tagid']] = $t;
		}
	}

	/**
	 * Последние товары
	 */
	if ($db->numrows($inqs) > 0)
	{
		$posts = TRUE;

		// Валюта
		if (isset($config['arrcur'][$config['viewcur']]))
		{
			$cur = $config['arrcur'][$config['viewcur']];
		}
		else
		{
			$cur = array
					(
						'value'         => 1,
						'title'         => '',
						'symbol_left'   => '',
						'symbol_right'  => '',
						'decimal'       => 2,
						'decimalpoint'  => '.',
						'thousandpoint' => ','
					);
		}

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inqs))
		{
			// Переменные
			// Переменные
			$ins['tags']
			= $ins['discount']
			= $ins['priceold']
			= $ins['image']
			= $ins['icon']
			= $ins['cat']
			= $ins['tax']
			= $ins['buying']
			= $ins['buyinfo']
			= $ins['recinfo']
			= $ins['ajaxadd']
			= '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

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
					'icon'       => $ins['icon'],
					'cat'        => $ins['cat'],
					'title'      => $api->siteuni($item['title']),
					'text'       => $api->siteuni($item['textshort']),
					'date'       => $ins['public'],
					'image'      => $ins['image'],
					'url'        => $ins['url'],
					'mods'       => WORKMOD,
					'read'       => $lang['catalog_info'],
					'tags'       => $ins['tags'],
					'buyinfo'    => $ins['buyinfo'],
					'recinfo'    => $ins['recinfo'],
					'langrate'   => $lang['all_rating'],
					'rating'     => $ins['rate'],
					'titlerate'  => $ins['title_rate'],
					'review'     => $ins['review'],
					'langreview' => $lang['response'],
					// покупки
					'buying'     => $ins['buying'],
					'buylink'    => $ins['buylink'],
					'post_url'   => $ro->seo('index.php?dn='.WORKMOD),
					'langstore'  => $lang['storehouse'],
					'store'      => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
					'ajaxadd'    => $ins['ajaxadd'],
					'price'      => $ins['price'],
					'langprice'  => $lang['price'],
					'langbasket' => $lang['add_basket'],
					'discount'   => $ins['discount'],
					'priceold'   => $ins['priceold'],
					'count'      => $item['amountmin'],
					'tax'        => $ins['tax'],
					'id'         => $item['id']
				),
				$ins['standart']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		// Вывод, последние товары
		$ins['last'] = $tm->parse(array
			(
				'title' => $lang['new_goods'],
				'content' => $ins['output']
			),
			$ins['section']);
	}

	/**
	 * Товары без категории
	 */
	if ($total['total'] > 0)
	{
		$posts = TRUE;
		$inq = $db->query
				(
					"SELECT * FROM ".$basepref."_".WORKMOD." WHERE catid = '0' AND act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY id DESC LIMIT ".$s.", ".$conf['pagcol']
				);

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
						'symbol_left'   => '',
						'symbol_right'	=> '',
						'decimal'       => 2,
						'decimalpoint'	=> '.',
						'thousandpoint'	=> ','
					);
		}

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			// Переменные
			$ins['tags']
			= $ins['discount']
			= $ins['priceold']
			= $ins['image']
			= $ins['icon']
			= $ins['cat']
			= $ins['tax']
			= $ins['buying']
			= $ins['buyinfo']
			= $ins['recinfo']
			= $ins['ajaxadd']
			= '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Акция
			if ( ! empty($item['buyinfo']))
			{
				$ins['buyinfo'] = $tm->parse(array(
										'buyinfo' => $api->siteuni($item['buyinfo'])
									),
									$tm->manuale['buyinfo']);
			}

			// Рекомендуем
			if ($item['rec'] == 1)
			{
				$ins['recinfo'] = $tm->parse(array(
										'recinfo' => ''
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
					'icon'       => '', // not
					'cat'        => '', // not
					'title'      => $api->siteuni($item['title']),
					'text'       => $api->siteuni($item['textshort']),
					'date'       => $ins['public'],
					'image'      => $ins['image'],
					'url'        => $ins['url'],
					'mods'       => WORKMOD,
					'read'       => $lang['catalog_info'],
					'tags'       => $ins['tags'],
					'buyinfo'    => $ins['buyinfo'],
					'recinfo'    => $ins['recinfo'],
					'langrate'   => $lang['all_rating'],
					'rating'     => $ins['rate'],
					'titlerate'	 => $ins['title_rate'],
					'review'     => $ins['review'],
					'langreview' => $lang['response'],
					// покупки
					'buying'     => $ins['buying'],
					'buylink'    => $ins['buylink'],
					'post_url'   => $ro->seo('index.php?dn='.WORKMOD),
					'langstore'  => $lang['storehouse'],
					'store'      => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
					'ajaxadd'    => $ins['ajaxadd'],
					'price'		 => $ins['price'],
					'langprice'	 => $lang['price'],
					'langbasket' => $lang['add_basket'],
					'discount'   => $ins['discount'],
					'priceold'   => $ins['priceold'],
					'count'      => $item['amountmin'],
					'tax'        => $ins['tax'],
					'id'         => $item['id']
				),
				$ins['standart']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		// Вывод, товары без категории
		$ins['nocat'] = $tm->parse(array
			(
				'title'   => $lang['nocat_goods'],
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
			'nocat'	    => $ins['nocat'],
			'pages'	    => $ins['pages'],
			'noposts'	=> $lang['no_posts'],
			'search'	=> ($posts) ? catalog_search(1) : ''
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
	$obj = $menu = $area = $tc = array();

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
	$ins['sort'] = array('id', 'public', 'title', 'price', 'hits', 'rec');
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
					'lang_icon'  => $lang['all_icon'],
					'lang_col'   => $lang['all_col'],
					'lang_total' => $lang['all_goods'],
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

	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND catid IN (".$obj['catid'].$whe.")
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY buyinfo ".$order.", ".$sort." ".$order.", rating ".$order.", public ".$order." LIMIT ".$s.", ".$conf['pagcol']
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
		 * Управление
		 */
		$tm->unmanule['buy'] = ($conf['buy'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['date'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['link'] = ($conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['rating'] = ($conf['rating'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['review'] = ($conf['resact'] == 'yes' AND $conf['buy'] == 'no') ? 'yes' : 'no';
		$tm->unmanule['desc'] = (preparse($menu[$obj['catid']]['catdesc'], THIS_EMPTY) == 0) ? 'yes' : 'no';
		$tm->unmanule['subtitle'] = (preparse($menu[$obj['catid']]['subtitle'], THIS_EMPTY) == 0) ? 'yes' : 'no';

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
				'agreed' => null,
				'priceold' => null,
				'discount' => null
			);

		/**
		 * Валюта
		 */
		if (isset($config['arrcur'][$config['viewcur']]))
		{
			$cur = $config['arrcur'][$config['viewcur']];
		}
		else
		{
			$cur = array
					(
						'value'         => 1,
						'title'         => '',
						'symbol_left'   => '',
						'symbol_right'  => '',
						'decimal'       => 2,
						'decimalpoint'	=> '.',
						'thousandpoint' => ','
					);
		}

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
			// Переменные
			$ins['tags']
			= $ins['discount']
			= $ins['priceold']
			= $ins['image']
			= $ins['icon']
			= $ins['cat']
			= $ins['tax']
			= $ins['buying']
			= $ins['buyinfo']
			= $ins['recinfo']
			= $ins['ajaxadd']
			= '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$menu[$item['catid']]['catcpu'] : '';

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
			if (isset($menu[$item['catid']]['catname']) AND $item['catid'] != $obj['catid'] AND $conf['linkcat'] == 'yes')
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
					'icon'       => $ins['icon'],
					'cat'        => $ins['cat'],
					'title'      => $api->siteuni($item['title']),
					'text'       => $api->siteuni($item['textshort']),
					'date'       => $ins['public'],
					'image'      => $ins['image'],
					'url'        => $ins['url'],
					'mods'       => WORKMOD,
					'read'       => $lang['catalog_info'],
					'tags'       => $ins['tags'],
					'buyinfo'    => $ins['buyinfo'],
					'recinfo'    => $ins['recinfo'],
					'langrate'   => $lang['all_rating'],
					'rating'     => $ins['rate'],
					'titlerate'	 => $ins['title_rate'],
					'review'     => $ins['review'],
					'langreview' => $lang['response'],
					// покупки
					'buying'     => $ins['buying'],
					'buylink'    => $ins['buylink'],
					'post_url'   => $ro->seo('index.php?dn='.WORKMOD),
					'langstore'  => $lang['storehouse'],
					'store'      => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
					'ajaxadd'    => $ins['ajaxadd'],
					'price'      => $ins['price'],
					'langprice'  => $lang['price'],
					'langbasket' => $lang['add_basket'],
					'discount'   => $ins['discount'],
					'priceold'   => $ins['priceold'],
					'count'      => $item['amountmin'],
					'tax'        => $ins['tax'],
					'id'         => $item['id']
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
				'category' => $ins['category'],
				'catdesc'  => $ins['catdesc'],
				'title'    => $api->siteuni($obj['catname']),
				'subtitle' => $api->siteuni($obj['subtitle']),
				'content'  => $ins['output'],
				'pages'    => $ins['pages'],
				'search'   => catalog_search_cat($obj, $lang['search_in_section'])
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
 */
if ($to == 'page')
{
	$obj = $ins = $area = $maker = array();
	$id = preparse($id, THIS_INT);

	/**
	 * Запрос с учетом ЧПУ
	 */
	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query
					(
						"SELECT * FROM ".$basepref."_".WORKMOD."
						 WHERE cpu = '".$db->escape($cpu)."' AND act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
					);
		$v = 0;
	}
	else
	{
		$valid = $db->query
					(
						"SELECT * FROM ".$basepref."_".WORKMOD."
						 WHERE id = '".$id."' AND act = 'yes'
						 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
						 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
					);
		$v = 1;
	}

	$item = $db->fetchassoc($valid);

	/**
	 * Обновляем количество просмотров
	 */
	$db->query("UPDATE ".$basepref."_".WORKMOD." SET hits = hits + 1 WHERE id = '".$item['id']."'");

	/**
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchassoc($inq, $config['cache'])) {
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
	$p = preparse($p, THIS_INT);
	if ($conf['resact'] == 'yes')
	{
		$lp = (isset($p)) ? FALSE : TRUE;
		$p = ($p <= 1) ? 1 : $p;
		$nums = ceil($item['reviews'] / $conf['respage']);
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
					'catid'    => '',
					'parentid' => '',
					'catcpu'   => '',
					'catname'  => '',
					'icon'     => '',
					'access'   => '',
					'groups'   => '',
					'options'  => ''
				);
	}

	/**
	 * Свой TITLE
	 */
	if (isset($item['customs']) AND ! empty($item['customs'])) {
		define('CUSTOM', $api->siteuni($item['customs']));
	} else {
		$global['title'] = preparse($item['title'], THIS_TRIM);
		$global['title'].= (empty($obj['catname'])) ? '' : ' - '.$obj['catname'];
	}

	/**
	 * Мета данные
	 */
    $global['keywords'] = (empty($item['keywords'])) ? $api->seokeywords($item['title'].' '.$item['textshort'].' '.$item['textmore'],5,35) : $item['keywords'];
    $global['descript'] = (empty($item['descript'])) ? '' : $item['descript'];

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($item['title'])) ? $api->siteuni($item['title']) : '';
	$global['og_desc'] = ( ! empty($item['textshort'])) ? $api->siteuni($item['descript']) : $api->siteuni($item['textshort']);
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
	 * Переменные
	 */
	$ins = array(
		'cat'       => '',
		'icon'      => '',
		'image'     => '',
		'subimg'    => '',
		'tags'      => '',
		'langtags'  => '',
		'maker'     => '',
		'options'   => '',
		'details'   => '',
		'associat'  => '',
		'social'    => '',
		'priceold'  => '',
		'discount'  => '',
		'files'     => '',
		'tax'       => '',
		'rec'       => '',
		'tagword'   => '',
		'srows'     => '',
		'formrate'  => '',
		'valrate'   => '',
		'rating'    => '',
		'rate'      => 0,
		'itemprice' => '',
		'ajaxadd'   => '',
		// reviews
		'reviews'   => '',
		'reform'    => '',
		'ajaxbox'   => '',
		'amountmin' => ''
	);

	/**
	 * CPU
	 */
	$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
	$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

	// URL
	$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
	$ins['caturl'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

	/**
	 * Отзывы
	 */
	if ($conf['resact'] == 'yes')
	{
		$re = new Reviews(WORKMOD);

		// Вывод
		if ($item['reviews'] > 0)
		{
			$ins['reviews'] = $re->reviews($item['id'], $item['reviews'], $ins['cpu'], $ins['catcpu'], $item['title'], $p);
		}

		// Новые посты ajax
		$ins['ajaxbox'] = $tm->parse(array('empty' => 'empty'), $tm->manuale['ajaxbox']);

		// Форма
		$ins['reform'] = $re->reform($item['id'], $item['title']);
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
								'langrate'		=> $lang['rate_button'],
								'waitup'		=> $lang['wait_up'],
								'countrating'	=> $lang['rate_'.$ins['rate']]
							),
							$ins['temp_rating']);
	}

	/**
	 * Производители
	 */
	if ($conf['maker'] == 'yes')
	{
		$inqsets = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_maker ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($items = $db->fetchassoc($inqsets, $config['cache'])) {
			$maker[$items['makid']] = $items;
		}

		$tm->unmanule['adress'] = ( ! empty($maker[$item['makid']]['adress'])) ? 'yes' : 'no';
		$ins['temp_maker'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/maker'));

		if ($item['makid'] > 0 AND isset($maker[$item['makid']]))
		{
			$m = $maker[$item['makid']];
			$c = (defined('SEOURL') AND $m['cpu']) ? '&amp;cpu='.$m['cpu'] : '';
			$ins['maker'] = $tm->parse(array
										(
											'makurl'  => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=maker&amp;to=page&amp;id='.$item['makid'].$c),
											'makicon' => $m['icon'],
											'makname' => $m['makname'],
											'adress'  => $m['adress'],
											'langmaker' => $lang['manufacturer']
										),
										$ins['temp_maker']);
		}
	}

	/**
	 * Сопутствующие товары
	 */
	if ( ! empty($item['associat']))
	{
		$ins['temp_associat'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/associat'));
		$ass = Json::decode($item['associat']);

		unset($ass[$item['id']]);
		if (sizeof($ass) > 0)
		{
			$new = array();
			$in = implode(',', $ass);
			$ass_inq = $db->query
						(
							"SELECT * FROM ".$basepref."_".WORKMOD."
							 WHERE id IN(".$db->escape($in).") AND act = 'yes'
							 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
							 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
						);
			if ($db->numrows($ass_inq) > 0)
			{
				$ass_out = null;
				while ($aitem = $db->fetchassoc($ass_inq))
				{
					$ass_cpu = (defined('SEOURL') AND $aitem['cpu']) ? '&amp;cpu='.$aitem['cpu'] : '';
					$ass_ccpu = (defined('SEOURL') AND ! empty($area[$aitem['catid']]['catcpu'])) ? '&amp;ccpu='.$area[$aitem['catid']]['catcpu'] : '';

					$ass_out.= $tm->parse(array
											(
												'url'      => $ro->seo('index.php?dn='.WORKMOD.$ass_ccpu.'&amp;to=page&amp;id='.$aitem['id'].$ass_cpu),
												'title'    => $api->siteuni($aitem['title']),
												'subtitle' => $api->siteuni($aitem['subtitle'])
											),
											$tm->manuale['associat']);
				}

				$ins['associat'] = $tm->parse(array
										(
											'associat'     => $ass_out,
											'langassociat' => $lang['associat']
										),
										$ins['temp_associat']);
			}
		}
	}

	/**
	 * Настройки
	 */
	$ins['weight']   = (($item['weight'] > 0) ? 'yes' : 'no');
	$ins['creation'] = ($item['creation'] > 0) ? 'yes' : 'no';
	$ins['weight']   = (($item['weight'] > 0) ? 'yes' : 'no');
	$ins['articul']  = ( ! empty($item['articul'])) ? 'yes' : 'no';
	$ins['size']     = (($item['length'] > 0 AND $item['width'] > 0 AND $item['height'] > 0 ) ? 'yes' : 'no');
	$ins['fields']   = (! empty($obj['options']) OR ! empty($item['options'])) ? 'yes' : 'no';

	/**
	 * Переключатели
	 */
	$tm->unmanule = array
	(
		'weight'   => $ins['weight'],
		'size'     => $ins['size'],
		'files'    => (($item['files']) ? 'yes' : 'no'),
		'creation' => $ins['creation'],
		'maker'    => (($ins['maker']) ? 'yes' : 'no'),
		'associat' => (($ins['associat']) ? 'yes' : 'no'),
		'buy'      => $conf['buy'],
		'buyinfo'  => (( ! empty($item['buyinfo']) AND $item['actinfo'] == 'yes') ? 'yes' : 'no'),
		'tags'     => (($conf['tags'] == 'yes' AND ! empty($item['tags'])) ? 'yes' : 'no'),
		'social'   => $config['social_bookmark'],
		'articul'  => $ins['articul'],
		'date'     => $conf['date']
	);

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'cat'       => null,
			'icon'      => null,
			'tags'      => null,
			'files'     => null,
			'options'   => null,
			'social'    => null,
			'media'     => null,
			'image'     => null,
			'thumb'     => null,
			'buyinfo'   => null,
			'recinfo'   => null,
			'rows'      => null,
			'priceold'  => null,
			'discount'  => null,
			'price'     => null,
			'agreed'    => null,
			'tax'       => null,
			'ajaxadd'   => null,
			'amountmin' => null
		);

	// Валюта
	if (isset($config['arrcur'][$config['viewcur']]))
	{
		$cur = $config['arrcur'][$config['viewcur']];
	}
	else
	{
		$cur = array
				(
					'value'         => 1,
					'title'         => '',
					'symbol_left'   => '',
					'symbol_right'  => '',
					'decimal'       => 2,
					'decimalpoint'  => '.',
					'thousandpoint' => ','
				);
	}

	/**
	 * Тэги
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
								'tag_word' => $tc[$k]['tagword']
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
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/open'));

	/**
	 * Файлы
	 */
	if ( ! empty($item['files']))
	{
		$fs = Json::decode($item['files']);
		if (is_array($fs))
		{
			foreach ($fs as $k => $v)
			{
				$ins['files'] .= $tm->parse(array
									(
										'key' => $k,
										'path' => $v['path'],
										'title' => $v['title']
									),
									$tm->manuale['files']);
			}

		}
	}

	/**
	 * Доп. поля
	 */
	if ($ins['fields'] == 'yes')
	{
		$ins['temp_details'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/details'));

		$opt = Json::decode($obj['options']);
		$opts = Json::decode($item['options']);

		if (is_array($opt) AND sizeof($opt) > 0)
		{
			$in = implode(',', $opt);
			$listopt = $listoptval = $productopt = array();

			$inq = $db->query("SELECT oid,title FROM ".$basepref."_".WORKMOD."_option WHERE oid IN(".$db->escape($in).") ORDER BY posit ASC", $config['cachetime'], WORKMOD);
			while ($o = $db->fetchassoc($inq, $config['cache']))
			{
				$listopt[$o['oid']] = $o['title'];
			}

			if (sizeof($listopt) > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_product_option WHERE oid IN(".$db->escape($in).") AND id = '".$db->escape($item['id'])."'", $config['cachetime'], WORKMOD);
				while ($o = $db->fetchassoc($inq, $config['cache']))
				{
					$productopt[$o['oid']][$o['vid']] = $o['id'];
				}

				if (sizeof($productopt) > 0 OR sizeof($opts) > 0)
				{
					$inq = $db->query("SELECT vid, oid, title FROM ".$basepref."_".WORKMOD."_option_value WHERE oid IN(".$db->escape($in).") ORDER BY posit ASC", $config['cachetime'], WORKMOD);
					while ($o = $db->fetchassoc($inq, $config['cache'])) {
						$listoptval[$o['oid']][$o['vid']] = $o['title'];
					}

					foreach ($listopt as $k => $v)
					{
						if (isset($productopt[$k]) OR isset($opts[$k]) AND ! empty($opts[$k]))
						{
							$value = null;
							if (isset($opts[$k]))
							{
								$value = $api->siteuni(nl2br($opts[$k]));
							}
							if (isset($productopt[$k]))
							{
								$n = array();
								foreach ($productopt[$k] as $kp => $kv)
								{
									if (isset($listoptval[$k][$kp]))
									{
										$n[] = $listoptval[$k][$kp];
									}
								}
								$value = implode(', ',$n);
							}
							$ins['options'].= $tm->parse(array('name' => $v, 'value' => $value), $tm->manuale['options']);
						}
					}
				}
			}
		}

		if (isset($conf['sizes'])) {
			$sizes = Json::decode($conf['sizes']);
			$ins['salias'] = $sizes[$item['size']]['alias'];
		} else {
			$ins['salias'] = $item['size'];
		}

		if (isset($conf['weights'])) {
			$weights = Json::decode($conf['weights']);
			$ins['walias'] = $weights[$item['weights']]['alias'];
		} else {
			$ins['walias'] = $item['weights'];
		}

		if (in_array('yes', array($ins['articul'], $ins['size'], $ins['weight'], $ins['creation'])) OR ! empty($ins['options']))
		{
			$ins['details'] = $tm->parse(array
				(
					'options'      => $ins['options'],
					'feature'      => $lang['add_feature'],
					'langarticul'  => $lang['articul'],
					'articul'      => $item['articul'],
					'langcreation' => $lang['catalog_data'],
					'creation'     => $api->sitetime($item['creation'], 0, 1),
					'langsize'     => $lang['size'],
					'salias'       => $ins['salias'],
					'hintsize'     => $lang['size_hint'],
					'length'       => ($item['size'] == 'mm') ? intval($item['length']) : $item['length'],
					'width'        => ($item['size'] == 'mm') ? intval($item['width']) : $item['width'],
					'height'       => ($item['size'] == 'mm') ? intval($item['height']) : $item['height'],
					'langweight'   => $lang['weight'],
					'walias'       => $ins['walias'],
					'weight'       => ($item['weights'] == 'g') ? intval($item['weight']) : $item['weight'],
				),
				$ins['temp_details']);
		}
	}

	/**
	 * Содержимое
	 */
	$ins['textshort'] = $api->siteuni($item['textshort']);
	$ins['textmore']  = $api->siteuni($item['textmore']);

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

	$ins['temp_thumb'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/images'));

	if ( ! empty($item['image_thumb']))
	{
		// Sub images
		if ( ! empty($item['images']))
		{
			$im = Json::decode($item['images']);
			if (is_array($im))
			{
				foreach ($im as $v)
				{
					$ins['title']   = ( ! empty($v['title'])) ? $api->siteuni($v['title']) : '';
					if ( ! empty($v['image']))
					{
						$ins['subimg'].= $tm->parse(array(
												'thumb' => $v['thumb'],
												'image' => $v['image'],
												'title' => $ins['title']
											),
											$tm->manuale['image']);
					}
					else
					{
						$ins['subimg'].= $tm->parse(array(
												'thumb' => $v['thumb'],
												'title' => $ins['title']
											),
											$tm->manuale['thumb']);
					}

				}
			}
		}

		$ins['image'] = $tm->parse(array
							(
								'float'  => $ins['float'],
								'thumb'  => $item['image_thumb'],
								'image'  => $item['image'],
								'alt'    => $ins['alt'],
								'subimg' => $ins['subimg']
							),
							$ins['temp_thumb']);
	}

	/**
	 * Похожие товары
	 */
	if ($conf['rec'] == 'yes')
	{
		$inq = $db->query
				(
					"SELECT id, cpu, title, textshort, image_thumb, image_alt, buyinfo, actinfo, tax, price, rec
					 FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND catid = '".$item['catid']."'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."') AND (unpublic = 0 OR unpublic > '".NEWTIME."') AND id <> '".$item['id']."'
					 ORDER BY buyinfo DESC, rec DESC, rating DESC, public DESC LIMIT ".$conf['lastrec']
				);

		if ($db->numrows($inq) > 0)
		{
			$ins['temprec'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/rec'));

			while ($anyitem = $db->fetchassoc($inq))
			{
				$anycpu = (defined('SEOURL') AND $anyitem['cpu']) ? '&amp;cpu='.$anyitem['cpu'] : '';
				$anycatcpu = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';
				$anylink = $ro->seo('index.php?dn='.WORKMOD.$anycatcpu.'&amp;to=page&amp;id='.$anyitem['id'].$anycpu);

				$anyimage = $buyinfo = $recinfo = $recprice = null;

				// Акция
				if ( ! empty($anyitem['buyinfo']) AND $anyitem['actinfo'] == 'yes')
				{
					$buyinfo = $tm->parse(array(
										'stock'   => $lang['stock']
									),
									$tm->manuale['buyinfo']);
				}

				// Рекомендуем
				if ($anyitem['rec'] == 1)
				{
					$recinfo = $tm->parse(array(
										'mess' => $lang['recommended']
									),
									$tm->manuale['recinfo']);
				}

				// Цена
				if ($anyitem['price'] > 0)
				{
					if ($anyitem['tax'] > 0) {
						$anyintax = Json::decode($conf['taxes']);
						$anyitem['price'] += ($anyitem['price'] / 100) * $anyintax[$anyitem['tax']]['tax'];
					}
					$prices = $cur['symbol_left'].formats($anyitem['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
					$recprice = $tm->parse(array('price' => $prices), $tm->manuale['price']);
				}

				if ( ! empty($anyitem['image_thumb']))
				{
					$anyimage = $tm->parse(array
									(
										'url'   => $anylink,
										'alt'   => $anyitem['image_alt'],
										'img'   => $anyitem['image_thumb']
									),
									$tm->manuale['thumb']);
				}

				$ins['rec'] .= $tm->parse(array
									(
										'title'    => $api->siteuni($anyitem['title']),
										'link'     => $anylink,
										'image'    => $anyimage,
										'buyinfo'  => $buyinfo,
										'recinfo'  => $recinfo,
										'recprice' => $recprice,
										'text'     => $api->siteuni($anyitem['textshort'])
									),
									$tm->manuale['rows']);
			}

			$ins['rec'] = $tm->parse(array
							(
								'rectitle' => $lang['product_rec'],
								'recprint' => $ins['rec']
							),
							$ins['temprec']);
		}
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

	// Цена
	if ($item['price'] > 0)
	{
		$ins['availability'] = 'InStock';
		if ($item['tax'] > 0) {
			$intax = Json::decode($conf['taxes']);
			$item['price'] += ($item['price'] / 100) * $intax[$item['tax']]['tax'];
		}
		$ins['itemprice'] = formats($item['price'], 2, '.', '');
		$ins['prices'] = $cur['symbol_left'].formats($item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
		$ins['price'] = $tm->parse(array('price' => $ins['prices']), $tm->manuale['price']);
	}
	else
	{
		$ins['availability'] = 'PreOrder';
		$ins['itemprice'] = 0;
		$ins['price'] = $tm->parse(array('price' => $lang['agreed_price']), $tm->manuale['agreed']);
	}

	// Налог ндс
	if ($item['tax'] > 0)
	{
			$ins['tax'] = $tm->parse(array
							(
								'tax' => $intax[$item['tax']]['title'],
								'langtax' => $lang['tax'],
								'langinc' => $lang['included']
							),
							$tm->manuale['tax']);
	}

	// Ajax add
	if ($config['ajax'] == 'yes')
	{
		$ins['ajaxadd'] = $tm->parse(array(), $tm->manuale['ajaxadd']);
	}

	// Amountmin
	if ($item['amountmin'] > 1)
	{
		$ins['amountmin'] = $tm->parse(array
								(
									'count' => $item['amountmin'],
									'langmin' => $lang['amountmin'],
									'pcs' => $lang['all_pcs']
								), $tm->manuale['amount']);
	}

	// Дата
	$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

	// Подзаголовок
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	// Описание для schema.org
	if ( ! empty($item['descript'])) {
		$ins['descript'] = $api->siteuni($item['descript']);
	} elseif ( ! empty($item['subtitle'])) {
		$ins['descript'] = $api->siteuni($item['subtitle']);
	} else {
		$ins['descript'] = $api->siteuni($item['title']);
	}

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'icon'         => $ins['icon'],
			'cat'          => $ins['cat'],
			'id'           => $item['id'],
			'url'          => SITE_URL,
			'mods'         => WORKMOD,
			'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
			'title'        => $api->siteuni($item['title']),
			'subtitle'     => $ins['subtitle'],
			'textshort'    => $ins['textshort'],
			'textmore'     => $ins['textmore'],
			'date'         => $ins['public'],
			'image'        => $ins['image'],
			'subimg'       => $ins['subimg'],
			'buyinfo'      => $api->siteuni($item['buyinfo']),
			'stock'        => $lang['stock'],
			'tax'          => $ins['tax'],
			'langprice'    => $lang['price'],
			'price'        => $ins['price'],
			'priceold'     => $ins['priceold'],
			'langstore'    => $lang['storehouse'],
			'store'        => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
			'link'         => $ins['url'],
			'ratingvalue'  => $ins['rate'],
			'ratingcount'  => $item['rating'],
			'descript'     => $ins['descript'],
			'itemprice'    => $ins['itemprice'],
			'currency'     => $conf['currency'],
			'availability' => $ins['availability'],
			'count'        => $item['amountmin'],
			'amountmin'    => $ins['amountmin'],
			'langbasket'   => $lang['add_basket'],
			'tags'         => $ins['tags'],
			'langtags'     => $ins['langtags'],
			'social'       => $ins['social'],
			'associat'     => $ins['associat'],
			'langmaker'    => $lang['manufacturer'],
			'maker'        => $ins['maker'],
			'details'      => $ins['details'],
			'discount'     => $ins['discount'],
			'langdown'     => $lang['block_down'],
			'files'        => $ins['files'],
			'ajaxadd'      => $ins['ajaxadd'],
			'recommend'    => $ins['rec'],
			'rating'       => $ins['rating'],
			'reviews'      => $ins['reviews'],
			'reform'       => $ins['reform'],
			'ajaxbox'      => $ins['ajaxbox']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка send Request
 * -------------------- */
if ($to == 'send')
{
	header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
	header('Content-Type: text/html; charset='.$config['langcharset'].'');

	$ins['error'] = $ins['mess'] = null;
	$tm->manuale = array
		(
			'ok' => null,
			'error' => null
		);

	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/send.order'));

	if (empty($total))
	{
		$ins['error'] = $tm->parse(array
							(
								'title' => $lang['your_basket_empty'],
								'notice' => $lang['make_order_notice']
							),
							$tm->manuale['error']);

		$tm->parseprint(array('error' => $ins['error'], 'message' => ''), $ins['template']);

		exit;
	}

	function map_product($a, $b, $c)
	{
		return array('title' => $a, 'count' => $b, 'price' => $c);
	}
	$product = array_map("map_product", $title, $count, $price);

	/**
	 * Данные для отправки
	 */
	$to = ( ! empty($conf['request_email'])) ? $conf['request_email'] : $config['site_mail'];
	$from = $names." <".$config['site_mail'].">";
	$subject = $lang['order_goods']." — ".$config['site'];
	$message = $names.", ";
	$message.= $phone."\r\n\r\n";
	$message.= $lang['order'].":\r\n";
	$i = 1;
	foreach ($product as $kv)
	{
		$message.= "--\r\n".$kv['title']."\r\n";
		if (!empty($opttitle)) {
		$message.= $lang['order_type'].": ".$opttitle."\r\n";
		}
		$message.= $lang['all_col'].": ".$kv['count']."\r\n";
		$message.= $lang['sum'].": ".$kv['price']." ".$config['viewcur']."\r\n";
		$i++;
	}
	$message.= "\r\n--\r\n".$lang['all_alls'].": ".$total." ".$config['viewcur'];

	/**
	 * Отправка
	 */
	$cho = send_mail($to, $subject, $message, $from, '', true);

	/**
	 * Вывод сообщения
	 */
	if ($cho === TRUE)
	{
		$ins['mess'] = $tm->parse(array
						(
							'title'   => $lang['thank_order'],
							'success' => $lang['request_success'],
							'contact' => $lang['will_contact']
						),
						$tm->manuale['ok']);

		$tm->parseprint(array('error' => '', 'message' => $ins['mess']), $ins['template']);
	}
	else
	{
		$tm->message('Error: The message was not Sent!', 0, 0, 1);
	}
	exit;
}
