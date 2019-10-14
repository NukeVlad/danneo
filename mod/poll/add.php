<?php
/**
 * File:        /mod/poll/add.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__));

/**
 * Глобальные
 */
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $poll_block,
		$global, $id, $vid, $ajax, $tarray;

$id = preparse($id, THIS_INT);
$vid = preparse($vid, THIS_INT);
$ajax = preparse($ajax, THIS_INT);
$ajax = ($poll_block) ?  0 : $ajax;

/**
 * Заголовки
 */
if ($ajax == 1)
{
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Content-Type: text/plain; charset=".$config['langcharset']."");
}

/**
 * Свой TITLE
 */
if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
{
	define('CUSTOM', $config['mod'][WORKMOD]['custom'].$seopage);
}

/**
 * Мета данные
 */
$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript'].$seopage) : '';

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $global['insert']['breadcrumb'] = $global['modname'];

/**
 * Данные
 */
$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND finish > '".NEWTIME."' AND id = '".$id."'");
$item = $db->fetchrow($inq);

/**
 * Страница не существует
 */
if ($db->numrows($inq) == 0)
{
	$tm->error($lang['noexit_page'], 0, $ajax);
}

/**
 * ajax
 */
$ajax = ($item['ajax'] == 'yes' AND $ajax == 1) ? 1 : 0;

/**
 * Ограничение доступа
 */
if (isset($config['mod']['user']))
{
	if($item['acc'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($item['groups']))
		{
			$group = Json::decode($item['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}
}

/**
 * Проверка IP-адреса
 * Анонимный прокси-сервер или приватный IP-адрес исключаются
 */
if ( ! defined('CORRECT_REMOTE_ADDRS'))
{
	$tm->error($lang['comment_ip_bad'], 0, $ajax);
}

/**
 * Проверка пользователя и IP-адреса
 */
if ($item['acc'] == 'user' AND defined('USER_LOGGED'))
{
	$voteid = $db->fetchrow
				(
					$db->query
					(
						"SELECT SUM(voteid) AS total FROM ".$basepref."_".WORKMOD."_vote
						 WHERE id = '".$item['id']."'
						 AND userid = '".$usermain['userid']."'"
					)
				);
}
else
{
	$voteid = $db->fetchrow
				(
					$db->query
					(
						"SELECT SUM(voteid) AS total FROM ".$basepref."_".WORKMOD."_vote
						 WHERE id = '".$item['id']."'
						 AND voteip = '".REMOTE_ADDRS."'"
					)
				);
}

/**
 * Ошибка, уже голосовали
 */
if ($voteid['total'] > 0)
{
	$tm->error($lang['poll_dle'], 0, $ajax);
}

/**
 * Ошибка, вопрос не выбран
 */
$valsid = $db->numrows($db->query("SELECT valsid FROM ".$basepref."_".WORKMOD."_vals WHERE valsid = '".$vid."'"));
if ($valsid == 0)
{
	$tm->error($lang['poll_novals'], 0, ($poll_block) ?  0 : $ajax, 1, 0);
}

/**
 * Сохраняем данные
 */
$in = $db->query
		(
			"INSERT INTO ".$basepref."_".WORKMOD."_vote VALUES (
			 NULL,
			 '".$item['id']."',
			 '".$usermain['userid']."',
			 '".NEWTIME."',
			 '".REMOTE_ADDRS."'
			 )"
		);

/**
 * Данные по опросу
 */
if ($in)
{
	$db->query("UPDATE ".$basepref."_".WORKMOD."_vals SET vals_voices = vals_voices + 1 WHERE valsid = '".$vid."'");

	if ($ajax == 0)
	{
		$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
		$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=page&amp;id='.$id.$ins['cpu']);

		$message = $lang['rating_senks'].' <a href="'.SITE_URL.'">'.$config['site'].'</a>.';

		$tm->redirectprint($lang['inf_mess'], $config['redirtime'], $message, $ins['url']);
	}
	else
	{
		$view = $tm->parsein($tm->create('mod/'.WORKMOD.'/view'));

		$count = $db->fetchrow($db->query("SELECT SUM(vals_voices) AS total FROM ".$basepref."_".WORKMOD."_vals WHERE id = '".$item['id']."'"));
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_vals WHERE id = '".$item['id']."' ORDER BY posit");

		$newvoices = null;
		while ($vitem = $db->fetchrow($inq))
		{
			$voices = preparse($vitem['vals_voices'], THIS_INT);
			$percent = ($voices > 0) ? (int)(($voices * 100) / $count['total']) : $voices;
			$line = ($voices > 0) ? $percent : 1;
			$newvoices .= $tm->parse(array
							(
								'radio'     => '',
								'val_name'  => $api->siteuni($vitem['vals_title']),
								'val_voc'   => $voices.' '.$lang['poll_vocshort'],
								'val_line'  => $line.'%',
								'val_color' => '#'.$vitem['vals_color'],
								'val_perc'  => $percent
							),
							$tm->manuale['percent']);
		}

		echo "<!--pollok ".NEWTIME."-->";

		// Подзаголовок
		$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

		$tm->parseprint(array
			(
				'title'   => $api->siteuni($item['title']),
				'subtitle'=> $ins['subtitle'],
				'percent' => $newvoices,
				'desc'    => $api->siteuni($item['decs']),
				'message' => $lang['rating_senks'],
				// nocomment
				'comment' => '',
				'comform' => '',
				'ajaxbox' => ''
			),
			$view);
	}
}
else
{
	$tm->error($lang['system_error'], 0, $ajax);
}
