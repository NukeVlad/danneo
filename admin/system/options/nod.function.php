<?php
/**
 * File:        /admin/system/options/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Функция выбора и соответствия шаблонов модам
 */
function find_sel($sel, $find)
{
	$viewsel = '';
	foreach ($sel as $k => $v) {
		$viewsel.='<option value="'.$v.'"'.(($v == $find) ? ' selected' : '').'>'.$v.'</option>';
	}
	return $viewsel;
}

/**
 * Функция вставки позиций блоков в шаблон
 -------------------------------------------*/
function InsertInfo($id, $list, $element, $zone)
{
	global $lang;

	$newlist = $newzone = '';
	if (is_array($list))
	{
		foreach ($list as $k =>$v)
		{
			$title = $lang['all_col'].' - '.$v['total'];
			if (isset($element[$k]) AND is_array($list))
			{
				foreach ($element[$k] as $ek =>$ev) {
					$title.= '<br />'.$ev['block_name'];
				}
			}
			$newlist.= '<a href="javascript:$.insertinfo(\''.$id.'\',\''.$v['positcode'].'\')" title="'.$title.'">{'.$v['positcode'].'}</a> , ';
		}
		$newlist = substr($newlist, 0, -3);
	}
	if (is_array($zone))
	{
		foreach ($zone as $k =>$v)
		{
			$title = $lang['all_col'].' - '.$v['total'];
			$newzone.= '<a href="javascript:$.insertinfo(\''.$id.'\',\''.$v['banzoncode'].'\')" title="'.$title.'">{'.$v['banzoncode'].'}</a> , ';
		}
		$newzone = substr($newzone, 0, -3);
	}
	echo '<div class="pad al">'.$lang['block_posit'].' : <strong>'.$newlist.'</strong><br />'.$lang['banner_zone'].' : <strong>'.$newzone.'</strong></div>';
}

/**
 * Функция замены сущностей
 */
function lc($n)
{
	$lc = str_replace(array('&lt;', '&gt;', '&quot;', '&#039;', '&amp;', '<', '>', '"', "'", '|', '{', '}'), '', $n);
	return strip_tags(substr($lc, 0, 255));
}
