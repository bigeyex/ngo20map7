   $(function(){
       if($('#map-input-box').length >= 1 && $('#inputName').length < 1){
         loadGoogleMap();
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
   });
   function loadGoogleMap(){
      var script = document.createElement("script");
      script.src = "https://maps.googleapis.com/maps/api/js?v=3.exp&callback=onGoogleMapLoaded";
      document.body.appendChild(script);  
   }
   
   function onGoogleMapLoaded(){
      var mapOptions = {
          zoom: 4,
          center: new google.maps.LatLng(trans.default_lat, trans.default_lon)
      };
      var map = new google.maps.Map(document.getElementById('map-input-box'),
      mapOptions);  
      map_control.init(map);
   }
   
   // map control
   var map_control = {
       markers: [],
       marker: null,
       map: null,
       init: function(map){
           var self = this;
           var change_timer = null;
           self.map = map;

           self.geocoder = new google.maps.Geocoder();
           google.maps.event.addListener(self.map, 'click', function(e){
               var location = self.set_location(e.latLng, false, function(result){
                   $('.map-address').val(result);
                });
           });
           $('.map-address').keyup(function(){
               if(change_timer !== null){
                   clearTimeout(change_timer);
               }
               change_timer = setTimeout(function(){  
                   self.geocoder.geocode( { 'address': $('.map-address').val() }, function(results, status){      
                      if (status == google.maps.GeocoderStatus.OK) {
                        self.set_location(results[0].geometry.location, true); 
                      } else {
                        console.log('Geocode was not successful for the following reason: ' + status);
                      }
                      if (point) {      
                          self.set_location(point, true);  
                      }      
                      return null;
                   });
               }, 1000);
           });

           $('.add-location-button').click(self.save_location);
       },
       geocode: function(address){
           
       },
       set_location: function(point, center, callback){
           var self = this;
           if(self.marker === null){    
             var marker = new google.maps.Marker({
                position: point,
                map: self.map,
                icon: app_path+"/Public/img/icons/red-marker.png"
             });
             marker.setZIndex(10);
             self.marker = marker;
           }
           else{  // alread has a marker
              self.marker.setPosition(point);
           }
           $('.map-longitude').val(point.lng());
           $('.map-latitude').val(point.lat());
           if(center){
               self.map.setCenter(point);
           }
          
          self.geocoder.geocode({'latLng': point}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {    
                     $('.map-province').val(results[1].formatted_address);
                     $('.map-city').val(results[1].formatted_address);
                     $('.add-location-button').prop('disabled', false);
                    if(callback !== undefined){
                        callback(results[1].formatted_address);
                    }    
                } else {
                  console.log('No results found');
                }
              } else {
                console.log('Geocoder failed due to: ' + status);
              }
            });
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
            var marker = new google.maps.Marker({
              position: new google.maps.LatLng($('.map-longitude').val(), $('.map-latitude').val()),
              map: self.map,
              icon: app_path+"/Public/img/icons/blue-marker.png"
            });
            dom.find('.delete-item').click(function(){
              marker.setMap(null);
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
            $('.name-checking').html(trans.check_name);
            if(name == ''){
                $('.name-checking').html(trans.check_name_default);
                $('.other-fields').hide();
                return;
            }
            $.get(app_path+'/User/ajax_check_name/name/'+name, function(res){
                if(!res){
                    $('.name-checking').html(trans.check_name_ok);
                    $('.other-fields').show();
                    if(map_control.map === null){
                      loadGoogleMap();
                    }
                }
                else{
                    // $('.name-checking').html('该机构已经在地图上注册。您可以<a href="'+app_path+'/User/coauthor/id/'+res
                    //                             +'">申请成为协作者</a>');
                    $('.name-checking').html(trans.check_name_failed);
                    $('.other-fields').hide();
                }
            });
        }, 1000);
    });
})