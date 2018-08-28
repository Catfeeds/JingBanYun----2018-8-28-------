(function() {
	$.NotifyBox = {
                //未登录的操作
                NotifyNotLogin : function(){ 
                    alert('登录信息已过期,请重新登录!');
                    location.href="http://baidu.com"; 
                },

		//一个按钮的提示框

		NotifyOne : function(title, msg, btnOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyOne", title, msg, btnOne);
			btnOk1(); //一个按钮的提示只是弹出消息，因此没必要用到回调函数callback
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		NotifyPromptOne: function(title, msg, btnOne)
		{
			this.NotifyOne(title, msg, btnOne);
		},
		//一个按钮的提示框，有回调函数
		NotifyOneCall : function(title, msg, btnOne, callbackOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyOneCall", title, msg, btnOne, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有,一个灰色按钮，一个蓝色
		NotifyTwoCallOneGray : function(title, msg, btnOne, btnTwo, callbackOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyTwoCallOneGray", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有，两个蓝色按钮
		NotifyTwoCallOneBlue : function(title, msg, btnOne, btnTwo, callbackOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyTwoCallOneBlue", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，两个都有回调函数
		NotifyTwoCallTwo : function(title, msg, btnOne, btnTwo, callbackOne, callbackTwo) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyTwoCallTwo", title, msg, btnOne, btnTwo, callbackOne, callbackTwo);
			btnOk1(callbackOne);
			btnOk2(callbackTwo);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		}
	}
	var isNoitfy = 0;
	var GenerateHtml = function (type, title, msg, btnOne, btnTwo ) {
		isNoitfy = 1;
		var _html = "";
		_html += '<div class="fullscr" style="display: block;">';
		_html += '<div class="adminNotifyBox" id="adminNotifyBox" style="display: block;">'
		_html += '<div class="adminNotifyTitle">' + title + '</div>';
		_html += '<div class="adminNotifyContent">'
		_html += '<p class="adminNotifyMsg">' + msg + '</p>';
		_html += '<p class="adminNotifyButton">';

		if (type == "notifyOne" || type == "notifyOneCall") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue1" id="btn_ok1">' + btnOne + '</a>'
		} else if(type == "notifyTwoCallOneGray") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyGray2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_no">' + btnTwo + '</a>'
		} else if(type == "notifyTwoCallOneBlue") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_no">' + btnTwo + '</a>'
		} else if(type == "notifyTwoCallTwo") {
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok1">' + btnOne + '</a>'
			_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyBlue2" id="btn_ok2">' + btnTwo + '</a>'
		};

		_html += '</p></div></div></div>';

		$("body").append(_html);
		
		if($.trim($('.adminNotifyMsg').html()).length <= 24) {
			$('.adminNotifyMsg').css({
				'padding':'60px 20px 0',
				'text-align':'center'
			})
		} else if($.trim($('.adminNotifyMsg').html()).length >=25 && $.trim($('.adminNotifyMsg').html()).length < 54) {
			$('.adminNotifyMsg').css({
				'padding':'50px 20px 0',
				'text-align':'left'
			})
		} else if($.trim($('.adminNotifyMsg').html()).length >= 55 && $.trim($('.adminNotifyMsg').html()).length < 70) {
			$('.adminNotifyMsg').css({
				'padding':'40px 20px 0',
				'text-align':'left'
			})
		} else {
			$('.adminNotifyMsg').css({
				'padding':'20px 20px 0',
				'text-align':'left'
			})
		}
	}	

	//一个确定按钮事件
	var btnOk1 = function (callbackOne) {
		$("#btn_ok1").click(function () {
			isNoitfy = 0;
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
			isNoitfy = 0;
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
			isNoitfy = 0;
		    $(".fullscr,#adminNotifyBox").remove();
			document.onkeydown=function(){
				return true;
			}
		});
	}

})()
