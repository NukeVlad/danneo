<?php
/**
 * File:        /admin/mod/subscribe/index.php
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
define('PERMISS', 'subscribe');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $wysiwyg, $modname, $lang, $sess, $ops, $cache;

	if ( ! isset($modname[PERMISS]))
	{
		redirect(ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash']);
	}

	$template['breadcrumb'] = array
		(
			'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
			'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
			$modname[PERMISS]
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'start', 'save', 'del', 'export', 'exportsave', 'work', 'worksave', 'reloc', 'users', 'userdel', 'useract');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn']= (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Доп. функции мода
		 */
		include('mod.function.php');

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $tm, $lang, $sess;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['subscribe_menu_all'].'</a>'
					.'<a'.cho('start').' href="index.php?dn=start&amp;ops='.$sess['hash'].'">'.$lang['subscribe_menu_create'].'</a>'
					.'<a'.cho('export').' href="index.php?dn=export&amp;ops='.$sess['hash'].'">'.$lang['subscribe_menu_export'].'</a>'
					.'<a'.cho('users').' href="index.php?dn=users&amp;ops='.$sess['hash'].'">'.$lang['subscribe_mail_for'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Лист рассылок
		 --------------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			global $selective, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_menu_all']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}

			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_subscribe_archive ORDER BY archivid DESC LIMIT ".$sf.", ".$nu);

			$pages  = $lang['all_pages'].':&nbsp; '.adm_pages('subscribe_archive', 'archivid', 'index', 'list', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'], $nu);

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$lang['subscribe'].': '.$lang['subscribe_menu_all'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th class="al pw25">'.$lang['all_name'].'</th>
							<th>'.$lang['subscribe_mail_for'].'</th>
							<th>'.$lang['subscribe_while'].'</th>
							<th>'.$lang['subscribe_send_col'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			if ($db->numrows($inq) > 0)
			{
				while ($item = $db->fetchrow($inq))
				{
					$style = ($item['status'] == 'finish') ? 'noactive' : '';
					$stylework = ($item['status'] == 'finish') ? 'noactive' : '';
					$func = ($item['status'] == 'finish') ? 'reloc' : 'work';
					echo '	<tr class="list">
								<td class="'.$style.' vm al site">'.$item['title'].'</td>
								<td class="'.$style.'">'.$item['total'].'</td>
								<td class="'.$style.'">'.$item['step'].'</td>
								<td class="'.$stylework.'">'.($item['total'] - $item['send']).'&nbsp; &#8260; &nbsp;'.$item['send'].'</td>
								<td class="gov '.$style.'">
									<a href="index.php?dn='.$func.'&amp;archivid='.$item['archivid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['subscribe_send_work'].'" /></a>
									<a href="index.php?dn=del&amp;archivid='.$item['archivid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			else
			{
				echo '	<tr>
							<td class="ac" colspan="11">
								<div class="pads">'.$lang['down_brok_no'].'</div>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="5">'.$pages.'</td></tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Создание новой рассылки
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'start')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_menu_create']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['subscribe'].': '.$lang['subscribe_menu_create'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="subjects" size="50" required="required"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['subscribe_return_mail'].'</td>
							<td><input type="email" name="mail" value="'.$conf['site_mail'].'" size="50" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_format'].'</td>
							<td>
								<select name="formats" style="width: 95px;">
									<option value="0" selected>Text</option>
									<option value="1">Html</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<th class="site">'.$lang['subscribe_new_add_news'].'</th>
						</tr>
						<tr>
							<td>'.$lang['subscribe_new_add_news_title'].'</td>
							<td>
								<input type="radio" name="newsadd" value="yes"> '.$lang['all_yes'].' &nbsp;&nbsp;
								<input type="radio" name="newsadd" value="no" checked> '.$lang['all_no'].'
							</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_new_col'].'</td>
							<td>
								<select name="newscol" style="width: 55px;">';
			for ($i = 1; $i < 11; $i ++) {
				echo '				<option value="'.$i.'">'.$i.'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<th class="site">'.$lang['subscribe_format_mail_ignore_hint'].'</th>
						</tr>
						<tr>
							<td>'.$lang['subscribe_format_mail_ignore'].'</td>
							<td>
								<input type="radio" name="ignadd" value="yes"> '.$lang['all_yes'].' &nbsp;
								<input type="radio" name="ignadd" value="no" checked> '.$lang['all_no'].'
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<th class="site"><div>'.$lang['subscribe_while_hint'].'</div></th>
						</tr>
						<tr>
							<td>'.$lang['subscribe_while'].'</td>
							<td><input type="text" name="mailwhile" value="100" size="50" maxlength="8"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="save">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Создание новой рассылки (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'save')
		{
			global $subjects, $mail, $formats, $ignadd, $newsadd, $newscol, $mailwhile;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_menu_create']
				);

			$ignadd = ($ignadd == 'yes') ? 'yes' : 'no';
			$formats = preparse($formats, THIS_INT);
			$mailwhile = preparse($mailwhile, THIS_INT);
			$subjects = preparse($subjects, THIS_TRIM, 0, 255);
			$newscol = preparse($newscol, THIS_INT);

			$list = $text = null;
			$usercount = 0;

			if ($newsadd == 'yes' AND $newscol > 0 AND $newscol < 11)
			{
				$ninq = $db->query("SELECT * FROM ".$basepref."_news ORDER BY id DESC LIMIT ".$newscol."");
				$ncinq = $db->query("SELECT * FROM ".$basepref."_news_cat ORDER BY posit ASC");
				while ($c = $db->fetchrow($ncinq, $conf['cache'])) {
					$area[$c['catid']] = $c;
				}
				while ($nitem = $db->fetchrow($ninq))
				{
					$list.= $nitem['title']."\n";
					if (defined('SEOURL') AND ! empty($nitem['cpu'])) {
						$catnews = ( ! empty($area[$nitem['catid']]['catcpu'])) ? $area[$nitem['catid']]['catcpu'].'/' : '';
						$list.= $conf['site_url']."/news/".$catnews.$nitem['cpu'].SUF."\n";
					} else {
						$list.= $conf['site_url']."/index.php?dn=news&amp;to=page&amp;id=".$nitem['id']."\n";
					}
				}
				$text.= "\r\n".$lang['subscribe_last_news']."\r\n".$list;
			}

			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == "yes")
			{
				$user = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_user WHERE active = '1' AND blocked = '0' ORDER BY regdate"));
				$usercount = $usercount + $user['total'];
			}

			$user = ($ignadd == 'yes') ? $db->fetchrow
												(
													$db->query
													(
														"SELECT COUNT(*) AS total FROM ".$basepref."_subscribe_users
														 WHERE subactive = '1' ORDER BY regtime"
													)
												)
											 :
											 $db->fetchrow
												(
													$db->query
													(
														"SELECT COUNT(*) AS total FROM ".$basepref."_subscribe_users
														 WHERE subformat = '".$formats."'
														 AND subactive = '1'
														 ORDER BY regtime"
													)
												);

			$usercount = $usercount + $user['total'];

			if (verify_mail($mail) == 1 AND $subjects AND $usercount > 0)
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_subscribe_archive VALUES (
						 NULL,
						 '".$db->escape($subjects)."',
						 '".$db->escape($mail)."',
						 '".$db->escape($text)."',
						 '".$db->escape($formats)."',
						 '".$db->escape($ignadd)."',
						 'un',
						 '0',
						 '".$db->escape($usercount)."',
						 '".$db->escape($mailwhile)."'
						 )"
					);
			}
			else
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['subscribe_menu_create'], $lang['subscribe_error_empty']);
				$tm->footer();
			}

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Экспорт подписчиков
		 -------------------------*/
		if ($_REQUEST['dn'] == 'export')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_menu_export']
				);

			$tm->header();

			$sub = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_subscribe_users"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['subscribe'].': '.$lang['subscribe_menu_export'].'</caption>';
			if (isset($conf['user']['regact']) AND $conf['user']['regact'] == 'yes')
			{
				echo '	<tr>
							<td>'.$lang['subscribe_exp_user'].'</td>
							<td>
								<input type="radio" name="user_add" value="yes"> '.$lang['all_yes'].' &nbsp;&nbsp;
								<input type="radio" name="user_add" value="no" checked> '.$lang['all_no'].'
							</td>
						</tr>';
			}
			echo '		<tr>
							<td>'.$lang['subscribe_mail_for'].'</td>
							<td>'.$sub['total'].'</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_exp_add_name'].'</td>
							<td>
								<input type="radio" name="name_add" value="yes"> '.$lang['all_yes'].' &nbsp;&nbsp;
								<input type="radio" name="name_add" value="no" checked> '.$lang['all_no'].'
							</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_exp_raz_nm'].'</td>
							<td><input type="text" name="nm_raz" value="#" size="50"></td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_exp_raz'].'</td>
							<td><input type="text" name="raz" value="|" size="50"></td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_exp_where_brow'].'</td>
							<td><input type="radio" name="where" value="1" checked="checked"></td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_exp_where_dump'].'</td>
							<td><input type="radio" name="where" value="0"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="exportsave">';
			if (isset($conf['user']['regact']) AND $conf['user']['regact'] == 'no')
			{
				echo '			<input type="hidden" name="user_add" value="no">';
			}
			echo '				<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Экспорт подписчиков (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'exportsave')
		{
			global $where, $user_add, $name_add, $nm_raz, $raz;

			$text = '';

			if ( ! isset($nm_raz)) {
				$nm_raz = '#';
			}

			if ( ! isset($raz)) {
				$raz = '|';
			}

			$user = $db->tables("user");
			if ($user_add == 'yes' AND $user == 1)
			{
				$only_user = $db->query("SELECT uname, umail FROM ".$basepref."_user");
				while ($item = $db->fetchrow($only_user)) {
					$text.= $item['umail'];
					$text.= ($name_add == 'yes') ? $nm_raz.$item['uname'].$raz : $raz;
				}
			}

			$only_sub = $db->query("SELECT subname, submail FROM ".$basepref."_subscribe_users WHERE subactive = '1' ORDER BY regtime");

			while ($item = $db->fetchrow($only_sub))
			{
				$text.= $item['submail'];
				$text.= ($name_add == 'yes') ? $nm_raz.$item['subname'].$raz : $raz;
			}

			if ($where == 1)
			{
				$db->download($text, "dn_subscribers.txt", "text/plain");
			}
			else
			{
				$cache->cachefile('CMSDanneo.'.$conf['version'].'.subscribers.txt', $text, APANEL.'/dump/');
			}

			redirect('index.php?dn=export&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить рассылку
		 ----------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $archivid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_delet']
				);

			$archivid = preparse($archivid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_subscribe_archive WHERE archivid = '".$archivid."'");
				redirect('index.php?dn=index&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_subscribe_archive WHERE archivid = '".$archivid."'"));

				$yes = 'index.php?dn=del&amp;archivid='.$archivid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=index&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($modname[PERMISS], $lang['del_subscribe'], $yes, $not, preparse_un($item['title']));
				$tm->footer();
			}
		}

		/**
		 * Работать с рассылкой
		 -------------------------*/
		if ($_REQUEST['dn'] == 'work')
		{
			global $archivid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_send_work']
				);

			$tm->header();

			$archivid = preparse($archivid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_subscribe_archive WHERE archivid = '".$archivid."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['subscribe'].': '.$lang['subscribe_send_work'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="subjects" size="50" value="'.$item['title'].'" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_return_mail'].'</td>
							<td class="site">'.$item['mail'].'</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_send_col'].'</td>
							<td class="site">'.($item['total'] - $item['send']).'&nbsp; &#8260; &nbsp;'.$item['send'].'</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_arh_format'].'</td>
							<td class="site">'.(($item['formats'] == 0) ? 'Text' : 'Html').'</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['subscribe_arh_msgtext'].'</td>
							<td>';
								$tm->textarea('text', 10, 70, $item['text'], 1, '', '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['subscribe_while'].'</td>
							<td><input type="text" name="mailwhile" size="50" maxlength="8" value="'.$item['step'].'"></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="formats" value="'.$item['formats'].'">
								<input type="hidden" name="ignadd" value="'.$item['ignores'].'">
								<input type="hidden" name="archivid" value="'.$archivid.'">
								<input type="hidden" name="dn" value="worksave">
								<input class="main-button" value="'.$lang['subscribe_send'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Работать с рассылкой (отправка)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'worksave')
		{
			global $archivid, $formats, $ignadd, $subjects, $text, $mailwhile, $ro;

			$archivid  = preparse($archivid, THIS_INT);
			$formats   = preparse($formats, THIS_INT);
			$mailwhile = preparse($mailwhile, THIS_INT);
			$subjects  = preparse($subjects, THIS_TRIM, 0, 255);
			$text      = preparse($text, THIS_ADD_SLASH);

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			$sendarray = array();
			$i = 1; $usercount = 0;
			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				$user = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_user WHERE active = '1' AND blocked = '0' ORDER BY regdate"));
				$usercount = $usercount + $user['total'];
			}

			if ($ignadd == 'yes')
			{
				$user = $db->fetchrow
							(
								$db->query
								(
									"SELECT COUNT(*) AS total FROM ".$basepref."_subscribe_users
									 WHERE subactive = '1'
									 ORDER BY regtime"
								)
							);
			}
			else
			{
				$user = $db->fetchrow
							(
								$db->query
								(
									"SELECT COUNT(*) AS total FROM ".$basepref."_subscribe_users
									 WHERE subformat = '".$formats."'
									 AND subactive = '1'
									 ORDER BY regtime"
								)
							);
			}
			$usercount = $usercount + $user['total'];
			$db->query
				(
					"UPDATE ".$basepref."_subscribe_archive SET
					 title = '".$db->escape($subjects)."',
					 text  = '".$db->escape($text)."',
					 total = '".$db->escape($usercount)."',
					 step  = '".$db->escape($mailwhile)."'
					 WHERE archivid = '".$archivid."'"
				);

			$senditem = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_subscribe_archive WHERE archivid = '".$archivid."'"));

			if (isset($conf['user']['regtype']) AND $conf['user']['regtype'] == 'yes')
			{
				$inq = $db->query("SELECT uname,umail FROM ".$basepref."_user WHERE active = '1' AND blocked = '0' ORDER BY regdate");
				while ($item = $db->fetchrow($inq))
				{
					$sendarray[$i]['uname'] = $item['uname'];
					$sendarray[$i]['umail'] = $item['umail'];
					$i ++;
				}
			}

			$i = ($i > 1) ? ($i + 1) : 1;
			if ($ignadd == 'yes')
			{
				$inq = $db->query
						(
							"SELECT * FROM ".$basepref."_subscribe_users
							 WHERE subactive = '1'
							 ORDER BY regtime"
						);
			}
			else
			{
				$inq = $db->query
						(
							"SELECT * FROM ".$basepref."_subscribe_users
							 WHERE subformat = '".$formats."'
							 AND subactive = '1'
							 ORDER BY regtime"
						);
			}
			while ($item = $db->fetchrow($inq))
			{
				$sendarray[$i]['uname'] = $item['subname'];
				$sendarray[$i]['umail'] = $item['submail'];
				$i ++;
			}

			$countmail = 0;
			$newsend = ($senditem['step'] == 0) ? $sendarray : this_sub($sendarray, $senditem['send'], $senditem['step']);

			if (is_array($newsend))
			{
				if ($senditem['formats'] == 1)
				{
					$senditem['text'] = str_replace("{text}", $senditem['text'], $lang['subscribe_html']);
					$senditem['autortext'] = str_replace(array("{unsub}", "{site}", "{siteurl}"), array($conf['site_url'].$ro->seo('index.php?dn=subscribe&to=unsub'), $conf['site'], $conf['site_url']), $lang['subscribe_autor_html']);
				}
				else
				{
					$senditem['autortext'] = str_replace(array("<br />", '{unsub}', '{site}'), array("\r\n", $conf['site_url'].$ro->seo('index.php?dn=subscribe&to=unsub'), $conf['site']), $lang['subscribe_autor_text']);
				}

				foreach ($newsend as $key => $val)
				{
					$countmail ++;
					send_mail($val['umail'], $senditem['title'], $senditem['text'].$senditem['autortext'], $conf['site']." <".$senditem['mail'].">", $senditem['formats']);
				}

				if ($countmail > 0) {
					$db->query("UPDATE ".$basepref."_subscribe_archive SET send = send + '".$countmail."' WHERE archivid = '".$archivid."'");
				}
			}

			$senditem = $db->fetchrow($db->query("SELECT send, total FROM ".$basepref."_subscribe_archive WHERE archivid = '".$archivid."'"));
			if ($senditem['total'] <= $senditem['send'])
			{
				$db->query("UPDATE ".$basepref."_subscribe_archive SET status = 'finish', send = '".$senditem['send']."' WHERE archivid = '".$archivid."'");
			}

			redirect('index.php?dn=work&amp;archivid='.$archivid.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Изменение статуса рассылки
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'reloc')
		{
			global $archivid;

			$archivid = preparse($archivid, THIS_INT);
			if ($archivid)
			{
				$db->query("UPDATE ".$basepref."_subscribe_archive SET status = 'un', send = '0' WHERE archivid = '".$archivid."'");
			}

			redirect('index.php?dn=work&amp;archivid='.$archivid.'&amp;ops='.$sess['hash']);
		}

		/**
		 * Список подписчиков
		 ----------------------*/
		if ($_REQUEST['dn'] == 'users')
		{
			global $selective, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['subscribe_mail_for']
				);

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}

			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu,$conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf = $nu * ($p - 1);

			$inq = $db->query("SELECT * FROM ".$basepref."_subscribe_users ORDER BY subuserid DESC LIMIT ".$sf.", ".$nu);

			$pages  = $lang['all_pages'].':&nbsp; '.adm_pages('subscribe_users', 'subuserid', 'index', 'users', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=users&amp;p=".$p."&amp;ops=".$sess['hash'], $nu);

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$lang['subscribe'].': '.$lang['subscribe_mail_for'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th class="al pw15">'.$lang['in_name'].'</th>
							<th>'.$lang['subscribe_arh_mail'].'</th>
							<th class="ac">'.$lang['all_format'].'</th>
							<th class="ac">'.$lang['registr_date'].'</th>
							<th class="ac">'.$lang['sys_manage'].'</th>
						</tr>';
			if ($db->numrows($inq) > 0)
			{
				while ($item = $db->fetchrow($inq))
				{
					$style = ($item['subactive'] == '1') ? '' : 'no-active';
					$mail_format = ($item['subformat'] == '0') ? 'TEXT' : 'HTML';
					$act_link = ($item['subactive'] == '0') ? '<a href="index.php?dn=useract&amp;userid='.$item['subuserid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" alt="'.$lang['all_submint'].'" /></a>' : '';
					echo '	<tr class="list">
								<td class="'.$style.' vm al site">'.$item['subname'].'</td>
								<td class="'.$style.'">'.$item['submail'].'</td>
								<td class="'.$style.' ac">'.$mail_format.'</td>
								<td class="'.$style.' ac">'.format_time($item['regtime'], 0, 1).'</td>
								<td class="'.$style.' gov">
									'.$act_link.'
									<a href="index.php?dn=userdel&amp;userid='.$item['subuserid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
							</tr>';
				}
			}
			else
			{
				echo '	<tr>
							<td class="ac" colspan="11">
								<div class="pads">'.$lang['down_brok_no'].'</div>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="5">'.$pages.'</td></tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Удалить пользователя рассылки
		 ---------------------------------*/
		if ($_REQUEST['dn'] == 'userdel')
		{
			global $userid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['del_subscribe_user']
				);

			$userid = preparse($userid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_subscribe_users WHERE subuserid = '".$userid."'");
				redirect('index.php?dn=users&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_subscribe_users WHERE subuserid = '".$userid."'"));

				$yes = 'index.php?dn=userdel&amp;userid='.$userid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=users&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($modname[PERMISS], $lang['del_subscribe_user'], $yes, $not, $item['subname'].' ('.$item['submail'].')');
				$tm->footer();
			}
		}

		/**
		 * Изменение статуса активации рассылки
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'useract')
		{
			global $userid;

			$userid = preparse($userid, THIS_INT);

			if ($userid)
			{
				$db->query("UPDATE ".$basepref."_subscribe_users SET subactive = '1' WHERE subuserid = '".$userid."'");
			}

			redirect('index.php?dn=users&amp;ops='.$sess['hash']);
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($modname[PERMISS], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
