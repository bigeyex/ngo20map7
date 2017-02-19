<?php
// replace a part of the url request.
// eg. for current url as
// http://localhost/show?a=1&b=2
// url_replace('a', 3) returns:
// http://localhost/show?a=3&b=2
function url_replace($key, $value){
    $schema = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $current_uri = $schema .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if(preg_match("/$key=/", $current_uri)){
        // if it is already defined - change it
        $new_uri = preg_replace("/$key=[^&]*/", "$key=$value", $current_uri);
    }
    else{
        if(preg_match("/\?/", $current_uri)){
            $new_uri = $current_uri . "&$key=$value";
        }
        else{
            $new_uri = $current_uri . "?$key=$value";
        }
    }
    return $new_uri;
}

function request_var($key){
    if(isset($_GET[$key])){
        return $_GET[$key];
    }
    else{
        return null;
    }
}

function in_result_set($needle, $heystack, $search_by='id'){
    foreach($heystack as $row){
        if($row[$search_by] == $needle){
            return true;
        }
    }
    return false;
}

function receive_tourist($tour_id){
    return false;
    if(APP_DEBUG){
        return true;
    }

    if(isset($_SESSION['tour_'.$tour_id]) || isset($_COOKIE['tour_'.$tour_id])){
        return false;
    }
    else{
        $_SESSION['tour_'.$tour_id] = 1;
        setcookie('tour_'.$tour_id,1,time() + (86400 * 60)); // 86400 = 1 day
        return true;
    }
}

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

function active_if($condition){
    return class_if('active', $condition);
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
            if(APP_DEBUG){
                return '<script type="text/javascript" src="'.__APP__.'/Public/js/'.substr($str, 1).'.js"></script>';
            }
            else{
                return '<script type="text/javascript" src="'.__APP__.'/Runtime/Cache/'.minimize_js(array(substr($str, 1))).'.js"></script>';
            }
        }
        else{
            $js_list[] = $str;
            // if(APP_DEBUG){
            //     return '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$str.'.js"></script>';
            // }
            // else{
            //     return '<script type="text/javascript" src="'.__APP__.'/Runtime/Cache/'.minimize_js(array($str)).'.js"></script>';
            // }
        }
    }
    else{    // render js files
        if(APP_DEBUG){
            $ret = '';
            foreach($js_list as $js){
                $ret .= '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$js.'.js"></script>';
            }
            $js_list = array();
            return $ret;
        }
        else{
            $result = '<script type="text/javascript" src="'.__APP__.'/Runtime/Cache/'.minimize_js($js_list).'.js"></script>';
            $js_list = array();
            return $result;
        }
    }
    return '';
}

function js_list($js_list) {
  if(APP_DEBUG) {
    $ret = '';
    foreach($js_list as $js){
        $ret .= '<script type="text/javascript" src="'.__APP__.'/Public/js/'.$js.'.js"></script>';
    }
    return $ret;
  }
  else {
    return '<script type="text/javascript" src="'.__APP__.'/Runtime/Cache/'.minimize_js($js_list).'.js"></script>';
  }
}

function minimize_js($file_list){
    // get the newest file
    $max_time = 0;
    foreach($file_list as $file){
        // $time: the last modified time of the js file
        $time = filemtime(APP_PATH.'Public/js/'.$file.'.js');
        if($time > $max_time) $max_time = $time;
    }
    $files_md5 = 'minified_'.md5(implode('', $file_list));
    $md5_file = APP_PATH.'Runtime/Cache/'.$files_md5.'.js';
    if(file_exists($md5_file) && filemtime($md5_file)>=$max_time){
       return $files_md5;
    }
    else{
        require_once APP_PATH.'Lib/Classes/Minifier2.php';
        $final_js = '';
        // minimize each file
        foreach($file_list as $file){
            $file_md5 = 'ms_'.md5($file);
            $minimized_single_file = APP_PATH.'Runtime/Cache/'.$file_md5.'.js';
            $time = filemtime(APP_PATH.'Public/js/'.$file.'.js');
            // if the file is old, rebuild if
            if(!file_exists($minimized_single_file) || filemtime($minimized_single_file)<$time){
               $single_file_js = file_get_contents(APP_PATH.'Public/js/'.$file.'.js');
               $partial_js = Minifier::minify($single_file_js);
               file_put_contents($minimized_single_file, $partial_js);
            }
            else{   // otherwise load from compressed file
                $partial_js = file_get_contents($minimized_single_file);
            }
            $final_js .= $partial_js;
        }
        // concat all files as one
        file_put_contents($md5_file, $final_js);
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
function css($str=null, $max_ie=20){

	static $css_list = array();
	if($str !== null && get_ie_version()<$max_ie){
        if(substr($str, 0, 1)==='-'){
            return '<link href="'.__APP__.'/Public/css/'.substr($str, 1).'.css" rel="stylesheet"/>';
        }
        else{
            $css_list[] = $str;
            // return '<link href="'.__APP__.'/Public/css/'.$str.'.css" rel="stylesheet"/>';
        }
    }
    else{    // render js files
        if(APP_DEBUG){
            $ret = '';
            foreach($css_list as $css){
                if(substr($css, strlen($css)-5) == '.less'){
                    $file_md5 = 'lc_' . substr($css, 0, strlen($css)-5) . '.css';
                    $output_path = APP_PATH . 'Runtime/Cache/' . $file_md5;
                    $file_path = APP_PATH . 'Public/css/' . $css;
                    require_once APP_PATH.'Lib/Classes/lessc.inc.php';
                    $less = new lessc;
                    $less->checkedCompile($file_path, $output_path);
                    $ret .= '<link href="'.__APP__.'/Runtime/Cache/'.$file_md5.'" rel="stylesheet"/>';
                }
                else{
                    $ret .= '<link href="'.__APP__.'/Public/css/'.$css.'.css" rel="stylesheet"/>';
                }
            }
            return $ret;
        }
        else{
            return '<link href="'.__APP__.'/Runtime/Cache/'.minimize_css($css_list).'.css" rel="stylesheet"/>';
        }
        $css_list = array();
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
    $md5_file = APP_PATH.'Runtime/Cache/'.$files_md5.'.css';
    if(file_exists($md5_file) && filemtime($md5_file)>=$max_time){
       return $files_md5;
    }
    else{
        require APP_PATH.'Lib/Classes/CSSMin.class.php';
        require APP_PATH.'Lib/Classes/lessc.inc.php';
        // concat all files as one
        $final_css = '';
        foreach($file_list as $file){
            $file_md5 = 'ms_'.md5($file);
            $minimized_single_file = APP_PATH.'Runtime/Cache/'.$file_md5.'.css';
            $time = filemtime(APP_PATH.'Public/js/'.$file.'.css');
            // compile the file if it is old
            if(!file_exists($minimized_single_file) || filemtime($minimized_single_file)<$time){
               // check if it is a .less file, compile it first
                if(substr($file, strlen($file)-5) == '.less'){
                    $single_file_css = file_get_contents(APP_PATH.'Public/css/'.$file);
                    $less = new lessc;
                    $single_file_css = $less->compile($single_file_css);
                }
                else{
                    $single_file_css = file_get_contents(APP_PATH.'Public/css/'.$file.'.css');
                }
                $partial_css = OO('CSSMin')->run($single_file_css);
                file_put_contents($minimized_single_file, $partial_css);
            }
            else{   // otherwise, load it from cache
                $partial_css = file_get_contents($minimized_single_file);
            }
            $final_css .= $partial_css;
        }
        // concat all files as one
        file_put_contents($md5_file, $final_css);
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

function label_for($group, $str){
    $c_group = C($group);
    if(empty($c_group)){
        return '';
    }
    if(empty($c_group[$str])){
        return L('无');
    }
    return L($c_group[$str]);
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

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
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

function admin_only(){

}

?>
