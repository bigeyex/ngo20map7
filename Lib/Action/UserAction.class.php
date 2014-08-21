<?php
define('PAGE_BASIC_INFO',1);
define('PAGE_PHOTOS',2);
define('PAGE_CONTACT_INFO',3);
define('PAGE_MORE_INFO',4);

class UserAction extends BaseAction{
    function view($id){
        $user = O('user')->find($id);
        $events = O('event')->with('user_id', $id)->select();
        $related_users = O('user')->recommend($user);
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        $this->display();
    }

    function add(){
        $this->needLoggedIn();
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
        }

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
        $user = O('user');
        $user->create();
        $user->save();

        $this->back();
    }

    function addUserPhoto(){
        $this->userMayEditUser($_POST['user_id']);
        O('media')->add(array(
            'url' => $_POST['url'],
            'user_id' => $_POST['user_id'],
            'type' => 'image'
        ));
        echo 'ok';
    }

    function deleteUserPhoto(){
        $this->userMayEditUser($_POST['user_id']);
        $media = O('Media')->with('user_id', $_POST['user_id'])
                           ->with('url', $_POST['url'])->find();
        
        O('Media')->with('id', $media['id'])->delete();
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