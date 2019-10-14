<?php
/**
 * File:        /mod/pages/index.php
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
global $tm, $ro, $to, $pa, $cpu, $p, $config, $global, $api;

/**
 * Константы, Рабочий мод
 */
define('WORKMOD', ($global['dn'] == 'pages') ? 'pages' : $global['dn']);
define('DIRMOD', ($global['dn'] == 'pages') ? DNDIR.'cache/pages' : DNDIR.'cache/pages/'.WORKMOD);

/**
 * Проверка меток
 */
$to = (isset($cpu) OR $to == 'page') ? 'page' : 'index';

/**
 * Главная
 * ---------- */
if ($to == 'index')
{
	$ins = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$sp = isset($p) ? 1 : 0;
	$p = preparse($p, THIS_INT);

	/**
	 * Файл с данными
	 */
	if (WORKMOD != 'pages')
	{
		$fileshort = DIRMOD.'/'.WORKMOD.'.short.php';
		if (is_file($fileshort) AND file_exists($fileshort)) {
			$pshort = include($fileshort);
			$count = count($pshort);
		}
	}
	else
	{
		$tm->noexistprint();
	}

	/**
	 * Ошибка листинга
	 */
	$cols = (isset($count) AND $count != 0) ? ceil($count / $config[WORKMOD]['pagcol']) : 0;
	if ($p > $cols AND $p != 1)
	{
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
	$tm->unmanule['date'] = (isset($config[WORKMOD]['date']) AND $config[WORKMOD]['date'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['desc'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? 'yes' : 'no';
	$tm->unmanule['link'] = 'yes';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale['thumb'] = null;

	/**
	 * Описание раздела
	 */
	$ins['map'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? $config['mod'][WORKMOD]['map'] : '';

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/pages/standart'));

	if (isset($pshort) AND ! empty($pshort) AND is_array($pshort))
	{
		/**
		 * Сортировка (по ID в обратном порядке | DESC)
		 */
		krsort($pshort);

		/**
		 * Формирование постраничной разбивки
		 */
		$pag = new Pagination('index', 'pages&pa='.WORKMOD.'&to=index', $p, $config[WORKMOD]['pagcol']);
		$arrays = $pag->iterator($pshort);

		$ins['content'] = array();
		foreach ($arrays as $item)
		{
			$ins['image'] = NULL;

			// Изображение
			if ( ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $item['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Ссылка
			$ins['url'] = $ro->seo('index.php?dn=pages&amp;pa='.WORKMOD.'&amp;cpu='.$item['cpu']);

			$ins['content'][] = $tm->parse(array
				(
					'mod'   => '', // not
					'title' => $api->siteuni($item['title']),
					'date'  => $item['public'],
					'text'  => $api->siteuni($item['textshort']),
					'image' => $ins['image'],
					'url'   => $ins['url'],
					'read'  => $lang['in_detail']
				),
				$ins['template']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $config[WORKMOD]['indcol']);

		/**
		 * Вывод
		 */
		$tm->parseprint(array
			(
				'descript'	=> $ins['map'],
				'content'	=> $ins['output'],
				'pages'		=> $pag->output($count),
				'search'	=> $tm->search($config[WORKMOD]['search'], 'pages', 1)
			),
			$tm->parsein($tm->create('mod/pages/index'))
		);
	}
	else
	{
		$tm->message($lang['data_not'], 0, 0, 1);
	}

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
	$data = $seo = $mod = array();

	/**
	 * CPU->ID
	 */
	if (file_exists(DIRMOD.'/'.WORKMOD.'.id.php'))
	{
		$id = include(DIRMOD.'/'.WORKMOD.'.id.php');
	}

	/**
	 * ID страницы
	 */
	$id = isset($id[$cpu]) ? $id[$cpu] : NULL;

	/**
	 * Файл страницы
	 */
	$page = DIRMOD.'/'.WORKMOD.'.'.$id.'.php';

	/**
	 * Страницы не существует
	 */
	if ( ! file_exists($page) OR ! isset($id) OR empty($id))
	{
		$tm->noexistprint();
	}

	/**
	 * Массив с данными страницы
	 */
	$data = include($page);

	/**
	 * Страницы не существует
	 */
	if ($data['act'] == 'no')
	{
		$tm->noexistprint();
	}

	/**
	 * Ссылка, параметр мода
	 */
	$ins['pamod'] = (WORKMOD != 'pages') ? '&amp;pa='.WORKMOD : '';

	/**
	 * Ссылка на страницу
	 */
	$ins['url'] = $ro->seo('index.php?dn=pages'.$ins['pamod'].'&amp;cpu='.$data['cpu']);

	/**
	 * Свой TITLE
	 */
	if ( ! empty($data['customs'])) {
		define('CUSTOM', $api->siteuni($data['customs']));
	} else {
		$global['title'] = preparse($data['title'], THIS_TRIM);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($data['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($data['keywords']) : '';
	$global['descript'] = (preparse($data['descript'], THIS_EMPTY) == 0) ? $api->siteuni($data['descript']) : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($data['title'])) ? $api->siteuni($data['title']) : '';
	$global['og_desc'] = ( ! empty($data['textshort'])) ? $api->siteuni($data['descript']) : $api->siteuni($data['textshort']);
	$global['og_image'] = ( ! empty($data['image_thumb'])) ? SITE_URL.'/'.$data['image_thumb'] : '';

	/**
	 * Меню, хлебные крошки
	 */
	if (WORKMOD == 'pages') {
		$global['insert']['current'] = $global['insert']['breadcrumb'] = $data['title'];
	} else {
		$global['insert']['current'] = $data['title'];
		$global['insert']['breadcrumb'] = array('<a href="'.$ro->seo('index.php?dn=pages&pa='.WORKMOD).'">'.$global['modname'].'</a>', $data['title']);
	}

	/**
	 * Ограничение доступа к странице
	 */
	if($data['acc'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
		}
		if (defined('GROUP_ACT') AND ! empty($data['groups']))
		{
			$group = Json::decode($data['groups']);
			if ( ! isset($group[$usermain['gid']]))
			{
				$tm->norightprint();
			}
		}
	}

	/**
	 * Ограничение доступа к файлам
	 */
	$facc = $fgroups = TRUE;
	if (isset($config['mod']['user']))
	{
		if ( ! empty($data['files']) AND $data['facc'] == 'user')
		{
			if ( ! defined('USER_LOGGED'))
			{
				$facc = FALSE;
			}
			elseif ( ! empty($data['fgroups']))
			{
				$group = Json::decode($data['fgroups']);
				if ( ! isset($group[$usermain['gid']]))
				{
					$fgroups = FALSE;
				}
			}
		}
	}

	/**
	 * Переменные
	 */
	$ins['file'] = $ins['files'] = $ins['filenotice'] = $ins['textmore'] = $ins['srows'] = $ins['social'] = '';

	/**
	 * Сссылка, Печать
	 */
	$ins['print_url'] = $ro->seo('index.php?dn=pages&amp;pa='.WORKMOD.'&amp;re=print&amp;id='.$id);

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Переключатели
	 */
	$tm->unmanule['date'] = ($config[WORKMOD]['date'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['print'] = ($config[WORKMOD]['print'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['redate'] = ($config[WORKMOD]['date'] == 'yes' AND $data['uppublic'] != 0) ? 'yes' : 'no';
	$tm->unmanule['files'] = ( ! empty($data['files']) AND $facc AND $fgroups) ? 'yes' : 'no';

	/**
	 * Вложенные шаблоны
	 */
	$tm->manuale = array
		(
			'files' => null,
			'social' => null
		);

	/**
	 * Содержимое
	 */
	$ins['textshort'] = $api->siteuni($data['textshort']);
	$ins['textmore']  = $api->siteuni($data['textmore']);

	/**
	 * Шаблон оформления
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/pages/read'));

	/**
	 * Прикрепленные файлы
	 */
	if ( ! empty($data['files']))
	{
		// Сообщение
		$tm->unmanule['fgroups'] = ($fgroups) ? 'yes' : 'no';
		$ins['temp_notice'] = $tm->parsein($tm->create('mod/pages/files.notice'));

		if ( ! $facc OR ! $fgroups)
		{
			$ins['filenotice'] = $tm->parse(array(
										'langdown'	=> $lang['block_down'],
										'text'		=> $lang['access_file'],
										'login'		=> $ro->seo('index.php?dn=user&amp;re=login'),
										'enter'		=> $lang['user_login']
									),
									$ins['temp_notice']);
		}

		// Файлы
		$fs = Json::decode($data['files']);
		if (is_array($fs) AND $facc AND $fgroups)
		{
			$trl = new Translit();
			$ins['temp_file'] = $tm->parsein($tm->create('mod/pages/files'));

			foreach ($fs as $k => $v)
			{
				$fname = $trl->title($trl->process($v['title']));
				$loads = $ro->seo('index.php?dn=pages'.$ins['pamod'].'&amp;re=load&amp;id='.$id.'&amp;fid='.$k.'&amp;ds='.$fname);

				$ins['file'].= $tm->parse(array(
										'key'   => $k,
										'path'  => $loads,
										'title' => $v['title']
									),
									$tm->manuale['files']);
			}

			$ins['files'] = $tm->parse(array(
										'langdown'	=> $lang['block_down'],
										'filerows'	=> $ins['file']
									),
									$ins['temp_file']);
		}
	}

	/**
	 * Вводное изображение
	 */
	$ins['float'] = ($data['image_align'] == 'left') ? 'imgleft' : 'imgright';
	$ins['alt']   = ( ! empty($data['image_alt'])) ? $api->siteuni($data['image_alt']) : '';

	$tm->unmanule['image'] = ( ! empty($data['image'])) ? 'yes' : 'no';
	$ins['temp_thumb'] = $tm->parsein($tm->create('mod/pages/thumb'));

	$ins['image'] = NULL;
	if ( ! empty($data['image_thumb']))
	{
		$ins['image'] = $tm->parse(array
							(
								'float' => $ins['float'],
								'thumb' => $data['image_thumb'],
								'image' => $data['image'],
								'alt'   => $ins['alt']
							),
							$ins['temp_thumb']);
	}

	/**
	 * Изображения по тексту
	 */
	if ( ! empty($data['images']))
	{
		$im = Json::decode($data['images']);
		if (is_array($im))
		{
			foreach ($im as $k => $v)
			{
				$ins['float'] = 'imgtext-'.$v['align'];
				$ins['alt']   = ( ! empty($v['alt'])) ? $api->siteuni($v['alt']) : '';

				$tm->unmanule['image'] = ( ! empty($v['image'])) ? 'yes' : 'no';
				$ins['temp_thumb'] = $tm->parsein($tm->create('mod/pages/thumb'));

				if ( ! empty($v['thumb']))
				{
					$ins['img'] = $tm->parse(array
						(
							'float' => $ins['float'],
							'thumb' => $v['thumb'],
							'image' => $v['image'],
							'alt'   => $ins['alt']
						),
						$ins['temp_thumb']);
				}

				// Содержимое
				$ins['textmore'] = $tm->parse(array('img'.$k => $ins['img']), $ins['textmore']);
			}
		}
	}

	/**
	 * Перелинковка
	 */
 	if ($config['anchor'] == 'yes' AND $config['mod'][WORKMOD]['seo'] == 'yes')
	{
		$array_links = DNDIR.'cache/cache.seo.php';
		if (file_exists($array_links))
		{
			include($array_links);
			if (! empty($seo) AND isset($seo[WORKMOD]))
			{
				foreach ($seo[WORKMOD] as $val)
				{
					$seolink = seo_link($val['link']);
					if (isset($seolink))
					{
						$ins['textshort'] = preg_replace
												(
													'/([^\<\>])'.$val['word'].'(?![^<]*>)(?=\W|$)/um',
													' <a href="'.$seolink.'" title="'.$val['title'].'">'.$val['word'].'</a>',
													$ins['textshort'],
													$val['count'],
													$done
												);
						$ins['textmore'] = preg_replace
												(
													'/([^\<\>])'.$val['word'].'(?![^<]*>)(?=\W|$)/um',
													' <a href="'.$seolink.'" title="'.$val['title'].'">'.$val['word'].'</a>',
													$ins['textmore'],
													$val['count'] - $done
												);
					}
				}
			}
		}
	}

	/**
	 * Социальные закладки
	 */
	if (
		$config['social_bookmark'] == 'yes' AND
		isset($config[WORKMOD]['social']) AND
		$config[WORKMOD]['social'] == 'yes'
	)
	{
		// Шаблон
		$ins['tempsocial']= $tm->parsein($tm->create('mod/pages/social'));

		$l = Json::decode($config['social']);
		if (is_array($l))
		{
			foreach ($l as $k => $v)
			{
				$url = $ro->seo('index.php?dn=pages'.$ins['pamod'].'&amp;cpu='.$data['cpu'], true);
				$url = urlencode(stripslashes($url));
				$title = urlencode(stripslashes($data['title']));
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

			// Вывод
			$ins['social'] = $tm->parse(array('socialrows' => $ins['srows']), $ins['tempsocial']);
		}
	}

	/**
	 * Поиск
	 */
	$ins['search'] = $tm->search($config[WORKMOD]['search'], 'pages', 1);

	/**
	 * Подзаголовок
	 */
	$ins['title'] = ( ! empty($data['subtitle'])) ? $data['subtitle'] : $data['title'];

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'title'		=> $ins['title'],
			'date'		=> $data['public'],
			'redate'	=> $data['uppublic'],
			'updata'	=> $lang['all_updata'],
			'public'	=> $lang['all_data'],
			'image'		=> $ins['image'],
			'textshort'	=> $ins['textshort'],
			'textmore'	=> $ins['textmore'],
			'print_url'	=> $ins['print_url'],
			'print'		=> $lang['print_link'],
			'files'		=> $ins['files'],
			'filenotice'=> $ins['filenotice'],
			'search'	=> $ins['search'],
			'social'	=> $ins['social'],
			'link'		=> $ins['url']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
