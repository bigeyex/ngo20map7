CREATE TABLE `huzhu` (
`id`  int NOT NULL AUTO_INCREMENT ,
`content`  text NULL ,
`user_id`  int NULL ,
`likes`  int NULL DEFAULT 0,
`replies`  int NULL ,
`city`  varchar(255) NULL ,
`category`  varchar(255) NULL ,
`is_open`  smallint NULL ,
`expire_date`  datetime NULL ,
PRIMARY KEY (`id`)
)
;

CREATE TABLE `huzhu_reply` (
`id`  int NOT NULL AUTO_INCREMENT ,
`content`  text NULL ,
`user_id`  int NULL ,
`account_id`  int NULL ,
`huzhu_id`  int NULL ,
PRIMARY KEY (`id`)
)
;

CREATE TABLE `huzhu_unread` (
`id`  int NOT NULL AUTO_INCREMENT ,
`huzhu_id`  int NULL ,
`account_id`  int NULL ,
PRIMARY KEY (`id`)
)
;
