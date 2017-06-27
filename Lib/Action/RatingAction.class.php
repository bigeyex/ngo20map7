<?php
class RatingAction extends BaseAction
{

    public function ratings($work_field = "", $location = "", $rating_level = null, $offset = 0) {
        $userModel = O("user");

        $data = "type = 'ngo'";
        if(isset($work_field) && $work_field != "") {
            $data .= "and MULTI_FIND_IN_SET('" . $work_field . "', work_field)";
        }
        if(isset($location) && $location != "") {
            $data .= "and (province like '" . $location ."%' or city like '" . $location ."%')";
        }
        if(isset($rating_level) && $rating_level != "") {
            $data .= " and FIND_IN_SET(rating_level, '" . $rating_level . "')";
        }
        $limit = C("LIST_RECORD_PER_PAGE");

        $levelCounts = null;
        if(!isset($offset) || $offset == 0) {
            $offset = 0;
            $levelCounts = $userModel->where($data)->group("rating_level")->field('rating_level, count(1) as count')->select();
        }

        $ratings = $userModel->where($data)->order("rating_score desc, id")->limit($offset, $limit)->field('id, account_id, name, rating_level')->select();

        $ret = array('ratings' => $ratings, 'counts' => $levelCounts);

        return $this->ajaxReturn($ret);
    }

    public function view()
    {
        return $this->display();
    }

    public function rating() {
        $id = user('user_id');
        $user = O('user')->find($id);
        $this->assign('user', $user);
        return $this->display();
    }

}
