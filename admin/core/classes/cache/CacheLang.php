<?php
/**
 * File:        /admin/core/classes/cache/CacheLang.php
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
 * Class CacheLang
 */
class CacheLang extends Cache
{
	public function __construct() { }

	public function cachelang()
	{
		global $db, $basepref, $conf, $sess, $lang;

		$this->is_check();

		$inq = $db->query("SELECT langvars, langvals, langsetid FROM ".$basepref."_language WHERE langpackid = '".$conf['langid']."' AND langcache = '1'");
		$langdate = $this->output = '';
		if ($db->numrows($inq) > 0)
		{
			$this->output.= "// All variables\n";
			$this->output.="\$lang = array(";
			while ($al = $db->fetchrow($inq))
			{
				if ($al['langsetid'] == $conf['langdateset']) {
					$langdate.="\n'".$al[0]."'=>'".addslashes($al[1])."',";
				} else {
					$this->output.="\n'".$al[0]."'=>'".addslashes($al[1])."',";
				}
			}
			if (substr($this->output, -1) == ',') {
				$this->output = substr($this->output, 0, -1);
			}
			if (substr($langdate, -1) == ',') {
				$langdate = substr($langdate, 0, -1);
			}
			$this->output.="\n);\n";
		}
		if ($langdate)
		{
			$this->output.= "// Langdate\n";
			$this->output.="\$langdate = array(".$langdate."\n);\n";
		}
		$this->output.= "// Define\n";
		$this->output.= "define('".$this->defcache[2]."',1);";
		if ( ! empty($this->output))
		{
			$this->cachewrite(2);
		}

	}

	public function exportlang($ids = false, $lid = false, $set = false)
	{
		global $db, $basepref, $conf, $lang;

		$this->is_check();
		if ($ids == false OR $lid == false) {
			return;
		}
		$this->output ="<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		switch ($ids)
		{
			case 1 :
				$litem = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));
				$count = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_language WHERE langpackid = '".$lid."'"));
				$this->output.="<language type=\"setup\">\n";
				$this->output.=" <atr>\n";
				$this->output.="  <packname><![CDATA[".preparse_lga($litem['langpack'])."]]></packname>\n";
				$this->output.="  <codes><![CDATA[".preparse_lga($litem['langcode'])."]]></codes>\n";
				$this->output.="  <charset><![CDATA[".preparse_lga($litem['langcharset'])."]]></charset>\n";
				$this->output.="  <version><![CDATA[".preparse_lga($conf['version'])."]]></version>\n";
				$this->output.="  <author><![CDATA[".preparse_lga($litem['langauthor'])."]]></author>\n";
				$this->output.="  <total><![CDATA[".$count['total']."]]></total>\n";
				$this->output.=" </atr>\n\n";
				$setinq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");
				while ($item = $db->fetchrow($setinq))
				{
					$this->output.=" <set>\n";
					$this->output.="  <name><![CDATA[".preparse_lga($item['langsetname'])."]]></name>\n";
					if ($item['langsetid'] == $litem['langdateset']) {
						$this->output.="  <date><![CDATA[1]]></date>\n";
					} else {
						$this->output.="  <date><![CDATA[0]]></date>\n";
					}
					$linq = $db->query("SELECT * FROM ".$basepref."_language WHERE langsetid='".$item['langsetid']."' GROUP BY langid ORDER BY langid");
					$one = 0;
					while ($langit = $db->fetchrow($linq))
					{
						$this->output.="   <lang name=\"".$langit['langvars']."\" cache=\"".$langit['langcache']."\"><![CDATA[".preparse_lga($langit['langvals'])."]]></lang>\n";
						$one ++;
					}
					if ($one == 1) {
						$this->output.="   <lang name=\"empty\" cache=\"\"><![CDATA[]]></lang>\n";
					}
					$this->output.=" </set>\n";
				}
				$this->output.="</language>\n";
				$file =  "dn-".$this->edition."-".str_replace('.', '', $conf['version'])."-".$litem['langcode']."-lang.".$this->extlangcache;
			break;
			case 2 :
				$litem = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));
				$this->output.="<language type=\"update\">\n";
				foreach ($set AS $sk => $tag)
				{
					$setinq = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_setting WHERE langsetid = '".$sk."'"));
					$this->output.=" <set>\n";
					$this->output.="  <name><![CDATA[".preparse_lga($setinq['langsetname'])."]]></name>\n";
					$linq = $db->query("SELECT * FROM ".$basepref."_language WHERE langsetid='".$sk."' GROUP BY langid ORDER BY langvars");
					$one = 0;
					while ($langit = $db->fetchrow($linq))
					{
						$this->output.="   <lang name=\"".$langit['langvars']."\" cache=\"".$langit['langcache']."\"><![CDATA[".preparse_lga($langit['langvals'])."]]></lang>\n";
						$one ++;
					}
					if ($one == 1) {
						$this->output.="   <lang name=\"empty\" cache=\"\"><![CDATA[]]></lang>\n";
					}
					$this->output.=" </set>\n";
				}
				$this->output.=" <set>\n";
				$this->output.="  <name><![CDATA[empty]]></name>\n";
				$this->output.="   <lang name=\"empty\" cache=\"\"><![CDATA[]]></lang>\n";
				$this->output.=" </set>\n";
				$this->output.="</language>\n";
				$file =  "dn-".$this->edition."-".str_replace('.', '', $conf['version'])."-".$litem['langcode']."-setting.".$this->extlangcache;
			break;
			default :
				$this->output = '';
			break;
		}

		$file_write = WORKDIR.$this->outlangcache.$file;
		if ( ! file_exists($file_write))
		{
			if (touch($file_write))
			{
				chmod($file_write, 0666);
			}
			else
			{
				die($lang['not_create'].': '.$file_write);
			}
		}

		if (is_writable($file_write))
		{
			$php_write = fopen($file_write, 'wb');
			fputs($php_write, $this->output);
			fclose($php_write);
		}
		else
		{
			die($lang['not_writable'].': '.$file_write);
		}

	}
}
