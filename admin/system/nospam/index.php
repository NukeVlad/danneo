<?php
/**
 * File:        /admin/system/nospam/index.php
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
			$lang['menu_nospam']
		);

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'optsave', 'control', 'controlup', 'controladd', 'controladdsave', 'controldel');

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
					.'<a'.cho('control').' href="index.php?dn=control&amp;ops='.$sess['hash'].'">'.$lang['control_word'].'</a>'
					.'<a'.cho('controladd').' href="index.php?dn=controladd&amp;ops='.$sess['hash'].'">'.$lang['control_add'].'</a>';

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
					$lang['menu_nospam'], $lang['all_set']
				);

			$tm->header();

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['menu_nospam'].': '.$lang['all_set'].'</caption>';
			$inqset = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'nospam'");
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
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_nospam'].'</a>',
					$lang['all_set']
				);

			$inq = $db->query("SELECT * FROM ".$basepref."_settings WHERE setopt = 'nospam'");

			while ($item = $db->fetchrow($inq))
			{
				if (isset($set[$item['setname']]))
				{
					if ($item['setmark'] == 1 AND preparse($set[$item['setname']], THIS_EMPTY) == 1) {
						$tm->header();
						$tm->error($lang['menu_nospam'], $lang['all_set'], $lang['forgot_name']);
						$tm->footer();
					}
					if (preparse($item['setvalid'],THIS_EMPTY) == 0) {
						@eval($item['setvalid']);
					}
					$db->query("UPDATE ".$basepref."_settings SET setval = '".$db->escape(preparse($set[$item['setname']],THIS_TRIM))."' WHERE setid = '".$item['setid']."'");
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=index&amp;ops='.$sess['hash']);
		}

		/**
		 * Контрольный вопрос
		 ----------------------*/
		if ($_REQUEST['dn'] == 'control')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_nospam'].'</a>',
					$lang['control_word']
				);

			$tm->header();

			if ($conf['control'] == 'yes')
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_control");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table id="list" class="work">
						<caption>'.$lang['control_word'].'</caption>
						<tr>
							<th class="ac">ID</th>
							<th class="ac pw45">'.$lang['faq_question'].'</th>
							<th class="ac pw45">'.$lang['faq_answer'].'</th>
							<th class="al">'.$lang['sys_manage'].'</th>
						</tr>';
			while ($item = $db->fetchrow($inq))
			{
				echo '	<tr class="list">
							<td class="ac">'.$item['cid'].'</td>
							<td class="pw45 vm">';
								$tm->textarea('issue['.$item['cid'].']', 1, 50, $item['issue'], 0);
				echo '		</td>
							<td class="pw45 vm">';
								$tm->textarea('response['.$item['cid'].']', 1, 50, $item['response'], 0);
				echo '		</td>
							<td class="vm gov">
								<a href="index.php?dn=controldel&amp;id='.$item['cid'].'&amp;ops='.$sess['hash'].'"><img src="'.ADMPATH.'/template/skin/'.$sess['skin'].'/images/del.png" alt="'.$lang['all_delet'].'" /></a>
							</td>
						</tr>';
			}
			echo '		<tr class="tfoot">
							<td colspan="4">
								<input type="hidden" name="dn" value="controlup">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_save'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
			}

			$tm->footer();
		}

		/**
		 * Контрольный вопрос (сохранение настроек)
		 */
		if ($_REQUEST['dn'] == 'controlup')
		{
			global $issue, $response;

			if (is_array($issue) AND is_array($response))
			{
				foreach ($issue as $k => $v)
				{
					if (isset($response[$k]))
					{
						$k = intval($k);
						$v = trim($v);
						$rv = trim($response[$k]);
						if ($k > 0 AND ! empty($v) AND ! empty($rv))
						{
							$db->query
								(
									"UPDATE ".$basepref."_control SET
									 issue     = '".$db->escape($v)."',
									 response  = '".$db->escape($rv)."'
									 WHERE cid = '".$k."'"
								);
						}
					}
				}
			}

			$cache->cachesave(1);
			redirect('index.php?dn=control&amp;ops='.$sess['hash']);
		}

		/**
		 * Добавить контрольный вопрос
		 -------------------------------*/
		if ($_REQUEST['dn'] == 'controladd')
		{
			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_nospam'].'</a>',
					$lang['control_add']
				);

			$tm->header();

			if ($conf['control'] == 'yes')
			{
				$inq = $db->query("SELECT * FROM ".$basepref."_control");

			echo '	<div class="section">
					<form action="index.php" method="post">
					<table class="work">
						<caption>'.$lang['all_add'].' '.$lang['control_word'].'</caption>
						<tr>
							<td class="first pw10 vm"><span>*</span> '.$lang['faq_question'].'</td>
							<td class="pw90 vm">';
								$tm->textarea('issue', 1, 50, '', 0, '', '', 1);
			echo '			</td>
						</tr>
						<tr>
							<td class="first pw10 vm"><span>*</span> '.$lang['faq_answer'].'</td>
							<td class="pw90 vm">';
								$tm->textarea('response', 1, 50, '', 0, '', '', 1);
			echo '			</td>
						</tr>
						<tr class="tfoot">
							<td colspan="2">
								<input type="hidden" name="dn" value="controladdsave">
								<input type="hidden" name="ops" value="'.$sess['hash'].'">
								<input class="main-button" value="'.$lang['all_submint'].'" type="submit">
							</td>
						</tr>
					</table>
					</form>
					</div>';
			}

			$tm->footer();
		}

		/**
		 * Добавить контрольный вопрос (сохранение)
		 -------------------------------------------*/
		if ($_REQUEST['dn'] == 'controladdsave')
		{
			global $issue, $response;

			$issue = trim($issue);
			$response = trim($response);

			if( ! empty($response) AND ! empty($issue))
			{
				$db->query
					(
						"INSERT INTO ".$basepref."_control VALUES (
						 NULL,
						 '".$db->escape($issue)."',
						 '".$db->escape($response)."'
						)"
					);
			}

			$cache->cachesave(1);
			redirect('index.php?dn=control&amp;ops='.$sess['hash']);
		}

		/**
		 * Удалить контрольный вопрос
		 */
		if ($_REQUEST['dn'] == 'controldel')
		{
			global $id, $ok;

			$template['breadcrumb'] = array
				(
					'<a href="'.ADMPATH.'/index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['desktop'].'</a>',
					'<a href="'.ADMPATH.'/index.php?dn=system&amp;ops='.$sess['hash'].'">'.$lang['all_system'].'</a>',
					'<a href="index.php?dn=index&amp;ops='.$sess['hash'].'">'.$lang['menu_nospam'].'</a>',
					$lang['control_word']
				);

			$id = preparse($id, THIS_INT);

			if ($ok == 'yes')
			{
				$db->query("DELETE FROM ".$basepref."_control WHERE cid = '".$id."'");
				$cache->cachesave(1);
				redirect('index.php?dn=control&amp;ops='.$sess['hash']);
			}
			else
			{
				$item = $db->fetchrow($db->query("SELECT issue FROM ".$basepref."_control WHERE cid = '".$id."'"));

				$yes = 'index.php?dn=controldel&amp;id='.$id.'&amp;ok=yes&amp;ops='.$sess['hash'];
				$not = 'index.php?dn=control&amp;ops='.$sess['hash'];

				$tm->header();
				$tm->shortdel($lang['all_delet'], preparse_un($item['issue']), $yes, $not);
				$tm->footer();
			}
		}

	/**
	 * Права доступа
	 */
	} else {
		$tm->header();
		$tm->access($lang['menu_nospam'], $lang['no_access']);
		$tm->footer();
	}
/**
 * Авторизация, редирект
 */
} else {
	redirect(ADMPATH.'/login.php');
	exit();
}
