<?php
/**
 * File:        /core/init.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Error logging
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

/**
 * Unicode Support
 */
mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');

/**
 * Global arrays
 */
$lang = $langdate = $group = $config = $charsebd = $global = $scheme = $realmod = array();
$global['insert'] = array();

/**
 * Request Config
 */
require DNDIR.'core/config.php';

/**
 * System settings from the Cache
 */
if (file_exists(DNDIR.'cache/cache.config.php'))
{
	include DNDIR.'cache/cache.config.php';
	if (empty($config))
	{
		die('No configuration settings! Save the settings in the admin panel.');
	}
}
else
{
	die('No configuration file systems! Save the settings in the admin panel.');
}

/**
 * Init DB
 */
require DNDIR.'core/classes/DB.php';
$db = new DB($hostname, $nameuser, $password, $namebase, $charsebd);

/**
 * Активные моды
 */
$inq = $db->query("SELECT file, active FROM ".$basepref."_mods WHERE active = 'yes'");
while($item = $db->fetchrow($inq))
{
	$realmod[] = $item['file'];
}

/**
 * Caching basic database queries
 */
if ($config['cache'] == 'yes') {
	$config['cache'] =  TRUE;
} else {
	$config['cache'] = $config['cachetime'] = FALSE;
}

/**
 * Declare variables, mod | breadcrumb
 */
$global['insert']['breadcrumb'] = NULL;

/**
 * DOCUMENT_ROOT
 */
$_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);

/**
 * Function
 */
require DNDIR.'core/function.php';

/**
 * Relative path root
 */
$DNROOT = str_replace(DOCUMENT_ROOT, '', str_replace('core', '', str_replace('\\', '/', __DIR__)));

$sub_domain = implode('', array_filter(explode('/', $DNROOT), 'trim'));
$host_array = explode('.', parse_url(SITE_URL, PHP_URL_HOST));

/**
 * DNROOT
 */
if ( ! empty($sub_domain) AND in_array($sub_domain, $host_array)) 
{
	$DNROOT = '/';
}

define('DNROOT', $DNROOT);

/**
 * Includes
 */
include_once DNDIR.'core/includes/lang.php';
include_once DNDIR.'core/includes/track.php';
include_once DNDIR.'core/includes/protect.php';

/**
 * Autoloader
 */
require_once __DIR__.'/Loader.php';
new Loader(DNDIR.'/core/classes/');

/**
 * Init Core Classes
 */
$tm  = new Template;
$ro  = new Router;
$api = new Api;

/**
 * Service SMS
 */
if ($config['service_sms'] == 'smsc') {
	$sms = new DN\Sms\SMSC();
} elseif ($config['service_sms'] == 'smsru') {
	$sms = new DN\Sms\SMSRU();
}

/**
 * USERS
 */
if (isset($config['mod']['user']))
{
	include_once DNDIR.'core/includes/users.php';
}

/**
 * currency
 */
if (isset($config['catalog']) AND  ! empty($config['catalog']))
{
	$config['viewcur'] = $config['catalog']['currency'];
	$config['arrcur'] = Json::decode($config['catalog']['currencys']);
	if (isset($_COOKIE['currency']))
	{
		if (isset($config['arrcur'][$_COOKIE['currency']]))
		{
			$config['viewcur'] = $_COOKIE['currency'];
		}
	}
}

/**
 * Init Banner Rotation
 */
if ($config['banner'] == 'yes')
{
	$adv = new Rotator;
	require DNDIR.'core/includes/banner.php';
}

/**
 * Debugging the Code
 */
if ($config['debug_code'] == 'yes')
{
	new Debug();
}
else
{
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
}
