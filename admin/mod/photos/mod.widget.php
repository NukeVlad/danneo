<?php
/**
 * File:        /admin/mod/photos/mod.widget.php
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
		// Total, hits
		$total = $db->fetchassoc
					(
						$db->query
							(
								"SELECT COUNT(id) AS total, SUM(hits) AS hits FROM ".$basepref."_".$WORKMOD." WHERE act = 'yes'
								 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
								 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
							)
					);

		// Comments
		if (isset($conf[$WORKMOD]['comact']) AND $conf[$WORKMOD]['comact'] == 'yes')
		{
			$comm = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_comment WHERE file = '".$WORKMOD."'"));
			$comment = '<li>'.$lang['comment_total'].' — '.$comm['total'].'</li>';
		}

		// Categories
		$cats = $db->fetchassoc($db->query("SELECT COUNT(catid) AS total FROM ".$basepref."_".$WORKMOD."_cat"));

		echo '<div class="widget">
					<h3><a class="" href="'.ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'].'">'.$modname[$WORKMOD].'</a></h3>
					<ul>
						<li>'.$lang['all_cats'].' — '.$cats['total'].'</li>
						<li>'.$lang['all_photos'].' — '.$total['total'].'</li>
						<li>'.$lang['all_hits'].' — '.$total['hits'].'</li>
						'.(isset($comment) ? $comment : '').'
					</ul>
				</div>';
	}
}
