<?php
/**
 * File:        /admin/mod/catalog/mod.today.php
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
		// New orders
		if ($conf[$WORKMOD]['buy'] == 'yes' AND $conf[$WORKMOD]['request'] == 'no')
		{
			$order = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_order WHERE (public >= '".$altime."')"));
			if ($order['total'] > 0)
			{
				$check = 1;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['orders'].'</td>
							<td>'.$order['total'].'</td>
							<td>
								<a href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=ordlist&amp;atime='.$altime.'&amp;ops='.$sess['hash'].'">
									'.$lang['orders'].'
								</a>
							</td>
						</tr>';
			}
		}

		// New reviews
		if (isset($conf[$WORKMOD]['resmoder']) AND $conf[$WORKMOD]['resmoder'] == 'yes')
		{
			$res = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_reviews WHERE file = '".$WORKMOD."' AND active = '0'"));
			if ($res['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['response_new'].'</td>
							<td>'.$res['total'].'</td>
							<td>
								<a class="window-box" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=newreviews&amp;ops='.$sess['hash'].((defined('ENABLE_AJAX') && ENABLE_AJAX == 'yes') ? '&amp;ajax=1' : '').'">
									'.$lang['response_new'].'
								</a>
							</td>
						</tr>';
			}
		}

		// New reviews
		if (isset($conf[$WORKMOD]['resact']) AND $conf[$WORKMOD]['resact'] == 'yes')
		{
			$res = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_reviews WHERE file = '".$WORKMOD."' AND  (public >= '".$altime."') AND active = '1'"));
			if ($res['total'] > 0)
			{
				$check = true;
				echo '	<tr>
							<td>'.$modname[$WORKMOD].'&nbsp; &#8260; &nbsp;'.$lang['response_new'].'</td>
							<td>'.$res['total'].'</td>
							<td>
								<a class="window-box" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=reviews&amp;atime='.$altime.'&amp;ops='.$sess['hash'].((defined('ENABLE_AJAX') && ENABLE_AJAX == 'yes') ? '&amp;ajax=1' : '').'">
									'.$lang['response_new'].'
								</a>
							</td>
						</tr>';
			}
		}
	}
}
