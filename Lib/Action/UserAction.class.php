<?php
define('PAGE_BASIC_INFO',1);
define('PAGE_PHOTOS',2);
define('PAGE_CONTACT_INFO',3);
define('PAGE_MORE_INFO',4);
define('PAGE_USER_PASSWORD',5);

class UserAction extends BaseAction{
    function view($id){
        $user = O('user')->find($id);
        $events = O('event')->with('user_id', $id)->fetch('event_location')->select();
        // concate and attach longitude and latitude
        for($i=0;$i<count($events);$i++){
            $lngs = array();
            $lats = array();
            foreach($events[$i]['event_location'] as $location){
                $lngs[] = $location['longitude'];
                $lats[] = $location['latitude'];
            }
            $events[$i]['lngs'] = implode(',', $lngs);
            $events[$i]['lats'] = implode(',', $lats);
        }
        $related_users = O('user')->recommend($user);
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        $this->display();
    }

    function add(){

        if(user('user_id')){
            $this->redirect('User/edit');
        }
        $this->display();
    }

    function edit($id=0, $p=0){
        if($id == 0){
            if(!user('user_id')){
                $this->redirect('User/add');
            }
            $id = user('user_id');
        }
        $this->userMayEditUser($id);
        $user = O('user')->find($id);

        if(!user){
            $this->redirectWithError('无法正常打开项目/活动');
        }

        // get all photos
        if($p == PAGE_PHOTOS){
            $images = O('media')->where(array('user_id'=>$id, 'type'=>'image'))->select();
            $this->assign('images', $images);
        }

        //calculate completion
        $completion = array(
                PAGE_CONTACT_INFO => $this->isSectionCompleted($user, 3, 
                    array('contact_name', 'phone', 'public_email', 'website', 'weibo')),
                PAGE_MORE_INFO => $this->isSectionCompleted($user, 5, 
                    array('service_area', 'register_year', 'register_type', 'documented_year', 'staff_fulltime',
                            'staff_parttime', 'staff_volunteer', 'financial_link', 'fund_source'))

            );
        $image_count = O('media')->where(array('user_id'=>$id, 'type'=>'image'))->count();
        if($image_count > 0){
            $completion[PAGE_PHOTOS] = true;
        }
        else{
            $completion[PAGE_PHOTOS] = false;
        }
        // if edit tab is not specified, set it to the nearest uncompleted part.
        if($p == 0){
            foreach($completion as $k=>$v){
                if(!$v){
                    $p = $k;
                    break;
                }
            }
            $p = 1;
        }

        if($p == PAGE_USER_PASSWORD){
            $account = O('Account')->find($user['account_id']);
            $user['email'] = $account['email'];
        }
        $this->assign('with_notification', true);
        $this->assign('completion', $completion);
        $this->assign('user', $user);
        $this->assign('p', $p);
        $this->display();
    }


    function insert(){
        $this->needLoggedIn();
        $user_model = O('user');
        $user_model->create();
        $user_model->create_time = date('Y-m-d H:i:s');
        $user_model->account_id = user('account_id');

        $new_id = $user_model->add();
        if(!$new_id){
            $this->redirectWithError('登记公益机构失败');
        }
        $user_data = $user_model->find($new_id);

        // update all images
        $media_model = O('media');
        foreach($_POST['images'] as $image){
            $media_model->add(array(
                'url' => $image,
                'user_id' => $new_id,
                'type' => 'image'
            ));
        }

        // attach all events of current user to this ngo
        O('event')->with('account_id', user('account_id'))->save(array('user_id'=>$new_id));
        O('event')->updateMediaUserId($new_id);

        // update cover image
        $image = O('media')->with('user_id', $new_id)->with('type', 'image')->find();
        if($image){
            O('user')->save(array(
                'id' => $new_id,
                'cover_img' => $image['url']
            ));
        }

        // update login data with current ngo
        $_SESSION['login_user'] = array_merge($user_data, $_SESSION['login_user']);
        $_SESSION['login_user']['id'] = $user_data['id'];
        $_SESSION['login_user']['user_id'] = $user_data['id'];
        $_SESSION['login_user']['name'] = $user_data['name'];

        $this->redirect('User/edit', array('id'=>$new_id));
    }

    function save(){
        $this->userMayEditUser($_POST['id']);
        if(isset($_POST['password'])){
            $user = O('user')->find($_POST['id']);
            $account = O('account')->find($user['account_id']);
            if($account){
                $account['email'] = $_POST['email'];
                $account['password'] = md5($_POST['password']);
                O('account')->save($account);
            }
            else{
                flash('修改登录凭据失败');
                $this->back();
            }
        }
        else{
            $user = O('user');
            $user->create();
            $user->save();
        }

        flash('机构信息已更新', 'success');
        $this->back();
    }

    function addUserPhoto(){
        $this->userMayEditUser($_POST['user_id']);
        O('media')->add(array(
            'url' => $_POST['url'],
            'user_id' => $_POST['user_id'],
            'type' => 'image'
        ));
        // if user does not have cover image, set it to the cover image
        $user = O('user')->find($_POST['user_id']);
        if(empty($user['cover_img'])){
            $user['cover_img'] = $_POST['url'];
            O('user')->save($user);
        }
        echo 'ok';
    }

    function deleteUserPhoto(){
        $this->userMayEditUser($_POST['user_id']);
        $media = O('Media')->with('user_id', $_POST['user_id'])
                           ->with('url', $_POST['url'])->find();
        // if the cover image of the user is THIS image, change for a next one
        $user = O('user')->find($_POST['user_id']);
        if($user['cover_img'] == $_POST['url']){
            $next_media = O('Media')->with('type', 'image')->with('user_id', $_POST['user_id'])->find();
            if($next_media){
                $user['cover_img'] = $next_media['url'];
                O('user')->save($user);
            }
        }
        O('Media')->with('id', $media['id'])->delete();
        echo 'ok';
    }

    function setCoverPhoto(){
        $this->userMayEditUser($_POST['user_id']);
        O('user')->with('id', $_POST['user_id'])->save(array('cover_img'=>$_POST['url']));
        echo 'ok';
    }


    // deprecated
    function coauthor($id){
        O('account_user')->add(array(
                'account_id' => user('id'),
                'user_id' => $id
            ));
        $this->redirect('Account/dashboard');
    }



    function ajax_check_name($name){
        $user = O('user')->with('name', $name)->find();
        if($user){
            echo $user['id'];
        }
    }

    public function ajax_suggest($q, $page=1){
        $record_per_page = 10;
        $user_model = new UserModel();
        $result = $user_model->field('id,name text')->where(array('name' => array('like', "%$q%")))->limit($record_per_page)->select();
        echo json_encode($result);
    }

    public function getUserCompletion($id){
        $user = O('user')->find($id);
        if(!$this->isSectionCompleted($user, 3, 
                    array('contact_name', 'phone', 'public_email', 'website', 'weibo'))){
            return array('msg'=>'联系方式', 'p'=>PAGE_CONTACT_INFO);
        }
        else if(!$this->isSectionCompleted($user, 5, 
                    array('service_area', 'register_year', 'register_type', 'documented_year', 'staff_fulltime',
                            'staff_parttime', 'staff_volunteer', 'financial_link', 'fund_source'))){
            return array('msg'=>'信息披露', 'p'=>PAGE_MORE_INFO);
        }
        $image_count = O('media')->where(array('user_id'=>$id, 'type'=>'image'))->count();
        if($image_count <= 0){
            return array('msg'=>'机构图片', 'p'=>PAGE_PHOTOS);
        }
        
        return false;   
    }


    private function isSectionCompleted($user, $minCriteria, $sections){
        $filled = 0;
        foreach($sections as $section){
            if(!empty($user[$section])){
                $filled++;
            }
        }
        if($filled>=$minCriteria){
            return true;
        }
        else{
            return false;
        }
    }



}