<?php
/**
 * File:        /admin/mod/catalog/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Функция вывода категорий
 */
function catalog_cat($cid = 0, $depth = 0, $last = '', $prefix = false)
{
	global $basepref, $db, $catcache, $conf, $sess, $lang;

	if ( ! isset($catcache[$cid]))
	{
		return false;
	}

	$groups_only = array();
	if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
	{
		$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
		while ($items = $db->fetchrow($inqs)) {
			$groups_only[] =  $items['title'];
		}
	}

	foreach ($catcache[$cid] as $key => $incat)
	{
		if ($incat['parentid'] == 0)
		{
			$bg = 'main';
			$font = 'site bold';
			$mess = ' title="'.$lang['home'].'"';
		}  else {
			$bg = 'parent';
			$font = 'site';
			$mess = '';
		}

		if($last == '' AND $incat['parentid'] > 0)
		{
			$last = $incat['parentid'];
		}

		if($last > 0 AND $last == $incat['parentid'])
		{
			$bg = 'parent';
			$font = 'site';
		}

		$groupact = null;
		if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
		{
			if ( ! empty($incat['groups']))
			{
				$groups = Json::decode($incat['groups']);
				reset($groups);
				foreach ($groups as $key => $val)
				{
					$groupact.=  ' '.$groups_only[$key - 1].',';
				}
				$groupact = chop($groupact, ',');
			}
		}

		echo '	<tr class="list">
					<td class="al '.$bg.'">
						<span class="'.$font.'"'.$mess.'>'.$incat['catid'].'</span>
					</td>
					<td class="al '.$bg.'">
						<span class="'.$font.'"'.$mess.'>'.$prefix.' '.preparse_un($incat['catname']).'</span>
					</td>
					<td class="'.$bg.'">
						'.(($incat['access'] == 'user') ? ( ! empty($incat['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']).'
					</td>
					<td class="'.$bg.'">
						'.$incat['total'].'
					</td>
					<td class="'.$bg.'">';
		if($incat['icon'] != '')
		{
			echo '		<img src="'.WORKURL.'/'.$incat['icon'].'" alt="'.preparse_un($incat['catname']).'" style="max-width: 36px; max-height: 27px;" />';
		}
		echo '		</td>
					<td class="'.$bg.'">
						<input name="posit['.$incat['catid'].']" type="text" size="3" maxlength="3" value="'.$incat['posit'].'">
					</td>
					<td class="'.$bg.' gov">
						<a href="index.php?dn=catedit&amp;catid='.$incat['catid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
						<a href="index.php?dn=catadd&amp;catid='.$incat['catid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png" alt="'.$lang['all_add_sub'].'" /></a>
						<a href="index.php?dn=catdel&amp;catid='.$incat['catid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
					</td>
				</tr>';

		catalog_cat($incat['catid'], $depth + 1, $last, $prefix.' - ');
	}

	unset($catcache[$cid]);
	return;
}

/**
 * Функция удаления категорий
 ------------------------------*/
function catalog_delcat($catid, $table)
{
	global $basepref, $db;

	$catid = intval($catid);
	$inq = $db->numrows($db->query("SELECT catid FROM ".$basepref."_".$table."_cat WHERE parentid = '".$catid."'"));

	if ($inq > 0)
	{
		$inquiry = $db->query("SELECT * FROM ".$basepref."_".$table."_cat WHERE parentid = '".$catid."'");
		while ($row = $db->fetchrow($inquiry))
		{
			$catid_del = $row[0];
			if (this_delcat($catid_del, $table) != 0)
			{
				$db->query("DELETE FROM ".$basepref."_".$table."_product_option WHERE id IN (SELECT id FROM ".$basepref."_".$table." WHERE catid = '".$catid_del."')");
				$db->query("DELETE FROM ".$basepref."_".$table." WHERE catid = '".$catid_del."'");
				$db->query("DELETE FROM ".$basepref."_".$table."_cat WHERE catid = '".$catid_del."'");
			}
		}
	}
	else
	{
		$db->query("DELETE FROM ".$basepref."_".$table."_product_option WHERE id IN (SELECT id FROM ".$basepref."_".$table." WHERE catid = '".$catid."')");
		$db->query("DELETE FROM ".$basepref."_".$table." WHERE catid = '".$catid."'");
		$db->query("DELETE FROM ".$basepref."_".$table."_cat WHERE catid = '".$catid."'");
	}

	return $inq;
}

/**
 * Массив доступных полей
 */
$types = array
	(
		'text'     => 'input_texts',
		'textarea' => 'input_textarea',
		'select'   => 'input_select',
		'radio'    => 'input_radio',
		'checkbox' => 'input_checkbox'
);

/**
 * Массив сортировок категорий
 */
$catsort = array
	(
		'public' => $lang['all_data'],
		'id'     => $lang['all_id'],
		'title'  => $lang['all_name'],
		'price'  => $lang['price']
);

/**
 * Функция сортировки (листинг)
 */
function ordsort($item)
{
	global $s, $l, $ajaxlink, $p, $sess, $revs, $rev;

	$class = (($s == $item) ? ' class="work-'.$l.'-sort"' : ' class="work-sort"');
	$onclick = 'onclick="'.(($ajaxlink == 1) ? '$.ajaxget' : '$.openurl').'(\'index.php?dn=ordlist&amp;p='.$p.'&amp;ops='.$sess['hash'].(($s == $item) ? $revs : $rev.$item).'\');"';
	$items = $class.' '.$onclick;
	return $items;
}

/**
 * Функция сортировки (листинг)
 */
function isort($item)
{
	global $s, $l, $ajaxlink, $p, $sess, $revs, $rev;

	$class = (($s == $item) ? ' class="work-'.$l.'-sort"' : ' class="work-sort"');
	$onclick = 'onclick="'.(($ajaxlink == 1) ? '$.ajaxget' : '$.openurl').'(\'index.php?dn=list&amp;p='.$p.'&amp;ops='.$sess['hash'].(($s == $item) ? $revs : $rev.$item).'\');"';
	$items = $class.' '.$onclick;

	return $items;
}
