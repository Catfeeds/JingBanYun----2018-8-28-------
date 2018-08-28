function share_weixin(appId,timestamp,nonceStr,signature,title,content,linkimgUrl,res_id,types,url) {

	var id = res_id;
	var type = types;

	wx.config({
		debug: false,
		appId: appId,
		timestamp: timestamp,
		nonceStr: nonceStr,
		signature: signature,
		jsApiList: [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo',
			'onMenuShareQZone'
		]
	});
    if(url == undefined)
    	url = '';
	wx.ready(function () {

		wx.checkJsApi({
			jsApiList: [
				'getNetworkType',
				'previewImage',
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'onMenuShareQZone'
			],
		});
		var shareData = {

			title: title,

			desc: content,

			link: url,

			imgUrl: linkimgUrl,

			trigger: function (res) {
				//alert('用户点击发送给朋友');
			},
			success: function () {

				//alert('http://test.jingbanyun.com/index.php?m=Monitor&c=Qrcode&a=setShare');
				var host = window.location.host;

				$.ajax({
					url:'http://'+host+'/index.php?m=Monitor&c=Qrcode&a=setShare',
					type:'POST', //GET
					async:true,
					cache: false,
					data:{
						'id':id,
						'type':type
					},
					dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
					success:function(data,textStatus,jqXHR){
						alert('发送成功');
					}
				})

	
				//alert('分享确定');
			},

			cancel: function () {

				//alert('取消分享');
			}

		};
		wx.onMenuShareAppMessage(shareData);
		wx.onMenuShareTimeline(shareData);
		wx.onMenuShareQQ(shareData);
		wx.onMenuShareWeibo(shareData);

	});

	wx.error(function(res){
		//alert(res);
	});
}

