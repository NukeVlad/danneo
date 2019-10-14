<?php
/**
 * File:        /block/b-CatalogBasket.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $lang, $config, $ro, $dn;

$bc = null;
$lang['basket'] = isset($lang['basket']) ? $lang['basket'] : 'Catalog Basket';

/**
 * Настройки
 */
$bs = array
	(
		'blockname' => $lang['basket'],
		'mod' => array
			(
			'lang'    => 'block_mods',
			'form'    => 'text',
			'value'   => 'catalog',
			'default' => 'catalog'
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

	// Option for workmod
	$config['option'] = null;
	$cache_option = DNDIR.'cache/'.$bs['mod'].'.option.php';
	if (file_exists($cache_option))
	{
		include($cache_option);
		$config['option'] = $option;
	}

	/**
	 * Онлайн заявка
	 ----------------*/
	if ($config[$bs['mod']]['request'] == 'yes')
	{
		$ins = array
			(
				'currency' => '',
				'basket'   => '',
				'forms'    => ''
			);

		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/basket.request'));

		if (sizeof($config['arrcur']) > 1 AND isset($config['arrcur'][$config[$bs['mod']]['currency']]))
		{
			$currencyopt = null;
			foreach ($config['arrcur'] as $ck => $cv)
			{
				$currencyopt.= '<a href="#" onclick="$.currency(\''.$ck.'\');">'.$cv['title'].'</a>';
			}

			$ins['currency'] = $tm->parse(array
									(
										'currencytitle' => $config['arrcur'][$config['viewcur']]['title'],
										'currencyopt'   => $currencyopt,
										'help_change'   => $lang['all_change']
									),
									$tm->manuale['currency']);
		}

		$session = null;
		if (
			isset($_COOKIE[$config[$bs['mod']]['cookie'].'basket']) AND
			preg_match('/^[a-z0-9]+$/D', $_COOKIE[$config[$bs['mod']]['cookie'].'basket'])
		)
		{
			$session = $_COOKIE[$config[$bs['mod']]['cookie'].'basket'];
		}

		$ins['basket'] = $tm->parse(array
							(
								'empty' => $lang['basket_empty']
							),
							$tm->manuale['empty']);

		$bv = 0;
		if ($config[$bs['mod']]['buy'] == 'yes' AND ! empty($session))
		{
			$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_basket WHERE session = '".$db->escape($session)."'");

			$obj = array();
			$inqs = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_cat ORDER BY posit ASC", $config['cachetime'], $bs['mod']);
			while ($c = $db->fetchrow($inqs, $config['cache']))
			{
				$obj[$c['catid']] = $c;
			}

			if ($db->numrows($inq) == 1)
			{
				$bload = $db->fetchrow($inq);

				if (is_array($bload) AND isset($bload['basket']))
				{
					$bnewload = Json::decode($bload['basket']);

					$in = implode(',', array_keys($bnewload));
					$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']." WHERE id IN (".$db->escape($in).")");

					if ($db->numrows($inq) > 0)
					{
						$ins['forms'] = $ins['basket'] = $ins['inforow'] = null;

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
						$taxes = Json::decode($config[$bs['mod']]['taxes']);
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
						$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".$bs['mod']."_product_option WHERE id IN (".$db->escape($in).")");
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
													'opt'      => $lang['all_col'],
													'opttitle' => $pcount,
													'optprice' => formats(($pcount * $item['price']), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);
							// formcount
							$formcount = $pcount;

							$pgoods = null;
							if (isset($product[$item['id']]) AND sizeof($product[$item['id']]) > 0 AND isset($opt[$item['id']]) AND sizeof($opt[$item['id']]) > 0)
							{
								foreach ($opt[$item['id']] as $kp => $kv)
								{
									if (is_array($config['option']) AND sizeof($config['option']) > 0 AND isset($config['option'][$kp]['value'][$kv]) AND $config['option'][$kp]['buy'] == 1)
									{
										$ps = $config['option'][$kp];
										$modvalue = $ps['value'][$kv]['modvalue'];
										$ins['inforow'].= $tm->parse(array
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

							if (is_array($taxes) AND isset($taxes[$item['tax']])) {
								$ins['tax'] = $taxes[$item['tax']];
							} else {
								$ins['tax'] = array('title' => '', 'tax' => 0);
							}

							$bv = 1;
							$item['price'] += $pgoods;
							$tax = (($pcount * $item['price']) / 100) * $ins['tax']['tax'];

							if (is_array($taxes) AND isset($taxes[$item['tax']])) {
								$fulltotal += $ptotal += $tax;
							} else {
								$fulltotal += $ptotal;
							}

							$ins['inforow'].= $tm->parse(array
													(
														'opt'      => $lang['one_tax'],
														'opttitle' => $ins['tax']['title'],
														'optprice' => formats($tax, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
													),
													$tm->manuale['inforow']);

							$onetotal = ((($pcount * $item['price'] / 100) * $ins['tax']['tax']) + ($item['price'] * $pcount));

							$ins['inforow'].= $tm->parse(array
													(
														'opt'      => '',
														'opttitle' => '',
														'optprice' => formats($onetotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
													),
													$tm->manuale['inforow']);

							// formprice
							$formprice = formats($onetotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']);

							$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
							$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

							$ins['info'] = $tm->parse(array
												(
													'id'      => $item['id'],
													'inforow' => $ins['inforow'],
												),
												$tm->manuale['info']);

							$ins['basket'].= $tm->parse(array
												(
													'id'       => $item['id'],
													'title'    => $item['title'],
													'info'     => $ins['info'],
													'count'    => $pcount,
													'del'      => $lang['all_delet'],
													'detail'   => $lang['order_detail'],
													'linkgods' => $ro->seo('index.php?dn='.$bs['mod'].$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])
												),
												$tm->manuale['basket']);
							// formbasket
							$ins['forms'].= $tm->parse(array
												(
													'id'       => $item['id'],
													'title'    => $item['title'],
													'count'    => $formcount,
													'price'    => $formprice,
													'count'    => $pcount,
													'del'      => $lang['all_delet'],
													'detail'   => $lang['order_detail'],
													'linkgods' => $ro->seo('index.php?dn='.$bs['mod'].$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])
												),
												$tm->manuale['formbasket']);
						}

						$ins['basket'].= $tm->parse(array
												(
													'langtotal' => $lang['in_total'],
													'fulltotal' => $cur['symbol_left'].formats($fulltotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right']
												),
												$tm->manuale['total']);
						// formtotal
						$ins['forms'] .= $tm->parse(array
												(
													'langtotal' => $lang['in_total'],
													'fulltotal' => formats($fulltotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['formtotal']);
					}
				}
			}
		}

		if ($bv)
		{
			$ins['basket'].= $tm->parse(array(), $tm->manuale['basketlink']);
		}

		if ($config[$bs['mod']]['buy'] == 'no')
		{
			$ins['basket'] = null;
		}

		return $tm->parse(array
				(
					'currency'      => $ins['currency'],
					'basket'        => $ins['basket'],
					'forms'         => $ins['forms'],
					'mods'          => $bs['mod'],
					'make_order'    => $lang['make_order'],
					'no_prepayment' => $lang['no_prepayment'],
					'make_notice'   => $lang['make_order_notice'],
					'send_request'  => $lang['send_request'],
					'will_contact'  => $lang['will_contact'],
					'sending'       => $lang['sending'],
					'your_name'     => $lang['email_name'],
					'your_phone'    => $lang['mail_phone'],
					'to_request'    => $lang['data_to_request'],
					'send'          => $lang['email_send'],
				),
				$ins['template']);

	/**
	 * Магазин
	 -----------*/
	}
	else
	{
		$ins = array
			(
				'currency' => '',
				'basket'   => ''
			);

		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/basket.block'));

		if (sizeof($config['arrcur']) > 1 AND isset($config['arrcur'][$config[$bs['mod']]['currency']]))
		{
			$currencyopt = null;
			foreach ($config['arrcur'] as $ck => $cv)
			{
				$currencyopt.= '<a href="#" onclick="$.currency(\''.$ck.'\');">'.$cv['title'].'</a>';
			}

			$ins['currency'] = $tm->parse(array
									(
										'currencytitle' => $config['arrcur'][$config['viewcur']]['title'],
										'currencyopt'   => $currencyopt,
										'help_change'   => $lang['all_change']
									),
									$tm->manuale['currency']);
		}

		$session = null;
		if (
			isset($_COOKIE[$config[$bs['mod']]['cookie'].'basket']) AND
			preg_match('/^[a-z0-9]+$/D', $_COOKIE[$config[$bs['mod']]['cookie'].'basket'])
		)
		{
			$session = $_COOKIE[$config[$bs['mod']]['cookie'].'basket'];
		}

		$ins['basket'] = $tm->parse(array
							(
								'empty' => $lang['basket_empty']
							),
							$tm->manuale['empty']);

		$bv = 0;
		if ($config[$bs['mod']]['buy'] == 'yes' AND ! empty($session))
		{
			$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_basket WHERE session = '".$db->escape($session)."'");

			$obj = array();
			$inqs = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_cat ORDER BY posit ASC", $config['cachetime'], $bs['mod']);
			while ($c = $db->fetchrow($inqs, $config['cache']))
			{
				$obj[$c['catid']] = $c;
			}

			if ($db->numrows($inq) == 1)
			{
				$bload = $db->fetchrow($inq);

				if (is_array($bload) AND isset($bload['basket']))
				{
					$bnewload = Json::decode($bload['basket']);

					$in = implode(',', array_keys($bnewload));
					$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']." WHERE id IN (".$db->escape($in).")");

					if ($db->numrows($inq) > 0)
					{
						$ins['basket'] = $ins['inforow'] = null;

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

						$taxes = Json::decode($config[$bs['mod']]['taxes']);
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
						$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".$bs['mod']."_product_option WHERE id IN (".$db->escape($in).")");
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
													'opt'      => $lang['all_col'],
													'opttitle' => $pcount,
													'optprice' => formats(($pcount * $item['price']), $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);

							$pgoods = null;
							if (isset($product[$item['id']]) AND sizeof($product[$item['id']]) > 0 AND isset($opt[$item['id']]) AND sizeof($opt[$item['id']]) > 0)
							{
								foreach ($opt[$item['id']] as $kp => $kv)
								{
									if (is_array($config['option']) AND sizeof($config['option']) > 0 AND isset($config['option'][$kp]['value'][$kv]) AND $config['option'][$kp]['buy'] == 1)
									{
										$ps = $config['option'][$kp];
										$modvalue = $ps['value'][$kv]['modvalue'];

										$ins['inforow'].= $tm->parse(array
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

							if (is_array($taxes) AND isset($taxes[$item['tax']])) {
								$ins['tax'] = $taxes[$item['tax']];
							} else {
								$ins['tax'] = array('title' => '', 'tax' => 0);
							}

							$bv = 1;
							$item['price'] += $pgoods;
							$tax = (($pcount * $item['price']) / 100) * $ins['tax']['tax'];

							if (is_array($taxes) AND isset($taxes[$item['tax']])) {
								$fulltotal += $ptotal += $tax;
							} else {
								$fulltotal += $ptotal;
							}

							$ins['inforow'].= $tm->parse(array
												(
													'opt'      => $lang['one_tax'],
													'opttitle' => $ins['tax']['title'],
													'optprice' => formats($tax, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);

							$onetotal = ((($pcount * $item['price'] / 100) * $ins['tax']['tax']) + ($item['price'] * $pcount));

							$ins['inforow'].= $tm->parse(array
												(
													'opt'      => '',
													'opttitle' => '',
													'optprice' => formats($onetotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value'])
												),
												$tm->manuale['inforow']);

							$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
							$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

							$ins['info'] = $tm->parse(array
												(
													'id'		=> $item['id'],
													'inforow'	=> $ins['inforow'],
												),
												$tm->manuale['info']);

							$ins['basket'].= $tm->parse(array
												(
													'id'       => $item['id'],
													'title'    => $item['title'],
													'info'     => $ins['info'],
													'count'    => $pcount,
													'del'      => $lang['all_delet'],
													'detail'   => $lang['order_detail'],
													'linkgods' => $ro->seo('index.php?dn='.$bs['mod'].$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])
												),
												$tm->manuale['basket']);
						}

						$ins['basket'].= $tm->parse(array
											(
												'langtotal' => $lang['in_total'],
												'fulltotal' => $cur['symbol_left'].formats($fulltotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right']
											),
											$tm->manuale['total']);
					}
				}
			}
		}

		if ($bv)
		{
			$ins['basket'].= $tm->parse(array
								(
									'viewbasket'   => $lang['basket'],
									'checkout'     => $lang['checkout'],
									'linkpersonal' => $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=basket&amp;to=personal'),
									'linkbasket'   => $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=basket')
								),
								$tm->manuale['basketlink']);
		}

		// user
		if ($usermain['userid'] > 0 AND $config[$bs['mod']]['buy'] == 'yes')
		{
			$ks = implode(',', array($config[$bs['mod']]['statuspersonal'], $config[$bs['mod']]['statusdelive'], $config[$bs['mod']]['statuscheckout']));
			$inq = $db->query("SELECT oid FROM ".$basepref."_".$bs['mod']."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
			$oa = $db->numrows($inq);

			if ($oa > 0)
			{
				$ins['basket'].= $tm->parse(array
									(
										'orderactive' => $lang['order_active'],
										'linkorder'   => $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=order'),
										'oa'          => $oa
									),
									$tm->manuale['basketactive']);
			}

			if ($config[$bs['mod']]['history'] == 'yes')
			{
				$ks = implode(',', array($config[$bs['mod']]['statusok']));
				$inq = $db->query("SELECT oid FROM ".$basepref."_".$bs['mod']."_order WHERE statusid IN (".$db->escape($ks).") AND userid = '".$db->escape($usermain['userid'])."'");
				$oa = $db->numrows($inq);
				if ($oa > 0)
				{
					$ins['basket'].= $tm->parse(array
										(
											'orderhistory' => $lang['order_history'],
											'linkhistory'  => $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=history'),
											'oa'           => $oa
										),
										$tm->manuale['baskethistory']);
				}
			}
		}

		if ($config[$bs['mod']]['buy'] == 'no')
		{
			$ins['basket'] = null;
		}

		return $tm->parse(array
				(
					'currency' => $ins['currency'],
					'basket'   => $ins['basket'],
					'mods'     => $bs['mod'],
				),
				$ins['template']);
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
