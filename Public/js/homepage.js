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
                                        top: (top) + "px" });
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

        var city_carousel_handle = null;
        // resize hero slideshow according to the window width
        $(window).resize(function(){
            var width = $(window).width();
            if(width > 1280){
                var wrapWidth = $('.slideshow-wrapper').width() + 10;
                $('#hero-region .slideshow').width(wrapWidth);
                $('#hero-region .slideshow img').width(wrapWidth);
                $('#hero-region .slideshow').height('auto');
                $('#hero-region .slideshow img').height('auto');
            }
            else if(width < 980){
                $('#hero-region .slideshow').height(215);
                $('#hero-region .slideshow img').height(215);
                $('#hero-region .slideshow').width('auto');
                $('#hero-region .slideshow li').width(width);
                $('#hero-region .slideshow img').width('auto');
            }
            else{
                $('#hero-region .slideshow').width(1280);
                $('#hero-region .slideshow img').width(1280);
                $('#hero-region .slideshow').height('auto');
                $('#hero-region .slideshow img').height('auto');
            }
            $('#hero-region .slideshow').css('left', 0-(width-$(window).width())/2);
            if(width > 1280){
//                 $('#hero-search-bar').css('left',width/2-475);
            }
            else{
//                 $('#hero-search-bar').css('left',165);
            }

            if(width < 980){
                $('#search-input-keyword').attr('placeholder', '来寻找你的小伙伴吧');

                // init carousel
                if(!city_carousel_handle){
                    $('.city-list').owlCarousel({
                        singleItem: true
                    });

                    city_carousel_handle = $('.city-list').data('owlCarousel');
                }
            }
            else{
                // destroy city carousel
                if(city_carousel_handle){
                    city_carousel_handle.destroy();
                    city_carousel_handle = null;
                }
            }
        });
        $(window).resize();
        
        // story-showcase slideshow
        $('#story-showcase-slideshow,#story-showcase-slideshow2').owlCarousel({
            items: 3,
            lazyLoad: true,
            itemsDesktop: false,
            itemsDesktopSmall : false
        });
        var story_slideshow = $('#story-showcase-slideshow').data('owlCarousel');
        window.story_slideshow = story_slideshow;

        $('.story-showcase .left-arrow').click(function(){
            story_slideshow.prev();
        });
        $('.story-showcase .right-arrow').click(function(){
            story_slideshow.next();
        });
    });