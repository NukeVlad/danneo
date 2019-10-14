<?php
/**
 * File:        /admin/login.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
define('ADMREAD', 1);
ini_set('display_errors', 0);

if (file_exists(__DIR__.'/../cache/lang/login.php'))
{
	include __DIR__.'/../cache/lang/login.php';
}

if ( ! defined('CACHELOGIN') )
{
	include __DIR__.'/lang/login.php';
	include __DIR__.'/core/permission.php';
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

$error = null;
$opsss = ( ! empty($_GET['opsss']) ) ? intval($_GET['opsss']) : '';

if ( stristr(USER_AGENT, 'MSIE 6.0') OR stristr(USER_AGENT, 'MSIE 7.0') OR stristr(USER_AGENT, 'MSIE 8.0') !== FALSE) 
{
	$opsss = 4;
}

echo '
<!DOCTYPE html>
<html lang="'.CODE_DEF.'">
<head>
<meta charset="'.CHAR_DEF.'">
<title>'.$lang['adm_panel'].'</title>
<link rel="stylesheet" href="template/skin/'.SKIN_DEF.'/css/login.css?v.'.VERSION.'">
</head>
<body>
<noscript>'.$lang['adm_noscript'].'</noscript>
<script>if (!window.navigator.cookieEnabled) {document.write("<div class=\'bad-cookie\'>'.$lang['adm_bad_cookie'].'</div>");}</script>
<div class="core">
<div class="content">
	<h1>'.$lang['adm_panel'].'</h1>
	<form action="./index.php" method="post">
		<label for="login">'.$lang['adm_login'].'</label>
		<input type="text" name="adlog" id="login" maxlength="15" required="required" autofocus="autofocus" /><br />
		<label for="password">'.$lang['adm_passw'].'</label>
		<input type="password" name="adpwd" id="password" maxlength="15" required="required" /><br />
		<label></label>
		<input class="blogin" type="submit" value="'.$lang['adm_enter'].'" />
	</form>'.PHP_EOL;

if ($opsss == 1)
	$error.= '<li>'.$lang['adm_auth_error'].'</li>';
elseif ($opsss == 2)
	$error.= '<li>'.str_replace('{lifeadmin}', LIFE_ADMIN, $lang['adm_sess_out']).'</li>';
elseif ($opsss == 3)
	$error.= '<li>'.$lang['adm_non_cookie'].'</li>';
elseif ($opsss == 4)
	$error.= '<li>'.$lang['adm_bad_agent'].'</li>';

if ($error) {
	echo '
	<ul>'.$error.'</ul>';
}
echo '
</div>
</div>
<div class="powered"><a href="http://danneo.ru" target="_blank">DANNEO CMS</a> '.VERSION.' <i>©</i> 2005 - '.date('Y').'</div>
</body>
</html>';
exit();
