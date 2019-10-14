<?php
/**
 * File:        /admin/mod/poll/mod.widget.php
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
		// Total, poll
		$total = $db->fetchassoc
					(
						$db->query
							(
								"SELECT COUNT(id) AS total FROM ".$basepref."_".$WORKMOD." WHERE act = 'yes'
								 AND (finish = 0 OR finish > '".NEWTIME."')"
							)
					);

		// Comments
		if (isset($conf[$WORKMOD]['comact']) AND $conf[$WORKMOD]['comact'] == 'yes')
		{
			$comm = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_comment WHERE file = '".$WORKMOD."'"));
			$comment = '<li>'.$lang['comment_total'].' — '.$comm['total'].'</li>';
		}

		// Vals
		$vals = $db->fetchassoc($db->query("SELECT COUNT(valsid) AS total FROM ".$basepref."_".$WORKMOD."_vals"));

		echo '<div class="widget">
					<h3><a class="" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[$WORKMOD].'</a></h3>
					<ul>
						<li>'.$lang['poll'].' — '.$total['total'].'</li>
						<li>'.$lang['poll_voc'].' — '.$vals['total'].'</li>
						'.(isset($comment) ? $comment : '').'
					</ul>
				</div>';
	}
}
