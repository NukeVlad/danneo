<?php
/**
 * File:        /mod/catalog/mod.rules.php
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
			"index.php\?dn=".$WORKMOD."&re=order&to=([a-z]*)&id=(\d+)" => $WORKMOD."/order-$1-$2",
			"index.php\?dn=".$WORKMOD."&re=order&to=([a-z]*)" => $WORKMOD."/order-$1",
			"index.php\?dn=".$WORKMOD."&re=order&p=(\d+)" => $WORKMOD."/order-$1",
			"index.php\?dn=".$WORKMOD."&to=index&p=(\d+)" => $WORKMOD."/p-$1",
			"index.php\?dn=".$WORKMOD."&to=index" => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."" => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)" => $WORKMOD."/$1",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)&p=(\d+)" => $WORKMOD."/$1-$2-$3",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)" => $WORKMOD."/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=(tags|search|rss|reviews|maker)" => $WORKMOD."/$1",
			"index.php\?dn=".$WORKMOD."&re=rss&ya=([a-zA-Z0-9_\-]*)" => $WORKMOD."/rss-$1",
			"index.php\?dn=".$WORKMOD."&re=search&id=(\d+)" => $WORKMOD."/search-$1",
			"index.php\?dn=".$WORKMOD."&re=add&ajax=(\d+)" => $WORKMOD."/add-ajax-$1",
			"index.php\?dn=".$WORKMOD."&re=maker&to=page&id=(\d+)&cpu=([a-zA-Z0-9\-]*)" => $WORKMOD."/maker/$2",
			"index.php\?dn=".$WORKMOD."&re=basket&to=([a-zA-Z]*)" => $WORKMOD."/basket-$1",
			"index.php\?dn=".$WORKMOD."&re=tags&to=tag&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/tags/$2-p$3",
			"index.php\?dn=".$WORKMOD."&re=tags&to=tag&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/tags/$2",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2/p-$3",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2/",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2-p$3",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2",
			"index.php\?dn=".$WORKMOD."&ccpu=([a-zA-Z0-9_\-]*)&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$1/$3-p$4",
			"index.php\?dn=".$WORKMOD."&ccpu=([a-zA-Z0-9_\-]*)&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$1/$3",
			"index.php\?dn=".$WORKMOD."&re=gateway&id=(\d+)" => $WORKMOD."/gateway-$1",
		),

		// cpu > url
		'to' => array
		(
			$WORKMOD."/order-([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&re=order&to=$1&id=$2",
			$WORKMOD."/order-([a-z]*)" => "index.php?dn=".$WORKMOD."&re=order&to=$1",
			$WORKMOD."/order-(\d+)" => "index.php?dn=".$WORKMOD."&re=order&p=$1",
			$WORKMOD."/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=index&p=$1",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD."&to=index",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/([a-z]*)" => "index.php?dn=".$WORKMOD."&re=$1",
			$WORKMOD."/([a-z]*)-(\d+)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2&p=$3",
			$WORKMOD."/([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2",
			$WORKMOD."/(tags|search|rss|reviews|maker)" => "index.php?dn=".$WORKMOD."&re=$1",
			$WORKMOD."/rss-([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=rss&ya=$1",
			$WORKMOD."/search-(\d+)" => "index.php?dn=".$WORKMOD."&re=search&id=$2",
			$WORKMOD."/add-ajax-(\d+)" => "index.php?dn=".$WORKMOD."&re=add&ajax=$1",
			$WORKMOD."/maker/([a-zA-Z0-9\-]*)" => "index.php?dn=".$WORKMOD."&re=maker&to=page&cpu=$1",
			$WORKMOD."/basket-([a-zA-Z]*)" => "index.php?dn=".$WORKMOD."&re=basket&to=$1",
			$WORKMOD."/tags/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&re=tags&to=tag&cpu=$1&p=$2",
			$WORKMOD."/tags/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=tags&to=tag&cpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&ccpu=$1&to=page&cpu=$2&p=$3",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&ccpu=$1&to=page&cpu=$2",
			$WORKMOD."/gateway-(\d+)" => "index.php?dn=".$WORKMOD."&re=gateway&id=$1",
		)
	)
);
