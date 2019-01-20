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

function O($name='', $tablePrefix='',$connection='') {
    $class = ucwords($name).'Model';
    import('Model/'.$name);
    
    if($name!='' && class_exists($class)){
        $model = new $class($name);
    }else{
        $model = new BaseModel($name);
    }
    return $model;
}

function OO($name){
    import('@.Classes.'.$name );
    return new $name;
}

function extract_field($arr, $field){
    $ret = array();
    foreach($arr as $result){
        array_push($ret, $result[$field]);
    }
    return array_unique($ret);
}

function assignStringWithDefaultValue($name, $maybeUrl, $default = '') {
    $url_pattern = '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/';
    if($maybeUrl && preg_match($url_pattern, $name) == TRUE) {
       $restult =  '<a href="';
       if (substr($name, 0, 4) != 'http') {
           $restult .= 'http://';
       }
       $restult .= $name;
       $restult .= '" target="_blank">'.$name.'</a>';
       return $restult;
    }
    return $name ?: $default;
}

function existCh($b) {
    return $b ? '有' : '';
}