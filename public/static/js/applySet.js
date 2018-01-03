//提交 限制金额
$(function() {
    $(document).on('click','.setSub',function(){
        var price = $('.setp input').val();
        $.ajax({
            url:"limitP",
            type:"post",
            data:{price:price},
            success:function(msg){
                if(msg == 1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert('操作失败');
                }
            },
            dataType:'html',
        })
    })
})