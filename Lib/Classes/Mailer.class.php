<?php


class Mailer{
    protected $mail_to;
    protected $subject;
    protected $content;

    function to($address){
        $this->mail_to = $address;
        return $this;
    }

    function withSubject($subject){
        $this->subject = $subject;
        return $this;
    }

    function withContent($content){
        $this->content = $content;
        return $this;
    }

    function formatContent($content, $format){
        foreach($format as $k => $v){
            $content = str_replace('{{'.$k.'}}', $v, $content);
        }
        $this->content = $content;

        return $this;
    }

    function send(){
        if(class_exists('Redis')){
            $redis = new Redis();
            $redis->connect('127.0.0.1',6379);

            $info = array();
            $info['to'] = $this->mail_to;
            $info['subject'] = $this->subject;
            $info['content'] = $this->content;

            if($ret = $redis->publish( 'mail_channel_km' , serialize($info) )){
                return true;
            }
            else {
                return false;
            }
        }
    }

}