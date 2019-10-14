<?php
/**
 * File:        /core/classes/Rotator.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Banner Rotation
 */
class Rotator
{
	public $advcache = '';

	/**
	 * Class Rotator _contructor
	 */
	public function __construct()
	{
	}

	function banners($contents)
	{
		global $db, $basepref, $global, $config, $tm;

		if ( ! is_array($this->advcache))
		{
			$this->advcache = array();

			$itembanner = $db->query("SELECT * FROM ".$basepref."_banners WHERE banmods regexp '[[:<:]](".$global['dn'].")[[:>:]]' AND banlimit > banview ORDER BY MD5(RAND())");
			$arrbaner = array();
			while ($bitem = $db->fetchrow($itembanner))
			{
				$arrbaner[$bitem['banzonid']] = $bitem;
			}

			if ( ! isset($config['bannerzone']) OR empty($config['bannerzone']))
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_banners_zone", $config['cachetime']);
				while ($witem = $db->fetchrow($inq, $config['cache']))
				{
					$config['bannerzone'][$witem['banzonid']] = array('code' => $witem['banzoncode']);
				}
			}

			$in = null;
			if (isset($config['bannerzone']) AND is_array($config['bannerzone']))
			{
				foreach ($config['bannerzone'] as $key => $val)
				{
					if (isset($arrbaner[$key]))
					{
						$in.= $arrbaner[$key]['banid'].',';
						if ($arrbaner[$key]['bantype'] == 'click')
						{
							$bnn = ($arrbaner[$key]['banimg']) ? '<img src="'.SITE_URL.'/'.trim($arrbaner[$key]['banimg']).'" alt="'.$arrbaner[$key]['bantitle'].'" />' : $arrbaner[$key]['bantitle'];
							$insert = '<a href="'.SITE_URL.'/index.php?banid='.$arrbaner[$key]['banid'].'" target="_blank">'.$bnn.'</a>';
						}
						else
						{
							$insert = (isset($arrbaner[$key]['bancode'])) ? $arrbaner[$key]['bancode'] : '';
						}
						$this->advcache[$val['code']] = $insert;
					}
					else
					{
						$this->advcache[$val['code']] = null;
					}
				}
			}

			if (strlen($in) > 1)
			{
				$in = mb_substr($in, 0, -1);
				$db->query("UPDATE ".$basepref."_banners SET banview = banview + 1 WHERE banid IN (".$in.")");
			}
		}
		return $tm->parse($this->advcache, $contents);
	}
}
