<?php
/**
 * File:        /mod/user/index.php
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
global $dn, $to, $db, $basepref, $tm, $config, $lang, $usermain, $userapi, $tm, $edit, $country, $region;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Если интеграция включена
 * Если регистрация отключена
 * Редирект
 */
if ( ! defined('REGTYPE') OR ! defined('USER_DANNEO'))
{
	redirect(SITE_URL);
}

/**
 * Форма авторизации
 */
if ( ! defined('USER_LOGGED'))
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom']);
	} else {
		$global['title'] = $global['modname'];
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = (defined('CUSTOM')) ? CUSTOM : $global['modname'];
	if ( ! empty($config['mod'][WORKMOD]['map'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['map']);
	} elseif ( ! empty($config['mod'][WORKMOD]['descript'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['descript']);
	}

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['enter_profile']);

	$tm->noaccessprint();
}

/**
 * Метки
 */
$legaltodo = array('index', 'repassw', 'remail', 'redata', 'logout');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * -------------- */
if ($to == 'index')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' - '.$usermain['uname']);
	} else {
		$global['title'] = $global['modname'].' - '.$usermain['uname'];
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].', '.$lang['profile'].' - '.$usermain['uname'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = (defined('CUSTOM')) ? CUSTOM : $global['title'];
	if ( ! empty($config['mod'][WORKMOD]['map'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['map']);
	} elseif ( ! empty($config['mod'][WORKMOD]['descript'])) {
		$global['og_desc'] = $api->siteuni($config['mod'][WORKMOD]['descript']);
	}
	$global['og_image'] = ( ! empty($usermain['avatar'])) ? SITE_URL.$userapi->avatar($usermain['favatar'], 0) : SITE_URL.$userapi->data['avatarpath'].$userapi->data['noavatar'];

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['profile'], $usermain['uname']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Переключатели
	 */
	$tm->unmanule['editpass'] = $conf['editpass'];
	$tm->unmanule['editmail'] = $conf['editmail'];

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'apart' => null,
			'field' => null,
			'avatar_danneo' => null,
			'avatar_thumb' => null
	);

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/form.profile'));

	/**
	 * Дополнительные данные
	 */
	$field = null;
	$inqure = $db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes' ORDER BY posit");

	if ($db->numrows($inqure) > 0)
	{
		$ui = $db->fetchassoc($db->query("SELECT userfield FROM ".$basepref."_user WHERE userid = '".$usermain['userid']."'"));
		$user = ( ! empty($ui['userfield'])) ? Json::decode($ui['userfield']) : '';
		while ($item = $db->fetchassoc($inqure))
		{
			// Название дополнительного поля
			$name = 'fields['.$item['fieldname'].']';
			if ($item['profile'] == 'yes')
			{
				if ($item['fieldtype'] == 'apart')
				{
					$field .= $tm->parse(array
							(
								'name'  => $item['name'],
								'field' => ''
							),
							$tm->manuale['apart']);
				}
				else
				{
					$value = (isset($user[$item['fieldid']])) ? $user[$item['fieldid']] : '';
					$newfield = '';

					// Телефон
					if ($item['fieldtype'] == 'text')
					{
						$place = ($item['method'] == 'phone' AND empty($value)) ? ' placeholder="+(7)"' : '';
						$empty = ($item['requires'] == 'yes') ? ' title="'.$lang['all_not_empty'].'"' : '';
						$requires = ($item['requires'] == 'yes') ? '<i></i>' : '';
						$newfield = '<input type="text" name="'.$name.'" maxlength="'.$item['maxlen'].'" value="'.$value.'"'.$place.'>';
					}

					// Текстовое поле
					if ($item['fieldtype'] == 'textarea')
					{
						$empty = ($item['requires'] == 'yes') ? ' title="'.$lang['all_not_empty'].'"' : '';
						$requires = ($item['requires'] == 'yes') ? '<i></i>' : '';
						$newfield = '<textarea cols="30" rows="7" name="'.$name.'">'.$value.'</textarea>';
					}

					// Переключатель
					if ($item['fieldtype'] == 'radio')
					{
						$list = Json::decode($item['fieldlist']);
						$empty = ($item['requires'] == 'yes') ? ' title="'.$lang['all_not_empty'].'"' : '';
						$requires = ($item['requires'] == 'yes') ? '<i></i>' : '';
						foreach ($list as $k => $v)
						{
							$newfield.= '<div class="field-radio"><input type="radio" name="'.$name.'" value="'.$k.'"'.(($k == $value) ? ' checked' : '').'><span>'.$v.'</span></div>';
						}
					}

					// Выпадающий список
					if ($item['fieldtype'] == 'select')
					{
						$list = Json::decode($item['fieldlist']);
						$empty = ($item['requires'] == 'yes') ? ' title="'.$lang['all_not_empty'].'"' : '';
						$requires = ($item['requires'] == 'yes') ? '<i></i>' : '';
						$newfield.='<select name="'.$name.'">';
						foreach ($list as $k => $v)
						{
							$newfield.='<option value="'.$k .'"'.(($k == $value) ? ' selected' : '').'>'.$v.'</option>';
						}
						$newfield.='</select>';
					}

					// Дата
					if ($item['fieldtype'] == 'date')
					{
						$value = empty($value) ? array('d' => 1, 'm' => 1, 'y' => 1971) : $value;
						$empty = ($item['requires'] == 'yes') ? ' title="'.$lang['all_not_empty'].'"' : '';
						$requires = ($item['requires'] == 'yes') ? '<i></i>' : '';
						$newfield.= '<div class="field-date">';

						// Дни
						$newfield.= '<select name="'.$name.'[day]">';
						for ($i = 1; $i < 32; $i ++)
						{
							$newfield.= '<option value="'.$i.'"'.(($value['d'] == $i) ? ' selected' : '').'>'.$i.'</option>';
						}
						$newfield.= '</select>&nbsp;';

						// Месяцы
						$newfield.= '<select name="'.$name.'[month]">';
						for ($i = 1; $i < 13; $i ++)
						{
							$newfield.= '<option value="'.$i.'"'.(($value['m'] == $i) ? ' selected' : '').'>'.$i.'</option>';
						}
						$newfield.= '</select>&nbsp;';

						// Года
						$newfield.= '<select name="'.$name.'[year]">';
						for($i = 1928; $i < (NEWYEAR + 1); $i ++)
						{
							if (empty($value) AND $i == 1971)
								$newfield.= '	<option value="'.$i.'" selected>'.$i.'</option>';
							elseif ($i == $value['y'])
								$newfield.= '	<option value="'.$i.'" selected>'.$i.'</option>';
							else
								$newfield.= '	<option value="'.$i.'">'.$i.'</option>';
						}
						$newfield.= '</select>';

						$newfield.= '</div>';
					}

					// Вывод
					$field .= $tm->parse(array
							(
								'name'  => $item['name'],
								'empty' => $empty,
								'req'   => $requires,
								'field' => $newfield
							),
							$tm->manuale['field']);
				}
			}
		}
	}

	/**
	 * Аватары
	 */
	$avatarlist = null;
	$avatar = $usermain['avatar'];

	if (defined('USER_DANNEO'))
	{
		// Пользователь
		$avatar = $tm->parse(array
					(
						'src'	=> ( ! empty($usermain['avatar'])) ? $userapi->avatar($usermain['favatar'], 0) : $userapi->data['avatarpath'].$userapi->data['noavatar'],
						'name'	=> ( ! empty($usermain['avatar'])) ? $usermain['favatar'] : $userapi->data['noavatar'],
						'alt'	=> ( ! empty($usermain['avatar'])) ? $lang['avatar'].': '.mb_substr($usermain['favatar'], 0, -4) : $lang['avatar_clear']
					),
					$tm->manuale['avatar_danneo']);

		// Выбор аватар
		$avatars = array();
		$avatars_dir = opendir(str_replace('//','/',DNDIR.$userapi->data['avatarpath']));
		$avatars[] = $tm->parse(array
						(
							'path' => $userapi->data['avatarpath'].$userapi->data['noavatar'],
							'name'	=> $userapi->data['noavatar'],
							'alt'	=> $lang['avatar_clear']
						),
						$tm->manuale['avatar_thumb']);

		while (($name = readdir($avatars_dir)) !== false)
		{
			if (preg_match("%\.(jpg|jpeg|gif|png)%i", $name) AND $name != $userapi->data['noavatar'])
			{
				$avatars[] = $tm->parse(array
								(
									'path'	=> $userapi->data['avatarpath'].$name,
									'name'	=> $name,
									'alt'	=> mb_substr($name, 0, -4)
								),
								$tm->manuale['avatar_thumb']);
			}
		}
		closedir($avatars_dir);

		$avatarlist = $tm->tableprint($avatars, 5, 0);
	}

	/**
	 * Страна, регион
	 */
	$country = array();
	$get_country = DNDIR.'cache/cache.country.php';
	if (file_exists($get_country))
	{
		$country = include($get_country);
	}

	if ( ! is_array($country))
	{
		$inq = $db->query("SELECT * FROM ".$basepref."_country ORDER BY posit ASC");
		while ($item = $db->fetchassoc($inq))
    	{
			$country[$item['countryid']] = $item;
		}

		$inq = $db->query("SELECT * FROM ".$basepref."_country_region ORDER BY posit ASC");
		while ($item = $db->fetchassoc($inq))
		{
			$country[$item['countryid']]['region'][$item['regionid']] = $item['regionname'];
		}
	}

    $c = $r = 0;
    $inq = $db->query("SELECT countryid, regionid FROM ".$basepref."_user WHERE userid = '".$usermain['userid']."'");
    if ($db->numrows($inq) == 1)
    {
        $item = $db->fetchassoc($inq);
        $c = $item['countryid'];
        $r = $item['regionid'];
    }

	$countrysel = $statesel = null;
	foreach ($country as $k => $v)
	{
		$countrysel.= '<option value="'.$k.'"'.(($k == $c) ? ' selected' : '').'>'.$v['countryname'].'</option>';
		//if ($k == $c AND is_array($v['region']) AND sizeof($v['region']) > 0)
		if (is_array($v['region']) AND sizeof($v['region']) > 0)
		{
			foreach ($v['region'] as $sk => $sv)
			{
				$statesel.= '<option value="'.$sk.'"'.(($sk == $r) ? ' selected' : '').'>'.$sv.'</option>';
			}
		}
	}

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'post_url'          => $ro->seo('index.php?dn='.WORKMOD),
			'title'             => $lang['profile'],
			'chang_pass'        => $lang['chang_pass'],
			'chang_email'       => $lang['chang_email'],
			'user_data'         => $lang['user_data'],
			'pass'              => $lang['pass'],
			're_pass'           => $lang['re_pass'],
			'e_mail'            => $lang['e_mail'],
			're_e_mail'         => $lang['re_e_mail'],
			'pass_hint'         => $lang['pass_hint'],
			'mail_hint'         => $lang['mail_hint'],
			'addit_fields'      => $field,
			'maxpass'           => $conf['maxpass'],
			'minpass'           => $conf['minpass'],
			'umail'             => $usermain['umail'],
			'phone'             => $usermain['phone'],
			'lang_phone'        => $lang['phone'],
			'phone_hint'        => $lang['phone_help'],
			'city'              => $usermain['city'],
			'lang_city'         => $lang['city'],
			'skype'             => $usermain['skype'],
			'url'               => $usermain['www'],
			'urlname'           => $lang['site_url'],
			'chang_button_pass' => $lang['chang_button_pass'],
			'chang_button_email'=> $lang['chang_button_email'],
			'up_data'           => $lang['all_save'],
			'lang_avatar'       => $lang['avatar_add'],
			'avatar'            => $avatar,
			'avatarlist'        => $avatarlist,
			'lang_country'      => $lang['country'],
			'lang_state'        => $lang['state'],
			'countrysel'        => $countrysel,
			'statesel'          => $statesel,
			'username'          => $usermain['uname'],
			'last_visit'        => $lang['last_visit'],
			'registration'      => $lang['registr_date'],
			'logout_url'        => $ro->seo('index.php?dn=user&amp;re=logout'),
			'logout'            => $lang['goto_logout'],
			'date'              => $usermain['regdate'],
			'redate'            => $usermain['lastvisit']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка repassw, danneo
 * ----------------------- */
if ($to == 'repassw')
{
	/**
	 * Редирект, если запрещено изменение пароля
	 */
	if ($conf['editpass']== 'no')
	{
		redirect($ro->seo('index.php?dn='.WORKMOD));
	}

	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' | '.$usermain['uname']);
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].', '.$lang['profile'].' - '.$usermain['uname'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $global['modname'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$lang['profile'].'</a>', $usermain['uname']);

	/**
	 * Ошибка, если пароль не валидный
	 */
	if (verify_pwd($onepassw) == 0 OR $onepassw <> $twopassw)
	{
		$not_coin = ($onepassw <> $twopassw) ? '<li>'.$lang['not_coin_pass'].'</li>' : '';
		$bad_pass = this_text(array
			(
				"minpass" => $conf['minpass'],
				"maxpass" => $conf['maxpass']
			),
			$lang['pass_hint']);

		$error_mess = $lang['possible_reason'].'
		<ol>
			'.$not_coin.'
			<li>'.$bad_pass.'</li>
		</ol>';

		$tm->error($error_mess, $lang['isset_error'], 0);
	}

	$passw = md5($onepassw);
	$db->query("UPDATE ".$basepref."_user SET upass = '".$passw."' WHERE userid = '".$usermain['userid']."'");

	$cookie = serialize(array($usermain['userid'], md5($usermain['uname'].$passw), $usermain['uname']));
	setcookie(USERCOOKIE, $cookie, NEWTIME + $config['cookexpire'], DNROOT);

	// ОК
	$tm->message($lang['pass_success'], 0, 1);

	/**
	 * Редирект
	 */
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метка remail
 * ---------------- */
if ($to == 'remail')
{
	/**
	 * Редирект, если запрещено изменение пароля
	 */
	if ($conf['editmail'] == 'no')
	{
		redirect($ro->seo('index.php?dn='.WORKMOD));
	}

	/**
	 * Ошибка, неверный формат
	 */
	if (
		! is_array($edit) OR
		! isset($edit['onemail']) OR
		! isset($edit['twomail']) OR
		verify_mail($edit['onemail']) == 0 OR
		$edit['onemail'] != $edit['twomail']
	) {
		$plus = ($edit['onemail'] != $edit['twomail']) ? '<b>'.$lang['not_identical_email'].'</b><br />' : '';
		$tm->error($plus.$lang['bad_mail']);
	}

	/**
	 * Ошибка, если e-mail уже есть в базе
	 */
	if ($userapi->issetmail($edit['onemail']) > 0)
	{
		$tm->error($lang['bad_mail_user']);
	}

	/**
	 * Обновляем  e-mail
	 */
	$userapi->addmail($edit['onemail']);

	/**
	 * Редирект
	 */
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метка redata
 * ---------------- */
if ($to == 'redata')
{
	/**
	 * Основные данные
	 ------------------*/

	// Phone
	if ( ! empty($edit['phone']) AND ! preg_match('/^[0-9\-()+ ]+$/D', $edit['phone']))
	{
		$tm->error($lang['mail_phone_error']);
	}

	// URL
	$edit['www'] = ( ! empty($edit['www'])) ? $userapi->addurl($edit['www']) : '';

	// Avatar
	$is_avatar = is_file(DNDIR.$userapi->data['avatarpath'].'/'.$edit['avatar']);
	$edit['avatar'] = ( ! empty($edit['avatar']) AND $edit['avatar'] != $userapi->data['noavatar'] AND $is_avatar) ? $edit['avatar'] : '';

	// Обновляем
	$userapi->adduse($edit['phone'], $edit['city'], $edit['skype'], $edit['www'], $edit['avatar']);

	/**
	 * Дополнительные данные
	 -------------------------*/

	$inqure = $db->query("SELECT * FROM ".$basepref."_user_field WHERE act = 'yes'");
	$checkfield = $newfield = array();

	if ($db->numrows($inqure) > 0)
	{
		$error = 0;
		$list = '';
		while ($item = $db->fetchassoc($inqure)) {
			$checkfield[$item['fieldid']] = $item;
		}
		foreach($checkfield as $k => $v)
		{
			if (isset($fields[$v['fieldname']]))
			{
				if ($v['fieldtype'] == 'text')
				{
					if ($v['method'] == 'text')
					{
						if ($v['requires'] == 'yes')
						{
							$newfield[$v['fieldid']] = (mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\pL\pNd\-\s\.(),!?]+$/ui',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							if ( ! $newfield[$v['fieldid']]) {
								$error = 1;
								$list.= $v['name'].', ';
							}
						}
						else
						{
							if (preg_match('/^[\pL\pNd\-\s\.(),!?]+$/ui',$fields[$v['fieldname']])) {
								$newfield[$v['fieldid']] =  $api->siteuni($fields[$v['fieldname']]);
							} else {
								$newfield[$v['fieldid']] = '';
							}
						}
					}

					if ($v['method'] == 'email')
					{
						$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND mb_strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/uis',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}

					if ($v['method'] == 'number')
					{
						$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[\d]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}

					if($v['method'] == 'phone')
					{
						if ( ! empty($fields[$v['fieldname']]))
						{
							$newfield[$v['fieldid']] = ($v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) < $v['minlen'] OR $v['requires'] == 'yes' AND strlen($fields[$v['fieldname']]) > $v['maxlen'] OR ! preg_match('/^[0-9\s\.\+()-]+$/',$fields[$v['fieldname']])) ? '' : $fields[$v['fieldname']];
							if ( ! $newfield[$v['fieldid']]) {
								$error = 1;
								$list.= $v['name'].', ';
							}
						}
					}
				}

				if ($v['fieldtype'] == 'textarea')
				{
					if ($v['requires'] == 'yes')
					{
						$newfield[$v['fieldid']] = (mb_strlen($fields[$v['fieldname']]) < $v['minlen'] OR mb_strlen($fields[$v['fieldname']]) > $v['maxlen']) ? '' : $api->siteuni($fields[$v['fieldname']]);
						if ( ! $newfield[$v['fieldid']]) {
							$error = 1;
							$list.= $v['name'].', ';
						}
					}
					else
					{
						if (preg_match('/^[\pL\pNd\-\s\.(),!?]+$/ui',$fields[$v['fieldname']])) {
							$newfield[$v['fieldid']] =  $api->siteuni($fields[$v['fieldname']]);
						} else {
							$newfield[$v['fieldid']] = '';
						}
					}
				}

				if ($v['fieldtype'] == 'select' OR $v['fieldtype'] == 'radio')
				{
					$newfield[$v['fieldid']] = $fields[$v['fieldname']];
				}

				if ($v['fieldtype']=='date')
				{
					$date = array();
					$date['d'] = (preparse($fields[$v['fieldname']]['day'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['day'],THIS_INT) > 31) ? 1 : preparse($fields[$v['fieldname']]['day'],THIS_INT);
					$date['m'] = (preparse($fields[$v['fieldname']]['month'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['month'],THIS_INT) > 12) ? 1 : preparse($fields[$v['fieldname']]['month'],THIS_INT);
					$date['y'] = (preparse($fields[$v['fieldname']]['year'],THIS_INT) <= 0 OR preparse($fields[$v['fieldname']]['year'],THIS_INT) > NEWYEAR) ? NEWYEAR : preparse($fields[$v['fieldname']]['year'],THIS_INT);
					$newfield[$v['fieldid']] = $date;
				}
			}
			else
			{
				$newfield[$v['fieldid']] = '';
			}
		}

		/**
		 * Ошибка, дополнительные поля
		 */
		if ($error)
		{
			$tm->error($lang['bad_fields'].'<br />'.mb_substr($list, 0, -2));
		}
	}

	/**
	 * Обновляем
	 */
	$insert = ( ! empty($newfield) ? Json::encode($newfield) : '');
	$db->query
		(
			"UPDATE ".$basepref."_user SET
			 userfield = '".$db->escape($insert)."',
			 countryid = '".$db->escape($country)."',
			 regionid  = '".$db->escape($region)."'
			 WHERE userid = '".$usermain['userid']."'"
		);

	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Метка logout
 * ---------------- */
if ($to == 'logout')
{
	$userapi->logout();
	redirect((defined('HTTP_REFERERS')) ? HTTP_REFERERS : $ro->seo('index.php?dn='.WORKMOD));
}
