   $(function(){
       loadBaiduMap();
       $('.pill-select').pillSelectBox();
       $('.datepicker').datepicker();

       // jquery upload and crop
       if($().fileupload!==undefined){
          $('.fileupload').fileupload({
              dataType: 'json',
              done: function (e, data) {
                  $('.crop-img,.preview-img').attr('src', app_path+'/Public/Uploaded/'+data.result.url);
                  $.fancybox.open({
                    href:"#crop-dialog",
                    afterShow: function(){
                      var showPreview = function(coords){
                        var rx = 100 / coords.w;
                        var ry = 100 / coords.h;
                        $('.preview-img').css({
                          width: Math.round(rx * $('.crop-img').width()) + 'px',
                          height: Math.round(ry * $('.crop-img').height()) + 'px',
                          marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                          marginTop: '-' + Math.round(ry * coords.y) + 'px'
                        });
                      };
                      var original_img = $('.crop-img');
                      if(original_img.height()>400 && original_img.height()>original_img.width()){
                        original_img.height(400);
                      }
                      else if(original_img.width()>500 && original_img.width()>original_img.height()){
                        original_img.width(400);
                      }
                      
                      $('.crop-img').Jcrop({
                        aspectRatio: 1,
                        onChange: showPreview,
                        onSelect: showPreview,
                      });
                    }
                  });
                  
                  $('input[name="image"]').val(data.result.url);
              }
          });
       }
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
       },
       geocode: function(address){
           
       },
       set_location: function(point, center, callback){
           var self = this;
           self.map.clearOverlays();
           var myIcon = new BMap.Icon(app_path+"/Public/img/icons/blue-marker.png", new BMap.Size(26, 36), {    
                offset: new BMap.Size(7, 18),    
            });         
           var marker = new BMap.Marker(point, {icon: myIcon});
           self.map.addOverlay(marker);
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
                    if(callback !== undefined){
                        callback(result.address);
                    }    
                 }      
           });
       }

   };
   
   // the jquery plugin for generating select box
   (function( $ ) {
 
        $.fn.pillSelectBox = function() {
            this.each(function(){
                var hidden_select = $(this);
                var selectOptions = hidden_select.attr('data-options').split(',');
                var pills = $.map(selectOptions, function(a){
                                return '<li><a href="javascript:void(0)">'+a+'</a></li>';
                            }).join('');
                var trigger_box = $('<div><span class="trigger-box-text"></span></div>');
                var dropdown_menu = $('<ul>'+pills+'</ul>');

                dropdown_menu.appendTo(trigger_box);
                trigger_box.insertAfter(hidden_select).attr('class', hidden_select.attr('class')).addClass('select-pills');

                hidden_select.hide();
                dropdown_menu.hide();
                dropdown_menu.css('position', 'absolute');
                dropdown_menu.css('left',0);
                dropdown_menu.css('top',trigger_box.outerHeight());

                // events
                trigger_box.click(function(e){
                    dropdown_menu.toggle();
                    e.stopPropagation();
                });
                dropdown_menu.click(function(e){
                    e.stopPropagation();
                });
                dropdown_menu.find('a').click(function(){
                    $(this).toggleClass('selected');
                    var selected_items = dropdown_menu.find('a.selected').map(function(){
                        return $(this).text();
                    });
                    selected_items = $.makeArray(selected_items);
                    hidden_select.val(selected_items.join(','));
                    trigger_box.find('.trigger-box-text').text(selected_items.join(', '));
                });
                $(document).click(function(){
                    dropdown_menu.hide();
                });
            });
            
            return this;

        };

    }( jQuery ));