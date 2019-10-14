<?php
/**
 * File:        /admin/system/seo/index.php
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
			$lang['seo']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID,$CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array
			(
				'index', 'upsave', 'smsc', 'smsru'
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

			$link = '<a'.cho('index, sitemapsave').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['site_map'].'</a>'
					.'<a'.cho('linking, linkedit, linkdel, linkeditsave').' href="index.php?dn=linking&amp;ops='.$sess['hash'].'">'.$lang['seo_link'].'</a>'
					.'<a'.cho('social, socialedit').' href="index.php?dn=social&amp;ops='.$sess['hash'].'">'.$lang['social_bookmark'].'</a>';

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
			global $lang, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['nod_sms'].'</a>',
					$lang['opt_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_set'].' SMS</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' AND setname IN ('use_sms', 'service_sms')");
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
								<input type="hidden" name="req" value="index">
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
		 * Сервис SMS-центр
		 */
		if ($_REQUEST['dn'] == 'smsc')
		{
			global $lang, $conf, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['nod_sms'].'</a>',
					$lang['sms_center']
				);

			$tm->header();

			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' AND setname IN ('smsc_login', 'smsc_password')");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['sms_center'].': '.$lang['all_set'].'</caption>';
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
								<input type="hidden" name="req" value="smsc">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
						<table class="work">
							<caption>'.$lang['api_service'].'</caption>
							<script>
							$(function(){
							jQuery.request = function(opt, serv, sess) {
								$.ajax({
									cache: false,
									type: "POST",
									url: $.apanel + "/system/'.PERMISS.'/ajax.php",
									data: {
										dn: "request",
										service: serv,
										option: opt,
										ops: sess
									},
									error: function(msg) {},
									success: function(data) {
										if (data.length > 0) {
											$("#" + opt).html(data);
										}
									}
								});
							}
							});
							</script>
							<tr>
								<td class="first vm vars norm">'.$lang['check_login'].'</td>
								<td class="vm pw15"><a class="side-button" href="javascript:$.request(\'check\',\'smsc\',\''.$sess['hash'].'\')">'.$lang['all_go'].'</a></td>
								<td class="vm" style="background-color: #f8f8f9 !important">
									<div id="check"><span class="light">►</span></div>
								</td>
							</tr>
							<tr>
								<td class="first vm vars norm">'.$lang['check_balance'].'</td>
								<td class="vm pw15"><a class="side-button" href="javascript:$.request(\'balance\',\'smsc\',\''.$sess['hash'].'\')">'.$lang['all_go'].'</a></td>
								<td class="vm black">
									<div id="balance"><span class="light">►</span></div>
								</td>
							</tr>
						</table>
					</div>
					<div class="pad"></div>';
			$info = ADMDIR.'/system/sms/SMSC.md';
			if (file_exists($info))
			{
				$string = file_get_contents($info);
				$print = Markdown::defaultTransform($string);
			}
			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['all_decs'].'</caption>
						<tr>
							<td class="pw100" style="padding:0">
								<div class="markdown al" style="margin:0">
									'.(isset($print) ? $print : $lang['data_not']).'
								</div>
							</td>
						</tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Сервис SMS.RU
		 */
		if ($_REQUEST['dn'] == 'smsru')
		{
			global $lang, $conf, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['nod_sms'].'</a>',
					'SMS.RU'
				);

			$tm->header();

			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."' AND setname IN ('smsru_auth', 'smsru_login', 'smsru_password', 'smsru_api_id')");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>SMS.RU: '.$lang['all_set'].'</caption>';
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
								<input type="hidden" name="req" value="smsru">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
						<table class="work">
							<caption>'.$lang['api_service'].'</caption>
							<script>
							$(function(){
							jQuery.request = function(opt, serv, sess) {
								$.ajax({
									cache: false,
									type: "POST",
									url: $.apanel + "/system/'.PERMISS.'/ajax.php",
									data: {
										dn: "request",
										service: serv,
										option: opt,
										ops: sess
									},
									error: function(msg) {},
									success: function(data) {
										if (data.length > 0) {
											$("#" + opt).html(data);
										}
									}
								});
							}
							});
							</script>
							<tr>
								<td class="first vm vars norm">'.$lang['check_login'].'</td>
								<td class="vm pw15"><a class="side-button" href="javascript:$.request(\'check\',\'smsru\',\''.$sess['hash'].'\')">'.$lang['all_go'].'</a></td>
								<td class="vm" style="background-color: #f8f8f9 !important">
									<div id="check"><span class="light">►</span></div>
								</td>
							</tr>
							<tr>
								<td class="first vm vars norm">'.$lang['check_balance'].'</td>
								<td class="vm pw15"><a class="side-button" href="javascript:$.request(\'balance\',\'smsru\',\''.$sess['hash'].'\')">'.$lang['all_go'].'</a></td>
								<td class="vm black">
									<div id="balance"><span class="light">►</span></div>
								</td>
							</tr>
						</table>
					</div>
					<div class="pad"></div>';
			$info = ADMDIR.'/system/sms/SMS.RU.md';
			if (file_exists($info))
			{
				$string = file_get_contents($info);
				$print = Markdown::defaultTransform($string);
			}
			echo '	<div class="section">
					<table class="work">
						<caption>'.$lang['all_decs'].'</caption>
						<tr>
							<td class="pw100" style="padding:0">
								<div class="markdown al" style="margin:0">
									'.(isset($print) ? $print : $lang['data_not']).'
								</div>
							</td>
						</tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Сохранение настроек
		 */
		if ($_REQUEST['dn'] == 'upsave')
		{
			global $req, $set, $conf, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['nod_sms'].'</a>',
					$lang['opt_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'sms'");
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
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse(preparse_sp($set[$item['setname']]), THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn='.$req.'&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['seo'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
