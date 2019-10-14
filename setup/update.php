<?php
/**
 * File:        setup/update.php
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
 * Global
 */
global $config_path, $mysql_path, $db, $basepref, $charsebd, $hostname, $nameuser, $password, $namebase, $tm, $_up, $_update, $CHECK_ADMIN,
       $setup_de, $setup_in, $nowrite, $ecount, $icount, $find_error_no, $oldpref, $step;

/**
 * Require core Setup
 */
require_once ('base/setup.function.php');
require_once ('base/setup.template.php');
require_once ('base/setup.ini.php');

$DIRBASE = str_replace('/setup/'.basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
define('DIRBASE', $DIRBASE);

/**
 * Ini Class Template
 */
$tm = new Template();

/**
 * Works $_REQUEST
 */
$height = ( ! isset($_REQUEST['height'])) ? 300 : trim($_REQUEST['height']);
$step = ( ! isset($_REQUEST['step'])) ? 0 : trim($_REQUEST['step']);

/**
 *  Include core Site
 */
require_once (DNBASE.'core/config.php');
require_once (DNBASE.'core/classes/Api.php');
require_once (DNBASE.'core/classes/DB.php');

$api = new Api();

// DB Connect
$db = new DB($hostname, $nameuser, $password, $namebase, $charsebd);

$setid['langsetid'] = null;
/**
 * STEP UPDATE
 * ------------ */
$_up[0]['tpl']    = 'update.1';
$_up[0]['file']   = 'docs/update_ru.txt';
$_up[0]['action'] = 'txt';

$_up[1]['tpl']    = 'update.2';
$_up[1]['file']   = 'sql/update/lang.sql';
$_up[1]['action'] = 'mysql';
$_up[1]['mysql']  = PHP_EOL;

$_up[2]['tpl']    = 'update.2';
$_up[2]['file']   = 'sql/update/insert.sql';
$_up[2]['action'] = 'mysql';
$_up[2]['mysql']  = PHP_EOL;

$_up[3]['tpl']    = 'update.3';
$_up[3]['file']   = 'sql/update/setting.sql';
$_up[3]['action'] = 'mysql';
$_up[3]['mysql']  = PHP_EOL;

$_up[4]['tpl']    = 'update.5';
$_up[4]['file']   = '';
$_up[4]['action'] = 'not';

/**
 *  Language
 */
require_once ('lang/'.$language.'.php');

//print $db->tables($basepref."_user"); exit;
/**
 * INDEX OR SETUP
 * ------------------ */
$template = $tm->create('index');
$tempform = $tm->create($_up[$step]['tpl']);

/**
 * START switch($step)
 * ---------------------- */
switch ($step)
{
	case $step:

		$forms = $re = $vt = null;
		$barwidth = 0;
		$text = ( ! empty($_up[$step]['file'])) ? $tm->content($_up[$step]['file']): '';
		$submit = ( ! empty($_up[$step]['submit'])) ? $_up[$step]['submit']: '';

		/**
		 * Progress Bar
		 */
		if ($step > 0)
		{
			$cs = count($_up);
			$percent = intval(100 / ($cs - 2));
			$barwidth = (empty($re)) ? intval($percent * $step) : intval($percent * ($step - 1));
			if ($cs == ($step + 1) && empty($re))
				$barwidth = 100;
		} else {
			$barwidth = 0;
		}
		$vt = count($_up) - 2;

		if (isset($_up[$step]['alltext']) && is_array($_up[$step]['alltext'])) {
			$tempform = $tm->parse($_up[$step]['alltext'], $tempform);
		}

		/**
		 * MySQL
		 */
		if (isset($_up[$step]['action']) && $_up[$step]['action'] == 'mysql')
		{
			$out = null;
			$tables = 1;
			$ecount = $icount = 0;
			$sql = ( ! empty($_up[$step]['file'])) ? $tm->content($_up[$step]['file']) : '';
			$stringdump = explode($_up[$step]['mysql'], $sql);

			$last = $db->fetchassoc(
				$db->query("SELECT setname, setval FROM ".$basepref."_settings WHERE setname = 'lastup' LIMIT 1")
			);

			if ($last['setval'] <> NEWTIME) {
				$db->query("UPDATE ".$basepref."_settings SET setval = '".NEWTIME."' WHERE setname = 'lastup'");
			}
/*
			$ins = $db->query("SELECT setopt FROM ".$basepref."_settings WHERE setopt = 'sms' LIMIT 1");
			$lgs = $db->query("SELECT langvars FROM ".$basepref."_language WHERE langvars = 'auth_method' LIMIT 1");

			if (($step == 1 AND $db->numrows($lgs) == 1) OR ($step == 2 AND $db->numrows($ins) == 1))
			{
				$disabled = "readonly";
				$text = $no_need;
			}
			else
			{*/
				foreach ($stringdump as $insert)
				{
					$insert = trim(rtrim($insert, ';'));
					if ( ! empty($insert))
					{
						$string = str_replace
						(
							array('{pref}', '{time}', '{cookie}', '{site}', '{setid}'),
							array($basepref, time(), acookname(), 'http://'.$_SERVER['HTTP_HOST'], $setid['langsetid']),
							$insert
						);

						$gomod = null;
						if (preg_match("/INSERT INTO/i", $string))
						{
							if (preg_match("/\b(article|down|news|photos)\b/i", $string, $matches))
							{
								$set = $db->fetchrow($db->query("SELECT COUNT(setid) AS total FROM ".$basepref."_settings WHERE setopt = '".$matches[0]."' AND setname = 'injpg'"));
								if ( ! $db->tables($matches[0]) OR ($db->tables($matches[0]) AND $set['total'] > 0)) {
									 continue;
								}
								$gomod = '`'.$matches[0].'`';
							}
						}

						$inq = $db->query($string, 0, '', 0);

						if (preg_match("/CREATE TABLE/i", $string))
						{
							preg_match("/CREATE TABLE ([a-zA-Z0-9_]*)/i", $string, $matches);
						}

						if (preg_match("/INSERT INTO/i", $string))
						{
							preg_match("/INSERT INTO ([a-zA-Z0-9_]*)/i", $string, $matches);
						}

						if (preg_match("/ALTER TABLE/i", $string))
						{
							preg_match("/ALTER TABLE ([a-zA-Z0-9_]*)/i", $string, $matches);
						}

						if (preg_match("/UPDATE/i", $string))
						{
							preg_match("/UPDATE ([a-zA-Z0-9_]*)/i", $string, $matches);
						}

						// lang value
						if (isset($matches[1]) AND preg_match('/language$/', $matches[1]) AND $_up[$step]['file'] == 'sql/update/lang.sql')
						{
							$strings = explode(',', $string);
							$matches[1] = isset($strings[3]) ? '<span class="blue">lang:</span> '.str_replace("'", '', trim($strings[3])) : $matches[1];
						}

						// No DROP
						if ( ! preg_match("/DROP/i", $string))
						{
							if ( ! $inq)
							{
								$out .= (isset($matches[1])) ? '» <strong>'.$matches[1].'</strong> <span class="closed">'.$create_no.'</span><br />' : '';
								$ecount++;
							} else {
								$out .= (isset($matches[1])) ? '» <span class="bolds">'.$matches[1].' '.$gomod.'</span> <span class="open">'.$create_yes.'</span><br />' : '';
								$icount++;
							}
						}
					}
				}
				$disabled = ($ecount > 0) ? "disabled" : "readonly";
				$text = ($ecount > 0) ? $find_error.$ecount : $find_error_no1.$icount;
			//}


			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_up[$step]['title']),
					'text'     => $out,
					'nowrite'  => $text,
					'step'     => ($step + 1),
					'disabled' => $disabled,
					'submit'   => $submit
				),
				$tempform
			);
		}

		/**
		 * Txt
		 */
		if (isset($_up[$step]['action']) AND $_up[$step]['action'] == 'txt')
		{
			$forms = $tm->parse(array
				(
					'title'  => str_replace('%vt%', $vt, $_up[$step]['title']),
					'text'   => $text,
					'step'   => ($step + 1),
					'submit' => $submit
				),
				$tempform
			);
		}

		/**
		 * Cache
		 */
		if (isset($_up[$step]['action']) && $_up[$step]['action'] == 'cache')
		{
			$cs = $ct = $cl = 0;

			$set = fopen(DNBASE.'cache/cache.config.php','wb');
			if (is_resource($set)) {
				fputs($set, '');
				$nowrite.= 'cache.config<br>';
			} else {
				$cs = 1;
			}
			fclose($set);

			$let = fopen(DNBASE.'cache/cache.lang.php','wb');
			if (is_resource($let)) {
				fputs($let, '');
				$nowrite.= 'cache.lang<br>';
			} else {
				$cl = 1;
			}
			fclose($let);

			$text = ($ecount > 0) ? $find_error.' <strong>'.$ecount.'</strong>' : $find_error_no.' <strong>'.$icount.'</strong>';
			$text.= '<br />» <strong>cache/cache.config.php</strong>'.(($cs == 0) ? '<span class="open">'.$write_yes.'</span>' : '<span class="closed">'.$write_no.'</span>');
			$text.= '<br />» <strong>cache/cache.lang.php</strong>'.(($cl == 0) ? '<span class="open">'.$write_yes.'</span>' : '<span class="closed">'.$write_no.'</span>');

			$setinq = $db->query("SELECT setid, setcode FROM ".$basepref."_settings");

			while ($setval = $db->fetchrow($setinq))
			{
				if (preg_match("/TextArea|OutHint|InHint|width:270px/i", $setval['setcode']))
				{
					$n = str_replace
							(
								array(' style=\"width:270px;\"', 'TextArea', 'OutHint', 'InHint'),
								array('', '$tm->textarea', '$tm->outhint', '$tm->outhint'),
								$setval['setcode']
							);

					$db->query("UPDATE ".$basepref."_settings SET setcode = '".$db->escape($n)."' WHERE setid = '".$setval['setid']."'");
				}
			}

			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_up[$step]['title']),
					'text'     => $text,
					'disabled' => 'readonly',
					'nowrite'  => $nowrite,
					'step'     => ($step + 1),
					'submit'   => $submit
				),
				$tempform
			);
		}

		/**
		 * NOT
		 */
		if (isset($_up[$step]['action']) && $_up[$step]['action'] == 'not')
		{
			$forms = $tm->parse(array
				(
					'title'    => str_replace('%vt%', $vt, $_up[$step]['title']),
					'text'     => $text,
					'disabled' => 'readonly',
					'step'     => ($step + 1),
					'submit'   => $submit
				),
				$tempform
			);
		}

		/**
		 * Forms
		 */
		/*
		$forms = $tm->parse(array
			(
				'title'    => $_up[$step]['title'],
				'text'     => $total,
				'step'     => ($step + 1),
				'disabled' => $disabled,
				'nowrite'  => $nowrite,
				'submit'   => $submit
			),
			$tempform
		);
		*/

		/**
		 * Print
		 */
		$tm->parseprint(array
			(
				're'        => $re,
				'in'        => $setup_in,
				'de'        => $setup_de,
				'title'     => str_replace('%vt%', $vt, $_up[$step]['title']),
				'forms'     => $forms,
				'height'    => $height,
				'divheight' => $height - 40,
				'product'   => $product,
				'inproduct' => $product,
				'progress'  => $progress,
				'barwidth'  => $barwidth,
				'copy'      => $copyright
			),
			$template
		);
}
