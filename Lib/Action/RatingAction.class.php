<?php
class RatingAction extends BaseAction
{

    public function ratings($work_field = "", $location = "", $keyword = "", $rating_level = null, $offset = 0, $order=0) {
        $userModel = O("user");

        $data = "type = 'ngo' and rating_level IS NOT NULL and is_checked=1 and enabled=1 ";
        if(isset($work_field) && $work_field != "") {
            $data .= " and MULTI_FIND_IN_SET('" . $work_field . "', work_field)";
        }
        if(isset($location) && $location != "") {
            $data .= " and (province like '" . $location ."%' or city like '" . $location ."%')";
        }
        if(isset($keyword) && $keyword != "") {
            $data .= " and (name like '%" . $keyword ."%' or intro like '%$keyword%')";
        }
        if(isset($rating_level) && $rating_level != "") {
            $data .= " and FIND_IN_SET(rating_level, '" . $rating_level . "')";
        }
        $limit = C("LIST_RECORD_PER_PAGE");

        if(!isset($offset) || $offset == 0) {
            $total = $userModel->where($data)->count();
        }

        if($order == 0) {
            $orderStr = "rating_score desc, id";
        } else {
            $orderStr = "isnull(register_year), length(register_year) < 4, str_to_date(register_year, '%Y'), isnull(register_month), str_to_date(register_month, '%M')";
        }

        $ratings = $userModel->where($data)->order($orderStr)->limit($offset, $limit)
            ->field('id, account_id, name, image, register_year, register_month, gov_level, 
                province, register_type, work_field, rating_level')
            ->select();

        $ret = array('ratings' => $ratings, 'total' => $total);

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
