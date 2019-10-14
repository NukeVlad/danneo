<?php
/**
 * File:        /admin/mod/faq/mod.today.php
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
	if (in_array($WORKMOD, $ADMIN_PERM_ARRAY) OR in_array($ADMIN_ID, $CHECK_ADMIN['admid']))
	{
		// New questions
		if ($conf[$WORKMOD]['addit'] == 'yes')
		{
			$faq = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_new WHERE (public >= '".$altime."')"));
			if ($faq['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['new_faq'].'</td>
							<td>'.$faq['total'].'</td>
							<td>
								<a class="block" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=new&amp;ops='.$sess['hash'].'">
									'.$lang['all_new'].'
								</a>
							</td>
						</tr>';
			}
		}
	}
}
