<?php
/**
 * File:        /admin/mod/user/mod.menu.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $conf, $sess, $lang, $realmod, $modposit, $modname, $ADMIN_PERM_ARRAY, $ADMIN_ID, $CHECK_ADMIN;

$block = array();
$WORKMOD = basename(__DIR__);

if (in_array($WORKMOD, $realmod))
{
	if (isset($conf['user']) AND (in_array('user', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])))
	{
		$block['id'] = $WORKMOD;
		$block['posit'] = $modposit[$WORKMOD];
		$block['title'] = $modname[$WORKMOD];
		$block['link'] = array(
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['all_set'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'] => $lang['user_list'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=register&amp;ops='.$sess['hash'] => $lang['register_user'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=field&amp;ops='.$sess['hash'] => $lang['addit_fields'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=fieldadd&amp;ops='.$sess['hash'] => $lang['add_field']
		);

		if (isset($conf['user']['groupact']) AND $conf['user']['groupact'] == 'yes')
		{
			$block['links'] = array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=group&amp;ops='.$sess['hash'] => $lang['all_groups']);
			$block['link'] = array_merge($block['link'], $block['links']);
		}

		$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=integ&amp;ops='.$sess['hash'] => $lang['opt_manager_forum']));
	}
}
