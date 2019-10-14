<?php
/**
 * File:        /block/b-Poll.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $api, $ro, $lang, $config;

$bc = null;
$lang['block_poll'] = isset($lang['block_poll']) ? $lang['block_poll'] : 'Poll';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_poll'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'poll',
		'default'	=>	'poll'
	),
	'sort' => array(
		'lang'    => 'all_sorting',
		'form'    => 'select',
		'value'   => array('last' => 'all_last', 'rand' => 'all_random'),
		'default' => 'last'
	)
);

if (defined('SETTING'))
{
	return $bs;
}

/**
 * Получаем настройки
 */
if (
	isset($config['bsarray']) AND
	is_array($config['bsarray']) AND
	isset($config['mod'][$config['bsarray']['mod']])
) {
	$ins = array();
	$bs = $config['bsarray'];

	if ($bs['sort'] == 'rand')
	{
		$poll_count = $db->fetchrow($db->query("SELECT count(*) FROM ".$basepref."_".$bs['mod']));
		$poll_rand = mt_rand(0, $poll_count[0] - 1);

		$inq = $db->query
				(
					"SELECT id, title, decs, ajax, acc FROM ".$basepref."_".$bs['mod']."
					 WHERE act = 'yes' AND finish > '".NEWTIME."' ORDER BY id LIMIT ".$poll_rand.", 1"
				);
	}
	else {
		$inq = $db->query
				(
					"SELECT id, title, decs, ajax, acc FROM ".$basepref."_".$bs['mod']."
					 WHERE act = 'yes' AND finish > '".NEWTIME."' ORDER BY id DESC LIMIT 1"
				);
	}

	if (isset($inq) AND $db->numrows($inq) > 0)
	{
		$item = $db->fetchrow($inq);

		$ins['view'] = 0;
		$ins['message'] = '';

		$tm->unmanule['user'] = (defined('USER_LOGGED')) ? 'yes' : 'no';

		if ($item['acc'] == 'user')
		{
			if (defined('USER_LOGGED'))
			{
				$vote = $db->fetchrow($db->query("SELECT SUM(voteid) AS total FROM ".$basepref."_".$bs['mod']."_vote WHERE id='".$item['id']."' AND userid = '".$usermain['userid']."'"));
				$ins['view'] = ($vote['total'] == 0) ? 1 : 0;
				$ins['message'] = ($vote['total'] > 0) ? $lang['poll_dle'] : '';
			}
		}
		else
		{
			$vote = $db->fetchrow($db->query("SELECT SUM(voteid) AS total FROM ".$basepref."_".$bs['mod']."_vote WHERE id = '".$item['id']."' AND voteip = '".REMOTE_ADDRS."'"));
			$ins['view'] = ($vote['total'] == 0) ? 1 : 0;
			$ins['message'] = ($vote['total'] > 0) ? $lang['poll_dle'] : '';
		}

		$tm->unmanule['ajax'] = ($config['ajax'] == 'yes' AND $item['ajax'] == 'yes') ? 'yes' : 'no';

		$ins['template'] = ($ins['view'] == 0) ? $tm->parsein($tm->create('mod/'.$bs['mod'].'/block.view')) : $tm->parsein($tm->create('mod/'.$bs['mod'].'/block.form'));

		$ins['count'] = $db->fetchrow($db->query("SELECT SUM(vals_voices) AS total FROM ".$basepref."_".$bs['mod']."_vals WHERE id = '".$item['id']."'"));
		$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_vals WHERE id = '".$item['id']."' ORDER BY posit");
		$ins['voices'] = '';

		while ($vitem = $db->fetchrow($inq))
		{
			$voices = preparse($vitem['vals_voices'], THIS_INT);
			$percent = ($voices > 0) ? (int)(($voices * 100) / $ins['count']['total']) : $voices;
			$line = ($voices > 0) ? $percent : 1;
			$radio = ($ins['view'] == 1) ? '<input type="radio" name="vid" value="'.$vitem['valsid'].'" />' : '';
			$ins['voices'].= $tm->parse(array
								(
									'val_name'  => $api->siteuni($vitem['vals_title']),
									'radio'     => $radio,
									'val_voc'   => $voices.' '.$lang['poll_vocshort'],
									'val_line'  => $line.'%',
									'val_color' => '#'.$vitem['vals_color'],
									'val_perc'  => $percent
								),
								$tm->manuale['percent']);
		}

		$button = ($ins['view'] == 0) ? '' : $lang['poll_button'];
		$voteajax = ($config['ajax'] == 'yes' AND $item['ajax'] == 'yes') ? 1 : 0;
		$votejs = ($voteajax == 1) ? 'true' : 'false';

		$bc.= $tm->parse(array
				(
					'post_url'  => $ro->seo('index.php?dn='.$bs['mod']),
					'vote_ajax' => $votejs,
					'val'       => $voteajax,
					'title'     => $api->siteuni($item['title']),
					'percent'   => $ins['voices'],
					'desc'      => $api->siteuni($item['decs']),
					'message'   => $ins['message'],
					'all_sends' => $lang['all_sends'],
					'button'    => $button,
					'id'        => $item['id']
				),
				$ins['template']);
	}
	else
	{
		$bc.= $lang['data_not'];
	}
}
else
{
	$bc.= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
