DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE IF NOT EXISTS {pref}_{mod} (
  `paid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mods` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `cpu` varchar(255) NOT NULL DEFAULT '',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `uppublic` int(11) unsigned NOT NULL DEFAULT '0',
  `customs` text NOT NULL,
  `descript` text NOT NULL,
  `keywords` text NOT NULL,
  `textshort` text NOT NULL,
  `textmore` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_align` enum('left','right') NOT NULL DEFAULT 'left',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  `act` enum('yes','no') NOT NULL DEFAULT 'yes',
  `acc` enum('all','user') NOT NULL DEFAULT 'all',
  `groups` text NOT NULL,
  `imp` smallint(1) unsigned NOT NULL,
  `files` text NOT NULL,
  `facc` enum('all','user') NOT NULL DEFAULT 'all',
  `fgroups` text NOT NULL,
  `images` text NOT NULL,
  PRIMARY KEY (`paid`),
  UNIQUE KEY `title` (`title`),
  KEY `cpu` (`cpu`),
  KEY `acc` (`acc`),
  KEY `mods` (`mods`)
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