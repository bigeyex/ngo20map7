<?php

function by_date_sorter($a, $b){
    $column = 'create_time';
    $datea = date($a[$column]);
    $dateb = date($b[$column]);
    if($datea == $dateb) return 0;
    return ($a > $b) ? -1 : 1;
}

class DashboardAction extends BaseAction{

    // views
    function index(){
        if(user('id') != 0){
            $user_action = new UserAction();
            $this->assign('user_info_completion', $user_action->getUserCompletion(user('id')));
        }

        // get recent update feed.
        $five_events = O('event')->active_only()->order('create_time desc')->attach('user')->limit(5)->select();
        $five_users = O('user')->active_only()->order('create_time desc')->limit(5)->select();
        $feeds = array_merge($five_events, $five_users);
        usort($feeds, "by_date_sorter");
        $this->assign('feeds', $feeds);

        // get the last time user post a new event
        $event = O('event')->with('user_id', user('id'))->order('create_time desc')->find();
        $one_month_ago = date('Y-m-d H:i:s', strtotime('-1 month'));
        if(!$event || $event['create_time'] < $one_month_ago){
            $this->assign('event_too_old', true);
        }   

        $this->display();
    }



}