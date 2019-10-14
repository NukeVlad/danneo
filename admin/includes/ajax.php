<?php
/**
 * File:        /admin/includes/ajax.php
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
define('PERMISS', '');

/**
 * Инициализация ядра
 */
require_once __DIR__.'/../init.php';

/**
 * Авторизация
 */
if ($ADMIN_AUTH == 1 AND $sess['hash'] == $ops)
{
	global $ADMIN_ID, $CHECK_ADMIN, $db, $basepref, $tm, $conf, $lang, $sess, $ops;

	header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
	header('Content-Type: text/html; charset='.$conf['langcharset'].'');

	/**
	 *  Список разрешенных админов
	 */
	if ($ADMIN_PERM == 1 OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		/**
		 * Только Ajax
		 */
		if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
		{
			return;
		}

		/**
		 * Массив доступных $_REQUEST['dn']
		 */
		$legaltodo = array('index', 'translit', 'platform', 'pagesclone');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		if ($_REQUEST['dn'] == 'index')
		{
			return;
		}

		/**
		 * Транслит ЧПУ
		 ------------------*/
		if ($_REQUEST['dn'] == 'translit')
		{
			global $conf, $title;

			$translit = new Translit();
			$str_cpu = $translit->process($title);
			$str_cpu = str_replace(array('&amp; ', '& '), '', $str_cpu);
			$out_cpu = mb_substr(trim($str_cpu), 0, 90, $conf['langcharset']);

			echo $translit->title($out_cpu);
		}

		/**
		 * Платформы сайтов
		 ---------------------*/
		if ($_REQUEST['dn'] == 'platform')
		{
			global $sess, $ajaxpid, $PLATFORM;

			$ajaxpid = preparse($ajaxpid, THIS_INT);
			if (in_array('platform', $ADMIN_PERM_ARRAY))
			{
				if (isset($PLATFORM[$ajaxpid]) OR $ajaxpid == 0) {
					setcookie(PCOOKIE, serialize(array($ajaxpid)), time() + LIFE_ADMIN, ADMPATH.'/');
				}
			}
			$json['id'] = $ajaxpid;
			echo Json::encode($json);
			exit();
		}

		/**
		 * Платформы страниц
		 ---------------------*/
		if ($_REQUEST['dn'] == 'pagesclone')
		{
			global $sess, $pl, $IPS;

			$pl = preparse($pl, THIS_INT);
			if (isset($IPS[$pl])) {
				setcookie(PCLONE, serialize(array($pl)), time() + LIFE_ADMIN, ADMPATH.'/');
			}
			$json['id'] = $pl;
			echo Json::encode($json);
			exit();
		}
	}
}
