String.prototype.format = function (args) {
    var result = this;
    if (arguments.length > 0) {
        if (arguments.length == 1 && typeof (args) == "object") {
            for (var key in args) {
                if (args[key] != undefined) {
                    var reg = new RegExp("({" + key + "})", "g");
                    result = result.replace(reg, args[key]);
                }
            }
        } else {
            for (var i = 0; i < arguments.length; i++) {
                if (arguments[i] != undefined) {
                    var reg = new RegExp("({)" + i + "(})", "g");
                    result = result.replace(reg, arguments[i]);
                }
            }
        }
    }
    return result;
}

function setData(res){
    return res;
}
function optionShow(optionClass){
    var optionVal = $(optionClass).children('option:selected').val();
    $('.editors').hide();
    for(var i=0;i<optionVal;i++){
        $('.editors').eq(i).show()
    }
}

function layerlocal() {
    $.NotifyBox.NotifyTwoCallTwo('添加成功','是否继续进行添加习题？','继续','去列表',function(){
        window.location.reload();//刷新当前页面.
    },function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    })
}

function layerlocalPaper() {

    $.NotifyBox.NotifyTwoCallTwo('添加成功','是否继续进行添加习题？','继续','去列表',function(){
        window.location.reload();//刷新当前页面.
    },function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=draftList";
    })

}

function showError(msg) {
    $.NotifyBox.NotifyPromptOne('错误信息',msg,'关闭');
}

function showImport(msg) {
    layer.alert(msg, {
        skin: 'layui-layer-molv',
        closeBtn: 0,
        title:'导入成功',
    },function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    });
}

function ImportError(msg) {
    layer.alert(msg, {
        skin: 'layui-layer-molv',
        closeBtn: 1,
        title:'导入错误信息',
    },function(){
        $('#errorImportSubmit').submit();
        $('.layui-layer-shade').hide();
        $('#layui-layer1').hide();
    });
}

function loadingImport(name) {
    $('body').loading({
        loadingWidth:240,
        title:'正在导入中...',
        name:name,
        discription:'正在导入...',
        direction:'row',
        type:'origin',
        originBg:'#71EA71',
        originDivWidth:30,
        originDivHeight:30,
        originWidth:4,
        originHeight:4,
        smallLoading:false,
        titleColor:'#388E7A',
        loadingBg:'#312923',
        loadingMaskBg:'rgba(22,22,22,0.2)'
    });
}

function showDetails(msg) {
    if (msg!=''&& msg!=undefined)
    $.NotifyBox.NotifyPromptOne('详细信息',msg,'关闭');
}

function isloading(name) {
    $('body').loading({
        loadingWidth:240,
        title:'正在加载中...',
        name:name,
        discription:'这是一个描述...',
        direction:'row',
        type:'origin',
        originBg:'#71EA71',
        originDivWidth:30,
        originDivHeight:30,
        originWidth:4,
        originHeight:4,
        smallLoading:false,
        titleColor:'#388E7A',
        loadingBg:'#312923',
        loadingMaskBg:'rgba(22,22,22,0.2)'
    });
}

function loading(name) {
    $('body').loading({
        loadingWidth:240,
        title:'正在提交中...',
        name:name,
        discription:'这是一个描述...',
        direction:'row',
        type:'origin',
        originBg:'#71EA71',
        originDivWidth:30,
        originDivHeight:30,
        originWidth:4,
        originHeight:4,
        smallLoading:false,
        titleColor:'#388E7A',
        loadingBg:'#312923',
        loadingMaskBg:'rgba(22,22,22,0.2)'
    });
}

function removeLoad(name) {
    removeLoading(name);
}


isloading('isloading');
$(document).ready(function(){
    setTimeout("remainTime()",1000);
});
function remainTime() {
    removeLoad('isloading');
}

$(document).on('click','.showDetails',function(){
    var Details = $(this).find('a').html();
    showDetails(Details);
})

$(document).on('click','.Canceloperation',function(){

    $.NotifyBox.NotifyTwoCallTwo('取消','取消后所有习题会重置？','继续编辑','取消',function(){

    },function(){
        window.location.reload();//刷新当前页面.
    })
})


$(document).on('click','.subjectLi>.color333',function(){
    $(this).addClass('color3baeab').siblings().removeClass('color3baeab')
})

$(document).on('click','.setAnswer',function(){
    $(this).addClass('active').siblings('button').removeClass('active').parent().siblings().find('.btn').removeClass('active')
})

$(document).on('click','.Deselect',function(){
    $(this).siblings('.btn').removeClass('active')
})


$(document).on('click','.duli',function(){
    window.location.href="index.php?m=Exercise&c=CreateExercise&a=createChoiceExercise";
})

$(document).on('click','.fuhe',function(){
    window.location.href="index.php?m=Exercise&c=CreateExercise&a=moreQuestions";
})


$(document).on('click','#batchReject',function(){
    $('#addRemarks').show();
});

$(document).on('click','.caogao',function(){
    window.location.href="index.php?m=Exercise&c=CreateExercise&a=draftList";
});

$(document).on('click','.subjectLi>.color333,.cityDiv>span',function(){
    $(this).addClass('color3baeab').siblings().removeClass('color3baeab')
})
var numArr = ['一','二','三','四','五','六','七','八','九','十'];

$(document).on('change','.paper_model_num',function(){

    $('.tableLi').children().remove()
    var moduleNum = $('#moduleNum').children('option:selected').val();

    if (moduleNum=='' || moduleNum <0 || moduleNum=='请选择') {
        return false;
    }
    var tableMathing = '';
    tableMathing += "<table style='width: 100%;' class='tableCommon dataTable no-footer'>";
    tableMathing += "<tbody>";
    for (var k = 0; k <5 ; k++) {
        tableMathing += "<tr>"

        switch (k) {
            case 0:
                tableMathing += "<td>大题题号</td>";
                for(var j=0;j<moduleNum;j++){
                    tableMathing += "<td>"+numArr[j]+"</td>";
                    tableMathing += '<input type="hidden" name="big_topic_asnumber[]" class="inputCommon big_topic_asnumber" value="'+numArr[j]+'">';
                }
                break;
            case 1:
                tableMathing += "<td>大题名称</td>";
                for(var j=0;j<moduleNum;j++){
                    tableMathing += '<td><input type="text" name="big_topic_name[]" class="inputCommon big_topic_name" placeholder="大题名称"></td>';
                }
                break;
            case 2:
                tableMathing += "<td>包含题数</td>";
                for(var j=0;j<moduleNum;j++){
                    tableMathing += '<td></td>';
                }
                break;
            case 3:
                tableMathing += "<td>大题说明</td>";
                for(var j=0;j<moduleNum;j++){
                    tableMathing += '<td><input type="text" name="big_topic_describe[]" class="inputCommon big_topic_describe" placeholder="大题说明"></td>';
                }
                break;
            /*case 4:
                tableMathing += "<td>操作</td>";
                for(var j=0;j<moduleNum;j++){
                    tableMathing += '<td><button type="button" name="button" class="btn mb10">添加独立体</button><br><button type="button" name="button" class="btn">添加复合题</button></td>';
                }
                break;*/
        }

        // }
        tableMathing += "</tr>"
    }
    tableMathing += "</tbody>"
    tableMathing += "</table>"
    $('.tableLi').append(tableMathing);
    $('.tableLi').append('<div class="red fz14 dn">*大题名称和大题说明都为必填项</div>')
})

$(document).on('click','.sureBtn',function(){
    //验证提交数据
    var questionCategory=''; //来源
    var gradeid=''; //年级
    var termid='';//分册
    var yearinfo='';//年份
    var regioninfo='';//地区
    var courseid='';//科目
    var cityid='';//省份
    var paperType='';//试卷类型
    var paper_name = '';//试卷名称
    var paper_model_num= '';//大题数量
    var dataList={};
    var big_topic_asnumber= new Array();//序号
    var big_topic_name= new Array();//大题名称
    var big_topic_describe= new Array();//大题说明

    $('.questionCategory').each(function(){
        if ($(this).hasClass('color3baeab')) {
            questionCategory =  $(this).attr('questionCategory');
        }
    })

    $('.Gradeid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            gradeid =  $(this).attr('gradeid');
        }
    })

    $('.Termid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            termid =  $(this).attr('termid');
        }
    })
    yearinfo = $('.yearinfo').val();
    regioninfo = $('.regioninfo').val();

    $('.Courseid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseid =  $(this).attr('courseid');
        }
    })

    $('.Cityid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            cityid =  $(this).attr('cityid');
        }
    })
    paperType = $('.paperType').val();
    paper_name = $('.paper_name').val();
    paper_model_num = $('.paper_model_num').val();

    $('.big_topic_asnumber').each(function(index){
        big_topic_asnumber[index]= $(this).val(); //序号
    })

    $('.big_topic_name').each(function(index){
        big_topic_name[index]= $(this).val(); //序号
    })

    $('.big_topic_describe').each(function(index){
        big_topic_describe[index]= $(this).val(); //序号

    })

    var token = $('.repeat_commit').val();

    dataList={questionCategory:questionCategory,gradeid:gradeid,termid:termid,yearinfo:yearinfo,regioninfo:regioninfo,courseid:courseid,cityid:cityid,paperType:paperType,paper_name:paper_name,paper_model_num:paper_model_num,big_topic_asnumber:big_topic_asnumber,big_topic_name:big_topic_name,big_topic_describe:big_topic_describe,token:token};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=createPaper', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:dataList,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                //removeLoad('createAnswerQuestions');
                //跳转
                window.location.href="index.php?m=Exercise&c=CreateExercise&a=editpaper&paperid="+res.id;
            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });

})

//修改试卷
$(document).on('click','.editsureBtn',function(){
    //验证提交数据
    var questionCategory=''; //来源
    var gradeid=''; //年级
    var termid='';//分册
    var yearinfo='';//年份
    var regioninfo='';//地区
    var courseid='';//科目
    var cityid='';//省份
    var paperType='';//试卷类型
    var paper_name = '';//试卷名称
    var paper_model_num= '';//大题数量
    var dataList={};
    var big_topic_asnumber= new Array();//序号
    var big_topic_name= new Array();//大题名称
    var big_topic_describe= new Array();//大题说明
    var big_question_id = new Array(); //大题id号
    var paper_id='';
    $('.questionCategory').each(function(){
        if ($(this).hasClass('color3baeab')) {
            questionCategory =  $(this).attr('questionCategory');
        }
    })

    $('.Gradeid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            gradeid =  $(this).attr('gradeid');
        }
    })

    $('.Termid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            termid =  $(this).attr('termid');
        }
    })
    yearinfo = $('.yearinfo').val();
    regioninfo = $('.regioninfo').val();

    $('.Courseid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseid =  $(this).attr('courseid');
        }
    })

    $('.Cityid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            cityid =  $(this).attr('cityid');
        }
    })
    paperType = $('.paperType').val();
    paper_name = $('.paper_name').val();
    paper_model_num = $('.paper_model_num').val();

    $('.big_topic_asnumber').each(function(index){
        big_topic_asnumber[index]= $(this).val(); //序号
    })

    $('.big_topic_name').each(function(index){
        big_topic_name[index]= $(this).val(); //序号
    })

    $('.big_topic_describe').each(function(index){
        big_topic_describe[index]= $(this).val(); //序号

    })

    $('.big_question_id').each(function(index){
        big_question_id[index]= $(this).val(); //序号

    })

    paper_id = $('.paper_id').val();

    var token = $('.repeat_commit').val();
    dataList={paper_id:paper_id,questionCategory:questionCategory,gradeid:gradeid,termid:termid,yearinfo:yearinfo,regioninfo:regioninfo,courseid:courseid,cityid:cityid,paperType:paperType,paper_name:paper_name,paper_model_num:paper_model_num,big_topic_asnumber:big_topic_asnumber,big_topic_name:big_topic_name,big_topic_describe:big_topic_describe,big_question_id:big_question_id,token:token};
    console.log(dataList);
    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=EditPaperInfo', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:dataList,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');
                //跳转
                layerlocalPaper();
            } else {
                removeLoad('createAnswerQuestions');
                $.NotifyBox.NotifyPromptOne('错误信息',res.msg,'关闭');
            }
        }
    });

})


$(document).on('click','.addExercise',function(){
    var big_paper_id = $(this).attr('big_paper_id');
    var paper_id = $('.paper_id').val();
    var url = "/index.php?m=Exercise&c=CreateExercise&a=createChoiceExercise&big_paper_id="+big_paper_id+"&paper_id="+paper_id;
    console.log(url);
    layer.open({
        type: 2,
        title: '添加习题',
        shadeClose: true,
        fixed: false, //不固定
        shade: 0.8,
        area: ['80%', '80%'],
        maxmin: true,
        content: url , //iframe的url
    });
})

function layerpaper(paperid) {
    /*layer.confirm('添加成功,是否继续进行添加习题？', {
        btn: ['继续','查看'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
        parent.location.reload();
        //window.location.href="index.php?m=Exercise&c=createExercise&a=editPaper&paperid="+paperid;
    });*/

    $.NotifyBox.NotifyTwoCallTwo('添加成功','添加成功,是否继续进行添加习题？','继续','查看',function(){
        window.location.reload();
    },function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.location.reload();
        parent.layer.close(index);
    })
}

function layerlocal() {
   /* layer.confirm('修改成功,是否继续进行习题修改？', {
        btn: ['继续','去列表'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        window.history.go(-1);
    });*/

    $.NotifyBox.NotifyTwoCallTwo('修改成功','修改成功,是否继续进行习题修改？','继续','去列表',function(){
        window.location.reload();//刷新当前页面.
    },function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    })
}

function deleteQuestion(id,bigid)
{
    $.NotifyBox.NotifyTwoCallTwo('确定','确定删除该习题？','确定','取消',function(){
        loading('loadData');
        $.ajax({
            url : "/index.php?m=Exercise&c=ExerciseState&a=deleteExercise",//这个就是请求地址对应sAjaxSource
            data : {ids:id,bigid:bigid},//这个是把datatable的一些基本数据传给后台,比如起始位置,每页显示的行数
            type : 'post',
            dataType : 'json',
            async : true,
            success : function(result) {
                removeLoad('loadData');
                if(result.status == 200) {
                    window.location.reload();//刷新当前页面.
                } else {
                    $.NotifyBox.NotifyOne('错误',result.message,'确定');
                }
            },
            error : function(msg) {
                removeLoad('loadData');
                $.NotifyBox.NotifyOneCall('错误','删除失败','确定');
            }
        });
    })

}

function editQuestion(exercise_id,paper_id) {
    var url = "/index.php?m=Exercise&c=CreateExercise&a=editChoiceExercise&paper_id="+paper_id+'&id='+exercise_id;
    layer.open({
        type: 2,
        title: '修改习题',
        shadeClose: true,
        fixed: false, //不固定
        shade: 0.8,
        area: ['80%', '80%'],
        maxmin: true,
        content: url , //iframe的url
    });
}


//提交试卷

$(document).on('click','.submitPaper',function(){
    var paper_id = $(this).attr('paper_id');
    $.NotifyBox.NotifyTwoCallTwo('确定','确定要提交试卷吗？ 提交后进入审核流程并且不能修改','确定','取消',function(){
        loading('loadData');
        $.ajax({
            url : "/index.php?m=Exercise&c=CreateExercise&a=submitPaper",//这个就是请求地址对应sAjaxSource
            data : {paper_id:paper_id},//这个是把datatable的一些基本数据传给后台,比如起始位置,每页显示的行数
            type : 'post',
            dataType : 'json',
            async : true,
            success : function(result) {
                removeLoad('loadData');
                if(result.status == 200) {
                    window.location.href="index.php?m=Exercise&c=CreateExercise&a=testEntering";
                } else {
                    $.NotifyBox.NotifyOne('错误',result.message,'确定');
                }
            },
            error : function(msg) {
                removeLoad('loadData');
                $.NotifyBox.NotifyOneCall('错误','提交失败请重试！','确定');
            }
        });
    })
})
