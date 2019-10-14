<?php
/**
 * File:        /admin/mod/pages/mod.menu.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $pl, $IPS, $conf, $sess, $lang, $modposit, $modname, $realmod, $ADMIN_ID, $CHECK_ADMIN, $ADMIN_PERM_ARRAY;

$block = array();
$WORKMOD = basename(__DIR__);
if (in_array($WORKMOD, $realmod))
{
	$pl = (isset($pl) AND isset($IPS[$pl]['mod'])) ? preparse($pl, THIS_INT) : 0;

	if(in_array($WORKMOD, $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		$block['id'] = $WORKMOD;
		$block['posit'] = $modposit[$WORKMOD];
		$block['title'] = $modname[$WORKMOD];
		$block['link'] = array(
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=index&amp;pl='.$pl.'&amp;ops='.$sess['hash'] => $lang['all_set'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;pl='.$pl.'&amp;ops='.$sess['hash'] => $lang['all_page'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=add&amp;pl='.$pl.'&amp;ops='.$sess['hash'] => $lang['add_page'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=mod&amp;pl='.$pl.'&amp;ops='.$sess['hash'] => $lang['platforms_pages']
		);
	}
}
