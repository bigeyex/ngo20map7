window.HuZhu = window.HuZhu || {};
window.HuZhu.initModels = function(){
  // ============= view models
  var wishesViewModel = {
    wishes: ko.observableArray(),
    showDetail: function() {
      showWishDetail(this);
    }
  };
  ko.applyBindings(wishesViewModel, $('#needs-list')[0]);
  window.wishesViewModel = wishesViewModel;

  var myWishesViewModel = {
    wishes: ko.observableArray(),
    showDetail: function() {
      showWishDetail(this);
    }
  };
  ko.applyBindings(myWishesViewModel, $('#tab-my-needs')[0]);
  window.myWishesViewModel = myWishesViewModel;

  var detailViewModel = ko.mapping.fromJS({
    id: 0,
    content: '',
    category: '',
    expire_date: '',
    is_supply: 0,
    is_verified: 0,
    isLiked: 0,
    author: '',
    ngo_name: '',
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
};
