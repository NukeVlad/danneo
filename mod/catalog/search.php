<?php
/**
 * File:        /mod/catalog/search.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $ro, $api, $global, $sea, $search, $id;

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
 * Массив obj
 */
$obj = array(
    'seaid'    => 0,
    'cid'      => 0,
    'seaart'   => '',
    'seaword'  => '',
    'seamin'   => 0,
    'seamax'   => 0,
    'seamaker' => 0,
    'seaopt'   => ''
);

/**
 * REDIRECT
 */
if ($conf['search'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * search block ~field
 */
if (isset($sea))
{
	$search['word'] = $sea;
}

/**
 * search
 */
if (is_array($search))
{
	$obj['seaart'] = (isset($search['art'])) ? $search['art'] : '';
	$obj['seaword'] = (isset($search['word'])) ? $search['word'] : '';
	$obj['seamin'] = (isset($search['min']) AND is_numeric($search['min'])) ? number_format($search['min'],4,'.','') : 0;
	$obj['seamax'] = (isset($search['max']) AND is_numeric($search['max'])) ? number_format($search['max'],4,'.','') : 0;

	if (isset($search['maker']) AND $conf['maker'] == 'yes')
	{
		$maker = preparse($search['maker'],THIS_INT);
		if ($maker > 0)
		{
			$m = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_maker ORDER BY posit ASC");
			while ($c = $db->fetchrow($inq))
			{
				$m[$c['makid']] = $c;
			}
			$obj['seamaker'] = (isset($m[$maker])) ? $maker : 0;
		}
	}

	if (isset($search['cid']))
	{
		$cid = preparse($search['cid'], THIS_INT);
		if ($cid > 0)
		{
			$a = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC");
			while ($c = $db->fetchrow($inq))
			{
				$a[$c['catid']] = $c;
			}
			$obj['cid'] = (isset($a[$cid])) ? $cid : 0;
		}
		if ($obj['cid'] > 0 AND isset($search['opt']) AND is_array($search['opt']))
		{
			if (isset($a[$obj['cid']]['options']))
			{
				$catopt = Json::decode($a[$obj['cid']]['options']);
				if (sizeof($catopt) > 0)
				{
					$opt = $val = array();
					$inq = $db->query("SELECT oid FROM ".$basepref."_".WORKMOD."_option WHERE search = '1' ORDER BY posit ASC");
					while ($c = $db->fetchrow($inq))
					{
						$opt[$c['oid']] = $c['oid'];
					}
					$inq = $db->query("SELECT oid, vid FROM ".$basepref."_".WORKMOD."_option_value");
					while ($c = $db->fetchrow($inq))
					{
						$val[$c['oid']][$c['vid']] = $c['oid'];
					}
					foreach ($search['opt'] as $k => $v)
					{
						if(isset($opt[$k]) AND isset($catopt[$k]))
						{
							if(isset($val[$k][$v])) {
								$obj['seaopt'][$k] = $v;
							}
						}
					}
				}
			}
		}
	}
}

/**
 * DELETE
 */
$db->query("DELETE FROM ".$basepref."_".WORKMOD."_search WHERE seatime < '".(NEWTIME - $config['searchtime'])."'");

/**
 * error
 */
$error = array
(
	'min' => ((preparse($obj['seaword'], THIS_STRLEN) < $config['searchmin'] AND preparse($obj['seaart'], THIS_STRLEN) < $config['searchmin'] AND $id == 0) ? 1 : 0),
	'max' => ((preparse($obj['seaword'], THIS_STRLEN) > $config['searchmax'] AND preparse($obj['seaart'], THIS_STRLEN) < $config['searchmax'] AND $id == 0) ? 1 : 0),
	'seamin' => (($obj['seamin'] > 0 AND $id == 0) ? 1 : 0),
	'seamax' => (($obj['seamax'] > 0 AND $id == 0) ? 1 : 0),
);

/**
 * key price
 */
$key = ($error['min'] OR $error['max']) ? 1 : 0;
$price = ($error['seamin'] OR $error['seamax']) ? 1 : 0;

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $lang['search_catalog'];
$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['search']);

/**
 * EMPTY
 */
if ($key AND $price == 0 OR $price AND $key == 0)
{
	$tm->header();

		// Нет совпадений, ошибка
		$tm->error($lang['catalog_search_error'], 0, 0, 0, 1, 1);

		// Форма поиска
		echo catalog_search(0, isset($search['maker']) ? $search['maker'] : 0);

	$tm->footer();
}

/**
 * WORK
 */
if ($id > 0)
{
	$obj = $db->fetchrow
			(
				$db->query
				(
					"SELECT * FROM ".$basepref."_".WORKMOD."_search
					 WHERE seaid = '".$id."' AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
				)
			);
}
else
{
	$ins['flood'] = $db->fetchrow
						(
							$db->query
							(
								"SELECT COUNT(seaid) AS total FROM ".$basepref."_".WORKMOD."_search
								 WHERE seatime > '".(NEWTIME - $config['searchflood'])."'
								 AND seaip = '".$db->escape(REMOTE_ADDRS)."'"
							)
						);
	/**
	 * Ошибка, слишком частые запросы
	 */
	if ($ins['flood']['total'] > 0)
	{
		$tm->error($lang['search_flood'], 0);
	}

	if (is_array($obj['seaopt']))
	{
		$obj['seaopt'] = serialize($obj['seaopt']);
	}

	$db->query
		(
			"INSERT INTO ".$basepref."_".WORKMOD."_search VALUES (
			 NULL,
			 '".$db->escape($obj['cid'])."',
			 '".$db->escape($obj['seaart'])."',
			 '".$db->escape($obj['seaword'])."',
			 '".$db->escape($obj['seamin'])."',
			 '".$db->escape($obj['seamax'])."',
			 '".$db->escape($obj['seamaker'])."',
			 '".$db->escape($obj['seaopt'])."',
			 '".$db->escape(REMOTE_ADDRS)."',
			 '".NEWTIME."'
			 )"
		);

	$obj['seaid'] = $db->insertid();
}

/**
 * EMPTY
 */
if ($obj['seaid'] == 0)
{
	$tm->header();

		// Нет совпадений, ошибка
		$tm->error($lang['following_no_found'], 0, 0, 0, 1, 1);

		// Форма поиска
		echo catalog_search(0, isset($search['maker']) ? $search['maker'] : 0);

	$tm->footer();
}
else
{
	$p   = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$sf  = $config['searchcol'] * ($p - 1);

	$and = array();
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

	if ( ! empty($obj['seaword']))
	{
		$and[] = "(title LIKE '%".$db->escape($obj['seaword'])."%' OR textshort LIKE '%".$db->escape($obj['seaword'])."%' OR textmore LIKE '%".$db->escape($obj['seaword'])."%')";
	}
	if ( ! empty($obj['seaart']))
	{
		$and[] = "(articul LIKE '%".$db->escape($obj['seaart'])."%')";
	}
	if ($obj['cid'] > 0)
	{
		$and[] = "catid = '".intval($obj['cid'])."'";
	}
	if ($obj['seamin'] > 0)
	{
		$and[] = "price >= '".$obj['seamin']."'";
	}
	if ($obj['seamax'] > 0)
	{
		$and[] = "price <= '".$obj['seamax']."'";
	}
	if ($obj['seamaker'] > 0)
	{
		$and[] = "makid = '".intval($obj['seamaker'])."'";
	}
	if ( ! empty($obj['seaopt']) AND $obj['cid'] > 0)
	{
		$opt = unserialize($obj['seaopt']);
		$un = $run = array();
		if (sizeof($opt) > 0)
		{
			foreach ($opt as $k => $v)
			{
				$un[] = "oid = '".intval($k)."' AND vid = '".intval($v)."'";
			}
			if (sizeof($un) > 0)
			{
				$insql = implode(' OR ', $un);
				$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_product_option WHERE ".$insql);
				if ($db->numrows($inq) > 0)
				{
					$in = $cl = array();
					while ($item = $db->fetchrow($inq))
					{
						$cl[$item['id']] = $in[$item['id']][$item['oid']][$item['vid']] = $item['id'];
					}
					if (sizeof($in) > 0)
					{
						foreach ($opt as $k => $v)
						{
							foreach ($in as $ik => $iv)
							{
								if ( ! isset($in[$ik][$k][$v]))
								{
									unset($cl[$ik]);
								}
							}
						}
						if (is_array($cl) AND sizeof($cl) > 0)
						{
							$whe = implode(',', $cl);
							$and[] = "id IN (".$db->escape($whe).")";
						}
					}
				}
			}
		}
	}

	if (sizeof($and) == 0)
	{
		$tm->header();

		// Нет совпадений, ошибка
		$tm->error($lang['following_no_found'], 0, 0, 0, 1, 1);

		// Форма поиска
		echo catalog_search(0, isset($search['maker']) ? $search['maker'] : 0);

		$tm->footer();
	}

	$sql = implode(' AND ', $and)." AND act = 'yes'";
	$ins['count'] = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE ".$sql));
	$nums = ceil($ins['count']['total'] / $config['searchcol']);
	if ($p > $nums AND $p != 1)
	{
		$tm->noexistprint();
	}

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['search_catalog'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['search_count'].': '.$ins['count']['total']);

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
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE ".$sql." ORDER BY price ASC LIMIT ".$sf.", ".$config['searchcol']);

		$inqs = $db->query("SELECT catid, catcpu, catname, icon FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
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
					'title'			=> $tm->wordlight($obj['seaword'], $api->siteuni($item['title'])),
					'text'			=> $tm->wordlight($obj['seaword'], $api->siteuni($item['textshort'])),
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

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'content' => $ins['output'],
				'pages'	  => $ins['pages'],
				'search'  => catalog_search(0, isset($search['maker']) ? $search['maker'] : 0)
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/search'))
		);
	}
	else
	{
		$tm->error($lang['following_no_found'], 0, 0, 0, 1, 1);

		// Форма поиска
		echo catalog_search(0, isset($search['maker']) ? $search['maker'] : 0);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
