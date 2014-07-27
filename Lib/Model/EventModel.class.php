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
        return $this->where(array('province'=>array('like', "$key%")));
    }
    
    function active_only(){
        return $this->with('is_checked', 1)->with('enabled', 1);
    }


}