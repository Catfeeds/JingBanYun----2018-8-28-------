function courseClick( id ) {
    $.post('index.php?m=Exercise&c=CreateExercise&a=getCourseChild', {id: id}, function (res) {
        var options = [];
        var tplone = '<option value="{0}">{1}</option>';
        $.each(res, function (index, item) {
            options.push( tplone.format(item.id, item.name) );
        });
        $('.presentation').find('option').remove();
        $('.presentation').append('<option value="0" selected>请选择</option>'+options.join(''));
    })
}

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


$(document).on('click','.addExercise',function(){
    var studyIddata;
    var courseIddata;
    var type;
    var id;
    var subject;
    var answer_id;
    var presentation_type;
    var exerciseNums;
    var Fractiondata;
    var arrayMessage = new Array();
    var fraction = new Array();

    $('.study_id').each(function(){
        if ($(this).hasClass('color3baeab')) {
            studyIddata =  $(this).attr('study_id');
        }
    });

    $('.CourseVdata').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseIddata =  $(this).attr('courseid');

        }
    })
    type = $('.question_type').val();
    id = $('.id').val();
    subject = $('.subject_type').val();

    $('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            answer_id =  $(this).attr('answer_id');
        }
    })
    presentation_type = $('.presentation').val();
    exerciseNums = $('.exerciseNums').val();
    Fractiondata = $('.Fractiondata').val();

    for (var i=0;i<clickbefore;i++) {
        arrayMessage[i] = messageArray[i].getContent();//答案内容
        fraction[i] = $('.fraction'+i).val();//答案的分数
    }
    Fractiondata = fraction.join(",");

    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();

    var token = $('.repeat_commit').val();
    previewCompletion()
    var json_html = $('.exerciseTitle').html();
    layer.closeAll();
    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:completionA.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:answer_id,Fractiondata:Fractiondata,messageJx:messageJx.getContent(),arrayMessage:arrayMessage,big_paper_id:big_paper_id,paper_id:paper_id,token:token,html:json_html};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=addExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');

                if (big_paper_id>0 && paper_id>0 ) {
                    layerpaper(paper_id);
                } else {
                    layerlocal();
                }
            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });

})

$(document).on('click','.xuanzhetiankongExercise',function(){
    var studyIddata;
    var courseIddata;
    var type;
    var id;
    var subject;
    var answer_id;
    var presentation_type;
    var exerciseNums;
    var Fractiondata;
    var arrayMessage = new Array();
    var  AnswerInfo = new Array();
    var fraction = new Array();

    $('.study_id').each(function(){
        if ($(this).hasClass('color3baeab')) {
            studyIddata =  $(this).attr('study_id');
        }
    });

    $('.CourseVdata').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseIddata =  $(this).attr('courseid');

        }
    })
    type = $('.question_type').val();
    id = $('.id').val();
    subject = $('.subject_type').val();

    $('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            answer_id =  $(this).attr('answer_id');
        }
    })
    presentation_type = $('.presentation').val();
    exerciseNums = $('.exerciseNums').val();

    for (var i=0;i<exerciseNums;i++) {
        arrayMessage[i] = AnswerMessageArray[i].getContent();//答案选项
    }

    for (var j=0;j<clickbefore;j++) {
        AnswerInfo[j] = messageArray[j].getContent();//答案
        fraction[j] = $('.fraction'+j).val();//答案的分数

    }
    Fractiondata = fraction.join(",");

    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();

    var token = $('.repeat_commit').val();
    previewCompletionChoice('.previewAllBtn');
    var json_html = $('.exerciseTitle').html();
    layer.closeAll();
    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:completionA.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:answer_id,Fractiondata:Fractiondata,messageJx:messageJx.getContent(),arrayMessage:AnswerInfo,answer_select:arrayMessage,big_paper_id:big_paper_id,paper_id:paper_id,token:token,answerWidth:answerWidth,html:json_html};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=addExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');
                if (big_paper_id>0 && paper_id>0 ) {
                    layerpaper(paper_id);
                } else {
                    layerlocal();
                }
            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });

})


$(document).on('click','.lianxianExercise',function(){
    var studyIddata;
    var courseIddata;
    var type;
    var id;
    var subject;
    var answer_id;
    var presentation_type;
    var exerciseNums;
    var Fractiondata='';
    var arrayMessage = new Array();
    var  AnswerInfo = new Array();
    var fraction = new Array();

    $('.study_id').each(function(){
        if ($(this).hasClass('color3baeab')) {
            studyIddata =  $(this).attr('study_id');
        }
    });

    $('.CourseVdata').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseIddata =  $(this).attr('courseid');

        }
    })
    type = $('.question_type').val();
    id = $('.id').val();
    subject = $('.subject_type').val();

    $('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            answer_id =  $(this).attr('answer_id');
        }
    })
    presentation_type = $('.presentation').val();
    exerciseNums = $('.answerNumSel').val();

    for (var j=0;j<exerciseNums;j++) {
        var inputval = $('.mathAnswer').find('div').eq(j+1).find('.AnswerInfo').val();
        var fractionj = $('.mathAnswer').find('div').eq(j+1).find('.fraction').val();
        if (fractionj!='' && fractionj!=undefined) {
            Fractiondata += fractionj+',';
        }
        AnswerInfo.push(inputval);
    }

    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();
    var token = $('.repeat_commit').val();
    $('.matchPreview').click();
    var json_html = $('.exerciseTemplate').prop('outerHTML');
    layer.closeAll();
    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:completionA.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:answer_id,Fractiondata:Fractiondata,messageJx:messageJx.getContent(),arrayMessage:AnswerInfo,answer_select:tableUE.getContent(),big_paper_id:big_paper_id,paper_id:paper_id,token:token,html:json_html};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=addExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');

                if (big_paper_id>0 && paper_id>0 ) {
                    layerpaper(paper_id);
                } else {
                    layerlocal();
                }

            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });

})

$(document).on('click','.jiedaaddExercise',function(){

    var studyIddata;
    var courseIddata;
    var type;
    var id;
    var subject;
    var answer_id;
    var presentation_type;
    var exerciseNums;
    var Fractiondata;
    var arrayMessage = new Array();
    var count_score;

    var showMessage=['A','B','C','D','E','F','G','H'];

    $('.study_id').each(function(){
        if ($(this).hasClass('color3baeab')) {
            studyIddata =  $(this).attr('study_id');
        }
    });

    $('.CourseVdata').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseIddata =  $(this).attr('courseid');

        }
    })
    type = $('.question_type').val();
    id = $('.id').val();
    subject = $('.subject_type').val();

    $('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            answer_id =  $(this).attr('answer_id');
        }
    })

    count_score = $('.count_score').val();
    presentation_type = $('.presentation').val();
    exerciseNums = $('.exerciseNums').val();
    Fractiondata = $('.Fractiondata').val();

    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();
    var token = $('.repeat_commit').val();
    $('.previewAllBtn').click();
    var json_html = $('.exerciseTemplate').prop('outerHTML');
    layer.closeAll();
    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:completionA.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:Answermessage1.getContent(),Fractiondata:count_score,messageJx:messageJx.getContent(),arrayMessage:Answermessage1.getContent(),big_paper_id:big_paper_id,paper_id:paper_id,token:token,html:json_html};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=addExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');

                if (big_paper_id>0 && paper_id>0 ) {
                    layerpaper(paper_id);
                } else {
                    layerlocal();
                }

            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });

})


$(document).on('click','.zuotuaddExercise',function(){

    var studyIddata;
    var courseIddata;
    var type;
    var id;
    var subject;
    var answer_id;
    var presentation_type;
    var exerciseNums;
    var Fractiondata;
    var arrayMessage = new Array();
    var count_score;

    var showMessage=['A','B','C','D','E','F','G','H'];

    $('.study_id').each(function(){
        if ($(this).hasClass('color3baeab')) {
            studyIddata =  $(this).attr('study_id');
        }
    });

    $('.CourseVdata').each(function(){
        if ($(this).hasClass('color3baeab')) {
            courseIddata =  $(this).attr('courseid');

        }
    })
    type = $('.question_type').val();
    id = $('.id').val();
    subject = $('.subject_type').val();

    $('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            answer_id =  $(this).attr('answer_id');
        }
    })

    count_score = $('.count_score').val();
    presentation_type = $('.presentation').val();
    exerciseNums = $('.exerciseNums').val();
    Fractiondata = $('.Fractiondata').val();

    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();
    var token = $('.repeat_commit').val();
    $('.previewAllBtn').click();
    var json_html = $('.exerciseTemplate').prop('outerHTML');
    layer.closeAll();
    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:completionA.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:Answermessage1.getContent(),Fractiondata:count_score,messageJx:messageJx.getContent(),arrayMessage:Answermessage1.getContent(),big_paper_id:big_paper_id,paper_id:paper_id,token:token,html:json_html};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=addExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');

                if (big_paper_id>0 && paper_id>0 ) {
                    layerpaper(paper_id);
                } else {
                    layerlocal();
                }

            } else {
                removeLoad('createAnswerQuestions');
                showError(res.msg);
            }
        }
    });
})


/*
function layerpaper(paperid) {
    layer.confirm('添加成功,是否继续进行添加习题？', {
        btn: ['继续','查看'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
        parent.location.reload();
        //window.location.href="index.php?m=Exercise&c=createExercise&a=editPaper&paperid="+paperid;
    });
}

function layerlocal() {
    layer.confirm('添加成功,是否继续进行添加习题？', {
        btn: ['继续','去列表'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    });
    $('.layui-layer-btn0').css('background-color','#3baeab');
}*/

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

    $.NotifyBox.NotifyTwoCallTwo('添加成功','添加成功,是否继续进行习题添加？','继续','去列表',function(){
        window.location.reload();//刷新当前页面.
    },function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
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

function continueEditing() {

    var nextEId = $('.nextE').val();

    if (nextEId!='' && nextEId!= undefined) {

        $.NotifyBox.NotifyTwoCallTwo('标引','提交标引成功','继续校审下一道','去列表',function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=examinationQuestions&id="+nextEId;
        },function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==";
        })

    } else {
        $.NotifyBox.NotifyTwoCallOneGrayDle('标引', '提交标引成功', '继续校审下一道', '去列表', function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==";
        })
    }
}

function continueNoEditing() {

    var nextEId = $('.nextE').val();

    if (nextEId!='' && nextEId!= undefined) {

        $.NotifyBox.NotifyTwoCallTwo('校审','提交成功','继续校审下一道','去列表',function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=examinationQuestions&id="+nextEId;
        },function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==";
        })

    } else {
        $.NotifyBox.NotifyTwoCallOneGrayDle('校审', '提交成功', '继续校审下一道', '去列表', function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==";
        })
    }

}

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
    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();
    window.location.href="index.php?m=Exercise&c=CreateExercise&a=createChoiceExercise&big_paper_id="+big_paper_id+"&paper_id="+paper_id;
})

$(document).on('click','.fuhe',function(){
    var big_paper_id = $('.big_paper_id').val();
    var paper_id = $('.paper_id').val();
    window.location.href="index.php?m=Exercise&c=CreateExercise&a=moreQuestions&big_paper_id="+big_paper_id+"&paper_id="+paper_id;
})

$('#batchReject').click(function(){
    $('#addRemarks').show();
});

//审核习题为返工
$('#addRemarksSure').unbind().click(function(){
    var reason = $('.remarksMain').val().trim();
    var id = $(this).attr('pid');
    var state = $(this).attr('state');

    if(reason == '')
    {
        $.NotifyBox.NotifyOne('提示','请输入不通过原因','确定');
    }
    else {
        $('.fullBack,#addRemarks').hide();
        reject(id,state,reason);
    }

})

function reject( id,state,reason ) {
    var requestdata={ids:id,state:state,additionalInfo:reason}
    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=ExerciseState&a=setExerciseState', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        dataType:'json',
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            console.log(res);
            if (res.status ==200) {
                removeLoad('createAnswerQuestions');
                continueNoEditing();
            } else {
                removeLoad('createAnswerQuestions');
                showError(res.message);
            }
        }
    });
}

// 添加备注
$('#addRemarksCancel').click(function(){
    $('.fullBack,#addRemarks').hide();
})

//提交标引，审核通过

$('#biaoyin').click(function(){
    var id = $(this).attr('pid');
    var state = $(this).attr('state');
    $.NotifyBox.NotifyTwoCallTwo('标引','确定要标引吗？','确定','取消',function(){
        var requestdata={ids:id,state:state}
        $.ajax({
            type: "POST",   //访问WebService使用Post方式请求
            url: 'index.php?m=Exercise&c=ExerciseState&a=setExerciseState', //调用WebService的地址和方法名称组合---WsURL/方法名
            data:requestdata,
            dataType:'json',
            beforeSend:function () {
                loading('createAnswerQuestions');
            },
            success: function(res){
                console.log(res);
                if (res.status ==200) {
                    removeLoad('createAnswerQuestions');
                    continueEditing();
                } else {
                    removeLoad('createAnswerQuestions');
                    showError(res.message);
                }
            }
        });
    })
})
