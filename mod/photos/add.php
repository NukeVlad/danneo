<?php
/**
 * File:        /mod/photos/add.php
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
global	$db, $basepref, $global, $config, $lang, $usermain, $tm, $api,
		$catid, $title, $textshort, $textmore, $link, $image, $cid, $respon;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Редирект, добавление отключено
 */
if ($conf['addit'] == 'no')
{
	redirect($ro->seo('index.php?dn='.WORKMOD));
}

/**
 * Меню, хлебные крошки
 */
$global['insert']['current'] = $lang['add_photos'];
$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['add_photos']);

/**
 * Доступ
 */
$ins['active'] = FALSE;
if($conf['adduse'] == 'user')
{
	if ( ! defined('USER_LOGGED'))
	{
		$tm->noaccessprint();
	}
	if (defined('GROUP_ACT') AND ! empty($conf['groups']))
	{
		$group = Json::decode($conf['groups']);
		if ( ! isset($group[$usermain['gid']]))
		{
			$tm->norightprint();
		}
		if (isset($group[$usermain['gid']]) AND $usermain['gid'] == 1)
		{
			$ins['active'] = TRUE;
		}
	}
}

/**
 * Проверка дубликатов
 */
function check_name($field, $name)
{
	global $db, $basepref, $lang, $tm;

	$checkname = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE ".$field." = '".$db->escape($name)."'"));
	if ($checkname['total'] > 0)
	{
		$tm->error($lang['cpu_error_isset'], 0, 0);
	}
}
function check_title($title)
{
	global $db, $basepref, $lang, $tm;

	$checktitle = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."_user WHERE title = '".$db->escape($title)."'"));
	if ($checktitle['total'] > 0)
	{
		$tm->error($lang['cpu_error_isset'], 0, 0);
	}
}

/**
 * Метки
 */
$legaltodo = array('index', 'save');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * --------------- */
if ($to == 'index')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' | '.$lang['add_photos']);
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].', '.$lang['profile'].' - '.$usermain['uname'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['add_photos'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['add_photos']);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Категории
	 */
	$area = array();
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchrow($inq, $config['cache']))
	{
		$area[$c['parentid']][$c['catid']] = $c;
	}
	$api->catcache = $area;

	/**
	 * Проверки, ключи
	 */
	$tm->unmanule['captcha'] = ($config['captcha']=='yes' AND defined('REMOTE_ADDRS')) ? 'yes' : 'no';
	$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['showcat']  = ( ! empty($area)) ? 'yes' : 'no';

	/**
	 * Отключить проверку для пользователей
	 */
	noprotectspam(0);

	/**
	 * Контрольный вопрос
	 */
	$control = send_quest();

	/**
	 * Форма добавления, в шаблон
	 */
	$tm->parseprint(array
		(
			'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
			'title'        => $lang['all_title'],
			'in_cat'       => $lang['all_in_cat'],
			'no_cat'       => $lang['no_cat_add'],
			'sel'          => $api->siteuni($api->selcat()),
			'descript'     => $lang['descript'],
			'image'        => $lang['all_image'],
			'all_refresh'  => $lang['all_refresh'],
			'control_word' => $lang['control_word'],
			'captcha'      => $lang['all_captcha'],
			'help_captcha' => $lang['help_captcha'],
			'help_control' => $lang['help_control'],
			'not_empty'    => $lang['all_not_empty'],
			'select'       => $lang['all_select'],
			'control'      => $control['quest'],
			'cid'          => $control['cid'],
			'all_add'      => $lang['all_add'],
			'select_file'  => $lang['select_file'],
			'img_help'     => $lang['img_help'],
			'is_large'     => $lang['is_large'],
			'incor_format' => $lang['incor_format']
		),
		$tm->parsein($tm->create('mod/'.WORKMOD.'/form.add')));

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка save
 * ------------- */
if ($to == 'save')
{
	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].' | '.$lang['add_photos']);
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].', '.$lang['profile'].' - '.$usermain['uname'] : '';
	$global['keywords'] = ( ! empty($config['mod'][WORKMOD]['keywords'])) ? $config['mod'][WORKMOD]['keywords'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $lang['add_photos'];
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $lang['add_photos']);

	/**
	 * Проверка заголовка
	 */
	if (empty($title))
	{
		$tm->error($lang['pole_add_error'], 0);
	}


	/**
	 * Отключить антиспам, для пользователей
	 */
	noprotectspam(1);

	/**
	 * Проверка секретного кода
	 */
	if ($config['captcha'] == 'yes')
	{
		if (findcaptcha(REMOTE_ADDRS, $captcha) == 1)
		{
			$tm->error($lang['bad_captcha'], 0);
		}
	}

	/**
	 * Проверка контрольного вопроса
	 */
	check_quest($cid, $respon);

	/**
	 * Антифлудер
	 */
	$checktime = $db->fetchrow
					(
						$db->query
						(
							"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."_user WHERE (
							 userid = '".$usermain['userid']."'
							 AND public >= '".(NEWTIME - $conf['addtime'])."'
							)"
						)
					);

	if ($checktime['total'] > 0)
	{
		$tm->error($lang['add_time_error'], 0);
	}

	/**
	 * Данные
	 */
	$catid = preparse($catid, THIS_INT);
	$title = $api->sitesp(preparse($title, THIS_TRIM));
	$text = preparse($text, THIS_TRIM);
	$customs = $descript = $image_alt = $title;

	$trl = new Translit();
	$cpu = $trl->title($trl->process($title));

	check_name('cpu', $cpu);
	check_name('title', $title);
	check_title($title);

	/**
	 * Обработка изображения
	 */
	if (isset($_FILES['image']) AND ! empty($_FILES['image']['name']))
	{
		$tmp_name = $_FILES['image']['tmp_name'];
		if (is_uploaded_file($tmp_name))
		{
			$dirimg = 'up/'.WORKMOD.'/add/';
			$extname = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
			$newname = date("ymd", time()).'_'.mt_rand(0, 9999);
			$imgname = $newname.'.'.$extname;
			$imgname_thumb = $newname.'_thumb.'.$extname;

			$typename = exif_imagetype($tmp_name);
			if (in_array($typename, array(1, 2, 3, 18))) // gif, jpg, png, webp
			{
				if (filesize($tmp_name) <= $config['maxfile'])
				{
				 	if (move_uploaded_file($tmp_name, DNDIR.$dirimg.$imgname))
					{
						require DNDIR.'/core/classes/Image.php';
						$img = new Image();
						$img->start();

						if ($conf['injpg'] == 'yes' AND $extname != 'jpg')
						{
							$img->imgconvert(DNDIR.$dirimg.$imgname, DNDIR.$dirimg.$newname.'.jpg');
							$imgname = $newname.'.jpg';
							$imgname_thumb = $newname.'_thumb.jpg';
						}

						$img->createthumb
								(
									DNDIR.$dirimg.$imgname,
									DNDIR.$dirimg,
									$imgname,
									$config['wbig'],
									$config['hbig'],
									'symm'
								);

						$img->createthumb
								(
									DNDIR.$dirimg.$imgname,
									DNDIR.$dirimg,
									$imgname_thumb,
									$config['width'],
									$config['height'],
									$config['resize']
								);
					}
					else
					{
						$tm->error($lang['down_na_title'].' Not move_uploaded_file', 0);
					}
				}
				else
				{
					$tm->error($lang['down_na_title'].' Not MAX_FILE_SIZE', 0);
				}
			}
			else
			{
				$tm->error($lang['down_na_title'].' Not imagetype', 0);
			}
		}
		else
		{
			$tm->error($lang['down_na_title'].' Not is_uploaded_file', 0);
		}
	}
	else
	{
		$tm->error($lang['not_selected'], 0);
	}

	/**
	 * Добавление без модерации
	 * Только для группы "Публикатор"
	 */
	if ($ins['active'])
	{
		$db->query
			(
				"INSERT INTO ".$basepref."_".WORKMOD." VALUES (
				 NULL,
				 '".$catid."',
				 '".NEWTIME."',
				 '0',
				 '0',
				 '".$cpu."',
				 '".$db->escape($title)."',
				 '".$db->escape($title)."',
				 '".$db->escape($text)."',
				 '".$db->escape($customs)."',
				 '',
				 '".$db->escape($descript)."',
				 '".$db->escape($dirimg.$imgname)."',
				 '".$db->escape($dirimg.$imgname_thumb)."',
				 '".$db->escape($image_alt)."',
				 '0',
				 'yes',
				 '0',
				 '0',
				 'all',
				 '',
				 '0',
				 '',
				 '".$usermain['uname']."',
				 '0'
				 )"
			);

			$counts = new Counts(WORKMOD, 'id');

			// Сообщение, ОК
			$tm->message($lang['public_photo'], 0, 0);
	}
	else
	{
		$db->query
			(
				"INSERT INTO ".$basepref."_".WORKMOD."_user VALUES (
				 NULL,
				 '".$catid."',
				 '".$usermain['userid']."',
				 '".NEWTIME."',
				 '".$db->escape($title)."',
				 '".$db->escape($text)."',
				 '".$db->escape($dirimg.$imgname)."',
				 '".$db->escape($dirimg.$imgname_thumb)."'
				)"
			);
	}

	/**
	 * Сообщение на E-Mail
	 */
	if ($conf['mailadd'] == 'yes')
	{
		$subject = $global['modname'].': '.$lang['photo_subject'];
		$message = this_text(array
						(
							"br"    => "\r\n",
							"title" => $title,
							"text"  => $text,
							"date"  => $api->sitetime(NEWTIME, 1, 1)
						),
						$lang['photo_msgtext']);

			send_mail($config['site_mail'], $subject, $message, $config['site']." <robot.".$config['site_mail'].">");
	}

	/**
	 * Сообщение, модерация
	 */
	$tm->message($lang['moder_photo'], 0);
}
