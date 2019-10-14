<?php
/**
 * File:        /admin/system/geo/index.php
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
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $lang, $sess, $ops, $cache;

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
			$lang['menu_geo']
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
				'index', 'up', 'add', 'save', 'edit', 'editsave', 'del',
				'region', 'regionadd', 'regionaddsave', 'upregion', 'regiondel'
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

			$link = '<a'.cho('index, edit').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['list_country'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['add_country'].'</a>'
					.'<a'.cho('regionadd').' href="index.php?dn=regionadd&amp;ops='.$sess['hash'].'">'.$lang['add_state'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Список стран
		 ----------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					$lang['list_country']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['menu_geo'].': '.$lang['list_country'].'</caption>
						<tr>
							<th class="al">'.$lang['country'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>ISO</th>
							<th>ISO 2</th>
							<th>ISO 3</th>
							<th>'.$lang['flag_block'].'</th>
							<th>'.$lang['states'].'</th>
							<th>'.$lang['sys_manage'].'</td>
						</tr>';
			$region = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($region[$item['countryid']])) {
					$region[$item['countryid']] ++;
				} else {
					$region[$item['countryid']] = 1;
				}
			}
			$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="site al vm pw15">'.$item['countryname'].'</td>
							<td class="pw10"><input name="posit['.$item['countryid'].']" size="3" maxlength="3" value="'.$item['posit'].'" type="text"></td>
							<td class="vm">'.$item['iso'].'</td>
							<td class="vm">'.$item['iso2'].'</td>
							<td class="vm">'.$item['iso3'].'</td>
							<td class="vm"><img src="'.WORKURL.'/'.$item['icon'].'" alt="'.$item['countryname'].'"></td>
							<td class="com"><a href="index.php?dn=region&amp;id='.$item['countryid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/link.png" alt="'.$lang['states'].'" /></a> <span class="gray">('.((isset($region[$item['countryid']]) ? $region[$item['countryid']] : 0)).')</span></td>
							<td class="gov">
								<a href="index.php?dn=edit&amp;id='.$item['countryid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=del&amp;id='.$item['countryid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="up">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>';

			$tm->footer();
		}

		/**
		 * Список стран, сохранить позиции
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'up')
		{
			global $posit;

			if (is_array($posit))
			{
				foreach ($posit as $k => $v)
				{
					$id = preparse($k, THIS_INT);
					$v = preparse($v, THIS_INT);
					$db->query("UPDATE ".$basepref."_country SET posit = '".$db->escape($v)."' WHERE countryid = '".$id."'");
				}
				$cache = new DN\Cache\CacheCountry;
				$cache->cachecountry();
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить страну
		 -------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					$lang['add_country']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['menu_geo'].': '.$lang['add_country'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="countryname" size="50" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="50" type="text">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/country/\',\'&field[1]=icon\')" value=" '.$lang['filebrowser'].' " type="button">
							</td>
						</tr>
						<tr>
							<td>ISO</td>
							<td><input type="text" name="iso" maxlength="11" size="50"></td>
						</tr>
						<tr>
							<td>ISO 2</td>
							<td><input type="text" name="iso2" maxlength="2" size="50"></td>
						</tr>
						<tr>
							<td>ISO 3</td>
							<td><input type="text" name="iso3" maxlength="3" size="50"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="7">
								<input type="hidden" name="dn" value="save">
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
		 * Добавить страну, сохранение
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'save')
		{
			global $countryname, $icon, $iso, $iso2, $iso3;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					$lang['add_country']
				);

			$countryname = preparse($countryname, THIS_TRIM, 0, 128);
			$icon = preparse($icon, THIS_TRIM, 0, 255);
			$iso = preparse($iso, THIS_INT);
			$iso2 = preparse($iso2, THIS_TRIM, 0, 2);
			$iso3 = preparse($iso3, THIS_TRIM, 0, 3);

			if (preparse($countryname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['add_country'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$db->query("
						INSERT INTO ".$basepref."_country VALUES (
						NULL,
						'".$db->escape(preparse_sp($countryname))."',
						'".$db->escape($icon)."',
						'".$db->escape($iso2)."',
						'".$db->escape($iso3)."',
						'".$db->escape($iso)."',
						'0'
						)");
			}

			$cache = new DN\Cache\CacheCountry;
			$cache->cachecountry();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать страну
		 ------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $id;

			$id = preparse($id, THIS_INT);

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['list_country'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE countryid = '".$db->escape($id)."'"));

			echo '	<div class="section">
					<form action="index.php?ops='.$sess['hash'].'&amp;dn=editsave" method="post">
					<table class="work">
						<caption>'.$lang['country'].': '.$item['countryname'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="countryname" size="50" value="'.$item['countryname'].'" required="required">
							</td>
						</tr>
						<tr>
							<td>'.$lang['all_icon'].'</td>
							<td>
								<input name="icon" id="icon" size="50" type="text" value="'.$item['icon'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/country/\',\'&field[1]=icon\')" value=" '.$lang['filebrowser'].' " type="button">
							</td>
						</tr>
						<tr>
							<td>ISO</td>
							<td>
								<input type="text" name="iso" maxlength="11" size="50" value="'.$item['iso'].'">
							</td>
						</tr>
						<tr>
							<td>ISO 2</td>
							<td>
								<input type="text" name="iso2" maxlength="2" size="50" value="'.$item['iso2'].'">
							</td>
						</tr>
						<tr>
							<td>ISO 3</td>
							<td>
								<input type="text" name="iso3" maxlength="3" size="50" value="'.$item['iso3'].'">
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="id" value="'.$item['countryid'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать страну, сохранение
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $id, $countryname, $icon, $iso, $iso2, $iso3;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['list_country'].'</a>',
					$lang['all_edit']
				);

			$id = preparse($id, THIS_INT);
			$countryname = preparse($countryname, THIS_TRIM, 0, 128);
			$icon = preparse($icon, THIS_TRIM, 0, 255);
			$iso = preparse($iso, THIS_INT);
			$iso2 = preparse($iso2, THIS_TRIM, 0, 2);
			$iso3 = preparse($iso3, THIS_TRIM, 0, 3);

			if (preparse($countryname, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['country'], $lang['all_edit'], $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$db->query
					(
						"UPDATE ".$basepref."_country SET
						 countryname	= '".$db->escape(preparse_sp($countryname))."',
						 icon			= '".$db->escape($icon)."',
						 iso2			= '".$db->escape(preparse_sp($iso2))."',
						 iso3			= '".$db->escape(preparse_sp($iso3))."',
						 iso				= '".$db->escape(preparse_sp($iso))."'
						 WHERE countryid = '".$id."'"
					);
			}

			$cache = new DN\Cache\CacheCountry;
			$cache->cachecountry();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить страну
		 -------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['list_country'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_country WHERE countryid = '".$id."'");
				$db->query("DELETE FROM ".$basepref."_country_region WHERE countryid = '".$id."'");

				$db->increment('country');
				$db->increment('country_region');

				$cache = new DN\Cache\CacheCountry;
				$cache->cachecountry();

				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE countryid = '".$id."'"));

				$yes = 'index.php?dn=del&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=index&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $item['countryname'], $yes, $not, $item['countryname']);
				$tm->footer();
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Регионы
		 -----------*/
		if ($_REQUEST['dn'] == 'region')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE countryid = '".$db->escape($id)."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					'<a href="index.php?dn=region&amp;id='.$id.'&amp;ops='.$sess['hash'].'">'.$item['countryname'].'</a>',
					$lang['states']
				);

			$tm->header();

			$region = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_country_region WHERE countryid = '".$item['countryid']."' ORDER BY posit ASC");
			while ($items = $db->fetchrow($inq)) {
				$region[$items['regionid']] = $items;
			}

			if (sizeof($region) > 0)
			{
				echo '	<div class="section">
						<form action="index.php?dn=upregion&amp;id='.$item['countryid'].'&amp;ops='.$sess['hash'].'" method="post">
						<table id="list" class="work">
							<caption>'.$item['countryname'].': '.$lang['states'].'</caption>
							<tr>
								<th>'.$lang['all_name'].'</th>
								<th>'.$lang['all_posit'].'</th>
								<th>'.$lang['sys_manage'].'</th>
							</tr>';
				foreach ($region as $k => $v)
				{
					echo '	<tr class="list">
								<td>
									<input class="width" value="'.$v['regionname'].'" name="region['.$k.'][name]" size="15" type="text">
								</td>
								<td>
									<input value="'.$v['posit'].'" name="region['.$k.'][posit]" size="3" maxlength="3" type="text">
								</td>
								<td class="gov">
									<a href="index.php?dn=regiondel&amp;cid='.$item['countryid'].'&amp;id='.$k.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
				echo '		<tr class="tfoot">
								<td colspan="3">
									<input class="main-button" value="'.$lang['all_save'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>
					<div class="pad"></div>';
			}

			echo '	<div class="section">
					<form action="index.php?dn=regionaddsave&amp;id='.$item['countryid'].'&amp;ops='.$sess['hash'].'" method="post">
					<table class="work">
						<caption>'.$item['countryname'].': '.$lang['add_state'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="regionname" size="50" required="required"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input class="main-button" value="'.$lang['all_add'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить регион
		 ------------------*/
		if ($_REQUEST['dn'] == 'regionadd')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					$lang['add_state']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_state'].'</caption>
						<tr>
							<td>'.$lang['country'].'</td>
							<td>
								<select name="id" style="width: 290px;">';
			$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
			while ($item = $db->fetchrow($inq)) {
				echo '				<option value="'.$item['countryid'].'"'.(($item['iso2'] == 'RU') ? ' selected' : '').'>'.$item['countryname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="regionname" size="50" required="required"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="7">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="regionaddsave">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить регион, сохранение
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'regionaddsave')
		{
			global $regionname, $id;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					$lang['add_state']
				);

			$regionname = preparse($regionname, THIS_TRIM, 0, 64);
			$id = preparse($id, THIS_INT);

			$inq = $db->query("SELECT countryid FROM ".$basepref."_country");
			$country = array();
			while ($item = $db->fetchrow($inq)) {
				$country[$item['countryid']] = $item['countryid'];
			}

			if (preparse($regionname, THIS_EMPTY) == 1 OR ! isset($country[$id]))
			{
				$tm->header();
				$tm->error($lang['add_state'], null, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_country_region VALUES (
						 NULL,
						 '".$db->escape($id)."',
						 '".$db->escape(preparse_sp($regionname))."',
						 '0'
						 )"
					);
			}

			$cache = new DN\Cache\CacheCountry;
			$cache->cachecountry();

			redirect('index.php?dn=region&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Обновить регионы
		 --------------------*/
		if ($_REQUEST['dn'] == 'upregion')
		{
			global $region, $id;

			$id = preparse($id, THIS_INT);

			if (is_array($region))
			{
				foreach ($region as $k => $v)
				{
					if (isset($v['name']))
					{
						$n = preparse($v['name'], THIS_TRIM, 0, 64);
						if (preparse($n, THIS_EMPTY) == 0)
						{
							$p = (isset($v['posit'])) ? intval($v['posit']) : 0;
							$db->query
								(
									"UPDATE ".$basepref."_country_region SET
									 regionname = '".$db->escape(preparse_sp($n))."',
									 posit = '".$db->escape($p)."'
									 WHERE regionid = '".intval($k)."'"
								);
						}
					}
				}
			}

			$cache = new DN\Cache\CacheCountry;
			$cache->cachecountry();

			redirect('index.php?dn=region&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить регион
		 ------------------*/
		if ($_REQUEST['dn'] == 'regiondel')
		{
			global $id, $cid, $ok;

			$id = preparse($id, THIS_INT);
			$cid = preparse($cid, THIS_INT);

			$items = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country WHERE countryid = '".$db->escape($cid)."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_geo'].'</a>',
					'<a href="index.php?dn=region&amp;id='.$cid.'&amp;ops='.$sess['hash'].'">'.$items['countryname'].'</a>',
					$lang['states'],
					$lang['all_delet']
				);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_country_region WHERE regionid = '".$id."'");
				$db->increment('country_region');

				$cache = new DN\Cache\CacheCountry;
				$cache->cachecountry();

				redirect('index.php?dn=region&amp;id='.$cid.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_country_region WHERE regionid = '".$id."'"));

				$yes = 'index.php?dn=regiondel&amp;cid='.$cid.'&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=region&amp;id='.$cid.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['regionname']), $yes, $not, preparse_un($item['regionname']));
				$tm->footer();
			}

			redirect('index.php?dn=region&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['menu_geo'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
