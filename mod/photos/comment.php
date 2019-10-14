<?php
/**
 * File:        /mod/photos/comment.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные
 */
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $userapi, $global,
		$id, $cid, $respon, $comname, $comtitle, $comtext, $ajax, $captcha, $ct;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * ajax
 */
$ajax = ($config['ajax'] == 'yes' AND preparse($ajax, THIS_INT) > 0) ? 1 : 0;

/**
 * Ошибка, комментарии отключены
 */
if ($conf['comact'] == 'no')
{
	$tm->norightprint();
}

/**
 * Время, ajax
 */
$ct = preparse($ct, THIS_INT);
$ct = ($ct > (NEWTIME - $config['comtime']) AND $ct < NEWTIME) ? $ct : NEWTIME - 1;

/**
 * Заголовки
 */
if ($ajax == 1)
{
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Type: text/plain; charset='.$config['langcharset'].'');
}

/**
 * Отключить проверки, для пользователей
 */
noprotectspam(1);

/**
 * Проверка секретного кода
 */
$captcha = (preparse($captcha, THIS_NUMBER) == 1) ? '' : preparse($captcha, THIS_TRIM, 0, 5);
if ($config['captcha'] == 'yes')
{
	if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
	{
		$tm->error($lang['bad_captcha'], 1, 1);
	}
}

/**
 * Проверка контрольного вопроса
 */
check_quest($cid, $respon, 1);

/**
 * Данные страницы
 */
$id = preparse($id, THIS_INT);
$valid = $db->query
			(
				"SELECT id, catid, cpu, title, acc, groups, comments FROM ".$basepref."_".WORKMOD."
				 WHERE id = '".$id."' AND act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')"
			);

$item = $db->fetchassoc($valid);

/**
 * Ошибка, страницы не существует
 */
if ($db->numrows($valid) == 0)
{
	$tm->error($lang['noexit_page'], 1, 1);
}

/**
 * Категории
 */
$area = array();
$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
while ($c = $db->fetchassoc($inq, $config['cache']))
{
	$area[$c['catid']] = $c;
}
if (isset($area[$item['catid']]))
{
	$obj = $area[$item['catid']];
}
else
{
	$obj = array
		(
			'catid'		=> '',
			'parentid'	=> '',
			'catcpu'	=> '',
			'catname'	=> '',
			'icon'		=> '',
			'access'	=> '',
			'groups'	=> ''
		);
}

/**
 * Ограничение доступа
 */
if (isset($config['mod']['user']))
{
	if (
		$item['acc'] == 'user' OR
		$obj['access'] == 'user' OR
		$conf['comwho'] == 'user'
	) {
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint(0, 1, $ajax);
		}
		if (defined('GROUP_ACT') AND ! empty($item['groups']))
		{
			$group = Json::decode($item['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint(NULL, 1 , $ajax, 1);
			}
		}
		if (defined('GROUP_ACT') AND ! empty($obj['groups']))
		{
			$group = Json::decode($obj['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint(NULL, 1 , $ajax, 1);
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
	$tm->error($lang['comment_ip_bad'], 1, 1);
}

/**
 * Запрещенные имена
 */
if (isset($config['user']))
{
	$blogin = explode('|', $config['user']['badname']);
	if ($userapi->checklogin($comname) == 0 OR (in_array($comname, $blogin)))
	{
		$boxtext = str_replace(
					array('{maxname}', '{minname}'),
					array($config['user']['maxname'], $config['user']['minname']),
					$lang['bad_login']
				);
		$tm->error($boxtext, 1, 1);
	}
}

/**
 * Проверка текста комментария
 */
$pretext = preparse(deltags($comtext), THIS_STRLEN);
if ($pretext < $config['commin'] OR $pretext > $config['comsize'])
{
	$tm->error($lang['comment_empty'], 1, 1);
}

/**
 * Проверка на флуд
 */
$checktime = $db->fetchassoc
				(
					$db->query
					(
						"SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE (
						 file = '".WORKMOD."'
						 AND id = '".$item['id']."'
						 AND ctime >= '".(NEWTIME - $config['comtime'])."'
						 AND cip = '".REMOTE_ADDRS."'
						 )"
					)
				);

if ($checktime['total'] > 0)
{
	$tm->error($lang['comment_flood'], 1, 1);
}

/**
 * Добавляем комментарий
 */
$comtext = ($conf['comeditor'] == 'yes') ? commentparse($comtext) : deltags(commentparse($comtext));
$comname = (defined('USER_LOGGED')) ? $usermain['uname'] : mb_substr(deltags($comname), 0, 50);

$in = $db->query
		(
			"INSERT INTO ".$basepref."_comment VALUES (
			 NULL,
			 '".WORKMOD."',
			 '".$item['id']."',
			 '".$usermain['userid']."',
			 '".NEWTIME."',
			 '".$db->escape($comname)."',
			 '".$db->escape($item['title'])."',
			 '".$db->escape($comtext)."',
			 '".REMOTE_ADDRS."'
			 )"
		);

/**
 * Если успешно
 */
if ($in)
{
	$ins = $associat = array();
	$db->query("UPDATE ".$basepref."_".WORKMOD." SET comments = comments + 1 WHERE id = '".$item['id']."'");

	if ($ajax == 0)
	{
		$ins['pc'] = null;
		if ($config['comsort'] == 'asc')
		{
			$count = $db->fetchassoc($db->query("SELECT COUNT(comid) AS total FROM ".$basepref."_comment WHERE file = '".WORKMOD."' AND id = '".$item['id']."'"));
			$nums = ceil($count['total'] / $config['compage']);
			$ins['pc'] = ($nums > 1) ? '&amp;p='.$nums : '';
		}

		// cpu
		$ins['cpu']    = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
		$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

		// url
		$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'].$ins['pc']);

		// Редирект
		$tm->redirectprint($lang['comment_added_title'], $config['redirtime'], $lang['comment_added'], $ins['url']);
	}
	else
	{
		$tm->unmanule['title'] = 'no';

		/**
		 * Вложенные шаблоны
		 */
		$tm->manuale = array
			(
				'user' => null,
				'guest' => null,
				'author' => null
			);

		// Шаблон
		$ins['comment'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/comment.standart'));

		$com = $db->query
				(
					"SELECT comid, ctime, userid, cname, ctitle, ctext FROM ".$basepref."_comment
					 WHERE file = '".WORKMOD."' AND id = '".$item['id']."' AND ctime > '".$ct."'
					 ORDER BY ctime ASC LIMIT ".$config['compage']
				);

		// Смайлы
		$smiliearray = array();
		if (isset($config['smilie']) AND is_array($config['smilie']))
		{
			$smiliearray = $config['smilie'];
		}
		else
		{
			$i = 0;
			$si = $db->query("SELECT * FROM ".$basepref."_smilie ORDER BY posit", $config['cachetime']);
			while ($sm = $db->fetchassoc($si, $config['cache']))
			{
				$smiliearray[$i] = array
										(
											'code' => $sm['smcode'],
											'alt'  => $sm['smalt'],
											'img'  => $sm['smimg']
										);
				$i ++;
			}
		}

		$in = $work = array();
		while ($itemc = $db->fetchassoc($com))
		{
			$work[$itemc['comid']] = $itemc;
			if($itemc['userid'] > 0)
			{
				$in[$itemc['userid']] = $itemc['userid'];
			}
		}

		if (isset($config['user']))
		{
			$associat = $userapi->associat(implode(',', $in));
		}

		$ins['output'] = null;
		foreach ($work as $k => $comitem)
		{
			$text = ($conf['comeditor'] == 'yes') ? commentout($api->siteuni($comitem['ctext'])) : deltags($api->siteuni($comitem['ctext']));
			$text = ($conf['comsmilie'] == 'yes' AND is_array($smiliearray)) ? smilieparse($text, $smiliearray) : smilieparse($text, $smiliearray, FALSE);

			$guest = $user = '';
			if ($comitem['userid'] > 0 AND isset($associat[$comitem['userid']]))
			{
				if (isset($userapi->data['linkprofile']) AND $userapi->data['linkprofile']) {
					$link_profile = $ro->seo($userapi->data['linkprofile'].$comitem['userid']);
				} else {
					$link_profile = FULL_REQUEST_URI;
				}
				$author = $tm->parse(array
							(
								'name'  => $api->siteuni($comitem['cname']),
								'link'  => $link_profile,
								'title' => $lang['profile']
							),
							$tm->manuale['author']);

				if (isset($associat[$comitem['userid']]) AND ! empty($associat[$comitem['userid']]['avatar'])) {
					$avatar = $associat[$comitem['userid']]['avatar'];
				} else {
					$avatar = '/up/avatar/blank/guest.png';
				}

				$regdate = (isset($associat[$comitem['userid']]) AND ! empty($associat[$comitem['userid']]['regdate'])) ? $associat[$comitem['userid']]['regdate'] : '';
				$user = $tm->parse(array
							(
								'languser'	=> $lang['block_user'],
								'avatar'	=> $avatar,
								'link'		=> $link_profile,
								'nameuser'	=> $api->siteuni($comitem['cname']),
								'register'	=> $lang['registr_date'],
								'date'	=> $regdate
							),
							$tm->manuale['user']);
			}
			else
			{
				$author = $api->siteuni($comitem['cname']);
				$guest = $tm->parse(array('guest' => $lang['guest']), $tm->manuale['guest']);
			}

			$ins['output'].= $tm->parse(array
				(
					'author' => $author,
					'title'  => $api->siteuni(str_word($comitem['ctitle'], 35)),
					'date'   => $comitem['ctime'],
					'text'   => $text,
					'guest'  => $guest,
					'user'   => $user
				),
				$ins['comment']);

			/**
			 * Вывод
			 */
			$tm->parseprint(array
				(
					'title' => $lang['comment_last'],
					'total' => $lang['all_alls'],
					'count' => $item['comments'],
					'comment' => $ins['output'],
					'pages' => null
				),
				$tm->parsein($tm->create('mod/'.WORKMOD.'/comment'))
			);
		}

		echo '<!--ok '.NEWTIME.'-->';

		exit();
	}
}
else
{
	$tm->error($lang['system_error'], 1, 1);
}
