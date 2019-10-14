<?php
/**
 * File:        /admin/core/permission.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Привилегированные администраторы.
 * Несколько ID разделяются символом ";" без пробелов.
 * Пример: '1;2;3';
 */
$LIST_ADMID = '1';

/**
 * Не редактируемый массив.
 */
$CHECK_ADMIN['admid'] = explode(';', $LIST_ADMID);

/**
 * Секретное слово.
 * Только английские символы или цифры!
 */
$CHECK_ADMIN['sword'] = 'qwerty';

/**
 * Дополнение Cookies администратора.
 * Только латинские символы и цифры.
 */
define('SALT_ADMIN', '123456');

/**
 * Каталог администратора
 */
define('APANEL', 'admin');

/**
 * Время жизни сессси администратора.
 */
define('LIFE_ADMIN', 14400);

/**
 * Шаблон оформления апанели по умолчанию.
 */
define('SKIN_DEF', 'Lite');

/**
 * Константа кодировки по умолчанию.
 */
define('CHAR_DEF', 'utf-8');

/**
 * Кодировки
 */
$LIST_CHARSET = 'utf-8';

/**
 * Массив кодировок.
 */
$CHECK_CHARSET = explode(';', $LIST_CHARSET);

/**
 * Атрибут lang по умолчанию.
 */
define('CODE_DEF', 'en');

/**
 * Разрешить AJAX. yes - да, no - нет.
 */
define('ENABLE_AJAX', 'yes');

/**
 * Таблица настроек.
 */
define('TABLE', 'settings');

/**
 * Группы исключения из редактора настроек
 */
$MOD_NOSET = array
(
	'base',
	'filebrowser',
	'platform',
	'amanage',
	'lang',
	'subscribe',
	'map',
	'home',
	'apanel',
	'integ',
	'menu',
	'team'
);

/**
 * Ноды "Управление системой"
 */
$LIST_MOD_ADM = array
(
	'options',
	'system',
	'block',
	'menu',
	'seo',
	'lang',
	'banner',
	'base',
	'amanage',
	'platform',
	'comment',
	'nospam',
	'geo',
	'filebrowser',
	'sms'
);

/**
 * Зарезервированные имена
 */
$MOD_LOCK = array
(
	'options',
	'system',
	'search',
	'upload',
	'team',
	'lang',
	'base',
	'menu',
	'time',
	'apanel',
	'integ',
	'seo',
	'banner',
	'comment',
	'nospam',
	'mail',
	'debug',
	'sms'
);
