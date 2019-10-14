<?php
/**
 * File:        /admin/nod/block/index.php
 *
 * Управление системой, Управление блоками
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
			$lang['opt_manage_block']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Метки
		 */
		$legaltodo = array
			(
				'index', 'blocksave', 'blockajaxup', 'blockadd', 'blockedit', 'blockact', 'blockeditsave', 'blockdel',
				'blockposit', 'blockpositup', 'blockpositsave', 'blockpositdel',
			);

		/**
		 * Проверка меток
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link = '<a'.cho('index, blockedit, blockdel').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_block'].'</a>'
					.'<a'.cho('blockposit').' href="index.php?dn=blockposit&amp;ops='.$sess['hash'].'">'.$lang['block_posit'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Управление блоками
		 -----------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $IPS;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['opt_manage_block'],
					$lang['all_block']
				);

			$tm->header();

			$sort = $side = array();

			$block = $db->query("SELECT * FROM ".$basepref."_block ORDER BY block_posit");
			while ($item = $db->fetchassoc($block))
			{
				$sort[$item['block_side']][$item['blockid']] = $item;
			}
			//asort($sort);

			$sides = $db->query("SELECT * FROM ".$basepref."_block_posit ORDER BY pposit");
			while ($item = $db->fetchassoc($sides))
			{
				$side[$item['positcode']][$item['positname']] = $item;
			}
			//asort($side);

			echo '	<script src="'.ADMPATH.'/js/jquery.tablednd.js"></script>';
			echo '	<script>
					$(function(){
						$("#tablesort tr").hover(function() {
							$(this).addClass("trdragact");
						}, function() {
							$(this).removeClass("trdragact");
						});
						$("#tablesort").tableDnD( {
							onDragClass:"trdragact",
							onDropStyle:"trdragact",
							onDrop:function(table,row) {
								$.ajax( {
									type:"POST",
									url:"index.php?dn=blockajaxup&ops='.$sess['hash'].'",
									data:$.tableDnD.serialize(),
									success:function(data,textStatus){
										//alert(data);
										$("#tablesort").tableDnDUpdate();
										var i = 0;
										$("tr.trdrag .dragcount").each(function() {
											this.value = i;
											i ++;
										});
									}
								});
							}
						});
						$("#block_access").bind("change", function () {
							if ($(this).val() == "user") {
								$("#block_group").slideDown();
							} else {
								$("#block_group").slideUp();
							}
						});
					});
					</script>';
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="tablesort" class="work">
						<caption>'.$lang['opt_manage_block'].'</caption>
						<tr class="nodrag nodrop">
							<th>ID</th>
							<th>'.$lang['all_name'].'</th>
							<th>'.$lang['all_file'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			$f = 0;
			foreach ($side as $k => $v)
			{
				if($k)
				{
					foreach ($v as $mv)
					{
						echo '	<tr id="'.$k.'" class="list nodrag'.(($f == 0) ? ' nodrop' : '').'">
									<td class="sw10" style="background-color: #f3f4f6 !important;"></td>
									<td class="al" colspan="4" style="background-color: #f3f4f6 !important; padding: 11px 10px;">
										<strong class="site bold">'.$mv['positname'].'</strong>&nbsp; &#8226; &nbsp;<strong class="bold">{'.$mv['positcode'].'}</strong>';
						echo '		</td>
								</tr>';
					}
				}
				else
				{
					echo '		<tr class="nodrag">
									<th colspan="5"> — — — </th>
								</tr>';
				}
				$f ++;
				if (isset($sort[$k]))
				{
					foreach ($sort[$k] as $nk => $nv)
					{
						$style = ($nv['block_active'] == 'no') ? 'no-active' : 'tddrag';
						$file = !empty($nv['block_file']) ? $nv['block_file'] : $lang['text_block'];
						echo '	<tr id="'.$nv['blockid'].'" class="list trdrag">
									<td class="'.$style.' vm sw10">
										<div>'.$nv['blockid'].'</div>
									</td>
									<td class="'.$style.' nodrag nodrop">
										<input type="text" style="width: 96%;" value="'.($nv['block_name']).'" name="name['.$nv['blockid'].']" size="20" maxlength="80">
									</td>
									<td class="'.$style.' vm" title="Drag Drop &#8597;">
										<div>'.$file.'</div>
									</td>
									<td class="'.$style.' vm" title="Drag Drop &#8597;">
										<div><input class="dragcount" type="text" value="'.$nv['block_posit'].'" name="posit['.$nv['blockid'].']" size="3" maxlength="3"></div>
									</td>
									<td class="nodrag nodrop '.$style.' gov vm">
										<a href="index.php?dn=blockedit&amp;blockid='.$nv['blockid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_edit'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png"></a>';
						if ($nv['block_active'] == 'yes') {
							echo '		<a href="index.php?dn=blockact&amp;act=no&amp;blockid='.$nv['blockid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['not_included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
						} else {
							echo '		<a class="inact" href="index.php?dn=blockact&amp;act=yes&amp;blockid='.$nv['blockid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['included'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png"></a>';
						}
						echo '			<a href="index.php?dn=blockdel&amp;blockid='.$nv['blockid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png"></a>
									</td>
								</tr>';
					}
				}
			}
			echo '		<tr class="tfoot nodrag nodrop">
							<td colspan="5">
								<input type="hidden" name="dn" value="blocksave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>';
			$blockdir = opendir(WORKDIR.'/block/');
			$listing = array();
			while ($name = readdir($blockdir))
			{
				if ( substr($name, 0, 2) == 'b-') {
					$listing[]= $name;
				}
			}
			closedir($blockdir);
			sort($listing);
			define('SETTING', 1);
			echo '	<div class="section">
					<form action="index.php" method="post" id="blockform">
					<table class="work">
						<caption>'.$lang['add_block'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="block_name" size="50" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['choice_block'].'</td>
							<td>
								<select name="block_file" class="sw210">
									<option value="" selected>'.$lang['text_block'].'</option>';
			$bs = $sname = array();
			for ($i = 0; $i < sizeof($listing); $i ++)
			{
				if ($listing[$i])
				{
					$br = str_replace(array('b-', '.php'), '', $listing[$i]);
					$bs[$br] = include(WORKDIR.'/block/'.$listing[$i]);
					if (isset($bs[$br]['blockname']) AND ! empty($bs[$br]['blockname'])) {
						$sname[$listing[$i]] = $bs[$br]['blockname'];
					} else {
						$sname[$listing[$i]] = $br;
					}
				}
			}
			natsort($sname);
			foreach ($sname as $k => $vname) {
				echo '				<option value="'.$k.'">'.$vname.'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_posit'].'</td>
							<td>
								<select name="block_side" class="sw210">';
			$inqs = $db->query("SELECT * FROM ".$basepref."_block_posit ORDER BY pposit");
			while($items = $db->fetchrow($inqs)){
				echo '				<option value="'.$items['positid'].'">'.$items['positname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_temp'].'</td>
							<td>
								<select name="block_temp" class="sw210">';
			$tempdir = opendir(WORKDIR.'/template/'.$conf['site_temp'].'/block/');
			echo '					<optgroup label="'.$lang['site_temp'].' '.$conf['site_temp'].'" class="optgroup">';
			while ($name = readdir($tempdir))
			{
				if (substr($name, -4) == '.tpl')
				{
					$tpl = str_replace('.tpl', '', $name);
					$sel[$tpl] = $tpl;
				}
			}
			natsort($sel);
			foreach ($sel as $k => $vsel) {
				echo '					<option value="'.$k.'">'.$vsel.'</option>';
			}
			closedir($tempdir);
			echo '					</optgroup>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
							<td>
								<select class="group-sel sw210" name="block_access" id="acc">
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
						$group_out.= '<input type="checkbox" name="block_group['.$items['gid'].']" value="yes"><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['block_mods'].'</td>
							<td>
								<div class="but" style="margin-bottom: 7px;" onclick="$.allselect(\'blockform\');"> '.$lang['all_mark'].'</div>';
			$inq = $db->query("SELECT * FROM ".$basepref."_mods WHERE active='yes'");
			echo '				<table class="work">';
			while ($item = $db->fetchrow($inq))
			{
				$table = NULL;
				$label = Json::decode($item['label']);
				foreach ($IPS as $k => $v)
				{
					if ($v['mod'] == $item['file'])
					{
						$table = $v['mod'];
					}
				}
				echo '				<tr>
										<td class="al bl">
											'.((is_array($label)) ? '<img onclick="$(\'#'.$item['file'].'_toggle\').toggle(\'fast\');" class="image-toggle" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png" alt="" />' : '').'
											<input type="checkbox" id="'.$item['file'].'checkbox" name="block_mods['.$item['file'].']" onclick="$.modcheck(\''.$item['file'].'\');" value="yes" checked="checked"> &nbsp;';
				echo '						<span onclick="$(\'#'.$item['file'].'_toggle\').toggle(\'fast\');" class="'.((is_array($label)) ? 'site strong pointer' : 'gray').'">'.$item['name'].'</span>';
				echo '						<div id="'.$item['file'].'_toggle" style="display: none">';
				if (is_array($label))
				{
					foreach ($label as $k => $v)
					{
						if ( ! ($item['file'] == 'pages' AND $k == 'index'))
						{
						echo '					<div class="blocking">
													<span>'.$lang['all_file'].': <strong>'.$k.'</strong></span>';
						if (is_array($v)) {
							foreach ($v as $sk => $sv) {
								echo '				<p><input type="checkbox" name="block_mods['.$item['file'].']['.$k.']['.$sk.']" value="yes" checked> '.$sk.'</p>';
							}
						}
						echo '					</div>';
						}
					}
					// IDs of categories
					if (method_exists($db, 'tables') AND $db->tables($item['file']."_cat"))
					{
						echo '						<div style="clear:both"></div>
													<div class="blockinfo">
														<input type="text" name="block_cats['.$item['file'].'][cat]" size="12"> ID '.$lang['all_cats'].'
														&nbsp; &nbsp; <input type="checkbox" name="block_cats['.$item['file'].'][exc]" value="1"> '.$lang['exception'].' ';
														$tm->outhint($lang['block_cat_help']);
						echo '						</div>';
					}
				}
				if (isset($table{0}))
				{
					$painq = $db->query("SELECT * FROM ".$basepref."_pages WHERE mods = '".$table."'");
					echo '						<dl class="blockinfo">
													<dt>'.$lang['all_page'].':</dt>
													<dd>';
					while ($page = $db->fetchrow($painq)) {
						echo '							<p><input type="checkbox" name="block_mods['.$item['file'].']['.$page['cpu'].']" value="yes" checked>'.$page['title'].'</p>';
					}
					echo '							</dd>
												</dl>';
				}
				echo '						</div>
										</td>
									</tr>';
			}
			echo '				</table>
							</td>
						</tr>
						<tr>
							<td>';
								$tm->outhint($lang['block_hint_add']);
			echo '				'.$lang['all_text'].'
							</td>
							<td class="usewys">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS",1);
				$form_more = 'block_cont';
				$WYSFORM = 'block_cont';
				$WYSVALUE = '';
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('block_cont', 5, 50, '', 1);
			}
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="block_access" value="all">';
			}
			echo '				<input type="hidden" name="dn" value="blockadd">
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
		 * Управление блоками (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'blocksave')
		{
			global $name, $posit;

			foreach ($name as $blockid => $block_name)
			{
				if ($block_name)
				{
					$blockid = preparse($blockid, THIS_INT);
					$db->query("UPDATE ".$basepref."_block SET block_name = '".$db->escape(preparse_sp($block_name))."' WHERE blockid = '".$blockid."'");
				}
			}

			foreach ($posit as $blockid => $block_posit)
			{
				$blockid = preparse($blockid, THIS_INT);
				$block_posit = preparse($block_posit, THIS_INT);
				$db->query("UPDATE ".$basepref."_block SET block_posit = '".$block_posit."' WHERE blockid = '".$blockid."'");
			}

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Ajax для Drag Drop (перетаскивание блоков)
		 ----------------------------------------------*/
		if ($_REQUEST['dn'] == 'blockajaxup')
		{
			global $tablesort;

			if (is_array($tablesort))
			{
				$sort = $sortid = array();

				$block = $db->query("SELECT * FROM ".$basepref."_block_posit");
				while ($item = $db->fetchrow($block))
				{
					$sort[$item['positcode']] = $item['positcode'];
					$sortid[$item['positcode']] = $item['positid'];
				}

				$i = $s = 0;
				$new = array();

				foreach ($tablesort as $k => $v)
				{
					if (isset($sort[$v])) {
						$s = $sort[$v];
					} else {
						$new[$s][$i] = $v;
						$i ++;
					}
				}

				$p = 0;
				foreach ($new as $k => $v)
				{
					foreach ($v as $sk => $sv)
					{
						if ($sv > 0)
						{
							if (isset($sort[$k]) AND isset($sortid[$k]) AND $sortid[$k] > 0) {
								$sql = "positid = '".intval($sortid[$k])."',block_side = '".$db->escape($sort[$k])."',";
							} else {
								$sql = '';
							}
							$db->query("UPDATE ".$basepref."_block SET ".$sql."block_posit = '".intval($sk)."' WHERE blockid = '".intval($sv)."'");
						}
					}
				}

				$cache->cachesave(1);
				$cache = new DN\Cache\CacheBlock;
				$cache->cacheblock();
			}
		}

		/**
		 * Управление блоками (активация)
		 ----------------------------------*/
		if ($_REQUEST['dn']=='blockact')
		{
			global $act, $blockid;

			$act = preparse($act, THIS_TRIM);
			$blockid = preparse($blockid, THIS_INT);

			if ($act == 'no' OR $act == 'yes')
			{
				$db->query("UPDATE ".$basepref."_block SET block_active = '".$act."' WHERE blockid = '".$blockid."'");
			}

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить блок (сохранение)
		 --------------------------------*/
		if ($_REQUEST['dn']=='blockadd')
		{
			global $block_name, $block_mods, $block_cats, $block_file, $block_side, $block_cont, $conf, $block_temp, $block_access, $block_group;

			$block_side = preparse($block_side, THIS_INT);
			$block_cont = preparse($block_cont, THIS_TRIM);

			$binq = $db->query("SELECT * FROM ".$basepref."_block_posit WHERE positid = '".$block_side."'");

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['opt_manage_block'],
					$lang['add_block']
				);

			if ($db->numrows($binq) == 0)
			{
				$tm->header();
				$tm->error($lang['all_add'], $block_name, $lang['all_error']);
				$tm->footer();
			}
			else
			{
				$bs = $db->fetchrow($binq);
				$positid = $block_side;
				$block_side = $bs['positcode'];
			}

			if (empty($block_name))
			{
				$tm->header();
				$tm->error($lang['all_add'], $block_name, $lang['block_error_name']);
				$tm->footer();
			}

			if(empty($block_file) AND empty($block_cont))
			{
				$tm->header();
				$tm->error($lang['all_add'], $block_name, $lang['block_error_text']);
				$tm->footer();
			}

			if( ! empty($block_file) AND ! empty($block_cont))
			{
				$tm->header();
				$tm->error($lang['all_add'], $block_name, $lang['block_error_folder']);
				$tm->footer();
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$block_access == 'group' AND is_array($block_group)
			)
			{
				$block_group = Json::encode($block_group);
			}

			$block_file = preparse($block_file, THIS_TRIM, 0, 255);
			$block_name = preparse($block_name, THIS_TRIM, 0, 255);
			$block_cont = preparse($block_cont, THIS_TRIM);
			$block_access = ($block_access == 'user' OR $block_access == 'group') ? 'user' : 'all';

			$labellist = $labelarray = $blocksetting = array();

			if(is_array($block_mods))
			{
				foreach ($block_mods as $k => $v)
				{
					if (is_array($v))
					{
						$labelarray[$k] = array();
						foreach ($v as $lk => $lv) {
							$labelarray[$k][$lk] = $lv;
						}

						// IDs of categories
						if (isset($v['index']['cat']))
						{
							$cat = preg_replace('/[^0-9\,]/', '', trim($block_cats[$k]['cat'], ' '));
							if ( ! empty($cat))
							{
								$labelarray[$k]['cat'] = trim($cat, ',');
								if (isset($block_cats[$k]['exc']))
								{
									$labelarray[$k]['exc'] = 'yes';
								}
							}
						}
					}
					else
					{
						$labelarray[$k] = array('index'=>'index');
					}
				}
			}

			if ( ! empty($block_file))
			{
				define('SETTING', 1);
				$bs = include(WORKDIR.'/block/'.$block_file);
				if (is_array($bs))
				{
					foreach ($bs as $k => $v)
					{
						if ($k != 'blockname')
						{
							if (isset($block_setting[$k]))
							{
								if ($v['form'] == 'text') {
									$blocksetting[$k] = (intval($block_setting[$k]) > 0) ? $block_setting[$k] : 1;
								}
								if ($v['form'] == 'checkbox') {
									$blocksetting[$k] = ($block_setting[$k] == 'yes') ? 'yes' : 'no';
								}
								if ($v['form'] == 'select') {
									$blocksetting[$k] = (isset($v['value'][$block_setting[$k]])) ? $block_setting[$k] : $v['default'];
								}
							}
							else
							{
								$blocksetting[$k] = $v['default'];
							}
						}
					}
				}
			}

			$labellist = (is_array($labelarray)) ? Json::encode($labelarray) : '';
			$blocklist = (is_array($blocksetting)) ? Json::encode($blocksetting) : '';

			$db->query
				(
					"INSERT INTO ".$basepref."_block VALUES (
					 NULL,
					 '".$db->escape($positid)."',
					 '".$db->escape($block_side)."',
					 '".$db->escape($block_file)."',
					 '".$db->escape(preparse_sp($block_name))."',
					 '".$db->escape($block_cont)."',
					 'yes',
					 '0',
					 '".$db->escape($block_temp)."',
					 '".$db->escape($labellist)."',
					 '".$db->escape($block_access)."',
					 '".$db->escape($blocklist)."',
					 '".$db->escape($block_group)."'
					 )"
				);

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать блок
		 -----------------------*/
		if ($_REQUEST['dn'] == 'blockedit')
		{
			global $blockid, $catid, $selective;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['edit_block']
				);

			$tm->header();

			// Запросы в блоке, только для панели
			$admb = 1;

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_block WHERE blockid = '".$blockid."'"));

			$content = $item['block_cont'];

			$setarray = Json::decode($item['block_setting']);
			$labelarray = Json::decode($item['block_mods']);

			$blockdir = opendir(WORKDIR.'/block/');
			$listing = array();
			while ($name = readdir($blockdir))
			{
				if ( substr($name, 0, 2) == 'b-') {
					$listing[] = $name;
				}
			}
			closedir($blockdir);
			sort($listing);

			define('SETTING', 1);

			if ( ! empty($item['block_file'])) {
				$nocont = 'none';
			} elseif (empty($item['block_file']) AND empty($item['block_cont'])) {
				$nocont = 'fill';
			} else {
				$nocont = '';
			}

			echo '	<div class="section">
					<form action="index.php" method="post" id="blockform">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.preparse_un($item['block_name']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="block_name" size="50" value="'.preparse_un($item['block_name']).'" required="required">
							</td>
						</tr>
						<tr>
							<td>'.$lang['choice_block'].'</td>
							<td>
								<select name="block_file" class="sw210">
									<option value="">'.$lang['text_block'].'</option>';
			$bs = $sname = array();
			for ($i = 0; $i < sizeof($listing); $i ++)
			{
				if ($listing[$i])
				{
					$br = str_replace(array('b-', '.php'), '', $listing[$i]);
					$bs[$br] = include(WORKDIR.'/block/'.$listing[$i]);
					if (isset($bs[$br]['blockname']) AND ! empty($bs[$br]['blockname']))
					{
						$sname[$listing[$i]] = $bs[$br]['blockname'];
					}
					else
					{
						$sname[$listing[$i]] = $br;
					}
				}
			}
			natsort($sname);
			foreach ($sname as $k => $vname) {
				echo '				<option value="'.$k.'"'.(($k == $item['block_file']) ? ' selected' : '').'>'.$vname.'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_posit'].'</td>
							<td>
								<select name="block_side" class="sw210">';
			$inqs = $db->query("SELECT * FROM ".$basepref."_block_posit ORDER BY pposit");
			while ($items = $db->fetchrow($inqs)) {
				echo '				<option value="'.$items['positid'].'"'.(($items['positid'] == $item['positid']) ? ' selected' : '').'>'.$items['positname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_temp'].'</td>
							<td>
								<select name="block_temp" class="sw210">';
			$tempdir = opendir(WORKDIR.'/template/'.$conf['site_temp'].'/block/');
			echo '					<optgroup label="'.$lang['site_temp'].' '.$conf['site_temp'].'" class="optgroup">';
			while ($name = readdir($tempdir))
			{
				if (substr($name, -4) == '.tpl')
				{
					$tpl = str_replace('.tpl', '', $name);
					$sel[$tpl] = $tpl;
				}
			}
			natsort($sel);
			foreach ($sel as $k => $vsel) {
				echo '					<option value="'.$k.'"'.(($k == $item['block_temp']) ? ' selected' : '').'>'.$vsel.'</option>';
			}
			closedir($tempdir);
			echo '					</optgroup>
								</select>
							</td>
						</tr>';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['all_access'].'</td>
							<td>
								<select class="group-sel sw210" name="block_access" id="acc">
									<option value="all"'.(($item['block_access'] == 'all') ? ' selected' : '').'>'.$lang['all_all'].'</option>
									<option value="user"'.(($item['block_access'] == 'user') ? ' selected' : '').'>'.$lang['all_user_only'].'</option>';
				echo '				'.(($conf['user']['groupact'] == 'yes') ? '<option value="group"'.(($item['block_access'] == 'user' AND ! empty($item['block_group']))  ? ' selected' : '').'>'.$lang['all_groups_only'].'</option>' : '');
				echo '			</select>
								<div class="group" id="group"'.(($item['block_access'] == 'all' OR $item['block_access'] == 'user' AND empty($item['block_group'])) ? ' style="display: none;"' : '').'>';
				if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
				{
					$inqs = $db->query("SELECT * FROM ".$basepref."_user_group");
					$group = Json::decode($item['block_group']);
					$group_out = '';
					while ($items = $db->fetchrow($inqs))
					{
						$group_out.= '	<input type="checkbox" name="block_group['.$items['gid'].']" value="yes"'.(isset($group[$items['gid']]) ? ' checked' : '').'><span>'.$items['title'].'</span>,';
					}
					echo chop($group_out, ',');
				}
				echo '			</div>
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['block_mods'].'</td>
							<td>
								<div class="but" style="margin-bottom: 7px;" onclick="$.allselect(\'blockform\');"> '.$lang['all_mark'].'</div>';
			$inqs = $db->query("SELECT * FROM ".$basepref."_mods WHERE active = 'yes'");
			echo '				<table class="work">';
			while ($items = $db->fetchrow($inqs))
			{
				$table = NULL;
				$label = Json::decode($items['label']);
				foreach ($IPS as $k => $v)
				{
					if ($v['mod'] == $items['file'])
					{
						$table = $v['mod'];
					}
				}
				echo '				<tr>
										<td class="al bl">
											'.((is_array($label)) ? '<img onclick="$(\'#'.$items['file'].'_toggle\').toggle(\'fast\');" class="image-toggle" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png" alt="" />' : '').'
											<input type="checkbox" id="'.$items['file'].'checkbox" name="block_mods['.$items['file'].']" onclick="$.modcheck(\''.$items['file'].'\');" value="yes"'.(isset($labelarray[$items['file']]) ? " checked" : "").'> &nbsp';
				echo '						<span onclick="$(\'#'.$items['file'].'_toggle\').toggle(\'fast\');" class="'.((is_array($label)) ? 'site strong pointer' : 'gray').'">'.$items['name'].'</span>
											<div id="'.$items['file'].'_toggle" style="display: none;">';
				if (is_array($label))
				{
					foreach ($label as $k => $v)
					{
						if ( ! ($items['file'] == 'pages' AND $k == 'index'))
						{
							echo '					<div class="blocking">
														<span>'.$lang['all_file'].': <strong>'.$k.'</strong></span>';
							if (is_array($v))
							{
								foreach ($v as $sk => $sv)
								{
									echo '				<p><input type="checkbox" name="block_mods['.$items['file'].']['.$k.']['.$sk.']" value="yes"'.(isset($labelarray[$items['file']][$k][$sk]) ? ' checked' : '').'> '.$sk.'</p>';
								}
							}
							echo '					</div>';
						}
					}
					// IDs of categories
					if (method_exists($db, 'tables') AND $db->tables($items['file']."_cat"))
					{
						$cat = isset($labelarray[$items['file']]['cat']) ? $labelarray[$items['file']]['cat'] : '';
						$exc = isset($labelarray[$items['file']]['exc']) ? ' checked' : '';
						echo '						<div style="clear:both"></div>
													<div class="blockinfo">
														<input type="text" name="block_cats['.$items['file'].'][cat]" value="'.$cat.'" size="12"> ID '.$lang['all_cats'].'
														&nbsp; &nbsp; <input type="checkbox" name="block_cats['.$items['file'].'][exc]" value="yes"'.$exc.'> '.$lang['exception'].' ';
														$tm->outhint($lang['block_cat_help']);
						echo '						</div>';
					}
				}
				if (isset($table{0}))
				{
					$painq = $db->query("SELECT * FROM ".$basepref."_pages WHERE mods = '".$table."'");
					echo '						<dl class="blockinfo">
													<dt>'.$lang['all_page'].':</dt>
													<dd>';
					while ($page = $db->fetchrow($painq))
					{
						echo '							<p><input type="checkbox" name="block_mods['.$table.']['.$page['cpu'].']" value="yes"'.(isset($labelarray[$items['file']][$page['cpu']]) ? ' checked' : '').'>'.$page['title'].'</p>';
					}
					echo '							</dd>
												</dl>';
				}
				echo '						</div>
										</td>
									</tr>';
			}
			echo '				</table>
							</td>
						</tr>
						<tr>
							<td class="'.$nocont.'">';
			if (empty($item['block_cont'])) {
								$tm->outhint($lang['block_hint_add']);
			}
			echo '				&nbsp;'.$lang['all_text'].'
							</td>
							<td class="usewys '.$nocont.'">';
			if ($wysiwyg == 'yes')
			{
				define("USEWYS", 1);
				$form_more = 'block_cont';
				$WYSFORM = 'block_cont';
				$WYSVALUE = $content;
				include(ADMDIR.'/includes/wysiwyg.php');
			}
			else
			{
				$tm->textarea('block_cont', 5, 50, $content, 1);
			}
			echo '			</td>
						</tr>';

			if ( ! empty($item['block_file']))
			{
				$selcat = array();
				$bs = include(WORKDIR.'/block/'.$item['block_file']);
				if (is_array($bs))
				{
					if (is_array($setarray) AND ! empty($setarray)) {
						echo '	<tr>
									<th></th><th class="site"><strong>'.$lang['all_set'].'</strong></th>
								</tr>';
					}
					foreach ($bs as $k => $v)
					{
						if ($k != 'blockname')
						{
							echo '	<tr>
										<td>'.(isset($lang[$v['lang']]) ? $lang[$v['lang']] : $v['lang']).'</td>
										<td class="vm">';

							$val = isset($setarray[$k]) ? $setarray[$k] : $v['default'];
							if ($v['form'] == 'text')
							{
								$mod = ($k == 'mod') ? (isset($setarray[$k]) ? $setarray[$k] : $v['default']) : null;
								echo '		<input type="text" name="block_setting['.$k.']" size="25" value="'.$val.'">';
							}

							if ($v['form'] == 'checkbox')
							{
								$checked = ($val == 'yes') ? ' checked' : '';
								echo '		<input class="cset" type="checkbox" name="block_setting['.$k.']" value="'.$v['value'].'"'.$checked.'>';
							}

							if ($v['form'] == 'select')
							{
								if (isset($mod) AND $k == 'selcat')
								{
									$catcache = array();

									$inqs = $db->query("SELECT * FROM ".$basepref."_".$mod."_cat ORDER BY posit ASC", $conf['cachetime'], $mod);
									if ($db->numrows($inqs, $conf['cache']) > 0)
									{
										while ($citem = $db->fetchassoc($inqs, $conf['cache']))
										{
											$catcache[$citem['parentid']][$citem['catid']] = $citem;
										}
										$catid = $val;
										this_selectcat(0);
									}

									if (isset($catcache))
									{
										echo '	<select name="block_setting['.$k.']" class="sw165">
													<option value=""'.(($val === '' AND $val !== 0) ? ' selected' : '').'>'.$lang['all_all_cat'].'</option>
													<option value="0"'.(($val !== '' AND $val == 0) ? ' selected' : '').'>'.$lang['cat_not'].'</option>';
										echo		 $selective;
										echo '	</select>';
									}
								}
								else
								{
									if (isset($v['value']))
									{
										echo '	<select name="block_setting['.$k.']" class="sw165">';
										foreach ($v['value'] as $ks => $vs) {
											$selected = ($val == $ks) ? ' selected' : '';
											echo '	<option value="'.$ks.'"'.$selected.'>'.(isset($lang[$vs]) ? $lang[$vs] : $vs).'</option>';
										}
										echo '	</select>';
									}
								}
							}
							if ($v['form'] == 'multiple')
							{
								if (isset($v['value']))
								{
									echo '	<select name="block_setting['.$k.'][]" class="sw165" size="4" style="height: 75px;" multiple>';
									foreach ($v['value'] as $vk => $vv) {
										$selected = (strpos($setarray[$k], ''.$vk.'') !== false) ? ' selected' : '';
										echo '	<option value="'.$vk.'"'.$selected.'>'.(isset($lang[$vv]) ? $lang[$vv] : $vv).'</option>';
									}
									echo '	</select>';
								}
							}
							(isset($v['hint']) ? $tm->outhint(isset($lang[$v['hint']]) ? $lang[$v['hint']] : $v['hint']) : '');
							echo '		</td>
									</tr>';
						}
					}
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">';
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'no')
			{
				echo '			<input type="hidden" name="block_access" value="all">';
			}
			echo '				<input type="hidden" name="blockid" value="'.$blockid.'">
								<input type="hidden" name="dn" value="blockeditsave">
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
		 * Редактировать блок (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'blockeditsave')
		{
			global	$block_name, $block_file, $block_side, $block_cont, $blockid, $block_temp, $block_mods,
					$block_cats, $block_access, $block_setting, $block_group;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['edit_block']
				);

			$block_side = preparse($block_side, THIS_INT);
			$blockid = preparse($blockid, THIS_INT);
			$block_cont = preparse($block_cont, THIS_TRIM);

			$binq = $db->query("SELECT * FROM ".$basepref."_block_posit WHERE positid = '".$block_side."'");

			if ($db->numrows($binq) == 0)
			{
				$tm->header();
				$tm->error($lang['all_edit'], $block_name, $lang['all_error']);
				$tm->footer();
			}
			else
			{
				$bsi = $db->fetchrow($binq);
			}

			if (empty($block_name))
			{
				$tm->header();
				$tm->error($lang['all_edit'], $block_name, $lang['block_error_name']);
				$tm->footer();
			}

			if( ! empty($block_file) AND ! empty($block_cont))
			{
				$tm->header();
				$tm->error($lang['all_edit'], $block_name, $lang['block_error_folder']);
				$tm->footer();
			}

			if (
				isset($conf['user']['groupact']) AND
				$conf['user']['groupact'] == 'yes' AND
				$block_access == 'group' AND is_array($block_group)
			)
			{
				$block_group = Json::encode($block_group);
			}

			$block_file = preparse($block_file, THIS_TRIM, 0, 255);
			$block_name = preparse($block_name, THIS_TRIM, 0, 255);
			$block_cont = preparse($block_cont, THIS_TRIM);
			$block_access = ($block_access == 'user' OR $block_access == 'group') ? 'user' : 'all';

			$labelarray = $blocksetting = array();

			if(is_array($block_mods))
			{
				foreach ($block_mods as $k => $v)
				{
					if (is_array($v))
					{
						$labelarray[$k] = array();
						foreach ($v as $lk => $lv)
						{
							$labelarray[$k][$lk] = $lv;
						}

						// IDs of categories
						if (isset($v['index']['cat']))
						{
							$cat = preg_replace('/[^0-9\,]/', '', trim($block_cats[$k]['cat'], ' '));
							if ( ! empty($cat))
							{
								$labelarray[$k]['cat'] = trim($cat, ',');
								if (isset($block_cats[$k]['exc']))
								{
									$labelarray[$k]['exc'] = 'yes';
								}
							}
						}
					}
					else
					{
						$labelarray[$k] = array('index' => 'index');
					}
				}
			}

			if ( ! empty($block_file) AND is_array($block_setting))
			{
				define('SETTING', 1);
				$bs = include(WORKDIR.'/block/'.$block_file);
				if (is_array($bs))
				{
					foreach ($bs as $k => $v)
					{
						if ($k != 'blockname')
						{
							if (isset($block_setting[$k]))
							{
								if ($v['form'] == 'text') {
									$blocksetting[$k] = ($block_setting[$k] !== '') ? $block_setting[$k] : $v['default'];
								}
								if ($v['form'] == 'checkbox') {
									$blocksetting[$k] = ($block_setting[$k] == 'yes') ? 'yes' : 'no';
								}
								if ($v['form'] == 'select') {
									$blocksetting[$k] = ($block_setting[$k] !== '') ? $block_setting[$k] : $v['default'];
								}
								if ($v['form'] == 'multiple') {
									$multiple = implode(',',$block_setting[$k]);
									$blocksetting[$k] = (isset($block_setting[$k]) AND in_array('0', $block_setting[$k]) == false) ? $multiple : $v['default'];
								}
							}
							else
							{
								if ($v['form'] == 'checkbox') {
									$blocksetting[$k] = 'no';
								} else {
									$blocksetting[$k] = $v['default'];
								}
							}
						}
					}
				}
			}

			$labellist = (is_array($labelarray)) ? Json::encode($labelarray) : '';
			$blocklist = (is_array($blocksetting)) ? Json::encode($blocksetting) : '';

			$db->query
				(
					"UPDATE ".$basepref."_block SET
					 positid       = '".$db->escape($bsi['positid'])."',
					 block_side    = '".$db->escape($bsi['positcode'])."',
					 block_file    = '".$db->escape($block_file)."',
					 block_name    = '".$db->escape(preparse_sp($block_name))."',
					 block_cont    = '".$db->escape($block_cont)."',
					 block_temp    = '".$db->escape($block_temp)."',
					 block_mods    = '".$db->escape($labellist)."',
					 block_access  = '".$db->escape($block_access)."',
					 block_setting = '".$db->escape($blocklist)."',
					 block_group   = '".$db->escape($block_group)."'
					 WHERE blockid = '".$blockid."'"
				);

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=blockedit&amp;blockid='.$blockid.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить блок
		 -----------------*/
		if ($_REQUEST['dn'] == 'blockdel')
		{
			global $blockid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['del_block']
				);

			$blockid = preparse($blockid, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_block WHERE blockid = '".$blockid."'");

				$cache->cachesave(1);
				$cache = new DN\Cache\CacheBlock;
				$cache->cacheblock();

				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_block WHERE blockid = '".$blockid."'"));

				$yes = 'index.php?dn=blockdel&amp;blockid='.$blockid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=index&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['block_name']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Блочные позиции
		 ---------------------*/
		if ($_REQUEST['dn'] == 'blockposit')
		{
			global $nu, $ok, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['block_posit']
				);

			$tm->header();

			echo "	<script>
					$(function() {
						$('.readonly').focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>";

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query
					(
						"SELECT posit.*, COUNT(block.blockid) AS total
						 FROM ".$basepref."_block_posit AS posit
						 LEFT JOIN ".$basepref."_block AS block ON (posit.positid = block.positid)
						 GROUP BY posit.positid
						 ORDER BY posit.pposit ASC LIMIT ".$sf.", ".$nu
					);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages('block_posit', 'positid', 'index', 'blockposit', $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=blockposit&amp;p=".$p."&amp;ops=".$sess['hash']."&amp;nu=", $nu);

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['block_posit'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th>'.$lang['all_name'].' <strong>*</strong></th>
							<th>'.$lang['all_posit'].' <strong class="alternative">*</strong></th>
							<th>'.$lang['all_temp_tag'].'</th>
							<th>'.$lang['all_col'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="site al">
								<input style="width: 96%;" type="text" name="name['.$item['positid'].']" size="20" value="'.$item['positname'].'">
							</td>
							<td class="pw15">
								<input type="text" name="posit['.$item['positid'].']" size="2" value="'.$item['pposit'].'" maxlength="2">
							</td>
							<td class="pw15">
								<input value="{'.$item['positcode'].'}" size="15" readonly="readonly" class="readonly" type="text">
							</td>
							<td>'.$item['total'].'</td>
							<td class="gov pw15">
								<a href="index.php?dn=blockpositdel&amp;positid='.$item['positid'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png"></a>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="5">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="5">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="blockpositup">
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
						<caption>'.$lang['new_blockposit'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="positname" size="50" required="required"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_temp_tag'].'</td>
							<td><input type="text" name="positcode" size="50" required="required">';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="blockpositsave">
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
		 * Блочные позиции (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'blockpositup')
		{
			global $name, $posit;

			foreach ($name as $positid => $positname)
			{
				if ($positname) {
					$positid = preparse($positid, THIS_INT);
					$db->query
						(
							"UPDATE ".$basepref."_block_posit
							 SET positname = '".$db->escape(preparse_sp($positname))."'
							 WHERE positid = '".$positid."'"
						);
				}
			}
			foreach ($posit as $positid => $pposit)
			{
				$positid = preparse($positid, THIS_INT);
				$pposit = preparse($pposit, THIS_INT);
				$db->query
					(
						"UPDATE ".$basepref."_block_posit
						 SET pposit = '".$pposit."'
						 WHERE positid = '".$positid."'"
					);
			}

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=blockposit&amp;ops='.$sess['hash']);
		}

		/**
		 * Блочные позиции (добавление)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'blockpositsave')
		{
			global $positcode, $positname;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['block_posit']
				);

			if (
				preparse($positcode, THIS_SYMNUM) == 1 OR
				preparse($positcode, THIS_EMPTY) == 1 OR
				preparse($positname, THIS_EMPTY) == 1
			) {
				$tm->header();
				$tm->error($lang['add_posit'], $positname, $lang['forgot_name']);
				$tm->footer();
			}

			$codearr = array();
			$binq = $db->query("SELECT * FROM ".$basepref."_block_posit");

			while ($item = $db->fetchrow($binq))
			{
				$codearr[$item['positcode']] = $item['positcode'];
			}

			$newpposit = $db->numrows($binq) + 1;

			if (in_array($positcode, $codearr))
			{
				$tm->header();
				$tm->error($lang['add_posit'], $positname, $lang['positcode_isset']);
				$tm->footer();
			}
			else
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_block_posit VALUES (
						 NULL,
						 '".$db->escape($positcode)."',
						 '".$db->escape($positname)."',
						 '".$newpposit."'
						 )"
					);
			}

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheBlock;
			$cache->cacheblock();

			redirect('index.php?dn=blockposit&amp;ops='.$sess['hash']);
		}

		/**
		 * Блочные позиции (удаление)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'blockpositdel')
		{
			global $positid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_block'].'</a>',
					$lang['block_posit'],
					$lang['all_delet']
				);

			$positid = preparse($positid, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_block WHERE positid = '".$positid."'");
				$db->query("DELETE FROM ".$basepref."_block_posit WHERE positid = '".$positid."'");

				$cache->cachesave(1);
				$cache = new DN\Cache\CacheBlock;
				$cache->cacheblock();

				redirect('index.php?dn=blockposit&ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT positcode FROM ".$basepref."_block_posit WHERE positid = '".$positid."'"));

				$yes = 'index.php?dn=blockpositdel&amp;positid='.$positid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=blockposit&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_posit'], $item['positcode'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['opt_manage_block'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
