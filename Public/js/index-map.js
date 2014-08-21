$(function(){
    $(window).resize(function(){
        var height = $(window).height();
        var width = $(window).width();
        $('.left-fluid-column, .right-fixed-column').height(height-67);
        $('.result-panel').height(height-67-60);
        if(width<756){
            $('.right-fixed-column').width(width);
        }
        else{
            $('.right-fixed-column').width(756);
        }
   });
   $(window).resize();
    
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
        self.map = new BMap.Map('main-map-container');  
        self.map.centerAndZoom(new BMap.Point(121.491, 31.233), 11);  
        self.map.addControl(new BMap.NavigationControl());
        self.refreshViewport();
        var on_move_or_zoom_end = function(){
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
       self.attach_autocomplete("#search-input-type", type_categories);
       self.attach_autocomplete("#search-input-cause", cause_categories);
       self.attach_autocomplete("#search-input-region", region_categories);
       self.attach_autocomplete("#search-input-keyword", keyword_categories);
       
       $('.commit-search-button').click(function(){
           // when click the search button, 
           // search in the whole nation regardless of current viewport of the map.
           self.restartViewport();
           self.commitChange();
       });

       $('.result-panel').on('click', '.pager span', function(e){
            var dom = $(this);
            var page;

            if(dom.hasClass('pager-prev')){
                page=self.page-1;
            }
            else if(dom.hasClass('pager-next')){
                page=self.page+1;
            }
            else if(dom.hasClass('pager-number')){
                page=dom.text();
            }
            else{
                return;
            }
            if( page == self.page ){
                return;
            }
            self.page = parseInt(page);
            self.reload();
       });
        
       dispatcher.subscribe('viewport.changed', function(minlon, maxlon, minlat, maxlat){
           self.minlon=minlon;
           self.maxlon=maxlon;
           self.minlat=minlat;
           self.maxlat=maxlat;
           $('#search-input-region').val('');
           self.commitChange();
       });
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
    
    this.restartViewport = function(){
        dispatcher.dispatch('filter.change.province');
        self.minlon=self.minlat=self.maxlon=self.maxlat=null;
    }
    
    this.reload = function(){
        var params = {
            province : $('#search-input-region').val(),
            work_field : $('#search-input-cause').val(),
            type : $('#search-input-type').val(),
            keyword : $('#search-input-keyword').val(),
            minlon: self.minlon,
            maxlon: self.maxlon,
            minlat: self.minlat,
            maxlat: self.maxlat,
            page: self.page
        };
        $('.result-panel').load(app_path+'/Index/map_result?'+$.param(params), function(){
            dispatcher.dispatch('result.refreshed');
            $('.result-panel').scrollTop(0);
        });
    }
    
    this.on_text_change = function(e, autoselect_item){
        var dom = e.target;
        if(autoselect_item || $(dom).val()){
            $(dom).parent().addClass('has-text');
        }
        else{
            $(dom).parent().removeClass('has-text');
        }
    }
    
    this.attach_autocomplete = function(id, source){
        var container_id = id+'-results';
        $(id).autocomplete({
            source: function(request, response){
                var req = request.term.toLowerCase().replace("'", "");
                result = [];
                for(var i in source){
                    var o = source[i];
                    if(req=='' || o.q.indexOf(req)!=-1 || o.p.indexOf(req)!=-1){
                        result.push(o.q);
                    }
                }
                response(result);
            },
            appendTo: container_id,
            open: function() {
                var position = $(container_id).position(),
                    left = position.left, top = position.top;

                $(container_id+" > ul").css({left: (left) + "px",
                                        top: (top + 5) + "px" });

            },
            close: function(){
            },
            select: self.onselect,
            minLength: 0 
        });
        $(id).keyup(self.on_text_change);
        $(id+'-cross').click(function(){
            $(id).val('');
            $(id).parent().removeClass('has-text');
            if(id=='#search-input-region'){
                self.restartViewport();
            }
            self.commitChange();
        });
        if(!$(id).hasClass('no-dropdown')){
            $(id).focus(function(){
                $(id).autocomplete("search", "");
            });
            $(id).click(function(){
                $(id).autocomplete("search", "");
            });
            $(id+'-dropdown').click(function(){
                $(id).autocomplete("search", "");
            });
        }

    };
}
filterView = new FilterView();
