<?php



class FormAction extends BaseAction{

    public function index(){
        $this->needLoggedIn();

        $forms = M('Form')->where(array(
            'account_id' => user('account_id')
            ))->select();

        $this->assign('forms', $forms);
        $this->display();
    }

    public function edit($form_id, $page=1){
        
        if(!user() || !user('user_id')){
            flash(L('请先登记公益机构'));
            $this->redirect('User/add');
            return;
        }

        if(empty($form_id)) $this->redirectWithError('出错了');

        $form = D('Form')->find($form_id);
        if(!isset($form['form_data'])) $this->redirectWithError('出错了,请联系管理员');

        $fdata = $form['form_data'];
        if($page==1){
            if(empty($fdata['org_name'])) $fdata['org_name']=user('name');
            if(empty($fdata['org_province'])) $fdata['org_province']=user('province');
            if(empty($fdata['org_city'])) $fdata['org_city']=user('city');
            if(empty($fdata['org_contact'])) $fdata['org_contact']=user('contact_name');
            if(empty($fdata['org_phone'])) $fdata['org_phone']=user('phone');
            if(empty($fdata['org_email'])) $fdata['org_email']=user('public_email');
            if(empty($fdata['org_website'])) $fdata['org_website']=user('website');
            if(empty($fdata['org_weibo'])) $fdata['org_weibo']=user('weibo');

        }

        $this->assign('form', $fdata);
        $this->display('chuangtou_page1');
    }

    public function save(){
        
    }

    public function add(){
        $this->needLoggedIn();
        $form_data = array(
            'account_id' => user('account_id'),
            'user_id' => user('user_id'),
            'title' => '公益创投大赛申请',
            'step_id' => 0,
            'form' => '{}',
            'create_time' => date('Y-m-d H:i:s'),
            'edit_time' => date('Y-m-d H:i:s')
            );

        $form_id = O('Form')->add($form_data);
        $this->redirect('edit', array('form_id'=>$form_id));
    }

    public function delete($form_id){
        $this->needLoggedIn();
        M('Form')->where(array(
            'account_id' => user('account_id'),
            'id' => $form_id
            ))->delete();
        $this->redirect('index');
    }

}