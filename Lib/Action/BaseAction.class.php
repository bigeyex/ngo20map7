<?php


class BaseAction extends Action{
    protected function userMayEditEvent($id){
        if(!user()){
            $this->redirectWithError('需要先登录');
        }
        if(user('is_admin')){
            return;
        }
        $event = O('event')->find($id);
        $event_location = O('event_location')->with('event_id', $id)->find();
        if($event && ($event['account_id']==0 || $event['account_id']==user('account_id'))){
            return;
        }
        if($event_location){
            $local_maps = O('local_map')->with('admin_id', user('id'))->select();
            foreach($local_maps as $local){
                if(strpos($event_location['province'], $local['province']) !== FALSE || strpos($event_location['city'], $local['province']) !== FALSE){
                    return;
                }
            }
        }
        $this->redirectWithError('需要先登录或权限不足');
    }

    protected function needLoggedIn(){
        if(!user() || !intval(user('account_id'))){
            $this->redirectWithError('需要先登录');
        }
    }

    protected function needToBeAdmin(){
        if(!user() || !user('is_admin')){
            $this->redirectWithError('权限不足');
        }
    }

    protected function userMayEditUser($id){
        if(!user()){
            $this->redirectWithError('需要先登录');
        }
        if(user('is_admin')){
            return;
        }
        $user = O('user')->find($id);
        if($user && ($user['account_id']==0 || $user['account_id']==user('account_id'))){
            return;
        }
        // check if user is the local admin of another user
        $local_maps = O('local_map')->with('admin_id', user('id'))->select();
        foreach($local_maps as $local){
            if(strpos($user['province'], $local['province']) !== FALSE || strpos($user['city'], $local['province']) !== FALSE){
                return;
            }
        }
        $this->redirectWithError('需要先登录或权限不足');
    }

    protected function redirectWithError($message=''){
        if($this->isAjax()){
            die($message);
        }
        else{
            $this->redirect('Base/error', array('message'=>$message));
            die();
        }
    }

    protected function back(){
        redirect($_SERVER["HTTP_REFERER"]);
    }

    protected function setTitle($title){
        $this->assign('page_title', $title);
    }

    protected function setKeywords($keywords){
        $this->assign('page_keywords', $keywords);
    }

    protected function setDescription($desc){
        $this->assign('page_description', $desc);
    }

    public function error($message='无法找到页面'){
        $this->assign('message', $message);
        $this->display();
    }

}