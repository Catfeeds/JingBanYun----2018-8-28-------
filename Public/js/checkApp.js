function CheckIsAndroid() {
	var browser = {
			versions: function () {
				var u = navigator.userAgent,
					app = navigator.appVersion;
				return { //移动终端浏览器版本信息
					ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
					android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
					iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
					iPad: u.indexOf('iPad') > -1, //是否iPad
				};
			}(),
		}
	if (browser.versions.android)
		return true;
	return false;
}


function CheckIsIOS() {
	var browser = {
		versions: function () {
			var u = navigator.userAgent,
				app = navigator.appVersion;
			return { //移动终端浏览器版本信息
				ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
				android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
				iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
				iPad: u.indexOf('iPad') > -1, //是否iPad
			};
		}(),
	}
	if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
		return true;
	}
}
function postApp(obj){
	if (CheckIsIOS()){
		window.webkit.messageHandlers.iosModel.postMessage(obj)

	}
	if(CheckIsAndroid()){
		AppFunction.postMessage(JSON.stringify(obj))

	}
}
