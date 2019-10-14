<?php
/**
 * File:        /admin/mod/faq/index.php
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
				'index', 'optsave', 'cat', 'catadd', 'catedit', 'catdel', 'cataddsave', 'catup', 'cateditsave', 'list',
				'work', 'newdel', 'arrdel', 'arrmove', 'arract', 'arracc', 'add', 'edit', 'addsave', 'editsave', 'del', 'act',
				'ajaxeditansdate', 'ajaxsaveansdate', 'ajaxeditdate', 'ajaxsavedate', 'ajaxeditcat', 'ajaxsavecat', 'ajaxedittitle', 'ajaxsavetitle',
				'new', 'newarrdel', 'newedit', 'neweditsave'
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
				'id'       => $lang['all_id'],
				'public'   => $lang['all_data'],
				'question' => $lang['all_name']
			);

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $db, $basepref, $conf, $tm, $lang, $sess, $AJAX;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, edit, del').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['faq_all'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['faq_add'].'</a>'
					.'<a'.cho('cat, catedit, catdel').' href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>'
					.'<a'.cho('catadd').' href="index.php?dn=catadd&amp;ops='.$sess['hash'].'">'.$lang['all_add_cat'].'</a>';

			if (isset($conf[PERMISS]['addit']) AND $conf[PERMISS]['addit'] == 'yes')
			{
				$count = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".PERMISS."_new"));
				$link.= '<a'.cho('new').' href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['new_faq'].'&nbsp; &#8260; &nbsp;'.$count['total'].'</a>';
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
		 ----------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $conf, $realmod;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			$subtitle = array(
				'minname' => 'all_sym_name',
				'minsymbol' => 'all_sym_message'
			);

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");
			while ($itemset = $db->fetchrow($inqset))
			{
					foreach($subtitle as $k => $v)
					{
						if ($itemset['setname'] == $k)
						{
							echo '<tr><th></th><th class="site">'.$lang[$v].'</th></tr>';
						}
					}
					echo '	<tr>
								<td class="first">'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'</td>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
							</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave">
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
		 * Настройки (сохранение)
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
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
						eval($item['setvalid']);
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
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'cat')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
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
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="catup">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Категории - сохранение позиций
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
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'catadd')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$inquiry = $db->query("SELECT catid,parentid,catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
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
							<td><input type="text" name="catname" id="catname" size="70" autofocus required /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70">';
								$tm->outtranslit('catname', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="catcustom" size="70"> <span class="light">&lt;title&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70"></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input type="text" name="keywords" size="70">';
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
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['faq_cat_mail'].'</td>
							<td><input type="email" name="catmail" size="70">';
								$tm->outhint($lang['faq_cat_mail_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all">';
			}
			echo '				<input type="hidden" name="dn" value="cataddsave">
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
		 * Добавить категорию (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'cataddsave')
		{
			global $catid, $catname, $subtitle, $catcustom, $keywords, $descript, $catmail, $cpu, $icon, $descr, $acc, $group, $sort, $ord;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
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

			if ( ! empty($catmail) AND verify_mail($catmail) == 0)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['all_add_cat'], $lang['bad_mail']);
				$tm->footer();
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$sort  = isset($catsort[$sort]) ? $sort : 'public';
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$ord   = ($ord == 'asc') ? 'asc' : 'desc';

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_cat VALUES (
					 NULL,
					 '".$catid."',
					 '".$db->escape($cpu)."',
					 '".$db->escape($catmail)."',
					 '".$db->escape(preparse_sp($catname))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape($descr)."',
					 '".$db->escape($catcustom)."',
					 '".$db->escape($keywords)."',
					 '".$db->escape($descript)."',
					 '0',
					 '".$db->escape($icon)."',
					 '".$acc."',
					 '".$db->escape($group)."',
					 '".$sort."',
					 '".$ord."',
					 '0'
					 )"
				);

			$counts = new Counts(PERMISS, 'id');
			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать категорию
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'catedit')
		{
			global $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$catid = preparse($catid,THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = array();
			while ($items = $db->fetchrow($inquiry))
			{
				$catcache[$items['parentid']][$items['catid']] = $items;
			}

			this_selectcat(0);

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
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$item['catcpu'].'">';
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
							<td><input type="text" name="descript" size="70" value="'.preparse_un($item['descript']).'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td><input type="text" name="keywords" size="70" value="'.preparse_un($item['keywords']).'">';
								$tm->outhint($lang['keyword_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="parentid" class="sw250">
									<option value="0">'.$lang['home'].'</option>
									'.$selective.'
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_sorting'].'</td>
							<td>
								<select name="sort">';
			foreach ($catsort as $k => $v) {
				echo '				<option value="'.$k.'"'.(($item['sort'] == $k) ? ' selected' : '').'>'.$v.'</option>';
			}
			echo '				</select> &nbsp;&#247;&nbsp;
								<select name="ord">
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
								<select class="group-sel" name="acc" id="acc">
									<option value="all"'.(($item['access'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['access'] == 'user' AND empty($item['groups'])) ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['access'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['access'] == 'all' OR $item['access'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					$group = Json::decode($item['groups']);
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
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('descr', 5, 50, $item['catdesc'], 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['faq_cat_mail'].'</td>
							<td><input type="text" name="catmail" size="70" value="'.$item['catmail'].'">';
								$tm->outhint($lang['faq_cat_mail_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all">';
			}
			echo '				<input type="hidden" name="dn" value="cateditsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="catid" value="'.$catid.'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать категорию (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'cateditsave')
		{
			global $catid, $parentid, $catname, $subtitle, $catcustom, $keywords, $descript, $catmail, $cpu, $icon, $descr, $acc, $group, $sort, $ord;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=cat&amp;ops='.$sess['hash'].'">'.$lang['all_cat'].'</a>',
					$lang['all_edit']
				);

			$icon = preparse($icon, THIS_TRIM);
			$catid = preparse($catid, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$catname = preparse($catname, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$parentid = preparse($parentid, THIS_INT);
			$err = this_councat($catid, $parentid, PERMISS);

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

			if ($err == 1)
			{
				$tm->header();
				$tm->error($lang['cat_edit'], $catname, $lang['move_cat_alert']);
				$tm->footer();
			}

			if ( ! empty($catmail) AND verify_mail($catmail) == 0)
			{
				$tm->header();
				$tm->error($lang['cat_edit'], $catname, $lang['bad_mail']);
				$tm->footer();
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
			$upparentid = ($catid != $parentid) ? " parentid = '".$parentid."'," : "";

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_cat SET".$upparentid."
					 catcpu    = '".$db->escape($cpu)."',
					 catmail   = '".$db->escape($catmail)."',
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
					 ord       = '".$ord."'
					 WHERE catid = '".$catid."'"
				);

			$counts = new Counts(PERMISS, 'id');
			redirect('index.php?dn=cat&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить категорию
		 -----------------------*/
		if ($_REQUEST['dn'] == 'catdel')
		{
			global $catid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
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
		 * Все вопросы-ответы (листинг)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $selective, $nu, $p, $cat, $s, $l, $ajax, $filter, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['faq_all']
				);

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;

			if (preparse($ajax,THIS_INT) == 0)
			{
				$tm->header();
				echo '<div id="ajaxbox">';
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

			$sort = array('id', 'quest', 'public', 'author');
			$limit = array('desc', 'asc');
			$s  = (in_array($s, $sort)) ? $s : 'public';
			$l  = (in_array($l, $limit)) ? $l : 'desc';
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcache = $catcaches = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcache[$item['parentid']][$item['catid']] = $item;
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}
			$catid = $cat;
			if (isset($cat) AND isset($catcaches[$cat]) OR isset($cat) AND $cat == 0 AND $cat != 'all') {
				$sql = " WHERE catid='".preparse($cat, THIS_INT)."'";
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
							'title'  => array('quest', 'faq_question', 'input'),
							'author' => array('author', 'author', 'input'),
							'public' => array('public', 'all_data', 'date'),
							'spublic' => array('spublic', 'faq_anspublic', 'date')
							);
			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0) {
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql = (($sql) ? ' AND ' : ' WHERE ').implode(' AND ',$insert);
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
							if ($f[2] == 'date' AND is_array($v)) {
								if(isset($v[0]) AND ! empty($v[0])){
									$sw[] = $f[0]." > '".$db->escape(ReDate($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." < '".$db->escape(ReDate($v[1]))."'";
								}
							}
						}
					}
					if (sizeof($sw) > 0)
					{
						$sql = (($sql) ? ' AND ' : ' WHERE ').implode(' AND ',$sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$nif = $db->insertid();
						if($nif > 0){
							$fu = '&amp;fid='.$nif;
						}
					}
				}
			}
			$link.= $fu;
			$a    = ($ajaxlink) ? '&amp;ajax=1' : '';
			$revs = $link.$a.'&amp;nu='.$nu.'&amp;s='.$s.'&amp;l='.(($l=='desc') ? 'asc' : 'desc');
			$rev  =  $link.$a.'&amp;nu='.$nu.'&amp;l=desc&amp;s=';
			$link.= $a.'&amp;s='.$s.'&amp;l='.$l;
			$inq   = $db->query("SELECT * FROM ".$basepref."_".PERMISS.$sql." ORDER BY ".$s." ".$l." LIMIT ".$sf.", ".$nu);

			$pages  = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.$sql, 'id', 'index', 'list'.$link, $nu, $p, $sess, $ajaxlink);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'].$link, $nu, $ajaxlink);
			this_selectcat(0);

			$tm->filter('index.php?dn=list&amp;ops='.$sess['hash'], $myfilter, $modname[PERMISS]);

			echo '	<script>
						var ajax = '.$ajaxlink.';
					</script>';
			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['faq_all'].'</caption>
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
									<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit" />
								</form>
							</td>
						</tr>
					</table>
					<div class="upad"></div>
					<form action="index.php" method="post">
					<table id="list" class="work">
						<tr><td colspan="7">'.$amount.'</td></tr>
						<tr>
							<th'.listsort('id').'>ID</th>
							<th'.listsort('quest').'>'.$lang['faq_question'].'&nbsp; &#8260; &nbsp;'.$lang['all_cat_one'].'</th>
							<th'.listsort('spublic').'>'.$lang['faq_anspublic'].'</th>
							<th'.listsort('public').'>'.$lang['all_data'].'</th>
							<th'.listsort('author').'>'.$lang['author'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox"></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = ($item['act'] == 'no') ? 'no-active' : '';
				$stylework = ($item['act'] == 'no') ? 'no-active' : '';

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
					echo '		<img alt="'.$lang['add_today'].'" src="'.ADMPATH.'/template/images/new.gif" class="fr">';
				}
				if ($ajaxlink == 1) {
					echo '		<div id="te'.$item['id'].'">
									<a class="notooltip" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'te'.$item['id'].'\',\'405\')" title="'.$lang['all_change'].'">
										'.preparse_un($item['quest']).'
									</a>
								</div>';
				} else {
					echo '		<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'">'.preparse_un($item['title']).'</a>';
				}
				if ($ajaxlink == 1) {
					echo '		<div class="cats" id="ce'.$item['id'].'">
									<a class="notooltip" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'ce'.$item['id'].'\',\'305\')" title="'.$lang['all_change'].'">
										'.preparse_un(linecat($item['catid'],$catcaches)).'
									</a>
								</div>';
				} else {
					echo '		<div class="cats">'.preparse_un(linecat($item['catid'],$catcaches)).'</div>';
				}
				echo '		</td>
							<td class="'.$style.'">';
				if ($ajaxlink == 1) {
					echo '		<div id="ans'.$item['id'].'">
									<a class="notooltip" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditansdate&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'ans'.$item['id'].'\',\'220\')" title="'.$lang['all_change'].'">
										'.format_time($item['spublic'], 0, 1).'
									</a>
								</div>';
				} else {
					echo		format_time($item['spublic'], 0, 1);
				}
				echo '		</td>
							<td class="'.$style.'">';
				if ($ajaxlink == 1) {
					echo '		<div id="de'.$item['id'].'">
									<a class="notooltip" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$item['id'].$link.'&amp;ops='.$sess['hash'].'\',\'de'.$item['id'].'\',\'220\')" title="'.$lang['all_change'].'">
										'.format_time($item['public'], 0, 1).'
									</a>
								</div>';
				} else {
					echo		format_time($item['public'], 0, 1);
				}
				echo '		</td>
							<td class="'.$style.' pw10">'.$author.'</td>
							<td class="'.$style.' gov">
								<a href="index.php?dn=edit&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if ($item['act'] == 'yes') {
					echo '		<a href="index.php?dn=act&amp;act=no&amp;cat='.$cat.'&amp;fid='.$fid.'&amp;id='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=act&amp;act=yes&amp;cat='.$cat.'&amp;fid='.$fid.'&amp;id='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				}
				echo '			<a href="index.php?dn=del&amp;p='.$p.'&amp;nu='.$nu.$link.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
							<td class="'.$style.' mark pw5"><input type="checkbox" name="array['.$item['id'].']" value="yes"></td>
						</tr>';
			}
			echo '		<tr>
							<td colspan="7">'.$lang['all_mark_work'].':&nbsp;
								<select name="workname">
									<option value="move">'.$lang['all_move'].'</option>
									<option value="del">'.$lang['all_delet'].'</option>
									<option value="active">'.$lang['included'].'&nbsp; &#8260; &nbsp;'.$lang['not_included'].'</option>
								</select>
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="cat" value="'.$cat.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="s" value="'.$s.'">
								<input type="hidden" name="l" value="'.$l.'">
								<input type="hidden" name="dn" value="work">
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit">
							</td>
						</tr>
						<tr><td colspan="7">'.$pages.'</td></tr>
					</table>
					</form>
					</div>';

			if (preparse($ajax, THIS_INT) == 0)
			{
				echo '</div>';
				$tm->footer();
			}
		}

		/**
		 * Массовая обработка
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'work')
		{
			global $array, $workname, $selective, $p, $cat, $nu, $s, $l, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['array_control']
				);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				$temparray = $array;
				$count = count($temparray);
				$hidden = '';
				foreach ($array as $key => $id) {
				$hidden.= '<input type="hidden" name="array['.$key.']" value="yes">';
				}
				$p   = preparse($p, THIS_INT);
				$s   = preparse($s, THIS_TRIM, 1, 7);
				$l   = preparse($l, THIS_TRIM, 1, 4);
				$nu  = preparse($nu, THIS_INT);
				$cat = preparse($cat, THIS_INT);
				$fid = preparse($fid, THIS_INT);
				$h = '	<input type="hidden" name="p" value="'.$p.'">
						<input type="hidden" name="cat" value="'.$cat.'">
						<input type="hidden" name="nu" value="'.$nu.'">
						<input type="hidden" name="s" value="'.$s.'">
						<input type="hidden" name="l" value="'.$l.'">
						<input type="hidden" name="fid" value="'.$fid.'">
						<input type="hidden" name="ops" value="'.$sess['hash'].'">';

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
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrdel">
										<input class="side-button" value="'.$lang['all_go'].'" type="submit">
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
									</td>
								</tr>
							</table>
							</form>';
					$tm->footer();

				// Перемещение
				}
				elseif ($workname == 'move')
				{
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
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrmove">
										<input class="side-button" value="'.$lang['all_go'].'" type="submit">
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
									</td>
								</tr>
							</table>
							</form>
							</div>';
					$tm->footer();

				// Активация
				}
				elseif ($workname == 'active')
				{
					$tm->header();
					echo '	<div class="section">
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
										<input type="hidden" name="dn" value="arract">
										<input class="side-button" value="'.$lang['all_go'].'" type="submit">
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
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

			allarrdel($array, 'id', PERMISS);
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
		 ------------------------------------*/
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
		 * Изменение состояния (вкл./выкл.)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'act')
		{
			global $act, $id, $p, $nu, $cat, $s, $l, $fid;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);

			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			if ($act == 'no' OR $act == 'yes')
			{
				$db->query("UPDATE ".$basepref."_".PERMISS." SET act='".$act."' WHERE id = '".$id."'");
				$counts = new Counts(PERMISS, 'id');
			}

			$cache->cachesave(1);

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
		 * Добавить вопрос-ответ
		 --------------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			global $catid, $cpu, $author, $email, $quest, $answer, $act, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$time = FLODATE.' '.date('H:i');

			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" name="total-form">
					<table class="work">
						<caption>'.$lang['faq_add'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['faq_anspublic'].'</td>
							<td><input type="text" name="spublic" id="spublic" value="'.$time.'">';
								Calendar('stcal', 'spublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0">'.$lang['cat_not'].'</option>';
				$catcache = array();
				while ($item = $db->fetchrow($inqcat))
				{
					$catcache[$item['parentid']][$item['catid']] = $item;
				}
				this_selectcat(0);
				echo				$selective.'
								</select>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['author'].'</td>
							<td><input type="text" name="author" size="42"></td>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td><input type="email" name="email" size="42"></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$cpu.'">';
								$tm->outtranslit('quest', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_question'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes') {
				define('USEWYS', 1);
				$form_short = 'quest';
				$form_more = 'answer';

				$WYSFORM = 'quest';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			} else {
				$tm->textarea('quest', 5, 70, '', 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_answer'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes') {
				$WYSFORM = 'answer';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			} else {
				$tm->textarea('answer', 7, 70, '', 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td colspan="2">';
			if ($conf['cpu'] == 'no') {
				echo '			<input type="hidden" name="cpu" value="">';
			}
			echo '				<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="addsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
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
		 * Добавить вопрос-ответ (сохранение)
		 --------------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $catid, $spublic, $public, $cpu, $author, $email, $quest, $answer, $act;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$act = ($act == 'yes') ? 'yes' : 'no';
			$catid = preparse($catid,THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$quest = preparse($quest, THIS_TRIM);
			$answer = preparse($answer, THIS_TRIM);

			$public	= (empty($public)) ? NEWTIME : ReDate($public);
			$spublic = (empty($public)) ? NEWTIME : ReDate($spublic);

			if (preparse($quest, THIS_EMPTY) == 1 OR preparse($answer, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['faq_add'], $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($quest);
				}

				$inqure = $db->query("SELECT quest, cpu FROM ".$basepref."_".PERMISS." WHERE quest = '".$db->escape($quest)."' OR cpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['faq_add'], $quest, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS." VALUES (
					 NULL,
					 '".$catid."',
					 '".$public."',
					 '".$spublic."',
					 '".$db->escape($cpu)."',
					 '".$db->escape(preparse_sp($author))."',
					 '".$db->escape($email)."',
					 '".$db->escape($quest)."',
					 '".$db->escape($answer)."',
					 '".$act."'
					 )"
				);

			$counts = new Counts(PERMISS, 'id');
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать вопрос-ответ
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $s, $l, $id, $p, $cat, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$p	= preparse($p, THIS_INT);
			$nu	= preparse($nu, THIS_INT);
			$cat	= preparse($cat, THIS_INT);
			$id	= preparse($id, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time	= CalendarFormat($item['public']);
			$anstime = CalendarFormat($item['spublic']);

			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" name="total-form">
					<table class="work">
						<caption>'.$lang['faq_edit'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['faq_anspublic'].'</td>
							<td><input type="text" name="spublic" id="spublic" value="'.$anstime.'">';
								Calendar('stcal', 'spublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>';
			if ($db->numrows($inqcat) > 0)
			{
				echo '
						<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0">'.$lang['cat_not'].'</option>';
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
			echo '		<tr>
							<td>'.$lang['author'].'</td>
							<td><input type="text" name="author" size="42" value="'.preparse_un($item['author']).'"></td>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td><input type="email" name="email" size="42" value="'.$item['email'].'"></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$item['cpu'].'">';
								$tm->outtranslit('quest', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_question'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS', 1);
				$form_short = 'quest';
				$form_more = 'answer';

				$WYSFORM = 'quest';
				$WYSVALUE = $item['quest'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('quest', 5, 70, $item['quest'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_answer'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'answer';
				$WYSVALUE = $item['answer'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('answer', 7, 70, $item['answer'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td colspan="2">';
			if ($conf['cpu'] == 'no') {
				echo '			<input type="hidden" name="cpu">';
			}
			echo '				<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="cat" value="'.$cat.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="s" value="'.$s.'">
								<input type="hidden" name="l" value="'.$l.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="editsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
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
		 * Редактировать вопрос-ответ (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $s, $l, $id, $catid, $cpu, $spublic, $public, $author, $email, $quest, $answer, $act;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_edit']
				);

			$act = ($act == 'yes') ? 'yes' : 'no';
			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$quest = preparse($quest, THIS_TRIM);
			$answer = preparse($answer, THIS_TRIM);

			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$spublic = (empty($public)) ? NEWTIME : ReDate($spublic);

			if (
				preparse($quest, THIS_EMPTY) == 1 OR
				preparse($answer, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($lang['faq_edit'], null, $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($quest);
				}

				$inqure = $db->query
							(
								"SELECT quest, cpu FROM ".$basepref."_".PERMISS."
								 WHERE (quest = '".$db->escape($quest)."' OR cpu = '".$db->escape($cpu)."')
								 AND id <> '".$id."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['faq_edit'], null, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS." SET catid = '".$catid."',
					 cpu     = '".$db->escape($cpu)."',
					 public  = '".$db->escape($public)."',
					 spublic = '".$db->escape($spublic)."',
					 author  = '".$db->escape(preparse_sp($author))."',
					 email   = '".$db->escape($email)."',
					 quest   = '".$db->escape($quest)."',
					 answer  = '".$db->escape($answer)."',
					 act     = '".$act."'
					 WHERE id = '".$id."'"
				);

			$counts = new Counts(PERMISS, 'id');

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($cat)) ? '&amp;cat='.preparse($cat, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
			$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';

			redirect($redir);
		}

		/**
		 * Удалить вопрос-ответ
		 --------------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $p, $cat, $nu, $s, $l, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");
				$counts = new Counts(PERMISS, 'id');

				$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= ( ! empty($s)) ? '&amp;s='.$s : '';
				$redir.= ( ! empty($l)) ? '&amp;l='.$l : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

				$yes = 'index.php?dn=del&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;p='.$p.'&amp;s='.$s.'&amp;l='.$l.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['faq_del'], null, $yes, $not, preparse_un($item['quest']));
				$tm->footer();
			}
		}

		/**
		 * Новые вопросы
		 --------------------------*/
		if ($_REQUEST['dn'] == 'new')
		{
			global $selective, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['added']
				);

			$tm->header();

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_new ORDER BY id DESC LIMIT ".$sf.", ".$nu);
			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_new', 'id', 'index', 'new', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=new&amp;p=".$p."&amp;ops=".$sess['hash'], $nu);

			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcaches = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}

			echo '	<div class="section">
					<form action="index.php" method="post" name="form">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['new_faq'].'</caption>';
			if ($db->numrows($inq) > 0)
			{
				echo '	<tr><td colspan="6">'.$amount.'</td></tr>
						<tr>
							<th class="work-no-sort">'.$lang['faq_question'].'</th>
							<th class="work-no-sort">'.$lang['all_cat_one'].'</th>
							<th class="work-no-sort">'.$lang['faq_anspublic'].'</th>
							<th class="work-no-sort">'.$lang['author'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox"></th>
						</tr>';
				while ($item = $db->fetchrow($inq))
				{
					echo '	<tr>
								<td class="al"><a href="index.php?dn=newedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'">'.preparse_un($item['quest']).'</a></td>
								<td>'.preparse_un(linecat($item['catid'],$catcaches)).'</td>
								<td>'.format_time($item['public'], 1, 1).'</td>
								<td>'.$item['author'].'</td>
								<td class="gov">
									<a href="index.php?dn=newedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$lang['all_add'].'" /></a>
									<a href="index.php?dn=newdel&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
								<td class="check"><input type="checkbox" name="array['.$item['id'].']" value="yes"></td>
							</tr>';
				}
				echo '	<tr><td colspan="6">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="6">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="dn" value="newarrdel">
								<input type="hidden" name="workname" value="del">
								<input id="button" class="main-button" value="'.$lang['all_delet'].'" type="submit">
							</td>
						</tr>';
			}
			else
			{
				echo '		<tr>
								<td class="ac" colspan="7">
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
		 * Удаление добавленной новости
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'newdel')
		{
			global $id, $p, $cat, $nu, $ok, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['added'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_new WHERE id = '".$id."'");

				$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($cat)) ? '&amp;cat='.$cat : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_new WHERE id = '".$id."'"));

				$yes = 'index.php?dn=newdel&amp;p='.$p.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=new&amp;p='.$p.'&amp;cat='.$cat.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['faq_del'], null, $yes, $not, preparse_un($item['quest']));
				$tm->footer();
			}
		}

		/**
		 * Массовое удаление
		 --------------------------*/
		if ($_REQUEST['dn'] == 'newarrdel')
		{
			global $array, $p, $nu, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['added'].'</a>',
					$lang['array_del']
				);

			$p	= preparse($p, THIS_INT);
			$nu	= preparse($nu, THIS_INT);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				if ($ok == 'yes')
				{
					allarrdel($array, 'id', PERMISS.'_new');
					$redir = 'index.php?dn=new&amp;ops='.$sess['hash'];
					$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
					$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
					redirect($redir);
				}
				else
				{
					$temparray = $array;
					$count = count($temparray);
					$hidden = '';
					foreach ($array as $key => $id) {
						$hidden.= '<input type="hidden" name="array['.$key.']" value="yes">';
					}
					$h = '	<input type="hidden" name="p" value="'.$p.'">
							<input type="hidden" name="nu" value="'.$nu.'">';
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$modname[PERMISS].'&nbsp; &#8260; &nbsp;'.$lang['array_del'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="newarrdel">
										<input type="hidden" name="ok" value="yes">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input class="main-button" value="'.$lang['all_go'].'" type="submit">
										<input class="main-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
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
		 * Модерировать вопрос
		 --------------------------*/
		if ($_REQUEST['dn'] == 'newedit')
		{
			global $id, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['added'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$id = preparse($id, THIS_INT);
			$p = preparse($p, THIS_INT);
			$nu = preparse($nu, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_new WHERE id = '".$id."'"));
			$anstime = CalendarFormat($item['public']);
			$time = CalendarFormat(NEWTIME);
			$cpu = cpu_translit($item['quest']);

			$inqcat = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post" name="total-form">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['faq_edit'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['faq_anspublic'].'</td>
							<td><input type="text" name="spublic" id="spublic" value="'.$anstime.'">';
								Calendar('stcal', 'spublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>';
			if ($db->numrows($inqcat) > 0)
			{
				echo '	<tr>
							<td>'.$lang['all_in_cat'].'</td>
							<td>
								<select name="catid" class="sw250">
									<option value="0">'.$lang['cat_not'].'</option>';
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
			echo '		<tr>
							<td>'.$lang['author'].'</td>
							<td><input type="text" name="author" size="42" value="'.$item['author'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td><input type="email" name="email" size="42" value="'.$item['email'].'"></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$cpu.'">';
								$tm->outtranslit('quest', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_question'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS', 1);
				$form_short = 'quest';
				$form_more = 'answer';

				$WYSFORM = 'quest';
				$WYSVALUE = $item['quest'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('quest', 5, 70, $item['quest'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['faq_answer'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'answer';
				$WYSVALUE = $item['answer'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('answer', 7, 70, $item['answer'], 1, '', '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['faq_database'].'</td>
							<td>
								<select name="post" class="sw165">
									<option value="yes">'.$lang['all_yes'].'</option>
									<option value="no">'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td colspan="2">';
			if ($conf['cpu'] == 'no') {
				echo '			<input type="hidden" name="cpu">';
			}
			echo '				<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="dn" value="neweditsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
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
		 * Модерировать вопрос (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'neweditsave')
		{
			global $id, $catid, $cpu, $post, $del, $public, $spublic, $author, $email, $quest, $answer, $act;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=new&amp;ops='.$sess['hash'].'">'.$lang['added'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$del = preparse($del, THIS_INT);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$quest = preparse($quest, THIS_TRIM);
			$answer = preparse($answer, THIS_TRIM);

			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$spublic = (empty($public)) ? NEWTIME : ReDate($spublic);

			if (
				preparse($quest, THIS_EMPTY) == 1 OR
				preparse($answer, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($lang['faq_edit'], null, $lang['pole_add_error']);
				$tm->footer();
			}
			else
			{
				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($quest);
				}

				$inqure = $db->query
							(
								"SELECT quest, cpu FROM ".$basepref."_".PERMISS."
								 WHERE (quest = '".$db->escape($quest)."' OR cpu = '".$db->escape($cpu)."')
								 AND id <> '".$id."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['faq_edit'], null, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if ($post == 'yes')
			{
				if ( ! empty($cpu) AND preparse($cpu, THIS_CPU) == 0)
				{
					$cpu = preparse($cpu, THIS_TRIM, 0, 255);
					$inq = $db->query("SELECT id FROM ".$basepref."_".PERMISS." WHERE cpu = '".$db->escape($cpu)."' AND id <> '".$id."'");
					if ($db->numrows($inq) > 0)
					{
						$tm->header();
						$tm->error($lang['cpu_error_isset']);
						$tm->footer();
					}
				}
				else
				{
					$cpu = cpu_translit($quest);
				}

				$db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS." VALUES (
						 NULL,
						 '".$catid."',
						 '".$public."',
						 '".$spublic."',
						 '".$db->escape($cpu)."',
						 '".$db->escape(preparse_sp($author))."',
						 '".$db->escape($email)."',
						 '".$db->escape(preparse_sp($quest))."',
						 '".$db->escape($answer)."',
						 '".$act."'
						 )"
					);

				$counts = new Counts(PERMISS, 'id');

				if ( ! empty($email))
				{
					$subject = $lang['faq_question_answer'];
					$message = $lang['faq_question'].":\r\n".$quest."\r\n\r\n";
					$message.= $lang['faq_answer'].":\r\n".$answer."\r\n";
					$message.= "\r\n--\r\n".$lang['site_respect'].": ".$conf['site_url'];

					send_mail($email, $subject, $message, $conf['site']." <robot.".$conf['site_mail'].">");
				}

				$db->query("DELETE FROM ".$basepref."_".PERMISS."_new WHERE id = '".$id."'");
			}
			else
			{
				$db->query
					(
						"UPDATE ".$basepref."_".PERMISS."_new SET
						 catid     = '".$catid."',
						 public    = '".$db->escape($public)."',
						 author = '".$db->escape(preparse_sp($author))."',
						 email  = '".$db->escape($email)."',
						 quest  = '".$db->escape($quest)."',
						 answer   = '".$db->escape($answer)."'
						 WHERE id = '".$id."'"
					);

				$redir = 'index.php?dn=newedit&amp;id='.$id;
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? '&amp;nu='.preparse($nu, THIS_INT) : '';
				$redir.= '&amp;ops='.$sess['hash'];

				redirect($redir);
			}

			$redir = 'index.php?dn=new';
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= '&amp;ops='.$sess['hash'];

			redirect($redir);
		}

		/**
		 * Быстрое редактирование названия вопроса
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxedittitle')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

			echo '	<form action="index.php" method="post" id="post" name="post" onsubmit="return $.posteditor(this,\'te'.$item['id'].'\',\'index.php?dn=ajaxsavetitle&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 400px;">
						<input type="text" name="title" size="60" value="'.preparse_un($item['quest']).'">&nbsp;
						<input type="hidden" name="ops" value="'.$sess['hash'].'">
						<input type="hidden" name="dn" value="ajaxsavetitle">
						<input type="hidden" name="id" value="'.$id.'">
						<input accesskey="s" class="side-button" value=" » " type="submit">
					</div>
					</form>';
		}

		/**
		 * Быстрое редактирование названия вопроса (сохранение)
		 -------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavetitle')
		{
			global $id, $title;

			$id = preparse($id, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);

			if ($id > 0 AND $title) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET quest = '".$db->escape(preparse_sp($title))."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxedittitle&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'te'.$id.'\',\'405\')">'.preparse_un($title).'</a>';

			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение категории вопроса
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
						<input type="hidden" name="ops" value="'.$sess['hash'].'">
						<input type="hidden" name="dn" value="ajaxsavecat">
						<input type="hidden" name="id" value="'.$id.'">
						<input class="side-button" value=" » " type="submit">
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение категории вопроса (сохранение)
		 ----------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavecat')
		{
			global $id, $catid;

			$id = preparse($id, THIS_INT);
			$catid = preparse($catid, THIS_INT);

			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET catid = '".$catid."' WHERE id = '".$id."'");
			}
			$inquiry = $db->query("SELECT catid, parentid, catname FROM ".$basepref."_".PERMISS."_cat ORDER BY posit ASC");
			$catcaches = array();
			while ($item = $db->fetchrow($inquiry)) {
				$catcaches[$item['catid']] = array($item['parentid'], $item['catid'], $item['catname']);
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditcat&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'ce'.$id.'\',\'305\')">'.preparse_un(linecat($catid,$catcaches)).'</a>';

			$counts = new Counts(PERMISS, 'id');
			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение даты поступления вопроса
		 ---------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditansdate')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time = CalendarFormat($item['spublic']);

			echo '	<form action="index.php" method="post" id="post" name="post" onsubmit="return $.posteditor(this,\'ans'.$item['id'].'\',\'index.php?dn=ajaxsaveansdate&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 200px;">
						<input type="text" name="spublic" id="spublic" size="16" value="'.$time.'">';
						Calendar('cal', 'spublic');
			echo '		<input type="hidden" name="ops" value="'.$sess['hash'].'">
						<input type="hidden" name="dn" value="ajaxsaveansdate">
						<input type="hidden" name="id" value="'.$id.'">
						<input class="side-button" value=" » " type="submit">
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение даты поступления вопроса (сохранение)
		 ---------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsaveansdate')
		{
			global $id, $spublic;

			$id = preparse($id, THIS_INT);
			$time = (empty($spublic)) ? NEWTIME : ReDate($spublic);

			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET spublic='".$time."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditansdate&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'ans'.$id.'\',\'220\')">'.format_time($time, 0, 1).'</a>';

			$cache->cachesave(3);
			exit();
		}

		/**
		 * Быстрое изменение даты публикации вопроса
		 ---------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxeditdate')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time = CalendarFormat($item['public']);

			echo '	<form action="index.php" method="post" id="post" name="post" onsubmit="return $.posteditor(this,\'de'.$item['id'].'\',\'index.php?dn=ajaxsavedate&id='.$item['id'].'&ops='.$sess['hash'].'\')">
					<div style="width: 200px;">
						<input type="text" name="public" id="public" size="16" value="'.$time.'">';
						Calendar('cal', 'public');
			echo '		<input type="hidden" name="ops" value="'.$sess['hash'].'">
						<input type="hidden" name="dn" value="ajaxsavedate">
						<input type="hidden" name="id" value="'.$id.'">
						<input class="side-button" value=" » " type="submit">
					</div>
					</form>';
		}

		/**
		 * Быстрое изменение даты публикации вопроса (сохранение)
		 ---------------------------------------------------------*/
		if ($_REQUEST['dn'] == 'ajaxsavedate')
		{
			global $id, $public;

			$id = preparse($id, THIS_INT);
			$time = (empty($public)) ? NEWTIME : ReDate($public);

			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET public='".$time."' WHERE id = '".$id."'");
			}
			echo '<a class="notooltip" title="'.$lang['all_change'].'" href="javascript:$.ajaxeditor(\'index.php?dn=ajaxeditdate&amp;id='.$id.'&amp;ops='.$sess['hash'].'\',\'de'.$id.'\',\'220\')">'.format_time($time, 0, 1).'</a>';

			$cache->cachesave(3);
			exit();
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
