<?php
/**
 * File:        /admin/core/classes/cache/CacheOption.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace DN\Cache;
use Cache;

/**
 * Class CacheOption
 */
class CacheOption extends Cache
{
	public function __construct() { }

	/**
	 * Option write function
	 */
	function write_option($mod)
	{
		global $tm, $lang;

		$this->is_check();
		$file_write = WORKDIR.$this->outcache.$mod.'.'.$this->extcache;
		if ( ! file_exists($file_write))
		{
			file_put_contents($file_write, NULL);
			chmod($file_write, 0666);
		}
		if (is_writable($file_write))
		{
			$php_write = fopen($file_write, 'wb');
			fputs($php_write, "<?php if(!defined('DNREAD')) exit();\n".$this->output."\n");
			fclose($php_write);
		}
		else
		{
			$tm->errorbox($lang['not_writable'].': '.$file_write);
		}
	}

	/**
	 * Function Cache Option
	 */
	function cacheoption($WORKMOD)
	{
		global $db, $basepref, $conf, $lang;

		$this->output.= "// ".$WORKMOD." option\n";
		$inq = $db->query("SELECT * FROM ".$basepref."_".$WORKMOD."_option ORDER BY posit ASC");
		$this->output.= "\$option = array(";
		if ($db->numrows($inq) > 0)
		{
			while ($witem = $db->fetchrow($inq))
			{
				$this->output.= "\n'".$witem['oid']."'=>array(";
				$this->output.= "'title'=>'".addslashes($witem['title'])."',";
				$this->output.= "'type'=>'".addslashes($witem['type'])."',";
				$this->output.= "'search'=>'".addslashes($witem['search'])."',";
				$this->output.= "'buy'=>'".addslashes($witem['buy'])."',";
				$this->output.= "'value'=>array(";
				$vinq = $db->query("SELECT * FROM ".$basepref."_".$WORKMOD."_option_value WHERE oid = '".$witem['oid']."' ORDER BY posit ASC");
				if ($db->numrows($vinq) > 0)
				{
					while ($vitem = $db->fetchrow($vinq))
					{
						$this->output.= "'".$vitem['vid']."'=>array(";
						$this->output.= "'title'=>'".addslashes($vitem['title'])."',";
						$this->output.= "'modify'=>'".addslashes($vitem['modify'])."',";
						$this->output.= "'modvalue'=>'".addslashes($vitem['modvalue'])."'),";
					}
					if (substr($this->output, -1) == ',')
					{
						$this->output = substr($this->output, 0, -1);
					}
				}
				$this->output.= "),";
				if (substr($this->output, -1) == ',')
				{
					$this->output = substr($this->output, 0, -1);
				}
				$this->output.= "),";
			}
		}
		if (substr($this->output, -1) == ',')
		{
			$this->output = substr($this->output, 0, -1);
		}
		$this->output.= ");";
		$this->write_option($WORKMOD.'.option');
	}
}
