<?php
/**
 * File:        /admin/core/classes/Image.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ADMREAD') OR die('No direct access');

/**
 * Class Image
 */
class Image
{
	public $ext, $thumb;
	private $obj, $size;
	private $gd = FALSE;

	public $funclist = array
	(
		'imagecreatefromjpeg' => FALSE,
		'imagecreatefromgif'  => FALSE,
		'imagecreatefrompng'  => FALSE,
		'imagecreatefrombmp'  => FALSE,
		'imagecreatefromwebp'  => FALSE
	);

	public $funcext = array
	(
		'jpeg' => 'imagecreatefromjpeg',
		'jpg'  => 'imagecreatefromjpeg',
		'gif'  => 'imagecreatefromgif',
		'png'  => 'imagecreatefrompng',
		'bmp'  => 'imagecreatefrombmp',
		'webp'  => 'imagecreatefromwebp'
	);

	public function __construct()
	{
		$gdinfo = gd_info();
		preg_match('/\d/', $gdinfo['GD Version'], $m);
		$this->gd = $m[0];

		foreach ($this->funclist as $key => $working)
		{
			if (function_exists($key))
			{
				$this->funclist[$key] = TRUE;
			}
		}
	}

	public function start()
	{
		// old
	}

	public function realsize($path, &$width, &$height)
	{
		$size = getimagesize($path);
		$width = $size[0];
		$height = $size[1];

		return TRUE;
	}

	public function newsize($nsize, $osize)
	{
		return (($nsize * $osize) / 100);
	}

	public function extre($file)
	{
		$this->obj = '';
		$fi = pathinfo($file);
		$this->obj = strtolower($fi['extension']);

		return strtolower($fi['extension']);
	}

	public function blankimage($width, $height)
	{
		if ($this->gd >= 2)
		{
			$upimg = imagecreatetruecolor($width, $height);
			imagealphablending($upimg, false); // отключить режим смешивания
			imagesavealpha($upimg, true);      // сохранить информацию о прозрачности

			return $upimg;
		}
		else
		{
			return imagecreate($width,$height);
		}
	}

	/**
	 * Fix function imageCopyMerge()
	 * @autor serega_pyter
	 */
	private function _imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
	{
		$opacity = $pct;
		$w = imagesx($src_im);
		$h = imagesy($src_im);
		$cut = imagecreatetruecolor($src_w, $src_h);
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
		$opacity = 100 - $opacity;
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
	}

	private function makeimage($path)
	{
		$this->obj = '';
		$this->extre($path);
		ini_set("gd.jpeg_ignore_warning", 1);
		if (isset($this->funcext[$this->obj]))
		{
			if ($this->funclist[$this->funcext[$this->obj]] == TRUE) {
				return $this->funcext[$this->obj]($path);
			} else {
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	private function imagefile($image, $path)
	{
		global $conf;

		switch ($this->obj)
		{
			case 'jpg' : return imagejpeg($image, $path, $conf['imgquality']); break;
			case 'jpeg': return imagejpeg($image, $path, $conf['imgquality']); break;
			case 'gif' : return imagegif($image, $path); break;
			case 'png' : return imagepng($image, $path); break;
			case 'bmp' : return imagewbmp($image, $path); break;
			case 'webp' : return imagewebp($image, $path); break;
		}
	}

	private function imageprint($image)
	{
		global $conf;

		switch ($this->obj)
		{
			case 'jpg' : return imagejpeg($image, NULL, $conf['imgquality']); break;
			case 'jpeg': return imagejpeg($image, NULL, $conf['imgquality']); break;
			case 'gif' : return imagegif($image); break;
			case 'png' : return imagepng($image); break;
			case 'bmp' : return imagewbmp($image); break;
			case 'webp' : return imagewebp($image); break;
		}
	}

	public function createthumb($path, $dest, $tname, $width, $height, $resize)
	{
		$scrimg = $this->makeimage($path);

		if ( ! $this->realsize($path, $scr_width, $scr_height))
		{
			return FALSE;
		}

		if ($resize == 'symm')
		{
			if ($scr_width > $scr_height) {
				$height = ($width / $scr_width) * $scr_height;
			} else {
				$width = ($height / $scr_height) * $scr_width;
			}
			if ($scr_width <= $width) {
				$width = $scr_width;
			}
			if ($scr_height <= $height) {
				$height = $scr_height;
			}
		}

		$thumbimage = $this->blankimage($width, $height);

		if ($this->gd >= 2)
		{
			if ($resize == 'crop')
			{
				if ($width >= $scr_width)
				{
					$width = $scr_width;
				}

				if ($height >= $scr_height)
				{
					$height = $scr_height;
				}

				$w_ratio = $scr_width / $width;
				$h_ratio = $scr_height / $height;
				$r_ratio = min($h_ratio, $w_ratio);
				$x_resize = ($scr_width / 2) - ($width / 2) * $r_ratio;
				$y_resize = ($scr_height / 2) - ($height / 2) * $r_ratio;

				imagecopyresampled($thumbimage, $scrimg, 0, 0, $x_resize, $y_resize, $width, $height, $scr_width - 2 * $x_resize, $scr_height - 2 * $y_resize);
			}
			else
			{
				imagecopyresampled($thumbimage, $scrimg, 0, 0, 0 , 0, $width, $height, $scr_width, $scr_height);
			}
		}
		else
		{
			imagecopyresized($thumbimage, $scrimg, 0, 0, 0, 0, $width, $height, $scr_width, $scr_height);
		}

		$this->imagefile($thumbimage, $dest.$tname);
		imagedestroy($scrimg);
		imagedestroy($thumbimage);

		$this->obj = '';
	}

	public function createwater($path, $water = FALSE, $type = FALSE, $text = FALSE)
	{
		global $conf;

		if ( ! $this->realsize($path, $scr_width, $scr_height))
		{
			return FALSE;
		}
		if (empty($type))
		{
			$insert = $this->makeimage($water);
			$scrimg = $this->makeimage($path);
			if ( ! $this->realsize($insert, $wat_width, $wat_height))
			{
				return FALSE;
			}
			if ($scr_width < ($wat_width + 15) OR $scr_height < ($wat_height + 15))
			{
				return FALSE;
			}
			$insert_x = imagesx($insert);
			$insert_y = imagesy($insert);
			$dest_x = ($scr_width - $insert_x) - 15;
			$dest_y = ($scr_height - $insert_y) - 15;
			$this->_imagecopymerge($scrimg, $insert, $dest_x, $dest_y, 0, 0, $insert_x, $insert_y, $conf['markquality']);
		}
		else
		{
			$scrimg = $this->makeimage($path);
			$textcolor = imagecolorallocate($scrimg, 255, 255, 255);
			$size = 5;
			$x_text = $scr_width - imagefontwidth($size)*strlen($text) - 23;
			$y_text = $scr_height - imagefontheight($size) - 18;
			imagestring($scrimg, $size, $x_text, $y_text, $text, $textcolor);
		}
		$this->imagefile($scrimg,$path);
		imagedestroy($scrimg);
		$this->obj = '';
	}

	public function viewthumb($path, $width, $height, $resize)
	{
		$scrimg = $this->makeimage($path);

		if ( ! $this->realsize($path, $scr_width, $scr_height))
		{
			return FALSE;
		}

		if ($resize != 'symm')
		{
			if ($scr_width > $scr_height) {
				$height = ($width / $scr_width) * $scr_height;
			} else {
				$width = ($height / $scr_height) * $scr_width;
			}
		}

		if ($scr_width <= $width)
		{
			$width = $scr_width;
		}

		if ($scr_height <= $height)
		{
			$height = $scr_height;
		}

		$thumbimage = $this->blankimage($width, $height);
		if ($this->gd >= 2) {
			imagecopyresampled($thumbimage, $scrimg, 0, 0, 0, 0, $width, $height, $scr_width, $scr_height);
		} else {
			imagecopyresized($thumbimage, $scrimg, 0, 0, 0, 0, $width, $height, $scr_width, $scr_height);
		}
		$this->imageprint($thumbimage);
		imagedestroy($scrimg);
		imagedestroy($thumbimage);
		$this->obj = '';
	}

	public function imgconvert($inimg, $outimg, $type = FALSE)
	{
		global $conf;

		$scrimg = $this->makeimage($inimg);
		imageinterlace($scrimg, 1);
		imagejpeg($scrimg, $outimg, $conf['imgquality']);
		imagedestroy($scrimg);
		unlink($inimg);
		$this->obj = '';
	}

	public function urlimg($url, $size)
	{
		$this->size = $size;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024);
		curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_PROGRESSFUNCTION,
			function ($ch, $down_size, $down, $up_size, $upload)
			{
				if ($down > $this->size) {
					return -1;
				}
			}
		);

		return curl_exec($ch);
	}

	public function tmpfile($image)
	{
		if (empty($image))
			return false;

		$info = getimagesizefromstring($image);

		$ext = image_type_to_extension($info[2]);
		$this->ext = str_replace('jpeg', 'jpg', $ext);

		if (strpos($info['mime'], 'image') !== false)
		{
			$temp_dir = sys_get_temp_dir();
			$first = strpos($temp_dir, ':') === false ? DIRECTORY_SEPARATOR : '';
			$file = $first . trim($temp_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim(md5($image) . $this->ext, DIRECTORY_SEPARATOR);

			file_put_contents($file, $image);

			register_shutdown_function(
				function() use($file)
				{
					if (file_exists($file)) {
						unlink($file);
					}
				}
			);
			return $file;
		}
		return false;
	}

	public function unique()
	{
		return date("ymd", time()).'_'.mt_rand(0, 9999);
	}

	/*
	 * Create thumb url
	 * @param Image URL and save path
	 * @return Saved image path
	 * */
	public function url_thumb($url, $patch, $width = null, $height = null)
	{
		global $conf;

		$image = $this->urlimg($url, $conf['maxfile']);

		$width = ($width) ? $width : $conf['width'];
		$height = ($height) ? $height : $conf['height'];

		$name = $this->unique();
		$tmp_name = $this->tmpfile($image);

		if ($tmp_name AND copy($tmp_name, $patch.$name.$this->ext))
		{
			$image = $name.$this->ext;
			$image_thumb = $name.'_thumb'.$this->ext;

			if ($this->ext != '.jpg')
			{
				$this->imgconvert($patch.$image, $patch.$name.'.jpg');
				$image = $name.'.jpg';
				$image_thumb = $name.'_thumb.jpg';
			}

			$this->createthumb($patch.$image, $patch, $image_thumb, $width, $height, $conf['resize']);
			$this->thumb = $image_thumb;

			if (file_exists($patch.$image))
			{
				unlink($patch.$image);
			}
		}
	}
}
