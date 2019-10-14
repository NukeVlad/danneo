<?php
/**
 * File:        /mod/home/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные
 */
global $tm, $config, $global, $api, $ro;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = '';
$global['insert']['breadcrumb'] = '';

/**
 * Вывод
 */
$tm->header();
$tm->footer();
