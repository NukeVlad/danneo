<?php
/**
 * File:        /mod/contact/index.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $ro, $api, $global,
		$captcha, $title, $to_email, $sendnames, $sendtexts, $cid, $respon, $sub;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

if ($_SERVER['REQUEST_METHOD'] == 'TRACE') {
    exit();
}
if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
    exit();
}
if ( ! is_array($GLOBALS) ) {
    exit();
}

/**
 * Метки
 */
$legaltodo = array('index', 'send');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * -------------- */
if ($to == 'index')
{
	$ins = array();

	$item = $db->fetchassoc($db->query("SELECT * FROM ".$basepref."_".WORKMOD));

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
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript']) : '';

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
	$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
	$global['insert']['breadcrumb'] = $global['modname'];

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

    /**
	 * Переключатели
	 */
	$tm->unmanule['captcha'] = ($config['captcha'] == 'yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
	$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['attach'] = ($config['mail_attach'] == 'yes') ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'notice' => null,
			'thumb' => null
		);

	$ins['image'] = $ins['notice'] = $ins['vcard'] = $ins['form'] = $ins['map'] = '';

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/index'));

	/**
	 * Содержимое
	 */
	$ins['textshort'] = $api->siteuni($item['textshort']);
	$ins['textmore']  = $api->siteuni($item['textmore']);

	/**
	 * Сообщение для пользователей
	 */
	if ($item['textnotice'] AND defined('USER_LOGGED'))
	{
		$ins['notice'] = $tm->parse(array
							(
								'text' => $api->siteuni($item['textnotice']),
								'css' => 'user'
							),
							$tm->manuale['notice']);
	}
	elseif($item['textnotice'])
	{
		$ins['notice'] = $tm->parse(array
							(
								'text' => $lang['block_user_view'],
								'css' => 'guest'
							),
							$tm->manuale['notice']);
	}

	/**
	 * Вводное изображение
	 */
	$ins['alt']   = ( ! empty($item['image_alt'])) ? $item['image_alt'] : '';
	$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
	$ins['thumb'] = '<img src="'.SITE_URL.'/'.$item['image_thumb'].'" alt="'.$ins['alt'].'" />';

	if ($item['image_thumb'] AND $item['image'])
	{
		$ins['big'] = ($item['image']) ? '<a rel="tag" class="media-view" href="'.SITE_URL.'/'.$item['image'].'" data-title="'.$ins['alt'].'">'.$ins['thumb'].'</a>' : '';
		$ins['image'] = $tm->parse(array
							(
								'float' => $ins['float'],
								'thumb' => $ins['big']
							),
							$tm->manuale['thumb']);
	}
	elseif ($item['image_thumb'])
	{
		$ins['image'] = $tm->parse(array
							(
								'float' => $ins['float'],
								'thumb' => $ins['thumb']
							),
							$tm->manuale['thumb']);
	}

	/**
	 * Изображения по тексту
	 */
	if ( ! empty($item['images']))
	{
		$im = Json::decode($item['images']);
		if (is_array($im))
		{
			$i = 1;
			foreach ($im as $k => $v)
			{
				if ($v['align'] == 'left') {
					$float = 'imgtext-left';
				} elseif ($v['align'] == 'right') {
					$float = 'imgtext-right';
				} else {
					$float = 'imgtext-center';
				}
				$thumb = '<img src="'.SITE_URL.'/'.$v['thumb'].'" alt="'.$v['alt'].'" />';
				$image = ($v['image']) ? '<a rel="tag" class="media-view" href="'.SITE_URL.'/'.$v['image'].'" data-title="'.$v['alt'].'">'.$thumb.'</a>' : $thumb;
				$val = $tm->parse(array
						(
							'float' => $float,
							'thumb' => $image
						),
						$tm->manuale['thumb']);
				$ins['textmore'] = $tm->parse(array('img'.$i => $val), $ins['textmore']);
				$i ++;
			}
		}
	}

	/**
	 * Контакты организации (vCard)
	 */
	if ($conf['org'] == 'yes')
	{
		$org = Json::decode($conf['vcard']);
		if (is_array($org) AND ! empty($org))
		{
			$tm->unmanule['code'] = ( ! empty($org['vcard_code'])) ? 'yes' : 'no';
			$tm->unmanule['country'] = ( ! empty($org['vcard_country'])) ? 'yes' : 'no';
			$tm->unmanule['region'] = ( ! empty($org['vcard_region'])) ? 'yes' : 'no';
			$tm->unmanule['locality'] = ( ! empty($org['vcard_locality'])) ? 'yes' : 'no';
			$tm->unmanule['street'] = ( ! empty($org['vcard_locality']) AND ! empty($org['vcard_street'])) ? 'yes' : 'no';
			$tm->unmanule['tel'] = ( ! empty($org['vcard_tel'])) ? 'yes' : 'no';
			$tm->unmanule['email'] = ( ! empty($org['vcard_email'])) ? 'yes' : 'no';
			$tm->unmanule['work'] = ( ! empty($org['vcard_work'])) ? 'yes' : 'no';
			$tm->unmanule['geo'] = ( ! empty($org['longitude']) AND ! empty($org['latitude'])) ? 'yes' : 'no';

			$ins['vcard_url'] = ( ! empty($org['vcard_url'])) ? $org['vcard_url'] : SITE_URL;

			// Шаблон
			$ins['tempcard']= $tm->parsein($tm->create('mod/'.WORKMOD.'/card'));

			$ins['vcard'] = $tm->parse(array
								(
									'org'		=> $org['vcard_org'],
									'code'		=> $org['vcard_code'],
									'country'	=> $org['vcard_country'],
									'region'	=> $org['vcard_region'],
									'locality'	=> $org['vcard_locality'],
									'street'	=> $org['vcard_street'],
									'tel'		=> $api->call_tel($org['vcard_tel']),
									'email'		=> $org['vcard_email'],
									'url'		=> $ins['vcard_url'],
									'work'		=> $org['vcard_work'],
									// geo
									'longitude' => $org['longitude'],
									'latitude'  => $org['latitude'],
									// lang
									'title'		=> $lang['vcard'],
									'langtel'	=> $lang['vcard_tel'],
									'langmail'	=> $lang['e_mail'],
									'langwork'	=> $lang['vcard_work'],
								),
								$ins['tempcard']);
		}
	}

	/**
	 * Карта проезда
	 */
	if ($conf['map'] == 'yes')
	{
		$org = Json::decode($conf['vcard']);
		if (is_array($org) AND ! empty($org))
		{
			if ( ! empty($org['vcard_locality']))
			{
				$ins['tempmap']= $tm->parsein($tm->create('mod/'.WORKMOD.'/map'));
				$ins['map'] = $tm->parse(array
								(
									'title'		=> $lang['contact_map'],
									'country'	=> $org['vcard_country'],
									'region'	=> $org['vcard_region'],
									'locality'	=> $org['vcard_locality'],
									'street'	=> $org['vcard_street'],
									'longitude'	=> $org['longitude'],
									'latitude'	=> $org['latitude']
								),
								$ins['tempmap']);
			}
		}
	}

	/**
	 * Контрольный вопрос
	 */
	$control = send_quest();

	/**
	 * Форма обратной связи
	 */
	if ($conf['feedback'] == 'yes')
	{
		$ins['form'] = $tm->parse(array
							(
								'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
								'form_title'   => $lang['feedback'],
								'email_name'   => $lang['email_name'],
								'email'        => $lang['e_mail'],
								'email_text'   => $lang['email_text'],
								'mail_hint'    => $lang['mail_hint'],
								'email_org'    => $lang['mail_org'],
								'email_phone'  => $lang['mail_phone'],
								'email_file'   => this_text(array('num' => $config['mail_file_col']), $lang['mail_file']),
								'file_help'    => $lang['mail_file_help'],
								'uname'        => $usermain['uname'],
								'umail'        => $usermain['umail'],
								'all_refresh'  => $lang['all_refresh'],
								'captcha'      => $lang['all_captcha'],
								'help_captcha' => $lang['help_captcha'],
								'control_word' => $lang['control_word'],
								'help_control' => $lang['help_control'],
								'not_empty'    => $lang['all_not_empty'],
								'control'      => $control['quest'],
								'cid'          => $control['cid'],
								'email_send'   => $lang['email_send']
							),
							$tm->parsein($tm->create('mod/'.WORKMOD.'/form')));
	}

	/**
	 * Подзаголовок
	 */
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'title'      => $ins['subtitle'],
			'image'      => $ins['image'],
			'text'       => $ins['textshort'],
			'textmore'   => $ins['textmore'],
			'textnotice' => $ins['notice'],
			'vcard'      => $ins['vcard'],
			'formmail'   => $ins['form'],
			'locmap'     => $ins['map']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка send
 * -------------- */
if ($to == 'send')
{
	$files = array();

	/**
	 * Проверка секретного кода
	 */
	if ($config['captcha'] == 'yes')
	{
		if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
		{
			$tm->error($lang['bad_captcha']);
		}
	}

	/**
	 * Проверка контрольного вопроса
	 */
	check_quest($cid, $respon);

	/**
	 * Проверка имени
	 */
	if (verify_send_name($sendnames) == 0)
	{
		$bad_login = this_text(array
			(
				"minname" => $config['user']['minname'],
				"maxname" => $config['user']['maxname']
			),
			$lang['bad_login']);
		$tm->error($bad_login.'<br />'.$lang['bad_login_symbol']);
	}

	/**
	 * Проверка организации
	 */
	if ( ! empty($sendorg) AND verify_send_name($sendorg) == 0)
	{
		$tm->error($lang['mail_org_error']);
	}

	/**
	 * Проверка телефона
	 */
	if ( ! empty($sendphone) AND verify_phone($sendphone) == 0)
	{
		$tm->error($lang['mail_phone_error']);
	}

	/**
	 * Проверка e-mail
	 */
	if (verify_mail($sendmails) == 0)
	{
		$tm->error($lang['bad_mail']);
	}

	/**
	 * Проверка сообщения
	 */
	$pretext = preparse(deltags($sendtexts), THIS_STRLEN);
	if ($pretext < $config['commin'] OR $pretext > $config['comsize'])
	{
		$errmess = this_text(array
				(
					"min" => $config['commin'],
					"max" => $config['comsize']
				),
				$lang['email_error_text']);
		$tm->error($errmess);
	}

	/**
	 * Данные для отправки
	 */
	$to = (isset($to_email)) ? $to_email : $config['site_mail'];
	$from = ( ! empty($sendorg) ? $sendorg : $sendnames)." <".$sendmails.">";
	$subject = $lang['contact_subject'].' - '.$config['site'];
	$message = this_text(array
				(
					"br"        => "\r\n",
					"sendname"  => $sendnames,
					"sendmail"  => $sendmails,
					"sendorg"   => $sendorg,
					"sendphone"	=> $sendphone,
					"text"      => $sendtexts
				),
				$lang['contact_msgtext']);
	$files = (isset($_FILES['files'])) ? $_FILES['files'] : '';

	/**
	 * Отправка
	 */
	$cho = send_mail($to, $subject, $message, $from, $files);

	/**
	 * Вывод сообщения
	 */
	if ($cho === TRUE)
	{
		// ok
		$tm->message
		(
			$lang['email_cong_text'].' <a href="'.SITE_URL.'">'.$config['site'].'</a>',
			$global['modname'], // its headline
			FALSE // without button
		);
	}
	else
	{
		// error
		$tm->error('Error: The message was not Sent!');
	}
}
