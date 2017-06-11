<?php
return array(
    'ORG_FIELDS' => array('医疗卫生健康','女性权利','儿童青少年','教育助学','艾滋病','灾害管理','文化艺术','环境保护','农村发展','城市社区建设','劳工权益','同性恋','政策倡导','信息网络','公益行业支持','社会企业','动物福利','老年人','民间研究机构','企业社会责任','残障人士','综合志愿服务','其它'),

    'ORG_RATINGS' => array('A+', 'A', 'A-', 'B+', 'B', 'B-'),

    'PROVINCES' => array('北京','天津','河北','内蒙古','山西','上海','安徽','江苏','浙江','山东','福建','江西','广东','广西','海南','河南','湖北','湖南','黑龙江','吉林','辽宁','陕西','甘肃','宁夏','青海','新疆','重庆','四川','云南','贵州','西藏','香港','澳门','台湾'
        ),
    'event_labels' => array(
        'ngo' => '公益活动',
        'csr' => '企业活动',
        'case' => '对接案例'
        ),
    'COMPLETION_SCRIPT' => <<<EOS
    var type_categories = [{q:"公益机构",p:"gongyijigou"}, {q:"公益活动",p:"gongyihuodong"}, {q:"企业公益活动",p:"qiyegongyihuodong"}, {q:"对接案例",p:"duijieanli"}];
    var cause_categories = [{q:'医疗卫生健康',p:'yiliaoweishengjiankang'},{q:'女性权利',p:'nvxingquanli'},{q:'儿童青少年',p:'ertongqingshaonian'},{q:'教育助学',p:'jiaoyuzhuxue'},{q:'艾滋病',p:'aizibing'},{q:'灾害管理',p:'zaihaiguanli'},{q:'文化艺术',p:'wenhuayishu'},{q:'环境保护',p:'huanjingbaohu'},{q:'农村发展',p:'nongcunfazhan'},{q:'城市社区建设',p:'chengshishequjianshe'},{q:'劳工权益',p:'laogongquanyi'},{q:'同性恋',p:'tongxinglian'},{q:'政策倡导',p:'zhengcechangdao'},{q:'信息网络',p:'xinxiwangluo'},{q:'公益行业支持',p:'gongyihangyezhichi'},{q:'社会企业',p:'shehuiqiye'},{q:'动物福利',p:'dongwufuli'},{q:'老年人',p:'laonianren'},{q:'民间研究机构',p:'minjianyanjiujigou'},{q:'企业社会责任',p:'qiyeshehuizeren'},{q:'残障人士',p:'canzhangrenshi'},{q:'综合志愿服务',p:'zonghezhiyuanfuwu'},{q:'其它',p:'qita'}];
    var region_categories = [{q:'安徽',p:'anhuiah'},{q:'北京',p:'beijingbj'},{q:'重庆',p:'chongqingcq'},{q:'福建',p:'fujianfj'},{q:'甘肃',p:'gansugs'},{q:'广东',p:'guangdonggd'},{q:'广西',p:'guangxigx'},{q:'贵州',p:'guizhougz'},{q:'海南',p:'hainanhn'},{q:'河北',p:'hebeihb'},{q:'黑龙江',p:'heilongjianghlj'},{q:'河南',p:'henanhn'},{q:'香港',p:'xianggangxg'},{q:'湖北',p:'hubeihb'},{q:'湖南',p:'hunanhn'},{q:'江苏',p:'jiangsujs'},{q:'江西',p:'jiangxijx'},{q:'吉林',p:'jilinjl'},{q:'辽宁',p:'liaoningln'},{q:'澳门',p:'aomenam'},{q:'内蒙古',p:'neimenggunmg'},{q:'宁夏',p:'ningxianx'},{q:'青海',p:'qinghaiqh'},{q:'山东',p:'shandongsd'},{q:'上海',p:'shanghaish'},{q:'山西',p:'shanxisx'},{q:'陕西',p:'shanxisx'},{q:'四川',p:'sichuansc'},{q:'台湾',p:'taiwantw'},{q:'天津',p:'tianjintj'},{q:'新疆',p:'xinjiangxj'},{q:'西藏',p:'xizangxz'},{q:'云南',p:'yunnanyn'},{q:'浙江',p:'zhejiangzj'}];
    var keyword_categories = [{q:'留守儿童',p:'liushouertong'},{q:'义工',p:'yigong'},{q:'农民工',p:'nongmingong'},{q:'捐书',p:'juanshu'},{q:'助学',p:'zhuxue'},{q:'乡村教育',p:'xiangcunjiaoyu'},{q:'电脑教室',p:'diannaojiaoshi'},{q:'希望小学',p:'xiwangxiaoxue'},{q:'爱心图书室',p:'aixintushushi'},{q:'爱心书包',p:'aixinshubao'},{q:'夏令营',p:'xialingying'},{q:'结对',p:'jiedui'},{q:'残疾',p:'canji'},{q:'艾滋',p:'aizi'},{q:'心理健康',p:'xinlijiankang'},{q:'环保',p:'huanbao'},{q:'植树',p:'zhishu'},{q:'低碳',p:'ditan'},{q:'志愿者',p:'zhiyuanzhe'},{q:'孤儿',p:'guer'},{q:'社区服务',p:'shequfuwu'},{q:'老人',p:'laoren'},{q:'自闭症',p:'zibizheng'},{q:'妇女',p:'funv'},{q:'企业社会责任',p:'qiyeshehuizeren'},{q:'智障',p:'zhizhang'},{q:'地震',p:'dizhen'},{q:'白血病',p:'baixuebing'},{q:'捐衣',p:'juanyi'},{q:'聋哑',p:'longya'},{q:'白内障',p:'baineizhang'}];
EOS
,
    'EVENT_RESOURCES' => array('资金','物品','场地','技术','志愿者','媒体','能力建设','咨询','法律支持','技术培训','免费广告位','多媒体设备','交通设施'),
    'EVENT_NEEDS' => array('需要合作方','媒体需求','物资需求','资金需求','志愿者需求'),
    'EVENT_TYPE' => array(
                        'requirement' => array('需要合作方','媒体需求','物资需求','资金需求','志愿者需求'),
                        'resource' => array('资金','物品','场地','技术','志愿者','媒体','能力建设','咨询','法律支持','技术培训','免费广告位','多媒体设备','交通设施'),
        ),
    'HOMEPAGE_TAG' => array('留守儿童','义工','农民工','捐书','助学','乡村教育','电脑教室','希望小学',
                    '爱心图书室','爱心书包','夏令营','结对','残疾','艾滋','心理健康','环保','植树','低碳','志愿者','孤儿','社区服务','老人','自闭症','妇女','企业社会责任','智障','地震','白血病','捐衣','聋哑','白内障'),
    'VOLUNTEER_SKILLS' => array('外语', 'IT基础设施维护', '网络编程', '美工', '营销', '法律','摄影摄像','文字创作','口语表达'),
    'DEFAULT_LOCAL_CONFIG' => array(
        'modules' => array(
            array('name' => '公告', 'type' => 'post'),
            array('name' => '资源', 'type' => 'post'),
            array('name' => '活动', 'type' => 'post'),
        ),
    ),
    'LOCAL_MODULES' => array(
        'post' => '文章',
        'ngo' => '公益机构',
        'csr' => '企业公益活动',
        'event' => '公益活动',
        'case' => '对接案例',
    ),
    'TERM' => <<<EndOfFile

3．1　用户在使用公益地图服务时，必须遵守中华人民共和国相关法律法规的规定，用户应同意将不会利用本服务进行任何违法或不正当的活动，包括但不限于下列行为∶
（1）上载、展示、张贴、传播或以其它方式传送含有下列内容之一的信息：
1） 反对宪法所确定的基本原则的； 2） 危害国家安全，泄露国家秘密，颠覆国家政权，破坏国家统一的； 3） 损害国家荣誉和利益的； 4） 煽动民族仇恨、民族歧视、破坏民族团结的； 5） 破坏国家宗教政策，宣扬邪教和封建迷信的； 6） 散布谣言，扰乱社会秩序，破坏社会稳定的； 7） 散布淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的； 8） 侮辱或者诽谤他人，侵害他人合法权利的； 9） 含有虚假、有害、胁迫、侵害他人隐私、骚扰、侵害、中伤、粗俗、猥亵、或其它道德上令人反感的内容； 10） 含有中国法律、法规、规章、条例以及任何具有法律效力之规范所限制或禁止的其它内容的；
（2）不得为任何非法目的而使用网络服务系统；
（3）不利用公益地图服务从事以下活动：
1) 未经允许，进入计算机信息网络或者使用计算机信息网络资源的；
2) 未经允许，对计算机信息网络功能进行删除、修改或者增加的；
3) 未经允许，对进入计算机信息网络中存储、处理或者传输的数据和应用程序进行删除、修改或者增加的；
4) 故意制作、传播计算机病毒等破坏性程序的；
5) 其他危害计算机信息网络安全的行为。

EndOfFile
);
?>