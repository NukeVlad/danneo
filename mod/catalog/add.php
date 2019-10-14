<?php
/**
 * File:        /mod/catalog/add.php
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
global $dn, $to, $db, $basepref, $config, $lang, $usermain, $tm, $ro, $global, $acc, $id, $ajax, $count, $recount, $opt, $opts;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * this int
 */
$id = preparse($id, THIS_INT);
$acc = preparse($acc, THIS_INT);
$count = preparse($count, THIS_INT);

/**
 * ajax
 */
$ajax = ($config['ajax'] == 'yes' AND preparse($ajax, THIS_INT) > 0) ? 1 : 0;

/**
 * Редирект, если покупки отключены
 */
if ($conf['buy'] == 'no')
{
	if ( ! $ajax )
		redirect($ro->seo('index.php?dn='.WORKMOD));
	else
		exit();
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
 * Метки
 */
$legaltodo = array('index', 'add');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Пересчёт
 */
if( isset($recount) )
{
   $to = 'index';
}

function createbasket($opt)
{
	global $db, $basepref, $conf, $config;

	$session = md5(REMOTE_ADDRS.uniqid(microtime()));
	$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE lifetime < '".intval(NEWTIME - $conf['cookieexp'])."'");

	$inq = $db->query
			(
				"INSERT INTO ".$basepref."_".WORKMOD."_basket VALUES (
				 NULL,
				 '".$db->escape($session)."',
				 '".NEWTIME."',
				 '".$db->escape($opt)."'
				 )"
			);

	if ($inq) {
		setcookie($conf['cookie'].'basket', $session, NEWTIME + $conf['cookieexp'], DNROOT);
	}

	return $session;
}

/**
 * Метка index
 */
if ($to == 'index')
{
	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['add_product'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['add_product']);

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$db->escape($id)."' AND act = 'yes'");

	if ($db->numrows($inq) > 0)
	{
		if ( ! $ajax ) {
			$tm->header();
		} else {
			header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
			header('Content-Type: text/html; charset='.$config['langcharset'].'');
		}

		$item = $db->fetchrow($inq);
		if ($count < $item['amountmin'])
		{
			//$count = $item['amountmin'];
		}
		if ($count > $item['amountmax'] AND $item['amountmax'] != 0)
		{
			$count = $item['amountmax'];
		}
		if ($item['amountmax'] == 1)
		{
			$count = 1;
		}

		$tm->unmanule['tax'] = 'no';

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

		// НДС
		$ins['tax'] = array('title' => '', 'tax' => 0);
		if ($item['tax'] > 0)
		{
			$in = Json::decode($conf['taxes']);
			if (is_array($in) AND isset($in[$item['tax']]))
			{
				$ins['tax'] = $in[$item['tax']];
				$tm->unmanule['tax'] = 'yes';
			}
		}

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/row'));

		$cat = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".WORKMOD."_cat WHERE catid = '".$db->escape($item['catid'])."'"));
		$ins['cat'] = Json::decode($cat['options']);

		$ins['option'] = '';
		$ins['price'] = $total = $count * $item['price'];

		if (is_array($ins['cat']) AND sizeof($ins['cat']) > 0)
		{
			if (is_array($config['option']) AND sizeof($config['option']) > 0)
			{
				$product = array();
				$pinq = $db->query("SELECT oid,vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id = '".$db->escape($item['id'])."'");
				while ($pitem = $db->fetchrow($pinq))
				{
					$product[$pitem['oid']][$pitem['vid']] = $pitem['vid'];
				}

				foreach ($config['option'] as $k => $v)
				{
					$ins['optionsel'] = $modvalue = $goods = '';
					$ins['optionprice'] = $cur['symbol_left'].formats($modvalue, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'];

					if (isset($product[$k]) AND isset($config['option'][$k]) AND $config['option'][$k]['buy'] == 1)
					{
						if (is_array($config['option'][$k]['value']) AND sizeof($config['option'][$k]['value']) > 0)
						{
							$ins['optionsel'] = '<select name="opts['.$k.']">';
							foreach ($config['option'][$k]['value'] as $ok => $ov)
							{
								if (isset($product[$k][$ok]))
								{
									$ins['optionsel'].= '<option value="'.$ok.'"'.((isset($opts[$k]) AND $ok == $opts[$k]) ? ' selected' : '').'> '.$ov['title'].' </option>';
									if (isset($opts[$k]) AND $ok == $opts[$k])
									{
										if ($ov['modvalue'] > 0)
										{
											if ($ov['modify'] == 'fix')
											{
												$total += $count * $ov['modvalue'];
												$modvalue = $ov['modvalue'];
											}
											else if($ov['modify'] == 'percent')
											{
												$c = (($count * $item['price']) / 100) * $ov['modvalue'];
												$total += $c;
											}
										}
										$goods += $modvalue;
										$ins['optionprice'] = $cur['symbol_left'].formats($modvalue,$cur['decimal'],$cur['decimalpoint'],$cur['thousandpoint'],$cur['value']).$cur['symbol_right'];
									}
								}
							}
							$ins['optionsel'].= '</select>';
						}
						$ins['option'] .= $tm->parse(array
											(
												'optiontitle' => $config['option'][$k]['title'],
												'optionsel' => $ins['optionsel'],
												'optionprice' => $ins['optionprice']
											),
											$tm->manuale['option']);
						$item['price'] += $goods;
					}
				}
			}
		}

		$tax = (($count * $item['price']) / 100) * $ins['tax']['tax'];
		$ins['count'] = '<input name="count" size="5" value="'.$count.'" type="text">';
		$total = ((($count * $item['price'] / 100) * $ins['tax']['tax']) + ($item['price'] * $count));

		$tm->parseprint(array
			(
				'post_url'   => $ro->seo('index.php?dn='.WORKMOD),
				'langproduct'=> $lang['product_name'],
				'langcol'    => $lang['all_col'],
				'langprice'  => $lang['price'],
				'title'      => $item['title'],
				'mods'       => WORKMOD,
				'price'      => $cur['symbol_left'].formats($ins['price'], $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
				'count'      => $ins['count'],
				'langtax'    => $lang['tax'],
				'titletax'   => $ins['tax']['title'],
				'pricetax'   => $cur['symbol_left'].formats($tax, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
				'langtotal'	 => $lang['in_total'],
				'pricetotal' => $cur['symbol_left'].formats($total, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
				'langbasket' => $lang['add_basket'],
				're_count'   => $lang['re_count'],
				'option'     => $ins['option'],
				'id'         => $item['id']
			),
			$ins['template']);

		if ( ! $ajax) {
			$tm->footer();
		} else {
			exit();
		}
	}
	else
	{
		/**
		 * Ошибка добавления товара
		 */
		if ($config['ajax'] == 'yes') {
			$tm->message($lang['noexit_goods'], 0, 0, 1);
		} else {
			$tm->error($lang['noexit_goods'], 0, 1);
		}
	}
}

/**
 * Метка add
 */
if ($to == 'add')
{
	$global['insert']['current'] = $lang['add_product'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['add_product']);

	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$db->escape($id)."' AND act = 'yes'");

	$obj = array();
	$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchrow($inqs, $config['cache']))
	{
		$obj[$c['catid']] = $c;
	}

	if ($db->numrows($inq) > 0)
	{
		if ( ! $ajax ) {
			$tm->header();
		} else {
			header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
			header('Content-Type: text/html; charset='.$config['langcharset'].'');
		}

		$item = $db->fetchrow($inq);
		$basket = (isset($_COOKIE[$conf['cookie'].'basket']) AND preg_match('/^[a-z0-9]+$/D',$_COOKIE[$conf['cookie'].'basket'])) ? $_COOKIE[$conf['cookie'].'basket'] : FALSE;

		if ($count < $item['amountmin'])
		{
			//$count = $item['amountmin'];
		}
		if ($count > $item['amountmax'] AND $item['amountmax'] != 0)
		{
			$count = $item['amountmax'];
		}
		if ($item['amountmax'] == 1)
		{
			$count = 1;
		}

		$cat = $db->fetchrow($db->query("SELECT options FROM ".$basepref."_".WORKMOD."_cat WHERE catid = '".$db->escape($item['catid'])."'"));
		$ins['cat'] = Json::decode($cat['options']);

		$p = $b = '';
		$opt = $opts;
		$opts = array();

		if (is_array($ins['cat']) AND sizeof($ins['cat']) > 0)
		{
			if (is_array($config['option']) AND sizeof($config['option']) > 0)
			{
				$product = array();
				$pinq = $db->query("SELECT oid,vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id = '".$db->escape($item['id'])."'");
				while ($pitem = $db->fetchrow($pinq))
				{
					$product[$pitem['oid']][$pitem['vid']] = $pitem['vid'];
				}

				foreach ($config['option'] as $k => $v)
				{
					if (isset($product[$k]) AND isset($config['option'][$k]) AND $config['option'][$k]['buy'] == 1)
					{
						if (is_array($config['option'][$k]['value']) AND sizeof($config['option'][$k]['value']) > 0)
						{
							foreach ($config['option'][$k]['value'] as $ok => $ov)
							{
								if (isset($product[$k][$ok]))
								{
									if (isset($opt[$k]) AND $ok == $opt[$k])
									{
										$opts[$k] = $ok;
									}
								}
							}
						}
					}
				}
			}
		}

		$p[$item['id']] = array('count' => $count, 'option' => $opts);
		if ($basket)
		{
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_basket WHERE session = '".$db->escape($basket)."'");
			if ($db->numrows($inq) > 0)
			{
				$baskets = $db->fetchrow($inq);
				$b = Json::decode($baskets['basket']);
				if (is_array($b) AND sizeof($b) > 0)
				{
					$b[$item['id']] = array('count' => $count, 'option' => $opts);
					$db->query
						(
							"UPDATE ".$basepref."_".WORKMOD."_basket SET
							 basket = '".$db->escape(Json::encode($b))."'
							 WHERE bid = '".$db->escape($baskets['bid'])."'"
						);
					$s = $baskets['session'];
				}
			}
			else
			{
				$s = createbasket(Json::encode($p));
			}
		}
		else
		{
			$s = createbasket(Json::encode($p));
		}

		if ($ajax)
		{
			header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
			header('Content-Type: text/html; charset='.$config['langcharset'].'');

			if ($conf['request'] == 'yes') {
				$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/basket.request.ajax'));
			} else {
				$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/basket.block.ajax'));
			}

			$ins['basket'] = $tm->parse(array(
											'empty' => $lang['basket_empty']
											),
											$tm->manuale['empty']);
			$bv = 0;
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_basket WHERE session = '".$db->escape($s)."'");

			if ($db->numrows($inq) == 1)
			{
				$bload = $db->fetchrow($inq);
				$ins['basket'] = '';

				if (is_array($bload) AND isset($bload['basket']))
				{
					$bnewload = Json::decode($bload['basket']);
					$ids = array_keys($bnewload);
					$in = implode(',', $ids);
					$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");

					if ($db->numrows($inq) > 0)
					{

						$bt = '';

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

							$bv = 1;
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

							$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
							$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

							$ins['info'] = $tm->parse(array
												(
													'id'      => $item['id'],
													'inforow' => $ins['inforow'],
												),
												$tm->manuale['info']);

							$ins['basket'] .= $tm->parse(array
												(
													'id'       => $item['id'],
													'title'    => $item['title'],
													'info'     => $ins['info'],
													'count'    => $pcount,
													'del'      => $lang['all_delet'],
													'detail'   => $lang['order_detail'],
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
					}
				}
			}

			if ($bv)
			{
				$ins['basket'] .= $tm->parse(array
									(
										'viewbasket'   => $lang['basket'],
										'checkout'     => $lang['checkout'],
										'linkpersonal' => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=basket&amp;to=personal'),
										'linkbasket'   => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=basket')
									),
									$tm->manuale['basketlink']);
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
											'linkorder'   => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=order'),
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
												'linkhistory'  => $ro->seo('index.php?dn='.WORKMOD.'&amp;re=history'),
												'oa' => $oa
											),
											$tm->manuale['baskethistory']);
					}
				}
			}

			echo $tm->parse(array
					(
						'currency'      => '',
						'basket'        => $ins['basket'],
						'mods'          => WORKMOD,
						'make_order'    => $lang['make_order'],
						'no_prepayment' => $lang['no_prepayment'],
						'make_notice'   => $lang['make_order_notice'],
						'send_request'  => $lang['send_request'],
						'will_contact'  => $lang['will_contact'],
					),
					$ins['template']);

			exit();
		}
		else
		{
			redirect($ro->seo('index.php?dn='.WORKMOD.'&re=basket'));
		}

		if ( ! $ajax) {
			$tm->footer();
		} else {
			exit();
		}
	}
	else
	{
		/**
		 * Ошибка добавления товара
		 */
		$tm->error($lang['noexit_goods'], 0, 1);
	}
}
