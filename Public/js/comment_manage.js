$(function(){
    $('.reply-delete-button').click(function(){
        var $this = $(this);
        var $thispp = $this.parent().parent();
        var reply_to_id = $this.attr('reply-to');
        $.post(app_path+'/User/delete_comment', {id:reply_to_id}, function(result){
            if(result=='ok'){
                toastr.success('评论已删除');
                $thispp.html('<p class="deleted">评论已删除</p>');
            }
            else{
                toastr.error(result);
            }
        });
    });

    $('.reply-button').click(function(){
        var $this = $(this);
        var $thispp = $this.parent().parent();
        var reply_to_id = $this.attr('reply-to');
        
        var comment_box = $('<div class="reply-comment"><textarea></textarea><span class="reply-warning">回复后此评论将公开可见</span><button class="btn btn-primary">发表回复</button><button class="btn btn-cancel">取消</button></div>')
        comment_box.appendTo($thispp);
        var old_content = $thispp.find('.comment-reply');
        if(old_content){
            comment_box.find('textarea').val(old_content.text().trim().replace('回复: ', ''));
        }

        comment_box.find('.btn-cancel').click(function(){
            $this.parent().show();
            comment_box.remove();
        });

        comment_box.find('.btn-primary').click(function(){
            var comment_content = comment_box.find('textarea').val();
            $.post(app_path+'/User/reply_comment', {id:reply_to_id, content:comment_content}, function(result){
                if(result=='ok'){
                    toastr.success('成功发表评论');
                    old_content.text('回复: '+comment_content);
                    $this.parent().show();
                    comment_box.remove();
                }
                else{
                    toastr.error(result);
                }
            });
        });

        $this.parent().hide();
    });
});