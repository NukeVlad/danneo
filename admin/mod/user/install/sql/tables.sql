DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE IF NOT EXISTS {pref}_{mod} (
  `userid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL DEFAULT '0',
  `uname` varchar(50) NOT NULL DEFAULT '',
  `upass` varchar(32) NOT NULL DEFAULT '',
  `umail` varchar(50) NOT NULL DEFAULT '',
  `regdate` int(11) unsigned NOT NULL DEFAULT '0',
  `lastvisit` int(11) unsigned NOT NULL DEFAULT '0',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `www` varchar(250) NOT NULL DEFAULT '',
  `skype` varchar(50) NOT NULL DEFAULT '',
  `newpass` varchar(32) NOT NULL DEFAULT '',
  `activate` varchar(11) NOT NULL DEFAULT '0',
  `active` int(1) unsigned NOT NULL DEFAULT '0',
  `blocked` int(1) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `userfield` text NOT NULL,
  `countryid` int(11) unsigned NOT NULL DEFAULT '0',
  `regionid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `username` (`uname`),
  KEY `active` (`active`),
  KEY `phone` (`phone`),
  KEY `city` (`city`),
  KEY `blocked` (`blocked`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_field;
--
CREATE TABLE IF NOT EXISTS {pref}_{mod}_field (  
  `fieldid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fieldtype` varchar(10) NOT NULL DEFAULT '',
  `fieldname` varchar(10) NOT NULL DEFAULT '',
  `fieldlist` text NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `requires` enum('yes','no') NOT NULL DEFAULT 'yes',
  `method` varchar(10) NOT NULL DEFAULT '',
  `minlen` int(1) unsigned NOT NULL DEFAULT '3',
  `maxlen` int(3) unsigned NOT NULL DEFAULT '255',
  `act` enum('no','yes') NOT NULL DEFAULT 'no',
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  `profile` enum('no','yes') NOT NULL DEFAULT 'no',
  `registr` enum('no','yes') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`fieldid`),
  KEY `act` (`act`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_group;
--
CREATE TABLE IF NOT EXISTS {pref}_{mod}_group (
  `gid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `def` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
