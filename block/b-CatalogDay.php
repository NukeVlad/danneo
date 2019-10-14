<?php
/**
 * File:        /block/b-CatalogDay.php
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
$lang['block_day'] = isset($lang['block_day']) ? $lang['block_day'] : 'Catalog Day';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_day'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'catalog',
		'default'	=>	'catalog'
	),
	'sort' => array(
		'lang'		=>	'all_sorting',
		'form'		=>	'select',
		'value'		=>	array('hits' => 'all_hits', 'buyhits' => 'buy_amount', 'totalrating' => 'all_rating', 'rec' => 'recommended'),
		'default'	=>	'buyhits'
	),
	'order' => array(
		'lang'		=>	'all_sorting',
		'form'		=>	'select',
		'value'		=>	array('desc' => 'all_desc', 'random' => 'view_random'),
		'default'	=>	'desc'
	),
	'wrap' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 150,
		'default'	=> 150
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

	$day_count = $db->fetchrow($db->query("SELECT count(*) FROM ".$basepref."_".$bs['mod']));
	$day_rand = mt_rand(0, $day_count[0] - 1);

	$order = ($bs['order'] == 'random') ? $day_rand : 0;
	$inq = $db->query
				(
					"SELECT ".$bs['mod'].".*, cat.* FROM ".$basepref."_".$bs['mod']." AS ".$bs['mod']."
					 LEFT JOIN ".$basepref."_".$bs['mod']."_cat AS cat ON (".$bs['mod'].".catid = cat.catid)
					 WHERE ".$bs['mod'].".act = 'yes'
					 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
					 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
					 ORDER BY ".$bs['mod'].".".$bs['sort']." DESC
					 LIMIT ".$order.", 1"
				);

	if ($db->numrows($inq) > 0)
	{
		/**
		 * Управление
		 */
		$tm->unmanule['buy'] = ($config[$bs['mod']]['buy'] == 'yes') ? 'yes' : 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'thumb'    => null,
				'price'    => null,
				'agreed'   => null,
				'priceold' => null,
				'discount' => null,
				'ajaxadd'  => null,
				'maker'    => null
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
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/block.day'));

		while ($item = $db->fetchrow($inq))
		{
			// Переменные
			$ins['maker'] = $ins['discount'] = $ins['priceold'] = $ins['image'] = null;

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['image_thumb']) AND $bs['image'] == 'yes')
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt'] = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';
				$ins['image_thumb'] = ( ! empty($item['image'])) ? $item['image'] : $item['image_thumb'];

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $ins['image_thumb'],
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
					$intax = Json::decode($config[$bs['mod']]['taxes']);
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
				$intax = Json::decode($config[$bs['mod']]['taxes']);
				$item['price'] += ($item['price'] / 100) * $intax[$item['tax']]['tax'];
				$taxprice = $cur['symbol_left'].formats($item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
				$ins['price'] = $tm->parse(array
									(
										'price' => $taxprice
									),
									$tm->manuale['price']);
			}

			// В корзину
			$ins['buylink'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=add&amp;id='.$item['id']);

			// Текст
			$ins['text'] = ($bs['short'] == 'yes') ? str_word(deltags($api->siteuni($item['textshort'])), $bs['wrap']) : '';

			// Рейтинг
			$ins['rate']   = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['rating'] = '<img src="'.SITE_URL.'/template/'.SITE_TEMP.'/images/rating/'.$ins['rate'].'.png" alt="'.(($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].'']).'" />';

			// Ajax add
			if ($config['ajax'] == 'yes')
			{
				$ins['ajaxadd'] = $tm->parse(array(), $tm->manuale['ajaxadd']);
			}

			/**
			 * Производители
			 */
			if ($config[$bs['mod']]['maker'] == 'yes')
			{
				$inq_mak = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_maker ORDER BY posit ASC");

				while ($item_mak = $db->fetchrow($inq_mak))
				{
					$maker[$item_mak['makid']] = $item_mak;
				}

				if ($item['makid'] > 0 AND isset($maker[$item['makid']]) == 'yes')
				{
					$mak = $maker[$item['makid']];
					$mcpu = (defined('SEOURL') AND $mak['cpu']) ? '&amp;cpu='.$mak['cpu'] : '';
					$ins['maker'] = $tm->parse(array
										(
											'maker_lang' => $lang['manufacturer'],
											'maker_name' => $mak['makname'],
											'maker_url'  => $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=maker&amp;to=page&amp;id='.$item['makid'].$mcpu)
										),
										$tm->manuale['maker']);
				}
			}

			/**
			 * Вывод
			 */
			$bc.= $tm->parse(array
					(
						'title'      => $api->siteuni($item['title']),
						'text'       =>	$ins['text'],
						'image'      => $ins['image'],
						'url'        => $ins['url'],
						'mods'       => $bs['mod'],
						'read'       => $lang['catalog_info'],
						// покупки
						'post_url'   => $ro->seo('index.php?dn='.$bs['mod']),
						'buylink'    => $ins['buylink'],
						'langstore'  => $lang['storehouse'],
						'store'      => ($item['store'] == 'yes') ? $lang['all_there'] : $lang['all_there_no'],
						'ajaxadd'    => $ins['ajaxadd'],
						'price'      => $ins['price'],
						'langprice'  => $lang['price'],
						'langbasket' => $lang['add_basket'],
						'discount'   => $ins['discount'],
						'priceold'   => $ins['priceold'],
						'count'      => $item['amountmin'],
						'id'         => $item['id'],
						'rating'     => $ins['rating'],
						'maker'      => $ins['maker']
					),
					$ins['template']);
		}
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
