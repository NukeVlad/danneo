<?php
/**
 * File:        /admin/mod/catalog/install/mod.scheme.php
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
	'index'     => array('index' => 1, 'cat' => 1, 'view' => 1, 'page' => 1),
	'basket'    => array('index' => 1, 'personal' => 1, 'add' => 1),
	'order'     => array('index' => 1, 'del' => 1, 'delive' => 1, 'checkout' => 1, 'custom' => 1, 'payment' => 1, 'confirm' => 1, 'check' => 1, 'edit' => 1, 'save' => 1),
	'search'    => array('index' => 1),
	'add'       => array('index' => 1, 'add' => 1),
	'tags'      => array('index' => 1, 'tag' => 1),
	'history'   => array('index' => 1),
	'maker'     => array('index' => 1, 'page' => 1),
	'agreement' => array('index' => 1),
	'reviews'   => array('index' => 1),
	'rating'    => array('index' => 1)
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
	$WORKMOD.'_basket',
	$WORKMOD.'_cat',
	$WORKMOD.'_delivery',
	$WORKMOD.'_maker',
	$WORKMOD.'_option',
	$WORKMOD.'_option_value',
	$WORKMOD.'_order',
	$WORKMOD.'_payment',
	$WORKMOD.'_product_option',
	$WORKMOD.'_search',
	$WORKMOD.'_tag'
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
	'/up/'.$WORKMOD.'/cat',
	'/up/'.$WORKMOD.'/file',
	'/up/'.$WORKMOD.'/icon',
	'/up/'.$WORKMOD.'/maker',
	'/up/'.$WORKMOD.'/product',
	'/up/'.$WORKMOD.'/video',
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
