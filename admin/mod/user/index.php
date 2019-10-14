<?php
/**
 * File:        /admin/mod/user/index.php
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
define('PERMISS', 'user');

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
		$legaltodo = array
			(
				'index', 'optsave', 'list', 'ban', 'unban', 'del', 'edit', 'editsave', 'field', 'fieldup',
				'fielddel', 'fieldadd', 'fieldsave', 'integ', 'integpresave', 'integsave',
				'register', 'registersave',
				'group', 'groupadd', 'groupup', 'groupdel'
			);

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		/**
		 * Доп. функции мода
		 */
		include('mod.function.php');

		/**
		 * Функция меню
		 */
		function this_menu()
		{
			global $conf, $tm, $lang, $sess;

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['options'].'</a>'
					.'<a'.cho('list').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['user_list'].'</a>'
					.'<a'.cho('register').' href="index.php?dn=register&amp;ops='.$sess['hash'].'">'.$lang['register_user'].'</a>'
					.'<a'.cho('field').' href="index.php?dn=field&amp;ops='.$sess['hash'].'">'.$lang['addit_fields'].'</a>'
					.'<a'.cho('fieldadd').' href="index.php?dn=fieldadd&amp;ops='.$sess['hash'].'">'.$lang['add_field'].'</a>';
			if ($conf['user']['groupact'] == 'yes') {
				$link.= '<a'.cho('group, groupdel').' href="index.php?dn=group&amp;ops='.$sess['hash'].'">'.$lang['all_groups'].'</a>';
			}
			$link.= '<a'.cho('integ, integpresave').' href="index.php?dn=integ&amp;ops='.$sess['hash'].'">'.$lang['opt_manager_forum'].'</a>';

			$filter = null;
			if (cho('list')) {
				$filter = '<a'.cho('list', 1).' href="#" onclick="$(\'#filter\').slideToggle();" title="'.$lang['search_in_section'].'">'.$lang['all_filter'].'</a>';
			}

			$tm->this_menu($link, $filter);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 ---------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['user'].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo	in_array($itemset['setname'], array('groupact', 'editmail')) ? '<tr><th colspan="2"></th></tr>' : '';
				echo '	<tr>
							<td class="first">'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'</td>
							<td>';
				echo			eval($itemset['setcode']);
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="optsave">
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
		 * Настройки (сохранение)
		 ---------------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($modname[PERMISS], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']], THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Список пользователей
		 ------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $act, $nu, $p, $filter, $fid, $atime;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['user_list']
				);

			$tm->header();

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}

			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu  = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$act = ( ! isset($act) OR $act == 1) ? 1 : 0;
			$p   = ( ! isset($p) OR $p <= 1) ? 1 : $p;
			$sf  = $nu * ($p - 1);
			$sql = $fu = '';
			$fid = preparse($fid,THIS_INT);
			$atime = preparse($atime,THIS_INT);

			if ($fid > 0)
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_mods_filter WHERE fid = '".$fid."'");
				if ($db->numrows($inq) > 0)
				{
					$item = $db->fetchrow($inq);
					$insert = unserialize($item['filter']);
					$sql.= (($conf['userbase'] == 'phpbb30') ? ' AND ' : ' WHERE ').implode(' AND ',$insert);
					$fu = '&amp;fid='.$item['fid'];
				}
			}
			else
			{
				if (isset($filter) AND is_array($filter))
				{
					$sw = array();
					foreach ($filter as $k => $v)
					{
						if (isset($userapi->userfilter[$k]))
						{
							$f = $userapi->userfilter[$k];
							if ($f[2] == 'input' AND ! empty($v)) {
								$v = str_replace(array('"', "'"), '', strip_tags($v));
								$sw[] = $f[0]." LIKE '%".$db->escape($v)."%'";
							}
							if ($f[2] == 'date' AND is_array($v))
							{
								if(isset($v[0]) AND ! empty($v[0])){
									$sw[] = $f[0]." > '".$db->escape(ReDate($v[0]))."'";
								}
								if(isset($v[1]) AND ! empty($v[1])){
									$sw[] = $f[0]." < '".$db->escape(ReDate($v[1]))."'";
								}
							}
						}
					}
					if (sizeof($sw) > 0)
					{
						$sql.= (($conf['userbase'] == 'phpbb30') ? ' AND ' : ' WHERE ').implode(' AND ', $sw);
						$insert = serialize($sw);
						$db->query("DELETE FROM ".$basepref."_mods_filter WHERE start < '".(NEWTIME - 360)."'");
						$db->increment('mods_filter');
						$db->query("INSERT INTO ".$basepref."_mods_filter VALUES (NULL, '".NEWTIME."', '".$db->escape($insert)."')");
						$nif = $db->insertid();
						if($nif > 0){
							$fu = '&amp;fid='.$nif;
						}
					}
				}
			}

			if ($atime != 0) {
				$sql.= " WHERE (regdate  >= '".$atime."')";
			}

			$p.= $fu;
			if (isset($userapi->userfilter) AND is_array($userapi->userfilter))
			{
				$tm->filter('index.php?dn=list&amp;ops='.$sess['hash'], $userapi->userfilter, $lang['user']);
			}

			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages('index.php?dn=list&amp;p='.$p.'&amp;ops='.$sess['hash'], $nu);
			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.(($atime == 0) ? $lang['user_list'] : $lang['user_new']).'</caption>
						<tr><td colspan="6">'.$amount.'</td></tr>
						<tr>
							<th class="work-no-sort al pw20">'.$lang['login'].'</th>';
			if ($conf['userbase'] == 'danneo') {
				echo '		<th class="work-no-sort">'.$lang['file_group'].'</th>';
			}
			echo '			<th class="work-no-sort">'.$lang['registr_date'].'</th>
							<th class="work-no-sort">'.$lang['last_visit'].'</th>
							<th class="work-no-sort">E-Mail</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
						</tr>';
			$userapi->userlist($sf, $nu, $p, $sess, $sql);

			$pages = null;
			if ($userapi->error)
			{
				$pages = $lang['all_pages'].':&nbsp; '.user_pages($userapi->data['table'].$sql, $userapi->data['userid'], 'index', 'list', $nu, $p, $sess);
			}
			echo '		<tr>
							<td class="bbno" colspan="6">'.$pages.'</td>
						</tr>
					</table>
					</div>';

			$tm->footer();
		}

		/**
		 * Регистрация пользователя
		 ----------------------------*/
		if ($_REQUEST['dn'] == 'register')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['register_user'].'</caption>
						<tr>
							<td>'.$lang['adm_login'].'</td>
							<td><input type="text" name="name" size="40" required></td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td><input type="email" name="mail" size="40" required></td>
						<tr>
						<tr>
							<td>'.$lang['pass'].'</td>
							<td><input type="password" name="pass" size="40" required></td>
						<tr>
						<tr>
							<td>'.$lang['file_group'].'</td>
							<td>
								<select name="group" style="width: 273px;">';
			echo '					<option value="0" selected>'.$lang['user'].'</option>';
			if ($conf['user']['groupact'] == 'yes')
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_user_group");
				while ($item = $db->fetchassoc($inq)) {
					echo '			<option value="'.$item['gid'].'">'.$item['title'].'</option>';
				}
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['operation'].'</td>
							<td>
								<select name="action" style="width: 273px;">
									<option value="1">'.$lang['just_add'].'</option>
									<option value="2">'.$lang['send_password_user'].'</option>
									<option value="3" selected>'.$lang['request_activate_user'].'</option>
								</select>
							</td>
						<tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="registersave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</div>
					</form>';

			$tm->footer();
		}

		/**
		 * Регистрация пользователя (сохранение)
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'registersave')
		{
			global $db, $basepref, $name, $mail, $pass, $group, $action, $conf, $ro, $sess;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_add']
				);

			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			if ($db->numrows($db->query("SELECT uname FROM ".$basepref."_user WHERE uname = '".$db->escape($name)."'")) > 0)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['register_user'], $lang['bad_login_user']);
				$tm->footer();
			}

			if ( ! verify_mail($mail))
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['register_user'], $lang['bad_mail']);
				$tm->footer();
			}

			if ($db->numrows($db->query("SELECT umail FROM ".$basepref."_user WHERE umail = '".$db->escape($mail)."'")) > 0)
			{
				$tm->header();
				$tm->error($modname[PERMISS], $lang['register_user'], $lang['bad_mail_user']);
				$tm->footer();
			}

			if ( ! verify_pwd($pass))
			{
				$bad_pass = this_text(array("minpass" => $conf['user']['minpass'], "maxpass" => $conf['user']['maxpass']), $lang['pass_hint']);
				$tm->header();
				$tm->error($modname[PERMISS], $lang['register_user'], $bad_pass);
				$tm->footer();
			}

			$reg_name = preparse($name, THIS_TRIM);
			$reg_mail = preparse($mail, THIS_TRIM);
			$reg_pass = md5(trim($pass));
			$activate = verify_code();

			$inq = $db->query
			(
				"INSERT INTO ".$basepref."_user VALUES (
				 NULL,
				 '".intval($group)."',
				 '".$db->escape($reg_name)."',
				 '".$reg_pass."',
				 '".$db->escape($reg_mail)."',
				 '".NEWTIME."',
				 '0',
				 '',
				 '',
				 '',
				 '',
				 '',
				 '".$activate."',
				 '0',
				 '0',
				 '',
				 '',
				 '0',
				 '0'
				 )"
			);
			$userid = $db->insertid();

			if ($action == 1)
			{
				$db->query("UPDATE ".$basepref."_user SET lastvisit = '".NEWTIME."', activate = '', active = '1' WHERE userid = '".$userid."'");
			}
			else if ($action == 2)
			{
				$db->query("UPDATE ".$basepref."_user SET lastvisit = '".NEWTIME."', activate = '', active = '1' WHERE userid = '".$userid."'");

				$profile = $conf['site_url'].$ro->seo('index.php?dn=user')."\n\n";
				$subject = $lang['admin_nu_subject']." - ".$conf['site'];
				$message = this_text(array
					(
						"br"       => "\r\n",
						"ulogin"   => $name,
						"upass"    => $pass,
						"link"    => $profile,
						"site"     => $conf['site'],
						"site_url" => SITE_URL
					),
					$lang['registr_ok_msg']);

				send_mail($reg_mail, $subject, $message, $conf['site']." <".$conf['site_mail'].">");
			}
			else if ($action == 3)
			{
				$time_limit = NEWTIME + 604800;
				$time_remove = date("d.m.Y", $time_limit);
				$act_url = $conf['site_url'].$ro->seo("index.php?dn=user&re=register&to=act&id=".$userid."&code=".$activate)."\n\n";
				$subject = $lang['admin_nu_subject']." - ".$conf['site'];
				$message = this_text(array
					(
						"br"       => "\r\n",
						"rtime"    => $time_remove,
						"ulogin"   => $name,
						"upass"    => $pass,
						"umail"    => $reg_mail,
						"alink"    => $act_url,
						"site"     => $conf['site'],
						"site_url" => SITE_URL
					),
					$lang['registr_act_msgtext']);

				send_mail($reg_mail, $subject, $message, $conf['site']." <".$conf['site_mail'].">");
			}

			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Заблокировать пользователя
		 ------------------------------*/
		if ($_REQUEST['dn'] == 'ban')
		{
			global $fid, $uid, $act, $nu, $p;

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			$uid = preparse($uid, THIS_INT);
			$fid = preparse($fid, THIS_INT);
			$userapi->banadd($uid);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($act)) ? '&amp;act='.preparse($act, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Снять блокировку
		 ---------------------*/
		if ($_REQUEST['dn'] == 'unban')
		{
			global $fid, $uid, $act, $nu, $p;

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			$uid = preparse($uid, THIS_INT);
			$fid = preparse($fid, THIS_INT);
			$userapi->bandel($uid);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($act)) ? '&amp;act='.preparse($act, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Удалить пользователя
		 -------------------------*/
		if ($_REQUEST['dn']=='del')
		{
			global $fid, $ok, $uid, $act, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_delet']
				);

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db,false);

			$uid = preparse($uid, THIS_INT);
			$fid = preparse($fid, THIS_INT);

			if ($ok == 'yes')
			{
				$userapi->userdel($uid);

				$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
				$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
				$redir.= ( ! empty($act)) ? '&amp;act='.preparse($act, THIS_INT) : '';
				$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
				$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

				redirect($redir);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_user WHERE userid = '".$uid."'"));

				$yes = 'index.php?dn=del&amp;uid='.$uid.'&amp;act='.$act.'&amp;nu='.$nu.'&amp;p='.$p.'&amp;ok=yes&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'].(($fid > 0) ? '&amp;fid='.$fid : '');

				$tm->header();
				$tm->shortdel($modname[PERMISS], $lang['del_user'], $yes, $not, $item['uname']);
				$tm->footer();
			}
		}

		/**
		 * Редактировать пользователя
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $conf, $fid, $uid, $act, $nu, $p;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['all_edit']
				);

			$tm->header();

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			$uid = preparse($uid, THIS_INT);
			$fid = preparse($fid, THIS_INT);

			echo '<form action="index.php" method="post">';

			$userapi->useredit($uid);
			$inqure = $db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes' ORDER BY posit");

			if ($db->numrows($inqure) > 0)
			{
				echo '	<tr>
							<th></th><th class="site" colspan="2">'.$lang['addit_fields'].'</th>
						</tr>';
				$ui = $db->fetchrow($db->query("SELECT userfield FROM ".$basepref."_user WHERE userid = '".$uid."'"));
				$user = ( ! empty($ui['userfield'])) ? Json::decode($ui['userfield']) : '';
				while ($item = $db->fetchrow($inqure))
				{
					$name = "fields[".$item['fieldname']."]";
					if ($item['fieldtype'] != 'apart')
					{
						echo '	<tr>
									<td>'.$item['name'].'</td>
									<td>';
						$value = ( ! empty($user[$item['fieldid']])) ? $user[$item['fieldid']] : '';
						if ($item['fieldtype'] == 'text') {
							echo '		<input type="text" name="'.$name.'" size="40" value="'.$value.'">';
						}
						if ($item['fieldtype'] == 'textarea') {
							$tm->textarea($name, 5, 70, $value, 1);
						}
						if ($item['fieldtype'] == 'radio') {
							$list = Json::decode($item['fieldlist']);
							foreach ($list as $k => $v) {
								echo '	<input type="radio" name="'.$name.'" value="'.$k.'"'.(($k == $value) ? ' checked' : '').'> &nbsp; '.$v.'<br />';
							}
						}
						if ($item['fieldtype'] == 'select')
						{
							$list = Json::decode($item['fieldlist']);
							echo '		<select name="'.$name.'">';
							foreach ($list as $k => $v) {
								echo '		<option value="'.$k .'"'.(($k == $value) ? ' selected' : '').'>'.$v.'</option>';
							}
							echo '		</select>';
						}
						if ($item['fieldtype'] == 'date')
						{
							$value = empty($value) ? array('d' => 1, 'm' => 1, 'y' => 1971) : $value;
							$sel = '	<select name="'.$name.'[day]">';
							for ($i = 1; $i < 32; $i ++) {
								$sel.= '	<option value="'.$i.'"'.(($value['d'] == $i) ? ' selected' : '').'>'.$i.'</option>';
							}
							$sel.=   '	</select>&nbsp;'
									.'	<select name="'.$name.'[month]">';
							for ($i = 1; $i < 13; $i ++) {
								$sel.= '	<option value="'.$i.'"'.(($value['m'] == $i) ? ' selected' : '').'>'.$i.'</option>';
							}
							$sel.=   '	</select>&nbsp;'
									.'	<select name="'.$name.'[year]">';
							for($i = 1928; $i < (NEWYEAR + 1); $i ++)
							{
								if (empty($value) AND $i == 1971)
									$sel.= '	<option value="'.$i.'" selected>'.$i.'</option>';
								elseif ($i == $value['y'])
									$sel.= '	<option value="'.$i.'" selected>'.$i.'</option>';
								else
									$sel.= '	<option value="'.$i.'">'.$i.'</option>';
							}
							$sel.= '	</select>';
							echo $sel;
						}
						echo '		</td>
								</tr>';
					}
				}
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="uid" value="'.$uid.'">
								<input type="hidden" name="p" value="'.$p.'">
								<input type="hidden" name="nu" value="'.$nu.'">
								<input type="hidden" name="act" value="'.$act.'">';
			if ($fid > 0) {
				echo '			<input type="hidden" name="fid" value="'.$fid.'">';
			}
			echo '				<input type="hidden" name="dn" value="editsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</div>
					</form>';

			$tm->footer();
		}

		/**
		 * Редактировать пользователя (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'editsave')
		{
			global $edit, $fid, $uid, $fields, $country, $region, $act, $nu, $p;

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			$uid = preparse($uid, THIS_INT);
			$fid = preparse($fid, THIS_INT);
			$userapi->usersave($uid, $edit);

			$inqure = $db->query("SELECT fieldid, fieldtype, fieldname, name, requires, method, minlen, maxlen FROM ".$basepref."_user_field WHERE act = 'yes'");

			$checkfield = $newfield = array();

			if ($db->numrows($inqure) > 0)
			{
				while ($item = $db->fetchrow($inqure)) {
					$checkfield[$item['fieldid']] = $item;
				}
				foreach ($checkfield as $k => $v)
				{
					if (isset($fields[$v['fieldname']]))
					{
						if ($v['fieldtype'] == 'text')
						{
							if ($v['method'] == 'text') {
								$newfield[$v['fieldid']] = ($v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\p{L}\p{Nd}\-\s\.(),!?]+$/ui',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							}
							if ($v['method'] == 'email') {
								$newfield[$v['fieldid']] = ($v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							}
							if ($v['method'] == 'number') {
								$newfield[$v['fieldid']] = ($v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\d]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							}
							if ($v['method'] == 'phone') {
								$newfield[$v['fieldid']] = ($v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[0-9\s\.\+()-]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							}
						}
						if ($v['fieldtype'] == 'textarea') {
							$newfield[$v['fieldid']] = ($v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires']=='yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen']) ? '' : $fields[$v['fieldname']];
						}
						if ($v['fieldtype'] == 'select' OR $v['fieldtype'] == 'radio') {
							$newfield[$v['fieldid']] = $fields[$v['fieldname']];
						}
						if ($v['fieldtype'] == 'date') {
							$date = array();
							$date['d'] = (preparse($fields[$v['fieldname']]['day'], THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['day'], THIS_INT) > 31) ? 1 : preparse($fields[$v['fieldname']]['day'], THIS_INT);
							$date['m'] = (preparse($fields[$v['fieldname']]['month'], THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['month'], THIS_INT) > 12) ? 1 : preparse($fields[$v['fieldname']]['month'], THIS_INT);
							$date['y'] = (preparse($fields[$v['fieldname']]['year'], THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['year'], THIS_INT) > NEWYEAR) ? NEWYEAR : preparse($fields[$v['fieldname']]['year'], THIS_INT);
							$newfield[$v['fieldid']] = $date;
						}
					}
					else
					{
						$newfield[$v['fieldid']] = '';
					}
				}
			}
			$insert = (sizeof($newfield) > 0) ? Json::encode($newfield) : '';
			$db->query
				(
					"UPDATE ".$basepref."_user SET
					 userfield = '".$db->escape($insert)."',
					 countryid = '".$db->escape($country)."',
					 regionid  = '".$db->escape($region)."'
					 WHERE userid = '".$uid."'"
				);

			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($act)) ? '&amp;act='.preparse($act, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			$redir.= ($fid > 0) ? '&amp;fid='.$fid : '';

			redirect($redir);
		}

		/**
		 * Дополнительные поля
		 -------------------------*/
		if ($_REQUEST['dn'] == 'field')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					$lang['addit_fields']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['addit_fields'].'</caption>
						<tr>
							<th class="ar work-no-sort">'.$lang['all_decs'].'</th>
							<th class="work-no-sort">'.$lang['form_name_field'].'</th>
							<th class="work-no-sort">'.$lang['type_field'].'</th>
							<th class="work-no-sort">'.$lang['all_posit'].'</th>
							<th class="work-no-sort">'.$lang['all_not_empty'].'</th>
							<th class="work-no-sort">'.$lang['profile'].'</th>
							<th class="work-no-sort">'.$lang['registr'].'</th>
							<th class="work-no-sort">'.$lang['sys_included'].'</th>
							<th class="work-no-sort">'.$lang['sys_manage'].'</th>
						</tr>';
			$inqure = $db->query("SELECT * FROM ".$basepref."_user_field ORDER BY posit");
			while ($item = $db->fetchrow($inqure))
			{
				$title = (isset($feild[$item['fieldtype']])) ? $feild[$item['fieldtype']] : '';
				$style = ($item['act']=='no') ? 'noactive' : '';
				$stylework = ($item['act']=='no') ? 'noactive' : '';
				echo '	<tr class="list">
							<td class="'.$style.' pw15 site">'.$title.'</td>
							<td class="'.$style.' pw10"><span class="alternative">'.$item['fieldname'].'</span></td>
							<td class="'.$style.'">
								<input type="text" name="form['.$item['fieldid'].'][name]" size="40" maxlength="255" value="'.$item['name'].'">';
				if ($item['requires'] == 'yes')
				{
					$tm->outhint($lang['minlen_symbol'].':&nbsp; '.$item['minlen'].'<br> '.$lang['maxlen_symbol'].':&nbsp; '.$item['maxlen']);
				}
				echo '		</td>
							<td class="'.$style.' ac check">
								<input type="text" name="form['.$item['fieldid'].'][posit]" size="2" maxlength="3" value="'.$item['posit'].'">
							</td>
							<td class="'.$style.' ac check">';
				if ($item['fieldtype'] == 'text' OR $item['fieldtype'] == 'textarea') {
					echo '		<input name="form['.$item['fieldid'].'][requires]" value="yes" type="checkbox"'.(($item['requires']=='yes') ? ' checked="checked"' : '').'>';
				}
				echo '		</td>
							<td class="'.$style.' check">
								<input name="form['.$item['fieldid'].'][profile]" value="yes" type="checkbox"'.(($item['profile'] == 'yes') ? ' checked="checked"' : '').'>
							</td>
							<td class="'.$style.' check">
								<input name="form['.$item['fieldid'].'][registr]" value="yes" type="checkbox"'.(($item['registr'] == 'yes') ? ' checked="checked"' : '').'>
							</td>
							<td class="'.$style.' check">
								<input name="form['.$item['fieldid'].'][act]" value="yes" type="checkbox"'.(($item['act']=='yes') ? ' checked="checked"' : '').'>
							</td>
							<td class="'.$style.' gov pw5">
								<a href="index.php?dn=fielddel&amp;fieldid='.$item['fieldid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="9">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="fieldup">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Дополнительные поля (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'fieldup')
		{
			global $form;

			if (preparse($form, THIS_ARRAY) == 1)
			{
				foreach ($form as $k => $v)
				{
					$name = preparse($v['name'], THIS_TRIM, 1, 255);
					$requires = (isset($v['requires']) AND $v['requires'] == 'yes') ? 'yes' : 'no';
					$profile = (isset($v['profile']) AND $v['profile'] == 'yes') ? 'yes' : 'no';
					$registr = (isset($v['registr']) AND $v['registr'] == 'yes') ? 'yes' : 'no';
					$act = (isset($v['act']) AND $v['act'] == 'yes') ? 'yes' : 'no';
					$posit = preparse($v['posit'], THIS_INT);
					$fieldid = preparse($k, THIS_INT);

					if ($fieldid > 0 AND $name)
					{
						$db->query
							(
								"UPDATE ".$basepref."_user_field SET
								 name     = '".$db->escape($name)."',
								 requires = '".$requires."',
								 posit    = '".$posit."',
								 act      = '".$act."',
								 profile  = '".$profile."',
								 registr  = '".$registr."'
								 WHERE fieldid = '".$fieldid."'"
							);
					}
				}
			}

			redirect('index.php?dn=field&ops='.$sess['hash']);
		}

		/**
		 * Удаление дополнительного поля
		 ----------------------------------*/
		if ($_REQUEST['dn'] == 'fielddel')
		{
			global $ok, $fieldid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=field&amp;ops='.$sess['hash'].'">'.$lang['addit_fields'].'</a>',
					$lang['all_delet']
				);

			$fieldid = preparse($fieldid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_user_field WHERE fieldid = '".$fieldid."'");
				redirect('index.php?dn=field&ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT name FROM ".$basepref."_user_field WHERE fieldid = '".$fieldid."'"));

				$yes = 'index.php?dn=fielddel&amp;fieldid='.$fieldid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=field&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['addit_fields'], $lang['del_item'], $yes, $not, $item['name']);
				$tm->footer();
			}
		}

		/**
		 * Добавление дополнительного поля
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'fieldadd')
		{
			global $feild, $method, $type, $step;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=field&amp;ops='.$sess['hash'].'">'.$lang['addit_fields'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			$step = preparse($step, THIS_INT);
			$title = (isset($feild[$type])) ? ' : '.$feild[$type] : '';
			$list = (isset($feild[$type])) ? $feild[$type] : '';

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_field'].$title.'</caption>';
			if ($step == 2 AND isset($type) AND isset($feild[$type]))
			{
				echo '	<tr>
							<td class="first"><span>*</span> '.$lang['form_name_field'].'</td>
							<td><input type="text" name="fieldname" size="70" maxlength="10" value="">';
								$tm->outhint($lang['filed_name_hint']);
				echo '		</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="name" size="70" maxlength="255" value=""></td>
						</tr>';
				if ($type == 'text')
				{
					echo '	<tr>
								<td>'.$lang['all_not_empty'].'</td>
								<td><input name="requires" value="yes" type="checkbox"></td>
							</tr>
							<tr>
								<td>'.$lang['type_check'].'</td>
								<td>
									<select name="check">';
					foreach ($method as $k=>$v) {
						echo '			<option value="'.$k.'">'.$v.'</option>';
					}
					echo '			</select>
									<input type="hidden" name="dn" value="fieldsave">
									<input type="hidden" name="type" value="text">
								</td>
							</tr>
							<tr>
								<td>'.$lang['minlen_symbol'].'</td>
								<td><input type="text" name="minlen" size="3" maxlength="1" value="3"></td>
							</tr>
							<tr>
								<td>'.$lang['maxlen_symbol'].'</td>
								<td><input type="text" name="maxlen" size="3" maxlength="3" value="255"></td>
							</tr>';
				}
				if ($type == 'textarea')
				{
					echo '	<tr>
								<td>'.$lang['all_not_empty'].'</td>
								<td><input name="requires" value="yes" type="checkbox"></td>
							</tr>
							<tr>
								<td>'.$lang['minlen_symbol'].'</td>
								<td><input type="text" name="minlen" size="3" maxlength="1" value="5"></td>
							</tr>
							<tr>
								<td>'.$lang['maxlen_symbol'].'</td>
								<td>
									<input type="text" name="maxlen" size="3" maxlength="3" value="455">
									<input type="hidden" name="dn" value="fieldsave">
									<input type="hidden" name="type" value="textarea">
								</td>
							</tr>';
				}
				if ($type == 'radio')
				{
					echo '	<tr>
								<td class="first"><span>*</span> '.$list.'</td>
								<td>
									<table class="work">
										<tr>
											<td>'.$lang['all_element'];
												$tm->outhint($lang['all_new_string']);
					echo '					</td>
											<td>'.$lang['all_value'];
												$tm->outhint($lang['all_new_string']);
					echo '					</td>
										</tr>
										<tr>
											<td>';
												$tm->textarea('listname', 5, 45, '', 1);
					echo '					</td>
											<td>';
												$tm->textarea('listvalue', 5, 45, '', 1);
					echo '						<input type="hidden" name="dn" value="fieldsave">
												<input type="hidden" name="type" value="radio">
											</td>
										</tr>
									</table>
								</td>
							</tr>';
				}
				if ($type == 'select')
				{
					echo '	<tr>
								<td class="first"><span>*</span> '.$list.'</td>
								<td>
									<table class="work">
										<tr>
											<td>'.$lang['all_element'];
												$tm->outhint($lang['all_new_string']);
					echo '					</td>
											<td>'.$lang['all_value'];
												$tm->outhint($lang['all_new_string']);
					echo '					</td>
										</tr>
										<tr>
											<td>';
												$tm->textarea('listname', 5, 45, '', 1);
					echo '					</td>
											<td>';
												$tm->textarea('listvalue', 5, 45, '', 1);
					echo '						<input type="hidden" name="dn" value="fieldsave">
												<input type="hidden" name="type" value="select">
											</td>
										</tr>
									</table>
								</td>
							</tr>';
				}
				if ($type == 'date')
				{
					echo '	<input type="hidden" name="dn" value="fieldsave">
							<input type="hidden" name="type" value="date">';
				}
				if ($type == 'apart')
				{
					echo '	<input type="hidden" name="dn" value="fieldsave">
							<input type="hidden" name="type" value="apart">';
				}
			}
			else
			{
				echo '		<tr>
								<th class="ar"><strong>'.$lang['type_field'].'</strong></th>
								<th>
									<select name="type">';
				foreach ($feild as $k => $v) {
					echo '				<option value="'.$k.'">'.$v.'</option>';
				}
				echo '				</select>
									<input type="hidden" name="dn" value="fieldadd">
									<input type="hidden" name="step" value="2">
								</th>
							</tr>
							<tr>
								<td class="vt">
									'.$lang['examples'].'
								</td>
								<td class="without">
									<table class="work">';
				foreach ($feild as $k => $v)
				{
					echo '				<tr>
											<td class="al odd">';
					echo '						<a class="infos-link" onclick="javascript:$(\'#field'.$k.'\').toggle();" href="javascript:void(0);">'.$v.'</a>
												<div id="field'.$k.'" style="display:none;">'.((isset($feildhelp[$k])) ? $feildhelp[$k] : '').'</div>';
					echo '					</td>
										</tr>';
				}
				echo '				</table>
								</td>
							</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_add'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавление дополнительного поля (сохранение)
		 ------------------------------------------------*/
		if ($_REQUEST['dn'] == 'fieldsave')
		{
			global $feild, $type, $listname, $listvalue, $name, $fieldname, $method, $check, $minlen, $maxlen, $requires;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=field&amp;ops='.$sess['hash'].'">'.$lang['addit_fields'].'</a>',
					$lang['all_add']
				);

			if (isset($type) AND isset($feild[$type]))
			{
				$fieldlist = '';
				$name = preparse($name, THIS_TRIM, 1, 255);
				$fieldname = (preparse($fieldname, THIS_SYMNUM) == 1) ? '' : preparse($fieldname, THIS_TRIM, 1, 10);
				$requires = ($requires=='yes') ? 'yes' : 'no';
				$minlen = preparse($minlen, THIS_INT);
				$maxlen = preparse($maxlen, THIS_INT);
				$n = mb_strlen($name);
				$f = mb_strlen($fieldname);

				if ($type=='select' OR $type=='radio')
				{
					if (preparse($listname, THIS_EMPTY) == 1 OR preparse($listvalue, THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($lang['addit_fields'], $lang['all_add'], $lang['forgot_name']);
						$tm->footer();
					}
					$ln = $lw = explode("\r\n", $listname);
					$lv = explode("\r\n", $listvalue);
					$fl = array();
					if ($fieldname)
					{
						foreach ($lw as $k => $v)
						{
							if (
								isset($ln[$k]) AND
								isset($lv[$k]) AND
								preparse($ln[$k], THIS_EMPTY) == 0 AND
								preparse($lv[$k], THIS_EMPTY) == 0 AND
								(preparse($lv[$k], THIS_SYMNUM) == 0)
							) {
								$fl[$ln[$k]] = $lv[$k];
							}
						}
					}
					if (preparse($fl, THIS_EMPTY) == 1 OR count($fl) <= 1)
					{
						$tm->header();
						$tm->error($lang['addit_fields'], $lang['all_add'], $lang['forgot_name']);
						$tm->footer();
					}
					$fieldlist = Json::encode($fl);
				}

				if ($n > 0 AND $f > 0 AND $type != 'text' OR $type == 'text' AND isset($method[$check]) AND $n > 0 AND $f > 0)
				{
					$check = ($type == 'text' AND isset($method[$check])) ? $check : '';
					$maxlen = ($maxlen < 10) ? 10 : $maxlen;
					$minlen = ($minlen > 5) ? 5 : $minlen;
					$maxlen = ($type=='text' AND  $maxlen > 255) ? 255 : $maxlen;
					$inqure = $db->query("SELECT fieldid FROM ".$basepref."_user_field WHERE fieldname = '".$db->escape($fieldname)."'");

					if ($db->numrows($inqure) == 0)
					{
						$db->query
							(
								"INSERT INTO ".$basepref."_user_field VALUES (
								 NULL,
								 '".$db->escape($type)."',
								 '".$db->escape($fieldname)."',
								 '".$db->escape($fieldlist)."',
								 '".$db->escape($name)."',
								 '".$db->escape($requires)."',
								 '".$db->escape($check)."',
								 '".$db->escape($minlen)."',
								 '".$db->escape($maxlen)."',
								 'yes',
								 '0',
								 'no',
								 'no'
								)"
							);
					}
				}
				else
				{
					$tm->header();
					$tm->error($lang['addit_fields'], $lang['all_add'], $lang['forgot_name']);
					$tm->footer();
				}
			}
			redirect('index.php?dn=field&amp;ops='.$sess['hash']);
		}

		/**
		 * Интеграция с форумами
		 */
		if ($_REQUEST['dn'] == 'integ')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=integ&amp;ops='.$sess['hash'].'">'.$lang['opt_manager_forum'].'</a>'
				);

			$tm->header();

			$user_base = opendir(WORKDIR.'/core/userbase/');
			$listing = array();
			while ($name = readdir($user_base)) {
				if ($name != '.' AND $name != '..' AND $name != 'danneo' AND is_dir(WORKDIR.'/core/userbase/'.$name)) {
					$listing[]= $name;
				}
			}
			closedir($user_base);
			sort($listing);

			if ($conf['userbase'] != 'danneo')
			{
				require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
				$userapi = new userapi($db, false);
			}

			$data = Json::decode($conf['datainteg']);

			echo '	<div class="section">
					<form action="index.php" method="post" name="integ">
					<table class="work">
						<caption>'.$lang['opt_manager_forum'].'</caption>
						<tr>
							<td><strong>'.$lang['file_integ'].'</strong></td>
							<td>
								<select name="userbase">
									<option value=""> — — — </option>';
			for ($i = 0; $i < sizeof($listing); $i ++)
			{
				if ($listing[$i])
				{
					echo '			<option value="'.$listing[$i].'"'.(($listing[$i] == $conf['userbase']) ? ' selected' : '').'>'.$listing[$i].'</option>';
				}
			}
			echo '				</select>
							</td>
						</tr>';
			if (($conf['userbase'] != 'danneo') AND isset($userapi->set) AND is_array($userapi->set))
			{
				foreach ($userapi->set as $k => $v)
				{
					$val = (isset($data[$k]) AND ! empty($data[$k])) ? $data[$k] : $userapi->data[$k];
					echo '	<tr>
								<td>'.(isset($lang[$v]) ? $lang[$v] : $v).'</td>
								<td>'.$val.'</td>
							</tr>';
				}
				$button = $lang['all_change'];
			}
			else
			{
				$button = $lang['all_submint'];
			}
			echo '		<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="integpresave">
								<input class="main-button" value="'.$button.'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактирование интеграции
		 */
		if ($_REQUEST['dn'] == 'integpresave')
		{
			global $userbase;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=integ&amp;ops='.$sess['hash'].'">'.$lang['opt_manager_forum'].'</a>',
					$lang['all_set']
				);

			if (empty($userbase))
			{
				$db->query("UPDATE ".$basepref."_settings SET setval = 'danneo' WHERE setname = 'userbase'");
				$db->query("UPDATE ".$basepref."_settings SET setval = '' WHERE setname = 'datainteg'");
				$cache->cachesave(1);
				redirect('index.php?dn=integ&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();

				require_once(WORKDIR.'/core/userbase/'.$userbase.'/danneo.user.php');
				$userapi = new userapi($db, false);

				$data = Json::decode($conf['datainteg']);

				echo '	<div class="section">
						<form action="index.php" method="post" name="integ">
						<table class="work">
							<caption>'.$lang['opt_manager_forum'].': '.$lang['all_set'].'</caption>
							<tr>
								<td><strong>'.$lang['file_integ'].'</strong></td>
								<td><strong>'.$userbase.'</strong></td>
							</tr>';
				if (isset($userapi->set) AND is_array($userapi->set))
				{
					foreach ($userapi->set as $k => $v)
					{
						$val = (isset($data[$k]) AND ! empty($data[$k])) ? $data[$k] : $userapi->data[$k];
						echo '	<tr>
									<td class="first"><span>*</span> '.(isset($lang[$v]) ? $lang[$v] : $v).'</td>
									<td>
										<input type="text" name="integ_set['.$k.']" size="50" value="'.$val.'" required="required">
									</td>
								</tr>';
					}
				}
				echo '		<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input type="hidden" name="userbase" value="'.$userbase.'">
									<input type="hidden" name="dn" value="integsave">
									<input class="main-button" value="'.$lang['all_save'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
		}

		/**
		 * Сохранение интеграции
		 */
		if ($_REQUEST['dn']=='integsave')
		{
			global $userbase, $integ_set;

			if (isset($integ_set) AND is_array($integ_set))
			{
				$data = '';
				require_once(WORKDIR.'/core/userbase/'.$userbase.'/danneo.user.php');
				$userapi = new userapi($db, false);
				if (isset($userapi->set) AND is_array($userapi->set))
				{
					foreach ($userapi->set as $k => $v) {
						$data[$k] = (isset($integ_set[$k]) AND ! empty($integ_set[$k])) ? $integ_set[$k] : $userapi->data[$k];
					}
				}
				$data = (is_array($data)) ? Json::encode($data) : '';
				$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($data)."' WHERE setname = 'datainteg'");
			}
			$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($userbase)."' WHERE setname = 'userbase'");

			// Если интеграция включена, отключаем дополнительные группы на сайте
            if (isset($userbase) AND ! empty($userbase)) {
				$db->query("UPDATE ".$basepref."_settings SET setval = 'no' WHERE setname = 'groupact'");
			}

			$cache->cachesave(1);
			redirect('index.php?dn=integ&amp;ops='.$sess['hash']);
		}

		/**
		 * Группы пользователей
		 -----------------------*/
		if ($_REQUEST['dn'] == 'group')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=group&amp;ops='.$sess['hash'].'">'.$lang['all_groups'].'</a>'
				);

			$tm->header();

			$inq = $db->query("SELECT * FROM ".$basepref."_user_group");
			if ($conf['userbase'] != 'danneo') {
				$g = $userapi->group();
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['user'].': '.$lang['all_groups'].'</caption>
						<tr>
							<th class="al">'.$lang['all_name'].'</th>
							<th>'.$lang['all_alias'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="al">
								<input name="title['.$item['gid'].']" value="'.$item['title'].'" size="15" type="text" style="width:96%;">
							</td>
							<td>';
				if ($conf['userbase'] == 'danneo') {
					echo '		&#8212; <input type="hidden" name="fid['.$item['gid'].']" value="0">';
				} else {
					if (is_array($g) AND sizeof($g) > 0)
					{
						echo '	<select name="fid['.$item['gid'].']">
									<option value="0"> &#8212; </option>';
						foreach ($g as $k => $v) {
							echo '	<option value="'.$k.'"'.(($item['fid'] == $k) ? ' selected' : '').'>'.$conf['userbase'].' : '.$v.'</option>';
						}
						echo '	</select>';
					} else {
						echo '	&#8212; <input type="hidden" name="fid['.$item['gid'].']" value="0">';
					}
				}
				echo '		</td>
							<td class="gov">';
				if ($item['gid'] == 1)
				{
					echo '		<img alt="'.$lang['def_value'].'" src="'.ADMPATH.'/template/images/totalinfo.gif" style="padding:1px;">';
				}
				else
				{
					echo '		<a href="index.php?dn=groupdel&amp;gid='.$item['gid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>';
				}
				echo '		</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="3">
								<input type="hidden" name="dn" value="groupup">
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
						<caption>'.$lang['all_submint'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input type="text" name="title" size="50" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['all_alias'].'</td>
							<td>';
			if ($conf['userbase'] == 'danneo') {
				echo '			&#8212; <input type="hidden" name="gid" value="0">';
			} else {
				if (is_array($g) AND sizeof($g) > 0) {
					echo '		<select name="fid">
                                  <option value="0"> &#8212; </option>';
					foreach ($g as $k => $v) {
						echo '		<option value="'.$k.'">'.$conf['userbase'].' : '.$v.'</option>';
					}
					echo '		</select>';
				} else {
					echo '		&#8212; <input type="hidden" name="gid" value="0">';
				}
			}
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="groupadd">
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
		 * Группы пользователей (сохранение)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'groupup')
		{
			global $title, $fid;

			foreach ($title as $id => $name)
			{
				if ($name)
				{
					$id = preparse($id, THIS_INT);
					$f = (isset($fid[$id]) AND intval($fid[$id]) > 0) ? intval($fid[$id]) : 0;
					$db->query("UPDATE ".$basepref."_user_group SET fid='".$db->escape($f)."', title = '".$db->escape($name)."' WHERE gid = '".$id."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=group&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить группу
		 ---------------------*/
		if ($_REQUEST['dn'] == 'groupadd')
		{
			global $title, $fid;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=group&amp;ops='.$sess['hash'].'">'.$lang['all_groups'].'</a>',
					$lang['all_add']
				);

			$fid = preparse($fid, THIS_INT);

			if (empty($title))
			{
				$tm->header();
				$tm->error($lang['all_groups'], $lang['all_add'], $lang['forgot_name']);
				$tm->footer();
			}

			require_once(WORKDIR.'/core/userbase/'.$conf['userbase'].'/danneo.user.php');
			$userapi = new userapi($db, false);

			$title = preparse($title, THIS_TRIM, 0, 255);
			$g = $userapi->group();
			$fid = isset($g[$fid]) ? $fid : 0;

			$db->query
				(
					"INSERT INTO ".$basepref."_user_group VALUES (
					 NULL,
					 '".$db->escape($fid)."',
					 '".$db->escape($title)."',
					 '0'
					 )"
				);

			$cache->cachesave(1);
			redirect('index.php?dn=group&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить группу
		 -------------------*/
		if ($_REQUEST['dn'] == 'groupdel')
		{
			global $gid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=content&amp;ops='.$sess['hash'].'">'.$lang['all_content'].'</a>',
					'<a href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[PERMISS].'</a>',
					'<a href="index.php?dn=group&amp;ops='.$sess['hash'].'">'.$lang['all_groups'].'</a>',
					$lang['all_delet']
				);

			$gid = preparse($gid, THIS_INT);

			if ($gid == 1)
			{
				redirect('index.php?dn=group&amp;ops='.$sess['hash']);
			}

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_user_group WHERE gid = '".$gid."'");
				$cache->cachesave(1);
				redirect('index.php?dn=group&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_user_group WHERE gid = '".$gid."'"));

				$yes = 'index.php?dn=groupdel&amp;gid='.$gid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=group&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_groups'], $lang['all_delet'], $yes, $not, $item['title']);
				$tm->footer();
			}
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
