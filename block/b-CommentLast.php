<?php
/**
 * File:        /block/b-CommentLast.php
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
$ins = array();

/**
 * Настройки
 */
$bs = array
(
	'blockname' => $lang['block_comment'],
	'mod' => array(
		'lang'		=> 'block_mods',
		'form'		=> 'select',
		'value'		=> array('catalog' => 'catalog', 'down' => 'down', 'article' => 'article', 'news' => 'news', 'poll' => 'poll'),
		'default'	=> 'poll'
	),
	'col' => array(
		'lang'		=> 'comment_total',
		'form'		=> 'text',
		'value'		=> 10,
		'default'	=> 10
	),
	'row' => array(
		'lang'		=> 'who_col_all',
		'form'		=> 'text',
		'value'		=> 1,
		'default'	=> 1
	),
	'wrap'	=> array(
		'lang'		=> 'anons_count',
		'form'		=> 'text',
		'value'		=> 44,
		'default'	=> 44
	),
	'auth'	=> array(
		'lang'		=> 'author',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'name'	=> array(
		'lang'		=> 'all_title',
		'form'		=> 'checkbox',
		'value'		=> 'yes',
		'default'	=> 'yes'
	),
	'date'	=> array(
		'lang'		=> 'all_data',
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
if (isset($config['bsarray']) AND is_array($config['bsarray']))
{
	$bs = $config['bsarray'];

	if (isset($config['mod'][$bs['mod']]))
	{
		$inqc = $db->query
					(
						"SELECT a.id, a.file, a.ctime, a.cname, a.ctitle, a.ctext, b.id, b.cpu, b.title
						 FROM ".$basepref."_comment AS a
						 LEFT JOIN ".$basepref."_".$bs['mod']." AS b ON (a.id = b.id)
						 WHERE a.file = '".$bs['mod']."'
						 ORDER BY ctime DESC LIMIT ".$bs['col']
					);

		// Категории
		if ($bs['mod'] != 'poll')
		{
			$obj = array();
			$inqs = $db->query("SELECT * FROM ".$basepref."_".$bs['mod']."_cat ORDER BY posit ASC", $config['cachetime'], $bs['mod']);
			while ($c = $db->fetchassoc($inqs, $config['cache'])) {
				$obj[$c['catid']] = $c;
			}
		}

		// Управление
		$tm->unmanule['date'] = $bs['date'];
		$tm->unmanule['auth'] = $bs['auth'];
		$tm->unmanule['name'] = $bs['name'];
		$tm->unmanule['info'] = ($bs['date'] == 'yes' OR $bs['auth'] == 'yes') ? 'yes' : 'no';

		// Шаблон
		$ins['template'] = $tm->parsein($tm->create('comment'));

		if ($db->numrows($inqc) > 0)
		{
			$content = array();
			while ($item = $db->fetchassoc($inqc))
			{
				$ins['cats'] = '';
				if (isset($config['smilie']) AND is_array($config['smilie']))
				{
					$item['ctext'] = smilieparse($item['ctext'], $config['smilie'], false);
				}

				// ЧПУ страницы
				$ins['cpu'] = (defined('SEOURL') AND ! empty($item['cpu'])) ? '&amp;cpu='.$item['cpu'] : '';

				// ЧПУ категории
				if ($bs['mod'] != 'poll')
				{
					$catid = $db->fetchassoc($db->query("SELECT catid FROM ".$basepref."_".$bs['mod']." WHERE id = '".$item['id']."' LIMIT 1"));
					$ins['cats'] = (defined('SEOURL') AND ! empty($obj[$catid['catid']]['catcpu'])) ? '&amp;ccpu='.$obj[$catid['catid']]['catcpu'] : '';
				}

				// Содержимое
				$ins['title'] = '<a href="'.$ro->seo('index.php?dn='.$bs['mod'].$ins['cats'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']).'">'.$api->siteuni($item['title']).'</a>';
				$ins['text'] = ($bs['name'] == 'yes') ? str_word(deltags($item['ctext'], $bs['wrap'])) : '<a href="'.$ro->seo('index.php?dn='.$bs['mod'].$ins['cats'].'&amp;to=page&amp;id='.$item['id'].$ins['cpu']).'">'.str_word(deltags($item['ctext'], $bs['wrap'])).'</a>';

				// Вывод
				$content[] = $tm->parse(array
								(
									'title'   => $ins['title'],
									'text'    => $ins['text'],
									'date'    => $item['ctime'],
									'author'  => $api->siteuni($item['cname']),
									'public'  => $lang['all_data'],
									'langaut' => $lang['author']
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
	else {
		$bc .= $lang['all_set_no'];
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
