<?php
use NGO20Map\Local;

class LocalAction extends Action{
    function index($name=0){
        $local_map_model = new LocalMapModel();
        $user_model = new UserModel();
        $local_map = $local_map_model->where(array(
            'identifier' => $name,
        ))->find();
        $default_map_center = array('lng'=>'', 'lat'=>'', 'zoom'=>'');
        $config = json_decode($local_map['config'], true);
        if(isset($config['map_center'])){
            $map_center_config = explode(',', $config['map_center']);
            $default_map_center['lng'] = $map_center_config[0];
            $default_map_center['lat'] = $map_center_config[1];
            $default_map_center['zoom'] = $map_center_config[2];
        }
        $this->assign('default_map_center', $default_map_center);
        $modules = M('LocalModules')->where(array('local_id'=>$local_map['id']))->order('sortkey')->select();
        $admin_user = $user_model->find($local_map['admin_id']);
        $this->assign('with_notification', true);
        $this->assign('local_map', $local_map);
        $this->assign('modules', $modules);
        $this->assign('admin_user', $admin_user);
        $this->assign('is_local_admin', $local_map['admin_id']==user('id')||user('is_admin'));
        $this->display();
    }
     
    
    public function manage(){
        $local_map_model = new LocalMapModel();
        
        import("@.Classes.TBPage");
        $listRows = C('ADMIN_ROW_LIST');
        $local_map_count = $local_map_model->where('enabled=1')->count();
        $Page = new TBPage($news_count,$listRows);
        $local_map_result = $local_map_model->order('id desc')->limit($Page->firstRow.','.$listRows)->select();
        
        $page_bar = $Page->show();
    
        $this->assign('local_result', $local_map_result);
        $this->assign('page', $page_bar);
        $this->display();
    }

    public function org_list($local_id, $mode='all'){
        $this->need_right_to_admin($local_id);
        $local = O('local_map')->find($local_id);
        $province = $local['province'];
        $user_model = O('user')->province($province);
        if($mode == 'audit'){
            $user_model = $user_model->with('is_checked', 0)->with('type','neq','ind');
        }

        import("@.Classes.TBPage");
        $listRows = C('ADMIN_ROW_LIST');
        $count = $user_model->count();
        $Page = new TBPage($count,$listRows);
        $users = $user_model->limit($Page->firstRow.','.$listRows)->order('create_time desc')->select();
        $page_bar = $Page->show();
    
        $this->assign('page_bar', $page_bar);
        $this->assign('users', $users);
        $this->display();
    }

    public function org_export($local_id){
        $this->need_right_to_admin($local_id);
        $local = O('local_map')->find($local_id);
        $province = $local['province'];
        $result = O('user')->province($province)->order('create_time desc')->select();
        
        $fields = array(
            'name' => '名称',
            'contact_name' => '联系人',
            'public_email' => '电子邮箱',
            'phone' => '联系电话',
            'website' => '网站',
            'city' => '所在城市',
            'county' => '所在乡镇',
            'place' => '地址',
            'aim' => '机构使命',
            'introduction' => '简介',
            'work_field' => '服务领域',
            'register_type' => '注册类型',
            'register_year' => '注册年份',
            'staff_fulltime' => '全职人数',
            'staff_parttime' => '兼职人数',
            'staff_volunteer' => '志愿者人数',
            'financial_link' => '财务报告链接',
        );
        OO('ExcelExport')->output_excel($fields, $result);
    }
    
    public function add_map(){
        $this->assign('action', 'insert');
        $this->display();
    }

    public function insert(){
        $local_map_model = new LocalMapModel();
        $local_map_model->create();

        $local_id = $local_map_model->add();
        
        //insert module config
        $default_config = C('DEFAULT_LOCAL_CONFIG');
        foreach($default_config['modules'] as $module){
            M('local_modules')->add(array(
                    'local_id' => $local_id,
                    'name' => $module['name'],
                    'type' => $module['type'],
                ));
        }

        $this->redirect('manage');
    }

    public function edit($id){
        $local_map_model = new LocalMapModel();
        $local_map = $local_map_model->find($id);

        $user_model = new UserModel();
        $user = $user_model->find($local_map['admin_id']);
        $local_map['user_name'] = $user['name'];

        $this->assign('local_map', $local_map);
        $this->assign('action', 'save');
        $this->display('add_map');
    }

    public function save(){
        $news_model = new LocalMapModel();
        $news_model->create();
        $news_model->save();

        $this->redirect('manage');
    }

    public function delete_map($id){
        $local_map_model = new LocalMapModel();
        $local_map_model->delete($id);

        $this->redirect('manage');
    }
    
    public function embed_map($local_id){
        $pre_path = 'http://'.$_SERVER['HTTP_HOST'].__APP__;
        
        $this->assign('pre_path', $pre_path);
        $this->assign('local_id', $local_id);
        $this->display();
    }

    public function save_map_center(){
        $this->need_right_to_admin($_POST['local_id']);
        $local = O('local_map')->find($_POST['local_id']);
        $config = json_decode($local['config'], true);
        $config['map_center'] = $_POST['center'];
        O('local_map')->with('id', $_POST['local_id'])->save(array('config'=>json_encode($config)));
        echo 'ok';
    }
    
    // post content
    
    function post_add($local_id, $content_id){
        $local_map_model = new LocalMapModel();
        $local_map = $local_map_model->find($id);
        $module = M('local_modules')->find($content_id);
        
        $post = array();
        $post['local_id'] = $local_id;
        $post['key'] = $content_id;
        
        $this->assign('local_map', $local_map);
        $this->assign('module', $module);
        
        $this->assign('post', $post);
        $this->display();
    }
    
    function post_edit($id){
        $post = O('local_content')->find($id);
        $this->assign('post', $post);
        $this->assign('target', 'post_save');
        $this->display('post_add');
    }
    
    function post_delete($id){
        $this->need_right_to_edit($id);
        
        O('local_content')->with('id', $id)->delete();
        $this->redirect('post_list', array('local_id'=>$old_post['local_id'], 'content_id'=>$old_post['key']));
    }
    
    function post_save(){
        $local_content = O('local_content');
        $post = $local_content->create();
        
        $this->need_right_to_edit($post['id']);
        
        $local_content->save();
        $this->redirect('post_view', array('local_id'=>$post['local_id'], 'content_id'=>$post['key'], 'post_id'=>$post['id']));
    }
    
    function post_insert(){
        $local_content_model = new LocalContentModel();
        if($this->has_right_to_admin($_POST['local_id'])){
            $is_checked = 1;
        }
        else{
            $is_checked = 0;
        }
        $post_id = $local_content_model->add(array(
            'local_id' => $_POST['local_id'],
            'name' => $_POST['name'],
            'content' => $_POST['description'],
            'key' => $_POST['key'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'is_checked' => $is_checked,
            'users_id' => user('id'),
        ));
        if(!$is_checked){
            flash('投稿文章在审核之后会出现在列表中');
        }
        
        $this->redirect('post_view', array('local_id'=>$_POST['local_id'], 'content_id'=>$_POST['key'], 'post_id'=>$post_id));
    }
    
    function post_view($post_id){
        $local_content_model = new LocalContentModel();
        $post = $local_content_model->find($post_id);
        
        $this->assign('local_id', $post['local_id']);
        $this->assign('post', $post);
        $this->display();
    }

    /*
        @param $local_id: the id of local map in local_map table;
        @param $content_id: the category of the content: the "key" field of the local_content table
    */
     public function post_list($local_id, $content_id){
        $local_map_model = new LocalMapModel();
        $local_content_model = new LocalContentModel();

        $local_map = $local_map_model->find($local_id);
        $query_map = array(
                'local_id' => $local_id,
                'key' => $content_id,
            );
            
        if(isset($_GET['q'])){
            $query_map['_complex'] = array(
                    'name' => array('like', '%'.$_GET['q'].'%'),
                    'content' => array('like', '%'.$_GET['q'].'%'),
                    '_logic' => 'or',
                );
        }
        import("@.Classes.TBPage");
        $listRows = C('ADMIN_ROW_LIST');
        $post_count = $local_content_model->where($query_map)->count();
        $posts = O('local_content')->where($query_map)->attach('users')->select();
        $Page = new TBPage($post_count,$listRows);
        $page_bar = $Page->show();
        
        $module = M('local_modules')->find($content_id);
    
        $this->assign('local_map', $local_map);
        $this->assign('module', $module);
        $this->assign('posts', $posts);
        $this->assign('page_bar', $page_bar);
        $this->assign('has_right_to_admin', $this->has_right_to_admin());
        $this->display();
    }
    
    public function post_stick($id){
        $this->need_right_to_edit($id);
        $post = O('local_content')->find($id);
        $max_sortkey = O('local_content')->with('key', $post['key'])->max('sortkey');
        $post['sortkey'] = $max_sortkey+1;
        O('local_content')->save($post);
        echo 'ok';
    }
    
    public function post_unstick($id){
        $this->need_right_to_edit($id);
        $post = O('local_content')->find($id);
        $post['sortkey'] = 0;
        O('local_content')->save($post);
        echo 'ok';
    }
    
    public function post_audit($id){
        $this->need_right_to_edit($id);
        $post = O('local_content')->find($id);
        $post['is_checked'] = 1;
        O('local_content')->save($post);
        echo 'ok';
    }
    
    public function post_unaudit($id){
        $this->need_right_to_edit($id);
        $post = O('local_content')->find($id);
        $post['is_checked'] = 0;
        O('local_content')->save($post);
        echo 'ok';
    }
    
    // module editors
    
    public function module_edit($local_id){
        $this->need_right_to_admin($local_id);
        $modules = O('local_modules')->with('local_id', $local_id)->order('sortkey')->select();
        
        $this->assign('local_map', O('local_map')->find($local_id));
        $this->assign('modules', $modules);
        $this->assign('module_types', C('LOCAL_MODULES'));
        $this->display();
    }
    
    public function act_module_add($local_id, $name, $type){
        $this->need_right_to_admin($local_id);
        
        $new_id = O('local_modules')->add(array(
            'local_id' => $local_id,
            'name' => $name,
            'type' => $type,
        ));
        echo $new_id;
    }
    
    public function act_module_save($id, $name, $type){
        $module = O('local_modules')->find($id);
        $this->need_right_to_admin($module['local_id']);
        
        O('local_modules')->with('id', $id)->save(array(
            'id' => $id,
            'name' => $name,
            'type' => $type,
        ));
        
        echo 'ok';
    }

    public function act_module_delete($id){
        $module = O('local_modules')->find($id);
        $this->need_right_to_admin($module['local_id']);
        
        O('local_modules')->with('id', $id)->delete();
        
        echo 'ok';
    }
    
    public function act_module_change_order($new_order){
        $orders = explode(',', $new_order);
        for($i=0;$i<count($orders);$i++){
            O('local_modules')->save(array(
                'id' => $orders[$i],
                'sortkey' => $i,
            ));
        }
        
        echo 'ok';
    }
    
    public function map_widget($local_id){
        $local_map = O('local_map')->find($local_id);
        $default_map_center = array('lng'=>'', 'lat'=>'', 'zoom'=>'');
        $config = json_decode($local_map['config'], true);
        if(!empty($_GET['center_lng'])){
            $default_map_center = array('lng'=>$_GET['center_lng'], 'lat'=>$_GET['center_lat'], 'zoom'=>$_GET['center_zoom']);
        }
        else if(isset($config['map_center'])){
            $map_center_config = explode(',', $config['map_center']);
            $default_map_center['lng'] = $map_center_config[0];
            $default_map_center['lat'] = $map_center_config[1];
            $default_map_center['zoom'] = $map_center_config[2];
        }
        $this->assign('default_map_center', $default_map_center);
        $this->assign('local_map', $local_map);
        $this->display('_map_widget');
    }
    
    public function _post_widget($local_id, $module_info){
        $local_content_model = new LocalContentModel();
        $results = $local_content_model->where(array(
            'local_id'=>$local_id,
            'key'=>$module_info['id'],
            'is_checked'=>1,
        ))->limit(C('RECORD_PER_POST_WIDGET'))->select();
        
        $this->assign('local_id', $local_id);
        $this->assign('module_info', $module_info);
        $this->assign('posts', $results);
        $this->display('_post_widget');   
    }
    
    public function _mapdata_widget($local_id, $module_info){
        $local_map = O('LocalMap')->find($local_id);
        $province = $local_map['province'];
        
        switch($module_info['type']){
            case 'ngo':
                $submit_link = U('User/register').'/type/ngo';
                $detail_link_before_id = U('User/view').'/id';
                $more_link = U('Index/list_index').'/province/'.$province.'/type/ngo/model/users';
                $results = O('Users')->with('type','ngo')->with('is_checked',1)->with('province',$province)
                    ->order('create_time desc')->limit(C('RECORD_PER_POST_WIDGET'))->select();
                break;
            case 'event':
                $submit_link = U('Event/add').'/type/ngo';
                $detail_link_before_id = U('Event/view').'/id';
                $more_link = U('Index/list_index').'/province/'.$province.'/type/ngo/model/events';
                $results = O('Events')->with('type','ngo')->with('province',$province)
                    ->with('is_checked',1)->with('enabled',1)
                    ->order('create_time desc')->limit(C('RECORD_PER_POST_WIDGET'))->select();
                break;
            case 'csr':
                $submit_link = U('Event/add').'/type/csr';
                $detail_link_before_id = U('Event/view').'/id';
                $more_link = U('Index/list_index').'/province/'.$province.'/type/csr/model/events';
                $results = O('Events')->with('type','csr')->with('province',$province)
                    ->with('is_checked',1)->with('enabled',1)
                    ->order('create_time desc')->limit(C('RECORD_PER_POST_WIDGET'))->select();
                break;
            case 'case':
                $submit_link = U('Event/add').'/type/case';
                $detail_link_before_id = U('Event/view').'/id';
                $more_link = U('Index/list_index').'/province/'.$province.'/type/case/model/events';
                $results = O('Events')->with('type','case')->with('province',$province)
                    ->with('is_checked',1)->with('enabled',1)
                    ->order('create_time desc')->limit(C('RECORD_PER_POST_WIDGET'))->select();
                break;
            
        }
        
        $this->assign('submit_link', $submit_link);
        $this->assign('detail_link_before_id', $detail_link_before_id);
        $this->assign('more_link', $more_link);
        $this->assign('local_id', $local_id);
        $this->assign('module_info', $module_info);
        $this->assign('results', $results);
        $this->display('_mapdata_widget');   
    }

    public function _content_sidebar($local_id, $content_id=0){
        $local_map_model = new LocalMapModel();
        $local_map = $local_map_model->find($local_id);
        $modules = M('LocalModules')->where(array('local_id'=>$local_id))->select();

        
        $this->assign('modules', $modules);
        $this->assign('content_id', $content_id);
        $this->assign('local_map', $local_map);
        $this->assign('has_right_to_admin', $this->has_right_to_admin());
        $this->display('_content_sidebar');
    }
    
    private function need_right_to_edit($post_id){
        //security check
        $old_post = O('local_content')->find($post_id);
        $old_local_map = O('local_map')->find($old_post['local_id']);
        if($old_post['user_id']!=user('id') && $old_local_map['admin_id']!=user('id') && !user('is_admin')){
            die('您没有权限修改此信息');
        }
    }
    
    private function need_right_to_admin($local_id){
        if($this->has_right_to_admin($local_id)){
            return true;
        }
        else{
            die('您没有权限管理此二级地图');
        }
    }
    
    private function has_right_to_admin($local_id){
        $local_map = O('local_map')->find($local_id);
        if(!empty($local_map) && (user('is_admin') || $local_map['admin_id']==user('id'))){
            return true;
        }
        else{
            return false;
        }
    }

}
?>