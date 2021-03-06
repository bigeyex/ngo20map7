<?php

class AccountAction extends BaseAction{

	// views
	function dashboard(){
		$this->display();
	}

	public function login(){
		$account_model = new AccountModel();
		if($account_model->login($_POST['email'], $_POST['password'])){

			//处理自动登录
            if(!empty($_POST['remember'])){
            	$login_email = user('email');
            	$login_key = md5($login_email . rand(0,10000) .time() . SALT_KEY);
            	$login_token = md5($login_key . SALT_KEY . user('password'));
            	setcookie("ngo20_login_email", $login_email, time()+3600*24*14);
            	setcookie("ngo20_login_key", $login_key, time()+3600*24*14);
            	setcookie("ngo20_login_token", $login_token, time()+3600*24*14);
            }
            
            
            echo 'ok';
        }
		else{
			//login failed
			echo '用户名或密码不正确';
		}
	}


    public function email_register(){
        if($_POST['password'] != $_POST['password_again']){
            echo '两次输入密码不一致!';
            return;
        }
        $account_model = new AccountModel();
        $id = $account_model->add_user($_POST);
        if(is_numeric($id)){
            echo 'ok';
        }
        else{
            echo $id;
        }
    }

    public function login_redirect(){
        if(isset($_SESSION['next_mission'])){
            $mission = $_SESSION['next_mission'];
            unset($_SESSION['next_mission']);
            $this->redirect($mission);
        }
        if(user('is_admin')){
            $this->redirect('Admin/users');
        }
        $user_count = O('user')->with('account_id', user('id'))->count();
        if($user_count == 0){
            $this->redirect('User/add');    // if no organization created, redirect to create org page.
        }
        else{
            $this->redirect('Account/dashboard');
        }
    }

    public function forgot_password(){
        $this->display();
    }

    public function forgot_password_action(){
        if(empty($_POST['email'])){
            $this->redirectWithError('电子邮件地址不能为空！');
        }
        $account = O('Account')->where(array('email' => $_POST['email']))->find();
        if(empty($account)){
            flash(L('输入的电子邮件地址不匹配'));
            $this->back();
        }
        if($account['name'] != $_POST['name']){
            $users = O('user')->with('account_id', $account['id'])->select();
            $flag = false;
            foreach($users as $user){
                if($user['name'] == $_POST['name']){
                    $flag = true;
                    break;
                }
            }
            if(!$flag){
                flash('输入的机构名称和电子邮件地址不匹配');
                $this->back();
            }

        }
        
        $user_id = $account['id'];
        $expDate = date('Y-m-d H:i:s',strtotime("+3 Day",time()));
        $key = md5($account['id'] . rand(0,10000) .$expDate . $salt);
        $issue_count = O('forget_password')->where(array('user_id'=>$user_id, 'expire_date'=>array('lt', 'now()')))->count();
        if($issue_count > 0){
            flash(L('已经发送过找回密码邮件'));
            $this->back();
        }
        O('forget_password')->data(array(
            'user_id' => $user_id,
            'link' => $key,
            'expire_date' => $expDate
        ))->add();

        $passwordLink = 'http://' . $_SERVER['HTTP_HOST'] . __APP__ . "/Account/reset_password/key/$key";
        $mailer = OO('Mailer')->to($account['email'])->withSubject('公益地图找回密码')
                        ->formatContent(C('forget_password_email_tmpl'), array('link' => $passwordLink));
        if($mailer->send()){
            flash('找回密码邮件已发送，请注意查收', 'success');
        }
        else{
            flash('找回密码邮件发送失败');
        }

        $this->redirect('Index/index');
    }

    function reset_password(){
        $issue_count = O('forget_password')->where(array('link' => $_GET['key'], 'expire_date'=>array('gt', date('Y-m-d H:i:s'))))->count();
        if($issue_count <= 0){
            flash(L('找回密码链接不正确，请访问完整链接地址'));
            $this->redirect('Index/index');
        }
        $this->display();
    }
    
    function reset_password_action(){
        $issue = O('forget_password')->where(array('link' => $_POST['key'], 'expire_date'=>array('gt', date('Y-m-d H:i:s'))))->find();
        if(!$issue){
            $this->redirectWithError('密码重置链接不正确');
        }
        $new_password = $_POST['password'];
        O('account')->where(array('id'=>$issue['user_id']))->data(array('password'=>md5($new_password)))->save();
        O('forget_password')->where(array('id'=>$issue['id']))->delete();
        flash(L('密码修改成功'), 'success');
        $this->redirect('Index/index');
    }

	public function logout(){
		unset($_SESSION['login_user']);
        unset($_SESSION['last_page']);
        unset($_SESSION['last_page']);
        unset($_SESSION['api']);
        setcookie("ngo20_login_email", "", time()-3600);
        setcookie("ngo20_login_key", "", time()-3600);
        setcookie("ngo20_login_token", "", time()-3600);
        $this->redirect('Index/index');
	}

    public function change_password(){
        $api_weibo_id = user('api_weibo_id');
        $api_qq_id = user('api_qq_id');
        if(empty($api_weibo_id) && empty($api_qq_id) && $_POST['new_password'] != $_POST['new_password_again']){
            flash('密码修改失败：两次输入的密码不一致');
            $this->redirect('Account/settings');
            return;
        }
        if(!(user('account_id'))){
            flash('登录用户才可以修改密码');
            $this->redirect('Account/settings');
            return;
        }

        $account_count = O('Account')->with('id', user('account_id'))->with('password', md5($_POST['old_password']))->count();
        if($account_count <= 0){
            flash('旧密码输入不正确');
            $this->redirect('Account/settings');
            return;
        }

        $data = array('password' => md5($_POST['new_password']));
        if(isset($_POST['email'])){
            $data['email'] = $_POST['email'];
        }
        O('Account')->with('id', user('account_id'))->save($data);
        flash('成功修改密码', 'success');
        $this->redirect('Account/settings');
    }

	public function qq_login(){
        $code = $_GET['code'];
        $openid = $_GET['openid'];
        $openkey = $_GET['openkey'];
        $redirect_uri = "http://" . $_SERVER['HTTP_HOST'] . __APP__ . "/Account/qq_login";

        $client_id = C('QQ_APPKEY');
        $client_secret = C('QQ_APPSECRET');
        $access_token = '';
        $expires_in = '';

        $request_uri = "https://open.t.qq.com/cgi-bin/oauth2/access_token?client_id=$client_id&client_secret=$client_secret&redirect_uri=$redirect_uri&grant_type=authorization_code&code=$code";
        if($result = file_get_contents($request_uri)){
            //parse param from qq response
            foreach(explode('&', $result) as $block){
                $param = explode('=', $block);
                if($param[0] == "access_token") $access_token = $param[1];
                if($param[0] == "expires_in") $expires_in = $param[1];
            }

            //save param to session
            $api = array();
            $api['api_vendor'] = 'qq';
            $api['api_id'] = $openid;
            $api['api_openkey'] = $openkey;
            $api['api_token'] = $access_token;
            $_SESSION['api'] = $api;

            //check if new user
            $account_model = new AccountModel();

            if(user() && isset($_GET['assoc'])){
                $account_id = user('account_id');
                if(empty($account_id)){
                    die('用户登录信息错误');
                }
                O('Account')->with('id', user('account_id'))
                    ->save(array('api_qq_id'=>$openid, 'api_qq_token'=>$openkey));
                user('api_qq_id',$openid);
                user('api_qq_token', $openkey);
                $this->redirect('Account/settings');
            }
            else if($account_model->login('qq', $openid, 'api', $openkey)){
                $this->login_redirect();
            }
            else{
                die('登录错误');
            }
        }
    }

    public function weibo_login(){
        $code = $_GET['code'];
        $redirect_uri = "http://" . $_SERVER['HTTP_HOST'] . __APP__ . "/Api/weibo_login";

        $client_id = C('WEIBO_APPKEY');
        $client_secret = C('WEIBO_APPSECRET');
        $access_token = '';
        $expires_in = '';
        $api_id = '';

        $request_uri = "https://api.weibo.com/oauth2/access_token?client_id=$client_id&client_secret=$client_secret&grant_type=authorization_code&redirect_uri=$redirect_uri&code=$code";
        $opts = array('http' =>
                array(
                        'method'  => 'POST',
                        'header'  => "Content-Type: text/xml\r\n"                       
                )
        );
        $context = stream_context_create($opts);
        if($result = file_get_contents($request_uri,false,$context)){
            //parse param from weibo response
            $token = json_decode($result, true);
            $access_token = $token['access_token'];
            $expires_in = $token['expires_in'];

            $api_id = $token['uid'];
            //save param to session
            $api = array();
            $api['api_vendor'] = 'weibo';
            $api['api_id'] = $api_id;
            $api['api_token'] = $access_token;
            $_SESSION['api'] = $api;

            //check if new user
            $account_model = new AccountModel();

            if(user() && isset($_GET['assoc'])){
                $account_id = user('account_id');
                if(empty($account_id)){
                    die('用户登录信息错误');
                }
                O('Account')->with('id', user('account_id'))
                    ->save(array('api_weibo_id'=>$api_id, 'api_weibo_token'=>$access_token));
                user('api_weibo_id',$api_id);
                user('api_weibo_token', $access_token);
                $this->redirect('Account/settings');
            }
            else if($account_model->login('weibo', $api_id, 'api', $access_token)){
                $this->login_redirect();
            }
            else{
                die('登录错误');
            }
        }   //get access token
        else {die ('get access token failed');};
    }

    private function http_post($request_uri, $username, $password){
        $url = $request_uri;
        $ch = curl_init($url);
         
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "client_id: $username",
            "client_secret: $password"
        ));
         
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }


	// partials
	function partial_nav_bar(){
		$this->display('partials:navbar');
	}

	function partial_login_box(){
		$this->display('partials:loginbox');
	}

	function partial_register_box(){
		$this->display('partials:register');
	}



}