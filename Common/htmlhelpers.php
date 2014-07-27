<?php
function active_for($str){
    $condition_groups = explode('/', $str);
    for($i=0;$i<floor(count($condition_groups)/2);$i++){
        $arg1 = $condition_groups[$i];
        $arg2 = $condition_groups[$i+1];
        if(substr($arg1, 0, 1) == ':'){
            if($_GET[substr($arg1, 1)] != $arg2){
                return '';
            }
        }    // start with ':'
        else{
            if($arg1!='*' && MODULE_NAME != $arg1){
                return '';
            }
            if($arg2!='*' && ACTION_NAME != $arg2){
                return '';
            }
        }    // not start with ':'
    }
    return 'active';
}

function class_if($class, $condition){
	if($condition){
		return $class;
	}
	else{
		return '';
	}
}

function text_if($condition, $text, $default=''){
    if($condition){
        return $text;
    }
    else{
        return $default;
    }
}


function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

// insert a <script> tag
// usage: 
// js('example.js') -> add example.js to the javascript list;
// js() -> render concatnated and minimized js at current point;
// js('nc:example.js') -> no compile - insert a raw js stab at current point
// js('nc:example.js', 7) -> insert js stab only if ie version < 7
function js($str=null, $max_ie=20){ 
	static $js_list = array();
	if($str !== null && get_ie_version()<$max_ie){
        if(substr($str, 0, 1)==='-'){
            if(C('APP_DEBUG')){
                return '<script type="text/javascript" src="'.__APP__.'/Public/js/'.substr($str, 1).'.js"></script>';
            }
            else{
                return '<script type="text/javascript" src="'.__APP__.'/Public/cache/'.minimize_js(array(substr($str, 1))).'.js"></script>';
            }
        }
        else{
            $js_list[] = $str;
        }
    }
    else{    // render js files
        if(C('APP_DEBUG')){
            $ret = '';
            foreach($js_list as $js){
                $ret .= '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$js.'.js"></script>';
            }
            return $ret;
        }
        else{
            return '<script type="text/javascript" src="'.__APP__.'/Public/cache/'.minimize_js($js_list).'.js"></script>';
        }
    }
    return '';
}

function minimize_js($file_list){
    // get the newest file
    $max_time = 0;
    foreach($file_list as $file){
        $time = filemtime(APP_PATH.'Public/js/'.$file.'.js');
        if($time > $max_time) $max_time = $time;
    }
    $files_md5 = 'minified_'.md5(implode('', $file_list));
    $md5_file = APP_PATH.'Public/cache/'.$files_md5.'.js';
    if(file_exists($md5_file) && filemtime($md5_file)>=$max_time){
       return $files_md5; 
    }
    else{
        require_once APP_PATH.'Lib/Classes/Minifier2.php';
        // concat all files as one
        $final_js = '';
        foreach($file_list as $file){
            $final_js .= file_get_contents(APP_PATH.'Public/js/'.$file.'.js');
        }
        file_put_contents($md5_file, Minifier::minify($final_js));
        return $files_md5;
    }
    
}

function get_ie_version(){
    if(ereg('MSIE 6',$_SERVER['HTTP_USER_AGENT'])){
        return 6;
    }
    else if(ereg('MSIE 7',$_SERVER['HTTP_USER_AGENT'])){
        return 7;
    }
    else if(ereg('MSIE 8',$_SERVER['HTTP_USER_AGENT'])){
        return 8;
    }
    return 10;
}

// insert css file
function css($str, $max_ie=20){

	static $css_list = array();
	if($str !== null && get_ie_version()<$max_ie){
        if(substr($str, 0, 1)==='-'){
            return '<link href="'.__APP__.'/Public/css/'.substr($str, 1).'.css" rel="stylesheet"/>';
        }
        else{
            $css_list[] = $str;
        }
    }
    else{    // render js files
        if(C('APP_DEBUG')){
            $ret = '';
            foreach($css_list as $css){
                $ret .= '<link href="'.__APP__.'/Public/css/'.$css.'.css" rel="stylesheet"/>';
            }
            return $ret;
        }
        else{
            return '<link href="'.__APP__.'/Public/cache/'.minimize_css($css_list).'.css" rel="stylesheet"/>';
        }
    }
    return '';
}

function minimize_css($file_list){
    // get the newest file
    $max_time = 0;
    foreach($file_list as $file){
        $time = filemtime(APP_PATH.'Public/css/'.$file.'.css');
        if($time > $max_time) $max_time = $time;
    }
    $files_md5 = 'minified_'.md5(implode('', $file_list));
    $md5_file = APP_PATH.'Public/cache/'.$files_md5.'.css';
    if(file_exists($md5_file) && filemtime($md5_file)>=$max_time){
       return $files_md5; 
    }
    else{
        require APP_PATH.'Lib/Classes/CSSMin.class.php';
        // concat all files as one
        $final_css = '';
        foreach($file_list as $file){
            $final_css .= file_get_contents(APP_PATH.'Public/css/'.$file.'.css');
        }
        file_put_contents($md5_file, OO('CSSMin')->run($final_css));
        return $files_md5;
    }   
}

function link_for($str){
    return __APP__.'/Public/'.$str;
}

// insert image file
function img($str, $alt='', $attr=array()){
	$extra_attr = '';
	foreach($attr as $k=>$v){
		$extra_attr .= ' '.$k.'="'.$v.'"';
	}
	return '<img src="'.__APP__.'/Public/img/'.$str.'" alt="'.$alt.'"'.$extra_attr.'/>';
}

// insert url of uploaded image or thumbnail
function thumb($str, $thumb_level = -1){
	if(is_array($str)){
		if(isset($str['image'])){
			$str = $str['image'];
		}
	}

	if($thumb_level === 0){
		return __APP__.'/Public/Uploaded/'.$str;
	}
	elseif($thumb_level == -1){
		return __APP__.'/Public/Uploadedthumb/'.$str;
	}
	else{
		return __APP__.'/Public/Uploadedthumb/'.$thumb_level.'_'.$str;
	}
}

function upimage($str, $thumb=null){
	if(!empty($str)){
		if(substr($str, 0, 7)=='http://' || substr($str, 0, 8)=='https://'){
            return $str;
        }
		if($thumb){
			return __APP__.'/Public/Uploaded/th'.$thumb.'_'.$str;
		}
		else{
			return __APP__.'/Public/Uploaded/'.$str;
		}
	}
	else{
		return __APP__.'/Public/img/no-image-placeholder.png';
	}

}

// print default text if string is empty
function place($str, $ifempty = "暂无"){
	if(empty($str)){
		return $ifempty;
	}
	else{
		return $str;
	}
}

function short($str, $length=150){
	if(mb_strlen($str) > $length){
		$str = mb_substr($str, 0, $length) . '...';
	}

	return $str;
}

function datef($str, $format='Y年m月d日 h:i'){
	return date($format, strtotime($str));
}

function label_type($str){
	switch ($str) {
		case 'ngo':
			return '公益组织';
			break;
		case 'csr':
		case 'ind':
			return '企业';
			break;
		case 'case':
			return '对接案例';
			break;
		case 'event':
			return '活动';
			break;
		
		default:
			return '';
			break;
	}
}

function cleanInput($input) {

	$search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
    );

	$output = preg_replace($search, '', $input);
	return $output;
}

function escape_sql($inp) { 
    if(is_array($inp)) 
        return array_map(__METHOD__, $inp); 

    if(!empty($inp) && is_string($inp)) { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
    } 

    return $inp; 
}

// back to the previous page
function back(){
	if(isset($_SESSION['last_page'])){
        redirect($_SESSION['last_page']);
        return true;
    }
    else{
        return false;
    }
}

function flash($content, $type='error'){
	$_SESSION['flash']['type'] = $type;
	$_SESSION['flash']['content'] = $content;
}

?>