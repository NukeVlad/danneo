<?php
/**
 * File:        /admin/mod/media/mod.menu.php
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
	if(in_array($WORKMOD, $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		$block['id'] = $WORKMOD;
		$block['posit'] = $modposit[$WORKMOD];
		$block['title'] = $modname[$WORKMOD];
		$block['link'] = array(
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=index&amp;ops='.$sess['hash'] => $lang['all_set'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'] => $lang['media'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=listadd&amp;ops='.$sess['hash'] => $lang['add_media'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=imgadd&amp;ops='.$sess['hash'] => $lang['add_photo_video'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=upmass&amp;ops='.$sess['hash'] => $lang['add_mass']
		);
	}
}
