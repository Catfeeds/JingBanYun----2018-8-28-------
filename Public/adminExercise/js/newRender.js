//选择
function preview(obj){
	$('.exerciseContent').children('li').hide()
	if(answerWidth == '1'){
		$('.answer1').click()
	}else if (answerWidth == '2') {
		$('.answer2').click()
	}else {
		$('.answer4').click()
	}
	if($(obj).is('.preview')){
		$('.answer1,.answer2,.answer4').show()
	}else {
		$('.answer1,.answer2,.answer4').hide()
	}
	layer.open({
		type: 1,
		shade: 0,
		zIndex: 20160922,
		content: $('.exercise_choice'),
		area: ['100%', '100%'],
		closeBtn: 1,
		move: false,
		scrollbar: false,
		title: ['习题预览', 'color：#fff']
	});
	$('.solution').html('')
	$('.score').text('('+$('.inputCommon').val()+'分)');
	var title = ueMessage.getContent();
	$('.caption').html(title);
	var exerciseNum = $('.exerciseNums').children('option:selected').val();
	var ueName = [messageA,messageB,messageC,messageD,messageE,messageF,messageG,messageH];
	for(var i=0;i<exerciseNum;i++){
		let word = $('.editors').eq(i).children('.optionSpan').text();
		$('.exerciseContent').children().eq(i).show().html('<span class="choiceAnswer">'+word+'</span>'+ueName[i].getContent())
	}
	for(var i=0;i<exerciseNum;i++){
		var answerName = $('.editors').eq(i).find('.active').parents('.editors').children('.optionSpan').text()
		$('.solution').append('<span>'+answerName+'</span>')
	}
	var analysis = messageJx.getContent();
	$('.analysisSpan').html(analysis)
}
$('.answer1').click(function(){
	answerWidth = 1;
	$('.exerciseContent').find('li').removeAttr('class')
})
$('.answer2').click(function(){
	answerWidth = 2;
	$('.exerciseContent').find('li').removeAttr('class').addClass('w50')
})
$('.answer4').click(function(){
	var exerciseNum = $('.exerciseNums').children('option:selected').val();
	answerWidth = exerciseNum;
	if(exerciseNum ==2){
		$('.exerciseContent').find('li').removeAttr('class').addClass('w50');
	}else if (exerciseNum ==3) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w33')
	}else if (exerciseNum ==4) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w25')
	}else if (exerciseNum ==5) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w20')
	}else if (exerciseNum ==6) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w16')
	}else if (exerciseNum ==7) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w14')
	}else if (exerciseNum ==8) {
		$('.exerciseContent').find('li').removeAttr('class').addClass('w12')
	}else {
		$('.exerciseContent').find('li').removeAttr('class')
	}
})


//文字填空
function previewCompletion(){
	$('.remarkAnswer').html('');
	layer.open({
		type: 1,
		shade: 0,
		zIndex: 20160922,
		content: $('.layerOutter'),
		area: ['100%', '100%'],
		closeBtn: 1,
		move: false,
		scrollbar: false,
		title: ['习题预览', 'color：#fff']
	});

	var claimcaption = completionA.getContent();//填空题题干
	var remarkParse = messageJx.getContent();//填空题解析
	$('.remarkParse').html(remarkParse);
	var score = 0;
	var input = '<input type="text" class="answerInput" disabled>';//这是题目上的答题框
	var newClaimcaption = claimcaption.replace(/ㄖ/g,input);//替换特殊字符
	$('.claimcaption').html(newClaimcaption);

    for(var i = 0; i < $('.comAnswerBox').length; i++){
    	var j = i+1;
    	score+= $('.fraction'+i).val()*1;//总分
    	var div_ = '<div class="answerBox">【第'+j+'空】'+messageArray[i].getContent()+'</div>';
		$('.remarkAnswer').append(div_)
    }

    $('.claimScore').html('('+score+'分)');//这道题的总分

}
// previewCompletion()

//选择填空
function previewCompletionChoice(obj){
	$('.claimChoice,.remarkAnswer').html('');
	for(var w=0;w<$('.claimChoice').length;w++){
		if(w!='0'){
			$('.claimChoice').eq(w).remove()
		}
	}
	layer.open({
		type: 1,
		shade: 0,
		zIndex: 20160922,
		content: $('.layerOutter'),
		area: ['100%', '100%'],
		closeBtn: 1,
		move: false,
		scrollbar: false,
		title: ['习题预览', 'color：#fff']
	});

	var exerciseNum = $('.exerciseNums').children('option:selected').val();//共有几个选项
	//如果选项数量超过8个，则把“一行显示”按钮隐藏
	if(exerciseNum > 8) {
		$('.answer4').hide()
	}
	for(var a=0;a<$('.comAnswerBox').length;a++){
		if(a>0){
			$('.claimChoice').eq(a).remove()
		}
	}
	//这是全部选项的输入编辑框
	for(var i=0;i<exerciseNum;i++){
		let word = $('.editors').eq(i).children('.optionSpan').text();
		var li_ = '<li><span class="choiceAnswer">'+word+'</span>'+AnswerMessageArray[i].getContent()+'</li>';
		$('.claimChoice').append(li_)
	}
	for(var t=0;t<$('.comAnswerBox').length-1;t++){
		$('.exerciseMain').append($('.claimChoice').prop("outerHTML"))

	}
	for(var b=0;b<$('.claimChoice').length;b++){
		$('.claimChoice').eq(b).prepend('<p>第'+(b+1)+'空</p>')
	}

	//"预览"显示三个样式按钮，"整体预览"不显示三个样式按钮
	if($(obj).is('.preview')) {
		$('.answer1, .answer2, .answer4').show()
	} else {
		$('.answer1, .answer2, .answer4').hide()
	}

	if(answerWidth == '1'){
		$('.answer1').click()
	}else if (answerWidth == '2') {
		$('.answer2').click()
	}else {
		$('.answer4').click()
	}


	var claimcaption = completionA.getContent();//填空题题干
	var remarkParse = messageJx.getContent();//填空题解析
	$('.remarkParse').html(remarkParse);
	var score = 0;
	var input = '<input type="text" class="answerInput" disabled>';//这是题目上的答题框
	var newClaimcaption = claimcaption.replace(/ㄖ/g,input);//替换特殊字符
	$('.claimcaption').html(newClaimcaption);

    for(var i = 0; i < $('.comAnswerBox').length; i++){
    	var j = i+1;
    	score+= $('.fraction'+i).val()*1;//总分
    	var div_ = '<div class="answerBox">【第'+j+'空】'+messageArray[i].getContent()+'</div>';
		$('.remarkAnswer').append(div_)
    }

    $('.claimScore').html('('+score+'分)');//这道题的总分

}
// previewCompletionChoice()

//连线
//作图
//问答
function morePreview(){
	$('.layerHtmlContent').html('');
	$('.oneTitle').html(firstQuestionInfo.getContent())
	layer.open({
		type: 1,
		shade: 0,
		zIndex: 20160922,
		content: $('.layerHtml'),
		area: ['100%', '100%'],
		closeBtn: 1,
		move: false,
		scrollbar: false,
		title: ['习题预览', 'color：#fff']
	});
	var iframeContentlength = $('.iframeContent').length
	for (var i = 0; i < iframeContentlength; i++) {
		 var  numVal = $('.iframeContent').eq(i).find('.topicNum').val()
		 $('.layerHtmlContent').append($('#ExerciseId'+i)[0].contentWindow.exerciseHtml());
		$('.numval').eq(i).html(numVal+'.')
	}
	for(var j=0;j<answerArr.length;j++){
		if(answerArr[j] == 1){
			$('.exerciseTitle').eq(j).find('.answer1').bind('click').click()
		}else if(answerArr[j] == 2){
			$('.exerciseTitle').eq(j).find('.answer2').bind('click').click()
		}else if(answerArr[j] == 0){

		}else {
			$('.exerciseTitle').eq(j).find('.answer4').bind('click').click()
		}

	}
}
