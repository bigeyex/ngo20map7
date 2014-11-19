<?php


class Setting{

    public function write($k, $v){
        $count = M('Settings')->where(array('k'=>$k))->count();
        if($count == 0){
            M('Settings')->add(array('k'=>$k,'v'=>$v));
        }
        else{
            M('Settings')->where(array('k'=>$k))->data(array('v'=>$v))->save();
        }
    }

    public function read($k){
        $record = M('Settings')->where(array('k'=>$k))->find();
        return $record;
    }

    public function write_json($k, $v){
        $this->write($k, json_encode($v, true));
    }

    public function read_json($k){
        return json_decode($this->read($k));
    }

}