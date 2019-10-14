<?php
/**
 * File:        /mod/catalog/gateway.php
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
global $dn, $to, $db, $basepref, $config, $lang, $usermain, $tm, $global, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * id
 */
$id = preparse($id, THIS_INT);

/**
 * $config['buy']
 */
if ($conf['buy'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_payment WHERE payact = '1' ORDER BY payposit");
$payment = array();
while ($items = $db->fetchrow($inq))
{
	$payment[$items['payid']] = $items;
}

if (sizeof($payment) > 0 AND isset($payment[$id]))
{
	$p = $payment[$id];
	include(DNDIR.'core/shop/payment/'.$p['payext'].'.php');
	$r = new payment;
	$oid = $r->findid();

	if ($oid > 0)
	{
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_order WHERE oid = '".$db->escape($oid)."' AND payid = '".$db->escape($id)."'");
		if ($db->numrows($inq) > 0)
		{
			$item = $db->fetchrow($inq);
			$r->check($p, $item);
		}
	}
}
exit();
