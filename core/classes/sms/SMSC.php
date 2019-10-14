<?php
/**
 * File:        /core/classes/sms/SMSC.php
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
 * Класс SMSC
 * СМС-центр, сервис для отправки SMS-сообщений в сети мобильных операторов
 * Сайт: http://smsc.ru
 */
class SMSC
{
    /**
     * Запрос баланса
     * @param 3 – ответа сервера в json формате.
     */
    const FMT = 3;

    /**
     * Добавление в ответ сервера названия валюты Клиента
     * @param 0 или 1
     */
    const CUR = 1;

    /**
     * Получить стоимость рассылки
     * @param 1 – стоимость без реальной отправки
     */
    const COST = 1;

    /**
     * Кодировка сообщения
     * @param utf-8, koi8-r, windows-1251.
     */
    const CHARSET = 'utf-8';

    private $login;
    private $password;
    private $options = array();
    private $types = array('', 'flash=1', 'push=1', 'hlr=1', 'bin=1', 'bin=2', 'ping=1');

    /**
     * Init
     *
     * @param string $login логин
     * @param string $password пароль
     *
     * @return \SMSC
     */
    public function __construct()
	{
		global $tm, $lang, $config;

        if ( ! function_exists ('curl_init'))
        {
			$tm->errorbox($lang['not_curl']);
        }

        $this->login = $config['smsc_login'];
        $this->password = md5($config['smsc_password']);

        $this->options = array
			(
				'charset' => self::CHARSET
			);
    }

    /**
     * Отправка сообщения
     *
     * @access public
     *
     * @param string|array $phones  номера телефонов
     * @param string       $message текст сообщения
     * @param string       $sender  имя отправителя
     * @param array        $options дополнительные параметры
     *
     * @return false если список телефонов пуст или длина сообщения больше 70 символов
     * @return bool|string|\stdClass результат выполнения запроса в виде строки, объекта (JSON) или false в случае ошибки.
     */
    public function send($phones, $message, $sender = null, $options = array())
	{
        $options = array_merge($this->options, $options);

        if (empty($phones))
			return false;

		if(strpos($phones, ';') !== false) {
			$phones = explode(';', trim($phones, ';'));
		} elseif (strpos($phones, ',') !== false) {
			$phones = explode(',', trim($phones, ','));
		} else {
			$phones = $phones;
		}

		if (is_array($phones)) {
			$phones = array_map(__CLASS__.'::clear', $phones);
			$phones = implode(',', $phones);
		} else {
			$phones = self::clear($phones);
		}

        if ($message !== null AND empty($message)) {
            return false;
        } elseif (mb_strlen($message, 'UTF-8') > 800) {
            return false;
        }

        $options['phones'] = $phones;
        $options['mes']    = $message;

        if ($sender !== null) {
            $options['sender'] = $sender;
        }

        $output = $this->request('send', $options);

		if (strpos($output, 'OK') === 0)
		{
			$params = explode(', ', $output);
			$id = explode(' - ', $params[1]);
			$output = $id[1];
		}

		return $output;
    }

    /**
     * Запрос баланса
     *
     * @access public
     * @param формат ответа сервера в JSON
     * @return string баланс в виде строки или false в случае ошибки.
     */
    public function balance()
	{
		$balance = $this->request
			(
				'balance',
				array('fmt' => self::FMT, 'cur' => self::CUR)
			);
		$res = Json::decode($balance);
		if (isset($res['balance']))
		{
			return $res['balance'].' '.$res['currency'];
		}
		return false;
	}

    /**
     * Стоимость SMS
     *
     * @access public
     *
     * @param string|array $phones  номера телефонов
     * @param string       $message текст сообщения
     *
     * @return bool|string|\stdClass стоимость рассылки в виде строки, объекта JSON или FALSE в случае ошибки.
     */
    public function cost($phones, $message)
	{
        $cost = $this->request('send', array
			(
				'phones' => self::clear($phones),
				'mes'    => $message,
				'fmt'    => self::FMT,
				'cost'   => 1
			)
		);
		$res = Json::decode($cost);
		$res = (isset($res['cost'])) ? $res['cost'] : false;
		return $res;
    }

    /**
     * Валидация Логин / Пароль
     *
     * @return type
     */
    public function check()
	{
		$rand = implode('', array_rand(array(9,2,3,4,5,6,7,1), 7));

        $check = $this->request('send', array
			(
				'phones' => '7928'.$rand,
				'mes'  => 'Message',
				'fmt'  => self::FMT,
				'cost' => 1,
			)
		);
		$res = Json::decode($check);
		$res = (isset($res['error'])) ? false : true;
		return $res;
    }

    /**
     * Получение статуса сообщения
     *
     * @param $phone номер телефона
     * @param $id    идентификатор сообщения
     * @return статус сообщения true или false
     */
    public function status($phone, $id)
	{
        $status = $this->request('status', array
			(
				'phone' => self::clear($phone),
				'fmt'   => self::FMT,
				'id'    => (int)$id,
				'all'   => 0,
			)
		);
		$res = Json::decode($status);
		if (isset($res['status']) AND $res['status'] == 1)
		{
			return true;
		}
		return false;
    }

    /**
     * Запрос
     *
     * @param string $file
     * @param array  $options
     * @return array ответ сервера
     */
    private function request($file, array $options)
	{
        $options = array_merge($this->options, $options);

		$params = array(
            'login='.urlencode($this->login),
            'psw='.urlencode($this->password)
        );

        foreach ($options as $key => $value)
		{
            switch ($key) {
                case 'type':
                    if ($value > 0 AND $value < count($this->types)) {
                        $params[] = $this->types[$value];
                    }
                    break;
                default:
                    if (!empty($value)) {
                        $params[] = $key . '=' . urlencode($value);
                    }
            }
        }

		$url = 'https://smsc.ru/sys/'.$file.'.php';
        $data = implode('&', $params);

		$i = 0;
		do {
			if ($i) {
				sleep(2);
				if ($i == 2) {
					$url = str_replace('://smsc.ru/', '://www2.smsc.ru/', $url);
				}
			}
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($curl);
		}
		while($output == '' AND ++ $i < 3);
		curl_close($curl);

        return $output;
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
