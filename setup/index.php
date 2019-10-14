<?php
/**
 * File:        setup/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

$realpath = str_replace('\\', '/', realpath(__DIR__).DIRECTORY_SEPARATOR);

define('DNBASE', dirname($realpath).'/');
define("DNREAD", 1);

/**
 *  Global
 */
global	$db, $basepref, $version, $tm, $hostname, $nameuser, $password, $namebase,
		$config_path, $setup_de, $setup_in, $cdb, $cdbu,
		$bdpref, $bdhost, $bduser, $bdpass, $bdbase, $step;

/**
 *  Require once
 */
require_once ('base/setup.function.php');
require_once ('base/setup.template.php');
require_once ('base/setup.ini.php');
require_once ('base/setup.xml.php');

$DIRBASE = str_replace('/setup/'.basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
define('DIRBASE', $DIRBASE);

/**
 * Ini Class Template 
 */
$tm = new Template();

/**
 *  Language
 */
require_once ('lang/'.$language.'.php');

/**
 *  LICENSE
 */
if ( ! isset($step))
{
	$tm->parseprint(array
		(
			'product'   => $product,
			'noscript'  => $noscript,
			'title'     => $license['title'],
			'inproduct' => $product,
			'present'   => $license['present'],
			'submit'    => $license['submit'],
			'copy'      => $copyright,
			'text'      => @file_get_contents('docs/license_ru.txt')
		),
		$tm->create('license'));
}
else
{

	/**
	 *  Works $_REQUEST
	 */
	$who    = ( ! isset($_REQUEST['who'])) ? '' : trim($_REQUEST['who']);
	$height = ( ! isset($_REQUEST['height'])) ? 250 : trim($_REQUEST['height']);
	$step   = ( ! isset($_REQUEST['step'])) ? 1 : trim($_REQUEST['step']);
	$step   = (isset($_REQUEST['nextstep']) AND $_REQUEST['nextstep'] == 'no') ? ($step + 1) : $step;

	/**
	 *  Update
	 */
	if ($who == 'up')
	{
		Header('Location: update.php');
		exit();
	}

	/*
	 *  Include Core Site
	 */
	if ($step > 5)
	{
		require_once (DNBASE.'core/config.php');
		require_once (DNBASE.'core/classes/Api.php');
		require_once (DNBASE.'core/classes/DB.php');

		$api = new Api();

		// DB Connect
		$db = new DB($hostname, $nameuser, $password, $namebase, $charsebd);
	}

	/**
	 *  Templates
	 */
	$template = $tm->create('index');
	$tempform = $tm->create($_step[$step]['tpl']);

	/**
	 *  START switch ($step)
	 * --------------------- */
	switch ($step)
	{
		case $step:

		$forms = $re = $vt = null;
		$barwidth = 0;
		$text = ( ! empty($_step[$step]['file'])) ? $tm->content($_step[$step]['file']): '';
		$submit = ( ! empty($_step[$step]['submit'])) ? $_step[$step]['submit']: '';

		/**
		 * Progress Bar
		 */
		$cs = count($_step);
		$percent = intval(100 / ($cs));
		$barwidth = intval($percent * ($step));

		/**
		 * Step action
		 */
		$vt = count($_step) - 1;
		if (isset($_step[$step]['alltext']) AND is_array($_step[$step]['alltext']))
		{
			$tempform = $tm->parse($_step[$step]['alltext'], $tempform);
		}

		/**
		 * Not action
		 */
		if ( ! isset($_step[$step]['action']))
		{
			exit;
		}

		/**
		 * Choice
		 */
		if ($_step[$step]['action'] == 'choice')
		{
			$count = count(glob(DNBASE.'cache/*'));
			$set = ($count) ? '' : 'checked';
			$upd = ($count) ? 'checked' : '';
			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => 'readonly',
					'submit'   => $submit,
					'text'     => $text,
					'step'     => ($step + 1),
					'set'      => $set,
					'upd'      => $upd
				),
				$tempform);
		}

		/**
		 * Not
		 */
		/*if ($_step[$step]['action'] == 'not')
		{
			$count = count(glob(DNBASE.'cache/*'));
			$set = ($count) ? '' : 'checked';
			$upd = ($count) ? 'checked' : '';
			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => 'readonly',
					'submit'   => $submit,
					'text'     => $text,
					'step'     => ($step + 1),
					'set'      => $set,
					'upd'      => $upd
				),
				$tempform);
		}*/

		/**
		 * Server requirements
		 */
		if ($_step[$step]['action'] == 'server')
		{
			$error = array();
			$not_match = $total = $notice = $status = null;

			// PHP version
			preg_match('/\d\.\d.\d/', phpversion(), $php);
			if (version_compare($php[0], '5.3', '>=')) {
				$total .= '» <span>PHP '.$php[0].'</span> — <span class="open">'.$supported.'</span><br />';
			} else {
				$total .= '» <span>PHP '.$php[0].'</span> — <span class="closed">'.$unsupported.'</span><br />';
				$not_match .= 'PHP '.$php[0].'<br />';
				$notice .= '<li>'.$php_notice.'</li>';
				$error[] = 1;
			}

			if (function_exists('mysqli_connect')) {
				$total .= '» <span>MySQLi</span> — <span class="open">'.$supported.'</span><br />';
			} else {
				$total .= '» <span>MySQLi</span> — <span class="closed">'.$unsupported.'</span><br />';
				$not_match .= 'MySQLi<br />';
				$notice .= '<li>'.$mysqli_notice.'</li>';
				$error[] = 1;
			}

			// JSON
			if (function_exists('json_encode')) {
				$total .= '» <span>JSON</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>JSON</span> — <span class="closed">'.$disabled.'</span><br />';
				$not_match .= 'JSON<br />';
				$notice .= '<li>'.$json_notice.'</li>';
				$error[] = 1;
			}

			// cURL
			if (function_exists('curl_init')) {
				$total .= '» <span>cURL</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>cURL</span> — <span class="closed">'.$disabled.'</span><br />';
			}

			// Zlib
			if (in_array('zlib', get_loaded_extensions())) {
				$total .= '» <span>Zlib</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>Zlib</span> — <span class="closed">'.$disabled.'</span><br />';
				$not_match .= 'Zlib<br />';
				$notice .= '<li>'.$zlib_notice.'</li>';
				$error[] = 1;
			}

			// ZIP
			if (in_array('zip', get_loaded_extensions())) {
				$total .= '» <span>ZIP</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>ZIP</span> — <span class="closed">'.$disabled.'</span><br />';
				$not_match .= 'ZIP<br />';
				$notice .= '<li>'.$zlib_notice.'</li>';
				$error[] = 1;
			}

			// Iconv
			if (function_exists('iconv')) {
				$total .= '» <span>Iconv</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>Iconv</span> — <span class="closed">'.$disabled.'</span><br />';
			}

			// Multibyte String
			if (function_exists('mb_internal_encoding')) {
				$total .= '» <span>mbString</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>mbString</span> — <span class="closed">'.$disabled.'</span><br />';
			}

			// SimpleXML
			if (function_exists('simplexml_load_string')) {
				$total .= '» <span>SimpleXML</span> — <span class="open">'.$enabled.'</span><br />';
			} else {
				$total .= '» <span>SimpleXML</span> — <span class="closed">'.$disabled.'</span><br />';
			}

			// GD Version
			if (function_exists('gd_info'))
			{
				$gd_info = @gd_info();
				$gd = preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);

				if (version_compare($gd, '2.0', '>=')) {
					$total .= '» <span>GD '.$gd.'</span> — <span class="open">'.$supported.'</span><br />';
				} else {
					$total .= '» <span>GD '.$gd.'</span> — <span class="closed">'.$unsupported.'</span><br />';
					$not_match .= 'GD Version '.$gd.'<br />';
					$notice .= '<li>'.$gd_notice.'</li>';
					$error[] = 1;
				}
			}
			else
			{
				$total .= '» <span>GD</span> — <span class="closed">'.$not_installed.'</span>';
				$error[] = 1;
			}

			// PCRE
			if (defined('PCRE_VERSION'))
			{
				list($pcre) = explode(' ', constant('PCRE_VERSION'));
				if (version_compare($pcre, '7.0', '>=')) {
					$total .= '» <span>PCRE '.$pcre.'</span> — <span class="open">'.$supported.'</span><br />';
				} else {
					$total .= '» <span>PCRE '.$pcre.'</span> — <span class="closed">'.$unsupported.'</span><br />';
					$not_match .= 'PCRE Version '.$pcre.'<br />';
					$notice .= '<li>'.$pcre_notice.'</li>';
					$error[] = 1;
				}
			}
			else
			{
				$total .= '» <span>PCRE</span> — <span class="closed">'.$not_installed.'</span>';
				$error[] = 1;
			}

			// OUT
			$output = $server_normal;
			if (in_array(1, $error))
			{
				$status = 'disabled';
				$output = $server_notice.'<ol>'.$notice.'</ol>'.trim('<pre><mark>'.$not_match.'</mark></pre>');
			}

			$forms = $tm->parse(array
				(
					'title'  => str_replace('%vt%', $vt, $_step[$step]['title']),
					'status' => $status,
					'output' => $output,
					'submit' => $submit,
					'text'   => $total,
					'step'   => ($step + 1)
				),
				$tempform);
		}

		/**
		 * Write
		 */
		if ($_step[$step]['action'] == 'write')
		{
			$no_write = $total = null;
			$error = array();
			if (isset($_write[$_step[$step]['action']]) AND is_array($_write[$_step[$step]['action']]))
			{
				foreach ($_write[$_step[$step]['action']] as $id => $dirs)
				{
					$total .= (is_writable(DNBASE.$dirs)) ? '» <span>'.$dirs.'</span> <span class="open">'.$write_yes.'</span><br />' : '» <span>'.$dirs.'</span> <span class="closed">'.$write_no.'</span><br />';
					if (in_array($dirs, $waring) AND ! is_writable(DNBASE.$dirs))
					{
						$no_write .= '<mark>'.$dirs.'</mark>';
						$error[] = (is_writable(DNBASE.$dirs)) ? 0 : 1;
					}
				}

				$re = (in_array(1, $error)) ? $return : '';
				$disabled = (in_array(1, $error)) ? 'disabled' : 'readonly';
				$nowrite  = (empty($no_write)) ? $write_normal : $write_notice.trim('<pre>'.$no_write.'</pre>');
			}

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => $disabled,
					'nowrite'  => $nowrite,
					'submit'   => $submit,
					'text'     => $total,
					'step'     => ($step + 1)
				),
				$tempform);
		}

		/**
		 * Connect
		 */
		if ($_step[$step]['action'] == 'connect')
		{
			$conn = @mysqli_connect($bdhost, $bduser, $bdpass);

			$work = ($conn) ? 1 : 0;
			if (isset($bdhost) AND isset($bduser) AND $work == 1)
			{
					$writes = write_php_file($config_path, $bdhost, $bduser, $bdpass, $bdbase, $bdpref);
					$mess = ($writes == 1) ? $yes_conf : $no_conf;
					$mess.= $bd_ok;

					if ($cdb == 1)
					{
						$create = ($cdb == 1 AND @mysqli_query($conn, "CREATE DATABASE ".$bdbase.";")) ? 1 : 0;
						$mess .= ($create == 1) ? $cdb_ok : $cdb_no;
					}

					$select = (@mysqli_select_db($conn, $bdbase)) ? 1 : 0;
					$mess .= ($select == 1) ? $sel_ok : $sel_no;
					$disabled = ($select == 1 AND $writes == 1) ? 'readonly' : 'disabled';
					$re = ($select == 1 AND $writes == 1) ? '' : $return;
					$nowrite = ($select == 1 AND $writes == 1) ? $nt_ok : $nt_no;
			}
			else
			{
				$mess = $bd_no . $sel_no;
				$nowrite = $nt_no;
				$re = $return;
				$disabled = 'disabled';
			}

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'text'     => $mess,
					'nowrite'  => $nowrite,
					'step'     => ($step + 1),
					'disabled' => $disabled,
					're'       => $re,
					'submit'   => $submit
				),
				$tempform
			);
		}

		/**
		 * MySQL
		 */
		if ($_step[$step]['action'] == 'mysql')
		{
			$out = null;
			$ecount = $icount = 0;
			$sql = ( ! empty($_step[$step]['file'])) ? $tm->content($_step[$step]['file']) : '';
			$stringdump = explode($_step[$step]['mysql'], $sql);

			foreach ($stringdump as $insert)
			{
				$insert = trim(rtrim($insert, ';'));
				if ( ! empty($insert))
				{
					$string = str_replace(array('{pref}', '{time}'), array($basepref, time()), $insert);
					$inq = $db->query($string, 0, '', 0);

					if (preg_match("/CREATE TABLE/i", $string))
					{
						preg_match("/CREATE TABLE ([a-zA-Z0-9_]*)/i", $string, $matches);
					}

					if (preg_match("/INSERT INTO/i", $string))
					{
						preg_match("/INSERT INTO ([a-zA-Z0-9_]*)/i", $string, $matches);
					}

					// lang value
					if (isset($matches[1]) AND preg_match('/language$/', $matches[1]) AND $_step[$step]['file'] == 'sql/lang.sql')
					{
						$strings = explode(',', $string);
						$matches[1] = isset($strings[3]) ? '<span class="blue">lang:</span> '.str_replace("'", '', trim($strings[3])) : $matches[1];
					}

					if ( ! preg_match("/DROP/i", $string))
					{
						if ( ! $inq) {
							$out .= (isset($matches[1])) ? '» <span>'.$matches[1].'</span> <span class="closed">'.$create_no.'</span><br />' : '';
							$ecount++;
						} else {
							$out .= (isset($matches[1])) ? '» <span>'.$matches[1].'</span> <span class="open">'.$create_yes.'</span><br />' : '';
							$icount++;
						}
					}
				}
			}

			$disabled = ($ecount > 0) ? 'disabled' : 'readonly';
			$text = ($ecount > 0) ? $find_error.$ecount : $find_error_no1.$icount.$find_error_no2;

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'text'     => $out,
					'nowrite'  => $text,
					'step'     => ($step + 1),
					'disabled' => $disabled,
					'submit'   => $submit
				),
				$tempform);
		}

		/**
		 * Copy
		 */
		if ($_step[$step]['action'] == 'copy')
		{
			dn_unzip(DNBASE.'setup/ext/up.zip', DNBASE.'up');
			dn_unzip(DNBASE.'setup/ext/cache.zip', DNBASE.'cache');

			dn_permiss(DNBASE.'up');
			dn_permiss(DNBASE.'cache');


			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => 'readonly',
					'submit'   => $submit,
					'text'     => $text,
					'step'     => ($step + 1)
				),
				$tempform);
		}

		/**
		 * Setting
		 */
		if ($_step[$step]['action'] == 'setting')
		{
			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => 'readonly',
					'submit'   => $submit,
					'text'     => $text,
					'step'     => ($step + 1)
				),
				$tempform);
		}

		/**
		 * Update
		 */
		if ($_step[$step]['action'] == 'update')
		{
			$out = null;
			$ecount = 0;
			$uarray = (isset($_update[$_step[$step]['action']])) ? $_update[$_step[$step]['action']] : '';

			if (isset($uarray) AND is_array($uarray))
			{
				foreach ($uarray as $id => $value)
				{
					if (empty($_POST[$id]))
					{
						$out .= $value.'<br />';
						$ecount++;
					}
				}
			}

			if ($ecount == 0)
			{
				if ($site_name) {
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$site_name."' WHERE setname = 'site'");
				}

				if ($site_url) {
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$site_url."' WHERE setname = 'site_url'");
				}

				if ($site_mail) {
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$site_mail."' WHERE setname = 'site_mail'");
				}

				$db->query("UPDATE ".$basepref."_settings SET setval = '".acookname()."' WHERE setname = 'acookname'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".acookname()."' WHERE setname = 'cookname'");

				$db->query("TRUNCATE TABLE ".$basepref."_admin");
				$pass = md5(trim($apass));
				$valid = ($ecount == 0) ? $db->query
											(
												"INSERT INTO ".$basepref."_admin (`admid`, `adlog`, `adpwd`, `admail`, `adlast`, `permiss`) VALUES (
												 NULL, '".$aname."', '".$pass."', '', '".NEWTIME."',
												 'news|article|down|catalog|poll|photos|pages|user|comment|subscribe|faq|options|seo|amanage|lang|base|filebrowser|platform|media'
												 )"
											) : '';
				if ( ! $valid )
				{
					$ecount = 1;
					$out .= $admin_no.'<br />';
				}
			}

			$text = ($ecount > 0) ? $out : $end_text_yes;
			$nt = ($ecount > 0) ? $end_nt_no : $end_nt_yes;
			$notice = ($ecount > 0) ? $end_title_no : $end_title_yes;
			$re = ($ecount > 0) ? $return : '';
			$disabled = ($ecount > 0) ? 'disabled' : 'readonly';

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'disabled' => $disabled,
					'notice'   => $notice,
					'text'     => $text,
					'submit'   => $submit,
					'nt'       => $nt
				),
				$tempform);
		}

		/**
		 * Txt
		 */
		if ($_step[$step]['action'] == 'txt')
		{
			$forms = $tm->parse(array
				(
					'title'  => str_replace('%vt%', $vt, $_step[$step]['title']),
					'text'   => $text,
					'step'   => ($step + 1),
					'submit' => $submit
				),
				$tempform);
		}

		/**
		 * Lang
		 */
		if ($_step[$step]['action'] == 'xml')
		{
			$out = null;
			$ecount = $icount = 0;

			$db->query("TRUNCATE TABLE ".$basepref."_language;");
			$db->query("TRUNCATE TABLE ".$basepref."_language_pack");
			$db->query("TRUNCATE TABLE ".$basepref."_language_setting");

			$xml = new XML();
			$xml->parse($_step[$step]['file']);
			$xml_array = $xml->parseout;

			if ( ! empty($xml_array) AND $xml_array['type'] == 'setup')
			{
				$newlang = array();
				foreach ($xml_array as $key => $val)
				{
					if ($key == 'type')
						$newlang['type'] = $val;
					if ($key == 'atr')
						$newlang['atr'] = $val;
					if ($key == 'set')
						$newlang['set'] = $val;
				}

				if (
					empty($newlang['atr']['packname']) OR
					empty($newlang['atr']['codes']) OR
					empty($newlang['atr']['charset']) OR
					empty($newlang['atr']['version']))
				{
					$ecount == 1;
				}

				// Insert
				$inq = $db->query
							(
								"INSERT INTO ".$basepref."_language_pack VALUES (
								 NULL,
								 '".$newlang['atr']['packname']."',
								 '".$newlang['atr']['codes']."',
								 '".$newlang['atr']['charset']."',
								 '21',
								 '".$newlang['atr']['author']."'
								 )"
							);

				if ( ! $inq ) {
					$out .= '» <span>'.$basepref.'_language_pack</span> <span class="closed">'.$create_no.'</span><br />';
					$ecount++;
				} else {
					$out .= '» <span>'.$basepref.'_language_pack</span> <span class="open">'.$create_yes.'</span><br />';
					$icount++;
				}

				$newlangid = $db->insertid();

				// $newlang
				foreach ($newlang['set'] as $key => $val)
				{
					$inq = $db->query
								(
									"INSERT INTO ".$basepref."_language_setting VALUES (
									 NULL,
									 '".$newlangid."',
									 '".$val['name']."',
									 '".md5($val['name'])."'
									 )"
								);

					if ( ! $inq ) {
						$out .= '» <span>'.$basepref.'_language_setting</span> <span class="closed">'.$create_no.'</span><br />';
						$ecount++;
					} else {
						$out .= '» <span>'.$basepref.'_language_setting</span> <span class="open">'.$create_yes.'</span><br />';
						$icount++;
					}

					$newlangset = $db->insertid();

					// Val
					foreach ($val['lang'] as $tag)
					{
						if ($tag['name'] AND $tag['name'] != 'empty' AND $tag['vals'])
						{
							$db->query
								(
									"INSERT INTO ".$basepref."_language VALUES (
									 NULL,
									 '".$newlangid."',
									 '".$newlangset."',
									 '".$tag['name']."',
									 '".$tag['vals']."',
									 '".$tag['vals']."',
									 '".$tag['cache']."'
									 )"
								);

							if ( ! $inq ) {
								$out .= '» <span>'.$basepref.'_language</span> <span class="closed">'.$create_no.'</span><br />';
								$ecount++;
							} else {
								$out .= '» <span>'.$basepref.'_language</span> <span class="open">'.$create_yes.'</span><br />';
								$icount++;
							}
						}
					}
				}
			}

			$disabled = ($ecount > 0) ? "disabled" : "readonly";
			$text = ($ecount > 0) ? $find_error.$ecount : $find_error_no1.$icount.$find_error_no2;

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_step[$step]['title']),
					'text'     => $out,
					'nowrite'  => $text,
					'disabled' => $disabled,
					'step'     => ($step + 1),
					'submit'   => $submit
				),
				$tempform);
		}

		/**
		 * Print
		 */
		$tm->parseprint(array
			(
				'product'   => $product,
				'noscript'  => $noscript,
				'title'     => str_replace('%vt%', $vt, $_step[$step]['title']),
				'forms'     => $forms,
				'in'        => $setup_in,
				'de'        => $setup_de,
				're'        => $re,
				'height'    => $height,
				'divheight' => $height - 40,
				'inproduct' => $product,
				'progress'  => $progress,
				'barwidth'  => $barwidth,
				'nextstep'  => ((isset($_step[$step]['nextstep'])) ? $_step[$step]['nextstep'] : ''),
				'copy'      => $copyright
			),
			$template
		);
	}
}
