<?php
/**
 * File:        /block/b-Presents.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $lang, $api, $ro, $config;

$bc = null;
$lang['block_media'] = isset($lang['block_media']) ? $lang['block_media'] : 'Presents';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_media'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'media',
		'default'	=>	'media'
	),
	'cats' => array(
		'lang'		=> 'all_cat',
		'form'		=> 'text',
		'value'		=> '',
		'default'	=> '',
		'hint'	    => 'block_cat_help'
	),
	'col' => array(
		'lang'		=> 'col_media',
		'form'		=> 'text',
		'value'		=> 3,
		'default'	=> 3
	),
	'row' => array(
		'lang'		=> 'who_col_all',
		'form'		=> 'text',
		'value'		=> 1,
		'default'	=> 1
	),
	'sort' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('catid' => 'ID', 'listname' => 'all_name', 'public' => 'all_data', 'hits' => 'all_hits', 'posit' => 'all_posit'),
		'default'	=> 'listid'
	),
	'order' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('desc' => 'all_desc', 'asc' => 'all_acs'),
		'default'	=> 'desc'
	),
	'title' => array(
		'lang'		=> 'all_title',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'icon'	=> array(
		'lang'		=> 'all_icon',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'date'	=> array(
		'lang'		=> 'all_data',
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
	'cols'	=> array(
		'lang'		=> 'all_col',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'hits'	=> array(
		'lang'		=> 'all_hits',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'imp'	=> array(
		'lang'		=> 'all_important',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'no'
	)
);

if (defined('SETTING')) {
	return $bs;
}

/**
 * Получаем настройки
 */
if (
	isset($config['bsarray']) AND
	is_array($config['bsarray']) AND
	isset($config['mod'][$config['bsarray']['mod']])
) {
	$ins = array();
	$bs = $config['bsarray'];

	$cats = preg_replace('/[^0-9\,]/', '', trim($bs['cats'], ' '));
	$cats = ($cats !== '') ? " a.catid IN (".$cats.") AND" : "";

	$inq = $db->query
			(
				"SELECT a.*, COUNT(b.id) AS total FROM ".$basepref."_".$bs['mod']."_cat AS a LEFT JOIN ".$basepref."_".$bs['mod']." AS b ON (a.catid = b.catid)
				 WHERE".$cats." a.act = 'yes'".(($bs['imp'] == 'yes') ? ' AND a.imp = 1' : '')."
				 AND (a.stpublic = 0 OR a.stpublic < '".NEWTIME."')
				 AND (a.unpublic = 0 OR a.unpublic > '".NEWTIME."')
				 GROUP BY a.catid ORDER BY a.".$bs['sort']." ".$bs['order']." LIMIT ".$bs['col']
			);

	if (isset($inq) AND $db->numrows($inq) > 0)
	{
		// Управление
		$tm->unmanule['title'] = $bs['title'];
		$tm->unmanule['date'] = $bs['date'];
		$tm->unmanule['cols'] = $bs['cols'];
		$tm->unmanule['hits'] = $bs['hits'];
		$tm->unmanule['cont'] = $bs['short'];
		$tm->unmanule['info'] = ($bs['cols'] == 'yes' OR $bs['hits'] == 'yes') ? 'yes' : 'no';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/block'));

		$content = array();
		while ($item = $db->fetchassoc($inq))
		{
			$ins['text'] = ($bs['short'] == 'yes') ? $api->siteuni($item['listdesc']) : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			$ins['icon'] = '';
			if ($bs['icon'] == 'yes' AND  ! empty($item['icon']))
			{
				$ins['icon'] = $tm->parse(array('icon' => $item['icon']), $tm->manuale['icon']);
			}

			$content[]  = $tm->parse(array
							(
								'icon'    => $ins['icon'],
								'url'     => $ins['url'],
								'title'   => $api->siteuni($item['listname']),
								'date'    => $item['public'],
								'public'  => $lang['all_data'],
								'langcol' => $lang['all_col'],
								'langhits'=> $lang['all_hits'],
								'hits'    => $item['hits'],
								'text'    => $ins['text'],
								'total'   => $item['total']
							),
							$ins['template']);
		}

		$bc.= $tm->tableprint($content, $bs['row']);
	}
	else
	{
		$bc.= $lang['data_not'];
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
