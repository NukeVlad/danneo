<?php
/**
 * File:        /admin/system/menu/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Базовые константы
 */
define('READCALL', 1);
define('PERMISS', basename(__DIR__));

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $wysiwyg, $lang, $sess, $ops, $cache;

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
			$lang['site_menu']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'setmenu', 'list', 'listup', 'addsave', 'edit', 'editsave', 'del',
				'links', 'fullout', 'positup', 'linkadd', 'linkaddsave', 'linkedit', 'linkeditsave', 'linkdel'
			);

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link =	'<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, links').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_positions'].'</a>'
					.'<a'.cho('linkadd').' href="index.php?dn=linkadd&amp;ops='.$sess['hash'].'">'.$lang['link_add'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Доп. функции мода
		 */
		include('nod.function.php');

		/**
		 * Управление меню
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['site_menu'].': '.$lang['all_set'].'</caption>
						<tr>
							<td>'.$lang['cache_menu'].'</td>
							<td>
								<select name="set[cache_menu]" class="sw165">
									<option value="yes"'.(($conf['cache_menu'] == 'yes') ? ' selected' : '').'>'.$lang['included'].'</option>
									<option value="no"'.(($conf['cache_menu'] == 'no') ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['active_item'].'</td>
							<td>
								<select name="set[act_menu]" class="sw165">
									<option value="link"'.(($conf['act_menu'] == 'link') ? ' selected' : '').'>'.$lang['hyperlink'].'</option>
									<option value="tag"'.(($conf['act_menu'] == 'tag') ? ' selected' : '').'>'.$lang['generic_tag'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['active_tag'].'</td>
							<td>
								<input name="set[tag_menu]" type="text" size="25" value="<'.$conf['tag_menu'].'>" placeholder="<strong>" required="required" />
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="setmenu">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Обновление настроек меню
		 */
		if ($_REQUEST['dn'] == 'setmenu')
		{
			global $set, $sess;

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'menu'");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse_dp($set[$item['setname']]))."' WHERE setid = '".$item['setid']."'");
				}
			}
			$cache->cachesave(1);
			cache_menu();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Все меню
		 -------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_positions']
				);

			$tm->header();

			echo "	<script>
					$(function() {
						$('.code').focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>";

			$inqset = $db->query("SELECT * FROM ".$basepref."_site_menu WHERE parent = '0'");
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['site_menu'].': '.$lang['all_positions'].'</caption>
						<tr>
							<th class="none">ID</th>
							<th>'.$lang['all_name'].'</th>
							<th>'.$lang['all_temp_tag'].'</th>
							<th>CSS</th>
							<th>'.$lang['all_links'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inqset))
			{

					echo '	<tr class="list">
								<td class="ac none">'.$item['id'].'</td>
								<td class="site"><input style="width: 96%;" type="text" name="name['.$item['id'].']" size="20" value="'.$item['name'].'" /></td>
								<td><input class="code" type="text" value="{'.$item['code'].'}" size="20" readonly="readonly" class="readonly" /></td>
								<td><input type="text" name="css['.$item['id'].']" size="20" value="'.$item['css'].'" /></td>
								<td class="com"><a href="index.php?dn=links&amp;mid='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/link.png" alt="'.$lang['link_all'].'" /></a> &nbsp; ('.$item['total'].')</td>
								<td class="gov pw10 ac">
									<a href="index.php?dn=edit&amp;mid='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
									<a href="index.php?dn=del&amp;mid='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
			}
			echo '
						<tr class="tfoot">
							<td colspan="6">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="listup">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			echo '	<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_menu'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_temp_tag'].'</td>
							<td>
								<input name="code" type="text" size="50" required="required" />';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input name="name" type="text" size="50" required="required" />
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> CSS</td>
							<td>
								<input name="css" type="text" size="50" required="required" />
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="addsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Все меню (сохранение)
		 -------------------------*/
		if ($_REQUEST['dn'] == 'listup')
		{
			global $db, $basepref, $name, $css,  $conf,  $sess;

			foreach ($name as $id_name => $val_name)
			{
				if ($val_name) {
					$db->query("UPDATE ".$basepref."_site_menu SET name = '".$db->escape(preparse_sp($val_name))."' WHERE id = '".$id_name."'");
				}
			}

			foreach ($css as $id_css => $val_css)
			{
				if ($val_css) {
					$db->query("UPDATE ".$basepref."_site_menu SET css = '".$db->escape($val_css)."' WHERE id = '".$id_css."'");
				}
			}

			cache_menu();

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление меню (сохранение)
		 -------------------------------*/
		if ($_REQUEST['dn']=='addsave')
		{
			global $db, $basepref, $code, $name, $css, $conf, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_add']
				);

			if (preparse(array($code, $name, $css), THIS_GROUP_EMPTY) == 0)
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_site_menu VALUES (
						 NULL,
						 '0',
						 '".$db->escape($code)."',
						 '".$db->escape(preparse_sp($name))."',
						 '',
						 '',
						 '',
						 '0',
						 '".$db->escape($css)."',
						 '_self',
						 '0'
						 )"
					);
			}
			else
			{
				$tm->header();
				$tm->error($lang['add_menu'], null, $lang['forgot_name']);
				$tm->footer();
			}

			cache_menu();

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактирование меню
		 -----------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $mid, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$mid = preparse($mid, THIS_INT);

			$menu = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$menu[$item['id']] = $item;
			}

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_site_menu WHERE id = '".$mid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.$menu[$mid]['name'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_temp_tag'].'</td>
							<td>
								<input name="code" type="text" size="50" value="'.$item['code'].'" required="required" />';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input name="name" type="text" size="50" value="'.preparse_un($item['name']).'" required="required" />
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> CSS</td>
							<td>
								<input name="css" type="text" size="50" value="'.$item['css'].'" required="required" />
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="editsave">
								<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактирование меню
		 -----------------------*/
		if ($_REQUEST['dn']=='editsave')
		{
			global $db, $basepref, $mid, $code, $name, $conf, $css;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_edit']
				);

			$mid = preparse($mid, THIS_INT);
			if (preparse(array($code, $name, $css), THIS_GROUP_EMPTY) == 0)
			{
				$tree = array();
				$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
				while ($item = $db->fetchrow($inq))
				{
					$tree[$item['parent']][$item['id']] = $item;
				}

				$total = count(sub_menu($tree, $mid));
				$db->query
					(
						"UPDATE ".$basepref."_site_menu SET
						 code  = '".$db->escape($code)."',
						 name  = '".$db->escape(preparse_sp($name))."',
						 css   = '".$db->escape($css)."',
						 total = '".$db->escape($total)."'
						 WHERE id = '".$mid."'"
					);
			}
			else
			{
				$tm->header();
				$tm->error($lang['all_edit'], $name, $lang['forgot_name']);
				$tm->footer();
			}

			cache_menu();

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление меню
		 -----------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $mid, $ok, $conf, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_delet']
				);

			$mid = preparse($mid, THIS_INT);

			if ($ok == 'yes')
			{
				$del = del_menu($mid);
				if ($del) {
					$db->query("DELETE FROM ".$basepref."_site_menu WHERE id = '".$mid."'");
					$db->increment('site_menu');
				}

				cache_menu();

				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT name FROM ".$basepref."_site_menu WHERE id = '".$mid."'"));

				$yes = 'index.php?dn=del&amp;mid='.$mid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['name']), $yes, $not, $lang['del_menu_alert']);
				$tm->footer();
			}
		}

		/**
		 * Ссылки меню
		 ----------------*/
		if ($_REQUEST['dn'] == 'links')
		{
			global $mid, $sess;

			$mid = preparse($mid, THIS_INT);
			$menuid = $mid;
			$tree = $menu = $rows = $arr_id = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$tree[$item['parent']][$item['id']] = $menu[$item['id']] = $item;
			}

			$arr_id = sub_menu($tree, $mid);
			$in_id = ( ! empty($arr_id)) ? implode(',', $arr_id) : 0;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_positions'].'</a>',
					$mid > 0 ? $menu[$mid]['name'] : $lang['link_all']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.($mid > 0 ? $menu[$mid]['name'] : $lang['all_positions']).': '.$lang['link_all'].'</caption>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_link'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>Title</th>
							<th>CSS</th>
							<th>Target</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';

			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu WHERE id IN (".$in_id.") ORDER BY posit ASC");
			while ($item = $db->fetchassoc($inq))
			{
				$rows[$item['parent']][$item['id']] = $item;
			}

			$ignore = array();
			if (isset($tree[$mid])) {
				$ignore = array_unique(tree_menu($tree, $mid));
			}

			print_menu($mid);

			$mid = $menuid;
			$mod_array = array();
			$inq_mods = $db->query("SELECT file, name, parent FROM ".$basepref."_mods WHERE active = 'yes' ORDER BY posit");
			while ($item = $db->fetchassoc($inq_mods))
			{
				$mod_array[$item['file']] = $item;
			}

			$mod_page = array();
			if ( ! empty($mod_array) AND isset($mod_array['pages']))
			{
				$inq_page = $db->query("SELECT paid, cpu, title, mods FROM ".$basepref."_pages");
				if ($db->numrows($inq_page) > 0)
				{
					while ($item = $db->fetchassoc($inq_page))
					{
						$mod_page[$item['mods']][] = $item;
					}
				}
			}

			$css1 = 'style="color: #000; background: #ffd4d8;"'; // красный, темный
			$css2 = 'style="color: #000; background: #d8fad0;"'; // зеленый, темный
			$css3 = 'style="color: #123; background: #ffeef0;"'; // красный, светлый
			$css4 = 'style="color: #123; background: #f1fdec;"'; // зеленый, светлый

			echo '		<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="positup">
								<input type="hidden" name="direct" value="links">
								<input class="main-button" value="'.$lang['save_posit'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
			echo '	<script>
					function setkink(urllist, url, name) {
						url = document.getElementById(url);
						name = document.getElementById(name);
						var idx = urllist.selectedIndex;
						var urls = urllist.options[idx].value;
						var names = urllist.options[idx].innerHTML;
						url.value = (urls != 0) ? urls : "";
						name.value = (urls != 0) ? names.replace(/\&nbsp;/g, "") : "";
					}
					</script>';

			echo '	<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post" name="formmenu">
					<table class="work">
						<caption>'.$menu[$mid]['name'].'&nbsp; &#8260; &nbsp;'.$lang['link_add'].'</caption>';
			echo '		<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input id="name" name="name" type="text" size="70" required="required" />
								 <select name="urllist" onChange="setkink(this, \'url\', \'name\')">
									<option value="0" '.$css1.'>'.$lang['all_mod'].'</option>';
								if ( ! empty($mod_array))
								{
									foreach ($mod_array as $val)
									{
										if ($val['file'] != 'pages' AND $val['parent'] == 0)
										{
											if ( ! in_array($val['name'], $ignore)) {
			echo '								<option value="index.php?dn='.$val['file'].'" '.$css2.'>&nbsp;&nbsp;'.preparse_un($val['name']).'</option>';
											} else {
			echo '								<option value="0" '.$css1.'>&nbsp;&nbsp;'.preparse_un($val['name']).'</option>';
											}
										}
									}
								}

								$link = array();
								if ( ! empty($mod_page))
								{
									foreach ($mod_page as $mod => $link)
									{
										if ($mod == 'pages')
										{
			echo '							<option value="0" '.$css1.'>'.$lang['pages'].'</option>';
										}
										else
										{
											if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '								<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
											} else {
			echo '								<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
											}
										}

										foreach ($link as $val)
										{
											if ($mod == 'pages')
											{
												if ( ! in_array($val['title'], $ignore))
												{
			echo '									<option value="index.php?dn=pages&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												} else {
			echo '									<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												}
											}
											else
											{
												if ( ! in_array($val['title'], $ignore))
												{
			echo '									<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												} else {
			echo '									<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												}
											}
										}
									}
								}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_link'].'</td>
							<td>
								<input id="url" name="url" type="text" size="70" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="70" type="text">&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/menu/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>Title</td>
							<td>
								<input name="title" type="text" size="70" />
							</td>
						</tr>
						<tr>
							<td>CSS</td>
							<td>
								<input name="css" type="text" size="25" />
							</td>
						</tr>
						<tr>
							<td>Target</td>
							<td>
								<select name="target" class="sw165">
									<option value="_self">_self</option>
									<option value="_blank">_blank</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="linkaddsave">
								<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Все позиции * Все ссылки
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'fullout')
		{
			global $mid, $sess;

			$mid = preparse($mid, THIS_INT);
			$menuid = $mid;
			$tree = $menu = $rows = $arr_id = array();

			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$tree[$item['parent']][$item['id']] = $menu[$item['id']] = $item;
			}

			$arr_id = sub_menu($tree, $mid);
			$in_id = ( ! empty($arr_id)) ? implode(',', $arr_id) : 0;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_positions'].'</a>',
					$mid > 0 ? $menu[$mid]['name'] : $lang['link_all']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.($mid > 0 ? $menu[$mid]['name'] : $lang['all_positions']).': '.$lang['link_all'].'</caption>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_link'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>Title</th>
							<th>CSS</th>
							<th>Target</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';

			$ignore = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu WHERE id IN (".$in_id.") ORDER BY posit ASC");
			while ($item = $db->fetchassoc($inq))
			{
				$rows[$item['parent']][$item['id']] = $item;
				if ($item['parent'] == $mid) {
					$ignore[] = $item['name'];
				}
			}
			print_menu($mid);

			$mid = $menuid;
			$mod_array = array();
			$inq_mods = $db->query("SELECT file, name, parent FROM ".$basepref."_mods WHERE active = 'yes' ORDER BY posit");
			while ($item = $db->fetchassoc($inq_mods))
			{
				$mod_array[$item['file']] = $item;
			}

			$mod_page = array();
			if ( ! empty($mod_array) AND isset($mod_array['pages']))
			{
				$inq_page = $db->query("SELECT paid, cpu, title, mods FROM ".$basepref."_pages");
				if ($db->numrows($inq_page) > 0)
				{
					while ($item = $db->fetchassoc($inq_page))
					{
						$mod_page[$item['mods']][] = $item;
					}
				}
			}

			echo '		<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="positup">
								<input type="hidden" name="direct" value="fullout">
								<input class="main-button" value="'.$lang['save_posit'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
			echo '	<script>
					function setkink(urllist, url, name) {
						url = document.getElementById(url);
						name = document.getElementById(name);
						var idx = urllist.selectedIndex;
						var urls = urllist.options[idx].value;
						var names = urllist.options[idx].innerHTML;
						url.value = (urls != 0) ? urls : "";
						name.value = (urls != 0) ? names.replace(/\&nbsp;/g, "") : "";
					}
					</script>';

			echo '	</div>';

			$tm->footer();
		}

		/**
		 * Ссылки меню (сохранение позиций)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'positup')
		{
			global $mid, $posit, $direct, $db, $basepref, $conf, $sess;

			if (preparse($posit, THIS_ARRAY) == 1)
			{
				foreach ($posit as $id => $val)
				{
					$db->query("UPDATE ".$basepref."_site_menu SET posit = '".intval($val)."' WHERE id = '".intval($id)."'");
				}
			}

			cache_menu();

			if ($direct == 'fullout') {
				redirect('index.php?dn=fullout&amp;ops='.$sess['hash']);
			} else {
				redirect('index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Добавление пункта меню
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'linkadd')
		{
			global $mid, $id, $select, $direct, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$id = preparse($id, THIS_INT);

			if ($mid) {
				$mid = explode('.', $mid);
				$mid = $mid[0];
			}

			if (isset($_SERVER['HTTP_REFERER'])) {
				$query = parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $params);
				$direct = $params['dn'];
			} else {
				$direct = 'links';
			}

			$tree = $menu = array();
			$inq = $db->query("SELECT id, parent, name, link FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchassoc($inq))
			{
				$tree[$item['parent']][$item['id']] = $menu[$item['id']] = $item;
			}

			if (empty($tree))
			{
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}

			$ignore = array();
			if (isset($tree[$mid])) {
				$ignore = array_unique(tree_menu($tree, $mid));
			}

			$mod_array = array();
			$inq_mods = $db->query("SELECT file, name, parent FROM ".$basepref."_mods WHERE active = 'yes' ORDER BY posit");
			while ($item = $db->fetchassoc($inq_mods))
			{
				$mod_array[$item['file']] = $item;
			}

			$mod_page = $platform_page = array();
			if ( ! empty($mod_array) AND isset($mod_array['pages']))
			{
				$inq_page = $db->query("SELECT paid, cpu, title, mods FROM ".$basepref."_pages");
				if ($db->numrows($inq_page) > 0)
				{
					while ($item = $db->fetchassoc($inq_page))
					{
						$mod_page[$item['mods']][] = $item;
						if ($item['mods'] != 'pages')
						{
							$platform_page[$item['mods']][] = $item;
						}
					}
				}
			}

			echo '	<script>
					function setkink(urllist, url, name) {
						url = document.getElementById(url);
						name = document.getElementById(name);
						var idx = urllist.selectedIndex;
						var urls = urllist.options[idx].value;
						var names = urllist.options[idx].innerHTML;
						url.value = (urls != 0) ? urls : "";
						name.value = (urls != 0) ? names.replace(/\&nbsp;/g, "") : "";
					}
					function selectmenu() {
						location.href="index.php?dn=linkadd&mid="+document.formmenu.select.value+"&ops='.$sess['hash'].'";
					}
					</script>';

			$check_link = ! empty($menu[$id]['link']) ? TRUE : FALSE;
			$menu_link = $id > 0 ? parse_url($menu[$id]['link']) : null;
			$check_host = isset($menu_link['host']) ? ($menu_link['host'] == parse_url(SITE_URL, PHP_URL_HOST) ? TRUE : FALSE) : TRUE;

			$menu_name = current($tree[0]);
			$menu_name = isset($menu[$mid]['name']) ? $menu[$mid]['name'] : $menu_name['name'];

			$css1 = 'style="color: #000; background: #ffd4d8;"'; // красный, темный
			$css2 = 'style="color: #000; background: #d8fad0;"'; // зеленый, темный
			$css3 = 'style="color: #123; background: #ffeef0;"'; // красный, светлый
			$css4 = 'style="color: #123; background: #f1fdec;"'; // зеленый, светлый

			echo '	<div class="section">
					<form action="index.php" method="post" name="formmenu">
					<table class="work">
						<caption>'.$menu_name.': '.(($id > 0 AND ! empty($menu[$id]['name'])) ? $menu[$id]['name'].': ' : '').$lang['link_add'].'</caption>';
						if ($id == 0)
						{
			echo '			<tr>
								<td>'.$lang['all_posit'].'</td>
								<td>
								<select name="select" onchange="javascript:selectmenu()" class="sw250">';
								foreach ($tree[0] as $key => $val)
								{
			echo 					'<option value="'.$key.'"'.(($mid == $key) ? ' selected' : '').'>'.preparse_un($val['name']).'</option>';
								}
			echo '				</select>
								</td>
							</tr>';
						}
			echo '		<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input id="name" name="name" type="text" size="70" required="required" />';
							if ($id == 0)
							{
			echo '				<select name="urllist" onChange="setkink(this, \'url\', \'name\')">
								<option value="0" '.$css1.'>'.$lang['all_mod'].'</option>';
								if ( ! empty($mod_array))
								{
									foreach ($mod_array as $val)
									{
										if ($val['file'] != 'pages' AND $val['parent'] == 0)
										{
											if ( ! in_array($val['name'], $ignore)) {
			echo '								<option value="index.php?dn='.$val['file'].'" '.$css2.'>&nbsp;&nbsp;'.preparse_un($val['name']).'</option>';
											} else {
			echo '								<option value="0" '.$css1.'>&nbsp;&nbsp;'.preparse_un($val['name']).'</option>';
											}
										}
									}
								}

								$link = array();
								if ( ! empty($mod_page))
								{
									foreach ($mod_page as $mod => $link)
									{
										if ($mod == 'pages')
										{
			echo '							<option value="0" '.$css1.'>'.$lang['pages'].'</option>';
										}
										else
										{
											if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '								<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
											} else {
			echo '								<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
											}
										}
										foreach ($link as $val)
										{
											if ($mod == 'pages')
											{
												if ( ! in_array($val['title'], $ignore))
												{
			echo '									<option value="index.php?dn=pages&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												} else {
			echo '									<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												}
											}
											else
											{
												if ( ! in_array($val['title'], $ignore))
												{
			echo '									<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												} else {
			echo '									<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
												}
											}
										}
									}
								}
			echo '				</select>';
							}
							else
							{
								if ($check_host AND $id > 0)
								{
									if ($check_link AND isset($menu_link['query']))
									{
			echo '						<select name="urllist" onChange="setkink(this, \'url\', \'name\')">';
										parse_str($menu_link['query'], $params);

										if ($db->tables($params['dn']."_cat"))
										{
											$inq = $db->query("SELECT catid, parentid, catcpu, catname FROM ".$basepref."_".$params['dn']."_cat ORDER BY posit ASC");
											while ($c = $db->fetchassoc($inq))
											{
												$selectcat[$c['parentid']][$c['catid']] = $c;
												$obj[$c['catid']] = $c['catname'];
											}
											$ignore_cat = tree_menu($tree, $id);
											$cat_id = ($id > 0 AND isset($params['id'])) ? $params['id']: 0;
											$name_cat = ($id > 0 AND ! empty($menu[$id]['name'])) ? $menu[$id]['name']: $obj[$params['id']];
											echo '	<option value="0" '.$css1.'>'.preparse_un($name_cat).'</option>
														'.menu_cat($cat_id, 0, $params['dn'], $ignore_cat);
										}
										else
										{
											$link = array();
											if ( ! empty($mod_page))
											{
												if (isset($params['pa']))
												{
													foreach ($platform_page as $mod => $link)
													{
														if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '											<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
														} else {
			echo '											<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
														}
														foreach ($link as $val)
														{
															if ( ! in_array($val['title'], $ignore)) {
			echo '												<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
															} else {
			echo '												<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
															}
														}
													}
												}
												else
												{
													foreach ($mod_page as $mod => $link)
													{
														if ($mod == 'pages')
														{
			echo '											<option value="0" '.$css1.'>'.$lang['pages'].'</option>';
														}
														else
														{
															if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '												<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
															} else {
			echo '												<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
															}
														}

														foreach ($link as $val)
														{
															if ($mod == 'pages')
															{
																if ( ! in_array($val['title'], $ignore)) {
			echo '													<option value="index.php?dn=pages&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																} else {
			echo '													<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																}
															}
															else
															{
																if ( ! in_array($val['title'], $ignore)) {
			echo '													<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																} else {
			echo '													<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																}
															}
														}
													}
												}
											}
										}
			echo '						</select>';
									}
									else
									{
										$array_path = explode ('/', $menu_link['path']);
										if (count($array_path) > 2)
										{
			echo '							<select name="urllist" onChange="setkink(this, \'url\', \'name\')">';
											if ($db->tables($array_path[1]."_cat"))
											{
												$inq = $db->query("SELECT catid, parentid, catcpu, catname FROM ".$basepref."_".$array_path[1]."_cat ORDER BY posit ASC");
												while ($c = $db->fetchassoc($inq))
												{
													$selectcat[$c['parentid']][$c['catid']] = $obj[$c['catid']] = $c;
													$obj[$c['catcpu']] = $c;
												}

												$ignore_cat = tree_menu($tree, $id);
												$cat_id = ($id > 0 AND isset($obj[$array_path[2]]['catid'])) ? $obj[$array_path[2]]['catid']: 0;
												$name_cat = ($id > 0 AND ! empty($menu[$id]['name'])) ? $menu[$id]['name'] : $mod_array[$array_path[1]]['name'];

												echo '	<option value="0" '.$css1.'>'.preparse_un($name_cat).'</option>
														'.menu_cat($cat_id, 0, $array_path[1], $ignore_cat).'';
											}
											else
											{
												$link = array();
												if ( ! empty($mod_page))
												{
													if (isset($mod_page[$array_path[1]]) AND $mod_page[$array_path[1]] != 'pages')
													{
														foreach ($platform_page as $mod => $link)
														{
															if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '												<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
															} else {
			echo '												<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
															}
															foreach ($link as $val)
															{
																if ( ! in_array($val['title'], $ignore)) {
			echo '													<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																} else {
			echo '													<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																}
															}
														}
													}
													else
													{
														foreach ($mod_page as $mod => $link)
														{
															if ($mod == 'pages')
															{
			echo '												<option value="0" '.$css1.'>'.$lang['pages'].'</option>';
															}
															else
															{
																if ( ! in_array($mod_array[$mod]['name'], $ignore)) {
			echo '													<option value="index.php?dn=pages&pa='.$mod.'" '.$css2.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
																} else {
			echo '													<option value="0" '.$css1.'>'.preparse_un($mod_array[$mod]['name']).'</option>';
																}
															}

															foreach ($link as $val)
															{
																if ($mod == 'pages')
																{
																	if ( ! in_array($val['title'], $ignore))
																	{
			echo '														<option value="index.php?dn=pages&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																	} else {
			echo '														<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																	}
																}
																else
																{
																	if ( ! in_array($val['title'], $ignore))
																	{
			echo '														<option value="index.php?dn=pages&pa='.$mod.'&cpu='.$val['cpu'].'" '.$css4.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																	} else {
			echo '														<option value="0" '.$css3.'>&nbsp;&nbsp;'.preparse_un($val['title']).'</option>';
																	}
																}
															}
														}
													}
												}
											}
			echo '							</select>';
										}
									}
								}

							}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_link'].'</td>
							<td>
								<input id="url" name="url" type="text" size="70" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="70" type="text">&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/menu/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>Title</td>
							<td>
								<input name="title" type="text" size="70" />
							</td>
						</tr>
						<tr>
							<td>CSS</td>
							<td>
								<input name="css" type="text" size="25" />
							</td>
						</tr>
						<tr>
							<td>Target</td>
							<td>
								<select name="target" class="sw165">
									<option value="_self">_self</option>
									<option value="_blank">_blank</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="linkaddsave">';
							if ($id > 0) {
			echo '				<input type="hidden" name="id" value="'.$id.'">';
							}
			echo '				<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="direct" value="'.$direct.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление пункта меню (сохранение)
		 ---------------------------------------*/
		if ($_REQUEST['dn']=='linkaddsave')
		{
			global $db, $basepref, $id, $mid, $name, $url, $title, $icon, $css, $target, $conf, $direct, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					$lang['all_add']
				);

			$id = preparse($id, THIS_INT);
			$mid = preparse($mid, THIS_INT);
			$icon = preparse($icon, THIS_TRIM);
			$title = preparse($title, THIS_TRIM, 0, 255);

			$parent = $id > 0 ? $id : $mid;
			if (preparse(array($name), THIS_GROUP_EMPTY) == 0)
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_site_menu VALUES (
						 NULL,
						 '".$parent."',
						 '',
						 '".$db->escape(preparse_sp($name))."',
						 '".$db->escape($url)."',
						 '".$db->escape(preparse_sp($title))."',
						 '".$db->escape($icon)."',
						 '0',
						 '".$db->escape($css)."',
						 '".$target."',
						 '0'
						 )"
					);

				$db->query("UPDATE ".$basepref."_site_menu SET total = total + 1 WHERE id = '".$mid."'");
			}
			else
			{
				$tm->header();
				$tm->error($lang['link_add'], null, $lang['pole_add_error']);
				$tm->footer();
			}

			cache_menu();

			if ($direct == 'fullout') {
				redirect('index.php?dn=fullout&amp;ops='.$sess['hash']);
			} else {
				redirect('index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Редактирование пункта меню
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'linkedit')
		{
			global $direct, $mid, $id, $sess;

			$id = preparse($id, THIS_INT);

			if (isset($_SERVER['HTTP_REFERER'])) {
				$query = parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $params);
				$direct = $params['dn'];
			} else {
				$direct = 'links';
			}

			$menu = array();
			$inq = $db->query("SELECT id, name FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$menu[$item['id']] = $item;
			}

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_site_menu WHERE id = '".$id."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_positions'].'</a>',
					'<a href="index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash'].'">'.$menu[$mid]['name'].'</a>'
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['edit_link'].': '.$menu[$id]['name'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input id="name" name="name" type="text" size="70" value="'.preparse_un($item['name']).'" required="required" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_link'].'</td>
							<td>
								<input id="url" name="url" type="text" size="70" value="'.$item['link'].'" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="70" type="text" value="'.$item['icon'].'" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/menu/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>Title</td>
							<td>
								<input name="title" type="text" size="70" value="'.preparse_un($item['title']).'" />
							</td>
						</tr>
						<tr>
							<td>CSS</td>
							<td>
								<input name="css" type="text" size="25" value="'.$item['css'].'" />
							</td>
						</tr>
						<tr>
							<td>Target</td>
							<td>
								<select name="target" class="sw165">
									<option value="_self"'.(($item['target'] == '_self') ? ' selected' : '').'>_self</option>
									<option value="_blank"'.(($item['target'] == '_blank') ? ' selected' : '').'>_blank</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="linkeditsave">
								<input type="hidden" name="direct" value="'.$direct.'">
								<input type="hidden" name="mid" value="'.$mid.'">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление пункта меню (сохранение)
		 ---------------------------------------*/
		if ($_REQUEST['dn']=='linkeditsave')
		{
			global $db, $basepref, $mid, $id, $name, $url, $title, $css, $css, $target, $direct, $sess;

			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$menu[$item['id']] = $item;
			}

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					'<a href="index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash'].'">'.$menu[$mid]['name'].'</a>',
					$lang['edit_link']
				);

			$id = preparse($id, THIS_INT);
			$mid = preparse($mid, THIS_INT);
			if (preparse($name, THIS_GROUP_EMPTY) == 0)
			{
				$db->query
					(
						"UPDATE ".$basepref."_site_menu SET
						 name   = '".$db->escape(preparse_sp($name))."',
						 link    = '".$db->escape($url)."',
						 title  = '".$db->escape(preparse_sp($title))."',
						 icon   = '".$db->escape($icon)."',
						 css    = '".$db->escape($css)."',
						 target = '".$target."'
						 WHERE id = '".$id."'"
					);
			}
			else
			{
				$tm->header();
				$tm->error($lang['edit_link'], $name, $lang['forgot_name']);
				$tm->footer();
			}

			cache_menu();

			if ($direct == 'fullout') {
				redirect('index.php?dn=fullout&amp;ops='.$sess['hash']);
			} else {
				redirect('index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Удаление пункта меню
		 ------------------------*/
		if ($_REQUEST['dn'] == 'linkdel')
		{
			global $mid, $id, $ok, $conf, $direct, $sess;

			$id = preparse($id, THIS_INT);
			$mid = preparse($mid, THIS_INT);

			if ($ok == 'yes') {
				$direct = $direct;
			} else {
				if (isset($_SERVER['HTTP_REFERER'])) {
					$query = parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $params);
					$direct = $params['dn'];
				} else {
					$direct = 'links';
				}
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				$menu[$item['id']] = $item;
			}

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['site_menu'].'</a>',
					'<a href="index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash'].'">'.$menu[$mid]['name'].'</a>',
					$lang['del_link']
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_site_menu WHERE id = '".$id."'");
				$db->increment('site_menu');

				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_site_menu WHERE id = '".$mid."'"));
				$sql = ($item['total'] == 0) ? 'total = 0' : 'total = total - 1';
				$db->query("UPDATE ".$basepref."_site_menu SET ".$sql." WHERE id = '".$mid."'");

				cache_menu();

				if ($direct == 'fullout') {
					redirect('index.php?dn=fullout&amp;ops='.$sess['hash']);
				} else {
					redirect('index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash']);
				}
			}
			else
			{
				$yes = 'index.php?dn=linkdel&amp;mid='.$mid.'&amp;id='.$id.'&amp;ok=yes&amp;direct='.$direct.'&amp;ops='.$sess['hash'];

				if ($direct == 'fullout') {
					$not = 'index.php?dn=fullout&amp;ops='.$sess['hash'];
				} else {
					$not = 'index.php?dn=links&amp;mid='.$mid.'&amp;ops='.$sess['hash'];
				}

				$item = $db->fetchrow($db->query("SELECT name FROM ".$basepref."_site_menu WHERE id = '".$id."'"));

				$tm->header();
				$tm->shortdel($lang['del_link'], preparse_un($item['name']), $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['site_menu'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
