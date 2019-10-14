<?php
/**
 * File:        /admin/core/classes/Pages.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Pages
 */
class Pages
{
	private $dir_cache = '/cache/pages';
	private $dir_up = '/up/pages';

	function __construct()
	{
		$this->is_path(WORKDIR.$this->dir_cache);
		$this->is_path(WORKDIR.$this->dir_up);
	}

	function cachepage($mod, $paid, $content)
	{
		global $tm, $lang;

		$file = ($mod != 'pages') ? $mod.'/' : '';
		$file_path = WORKDIR.$this->dir_cache.'/'.$file.$mod.'.'.$paid.'.php';

		$output ="<?php if(!defined('DNREAD')) exit();\n";
		$output.="return array(";
		foreach ($content as $key => $val)
		{
			$output.="\n'".$key."'=>'".$val."',";
		}
		$output.="\n);\n";

		if (
			! file_exists($file_path) OR
			(is_file($file_path) AND is_writable($file_path))
		) {
			file_put_contents($file_path, $output, LOCK_EX);
		}
		else
		{
			$tm->errorbox($lang['not_writable'].': '.$file_path);
		}
	}

	function pageid($mod, $id = NULL, $cpu = NULL)
	{
		global $db, $basepref;

		$dir_path = ($mod == 'pages') ? WORKDIR.$this->dir_cache : WORKDIR.$this->dir_cache.'/'.$mod;

		$inq = $db->query("SELECT paid, cpu FROM ".$basepref."_pages WHERE mods = '".$mod."'");
		if ($db->numrows($inq) > 0)
		{
			$output ="<?php if(!defined('DNREAD')) exit();\n";
			$output.="return array(";
			while ($item = $db->fetchrow($inq)) {
				$output.="\n'".$item['cpu']."'=>'".$item['paid']."',";
			}
			if (substr($output, -1) == ',') {
				$output = substr($output, 0, -1);
			}
			$output.="\n);\n";
			$file_path = $dir_path.'/'.$mod.'.id.php';
			file_put_contents($file_path, $output, LOCK_EX);
		}
		else
		{
			unlink($dir_path.'/'.$mod.'.id.php');
		}
	}

	function mk_dir($dir, $copy)
	{
		global $tm, $lang;

		if ( ! is_dir($dir))
		{
			mkdir($dir, 02777);
			chmod($dir, 02777);
			file_put_contents($dir.'/index.html', NULL);
		}
	}

	function page_dir($mod)
	{
		$dir_cache = WORKDIR.$this->dir_cache;
		$dir_up = WORKDIR.$this->dir_up;

		if ( ! empty($mod) AND $mod != 'pages')
		{
			$this->mk_dir($dir_cache.'/'.$mod, $dir_cache);
			$this->mk_dir($dir_up.'/'.$mod, $dir_up);
			$this->mk_dir($dir_up.'/'.$mod.'/img', $dir_up);
			$this->mk_dir($dir_up.'/'.$mod.'/file', $dir_up);
		}
	}

	function undir($path)
	{
		$array = new GlobIterator($path.'/*');
		foreach ($array as $obj)
		{
			is_dir($obj) ? $this->undir($obj) : unlink($obj);
		}
		rmdir($path);
	}

	function is_path($path)
	{
		global $tm, $lang;

		if ( ! is_writable($path))
		{
			$tm->errorbox($lang['not_writable'].': '.$path);
			exit;
		}
	}

	function realpath_mod($mod)
	{
		return ($mod == 'pages') ? WORKDIR.$this->dir_cache : WORKDIR.$this->dir_cache.'/'.$mod;
	}

	function modshort($mod)
	{
		global $db, $basepref, $tm, $lang;

		$mod_path = ($mod != 'pages') ? $mod.'/' : '';
		$file_path = WORKDIR.$this->dir_cache.'/'.$mod_path.$mod.'.short.php';

		$output = "<?php if(!defined('DNREAD')) exit();\n";
		$inq = $db->query("SELECT * FROM ".$basepref."_pages WHERE act = 'yes' AND mods = '".$mod."'");
		if ($db->numrows($inq) > 0)
		{
			$output.= "return array(";
			while ($item = $db->fetchrow($inq))
			{
				$textshort = str_replace(array("\n", "\r"), "", $item['textshort']);
				$output.= "\n"
				."'".$item['paid']."'=>array("
				."'title'=>'".$db->escape(preparse_sp($item['title']))."',"
				."'public'=>'".$item['public']."',"
				."'cpu'=>'".$item['cpu']."',"
				."'textshort'=>'".$db->escape($textshort)."',"
				."'image_thumb'=>'".$item['image_thumb']."',"
				."'image_align'=>'".$item['image_align']."',"
				."'image_alt'=>'".$db->escape(preparse_sp($item['image_alt']))."'"
				."),";
			}
			$output = chop($output, ',');
			$output.= "\n);\n";

			if (
				! file_exists($file_path) OR
				(is_file($file_path) AND is_writable($file_path))
			) {
				file_put_contents($file_path, $output, LOCK_EX);
			}
			else
			{
				$tm->errorbox($lang['not_writable'].': '.$file_path);
			}
		}
		else
		{
			unlink($file_path);
		}
	}
}
