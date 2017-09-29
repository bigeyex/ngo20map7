(function() {
    3
    var hasFlash = function (a,b) {
        try {
            a = new ActiveXObject(a+b+'.'+a+b);
        } catch (e) {
            a = navigator.plugins[a+' '+b];
        }
        return !!a;
    }('Shockwave', 'Flash');

    function isPC(){
        var userAgentInfo = navigator.userAgent;
        var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = false; break; }
        }
        return flag;
    }

    if (!hasFlash && isPC()) $(window).load(function() {
        if(window.confirm('未发现Flash插件，是否启用或安装？')) {
            window.location.href = 'http://get.adobe.com/flashplayer/';
        }
    });
}());