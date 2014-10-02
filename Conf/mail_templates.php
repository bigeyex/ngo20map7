<?php

return array(

    'forget_password_email_tmpl' => <<<EndOfFile

您收到这封邮件是因为您使用了公益地图(www.ngo20map.com)的找回密码功能。<br/>
请点击下面的链接找回密码：<br/>
<br/>
<a href="{{link}}">点此重置密码</a><br/>
<br/>
如果您没有使用过找回密码功能，请忽略它。<br/>
如果您有其他的问题，请联系<a href="mailto:info@ngo20.org">NGO20</a><br/>




EndOfFile
    ,'pass_check_email_tmpl' => <<<EndOfFile

恭喜！您在公益地图上添加的机构已经成功通过审核！

赶快<a href="http://www.ngo20map.com">去公益地图添加您的活动项目吧</a>。

EndOfFile

    );