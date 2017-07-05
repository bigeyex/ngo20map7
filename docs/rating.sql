SET NAMES utf8;

DELIMITER //

CREATE FUNCTION CalcRating(score INT)
  RETURNS VARCHAR(10)
  BEGIN
    DECLARE rating VARCHAR(10);

    IF score >= 85 THEN SET rating = 'A+';
    ELSEIF score >= 80 THEN SET rating = 'A';
    ELSEIF score >= 75 THEN SET rating =  'A-';
    ELSEIF score >= 65 THEN SET rating = 'B+';
    ELSEIF score >= 60 THEN SET rating = 'B';
    ELSE SET rating = 'B-';
    END IF;

    RETURN rating;
  END //

CREATE FUNCTION CalcFullStaffRange(v INT)
  RETURNS VARCHAR(50)
  BEGIN

    IF ISNULL(v) || v < 10 THEN RETURN '10人以下';
    ELSEIF v < 20 THEN RETURN '10-19人';
    ELSE RETURN '20人及以上';
    END IF;

    RETURN '10人以下';

  END //

CREATE FUNCTION MULTI_FIND_IN_SET(field1 VARCHAR(200), field2 VARCHAR(200))
  RETURNS BOOLEAN
  BEGIN
    DECLARE i INT;
    DECLARE len INT;
    DECLARE subStr VARCHAR(200);
    SET i = 1;
    SET len = 0;
    IF LENGTH(field1) <= 0 THEN RETURN FALSE; END IF;

    WHILE len < LENGTH(field1) DO
      SET subStr = SUBSTRING_INDEX(field1, ',', i);
      SET len = length(subStr);
      IF FIND_IN_SET(SUBSTRING_INDEX(subStr, ',', -1), field2)
      THEN RETURN TRUE; END IF;
      SET i = i + 1;
    END WHILE;
    RETURN FALSE;
  END //

CREATE FUNCTION NormalizeServiceArea(province VARCHAR(20), service_area VARCHAR(100))
  RETURNS VARCHAR(100)
  BEGIN
    IF ISNULL(service_area) || service_area = ''
    THEN RETURN NULL;
    ELSEIF LOCATE('全国', service_area) || LOCATE('亚洲', service_area) || LOCATE('全球', service_area) ||
           LOCATE('不限', service_area) ||
           FIELD(service_area, '上海及中国贫困地区', '中国', '中国大陆', '中国大学生', '中国城市社区', '国内', '中美', '大中华及东南亚地区')
      THEN RETURN '全国范围都有';
    ELSEIF
      LOCATE(',', service_area) || LOCATE('、', service_area) || LOCATE('，', service_area) || LOCATE('-', service_area)
      || LOCATE('/', service_area)
      THEN IF LOCATE('、农村', service_area) || LOCATE('阜阳', service_area) || LOCATE('玉树', service_area) ||
              LOCATE('凉山', service_area) || LOCATE('清水江', service_area) || LOCATE('南京', service_area) ||
              FIELD(service_area, '立足成都，面向中国四川地区', '永宁县、银川市、自治区', '四川农村的三个贫困县（汉源县，甘洛县，峨边县）', '四川、成都')
      THEN RETURN '组织所在的省份';
      ELSEIF LOCATE('省', service_area) || LOCATE('北京', service_area) || LOCATE('上海', service_area) ||
             LOCATE('广西', service_area) || LOCATE('江西', service_area) || LOCATE('云南', service_area) ||
             LOCATE('贵州', service_area) || LOCATE('青海', service_area) || LOCATE('青岛市', service_area) ||
             LOCATE('郑州市', service_area)
        THEN RETURN '涵盖几个省份';
      ELSEIF LOCATE('青岛', service_area) || LOCATE('彭阳县', service_area) || LOCATE('-深圳', service_area)
        THEN RETURN '组织所在的城市';
      ELSE RETURN NULL;
      END IF;
    ELSE
      IF
      province = service_area || LOCATE(service_area, province) || service_area LIKE '%省' || service_area LIKE '%自治区' ||
      service_area LIKE '北京%' || service_area LIKE '重庆%' || service_area LIKE '天津%' || LOCATE('境内', service_area) ||
      field(service_area, '河南全境', '四川为主', '河北中部农村', '长株潭', '黔东南', '安徽各乡镇', '帝都', '在京外来工及其子女', '四川山村', '甘肃省境内', '上海',
            '上海市', '上海市卢湾区')
      THEN RETURN '组织所在的省份';
      ELSEIF LOCATE('江浙', service_area) || LOCATE('西北地区', service_area) || LOCATE('周边省', service_area) ||
             FIELD(service_area, '华南', '西北', '山东省内外', '中国西部', '上海市乃至长三角', '洞庭湖及常见中下游', '西部少数民族', '藏区', '淮河流域', '长江源区')
        THEN RETURN '涵盖几个省份';
      ELSEIF LOCATE('市', service_area) || LOCATE('县', service_area) || LOCATE('州', service_area) ||
             LOCATE('金华', service_area)
        THEN RETURN '组织所在的城市';
      ELSEIF LOCATE('公益', service_area) || LOCATE('大学生', service_area) || LOCATE('心理', service_area) ||
             LOCATE('综合', service_area) || LOCATE('助', service_area) || Locate('教育', service_area) ||
             LOCATE('护', service_area) || LOCATE('生态', service_area) || LOCATE('项目', service_area) ||
             LOCATE('教师', service_area) || LOCATE('人口', service_area) || LOCATE('劳工', service_area) ||
             LOCATE('单独', service_area) || LOCATE('国际', service_area) || LOCATE('平台', service_area) ||
             LOCATE('残疾', service_area) || LOCATE('自闭症', service_area) || FIELD(service_area, '关注领域')
        THEN RETURN NULL;
      ELSEIF LOCATE('宁夏', service_area)
        THEN RETURN '组织所在的省份';
      ELSEIF LOCATE('社区', service_area)
        THEN RETURN '组织所在的社区';
      ELSE RETURN '组织所在的城市';
      END IF;
    END IF;
    RETURN service_area;
  END //

DELIMITER ;


-- add user columns
ALTER TABLE user
  ADD byname VARCHAR(100),
  ADD wechat VARCHAR(50),
  ADD staff_fulltime_range VARCHAR(50),
  ADD member_experience VARCHAR(50),
  ADD lead_experience VARCHAR(50),
  ADD accountant_status VARCHAR(50),
  ADD info_platform VARCHAR(100),
  ADD org_rules VARCHAR(100),
  ADD has_board int(11),
  ADD has_plan int(11),
  ADD plan_file VARCHAR(100),
  ADD has_report int(11),
  ADD report_file VARCHAR(100),
  ADD project_scale VARCHAR(100),
  ADD participant_scale VARCHAR(100),
  ADD media_report VARCHAR(100),
  ADD fund_info VARCHAR(100),
  ADD has_reward int(11),
  ADD reward_detail VARCHAR(100),
  ADD gov_level VARCHAR(20),
  ADD key_resource VARCHAR(100),
  ADD vision VARCHAR(300),
  ADD partner_info VARCHAR(100),
  ADD typical_case VARCHAR(500),
  ADD rating_score int(11),
  ADD rating_level VARCHAR(10);

-- update user table's data;
UPDATE user SET staff_fulltime_range = CalcFullStaffRange(staff_fulltime);
UPDATE user SET  rating_score = 90 * RAND() + 10, rating_level = CalcRating(rating_score);

-- ALTER TABLE user ADD typical_case VARCHAR(100);