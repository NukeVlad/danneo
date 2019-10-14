<?php
/**
 * File:        /admin/mod/down/install/mod.scheme.php
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
	'index'   => array('index' => 1, 'cat' => 1, 'dat' => 1, 'page' => 1),
	'broken'  => array('index' => 1),
	'search'  => array('index' => 1),
	'tags'    => array('index' => 1, 'tag' => 1),
	'comment' => array('index' => 1),
	'rating'  => array('index' => 1),
	'load'    => array('index' => 1),
	'add'     => array('index' => 1, 'save' => 1)
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
	$WORKMOD.'_cat',
	$WORKMOD.'_broken',
	$WORKMOD.'_search',
	$WORKMOD.'_sess',
	$WORKMOD.'_tag',
	$WORKMOD.'_user'
);

/**
 * Массив каталогов с правами на запись
 *
 * @param val | folders with write access
 * @return array
 */
$chmod = array
(
	'/cache/sql/'.$WORKMOD,
	'/up/'.$WORKMOD,
	'/up/'.$WORKMOD.'/file',
	'/up/'.$WORKMOD.'/icon',
	'/up/'.$WORKMOD.'/img'
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
	'/cache/sql/'.$WORKMOD,
	'/mod/'.$WORKMOD,
	'/up/'.$WORKMOD,
	'/template/'.SITE_TEMP.'/mod/'.$WORKMOD
);
