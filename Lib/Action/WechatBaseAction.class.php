<?php
    import("@.Classes.CachedWechat");

    class WechatBaseAction extends BaseAction{

        protected $wechat = null;

        protected function wechatInit(){
            if($this->wechat !== null) return;
            $wechatOptions = array(
                'token' => C('WECHAT_TOKEN'),
                'encodingaeskey' => C('WECHAT_AESKEY'),
                'appid' => C('WECHAT_APPID'),
                'appsecret' => C('WECHAT_APPSECRET'),
            );
            $this->wechat = new CachedWechat($wechatOptions);
        }

        protected function getJsSign(){
            // try to retrive accessToken and jsapiTicket
            $this->wechatInit();
            return $this->wechat->getJsSign("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        }

        protected function redirectWithOpenID($state=0){
            $appid = C('WECHAT_APPID');
            $redirect_uri = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            if(!isset($_SESSION['wechat_openid']) && !isset($_GET['code'])){
                $authorize_uri = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=$state#wechat_redirect";
                redirect($authorize_uri);
            }
            else if(!isset($_SESSION['wechat_openid'])){
                $appsecret = C('WECHAT_APPSECRET');
                $code = $_GET['code'];
                $userinfo_request_uri = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
                $userinfo = json_decode(file_get_contents($userinfo_request_uri), true);
                if(!empty($userinfo['openid'])){
                    $_SESSION['wechat_openid'] = $userinfo['openid'];
                    // try logging in
                    $account_model = new AccountModel();
                    $account_model->login('wechat', $userinfo['openid'], 'api');
                }
                else{
                    $this->redirectWithError(L('不能使用微信登录'));
                }
            }
        }
    }
