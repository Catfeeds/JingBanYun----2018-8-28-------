var vm = new Vue({
    el: '#app',
    data: {
        course:'',
        presentation:'',
    },
    created: function () { //应用初始化完毕加载

    },
    methods: {

        /*courseClick: function (id) {

            this.$http.post('index.php?m=Exercise&c=CreateExercise&a=getCourseChild',{id:id},{emulateJSON:'application/x-www-form-urlencoded'}).then(function(response){ //发送http请求
                this.presentation=eval(response.data);
            }, function(response){
                // 响应错误回调
                console.log('获取展示类型失败');
            });
        },*/

    }
})
function courseClick( id ) {
    $.post('index.php?m=Exercise&c=CreateExercise&a=getCourseChild', {id: id}, function (res) {
        var options = [];
        var tplone = '<option value="{0}">{1}</option>';
        $.each(res, function (index, item) {
            options.push( tplone.format(item.id, item.name) );
        });
        $('.presentation').find('option').remove();
        $('.presentation').append(options.join(''));
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
    var studyIddata='';
    var courseIddata='';
    var type='';
    var id='';
    var subject='';
    var answer_id='';
    var presentation_type='';
    var exerciseNums='';
    var Fractiondata='';
    var arrayMessage = new Array();
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
            answer_id += ',' + $(this).attr('answer_id');
        }
    })
    answer_id = answer_id.replace(',', '');

    presentation_type = $('.presentation').val();
    exerciseNums = $('.exerciseNums').val();
    Fractiondata = $('.Fractiondata').val();

    for (var i=0;i<exerciseNums;i++) {
        var testVar;
        eval('testVar=message'+showMessage[i]);
        arrayMessage[i] = testVar.getContent();
    }

    var _send_paperid = $('._send_paperid').val();

    var token = $('.repeat_commit').val();

    preview('.textSpan');
    var json_html = $(".exerciseTemplate").prop("outerHTML");
    layer.closeAll()

    var requestdata={id:id,studyId:studyIddata,courseId:courseIddata,type:type,message:ueMessage.getContent(),Problempresentation:presentation_type,exerciseNums:exerciseNums,trueAnswer:answer_id,Fractiondata:Fractiondata,messageJx:messageJx.getContent(),arrayMessage:arrayMessage,_send_paperid:_send_paperid,token:token,answerWidth:answerWidth,html:json_html};
    console.log(requestdata);
    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=editChoiceExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');
                if (_send_paperid>0) {
                    layerpaper(_send_paperid);
                } else {
                    layerlocal();
                }
            } else {
                removeLoad('createAnswerQuestions');
                console.log("修改失败");
                showError(res.msg);
            }
        }
    });


})

/*function layerlocal() {
    layer.confirm('修改成功,是否继续进行习题修改？', {
        btn: ['继续','去列表'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    });
    $('.layui-layer-btn0').css('background-color','#3baeab');
}

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
}*/

function layerpaper(paperid) {

    var nextEId = $('.nextE').val();

    if (nextEId!='' && nextEId!= undefined) {

        $.NotifyBox.NotifyTwoCallTwo('修改成功','修改成功,是否继续进行习题修改？','继续','查看',function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=editChoiceExercise&id="+nextEId;
        },function(){
            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
            parent.location.reload();
            parent.layer.close(index);
        })

    } else {
        $.NotifyBox.NotifyTwoCallOneGrayDle('修改成功', '修改成功,是否继续进行习题修改？', '继续', '查看', function(){
            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
            parent.location.reload();
            parent.layer.close(index);
        })
    }
}

function layerlocal() {

    var nextEId = $('.nextE').val();

    if (nextEId!='' && nextEId!= undefined) {

        $.NotifyBox.NotifyTwoCallTwo('修改成功','修改成功,是否继续进行习题修改？','继续','去列表',function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=editChoiceExercise&id="+nextEId;
        },function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering#eyJjYXQiOiIzIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==";
        })

    } else {
        $.NotifyBox.NotifyTwoCallOneGrayDle('修改成功', '修改成功,是否继续进行习题修改？', '继续', '去列表', function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
        })
    }

}



function showError(msg) {
    $.NotifyBox.NotifyPromptOne('错误信息',msg,'关闭');
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

isloading('isloading');
$(document).ready(function(){
    setTimeout("remainTime()",1000);
});
function remainTime() {
    removeLoad('isloading');
}

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
    $(this).addClass('active');
})

$(document).on('click','.Deselect',function(){
    $(this).siblings('.btn').removeClass('active')
})


$(document).on('click','.showDetails',function(){
    var Details = $(this).find('a').html();
    showDetails(Details);
})
