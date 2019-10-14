<?php
/**
 * File:        /admin/core/classes/sms/SMSRU.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace DN\Sms;
use \Json;

/**
 * Класс SMSRU
 * СМС сервис для отправки SMS-сообщений в сети мобильных операторов
 * Сайт: http://sms.ru
 */
class SMSRU
{
    public $token;
    public $default = array();
    public $sha512;

    /**
     * Init
     */
    public function __construct()
	{
		global $tm, $lang, $conf;

        if ( ! function_exists ('curl_init'))
        {
			$tm->errorbox($lang['not_curl']);
        }

        $this->get_token();

		if (isset($conf['smsru_auth']) AND $conf['smsru_auth'] == 'api_id')
		{
			$this->default = array(
				'api_id' => $conf['smsru_api_id']
			);
		}
		else
		{
			$login = isset($conf['smsru_login']) ? $conf['smsru_login'] : '';
			$password = isset($conf['smsru_password']) ? hash('sha512', $conf['smsru_password'].$this->token) : '';

			$this->default = array
				(
					'login'  => $login,
					'token'  => $this->token,
					'sha512' => $password
				);
		}
    }

    /**
     * Send message
     *
     * @param string $phone
     * @param string $message
     * @param string $from
     * @param integer $time
     * @param boolean $test
     * @param type $partner_id
     * @return ID SMS or false
     */
    public function send($phones, $message, $from = null, $time = null, $test = false, $partner_id = null)
    {
		if ($this->check() == '100')
		{
			if (empty($phones))
				return false;

			if(strpos($phones, ';') !== false) {
				$phones = explode(';', trim($phones, ';'));
			} elseif (strpos($phones, ',') !== false) {
				$phones = explode(',', trim($phones, ','));
			}

			if (is_array($phones)) {
				$phones = array_map(__CLASS__.'::clear', $phones);
				$phones = implode(',', $phones);
			} else {
				$phones = self::clear($phones);
			}

			$data = $this->default;
			$data['to'] = $phones;
			$data['text'] = $message;

			if ($from)
				$data['from'] = $from;

			if ($time && $time < (time() + 7 * 60 * 60 * 24))
				$data['time'] = $time;

			if ($test)
				$data['test'] = 1;

			if ($partner_id)
				$data['partner_id'] = $partner_id;

			$res = $this->request('http://sms.ru/sms/send?', $data);
			$res = explode("\n", $res);

			if ($res[0] == '100')
			{
				return $res[1];
			}
			return false;
		}
		return false;
    }

    /**
     * Request
     *
     * @return array
     */
    protected function request($url, $data = array())
    {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => http_build_query($data)
        );
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    /**
     * Check message status
     *
     * @param type ID SMS
     * @return true or false
     */
    public function status($id)
    {
		if ($this->check() == '100')
		{
			$data = $this->default;
			$data['id'] = $id;
			$res = $this->request('http://sms.ru/sms/status?', $data);
			return $res == '103' ? true : false;
		}
		return false;
    }

    /**
     * Check user balance
     * @return string num or false
     */
    public function balance()
    {
		if ($this->check() == '100')
		{
			$res = $this->request('http://sms.ru/my/balance?', $this->default);
			$res = explode("\n", $res);

			if ($res[0] == '100')
			{
				return 'Balance: '.$res[1];
			}
			return false;
		}
		return false;
    }

    /**
     * Check day limit
     *
     * @return string Daily limit or false
     */
    public function limit()
    {
		if ($this->check() == '100')
		{
			$res = $this->request('http://sms.ru/my/limit?', $this->default);
			$res = explode("\n", $res);

			if ($res[0] == '100')
			{
				return 'Daily limit: '.$res[1].' SMS';
			}
			return false;
		}
		return false;
    }

    /**
     * Get message cost
     *
     * @param type $phone
     * @param type $message
     * @return string price sms or false
     */
    public function cost($phone, $message)
    {
		if ($this->check() == '100')
		{
			$data = $this->default;
			$data['to'] = $phone;
			$data['text'] = $message;

			$res = $this->request('http://sms.ru/sms/cost?', $data);
			$res = explode("\n", $res);

			if ($res[0] == '100')
			{
				return 'Price SMS: '.$res[1];
			}
			return false;
		}
		return false;
    }

    /**
     * Check user auth
     *
     * @return code
     */
    public function check()
    {
        return $this->request('http://sms.ru/sms/auth/check?', $this->default);
    }

    /**
     * Get token
     *
     * @return code
     */
    protected function get_token()
    {
        $this->token = $this->request('http://sms.ru/auth/get_token');
    }

    /**
     * Очистка номера телефона
     *
     * @param string $phone
     * @return string номер телефона (только цифры)
     */
    public static function clear($phone)
	{
        return preg_replace('~[^\d+]~', '', $phone);
    }
}
