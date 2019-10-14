
--
-- Структура таблицы '{pref}_admin'
--

DROP TABLE IF EXISTS {pref}_admin;
CREATE TABLE {pref}_admin (
  `admid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `adlog` varchar(25) NOT NULL DEFAULT '',
  `adpwd` varchar(32) DEFAULT NULL,
  `admail` varchar(50) NOT NULL DEFAULT '',
  `adlast` int(11) NOT NULL DEFAULT '0',
  `permiss` text NOT NULL,
  `blocked` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`admid`),
  KEY `adlog` (`adlog`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_admin_sess'
--

DROP TABLE IF EXISTS {pref}_admin_sess;
CREATE TABLE {pref}_admin_sess (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `admid` int(11) NOT NULL DEFAULT '0',
  `ipadd` varchar(16) NOT NULL DEFAULT '',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `lastactivity` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_banners'
--

DROP TABLE IF EXISTS {pref}_banners;
CREATE TABLE {pref}_banners (
  `banid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bantype` enum('code','click') DEFAULT NULL,
  `banurl` varchar(255) NOT NULL DEFAULT '',
  `bancode` text,
  `bantitle` varchar(255) NOT NULL DEFAULT '',
  `banimg` varchar(255) NOT NULL DEFAULT '',
  `banlimit` int(11) unsigned NOT NULL DEFAULT '0',
  `banview` int(11) unsigned NOT NULL DEFAULT '0',
  `banclick` int(11) unsigned NOT NULL DEFAULT '0',
  `banmods` text NOT NULL,
  `banzonid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`banid`),
  KEY `banlimit` (`banlimit`),
  KEY `banview` (`banview`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_banners_zone'
--

DROP TABLE IF EXISTS {pref}_banners_zone;
CREATE TABLE {pref}_banners_zone (
  `banzonid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `banzoncode` varchar(255) NOT NULL DEFAULT '',
  `banzonname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`banzonid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_block'
--

DROP TABLE IF EXISTS {pref}_block;
CREATE TABLE {pref}_block (
  `blockid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `positid` int(11) unsigned NOT NULL DEFAULT '0',
  `block_side` varchar(255) NOT NULL DEFAULT '',
  `block_file` varchar(100) NOT NULL DEFAULT '',
  `block_name` varchar(80) NOT NULL DEFAULT '',
  `block_cont` text NOT NULL,
  `block_active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `block_posit` int(11) unsigned NOT NULL DEFAULT '0',
  `block_temp` varchar(255) NOT NULL DEFAULT '',
  `block_mods` text NOT NULL,
  `block_access` enum('all','user') NOT NULL DEFAULT 'all',
  `block_setting` text NOT NULL,
  `block_group` text NOT NULL,
  PRIMARY KEY (`blockid`),
  KEY `block_active` (`block_active`),
  KEY `block_posit` (`block_posit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_block_posit'
--

DROP TABLE IF EXISTS {pref}_block_posit;
CREATE TABLE {pref}_block_posit (
  `positid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `positcode` varchar(255) NOT NULL DEFAULT '',
  `positname` varchar(255) NOT NULL DEFAULT '',
  `pposit` smallint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`positid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_captcha'
--

DROP TABLE IF EXISTS {pref}_captcha;
CREATE TABLE {pref}_captcha (
  `captchid` int(11) NOT NULL AUTO_INCREMENT,
  `captchip` varchar(255) NOT NULL DEFAULT '',
  `captchcode` varchar(10) NOT NULL DEFAULT '',
  `captchtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`captchid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_comment'
--

DROP TABLE IF EXISTS {pref}_comment;
CREATE TABLE {pref}_comment (
  `comid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file` char(25) NOT NULL,
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  `cname` varchar(50) NOT NULL DEFAULT '',
  `ctitle` varchar(255) NOT NULL DEFAULT '',
  `ctext` text NOT NULL,
  `cip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`comid`),
  KEY `file` (`file`),
  KEY `ctime` (`ctime`),
  KEY `cip` (`cip`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_control'
--

DROP TABLE IF EXISTS {pref}_control;
CREATE TABLE {pref}_control (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `issue` text NOT NULL,
  `response` text NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_country'
--

DROP TABLE IF EXISTS {pref}_country;
CREATE TABLE {pref}_country (
  `countryid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `countryname` varchar(128) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `iso2` char(2) NOT NULL,
  `iso3` char(3) NOT NULL,
  `iso` int(11) NOT NULL DEFAULT '0',
  `posit` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`countryid`),
  KEY `iso` (`iso`),
  KEY `iso2` (`iso2`),
  KEY `iso3` (`iso3`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_country_region'
--

DROP TABLE IF EXISTS {pref}_country_region;
CREATE TABLE {pref}_country_region (
  `regionid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `countryid` int(11) unsigned NOT NULL DEFAULT '0',
  `regionname` varchar(64) NOT NULL DEFAULT '',
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`regionid`),
  KEY `countryid` (`countryid`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_language'
--

DROP TABLE IF EXISTS {pref}_language;
CREATE TABLE {pref}_language (
  `langid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `langpackid` int(2) unsigned NOT NULL DEFAULT '1',
  `langsetid` int(5) unsigned NOT NULL DEFAULT '0',
  `langvars` varchar(35) NOT NULL DEFAULT '',
  `langvals` text NOT NULL,
  `langvalsold` text NOT NULL,
  `langcache` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`langid`),
  KEY `langpackid` (`langpackid`),
  KEY `langcache` (`langcache`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_language_pack'
--

DROP TABLE IF EXISTS {pref}_language_pack;
CREATE TABLE {pref}_language_pack (
  `langpackid` int(2) NOT NULL AUTO_INCREMENT,
  `langpack` varchar(100) NOT NULL DEFAULT '',
  `langcode` varchar(4) NOT NULL DEFAULT '',
  `langcharset` varchar(25) NOT NULL DEFAULT '',
  `langdateset` int(11) unsigned NOT NULL DEFAULT '0',
  `langloginset` int(11) unsigned NOT NULL DEFAULT '0',
  `langauthor` text NOT NULL,
  PRIMARY KEY (`langpackid`),
  KEY `langcharset` (`langcharset`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_language_setting'
--

DROP TABLE IF EXISTS {pref}_language_setting;
CREATE TABLE {pref}_language_setting (
  `langsetid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `langpackid` int(11) unsigned NOT NULL DEFAULT '0',
  `langsetname` varchar(80) NOT NULL DEFAULT '',
  `langsetmd5` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`langsetid`),
  KEY `langpackid` (`langpackid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_mods'
--

DROP TABLE IF EXISTS {pref}_mods;
CREATE TABLE {pref}_mods (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file` char(25) NOT NULL DEFAULT 'Default',
  `name` varchar(255) NOT NULL DEFAULT '',
  `custom` text NOT NULL,
  `keywords` text NOT NULL,
  `descript` text NOT NULL,
  `map` text NOT NULL,
  `temp` char(25) NOT NULL DEFAULT 'Lite',
  `posit` int(11) unsigned NOT NULL DEFAULT '0',
  `label` text NOT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'no',
  `actmap` enum('yes','no') NOT NULL DEFAULT 'yes',
  `parent` int(11) unsigned NOT NULL DEFAULT '0',
  `linking` enum('yes','no') NOT NULL DEFAULT 'no',
  `sitemap` enum('yes','no') NOT NULL DEFAULT 'no',
  `langsetid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `file` (`file`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_mods_filter'
--

DROP TABLE IF EXISTS {pref}_mods_filter;
CREATE TABLE {pref}_mods_filter (
  `fid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start` int(11) unsigned NOT NULL,
  `filter` text NOT NULL,
  PRIMARY KEY (`fid`),
  KEY `start` (`start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_rating'
--

DROP TABLE IF EXISTS {pref}_rating;
CREATE TABLE {pref}_rating (
  `ratingid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file` char(25) NOT NULL DEFAULT '',
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `ratingip` varchar(255) NOT NULL DEFAULT '',
  `ratingtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ratingid`),
  KEY `ratingip` (`ratingip`),
  KEY `ratingtime` (`ratingtime`),
  KEY `file` (`file`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_reviews'
--

DROP TABLE IF EXISTS {pref}_reviews;
CREATE TABLE {pref}_reviews (
  `reid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file` char(25) NOT NULL DEFAULT '',
  `pageid` int(11) unsigned NOT NULL DEFAULT '0',
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `public` int(11) unsigned NOT NULL DEFAULT '0',
  `uname` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `region` varchar(64) NOT NULL,
  `active` int(1) unsigned NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reid`),
  KEY `public` (`public`),
  KEY `file` (`file`),
  KEY `id` (`pageid`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_seo_anchor'
--

DROP TABLE IF EXISTS {pref}_seo_anchor;
CREATE TABLE {pref}_seo_anchor (
  `said` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mods` char(25) NOT NULL DEFAULT '',
  `count` smallint(1) unsigned NOT NULL DEFAULT '1',
  `word` text NOT NULL,
  `link` text NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY (`said`),
  KEY `mods` (`mods`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_settings'
--

DROP TABLE IF EXISTS {pref}_settings;
CREATE TABLE {pref}_settings (
  `setid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setopt` varchar(255) NOT NULL DEFAULT '',
  `setname` varchar(255) NOT NULL DEFAULT '',
  `setval` mediumtext NOT NULL,
  `setmark` int(1) unsigned NOT NULL DEFAULT '0',
  `setlang` varchar(255) NOT NULL DEFAULT '',
  `setcode` mediumtext NOT NULL,
  `setvalid` mediumtext NOT NULL,
  PRIMARY KEY (`setid`),
  FULLTEXT KEY `setname` (`setname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_site_menu'
--

DROP TABLE IF EXISTS {pref}_site_menu;
CREATE TABLE {pref}_site_menu (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `code` char(25) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `posit` smallint(3) unsigned NOT NULL DEFAULT '0',
  `css` char(25) NOT NULL DEFAULT '',
  `target` enum('_self','_blank') NOT NULL DEFAULT '_self',
  `total` smallint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `posit` (`posit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы '{pref}_smilie'
--

DROP TABLE IF EXISTS {pref}_smilie;
CREATE TABLE {pref}_smilie (
  `smid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `smcode` varchar(10) NOT NULL DEFAULT '',
  `smalt` varchar(100) NOT NULL DEFAULT '',
  `smimg` varchar(100) NOT NULL DEFAULT '',
  `posit` mediumint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`smid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;