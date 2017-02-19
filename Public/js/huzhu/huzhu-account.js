window.HuZhu = window.HuZhu || {};
window.HuZhu.initAccount = function(){
  // ============== Link Account
  $('#publish-wish-tab-button').click(function(){
    // 现在不要求必须是地图用户
    // if(!window.user_id || window.user_id == 0) {
    //   $('#connect-account-popup').popup();
    // }
  });

  $('#cancel-link-account').click(function(){
    $.closePopup();
    if(!window.user_id || window.user_id == 0) {
      $('#all-wish-tab-button').click();
    }
  });

  $('#create-user-name').text(window.account_author_name);
  $('#link-account-action-button').click(function(){
    $.showLoading();
    $.post(app_path+'/HuZhu/linkAccount', {
      'username': $('#link-email-input').val(),
      'password': $('#link-password-input').val()
    }, function(result){
      if(result['error']){
        $.alert("用户名或密码错误", "提示");
        $.hideLoading();
      }
      else {
        window.user_id = result.user_id;
        window.account_author_name = result.name;
        $('#create-user-name').text(result.name);
        $.hideLoading();
        $.closePopup();
      }
    }, 'json');
  });

  if(window.get_detail_id) {
    showWishDetail({id: window.get_detail_id});
  }
}
