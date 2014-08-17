$(function(){
    var name_check_keyup_timer = null;
    $('#inputName').keyup(function(){
        if(name_check_keyup_timer){
            clearTimeout(name_check_keyup_timer);
        }
        name_check_keyup_timer = setTimeout(function(){
            var name = $('#inputName').val();
            $('.name-checking').html('正在检查机构名称有没有被使用...');
            if(name == ''){
                $('.name-checking').html('请首先填写机构名称');
                $('.other-fields').hide();
                return;
            }
            $.get(app_path+'/User/ajax_check_name/name/'+name, function(res){
                if(!res){
                    $('.name-checking').html('该机构在地图上尚无记录，欢迎入驻公益地图');
                    $('.other-fields').show();
                }
                else{
                    // $('.name-checking').html('该机构已经在地图上注册。您可以<a href="'+app_path+'/User/coauthor/id/'+res
                    //                             +'">申请成为协作者</a>');
                    $('.name-checking').html('该机构已经在地图上注册。');
                    $('.other-fields').hide();
                }
            });
        }, 1000);
    });
})