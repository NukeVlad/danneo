<?php
/**
 * File:        /mod/subscribe/mod.rules.php
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
		// url -> cpu
		're' => array
		(
			"index.php\?dn=".$WORKMOD => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."&to=unsub" => $WORKMOD."/unsub",
			"index.php\?dn=".$WORKMOD."&to=act&id=(\d+)&sa=([a-z0-9]*)" => $WORKMOD."/act-$1-$2",
			"index.php\?dn=".$WORKMOD."&to=del&id=(\d+)&sa=([a-z0-9]*)" => $WORKMOD."/del-$1-$2"
		),

		// cpu -> url
		'to' => array
		(
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/unsub" => "index.php?dn=".$WORKMOD."&to=unsub",
			$WORKMOD."/act-(\d+)-([a-z0-9]*)" => "index.php?dn=".$WORKMOD."&to=act&id=$1&sa=$2",
			$WORKMOD."/del-(\d+)-([a-z0-9]*)" => "index.php?dn=".$WORKMOD."&to=del&id=$1&sa=$2"
		)
	)
);
