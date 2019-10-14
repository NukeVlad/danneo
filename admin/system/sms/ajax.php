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
require_once __DIR__.'/../../init.php';

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
		$legaltodo = array('index', 'request');

		/**
		 * Проверка $_REQUEST['dn']
		 */
		$_REQUEST['dn'] = (isset($_REQUEST['dn']) AND in_array(preparse_dn($_REQUEST['dn']), $legaltodo)) ? preparse_dn($_REQUEST['dn']) : 'index';

		if ($_REQUEST['dn'] == 'index')
		{
			return;
		}

		/**
		 * Проверки API сервиса
		 -----------------------*/
		if ($_REQUEST['dn'] == 'request')
		{
			global $sess, $option, $service;

			if ($service == 'smsc')
			{
				$sms = new DN\Sms\SMSC();
				switch ($option) {
					case 'check':
						echo ($sms->check()) ? '<span class="green">Success!</span>' : '<span class="red">Not confirmed!</span>';
						break;
					case 'balance':
						echo ($sms->balance()) ? $sms->balance() : '<span class="red">Error!</span>';
				}
			}
			elseif ($service == 'smsru')
			{
				$sms = new DN\Sms\SMSRU();
				switch ($option) {
					case 'check':
						if ($sms->check() == '100') {
							echo '<span class="green">Success!</span>';
						} elseif ($sms->check() == '301') {
							echo '<span class="red">Not confirmed!</span>';
						}
						break;
					case 'balance':
						echo ($sms->balance()) ? $sms->balance() : '<span class="red">Error!</span>';
				}
			}
			exit();
		}
	}
}
