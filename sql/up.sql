# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.12)
# Database: pixel
# Generation Time: 2014-06-10 15:54:06 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table leads
# ------------------------------------------------------------

CREATE TABLE `leads` (
  `vk_lead_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `caption` varchar(300) NOT NULL DEFAULT '',
  `secret` varchar(65) NOT NULL DEFAULT '',
  PRIMARY KEY (`vk_lead_id`),
  KEY `idx_leads_site` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pixel_log
# ------------------------------------------------------------

CREATE TABLE `pixel_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) unsigned NOT NULL,
  `url_id` bigint(11) unsigned NOT NULL,
  `user_id` varchar(30) NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_group2` (`site_id`,`timestamp`),
  KEY `idx_group3` (`timestamp`),
  KEY `idx_group4` (`user_id`,`timestamp`),
  KEY `idx_group` (`site_id`,`user_id`,`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `session_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vk_sid` varchar(65) NOT NULL DEFAULT '',
  `vk_lead_id` int(10) unsigned NOT NULL,
  `vk_uid` int(11) unsigned NOT NULL,
  `user_id` varchar(30) NOT NULL DEFAULT '',
  `site_id` int(11) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `idx_sessions` (`site_id`,`user_id`),
  KEY `idx_sessions2` (`site_id`,`finished`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table sites
# ------------------------------------------------------------

CREATE TABLE `sites` (
  `site_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL DEFAULT '',
  `request_threshold` int(11) unsigned NOT NULL DEFAULT '4',
  `site_uid` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`site_id`),
  UNIQUE KEY `idx_site_uid` (`site_uid`),
  KEY `idx_site_domain` (`domain`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table urls
# ------------------------------------------------------------

CREATE TABLE `urls` (
  `url_id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_crc` int(10) unsigned NOT NULL,
  `url` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`url_id`),
  KEY `idx_url_crc` (`url_crc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
