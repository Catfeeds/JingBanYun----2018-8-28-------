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
        }
        else {
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
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(decodeURI(r[2])); return null;
}

String.prototype.replaceAll = function (reallyDo, replaceWith, ignoreCase) {
    if (!RegExp.prototype.isPrototypeOf(reallyDo)) {
        return this.replace(new RegExp(reallyDo, (ignoreCase ? "gi" : "g")), replaceWith);
    } else {
        return this.replace(reallyDo, replaceWith);
    }
}

function getEle(id) {
    return document.getElementById(id);
}

function guid() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function getTextbooks() {

    var courseId = $('#course_id').val();
    var gradeId = $('#grade_id').val();
    if (courseId == '' || gradeId == '') {
        $('#textbook_id').html('<option value="">-请先选择学科和年级-</option>');
        return;
    }
    $.get('index.php?m=Home&c=DictRest&a=get_textbooks_by_courseAndGrade', {
        'course_id': courseId,
        'grade_id': gradeId
    }, function (res) {
        var options = [];
        options.push('<option value="">-请选择-</option>');
        $.each(res, function (index, item) {
            options.push('<option value="{0}">{1}</option>'.format(item.id, item.name));
        });
        if (options.length > 0) {
            $('#textbook_id').html(options.join(''));
        } else {
            $('#textbook_id').html('<option value="">-请选择-</option>');
        }

    })
}

function getTextbooksByCourseAndGrade(courseId, gradeId, textbookEle, callback) {
    if (courseId == '' || gradeId == '') {
        $(textbookEle).html('<option value="">-请先选择学科和年级-</option>');
    }
    $.get('index.php?m=Home&c=DictRest&a=get_textbooks_by_courseAndGrade', {
        'course_id': courseId,
        'grade_id': gradeId
    }, function (res) {
        var options = [];
        options.push('<option value="">-请选择-</option>');
        $.each(res, function (index, item) {
            options.push('<option value="{0}">{1}</option>'.format(item.id, item.name));
        });
        if (options.length > 0) {
            $(textbookEle).html(options.join(''));
        } else {
            $(textbookEle).html('<option value="">-请选择-</option>');
        }
        if (typeof callback == 'function') {
            callback();
        }
    })
}

function img_teacher(obj) {
    var sex = $(obj).attr('sex');
    if (sex=='男'){
        obj.src = './Public/img/classManage/teacher_m.png';
    }else{
        obj.src = './Public/img/classManage/teacher_w.png';
    }

}

function img_stu(obj) {
    var sex = $(obj).attr('sex');
    if (sex=='男'){
        obj.src = './Public/img/classManage/student_m.png';
    }else{
        obj.src = './Public/img/classManage/student_w.png';
    }

}

$(function () {
    var hash = window.location.hash;
    if ("undefined" == typeof store)return;
    if (store.get('fullscreen') == 'true') {
        fullScreen();
    }
    $('body').show();
});

function myBrowser(){
    var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
    var isOpera = userAgent.indexOf("Opera") > -1;
    if (isOpera) {
        return "Opera"
    }; //判断是否Opera浏览器
    if (userAgent.indexOf("Firefox") > -1) {
        return "FF";
    } //判断是否Firefox浏览器
    if (userAgent.indexOf("Chrome") > -1){
        return "Chrome";
    }
    if (userAgent.indexOf("Safari") > -1) {
        return "Safari";
    } //判断是否Safari浏览器
    if ((userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) || (userAgent.indexOf("rv:11.0") > -1)) {
        if(userAgent.indexOf("MSIE 6.0")>0){
            return "IE6";
        }
        if(userAgent.indexOf("MSIE 7.0")>0){
            return "IE7";
        }
        if(userAgent.indexOf("MSIE 8.0")>0 || (userAgent.indexOf("MSIE 9.0")>0 && !window.innerWidth)){
            return "IE8";
        }
        if(userAgent.indexOf("MSIE 9.0")>0){
            return "IE9";
        }
        if(userAgent.indexOf("MSIE 10.0")>0){
            return "IE10";
        }
        return "IE";
    }; //判断是否IE浏览器
    return '';
}
