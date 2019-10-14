<?php
/**
 * File:        /core/classes/Video.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Video
 */
class Video
{
	public 	$url,
			$title,
			$image,
			$thumb,
			$video,
			$service,
			$duration;

	public $result = array();

	/*
	 * Init
	 * */
	public function __construct($url)
	{
		$url = $this->clear_url($url);
		if (
			filter_var($url, FILTER_VALIDATE_URL) !== FALSE AND
			$this->curl($url, 1) < '404'
		) {
			$this->url = $url;
			$metod = $this->get_metod($url);

			if ( ! method_exists(__CLASS__, $metod))
				return FALSE;

			$this->$metod();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Service vimeo.ru
	 * @param string $url
	 * @return format video link, preview image
	 * */
	private function vimeo()
	{
		$this->parse('property');
		if ( ! empty($this->result))
		{
			$this->title = $this->result['og:title'];
			$this->image = $this->result['og:image'];
			$match = explode('/', $this->result['og:url']);
			$this->video = 'https://player.vimeo.com/video/'.end($match);
			$this->duration = $this->ld_json();
			$this->service = 'Vimeo';
		}
	}

	/*
	 * Service youtube.ru
	 * @param string $url
	 * @return format video link, preview image
	 * */
	private function youtu()
	{
		$this->parse('property', true);
		if ( ! empty($this->result))
		{
			parse_str(parse_url($this->result['og:url'], PHP_URL_QUERY), $params);

			$this->title = $this->result['og:title'];
			$this->image = $this->result['og:image'];
			$this->video = 'https://www.youtube.com/embed/'.$params['v'];
			$this->service = 'Youtube';
		}

		$this->parse('itemprop', true);
		if ( ! empty($this->result))
		{
			$this->duration = $this->iso8601_sec($this->result['duration']);
		}
	}

	/*
	 * Service rutube.ru
	 * @param string $url
	 * @return format video link, preview image
	 * */
	private function rutube()
	{
		$this->parse('property');
		if ( ! empty($this->result))
		{
			$this->title = $this->result['og:title'];
			$this->image = $this->result['og:image'];
			$this->video = $this->result['og:video:iframe'];
			$this->duration = $this->result['og:video:duration'];
			$this->service = 'Rutube';
		}
	}

	/*
	 * Service vk.ru
	 * @param string $url
	 * @return format video link, preview image
	 * */
	private function vk()
	{
		$this->parse('property');
		if ( ! empty($this->result))
		{
			parse_str(parse_url($this->result['og:video'], PHP_URL_QUERY), $param);

			$this->title = $this->result['og:title'];
			$this->image = $this->result['og:image'];
			$this->video ='https://vk.com/video_ext.php?oid='.$param['oid'].'&id='.$param['vid'].'&hash='.$param['embed_hash'];
			$this->duration = $this->result['og:video:duration'];
			$this->service = 'ВКонтакте';
		}
	}

	/*
	 * Html Parser tags Meta
	 * @param attribute name
	 * @return array video link, preview image
	 * */
	private function parse($name, $convert = false)
	{
		if($contents = file_get_contents($this->url))
		{
			$dom = new \DOMDocument;
			libxml_use_internal_errors(true); $dom->loadHTML($contents);

			foreach ($dom->getElementsByTagName('meta') as $meta)
			{
				$key = FALSE;
				$val = FALSE;

				if ($meta->hasAttribute($name))
					$key = $meta->getAttribute($name);

				if ($meta->hasAttribute('content'))
					$val = $meta->getAttribute('content');

				if ($key AND $val)
					$this->result[$key] = ($convert) ? utf8_decode($val) : $val;
			}
			return FALSE;
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Check metod service
	 * @param url
	 * @return name metod
	 * */
	private function get_metod($url)
	{
		$hosts = explode('.', parse_url($url, PHP_URL_HOST)); array_pop($hosts);
		$out = (strpos(end($hosts), 'youtu') !== FALSE) ? 'youtu' : end($hosts);
		return $out;
	}

	/*
	 * cURL
	 * */
	public function curl($url, $code = FALSE)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if ($code) {
			curl_exec($ch);
			return curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		return curl_exec($ch);
		curl_close($ch);
	}

	/*
	 * Clear url
	 * @param url
	 * @return sanitize url
	 * */
	private function clear_url($url)
	{
		$scheme = parse_url($url, PHP_URL_SCHEME);
		$url = ( ! isset($scheme) AND substr($url, 0, 2) == '//') ? 'http:'.$url : $url;
		return filter_var($url, FILTER_SANITIZE_URL);
	}

	/*
	 * Convert ISO-8601 in seconds
	 * @param time ISO-8601
	 * @return seconds
	 * */
	private function iso8601_sec($iso)
	{
		$int = new \DateInterval($iso);
		return $int->h * 360 + $int->i * 60 + $int->s;
	}

	/*
	 * Get duration from JSON-LD
	 * @param url
	 * @return time seconds
	 * */
	private function ld_json()
	{
		$html = $this->curl($this->url);

		$dom = new DOMDocument;
		$dom->loadHTML($html);
		$tag = $dom->getElementsByTagName('script');

		$node = '';
		foreach ($tag as $val) {
			if ($val->getAttribute('type') == 'application/ld+json') {
				$node = $val->nodeValue;
			}
		}

		if ( ! empty($node)) {
			$node = Json::decode($node);
			return $this->iso8601_sec($node[0]['duration']);
		}

		return false;
	}
}
