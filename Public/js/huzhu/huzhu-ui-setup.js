window.HuZhu = window.HuZhu || {};
HuZhu.initUI = function(){
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
    title: "心愿类型",
    items: window.config_need_types
  });

  $("#edit-need-type").select({
    title: "筛选心愿",
    items: window.config_need_types
  });

  $("#create-need-category").select({
    title: "供/需类型",
    items: window.config_progress_input_types
  });

  $("#edit-need-category").select({
    title: "供/需类型",
    items: window.config_progress_input_types
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

  // ============= Setup buttons and page transitions
  $('#change-user-button').click(function(){
    $('#connect-account-popup').popup();
  });

  $('#reply-nav-button').click(function(){
    $('#reply-content-input').focus();
  });
}
