<?php
/**
 * File:        /block/b-VCard.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $lang, $config, $api;

$bc = NULL;
$vc = array();

if (defined('SETTING'))
{
	return $bs = array('blockname' => $lang['vcard']);
}

/**
 * Контакты организации
 */
if ( ! empty($config['contact']['vcard']))
{
	$vc = Json::decode($config['contact']['vcard']);
	$url = ( ! empty($vc['vcard_url'])) ? $vc['vcard_url'] : SITE_URL;
	if (is_array($vc) AND ! empty($vc))
	{
		$bc.= '
		<div class="vcard">
			<a class="fn org url" href="'.$url.'">'.$vc['vcard_org'].'</a>
			<p class="adr">';
				$bc.= ! empty($vc['vcard_code']) ? '<span class="postal-code">'.$vc['vcard_code'].'</span>, ' : '';
				$bc.= ! empty($vc['vcard_country']) ? '<span class="country-name">'.$vc['vcard_country'].'</span>, ' : '';
				$bc.= ! empty($vc['vcard_region']) ? '<span class="region">'.$vc['vcard_region'].'</span>, ' : '';
				$bc.= ! empty($vc['vcard_locality']) ? '<span class="locality">'.$vc['vcard_locality'].'</span>, ' : '';
				$bc.= ! empty($vc['vcard_street']) ? '<span class="street-address">'.$vc['vcard_street'].'</span>' : '';
			$bc.= '
			</p>';
			$bc.= ! empty($vc['vcard_tel']) ? $lang['vcard_tel'].': <span class="tel">'.$api->call_tel($vc['vcard_tel']).'</span><br>' : '';
			$bc.= ! empty($vc['vcard_email']) ? $lang['e_mail'].': <span class="email"><a href="mailto:'.$vc['vcard_email'].'">'.$vc['vcard_email'].'</a></span><br>' : '';
			$bc.= ! empty($vc['vcard_work']) ? $lang['vcard_work'].': <span class="workhours">'.$vc['vcard_work'].'</span><br>' : '';
			if ( ! empty($vc['longitude']) AND ! empty($vc['latitude']))
			{
				$bc.= '<span class="geo">
							<span class="latitude"><span class="value-title" title="'.$vc['latitude'].'"></span></span>
							<span class="longitude"><span class="value-title" title="'.$vc['longitude'].'"></span></span>
						</span>';
			}
		$bc.= '
		</div>';
	}
	else
	{
		$bc.= $lang['data_not'];
	}
}
else
{
	$bc.= $lang['data_not'];
}
return $bc;
