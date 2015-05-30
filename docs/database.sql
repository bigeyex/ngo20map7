/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50616
 Source Host           : localhost
 Source Database       : ngo20map7

 Target Server Type    : MySQL
 Target Server Version : 50616
 File Encoding         : utf-8

 Date: 09/28/2014 21:32:11 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `account`
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `url_name` varchar(20) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `api_vendor` varchar(20) DEFAULT NULL,
  `api_id` varchar(100) DEFAULT NULL,
  `api_token` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `enabled` smallint(6) NOT NULL DEFAULT '1',
  `is_admin` smallint(6) NOT NULL DEFAULT '0',
  `roles` varchar(200) DEFAULT NULL,
  `image` varchar(300) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(35) DEFAULT NULL,
  `login_count` int(11) DEFAULT NULL,
  `api_weibo_id` varchar(100) NOT NULL,
  `api_weibo_token` varchar(100) NOT NULL,
  `api_qq_id` varchar(100) NOT NULL,
  `api_qq_token` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6269 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `account_user`
-- ----------------------------
DROP TABLE IF EXISTS `account_user`;
CREATE TABLE `account_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_checked` smallint(6) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `event`
-- ----------------------------
DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `intro` text,
  `begin_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `work_field` varchar(100) DEFAULT NULL,
  `progress` int(11) DEFAULT '0',
  `origin` varchar(500) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `is_commentable` tinyint(4) DEFAULT '1',
  `is_checked` tinyint(4) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `host` varchar(200) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT '1',
  `tag_id` varchar(200) DEFAULT NULL,
  `res_tags` varchar(50) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `outcome` text,
  `req_description` text,
  `contact_name` text,
  `contact_phone` text,
  `contact_email` text,
  `contact_qq` text,
  `mailed` tinyint(4) NOT NULL DEFAULT '0',
  `cover_img` varchar(255) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `like_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6389 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `event_location`
-- ----------------------------
DROP TABLE IF EXISTS `event_location`;
CREATE TABLE `event_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `longitude` double NOT NULL,
  `latitude` double NOT NULL,
  `province` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3805 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `follow`
-- ----------------------------
DROP TABLE IF EXISTS `follow`;
CREATE TABLE `follow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'user',
  `extra` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=264 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `forget_password`
-- ----------------------------
DROP TABLE IF EXISTS `forget_password`;
CREATE TABLE `forget_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `link` varchar(50) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `local_content`
-- ----------------------------
DROP TABLE IF EXISTS `local_content`;
CREATE TABLE `local_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `key` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `is_checked` smallint(6) NOT NULL DEFAULT '0',
  `users_id` int(11) NOT NULL DEFAULT '0',
  `sortkey` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `local_map`
-- ----------------------------
DROP TABLE IF EXISTS `local_map`;
CREATE TABLE `local_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `config` text,
  `enabled` smallint(6) NOT NULL DEFAULT '1',
  `province` varchar(127) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `local_modules`
-- ----------------------------
DROP TABLE IF EXISTS `local_modules`;
CREATE TABLE `local_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) NOT NULL,
  `type` varchar(127) NOT NULL DEFAULT 'post',
  `local_id` int(11) NOT NULL,
  `sortkey` int(11) NOT NULL DEFAULT '0',
  `extra` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `medal`
-- ----------------------------
DROP TABLE IF EXISTS `medal`;
CREATE TABLE `medal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `score` int(11) DEFAULT '0',
  `image` varchar(100) DEFAULT NULL,
  `image_gray` varchar(100) DEFAULT NULL,
  `description` text,
  `type` varchar(10) NOT NULL DEFAULT 'user',
  `extra` text,
  `code_name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `medalmap`
-- ----------------------------
DROP TABLE IF EXISTS `medalmap`;
CREATE TABLE `medalmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `medal_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=499 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
--  Table structure for `media`
-- ----------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `url2` varchar(500) NOT NULL,
  `type` varchar(20) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=813 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `messages`
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `content` text,
  `create_time` datetime NOT NULL,
  `is_read` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `news`
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `url` varchar(250) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `type` varchar(10) DEFAULT 'news',
  `swffile` varchar(250) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `oauth_client`
-- ----------------------------
DROP TABLE IF EXISTS `oauth_client`;
CREATE TABLE `oauth_client` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `client_secret` varchar(32) NOT NULL,
  `redirect_uri` varchar(200) NOT NULL,
  `create_time` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `oauth_code`
-- ----------------------------
DROP TABLE IF EXISTS `oauth_code`;
CREATE TABLE `oauth_code` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `code` varchar(40) NOT NULL,
  `redirect_uri` varchar(200) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `oauth_token`
-- ----------------------------
DROP TABLE IF EXISTS `oauth_token`;
CREATE TABLE `oauth_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `access_token` varchar(40) NOT NULL,
  `refresh_token` varchar(40) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `related_links`
-- ----------------------------
DROP TABLE IF EXISTS `related_links`;
CREATE TABLE `related_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=296 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `related_ngos`
-- ----------------------------
DROP TABLE IF EXISTS `related_ngos`;
CREATE TABLE `related_ngos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `related_user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `type` varchar(63) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `reviews`
-- ----------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_checked` tinyint(4) NOT NULL DEFAULT '1',
  `create_time` datetime NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `settings`
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `k` varchar(255) NOT NULL,
  `v` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `tagmap`
-- ----------------------------
DROP TABLE IF EXISTS `tagmap`;
CREATE TABLE `tagmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4452 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
--  Table structure for `tags`
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL,
  `change_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1396 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `english_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `intro` text,
  `image` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `aim` text,
  `work_field` varchar(200) DEFAULT NULL,
  `register_year` varchar(10) DEFAULT NULL,
  `service_area` varchar(100) DEFAULT NULL,
  `staff_fulltime` int(11) DEFAULT NULL,
  `staff_parttime` int(11) DEFAULT NULL,
  `staff_volunteer` int(11) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `public_email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `longitude` double(20,6) DEFAULT NULL,
  `latitude` double(20,6) DEFAULT NULL,
  `province` varchar(20) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `county` varchar(20) DEFAULT NULL,
  `place` text,
  `is_admin` tinyint(4) DEFAULT '0',
  `is_checked` tinyint(4) DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `last_login_ip` varchar(35) DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT '1',
  `past_projects` text,
  `fax` varchar(20) DEFAULT NULL,
  `contact_name` varchar(30) DEFAULT NULL,
  `post_code` varchar(20) DEFAULT NULL,
  `media_link` varchar(200) DEFAULT NULL,
  `weibo_provider` varchar(20) DEFAULT NULL,
  `weibo` varchar(50) DEFAULT NULL,
  `is_vip` tinyint(4) DEFAULT '0',
  `is_blocked` tinyint(4) DEFAULT '0',
  `fund_source` text,
  `login_count` int(11) NOT NULL DEFAULT '0',
  `expertise` varchar(200) DEFAULT NULL,
  `register_month` char(4) DEFAULT NULL,
  `documented_year` char(4) DEFAULT NULL,
  `documented_month` char(4) DEFAULT NULL,
  `register_type` char(20) DEFAULT NULL,
  `financial_link` text,
  `medal_score` int(11) DEFAULT '0',
  `medals` text,
  `sina_weibo_uid` varchar(20) DEFAULT NULL,
  `cover_img` varchar(255) DEFAULT NULL,
  `like_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19014 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `weibo`
-- ----------------------------
DROP TABLE IF EXISTS `weibo`;
CREATE TABLE `weibo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` varchar(30) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `weibo_name` varchar(127) DEFAULT NULL,
  `provider` varchar(20) DEFAULT NULL,
  `content` text,
  `lon` varchar(20) DEFAULT NULL,
  `lat` varchar(20) DEFAULT NULL,
  `avatar_img` varchar(127) DEFAULT NULL,
  `image` varchar(127) DEFAULT NULL,
  `retweet_count` int(11) DEFAULT NULL,
  `comment_count` int(11) DEFAULT NULL,
  `post_time` datetime DEFAULT NULL,
  `source` text,
  `uid` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10747 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender` varchar(255) DEFAULT NULL,
  `content` text,
  `reply` text,
  `is_visible` smallint(6) DEFAULT '0',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Procedure structure for `mig`
-- ----------------------------
DROP PROCEDURE IF EXISTS `mig`;
delimiter ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `mig`()
BEGIN
declare Done int default 0;
declare u_id int(11);
declare u_name varchar(100);
declare u_password varchar(50);
declare u_api_vendor varchar(20);
declare u_api_id varchar(100);
declare u_email varchar(100);
declare u_enabled smallint(6);
declare u_is_admin smallint(6);
declare u_image varchar(300);

declare rs cursor for select id,name,password,api_vendor,api_id,email,enabled,is_admin,image from users;
DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET Done = 1;

open rs;
fetch next from rs into u_id, u_name, u_password, u_api_vendor, u_api_id, u_email, u_enabled, u_is_admin, u_image;

while Done <> 1 do
    insert into accounts(name, password, api_vendor, api_id, email, enabled, is_admin, image)
        values (u_name, u_password, u_api_vendor, u_api_id, u_email, u_enabled, u_is_admin, u_image);
    update users set account_id=LAST_INSERT_ID() where id=u_id;
    fetch next from rs into u_id, u_name, u_password, u_api_vendor, u_api_id, u_email, u_enabled, u_is_admin, u_image;
end WHILE;

close rs;

END
 ;;
delimiter ;

-- ----------------------------
--  Procedure structure for `swap_lat_lon`
-- ----------------------------
DROP PROCEDURE IF EXISTS `swap_lat_lon`;
delimiter ;;
CREATE DEFINER=`root`@`%` PROCEDURE `swap_lat_lon`()
begin
    declare event_id int;
    declare lon varchar(200);
    declare lat varchar(200);
    declare stop int default 0;
    declare cur cursor for select id,longitude,latitude from `events` where latitude<longitude;
    declare CONTINUE HANDLER FOR SQLSTATE '02000' SET stop=1;

    open cur;

    
    while stop <> 1 DO
        fetch cur into event_id,lon,lat;
        update `events` set longitude=lat,latitude=lon where id=event_id;
    end while;

    close cur;
end
 ;;
delimiter ;

