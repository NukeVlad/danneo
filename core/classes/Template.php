<?php
/**
 * File:        /core/classes/Template.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Template
 */
class Template
{
	public $top;
	public $bot;
	public $powered = 0;
	public $manuale  = array();
	public $unmanule = array();

	public function __construct()
	{
		$this->autor_check();
	}

	/**
	 * Шапка, шаблон оформления
	 */
	function header()
	{
		global $db, $config, $lang, $api, $ro, $adv, $global, $dn;

		if ( ! is_object($api) OR ! is_array($global)) exit();

		$this->gzip_start();

		if (isset($_GET['cap']) AND $_GET['cap'] == 'captcha')
		{
			new Captcha;
		}
		else
		{
			@header('Last-Modified: '.gmdate("D, d M Y H:i:s").' GMT');
			@header('Content-Type: text/html; charset='.$config['langcharset']);
			@header('X-Powered-By: Danneo CMS '.$config['version']);
		}

		if (defined('CUSTOM'))
		{
			$global['title'] = CUSTOM;
			$global['site'] = '';
		}
		else
		{
			$global['title'] = ( ! empty($global['title'])) ? $global['title'] : $global['modname'];
			$global['site']  = ' - '.$config['site'];
		}

		$global['insert']['lang'] = $config['langcode'];
		$global['insert']['langcharset'] = $config['langcharset'];
		$global['insert']['site_temp'] = $config['site_temp'];
		$global['insert']['site_desc'] = $config['site_descript'];
		$global['insert']['keywords'] = (empty($global['keywords'])) ? $config['site_keyword'] : $api->sitedp($global['keywords']);
		$global['insert']['descript'] = (empty($global['descript'])) ? $config['site_descript'] : $api->sitedp($global['descript']);
		$global['insert']['title'] = (empty($dn) OR $dn == 'home') ? $api->siteuni($config['site']) : $api->siteuni($global['title']).$global['site'];
		$global['insert']['site'] = $config['site'];
		$global['insert']['version'] = $config['version'];
		$global['insert']['canonical'] = FULL_REQUEST_URI;

		$global['insert']['og_title'] = (isset($global['og_title']) AND ! empty($global['og_title'])) ? $global['og_title'] : $api->siteuni($global['title']);
		$global['insert']['og_desc'] = (isset($global['og_desc']) AND ! empty($global['og_desc'])) ? str_word(deltags($global['og_desc']), 160) : $config['site_descript'];
		$global['insert']['og_image'] = (isset($global['og_image']) AND ! empty($global['og_image'])) ? $global['og_image'] : DNROOT.'up/logo.png';

		$this->unmanule['parent'] = ($global['dn'] <> $config['site_home']) ? 'yes' : 'no';
		$this->unmanule['home'] = ($global['dn'] == 'home') ? 'yes' : 'no';
		$this->unmanule['user'] = (defined('USER_LOGGED')) ? 'yes' : 'no';

		$this->top = $this->parsein($this->create('top'));
		$this->top.= $this->parsein($this->create('insert'));

		$config['separate_crumb'] = ! empty($config['separate_crumb']) ? $config['separate_crumb'] : '<i>»</i>';

		if ( ! empty($global['insert']['breadcrumb']))
		{
			if (is_array($global['insert']['breadcrumb']))
			{
				$bread_crumb = null;
				$end_crumb = array_pop($global['insert']['breadcrumb']);
				foreach ($global['insert']['breadcrumb'] as $crumb)
				{
					$bread_crumb.= $crumb.$config['separate_crumb'];
				}
				$bread_crumb.= $end_crumb;
			}
			else
			{
				$bread_crumb = $global['insert']['breadcrumb'];
			}

			$global['insert']['breadcrumb'] = preg_replace
				(
					'%<a href="(.*?)">(.*?)</a>%',
					'<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="$1" itemprop="url"><span itemprop="title">$2</span></a></span>',
					$bread_crumb
				);

			$global['insert']['breadcrumb'] = $this->parse(array
				(
					'url_root'  => DNROOT,
					'separate'  => $config['separate_crumb'],
					'current'   => $global['insert']['current'],
					'crumbs'    => $global['insert']['breadcrumb'],
					'lang_home' => $lang['home'],
					'mod'       => $global['dn']
				),
				$this->parsein($this->create('breadcrumb'))
			);
		}

		$contents = $api->siteuni($this->parse($global['insert'], $this->top));

		if (is_object($adv))
		{
			$contents = $adv->banners($contents);
		}
		elseif ( ! empty($config['banzoncode_empty']))
		{
			$contents = $this->parse($config['banzoncode_empty'], $contents);
		}

		echo $contents;
	}

	/**
	 * Подвал, шаблон оформления
	 */
	public function footer()
	{
		global $db, $config, $api, $ro, $adv, $global;

		if ( ! is_object($api) OR ! is_array($global)) exit();

		$this->bot = $this->create('bot');

		$global['insert']['copy']    = $api->siteuni($config['site_copy']).' <i>&copy;</i> '.NEWYEAR;
		$global['insert']['count']   = $this->botsys();
		$global['insert']['debug']   = $this->debug();
		$global['insert']['powered'] = $this->powered();

		$contents = $api->siteuni($this->parse($global['insert'], $this->bot));

		if (is_object($adv))
		{
			$contents = $adv->banners($contents);
		}
		elseif ( ! empty($config['banzoncode_empty']))
		{
			$contents = $this->parse($config['banzoncode_empty'], $contents);
		}

		echo $contents;
		$this->gzip_end();
		exit();
	}

	/**
	 * Подключение шаблонов контента
	 */
	public function create($tpl)
	{
		global $config;

		if (in_array($tpl, $this->manuale))
		{
			$contents = $manuale[$tpl];
		}
		else
		{
			$path = DNDIR.'template/'.$config['site_temp'].'/'.$tpl.'.tpl';
			if (file_exists($path))
			{
				$contents = file_get_contents($path);
			} else {
				return '<p>/template/'.$config['site_temp'].'/'.$tpl.'.tpl - Not found!</p>';
			}

			$mtpl = str_replace('/', '_', $tpl);
			$this->manuale[$mtpl] = $contents;
		}

		return ( ! empty($contents)) ? $contents : '<p>'.$tpl.'.tpl - Not found!</p>';
	}

	/**
	 * Подключение шаблонов блоков
	 */
	public function block($tpl)
	{
		if (in_array($tpl, $this->manuale))
		{
			$contents = $manuale[$tpl];
		}
		else
		{
			$contents = file_get_contents(DNDIR.'template/"'.$tpl.'.tpl');
			$mtpl = str_replace('/', '_', $tpl);
			$this->manuale[$mtpl] = $contents;
		}

		return (empty($contents)) ? '<strong>'.$tpl.'.tpl</strong> Not found!<br />' : $contents;
	}

	public function pw($str)
	{
		global $lang;

		if (crc_32($str) != crc_32(dntm()) OR ! $this->powered)
		{
			echo '<div class="error-box">'.stripcslashes($lang['not_copy']).'</div>';
		}
	}

	/**
	 * Парсинг шаблонов, возврат значений
	 */
	public function parse($var, $contents)
	{
		global $lang, $config, $api;

		$sub = array
			(
				'site_url' => rtrim(DNROOT, '/'),
				'self_url' => FULL_REQUEST_URI,
				'site_temp' => $config['site_temp'],
				'site' => $config['site'],
				'lang' => $config['langcode']
			);

		$newkey = $newval = array();
		$result = array_merge($var, $sub);

		foreach ($result as $key => $val)
		{
			if(strpos($key, 'date') === false)
			{
				$newkey[] = '{'.$key.'}';
				$newval[] = $val;
			}
			else
			{
				preg_match_all('/\{'.$key.'\s?\:\s?((?:\%.*?\%?|[0-9a-zA-Z\/]+))\s?\:?\s?((?:[0-9a-zA-Z\/]+))?\}/', $contents, $date);

				if ( ! empty($date[0]))
				{
					foreach ($date[0] as $k => $pattern)
					{
						if ( ! empty($val))
						{
							$first = clear_date($date[1][$k]);
							$second = clear_date($date[2][$k]);

							if ( ! empty($first) AND $first == 'datetime')
							{
								if ($second == 'c') {
									$datetime = $api->timeformat($val, 'c');
								} elseif ($second == 'r') {
									$datetime = $api->timeformat($val, 'r');
								} elseif ($second == 'z') {
									$datetime = $api->timeformat($val, 'z');
								} else {
									if (empty($second)) {
										$datetime = $api->timeformat($val);
									} else {
										$datetime = $api->timeformat($val, $second);
									}
								}
							}
							else
							{
								$datetime = $api->sitetime($val, $first, $second);
							}
							$newkey[] = $pattern;
							$newval[] = $datetime;
						}
						else
						{
							$newkey[] = $pattern;
							$newval[] = null;
						}
					}
				}
				else
				{
					$newkey[] = '{'.$key.'}';
					$newval[] = $api->sitetime($val);
				}
			}
		}

		return str_replace($newkey, $newval, $contents);
	}

	/**
	 * Парсинг, вывод на страницу
	 */
	public function parseprint($var, $contents)
	{
		echo $this->parse($var, $contents);
	}

	/**
	 * Reserve
	 */
	public function contentprint($contents)
	{
		echo $contents;
	}

	/**
	 * Парсинг вложенных шаблонов
	 */
	public function parsein($content)
	{
		if ($count = preg_match_all('#<\!--(if|buffer|add):([a-zA-Z0-9_]*):([a-zA-Z0-9_]*)-->(.*?)<\!--(if|buffer|add)-->#is', $content, $attribut))
		{
			for ($i = 0; $i < $count; $i ++)
			{
				$tags = $attribut[1][$i];
				$pers = $attribut[2][$i];
				$vals = $attribut[3][$i];

				if ($tags == 'if')
				{
					if (isset($this->unmanule[$pers]))
					{
						if ($this->unmanule[$pers] == $vals) {
							$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->', '', $content);
						} else {
							$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', '', $content);
						}
					}
				}

				if ($tags == 'buffer')
				{
					$this->manuale[$pers] = $attribut[4][$i];
					$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', '', $content);
				}

				if ($tags == 'add')
				{
					if (isset($attribut[4][$i]) AND ! empty($attribut[4][$i]))
					{
						$add = file_get_contents(DNDIR.$attribut[4][$i]);
						$content = str_replace('<!--'.$tags.':'.$pers.':'.$vals.'-->'.$attribut[4][$i].'<!--'.$tags.'-->', $add, $content);
					}
				}
			}
		}
		return str_replace('<!--if-->', '', $content);
	}

	/**
	 * Разбивка на несколько колонок
	 */
	public function tableprint($var, $col = 1, $re = FALSE)
	{
		$itr = 0;
		$r = NULL;
		$cnt = count($var);
		$w = intval (100 / $col);

		if($col > 1)
		{
			$row = 0;
			$r = "\n<ul class=\"tables w".$w."\">\n<li>\n<div>\n";
			foreach ($var as $v) {
				$row ++;
				$itr ++;
				$r.= $v;
				if ($row < $col AND $cnt > $itr) {
					$r.= "</div>\n<div>\n";
				} else if ($row < $col) {
					$r.= "</div>\n";
					for ($i = 0; $i < ($col - $row); $i ++) {
						$r.= "<div class=\"null-cell\"></div>\n";
					}
					$r.= "</li>\n";
				}
				if ($row == $col AND $cnt > $itr) {
					$row = 0;
					$r.= "</div>\n</li>\n<li>\n<div>\n";
				} else if ($row == $col) {
					$row = 0;
					$r.= "</div>\n</li>\n";
				}
			}
			$r.= "</ul>\n\n";
		} else {
			foreach ($var as $v) {
				$r.= $v;
			}
		}

		if ($re) {
			echo $r;
		} else {
			return $r;
		}
	}

	/**
	 * Страница редиректа
	 */
	public function redirectprint($title, $sec, $message, $url)
	{
		global $config, $lang;

		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Content-Type: text/html; charset=".$config['langcharset']."");

		$redirlink = '<a href="'.$url.'">'.$lang['redirect_text'].'</a>';
		$this->parseprint(array
			(
				'site_url'    => SITE_URL,
				'langcharset' => $config['langcharset'],
				'site_temp'   => $config['site_temp'],
				'title'       => $title,
				'message'     => $message,
				'link'        => $redirlink,
				'url'         => $url,
				'sec'         => $sec
			),
			$this->create('redirect')
		);

		exit();
	}

	/**
	 * Форма доступа
	 */
	public function noaccessprint($re = FALSE, $title = FALSE, $ajax = FALSE, $block = FALSE)
	{
		global $config, $global, $userapi, $lang, $ro;

		$userapi->data['linkreg'] = (isset($userapi->data['linkreg']) AND (empty($userapi->data['linkreg']) == FALSE)) ? $userapi->data['linkreg'] : 'index.php?dn=user&amp;re=register';
		$userapi->data['linklost'] = (isset($userapi->data['linklost']) AND (empty($userapi->data['linklost']) == FALSE)) ? $userapi->data['linklost'] : 'index.php?dn=user&amp;re=login&amp;to=lost';

		$this->unmanule['captcha'] = ($config['captcha']=='yes' AND defined('REMOTE_ADDRS')) ? 'yes' : 'no';
		$this->unmanule['title'] = ($title AND ! $ajax) ? 'yes' : 'no';
		$this->unmanule['logged'] = 'no';

		$block = ($block) ? 'noaccess' : 'userblock';
		if ( $re )
		{
			return $this->parse(array
			(
				'title'     => $lang['ou_title'],
				'post_url'  => $ro->seo('index.php?dn=user'),
				'registr'   => $lang['registr'],
				'login'     => $lang['login'],
				'pass'      => $lang['pass'],
				'linklost'  => $ro->seo($userapi->data['linklost']),
				'linkreg'   => $ro->seo($userapi->data['linkreg']),
				'maxname'   => $config['user']['maxname'],
				'maxpass'   => $config['user']['maxpass'],
				'send_pass' => $lang['send_pass'],
				'to_enter'  => $lang['to_enter'],
				'enter'     => $lang['enter'],
				'redirect'  => (defined('REDIRECT') ? '<input name="redirect" value="'.REDIRECT.'" type="hidden" />'.(defined('REDIRECTCPU') ? '<input name="redirectcpu" value="1" type="hidden" />' : '') : '')
			),
			$this->parsein($this->create($block)));
		}
		else
		{
			if ( ! $ajax )
				$this->header();

			$this->parseprint(array
			(
				'title'     => $lang['ou_title'],
				'post_url'  => $ro->seo('index.php?dn=user'),
				'registr'   => $lang['registr'],
				'login'     => $lang['login'],
				'pass'      => $lang['pass'],
				'linklost'  => $ro->seo($userapi->data['linklost']),
				'linkreg'   => $ro->seo($userapi->data['linkreg']),
				'maxname'   => $config['user']['maxname'],
				'maxpass'   => $config['user']['maxpass'],
				'send_pass' => $lang['send_pass'],
				'to_enter'  => $lang['to_enter'],
				'enter'     => $lang['enter'],
				'redirect'  => (defined('REDIRECT') ? '<input name="redirect" value="'.REDIRECT.'" type="hidden" />'.(defined('REDIRECTCPU') ? '<input name="redirectcpu" value="1" type="hidden" />' : '') : '')
			),
			$this->parsein($this->create('noaccess')));

			if ( ! $ajax )
				$this->footer();
		}
	}

	/**
	 * Форма поиска
	 */
	public function search($set, $mod, $re = FALSE)
	{
		global $ro, $lang;

		if ($set == 'yes')
		{
			if ($re)
			{
				return $this->parse(array
				(
					'post_url' => $ro->seo('index.php?dn='.$mod.'&re=search'),
					'search_input_word' => $lang['search_input_word'],
					'search' => $lang['search']
				),
				$this->create('mod/'.$mod.'/form.search'));
			}
			else
			{
				$this->parseprint(array
				(
					'post_url' => $ro->seo('index.php?dn='.$mod.'&re=search'),
					'search_input_word' => $lang['search_input_word'],
					'search' => $lang['search']
				),
				$this->create('mod/'.$mod.'/form.search'));
			}
		}
	}

	/**
	 * Страница не существует
	 */
	public function noexistprint()
	{
		global $config, $lang;

		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');

		$this->parseprint(array
			(
				'site_url'          => SITE_URL,
				'langcharset'       => $config['langcharset'],
				'site_temp'         => $config['site_temp'],
				'noexit_page_title' => $lang['noexit_page_title'],
				'message'           => $lang['noexit_page'],
				'go_back'           => $lang['all_goback']
			),
			$this->create('noexist'));

		exit();
	}

	/**
	 * Страница не существует, box
	 */
	public function noexist()
	{
		global $lang, $config;

		if ($config['gzip'] == 'yes')
		{
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		}

		$this->parseprint(array
			(
				'isset_error'	=> $lang['isset_error'],
				'error' => $lang['noexit_page'],
				'style' => 'err',
				'go_back' => $lang['all_goback']
			),
			$this->parsein($this->create('error')));

		$this->footer();
	}

	/**
	 * Закрыть сайт
	 */
	public function closeprint()
	{
		global $config, $lang;

		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Type: text/html; charset='.$config['langcharset'].'');
		header($_SERVER['SERVER_PROTOCOL'].' 503 Service Unavailable');
		header('Retry-After: 3600');

		$this->parseprint(array
			(
				'site_url'    => SITE_URL,
				'langcharset' => $config['langcharset'],
				'titlemess'   => $lang['inf_mess'],
				'site_temp'   => $config['site_temp'],
				'closedtext'  => $config['closedtext']
			),
			$this->create('closed'));

		exit();
	}

	/**
	 * Сообщение, нет прав доступа
	 */
	public function norightprint($error = NULL, $box = TRUE, $ajax = FALSE, $stop = FALSE, $re = FALSE)
	{
		global $lang, $config;

		$this->unmanule['box'] = ($box) ? 'yes' : 'no';

		if ( $box AND ! $ajax )
			$this->header();

		if ($re)
		{
			return $this->parse(array
				(
					'title'	=> $lang['isset_error'],
					'text'		=> isset($error) ? $error : $lang['no_rights'],
					'go_back'	=> $lang['all_goback']
				),
				$this->parsein($this->create('noright')));
		}
		else
		{
			$this->parseprint(array
				(
					'title'	=> $lang['isset_error'],
					'text'		=> isset($error) ? $error : $lang['no_rights'],
					'go_back'	=> $lang['all_goback']
				),
				$this->parsein($this->create('noright')));
		}

		if ( $box AND ! $ajax )
			$this->footer();

		if ( $stop )
			exit();
	}

	/**
	 * Вывод ошибок
	 */
	public function error($error, $title = NULL, $ajax = FALSE, $button = TRUE, $style = TRUE, $ins = FALSE)
	{
		global $config, $lang;

		$ajax = ($config['ajax'] == 'yes' AND $ajax ) ? 1 : 0;
		$this->unmanule['go'] = ($button) ? 'yes' : 'no';
		$this->unmanule['title'] = ($title !== 0 AND ! $ajax) ? 'yes' : 'no';
		$box = ($ajax) ? 'box' : 'error';
		$title = isset($title{0}) ? $title : $lang['isset_error'];
		$style = (isset($title{0}) AND ! $ajax AND $style) ? 'err' : '';

		if ( ! $ajax AND ! $ins )
			$this->header();
		$this->parseprint(array(
			'isset_error'	=> $title,
			'error'		=> $error,
			'style'		=> $style,
			'go_back'		=> $lang['all_goback']
		),
		$this->parsein($this->create($box)));
		if ( ! $ajax AND ! $ins )
			$this->footer();
		if ( $ajax )
			exit();
	}

	/**
	 * Вывод сообщений
	 */
	public function message($message, $title = NULL, $button = TRUE, $box = FALSE)
	{
		global $config, $lang;

		$this->unmanule['go'] = ($button == TRUE) ? 'yes' : 'no';
		$this->unmanule['title'] = ( ! isset($title) OR isset($title{0}) OR $title == TRUE) ? 'yes' : 'no';
		$title = isset($title{0}) ? $title : $lang['inf_mess'];

		if ( ! $box ) $this->header();

		$this->parseprint(array(
			'title'	=> $title,
			'text'		=> $message,
			'go_back'	=> $lang['all_goback']
		),
		$this->parsein($this->create('message')));

		if ( ! $box ) $this->footer();
	}

	public function autor_check()
	{
		global $lang, $config;

		$this->powered = 1;
		$this->bot = $this->create('bot');
		if (strpos($this->bot, '{powered}') == FALSE)
		{
			return '<div class="error-box">'.stripcslashes($lang['not_copy']).'</div>';
			$this->powered = 0;
		}
	}

	public function debug()
	{
		global $db, $config;

		if ($config['debug_db'] == 'yes')
		{
			return $this->parse(array('explain' => $db->explain), $this->create('debug'));
		}
		return FALSE;
	}

	public function botsys()
	{
		global $db, $config;

		if ($config['botsys'] == 'yes')
		{
			$totaltime = microtime(TRUE) - TIMESTART;
			$pgt = 'PG.t: '.substr($totaltime, 0, 5);
			$dbq = 'DB.q: '.$db->sqlcount;
			$frq = ($config['cache'] == 1) ? 'FR.q: '.$db->filecount.' &nbsp; ' : '';
			$dbt = 'DB.t: '.substr($db->totaltime, 0, 6);
			return $pgt.' &nbsp; '.$dbq.' &nbsp; '.$frq.$dbt;
		}

		return FALSE;
	}

	public function powered()
	{
		global $config, $lang;

		$imgcopy = '<img src="'.DNROOT.'template/'.$config['site_temp'].'/images/power.gif" alt="'.$lang['powered'].'" />';
		if (REQUEST_URI == DNROOT) {
			return '<a href="http://danneo.ru">'.$imgcopy.'</a>';
		} else {
			return '<a class="dncopy" href="'.SITE_URL.'">'.$imgcopy.'</a>';
		}
	}

	/**
	 * Gzip Start
	 */
	public function gzip_start()
	{
		global $config;

		if (extension_loaded('zlib') AND $config['gzip'] == 'yes')
		{
			ob_start();
			ob_implicit_flush(0);
		}
	}

	/**
	 * Gzip Output
	 */
	public function gzip_end()
	{
		global $config;

		if (extension_loaded('zlib') AND $config['gzip'] == 'yes')
		{
			if(headers_sent()) {
				$gzipenc = FALSE;
			} elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== FALSE) {
				$gzipenc = 'x-gzip';
			} elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== FALSE) {
				$gzipenc = 'gzip';
			} else {
				$gzipenc = FALSE;
			}

			if( $gzipenc )
			{
				$contents = ob_get_contents();
				ob_end_clean();

				$width = strlen($contents);
				if ($width < 2048) {
					echo ($contents);
				} else {
					header('Content-Encoding: '.$gzipenc);
					echo("\x1f\x8b\x08\x00\x00\x00\x00\x00");
					$contents = gzcompress($contents, $config['gziplevel']);
					$contents = substr($contents, 0, $width);
					echo ($contents);
				}
			}
			else
			{
				ob_end_flush();
			}
		}
	}

	/**
	 * Выделение слов в контексте содержимого.
	 *
	 * @param string | array, $word - Слово | Массив слов
	 * @param string $contents Контекст
	 */
	public function wordlight($word, $contents)
	{
		if( ! $word OR ! $contents) return $contents;
		$word = (array) $word;
		foreach($word as $k => $v)
		{
			$v = preg_quote(str_replace(array('<', '>'), '', trim($v)), '/');
			if ($v == '') unset($word[$k]);
		}
		return preg_replace_callback('/(?<=>|^)([^<]+)/',
			function ($contents) use ($word)
			{
				return preg_replace('/(?:\b)('.join('|',$word).')(?:\b)/ui','<mark class="word-light">$1</mark>',$contents[1]);
			},
			$contents
		);
	}
}
