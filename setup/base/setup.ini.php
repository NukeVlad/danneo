<?php
/**
 * File:        setup/base/danneo.ini.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 *  Product
 */
$product = 'Danneo CMS <span>v.1.5.5</span>';
$copyright = '<a href="http://danneo.ru" target="_blank">Danneo CMS</a> <i>&copy;</i> 2005 - '.date('Y');

/**
 * Path setting
 */
$config_path = DNBASE."/core/config.php";

/**
 * Language
 */
$language = 'ru';

/**
 * Charset bd
 */
$charsebd = "utf8";

/**
 * Write list
 */
$write_list = array
	(
		'core/config.php',
		'cache/',
		'up/'
	);

/**
 * Write, worker arrays
 */
$waring = $write_list;
$_write['write'] = $write_list;

/**
 * Step Setup
 */
$_step[1] = array('tpl'=>'setup.1','file'=>'','action'=>'server');
$_step[2] = array('tpl'=>'setup.2','file'=>'','action'=>'choice');
$_step[3] = array('tpl'=>'setup.3','file'=>'','action'=>'write');
$_step[4] = array('tpl'=>'setup.4','file'=>'','action'=>'copy');
$_step[5] = array('tpl'=>'setup.5','file'=>'','action'=>'connect');
$_step[6] = array('tpl'=>'setup.6','file'=>'sql/table.sql','action'=>'mysql','mysql'=>';');
$_step[7] = array('tpl'=>'setup.7','file'=>'sql/lang.sql','action'=>'mysql','mysql'=>';');
$_step[8] = array('tpl'=>'setup.8','file'=>'sql/insert.sql','action'=>'mysql','mysql'=>PHP_EOL);
$_step[9] = array('tpl'=>'setup.9','file'=>'','action'=>'setting');
$_step[10] = array('tpl'=>'setup.10','file'=>'','action'=>'update');

/**
 * Step Update
 */
$_up = array();
