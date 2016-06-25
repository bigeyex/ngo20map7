$(function(){
  // $('#wish-detail-popup').popup();

  // =========== Setup all the pickers
  $('#city-picker').cityPicker({
    showDistrict: false,
    onClose: function(){loadWishesList();}
  });

  $("#category-selector").select({
    title: "筛选心愿",
    items: window.config_need_filter_types,
    onChange: function(){loadWishesList();}
  });

  $("#create-need-type").select({
    title: "筛选心愿",
    items: window.config_need_types
  });

  $("#edit-need-type").select({
    title: "筛选心愿",
    items: window.config_need_types
  });


  $("#progress-selector").select({
    title: "筛选心愿",
    items: window.config_progress_types,
    onChange: function(){loadWishesList();}
  });

  $("#my-filter-selector").select({
    title: "筛选心愿",
    items: window.config_mylist_types,
    onChange: function(){loadMyWishesList();}
  });

  $("#create-exp-date, #edit-exp-date").calendar();

  // ----------setup control events
  var search_input_timeout = null;
  $('#search_input').on('input', function(){
    if(search_input_timeout) clearTimeout(search_input_timeout);
    search_input_timeout = setTimeout(function(){
      loadWishesList();
    }, 1000);
  });

  // ============= Setup buttons and page transitions
  $('#change-user-button').click(function(){
    $('#connect-account-popup').popup();
  });

  $('#reply-nav-button').click(function(){
    $('#reply-content-input').focus();
  });

  // ============= Show Wish Detail interface
  var showWishDetail = function(wish) {
    $('#wish-detail-popup').popup();
    ko.mapping.fromJS(wish, detailViewModel);
    $.showLoading();
    $.getJSON(app_path+'/HuZhu/detail/id/'+wish.id, function(result){
      ko.mapping.fromJS(result, detailViewModel);
      $.hideLoading();
    });
  }

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
        detailViewModel.replyList.push(reply);
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
    $.actions({
      actions: [{
        text: "分享到朋友圈",
        onClick: function() {
          //do something
        }
      },{
        text: "分享给微信好友",
        onClick: function() {
          //do something
        }
      },{
        text: "分享到QQ",
        onClick: function() {
          //do something
        }
      },{
        text: "分享到QQ空间",
        onClick: function() {
          //do something
        }
      }]
    });
  });

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
            if (result=='ok') detailViewModel.is_open(0);
          });
        }
      });
    }
    else {
      actions.push({
        text: "重新打开",
        onClick: function() {
          $.get(app_path+'/HuZhu/markOpen/id/'+detailViewModel.id()+'/status/1', function(result){
            if (result=='ok') detailViewModel.is_open(1);
          });
        }
      });
    }
    if(detailViewModel.is_complete() != 0) {
      actions.push({
        text: "重新标记为未完成",
        onClick: function() {
          $.get(app_path+'/HuZhu/markCompleted/id/'+detailViewModel.id()+'/status/0', function(result){
            if (result=='ok') detailViewModel.is_complete(0);
          });
        }
      });
    }
    else {
      actions.push({
        text: "标记为完成",
        onClick: function() {
          $.get(app_path+'/HuZhu/markCompleted/id/'+detailViewModel.id()+'/status/1', function(result){
            if (result=='ok') detailViewModel.is_complete(1);
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

  // ============= view models
  var wishesViewModel = {
    wishes: ko.observableArray(),
    showDetail: function() {
      showWishDetail(this);
    }
  };
  ko.applyBindings(wishesViewModel, $('#needs-list')[0]);

  var myWishesViewModel = {
    wishes: ko.observableArray(),
    showDetail: function() {
      showWishDetail(this);
    }
  };
  ko.applyBindings(myWishesViewModel, $('#tab-my-needs')[0]);

  var detailViewModel = ko.mapping.fromJS({
    id: 0,
    content: '',
    category: '',
    expire_date: '',
    isLiked: 0,
    author: '',
    likes: 0,
    replies: 0,
    replyList: [],
    account_id: 0,
    showReplyMenu: function(reply) {
      $.actions({
        actions: [{
          text: "删除",
          className: "color-danger",
          onClick: function() {
            $.get(app_path+'/HuZhu/deleteReply/id/'+reply.id(), function(result){
              if(result == 'ok') {
                detailViewModel.replyList.remove(reply);
              }
            });
          }
        }]
      });
    }
  });
  ko.applyBindings(detailViewModel, $('#wish-detail-popup')[0]);
  ko.applyBindings(detailViewModel, $('#edit-wish-popup')[0]);
  window.detailViewModel = detailViewModel;
  // ============== Async load data
  var loadWishesList = function() {
    $.showLoading();
    var condition = {
      city: $('#city-picker').val(),
      category: $('#category-selector').attr('data-values'),
      q: $('#search_input').val()
    };
    var type = $('#progress-selector').attr('data-values');
    if(type) condition[type]=1;
    $.get(app_path+'/HuZhu/query', condition,  function(result){
      wishesViewModel.wishes(result);
      $.hideLoading();
    }, 'json')
  }
  window.loadWishesList = loadWishesList;
  loadWishesList();

  var isMyWishInited = false;
  var loadMyWishesList = function(){
    $.showLoading();
    var condition = { };
    var type = $('#my-filter-selector').attr('data-values');
    if(type) condition[type]=1;
    $.get(app_path+'/HuZhu/query', condition,  function(result){
      if(isMyWishInited || result.length > 0){
        myWishesViewModel.wishes(result);
        $.hideLoading();
      }
      else{
        $('#my-filter-selector').attr('data-values', 'my');
        $('#my-filter-selector').val('我发起的');
        loadMyWishesList();
      }
      isMyWishInited = true;
    }, 'json')
  }
  window.loadMyWishesList = loadMyWishesList;

  // ============== Add HuZhu
  $('#publish-huzhu-button').click(function(){
    if(!$('#input-create-content').val()){
      $.toast('必须填写心愿内容', 'text');
    }
    else{
      $.post(app_path+'/HuZhu/insert', {
        city: $('#city-picker').val(),
        content: $('#input-create-content').val(),
        category: $('#create-need-type').val(),
        expire_date: $('#create-exp-date').val()
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
      $.post(app_path+'/HuZhu/saveWish', {
        id: detailViewModel.id(),
        content: detailViewModel.content(),
        category: detailViewModel.category(),
        expire_date: detailViewModel.expire_date()
      }, function(result){
        if(result == 'ok'){
          $.hideLoading();
          $('#wish-detail-popup').popup();
        }
      });
    }
  });

  $('#return-to-huzhu-button').click(function(){
    $('#wish-detail-popup').popup();
  });
});
