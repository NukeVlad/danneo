<?php
/**
 * File:        /mod/user/ajax.php
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
global $db, $basepref, $config, $to, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * id
 */
$id = preparse($id, THIS_INT);

/**
 * Метки
 */
$legaltodo = array('index', 'region');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка region
 */
if ($to == 'region')
{
	header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
	header('Content-Type: text/html; charset='.$config['langcharset'].'');

	$id = preparse($id, THIS_INT);
	$cache = DNDIR.'cache/cache.country.php';

	if (file_exists($cache))
	{
		$country = require($cache);
	}

	if (is_array($country))
	{
		foreach ($country[$id]['region'] as $k => $v)
		{
           echo '<option value="'.$k.'">'.$v.'</option>';
		}
	}
	else
	{
		$inq = $db->query("SELECT * FROM ".$basepref."_country_region WHERE countryid = '".$db->escape($id)."' ORDER BY posit ASC");
		while ($item = $db->fetchassoc($inq))
		{
			echo '<option value="'.$item['regionid'].'">'.$item['regionname'].'</option>';
		}
	}
	exit();
}
