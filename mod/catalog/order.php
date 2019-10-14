<?php
/**
 * File:        /mod/catalog/order.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global,
		$p, $to, $id, $did, $delivery, $agree, $data, $ok;

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
 * id, did
 */
$id = preparse($id, THIS_INT);
$did = preparse($did, THIS_INT);

/**
 * delete
 */
$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE lifetime < '".intval(NEWTIME - $conf['cookieexp'])."'");

/**
 * форма авторизации
 */
if ( ! defined('USER_LOGGED'))
{
	define('REDIRECT', $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order'));

	$global['insert']['current'] = $lang['checkout'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['checkout']);

	$tm->noaccessprint();
}

/**
 * Метки
 */
$legaltodo = array('index', 'edit', 'save', 'del', 'delive', 'custom', 'checkout', 'payment', 'confirm', 'check');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to),$legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * weightconvert
 */
function weightconvert($sin, $w, $v, $t)
{
	global $config;
	if ($v != $t)
	{
		if (isset($sin[$t]))
		{
			$w = $w * $sin[$t]['value'];
		}
	}
	return $w;
}

/**
 * Метка index
 */
if ($to == 'index')
{
	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['order_active'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=order').'">'.$lang['order_active'].'</a>');

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	$ks = implode(',', array($conf['statuspersonal'], $conf['statusdelive'], $conf['statuscheckout']));

	// Количество заказов
	$total = $db->fetchrow($db->query("SELECT COUNT(oid) AS total FROM ".$basepref."_".WORKMOD."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'"));
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
		$ins['pagesview'] = ($total['total'] > $conf['pagcol']) ? 1 : 0;
		$ins['pages'] = ($ins['pagesview'] == 1) ? $api->pages('', '', 'index', WORKMOD.'&amp;re=order', $conf['pagcol'], $p, $total['total']) : '';

		$inq = $db->query
				(
					"SELECT * FROM ".$basepref."_".WORKMOD."_order
					 WHERE statusid IN (".$db->escape($ks).")
					 AND userid = '".$db->escape($usermain['userid'])."'
					 ORDER BY public DESC LIMIT ".$s.", ".$conf['pagcol']
				);

		$obj = array();
		$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($c = $db->fetchrow($inqs, $config['cache']))
		{
			$obj[$c['catid']] = $c;
		}

		/**
		 * Шаблон, все заказы
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.list'));

		$order = $goods = $listgoods = $count = $url = array();
		$rows = '';
		while ($item = $db->fetchrow($inq))
		{
			$order[$item['oid']] = $item;
			$in = Json::decode($item['orders']);
			$count[$item['oid']] = 0;
			if (is_array($in) AND sizeof($in) > 0)
			{
				foreach ($in as $k => $v)
				{
					$goods[$k] = $k;
					$count[$item['oid']] += 1;
				}
			}
		}
		if (is_array($goods) AND sizeof($goods) > 0)
		{
			$inq = $db->query("SELECT id, catid, cpu, title FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape(implode(',',$goods)).")");
			if ($db->numrows($inq) > 0)
			{
				while ($item = $db->fetchrow($inq))
				{
					$listgoods[$item['id']] = $item;
				}
			}

			$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id IN (".$db->escape(implode(',',$goods)).")");
			while ($pitem = $db->fetchrow($pinq))
			{
				$product[$pitem['id']][$pitem['oid']][$pitem['vid']] = $pitem['vid'];
			}
		}

		$in = Json::decode($conf['status']);

		/**
		 * Шаблон, детали заказа
		 */
		$ins['detail'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.detail'));

		// Регионы
		$country = null;
		$cache_country = DNDIR.'cache/cache.country.php';
		if (file_exists($cache_country))
		{
			$country = include($cache_country);
		}

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
			'delid'     => $lang['delivery'],
			'payid'     => $lang['pay'],
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

			$rd = null;
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
					$val = ! empty($val) ? $val : '&#8212;';
					$rd.= $tm->parse(array(
										'name' => $rv,
										'val' => $val
										),
										$tm->manuale['detailrows']);
				}
			}
			$detail = $tm->parse(array('detailrows' => $rd), $ins['detail']);

			$s = isset($in[$v['statusid']]) ? $in[$v['statusid']] : '&#8212;';

			$o = Json::decode($v['orders']);
			$productlist = $productcol = null;
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
								$productcol = $ov['count'].' '.$lang['all_pcs'];
							}
							if ( ! empty($ov['option']))
							{
								foreach ($ov['option'] as $sk => $sv)
								{
									$p = $config['option'][$sk];
									if (isset($product[$ok][$sk][$sv]))
									{
										$newopt[] = $p['title'].': '.$p['value'][$sv]['title'];
									}
								}
							}
						}

						$ins['cpu'] = (defined('SEOURL') AND $listgoods[$ok]['cpu']) ? '&amp;cpu='.$listgoods[$ok]['cpu'] : '';
						$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$listgoods[$ok]['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$listgoods[$ok]['catid']]['catcpu'] : '';

						$productlist .= $tm->parse(array
											(
												'id'          => $ok,
												'cpu'         => $ins['cpu'],
												'productcol'  => $productcol,
												'productname' => $listgoods[$ok]['title'],
												'productval'  => implode('<br> ',$newopt),
												'linklist'    => $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$ok.$ins['cpu']),
											),
											$tm->manuale['productlist']);
					}
				}
			}

			$url['pay'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=delive&amp;id='.$k);
			$url['del'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=del&amp;id='.$k);

			if ($v['delid'] > 0) {
				$v['price'] = $v['price'] + $v['delivprice'];
			}

			if($v['payid'] == 0 AND $v['delid'] > 0)
			{
				$url['pay'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=checkout&amp;id='.$k);
			}
			elseif($v['delid'] > 0 AND $v['payid'] > 0)
			{
				$url['pay'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=confirm&amp;id='.$k);
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

			$rows .= $tm->parse(array
						(
							'number_order' => $lang['number_order'],
							'id'           => $k,
							'langpublic'   => $lang['order_date'],
							'public'       => $api->sitetime($v['public'], 1, 1),
							'order_status' => $lang['order_status'],
							'status'       => $s,
							'products'     => $lang['products'],
							'product'      => $count[$k],
							'in_total'     => $lang['in_total'],
							'intotal'      => $cur['symbol_left'].formats($v['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
							'productlist'  => $productlist,
							'href'         => $url['pay'],
							'proceed'      => $lang['proceed'],
							'hrefdel'      => $url['del'],
							'del'          => $lang['del_box'],
							'detail'       => $detail,
							'productcol'   => $lang['all_col'],
							'orderdetail'  => $lang['order_detail']
						),
						$tm->manuale['rows']);
		}

		$tm->parseprint(array('rows' => $rows), $ins['template']);

		/**
		 * Листинг, вывод
		 */
		if ($ins['pagesview'] == 1)
		{
			$tm->parseprint(array('text'  => $lang['all_pages'], 'pages' => $ins['pages']), $tm->manuale['pagesout']);
		}
	}
	else
	{
		$tm->parseprint(array('text' => $lang['order_empty']), $tm->create('mod/'.WORKMOD.'/order.empty'));
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка del
 */
if ($to == 'del')
{
	if ($ok == 'yes')
	{
		$db->query
			(
				"DELETE FROM ".$basepref."_".WORKMOD."_order
				 WHERE oid = '".$db->escape($id)."'
				 AND userid = '".$db->escape($usermain['userid'])."'
				 AND statusid <> '".$db->escape($conf['statusok'])."'"
			);

		redirect($ro->seo('index.php?dn='.WORKMOD.'&re=order'));
	}
	else
	{
		$global['insert']['current'] = $lang['del_order'];
		$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['del_order']);

		$tm->header();
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.delete'));
		$tm->parseprint(array
			(
				'id' => $id,
				'post_url' => $ro->seo('index.php?dn='.WORKMOD),
				'number_order' => $lang['number_order'],
				'goback' => $lang['all_goback'],
				'delete' => $lang['all_delet']
			),
			$ins['template']);
		$tm->footer();
	}
}

/**
 * Метка deliv
 */
if ($to == 'delive')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_delivery WHERE act = '1' ORDER BY posit");
		$deliv = array();
		while ($items = $db->fetchrow($inq))
		{
			$c = Json::decode($items['data']);
			if (isset($c['country'][$item['countryid']]) AND isset($c['state'][$item['regionid']]))
			{
				$deliv[$items['did']] = $items;
			}
		}

		if (sizeof($deliv) > 0)
		{
			/**
			 * Меню, хлебные крошки
			 */
			$global['insert']['current'] = $lang['delivery'];
			$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=order').'">'.$lang['checkout'].'</a>', $lang['delivery']);

			/**
			 * Вывод на страницу, шапка
			 */
			$tm->header();

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
			 * Шаблон, доставка
			 */
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/delive.service'));

			$deliverymethod = '';
			$i = 0;
			$weight = 0;
			$sin = Json::decode($conf['weights']);
			$load = Json::decode($item['orders']);
			$oid = array_keys($load);
			$in = implode(',', $oid);
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");
			if ($db->numrows($inq) > 0)
			{
				while ($itemw = $db->fetchrow($inq))
				{
					$weight += weightconvert($sin, $itemw['weight'] * $load[$itemw['id']]['count'], 'g', $itemw['weights']);
				}
			}

			// Регионы
			$country = null;
			$cache_country = DNDIR.'cache/cache.country.php';
			if (file_exists($cache_country))
			{
				$country = include($cache_country);
			}

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

			foreach ($deliv as $k => $v)
			{
				$icon = ($v['icon']) ? '<img src="'.SITE_URL.'/'.$v['icon'].'" alt="'.$v['title'].'" />' : '';
				$r = Json::decode($v['data']);
				if ($v['type'] == 'auto')
				{
					require_once(DNDIR.'core/shop/delivery/'.$v['ext'].'.php');
					$r['geo'] = $country;
					$r['countryid'] = $item['countryid'];
					$r['regionid'] = $item['regionid'];
					$r['price'] = $item['price'];
					$class = str_replace('.', '', $v['ext']);
					$d = new $class;
					$addform = $d->addform($k, $cur, $r, $weight);
					unset($d);
				}
				else
				{
					if ($r['data'] == 'fix')
					{
						if ($v['price'] > 0)
						{
							$addform = $cur['symbol_left'].formats(round($v['price'] + $item['price']), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
						}
						else
						{
							$addform = $cur['symbol_left'].formats(round($item['price']), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
						}
					}
					else if ($r['data'] == 'percent')
					{
						$c = round(($item['price'] / 100) * $r['percent']);
						$addform = $cur['symbol_left'].formats($c + $item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
					}
					else if ($r['data'] == 'fixpercent')
					{
						$c = round(($v['price'] + ($item['price'] / 100) * $r['percent']));
						$addform = $cur['symbol_left'].formats($c + $item['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];
					}
				}
				$disabled = (empty($addform)) ? ' disabled' : '';
				$checked = ($i == 0 AND empty($disabled)) ? ' checked' : '';
				$deliverymethod .= $tm->parse(array
										(
											'did'		=> $k,
											'checked'	=> $checked,
											'disabled'	=> $disabled,
											'icon'		=> $icon,
											'title'		=> $v['title'],
											'descript'	=> $v['descr'],
											'addform'	=> $addform
										),
										$tm->manuale['delivery']);
				$i ++;
			}
			$tm->parseprint(array
				(
					'post_url'        => $ro->seo('index.php?dn='.WORKMOD),
					'delivery_method' => $lang['delivery_method'],
					'deliverymethod'  => $deliverymethod,
					'proceed'         => $lang['proceed'],
					'price'           => $lang['price'],
					'alldecs'         => $lang['descript'],
					'id'              => $id
				),
				$ins['template']);
		}
		else
		{
			$tm->parseprint(array('text' => $lang['order_empty']), $tm->create('mod/'.WORKMOD.'/order.empty'));
		}
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка custom
 */
if ($to == 'custom')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_delivery WHERE act = '1' ORDER BY posit");
		$deliv = array();
		while ($items = $db->fetchrow($inq))
		{
			$c = Json::decode($items['data']);
			if (isset($c['country'][$item['countryid']]) AND isset($c['state'][$item['regionid']]))
			{
				$deliv[$items['did']] = $items;
			}
		}

		if (sizeof($deliv) > 0 AND isset($deliv[$did]))
		{
			$custom = $deliv[$did];
			$r = Json::decode($custom['data']);
			$cr = array();
			$c = $i = $weight = 0;

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

			$sin = Json::decode($conf['weights']);
			$load = Json::decode($item['orders']);
			$oid = array_keys($load);
			$in = implode(',', $oid);
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");
			if ($db->numrows($inq) > 0)
			{
				while ($itemw = $db->fetchrow($inq))
				{
					$weight += weightconvert($sin, $itemw['weight'] * $load[$itemw['id']]['count'], 'g', $itemw['weights']);
				}
			}

			// Регионы
			$country = null;
			$cache_country = DNDIR.'cache/cache.country.php';
			if (file_exists($cache_country))
			{
				$country = include($cache_country);
			}

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

			if ($custom['type'] == 'auto')
			{
				require_once(DNDIR.'core/shop/delivery/'.$custom['ext'].'.php');

				$r['geo'] = $country;
				$r['countryid'] = $item['countryid'];
				$r['regionid'] = $item['regionid'];
				$r['price'] = $item['price'];
				$r['delivery'] = $delivery;

				$class = str_replace('.', '', $custom['ext']);
				$d = new $class;
				$cr = $d->checkform($did, $cur, $r, $weight);
			}
			else
			{
				if ($r['data'] == 'fix')
				{
					$c = ($custom['price'] > 0) ? $custom['price'] : 0;
				}
				else if ($r['data'] == 'percent')
				{
					$c = ($item['price'] / 100) * $r['percent'];
				}
				else if ($r['data'] == 'fixpercent')
				{
					$c = ($custom['price'] + ($item['price'] / 100) * $r['percent']);
				}
				$cr = array($c, '');
			}

			$qwe = '';
			if (isset($cr[1]) AND is_array($cr[1]))
			{
				$qwe = ",delive = '".$db->escape(Json::encode($cr[1]))."'";
			}

			$db->query
				(
					"UPDATE ".$basepref."_".WORKMOD."_order SET
					 delivprice	= '".$db->escape(intval($cr[0]))."',
					 delid		= '".$db->escape($did)."',
					 statusid	= '".$db->escape($conf['statusdelive'])."'".$qwe."
					 WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'"
				);

			redirect($ro->seo('index.php?dn='.WORKMOD.'&re=order&to=checkout&id='.$id));
		}
		else
		{
			$tm->error($lang['noisset_delive']);
		}
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}
}

/**
 * Метка checkout
 */
if ($to == 'checkout')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."' AND delid > 0");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$payment = array();
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
		while ($item = $db->fetchrow($inq))
		{
			$payment[$item['payid']] = $item;
		}

		if (sizeof($payment) > 0)
		{
			/**
			 * Меню, хлебные крошки
			 */
			$global['insert']['current'] = $lang['payment_method'];
			$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=order').'">'.$lang['checkout'].'</a>', $lang['payment_method']);

			/**
			 * Вывод на страницу, шапка
			 */
			$tm->header();

			$paymentmethod = '';

			// Шаблон, Способ оплаты
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/checkout'));

			$i = 0;
			foreach ($payment as $k => $v)
			{
				$icon = ($v['payicon']) ? '<img src="'.SITE_URL.'/'.$v['payicon'].'" alt="'.$v['paytitle'].'" />' : '';
				$checked = ($i == 0) ? ' checked' : '';
				$paymentmethod .= $tm->parse(array
									(
										'id'		=> $k,
										'checked'	=> $checked,
										'icon'		=> $icon,
										'title'		=> $v['paytitle'],
										'descript'	=> $v['paydescr']
									),
									$tm->manuale['payment']);
				$i ++;
			}

			$url = $ro->seo('index.php?dn='.WORKMOD.'&re=agreement');
			$add_agree = $tm->parse(array('href' => $url), $lang['add_agree']);

			$tm->parseprint(array
				(
					'post_url'       => $ro->seo('index.php?dn='.WORKMOD),
					'payment_method' => $lang['payment_method'],
					'paymentmethod'	 => $paymentmethod,
					'add_agree'      => $add_agree,
					'proceed'        => $lang['proceed'],
					'alldecs'        => $lang['descript'],
					'oid'            => $id
				),
				$ins['template']);

			$tm->footer();
		}
		else
		{
			$tm->error($lang['noisset_payment']);
		}
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}
}

/**
 * Метка payment
 */
if ($to == 'payment')
{
	$agree = preparse($agree, THIS_INT);

	if ($agree == 0)
	{
		$tm->error($lang['error_agree']);
	}

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."' AND statusid <> '".intval($conf['statuscheckout'])."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
		$payment = array();
		while ($items = $db->fetchrow($inq))
		{
			$payment[$items['payid']] = $items;
		}

		if (sizeof($payment) > 0 AND isset($payment[$payid]))
		{
			$db->query
				(
					"UPDATE ".$basepref."_".WORKMOD."_order SET
					 payid = '".$db->escape($payid)."',
					 statusid = '".$db->escape($conf['statuscheckout'])."'
					 WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'"
				);

			redirect($ro->seo('index.php?dn='.WORKMOD.'&re=order&to=confirm&id='.$id));
		}
		else
		{
			$tm->error($lang['noisset_payment']);
		}
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}
}

/**
 * Метка confirm
 */
if ($to == 'confirm')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
		$payment = array();
		while ($items = $db->fetchrow($inq))
		{
			$payment[$items['payid']] = $items;
		}

		if (sizeof($payment) > 0 AND isset($payment[$item['payid']]))
		{
			// Шаблон, детали
			$ins['detail'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/order.detail'));

			// Регионы
			$country = null;
			$cache_country = DNDIR.'cache/cache.country.php';
			if (file_exists($cache_country))
			{
				$country = include($cache_country);
			}

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
			while ($items = $db->fetchrow($inq))
			{
				$payment[$items['payid']] = $items;
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

			$v = $item;
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

			$c = $cou = isset($country[$v['countryid']]) ? $country[$v['countryid']]['countryname'] : '&#8212;';
			$r = $reg = isset($country[$v['countryid']]['region'][$v['regionid']]) ? $country[$v['countryid']]['region'][$v['regionid']] : '&#8212;';

			if ($v['delid'] > 0 AND isset($delive[$v['delid']]))
			{
				$d['delid'] = $lang['delivery'];
			}
			if ($v['payid'] > 0 AND isset($payment[$v['payid']]))
			{
				$d['payid'] = $lang['pay'];
			}
			$d['total'] = $lang['in_total'];

			$rd = '';
			$sum = $item['price'] + $item['delivprice'];
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

					$val = ! empty($val) ? $val : '&#8212;';
					$rd .= $tm->parse(array
							(
								'name' => $rv,
								'val' => $val
							),
							$tm->manuale['detailrows']);
				}
				elseif($rk == 'total')
				{
					$rd .= $tm->parse(array
							(
								'name' => $lang['in_total'],
								'val' => $cur['symbol_left'].formats($sum, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right']
							),
							$tm->manuale['detailrows']);
				}
			}
			$detail = $tm->parse(array('detailrows' => $rd),$ins['detail']);

			/**
			 * Меню, хлебные крошки
			 */
			$global['insert']['current'] = $lang['order_detail'];
			$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=order').'">'.$lang['checkout'].'</a>', $lang['order_detail']);

			$tm->header();

				$p = $payment[$item['payid']];
				$s = Json::decode($p['paydata']);

				require_once(DNDIR.'core/shop/payment/'.$p['payext'].'.php');
				$r = new payment;
				$r->confirm($p['paydata'], $detail, $item);

			$tm->footer();
		}
		else
		{
			$tm->error($lang['noisset_payment']);
		}
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}
}

/**
 * Метка check
 */
if ($to == 'check')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND statusid = '".$db->escape($conf['statuscheckout'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);

		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
		$payment = array();
		while ($items = $db->fetchrow($inq))
		{
			$payment[$items['payid']] = $items;
		}

		if (sizeof($payment) > 0 AND isset($payment[$item['payid']]))
		{
			$p = $payment[$item['payid']];
			require_once(DNDIR.'core/shop/payment/'.$p['payext'].'.php');
			$r = new payment;
			$r->check($item);
		}
		else
		{
			echo 'Error : '.$lang['noisset_payment'];
			exit();
		}
	}
	else
	{
		echo 'Error : '.$lang['noisset_order'];
		exit();
	}
}

/**
 * Метка edit
 */
if ($to == 'edit')
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);

		/**
		 * Меню, хлебные крошки
		 */
		$global['insert']['current'] = $lang['all_edit'];
		$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;re=order').'">'.$lang['checkout'].'</a>', $lang['all_edit']);

		/**
		 * Вывод на страницу, шапка
		 */
		$tm->header();

		// Шаблон, редактировать личные данные
		$template = $tm->create('mod/'.WORKMOD.'/personal.edit');

		$tm->parseprint(array
			(
				'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
				'user_data'    => $lang['user_data'],
				'not_empty'    => $lang['all_not_empty'],
				'firstname'    => $lang['firstname'],
				'firstnameval' => $item['firstname'],
				'surname'      => $lang['surname'],
				'surnameval'   => $item['surname'],
				'country'      => $lang['country'],
				'omit'         => $lang['omit'],
				'state'        => $lang['state'],
				'city'         => $lang['city'],
				'cityval'      => $item['city'],
				'zip'          => $lang['zip'],
				'zipval'       => $item['zip'],
				'adress'       => $lang['adress'],
				'adressval'    => $item['adress'],
				'phone'        => $lang['phone'],
				'phoneval'     => $item['phone'],
				'notice'       => $lang['order_notice'],
				'commentval'   => $item['comment'],
				'proceed'      => $lang['all_save'],
				'id'           => $item['oid']
			),
			$template);

		/**
		 * Вывод на страницу, подвал
		 */
		$tm->footer();
	}
}

/**
 * Метка save
 */
if ($to == 'save')
{
	$c = array
	(
		'firstname' => array('max' => 32, 'lang' => 'firstname'),
		'surname'   => array('max' => 32, 'lang' => 'surname'),
		'city'      => array('max' => 64, 'lang' => 'city'),
		'zip'       => array('max' => 10, 'lang' => 'zip'),
		'adress'    => array('max' => 255, 'lang' => 'adress'),
		'phone'     => array('max' => 32, 'lang' => 'phone')
	);

	$error = 0;
	$field = array();

	foreach ($c as $k => $v)
	{
		if (isset($data[$k]))
		{
			if (mb_strlen($data[$k]) < 2 OR mb_strlen($data[$k]) > $v['max'])
			{
				$error = 1;
				$field[$k] = (isset($lang[$v['lang']]) ? $lang[$v['lang']] : $v['lang']);
			}
		}
		else
		{
			$error = 1;
			$field[$k] = (isset($lang[$v['lang']]) ? $lang[$v['lang']] : $v['lang']);
		}
	}

	if ($error)
	{
		$tm->error($lang['bad_fields'].'<br />'.implode(', ',$field));
	}

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($id)."'");
	if ($db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);
		$db->query
			(
				"UPDATE ".$basepref."_".WORKMOD."_order SET
				 firstname	= '".$db->escape($data['firstname'])."',
				 surname	= '".$db->escape($data['surname'])."',
				 city		= '".$db->escape($data['city'])."',
				 zip		= '".$db->escape($data['zip'])."',
				 phone		= '".$db->escape($data['phone'])."',
				 adress	= '".$db->escape($data['adress'])."',
				 comment	= '".$db->escape($data['comment'])."'
				 WHERE userid = '".$db->escape($usermain['userid'])."' AND oid = '".$db->escape($item['oid'])."'"
			);

		redirect($ro->seo('index.php?dn='.WORKMOD.'&re=order&to=confirm&id='.$item['oid']));
	}
	else
	{
		$tm->error($lang['noisset_order']);
	}
}
