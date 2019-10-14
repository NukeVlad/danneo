<?php
/**
 * File:        /mod/poll/index.php
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
global	$db, $basepref, $config, $lang, $usermain, $tm, $api, $global, $to, $p, $id;

/**
 * Рабочий мод
 */
define('WORKMOD', basename(__DIR__)); $conf = $config[WORKMOD];

/**
 * Метки
 */
$legaltodo = array('index', 'page');

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
	$not = FALSE;

	$ins = array
		(
			'pages' => null,
			'output' => null
		);

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);
	$p = ( ! isset($p) OR $p <= 1) ? 1 : $p;
	$s = $conf['pagcol'] * ($p - 1);

	// всего опросов
	$total = $db->fetchrow($db->query("SELECT COUNT(id) AS total FROM ".$basepref."_".WORKMOD." WHERE act = 'yes' AND finish > '".NEWTIME."'"));

	/**
	 * Ошибка листинга страниц
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

	$inq = $db->query
			(
				"SELECT p.*, SUM(v.vals_voices) AS total FROM ".$basepref."_".WORKMOD." p LEFT JOIN ".$basepref."_".WORKMOD."_vals AS v ON (p.id = v.id)
				 WHERE p.finish > '".NEWTIME."' AND p.act = 'yes'
				 GROUP BY p.id ORDER BY p.id DESC LIMIT ".$s.", ".$conf['pagcol']
			);

    /**
	 * Переключатели
	 */
	$tm->unmanule['desc'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? 'yes' : 'no';

	/**
	 * Листинг, формирование постраничной разбивки
	 */
	if ($total['total'] > $conf['pagcol'])
	{
		$ins['pages'] = $tm->parse(array
							(
								'text' => $lang['all_pages'],
								'pages' => $api->pages('', '', 'index', WORKMOD.'&amp;to=index', $conf['pagcol'], $p, $total['total'])
							),
							$tm->manuale['pagesout']);
	}

	/**
	 * Описание раздела
	 */
	$ins['map'] = (preparse($config['mod'][WORKMOD]['map'], THIS_EMPTY) == 0) ? $config['mod'][WORKMOD]['map'] : '';

	if ($db->numrows($inq) > 0)
	{
		$not = TRUE;
		$ins['template'] = $tm->create('mod/'.WORKMOD.'/standart');

		$ins['content'] = array();
		while ($item = $db->fetchrow($inq))
		{
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['content'][] = $tm->parse(array
									(
										'link'		=> $ro->seo('index.php?dn='.WORKMOD.'&amp;to=page&amp;id='.$item['id'].$ins['cpu']),
										'title'		=> $api->siteuni($item['title']),
										'lstart'	=> $lang['poll_start'],
										'date'		=> $item['start'],
										'lfinish'	=> $lang['poll_end'],
										'redate'	=> $item['finish'],
										'poll_voc'	=> $lang['poll_voc'],
										'total'		=> $item['total']
									),
									$ins['template']);
		}

		// Разбивка
		$ins['output'] = $tm->tableprint($ins['content'], $conf['indcol']);
	}

	$tm->unmanule['not'] = ($not) ? 'no' : 'yes';

	/**
	 * Вывод
	 */
	$tm->parseprint(array
		(
			'descript'	=> $ins['map'],
			'polling'	=> $ins['output'],
			'pages'		=> $ins['pages'],
			'datanot'	=> $lang['data_not']
		),
		$tm->parsein($tm->create('mod/'.WORKMOD.'/index'))
	);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}

/**
 * Метка pa
 * ------------- */
if ($to == 'page')
{
	$ins = array();

	/**
	 * Номер страницы, SEO
	 */
	$seopage = isset($p) ? ', '.mb_strtolower($lang['page_one']).'-'.$p : '';

	$p = preparse($p, THIS_INT);

	if ( ! empty($cpu) AND preparse($cpu, THIS_SYMNUM, TRUE) == 0 AND defined('SEOURL'))
	{
		$cpu = preparse($cpu, THIS_TRIM, 0, 255);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE cpu = '".$db->escape($cpu)."' AND act = 'yes' AND finish > '".NEWTIME."'");
		$v = 0;
	}
	else
	{
		$id = preparse($id, THIS_INT);
		$valid = $db->query("SELECT * FROM ".$basepref."_".WORKMOD." WHERE id = '".$id."' AND act = 'yes' AND finish > '".NEWTIME."'");
		$v = 1;
	}

    $item = $db->fetchrow($valid);

	/**
	 * Страница не существует
	 */
	if ($db->numrows($valid) == 0) {
		$tm->noexistprint();
	}
	elseif ( ! empty($item['cpu']) AND $config['cpu'] == 'yes' AND $v)
	{
		$tm->noexistprint();
	}

	/**
	 * Ошибка листинга комментариев
	 */
	if ($conf['comact'] == 'yes')
	{
		$lp = (isset($p)) ? FALSE : TRUE;
		$p = ($p <= 1) ? 1 : $p;
		$nums = ceil($item['comments'] / $config['compage']);
		if ($p > $nums AND $p != 1) {
			$tm->noexistprint();
		}
	}
	else
	{
		if ($p > 0) {
			$tm->noexistprint();
		} else {
			$p = 1;
		}
	}

	/**
	 * Свой TITLE
	 */
	if (isset($item['customs']) AND ! empty($item['customs'])) {
		define('CUSTOM', $api->siteuni($item['customs'].$seopage));
	} else {
		$global['title'] = preparse($item['title'], THIS_TRIM);
		$global['title'].= ( ! empty($obj['catname'])) ? ' - '.$obj['catname'].$seopage : '';
	}

	/**
	 * Мета данные
	 */
	$global['keywords'] = (preparse($item['keywords'], THIS_EMPTY) == 0) ? $api->siteuni($item['keywords']) : $api->seokeywords($item['title'].' '.$item['decs'], 5, 35);
	$global['descript'] = (preparse($item['descript'], THIS_EMPTY) == 0) ? $api->siteuni($item['descript'].$seopage) : '';

	/**
	 * Мета данные Open Graph
	 */
	$global['og_title'] = ( ! empty($item['title'])) ? $api->siteuni($item['title']) : '';
	$global['og_desc'] = ( ! empty($item['decs'])) ? $api->siteuni($item['descript']) : $api->siteuni($item['decs']);

	/**
	 * Меню, хлебные крошки, с учетом категории
	 */
	$global['insert']['current'] = preparse($item['title'], THIS_TRIM);
	$global['insert']['breadcrumb'] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>';

	/**
	 * Ограничение доступа
	 */
	if($item['acc'] == 'user')
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
	}

	/**
	 * Вывод на страницу, шапка
	 */
	$tm->header();

	/**
	 * Переменные
	 */
	$ins = array
	(
		'view'	=> 0,
		'message' => '',
		'voices'  => '',
		'comment' => '',
		'comform' => '',
		'ajaxbox' => ''
	);

	// CPU
	$ins['cpu']    = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';

	// URL
	$ins['url'] = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

	/**
	 * Комментарии
	 */
	if ($conf['comact'] == 'yes')
	{
		$cm = new Comment(WORKMOD);

		// Вывод
		if ($item['comments'] > 0)
		{
			$ins['comment'] = $cm->comment($item['id'], $item['comments'], $ins['cpu'], '', $p);
		}

		// Новые посты ajax
		$ins['ajaxbox'] = $tm->parse(array('empty' => 'empty'), $tm->manuale['ajaxbox']);

		// Форма
		$ins['comform'] = $cm->comform($item['id'], $item['title']);
	}

	/**
	 * Если проголосовал, выводим сообщение
	 */
	if ($item['acc'] == 'user')
	{
		if (defined('USER_LOGGED'))
		{
			$vote = $db->fetchrow
						(
							$db->query
							(
								"SELECT SUM(voteid) AS total FROM ".$basepref."_".WORKMOD."_vote
								 WHERE id = '".$item['id']."'
								 AND userid = '".$usermain['userid']."'"
							)
						);

			$ins['view'] = ($vote['total'] == 0) ? 1 : 0;
			$ins['message'] = ($vote['total'] > 0) ? $lang['poll_dle'] : '';
		}
	}
	else
	{
		// Для гостей
		$vote = $db->fetchrow
					(
						$db->query
						(
							"SELECT SUM(voteid) AS total FROM ".$basepref."_".WORKMOD."_vote
							 WHERE id = '".$item['id']."'
							 AND voteip = '".REMOTE_ADDRS."'"
						)
					);

		$ins['view'] = ($vote['total'] == 0) ? 1 : 0;
		$ins['message'] = ($vote['total'] > 0) ? $lang['poll_dle'] : '';
	}

	// AJAX
	$tm->unmanule['ajax'] = ($config['ajax'] == 'yes' AND $item['ajax'] == 'yes') ? 'yes' : 'no';

	// Шаблон оформления
	$ins['template'] = ($ins['view'] == 0) ? $tm->parsein($tm->create('mod/'.WORKMOD.'/view')) : $tm->parsein($tm->create('mod/'.WORKMOD.'/form.poll'));

	// Количество голосов
	$ins['count'] = $db->fetchrow
						(
							$db->query
							(
								"SELECT SUM(vals_voices) AS total FROM ".$basepref."_".WORKMOD."_vals
								 WHERE id = '".$item['id']."'"
							)
						);

	// Общие данные
	$inq = $db->query
			(
				"SELECT * FROM ".$basepref."_".WORKMOD."_vals
				 WHERE id = '".$item['id']."' ORDER BY posit"
			);

	/**
	 * Вывод опроса
	 */
	while ($vitem = $db->fetchrow($inq))
	{
		$voices = preparse($vitem['vals_voices'], THIS_INT);
		$percent = ($voices > 0) ? round(($voices * 100) / $ins['count']['total'], 1) : $voices;
		$line = ($voices > 0) ? $percent : 1;
		$radio = ($ins['view'] == 1) ? '<input type="radio" name="vid" value="'.$vitem['valsid'].'" />' : '';

		$ins['voices'].= $tm->parse(array
							(
								'val_name'  => $api->siteuni($vitem['vals_title']),
								'radio'     => $radio,
								'val_voc'   => $voices.' '.$lang['poll_vocshort'],
								'val_line'  => $line.'%',
								'val_color' => '#'.$vitem['vals_color'],
								'val_perc'  => $percent
							),
							$tm->manuale['percent']);
	}

	$button = ($ins['view'] == 0) ? '' : $lang['poll_button'];
	$voteajax = ($config['ajax']=='yes' AND $item['ajax']=='yes') ? 1 : 0;
	$votejs = ($voteajax == 1) ? 'true' : 'false';

	/**
	 * Подзаголовок
	 */
	$ins['subtitle'] = ( ! empty($item['subtitle'])) ? $api->siteuni($item['subtitle']) : $api->siteuni($item['title']);

	$tm->parseprint(array
		(
			'post_url'  => $ro->seo('index.php?dn='.WORKMOD),
			'link'      => $ins['url'],
			'id'        => $item['id'],
			'vote_ajax' => $votejs,
			'val'       => $voteajax,
			'title'     => $api->siteuni($item['title']),
			'subtitle'  => $ins['subtitle'],
			'percent'   => $ins['voices'],
			'desc'      => $api->siteuni($item['decs']),
			'message'   => $ins['message'],
			'all_sends' => $lang['all_sends'],
			'button'    => $button,
			// comment
			'comment'   => $ins['comment'],
			'comform'   => $ins['comform'],
			'ajaxbox'   => $ins['ajaxbox']
		),
		$ins['template']);

	/**
	 * Вывод на страницу, подвал
	 */
	$tm->footer();
}
