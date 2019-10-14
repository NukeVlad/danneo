<?php
/**
 * File:        /admin/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

use DN\Cache\CacheLang;
use DN\Cache\CacheLogin;

/**
 * Константы
 */
define('READCALL', 1);
define('PERMISS', '');

/**
 * Подключение файла инициализации
 */
require_once __DIR__.'/init.php';

/**
 * Проверка авторизации
 */
if (isset($_POST['adlog']) AND isset($_POST['adpwd']) AND empty($sess['hash']))
{
	if ($ADMIN_AUTH != 1) {
		redirect(ADMURL.'/login.php?opsss=1');
	} else {
		redirect(ADMURL.'/login.php');
	}
}
else
{
	/**
	 * Проверка авторизации
	 */
	if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
	{
		global $conf, $db, $basepref, $tm, $sess, $lang, $PLATFORM, $ADMIN_PERM_ARRAY;

		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'webstat', 'server', 'content', 'system', 'support', 'logout', 'nowys', 'yeswys', 'closed', 'panelsave', 'platformchange');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn']= (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess, $ADMIN_PERM, $ADMIN_ID, $CHECK_ADMIN;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['goto_index'].'</a>';
			if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) {
				$link.= '<a'.cho('server', 0, 1).' href="index.php?dn=server&amp;ops='.$sess['hash'].'">'.$lang['server'].'</a>';
			}
			$link.= '<a'.cho('support').' href="index.php?dn=support&amp;ops='.$sess['hash'].'">'.$lang['support'].'</a>';
			$link.= '<a href="index.php?dn=logout&amp;ops='.$sess['hash'].'">'.$lang['goto_logout'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Основная страница
		 ----------------------*/
		if ($_REQUEST['dn']=='index')
		{
			global	$mods, $db, $basepref, $conf, $cache, $lang, $sess, $tm, $pid, $altime,
					$_SERVER, $PLATFORM, $ADMIN_LAST, $ADMIN_PERM_ARRAY, $ADMIN_PERM, $ADMIN_ID, $CHECK_ADMIN;

			$tm->header();

			/**
			 * Permission
			 */
			$permiss = ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])) ? TRUE : FALSE;

			/**
			 * Last Time
			 */
			$altime = preparse($altime, THIS_TRIM);
			$altime = ( ! empty($altime)) ? $db->escape($altime) : $ADMIN_LAST;

			/**
			 * SMS settings from the Cache
			 */
			if ( ! isset($config['service_sms']) )
			{
				$cache->cachesave(1);
			}

			/**
			 * Language in System
			 */
			if ( ! file_exists(DNDIR.'cache/cache.lang.php') )
			{
				$clang = new CacheLang;
				$clang->cachelang();

				$clogin = new CacheLogin;
				$clogin->cachelogin();
			}


			/**
			 * Рабочий стол
			 */
			echo '	<div class="desktop clearfix">
						<div class="sheet sl">
							<ul>
								<li><h4>'.format_time(NEWTIME, 1, 1).'</h4></li>
								<li>'.$lang['data_site'];
			if ($permiss) {
				echo '				&nbsp; &#8260; &nbsp;<a href="'.ADMURL.'/system/options/index.php?dn=time&amp;ops='.$sess['hash'].'">'.$lang['all_change'].'</a>';
			}
			echo '				</li>
							</ul>';
			if ($ADMIN_LAST > 0)
			{
				echo '		<ul>
								<li><h4>'.$lang['one_amanage'].'</h4></li>
								<li>'.$lang['you_auth'].': <strong>'.$checklogin.'</strong>';
				if ($permiss) {
					echo '			&nbsp; &#8260; &nbsp;<a href="'.ADMURL.'/system/amanage/index.php?dn=index&amp;ops='.$sess['hash'].'" title="'.$lang['personal_cabinet'].'">'.$lang['personal_cabinet'].'</a>';
				}
				echo '			</li>
								<li>'.$lang['last_visit'].': '.format_time($ADMIN_LAST, 1, 1).'</li>
							</ul>';
			}
			else
			{

				echo '		<ul>
								<li><h4>'.$lang['one_amanage'].'</h4></li>
								<li>'.$lang['you_auth'].': <strong>'.$checklogin.'</strong></li>
								<li>'.$lang['first_visit'];
				if ($permiss) {
					echo '			&nbsp; &#8260; &nbsp;<a href="'.ADMURL.'/system/amanage/index.php?dn=index&amp;ops='.$sess['hash'].'" title="'.$lang['personal_cabinet'].'">'.$lang['personal_cabinet'].'</a>';
				}
				echo '			</li>
							</ul>';
			}
			echo '			<ul>
								<li><h4>'.$lang['last_changes'].'</h4>
								<li>
									<table>
										<caption>
											<a href="index.php?dn=index&amp;altime='.(NEWTIME - (NEWTIME - $ADMIN_LAST)).'&amp;ops='.$sess['hash'].'">'.$lang['today'].'</a>&nbsp; &#8260; &nbsp;
											<a href="index.php?dn=index&amp;altime='.(NEWTIME - 86400).'&amp;ops='.$sess['hash'].'">'.$lang['day'].'</a>&nbsp; &#8260; &nbsp;
											<a href="index.php?dn=index&amp;altime='.(NEWTIME - 604800).'&amp;ops='.$sess['hash'].'">'.$lang['week'].'</a>&nbsp; &#8260; &nbsp;
											<a href="index.php?dn=index&amp;altime='.(NEWTIME - 2592000).'&amp;ops='.$sess['hash'].'">'.$lang['month'].'</a>
										</caption>
										<tr>
											<th>'.$lang['all_name'].'</th>
											<th>'.$lang['all_col'].'</th>
											<th>'.$lang['all_link'].'</th>
										</tr>';
				$today = array();
				$check = false;
				$today = new GlobIterator(ADMDIR.'/mod/*/mod.today.php');
				foreach ($today as $file)
				{
					if (file_exists($file->getPathname()))
					{
						include ($file->getPathname());
					}
				}
				if ( ! $check) {
					echo '				<tr>
											<td class="data-not" colspan="3">'.$lang['data_not'].'</td>
										</tr>';
				}
				echo '				</table>
								</li>
							</ul>
						</div>
						<div class="sheet sr">';

			// Site setting
			if ($permiss)
			{
				echo '		<ul>
								<li><h4>'.$lang['optset_site'].'</h4></li>
								<li>'.$conf['site'].'</li>
								<li>'.$conf['site_descript'].'</li>
								<li>
									<a href="'.ADMURL.'/system/options/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_change'].'</a>&nbsp; &#8260; &nbsp;';
				if ($conf['closed'] == 'no') {
					echo '			<a href="index.php?dn=closed&amp;set=yes&amp;ops='.$sess['hash'].'">'.$lang['site_closed'].'</a>';
				} else {
					echo '			<a href="index.php?dn=closed&amp;set=no&amp;ops='.$sess['hash'].'">'.$lang['site_open'].'</a>';
				}
				echo '			</li>
							</ul>';
			}

			// Platform
			if ($permiss)
			{
				if (preparse($PLATFORM, THIS_ARRAY) == 1)
				{
					$selplatform = '<option value="0">'.DEF_SITE.'</option>'; // Название платформы по умолчанию
					foreach ($PLATFORM as $id => $site)
					{
						$selplatform.= '<option value="'.$id.'"'.((isset($pid) AND $pid == $id) ? ' selected' : '').'>'.$site['name'].'</option>';
					}
					echo '	<ul>
								<li><h4>'.$lang['platform_one'].'</h4></li>
								<li>'.$lang['all_plat'].'</li>
								<li>
									<form action="index.php?dn=index&ops='.$sess['hash'].'" method="post" id="platforms">
										<select name="selplatform">
											'.$selplatform.'
										</select>&nbsp;
										<input type="hidden" name="dn" value="platformchange">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input id="reload" class="side-button" value="'.$lang['re_platform'].'" type="submit">
									</form>
								</li>
								<li>'.$lang["set_platform"].'&nbsp; &#8260; &nbsp;<a href="'.ADMURL.'/system/platform/index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['all_platform'].'</a></li>
							</ul>';
				}
			}

			// Language default
			if ($permiss)
			{
				$langing = $db->fetchrow($db->query("SELECT langcode, langpack, langcharset, langauthor FROM ".$basepref."_language_pack WHERE langcode = '".$conf['langcode']."'"));
				echo '		<ul>
								<li><h4>'.$lang['lang_site'].'</h4></li>
								<li>'.$lang['lang_default'].': '.$langing['langpack'].'&nbsp; &#8260; &nbsp;<a href="'.ADMURL.'/system/lang/index.php?ops='.$sess['hash'].'">'.$lang['all_change'].'</a></li>
								<li>'.$lang['code_page'].': '.$langing['langcharset'].'</li>
							</ul>';
			}

			// Data base
			if ($permiss)
			{
				$l_optimize = format_time($conf['lastopt'], 1, 1);
				echo '		<ul>
								<li><h4>'.$lang['base'].'</h4></li>
								<li>'.$lang['size_database'].': '.databasesize().'</li>
								<li>'.$lang['base_last_optim'].': '.format_time($conf['lastopt'], 0, 1).' <p class="hint" title="'.$lang['base_notis'].'">?</p></li>
								<li class="line"></li>
								<li><a class="side-button" href="'.ADMURL.'/system/base/index.php?dn=improvement&amp;ops='.$sess['hash'].'">'.$lang['base_optim'].'</a></li>
							</ul>';
			}

			// Setting panel
			$skin_list = null;
			$skin_dir = new GlobIterator(ADMDIR.'/template/skin/*');
			foreach ($skin_dir as $file)
			{
				if ($file->isDir()) {
					$skin = $file->getFilename();
					$skin_list.= '<option value="'.$skin.'"'.(($sess['skin'] == $skin) ? ' selected' : '').'>'.$skin.'</option>';
				}
			}
			echo '			<ul>
							<form action="index.php" method="post">
								<li><h4>'.$lang['set_panel'].'</h4></li>
								<li>
									<select name="skin" class="sw150">
										'.$skin_list.'
									</select> &nbsp; '.$lang['site_temp'].'
								</li>
								<li>
									<select name="icon" class="sw150">
										<option value="yes"'.(($sess['icon'] == 'yes') ? ' selected' : '').'>'.$lang['all_yes'].'</option>
										<option value="no"'.(($sess['icon'] == 'no') ? ' selected' : '').'>'.$lang['all_no'].'</option>
									</select> &nbsp; '.$lang['view_icon'].'
								</li>
								<li class="line"></li>
								<li>
									<input type="hidden" name="dn" value="panelsave">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="admid" value="'.$ADMIN_ID.'">
									<input id="reload" class="side-button" value="'.$lang['all_save'].'" type="submit">
								</li>
							</form>
							</ul>';

			// Current version
			if ($permiss)
			{
				echo '		<ul>
								<li><h4>'.$lang['control_system'].'</h4></li>
								<li>'.$lang['current_version'].': Danneo CMS v.'.VERSION.'</li>';
				if (isset($conf['lastup']) AND ! empty($conf['lastup']))
				{
					if ($conf['lastup'] <> filemtime(ADMDIR.'/init.php')) {
						$db->query("UPDATE ".$basepref."_settings SET setval = '".NEWTIME."' WHERE setname = 'lastup'");
					}
					echo '			<li>'.$lang['last_update'].': '.format_time($conf['lastup'], 0, 1).'</li>';
				}
				echo '			<li class="line"></li>
								<li>'.$lang['support'].': <a href="'.ADMURL.'/index.php?dn=support&amp;ops='.$sess['hash'].'">'.$lang['in_details'].'</a></li>
							</ul>';
			}

			echo '		</div>
					</div>';

			$tm->footer();
		}

		/**
		 * Параметры Сервера
		 ---------------------*/
		if ($_REQUEST['dn']=='server')
		{
			global $db, $basepref, $conf, $lang, $sess, $tm, $_SERVER, $ADMIN_PERM, $ADMIN_ID, $CHECK_ADMIN;

			$template['breadcrumb'] = array('<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>', $lang['server']);

			if ($ADMIN_PERM == 0 OR ! in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
			{
				$tm->header();
				$tm->error($lang['manage_server'], $lang['server'], $lang['no_access']);
				$tm->footer();
			}

			// register globals
			$register_globals = (ini_get('register_globals')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// safe mode
			$safe_mode = (ini_get('safe_mode')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// session.auto_start
			$session_auto_start = (ini_get('session.auto_start')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// magic quotes
			$magic_quotes_gpc = (ini_get('magic_quotes_gpc')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// magic_quotes_runtime
			$magic_quotes_runtime = (ini_get('magic_quotes_runtime')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// max size up
			$file_uploads = (ini_get('file_uploads')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
			if (ini_get('file_uploads')) {
				// max size up
				$post_max_size = ini_get('post_max_size');
				// max size up
				$upload_max_filesize = ini_get('upload_max_filesize');
			}

			// memory limit
			$memory_limit = (ini_get('memory_limit')) ? ini_get('memory_limit') :  ' --- ';

			// php version
			$php_version = sprintf("%s\n",phpversion());

			// gd info
			$gdinfo = (function_exists('gd_info')) ? gd_info() : '';
			if (is_array($gdinfo))
			{
				preg_match('/\d\.\d/', $gdinfo['GD Version'], $m);
				$gdversion = (isset($m[0])) ? $m[0] : $gdinfo['GD Version'];
				$gif_read = ($gdinfo['GIF Read Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
				$gif_create = ($gdinfo['GIF Create Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

				// for <= php 5.2
				if (isset($gdinfo['JPG Support'])) {
					$jpeg_support = ($gdinfo['JPG Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
				}
				elseif (isset($gdinfo['JPEG Support']))  // for >= php 5.3
				{
					$jpeg_support = ($gdinfo['JPEG Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
				}
				$png_support = ($gdinfo['PNG Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
				if (isset($gdinfo['WBMP Support'])) {
					$wbmp_support = ($gdinfo['WBMP Support'] == true) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';
				}
			} else {
				$gdversion = $lang['sys_offswitched'];
			}

			// database size
			$db_size = databasesize();

			// database version
			$db_version = sprintf("%s\n", $db->serverinfo());

			// max time
			$max_execution_time = ini_get('max_execution_time');

			// server
			if (preg_match('#Microsoft-IIS/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $servers)) {
				$type_server = 'IIS &nbsp; '.$servers[1];
			} elseif (preg_match('#(Apache)/([0-9\.]+)\s#siU', $_SERVER['SERVER_SOFTWARE'], $servers)) {
				$type_server = $servers[1].' &nbsp;'.$servers[2];
			} elseif (strtoupper($_SERVER['SERVER_SOFTWARE']) == 'APACHE') {
				$type_server = apache_get_version();
			} else {
				$type_server = ' &#8212; ';
			}

			// mod_rewrite
			$rewrite = '';
			if (function_exists('apache_get_modules'))
			{
				foreach( apache_get_modules() as $v ) {
					$rewrite.= ($v == 'mod_rewrite') ? 1 : '';
				}
			}
			$mod_rewrite = ( ! empty($rewrite)) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// OS
			$os = (PHP_OS) ? PHP_OS : ' &#8212; ';

			// Document Root
			$dirname = __DIR__;
			$document_root = str_replace('/'.basename(__DIR__), '', $dirname);

			// Server Name
			$server_name = $_SERVER['SERVER_NAME'];

			// Display Errors
			$display_errors = (ini_get('display_errors')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// Display Startup Errors
			$display_startup_errors = (ini_get('display_startup_errors')) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// Zlib
			$print_zlib = in_array('zlib', get_loaded_extensions()) ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// cURL
			$print_curl = function_exists('curl_init') ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// JSON
			$print_json = function_exists('json_encode') ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			// Multibyte String
			$print_mbstring = function_exists('mb_internal_encoding') ? '<span class="server">'.$lang['sys_included'].'</span>' : '<span class="alternative">'.$lang['sys_offswitched'].'</span>';

			$tm->header();

			echo '	<table class="tables">
						<tr>
							<th>'.$lang['all_key'].'</th>
							<th>'.$lang['all_value'].'</th>
						</tr>
						<tr>
							<td>Operating System (OS)</td><td>'.$os.'</td>
						</tr>
						<tr>
							<td>HTTP Server</td><td>'.$type_server.'</td>
						</tr>
						<tr>
							<td>Apache mod_rewrite</td><td>'.$mod_rewrite.'</td>
						</tr>
						<tr>
							<td>MySQL Server</td><td>'.$lang["sys_version"]." &nbsp;".$db_version.'</td>
						</tr>
						<tr>
							<td>DataBase Size</td><td>'.$db_size.'</td>
						</tr>
						<tr>
							<td>PHP Language</td><td>'.$lang['sys_version']." &nbsp;".$php_version.'</td>
						</tr>
						<tr>
							<td>Zlib Compression</td><td>'.$print_zlib.'</td>
						</tr>
						<tr>
							<td>cURL</td><td>'.$print_curl.'</td>
						</tr>
						<tr>
							<td>JSON</td><td>'.$print_json.'</td>
						</tr>
						<tr>
							<td>Multibyte String</td><td>'.$print_mbstring.'</td>
						</tr>
						<tr>
							<td>File Uploads</td><td>'.$file_uploads.'</td>
						</tr>';
			if (ini_get('file_uploads'))
			{
				echo '	<tr>
							<td>File Post Size</td><td>'.$post_max_size.'</td>
						</tr>
						<tr>
							<td>Max File Size Upload</td><td>'.$upload_max_filesize.'</td>
						</tr>';
			}
			echo '		<tr>
							<td>Max Execution Time</td><td>'.$max_execution_time.'</td>
						</tr>
						<tr>
							<td>Memory Limit</td><td>'.$memory_limit.'</td>
						</tr>
						<tr>
							<td>GD Graphics Library<td>'.$lang['sys_version']." &nbsp;".$gdversion.'</td>
						</tr>';
			if (is_array($gdinfo))
			{
				echo '	<tr>
							<td>GIF Read Support</td><td>'.$gif_read.'</td>
						</tr>
						<tr>
							<td>GIF Create Support</td><td>'.$gif_create.'</td>
						</tr>
						<tr>
							<td>JPEG Support</td><td>'.$jpeg_support.'</td>
						</tr>
						<tr>
							<td>PNG Support</td><td>'.$png_support.'</td>
						</tr>
						<tr>
							<td>WBMP Support</td><td>'.$wbmp_support.'</td>
						</tr>';
            }
			echo '		<tr>
							<td>Safe Mode</td><td>'.$safe_mode.'</td>
						</tr>
						<tr>
							<td>Register Globals</td><td>'.$register_globals.'</td>
						</tr>
						<tr>
							<td>Session.auto_start</td><td>'.$session_auto_start.'</td>
						</tr>
						<tr>
							<td>Magic_quotes_gpc</td><td>'.$magic_quotes_gpc.'</td>
						</tr>
						<tr>
							<td>Magic_quotes_runtime</td><td>'.$magic_quotes_runtime.'</td>
						</tr>
						<tr>
							<td>Display Errors</td><td>'.$display_errors.'</td>
						</tr>
						<tr>
							<td>Display Startup Errors</td><td>'.$display_startup_errors.'</td>
						</tr>
						<tr>
							<td>Server Name</td><td>'.$server_name.'</td>
						</tr>
						<tr>
							<td>Document Root</td><td>'.$document_root.'</td>
						</tr>
					</table>';

			$tm->footer();
		}

		/**
		 * Настройки панели управления (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'panelsave')
		{
			global $admid, $skin, $icon;

			$admid = preparse($admid, THIS_INT);
			$in = array();

			$apanel_set = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setname = 'apanelset'"));
			$label = Json::decode($apanel_set['setval']);

			if (preparse($skin, THIS_SYMNUM) == 0 AND preparse($icon, THIS_SYMNUM) == 0)
			{
				if (is_array($label))
				{
					if (key_exists($admid, $label))
					{
						foreach ($label as $k => $v)
						{
							if ($k == $admid) {
								$in[$k] = array('skin' => $skin, 'icon' => $icon);
							} else {
								$in[$k] = array('skin' => $v['skin'], 'icon' => $v['icon']);
							}
						}
					}
					else
					{
						foreach ($label as $k => $v)
						{
							$in[$k] = array('skin' => $v['skin'], 'icon' => $v['icon']);
						}
						$in[$admid] = array('skin' => $skin, 'icon' => $icon);
					}
				}
			}

			ksort($in);
			$ins = Json::encode($in);
			$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'apanelset'");

			$cache->cachesave(1);

			$cache = new DN\Cache\CacheLogin;
			$cache->cachelogin();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Управление контентом
		 -----------------------*/
		if ($_REQUEST['dn'] == 'content')
		{
			global $db, $basepref, $conf, $lang, $sess, $tm, $_SERVER, $PLATFORM;

			$template['breadcrumb'] = array('<a href="'.ADMURL.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>', $lang['all_content']);

			$tm->header();

			$widget = array();
			$widget = new GlobIterator(ADMDIR.'/mod/*/mod.widget.php');
			foreach ($widget as $file)
			{
				if (file_exists($file->getPathname()))
				{
					include ($file->getPathname());
				}
			}

			$tm->footer();
		}

		/**
		 * Управление системой
		 -----------------------*/
		if ($_REQUEST['dn'] == 'system')
		{
			global $db, $basepref, $conf, $lang, $sess, $tm, $_SERVER, $PLATFORM;

			$template['breadcrumb'] = array('<a href="'.ADMURL.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>', $lang['all_system']);

			$tm->header();

			$nod_menu = $nod_array = array();
			$nod_menu = new GlobIterator(ADMDIR.'/system/*/nod.menu.php');

			$templates = $tm->parsein($tm->create('system'));

			foreach ($nod_menu as $file)
			{
				if (file_exists($file->getPathname()))
				{
					include ($file->getPathname());

					$rows = null;
					if (isset($block) AND ! empty($block))
					{
						foreach ($block['link'] as $url => $name)
						{
							$box = is_array($name) ? $name[1] : null;
							$name = is_array($name) ? $name[0] : $name;
							$rows.= $tm->parse(array
								(
									'url'    => $url,
									'box'    => $box,
									'name'   => $name
								),
								$tm->manuale['rows']);
						}

						$posit = (strlen(intval($block['posit'])) > 1) ? $block['posit'] : '0'.$block['posit'];
						$nod_array[$posit] = $tm->parse(array
							(
								'nod_url'  => ADMURL.'/system/'.$block['id'].'/index.php?dn=index&amp;ops='.$sess['hash'],
								'nod_name' => $block['title'],
								'rows'     => $rows
							),
							$templates);
					}
				}
			}

			if ( ! empty($nod_array))
			{
				ksort($nod_array);
				foreach ($nod_array as $nod_block)
				{
					echo $nod_block;
				}
			}

			$tm->footer();
		}

		/**
		 * Поддержка
		 -----------------*/
		if ($_REQUEST['dn'] == 'support')
		{
			global $db, $basepref, $conf, $lang, $sess, $tm, $_SERVER, $PLATFORM;

			$template['breadcrumb'] = array('<a href="'.ADMURL.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>', $lang['support']);

			$tm->header();

			$support = NULL;
			$file_support = ($conf['langcode'] == 'ru') ? 'lang/support_ru.php' : 'lang/support_en.php';
			if (file_exists($file_support)) {
				include($file_support);
				$support = str_replace('{version}', $conf['version'], $content_support);
			}
			echo '	<div class="board">
						'.$support.'
					</div>';

			$tm->footer();
		}

		/**
		 * Выход из апанели
		 */
		if ($_REQUEST['dn'] == 'logout')
		{
			global $db, $basepref, $sess, $ADMIN_ID;

			if (isset($sess['hash'])) {
				$db->query("DELETE FROM ".$basepref."_admin_sess WHERE hash = '".$sess['hash']."'");
			}
			if (isset($_COOKIE[ACOOKIE])) {
				setcookie(ACOOKIE, '', NEWTIME-LIFE_ADMIN, ADMPATH.'/');
			}
			if (isset($_COOKIE['openmenu'])) {
				setcookie('openmenu', '', NEWTIME-LIFE_ADMIN, ADMPATH.'/');
			}
			$db->query("UPDATE ".$basepref."_admin SET adlast = '".time()."' WHERE admid = '".$ADMIN_ID."'");

			redirect("login.php");
		}

		/**
		 * Смена платформы
		 */
		if ($_REQUEST['dn'] == 'platformchange')
		{
			global $sess, $selplatform, $PLATFORM;

			$selplatform = preparse($selplatform, THIS_INT);
			if (in_array('platform', $ADMIN_PERM_ARRAY))
			{
				if (isset($PLATFORM[$selplatform]) OR $selplatform == 0) {
					setcookie(PCOOKIE, serialize(array($selplatform)), time() + LIFE_ADMIN, ADMPATH.'/');
				}
			}

			redirect($_SERVER['HTTP_REFERER']);
		}

		/**
		 * Выключение визуального редактора
		 */
		if ($_REQUEST['dn'] == 'nowys')
		{
			global $sess;

			setcookie(WCOOKIE, 'no');
			if (isset($_SERVER['HTTP_REFERER'])) {
				$location = $_SERVER['HTTP_REFERER'];
			} else {
				$location = "index.php?dn=index&amp;ops=".$sess['hash']."";
			}

			redirect($location);
		}

		/**
		 * Включение визуального редактора
		 */
		if ($_REQUEST['dn'] == 'yeswys')
		{
			global $sess;

			setcookie(WCOOKIE, 'yes');
			if (isset($_SERVER['HTTP_REFERER'])) {
				$location = $_SERVER['HTTP_REFERER'];
			} else {
				$location = "index.php?dn=index&amp;ops=".$sess['hash']."";
			}

			redirect("".$location."");
		}

		/**
		 * Закрыть сайт
		 */
		if ($_REQUEST['dn'] == 'closed')
		{
			global $set, $cache;

			$set = preparse($set, THIS_TRIM);
			if (isset($set) AND $set == 'yes' OR $set == 'no') {
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($set)."' WHERE setname = 'closed'");
			} else {
				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

	/**
	 * Редирект на страницу авторизации
	 */
	}
	else
	{
		if (isset($ops) AND $ops != $sess['hash']) {
			redirect(ADMURL.'/login.php?opsss=2');
		} elseif ( ! empty($sess['hash'])) {
			redirect(ADMURL.'/login.php?opsss=3');
		} else {
			redirect(ADMURL.'/login.php');
		}
	}
}
