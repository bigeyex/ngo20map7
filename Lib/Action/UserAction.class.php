<?php

class UserAction extends Action{

    function view($id){
        $user = O('user')->find($id);
        $events = O('event')->with('user_id', $id)->select();
        $related_users = O('user')->recommend($user);
        $event_media = O('user')->get_event_media($user['id']);

        $this->assign('event_media', $event_media);
        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        $this->display();
    }









}