<?php
/**
 * File:        /admin/mod/down/mod.menu.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $db, $basepref, $conf, $sess, $realmod, $modposit, $modname, $lang, $ADMIN_PERM_ARRAY, $ADMIN_ID, $CHECK_ADMIN, $AJAX;

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
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['all_set'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'] => $lang['down_all'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=add&amp;ops='.$sess['hash'] => $lang['down_add'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=cat&amp;ops='.$sess['hash'] => $lang['all_cat'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=catadd&amp;ops='.$sess['hash'] => $lang['all_add_cat']
		);

		$tab_user = $db->tables($WORKMOD."_user");
		if ($tab_user AND isset($conf[$WORKMOD]['addit']) AND $conf[$WORKMOD]['addit'] == 'yes')
		{
			$du = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_user"));
			$dnew = ($du['total'] > 0) ? ' ('.$du['total'].')' : '';
			$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=new&amp;ops='.$sess['hash'] => $lang['down_added'].$dnew));
		}

		if(isset($conf[$WORKMOD]['broken']) AND $conf[$WORKMOD]['broken'] == 'yes')
		{
			$br = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_broken"));
			$dbrok = ($br['total'] > 0) ? ' ('.$br['total'].')' : '';
			$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=brokenlist&amp;ops='.$sess['hash'] => $lang['down_no_access'].$dbrok));
		}

		if (isset($conf[$WORKMOD]['comact']) AND $conf[$WORKMOD]['comact'] == 'yes')
		{
			if ($AJAX) {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=comment&amp;ajax=1&amp;ops='.$sess['hash'] => array($lang['menu_comment'], ' window-box')));
			} else {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=comment&amp;ajax=&amp;ops='.$sess['hash'] => $lang['menu_comment']));
			}
		}

		if (isset($conf[$WORKMOD]['tags']) AND $conf[$WORKMOD]['tags'] == 'yes')
		{
			$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=tag&amp;ops='.$sess['hash'] => $lang['all_tags']));
		}
	}
}
