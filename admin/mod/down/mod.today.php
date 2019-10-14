<?php
/**
 * File:        /admin/mod/down/mod.today.php
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
		// Added
		if ($conf[$WORKMOD]['addit'] == 'yes')
		{
			$add = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_user WHERE (public >= '".$altime."')"));
			if ($add['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['added'].'</td>
							<td>'.$add['total'].'</td>
							<td>
								<a href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=new&amp;ops='.$sess['hash'].'">
									'.$lang['all_new'].'
								</a>
							</td>
						</tr>';
			}
		}

		// Broken file
		if ($conf[$WORKMOD]['broken'] == 'yes')
		{
			$brok = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_broken WHERE (broktime  >= '".$altime."')"));
			if ($brok['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['down_no_access'].'</td>
							<td>'.$brok['total'].'</td>
							<td>
								<a href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=brokenlist&amp;ops='.$sess['hash'].'">
									'.$lang['down_no_access'].'
								</a>
							</td>
						</tr>';
			}
		}

		// New comments
		if ($conf[$WORKMOD]['comact'] == 'yes')
		{
			$com = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_comment WHERE file = '".$WORKMOD."' AND  (ctime >= '".$altime."')"));
			if ($com['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['comment_last'].'</td>
							<td>'.$com['total'].'</td>
							<td>
								<a class="window-box block" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=comment&amp;atime='.$altime.'&amp;ops='.$sess['hash'].((defined('ENABLE_AJAX') && ENABLE_AJAX == 'yes') ? '&amp;ajax=1' : '').'" title="'.$lang['comment_last'].'">
									<img src="template/images/prev.png" alt="'.$lang['comment_last'].'" />
								</a>
							</td>
						</tr>';
			}
		}
	}
}
