<?php
/**
 * File:        /core/includes/track.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $config, $lang, $api, $ajax;

/**
 * AJAX
 */
$ajax = ($config['ajax'] == 'yes') ? 1 : 0;

/**
 * HTTP_USER_AGENT
 */
if ($_SERVER['HTTP_USER_AGENT'] == '-' OR empty($_SERVER['HTTP_USER_AGENT'])) {
	die('Bad metods!');
}

/**
 * TRACE
 */
if ($_SERVER['REQUEST_METHOD'] == 'TRACE') {
	die('Bad metods!');
}

/**
 * GLOBALS
 */
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	die('Bad metods!');
}
if ( ! is_array($GLOBALS)) {
	die('Bad metods!');
}

/**
 * REQUEST
 */
if ( ! isset($_REQUEST)) {
	return;
}

/**
 * badcount
 */
$bad_get = $bad_post = 0;

/**
 * badget
 */
$badget = array
			(
				"UNION", "OUTFILE", "FROM", "CREATE", "SELECT", "WHERE",
				"SHUTDOWN", "UPDATE", "DELETE", "CHANGE", "MODIFY", "RENAME",
				"RELOAD", "ALTER", "GRANT", "DROP", "INSERT", "CONCAT", "cmd", "exec",
				"\([^>]*\"?[^)]*\)",
				"<[^>]*body*\"?[^>]*>",
				"<[^>]*script*\"?[^>]*>",
				"<[^>]*object*\"?[^>]*>",
				"<[^>]*iframe*\"?[^>]*>",
				"<[^>]*img*\"?[^>]*>",
				"<[^>]*frame*\"?[^>]*>",
				"<[^>]*applet*\"?[^>]*>",
				"<[^>]*meta*\"?[^>]*>",
				"<[^>]*style*\"?[^>]*>",
				"<[^>]*form*\"?[^>]*>",
				"<[^>]*div*\"?[^>]*>"
		);

/**
 * badpost
 */
$badpost = array
			(
				"<[^>]*body*\"?[^>]*>",
				"<[^>]*script*\"?[^>]*>",
				"<[^>]*object*\"?[^>]*>",
				"<[^>]*iframe*\"?[^>]*>",
				"<[^>]*img*\"?[^>]*>",
				"<[^>]*frame*\"?[^>]*>",
				"<[^>]*applet*\"?[^>]*>",
				"<[^>]*meta*\"?[^>]*>",
				"<[^>]*style*\"?[^>]*>",
				"<[^>]*form*\"?[^>]*>"
			);

$bad_tags = '';
/**
 * foreach GET
 */
foreach ($_GET as $params => $inputdata)
{
	for ($i = 0; $i < sizeof($badget); $i++)
	{
		if (is_string($inputdata) AND preg_match_all('/'.$badget[$i].'/i',$inputdata, $out, PREG_SET_ORDER))
		{
			$bad_get = 1;
			$bad_tags.= '<p><mark>'.preg_replace('/'.$badget[$i].'/i', '\0', htmlentities($out[0][0], ENT_QUOTES, $config['langcharset'])).'</mark></p>';
		}
	}
}

/**
 * foreach POST
 */
foreach ($_POST as $params => $inputdata)
{
	for ($i = 0; $i < sizeof($badpost); $i++)
	{
		if (is_string($inputdata) AND preg_match_all('/'.$badpost[$i].'/i',$inputdata, $out, PREG_SET_ORDER))
		{
			$bad_post = 1;
			$bad_tags.= '<p><mark>'.preg_replace('/'.$badpost[$i].'/i', '\0', htmlentities($out[0][0], ENT_QUOTES, $config['langcharset'])).'</mark></p>';
		}
	}
}

/**
 * Вывод
 */
if ($bad_get == 1)
{
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Content-Type: text/html; charset=".$config['langcharset']."");

	echo '	<!DOCTYPE html>
			<html>
			<head>
			<meta charset='.$config['langcharset'].'">
			<title>You use the forbidden tags!</title>
			<link rel="stylesheet" href="'.$config['site_url'].'/template/'.$config['site_temp'].'/css/go.css" />
			</head>
			<body>
				<div>
					<p class="err">'.$lang['bad_tags'].'</p>
				</div>
			</body>
			</html>';
		exit();
}
if ($bad_post == 1)
{
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Content-Type: text/html; charset=".$config['langcharset']."");

	if ($ajax == 0)
	{
		echo '	<!DOCTYPE html>
				<html>
				<head>
				<meta charset='.$config['langcharset'].'">
				<title>You use the forbidden tags!</title>
				<link rel="stylesheet" href="'.$config['site_url'].'/template/'.$config['site_temp'].'/css/go.css" />
				</head>
				<body>
					<div>
						<p class="err">'.$lang['bad_tags'].'</p>
						<p>'.$bad_tags.'</p>
						<button onclick="history.back();return false;" class="sub go">'.$lang['all_goback'].'</button>
					</div>
				</body>
				</html>';
		exit();
	}
	else
	{
		die('
		<div class="error-box">
			<div>'.$lang['bad_tags'].' <p>'.$bad_tags.'</p></div>
		</div>
		');
	}
}
