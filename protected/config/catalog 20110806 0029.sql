-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.58-1~dotdeb.1-log


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema catalog
--

CREATE DATABASE IF NOT EXISTS catalog;
USE catalog;

--
-- Definition of table `category`
--
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentid` int(11) NOT NULL,
  `catname` text NOT NULL,
  `alias` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parentid_IDX` (`parentid`),
  KEY `catname_IDX` (`catname`(16)),
  KEY `alias_IDX` (`alias`)
) ENGINE=MyISAM AUTO_INCREMENT=311 DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Definition of table `comments`
--
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linkid` int(11) DEFAULT NULL,
  `text` mediumtext NOT NULL,
  `user` text NOT NULL,
  `userid` int(11) NOT NULL,
  `ip` text NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `linkid_IDX` (`linkid`)
) ENGINE=MyISAM AUTO_INCREMENT=592 DEFAULT CHARSET=utf8 PACK_KEYS=0;


--
-- Definition of table `email_change`
--
CREATE TABLE `email_change` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_IDX` (`hash`),
  KEY `user_IDX` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `email_change` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_change` ENABLE KEYS */;


--
-- Definition of table `favs`
--
CREATE TABLE `favs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `desc` mediumtext NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_IDX` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

--
-- Definition of table `links`
--
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `catid` int(11) NOT NULL,
  `title` text,
  `desc` mediumtext NOT NULL,
  `foto` varchar(255) NOT NULL,
  `userid` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `ip` text NOT NULL,
  `rate` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `pr` int(2) NOT NULL COMMENT 'Google PR',
  `ci` int(15) NOT NULL COMMENT 'Яндекс ТИЦ',
  `pr_lastdate` int(15) NOT NULL DEFAULT '0',
  `ci_lastdate` int(15) NOT NULL DEFAULT '0',
  `date_ts` int(10) unsigned NOT NULL,
  `domain` varchar(255) NOT NULL,
  `broken` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid_IDX` (`catid`),
  KEY `visible_IDX` (`visible`),
  KEY `url_IDX` (`url`(16)),
  KEY `domain_IDX` (`domain`),
  KEY `broken_IDX` (`broken`)
) ENGINE=MyISAM AUTO_INCREMENT=5869 DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Definition of table `links_favs`
--
CREATE TABLE `links_favs` (
  `fav_id` int(10) unsigned NOT NULL,
  `link_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fav_id`,`link_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `profile`
--
CREATE TABLE `profile` (
  `user_id` int(10) unsigned NOT NULL,
  `birthday` varchar(10) NOT NULL,
  `site` varchar(255) NOT NULL,
  `skype` varchar(100) NOT NULL,
  `icq` varchar(15) NOT NULL,
  `from` varchar(255) NOT NULL,
  `avatar` varchar(45) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `users`
--
CREATE TABLE `users` (
  `userID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8;

--
-- Definition of table `votes`
--
CREATE TABLE `votes` (
  `link_id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vote` enum('1','-1') NOT NULL,
  KEY `linkid_IDX` (`link_id`,`ip`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
