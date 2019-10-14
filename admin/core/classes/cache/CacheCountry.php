<?php
/**
 * File:        /admin/core/classes/cache/CacheCountry.php
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
 * Class CacheCountry
 */
class CacheCountry extends Cache
{
	public function __construct() { }

	function cachecountry()
	{
		global $db, $basepref, $conf, $lang;

		$this->is_check();
		$r = array();
		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($region = $db->fetchrow($inq))
		{
			$r[$region['countryid']][$region['regionid']] = $region;
		}
		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		if ($db->numrows($inq) > 0)
		{
			$this->output = "// COUNTRY\n";
			$this->output.= "return array(";
			while ($country = $db->fetchrow($inq))
			{
				$this->output.="\n'".$country['countryid']."'=>array("
								."\n\t'countryname'=>'".addslashes($country['countryname'])."',"
								."\n\t'icon'=>'".addslashes($country['icon'])."',"
								."\n\t'iso2'=>'".addslashes($country['iso2'])."',"
								."\n\t'iso3'=>'".addslashes($country['iso3'])."',"
								."\n\t'iso'=>'".addslashes($country['iso'])."',"
								."\n\t'region'=>array(";
				if (isset($r[$country['countryid']]))
				{
					foreach ($r[$country['countryid']] as $k => $v)
					{
						$this->output.= "'".$k."'=>'".addslashes($v['regionname'])."',";
					}
					if (substr($this->output, -1) == ',')
					{
						$this->output = substr($this->output, 0, -1);
					}
				}
				$this->output.= ")";
				$this->output.= "\n),";
			}
			if (substr($this->output, -1) == ',')
			{
				$this->output = substr($this->output, 0, -1);
			}
			$this->output.= "\n);";
			$this->output.= "\ndefine('".$this->defcache[8]."',1);";
			$this->cachewrite(8);
		}
	}
}
