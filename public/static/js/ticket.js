// 出租车保存
$(function(){
    $(document).on('click','.czbtn',function(){
        $.ajax({
            type: "post",
            url:"cSave",
            data:$('#taxiForm').serialize(),// 序列化表单值
            error: function(request) {
                alert("Connection error");
            },
            success: function(data) {
                if(data ==1){
                    alert('保存成功');
                }else{
                    alert('保存失败');
                }
            },
            dateType:"html"
        });
    })
})

// 火车保存
$(function(){
    $(document).on('click','.hcbtn',function(){
        $.ajax({
            type: "post",
            url:"hSave",
            data:$('#trainForm').serialize(),// 序列化表单值
            error: function(request) {
                alert("Connection error");
            },
            success: function(data) {
                if(data ==1){
                    alert('保存成功');
                }else{
                    alert('保存失败');
                }
            },
            dateType:"html"
        });
    })
})

// 飞机保存
$(function(){
    $(document).on('click','.fjbtn',function(){
        $.ajax({
            type: "post",
            url:"fSave",
            data:$('#planeForm').serialize(),// 序列化表单值
            error: function(request) {
                alert("Connection error");
            },
            success: function(data) {
                if(data ==1){
                    alert('保存成功');
                }else{
                    alert('保存失败');
                }
            },
            dateType:"html"
        });
    })
})