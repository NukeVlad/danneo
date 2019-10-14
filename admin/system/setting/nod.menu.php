<?php
/**
 * File:        /admin/system/setting/nod.menu.php
 *
 * Управление системой, Настройки сайта
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $conf, $sess, $lang, $ADMIN_PERM_ARRAY, $ADMIN_ID, $CHECK_ADMIN;

$block = array();
$WORKMOD = basename(__DIR__);
if (in_array($WORKMOD, $ADMIN_PERM_ARRAY) || in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
{
	$block['posit'] = 2;
	$block['id'] = $WORKMOD;
	$block['title'] = $lang['optset_system'];
	$block['link'] = array(
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['opt_set'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=setmod&amp;ops='.$sess['hash'] => $lang['menu_seditor'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=debug&amp;ops='.$sess['hash'] => $lang['debug']
						);
}
