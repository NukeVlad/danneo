<?php
/**
 * File:        /admin/system/menu/nod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Функция вывода ссылок
 */
function print_menu($id = 0, $ins = 1, $pre = NULL)
{
	global $rows, $mid, $lang, $sess;

	if ( ! isset($rows[$id]))
		return;

	$css = NULL;
	foreach ($rows[$id] as $k => $in)
	{
		$css = ($ins == 1) ? ' bold' : '';

		echo '	<tr class="list">
					<td class="al nowrap pw25"><span class="light"> &bull; '.$pre.'</span> &nbsp;<span class="site'.$css.'">'.$in['name'].'</span></td>
					<td class="black">'.$in['link'].'</td>
					<td><input type="text" value="'.$in['posit'].'" name="posit['.$in['id'].']" size="3" maxlength="3"></td>
					<td>'.( ! empty($in['icon']) ? '<img src="'.WORKURL.'/'.$in['icon'].'" alt="'.preparse_un($in['name']).'" class="icon" alt="" />' : '').'</td>
					<td>'.$in['title'].'</td>
					<td>'.$in['css'].'</td>
					<td>'.str_replace('_self', '', $in['target']).'</td>';

		if ($in['parent'] == 0)
		{
			echo '	<td class="gov">
						<a href="index.php?dn=edit&amp;mid='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png"></a>
						<a href="index.php?dn=linkadd&amp;mid='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['link_add'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png"></a>
						<a href="index.php?dn=del&amp;mid='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png"></a>
					</td>';

			$mid = $in['id'];
		}
		else
		{
			echo '	<td class="gov">
						<a href="index.php?dn=linkedit&amp;mid='.$mid.'&amp;id='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png"></a>
						<a href="index.php?dn=linkadd&amp;mid='.$mid.'&amp;id='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['link_add'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png"></a>
						<a href="index.php?dn=linkdel&amp;mid='.$mid.'&amp;id='.$in['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png"></a>
					</td>';
		}

		echo '	</tr>';

		print_menu($in['id'], $ins + 1, $pre.' &bull; ');
	}

	unset($rows[$id]);
	return;
}

/**
 * Функция удаления меню
 */
function del_menu($id)
{
	global $basepref, $db;

	$id = intval($id);
	$ins = $db->numrows(
		$db->query("SELECT id FROM ".$basepref."_site_menu WHERE parent = '".$id."'")
	);

	if ($ins)
	{
		$inq = $db->query("SELECT * FROM ".$basepref."_site_menu WHERE parent = '".$id."'");
		while ($row = $db->fetchrow($inq))
		{
			$del = $row[0];
			if (del_menu($del))
			{
				$db->query("DELETE FROM ".$basepref."_site_menu WHERE id = '".$del."'");
			}
		}
	}
	else
	{
		$db->query("DELETE FROM ".$basepref."_site_menu WHERE id = '".$id."'");
		$db->increment('site_menu');
	}

	return $ins;
}

/**
 * Функция фильтрации ссылок по родителю
 */
function sub_menu($tree, $id = 0)
{
	static $cl = array();

	if ( ! isset($tree[$id]))
		return;

	foreach ($tree[$id] as $v)
	{
		$cl[] = $v['id'];
		sub_menu($tree, $v['id']);
	}

	unset($tree[$id]);
	return $cl;
}

/**
 * Функция построения дерева меню для формы (select)
 */
function select_menu($mid = 0, $level = 0, $menu = 0)
{
	global $tree, $id, $option;

	if ( ! isset($tree[$mid]))
		return;

	foreach ($tree[$mid] as $in)
	{
		$sel = $ind = $css = null;

		if ($in['id'] == $id) {
			$sel = ' selected="selected"';
		}
		if ($level == 0) {
			$css = ' class="selective"';
			$menu = $in['id'];
		} else {
			$ind = str_repeat("&nbsp;&nbsp;", $level);
		}

		$option.= '<option value="'.$menu.'.'.$in['id'].'"'.$css.$sel.'>'.$ind.preparse_un($in['name']).'</option>';
		select_menu($in['id'], $level + 1, $menu);
	}
	unset($tree[$mid]);
	return $option;
}

/**
 * Функция построения дерева меню для формы (select)
 */
function menu_cat($cid = 0, $level = 0, $mod = 0, $ignore_cat)
{
	global $selectcat, $catid, $ignore_cat, $option;

	if ( ! isset($selectcat[$cid]))
		return;

	foreach ($selectcat[$cid] as $in)
	{
		$sel = $ind = null;

		if ($in['catid'] == $catid) {
			$sel = ' selected="selected"';
		}
		if ($level == 0) {
			$ind = "&nbsp;&nbsp;";
		} else {
			$ind = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level);
		}

		if ( ! empty($ignore_cat) AND in_array($in['catname'], $ignore_cat))
		{
			$option.= '<option style="color: #123; background: #ffeef0;" value="0"'.$sel.'>'.$ind.preparse_un($in['catname']).'</option>';
		}
		else
		{
			$option.= '<option style="color: #123; background: #f1fdec;" value="index.php?dn='.$mod.'&to=cat&id='.$in['catid'].'&ccpu='.$in['catcpu'].'"'.$sel.'>'.$ind.preparse_un($in['catname']).'</option>';
		}
		menu_cat($in['catid'], $level + 1, $mod, $ignore_cat);
	}
	unset($selectcat[$cid]);
	return $option;
}

function tree_menu($tree, $mid = 0)
{
	static $cl = array();

	if ( ! isset($tree[$mid]))
		return false;

	foreach ($tree[$mid] as $v)
	{
		$cl[] = preparse_un($v['name']);
		tree_menu($tree, $v['id']);
	}

	$tree[$mid] = null;
	return $cl;
}

/**
 * Функция кеширования меню
 */
function cache_menu()
{
	global $db, $basepref;

	$conf = $db->fetchassoc(
		$db->query("SELECT setval FROM ".$basepref."_settings WHERE setname = 'cache_menu' ORDER BY setid LIMIT 1")
	);

	$type = ($conf['setval'] == 'yes') ? 'print' : 'array';

	$cache = new DN\Cache\CacheMenu;
	$cache->cachemenu($type);
}
