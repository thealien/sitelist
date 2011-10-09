-- phpMyAdmin SQL Dump
-- version 3.4.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 09 2011 г., 11:11
-- Версия сервера: 5.1.58
-- Версия PHP: 5.3.8-1~dotdeb.2

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `catalog`
--

-- --------------------------------------------------------

--
-- Структура таблицы `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catname` text NOT NULL,
  `alias` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catname_IDX` (`catname`(16)),
  KEY `alias_IDX` (`alias`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=311 ;

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linkid` int(11) DEFAULT NULL,
  `text` mediumtext NOT NULL,
  `user` text NOT NULL,
  `userid` int(11) NOT NULL,
  `ip` text NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `linkid_IDX` (`linkid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=608 ;

-- --------------------------------------------------------

--
-- Структура таблицы `email_change`
--

DROP TABLE IF EXISTS `email_change`;
CREATE TABLE IF NOT EXISTS `email_change` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_IDX` (`hash`),
  KEY `user_IDX` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `favs`
--

DROP TABLE IF EXISTS `favs`;
CREATE TABLE IF NOT EXISTS `favs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `desc` mediumtext NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_IDX` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Структура таблицы `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=6306 ;

-- --------------------------------------------------------

--
-- Структура таблицы `links_favs`
--

DROP TABLE IF EXISTS `links_favs`;
CREATE TABLE IF NOT EXISTS `links_favs` (
  `fav_id` int(10) unsigned NOT NULL,
  `link_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fav_id`,`link_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `user_id` int(10) unsigned NOT NULL,
  `birthday` varchar(10) NOT NULL,
  `site` varchar(255) NOT NULL,
  `skype` varchar(100) NOT NULL,
  `icq` varchar(15) NOT NULL,
  `from` varchar(255) NOT NULL,
  `avatar` varchar(45) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=98 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_openid`
--

DROP TABLE IF EXISTS `users_openid`;
CREATE TABLE IF NOT EXISTS `users_openid` (
  `identity` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `link_id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vote` enum('1','-1') NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `linkid_IDX` (`link_id`,`ip`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
