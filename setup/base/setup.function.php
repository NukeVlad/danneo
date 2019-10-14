<?php
/**
 * File:        setup/base/danneo.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 *  IF ISSET GLOBALS OLD'S HTTP
 */
if (isset($HTTP_POST_VARS) OR isset($HTTP_GET_VARS))
{
    $_POST    = $HTTP_POST_VARS;
    $_GET     = $HTTP_GET_VARS;
    $_REQUEST = array_merge($_POST, $_GET);
    $_COOKIE  = $HTTP_COOKIE_VARS;
    $_SERVER  = $HTTP_SERVER_VARS;
}

/**
 *  STRIP SLASHES ALL FUNCTION
 */
function stripslashesall(&$array)
{
    reset($array);
	foreach ($array as $key => $val)
	{
        if (is_string($val)) {
        	$array[$key] = stripslashes($val);
        } elseif (is_array($val)) {
        	$array[$key] = stripslashesall($val);
        }
    }
    return $array;
}

/**
 *  IF GET MAGICQUOTES GPC
 */
if (get_magic_quotes_gpc())
{
    if ($_POST)
        $_POST = stripslashesall($_POST);
    if ($_GET)
        $_GET = stripslashesall($_GET);
    if ($_REQUEST)
        $_REQUEST = stripslashesall($_REQUEST);
    if ($_COOKIE)
        $_COOKIE = stripslashesall($_COOKIE);
}

/**
 *  IF REGISTER GLOBALS
 */
if (!ini_get("register_globals") OR (@get_cfg_var('register_globals') == 1))
{
    foreach($_COOKIE as $key => $val) {
        if(!isset($s_globals[$key])) {
            $GLOBALS[$key] = $val;
        }
    }
    foreach($_POST as $key => $val) {
        if(!isset($s_globals[$key])) {
            $GLOBALS[$key] = $val;
        }
    }
    foreach($_GET as $key => $val) {
        if(!isset($s_globals[$key])) {
            $GLOBALS[$key] = $val;
        }
    }
    foreach($_REQUEST as $key => $val) {
        if(!isset($s_globals[$key])) {
            $GLOBALS[$key] = $val;
        }
    }
}

/**
 * Constants
 */
define('NEWDATE', date("d-m-Y"));
define('NEWDAY', date("d"));
define('NEWMONT', date("m"));
define('NEWYEAR', date("Y"));
define('NEWTIME', time());
define('TODAY', mktime(0,0,0,date('m'),date('d'),date('Y')));

/**
 *  CODE
 */
function code($text, $liter)
{
    $glif = array();
    for ($exi = 128; $exi <= 143; $exi++) {
        $glif['w'][] = chr($exi + 112);
        $glif['u'][] = chr(209) . chr($exi);
    }
    for ($exi = 144; $exi <= 191; $exi++) {
        $glif['w'][] = chr($exi + 48);
        $glif['u'][] = chr(208) . chr($exi);
    }
    $glif['w'][] = chr(168);
    $glif['w'][] = chr(184);
    $glif['u'][] = chr(208) . chr(129);
    $glif['u'][] = chr(209) . chr(145);
    return ($liter == 'w') ? str_replace($glif['u'], $glif['w'], $text) : str_replace($glif['w'], $glif['u'], $text);
}

/**
 *  Write Config
 */
function write_php_file($files, $bdhost, $bduser, $bdpass, $bdbase, $bdpref)
{
	global $conf, $charsebd;

	if (file_exists($files) AND is_writable($files))
	{
		$php_write = fopen($files, 'wb');

		if (is_resource($php_write))
		{
			$create = "<?php\n";
			$create .= "/** \n";
			$create .= " * File:        /core/config.php\n";
			$create .= " * \n";
			$create .= " * @package     Danneo Basis kernel\n";
			$create .= " * @version     Danneo CMS (Next) \n";
			$create .= " * @copyright   (c) 2005-2019 Danneo Team\n";
			$create .= " * @link        http://danneo.ru\n";
			$create .= " * @license     http://www.gnu.org/licenses/gpl-2.0.html\n";
			$create .= " */\n";
			$create .= "if(!defined('DNREAD')) exit();\n";
			$create .= "/**\n";
			$create .= " * Данные подключения к БД \n";
			$create .= " * -------------------------- */\n";
			$create .= "\$hostname = \"".$bdhost."\";\n";
			$create .= "\$nameuser = \"".$bduser."\";\n";
			$create .= "\$password = \"".$bdpass."\";\n";
			$create .= "\$namebase = \"".$bdbase."\";\n";
			$create .= "\$basepref = \"".$bdpref."\";\n";
			$create .= "/**\n";
			$create .= " * Кодировка по умолчанию \n";
			$create .= " * -------------------------- */\n";
			$create .= "\$charsebd = \"".$charsebd."\";\n";

			fputs($php_write, $create);
			fclose($php_write);

			return true;
		}
		else
		{
			return false;
		}
	}
}

/**
 *  Acookname
 */
function acookname()
{
    global $conf;
    $name = "";
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    for ($i = 0; $i < 20; $i++) {
        $name .= substr($chars, (mt_rand() % strlen($chars)), 1);
    }
    return $name;
}

/**
 * Array Replace Recursive
 * Fix array_replace_recursive for < PHP 5.3
 */
if ( ! function_exists('array_replace_recursive'))
{
    function array_recurse($array, $array1)
    {
        foreach ($array1 as $key => $value)
        {
            if ( ! isset($array[$key]) OR (isset($array[$key]) AND ! is_array($array[$key])))
            {
                $array[$key] = array();
            }

            if (is_array($value))
            {
                $value = array_recurse($array[$key], $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    function array_replace_recursive($array, $array1)
    {
        $args = func_get_args();
        $array = $args[0];

        if ( ! is_array($array))
        {
            return $array;
        }

        for ($i = 1; $i < count($args); $i++)
        {
            if (is_array($args[$i]))
            {
                $array = array_recurse($array, $args[$i]);
            }
        }
        return $array;
	}
}

/**
 * Define HOST_URL
 */
if (isset($_SERVER['HTTP_HOST']))
{
	$host_url = (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) !== 'Off') ? 'https' : 'http';
	$host_url.= '://'. $_SERVER['HTTP_HOST'];
}
else
{
	$host_url = 'http://localhost';
}
$host_url = rtrim($host_url, '/');
if ( ! defined('HOST_URL') )
{
	define('HOST_URL', $host_url);
}

/**
 * Database version
 */
function databaseversion()
{
	global $db;

	list($version) = $db->fetchrow($db->query("SELECT VERSION()"));
	return $version;
}

/**
 *  Recursive directory processing,
 *  copying, change permissions
 */
function setup_copy($src, $end)
{
	$php = array('cache', 'lang', 'pages');

	$dir = opendir($src);
	@mkdir($end);
	while(false !== ( $file = readdir($dir)) )
	{
		if (( $file != '.' ) AND ( $file != '..' ))
		{
			if ( ! file_exists($end.'/'.$file))
			{
				if ( is_dir($src.'/'.$file) )
				{
					setup_copy($src.'/'.$file, $end.'/'.$file);
					chmod($end.'/'.$file, 0777);
				}
				else
				{
					copy($src.'/'.$file, $end.'/'.$file);
					$ext = substr(strrchr($end.'/'.$file, '.'), 1);
					if (in_array(basename($end), $php) AND $ext == 'php') {
						chmod($end.'/'.$file, 0666);
					} else {
						chmod($end.'/'.$file, 0644);
					}
					touch($end.'/'.$file, filemtime($src.'/'.$file));
				}
			}
		}
	}
	closedir($dir);
}

/**
 *  Recursive, change permissions
 */
function dn_permiss($src)
{
	$dir = opendir($src);
	$php = array('cache', 'lang', 'pages');
	while(false !== ( $file = readdir($dir)) )
	{
		if (( $file != '.' ) AND ( $file != '..' ))
		{
			if (file_exists($src.'/'.$file))
			{
				if ( is_dir($src.'/'.$file) )
				{
					chmod($src.'/'.$file, 0777);
					dn_permiss($src.'/'.$file);
				}
				else
				{
					$ext = substr(strrchr($src.'/'.$file, '.'), 1);
					if (in_array(basename($src), $php) AND $ext == 'php') {
						chmod($src.'/'.$file, 0666);
					} else {
						chmod($src.'/'.$file, 0644);
					}
				}
			}
		}
	}
	closedir($dir);
}

/**
 *  Unpack the archive
 */
function dn_unzip($src, $dir)
{
	$zip = new ZipArchive;
	if ($zip->open($src) === true)
	{
		$zip->extractTo($dir);
		$zip->close();
	}
	else
	{
		die('Error extractTo Zip');
	}
}
