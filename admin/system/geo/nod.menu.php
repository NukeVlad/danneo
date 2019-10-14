<?php
/**
 * File:        /admin/system/geo/nod.menu.php
 *
 * Управление системой, ГЕО-инструменты
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
	$block['posit'] = 13;
	$block['id'] = $WORKMOD;
	$block['title'] = $lang['menu_geo'];
	$block['link'] = array(
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['list_country'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=add&amp;ops='.$sess['hash'] => $lang['add_country'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=regionadd&amp;ops='.$sess['hash'] => $lang['add_state']
						);
}
