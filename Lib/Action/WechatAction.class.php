<?php
    
    class WechatAction extends Action{
        public function endpoint(){
            import('@.Classes.Wechat');
            $options = array(
                'token' => C('WECHAT_TOKEN'),
                'encodingaeskey' => C('WECHAT_AESKEY'),
            );
            $wechat = new Wechat($options);
            $type = $wechat->getRev()->getRevType();
            switch($type) {
                case Wechat::MSGTYPE_TEXT:
//                         $wechat->text("hello, I'm wechat")->reply();
                        break;
                case Wechat::MSGTYPE_EVENT:
                        $eventObject = $wechat->getRevEvent();
                        if($eventObject['event'] == Wechat::EVENT_SUBSCRIBE){
                            $wechat->news(array(array(
                                'Title' => '点击关联或创建机构',
                                'Description' => '欢迎使用公益地图！请先关联或创建机构',
                                'PicUrl' => 'http://www.ngo20map.com/Public/img/ngo20-logo-m.png',
                                'Url' => "http://$_SERVER[HTTP_HOST]".U('Wechat/link_account')
                            )))->reply();  
                        }
                        break;
                case Wechat::MSGTYPE_IMAGE:
                        break;
                default:
                        $wechat->text("help info")->reply();
            }
        }
        
        // provided by wechat
        private function checkSignature()
        {
            $signature = $_GET["signature"];
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];	
                		
            	$token = '28b4ffad818c823d16b38947d3eedc7e';
            	$tmpArr = array($token, $timestamp, $nonce);
            	sort($tmpArr, SORT_STRING);
            	$tmpStr = implode( $tmpArr );
            	$tmpStr = sha1( $tmpStr );
            	
            	if( $tmpStr == $signature ){
            		return true;
            	}else{
            		return false;
            	}
        }
    }