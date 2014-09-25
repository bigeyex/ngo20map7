<?php

class AccountModel extends QnDModel{

    public function login($email, $pwd, $mode='password', $token=''){
        if(empty($pwd))return false;
        if($mode == 'password'){
            $result = $this->where(array('email' => $email, 'password' => md5($pwd)))->find();
        }
        else if($mode == 'api'){
            $api_type = $email;
            $result = $this->where(array('api_'.$api_type.'_id' => $pwd))->find();
            if(!$result){
                // create an empty user
                $result = array(
                        'api_'.$api_type.'_id' => $pwd,
                        'api_'.$api_type.'_token' => $token
                    );
                $result['id'] = O('account')->add($result);
                $_SESSION['login_user'] = $result;
                return true;
            }
            else{
                $result['api_'.$api_type.'_token'] = $token;
                $this->save($result);
            }
        }
        if(!$result || empty($result)){
            return false;
        }
        elseif($result['enabled'] == 0){
            return false;
        }
        else{
            //login successfully
            //fetch other user information
            $user_model = new UserModel();
            $user_data = $user_model->where(array('account_id'=>$result['id']))->find();
            if($user_data){
                $user_data['local_maps'] = O('local_map')->with('admin_id', $user_data['id'])->select();
                $_SESSION['login_user'] = array_merge($user_data, $result);
                $_SESSION['login_user']['id'] = $user_data['id'];
                $_SESSION['login_user']['user_id'] = $user_data['id'];
                $_SESSION['login_user']['name'] = $user_data['name'];
                $this->query("update user set login_count=login_count+1 where id=$user_id");
            }
            else{
                $_SESSION['login_user'] = $result;
                $_SESSION['login_user']['id'] = 0;
            }
            $_SESSION['login_user']['password'] = '******';
            $_SESSION['login_user']['account_id'] = $result['id'];

            $this->where(array('id'=>$result['id']))->data(array('last_login'=>date('Y-m-d h:i:s')))->save();
            return true;
        }
    }

    public function add_user($post){
        if(!isset($post['email'])){
            return "电子邮件必填";
        }
        else if(!isset($post['name'])){
            return '姓名必填';
        }
        else if(!isset($post['password'])){
            return '密码必填';
        }
        $account_count = $this->where(array('email'=>$post['email']))->count();
        if($account_count > 1){
            return '该电子邮件已经被注册，请换一个电子邮件或者直接登录';
        }
        $id = $this->add(array(
                'name' => $post['name'],
                'password' => md5($post['password']),
                'email' => $post['email']
            ));
        $this->login($post['email'], $post['password']);
        return $id;
    }
}

