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
	if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
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
}

var stopFunction;
(function(){
	var msgTemplate = '<li><a msgid="{0}" class="unReadMessage msg{0}" href="javascript:void(0);">{1}</a></li>'
	$.post('/index.php/Admin/Message/getUnreadAdminMessages',  function (res) {
		res = eval('(' + res + ')');
		var unReadCount = 0;
		$(res.data).each(function(i,n){
			if(typeof(n.hasRead) == 'undefined') {
				$('.adminMessage').append(msgTemplate.format(i, n.title));
				unReadCount ++;
			}
		});
		if(unReadCount == 0)
          $('.adminName').removeClass('dropdown-toggle');

  		if($(".adminMessage").children('li').length > 1) {
        	// stopFunction=$("#scrollDiv").Slider({
	        //     line: 1,
	        //     speed: 500,
	        //     timer: 3000
	        // });
        }
        
	});
})();
$('.unReadMessage').live("click",function() {
	var msgId = $(this).attr('msgid');
	$.post('/index.php/Admin/Message/displayUnreadAdminMessage', {id: msgId}, function (res) {
		res = eval('(' + res + ')');
		// alert(res.data);
		$.NotifyBox.NotifyOneCall('提示',res.data,'确定',function(){
			$('.msg'+msgId).parent('li').remove();
			if($(".adminMessage").children('li').length <= 1) {
				stopFunction();
			}
		})
	});
	
})

