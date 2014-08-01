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
	

});

/**********************
*   class Dispatcher
*   a global event dispatcher
**********************/
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
        }
    };
}
window.dispatcher = new Dispatcher();