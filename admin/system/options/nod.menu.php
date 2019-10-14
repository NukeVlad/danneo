<?php
/**
 * File:        /admin/system/options/nod.menu.php
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
	$block['posit'] = 1;
	$block['id'] = $WORKMOD;
	$block['title'] = $lang['optset_site'];
	$block['link'] = array(
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['opt_set'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=mod&amp;ops='.$sess['hash'] => $lang['opt_manage_mod'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=tempmod&amp;ops='.$sess['hash'] => $lang['mod_template'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=tempedit&amp;ops='.$sess['hash'] => $lang['site_temp'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=upload&amp;ops='.$sess['hash'] => $lang['image_upload'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=search&amp;ops='.$sess['hash'] => $lang['set_search'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=mail&amp;ops='.$sess['hash'] => $lang['mail'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=time&amp;ops='.$sess['hash'] => $lang['opt_time_cookie']
						);
}
