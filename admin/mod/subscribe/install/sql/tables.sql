DROP TABLE IF EXISTS {pref}_{mod}_archive;
--
CREATE TABLE {pref}_{mod}_archive (
  `archivid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `formats` int(1) unsigned NOT NULL DEFAULT '0',
  `ignores` enum('no','yes') NOT NULL DEFAULT 'no',
  `status` enum('un','finish') NOT NULL DEFAULT 'un',
  `send` int(11) unsigned NOT NULL DEFAULT '0',
  `total` int(11) unsigned NOT NULL DEFAULT '0',
  `step` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`archivid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_users;
--
CREATE TABLE {pref}_{mod}_users (
  `subuserid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subname` varchar(255) NOT NULL DEFAULT '',
  `submail` varchar(255) NOT NULL DEFAULT '',
  `subformat` int(1) unsigned NOT NULL DEFAULT '0',
  `subcode` varchar(11) NOT NULL DEFAULT '',
  `subactive` int(1) unsigned NOT NULL DEFAULT '0',
  `regtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`subuserid`),
  KEY `subcode` (`subcode`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;