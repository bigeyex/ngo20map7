var CT = CT || {};

CT.save = function(target){
    $.post(app_path+'/Form/save', $('#chuangtou_form').serialize(), function(feedback){
        if(feedback === 'ok'){
            toastr.success('保存成功');
            var form_id = $('#form_id').val();
            var form_page = $('#form_page').val();
            if(target == -2){
                window.location.href = app_path+'/Form/edit/form_id/'+form_id+'/page/'+
                    (parseInt(form_page)-1);
            }
            else if(target == -1){
                window.location.href = app_path+'/Form/edit/form_id/'+form_id+'/page/'+
                    (parseInt(form_page)+1);
            }
            else if(target != 0){
                window.location.href = app_path+'/Form/edit/form_id/'+form_id+'/page/'+target;
            }
        }
        else{
            toastr.error('保存失败');
        }
    });
}