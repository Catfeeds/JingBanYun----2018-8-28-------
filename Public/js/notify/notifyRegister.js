(function() {
	$.NotifyBox = {
		//一个按钮的提示框
		NotifyPromptOne : function(title, msg, btnOne, timeout) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptOne", title, msg, btnOne);
			btnOk1(); //一个按钮的提示只是弹出消息，因此没必要用到回调函数callback
			btnNo();
			if(typeof(timeout)!='undefined')
			{
				setTimeout('$(".fullscr,#adminNotifyBox").remove();',timeout);
			};
			document.onkeydown=function(){
				return false;
			}
		},
		//一个按钮的提示框，有回调函数
		NotifyPromptOneC : function(title, msg, btnOne, callbackOne, timeout) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptOneC", title, msg, btnOne, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			if(typeof(timeout)!='undefined')
			{
				setTimeout('$(".fullscr,#adminNotifyBox").remove();',timeout);
			};
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，一个有回调函数，一个没有
		NotifyPromptTwoCo : function(title, msg, btnOne, btnTwo, callbackOne,timeout) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptTwoCo", title, msg, btnOne, btnTwo, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			if(typeof(timeout)!='undefined')
			{
				setTimeout('$(".fullscr,#adminNotifyBox").remove();',timeout);
			};
			document.onkeydown=function(){
				return false;
			}
		},
		//两个按钮的提示框，两个都有回调函数
		NotifyPromptTwoCt : function(title, msg, btnOne, btnTwo, callbackOne, callbackTwo) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptTwoCt", title, msg, btnOne, btnTwo, callbackOne, callbackTwo);
			btnOk1(callbackOne);
			btnOk2(callbackTwo);
			btnNo();
			document.onkeydown=function(){
				return false;
			}
		},
		//注册提示框
		NotifyRegisterOne : function(title, msg, btnOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyRegisterOne", title, msg, btnOne);
			btnOk1(); //一个按钮的提示只是弹出消息，因此没必要用到回调函数callback
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
		if(type != "notifyRegisterOne") {
			_html += '<div class="fullscr" style="display: block;">';
			_html += '<div class="adminNotifyBox" id="adminNotifyBox" style="display: block;">'
			_html += '<div class="adminNotifyTitle">';
			_html += '<div class="adTitle">'+ title + '</div>';
			_html += '<img src="/Public/img/icon/adTitleImg1.png" class="adTitleImg"></div>'
			_html += '<div class="adminNotifyContent">'
			_html += '<p class="adminNotifyMsg">' + msg + '</p>';
			_html += '<p class="adminNotifyButton">';

			if (type == "notifyPromptOne" || type == "notifyPromptOneC") {
				_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyColor1" id="btn_ok1">' + btnOne + '</a>'
			} else if(type == "notifyPromptTwoCo") {
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok1">' + btnOne + '</a>'
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_no">' + btnTwo + '</a>'
			} else if(type == "notifyPromptTwoCt") {
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok1">' + btnOne + '</a>'
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok2">' + btnTwo + '</a>'
			};

			_html += '</p></div></div></div>';
		} else {
			_html += '<div class="fullscr" style="display: block;">';
			_html += '<div class="adminNotifyBox" id="adminNotifyBox" style="display: block;">';
			_html += '<div class="regTop">';
			_html += '<img src="/Public/img/register/notify2.png" alt="" class="regTopImg">';
			_html += '<span class="regTopTitle">' + title + '</span>';
			_html += '<img src="/Public/img/register/regTopClose.png" alt="" class="right regTopClose adTitleImg"></div>';
			_html += '<div class="regMiddle"><div class="notifyCon">';
			_html += '<p>' + msg + '</p></div></div>';
			_html += '<div class="regBottom"><a id="btn_ok1" class="regSureBtn" href="javascript:;" onclick="$(this).hide();">' + btnOne + '</a></div></div></div>';
		}
		

		$("body").append(_html);
		
		if($.trim($('.adminNotifyBtn').html()).length >= 7) {
			$('.adminNotifyBtn').css({
				'width': 'auto',
				'padding': '0 5px 0 7px'
			})
		} else {
			$('.adminNotifyBtn').css({
				'width': '150px',
				'padding': '0 0 0 2px'
			})
		};
		
		if($.trim($('.adminNotifyBtn2').html()).length >= 5) {
			$('.adminNotifyBtn2').css({
				'width': '150px'
			})
		} else {
			$('.adminNotifyBtn2').css({
				'width': '100px'
			})
		};
		
		if($.trim($('.adminNotifyMsg').html()).length >= 55 && $.trim($('.adminNotifyMsg').html()).length < 70) {
			$('.adminNotifyMsg').css('padding','40px 20px 0')
		} else if($.trim($('.adminNotifyMsg').html()).length >= 70) {
			$('.adminNotifyMsg').css('padding','30px 20px 0')
		} else {
			$('.adminNotifyMsg').css('padding','60px 20px 0')
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
		$("#btn_no,.adTitleImg").click(function () {
			isNoitfy = 0;
		    $(".fullscr,#adminNotifyBox").remove();
			document.onkeydown=function(){
				return true;
			}
		});
	}

})()