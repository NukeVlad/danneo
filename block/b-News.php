<?php
/**
 * File:        /block/b-News.php
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
$lang['block_news'] = isset($lang['block_news']) ? $lang['block_news'] : 'News';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_news'],
	'mod'  => array(
		'lang'		=>	'block_mods',
		'form'		=>	'text',
		'value'		=>	'news',
		'default'	=>	'news'
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
	'wrap' => array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 150,
		'default'	=> 150
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
	'cat' => array(
		'lang'		=> 'all_cat_one',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'icon' => array(
		'lang'		=> 'cat_icon',
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
	'tags'	=> array(
		'lang'		=> 'all_tags',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'comment' => array(
		'lang'		=> 'menu_comment',
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
	'link' => array(
		'lang'		=> 'all_link',
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

	$cats = preg_replace('/[^0-9\,]/', '', trim($bs['cats'], ' '));
	$cats = ($cats !== '') ? " a.catid IN (".$cats.") AND" : "";

	$inq = $db->query
			(
				"SELECT a.*, b.* FROM ".$basepref."_".$bs['mod']." AS a
				 LEFT JOIN ".$basepref."_".$bs['mod']."_cat AS b ON (a.catid = b.catid)
				 WHERE".$cats." a.act = 'yes'
				 AND (stpublic = 0 OR stpublic < '".NEWTIME."')
				 AND (unpublic = 0 OR unpublic > '".NEWTIME."')
				 ".(($bs['imp'] == 'yes') ? 'AND imp = 1 ' : '')."ORDER BY ".$bs['sort']." ".$bs['order']." LIMIT ".$bs['col']
			);

	$content = array();
	if (isset($inq) AND $db->numrows($inq) > 0)
	{
		$item_tags   = (isset($config[$bs['mod']]['tags']) AND $config[$bs['mod']]['tags'] == 'yes' AND $bs['tags'] == 'yes') ? 'yes' : 'no';
		$item_comact = (isset($config[$bs['mod']]['comact']) AND $config[$bs['mod']]['comact'] == 'yes' AND $bs['comment'] == 'yes') ? 'yes' : 'no';

		// Управление
		$tm->unmanule['date'] = $bs['date'];
		$tm->unmanule['link'] = $bs['link'];
		$tm->unmanule['info'] = $bs['info'];
		$tm->unmanule['rating'] = ($config[$bs['mod']]['rating'] == 'yes' AND $bs['rating'] == 'yes') ? 'yes' : 'no';
		$tm->unmanule['comment'] = $item_comact;

		// Вложенные шаблоны
		$tm->manuale = array('cat' => null, 'icon' => null, 'tags' => null, 'thumb' => null, 'author' => null);

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/standart'));

		// Все теги
		if ($item_tags == 'yes')
		{
			$taginq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_tag");
			while ($t = $db->fetchassoc($taginq)) {
				$tc[$t['tagid']] = $t;
			}
		}

		while ($item = $db->fetchassoc($inq))
		{
			$ins['tags'] = $ins['image'] = $ins['icon'] = $ins['cat'] = $tagword = $ins['author'] = null;

			// CPU
			$ins['cpu'] = (defined('SEOURL') AND $item['cpu']) ? '&amp;cpu='.$item['cpu'] : '';
			$ins['catcpu'] = (defined('SEOURL') AND ! empty($item['catcpu'])) ? '&amp;ccpu='.$item['catcpu'] : '';

			// URL
			$ins['url'] = $ro->seo('index.php?dn='.$bs['mod'].''.$ins['catcpu'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']);
			$ins['caturl'] = $ro->seo('index.php?dn='.$bs['mod'].'&amp;to=cat&amp;id='.$item['catid'].$ins['catcpu']);

			// Теги
			if ($config[$bs['mod']]['tags'] == 'yes' AND $bs['tags'] == 'yes')
			{
				$ins['temptags'] = $tm->parsein($tm->create('mod/'.$bs['mod'].'/tags'));

				$key = explode(',', $item['tags']);
				foreach ($key as $k)
				{
					if (isset($tc[$k]))
					{
						$tag_cpu = (defined('SEOURL') AND $tc[$k]['tagcpu']) ? '&amp;cpu='.$tc[$k]['tagcpu'] : '';
						$tag_url = $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=tags&amp;to=tag&amp;id='.$tc[$k]['tagid'].$tag_cpu);
						$tagword .= $tm->parse(array(
													'tag_url'  => $tag_url,
													'tag_word' => $tc[$k]['tagword'],
													'tag_desc' => $tc[$k]['descript']
												),
												$tm->manuale['tags']);
					}
				}

				if (isset($tc[$k]) AND ! empty($key))
				{
					$ins['tags'] = $tm->parse(array
										(
											'tags'		=> $tagword,
											'langtags'	=> $lang['all_tags']
										),
										$ins['temptags']);
				}
			}

			// Изображение
			if ( ! empty($item['image_thumb']) AND $bs['image'] == 'yes')
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

			// Категория
			if (isset($item['catname']) AND $bs['cat'] == 'yes')
			{
				$ins['cat'] = $tm->parse(array(
									'caturl'  => $ins['caturl'],
									'catname' => $api->siteuni($item['catname'])
								),
								$tm->manuale['cat']);
			}

			// Иконка категории
			if ( ! empty($item['icon']) AND $bs['icon'] == 'yes')
			{
				$ins['icon'] = $tm->parse(array(
										'icon'  => $item['icon'],
										'alt'   => $api->siteuni($item['catname'])
									),
									$tm->manuale['icon']);
			}

			// Автор
			if ( ! empty($item['author']) AND $bs['author'] == 'yes')
			{
				$author = preg_replace('/[^\pL\pNd\pZs\pP\pM]/us', '', $item['author']);
				if (isset($config['mod']['user']))
				{
					$udata = $userapi->userdata('uname', $author);
					if ( ! empty($udata))
					{
						$author = '<a href="'.$ro->seo($userapi->data['linkprofile'].$udata['userid']).'">'.$udata['uname'].'</a>';
					}
				}
				$ins['author'] = $tm->parse(array(
										'author' => $author,
										'langauthor' => $lang['author']
									),
									$tm->manuale['author']);
			}

			// Кол. комментариев
			$ins['count'] = ($item_comact == 'yes') ? $item['comments'] : '';

			// Текст
			$ins['text'] = ($bs['short'] == 'yes') ? str_word(deltags($api->siteuni($item['textshort'])), $bs['wrap']) : '';

			// Рейтинг
			$ins['rate'] = ($item['rating'] == 0) ? 0 : round($item['totalrating'] / $item['rating']);
			$ins['title_rate'] = ($ins['rate'] == 0) ? $lang['rate_0'] : $lang['rate_'.$ins['rate'].''];

			$content[] = $tm->parse(array
							(
								'icon'		=> $ins['icon'],
								'cat'		=> $ins['cat'],
								'title'		=> $api->siteuni($item['title']),
								'date'		=> $item['public'],
								'text'		=> $ins['text'],
								'image'		=> $ins['image'],
								'author'	=> $ins['author'],
								'comment'	=> $lang['comment_total'],
								'count'		=> $ins['count'],
								'langhits'	=> $lang['all_hits'],
								'hits'		=> $item['hits'],
								'langrate'	=> $lang['all_rating'],
								'titlerate'	=> $ins['title_rate'],
								'rating'	=> $ins['rate'],
								'url'		=> $ins['url'],
								'tags'		=> $ins['tags'],
								'read'		=> $lang['in_detail']
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
