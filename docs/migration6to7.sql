# attach cover img
update users u set cover_img=(select url from media where type='image' and event_id in (select id from events where user_id=u.id) limit 1) where type='ngo' and enabled=1 and is_checked=1

-- rename tables
alter table users rename to `user`;
alter table `events` rename to `event`;
alter table accounts rename to account;

-- drop columns
alter table user drop column `password`;
alter table user drop column api_vendor;
alter table user drop column api_id;

-- add columns
-- alter table `account` add (
--     api_qq_id varchar(50) null,
--     api_qq_token varchar(50) null,
--     api_weibo_id varchar(50) null,
--     api_weibo_token varchar(50) null
-- );
ALTER TABLE `event` ADD COLUMN `account_id` int null;
ALTER TABLE `media` ADD COLUMN `user_id` int null;
ALTER TABLE `account` ADD COLUMN `api_token` varchar(100) AFTER `api_id`

-- update `account` set api_qq_id=api_id where api_vendor='qq';
-- update `account` set api_weibo_id=api_id where api_vendor='weibo';
-- alter table `account` drop column api_id;
-- alter table `account` drop column api_vendor;

ALTER TABLE `event` CHANGE COLUMN `item_field` `work_field` varchar(100) DEFAULT NULL;
ALTER TABLE `user` CHANGE COLUMN `introduction` `intro` text DEFAULT NULL;
ALTER TABLE `event` CHANGE COLUMN `description` `intro` text DEFAULT NULL;
ALTER TABLE `ngo20map7`.`user` CHANGE COLUMN `longitude` `longitude` double(20,6) DEFAULT NULL, CHANGE COLUMN `latitude` `latitude` double(20,6) DEFAULT NULL;

update `event` set type='csr' where type='ind';
alter table `event` add cover_img varchar(255) null;
update `event` e set cover_img=(select url from media where type='image' and event_id=e.id limit 1);
update `event` e set account_id=(select account_id from user where id=e.user_id);
update `media` m set user_id=(select user_id from event where id=m.event_id);


-- deal with new tables
-- ngo collaborator
CREATE TABLE `account_user` (
    `id` int NOT NULL AUTO_INCREMENT,
    `account_id` int NOT NULL,
    `user_id` int NOT NULL,
    `is_checked` smallint DEFAULT '0',
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

-- multi location system
CREATE TABLE `event_location` (
    `id` int NOT NULL AUTO_INCREMENT,
    `longitude` double NOT NULL,
    `latitude` double NOT NULL,
    `province` varchar(40),
    `city` varchar(40),
    `place` varchar(255),
    `event_id` int NOT NULL,
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

insert into event_location (longitude, latitude, province, city, place, event_id)  (select longitude, latitude, province, city, place, id from `event`);
ALTER TABLE `ngo20map7`.`event` DROP COLUMN `longitude`, DROP COLUMN `latitude`, DROP COLUMN `province`, DROP COLUMN `city`, DROP COLUMN `county`, DROP COLUMN `place`;


-- finally: manage ids and tokens of weibo / qq seperately.

ALTER TABLE `account` ADD COLUMN `api_weibo_id` varchar(100) NOT NULL AFTER `login_count`, ADD COLUMN `api_weibo_token` varchar(100) NOT NULL AFTER `api_weibo_id`, ADD COLUMN `api_qq_id` varchar(100) NOT NULL AFTER `api_weibo_token`, ADD COLUMN `api_qq_token` varchar(100) NOT NULL AFTER `api_qq_id`;
update `account` set api_qq_id=api_id where api_vendor = 'qq';
update `account` set api_weibo_id=api_id where api_vendor = 'weibo';
ALTER TABLE `event` ADD COLUMN `like_count` int NOT NULL DEFAULT '0';
ALTER TABLE `user` ADD COLUMN `like_count` int NOT NULL DEFAULT '0';
