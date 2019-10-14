<?php
/**
 * File:        /mod/pages/mod.rules.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

global $pa;

/**
 * Рабочий мод
 */
$WORKMOD = basename(__DIR__);

/**
 * Шаблоны преобразований URL
 */
return array
(
	$WORKMOD => array
	(
		// url -> cpu
		're' => array
		(
			"index.php\?dn=".$WORKMOD."&re=search" => $WORKMOD,
			"index.php\?dn=".$WORKMOD."&re=search&id=(\d+)&p=(\d+)" => $WORKMOD."-$1-$2",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)&to=index&p=(\d+)" => "$1/p-$2",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)&re=([a-z]*)&id=(\d+)" => "$1/$2-$3",
			"index.php\?dn=".$WORKMOD."&re=load&id=(\d+)&fid=(\d+)&ds=([a-zA-Z0-9_\-]*)" => "load-$1-$2-$3",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)&re=load&id=(\d+)&fid=(\d+)&ds=([a-zA-Z0-9_\-]*)" => "$1/load-$2-$3-$4",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)&to=index" => "$1/",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)" => "$1/",
			"index.php\?dn=".$WORKMOD."&cpu=([a-zA-Z0-9_\-]*)" => "$1",
			"index.php\?dn=".$WORKMOD."&pa=([a-zA-Z0-9_\-]*)&cpu=([a-zA-Z0-9_\-]*)" => "$1/$2"
		),

		// cpu -> url
		'to' => array
		(
			$WORKMOD => "index.php?dn=".$WORKMOD."&re=search",
			$WORKMOD."-(\d+)-(\d+)" => "index.php?dn=".$WORKMOD."&re=search&id=$1&p=$2",
			"([a-zA-Z0-9_\-]*)/p-(\d+)" => "index.php?dn=".$WORKMOD."&pa=$1&to=index&p=$2",
			"([a-zA-Z0-9_\-]*)/([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&pa=$1&re=$2&id=$3",
			"load-(\d+)-(\d+)-([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=load&id=$1&fid=$2&ds=$3",
			"([a-zA-Z0-9_\-]*)/load-(\d+)-(\d+)-([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&pa=$1&re=load&id=$2&fid=$3&ds=$4",
			"([a-zA-Z0-9_\-]*)/" => "index.php?dn=".$WORKMOD."&pa=$1&to=index",
			"([a-zA-Z0-9_\-]*)/" => "index.php?dn=".$WORKMOD."&pa=$1",
			"([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&cpu=$1",
			"([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&pa=$1&cpu=$2"
		)
	)
);
