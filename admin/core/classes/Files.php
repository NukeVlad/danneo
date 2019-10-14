<?php
/**
 * File:        /core/classes/Files.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Files
*/
class Files
{
    /**
     * Local array mimes
     * @var array
     */
    protected $mimes = array();

	/**
	 * Class File _contructor
	 */
	public function __construct()
	{
		$this->det_mimes();
	}

	/**
	 * Attempt to get the mime type from a file
	 * @param   string $filename
	 * @return  string mime type on success or false on failure
	 */
	public function mimes($name)
	{
		$path = realpath($name);
		$ext = $this->file_ext($name);

		// getimagesize mime for images
		if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $ext))
		{
			$file = getimagesize($path);
			if (isset($file['mime']))
			{
				return $file['mime'];
			}
		}
		// Search for in local array
		if ( ! empty($ext))
		{
			return $this->ext_mime($ext);
		}
		// PECL fileinfo
		if (class_exists('finfo', FALSE))
		{
			if ($info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME))
			{
				return $info->file($path);
			}
		}
		// Detect MIME Content-type for a file (deprecated)
		if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type'))
		{
			return mime_content_type($path);
		}

		return FALSE;
	}

	/**
	 * Return the mime type of an extension
	 * @param   string  $ext
	 * @return  string  mime type on success or false on failure
	 */
	public function ext_mime($ext)
	{
		return isset($this->mimes[$ext]) ? $this->mimes[$ext][0] : FALSE;
	}

	/**
	 * Lookup MIME types for a file
	 * @param string $ext
	 * @return array Array of MIMEs associated with the specified extension
	 */
	public function ext_mimes($ext)
	{
		return isset($this->mimes[$ext]) ? ( (array) $this->mimes[$ext]) : array();
	}

	/**
	 * Lookup file extensions by MIME type
	 *
	 * @param   string  $type File MIME type
	 * @return  array   File extensions matching MIME type
	 */
	public function types_ext($type)
	{
		static $types = array();

		$array_mimes = require ADMDIR.'/includes/mimes.php';

		if (empty($types))
		{
			foreach ($array_mimes as $ext => $mimes)
			{
				foreach ($mimes as $mime)
				{
					if ($mime == 'application/octet-stream')
					{
						continue;
					}
					if ( ! isset($types[$mime]))
					{
						$types[$mime] = array( (string) $ext);
					}
					elseif ( ! in_array($ext, $types[$mime]))
					{
						$types[$mime][] = (string) $ext;
					}
				}
			}
		}

		return isset($types[$type]) ? $types[$type] : FALSE;
	}

	/**
	 * Searches a single file extension to MIME type
	 *
	 * @param   string $type MIME type to a search
	 * @return  mixed First file extension matching or false
	 */
	public function type_ext($type)
	{
		return current($this->types_ext($type));
	}

	/**
	 * Local array mime types
	 * @return  array MIME type
	 */
	public function det_mimes()
	{
		$this->mimes = require ADMDIR.'/includes/mimes.php';
	}

	/**
	 * Get extension of the file
	 *
	 * @param   string $name path to file
	 * @return  string extension
	 */
	public function file_ext($name)
	{
		$path = realpath($name);
		return strtolower(pathinfo($path, PATHINFO_EXTENSION));
	}

	/**
	 * Get extension of the file
	 *
	 * @param   string $name path to file
	 * @return  string extension
	 */
	public function file_name($file, $new = NULL)
	{
		$pathinfo = pathinfo($file);

		if (isset($new)) {
			return str_replace('-', '_', $new).'.'.$this->file_ext($file);
		} else {
			return $pathinfo['basename'];
		}

	}

	/**
	 * Type file for headline
	 *
	 * @param   string $file path to file
	 * @return  string extension
	 */
	public function file_type($file)
	{
		return ($this->mimes($file)) ? $this->mimes($file) : 'application/force-download';
	}

	/**
	 * Force download
	 *
	 * @param   string $file path to file | $new new name for file
	 * @return  read and return in browser
	 */
	public function download($file, $new = NULL)
	{
		if (ob_get_level()) {
			ob_end_clean();
		}

		$fname = isset($new) ? $this->file_name($file, $new) : $this->file_name($file);
		$readsize = 1024 * 1024;

		header('Content-Description: File Transfer');
		header("Content-Type: ".$this->file_type($file)."");
		header("Content-Disposition: attachment; filename=".$fname."");
		header("Content-Transfer-Encoding: binary");
		header("Expires: 0");
		header("Pragma: public");
		header("Cache-Control: private", FALSE);
		header("Cache-Control: must-revalidate");
		header('ETag: '.sprintf('%x-%x-%x', fileinode($file), filesize($file), filemtime($file)));
		header("Content-Length: ".filesize($file));

		$fopen = fopen($file, 'rb');
		if ($fopen == TRUE)
		{
			while ( ! feof($fopen) AND connection_status() == 0)
			{
				$buffer = fread($fopen, $readsize);
				echo $buffer;
				flush();
			}
		}

		return fclose($fopen);
		exit;
	}
}
