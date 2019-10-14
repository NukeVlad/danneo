<?php
/**
 * File:        /mod/media/index.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $to, $p,
		$global, $captcha, $title, $sendnames, $sendtexts, $id, $cid, $respon, $ccpu;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Метки
 */
$legaltodo = array('index', 'cat', 'page', 'image', 'video');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Главная
 ------------*/
if ($to == 'index')
{
	$ins = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['maincol'] * ($p - 1);

	$total = $db->fetchassoc($db->query("SELECT COUNT(catid) AS total FROM ".$basepref."_".WORKMOD."_cat WHERE act = 'yes'"));

	/**
	 * Ошибка листинга
	 */
	$nums = ceil($total['total'] / $conf['maincol']);
	if ($p > $nums AND $p != 1) {
		$tm->noexistprint();
	}

	/**
	 * Свой TITLE
	 */
	if (isset($config['mod'][WORKMOD]['custom']) AND ! empty($config['mod'][WORKMOD]['custom']))
	{
		define('CUSTOM', $config['mod'][WORKMOD]['custom'].$seopage);
	} else {
		$global['title'] = $global['modname'].$seopage;
	}

	/**
	 * Мета данные
	 */
	$global['descript'] = ( ! empty($config['mod'][WORKMOD]['descript'])) ? $config['mod'][WORKMOD]['descript'].$seopage : '';
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
	$global['insert']['current'] = $global['insert']['breadcrumb'] = $global['modname'];

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

    /**
	 * Переключатели
	 */
	$tm->unmanule['date'] = $conf['date'];

    /**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array('thumb' => null, 'pagesout'	=> null);

	$inq = $db->query
			(
				"SELECT a.*, COUNT(b.id) AS total FROM ".$basepref."_".WORKMOD."_cat AS a LEFT JOIN ".$basepref."_".WORKMOD." AS b ON (a.catid = b.catid)
				 WHERE a.act = 'yes'
				 AND (a.stpublic = 0 OR a.stpublic < '".NEWTIME."')
				 AND (a.unpublic = 0 OR a.unpublic > '".NEWTIME."')
				 GROUP BY a.catid ORDER BY a.posit ASC LIMIT ".$s.", ".$conf['maincol']
			);

	if ($db->numrows($inq) > 0)
	{
		/**
		 * Листинг страниц
		 */
		$ins['pages'] = $tm->manuale['pagesout'] = null;
		if ($total['total'] > $conf['maincol'])
		{
			$ins['pages'] = $tm->parse(array
									(
										'text' => $lang['all_pages'],
										'pages' => $api->pages(WORKMOD."_cat WHERE act = 'yes'", 'catid', 'index', WORKMOD.'&amp;to=index', $conf['maincol'], $p)
									),
									$tm->manuale['pagesout']);
		}

		/**
		 * Шаблон
		 */
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/standart'));

		$ins['content'] = array();
		while ($item = $db->fetchassoc($inq))
		{
			$tm->unmanule['date'] = $conf['date'];

			// URL
			$ins['catcpu'] = (defined('SEOURL') AND $item['catcpu']) ? '&amp;ccpu='.$item['catcpu'] : '';
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Изображение
			if ( ! empty($item['icon']))
			{
				$ins['alt']   = ( ! empty($item['listname'])) ? $api->siteuni($item['listname']) : '';
				$ins['image'] = $tm->parse(array(
										'thumb' => $item['icon'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];

			// Вывод
			$ins['content'][] = $tm->parse(array
									(
										'title'		=> $api->siteuni($item['listname']),
										'date'		=> $ins['public'],
										'text'		=> $api->siteuni($item['listdesc']),
										'public'	=> $lang['all_data'],
										'langcol'	=> $lang['all_col'],
										'total'		=> $item['total'],
										'langhits'	=> $lang['all_hits'],
										'hits'		=> $item['hits'],
										'image'		=> $ins['image'],
										'url'		=> $ins['url']
									),
									$ins['template']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(
			array(
				'content' => $ins['output'],
				'pages' => $ins['pages']
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/index'))
		);
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Отдельная презентация
 --------------------------*/
if ($to == 'cat')
{
	$ins = array();
	$id = preparse($id, THIS_INT);

	/**
	 * Презентация, с учётом чпу
	 */
	if ( ! empty($ccpu) AND preparse($ccpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$ccpu = preparse($ccpu, THIS_TRIM, 0, 255);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat WHERE catcpu = '".$db->escape($ccpu)."'");
		$v = 0;
	}
	else
	{
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat WHERE catid = '".$id."'");
		$v = 1;
	}

	$list = $db->fetchassoc($valid);

	/**
	 * Страницы не существует
	 */
	if ( ! empty($list['catcpu']) AND defined('SEOURL') AND $v) {
		$tm->noexistprint();
	}
	if ($db->numrows($valid) == 0) {
		$tm->noexistprint();
	}

	/**
	 * Содержимое презентации
	 */
	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD."
				 WHERE catid = '".$list['catid']."' AND act = 'yes' ORDER BY posit ASC"
			);

	/**
	 * Всего фото-видео
	 */
	$total = $db->fetchassoc
				(
					$db->query
					(
						"SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD."
						 WHERE act = 'yes' AND catid = '".$list['catid']."'"
					)
				);

	/**
	 * Обновляем количество просмотров
	 */
	$db->query("UPDATE ".$basepref."_".WORKMOD."_cat SET hits = hits + 1 WHERE catid = '".$list['catid']."'");

	/**
	 * Свой TITLE
	 */
	if ( ! empty($list['customs'])) {
		define('CUSTOM', $api->siteuni($list['customs']));
	} else {
		$global['title'] = $api->siteuni($list['listname']);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($list['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($list['keywords']) : $api->seokeywords($list['listname'].' '.$list['listdesc'], 5, 35);
	$global['descript'] = (preparse($list['descript'], THIS_EMPTY) == 0) ? $api->siteuni($list['descript']) : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = (defined('CUSTOM')) ? $api->siteuni($list['customs']) : $api->siteuni($list['listname']);
	if ( ! empty($list['listdesc'])) {
		$global['og_desc'] = $api->siteuni($list['listdesc']);
	} elseif ( ! empty($list['descript'])) {
		$global['og_desc'] = $api->siteuni($list['descript']);
	}
	$global['og_image'] = ( ! empty($list['icon'])) ? SITE_URL.'/'.$list['icon'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	$global['insert']['current'] = $api->siteuni($list['listname']);
	$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>', $api->siteuni($list['listname']));

	/**
	 * Ограничение доступа
	 */
	if($list['access'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($list['groups']))
		{
			$group = Json::decode($list['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Переключатели
	 */
	$tm->unmanule['date'] = $conf['date'];

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/view'));

	/**
	 * CPU
	 */
	$ins['catcpu'] = (defined('SEOURL') AND $list['catcpu']) ? '&amp;ccpu='.$list['catcpu'] : '';

	$ins['media'] = null;
	if ($db->numrows($inq) > 0)
	{
		$thumb = $tm->create('mod/'.WORKMOD.'/thumb');

		while ($item = $db->fetchassoc($inq))
		{
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

			$ins['thumb'] = DNROOT.$item['image_thumb'];
			$ins['alt'] = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

			$ins['mediain'][] = $tm->parse(array
									(
										'url'   => $ins['url'],
										'alt'   => $ins['alt'],
										'img'   => $ins['thumb'],
										'title' => $api->siteuni($item['title']),
										'text'  => $api->siteuni($item['text'])
									),
									$thumb);
		}
		$ins['media'] = $tm->tableprint($ins['mediain'], $list['listcol']);
	}

	// Дата
	$ins['public'] = ($list['stpublic'] > 0) ? $list['stpublic'] : $list['public'];

	// Подзаголовок
	$ins['subtitle'] = ( ! empty($list['subtitle'])) ? $api->siteuni($list['subtitle']) : $api->siteuni($list['listname']);

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'title'		=> $api->siteuni($list['listname']),
			'subtitle'	=> $ins['subtitle'],
			'desc'		=> $list['listdesc'],
			'text'		=> $list['listtext'],
			'date'		=> $ins['public'],
			'public'	=> $lang['all_data'],
			'langcol'	=> $lang['all_col'],
			'total'		=> $total['total'],
			'langhits'	=> $lang['all_hits'],
			'hits'		=> $list['hits'],
			'media'		=> $ins['media']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка page
 * -------------- */
if ($to == 'page')
{
	$obj = $ins = $area = array();
	$id = preparse($id, THIS_INT);

	/**
	 * Переменные
	 */
	$ins = array(
		'social' => '',
		'size'   => '',
		'video'  => '',
		'srows'  => ''
	);

	/**
	 * Запрос, ЧПУ или без
	 */
	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE cpu = '".$db->escape($cpu)."' AND act = 'yes'");
		$v = 0;
	}
	else
	{
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes'");
		$v = 1;
	}

	$item = $db->fetchassoc($valid);

	/**
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	while ($c = $db->fetchassoc($inq, $config['cache']))
	{
		$area[$c['catid']] = $c;
	}
	$ins['catcpu'] = (defined('SEOURL') AND $item['catid'] > 0) ? $area[$item['catid']]['catcpu'] : '';

    /**
     * Ошибка страницы
     */
	if ($db->numrows($valid) == 0)
	{
		$tm->noexistprint();
	}
	elseif ( ! empty($item['cpu']) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}
	elseif ( ! isset($ccpu) AND $ins['catcpu'] OR isset($ccpu) AND $ins['catcpu'] != $ccpu)
	{
		$tm->noexistprint();
	}

    /**
     * Данные категории
     */
	if (isset($area[$item['catid']])) {
		$obj = $area[$item['catid']];
	} else {
		$obj = array
		(
			'catid'    => '',
			'catcpu'   => '',
			'listname' => '',
			'icon'     => '',
			'access'   => '',
			'groups'   => ''
		);
	}

	/**
	 * Свой TITLE
	 */
	if (isset($item['customs']) AND ! empty($item['customs'])) {
		define('CUSTOM', $api->siteuni($item['customs']));
	} else {
		$global['title'] = preparse($item['title'], THIS_TRIM);
		$global['title'].= (empty($obj['listname'])) ? '' : ' - '.$obj['listname'];
	}

	/**
	 * Мета данные
	 */
    $global['keywords'] = (empty($item['keywords'])) ? $api->seokeywords($item['title'].' '.$item['text'], 5, 35) : $item['keywords'];
    $global['descript'] = (empty($item['descript'])) ? '' : $item['descript'];

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($item['title'])) ? $api->siteuni($item['title']) : '';
	$global['og_desc'] = ( ! empty($item['text'])) ? $api->siteuni($item['descript']) : $api->siteuni($item['text']);
	$global['og_image'] = ( ! empty($item['image_thumb'])) ? SITE_URL.'/'.$item['image_thumb'] : '';

	/**
	 * Меню, хлебные крошки, с учетом категории
	 */
	if ($item['catid'] > 0) {
		$api->catcache = $area;
		$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
		$global['insert']['breadcrumb'] = $api->sitecat($item['catid'], 0, 'listname');
	} else {
		$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
		$global['insert']['breadcrumb'] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>';
	}

	/**
	 * Ограничение доступа
	 */
	if($obj['access'] == 'user')
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
		if (defined('GROUP_ACT') AND ! empty($obj['groups']))
		{
			$group = Json::decode($obj['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * CPU
	 */
	$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
	$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

	/**
	 * Переключатели
	 */
	$tm->unmanule['lightbox'] = 'no';
	$tm->unmanule['text'] = ( ! empty($item['text'])) ? 'yes' : 'no';
	$tm->unmanule['image'] = ( ! empty($item['image'])) ? 'yes' : 'no';
	$tm->unmanule['video'] = ( ! empty($item['video'])) ? 'yes' : 'no';

    /**
	 * Вложенные шаблоны
	 */
	$tm->manuale['social'] = null;

	/**
	 * Видео
	 */
	if ( ! empty($item['video']))
	{
		$ins['video'] = (substr($item['video'], 0, 7) == 'http://') ? $item['video'] : DNROOT.$item['video'];
	}

	/**
	 * Размер изображения для Lightbox
	 */
	$conf['lightbox'] = 500;
	if (
		! empty($item['image']) AND
		file_exists(DNDIR.$item['image']) AND
		$size = getimagesize(DNDIR.$item['image'])
	) {
		if ($size[0] > $conf['lightbox'])
		{
			$tm->unmanule['lightbox'] = 'yes';

			$height = intval(($conf['lightbox'] / $size[0]) * $size[1]);
			$width  = intval(($height / $size[1]) * $size[0]);
			$ins['size'] = ' height="'.$height.'" width="'.$width.'"';
		}
	}

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/open'));

	/**
	 * Социальные закладки
	 */
	if ($config['social_bookmark'] == 'yes')
	{
		$ins['tempsocial']= $tm->parsein($tm->create('mod/'.WORKMOD.'/social'));
		$l = Json::decode($config['social']);
		if (is_array($l))
		{
			foreach ($l as $k => $v)
			{
				$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';
				$url = $ro->seo('index.php?dn='.WORKMOD.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu'], true);
				$url = urlencode(stripslashes($url));
				$title = urlencode(stripslashes($item['title']));
				$link = str_replace(array('{link}', '{title}'), array($url, $title), $v['link']);
				if ($v['act'] == 'yes')
				{
					$ins['srows'] .= $tm->parse(array
											(
												'link' => $link,
												'icon' => $v['icon'],
												'alt'  => $v['alt']
											),
											$tm->manuale['social']);
				}
			}
			$ins['social'] = $tm->parse(array('socialrows' => $ins['srows']), $ins['tempsocial']);
		}
	}

	// Подзаголовок
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'title'		=> $api->siteuni($item['title']),
			'subtitle'	=> $ins['subtitle'],
			'text'		=> $api->siteuni($item['text']),
			'alt'		=> $api->siteuni($item['image_alt']),
			'social'	=> $ins['social'],
			'langhits'	=> $lang['all_hits'],
			'langname'	=> $lang['all_name'],
			'catname'	=> $obj['listname'],
			'descript'	=> $lang['descript'],
			'thumb'		=> $item['image_thumb'],
			'image'		=> $item['image'],
			'size'		=> $ins['size'],
			'video'		=> $ins['video']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Фото
 ----------*/
if ($to == 'image')
{
	$id = preparse($id, THIS_INT);
	$item = $db->fetchassoc($db->query("SELECT image FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."'"));
	$ins['size'] = '';
	if (file_exists(DNDIR.$item['image']) AND $size = getimagesize(DNDIR.$item['image']))
	{
		$width = intval($size[0]);
		$height = intval($size[1]);
		$ins['size'] = ' style="width:'.$width.'px; height:'.$height.'px"';
	}
	$tm->parseprint(array('image' => $item['image'], 'size' => $ins['size']), $tm->parsein($tm->create('mod/'.WORKMOD.'/image')));
}
/**
 * Видео
 ----------*/
if ($to == 'video')
{
	$id = preparse($id, THIS_INT);
	$item = $db->fetchassoc($db->query("SELECT video FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."'"));
	$tm->parseprint(array('video' => $item['video']), $tm->parsein($tm->create('mod/'.WORKMOD.'/video')));
}
