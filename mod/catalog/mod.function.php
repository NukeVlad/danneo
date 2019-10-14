<?php
/**
 * File:        /mod/catalog/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Форма поиска
 */
function catalog_search($title = TRUE, $makid = 0)
{
	global $db, $basepref, $conf, $config, $lang, $tm, $ro;

	if ($conf['search'] == 'yes')
	{
		$tm->unmanule['maker'] = ($conf['maker'] == 'yes') ? 'yes' : 'no';

		$maker = '';
		if ($conf['maker'] == 'yes')
		{
			$inqset = $db->query("SELECT makid, makname FROM ".$basepref."_".WORKMOD."_maker ORDER BY posit ASC", $config['cachetime'], WORKMOD);
			while ($item = $db->fetchrow($inqset, $config['cache']))
			{
				$maker.= '<option value="'.$item['makid'].'"'.(($item['makid'] == $makid) ? ' selected' : '').'>'.$item['makname'].'</option>';
			}
		}

		$tm->unmanule['cid'] = 'no';
		$tm->unmanule['title'] = ($title) ? 'yes' : 'no';
		$tm->unmanule['more'] = (empty($maker)) ? 'no' : 'yes';

		return $tm->parse(array
			(
				'post_url'    => $ro->seo('index.php?dn='.WORKMOD.'&re=search'),
				'titlesearch' => $lang['search_catalog'],
				'langproduct' => $lang['product_name'],
				'langarticul' => $lang['articul'],
				'langprice'   => $lang['price'],
				'allno'       => $lang['all_no'],
				'langto'      => $lang['to'],
				'langfro'     => $lang['fro'],
				'search'      => $lang['search'],
				'simple'      => $lang['search_simple'],
				'extended'    => $lang['search_more'],
				'langmaker'	  => $lang['maker'],
				'maker'       => $maker,
				'rows'        => ''
			),
			$tm->parsein($tm->create('mod/'.WORKMOD.'/form.search')));
	}
}

/**
 * Форма поиска в категориях
 */
function catalog_search_cat($obj, $title = NULL)
{
	global $db, $basepref, $conf, $config, $lang, $tm, $ro;

	if ($conf['search'] == 'yes')
	{
		$maker = $rows = NULL;
		$options = $voptions = $vid = array();

		$tm->unmanule['cid'] = 'yes';
		$tm->unmanule['title'] = ($title) ? 'yes' : 'no';
		$tm->unmanule['maker'] = ($conf['maker'] == 'yes') ? 'yes' : 'no';

		if ($conf['maker'] == 'yes')
		{
			$inqset = $db->query("SELECT makid, makname FROM ".$basepref."_".WORKMOD."_maker ORDER BY posit ASC", $config['cachetime'], WORKMOD);
			while ($item = $db->fetchrow($inqset, $config['cache']))
			{
				$maker.= '<option value="'.$item['makid'].'">'.$item['makname'].'</option>';
			}
		}

		$tm->unmanule['more'] = (empty($maker)) ? 'no' : 'yes';

		// Шаблон
		$template = $tm->parsein($tm->create('mod/'.WORKMOD.'/form.search'));

		$opt = Json::decode($obj['options']);
		$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_option WHERE search = '1' ORDER BY posit ASC", $config['cachetime'], WORKMOD);

		while ($item = $db->fetchrow($inq, $config['cache']))
		{
			if(isset($opt[$item['oid']]) AND $item['type'] != 'text')
			{
				$options[$item['oid']] = array('title' => $item['title'],'type' => $item['type']);
				$vid[$item['oid']] = $item['oid'];
			}
		}

		if (sizeof($options) > 0)
		{
			$in = implode(',', $vid);
			$inq = $db->query("SELECT * FROM ".$basepref."_".WORKMOD."_option_value WHERE oid IN (".$in.") ORDER BY posit ASC", $config['cachetime'], WORKMOD);
			while ($item = $db->fetchrow($inq, $config['cache']))
			{
				$voptions[$item['oid']][$item['vid']] = $item['title'];
			}

			foreach ($options as $k => $v)
			{
				$val = null;
				if ($v['type'] == 'select')
				{
					$val = '	<select name="search[opt]['.$k.']">
								<option value="0">'.$lang['unimportant'].'</option>';
					foreach ($voptions[$k] as $vk => $vv)
					{
						$val .= '	<option value="'.$vk.'">'.$vv.'</option>';
					}
					$val .= '	</select>';
				}
				elseif($v['type'] == 'checkbox')
				{
					foreach ($voptions[$k] as $vk => $vv)
					{
						$val .= $vv.'	<input name="search[opt]['.$k.'][]" value="'.$vk.'" type="checkbox">';
					}
				}
				elseif($v['type'] == 'radio')
				{
					foreach ($voptions[$k] as $vk => $vv)
					{
						$val .= $vv.'	<input name="search[opt]['.$k.']" value="'.$vk.'" type="radio">';
					}
				}
				$rows .= $tm->parse(array
							(
								'name' => $v['title'],
								'val' => $val
							),
							$tm->manuale['rows']);
			}
		}

		// Вывод
		return $tm->parse(array
			(
				'post_url'    => $ro->seo('index.php?dn='.WORKMOD.'&re=search'),
				'titlesearch' => (empty($title)) ? $lang['search_catalog'] : $title,
				'langproduct' => $lang['product_name'],
				'langarticul' => $lang['articul'],
				'langprice'	  => $lang['price'],
				'allno'       => $lang['unimportant'],
				'langto'      => $lang['to'],
				'langfro'     => $lang['fro'],
				'search'      => $lang['search'],
				'simple'      => $lang['search_simple'],
				'extended'    => $lang['search_more'],
				'langmaker'	  => $lang['maker'],
				'maker'       => $maker,
				'cid'         => $obj['catid'],
				'rows'        => $rows
			),
			$template);
	}
}
