SET NAMES utf8;

DROP TABLE IF EXISTS `rating`;
CREATE TABLE `rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `name` VARCHAR (100) NOT NULL,
  `intro` text,
  `target_areas` SET('医疗卫生健康','女性权利','儿童青少年','教育助学','艾滋病','灾害管理','文化艺术','环境保护','农村发展','城市社区建设','劳工权益','同性恋','政策倡导','信息网络','公益行业支持','社会企业','动物福利','老年人','民间研究机构','企业社会责任','残障人士','综合志愿服务','其它'),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT  CHARSET=utf8 ROW_FORMAT=DYNAMIC;

INSERT INTO rating (`account_id`, `score`, `name`, `intro`, `target_areas`)
SELECT `account_id`, 90 * RAND() + 10, `name`, `intro`, REPLACE(TRIM(work_field)," ", ",") FROM user;