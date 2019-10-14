<?php
/**
 * File:        /admin/system/banner/index.php
 *
 * Управление системой, Управление баннерами
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
			$lang['banner']
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
				'index', 'optsave', 'list', 'add', 'addsave', 'arrdel', 'edit', 'editsave', 'del',
				'zone', 'zonesave', 'zonedel'
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

			$link = '<a'.cho('index').' href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('list').' href="index.php?dn=list&amp;ops='.$sess['hash'].'">'.$lang['banner_all'].'</a>'
					.'<a'.cho('zone').' href="index.php?dn=zone&amp;ops='.$sess['hash'].'">'.$lang['banner_zone'].'</a>'
					.'<a'.cho('add').' href="index.php?dn=add&amp;ops='.$sess['hash'].'">'.$lang['banner_add'].'</a>';

			$tm->this_menu($link);
		}

		/**
		 * Вывод меню
		 */
		this_menu();

		/**
		 * Настройки
		 -------------*/
		if ($_REQUEST['dn'] == 'index')
		{
			$tm->header();

			$inqset = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = '".PERMISS."'"));

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['banner'].': '.$lang['all_set'].'</caption>';
					echo '	<tr>
								<td class="first">
									'.(($inqset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$inqset['setlang']])) ? $lang[$inqset['setlang']] : $inqset['setlang']).'
								</td>
								<td>';
					echo eval($inqset['setcode']);
					echo '		</td>
							</tr>';
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
		 * Сохранение настроек
		 -----------------------*/
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape($set['banner'])."' WHERE setopt = '".PERMISS."'");

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Управление баннерами ( Все баннеры )
		 ----------------------------------------*/
		if ($_REQUEST['dn'] == 'list')
		{
			global $nu, $p;

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}
			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$c  = $db->fetchrow($db->query("SELECT COUNT(banid) AS total FROM ".$basepref."_banners"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}

			$sf = $nu * ($p - 1);
			$inq = $db->query("SELECT * FROM ".$basepref."_banners ORDER BY banid DESC LIMIT ".$sf.", ".$nu);

			$pages  = $lang['all_pages'].':&nbsp; '.adm_pages('banners', 'banid', 'index', 'list', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=list&amp;p=".$p."&amp;ops=".$sess['hash'], $nu);

			$bz = array();
			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone ORDER BY banzonid ASC");
			while ($bitem = $db->fetchrow($binq)) {
				$bz[$bitem['banzonid']] = $bitem['banzoncode'];
			}

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['banner'].': '.$lang['banner_all'].'</caption>
						<tr><td colspan="11">'.$amount.'</td></tr>
						<tr>
							<th>ID</th>
							<th>'.$lang['all_name'].'</th>
							<th>'.$lang['banner_type'].'</th>
							<th>'.$lang['banner_zone'].'</th>
							<th>'.$lang['view_count'].'</th>
							<th>'.$lang['view_alr'].'</th>
							<th>'.$lang['remain'].'</th>
							<th>'.$lang['all_trans'].'</th>
							<th>CTR</th>
							<th>'.$lang['sys_manage'].'</th>
							<th class="ac"><input name="checkboxall" id="checkboxall" value="yes" type="checkbox"></th>
						</tr>';
			if ($db->numrows($inq) > 0)
			{
				while ($item = $db->fetchrow($inq))
				{
					echo '	<tr class="list">
								<td class="ac">'.$item['banid'].'</td>
								<td>'.$item['bantitle'].'</td>
								<td>'.(($item['bantype'] == 'code') ? $lang['all_code'] : '').(($item['bantype'] == 'click') ? $lang['all_click'] : '').'</td>
								<td>'.$bz[$item['banzonid']].'</td>
								<td>'.$item['banlimit'].'</td>
								<td>'.$item['banview'].'</td>
								<td>'.((($item['banlimit'] - $item['banview']) > 0) ? ($item['banlimit'] - $item['banview']) : '&#8212;').'</td>
								<td>'.(($item['bantype'] == 'click') ? $item['banclick'] : '&#8212;').'</td>
								<td>'.(($item['bantype'] == 'click' AND $item['banview'] > 0) ? number_format(($item['banclick'] / $item['banview'] * 100),2).' %' : '&#8212;').'</td>
								<td class="gov">
									<a href="index.php?dn=edit&amp;banid='.$item['banid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
									<a href="index.php?dn=del&amp;banid='.$item['banid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
								</td>
								<td class="mark ac"><input type="checkbox" name="delbann['.$item['banid'].']" value="yes"></td>
							</tr>';
				}
			}
			else
			{
				echo '		<tr>
								<td class="ac" colspan="11">
									<div class="pads">'.$lang['down_brok_no'].'</div>
								</td>
							</tr>';
			}
			echo '			<tr><td colspan="11">'.$pages.'</td></tr>
							<tr class="tfoot">
								<td colspan="11">
									<input type="hidden" name="dn" value="arrdel">
									<input type="hidden" name="p" value="'.$p.'">
									<input type="hidden" name="nu" value="'.$nu.'">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input accesskey="s" id="button" class="main-button" value="'.$lang['all_delet'].'" type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';

			$tm->footer();
		}

		/**
		 * Добавить баннер
		 ---------------------*/
		if ($_REQUEST['dn'] == 'add')
		{
			$tm->header();

			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone ORDER BY banzonid ASC");

			echo '	<div class="section">
					<form action="index.php" method="post" id="bannerform">
					<table class="work">
						<caption>'.$lang['banner'].': '.$lang['banner_add'].'</caption>
						<tr>
							<td>'.$lang['banner_type'].'</td>
							<td>
								<select name="bantype" style="width: 165px;">
									<option value="click">'.$lang['all_click'].'</option>
									<option value="code">'.$lang['all_code'].'</option>
								</select>';
								$tm->outhint($lang['banner_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['banner_zone'].'</td>
							<td>
								<select name="banzonid" style="width: 165px;">';
			while ($bitem = $db->fetchrow($binq)) {
				echo '				<option value="'.$bitem['banzonid'].'">'.$bitem['banzoncode'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['view_count'].'</td>
							<td><input type="text" name="banlimit" size="25" maxlength="11" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['act_in_mod'].'</td>
							<td class="banner-mod">
								<div class="but" style="margin-bottom: 5px;" onclick="$.allselect(\'bannerform\');"> '.$lang['all_mark'].'</div>';
			$inq = $db->query("SELECT * FROM ".$basepref."_mods WHERE active='yes'");
			while ($item = $db->fetchrow($inq)) {
				echo '			<p><input type="checkbox" name="banner_mods['.$item['file'].']" value="yes" checked="checked"> &nbsp; '.$item['name'].'</p>';
			}
			echo '			</td>
						</tr>
						<tr><th class="ac site bold" colspan="2">'.$lang['all_click'].'</th></tr>
						<tr>
							<td>'.$lang['all_name'].'</td>
							<td><input type="text" name="bantitle" size="70"></td>
						</tr>
						<tr>
							<td>'.$lang['all_link'].'</td>
							<td><input type="text" name="banurl" size="70"></td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="banimg" id="banimg" size="70" type="text">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/banner/\',\'&amp;field[1]=banimg&amp;field[2]=img\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr><th class="ac alternative bold" colspan="2">'.$lang['all_code'].'</th></tr>
						<tr>
							<td>'.$lang['all_code'].'</td>
							<td>';
								$tm->textarea('bancode', 10, 70, '', 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="dn" value="addsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Добавить баннер (сохранение)
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'addsave')
		{
			global $bantype, $bantitle, $banlimit, $banurl, $banimg, $bancode, $banner_mods, $banzonid;

			$banlimit = (intval($banlimit) > 0) ? intval($banlimit) : 100;
			$banzonid = preparse($banzonid, THIS_INT);
			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone WHERE banzonid = '".$banzonid."'");
			$error = 1;
			$modslist = '';

			if ($db->numrows($binq) == 0)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['banner_add'], $lang['banner_no_zone']);
				$tm->footer();
			}

			if (is_array($banner_mods))
			{
				$list = array();
				foreach ($banner_mods as $key => $val)
				{
					if (isset($key)) {
						$list[]= $key;
					}
				}
				$modslist = implode('|', $list);
				if ($bantype == 'click') {
					$bancode == '';
					if ($bantitle AND $banurl OR $banimg AND $banurl) {
						$error = 0;
					}
				} else {
					$bantitle = $banurl = $banimg = '';
					if ($bancode) {
						$error = 0;
					}
				}
			}

			if ($error == 1)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['banner_add'], $lang['forgot_name']);
				$tm->footer();
			}

			$db->query
				(
					"INSERT INTO ".$basepref."_banners VALUES (
					 NULL,
					 '".$db->escape($bantype)."',
					 '".$db->escape($banurl)."',
					 '".$db->escape($bancode)."',
					 '".$db->escape($bantitle)."',
					 '".$db->escape($banimg)."',
					 '".$banlimit."',
					 '0',
					 '0',
					 '".$db->escape($modslist)."',
					 '".$banzonid."'
					 )"
				);

			$cache->cachesave(1);
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактировать баннер
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'edit')
		{
			global $banid;

			$tm->header();

			$banid = preparse($banid, THIS_INT);
			$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_banners WHERE banid = '".$banid."'"));
			$banner_mods = explode('|', $item['banmods']);
			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone ORDER BY banzonid ASC");

			echo '	<div class="section">
					<form action="index.php" method="post" id="bannerform">
					<table class="work">
						<caption>'.$item['bantitle'].': '.$lang['all_edit'].'</caption>
						<tr>
							<td>'.$lang['banner_type'],'</td>
							<td>
								<select name="bantype" style="width: 165px;">
									<option value="click"'.(($item['bantype'] == 'click') ? ' selected' : '').'>'.$lang['all_click'].'</option>
									<option value="code"'.(($item['bantype'] == 'code') ? ' selected' : '').'>'.$lang['all_code'].'</option>
								</select> ';
								$tm->outhint($lang['banner_hint']);
			echo '			</td>
						</tr>
						<tr>
							<td>'.$lang['banner_zone'].'</td>
							<td>
								<select name="banzonid" style="width: 165px;">';
			while ($bitem = $db->fetchrow($binq)) {
				echo '				<option value="'.$bitem['banzonid'].'"'.(($item['banzonid'] == $bitem['banzonid']) ? ' selected' : '').'>'.$bitem['banzoncode'].'</option>';
			}
			echo '				</select>
							</td>
						</tr>
						<tr>
							<td>'.$lang['view_count'].'</td>
							<td><input type="text" name="banlimit" size="25" maxlength="11" value="'.$item['banlimit'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['view_alr'].'</td>
							<td><input type="text" name="banview" size="25" maxlength="11" value="'.$item['banview'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['act_in_mod'].'</td>
							<td class="banner-mod">
								<div class="but" style="margin-bottom: 5px;" onclick="$.allselect(\'bannerform\');"> '.$lang['all_mark'].'</div>';
			$inqs = $db->query("SELECT * FROM ".$basepref."_mods WHERE active='yes'");
			while ($items = $db->fetchrow($inqs))
			{
				echo '			<p class="'.((@in_array($items['file'],$banner_mods)) ? 'm-act' : 'm-no').'">
									<input type="checkbox" name="banner_mods['.$items['file'].']" value="yes"'.((@in_array($items['file'],$banner_mods)) ? ' checked' : '').'> '.$items['name'].'
								</p>';
			}
			echo '			</td>
						</tr>
						<tr>
							<th class="ac site bold" colspan="2">'.$lang['all_click'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_name'].'</td>
							<td><input type="text" name="bantitle" size="70" value="'.$item['bantitle'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_link'].'</td>
							<td><input type="text" name="banurl" size="70" value="'.$item['banurl'].'"></td>
						</tr>
						<tr>
							<td>'.$lang['all_image'].'</td>
							<td>
								<input name="banimg" id="banimg" size="40" type="text" value="'.$item['banimg'].'">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/banner/\',\'&amp;field[1]=banimg&amp;field[2]=img\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr>
							<th class="ac alternative bold" colspan="2">'.$lang['all_code'].'</th>
						</tr>
						<tr>
							<td>'.$lang['all_code'].'</td>
							<td>';
								$tm->textarea('bancode', 10, 70, $item['bancode'], 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input type="hidden" name="banid" value="'.$item['banid'].'">
								<input type="hidden" name="dn" value="editsave">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';

			$tm->footer();
		}

		/**
		 * Редактировать баннер (сохранение)
		 -------------------------------------*/
		if ( $_REQUEST['dn'] == 'editsave')
		{
			global $bantype, $bantitle, $banlimit, $banview, $banurl, $banimg, $bancode, $banner_mods, $banid, $banzonid;

			$banlimit = (preparse($banlimit, THIS_INT) > 0) ? preparse($banlimit, THIS_INT) : 100;
			$banview  = (preparse($banview, THIS_INT) > 0) ? preparse($banview, THIS_INT) : 1;
			$banid    = preparse($banid, THIS_INT);
			$banzonid = preparse($banzonid, THIS_INT);

			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone WHERE banzonid = '".$banzonid."'");

			$error = 1;
			$modslist = null;

			if ($db->numrows($binq) == 0)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['all_edit'], $lang['banner_no_zone']);
				$tm->footer();
			}

			if (is_array($banner_mods))
			{
				$list = array();
				foreach ($banner_mods as $key => $val)
				{
					if (isset($key)) {
						$list[]= $key;
					}
				}
				$modslist = implode('|', $list);
				if ($bantype == 'click')
				{
					$bancode == '';
					if ($bantitle AND $banurl OR $banimg AND $banurl) {
						$error = 0;
					}
				}
				else
				{
					$bantitle = $banurl = $banimg = '';
					if ($bancode) {
						$error = 0;
					}
				}
			}

			if ($error == 1)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['all_edit'], $lang['forgot_name']);
				$tm->footer();
			}

			$db->query
				(
					"UPDATE ".$basepref."_banners SET
					 bantype  = '".$db->escape($bantype)."',
					 banurl   = '".$db->escape($banurl)."',
					 bancode  = '".$db->escape($bancode)."',
					 bantitle = '".$db->escape($bantitle)."',
					 banimg   = '".$db->escape($banimg)."',
					 banlimit = '".$banlimit."',
					 banview  = '".$banview."',
					 banmods  = '".$db->escape($modslist)."',
					 banzonid = '".$banzonid."'
					 WHERE banid = '".$banid."'"
				);

			$cache->cachesave(1);
			redirect('index.php?dn=list&amp;ops='.$sess['hash']);
		}

		/**
		 * Массовое удаление баннеров
		 --------------------------------*/
		if ($_REQUEST['dn'] == 'arrdel')
		{
			global $delbann, $p, $nu, $ok;

			$p   = preparse($p, THIS_INT);
			$nu  = preparse($nu, THIS_INT);
			if (preparse($delbann, THIS_ARRAY) == 1)
			{
				if ($ok == 'yes')
				{
					if (is_array($delbann))
					{
						foreach ($delbann as $id => $v)
						{
							$db->query("DELETE FROM ".$basepref."_banners WHERE banid = '".intval($id)."'");
						}
					}
					$redir = 'index.php?dn=index&amp;ops='.$sess['hash'];
					$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
					$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
					redirect($redir);
				}
				else
				{
					$temparray = $delbann;
					$count = count($temparray);
					$hidden = '';
					foreach ($delbann as $key => $id) {
						$hidden.= '<input type="hidden" name="delbann['.$key.']" value="yes">';
					}
					$h = '	<input type="hidden" name="p" value="'.$p.'">
							<input type="hidden" name="nu" value="'.$nu.'">';
					$tm->header();
					echo '	<form action="index.php" method="post">
							<table id="arr-work" class="work">
								<caption>'.$lang['array_control'].': '.$lang['all_delet'].' ('.$count.')</caption>
								<tr>
									<td class="cont">'.$lang['alertdel'].'</td>
								</tr>
								<tr class="tfoot">
									<td>
										'.$hidden.'
										'.$h.'
										<input type="hidden" name="dn" value="arrdel">
										<input type="hidden" name="ok" value="yes">
										<input type="hidden" name="ops" value="'.$sess['hash'].'">
										<input accesskey="s" class="side-button" value="'.$lang['all_go'].'" type="submit">
										<input class="side-button" onclick="javascript:history.go(-1)" value="'.$lang['cancel'].'" type="button">
									</td>
								</tr>
							</table>
							</form>';
					$tm->footer();
				}
			}
			$redir = 'index.php?dn=list&amp;ops='.$sess['hash'];
			$redir.= ( ! empty($p)) ? '&amp;p='.preparse($p, THIS_INT) : '';
			$redir.= ( ! empty($nu)) ? "&amp;nu=".preparse($nu, THIS_INT) : '';
			redirect($redir);
		}

		/**
		 * Удалить баннер
		 -------------------*/
		if ($_REQUEST['dn'] == 'del')
		{
			global $banid, $ok;

			$banid = preparse($banid, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_banners WHERE banid = '".$banid."'");
				$cache->cachesave(1);
				redirect('index.php?dn=list&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT bantitle FROM ".$basepref."_banners WHERE banid = '".$banid."'"));

				$yes = 'index.php?dn=del&amp;banid='.$banid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=list&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['bantitle']), $yes, $not);
				$tm->footer();
			}
		}

		/**
		 * Баннерные зоны (листинг / добавление)
		 -----------------------------------------*/
		if ($_REQUEST['dn'] == 'zone')
		{
			global $nu, $p;

			$tm->header();

			if(isset($nu) AND ! empty($nu)) {
				echo '<script>cookie.set("num", "'.$nu.'", { path: "'.ADMPATH.'/" });</script>';
			}

			$nu = isset($nu) ? $nu : (isset($_COOKIE['num']) ? $_COOKIE['num'] : null);
			$nu = ( ! is_null($nu) AND in_array($nu, $conf['num'])) ? $nu : $conf['num'][0];
			$p  = ( ! isset($p) OR $p <= 1) ? 1 : $p;

			$c  = $db->fetchrow($db->query("SELECT COUNT(banzonid) AS total FROM ".$basepref."_banners_zone"));
			if ($nu > 10 AND $c['total'] <= (($nu * $p) - $nu)) {
				$p = 1;
			}

			$sf = $nu * ($p - 1);
			$inq = $db->query
					(
						"SELECT zone.*,COUNT(banner.banid) AS total
						 FROM ".$basepref."_banners_zone AS zone
						 LEFT JOIN ".$basepref."_banners AS banner ON (zone.banzonid = banner.banzonid)
						 GROUP BY zone.banzonid
						 ORDER BY zone.banzonid ASC LIMIT ".$sf.", ".$nu
					);

			$pages = $lang['all_pages'].':&nbsp; '.adm_pages('banners_zone', 'banzonid', 'index', 'zone', $nu, $p, $sess);
			$amount = $lang['amount_on_page'].':&nbsp; '.amount_pages("index.php?dn=zone&amp;p=".$p."&amp;ops=".$sess['hash']."&amp;nu=", $nu);

			echo "	<script>
					$(function() {
						$('.readonly').focus(function () {
							$(this).select();
						}).mouseup(function(e){
							e.preventDefault();
						});
					});
					</script>";
			echo '	<div class="section">
					<table id="list" class="work">
						<caption>'.$lang['banner'].': '.$lang['banner_zone'].'</caption>
						<tr><td colspan="5">'.$amount.'</td></tr>
						<tr>
							<th>ID</th>
							<th>'.$lang['all_name'].'</th>
							<th>'.$lang['all_temp_tag'].'</th>
							<th>'.$lang['all_col'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="ac">'.$item['banzonid'].'</td>
							<td class="site pw15">'.$item['banzonname'].'</td>
							<td class="vm">
								<input value="{'.$item['banzoncode'].'}" readonly="readonly" class="readonly" type="text" size="3" />
							</td>
							<td class="vm">'.$item['total'].'</td>
							<td class="vm gov">
								<a href="index.php?dn=zonedel&amp;banzonid='.$item['banzonid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr><td colspan="5">'.$pages.'</td></tr>
					</table>
					</div>
					<div class="pad"></div>
					<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_banner_zone'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_name'].'</td>
							<td><input name="name" type="text" size="50" required="required"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_temp_tag'].'</td>
							<td><input type="text" name="code" size="50" required="required" />';
								$tm->outhint($lang['filed_name_hint']);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="zonesave">
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
		 * Баннерные зоны (добавление зоны)
		 ------------------------------------*/
		if ($_REQUEST['dn'] == 'zonesave')
		{
			global $code, $name;

			if (preparse($code, THIS_SYMNUM) == 1 OR preparse($code, THIS_EMPTY) == 1)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['add_banner_zone'], $lang['banner_format_zone']);
				$tm->footer();
			}

			$binq = $db->query("SELECT * FROM ".$basepref."_banners_zone WHERE banzoncode = '".$db->escape($code)."'");
			if ($db->numrows($binq) > 0)
			{
				$tm->header();
				$tm->error($lang['banner'], $lang['add_banner_zone'], $lang['banner_no_zone']);
				$tm->footer();
			}

			$db->query("INSERT INTO ".$basepref."_banners_zone VALUES (NULL, '".$db->escape($code)."', '".$db->escape($name)."')");

			$cache->cachesave(1);
			redirect('index.php?dn=zone&amp;ops='.$sess['hash']);
		}

		/**
		 * Баннерные зоны (удаление зоны)
		 ----------------------------------*/
		if ($_REQUEST['dn']=='zonedel')
		{
			global $banzonid, $ok;

			$banzonid = preparse($banzonid, THIS_INT);
			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_banners WHERE banzonid = '".$banzonid."'");
				$db->query("DELETE FROM ".$basepref."_banners_zone WHERE banzonid = '".$banzonid."'");
				$cache->cachesave(1);
				redirect('index.php?dn=zone&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_banners_zone WHERE banzonid = '".$banzonid."'"));

				$yes = 'index.php?dn=zonedel&amp;banzonid='.$banzonid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=zone&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['banner_zone'], $lang['all_delet'], $yes, $not, preparse_un($item['banzoncode']));
				$tm->footer();
			}
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
