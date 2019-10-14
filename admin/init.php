<?php
/**
 * File:        /admin/init.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('READCALL') OR die('No direct access');

define("ADMREAD", 1);

/**
 * Регистрация ошибок
 */
error_reporting(E_ALL);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

/**
 * Поддержка Unicode
 */
mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');

/**
 * Маркеры оформления ошибок
 */
ini_set('error_prepend_string', '<div style="background-color: #ffe; font-size: 12px; width: 97%; margin: 1em auto; padding: 0px 15px 10px; border: 1px solid #f90;">');
ini_set('error_append_string', '</div>');

/**
 * Файл настроек
 */
require_once __DIR__.'/core/permission.php';

/**
 * Версия Danneo CMS
 */
if ( ! defined('VERSION') )
{
	define('VERSION', '1.5.5');
}

/**
 * Абсолютный путь
 */
$DIR = new SplFileInfo(__DIR__);

/**
 * Абсолютный путь, Корень сайта
 */
$DNDIR = str_replace('\\', '/', $DIR->getPathInfo());
$ROOTDIR = str_replace('\\', '/', realpath($DIR->getPathInfo() . DIRECTORY_SEPARATOR . '..'));

/**
 * Абсолютный путь, Каталог администратора
 */
$ADMDIR = str_replace('\\', '/', $DNDIR.'/'.APANEL);

/**
 * DOCUMENT_ROOT
 */
$_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);

/**
 * Базовые константы
 */
define('DNREAD', 1);
define('ADMDIR', $ADMDIR);
define('ROOTDIR', $ROOTDIR);
define('DNDIR', str_replace('\\', '/', $DNDIR.DIRECTORY_SEPARATOR));

// Not curl
if ( ! function_exists('curl_init'))
{
	define('NOTCURL', 1);
}

// AJAX
$AJAX = FALSE;
if (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes')
{
	$AJAX = TRUE;
}

// User agent
if (isset($_SERVER['HTTP_USER_AGENT']) AND $_SERVER['HTTP_USER_AGENT'] != "-")
{
	define('USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
}
else
{
	die();
}

/**
 * Буферизация
 */
ob_start();

/**
 * Обнуляем переменные
 */
unset($sess, $ops, $checkid, $checklogin, $checkpass);
$ADMIN_AUTH = $ADMIN_PERM = $ADMIN_ID = 0;

/**
 * Рабочие массивы
 */
$mods = $modname = $modposit = $realmod = $conf = $confmod = $ro = $template = array();

/**
 * Setting DB
 */
require_once DNDIR.'core/config.php';
require_once ADMDIR.'/core/classes/DB.php';

/**
 * System settings from the Cache
 */
if (file_exists(DNDIR.'cache/cache.config.php'))
{
	include_once DNDIR.'cache/cache.config.php';
}

/**
 * Init DB
 */
$db = new DB($hostname, $nameuser, $password, $namebase, $charsebd);

/**
 * Активные моды
 */
$inq = $db->query("SELECT file, name, posit, active FROM ".$basepref."_mods ORDER BY posit");
while($item = $db->fetchrow($inq))
{
	$mods[$item['file']] = $item['active'];
	$modname[$item['file']] = $item['name'];
	$modposit[$item['file']] = $item['posit'];
	$realmod[] = $item['file'];
}

/**
 * Настройки сайта
 */
$inq = $db->query("SELECT setopt, setname, setval FROM ".$basepref."_settings");
while($item = $db->fetchrow($inq))
{
	if (in_array($item['setopt'], $realmod)) {
		$confmod[$item['setopt']][$item['setname']] = $item['setval'];
	} else {
		$conf[$item['setname']] = $item['setval'];
	}
}
$conf = array_merge($conf, $confmod);

/**
 * Функциональные файлы
 */
require_once ADMDIR.'/core/function.php';
require_once ADMDIR.'/core/track.php';

/**
 * Autoloader
 */
require_once ADMDIR.'/core/Loader.php';
new Loader(ADMDIR.'/core/classes/');

/**
 * Init Core Classes
 */
$tm = new Template;
$cache = new Cache;

/**
 * Service SMS
 */
if ($conf['service_sms'] == 'smsc') {
	$sms = new DN\Sms\SMSC();
} elseif ($conf['service_sms'] == 'smsru') {
	$sms = new DN\Sms\SMSRU();
}

/**
 * URL панели
 */
define('ADMURL', SITE_URL.'/'.APANEL);

/**
 * Абсолютный путь от корня, Каталог администратора
 */
$ADMPATH = str_replace(DOCUMENT_ROOT, '', str_replace('\\', '/', __DIR__));

/**
 * Virtual ADMPATH
 */
$subdomain = implode('', array_filter(explode('/', str_replace(APANEL, '', $ADMPATH)), 'trim'));
$hostarray = explode('.', parse_url(SITE_URL, PHP_URL_HOST));

if ( ! empty($subdomain) AND in_array($subdomain, $hostarray)) {
	$ADMPATH = '/'.APANEL;
}

define('ADMPATH', $ADMPATH);

/**
 * Relative PATH ROOT
 */
$DNROOT = str_replace(DOCUMENT_ROOT, '', str_replace(APANEL, '', str_replace('\\', '/', __DIR__)));

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
 * Template Site
 */
define('SITE_TEMP', $conf['site_temp']);

/**
 * Количество на страницу
 */
$conf['num'] = explode(',', $conf['number']);

/**
 * Заголовки
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
header('Content-Type:text/html; charset='.$conf['langcharset'].'');

/**
 * Последний визит
 */
if (isset($_POST['adlog']) AND isset($_POST['adpwd']))
{
	$admlast = new Session();
	$admlast->adlast($_POST['adlog'], $_POST['adpwd']);
}

/**
 * Переменная сессии
 */
$ops = (isset($_REQUEST['ops'])) ? preparse($_REQUEST['ops'], THIS_STR, 1, 32) : '';

/**
 * Проверка сессии
 */
if (isset($ops))
{
	$validate = new Session();

	$session_error = $validate->update($ops);
	$sess['hash']  = $validate->hash;
	$sess['skin']  = $validate->skin;
	$sess['icon']  = $validate->icon;
}
else
{
	$session_error = 0;
	$sess['hash'] = $sess['skin'] = $sess['icon'] = '';
}

/**
 * Авторизация
 */
if (isset($_POST['adlog']) AND isset($_POST['adpwd']) AND empty($sess['hash']))
{
	$validate = new Session();
	$validadmin = $validate->check($_POST['adlog'], $_POST['adpwd']);
	if ($validadmin == 0)
	{
		$sess['hash'] = $validate->hash;
		header('Location: index.php?dn=index&ops='.$sess['hash']);
		exit();
	}
	else
	{
		$sess['hash'] = NULL;
		$ADMIN_AUTH = $ADMIN_PERM = $ADMIN_ID = 0;
	}
}

/**
 * Cookie
 */
if (isset($_COOKIE[ACOOKIE]) AND isset($sess['hash']))
{
	list($checkid, $checklogin, $checkpass) = unserialize($_COOKIE[ACOOKIE]);

	$checklogin = preparse_dn($checklogin);
	$checkid    = preparse($checkid, THIS_INT);
	$checkpass  = preparse($checkpass, THIS_STR, 1, 32);
}

/**
 * Языковые переменные
 */
$lang = array();
$inq_lang = $db->query(
					"SELECT langvars, langvals, langsetid FROM ".$basepref."_language
					 WHERE langpackid = '".$conf['langid']."'"
				);
while ($al = $db->fetchrow($inq_lang))
{
	$lang[$al[0]] = $al[1];
	if ($al[2] == $conf['langdateset'])
	{
		$langdate[$al[0]] = preparse_lga($al[1]);
	}
}

/**
 * Платформы сайтов
 * ------------------ */

// Платформа по умолчанию
define('DEF_SITE', $lang['main_site']);

$pls = $db->fetchassoc($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));

// Дополнительные
if ( ! empty($pls['setval']) AND Json::is_json($pls['setval']))
{
	$PLATFORM = Json::decode($pls['setval']);
}

/**
 * Авторизация
 * -------------- */
if(isset($checkid) AND isset($checklogin) AND isset($checkpass) AND $session_error == 0 AND isset($sess['hash']))
{
	if (empty($checkid) OR empty($checklogin) OR empty($checkpass))
	{
		$ADMIN_AUTH = 0;
		exit();
	}

	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_admin
				 WHERE admid = '".$db->escape($checkid)."'
				 AND adlog = '".$db->escape($checklogin)."' AND blocked = '0' LIMIT 1"
			);

	if ($db->numrows($inq) == 1)
	{
		$aditem = $db->fetchrow($inq);
		$adtemp = md5($aditem['adpwd'].SALT_ADMIN);

		if ($adtemp == $checkpass AND ! empty($aditem['adpwd']) AND $aditem['admid'] > 0)
		{
			$ADMIN_AUTH = $ADMIN_PERM = $ADMIN_ID = 0;
			$ADMIN_ID   = preparse($aditem['admid'], THIS_INT);
			$ADMIN_MAIL = $aditem['admail'];
			$ADMIN_LAST = preparse($aditem['adlast'], THIS_INT);
			$ADMIN_PERM_ARRAY = explode('|', $aditem['permiss']);

			if (in_array(PERMISS, $ADMIN_PERM_ARRAY) OR in_array('platform', $ADMIN_PERM_ARRAY))
			{
				$ADMIN_PERM = 1;
				if (isset($_COOKIE[PCOOKIE]))
				{
					list($pid) = unserialize($_COOKIE[PCOOKIE]);
					if (preparse($pid, THIS_INT) > 0 AND isset($PLATFORM[$pid]))
					{
						unset($conf, $basepref);
						$db->select($PLATFORM[$pid]['base'], 0);
						$basepref = $PLATFORM[$pid]['pref'];

						if (isset($PLATFORM[$pid]['path']))
						{
							define('WORKDIR', $PLATFORM[$pid]['path'].'/');
						}

						$mods = $modname = $modposit = $realmod = array();
						$inqs = $db->query("SELECT file, name, posit, active FROM ".$basepref."_mods ORDER BY posit");
						while($item = $db->fetchrow($inqs))
						{
							$mods[$item['file']] = $item['active'];
							$modname[$item['file']] = $item['name'];
							$modposit[$item['file']] = $item['posit'];
							$realmod[] = $item['file'];
						}

						$conf = array();
						$inq = $db->query("SELECT setopt, setname, setval FROM ".$basepref."_settings WHERE setopt <> 'apanel' AND setname <> 'agreement' ORDER BY setid");
						while($item = $db->fetchrow($inq))
						{
							if (in_array($item['setopt'], $realmod)) {
								$confmod[$item['setopt']][$item['setname']] = $item['setval'];
							} else {
								$conf[$item['setname']] = $item['setval'];
							}
						}

						$conf = array_merge($conf, $confmod);
						$conf['num'] = explode(',', $conf['number']);

						$p_lang = $db->query(
							"SELECT langvars, langvals, langsetid FROM ".$basepref."_language
							 WHERE langpackid = '".$conf['langid']."'"
						);
						while ($al = $db->fetchrow($p_lang))
						{
							$lang[$al[0]] = $al[1];
							if ($al[2] == $conf['langdateset'])
							{
								$langdate[$al[0]] = preparse_lga($al[1]);
							}
						}
					}
				}
			}

			$ADMIN_AUTH = 1;

			/*
			 * Платформы страниц
			 *-------------------- */

			// Базовый модуль
			$IPS[0]['mod'] = 'pages';
			$IPS[0]['name'] = $lang['pages'];

			// Клоны
			if (isset($conf['pages']['mods']))
			{
				$page_mod = Json::decode($conf['pages']['mods']);
				if (isset($page_mod{0}) AND is_array($page_mod))
				{
					$IPS = $page_mod;
				}
			}

			/**
			 * Абсолютный путь, корень сайта
			 */
			if ( ! defined('WORKDIR')) {
				define('WORKDIR', $DNDIR);
			}

			/**
			 * Относительный путь, корень сайта
			 */
			if ( ! defined('SITEDIR')) {
				define('SITEDIR', rtrim(str_replace(APANEL, '', str_replace('\\', '/', ADMPATH)), '/'));
			}

			/**
			 * URL сайта
			 */
			define('WORKURL', SITE_URL);

			/**
			 * Визуальный редактор
			 */
			$wysiwyg = (isset($_COOKIE[WCOOKIE]) AND $_COOKIE[WCOOKIE] == 'yes') ? 'yes' : 'no';
			$conf['empty'] = NULL;
		}
	}
}
