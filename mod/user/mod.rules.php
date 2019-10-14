<?php
/**
 * File:        /mod/user/mod.rules.php
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
			"index.php\?dn=".$WORKMOD => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)" => $WORKMOD."/$1",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)" => $WORKMOD."/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&to=([a-z]*)" => $WORKMOD."/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&to=([a-z]*)&id=(\d+)&code=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$1/$2/$3-$4"
		),

		// cpu > url
		'to' => array
		(
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/([a-z]*)" => "index.php?dn=".$WORKMOD."&re=$1",
			$WORKMOD."/([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2",
			$WORKMOD."/([a-z]*)-([a-z]*)" => "index.php?dn=".$WORKMOD."&re=$1&to=$2",
			$WORKMOD."/([a-z]*)/([a-z]*)/(\d+)-([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=$1&to=$2&id=$3&code=$4"
		)
	)
);
