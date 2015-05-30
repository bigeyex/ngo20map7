ngo20map7
=========

Bridge the information gaps between grassroots NGO and Corporate Social Responsibility Program.

Installation
============

1. copy the whole ngo20map7 folder to your server;
2. change Conf/db.php according to your db configuration;
3. give write permission on /Public/Uploaded (create if not exist), and /Runtime folder;
4. import docs/database.sql into your mysql server;
5. if you need mail sending service, you need to install Radis;
6. if you need the search engine service, you need to install xunsearch and change SEARCH_API_PATH db.php;

How to Make Changes
===================

1. This project uses Thinkphp framework (currently only documented in Chinese).
2. CSS/Js/Image files are under /Public folder. they are refered in template using the formats such as:
    {:css('base')} -> /Public/css/base.css
    {:css('base.less')} -> /Public/css/base.less (compiled into css)
3. app logics are located under Lib/Action. For url such as:
    http://yourdomainname.com/path-to-app/User/view
    it first consult the "view()" function in /Lib/Action/UserAction.class.php
        and probably rendered with template: /Tpl/User/view.html