(function() {
	//public functions
	function getQueryString(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
		var r = location.search.substr(1).match(reg);
		if (r != null)
			return unescape(decodeURI(r[2]));
		return null;
	}

	Date.prototype.Format = function (fmt) { //author: meizz
		var o = {
			"M+": this.getMonth() + 1, //月份
			"d+": this.getDate(), //日
			"h+": this.getHours(), //小时
			"m+": this.getMinutes(), //分
			"s+": this.getSeconds(), //秒
			"q+": Math.floor((this.getMonth() + 3) / 3), //季度
			"S": this.getMilliseconds() //毫秒
		};
		if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
		for (var k in o)
			if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
		return fmt;
	}

	function callPay() {
		if (typeof WeixinJSBridge == "undefined") {
			if (document.addEventListener) {
				document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			} else if (document.attachEvent) {
				document.attachEvent('WeixinJSBridgeReady', jsApiCall);
				document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			}
		} else {
			jsApiCall();
		}
	}

	$.wxFunction = {
		getOpenId: function () {
			return openId;
		},
		pay: function (orderInfo, callback) {
			//orderInfo = ajaxCreateWxOrder(price);
			//ajax create wx order
			if (!orderInfo) {
				return;
			}
			window.jsApiCall = function () {
				WeixinJSBridge.invoke(
					'getBrandWCPayRequest',
					JSON.parse(orderInfo.wxOrderInfo),
					function (res) {
						//WeixinJSBridge.log(res.err_msg);
						//alert(res.err_code+res.err_desc+res.err_msg);
						if (res.err_msg == "get_brand_wcpay_request:ok") {
							//alert("支付成功!");
							if(typeof(callback) == "function")
							callback()
						} else if (res.err_msg == "get_brand_wcpay_request:cancel") {
							//alert("取消支付!");
						} else {
							alert("支付失败!");
						}
					}
				);
			}

			callPay();
		}

	}
	var openId = 0;
	var baseUrl = '/index.php?m=Wchat&c=WxTravelActivity&a=';
	var access_code = getQueryString('code');
	if (access_code == null) {
		var fromurl = location.href;//获取授权code的回调地址，获取到code，直接返回到当前页
		var url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxa6d2714aa7728aef&redirect_uri=' + encodeURIComponent(fromurl) + '&response_type=code&scope=snsapi_base&state=0#wechat_redirect';
		location.replace(url);
	} else {
		$.ajax({
			type: 'get',
			url: baseUrl + 'authorize',
			async: false,
			cache: false,
			data: {code: access_code},
			dataType: 'json',
			success: function (result) {
				if (result != null && result.status == 200) {
					openId = JSON.parse(result.data).openid;
					if(openId == '')
					{
						location.reload();
					}	
				}
				else {
					alert('微信身份识别失败 \n ' + result);
					location.href = fromurl;
				}

			}
		});
	}
})()
		   
