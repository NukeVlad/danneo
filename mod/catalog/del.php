<?php
/**
 * File:        /mod/catalog/del.php
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
global $dn, $to, $db, $basepref, $config, $lang, $usermain, $tm, $ro, $global, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * id
 */
$id = preparse($id, THIS_INT);

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

$basket = (isset($_COOKIE[$conf['cookie'].'basket']) AND preg_match('/^[a-z0-9]+$/D', $_COOKIE[$conf['cookie'].'basket'])) ? $_COOKIE[$conf['cookie'].'basket'] : FALSE;
if ($basket)
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_basket WHERE session = '".$db->escape($basket)."'");

	$obj = array();
	$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchrow($inqs, $config['cache']))
	{
		$obj[$c['catid']] = $c;
	}

	if ($db->numrows($inq) == 1)
	{
		$bload = $db->fetchrow($inq);
		$b = Json::decode($bload['basket']);

		header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
		header('Content-Type: text/html; charset='.$config['langcharset'].'');

		if ($conf['request'] == 'yes') {
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/basket.request.ajax'));
		} else {
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/basket.block.ajax'));
		}

		unset($b[$id]);

		if (sizeof($b) > 0)
		{
			$load = Json::encode($b);
			$db->query("UPDATE ".$basepref."_".WORKMOD."_basket SET basket = '".$db->escape($load)."' WHERE bid = '".$db->escape($bload['bid'])."'");

			if (is_array($bload) AND isset($bload['basket']))
			{
				$ins['basket'] = '';
				$bnewload = Json::decode($load);
				$ids = array_keys($bnewload);
				$in = implode(',', $ids);
				$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");

				if ($db->numrows($inq) > 0)
				{
					if (isset($config['arrcur'][$config['viewcur']])) {
						$cur = $config['arrcur'][$config['viewcur']];
					} else {
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

					$taxes = Json::decode($conf['taxes']);
					$ptotal = $fulltotal = 0;
					$opt = array();
					foreach ($bnewload as $k => $v)
					{
						if (isset($v['option']) AND is_array($v['option']))
						{
							$opt[$k] = $v['option'];
						}
					}

					$product = array();
					$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id IN (".$db->escape($in).")");
					while ($pitem = $db->fetchrow($pinq))
					{
						$product[$pitem['id']][$pitem['oid']][$pitem['vid']] = $pitem['vid'];
					}

					while ($item = $db->fetchrow($inq))
					{
						$pcount = $bnewload[$item['id']]['count'];
						$ptotal = $pcount * $item['price'];
						$ins['inforow'] = $tm->parse(array
											(
												'opt' => $lang['all_col'],
												'opttitle' => $pcount,
												'optprice' => formats(($pcount * $item['price']), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
											),
											$tm->manuale['inforow']);

						$pgoods = '';
						if (isset($product[$item['id']]) AND sizeof($product[$item['id']]) > 0 AND isset($opt[$item['id']]) AND sizeof($opt[$item['id']]) > 0)
						{
							foreach ($opt[$item['id']] as $kp => $kv)
							{
								if (is_array($config['option']) AND sizeof($config['option']) > 0 AND isset($config['option'][$kp]['value'][$kv]) AND $config['option'][$kp]['buy'] == 1)
								{
									$ps = $config['option'][$kp];
									$modvalue = $ps['value'][$kv]['modvalue'];
									$ins['inforow'] .= $tm->parse(array
															(
																'opt' => $ps['title'],
																'opttitle' => $ps['value'][$kv]['title'],
																'optprice' => formats(($pcount * $modvalue), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
															),
															$tm->manuale['inforow']);

									if (isset($modvalue) AND $modvalue > 0)
									{
										if ($ps['value'][$kv]['modify'] == 'fix')
										{
											$ptotal += $pcount * $modvalue;
										}
										else if ($ps['value'][$kv]['modify'] == 'percent')
										{
											$c = (($pcount * $item['price']) / 100) * $modvalue;
											$ptotal += $c;
										}
										$pgoods += $modvalue;
									}
								}
							}
						}

						if (is_array($taxes) AND isset($taxes[$item['tax']]))
						{
							$ins['tax'] = $taxes[$item['tax']];
						}
						else
						{
							$ins['tax'] = array('title' => '', 'tax' => 0);
						}

						$item['price'] += $pgoods;
						$tax = (($pcount * $item['price']) / 100) * $ins['tax']['tax'];

						if (is_array($taxes) AND isset($taxes[$item['tax']])) {
							$fulltotal += $ptotal += $tax;
						} else {
							$fulltotal += $ptotal;
						}

						$ins['inforow'] .= $tm->parse(array
												(
													'opt' => $lang['one_tax'],
													'opttitle' => $ins['tax']['title'],
													'optprice' => formats($tax, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);

						$onetotal = ((($pcount * $item['price'] / 100) * $ins['tax']['tax']) + ($item['price'] * $pcount));

						$ins['inforow'] .= $tm->parse(array
												(
													'opt' => '',
													'opttitle' => '',
													'optprice' => formats($onetotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);

						$ins['info'] = $tm->parse(array
											(
												'id'      => $item['id'],
												'inforow' => $ins['inforow'],
											),
											$tm->manuale['info']);

						$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
						$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

						$ins['basket'] .= $tm->parse(array
											(
												'id'     => $item['id'],
												'title'  => $item['title'],
												'info'   => $ins['info'],
												'count'  => $pcount,
												'del'    => $lang['all_delet'],
												'detail' => $lang['order_detail'],
												'linkgods' => $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])
											),
											$tm->manuale['basket']);
					}

					$ins['basket'] .= $tm->parse(array
										(
											'langtotal' => $lang['in_total'],
											'fulltotal' => $cur['symbol_left'].formats($fulltotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right']
										),
										$tm->manuale['total']);

					$ins['basket'] .= $tm->parse(array
										(
											'viewbasket' => $lang['basket'],
											'checkout' => $lang['checkout'],
											'linkpersonal' => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=basket&amp;to=personal'),
											'linkbasket' => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=basket')
										),
										$tm->manuale['basketlink']);
				}
			}

			// user
			if ($usermain['userid'] > 0)
			{
				$ks = implode(',', array($conf['statuspersonal'], $conf['statusdelive'], $conf['statuscheckout']));
				$inq = $db->query("SELECT oid FROM ".$basepref."_".WORKMOD."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
				$oa = $db->numrows($inq);

				if ($oa > 0)
				{
					$ins['basket'] .= $tm->parse(array
										(
											'orderactive' => $lang['order_active'],
											'linkorder' => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order'),
											'oa' => $oa
										),
										$tm->manuale['basketactive']);
				}

				if ($conf['history'] == 'yes')
				{
					$ks = implode(',', array($conf['statusok']));
					$inq = $db->query("SELECT oid FROM ".$basepref."_".WORKMOD."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
					$oa = $db->numrows($inq);

					if ($oa > 0)
					{
						$ins['basket'] .= $tm->parse(array
											(
												'orderhistory' => $lang['order_history'],
												'linkhistory' => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=history'),
												'oa' => $oa
											),
											$tm->manuale['baskethistory']);
					}
				}
			}

			echo $tm->parse(array
					(
						'currency'      => '',
						'mods'          => WORKMOD,
						'basket'        => $ins['basket'],
						'make_order'    => $lang['make_order'],
						'no_prepayment' => $lang['no_prepayment'],
						'make_notice'   => $lang['make_order_notice'],
						'send_request'  => $lang['send_request'],
						'will_contact'  => $lang['will_contact'],
					),
					$ins['template']);
		}
		else
		{
			$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE session = '".$db->escape($basket)."'");

			setcookie($conf['cookie'].'basket', $db->escape($basket), NEWTIME - $conf['cookieexp'], DNROOT);

			$ins['basket'] = $tm->parse(array('empty' => $lang['basket_empty']), $tm->manuale['empty']);

			// user
			if ($usermain['userid'] > 0)
			{
				$ks = implode(',', array($conf['statuspersonal'], $conf['statusdelive'], $conf['statuscheckout']));
				$inq = $db->query("SELECT oid FROM ".$basepref."_".WORKMOD."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
				$oa = $db->numrows($inq);

				if ($oa > 0)
				{
					$ins['basket'] .= $tm->parse(array
										(
											'orderactive' => $lang['order_active'],
											'oa' => $oa
										),
										$tm->manuale['basketactive']);
				}

				if ($conf['history'] == 'yes')
				{
					$ks = implode(',', array($conf['statusok']));
					$inq = $db->query("SELECT oid FROM ".$basepref."_".WORKMOD."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
					$oa = $db->numrows($inq);

					if ($oa > 0)
					{
						$ins['basket'] .= $tm->parse(array
											(
												'orderhistory' => $lang['order_history'],
												'oa' => $oa
											),
											$tm->manuale['baskethistory']);
					}
				}
			}

			echo $tm->parse(array
					(
						'currency'      => '',
						'mods'          => WORKMOD,
						'basket'        => $ins['basket'],
						'make_order'    => $lang['make_order'],
						'no_prepayment' => $lang['no_prepayment'],
						'make_notice'   => $lang['make_order_notice'],
						'send_request'  => $lang['send_request'],
						'will_contact'  => $lang['will_contact'],
					),
					$ins['template']);
			exit();
		}
	}
}
