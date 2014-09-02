if(Dispatcher === undefined){
	function Dispatcher(){
	    this.eventList = {};
	    var self = this;
	    
	    this.subscribe = function(eventName, eventHandler){
	        if(self.eventList[eventName]===undefined){
	            self.eventList[eventName] = [];
	        }
	        self.eventList[eventName].push(eventHandler);
	    };
	    
	    this.dispatch = function(eventName){
	        var args = Array.prototype.slice.call(arguments);
	        args.shift();
	        var eventList = self.eventList[eventName];
	        if(eventList!==undefined){
	            for(var i in eventList){
	                eventList[i].apply(this, args);
	            }
	            return true;
	        }
	        else{
	            return false;
	        }
	    };
	}
}
window.dispatcher = new Dispatcher();

$(function(){
    
    // init all modules
    mapView.init();
    filterView.init();


});



/**********************
*   class MapView
*   control everything Baidu Map does
**********************/
function MapView(){
    var self=this;
    var viewport_activated = false;
    
    this.init = function(){
        var script = document.createElement("script");
        script.src = "http://api.map.baidu.com/api?v=1.5&ak=1m5xok7fCAjkwvynKoxxEnb1&callback=mapView.onload";
        document.body.appendChild(script);  
    };
    
    this.onload = function(){
        self.map = new BMap.Map('allmap');  
        
        self.map.centerAndZoom(new BMap.Point(121.491, 31.233), 11);  
        self.map.addControl(new BMap.NavigationControl());
        self.refreshViewport();
        if(typeof default_map_lng!=='undefined' && default_map_lng != ''){
            self.map.centerAndZoom(new BMap.Point(default_map_lng, default_map_lat), default_map_zoom);  
        }
        var on_move_or_zoom_end = function(){
            if(typeof window.parent.set_map_center !== 'undefined'){
                var c = self.map.getCenter();
                window.parent.set_map_center(c.lng, c.lat, self.map.getZoom());
            }
            var bounds = self.map.getBounds();
            var sw = bounds.getSouthWest();
            var ne = bounds.getNorthEast();
            if(self.viewport_activated){
                dispatcher.dispatch('viewport.changed', sw.lng, ne.lng, sw.lat, ne.lat);
            }
            self.viewport_activated = true;
        }
        self.map.addEventListener('moveend', on_move_or_zoom_end);
        self.map.addEventListener('zoomend', on_move_or_zoom_end);
        dispatcher.subscribe('filter.change.province', function(){
            self.viewport_activated = false;
        });
        dispatcher.subscribe('result.refreshed', function(){
            self.refreshViewport();
        });
        $('.save-map-center').click(function(){
            var cp = self.map.getCenter();
            var zoom = self.map.getZoom();
            var center = [cp.lng, cp.lat, zoom].join(',');

            $.post(app_path+'/Local/save_map_center',{local_id:$('#hidden-local-id').val(), center:center }, function(result){
                if(result=='ok'){
                    toastr.success('成功保存地图中心点和缩放级别');
                }
            });
        });
    };
    
    this.refreshViewport = function(){
        var markers = self.refreshMarkers();
        if(!self.viewport_activated){
            points = $.map(markers, function(a){
                return a.getPosition();
            });
            
            self.map.setViewport(points);
        }
        
    };
    
    this.refreshMarkers = function(){
        self.map.clearOverlays();
        var markers = [];
        $('.map-item').each(function(){
            var item = $(this);
            lng_list = item.attr('lng').split(',');
            lat_list = item.attr('lat').split(',');
            for(var i=0;i<lng_list.length;i++){
                var marker = self.addMarker(lng_list[i], lat_list[i], item.attr('title'));
                marker.domElement = this;
                marker.addEventListener('mouseover', function(){
                    var elem = $(this.domElement);
                    $('.result-list li').removeClass('active');
                    elem.addClass('active');
                    this.hoverTimeout = setTimeout(function(){
                        $('.result-panel').animate({
                            scrollTop: elem.offset().top-$('.result-list li').eq(0).offset().top
                        }, 500);
                    }, 200);
                });
                marker.addEventListener('mouseout', function(){
                    clearTimeout(this.hoverTimeout);
                });
                item.mouseover(function(){
                    self.highlightMarker(marker, true);
                }).mouseout(function(){
                    self.highlightMarker(marker, false);
                });
                markers.push(marker);
            }
        });
        return markers;
    };

    this.highlightMarker = function(marker, highlight){
        var blueIcon = new BMap.Icon(app_path+"/Public/img/icons/blue-marker.png", new BMap.Size(26, 36), {    
            offset: new BMap.Size(7, 18),    
        });  
        var redIcon = new BMap.Icon(app_path+"/Public/img/icons/red-marker.png", new BMap.Size(30, 36), {    
            offset: new BMap.Size(15, 18),    
        });  
        if(highlight){
            marker.setIcon(redIcon);
        }
        else{
            marker.setIcon(blueIcon);
        }
        marker.setTop(highlight);
    }   
    
    this.addMarker = function(lng, lat, title){
        var point = new BMap.Point(lng, lat);
        var myIcon = new BMap.Icon(app_path+"/Public/img/icons/blue-marker.png", new BMap.Size(26, 36), {    
            offset: new BMap.Size(7, 18),    
        });      
        var marker = new BMap.Marker(point, {icon: myIcon,title: title});
        self.map.addOverlay(marker);  
        return marker;
    };
}
mapView = new MapView();

/**********************
*   class FilterView
*   control the behavior of top filter
**********************/
function FilterView(){
    var self = this;
    this.minlon=this.minlat=this.maxlon=this.maxlat=null;
    this.page = 1;
    this.refreshTimeout = null;
    this.init = function(){

       $('.result-panel').on('click', '.next-page-mini', function(e){            
            self.page = parseInt(self.page)+1;
            self.reload();
       });
       $('.result-panel').on('click', '.prev-page-mini', function(e){            
            self.page = parseInt(self.page)-1;
            self.reload();
       });
       $('#set-field').change(function(){
       		self.reload();
       })
        
       dispatcher.subscribe('viewport.changed', function(minlon, maxlon, minlat, maxlat){
           self.minlon=minlon;
           self.maxlon=maxlon;
           self.minlat=minlat;
           self.maxlat=maxlat;
           self.commitChange();
       });
       self.reload();
    };
    
    this.onselect = function(event, autoselect_item){
        $(this).val(autoselect_item.item.value);
        self.on_text_change(event, autoselect_item);
        if($(this).attr('id')=='search-input-region'){
            self.restartViewport();
        }
        self.commitChange();
    }
    
    this.commitChange = function(){
        dispatcher.dispatch('filter.changed');
        this.page = 1;
        this.reload();
    };
    
    this.reload = function(){
        var params = {
            province : $('#local-province').val(),
            work_field : $('#set-field').val(),
            type : 'ngo',
            mini : 1,
            minlon: self.minlon,
            maxlon: self.maxlon,
            minlat: self.minlat,
            maxlat: self.maxlat,
            page: self.page
        };
        $('.result-panel').load(app_path+'/Index/map_result?'+$.param(params), function(){
            dispatcher.dispatch('result.refreshed');
        });
    }
    
    
}
filterView = new FilterView();
