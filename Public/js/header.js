$(function(){
	$(".fancybox").fancybox({
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		fitToView : false,
		topRatio : 0.1,
		closeBtn : false,
		afterShow: function(){
			if($().placeholder !== undefined){
				$('input textarea').placeholder();
			}
		}
	});
	
    $('#header-search-input').focus(function(){
        $('#header-search-result').show();
    }).blur(function(){
        $('#header-search-result').hide();
    });
    $('#header-search-result').on('mousedown', 'li',function(e){
        window.location.href = $(this).find('a').attr('href');
    });
    $('#header-search-result').on('mousedown', 'ul',function(e){
        console.log(this);
    });

    var search_timer = null;
    $('#header-search-input').keyup(function(){
        if(search_timer !== null){
            clearTimeout(search_timer);
            search_timer = null;
        }
        search_timer = setTimeout(function(){
            var input_value = $('#header-search-input').val();
            if(input_value != ''){
                $('#header-search-result').empty().append('<span class="search-sub-text">搜索中...</span>');
                $('#header-search-result').load(app_path+'/Index/mini_search/q/'+input_value);
            }
            else{
                $('#header-search-result').empty().append('<span class="search-sub-text">输入关键词开始搜索</span>');
            }
            
        }, 500);
        
    });

});

function pre_delete(e){
    var dom = $(e.currentTarget);
    dom.next().show();
    dom.hide();
    setTimeout(function(){
        if(dom){
            dom.show();
            dom.next().hide();
        }
    }, 2000);
}
/*
    Dispatcher module
    by: wangyu (bigeyex@gmail.com)
    dispatch global event
    usage:
        - fire an event:
            dispatcher.dispatch('example.event.name', arg1, arg2...)
        - subscribe an event:
            dispatcher.subscribe('example.event.name', function(arg1, arg2...){});

*/
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
window.dispatcher = new Dispatcher();

/* login related functions */
function do_login(){
    // check it first
    $.post(app_path+'/Account/login', $('.email-login-form').serializeArray(), function(result){
        if(result == 'ok'){
            if(!dispatcher.dispatch('login')){
                window.location.href=app_path+"/Account/login_redirect";
            }
        }
        else{
            $('.login-error-bar').show().text(result);
        }
    });
}

function do_register(){
    $.post(app_path+'/Account/email_register', $('.email-register-form').serializeArray(), function(result){
        if(result == 'ok'){
            if(!dispatcher.dispatch('login')){
                window.location.href=app_path+"/Account/login_redirect";
            }
        }
        else{
            $('.register-error-bar').show().text(result);
        }
    });
}

function signup_keydown(e){
    if(e.keyCode == 13){
        $('.login-button').click();
    } 
}
