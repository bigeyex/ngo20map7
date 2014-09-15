<?php

class LocalMapModel extends QnDModel{
    public function add_local_map(){
        
    }

    public function byUserId($userId){
        return $this->with('admin_id', $userId)->select();
    }
}