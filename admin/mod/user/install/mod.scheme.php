<?php
/**
 * File:        /admin/mod/user/install/mod.scheme.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Рабочий мод
 */
$WORKMOD = basename(dirname(__DIR__));

/**
 * Массив меток мода
 *
 * @param key - file
 * @param val - labels array
 * @return setting for blocks by default, when mod install
 */
$label = array
(
	'index'    => array('index' => 1, 'repassw' => 1, 'remail' => 1, 'redata' => 1),
	'login'    => array('index' => 1, 'check ' => 1, 'lost ' => 1, 'send ' => 1, 'relost ' => 1),
	'register' => array('index' => 1, 'check' => 1, 'act' => 1),
	'lost'     => array('index' => 1, 'send' => 1, 'relost' => 1),
	'profile'  => array('index' => 1)
);

/**
 * Массив имен таблиц мода
 *
 * @param val | tables of module
 * @return array
 */
$tables = array
(
	$WORKMOD,
	$WORKMOD.'_field',
	$WORKMOD.'_group'
);

/**
 * Массив каталогов с правами на запись
 *
 * @param val | folders and files
 * @return array
 */
$chmod = array
(
	'/up/avatar'
);

/**
 * Список каталогов и файлов мода
 *
 * @param val | tables of module
 * @return array
 */
$filelist = array
(
	'/'.APANEL.'/mod/'.$WORKMOD,
	'/core/includes/users.php',
	'/core/userbase',
	'/mod/'.$WORKMOD,
	'/up/avatar',
	'/template/'.SITE_TEMP.'/mod/'.$WORKMOD
);
