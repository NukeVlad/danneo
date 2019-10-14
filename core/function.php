<?php
/**
 * File:        /core/function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * date_default_timezone_set
 */
if (function_exists('date_default_timezone_set'))
{
	$tz = array
	(
		'-12' => 'Pacific/Kwajalein',
		'-11' => 'Pacific/Samoa',
		'-10' => 'Pacific/Honolulu',
		'-9'  => 'America/Juneau',
		'-8'  => 'America/Los_Angeles',
		'-7'  => 'America/Denver',
		'-6'  => 'America/Mexico_City',
		'-5'  => 'America/New_York',
		'-4'  => 'America/Caracas',
		'-3'  => 'America/Argentina/Buenos_Aires',
		'-2'  => 'Atlantic/South_Georgia',
		'-1'  => 'Atlantic/Azores',
		'0'   => 'Europe/London',
		'1'   => 'Europe/Berlin',
		'2'   => 'Europe/Kaliningrad',
		'3'   => 'Europe/Moscow',
		'4'   => 'Europe/Samara',
		'5'   => 'Asia/Yekaterinburg',
		'6'   => 'Asia/Omsk',
		'7'   => 'Asia/Krasnoyarsk',
		'8'   => 'Asia/Irkutsk',
		'9'   => 'Asia/Yakutsk',
		'10'  => 'Asia/Vladivostok',
		'11'  => 'Asia/Magadan',
		'12'  => 'Asia/Kamchatka'
	);

	$uct = (isset($tz[$config['timezone']])) ? $tz[$config['timezone']] : $tz[3];
	date_default_timezone_set($uct);
}

/**
 * Constants
 */
define('THIS_INT', 1);
define('THIS_STR', 2);
define('THIS_MD_5', 3);
define('THIS_ADD_SLASH', 4);
define('THIS_STRLEN', 5);
define('THIS_ARRAY', 6);
define('THIS_EMPTY', 7);
define('THIS_TRIM', 8);
define('THIS_SYMNUM', 9);
define('THIS_EMAIL', 10);
define('THIS_NUMBER', 11);
define('NEWDATE', date("d-m-Y"));
define('NEWDAY', date("d"));
define('NEWMONT', date("m"));
define('NEWYEAR', date("Y"));
define('NEWTIME', time());
define('USERCOOKIE', 'user_'.$config['cookname']);
define('TODAY', mktime(0, 0, 0, date('m'), date('d'), date('Y')));

/**
 * Define the memory usage
 */
if ( ! defined('MEMORYSTART'))
{
	define('MEMORYSTART', memory_get_usage());
}

/**
 * USER_AGENT
 */
if (isset($_SERVER['HTTP_USER_AGENT']) AND $_SERVER['HTTP_USER_AGENT'] != '-')
{
	define('USER_AGENT',$_SERVER['HTTP_USER_AGENT']);
}
else
{
	die();
}

/**
 * function preparse
 */
function preparse($resursing, $type, $c = FALSE)
{
	global $config;

	if ($type == THIS_INT) {
		return (intval($resursing) > 0) ? intval($resursing) : 0;
	}
	if ($type == THIS_MD_5) {
		return md5($resursing);
	}
	if ($type == THIS_ADD_SLASH) {
		return addslashes($resursing);
	}
	if ($type == THIS_STRLEN) {
		return mb_strlen($resursing, $config['langcharset']);
	}
	if ($type == THIS_TRIM) {
		return trim($resursing);
	}
	if ($type == THIS_ARRAY) {
		return (is_array($resursing)) ? 1 : 0;
	}
	if ($type == THIS_EMPTY) {
		return (empty($resursing)) ? 1 : 0;
	}
	if ($type == THIS_SYMNUM) {
		if ($c) {
			return $resursing = ( ! preg_match('/^[a-zA-Z0-9_-]+$/D', $resursing)) ? 1 : 0;
		} else {
			return $resursing = ( ! preg_match('/^[a-zA-Z0-9_]+$/D', $resursing)) ? 1 : 0;
		}
	}
	if ($type == THIS_EMAIL) {
		return (preg_match('/[\w\.\-]+@\w+[\w\.\-]*?\.\w{2,4}/', $resursing)) ? 1 : 0;
	}
	if ($type == THIS_NUMBER) {
		return ( ! preg_match('/^[0-9]+$/D', $resursing)) ? 1 : 0;
	}
}

/**
 * Get the client IP address
 * @return constant REMOTE_ADDRS
 */
if ( ! empty($_SERVER['HTTP_X_REAL_IP']))
{
	$REMOTE_ADDR = $_SERVER['HTTP_X_REAL_IP'];
}
elseif ( ! empty($_SERVER['HTTP_CLIENT_IP']))
{
	$REMOTE_ADDR = $_SERVER['HTTP_CLIENT_IP'];
}
elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR']))
{
	$REMOTE_ADDR = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
	$REMOTE_ADDR = end($REMOTE_ADDR);
}
else
{
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
}
if (isset($REMOTE_ADDR))
{
	define('REMOTE_ADDRS', $REMOTE_ADDR);
}

/**
 * Get correct IP address of the client
 *
 * @param private IP-address are excluded
 * @return constant CORRECT_REMOTE_ADDRS
 */
if (defined('REMOTE_ADDRS'))
{
	$CORRECT_ADDRS = '';
	if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", REMOTE_ADDRS, $ip_match))
	{
		$private = array("/^0\./", "/^127\.0\.0\.1/", "/^192\.168\..*/", "/^172\.16\..*/","/^10..*/", "/^224..*/", "/^240..*/");
		$CORRECT_ADDRS = preg_replace($private, REMOTE_ADDRS, $ip_match[1]);
	}

	if (strlen($CORRECT_ADDRS) > 16)
	{
		$CORRECT_ADDRS = substr($CORRECT_ADDRS, 0, 16);
	}

	if ( ! empty($CORRECT_ADDRS))
	{
		define('CORRECT_REMOTE_ADDRS', $CORRECT_ADDRS);
	}
}

/**
 * Define DOCUMENT_ROOT
 */
if(isset($_SERVER['DOCUMENT_ROOT']))
{
	define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
}
else
{
	define('DOCUMENT_ROOT', realpath(getcwd()));
}

/**
 * Get the client's referers addres
 * @return Define constant HTTP_REFERERS
 */
if (isset($_SERVER['HTTP_REFERER']))
{
	$REFERER = $_SERVER['HTTP_REFERER'];
}
if (empty($REFERER) AND getenv('HTTP_REFERER'))
{
	$REFERER = getenv('HTTP_REFERER');
}
if (isset($REFERER))
{
	define('HTTP_REFERERS', $REFERER);
}

/**
 * Define SITE_URL
 */
if (isset($config['site_url']) AND ! empty($config['site_url']))
{
	define('SITE_URL', $config['site_url']);
}
else
{
	if (isset($_SERVER['HTTP_HOST']))
	{
		$SITE_URL = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : NULL;
		$SITE_URL = (($SITE_URL) AND ($SITE_URL != 'off')) ? 'https' : 'http';
		$SITE_URL = $SITE_URL.'://'.$_SERVER['HTTP_HOST'];
	}
	else
	{
		$SITE_URL = 'http://localhost';
	}
	$SITE_URL = rtrim($SITE_URL, '/');
	define('SITE_URL', $SITE_URL);
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
 * Get the request_uri addres
 *
 * @return Define constant REQUEST_URI
 * @return Define constant FULL_REQUEST_URI
 */
if ( ! empty($_SERVER['PATH_INFO']))
{
	$REQUEST_URI = $_SERVER['PATH_INFO'];
}
else
{
	if (isset($_SERVER['REQUEST_URI']))
	{
		$REQUEST_URI = $_SERVER['REQUEST_URI'];
	}
	elseif (isset($_SERVER['PHP_SELF']))
	{
		$REQUEST_URI = $_SERVER['PHP_SELF'];
	}
	elseif (isset($_SERVER['REDIRECT_URL']))
	{
		$REQUEST_URI = $_SERVER['REDIRECT_URL'];
	}
	else
	{
		if (isset($_SERVER['QUERY_STRING']))
		{
			$REQUEST_URI = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
		}
		else
		{
			$REQUEST_URI = $_SERVER['SCRIPT_NAME'];
		}
	}
}
if (isset($REQUEST_URI))
{
	$REQUEST_URI = '/'.ltrim($REQUEST_URI, '/');
	define('REQUEST_URI', $REQUEST_URI);
	define('FULL_REQUEST_URI', HOST_URL.REQUEST_URI);
}

/**
 * Define SEOURL
 */
if ( ! defined('SITE_HOST'))
{
	define('SITE_HOST', parse_url(SITE_URL, PHP_URL_HOST));
}

/**
 * Define SITE_HOST_URL
 */
$array_url = parse_url(SITE_URL);
$host_url = $array_url['scheme'].'//'.$array_url['host'];
if ( ! defined('SITE_HOST_URL') )
{
	define('SITE_HOST_URL', $host_url);
}

/**
 * Define SEOURL
 */
if ($config['cpu'] == 'yes') {
	define('SEOURL', TRUE);
}

/**
 * Define SEOURL
 */
if (isset($config['langcharset'])) {
	define('CHARSET', TRUE);
}

/**
 * Define SUF
 */
if (isset($config['suffix']) AND $config['suffix'] == 'yes') {
	define('SUF', '.html');
} else {
	define('SUF', NULL);
}

/**
 * redirect
 */
function redirect($url)
{
	$url = str_replace('&amp;', '&',$url);
	header('Location: '.$url);
	exit();
}

/**
 * file_size
 */
function file_size($size)
{
	$farr = array('Bt','Kb','Mb','Gb','Tb','Pb');
	$i = 0;
	while ($size >= 1024) {
		$size /= 1024;
		$i ++;
	}
	return round($size, 2).' '.$farr[$i];
}

/**
 * verify_pwd
 */
function verify_pwd($pwd)
{
	global $config;
	return ((preparse($pwd, THIS_STRLEN) < $config['user']['minpass']) OR (preparse($pwd, THIS_STRLEN) > $config['user']['maxpass']) OR ! preg_match('/^[a-zA-Z0-9]+$/D', $pwd)) ? 0 : 1;
}

/**
 * verify_name
 */
function verify_name($name)
{
	global $config;
	return (preparse($name, THIS_STRLEN) < $config['user']['minname'] OR preparse($name, THIS_STRLEN) > $config['user']['maxname'] OR ! preg_match('/^[\pL\pNd]+$/u', $name)) ? 0 : 1;
}

/**
 * verify send name
 */
function verify_send_name($name)
{
	return (preg_match('/^[\pL\pNd\pZs\pP\pM]+$/u', $name)) ? 1 : 0;
}

/**
 * verify_mail
 */
function verify_mail($email)
{
	if (function_exists('filter_var')) {
		return (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) ? FALSE : TRUE;
	} else {
		return (boolean)preg_match(
			'/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
			'[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
			$email
		);
	}
}

/**
 * verify_code
 */
function verify_code($length = 11)
{
	$length = ($length > 32) ? 32 : $length;
	return substr(md5(uniqid(mt_rand(), true)), 0, $length);
}

/**
 * Verify for phone number
 */
function verify_phone($phone)
{
	return preg_match('/^[+0-9. ()-]*$/', $phone);
}

/**
 * Clear format date
 */
function clear_date($format)
{
	return preg_replace('/[^A-Za-z1:%-\/\.\s]/', '', $format);
}

/**
 * parser text
 */
function this_text($carray, $contents)
{
	foreach ($carray as $key => $value)
	{
		$newkey[$key] = '{'.$key.'}';
		$newval[$key] = $value;
	}
	return str_replace($newkey, $newval, $contents);
}

/**
 * send_mail
 */
function send_mail($to, $subject, $message, $from, $attach = null, $box = false)
{
	global $config;

	require_once(DNDIR.'core/classes/Mail.php');
	$send = new Mail();

	$send->From($from);
	$send->To($to);
	$send->Subject($subject);
	$send->Body($message);
	$send->Priority(1);

	if ($config['mail_format'] == 'html') {
		$send->Html();
	}
	if ($config['mail_attach'] == 'yes' AND isset($attach)) {
		$send->Attach($attach);
	}

	return $send->acting($box);
}

/**
 * utfinwin
 */
function utfinwin($string)
{
	static $cv = '';
	if ( ! is_array($cv))
	{
		$cv = array();
		for ($x = 129; $x <= 143; $x ++)
		{
			$cv['u'][] = chr(209).chr($x);
			$cv['w'][] = chr($x + 112);
		}
		for ($x = 144; $x <= 191; $x ++)
		{
			$cv['u'][] = chr(208).chr($x);
			$cv['w'][] = chr($x + 48);
		}
		$cv['u'][] = chr(208).chr(129);
		$cv['w'][] = chr(168); // Ё
		$cv['u'][] = chr(209).chr(145);
		$cv['w'][] = chr(184); // ё
		$cv['u'][] = chr(209).chr(128);
		$cv['w'][] = chr(240); // р
	}
	return str_replace ($cv['u'], $cv['w'], $string);
}

/**
 * utfread
 */
function utfread($string, $code)
{
	if (function_exists('iconv'))
	{
		$result = iconv('utf-8', $code, $string);
	}
	else if (function_exists('mb_convert_encoding'))
	{
		$result = mb_convert_encoding($string, $code, 'utf-8');
	}
	else
	{
		if ($code = 'windows-1251')
		{
			$result = utfinwin($string);
		}
	}
	return $result;
}

/**
 * commentparse
 */
function commentparse($text)
{
	global $config;

	$text = htmlspecialchars(strip_tags($text), ENT_QUOTES, $config['langcharset']);
	$text = str_replace(array('  ', '%20%20', '%20'), ' ', $text);
	$xss = array("'data'is", "'javascript:'is", "'alert'is", "'vbscript'is", "'onmouseover'is", "'onclick'is", "'onload'is");
	$text = preg_replace($xss, '', $text);
	return nl2br($text);
}

/**
 * smilieparse
 */
function smilieparse($contents, $carray, $show = TRUE)
{
	global $config;
	$newkey = $newval = array();
	foreach ($carray as $smilie)
	{
		$newkey[$smilie['code']] = $smilie['code'];
		$newval[$smilie['code']] = ($show) ? '<img src="'.$config['site_url'].'/'.$smilie['img'].'" alt="'.$smilie['alt'].'" />' : '';
	}
	return str_replace($newkey, $newval, $contents);
}

/**
 * commentout
 */
function commentout($text)
{
	global $config;

	if ( ! empty($config['combad']))
	{
		$bwords = explode(',', str_replace(' ', '', $config['combad']));
		$text = str_replace($bwords, '****', $text);
	}

	if ($config['comauto'] == 'yes')
	{
		// urldecode
		$text = urldecode($text);

		$text = preg_replace("'(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)'is", "\\1[URL]\\2[/URL]", $text);
		$text = preg_replace("'(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)'is", "\\1[URL]\\2[/URL]", $text);
		$find = array("/([ \n\r\t])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[^\s]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/usi", "/^([_a-z0-9-]+(\.[_a-z0-9-]+)*@[^\s]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/usi");
		$re = array("\\1[MAIL]\\2[/MAIL]","[MAIL]\\0[/MAIL]");
		$text = (strpos($text, '@')) ? preg_replace($find, $re, $text) : $text;
	}

	if ($config['comwrap'] > 0)
	{
		$text = preg_replace('#(?>[^\s&/<>"\\-\[\]]|&[\#a-z0-9]{1,4};){'.$config['comwrap'].'}#ui','$0 ', $text);
	}

	$find = array
	(
		"'\[QUOTE\](.*?)\[/QUOTE\]'is",
		"'\[B\](.*?)\[/B\]'is",
		"'\[I\](.*?)\[/I\]'is",
		"'\[U\](.*?)\[/U\]'is",
		"'\[URL\]([\w]+?://[^ \"\n\r\t<]*?)\[/URL\]'is",
		"'\[URL\]((www|ftp)\.[^ \"\n\r\t<]*?)\[/URL\]'is",
		"'\[URL=([\w]+?://[^ \"\n\r\t<]*?)\](.*?)\[/URL\]'i",
		"'\[URL=((www|ftp)\.[^ \"\n\r\t<]*?)\]([^?\n\r\t].*?)](.*?)\[/URL\]'is",
		"'\[MAIL\](.*?)\[/MAIL\]'is",
		"'\[MAIL=(.*?)\](.*?)\[/MAIL\]'is"
	);

	$replace = array
	(
		"<q>\\1</q>",
		"<b>\\1</b>",
		"<i>\\1</i>",
		"<u>\\1</u>",
		"<a href=\"/index.php?go=\\1\" rel=\"nofollow\">\\1</a>",
		"<a href=\"/index.php?go=http://\\1\" rel=\"nofollow\">\\1</a>",
		"<a href=\"/index.php?go=\\1\" rel=\"nofollow\">\\2</a>",
		"<a href=\"/index.php?go=\\1\" rel=\"nofollow\">\\2</a>",
		"<a href=\"mailto:\\1\" rel=\"nofollow\">\\1</a>",
		"<a href=\"mailto:\\1\" rel=\"nofollow\">\\2</a>"
	);

	$text = preg_replace($find, $replace, $text);
	return preg_replace("#\[(/?)(QUOTE|B|U|I|URL|MAIL)(.*?)\]#is", '', $text);
}

/**
 * deltags
 */
function deltags($text)
{
	$text = strip_tags($text);
	$text = preg_replace("#\[(/?)(QUOTE|B|U|I|URL|MAIL)(.*?)\]#is", "", $text);
	return commentout($text);
}

/**
 * titleout
 */
function str_word($text, $zero = '60', $dots = '...')
{
	global $config;

	$who = ''; $col = 0;
	$text = htmlspecialchars(strip_tags($text), ENT_QUOTES, $config['langcharset']);
	foreach (explode(' ', $text) as $vm)
	{
		$res = (( ! empty($who)) ? ' ' : '').$vm;
		$who.= $res;
		$col += mb_strlen($res);
		if (mb_strlen($who) >= $zero) {
			break;
		}
	}
	return preg_replace('/[;,.!?:]+$/um', '', $who).(($col < mb_strlen($text)) ? $dots : '');
}

/**
 * randoms
 */
function randoms()
{
	return mt_rand(-5, 5);
}

/**
 * dirbase
 */
function dirbase($dir = FALSE)
{
	return str_replace($dir.__DIR__, '', $_SERVER['SCRIPT_NAME']);
}

/**
 * findcaptcha
 */
function findcaptcha($ip, $code)
{
	global $db, $basepref;

	$error = 0;
	if (preparse($code, THIS_NUMBER) == 1) {
		return 1;
	}
	if (preparse($code, THIS_STRLEN) > 5) {
		return 1;
	}
	$captchaitem = $db->fetchassoc($db->query("SELECT captchcode FROM ".$basepref."_captcha WHERE captchip = '".$db->escape($ip)."' ORDER BY captchtime ASC LIMIT 1"));
	if ($captchaitem['captchcode'] != $code) {
		return 1;
	}
}

function formats($val, $point, $decimal = '.', $thousand = ',', $convert = FALSE)
{
	if ($convert)
	{
		$val = $convert * $val;
	}
	return number_format(round($val, $point), $point, $decimal, $thousand);
}

function convert64b32($int)
{
	if ($int > 2147483647 OR $int < -2147483648)
	{
		$int = $int ^ 18446744069414584320;
	}
	return $int;
}

function crc_32($value)
{
	return convert64b32(crc32($value));
}

function dntm()
{
	global $lang;

	$imgcopy = '<img src="'.SITE_URL.'/template/'.SITE_TEMP.'/images/power.gif" alt="'.$lang['powered'].'" />';
	if (REQUEST_URI == '/') {
		return '<a href="http://danneo.ru">'.$imgcopy.'</a>';
	} else {
		return '<a class="dncopy" href="'.SITE_URL.'">'.$imgcopy.'</a>';
	}
}

/**
 * Serialize to check
 *
 * @param   serialize string
 * @return  boolean
 */
function is_serialize($str)
{
	return ($str == serialize(FALSE) OR @unserialize($str) !== FALSE);
}

/**
 * Tests whether a string contains only 7-bit ASCII bytes.
 *
 * @param   mixed $str string or array of strings to check
 * @return  boolean
 */
function is_ascii($str)
{
	if ( is_array($str) ) {
		$str = implode($str);
	}
	return ( ! preg_match('/[^\x00-\x7F]/S', $str)) ? FALSE : TRUE;
}

function is_utf8($str)
{
  if (strlen($str) == 0)
  {
    return TRUE;
  }
  return (preg_match('/^./us', $str) == 1);
}

function check_str($str)
{
  return is_utf8($str) ? htmlspecialchars($str, ENT_QUOTES) : '';
}

if ( ! function_exists( 'exif_imagetype'))
{
	function exif_imagetype($filename)
	{
		if ((list($width, $height, $type, $attr) = getimagesize($filename )) !== false)
		{
			return $type;
		}
		return false;
	}
}

function check_length($value = '', $min, $max)
{
	$result = (mb_strlen($value) < $min OR mb_strlen($value) > $max);
	return ! $result;
}

/**
 * Crosslinking the formation of a URL
 *
 * @param   $seo['link'] string to check
 * @return  string clearing url
 */
function seo_link($url)
{
	$parse = parse_url($url);
	if (
		! array_key_exists('scheme', $parse) OR
		! in_array($parse['scheme'], array('http', 'https', 'ftp'))
	) {
		$uri = str_replace(SITE_HOST, '', $url);
		if (SITE_URL.'/'.ltrim($uri, '/') <> SITE_URL.'/'.ltrim(REQUEST_URI, '/'))
		{
			return SITE_URL.'/'.trim($uri, '/');
		}
		return NULL;
	}
	else
	{
		return $url;
	}
}

/**
 * Get the ID mods according to the name
 *
 * @param   string name mod
 * @return  string id mod or false
 */
function id_mod($name)
{
	global $config;

	return isset($config['mod'][$name]) ? intval($config['mod'][$name]['id']): FALSE;
}

/**
 * Recursively sanitizes an input variable
 *
 * Strips slashes if magic quotes are enabled
 * Normalizes all newlines to LF
 */
function filter_sanitize($value)
{
	$magic_quotes = (bool)get_magic_quotes_gpc();
	if (is_array($value) OR is_object($value))
	{
		foreach ($value as $key => $val)
		{
			$value[$key] = filter_sanitize($val);
		}
	}
	elseif (is_string($value))
	{
		if ($magic_quotes === TRUE)
		{
			$value = stripslashes($value);
		}
		if (strpos($value, "\r") !== FALSE)
		{
			$value = str_replace(array("\r\n", "\r"), "\n", $value);
		}
	}
	return $value;
}

/**
 * Multy-Bite function
 */
if ( ! function_exists('mb_strlen')) {
	function mb_strlen($str)
	{
		$result = strlen(iconv('UTF-8', 'Windows-1251', $str));
		return (int)$result;
	}
}

if ( ! function_exists('mb_strtolower')) {
	function mb_strtolower($str)
	{
		$str = iconv('UTF-8', 'Windows-1251', $str);
		$str = strtolower($str);
		return iconv('Windows-1251', 'UTF-8', $str);
	}
}

if ( ! function_exists('mb_strtoupper')) {
	function mb_strtoupper($str)
	{
		$str = iconv('UTF-8', 'Windows-1251', $str);
		$str = strtoupper($str);
		return iconv('Windows-1251', 'UTF-8', $str);
	}
}

if ( ! function_exists('mb_str_replace')) {
	function mb_str_replace($needle, $replacement, $haystack)
	{
		return implode($replacement, mb_split($needle, $haystack));
	}
}

/**
 * Global Site Menu
 * Template for menu display in a tree
 * ------------------------------------ */

	/**
	 * To compile the menu
	 */
	function comp_menu($array)
	{
		global $ro, $config;

		$link = ltrim($array['link'], '/');
		$parse = parse_url($link);

		$path = isset($parse['path']) ? $parse['path'] : '';
		$frag = isset($parse['fragment']) ? '#'.$parse['fragment'] : '';
		$uri = isset($parse['query']) ? $path.'?'.$parse['query'] : $path;

		$url = isset($parse['scheme']) ? $link : $ro->seo($uri).$frag;

		$current = ($ro->seo($link) == REQUEST_URI) ? 1 : 0;

		$arrow = (isset($array['sub'])) ? 'arrow' : '';
		$css = ($array['css']) ? ' '.$array['css'] : '';
		$class = ($current) ? 'active '.$arrow.$css : $arrow.$css;
		$title = ( ! empty($array['title'])) ? ' title="'.$array['title'].'"' : '';
		$target = ($array['target'] == '_blank') ? ' target="_blank"' : '';
		$icon = ( ! empty($array['icon'])) ? '<img src="'.SITE_URL.'/'.$array['icon'].'" alt="'.$array['title'].'" />' : '';

		$tag = ( ! empty($config['tag_menu'])) ? $config['tag_menu'] : 'strong';
		$act = '<'.$tag.' class="'.$class.'">'.$icon.$array['name'].'</'.$tag.'>';
		$url = '<a'.(( ! empty($class)) ? ' class="'.$class.'"' : '').' href="'.$url.'"'.$title.$target.'>'.$icon.$array['name'].'</a>';

		$item = ($current AND $config['act_menu'] == 'tag') ? $act : $url;

		// Levels
		$out = '<li>'.$item;
		if (isset($array['sub']))
		{
			$out.= '<ul>'.do_menu($array['sub']).'</ul>';
		}
		$out.= '</li>';

		return $out;
	}

	/**
	 * Recursively read the template
	 */
	function do_menu($array)
	{
		$out = null;
		foreach($array as $v) {
			$out.= comp_menu($v);
		}
		return $out;
	}

	/**
	 * Insert the global template
	 */
	function print_menu($array)
	{
		global $ro, $api, $global;

		if ( ! is_array($array))
			return FALSE;

		foreach ($array as $m)
		{
			// Next Menu. Markup
			$print = '<ul class="'.$m['css'].'">';
			if (isset($m['sub'])) {
				$print.= do_menu($m['sub']);
			}
			$print.= '</ul>';

			// Insert on template
			$global['insert'][$m['code']] = $api->siteuni($print);
		}
	}

/*
 * END / Global Site Menu
 * ----------------------- */
