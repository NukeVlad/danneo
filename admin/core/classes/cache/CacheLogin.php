<?php
/**
 * File:        /admin/core/classes/cache/CacheLogin.php
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
 * Class CacheLogin
 */
class CacheLogin extends Cache
{
	public function __construct() { }

	function cachelogin()
	{
		global $db, $basepref, $conf, $sess, $lang;

		$this->is_check();

		$admins = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'apanel'"));
		$label = Json::decode($admins['setval']);

		$this->output = "// SETTINGS\n";
		$this->output.= "define(\"SKIN_DEF\", '".$label[1]['skin']."');\n";
		$this->output.= "define(\"VERSION\", '".$conf['version']."');\n";
		$this->output.= "define(\"CHAR_DEF\", '".$conf['langcharset']."');\n";
		$this->output.= "define(\"CODE_DEF\", '".$conf['langcode']."');\n";
		$this->output.= "define(\"LIFE_ADMIN\", '".LIFE_ADMIN."');\n";
		$this->output.= "// LANGUAGE\n";
		$inq = $db->query("SELECT langvars, langvals, langsetid FROM ".$basepref."_language WHERE langpackid = '".$conf['langid']."' AND langsetid = '".$conf['langloginset']."'");
		if ($db->numrows($inq) > 0)
		{
			$this->output.="\$lang = array(";
			while ($al = $db->fetchrow($inq))
			{
				$this->output.="\n'".$al[0]."' => '".addslashes($al[1])."',";
			}
			if (substr($this->output, -1) == ',') {
				$this->output = substr($this->output, 0, -1);
			}
			$this->output.="\n);\n";
		}
		$this->output.= "define('".$this->defcache[4]."', 1);";
		$this->cachewrite_login(4);
	}
}
