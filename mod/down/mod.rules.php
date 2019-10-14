<?php
/**
 * File:        /mod/".$WORKMOD."/mod.rules.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.4
 * @copyright   (c) 2005-2017 Danneo Team
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
			"index.php\?dn=".$WORKMOD => $WORKMOD."/",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)&p=(\d+)" => $WORKMOD."/$1-$2-$3",
			"index.php\?dn=".$WORKMOD."&re=([a-z]*)&id=(\d+)" => $WORKMOD."/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=(tags|search|add|rss|comment)" => $WORKMOD."/$1",
			"index.php\?dn=".$WORKMOD."&re=load&cpu=([a-zA-Z0-9\-]*)&ds=([a-z0-9]*)" => $WORKMOD."/load/$1-$2",
			"index.php\?dn=".$WORKMOD."&re=rss&ya=([a-zA-Z0-9_\-]*)" => $WORKMOD."/rss-$1",
			"index.php\?dn=".$WORKMOD."&re=tags&to=tag&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/tags/$2-p$3",
			"index.php\?dn=".$WORKMOD."&re=tags&to=tag&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/tags/$2",
			"index.php\?dn=".$WORKMOD."&to=dat&ye=(\d+)&mo=(\d+)&da=(\d+)&p=(\d+)" => $WORKMOD."/date-$1-$2-$3-p$4",
			"index.php\?dn=".$WORKMOD."&to=dat&ye=(\d+)&mo=(\d+)&da=(\d+)" => $WORKMOD."/date-$1-$2-$3",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2/p-$3",
			"index.php\?dn=".$WORKMOD."&to=cat&id=(\d+)&ccpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2/",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$2-p$3",
			"index.php\?dn=".$WORKMOD."&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$2",
			"index.php\?dn=".$WORKMOD."&ccpu=([a-zA-Z0-9_\-]*)&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)&p=(\d+)" => $WORKMOD."/$1/$3-p$4",
			"index.php\?dn=".$WORKMOD."&ccpu=([a-zA-Z0-9_\-]*)&to=page&id=(\d+)&cpu=([a-zA-Z0-9_\-]*)" => $WORKMOD."/$1/$3"
		),

		// cpu > url
		'to' => array
		(
			$WORKMOD."/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=index&p=$1",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD."&to=index",
			$WORKMOD."/" => "index.php?dn=".$WORKMOD,
			$WORKMOD."/([a-z]*)-(\d+)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2&p=$3",
			$WORKMOD."/([a-z]*)-(\d+)" => "index.php?dn=".$WORKMOD."&re=$1&id=$2",
			$WORKMOD."/(tags|search|add|rss|comment)" => "index.php?dn=".$WORKMOD."&re=$1",
			$WORKMOD."/load/([a-zA-Z0-9\-]*)-([a-z0-9]*)" => "index.php?dn=".$WORKMOD."&re=load&cpu=$1&ds=$2",
			$WORKMOD."/rss-([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=rss&ya=$1",
			$WORKMOD."/tags/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&re=tags&to=tag&cpu=$1&p=$2",
			$WORKMOD."/tags/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&re=tags&to=tag&cpu=$1",
			$WORKMOD."/date-(\d+)-(\d+)-(\d+)-p(\d+)" => "index.php?dn=".$WORKMOD."&to=dat&ye=$1&mo=$2&da=$3&p=$4",
			$WORKMOD."/date-(\d+)-(\d+)-(\d+)" => "index.php?dn=".$WORKMOD."&to=dat&ye=$1&mo=$2&da=$3",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/p-(\d+)" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/" => "index.php?dn=".$WORKMOD."&to=cat&ccpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1&p=$2",
			$WORKMOD."/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&to=page&cpu=$1",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)-p(\d+)" => "index.php?dn=".$WORKMOD."&ccpu=$1&to=page&cpu=$2&p=$3",
			$WORKMOD."/([a-zA-Z0-9_\-]*)/([a-zA-Z0-9_\-]*)" => "index.php?dn=".$WORKMOD."&ccpu=$1&to=page&cpu=$2",
		)
	)
);
