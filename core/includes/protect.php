<?php
/**
 * File:        /core/includes/protect.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $config;

/**
 * Protection from spam
 */
function noprotectspam($save = FALSE, $adduse = TRUE)
{
	global $config, $tm;

	$protect = ($adduse AND defined('USER_LOGGED')) ? TRUE : FALSE;
	if ($save)
	{
		if ($protect)
		{
			if ($config['usercontrol'] == 'yes') $config['control'] = 'no';
			if ($config['usercaptcha'] == 'yes') $config['captcha'] = 'no';
		}
	}
	else
	{
		if ($protect)
		{
			if ($config['usercontrol'] == 'yes') $tm->unmanule['control'] = 'no';
			if ($config['usercaptcha'] == 'yes') $tm->unmanule['captcha'] = 'no';
		}
	}
}

/**
 * Контрольный вопрос, передача
 */
function send_quest()
{
	global $db, $basepref, $config;

	$controlarray = array();

	if ($config['control'] == 'yes')
	{
		if (isset($config['controls']) AND is_array($config['controls']))
		{
			$controlarray = $config['controls'];
		}
		else
		{
			$i = 0;
			$ci = $db->query("SELECT * FROM ".$basepref."_control", $config['cachetime']);
			while ($cm = $db->fetchrow($ci, $config['cache']))
			{
				$controlarray[$i] = array('cid' => $cm['cid'], 'issue' => $cm['issue']);
				$i ++;
			}
		}

		$k = rand(0, sizeof($controlarray) - 1);

		return array(
				'cid'   => $controlarray[$k]['cid'],
				'quest' => $controlarray[$k]['issue']
				);
	}
	return FALSE;
}

/**
 * Контрольный вопрос, проверка
 */
function check_quest($cid, $respon, $ajax = FALSE, $title = TRUE)
{
	global $lang, $tm, $db, $basepref, $config;

	$cid = preparse($cid, THIS_INT);
	if ($config['control'] == 'yes')
	{
		$valid = $db->fetchrow($db->query("SELECT * FROM ".$basepref."_control WHERE cid = '".$cid."'"));
		if (
			$valid['cid'] > 0 AND
			mb_strtolower($valid['response']) != mb_strtolower($respon) OR
			empty($valid)
		) {
			if ($ajax) {
				$tm->error($lang['bad_control'], 1, 1);
			} else {
				$tm->error($lang['bad_control'], $title, 0);
			}
		}
	}
	return;
}
