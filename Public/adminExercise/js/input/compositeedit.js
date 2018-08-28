function courseClick( id ) {
    $.post('index.php?m=Exercise&c=CreateExercise&a=getCourseChild', {id: id}, function (res) {
        if(res != ''){
            var options = [];
            options.push( '<span class="color3baeabFixed">题型</span>' );
            var tplone = '<span class="color333 exercisesTypeid" exercisesTypeid="{0}">{1}</span>';
            $.each(res, function (index, item) {
                options.push( tplone.format(item.id, item.name) );
            });
            $('.exercisesType').find('span').remove();
            for (var i=0;i<options.length;i++){
                $('.exercisesType').append(options[i]);
            }
            $('.exercisesType').append('<span class="red classred " style="display:none">*该项为必选项</span>');
        }

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
    var exercisesTypeid;
    var firstmessage;
    var datalist={};
    var id = $('.pid').val();
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
    $('.exercisesTypeid').each(function(){
        if ($(this).hasClass('color3baeab')) {
            exercisesTypeid =  $(this).attr('exercisesTypeid');

        }
    })
    var _send_paperid = $('._send_paperid').val();
    var token = $('.repeat_commit').val();

    firstmessage = firstQuestionInfo.getContent();
    datalist['id'] = id;
    datalist['studyId'] = studyIddata;
    datalist['courseId'] = courseIddata;
    datalist['exercisesId'] = exercisesTypeid;
    datalist['firstmessage'] = firstmessage;
    datalist['_send_paperid'] = _send_paperid;
    datalist['token'] = token;
    datalist['list']={};
    //开始循环遍历iframe 里面的数据尼玛
    $('.iframeContent').each(function(index){
        var type = $(this).find('iframe').attr('type');//type判断习题的类型
        var iframeid = $(this).find('iframe').attr('id');//iframeid为了获取里面的数据
        var topicNum = $(this).find('.choiceSelect').find('input').val();
        type = parseInt(type);
        //datalist[type] = getWenziTiankong(iframeid); //调试模式
        switch (type){
            case 1: //选择题
                datalist['list'][index] = getXuanzhe(iframeid,type,topicNum);
                break;
            case 2://文字填空题
                datalist['list'][index] = getWenziTiankong(iframeid,type,topicNum);
                break;
            case 3: //选择填空题
                datalist['list'][index] = getXuanzheTianKong(iframeid,type,topicNum);
                break;
            case 4: //连线题
                datalist['list'][index] = getLianXian(iframeid,type,topicNum);
                break;
            case 5://作图题
                datalist['list'][index] = getZuoTu(iframeid,type,topicNum);
                break;
            case 6://解答题
                datalist['list'][index] = getJieDa(iframeid,type,topicNum);
                break;
            default:
                datalist['list'][index] = getXuanzhe(iframeid,type,topicNum);
                break;

        }

        datalist['list'][index]['answerWidth'] = answerArr[index];
    });

    console.log(answerArr);
    console.log(datalist);
    datalist['answerArr'] = answerArr;
    morePreview()
    datalist['html'] = $('.exerciseTemplate').prop('outerHTML');
    layer.closeAll();
    var requestdata={exerciseList:datalist};

    $.ajax({
        type: "POST",   //访问WebService使用Post方式请求
        url: 'index.php?m=Exercise&c=CreateExercise&a=editCompositeExercise', //调用WebService的地址和方法名称组合---WsURL/方法名
        data:requestdata,
        beforeSend:function () {
            loading('createAnswerQuestions');
        },
        success: function(res){
            if (res.code ==200) {
                removeLoad('createAnswerQuestions');
                if (_send_paperid>0) {
                    layerpaper();
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

//获取解答题
function getJieDa(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var data={};
    var fraction='';

    var completionA = $('#'+iframeid)[0].contentWindow.completionA;
    var completionAContent = completionA.getContent();

    arrayMessage = $('#'+iframeid)[0].contentWindow.Answermessage1.getContent();
    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();

    data['arrayMessage'] = arrayMessage;
    data['message'] = completionAContent;
    data['fenshu'] = $('#'+iframeid)[0].contentWindow.$('.count_score').val();
    data['messageJx'] = messageJx;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}

//获取作图题
function getZuoTu(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var data={};
    var fraction='';

    var completionA = $('#'+iframeid)[0].contentWindow.completionA;
    var completionAContent = completionA.getContent();

    arrayMessage = $('#'+iframeid)[0].contentWindow.Answermessage1.getContent();
    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();

    data['arrayMessage'] = arrayMessage;
    data['message'] = completionAContent;
    data['fenshu'] = $('#'+iframeid)[0].contentWindow.$('.count_score').val();
    data['messageJx'] = messageJx;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}

//获取连线
function getLianXian(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var AnswerMessage='';
    var data={};
    var fraction='';

    var completionA = $('#'+iframeid)[0].contentWindow.completionA;
    var completionAContent = completionA.getContent();

    var exerciseNums = $('#'+iframeid)[0].contentWindow.$('.answerNumSel').val();

    for (var j=0;j<exerciseNums;j++) {
        var inputval = $('#'+iframeid)[0].contentWindow.$('.mathAnswer').find('div').eq(j+1).find('.AnswerInfo').val();
        var fractionj = $('#'+iframeid)[0].contentWindow.$('.mathAnswer').find('div').eq(j+1).find('.fraction').val();
        if (fractionj!='' && fractionj!=undefined) {
            fraction += fractionj+',';
        }
        arrayMessage.push(inputval);
    }
    AnswerMessage = $('#'+iframeid)[0].contentWindow.tableUE.getContent();
    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();

    data['arrayMessage'] = arrayMessage;
    data['AnswerMessage'] = AnswerMessage;
    data['exerciseNums'] = exerciseNums;
    data['message'] = completionAContent;
    data['fenshu'] = fraction;
    data['messageJx'] = messageJx;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}


//获取选择填空题
function getXuanzheTianKong(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var AnswerMessage= new Array();
    var data={};
    var fraction = new Array();

    var completionA = $('#'+iframeid)[0].contentWindow.completionA;
    var completionAContent = completionA.getContent();
    var exerciseNums = $('#'+iframeid)[0].contentWindow.$('.exerciseNums').val();

    for (var i=0;i<exerciseNums;i++) {
        var testVar = $('#'+iframeid)[0].contentWindow.AnswerMessageArray[i];
        AnswerMessage[i]=testVar.getContent();//答案内容
    }

    if ($('#'+iframeid)[0].contentWindow.clickbefore==0){
        $('#'+iframeid)[0].contentWindow.clickbefore = $('#'+iframeid)[0].contentWindow.numCount;
    }

    for (var j=0;j<$('#'+iframeid)[0].contentWindow.clickbefore;j++) {
        var messageVar = $('#'+iframeid)[0].contentWindow.messageArray[j];
        arrayMessage[j]=messageVar.getContent();//答案内容
        fraction[j] = $('#'+iframeid)[0].contentWindow.$('.fraction'+j).val();//答案的分数
    }

    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();
    data['arrayMessage'] = arrayMessage;
    data['AnswerMessage'] = AnswerMessage;
    data['exerciseNums'] = exerciseNums;
    data['message'] = completionAContent;
    data['fenshu'] = fraction;
    data['messageJx'] = messageJx;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}

//获取文字填空
function getWenziTiankong(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var data={};
    var fraction = new Array();

    var completionA = $('#'+iframeid)[0].contentWindow.completionA;
    var completionAContent = completionA.getContent();


    if ($('#'+iframeid)[0].contentWindow.clickbefore==0){
        $('#'+iframeid)[0].contentWindow.clickbefore = $('#'+iframeid)[0].contentWindow.numCount;
    }


    for (var i=0;i<$('#'+iframeid)[0].contentWindow.clickbefore;i++) {
        var testVar = $('#'+iframeid)[0].contentWindow.messageArray[i];
        arrayMessage[i]=testVar.getContent();//答案内容
        fraction[i] = $('#'+iframeid)[0].contentWindow.$('.fraction'+i).val();//答案的分数
    }

    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();

    data['arrayMessage'] = arrayMessage;
    data['exerciseNums'] = $('#'+iframeid)[0].contentWindow.clickbefore;
    data['message'] = completionAContent;
    data['fenshu'] = fraction;
    data['messageJx'] = messageJx;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}


//获取选择题
function getXuanzhe(iframeid,type,topicNum) {
    var arrayMessage= new Array();
    var showMessage=['A','B','C','D','E','F','G','H'];
    var data={};

    var ueMessage = $('#'+iframeid)[0].contentWindow.ueMessage;
    var message = ueMessage.getContent();
    var exerciseNums = $('#'+iframeid)[0].contentWindow.$('.exerciseNums').val();
    for (var i=0;i<exerciseNums;i++) {
        var testVar;
        eval("testVar=$('#'+iframeid)[0].contentWindow.message"+showMessage[i]);
        arrayMessage[i] = testVar.getContent();
    }
    var fenshu = $('#'+iframeid)[0].contentWindow.$('.inputCommon').val();
    var messageJx = $('#'+iframeid)[0].contentWindow.messageJx;
    messageJx = messageJx.getContent();
    var setAnswerinfo = '';

    $('#'+iframeid)[0].contentWindow.$('.setAnswer').each(function(){
        if ($(this).hasClass('active')) {
            setAnswerinfo += ',' + $(this).attr('answer_id');
        }
    })
    setAnswerinfo = setAnswerinfo.replace(',', '');


    data['arrayMessage'] = arrayMessage;
    data['exerciseNums'] = exerciseNums;
    data['message'] = message;
    data['fenshu'] = fenshu;
    data['messageJx'] = messageJx;
    data['right_key'] = setAnswerinfo;
    data['type'] = type;
    data['topicNum'] = topicNum;

    return data;
}


/*function layerpaper(paperid) {
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
    layer.confirm('修改成功,是否继续进行修改习题？', {
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
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
        })

    } else {
        $.NotifyBox.NotifyTwoCallOneGrayDle('修改成功', '修改成功,是否继续进行习题修改？', '继续', '去列表', function(){
            window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
        })
    }
}

function showDetails(msg) {
    if (msg!=''&& msg!=undefined)
    $.NotifyBox.NotifyPromptOne('详细信息',msg,'关闭');
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
    setTimeout("remainTime()",2000);
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

$(document).on('change','.quesType',function(){
    var type = $(this).val();
    $(this).parent().next('iframe').attr('src','index.php?m=Exercise&c=CreateExercise&a=compositecreateChoiceExercise&type='+type);
    $(this).parent().next('iframe').attr('type',type);
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
