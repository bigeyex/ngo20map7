<?php
    define(HUZHU_ITEMS_PER_PAGE, 20);
    class HuZhuAction extends WechatBaseAction{
        public function _initialize(){
          // TODO: added wechat auth function
          // TODO: remove DEMO DATA
          $_SESSION['login_user'] = array(
            'account_id' => 4176,
            'name' => '测试用户',
            'user_id' => 19184
          );
        }

        public function index(){
          // $this->assign('jsSignature', $this->getJsSign("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));
          $this->display();
        }

        public function insert() {
          $this->needLoggedIn();
          $huzhu_model = O('Huzhu');
          $huzhu_model->create();
          $huzhu_model->account_id = user('account_id');
          $huzhu_model->user_id = user('user_id');
          $huzhu_model->publish_date = date('Y-m-d');
          $new_id = $huzhu_model->add();
          if($new_id) {
            echo 'ok';
          }
          else{
            echo '发布失败';
          }
        }

        public function deleteWish($id) {
          $this->userShouldOwnWish($id);
          O('Huzhu')->with('id', $id)->delete();
          echo 'ok';
        }

        public function saveWish() {
          $this->userShouldOwnWish($_POST['id']);
          O('Huzhu')->with('id', $_POST['id'])->save(array(
            'content' => $_POST['content'],
            'category' => $_POST['category'],
            'expire_date' => $_POST['expire_date']
          ));
          echo 'ok';
        }

        public function query() {
          $page = place($_GET['page'], 1);
          $huzhu_model = O('huzhu');
          if(!empty($_GET['city'])) {
            $city_info = explode(' ', $_GET['city']);
            $huzhu_model = $huzhu_model->with('city', $city_info[count($city_info)-1]);
          }
          if(!empty($_GET['q'])) {
            $q = $_GET['q'];
            $huzhu_model = $huzhu_model->with('content', array('like', "%$q%"));
          }
          if(!empty($_GET['category'])) {
            $huzhu_model = $huzhu_model->with('category', $_GET['category']);
          }
          if(!empty($_GET['completed'])) {
            $huzhu_model = $huzhu_model->with('is_complete', 1);
          }
          if(!empty($_GET['noreply'])) {
            $huzhu_model = $huzhu_model->with('replies', 0);
          }
          if(!empty($_GET['unread'])) {
            // get a list of unread ids
            $unread_ids = O()->query('select distinct huzhu_id from huzhu_unread where account_id=' . intval(user('account_id')));
            $huzhu_model = $huzhu_model->with('id', array('in', extract_field($unread_ids, 'huzhu_id')));
          }
          if(!empty($_GET['my'])) {
            $huzhu_model = $huzhu_model->with('account_id', user('account_id'));
          }
          if(!empty($_GET['replied'])) {
            $replied_ids = O()->query('select distinct huzhu_id from huzhu_reply where account_id=' . intval(user('account_id')));
            $huzhu_model = $huzhu_model->with('id', array('in', extract_field($replied_ids, 'huzhu_id')));
          }
          if(!empty($_GET['liked'])) {
            $liked_ids = O()->query('select distinct huzhu_id from huzhu_like where account_id=' . intval(user('account_id')));
            $huzhu_model = $huzhu_model->with('id', array('in', extract_field($liked_ids, 'huzhu_id')));
          }
          $wishes = $huzhu_model->order('id desc')->limit(HUZHU_ITEMS_PER_PAGE*($page-1), HUZHU_ITEMS_PER_PAGE)->select();
          if(empty($wishes)) {
            $wishes = array();
          }
          for($i=0;$i<count($wishes);$i++) {
            if(empty($wishes[$i]['user_id'])) {
              $wishes[$i]['author'] = '';
              continue;
            }
            $user = O('User')->find($wishes[i]['user_id']);
            $wishes[$i]['author'] = empty($user)?'':$user['name'];
          }

          echo json_encode($wishes);
        }

        public function detail($id) {
          $wish = O('huzhu')->find($id);
          if(!$wish){
            echo "{error:'error'}";
            return;
          }
          $wish['replyList'] = O('huzhu_reply')->with('huzhu_id', $id)->select();
          // get is liked or not
          $wish['isLiked'] = O('huzhu_like')->with('huzhu_id', $id)->with('account_id', user('account_id'))->count();
          // get all replies and attach to wish object
          for($i=0;$i<count($wish['replyList']);$i++) {
            $reply = $wish['replyList'][$i];
            $wish['replyList'][$i]['author'] = '';
            if(!empty($reply['account_id'])){
              $account = O('account')->find($reply['account_id']);
              if(!empty($account)){
                $wish['replyList'][$i]['author'] = $account['name'];
                $reply_user = O('user')->find($reply['account_id']);
                if($reply_user){
                  $wish['replyList'][$i]['author'] = $reply_user['name'];
                }
              }
            }
          }
          echo json_encode($wish);
        }

        private function addUnread($huzhu_id, $account_id) {
          $this->needLoggedIn();
          O('huzhu_unread')->add(array('huzhu_id'=>$huzhu_id, 'account_id'=>$account_id));
        }

        public function postReply() {
          $this->needLoggedIn();
          if(empty($_POST['huzhu_id'])) return;
          $reply_id = $this->insertReplyRecord($_POST['huzhu_id'], $_POST['content']);
          echo $reply_id;
        }

        private function insertReplyRecord($huzhu_id, $reply_content) {
          $reply_model = O('huzhu_reply');
          $reply_model->account_id = user('account_id');
          $reply_model->huzhu_id = $huzhu_id;
          $reply_model->content = $reply_content;
          $reply_model->publish_date = date('Y-m-d');
          $new_id = $reply_model->add();

          // issue notice to the author and all repliers.
          $huzhu = O('huzhu')->find($huzhu_id);
          $huzhu_replies = O('huzhu_reply')->with('huzhu_id', $huzhu_id)->select();
          if($huzhu['account_id'] != user('account_id')) {
            $this->addUnread($huzhu['id'], $huzhu['account_id']);
          }
          foreach($huzhu_replies as $reply) {
            if($reply['account_id'] != user('account_id') && $reply['account_id'] != $huzhu['account_id']) {
              $this->addUnread($huzhu['id'], $reply['account_id']);
            }
          }

          return $new_id;
        }

        public function deleteReply($id) {
          $reply = O('huzhu_reply')->find($id);
          if(user() && $reply['account_id'] == user('account_id')) {
            O('huzhu_reply')->with('id', $id)->delete();
            echo 'ok';
          }
        }

        public function like($id, $status) {
          $this->needLoggedIn();
          if($status) {
            O('huzhu_like')->add(array(
              'huzhu_id' => $id,
              'account_id' => user('account_id')
            ));
          }
          else{
            O('huzhu_like')->with('huzhu_id', $id)->with('account_id', user('account_id'))->delete();
          }
          echo 'ok';
        }

        public function markOpen($id, $status) {
          $this->userShouldOwnWish($id);
          O('huzhu')->with('id', $id)->save(array('is_open'=>$status));
          if($status) {
            $this->insertReplyRecord($id, '[打开心愿]');
          }
          else{
            $this->insertReplyRecord($id, '[关闭心愿]');
          }
          echo 'ok';
        }

        public function markCompleted($id, $status) {
          $this->userShouldOwnWish($id);
          O('huzhu')->with('id', $id)->save(array('is_complete'=>$status));
          if($status) {
            $this->insertReplyRecord($id, '[标记为完成]');
          }
          else{
            $this->insertReplyRecord($id, '[标记为未完成]');
          }
          echo 'ok';
        }

        public function linkAccount() {
          // login and fetch user in session. Delete temp account if needed
        }

        public function userShouldOwnWish($wishId) {
          $wish = O('Huzhu')->find($wishId);
          if(!empty($wishId) && $wish['account_id'] == user('account_id')) {
            return;
          }
          else{
            echo '权限不足';
            die();
          }
        }
    }
