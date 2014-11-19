<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminActionclass
 *
 * @author 王全斌
 */
class AdminAction extends BaseAction{

    //彻底从数据库删除用户数据
    public function deleteUser(){
        $this->userMayEditUser($_GET['id']);
        $user_model = M('User');
        $del = $user_model->where(array('id'=>$_GET['id']))->delete();

        $this->redirect('users', array("hold_page"=>1));
    }

    public function change_type(){
        $this->userMayEditUser($_GET['id']);
        $user_model = M('User');
        $user_model->create();
        $del = $user_model->where(array('id'=>$_GET['id']));
        $del->type = $_GET['type'];
        $del->save();

        echo 'ok';
    }

    public function setvip(){
        $this->userMayEditUser($_GET['id']);
        $user_model = M('User');
        if($_GET['status']){
            $status = 1;
        }
        else{
            $status = 0;
        }

        $user_model->where(array('id'=>$_GET['id']))->data(array('is_vip'=>$status))->save();
        $this->redirect('users', array("hold_page"=>1));
    }

    public function check_unique(){
        $user=M('User');
        $user->create();
        $email = $_GET['email'];
        $u = $user->where(array('email'=>array('eq',$email)))->count();
        if($u!=0){
            echo 'false';
        }
        else{
            echo 'true';
        }
    }
    
    public function events(){
        $this->needToBeAdmin();
        $event_model = D('Event');
        
        //从session中读取搜索条件
        if(isset($_SESSION['admin_events_condition'])){
            $admin_events_condition = $_SESSION['admin_events_condition'];
            unset($admin_events_condition['p']);
        }
        else{
            $admin_events_condition = array('type'=>'all', 'check'=>'all');
        }
        //用传入的搜索条件覆盖现有的搜索条件
        //XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
        foreach($_GET as $key=>$value){
            $admin_events_condition[$key] = $value;
        }
        if($_GET['q'] == 'all'){
            $admin_events_condition['q'] = '';
        }
        //保存搜索条件
        $_SESSION['admin_events_condition'] = $admin_events_condition;
        extract($admin_events_condition);
        
        //筛选
        $where_clause = array();
        if($type != 'all'){
            $where_clause['type'] = $type;
        }
        if($check == 'deleted'){
            $where_clause['enabled'] = 0;
        }
        else if($check == 'pending'){
            $where_clause['is_checked'] = 0;
            $where_clause['enabled'] = 1;
        }
        else if($check == 'checked'){
            $where_clause['is_checked'] = 1;
            $where_clause['enabled'] = 1;
        }
        if(!empty($q)){
            $where_clause['name'] = array('like', "%$q%");
        }
        
        import("@.Classes.TBPage");
        $listRows = C('ADMIN_ROW_LIST');
        $event_count = $event_model->where($where_clause)->count();
        $Page = new TBPage($event_count,$listRows);
        $event_result = $event_model->where($where_clause)->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();

        //fetch user name for each event
        $user_ids = array();
        foreach($event_result as $e){
            if(!empty($e['user_id'])){
                $user_ids[$e['user_id']] = 1;
            }
        }
        $user_model = new UserModel();
        $related_users = $user_model->query("select id,name from user where id in (".implode(',', array_keys($user_ids)).")");
        foreach($related_users as $r){
            $user_ids[$r['id']] = $r['name'];
        }
        for($i=0;$i<count($event_result);$i++){
            $event_result[$i]['creator_name'] = $user_ids[$event_result[$i]['user_id']];
        }



        $page_bar = $Page->show();
    
        $this->assign('q', $q);
        $this->assign('check', $check);
        $this->assign('type', $type);
        $this->assign('event_result', $event_result);
        $this->assign('page', $page_bar);
        $this->display();
    }
    
    public function users(){
        $this->needToBeAdmin();
        $user_model = M('User');
        
        //从session中读取搜索条件
        if(isset($_SESSION['admin_users_condition']) && !isset($_GET['clear'])){
            $admin_users_condition = $_SESSION['admin_users_condition'];
            if(isset($_GET['hold_page']) && !isset($_GET['p'])){
                $_GET['p'] = $admin_users_condition['p'];
            }
        }
        else{
            $admin_users_condition = array('type'=>'all', 'check'=>'all');
        }
        //用传入的搜索条件覆盖现有的搜索条件
        //XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
        foreach($_GET as $key=>$value){
            $admin_users_condition[$key] = $value;
        }
        if($_GET['q'] == 'all' || $_GET['q']===''){
            $admin_users_condition['q'] = '';
        }
        //保存搜索条件
        $_SESSION['admin_users_condition'] = $admin_users_condition;
        extract($admin_users_condition);
        
        //筛选
        $where_clause = array();
        if($type != 'all'){
            $where_clause['type'] = $type;
        }
        if($check == 'deleted'){
            $where_clause['enabled'] = 0;
        }
        else if($check == 'pending'){
            $where_clause['is_checked'] = 0;
            $where_clause['enabled'] = 1;
        }
        else if($check == 'checked'){
            $where_clause['is_checked'] = 1;
            $where_clause['enabled'] = 1;
        }
        else{
            $where_clause['enabled'] = 1;
        }
        if(!empty($q)){
            $where_clause['name'] = array('like', "%$q%");
        }
        
        import("@.Classes.TBPage");
        $listRows = C('ADMIN_ROW_LIST');
        $user_count = $user_model->where($where_clause)->count();
        $Page = new TBPage($user_count,$listRows);
        $user_result = $user_model->where($where_clause)->order('create_time desc')->limit($Page->firstRow.','.$listRows)->select();
        
        $page_bar = $Page->show();
    
        $this->assign('q', $q);
        $this->assign('check', $check);
        $this->assign('type', $type);
        $this->assign('user_result', $user_result);
        $this->assign('page', $page_bar);
        $this->display();
    }

    //事件管理函数
    public function batch(){
        if($_GET['type'] == 'users'){
            $model = M('User');
            $model_word = '用户';
        }
        else{
            $model = M('Event');
            $model_word = '事件';
        }
        $ids=explode(",",$_GET['ids']);
        foreach($ids as $id){
            if($_GET['type'] == 'users'){
                $this->userMayEditUser($id);
            }
            else{
                $this->userMayEditEvent($id);
            }
        }
        $action=$_GET['action'];
        $type=$_GET['type'];    //ATTENTION: this 'type' indicates where to redirect. Only use 'events' or 'users'

        if($action=='lock'){
            $data['is_checked']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            // if it is a user, lock all the events
            if($_GET['type'] == 'users'){
                O('event')->where(array('user_id'=>array('in',$ids)))->save($data);
            }
            flash(L("您已成功屏蔽所选$model_word"), 'success');
        }
        else if($action=='check'){
            $data['is_checked']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            // 对审核通过的用户发送电子邮件
            if($_GET['type'] == 'users'){
                $users = O('User')->where(array('id'=>array('in',$ids)))->select();
                // 取得所有的账户id，再从账户表取得注册时填写的电子邮件
                $account_ids = extract_field($users, 'account_id');
                $accounts = O('Account')->where(array('id'=>array('in',$account_ids)))->select();
                foreach($accounts as $account){
                    OO('Mailer')->to($account['email'])->withSubject('公益机构审核通过!')->withContent(C('pass_check_email_tmpl'))->send();
                }
            }
            // if it is a user, unlock all the events
            if($_GET['type'] == 'users'){
                O('event')->where(array('user_id'=>array('in',$ids)))->save($data);
            }
            flash("您已成功审核所选$model_word", 'success');
        }
        else if($action=='recovery'){
            $data['enabled']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功恢复所选$model_word"), 'success');
        }
        else if($action=='del'){
            $data['enabled']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功删除所选$model_word"), 'success');
        }
        else if($action=='erase'){
            $model->where(array('id'=>array('in',$ids)))->delete();
            flash(L("您已彻底删除所选$model_word"), 'success');
        }
        else if($action=='add_v'){
            $data['is_vip']='1';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功加V所选$model_word"), 'success');
        }
        else if($action=='del_v'){
            $data['is_vip']='0';
            $model->where(array('id'=>array('in',$ids)))->save($data);
            flash(L("您已成功取消加V所选$model_word"), 'success');
        }

        //update search index
        if($action == 'check' || $action == 'recovery'){
            $items = $model->where(array('id'=>array('in',$ids)))->select();
            if($_GET['type'] == 'users'){
                $type = 'user';
            }
            else{
                $type = 'event';
            }
            foreach($items as $item){
                OO('XSearch')->index($type, $item['id'], $item['name'], $item['intro']);
            }
        }
        else if($action == 'lock' || $action == 'del'){
            if($_GET['type'] == 'users'){
                $type = 'user';
            }
            else{
                $type = 'event';
            }
            foreach($ids as $id){
                OO('XSearch')->delete($type, $id);
            }
        }

        if(!empty($_GET['ajax'])){
            echo 'ok';
        }
        else{
            // $this->redirect($_GET['type']);
            $this->back();
        }
    }

    public function send_check_emails($user_id){
        $user_model = M("User");
        $user = $user_model->find($user_id);
        $this->assign("mail_user", $user);
        $mail_content = $this->fetch("check_email");
        $subject = "[审核成功]中国公益2.0欢迎您使用公益地图";
        $headers = "From: 公益地图 <no-reply@ngo20.org> \n";  
        $headers .= "To-Sender: \n";  
        $headers .= "X-Mailer: PHP\n"; // mailer  
        $headers .= "Reply-To: no-reply@ngo20.org\n"; // Reply address  
        $headers .= "Return-Path: no-reply@ngo20.org\n"; //Return Path for errors  
        $headers .= "Content-Type: text/html; charset=utf-8"; //Enc-type  
        mail($user['email'], $subject, $mail_content, $headers);
    }

    public function cover_pictures(){
        $this->needToBeAdmin();
        $hero_images = D('Setting')->read_json('hero_images');
        $this->assign('hero_images', $hero_images);
        $this->display();
    }

    public function set_as_cover(){
        O('Setting')->write_json('hero_images', $_POST);

        $this->redirect('Admin/cover_pictures');
    }

}
?>
