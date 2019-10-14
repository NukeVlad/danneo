<?php
/**
 * File:        /admin/core/classes/Lang.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Lang
 */
class Lang
{
	public $setid = 0;
	public $langs = FALSE;

	public function __construct($langs)
	{
		$this->setid;
		$this->langs = ($langs) ? TRUE : FALSE;
	}

	public function imp_group($file, $type = TRUE)
	{
		global $db, $basepref, $conf;

		$xml = new XML();
		if ($type) {
			$xml->read($file);
		} else {
			$xml->parse($file);
		}
		$data = $xml->parseout;

		if ( ! empty($data) AND $data['type'] == 'update')
		{
			if (isset($data['set'][0]))
			{
				foreach ($data['set'] as $val)
				{
					if ($val['name'] != 'empty')
					{
						$hash = preparse($val['name'], THIS_MD_5);
						$is_setid = $db->query
										(
											"SELECT langsetid FROM ".$basepref."_language_setting
											 WHERE langsetmd5 = '".$hash."' AND langpackid = '".$conf['langid']."'"
										);
						if ($db->numrows($is_setid) > 0)
						{
							$la = $db->fetchrow($is_setid);
							$setid = $la['langsetid'];
							$this->setid = 0;
						}
						else
						{
							$db->query
								(
									"INSERT INTO ".$basepref."_language_setting VALUES (
									 NULL, '".$conf['langid']."', '".$db->escape($val['name'])."', '".$db->escape($hash)."'
									)"
								);

							$setid = $db->insertid();
							$this->setid = $setid;
						}

						if (isset($val['lang'][0]))
						{
							foreach ($val['lang'] as $tag)
							{
								if ($tag['name'] AND $tag['name'] != 'empty' AND  ! empty($tag['vals']))
								{
									if ($this->is_lang($tag['name'])) {
										$this->up_lang($tag['name'], $tag['cache'], $tag['vals']);
									} else {
										$this->ins_lang($setid, $tag['name'], $tag['cache'], $tag['vals']);
									}
								}
							}
						}
						else
						{
							if (isset($val['lang']['name']) AND $val['lang']['name'] != 'empty' AND ! empty($val['lang']['vals']))
							{
								if ($this->is_lang($val['lang']['name'])) {
									$this->up_lang($val['lang']['name'], $val['lang']['cache'], $val['lang']['vals']);
								} else {
									$this->ins_lang($setid, $val['lang']['name'], $val['lang']['cache'], $val['lang']['vals']);
								}
							}
						}
					}
				}
			}
			else
			{
				if ($data['set']['name'] != 'empty')
				{
					$hash = preparse($data['set']['name'], THIS_MD_5);
					$is_setid = $db->query
									(
										"SELECT langsetid FROM ".$basepref."_language_setting
										 WHERE langsetmd5 = '".$hash."' AND langpackid = '".$conf['langid']."'"
									);
					if ($db->numrows($is_setid) > 0)
					{
						$la = $db->fetchrow($is_setid);
						$setid = $la['langsetid'];
						$this->setid = 0;
					}
					else
					{
						$db->query
							(
								"INSERT INTO ".$basepref."_language_setting VALUES (
								 NULL, '".$conf['langid']."', '".$db->escape($data['set']['name'])."', '".$db->escape($hash)."'
								 )"
							);

						$setid = $db->insertid();
						$this->setid = $setid;
					}

					if (isset($data['set']['lang'][0]))
					{
						foreach ($data['set']['lang'] as $tag)
						{
							if ($tag['name'] AND $tag['name'] != 'empty' AND  ! empty($tag['vals']))
							{
								if ($this->is_lang($tag['name'])) {
									$this->up_lang($tag['name'], $tag['cache'], $tag['vals']);
								} else {
									$this->ins_lang($setid, $tag['name'], $tag['cache'], $tag['vals']);
								}
							}
						}
					}
					else
					{
						if (isset($data['set']['lang']['name']) AND $data['set']['lang']['name'] != 'empty' AND ! empty($data['set']['lang']['vals']))
						{
							if ($this->is_lang($data['set']['lang']['name'])) {
								$this->up_lang($data['set']['lang']['name'], $data['set']['lang']['cache'], $data['set']['lang']['vals']);
							} else {
								$this->ins_lang($setid, $data['set']['lang']['name'], $data['set']['lang']['cache'], $data['set']['lang']['vals']);
							}
						}
					}
				}
			}
		}
		else
		{
			return false;
		}
	}

	private function read($file)
	{
		if ( ! $file) {
			return false;
		}
		$fp = fopen($file, 'r');
		$data = fread($fp, filesize($file));
		fclose($fp);

		return $data;
	}

	private function up_lang($name, $cache, $vals)
	{
		global $db, $basepref;

		$db->query
		(
			"UPDATE ".$basepref."_language
			 SET langvals  = '".$db->escape($vals)."', langcache = '".$cache."'
			 WHERE langvars = '".$db->escape($name)."'"
		);
	}

	private function ins_lang($setid, $name, $cache, $vals)
	{
		global $db, $basepref, $conf;

		$db->query
		(
			"INSERT INTO ".$basepref."_language VALUES (
			 NULL, '".$conf['langid']."', '".$setid."', '".$db->escape($name)."', '".$db->escape($vals)."', '".$db->escape($vals)."', '".$cache."'
			)"
		);
	}

	private function is_lang($name)
	{
		global $db, $basepref;

		if ($this->langs)
			return FALSE;

		$result = FALSE;
		$inq = $db->numrows(
						$db->query("SELECT langid FROM ".$basepref."_language WHERE langvars = '".$db->escape($name)."'")
					);
		if ($inq > 0) {
			$result = TRUE;
		}
		return $result;
	}
}
