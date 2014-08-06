# attach cover img
update users u set cover_img=(select url from media where type='image' and event_id in (select id from events where user_id=u.id) limit 1) where type='ngo' and enabled=1 and is_checked=1

-- rename tables
alter table users rename to `user`;
alter table `events` rename to `event`;
alter table accounts rename to account;

-- drop columns
alter table ngo drop column `password`;
alter table ngo drop column api_vendor;
alter table ngo drop column api_id;

-- add columns
-- alter table `account` add (
--     api_qq_id varchar(50) null,
--     api_qq_token varchar(50) null,
--     api_weibo_id varchar(50) null,
--     api_weibo_token varchar(50) null
-- );
ALTER TABLE `event` ADD COLUMN `account_id` int null;
ALTER TABLE `account` ADD COLUMN `api_token` varchar(100) AFTER `api_id`

-- update `account` set api_qq_id=api_id where api_vendor='qq';
-- update `account` set api_weibo_id=api_id where api_vendor='weibo';
-- alter table `account` drop column api_id;
-- alter table `account` drop column api_vendor;

ALTER TABLE `ngo20map7`.`event` CHANGE COLUMN `item_field` `work_field` varchar(100) DEFAULT NULL;
ALTER TABLE `ngo20map7`.`ngo` CHANGE COLUMN `introduction` `intro` text DEFAULT NULL;
ALTER TABLE `ngo20map7`.`event` CHANGE COLUMN `description` `intro` text DEFAULT NULL;

update `event` set type='csr' where type='ind';
alter table `event` add cover_img varchar(255) null;
update `event` e set cover_img=(select url from media where type='image' and event_id=e.id limit 1);
update `event` e set account_id=(select account_id from user where id=e.user_id);
