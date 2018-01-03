// 修改密码
$(function() {
    $(document).on('click','.subPwd',function() {
        var pwd = $('.pwd').val();
        var npwd = $('.npwd').val();
        if(pwd == npwd){
            alert('新密码不能和原密码重复');
            return;
        }
        $.ajax({
            url:"upPassWord",
            type:'post',
            data:{pwd:pwd,npwd:npwd},
            error: function(request) {
                alert("Connection error");
            },
            success:function(msg){
                if(msg ==1){
                    alert('原密码不对');
                }else if(msg == 2){
                    alert('修改成功');
                    window.location.reload();
                }else{
                    alert('修改失败');
                }
            }
        })
    })
})

//恢复初始密码
$(function(){
    $(document).on('click','.startPwd',function(){
        var content = $('.startPwd').html();
        if(content == '是'){
            $.ajax({
                url:"startPwd",
                type:"post",
                data:{data:1},
                error:function(request){
                    alert('content error')
                },
                success:function(msg){
                    if(msg ==1){
                        alert('操作成功');
                        $('.startPwd').html('是');
                    }else if(msg ==3) {
                        alert('已经是初始密码');
                    }else{
                        alert('操作失败');
                    }
                }
            })

        }
    })
})

$(function(){
    $(document).on('click','.subForm',function() {
        $.ajax({
            type: "post",
            url: "selfForm",
            data: $('#idForm').serialize(),// 序列化表单值
            error: function (request) {
                alert("Connection error");
            },
            success: function (data) {
                if (data == 1) {
                    alert('保存成功');
                    window.location.reload();
                } else {
                    alert('保存失败');
                }
            },
            dateType: "html"
        });
    })
})

$(function(){
    $(document).on('click','.addForm',function() {
        var name = $('.name').val();
        var num = $('.num').val();
        var rank = $('.rank').val();
        var bankName = $('.bankName').val();
        var bankcardNo = $('.bankcardNo').val();
        var company = $('.company').val();
        var department = $('.department').val();
        var clas = $('.class').val();
        if(name == ''){
            alert('姓名不能为空');
            return false;
        }else if(num == ''){
            alert('账号不能为空');
            return false;
        }else if(bankName == ''){
            alert('银行名称不能为空');
            return false;
        }else if(bankcardNo == ''){
            alert('银行卡号不能为空');
            return false;
        }
        $.ajax({
            type: "post",
            url: "personForm",
            data: $('#addPersonForm').serialize(),// 序列化表单值
            error: function (request) {
                alert("Connection error");
            },
            success: function (data) {
                alert(data);
                if (data == 1) {
                    alert('添加成功');
                    window.location.reload();
                } else if(data ==3){
                    alert('请选择职务');
                }else if(data ==4){
                    alert('请选择单位');
                }else if(data ==5){
                    alert('请选择部门');
                }else if(data ==6){
                    alert('请选择课题组');
                }else{
                    alert('添加失败');
                }
            },
            dateType: "html"
        });
    })
})