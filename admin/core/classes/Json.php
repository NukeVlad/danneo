<?php
/**
 * File:        /admin/core/Json/Dump.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Json
 *
 */
class Json
{
    const JSON_PRETTY_PRINT = 128;
    const JSON_UNESCAPED_SLASHES = 64;
    const JSON_UNESCAPED_UNICODE = 256;

	public function __construct() { }

	/**
	 * @param mixed $value
	 * @param int $options
	 * @return string
	 * @throws string json_last_error_msg
	 */
	public static function encode($value, $options = 320)
	{
		if (PHP_VERSION_ID >= 50400) {
			$json = json_encode($value, $options);
			if (false === $json) {
				self::json_error('encode');
			}
            if (PHP_VERSION_ID < 50428 || (PHP_VERSION_ID >= 50500 && PHP_VERSION_ID < 50512) || (defined('JSON_C_VERSION') && version_compare(phpversion('json'), '1.3.6', '<'))) {
                $json = preg_replace('/\[\s+\]/', '[]', $json);
                $json = preg_replace('/\{\s+\}/', '{}', $json);
            }
		} else {
			$json = preg_replace_callback
				(
					'/\\\\u([0-9a-zA-Z]{4})/',
					function ($matches) {
						return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
					},
					json_encode($value, $options)
				);
		}
		return $json;
    }

	/**
	 * @param $json
	 * @param bool $assoc
	 * @return mixed
	 * @throws string json_last_error_msg
	 */
	public static function decode($json, $assoc = true)
	{
        if (null === $json) {
            return;
        } else {
			return json_decode($json, $assoc);
			self::json_error('decode');
		}
	}

	/**
	 * Pretty-print JSON string
	 * @param string $data Original JSON string
	 * @return string
	 */
	public static function json_print($data, $options = 448)
	{
		if (PHP_VERSION_ID >= 50400) {
			$result = json_encode($data, $options);
			self::json_error('print');
		} else {
			$result = self::format_print(self::encode($data));
		}
		return $result;
	}

	/**
	 * Pretty-print format JSON string
	 * @param string $json Original JSON string
	 * @return string
	 */
	public static function format_print($json, $options = 64)
	{
		$pos = 0;
		$indent = "\t";
		$line = "\n";
		$quotes = true;
		$escape = true;

		$result = $buffer = '';
		for ($i = 0; $i < strlen($json); $i ++)
		{
			$char = substr($json, $i, 1);
			if ('"' === $char AND $escape) {
				$quotes = ! $quotes;
			}

			if ( ! $quotes)
			{
				$buffer.= $char;
				$escape = '\\' === $char ? ! $escape : true;
				continue;
			}
			elseif ('' !== $buffer)
			{
				if ((bool)($options & JSON_UNESCAPED_SLASHES))
				{
					$buffer = str_replace('\\/', '/', $buffer);
				}
				$result.= $buffer.$char;
				$buffer = '';
				continue;
			}
			elseif (strpos(" \t\r\n", $char) !== false)
			{
				continue;
			}

			if (':' === $char)
			{
				$char.= " ";
			}
			elseif (('}' === $char OR ']' === $char))
			{
				$pos --;
				$prev = substr($json, $i - 1, 1);
				if ('{' !== $prev AND '[' !== $prev)
				{
					$result.= $line;
					for ($j = 0; $j < $pos; $j++) {
						$result.= $indent;
					}
				} else {
					$result = rtrim($result) . "\n\n" . $indent;
				}
			}

			$result.= $char;

			if (',' === $char OR '{' === $char OR '[' === $char)
			{
				$result.= $line;
				if ('{' === $char OR '[' === $char) {
					$pos ++;
				}
				for ($j = 0; $j < $pos; $j++)
				{
					$result.= $indent;
				}
			}
		}
		if (strlen($buffer) > 0)  {
			$result = false;
		}
		return $result;
	}

	/**
	 * @throws string json_last_error_msg
	 */
	private static function json_error($type)
	{
		$message = array
			(
				JSON_ERROR_NONE           => 'No error has occurred',
				JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
				JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
				JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
				JSON_ERROR_SYNTAX         => 'Syntax error',
				JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
			);

		$code = json_last_error();

		if ($code > 0)
		{
			$error = (isset($message[$code])) ? $message[$code] : 'Unknown error';
			if (ini_get('display_errors'))
			{
				ob_start();
				echo '<div class="error-box">Could not '.$type.' JSON - '.$error.'</div>';
				ob_end_flush();
			}
		}
	}

	/**
	 * @throws Is Json
	 */
	public static function is_json($str)
	{
		return is_string($str) AND (is_object(Json::decode($str)) OR is_array(Json::decode($str))) ? TRUE : FALSE;
	}
}
