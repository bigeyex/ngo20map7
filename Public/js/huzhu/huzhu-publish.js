window.HuZhu = window.HuZhu || {};
window.HuZhu.initPublish = function(){
  // ============== Add HuZhu
  $('#publish-huzhu-button').click(function(){
    if(!$('#input-create-content').val()){
      $.toast('必须填写心愿内容', 'text');
    }
    else{
      var is_supply = 0;
      if($('#create-need-category').attr('data-values') == 'supply') {
        is_supply = 1;
      }
      $.post(app_path+'/HuZhu/insert', {
        city: $('#city-picker').val(),
        content: $('#input-create-content').val(),
        category: $('#create-need-type').val(),
        expire_date: $('#create-exp-date').val(),
        is_supply: is_supply
      }, function(result){
        if(result == 'ok'){
          $('#publish-success-popup').popup();
          $('#input-create-content').val('');
        }
      });
    }
  });

  // ============== Edit HuZhu
  $('#save-huzhu-button').click(function(){
    $.showLoading('保存中');
    if(!detailViewModel.content()){
      $.toast('必须填写心愿内容', 'text');
    }
    else{
      var is_supply = 0;
      if($('#edit-need-category').attr('data-values') == 'supply') {
        is_supply = 1;
      }
      $.post(app_path+'/HuZhu/saveWish', {
        id: detailViewModel.id(),
        content: detailViewModel.content(),
        category: detailViewModel.category(),
        expire_date: detailViewModel.expire_date(),
        is_supply: is_supply
      }, function(result){
        if(result == 'ok'){
          $.hideLoading();
          $.toptip('成功修改心愿', 'success');
          $('#wish-detail-popup').popup();
        }
      });
    }
  });

  $('#return-to-huzhu-button').click(function(){
    $('#wish-detail-popup').popup();
  });
}
