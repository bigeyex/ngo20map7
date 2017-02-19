window.HuZhu = window.HuZhu || {};
window.HuZhu.initDetails = function(){
    // ============= Show Wish Detail interface
    var showWishDetail = function(wish) {
      $.closePicker();
      $('#wish-detail-popup').popup();
      ko.mapping.fromJS(wish, detailViewModel);
      $.showLoading();
      $.getJSON(app_path+'/HuZhu/detail/id/'+wish.id, function(result){
        ko.mapping.fromJS(result, detailViewModel);
        $.hideLoading();
      });
    }
    window.showWishDetail = showWishDetail;

    $('#post-reply-button').click(function(){
      var reply = {
        huzhu_id: detailViewModel.id(),
        content: $('#reply-content-input').val()
      }
      $.post(app_path+'/HuZhu/postReply/', reply, function(resultId){
        if(resultId){
          reply.id = ko.observable(resultId);
          reply.author = ko.observable(window.account_author_name);
          reply.account_id = ko.observable(window.account_id);
          reply.publish_date = ko.observable((new Date()).toLocaleDateString());
          detailViewModel.replyList.push(reply);
          $.toptip('成功发布回应', 'success');
        }
      });
    });

    $('#like-wish-button').click(function(){
      var status = $(this).hasClass('liked')?0:1;
      var button = $(this);
      $.get(app_path+'/HuZhu/like/id/'+detailViewModel.id()+'/status/'+status, function(result){
        if(result == 'ok') {
          button.toggleClass('liked');
        }
      });
    });

    $('#share-wish-button').click(function(){
      // TODO: add share wish logic

    });

    var checkCloseStatus = function(){
      var id = detailViewModel.id();
      var maskElement = $('.wish-item[need-id='+id+'] .finish-mask');
      if(!detailViewModel.is_open() || detailViewModel.is_complete()) {
        maskElement.show();
      }
      else{
        maskElement.hide();
      }

      if(!detailViewModel.is_open()) {
        maskElement.text('已关闭');
      }
      else {
        maskElement.text('已完成');
      }
    }

    $('#wish-action-button').click(function(){
      var actions = [
        {
          text: "编辑",
          onClick: function() {
            $('#edit-wish-popup').popup();
          }
        }
      ];
      if(detailViewModel.is_open() != 0) {
        actions.push({
          text: "标记为关闭",
          onClick: function() {
            $.get(app_path+'/HuZhu/markOpen/id/'+detailViewModel.id()+'/status/0', function(result){
              if (result=='ok') {
                detailViewModel.is_open(0);
                checkCloseStatus();
              }
            });
          }
        });
      }
      else {
        actions.push({
          text: "重新打开",
          onClick: function() {
            $.get(app_path+'/HuZhu/markOpen/id/'+detailViewModel.id()+'/status/1', function(result){
              if (result=='ok'){
                 detailViewModel.is_open(1);
                 checkCloseStatus();
              }
            });
          }
        });
      }
      if(detailViewModel.is_complete() != 0) {
        actions.push({
          text: "重新标记为未完成",
          onClick: function() {
            $.get(app_path+'/HuZhu/markCompleted/id/'+detailViewModel.id()+'/status/0', function(result){
              if (result=='ok'){
                detailViewModel.is_complete(0);
                checkCloseStatus();
              }
            });
          }
        });
      }
      else {
        actions.push({
          text: "标记为完成",
          onClick: function() {
            $.get(app_path+'/HuZhu/markCompleted/id/'+detailViewModel.id()+'/status/1', function(result){
              if (result=='ok') {
                detailViewModel.is_complete(1);
                checkCloseStatus();
              }
            });
          }
        });
      }
      actions.push({
        text: "删除",
        className: "color-danger",
        onClick: function() {
          $.confirm("确认要删除心愿么? 此操作无法撤销", "确认删除", function() {
            $.get(app_path+'/HuZhu/deleteWish/id/'+detailViewModel.id(), function(result){
              if (result=='ok') {
                $.closePopup();
                loadWishesList();
              }
            });
          });
        }
      });
      $.actions({ actions: actions });
    });

}
