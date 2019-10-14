<?php
/**
 * File:        /block/b-FaqLast.php
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
global $db, $basepref, $lang, $api, $ro, $config, $selective;

$bc = null;
$lang['block_faq'] = isset($lang['block_faq']) ? $lang['block_faq'] : 'Faq last';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_faq'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'faq',
		'default'	=>	'faq'
	),
	'sort' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('id' => 'ID', 'author' => 'author', 'public' => 'all_data'),
		'default'	=> 'listid'
	),
	'order' => array(
		'lang'		=> 'all_sorting',
		'form'		=> 'select',
		'value'		=> array('desc' => 'all_desc', 'asc' => 'all_acs'),
		'default'	=> 'desc'
	),
	'selcat' => array(
		'lang'		=> 'all_cat',
		'form'		=> 'select',
		'value'		=> $selective,
		'default'	=> ''
	),
	'subcat' => array(
		'lang'		=> 'all_subcat',
		'form'		=> 'select',
		'value'		=> array('yes' => 'all_yes', 'no' => 'all_no'),
		'default'	=> 'no'
	),
	'row' => array(
		'lang'		=> 'who_col_all',
		'form'		=> 'select',
		'value'		=> array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'),
		'default'	=> 1
	),
	'col' => array(
		'lang'		=> 'all_col',
		'form'		=> 'text',
		'value'		=> 5,
		'default'	=> 5
	),
	'wrap' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 50,
		'default'	=> 50
	),
	'date' => array(
		'lang'		=> 'all_data',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'auth'  => array(
		'lang'		=> 'author',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'oncat' => array(
		'lang'		=> 'all_cat_one',
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
if (
	isset($config['bsarray']) AND
	is_array($config['bsarray']) AND
	isset($config['mod'][$config['bsarray']['mod']])
) {
	$ins = $match = array();
	$bs = $config['bsarray'];

	$inqcat = $db->query("SELECT catid, parentid FROM ".$basepref."_".$bs['mod']."_cat ORDER BY posit ASC", $config['cachetime'], $bs['mod']);
	while ($sub = $db->fetchassoc($inqcat, $config['cache']))
	{
		$match[$sub['parentid']][$sub['catid']] = $sub;
	}

	if ($bs['selcat'] > 0)
	{
		$incat = $api->findsubcat($match, $bs['selcat']);
		$subcat = (is_array($incat) AND sizeof($incat) > 0) ? $bs['subcat'] == 'yes' ? ','.implode(',', $incat) : '' : '';
		$cats = " AND a.catid IN (".$bs['selcat'].$subcat.")";
	}
	elseif ($bs['selcat'] == 0 AND $bs['selcat'] !== '')
	{
		$cats = " AND a.catid = '0'";
	}
	elseif ($bs['selcat'] === '')
	{
		$cats = '';
	}

	$inq = $db->query
			(
				"SELECT a.*, b.* FROM ".$basepref."_".$bs['mod']." AS a
				 LEFT JOIN ".$basepref."_".$bs['mod']."_cat AS b ON (a.catid = b.catid)
				 WHERE a.act = 'yes'".$cats."
				 ORDER BY a.".$bs['sort']." ".$bs['order']." LIMIT ".$bs['col']
			);

	if (isset($inq) AND $db->numrows($inq) > 0)
	{
		// Управление
		$tm->unmanule['date'] = $bs['date'];
		$tm->unmanule['auth'] = $bs['auth'];
		$tm->unmanule['info'] = ($bs['date'] == 'yes' OR $bs['auth'] == 'yes') ? 'yes' : 'no';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/block'));

		$content = array();
		while ($item = $db->fetchrow($inq))
		{
			$ins['cat'] = '';

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

			// Категории
			if ( ! empty($item['catid']) AND $bs['oncat'] == 'yes')
			{
				$ins['cat'] = $tm->parse(array(
									'caturl'	=> $ro->seo('index.php?dn='.$bs['mod'].'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']),
									'catname'	=> $api->siteuni($item['catname'])
								),
								$tm->manuale['cat']);
			}

			// Ссылки
			$ins['linkurl'] = (defined('SEOURL') AND $item['cpu'] AND empty($item['catcpu'])) ? '&amp;id='.$item['id'].$ins['cpu'] : '';
			$ins['linkcat'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu'] : '';
			$ins['anchor']  = (defined('SEOURL') AND ! empty($item['cpu'])) ? $item['cpu'] : $item['id'];

			// Шаблон
			$content[]  = $tm->parse(array
							(
								'cat'     => $ins['cat'],
								'url'     => $ro->seo('index.php?dn='.$bs['mod'].$ins['linkcat'].$ins['linkurl']).'#'.$ins['anchor'],
								'quest'   => $api->siteuni(str_word($api->siteuni($item['quest']), $bs['wrap'])),
								'author'  => $api->siteuni($item['author']),
								'time'    => $api->sitetime($item['public'],1),
								'public'  => $lang['all_data'],
								'langaut' => $lang['author']
							),
							$ins['template']);
		}

		// Вывод
		$bc.= $tm->tableprint($content, $bs['row']);
	}
	else
	{
		$bc.= $lang['data_not'];
	}
}
else
{
	$bc.= $lang['all_set_no'];
}

/**
 * Вывод
 */
return $api->siteuni($bc);
