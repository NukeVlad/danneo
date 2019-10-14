<?php
/**
 * File:        /core/classes/Debug.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Debug
 */
class Debug
{
	/**
	 * Выводить на экран или писать в лог
	 * @param	save | show
	 * @type string
	 */
	private $output = 'save';

	/**
	 * Количество строк для записи в файл
	 * @type integer
	 */
	private $coll = 50;

    /**
     * Отображать ошибки закрытые оператором @
     * @type boolean
     */
	private $locked = 'yes';

    /**
     * Папка с лог-файлами (путь от корня сайта)
     * @type string
     */
	public $dir = 'cache/log';

    /**
     * Имя лог-файла ошибок
     * @type string
     */
	public $file = 'debug.log';

    /**
     * Текущее время на сервере
     * @type string
     */
	public $_time;

	/**
	 * Class Debug _contructor
	 */
	public function __construct()
	{
		global $config;

		$this->_time = time();
		$this->error_handler();

		$this->output = empty($config['debug_code_out']) ? $this->output : $config['debug_code_out'];
		$this->locked = empty($config['debug_code_loc']) ? $this->locked : $config['debug_code_loc'];
		$this->coll   = empty($config['debug_code_col']) ? $this->coll   : $config['debug_code_col'];

	}

	/**
	 * Обработчик ошибок
	 * @return boolean
	 */
	public function debug_log($errno, $errstr, $errfile, $errline)
	{
		global $config;

		$this->locked = ($config['debug_code_loc'] == 'yes') ? TRUE : FALSE;
		$operator = ($this->locked == FALSE) ? (error_reporting() & $errno) : TRUE;
		if ($operator)
		{
			$errtype = array
			(
				E_ERROR             => 'Error',
				E_CORE_ERROR        => 'Core error',
				E_COMPILE_ERROR     => 'Compile error',
				E_USER_ERROR        => 'User error',
				E_RECOVERABLE_ERROR => 'Recoverable error',
				E_WARNING           => 'Warning',
				E_CORE_WARNING      => 'Core warning',
				E_COMPILE_WARNING   => 'Compile warning',
				E_USER_WARNING      => 'User warning',
				E_NOTICE            => 'Notice',
				E_USER_NOTICE       => 'User notice',
				E_DEPRECATED        => 'Deprecated',
				E_USER_DEPRECATED   => 'User_deprecated',
				E_PARSE             => 'Parsing error',
				E_STRICT            => 'Strict'
			);

			// Тип ошибки
			if (array_key_exists($errno, $errtype)) {
				$errname = $errtype[$errno];
			} else {
				$errname = 'Unknown error';
			}

			// Писать в Лог-файл OR Выводить на Экран
			if ($this->output == 'save') {
				$messages = $this->_time.'|'.$errname.' '.$errno.'|'.$errstr.'|'.$errfile.'|'.$errline.PHP_EOL;
				$this->log_write($messages);
			} else {
				ob_start();
				echo '<div class="error-box">'.$errname.': ['.$errno.'] '.$errstr.' '.$errfile.' on line '.$errline.'</div>';
				ob_end_flush();
			}
		}
		return TRUE;
	}

	/**
	 * Set Error handler
	 * Установить в пользовательский обработчик ошибок
	 */
	protected function error_handler()
	{
		set_error_handler(array($this, 'debug_log'));
	}

	/**
	 * Writes each of the messages into the log file
	 * @param   string   $messages
	 * @return  void
	 */
	protected function log_write($messages)
	{
		// Set the yearly directory name
		$directory = DNDIR.$this->dir;

		if ( ! is_dir($directory))
		{
			// Create the yearly directory
			mkdir($directory, 02777);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 02777);
		}

		// Set the name of the log file
		$filename = DNDIR.$this->dir.DIRECTORY_SEPARATOR.$this->file;

		if ( ! file_exists($filename))
		{
			// Create the log file
			file_put_contents($filename, NULL);

			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		// Limit amount of the lines
		$lines = explode(PHP_EOL, file_get_contents($filename));
		if (count($lines) > $this->coll)
		{
			file_put_contents($filename, implode(PHP_EOL, array_slice($lines, - $this->coll)), LOCK_EX);
		}

		// Write each message into the log file
		file_put_contents($filename, $messages, FILE_APPEND | LOCK_EX);
	}
}
