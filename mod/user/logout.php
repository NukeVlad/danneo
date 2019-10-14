<?php
/**
 * File:        /mod/user/logout.php
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
global $userapi;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Регистрация отключена
 */
if ( ! defined('REGTYPE'))
{
	redirect(SITE_URL);
}

/**
 * Ссылка редиректа
 */
$locurl = (defined('SEOURL')) ? SITE_URL.'/'.WORKMOD.'/' : SITE_URL.'/index.php?dn='.WORKMOD;

/**
 * Выход, редирект
 */
if (defined('USER_LOGGED'))
{
	$userapi->logout();
	redirect(defined('HTTP_REFERERS') ? HTTP_REFERERS : $locurl);
}
else
{
	redirect($locurl);
}

