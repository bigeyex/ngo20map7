window.HuZhu = window.HuZhu || {};
window.HuZhu.initQuery = function(){
  // ----------setup control events
  var search_input_timeout = null;
  var infinitePage = 1;
  var isLoadingNextPage = 0;
  $('#search_input').on('input', function(){
    if(search_input_timeout) clearTimeout(search_input_timeout);
    search_input_timeout = setTimeout(function(){
      loadWishesList();
    }, 1000);
  });
  $('#needs-list').pullToRefresh().on('pull-to-refresh', function(){
    loadWishesList();
  });
  $('#needs-list').infinite().on("infinite", function() {
    infinitePage++;
    if(isLoadingNextPage == 0){
      loadWishesList(infinitePage);
    }
    isLoadingNextPage = 1;
  });

  // ============== Async load data
  var loadWishesList = function(page) {
    if(!page) {
      page = 1;
      infinitePage = 1;
    }
    $.showLoading();
    var condition = {
      city: $('#city-picker').val(),
      category: $('#category-selector').attr('data-values'),
      q: $('#search_input').val(),
      page: page
    };
    var type = $('#progress-selector').attr('data-values');
    if(type) condition[type]=1;
    $.get(app_path+'/HuZhu/query', condition,  function(result){
      if(page == 1) {
        wishesViewModel.wishes(result);
        $('#needs-list-infinite').show();
        $('#needs-list').infinite();
        $('#needs-list').pullToRefreshDone();
      }
      else {
        for(var i=0;i<result.length;i++) {
          wishesViewModel.wishes.push(result[i]);
        }
      }
      if(result.length < window.config_items_per_page) { // 已经是最后一页了
        $('#needs-list').destroyInfinite();
        $('#needs-list-infinite').hide();
      }
      isLoadingNextPage = 0;
      $.hideLoading();
    }, 'json')
  }
  window.loadWishesList = loadWishesList;
  // 设定用户当前地区
  var gpsCity = localStorage.getItem('cityname');
  if(gpsCity) {
    $('#city-picker').val(gpsCity);
    $('#city-picker').attr('data-values', gpsCity);
    loadWishesList();
  }

  $('#my-needs-list').pullToRefresh().on('pull-to-refresh', function(){
    loadMyWishesList();
  });
  $('#my-needs-list').infinite().on("infinite", function() {
    infinitePage++;
    if(isLoadingNextPage == 0){
      loadMyWishesList(infinitePage);
    }
    isLoadingNextPage = 1;
  });
  var isMyWishInited = false;
  var loadMyWishesList = function(page){
    if(!page) {
      page = 1;
      infinitePage = 1;
    }
    $.showLoading();
    var condition = { page:page };
    var type = $('#my-filter-selector').attr('data-values');
    if(type) condition[type]=1;
    $.get(app_path+'/HuZhu/query', condition,  function(result){
      if(isMyWishInited || result.length > 0){

        if(page == 1) {
          myWishesViewModel.wishes(result);
          $('#my-needs-infinite').show();
          $('#my-needs-list').infinite();
          $('#my-needs-list').pullToRefreshDone();
        }
        else {
          for(var i=0;i<result.length;i++) {
            myWishesViewModel.wishes.push(result[i]);
          }
        }
        if(result.length < window.config_items_per_page) { // 已经是最后一页了
          $('#my-needs-list').destroyInfinite();
          $('#my-needs-infinite').hide();
        }
        isLoadingNextPage = 0;


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
};

window.HuZhu.locateCity = function(latitude, longitude){
  $.ajax({
			url: 'http://api.map.baidu.com/geocoder/v2/?ak=1m5xok7fCAjkwvynKoxxEnb1&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=1',
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				console.log(data);
				var province = data.result.addressComponent.province;
				var cityname = (data.result.addressComponent.city);
				localStorage.setItem("province", province);
				localStorage.setItem("cityname", cityname);
				if (typeof callback == "function") {
					callback(data);
				}

        $('#city-picker').val(cityname);
        $('#city-picker').attr('data-values', cityname);
        loadWishesList();
			}
		});
}
