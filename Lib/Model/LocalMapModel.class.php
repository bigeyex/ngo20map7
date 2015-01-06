<?php

class LocalMapModel extends BaseModel{
    public function add_local_map(){
        
    }

    public function byUserId($userId){
        return $this->with('admin_id', $userId)->select();
    }
}