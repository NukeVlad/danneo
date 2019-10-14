<?php
/**
 * File:        /core/classes/Captcha.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Class Captcha
 */
class Captcha
{
	public $code = array();
	public $life = 240;
	public $chars = '123456789';
	public $letters = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
	public $fonts = array(DNDIR.'core/truefonts/captcha.ttf');
	public $size = 12;
	public $imagewidth = 100;
	public $imageheight = 50;
	public $imagebg = array(255, 255, 255);
	public $colors = array('10', '30', '50', '70', '90', '110', '130', '150', '170', '190', '210');

	/**
	 * Class captcha _contructor
	 */
	public function __construct()
	{
		$this->find();
		$this->view();
	}

	private function find()
	{
		global $db, $basepref;

		$db->query
			(
				"DELETE FROM ".$basepref."_captcha
				 WHERE captchtime < ".(NEWTIME - $this->life).""
			);

		$capitem = $db->fetchassoc
							(
								$db->query
								(
									"SELECT captchcode FROM ".$basepref."_captcha
									 WHERE captchip = '".$db->escape(REMOTE_ADDRS)."'
									 AND captchtime > ".(NEWTIME - $this->life)."
									 ORDER BY captchtime ASC LIMIT 1"
								)
							);

		if ($capitem['captchcode'])
		{
			for ($i = 0; $i < mb_strlen($capitem['captchcode']); $i++)
			{
				$this->code[$i] = $capitem['captchcode']{$i};
			}
		}
		else
		{
			$insertcode = '';
			for ($i = 0; $i < 5; $i ++)
			{
				$insertcode.= $symbol = mb_substr($this->chars, (mt_rand() % mb_strlen($this->chars)), 1);
				$this->code[$i] = $symbol;
			}
			$db->query
				(
					"INSERT INTO ".$basepref."_captcha VALUES (
					 NULL,
					 '".$db->escape(REMOTE_ADDRS)."',
					 '".$insertcode."',
					 '".NEWTIME."'
					 )"
				);
		}
	}

	private function blur($image, $radius)
	{
		$radius = round(max(0, min($radius, 50)) * 2);
		$w = imagesx($image);
		$h = imagesy($image);
		$blur = imagecreate($w, $h);

		for ($i = 0; $i < $radius; $i ++)
		{
			imagecopy($blur, $image, 0, 0, 1, 1, $w - 1, $h - 1);
			imagecopymerge($blur, $image, 1, 1, 0, 0, $w, $h, 50.0000);
			imagecopymerge($blur, $image, 0, 1, 1, 0, $w - 1, $h, 33.3333);
			imagecopymerge($blur, $image, 1, 0, 0, 1, $w, $h - 1, 25.0000);
			imagecopymerge($blur, $image, 0, 0, 1, 0, $w - 1, $h, 33.3333);
			imagecopymerge($blur, $image, 1, 0, 0, 0, $w, $h, 25.0000);
			imagecopymerge($blur, $image, 0, 0, 0, 1, $w, $h - 1, 20.0000);
			imagecopymerge($blur, $image, 0, 1, 0, 0, $w, $h, 16.6667);
			imagecopymerge($blur, $image, 0, 0, 0, 0, $w, $h, 50.0000);
			imagecopy($image, $blur, 0, 0, 0, 0, $w, $h);
		}

		imagedestroy($blur);
	}

	private function wave($image, $x, $y, $width, $height, $grade = 5)
	{
		for ($i = 0; $i < $width; $i += 2)
		{
			imagecopy($image, $image, $x + $i - 2, $y + sin($i / 10) * $grade, $x + $i, $y, 2, $height);
		}
	}

	public function view()
	{
		header("Expires: Tue, 11 Jun 1985 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", FALSE);
		header("Pragma: no-cache");
		header("Content-type: image/gif");

		$width = $this->imagewidth;
		$height = $this->imageheight;
		$colors = $this->colors;

		$this->size = rand($this->size - 1, $this->size + 1);
		$image = imagecreatetruecolor($width, $height);
		$bgcolor = imagecolorallocate($image, $this->imagebg[0], $this->imagebg[1], $this->imagebg[2]);
		imagefill($image, 0, 0, $bgcolor);

		for ($i = 0; $i < 30; $i ++)
		{
			$color = imagecolorallocatealpha($image, rand(0, 255), rand(0, 255), rand(0, 255), 90);
			$font = $this->fonts[rand(0, sizeof($this->fonts)-1)];
			$letter = $this->letters[rand(0, sizeof($this->letters)-1)];
			$size = rand($this->size * 1.2 - 2, $this->size * 1.2 + 2);

			imagettftext($image, $size, rand(0, 45), rand($width * 0.1, $width - $width *0.1), rand($height * 0.2, $height), $color, $font, $letter);
		}

		for ($i = 0; $i < count($this->code); $i ++)
		{
			$color = imagecolorallocatealpha($image, $colors[rand(0, sizeof($colors)-1)], $colors[rand(0, sizeof($colors)-1)], $colors[rand(0, sizeof($colors)-1)], rand(20, 40));
			$font = $this->fonts[rand(0, sizeof($this->fonts)-1)];
			$letter = $this->code[$i];
			$size = rand($this->size * 2.1 - 2, $this->size * 2.1 + 2);
			$x = (($i + 1) * $this->size) + rand($this->size, $this->size * 2);
			$x = ($i + 1) * $this->size  + rand(0, 5);
			$y = (($height * 2) / 3) + rand(0, 10);

			imagettftext($image, $size, rand(-15, 15), $x, $y, $color, $font, $letter);
		}

		imagegif($image);
		imagedestroy($image);
		exit();
	}
}
