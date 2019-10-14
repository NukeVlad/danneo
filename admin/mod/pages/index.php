<?php
/**
 * File:        /admin/mod/pages/index.php
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
define('PERMISS', 'pages');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $modname, $wysiwyg, $lang, $sess, $ops, $cache, $IPS, $pl;

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
		 * Текущий мод
		 */
		$pl = (isset($pl) AND isset($IPS[$pl]['mod'])) ? preparse($pl, THIS_INT) : 0;

		if (isset($_COOKIE[PCLONE])) {
			list($pl) = unserialize($_COOKIE[PCLONE]);
		}

		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'optsave', 'list', 'add', 'addsave', 'edit', 'editsave', 'act', 'del', 'arrdel',
				'mod', 'addmod', 'editmod', 'editmodsave', 'modsetsave', 'delmod'
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
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess, $IPS, $pl;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, edit').' href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['all_page'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['add_page'].'</a>'
					.'<a'.cho('mod, editmod').' href="index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['platforms_pages'].'</a>';

			$filter = null;
			if (cho('list')) {
				$filter = '<a'.cho('list', 1).' href="#" onclick="$(\'#filter\').slideToggle();" title="'.$lang['search_in_section'].'">'.$lang['all_filter'].'</a>';
			}

			$form = null;
			if (cho('index, list, add, edit') AND count($IPS) > 1)
			{
				$form = '<form action="javascript:void(0);" method="post" id="ajaxpages">'
						.'<select name="pl" class="sw130">';
				foreach ($IPS as $paid => $tarrs)
				{
					$form.= '<option value="'.$paid.'"'.((isset($pl) AND $pl == $paid) ? ' selected' : '').'>'.$tarrs['name'].'</option>';
				}
				$form.= '</select>'
						.'<input type="hidden" name="ops" value="'.$sess['hash'].'">'
						.'<input type="hidden" name="dn" value="pagesclone">'
						.'<a href="javascript:document.getElementById(\'ajaxpages\').submit();" onclick="$.pagesclone();">'.$lang['re_pages'].'</a>'
						.'</form>';
			}

			$tm->this_menu($link, $filter, $form);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 -------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $dn, $pl, $IPS, $tm, $sess, $lang;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
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
				if ($itemset['setname'] != 'mods')
				{
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
								<input type="hidden" name="dn" value="optsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="pl" value="'.$pl.'">
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
			global $set, $pl, $cache, $IPS;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
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

			$ins = Json::encode($IPS);
			$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mods'");

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;pl='.$pl.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Все страницы (листинг)
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $nu, $p, $ro, $s, $l, $ajax, $filter, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_page']
				);

			$ajaxlink = (defined('ENABLE_AJAX') AND ENABLE_AJAX == 'yes') ? 1 : 0;

			if (preparse($ajax, THIS_INT) == 0)
			{
				$tm->header();
				echo '<div id="ajaxbox">';
			}
			else
			{
				echo '	<script>
							$(function(){
								$("img, a").tooltip();
								cookie.set("num", "'.$nu.'", { path: "/'.APANEL.'/" }); // (num) in a cookie
							});
						</script>';
			}

			$sort  = array('paid', 'title', 'public', 'uppublic');
			$limit = array('desc', 'asc');
			$s = (in_array($s, $sort)) ? $s : 'public';
			$l = (in_array($l, $limit)) ? $l : 'desc';

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);

			$nu  = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$act = ( ! isset($act) OR $act == 1) ? 1 : 0;
			$p   = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$fu  = '';
			$sql = '';
			$fid = preparse($fid, THIS_INT);
			$myfilter = array
			(
				'title'    => array('title', 'all_name', 'input'),
				'public'   => array('public', 'all_data', 'date'),
				'uppublic' => array('uppublic', 'all_updata', 'date'),
				'acc'      => array('acc', 'all_access', 'type', array('unimportant', 'all_all', 'all_user_only'), array('', 'all', 'user')),
				'imp'      => array('imp', 'all_important', 'checkbox')
			);
			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0)
				{
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ', $insert);
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
							if ($f[2] == 'checkbox' AND ! empty($v)) {
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
						$sql.= (($sql == '') ? ' WHERE ' : ' AND ').implode(' AND ', $sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->increment('mods_filter');
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$fid = $db->insertid();
						if ($fid > 0) {
							$fu = '&amp;fid='.$fid;
						}
					}
				}
			}
			$link = $fu;
			$a = ($ajaxlink) ? '&amp;ajax=1' : '';
			$revs = $a.'&amp;nu='.$nu.'&amp;s='.$s.'&amp;l='.(($l=='desc') ? 'asc' : 'desc');
			$rev =  $a.'&amp;nu='.$nu.'&amp;l=desc&amp;s=';
			$link.= $a.'&amp;s='.$s.'&amp;l='.$l;

			$c = $db->fetchrow($db->query("SELECT COUNT(paid) AS total FROM ".$basepref."_pages WHERE mods = '".$IPS[$pl]['mod']."'"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}
			$sf = $nu * ($p - 1);

			$sql.= ($sql == '') ? " WHERE mods = '".$IPS[$pl]['mod']."'" : " AND mods = '".$IPS[$pl]['mod']."'";
			$inq = $db->query("SELECT * FROM ".$basepref."_pages".$sql." ORDER BY ".$s." ".$l." LIMIT ".$sf.", ".$nu);
			$pages = $modname[PERMISS].':&nbsp; '.adm_pages("pages WHERE mods = '".$IPS[$pl]['mod']."'", 'paid', 'index', 'list'.$link, $nu, $p, $sess, $ajaxlink);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;pl=".$pl."&amp;ops=".$sess['hash'].$link, $nu, $ajaxlink);

			// Группы в массив
			$groups_only = array();
			if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
			{
                $inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
                while ($items = $db->fetchrow($inqs)) {
                    $groups_only[] =  $items['title'];
               }
			}

			// Поиск по фильтру
			$tm->filter('index.php?dn=list&amp;ops='.$sess['hash'], $myfilter, $IPS[$pl]['name']);

			echo '	<script>
					$(function() {
						$("*").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>';
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$IPS[$pl]['name'].': '.$lang['all_page'].'</caption>';
			if ($db->numrows($inq) > 0)
			{
				echo '	<tr><td colspan="8">'.$amount.'</td></tr>
						<tr>
							<th'.artsort('paid').' class="ac">ID</th>
							<th'.artsort('title').'>'.$lang['all_name'].'</th>
							<th'.artsort('public').'>'.$lang['all_data'].'</th>
							<th'.artsort('uppublic').'>'.$lang['all_updata'].'</th>
							<th class="work-no-sort">'.$lang['all_access'].'</th>
							<th class="work-no-sort">'.$lang['all_link'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
							<th class="work-no-sort ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox"></th>
						</tr>';
				while ($item = $db->fetchrow($inq))
				{

					// Ассоциируем группы
					$groupact = '';
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

					$style = ($item['act'] == 'no') ? 'no-active ' : '';
					$mod_page = ($IPS[$pl]['mod'] == 'pages') ? '' : '&pa='.$IPS[$pl]['mod'];
					$url_page = 'index.php?dn=pages'.$mod_page.'&amp;cpu='.$item['cpu'];

					echo '	<tr class="list">
								<td class="'.$style.'ac pw5">'.$item['paid'].'</td>
								<td class="'.$style.'pw25">'.$item['title'].'</td>
								<td class="'.$style.'pw10">'.format_time($item['public'], 0, 1).'</td>
								<td class="'.$style.'pw10">'.(($item['uppublic'] > 0) ? format_time($item['uppublic'], 0, 1) : '&#8212;').'</td>
								<td class="'.$style.'pw15">
									'.(($item['acc'] == 'user') ? ( ! empty($item['groups']) ? $lang['all_groups_only'].': <span class="server">'.$groupact.'</span>' : $lang['all_user_only']) : $lang['all_all']).'
								</td>
								<td class="'.$style.'pw25">
									<input class="readonly" type="text" name="title" size="50" value="'.$url_page.'" readonly="readonly">
								</td>
								<td class="'.$style.'gov pw10">
									<a href="index.php?dn=edit&amp;pl='.$pl.'&amp;paid='.$item['paid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
							if ($item['act'] == 'yes') {
								echo '		<a href="index.php?dn=act&amp;pl='.$pl.'&amp;act=no&amp;paid='.$item['paid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
							} else {
								echo '		<a class="inact" href="index.php?dn=act&amp;pl='.$pl.'&amp;act=yes&amp;paid='.$item['paid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
							}
					echo '			<a href="index.php?dn=del&amp;pl='.$pl.'&amp;paid='.$item['paid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
								<td class="'.$style.'mark"><input type="checkbox" name="delarray['.$item['paid'].']" value="yes"></td>
							</tr>';
				}
				echo '	<tr><td colspan="8">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="dn" value="arrdel">
								<input type="hidden" name="pl" value="'.$pl.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_delet'].'" type="submit">
							</td>
						</tr>';
			}
			else
			{
				echo '	<tr>
							<td colspan="8" class="ac">
								<div class="pad">'.$lang['data_not'].'</div>
							</td>
						</tr>';
			}
			echo '	</table>
					</form>
					</div>';

			if (preparse($ajax, THIS_INT) == 0)
			{
				echo '</div>';
				$tm->footer();
			}
		}

		/**
		 * Добавить страницу
		 ----------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_submint']
				);

			$tm->header();

			$public = CalendarFormat(NEWTIME);
			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script>
					var all_name     = "'.$lang['all_name'].'";
					var all_cpu      = "'.$lang['all_cpu'].'";
					var all_thumb    = "'.$lang['all_image_thumb'].'";
					var all_img      = "'.$lang['all_image'].'";
					var all_images   = "'.$lang['all_image_big'].'";
					var all_align    = "'.$lang['all_align'].'";
					var all_right    = "'.$lang['all_right'].'";
					var all_left     = "'.$lang['all_left'].'";
					var all_center   = "'.$lang['all_center'].'";
					var all_alt      = "'.$lang['all_alt_image'].'";
					var all_copy     = "'.$lang['all_copy'].'";
					var all_delet    = "'.$lang['all_delet'].'";
					var code_paste   = "'.$lang['code_paste'].'";
					var all_file     = "'.$lang['all_file'].'";
					var all_path     = "'.$lang['all_path'].'";
					var page         = "pages";
					var ops          = "'.$sess['hash'].'";
					var filebrowser = "'.$lang['filebrowser'].'";
					$(function() {
						$(".imgcount").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
						$("#facc").bind("change", function() {
							if ($(this).val() == "group") {
								$("#fgroup").slideDown();
							} else {
								$("#fgroup").slideUp();
							}
						});
					});
					</script>';
			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';
			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$IPS[$pl]['name'].': '.$lang['add_page'].'</caption>
						<tr>
							<th></th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="title" id="title" size="70" required="required"> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70"> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70">';
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
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$public.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_updata'].'</td>
							<td><input type="text" name="uppublic" id="uppublic">';
								Calendar('upcal', 'uppublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['input_text'].'</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define('USEWYS', 1);
				$WYSFORM = 'textshort';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('textshort', 7, 70, '', 1, '', '', 1);
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
				$tm->textarea('textmore', 10, 70, '', 1);
			}
			echo '			</td>
						</tr>
						</tbody>';

			$path_img = ($pl == 0) ? '/pages/img/' : '/pages/'.$IPS[$pl]['mod'].'/img/';
			$path_file = ($pl == 0) ? '/pages/file/' : '/pages/'.$IPS[$pl]['mod'].'/file/';

			echo '		<tbody class="tab-2">
						<tr>
							<td>'.$lang['img_extra_hint'].'</td>
							<td class="vm">
								<div class="upad ipad">
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;ims=1\');">'.$lang['filebrowser'].'</a>
									<a class="side-button" href="javascript:$.personalupload(\''.$sess['hash'].'&amp;objdir='.$path_img.'\');">'.$lang['file_review'].'</a>
								</div>
								<div id="image-area"></div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr><th>&nbsp;</th><th class="site">'.$lang['all_image_big'].'</th></tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;field[1]=image_thumb&amp;field[2]=image\')" value="'.$lang['filebrowser'].'" type="button">
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir='.$path_img.'\')" value="'.$lang['file_review'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;field[1]=image&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text"></td>
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
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['all_files'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="file-area"></div>
								<div class="upad">
									<input class="side-button" onclick="javascript:$.addfileinput(\'total-form\',\'file-area\',\''.$path_file.'\')" value="'.$lang['down_add'].'" type="button">
								</div>
							</td>
						</tr>
						<tr><th>&nbsp;</th><th class="site" colspan="2">'.$lang['all_set'].'</th></tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes">'.$lang['included'].' </option>
									<option value="no">'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['page_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="group" class="group" style="display: none;">';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="groups['.$items['gid'].']" value="yes"><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>
						<tr>
							<td>'.$lang['files_access'].'</td>
							<td>
								<select class="group-sel sw165" name="facc" id="facc">
									<option value="all">'.$lang['all_all'].'</option>
									<option value="user">'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group">'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div id="fgroup" class="group" style="display: none;">';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="fgroups['.$items['gid'].']" value="yes"><span>'.$items['title'].'</span>,';
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
							<td colspan="2">';
			if(isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all">
								<input type="hidden" name="facc" value="all">';
			}
			echo '				<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="addsave">
								<input type="hidden" name="pl" value="'.$pl.'">
								<input type="hidden" id="fileid" value="0">
								<input type="hidden" id="imgid" value="0">
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
		 * Добавить страницу (сохранение)
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $title, $subtitle, $cpu, $public, $uppublic, $customs, $descript, $keywords, $textshort, $textmore,
					$image, $image_thumb, $image_align, $image_alt, $act, $acc, $groups, $imp, $files, $facc, $fgroups, $images;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_submint']
				);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$textshort = preparse($textshort, THIS_TRIM);
			$textmore = preparse($textmore, THIS_TRIM);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_thumb =  preparse($image_thumb, THIS_TRIM, 0, 255);
			$image_align = ($image_align == 'left') ? 'left' : 'right';
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($groups)
			)
			{
				$groups = Json::encode($groups);
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$facc == 'group' AND is_array($fgroups)
			)
			{
				$fgroups = Json::encode($fgroups);
			}

			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$facc = ($facc == 'user' OR $facc == 'group') ? 'user' : 'all';
			$act = ($act == 'yes') ? 'yes' : 'no';
			$imp = ($imp == 1) ? 1 : 0;

			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$uppublic = (ReDate($uppublic) > 0) ? ReDate($uppublic) : 0;

			if (is_array($files))
			{
				$f = 1;
				$file = array();
				foreach ($files as $k => $v)
				{
					if (isset($v['path']) AND ! empty($v['path']) AND isset($v['title']) AND ! empty($v['title']))
					{
						$file[$f] = array
										(
											'path'  => $v['path'],
											'title' => str_replace(array("'", '"'), '', $v['title']),
										);
						$f ++;
					}
				}
				$file =  ! empty($file) ? Json::encode($file) : NULL;
			}
			else
			{
				$file = NULL;
			}

			if (is_array($images))
			{
				$c = 1;
				$img = array();
				foreach ($images as $v)
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
				$img =  ! empty($img) ? Json::encode($img) : NULL;
			}
			else
			{
				$img = NULL;
			}

			$mod =  $IPS[$pl]['mod'];
			$pa = new Pages();

			if (preparse($title, THIS_EMPTY) == 1 OR preparse($textshort, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($IPS[$pl]['name'], $lang['add_page'], $lang['pole_add_error']);
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
					$tm->error($IPS[$pl]['name'], $lang['add_page'], $lang['cpu_error_isset'], $title);
					$tm->footer();
				}
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_pages VALUES (
					 NULL,
					 '".$mod."',
					 '".$db->escape(preparse_sp($title))."',
					 '".$db->escape(preparse_sp($subtitle))."',
					 '".$cpu."',
					 '".$public."',
					 '".$uppublic."',
					 '".$db->escape(preparse_sp($customs))."',
					 '".$db->escape(preparse_sp($descript))."',
					 '".$db->escape(preparse_sp($keywords))."',
					 '".$db->escape($textshort)."',
					 '".$db->escape($textmore)."',
					 '".$db->escape($image)."',
					 '".$db->escape($image_thumb)."',
					 '".$image_align."',
					 '".$db->escape(preparse_sp($image_alt))."',
					 '".$act."',
					 '".$acc."',
					 '".$db->escape($groups)."',
					 '".$imp."',
					 '".$db->escape($file)."',
					 '".$facc."',
					 '".$db->escape($fgroups)."',
					 '".$db->escape($img)."'
					 )"
				);

			$paid = $db->insertid();
			$content = array
				(
					'paid'        => $paid,
					'mod'         => $mod,
					'title'       => $db->escape(preparse_sp($title)),
					'subtitle'		=> $db->escape(preparse_sp($subtitle)),
					'cpu'         => $cpu,
					'public'      => $public,
					'uppublic'    => $uppublic,
					'customs'     => $db->escape(preparse_sp($customs)),
					'descript'    => $db->escape(preparse_sp($descript)),
					'keywords'    => $db->escape(preparse_sp($keywords)),
					'textshort'   => $db->escape($textshort),
					'textmore'    => $db->escape($textmore),
					'image'       => $db->escape($image),
					'image_thumb' => $db->escape($image_thumb),
					'image_align' => $image_align,
					'image_alt'   => $db->escape(preparse_sp($image_alt)),
					'act'         => $act,
					'acc'         => $acc,
					'groups'      => $groups,
					'imp'         => $imp,
					'files'       => $file,
					'facc'        => $facc,
					'fgroups'     => $fgroups,
					'images'      => $img
				);

			$pa->modshort($mod);
			$pa->pageid($mod);
			$pa->cachepage($mod, $paid, $content);

			redirect('index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать страницу
		 ---------------------------*/
		if($_REQUEST['dn'] == 'edit')
		{
			global $paid, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$paid = preparse($paid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_pages WHERE paid = '".$paid."'"));

			$public = CalendarFormat($item['public']);
			$uppublic = CalendarFormat(NEWTIME);
			echo '	<script src="'.ADMPATH.'/js/jquery.apanel.tabs.js"></script>
					<script>
					var all_name     = "'.$lang['all_name'].'";
					var all_cpu      = "'.$lang['all_cpu'].'";
					var all_popul    = "'.$lang['all_popul'].'";
					var all_thumb    = "'.$lang['all_image_thumb'].'";
					var all_img      = "'.$lang['all_image'].'";
					var all_images   = "'.$lang['all_image_big'].'";
					var all_align    = "'.$lang['all_align'].'";
					var all_right    = "'.$lang['all_right'].'";
					var all_left     = "'.$lang['all_left'].'";
					var all_center   = "'.$lang['all_center'].'";
					var all_alt      = "'.$lang['all_alt_image'].'";
					var all_copy     = "'.$lang['all_copy'].'";
					var all_delet    = "'.$lang['all_delet'].'";
					var code_paste   = "'.$lang['code_paste'].'";
					var all_file     = "'.$lang['all_file'].'";
					var all_path     = "'.$lang['all_path'].'";
					var page         = "index";
					var ops          = "'.$sess['hash'].'";
					var filebrowser = "'.$lang['filebrowser'].'";
					$(function() {
						$(".imgcount").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
						$("#facc").bind("change", function() {
							if ($(this).val() == "group") {
								$("#fgroup").slideDown();
							} else {
								$("#fgroup").slideUp();
							}
						});
					});
					</script>';
			$tabs = '	<div class="tabs" id="tabs">
							<a href="#" data-tabs=".tab-1">'.$lang['home'].'</a>
							<a href="#" data-tabs=".tab-2" style="display: none;"></a>
							<a href="#" data-tabs="all">'.$lang['all_field'].'</a>
						</div>';
			echo '	<div class="section">
					<form action="index.php" method="post" id="total-form">
					<table class="work">
						<caption>'.$IPS[$pl]['name'].': '.$lang['page_edit'].'</caption>
						<tr>
							<th class="ar site">'.$lang['all_bookmark'].' &nbsp; </th>
							<th>'.$tabs.'</th>
						</tr>
						<tbody class="tab-1">
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="title" id="title" size="70" value="'.preparse_un($item['title']).'" required="required"> <span class="light">&lt;h1&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-2">
						<tr>
							<td>'.$lang['sub_title'].'</td>
							<td><input type="text" name="subtitle" size="70" value="'.preparse_un($item['subtitle']).'"> <span class="light">&lt;h2&gt;</span></td>
						</tr>
						</tbody>
						<tbody class="tab-1">';
			if ($conf['cpu'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_cpu'].'</td>
							<td><input type="text" name="cpu" id="cpu" size="70" value="'.$item['cpu'].'">';
								$tm->outtranslit('title', 'cpu', $lang['cpu_int_hint']);
				echo '	</tr>';
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
						<tr>
							<td>'.$lang['all_data'].'</td>
							<td><input type="text" name="public" id="public" value="'.$public.'">';
								Calendar('cal', 'public');
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['all_updata'].'</td>
							<td><input type="text" name="uppublic" id="uppublic" value="'.$uppublic.'">';
								Calendar('upcal', 'uppublic');
			echo '			</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
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
				$tm->textarea('textshort', 7, 70, $item['textshort'], 1, '', '', 1);
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
				$tm->textarea('textmore', 10, 70, $item['textmore'], 1);
			}
			echo '			</td>
						</tr>
						</tbody>';

			$img = Json::decode($item['images']);
			$class = (is_array($img) AND sizeof($img) > 0) ? ' class="image-area"' : '';
			$path_img = ($pl == 0) ? '/pages/img/' : '/pages/'.$IPS[$pl]['mod'].'/img/';
			$path_file = ($pl == 0) ? '/pages/file/' : '/pages/'.$IPS[$pl]['mod'].'/file/';

			echo '		<tbody class="tab-2">
						<tr>
							<td>'.$lang['img_extra_hint'].'</td>
							<td class="vm">
								<div class="upad ipad">
									<a class="side-button" href="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;ims=1\');">'.$lang['filebrowser'].'</a>&nbsp;
									<a class="side-button" href="javascript:$.personalupload(\''.$sess['hash'].'&amp;objdir='.$path_img.'\');">'.$lang['file_upload'].'</a>
								</div>
								<div id="image-area"'.$class.'>';
			$ic = 0;
			if (is_array($img))
			{
				foreach ($img as $k => $v)
				{
					$ic ++;
					echo '			<div id="imginput'.$ic.'">
										<table class="work">
											<tr>
												<td>';
					if ( ! empty($v['image'])) {
						echo '						<img class="sw50" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_thumb'].'" />';
					} else {
						echo '						<img class="sw70" src="'.WORKURL.'/'.$v['thumb'].'" alt="'.$lang['all_image_big'].'" />';
					}
					echo '							<input type="hidden" name="images['.$ic.'][image_thumb]" value="'.$v['thumb'].'">';
					if ( ! empty($v['image'])) {
						echo '						&nbsp;&nbsp;<img class="sw70" src="'.WORKURL.'/'.$v['image'].'" alt="'.$lang['all_image'].'" />
													<input type="hidden" name="images['.$ic.'][image]" value="'.$v['image'].'">';
					}
					echo '						</td>
												<td>
													<a class="but fr" href="javascript:$.filebrowserimsremove(\''.$ic.'\');" title="'.$lang['all_delet'].'">x</a>
													<p><input type="text" size="3" value="{img'.$ic.'}" class="imgcount" readonly="readonly" title="'.$lang['all_copy'].'"> <cite>'.$lang['code_paste'].'</cite></p>
													<p class="label">'.$lang['all_align'].'&nbsp; &nbsp; &nbsp; &nbsp;'.$lang['all_alt_image'].'</p>
													<p>
														<select name="images['.$ic.'][image_align]">
															<option value="left"'.(($v['align'] == 'left') ? ' selected' : '').'>'.$lang['all_left'].'</option>
															<option value="right"'.(($v['align'] == 'right') ? ' selected' : '').'>'.$lang['all_right'].'</option>
															<option value="center"'.(($v['align'] == 'center') ? ' selected' : '').'>'.$lang['all_center'].'</option>
														</select>&nbsp; &nbsp; &nbsp;
														<input type="text" name="images['.$ic.'][image_alt]" size="25" value="'.$v['alt'].'">
													</p>
												</td>
											</tr>
										</table>
									</div><div class="upad"></div>';
				}
			}
			echo '				</div>
							</td>
						</tr>
						</tbody>
						<tbody class="tab-1">
						<tr><th>&nbsp;</th><th class="site">'.$lang['all_image_big'].'</th></tr>
						<tr>
							<td>'.$lang['all_image_thumb'].'</td>
							<td>
								<input name="image_thumb" id="image_thumb" size="70" type="text" value="'.$item['image_thumb'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;field[1]=image_thumb&amp;field[2]=image\')" value="'.$lang['filebrowser'].'" type="button">
								<input class="side-button" onclick="javascript:$.quickupload(\''.$sess['hash'].'&amp;objdir='.$path_img.'\')" value="'.$lang['file_review'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="70" type="text" value="'.$item['image'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_img.'\',\'&amp;field[1]=image&amp;field[2]=image_thumb\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input name="image_alt" id="image_alt" size="70" type="text" value="'.$item['image_alt'].'"></td>
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
						<tr>
							<th>&nbsp;</th><th class="site">&nbsp;'.$lang['all_files'].'</th>
						</tr>
						<tr>
							<td class="vm">'.$lang['all_submint'].'&nbsp; &#8260; &nbsp;'.$lang['all_delet'].'</td>
							<td>
								<div id="file-area">';
			$fp = Json::decode($item['files']);
			$f = 1;
			if (is_array($fp) AND sizeof($fp) > 0)
			{
				foreach ($fp as $k => $v)
				{
					echo '			<div class="section tag" id="file-'.$f.'">
										<table class="work">
											<tr>
												<td>'.$lang['all_path'].'</td>
												<td>
													<input name="files['.$f.'][path]" id="files'.$f.'" size="50" type="text" value="'.$v['path'].'" required="required">
													<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\''.$path_file.'\',\'&amp;field[1]=files'.$f.'\')" value="'.$lang['filebrowser'].'" type="button">';
					echo '							<a class="but fr" href="javascript:$.removetaginput(\'total-form\',\'file-area\',\'file-'.$f.'\');">&#215;</a>';
					echo '						</td>
											</tr>
											<tr>
												<td>'.$lang['all_name'].'</td>
												<td><input name="files['.$f.'][title]" size="50" type="text" value="'.$v['title'].'" required="required"></td>
											</tr>
										</table>
									</div><div class="upad"></div>';
					$f ++;
				}
			}
			echo '				</div>
								<div class="upad">
									<input class="side-button" onclick="javascript:$.addfileinput(\'total-form\',\'file-area\',\''.$path_file.'\')" value="'.$lang['down_add'].'" type="button">
								</div>
							</td>
						</tr>
						<tr><th>&nbsp;</th><th class="site">'.$lang['all_set'].'</th></tr>
						<tr>
							<td>'.$lang['all_status'].'</td>
							<td>
								<select name="act" class="sw165">
									<option value="yes"'.(($item['act'] == 'yes') ? ' selected' : '').'>'.$lang['included'].' </option>
									<option value="no"'.(($item['act'] == 'no')  ? ' selected' : '').'>'.$lang['not_included'].'</option>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['page_access'].'</td>
							<td>
								<select class="group-sel sw165" name="acc" id="acc">
									<option value="all"'.(($item['acc'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['acc'] == 'user' AND empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['acc'] == 'user' AND ! empty($item['groups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['acc'] == 'all' OR $item['acc'] == 'user' AND empty($item['groups'])) ? ' style="display: none;"' : '').'>';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['groups']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs)) {
						$group_out.= '<input type="checkbox" name="groups['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').'><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>
						<tr>
							<td>'.$lang['files_access'].'</td>
							<td>
								<select class="group-sel sw165" name="facc" id="facc">
									<option value="all"'.(($item['facc'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['facc'] == 'user' AND empty($item['fgroups']))  ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['facc'] == 'user' AND ! empty($item['fgroups']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="fgroup"'.(($item['facc'] == 'all' OR $item['facc'] == 'user' AND empty($item['fgroups'])) ? ' style="display: none;"' : '').'>';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$finqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$fgroup = Json::decode($item['fgroups']);
					$fgroup_out = '';
					while ($fitems = $db->fetchrow($finqs)) {
						$fgroup_out.= '<input type="checkbox" name="fgroups['.$fitems['gid'].']" value="yes"'.(isset($fgroup[$fitems['gid']]) ? ' checked' : '').'><span>'.$fitems['title'].'</span>,';
					}
					echo chop($fgroup_out, ',');
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
							<td colspan="2">';
			if(isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="acc" value="all">
								<input type="hidden" name="facc" value="all">';
			}
			echo '				<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="editsave">
								<input type="hidden" name="pl" value="'.$pl.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="id" value="'.$paid.'">
								<input type="hidden" id="fileid" value="'.$f.'">
								<input type="hidden" id="imgid" value="'.$ic.'">
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
		 * Редактировать страницу (сохранение)
		 ---------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $id, $title, $subtitle, $cpu, $public, $uppublic, $customs, $descript, $keywords, $textshort, $textmore,
					$image, $image_thumb, $image_align, $image_alt, $act, $acc, $groups, $imp, $files, $facc, $fgroups, $images, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$title = preparse($title, THIS_TRIM, 0, 255);
			$subtitle = preparse($subtitle, THIS_TRIM, 0, 255);
			$cpu = preparse($cpu, THIS_TRIM, 0, 255);
			$textshort = preparse($textshort, THIS_TRIM);
			$textmore = preparse($textmore, THIS_TRIM);
			$customs = preparse($customs, THIS_TRIM);
			$descript = preparse($descript, THIS_TRIM);
			$keywords = preparse($keywords, THIS_TRIM);
			$image = preparse($image, THIS_TRIM, 0, 255);
			$image_thumb = preparse($image_thumb, THIS_TRIM, 0, 255);
			$image_align = ($image_align == 'left') ? 'left' : 'right';
			$image_alt = preparse($image_alt, THIS_TRIM, 0, 255);

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($groups)
			)
			{
				$groups = Json::encode($groups);
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$acc == 'group' AND is_array($fgroups)
			)
			{
				$fgroups = Json::encode($fgroups);
			}

			$acc = ($acc == 'user' OR $acc == 'group') ? 'user' : 'all';
			$facc = ($facc == 'user' OR $facc == 'group') ? 'user' : 'all';

			$act = ($act == 'yes') ? 'yes' : 'no';
			$imp = ($imp == 1) ? 1 : 0;
			$public = (empty($public)) ? NEWTIME : ReDate($public);
			$uppublic = (ReDate($uppublic) > 0) ? ReDate($uppublic) : 0;

			if (is_array($files))
			{
				$f = 1;
				$file = array();
				foreach ($files as $k => $v)
				{
					if (isset($v['path']) AND ! empty($v['path']) AND isset($v['title']) AND ! empty($v['title']))
					{
						$file[$f] = array
										(
											'path'  => $v['path'],
											'title' => str_replace(array("'", '"'), '', $v['title']),
										);
						$f ++;
					}
				}
				$file =  ! empty($file) ? Json::encode($file) : NULL;
			}
			else
			{
				$file = NULL;
			}

			if (is_array($images))
			{
				$c = 1;
				$img = array();
				foreach ($images as $v)
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
				$img =  ! empty($img) ? Json::encode($img) : NULL;
			}
			else
			{
				$img = NULL;
			}

			$mod =  $IPS[$pl]['mod'];
			$pa = new Pages();

			$item = $db->fetchrow($db->query("SELECT cpu, title FROM ".$basepref."_pages WHERE paid = '".$id."'"));

			if (
				preparse($title, THIS_EMPTY) == 1 OR
				preparse($textshort, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($IPS[$pl]['name'], $lang['page_edit'], $lang['pole_add_error']);
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
								"SELECT title, cpu FROM ".$basepref."_pages
								 WHERE (title = '".$db->escape($title)."' OR cpu = '".$db->escape($cpu)."')
								 AND paid <> '".$id."'"
							);

				if ($db->numrows($inqure) > 0)
				{
					$tm->header();
					$tm->error($IPS[$pl]['name'], $lang['page_edit'], $lang['cpu_error_isset'], $item['title']);
					$tm->footer();
				}
			}

			$db->query
				(
					"UPDATE ".$basepref."_pages SET
					 title       = '".$db->escape(preparse_sp($title))."',
					 subtitle    = '".$db->escape(preparse_sp($subtitle))."',
					 cpu         = '".$cpu."',
					 public      = '".$public."',
					 uppublic    = '".$uppublic."',
					 customs     = '".$db->escape(preparse_sp($customs))."',
					 descript    = '".$db->escape(preparse_sp($descript))."',
					 keywords    = '".$db->escape(preparse_sp($keywords))."',
					 textshort   = '".$db->escape($textshort)."',
					 textmore    = '".$db->escape($textmore)."',
					 image       = '".$db->escape($image)."',
					 image_thumb = '".$db->escape($image_thumb)."',
					 image_align = '".$image_align."',
					 image_alt   = '".$db->escape(preparse_sp($image_alt))."',
					 act         = '".$act."',
					 acc         = '".$acc."',
					 groups      = '".$db->escape($groups)."',
					 imp         = '".$imp."',
					 files       = '".$db->escape($file)."',
					 facc        = '".$facc."',
					 fgroups     = '".$db->escape($fgroups)."',
					 images      = '".$db->escape($img)."'
					 WHERE paid  = '".$id."'"
				);

			$content = array
				(
					'paid'        => $id,
					'mod'         => $mod,
					'title'       => $db->escape(preparse_sp($title)),
					'subtitle'		=> $db->escape(preparse_sp($subtitle)),
					'cpu'         => $cpu,
					'public'      => $public,
					'uppublic'    => $uppublic,
					'customs'     => $db->escape(preparse_sp($customs)),
					'descript'    => $db->escape(preparse_sp($descript)),
					'keywords'    => $db->escape(preparse_sp($keywords)),
					'textshort'   => $db->escape($textshort),
					'textmore'    => $db->escape($textmore),
					'image'       => $db->escape($image),
					'image_thumb' => $db->escape($image_thumb),
					'image_align' => $image_align,
					'image_alt'   => $db->escape(preparse_sp($image_alt)),
					'act'         => $act,
					'acc'         => $acc,
					'groups'      => $groups,
					'imp'         => $imp,
					'files'       => $file,
					'facc'        => $facc,
					'fgroups'     => $fgroups,
					'images'      => $img
				);

			$pa->modshort($mod);
			$pa->pageid($mod);
			$pa->cachepage($mod, $id, $content);

			$redir = 'index.php?dn=list&amp;pl='.$pl;
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= '&amp;ops='.$sess['hash'];

			redirect($redir);
		}

		/**
		 * Состояние, активность страницы
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'act')
		{
			global $act, $paid, $p, $nu;

			$act = preparse($act, THIS_TRIM);
			$id = preparse($paid, THIS_INT);

			$mod =  $IPS[$pl]['mod'];
			$pa = new Pages();

			if ($act)
			{
				$db->query("UPDATE ".$basepref."_pages SET act = '".$act."' WHERE paid = '".$id."'");
			}

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_pages WHERE paid = '".$id."'"));
			$content = array
			(
				'paid'        => $id,
				'mod'         => $mod,
				'title'       => $db->escape(preparse_sp($item['title'])),
				'subtitle'		=> $db->escape(preparse_sp($item['subtitle'])),
				'cpu'         => $item['cpu'],
				'public'      => $item['public'],
				'uppublic'    => $item['uppublic'],
				'customs'     => $db->escape(preparse_sp($item['customs'])),
				'descript'    => $db->escape(preparse_sp($item['descript'])),
				'keywords'    => $db->escape(preparse_sp($item['keywords'])),
				'textshort'   => $db->escape($item['textshort']),
				'textmore'    => $db->escape($item['textmore']),
				'image'       => $db->escape($item['image']),
				'image_thumb' => $db->escape($item['image_thumb']),
				'image_align' => $item['image_align'],
				'image_alt'   => $db->escape(preparse_sp($item['image_alt'])),
				'act'         => $item['act'],
				'acc'         => $item['acc'],
				'groups'      => $item['groups'],
				'imp'         => $item['imp'],
				'files'       => $item['files'],
				'facc'        => $item['facc'],
				'fgroups'     => $item['fgroups'],
				'images'      => $item['images']
			);

			$pa->modshort($mod);
			$pa->cachepage($mod, $id, $content);

			$redir = 'index.php?dn=list&amp;pl='.$pl;
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= '&amp;ops='.$sess['hash'];

			redirect($redir);
		}

		/**
		 * Удалить страницу
		 ---------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $paid, $pl, $ok, $p, $nu;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['all_delet']
				);

			$paid = preparse($paid, THIS_INT);
			$mod =  $IPS[$pl]['mod'];
			$pa = new Pages();

			if ($ok == 'yes')
			{
				$dir_path = $pa->realpath_mod($mod);

				$db->query("DELETE FROM ".$basepref."_pages WHERE paid = '".$paid."'");
				$db->increment('pages');

				unlink($dir_path.'/'.$mod.'.'.$paid.'.php');
				$pa->pageid($mod);
				$pa->modshort($mod);

				$redir = 'index.php?dn=list&amp;pl='.$pl;
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= '&amp;ops='.$sess['hash'];

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_pages WHERE paid='".$paid."'"));

				$yes = 'index.php?dn=del&amp;pl='.$pl.'&amp;paid='.$paid.'&amp;ok=yes&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;pl='.$pl.'&amp;p='.$p.'&amp;nu='.$nu.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($IPS[$pl]['name'], $lang['page_del'], $yes, $not, preparse_un($item['title']));
				$tm->footer();
			}
		}

		/**
		 * Массовое удаление страниц
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'arrdel')
		{
			global $delarray, $pl, $p, $nu, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					$lang['array_del']
				);

			$mod =  $IPS[$pl]['mod'];
			$pa = new Pages();

			if (preparse($delarray, THIS_ARRAY) == 1)
			{
				if ($ok == 'yes')
				{
					if (preparse($delarray, THIS_ARRAY) == 1)
					{
						foreach ($delarray as $paid => $v)
						{
							$paid = preparse($paid, THIS_INT);

							$dir_path = $pa->realpath_mod($mod);

							$db->query("DELETE FROM ".$basepref."_pages WHERE paid = '".$paid."'");
							$db->increment('pages');

							unlink($dir_path.'/'.$mod.'.'.$paid.'.php');
							$pa->pageid($mod);
							$pa->modshort($mod);
						}
					}
					$redir = 'index.php?dn=list&amp;pl='.$pl;
					$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
					$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
					$redir.= '&amp;ops='.$sess['hash'];
					redirect($redir);
				}
				else
				{
					$temparray = $delarray;
					$count = count($temparray);
					$hidden = '';
					foreach ($delarray as $key => $id) {
						$hidden.= '<input type="hidden" name="delarray['.$key.']" value="yes">';
					}
					$tm->header();
					echo '	<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$IPS[$pl]['name'].': '.$lang['all_delet'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										<input type="hidden" name="dn" value="arrdel">
										<input type="hidden" name="ok" value="yes">
										<input type="hidden" name="pl" value="'.$pl.'">
										<input type="hidden" name="p" value="'.$p.'">
										<input type="hidden" name="nu" value="'.$nu.'">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input class="side-button" value="'.$lang['all_go'].'" type="submit">
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
									</td>
								</tr>
							</table>
							</form>';
					$tm->footer();
				}
			}

			$redir = 'index.php?dn=list&amp;pl='.$pl;
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= '&amp;ops='.$sess['hash'];
			redirect($redir);
		}

		/**
		 * Платформы
		 -------------*/
		if ($_REQUEST['dn'] == 'mod')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['platforms_pages']
				);

			$tm->header();

			echo '	<div class="section">
					<table class="work">
						<caption>'.$modname[PERMISS].': '.$lang['all_platform'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_name'].'</th>
							<th>'.$lang['all_mod'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			foreach($IPS as $key => $val)
			{
				if ($key == 0)
				{
					echo ' <tr class="list">
								<td class="first"><span>*</span> '.$val['name'].'</td>
								<td>'.$val['mod'].'</td>
								<td><img alt="'.$lang['def_value'].'" src="'.ADMPATH.'/template/images/totalinfo.gif" style="padding:1px;"></td>';
				}
				else
				{
					echo ' <tr class="list">
								<td>'.$val['name'].'</td>
								<td>'.$val['mod'].'</td>
								<td class="gov">
									<a href="index.php?dn=editmod&amp;mod='.$val['mod'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_set'].'" /></a>
									<a href="index.php?dn=delmod&amp;pl='.$pl.'&amp;km='.$key.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			echo '	</table>
					</div>
					<div class="sline"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_platform'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="name" type="text" size="50" required="required"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['one_mod'].'</td>
							<td><input name="mod" type="text" size="50" required="required">';
								$tm->outhint($lang['help_mod']);
            echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="addmod">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="pl" value="'.$pl.'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить мод (сохранение)
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'addmod')
		{
			global $name, $mod, $pl;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['platforms_pages']
				);

			$name = preparse($name, THIS_TRIM, 0, 255);

			function file_read($files)
			{
				$read = fopen($files, "r");
				$contents = fread($read, filesize($files));
				fclose($read);
				return $contents;
			}

			if (
				preparse($name, THIS_EMPTY) == 1 OR
				preparse($mod, THIS_EMPTY) == 1 OR
				strlen($mod) > 32 /*OR ! ctype_alnum($mod)*/
			) {
				$tm->header();
				$tm->error($lang['platforms_pages'], $lang['all_submint'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$inq = $db->query("SELECT id FROM ".$basepref."_mods WHERE file = '".$db->escape($mod)."'");
				if ($db->numrows($inq) > 0)
				{
					$tm->header();
					$tm->error($lang['platforms_pages'], $lang['all_submint'], $lang['mod_error_isset']);
					$tm->footer();
				}

				$pa = new Pages();
				$pa->page_dir($mod);

				$parent = $db->fetchrow($db->query("SELECT id FROM ".$basepref."_mods WHERE file = 'pages'"));
				$maxposit = $db->fetchrow($db->query("SELECT MAX(posit) FROM ".$basepref."_mods"));
				$posit = $maxposit[0] + 1;

				$modlabel = null;
				$scheme = ADMDIR.'/mod/pages/install/mod.scheme.php';
				if (file_exists($scheme))
				{
					include_once($scheme);
					$modlabel = Json::encode($label);
				}

				$db->query
					(
						"INSERT INTO ".$basepref."_mods VALUES (
						 NULL,
						 '".$db->escape($mod)."',
						 '".$db->escape(preparse_sp($name))."',
						 '',
						 '',
						 '',
						 '',
						 '".$conf['site_temp']."',
						 '".$posit."',
						 '".$modlabel."',
						 'yes',
						 'no',
						 '".$parent['id']."',
						 'no',
						 'no',
						 '0'
						 )"
					);

				$paid = $db->insertid();

				$new = array(
					'mod'  => $mod,
					'name' => $name
				);

				$IPS[$paid] = $new;
				$ins = Json::encode($IPS);
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mods'");

				// Добавляем настройки
				$set_mod = ADMDIR.'/mod/pages/install/sql/setting.sql';
				if (file_exists($set_mod))
				{
					$set_array = explode(PHP_EOL, file_read($set_mod));
					array_pop($set_array);

					$db->query("DELETE FROM ".$basepref."_settings WHERE setopt = '".$mod."'");
					$db->increment('settings');

					foreach ($set_array as $insert)
					{
						if (trim($insert) != "") {
							$string = str_replace(array('{pref}', '{mod}'), array($basepref, $mod), $insert);
							$inq = $db->query($string, 0);
						}
					}
				}

				$cache->cachesave(1);
				redirect('index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Редактирование клона
		 -----------------------*/
		if ($_REQUEST['dn'] == 'editmod')
		{
			global $mod;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['platforms_pages'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$mod = preparse($mod, THIS_TRIM, 0, 255);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE file = '".$mod."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['edit_platform'].': '.$item['name'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="name" size="70" value="'.preparse_un($item['name']).'" required="required" /></td>
						</tr>
						<tr>
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
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('map', 5, 50, $item['map'], 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="mod" value="'.$mod.'">
								<input type="hidden" name="dn" value="editmodsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].': '.$item['name'].'</caption>';
			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".$mod."'");
			while ($set = $db->fetchassoc($inq))
			{
					echo '	<tr>
								<td class="first">'.(($set['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$set['setlang']])) ? $lang[$set['setlang']] : $set['setlang']).'</td>
								<td>';
					echo eval($set['setcode']);
					echo '		</td>
							</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="mod" value="'.$mod.'">
								<input type="hidden" name="dn" value="modsetsave">
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
		 * Редактирования клона (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'editmodsave')
		{
			global $mod, $name, $custom, $keywords, $descript, $map, $label;

			$mod = preparse($mod, THIS_TRIM, 0, 255);
			if ($mod)
			{
				$db->query
					(
						"UPDATE ".$basepref."_mods SET
						 name     = '".$db->escape(preparse_sp($name))."',
						 custom   = '".$db->escape(preparse_sp($custom))."',
						 keywords = '".$db->escape(preparse_sp($keywords))."',
						 descript = '".$db->escape(preparse_sp($descript))."',
						 map      = '".$db->escape($map)."'
						 WHERE file = '".$mod."'
						");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mod&amp;pl=0&amp;ops='.$sess['hash']);
		}

		/**
		 * Настройки клона (сохранение)
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'modsetsave')
		{
			global $mod, $pl;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['platforms_pages'].'</a>',
					$lang['all_edit']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".$mod."'");
			$mods = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE file = '".$mod."'"));

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['all_set'], $mods['name'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mod&amp;pl=0&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить мод
		 ---------------*/
		if ($_REQUEST['dn'] == 'delmod')
		{
			global $km, $pl, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$IPS[$pl]['name'].'</a>',
					'<a href="index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'].'">'.$lang['platforms_pages'].'</a>',
					$lang['all_delet']
				);

			$key = preparse($km, THIS_INT);
			$mod = $IPS[$key]['mod'];

			if ($ok == 'yes')
			{
				$pa = new Pages();

				$db->query("DELETE FROM ".$basepref."_mods WHERE file = '".$mod."'");
				$db->increment('mods');

				$db->query("DELETE FROM ".$basepref."_pages WHERE mods = '".$mod."'");
				$db->increment('pages');

				$db->query("DELETE FROM ".$basepref."_settings WHERE setopt = '".$mod."'");
				$db->increment('settings');

				$pa->undir(WORKDIR.'/cache/pages/'.$mod);
				$pa->undir(WORKDIR.'/up/pages/'.$mod);

				unset($IPS[$key]);
				$new = array_values($IPS);
				$ins = Json::encode($new);

				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mods'");

				$cache->cachesave(1);
				redirect('index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=delmod&amp;pl='.$pl.'&amp;km='.$key.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_platform'], $IPS[$key]['name'], $yes, $not, $lang['mod_del_pages']);
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
