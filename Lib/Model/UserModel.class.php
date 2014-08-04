<?php

class UserModel extends QnDModel{

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

    function get_event_media($user_id){
        $sql = "select * from media where type='image' and event_id in (select id from event where user_id=".escape_sql($user_id).')';
        return $this->query($sql);
    }
    
    function recommend($user, $number=4){
        $fields = preg_split("/[\s,]+/", escape_sql($user['work_field'])); 
        $model = new Model();
        $sql = "select id, image, name, 0";
        foreach($fields as $field){
            $sql .= "-if(work_field like '%$field%',1,0)";
        }
        $sql .= " score from user where type='ngo' and is_checked=1 and enabled=1 order by score limit $number";
        return $model->query($sql);
    }
}