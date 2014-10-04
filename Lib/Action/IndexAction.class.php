<?php

class IndexAction extends Action {
    public function index(){
    	// $news = O('news')->limit(20)->select();
    	for($i=0;$i<count($news);$i++){
    		$name_parts = explode('：', $news[$i]['name']);
    		if(isset($name_parts[1])){
    			$news[$i]['org_name'] = $name_parts[0];
    			$news[$i]['name'] = $name_parts[1];
    		}
    	}
    	
    	$ngo_count = O('user')->with('type', 'ngo')->with('is_checked', 1)->active_only()->count();
        $ngo_event_count = O('event')->with('type', 'ngo')->active_only()->count();
        $csr_count = O('event')->with('type', 'csr')->active_only()->count();
        $case_count = O('event')->with('type', 'case')->active_only()->count();
        $account_count = O('account')->count();
        $this->assign('account_count', $account_count);
        $ngo_province_count = O()->query("select province, count(*) cnt from user where is_checked=1 and enabled=1 and type='ngo' group by province");
        $province_count = array();
        foreach($ngo_province_count as $rec){
            if(!empty($rec['province'])){
                $province_count[$rec['province']] = $rec['cnt'];
            }
        }
        $this->assign('province_count', $province_count);
        $total_count = array(
                'ngo' => $ngo_count,
                'event' => $ngo_event_count,
                'csr' => $csr_count,
                'case' => $case_count
            );
        $recent_events = O('event')->join('user on event.user_id=user.id')->field('event.id id, event.name name, user.name uname, event.cover_img cimg')
                            ->with('event.is_checked', 1)->limit(10)->order('event.cover_img is not null desc,event.create_time desc')->select();
    	
    	$this->assign('total_count', $total_count);
    	$this->assign('news', $recent_events);
    	$this->display();
    }

    public function list_index($type='ngo', $work_field=null, $province=null, $keyword=null, $medal=null){
        $medals = O('Medal')->order('score desc')->select();
        $is_user = false;
        if($type == 'event'){
            $base_model = O('event')->with('type', 'ngo')->attach('user');
        }
        else if($type == 'csr'){
            $base_model = O('event')->with('type', 'csr')->attach('user');
        }
        else if($type == 'case'){
            $base_model = O('event')->with('type', 'case')->attach('user');
        }
        else{
            $base_model = O('user')->with('type', 'ngo');
            $is_user = true;
        }
        $base_model = $base_model->active_only();

        if(!empty($province)){
            $base_model = $base_model->province($province);
        }
        if(!empty($keyword)){
            $base_model = $base_model->keyword($keyword);
        }
        if(!empty($work_field)){
            $base_model = $base_model->with('work_field', array('like', "%$work_field%"));
        }
        if($type=='ngo' && !empty($medal)){
            $base_model = $base_model->with('_string', 'id in (select user_id from medalmap where medal_id='.intval($medal).')');
        }

        $count = $base_model->count();
        import("@.Classes.TBPage");
        $listRows = C('LIST_RECORD_PER_PAGE');
        $pager = new TBPage($count, $listRows);
        if($type=='ngo'){
            $result = $base_model->order('medal_score desc, id desc')->limit($pager->firstRow, $listRows)->fetch('medalmap')->select();
            if(!empty($medal)){
                $this->assign('current_medal', O('medal')->find($medal));
            }
        }
        else{
            $result = $base_model->order('id desc')->limit($pager->firstRow, $listRows)->select();
        }
        $this->assign('count', $count_with_multipal_locations);
        $this->assign('page', $page);
        $this->assign('type', $type);
        $this->assign('result', $result);
        $this->assign('pager_html', $pager->show());

        $this->assign('medals', $medals);
        $this->display();
    }

    public function mini_search($q){
        $results = OO('XSearch')->search($q,5);
        for($i=0;$i<count($results);$i++){
            if(substr($results[$i]['pid'], 0, 5)=='event'){
                $results[$i]['url'] = U('Event/view').'/id/'.substr($results[$i]['pid'], 6);
            }
            else if(substr($results[$i]['pid'], 0, 4)=='user'){
                $results[$i]['url'] = U('User/view').'/id/'.substr($results[$i]['pid'], 5);
            }
            else if(substr($results[$i]['pid'], 0, 5)=='local'){
                $results[$i]['url'] = U('Local/post_view').'/id/'.substr($results[$i]['pid'], 6);
            }
        }

        $this->assign('results', $results);
        $this->display();
    }   
    
    public function map(){
        $this->display();
    }
    
    public function map_result($province=null, $keyword=null, $type=null, $work_field=null, $minlon=null, $maxlon=null, $minlat=null, $maxlat=null, $page=1, $mini=false){
        // unpack arguments
        if(is_array($province)){
            $args = $province;
            $province = $args['province'];
            $keyword = $args['keyword'];
            $type = $args['type'];
            $work_field = $args['work_field'];
        };
        // build model based on type
        $is_user = false;
        if($type == '公益活动'){
            $base_model = O('event')->with('type', 'ngo')->attach('user');
        }
        else if($type == '企业公益活动'){
            $base_model = O('event')->with('type', 'csr')->attach('user');
        }
        else if($type == '对接案例'){
            $base_model = O('event')->with('type', 'case')->attach('user');
        }
        else{
            $base_model = O('user')->with('type', 'ngo');
            $is_user = true;
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
        if(!$is_user){
            $base_model = $base_model->join('event_location on event.id=event_location.event_id');
        }
        $grand_total = $base_model->count();
        // do the search
        // $count = $base_model->count();
        
            
        if(!empty($minlon)){
        $base_model = $base_model->with('longitude', 
                                     array(array('gt', floatval($minlon)), array('lt', floatval($maxlon))))
                                 ->with('latitude', 
                                     array(array('gt', floatval($minlat)), array('lt', floatval($maxlat))));
        }
        
        $count_with_multipal_locations = $base_model->count();
        import("@.Classes.BNBPage");
        $results_per_page = C('LIST_RECORD_PER_PAGE');
        if($mini){
            $results_per_page = 7;
        }
        $pager = OO('BNBPage')->build($count_with_multipal_locations, $results_per_page, $page);

        $result = $base_model->order('cover_img desc')->limit($pager->firstRow, $pager->rowsPerPage)->select();
        // process result: merge same items, concate lng and lat
        $result_map = array();
        if(!is_user){
            foreach($result as $res){
                if(isset($result_map[$res['id']])){
                    // if already in map, concat lon, lat
                    $result_map[$res['id']]['longitude'] .= ','.$res['longitude'];
                    $result_map[$res['id']]['latitude'] .= ','.$res['latitude'];
                }
                else{
                    $result_map[$res['id']] = $res;
                }
            }
        }
        else{
            $result_map = $result;
        }
        $this->assign('count', $count_with_multipal_locations);
        $this->assign('page', $page);
        $this->assign('total_page', $pager->totalPages);
        $this->assign('grand_total', $grand_total);
        $this->assign('result', $result_map);
        $this->assign('pager_html', $pager->show());
        if($mini){
            $this->display('Index:map_result_mini');
        }
        else{
            $this->display('Index:map_result');
        }
        
    }
}