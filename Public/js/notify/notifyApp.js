(function() {
	$.NotifyBox = {
		//未登录的操作
		NotifyNotLogin : function(){ 
			alert('登录信息已过期,请重新登录!');
			location.href="http://baidu.com"; 
		},
		//一个按钮的提示框
		NotifyPromptOne : function(title, msg, btnOne) {
			if(1 == isNotify) return;
			GenerateHtml("notifyPromptOne", title, msg, btnOne);
			btnOk1(); //一个按钮的提示只是弹出消息，因此没必要用到回调函数callback
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//一个按钮的提示框，有回调函数
		NotifyPromptOneC : function(title, msg, btnOne, callbackOne) {
			if(1 == isNotify) return;
			GenerateHtml("notifyPromptOneC", title, msg, btnOne, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有,一个灰色按钮，一个蓝色
		NotifyTwoCallOneGray : function(title, msg, btnOne, btnTwo, callbackOne) {
			if(1 == isNotify) return;
			GenerateHtml("notifyTwoCallOneGray", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有,一个灰色按钮，一个蓝色，左灰右蓝
		NotifyTwoCallLeftGray : function(title, msg, btnOne, btnTwo, callbackOne) {
			if(1 == isNotify) return;
			GenerateHtml("notifyTwoCallLeftGray", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有，两个蓝色按钮
		NotifyPromptTwoCo : function(title, msg, btnOne, btnTwo, callbackOne) {
			if(1 == isNotify) return;
			GenerateHtml("notifyPromptTwoCo", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，两个都有回调函数
		NotifyPromptTwoCt : function(title, msg, btnOne, btnTwo, callbackOne, callbackTwo) {
			if(1 == isNotify) return;
			GenerateHtml("notifyPromptTwoCt", title, msg, btnOne, btnTwo, callbackOne, callbackTwo);
			btnOk1(callbackOne);
			btnOk2(callbackTwo);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		}
	}
	var isNotify = 0;
	var GenerateHtml = function (type, title, msg, btnOne, btnTwo ) {
		isNotify = 1;
		var _html = "";
		_html += '<div class="fullscr" style="display: block;">';
		_html += '<div class="adminNotifyBox" id="adminNotifyBox" style="display: block;">'
		_html += '<div class="adminNotifyTitle">' + title + '</div>';
		_html += '<div class="adminNotifyContent">'
		_html += '<div class="adminNotifyMsgBox"><p class="adminNotifyMsg">' + msg + '</p></div>';
		_html += '<p class="adminNotifyButton">';

		if (type == "notifyPromptOne" || type == "notifyPromptOneC") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue1" id="btn_ok1">' + btnOne + '</a>'
		} else if(type == "notifyTwoCallOneGray") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyGray2" id="btn_no">' + btnTwo + '</a>'
		} else if(type == "notifyTwoCallLeftGray") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyGray2" id="btn_no">' + btnTwo + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
		} else if(type == "notifyTwoCallOneBlue" || type == "notifyPromptTwoCo") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_no">' + btnTwo + '</a>'
		} else if(type=="notifyPromptTwoCt") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok2">' + btnTwo + '</a>'
		};

		_html += '</p></div></div></div>';

		$("body").append(_html);
	}	

	//一个确定按钮事件
	var btnOk1 = function (callbackOne) {
		$("#btn_ok1").click(function () {
			isNotify = 0;
	   		$(".fullscr,#adminNotifyBox").remove();
			document.onkeydown=function(){
				return true;
			}
			if (typeof (callbackOne) == 'function') {
			callbackOne();
			};
		});
	}

	//两个确定按钮事件
	var btnOk2 = function (callbackTwo) {
		$("#btn_ok2").click(function () {
			isNotify = 0;
		    $(".fullscr,#adminNotifyBox").remove();
			document.onkeydown=function(){
				return true;
			}
		    if (typeof (callbackTwo) == 'function') {
				callbackTwo();
		    }
		});
	}

	//取消按钮事件
	var btnNo = function () {
		$("#btn_no").click(function () {
			isNotify = 0;
		    $(".fullscr,#adminNotifyBox").remove();
			document.onkeydown=function(){
				return true;
			}
		});
	}

})()
