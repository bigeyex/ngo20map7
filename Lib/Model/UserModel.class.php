<?php

class UserModel extends BaseModel{

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
    
    function recommend($user, $number=5){
        $fields = preg_split("/[\s,]+/", escape_sql($user['work_field'])); 
        $model = new Model();
        $sql = "select id, image, name, 0";
        foreach($fields as $field){
            $sql .= "-if(work_field like '%$field%',1,0)";
        }
        $sql .= " score from user where type='ngo' and is_checked=1 and enabled=1 order by score limit $number";
        return $model->query($sql);
    }

    function recommendByProvince($user, $number=5) {
        $fields = preg_split("/[\s,]+/", escape_sql($user['work_field']));
        $model = new Model();
        $sql = "select id, image, name, rating_score, 0";
        foreach($fields as $field){
            $sql .= "-if(work_field like '%$field%',1,0)";
        }
        $province = $user['province'];
        $sql .= " score from user where type='ngo' and is_checked=1 and enabled=1 and province='$province'";
        $sql .= " order by score limit $number";
        return $model->query($sql);
    }

    function recommendByRatingScore($user, $number=5) {
        $model = new Model();
        $sql = "select id, image, name, rating_level from user";
        $sql .= " where type='ngo' and is_checked=1 and enabled=1";
        $ratingScore = $user['rating_score'] ?: 0;
        $sql .= " order by abs(rating_score - $ratingScore)";
        $sql .= " limit $number";

        return $model->query($sql);
    }

    function countOrgs($province = null) {
        $model = new Model();
        $sql = "select count(1) c from user where type='ngo' and is_checked=1 and enabled=1";
        if (isset($province)) {
            $sql .= " and province='$province'";
        }
        $r = $model->query($sql);
        return empty($r) ? 0 : current(current($r));
    }

    function rankOrg($id, $province = null) {
        $model = new Model();
        $rankSql = "select id, @rank := @rank + 1 as rank from user u, (select @rank := 0) r";
        $rankSql .= " where type='ngo' and is_checked=1 and enabled=1";
        if ($province) {
            $rankSql .= " and province='$province'";
        }
        $rankSql .= " order by rating_score";
        $sql = "select rank from ($rankSql) t where id = $id";
        $r = $model->query($sql);
        return empty($r) ? 0 : current(current($r));
    }
}