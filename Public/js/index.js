function adjustLayout() {
	var positionXRate = $('#navImg').width() / 1264;
	var positionYRate = $('#navImg').height() / 710;

//	var position1X = parseInt(370 * positionXRate) + 5;
//	var position1Y = parseInt(197 * positionXRate) + 5;
//	var position1Width = parseInt(285 * positionXRate) - 12;
//	var position1Height = parseInt(315 * positionXRate) - 12;
	
	var position1X = parseInt(365 * positionXRate) + 5;
	var position1Y = parseInt(150 * positionXRate) + 5;
	var position1Width = parseInt(290 * positionXRate) - 12;
	var position1Height = parseInt(290 * positionXRate) - 12;

	$('#jiaoxuejia').css({
		'top': position1Y + 'px',
		'left': position1X + 'px',
		'width': position1Width + 'px',
		'height': position1Height + 'px'
	});

	var position2X = parseInt(670 * positionXRate) + 5;
	var position2Y = parseInt(148 * positionXRate) + 5;
	var position2Width = parseInt(227 * positionXRate) - 12;
	var position2Height = parseInt(142 * positionXRate) - 12;

	$('#liyunquan').css({
		'top': position2Y + 'px',
		'left': position2X + 'px',
		'width': position2Width + 'px',
		'height': position2Height + 'px'
	});

	var position3X = parseInt(670 * positionXRate) + 5;
	var position3Y = parseInt(302 * positionXRate) + 5;
	var position3Width = parseInt(227 * positionXRate) - 12;
	var position3Height = parseInt(140 * positionXRate) - 9;

	$('#banjixing').css({
		'top': position3Y + 'px',
		'left': position3X + 'px',
		'width': position3Width + 'px',
		'height': position3Height + 'px'
	});
}

function adjustLayout_liyunquan() {
	var positionXRate = $('#navImg').width() / 1336;
	var positionYRate = $('#navImg').height() / 750;

	var position1X = parseInt(47 * positionXRate);
	var position1Y = parseInt(318 * positionXRate);

	var position2X = parseInt(242 * positionXRate);
	var position2Y = parseInt(318 * positionXRate);

	var position3X = parseInt(47 * positionXRate);
	var position3Y = parseInt(517 * positionXRate);

	var position4X = parseInt(242 * positionXRate);
	var position4Y = parseInt(517 * positionXRate);

	var positionWidth = parseInt(180 * positionXRate);
	var positionHeight = parseInt(180 * positionXRate);

	$('#f_jingbangailan').css({
		'top': position1Y + 'px',
		'left': position1X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_jingbanhuodong').css({
		'top': position2Y + 'px',
		'left': position2X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_jiaoshifengcai').css({
		'top': position3Y + 'px',
		'left': position3X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_zhuanjiazixun').css({
		'top': position4Y + 'px',
		'left': position4X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
}

function adjustLayout_jiaoxuejia() {
	var positionXRate = $('#navImg').width() / 1336;

	var position1X = parseInt(913 * positionXRate);
	var position1Y = parseInt(316 * positionXRate);

	var position2X = parseInt(1112 * positionXRate);
	var position2Y = parseInt(316 * positionXRate);

	var position3X = parseInt(715 * positionXRate);
	var position3Y = parseInt(517 * positionXRate);

	var position4X = parseInt(913 * positionXRate);
	var position4Y = parseInt(517 * positionXRate);

	var position5X = parseInt(1112 * positionXRate);
	var position5Y = parseInt(517 * positionXRate);

	var positionWidth = parseInt(180 * positionXRate);
	var positionHeight = parseInt(180 * positionXRate);

	$('#f_jingbanziyuan').css({
		'top': position1Y + 'px',
		'left': position1X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_beikexitong').css({
		'top': position2Y + 'px',
		'left': position2X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_dianzikeben').css({
		'top': position3Y + 'px',
		'left': position3X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_jiaoshiziyuan').css({
		'top': position4Y + 'px',
		'left': position4X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_bianjiaozhitongche').css({
		'top': position5Y + 'px',
		'left': position5X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
}

function adjustLayout_banjixing() {
	var positionXRate = $('#navImg').width() / 1336;

	var position1X = parseInt(42 * positionXRate);
	var position1Y = parseInt(310 * positionXRate);

	var position2X = parseInt(252 * positionXRate);
	var position2Y = parseInt(310 * positionXRate);

	var position3X = parseInt(474 * positionXRate);
	var position3Y = parseInt(310 * positionXRate);

	var position4X = parseInt(42 * positionXRate);
	var position4Y = parseInt(529 * positionXRate);

	var position5X = parseInt(252 * positionXRate);
	var position5Y = parseInt(529 * positionXRate);

	var position6X = parseInt(474 * positionXRate);
	var position6Y = parseInt(529 * positionXRate);

	var positionWidth = parseInt(180 * positionXRate);
	var positionHeight = parseInt(180 * positionXRate);

	$('#f_shuziketang').css({
		'top': position1Y + 'px',
		'left': position1X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_zuoyexitong').css({
		'top': position2Y + 'px',
		'left': position2X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_banjixinxiguanli').css({
		'top': position3Y + 'px',
		'left': position3X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_xuexiguiji').css({
		'top': position4Y + 'px',
		'left': position4X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
	$('#f_xiaoheiban').css({
		'top': position5Y + 'px',
		'left': position5X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});

	$('#f_jiazhangduxue').css({
		'top': position6Y + 'px',
		'left': position6X + 'px',
		'width': positionWidth + 'px',
		'height': positionHeight + 'px'
	});
}
$(function () {
	adjustLayout();
	adjustLayout_liyunquan();
	adjustLayout_jiaoxuejia();
	adjustLayout_banjixing();
	$(window).resize(function () {
		adjustLayout();
		adjustLayout_liyunquan();
		adjustLayout_jiaoxuejia();
		adjustLayout_banjixing();
	});
});
