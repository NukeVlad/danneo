<?php
/**
 * File:        /core/classes/DB.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class DB
 */
class DB
{
	public $connid = 0;
	public $result;
	public $message;
	public $sqlcount = 0;
	public $filecount = 0;
	public $totaltime = 0;
	public $works = 0;
	public $explain = '';
	public $save = '';
	public $cache = array('fields' => array(), 'data' => array());
	public $file = '';
	public $initcache = TRUE;
	public $incache = '';

	public function __construct($server, $user, $password, $database, $charsebd)
	{
		global $config;

		$this->server   = $server;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;
		$start          = $this->dbtime();

		if ($this->connid == 0)
		{
			if (empty($password)) {
				$this->connid = @mysqli_connect($server, $user);
			} else {
				$this->connid = @mysqli_connect($server, $user, $password);
			}

			if (function_exists('mysqli_set_charset')) {
				@mysqli_set_charset($this->connid,$charsebd);
			}

			@mysqli_query($this->connid, 'SET NAMES '.$charsebd);

			if ($this->connid)
			{
				if ($database) {
					if (mysqli_select_db($this->connid,$database)) {
						$this->totaltime += sprintf('%.5f', $this->dbtime() - $start);
					} else {
						$this->message = 'Not selected DB!';
						$this->error();
					}
				} else {
					$this->message = 'Not selected DB!';
					$this->error();
				}
			}
			else
			{
				$this->message = 'Not connect server!';
				$this->error();
			}
		}
	}

	private function dbtime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	private function backtick($query)
	{
		global $config;

		if ( ! empty($config['list_field']))
		{
			$query = preg_replace_callback(
				"/\'(.*?)\'/s",
				function (array $matches) {
					return str_replace("`", "", "'".$matches[1]."'");
				},
				preg_replace("/(?<!\')\b(".$config['list_field'][0].")\b(?!\')/", "`$1`", str_replace("`", "", $query))
			);
		}
		return $query;
	}

	public function escape($query)
	{
		return mysqli_real_escape_string($this->connid, $query);
	}

	public function cachewrite($cacheresult)
	{
		$cachefile = fopen($this->file, 'w+');
		if ($cachefile) {
			fwrite($cachefile, $cacheresult);
			fclose($cachefile);
		}
	}

	public function query($query = '', $time = FALSE, $in = '', $tick = TRUE)
	{
		global $config;

		$config['debug_db'] = (isset($config['debug_db'])) ? $config['debug_db'] : 'no';
		$this->result = $write = '';
		$nodb = 1;
		$start = $this->dbtime();

		if ($query)
		{
			if ( ! is_null($this->backtick($query)) AND $tick)
				$query = $this->backtick($query);

			if ($time == FALSE OR $config['cache'] == 0)
			{
				$this->result = mysqli_query($this->connid, $query);
			}
			else
			{
				$this->file = DNDIR.'cache/sql/'.((empty($in)) ? '' : $in.'/').md5($query).'.txt';
				$this->initcache = TRUE;
				if (file_exists($this->file) AND filemtime($this->file) > (time() - $time))
				{
					$file = file_get_contents($this->file);
					$this->result = unserialize($file);
					$nodb = 0;
					if (empty($this->result))
					{
						$this->initcache = FALSE;
						$nodb = 1;
						$this->result = mysqli_query($this->connid, $query);
					}
				}
				else
				{
					$this->result = mysqli_query($this->connid, $query);
					$resurse = array();
					while ($insert = $this->fetchrow($this->result, 0)) {
						array_push($resurse, $insert);
					}
					$write = serialize($resurse);
					$this->cachewrite($write);
					$this->result = $resurse;
				}
			}
		}

		if ($this->result)
		{
			if ($config['debug_db'] == 'yes')
			{
				$this->totaltime += sprintf('%.5f',$this->dbtime() - $start);
				$this->explain .= ($nodb == 1) ? '<li><p>QUERY: <i>'.$query.'</i><br>TIME: <u>'.sprintf('%.5f',$this->dbtime() - $start).'</u></p></li>' : '<li><p>READ: <b>file -></b> <i>'.md5($query).'.txt</i><br>TIME: <u>'.sprintf('%.5f',$this->dbtime() - $start).'</u></p></li>';
			}

			if ($nodb == 1) {
				++$this->sqlcount;
			} else {
				++$this->filecount;
			}

			return $this->result;
		}
		else
		{
			if($config['debug_db'] == 'yes' AND $nodb == 0) {
				$this->message = $query;
				$this->error();
			} else {
				FALSE;
			}
		}
	}

	/**
	 * Функция возвращает количество рядов результата запроса
	 */
	public function numrows($query = 0, $cache = FALSE)
	{
		if ($cache AND $this->initcache == TRUE) {
			return sizeof($this->result);
		} else {
			return mysqli_num_rows($query);
		}
	}

	/**
	 * Функция обрабатывает ряды результата запроса,
	 * возвращая ассоциативный массив, численный массив или оба
	 */
	public function fetchrow($query = 0, $cache = FALSE)
	{
		if ($cache AND $this->initcache == TRUE)
		{
			return array_shift($this->result);
		}
		else
		{
			return mysqli_fetch_array($query);
		}
	}

	/**
	 * Возвращая ассоциативный массив
	 */
	public function fetchassoc($query = 0, $cache = FALSE)
	{
		if ($cache AND $this->initcache == TRUE)
		{
			return array_shift($this->result);
		}
		else
		{
			return mysqli_fetch_assoc($query);
		}
	}

	/**
	 * Освобождает память от результата запроса
	 */
	public function freerow($query = 0)
	{
		return mysqli_free_result($query);
	}

	/**
	 * Функция возвращает количество полей результата запроса
	 */
	public function numfields($query = 0)
	{
		return mysqli_num_fields($query);
	}

	/**
	 * Функция возвращает название колонки с указанным индексом
	 */
	public function fieldname($offset, $query = 0)
	{
		mysqli_field_seek($query, $offset);
		$field = mysqli_fetch_field($query);
		return $field->name;
	}

	/**
	 * Функция возвращает ID, сгенерированный колонкой с AUTO_INCREMENT последним запросом INSERT к серверу
	 */
	public function insertid()
	{
		return mysqli_insert_id($this->connid);
	}

	/**
	 * Функция сбрасывает AUTO_INCREMENT до указанного ID
	 */
	public function increment($name, $num = 1)
	{
		global $basepref;

		return mysqli_query($this->connid, "ALTER TABLE ".$basepref."_".$name." AUTO_INCREMENT = ".$num);
	}

	/**
	 * Функция возвращает количество рядов, задействованных в последнем запросе INSERT, UPDATE или DELETE
	 */
	public function affrows()
	{
		return mysqli_affected_rows($this->connid);
	}

	/**
	 * Функция возвращает текст сообщения об ошибке предыдущей операции с MySQL
	 */
	private function get_error()
	{
		return $this->error = @mysqli_error($this->connid);
	}

	/**
	 * Функция Возвращает численный код ошибки выполнения последней операции с MySQL
	 */
	private function get_errno()
	{
		return $this->errno = @mysqli_errno($this->connid);
	}

	/**
	 * Проверить наличие таблицы
	 * @type integer | 1 есть, 0 нет
	 */
	public function tables($name)
	{
		global $basepref;
		return mysqli_num_rows(
						mysqli_query($this->connid, "SHOW TABLES LIKE '".$basepref."_".$name."'")
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
						mysqli_query($this->connid, "SHOW COLUMNS FROM ".$basepref."_".$name." WHERE field = '".$col."'")
					);
		return $result;
	}

	/**
	 * Функция выводит HTML-визуализацию ошибки при операциях с MySQL
	 */
	private function error()
	{
		global $config;

		$langcharset = (isset($config['langcharset'])) ? $config['langcharset'] : 'utf-8';
		echo '	<html>
				<head>
				<meta http-equiv="Content-type" content="text/html; charset='.$langcharset.'">
				<title>MySQL Debugging - Danneo CMS '.@$config['version'].'</title>
				<style>
				.debug    { font: 13px/1.3 Arial, Sans-serif; background-color: #ffe; color: #000; width: 96%; margin: 1% auto; padding: 15px 15px 5px; border: 1px solid #f90; }
				.debug h3 { display: block; color: #000; font-size: 13px; font-weight: bold; margin: 0 0 15px; padding: 0 1px 5px; border-bottom: 1px solid #ddd; }
				.debug dl { margin-left: 10px; }
				.debug dt { color: #b90000; margin-top: 10px; }
				.debug dd { margin: 0; }
				</style>
				</head>
				<body>
					<div class="debug">
						<h3>MySQL Debugging - Danneo CMS '.@$config['version'].'</h3>
						<dl>
							<dt>SQL.q</dt>
								<dd>'.$this->message.'</dd>
							<dt>MySQL.e</dt>
								<dd>'.convert_cyr_string($this->get_error(),"k","w").'</dd>
							<dt>MySQL.e.№</dt>
								<dd>'.$this->get_errno().'</dd>
							<dt>PHP.v</dt>
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
		exit();
	}

	public function close()
	{
		if ($this->connid)
		{
			if ($this->result){
				mysqli_free_result($this->result);
			}
			return mysqli_close($this->connid);
		}
	}
}
