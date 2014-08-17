<?php

class UserAction extends Action{

    function view($id){
        $user = O('user')->find($id);
        $events = O('event')->with('user_id', $id)->select();
        $related_users = O('user')->recommend($user);
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        $this->display();
    }

    /* 
        actions
    */
    // apply to co-author an organization
    function insert(){
        $user_model = O('user');
        $user_model->create();
        print_r($user_model);die();
    }

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





}