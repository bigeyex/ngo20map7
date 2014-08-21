
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
      $.fn.uploadAndCrop.defaults = {
          cropWidth: 150,
          cropHeight: 150,
          callback: null  // the callback funciton after upload. @param: url
      };
  
      $.fn.uploadAndCrop = function( options ) {
          var opts = $.extend( {}, $.fn.uploadAndCrop.defaults, options );
          if($.fn.uploadAndCrop.fancyDom === undefined){
            $('body').append('<div style="display:none;min-width:600px;min-height:400px;" id="crop-dialog"><div style="float: right;width:100px;height:100px;overflow:hidden;" class="crop-preview"><img class="preview-img"/></div><div class="crop-window" style="margin-right:160px;"><img style="" src="" class="crop-img"/></div><button class="button crop-action" style="margin-top: 15px;">确认裁剪</button></div>');

            $.fn.uploadAndCrop.fancyDom = true;
          }
          var jcropAPI;
          this.each(function(){
            var cropWidth = opts.cropWidth,
                cropHeight = opts.cropHeight,
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
                            $.fancybox.close();
                            $('#imgupload-retext-'+targetInput).text(reText);
                            if(opts.callback!==undefined){
                              opts.callback(data.result.url);
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

      };  // definition
  }( jQuery ));


