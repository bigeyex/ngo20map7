function attach_autocomplete(id, source){
        var container_id = id+'-results';
        var on_text_change = function(e, autoselect_item){
            var dom = e.target;
            if(autoselect_item || $(dom).val()){
                $(dom).parent().addClass('has-text');
            }
            else{
                $(dom).parent().removeClass('has-text');
            }
        }

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
            select: on_text_change,
            minLength: 0 
        });
        $(id).keyup(on_text_change);
        $(id+'-cross').click(function(){
            $(id).val('');
            $(id).parent().removeClass('has-text');
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
        
    }
    
    $(function(){
        // auto completes
        attach_autocomplete("#search-input-type", type_categories);
        attach_autocomplete("#search-input-cause", cause_categories);
        attach_autocomplete("#search-input-region", region_categories);
        attach_autocomplete("#search-input-keyword", keyword_categories);
        
        // hero slideshow
        var jcarousel_api = $('#hero-region .slideshow').jcarousel({
            wrap: 'circular',
            animation: {
                duration: 1500
            }
        }).jcarouselAutoscroll({
            interval: 6000,
            target: '+=1',
            autostart: true
        });
        $('.icon-slideshow-left').click(function(){
            $('#hero-region .slideshow').jcarousel('scroll', '-=1');
        });
        $('.icon-slideshow-right').click(function(){
            $('#hero-region .slideshow').jcarousel('scroll', '+=1');
        });
        $('.search-filter-form input').focus(function(){
            jcarousel_api.jcarouselAutoscroll('stop');
        }).blur(function(){
            jcarousel_api.jcarouselAutoscroll('start');
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