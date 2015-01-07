   $(function(){
       if($('#map-input-box').length >= 1){
         loadBaiduMap();
       }
       $('.pill-select').pillSelectBox();
       $('.datepicker').datetimepicker({lang: 'ch'});
       $('select').each(function(){$(this).val($(this).attr('value'))});

       // jquery upload and crop
       if(typeof FlashUploader !== 'undefined'){
          FlashUploader.init();
          
          $('.upload-photo-button').click(function(){
            FlashUploader.open(function(url){
              $('.image-showcase').append('<a class="uploaded-image-slide" href="'+app_path+'/Public/Uploaded/'+url+'" data-lightbox="image-1" ><img src="'+app_path+'/Public/Uploaded/th628x326_'+url+'" width="119"/><input type="hidden" name="images[]" value="'+url+'"/><i class="fa fa-times remove-image-icon" ></i></a>');
              dispatcher.dispatch('image.uploaded', url);
            });
          });
       }

       if(typeof $.fn.fileupload !== 'undefined'){
         $('.upload-logo').fileupload({
          dataType: 'json',
          url:app_path+'/Util/upload/w/150/h/150/',
          add: function(e, data){
            $('#imgpreview-image').attr('src', app_path+'/Public/img/loading.gif');
            $('#imgpreview-image').show();
            data.submit();
          },
          done: function(e, data){
            $('#imgpreview-image').attr('src', app_path+'/Public/Uploaded/'+data.result.url);
            $('#hidden-input-image').val(data.result.url);
            $('#imgpreview-image').show();
          }
         });
       }

       if($.fn.validate !== undefined){
         $('.add-event-form').validate();
       }

       $('.image-showcase').on('click', '.remove-image-icon', function(e){
          dispatcher.dispatch('image.deleted', $(this).parent().find('input').val());
          $(this).parent().remove();
          e.stopPropagation();
          e.preventDefault();
       })
   });
   function loadBaiduMap(){
       var script = document.createElement("script");
       script.src = "http://api.map.baidu.com/api?v=1.5&ak=1m5xok7fCAjkwvynKoxxEnb1&callback=onBaiduMapLoaded";
       document.body.appendChild(script);  
   }
   
   function onBaiduMapLoaded(){
       var map = new BMap.Map('map-input-box');  
       map.centerAndZoom(new BMap.Point(121.491, 31.233), 11);  
       map.addControl(new BMap.NavigationControl({type: BMAP_NAVIGATION_CONTROL_ZOOM}));
       map_control.init(map);
   }
   
   // map control
   var map_control = {
       markers: [],
       marker: null,
       init: function(map){
           var self = this;
           var change_timer = null;
           self.map = map;
           self.map.addEventListener('click', function(e){
               var location = self.set_location(e.point, false, function(result){
                   $('.map-address').val(result);
                });
           });
           $('.map-address').keyup(function(){
               if(change_timer !== null){
                   clearTimeout(change_timer);
               }
               change_timer = setTimeout(function(){
                   var myGeo = new BMap.Geocoder();      
                   myGeo.getPoint($('.map-address').val(), function(point){      
                      if (point) {      
                          self.set_location(point, true);  
                      }      
                      return null;
                   }, "北京市");
               }, 1000);
           });

           if($('.map-longitude').val() != '' && $('.map-latitude').val() != ''){
              self.set_initial_location();
           }

           $('.add-location-button').click(self.save_location);
       },
       geocode: function(address){
           
       },
       set_location: function(point, center, callback){
           var self = this;
           if(self.marker === null){
             var redIcon = new BMap.Icon(app_path+"/Public/img/icons/red-marker.png", new BMap.Size(30, 36), {    
                offset: new BMap.Size(15, 18)
            });       
             var marker = new BMap.Marker(point, {icon: redIcon});
             self.map.addOverlay(marker);
             marker.setTop(true);
             self.marker = marker;
           }
           else{  // alread has a marker
              self.marker.setPosition(point);
           }
           $('.map-longitude').val(point.lng);
           $('.map-latitude').val(point.lat);
           if(center){
               self.map.setCenter(point);
           }
           var myGeo = new BMap.Geocoder();      

           myGeo.getLocation(point, function(result){      
                 if (result){      
                     $('.map-province').val(result.addressComponents.province);
                     $('.map-city').val(result.addressComponents.city);
                     $('.add-location-button').prop('disabled', false);
                    if(callback !== undefined){
                        callback(result.address);
                    }    
                 }      
           });
       },
       set_initial_location: function(){
          var self = this;
          var lng = $('.map-longitude').val();
          var lat = $('.map-latitude').val();
          var point = new BMap.Point(lng, lat);
          var redIcon = new BMap.Icon(app_path+"/Public/img/icons/red-marker.png", new BMap.Size(30, 36), {    
              offset: new BMap.Size(15, 18)
          });  
          var marker = new BMap.Marker(point, {icon: redIcon});
          self.map.addOverlay(marker);
          self.map.centerAndZoom(point, 11);
          marker.setTop(true);
          self.marker = marker;
       },
       save_location: function(){
          var self = this;
          if($('.map-address').val() == ''){
            return;
          }
          var item_dom = $('<div class="one-location">'+
                  '<input type="hidden" name="lngs[]" value="'+$('.map-longitude').val()+'"/>'+
                  '<input type="hidden" name="lats[]" value="'+$('.map-latitude').val()+'"/>'+
                  '<input type="hidden" name="provinces[]" value="'+$('.map-province').val()+'"/>'+
                  '<input type="hidden" name="cities[]" value="'+$('.map-city').val()+'"/>'+
                  '<input type="hidden" name="places[]" value="'+$('.map-address').val()+'"/>'+
                  $('.map-address').val()+' <a href="javascript:void(0);" class="delete-item">删除</a>'+
                  '</div>');
          $('.location-sets').append(item_dom);
          (function(item_dom){  // create a scope to store current position data
            var dom = item_dom;
            var blueIcon = new BMap.Icon(app_path+"/Public/img/icons/blue-marker.png", new BMap.Size(26, 36), {    
                offset: new BMap.Size(7, 18),    
            });
            var marker = new BMap.Marker(new BMap.Point($('.map-longitude').val(), $('.map-latitude').val()), {icon: blueIcon});
            map_control.map.addOverlay(marker);
            dom.find('.delete-item').click(function(){
              map_control.map.removeOverlay(marker);
              dom.remove();
            });


          })(item_dom);
          
          $('.add-location-button').prop('disabled', true);
          $('.map-address').val('');
          return false;
       },

       delete_location: function(e){
          $(e.currentTarget).parent().remove();
       }

   };
   
$(function(){
    var name_check_keyup_timer = null;
    $('#inputName').keyup(function(){
        if(name_check_keyup_timer){
            clearTimeout(name_check_keyup_timer);
        }
        name_check_keyup_timer = setTimeout(function(){
            var name = $('#inputName').val();
            $('.name-checking').html('正在检查机构名称有没有被使用...');
            if(name == ''){
                $('.name-checking').html('请首先填写机构名称');
                $('.other-fields').hide();
                return;
            }
            $.get(app_path+'/User/ajax_check_name/name/'+name, function(res){
                if(!res){
                    $('.name-checking').html('该机构在地图上尚无记录，欢迎入驻公益地图');
                    $('.other-fields').show();
                }
                else{
                    // $('.name-checking').html('该机构已经在地图上注册。您可以<a href="'+app_path+'/User/coauthor/id/'+res
                    //                             +'">申请成为协作者</a>');
                    $('.name-checking').html('该机构已经在地图上注册。');
                    $('.other-fields').hide();
                }
            });
        }, 1000);
    });
})