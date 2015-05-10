var CT = CT || {};

CT.save = function(target){
    $.post(app_path+'/Form/save', $('#chuangtou_form').serialize(), function(feedback){
        if(feedback === 'ok'){
            toastr.success('保存成功');
        }
        else{
            toastr.error('保存失败');
        }
    });
}