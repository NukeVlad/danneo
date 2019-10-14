<?php
/**
 * File:        /admin/mod/poll/index.php
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
				'index', 'optsave', 'list', 'add', 'addsave', 'edit', 'editsave', 'valsaddsave',
				'valsup', 'valsdel', 'del', 'gethtml', 'comment', 'commentrep', 'commentedit', 'commenteditrep'
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
			global $db, $basepref, $conf, $tm, $lang, $sess, $AJAX;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['polling_menu_all'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['polling_menu_add'].'</a>';

			if (isset($conf[PERMISS]['comact']) AND $conf[PERMISS]['comact'] == 'yes')
			{
				if ($AJAX) {
					$link.= '<a class="all-comments" href="index.php?dn=comment&amp;ajax=1&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>';
				} else {
					$link.= '<a href="index.php?dn=comment&amp;ajax=0&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>';
				}
			}

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
				echo 			eval($itemset['setcode']);
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
						$tm->error($modname[PERMISS], $lang['all_save'].' '.$lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'],THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Все опросы (листинг)
		 -------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $indextime;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['polling_menu_all'].'</a>'
				);

			$tm->header();

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;

			if ($ajaxlink)
			{
				echo '	<script>
						$(document).ready(function()
						{
							$.ajaxSetup({cache: false, async: false});
							$(".comment-view").colorbox({
								width	: "92%",
								height	: "90%",
								initialWidth	: 900,
								initialHeight	: 600,
								maxHeight	: 800,
								maxWidth	: 1200,
								fixed: true,
								onComplete: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
						});
						</script>';
			}

			$inq = $db->query("SELECT * FROM ".$basepref."_".PERMISS." ORDER BY id DESC");
			$vals = $db->fetchrow($db->query("SELECT SUM(vals_voices) AS total FROM ".$basepref."_".PERMISS."_vals"));

			// Группы в массив
			$groups = array();
			if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
			{
				$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
				while ($items = $db->fetchrow($inqs)) {
					$groups[] =  $items['title'];
				}
			}

			echo '	<div class="section">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['polling_menu_all'].'</caption>
						<tr>
							<th>'.$lang['all_title'].'</th>
							<th>'.$lang['polling_all_sf'].'</th>
							<th>'.$lang['all_status'].'</th>
							<th>'.$lang['all_access'].'</th>
							<th>'.$lang['polling_item_add_voc'].'</th>
							<th>'.$lang['menu_comment'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = ($item['finish'] > $indextime AND  $item['act'] == 'yes') ? '' : 'noactive';

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

				echo '	<tr>
							<td class="'.$style.' al vm site">'.$item['title'].'</td>
							<td class="'.$style.' pw25"><span class="server">'.format_time($item['start'], 0, 1).'</span> &nbsp; &#8260; &nbsp; <span class="alternative">'.format_time($item['finish'],0,1).'</span></td>
							<td class="'.$style.'">
								'.(($item['finish'] > $indextime AND  $item['act'] == 'yes') ? '<span class="server">'.$lang['actively'].'</span><br />' : '<span class="alternative">'.$lang['inactive'].'</span><br />').'
								'.(($item['finish'] < $indextime) ? '<span class="alternative">'.$lang['polling_status_end'].'</span><br />' : '').'
							</td>
							<td class="'.$style.'">
								'.(($item['acc'] == 'user') ? ( ! empty($item['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']).'
							</td>
							<td class="'.$style.'">'.$vals['total'].'</td>
							<td class="'.$style.' light com">';
				$cinq = $db->numrows($db->query("SELECT * FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$item['id']."'"));
				if ($cinq > 0) {
					echo '		'.$cinq.'&nbsp; &nbsp;
								<a class="comment-view" href="index.php?dn=commentedit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].(($ajaxlink) ? '&amp;ajax=1' : '').'">
									<img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" />
								</a>';
				} else {
					echo		'0';
				}
				echo '		</td>
							<td class="'.$style.' gov">
								<a href="index.php?dn=edit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=del&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '	</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить опрос
		 ------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['polling_menu_all'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$stime = CalendarFormat(NEWTIME);
			$ftime = CalendarFormat(NEWTIME + 604800);

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['polling_menu_add'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_title'].'</td>
							<td><input type="text" name="title" id="title" size="70" autofocus required /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" /> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70">';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70"> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
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
						</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_start'].'</td>
							<td><input type="text" name="start" id="start" value="'.$stime.'" required="required" />';
								Calendar('cal', 'start');
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_finish'].'</td>
							<td><input type="text" name="finish" id="finish" value="'.$ftime.'" required="required" />';
								Calendar('cal1', 'finish');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('decs', 5, 50, '', 1);
			echo '			</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['polling_add_access'].'</td>
							<td>
								<select class="group-sel" id="acc" name="acc">
									<option value="all" selected>'.$lang['all_all'].'</option>
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
			echo '		</tbody>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="addsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit" />
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
		 * Добавить опрос (сохранение)
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $decs, $title, $subtitle, $cpu, $customs, $descript, $keywords, $start, $finish, $acc, $group;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['polling_menu_all'].'</a>',
					$lang['all_add']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);
			$decs = preparse($decs, THIS_TRIM);
			$start = (empty($start)) ? NEWTIME : ReDate($start);
			$finish = (empty($start)) ? (NEWTIME + (3600 * 24)) : ReDate($finish);
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';

			if (preparse($title, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['polling_menu_add'], $lang['pole_add_error']);
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
					$tm->error($modname[PERMISS], $lang['polling_menu_add'], $lang['cpu_error_isset'], $title);
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

			$db->query
				(
					"INSERT INTO ".$basepref."_".PERMISS." VALUES (
					 NULL,
					 '".$db->escape($cpu)."',
					 'no',
					 '".$start."',
					 '".$finish."',
					 '".$acc."',
					 '".$db->escape($group)."',
					 '".$db->escape(preparse_sp($title))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$db->escape(preparse_sp($customs))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape($decs)."',
					 'yes',
					 '0'
					 )"
				);

			$id = $db->insertid();
			redirect('index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать опрос
		 ------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$item  = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$stime = CalendarFormat($item['start']);
			$ftime = CalendarFormat($item['finish']);

			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>';

			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['polling_menu_edit'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_title'].'</td>
							<td><input type="text" name="title" id="title" size="70" value="'.$item['title'].'" autofocus required /> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'"> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes') {
			echo '		<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td>
								<input type="text" name="cpu" id="cpu" size="70" value="'.$item['cpu'].'" />';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
			echo '        </td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="customs" size="70" value="'.preparse_un($item['customs']).'"> <span class="light">&lt;title&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
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
						</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_start'].'</td>
							<td><input type="text" name="start" id="start" value="'.$stime.'" required />';
								Calendar('cal', 'start');
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_add_finish'].'</td>
							<td><input type="text" name="finish" id="finish" value="'.$ftime.'" required />';
								Calendar('cal1', 'finish');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('decs', 5, 50, $item['decs'], 1);
			echo '			</td>
						</tr>
						<tr>
							<td>AJAX</td>
							<td>
								<select name="ajax">
									<option value="yes"'.(($item['ajax'] == 'yes') ? ' selected' : '').'>'.$lang['included'].'</option>
									<option value="no"'.(($item['ajax'] == 'no') ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['polling_add_access'].'</td>
							<td>
								<select class="group-sel" name="acc" id="acc">
									<option value="all"'.(($item['acc'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['acc'] == 'user') ? ' selected' : '').'>'.$lang['all_user_only'].'</option> ';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['acc'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['acc'] == 'all' OR $item['acc'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if ($conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').' /><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['condition'].'</td>
							<td>
								<select name="act">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['actively'].'</option>
									<option value="no"'.(($item['act'] == 'no') ? ' selected' : '').'>'.$lang['inactive'].'</option>
								</select>
							</td>
						</tr>
						</tbody>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no') {
				echo '			<input type="hidden" name="acc" value="all" />';
			}
			echo '				<input type="hidden" name="dn" value="editsave" />
								<input type="hidden" name="id" value="'.$id.'" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value=" '.$lang['all_save'].' " type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>';

			// Все пункты данного опроса
			$inquiry = $db->query("SELECT * FROM ".$basepref."_".PERMISS."_vals WHERE id='".$id."' order by posit");
			if ($db->numrows($inquiry) > 0)
			{
				echo '	<script>
						$(function()
						{
							$(".colur").ColorPicker({
								onSubmit: function(hsb, hex, rgb, el) {
									$(el).val(hex);
									$(el).ColorPickerHide();
									var id = $(el).attr("id");
									$("#" + id + "-out").css({background : "#" + hex});
								},
								onBeforeShow: function () {
									$(this).ColorPickerSetColor(this.value);
								}
							}).bind("keyup", function(){
								$(this).ColorPickerSetColor(this.value);
							});
						});
						</script>';
				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['polling_item_add'].'</caption>
							<tr>
								<th>'.$lang['polling_item_add_name'].'</th>
								<th>'.$lang['all_posit'].'</th>
								<th>'.$lang['polling_item_add_voc'].'</th>
								<th>'.$lang['all_color'].'</th>
								<th>'.$lang['sys_manage'].'</th>
							</tr>';
				while ($item = $db->fetchrow($inquiry))
				{
					echo '	<tr>
								<td class="pw25"><input type="text" value="'.$item['vals_title'].'" name="title['.$item['valsid'].']" size="5" style="width: 96%;"></td>
								<td><input type="text" value="'.$item['posit'].'" name="posit['.$item['valsid'].']" size="3" maxlength="3"></td>
								<td><input type="text" value="'.$item['vals_voices'].'" name="voc['.$item['valsid'].']" size="5" maxlength="11"></td>
								<td>
									<input class="fl colur" type="text" id="colur-'.$item['valsid'].'" value="'.$item['vals_color'].'" name="color['.$item['valsid'].']" size="5" maxlength="11">
									<div class="vals-color" id="colur-'.$item['valsid'].'-out" style="background:#'.$item['vals_color'].';border: 1px solid #'.$item['vals_color'].'">&nbsp;</div>
								</td>
								<td class="ac gov pw5">
									<a href="index.php?dn=valsdel&amp;id='.$id.'&amp;valsid='.$item['valsid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
				echo '		<tr class="tfoot">
								<td colspan="5">
									<input type="hidden" name="dn" value="valsup">
									<input type="hidden" name="id" value="'.$id.'">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input class="main-button" value="'.$lang['all_save'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>
						<div class="pad"></div>';
			}

			// Добавление нового пункта опроса
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['polling_item_add_title'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['polling_item_add_name'].'</td>
							<td><input type="text" name="vals_title" size="50" required="required"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="valsaddsave">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
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
		 * Добавление нового пункта опроса (сохранение)
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'valsaddsave')
		{
			global $vals_title, $id;

			$id = preparse($id, THIS_INT);
			$vals_title = preparse($vals_title, THIS_TRIM, 0, 255);

			if ($vals_title AND $id)
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_".PERMISS."_vals VALUES (
						 NULL,
						 '".$id."',
						 '".$db->escape($vals_title)."',
						 '0',
						 '000000',
						 '0'
						 )"
					);
			}

			redirect('index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Все пункты данного опроса (сохранение)
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'valsup')
		{
			global $posit, $title, $voc, $color, $id;

			$id = preparse($id, THIS_INT);

			// Позиция пункта
			foreach ($posit as $id_pos => $pos)
			{
				if (empty($pos)) {
					$pos = 0;
				}
				if (isset($id_pos)) {
					$db->query("UPDATE ".$basepref."_".PERMISS."_vals SET posit = '".intval($pos)."' WHERE valsid = '".intval($id_pos)."'");
				}
			}

			// Название пункта
			foreach ($title as $id_name => $names)
			{
				if (isset($id_name) AND ( ! empty($names))) {
					$names = preparse($names,THIS_TRIM,0,255);
					$db->query("UPDATE ".$basepref."_".PERMISS."_vals SET vals_title = '".$db->escape($names)."' WHERE valsid = '".intval($id_name)."'");
				}
			}

			// Голосов
			foreach ($voc as $id_vote => $vote)
			{
				if (empty($vote)) {
					$vote = 0;
				}
				if (isset($id_vote)) {
					$db->query("UPDATE ".$basepref."_".PERMISS."_vals SET vals_voices = '".intval($vote)."' WHERE valsid = '".intval($id_vote)."'");
				}
			}

			// Цвет пункта
			foreach ($color as $id_color => $newcolor)
			{
				if (empty($newcolor) OR preparse($newcolor,THIS_STRLEN) != 6) {
					$newcolor = '000000"';
				}
				if (isset($id_color)) {
					$db->query("UPDATE ".$basepref."_".PERMISS."_vals SET vals_color = '".$db->escape($newcolor)."' WHERE valsid = '".intval($id_color)."'");
				}
			}

			redirect('index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление пункта опроса (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'valsdel')
		{
			global $id, $valsid, $ok;

			$id = preparse($id, THIS_INT);
			$valsid = preparse($valsid, THIS_INT);

			$poll = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS."_vals WHERE valsid = '".$valsid."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash'].'">'.preparse_un($poll['title']).'</a>',
					$lang['del_item']
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_vals WHERE valsid = '".$valsid."'");
				redirect('index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=valsdel&amp;id='.$id.'&amp;valsid='.$valsid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel(preparse_un($poll['title']), $lang['del_item'], $yes, $not, preparse_un($item['vals_title']));
				$tm->footer();
			}
		}

		/**
		 * Редактировать опрос (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $id, $decs, $title, $subtitle, $cpu, $customs, $descript, $keywords, $ajax, $act, $start, $finish, $acc, $group;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['polling_menu_edit']
				);

			$id = preparse($id, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);
			$decs = preparse($decs, THIS_TRIM);
			$start = (empty($start)) ? NEWTIME : ReDate($start);
			$finish = (empty($start)) ? (NEWTIME + (3600 * 24)) : ReDate($finish);
			$ajax = ($ajax == 'yes') ? 'yes' : 'no';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';

			if (preparse($title, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['polling_menu_edit'], $lang['pole_add_error']);
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
					$tm->error($modname[PERMISS], $lang['polling_menu_edit'], $lang['cpu_error_isset'], $title);
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

			if ($finish < NEWTIME)
			{
				$act = 'no';
			}

			if ($start > $finish OR $start == $finish)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['polling_menu_edit'], $lang['polling_error_date']);
				$tm->footer();
			}

			$db->query
				(
					"UPDATE ".$basepref."_".PERMISS." SET
					 cpu    = '".$db->escape($cpu)."',
					 act    = '".$act."',
					 start  = '".$start."',
					 finish = '".$finish."',
					 acc    = '".$acc."',
					 groups = '".$db->escape($group)."',
					 title  = '".$db->escape(preparse_sp($title))."',
					 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
					 customs     = '".$db->escape(preparse_sp($customs))."',
					 descript    = '".$db->escape(preparse_sp($descript))."',
					 keywords    = '".$db->escape(preparse_sp($keywords))."',
					 decs   = '".$db->escape($decs)."',
					 ajax   = '".$ajax."'
					 WHERE id = '".$id."'"
				);

			redirect('index.php?dn=edit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление опроса
		 --------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['del_poll']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_vote WHERE id = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS."_vals WHERE id = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'");

				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".PERMISS." WHERE id = '".$id."'"));

				$yes = 'index.php?dn=del&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($modname[PERMISS], $lang['del_poll'], $yes, $not, preparse_un($item['title']));
				$tm->footer();
			}
		}

		/**
		 * Комментарии - Все опросы
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'comment')
		{
			global $nu, $p, $id, $ajax, $atime;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);
			$atime = preparse($atime, THIS_INT);

			if ($ajax == 0)
			{
				$tm->header();
			}

			$nu = (isset($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$total = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND (ctime >= '".$atime."')"));
			if (($p - 1) * $nu > $total['total']) {
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
							$.ajaxSetup({cache: false, async: false});
							$(".sort a").colorbox({
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								"href"	: $(this).attr("href"),
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#comment-form");
									$.ajax({
											cache	: false,
											type	: "POST",
											data	: $elm.serialize() + "&ajax=1",
											url		: $.apanel + "/mod/'.PERMISS.'/index.php",
											error	: function(data) {  },
											success : function(data) { $("#comment-form").html(data).show(); }
										});
								},
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								onComplete	: function () {
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
							<th>'.$lang['polling_one'].'</th>
							<th>'.$lang['comment_text'].'</th>
							<th class="ac">'.$lang['one_add'].'</th>
							<th class="ac"><input class="but" id="selects" value="x" type="button" title="'.$lang['all_delet'].'"></th>
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
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['comid'].']" value="1"></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="atime" value="'.$atime.'">
								<input type="hidden" name="dn" value="commentrep">
								<input class="but submit" value="'.$lang['all_save'].'" type="'.(($ajax) ? 'button' : 'submit').'">
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
		 * Комментарии - Все опросы (сохранение)
		 -----------------------------------------*/
		if ($_REQUEST['dn'] == 'commentrep')
		{
			global $id, $p, $nu, $text, $dels, $ajax, $atime;

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
							$texts = preparse($text[$key], THIS_TRIM);
							$db->query("UPDATE ".$basepref."_comment SET ctext  = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
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
		 * Комментарии отдельного опроса
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
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$total = $db->fetchrow($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."' ORDER BY comid DESC"));
			if (($p - 1) * $nu > $total['total']) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages("comment WHERE file = '".PERMISS."' AND id='".$id."' ORDER BY comid DESC", 'id', 'index', 'commentedit&amp;id='.$id.'&amp;ajax='.$ajax, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=commentedit&amp;p=".$p."&amp;id=".$id."&amp;ops=".$sess['hash']."&amp;ajax=".$ajax, $nu);

			$inq = $db->query("SELECT * FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND id = '".$id."' ORDER BY comid DESC LIMIT ".$sf.", ".$nu);
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
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								"href"	: $(this).attr("href"),
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 145) + "px"});
								}
							});
							$(".submit").colorbox({
								onLoad: function() {
									var $elm = $("#comment-form");
									$.ajax({
										cache	: false,
										type	: "POST",
										data	: $elm.serialize() + "&ajax=1",
										url	: "index.php",
										error	: function(data) {  },
										success	: function(data) {  }
									});
								},
								width	: "92%",
								height	: "90%",
								maxHeight	:  800,
								maxWidth	:  1200,
								fixed: true,
								onComplete	: function () {
									var $h = $("#cboxLoadedContent").height();
									$("#fb-work-comm").css({"height" : ($h - 162) + "px"});
								},
								"href"  : "index.php?dn=commentedit&p='.$p.'&id='.$id.'&ops='.$sess['hash'].'&ajax=1"
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
							<th class="ac"><input class="but" id="selects" value="x" type="button" title="'.$lang['all_delet'].'"></th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="ac vm">';
				if ($item['userid'] > 0) {
					echo '		<a href="user.php?dn=edit&amp;uid='.$item['userid'].'&amp;ops='.$sess['hash'].'" title="'.$lang['all_edit'].'">'.$item['cname'].'</a>';
				} else {
					echo '		'.$item['cname'];
				}
				echo '		</td>
							<td>';
								$tm->textarea('text['.$item['comid'].']', 5, 25, $item['ctext'], 1);
				echo '		</td>
							<td class="vt pw16">'.format_time($item['ctime'], 1, 1).'</td>
							<td class="ac pw5"><input type="checkbox" name="dels['.$item['comid'].']" value="1"></td>
						</tr>';
			}
			echo '	</table>
					</div>
					<table class="fb-work">
						<tr><td class="sort ar" colspan="3">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="dn" value="commenteditrep">
								<input class="but submit" value="'.$lang['all_save'].'" type="'.(($ajax) ? 'button' : 'submit').'">
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
		 * Комментарии отдельного опроса (сохранение)
		 ----------------------------------------------*/
		if ($_REQUEST['dn'] == 'commenteditrep')
		{
			global $id, $p, $nu, $text, $title, $dels, $ajax;

			$ajax = preparse($ajax, THIS_INT);
			$id = preparse($id, THIS_INT);
			$nu = preparse($nu, THIS_INT);
			$p = preparse($p, THIS_INT);

			if (is_array($text) AND ! empty($text))
			{
				foreach ($text as $key => $val)
				{
					if (isset($dels[$key]) AND $dels[$key] == 1)
					{
						$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".PERMISS."' AND comid = '".$key."'");
					}
					else
					{
						if (preparse($text[$key], THIS_EMPTY) == 0)
						{
							$texts = preparse($text[$key], THIS_TRIM);
							$db->query("UPDATE ".$basepref."_comment SET ctext  = '".$db->escape($texts)."' WHERE file = '".PERMISS."' AND comid = '".$key."'");
						}
					}
				}
			}

			if ($ajax == 0)
			{
				redirect('news.php?dn=commentedit&amp;p='.$p.'&amp;nu='.$nu.'&amp;id='.$id.'&amp;ops='.$sess['hash']);
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
