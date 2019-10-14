<?php
/**
 * File:        /block/b-Pages.php
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
global $db, $basepref, $lang, $api, $ro, $conf, $config;

$bc = null;
$ins = $modname = $blockshort = array();

/**
 * Моды
 */
if (in_array('pages', $realmod))
{
	$setmod = isset($conf['pages']['mods']) ? Json::decode($conf['pages']['mods']) : Json::decode($config['pages']['mods']);
	foreach($setmod as $v)
	{
		$modname[$v['mod']] = $v['name'];
	}
}

/**
 * Настройки
 */
$bs = array
(
	'blockname' => $lang['pages'],
	'mods'	=> array(
		'lang'		=> 'block_mods',
		'form'		=> 'select',
		'value'		=> $modname,
		'default'	=> 'poll'
	),
	'order' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('desc' => 'all_desc', 'asc' => 'all_acs'),
		'default'	=> 'desc'
	),
	'col' => array(
		'lang'		=> 'all_col',
		'form'		=> 'text',
		'value'		=> 1,
		'default'	=> 1
	),
	'row' => array(
		'lang'		=> 'who_col_all',
		'form'		=> 'text',
		'value'		=> 1,
		'default'	=> 1
	),
	'wrap' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 150,
		'default'	=> 150
	),
	'mod' => array(
		'lang'		=> 'one_mod',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'short' => array(
		'lang'		=> 'input_text',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'date' => array(
		'lang'		=> 'all_data',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'image' => array(
		'lang'		=> 'all_image',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'link' => array(
		'lang'		=> 'all_link',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	)
);

if (defined('SETTING'))
{
	return $bs;
}

/**
 * Получаем настройки
 */
if (isset($config['mod']['pages']) AND isset($config['bsarray']) AND is_array($config['bsarray']))
{
	$ins = array();
	$bs = $config['bsarray'];

	/**
	 * Управление
	 */
	$tm->unmanule['date'] = ($config['pages']['date'] == 'yes' AND $bs['date'] == 'yes') ? 'yes' : 'no';
	$tm->unmanule['link'] = $bs['link'];

	/**
	 * Шаблон
	 */
	$ins['template'] = $tm->parsein($tm->create('mod/pages/standart'));

	/**
	 * Файл с данными
	 */
	$dirmod = ($bs['mods'] != 'pages') ? $bs['mods'].'/' : '';
	$pathshort = realpath('cache/pages/'.$dirmod.$bs['mods'].'.short.php');

	if (is_file($pathshort) AND file_exists($pathshort))
	{
		$blockshort = include($pathshort);
	}

	/**
	 * Вывод
	 */
	if ( ! empty($blockshort))
	{
		// Сортировки
		($bs['order'] == 'desc') ? krsort($blockshort) : ksort($blockshort);

		$i = 1;
		foreach ($blockshort as $bitem)
		{
			$ins['image'] = $ins['mod'] = NULL;

			// Мод
			if ($bs['mods'] != 'pages' AND $bs['mod'] == 'yes')
			{
				$ins['mod_url'] = $ro->seo('index.php?dn=pages&amp;pa='.$bs['mods']);
				$ins['mod_name'] = $api->siteuni($modname[$bs['mods']]);

				$ins['mod'] = $tm->parse(array(
									'mod_url' => $ins['mod_url'],
									'mod_name' => $ins['mod_name']
								),
								$tm->manuale['mod']);
			}

			// Изображение
			if ( ! empty($bitem['image_thumb']) AND $bs['image'] == 'yes')
			{
				$ins['float'] = ($bitem['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['alt']   = ( ! empty($bitem['image_alt'])) ? $api->siteuni($bitem['image_alt']) : '';

				$ins['image'] = $tm->parse(array(
										'float' => $ins['float'],
										'thumb' => $bitem['image_thumb'],
										'alt'   => $ins['alt']
									),
									$tm->manuale['thumb']);
			}

			// Ссылка на страницу
			$ins['pa'] = ($bs['mods'] != 'pages') ? '&amp;pa='.$bs['mods'] : '';
			$ins['url'] = $ro->seo('index.php?dn=pages'.$ins['pa'].'&amp;cpu='.$bitem['cpu']);

			// Текст
			$ins['text'] = ($bs['short'] == 'yes') ? str_word(deltags($api->siteuni($bitem['textshort'])), $bs['wrap']) : '';

			// В шаблон
			$ins['cpages'][] = $tm->parse(array
				(
					'mod'   => $ins['mod'],
					'title' => $api->siteuni($bitem['title']),
					'date'  => $api->timeformat($bitem['public'], 1), // datetime
					'time'  => $api->sitetime($bitem['public'], 1, 1),
					'date'  => $bitem['public'],
					'text'  => $ins['text'],
					'image' => $ins['image'],
					'url'   => $ins['url'],
					'read'  => $lang['in_detail']
				),
				$ins['template']);

			if ($i == $bs['col']) break;
			$i ++;
		}
		$bc .= $tm->tableprint($ins['cpages'], $bs['row']);
	}
	else
	{
		$bc .= $lang['data_not'];
	}
}
else
{
	$bc .= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
