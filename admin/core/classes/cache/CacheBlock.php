<?php
/**
 * File:        /admin/core/classes/cache/CacheBlock.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace DN\Cache;
use Cache;
use \Json;

/**
 * Class CacheBlock
 */
class CacheBlock extends Cache
{
	public function __construct() { }

	function cacheblock()
	{
		global $db, $basepref, $conf, $sess, $lang;

		$this->is_check();

		$unsetmod = array();
		$this->output = "// CACHE BLOCK\n";
		$binq = $db->query("SELECT * FROM ".$basepref."_block WHERE block_active = 'yes' ORDER BY block_posit");
		$this->output.= "\$site_blocks = array(";
		while ($item = $db->fetchassoc($binq))
		{
			// block mods
			$unsetmod = Json::decode($item['block_mods']);
			$setmod = null;
			foreach ($unsetmod as $k => $v)
			{
				$scheme = null;
				foreach ($v as $sk => $sv)
				{
					$label = null;
					if (is_array($sv)) {
						foreach ($sv as $lk => $lv) {
							$label.= "'".$lk."'=>'".$lv."',";
						}
						if (substr($label, -1) == ',') {
							$label = substr($label, 0, -1);
						}
						$label = "array(".$label."),";
					} else {
						$label = "'".$sv."',";
					}
					if (substr($label, -1) == ',') {
						$label = substr($label, 0, -1);
					}
					$scheme.= "'".$sk."'=>".$label.",";
				}
				if (substr($scheme, -1) == ',') {
					$scheme = substr($scheme, 0, -1)."";
				}
				$setmod.= "'".$k."'=>array(".$scheme."),";
			}
			if (substr($setmod, -1) == ',') {
				$setmod = substr($setmod, 0, -1);
			}

			// block setting
			$unsetting = Json::decode($item['block_setting']);
			$settings = null;
			if ( ! empty($unsetting) ) {
				foreach ($unsetting as $k => $v)
				{
					$settings.= "'".$k."'=>'".$v."',";
				}
				if (substr($settings, -1) == ',') {
					$settings = substr($settings, 0, -1);
				}
				$settings = "array(".$settings.")";
			} else {
				$settings = "''";
			}

			// block group
			$ungroup = Json::decode($item['block_group']);
			$groups = null;
			if ( ! empty($ungroup) ) {
				foreach ($ungroup as $k => $v) {
					$groups.= "".$k.",";
				}
				if (substr($groups, -1) == ',') {
					$groups = substr($groups, 0, -1);
				}
				$groups = "array(".$groups.")";
			} else {
				$groups = "''";
			}

			// block output
			$this->output.= "\n'".$item['blockid']."'=>array(";
			$this->output.= "\n\t'block_side'=>'".addslashes($item['block_side'])."',";
			$this->output.= "\n\t'block_file'=>'".addslashes($item['block_file'])."',";
			$this->output.= "\n\t'block_name'=>'".addslashes($item['block_name'])."',";
			$this->output.= "\n\t'block_cont'=>'".addslashes($item['block_cont'])."',";
			$this->output.= "\n\t'block_temp'=>'".addslashes($item['block_temp'])."',";
			$this->output.= "\n\t'block_mods'=>array(".$setmod."),";
			$this->output.= "\n\t'block_access'=>'".$item['block_access']."',";
			$this->output.= "\n\t'block_setting'=>".$settings.",";
			$this->output.= "\n\t'block_group'=>".$groups."";
			$this->output.= "\n),";
		}
		if (substr($this->output, -1) == ',')
		{
			$this->output = substr($this->output, 0, -1);
		}
		$this->output.= "\n);\n";
		$this->output.= "define('".$this->defcache[6]."',1);";
		$this->cachewrite(6);
	}
}
