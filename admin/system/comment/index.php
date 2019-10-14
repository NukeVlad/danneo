<?php
/**
 * File:        /admin/system/comment/index.php
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
			$lang['menu_comment']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID,$CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'optsave', 'smilie', 'smilieup', 'smilieadd', 'smilieaddsave', 'smilieedit', 'smiliedel');

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

			$link = '<a'.cho('index').'  href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['all_set'].'</a>'
					.'<a'.cho('smilie').' href="index.php?dn=smilie&amp;ops='.$sess['hash'].'">'.$lang['smilies'].'</a>'
					.'<a'.cho('smilieadd').' href="index.php?dn=smilieadd&amp;ops='.$sess['hash'].'">'.$lang['add_smilie'].'</a>';

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
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['menu_comment'], $lang['all_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['menu_comment'].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'comment'");
			while ($itemset = $db->fetchrow($inqset))
			{
				echo '	<tr>
							<td class="first">
								'.(($itemset['setmark'] == 1) ? '<span>*</span> ' : '').((isset($lang[$itemset['setlang']])) ? $lang[$itemset['setlang']] : $itemset['setlang']).'
							</td>
							<td>';
				echo eval($itemset['setcode']);
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
		 */
		if ($_REQUEST['dn'] == 'optsave')
		{
			global $set, $cache;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					$lang['menu_comment'], $lang['all_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'comment'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1)
					{
						$tm->header();
						$tm->error($lang['menu_comment'], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'], THIS_EMPTY) == 0) {
						eval($item['setvalid']);
					}
					if ($item['setname'] == 'combad') {
						$set[$item['setname']] = trim(trim($set['combad'], ' '), ',');
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']],THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Смайлики
		 -----------*/
		if ($_REQUEST['dn'] == 'smilie')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>',
					$lang['smilies']
				);

			$tm->header();

			$inq = $db->query("SELECT * FROM ".$basepref."_smilie ORDER BY posit");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['smilies'].'</caption>
						<tr>
							<th class="ar">'.$lang['view'].'</th>
							<th>'.$lang['code_paste'].'</th>
							<th>'.$lang['all_posit'].'</th>
							<th>'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item=$db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td><img src="'.WORKURL.'/'.$item['smimg'].'" alt="'.$item['smalt'].'" /></td>
							<td><input type="text" value="'.$item['smcode'].'" name="code['.$item['smid'].']" size="3" maxlength="10"></td>
							<td><input type="text" value="'.$item['posit'].'" name="posit['.$item['smid'].']" size="3" maxlength="3"></td>
							<td class="gov">
								<a href="index.php?dn=smilieedit&amp;smid='.$item['smid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/edit.png" alt="'.$lang['all_edit'].'" /></a>
								<a href="index.php?dn=smiliedel&amp;smid='.$item['smid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="dn" value="smilieup">
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
		 * Смайлики (сохранение)
		 ------------------------*/
		if ($_REQUEST['dn'] == 'smilieup')
		{
			global $posit, $code;

			foreach ($posit as $id => $pos)
			{
				$pos = intval($pos);
				$id = intval($id);
				if ($id > 0) {
					$db->query("UPDATE ".$basepref."_smilie SET posit = '".$pos."' WHERE smid = '".$id."'");
				}
			}

			foreach ($code as $id => $co)
			{
				$co = trim($co);
				$id = intval($id);
				if ($id > 0 AND isset($co)) {
					$db->query("UPDATE ".$basepref."_smilie SET smcode = '".$db->escape($co)."' WHERE smid = '".$id."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=smilie&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавление смайлика
		 ----------------------*/
		if ($_REQUEST['dn'] == 'smilieadd')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>',
					'<a href="index.php?dn=smilie&amp;ops='.$sess['hash'].'">'.$lang['smilies'].'</a>',
					$lang['all_add']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['add_smilie'].'</caption>
						<tr>
							<td class="first"><span>*</span> '.$lang['code_paste'].'</td>
							<td><input type="text" name="smcode" size="50" maxlength="10" required="required"></td>
						</tr>
						<tr>
							<td>'.$lang['all_alt_image'].'</td>
							<td><input type="text" name="smalt" size="50" maxlength="100"></td>
						</tr>
						<tr>
							<td class="first"><span>*</span> '.$lang['all_image'].'</td>
							<td>
								<input name="image" id="image" size="50" type="text" required="required">
								<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/comment/smilie/\',\'&amp;field[1]=image\')" value="'.$lang['filebrowser'].'" type="button">
							</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="smilieaddsave">
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
		 * Добавление смайлика (сохранение)
		 -----------------------------------*/
		if ($_REQUEST['dn'] == 'smilieaddsave')
		{
			global $smcode, $smalt, $image;

			$smcode = trim($smcode);
			$smalt = trim($smalt);
			$image = trim($image);

			if( ! empty($smcode) AND ! empty($image))
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_smilie VALUES (
						 NULL,
						 '".$db->escape($smcode)."',
						 '".$db->escape($smalt)."',
						 '".$db->escape($image)."',
						 '0'
						 )"
					);
			}

			$cache->cachesave(1);
			redirect('index.php?dn=smilie&amp;ops='.$sess['hash']);
		}

		/**
		 * Редактирвание смайлика
		 */
		if ($_REQUEST['dn'] == 'smilieedit')
		{
			global $ok, $lang, $smid, $smcode, $smalt, $image;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>',
					'<a href="index.php?dn=smilie&amp;ops='.$sess['hash'].'">'.$lang['smilies'].'</a>',
					$lang['all_edit']
				);

			$smid = intval($smid);
			$smid = preparse($smid, THIS_INT);

			if ($ok == 'yes')
			{
				$smcode = trim($smcode);
				$smalt = trim($smalt);
				$image = trim($image);

				if( ! empty($smcode) AND ! empty($image))
				{
					$db->query
						(
							"UPDATE ".$basepref."_smilie SET
							 smcode = '".$db->escape($smcode)."',
							 smalt  = '".$db->escape($smalt)."',
							 smimg  = '".$db->escape($image)."'
							 WHERE smid = '".$smid."'"
						);
				}

				$cache->cachesave(1);
				redirect('index.php?dn=smilie&amp;ops='.$sess['hash']);
			}
			else
			{
				$tm->header();

				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_smilie WHERE smid = '".$smid."'"));

				echo '	<div class="section">
						<form action="index.php" method="post">
						<table class="work">
							<caption>'.$lang['all_edit'].' '.$item['smcode'].'</caption>
							<tr>
								<td class="first"><span>*</span> '.$lang['code_paste'].'</td>
								<td>
									<input type="text" name="smcode" value="'.$item['smcode'].'" size="50" maxlength="10" required="required">
								</td>
							</tr>
							<tr>
								<td>'.$lang['all_alt_image'].'</td>
								<td>
									<input type="text" name="smalt" value="'.$item['smalt'].'" size="50" maxlength="100">
								</td>
							</tr>
							<tr>
								<td class="first"><span>*</span> '.$lang['all_image'].'</td>
								<td>
									<input name="image" id="image" value="'.$item['smimg'].'" size="50" type="text" required="required">
									<input class="side-button" onclick="javascript:$.filebrowser(\''.$sess['hash'].'\',\'/comment/smilie/\',\'&amp;field[1]=image\')" value="'.$lang['filebrowser'].'" type="button">
								</td>
							</tr>
							<tr class="tfoot">
								<td colspan="2">
									<input type="hidden" name="dn" value="smilieedit">
									<input type="hidden" name="smid" value="'.$smid.'">
									<input type="hidden" name="ok" value="yes">
									<input type="hidden" name="ops" value="'.$sess['hash'].'">
									<input accesskey="s" class="main-button" value=" '.$lang['all_save'].' " type="submit">
								</td>
							</tr>
						</table>
						</form>
						</div>';

				$tm->footer();
			}
		}

		/**
		 * Удаление смайлика
		 */
		if ($_REQUEST['dn'] == 'smiliedel')
		{
			global $smid, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_comment'].'</a>',
					'<a href="index.php?dn=smilie&amp;ops='.$sess['hash'].'">'.$lang['smilies'].'</a>',
					$lang['all_delet']
				);

			$smid = preparse($smid, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_smilie WHERE smid = '".$smid."'");
				$cache->cachesave(1);
				redirect('index.php?dn=smilie&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_smilie WHERE smid = '".$smid."'"));

				$yes = 'index.php?dn=smiliedel&amp;smid='.$smid.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=smilie&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], $item['smalt'].' '.$item['smcode'], $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['menu_comment'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
