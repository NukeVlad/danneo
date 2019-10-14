<?php
/**
 * File:        /block/b-PhotosNormal.php
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
$lang['block_photos'] = isset($lang['block_photos']) ? $lang['block_photos'] : 'Photos';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_photos'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'photos',
		'default'	=>	'photos'
	),
	'cats' => array(
		'lang'		=> 'all_cat',
		'form'		=> 'text',
		'value'		=> '',
		'default'	=> '',
		'hint'	    => 'block_cat_help'
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
	'title' => array(
		'lang'		=> 'all_name',
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
	'author' => array(
		'lang'		=> 'author',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'rating' => array(
		'lang'		=> 'all_rating',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'info' => array(
		'lang'		=> 'stat',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'comm' => array(
		'lang'		=> 'block_comment',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'no'
	),
	'random' => array(
		'lang'		=> 'photos_random',
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

	$cats = preg_replace('/[^0-9\,]/', '', trim($bs['cats'], ' '));
	$cats = ($cats !== '') ? " a.catid IN (".$cats.") AND" : "";

	$inq = $db->query
			(
				"SELECT a.*, b.* FROM ".$basepref."_".$bs['mod']." AS a 
				 LEFT JOIN ".$basepref."_".$bs['mod']."_cat AS b ON (a.catid = b.catid)
				 WHERE".$cats." a.act = 'yes' ORDER BY ".(($bs['random'] == 'yes') ? 'MD5(RAND())' : $bs['sort'].' '.$bs['order'])." LIMIT ".$bs['col']
			);

	if (isset($inq) AND $db->numrows($inq) > 0)
	{
		// Управление
		$tm->unmanule['title'] = $bs['title'];
		$tm->unmanule['date']  = $bs['date'];
		$tm->unmanule['info']  = $bs['info'];
		$tm->unmanule['comment'] = ($config[$bs['mod']]['comact'] == 'yes' AND $bs['comm'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['rating'] = ($config[$bs['mod']]['rating'] == 'yes' AND $bs['rating'] == 'yes') ? 'yes' : 'no';

		// Вложенные шаблоны
		$tm->manuale = array('author' => null);

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/thumb'));

		$content = array();
		while ($item = $db->fetchrow($inq))
		{
			$ins['author'] = null;

			// CPU / URL
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			$ins['public'] = ($item['stpublic'] > 0) ? $item['stpublic'] : $item['public'];
			$ins['count'] = ($config[$bs['mod']]['comact'] == 'yes') ? $item['comments'] : '';
			$ins['alt'] = ( ! empty($item['image_alt'])) ? $api->siteuni($item['image_alt']) : '';

			// Автор
			$ins['author'] = null;
			if ( ! empty($item['author']) AND $bs['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Вывод
			$content[] = $tm->parse(array
							(
								'title'		=> $api->siteuni($item['title']),
								'date'		=> $ins['public'],
								'thumb'		=> $item['image_thumb'],
								'alt'		=> $ins['alt'],
								'url'		=> $ins['url'],
								'author'	=> $ins['author'],
								'langrate'	=> $lang['all_rating'],
								'titlerate'	=> $ins['title_rate'],
								'rating'	=> $ins['rate'],
								'langhits'	=> $lang['all_hits'],
								'hits'		=> $item['hits'],
								'comment'	=> $lang['comment_total'],
								'count'		=> $ins['count']
							),
							$ins['template']);
		}

		$bc = $tm->tableprint($content, $bs['row']);
	}
	else
	{
		$bc = $lang['data_not'];
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
