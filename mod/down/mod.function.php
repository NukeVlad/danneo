<?php
/**
 * File:        /mod/down/mod.function.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.4
 * @copyright   (c) 2005-2017 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

/**
 * Функция рейтинга
 */
function rating($rate, $id, $current)
{
	global $config, $lang, $tm;

	if ($config['ajax'] == 'yes' AND $current == 0)
	{
		$width = intval((100 / 5) * $rate);
		return $tm->parse(array
					(
						'id'     => $id,
						'width'  => $width,
						'rate_1' => $lang['rate_1'],
						'rate_2' => $lang['rate_2'],
						'rate_3' => $lang['rate_3'],
						'rate_4' => $lang['rate_4'],
						'rate_5' => $lang['rate_5']
					),
					$tm->create('mod/'.WORKMOD.'/ajax.rating'));
	} else {
		return $r = '<img src="'.SITE_URL.'/template/'.SITE_TEMP.'/images/rating/'.$rate.'.png" alt="'.(($rate == 0) ? $lang['rate_0'] : $lang['rate_'.$rate.'']).'" />';
	}
}

/**
 * Типы файлов
 */
function file_type($path)
{
	$type = strtolower(substr(strrchr($path, '.'), 1));
	$ext = array(
	'rar'	=> '<a href="http://www.rarlab.com/">RAR</a>',
	'zip'	=> '<a href="http://www.winzip.com/">ZIP</a>',
	'7z'	=> '<a href="http://www.7-zip.org/">7z</a>',
	'bz2'	=> 'BZ2',
	'cab'	=> 'CAB',
	'ace'	=> 'ACE',
	'arj'	=> '<a href="http://www.tsf.be/">ARJ</a>',
	'jar'	=> '<a href="http://www.tsf.be/">JAR</a>',
	'gzip'	=> 'GZIP',
	'tar'	=> 'TAR',
	'tgz'	=> 'TGZ',
	'gz'	=> 'GZ',
	'gif'	=> 'GIF',
	'jpeg'	=> 'JPEG',
	'jpg'	=> 'JPG',
	'png'	=> 'PNG',
	'bmp'	=> 'BMP',
	'txt'	=> 'TXT',
	'sql'	=> 'SQL',
	'exe'	=> 'EXE',
	'swf'	=> 'SWF',
	'fla'	=> 'FLA',
	'wav'	=> 'WAV',
	'mp2'	=> 'MP2',
	'mp3'	=> 'MP3',
	'mp4'	=> 'MP4',
	'mid'	=> 'MID',
	'midi'	=> 'MIDI',
	'mmf'	=> 'MMF',
	'mpeg'	=> 'MPEG',
	'mpe'	=> 'MPE',
	'mpg'	=> 'MPG',
	'mpa'	=> 'MPA',
	'avi'	=> 'AVI',
	'mpga'	=> 'MPGA',
	'pdf'	=> 'Document Adobe',
	'pds'	=> 'Document Adobe',
	'xls'	=> 'MS-Excel',
	'xl'	=> 'MS-Excel',
	'xla'	=> 'MS-Excel',
	'xlb'	=> 'MS-Excel',
	'xlc'	=> 'MS-Excel',
	'xld'	=> 'MS-Excel',
	'xlk'	=> 'MS-Excel',
	'xll'	=> 'MS-Excel',
	'xlm'	=> 'MS-Excel',
	'xlt'	=> 'MS-Excel',
	'xlv'	=> 'MS-Excel',
	'xlw'	=> 'MS-Excel',
	'doc'	=> 'MS-Word',
	'dot'	=> 'MS-Word',
	'wiz'	=> 'MS-Word',
	'wzs'	=> 'MS-Word',
	'pot'	=> 'MS-PowerPoint',
	'ppa'	=> 'MS-PowerPoint',
	'pps'	=> 'MS-PowerPoint',
	'ppt'	=> 'MS-PowerPoint',
	'pwz'	=> 'MS-PowerPoint'
	);
	return (isset($ext[$type])) ? $ext[$type] : '';
}

/**
 * Заголовки
 */
function conttype($type)
{
	$mime = array(
		'zip'	=> 'Content-type: application/zip',
		'rar'	=> 'Content-type: application/x-rar-compressed',
		'gtar'	=> 'Content-type: application/x-gtar',
		'gz'	=> 'Content-type: application/x-gzip',
		'gzip'	=> 'Content-type: application/x-gzip',
		'tgz'	=> 'Content-type: application/x-gzip',
		'psd'	=> 'Content-type: image/x-photoshop',
		'pdf'	=> 'Content-type: application/pdf',
		'swf'	=> 'Content-type: application/x-shockwafe-flash',
		'pps'	=> 'Content-type: application/vnd.ms-powerpoint',
		'hlp'	=> 'Content-type: application/winhlp',
		'doc'	=> 'Content-type: application/msword',
		'xml'	=> 'Content-type: application/xml',
		'xls'	=> 'Content-type: application/vnd.ms-excel',
		'csv'	=> 'Content-type: text/comma-separated-values',
		'midi'	=> 'Content-type: audio/midi',
		'mp2'	=> 'Content-type: audio/mpeg',
		'mp3'	=> 'Content-type: audio/mpeg',
		'wav'	=> 'Content-type: audio/wav',
		'wmv'	=> 'Content-type: video/mpeg',
		'wma'	=> 'Content-type: video/mpeg',
		'mlv'	=> 'Content-type: video/mpeg',
		'mpa'	=> 'Content-type: video/mpeg',
		'mpe'	=> 'Content-type: video/mpeg',
		'mpeg'	=> 'Content-type: video/mpeg',
		'mpg'	=> 'Content-type: video/mpeg',
		'rm'	=> 'Content-type: application/vnd.rn-realplayer',
		'qt'	=> 'Content-type: video/quicktime',
		'mov'	=> 'Content-type: video/quicktime',
		'avi'	=> 'Content-type: video/x-msvideo'
	);
	return (isset($mime[$type])) ? $mime[$type] : 'application/force-download';
}
