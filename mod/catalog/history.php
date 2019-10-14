<?php
/**
 * File:        /mod/catalog/history.php
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
global $to, $db, $basepref, $config, $lang, $usermain, $tm, $global, $p;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект, если покупки отключены
 */
if ($conf['buy'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Option for WORKMOD
 */
$config['option'] = null;
$cache_option = DNDIR.'cache/'.WORKMOD.'.option.php';
if (file_exists($cache_option))
{
	include($cache_option);
	$config['option'] = $option;
}

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $lang['order_history'];
$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['order_history']);

$p = preparse($p, THIS_INT);
$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
$s = $conf['pagcol'] * ($p - 1);

$ks = implode(',', array($conf['statusok']));

// Количество страниц
$total = $db->fetchrow
			(
				$db->query
				(
					"SELECT COUNT(oid) AS total FROM ".$basepref."_".WORKMOD."_order
					 WHERE statusid IN (".$db->escape($ks).")
					 AND userid = '".$db->escape($usermain['userid'])."'"
				)
			);

$nums = ceil($total['total'] / $conf['pagcol']);

// Ошибка листинга
if ($p > $nums AND $p != 1)
{
    $tm->noexistprint();
}

/**
 * Вывод на страницу, шапка
 */
$tm->header();

if ($total['total'] > 0)
{
	$ins['pages'] = $api->pages('', '', 'index', WORKMOD.'&amp;re=history', $conf['pagcol'], $p, $total['total']);

	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD."_order
				 WHERE statusid IN (".$db->escape($ks).")
				 AND userid = '".$db->escape($usermain['userid'])."'
				 ORDER BY public DESC LIMIT ".$s.", ".$conf['pagcol']
			);

	// Шаблон, история заказов
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.history.list'));

	$order = $goods = $listgoods = $count = $product = array();
	$rows = '';

	while ($item = $db->fetchrow($inq))
	{
		$order[$item['oid']] = $item;
		$in = Json::decode($item['order']);
		$count[$item['oid']] = 0;
		if (is_array($in) AND sizeof($in) > 0)
		{
			foreach ($in as $k => $v)
			{
				$goods[$k] = $k;
				$count[$item['oid']]+= 1;
			}
		}
	}
	if (is_array($goods) AND sizeof($goods) > 0)
	{
		$inq = $db->query("SELECT id, title FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape(implode(',', $goods)).")");
		if ($db->numrows($inq) > 0)
		{
			while ($item = $db->fetchrow($inq))
			{
				$listgoods[$item['id']] = $item['title'];
			}
		}
		$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id IN (".$db->escape(implode(',', $goods)).")");
		while ($pitem = $db->fetchrow($pinq))
		{
			$product[$pitem['id']][$pitem['oid']][$pitem['vid']] = $pitem['vid'];
		}
	}
	$in = Json::decode($conf['status']);

	// Шаблон, детали заказа
	$ins['detail'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.detail'));

	// Регионы
	$country = require(DNDIR.'cache/cache.country.php');

	if ( ! is_array($country))
	{
		$country = array();

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($itemc = $db->fetchrow($inq))
		{
			$country[$itemc['countryid']] = $itemc;
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($itemc = $db->fetchrow($inq))
		{
			$country[$itemc['countryid']]['region'][$itemc['regionid']] = $itemc['regionname'];
		}
	}

	$delive = $payment = array();
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_delivery WHERE act = '1' ORDER BY posit");
	while ($items = $db->fetchrow($inq))
	{
		$delive[$items['did']] = $items;
	}

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
	while ($item = $db->fetchrow($inq))
	{
		$payment[$item['payid']] = $item;
	}

	$d = array
	(
		'firstname' => $lang['firstname'],
		'surname'   => $lang['surname'],
		'countryid' => $lang['country'],
		'regionid'  => $lang['state'],
		'city'      => $lang['city'],
		'zip'       => $lang['zip'],
		'adress'    => $lang['adress'],
		'phone'     => $lang['phone'],
		'comment'   => $lang['order_notice']
	);

	foreach ($order as $k => $v)
	{
		$c = isset($country[$v['countryid']]) ? $country[$v['countryid']]['countryname'] : '&#8212;';
		$r = isset($country[$v['countryid']]['region'][$v['regionid']]) ? $country[$v['countryid']]['region'][$v['regionid']] : '&#8212;';

		if ($v['delid'] > 0 AND isset($delive[$v['delid']]))
		{
			$d['delid'] = $lang['delivery'];
		}
		if ($v['payid'] > 0 AND isset($payment[$v['payid']]))
		{
			$d['payid'] = $lang['pay'];
		}

		$rd = '';
		foreach ($d as $rk => $rv)
		{
			if (isset($v[$rk]))
			{
				if ($rk == 'countryid') {
					$val = $c;
				} elseif ($rk == 'regionid') {
					$val = $r;
				} elseif ($rk == 'delid') {
					$val = isset($delive[$v['delid']]['title']) ? $delive[$v['delid']]['title'] : '';
				} elseif ($rk == 'payid') {
					$val = isset($payment[$v['payid']]['paytitle']) ? $payment[$v['payid']]['paytitle'] : '';
				} else {
					$val = $v[$rk];
				}

				$rd .= $tm->parse(array
						(
							'name' => $rv,
							'val' => $val
						),
						$tm->manuale['detailrows']);
			}
		}

		$detail = $tm->parse(array
					(
						'detailrows' => $rd
					),
					$ins['detail']);

		$s = isset($in[$v['statusid']]) ? $in[$v['statusid']] : '&#8212;';
		$o = Json::decode($v['order']);
		$productlist = '';

		if (is_array($o) AND sizeof($o) > 0)
		{
			foreach ($o as $ok => $ov)
			{
				if (isset($listgoods[$ok]))
				{
					$newopt = array();
					if (isset($product[$ok]))
					{
						if ($ov['count'] > 1)
						{
							$newopt[] = $lang['all_col'].' : '.$ov['count'];
						}
						foreach ($ov['option'] as $sk => $sv)
						{
							$p = $config['option'][$sk];
							if (isset($product[$ok][$sk][$sv]))
							{
								$newopt[] = $p['title'].' : '.$p['value'][$sv]['title'];
							}
						}
					}
					$productlist .= $tm->parse(array
										(
											'productname' => $listgoods[$ok],
											'productval' => '<br>'.implode(', ',$newopt),
										),
										$tm->manuale['productlist']);
				}
			}
		}

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

		$rows .= $tm->parse(array
					(
						'number_order'	=> $lang['number_order'],
						'id'			=> $k,
						'public'		=> $api->sitetime($v['public'],1),
						'order_status'	=> $lang['order_status'],
						'status'		=> $s,
						'products'		=> $lang['products'],
						'product'		=> $count[$k],
						'in_total'		=> $lang['in_total'],
						'intotal'		=> $cur['symbol_left'].formats($v['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
						'productlist'	=> $productlist,
						'proceed'		=> $lang['proceed'],
						'detail'		=> $detail,
						'orderdetail'	=> $lang['order_detail']
					),
					$tm->manuale['rows']);
	}

	$tm->parseprint(array('rows' => $rows), $ins['template']);
	$tm->parseprint(array('text' => '', 'pages' => $ins['pages']), $tm->manuale['pagesout']);
}
else
{
	$tm->parseprint(array('text' => $lang['order_empty']), $tm->create('mod/'.WORKMOD.'/order.empty'));
}

/**
 * Вывод на страницу, подвал
 */
$tm->footer();
