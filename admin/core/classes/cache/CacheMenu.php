<?php
/**
 * File:        /admin/core/classes/cache/CacheMenu.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace DN\Cache;
use Cache;
use Router;

/**
 * Class CacheMenu
 */
class CacheMenu extends Cache
{
	public function __construct() { }

	/**
	 * Activate function sub menu
	 * @param array
	 */
	private function sub_menu($tree, $id = 0)
	{
		static $c = array();

		if ( ! isset($tree[$id]))
			return;

		foreach ($tree[$id] as $v)
		{
			$c[] = $v['id'];
			$this->sub_menu($tree, $v['id']);
		}

		unset($tree[$id]);
		return $c;
	}

	/**
	 * Activate function get menu
	 * @param string
	 */
	private function get_menu($mid = 1)
	{
		global $basepref, $db;

		$tree = $id = array();
		$inq = $db->query("SELECT * FROM ".$basepref."_site_menu ORDER BY posit ASC");
		while ($item = $db->fetchassoc($inq))
		{
			$tree[$item['parent']][$item['id']] = $item;
		}

		$id = $this->sub_menu($tree, $mid);
		$id = ( ! empty($id)) ? implode(',', $id) : 0;
		$inq = $db->query("SELECT * FROM ".$basepref."_site_menu WHERE id IN (".$mid.",".$id.") ORDER BY posit ASC");

		$menu = array();
		while($row = $db->fetchassoc($inq)){
			$menu[$row['id']] = $row;
		}
		return $menu;
	}

	/**
	 * Activate function get tree
	 * @param array
	 */
	private function get_tree($array)
	{
		$tree = array();
		foreach ($array as $id => &$node)
		{
			if ( ! isset($node['parent'])) {
				$tree[$id] = &$node;
			} else{
				$tree[$id] = $array[$node['parent']]['sub'][$id] = &$node;
			}
		}
		return $tree;
	}

	/**
	 * Activate function parse menu
	 * @param array
	 */
	private function parse_menu($array, $type= 'array')
	{
		global $db, $basepref, $conf;

		// Array
		if ($type == 'array')
		{
			$out = "'".$array['id']."'=>array(";
			$out.= "'code'=>'".$array['code']."',";
			$out.= "'name'=>'".$array['name']."',";
			$out.= "'link'=>'".$array['link']."',";
			$out.= "'title'=>'".$array['title']."',";
			$out.= "'icon'=>'".$array['icon']."',";
			$out.= "'css'=>'".$array['css']."',";
			$out.= "'target'=>'".$array['target']."',";
			if (isset($array['sub']))
			{
				$out.= "'sub'=>array(";
				$out.= "".$this->print_menu($array['sub'])."";
				$out.= "),";
			}
			$out.= "),";
		}
		// Print
		else
		{
			require_once(WORKDIR.'/core/classes/Router.php');
			$ro = new Router();

			$links = ltrim($array['link'], '/');
			$parse = parse_url($links);

			if( ! isset($parse['scheme']))
			{
				$url = ($conf['cpu'] == 'yes') ? $conf['site_url'].$ro->seo($links) : $conf['site_url'].'/'.$links;
				$url = isset($parse['fragment']) ? $url.'#'.$parse['fragment'] : $url;
			} 
			else 
			{
				$url = $links;
			}

			$arrow = (isset($array['sub'])) ? 'arrow' : '';
			$class = ( ! empty($array['css'])) ? $arrow.' '.$array['css'] : $arrow;
			$title = ( ! empty($array['title'])) ? ' title="'.$array['title'].'"' : '';
			$target = ($array['target'] == '_blank') ? ' target="_blank"' : '';
			$icon = ( ! empty($array['icon'])) ? '<img src="'.WORKURL.'/'.$array['icon'].'" alt="'.$array['title'].'" />' : '';
			$item = '<a'.(( ! empty($class)) ? ' class="'.$class.'"' : '').' href="'.$url.'"'.$title.$target.'>'.$icon.$array['name'].'</a>';

			$out = '<li>'.$item;
			if (isset($array['sub']))
			{
				$out.= '<ul>'.$this->print_menu($array['sub'], $type).'</ul>';
			}
			$out.= '</li>';
		}

		return $out;
	}

	/**
	 * Activate function print menu
	 * @param array
	 */
	private function print_menu($array, $type = 'array')
	{
		$out = null;
		foreach ($array as $item) {
			$out.= $this->parse_menu($item, $type);
		}
		return $out;
	}

	/**
	 * Activate function out cache menu
	 * @param array
	 */
	public function cachemenu($type = 'array')
	{
		global $basepref, $db, $conf;

		$tree = $mid = array();
		$inq = $db->query("SELECT id FROM ".$basepref."_site_menu WHERE parent = '0'");
		while ($item = $db->fetchassoc($inq))
		{
			$mid[] = $item;
		}

		foreach ($mid as $k => $v)
		{
			$menu = $this->get_menu($v['id']);
			$get = $this->get_tree($menu);
			$tree+= isset($get[0]['sub']) ? $get[0]['sub'] : $get;
		}

		// Cache out
		$this->output = "// CACHE MENU\n";

		// Array
		if ($type == 'array')
		{
			$this->output.= "\$array_menu = array(";
			$this->output.= "".$this->print_menu($tree)."";
			$this->output.= ");";
		}
		// Print
		else
		{
			$act_menu = null;
			foreach ($tree as $m)
			{
				$this->output.= "\$global['insert']['".$m['code']."'] = ";
				$this->output.= "'<ul class=\"".$m['css']."\">";
				if(isset($m['sub']))
				{
					$this->output.= $this->print_menu($m['sub'], $type);
				}
				$this->output.= "</ul>';\n";

				$act_menu.= $m['css'].",";
			}

			$act = ($conf['act_menu'] == 'tag') ? 'tag' : 'link';
			$tag = ( ! empty($conf['tag_menu'])) ? $conf['tag_menu'] : 'strong';
			$this->output.= "\$global['insert']['actmenu'] = '".substr($act_menu, 0, -1).":".$act.":".$tag."';";
		}

		// Cache write
		$this->cachewrite(5);
	}
}
