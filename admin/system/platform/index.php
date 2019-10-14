<?php
/**
 * File:        /admin/system/platform/index.php
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
			$lang['platform']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'setsave', 'list', 'clear', 'addsave', 'edit', 'editsave', 'del');

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

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list, addsave, del').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>'
					.'<a'.cho('clear').' href="index.php?dn=clear&amp;ops='.$sess['hash'].'">'.$lang['clear_list'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Платформы сайтов, управление
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $tm, $lang, $PLATFORM, $pid, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					$lang['all_set']
				);

			$sel = '<option value="0">'.DEF_SITE.'</option>';

			if (preparse($PLATFORM, THIS_ARRAY) == 1 AND in_array('platform', $ADMIN_PERM_ARRAY))
			{
				$pid = 0;
				if (isset($_COOKIE[PCOOKIE])) {
					list($pid) = unserialize($_COOKIE[PCOOKIE]);
				}

				foreach ($PLATFORM as $id => $site)
				{
					$sel.= '<option value="'.$id.'"'.((preparse($pid,THIS_INT) > 0 AND isset($PLATFORM[$pid]) AND $id == $pid) ? ' selected' : '').'>'.$site['name'].'</option>';
				}
			}

			$tm->header();

			echo '	<div class="board">
						<div class="pads">
						<form action="index.php" method="post" id="platforms">
							<select name="sel" class="sw210" style="height: 32px;">
								'.$sel.'
							</select>
							<input type="hidden" name="dn" value="setsave">
							<input type="hidden" name="ops" value="'.$sess["hash"].'">
							<input id="reload" class="main-button" value="'.$lang['re_platform'].'" type="submit">
						</form>
						</div>
						<div class="pads"><hr /></div>
						<div class="pads">'.$lang["help_platform"].'</div>
					</div>';

			$tm->footer();
		}

		/**
		 * Смена платформы (сохранение)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'setsave')
		{
			global $sess, $sel, $PLATFORM;

			$sel = preparse($sel, THIS_INT);

			if (in_array('platform', $ADMIN_PERM_ARRAY))
			{
				if (isset($PLATFORM[$sel]) OR $sel == 0)
				{
					setcookie(PCOOKIE, serialize(array($sel)), time() + LIFE_ADMIN, '/'.APANEL.'/');
				}
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Список платформ
		 -------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $db, $basepref, $lang, $PLATFORM, $pid, $tm, $sess, $_COOKIE;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>'
				);

			$pl_set = array();
			$get_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));
			$pl_set = Json::decode($get_set['setval']);

			if (isset($pid) AND $pid > 0)
			{
				$alert_name = str_replace('{name}', $PLATFORM[$pid]['name'], $lang['alert_platform']);

				$tm->header();
				$tm->alert($lang['all_error'].'!', $alert_name);
				$tm->footer();
			}
			else
			{
				$tm->header();

				echo '	<div class="section">
							<form action="index.php" method="post">
							<table class="work">
							<caption>'.$lang['all_platform'].'</caption>
								<tr>
									<th class="first ar"><span>No. 0</span></th><th class="site bold" colspan="2">'.DEF_SITE.'</th>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>'.$lang['basic_set'].'</td>
								</tr>
							</table>
						</div>
						<div class="sline"></div>
						<div class="section">
							<table class="work">
							<caption>'.$lang['addit_platform'].'</caption>';
				if (is_array($pl_set) AND ! empty($pl_set))
				{
					foreach ($pl_set as $k => $v)
					{
						echo '	<tr>
									<th class="first ar"><span>No. '.$k.'</span></th><th class="site bold" colspan="2">'.$v['name'].'</th>
								</tr>
								<tr>
									<td>'.$lang['all_name'].'</td>
									<td><input class="actonly" name="name" size="70" type="text" value="'.$v['name'].'" disabled="disabled" /></td>
									<td rowspan="4" class="pw5 ac gov">
										<a href="index.php?dn=edit&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
										<a href="index.php?dn=del&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
									</td>
								</tr>
								<tr>
									<td>'.$lang['site_root'].'</td>
									<td><input class="actonly" name="path" size="70" type="text" value="'.$v['path'].'" required="required" /></td>
								</tr>
								<tr>
									<td>'.$lang['name_base'].'</td>
									<td><input class="actonly" name="base" size="70" type="text" value="'.$v['base'].'" required="required" /></td>
								</tr>
								<tr>
									<td>'.$lang['table_prefix'].'</td>
									<td><input class="actonly" name="pref" size="70" type="text" value="'.$v['pref'].'" required="required" /></td>
								</tr>';
					}
					echo '		<tr class="tfoot">
									<td colspan="3">
										<input type="hidden" name="dn" value="clear">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input type="hidden" name="admid" value="'.$ADMIN_ID.'">
										<input id="reload" class="main-button" value="'.$lang['clear_list'].'" type="submit">
									</td>
								</tr>';
				}
				else
				{
					echo '		<tr>
									<td class="ac" colspan="3">
										<div class="pads">'.$lang['data_not'].'</div>
									</td>
								</tr>';
				}
				echo '		</table>
							</form>
						</div>
						<div class="sline"></div>
						<div class="section">
							<form action="index.php" method="post">
							<table class="work">
							<caption>'.$lang['add_platform'].'</caption>
								<tr>
									<td>'.$lang['all_name'].'</td>
									<td>
										<input name="name" size="70" type="text" placeholder="Danneo CMS" required="required" />
									</td>
								</tr>
								<tr>
									<td>'.$lang['site_root'].'</td>
									<td>
										<input name="path" size="70" type="text" placeholder="/var/www/data/danneo.ru" required="required" />';
										$tm->outhint($lang['no_slash']);
				echo '				</td>
								</tr>
								<tr>
									<td>'.$lang['name_base'].'</td>
									<td>
										<input name="base" size="70" type="text" placeholder="danneo" required="required" />
									</td>
								</tr>
								<tr>
									<td>'.$lang['table_prefix'].'</td>
									<td>
										<input name="pref" size="70" type="text" placeholder="dn" required="required" />
									</td>
								</tr>
								<tr class="tfoot">
									<td colspan="2">
										<input type="hidden" name="dn" value="addsave">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input type="hidden" name="admid" value="'.$ADMIN_ID.'">
										<input id="reload" class="main-button" value="'.$lang['all_submint'].'" type="submit">
									</td>
								</tr>
							</table>
							</form>
						</div>';

				$tm->footer();
			}
		}

		/**
		 * Удаление всех платформ (очистка)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'clear')
		{
			global $sess, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>',
					$lang['clear_list']
				);

			if ($ok == 'yes')
			{
				if (isset($_COOKIE[PCOOKIE]))
				{
					setcookie(PCOOKIE, '', time() - LIFE_ADMIN, '/'.APANEL.'/');
				}

				$ins = NULL;
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$ins."' WHERE setname = 'platforms'");
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=clear&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['platform'], $lang['clear_list'], $yes, $not, $lang['help_clear_platform']);
				$tm->footer();
			}
		}

		/**
		 * Редактирование платформы
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $sess, $id, $PLATFORM;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$id = preparse($id, THIS_INT);

			$get_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));
			$pl_set = Json::decode($get_set['setval']);

				echo '	<div class="section">
							<form action="index.php" method="post">
							<table class="work">
							<caption>'.$lang['edit_platform'].': '.$pl_set[$id]['name'].'</caption>';
						echo '	<tr>
									<th></th><th class="site bold" colspan="2">'.$pl_set[$id]['name'].'</th>
								</tr>
								<tr>
									<td>'.$lang['all_name'].'</td>
									<td><input name="name" size="70" type="text" value="'.$pl_set[$id]['name'].'" required="required" /></td>
								</tr>
								<tr>
									<td>'.$lang['site_root'].'</td>
									<td><input name="path" size="70" type="text" value="'.$pl_set[$id]['path'].'" required="required" />';
										$tm->outhint($lang['no_slash']);
						echo '		</td>
								</tr>
								<tr>
									<td>'.$lang['name_base'].'</td>
									<td><input name="base" size="70" type="text" value="'.$pl_set[$id]['base'].'" required="required" /></td>
								</tr>
								<tr>
									<td>'.$lang['table_prefix'].'</td>
									<td><input name="pref" size="70" type="text" value="'.$pl_set[$id]['pref'].'" required="required" /></td>
								</tr>';
					echo '		<tr class="tfoot">
									<td colspan="2">
										<input type="hidden" name="dn" value="editsave">
										<input type="hidden" name="id" value="'.$id.'">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input type="hidden" name="admid" value="'.$ADMIN_ID.'">
										<input id="reload" class="main-button" value="'.$lang['all_save'].'" type="submit">
									</td>
								</tr>';
				echo '		</table>
							</form>
						</div>';

			$tm->footer();
		}

		/**
		 * Редактирование платформы (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $id, $name, $path, $base, $pref, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);

			$get_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));
			$pl_set = Json::decode($get_set['setval']);

			if (preparse(array($name, $path, $base, $pref), THIS_GROUP_EMPTY) == 0)
			{
				$bad_name = FALSE;
				foreach ($pl_set as $k => $v)
				{
					if ($id != $k AND $v['name'] == $name)
					{
						$bad_name = TRUE;
					}
				}

				if ($bad_name)
				{
					$tm->header();
					$tm->error($lang['edit_platform'], $name, $lang['plat_already_exists']);
					$tm->footer();
				}

				if ( ! is_dir($path))
				{
					$tm->header();
					$tm->error($lang['edit_platform'], $name, 'Not correct path: <strong>'.$path.'</strong>');
					$tm->footer();
				}

				$check_bd = $db->numrows($db->query("SHOW DATABASES LIKE '".$base."'"));
				if ($check_bd == 0)
				{
					$tm->header();
					$tm->error($lang['edit_platform'], $name, 'Database does not exist: <strong>'.$base.'</strong>');
					$tm->footer();
				}

				$edit[$id]	= array
				(
					'name' => $name,
					'path' => $path,
					'base' => $base,
					'pref' => $pref
				);

				$pl_set = array_replace($pl_set, $edit);
				$ins = Json::encode($pl_set);
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'platforms'");
			}
			else
			{
				$tm->header();
				$tm->error($lang['edit_platform'], $name, $lang['forgot_name']);
				$tm->footer();
			}

			$cache->cachesave(1);
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление платформы (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $db, $name, $path, $base, $pref, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>',
					$lang['all_add']
				);

			$get_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));
			$pl_set = Json::decode($get_set['setval']);

			if (preparse(array($name, $path, $base, $pref), THIS_GROUP_EMPTY) == 0)
			{
				if ( ! is_dir($path))
				{
					$tm->header();
					$tm->error($lang['add_platform'], $name, 'Not correct path: <strong>'.$path.'</strong>');
					$tm->footer();
				}

				$check_bd = $db->numrows($db->query("SHOW DATABASES LIKE '".$base."'"));
				if ($check_bd == 0)
				{
					$tm->header();
					$tm->error($lang['add_platform'], $name, 'Database does not exist: <strong>'.$base.'</strong>');
					$tm->footer();
				}

				if ( ! empty($pl_set))
				{
					$bad_name = FALSE;
					foreach ($pl_set as $v) {
						if ($v['name'] == $name) {
							$bad_name = TRUE;
						}
					}
					if ($bad_name)
					{
						$tm->header();
						$tm->error($lang['add_platform'], $name, $lang['plat_already_exists']);
						$tm->footer();
					}

					$in = array('name' => $name, 'path' => $path, 'base' => $base, 'pref' => $pref);
					array_push($pl_set, $in);
				}
				else
				{
					$pl_set[1] = array('name' => $name, 'path' => $path, 'base' => $base, 'pref' => $pref);
				}

				sort($pl_set);
				array_unshift($pl_set, NULL);
				unset($pl_set[0]);

				$ins = Json::encode($pl_set);
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'platforms'");
			}
			else
			{
				$tm->header();
				$tm->error($lang['add_platform'], $name, $lang['forgot_name']);
				$tm->footer();
			}

			$cache->cachesave(1);
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление платформы
		 ----------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $ok, $lang, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['platform'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$get_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'platforms'"));
				$pl_set = Json::decode($get_set['setval']);

				unset($pl_set[$id]);

				sort($pl_set);
				array_unshift($pl_set, NULL);
				unset($pl_set[0]);

				$ins = Json::encode($pl_set);
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'platforms'");

				$cache->cachesave(1);
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=del&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['delet_of_list'], $PLATFORM[$id]['name'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['platform'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
