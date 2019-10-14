DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `makid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `stpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `unpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `price` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `priceold` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `articul` text NOT NULL,
  `creation` int(11) unsigned NOT NULL DEFAULT '0',
  `tax` smallint(2) unsigned NOT NULL DEFAULT '0',
  `amountmin` smallint(5) unsigned NOT NULL DEFAULT '1',
  `amountmax` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `customs` text NOT NULL,
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `textshort` text NOT NULL,
  `textmore` longtext NOT NULL,
  `buyinfo` text NOT NULL,
  `actinfo` enum('yes','no') NOT NULL DEFAULT 'yes',
  `weight` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `weights` varchar(10) NOT NULL,
  `length` decimal(7,2) unsigned NOT NULL DEFAULT '0.00',
  `width` decimal(7,2) unsigned NOT NULL DEFAULT '0.00',
  `height` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `size` varchar(10) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_align` enum('left','right') NOT NULL DEFAULT 'left',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  `video` varchar(255) NOT NULL,
  `act` enum('yes','no') NOT NULL DEFAULT 'yes',
  `acc` enum('all','user') NOT NULL DEFAULT 'all',
  `groups` text NOT NULL,
  `reviews` int(11) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL,
  `images` text NOT NULL,
  `files` text NOT NULL,
  `options` text NOT NULL,
  `associat` text NOT NULL,
  `buyhits` int(11) unsigned NOT NULL DEFAULT '0',
  `rec` smallint(1) unsigned NOT NULL DEFAULT '0',
  `store` enum('yes','no') NOT NULL DEFAULT 'yes',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `totalrating` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `makid` (`makid`),
  KEY `public` (`public`),
  KEY `price` (`price`),
  KEY `buyhits` (`buyhits`),
  KEY `rec` (`rec`),
  KEY `hits` (`hits`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_basket;
--
CREATE TABLE {pref}_{mod}_basket (
  `bid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `session` varchar(32) NOT NULL DEFAULT '',
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  `basket` text NOT NULL,
  PRIMARY KEY (`bid`),
  KEY `session` (`session`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_cat;
--
CREATE TABLE {pref}_{mod}_cat (
  `catid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
  `catcpu` varchar(255) NOT NULL DEFAULT '',
  `catname` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `catdesc` text NOT NULL,
  `catcustom` text NOT NULL,
  `keywords` text NOT NULL,
  `descript` text NOT NULL,
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `access` enum('all','user') NOT NULL DEFAULT 'all',
  `groups` text NOT NULL,
  `sort` varchar(11) NOT NULL DEFAULT 'id',
  `ord` enum('asc','desc') NOT NULL DEFAULT 'asc',
  `rss` enum('yes','no') NOT NULL DEFAULT 'yes',
  `options` text NOT NULL,
  `total` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`catid`),
  KEY `parentid` (`parentid`),
  KEY `catcpu` (`catcpu`),
  KEY `access` (`access`),
  KEY `rss` (`rss`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_delivery;
--
CREATE TABLE {pref}_{mod}_delivery (
  `did` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `descr` text NOT NULL,
  `data` text NOT NULL,
  `posit` smallint(2) NOT NULL DEFAULT '0',
  `type` enum('custom','auto') NOT NULL DEFAULT 'custom',
  `ext` text NOT NULL,
  `act` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`did`),
  KEY `posit` (`posit`),
  KEY `act` (`act`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_maker;
--
CREATE TABLE {pref}_{mod}_maker (
  `makid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `makname` varchar(255) NOT NULL DEFAULT '',
  `makdesc` text NOT NULL,
  `makcustom` text NOT NULL,
  `keywords` text NOT NULL,
  `descript` text NOT NULL,
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `site` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `adress` text NOT NULL,
  PRIMARY KEY (`makid`),
  KEY `cpu` (`cpu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_option;
--
CREATE TABLE {pref}_{mod}_option (
  `oid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `search` smallint(1) unsigned NOT NULL DEFAULT '0',
  `buy` smallint(1) unsigned NOT NULL DEFAULT '0',
  `posit` smallint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`),
  KEY `search` (`search`),
  KEY `buy` (`buy`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_option_value;
--
CREATE TABLE {pref}_{mod}_option_value (
  `vid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `oid` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `modify` enum('not','fix','percent') NOT NULL DEFAULT 'not',
  `modvalue` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `posit` int(11) unsigned NOT NULL,
  PRIMARY KEY (`vid`),
  KEY `oid` (`oid`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_order;
--
CREATE TABLE {pref}_{mod}_order (
  `oid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `price` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `delivprice` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `payid` int(11) unsigned NOT NULL DEFAULT '0',
  `delid` int(11) unsigned NOT NULL DEFAULT '0',
  `statusid` int(11) unsigned NOT NULL DEFAULT '0',
  `countryid` int(11) unsigned NOT NULL DEFAULT '0',
  `regionid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `firstname` varchar(32) NOT NULL,
  `surname` varchar(32) NOT NULL,
  `city` varchar(64) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `adress` text NOT NULL,
  `comment` text NOT NULL,
  `orders` text NOT NULL,
  `delive` text NOT NULL,
  PRIMARY KEY (`oid`),
  KEY `userid` (`userid`),
  KEY `public` (`public`),
  KEY `statusid` (`statusid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_payment;
--
CREATE TABLE {pref}_{mod}_payment (
  `payid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payext` varchar(20) NOT NULL,
  `paytitle` varchar(255) NOT NULL,
  `paydescr` text NOT NULL,
  `payicon` varchar(255) NOT NULL,
  `paydata` text NOT NULL,
  `paystatus` smallint(2) NOT NULL DEFAULT '0',
  `payposit` smallint(2) NOT NULL DEFAULT '0',
  `payact` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`payid`),
  KEY `payposit` (`payposit`),
  KEY `payact` (`payact`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_product_option;
--
CREATE TABLE {pref}_{mod}_product_option (
  `poid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) unsigned NOT NULL,
  `oid` int(11) unsigned NOT NULL,
  `vid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`poid`),
  KEY `oid` (`oid`),
  KEY `vid` (`vid`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_search;
--
CREATE TABLE {pref}_{mod}_search (
  `seaid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) unsigned NOT NULL DEFAULT '0',
  `seaart` varchar(255) NOT NULL DEFAULT '',
  `seaword` varchar(255) NOT NULL DEFAULT '',
  `seamin` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `seamax` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `seamaker` int(11) unsigned NOT NULL DEFAULT '0',
  `seaopt` text NOT NULL,
  `seaip` varchar(255) NOT NULL DEFAULT '',
  `seatime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`seaid`),
  KEY `seaip` (`seaip`),
  KEY `seatime` (`seatime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_tag;
--
CREATE TABLE {pref}_{mod}_tag (
  `tagid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tagcpu` varchar(255) NOT NULL DEFAULT '',
  `tagword` varchar(255) NOT NULL DEFAULT '',
  `tagdesc` text NOT NULL,
  `custom` text NOT NULL,
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT '',
  `tagrating` smallint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tagid`),
  KEY `tagrating` (`tagrating`),
  KEY `tagcpu` (`tagcpu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;