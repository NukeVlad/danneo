<?php
/**
 * File:        /mod/photos/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Функция рейтинга
 */
function rating($rate, $id, $current)
{
	global $config, $lang, $tm;

	if ($config['ajax'] == 'yes' AND $current == 0)
	{
		$width = intval((100 / 5) * $rate);
		return $tm->parse(array
					(
						'id'     => $id,
						'width'  => $width,
						'rate_1' => $lang['rate_1'],
						'rate_2' => $lang['rate_2'],
						'rate_3' => $lang['rate_3'],
						'rate_4' => $lang['rate_4'],
						'rate_5' => $lang['rate_5']
					),
					$tm->create('mod/'.WORKMOD.'/ajax.rating'));
	} else {
		return $r = '<img src="'.SITE_URL.'/template/'.SITE_TEMP.'/images/rating/'.$rate.'.gif" alt="'.(($rate == 0) ? $lang['rate_0'] : $lang['rate_'.$rate.'']).'" />';
	}
}
