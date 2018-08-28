//点击浏览按钮
$('.browse_button').click(function(){       console.log(331);
    //清空input框
    $("#address").val('');
    $('.file_csv').val('');
    $('.file_csv').trigger('click');
});

//input file发生变化
$('.file_csv').live('change',function(){    
    var filepath = $('.file_csv').val();
    var extStart = filepath.lastIndexOf(".");
    var ext = filepath.substring(extStart, filepath.length).toUpperCase();
    if(ext=='.CSV'){
        $('#address').val(filepath); 
    }else{
        // $.Notify({
        //     caption: '提示',
        //     content: '只支持csv文件上传',
        //     type: 'warning'
        // });
        $.NotifyBox.NotifyOne('注意', '只支持csv文件上传','确定');
        $('.file_csv').val('');
    }
});

var ajax_result=''; 
function upload_file(){
    if($('#file_csv').val()=='' || !$('#file_csv').val()){
        $.NotifyBox.NotifyOne('提示', '请选择需要上传的csv格式文件', '确定');
        // $.Notify({
        //     caption: '提示',
        //     content: '请选择需要上传的csv格式文件',
        //     type: 'warning'
        // });
    }else{
        $("#listWrapperSuccess").find('#body').empty();
        $("#listWrapperFail").find('#body').empty();
        $("#listWrapperSuccessOutter, #listWrapperSuccess").hide();
        $("#listWrapperFailOutter, #listWrapperFail").hide();
        $("#address").val('');
        $.ajaxFileUpload
        (
            {
                url: file_upload_url, //用于文件上传的服务器端请求地址
                secureuri: false, //是否需要安全协议，一般设置为false
                fileElementId: 'file_csv', //文件上传域的ID
                dataType: 'text', //返回值类型 一般设置为json
                success: function (data)  //服务器成功响应处理函数
                {   
                    data = eval('(' + data + ')');
                    ajax_result=data;
                    $('.file_csv').val('');//清空file
                    upload_management(ajax_result);
                },
                error: function (data, status, e)//服务器响应失败处理函数
                {
                    console.log(e);
                    console.log(status);
                    alert('导入失败,请刷新页面后重试');
                }
            }
        )
    }
}


$(".export_btn").click(function(){
        var check_input=$('#body').find("[name='checkbox']:checked");
        if(check_input.length<1){
            // $.Notify({
            //     caption: '提示',
            //     content: '请勾选您要导出的账号',
            //     type: 'warning'
            // });
            $.NotifyBox.NotifyOne('提示', '请勾选您要导出的账号', '确定');
            return false;
        }
        var form="";
        form = $("<form></form>");
        form.attr('action',export_btn_url);
        form.attr('method','post');

        for(var i=0;i<check_input.length;i++){ 
            var temp_val=$(check_input[i]).attr('attr'); 
            var temp= $("<input type='hidden' name='hid[]'/>")
            $(temp).attr('value',temp_val);
            form.append(temp);
        } 
        form.appendTo("body");
        form.css('display','none');
        form.submit();
    });


    var temp_option="<option value='0'>-请选择-</option>";
        //省份发生变化
        $('#province_list').change(function(){ 
            $("#city_list option:not(:eq(0))").remove();    
            $("#district_list option:not(:eq(0))").remove();
            $("#school_list option:not(:eq(0))").remove();
            var id=$("#province_list").val(); 
            $.ajax({
                type:"post",
                url:"index.php?m=Admin&c=Common&a=getCityByProvince",
                dataType:"json",
                data:{'province_id':id},
                success: function(msg){ 
                    for(var i=0;i<msg.data.length;i++){
                        var clone_option=$(temp_option).clone(true);
                        $(clone_option).val(msg['data'][i].id);
                        $(clone_option).text(msg['data'][i].name);
                        $("#city_list").append(clone_option);
                    }
                }
            })
        }); 
        //城市发生变化
        $('#city_list').change(function(){ 
            $("#district_list option:not(:eq(0))").remove();
            $("#school_list option:not(:eq(0))").remove();
            var id=$("#city_list").val(); 
            $.ajax({
                type:"post",
                url:"index.php?m=Admin&c=Common&a=getDistrictByCity",
                dataType:"json",
                data:{'city_id':id},
                success: function(msg){ 
                    for(var i=0;i<msg.data.length;i++){
                        var clone_option=$(temp_option).clone(true);
                        $(clone_option).val(msg['data'][i].id);
                        $(clone_option).text(msg['data'][i].name);
                        $("#district_list").append(clone_option);
                    }
                }
            })
        });
        //区县发生变化
        $('#district_list').change(function(){ 
            $("#school_list option:not(:eq(0))").remove();
            var id=$("#district_list").val(); 
            $.ajax({
                type:"post",
                url:"index.php?m=Admin&c=Common&a=getSchoolByDistrict",
                dataType:"json",
                data:{'district_id':id},
                success: function(msg){ 
                    for(var i=0;i<msg.data.length;i++){
                        var clone_option=$(temp_option).clone(true);
                        $(clone_option).val(msg['data'][i].id);
                        $(clone_option).text(msg['data'][i].name);
                        $("#school_list").append(clone_option);
                    }
                }
            })
        });
