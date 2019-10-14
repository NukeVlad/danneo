<?php
/**
 * File:        /core/includes/banner.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $global, $config, $tm;

/**
 * Init Banner
 */
if (isset($_GET['banid']) AND preparse($_GET['banid'], THIS_INT) > 0)
{
	$banid = substr(preparse($_GET['banid'], THIS_INT), 0, 11);
	$inqb = $db->query("SELECT * FROM ".$basepref."_banners WHERE banid = '".$banid."'");
}

if (isset($inqb) AND $db->numrows($inqb) == 1)
{
	$item = $db->fetchrow($inqb);
	$db->query("UPDATE ".$basepref."_banners SET banclick = banclick + 1 WHERE banid = '".$item['banid']."'");
	@header("Location: ".$item['banurl']);
	exit();
}
