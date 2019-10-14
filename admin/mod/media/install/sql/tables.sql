DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE IF NOT EXISTS {pref}_{mod} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `customs` varchar(255) NOT NULL DEFAULT '',
  `descript` varchar(255) NOT NULL DEFAULT '',
  `keywords` text NOT NULL,
  `text` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  `video` varchar(255) NOT NULL,
  `act` enum('yes','no') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `listid` (`catid`),
  KEY `cpu` (`cpu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_cat;
--
CREATE TABLE IF NOT EXISTS {pref}_{mod}_cat (
  `catid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `stpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `unpublic` int(11) unsigned NOT NULL DEFAULT '0',
  `listname` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `listdesc` text NOT NULL,
  `listcol` int(11) unsigned NOT NULL DEFAULT '0',
  `access` enum('all','user') NOT NULL DEFAULT 'all',
  `catcpu` varchar(255) NOT NULL DEFAULT '',
  `customs` text NOT NULL,
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `listtext` text NOT NULL,
  `posit` int(11) unsigned NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT '',
  `groups` text NOT NULL,
  `act` enum('yes','no') NOT NULL,
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `imp` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`catid`),
  KEY `public` (`public`),
  KEY `stpublic` (`stpublic`),
  KEY `unpublic` (`unpublic`),
  KEY `act` (`act`),
  KEY `imp` (`imp`),
  KEY `catcpu` (`catcpu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;