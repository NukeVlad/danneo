<?php
/**
 * File:        /admin/core/classes/cache/CacheSeolink.php
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
 * Class CacheSeolink
 */
class CacheSeolink extends Cache
{
	public function __construct() { }

	function cacheseolink()
	{
		global $db, $basepref, $conf, $lang;

		$seolink = $mod = array();
		$ing = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes' AND linking = 'yes'");
		while ($item = $db->fetchassoc($ing))
		{
			$mod[] = $item['file'];
		}

		$this->output = "// SEO LINKING";
		$this->output.= "\n\$seo = array(";
		foreach ($mod as $val)
		{
			$modlink = array();
			$sinq = $db->query("SELECT * FROM ".$basepref."_seo_anchor WHERE mods = '".$val."'");
			$this->output.="\n'".$val."'=>array(";
			while ($seo = $db->fetchassoc($sinq))
			{
				$this->output.="\n  '".$seo['said']."'=>array("
								."'count'=>'".$seo['count']."',"
								."'word'=>'".$seo['word']."',"
								."'link'=>'".$seo['link']."',"
								."'title'=>'".$seo['title']."'"
				."),";
			}
			if (substr($this->output, -1) == ',')
			{
				$this->output = substr($this->output, 0, -1);
			}
			$this->output.= "\n),";
		}
		if (substr($this->output, -1) == ',')
		{
			$this->output = substr($this->output, 0, -1);
		}
		$this->output.= ");";
		$this->cachewrite(7);
	}
}
