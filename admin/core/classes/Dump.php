<?php
/**
 * File:        /admin/core/classes/Dump.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Dump
*/
class Dump
{
    public $var = 0;
    public $message = array();

    public function __construct()
	{
        $this->var = 0;
        $this->message = array();
	}

	/**
	 * Функция возвращает структуру таблиц
	 */
	public function structure($name, $drop = 0)
	{
		global $db;

		$ins = null;
		$field = $keys = array();

		$res = $db->query("SHOW FIELDS FROM ".$name);
		$code = $db->fetchrow($db->query("SELECT CHARSET('".$name."')"));

		$ins.= ($drop) ? "DROP TABLE IF EXISTS `".$name."`;\n" : "";
		$ins.= "CREATE TABLE IF NOT EXISTS `".$name."` (\n";
		while ($row = $db->fetchrow($res))
		{
			$null = ($row['Null'] == 'NO') ? " NOT NULL" : " NULL";
			$def = (mb_strlen($row['Default']) > 0 AND $row['Default'] != 'NULL') ? " DEFAULT '".$row['Default']."'" : "";
			$extra = (empty($row['Extra'])) ? "" : " ".$row['Extra'];
			$field[] = "  `".$row['Field']."` ".$row['Type'].$null.$def.$extra;
		}
		$ins.= implode(",\n",$field);

		$res = $db->query("SHOW KEYS FROM ".$name);
		while ($row = $db->fetchrow($res))
		{
			if ($row['Non_unique'] == 0 AND $row['Key_name'] == 'PRIMARY') {
				$keys[] = "  PRIMARY KEY (`".$row['Column_name']."`)";
			} else {
				$keys[] = "  KEY `".$row['Key_name']."` (`".$row['Column_name']."`)";
			}
		}
		if (!empty($keys))
		{
			$ins.= ",\n".implode(",\n",$keys);
		}
		$ins.= "\n) ENGINE=MyISAM;\n\n";

		return $ins;
	}

	/**
	 * Функция возвращает данные таблиц
	 */
	public function datatable($name, $del = false)
	{
		global $db;

		$ins = null;
		$inq = $db->query("SELECT * FROM `".$name."`");
		if ($db->numrows($inq) > 0)
		{
			$ins.= ($del) ? 'TRUNCATE TABLE `'.$name.'`;'."\n" : '';
			$r = $n = $db->field($name);
			foreach($r as $v) {
				$f .= '`'.$v.'`,';
			}
			$ins.= 'INSERT INTO `'.$name.'` ('.rtrim($f, ',').') VALUES '."\n";
			$in = array();
			$inq = $db->query('SELECT * FROM '.$name);
			while ($item = $db->fetchrow($inq))
			{
				$t = array();
				foreach($r as $k => $v)
				{
					$t[] = "'".$db->escape($item[$n[$k]])."'";
				}
				$in[] = '('.implode(',', $t).')';
			}
			$ins.= implode(",\n", $in);
			$ins.= ';'."\n";
		}
		return $ins;
	}

	/**
	 * Функция восстанавливает базу из резервной копии
	 */
    public function import($patch, $ext)
	{
		global $db;

        if ($this->var == 0)
		{
			try
			{
				if (is_file($patch) AND is_readable($patch))
				{
					if ($ext == 'gz')
					{
						$zp = gzopen($patch, "r");
						$gzread = null;
						while ( ! gzeof($zp)) {
							$gzread.= gzgets($zp, 4096);
						}
						gzclose($zp);
						$temp = str_replace('.gz', '', $patch);
						file_put_contents($temp, $gzread, FILE_APPEND | LOCK_EX);

						$sql = file_get_contents($temp);
					}
					elseif ($ext == 'zip')
					{
						$zip = new ZipArchive;
						if ($zip->open($patch) === true)
						{
							$dir = str_replace(basename($patch), '', $patch);
							$zip->extractTo($dir);
							$zip->close();
							$temp = str_replace('.zip', '.sql', $patch);
							$sql = file_get_contents($temp);
						}
						else
						{
							$this->error("Reserve error: is not a readable file");
						}
					}
					elseif ($ext == 'sql')
					{
						$sql = file_get_contents($patch);
					}
					else
					{
						throw new Exception('incorrect file format');
					}

                    $sql = str_replace("\r", "\n", $sql);
                    $lines = preg_split("/\n/", $sql);

					$query = '';
					foreach ($lines as $line)
					{
						if (substr($line, 0, 2) == '--' || $line == '')
						{
							continue;
						}
						$query .= $line;
						if (substr(trim($line), -1, 1) == ';')
						{
							$db->query($query, 0);
							$query = '';
						}
					}

					if ($ext == 'gz' OR $ext == 'zip')
					{
						unlink($temp);
					}
				}
				else
				{
					throw new Exception('is not a readable file');
				}
			}
			catch(Exception $e)
			{
				$this->error("Restore error: ".$e->getMessage());
			}
			return false;
        }
    }

	/**
	 * Архив сайта в формате zip
	 */
	public function dumpsite($source, $dump, $skip)
	{
		if (extension_loaded('zip') === true)
		{
			$zip = new ZipArchive();
			if ($zip->open($dump, ZIPARCHIVE::CREATE) === true)
			{
				foreach ($source as $path)
				{
					if (is_dir($path) === true)
					{
						$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
						$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
						foreach ($files as $file)
						{
							if(!preg_match("%(".implode('|', $skip).")%is", $file))
							{
								if (is_dir($file) === true)
								{
									$zip->addEmptyDir(str_replace(ROOTDIR, '', $file));
								}
								else if (is_file($file) === true)
								{
									$zip->addFromString(str_replace(ROOTDIR, '', $file), file_get_contents($file));
								}
							} else {
								continue;
							}
						}
					}
					else if (is_file($path) === true)
					{
						$zip->addFromString(str_replace(ROOTDIR, '', $path), file_get_contents($path));
					}
				}
			}
			return $zip->close();
		}
		return false;
	}

	/**
	 * Архив в формате gzip (.gz)
	 */
	public function compress($name, $insert, $dir, $level = 9)
	{
		$this->_dir($dir);

		if ($out = @gzopen($dir.$name, 'wb'.$level))
		{
			gzwrite($out, $insert);
			gzclose($out);
		}
		else
		{
			$this->error("Reserve error: is not a readable file");
			return false;
		}
	}

	/**
	 * Запись в файл
	 */
	public function savedump($name, $insert, $dir)
	{
		$this->_dir($dir);
		file_put_contents($dir.$name, $insert, LOCK_EX);
	}

	/**
	 * Проверка имени файла
	 */
	public function check_name($name)
	{
		if ( ! preg_match('/^[a-zA-Z0-9&\.\-_]+$/D', $name))
		{
			$this->error("Reserve error: incorrect file name");
			return false;
		}
		return $name;
	}

	/**
	 * Проверка директории, создание
	 */
	private function _dir($dir)
	{
		if ( ! is_dir($dir))
		{
			mkdir($dir, 02777);
			chmod($dir, 02777);
		}
	}

	/**
	 * Вывод ошибок
	 */
    private function error($str)
	{
        $this->var = 1;
        $this->message[] = $str;
    }
}
