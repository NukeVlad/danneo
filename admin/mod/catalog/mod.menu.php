<?php
/**
 * File:        /admin/mod/catalog/mod.menu.php
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
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=list&amp;ops='.$sess['hash'] => $lang['all_product'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=add&amp;ops='.$sess['hash'] => $lang['add_product'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=cat&amp;ops='.$sess['hash'] => $lang['all_cat'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=catadd&amp;ops='.$sess['hash'] => $lang['all_add_cat'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=makerlist&amp;ops='.$sess['hash'] => $lang['maker'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=filedlist&amp;ops='.$sess['hash'] => $lang['multi_fields'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=weightlist&amp;ops='.$sess['hash'] => $lang['weight'],
			ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=sizelist&amp;ops='.$sess['hash'] => $lang['size']
		);

		if (isset($conf[$WORKMOD]['buy']) AND $conf[$WORKMOD]['buy'] == 'yes')
		{
			$c = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_".$WORKMOD."_order"));
			$cnew = ($c['total'] > 0) ? ' ('.$c['total'].')' : '';

			$block['link'] = array_merge($block['link'], array(
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=taxlist&amp;ops='.$sess['hash'] => $lang['tax'],
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=delivlist&amp;ops='.$sess['hash'] => $lang['delivery'],
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=statlist&amp;ops='.$sess['hash'] => $lang['buy_status'],
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=paylist&amp;ops='.$sess['hash'] => $lang['pay'],
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=ordlist&amp;ops='.$sess['hash'] => $lang['orders'].$cnew,
				ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=curlist&amp;ops='.$sess['hash'] => $lang['currency']
			));
		}

		if (isset($conf[$WORKMOD]['tags']) AND $conf[$WORKMOD]['tags'] == 'yes')
		{
			$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=tag&amp;ops='.$sess['hash'] => $lang['all_tags']));
		}

		if (isset($conf[$WORKMOD]['resact']) AND $conf[$WORKMOD]['resact'] == 'yes')
		{
			if ($AJAX) {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=reviews&amp;ajax=1&amp;ops='.$sess['hash'] => array($lang['response'], ' window-box')));
			} else {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=reviews&amp;ajax=0&amp;ops='.$sess['hash'] => $lang['response']));
			}
		}

		if (isset($conf[$WORKMOD]['resmoder']) AND $conf[$WORKMOD]['resmoder'] == 'yes')
		{
			$cr = $db->fetchrow($db->query("SELECT COUNT(*) AS total FROM ".$basepref."_reviews WHERE file = '".$WORKMOD."' AND active = '0'"));
			$rnew = ($cr['total'] > 0) ? ' ('.$cr['total'].')' : '';
			if ($AJAX) {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=newreviews&amp;ajax=1&amp;ops='.$sess['hash'] => array($lang['response_new'].$rnew, ' window-box')));
			} else {
				$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=newreviews&amp;ajax=0&amp;ops='.$sess['hash'] => $lang['response_new'].$rnew));
			}
		}

		if (isset($conf[$WORKMOD]['buy']) AND $conf[$WORKMOD]['buy'] == 'yes')
		{
			$block['link'] = array_merge($block['link'], array(ADMPATH.'/mod/'.$WORKMOD.'/index.php?dn=agreement&amp;ops='.$sess['hash'] => $lang['agree']));
		}
	}
}
