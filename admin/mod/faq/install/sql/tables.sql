DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `spublic` int(11) unsigned NOT NULL DEFAULT '0',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `quest` text NOT NULL,
  `answer` text NOT NULL,
  `act` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `act` (`act`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_cat;
--
CREATE TABLE {pref}_{mod}_cat (
  `catid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
  `catcpu` varchar(255) NOT NULL DEFAULT '',
  `catmail` varchar(255) NOT NULL DEFAULT '',
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
  `total` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`catid`),
  KEY `parentid` (`parentid`),
  KEY `catcpu` (`catcpu`),
  KEY `access` (`access`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_new;
--
CREATE TABLE {pref}_{mod}_new (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) NOT NULL DEFAULT '0',
  `author` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `quest` varchar(255) NOT NULL DEFAULT '',
  `answer` text NOT NULL,
  `fip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `public` (`public`),
  KEY `fip` (`fip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;