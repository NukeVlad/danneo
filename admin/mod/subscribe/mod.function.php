<?php
/**
 * File:        /admin/mod/subscribe/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Send Formats in Html
 -------------------------*/
function this_sub($old, $one = 0, $two)
{
	$new = array();
	foreach($old as $key => $val)
	{
		if ($key > $one AND $key <= ($one + $two))
		{
			$new[$key]['uname'] = $val['uname'];
			$new[$key]['umail'] = $val['umail'];
		}
	}
	return $new;
}
