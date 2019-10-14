<?php
/**
 * File:        /block/b-LiquidSlider.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $lang, $api, $ro, $config;

$bc = '';
$ins = $obj = array();

/**
 * Настройки
 */
$bs = array
(
	'blockname' => $lang['block_slide'],
	'mod' => array(
		'lang'		=> 'block_mods',
		'form'		=> 'select',
		'value'		=> array('news' => 'block_news', 'article' => 'block_article', 'down' => 'block_down'),
		'default'	=> 'news'
	),
	'col' => array(
		'lang'		=> 'all_col',
		'form'		=> 'text',
		'value'		=> 5,
		'default'	=> 5
	),
	'coltxt' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 270,
		'default'	=> 270
	),
	'sort' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('id' => 'ID', 'title' => 'all_name', 'public' => 'all_data', 'hits' => 'all_hits', 'comments' => 'menu_comment'),
		'default'	=> 'id'
	),
	'order' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('desc' => 'all_desc', 'asc' => 'all_acs'),
		'default'	=> 'desc'
	),
	'tabalign' => array(
		'lang'		=> 'Tabs Align',
		'form'		=> 'select',
		'value'		=> array('left' => 'left', 'right' => 'right'),
		'default'	=> 'right'
	),
	'tabposit' => array(
		'lang'		=> 'Tabs Position',
		'form'		=> 'select',
		'value'		=> array('bottom' => 'bottom', 'top' => 'top'),
		'default'	=> 'bottom'
	),
	'interval' => array(
		'lang'		=> 'Auto Slide Interval',
		'form'		=> 'text',
		'value'		=> 5000,
		'default'	=> 5000
	),
	'autoslide' => array(
		'lang'		=> 'Auto Slide',
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
	'short' => array(
		'lang'		=> 'input_text',
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
	'imp' => array(
		'lang'		=> 'all_important',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'no'
	)
);

if (defined('SETTING'))
{
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

	$imp = ($bs['imp'] == 'yes') ? 'AND imp = 1 ' : '';
	$inq = $db->query
			(
				"SELECT id, catid, public, stpublic, unpublic, cpu, title, textshort, image_thumb, image_align
				 FROM ".$basepref."_".$bs['mod']." WHERE act = 'yes' ".$imp."
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ORDER BY ".$bs['sort']." ".$bs['order']." LIMIT ".$bs['col']
			);

	// Категории
	$inqcat = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_cat ORDER BY posit ASC", $config['cachetime'], 'catalog');
	while ($c = $db->fetchassoc($inqcat, $config['cache']))
	{
		$obj[$c['catid']] = $c;
	}

	if ($db->numrows($inq) > 0)
	{
		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('slider'));

		$i = 1;
		$ins['slide'] = '';
		while ($item = $db->fetchassoc($inq))
		{
			$ins['image'] = '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($obj[$item['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$item['catid']]['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

			// Дата
			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
			$ins['date'] = ($bs['date'] == 'yes') ? '<time>'.$api->sitetime($ins['public'], 1).'</time>' : '';
			$ins['tabs'] = ($bs['date'] == 'yes') ? $api->sitetime($ins['public'], 'd').' '.$api->sitetime($ins['public'], 'M').'.' : '';

			// Изображение
			if ($bs['image'] == 'yes' AND ! empty($item['image_thumb']))
			{
				$ins['float'] = ($item['image_align'] == 'left') ? 'imgleft' : 'imgright';
				$ins['image'] = '	<figure class="thumb '.$ins['float'].'">
										<a href="'.$ro->seo('index.php?dn='.$bs['mod'].$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']).'">
											<img src="'.SITE_URL.'/'.$item['image_thumb'].'" alt="" />
										</a>
									</figure>';
			}

			// Текст
			$ins['text'] = ($bs['short'] == 'yes') ? '<p>'.str_word($item['textshort'], $bs['coltxt']).'</p>' : '';

			$ins['slide'].= $tm->parse(array
								(
									'num'   => $i,
									'time'  => $ins['date'],
									'tabs'  => $ins['tabs'],
									'title' => $api->siteuni($item['title']),
									'image' => $ins['image'],
									'text'  => $ins['text'],
									'url'   => $ins['url']
								),
								$tm->manuale['slide']);
			$i ++;
		}

		$ins['auto'] = ($bs['autoslide'] == 'yes') ? 'true' : 'false';

		// Вывод
		$bc.= $tm->parse(array
				(
					'key'      => $key_block,
					'slider'   => $ins['slide'],
					'auto'     => $ins['auto'],
					'interval' => $bs['interval'],
					'align'	   => $bs['tabalign'],
					'posit'	   => $bs['tabposit']
				),
				$ins['template']);
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
