<?php
/**
 * File:        /core/classes/Router.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Route
 */
class Router
{
	/**
	 * Clear URI
	 * @type string
	 */
	protected $uri = '';

	/**
	 * Redirect delay
	 * @type integer
	 */
	protected $sec = 5;

	/**
	 * Name mod
	 * @type array
	 */
	protected $mod = array();

	/**
	 * Array of the files rules
	 * @type array
	 */
	protected $rules = array();

	/**
	 * Array of the rules
	 * @type array
	 */
	protected $total = array();

	/**
	 * Array of the rules mode
	 * @type array
	 */
	protected $routes = array();

	/**
	 * Class Route _contructor
	 */
	public function __construct()
	{
		if (isset($_GET['go']))
		{
			$this->redirect();
		}

		$this->_uri();
		$this->routes();
		$this->parsing();

		$this->globals();
	}

	/**
	 * Function SEF parsing routes
	 */
	public function parsing()
	{
		if ( ! empty($this->uri) )
		{
			foreach($this->routes as $pattern => $route)
			{
				$pattern = "~^".$this->suf($pattern)."$~";
				if(preg_match($pattern, $this->uri))
				{
					$url = preg_replace($pattern, $route, $this->uri);
					$url = parse_url($url);
					if (isset($url['query']))
					{
						parse_str($url['query'], $params);
						$_REQUEST = array_merge($_REQUEST, $params);
					}
					break;
				}
			}
		}

		/**
		 * 404 - Not Found
		 */
		if (empty($_REQUEST) AND ! empty($this->uri))
		{
			$this->_404();
		}
	}

	/**
	 * Function set of the routes
	 */
	public function routes()
	{
		$this->rules = new GlobIterator(DNDIR.'mod/*/mod.rules.php');
		foreach ($this->rules as $file)
		{
			$this->total += include($file->getPathname());
		}

		if (isset($this->mod[0]))
		{
			if (isset($this->mod[1]) AND isset($this->total[$this->mod[0]]))
			{
				$this->mod = $this->total[$this->mod[0]];
			}
			else
			{
				$this->mod = $this->total['pages'];
			}
			$this->routes = $this->mod['to'];
		}
	}

	/**
	 * Function clear uri
	 */
	public function _uri()
	{
		$sub_root = str_replace(DOCUMENT_ROOT.'/', '', DNDIR);
		$real_uri = str_replace($sub_root, '', REQUEST_URI);
		$this->uri = ($real_uri == '/') ? trim($real_uri, '/') : ltrim($real_uri, '/');
		$this->mod = explode('/', $this->uri);
	}

	/**
	 * Function suffix
	 */
	public function suf($string)
	{
		return (substr($string, -1) != '/') ? $string.SUF : $string;
	}

	/**
	 * Function URL Parse
	 */
	public function seo($url, $direct = FALSE)
	{
		if (defined('SEOURL'))
		{
			$url = $this->_clear($url);
			$parse = parse_url($url);
			if (isset($parse['query']))
			{
				parse_str($parse['query'], $params);
				if (isset($params['dn']) AND isset($this->total[$params['dn']]))
				{
					foreach($this->total[$params['dn']]['re'] as $pattern => $route)
					{
						$pattern = "~^".$pattern."$~";
						if(preg_match($pattern, $url))
						{
							$url = preg_replace($pattern, $route, $url);
							$url = $this->suf($url);
							break;
						}
					}
				}
			}
		}
		return ($direct) ? SITE_URL.'/'.$url : DNROOT.$url;
	}

	/**
	 * Function clear url
	 */
	public function _clear($url)
	{
		//$url = $this->similar($url);
		if ($url != '/')
		{
			$parse = parse_url($url);
			if (isset($parse['query']))
			{
				$url = ltrim($parse['path'], '/').'?'.$parse['query'];
				return preg_replace('/&amp;([a-z0-9#]{2,6};)/u', '&$1', str_replace('&amp;', '&', $url));
			}
		}

		return $url;
	}

	/**
	 * Replacement is similar to writing characters
	 */
	public function similar($str)
	{
		if (preg_match('/[а-я]+/', $str) == TRUE)
		{
			$str = str_replace(
				array("е", "у", "и", "о", "р", "а", "к", "х", "с"), // к
				array("e", "y", "u", "o", "p", "a", "k", "x", "c"), // л
				$str
			);
		}
		return $str;
	}

	/**
	 * Function no exist
	 */
	public function redirect()
	{
		global $tm, $lang;

		$go_url = SITE_URL.'/';

		if (preg_match("/^[http|https]+[:\/\/]+[\pL\pNd\-_]+\\.+[\pL\pNd\.\/%&=\?\-_]+$/ui", $_GET['go']))
		{
			$go_url = $_GET['go'];
		}
		else
		{
			$this->_404();
		}

		$host_url = parse_url($go_url, PHP_URL_HOST);
		$view_url = preg_replace('/([^\s]{'.intval(50).'})+/uU', '$1'.' ', $go_url);
		$message = $lang['redirect_title'].' <a href="'.$go_url.'">'.$view_url.'</a>';

		return $tm->redirectprint('Redirect > '.$host_url, $this->sec, $message, $go_url);
	}

	/**
	 * Function no exist
	 */
	public function _404()
	{
		global $tm;

		$tm->noexistprint();
	}

	/**
	 * Function extract globals
	 */
	public function globals()
	{
		if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
		{
			echo "Global variable overload attack detected!\n";
			exit(1);
		}

		$global_var = array
		(
			'_COOKIE',
			'_ENV',
			'_GET',
			'_FILES',
			'_POST',
			'_REQUEST',
			'_SERVER',
			'_SESSION',
			'GLOBALS',
		);

		foreach($global_var as $name)
		{
			unset($_COOKIE[$name]);
			unset($_POST[$name]);
			unset($_GET[$name]);
			unset($_REQUEST[$name]);
		}

		if ( ! ini_get('register_globals'))
		{
			foreach($_REQUEST as $k => $v)
			{
				if( ! isset($array[$k]))
				{
					$GLOBALS[$k] = $v;
				}
			}
		}
	}
}
