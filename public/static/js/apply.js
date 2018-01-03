//通过
$(function() {
    $('.ajx').on('click','.ap_pass',function(){
    // $('.ajx .ap_pass').click(function () {
        var no = $(this).attr('pno');
        $.ajax({
            url:"apass",
            type:"post",
            data:{no:no},
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

//未通过
$(function() {
    $('.ajx').on('click','.ap_nopass',function(){
        var no = $(this).attr('npno');
        $.ajax({
            url:"npass",
            type:"post",
            data:{no:no},
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

//d2通过
$(function() {
    $('.ad2').on('click','.d2_pass',function(){
        // $('.ajx .ap_pass').click(function () {
        var no = $(this).attr('pno');
        $.ajax({
            url:"dapass",
            type:"post",
            data:{no:no},
            success:function(msg){
                if(msg == 1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert('操作失败');
                }
            }
        })
    })
})

//未d2通过
$(function() {
    $('.ad2').on('click','.d2_nopass',function(){
        var no = $(this).attr('npno');
        $.ajax({
            url:"dnpass",
            type:"post",
            data:{no:no},
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

//d1说明
$(function(){
    $(document).on('click','.d1_explain',function(){
        var no = $('.d1_explain').attr('d1_epno');
        $('#dtext').attr('tno',no);
        $('.aptext').css('display','none');
        $('.d2text').css('display','block');
        $('.ad4').css('display','none');
        $('.ad3').css('display','none');
        $('.ad2').css('display','none');
        $('.ad1').css('display','none');
        $.ajax({
            type: "post",
            url: "dsave",
            data: {no: no},
            success: function (msg) {
                $('#dtext').val( msg.apExplain);

            },
            dataType:"json"
        })
    })
})

//d2说明
$(function(){
    $(document).on('click','.s2 .d2_explain',function(){
        var no = $('.d2_explain').attr('epno');
        $('#dtext').attr('tno',no);
        $('.aptext').css('display','none');
        $('.d2text').css('display','block');
        $('.ad4').css('display','none');
        $('.ad3').css('display','none');
        $('.ad2').css('display','none');
        $('.ad1').css('display','none');
        $.ajax({
            type: "post",
            url: "dsave",
            data: {no: no},
            success: function (msg) {
                $('#dtext').val( msg.apExplain);

            },
            dataType:"json"
        })
    })
})

//d2保存说明
$(function(){
    $('.apsave').click(function(){
        var text = $('#dtext').val();
        var no = $('#dtext').attr('tno');
        $.ajax({
            type: "post",
            url: "dsavetext",
            data: {text: text,no:no},
            success: function (msg) {
                if(msg == 1){
                    alert('操作成功');
                    window.location.reload();
                }else{
                    alert('操作失败');
                }
            }
        })
    })
})

//出租返回
$(function(){
    $('.appreturn').click(function(){
        $('.ad1').css('display','none');
        $('.ad4').css('display','none');
        $('.ad2').css('display','none');
        $('.ad3').css('display','block');
        $('.aptext').css('display','none');
        $('.d2text').css('display','none');
    })
})

//d2 返回
$(function(){
    $('.apreturn').click(function(){
        window.location.href="index";
        // $('.ad1').css('display','none');
        // $('.ad4').css('display','none');
        // $('.ad2').css('display','block');
        // $('.ad3').css('display','none');
        // $('.aptext').css('display','none');
        // $('.d2text').css('display','none');
    })
})

