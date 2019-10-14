<?php
/**
 * File:        /mod/catalog/basket.php
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
global $db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $to, $clear, $count, $id, $payid, $delid, $recount, $data;

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
 * payid
 */
$payid = preparse($payid, THIS_INT);

/**
 * delid
 */
$delid = preparse($delid, THIS_INT);

/**
 * delete
 */
$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE lifetime < '".intval(NEWTIME - $conf['cookieexp'])."'");

/**
 * basket
 */
$basket = (isset($_COOKIE[$conf['cookie'].'basket']) AND preg_match('/^[a-z0-9]{32}+$/D', $_COOKIE[$conf['cookie'].'basket'])) ? $_COOKIE[$conf['cookie'].'basket'] : FALSE;

/**
 * empty
 */
$empty = $loadbasket = 1;

/**
 * empty
 */
$load = array();

/**
 * check
 */
if ($basket)
{
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_basket WHERE session = '".$db->escape($basket)."'");
	if ($db->numrows($inq) == 1)
	{
		$load = $db->fetchrow($inq);
		$loadbasket = (isset($load['basket']) AND ! empty($load['basket'])) ? 0 : 1;
		$empty = 0;

		if (is_array($count) AND sizeof($count) > 0)
		{
			$countload = Json::decode($load['basket']);
			foreach ($count as $k => $v)
			{
				if (isset($countload[$k]['count']))
				{
					$countload[$k]['count'] = (intval($v) > 0) ? intval($v) : 1;
				}
			}

			$load['basket'] = Json::encode($countload);
			$db->query("UPDATE ".$basepref."_".WORKMOD."_basket SET basket = '".$db->escape($load['basket'])."' WHERE bid = '".$db->escape($load['bid'])."'");
		}

		if (is_array($clear) AND sizeof($clear) > 0)
		{
			$clearload = Json::decode($load['basket']);
			foreach ($clear as $k => $v)
			{
				unset($clearload[$k]);
			}

			if (is_array($clearload) AND sizeof($clearload) > 0)
			{
				$load['basket'] = Json::encode($clearload);
				$db->query("UPDATE ".$basepref."_".WORKMOD."_basket SET basket = '".$db->escape($load['basket'])."' WHERE bid = '".$db->escape($load['bid'])."'");
			}
			else
			{
				$empty = 1;
				$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE bid = '".$db->escape($load['bid'])."'");
				setcookie($conf['cookie'].'basket', $load['session'], NEWTIME - $conf['cookieexp'], DNROOT);
			}
		}
	}
}

/**
 * function check empty
 */
function check_empty($empty = TRUE, $loadbasket = TRUE)
{
	global $config, $lang, $api, $ro, $tm, $global;

	if ($empty OR $loadbasket)
	{
		$global['insert']['current'] = $lang['basket'];
		$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['basket']);

		$tm->header();
		$tm->parseprint(array
			(
				'text' => $lang['basket_empty']
			),
			$tm->create('mod/'.WORKMOD.'/basket.empty'));
		$tm->footer();
	}
}

/**
 * Метки
 */
$legaltodo = array('index', 'personal', 'add');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * пересчёт
 */
if(isset($recount))
{
   $to = 'index';
}

/**
 * Метка index
 */
if ($to == 'index')
{
	/**
	 * empty
	 */
	check_empty($empty, $loadbasket);

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['basket'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['basket']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	if (is_array($load) AND isset($load['basket']))
	{
		$newload = Json::decode($load['basket']);
		$id = array_keys($newload);
		$in = implode(',', $id);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");

		$obj = array();
		$inqs = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
		while ($c = $db->fetchrow($inqs, $config['cache']))
		{
			$obj[$c['catid']] = $c;
		}

		if ($db->numrows($inq) > 0)
		{
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
			$total = $pricetotal = 0;
			$opt = array();
			foreach ($newload as $k => $v)
			{
				if (isset($v['option']) AND is_array($v['option']))
				{
					$opt[$k] = $v['option'];
				}
			}

			// Шаблон, корзина
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/basket.full'));

			$ins['rows'] = '';
			$product = array();
			$pinq = $db->query("SELECT oid, id, vid FROM ".$basepref."_".WORKMOD."_product_option WHERE id IN (".$db->escape($in).")");
			while ($pitem = $db->fetchrow($pinq))
			{
				$product[$pitem['id']][$pitem['oid']][$pitem['vid']] = $pitem['vid'];
			}

			while ($item = $db->fetchrow($inq))
			{
				if ($newload[$item['id']]['count'] < $item['amountmin'])
				{
					//$newload[$item['id']]['count'] = $item['amountmin'];
				}
				if ($newload[$item['id']]['count'] > $item['amountmax'] AND $item['amountmax'] != 0)
				{
					$newload[$item['id']]['count'] = $item['amountmax'];
				}
				$total = $newload[$item['id']]['count'] * $item['price'];

				$ins['opt'] = $ins['optnon'] = $goods = '';
				if (isset($product[$item['id']]) AND sizeof($product[$item['id']]) > 0 AND isset($opt[$item['id']]) AND sizeof($opt[$item['id']]) > 0)
				{
					foreach ($opt[$item['id']] as $kp => $kv)
					{
						if (is_array($config['option']) AND sizeof($config['option']) > 0 AND isset($config['option'][$kp]['value'][$kv]))
						{
							$p = $config['option'][$kp];
							$ins['opt'] .= $tm->parse(array
												(
													'titleopt' => $p['title'],
													'valueopt' => $p['value'][$kv]['title']
												),
												$tm->manuale['opt']);

							if (isset($p['value'][$kv]['modvalue']) AND $p['value'][$kv]['modvalue'] > 0)
							{
								if ($p['value'][$kv]['modify'] == 'fix')
								{
									$total += $newload[$item['id']]['count'] * $p['value'][$kv]['modvalue'];
								}
								else if ($p['value'][$kv]['modify'] == 'percent')
								{
									$c = (($newload[$item['id']]['count'] * $item['price']) / 100) * $p['value'][$kv]['modvalue'];
									$total += $c;
								}
								$goods += $p['value'][$kv]['modvalue'];
							}
						}
					}
				}
				else
				{
					$ins['optnon'] = $tm->parse(array(), $tm->manuale['optnon']);
				}

				if (is_array($taxes) AND isset($taxes[$item['tax']]))
				{
					$ins['tax'] = $taxes[$item['tax']];
				}
				else
				{
					$ins['tax'] = array('title' => '', 'tax' => 0);
				}

				$item['price'] += $goods;
				$tax = (($newload[$item['id']]['count'] * $item['price']) / 100) * $ins['tax']['tax'];

				if (is_array($taxes) AND isset($taxes[$item['tax']])) {
					$pricetotal += $total += $tax;
				} else {
					$pricetotal += $total;
				}

				$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
				$ins['ccpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

				$ins['rows'] .= $tm->parse(array
									(
										'id'         => $item['id'],
										'title'      => $item['title'],
										'opt'        => $ins['opt'],
										'non'        => $ins['optnon'],
										'tax'        => $ins['tax']['title'],
										'count'      => $newload[$item['id']]['count'],
										'price'      => $cur['symbol_left'].formats($total, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right'],
										'langdel'    => $lang['del_box'],
										'langproduct'=> $lang['all_name'],
										'langcol'    => $lang['all_col'],
										'langtax'    => $lang['one_tax'],
										'langprice'	 => $lang['price'],
										'langdetail' => $lang['order_detail'],
										'linkgods'   => $ro->seo('index.php?dn='.WORKMOD.$ins['ccpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'])
									),
									$tm->manuale['rows']);
			}
			$tm->parseprint(array
				(
					'post_url'    => $ro->seo('index.php?dn='.WORKMOD),
					'mods'        => WORKMOD,
					'langdel'     => $lang['del_box'],
					'langproduct' => $lang['all_name'],
					'langcol'     => $lang['all_col'],
					'langtax'     => $lang['one_tax'],
					'langprice'   => $lang['price'],
					'langdetail'  => $lang['order_detail'],
					'langtotal'   => $lang['in_total'],
					'checkout'    => $lang['checkout'],
					're_count'    => $lang['re_count'],
					'rows'        => $ins['rows'],
					'pricetotal'  => $cur['symbol_left'].formats($pricetotal, $cur['decimal'], $cur['decimalpoint'], $cur['thousandpoint'], $cur['value']).$cur['symbol_right']
				),
				$ins['template']);
		}
		else
		{
			$tm->parseprint(array('text' => $lang['basket_empty']), $tm->create('mod/'.WORKMOD.'/basket.empty'));
		}
	}
	else
	{
		$tm->parseprint(array('text' => $lang['basket_empty']), $tm->create('mod/'.WORKMOD.'/basket.empty'));
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Форма авторизации
 */
if ( ! defined('USER_LOGGED'))
{
	define('REDIRECT', $ro->seo('index.php?dn='.WORKMOD.'&amp;re=basket&amp;to=personal'));
	$global['insert']['current'] = $lang['checkout'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['checkout']);
	$tm->noaccessprint();
}

/**
 * Метка personal
 */
if ($to == 'personal')
{
	/**
	 * empty
	 */
	check_empty($empty, $loadbasket);

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['user_data'];
    $global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['checkout'], $lang['user_data']);

	/**
	 * Вывод на страницу, шапка
	 */
    $tm->header();

	// Шаблон
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/personal'));

	// Регионы
	$country = null;
	$cache_country = DNDIR.'cache/cache.country.php';
	if (file_exists($cache_country))
	{
		$country = include($cache_country);
	}

	$countrysel = $statesel = null;

	if ( ! is_array($country))
	{
		$country = array();

		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($item = $db->fetchrow($inq))
		{
			$country[$item['countryid']] = $item;
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($item = $db->fetchrow($inq))
		{
			$country[$item['countryid']]['region'][$item['regionid']] = $item['regionname'];
		}
	}

	$c = $r = 0;
	$inq = $db->query("SELECT * FROM ".$basepref."_user WHERE userid = '".$usermain['userid']."'");
	if ($db->numrows($inq) == 1)
	{
		$item = $db->fetchrow($inq);
		$c = $item['countryid'];
		$r = $item['regionid'];
	}

	foreach ($country as $k => $v)
	{
		$countrysel.= '<option value="'.$k.'"'.(($k == $c) ? ' selected' : '').'>'.$v['countryname'].'</option>';
		if ($k == $c AND is_array($v['region']) AND sizeof($v['region']) > 0)
		{
			foreach ($v['region'] as $sk => $sv)
			{
				$statesel.= '<option value="'.$sk.'"'.(($sk == $r) ? ' selected' : '').'>'.$sv.'</option>';
			}
		}
	}

	// форма
	$tm->parseprint(array
		(
			'post_url'   => $ro->seo('index.php?dn='.WORKMOD),
			'mods'	     => WORKMOD,
			'user_data'	 => $lang['user_data'],
			'not_empty'	 => $lang['all_not_empty'],
			'firstname'	 => $lang['firstname'],
			'surname'    => $lang['surname'],
			'country'    => $lang['country'],
			'omit'       => $lang['omit'],
			'countrysel' => $countrysel,
			'state'      => $lang['state'],
			'statesel'   => $statesel,
			'city'       => $lang['city'],
			'zip'        => $lang['zip'],
			'adress'     => $lang['adress'],
			'phone'      => $lang['phone'],
			'notice'     => $lang['order_notice'],
			'proceed'    => $lang['proceed']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка add
 */
if ($to == 'add')
{
	/**
	 * empty
	 */
	check_empty($empty, $loadbasket);

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

	$cid = isset($data['cid']) ? intval($data['cid']) : 0;
	$sid = isset($data['sid']) ? intval($data['sid']) : 0;

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
		while ($item = $db->fetchrow($inq))
		{
			$country[$item['countryid']] = $item;
			$country[$item['countryid']]['region'] = array();
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($item = $db->fetchrow($inq))
		{
			$country[$item['countryid']]['region'][$item['regionid']] = $item['regionname'];
		}
	}

	if (isset($country[$cid]))
	{
		if (!isset($country[$cid]['region'][$sid]))
		{
			$error = 1;
			$field['state'] = $lang['state'];
		}
		$sid = isset($country[$cid]['region'][$sid]) ? $sid : 0;
	}
	else
	{
		$error = 1;
		$field['country'] = $lang['country'];
	}

	if ($error)
	{
		$field_error = '<ol>';
		foreach ($field as $k => $v) {
			$field_error .= '<li>'.$v.'</li>';
		}
		$field_error .= '</ol>';
		$tm->error($lang['bad_fields'].$field_error);
	}

	if (is_array($load) AND isset($load['basket']))
	{
		$newload = Json::decode($load['basket']);
		$ids = array_keys($newload);
		$in = implode(',', $ids);

		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id IN (".$db->escape($in).")");

		if ($db->numrows($inq) > 0)
		{

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
			$total = $pricetotal = 0;

			$opt = array();
			foreach ($newload as $k => $v)
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
				if ($newload[$item['id']]['count'] < $item['amountmin'])
				{
					$newload[$item['id']]['count'] = $item['amountmin'];
				}
				if ($newload[$item['id']]['count'] > $item['amountmax'] AND $item['amountmax'] != 0)
				{
					$newload[$item['id']]['count'] = $item['amountmax'];
				}
				$total = $newload[$item['id']]['count'] * $item['price'];

				if (isset($product[$item['id']]) AND sizeof($product[$item['id']]) > 0 AND isset($opt[$item['id']]) AND sizeof($opt[$item['id']]) > 0)
				{
					foreach ($opt[$item['id']] as $kp => $kv)
					{
						if (is_array($config['option']) AND sizeof($config['option']) > 0 AND isset($config['option'][$kp]['value'][$kv]))
						{
							$p = $config['option'][$kp];
							if (isset($p['value'][$kv]['modvalue']) AND $p['value'][$kv]['modvalue'] > 0)
							{
								if ($p['value'][$kv]['modify'] == 'fix')
								{
									$total += $newload[$item['id']]['count'] * $p['value'][$kv]['modvalue'];
								}
								else if($p['value'][$kv]['modify'] == 'percent')
								{
									$c = (($newload[$item['id']]['count'] * $item['price']) / 100) * $p['value'][$kv]['modvalue'];
									$total += $c;
								}
							}
						}
					}
				}
				if (is_array($taxes) AND isset($taxes[$item['tax']])) {
					$ins['tax'] = $taxes[$item['tax']];
				} else {
					$ins['tax'] = array('title' => '', 'tax' => 0);
				}
				$tax = (($newload[$item['id']]['count'] * $item['price']) / 100) * $ins['tax']['tax'];
				$pricetotal += $total += $tax;
			}
		}
	}

	if ($pricetotal > 0)
	{
		$inq = $db->query
				(
					"INSERT INTO ".$basepref."_".WORKMOD."_order VALUES (
					 NULL,
					 '".$db->escape($usermain['userid'])."',
					 '".$db->escape($pricetotal)."',
					 '0.0000',
					 '0',
					 '0',
					 '".$db->escape($conf['statuspersonal'])."',
					 '".$db->escape($cid)."',
					 '".$db->escape($sid)."',
					 '".NEWTIME."',
					 '".$db->escape($data['firstname'])."',
					 '".$db->escape($data['surname'])."',
					 '".$db->escape($data['city'])."',
					 '".$db->escape($data['zip'])."',
					 '".$db->escape($data['phone'])."',
					 '".$db->escape($data['adress'])."',
					 '".$db->escape($data['comment'])."',
					 '".$db->escape($load['basket'])."',
					 ''
					 )"
				);

		$id = $db->insertid();

		if ($inq AND $id)
		{
			$db->query("DELETE FROM ".$basepref."_".WORKMOD."_basket WHERE bid = '".$db->escape($load['bid'])."'");
			setcookie($conf['cookie'].'basket', $load['session'], NEWTIME - $conf['cookieexp'], DNROOT);
			redirect($ro->seo('index.php?dn='.WORKMOD.'&amp;re=order&amp;to=delive&amp;id='.$id));
		}
		else
		{
			$tm->error($lang['error_order']);
		}
	}
	else
	{
		$tm->error($lang['bad_price']);
	}
}
