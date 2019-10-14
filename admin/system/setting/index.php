<?php
/**
 * File:        /admin/system/system/index.php
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
			$lang['optset_system']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'upsave', 'setmod', 'setsave', 'setedit', 'seteditsave', 'setdel', 'debug', 'debugsave', 'debugdel');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn']= (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_set'].'</a>'
					.'<a'.cho('setmod, setedit').' href="index.php?dn=setmod&amp;ops='.$sess['hash'].'">'.$lang['menu_seditor'].'</a>'
					.'<a'.cho('debug, debugdel').' href="index.php?dn=debug&amp;ops='.$sess['hash'].'">'.$lang['debug'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Системные настройки
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					$lang['opt_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['optset_system'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'system'");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo '	<tr>
							<td class="first">
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
				echo		eval(preparse_un($itemset['setcode']));
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="upsave">
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
		 * Сохранение настроек
		 */
		if ($_REQUEST['dn'] == 'upsave')
		{
			global $set, $conf, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					$lang['opt_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'system'");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['optset_system'], $lang['all_save'], $lang['forgot_name'].'<div class="black">'.$lang[$item['setlang']].'</div>');
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse(preparse_sp($set[$item['setname']]), THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			// Cachetime non zero
			if (isset($set['cache']) AND $set['cache'] == 'yes' AND $set['cachetime'] == 0)
			{
				$db->query("UPDATE ".$basepref."_settings SET setval = '1' WHERE setname = 'cachetime'");
			}

			$cache->cachesave(1);
			$cache = new DN\Cache\CacheLogin;
			$cache->cachelogin();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактор настроек
		 */
		if ($_REQUEST['dn'] == 'setmod')
		{
			global $type;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					$lang['menu_seditor']
				);

			$tm->header();

			$inqs = $db->query("SELECT setopt FROM ".$basepref."_".TABLE."");
			while ($items = $db->fetchrow($inqs))
			{
					$outset[] = $items['setopt'];
			}
			$outset = array_unique($outset);
			sort($outset);

			$type = ( ! empty($type) ? $type : 'catalog');
			echo '	<div class="section">
					<form action="index.php" method="post" name="setting">
					<table id="list" class="work">
						<caption>'.$lang['menu_seditor'].'</caption>
						<tr>
							<th class="ar">'.$lang['file_group'].'</th>
							<th class="al" colspan="2"><strong class="vars bold">'.$type.'</strong></th>
						</tr>
						<tr>
							<td class="ar">
								<select name="type">';
			foreach ($outset as $v)
			{
				if ( ! in_array($v, $MOD_NOSET))
				{
					echo '<option value="'.$v.'"'.(($v == $type) ? ' selected' : '').'> '.$v.' </option>';
				}
			}
			echo '				</select>
							</td>
							<td class="al vm" colspan="2">
								<input type="hidden" name="dn" value="setmod" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="side-button" value="'.$lang['all_select'].'" type="submit" />
							</td>
						</tr>
						<tr>
							<th class="ar pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['lang_val'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_".TABLE." WHERE setopt = '".$type."' AND setcode <> ''");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo '	<tr class="list">
							<td class="courier black">['.$itemset['setname'].']</td>
							<td class="courier vars">['.(empty($itemset['setlang']) ? ' — — — ' : $itemset['setlang']).']</td>
							<td class="gov">
								<a href="index.php?dn=setedit&amp;setid='.$itemset['setid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=setdel&amp;setid='.$itemset['setid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot"><td colspan="3">&nbsp;</td></tr>
					</table>
					</form>
					</div><div class="pads"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['group'].' '.$type.': '.$lang['add_field'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="setname" size="50" required="required" /></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['lang_val'].'</td>
							<td><input type="text" name="setlang" size="50" required="required" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_not_empty'].'</td>
							<td>
								<select name="setmark" class="sw70">
									<option value="0"> '.$lang['all_no'].' </option>
									<option value="1"> '.$lang['all_yes'].' </option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['code_interface'].'</td>
							<td>';
								$tm->textarea('setcode', 5, 70, '', 1, '', '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>PHP code check</td>
							<td>';
								$tm->textarea('setvalid', 5, 70, '', 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="setsave" />
								<input type="hidden" name="setopt" value="'.$type.'" />
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
		 * Сохранение настроек
		 */
		if ($_REQUEST['dn'] == 'setsave')
		{
			global $setname, $setlang, $setmark, $setcode, $setvalid, $setopt;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					$lang['menu_seditor']
				);

			if (preparse($setopt, THIS_SYMNUM) == 1 OR preparse($setopt, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['menu_seditor'], $lang['add_field'], $lang['isset_error'].'<div class="black">'.$lang['group'].'</div>');
				$tm->footer();
			}

			if (preparse($setname, THIS_SYMNUM) == 1 OR preparse($setname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['menu_seditor'], $lang['add_field'], $lang['bad_fields'].'<div class="black">'.$lang['all_name'].'</div>');
				$tm->footer();
			}

			$setname = preparse($setname, THIS_TRIM, 0, 255);
			if (preparse($setlang, THIS_SYMNUM) == 1 OR preparse($setlang, THIS_EMPTY) == 1 OR ! isset($lang[$setlang]))
			{
				$tm->header();
				$tm->error($lang['menu_seditor'], $lang['add_field'], $lang['bad_fields'].'<div class="black">'.$lang['lang_val'].'</div>');
				$tm->footer();
			}

			$setlang = preparse($setlang, THIS_TRIM, 0, 255);
			$setmark = preparse($setmark,THIS_INT);
			$setmark = ($setmark == 0) ? 0 : 1;
			if (preparse($setcode, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['menu_seditor'], $lang['add_field'], $lang['bad_fields'].'<div class="black">'.$lang['code_interface'].'</div>');
				$tm->footer();
			}

			$setopt = preparse($setopt, THIS_TRIM, 0, 255);
			$setcode = preparse($setcode, THIS_TRIM);
			$setvalid = preparse($setvalid, THIS_TRIM);
			$inq = $db->query("SELECT setid FROM ".$basepref."_".TABLE." WHERE setopt = '".$db->escape($setopt)."' AND setname = '".$db->escape($setname)."'");
			if ($db->numrows($inq) > 0)
			{
				$tm->header();
				$tm->error($lang['menu_seditor'], $lang['add_field'], $lang['isset_error']);
				$tm->footer();
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_".TABLE." VALUES (
					 NULL,
					 '".$db->escape($setopt)."',
					 '".$db->escape($setname)."',
					 '',
					 '".$db->escape($setmark)."',
					 '".$db->escape($setlang)."',
					 '".$db->escape($setcode)."',
					 '".$db->escape($setvalid)."'
					 )"
				);

			$cache->cachesave(1);
			redirect('index.php?dn=setmod&amp;type='.$setopt.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактирование настроек
		 */
		if ($_REQUEST['dn'] == 'setedit')
		{
			global $setid, $ro;

			$setid = preparse($setid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".TABLE." WHERE setid = '".$setid."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					'<a href="index.php?dn=setmod&amp;ops='.$sess['hash'].'">'.$lang['menu_seditor'].'</a>',
					'<a href="index.php?dn=setmod&amp;type='.$item['setopt'].'&amp;ops='.$sess['hash'].'">'.$lang['group'].': '.$item['setopt'].'</a>',
				);

			$tm->header();

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['all_preview'].'</caption>
						<tr>
							<td>'.(($item['setmark'] == 1) ? '<span class="red">*</span> ' : '').((isset($lang[$item['setlang']])) ? $lang[$item['setlang']] : $item['setlang']).'</td>
							<td>';
								preparse_un(eval($item['setcode']));
			echo '			</td>
						</tr>
					</table>
					</div>
					<div class="sline"></div>';

			$items = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_".TABLE." WHERE setid = '".$setid."'"));
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['edit_setting'].': '.preparse_un($items['setname']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="setname" size="50" value="'.preparse_un($items['setname']).'" required="required"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['lang_val'].'</td>
							<td><input type="text" name="setlang" size="50" value="'.preparse_un($items['setlang']).'" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['all_not_empty'].'</td>
							<td>
								<select name="setmark" class="sw70">
									<option value="0"'.(($items['setmark'] == 0) ? ' selected' : '').'> '.$lang['all_no'].' </option>
									<option value="1"'.(($items['setmark'] == 1) ? ' selected' : '').'> '.$lang['all_yes'].' </option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['code_interface'].'</td>
							<td><textarea class="courier" style="width: 99%;" name="setcode" id="setcode" rows="5" cols="70" required="required">'.$items['setcode'].'</textarea></td>
						</tr>
						<tr>
							<td>PHP code check</td>
							<td><textarea class="courier" style="width: 99%;" name="setvalid" id="setvalid" rows="5" cols="70">'.$items['setvalid'].'</textarea></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="seteditsave">
								<input type="hidden" name="setopt" value="'.$items['setopt'].'">
								<input type="hidden" name="setid" value="'.$items['setid'].'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="submit" name="apply" value="'.$lang['all_apply'].'" class="main-button">
								<input type="submit" value="'.$lang['all_save'].'" class="main-button">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактирование настроек (сохранение)
		 */
		if ($_REQUEST['dn'] == 'seteditsave')
		{
			global $setid, $setopt, $setname, $setlang, $setmark, $setcode, $setvalid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					'<a href="index.php?dn=setmod&amp;ops='.$sess['hash'].'">'.$lang['menu_seditor'].'</a>',
					'<a href="index.php?dn=setmod&amp;type='.$setopt.'&amp;ops='.$sess['hash'].'">'.$lang['group'].': '.$setopt.'</a>',
				);

			if (preparse($setopt, THIS_SYMNUM) == 1 OR preparse($setopt, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_setting'], $setname, $lang['isset_error'].'<div class="black">'.$lang['group'].'</div>');
				$tm->footer();
			}

			if (preparse($setname, THIS_SYMNUM) == 1 OR preparse($setname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_setting'], $setname, $lang['bad_fields'].'<div class="black">'.$lang['all_name'].'</div>');
				$tm->footer();
			}

			if (preparse($setlang, THIS_SYMNUM) == 1 OR preparse($setlang, THIS_EMPTY) == 1 OR ! isset($lang[$setlang]))
			{
				$tm->header();
				$tm->error($lang['edit_setting'], $setname, $lang['bad_fields'].'<div class="black">'.$lang['lang_val'].'</div>');
				$tm->footer();
			}

			if (preparse($setcode,THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['edit_setting'], $setname, $lang['bad_fields'].'<div class="black">'.$lang['code_interface'].'</div>');
				$tm->footer();
			}

			$setmark = (preparse($setmark, THIS_INT) == 0) ? 0 : 1;
			$setname = preparse($setname, THIS_TRIM, 0, 255);
			$setopt = preparse($setopt, THIS_TRIM, 0, 255);
			$setlang = preparse($setlang, THIS_TRIM, 0, 255);
			$setcode = preparse($setcode, THIS_TRIM);
			$setvalid = preparse($setvalid, THIS_TRIM);
			$setid = preparse($setid, THIS_INT);

			$inq = $db->query("SELECT setid FROM ".$basepref."_".TABLE." WHERE setopt = '".$setopt."' AND setname = '".$db->escape($setname)."' AND setid <> '".$setid."'");

			if ($db->numrows($inq) > 0)
			{
				$tm->header();
				$tm->error($lang['edit_setting'], $setname, $lang['isset_error']);
				$tm->footer();
			}

			$db->query
				(
					"UPDATE ".$basepref."_settings SET
					 setname = '".$db->escape($setname)."',
					 setlang = '".$db->escape($setlang)."',
					 setmark = '".$db->escape($setmark)."',
					 setcode = '".$db->escape($setcode)."',
					 setvalid = '".$db->escape($setvalid)."'
					 WHERE setid='".$setid."'"
				);

			$cache->cachesave(1);
			$setout = isset($apply) ? 'setedit&amp;setid='.$setid : 'setmod&amp;type='.$setopt;
			redirect('index.php?dn='.$setout.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление настройки
		 ----------------------*/
		if ($_REQUEST['dn'] == 'setdel')
		{
			global $setid, $type, $ok;

			$setid = preparse($setid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT setid, setname, setopt FROM ".$basepref."_settings WHERE setid = '".$setid."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					'<a href="index.php?dn=setmod&amp;ops='.$sess['hash'].'">'.$lang['menu_seditor'].'</a>',
					'<a href="index.php?dn=setmod&amp;type='.$item['setopt'].'&amp;ops='.$sess['hash'].'">'.$lang['group'].': '.$item['setopt'].'</a>',
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_settings WHERE setid = '".$setid."'");
				$cache->cachesave(1);
				redirect('index.php?dn=setmod&amp;type='.$type.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=setdel&amp;type='.$item['setopt'].'&amp;setid='.$setid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=setmod&amp;type='.$item['setopt'].'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['setname']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Отладка системы
		 ------------------*/
		if ($_REQUEST['dn'] == 'debug')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					$lang['debug']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['debug'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'debug'");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo '	<tr>
							<td class="first">
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
				echo			eval(preparse_un($itemset['setcode']));
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="debugsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div><div class="pads"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['file_log'].'</caption>
						<tr>
							<th>'.$lang['sys_date'].'</th>
							<th>'.$lang['type'].'</th>
							<th>'.$lang['all_decs'].'</th>
							<th>'.$lang['all_file'].'</th>
							<th>'.$lang['one_line'].'</th>
						<tr>';
			require_once(WORKDIR.'/core/classes/Debug.php');
			$deb = new Debug();
			$log_path = WORKDIR.DIRECTORY_SEPARATOR.$deb->dir.DIRECTORY_SEPARATOR.$deb->file;
			if (file_exists($log_path))
			{
				$log_data = file($log_path);
				if ( ! empty($log_data))
				{
					krsort($log_data);
					foreach ($log_data as $val)
					{
						$cell = explode("|", $val);
						$error_date = gmdate('d.m.Y  H:i:s', $cell[0] + (3600 * $conf['timezone']));
						echo '	<tr class="bugs">
									<td>'.$error_date.'</td>
									<td>'.$cell[1].'</td>
									<td>'.$cell[2].'</td>
									<td>'.str_replace(WORKDIR.'/', '', $cell[3]).'</td>
									<td>'.$cell[4].'</td>
								<tr>';
					}
					echo '	<tr class="tfoot">
								<td colspan="5">
									<input type="hidden" name="dn" value="debugdel">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input class="main-button" value="'.$lang['clear_list'].'" type="submit">
								</td>
							</tr>';
				}
				else
				{
					echo '		<tr class="bugs-not">
									<td colspan="5">'.$lang['data_not'].'</td>
								</tr>';
				}
			}
			else
			{
				echo '	<tr class="bugs-not">
							<td colspan="5">'.$lang['data_not'].'</td>
						<tr>';
			}
			echo '	</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Отладка системы (сохранение)
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'debugsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					'<a href="index.php?dn=debug&amp;ops='.$sess['hash'].'">'.$lang['debug'].'</a>'
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'debug'");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['debug'], $lang['all_save'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=debug&amp;ops='.$sess['hash']);
		}

		/**
		 * Очистить список ошибок
		 -------------------------*/
		if ($_REQUEST['dn'] == 'debugdel')
		{
			global $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['optset_system'].'</a>',
					'<a href="index.php?dn=debug&amp;ops='.$sess['hash'].'">'.$lang['debug'].'</a>'
				);

			if ($ok == 'yes')
			{
				require_once(WORKDIR.'/core/classes/Debug.php');
				$deb = new Debug();
				$filename = WORKDIR.DIRECTORY_SEPARATOR.$deb->dir.DIRECTORY_SEPARATOR.$deb->file;

				if (file_exists($filename) AND filesize($filename) > 0)
				{
					file_put_contents($filename, NULL);
				}

				redirect('index.php?dn=debug&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=debugdel&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=debug&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['file_log'], $lang['clear_list'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['all_system'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
