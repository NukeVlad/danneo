<?php
/**
 * File:        /admin/core/track.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * HTTP_USER_AGENT
 */
if ($_SERVER['HTTP_USER_AGENT'] == '-' OR empty($_SERVER['HTTP_USER_AGENT'])) {
	die('Bad metods!');
}

/**
 * TRACE
 */
if ($_SERVER['REQUEST_METHOD']=='TRACE') {
	die('Bad metods!');
}

/**
 * GLOBALS
 */
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	die('Bad metods!');
}
if (!is_array($GLOBALS)) {
	die('Bad metods!');
}

/**
 * REQUEST
 */
if( ! isset($_REQUEST)) return;

/**
 * badops
 */
$badops = array(
			'UNION',
			'OUTFILE',
			'FROM',
			'CREATE',
			// 'SELECT',
			'WHERE',
			'SHUTDOWN',
			'UPDATE',
			'DELETE',
			'CHANGE',
			'MODIFY',
			'RENAME',
			'RELOAD',
			'ALTER',
			'GRANT',
			'DROP',
			'INSERT',
			'CONCAT',
			'cmd',
			'exec',
			//'--'
			);

/**
 * foreach REQUEST
 */
foreach ($_REQUEST as $params => $inputdata)
{
	foreach ($badops as $opskey => $opsvalue)
	{
		if (is_string($inputdata) AND preg_match('/^'.$opsvalue.'/i',$inputdata))
		{
			$cleardata = preg_replace('/^'.$opsvalue.'/i','',$inputdata);
			$GLOBALS[$params] = $cleardata;
		}
	}
}
