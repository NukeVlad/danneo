<?php
/**
 * File:        /admin/mod/down/index.php
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
	global $ADMIN_ID, $CHECK_ADMIN, $AJAX, $db, $basepref, $tm, $conf, $modname, $wysiwyg, $lang, $sess, $ops, $cache;

	if ( ! isset($modname[PERMISS]))
	{
		redirect(ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash']);
	}

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
			$modname[PERMISS]
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
				'index', 'optsave', 'cat', 'catadd', 'catedit', 'catdel', 'cataddsave',
				'catup', 'cateditsave', 'list', 'work', 'arrdel', 'arrmove', 'arract', 'arracc',
				'add', 'addsave', 'edit', 'editsave', 'act', 'del', 'brokenlist', 'brokendel',
				'brokenclear', 'comment', 'commentrep', 'commentedit', 'commenteditrep', 'mediaadd', 'mediaaddsave', 'ajaxedittitle', 'ajaxsavetitle',
				'ajaxeditcat', 'ajaxsavecat', 'ajaxeditdate', 'ajaxsavedate', 'autocomplete',
				'new', 'newdel', 'newarrdel', 'newadd',
				'tag', 'tagsetsave', 'tagedit', 'tageditsave', 'tagaddsave', 'tagdel'
			);

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Доп. функции мода
		 */
		include('mod.function.php');

		/**
		 * Массив сортировок для категорий
		 */
		$catsort = array
			(
				'id'     => $lang['all_id'],
				'public' => $lang['all_data'],
				'title'  => $lang['all_name'],
				'hits'   => $lang['all_hits']
			);

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $db, $basepref, $conf, $tm, $lang, $sess, $AJAX;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, edit').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>'
					.'<a'.cho('add, newadd').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['down_add'].'</a>'
					.'<a'.cho('cat, catedit').' href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>'
					.'<a'.cho('catadd').' href="index.php?dn=catadd&amp;ops='.$sess['hash'].'">'.$lang['all_add_cat'].'</a>';

			if (isset($conf[PERMISS]['addit']) AND $conf[PERMISS]['addit'] == 'yes')
			{
				$c = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".PERMISS."_user"));
				$link.= '<a'.cho('new').' href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['down_added'].'&nbsp; &#8260; &nbsp;'.$c['total'].'</a>';
			}

			if (isset($conf[PERMISS]['comact']) AND $conf[PERMISS]['comact'] == 'yes')
			{
				if ($AJAX) {
					$link.= '<a class="all-comments" href="index.php?dn=comment&amp;ajax=1&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>';
				} else {
					$link.= '<a href="index.php?dn=comment&amp;ajax=0&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>';
				}
			}

			if (isset($conf[PERMISS]['tags']) AND $conf[PERMISS]['tags'] == 'yes')
			{
				$link.= '<a'.cho('tag, tagedit').' href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['all_tags'].'</a>';
			}

			if (isset($conf[PERMISS]['broken']) AND $conf[PERMISS]['broken'] == 'yes')
			{
				$bc = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".PERMISS."_broken"));
				$link.= '<a'.cho('brokenlist').' href="index.php?dn=brokenlist&amp;ops='.$sess['hash'].'">'.$lang['down_no_access'].'&nbsp; &#8260; &nbsp;'.$bc['total'].'</a>';
			}

			$filter = null;
			if (cho('list')) {
				$filter = '<a'.cho('list', 1).' href="#" onclick="$(\'#filter\').slideToggle();" title="'.$lang['search_in_section'].'">'.$lang['all_filter'].'</a>';
			}

			$tm->this_menu($link, $filter);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 --------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $ro;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			echo "	<script>
					$(function(){
						$('#acc').bind('change', function() {
							if ($(this).val() == 'group') {
								$('#group').slideDown();
							} else {
								$('#group').slideUp();
							}
						});
					});
					</script>";
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");
			while ($itemset = $db->fetchrow($inqset))
			{
				if ($itemset['setname'] != 'groups')
				{
					echo	in_array($itemset['setname'], array('broken', 'rating', 'comact', 'rec', 'rss', 'time', 'addit')) ? '<tr><th colspan="2"></th></tr>' : '';
					echo '	<tr>
								<td class="first">'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'</td>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Настройки (сохранение)
		 --------------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($modname[PERMISS], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0)
					{
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$counts = new Counts(PERMISS, 'id');

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Категории
		 ---------------*/
		if ($_REQUEST['dn'] == 'cat')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_cat']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_cat'].'</caption>
						<tr>
							<th>ID</th>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_cat_access'].'</th>
							<th>'.$lang['all_col'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$inquiry = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			print_cat(0, 0);
			echo '		<tr class="tfoot">
							<td colspan="7">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="dn" value="catup" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Категории (сохранение позиций)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'catup')
		{
			global $posit;

			if (preparse($posit,THIS_ARRAY) == 1)
			{
				this_catup($posit, PERMISS);
			}
			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить категорию
		 -----------------------*/
		if ($_REQUEST['dn'] == 'catadd')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
			}

			this_selectcat(0);

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_add_cat'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="catname" id="catname" size="70" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70" />';
								$tm->outtranslit('catname', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="catcustom" size="70" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0">'.$lang['all_cat_new'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_sorting'].'</td>
							<td>
								<select name="sort" class="sw165">';
			foreach ($catsort as $k => $v) {
				echo '				<option value="'.$k.'">'.$v.'</option>';
			}
			echo '				</select> &nbsp;&#247;&nbsp;
								<select name="ord" class="sw150">
									<option value="desc">'.$lang['all_desc'].'</option>
									<option value="asc">'.$lang['all_acs'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cat_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes" /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>RSS</td>
							<td>
								<select name="rss" class="sw165">
									<option value="yes">'.$lang['all_yes'].'</option>
									<option value="no">'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, '', 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="cataddsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление категории (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'cataddsave')
		{
			global $catid, $catname, $subtitle, $cpu, $catcustom, $keywords, $descript, $icon, $descr, $acc, $group, $sort, $ord, $rss;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_add']
				);

			$catname = preparse($catname, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);

			if (preparse($catname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['all_add_cat'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($catname);
				}

				$inqure = $db->query("SELECT catname, catcpu FROM ".$basepref."_".PERMISS."_cat WHERE catname = '".$db->escape($catname)."' OR catcpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['all_add_cat'], $catname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$sort = isset($catsort[$sort]) ? $sort : 'public';
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$ord = ($ord == 'asc') ? 'asc' : 'desc';
			$rss = ($rss == 'yes') ? 'yes' : 'no';

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_cat VALUES (
					 NULL,
					 '".$catid."',
					 '".$db->escape($cpu)."',
					 '".$db->escape(preparse_sp($catname))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape($descr)."',
					 '".$db->escape(preparse_sp($catcustom))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '0',
					 '".$db->escape($icon)."',
					 '".$acc."',
					 '".$db->escape($group)."',
					 '".$sort."',
					 '".$ord."',
					 '".$rss."',
					 '0'
					 )"
				);

			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление категории
		 -------------------------*/
		if ($_REQUEST['dn'] == 'catdel')
		{
			global $catid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_delet']
				);

			$catid = preparse($catid, THIS_INT);
			if ($ok == 'yes')
			{
				$del = this_delcat($catid, PERMISS);
				if ($del > 0) {
					$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE catid = '".$catid."'");
					$db->query("DELETE FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'");
				}
				$counts = new Counts(PERMISS, 'id');
				$cache->cachesave(3);
				redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT catname FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));

				$yes = 'index.php?dn=catdel&amp;catid='.$catid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=cat&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_cat'], preparse_un($item['catname']), $yes, $not, $lang['del_cat_alert']);
				$tm->footer();
			}
		}

		/**
		 * Редактировать категорию
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'catedit')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$catid = preparse($catid, THIS_INT);
			$inquiry = $db->query("SELECT catid,parentid,catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			this_selectcat(0);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['cat_edit'].': '.$item['catname'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="catname" id="catname" size="70" value="'.preparse_un($item['catname']).'" required="required" /> <span class="light">&lt;h1&gt;</span>
							</td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70" value="'.$item['catcpu'].'" />';
								$tm->outtranslit('catname', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="catcustom" size="70" value="'.preparse_un($item['catcustom']).'" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="parentid" class="sw250">
									<option value="0">'.$lang['all_cat_new'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_sorting'].'</td>
							<td>
								<select name="sort" class="sw165">';
			foreach ($catsort as $k => $v) {
				echo '				<option value="'.$k.'"'.(($item['sort'] == $k) ? ' selected' : '').'>'.$v.'</option>';
			}
			echo '				</select> &nbsp;&#247;&nbsp;
								<select name="ord" class="sw150">
									<option value="asc"'.(($item['ord'] == 'asc') ? ' selected' : '').'>'.$lang['all_acs'].'</option>
									<option value="desc"'.(($item['ord'] == 'desc') ? ' selected' : '').'>'.$lang['all_desc'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cat_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all"'.(($item['access'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['access'] == 'user' AND empty($item['groups'])) ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['access'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['access'] == 'all' OR $item['access'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').'><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>RSS</td>
							<td>
								<select name="rss" class="sw165">
									<option value="yes"'.(($item['rss'] == 'yes') ? ' selected' : '').'>'.$lang['all_yes'].'</option>
									<option value="no"'.(($item['rss'] == 'no') ? ' selected' : '').'>'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value=" '.$lang['filebrowser'].' " type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, $item['catdesc'], 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="cateditsave" />
								<input type="hidden" name="catid" value="'.$catid.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value=" '.$lang['all_save'].' " type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать категорию (сохранение)
		 ---------------------------------------*/
		if ($_REQUEST['dn'] == 'cateditsave')
		{
			global $parentid, $catid, $catname, $subtitle, $cpu, $catcustom, $keywords, $descript, $icon, $descr, $acc, $group, $sort, $ord, $rss;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$catname = preparse($catname, THIS_TRIM, 0, 255);
			$subtitle  = preparse($subtitle, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);
			$parentid = preparse($parentid, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$err = this_councat($catid,$parentid, PERMISS);

			if (preparse($catname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['cat_edit'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($catname);
				}

				$inqure = $db->query
							(
								"SELECT catname, catcpu FROM ".$basepref."_".PERMISS."_cat
								 WHERE (catname = '".$db->escape($catname)."' OR catcpu = '".$db->escape($cpu)."')
								 AND catid <> '".$catid."'
								"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['cat_edit'], $catname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}
			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$sort = isset($catsort[$sort]) ? $sort : 'public';
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$ord = ($ord == 'asc') ? 'asc' : 'desc';
			$rss = ($rss == 'yes') ? 'yes' : 'no';
			$upparentid = ($catid != $parentid) ? "parentid = '".$parentid."'," : "";

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_cat SET ".$upparentid."
					 catcpu    = '".$db->escape($cpu)."',
					 catname   = '".$db->escape(preparse_sp($catname))."',
					 subtitle  = '".$db->escape(preparse_sp($subtitle))."',
					 catdesc   = '".$db->escape($descr)."',
					 catcustom = '".$db->escape(preparse_sp($catcustom))."',
					 keywords  = '".$db->escape(preparse_sp($keywords))."',
					 descript  = '".$db->escape(preparse_sp($descript))."',
					 icon      = '".$db->escape($icon)."',
					 access    = '".$acc."',
					 groups    = '".$db->escape($group)."',
					 sort      = '".$sort."',
					 ord       = '".$ord."',
					 rss       = '".$rss."'
					 WHERE catid = '".$catid."'"
				);

			$counts = new Counts(PERMISS, 'id');

			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Все файлы (листинг)
		 ------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $selective, $catid, $nu, $p, $cat, $s, $l, $ajax, $filter, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['down_all']
				);

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;

			if (preparse($ajax,THIS_INT) == 0)
			{
				$tm->header();
				echo '<div id="ajaxbox">';
			}
			else
			{
				echo '<script>$(function(){$("img, a").tooltip();});</script>';
			}

			if (isset($conf['userbase']))
			{
				if ($conf['userbase'] == 'danneo') {
					require_once(WORKDIR.'/core/userbase/danneo/danneo.user.php');
				} else {
					require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
				}

				$userapi = new userapi($db, false);
			}

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu = ( ! is_null($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$sort  = array('id', 'title', 'public', 'hits', 'comments', 'author');
			$limit = array('desc', 'asc');
			$s  = (in_array($s,$sort)) ? $s : 'public';
			$l  = (in_array($l,$limit)) ? $l : 'desc';

			$groups_only = array();
			if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
			{
				$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
				while ($items = $db->fetchrow($inqs)) {
					$groups_only[] =  $items['title'];
				}
			}

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = $catcaches = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}
			if (isset($cat) AND isset($catcaches[$cat]) OR isset($cat) AND $cat == 0 AND $cat != 'all') {
				$sql = " WHERE catid = '".preparse($cat,THIS_INT)."'";
				$link = "&amp;cat=".preparse($cat, THIS_INT);
				$catid = $cat;
			} else {
				$sql = '';
				$link = '&amp;cat=all';
				$cat = 'all';
				$catid = 0;
			}
			$fu = '';
			$fid = preparse($fid,THIS_INT);
			$myfilter = array(
				'title'  => array('title', 'all_name', 'input'),
				'author' => array('author', 'author', 'input'),
				'public' => array('public', 'all_data', 'date'),
				'acc'    => array('acc', 'all_access', 'type', array('unimportant', 'all_all', 'all_user_only'), array('', 'all', 'user'))
			);
			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0)
				{
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ',$insert);
					$fu = '&amp;fid='.$item['fid'];
				}
			}
			else
			{
				if (isset($filter) AND is_array($filter))
				{
					$sw = array();
					foreach ($filter as $k => $v)
					{
						if (isset($myfilter[$k]))
						{
							$f = $myfilter[$k];
							if ($f[2] == 'input' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'type' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'date' AND is_array($v)) {
								if (isset($v[0]) AND ! empty($v[0])) {
									$sw[] = $f[0]." > '".$db->escape(ReDate($v[0]))."'";
								}
								if (isset($v[1]) AND ! empty($v[1])) {
									$sw[] = $f[0]." < '".$db->escape(ReDate($v[1]))."'";
								}
							}
						}
					}
					if (sizeof($sw) > 0)
					{
						$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ',$sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$fid = $db->insertid();
						if ($fid > 0) {
							$fu = '&amp;fid='.$fid;
						}
					}
				}
			}
			$link.= $fu;
			$a = ($ajaxlink) ? '&amp;ajax=1' : '';
			$revs = $link.$a.'&amp;nu='.$nu.'&amp;s='.$s.'&amp;l='.(($l=='desc') ? 'asc' : 'desc');
			$rev =  $link.$a.'&amp;nu='.$nu.'&amp;l=desc&amp;s=';
			$link.= $a.'&amp;s='.$s.'&amp;l='.$l;
			$c = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".PERMISS.$sql));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);
			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS.$sql." ORDER BY ".$s." ".$l." LIMIT ".$sf.", ".$nu);
			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.$sql, 'id', 'index', 'list'.$link, $nu, $p, $sess, $ajaxlink);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'].$link, $nu, $ajaxlink);
			this_selectcat(0);
			echo '	<script>
						var ajax = '.$ajaxlink.';
					</script>';
			if ($ajaxlink)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache:false,async:false});
							$(".comment-view").colorbox({
								width         : "92%",
								height        : "90%",
								initialWidth  : 900,
								initialHeight : 600,
								maxHeight     : 800,
								maxWidth      : 1200,
								fixed: true,
								onComplete: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
							$(".media-view").colorbox({initialHeight:"210px",width:"720px"});
						});
						</script>';
			}

			// Поиск по фильтру
			$tm->filter('index.php?dn=list&amp;ops='.$sess['hash'], $myfilter, $modname[PERMISS]);

			echo '	<div class="section">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['down_all'].'</caption>
						<tr>
							<td class="vm">
								'.$lang['all_cat_one'].':&nbsp;
								<form action="index.php" method="post">
									<select name="cat">
										<option value="all">'.$lang['all_all'].'</option>
										<option value="0"'.(($cat != 'all' AND $cat == 0) ? ' selected' : '').'>'.$lang['cat_not'].'</option>
										'.$selective.'
									</select>
									<input type="hidden" name="dn" value="list" />
									<input type="hidden" name="ops" value="'.$sess['hash'].'" />
									<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
								</form>
							</td>
						</tr>
					</table>
					<div class="upad"></div>
					<form action="index.php" method="post">
					<table id="list" class="work">
						<tr><td colspan="9">'.$amount.'</td></tr>
						<tr>
							<th'.listsort('id').'>ID</th>
							<th'.listsort('title').'>'.$lang['all_name'].'&nbsp; &#8260; &nbsp;'.$lang['all_cat_one'].'</th>
							<th'.listsort('public').'>'.$lang['all_data'].'</th>
							<th'.listsort('hits').'>'.$lang['all_hits'].'</th>
							<th'.listsort('comments').'>'.$lang['menu_comment'].'</th>
							<th'.listsort('author').'>'.$lang['author'].'</th>
							<th class="work-no-sort">'.$lang['all_access'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox" /></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$media = ($item['listid'] > 0) ? 1 : 0;
				$style = ($item['act'] == 'no') ? 'no-active' : '';
				$stylework = ($item['act'] == 'no') ? 'no-active' : '';

				// Ассоциируем группы
				$groupact = NULL;
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					if ( ! empty($item['groups']))
					{
						$groups = Json::decode($item['groups']);
						reset($groups);
						foreach ($groups as $key => $val)
						{
							$groupact.=  ' '.$groups_only[$key - 1].',';
						}
						$groupact = chop($groupact, ',');
					}
				}

				// Автор
				$author = '—';
				if ( ! empty($item['author']))
				{
					$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
					if (in_array('user', $realmod))
					{
						$udata = $userapi->userdata('uname', $author);
						if ( ! empty($udata))
						{
							require_once(WORKDIR.'/core/classes/Router.php');
							$ro = new Router();
							$author = '<a href="'.$conf['site_url'].$ro->seo($userapi->data['linkprofile'].$udata['userid']).'" title="'.$lang['profile'].' - '.$author.'" target="_blank">'.$author.'</a>';
						}
					}
				}

				echo '	<tr class="list">
							<td class="'.$style.' ac pw5">'.$item['id'].'</td>
							<td class="'.$style.'">';
				if ($item['public'] >= (TODAY - 86400)) {
					echo '		<img class="fr" src="'.ADMPATH.'/template/images/new.gif" alt="'.$lang['add_today'].'" />';
				}
				if ($ajaxlink == 1) {
					echo '		<div id="te'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'te'.$item['id'].'\',\'405\')" title="'.$lang['all_change'].'">
										'.preparse_un($item['title']).'
									</a>
								</div>';
				} else {
					echo '		<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'">'.preparse_un($item['title']).'</a>';
				}
				if ($ajaxlink == 1) {
					echo '		<div id="ce'.$item['id'].'" class="cats">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'ce'.$item['id'].'\',\'305\')" title="'.$lang['all_change'].'">
										'.preparse_un(linecat($item['catid'], $catcaches)).'
									</a>
								</div>';
				} else {
					echo '		<span class="cats">'.preparse_un(linecat($item['catid'],$catcaches)).'</span>';
				}
				echo '		</td>
							<td class="'.$style.' pw15">';
				if ($ajaxlink == 1) {
					echo '		<div id="de'.$item['id'].'">
									<a href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'de'.$item['id'].'\',\'220\')" title="'.$lang['all_change'].'">
										'.format_time($item['public'], 0, 1).'
									</a>
								</div>';
				} else {
					echo		format_time($item['public'], 0, 1);
				}
				echo '		</td>
							<td class="'.$style.' pw10">'.$item['hits'].'</td>
							<td class="'.$style.' pw10">';
				if ($item['comments'] > 0) {
					echo '		'.$item['comments'].'&nbsp; &nbsp;
								<a class="comment-view" href="index.php?dn=commentedit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].(($ajaxlink) ? '&amp;ajax=1' : '').'">
									<img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" />
								</a>';
				} else {
					echo '0';
				}
				echo '		</td>
							<td class="'.$style.' pw10">'.$author.'</td>
							<td class="'.$style.' pw10">';
				echo '			'.(($item['acc'] == 'user') ? ( ! empty($item['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']);
				echo '		</td>
							<td class="'.$style.' gov pw10">
								<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" /></a>';
				if (in_array('media', $realmod))
				{
					if ($item['listid'] > 0) {
						echo '	<a class="media-view exmedia" href="index.php?dn=mediaadd&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].(($ajaxlink) ? '&amp;ajax=1' : '').'">
									<img alt="'.$lang['work_attach_media'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/media.png" />
								</a>';
					} else {
						echo '	<a class="media-view" href="index.php?dn=mediaadd&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].(($ajaxlink) ? '&amp;ajax=1' : '').'">
									<img alt="'.$lang['attach_media'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/media.png" />
								</a>';
					}
				}
				if ($item['act'] == 'yes') {
					echo '		<a href="index.php?dn=act&amp;act=no&amp;cat='.$cat.'&amp;fid='.$fid.'&amp;id='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=act&amp;act=yes&amp;cat='.$cat.'&amp;fid='.$fid.'&amp;id='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				}
				echo '			<a href="index.php?dn=del&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" /></a>
							</td>
							<td class="'.$style.' mark pw5">
								<input type="checkbox" name="array['.$item['id'].']" value="yes" />
							</td>
						</tr>';
			}
			echo '		<tr>
							<td colspan="9">'.$lang['all_mark_work'].'&nbsp;
								<select name="workname">
									<option value="move">'.$lang['all_move'].'</option>
									<option value="del">'.$lang['all_delet'].'</option>
									<option value="active">'.$lang['included'].' / '.$lang['not_included'].'</option>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes') {
				echo '				<option value="access">'.$lang['all_access'].'</option>';
			}
			echo '				</select>
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="cat" value="'.$cat.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="s" value="'.$s.'" />
								<input type="hidden" name="l" value="'.$l.'" />';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'" />';
			}
			echo '				<input type="hidden" name="dn" value="work" />
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
							</td>
						</tr>
						<tr><td colspan="9">'.$pages.'</td></tr>
					</table>
					</form>
					</div>';
			if (preparse($ajax, THIS_INT) == 0) {
				echo '</div>';
				$tm->footer();
			}
		}

		/**
		 * Массовая обработка
		 -----------------------*/
		if ($_REQUEST['dn'] == 'work')
		{
			global $array, $workname, $selective, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['array_control']
				);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				$temparray = $array;
				$count = count($temparray);
				$hidden = '';
				foreach ($array as $key => $id) {
					$hidden.= '<input type="hidden" name="array['.$key.']" value="yes" />';
				}
				$p = preparse($p, THIS_INT);
				$cat = preparse($cat, THIS_INT);
				$nu = preparse($nu, THIS_INT);
				$s = preparse($s, THIS_TRIM, 1, 7);
				$l = preparse($l, THIS_TRIM, 1, 4);
				$fid = preparse($fid, THIS_INT);
				$h = '<input type="hidden" name="p" value="'.$p.'" />'
                     .'<input type="hidden" name="cat" value="'.$cat.'" />'
                     .'<input type="hidden" name="nu" value="'.$nu.'" />'
                     .'<input type="hidden" name="s" value="'.$s.'" />'
                     .'<input type="hidden" name="l" value="'.$l.'" />'
                     .'<input type="hidden" name="fid" value="'.$fid.'" />'
                     .'<input type="hidden" name="ops" value="'.$sess['hash'].'" />';

				// Удаление
				if ($workname == 'del')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_delet'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrdel" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>';
					$tm->footer();

				// Перемещение
				} elseif ($workname == 'move') {

					$tm->header();
					$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
					$catcache = array();
					while ($item = $db->fetchrow($inquiry)) {
						$catcache[$item['parentid']][$item['catid']] = $item;
					}
					this_selectcat(0);
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_move'].' '.$lang['all_in_cat'].' ('.$count.')</caption>
								<tr>
									<td class="cont">
										<select name="catid">
											<option value="0">'.$lang['cat_not'].'</option>
											'.$selective.'
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrmove" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				// Активация
				} elseif ($workname == 'active') {

					$tm->header();
					echo ' <div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_status'].' ('.$count.')</caption>
								<tr>
									<td class="cont">
										<select name="act">
											<option value="yes">'.$lang['included'].'</option>
											<option value="no">'.$lang['not_included'].'</option>
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arract" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				// Доступ
				} elseif (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes' AND $workname == 'access') {

					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_access'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['all_access'].':&nbsp;
										<select name="acc">
											<option value="all">'.$lang['all_all'].'</option>
											<option value="user">'.$lang['all_user_only'].'</option>
										</select>
									</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arracc" />
										<input class="side-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();
				}
			}
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Массовое удаление (сохранение)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'arrdel')
		{
			global $array, $p, $cat, $nu, $s, $l, $fid;

			allarrdel($array, 'id', PERMISS, 0, 1);

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое перемещение (сохранение)
		--------------------------------------*/
		if ($_REQUEST['dn'] == 'arrmove')
		{
			global $catid, $array, $p, $cat, $nu, $s, $l, $fid;

			$catid = preparse($catid, THIS_INT);
			allarrmove($array, $catid, PERMISS);
			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid, THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовая активация (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'arract')
		{
			global $array, $act, $p, $cat, $nu, $s, $l, $fid;

			$act = ($act == 'yes') ? 'yes' : 'no';
			allarract($array,'id', PERMISS, $act);
			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid,THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Массовое изменение доступа (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'arracc')
		{
			global $array, $acc, $p, $cat, $nu, $s, $l, $fid;

			if (preparse($array,THIS_ARRAY) == 1)
			{
				$acc = ($acc == 'all') ? 'all' : 'user';
				foreach ($array as $id => $v)
				{
					$id = preparse($id, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS." SET acc = '".$acc."' WHERE id = '".$id."'");
				}
			}

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			$fid = preparse($fid,THIS_INT);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Добавить файл
		 --------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			global $id, $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['down_add']
				);

			$tm->header();

			$time = CalendarFormat(NEWTIME);
			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script src="'.ADMPATH.'/js/jquery.autocomplete.js"></script>
					<script>
						var all_name   = "'.$lang['all_name'].'";
						var all_cpu    = "'.$lang['all_cpu'].'";
						var all_popul  = "'.$lang['all_popul'].'";
						var all_thumb  = "'.$lang['all_image_thumb'].'";
						var all_img    = "'.$lang['all_image'].'";
						var all_images = "'.$lang['all_image_big'].'";
						var all_align  = "'.$lang['all_align'].'";
						var all_right  = "'.$lang['all_right'].'";
						var all_left   = "'.$lang['all_left'].'";
						var all_center = "'.$lang['all_center'].'";
						var all_alt    = "'.$lang['all_alt_image'].'";
						var all_copy   = "'.$lang['all_copy'].'";
						var all_delet  = "'.$lang['all_delet'].'";
						var code_paste = "'.$lang['code_paste'].'";
						var all_file   = "'.$lang['all_file'].'";
						var all_link   = "'.$lang['all_link'].'";
						var page       = "'.PERMISS.'";
						var ops        = "'.$sess['hash'].'";
						var filebrowser = "'.$lang['filebrowser'].'";
						$(function() {
							$(".imgcount").focus(function () {
								$(this).select();
							}).mouseup(function(e){
								e.preventDefault();
							});
						});
					</script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" name="total-form" id="total-form">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['down_add'].'</caption>
						<tr>
							<th></th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="title" id="title" size="70" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].':</td>
							<td><input type="text" name="cpu" id="cpu" size="70" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input type="text" name="keywords" size="70" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'" />';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic" />';
								Calendar('stcal', 'stpublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_unpublic'].'</td>
							<td><input type="text" name="unpublic" id="unpublic" />';
								Calendar('uncal', 'unpublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0"> &#8212; '.$lang['cat_not'].' &#8212; </option>';
				$catcache = array();
				while ($item = $db->fetchrow($inqcat)) {
					$catcache[$item['parentid']][$item['catid']] = $item;
				}
				this_selectcat(0);
				echo				$selective.'
								</select>
							</td>
						</tr>';
			}
			echo '		<tr><th></th><th class="site">'.$lang['all_file'].'</th></tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['down_add'].'</td>
							<td class="nowrap">
								<input type="text" name="file" id="file" size="70" required="required" /> &nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/file/\',\'&amp;field[1]=file\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.fileallupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/file/\',\'&amp;field=file\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['down_size'].'</td>
							<td><input type="text" name="size" size="25" />';
								$tm->outhint($lang['down_add_size_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['file_version'].'</td>
							<td><input type="text" name="relis" size="25" /></td>
						</tr>
						<tr>
							<td>'.$lang['author'].'</td>
							<td><input type="text" name="auth" size="25" /></td>
						</tr>
						<tr>
							<td>'.$lang['author_site'].'</td>
							<td><input type="text" name="site" size="25" /></td>
						</tr>
						<tr>
							<td>'.$lang['mirrors'].'</td>
							<td class="vm">
								<div id="mirror-area"></div>
								<div><a class="side-button" href="javascript:$.addmirror(\'total-form\',\'mirror-area\');">'.$lang['all_submint'].'</a></div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr><th></th><th class="site">'.$lang['descript'].'</th></tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['input_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS',1);
				$WYSFORM = 'textshort';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textshort', 5, 70, '', 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'textmore';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textmore', 7, 70, '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['img_extra_hint'].'</td>
							<td class="vm">
								<div id="image-area"></div>
								<div>
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;ims=1\');">'.$lang['filebrowser'].'</a>&nbsp;
									<a class="side-button" href="javascript:$.personalupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/img/\');">'.$lang['file_upload'].'</a>
								</div>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['user_texts'].'</td>
							<td>';
								$tm->textarea('textnotice', 2, 70, '', true, false, 'ignorewysywig');
				echo '		</td>
						</tr>';
			}
			if($conf[PERMISS]['tags'] == 'yes')
			{
				echo '	<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_tags'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="tagarea">
								<table class="work">
									<tr>
										<td class="pw45">
											<select name="tagin" id="tagin" size="5" multiple class="blue pw100 app">';
				$tags = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag");
				while ($tag = $db->fetchrow($tags))
				{
						echo '					<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addtag();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.deltag();" value="&#9668;" />
										</td>
										<td>
											<select name="tagout" id="tagout" size="5" multiple class="green pw100 app">
											</select>
											<div id="area-tags">
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr><th></th><th class="site">'.$lang['all_image_big'].'</th></tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/img/\',\'&amp;field[1]=image_thumb&amp;field[2]=image\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/img/\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/img/\',\'&amp;field[1]=image&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" /></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_align_image'].'</td>
							<td>
								<select name="image_align" class="sw165">
									<option value="left">'.$lang['all_left'].'</option>
									<option value="right">'.$lang['all_right'].'</option>
								</select>
							</td>
						</tr>
						<tr><th></th><th class="site">'.$lang['options'].'</th></tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].'</option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['who_down'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes" /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['all_important'].'</td>
							<td>
								<select name="imp" class="sw165">
									<option value="0">'.$lang['all_no'].'</option>
									<option value="1">'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td class="work-button center" colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="dn" value="addsave" />
								<input type="hidden" id="imgid" value="0" />
								<input type="hidden" id="mirrid" value="0" />
								<input type="hidden" id="countid" value="0" />';
			if ($conf['cpu'] == 'no') {
				echo '			<input type="hidden" name="cpu" />';
			}
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="textnotice" />
								<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input class="main-button" value="'.$lang['all_go'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			echo "	<script>
						$(document).ready(function() {
							$('#tabs a').tabs('.tab-1');
						});
					</script>";

			$tm->footer();
		}

		/**
		 * Добавить файл (сохранение)
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $catid, $public, $stpublic, $unpublic, $cpu, $file, $size, $descript, $keywords, $title, $subtitle,
					$textshort, $textmore, $textnotice, $mirrors, $relis, $author, $site,
					$image, $image_thumb, $image_align, $image_alt, $tagword, $hits, $trans, $acc, $group, $act, $images, $new, $imp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['down_add']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$textshort = preparse($textshort, THIS_TRIM);
			$textmore = preparse($textmore, THIS_TRIM);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($file, THIS_EMPTY) == 1 OR
				preparse($textshort, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($modname[PERMISS], $lang['down_add'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query("SELECT title, cpu FROM ".$basepref."_".PERMISS." WHERE title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['down_add'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (is_array($mirrors) AND ! empty($mirrors))
			{
				$m = 1;
				foreach ($mirrors as $k => $v)
				{
					if (isset($v['title']) AND ! empty($v['title']))
					{
						$mirror[$m] = array
							(
								'title' => $v['title'],
								'link' => $v['link']
							);
						$m ++;
					}
				}
				$mirrors = Json::encode($mirror);
			}

			if (is_array($images) AND ! empty($images))
			{
				$c = 1;
				foreach ($images as $k => $v)
				{
					if (isset($v['image_thumb']) AND ! empty($v['image_thumb']))
					{
						$img[$c] = array
							(
								'thumb' => $v['image_thumb'],
								'image' => $v['image'],
								'align' => $v['image_align'],
								'alt'   => str_replace(array("'", '"'), '', $v['image_alt']),
							);
						$c ++;
					}
				}
				$images = Json::encode($img);
			}

			if ( ! empty($author))
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $author);
			}

			$tags = ($tagword) ? implode(',', $tagword) : '';
			$catid = preparse($catid, THIS_INT);
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
			$image_thumb =  preparse($image_thumb, THIS_TRIM, 0, 255);
			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;
			$image_align = ($image_align == 'left') ? 'left' : 'right';

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$imp = ($imp == 1) ? 1 : 0;

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS." VALUES (
					 NULL,
					 '".$catid."',
					 '".$public."',
					 '".$stpublic."',
					 '".$unpublic."',
					 '".$db->escape($cpu)."',
					 '".$db->escape(preparse_sp($customs))."',
					 '".$db->escape($file)."',
					 '".$db->escape($size)."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape(preparse_sp($title))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape($textshort)."',
					 '".$db->escape($textmore)."',
					 '".$db->escape($textnotice)."',
					 '".$db->escape($mirrors)."',
					 '".$db->escape(preparse_sp($relis))."',
					 '".$db->escape(preparse_sp($author))."',
					 '".$db->escape($site)."',
					 '".$db->escape($image)."',
					 '".$db->escape($image_thumb)."',
					 '".$image_align."',
					 '".$db->escape(preparse_sp($image_alt))."',
					 '0',
					 '0',
					 '0',
					 '0',
					 '0',
					 '".$act."',
					 '".$acc."',
					 '".$db->escape($group)."',
					 '0',
					 '0',
					 '".$db->escape($tags)."',
					 '".$db->escape($images)."',
					 '".$imp."'
					 )"
				);

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);

			if ($new == 'yes')
			{
				$id = preparse($id, THIS_INT);
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_user WHERE id = '".$id."'");

				$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				redirect($redir);
			}
			else
			{
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Редактировать файл
		 ------------------------*/
		if ($_REQUEST['dn'] == 'edit' OR $_REQUEST['dn'] == 'newadd')
		{
			global $catid, $id, $selective, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$id = preparse($id, THIS_INT);
			$p = preparse($p, THIS_INT);
			$cat = preparse($cat, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$s = preparse($s, THIS_TRIM, 1, 7);
			$l = preparse($l, THIS_TRIM, 1, 4);
			$fid = preparse($fid, THIS_INT);

			if ($_REQUEST['dn'] == 'newadd')
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_user WHERE id = '".$id."'"));

				$item['cpu'] = $item['customs'] = $item['descript'] = $item['keywords'] = $item['size'] =
				$item['relis'] = $item['site'] = $item['textnotice'] = $item['tags'] = $item['images'] = '';
				$item['hits'] = $item['trans'] = 0;
				$item['image_alt'] = ( ! empty($item['image_thumb'])) ? $item['title'] : '';
				$item['subtitle'] = $item['title'];
				$item['image_align'] = 'left';
				$item['act'] = 'yes';
				$item['acc'] = 'all';
				$item['imp'] = 0;

				// Автор
				$author = '&#8212;';
				if (isset($conf['userbase']))
				{
					if ($conf['userbase'] == 'danneo') {
						require_once(WORKDIR.'/core/userbase/danneo/danneo.user.php');
					} else {
						require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
					}

					$userapi = new userapi($db, false);

					if ( ! empty($item['userid']))
					{
						$udata = $userapi->userdata('userid', $item['userid']);
						if ( ! empty($udata))
						{
							$author = $udata['uname'];
						}
					}
				}
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
				$author = $item['author'];
			}

			$public = CalendarFormat($item['public']);
			$stpublic = ( ! isset($item['stpublic']) OR $item['stpublic'] == 0) ? '' : CalendarFormat($item['stpublic']);
			$unpublic = ( ! isset($item['unpublic']) OR $item['unpublic'] == 0) ? '' : CalendarFormat($item['unpublic']);

			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script src="'.ADMPATH.'/js/jquery.autocomplete.js"></script>
					<script>
						var all_name   = "'.$lang['all_name'].'";
						var all_cpu    = "'.$lang['all_cpu'].'";
						var all_popul  = "'.$lang['all_popul'].'";
						var all_thumb  = "'.$lang['all_image_thumb'].'";
						var all_img    = "'.$lang['all_image'].'";
						var all_images = "'.$lang['all_image_big'].'";
						var all_align  = "'.$lang['all_align'].'";
						var all_right  = "'.$lang['all_right'].'";
						var all_left   = "'.$lang['all_left'].'";
						var all_center = "'.$lang['all_center'].'";
						var all_alt    = "'.$lang['all_alt_image'].'";
						var all_copy   = "'.$lang['all_copy'].'";
						var all_delet  = "'.$lang['all_delet'].'";
						var code_paste = "'.$lang['code_paste'].'";
						var all_file   = "'.$lang['all_file'].'";
						var all_link   = "'.$lang['all_link'].'";
						var page       = "'.PERMISS.'";
						var ops        = "'.$sess['hash'].'";
						var filebrowser = "'.$lang['filebrowser'].'";
						$(function() {
							$(".imgcount").focus(function () {
								$(this).select();
							}).mouseup(function(e){
								e.preventDefault();
							});
						});
					</script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" name="total-form" id="total-form">
					<table class="work">
						<caption>'.$lang['down_edit'].': '.preparse_un($item['title']).'</caption>
						<tr>
							<th></th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
						<td><input type="text" name="title" id="title" size="70" value="'.preparse_un($item['title']).'" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].':</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$item['cpu'].'" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" value="'.preparse_un($item['customs']).'" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$public.'" />';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic" value="'.$stpublic.'" />';
								Calendar('stcal', 'stpublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_unpublic'].'</td>
							<td><input type="text" name="unpublic" id="unpublic" value="'.$unpublic.'" />';
								Calendar('uncal','unpublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0"> &#8212; '.$lang['cat_not'].' &#8212; </option>';
				$catcache = array();
				$catid = $item['catid'];
				while ($items = $db->fetchrow($inqcat))
				{
					$catcache[$items['parentid']][$items['catid']] = $items;
				}
				this_selectcat(0);
				echo				$selective.'
								</select>
							</td>
						</tr>';
			}
			echo '		<tr><th></th><th class="site">'.$lang['all_file'].'</th></tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['down_add'].'</td>
							<td>
								<input type="text" name="file" id="file" size="70" value="'.$item['file'].'" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/file/\',\'&amp;field[1]=file\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.fileallupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/file/\',\'&amp;field=file\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['down_size'].'</td>
							<td><input type="text" name="size" size="25" value="'.$item['size'].'" />';
								$tm->outhint($lang['down_add_size_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['file_version'].'</td>
							<td><input type="text" name="relis" size="25" value="'.$item['relis'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['author'].'</td>
							<td><input type="text" name="author" size="25" value="'.$author.'" /></td>
						</tr>
						<tr>
							<td>'.$lang['author_site'].'</td>
							<td><input type="text" name="site" size="25" value="'.$item['site'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_hits'].'</td>
							<td><input type="text" name="hits" size="25" maxlength="10" value="'.$item['hits'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['down_col'].'</td>
							<td><input type="text" name="trans" size="25" maxlength="10" value="'.$item['trans'].'" /></td>
						</tr>
						<tr>
							<td>'.$lang['mirrors'].'</td>
							<td class="vm">
								<div id="mirror-area">';
			$mirrors = Json::decode($item['mirrors']);
			$im = 0;
			if (is_array($mirrors))
			{
				foreach ($mirrors as $v)
				{
					$im ++;
					echo '			<div class="section tag" id="mirror-'.$im.'">
										<table class="work">
										<tr>
											<td class="first"><span>* *</span> '.$lang['all_name'].'&nbsp; &#8260; &nbsp;'.$lang['all_link'].'</td>
											<td class="nowrap">
												<input name="mirrors['.$im.'][title]" size="40" type="text" value="'.$v['title'].'" required="required" />
												<input name="mirrors['.$im.'][link]" id="mirrors'.$im.'" size="55" type="text" value="'.$v['link'].'" required="required" />
												<a class="side-button" href="javascript:$.removetaginput(\'total-form\',\'mirror-area\',\'mirror-'.$im.'\');">&#215;</a>
											</td>
										</tr>
										</table>
									</div>';
				}
			}
			echo '				</div>
								<div><a class="side-button" href="javascript:$.addmirror(\'total-form\',\'mirror-area\');">'.$lang['all_submint'].'</a></div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr><th></th><th class="site">'.$lang['descript'].'</th></tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['input_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS', 1);
				$WYSFORM = 'textshort';
				$WYSVALUE = $item['textshort'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textshort', 5, 70, $item['textshort'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'textmore';
				$WYSVALUE = $item['textmore'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textmore', 7, 70, $item['textmore'], 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">';
			$img = Json::decode($item['images']);
			$class = (is_array($img) AND sizeof($img) > 0) ? ' class="image-area"' : '';
			echo '		<tr>
							<td>'.$lang['img_extra_hint'].'</td>
							<td class="vm">
								<div id="image-area"'.$class.'>';
			$ic = 0;
			if (is_array($img))
			{
				foreach ($img as $k => $v)
				{
					$ic ++;
					echo '			<div class="section tag" id="imginput'.$ic.'" style="display:block;">
										<table class="work">
											<tr>
												<td>';
					if ( ! empty($v['image'])) {
						echo '						<img class="sw50" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_thumb'].'" />';
					} else {
						echo '						<img class="sw70" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_big'].'" />';
					}
					echo '							<input type="hidden" name="images['.$ic.'][image_thumb]" value="'.$v['thumb'].'" />';
					if ( ! empty($v['image'])) {
						echo '						&nbsp;&nbsp;<img class="sw70" src="'.WORKURL.'/'.$v['image'].'" alt="'.$lang['all_image'].'" />
													<input type="hidden" name="images['.$ic.'][image]" value="'.$v['image'].'" />';
					}
					echo '						</td>
												<td>
													<a class="but fr" href="javascript:$.filebrowserimsremove(\''.$ic.'\');" title="'.$lang['all_delet'].'">x</a>
													<p><input type="text" size="3" value="{img'.$ic.'}" class="imgcount" readonly="readonly" title="'.$lang['all_copy'].'" /> <cite>'.$lang['code_paste'].'</cite></p>
													<p class="label">'.$lang['all_align'].'&nbsp; &nbsp; &nbsp; &nbsp;'.$lang['all_alt_image'].'</p>
													<p>
														<select name="images['.$ic.'][image_align]">
															<option value="left"'.(($v['align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
															<option value="right"'.(($v['align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
															<option value="center"'.(($v['align'] == 'center') ? ' selected' : '').'>'.$lang['all_center'].'</option>
														</select>&nbsp; &nbsp; &nbsp;
														<input type="text" name="images['.$ic.'][image_alt]" size="25" value="'.$v['alt'].'" />
													</p>
												</td>
											</tr>
										</table>
									</div>';
				}
			}
			echo '				</div>
								<div>
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;ims=1\');">'.$lang['filebrowser'].'</a>&nbsp;
									<a class="side-button" href="javascript:$.personalupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/img/\');">'.$lang['file_upload'].'</a>
								</div>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['user_texts'].'</td>
							<td>';
								$tm->textarea('textnotice', 2, 70, $item['textnotice'], true, false, 'ignorewysywig');
				echo '		</td>
						</tr>';
			}
			if($conf[PERMISS]['tags'] == 'yes')
			{
				echo '	<tr>
							<th></th><th class="site">&nbsp;'.$lang['all_tags'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="tagarea">
								<table class="work">
									<tr>
										<td class="pw45 vm">
											<select name="tagin" id="tagin" size="5" multiple class="blue pw100 app">';
				$tagword = $tagshow = NULL;
				if ( ! empty($item['tags']))
				{
					$tag_in = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag WHERE tagid IN (".$item['tags'].")");
					while ($tag = $db->fetchrow($tag_in))
					{
						$tagshow.= '				<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
						$tagword.= '				<input type="hidden" name="tagword[]" value="'.$tag['tagid'].'" />';
					}
				}
				$sql = ( ! empty($item['tags'])) ? ' WHERE tagid NOT IN ('.$item['tags'].')' : '';
				$tag_not = $db->query("SELECT tagid, tagword FROM ".$basepref."_".PERMISS."_tag".$sql);
				while ($tag = $db->fetchrow($tag_not))
				{
						echo '					<option value="'.$tag['tagid'].'">'.$tag['tagword'].'</option>';
				}
				echo '						</select>
										</td>
										<td class="ac pw10 vm">
											<input class="side-button" type="button" onclick="$.addtag();" value="&#9658;" /><br /><br />
											<input class="side-button" type="button" onclick="$.deltag();" value="&#9668;" />
										</td>
										<td>
											<select name="tagout" id="tagout" size="5" multiple class="green pw100 app">
												'.$tagshow.'
											</select>
											<div id="area-tags">
												'.$tagword.'
											</div>
										</td>
									</tr>
								</table>
								</div>
							</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr><th></th><th class="site">'.$lang['all_image_big'].'</th></tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" value="'.$item['image_thumb'].'" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/img/\',\'&amp;field[1]=image_thumb&amp;field[2]=image\')" value="'.$lang['filebrowser'].'" type="button" />
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir=/'.PERMISS.'/img/\')" value="'.$lang['file_review'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" value="'.$item['image'].'" size="70" type="text" />
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/img/\',\'&amp;field[1]=image&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" value="'.preparse_un($item['image_alt']).'" /></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_align_image'].'</td>
							<td>
								<select name="image_align" class="sw165">
									<option value="left"'.(($item['image_align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
									<option value="right"'.(($item['image_align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
								</select>
							</td>
						</tr>
						<tr><th></th><th class="site">'.$lang['options'].'</th></tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].'</option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['who_down'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all"'.(($item['acc'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['acc'] == 'user' AND empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['acc'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['acc'] == 'all' OR $item['acc'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').'><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['all_important'].'</td>
							<td>
								<select name="imp" class="sw165">
									<option value="0"'.(($item['imp'] == 0) ? ' selected' : '').'>'.$lang['all_no'].'</option>
									<option value="1"'.(($item['imp'] == 1) ? ' selected' : '').'>'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" id="imgid" value="'.$ic.'" />
								<input type="hidden" id="mirrid" value="'.$im.'" />';
			if ($conf['cpu'] == 'no') {
				echo '			<input type="hidden" name="cpu" />';
			}
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="textnotice" />
								<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="cat" value="'.$cat.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="s" value="'.$s.'" />
								<input type="hidden" name="l" value="'.$l.'" />';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'" />';
			}
			if ($_REQUEST['dn'] == 'newadd') {
				echo '			<input type="hidden" name="dn" value="addsave" />
								<input type="hidden" name="new" value="yes" />';
			} else {
				echo '			<input type="hidden" name="dn" value="editsave" />';
			}
			echo '				<input class="main-button" value="'.$lang['all_go'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			echo "	<script>
						$(document).ready(function() {
							$('#tabs a').tabs('.tab-1');
						});
					</script>";

			$tm->footer();
		}

		/**
		 * Редактировать файл (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global	$id, $catid, $public, $stpublic, $unpublic, $cpu, $file, $size, $descript, $keywords, $title,
					$textshort, $textmore, $textnotice, $mirrors, $relis, $author, $site,
					$image, $image_thumb, $image_align, $image_alt, $tagword, $hits, $trans, $acc, $act, $fid, $images, $imp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['all_edit']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$textshort = preparse($textshort, THIS_TRIM);
			$textmore = preparse($textmore, THIS_TRIM);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);

			$id = preparse($id, THIS_INT);
			$fid = preparse($fid, THIS_INT);

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($file, THIS_EMPTY) == 1 OR
				preparse($textshort, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($modname[PERMISS], $lang['down_edit'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query
							(
								"SELECT title, cpu FROM ".$basepref."_".PERMISS."
								 WHERE (title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."')
								 AND id <> '".$id."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['down_edit'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if (is_array($mirrors) AND ! empty($mirrors))
			{
				$m = 1;
				foreach ($mirrors as $k => $v)
				{
					if (isset($v['title']) AND ! empty($v['title']))
					{
						$mirror[$m] = array
										(
											'title' => $v['title'],
											'link' => $v['link']
										);
						$m ++;
					}
				}
				$mirrors = Json::encode($mirror);
			}

			if (is_array($images) AND ! empty($images))
			{
				$c = 1;
				foreach ($images as $k => $v)
				{
					if (isset($v['image_thumb']) AND ! empty($v['image_thumb']))
					{
						$img[$c] = array
									(
										'thumb' => $v['image_thumb'],
										'image' => $v['image'],
										'align' => $v['image_align'],
										'alt'   => str_replace(array("'", '"'), '', $v['image_alt']),
									);
						$c ++;
					}
				}
				$images = Json::encode($img);
			}

			if ( ! empty($author))
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $author);
			}

			$tags = ( ! empty($tagword)) ? implode(',', $tagword) : '';
			$hits = ($hits) ? preparse($hits, THIS_INT) : 0;
			$trans = ($trans) ? preparse($trans, THIS_INT) : 0;
			$catid = preparse($catid, THIS_INT);
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
			$image_thumb = preparse($image_thumb, THIS_TRIM, 0, 255);
			$image_align = ($image_align == 'left') ? 'left' : 'right';
			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$imp = ($imp == 1) ? 1 : 0;

			$count = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."'"));

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS." SET
					 catid       = '".$catid."',
					 public      = '".$public."',
					 stpublic    = '".$stpublic."',
					 unpublic    = '".$unpublic."',
					 cpu         = '".$db->escape($cpu)."',
					 customs     = '".$db->escape(preparse_sp($customs))."',
					 file        = '".$db->escape($file)."',
					 size        = '".$db->escape($size)."',
					 keywords    = '".$db->escape(preparse_sp($keywords))."',
					 descript    = '".$db->escape(preparse_sp($descript))."',
					 title       = '".$db->escape(preparse_sp($title))."',
					 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
					 textshort   = '".$db->escape($textshort)."',
					 textmore    = '".$db->escape($textmore)."',
					 textnotice  = '".$db->escape($textnotice)."',
					 mirrors     = '".$db->escape($mirrors)."',
					 relis       = '".$db->escape(preparse_sp($relis))."',
					 author      = '".$db->escape($author)."',
					 site        = '".$db->escape($site)."',
					 image       = '".$db->escape($image)."',
					 image_thumb = '".$db->escape($image_thumb)."',
					 image_align = '".$image_align."',
					 image_alt   = '".$db->escape(preparse_sp($image_alt))."',
					 hits        = '".$hits."',
					 trans       = '".$trans."',
					 act         = '".$act."',
					 acc         = '".$acc."',
					 groups      = '".$db->escape($group)."',
					 comments    = '".$db->escape($count['total'])."',
					 tags        = '".$db->escape($tags)."',
					 images      = '".$db->escape($images)."',
					 imp         = '".$imp."'
					 WHERE id = '".$id."'"
				);

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Изменение состояния (вкл./выкл.)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'act')
		{
			global $act, $id, $p, $nu, $cat, $s, $l, $fid;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			if ($act == 'no' OR $act == 'yes')
			{
				$db->query("UPDATE ".$basepref."_".PERMISS." SET act='".$act."' WHERE id = '".$id."'");
				$counts = new Counts(PERMISS, 'id');
			}

			$cache->cachesave(1);
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ($cat !== '') ? '&amp;cat='.$cat : '';
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Удалить файл
		 ------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $p, $cat, $nu, $ok, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			$fid = preparse($fid, THIS_INT);

			if ($ok == 'yes')
			{
				// del image
				$item = $db->fetchrow($db->query("SELECT image, image_thumb FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
				if ( ! empty($item['image_thumb']))
				{
					@unlink(WORKDIR.'/'.$item['image']);
					@unlink(WORKDIR.'/'.$item['image_thumb']);
				}
				$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");

				$counts = new Counts(PERMISS, 'id');
				$cache->cachesave(3);

				$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
				$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';
				$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

				$yes = 'index.php?dn=del&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');
				$not = 'index.php?dn=list&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');

				$tm->header();
				$tm->shortdel($lang['del_file'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Список недоступных файлов
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'brokenlist')
		{
			global $selective, $nu, $p, $cat;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['down_no_access']
				);

			$tm->header();

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query
					(
						"SELECT b.brokid, b.id, b.broktime, b.brokip, l.id, l.catid, l.title, l.cpu, COUNT(b.brokid) AS total
						 FROM ".$basepref."_".PERMISS."_broken AS b
						 LEFT JOIN ".$basepref."_".PERMISS." AS l ON (b.id = l.id)
						 GROUP BY b.id, b.brokid ORDER BY b.brokid ASC LIMIT ".$sf.", ".$nu
					);

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			$rows = $db->fetchrow($db->query("SELECT COUNT(brokid) AS total FROM ".$basepref."_".PERMISS."_broken GROUP BY id"));

			$pages = $lang['all_pages'].':&nbsp; '.(empty($rows['total']) ? '<span class="pages">1</span>' : adm_pages('', '', 'index', 'brokenlist', $nu, $p, $sess, false, $rows['total']));
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=brokenlist&amp;p=".$p."&amp;ops=".$sess['hash']."", $nu);

			$obj = $catcaches = array();
			$inquiry = $db->query("SELECT catid, parentid, catcpu, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			while ($item = $db->fetchrow($inquiry))
			{
				$obj[$item['catid']] = $item;
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['down_no_access'].'</caption>';
			if ($db->numrows($inq) > 0)
			{
				echo '	<tr><td colspan="6">'.$amount.'</td></tr>
						<tr>
							<th class="work-no-sort al pw25">'.$lang['all_name'].'</th>
							<th class="work-no-sort">'.$lang['all_cat_one'].'</th>
							<th class="work-no-sort">'.$lang['one_add'].'</th>
							<th class="work-no-sort">'.$lang['ip_adress'].'</th>
							<th class="work-no-sort">'.$lang['all_col'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
						</tr>';
				while ($item = $db->fetchrow($inq))
				{
					$cpu = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
					$catcpu = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';
					echo '	<tr class="list">
								<td class="al">
									<a href="index.php?dn=edit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'" title="'.$lang['down_edit'].'">'.preparse_un($item['title']).'</a>
									<a class="fr" href="'.$conf['site_url'].$ro->seo('index.php?dn='.PERMISS.$catcpu.'&amp;to=page&amp;id='.$item['id'].$cpu).'" target="_blank"><img src="'.ADMPATH.'/template/images/blank.png" alt="'.$lang['all_chek'].'" /></a>
								</td>
								<td>'.linecat($item['catid'], $catcaches).'</td>
								<td>'.format_time($item['broktime'], 1, 1).'</td>
								<td>'.$item['brokip'].'</td>
								<td>'.$item['total'].'</td>
								<td class="gov">
									<a href="index.php?dn=edit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['down_edit'].'" /></a>
									<a href="index.php?dn=brokendel&amp;p='.$p.'&amp;nu='.$nu.'&amp;brokid='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['delet_of_list'].'" /></a>
								</td>
							</tr>';
				}
				echo '		<tr><td colspan="6">'.$pages.'</td></tr>';
			}
			else
			{
				echo '	<tr>
							<td class="ac" colspan="6">
								<div class="pads">'.$lang['down_brok_no'].'</div>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="6">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="dn" value="brokenclear" />
								<input class="main-button" value="'.$lang['clear_list'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Удалить позицию из списка недоступных
		 -----------------------------------------*/
		if ($_REQUEST['dn'] == 'brokendel')
		{
			global $brokid, $nu, $p;

			$brokid = preparse($brokid,	THIS_INT);

			if ($brokid > 0)
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_broken WHERE id = '".$brokid."'");
			}

			$redir = 'index.php?dn=brokenlist&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Очистить список недоступных
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'brokenclear')
		{
			$db->query("DELETE FROM ".$basepref."_".PERMISS."_broken");

			redirect('index.php?dn=brokenlist&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление презентации
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'mediaadd')
		{
			global $ajax, $id, $p, $cat, $nu;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$p = preparse($p, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}
			else
			{
				echo '	<script>
						$(document).ready(function()
						{
							$("#submit").click(function()
							{
								var data = $("#media-add").serialize() + "&ajax=1";
								var $elm = $.fn.colorbox.element().children("img"), $medialist = $("select#media-list");
								$.ajax({
									type: "POST",
									cache : false,
									url : this.action,
									data : data,
									error : function (msg) { },
									success : function (d) { }
								});
								if ($medialist.val() == 0) {
									$elm.attr({src : "'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/media.png"});
									$elm.parent().removeClass("exmedia");
								} else {
									$elm.attr({src : "'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/media.png"});
									$elm.parent().addClass("exmedia");
								}
								$.fn.colorbox.close();
								return false;
							});
						});
						</script>';
			}

			$items = $db->fetchrow($db->query("SELECT title, listid FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$inquiry = $db->query("SELECT catid, listname FROM ".$basepref."_media_cat");

			echo '	<form action="index.php" method="post" id="media-add">
					<table class="fb-work">
						<caption>'.$lang['attach_media'].'</caption>
						<tr>
							<td class="first ar" style="width:25%"><span>*</span> '.$lang['media'].'</td>
							<td>
								<select name="list" id="media-list" size="10" style="width: 100%; height: 100px;" class="app">
									<option value="0"'.(($items['listid'] == 0) ? ' selected' : '').'> &#8212; '.$lang['out_attach_media'].' &#8212; </option>';
			while ($item = $db->fetchrow($inquiry)) {
				echo '                <option value="'.$item['catid'].'"'.(($items['listid'] AND $item['catid'] == $items['listid']) ? ' selected' : '').'>'.$item['listname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="dn" value="mediaaddsave" />
								<input type="hidden" name="ajax" value="'.$ajax.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="cat" value="'.$cat.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input id="submit" class="but" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Добавление презентации (сохранение)
		 --------------------------------------*/
		if ($_REQUEST['dn'] == 'mediaaddsave')
		{
			global $ajax, $id, $list, $nu, $p, $cat;

			$id = preparse($id, THIS_INT);
			$list = preparse($list, THIS_INT);
			$ajax = preparse($ajax, THIS_INT);

			if ($id > 0)
			{
				$db->query("UPDATE ".$basepref."_".PERMISS." SET listid = '".$list."' WHERE id = '".$id."'");
			}

			if ($ajax == 0)
			{
				$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

				redirect($redir);
			}
		}

		/**
		 * Добавленные файлы
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'new')
		{
			global $selective, $nu, $p, $cat;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['down_added']
				);

			$tm->header();

			if (isset($conf['userbase']))
			{
				if ($conf['userbase'] == 'danneo') {
					require_once(WORKDIR.'/core/userbase/danneo/danneo.user.php');
				} else {
					require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
				}

				$userapi = new userapi($db, false);
			}

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_user ORDER BY id DESC LIMIT ".$sf.", ".$nu);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_user', 'id', 'index', 'new', $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=new&amp;p=".$p."&amp;ops=".$sess['hash']."", $nu);

			$catcache = $catcaches = array();
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			while ($item = $db->fetchrow($inquiry))
			{
				$catcache[$item['parentid']][$item['catid']] = $item;
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['down_added'].'</caption>';
			if ($db->numrows($inq) > 0)
			{
				echo '	<tr><td colspan="6">'.$amount.'</td></tr>
						<tr>
							<th class="work-no-sort al pw25">'.$lang['all_name'].'</th>
							<th class="work-no-sort">'.$lang['all_cat_one'].'</th>
							<th class="work-no-sort">'.$lang['author'].'</th>
							<th class="work-no-sort">'.$lang['input_date'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac pw5"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox" /></th>
						</tr>';
				while ($item = $db->fetchrow($inq))
				{
					// Автор
					$author = '&#8212;';
					if ( ! empty($item['userid']))
					{
						if (in_array('user', $realmod))
						{
							$udata = $userapi->userdata('userid', $item['userid']);
							if ( ! empty($udata))
							{
								$author = '<a href="'.$conf['site_url'].$ro->seo($userapi->data['linkprofile'].$item['userid']).'" title="'.$lang['profile'].'" target="_blank">'.$udata['uname'].'</a>';
							}
						}
					}

					echo '	<tr class="list">
								<td class="al"><a href="index.php?dn=newadd&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['all_add'].'">'.preparse_un($item['title']).'</a></td>
								<td>'.preparse_un(linecat($item['catid'], $catcaches)).'</td>
								<td>'.$author.'</td>
								<td>'.format_time($item['public'], 1, 1).'</td>
								<td class="gov">
									<a href="index.php?dn=newadd&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['all_add'].'" /></a>
									<a href="index.php?dn=newdel&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
								<td class="check">
									<input type="checkbox" name="array['.$item['id'].']" value="yes" />
								</td>
							</tr>';
				}
				echo '	<tr><td colspan="6">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="6">
								<input type="hidden" name="dn" value="newarrdel" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_delet'].'" type="submit" />
							</td>
						</tr>';
			}
			else
			{
				echo '	<tr>
							<td class="ac">
								<div class="pads">'.$lang['data_not'].'</div>
							</td>
						</tr>';
			}
			echo '	</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Удаление добавленного файла
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'newdel')
		{
			global $id, $p, $cat, $nu, $ok, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				// del image
				$item = $db->fetchrow($db->query("SELECT image, image_thumb FROM ".$basepref."_".PERMISS."_user WHERE id = '".$id."'"));
				if ( ! empty($item['image_thumb']))
				{
					@unlink(WORKDIR.'/'.$item['image']);
					@unlink(WORKDIR.'/'.$item['image_thumb']);
				}

				$db->query("DELETE FROM ".$basepref."_".PERMISS."_user WHERE id = '".$id."'");

				$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_user WHERE id = '".$id."'"));

				$yes = 'index.php?dn=newdel&amp;p='.$p.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=new&amp;p='.$p.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_file'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Массовое удаление добавленных файлов
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'newarrdel')
		{
			global $array, $p, $nu, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['array_control']
				);

			$p	= preparse($p, THIS_INT);
			$nu	= preparse($nu, THIS_INT);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				if ($ok == 'yes')
				{
					allarrdel($array, 'id', PERMISS.'_user', 0, 1); // 1 : del thumb

					$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
					$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
					$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

					redirect($redir);
				}
				else
				{
					$temparray = $array;
					$count = count($temparray);
					$hidden = null;

					foreach ($array as $key => $id)
					{
						$hidden.= '<input type="hidden" name="array['.$key.']" value="yes" />';
					}

					$h = '	<input type="hidden" name="p" value="'.$p.'" />
							<input type="hidden" name="nu" value="'.$nu.'" />';

					$tm->header();

					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['all_delet'].': '.$lang['down_all'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="newarrdel" />
										<input type="hidden" name="ok" value="yes" />
										<input type="hidden" name="ops" value="'.$sess['hash'].'" />
										<input class="main-button" value="'.$lang['all_go'].'" type="submit" />
										<input class="main-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button" />
									</td>
								</tr>
							</table>
							</form>
							</div>';

					$tm->footer();
				}
			}

			$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Комментарии - все файлы
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'comment')
		{
			global $nu, $p, $id, $ajax, $atime;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if ($ajax == 0) {
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$total = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND (ctime >= '".$atime."')"));
			if (($p - 1) * $nu > $total['total'])
			{
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("comment WHERE file = '".PERMISS."' AND (ctime >= '".$atime."') ORDER BY comid DESC", 'id', ADMPATH.'/mod/'.PERMISS.'/index', 'comment&amp;atime='.$atime.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages(ADMPATH.'/mod/'.PERMISS.'/index.php?dn=comment&amp;p='.$p.'&amp;atime='.$atime.'&amp;ops='.$sess['hash'].'&amp;ajax='.$ajax, $nu);

			$inq = $db->query
					(
						"SELECT a.*, b.title FROM ".$basepref."_comment AS a
						 LEFT JOIN ".$basepref."_".PERMISS." AS b ON (a.id = b.id)
						 WHERE a.file = '".PERMISS."' AND (a.ctime >= '".$atime."')
						 ORDER BY comid DESC LIMIT ".$sf.", ".$nu
					);

			echo '	<script>
					$(document).ready(function()
					{
						$("#select, #selects").click(function() {
							$("#comment-form input[type=checkbox]").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
					});
					</script>';

			if ($ajax)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache:false,async:false});
							$(".sort a").colorbox({
											width      : "92%",
											height     : "90%",
											maxHeight  :  800,
											maxWidth   :  1200,
											fixed: true,
											"href"     : $(this).attr("href"),
											onComplete : function () {
												var $h = $("#cboxLoadedContent").height();
												$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
											}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#comment-form");
									$.ajax({
										cache   : false,
										type    : "POST",
										data    : $elm.serialize() + "&ajax=1",
										url     : $.apanel + "/mod/'.PERMISS.'/index.php",
										error   : function(data) {  },
										success : function(data) { $("#comment-form").html(data).show(); }
									});
								},
								width      : "92%",
								height     : "90%",
								maxHeight  :  800,
								maxWidth   :  1200,
								fixed: true,
								onComplete : function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								},
								"href" : $.apanel + "/mod/'.PERMISS.'/index.php?dn=comment&p='.$p.'&nu='.$nu.'&atime='.$atime.'&ops='.$sess['hash'].'&ajax=1&t='.time().'"
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<form id="comment-form" action="index.php" method="post">
					<table class="fb-work">
						<caption>'.$modname[PERMISS].'&nbsp; &#8260; &nbsp;'.(($atime == 0) ? $lang['all_comments'] : $lang['comment_last']).'</caption>
						<tr>
							<td class="sort" colspan="3">'.$amount.'</td>
						</tr>
					</table>';
			echo '	<div id="fb-work-comm">
					<table class="fb-work">
						<tr>
							<th class="ac">'.$lang['author'].'</th>
							<th>'.$lang['all_file'].'</th>
							<th>'.$lang['comment_text'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><input class="but" id="selects" value="x" type="button" title="'.$lang['all_delet'].'" /></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac pw10">';
				if ($item['userid'] > 0) {
					echo '		<a href="'.ADMPATH.'/mod/user/index.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['cname'].'</a>';
				} else {
					echo '		'.$item['cname'];
				}
				echo '		</td>
							<td class="vt pw20">'.$item['title'].'</td>
							<td>';
								$tm->textarea('text['.$item['comid'].']', 5, 25, $item['ctext'], 1);
				echo '		</td>
							<td class="vt pw16">'.format_time($item['ctime'], 1, 1).'</td>
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['comid'].']" value="1" /></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="atime" value="'.$atime.'" />
								<input type="hidden" name="dn" value="commentrep" />
								<input class="but submit" value=" '.$lang['all_save'].' " type="'.(($ajax) ? 'button' : 'submit').'" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Комментарии - все файлы (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'commentrep')
		{
			global $id, $p, $nu, $text, $author, $dels, $ajax, $atime;

			$ajax = preparse($ajax, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$p = preparse($p, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					$key = intval($key);
					if (isset($dels[$key]) AND $dels[$key] == 1)
					{
						$count = $db->fetchrow($db->query("SELECT id FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND comid = '".$key."'"));
						$db->query("UPDATE ".$basepref."_".PERMISS." SET comments = comments - 1 WHERE id = '".$count['id']."'");
						$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND comid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$authors = preparse($author[$key], THIS_TRIM, 0, 255);
							$texts = preparse($text[$key], THIS_TRIM);

							if ($authors) {
								$db->query("UPDATE ".$basepref."_comment SET cname = '".$db->escape($authors)."', ctext = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
							} else {
								$db->query("UPDATE ".$basepref."_comment SET ctext = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
							}
						}
					}
				}
			}

			if ($ajax == 0)
			{
				redirect('index.php?dn=comment&amp;p='.$p.'&amp;nu='.$nu.'&amp;atime='.$atime.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Комментарии отдельного файла
		 ---------------------------------*/
		if ($_REQUEST['dn'] == 'commentedit')
		{
			global $nu, $p, $id, $ajax;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$total = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."' ORDER BY comid DESC"));
			if (($p - 1) * $nu > $total['total']) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("comment WHERE file = '".PERMISS."' AND id='".$id."' ORDER BY comid DESC", 'id', 'index', 'commentedit&amp;id='.$id.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages('index.php?dn=commentedit&amp;p='.$p.'&amp;id='.$id.'&amp;ops='.$sess['hash'].'&amp;ajax='.$ajax, $nu);

			$inq = $db->query("SELECT * FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id='".$id."' ORDER BY comid DESC LIMIT ".$sf.", ".$nu."");
			$alltotal = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

			echo '	<script>
					$(function()
					{
						$("#select, #selects").click(function() {
							$("#comment-form input[type=checkbox]").each(function() {
								this.checked = (this.checked) ? false : true;
							});
						});
					});
					</script>';
			if ($ajax)
			{
				echo '	<script>
						$(function()
						{
							$.ajaxSetup({cache:false,async:false});
							$(".sort a").colorbox({
								width      : "92%",
								height     : "90%",
								maxHeight  :  800,
								maxWidth   :  1200,
								fixed: true,
								"href"     : $(this).attr("href"),
								onComplete : function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 145) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#comment-form");
									$.ajax({
										cache   : false,
										type    : "POST",
										data    : $elm.serialize() + "&ajax=1",
										url     : "index.php",
										error   : function(data) {  },
										success : function(data) {  }
									});
								},
								width     : "92%",
								height    : "90%",
								maxHeight :  800,
								maxWidth  :  1200,
								fixed: true,
								onComplete: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								},
								"href"  : "'.ADMPATH.'/mod/'.PERMISS.'/index.php?dn=commentedit&p='.$p.'&id='.$id.'&ops='.$sess['hash'].'&ajax=1&t='.time().'"
							});
						});
						</script>';
			}
			echo '	<div class="section">
					<form action="index.php" method="post" name="comment-form" id="comment-form">
					<table class="fb-work">
						<caption>'.$lang['menu_comment'].'&nbsp; &#8260; &nbsp;'.$alltotal['title'].'</caption>
						<tr>
							<td class="sort" colspan="3">'.$amount.'</td>
						</tr>
					</table>';
			echo '	<div id="fb-work-comm">
					<table class="fb-work">
						<tr>
							<th class="ac">'.$lang['author'].'</th>
							<th>'.$lang['comment_text'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><input class="but" id="selects" value="x" type="button" title="'.$lang['all_delet'].'" /></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac vm">';
				if ($item['userid'] > 0) {
					echo '		<a href="'.ADMPATH.'/mod/user/index.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['cname'].'</a>';
                } else {
					echo '		'.$item['cname'];
				}
				echo '		</td>
							<td>';
								$tm->textarea('text['.$item['comid'].']', 5, 25, $item['ctext'], 1);
				echo '		</td>
							<td class="vt pw16">'.format_time($item['ctime'], 1, 1).'</td>
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['comid'].']" value="1" /></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="dn" value="commenteditrep" />
								<input class="but submit" value=" '.$lang['all_save'].' " type="'.(($ajax) ? 'button' : 'submit').'" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			if ($ajax == 0)
			{
				$tm->footer();
			}
		}

		/**
		 * Комментарии отдельного файла (сохранение)
		 ---------------------------------------------*/
		if ($_REQUEST['dn'] == 'commenteditrep')
		{
			global $id, $p, $nu, $text, $author, $dels, $ajax;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$p = preparse($p, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					$key = intval($key);
					if (isset($dels[$key]) AND $dels[$key] == 1)
					{
						$db->query("UPDATE ".$basepref."_".PERMISS." SET comments = comments-1 WHERE id = '".$id."'");
						$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND comid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$authors = preparse($author[$key], THIS_TRIM, 0, 255);
							$texts = preparse($text[$key], THIS_TRIM);

							if ($authors) {
								$db->query("UPDATE ".$basepref."_comment SET cname = '".$db->escape($authors)."', ctext = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
							} else {
								$db->query("UPDATE ".$basepref."_comment SET ctext = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
							}
						}
					}
				}
			}

			$count = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."'"));
			$db->query("UPDATE ".$basepref."_".PERMISS." SET comments = '".$count['total']."' WHERE id = '".$id."'");

			if ($ajax == 0)
			{
				redirect('index.php?dn=commentedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Быстрое редактирование названия файла
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxedittitle')
		{
			global $id;

			$id = preparse($id, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

			echo '	<form action="index.php" method="post" id="post" onsubmit="return $.posteditor(this,\'te'.$item['id'].'\',\'index.php?dn=ajaxsavetitle&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 400px;">
						<input type="text" name="title" size="60" value="'.preparse_un($item['title']).'" />&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavetitle" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" » " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое редактирование названия файла (сохранение)
		 ------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavetitle')
		{
			global $id, $title;

			$id = preparse($id, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);
			if ($id > 0 AND $title) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET title = '".$db->escape(preparse_sp($title))."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'te'.$id.'\',\'405\')">'.preparse_un($title).'</a>';
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение категории файла
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditcat')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

			echo '	<form action="index.php" method="post" id="post" name="post" onsubmit="return $.posteditor(this,\'ce'.$item['id'].'\',\'index.php?dn=ajaxsavecat&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 290px;">
						<select name="catid" style="float: left; width: 240px;">
							<option value="0">'.$lang['cat_not'].'</option>';
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			$catid = $item['catid'];
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
			}
			this_selectcat(0);
			echo '			'.$selective.'
						</select>&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavecat" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" » " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение категории файла (сохранение)
		 ----------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavecat')
		{
			global $id, $catid;

			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);

			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET catid='".$catid."' WHERE id = '".$id."'");
			}

			$inquiry = $db->query("SELECT catid,parentid,catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcaches = array();
			while ($item = $db->fetchrow($inquiry))
			{
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'ce'.$id.'\',\'305\')">'.preparse_un(linecat($catid,$catcaches)).'</a>';

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение даты файла
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditdate')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time = CalendarFormat($item['public']);

			echo '	<form action="index.php" method="post" id="post" name="post" onsubmit="return $.posteditor(this,\'de'.$item['id'].'\',\'index.php?dn=ajaxsavedate&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 200px;">
						<input type="text" name="public" id="public" size="16" value="'.$time.'" />';
						Calendar('cal', 'public');
			echo '		<input type="hidden" name="ops" value="'.$sess['hash'].'" />
						<input type="hidden" name="dn" value="ajaxsavedate" />
						<input type="hidden" name="id" value="'.$id.'" />
						<input class="side-button" value=" » " type="submit" />
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение даты файла (сохранение)
		 ----------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavedate')
		{
			global $id, $public;

			$id = preparse($id, THIS_INT);
			$time = (empty($public)) ? NEWTIME : ReDate($public);

			if ($id > 0)
			{
				$db->query("UPDATE ".$basepref."_".PERMISS." SET public='".$time."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'de'.$id.'\',\'220\')">'.format_time($time,0,1).'</a>';

			$cache->cachesave(3);
			exit();
		}

		/**
		 * Получение списка слов подсказок автозаполнения, при добавлении тегов
		 ------------------------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'autocomplete')
		{
			$q = '';
			if (isset($_REQUEST['q'])) {
				$q = preparse($_REQUEST['q'], THIS_TRIM, 0, 255);
			}
			if ( ! $q) {
				return;
			}
			$inq = $db->query("SELECT tagcpu, tagword FROM ".$basepref."_".PERMISS."_tag WHERE tagword LIKE '%".$db->escape($q['query'])."%' ORDER BY tagword");
			while ($item = $db->fetchrow($inq)) {
				echo $item['tagword']."|".$item['tagcpu']."\n";
			}
			exit();
		}

		/**
		 * Все теги
		 -------------*/
		if ($_REQUEST['dn'] == 'tag')
		{
			global $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					$lang['block_down_tags']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$c  = $db->fetchrow($db->query("SELECT COUNT(tagid) AS total FROM ".$basepref."_".PERMISS."_tag"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag ORDER BY tagid DESC LIMIT ".$sf.", ".$nu);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_tag', 'tagid', 'index', 'tag', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages('index.php?dn=tag&amp;p='.$p.'&amp;ops='.$sess['hash'], $nu);

			echo '	<script>
						var all_cpu   = "'.$lang['all_cpu'].'";
						var all_name  = "'.$lang['all_name'].'";
						var all_popul = "'.$lang['all_popul'].'";
					</script>';
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_tags'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th class="ar pw20">'.$lang['all_name'].'</th>
							<th>'.$lang['all_cpu'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>'.$lang['all_popul'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="site">'.$item['tagword'].'</td>
							<td class="server">'.$item['tagcpu'].'</td>
							<td>';
				if( ! empty($item['icon'])) {
					echo '		<img src="'.WORKURL.'/'.$item['icon'].'" alt="'.preparse_un($item['tagword']).'" style="max-width: 36px; max-height: 27px; " />';
				}
				echo '		</td>
							<td><input type="text" name="ratingid['.$item['tagid'].']" value="'.$item['tagrating'].'" size="3" maxlength="3" /></td>
							<td class="gov">
								<a href="index.php?dn=tagedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$item['tagid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=tagdel&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$item['tagid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="5">
								<input type="hidden" name="dn" value="tagsetsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
						<tr><td colspan="5">'.$pages.'</td></tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$lang['all_tags'].': '.$lang['all_submint'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="tagword" id="tagword" size="70" required="required" />
							</td>
						</tr>';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="tagcpu" id="cpu" size="70" />';
								$tm->outtranslit('tagword', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="custom" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('tagdesc', 5, 50, '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_popul'].'</td>
							<td><input type="text" name="tagrating" size="25" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="tagaddsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
                          </td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Все теги, сохранение
		 -------------------------*/
		if ($_REQUEST['dn'] == 'tagsetsave')
		{
			global $ratingid, $p, $nu;

			if (preparse($ratingid, THIS_ARRAY) == 1)
			{
				this_tagup($ratingid, PERMISS);
			}

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p,THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu,THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Добавление метки (сохранение)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'tagaddsave')
		{
			global $tagcpu, $tagword, $custom, $keywords, $descript, $icon, $tagdesc, $tagrating, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['block_down_tags'].'</a>',
					$lang['all_add']
				);

			$tagword = preparse($tagword, THIS_TRIM, 0, 255);
			$icon    = preparse($icon, THIS_TRIM);
			$tagcpu = preparse($tagcpu, THIS_TRIM, 0, 255);
			$tagdesc = preparse($tagdesc, THIS_TRIM);
			$custom = preparse($custom, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);

			if (preparse($tagword, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS].'&nbsp; &#8260; &nbsp;'.$lang['all_tags'], $lang['all_add'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($tagcpu, THIS_EMPTY) == 1)
				{
					$tagcpu = cpu_translit($tagword);
				}

				$inqure = $db->query
							(
								"SELECT tagword, tagcpu FROM ".$basepref."_".PERMISS."_tag
								 WHERE tagword = '".$db->escape($tagword)."' OR tagcpu = '".$db->escape($tagcpu)."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['all_add'], $tagword, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$tagrating = ( ! empty($tagrating)) ? preparse($tagrating, THIS_INT) : 0;
			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_tag VALUES (
					 NULL,
					 '".$db->escape($tagcpu)."',
					 '".$db->escape(preparse_sp($tagword))."',
					 '".$db->escape(preparse_sp($tagdesc))."',
					 '".$db->escape(preparse_sp($custom))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape($icon)."',
					 '".$tagrating."'
					 )"
				);

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Редактировать метку
		 ----------------------*/
		if ($_REQUEST['dn'] == 'tagedit')
		{
			global $tagid, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['block_down_tags'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$tagid = preparse($tagid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['edit_tag'].': '.preparse_un($item['tagword']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="tagword" id="tagword" size="70" value="'.preparse_un($item['tagword']).'" required="required" />
							</td>
						</tr>';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="tagcpu" id="cpu" size="70" value="'.$item['tagcpu'].'" />';
								$tm->outtranslit('tagword', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="custom" size="70" value="'.preparse_un($item['custom']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'" />';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'" />&nbsp;
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button" />
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('tagdesc', 5, 50, $item['tagdesc'], 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_popul'].'</td>
							<td><input type="text" name="tagrating" size="25" value="'.$item['tagrating'].'" /></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="tageditsave" />
								<input type="hidden" name="p" value="'.$p.'" />
								<input type="hidden" name="nu" value="'.$nu.'" />
								<input type="hidden" name="tagid" value="'.$tagid.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать метку (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'tageditsave')
		{
			global $tagid, $tagword, $tagcpu, $custom, $keywords, $descript, $icon, $tagdesc, $tagrating, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['block_down_tags'].'</a>',
					$lang['all_edit']
				);

			$tagword = preparse($tagword, THIS_TRIM, 0, 255);
			$tagcpu = preparse($tagcpu, THIS_TRIM, 0, 255);
			$icon = preparse($icon, THIS_TRIM);
			$tagid = preparse($tagid, THIS_INT);

			if (preparse($tagword, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_tag'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($tagcpu, THIS_EMPTY) == 1)
				{
					$tagcpu = cpu_translit($tagword);
				}

				$inqure = $db->query
							(
								"SELECT tagid, tagcpu, tagword FROM ".$basepref."_".PERMISS."_tag
								 WHERE (tagcpu = '".$db->escape($tagcpu)."' OR tagword = '".$db->escape($tagword)."')
								 AND tagid <> '".$tagid."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['edit_tag'], $tagword, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$tagrating = ( ! empty($tagrating)) ? preparse($tagrating, THIS_INT) : 0;
			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_tag SET
					 tagcpu    = '".$db->escape($tagcpu)."',
					 tagword   = '".$db->escape(preparse_sp($tagword))."',
					 tagdesc   = '".$db->escape(preparse_sp($tagdesc))."',
					 custom    = '".$db->escape(preparse_sp($custom))."',
					 keywords  = '".$db->escape(preparse_sp($keywords))."',
					 descript  = '".$db->escape(preparse_sp($descript))."',
					 icon      = '".$db->escape($icon)."',
					 tagrating = '".$db->escape($tagrating)."'
					 WHERE tagid = '".$tagid."'"
				);

			$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Удаление тегов
		 ------------------*/
		if ($_REQUEST['dn'] == 'tagdel')
		{
			global $p, $nu, $ok, $tagid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['down_all'].'</a>',
					'<a href="index.php?dn=tag&amp;ops='.$sess['hash'].'">'.$lang['block_down_tags'].'</a>',
					$lang['all_delet']
				);

			$tagid = preparse($tagid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'");
				$db->increment(PERMISS.'_tag');

				$redir = 'index.php?dn=tag&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? '&amp;nu='.preparse($nu, THIS_INT) : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_tag WHERE tagid = '".$tagid."'"));
				$yes = 'index.php?dn=tagdel&amp;p='.$p.'&amp;nu='.$nu.'&amp;tagid='.$tagid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=tag&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $item['tagword'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($modname[PERMISS], $lang['no_access']);
		$tm->footer();
    }
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
