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
			};
			$('body').css('overflow-y','hidden')
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
			};
			$('body').css('overflow-y','hidden')
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
			};
			$('body').css('overflow-y','hidden')
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
			};
			$('body').css('overflow-y','hidden')
		},
		//注册提示框
		NotifyRegisterOne : function(title, msg, btnOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyRegisterOne", title, msg, btnOne);
			btnOk1(); //一个按钮的提示只是弹出消息，因此没必要用到回调函数callback
			btnNo();
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		},
		//一个按钮的注册提示框，有回调函数
		NotifyRegisterOneC : function(title, msg, btnOne, callbackOne) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyRegisterOneC", title, msg, btnOne, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		},
		//注册提示框，两个都有回调函数
		NotifyRegisterTwoCt : function(title, msg, btnOne, btnTwo, callbackOne, callbackTwo) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyRegisterTwoCt", title, msg, btnOne, btnTwo, callbackOne, callbackTwo);
			btnOk1(callbackOne);
			btnOk2(callbackTwo);
			btnNo();
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		},
		//关闭按钮有函数
		NotifyPromptOneClose : function(title, msg, btnOne, callbackOne, callbackThree, timeout) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptOneC", title, msg, btnOne, callbackOne, callbackThree);
			btnOk1(callbackOne);
			btnClose(callbackThree);
			if(typeof(timeout)!='undefined')
			{
				setTimeout('$(".fullscr,#adminNotifyBox").remove();',timeout);
			};
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		},
		//没有关闭按钮的弹窗，
		NotifyPromptTwoCtNc : function(title, msg, btnOne, btnTwo, callbackOne, callbackTwo) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptTwoCtNc", title, msg, btnOne, btnTwo, callbackOne, callbackTwo);
			btnOk1(callbackOne);
			btnOk2(callbackTwo);
			btnNo();
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		},//一个按钮的提示框，有回调函数, 没有关闭按钮
		NotifyPromptOneCNc : function(title, msg, btnOne, callbackOne, timeout) {
			if(1 == isNoitfy) return;
			GenerateHtml("notifyPromptOneCNc", title, msg, btnOne, callbackOne);
			btnOk1(callbackOne);
			btnNo();
			if(typeof(timeout)!='undefined')
			{
				setTimeout('$(".fullscr,#adminNotifyBox").remove();',timeout);
			};
			document.onkeydown=function(){
				return false;
			};
			$('body').css('overflow-y','hidden')
		}
	}
	var isNoitfy = 0;
	var GenerateHtml = function (type, title, msg, btnOne, btnTwo ) {
		isNoitfy = 1;
		var _html = "";
		if(type != "notifyRegisterOne" && type != "notifyRegisterOneC" && type != "notifyRegisterTwoCt") {
			_html += '<div class="fullscr" style="display: block;">';
			_html += '<div class="adminNotifyBox" id="adminNotifyBox" style="display: block;">'
			_html += '<div class="adminNotifyTitle">';
			_html += '<div class="adTitle">'+ title + '</div>';
			if (type != "notifyPromptTwoCtNc" && type != "notifyPromptOneCNc") {
				_html += '<img src="/Public/img/icon/adTitleImg1.png" class="adTitleImg">'
			}
			_html += '</div>';
			_html += '<div class="adminNotifyContent">';
			_html += '<p class="adminNotifyMsg">' + msg + '</p>';
			_html += '<p class="adminNotifyButton">';

			if (type == "notifyPromptOne" || type == "notifyPromptOneC" || type == "notifyPromptOneCNc") {
				_html += '<a href="javascript:;" class="adminNotifyBtn adminNotifyColor1" id="btn_ok1">' + btnOne + '</a>'
			} else if(type == "notifyPromptTwoCo") {
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok1">' + btnOne + '</a>'
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_no">' + btnTwo + '</a>'
			} else if(type == "notifyPromptTwoCt" || type == "notifyPromptTwoCtNc") {
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok1">' + btnOne + '</a>'
				_html += '<a href="javascript:;" class="adminNotifyBtn2 adminNotifyColor2" id="btn_ok2">' + btnTwo + '</a>'
			};

			_html += '</p></div></div></div>';
		} else {
			_html += '<div class="fullscr" style="display: block;">';
			_html += '<div class="adminNotifyBox registerBox" id="adminNotifyBox" style="display: block;">';
			_html += '<div class="regTop">';
			_html += '<img src="/Public/img/register/notify2.png" alt="" class="regTopImg">';
			_html += '<span class="regTopTitle">' + title + '</span>';
			_html += '<img src="/Public/img/register/regTopClose.png" alt="" class="right regTopClose adTitleImg"></div>';
			_html += '<div class="regMiddle"><div class="notifyCon">';
			_html += '<p>' + msg + '</p></div></div>';
			_html += '<div class="regBottom">';
			if(type == "notifyRegisterOne"){
				_html += '<a id="btn_ok1" class="regSureBtn" href="javascript:;" onclick="$(this).hide();">' + btnOne + '</a>';
			} else if(type == "notifyRegisterOneC"){
				_html += '<a id="btn_ok1" class="regSureBtn" href="javascript:;">' + btnOne + '</a>';
			} else if(type == "notifyRegisterTwoCt") {
				_html += '<a href="javascript:;" class="regSureBtn2" id="btn_ok1">' + btnOne + '</a>'
				_html += '<a href="javascript:;" class="regSureBtn2" id="btn_ok2">' + btnTwo + '</a>'
			};
			_html += '</div></div></div>';
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
		
		if($.trim($('#adminNotifyBox .adminNotifyMsg').html()).length >= 55 && $.trim($('#adminNotifyBox .adminNotifyMsg').html()).length < 70) {
			$('#adminNotifyBox .adminNotifyMsg').css('padding','40px 20px 0')
		} else if($.trim($('#adminNotifyBox .adminNotifyMsg').html()).length >= 70) {
			$('#adminNotifyBox .adminNotifyMsg').css('padding','30px 20px 0')
		} else {
			$('#adminNotifyBox .adminNotifyMsg').css('padding','60px 20px 0')
		}
	}	

	//一个确定按钮事件
	var btnOk1 = function (callbackOne) {
		$("#btn_ok1").click(function () {
			isNoitfy = 0;
	   		$(".fullscr,#adminNotifyBox").remove();
	   		$('body').css('overflow-y','auto');
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
		    $('body').css('overflow-y','auto');
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
		    $('body').css('overflow-y','auto');
			document.onkeydown=function(){
				return true;
			}
		});
	}
	
	//关闭按钮事件
	var btnClose = function (callbackThree) {
		$(".adTitleImg").click(function () {
			isNoitfy = 0;
		    $(".fullscr,#adminNotifyBox").remove();
		    $('body').css('overflow-y','auto');
			document.onkeydown=function(){
				return true;
			}
		    if (typeof (callbackThree) == 'function') {
				callbackThree();
		    }
		});
	}

})()
