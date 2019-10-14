<?php
/**
 * File:        /core/includes/lang.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $config, $lang;

/**
 * Language in System
 */
if (file_exists(DNDIR.'cache/cache.lang.php'))
{
	include(DNDIR.'cache/cache.lang.php');
}
if ( ! defined('CACHLANG'))
{
	$inq = $db->query
				(
					"SELECT langsetid, langvars, langvals FROM ".$basepref."_language WHERE langcache = '1'
					 AND langpackid = '".$config['langid']."'",
					 $config['cachetime']
				);

	while ($val = $db->fetchassoc($inq, $config['cache']))
	{
		$lang[$val['langvars']] = $val['langvals'];

		if ($val['langsetid'] == $config['langdateset'])
		{
			$langdate[$val['langvars']] = $val['langvals'];
		}
	}
}
