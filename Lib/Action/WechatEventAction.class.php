<?php
    
    class WechatEventAction extends WechatBaseAction{
        public function nearby_events(){
//             echo $this->wechat->checkAuth();
            $this->redirectWithOpenID();
            echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            // $_SESSION['wechat_openid']
//             echo(json_encode($this->getJsSign(), true));
        }
    }