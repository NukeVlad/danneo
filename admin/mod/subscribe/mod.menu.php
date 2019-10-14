<?php
/**
 * File:        /admin/mod/subscribe/mod.menu.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $conf, $sess, $lang, $modposit, $modname, $realmod, $CHECK_ADMIN, $ADMIN_PERM_ARRAY, $ADMIN_ID;

$block = array();
$WORKMOD = basename(__DIR__);

if (in_array($WORKMOD, $realmod))
{
	if (in_array($WORKMOD, $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		$block['id'] = $WORKMOD;
		$block['posit'] = $modposit[$WORKMOD];
		$block['title'] = $modname[$WORKMOD];
		$block['link'] = array(
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['subscribe_menu_all'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=start&amp;ops='.$sess['hash'] => $lang['subscribe_menu_create'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=export&amp;ops='.$sess['hash'] => $lang['subscribe_menu_export'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=users&amp;ops='.$sess['hash'] => $lang['subscribe_mail_for']
		);
	}
}
