<?php
/**
 * File:        /admin/mod/user/mod.widget.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $db, $basepref, $conf, $sess, $tm, $realmod, $modposit, $modname, $lang, $ADMIN_PERM_ARRAY, $ADMIN_ID, $CHECK_ADMIN, $AJAX;

$WORKMOD = basename(__DIR__);

if (in_array($WORKMOD, $realmod))
{
	if (in_array($WORKMOD, $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		// Users
		$total = $db->fetchassoc
					(
						$db->query
							(
								"SELECT COUNT(userid) AS total FROM ".$basepref."_".$WORKMOD." WHERE active = '1'"
							)
					);

		// Groups
		$group = $db->fetchassoc($db->query("SELECT COUNT(gid) AS total FROM ".$basepref."_".$WORKMOD."_group"));

		echo '<div class="widget">
					<h3><a class="" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[$WORKMOD].'</a></h3>
					<ul>
						<li>'.$lang['user_total'].' — '.$total['total'].'</li>
						<li>'.$lang['group_total'].' — '.$group['total'].'</li>
					</ul>
				</div>';
	}
}
