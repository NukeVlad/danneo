<?php
/**
 * File:        /admin/core/classes/Cache.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Cache
 */
class Cache
{
	public $outcache     = '/cache/';
	public $outlangcache = '/cache/lang/';
	public $extcache     = 'php';
	public $edition      = 'standart';
	public $encoding     = 'UTF-8';
	public $extlangcache = 'xml';
	public $extinfocache = 'tpl';
	public $output       = '';

	/**
	 * The constants
	 */
	public $defcache = array
	(
		1 => 'CACHESET',
		2 => 'CACHLANG',
		3 => 'CACHLAST',
		4 => 'CACHELOGIN',
		5 => 'CACHEMENU',
		6 => 'CACHEBLOCK',
		7 => 'SEOLINK',
		8 => 'CAHECOUNTRY'
	);

	/**
	 * Switch
	 */
	public $varcache = array
	(
		1 => 'config',
		2 => 'lang',
		3 => 'last',
		4 => 'login',
		5 => 'menu',
		6 => 'block',
		7 => 'seo',
		8 => 'country'
	);

	public function __construct() { }

	/**
	 * The basic write function
	 */
	public function cachewrite($ids)
	{
		global $tm, $lang;

		$this->is_check();
		$file_write = WORKDIR.$this->outcache.'cache.'.$this->varcache[$ids].'.'.$this->extcache;

		if ( ! file_exists($file_write))
		{
			file_put_contents($file_write, NULL);
			chmod($file_write, 0666);
		}

		if (is_writable($file_write))
		{
			$php_write = fopen($file_write, 'wb');
			fputs($php_write, "<?php if(!defined('DNREAD')) exit();\n".$this->output);
			fclose($php_write);
		}
		else
		{
			$tm->errorbox($lang['not_writable'].': '.$file_write);
		}
	}

	/**
	 * Cache language login
	 */
	public function cachewrite_login($ids)
	{
		global $tm, $lang;

		$this->is_check();
		$file_write = WORKDIR.$this->outlangcache.$this->varcache[$ids].'.'.$this->extcache;

		if ( ! file_exists($file_write))
		{
			file_put_contents($file_write, NULL);
			chmod($file_write, 0666);
		}

		if (is_writable($file_write))
		{
			$php_write = fopen($file_write, 'wb');
			fputs($php_write, "<?php if(!defined('ADMREAD')) exit();\n".$this->output."\n?>");
			fclose($php_write);
		}
		else
		{
			$tm->errorbox($lang['not_writable'].': '.$file_write);
		}
	}

	/**
	 * The function write with arbitrary content
	 */
	public function cachefile($name, $content, $dir = false)
	{
		global $tm, $lang;

		$this->is_check();
		$file_write = WORKDIR.$dir.$name;

		if ( ! file_exists($file_write))
		{
			file_put_contents($file_write, NULL);
			chmod($file_write, 0666);
		}

		if (is_writable($file_write))
		{
			$php_write = fopen($file_write, 'wb');
			fputs($php_write, $content);
			fclose($php_write);
		}
		else
		{
			$tm->errorbox($lang['not_writable'].': '.$name);
		}
	}

	/**
	 * Checking existence of file
	 */
	public function is_exists($name)
	{
		global $tm, $lang;

		if ( ! file_exists($name))
		{
			$tm->errorbox($lang['dir_creat_error'].': '.$name);
		}

		if ( ! is_writable($name))
		{
			$tm->errorbox($lang['not_writable'].': '.$name);
		}
	}

	public function is_check()
	{
		$this->is_exists(WORKDIR.$this->outcache);
		$this->is_exists(WORKDIR.$this->outlangcache);
	}

	/**
	 * The basic configuration file system
	 * Example: $cache->cachesave(1);
	 */
	public function cachesave($ids = false)
	{
		$this->is_check();
		global $db, $basepref, $namebase, $PLATFORM, $conf, $lang, $mods;

		$unslashes = array(
			'site_menu', 'datainteg', 'seolink', 'social', 'currencys',
			'status', 'taxes', 'weights', 'sizes', 'mail_list_mime', 'mail_smtp', 'vcard', 'review_bad', 'mods', 'user_upload', 'groups'
		);

		if ($ids == false)
		{
			return;
		}

		$rea_mod = array();
		$inq = $db->query("SELECT file FROM ".$basepref."_mods ORDER BY posit");
		while($item = $db->fetchassoc($inq))
		{
			$rea_mod[] = $item['file'];
		}

		switch($ids)
		{
			case 1 :

				// Not setopt
				$not_opt = implode("','", array_merge($rea_mod, array('apanel')));

				// Not setname
				$not_name = "'user_upload','number','lastopt','lastrep','acookname'";

				$settings = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt NOT IN ('".$not_opt."') AND setname NOT IN (".$not_name.")");

				/**
				 * Global settings
				 */
				$this->output.= "// Global settings\n";
				$this->output.= "\$config = array(";
				while ($item = $db->fetchrow($settings))
				{
					$set_val = ((in_array($item['setname'], $unslashes)) ? $item['setval'] : addslashes($item['setval']));
					$this->output.= "\n'".$item['setname']."'=>'".$set_val."',";
				}

				/**
				 * Modules setting
				 */
				// Not setname
				$not_name_mod = "'agreement'";
				$this->output.= "\n// Modules setting";
				foreach ($rea_mod as $mods)
				{
					$setmod = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".$mods."' AND setname NOT IN (".$not_name_mod.") ORDER BY setid");
					$this->output.= "\n'".$mods."'=>array(";
					while ($item = $db->fetchrow($setmod))
					{
						$set_val = ((in_array($item['setname'], $unslashes)) ? $item['setval'] : addslashes($item['setval']));
						$this->output.= "\n  '".$item['setname']."'=>'".$set_val."',";
					}
					if (substr($this->output, -1) == ',') {
						$this->output = substr($this->output, 0, -1);
					}
					$this->output.= "\n),";
				}

				/**
				 * Modules data
				 */
				$this->output.= "\n// Modules data\n";
				$temp = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes' ORDER BY posit");
				$this->output.= "'mod'=>array(";
				while ($mod = $db->fetchrow($temp))
				{
					$this->output.= "\n  '".$mod['file']."'=>array(";
					$this->output.= "'id'=>'".addslashes($mod['id'])."',";
					$this->output.= "'name'=>'".addslashes($mod['name'])."',";
					$this->output.= "'custom'=>'".addslashes($mod['custom'])."',";
					$this->output.= "'keywords'=>'".addslashes($mod['keywords'])."',";
					$this->output.= "'descript'=>'".addslashes($mod['descript'])."',";
					$this->output.= "'temp'=>'".addslashes($mod['temp'])."',";
					$this->output.= "'map'=>'".addslashes($mod['map'])."',";
					$this->output.= "'seo'=>'".addslashes($mod['linking'])."',";
					$this->output.= "'parent'=>'".addslashes($mod['parent'])."'";
					$this->output.= "),";
				}
				if (substr($this->output, -1) == ',') {
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "\n),\n";

				/**
				 * Smilies
				 */
				$this->output.= "// Smilies\n";
				$smilie_temp = $db->query("SELECT * FROM ".$basepref."_smilie ORDER BY posit");
				$this->output.= "'smilie'=>array(";
				while ($smilie = $db->fetchrow($smilie_temp))
				{
					$this->output.= "\n  '".$smilie['smid']."'=>array(";
					$this->output.= "'code'=>'".addslashes($smilie['smcode'])."',";
					$this->output.= "'alt'=>'".addslashes($smilie['smalt'])."',";
					$this->output.= "'img'=>'".addslashes($smilie['smimg'])."'";
					$this->output.= "),";
				}
				if (substr($this->output, -1) == ',') {
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "\n),\n";

				/**
				 * Group
				 */
				if ($db->tables("user_group"))
				{
					$this->output.= "// Group\n";
					$this->output.= "'group'=>array(";
					$group_temp = $db->query("SELECT * FROM ".$basepref."_user_group");
					while ($group = $db->fetchrow($group_temp))
					{
						$this->output.= "\n  '".$group['gid']."'=>array(";
						$this->output.= "'gid'=>'".addslashes($group['gid'])."',";
						$this->output.= "'fid'=>'".addslashes($group['fid'])."',";
						$this->output.= "'title'=>'".addslashes($group['title'])."'";
						$this->output.= "),";
					}
					if (substr($this->output, -1) == ',') {
						$this->output = substr($this->output, 0, -1);
					}
					$this->output.= "\n),\n";
				}

				/**
				 * Control questions
				 */
				if ($conf['control'] == 'yes')
				{
					$this->output.= "// Control questions\n";
					$this->output.= "'controls'=>array(";
					$control_temp = $db->query("SELECT * FROM ".$basepref."_control");
					$i = 0;
					while ($control = $db->fetchrow($control_temp))
					{
						$this->output.= "\n  '".$i."'=>array(";
						$this->output.= "'cid'=>'".addslashes($control['cid'])."',";
						$this->output.= "'issue'=>'".addslashes($control['issue'])."',";
						$this->output.= "'response'=>'".addslashes($control['response'])."'";
						$this->output.= "),";
						$i ++;
					}
					if (substr($this->output, -1) == ',') {
						$this->output = substr($this->output, 0, -1);
					}
					$this->output.= "\n),\n";
				}

				$this->output.= "// Banners\n";
				$inq = $db->query("SELECT * FROM ".$basepref."_banners_zone");
				$this->output.= "'bannerzone'=>array(";
				if ($db->numrows($inq) > 0)
				{
					while ($witem = $db->fetchrow($inq))
					{
						$this->output.= "\n  '".$witem['banzonid']."'=>array(";
						$this->output.= "'code'=>'".$witem['banzoncode']."',";
						$this->output.= "'name'=>'".$witem['banzonname']."'";
						$this->output.= "),";
					}
				}
				if (substr($this->output, -1) == ',') {
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "\n),\n";

				$this->output.= "// Banner empty\n";
				$this->output.= "'banzoncode_empty'=>array(";
				$banzoncode = $db->query("SELECT * FROM ".$basepref."_banners_zone");
				while ($zoncode = $db->fetchrow($banzoncode))
				{
					$this->output.= "'".$zoncode['banzoncode']."'=>'',";
				}
				if (substr($this->output, -1) == ',') {
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "\n),\n";

				$this->output.= "// List field\n";
				$tables = $site_field = array();

				// List field main site
				$def_ing = $db->query("SHOW TABLES FROM ".$namebase);
				while($def_name = $db->fetchrow($def_ing))
				{
					$def_tables[]= $def_name[0];
				}
				foreach ($def_tables as $def_table)
				{
					$def_columns = $db->query("SHOW COLUMNS FROM ".$def_table);
					while($def_col = $db->fetchassoc($def_columns))
					{
						$site_field[0][] = $def_col['Field'];
					}
				}
				$site_field[0] = array_unique($site_field[0]);

				// List field platform
				if ( ! empty($PLATFORM))
				{
					foreach ($PLATFORM as $pid => $plat_base)
					{
						$plat_ing = $db->query("SHOW TABLES FROM ".$plat_base['base']);
						while($plat_name = $db->fetchrow($plat_ing))
						{
							$plat_tables[]= $plat_name[0];
						}
						foreach ($plat_tables as $plat_table)
						{
							$plat_columns = $db->query("SHOW COLUMNS FROM ".$plat_table);
							while($plat_col = $db->fetchassoc($plat_columns))
							{
								$site_field[$pid][] = $plat_col['Field'];
							}
						}
						$site_field[$pid] = array_unique($site_field[$pid]);
					}
				}

				// List field out
				$this->output.= "'list_field'=>array(";
				foreach ($site_field as $pids => $fields)
				{
					$fields = implode("|", array_unique($fields));
						$this->output.= "\n  '".$pids."'=>'".$fields."',";
				}
				if (substr($this->output, -1) == ',') {
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "\n));\n";

				/**
				 * Position blocks
				 */
				$this->output.= "// Position blocks";
				$block_posit = $db->query("SELECT * FROM ".$basepref."_block_posit");
				while ($block = $db->fetchrow($block_posit))
				{
					$this->output.= "\n\$global['insert']['".$block['positcode']."'] = null;";
				}

				/**
				 * Define
				 */
				$this->output.= "\n// Define";
				$this->output.= "\ndefine('".$this->defcache[1]."', 1);";
			break;
			default :
				$this->output = '';
			break;
		}
		if ( ! empty($this->output))
		{
			$this->cachewrite($ids);
		}
	}
}
