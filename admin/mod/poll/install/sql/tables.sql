DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `act` enum('yes','no') NOT NULL DEFAULT 'no',
  `start` int(11) unsigned NOT NULL DEFAULT '0',
  `finish` int(11) unsigned NOT NULL DEFAULT '0',
  `acc` enum('all','user') NOT NULL DEFAULT 'all',
  `groups` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `customs` varchar(255) NOT NULL DEFAULT '',
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `decs` text NOT NULL,
  `ajax` enum('no','yes') NOT NULL DEFAULT 'no',
  `comments` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `act` (`act`),
  KEY `start` (`start`),
  KEY `finish` (`finish`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_vals;
--
CREATE TABLE {pref}_{mod}_vals (
  `valsid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `vals_title` text NOT NULL,
  `vals_voices` int(11) unsigned NOT NULL DEFAULT '0',
  `vals_color` varchar(6) NOT NULL DEFAULT '',
  `posit` smallint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`valsid`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--

DROP TABLE IF EXISTS {pref}_{mod}_vote;
--
CREATE TABLE {pref}_{mod}_vote (
  `voteid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `votedate` int(11) unsigned NOT NULL DEFAULT '0',
  `voteip` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`voteid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;