<?php
/**
 * File:        /core/classes/Loader.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Loader
*/
class Loader
{
	/**
	 * Prefix and the value
	 * @var array
	 */
	protected $prefixes = array();

	/**
	 * The root directory of classes
	 * @param string
	 */
	protected $path = null;

	public function __construct($path)
	{
		$this->path = $path;
		$this->register();
		$this->subDir();
		$this->add();
	}

	/**
	 * Register loader with SPL autoloader stack.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'load'));
	}

	/**
	 * Unregisters this instance as an autoloader.
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'load'));
	}

	/**
	 * Connecting classes in the Root directory without namespaces.
	 * Global space.
	 *
	 * @param string	$prefix  The prefix
	 * @param string	$path  The path
	 */
	public function add($prefix = NULL, $path = NULL)
	{
		$prefix = trim($prefix, '\\');
		$path = ($path) ? $path : $this->path;
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$this->prefixes[$prefix][] = $path;
	}

	/**
	 * Scan sub directories
	 * Create a path and namespace, and connection of found classes
	 */
	public function subDir()
	{
		$iterator = new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS);
		foreach($iterator as $item )
		{
			if (is_dir($item))
			{
				$item = str_replace('\\', '/', $item);
				$subdir = str_replace($this->path, '', $item);
				$namespace = ucfirst(str_replace($this->path, '', $item));
				$prefix = 'DN\\'.basename($namespace).'';
				$this->add($prefix, $this->path.basename($subdir));
			}
		}
	}

	/**
	 * Connecting classes with arbitrary paths and namespaces
	 */
	public function handClass($prefix = NULL, $path = NULL)
	{
		$prefix = trim($prefix, '\\');
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$this->prefixes[$prefix][] = $path;
	}

	/**
	 * Loads the class file for a given class name.
	 * @param string	$class  Current class
	 */
	public function load($class)
	{
		$name = NULL;
		$parts = explode('\\', $class);
		while ($parts)
		{
			$name .= DIRECTORY_SEPARATOR.array_pop($parts);
			$prefix = implode('\\', $parts);

			if (isset($this->prefixes[$prefix]) === false)
			{
				continue;
			}

			foreach ($this->prefixes[$prefix] as $path)
			{
				$file = $path.$name.'.php';
				if (is_readable($file))
				{
					include $file;
				}
			}
		}
	}
}
