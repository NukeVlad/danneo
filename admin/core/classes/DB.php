<?php
/**
 * File:        /admin/core/classes/DB.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class DB
 */
class DB
{
	/**
	 * @access private
	 * @type boolean
	 */
	private $conid = null;
	private $queryres = null;
	private $acookname = null;

	/**
	 * @access public
	 * @type integer
	 */
	public $totaltime = 0;
	public $sqlcount = 0;

	/**
	 * @access public
	 * @type string
	 */
	public $message = '';
	public $explain = '';

	/**
	 * Функция соединения с MySQL сервером и базой данных
	 */
	public function __construct($server, $user, $password, $database, $charsebd)
	{
		global $basepref;

		$start = $this->dbtime();

		if ($this->conid == 0)
		{
			if (empty($password))
			{
				$this->conid = @mysqli_connect($server, $user);
			}
			else
			{
				$this->conid = @mysqli_connect($server, $user, $password);
			}

			if (function_exists('mysqli_set_charset'))
			{
				@mysqli_set_charset($this->conid, $charsebd);
			}
			@mysqli_query($this->conid, 'SET NAMES '.$charsebd);

			if ($this->conid)
			{
				if ($database)
				{
					if (mysqli_select_db($this->conid, $database))
					{
						$this->totaltime += sprintf('%.5f', $this->dbtime() - $start);
						$this->acookname = mysqli_fetch_assoc(
													mysqli_query($this->conid, "SELECT setval FROM ".$basepref."_settings WHERE setname = 'acookname'")
												);
					}
					else
					{
						$this->message = 'Not selected DB!';
						$this->error();
					}
				}
			}
			else
			{
				$this->message = 'Not connect server!';
				$this->error();
			}
		}
	}

	/**
	 * Функция для создания временных меток
	 */
	private function dbtime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	private function replace($string, $field)
	{
		$string = preg_replace_callback(
			"/\'(.*?)\'/s",
			function (array $matches) {
				return str_replace("`", "", "'".$matches[1]."'");
			},
			preg_replace("/(?<!\')\b(".$field.")\b(?!\')/", "`$1`", str_replace("`", "", $string))
		);
		return $string;
	}

	/**
	 * Функция экранирования имен полей
	 */
	private function backtick($query)
	{
		global $config;

		if ( ! empty($query))
		{
			if (isset($_COOKIE['dn_platform'.$this->acookname['setval']]))
			{
				list($pid) = unserialize($_COOKIE['dn_platform'.$this->acookname['setval']]);
				if (intval($pid) > 0 AND isset($config['list_field'][$pid])) {
					if ( ! empty($config['list_field'][$pid]))
						$query = $this->replace($query, $config['list_field'][$pid]);
				} else {
					if ( ! empty($config['list_field'][0]))
						$query = $this->replace($query, $config['list_field'][0]);
				}
			}
			else
			{
				if ( ! empty($config['list_field'][0]))
					$query = $this->replace($query, $config['list_field'][0]);
			}
		}
		return $query;
	}

	/**
	 * Функция экранирования данных
	 */
	public function escape($query)
	{
		return mysqli_real_escape_string($this->conid, $query);
	}

	/**
	 * Функция выполнения MySQL запросов
	 */
	public function query($query = '', $tick = TRUE)
	{
		global $conf;

		$this->queryres = '';
		$start = $this->dbtime();

		if ( ! empty($query))
		{
			if ( ! is_null($this->backtick($query)) AND $tick)
				$query = $this->backtick($query);

			$this->queryres = mysqli_query($this->conid, $query);
		}

		if ($this->queryres)
		{
			$this->totaltime += sprintf('%.5f', $this->dbtime() - $start);
			$this->explain.= '<li>EXPLAIN ' . $query . ' ' . sprintf('%.5f', $this->dbtime() - $start) . '</li>';
			++$this->sqlcount;

			return $this->queryres;
		}
		else
		{
			$this->message = $query;
			$this->error();
		}
	}

	public function select($database = '', $display = 1)
	{
		$this->works = 0;

		if ( ! empty($database))
			$this->database = $database;

		if ( ! mysqli_select_db($this->conid, $database))
		{
			if ($display == 1)
			{
				$this->message = 'Not connect server!';
				$this->error();
			}
		}
		else
		{
			$this->works = 1;
		}
	}

	/**
	 * Функция возвращает количество рядов результата запроса
	 */
	public function numrows($qid = 0)
	{
		return mysqli_num_rows($qid);
	}

	/**
	 * Функция обрабатывает ряды результата запроса, возвращая ассоциативный массив, численный массив или оба
	 */
	public function fetchrow($qid = 0)
	{
		return mysqli_fetch_array($qid);
	}

	/**
	 * Возвращая ассоциативный массив
	 */
	public function fetchassoc($query = 0)
	{
		return mysqli_fetch_assoc($query);
	}

	/**
	 * Функция возвращает количество полей результата запроса
	 */
	public function numfields($qid = 0)
	{
		return mysqli_num_fields($qid);
	}

	/**
	 * Функция возвращает название колонки с указанным индексом
	 */
	public function fieldname($offset,$qid = 0)
	{
		return mysqli_field_name($qid,$offset);
	}

	/**
	 * Функция возвращает версию БД
	 */
	public function serverinfo()
	{
		return mysqli_get_server_info($this->conid);
	}

	/**
	 * Функция возвращает ID, сгенерированный колонкой с AUTO_INCREMENT последним запросом INSERT к серверу
	 */
	public function insertid()
	{
		return mysqli_insert_id($this->conid);
	}

	/**
	 * Функция сбрасывает AUTO_INCREMENT до указанного ID
	 */
	public function increment($name, $num = 1)
	{
		global $basepref;

		return mysqli_query($this->conid, "ALTER TABLE ".$basepref."_".$name." AUTO_INCREMENT = ".$num);
	}

	/**
	 * Функция возвращает количество рядов, задействованных в последнем запросе INSERT, UPDATE или DELETE
	 */
	public function affrows()
	{
		return mysqli_affected_rows($this->conid);
	}

	/**
	 * Функция возвращает текст сообщения об ошибке предыдущей операции с MySQL
	 */
	public function get_error()
	{
		$this->error = @mysqli_error($this->conid);

		return $this->error;
	}

	/**
	 * Функция Возвращает численный код ошибки выполнения последней операции с MySQL
	 */
	public function get_errno()
	{
		$this->errno = @mysqli_errno($this->conid);

		return $this->errno;
	}

	public function field($name)
	{
		$field = array();
		$res = mysqli_query($this->conid, "SHOW FIELDS FROM ".$name);
		while ($row = mysqli_fetch_array($res)) {
			$field[] = $row['Field'];
		}
		return $field;
	}

	/**
	 * Проверить наличие таблицы
	 * @type integer | 1 есть, 0 нет
	 */
	public function tables($name)
	{
		global $basepref;
		return mysqli_num_rows(
						mysqli_query($this->conid, "SHOW TABLES LIKE '".$basepref."_".$name."'")
					);
	}

	/**
	 * Проверить наличие колонки (поля)
	 * @type integer | 1 есть, 0 нет
	 */
	public function columns($name, $col)
	{
		global $basepref;
		$result = mysqli_num_rows(
						mysqli_query($this->conid, "SHOW COLUMNS FROM ".$basepref."_".$name." WHERE field = '".$col."'")
					);
		return $result;
	}

	/**
	 * Функция закрывает соединение с сервером MySQL
	 */
	public function close()
	{
		if ($this->conid)
		{
			if ($this->queryres)
			{
				mysqli_free_result($this->queryres);
			}
			$result = mysqli_close($this->conid);

			return $result;
		}
	}

	/**
	 * Функция выводит HTML-визуализацию ошибки при операциях с MySQL
	 */
	public function error()
	{
		$langcharset = (defined('CHAR_DEF')) ? CHAR_DEF : 'utf-8';

		echo '	<html>
				<head>
				<meta http-equiv="Content-type" content="text/html; charset='.$langcharset.'">
				<title>MySQL Debugging - Danneo CMS '.VERSION.'</title>
				<style>
					.debug    { font: 12px/1.3 Courier New, Sans-serif; background-color: #ffe; color: #333; width: 96%; margin: 1% auto; padding: 15px; border: 2px solid #f60; }
					.debug h3 { font: bold 12px/1.4 Verdana, Sans-serif; display: block; color: #d00; margin: 0 0 10px; padding: 0 0 5px; border-bottom: 1px dotted #f60; }
					.debug dl { margin-left: 10px; }
					.debug dt { font: bold 12px/1.3 Arial, Sans-serif; color: #d00; margin-top: 7px; }
					.debug dd { margin: 0; }
				</style>
				</head>
				<body>
				<div class="debug">
					MySQL Debugging - Danneo CMS '.VERSION.'
					<dl>
					<dt>SQL.q</dt>
						<dd>'.$this->message.'</dd>
					<dt>MySQL.e</dt>
						<dd>'.convert_cyr_string($this->get_error(),"k","w").'</dd>
					<dt>MySQL.e.№</dt>
						<dd>'.$this->get_errno().'</dd>
					<dt>PHP</dt>
						<dd>'.phpversion().'</dd>
					<dt>Data</dt>
						<dd>'.date("d.m.Y H:i").'</dd>
					<dt>Script</dt>
						<dd>'.getenv("REQUEST_URI").'</dd>
					<dt>Refer</dt>
						<dd>'.getenv("HTTP_REFERER").'</dd>
					</dl>
				</div>
				</body>
				</html>';

		error_reporting(0);
	}
}
