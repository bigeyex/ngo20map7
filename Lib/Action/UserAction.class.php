<?php

class UserAction extends Action{

    function view($id){
        $user = O('user')->find($id);
        $events = O('events')->with('user_id', $id)->select();
        $related_users = D('user')->recommend($user);
        
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        $this->display();
    }









}