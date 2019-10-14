DROP TABLE IF EXISTS {pref}_{mod};
--
CREATE TABLE {pref}_{mod} (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `textshort` text NOT NULL,
  `textmore` longtext NOT NULL,
  `images` text NOT NULL,
  `textnotice` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_thumb` varchar(255) NOT NULL DEFAULT '',
  `image_align` enum('left','right') NOT NULL DEFAULT 'left',
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;