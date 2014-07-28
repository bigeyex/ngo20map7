<?php

class EventAction extends Action{

    function add(){
        $this->display();
    }

    function view($id){
    	$event = O('event')->find($id);
    	$user = O('user')->find($event['user_id']);
    	$media = O('media')->with('event_id', $event['id'])->select();
        $related_users = O('user')->recommend($user);

        $this->assign('user', $user);
        $this->assign('event', $event);
        $this->assign('related_users', $related_users);
        $this->display();
    }







}