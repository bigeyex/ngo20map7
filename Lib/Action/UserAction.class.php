<?php
define('PAGE_BASIC_INFO', 1);
define('PAGE_PHOTOS', 2);
define('PAGE_CONTACT_INFO', 3);
define('PAGE_MORE_INFO', 4);
define('PAGE_USER_PASSWORD', 5);

class UserAction extends BaseAction
{
    function view($id = 0)
    {
        if ($id === 0 && user()) {
            $id = user('id');
        }
        if (!$id) {
            $this->redirectWithError('我不知道你看的是哪个机构啊');
        }
        $user = O('user')->find($id);
        $events = O('event')->with('user_id', $id)->fetch('event_location')->limit(20)->select();
        // concate and attach longitude and latitude
        for ($i = 0; $i < count($events); $i++) {
            $lngs = array();
            $lats = array();
            foreach ($events[$i]['event_location'] as $location) {
                $lngs[] = $location['longitude'];
                $lats[] = $location['latitude'];
            }
            $events[$i]['lngs'] = implode(',', $lngs);
            $events[$i]['lats'] = implode(',', $lats);
        }
        if (!isMobile()) {
            $related_users = O('user')->recommend($user);
        }

        $medals = O('medal')->order('score desc')->select();
        $user_medal = O('medalmap')->with('user_id', $id)->select();
        $medal_arr = array();
        foreach ($user_medal as $map) {
            $medal_arr[] = $map['medal_id'];
        }
        tmpl_global('title', $user['name']);

        // load comments
        $comments = O('comment')->with('user_id', $id)->with('is_visible', true)->select();
        $this->assign('comments', $comments);

        $this->assign('medals', $medals);
        $this->assign('medal_list', $medal_arr);

        $photos = O('media')->with('type', 'image')->with('user_id', $id)->limit(20)->select();
        $this->assign('user_photos', $photos);

        $this->assign('user', $user);
        $this->assign('events', $events);
        $this->assign('related_users', $related_users);
        if (isMobile()) {
            $this->display('m_view');
        } else {
            $this->display();
        }

    }

    function add()
    {

        if (user('user_id')) {
            $this->redirect('User/edit');
        }
        $this->display();
    }

    function edit($id = 0, $p = 0)
    {
        if ($id == 0) {
            if (!user('user_id')) {
                $this->redirect('User/add');
            }
            $id = user('user_id');
        }
        $this->userMayEditUser($id);
        $user = O('user')->find($id);

        if (!user) {
            $this->redirectWithError('无法正常打开项目/活动');
        }

        // get all photos
        if ($p == PAGE_PHOTOS) {
            $images = O('media')->where(array('user_id' => $id, 'type' => 'image'))->select();
            $this->assign('images', $images);
        }

        //calculate completion
        $completion = array(
            PAGE_CONTACT_INFO => $this->isSectionCompleted($user, 3,
                array('contact_name', 'phone', 'public_email', 'website', 'weibo')),
            PAGE_MORE_INFO => $this->isSectionCompleted($user, 5,
                array('service_area', 'register_year', 'register_type', 'documented_year', 'staff_fulltime',
                    'staff_parttime', 'staff_volunteer', 'financial_link', 'fund_source'))

        );
        $image_count = O('media')->where(array('user_id' => $id, 'type' => 'image'))->count();
        if ($image_count > 0) {
            $completion[PAGE_PHOTOS] = true;
        } else {
            $completion[PAGE_PHOTOS] = false;
        }
        // if edit tab is not specified, set it to the nearest uncompleted part.
        if ($p == 0) {
            foreach ($completion as $k => $v) {
                if (!$v) {
                    $p = $k;
                    break;
                }
            }
            $p = 1;
        }

        if ($p == PAGE_USER_PASSWORD) {
            $account = O('Account')->find($user['account_id']);
            $user['email'] = $account['email'];
        }
        $this->assign('with_notification', true);
        $this->assign('completion', $completion);
        $this->assign('user', $user);
        $this->assign('p', $p);
        $this->display();
    }

    function calcScore($info)
    {
        $ruleMap = array(
            'work_field' => array(
                'score' => 2.91,
                'type' => 'range',
                'pre-process' => function($v) { return count(explode(',', $v));},
                'conds' => array(
                    3 => 1,
                    5 => 0.9,
                    7 => 0.5,
                    PHP_INT_MAX => 0
                )
            ),
            'register_year' => array(
                'score' => 2.34,
                'type' => 'range',
                'pre-process' => function($v) { return date('Y') - $v;},
                'conds' => array(
                    5 => 0.2,
                    PHP_INT_MAX => 1
                )
            ),
            'register_type' => array(
                'score' => 3.97,
                'type' => 'match',
                'conds' => array(
                    '民非注册' => 1,
                    '工商注册' => 0.8,
                    '机构挂靠' => 0.6,
                    '虚拟组织' => 0.4
                )
            ),
            'staff_fulltime' => array(
                'score' => 1.96,
                'type' => 'match',
                'conds' => array(
                    '20人及以上' => 1,
                    '10~19人' => 0.8,
                    '10人以下' => 0.5,
                )
            ),
            'member_experience' => array(
                'score' => 10.32,
                'type' => 'match',
                'conds' => array(
                    '3年以下' => 0.3,
                    '3-5年' => 0.5,
                    '5-10年' => 0.8,
                    '10年以上' => 1
                )
            ),
            'lead_experience' => array(
                'score' => 6.72,
                'type' => 'match',
                'conds' => array(
                    '3年以下' => 0.3,
                    '3-5年' => 0.5,
                    '5-10年' => 0.8,
                    '10年以上' => 1
                )
            ),
            'accountant_status' => array(
                'score' => 3.71,
                'type' => 'match',
                'conds' => array(
                    "全职" => 1,
                    "兼职" => 0.5,
                    "没有" => 0
                )
            ),
            'info_platform' => array(
                'score' => 2.52,
                'type' => 'range',
                'except' => '暂无',
                'conds' => array(
                    1 => 0.5,
                    2 => 0.8,
                    3 => 1
                )
            ),
            'org_conds' => array(
                'score' => 3.16,
                'type' => 'range',
                'except' => '以上都没有',
                'conds' => array(
                    1 => 0.5,
                    2 => 0.8,
                    3 => 1
                )
            ),
            'has_board' => array(
                'score' => 4.09,
                'type' => 'match',
                'conds' => array(
                    0 => 0,
                    1 => 1
                )
            ),
            'has_plan' => array(
                'score' => 5.30,
                'type' => 'match',
                'conds' => array(
                    0 => 0,
                    1 => 1,
                )
            ),
            'has_report' => array(
                'score' => 4.09,
                'type' => 'match',
                'conds' => array(
                    0 => 0,
                    1 => 1,
                )
            ),
            'project_scale' => array(
                'score' => 17.92,
                'type' => 'match',
                'conds' => array(
                    '20万以下' => 0,
                    '20万-50万' => 0.5,
                    '50万-100万' => 0.8,
                    '100万及以上' => 1.0
                )
            ),
            'participant_scale' => array(
                'score' => 4.70,
                'type' => 'match',
                'conds' => array(
                    '500人以下' => 0.2,
                    '501-2000人' => 0.5,
                    '2001-5000人' => 0.6,
                    '5001-10000人' => 0.8,
                    '10000人以上' => 1.0
                )
            ),
            'service_area' => array(
                'score' => 4.70,
                'type' => 'match',
                'conds' => array(
                    '组织所在的社区' => 0.6,
                    '组织所在的城市' => 0.7,
                    '组织所在的省份' => 0.8,
                    '涵盖几个省份' => 0.9,
                    '全国范围都有' => 1.0
                )
            ),
            'media_report' => array(
                'score' => 5.29,
                'type' => 'match',
                'conds' => array(
                    '100次以上' => 1,
                    '51-100' => 0.8,
                    '21-50' => 0.6,
                    '6-20' => 0.4,
                    '1-5' => 0.2,
                    '0' => 0
                )
            ),
            'fund_info' => array(
                'score' => 7.41,
                'type' => 'function',
                'function' => function ($value) {
                    if (strpos($value, '未获得资助') !== false) {
                        return 0;
                    }
                    $count = count(explode(',', $value));
                    if ($count >= 3)
                        return 1;
                    if (strpos($value, '企业'))
                        return 0.8;
                    return 0.6;
                }
            ),
            'has_reward' => array(
                'score' => 4.52,
                'type' => 'match',
                'conds' => array(
                    1 => 1,
                    0 => 0
                )
            ),
            'gov_level' => array(
                'score' => 4.38,
                'type' => 'match',
                'conds' => array(
                    '5A' => 1,
                    '4A' => 0.9,
                    '3A' => 0.8,
                    '2A' => 0.7,
                    'A' => 0.6,
                    '0' => 0
                )
            ),
        );

        $score = 0;
        foreach ($info as $name => $value) {
            $rule = $ruleMap[$name];
            if (!isset($rule)) continue;
            switch ($rule['type']) {
                case 'match':
                    $ratio = $rule['conds'][$value];
                    if (!isset($ratio)) $ratio = 0;
                    $score += $rule['score'] * $ratio;
                    break;
                case 'range':
                    $except = $rule['except'];
                    if (isset($except) && strpos($value, $except)) {
                        continue;
                    }
                    $preProcess = $rule['pre-process'];
                    if (isset($preProcess)) {
                        $value = $preProcess($value);
                    }
                    foreach ($rule['conds'] as $level => $ratio) {
                        if ($level >= $value) {
                            $score += $rule['score'] * $ratio;
                            break;
                        }
                    }
                    break;
                case 'function':
                    $f = $rule['function'];
                    $score += $rule['score'] * $f($value);
                    break;
                default:
                    break;
            }
        }

        return $score;
    }

    function insert()
    {
        $this->needLoggedIn();
        $user_model = O('user');
        $user_model->create();
        if (!user('is_admin')) {
            $user_model->is_admin = 0;
        }
        $user_model->create_time = date('Y-m-d H:i:s');
        $user_model->account_id = user('account_id');
        $user_model->email = user('email');

        $new_id = $user_model->add();
        if (!$new_id) {
            $this->redirectWithError('登记公益机构失败');
        }
        $user_data = $user_model->find($new_id);

        // update all images
        $media_model = O('media');
        foreach ($_POST['images'] as $image) {
            $media_model->add(array(
                'url' => $image,
                'user_id' => $new_id,
                'type' => 'image'
            ));
        }

        // attach all events of current user to this ngo
        O('event')->with('account_id', user('account_id'))->save(array('user_id' => $new_id));
        O('event')->updateMediaUserId($new_id);

        // update cover image
        $image = O('media')->with('user_id', $new_id)->with('type', 'image')->find();
        if ($image) {
            O('user')->save(array(
                'id' => $new_id,
                'cover_img' => $image['url']
            ));
        }

        $score = $this->calcScore($_POST);
        $Rating = M('rating');
        $data['account_id'] = user('account_id');
        $data['score'] = $score;
        $Rating->add($data);

        // update login data with current ngo
        $_SESSION['login_user'] = array_merge($user_data, $_SESSION['login_user']);
        $_SESSION['login_user']['id'] = $user_data['id'];
        $_SESSION['login_user']['user_id'] = $user_data['id'];
        $_SESSION['login_user']['name'] = $user_data['name'];

//        $this->redirect('User/edit', array('id' => $new_id));
        $this->redirect('Rating/rating');
    }

    function save()
    {
        $this->userMayEditUser($_POST['id']);
        if (isset($_POST['password'])) {
            $user = O('user')->find($_POST['id']);
            $account = O('account')->find($user['account_id']);
            if ($account) {
                $account['email'] = $_POST['email'];
                $account['password'] = md5($_POST['password']);
                O('account')->save($account);
            } else {
                flash('修改登录凭据失败');
                $this->back();
            }
        } else {
            $user = O('user');
            $user->create();
            if (!user('is_admin')) {
                $user->is_admin = 0;
            }
            $user->save();
            $user = O('user')->find($_POST['id']);
            if ($user['is_checked']) {
                OO('XSearch')->index('user', $_POST['id'], $user['name'], $user['intro']);
            }
        }

        flash('机构信息已更新', 'success');
        $this->back();
    }

    function addUserPhoto()
    {
        $this->userMayEditUser($_POST['user_id']);
        O('media')->add(array(
            'url' => $_POST['url'],
            'user_id' => $_POST['user_id'],
            'type' => 'image'
        ));
        // if user does not have cover image, set it to the cover image
        $user = O('user')->find($_POST['user_id']);
        if (empty($user['cover_img'])) {
            $user['cover_img'] = $_POST['url'];
            O('user')->save($user);
        }
        echo 'ok';
    }

    function deleteUserPhoto()
    {
        $this->userMayEditUser($_POST['user_id']);
        $media = O('Media')->with('user_id', $_POST['user_id'])
            ->with('url', $_POST['url'])->find();
        // if the cover image of the user is THIS image, change for a next one
        $user = O('user')->find($_POST['user_id']);
        if ($user['cover_img'] == $_POST['url']) {
            $next_media = O('Media')->with('type', 'image')->with('user_id', $_POST['user_id'])->find();
            if ($next_media) {
                $user['cover_img'] = $next_media['url'];
                O('user')->save($user);
            }
        }
        O('Media')->with('id', $media['id'])->delete();
        echo 'ok';
    }

    function setCoverPhoto()
    {
        $this->userMayEditUser($_POST['user_id']);
        O('user')->with('id', $_POST['user_id'])->save(array('cover_img' => $_POST['url']));
        echo 'ok';
    }

    function ajaxLike($id)
    {
        $record = 'USERLIKE_' . $id;
        if (!isset($_SESSION[$record]) && !isset($_COOKIE[$record])) {
            $_SESSION[$record] = true;
            setcookie($record, 1);
            O('user')->with('id', $id)->setInc('like_count');
            echo 'ok';
        } else {
            echo '已经点过赞了';
        }
    }

    function add_comment()
    {
        if (empty($_POST['content'])) {
            echo '内容不能为空';
            return;
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo '电子邮件格式不正确';
            return;
        }

        if (!is_numeric($_POST['user_id'])) {
            echo '用户信息出错';
            return;
        }

        O('comment')->add(array(
            'content' => $_POST['content'],
            'user_id' => $_POST['user_id'],
            'sender' => $_POST['email'],
            'sender_name' => $_POST['sender_name'],
        ));
        echo 'ok';
    }

    function manage_comments()
    {
        $comments = O('comment')->with('user_id', user('id'))->select();

        $this->assign('comments', $comments);
        $this->display();
    }

    function reply_comment()
    {
        $comment = O('comment')->find($_POST['id']);
        if (!$comment) {
            echo L('数据出错');
            return;
        }
        if (user('id') != $comment['user_id']) {
            echo L('登录已过期，请重新登录');
            return;
        }

        $comment['reply'] = $_POST['content'];
        $comment['is_visible'] = 1;
        O('comment')->save($comment);
        echo 'ok';
    }

    function delete_comment()
    {
        $comment = O('comment')->find($_POST['id']);
        if (!$comment || $comment['user_id'] != user('id')) {
            echo L('数据出错');
            return;
        }
        O('comment')->with('id', $_POST['id'])->delete();
        echo 'ok';
    }

    // deprecated
    function coauthor($id)
    {
        O('account_user')->add(array(
            'account_id' => user('id'),
            'user_id' => $id
        ));
        $this->redirect('Account/dashboard');
    }


    function ajax_check_name($name)
    {
        $user = O('user')->with('name', $name)->find();
        if ($user) {
            echo $user['id'];
        }
    }

    public function ajax_suggest($q, $page = 1)
    {
        $record_per_page = 10;
        $user_model = new UserModel();
        $result = $user_model->field('id,name text')->where(array('name' => array('like', "%$q%")))->limit($record_per_page)->select();
        echo json_encode($result);
    }

    public function getUserCompletion($id)
    {
        $user = O('user')->find($id);
        if (!$this->isSectionCompleted($user, 3,
            array('contact_name', 'phone', 'public_email', 'website', 'weibo'))
        ) {
            return array('msg' => '联系方式', 'p' => PAGE_CONTACT_INFO);
        } else if (!$this->isSectionCompleted($user, 5,
            array('service_area', 'register_year', 'register_type', 'documented_year', 'staff_fulltime',
                'staff_parttime', 'staff_volunteer', 'financial_link', 'fund_source'))
        ) {
            return array('msg' => '信息披露', 'p' => PAGE_MORE_INFO);
        }
        $image_count = O('media')->where(array('user_id' => $id, 'type' => 'image'))->count();
        if ($image_count <= 0) {
            return array('msg' => '机构图片', 'p' => PAGE_PHOTOS);
        }

        return false;
    }


    private function isSectionCompleted($user, $minCriteria, $sections)
    {
        $filled = 0;
        foreach ($sections as $section) {
            if (!empty($user[$section])) {
                $filled++;
            }
        }
        if ($filled >= $minCriteria) {
            return true;
        } else {
            return false;
        }
    }


}