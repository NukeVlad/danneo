<?php
/**
 * File:        /core/classes/IDNA.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class IDNA
 * Punycode конвертер
 *
 * Example
 * echo IDNA::encode('даннео.рф');
 * echo IDNA::decode('xn--80ahe5aah.xn--p1ai');
 * echo IDNA::encode('даннео.рус');
 * echo IDNA::decode('xn--80ahe5aah.xn--p1acf');
 *
 */
class IDNA
{
	protected static $_prefix = 'xn--';
	protected static $_overload = null;

	/**
	 * Constructor
	 * @return boolean
	 */
	public function __construct($options = false)
	{
		if (self::$_overload === null)
		{
			self::$_overload = extension_loaded('mbstring');
		}
	}

	/**
	 * Encode
	 * @access public static
	 */
	public static function encode($domain)
	{
        $arr = explode('.', $domain);
        foreach ($arr as $k => $v) {
			if (strpos($v, '@') !== false) 
			{
				$last = substr(strrchr($v, "@"), 1);
				list($first) = explode('@', $v);
				$conv = self::_encode($first, 'utf8').'@'.self::_encode($last, 'utf8');
			} else {
				$conv = self::_encode($v, 'utf8');
			}
            if ($conv) $arr[$k] = $conv;
        }
        $domain = join('.', $arr);
		return $domain;
	}

	/**
	 * Decode
	 * @access public static
	 */
	public static function decode($punycode)
	{
		$punycode = explode('.', $punycode);
		foreach ($punycode as $k => $v)
		{
			if (strpos($v, '@') !== false) 
			{
				$last = substr(strrchr($v, "@"), 1);
				list($first) = explode('@', $v);
				$conv = self::_decode($first).'@'.self::_decode($last);
			} else {
				$conv = self::_decode($v);
			}
			$arr[$k] = ($conv) ? $conv : $v;
		}
		$domain = join('.', $arr);
		return $domain;
	}

	/**
	 * Decode a given ACE domain name
	 * @param    string   Domain name (ACE string)
	 * @return   string   Decoded Domain name (UTF-8 or UCS-4)
	 */
	protected static function _decode($encoded)
	{
		if (strpos($encoded, self::$_prefix) !== 0) {
			return $encoded;
		}

		$idx   = 0;
		$bias  = 72;
		$first = 700;
		$char  = 0x80;
		$decoded = array();

		$dpos = strrpos($encoded, '-');
		if ($dpos > 4) {
			for($k = 4; $k < $dpos; ++$k) {
				$decoded[] = ord($encoded[$k]);
			}
		}
		$decol = count($decoded);
		$encol = strlen($encoded);

		for ($enco_idx = $dpos ? $dpos + 1 : 0; $enco_idx < $encol; ++ $decol)
		{
			$old_idx = $idx;
			$w = 1;
			$k = 36;

			while(true)
			{
				$cp = ord($encoded[$enco_idx++]);
				$digit = $cp - 48 < 10 ? $cp - 22 : ($cp - 65 < 26 ? $cp - 65 : ($cp - 97 < 26 ? $cp - 97 : 36));
				$idx += $digit * $w;
				$t = $k <= $bias ? 1 : ($k >= $bias + 26 ? 26 : $k - $bias);
				if ($digit < $t) {
					break;
				}
				$w *= 36 - $t;
				$k += 36;
			}

			$delta = floor(($idx - $old_idx) / $first);
			$first = 2;
			$delta += floor($delta / ($decol + 1));

			for ($k = 0; $delta > 455; $k += 36) {
				$delta = floor($delta / 35);
			}
			$bias = floor($k + 36 * $delta / ($delta + 38));
			$char += floor($idx / ($decol + 1));
			$idx %= $decol + 1;
			if ($decol > 0) {
				for ($i = $decol; $i > $idx; $i--) {
					$decoded[$i] = $decoded[$i-1];
				}
			}
			$decoded[$idx ++] = $char;
		}

		$result = NULL;
		foreach ($decoded as $v)
		{
			if ($v < 128) {
				$result .= chr($v);
			} elseif ($v < (1<<11)) {
				$result .= chr(192 + ($v>>6)).chr(128 + ($v&63));
			} elseif ($v < (1<<16)) {
				$result .= chr(224 + ($v>>12)).chr(128 + ($v>>6&63)).chr(128 + ($v&63));
			} elseif ($v<(1<<21)) {
				$result .= chr(240 + ($v>>18)).chr(128 + ($v>>12&63)).chr(128 + ($v>>6&63)).chr(128 + ($v&63));
			} else {
				$result .= 0xFFFC;
			}
		}
		return $result;
	}

	/**
	 * Encode a given UTF-8 domain name
	 * @param    string   Domain name (UTF-8)
	 * @return   string   Encoded Domain name (ACE string)
	 */
	public static function _encode($decoded)
	{
		$values = $unicode = array();
		$n = strlen($decoded);

		for ($i = 0; $i < $n; $i ++)
		{
			$v = ord($decoded[$i]);
			if ($v < 128) {
				$unicode[] = $v;
			} else {
				if( ! $values) {
					$cc = $v < 224 ? 2 : 3;
				}
				$values[] = $v;
				if (count($values) == $cc)
				{
					$unicode[] = $cc == 3 ? $values[0] % 16 * 4096 + $values[1] % 64 * 64 + $values[2] % 64 : $values[0] % 32 * 64 + $values[1] % 64;
					$values = array();
				}
			}
		}
		unset($decoded, $values);

		$delta = $cc = 0;
		$n = 128;
		$bias = 72;
		$first = 700;
		$ex = $bs = '';
		$ucnt = count($unicode);

		foreach($unicode as $v) {
			if($v < 128) {
				$bs .= chr($v);
				$cc ++;
			}
		}

		while($cc < $ucnt)
		{
			$m = 100000;
			foreach ($unicode as $v) {
				if($v >= $n and $v <= $m) {
					$m = $v;
				}
			}
			$delta += ($m - $n) * ($cc + 1);
			$n = $m;

			foreach($unicode as $v)
			{
				if ($v < $n)
				{
					$delta ++;
				}
				elseif ($v == $n)
				{

					$q = $delta;
					$k = 36;

					while (true)
					{
						if ($k <= $bias + 1) {
							$t = 1;
						} elseif ($k >= $bias + 26) {
							$t = 26;
						} else {
							$t = $k - $bias;
						}
						if($q < $t) {
							break;
						}

						$ex .= self::encode_digit($t + ($q - $t) % (36 - $t));
						$q = floor(($q - $t) / (36 - $t));
						$k += 36;
					}
					$ex .= self::encode_digit($q);

					$delta = floor($delta / $first);
					$delta += floor($delta / ($cc + 1));
					$first = 2;
					$k = 0;
					while($delta > 455)
					{
						$delta = floor($delta / 35);
						$k += 36;
					}
					$bias = $k + floor(36 * $delta / ($delta + 38));

					$delta = 0;
					$cc ++;
				}
			}
			$delta ++;
			$n ++;
		}

		if ( ! empty($bs) and empty($ex)) return $bs;
		if ( ! empty($bs) and ! empty($ex)) return self::$_prefix.$bs.'-'.$ex;
		if (empty($bs) and ! empty($ex)) return self::$_prefix.$ex;
	}

    /**
     * Encoding a certain digit
     * @param    int $d
     * @return string
     */
    protected static function encode_digit($d)
    {
        return chr($d + 22 + 75 * ($d < 26));
    }

    /**
     * Decode a certain digit
     * @param    int $cp
     * @return int
     */
    protected static function decode_digit($cp)
    {
        $cp = ord($cp);
        return ($cp - 48 < 10) ? $cp - 22 : (($cp - 65 < 26) ? $cp - 65 : (($cp - 97 < 26) ? $cp - 97 : $this->_base));
    }

    /**
     * Gets the length of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * @param string $string the string for which to get the length.
     * @return integer the length of the string in bytes.
     */
    public static function byte($string)
    {
        if (self::$_overload) {
            return mb_strlen($string, '8bit');
        }
        return strlen((binary) $string);
    }
}
