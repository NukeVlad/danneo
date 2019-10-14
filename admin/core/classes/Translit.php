<?php
/**
 * File:        /admin/core/classes/Translit.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Transliterates UTF-8 encoded text to US-ASCII.
 * Based on Mediawiki's UtfNormal::quickIsNFCVerify().
 *
 * @param $string: UTF-8 encoded text input.
 * @param $unknown: Replacement string for characters that do not have a suitable ASCII equivalent.
 * @param $langcode: Optional ISO 639 language code that denotes the language of the input and is used to apply language-specific variations.
 * @return Transliterated text.
 *
 * It is based on the development of
 * @link  https://github.com/AmazeeLabs/transliteration
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Translit
 */
class Translit
{
	function __construct()
	{
	}

	function process($string, $unknown = '?', $langcode = NULL)
	{
		if ( ! preg_match('/[\x80-\xff]/', $string)) {
			return $string;
		}
		static $tail_bytes;

		if ( ! isset($tail_bytes))
		{
			$tail_bytes = array();
			for ($n = 0; $n < 256; $n++)
			{
				if ($n < 0xc0) {
					$re = 0;
				} elseif ($n < 0xe0) {
					$re = 1;
				} elseif ($n < 0xf0) {
					$re = 2;
				} elseif ($n < 0xf8) {
					$re = 3;
				} elseif ($n < 0xfc) {
					$re = 4;
				} elseif ($n < 0xfe) {
					$re = 5;
				} else {
					$re = 0;
				}
				$tail_bytes[chr($n)] = $re;
			}
		}

		preg_match_all('/[\x00-\x7f]+|[\x80-\xff][\x00-\x40\x5b-\x5f\x7b-\xff]*/', $string, $matches);

		$result = '';
		foreach ($matches[0] as $str)
		{
			if ($str[0] < "\x80")
			{
				$result .= $str;
				continue;
			}

			$head = '';
			$chunk = strlen($str);
			$len = $chunk + 1;

			for ($i = -1; --$len; )
			{
				$c = $str[++$i];
				if ($re = $tail_bytes[$c])
				{
					$sequence = $head = $c;
					do {
						if (--$len AND ($c = $str[++$i]) >= "\x80" AND $c < "\xc0") {
							$sequence .= $c;
						} else {
							if ($len == 0) {
								$result .= $unknown;
								break 2;
							} else {
								$result .= $unknown;
								--$i;
								++$len;
								continue 2;
							}
						}
					}
					while (--$re);

					$n = ord($head);
					if ($n <= 0xdf) {
						$ord = ($n - 192) * 64 + (ord($sequence[1]) - 128);
					} elseif ($n <= 0xef) {
						$ord = ($n - 224) * 4096 + (ord($sequence[1]) - 128) * 64 + (ord($sequence[2]) - 128);
					} elseif ($n <= 0xf7) {
						$ord = ($n - 240) * 262144 + (ord($sequence[1]) - 128) * 4096 + (ord($sequence[2]) - 128) * 64 + (ord($sequence[3]) - 128);
					} elseif ($n <= 0xfb) {
						$ord = ($n - 248) * 16777216 + (ord($sequence[1]) - 128) * 262144 + (ord($sequence[2]) - 128) * 4096 + (ord($sequence[3]) - 128) * 64 + (ord($sequence[4]) - 128);
					} elseif ($n <= 0xfd) {
						$ord = ($n - 252) * 1073741824 + (ord($sequence[1]) - 128) * 16777216 + (ord($sequence[2]) - 128) * 262144 + (ord($sequence[3]) - 128) * 4096 + (ord($sequence[4]) - 128) * 64 + (ord($sequence[5]) - 128);
					}
					$result .= $this->replace($ord, $unknown, $langcode);
					$head = '';
				}
				elseif ($c < "\x80")
				{
					$result .= $c;
					$head = '';
				}
				elseif ($c < "\xc0")
				{
					if ($head == '') {
						$result .= $unknown;
					}
				}
				else
				{
					$result .= $unknown;
					$head = '';
				}
			}
		}
		return $result;
	}

	function replace($ord, $unknown = '?', $langcode = NULL)
	{
		global $conf;

		static $map = array();

		if ( ! isset($langcode))
		{
			$langcode = $conf['langcode'];
		}

		$bank = $ord >> 8;

		if ( ! isset($map[$bank][$langcode]))
		{
			$file = WORKDIR.'/core/includes/data/'.sprintf('x%02x', $bank).'.php';
			if (file_exists($file))
			{
				include $file;
				if ($langcode != 'en' AND isset($variant[$langcode]))
				{
					$map[$bank][$langcode] = $variant[$langcode] + $base;
				}
				else
				{
					$map[$bank][$langcode] = $base;
				}
			}
			else
			{
				$map[$bank][$langcode] = array();
			}
		}

		$ord = $ord & 255;

		return isset($map[$bank][$langcode][$ord]) ? $map[$bank][$langcode][$ord] : $unknown;
	}

	function title($title, $separator = '-')
	{
		$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', strtolower($title));
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

		// Замена сходных по написанию символов :: кириллица > латиница
		if (preg_match('/^[а-я]+$/iu', $title) == true)
		{
			$title = str_replace(
						array("е", "у", "и", "о", "р", "а", "к", "х", "с"), // к
						array("e", "y", "u", "o", "p", "a", "k", "x", "c"), // л
						$title
					);
		}

		return trim($title, $separator);
	}
}
