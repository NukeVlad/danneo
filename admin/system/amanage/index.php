<?php
/**
 * File:        /admin/system/amanage/index.php
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
			$lang['amanage']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'list', 'add', 'save', 'passsave', 'mailsave', 'admdel', 'admedit', 'admeditsave', 'blocked');

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

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['personal_cabinet'].'</a>'
					.'<a'.cho('list').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['list_admin'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['add_admin'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Личный кабинет
		 */
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['amanage'].'</a>',
					$lang['personal_cabinet']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post" name="total_form" id="total_form">
					<table class="work">
						<caption>'.$lang['personal_cabinet'].'</caption>
						<tr>
							<th>&nbsp;</th>
							<th><strong>'.$lang['chang_email'].'</strong></th>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td>
								<input name="newmail_1" size="25" maxlength="50" type="text" value="'.$ADMIN_MAIL.'">';
								$tm->outhint($lang['amanage_hint_email']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['re_e_mail'].'</td>
							<td><input name="newmail_2" size="25" maxlength="50" type="text" value="'.$ADMIN_MAIL.'"></td>
						</tr>
						<tr>
							<td>'.$lang['secret_word'].'</td>
							<td>
								<input name="s_word" size="25" maxlength="50" type="password" required>';
								$tm->outhint($lang['confirm_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="mailsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['chang_button_email'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<tr>
							<th>&nbsp;</th>
							<th><strong>'.$lang['chang_pass'].'</strong></th>
						</tr>
						<tr>
							<td>'.$lang['pass'].'</td>
							<td>
								<input name="newpass_1" size="25" maxlength="50" type="text">';
								$tm->outhint($lang['amanage_hint_pass']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['re_pass'].'</td>
							<td><input name="newpass_2" size="25" maxlength="50" type="text"></td>
						</tr>
						<tr>
							<td>'.$lang['secret_word'].'</td>
							<td>
								<input name="s_word" size="25" maxlength="50" type="password" required>';
								$tm->outhint($lang['confirm_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="passsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['chang_button_pass'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Смена E-Mail
		 */
		if ($_REQUEST['dn'] == 'mailsave')
		{
			global $newmail_1, $newmail_2, $s_word, $ADMIN_ID, $CHECK_ADMIN, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['amanage'], $lang['personal_cabinet']
				);

			if ($newmail_1 <> $newmail_2)
			{
				$tm->header();
				$tm->error($lang['chang_email'], null, $lang['not_identical_email']);
				$tm->footer();
			}

			if (verify_mail($newmail_1) == 0)
			{
				$tm->header();
				$tm->error($lang['chang_email'], null, $lang['bad_mail']);
				$tm->footer();
			}

			if($s_word <> $CHECK_ADMIN['sword'] OR empty($ADMIN_ID))
			{
				$tm->header();
				$tm->error($lang['personal_cabinet'], $lang['chang_email'], $lang['bad_secret_word']);
				$tm->footer();
			}

			$db->query("UPDATE ".$basepref."_admin SET admail = '".$db->escape($newmail_1)."' WHERE admid = '".intval($ADMIN_ID)."'");

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Смена пароля
		 */
		if ($_REQUEST['dn'] == 'passsave')
		{
			global $newpass_1, $newpass_2, $s_word, $ADMIN_ID, $CHECK_ADMIN, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['amanage'], $lang['personal_cabinet']
				);

			if ($newpass_1 <> $newpass_2)
			{
				$tm->header();
				$tm->error($lang['chang_pass'], null, $lang['not_identical_pass']);
				$tm->footer();
			}

			if($s_word <> $CHECK_ADMIN['sword'] OR empty($ADMIN_ID))
			{
				$tm->header();
				$tm->error($lang['chang_pass'], null, $lang['bad_secret_word']);
				$tm->footer();
			}

			$md_pass = md5(trim($newpass_1));
			$db->query("UPDATE ".$basepref."_admin SET adpwd = '".$md_pass."' WHERE admid = '".intval($ADMIN_ID)."'");

			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление администратора
		 */
		if ($_REQUEST['dn'] == 'add')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['amanage'], $lang['add_admin']
				);

			$tm->header();

			$inq = $db->query("SELECT * FROM ".$basepref."_mods");
			while ($c = $db->fetchassoc($inq))
			{
				$mod[$c['file']] = $c['parent'];
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_admin'].'</caption>
						<tr>
							<td>'.$lang['adm_login'].'</td>
							<td><input name="newlog" size="25" maxlength="15" type="text"></td>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td><input name="newmail" size="25" maxlength="50" type="text"></td>
						</tr>
						<tr>
							<td>'.$lang['pass'].'</td>
							<td><input name="newpass" size="25" maxlength="15" type="text"></td>
						</tr>
						<tr>
							<th class="ar"><strong>'.$lang['all_access'].'</strong></th>
							<th class="site is">'.$lang['amanage_hint_access'].'</th>
						</tr>
						<tr>
							<td></td>
							<td class="bold">'.$lang['opt_manage_mod'].'</td>
						</tr>';
			natsort($realmod);
			foreach($realmod as $val)
			{
				if ($mod[$val] == 0)
				{
					echo '	<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no" checked> '.$lang['all_no'].'
								</td>
							</tr>';
				}
			}
			echo '		<tr>
							<td></td>
							<td class="bold">'.$lang['all_system'].'</td>
						</tr>';
			foreach($LIST_MOD_ADM as $val)
			{
				if ($val <> 'filebrowser')
				{
					echo '	<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no" checked> '.$lang['all_no'].'
								</td>
							</tr>';
				}
				else
				{
					echo '	<tr>
								<td></td>
								<td class="bold">'.$lang['manage_files'].'</td>
							</tr>
							<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no" checked> '.$lang['all_no'].'
								</td>
							</tr>';
				}
			}
			echo '		<tr>
							<th class="ar"><strong>'.$lang['confirm'].'</strong></th>
							<th class="site is">'.$lang['confirm_hint'].'</th>
						</tr>
						<tr>
							<td></td>
							<td><input name="s_word" size="25" maxlength="50" type="password" required></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
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
		 * Добавление администратора (сохранение)
		 */
		if ($_REQUEST['dn'] == 'save')
		{
			global $newlog, $newmail, $newpass, $perm_mod, $s_word, $send_mail, $ADMIN_ID, $CHECK_ADMIN;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['amanage'], $lang['add_admin']
				);

			$newlog = preparse_dn($newlog);

			if (
				empty($newlog) OR
				empty($newmail) OR
				empty($newpass) OR
				mb_strlen($newlog) < 4 OR
				mb_strlen($newlog) > 15 OR
				mb_strlen($newpass) < 4 OR
				mb_strlen($newpass) > 15
			) {
				$tm->header();
				$tm->error($lang['add_admin'], null, $lang['amange_er_login_or_pass']);
				$tm->footer();
			}

			if (verify_mail($newmail) == 0)
			{
				$tm->header();
				$tm->error($lang['add_admin'], null, $lang['bad_mail']);
				$tm->footer();
			}

			if ($s_word <> $CHECK_ADMIN['sword'] OR empty($ADMIN_ID))
			{
				$tm->header();
				$tm->error($lang['add_admin'], null, $lang['bad_secret_word']);
				$tm->footer();
			}

			if ($db->numrows($db->query("SELECT admid FROM ".$basepref."_admin WHERE adlog = '".$db->escape($newlog)."'")) > 0)
			{
				$tm->header();
				$tm->error($lang['add_admin'], null, $lang['amanage_admin_ex']);
				$tm->footer();
			}

			$temp_perm = null;
			foreach ($perm_mod as $kat => $my)
			{
				if ($my == 'yes') {
					$temp_perm .= $kat."|";
				}
			}

			if ( ! empty($temp_perm))
			{
				if (substr($temp_perm, -1) == '|')
				{
					$temp_perm = substr($temp_perm, 0, -1);
				}
				$md_pass = md5(trim($newpass));
				$trim_mail = trim($newmail);
				if (in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
				{
					$db->query
						(
							"INSERT INTO ".$basepref."_admin VALUES (
							 NULL,
							 '".$db->escape($newlog)."',
							 '".$md_pass."',
							 '".$db->escape($trim_mail)."',
							 '0',
							 '".$db->escape($temp_perm)."',
							 '0'
							 )"
						);
				}
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();
				$tm->error($lang['add_admin'], null, $lang['forgot_access']);
				$tm->footer();
			}
		}

		/**
		 * Список администраторов
		 */
		if ($_REQUEST['dn'] == 'list')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['amanage'], $lang['list_admin']
				);

			$tm->header();

			$inq = $db->query("SELECT * FROM ".$basepref."_admin");

			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$lang['list_admin'].'</caption>
						<tr>
							<th>ID</th>
							<th>'.$lang['adm_login'].'</th>
							<th>'.$lang['e_mail'].'</th>
							<th>'.$lang['last_visit'].'</th>
							<th>'.$lang['all_set'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				$style = (intval($item['blocked']) > 0) ? 'no-active' : '';
				echo '	<tr class="list">
							<td class="al '.$style.'">'.$item['admid'].'</td>
							<td class="'.$style.'">'.$item['adlog'].'</td>
							<td class="'.$style.'"><a href="mailto:'.$item['admail'].'">'.$item['admail'].'</a></td>
							<td class="'.$style.'">'.(($item['adlast'] > 0) ? format_time($item['adlast'],1,1) : '&#8212;').'</td>
							<td class="gov '.$style.'">';
				if ($item['admid'] == $ADMIN_ID)
				{
					echo '		<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['personal_cabinet'].'</a>';
				}
				else
				{
					if (in_array($item['admid'],$CHECK_ADMIN['admid']))
					{
						echo	$lang['amanage_main_admin'];
					}
					else
					{
						echo '	<a href="index.php?dn=admedit&amp;id='.$item['admid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>';
						if (intval($item['blocked']) > 0) {
							echo '	<a class="inact" href="index.php?dn=blocked&amp;id='.$item['admid'].'&amp;act=yes&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" alt="'.$lang['ban_del'].'" /></a>';
						} else {
							echo '	<a href="index.php?dn=blocked&amp;id='.$item['admid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/act.png" alt="'.$lang['ban_add'].'" /></a>';
						}
						echo '	<a href="index.php?dn=admdel&amp;id='.$item['admid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
					}
				}
				echo '		</td>
						</tr>';
			}
			echo '	</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Блокировать / Разблокировать администратора
		 */
		if ($_REQUEST['dn'] == 'blocked')
		{
			global $id, $ok, $act;

			$id = preparse($id, THIS_INT);

			if ($id == $ADMIN_ID OR in_array($id, $CHECK_ADMIN['admid']))
			{
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}

			if ($act == 'yes')
			{
				if ($id > 0) {
					$db->query("UPDATE ".$basepref."_admin SET blocked = '0' WHERE admid = '".$id."'");
				}
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}

			if ($id > 0) {
				$db->query("UPDATE ".$basepref."_admin SET blocked = '1' WHERE admid = '".$id."'");
			}

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Удаление администратора
		 */
		if ($_REQUEST['dn'] == 'admdel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					$lang['all_system'],
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['amanage'].'</a>',
					$lang['all_delet']
				);

			$id = preparse($id, THIS_INT);

			if ($id == $ADMIN_ID)
			{
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}

			if ($ok == 'yes')
			{
				if (in_array($ADMIN_ID, $CHECK_ADMIN['admid']) AND ! in_array($id, $CHECK_ADMIN['admid']))
				{
					$db->query("DELETE FROM ".$basepref."_admin WHERE admid = '".$id."'");
				}

				// Разрушаем настройки панели для $id
				$in = array();
				$admins = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'apanel'"));
				$label = Json::decode($admins['setval']);

				if (is_array($label))
				{
					if (key_exists($id, $label))
					{
						foreach ($label as $k => $v)
						{
							if ($k == $id) {
								$del[$id] = array('skin' => $v['skin'], 'mout' => $v['mout'], 'icon' => $v['icon']);
							} else {
								$in[$k] = array('skin' => $v['skin'], 'mout' => $v['mout'], 'icon' => $v['icon']);
							}
						}
						unset($del);
						ksort($in);
						$ins = Json::encode($in);
						$db->query("UPDATE ".$basepref."_settings SET setval ='".$db->escape($ins)."' WHERE setname = 'apanelset'");
					}
				}

				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT adlog FROM ".$basepref."_admin WHERE admid = '".$id."'"));

				$yes = 'index.php?dn=admdel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['amanage'], $item['adlog'], $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Редактировать администратора
		 */
		if ($_REQUEST['dn']=='admedit')
		{
			global $id, $ok;

			$id = preparse($id, THIS_INT);
			if ($id == $ADMIN_ID) {
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}

			$tm->header();

			$mod = array();
			$inq = $db->query("SELECT * FROM ".$basepref."_mods");
			while ($c = $db->fetchassoc($inq))
			{
				$mod[$c['file']] = $c['parent'];
			}

			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_admin WHERE admid = '".$id."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_edit'].': '.$item['adlog'].'</caption>
						<tr>
							<td>'.$lang['adm_login'].'</td>
							<td><input name="newlog" size="25" maxlength="15" type="text" value="'.$item['adlog'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['e_mail'].'</td>
							<td><input name="newmail" size="25" maxlength="50" type="text" value="'.$item['admail'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['pass'].'</td>
							<td><input name="newpass" size="25" maxlength="15" type="text"></td>
						</tr>
						<tr>
							<th class="ar"><strong>'.$lang['all_access'].'</strong></th>
							<th class="site is">'.$lang['amanage_hint_access'].'</th>
						</tr>';
			echo '		<tr>
							<td></td>
							<td class="bold">'.$lang['opt_manage_mod'].'</td>
						</tr>';
			$act = explode("|", $item['permiss']);
			natsort($realmod);
			foreach($realmod as $val)
			{
				if ($mod[$val] == 0)
				{
					$style = ( ! in_array($val, $act)) ? ' class="noactive"' : '';
					echo '	<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td'.$style.'>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"'.((in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no"'.(( ! in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_no'].'
								</td>
							</tr>';
				}
			}
			echo '		<tr>
							<td></td>
							<td class="bold">'.$lang['all_system'].'</td>
						</tr>';
			foreach($LIST_MOD_ADM as $val)
			{
				$style = ( ! in_array($val, $act)) ? ' class="noactive"' : '';
				if ($val <> 'filebrowser')
				{
					echo '	<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td'.$style.'>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"'.((in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no"'.(( ! in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_no'].'
								</td>
							</tr>';
				}
				else
				{
					echo '	<tr>
								<td></td>
								<td class="bold">'.$lang['manage_files'].'</td>
							</tr>
							<tr>
								<td>'.(isset($lang[$val]) ? $lang[$val] : $val).'</td>
								<td'.$style.'>&nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="yes"'.((in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_yes'].' &nbsp; &nbsp;
									<input type="radio" name="perm_mod['.$val.']" value="no"'.(( ! in_array($val, $act)) ? ' checked' : '').'> '.$lang['all_no'].'
								</td>
							</tr>';
				}
			}
			echo '		<tr>
							<th class="ar"><strong>'.$lang['confirm'].'</strong></th>
							<th class="site is">'.$lang['confirm_hint'].'</th>
						</tr>
						<tr>
							<td></td>
							<td><input name="s_word" size="25" maxlength="50" type="password" required></td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="admeditsave">
								<input type="hidden" name="id" value="'.$id.'">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input accesskey="s" class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать администратора (сохранение)
		 */
		if ($_REQUEST['dn'] == 'admeditsave')
		{
			global $id, $lang, $newlog, $newmail, $newpass, $perm_mod, $s_word;

			$newlog = preparse_dn($newlog);
			$id = preparse($id, THIS_INT);

			if
			(
				empty($newlog) OR
				empty($newmail) OR
				mb_strlen($newlog) < 4 OR
				mb_strlen($newlog) > 15
			) {
				$tm->header();
				$tm->error($lang['all_edit'], $newlog, $lang['amange_er_login_or_pass']);
				$tm->footer();
			}

			if (verify_mail($newmail) == 0)
			{
				$tm->header();
				$tm->error($lang['all_edit'], $newlog, $lang['bad_mail']);
				$tm->footer();
			}

			if ($s_word <> $CHECK_ADMIN['sword'] OR empty($ADMIN_ID))
			{
				$tm->header();
				$tm->error($lang['all_edit'], $newlog, $lang['bad_secret_word']);
				$tm->footer();
			}

			if ($db->numrows($db->query("SELECT admid FROM ".$basepref."_admin WHERE adlog = '".$db->escape($newlog)."' AND admid <> '".$id."'")) > 0)
			{
				$tm->header();
				$tm->error($lang['all_edit'], $newlog, $lang['amanage_admin_ex']);
				$tm->footer();
			}

			$temp_perm = null;
			foreach ($perm_mod as $kat => $my)
			{
				if ($my == 'yes') {
					$temp_perm .= $kat."|";
				}
			}

			if ( ! empty($temp_perm))
			{
				if (substr($temp_perm, -1) == '|') {
					$temp_perm = substr($temp_perm, 0, -1);
				}
				$md_pass = md5(trim($newpass));
				$trim_mail = trim($newmail);
				if (in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
				{
					$db->query
						(
							"UPDATE ".$basepref."_admin SET
							 adlog   = '".$db->escape($newlog)."',
							 admail  = '".$db->escape($newmail)."',
							 permiss = '".$temp_perm."'
							 WHERE admid = '".$id."'"
						);
					if ( ! empty($newpass))
					{
						$md_pass = md5(trim($newpass));
						$db->query("UPDATE ".$basepref."_admin SET adpwd = '".$md_pass."' WHERE admid = '".$id."'");
					}
				}
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();
				$tm->error($lang['all_edit'], $newlog, $lang['forgot_access']);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['amanage'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
