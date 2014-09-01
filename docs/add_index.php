<?php

require './db.php';
require_once '/Users/wangyu/xunsearch/sdk/php/lib/XS.php';

$xs = new XS('ngo20map');
$xsIndex = $xs->index;
$xsIndex->clean();
$users = query('select * from user where is_checked=1 and enabled=1');
foreach($users as $user){
    $data = array(
        'pid'=> "user_".$user['id'],
        'subject' => $user['name'],
        'message' => $user['intro'],
        'prefix' => 'user',
        'chrono' => strtotime($user['create_time']),
        );
    $doc = new XSDocument;
    $doc->setFields($data);
    
    $xsIndex->update($doc);
}

$events = query('select * from event where is_checked=1 and enabled=1');
foreach($events as $event){
    $data = array(
        'pid'=> "event_".$event['id'],
        'subject' => $event['name'],
        'message' => $event['intro'],
        'prefix' => 'event',
        'chrono' => strtotime($event['create_time']),
        );
    $doc = new XSDocument;
    $doc->setFields($data);
    
    $xsIndex->update($doc);
}


$local_contents = query('select * from local_content where is_checked=1');
foreach($local_contents as $local){
    $data = array(
        'pid'=> "local_".$local['id'],
        'subject' => $local['name'],
        'message' => $local['content'],
        'prefix' => 'local',
        'chrono' => strtotime($local['create_time']),
        );
    $doc = new XSDocument;
    $doc->setFields($data);
    
    $xsIndex->update($doc);
}

