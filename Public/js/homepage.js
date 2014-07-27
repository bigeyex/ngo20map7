function attach_autocomplete(id, source){
        var container_id = id+'-results';
        $(id).autocomplete({
            source: function(request, response){
                var req = request.term.toLowerCase();
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
                // $(container_id+" > ul").mCustomScrollbar('destroy'); 
                // $(container_id+" > ul").mCustomScrollbar(); 

            },
            close: function(){
                // $(container_id+" > ul").mCustomScrollbar('destroy'); 
            },
            minLength: 0 
        });
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
    
    $(function(){
        // auto completes
        attach_autocomplete("#search-input-type", type_categories);
        attach_autocomplete("#search-input-cause", cause_categories);
        attach_autocomplete("#search-input-region", region_categories);
        attach_autocomplete("#search-input-keyword", keyword_categories);
        
        // hero slideshow
        $('#hero-region .slideshow').jcarousel({
            wrap: 'circular'   
        }).jcarouselAutoscroll({
            interval: 5000,
            target: '+=1',
            autostart: true
        });
        $('.icon-slideshow-left').click(function(){
            $('#hero-region .slideshow').jcarousel('scroll', '-=1');
        });
        $('.icon-slideshow-right').click(function(){
            $('#hero-region .slideshow').jcarousel('scroll', '+=1');
        });
        // resize hero slideshow according to the window width
        $(window).resize(function(){
            var width = $(window).width();
            if(width > 1280){
                $('#hero-region .slideshow').width(width);
                $('#hero-region .slideshow img').width(width);
            }
            else{
                $('#hero-region .slideshow').width(1280);
                $('#hero-region .slideshow img').width(1280);
            }
            $('#hero-region .slideshow').css('left', 0-(width-$(window).width())/2);
            if(width > 1280){
                $('#hero-search-bar').css('left',width/2-475);
            }
            else{
                $('#hero-search-bar').css('left',165);
            }
        });
        $(window).resize();
        
        // story-showcase slideshow
        $('#story-showcase-slideshow').jcarousel({
            wrap: 'circular'   
        }).on('jcarousel:scrollend', function(event, carousel) {
            $("img.lazy").trigger('lazyload');
        });
        $('.story-showcase .left-arrow').click(function(){
            $('#story-showcase-slideshow').jcarousel('scroll', '-=2');
        });
        $('.story-showcase .right-arrow').click(function(){
            $('#story-showcase-slideshow').jcarousel('scroll', '+=2');
        });

        $("img.lazy").lazyload({event: 'lazyload'});
        $("img.lazy").trigger('lazyload');
    });