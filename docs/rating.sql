SET NAMES utf8;

DROP TABLE IF EXISTS `rating`;
CREATE TABLE `rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `name` VARCHAR (100) NOT NULL,
  `province` varchar(20) DEFAULT NULL,
  `city` varchar(20) DEFAULT NULL,
  `target_areas` SET('医疗卫生健康','女性权利','儿童青少年','教育助学','艾滋病','灾害管理','文化艺术','环境保护','农村发展','城市社区建设','劳工权益','同性恋','政策倡导','信息网络','公益行业支持','社会企业','动物福利','老年人','民间研究机构','企业社会责任','残障人士','综合志愿服务','其它'),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT  CHARSET=utf8 ROW_FORMAT=DYNAMIC;


DELIMITER //

CREATE FUNCTION CalcRating(score INT)
  RETURNS INT
  BEGIN
    DECLARE rating INT;

    IF score >= 85 THEN SET rating = 0;
    ELSEIF score >= 80 THEN SET rating = 1;
    ELSEIF score >= 75 THEN SET rating = 2;
    ELSEIF score >= 65 THEN SET rating = 3;
    ELSEIF score >= 60 THEN SET rating = 4;
    ELSE SET rating= 5;
    END IF;

    RETURN rating;
  END //

DELIMITER ;

SET @score = 0;
INSERT INTO rating (`account_id`, `score`, `rating`, `name`, `province`, `city`, `target_areas`)
  SELECT `account_id`, @score := 90 * RAND() + 10, CalcRating(@score), `name`, `province`, `city`, REPLACE(TRIM(work_field)," ", ",") FROM user WHERE type='ngo';
