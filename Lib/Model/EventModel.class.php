<?php

class EventModel extends QnDModel{

    function keyword($key){
        return $this->where(array('_complex'=>array(
                        'name' => array('like', "%$key%"),
                        'intro' => array('like', "%$key%"),
                        '_logic' => 'or'
                    )));
    }
    
    function province($key){
        return $this->where(array('province|city'=>array('like', "$key%")));
    }
    
    function active_only(){
        return $this->with('is_checked', 1)->with('enabled', 1);
    }

    function updateMediaUserId($user_id){
        $user_id = intval($user_id);
        $account_id = intval(user('account_id'));
        $this->query("update media set user_id=$user_id where event_id in (select id from event where account_id=$account_id)");
    }


}