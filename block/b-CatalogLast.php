<?php
/**
 * File:        /block/b-CatalogLast.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $api, $ro, $config;

$bc = null;
$lang['catalog_last'] = isset($lang['catalog_last']) ? $lang['catalog_last'] : 'Catalog Last';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['catalog_last'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'catalog',
		'default'	=>	'catalog'
	),
	'cats' => array(
		'lang'		=> 'all_cat',
		'form'		=> 'text',
		'value'		=> '',
		'default'	=> '',
		'hint'	    => 'block_cat_help'
	),
	'col'  => array(
		'lang'		=>	'all_col',
		'form'		=>	'text',
		'value'		=>	1,
		'default'	=>	1
	),
	'row' => array(
		'lang'		=>	'who_col_all',
		'form'		=>	'text',
		'value'		=>	1,
		'default'	=>	1
	),
	'wrap' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 150,
		'default'	=> 150
	),
	'sort' => array(
		'lang'		=>	'all_sorting',
		'form'		=>	'select',
		'value'		=>	array('id' => 'ID', 'hits' => 'all_hits', 'buyhits' => 'buy_amount', 'public' => 'catalog_data', 'rec' => 'recommended'),
		'default'	=>	'id'
	),
	'order' => array(
		'lang'		=>	'all_sorting',
		'form'		=>	'select',
		'value'		=>	array('desc' => 'all_desc', 'asc' => 'all_acs'),
		'default'	=>	'desc'
	),
	'cat' => array(
		'lang'		=> 'all_cat_one',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'icon' => array(
		'lang'		=> 'cat_icon',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'short' => array(
		'lang'		=>	'input_text',
		'form'		=>	'checkbox',
		'value'		=>	'yes',
		'default'	=>	'yes'
	),
	'image' => array(
		'lang'		=>	'all_image',
		'form'		=>	'checkbox',
		'value'		=>	'yes',
		'default'	=>	'yes'
	),
	'tags' => array(
		'lang'		=>	'all_tags',
		'form'		=>	'checkbox',
		'value'		=>	'yes',
		'default'	=>	'yes'
	),
	'date' => array(
		'lang'		=> 'sys_date',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'link' => array(
		'lang'		=> 'all_link',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'rating' => array(
		'lang'		=> 'all_rating',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'review' => array(
		'lang'		=> 'response',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'buy' => array(
		'lang'		=> 'buy',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	)
);

if (defined('SETTING'))
{
	return $bs;
}

/**
 * Получаем настройки
 */
if (
	isset($config['bsarray']) AND
	is_array($config['bsarray']) AND
	isset($config['mod'][$config['bsarray']['mod']])
) {
	$ins = array();
	$bs = $config['bsarray'];

	$cats = preg_replace('/[^0-9\,]/', '', trim($bs['cats'], ' '));
	$cats = ($cats !== '') ? " ".$bs['mod'].".catid IN (".$cats.") AND" : "";

	$inq = $db->query
				(
					"SELECT ".$bs['mod'].".*, cat.* FROM ".$basepref."_".$bs['mod']." AS ".$bs['mod']."
					 LEFT JOIN ".$basepref."_".$bs['mod']."_cat AS cat ON (".$bs['mod'].".catid = cat.catid)
					 WHERE".$cats." ".$bs['mod'].".act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY ".$bs['sort']." ".$bs['order']." LIMIT ".$bs['col']
				);

	if ($db->numrows($inq) > 0)
	{
		$buy = ($bs['buy'] == 'yes' AND $config[$bs['mod']]['buy'] == 'yes') ? 'yes' : 'no';

		/**
		 * Управление
		 */
		$tm->unmanule['buy'] = $buy;
		$tm->unmanule['date'] = ($bs['date'] == 'yes' AND $buy == 'no') ? 'yes' : 'no';
		$tm->unmanule['link'] = ($bs['link'] == 'yes' AND $buy == 'no') ? 'yes' : 'no';
		$tm->unmanule['rating'] = ($config[$bs['mod']]['rating'] == 'yes' AND $bs['rating'] == 'yes' AND $buy == 'no') ? 'yes' : 'no';
		$tm->unmanule['review'] = ($config[$bs['mod']]['resact'] == 'yes' AND $bs['review'] == 'yes' AND $buy == 'no') ? 'yes' : 'no';
		$check_tags = (isset($config[$bs['mod']]['tags']) AND $config[$bs['mod']]['tags'] == 'yes' AND $bs['tags'] == 'yes' AND $buy == 'no') ? 'yes' : 'no';

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

		// Все теги
		if ($check_tags == 'yes')
		{
			$taginq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_tag");
			while ($t = $db->fetchrow($taginq))
			{
				$tc[$t['tagid']] = $t;
			}
		}

		// Валюта
		if (isset($config['arrcur'][$config['viewcur']]))
		{
			$cur = $config['arrcur'][$config['viewcur']];
		}
		else
		{
			$cur = array(
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
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/standart'));

		$content = array();
		while ($item = $db->fetchrow($inq))
		{
			// Переменные
			$ins['tags'] = $ins['discount'] = $ins['priceold'] = $ins['image'] = $ins['icon'] = $ins['cat'] = $ins['tax'] = $ins['buying'] = $ins['buyinfo'] = $ins['recinfo'] = '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

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
			if ($check_tags == 'yes')
			{
				$ins['temptags'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/tags'));
				$tagword = null;
				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
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
			if ( ! empty($item['image_thumb']) AND $bs['image'] == 'yes')
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
			if (isset($item['catname']) AND $bs['cat'] == 'yes')
			{
				$ins['cat'] = $tm->parse(array(
									'caturl'  => $ins['caturl'],
									'catname' => $api->siteuni($item['catname'])
								),
								$tm->manuale['cat']);
			}

			// Иконка категории
			if ( ! empty($item['icon']) AND $bs['icon'] == 'yes')
			{
				$ins['icon'] = $tm->parse(array(
										'icon'  => $item['icon'],
										'alt'   => $api->siteuni($item['catname'])
									),
									$tm->manuale['icon']);
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
					$intax = Json::decode($config[$bs['mod']]['taxes']);
					$priceold = $item['priceold'];
					$priceold += ($item['priceold'] / 100) * $intax[$item['tax']]['tax'];
					$ins['old'] = $cur['symbol_left'].formats($priceold, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				}
				$ins['priceold'] = $tm->parse(array
										(
											'priceold'     => $ins['old'],
											'langpriceold' => $lang['old_price']
										),
										$tm->manuale['priceold']);
			}

			// Скидка в %
			if ($item['price'] < $item['priceold'])
			{
				$discount = number_format(($item['priceold'] - $item['price']) / ($item['priceold'] / 100), 1);
				$ins['discount'] = $tm->parse(array
										(
											'percent' => $discount
										),
										$tm->manuale['discount']);
			}

			// Налог ндс
			if ($item['tax'] > 0)
			{
				$intax = Json::decode($config[$bs['mod']]['taxes']);
				$ins['tax'] = $tm->parse(array
								(
									'tax'     => $intax[$item['tax']]['title'],
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
			if ($bs['buy'] == 'yes' AND $config[$bs['mod']]['buy'] == 'yes' AND $access == 1)
			{
				$ins['buying'] = $tm->parse(array('mods' => $bs['mod']), $tm->manuale['buy']);
			}

			// В корзину
			$ins['buylink'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=add&amp;id='.$item['id']);

			// Кол. отзывов
			$ins['review'] = ($config[$bs['mod']]['resact'] == 'yes') ? $item['reviews'] : '';

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			// Текст
			$ins['text'] = ($bs['short'] == 'yes') ? str_word(deltags($api->siteuni($item['textshort'])), $bs['wrap']) : '';

			// Вывод
			$content[] = $tm->parse(array
				(
					'icon'       => $ins['icon'],
					'cat'        => $ins['cat'],
					'title'      => $api->siteuni($item['title']),
					'text'       => $api->siteuni($item['textshort']),
					'date'       => $ins['public'],
					'image'      => $ins['image'],
					'url'        => $ins['url'],
					'mods'       => $bs['mod'],
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
					'post_url'   => $ro->seo('index.php?dn='.$bs['mod']),
					'langstore'  => $lang['storehouse'],
					'store'		 => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
					'ajaxadd'    => $ins['ajaxadd'],
					'price'		 => $ins['price'],
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

		$bc.= $tm->tableprint($content, $bs['row']);
	}
	else
	{
		$bc.= $lang['data_not'];
	}
}
else
{
	$bc.= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
