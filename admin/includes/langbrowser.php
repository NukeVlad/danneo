<?php
/**
 * File:        /admin/includes/langbrowser.php
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
define('PERMISS', 'lang');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $wysiwyg, $lang, $sess, $ops, $cache;

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'add', 'save', 'setadd');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Основной интерфейс
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $langpackid, $langsetid, $langid;

			$first = $db->fetchrow
						(
							$db->query
							(
								"SELECT langsetid FROM ".$basepref."_language_setting
								 WHERE langpackid = '".$conf['langid']."'
								 ORDER BY langsetid ASC LIMIT 1"
							)
						);
			$langsetid = (isset($langsetid)) ? intval($langsetid) : $first['langsetid'];
			$result = $db->query
						(
							"SELECT * FROM ".$basepref."_language
							 WHERE langpackid = '".$conf['langid']."'
							 AND langsetid = '".$langsetid."'
							 ORDER BY langvars"
						);
			$all = $db->fetchrow
					(
						$db->query
						(
							"SELECT pack.langpack, COUNT(lang.langid) AS total
							 FROM ".$basepref."_language_pack AS pack
							 LEFT JOIN ".$basepref."_language AS lang ON (pack.langpackid = lang.langpackid)
							 WHERE pack.langpackid = '".$conf['langid']."'
							 GROUP BY pack.langpackid"
						)
					);
			$selinq = $db->query
						(
							"SELECT * FROM ".$basepref."_language_setting
							 WHERE langpackid = '".$conf['langid']."'"
						);
			$varsarray = $ajvaarray = array();
			while ($it = $db->fetchrow($result))
			{
				$varsarray[$it['langid']]['langvars'] = $it['langvars'];
				$ajvaarray[$it['langid']]['langvals'] = $it['langvals'];
			}
			echo '	<script>
					$(function() {
						var langsetid = '.$langsetid.';
						var textvars = new Array();';
			foreach ($ajvaarray as $id => $vars)
			{
				echo '	textvars['.$id.'] = \''.str_replace(array("\r\n", "\n"), '', $vars['langvals']).'\';';
			}
			echo '		$("#langid").change(function() {
							var i = $(this).val();
							var t = $(this).find(":selected").text();
							var content = textvars[i].replace("&quot;",\'"\');
							content = content.replace(\'&#039;\',"\'");
							$("#vals").val(content);
							$("#vars").val(t);
						});
						$("#lang-scroll form").submit(function() {
							$("#lang-scroll").hide();
							$("#cboxContent").css("background","url(template/images/loading.gif) 50% no-repeat");
							$(":input", this).each(function() {
								if(this.name == "langsetid") langsetid = this.value
							});
							$.ajax({
								async:false,
								cache: false,
								type : "POST",
								url  : this.action,
								data : $(this).serialize(),
								error: function() { alert($.errors); },
								success : function(data) {
									$.langbrowserupdate("'.$sess['hash'].'", langsetid);
								}
							});
							return false;
						});
					});
					</script>';
			echo '	<div id="lang-scroll">
					<div class="section">
					<form action="'.ADMPATH.'/includes/langbrowser.php?ops='.$sess['hash'].'&amp;dn=index" method="post">
					<table class="fb-work">
						<caption>'.$lang['lang_brow'].' &nbsp; &#8260; &nbsp; '.$all['langpack'].' ('.$all['total'].')</caption>
						<tr>
							<th class="ac" colspan="2">
								<select name="langsetid">';
			while($selitem = $db->fetchrow($selinq)){
				echo '				<option value="'.$selitem['langsetid'].'"'.(($selitem['langsetid'] == $langsetid) ? ' selected' : '').'>'.$selitem['langsetname'].'</option>';
			}
			echo '				</select>
								&nbsp;<input class="but" value=" '.$lang['go'].' " type="submit">
							</th>
						</tr>
					</table>
					</form>
					<form action="'.ADMPATH.'/includes/langbrowser.php?ops='.$sess['hash'].'&amp;dn=save" method="post">
					<table class="fb-work">
						<tr>
							<td class="strong ar" style="width: 50%;">'.$lang['all_value'].'</td>
							<td style="width: 50%;">
								<input type="text" style="width: 98%;" id="vars" name="vars" size="45" />
							</td>
						</tr>
						<tr>
							<td style="height: 100%;">
								<select class="app" style="width: 100%; height: 100px;" multiple="multiple" name="langid" id="langid">';
			foreach ($varsarray as $id => $vars)
			{
				echo '				<option value="'.$id.'">'.$vars['langvars'].'</option>';
			}
			echo '				</select>
							</td>
							<td>
								<textarea style="width: 98%; height: 100px" id="vals" name="vals"></textarea>
							</td>
						</tr>
						<tr>
							<td class="strong ar"></td>
							<td>
								<input name="del" type="checkbox" value="yes" /> &nbsp;'.$lang['all_delet'].'
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2" style="border-bottom: 1px solid #fff;">
								<input class="but" value=" '.$lang['all_save'].' " type="submit">
							</td>
						</tr>
					</table>
					</form>
					<form action="'.ADMPATH.'/includes/langbrowser.php?ops='.$sess['hash'].'&amp;dn=add" method="post">
					<table class="fb-work">
						<caption>'.$lang['add_vars_replace'].'</caption>
						<tr>
							<td class="strong ar" style="width: 50%;"><span>*</span> '.$lang['all_value'].'</td>
							<td style="width: 50%;">
								<input type="text" name="newvars" size="50" maxlength="150" style="width: 98%;" />
							</td>
						</tr>
						<tr>
							<td class="strong ar"><span>*</span> '.$lang['vars_replace'].'</td>
							<td>
								<textarea name="newtext" style="width: 98%; height: 100px;"></textarea>
							</td>
						</tr>
						<tr>
							<td class="strong ar"></td>
							<td>
								<input name="lcache" type="checkbox" value="yes" /> &nbsp;'.$lang['lang_cache'].'
							</td>
						</tr>
						<tr class="tfoot" style="border-bottom: 1px solid #fff;">
							<td colspan="2">
								<input type="hidden" name="langsetid" value="'.$langsetid.'" />
								<input class="but" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					<form action="'.ADMPATH.'/includes/langbrowser.php?ops='.$sess['hash'].'&amp;dn=setadd" method="post">
					<table class="fb-work">
						<caption>'.$lang['set_add'].'</caption>
						<tr>
							<td class="strong ar" style="width: 50%;"><span>*</span> '.$lang['all_name'].'</td>
							<td style="width: 50%;">
								<input type="text" name="newset" size="50" maxlength="150" style="width: 98%;" />
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="langsetid" value="'.$langsetid.'">
								<input class="but" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					</div>';
		}

		/**
		 * Добавление новой переменной / замены
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			global $db, $basepref, $newvars, $langsetid, $newtext, $lcache, $sess;

			$langsetid = preparse($langsetid, THIS_INT);

			if (
				! empty($newvars) AND
				! empty($newtext) AND
				preg_match('/^[a-zA-Z0-9_]+$/',$newvars) AND
				$langsetid
			) {
				$newvars = preparse($newvars, THIS_TRIM, 0, 255);
				$newtext = preparse_lga($newtext);
				$lcache = ($lcache == 'yes') ? 1 : 0;

				$inq = $db->numrows($db->query("SELECT langvars FROM ".$basepref."_language WHERE langvars = '".$newvars."'"));

				if ( ! $inq)
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_language VALUES (
							 NULL,
							 '".$conf['langid']."',
							 '".$langsetid."',
							 '".$newvars."',
							 '".$db->escape($newtext)."',
							 '".$db->escape($newtext)."',
							 '".$lcache."'
							 )"
						);

					$cache_lang = new DN\Cache\CacheLang;
					$cache_lang->cachelang();
				}
			}
		}

		/**
		 * Редактировать переменную / замену (сохранение)
		 --------------------------------------------------*/
		if ($_REQUEST['dn'] == 'save')
		{
			global $langid, $del, $vars, $vals;

			$vars = preparse($vars, THIS_TRIM, 0, 255);
			$vals = preparse_lga($vals);

			if( ! empty($langid))
			{
				$cache_lang = new DN\Cache\CacheLang;

				if (isset($del))
				{
					$db->query("DELETE FROM ".$basepref."_language WHERE langid = '".$langid."'");
					$cache_lang->cachelang();
				}
				else
				{
					if( ! empty($vars) AND ! empty($vals))
					{
						$db->query("UPDATE ".$basepref."_language SET langvals='".$db->escape($vals)."', langvars='".$db->escape($vars)."' WHERE langid = '".$langid."'");
						$cache_lang->cachelang();
					}
				}
			}
		}

		/**
		 * Добавить языковую группу
		 -----------------------------*/
		if ($_REQUEST['dn'] == 'setadd')
		{
			global $langsetid, $newset;

			$setmd5 = preparse($newset, THIS_MD_5);
			$sin = $db->query("SELECT langsetid FROM ".$basepref."_language_setting WHERE langsetmd5 = '".$setmd5."' AND langpackid = '".$conf['langid']."'");

			if ($db->numrows($sin) == 0 AND ! empty($newset))
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_language_setting VALUES (
						 NULL,
						 '".$conf['langid']."',
						 '".$db->escape($newset)."',
						 '".preparse($newset, THIS_MD_5)."'
						 )"
					);
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->blankstart();
		$tm->fberror($lang['no_access']);
		$tm->blankend();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
