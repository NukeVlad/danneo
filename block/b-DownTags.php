<?php
/**
 * File:        /block/b-DownTags.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $db, $basepref, $lang, $api, $ro, $config;

$tags = array();
$key = $tagword = $bc = null;
$lang['block_down_tags'] = isset($lang['block_down_tags']) ? $lang['block_down_tags'] : 'Down tags';

/**
 * Настройки
 */
$bs = array(
	'blockname' => $lang['block_down_tags'],
	'mod' => array(
		'lang'    => 'block_mods',
		'form'    => 'text',
		'value'   => 'down',
		'default' => 'down'
	),
	'col'	=> array(
		'lang'    => 'all_col',
		'form'    => 'text',
		'value'   => 10,
		'default' => 10
	),
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
	$bs = $config['bsarray'];

	$tdcol = (isset($bs['col']) AND ! empty($bs['col'])) ? $bs['col'] : 10;
	$inq = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_tag ORDER BY tagrating DESC LIMIT ".$tdcol, $config['cachetime'], $bs['mod']);

	while ($item = $db->fetchrow($inq,$config['cache']))
	{
		$tags[] = array
					(
						'tag'    => $item['tagword'],
						'rating' => $item['tagrating'],
						'tagid'  => $item['tagid'],
						'cpu'    => $item['tagcpu']
					);
		$key.= $item['tagword'].' ';
	}
	uksort($tags, 'randoms');

	foreach ($tags as $k)
	{
		if ($k['rating'] < 20) {
			$class = 'smallmin';
		} elseif ($k['rating'] >= 20 AND $k['rating'] < 40) {
			$class = 'small';
		} elseif ($k['rating'] >= 40 AND $k['rating'] < 60) {
			$class = 'medium';
		} elseif ($k['rating'] >= 60 AND $k['rating'] < 80) {
			$class = 'high';
		} else {
			$class = 'highmax';
		}

		$tag_cpu = (defined('SEOURL') AND $k['cpu']) ? '&amp;cpu='.$k['cpu'] : '';
		$tag_url = $ro->seo('index.php?dn='.$bs['mod'].'&amp;re=tags&amp;to=tag&amp;id='.$k['tagid'].$tag_cpu);
		$tagword.= '<a class="'.$class.'" href="'.$ro->seo('index.php?dn='.$bs['mod'].'&amp;re=tags&amp;to=tag&amp;id='.$k['tagid'].$tag_cpu).'" title="'.$k['tag'].'">'.$k['tag'].'</a> ';
	}

	if ( ! empty($key) )
	{
		$bc.= $tm->parse(array('text' => $tagword), $tm->create('mod/'.$bs['mod'].'/tag.block'));
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
