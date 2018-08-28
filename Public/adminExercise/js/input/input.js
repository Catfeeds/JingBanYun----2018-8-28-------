var vm = new Vue({
    el: '#app',
    data: {
        message:'',//题目
        messageJx:'',
        messageA:'',//message图文编辑器
        messageB:'',//message图文编辑器
        messageC:'',//message图文编辑器
        messageD:'',//message图文编辑器
        messageE:'',//message图文编辑器
        messageF:'',//message图文编辑器
        messageG:'',//message图文编辑器
        messageH:'',//message图文编辑器
        section:false, //显示错误
        subject:false,
        answerA:false,
        answerB:false,
        answerC:false,
        answerD:false,
        answerE:false,
        answerF:false,
        answerG:false,
        answerH:false,
        Fraction:false,
        subjecterror:false,
        Problempresentation:false,
        messageJxshow:false,
        isAnswer:false,

        Fractiondata:'',//分数
        studyId:'', //待提交学段数据
        course:{},
        courseId:'',//待提交科目数据
        exercisesId:'', //待提交类型
        exerciseNums:4, //选中答案的数量
        trueAnswer:'', //选中哪个选项为正确答案
        presentation:{},
        type:'',
        presentation_type:'',
        isflag:1,
        showMessage:['A','B','C','D','E','F','G','H'],
        arrayMessage:[],
        answerWidth:0,

    },
    created: function () { //应用初始化完毕加载

        this.type = $('.question_type').val();
        this.$http.post('index.php?m=Exercise&c=CreateExercise&a=getCourse',{},{emulateJSON:'application/x-www-form-urlencoded'}).then(function(response){ //发送http请求
            this.course=eval(response.data);
        }, function(response){
            // 响应错误回调
            console.log('初始化学科错误');
        });

    },
    methods: {
        inputExercises: function() { //录入习题
            this.initdata();//初始化
            this.check();
            if (this.isflag==1) {
                for (var i=0;i<this.exerciseNums;i++) {
                    var testVar;
                    eval('testVar=message'+this.showMessage[i]);
                    this.arrayMessage[i] = testVar.getContent();
                }

                var big_paper_id = $('.big_paper_id').val();
                var paper_id = $('.paper_id').val();
                this.trueAnswer = this.trueAnswer.replace(',', '');

                var token = $('.repeat_commit').val();

                preview('.textSpan');
                var json_html = $(".exerciseTemplate").prop("outerHTML");
                layer.closeAll()
                requestdata={studyId:this.studyId,courseId:this.courseId,type:this.type,message:ueMessage.getContent(),Problempresentation:this.presentation_type,exerciseNums:this.exerciseNums,trueAnswer:this.trueAnswer,Fractiondata:this.Fractiondata,messageJx:messageJx.getContent(),arrayMessage:this.arrayMessage,big_paper_id:big_paper_id,paper_id:paper_id,token:token,answerWidth:this.answerWidth,html:json_html};

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
            }
        },
        study: function(id) { //学段赋值
            this.studyId = id;
        },
        courseClick: function (id) {
            this.courseId = id;
            $('.selectSize').find('option').eq(0).attr("selected",true);
            this.$http.post('index.php?m=Exercise&c=CreateExercise&a=getCourseChild',{id:this.courseId},{emulateJSON:'application/x-www-form-urlencoded'}).then(function(response){ //发送http请求
                this.presentation=eval(response.data);
            }, function(response){
                // 响应错误回调
                console.log('获取展示类型失败');
            });

        },

        setAnswer: function (Answer) {
            if (this.trueAnswer.indexOf(Answer)<0) {
                this.trueAnswer = this.trueAnswer+','+Answer;
                $('.setAnswerinfo').val(this.trueAnswer);
            }
        },

        cancelAnswer: function (Answer,cancel) {
            this.trueAnswer = this.trueAnswer.replace(','+Answer,'');
            $('.setAnswerinfo').val(this.trueAnswer);
        },
        check: function () {
            this.message = ueMessage.getContent();
            this.messageJx = messageJx.getContent();
            this.answerWidth = answerWidth;
            var fuheti = $('.fuheti').val();
            if (fuheti != 1) {
                if (this.message==''){
                    this.subjecterror = true;
                    this.isflag = 2;
                }
            }


            if (this.studyId=='') {
                this.section = true;
                this.isflag = 2;
            }
            if (this.courseId=='') {
                this.subject = true;
                this.isflag = 2;
            }
            if (this.presentation_type=='') {
                this.Problempresentation = true;
                this.isflag = 2;
            }
            if(this.trueAnswer =='') {
                this.isAnswer = true;
                this.isflag = 2;
            }
            //稳
            for (var i=0;i<this.exerciseNums;i++) {
                var testVar;
                eval('testVar=message'+this.showMessage[i]);
                if (testVar.getContent()=='') {
                    eval("this.answer"+this.showMessage[i]+'=true');
                    this.isflag = 2;
                }
            }

            if (this.Fractiondata=='') {
                this.Fraction = true;
                this.isflag = 2;
            }

            if (this.Fractiondata =='' || this.Fractiondata<=0 ) {
                this.Fraction = true;
                this.isflag = 2;
            }


            if (this.messageJx=='') {
                this.messageJxshow = true;
                this.isflag = 2;
            }

        },

        initdata: function () {
            this.isflag = 1;
            this.isAnswer=false;
            this.section=false; //显示错误
            this.subject=false;
            this.answerA=false;
            this.answerB=false;
            this.answerC=false;
            this.answerD=false;
            this.answerE=false;
            this.answerF=false;
            this.answerG=false;
            this.answerH=false;
            this.Fraction=false;
            this.subjecterror=false;
            this.Problempresentation=false;
            this.messageJxshow=false;

        },

    }
})

/*function layerlocal() {
    layer.confirm('添加成功,是否继续进行添加习题？', {
        btn: ['继续','去列表'], //按钮
        closeBtn: 0,
    }, function(){
        window.location.reload();//刷新当前页面.
    }, function(){
        window.location.href="index.php?m=Exercise&c=CreateExercise&a=exerciseEntering&cat=1";
    });
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


function optionShow(optionClass){
    var optionVal = $(optionClass).children('option:selected').val();
    $('.editors').hide();
    for(var i=0;i<optionVal;i++){
        $('.editors').eq(i).show()
    }
}

function showDetails(msg) {
    if (msg!=''&& msg!=undefined)
    $.NotifyBox.NotifyPromptOne('详细信息',msg,'关闭');
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

$(document).on('click','.subjectLi>.color333',function(){
    $(this).addClass('color3baeab').siblings().removeClass('color3baeab')
})

$(document).on('click','.setAnswer',function(){
    $(this).addClass('active');
})

$(document).on('click','.Deselect',function(){
    $(this).siblings('.btn').removeClass('active')
})
