<?php
/**
 * File:        /admin/system/base/index.php
 *
 * Управление системой, Работа с базой данных
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
			$lang['base']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'dumpdb', 'dumpdbsave', 'dumpsite', 'dumpsitesave', 'restore', 'load', 'del');

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

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['base_opitm_repair'].'</a>'
					.'<a'.cho('dumpdb').' href="index.php?dn=dumpdb&amp;ops='.$sess['hash'].'">'.$lang['base_copy'].'</a>'
					.'<a'.cho('dumpsite').' href="index.php?dn=dumpsite&amp;ops='.$sess['hash'].'">'.$lang['site_backup'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Оптимизация / Восстановление
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $table, $ename;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['base'],
					$lang['base_opitm_repair']
				);

			$tm->header();

			$tables = array();
			$info = null;
			if ($ename == 'optimize')
			{
				if (sizeof($table) > 0)
				{
					$size_in = databasesize();
					foreach ($table as $key => $val)
					{
						$tables[] = $val;
					}
					$query = "OPTIMIZE TABLE ".implode(',', $tables);
					if ($db->query($query)) {
						$db->query("UPDATE ".$basepref."_settings SET setval = '".time()."' WHERE setname = 'lastopt'");
						$mess = str_replace(array('{sizein}', '{sizeout}'), array($size_in, databasesize()), $lang['optimize_end']);
					} else {
						$mess = $lang['optimize_error'];
					}
					$info = '	<tr>
									<td class="ac" colspan="3">
										<div class="black">'.$mess.'</div>
									</td>
								</tr>';
				}
			}
			if ($ename == 'repair')
			{
				if (sizeof($table) > 0)
				{
					foreach ($table as $key => $val)
					{
						$tables[] = $val;
					}
					$query = "REPAIR TABLE ".implode(',', $tables);
					if ($db->query($query)) {
						$db->query("UPDATE ".$basepref."_settings SET setval = '".time()."' WHERE setname = 'lastrep'");
						$mess = $lang['repair_end'];
					} else {
						$mess = $lang['repair_error'];
					}
					$info = '	<tr>
									<td class="ac" colspan="3">
										<div class="pad">'.$mess.'</div>
									</td>
								</tr>';
				}
			}
			$l_optimize = format_time($conf['lastopt'], 1, 1);
			$l_repair   = format_time($conf['lastrep'], 1, 1);
			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['base_opitm_repair'].'</caption>
						<tr>
							<th class="ac">'.$lang['db_tables'].'</th>
							<th>'.$lang['all_action'].'</th>
						</tr>
						'.$info.'
						<tr>
							<td>
								<select class="app" style="width: 100%; height: 142px;" size="12" name="table[]" multiple="multiple">';
			$inq = $db->query("SHOW TABLES");
			while ($row = $db->fetchrow($inq))
			{
				$title = $row[0];
				if($title == $basepref."_admin") {
					$title = '';
				}
				if (substr($title, 0, strlen($basepref)) == $basepref) {
					echo '			<option value="'.$title.'" selected>'.$title.'</option>';
				}
			}
			echo '				</select>
							</td>
							<td>
								<table class="work">
									<tr>
										<td class="ac vm pw5"><input type="radio" name="ename" checked="checked" value="optimize"></td>
										<td>'.str_replace("{l_optimize}", $l_optimize, $lang['db_optimize']).'</td>
									</tr>
									<tr>
										<td class="ac vm pw5"><input type="radio" name="ename" value="repair"></td>
										<td>'.str_replace("{l_repair}", $l_repair, $lang['db_repair']).'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="index">
								<input type="hidden" id="ops" name="ops" value="'.$sess['hash'].'">
								<input id="reload" class="main-button" value="'.$lang['all_go'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Резервное копирование базы
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'dumpdb')
		{
			global $namebase, $var, $re, $up;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['base'],
					$lang['base_copy']
				);

			$tm->header();

			$prefix = array();
			$result = $db->query('SHOW TABLES');
			while ($table = $db->fetchrow($result))
			{
				$px = explode('_', $table[0]);
				$prefix[$px[0]] = $px[0];
			}

			$height = count($prefix) > 1 ? '260px' : '195px';
			echo '	<div class="section">
					<form action="index.php" method="post" onsubmit="loads();">
					<table class="work">
						<caption>'.$lang['base_copy'].'</caption>
						<tr>
							<th class="ar">'.$lang['db_tables'].'</th>
							<th>'.$lang['db_backup_vars'].'</th>
						</tr>
						<tr>
							<td>
								<select class="app" style="width: 100%; height: '.$height.'" size="12" name="tables[]" multiple="multiple">';
			$result = $db->query('SHOW TABLES');
			while ($table = $db->fetchrow($result))
			{
				echo '				<option value="'.$table[0].'" selected>'.$table[0].'</option>';
			}
			echo '				</select>
							<td>
								<table id="list" class="work">
									<tr>
										<td class="ac vm pw10"><input type="checkbox" name="structure" value="yes" checked="checked" /></td>
										<td class="pw90">'.$lang['db_structur_hint'].'</td>
									</tr>
									<tr>
										<td class="ac vm"><input type="checkbox" name="datatable" value="yes" checked="checked" /></td>
										<td>'.$lang['db_data_hint'].'</td>
									</tr>
									<tr>
										<td class="ac vm"><input type="checkbox" name="compress" value="yes" checked="checked" /></td>
										<td><span class="vars">'.$lang['gzip_help'].'</td>
									</tr>';
			if (count($prefix) > 1)
			{
				echo '				<tr>
										<td class="ac vm">';
				foreach ($prefix as $val)
				{
					$checked = ($val == $basepref) ? ' checked' : '';
					echo '					<div style="position: relative; line-height: 2.1em;" class="ac"><span style="position: absolute; right: 10px; top: 0px;">'.$val.'</span> <input type="checkbox" name="prefix['.$val.']" value="yes"'.$checked.' /></div>';
				}
				echo '					</td>
										<td class="vm"><span class="vars">'.$lang['prefix_help'].'</td>
									</tr>';
			}
			else
			{
				echo '	<input type="hidden" name="prefix['.$basepref.']" value="yes" />';
			}
			echo '					<tr>
										<td class="ac vm"><input type="text" name="ownname" size="30" /></td>
										<td><span class="vars">'.$lang['custom_archive'].'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="ac vm" colspan="2">';
			if (isset($_COOKIE['dump']) AND $_COOKIE['dump'] == 1)
			{
				$message = isset($_COOKIE['mess']) ? '<div>'.$_COOKIE['mess'].'</div>' : null;
				if ($re)
				{
					$restore_ok = str_replace('{namebase}', $namebase, $lang['restore_db_ok']);
					$restore_bad = str_replace(array('{namebase}','{message}'), array($namebase, $message), $lang['restore_db_bad']);

					if (($var == 0 AND $var != null) OR $var == 1)
					{
						if ($var == 0 AND $var != null)
						{
							$tm->successbox($restore_ok);
						}
						elseif ($var == 1)
						{
							$tm->errorbox($restore_bad);
						}
					}
				}
				elseif ($up)
				{
					$backup_ok = str_replace('{namebase}', $namebase, $lang['backup_db_ok']);
					$backup_bad = str_replace(array('{namebase}','{message}'), array($namebase, $message), $lang['backup_db_bad']);

					if (($var == 0 AND $var != null) OR $var == 1)
					{
						if ($var == 0 AND $var != null)
						{
							$tm->successbox($backup_ok);
						}
						elseif ($var == 1)
						{
							$tm->errorbox($backup_bad);
						}
					}
				}
				setcookie('dump', '', NEWTIME-LIFE_ADMIN, ADMPATH.'/');
				setcookie('mess', '', NEWTIME-LIFE_ADMIN, ADMPATH.'/');
			}
			echo 			'<div class="upad">'.$lang['db_dumpbackup_hint'].'</div>';
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="dumpdbsave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';
			$datafile = array();
			$iterator = new GlobIterator(WORKDIR.'/cache/dump/db/*', FilesystemIterator::KEY_AS_FILENAME);
			foreach ($iterator as $val)
			{
				if ($val->isFile())
				{
					$datafile[$val->getMTime()] = array
						(
							'time' => $val->getMTime(),
							'name' => $val->getFilename(),
							'size' => $val->getSize(),
							'ext'  => $val->getExtension()
						);
				}
			}
			echo '	<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['dump_files'].'</caption>
						<tr>
							<th class="ar pw15">'.$lang['sys_date'].'&nbsp;</th>
							<th>'.$lang['all_file'].'</th>
							<th>'.$lang['file_size'].'</th>
							<th>'.$lang['down_type'].'</th>
							<th>'.$lang['all_import'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			if (($datafile))
			{
				krsort($datafile);
				foreach ($datafile as $val)
				{
					echo '	<tr class="list">
								<td class="site vm">'.format_time($val['time'], 1, 1).'</td>
								<td class="vm">'.$val['name'].'</td>
								<td class="vm">'.size($val['size']).'</td>
								<td class="vm">'.$val['ext'].'</td>
								<td class="gov pw10">
									<a href="index.php?dn=restore&amp;ext='.$val['ext'].'&amp;file='.$val['name'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/restore.png" alt="'.$lang['db_import'].'" /></a>
								</td>
								<td class="gov pw10">
									<a href="index.php?dn=load&amp;type=db&amp;file='.$val['name'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/down.png" alt="'.$lang['down_file'].'" /></a>
									<a href="index.php?dn=del&amp;type=db&amp;file='.$val['name'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			else
			{
				echo '	<tr>
							<td class="ac" colspan="6">
								<div class="pads">'.$lang['data_not'].'</div>
							</td>
						</tr>';
			}
			echo '	</table>
					</form>
					</div>
					<div id="lds"></div>';

			$tm->footer();
		}

		/**
		 * Резервное копирование сайта
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'dumpsite')
		{
			global $namebase, $var, $re, $up;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['base'],
					$lang['site_backup']
				);

			$directories = $files = array();
			$iterator = new FilesystemIterator(DNDIR.'/.');
			foreach($iterator as $entry)
			{
				if ($entry->isDir()) {
					$directories[] = $entry->getFilename();
				}
				natsort($directories);

				if ($entry->isFile()) {
					$in = $entry->getFilename();
					$files[$in] = $in;
				}
				natsort($files);
			}

			$ignore = array('setup', '.htaccess');
			$list = array_merge($directories, $files);
			$host = parse_url(SITE_URL, PHP_URL_HOST);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post" onsubmit="loads();">
					<table class="work">
						<caption>'.$lang['site_backup'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_site'].' '.$host.'&nbsp; ⁄ &nbsp;'.$lang['list_items'].'</th>
							<th>'.$lang['db_backup_vars'].'</th>
						</tr>';
				echo '	<tr>
							<td></td>
							<td class="vm pw70"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox" /></td>
						</tr>';
			foreach ($list as $key => $val)
			{
				$checked = in_array($val, $ignore) ? '' : ' checked="checked"';
				$style = ($key === $val) ? 'style="background-color: #f5eaf1 !important;"' : 'style="background-color: #ebeaf5 !important; font-weight: bold;"';
				echo '	<tr>
							<td '.$style.' class="ar vm pw20 black">'.$val.'</td>
							<td class="vm pw70"><input type="checkbox" name=list['.DNDIR.$val.'] value="yes"'.$checked.' /></td>
						</tr>';
			}
			echo '		<tr>
							<td class="ac vm" colspan="2">';
			if (isset($_COOKIE['dump']) AND $_COOKIE['dump'] == 1)
			{
				if ($up)
				{
					$backup_ok = str_replace('{host}', $host, $lang['backup_site_ok']);
					$tm->successbox($backup_ok);
				}
				setcookie('dump', '', NEWTIME-LIFE_ADMIN, ADMPATH.'/');
			}
			echo 			'<div class="upad">'.$lang['db_dumpbackup_hint'].'</div>';
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="dumpsitesave" />
								<input type="hidden" name="ops" value="'.$sess['hash'].'" />
								<input class="main-button" value="'.$lang['all_save'].'" type="submit" />
							</td>
						</tr>
					</table>
					</form>
					</div>';
			$datafile = array();
			$iterator = new GlobIterator(WORKDIR.'/cache/dump/site/*', FilesystemIterator::KEY_AS_FILENAME);
			foreach ($iterator as $val)
			{
				if ($val->isFile())
				{
					$datafile[$val->getMTime()] = array
						(
							'time' => $val->getMTime(),
							'name' => $val->getFilename(),
							'size' => $val->getSize(),
							'ext'  => $val->getExtension()
						);
				}
			}
			echo '	<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['dump_files'].'</caption>
						<tr>
							<th class="ar pw15">'.$lang['sys_date'].'&nbsp;</th>
							<th>'.$lang['all_file'].'</th>
							<th>'.$lang['file_size'].'</th>
							<th>'.$lang['down_type'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			if (($datafile))
			{
				krsort($datafile);
				foreach ($datafile as $val)
				{
					echo '	<tr class="list">
								<td class="site vm">'.format_time($val['time'], 1, 1).'</td>
								<td class="vm">'.$val['name'].'</td>
								<td class="vm">'.size($val['size']).'</td>
								<td class="vm">'.$val['ext'].'</td>
								<td class="gov pw10">
									<a href="index.php?dn=load&amp;type=site&amp;file='.$val['name'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/down.png" alt="'.$lang['down_file'].'" /></a>
									<a href="index.php?dn=del&amp;type=site&amp;file='.$val['name'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			else
			{
				echo '	<tr>
							<td class="ac" colspan="5">
								<div class="pads">'.$lang['data_not'].'</div>
							</td>
						</tr>';
			}
			echo '	</table>
					</form>
					</div>
					<div id="lds"></div>';

			$tm->footer();
		}

		/**
		 * Резервное копирование сайта (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'dumpsitesave')
		{
			global $list, $ownname;

			ini_set('memory_limit', '256M');
			ini_set('max_execution_time', 0);

			if ( ! file_exists(DNDIR.'cache/dump/site') )
			{
				mkdir(DNDIR.'cache/dump/site', 02777);
				chmod(DNDIR.'cache/dump/site', 02777);
			}
		
			if ( ! is_writable(DNDIR.'cache/dump/site') )
			{
				$tm->header();
				$tm->error($lang['site_backup'], $lang['all_save'], DNDIR.'cache/dump/site', $lang['not_writable']);
				$tm->footer();
			}

			$dp = new Dump();

			if ( ! empty($ownname)) {
				$dumpname = $dp->check_name($ownname);
			} else {
				$host = parse_url(SITE_URL, PHP_URL_HOST);
				$dumpname = 'dump_'.$host.'_'.date("Y-m-d_His");
			}

			$list = array_keys($list);
			$skip = array('/cache/dump/db/', '/cache/dump/site/');

			$dp->dumpsite($list, DNDIR.'cache/dump/site/'.$dumpname.'.zip', $skip);

			setcookie('dump', 1, NEWTIME+LIFE_ADMIN, ADMPATH.'/');
			redirect('index.php?dn=dumpsite&amp;up=1&amp;&amp;ops='.$sess['hash']);
		}

		/**
		 * Скачать файл
		 -----------------*/
		if ($_REQUEST['dn'] == 'load')
		{
			global $type, $file;

			if (preparse($type, THIS_EMPTY) == 0 AND preparse($file, THIS_EMPTY) == 0)
			{
				$fd = new Files();
				$fd->download(WORKDIR.'/cache/dump/'.$type.'/'.$file);
				exit;
			}
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить резервную копию
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $type, $file, $ok;

			if ($ok == 'yes')
			{
				unlink(WORKDIR.'/cache/dump/'.$type.'/'.$file);
			}
			else
			{
				$yes = 'index.php?dn=del&amp;type='.$type.'&amp;file='.$file.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=dump'.$type.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $file, $yes, $not);
				$tm->footer();
			}

			redirect('index.php?dn=dump'.$type.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Восстановление базы из резервной копии
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'restore')
		{
			global $file, $ext, $ok, $hostname, $nameuser, $password, $namebase;

			if ($ok == 'yes')
			{
				$dp = new Dump();

				ini_set('memory_limit', '256M');
				ini_set('max_execution_time', 0);

				$namebase = isset($PLATFORM[$tm->platform_id]['base']) ? $PLATFORM[$tm->platform_id]['base'] : $namebase;

				$disable = ini_get('disable_functions');
				$array_disable = !empty($disable) ? explode(',', $disable) : '';
				$allow_exec = is_array($array_disable) AND ! in_array('exec', $array_disable) ? 1 : 0;

				if (function_exists('exec') AND $allow_exec)
				{
					if ($ext == 'gz')
					{
						exec('gunzip -t '.DNDIR.'cache/dump/db/'.$file, $out, $var);
						exec('gunzip < '.DNDIR.'cache/dump/db/'.$file.' | mysql -h'.$hostname.' -u'.$nameuser.' -p'.$password.' '.$namebase);
					}
					elseif ($ext == 'zip')
					{
						$temp = str_replace('.zip', '.sql', $file);
						exec('unzip '.DNDIR.'cache/dump/db/'.$file.' -d '.DNDIR.'cache/dump/db/');
						exec('mysql -h'.$hostname.' -u'.$nameuser.' -p'.$password.' '.$namebase.' < '.DNDIR.'cache/dump/db/'.$temp, $out, $var);
						if ($var == 0) {
							unlink(DNDIR.'cache/dump/db/'.$temp);
						}
					}
					elseif ($ext == 'sql')
					{
						exec('mysql -h'.$hostname.' -u'.$nameuser.' -p'.$password.' '.$namebase.' < '.DNDIR.'cache/dump/db/'.$file, $out, $var);
					}
					else
					{
						$var = 1;
					}
				}
				else
				{
					$dp = new Dump();
					$dp->import(DNDIR.'cache/dump/db/'.$file, $ext);
					$var = $dp->var;
				}

				$message = isset($dp->message[0]) ? $dp->message[0] : null;

				setcookie('dump', 1, NEWTIME+LIFE_ADMIN, ADMPATH.'/');
				setcookie('mess', 1, NEWTIME+LIFE_ADMIN, ADMPATH.'/');

				redirect('index.php?dn=dumpdb&amp;re=1&amp;var='.$var.'&amp;ops='.$sess['hash']);
			}
			else
			{
				$yes = 'index.php?dn=restore&amp;file='.$file.'&amp;ext='.$ext.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=dumpdb&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['db_import'], $namebase, $yes, $not, $file);
				$tm->footer();
			}
		}

		/**
		 * Резервное копирование базы (сохранение)
		 ------------------------------------------*/
		if ($_REQUEST['dn'] == 'dumpdbsave')
		{
			global $db, $tables, $structure, $datatable, $compress, $prefix, $ownname, $hostname, $nameuser, $password, $namebase;

			ini_set('memory_limit', '256M');
			ini_set('max_execution_time', 0);

			if ( ! file_exists(DNDIR.'cache/dump/db') )
			{
				mkdir(DNDIR.'cache/dump/db', 02777);
				chmod(DNDIR.'cache/dump/db', 02777);
			}
		
			if ( ! is_writable(DNDIR.'cache/dump/db') )
			{
				$tm->header();
				$tm->error($lang['site_backup'], $lang['all_save'], DNDIR.'cache/dump/db', $lang['not_writable']);
				$tm->footer();
			}

			$dp = new Dump();
			$namebase = isset($PLATFORM[$tm->platform_id]['base']) ? $PLATFORM[$tm->platform_id]['base'] : $namebase;

			if ( ! empty($ownname)) {
				$dumpname = $dp->check_name($ownname);
			} else {
				$dumpname = $namebase.'_'.date("Y-m-d_His");
			}

			$_tables = array();
			$prefix = array_keys($prefix);
			foreach ($tables as $val)
			{
				$px = explode('_', $val);
				if (in_array($px[0], $prefix))
				{
					$_tables[] = $val;
				}
			}

			$disable = ini_get('disable_functions');
			$array_disable = !empty($disable) ? explode(',', $disable) : '';
			$allow_exec = is_array($array_disable) AND ! in_array('exec', $array_disable) ? 1 : 0;

			if (function_exists('exec') AND $allow_exec)
			{
				$nodata = ($datatable == 'yes') ? '' : '--no-data';
				$notable = ($structure == 'yes') ? '' : '--no-create-info';
				if ($compress == 'yes') {
					exec('mysqldump -h'.$hostname.' -u'.$nameuser.' -p'.$password.' '.$nodata.' '.$notable.' '.$namebase.' '.implode(' ', $_tables).' --single-transaction | gzip > '.DNDIR.'cache/dump/db/'.$dumpname.'.sql.gz', $out, $var);
				} else {
					exec('mysqldump -h'.$hostname.' -u'.$nameuser.' -p'.$password.' '.$nodata.' '.$notable.' '.$namebase.' '.implode(' ', $_tables).' --single-transaction > '.DNDIR.'cache/dump/db/'.$dumpname.'.sql', $out, $var);
				}
			}
			else
			{
				$insert = $outtable = $outdata = $drop = $del = null;

				if ($structure == 'yes')
				{
					$drop = true;
					foreach ($_tables as $val)
					{
						$outtable.= $dp->structure($val, $drop);
					}
				}

				if ($datatable == 'yes')
				{
					$del = ( ! $drop) ? true : false;
					foreach ($_tables as $val)
					{
						$outdata.= $dp->datatable($val, $del);
					}
				}

				$insert.= "-- SQL Dump\r\n";
				$insert.= "-- Danneo CMS v.".$conf['version']."\r\n";
				$insert.= "-- \r\n";
				$insert.= "-- Host: ".$hostname."\r\n";
				$insert.= "-- Time Create: ".date('M d Y, H:i:s')."\r\n";
				$insert.= "-- MySQL Server: ".databaseversion()."\r\n";
				$insert.= "-- PHP Language: ".PHP_VERSION."\r\n\r\n";
				$insert.= "-- \r\n";
				$insert.= "-- Database: ".$namebase."\r\n";
				$insert.= "-- \r\n\r\n";
				$insert.= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\n";
				$insert.= "SET time_zone = \"+00:00\";\r\n\r\n";
				$insert.= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n";
				$insert.= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n";
				$insert.= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n";
				$insert.= "/*!40101 SET NAMES utf8 */;\r\n";
				$insert.= "\r\n\r\n";
				$insert.= $outtable;
				$insert.= $outdata;

				if ($compress == 'yes') {
					$dp->compress($dumpname.'.sql.gz', $insert, DNDIR.'/cache/dump/db/');
				} else {
					$dp->savedump($dumpname.'.sql', $insert, DNDIR.'/cache/dump/db/');
				}
			}

			$var = $dp->var;
			$message = isset($dp->message[0]) ? $dp->message[0] : null;

			setcookie('dump', 1, NEWTIME+LIFE_ADMIN, ADMPATH.'/');
			setcookie('mess', 1, NEWTIME+LIFE_ADMIN, ADMPATH.'/');

			redirect('index.php?dn=dumpdb&amp;up=1&amp;var='.$var.'&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['base'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
