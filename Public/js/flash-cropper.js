var FlashUploader = {
    _flashObject: null,
    _callback: null,
    init: function(sizes){
        var self = this;
        if(typeof sizes === 'undefined'){
            sizes = ['628x326'];
        }
        if(this._flashObject === null){
            $('body').append('<div id="swf_div" style="display:none;">'+
                            '<div id="swfContainer"></div>'+
                         '</div>');
            // generate all sizes
            var halfSizes = [];
            var avatarSizes = [];
            for(var i in sizes){
                if(isNaN(i)){
                    return;
                }
                var coords = sizes[i].split('x');
                var halfX = parseInt(coords[0]/2);
                var halfY = parseInt(coords[1]/2);
                halfSizes.push(halfX+'*'+halfY);
                avatarSizes.push('__avatar'+sizes[i]);
            }
            this._flashObject = new fullAvatarEditor("swfContainer", {
                id: 'swf',
                upload_url: app_path+'/Util/flashUpload',
                src_upload: 0,
                src_size: '8MB',
                avatar_intro: '最终会生成以下尺寸的图片，请检查是否清晰',
                avatar_sizes_desc: halfSizes.join('|'),
                browse_tip: '仅支持JPG、JPEG、GIF、PNG格式的图片文件\n文件不能大于8MB',
                avatar_sizes: halfSizes.join('|'),
                avatar_field_names: avatarSizes.join('|'),
                avatar_scale: 2
            }, function(json){
                // flash upload callback function
                if(json.code==5){
                    $.fancybox.close();
                    self._callback(json.content.sourceUrl);
                }
            });
        }   // if(this._flashObject === null)
    },   // method: init
    open: function(callback){
        this._callback = callback;
        $.fancybox($('#swf_div'));
    }
}