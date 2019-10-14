<?php
/**
 * File:        /admin/mod/user/mod.today.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

global $db, $basepref, $conf, $lang, $sess, $realmod, $modposit, $modname, $ADMIN_PERM_ARRAY, $ADMIN_ID, $CHECK_ADMIN, $AJAX;

$WORKMOD = basename(__DIR__);

if (in_array($WORKMOD, $realmod))
{
	if (isset($conf['user']) AND (in_array('user', $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid'])))
	{
		// New users
		if ($conf['user']['regtype'] == 'yes')
		{
			$count = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_user WHERE (regdate >= '".$altime."')"));
			if ($count['total'] > 0)
			{
				$check = 1;
				echo '	<tr>
							<td>'.$lang['user_new'].'</td>
							<td>'.$count['total'].'</td>
							<td>
								<a href="'.ADMPATH.'/mod/user/index.php?dn=list&amp;atime='.$altime.'&amp;ops='.$sess['hash'].'">
									'.$lang['user_new'].'
								</a>
							</td>
						</tr>';
			}
		}
	}
}
