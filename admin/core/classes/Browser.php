<?php
/**
 * File:        /admin/core/classes/Browser.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

class Browser
{
	public $curl_resource       = null;
	public $curl_failonerror    = false;
	public $curl_followlocation = true;
	public $curl_returntransfer = true;
	public $curl_timeout        = 15;
	public $curl_post           = true;

	public $curl_useragent = array
	(
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3',
		'Opera/9.80 (Windows NT 5.1; U; en) Presto/2.5.22 Version/10.51',
		'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'
	);

	public function __construct()
	{
		$this->curl_resource = curl_init();
	}

	public function content($url)
	{
		$r = rand(0, count($this->curl_useragent) - 1);

		curl_setopt($this->curl_resource, CURLOPT_URL , $url);
		curl_setopt($this->curl_resource, CURLOPT_FAILONERROR , $this->curl_failonerror);
		curl_setopt($this->curl_resource, CURLOPT_RETURNTRANSFER , $this->curl_returntransfer);
		curl_setopt($this->curl_resource, CURLOPT_TIMEOUT , $this->curl_timeout);
		curl_setopt($this->curl_resource, CURLOPT_USERAGENT, $this->curl_useragent[$r]);
		curl_setopt($this->curl_resource, CURLOPT_FOLLOWLOCATION , true);
		curl_setopt($this->curl_resource, CURLOPT_MAXREDIRS, 10);
		curl_setopt($this->curl_resource, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->curl_resource, CURLOPT_SSL_VERIFYPEER, false);

		return array
				(
					'page'   => curl_exec($this->curl_resource),
					'error'  => curl_errno($this->curl_resource),
					'errmsg' => curl_error($this->curl_resource)
				);
	}

	public function close()
	{
		curl_close($this->curl_resource);
	}
}
