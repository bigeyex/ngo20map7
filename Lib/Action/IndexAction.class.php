<?php

class IndexAction extends Action {
    public function index(){
    	$news = O('news')->limit(20)->select();
    	for($i=0;$i<count($news);$i++){
    		$name_parts = explode('：', $news[$i]['name']);
    		if(isset($name_parts[1])){
    			$news[$i]['org_name'] = $name_parts[0];
    			$news[$i]['name'] = $name_parts[1];
    		}
    	}
    	
    	$ngo_count = D('user')->with('type', 'ngo')->with('is_checked', 1)->active_only()->count();
    	

    	$this->assign('ngo_count', $ngo_count);
    	$this->assign('news', $news);
    	$this->display();
    }
    
    public function map(){
        $this->display();
    }
    
    public function map_result($province=null, $keyword=null, $type=null, $work_field=null, $minlon=null, $maxlon=null, $minlat=null, $maxlat=null){
        // unpack arguments
        if(is_array($province)){
            $args = $province;
            $province = $args['province'];
            $keyword = $args['keyword'];
            $type = $args['type'];
            $work_field = $args['work_field'];
        };
        // build model based on type
        if($type == '公益活动'){
            $base_model = D('event')->with('type', 'ngo')->attach('user');
        }
        else if($type == '企业公益活动'){
            $base_model = D('event')->with('type', 'csr')->attach('user');
        }
        else if($type == '对接案例'){
            $base_model = D('event')->with('type', 'case')->attach('user');
        }
        else{
            $base_model = D('user')->with('type', 'ngo');
        }
        
        if(!empty($province)){
            $base_model = $base_model->province($province);
        }
        if(!empty($keyword)){
            $base_model = $base_model->keyword($keyword);
        }
        if(!empty($work_field)){
            $base_model = $base_model->with('work_field', array('like', "%$work_field%"));
        }
        if(!empty($minlon)){
            $base_model = $base_model->with('longitude', 
                                         array(array('gt', floatval($minlon)), array('lt', floatval($maxlon))))
                                     ->with('latitude', 
                                         array(array('gt', floatval($minlat)), array('lt', floatval($maxlat))));
        }
        
        // do the search
        $count = $base_model->count();
        $result = $base_model->order('cover_img desc')->limit(10)->select();
        $this->assign('count', $count);
        $this->assign('result', $result);
        $this->display('Index:map_result');
        
    }
}