<?php
/**
 * File:        /mod/catalog/reviews.php
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
		$id, $cid, $respon, $uname, $title, $message, $ajax, $captcha, $ct, $region, $rate;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * ajax
 */
$ajax = ($config['ajax'] == 'yes' AND preparse($ajax, THIS_INT) > 0) ? 1 : 0;

/**
 * Ошибка, отзывы отключены
 */
if ($conf['resact'] == 'no')
{
	$tm->norightprint();
}

/**
 * Время, ajax
 */
$ct = preparse($ct, THIS_INT);
$ct = ($ct > (NEWTIME - $conf['restime']) AND $ct < NEWTIME) ? $ct : NEWTIME - 1;

/**
 * Заголовки
 */
if ($ajax == 1)
{
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Type: text/plain; charset='.$config['langcharset'].'');
}

/**
 * Ошибка, не выбрана оценка
 */
if ($rate == 0)
{
	$tm->error($lang['rating_error_title'], 1, 1);
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
				"SELECT id, catid, cpu, title, acc, groups, reviews FROM ".$basepref."_".WORKMOD."
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
		$conf['resadd'] == 'user'
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
if (in_array($uname, $blogin))
{
	$tm->error($lang['bad_login_symbol'], 1, 1);
}
}

/**
 * Проверка текста отзыва
 */
$mess = preparse(deltags($message), THIS_STRLEN);
if ($mess < 5 OR $mess > $config['comsize'])
{
	$tm->error($lang['response_empty'], 1, 1);
}

/**
 * Проверка региона
 */
$reg = preparse(deltags($region), THIS_STRLEN);
if ($reg < 5 OR $reg > 70)
{
	$tm->error($lang['response_empty'], 1, 1);
}

/**
 * Проверка на флуд
 */
$checktime = $db->fetchassoc
				(
					$db->query
					(
						"SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE (
						 file = '".WORKMOD."'
						 AND pageid = '".$item['id']."'
						 AND public >= '".(NEWTIME - $conf['restime'])."'
						 AND ip = '".REMOTE_ADDRS."'
						 )"
					)
				);

if ($checktime['total'] > 0)
{
	$tm->error($lang['response_flood'], 1, 1);
}

/**
 * Данные в базу
 */
$message = deltags(commentparse($message));
$uname = (defined('USER_LOGGED')) ? $usermain['uname'] : mb_substr(deltags($uname), 0, 50);
$active = ($conf['resmoder'] == 'yes') ? 0 : 1;

$insert = $db->query
		(
			"INSERT INTO ".$basepref."_reviews VALUES (
			 NULL,
			 '".WORKMOD."',
			 '".$item['id']."',
			 '".$usermain['userid']."',
			 '".NEWTIME."',
			 '".$db->escape($uname)."',
			 '".$db->escape($item['title'])."',
			 '".$db->escape($message)."',
			 '".REMOTE_ADDRS."',
			 '".$region."',
			 '".$active."',
			 '".$rate."'
			 )"
		);

/**
 * Если успешно
 */
if ($insert)
{
	$ins = array();

	if ($active)
	{
		$db->query("UPDATE ".$basepref."_".WORKMOD." SET reviews = reviews + 1 WHERE id = '".$item['id']."'");
	}
	elseif ( ! $active AND $ajax == 1)
	{
		$tm->message($lang['response_add_moder'], 0, 0, 1);
	}

	// Full count
	$count = $db->fetchassoc($db->query("SELECT COUNT(reid) AS total FROM ".$basepref."_reviews WHERE file = '".WORKMOD."' AND pageid = '".$id."'"));

	if ($ajax == 0)
	{
		$ins['pc'] = null;
		if ($config['comsort'] == 'asc')
		{
			$nums = ceil($count['total'] / $conf['respage']);
			$ins['pc'] = ($nums > 1) ? '&amp;p='.$nums : '';
		}

		// cpu
		$ins['cpu']    = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
		$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

		// url
		$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'].$ins['pc']);

		// Редирект
		$tm->redirectprint(null, $config['redirtime'], ($active) ? $lang['response_added'] : $lang['response_add_moder'], $ins['url']);
	}
	else
	{
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
		$standart = $tm->parsein($tm->create('mod/'.WORKMOD.'/reviews.standart'));

		$rev = $db->query
				(
					"SELECT * FROM ".$basepref."_reviews
					 WHERE file = '".WORKMOD."' AND pageid = '".$item['id']."' AND public > '".$ct."' AND active = '1'
					 ORDER BY public ASC LIMIT ".$conf['respage']
				);

		$in = $work = array();
		while ($itemc = $db->fetchassoc($rev))
		{
			$work[$itemc['reid']] = $itemc;
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
		foreach ($work as $val)
		{
			$guest = $user = null;
			$text = deltags($api->siteuni($val['message']));

			if ($val['userid'] > 0 AND isset($associat[$val['userid']]))
			{
				if (isset($userapi->data['linkprofile']) AND $userapi->data['linkprofile']) {
					$link_profile = $ro->seo($userapi->data['linkprofile'].$val['userid']);
				} else {
					$link_profile = FULL_REQUEST_URI;
				}
				$author = $tm->parse(array
							(
								'name'  => $api->siteuni($val['uname']),
								'link'  => $link_profile,
								'title' => $lang['profile']
							),
							$tm->manuale['author']);

				if (isset($associat[$val['userid']]) AND ! empty($associat[$val['userid']]['avatar'])) {
					$avatar = $associat[$val['userid']]['avatar'];
				} else {
					$avatar = '/up/avatar/blank/guest.png';
				}

				$regdate = (isset($associat[$val['userid']]) AND ! empty($associat[$val['userid']]['regdate'])) ? $api->sitetime($associat[$val['userid']]['regdate']) : '';
				$user = $tm->parse(array
							(
								'languser'	=> $lang['block_user'],
								'avatar'	=> $avatar,
								'link'		=> $link_profile,
								'nameuser'	=> $api->siteuni($val['uname']),
								'register'	=> $lang['registr_date'],
								'date'	    => $regdate
							),
							$tm->manuale['user']);
			}
			else
			{
				$author = $api->siteuni($val['uname']);
				$guest = $tm->parse(array('guest' => $lang['guest']), $tm->manuale['guest']);
			}

			/**
			 * Вывод
			 */
			$tm->parseprint(array
				(
					'author'   => $author,
					'title'    => $api->siteuni(str_word($val['title'], 35)),
					'date'     => $val['public'],
					'message'  => $message,
					'guest'    => $guest,
					'user'     => $user,
					'state'    => $lang['state'],
					'region'   => $val['region'],
					'rate'     => $val['rating'],
					'langrate' => $lang['rate_'.$val['rating']],
					'valrate'  => $lang['rate_emp']
				),
				$standart);
		}

		echo '<!--ok '.NEWTIME.'-->';

		exit();
	}
}
else
{
	$tm->error($lang['system_error'], 1, 1);
}
