<?php
/**
 * File:        /block/b-User.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $config, $usermain, $lang, $userapi, $key_block;

$bc = null;
$ins = array
	(
		'addlinks'     => null,
		'message'      => null,
		'user_avatar'  => null,
		'link_profile' => null,
		'link_logout'  => null,
		'top_user'     => null
	);

if (defined('SETTING'))
{
	return $bs = array('blockname' => $lang['block_user']);
}

if ( ! defined('USER_LOGGED'))
{
	$tm->unmanule['logged'] = 'no';
	if (defined('REGTYPE')) {
		$ins['top_user'] = $tm->parsein($tm->create('userblock'));
	}
	else
	{
		$bc.= $lang['data_not'];
	}
}
else
{
	$blocks = $added = array();

	$tm->unmanule['logged'] = 'yes';
	$tm->manuale = array
		(
			'mess'  => null,
			'links' => null
		);

	// Шаблон
	$ins['top_user'] = $tm->parsein($tm->create('userblock'));

	// Профиль
	if (defined('USER_DANNEO')) {
		$ins['link_profile'] = $ro->seo('index.php?dn=user');
	} else {
		$ins['link_profile'] = $userapi->data['linkprofile'].$usermain['userid'];
	}

	// Log Out
	if (defined('USER_DANNEO')) {
		$ins['link_logout'] = $ro->seo('index.php?dn=user&amp;re=logout');
	} else {
		$ins['link_logout'] = $ro->seo('index.php?dn=user&amp;re=logout');
	}

	// Аватар
	if (isset($usermain['avatar']{0})) {
		$ins['user_avatar'] = $usermain['avatar'];
	} else {
		$ins['user_avatar'] = '<img src="'.SITE_URL.'/up/avatar/blank/guest.png" alt="'.$usermain['uname'].'" />';
	}

	/**
	 * Ссылки модов
	 */
	$blocks = glob(DNDIR.'mod/*/block.user.php');
	foreach ($blocks as $file)
	{
		if (file_exists($file))
		{
			include($file);
		}
	}

	foreach ($added as $val)
	{
		$val['css'] = isset($val['css']) ? $val['css'] : '';
		$ins['addlinks'].= $tm->parse(array
			(
				'url'	=> $val['url'],
				'title'	=> $val['title'],
				'css'	=> $val['css']
			),
			$tm->manuale['links']);
	}

	/**
	 * Приватные сообщения
	 */
	if (isset($userapi->data['linkprivmess']{0}))
	{
		$ins['message'] = $tm->parse(array
			(
				'mess_url'  => SITE_URL.'/'.$userapi->data['linkprivmess'],
				'mess_lang' => $lang['private_message'].' ('.$usermain['newmsg'].')'
			),
			$tm->manuale['mess']);
	}

}

/**
 * Вывод
 */
$bc.= $tm->parse(array
	(
		'post_url'     => $ro->seo('index.php?dn=user'),
		'registr'      => $lang['registr'],
		'login'        => $lang['login'],
		'pass'         => $lang['pass'],
		'linklost'     => $ro->seo($userapi->data['linklost']),
		'linkreg'      => $ro->seo($userapi->data['linkreg']),
		'maxname'      => $config['user']['maxname'],
		'maxpass'      => $config['user']['maxpass'],
		'send_pass'    => $lang['send_pass'],
		'to_enter'     => $lang['to_enter'],
		'enter'        => $lang['enter'],
		'user_avatar'  => $ins['user_avatar'],
		'user_name'    => $usermain['uname'],
		'lang_visit'   => $lang['last_visit'],
		'date'         => $usermain['lastvisit'],
		'add'          => $lang['all_add'],
		'add_links'    => $ins['addlinks'],
		'message'      => $ins['message'],
		'link_profile' => $ins['link_profile'],
		'link_logout'  => $ins['link_logout'],
		'lang_profile' => $lang['your_profile'],
		'lang_logout'  => $lang['goto_logout']
	),
	$ins['top_user']
);


/**
 * Вывод
 */
return $bc;
