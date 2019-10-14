<?php
/**
 * File:        /index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR define('DNREAD', TRUE);

define('TIMESTART', microtime(TRUE));
define('MEMORYSTART', memory_get_usage());
define('DNDIR', str_replace('\\', '/', realpath(__DIR__) . DIRECTORY_SEPARATOR));

/**
 * Init Core
 */
require_once DNDIR.'core/init.php';

/**
 * Closed Site
 */
if ($config['closed'] == 'yes')
{
	$tm->closeprint($config['closedtext']);
}

/**
 * 404 Not Found
 */
if (isset($_REQUEST['dn']))
{
	if (strpos($_REQUEST['dn'], '/') !== FALSE)
	{
		$tm->noexistprint();
	}

	$sdn = $api->sitedn($_REQUEST['dn']);
	if (isset($_REQUEST['pa']))
	{
		$sdn = $api->sitedn($_REQUEST['pa']);
	}

	if ( ! empty($sdn))
	{
		if ( ! isset($config['mod'][$sdn]))
		{
			$tm->noexistprint();
		}
	}
}

/**
 * $_REQUEST
 */
$global['dn'] = (isset($_REQUEST['dn'])) ? $api->sitedn($_REQUEST['dn']) : '';
$global['to'] = (isset($_REQUEST['to'])) ? $api->sitedn($_REQUEST['to']) : 'index';
$global['re'] = (isset($_REQUEST['re'])) ? $api->sitedn(basename($_REQUEST['re'])) : 'index';
$global['pa']  = (isset($_REQUEST['pa']) AND preparse($_REQUEST['pa'], THIS_SYMNUM, TRUE) == 0) ? $api->sitepa($_REQUEST['pa']) : '';
$global['cpu'] = ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0) ? preparse($cpu, THIS_TRIM, 0, 255) : '';

/**
 * Extras. mod file
 */
if ($global['re'] != 'index')
{
	if ( ! empty($global['dn']))
	{
		$mod_scheme = DNDIR.'mod/'.$_REQUEST['dn'].'/mod.scheme.php';
		if (file_exists($mod_scheme))
		{
			include($mod_scheme);
		}
	}

	$global['re'] = (is_array($scheme) AND isset($scheme[$global['re']])) ? $global['re'] : 'index';
	if ($global['re'] == 'index')
	{
		$tm->noexistprint();
	}
}

/**
 * Mod clone
 */
if (isset($_REQUEST['pa']))
{
	$global['dn'] = $api->sitedn($_REQUEST['pa']);
}

/**
 * Mod name
 */
$global['dn'] = (isset($config['mod'][$global['dn']])) ? $global['dn'] : $config['site_home'];
$global['modname'] = (isset($config['mod'][$global['dn']])) ? $config['mod'][$global['dn']]['name'] : '';

/**
 * Template
 */
if (isset($config['mod'][$global['dn']]) AND ! empty($config['mod'][$global['dn']]['temp']))
{
	$config['site_temp'] = $config['mod'][$global['dn']]['temp'];
}
if (isset($config['site_temp']))
{
	define('SITE_TEMP', $config['site_temp']);
}

/**
 * Connecting blocks
 */
$get_block = DNDIR.'cache/cache.block.php';
if (file_exists($get_block))
{
	include_once($get_block);
}
if (defined('CACHEBLOCK'))
{
	foreach ($site_blocks as $key_block => $val_block)
	{
		$config['bview'] = 0;
		if (isset($val_block['block_mods'][$global['dn']]))
		{
			// All mods
			if (isset($val_block['block_mods'][$global['dn']]['cat']) AND isset($val_block['block_mods'][$global['dn']][$global['re']][$global['to']]))
			{
				// Cat id
				preg_match_all('/\d+/', $val_block['block_mods'][$global['dn']]['cat'], $cats);

				if (defined('SEOURL') AND preparse($ccpu, THIS_SYMNUM, TRUE) == 0)
				{
					$incat = $db->fetchassoc(
						$db->query("SELECT catid FROM ".$basepref."_".$global['dn']."_cat WHERE catcpu = '".$ccpu."' LIMIT 1")
					);

					$catid = $incat['catid'];
				}
				else
				{
					$catid = $id;
				}

				if (isset($val_block['block_mods'][$global['dn']]['exc']))
				{
					$config['bview'] = ! in_array($catid, $cats[0]) ? 1 : 0;
				}
				else
				{
					$config['bview'] = in_array($catid, $cats[0]) ? 1 : 0;
				}
			}
			else
			{
				$config['bview'] = isset($val_block['block_mods'][$global['dn']][$global['re']][$global['to']]) ? 1 : 0;
			}

			// Pages mod
			if ( ! isset($_REQUEST['re']))
			{
				if ($global['dn']  == 'pages')
				{
					$config['bview'] = isset($val_block['block_mods'][$global['dn']][$global['cpu']]) ? 1 : 0;
				}
				// Clones [ pa ]
				if ( ! empty($global['pa']))
				{
					if ( ! empty($global['cpu'])) {
						$config['bview'] = isset($val_block['block_mods'][$global['pa']][$global['cpu']]) ? 1 : 0;
					} else {
						$config['bview'] = isset($val_block['block_mods'][$global['pa']][$global['to']]) ? 1 : 0;
					}
				}
			}

			// Access to blocks
			if ($config['bview'] == 1 AND $val_block['block_access'] == 'user')
			{
				if ( ! defined('USER_LOGGED'))
				{
					$config['bview'] = 0;
				}
				else
				{
					if ($config['user']['groupact'] == 'yes' AND ! empty($val_block['block_group']))
					{
						$config['bview'] = in_array($usermain['gid'], $val_block['block_group']) ? 1 : 0;
					}
				}
			}

			// Showing the blocks
			if ($config['bview'] == 1)
			{
				$config['bsarray'] = $val_block['block_setting'];

				if ( ! empty($val_block['block_file'])) {
					$config['bcontent'] = include DNDIR.'block/'.$val_block['block_file'];
				} else {
					$config['bcontent'] = ( ! empty($val_block['block_cont'])) ? $val_block['block_cont'] : $lang['data_not'];
				}

				$block_out = $tm->parse(array
								(
									'blockname' => $val_block['block_name'],
									'blockcont' => $config['bcontent']
								),
								$tm->create('block/'.$val_block['block_temp']));

				if (isset($global['insert'][$val_block['block_side']])) {
					$global['insert'][$val_block['block_side']].= $block_out;
				} else {
					$global['insert'][$val_block['block_side']] = $block_out;
				}
			}
		}
	}
}

/**
 * Init Site Menu
 */
$get_menu = DNDIR.'cache/cache.menu.php';
if (file_exists($get_menu))
{
	include_once($get_menu);

	// Array
	if ($config['cache_menu'] == 'no')
	{
		print_menu($array_menu);
		$global['insert']['actmenu'] = null;
	}
}

/**
 * Mod fold
 */
if (isset($_REQUEST['pa']) OR $config['mod'][$global['dn']]['parent'] > 0)
{
	$file_mod = DNDIR.'mod/pages/'.$global['re'].'.php';
}
else
{
	$file_mod = DNDIR.'mod/'.$global['dn'].'/'.$global['re'].'.php';
}

/**
 * Connecting of Mods
 */
if (file_exists($file_mod)) {
	include $file_mod;
} else {
	$tm->noexistprint();
}

/**
 * exit
 */
exit();
