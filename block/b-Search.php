<?php
/**
 * File:        /block/b-Search.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $lang, $config, $dn;

$bc = $mod = '';

if (defined('SETTING'))
{
	return $bs = array('blockname' => $lang['block_search']);
}

// Статьи
if (
	isset($config['mod']['article']) AND 
	($config['article']['search'] == 'yes' OR $config['article']['search'] == 'hide')
) {
	$mod.= '<option value="article"'.((isset($dn) AND $dn == 'article') ? ' selected="selected"' : '').'>'.$config['mod']['article']['name'].'</option>';
}

// Новости
if (
	isset($config['mod']['news']) AND 
	($config['news']['search'] == 'yes' OR $config['news']['search'] == 'hide')
) {
	$mod.= '<option value="news"'.((isset($dn) AND $dn == 'news') ? ' selected="selected"' : '').'>'.$config['mod']['news']['name'].'</option>';
}

// Страницы
if (
	isset($config['mod']['pages']) AND 
	($config['pages']['search'] == 'yes' OR $config['pages']['search'] == 'hide')
) {
	$mod.= '<option value="pages"'.((isset($dn) AND $dn == 'pages') ? ' selected="selected"' : '').'>'.$config['mod']['pages']['name'].'</option>';
}

// Фотогалерея
if (
	isset($config['mod']['photos']) AND 
	($config['photos']['search'] == 'yes' OR $config['photos']['search'] == 'hide')
) {
	$mod.= '<option value="photos"'.((isset($dn) AND $dn == 'photos') ? ' selected="selected"' : '').'>'.$config['mod']['photos']['name'].'</option>';
}

// Файлы
if (
	isset($config['mod']['down']) AND 
	($config['down']['search'] == 'yes' OR $config['down']['search'] == 'hide')
) {
	$mod.= '<option value="down"'.((isset($dn) AND $dn == 'down') ? ' selected="selected"' : '').'>'.$config['mod']['down']['name'].'</option>';
}

// Каталог товаров
if (
	isset($config['mod']['catalog']) AND 
	($config['catalog']['search'] == 'yes' OR $config['catalog']['search'] == 'hide')
) {
	$mod.= '<option value="catalog"'.((isset($dn) AND $dn == 'catalog') ? ' selected="selected"' : '').'>'.$config['mod']['catalog']['name'].'</option>';
}

// Ссылки
if (
	isset($config['mod']['link']) AND 
	($config['link']['search'] == 'yes' OR $config['link']['search'] == 'hide')
) {
	$mod.= '<option value="link"'.((isset($dn) AND $dn == 'link') ? ' selected="selected"' : '').'>'.$config['mod']['link']['name'].'</option>';
}

$bc.= $tm->parse(array
		(
			'mods'   => $mod,
			'word'   => $lang['search_input_word'],
			'search' => $lang['search']
		),
		$tm->create('search'));

/**
 * Вывод
 */
return $bc;
