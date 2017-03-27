/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 100113
Source Host           : localhost:3306
Source Database       : reactor

Target Server Type    : MYSQL
Target Server Version : 100113
File Encoding         : 65001

Date: 2017-03-27 16:57:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for blacklist
-- ----------------------------
DROP TABLE IF EXISTS `blacklist`;
CREATE TABLE `blacklist` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `useragent` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for namemap
-- ----------------------------
DROP TABLE IF EXISTS `namemap`;
CREATE TABLE `namemap` (
  `info_hash` char(40) NOT NULL DEFAULT '',
  `filename` varchar(250) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL,
  `pubDate` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`info_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for speedlimit
-- ----------------------------
DROP TABLE IF EXISTS `speedlimit`;
CREATE TABLE `speedlimit` (
  `uploaded` bigint(25) NOT NULL DEFAULT '0',
  `total_uploaded` bigint(30) NOT NULL DEFAULT '0',
  `started` bigint(25) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for summary
-- ----------------------------
DROP TABLE IF EXISTS `summary`;
CREATE TABLE `summary` (
  `info_hash` char(40) NOT NULL DEFAULT '',
  `dlbytes` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeds` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `finished` int(10) unsigned NOT NULL DEFAULT '0',
  `lastcycle` int(10) unsigned NOT NULL DEFAULT '0',
  `lastSpeedCycle` int(10) unsigned NOT NULL DEFAULT '0',
  `speed` bigint(20) unsigned NOT NULL DEFAULT '0',
  `piecelength` int(11) NOT NULL DEFAULT '-1',
  `numpieces` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`info_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for timestamps
-- ----------------------------
DROP TABLE IF EXISTS `timestamps`;
CREATE TABLE `timestamps` (
  `info_hash` char(40) NOT NULL,
  `sequence` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bytes` bigint(20) unsigned NOT NULL,
  `delta` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`sequence`),
  KEY `sorting` (`info_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `rank` int(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for webseedfiles
-- ----------------------------
DROP TABLE IF EXISTS `webseedfiles`;
CREATE TABLE `webseedfiles` (
  `info_hash` char(40) DEFAULT NULL,
  `filename` char(250) NOT NULL DEFAULT '',
  `startpiece` int(11) NOT NULL DEFAULT '0',
  `endpiece` int(11) NOT NULL DEFAULT '0',
  `startpieceoffset` int(11) NOT NULL DEFAULT '0',
  `fileorder` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `fileseq` (`info_hash`,`fileorder`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
