   $(function(){
       loadBaiduMap();
       $('.pill-select').pillSelectBox();
       $('.datepicker').datetimepicker({lang: 'ch'});

       // jquery upload and crop
       if($().fileupload!==undefined){
          $('.fileupload').uploadAndCrop();
       }
       $('.add-event-form').validate();
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

           $('.add-location-button').click(self.save_location);
       },
       geocode: function(address){
           
       },
       set_location: function(point, center, callback){
           var self = this;
           if(self.marker === null){
             var redIcon = new BMap.Icon(app_path+"/Public/img/icons/red-marker.png", new BMap.Size(30, 36), {    
                offset: new BMap.Size(15, 18),    
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
   
 // the jquery plugin for upload and crop
 /*
      a jquery plugin for "upload and crop"
      usage: markup
        <span class="button fileinput-button">
            <span id="imgupload-retext-image">点击上传标志</span>
            <!-- The file input field used as target for the file upload widget -->
            <input class="fileupload" type="file" name="files" re-text="重新上传标志"
                crop-width="150" crop-height="150" data-url="/Util/Upload" target-input="image"/>
        </span>

      - javascript
        $('.fileupload').uploadAndCrop(callback(data));

 */
 (function( $ ) {
      $.fn.uploadAndCrop = function(callback) {
          if($.fn.uploadAndCrop.fancyDom === undefined){
            $('body').append('<div style="display:none;min-width:600px;min-height:400px;" id="crop-dialog"><div style="float: right;width:100px;height:100px;overflow:hidden;" class="crop-preview"><img class="preview-img"/></div><div class="crop-window" style="margin-right:160px;"><img style="" src="" class="crop-img"/></div><button class="button crop-action" style="margin-top: 15px;">确认裁剪</button></div>');

            $.fn.uploadAndCrop.fancyDom = true;
          }
          var jcropAPI;
          this.each(function(){
            var cropWidth = $(this).attr('crop-width'),
                cropHeight = $(this).attr('crop-height'),
                targetInput = $(this).attr('target-input'),
                reText = $(this).attr('re-text'),
                previewWidth = cropWidth,
                previewHeight = cropHeight;

            if(cropWidth>150 && cropWidth>cropHeight){
              previewWidth = 150;
              previewHeight = cropHeight*150/cropWidth;
            }
            $(this).fileupload({
                dataType: 'json',
                done: function (e, data) {
                    $('.crop-img,.preview-img').attr('src', app_path+'/Public/Uploaded/'+data.result.url);
                    $.fancybox.open({
                      href:"#crop-dialog",
                      afterShow: function(){
                        // fit preview image into the preview box - in case it is too big
                        $('.crop-preview').width(previewWidth);
                        $('.crop-preview').height(previewHeight);
                        // resize and position preview image
                        var showPreview = function(coords){
                          var rx = previewWidth / coords.w;
                          var ry = previewHeight / coords.h;
                          $('.preview-img').css({
                            width: Math.round(rx * $('.crop-img').width()) + 'px',
                            height: Math.round(ry * $('.crop-img').height()) + 'px',
                            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                            marginTop: '-' + Math.round(ry * coords.y) + 'px'
                          });
                        };
                        // fit original picture to crop box - in case it's too big
                        var original_img = $('.crop-img');
                        original_img.width('auto');
                        original_img.height('auto');
                        var imageHeight = original_img.height(),
                            imageWidth = original_img.width();

                        if(imageHeight>400 && imageHeight>imageWidth){
                          original_img.height(400);
                        }
                        else if(imageWidth>500 && imageWidth>imageHeight){
                          original_img.width(500);
                        }
                        // calculate initial select region
                        var initSelectWidth, initSelectHeight;
                        if(imageWidth/imageHeight > cropWidth/cropHeight){  // width > hight
                          initSelectHeight = imageHeight;
                          initSelectWidth = imageHeight * cropWidth/cropHeight;
                        }
                        else{
                          initSelectWidth = imageWidth;
                          initSelectHeight = imageWidth * cropHeight/cropWidth;
                        }
                        $('.crop-img').Jcrop({
                          aspectRatio: cropWidth/cropHeight,
                          onChange: showPreview,
                          onSelect: showPreview,
                          setSelect: [0,0,initSelectWidth, initSelectHeight]
                        }, function(){jcropAPI = this});
                        $('.crop-action')[0].onclick=function(){
                          var res = jcropAPI.tellSelect();
                          var resizeRatio = imageWidth/original_img.width();
                          var param = $.param({
                            src: data.result.url,
                            x: Math.round(res.x*resizeRatio),
                            y: Math.round(res.y*resizeRatio),
                            w: Math.round(res.w*resizeRatio),
                            h: Math.round(res.h*resizeRatio),
                            resizeW: cropWidth,
                            resizeH: cropHeight
                          });
                          $.get(app_path+'/Util/cropResize/?'+param, function(result){
                            $('input[name="'+targetInput+'"]').val(data.result.url);
                            $('#imgpreview-'+targetInput).attr('src', app_path+'/Public/Uploaded/'+result).show();
                            $.fancybox.close();
                            $('#imgupload-retext-'+targetInput).text(reText);
                            if(callback!==undefined){
                              callback(data.result.url);
                            }
                          });
                        }
                      },
                      beforeClose: function(){
                        if(jcropAPI){
                          jcropAPI.destroy();
                        }
                      }
                    }); // fancybox
                    
                }
            });
          });
          
          return this;

      };
  }( jQuery ));


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