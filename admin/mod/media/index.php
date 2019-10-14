<?php
/**
 * File:        /admin/mod/media/index.php
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
define('PERMISS', 'media');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $wysiwyg, $modname, $lang, $sess, $ops, $cache;

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
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID,$CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'optsave', 'list', 'uplist', 'work', 'arrdel', 'arrmove', 'arract',
				'listadd', 'listsave', 'listdel', 'edit', 'editsave',
				'imgadd', 'imgsave', 'imgedit', 'imgeditsave', 'upmass', 'massupsave', 'imgact', 'imgdel',
				'media', 'thumb', 'video'
			);

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, edit').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['media'].'</a>'
					.'<a'.cho('listadd').' href="index.php?dn=listadd&amp;ops='.$sess['hash'].'">'.$lang['add_media'].'</a>'
					.'<a'.cho('imgadd').' href="index.php?dn=imgadd&amp;ops='.$sess['hash'].'">'.$lang['add_photo_video'].'</a>'
					.'<a'.cho('upmass').' href="index.php?dn=upmass&amp;ops='.$sess['hash'].'">'.$lang['add_mass'].'</a>';

			$tm->this_menu($link);
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
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");
			while ($itemset = $db->fetchrow($inqset))
			{
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
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($modname[PERMISS], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Медиа-презентации
		 -----------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_media']
				);

			$tm->header();

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query
					(
						"SELECT a.*, COUNT(b.id) AS total FROM ".$basepref."_".PERMISS."_cat AS a
						 LEFT JOIN ".$basepref."_".PERMISS." AS b ON (a.catid = b.catid) GROUP BY a.catid
						 ORDER BY a.posit ASC LIMIT ".$sf.", ".$nu
					);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.'_cat', 'catid', 'index', 'list&amp;nu='.$nu, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'], $nu);

			// Группы в массив
			$groups_only = array();
			if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
			{
				$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
				while ($items = $db->fetchrow($inqs))
				{
					$groups_only[] =  $items['title'];
				}
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$modname[PERMISS].'</caption>
						<tr><td colspan="8">'.$amount.'</td></tr>
						<tr>
							<th>ID</th>
							<th>'.$lang['all_name'].'</th>
							<th>'.$lang['all_icon'].'</th>
							<th>'.$lang['who_col_all'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_access'].'</th>
							<th>'.$lang['all_photo_video'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = ($item['act'] == 'no') ? 'noactive' : '';

				// Ассоциируем группы
				$groupact = null;
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

				echo '	<tr class="list">
							<td class="ac">'.$item['catid'].'</td>
							<td class="'.$style.' al site pw25">'.$item['listname'].'</td>
							<td class="'.$style.'">';
				if($item['icon'] != '') {
					echo '		<img src="index.php?dn=thumb&amp;type=list&amp;id='.$item['catid'].'&amp;x=36&amp;h=27&amp;r=yes&amp;ops='.$sess['hash'].'" alt="'.preparse_un($item['listname']).'" />';
				} else {
					echo '		&#8212;';
				}
				echo '		</td>
							<td class="'.$style.'"><input type="text" name="listcol['.$item['catid'].']" value="'.$item['listcol'].'"size="3" maxlength="1"></td>
							<td class="'.$style.'"><input type="text" name="posit['.$item['catid'].']" value="'.$item['posit'].'"size="3" maxlength="3"></td>
							<td class="'.$style.'">';
				echo '			'.(($item['access'] == 'user') ? ( ! empty($item['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']);
				echo '		</td>
							<td class="'.$style.' vm com">';
				if ($item['total'] > 0) {
					echo		'<a href="index.php?dn=media&amp;list='.$item['catid'].'&amp;ops='.$sess['hash'].'""><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/photo.png" alt="'.$lang['present_content'].'" /></a>
								&nbsp; ('.$item['total'].')';
				} else {
					echo '		0 ';
				}
				echo '		</td>
							<td class="'.$style.' gov">
								<a href="index.php?dn=edit&amp;catid='.$item['catid'].'&amp;p='.$p.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=imgadd&amp;catid='.$item['catid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png" alt="'.$lang['add_img'].'" /></a>
								<a href="index.php?dn=listdel&amp;list='.$item['catid'].'&amp;p='.$p.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="8">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="uplist">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Сохранение позиций
		 ------------------------*/
		if ($_REQUEST['dn'] == 'uplist')
		{
			global $listcol, $posit;

			foreach ($listcol as $id => $val)
			{
				$col = (intval($val) == 0) ? 1 : intval($val);
				$db->query("UPDATE ".$basepref."_".PERMISS."_cat SET listcol='".$col."' WHERE catid = '".intval($id)."'");
			}

			foreach ($posit as $id => $val)
			{
				$db->query("UPDATE ".$basepref."_".PERMISS."_cat SET posit='".intval($val)."' WHERE catid = '".intval($id)."'");
			}

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить презентацию
		---------------------------*/
		if ($_REQUEST['dn'] == 'listadd')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$time = (empty($public)) ? FLODATE.' '.date('H').':'.date('i') : $public;

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_media'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="listname" id="listname" size="70" required="required"> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70"> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="catcpu" id="catcpu" size="70">';
								$tm->outtranslit('listname', 'catcpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70"> <span class="light">&lt;title&gt;</span></td>
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
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '		</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic">';
								Calendar('stcal', 'stpublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_unpublic'].'</td>
							<td><input type="text" name="unpublic" id="unpublic">';
								Calendar('uncal', 'unpublic');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS', 1);

				$form_short = 'listdesc';
				$form_more = 'listtext';

				$WYSFORM = 'listdesc';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('listdesc', 5, 70, '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'listtext';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('listtext', 10, 70, '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input type="text" name="icon" id="icon" size="47">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['who_col_all'].'</td>
							<td><input type="text" name="listcol" size="25" value="2" maxlength="1"></td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
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
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_important'].'</td>
							<td>
								<select name="imp" class="sw165">
									<option value="0">'.$lang['all_no'].'</option>
									<option value="1">'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all">';
			}
			echo '				<input type="hidden" name="dn" value="listsave">
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
		 * Добавить презентацию (сохранение)
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'listsave')
		{
			global	$public, $stpublic, $unpublic, $listname, $subtitle, $listdesc, $listdesc, $listcol, $catcpu, $customs, $descript, $keywords,
					$listtext, $icon, $acc, $group, $act, $hits, $imp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$listname = preparse($listname, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$catcpu = preparse($catcpu, THIS_TRIM, 0, 255);
			$listcol = preparse($listcol, THIS_INT);
			$icon = preparse($icon, THIS_TRIM);

			if (preparse($listname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['add_media'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($catcpu, THIS_EMPTY) == 1)
				{
					$catcpu = cpu_translit($listname);
				}

				$inqure = $db->query
							(
								"SELECT listname, catcpu FROM ".$basepref."_".PERMISS."_cat
								 WHERE listname = '".$db->escape($listname)."' OR
								 catcpu = '".$db->escape($catcpu)."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['add_media'], $listname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if ($listcol == 0)
			{
				$listcol = 1;
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$time = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$hits = ($hits) ? preparse($hits,THIS_INT) : 0;
			$imp = ($imp == 1) ? 1 : 0;

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS."_cat VALUES (
					 NULL,
					 '".$time."',
					 '".$stpublic."',
					 '".$unpublic."',
					 '".$db->escape($listname)."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape($listdesc)."',
					 '".$listcol."',
					 '".$acc."',
					 '".$catcpu."',
					 '".$db->escape(preparse_sp($customs))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape($listtext)."',
					 '0',
					 '".$db->escape($icon)."',
					 '".$db->escape($group)."',
					 '".$act."',
					 '".$hits."',
					 '".$db->escape($imp)."'
					 )"
				);

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить презентацию
		 ------------------------*/
		if ($_REQUEST['dn'] == 'listdel')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_delet']
				);

			global $list, $ok;

			$list = preparse($list, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE catid = '".$list."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$list."'");
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$list."'"));

				$yes = 'index.php?dn=listdel&amp;list='.$list.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['listname']), $yes, $not, $lang['del_cat_alert']);
				$tm->footer();
			}
		}

		/**
		 * Редактировать презентацию
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $catid, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['edit_media']
				);

			$tm->header();

			$catid = preparse($catid, THIS_INT);
			$p = preparse($p, THIS_INT);

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
			$time = CalendarFormat($item['public']);

			$stpublic = ($item['stpublic'] == 0) ? '' : CalendarFormat($item['stpublic']);
			$unpublic = ($item['unpublic'] == 0) ? '' : CalendarFormat($item['unpublic']);

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.$item['listname'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="listname" id="listname" size="70" value="'.$item['listname'].'" required="required"> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'"> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="catcpu" id="catcpu" size="70" value="'.$item['catcpu'].'">';
								$tm->outtranslit('listname', 'catcpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" value="'.preparse_un($item['customs']).'"> <span class="light">&lt;title&gt;</span></td>
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
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_stpublic'].'</td>
							<td><input type="text" name="stpublic" id="stpublic" value="'.$stpublic.'">';
								Calendar('stcal','stpublic');
            echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_unpublic'].'</td>
							<td><input type="text" name="unpublic" id="unpublic" value="'.$unpublic.'">';
								Calendar('uncal','unpublic');
            echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);

				$form_short = 'listdesc';
				$form_more = 'listtext';

				$WYSFORM = 'listdesc';
				$WYSVALUE = $item['listdesc'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('listdesc', 5, 70, $item['listdesc'], (($wysiwyg == 'yes') ? 0 : 1));
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['full_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				$WYSFORM = 'listtext';
				$WYSVALUE = $item['listtext'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('listtext', 10, 70, $item['listtext'], (($wysiwyg == 'yes') ? 0 : 1));
			}
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_hits'].'</td>
							<td><input type="text" name="hits" size="25" value="'.$item['hits'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="47" type="text" value="'.$item['icon'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/icon/\',\'&amp;field[1]=icon\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['who_col_all'].'</td>
							<td><input type="text" name="listcol" size="25" value="'.$item['listcol'].'" maxlength="1"></td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
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
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_important'].'</td>
							<td>
								<select name="imp" class="sw165">
									<option value="0"'.(($item['imp'] == 0) ? ' selected' : '').'>'.$lang['all_no'].'</option>
									<option value="1"'.(($item['imp'] == 1) ? ' selected' : '').'>'.$lang['all_yes'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all">';
			}
			echo '				<input type="hidden" name="dn" value="editsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="catid" value="'.$catid.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать презентацию (сохранение)
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global	$p, $catid, $public, $stpublic, $unpublic, $listname, $subtitle, $listdesc, $listcol, $listdesc, $catcpu, $customs, $descript, $keywords,
					$listtext, $icon, $acc, $group, $act, $hits, $imp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['edit_media']
				);

			$listname = preparse($listname, THIS_TRIM, 0, 255);
			$subtitle  = preparse($subtitle, THIS_TRIM, 0, 255);
			$catcpu = preparse($catcpu, THIS_TRIM, 0, 255);
			$listcol = preparse($listcol, THIS_INT);
			$catid = preparse($catid, THIS_INT);
			$icon = preparse($icon, THIS_TRIM);
			$p = preparse($p, THIS_INT);

			if (preparse($listname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_media'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if (preparse($catcpu, THIS_EMPTY) == 1)
				{
					$catcpu = cpu_translit($listname);
				}

				$inqure = $db->query
							(
								"SELECT listname, catcpu FROM ".$basepref."_".PERMISS."_cat
								 WHERE (listname = '".$db->escape($listname)."' OR catcpu = '".$db->escape($catcpu)."')
								 AND catid <> '".$catid."'
								"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['edit_media'], $listname, $lang['cpu_error_isset']);
					$tm->footer();
				}
			}

			if ($listcol == 0) {
				$listcol = 1;
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($group)
			)
			{
				$group = Json::encode($group);
			}

			$hits = ($hits) ? preparse($hits, THIS_INT) : 0;
			$time = (empty($public)) ? NEWTIME : ReDate($public);
			$stpublic = (ReDate($stpublic) > 0) ? ReDate($stpublic) : 0;
			$unpublic = (ReDate($unpublic) > 0) ? ReDate($unpublic) : 0;
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$imp = ($imp == 1) ? 1 : 0;

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS."_cat SET
					 public   = '".$time."',
					 stpublic = '".$stpublic."',
					 unpublic = '".$unpublic."',
					 listname = '".$db->escape(preparse_sp($listname))."',
					 subtitle = '".$db->escape(preparse_sp($subtitle))."',
					 listdesc = '".$db->escape($listdesc)."',
					 listcol  = '".$listcol."',
					 access   = '".$acc."',
					 catcpu   = '".$catcpu."',
					 customs  = '".$db->escape(preparse_sp($customs))."',
					 descript = '".$db->escape(preparse_sp($descript))."',
					 keywords = '".$db->escape(preparse_sp($keywords))."',
					 listtext = '".$db->escape($listtext)."',
					 icon     = '".$db->escape($icon)."',
					 groups   = '".$db->escape($group)."',
					 act      = '".$act."',
					 hits     = '".$hits."',
					 imp      = '".$db->escape($imp)."'
					 WHERE catid = '".$catid."'"
				);

			redirect('index.php?dn=list&amp;ops='.$sess['hash'].'&amp;p='.$p);
		}

		/**
		 * Изображения презентации (листинг)
		 --------------------------------------*/
		if ($_REQUEST['dn'] == 'media')
		{
			global $nu, $p, $list;

			$nu = (isset($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$SQL  = ( ! isset($list)) ? '' : " WHERE catid = '".preparse($list,THIS_INT)."'";
			$LINK = ( ! isset($list)) ? '' : "&amp;list=".$list."";

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS.$SQL." ORDER BY posit ASC LIMIT ".$sf.", ".$nu);
			$items = $db->fetchrow($db->query("SELECT listname FROM ".$basepref."_".PERMISS."_cat WHERE catid='".$list."'"));

			$pages  = $lang['all_pages'].':&nbsp; '.adm_pages(PERMISS.$SQL, 'id', 'index', PERMISS.'&amp;nu='.$nu.$LINK, $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=media&amp;p=".$p."&amp;ops=".$sess['hash'].$LINK, $nu);

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$items['listname']
				);

			$tm->header();

			echo '	<script>
					$(document).ready(function()
					{
						$(".media-view").colorbox({
							initialWidth  : 450,
							initialHeight : 338,
							maxHeight     : 600,
							maxWidth      : 800,
							onLoad: function() {
								$("#cboxClose").hide();
							},
							onComplete: function () {
								$("#cboxClose").hide();
							}
						});
					});
					</script>';
			echo '	<div class="section">
					<form action="index.php" method="post" name="form" id="form">
					<table id="list" class="work">
						<caption>'.$items['listname'].': '.$lang['photo_video'].'</caption>
						<tr><td colspan="6">'.$amount.'</td></tr>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_data'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_thumb'].'&nbsp; &#8260; &nbsp;'.$lang['down_type'].'</th>
							<th>'.$lang['sys_manage'].'</th>
							<th class="ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox"></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = ($item['act'] == 'no') ? 'no-active' : '';
				echo '	<tr class="list">
							<td class="'.$style.' al site">'.$item['title'].'</td>
							<td class="'.$style.'">'.format_time($item['public'], 0, 1).'</td>
							<td class="'.$style.' pw15"><input type="text" name="posit['.$item['id'].']" size="3" value="'.$item['posit'].'" maxlength="3"></td>
							<td class="'.$style.' pw15">';
				if ( ! empty($item['image'])) {
					echo '		<a class="media-view" href="'.$conf['site_url'].'/'.$item['image'].'"><img src="index.php?dn=thumb&amp;type=media&amp;id='.$item['id'].'&amp;x=36&amp;h=27&amp;r=yes&amp;ops='.$sess['hash'].'" alt="'.$lang['file_view'].'" /></a>'
								.'&nbsp;&nbsp;<img src="'.ADMPATH.'/template/library/image.gif" alt="'.$lang['all_image'].'" />';
				} elseif ( ! empty($item['video'])) {
					echo '		<a class="media-view" href="index.php?dn=video&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="index.php?dn=thumb&amp;type=media&amp;id='.$item['id'].'&amp;x=36&amp;h=27&amp;r=yes&amp;ops='.$sess['hash'].'" alt="'.$lang['file_view'].'" /></a>'
								.'&nbsp;&nbsp;<img src="'.ADMPATH.'/template/library/move.gif" alt="'.$lang['all_video'].'" />';
				} else {
					echo '		<img src="index.php?dn=thumb&amp;type=media&amp;id='.$item['id'].'&amp;x=36&amp;h=27&amp;r=yes&amp;ops='.$sess['hash'].'">';
				}
				echo '		</td>
							<td class="'.$style.' gov pw15">
								<a href="index.php?dn=imgedit&amp;id='.$item['id'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
				if ($item['act'] == 'yes') {
					echo '		<a href="index.php?dn=imgact&amp;act=no&amp;id='.$item['id'].'&amp;list='.$list.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				} else {
					echo '		<a class="inact" href="index.php?dn=imgact&amp;act=yes&amp;id='.$item['id'].'&amp;list='.$list.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
				}
					echo '		<a href="index.php?dn=imgdel&amp;id='.$item['id'].'&amp;list='.$list.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
							<td class="'.$style.' mark pw5"><input type="checkbox" name="array['.$item['id'].']" value="yes"></td>
						</tr>';
			}
			echo '		<tr>
							<td colspan="6">'.$lang['all_mark_work'].':&nbsp;
								<select name="workname">
									<option value="pos" selected>'.$lang['save_posit'].'</option>
									<option value="move">'.$lang['all_move'].'</option>
									<option value="active">'.$lang['included'].'&nbsp; &#8260; &nbsp;'.$lang['not_included'].'</option>
									<option value="del">'.$lang['all_delet'].'</option>
								</select>
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="list" value="'.$list.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="dn" value="work">
								<input id="button" class="side-button" value="'.$lang['all_go'].'" type="submit">
							</td>
						</tr>
						<tr><td colspan="6">'.$pages.'</td></tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Массовая обработка
		 -----------------------*/
		if ($_REQUEST['dn'] == 'work')
		{
			global $array, $workname, $posit, $p, $list, $nu;

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
				$p = preparse($p, THIS_INT);
				$list = preparse($list, THIS_INT);
				$nu = preparse($nu, THIS_INT);
				$h = '<input type="hidden" name="p" value="'.$p.'">'
					.'<input type="hidden" name="list" value="'.$list.'">'
					.'<input type="hidden" name="nu" value="'.$nu.'">'
					.'<input type="hidden" name="ops" value="'.$sess['hash'].'">';

				// Удаление
				if ($workname == 'del')
				{
					$tm->header();
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_delet'].' ('.$count.')</caption>
								<tr><td class="cont">'.$lang['alertdel'].'</td></tr>
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
							</form>
							</div>';
					$tm->footer();

				// Перемещение
				}
				elseif ($workname == 'move')
				{
					$tm->header();
					$inquiry = $db->query("SELECT catid,listname FROM ".$basepref."_".PERMISS."_cat");
					echo '	<div class="section">
							<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_move'].' ('.$count.')</caption>
								<tr>
									<td class="cont">
										<select name="catid">';
					while ($items = $db->fetchrow($inquiry)) {
						echo '				<option value="'.$items['catid'].'"'.(($items['catid'] == $list) ? ' selected' : '').'>'.$items['listname'].'</option>';
					}
					echo '				</select>
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

				// Позиции
				}
				elseif ($workname == 'pos')
				{
					if (preparse($posit, THIS_ARRAY) == 1) {
						foreach ($posit as $id => $val)
						{
							$db->query("UPDATE ".$basepref."_".PERMISS." SET posit = '".intval($val)."' WHERE id = '".intval($id)."'");
						}
					}
				}
			}

			$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($list)) ? '&amp;list='.preparse($list, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			redirect($redir);
		}

		/**
		 * Массовое удаление (сохранение)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'arrdel')
		{
			global $array, $p, $list, $nu;

			allarrdel($array, 'id', PERMISS, 1, 1);

			$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($list)) ? '&amp;list='.preparse($list, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Массовое перемещение (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'arrmove')
		{
			global $array, $p, $list, $catid, $nu;

			$list = preparse($list, THIS_INT);
			$catid = preparse($catid, THIS_INT);

			if (preparse($array, THIS_ARRAY) == 1 AND $catid > 0)
			{
				$list = $catid;
				foreach ($array as $id => $v)
				{
					$db->query("UPDATE ".$basepref."_".PERMISS." SET catid = '".intval($catid)."' WHERE id = '".intval($id)."'");
				}
			}

			$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($list)) ? '&amp;list='.preparse($list, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Массовая активация (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'arract')
		{
			global $array, $p, $list, $act, $nu;

			$list = preparse($list, THIS_INT);

			if (preparse($array, THIS_ARRAY) == 1)
			{
				foreach ($array as $id => $v)
				{
					$id = preparse($id, THIS_INT);
					$db->query("UPDATE ".$basepref."_".PERMISS." SET act = '".$act."' WHERE id = '".intval($id)."'");
				}
			}

			$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($list)) ? '&amp;list='.preparse($list, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Добавить изображение
		 --------------------------*/
		if ($_REQUEST['dn'] == 'imgadd')
		{
			global $catid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$time = CalendarFormat(NEWTIME);
			$inq = $db->query("SELECT catid, listname FROM ".$basepref."_".PERMISS."_cat");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['add_photo_video'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input id="title" type="text" name="title" size="70" required="required" /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70"> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" /> <span class="light">&lt;title&gt;</span></td>
						</tr>
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
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['media'].'</td>
							<td>
								<select name="list" style="width: 390px;" required="required">
									<option value="">'.$lang['select_media'].'</option>';
			while ($item = $db->fetchrow($inq)) {
				echo '				<option value="'.$item['catid'].'"'.((isset($catid) AND $catid == $item['catid']) ? ' selected' : '').'>'.$item['listname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['descript'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);
				$form_more = 'text';
				$WYSFORM = 'text';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('text', 5, 70, '', 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image_thumb&amp;field[2]=image_video\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_image'].'&nbsp; &#8260; &nbsp;'.$lang['all_video'].'</td>
							<td>
								<input name="image_video" id="image_video" size="70" type="text" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image_video&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" size="70" type="text"></td>
						</tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="imgsave">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить изображение (сохранение)
		 -------------------------------------*/
		if ($_REQUEST['dn'] == 'imgsave')
		{
			global $list, $title, $subtitle, $cpu, $customs, $descript, $keywords, $text, $image_thumb, $image_video, $image_alt, $act;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($image_thumb, THIS_EMPTY) == 1 OR
				preparse($image_video, THIS_EMPTY) == 1 OR
				$list == 0 OR $list == ''
			) {
				$tm->header();
				$tm->error($modname[PERMISS], $lang['add_photo_video'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				// Type media
				$validimage = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
				$validvideo = array('flv', 'mp4', 'webm', 'ogv');

				// Valid image_thumb
				$extthumb = strtolower(pathinfo($image_thumb, PATHINFO_EXTENSION));
				$validthumb = (in_array($extthumb, $validimage)) ? 1 : 0;

				// Valid image_video
				$extmedia = strtolower(pathinfo($image_video, PATHINFO_EXTENSION));
				$validmedia = (in_array($extmedia, array_merge ($validimage, $validvideo))) ? 1 : 0;

				// Check valid media
				if ($validthumb == 0 OR $validmedia == 0)
				{
					$tm->header();
					$tm->error($modname[PERMISS], $lang['add_photo_video'], $lang['error_type']);
					$tm->footer();
				}

				// Image
				$image = (in_array($extmedia, $validimage)) ? preparse($image_video, THIS_TRIM, 0, 255) : '';

				// Video
				$video = (in_array($extmedia, $validvideo)) ? preparse($image_video, THIS_TRIM, 0, 255) : '';

				$act = ($act == 'yes') ? 'yes' : 'no';
				$list = preparse($list, THIS_INT);
				$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
				$image_thumb = preparse($image_thumb, THIS_TRIM, 0, 255);
				$title = preparse($title, THIS_TRIM, 0, 255);
				$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
				$cpu = preparse($cpu, THIS_TRIM, 0, 255);
				$text = preparse($text, THIS_TRIM);
				$customs = preparse($customs, THIS_TRIM);
				$descript = preparse($descript, THIS_TRIM);
				$keywords = preparse($keywords, THIS_TRIM);

				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query("SELECT title, cpu FROM ".$basepref."_".PERMISS." WHERE title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."'");
				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($lang['add_photo_video'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}

				$db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS." VALUES (
						 NULL,
						 '".$list."',
						 '".NEWTIME."',
						 '".$cpu."',
						 '".$db->escape(preparse_sp($title))."',
						 '".$db->escape(preparse_sp($subtitle))."',
						 '".$db->escape(preparse_sp($customs))."',
						 '".$db->escape(preparse_sp($keywords))."',
						 '".$db->escape(preparse_sp($descript))."',
						 '".$db->escape($text)."',
						 '".$db->escape($image)."',
						 '".$db->escape($image_thumb)."',
						 '".$db->escape(preparse_sp($image_alt))."',
						 '0',
						 '".$db->escape($video)."',
						 '".$act."'
						 )"
					);

				redirect('index.php?dn=media&amp;list='.$list.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Редактировать изображение
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'imgedit')
		{
			global $id, $p, $nu, $list;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$time = CalendarFormat($item['public']);

			$sellist = $namelist = '';
			$inq = $db->query("SELECT catid, listname FROM ".$basepref."_".PERMISS."_cat");
			while ($items = $db->fetchrow($inq))
			{
				$sellist.= '<option value="'.$items['catid'].'"'.(($item['catid'] == $items['catid']) ? ' selected' : '').'>'.$items['listname'].'</option>';
				if ($item['catid'] == $items['catid']) {
					$namelist = $items['listname'];
				}
			}

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=media&list='.$item['catid'].'&amp;ops='.$sess['hash'].'">'.$namelist.'</a>',
					$lang['edit_photo_video']
				);

			$tm->header();

			$media = ((empty($item['image']) AND empty($item['video'])) ? '' : ( ! empty($item['image']) ? $item['image'] : $item['video']));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$namelist.'&nbsp; &#8260; &nbsp;'.$lang['all_edit'].': '.preparse_un($item['title']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input id="title" type="text" name="title" size="70" value="'.preparse_un($item['title']).'" required="required"> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'"> <span class="light">&lt;h2&gt;</span></td>
						</tr>';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$item['cpu'].'">';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" value="'.preparse_un($item['customs']).'"> <span class="light">&lt;title&gt;</span></td>
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
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$time.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['media'].'</td>
							<td>
								<select name="list" style="width:390px;">
									'.$sellist.'
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['descript'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);
				$form_more = 'text';
				$WYSFORM = 'text';
				$WYSVALUE = $item['text'];
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('text', 5, 70, $item['text'], 1);
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" value="'.$item['image_thumb'].'" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image_thumb&amp;field[2]=image_video\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_image'].'&nbsp; &#8260; &nbsp;'.$lang['all_video'].'</td>
							<td>
								<input name="image_video" id="image_video" size="70" type="text" value="'.$media.'" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/'.PERMISS.'/\',\'&amp;field[1]=image_video&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" size="70" type="text" value="'.$item['image_alt'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="dn" value="imgeditsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать изображение (сохранение)
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'imgeditsave')
		{
			global $list, $id, $title, $subtitle, $public, $cpu, $customs, $descript, $keywords, $text, $image_thumb, $image_video, $image_alt, $p, $nu, $act;

			$item = $db->fetchrow($db->query("SELECT listname FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$list."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=media&list='.$list.'&amp;ops='.$sess['hash'].'">'.$item['listname'].'</a>',
					$lang['edit_photo_video']
				);

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($image_thumb, THIS_EMPTY) == 1 OR
				preparse($image_video, THIS_EMPTY) == 1 OR
				$list == 0 OR $id == 0
			) {
				$tm->header();
				$tm->error($item['listname'], $lang['edit_photo_video'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				// Type media
				$validimage = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
				$validvideo = array('flv', 'mp4', 'webm', 'ogv');

				// Valid image_thumb
				$extthumb = strtolower(pathinfo($image_thumb, PATHINFO_EXTENSION));
				$validthumb = (in_array($extthumb, $validimage)) ? 1 : 0;

				// Valid image_video
				$extmedia = strtolower(pathinfo($image_video, PATHINFO_EXTENSION));
				$validmedia = (in_array($extmedia, array_merge ($validimage, $validvideo))) ? 1 : 0;

				// Check valid media
				if ($validthumb == 0 OR $validmedia == 0)
				{
					$tm->header();
					$tm->error($item['listname'], $lang['edit_photo_video'], $lang['error_type']);
					$tm->footer();
				}

				// Image
				$image = (in_array($extmedia, $validimage)) ? preparse($image_video, THIS_TRIM, 0, 255) : '';

				// Video
				$video = (in_array($extmedia, $validvideo)) ? preparse($image_video, THIS_TRIM, 0, 255) : '';

				$act = ($act == 'yes') ? 'yes' : 'no';
				$list = preparse($list, THIS_INT);
				$id = preparse($id, THIS_INT);
				$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);
				$image_thumb = preparse($image_thumb, THIS_TRIM, 0, 255);
				$title = preparse($title, THIS_TRIM, 0, 255);
				$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
				$cpu = preparse($cpu, THIS_TRIM, 0, 255);
				$text = preparse($text, THIS_TRIM);
				$customs = preparse($customs, THIS_TRIM);
				$descript = preparse($descript, THIS_TRIM);
				$keywords = preparse($keywords, THIS_TRIM);
				$public = (empty($public)) ? NEWTIME : ReDate($public);

				if (preparse($cpu, THIS_EMPTY) == 1)
				{
					$cpu = cpu_translit($title);
				}

				$inqure = $db->query
							(
								"SELECT title, cpu FROM ".$basepref."_".PERMISS."
								 WHERE (title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."')
								 AND id <> '".$id."'
								"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($item['listname'].'&nbsp; &#8260; &nbsp;'.$lang['all_edit'], $title, $lang['cpu_error_isset']);
					$tm->footer();
				}

				$db->query
					(
						"UPDATE ".$basepref."_".PERMISS." SET
						 catid       = '".$list."',
						 public      = '".$public."',
						 cpu         = '".$cpu."',
						 title       = '".$db->escape(preparse_sp($title))."',
						 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
						 customs     = '".$db->escape(preparse_sp($customs))."',
						 descript    = '".$db->escape(preparse_sp($descript))."',
						 keywords    = '".$db->escape(preparse_sp($keywords))."',
						 text        = '".$db->escape($text)."',
						 image       = '".$db->escape($image)."',
						 image_thumb = '".$db->escape($image_thumb)."',
						 image_alt   = '".$db->escape(preparse_sp($image_alt))."',
						 video       = '".$db->escape($video)."',
						 act         = '".$act."'
						 WHERE id = '".$id."'"
					);

				$redir = 'index.php?dn=media&amp;list='.$list;
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= '&amp;ops='.$sess['hash'];

				redirect($redir);
			}
		}

		/**
		 * Изменение состояния (вкл./выкл.)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'imgact')
		{
			global $act, $id, $list, $p, $nu;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($id, THIS_INT);
			$list = preparse($list, THIS_INT);

			if ($act == 'no' OR $act == 'yes') {
				$db->query("UPDATE ".$basepref."_".PERMISS." SET act='".$act."' WHERE id = '".$id."'");
			}

			$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($list)) ? '&amp;list='.$list : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

			redirect($redir);
		}

		/**
		 * Удалить изображение
		 --------------------------*/
		if ($_REQUEST['dn'] == 'imgdel')
		{
			global $ok, $id, $list, $p, $nu;

			$items = $db->fetchrow($db->query("SELECT listname FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$list."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=media&list='.$list.'&amp;ops='.$sess['hash'].'">'.$items['listname'].'</a>',
					$lang['del_media']
				);

			$id = preparse($id, THIS_INT);
			$list = preparse($list, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");

				@unlink(DNDIR.$item['image']);
				@unlink(DNDIR.$item['image_thumb']);

				$redir = 'index.php?dn=media&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($list)) ? '&amp;list='.$list : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

				$yes = 'index.php?dn=imgdel&amp;p='.$p.'&amp;list='.$list.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=media&amp;p='.$p.'&amp;list='.$list.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['title']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Массовое добавление изображений
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'upmass')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['array_control']
				);

			$tm->header();

			$inq = $db->query("SELECT catid, listname FROM ".$basepref."_".PERMISS."_cat");

			$width = $height = '';
			for ($i = 70; $i <= 300; $i ++)
			{
				$width.= '<option value="'.$i.'"'.(($i == $conf['width']) ? ' selected' : '').'>'.$i.' px</option>';
				$height.= '<option value="'.$i.'"'.(($i == $conf['height']) ? ' selected' : '').'>'.$i.' px</option>';
				$i = $i + 4;
			}

			echo "<script src=\"".ADMPATH."/js/jquery.massupload.js\"></script>
					<script>
						var size          = '".$width."';
						var all_image     = '".$lang['all_image']."';
						var all_file      = '".$lang['all_file']."';
						var miniature_yes = '".$lang['miniature_yes']."';
						var all_yes       = '".$lang['all_yes']."';
						var all_no        = '".$lang['all_no']."';
						var all_width     = '".$lang['all_width']."';
						var all_height    = '".$lang['all_height']."';
						var all_resize    = '".$lang['all_resize']."';
						var all_name      = '".$lang['all_name']."';
						var all_decs      = '".$lang['all_decs']."';
						var image_alt     = '".$lang['all_alt_image']."';
						var all_delet     = '".$lang['all_delet']."';
					</script>";
			echo '	<form enctype="multipart/form-data" action="index.php" method="post" id="total-form">
					<div class="section">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['add_mass'].'</caption>
						<tr>
							<th></th>
							<th class="site bold">'.$lang['all_set'].'</th></tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['media'].'</td>
							<td>
								<select name="catid" class="sw250" required="required">
									<option value="">'.$lang['select_media'].'</option>';
			while ($item = $db->fetchrow($inq)) {
				echo '				<option value="'.$item['catid'].'">'.$item['listname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
							<td>
								<select class="group-sel sw250" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if ($conf['user']['groupact'] == 'yes')
				{
					$group_out = '';
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
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
							<td>'.$lang['all_format'].'</td>
							<td>
								<select class="sw250" name="injpg">
									<option value="yes">'.$lang['convert_in_jpg'].'</option>
									<option value="no">'.$lang['save_scr_type'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['watermark'].'</td>
							<td><input type="checkbox" name="wmark"></td>
						</tr>';
			echo '		<tr>
							<th></th>
							<th class="site bold">'.$lang['all_image_thumb'].'</th>
						</tr>
						<tr>
							<td>'.$lang['miniature_yes'].'</td>
							<td>
								<select class="sw250" name="thumb">
									<option value="yes">'.$lang['all_yes'].'</option>
									<option value="no">'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['process'].'</td>
							<td>
								<select name="resize">
									<option value="yes">'.$lang['all_resize'].'</option>
									<option value="crop">'.$lang['crop_resize'].'</option>
									<option value="no">'.$lang['scale_resize'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select name="width">'.$width.'</select> &nbsp;&#215;&nbsp;
								<select name="height">'.$height.'</select>
							</td>
						</tr>';
			echo '		<tr>
							<th></th>
							<th class="site bold">'.$lang['all_image_big'].'</th>
						</tr>
						<tr>
							<td>'.$lang['image_resize'].'</td>
							<td>
								<select class="sw250" name="resize_big">
									<option value="yes">'.$lang['all_yes'].'</option>
									<option value="no">'.$lang['all_no'].'</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_width'].' &nbsp;&#215;&nbsp; '.$lang['all_height'].'</td>
							<td>
								<select name="width_big">';
			for ($h = 300; $h <= 1900; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $conf['wbig']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 99;
			}
			echo '				</select> &nbsp;&#215;&nbsp;
								<select name="height_big">';
			for ($h = 240; $h <= 1200; $h ++) {
				echo '				<option value="'.$h.'"'.(($h == $conf['hbig']) ? ' selected' : '').'>'.$h.' px</option>';
									$h = $h + 59;
			}
			echo '				</select>
							</td>
						</tr>
					</table>
					</div>
					<div class="pad"></div>
					<div class="section">
					<table class="work">
						<caption>'.$lang['image_upload'].'</caption>
						<tr>
							<td style="padding:0;">
								<div id="upload-area">
									<div id="upload-input-1">
										<table class="work">
											<tr>
												<th></th>
												<th>'.$lang['all_image'].' 1</th>
											</tr>
											<tr>
												<td class="first site"><span>*</span> '.$lang['all_file'].'</td>
												<td><input name="files[]" type="file" onchange="$.massuploadcreate();" required="required"></td>
											</tr>
											<tr>
												<td class="gray">'.$lang['all_name'].'</td>
												<td><input type="text" name="names[]" size="70">';
													$tm->outhint($lang['mass_name']);
			echo '								</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
						<tr class="tfoot">
							<td>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all">';
			}
			echo '				<input type="hidden" name="upid" id="upid" value="1">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="massupsave">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</div>
					</form>';

			$tm->footer();
		}

		/**
		 * Массовое добавление изображений (сохранение)
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'massupsave')
		{
			global	$conf, $catid, $thumb, $width, $height, $width_big, $height_big,
					$resize_big, $injpg, $wmark, $resize, $names, $desc, $files, $acc;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_photos'].'</a>',
					$lang['array_control']
				);

			$catid = preparse($catid, THIS_INT);
			$ext   = array('gif', 'jpeg', 'jpg', 'png');
			$wmark = ((isset($wmark) AND $wmark == 'on') ? 'yes' : 'no');

			require_once(ADMDIR.'/core/classes/Image.php');
			$images = new Image();

			if (isset($_FILES['files']))
			{
				for ($i = 0; $i < sizeof($_FILES['files']['tmp_name']); $i ++)
				{
					$exttype = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
					$extname = explode('.', trim($_FILES['files']['name'][$i]));

					if (substr($_FILES['files']['type'][$i], 0, 5) == 'image' AND in_array($exttype, $ext) AND $_FILES['files']['tmp_name'][$i])
					{
						// Проверка существующего каталога, и создание нового
						$folder = strtolower(date("My"));
						$ndir = '/up/'.PERMISS.'/album/'.$folder;

						if(file_exists(WORKDIR.$ndir))
						{
							if(is_dir(WORKDIR.$ndir))
							{
								$folder = $folder;
							}
						}
						else
						{
							if(@mkdir(WORKDIR.$ndir))
							{
								chmod(WORKDIR.$ndir, 0777);
								$html_write = fopen(WORKDIR.$ndir."/index.html", "wb");
								fwrite($html_write,'<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body></body></html>');
								fclose($html_write);
							}
							else
							{
								$tm->header();
								$tm->error($modname[PERMISS], $lang['add_mass'], $lang['dir_creat_error']);
								$tm->footer();
							}
						}

						// Уникальное имя файла
						$fn = date("ymd", time()).'_'.mt_rand(0, 9999);

						$newname = $fn.'.'.$exttype;
						$newname_thumb = $fn.'_thumb.'.$exttype;

						// Путь к каталогу
						$new_folder = 'up/'.PERMISS.'/album/'.$folder.'/';

						if (move_uploaded_file($_FILES['files']['tmp_name'][$i], WORKDIR.'/'.$new_folder.$newname))
						{
							$images->start();
							if (isset($resize_big) AND $resize_big == 'yes' AND file_exists(WORKDIR.'/'.$new_folder.$newname))
							{
								$images->createthumb
									(
										WORKDIR.'/'.$new_folder.$newname,
										WORKDIR.'/'.$new_folder,
										$newname,
										$width_big,
										$height_big,
										'yes'
									);
							}

							// Если конвертировать в jpg
							if ($injpg == 'yes' AND $exttype != 'jpg')
							{
								$name_jpg = $fn.'.jpg';
								$images->imgconvert(WORKDIR.'/'.$new_folder.$newname, WORKDIR.'/'.$new_folder.$name_jpg);
								$newname = $fn.'.jpg';
								$newname_thumb = $fn.'_thumb.jpg';
							}

							$titlereal = (isset($names[$i]) AND ! empty($names[$i])) ? $names[$i] : $extname[0];
							$descreal = $db->escape($extname[0]);
							$altreal = $db->escape($extname[0]);
							$cpu = cpu_translit($titlereal);

							$sql = "INSERT INTO ".$basepref."_".PERMISS." VALUES (
									NULL,
									'".$catid."',
									'".NEWTIME."',
									'".$cpu."',
									'".$db->escape(preparse_sp($titlereal))."',
									'".$db->escape(preparse_sp($titlereal))."',
									'".$db->escape(preparse_sp($titlereal))."',
									'',
									'',
									'".$db->escape($descreal)."',
									'".$db->escape($new_folder.$newname)."',
									'',
									'".$db->escape(preparse_sp($altreal))."',
									'0',
									'',
									'yes'
									)";

							if (isset($thumb) AND $thumb == 'yes' AND file_exists(WORKDIR.'/'.$new_folder.$newname))
							{
								$images->createthumb(
											WORKDIR.'/'.$new_folder.$newname,
											WORKDIR.'/'.$new_folder,
											$newname_thumb,
											$width,
											$height,
											$resize
											);

								if (file_exists(WORKDIR.'/'.$new_folder.$newname_thumb))
								{
									$sql =	"INSERT INTO ".$basepref."_".PERMISS." VALUES (
											 NULL,
											 '".$catid."',
											 '".NEWTIME."',
											 '".$cpu."',
											 '".$db->escape(preparse_sp($titlereal))."',
											 '".$db->escape(preparse_sp($titlereal))."',
											 '".$db->escape(preparse_sp($titlereal))."',
											 '',
											 '',
											 '".$db->escape($descreal)."',
											 '".$db->escape($new_folder.$newname)."',
											 '".$db->escape($new_folder.$newname_thumb)."',
											 '".$db->escape(preparse_sp($altreal))."',
											 '0',
											 '',
											 'yes'
											 )";
								}
							}

							if ($conf['wateruse'] == 'img' AND ! empty($conf['waterpatch']) AND $wmark == 'yes')
							{
								$images->createwater(WORKDIR.'/'.$new_folder.$newname, WORKDIR.'/'.$conf['waterpatch']);
							}
							elseif ($conf['wateruse'] == 'txt' AND ! empty($conf['watertext']) AND $wmark == 'yes')
							{
								$images->createwater(WORKDIR.'/'.$new_folder.$newname, 0, 1, $conf['watertext']);
							}
						}
						$db->query($sql);
					}
				}
			}

			$counts = new Counts(PERMISS, 'id');
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Функция создания эскиза изображения
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'thumb')
		{
			global $type, $id, $x, $h, $r;

			if ($type == 'media')
			{
				$id = preparse($id, THIS_INT);
				$item = $db->fetchrow($db->query("SELECT image, image_thumb FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
				$path = $item['image_thumb'];
			}
			else
			{
				$catid = preparse($id, THIS_INT);
				$item = $db->fetchrow($db->query("SELECT icon FROM ".$basepref."_".PERMISS."_cat WHERE catid = '".$catid."'"));
				$path = $item['icon'];
			}
			thumb($path, $x, $h, $r);
			exit();
		}

		/**
		 * Предпросмотр видео
		 ----------------------*/
		if ($_REQUEST['dn'] == 'video')
		{
			global $id, $conf, $sess;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT video, image_thumb FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			echo '	<div style="width: 400px; height: 350px;">
						<object>
							<embed src="'.$conf['site_url'].'/up/mediaplayer.swf" allowscriptaccess="always" allowfullscreen="true" flashvars="file='.$conf['site_url'].'/'.$item['video'].'&amp;searchbar=false" width="400" height="350"></embed>
						</object>
					</div>';
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
