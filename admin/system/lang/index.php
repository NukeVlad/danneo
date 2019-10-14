<?php
/**
 * File:        /admin/system/lang/index.php
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
			$lang['lang']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID,  $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'langadd', 'langdef', 'langdel', 'langxml', 'langsetexp', 'langsetexpsave',
				'langsetdel', 'langseteditsave', 'langcache', 'langimp', 'langimpset', 'langsearch', 'langtranslit', 'langreturn',
				'langrep', 'langedit', 'langeditsave'
			);

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

			$link = '<a'.cho('index, langsearch').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('langadd').' href="index.php?dn=langadd&amp;ops='.$sess['hash'].'">'.$lang['lang_add'].'</a>'
					.'<a href="javascript:$.langbrowser(\''.$sess['hash'].'\')">'.$lang['lang_brow'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			global $dn;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['all_set']
				);

			$tm->header();

			$inq = $db->query
					(
						"SELECT pack.langpackid, pack.langpack, pack.langcharset, pack.langauthor, COUNT(lang.langid) AS total
						 FROM ".$basepref."_language_pack AS pack
						 LEFT JOIN ".$basepref."_language AS lang ON (pack.langpackid = lang.langpackid)
						 GROUP BY pack.langpackid
						 ORDER BY pack.langpackid ASC"
					);

			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['lang'].': '.$lang['all_set'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_name'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$count = $db->fetchrow($db->query("SELECT COUNT(langsetid) AS total FROM ".$basepref."_language_setting WHERE langpackid = '".$item['langpackid']."'"));
				$class = ($conf['langid'] == $item['langpackid']) ? 'work-lite' : 'work-clip';
				echo '	<tr>
							<td class="'.$class.'">
								'.(($conf['langid'] == $item['langpackid']) ? '<div class="server">'.$lang['lang_default'].'</div>' : '').'
								<div><span class="alternative">'.$item['langpack'].' - '.$item['langcharset'].'</span></div>
								<div>'.$count['total'].'&nbsp; &#8260; &nbsp;'.$item['total'].'</div>
								'.$item['langauthor'].'
							</td>
							<td class="'.$class.' vm gov">';
				if ($conf['langid'] != $item['langpackid']) {
					echo '		<a href="index.php?dn=langdef&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/add.png" alt="'.$lang['lang_default'].'" /></a>';
				}
				echo '			<a href="index.php?dn=langedit&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=langxml&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/export.png" alt="'.$lang['lang_exp'].'" /></a>
								<a href="index.php?dn=langsetexp&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/export2.png" alt="'.$lang['lang_exp_set'].'" /></a>
								<a href="index.php?dn=langtranslit&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/trans.png" alt="'.$lang['lang_translation'].'" /></a>';
				if ($conf['langid'] != $item['langpackid']) {
					echo '		<a href="index.php?dn=langdel&amp;lid='.$item['langpackid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
				}
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<form action="index.php" method="post">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="dn" value="langcache">
									<input class="main-button" value="'.$lang['lang_gen'].'" type="submit">
								</form>
							</td>
						</tr>
					</table>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['lang_search'].'</caption>
						<tr>
							<th class="ar">'.$lang['all_value'].'</th>
							<th>'.$lang['vars_replace'].'</th>
						</tr>
						<tr>
							<td><input type="text" size="50" name="seavars"></td>
							<td><textarea name="seatext" rows="3" cols="45" style="width: 99%"></textarea></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<select name="lid">';
			$inq = $db->query("SELECT * FROM ".$basepref."_language_pack");
			while ($item = $db->fetchrow($inq)) {
				echo '				<option value="'.$item['langpackid'].'"'.(($item['langpackid'] == $conf['langid']) ? ' selected' : '').'>'.$item['langpack'].'</option>';
			}
			echo '				</select>&nbsp;
								<input type="hidden" name="dn" value="langsearch">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="side-button" value="'.$lang['search'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить язык или новые группы
		 */
		if ($_REQUEST['dn'] == 'langadd')
		{
			global $lpackid, $CHECK_CHARSET;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" enctype="multipart/form-data" method="post">
					<table class="work">
						<caption>'.$lang['lang_add'].'</caption>
						<tr>
							<th class="ac site" colspan="2">'.$lang['lang_imp_xmlfile'].'</th>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_path'].'</td>
							<td>
								<input type="file" name="langis" required="required">
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langimp">
								<input accesskey="s" class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pads"></div>
					<div class="section">
					<form action="index.php" enctype="multipart/form-data" method="post">
					<table class="work">
						<tr>
							<th class="ac site" colspan="2">'.$lang['set_import'].'</th>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_path'].'</td>
							<td>
								<input type="file" name="langis" required="required">
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langimpset">
								<input accesskey="s" class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Язык по умолчанию
		 */
		if ($_REQUEST['dn'] == 'langdef')
		{
			global $lid;

			$lid = preparse($lid, THIS_INT);
			if ( ! empty($lid) AND $conf['langid'] != $lid)
			{
				$item = $db->fetchrow($db->query("SELECT langpackid, langcode, langcharset, langdateset, langloginset FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));

				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($lid)."' WHERE setname = 'langid'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($item['langcode'])."' WHERE setname = 'langcode'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($item['langcharset'])."' WHERE setname = 'langcharset'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($item['langdateset'])."' WHERE setname = 'langdateset'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($item['langloginset'])."' WHERE setname = 'langloginset'");
				$cache->cachesave(1);

				$cache = new DN\Cache\CacheLang;
				$cache->cachelang();

				$cache = new DN\Cache\CacheLogin;
				$cache->cachelogin();
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить язык
		 */
		if ($_REQUEST['dn'] == 'langdel')
		{
			global $lid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['all_delet']
				);

			$lid = preparse($lid, THIS_INT);

			if ($ok == 'yes' AND ! empty($lid) AND $conf['langid'] != $lid)
			{
				$db->query("DELETE FROM ".$basepref."_language WHERE langpackid = '".$lid."'");
				$db->query("DELETE FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");
				$db->query("DELETE FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'");

				$db->increment('language');
				$db->increment('language_setting');
				$db->increment('language_pack');

				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));

				$yes = 'index.php?dn=langdel&amp;lid='.$lid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=index&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $item['langpack'], $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Генерация языкового файла для кеш
		 */
		if ($_REQUEST['dn'] == 'langcache')
		{
			$cache = new DN\Cache\CacheLang;
			$cache->cachelang();

			$cache = new DN\Cache\CacheLogin;
			$cache->cachelogin();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Импорт языка из XML файла
		 */
		if ($_REQUEST['dn'] == 'langimp')
		{
			global $langis;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['lang_imp_xmlfile']
				);

			ignore_user_abort(true);
			$langis = $_FILES['langis'];

			if (is_uploaded_file($langis['tmp_name']) AND $langis['type'] == 'text/xml')
			{
				$fp = fopen($langis['tmp_name'], 'r');
				$content = fread($fp, filesize($langis['tmp_name']));
				fclose($fp);

				require_once(ADMDIR.'/core/classes/XML.php');
				$xml = new XML();
				$xml->read($content);
				$xml_array = $xml->parseout;

				if ( ! empty($xml_array) AND $xml_array['type'] == 'setup')
				{
					$newlang = array();
					foreach ($xml_array AS $key => $val)
					{
						if ($key == 'type') {
							$newlang['type'] = $val;
						}
						if ($key == 'atr') {
							$newlang['atr'] = $val;
						}
						if ($key == 'set') {
							$newlang['set'] = $val;
						}
					}
				}

				if (empty($newlang['atr']['packname']) OR empty($newlang['atr']['codes']) OR empty($newlang['atr']['charset']) OR empty($newlang['atr']['version']))
				{
					$tm->header();
					$tm->error($lang['lang_imp_xmlfile'], null, $lang['lang_bad_file']);
					$tm->footer();
				}

				if (isset($newlang['atr']['version']) AND $conf['version'] > $newlang['atr']['version'])
				{
					$tm->header();
					$tm->error($lang['lang_imp_xmlfile'], null, $lang['lang_old_text']);
					$tm->footer();
				}

				if ($db->numrows($db->query("SELECT langpackid FROM ".$basepref."_language_pack WHERE langcode='".$db->escape($newlang['atr']['codes'])."'")) > 0)
				{
                    $tm->header();
					$tm->error($lang['lang_imp_xmlfile'], null, $lang['lang_code_isset']);
					$tm->footer();
                }

				$db->query
					(
						"INSERT INTO ".$basepref."_language_pack VALUES (
						 NULL,
						 '".$db->escape($newlang['atr']['packname'])."',
						 '".$db->escape($newlang['atr']['codes'])."',
						 '".$db->escape($newlang['atr']['charset'])."',
						 '0',
						 '0',
						 '".$db->escape($newlang['atr']['author'])."'
						 )"
					);

				$newlangid = $db->insertid();

				foreach ($newlang['set'] AS $key => $val)
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_language_setting VALUES (
							 NULL,
							 '".$newlangid."',
							 '".$db->escape($val['name'])."',
							 '".preparse($val['name'],THIS_MD_5)."'
							 )"
						);
					$newlangset = $db->insertid();
					foreach ($val['lang'] AS $tag)
					{
						if ($tag['name'] AND $tag['name'] != 'empty' AND $tag['vals'])
						{
							$db->query
								(
									"INSERT INTO ".$basepref."_language VALUES (
									 NULL,
									 '".$newlangid."',
									 '".$newlangset."',
									 '".$db->escape($tag['name'])."',
									 '".$db->escape(preparse_lga($tag['vals']))."',
									 '".$db->escape(preparse_lga($tag['vals']))."',
									 '".$db->escape($tag['cache'])."')"
								);
						}
					}
				}
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Импорт языковых групп
		 */
		if ($_REQUEST['dn'] == 'langimpset')
		{
			global $langis;

			$langis = $_FILES['langis'];
			if (is_uploaded_file($langis['tmp_name']) AND $langis['type'] == 'text/xml')
			{
				$langs = new Lang();
				$langs->imp_group($langis['tmp_name'], 0);
			}

			$cache = new DN\Cache\CacheLang;
			$cache->cachelang();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Поиск значения / замены
		 */
		if ($_REQUEST['dn'] == 'langsearch')
		{
			global $seavars, $seatext, $prev, $next, $p, $lid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['search']
				);

			$tm->header();

			echo '	<script>
					$(function() {
						$(".langvars").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>';

			$lid = preparse($lid, THIS_INT);
			if (! empty($prev) OR ! empty($next)) {
				if(! empty($prev)) {
					$p -= 1;
				} else {
					$p += 1;
				}
			}
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = 10 * ($p - 1);
			$enter = 10;

			$SQL = ( ! empty($seavars)) ? "langvars LIKE '%".$seavars."%'" : "";
			$SQL.= ( ! empty($seavars) AND ! empty($seatext)) ? " OR " : "";
			$SQL.= ( ! empty($seatext)) ? "langvals LIKE '%".$seatext."%'" : "";
			$SQL = ( ! empty($SQL)) ? "(".$SQL.") AND " : "";

			$inq = $db->query
					(
						"SELECT lang.*,setting.langsetname FROM ".$basepref."_language AS lang
						 LEFT JOIN ".$basepref."_language_setting AS setting ON (lang.langsetid=setting.langsetid)
						 WHERE ".$SQL."lang.langpackid = '".$lid."'
						 GROUP BY lang.langid
						 ORDER BY setting.langsetname ASC LIMIT ".$sf.", ".$enter
					);

			$all = $db->fetchrow($db->query("SELECT COUNT(langid) AS total FROM ".$basepref."_language WHERE ".$SQL."langpackid = '".$lid."'"));
			$pages = sea_pages("language WHERE ".$SQL."langpackid = '".$lid."' ORDER BY langvars DESC", "langid", $enter, $p);

			if ($all['total'] > 10)
			{
				$f = '	<div class="pad"></div>
						<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<tr>
								<td>'.$lang['all_pages'].':&nbsp;
									<input type="hidden" name="seavars" value="'.$seavars.'">
									<input type="hidden" name="seatext" value="'.$seatext.'">
									<input type="hidden" name="lid" value="'.$lid.'">
									<input type="hidden" name="p" value="'.$p.'">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="dn" value="langsearch">
									'.$pages.'
								</td>
							</tr>
						</table>
						</form>
						</div>';
			}
			echo '		<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['lang_search'].' ( '.$all['total'].' )</caption>
							<tr>
								<th class="ar">'.$lang['all_value'].'</th>
								<th>'.$lang['vars_replace'].'</th>
							</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '		<tr>
								<td class="first">
									<input type="text" size="30" class="langvars" value="$lang[\''.$item['langvars'].'\']" readonly="readonly">
									<div class="pads">
										'.$lang['lang_cache'].' &nbsp;
										<input type="checkbox" name="lcache['.$item['langid'].']" value="1"'.(($item['langcache'] == 1) ? ' checked' : '').'>
									</div>';
				if ($item['langvals'] != $item['langvalsold'])
				{
					echo '			<div class="pad">
										<a href="index.php?dn=langreturn&amp;langid='.$item['langid'].'&amp;ops='.$sess['hash'].'">
											<img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/off.png" alt="'.$lang['lang_return'].'" />
										</a>
									</div>';
				}
				echo '			</td>
								<td>';
									$tm->textarea('text['.$item['langid'].']', 5, 50, preparse_unhtml($item['langvals']), 1);
				echo '			</td>
							</tr>';
			}
			echo '			<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="seavars" value="'.$seavars.'">
									<input type="hidden" name="seatext" value="'.$seatext.'">
									<input type="hidden" name="lid" value="'.$lid.'">
									<input type="hidden" name="p" value="'.$p.'">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="dn" value="langrep">
									<input class="main-button" value="'.$lang['all_save'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>
						'.(($all['total'] > 10) ? $f : '').'
						<div class="pad"></div>
						<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['lang_search'].'</caption>
							<tr>
								<th class="ar">'.$lang['all_value'].'</th>
								<th>'.$lang['vars_replace'].'</th>
							</tr>
							<tr>
								<td>
									<input type="text" size="50" name="seavars" value="'.$seavars.'">
								</td>
								<td>
									<textarea name="seatext" rows="3" cols="45" style="width:99%">'.$seatext.'</textarea>
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<select name="lid">';
			$inq = $db->query("SELECT * FROM ".$basepref."_language_pack");
			while ($item = $db->fetchrow($inq)) {
				echo '					<option value="'.$item['langpackid'].'"'.(($item['langpackid'] == $conf['langid']) ? ' selected' : '').'>'.$item['langpack'].'</option>';
			}
			echo '					</select>&nbsp;
									<input type="hidden" name="dn" value="langsearch">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input class="side-button" value="'.$lang['search'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';

			$tm->footer();
		}

		/**
		 * Перевод
		 */
		if ($_REQUEST['dn']=='langtranslit')
		{
			global $nu, $p, $lid, $sid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['lang_translation']
				);

			$tm->header();

			echo '	<script>
					$(function() {
						$(".langvars").focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>';

			$nu = ( ! isset($nu)) ? 10 : $nu;

			$sinq  = ( ! isset($sid)) ? "" : " AND langsetid = '".$sid."'";
			$sinqs = ( ! isset($sid)) ? "" : " AND lang.langsetid = '".$sid."'";
			$slink = ( ! isset($sid)) ? "" : "&amp;sid=".$sid;

			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$lid = preparse($lid, THIS_INT);
			$selinq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");

			$inq = $db->query
					(
						"SELECT lang.*, setting.langsetname FROM ".$basepref."_language AS lang
						 LEFT JOIN ".$basepref."_language_setting AS setting ON (lang.langsetid = setting.langsetid)
						 WHERE lang.langpackid = '".$lid."'".$sinqs."
						 GROUP BY lang.langid
						 ORDER BY setting.langsetname ASC LIMIT ".$sf.", ".$nu
					);

			$all = $db->fetchrow($db->query("SELECT COUNT(langid) AS total FROM ".$basepref."_language WHERE langpackid = '".$lid."'"));

			$pages = $lang['all_pages'].":&nbsp; ".adm_pages("language WHERE langpackid = '".$lid."'".$sinq." ORDER BY langvars DESC", "langid", "index", "langtranslit&amp;lid=".$lid.$slink, $nu, $p, $sess);
			$amount = $lang['all_col'].':&nbsp; '.amount_pages("index.php?dn=langtranslit&amp;p=".$p."&amp;lid=".$lid.$slink."&amp;ops=".$sess['hash'], $nu);

			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['lang_search'].' ( '.$all['total'].' )</caption>
						<tr class="tfoot">
							<td colspan="2">
								<form action="index.php" method="post">
									<select name="sid">';
			$langsetname = '';
			while($selitem = $db->fetchrow($selinq)){
				echo '					<option value="'.$selitem['langsetid'].'"'.(($selitem['langsetid'] == $sid) ? ' selected' : '').'>'.$selitem['langsetname'].'</option>';
				$langsetname.= ($selitem['langsetid'] == $sid) ? $selitem['langsetname'] : '';
			}
			echo '					</select>
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="lid" value="'.$lid.'">
									<input type="hidden" name="dn" value="langtranslit">
									<input class="side-button" value="'.$lang['all_sorting'].'" type="submit">
								</form>
							</td>
						</tr>
						<tr>
							<td>'.$lang['set_name'].'</td>
							<td class="vm"><span class="server">'.( ! empty($langsetname) ? $langsetname : $lang['all_all']).'</span><div class="fr">'.$amount.'</div></td>
						</tr>
					</table>
					<form action="index.php" method="post">
					<table class="work">
						<tr>
							<th class="ar">'.$lang['all_value'].'</th>
							<th>'.$lang['vars_replace'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="first">
								<input type="text" size="30" class="langvars" value="$lang[\''.$item['langvars'].'\']" readonly="readonly">
								<div class="pads">
									'.$lang['lang_cache'].' &nbsp;
									<input type="checkbox" name="lcache['.$item['langid'].']" value="1"'.(($item['langcache'] == 1) ? ' checked' : '').'>
								</div>';
				if ($item['langvals'] != $item['langvalsold'])
				{
					echo '		<div class="pads">
									<a href="index.php?dn=langreturn&amp;langid='.$item['langid'].'&amp;p='.$p.'&amp;nu='.$nu.'&amp;tr=1&amp;lid='.$lid.$slink.'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/off.png" alt="'.$lang['lang_return'].'" /></a>
								</div>';
				}
				echo '		</td>
							<td>';
								$tm->textarea('text['.$item['langid'].']', 5, 50, preparse_unhtml($item['langvals']), 1);
				echo '		</td>
						</tr>';
			}
			echo '		<tr><td colspan="2">'.$pages.'</td></tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="lid" value="'.$lid.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="p" value="'.$p.'">
								'.((isset($sid)) ? '<input type="hidden" name="sid" value="'.$sid.'">' : '').'
								<input type="hidden" name="tr" value="1">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langrep">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Откат назад после изменения
		 */
		if($_REQUEST['dn']=='langreturn')
		{
			global $tr, $p, $nu, $lid, $sid, $langid;

			$langid = preparse($langid, THIS_INT);
			$old = $db->fetchrow($db->query("SELECT langvalsold FROM ".$basepref."_language WHERE langid = '".$langid."'"));

			if ($old['langvalsold'])
			{
				$db->query("UPDATE ".$basepref."_language SET langvals = '".$db->escape($old['langvalsold'])."' WHERE langid = '".$langid."'");
				$cache = new DN\Cache\CacheLang;
				$cache->cachelang();
			}

			if ($tr == 1)
			{
				$redir = 'index.php?dn=langtranslit&amp;lid='.$lid.'&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($sid)) ? '&amp;sid='.preparse($sid, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				redirect($redir);
			}
			else
			{
				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Сохранение изменений
		 */
		if ($_REQUEST['dn'] == 'langrep')
		{
			global $text, $lcache, $tr, $p, $nu, $lid, $sid;

			foreach ($text as $key => $val)
			{
				$newtext = preparse_lga($val);
				$newtext = preparse($newtext, THIS_TRIM);
				$key = preparse($key, THIS_INT);
				$dbcache = (isset($lcache[$key])) ? $lcache[$key] : 0;
				$db->query("UPDATE ".$basepref."_language SET langvals = '".$db->escape($newtext)."', langcache = '".$dbcache."' WHERE langid = '".$key."'");
			}
			$cache = new DN\Cache\CacheLang;
			$cache->cachelang();

			if ($tr == 1)
			{
				$redir = 'index.php?dn=langtranslit&amp;lid='.$lid.'&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($sid)) ? '&amp;sid='.preparse($sid, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				redirect($redir);
			}
			else
			{
				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
		}

		/**
		 * Редактировать язык
		 */
		if ($_REQUEST['dn'] == 'langedit')
		{
			global $lid, $CHECK_CHARSET;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			$lid = preparse($lid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));
			$inq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].'&nbsp; &#8260; &nbsp;'.$item['langpack'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td>
								<input type="text" name="langpack" size="35" maxlength="100" value="'.$item['langpack'].'">
							</td>
						</tr>
						<tr>
							<td>'.$lang['code_page'].'</td>
							<td>
								<select name="langcharset">';
			foreach (CHECK_CHARSET as $out)
			{
				echo '				<option value="'.$out.'"'.(($out == $item['langcharset']) ? ' selected' : '').'>'.$out.'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['lang_code'].'</td>
							<td>
								<input type="text" name="langcode" size="35" maxlength="4" value="'.$item['langcode'].'">
							</td>
						</tr>
						<tr>
							<td>'.$lang['set_data'].'</td>
							<td>
								<select name="langdateset">';
			$dateinq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");
			while ($ditem = $db->fetchrow($dateinq)) {
				echo '				<option value="'.$ditem['langsetid'].'"'.(($ditem['langsetid'] == $conf['langdateset']) ? ' selected' : '').'>'.$ditem['langsetname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['set_login'].'</td>
							<td>
								<select name="langloginset">';
			$logininq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");
			while ($litem = $db->fetchrow($logininq)) {
				echo '				<option value="'.$litem['langsetid'].'"'.(($litem['langsetid'] == $conf['langloginset']) ? ' selected' : '').'>'.$litem['langsetname'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['author'].'</td>
							<td>';
								$tm->textarea('langauthor', 5, 50, $item['langauthor'], 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langeditsave">
								<input type="hidden" name="lid" value="'.$lid.'">
								<input class="main-button" value=" '.$lang['all_save'].' " type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['set_name'].'&nbsp; &#8260; &nbsp;'.$item['langpack'].'</caption>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td class="first"><span>*</span> '.$lang['set_name'].' № '.$item['langsetid'].'</td>
							<td>
								<input type="text" name="langsetname['.$item['langsetid'].']" size="65" maxlength="80" value="'.$item['langsetname'].'">';
				if ($item['langsetid'] != $conf['langdateset'] AND $item['langsetid'] != $conf['langloginset'])
				{
					echo '		<a class="but" href="index.php?dn=langsetdel&amp;sid='.$item['langsetid'].'&amp;lid='.$lid.'&amp;ops='.$sess['hash'].'" title="'.$lang['all_delet'].'"> &#215; </a>';
				}
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langseteditsave">
								<input type="hidden" name="lid" value="'.$lid.'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать язык (сохранение)
		 */
		if ($_REQUEST['dn']=='langeditsave')
		{
			global $lid, $langpack, $langcharset, $langcode, $langdateset, $langloginset, $langauthor;

			ignore_user_abort(true);

			if (
				! empty($lid) AND
				! empty($langpack) AND
				! empty($langcharset) AND
				! empty($langcode) AND
				! empty($langdateset) AND
				! empty($langloginset)
			) {
				$lid = preparse($lid, THIS_INT);
				$langdateset = preparse($langdateset, THIS_INT);
				$langloginset = preparse($langloginset, THIS_INT);
				$langpack = preparse($langpack, THIS_TRIM, 1, 100);
				$langcharset = preparse($langcharset, THIS_TRIM, 0, 25);
				$langcode = preparse($langcode, THIS_TRIM, 1, 4);
				$langauthor = preparse($langauthor, THIS_TRIM);

				$db->query
					(
						"UPDATE ".$basepref."_language_pack SET
						 langpack    = '".$langpack."',
						 langcode    = '".$langcode."',
						 langcharset = '".$langcharset."',
						 langdateset = '".$langdateset."',
						 langloginset = '".$langloginset."',
						 langauthor  = '".$db->escape($langauthor)."'
						 WHERE langpackid = '".$lid."'"
					);

				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($langcode)."' WHERE setname='langcode'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($langcharset)."' WHERE setname='langcharset'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($langdateset)."' WHERE setname='langdateset'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($langloginset)."' WHERE setname='langloginset'");
			}

			$cache->cachesave(1);

			$cache = new DN\Cache\CacheLogin;
			$cache->cachelogin();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать языковые группы (сохранение)
		 */
		if ($_REQUEST['dn'] == 'langseteditsave')
		{
			global $langsetname;

			foreach ($langsetname as $id => $val)
			{
				if (isset($id) AND isset($val))
				{
					$id = preparse($id, THIS_INT);
					$val = preparse($val, THIS_TRIM, 0, 80);
					$valmd5 = preparse($val, THIS_MD_5);
					$db->query
						(
							"UPDATE ".$basepref."_language_setting SET
							 langsetname = '".$db->escape($val)."',
							 langsetmd5  = '".$valmd5."'
							 WHERE langsetid = '".$id."'"
						);
				}
			}

			$cache = new DN\Cache\CacheLang;
			$cache->cachelang();

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Экспорт групп настроек в XML файл
		 */
		if ($_REQUEST['dn'] == 'langsetexp')
		{
			global $lid;

			$lid = preparse($lid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_pack WHERE langpackid = '".$lid."'"));
			$inq = $db->query("SELECT * FROM ".$basepref."_language_setting WHERE langpackid = '".$lid."'");

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$item['langpack']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post" name="setexp" id="setexp">
					<table class="work">
						<caption>'.$item['langpack'].': '.$lang['lang_exp_set'].'</caption>
						<tr>
							<td>'.$lang['set_name'].'</td>
							<td>
								<input type="checkbox" name="checkboxall" id="checkboxall" value="yes">
							</td>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr>
							<td>'.$item['langsetname'].'</td>
							<td>
								<input type="checkbox" name="langsetid['.$item['langsetid'].']" value="1">
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="langsetexpsave">
								<input type="hidden" name="lid" value="'.$lid.'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Экспорт групп настроек в XML файл (сохранение)
		 */
		if ($_REQUEST['dn']== 'langsetexpsave')
		{
			global $lid, $langsetid;

			if (is_array($langsetid))
			{
				$cache = new DN\Cache\CacheLang;
				$cache->exportlang(2, $lid, $langsetid);
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Экспорт языка в XML файл (сохранение)
		 */
		if ($_REQUEST['dn'] == 'langxml')
		{
			global $lid;

			$cache = new DN\Cache\CacheLang;
			$cache->exportlang(1, $lid);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление языковой группы
		 */
		if ($_REQUEST['dn'] == 'langsetdel')
		{
			global $ok, $sid, $lid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['lang'].'</a>',
					$lang['del_langset']
				);

			$sid = preparse($sid, THIS_INT);

			if ($ok == 'yes' AND $sid)
			{
				$db->query("DELETE FROM ".$basepref."_language WHERE langsetid = '".$sid."'");
				$db->query("DELETE FROM ".$basepref."_language_setting WHERE langsetid = '".$sid."'");

				$db->increment('language');
				$db->increment('language_setting');

				$cache = new DN\Cache\CacheLang;
				$cache->cachelang();

				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_language_setting WHERE langsetid = '".$sid."'"));

				$yes = 'index.php?dn=langsetdel&amp;sid='.$sid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=langedit&amp;lid='.$lid.'&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['del_langset'], $item['langsetname'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['lang'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
