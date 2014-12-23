<?php

function LS($str, $args){
    echo vsprintf(L($str), $args);
}

function tmpl_global($key, $val=null){
    static $global_list = array();
    if($val === null){
        if(isset($global_list[$key])){
            return $global_list[$key];
        }
        else{
            return FALSE;
        }
    }
    else{
        $global_list[$key] = $val;
        return FALSE;
    }
}