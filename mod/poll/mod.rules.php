<?php
/**
 * File:        /mod/poll/mod.rules.php
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
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2-p$3",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)&p=(\d+)" => $WORKMOD."/$1-$2-$3",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)" => $WORKMOD."/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)" => $WORKMOD."/$1",
		),

		// cpu > url
		'to' => array
		(
			$WORKMOD."/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=index&p=$1",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD."&to=index",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1",
			$WORKMOD."/([a-z]*)-(\d+)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2&p=$3",
			$WORKMOD."/([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2",
			$WORKMOD."/([a-z]*)" => "index.php?dn=".$WORKMOD."&re=$1",
		)
	)
);
