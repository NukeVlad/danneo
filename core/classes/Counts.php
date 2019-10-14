<?php
/**
 * File:        /admin/core/classes/Counts.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Counts
 */
class Counts
{
	public $workcount = array();
	public $newcount = array();
	public $table = '';
	public $tableid = '';
	public $total = 0;

	function __construct($table, $tableid)
	{
		global $db, $basepref;

		if (empty($table) OR empty($tableid)) {
			return false;
		}
		$this->table = $table;
		$this->tableid = $tableid;
		$inq = $db->query("SELECT catid FROM ".$basepref."_".$this->table."_cat");
		if ($db->numrows($inq) > 0)
		{
			while ($item = $db->fetchassoc($inq))
			{
				$count = $db->fetchassoc($db->query("SELECT COUNT(".$this->tableid.") AS total FROM ".$basepref."_".$this->table."
														WHERE catid='".$item['catid']."' AND act = 'yes'"));
				$this->total = 0;
				$intot = (int)($this->level($item['catid']) + $count['total']);
				$db->query("UPDATE ".$basepref."_".$this->table."_cat SET total = '".$intot."' WHERE catid = '".$item['catid']."'");
			}
		}
		$this->acc(0);
	}

	function level($catid = 0)
	{
		global $db, $basepref;

		$inquiry = $db->query("SELECT * FROM ".$basepref."_".$this->table."_cat WHERE parentid = '".$catid."'");
		if ($db->numrows($inquiry) > 0)
		{
			while ($item = $db->fetchassoc($inquiry))
			{
				$count = $db->fetchassoc($db->query("SELECT COUNT(".$this->tableid.") AS total FROM ".$basepref."_".$this->table."
														WHERE catid='".$item['catid']."' AND act = 'yes'"));
				$this->total += $count['total'];
				$this->level($item['catid']);
			}
		}
		return $this->total;
	}

	function acc($cid = 0)
	{
		global $db, $basepref;

		$inquiry = $db->query("SELECT * FROM ".$basepref."_".$this->table."_cat WHERE parentid = '".$cid."'");
		if ($db->numrows($inquiry) > 0)
		{
			while ($item = $db->fetchassoc($inquiry))
			{
				if ($item['access'] == 'user')
				{
					$db->query("UPDATE ".$basepref."_".$this->table."_cat SET access='".$item['access']."',groups='".$item['groups']."' WHERE parentid='".$item['catid']."'");
				}
				$this->acc($item['catid']);
			}
		}
	}
}
