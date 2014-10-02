<?php
// 把省份的简称改成全称

require 'db.php';

$provinces = query('select province from user group by province');
foreach($provinces as $rec){
    if(!empty($rec['province'])){
        $p = $rec['province'];
        foreach($provinces as $cmp){
            if($p != $cmp['province'] && strpos($cmp['province'], $p) !== FALSE){
                $d = $cmp['province'];
                query("update user set province='$d' where province='$p'");
                print $p . '==>' . $cmp['province'] . "\n";
                break;
            }
        }
    }
}