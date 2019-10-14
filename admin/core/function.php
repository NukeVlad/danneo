<?php
/**
 * File:        /admin/core/function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Function extract globals
 */
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
{
	echo "Global variable overload attack detected!\n";
	exit(1);
}
$global_var = array(
	'_COOKIE',
	'_ENV',
	'_GET',
	'_FILES',
	'_POST',
	'_REQUEST',
	'_SERVER',
	'_SESSION',
	'GLOBALS',
);
foreach($global_var as $name)
{
	unset($_COOKIE[$name]);
	unset($_POST[$name]);
	unset($_GET[$name]);
	unset($_REQUEST[$name]);
}
if ( ! ini_get('register_globals'))
{
	foreach($_REQUEST as $k => $v)
	{
		if ( ! isset($array[$k]))
		{
			$GLOBALS[$k] = $v;
		}
	}
}

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

	$uct = (isset($tz[$conf['timezone']])) ? $tz[$conf['timezone']] : $tz[3];

	date_default_timezone_set($uct);
}

/**
 * Константы
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
define('THIS_CPU', 10);
define('THIS_GROUP_EMPTY', 11);
define('THIS_NUM_COM', 12);
define('NEWDATE', date('d-m-Y'));
define('FLODATE', date('d.m.Y'));
define('NEWDAY', date('d'));
define('NEWMONT', date('m'));
define('NEWYEAR', date('Y'));
define('NEWTIME', time());
define('TODAY', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
define('ACOOKIE', 'dn_'.$conf['acookname']);
define('SCOOKIE', 'dn_skin'.$conf['acookname']);
define('PCOOKIE', 'dn_platform'.$conf['acookname']); // Не менять 'dn_platform'!
define('PCLONE', 'dn_pclone'.$conf['acookname']);
define('WCOOKIE', 'wysiwyg');

/**
 * Парсинг
 */
function preparse($resursing, $type, $clear = FALSE, $len = FALSE)
{
	if ($clear == 1) {
		$resursing = trim(strip_tags($resursing));
	}
	if ($len == TRUE AND $len > 0) {
		$resursing = mb_substr($resursing, 0, $len);
	}
	if ($type == THIS_INT) {
		return $resursing = (intval($resursing) < 0) ? 0 : intval($resursing);
	}
	if ($type == THIS_STR) {
		$resursing = trim(str_replace(array(
				'%09', '%20', '%22', '%2E', '%3E',')',
				'%3C', '%25', ':', '/', '@', '"', ' ', '(',
				'-', '*', '..', "'", '.', ';', '\\',
				'https', 'http', 'ftp'
			), '', $resursing)
		);
		return $resursing;
	}
	if ($type == THIS_NUM_COM) {
		$resursing = preg_replace('/[^0-9\,]/', '', $resursing);
		$resursing = trim($resursing, ',');
		$resursing = str_replace(' ', '', $resursing);
		return $resursing;
	}
	if ($type == THIS_MD_5) {
		return $resursing = md5($resursing);
	}
	if ($type == THIS_ADD_SLASH) {
		return $resursing;
	}
	if ($type == THIS_STRLEN) {
		return $resursing = mb_strlen($resursing);
	}
	if ($type == THIS_TRIM) {
		return $resursing = trim($resursing);
	}
	if ($type == THIS_ARRAY) {
		return $resursing = (is_array($resursing)) ? 1 : 0;
	}
	if ($type == THIS_EMPTY) {
		return $resursing = (empty($resursing) AND $resursing !== '0' AND $resursing !== 0) ? 1 : 0;
	}
	if ($type == THIS_GROUP_EMPTY) {
		return $resursing = (array_search('', $resursing)) ? 1 : 0;
	}
	if ($type == THIS_SYMNUM) {
		return $resursing = (preg_match('/^[a-zA-Z0-9_]+$/D', $resursing)) ? 0 : 1;
	}
	if ($type == THIS_CPU) {
		return $resursing = (preg_match('/^[a-zA-Z0-9_-]+$/D', $resursing)) ? 0 : 1;
	}
}

function preparse_dn($resursing)
{
	if (preg_match("[^a-zA-Z0-9_]", $resursing)) {
		$resursing = '';
	}
	return mb_substr($resursing, 0, 20);
}

function preparse_sp($resursing)
{
	global $conf;
	return htmlspecialchars($resursing, ENT_QUOTES, $conf['langcharset']);
}

function preparse_un($resursing)
{
	return $resursing;
}

function preparse_html($resursing)
{
	global $conf;
	return htmlspecialchars(str_replace(array('\\', '\"', "\'",), array('', '"', "'"), $resursing), ENT_QUOTES, $conf['langcharset']);
}

function preparse_unhtml($resursing)
{
	global $conf;
	return htmlentities(str_replace(array('\\', '\"', "\'",), array('', '"', "'"), $resursing), ENT_QUOTES, $conf['langcharset']);
}

function preparse_dp($resursing)
{
	return str_replace(array('&lt;', '&gt;', '&quot;', '&#039;', '&amp;', '<', '>', '"', "'", '&'), '', $resursing);
}

function preparse_lga($resursing)
{
    global $conf;
    return str_replace(array("'", "\r\n"),array('&#039;', ''), $resursing);
}

/**
 * Браузер и IP адрес
 */
if (isset($_SERVER['REMOTE_ADDR']))  {
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
} elseif ( isset($HTTP_SERVER_VARS['REMOTE_ADDR']) ) {
	$REMOTE_ADDR = $HTTP_SERVER_VARS['REMOTE_ADDR'];
} elseif (getenv('REMOTE_ADDR')) {
	$REMOTE_ADDR = getenv('REMOTE_ADDR');
} else {
	$REMOTE_ADDR = '';
}
if ($REMOTE_ADDR) {
	if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $REMOTE_ADDR, $ipm)) {
		$private = array("/^0\./", "/^127\.0\.0\.1/", "/^192\.168\..*/", "/^172\.16\..*/", "/^10..*/", "/^224..*/", "/^240..*/");
		$REMOTE_ADDR = preg_replace($private, $REMOTE_ADDR, $ipm[1]);
	}
}
if (preparse($REMOTE_ADDR, THIS_STRLEN) > 16) {
	$REMOTE_ADDR = preparse($REMOTE_ADDR, THIS_TRIM, 0, 16);
}
if ( ! empty($REMOTE_ADDR)) {
	define('REMOTE_ADDRS',$REMOTE_ADDR);
}
define('THIS_REALIP', preparse($REMOTE_ADDR, THIS_ADD_SLASH));

/**
 * Define SITE_URL
 */
if (isset($conf['site_url']) AND ! empty($conf['site_url']))
{
	if ( ! defined('SITE_URL') )
	{
		define('SITE_URL', $conf['site_url']);
	}
}
else
{
	if (isset($_SERVER['HTTP_HOST']))
	{
		$site_url = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : NULL;
		$site_url = (($site_url) AND ($site_url != 'off')) ? 'https' : 'http';
		$site_url = $site_url.'://'.$_SERVER['HTTP_HOST'];
	}
	else
	{
		$site_url = 'http://localhost';
	}

	$site_url = rtrim($site_url, '/');

	if ( ! defined('SITE_URL') )
	{
		define('SITE_URL', $site_url);
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
if ($conf['cpu'] == 'yes')
{
	if ( ! defined('SEOURL') )
	{
		define('SEOURL', TRUE);
	}
}

/**
 * Define SITE_HOST_URL
 */
$array_url = parse_url(SITE_URL);
$host_url = $array_url['scheme'].'://'.$array_url['host'];
if ( ! defined('SITE_HOST_URL') )
{
	define('SITE_HOST_URL', $host_url);
}

/**
 * Define SUF
 */
if (isset($conf['suffix']) AND $conf['suffix'] == 'yes')
{
	if ( ! defined('SUF') )
	{
		define('SUF', '.html');
	}
}
else
{
	if ( ! defined('SUF') )
	{
		define('SUF', '');
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
 * Редирект
 */
function redirect($url)
{
	$url = str_replace('&amp;', '&', $url);
	header('Location:'.$url);
	exit();
}

/**
 * Форматирование даты и времени
 */
function format_time($gtm, $time = FALSE, $month = FALSE)
{
	global $conf, $langdate;

	$date = new DateTime();
	$date->setTimestamp($gtm);

	$outdate = $date->format($conf['formatdate']);
	$outtime = $date->format($conf['formattime']);

	if (is_array($langdate))
	{
		$outdate = ($month) ? month_bias($outdate) : $outdate;
		$outdate = strtr(strtolower($outdate), $langdate);
	}

	if ($time)
	{
		$outtime = '&nbsp; '.$outtime;
		return $outdate.$outtime;
	}
	else
	{
		return $outdate;
	}
}

function month_bias($month)
{
	$month_in = array
		(
			"january"   => 'mont_1',
			"february"  => 'mont_2',
			"march"     => 'mont_3',
			"april"     => 'mont_4',
			"may"       => 'mont_5',
			"june"      => 'mont_6',
			"july"      => 'mont_7',
			"august"    => 'mont_8',
			"september" => 'mont_9',
			"october"   => 'mont_10',
			"november"  => 'mont_11',
			"december"  => 'mont_12',
		);

		return strtr(strtolower($month), $month_in);
}

function Calendar($id, $field)
{
	global $sess;
	echo '	<div class="calendar">
				<img id="'.$id.'" class="pointer" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/calendar.png" alt="" />
				<script>
				Calendar.setup({
					inputField : "'.$field.'",
					trigger    : "'.$id.'",
					onSelect   : function() { this.hide() },
					showTime   : 24,
					dateFormat : "%d.%m.%Y %H:%M"
				});
				</script>
			</div>';
}

function CalendarFormat($time)
{
	global $conf;

	$date = new DateTime();
	$date->setTimestamp($time);

	return $date->format('d.m.Y H:i');
}

function ReDate($re)
{
	global $conf;

	if ($re == 0 OR empty($re))
	{
		return FALSE;
	}

	$re = str_replace(array('  ', '%20%20'), ' ', $re);
	$work = explode(' ', $re);

	$stamp = TODAY;
	if (isset($work[0]) AND isset($work[1]))
	{
		$date = explode('.', $work[0]);
		$time = explode(':', $work[1]);
		if ($date[0] AND $date[1] AND $date[2] AND $time[0] AND $time[1])
		{
			$date = new DateTime($work[0].' '.$work[1]);
			$stamp = $date->getTimestamp();
		}
	}

	return $stamp;
}

/**
 * Размер базы данных
 */
function databasesize()
{
	global $db,$namebase;

	$res = $db->query("SHOW TABLE STATUS FROM `".$namebase."`");
	if ($res)
	{
		while ($row = $db->fetchrow($res)) {
			$all_db_table[] = $row;
		}
		$dbsize = 0;
		for ($i=0; $i < count($all_db_table); $i++) {
			$dbsize += $all_db_table[$i]['Data_length'] + $all_db_table[$i]['Index_length'];
		}
		$mb = 1024 * 1024;
		if ($dbsize > $mb) {
			$dbsize = sprintf ('%01.2f', $dbsize / $mb).' Mb';
		} elseif ($dbsize >= 1024) {
			$dbsize = sprintf('%01.2f', $dbsize / 1024)." Kb";
		} else {
			$dbsize = $dbsize.' Byte';
		}
	} else {
		$dbsize = 'Unable to get the size of the database!';
	}
	return $dbsize;
}

/**
 * Версия базы данных
 */
function databaseversion()
{
	global $db;

	list($version) = $db->fetchrow($db->query("SELECT VERSION()"));
	return $version;
}

/**
 * Размер удалённого файла
 */
function outfilesize($newfile)
{
	$out = filesize($newfile);
	$mb = 1024*1024;
	if ($out > $mb) {
		$fs = sprintf('%01.2f', $out / $mb).' Mb';
	} elseif ($out >= 1024) {
		$fs = sprintf('%01.2f', $out / 1024).' Kb';
	} else {
		$fs = $out.' Byte';
	}
	return $fs;
}

/**
 * Размер удалённого файла числом
 */
function numfilesize($newfile)
{
	$out = filesize($newfile);
	$mb = 1024*1024;
	if ($out > $mb) {
		$fs = sprintf('%01.2f', $out / $mb);
	} elseif ($out >= 1024) {
		$fs = sprintf('%01.2f', $out / 1024);
	} else {
		$fs = $out.' Byte';
	}
	return $fs;
}

/**
 * Размер из строки
 */
function size($num)
{
	$len = strlen ($num);

	if ($len < 4)
		return sprintf("%d b", $num);
	if ($len>= 4 && $len <=6)
		return sprintf("%0.2f Kb", $num / 1024);
	if ($len>= 7 && $len <=9)
		return sprintf("%0.2f Mb", $num / 1024 / 1024);

		return sprintf("%0.2f Gb", $num / 1024 / 1024 / 1024);
}

function this_selectcat($cid = 0, $depth = 0)
{
	global $catcache, $selective, $catid, $selected, $links;

	if ( ! isset($catcache[$cid]))
	{
		return FALSE;
	}

	$catcount = 0;
	foreach ($catcache[$cid] as $key => $incat)
	{
		$catcount ++;
		if ($incat['catid'] == $catid) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		if ($depth == 0) {
			$indent = '';
			$style = ' class="selective"';
		} else {
			$indent = str_repeat("&nbsp;&nbsp;", $depth);
			$style = '';
		}
		$selective.= '<option value="'.$links.$incat['catid'].'"'.$style.$selected.'>'.$indent.preparse_un($incat['catname']).'</option>';
		this_selectcat($incat['catid'], $depth + 1);
	}

	unset($catcache[$cid]);
	return;
}

function print_cat($cid = 0, $depth = 0, $table = '', $self = 'index', $last = FALSE, $prefix = FALSE)
{
	global $basepref, $db, $catcache, $sess, $lang, $conf;

	if ( ! isset($catcache[$cid]))
	{
		return FALSE;
	}

	// Группы в массив
	$groups_only = array();
	if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
	{
		$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
		while ($items = $db->fetchrow($inqs)) {
			$groups_only[] =  $items['title'];
		}
	}

	foreach ($catcache[$cid] as $key => $incat)
	{
		if ($incat['parentid'] == 0) {
			$bg = 'main';
			$font = 'site bold';
			$mess = ' title="'.$lang['home'].'"';
		}  else {
			$bg = 'parent';
			$font = 'site';
			$mess = '';
		}
		if(empty($last) AND $incat['parentid'] > 0){
			$last = $incat['parentid'];
		}
		if($last > 0 AND $last == $incat['parentid']){
			$bg = 'parent';
			$font = 'site';
		}

		// Ассоциируем группы
		$groupact = NULL;
		if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
		{
			if ( ! empty($incat['groups']))
			{
				$groups = Json::decode($incat['groups']);
				reset($groups);
				foreach ($groups as $key => $val)
				{
					$groupact.=  ' '.$groups_only[$key - 1].',';
				}
				$groupact = chop($groupact, ',');
			}
		}

		echo '	<tr class="list">
					<td class="ac">'.$incat['catid'].'</td>
					<td class="al pw25">
						<span class="'.$font.'"'.$mess.'>'.$prefix.preparse_un($incat['catname']).'</span>
					</td>
					<td>
						'.(($incat['access'] == 'user') ? ( ! empty($incat['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']).'
					</td>
					<td>'.$incat['total'].'</td>
					<td>';
		if( ! empty($incat['icon'])) {
			echo '		<img src="'.WORKURL.'/'.$incat['icon'].'" alt="'.preparse_un($incat['catname']).'" style="max-width: 36px; max-height: 27px; " />';
		}
		echo '		</td>
					<td>
						<input type="text" value="'.$incat['posit'].'" name="posit['.$incat['catid'].']" size="3" maxlength="3">
					</td>
					<td class="gov">
						<a href="'.$self.'.php?dn='.$table.'catedit&amp;catid='.$incat['catid'].(defined('THISPL') ? '&amp;pl='.THISPL : '').'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png"></a>
						<a href="'.$self.'.php?dn='.$table.'catadd&amp;catid='.$incat['catid'].(defined('THISPL') ? '&amp;pl='.THISPL : '').'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_add_sub'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png"></a>
						<a href="'.$self.'.php?dn='.$table.'catdel&amp;catid='.$incat['catid'].(defined('THISPL') ? '&amp;pl='.THISPL : '').'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png"></a>
					</td>
				</tr>';
		print_cat($incat['catid'], $depth + 1, $table, $self, $last, $prefix.' - ');
	}
	unset($catcache[$cid]);
	return;
}

function this_delcat($catid, $table)
{
	global $basepref, $db;

	$catid = intval($catid);
	$inq = $db->numrows($db->query("SELECT catid FROM ".$basepref."_".$table."_cat WHERE parentid = '".$catid."'"));
	if ($inq > 0)
	{
		$inquiry = $db->query("SELECT * FROM ".$basepref."_".$table."_cat WHERE parentid = '".$catid."'");
		while ($row = $db->fetchrow($inquiry))
		{
			$catid_del = $row[0];
			if (this_delcat($catid_del, $table) != 0)
			{
				$db->query("DELETE FROM ".$basepref."_".$table." WHERE catid = '".$catid_del."'");
				$db->query("DELETE FROM ".$basepref."_".$table."_cat WHERE catid = '".$catid_del."'");
			}
		}
	} else {
		$db->query("DELETE FROM ".$basepref."_".$table." WHERE catid = '".$catid."'");
		$db->query("DELETE FROM ".$basepref."_".$table."_cat WHERE catid = '".$catid."'");
	}
	return $inq;
}

function this_councat($catid, $parent, $table)
{
	global $basepref, $db;

	static $isset = 0;
	$inq = $db->numrows($db->query("SELECT catid FROM ".$basepref."_".$table."_cat WHERE parentid = '".intval($catid)."' ORDER BY posit ASC"));
	if ($inq > 0)
	{
		$inquiry = $db->query("SELECT catid FROM ".$basepref."_".$table."_cat WHERE parentid = '".intval($catid)."'");
		while ($row = $db->fetchrow($inquiry))
		{
			if ($row['catid'] != $parent) {
				this_councat($row['catid'],$parent,$table);
			} else {
				$isset = 1;
			}
		}
	}
	return $isset;
}

function this_catup($posit, $table)
{
	global $basepref, $db;

	foreach ($posit as $id => $val)
	{
		$db->query("UPDATE ".$basepref."_".$table."_cat SET posit = '".intval($val)."' WHERE catid = '".intval($id)."'");
	}
}

function this_tagup($rating, $table)
{
	global $basepref, $db;

	foreach ($rating as $id => $val)
	{
		$rateval = ($val > 100) ? 100 : $val;
		$db->query("UPDATE ".$basepref."_".$table."_tag SET tagrating = '".intval($rateval)."' WHERE tagid = '".intval($id)."'");
	}
}

function outfile ($file)
{
	$file = explode('.', $file);
	$ext = $file[(count($file)-1)];
	unset($file[(count($file)-1)]);
	$name = implode('.', $file);
	$outfile['ext'] = $ext;
	$outfile['body'] = $name;
	return $outfile;
}

function amount_pages($link, $num, $ajax = FALSE)
{
	global $conf;

	$point = array();
	foreach ($conf['num'] as $key => $val)
	{
		if ($num == $val)
		{
			$point[] = '<span class="pages">'.$val.'</span>';
		}
		else
		{
			if ($ajax == 1) {
				$point[] = '<a class="pages" href="javascript:$.ajaxget(\''.$link.'&amp;nu='.$val.'\');">'.$val.'</a>';
			} else {
				$point[] = '<a class="pages" href="'.$link.'&amp;nu='.$val.'">'.$val.'</a>';
			}
		}
	}
	return implode('', $point);
}

function adm_pages($table, $id, $page, $func, $num, $p, $sess, $ajax = FALSE, $rows = FALSE)
{
	global $db, $basepref;

	$wp = array();
	if ($rows == 0) {
		$item_num = $db->fetchrow($db->query("SELECT COUNT(".$id.") AS total FROM ".$basepref."_".$table.""));
	} else {
		$item_num['total'] = $rows;
	}
	$nums = ceil($item_num['total'] / $num);
	if ($nums <= 1) {
		$wp[] = '<span class="pages">1</span>';
	} else {
		if ($p > 1)
		{
			$goback = $p - 1;
			if ($ajax == 1) {
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p=1&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/laquo.png" /></a>';
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$goback.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/lt.png" /></a>';
			} else {
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p=1&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/laquo.png" /></a>';
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$goback.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/lt.png" /></a>';
			}
		}
		for ($i = 1; $i < $nums + 1; $i++)
		{
			if ($i == $p) {
				$wp[] = '<span class="pages">'.$i.'</span>';
			} else {
				if (($i > $p) AND ($i < $p + 5) OR ($i < $p) AND ($i > $p - 5))
				{
					if ($ajax == 1) {
						$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$i.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');">'.$i.'</a>';
					} else {
						$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$i.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'">'.$i.'</a>';
					}
				}
			}
		}
		if ($p < $nums)
		{
			$gonext = $p + 1;
			if ($ajax == 1) {
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$gonext.'&amp;nu=='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/gt.png" /></a>';
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$nums.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/raquo.png" /></a>';
			} else {
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$gonext.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/gt.png" /></a>';
			$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$nums.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/raquo.png" /></a>';
			}
		}
	}
	return implode('', $wp);
}

function user_pages($table, $id, $page, $func, $num, $p, $sess, $ajax = FALSE)
{
	global $db,$basepref;

	$wp = array();
	$item_num = $db->fetchrow($db->query("SELECT COUNT(".$id.") AS total FROM ".$table.""));
	$nums = ceil($item_num['total'] / $num);
	if ($nums <= 1) {
		$wp[] = '<span class="pages">1</span>';
	} else {
		if ($p > 1)
		{
			$goback = $p - 1;
			if ($ajax == 1) {
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p=1&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/laquo.png" /></a>';
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$goback.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/lt.png" /></a>';
			} else {
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p=1&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/laquo.png" /></a>';
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$goback.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/lt.png" /></a>';
			}
		}
		for ($i = 1; $i < $nums + 1; $i++)
		{
			if ($i == $p)
			{
				$wp[] = '<span class="pages">'.$i.'</span>';
			} else {
				if (($i > $p) AND ($i < $p + 5) OR ($i < $p) AND ($i > $p - 5))
				{
					if ($ajax == 1) {
						$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$i.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');">'.$i.'</a>';
					} else {
						$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$i.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'">'.$i.'</a>';
					}
				}
			}
		}
		if ($p < $nums)
		{
			$gonext = $p + 1;
			if ($ajax == 1) {
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$gonext.'&amp;nu=='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/gt.png" /></a>';
				$wp[] = '<a class="pages" href="javascript:$.ajaxget(\''.$page.'.php?dn='.$func.'&amp;p='.$nums.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'\');"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/raquo.png" /></a>';
			} else {
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$gonext.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/gt.png" /></a>';
				$wp[] = '<a class="pages" href="'.$page.'.php?dn='.$func.'&amp;p='.$nums.'&amp;nu='.$num.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/raquo.png" /></a>';
			}
		}
	}
	return implode('', $wp);

}

/**
 * SEA PAGES
 */
function sea_pages($table, $id, $num, $p)
{
	global $db, $basepref;

	$write_pages = array();
	$item_num = $db->fetchrow($db->query("SELECT COUNT(".$id.") AS total FROM ".$basepref."_".$table.""));
	$nums = ceil($item_num['total']/$num);

	if ($nums <= 1){
		$write_pages[] = ('<span class="pages">1</span>');
	} else {
		if ($p > 1) {
			$write_pages[] = ('<input class="pagebut lt" name="prev" value="«" type="submit">');
		}
		if ($p > 5) {
			$write_pages[] = ('<input class="movebut" name="p" value="1" type="submit"><span class="gap">&hellip;</span>');
		}
	}

	for($i = 1; $i < $nums + 1; $i ++)
	{
		if ($i==$p) {
			$write_pages[] = ('<input class="nopagebut" name="p" value="'.$i.'" disabled="disabled" type="submit">');
		} else {
			if (($i > $p) AND ($i < $p + 5) OR ($i < $p) AND ($i > $p -5)) {
				$write_pages[] = ('<input class="pagebut" name="p" value="'.$i.'" type="submit">');
			}
		}
	}
	if ($p < $nums) {
		if($p < ($nums - 5)) {
			$write_pages[] = ('<span class="gap">&hellip;</span><input class="movebut" name="p" value="'.$nums.'" type="submit">');
		} else {
			$write_pages[] = ('<input class="pagebut gt" name="next" value="»" type="submit">');
		}
	}
	return implode('', $write_pages);
}

/**
 * Verify pwd
 */
function verify_pwd($pwd)
{
	global $conf;
	return ((preparse($pwd, THIS_STRLEN) < $conf['user']['minpass']) OR (preparse($pwd, THIS_STRLEN) > $conf['user']['maxpass'])) ? 0 : 1;
}

/**
 * Verify Name
 */
function verify_name($name)
{
	global $conf;
	return (preparse($name, THIS_STRLEN) < $conf['user']['minname'] OR preparse($name, THIS_STRLEN) > $conf['user']['maxname'] OR ! preg_match('/^[\p{L}\p{Nd}]+$/u', $name)) ? 0 : 1;
}

/**
 * Verify Mail
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
 * Verify for phone number
 */
function verify_phone($phone)
{
	return preg_match('/^[+0-9. ()-]*$/', $phone);
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
 * Sel Day
 */
function Selday($day)
{
	for($i = 1; $i <= 31; $i ++)
	{
		echo '<option value="'.$i.'"';
		if ($i == $day) echo ' selected';
		echo '>'.$i.'</option>';
	}
}

/**
 * Sel Mont
 */
function Selmont($mont)
{
	for($i = 1; $i <= 12; $i ++)
	{
		echo '<option value="'.$i.'"';
		if($i==$mont) echo ' selected';
		echo '>'.$i.'</option>';
	}
}

/**
 * Sel Year
 */
function Selyear($year = FALSE)
{
	$y = date('Y');
	$e = $y + 10;
	for($i = $y; $i <= $e; $i ++)
	{
		echo '<option value="'.$i.'"'.((isset($year) AND $i == $year) ? ' selected' : '').'>'.$i.'</option>';
	}
}

/**
 * Sel Parse
 */
function data_parse($arr, $nu, $va)
{
	$new = array();
	foreach($arr as $out)
	{
		$new[$out[$nu]] = $out[$va];
	}
	return $new;
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
 * DECODE
 */
function decode($text, $liter)
{
	$glif = array();
	for ($exi = 128; $exi <= 143; $exi ++)
	{
		$glif['w'][] = chr($exi + 112);
		$glif['u'][] = chr(209).chr($exi);
	}
	for ($exi = 144; $exi <= 191; $exi ++)
	{
		$glif['w'][] = chr($exi + 48);
		$glif['u'][] = chr(208).chr($exi);
	}
	$glif['w'][] = chr(168);
	$glif['w'][] = chr(184);
	$glif['u'][] = chr(208).chr(129);
	$glif['u'][] = chr(209).chr(145);
	return ($liter == 'w') ? str_replace($glif['u'], $glif['w'], $text) : str_replace($glif['w'], $glif['u'], $text);
}

function utfinwin($string)
{
	$out = $cy = '';
	$bt = FALSE;
	for ($c = 0; $c < strlen($string); $c ++)
	{
		$i = ord($string[$c]);
		if (in_array($i, array(161, 162, 168, 170, 175, 184, 186, 191)))
		{
			$out.= chr($i);
		}
		if ($i <= 127) {
			$out.= $string[$c];
		}
		if ($bt)
		{
			$cynew = ($cy >> 2) & 5;
			$cynew1 = ($cy & 3) * 64 + ($i & 63);
			$inew = $cynew * 256 + $cynew1;
			if ($inew == 1025) {
				$iout = 168;
			} else {
				if ($inew == 1105) {
					$iout = 184;
				} else {
					$iout = $inew - 848;
				}
			}
			$out.= chr($iout);
			$bt = FALSE;
		}
		if (($i >> 5) == 6) {
			$cy = $i;
			$bt = TRUE;
		}
	}
	return $out;
}

function utfread($string, $code)
{
	if (function_exists('iconv'))
	{
		$result = iconv('UTF-8', $code.'//IGNORE', $string);
	}
	else if (function_exists('mb_convert_encoding'))
	{
		$result = mb_convert_encoding($string, $code, 'UTF-8');
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

function allarrmove($allarray, $catid, $table)
{
	global $basepref, $db, $cache, $sess;

	if (preparse($allarray, THIS_ARRAY) == 1)
	{
		foreach ($allarray as $id => $v)
		{
			$id = preparse($id, THIS_INT);
			$db->query("UPDATE ".$basepref."_".$table." SET catid = '".$catid."' WHERE id = '".$id."'");
        }
    }
}

function customallarrmove($allarray, $catid, $table, $id)
{
	global $basepref, $db, $cache, $sess;

	if (preparse($allarray, THIS_ARRAY) == 1)
	{
		foreach ($allarray as $pid => $v)
		{
			$pid = preparse($pid, THIS_INT);
			$db->query("UPDATE ".$basepref."_".$table." SET catid = '".$catid."' WHERE ".$id." = '".$pid."'");
		}
	}
}

function allarrdel($allarray, $misqlid, $table, $comment = FALSE, $image = FALSE)
{
	global $basepref, $db, $cache, $sess;

	if (preparse($allarray, THIS_ARRAY) == 1)
	{
		foreach ($allarray as $id => $v)
		{
			$id = preparse($id, THIS_INT);
			if ($image) {
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".$table." WHERE ".$misqlid." = '".$id."'"));
				if (isset($item['image']{0})){
					@unlink(WORKDIR.'/'.$item['image']);
				}
				if (isset($item['image_thumb']{0})){
					@unlink(WORKDIR.'/'.$item['image_thumb']);
				}
			}
			if ($comment) {
				$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".$table."' AND ".$misqlid." = '".$id."'");
			}
			$db->query("DELETE FROM ".$basepref."_".$table." WHERE ".$misqlid." = '".$id."'");
		}
	}
}

function allarract($allarray, $misqlid, $table, $act)
{
	global $basepref, $db, $cache, $sess;

	if (preparse($allarray, THIS_ARRAY) == 1)
	{
		foreach ($allarray as $id => $v)
		{
			$id = preparse($id,THIS_INT);
			$db->query("UPDATE ".$basepref."_".$table." SET act = '".$act."' WHERE ".$misqlid." = '".$id."'");
		}
	}
}

function thumb($path, $x, $h, $r)
{
	global $sess;

	require_once(ADMDIR.'/core/classes/Image.php');
	$image = new Image();
	$image->start();

	if (file_exists(WORKDIR.'/'.$path))
	{
		$image->viewthumb(WORKDIR.'/'.$path, $x, $h, $r);
	}
	else
	{
		$image->viewthumb(ADMDIR.'/template/library/noimage.png', $x, $h, $r);
	}
}

function un_dir($path)
{
	$array = new GlobIterator($path.'/*');
	foreach ($array as $obj)
	{
		is_dir($obj) ? un_dir($obj) : unlink($obj);
	}
    rmdir($path);
}

function name_mod($array)
{
	$result = array();
	foreach($array as $v)
	{
		$result[$v['mod']] = $v['name'];
	}
	return $result;
}

function is_serialize($str)
{
	return ($str == serialize(FALSE) OR @unserialize($str) !== FALSE);
}

function is_url($url)
{
	if ( ! empty($url))
	{
		require_once WORKDIR.'/core/classes/IDNA.php';

		$data = parse_url($url);

		$scheme = ( ! array_key_exists('scheme', $data) OR ! in_array($data['scheme'], array('http', 'https'))) ? 'http://' : $data['scheme'].'://';
		$host = (array_key_exists('host', $data) AND ! empty($data['host'])) ? $data['host'] : $data['path'];

		$url = filter_var(IDNA::encode($scheme.$host), FILTER_SANITIZE_URL);
		$url = filter_var($url, FILTER_VALIDATE_URL) !== false ? $scheme.$host : '';

		return $url;
	}

	return '';
}

function notslashes($resursing)
{
	return str_replace(array('\\', '\'', '\"'), array("", "'", '"'), $resursing);
}

function findcat($id, $catcaches, $recatcache)
{
	if (isset($catcaches[$id]) AND ! isset($recatcache[$id]))
	{
		$recatcache[$id] = $catcaches[$id];
		$recatcache = findcat($catcaches[$id][0], $catcaches, $recatcache);
	}
	return $recatcache;
}

function linecat($id, $catcaches)
{
	global $lang;
	$recatcache = array();
	$recatcache = findcat($id, $catcaches, $recatcache);
	$recatcache = array_reverse($recatcache);
	$total = '';
	foreach ($recatcache as $incat) {
		$total.= $incat[2]." &raquo; ";
	}
	return empty($total) ? $lang['cat_not'] : mb_substr($total, 0, -9);
}

function rannum($count, $symbol = FALSE)
{
	$number = '';
	$chars = '0123456789';
	if ($symbol) {
		$chars.= 'abcdefghijklmnopqrstuvwxyz';
	}
	for ($i = 0; $i < $count; $i ++) {
		$number.= substr($chars, (mt_rand() % strlen($chars)), 1);
	}
	return $number;
}

function formats($val, $point, $decimal = '.', $thousand = ',')
{
    return number_format($val, $point, $decimal, $thousand);
}

/**
 * Функция добавления класса в меню
 */
function cho($in = NULL, $filter = FALSE, $admid = FALSE)
{
	global $ADMIN_PERM, $ADMIN_ID, $CHECK_ADMIN;

	$admid = (($admid) ? ($ADMIN_ID == 1 ? '' : ' none') : '');
	$out = FALSE;
	$in = explode(',', str_replace(' ', '', $in));

	foreach ($in as $val)
	{
		if ($admid) {
			$out .= ($_REQUEST['dn'] == $val) ? ' class="current'.$admid.'"' : ' class="'.$admid.'"';
		} else {
			$out .= ($_REQUEST['dn'] == $val) ? ' class="current"' : (($filter) ? ' class="not"' : '');
		}
	}

	return $out;
}

/**
 * Массив литер
 */
function letters()
{
	global $conf, $lang;

	$latin = array(
		1  => 'A', 2  => 'B', 3  => 'C', 4  => 'D', 5  => 'E', 6  => 'F', 7  => 'G', 8  => 'H', 9  => 'I',
		10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R',
		18 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z', 27 => '0-9'
	);

	if (isset($lang['letters']{0}) AND $conf['langcode'] != 'en' )
	{
		$i = 28;
		$local = explode(',', preg_replace('/\s/', '', $lang['letters']));
		foreach ($local as $v)
		{
			$pair = explode(">", $v);
			$other[$i] = $pair[0];
			$i ++;
		}
		return $latin + $other;
	}
	else
	{
		return $latin;
	}
}

/**
 * Multy-Bite function strlen()
 */
if ( ! function_exists('mb_strlen'))
{
	function mb_strlen($str)
	{
		$result = strlen(iconv('UTF-8', 'Windows-1251', $str));
		return (int)$result;
	}
}

/**
 * Multy-Bite function strtolower()
 */
if ( ! function_exists('mb_strtolower'))
{
	function mb_strtolower($str)
	{
		$str = iconv('UTF-8', 'Windows-1251', $str);
		$str = strtolower($str);
		return iconv('Windows-1251', 'UTF-8', $str);
	}
}

/**
 * Multy-Bite function str_replace()
 */
if ( ! function_exists('mb_str_replace'))
{
	function mb_str_replace($needle, $replacement, $haystack)
	{
		return implode($replacement, mb_split($needle, $haystack));
	}
}

/**
 * Translit
 * Generation CPU
 */
function cpu_translit($string)
{
	global $conf;

	$translit = new Translit();
	$str_cpu = $translit->process($string);
	$str_cpu = str_replace(array('&amp; ', '& '), '', $str_cpu);
	$out_cpu = mb_substr(trim($str_cpu), 0, 90, $conf['langcharset']);

	return $translit->title($out_cpu);
}

/**
 * send_mail
 */
function send_mail($to, $subject, $message, $from, $format = FALSE)
{
	global $conf;

	require_once(ADMDIR.'/core/classes/Mail.php');
	$send = new Mail();

	$send->From($from);
	$send->To($to);
	$send->Subject($subject);
	$send->Body($message);
	$send->Priority(3);

	if ($format) $send->Html();

	return $send->acting();
}

if ( ! function_exists('gzopen'))
{
    function gzopen($filename , $mode, $use_include_path = 0 )
	{
		return gzopen64($filename, $mode, $use_include_path);
	}
}
