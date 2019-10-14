<?php
/**
 * File:        /core/classes/Api.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Api
 */
class Api
{
	public $sel          = '';
	public $print        = array();
	public $catcache     = array();
	public $catmenucache = array();
	public $subcatcache  = array();
	public $keywordcache = array();

	public function __construct() {}

	/**
	 * Letters Latin (en), default
	 * @type array
	 */
	public $latin = array
	(
		1  => array('A', 'en-A'),
		2  => array('B', 'en-B'),
		3  => array('C', 'en-C'),
		4  => array('D', 'en-D'),
		5  => array('E', 'en-E'),
		6  => array('F', 'en-F'),
		7  => array('G', 'en-G'),
		8  => array('H', 'en-H'),
		9  => array('I', 'en-I'),
		10  => array('J', 'en-J'),
		11 => array('K', 'en-K'),
		12 => array('L', 'en-L'),
		13 => array('M', 'en-M'),
		14 => array('N', 'en-N'),
		15 => array('O', 'en-O'),
		16 => array('P', 'en-P'),
		17 => array('Q', 'en-Q'),
		18 => array('R', 'en-R'),
		19 => array('S', 'en-S'),
		10 => array('T', 'en-T'),
		21 => array('U', 'en-U'),
		22 => array('V', 'en-V'),
		23 => array('W', 'en-W'),
		24 => array('X', 'en-X'),
		25 => array('Y', 'en-Y'),
		26 => array('Z', 'en-Z'),
		27 => array('0-9', 'NUM')
	);

	/**
	 * Функция литерации
	 */
	function letters($key = TRUE)
	{
		global $lang, $config;

		if (isset($lang['letters']{0}) AND $config['langcode'] != 'en' )
		{
			$i = 28;
			$local = explode(',', preg_replace('/\s/', '', $lang['letters']));
			foreach ($local as $v)
			{
				$pair = explode('>', $v);
				$other[$i] = array($pair[0], $config['langcode'].'-'.$pair[1]);
				$i ++;
			}
			$out = $this->latin + $other;
		}
		else
		{
			$out = $this->latin;
		}

		if ($key) {
			return $out;
		} else {
			foreach ($out as $k => $v) {
				$reout[$v[1]] = $k;
			}
			return $reout;
		}
	}

	/**
	 * Строковые функции
	 */
	function sitedn($resursing)
	{
		return $resursing = (preg_match("/^[a-zA-Z0-9_\-]+$/D",$resursing)) ? mb_substr($resursing, 0, 12) : '';
	}

	function sitepa($resursing)
	{
		return $resursing = (preg_match("/^[a-zA-Z0-9_\-]+$/D",$resursing)) ? mb_substr($resursing, 0, 255) : '';
	}

	function siteuni($resursing)
	{
		global $config;
		$resursing = html_entity_decode(stripcslashes($this->amp($resursing)), ENT_QUOTES, $config['langcharset']);
		return $resursing;
	}

	function sitesp($resursing)
	{
		global $config;
		return  htmlspecialchars($resursing, ENT_QUOTES, $config['langcharset']);
	}

	function sitedp($resursing)
	{
		global $config;
		return html_entity_decode($resursing, ENT_QUOTES, $config['langcharset']);
	}

	function amp($resursing, $type = false)
	{
		if ($type) {
			return preg_replace('/&amp;([a-z0-9#]{2,6};)/u', '&$1', str_replace('&amp;', '&', $resursing));
		} else {
			return preg_replace('/&amp;([a-z0-9#]{2,6};)/u', '&$1', str_replace('&', '&amp;', $resursing));
		}
	}

	/**
	 * Форматирование даты и времени
	 * @format 0 = прямое (декабрь)
	 * @format 1 = склонение (декабря)
	 * @time 0 = время выкл.
	 * @time 1 = время вкл.
	 */
	function sitetime($gtm, $format = false, $time = false)
	{
		global $config, $langdate;

		$gtm = ( ! is_numeric($gtm) OR empty($gtm)) ? NEWTIME : $gtm;

		$date = new DateTime();
		$date->setTimestamp($gtm);

		$sitedate = $date->format($config['formatdate']);
		$outtime = ' '.$date->format($config['formattime']);

		$itset = (strpos($format, '%') !== false) ? 1 : 0;

		if (($format !== false) AND is_numeric($format))
		{
			if (is_array($langdate))
			{
				switch($format)
				{
					case 1 : $exdate = $this->month($sitedate, 1);
						break;
					default :  $exdate = $this->month($sitedate);
						break;
				}
				$outdate = strtr(strtolower($exdate), $langdate);
			}
			return ($time) ? $outdate.$outtime : $outdate;
		}
		else
		{
			if ($itset)
			{
				$format = str_replace('%', '', $format);
				$sitedate = $date->format($format);
			}
			else
			{
				switch($format)
				{
				case 'ru' : $sitedate = $date->format('d.m.Y');	// Российская нотация даты
					break;
				case 'en' : $sitedate = $date->format('Y-m-d');	// Английская (UK)
					break;
				case 'us' : $sitedate = $date->format('Y/n/d');	// Американская (USA)
					break;
				case 'hm' : $sitedate = $date->format('H:i');		// Час:Минута
					break;
				case 'd' : $sitedate = $date->format('d');	// День месяца (01-31)
					break;
				case 'j' : $sitedate = $date->format('j');	// День месяца без ведущих нулей (1-31)
					break;
				case 'F' : $sitedate = $date->format('F');	// Месяц  (текст. полное, прямое)
					break;
				case 'M' : $sitedate = $date->format('M');	// Месяц  (текст. сокр. три буквы)
					break;
				case 'y' : $sitedate = $date->format('y');	// Год (два разряда)
					break;
				case 'Y' : $sitedate = $date->format('Y');	// Год (четыре разряда)
					break;
				case 'l' : $sitedate = $date->format('l');	// Полное название дня недели
					break;
				case 'D' : $sitedate = $date->format('D');	// Сокращенное название дня недели (три буквы)
					break;
				default : $sitedate = $date->format($config['formatdate']);
					break;
				}
			}

			if (is_array($langdate))
			{
				$exdate = ($format == 'P') ? $this->month($date->format('F'), 1) : $sitedate; // 'P' Месяц  (текст. полное, склонение)
				$outdate = strtr(strtolower($exdate), $langdate);
			}

			return ($time) ? $outdate.$outtime : $outdate;
		}
	}

	/**
	 * Месяц в текстовом формате
	 * @ins 0 = прямое (декабрь)
	 * @ins 1 = склонение (декабря)
	 */
	function month($direct, $ins = false)
	{
		$decline = array
		(
			"january"   => 'mont_1',
			"february"  => 'mont_2',
			"march"     => 'mont_3',
			"april"     => 'mont_4',
			"may"       => 'mont_5',
			"june"      => 'mont_6',
			"july"      => 'mont_7',
			"august"    => 'mont_8',
			"september" => 'mont_9',
			"october"   => 'mont_10',
			"november"  => 'mont_11',
			"december"  => 'mont_12',
		);

		return ($ins) ? strtr(strtolower($direct), $decline) : $direct;
	}

	/**
	 * Datetime
	 */
	function timeformat($gtm, $format = 'c')
	{
		global $config;

		$date = new DateTime();
		$date->setTimestamp($gtm);
		$format = str_replace('%', '', $format);
		$datetime = $date->format($format);

		return $datetime;
	}

	function findcat($id, $parent = true)
	{
		global $global;

		if (isset($this->catcache[$id]) AND ! isset($this->catmenucache[$id]))
		{
			$this->catmenucache[$id] = $this->catcache[$id];
			if ($parent) {
				$this->findcat($this->catcache[$id]['parentid']);
			}
		}
	}

	function findsubcat($catcache, $cid = 0)
	{
		static $c = array();

		if ( ! isset($catcache[$cid])){
			return false;
		}
		foreach ($catcache[$cid] as $key => $incat)
		{
			$c[] = $incat['catid'];
			$this->findsubcat($catcache, $incat['catid']);
		}
		unset($catcache[$cid]);

		return $c;
	}

	function sitecat($id, $parent = true, $catname = '')
	{
		global $global, $config, $ro;

		$return = array();
		$this->findcat($id, $parent);
		$this->catmenucache = array_reverse($this->catmenucache);
		$return[] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD).'">'.$global['modname'].'</a>';
		$i = 0;
		foreach ($this->catmenucache as $incat)
		{
			$title = (empty($catname)) ? $this->siteuni($incat['catname']) : $this->siteuni($incat[$catname]);
			$catcpu = ($config['cpu'] == 'yes' AND $incat['catcpu']) ? '&amp;ccpu='.$incat['catcpu'] : '';
			if ($i != count($this->catmenucache) -1 ) {
				$return[] = '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$incat['catid'].$catcpu).'">'.$title.'</a>';
			} else {
				$return[] = (($_REQUEST['to'] == 'page') ? '<a href="'.$ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$incat['catid'].$catcpu).'">'.$title.'</a>' : $title);
			}
			$i ++;
		}
		return $return;
	}

	function printsitecat($catid = 0, $depth = false, $caticon = true)
	{
		global $config, $nobr, $tm, $ro, $lang, $global, $print;

		if ( ! isset($this->subcatcache[$catid])) {
			return false;
		}

		$suburl = NULL;
		$tempsub = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat.sub'));
		$tempicon = $tm->parsein($tm->create('mod/'.WORKMOD.'/cat.icon'));

		foreach ($this->subcatcache[$catid] as $val)
		{
			$icon = $subcat = NULL;
			static $subcat;
			$sum = ($val['total'] > 0) ? '<i>'.$val['total'].'</i>' : '';

			if ($depth == 1)
			{
				$subname = $this->siteuni($val['catname']);
				$subcpu = ($config['cpu'] == 'yes' AND $val['catcpu']) ? '&amp;ccpu='.$val['catcpu'] : '';

				$suburl.= $tm->parse(array(
								'url'  => $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$val['catid'].$subcpu),
								'name' => $subname,
								'sum'  => $sum
							),
							$tm->manuale['sub']);

				$subcat = $tm->parse(array('subcat' => $suburl), $tempsub);
				unset($subcat);

			}

			$this->printsitecat($val['catid'], $depth + 1);

			if ($depth == 0)
			{
				$catname = $this->siteuni($val['catname']);
				$catcpu = ($config['cpu'] == 'yes' AND $val['catcpu']) ? '&amp;ccpu='.$val['catcpu'] : '';
				$caturl = $ro->seo('index.php?dn='.WORKMOD.'&amp;to=cat&amp;id='.$val['catid'].$catcpu);
				if ( ! empty($val['icon']) AND $caticon)
				{
					$icon = $tm->parse(array(
										'icon'		=> $val['icon'],
										'caturl'	=> $caturl,
										'catname'	=> $catname
								),
								$tempicon);
				}
				$desc = ( ! empty($val['catdesc'])) ? '<p>'.$val['catdesc'].'</p>' : '';

				$this->print[] = $tm->parse(array
									(
										'icon'		=> $icon,
										'caturl'	=> $caturl,
										'catname'	=> $catname,
										'sum'		=> $sum,
										'desc'		=> $desc,
										'subcat'	=> $subcat,
										'total'	=> $val['total'],
									),
									$tm->manuale['rows']);
			}
		}

		return $this->print;
	}

	function selcat($cid = 0, $depth = 0, $catid = 0)
	{
		global $selective, $selected;

		if ( ! isset($this->catcache[$cid]))
		{
			return false;
		}

		foreach ($this->catcache[$cid] as $key => $incat)
		{
			$selected = ($incat['catid'] == $catid) ? ' selected="selected"' : '';
			$indent = ($depth == 0) ? '' : str_repeat("&nbsp;&nbsp;",$depth);
			$style = ($depth == 0) ? ' class="oneselect"' : '';
			$this->sel.= '<option value="'.$incat['catid'].'"'.$style.$selected.'>'.$indent.$incat['catname'].'</option>';
			$this->selcat($incat['catid'], $depth + 1, $catid);
		}

		return $this->sel;
	}

	function mapreplace($text)
	{
		return str_replace(array("&", "'", "\"", ">", "<"), array("&amp;", "&#039;", "&quot;", "&gt;", "&lt;"), $text);
	}

	function seokeywords($contents, $symbol = 5, $words = 35)
	{
		$contents = preg_replace
			(
				array("'<[\/\!]*?[^<>]*?>'si", "'([\r\n])[\s]+'si", "'&[a-z0-9]{1,6};'si", "'( +)'si"),
				array("", "\\1 ", " ", " "),
				strip_tags($contents)
			);

		$rearray = array
			(
				"~","!","@","#","$","%","^","&","*","(",")","_","+",
				"`",'"',"№",";",":","?","-","=","|","\"","\\","/",
				"[","]","{","}","'",",",".","<",">","\r\n","\n","\t","«","»"
			);

		$adjectivearray = array
			(
				"ые","ое","ие","ий","ая","ый","ой","ми","ых","ее","ую","их","ым",
				"как","для","что","или","это","этих",
				"всех","вас","они","оно","еще","когда",
				"где","эта","лишь","уже","вам","нет",
				"если","надо","все","так","его","чем",
				"при","даже","мне","есть","только","очень",
				"сейчас","точно","обычно"
			);

		$contents = str_replace($rearray, ' ', $contents);
		$this->keywordcache = explode(' ', $contents);

		$rearray = array();
		foreach ($this->keywordcache as $word)
		{
			if (mb_strlen($word) >= $symbol AND ! is_numeric($word))
			{
				$adjective = mb_substr($word, -2);
				if ( ! in_array($adjective, $adjectivearray) AND ! in_array($word, $adjectivearray))
				{
					$rearray[$word] = (array_key_exists($word, $rearray)) ? ($rearray[$word] + 1) : 1;
				}
			}
		}

		arsort($rearray);
		$this->keywordcache = array_slice($rearray, 0, $words);
		$keywords = '';
		foreach ($this->keywordcache as $word => $count)
		{
			$keywords.= ', '.$word;
		}

		return ltrim(mb_substr($keywords, 1));
	}

	/**
	 * Pagination
	 */
	function pages($table, $id, $page, $func, $num, $p, $count = false)
	{
		global $lang, $ro, $db, $basepref, $c;

		$outpages = array();
		if ($count == 0) {
			$im = $db->fetchassoc($db->query("SELECT COUNT(".$id.") AS total FROM ".$basepref."_".$table.""));
		} else {
			$im['total'] = $count;
		}
		$com = (isset($c) AND ! empty($c)) ? '&amp;c='.$c : '';
		$nums = ceil($im['total'] / $num);
		$outpages[] = '<span class="pagesrow">'.str_replace(array('{p}', '{t}'), array('<strong>'.$p.'</strong>', '<strong>'.$nums.'</strong>'), $lang['pagenation']).'</span>';
		if ($nums <= 1)
		{
			$outpages[] = '<span class="pagesempty">1</span>';
		}
		else
		{
			if ($p > 1) {
				$goback = $p - 1;
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p=1'.$com).'">&laquo;</a>';
				if (($p - 1) == 1) {
					$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$goback.$com).'">&lsaquo;</a>';
				} else {
					$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$goback.$com).'">&lsaquo;</a>';
				}
			}
			for ($i = 1; $i < $nums + 1; $i ++)
			{
				if ($i == $p) {
					$outpages[] = '<span class="pagesempty">'.$i.'</span>';
				} else {
					if (($i > $p) AND ($i < $p + 5) OR ($i < $p) AND ($i > $p - 5))
					{
						if ($i == 1) {
							$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$i.$com).'">'.$i.'</a>';
						} else {
							$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$i.$com).'">'.$i.'</a>';
						}
					}
				}
			}
			if ($p < $nums)
			{
				$gonext = $p + 1;
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$gonext.$com).'">&rsaquo;</a>';
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.'&amp;p='.$nums.$com).'">&raquo;</a>';
			}
		}
		return implode('', $outpages);
	}

	/**
	 * Pagination
	 */
	function compage($table, $id, $page, $func, $num, $c, $count = false)
	{
		global $lang, $ro, $db, $basepref, $p;

		$outpages = array();
		if ($count == 0) {
			$im = $db->fetchassoc($db->query("SELECT COUNT(".$id.") AS total FROM ".$basepref."_".$table.""));
		} else {
			$im['total'] = $count;
		}
		$p = (isset($p) AND ! empty($p)) ? '&amp;p='.$p : '';
		$nums = ceil($im['total'] / $num);
		$outpages[] = '<span class="pagesrow">'.str_replace(array('{p}', '{t}'), array('<strong>'.$c.'</strong>', '<strong>'.$nums.'</strong>'), $lang['pagenation']).'</span>';
		if ($nums <= 1)
		{
			$outpages[] = '<span class="pagesempty">1</span>';
		}
		else
		{
			if ($c > 1) {
				$goback = $c - 1;
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c=1').'">&laquo;</a>';
				if (($c - 1) == 1) {
					$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$goback).'">&lsaquo;</a>';
				} else {
					$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$goback).'">&lsaquo;</a>';
				}
			}
			for ($i = 1; $i < $nums + 1; $i ++)
			{
				if ($i == $c) {
					$outpages[] = '<span class="pagesempty">'.$i.'</span>';
				} else {
					if (($i > $c) AND ($i < $c + 5) OR ($i < $c) AND ($i > $c - 5))
					{
						if ($i == 1) {
							$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$i).'">'.$i.'</a>';
						} else {
							$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$i).'">'.$i.'</a>';
						}
					}
				}
			}
			if ($c < $nums)
			{
				$gonext = $c + 1;
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$gonext).'">&rsaquo;</a>';
				$outpages[] = '<a class="pages" href="'.$ro->seo($page.'.php?dn='.$func.$p.'&amp;c='.$nums).'">&raquo;</a>';
			}
		}
		return implode('', $outpages);
	}

	/**
	 * vCard tel
	 */
	function call_tel($phone, $title = false, $css = false)
	{
		$tel = preg_replace('/\s/', '', $phone);
		$tel = str_replace (array('_', '-', '—'), '', $tel);
		$tel = urlencode($tel);

		$title = ($title) ? $title : $phone;
		$css = ($css) ? ' class="'.$css.'"' : '';

		return '<a'.$css.' href="tel:'.$tel.'">'.$this->siteuni($title).'</a>';
	}
}
