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
        if($event && ($event['account_id']==0 || $event['account_id']==user('account_id'))){
            return;
        }
        $this->redirectWithError('需要先登录或权限不足');
    }

    protected function needLoggedIn(){
        if(!user() || !intval(user('account_id'))){
            $this->redirectWithError('需要先登录');
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

    public function error($message='无法找到页面'){
        $this->assign('message', $message);
        $this->display();
    }

}