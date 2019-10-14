<?php
/**
 * File:        /mod/media/mod.rules.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('DNREAD') OR die('No direct access');

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
		// url > cpu
		're' => array
		(
			"index.php\?dn=".$WORKMOD."&to=index&p=(\d+)" => $WORKMOD."/p-$1",
			"index.php\?dn=".$WORKMOD."&to=index" => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."" => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."&to=video&id=(\d+)" => $WORKMOD."/video-$1",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2/p-$3",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2/",
			"index.php\?dn=".$WORKMOD."&ccpu=([a-zA-Z0-9_\-]*)&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$1/$3"
		),

		// cpu > url
		'to' => array
		(
			$WORKMOD."/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=index&p=$1",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD."&to=index",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/video-(\d+)" => "index.php?dn=".$WORKMOD."&to=video&id=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&ccpu=$1&to=page&cpu=$2"
		)
	)
);
