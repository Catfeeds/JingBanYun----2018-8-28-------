var oHead = document.getElementsByTagName('HEAD').item(0);
var oScript= document.createElement("script");
oScript.type = "text/javascript";
oScript.src="/Public/js/DistrictQuery.js";
oHead.appendChild( oScript);

var index=0;
var timer;
var region_load_complete=false;

function init_prefect(){ 
    if(typeof($.perfectBox)=='undefined'){ 
        console.log('wait');
        timer=setInterval(init_prefect,1000); 
    }else{ 
        clearInterval(timer); 
        $.perfectBox.TeachPerfect();
        bindQueryDistrictEvent("__URL__", 'pro_select', 'city_select', 'coun_select', 'sch_select'); 
        setCourse();
        setGrade();
    }
}
init_prefect();
    
var option_ele="<option value=''></option>";
function setCourse(){
    $.ajax({
        type:"json",
        url:"/index.php?m=Home&c=Teach&a=getCourseList",
        async:false,
        dataType:'json',
        success: function(msg){
            for(var i=0;i<msg.data.length;i++){
                var element=$(option_ele).clone(true);
                $(element).val(msg.data[i].id);
                $(element).text(msg.data[i].name);
                $('#gra_select').append(element);
            }
        }
    }); 
}

function setGrade(){
    $.ajax({
        type:"json",
        url:"/index.php?m=Home&c=Teach&a=getGradeList",
        async:false,
        dataType:'json',
        success: function(msg){
            for(var i=0;i<msg.data.length;i++){
                var element=$(option_ele).clone(true);      
                $(element).val(msg.data[i].id);
                $(element).text(msg.data[i].name);
                $('#cour_select').append(element);
            }
        }
    });
}

/*
try{}catch(err){
    console.log('错误');
};*/

(function() {
	$.perfectBox = {
		//教师完善资料
		TeachPerfect : function(){
			GenerateHtml("teachPerfect");
		},
		StudentPerfect : function(){
			GenerateHtml("studentPerfect");
		}
	}
	var GenerateHtml = function (type) {
		var _html = "";
		if (type == "teachPerfect") {
			_html += '<div class="perfectBack" style="display: block;">';
			_html += '<div class="perfectBox" id="teacherBox">';
			_html += '<form class="perfectForm" id="" action="/index.php?m=Home&c=Teach&a=perfectinformation" method="post" onsubmit="return teacherCheck()">';
			_html += '<div class="perfectTop"><img src="/Public/img/register/perfectTeacher.png" alt="" class="perfectImg">完善资料，即可体验平台！</div>';
			_html += '<div class="perfectMain"><div class="mb10">';
			_html += '<div class="perfectTitle"><img src="/Public/img/register/star.png" alt="" class="starImg">如果您是教研员，请选择一所学校进行体验<div class="rightStar">*</div></div>';
			_html += '<div class="pl20 radioBox">';
			//选择身份
			_html += '<input type="radio" class="radioInput" id="teacher" name="identity" checked><label for="teacher">普通教师</label><br>';
			_html += '<input type="radio" class="radioInput" id="countyTeacher" name="identity"><label for="countyTeacher">我是该校所在区县的教研员</label><br>';
			_html += '<input type="radio" class="radioInput" id="cityTeacher" name="identity"><label for="cityTeacher">我是该校所在市的教研员</label>';
			_html += '</div></div>';
			//选择学校
			_html += '<div class="mb10"><div class="perfectTitle"><img src="/Public/img/register/star.png" alt="" class="starImg"><span class="redSpan">所在学校</span><span class="sch_notify"><img src="/Public/img/register/classNotify.png" class="notifyEm">请选择学校</span><div class="rightStar">*</div></div>';
			_html += '<div class="pl20">';
			_html += '<div id="province" class="perSchool_select">省份：<select name="" id="pro_select" class="sch_select"><option value="">-请选择省份-</option></select></div>';
			_html += '<div id="city" class="perSchool_select">市区：<select name="" id="city_select" class="sch_select"><option value="">-请选择市区-</option></select></div>';
			_html += '<div id="country" class="perSchool_select">区县：<select name="" id="coun_select" class="sch_select"><option value="">-请选择区县-</option></select></div>';
			_html += '<div id="school" class="perSchool_select">学校：<select name="" id="sch_select" class="sch_select" onchange="sch()"><option value="">-请选择学校-</option></select></div>';
			_html += '<div class="schoolAdd">如果没有您所在的学校，请先申请<a href="/index.php?m=Home&c=Index&a=schoolJoin" target="_blank" class="nava schJoin">学校加入</a></div>';
			_html += '</div></div>';
			//选择学科
			_html += '<div class="mb10"><div class="perfectTitle"><img src="/Public/img/register/star.png" alt="" class="starImg"><span class="redSpan">任教年级及学科</span><span class="cour_notify"></span><div class="rightStar">*</div></div>';
			_html += '<div class="pl20">';
			_html += '<div id="grade" class="perCourse_select"><select name="" id="gra_select" class="cour_select"><option value="">-请选择年级-</option></select></div>';
			_html += '<div id="course" class="perCourse_select"><select name="" id="cour_select" class="cour_select"><option value="">-请选择学科-</option></select></div>';
			_html += '<span class="addClassBtn" id="addClassBtn" onclick="addClass()">添加</span><div class="cour_choice"></div>';
			_html += '</div></div>';
			//_html += '<a id="teacher_sure" class="regSureBtn mt10" href="javascript:;" onclick="teacherCheck()">确定</a>';
                        _html += '<button id="teacher_sure" class="regSureBtn mt10" href="javascript:;" >确定</button>';
			_html += '</div></form></div></div>';
			
		} else if(type == "studentPerfect") {
			_html += '<div class="perfectBack" style="display: block;">';
			_html += '<div class="perfectBox" id="studentBox">';
			_html += '<form class="perfectForm" id="" action="/index.php?m=Home&c=Student&a=perfectinformation" method="post">';
			_html += '<div class="perfectTop"><img src="/Public/img/register/perfectStudent.png" alt="" class="perfectImg">完善资料，即可体验平台！</div>';
			//选择学校
			_html += '<div class="perfectMain"><div class="mb10">';
			_html += '<div class="perfectTitle"><img src="/Public/img/register/star.png" alt="" class="starImg"><span class="redSpan">所在学校</span><span class="sch_notify"><img src="/Public/img/register/classNotify.png" class="notifyEm">请选择学校</span><div class="rightStar">*</div></div>';
			_html += '<div class="pl20">';
			_html += '<div id="province" class="perSchool_select">省份：<select name="" id="pro_select" class="sch_select"><option value="">-请选择省份-</option></select></div>';
			_html += '<div id="city" class="perSchool_select">市区：<select name="" id="city_select" class="sch_select"><option value="">-请选择市区-</option></select></div>';
			_html += '<div id="country" class="perSchool_select">区县：<select name="" id="coun_select" class="sch_select"><option value="">-请选择区县-</option></select></div>';
			_html += '<div id="school" class="perSchool_select">学校：<select name="" id="sch_select" class="sch_select" onchange="sch()"><option value="">-请选择学校-</option></select></div>';
			_html += '</div></div>';
			//_html += '<a id="student_sure" class="regSureBtn mt30" href="javascript:;" onclick="studentCheck()">确定</a>';
                        _html += '<button id="student_sure" class="regSureBtn mt30" href="javascript:;" onclick="studentCheck()">确定</button>';
			_html += '</div></form></div></div>';
		}
		
		_html += '<div id="bgBack"></div>';
		//完善资料成功
		_html += '<div class="regNotify regPerfect" id="perSuccess"><div class="regTop"><img src="/Public/img/register/notify3.png" alt="" class="regTopImg" style="height: 32px"><img src="/Public/img/register/regTopClose.png" alt="" class="right regTopClose close_img" onclick="closeNotify()"></div><div class="regMiddle"><div class="perCon"><img src="/Public/img/register/regSuccess.png" alt="" class="perImg"><br><h3>完善资料成功，赶快去体验吧！</h3></div></div><div class="regBottom"><a id="perSure" class="regSureBtn" href="javascript:;" onclick="closeNotify()">确定</a></div></div>';
		
		//【弹窗】服务器繁忙
		_html += '<div class="regNotify" id="serverBusy"><div class="regTop"><img<span class="regTopTitle">提示！</span><img src="/Public/img/register/regTopClose.png" alt="" class="right regTopClose close_img" onclick="closeNotify()"></div><div class="regMiddle"><div class="serverCon"><img src="/Public/img/register/serverBusy.png" alt="" class="serverImg"><h3>服务器繁忙，请稍候再试！</h3></div></div><div class="regBottom"><a id="serverSure" class="regSureBtn" href="javascript:;" onclick="closeNotify()">确定</a></div></div>';
		
		//【弹窗】服务器繁忙
		_html += '<div class="regNotify" id="addSchoolError"><div class="regTop"><img src="/Public/img/register/notify2.png" alt="" class="regTopImg"><span class="regTopTitle">信息审核提醒</span><img src="/Public/img/register/regTopClose.png" alt="" class="right regTopClose close_img" onclick="closeNotify()"></div><div class="regMiddle"><div class="middleCon">老师您好！<div>您所选择的学校已经开通了认证，学校管理员将会审核您的身份信息。</div></div></div><div class="regBottom"><a id="addSchoolSure" class="regSureBtn" href="javascript:;" onclick="closeNotify()">确定</a></div></div>';
		
		$("body").append(_html);
	}	

})();

	//学校验证
	function sch() {
		if($('#sch_select option:selected').text() == '-请选择-' || $('#sch_select option:selected').text() == '-请选择学校-') {
			$('.sch_notify').hide()
		}
	}
		
	//教师
	function teacherCheck() {
		if(1 == $('#sch_select option:selected').attr('hasadmin')){
			$('#bgBack,#addSchoolError').show();
			return false;
		}
                if($('#sch_select option:selected').text() == '-请选择-' || $('#sch_select option:selected').text() == '-请选择学校-') {
			$('.sch_notify').show();
			return false;
		}else{
                        $('.sch_notify').hide();
                }
                if($('.cour_choice').children('span').length == 0) {
			var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '请选择任教年级及学科';
			$('.cour_notify').html(notify);
			return false;
		}else{
                    $('.cour_notify').html('');
                }
                return true;
	}
		
	//学生
	function studentCheck() {
		if ($('#sch_select option:selected').text() == '-请选择-' || $('#sch_select option:selected').text() == '-请选择学校-'){
			$('.sch_notify').show();
			return false;
		} else {
			return true;
		}
	}
		
	function closeNotify(){
		$('#bgBack').hide();
		$('.regNotify').hide()
	}
	
	//选择学科
	function addClass(){
		var grade = $.trim($('#gra_select option:selected').text());
		var course = $.trim($('#cour_select option:selected').text());
		var gradeId = $('#gra_select option:selected').val();
		var courseId = $('#cour_select option:selected').val();
		var string = course + ' ' + grade;
		if (string == 0) {
			return false;
		} else { 
			var span_all = $('.cour_choice').children('span');
			var length = span_all.length;
			for (var i = 0; i <= length; i++) {
				if (grade == '-请选择年级-' && course == '-请选择学科-') {
					var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '请选择年级及学科';
					$('.cour_notify').html(notify);
					return false;
				} else if (grade == '-请选择年级-' && course != '-请选择学科-') {
					var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '请选择年级';
					$('.cour_notify').html(notify);
					return false;
				} else if (grade != '-请选择年级-' && course == '-请选择学科-') {
					var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '请选择学科';
					$('.cour_notify').html(notify);
					return false;
				} else if (grade != '-请选择年级-' && course != '-请选择学科-' && $(span_all[i]).text().trim() == string) {
					var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '该年级及学科已经被选择，请重新选择';
					$('.cour_notify').html(notify);
					return false;
				} else {
					$('.cour_notify').html('');
				}
			}
		}

		if(length < 6) {
			var addspan = '<span class="choiceSpan" gradeid=' + gradeId + ' courseid=' + courseId + '>' + string + '<img src="/Public/img/register/classClose.png" alt="" class="choiceSpanImg" onclick="choiceSpan()"></span>'
			$('.cour_choice').append(addspan);

			var preFix = ",";
			if ("" == $('#coursegrade_id').val())
				preFix = "";
			$('#coursegrade_id').val($('#coursegrade_id').val() + preFix + courseId + ',' + gradeId);
		} else {
			var notify = '<img src="/Public/img/register/classNotify.png" class="notifyEm">' + '最多选择6个任教班级及学科';
			$('.cour_notify').html(notify);
		}
	}
	
	function choiceSpan(){
		var current_index = $(this).parent().index();
		$('.cour_choice').children('span').eq(current_index).remove();
		var oldCourseGradeStr = $('#coursegrade_id').val();
		var newCourseGradeStr = oldCourseGradeStr.substring(0, current_index * 4) + oldCourseGradeStr.substring((current_index + 1) * 4);
		if(newCourseGradeStr.substring(newCourseGradeStr.length-1) == ',')
		newCourseGradeStr = newCourseGradeStr.substring(0,newCourseGradeStr.length-1);
		$('#coursegrade_id').val(newCourseGradeStr);
	}