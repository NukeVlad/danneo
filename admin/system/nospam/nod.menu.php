<?php
/**
 * File:        /admin/system/nospam/nod.menu.php
 *
 * Управление системой, Защита от спама
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
if(in_array($WORKMOD, $ADMIN_PERM_ARRAY) || in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
{
	$block['posit'] = 12;
	$block['id'] = $WORKMOD;
	$block['title'] = $lang['menu_nospam'];
	$block['link'] = array(
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['all_set'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=control&amp;ops='.$sess['hash'] => $lang['control_word'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=controladd&amp;ops='.$sess['hash'] => $lang['control_add']
						);
}
