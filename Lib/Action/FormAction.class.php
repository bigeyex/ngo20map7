<?php



class FormAction extends BaseAction{

    public function index(){

    }

    public function chuangtou_add(){
        if(!user() || !user('user_id')){
            flash(L('请先登记公益机构'));
            $this->redirect('User/add');
            return;
        }

        $this->display();
    }

}