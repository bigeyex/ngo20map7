$(function(){
   $( ".tabs" ).tabs({
        active: 0,
        activate: function(event, ui){
            if(undefined !== map && $(event.currentTarget).hasClass('tab-map')){
                setTimeout(function(){
                    map.setViewport(point_collection);
                }, 1000);
                
            }
        }
    });

   $('.user-zan-link').click(function(){
    $.get(app_path+'/User/ajaxLike/id/'+user_id, function(result){
        if(result == 'ok'){
            toastr.success('感谢您为机构点赞');
            var like_count = $('.like-count').text();
            like_count = parseInt(like_count)+1;
            $('.like-count').text(like_count);
        }
        else{
            toastr.warning('您已经点过赞了');
        }
    });
   });

   $('.event-zan-link').click(function(){
    $.get(app_path+'/Event/ajaxLike/id/'+event_id, function(result){
        if(result == 'ok'){
            toastr.success('感谢您为项目点赞');
            var like_count = $('.like-count').text();
            like_count = parseInt(like_count)+1;
            $('.like-count').text(like_count);
        }
        else{
            toastr.warning('您已经点过赞了');
        }
    });
   });


   loadBaiduMap();
});


function loadBaiduMap(){
   var script = document.createElement("script");
   script.src = "http://api.map.baidu.com/api?v=1.5&ak=1m5xok7fCAjkwvynKoxxEnb1&callback=onBaiduMapLoaded";
   document.body.appendChild(script);  
}

function onBaiduMapLoaded(){
    var overlay = null;
    var HTMLOverlay = function(lon, lat, px, py, html){
        this._center = new BMap.Point(lon, lat);
        this._px = px;
        this._py = py;
        this._html = html;
    };

    var showInfoWindow = function(longitude, latitude, event_info){
        if(overlay !== null){
            map.removeOverlay(overlay);
            overlay = null;
        }
        map.panTo(new BMap.Point(longitude, parseFloat(latitude)));
        if(event_info.image){
            var image_content = '<div class="info-image"><img src="'+app_path+'/Public/Uploaded/th628x326_'+event_info.image+'" width="120"/></div>';
        }
        else{
            var image_content = '';
        }
        var content = '<div class="info-title"><a href="'+app_path+'/Event/view/id/'+event_info.id+'">'+event_info.name+'</a></div>'+image_content+'<div class="info-desc">'+event_info.intro+'</div>';
        
        overlay = new HTMLOverlay(longitude, latitude, 170, 172, '<div class="info-window"><div class="info-window-box">'+content+'<div class="info-window-close-button fa fa-times"></div><div class="info-window-triangle"></div></div></div>');
        map.addOverlay(overlay);
        $('.info-window-close-button').click(function(){
            map.removeOverlay(overlay);
            overlay = null;
        });
    };

    var add_curve = function(org, dst, color, width){
        var curve = new BMapLib.CurveLine([org, dst], {strokeColor:color, strokeWeight:width, strokeOpacity:0.4}); //创建弧线对象
        map.addOverlay(curve); //添加到地图中
        return curve;
    }


    HTMLOverlay.prototype = new BMap.Overlay();    

    HTMLOverlay.prototype.initialize = function(map){    
     this.se = false;
     this._map = map;        
     var div = $(this._html)[0];    
     div.style.position = "absolute";        
     map.getPanes().markerPane.appendChild(div);      
     this._div = div;       
     return div;    
    }  

    HTMLOverlay.prototype.draw = function(){    
    // 根据地理坐标转换为像素坐标，并设置给容器    
     var position = this._map.pointToOverlayPixel(this._center);    
     this._div.style.left = position.x - this._px + "px";    
     this._div.style.top = position.y - this._py + "px";    
    }  


   map = new BMap.Map('map-container');  
   map.centerAndZoom(new BMap.Point(121.491, 31.233), 11);  
   map.addControl(new BMap.NavigationControl({type: BMAP_NAVIGATION_CONTROL_ZOOM}));

   // after map init
   var points = []; //points: raw information of points
   $('.event-switch').each(function(){
        var self = this;
        var self_dom = $(this);
        var event_info = {
            id: self_dom.attr('event-id'),
            name: self_dom.attr('title'),
            intro: self_dom.attr('intro'),
            image: self_dom.attr('image'),
            points: []
        };
        var lngs = self_dom.attr('lngs').split(',');
        var lats = self_dom.attr('lats').split(',');
        for(var i in lngs){
            var point = {
                lng: lngs[i],
                lat: lats[i],
                event_info: event_info
            };
            event_info.points.push(point);
            points.push(point);
        }
        self.event_info = event_info;
        $(this).click(function(){
            var first_point = this.event_info.points[0];
            if(undefined !== first_point){
                showInfoWindow(first_point.lng, first_point.lat, this.event_info);
            }
        });
   });

   // add all the points
   point_collection = [];   // BMap.Point objects
   for(var i in points){
        var p = points[i];
        var point = new BMap.Point(p.lng, p.lat);
        point_collection.push(point);
        var myIcon = new BMap.Icon(app_path+"/Public/img/icons/blue-marker.png", new BMap.Size(26, 36), {    
            offset: new BMap.Size(7, 18)  
        });      
        var marker = new BMap.Marker(point, {icon: myIcon,title: p.event_info.name});
        marker.event_info = p.event_info;
        marker.addEventListener("click", function(){
            var p = this.getPosition();
            showInfoWindow(p.lng, p.lat, this.event_info);
        });
        p.marker = marker;
        map.addOverlay(marker);  
        // draw a curve
        add_curve(new BMap.Point(home_lng, home_lat),point,'#49820b',2);
    }
    map.setViewport(point_collection);
}