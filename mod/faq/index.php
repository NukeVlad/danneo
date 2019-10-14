<?php
/**
 * File:        /mod/faq/index.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Глобальные мода
 */
global	$db, $basepref, $config, $lang, $usermain, $tm,
		$api, $global, $to, $p, $id, $selective;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Метки
 */
$legaltodo = array('index', 'cat', 'page');

/**
 * Проверка меток
 */
$to = (isset($to) AND in_array($api->sitedn($to), $legaltodo)) ? $api->sitedn($to) : 'index';

/**
 * Метка index
 * -------------- */
if ($to == 'index')
{
	$obj = $ins = $area = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	/**
	 * Общее количество вопросов без категории
	 */
	$total = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE catid = 0 AND act = 'yes'"));

	/**
	 * Ошибка листинга
	 */
	$nums = ceil($total['total'] / $conf['pagcol']);
	if ($p > $nums AND $p != 1)
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
	$global['keywords'] = (preparse($config['mod'][WORKMOD]['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['keywords']) : '';
	$global['descript'] = (preparse($config['mod'][WORKMOD]['descript'], THIS_EMPTY) == 0) ? $api->siteuni($config['mod'][WORKMOD]['descript'].$seopage) : '';

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
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['total'] = $db->numrows($inq, $config['cache']);
	while ($item = $db->fetchrow($inq, $config['cache']))
	{
		$area[$item['parentid']][$item['catid']] = $item;
		$obj[$item['catid']] = $item;
	}

	if ($conf['catmain'] == 'yes')
	{
		if ( ! empty($area))
		{
			$api->subcatcache = $area;
			$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat'));

			$api->printsitecat(0);
			if ( ! empty($api->print))
			{
				$stat = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'"));

				$catprint = $tm->tableprint($api->print, $conf['catcol']);

				$tm->parseprint(array
					(
						'cd'         => $lang['cat_desc'],
						'lang_icon'  => $lang['all_icon'],
						'lang_col'   => $lang['all_col'],
						'lang_total' => $lang['all_faq'],
						'lang_cat'   => $lang['all_cats'],
						'catprint'   => $catprint,
						'total'      => $stat['total'],
						'cats'       => $ins['total']
					),
					$ins['template']);
			}
		}
	}

	$cont = ( ! empty($area) AND $conf['catmain'] == 'yes') ? TRUE : FALSE;

	/**
	 * Последние вопросы
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE catid <> '0' AND act = 'yes' ORDER BY public ASC LIMIT ".$conf['pagmain']);

	if ($db->numrows($inq) > 0)
	{
		// Заголовок
		($cont) ? $tm->parseprint(array('title' => $lang['new_faq'], 'count' => '', 'class' => 'new'), $tm->manuale['subtitle']) : '';

		$ins['ask'] = $ins['ans'] = $ins['date'] = $ins['author'] = $ins['pages'] = '';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/read'));

		while ($item = $db->fetchrow($inq))
		{
			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$pages = ($total['total'] > $conf['pagcol']) ? '&amp;to=index&amp;p='.$p : '';
			$ins['link'] = $ro->seo('index.php?dn='.WORKMOD.$pages.'&amp;id='.$item['id'].$ins['cpu']);

			$title = mb_substr($api->siteuni($item['quest']), 0, $conf['maxsymbol']);
			$anchor = (defined('SEOURL') AND ! empty($item['cpu'])) ? $item['cpu'] : $item['id'];

			// Автор
			if ($conf['author'] == 'yes')
			{
				$ins['author'] = $tm->parse(array('author' => $api->siteuni($item['author'])), $tm->create('mod/'.WORKMOD.'/author'));
			}

			// Дата
			if ($conf['date'] == 'yes')
			{
				$ins['date'] = $tm->parse(array(
										'langask' => $lang['faq_anspublic'],
										'date'    => $item['spublic'],
										'langans' => $lang['all_data'],
										'redate'  => $item['public']
									),
									$tm->create('mod/'.WORKMOD.'/date'));
			}

			// Заголовки (вопросы)
			$ins['ask'] .= $tm->parse(array
								(
									'link'   => $ins['link'],
									'name'   => $anchor,
									'title'  => $api->siteuni($item['quest'])
								),
								$tm->manuale['quest']);

			// Ответы
			$ins['ans'] .= $tm->parse(array(
									'name'   => $anchor,
									'author' => $ins['author'],
									'time'   => $ins['date'],
									'title'  => $title,
									'text'   => $api->siteuni($item['answer'])
								),
								$tm->manuale['answer']);
		}

		// Вывод
		$tm->parseprint(array
			(
				'insertask' => $ins['ask'],
				'insertans' => $ins['ans'],
				'pages'     => $ins['pages']
			),
			$ins['template']);
	}

	/**
	 * Вопросы без категории
	 */
	if ($total['total'] > 0)
	{
		$ins['ask'] = $ins['ans'] = $ins['date'] = $ins['author'] = '';

		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE catid = '0' AND act = 'yes' ORDER BY public ASC LIMIT ".$s.", ".$conf['pagcol']);

		// Заголовок
		if ( ! empty($area) AND ($db->numrows($inq) <> 0))
		{
			$tm->parseprint(array('title' => $lang['faq_nocat'], 'count' => '', 'class' => 'new'), $tm->manuale['subtitle']);
		}

		// Листинг
		$ins['pages'] = ($total['total'] > $conf['pagcol']) ? $api->pages('', '', 'index', WORKMOD.'&amp;to=index', $conf['pagcol'], $p, $total['total']) : '';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/read'));

		while ($item = $db->fetchrow($inq))
		{
			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$pages = ($total['total'] > $conf['pagcol']) ? '&amp;to=index&amp;p='.$p : '';
			$ins['link'] = $ro->seo('index.php?dn='.WORKMOD.$pages.'&amp;id='.$item['id'].$ins['cpu']);

			$title = mb_substr($api->siteuni($item['quest']), 0, $conf['maxsymbol']);
			$anchor = (defined('SEOURL') AND ! empty($item['cpu'])) ? $item['cpu'] : $item['id'];

			// Автор
			if ($conf['author'] == 'yes')
			{
				$ins['author'] = $tm->parse(array('author' => $api->siteuni($item['author'])), $tm->create('mod/'.WORKMOD.'/author'));
			}

			// Дата
			if ($conf['date'] == 'yes')
			{
				$ins['date'] = $tm->parse(array(
										'langask'	=> $lang['faq_anspublic'],
										'date'		=> $item['spublic'],
										'langans'	=> $lang['all_data'],
										'redate'	=> $item['public']
									),
									$tm->create('mod/'.WORKMOD.'/date'));
			}

			// Заголовки (вопросы)
			$ins['ask'] .= $tm->parse(array
								(
									'link'   => $ins['link'],
									'name'   => $anchor,
									'title'  => $api->siteuni($item['quest'])
								),
								$tm->manuale['quest']);

			// Ответы
			$ins['ans'] .= $tm->parse(array(
									'name'   => $anchor,
									'author' => $ins['author'],
									'time'   => $ins['date'],
									'title'  => $title,
									'text'   => $api->siteuni($item['answer'])
								),
								$tm->manuale['answer']);
		}

		// Вывод
		$tm->parseprint(array
			(
				'insertask' => $ins['ask'],
				'insertans' => $ins['ans'],
				'pages'     => $ins['pages']
			),
			$ins['template']);
	}

	/**
	 * Форма добавления
	 */
	if ($conf['addit'] == 'yes')
	{
		// Категррии
		$api->catcache = $area;

		// Управление
		$tm->unmanule['cat'] = ( ! empty($area)) ? 'yes' : 'no';
		$tm->unmanule['captcha'] = ($config['captcha'] == 'yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
		$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';

		// Отключить капчу для пользователей
		noprotectspam(0);

		// Контрольный вопрос
		$control = send_quest();

		$tm->parseprint(array
			(
				'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
				'email_name'   => $lang['email_name'],
				'email'        => $lang['e_mail'],
				'question'     => $lang['faq_question'],
				'sel'          => $api->selcat(),
				'in_cat'       => $lang['all_in_cat'],
				'cat_not'      => $lang['cat_not'],
				'all_refresh'  => $lang['all_refresh'],
				'control_word' => $lang['control_word'],
				'captcha'      => $lang['all_captcha'],
				'help_captcha' => $lang['help_captcha'],
				'help_control' => $lang['help_control'],
				'not_empty'    => $lang['all_not_empty'],
				'uname'        => $usermain['uname'],
				'umail'        => $usermain['umail'],
				'control'      => $control['quest'],
				'cid'          => $control['cid'],
				'add_question' => $lang['faq_ask'],
				'send'         => $lang['email_send']
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/form.add')));
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка cat
 * ------------ */
if ($to == 'cat')
{
	$obj = $ins = $menu = $area = array();

	$id = preparse($id, THIS_INT);
	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;

	/**
	 * Категории
	 */
	$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_cat ORDER BY posit ASC", $config['cachetime'], WORKMOD);
	$ins['total'] = $db->numrows($inq, $config['cache']);
	while ($c = $db->fetchrow($inq, $config['cache']))
	{
		$area[$c['parentid']][$c['catid']] = $menu[$c['catid']] = $obj['id'][$c['catid']] = $obj['cpu'][$c['catcpu']] = $c;
	}

	if ( ! empty($ccpu) AND preparse($ccpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$ccpu = preparse($ccpu, THIS_TRIM, 0, 255);
		$ins['catcpu'] = '&amp;ccpu='.$ccpu;
		$ins['valid'] = (isset($obj['cpu'][$ccpu]) ? 1 : 0);
		$obj = ($ins['valid'] == 1) ? $obj['cpu'][$ccpu] : 'empty';
		$v = 0;
	}
	else
	{
		$ins['catcpu'] = '';
		$ins['valid'] = (isset($obj['id'][$id]) ? 1 : 0);
		$obj = ($ins['valid'] == 1) ? $obj['id'][$id] : 'empty';
		$v = 1;
	}

	/**
	 * Страницы не существует
	 */
	if ($ins['valid'] == 0 OR $obj == 'empty') {
		$tm->noexistprint();
	}
	elseif ( ! isset($ccpu) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}

	/**
	 * Ошибка при листинге страниц
	 */
	$nums = ceil($obj['total'] / $conf['pagcol']);
	if ($p > $nums AND $p != 1) {
		$tm->noexistprint();
	}

	// Редирект на эту страницу
	$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';
	$ins['redirect'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$obj['catid'].$ins['catcpu']);
	define('REDIRECT', $ins['redirect']);

	/**
	 * Сортировки
	 */
	$ins['link']  = $ins['redirect'];
	$ins['order'] = array('asc', 'desc');
	$ins['sort']  = array('public', 'id', 'title');
	$order = ($obj['ord'] AND in_array($obj['ord'], $ins['order'])) ? $obj['ord'] : 'asc';
	$sort = ($obj['sort'] AND in_array($obj['sort'], $ins['sort'])) ? $obj['sort'] : 'id';

	/**
	 * Свой TITLE
	 */
	if (isset($obj['catcustom']) AND ! empty($obj['catcustom'])) {
		define('CUSTOM', $api->siteuni($obj['catcustom']));
	} else {
		$global['title'] = $api->siteuni($obj['catname']);
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($obj['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($obj['keywords']) : '';
	$global['descript'] = (preparse($obj['descript'], THIS_EMPTY) == 0) ? $api->siteuni($obj['descript']) : '';

	/**
	 * Меню, хлебные крошки
	 */
	$api->catcache = $menu;
	$global['insert']['current'] = $api->catcache[$obj['catid']]['catname'];
	$global['insert']['breadcrumb'] = $api->sitecat($obj['catid']);

	/**
	 * Ограничение доступа
	 */
	if ($obj['access'] == 'user')
	{
		if ( ! defined('USER_LOGGED'))
		{
			$tm->noaccessprint();
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
	 * Категории
	 */
	if ( ! empty($area))
	{
		$api->subcatcache = $area;
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat'));

		$api->printsitecat($obj['catid']);
		if ( ! empty($api->print))
		{
			$stat = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE act = 'yes'"));

			$catprint = $tm->tableprint($api->print, $conf['catcol']);

			$tm->parseprint(array
				(
					'cd'         => $lang['cat_desc'],
					'lang_icon'  => $lang['all_icon'],
					'lang_col'   => $lang['all_col'],
					'lang_total' => $lang['all_faq'],
					'lang_cat'   => $lang['all_cats'],
					'catprint'   => $catprint,
					'total'      => $stat['total'],
					'cats'       => $ins['total']
				),
				$ins['template']);
		}
	}

	/**
	 * Вывод вопросов
	 */

	$s = $conf['pagcol'] * ($p - 1);
	$finq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE catid='".$obj['catid']."' AND act = 'yes' ORDER BY ".$sort." ".$order." LIMIT ".$s.", ".$conf['pagcol']);

	if ($db->numrows($finq) > 0)
	{
		$ins['ask'] = $ins['ans'] = $ins['date'] = $ins['author'] = $ins['pages'] = '';

		// Листинг
		$ins['pages'] = ($obj['total'] > $conf['pagcol']) ? $api->pages('', '', 'index', WORKMOD.'&amp;to=cat&amp;id='.$obj['catid'].$ins['catcpu'], $conf['pagcol'], $p, $obj['total']) : '';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.WORKMOD.'/read'));

		while ($item = $db->fetchrow($finq))
		{
			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj['catcpu'])) ? '&amp;ccpu='.$obj['catcpu'] : '';

			// URL
			$pages = ($obj['total'] > $conf['pagcol']) ? '&amp;p='.$p : '';
			$ins['link'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$obj['catid'].$ins['catcpu'].$pages);

			$title = mb_substr($api->siteuni($item['quest']), 0, $conf['maxsymbol']);
			$anchor = (defined('SEOURL') AND ! empty($item['cpu'])) ? $item['cpu'] : $item['id'];

			// Автор
			if ($conf['author'] == 'yes')
			{
				$ins['author'] = $tm->parse(array('author' => $api->siteuni($item['author'])), $tm->create('mod/'.WORKMOD.'/author'));
			}

			// Дата
			if ($conf['date'] == 'yes')
			{
				$ins['date'] = $tm->parse(array(
										'langask' => $lang['faq_anspublic'],
										'date'    => $item['spublic'],
										'langans' => $lang['all_data'],
										'redate'  => $item['public']
									),
									$tm->create('mod/'.WORKMOD.'/date'));
			}

			// Заголовки (вопросы)
			$ins['ask'] .= $tm->parse(array
								(
									'link'   => $ins['link'],
									'name'   => $anchor,
									'title'  => $api->siteuni($item['quest'])
								),
								$tm->manuale['quest']);

			// Ответы
			$ins['ans'] .= $tm->parse(array(
									'name'   => $anchor,
									'author' => $ins['author'],
									'time'   => $ins['date'],
									'title'  => $title,
									'text'   => $api->siteuni($item['answer'])
								),
								$tm->manuale['answer']);
		}

		// Вывод
		$tm->parseprint(array
			(
				'insertask' => $ins['ask'],
				'insertans' => $ins['ans'],
				'pages'     => $ins['pages']
			),
			$ins['template']);
	}
	else
	{
		// Данные отсутствуют
		$tm->message($lang['data_not'], 0, 0, 1);
	}

	/**
	 * Форма добавления вопросов
	 */
	if ($conf['addit'] == 'yes')
	{
		// Категррии
		$api->catcache = $area;

		// Управление
		$tm->unmanule['captcha'] = ($config['captcha']=='yes' AND defined("REMOTE_ADDRS")) ? 'yes' : 'no';
		$tm->unmanule['control'] = ($config['control'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['cat'] = 'yes';

		// Отключить проверку для пользователей
		noprotectspam(0);

		// Контрольный вопрос
		$control = send_quest();

		$tm->parseprint(array
			(
				'post_url'     => $ro->seo('index.php?dn='.WORKMOD),
				'email_name'   => $lang['email_name'],
				'email'        => $lang['e_mail'],
				'question'     => $lang['faq_question'],
				'sel'          => $api->selcat(),
				'in_cat'       => $lang['all_in_cat'],
				'cat_not'      => $lang['cat_not'],
				'all_refresh'  => $lang['all_refresh'],
				'control_word' => $lang['control_word'],
				'captcha'      => $lang['all_captcha'],
				'help_captcha' => $lang['help_captcha'],
				'help_control' => $lang['help_control'],
				'not_empty'    => $lang['all_not_empty'],
				'uname'        => $usermain['uname'],
				'umail'        => $usermain['umail'],
				'control'      => $control['quest'],
				'cid'          => $control['cid'],
				'add_question' => $lang['faq_ask'],
				'send'         => $lang['email_send']
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/form.add')));
	}

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
