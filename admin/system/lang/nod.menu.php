<?php
/**
 * File:        /admin/system/lang/nod.menu.php
 *
 * Управление системой, Языковой менеджер
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $conf, $sess, $lang, $ADMIN_PERM_ARRAY, $CHECK_ADMIN, $ADMIN_ID;

$block = array();
$WORKMOD = basename(__DIR__);
if(in_array($WORKMOD, $ADMIN_PERM_ARRAY) || in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
{
	$block['posit'] = 7;
	$block['id'] = $WORKMOD;
	$block['title'] = $lang[$WORKMOD];
	$block['link'] = array(
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['all_set'],
						ADMPATH.'/system/'.$WORKMOD.'/index.php?dn=langadd&amp;ops='.$sess['hash'] => $lang['lang_add'],
						'javascript:$.langbrowser(\''.$sess['hash'].'\')' => $lang['lang_brow']
						);
}
