DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `stpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `unpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `customs` text NOT NULL,
  `file` text NOT NULL,
  `size` varchar(255) NOT NULL DEFAULT '',
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `textshort` text NOT NULL,
  `textmore` longtext NOT NULL,
  `textnotice` text NOT NULL,
  `mirrors` text NOT NULL,
  `relis` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `site` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL,
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_align` enum('left','right') NOT NULL DEFAULT 'left',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `trans` int(11) unsigned NOT NULL DEFAULT '0',
  `lastdown` int(11) unsigned NOT NULL DEFAULT '0',
  `rating` int(11) unsigned NOT NULL DEFAULT '0',
  `totalrating` int(11) unsigned NOT NULL DEFAULT '0',
  `act` enum('yes','no') NOT NULL DEFAULT 'yes',
  `acc` enum('all','user') NOT NULL DEFAULT 'all',
  `groups` text NOT NULL,
  `listid` int(11) unsigned NOT NULL DEFAULT '0',
  `comments` int(11) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `images` text NOT NULL,
  `imp` smallint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `public` (`public`),
  KEY `stpublic` (`stpublic`),
  KEY `unpublic` (`unpublic`)
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
  `sort` varchar(11) NOT NULL DEFAULT 'downid',
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

DROP TABLE IF EXISTS {pref}_{mod}_broken;
--
CREATE TABLE {pref}_{mod}_broken (
  `brokid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `brokip` varchar(255) NOT NULL DEFAULT '',
  `broktime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`brokid`),
  KEY `brokip` (`brokip`),
  KEY `broktime` (`broktime`),
  KEY `id` (`id`)
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

DROP TABLE IF EXISTS {pref}_{mod}_sess;
--
CREATE TABLE {pref}_{mod}_sess (
  `sessid` varchar(32) NOT NULL DEFAULT '',
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `sessip` varchar(255) NOT NULL DEFAULT '',
  `sesstime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessid`)
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
  `textshort` text NOT NULL,
  `textmore` longtext NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;