DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `stpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `unpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `customs` text NOT NULL,
  `keywords` text NOT NULL,
  `descript` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `act` enum('yes','no') NOT NULL DEFAULT 'yes',
  `rating` int(11) NOT NULL DEFAULT '0',
  `totalrating` int(11) NOT NULL DEFAULT '0',
  `acc` enum('all','user') NOT NULL,
  `groups` text NOT NULL,
  `comments` int(11) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `imp` smallint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cpu` (`cpu`),
  KEY `public` (`public`),
  KEY `catid` (`catid`),
  KEY `act` (`act`),
  KEY `acc` (`acc`)
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
  `total` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`catid`),
  KEY `parentid` (`parentid`),
  KEY `catcpu` (`catcpu`),
  KEY `access` (`access`),
  KEY `rss` (`rss`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_search;
--
CREATE TABLE {pref}_{mod}_search (
  `seaid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `seaword` varchar(255) NOT NULL DEFAULT '',
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

--

DROP TABLE IF EXISTS {pref}_{mod}_user;
--
CREATE TABLE {pref}_{mod}_user (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;