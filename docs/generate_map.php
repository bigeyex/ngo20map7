<?php

ob_start();

require 'MapGenerator/MapGenerator.class.php';
require 'db.php';

ob_end_clean();

$generator = new MapGenerator(1280,500);
$generator->loadImageAsBackground('MapGenerator/base-map.png');
$generator->calibrate(48.104879,53.580089,159.518909,19.421305);

// mark all csrs
$csr_icon = $generator->loadImage('MapGenerator/csr-icon.png');
$csr_list = query("select longitude, latitude from event left join event_location on event.id=event_location.event_id where is_checked=1 and enabled=1 and type='csr'");
foreach($csr_list as $record){
    if(is_numeric($record['longitude']) && is_numeric($record['latitude'])){
        $generator->addMarker($csr_icon, $record['longitude'], $record['latitude']);
    }
}

// mark all NGOs
$ngo_icon = $generator->loadImage('MapGenerator/ngo-icon.png');
$ngo_list = query("select longitude,latitude from user where type='ngo' and is_checked=1 and enabled=1");
foreach($ngo_list as $record){
    if(is_numeric($record['longitude']) && is_numeric($record['latitude'])){
        $generator->addMarker($ngo_icon, $record['longitude'], $record['latitude']);
    }
}
$ngo_list = query("select longitude, latitude from event left join event_location on event.id=event_location.event_id where is_checked=1 and enabled=1 and type='ngo'");
foreach($ngo_list as $record){
    if(is_numeric($record['longitude']) && is_numeric($record['latitude'])){
        $generator->addMarker($ngo_icon, $record['longitude'], $record['latitude']);
    }
}

// mark all cases
$case_icon = $generator->loadImage('MapGenerator/case-icon.png');
$case_list = query("select longitude, latitude from event left join event_location on event.id=event_location.event_id where is_checked=1 and enabled=1 and type='case'");
foreach($case_list as $record){
    if(is_numeric($record['longitude']) && is_numeric($record['latitude'])){
        $generator->addMarker($case_icon, $record['longitude'], $record['latitude']);
    }
}


$generator->render('../Public/cache/map-photo.png');
// print_r($generator);