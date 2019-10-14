<?php
/**
 * File:        /admin/system/options/index.php
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
	global $ADMIN_ID, $CHECK_ADMIN, $IPS, $db, $basepref, $tm, $conf, $wysiwyg, $lang, $sess, $ops, $cache;

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
			$lang['options']
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
				'index', 'sitesave',
				'mod', 'modinfo', 'modsave', 'modadd', 'modaddsave', 'modedit', 'modeditsave', 'moddel', 'fulldel', 'addlabel', 'dellabel',
				'upload', 'uploadsave', 'search', 'searchsave',
				'tempmod', 'tempmodsave', 'tempedit', 'tempeditsave',
				'mail', 'mailsave', 'upmime', 'addmime',
				'time', 'timesave'
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

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['opt_set'].'</a>'
					.'<a'.cho('mod, modedit, modinfo, modadd, moddel, fulldel').' href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>'
					.'<a'.cho('tempmod').' href="index.php?dn=tempmod&amp;ops='.$sess['hash'].'">'.$lang['mod_template'].'</a>'
					.'<a'.cho('tempedit').' href="index.php?dn=tempedit&amp;ops='.$sess['hash'].'">'.$lang['site_temp'].'</a>'
					.'<a'.cho('upload').' href="index.php?dn=upload&amp;ops='.$sess['hash'].'">'.$lang['image_upload'].'</a>'
					.'<a'.cho('search').' href="index.php?dn=search&amp;ops='.$sess['hash'].'">'.$lang['set_search'].'</a>'
					.'<a'.cho('mail').' href="index.php?dn=mail&amp;ops='.$sess['hash'].'">'.$lang['mail'].'</a>'
					.'<a'.cho('time').' href="index.php?dn=time&amp;ops='.$sess['hash'].'">'.$lang['opt_time_cookie'].'</a>';

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
		 * Настройки сайта
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['opt_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['options'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");
			while ($itemset = $db->fetchrow($inqset))
			{
				if ($itemset['setname'] <> 'lastup')
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
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="sitesave">
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
		if ($_REQUEST['dn'] == 'sitesave')
		{
			global $set, $conf, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['opt_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['opt_set'], $lang['all_save'], $lang['forgot_name'].'<div class="black">'.$lang[$item['setlang']].'</div>');
						$tm->footer();
					}

					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}

					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			$cache_login = new DN\Cache\CacheLogin;
			$cache_login->cachelogin();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Моды
		 */
		if ($_REQUEST['dn'] == 'mod')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['opt_manage_mod']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['opt_manage_mod'].'</caption>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_status'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['all_mod'].'</th>
							<th class="ac">'.$lang['all_map'].'</th>
							<th class="ac">'.$lang['seo_link'].'</th>
							<th class="ac">'.$lang['site_map'].'</th>
							<th class="ac">'.$lang['sys_manage'].'</th>
						</tr>';

			$workmod = $clonmod = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_mods ORDER BY posit");

			while ($item = $db->fetchrow($inq))
			{
				if ($item['active'] == 'no')
					$class = 'no-active';
				elseif ($item['parent'] > 0)
					$class = 'selective';
				else
					$class = '';

				$workmod[$item['file']] = $item['file'];
				if ($item['parent'] > 0)
				{
					$clonmod[] = $item['file'];
				}
				echo '	<tr class="list">
							<td class="'.$class.' al">
								<input type="text" name="upname['.$item['id'].']" size="40" value="'.preparse_un($item['name']).'" required="required" />
							</td>
							<td class="'.$class.'">';
				if ($item['file'] == $conf['site_home']) {
					echo '		<span class="server">'.$lang['mess_home_mod'].'</span>';
				} else {
					echo '		'.$lang['included'].' <input type="radio" name="active['.$item['id'].']" value="yes"'.(($item['active'] == 'yes') ? ' checked' : '').'> &nbsp;
								'.$lang['not_included'].' <input type="radio" name="active['.$item['id'].']" value="no"'.(($item['active'] == 'no') ? ' checked' : '').'>';
				}
				echo '		</td>
							<td class="'.$class.'">
								<input type="text" name="posit['.$item['id'].']" size="3" maxlength="3" value="'.preparse_un($item['posit']).'" />
							</td>
							<td class="'.$class.'">
								'.$item['file'].'
							</td>
							<td class="'.$class.' ac">
								<input name="actmap[]" value="'.$item['id'].'" type="checkbox"'.(($item['actmap'] == 'yes') ? ' checked="checked"' : '').' />
							</td>
							<td class="'.$class.' ac">
								<input name="linking[]" value="'.$item['id'].'" type="checkbox"'.(($item['linking'] == 'yes') ? ' checked="checked"' : '').' />
							</td>
							<td class="'.$class.' ac">
								<input name="sitemap[]" value="'.$item['id'].'" type="checkbox"'.(($item['sitemap'] == 'yes') ? ' checked="checked"' : '').' />
							</td>
							<td class="gov sw100 ac '.$class.'">';
				if ($item['parent'] > 0) {
					echo '<span class="site">&#8712; pages &nbsp;&nbsp;</span>';
				} else {
					echo '		<a class="edit" href="index.php?dn=modedit&amp;id='.$item['id'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a class="info" href="index.php?dn=modinfo&amp;file='.$item['file'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/info.png" alt="'.$lang['all_info'].'" /></a>';
				}
				if ($item['file'] <> $conf['site_home']) {
					echo '		<a class="del" href="index.php?dn=moddel&amp;id='.$item['id'].'&amp;file='.$item['file'].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" /></a>';
				}
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="8">
								<input type="hidden" name="dn" value="modsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$listing = $selmod = $is_mod = NULL;
			$modsdir = opendir(WORKDIR.'/mod/');
			while ($name = readdir($modsdir))
			{
				if ($name != '.' AND $name != '..' AND is_dir(WORKDIR.'/mod/'.$name)) {
					$listing[] = $name;
				}
			}
			closedir($modsdir);

			sort($listing);
			for ($i = 0; $i < sizeof($listing); $i ++)
			{
				if( ! empty($listing[$i]) AND ( ! in_array($listing[$i], $workmod)))
				{
					$selmod.= '<tr class="list">
									<td class="site strong">'.(isset($lang[$listing[$i]]) ? $lang[$listing[$i]] : $listing[$i]).'</td>
									<td class="al">
										<a class="side-button" href="index.php?dn=modadd&amp;file='.$listing[$i].'&amp;ops='.$sess['hash'].'">'.$lang['all_submint'].'</a>
									</td>
									<td class="al">'.$listing[$i].'</td>
									<td class="gov">
										<a class="info" href="index.php?dn=modinfo&amp;file='.$listing[$i].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_info'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/info.png" /></a>
										<a class="del" href="index.php?dn=fulldel&amp;file='.$listing[$i].'&amp;ops='.$sess['hash'].'"><img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" /></a>
									</td>
								</tr>';
				}
				if( ! empty($listing[$i]) AND (in_array($listing[$i], $workmod)) AND (in_array($listing[$i], $clonmod)))
				{
					$is_mod.= '<mark class="pad alternative">'.(isset($lang[$listing[$i]]) ? $lang[$listing[$i]] : $listing[$i]).'</mark><br>';
				}
			}

			if ( ! empty($selmod) OR ! empty($is_mod))
			{
				echo '	<div class="pad"></div>
						<div class="section">
						<table id="list" class="work">
							<caption>'.$lang['mod_new'].'</caption>
							<tr>
								<th class="ar pw20">'.$lang['all_name'].'</th>
								<th class="pw35"></th>
								<th class="pw35">'.$lang['all_mod'].'</th>
								<th class="al">'.$lang['sys_manage'].'</th>
							</tr>';
				echo	( ! empty($selmod)) ? $selmod : '';
				if ( ! empty($is_mod))
				{
					echo '	<tr class="list">
								<td class="pw20">'.$is_mod.'</td>
								<td colspan="2">'.$lang['mod_error_isset'].'</td>
							</tr>';
				}
				echo '	</table>
						</div>';
			}
			echo '	<div class="pad"></div>
					<div class="section">
						<table id="del" class="work">
							<caption>'.$lang['lock_mod'].'</caption>
							<tr>
								<td>';
									$lockmod = '';
									foreach ($MOD_LOCK as $name) {
										$lockmod.= $name.', ';
									}
									echo chop($lockmod, ', ');
			echo '				<div class="upad alternative">'.$lang['lock_mod_help'].'</div></td>
							</tr>
						</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Информация о моде
		 */
		if ($_REQUEST['dn'] == 'modinfo')
		{
			global $file;

			$name_mod = isset($lang[$file]) ? $lang[$file] : $file;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['options'],
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$name_mod
				);

			$tm->header();

			$info = ADMDIR.'/mod/'.$file.'/install/README.md';
			if (file_exists($info))
			{
				$string = file_get_contents($info);
				$print = Markdown::defaultTransform($string);
			}

			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['mod_decs'].': '.$name_mod.'</caption>
						<tr>
							<td class="sw50">'.$lang['all_decs'].'</td>
							<td class="pw93">
								<div class="markdown">
									'.(isset($print) ? $print : $lang['data_not']).'
								</div>
							</td>
						</tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление мода
		 */
		if ($_REQUEST['dn'] == 'modadd')
		{
			global $file;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$lang['add_mods']
				);

			$tm->header();

			$scheme = ADMDIR.'/mod/'.$file.'/install/mod.scheme.php';
			if (file_exists($scheme))
			{
				include($scheme);
			}

			$name_mod = isset($lang[$file]) ? $lang[$file] : $file;

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_mod'].': '.preparse_un($name_mod).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="name" size="70" value="'.$name_mod.'" required="required" /></td>
						</tr>
						<tr>
							<td>'.$lang['custom_title'].'</td>
							<td><input type="text" name="custom" size="70" value="" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_descript'].'</td>
							<td><input type="text" name="descript" size="70" value="" /></td>
						</tr>
						<tr>
							<td>'.$lang['all_keywords'].'</td>
							<td>
								<input type="text" name="keywords" size="70" value="" />';
								$tm->outhint($lang['keyword_hint']);
            echo '		</td>
						</tr>
						<tr>
							<td>'.$lang['all_decs'].'</td>
							<td>';
								$tm->textarea('map', 5, 50, $item['map'], 1);
			echo '			</td>
						</tr>';
			$cont_mod = ADMDIR.'/mod/'.$file.'/install/sql/content.sql';
			if (file_exists($cont_mod))
			{
				echo '	<tr>
							<td>'.$lang['demo_content'].'</td>
							<td><input type="checkbox" name="demo" value="yes" checked></td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['own_lang'].'</td>
							<td><input type="checkbox" name="own_lang" value="1" checked>';
								$tm->outhint($lang['own_lang_help']);
            echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['lang_mod'].'</td>
							<td>
								<select name="lang_mod" class="sw150">';
			$array_lang = glob(ADMDIR.'/mod/'.$file.'/install/lang*.xml');
			foreach ($array_lang as $lang_path)
			{
				echo '				<option value="'.$lang_path.'">'.basename($lang_path, '.xml').'</option>';
			}
			echo '				</select>';
								$tm->outhint($lang['lang_mod_help']);
            echo '			</td>
						</tr>';
			if (isset($chmod) AND is_array($chmod) AND ! empty($chmod))
			{
				echo '	<tr>
							<th></th><th class="site" colspan="2">'.$lang['file_writable'].'</th>
						</tr>
						<tr>
							<td></td>
							<td class="scheme vt">';
				$file_chmod = TRUE;
				echo '			<ol>';
				foreach ($chmod as $path)
				{
					if (is_writable(WORKDIR.$path))
					{
						$name = basename($path);
						$file_mod = preg_replace('/\b('.$name.')\b/i', '<span class="green bold">'.$name.'</span>', $path);
						echo '		<li><span class="gray">OK &nbsp; </span>'.$file_mod.'</li>';
					} else {
						$not = ( ! is_dir(WORKDIR.$path)) ? '<span class="red"> &nbsp; Not exist!</span>' : '';
						$name = basename($path);
						$file_mod = preg_replace('/\b('.$name.')\b/i', '<span class="red bold">'.$name.'</span>', $path);
						echo '		<li><span class="red">NO &nbsp; </span>'.$file_mod.$not.'</li>';
						$file_chmod = FALSE;
					}
				}
				echo '			</ol>
							</td>
						</tr>';
			}
			if (isset($file_chmod))
			{
				echo '	<tr>
							<td class="first">'.(($file_chmod) ? '<span class="green bold">*</span>' : '<span class="red bold">*</span>').'</td>
							<td class="is gray first" style="line-height: 1.7em;">';
								echo ($file_chmod) ? $lang['ok_write'] : $lang['no_write'];
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="modaddsave">
								<input type="hidden" name="file" value="'.$file.'">
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
		 * Добавление мода (сохранение)
		 */
		if ($_REQUEST['dn'] == 'modaddsave')
		{
			global $file, $name, $custom, $keywords, $descript, $map, $demo, $own_lang, $lang_mod, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$lang['add_mods']
				);

			function file_read($files)
			{
				$read = fopen($files, "r");
				$contents = fread($read, filesize($files));
				fclose($read);
				return $contents;
			}

			// Зарезервированные имена
			if (in_array($file, $MOD_LOCK))
			{
					$tm->header();
					$tm->error($lang['add_mods'], $file, $lang['lock_mod_error']);
					$tm->footer();
			}

			// Файл с данными
			$scheme = ADMDIR.'/mod/'.$file.'/install/mod.scheme.php';
			if (file_exists($scheme))
			{
				include($scheme);
			}

			// Добавляем языки
			$setid = 0;
			if (file_exists($lang_mod))
			{
				$lg = new Lang($own_lang);
				$lg->imp_group($lang_mod, 0);
				$setid = $lg->setid;

				$cache_lang = new DN\Cache\CacheLang;
				$cache_lang->cachelang();

			}

			// Добавляем мод
			if (preparse($name, THIS_EMPTY) == 0)
			{
				$inq = $db->query("SELECT id FROM ".$basepref."_mods WHERE file = '".$db->escape($file)."'");
				if ($db->numrows($inq) > 0)
				{
					$tm->header();
					$tm->error($lang['add_mods'], $file, $lang['mod_error_isset']);
					$tm->footer();
				}

				$label = Json::encode($label);
				$db->query
					(
						"INSERT INTO ".$basepref."_mods VALUES (
						 NULL,
						 '".$db->escape($file)."',
						 '".$db->escape(preparse_sp($name))."',
						 '".$db->escape(preparse_sp($custom))."',
						 '".$db->escape(preparse_sp($keywords))."',
						 '".$db->escape(preparse_sp($descript))."',
						 '".$db->escape(preparse_sp($map))."',
						 '".$conf['site_temp']."',
						 '0',
						 '".$label."',
						 'no',
						 'no',
						 '0',
						 'no',
						 'no',
						 '".$setid."'
						 )"
					);
			}
			else
			{
				$tm->header();
				$tm->error($lang['add_mods'], $file, $lang['forgot_name']);
				$tm->footer();
			}

			// Создаем таблицы
			$tab_mod = ADMDIR.'/mod/'.$file.'/install/sql/tables.sql';
			if (file_exists($tab_mod))
			{
				$tab_array = explode("--", file_read($tab_mod));
				foreach ($tab_array as $create)
				{
					if (trim($create) != "") {
						$string = str_replace(array('{pref}', '{mod}'), array($basepref, $file), $create);
						$db->query($string, 0);
					}
				}
			}

			// Добавляем контент
			$cont_mod = ADMDIR.'/mod/'.$file.'/install/sql/content.sql';
			if (file_exists($cont_mod))
			{
				$cont_array = explode(PHP_EOL, file_read($cont_mod));
				if (isset($demo) AND ! empty($cont_array))
				{
					foreach ($cont_array as $insert)
					{
						if (trim($insert) != "") {
							$string = str_replace(array('{pref}', '{mod}', '{time}'), array($basepref, $file, NEWTIME), $insert);
							$db->query($string, 0);
						}
					}
				}
			}

			// Добавляем настройки
			$set_mod = ADMDIR.'/mod/'.$file.'/install/sql/setting.sql';
			if (file_exists($set_mod))
			{
				$set_array = explode(PHP_EOL, file_read($set_mod));
				$db->query("DELETE FROM ".$basepref."_settings WHERE setopt = '".$file."'");
				$db->increment('settings');

				foreach ($set_array as $insert)
				{
					if (trim($insert) != "") {
						$string = str_replace(array('{pref}', '{mod}'), array($basepref, $file), $insert);
						$db->query($string, 0);
					}
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mod&amp;ops='.$sess['hash']);
		}

		/**
		 * Сохранение модов
		 */
		if ($_REQUEST['dn'] == 'modsave')
		{
			global $active, $upname, $posit, $actmap, $linking, $sitemap, $sess;

			if (preparse($actmap, THIS_ARRAY) == 1 AND preparse($actmap, THIS_EMPTY) == 0)
			{
				$db->query("UPDATE ".$basepref."_mods SET actmap = 'yes' WHERE id IN (".join($actmap, ',').")");
				$db->query("UPDATE ".$basepref."_mods SET actmap = 'no' WHERE id NOT IN (".join($actmap, ',').")");
			} else {
				$db->query("UPDATE ".$basepref."_mods SET actmap = 'no'");
			}

			if (preparse($linking, THIS_ARRAY) == 1 AND preparse($linking, THIS_EMPTY) == 0)
			{
				$db->query("UPDATE ".$basepref."_mods SET linking = 'yes' WHERE id IN (".join($linking, ',').")");
				$db->query("UPDATE ".$basepref."_mods SET linking = 'no' WHERE id NOT IN (".join($linking, ',').")");
			} else {
				$db->query("UPDATE ".$basepref."_mods SET linking = 'no'");
			}

			if (preparse($sitemap, THIS_ARRAY) == 1 AND preparse($sitemap, THIS_EMPTY) == 0)
			{
				$db->query("UPDATE ".$basepref."_mods SET sitemap = 'yes' WHERE id IN (".join($sitemap, ',').")");
				$db->query("UPDATE ".$basepref."_mods SET sitemap = 'no' WHERE id NOT IN (".join($sitemap, ',').")");
			} else {
				$db->query("UPDATE ".$basepref."_mods SET sitemap = 'no'");
			}

			if (is_array($active) AND ! empty($active))
			{
				foreach ($active as $id => $result)
				{
					$id = preparse($id, THIS_INT);
					$db->query("UPDATE ".$basepref."_mods SET active = '".$db->escape($result)."' WHERE id = '".$id."'");
				}
			}

			foreach ($upname as $id => $result)
			{
				$id = preparse($id, THIS_INT);
				$result = preparse($result, THIS_ADD_SLASH, 0, 255);
				$db->query("UPDATE ".$basepref."_mods SET name = '".$db->escape(preparse_sp($result))."' WHERE id = '".$id."'");
			}

			foreach ($posit as $id => $result)
			{
				$id = preparse($id, THIS_INT);
				$result = preparse($result, THIS_INT);
				$db->query("UPDATE ".$basepref."_mods SET posit = '".$result."' WHERE id = '".$id."'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mod&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление мода
		 */
		if ($_REQUEST['dn'] == 'moddel')
		{
			global $id, $file, $ok, $IPS;

			$name_mod = isset($lang[$file]) ? $lang[$file] : $file;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['options'],
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$name_mod
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{

				// Файл с данными
				$scheme = ADMDIR.'/mod/'.$file.'/install/mod.scheme.php';
				if (file_exists($scheme))
				{
					include($scheme);
				}

				// Удаляем настройки
				$db->query("DELETE FROM ".$basepref."_settings WHERE setopt = '".$file."'");
				$db->increment('settings');

				// Удаляем комментарии
				$db->query("DELETE FROM ".$basepref."_comment WHERE file = '".$file."'");
				$db->increment('comment');

				// Удаляем отзывы
				$db->query("DELETE FROM ".$basepref."_reviews WHERE file = '".$file."'");
				$db->increment('comment');

				// Удаляем рейтинги
				$db->query("DELETE FROM ".$basepref."_rating WHERE file = '".$file."'");
				$db->increment('rating');

				// Удаляем перелинковку
				$db->query("DELETE FROM ".$basepref."_seo_anchor WHERE mods = '".$file."'");
				$db->increment('seo_anchor');

				// Удаляем языки
			 	$setid = $db->fetchassoc($db->query("SELECT langsetid FROM ".$basepref."_mods WHERE id = '".$id."'"));
				if ($setid['langsetid'] > 0)
				{
					$db->query("DELETE FROM ".$basepref."_language WHERE langsetid = '".$setid['langsetid']."'");
					$db->query("DELETE FROM ".$basepref."_language_setting WHERE langsetid = '".$setid['langsetid']."'");
					$db->increment('language');
					$db->increment('language_setting');

					$cache_lang = new DN\Cache\CacheLang;
					$cache_lang->cachelang();
				}

				// Если это основной мод, и он имеет алиасы, удаляем все дочерние моды
				$inq = $db->query("SELECT * FROM ".$basepref."_mods WHERE parent = '".$id."'");
				if ($db->numrows($inq) > 0)
				{
					$pa = new Pages();

					// Удаляем мод
					$db->query("DELETE FROM ".$basepref."_mods WHERE id = '".$id."'");
					while ($item = $db->fetchrow($inq))
					{
						// Удаляем дочерние моды
						$db->query("DELETE FROM ".$basepref."_mods WHERE id = '".$item['id']."'");

						// Удаляем следы
						$pages_mod = $IPS[$item['id']]['mod'];
						$db->query("DELETE FROM ".$basepref."_pages WHERE mods = '".$pages_mod."'");
						$db->increment('pages');

						// Удаляем настройки
						$db->query("DELETE FROM ".$basepref."_settings WHERE setopt = '".$pages_mod."'");
						$db->increment('settings');

						$pa->undir(WORKDIR.'/cache/pages/'.$pages_mod);
						$pa->undir(WORKDIR.'/up/pages/'.$pages_mod);

						unset($IPS[$item['id']]);
						$ins = Json::encode($IPS);
						$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mods'");
					}
				}
				else
				{
					$db->query("DELETE FROM ".$basepref."_mods WHERE id = '".$id."'");
				}

				$db->increment('mods');

				// Если это дочерний мод, удаляем все следы
				if (array_key_exists($id, $IPS))
				{
					$pa = new Pages();

					$mod = $IPS[$id]['mod'];
					$db->query("DELETE FROM ".$basepref."_pages WHERE mods = '".$mod."'");
					$db->increment('pages');

					$pa->undir(WORKDIR.'/cache/pages/'.$mod);
					$pa->undir(WORKDIR.'/up/pages/'.$mod);

					unset($IPS[$id]);
					$ins = Json::encode($IPS);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mods'");
				}

				// Удаляем таблицы
				foreach ($tables as $val)
				{
					if ( ! empty($val)) {
						$db->query("DROP TABLE IF EXISTS ".$basepref."_".$val);
					}
				}

				$cache->cachesave(1);
				redirect('index.php?dn=mod&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE id = '".$id."'"));

				$yes = 'index.php?dn=moddel&amp;id='.$id.'&amp;file='.$file.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=mod&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_mod'], preparse_un($item['name']), $yes, $not, $lang['del_bd_mod']);
				$tm->footer();
			}
		}

		/**
		 * Удаление мода
		 */
		if ($_REQUEST['dn'] == 'fulldel')
		{
			global $file;

			$name_mod = isset($lang[$file]) ? $lang[$file] : $file;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$name_mod
				);

			$tm->header();

			$scheme = ADMDIR.'/mod/'.$file.'/install/mod.scheme.php';
			if (file_exists($scheme))
			{
				include($scheme);
			}

			$name_mod = isset($lang[$file]) ? $lang[$file] : $file;

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['del_mod'].': '.$name_mod.'</caption>
						<tr>
							<th class="ar"><strong>'.$file.'</strong></th>
							<th class="server" colspan="2">'.$lang['mod_files'].'</th>
						</tr>
						<tr>
							<td></td>
							<td class="scheme vt">';
			if (isset($filelist) AND is_array($filelist))
			{
				echo '			<ol>';
				foreach ($filelist as $path)
				{
					$name = basename($path);
					$file_mod = preg_replace('/\b('.$name.')\b/i', '<span class="red bold">'.$name.'</span>', $path);
					echo '			<li>'.$file_mod.'</li>';
				}
				echo '			</ol>';
			}
			else
			{
				echo '	<span class="gray">'.$lang['data_not'].'</span>';
			}
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span></td>
							<td class="is first" style="line-height: 1.7em;">'.$lang['help_remove_mod'].'</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">&nbsp;</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактирование мода
		 */
		if ($_REQUEST['dn'] == 'modedit')
		{
			global $id;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE id = '".$id."'"));

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					preparse_un($item['name'])
				);

			$tm->header();

			$scheme = ADMDIR.'/mod/'.$item['file'].'/install/mod.scheme.php';

			if (file_exists($scheme))
			{
				include($scheme);
			}
echo preparse_un($item['name']);
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.preparse_un($item['name']).'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="name" size="70" value="'.preparse_sp($item['name']).'" required="required" /></td>
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
						<tr>
							<td>'.$lang['block_label'].'</td>
							<td>
								<table class="work">';
			$label = Json::decode($item['label']);
			if (is_array($label))
			{
				foreach ($label as $k => $v)
				{
					echo '			<tr>
										<td class="vm ac">'.$lang['all_file'].' - <strong>'.$k.'</strong></td>
										<td class="al bg-null">';
					if (is_array($v))
					{
						foreach ($v as $sk => $sv)
						{
							echo '			<div class="blocking">'.$sk.' <a href="index.php?dn=dellabel&amp;filename='.$k.'&amp;labelname='.$sk.'&amp;id='.$id.'&amp;ops='.$sess['hash'].'">
												<img alt="'.$lang['all_delet'].'" src="'.ADMPATH.'/template/images/close.gif"></a>
											</div>';
						}
					}
					echo '				</td>
									</tr>';
				}
			}
			echo '				</table>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="dn" value="modeditsave">
								<input type="hidden" name="file" value="'.$item['file'].'">
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
						<caption>'.$lang['block_label'].': '.$lang['all_submint'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_file'].'</td>
							<td>
								<input type="text" name="filename" size="50" required="required">';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="labelname" size="50" required="required">';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="addlabel">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="id" value="'.$item['id'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
			if (isset($chmod) AND is_array($chmod) AND ! empty($chmod))
			{
				echo '	<div class="pad"></div>
						<div class="section">
						<table class="work">
							<caption>'.$lang['rights_write'].'</caption>
							<tr>
								<th></th><th class="site" colspan="2">'.$lang['file_writable'].'</th>
							</tr>
							<tr>
								<td></td>
								<td class="scheme vt">';
				$file_chmod = TRUE;
				echo '				<ol>';
				foreach ($chmod as $path)
				{
					if (is_writable(WORKDIR.$path))
					{
						$name = basename($path);
						$file_mod = preg_replace('/\b('.$name.')\b/i', '<span class="green bold">'.$name.'</span>', $path);
						echo '			<li><span class="gray">OK &nbsp; </span>'.$file_mod.'</li>';
					} else {
						$name = basename($path);
						$file_mod = preg_replace('/\b('.$name.')\b/i', '<span class="red bold">'.$name.'</span>', $path);
						echo '			<li><span class="gray">NO &nbsp; </span>'.$file_mod.'</li>';
						$file_chmod = FALSE;
					}
				}
				echo '				</ol>
								</td>
							</tr>';
				if (isset($file_chmod))
				{
					echo '	<tr>
								<td class="first">'.(($file_chmod) ? '<span class="green bold">*</span>' : '<span class="red bold">*</span>').'</td>
								<td class="is gray first" style="line-height: 1.7em;">';
									echo ($file_chmod) ? $lang['ok_write'] : $lang['no_write'];
					echo '		</td>
							</tr>';
				}
				echo '		<tr class="tfoot">
								<td colspan="2">&nbsp;</td>
							</tr>
						</table>
						</div>';
			}

			$tm->footer();
		}

		/**
		 * Сохранение редактирование мода
		 */
		if ($_REQUEST['dn'] == 'modeditsave')
		{
			global $id, $file, $name, $custom, $keywords, $descript, $map, $label;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$file
				);

			$id = preparse($id, THIS_INT);

			if (preparse($name, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['all_edit'], $file, $lang['forgot_name']);
				$tm->footer();
			}
			else
			{
				if ($id)
				{
					$db->query
						(
							"UPDATE ".$basepref."_mods SET
							 name   = '".$db->escape(preparse_sp($name))."',
							 custom  = '".$db->escape(preparse_sp($custom))."',
							 keywords  = '".$db->escape(preparse_sp($keywords))."',
							 descript  = '".$db->escape(preparse_sp($descript))."',
							 map = '".$db->escape($map)."'
							 WHERE id = '".$id."'
							");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mod&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление меток модов
		 */
		if ($_REQUEST['dn'] == 'dellabel')
		{
			global $id, $filename, $labelname;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE id = '".$id."'"));

			if (preparse($filename, THIS_SYMNUM) == 0 AND preparse($labelname, THIS_SYMNUM) == 0)
			{
				$label = Json::decode($item['label']);
				if (is_array($label))
				{
					if (isset($label[$filename][$labelname]))
					{
						unset($label[$filename][$labelname]);
						$label = Json::encode(array_filter($label));
						$db->query("UPDATE ".$basepref."_mods SET label = '".$db->escape($label)."' WHERE id = '".$id."'");
					}
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=modedit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление меток модов
		 */
		if ($_REQUEST['dn'] == 'addlabel')
		{
			global $id, $filename, $labelname;

			$id = preparse($id, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_mods WHERE id = '".$id."'"));

			if (preparse($filename, THIS_SYMNUM) == 0 AND preparse($labelname, THIS_SYMNUM) == 0)
			{
				$label = Json::decode($item['label']);
				if (is_array($label))
				{
					if (isset($label[$filename])) {
						$label = array_merge_recursive($label, array($filename => array($labelname => 1)));
					} else {
						$label[$filename] = array($labelname => 1);
					}
					$label = Json::encode($label);
				} else {
					$label = Json::encode(array($filename => array($labelname => 1)));
				}
				$db->query("UPDATE ".$basepref."_mods SET label = '".$db->escape($label)."' WHERE id = '".$id."'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=modedit&amp;id='.$id.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Оформление модов
		 ----------------------*/
		if ($_REQUEST['dn'] == 'tempmod')
		{
			global $temp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					'<a href="index.php?dn=mod&amp;ops='.$sess['hash'].'">'.$lang['opt_manage_mod'].'</a>',
					$lang['all_temp']
				);

			$tm->header();

			$sel = array();
			$dir = new GlobIterator(WORKDIR.'/template/*');
			foreach ($dir as $file)
			{
				if ($file->isDir()) {
					$sel[]= $file->getFilename();
				}
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['mod_template'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_name'].'</th>
							<th>'.$lang['all_folder'].'</th>
							<th class="al">'.$lang['site_temp'].'</th>';
			$inq = $db->query("SELECT * FROM ".$basepref."_mods ORDER BY posit");
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="vars pw20">'.$item['name'].'</td>
							<td class="alternative pw45">/mod/'.$item['file'].'</td>
							<td class="gov pw25">
								<select name="temp['.$item['id'].']">
									'.find_sel($sel, $item['temp']).'
								</select>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td class="work-button center" colspan="3">
								<input type="hidden" name="dn" value="tempmodsave">
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
		 * Оформление модов (сохранение)
		 ---------------------------------*/
		if ($_REQUEST['dn'] == 'tempmodsave')
		{
			global $temp;

			foreach ($temp as $id => $fold)
			{
				if ($fold) {
					$id = intval($id);
					$db->query("UPDATE ".$basepref."_mods SET temp = '".$db->escape(preparse_sp($fold))."' WHERE id = '".$id."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=tempmod&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать шаблоны оформления
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'tempedit')
		{
			global $temp;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['site_temp']
				);

			$tm->header();

			$sel = array();
			$dir = new GlobIterator(WORKDIR.'/template/*');
			foreach ($dir as $file)
			{
				if ($file->isDir()) {
					$sel[]= $file->getFilename();
				}
			}

			$temp = in_array($temp, $sel) ? $temp : $conf['site_temp'];

			$blockposit = $blockinfo = $bannerinfo = array();
			$inq = $db->query
					(
						"SELECT posit.*, COUNT(block.blockid) AS total
						 FROM ".$basepref."_block_posit AS posit
						 LEFT JOIN ".$basepref."_block AS block ON (posit.positid = block.positid)
						 GROUP BY posit.positid"
					);
			while ($item = $db->fetchrow($inq))
			{
				$blockposit[$item['positid']] = $item;
			}

			$inq = $db->query("SELECT positid, blockid, block_name FROM ".$basepref."_block ORDER BY block_posit");
			while($item = $db->fetchrow($inq))
			{
				$blockinfo[$item['positid']][$item['blockid']] = $item;
			}

			$inq = $db->query
					(
						"SELECT zone.*, COUNT(banner.banid) AS total
						 FROM ".$basepref."_banners_zone AS zone
						 LEFT JOIN ".$basepref."_banners AS banner ON (zone.banzonid = banner.banzonid)
						 GROUP BY zone.banzonid"
					);
			while($item = $db->fetchrow($inq))
			{
				$bannerinfo[$item['banzonid']] = $item;
			}

			$css = @file_get_contents(WORKDIR.'/template/'.$temp.'/css/screen.css');
			$top = @file_get_contents(WORKDIR.'/template/'.$temp.'/top.tpl');
			$bot = @file_get_contents(WORKDIR.'/template/'.$temp.'/bot.tpl');

			echo '	<div class="section">
					<form action="index.php?dn=tempedit&amp;ops='.$sess['hash'].'" method="post">
					<table class="work">
						<caption>'.$lang['temp_edit'].': '.$temp.'</caption>
						<tr>
							<td class="ac">
								<select name="temp">
									'.find_sel($sel, $temp).'
								</select>&nbsp;
								<input class="side-button" value="'.$lang['site_temp'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<tr><th class="ac"><strong>template/'.$temp.'/css/screen.css</strong></th></tr>
						<tr>
							<td class="al"><textarea name="css" rows="10" cols="70" class="textr resize">'.$css.'</textarea></td>
						</tr>
						<tr><th class="ac"><strong>template/'.$temp.'/top.tpl</strong></th></tr>
						<tr>
							<td class="al">
								<textarea name="top" id="top" rows="10" cols="70" class="textr resize">'.$top.'</textarea>';
								InsertInfo('top', $blockposit, $blockinfo, $bannerinfo);
			echo '			</td>
						</tr>
						<tr><th class="ac"><strong>template/'.$temp.'/bot.tpl</strong></th></tr>
						<tr>
							<td class="al">
								<textarea name="foot" id="foot" rows="10" cols="70" class="textr resize">'.$bot.'</textarea>';
								InsertInfo('foot', $blockposit, $blockinfo, $bannerinfo);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td>
								<input type="hidden" name="dn" value="tempeditsave">
								<input type="hidden" name="temp" value="'.$temp.'">
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
		 * Редактировать шаблоны оформления (сохранение)
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'tempeditsave')
		{
			global $css, $top, $foot, $temp, $sess;

			if ($css) {
				$fo = WORKDIR.'/template/'.$temp.'/css/screen.css';
				$po = fopen($fo, 'w');
				fputs($po, $css);
				fclose($po);
			}

			if ($top) {
				$ft = WORKDIR.'/template/'.$temp.'/top.tpl';
				$pt = fopen($ft, 'w');
				fputs($pt, $top);
				fclose($pt);
			}

			if ($foot) {
				$fb = WORKDIR.'/template/'.$temp.'/bot.tpl';
				$pb = fopen($fb, 'w');
				fputs($pb, $foot);
				fclose($pb);
			}

			redirect('index.php?dn=tempedit&amp;temp='.$temp.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Настройка поиска
		 */
		if ($_REQUEST['dn'] == 'search')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['set_search']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].': '.$lang['set_search'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'search'");
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
								<input type="hidden" name="dn" value="searchsave">
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
		 * Настройка поиска, сохранение
		 */
		if ($_REQUEST['dn'] == 'searchsave')
		{
			global $set, $conf, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['set_search']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'search'");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['all_set'], $lang['set_search'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			$cache_login = new DN\Cache\CacheLogin;
			$cache_login->cachelogin();
			redirect('index.php?dn=search&amp;ops='.$sess['hash']);
		}

		/**
		 * Загрузка изображений
		 */
		if ($_REQUEST['dn'] == 'upload')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['image_upload']
				);

			$tm->header();

			$ignored = array(
				'user_upload',
				'unique',
				//'injpg',
				'thumb',
				'rbig'
			);

			$subtitle = array(
				'wateruse' => 'watermark',
				'markquality' => 'opt_set',
				'resize' => 'all_image_thumb',
				'wbig'  => 'all_image_big'
			);

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].': '.$lang['image_upload'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'upload'");
			while ($itemset = $db->fetchrow($inqset))
			{
				if ( ! in_array($itemset['setname'], $ignored))
				{
					foreach($subtitle as $k => $v)
					{
						if ($itemset['setname'] == $k)
						{
							echo '<tr><th></th><th class="site">'.$lang[$v].'</th></tr>';
						}
					}
					echo '	<tr>
								<td class="first">
									'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
								</td>
								<td>';
					echo		eval(preparse_un($itemset['setcode']));
					echo '		</td>
							</tr>';
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="uploadsave">
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
		 * Загрузка изображений, сохранение
		 */
		if ($_REQUEST['dn'] == 'uploadsave')
		{
			global $set, $conf, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['image_upload']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'upload'");
			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['all_set'], $lang['image_upload'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			$cache_login = new DN\Cache\CacheLogin;
			$cache_login->cachelogin();
			redirect('index.php?dn=upload&amp;ops='.$sess['hash']);
		}

		/**
		 * Управление почтой
		 ---------------------*/
		if ($_REQUEST['dn'] == 'mail')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['mail']
				);

			$tm->header();

			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'mail'");
			$active = ($conf['mail_acting'] == 'mail') ? 'noactive' : '';
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].': '.$lang['mail'].'</caption>';
			while ($itemset = $db->fetchrow($inqset))
			{
				if ($itemset['setname'] != 'mail_smtp' AND $itemset['setname'] != 'mail_list_mime')
				{
					echo	in_array($itemset['setname'], array('mail_attach', 'mail_smtp')) ? '<tr><th colspan="2"></th></tr>' : '';
					echo '	<tr>
								<td class="first">
									'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
								</td>
								<td>';
					echo eval($itemset['setcode']);
					echo '		</td>
							</tr>';
				}
				elseif ($itemset['setname'] == 'mail_smtp')
				{
					echo	'<tr><th colspan="2"></th></tr>';
					$re = array
					(
						'mail_host' => '',
						'mail_user' => '',
						'mail_pass' => '',
						'mail_port' => '',
						'mail_tout' => ''
					);
					$in = Json::decode($itemset['setval']);
					$ins = (is_array($in) AND ! empty($in)) ? $in : $re;
					foreach ($ins as $k => $v)
					{
						echo '	<tr>
									<td class="'.$active.'">'.$lang[$k].'</td>
									<td class="'.$active.'"><input name="smtp['.$k.']" value="'.$v.'" size="25" type="text"></td>
								</tr>';
					}
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="mailsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>';

				echo '	<div class="section">
						<form action="index.php" method="post" id="formitem">
						<table class="work">
							<caption>'.$lang['mail_mime'].'</caption>
							<tr>
								<th class="ar">'.$lang['mail_mime_type'].' &nbsp;</th>
								<th>'.$lang['mail_mime_data'].'</th>
								<th class="ac sw50"><input type="checkbox" name="checkboxall" id="checkboxall" value="yes" title="'.$lang['all_mark'].'"></th>
							</tr>';
				$mime = Json::decode($conf['mail_list_mime']);
				if (is_array($mime) AND ! empty($mime))
				{
					foreach ($mime as $k => $v)
					{
						echo '	<tr>
									<td><input name="mime['.$k.'][type]" value="'.$v['type'].'" size="15" type="text"></td>
									<td class="vm"><input name="mime['.$k.'][data]" value="'.$v['data'].'" size="15" type="text" class="pw45"></td>
									<td class="ac mark vm"><input type="checkbox" name="mime['.$k.'][del]" value="yes" title="'.$lang['delet_of_list'].'"></td>
								</tr>';
					}
					echo '	<tr class="tfoot">
								<td colspan="9">
									<input type="hidden" name="dn" value="upmime">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input class="main-button" value="'.$lang['up_data'].'" type="submit">
								</td>
							</tr>';
				}
				else
				{
					echo '	<tr>
								<td class="ac" colspan="3">
									<div class="pads">'.$lang['data_not'].'</div>
								</td>
							</tr>';
				}
				echo '	</table>
						</form>
						</div>
						<div class="pad"></div>
						<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['mail_mime'].': '.$lang['all_submint'].'</caption>
							<tr>
								<th class="ar first"><span>*</span> '.$lang['mail_mime_type'].' &nbsp;</th>
								<th class="first"><span>*</span> '.$lang['mail_mime_data'].'</th>
							</tr>
							<tr>
								<td><input name="mime[type]" value="" size="15" type="text" required="required"></td>
								<td class="vm"><input name="mime[data]" value="" size="15" type="text" class="pw45" required="required"></td>
							</tr>
							<tr class="tfoot">
								<td colspan="8">
									<input type="hidden" name="dn" value="addmime">
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
		 * Управление почтой (сохранение настроек)
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'mailsave')
		{
			global $set, $smtp, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['mail']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'mail'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]) AND $item['setname'] != 'smtp')
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['all_set'], $lang['mail'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
				else
				{
					foreach ($smtp as $k => $v) {
						$new[$k] = preparse_html($v);
					}
					$ins = Json::encode($new);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mail_smtp'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mail&amp;ops='.$sess['hash']);
		}

		/**
		 * MIME-типы файлов, сохранение
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'upmime')
		{
			global $mime, $sess;

			$in = Json::decode($conf['mail_list_mime']);
			if (isset($in)) {
				$re = $new = array();
				$r = $n = 1;
				if (is_array($in)) {
					foreach ($in as $k => $v) {
						if ( ! isset($mime[$k]['del'])) {
							$m['type'] = isset($mime[$k]['type']) ? lc($mime[$k]['type']) : $v['type'];
							$m['data'] = isset($mime[$k]['data']) ? lc($mime[$k]['data']) : $v['data'];
							$re[$r] = array
										(
											'type' => $m['type'],
											'data' => $m['data']
										);
							$r ++;
						}
					}
				}
				asort($re);
				foreach ($re as $k => $v) {
					$new[$n] = $v;
					$n ++;
				}
				$ins = Json::encode($new);
				$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'mail_list_mime'");
				$cache->cachesave(1);
			}

			redirect('index.php?dn=mail&amp;ops='.$sess['hash']);
		}

		/**
		 * MIME-типы файлов, добавление
		 --------------------------------*/
		if ($_REQUEST['dn']=='addmime')
		{
			global $mime, $sess;

			if (is_array($mime) AND ! empty($mime['type']) AND ! empty($mime['data']))
			{
				$in = Json::decode($conf['mail_list_mime']);
				$re = $new = array();
				$r = $n = $l = 1;
				$mime['type'] = lc($mime['type']);
				$mime['data'] = lc($mime['data']);

				if (isset($in)) {
					if (is_array($in)) {
						foreach ($in as $k => $v) {
							$re[$r] = array
										(
											'type'  => $v['type'],
											'data' => $v['data']
										);
							$r ++;
						}
					}
					if ($l == 1) {
						$re[$r] = array
									(
										'type'  => $mime['type'],
										'data' => $mime['data']
									);
					}
					asort($re);
					foreach ($re as $k => $v) {
						$new[$n] = $v;
						$n ++;
					}
					$ins = Json::encode($new);
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($ins)."' WHERE setname = 'mail_list_mime'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=mail&amp;ops='.$sess['hash']);
		}

		/**
		 * Время Cookies
		 */
		if ($_REQUEST['dn'] == 'time')
		{
			global $conf;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['opt_time_cookie']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].': '.$lang['opt_time_cookie'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'time'");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo '	<tr>
							<td class="first">
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
				echo			eval($itemset['setcode']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr>
							<td class="ar">'.$lang['all_result'].'</td>
							<td class="vm bold" style="font-size: 1.1em"><span class="alternative">'.format_time(NEWTIME, 1, 1).'</span></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="timesave">
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
		 * Время Cookies (сохранение настроек)
		 */
		if ($_REQUEST['dn'] == 'timesave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>',
					$lang['opt_time_cookie']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'time'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['all_set'], $lang['opt_time_cookie'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']],THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=time&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['options'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
