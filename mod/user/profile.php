<?php
/**
 * File:        /mod/user/profile.php
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
global $userapi, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * id
 */
$id = preparse($id, THIS_INT);

/**
 * Редирект
 */
if ( ! defined('REGTYPE') OR $id == 0)
{
	redirect(SITE_URL);
}

/**
 * Профиль
 */
$userapi->profile($id);
