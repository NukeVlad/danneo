<?php
/**
 * File:        /admin/mod/news/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Функция сортировки (листинг)
 */
function listsort($item)
{
	global $s, $l, $ajaxlink, $p, $sess, $revs, $rev;

	$class = (($s == $item) ? ' class="work-'.$l.'-sort"' : ' class="work-sort"');
	$onclick = 'onclick="'.(($ajaxlink == 1) ? '$.ajaxget' : '$.openurl').'(\'index.php?dn=list&amp;p='.$p.'&amp;ops='.$sess['hash'].(($s == $item) ? $revs : $rev.$item).'\');"';
	$items = $class.' '.$onclick;

	return $items;
}
