var checkArr = new Array();
function voice(data,parentDiv,inputType,form_selected_exercise,arrayList){
	if(data.data ==undefined){
		return false
	}
	var l = data.data.length;
	var elm ='';
	checkArr = form_selected_exercise.split(";")
	for(var i = 0;i<l;i++){
		if(arrayList != undefined){
			switch (data.data[i].category) {
				case '1':
				elm += '<div class="voiceDiv" section_attr="'+arrayList[data.data[i].id]+'" section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+'跟读-词汇'
					break;
				case '2':
				elm += '<div class="voiceDiv juzi" section_attr="'+arrayList[data.data[i].id]+'"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 跟读-课文'
					break;
				case '3':
				elm += '<div class="voiceDiv" section_attr="'+arrayList[data.data[i].id]+'"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 视频'
					break;
				case '4':
				elm += '<div class="voiceDiv" section_attr="'+arrayList[data.data[i].id]+'"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 课本'
					break;
			}
		}else{
			switch (data.data[i].category) {
				case '1':
				elm += '<div class="voiceDiv"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+'跟读-词汇'
					break;
				case '2':
				elm += '<div class="voiceDiv juzi"   section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 跟读-课文'
					break;
				case '3':
				elm += '<div class="voiceDiv"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 视频'
					break;
				case '4':
				elm += '<div class="voiceDiv"  section_id="'+data.data[i].id+'"><div class="voiveTitle">'+(i+1)+'.'+' 课本'
					break;
			}
		}
		if(data.data[i].rate != undefined){
			elm += '<span class="right">正确率'+data.data[i].rate+'<span>'
		}
		elm += '</div>';
		elm += '<div class="voiceName">'+data.data[i].name+'</div>';
		elm += '<div class="voiveFanyi"><p>'+data.data[i].translation+'</p></div>';
		elm += '<div class="voicePlay" onclick="\play(\'#voice'+i+'\')\" ><div  class="audioIcon1"></div>';
		elm += '<span onclick>播放</span><audio src="'+data.data[i].url+'" id="voice'+i+'" class="voice1"></audio></div>';
		elm += '<div class="voiveKanfy" onclick=\"fanyi(this)\"><img src="Public/img/exercise/fanyi1.png"><span>查看翻译</span></div>';
		if(data.data[i].answer != undefined){

			if(data.data[i].answer == ''){
				elm += '<div style="line-height:31px;color:#e9573f">未作答</div>';
			}else{
				elm += '<div class="voicePlay" onclick="\play(\'#voiceStudent'+i+'\')\" ><div  class="audioIcon2"></div>';
				elm += '<span onclick>播放跟读</span><audio src="'+data.data[i].answer+'" id="voiceStudent'+i+'"></audio></div>';
			}
		}

		elm += '</div>'
	}
	$(parentDiv).append(elm);

	if(inputType == 'checkbox'){
		for(var j = 0;j<l;j++){
			$(parentDiv).find('.voiveTitle').eq(j).prepend('<input type="checkbox" id="selectSingleExercises" class="selectSingleExercises" onclick=\"onClick_SelectOneExerciseInALibrary(this)\">')
		}
		var isTrue = false;
		$('#exerciseWrapper_1').find('.voiceDiv').each(function(){
			var eid = $(this).attr('section_id');
			isTrue = isInArray(eid);
			if (isTrue == true) {
				$(this).find('input').click();
			}
		})
	}
}

function isInArray(value){
    for(var jk = 0; jk < checkArr.length; jk++){
        if(value == checkArr[jk]){
            return true;
        }
    }

	return false;
}

function play(obj){
	var player = $(obj)[0];
	if($(player).attr('src') ==''){
		$.NotifyBox.NotifyPromptOne('提示','此题未提交语音作业','确定')
		return false
	}
	if (player.paused){//播放
		player.play();
		$(obj).siblings('div').addClass('active').parents('.voiceDiv').siblings().find('audio').trigger('pause').siblings('div').removeClass('active')
		player.loop = false;
		player.addEventListener('ended', function () {
			$(obj).siblings('div').removeClass('active')
		}, false);
	}else {
		player.pause();//暂停
		$(obj).siblings('div').removeClass('active')
	}
}
function fanyi(obj){
	if($(obj).siblings('.voiveFanyi').find('p').is(':hidden')){
		$(obj).find('span').html('隐藏翻译');
		$(obj).siblings('.voiveFanyi').find('p').show();

	}else{
		$(obj).find('span').html('查看翻译');
		$(obj).siblings('.voiveFanyi').find('p').hide()
	}

}
function onClick_SelectOneExerciseInALibrary(obj) {
	var ids = $('#form_exercise_chooser_selected').text();
	var idd = $('#form_exercise_chooser_selected1').text();
	var idlength = ($('#form_exercise_chooser_selected2').text()).replace(/[^0-9]/ig,"");
	var currentId = $(obj).parent().parent().attr('section_id');
	var unit = $('#chapter').children('option:selected').index();
	var section = ($('#section').children('option:selected').text()).replace(/[^0-9]/ig,"");
	var indexDiv = $(obj).parents('.voiceDiv').index()*1+1*1
	if (obj.checked) {
		if (ids.indexOf(currentId + ',') == -1) {
			ids = ids + currentId + ',';
			idd = idd + 'U'+unit+'-'+'L'+section+'-'+indexDiv+',';
			idlength = idlength*1+1*1
		}
		var checked_exercise=$(".selectSingleExercises:checked");
		var all_exercise=$(".selectSingleExercises");
		if(checked_exercise.length==all_exercise.length){
			$("#selectAllExercises").attr('checked',true);
		}
	} else {
		ids = ids.replace(currentId + ',', '');
		idd = idd.replace(('U'+unit+'-'+'L'+section+'-'+indexDiv) + ',','')
		idlength = idlength-1;
		$('#selectAllExercises').removeAttr('checked');
	}

	if (ids != '' && ids[ids.length - 1] != ',')
		ids = ids + ',' ;
		idd = idd ;
		$('#form_exercise_chooser_selected').html(ids);
		$('#form_exercise_chooser_selected1').html(idd);
		$('#form_exercise_chooser_selected2').html(idlength+'道');
		if(idlength == 0){
			$('#form_exercise_chooser_selected2').html(' ')
		}

}
function onClick_SelectAllTheExerciseInALibrary(obj) {
	if (obj.checked) {
		$('#exerciseWrapper_1 input').removeAttr('checked');
		$('#exerciseWrapper_1 input').click();

	} else {
		$('#exerciseWrapper_1 input').removeAttr('checked');
		$('#form_exercise_chooser_selected,#form_exercise_chooser_selected1,#form_exercise_chooser_selected2').html('');
	}
}
function in_array(stringToSearch, arrayToSearch) {
 for (s = 0; s < arrayToSearch.length; s++) {
  thisEntry = arrayToSearch[s].toString();
  if (thisEntry == stringToSearch) {
   return true;
  }
 }
 return false;
}

var selectedExercise = [];
var newResult = [];
var selectedChapterExercise = {};
function onClick_ConfirmTheSelectedExerciseInALibrary() {
    selectedChapterExercise[choosedExerciseLibraryChapter] = [];
	$.each(selectedExercise, function (i, n) {
		if (n.chapter_id != choosedExerciseLibraryChapter) {
			newResult.push(n);

		}
	});
	var currentSelectedIds = $('#form_exercise_chooser_selected').text().split(',');
	$.each(currentSelectedIds, function (i, n) {
		if (n == '') return true;
		else if (in_array(n,selectedChapterExercise[choosedExerciseLibraryChapter]) )
		 return true;
		selectedChapterExercise[choosedExerciseLibraryChapter].push({
			exercise_id: n,
			exercise_knowledge: $('#form_exercise_chooser_selected1').text().split(',')[i]
		});
		newResult.push({
			chapter_id: choosedExerciseLibraryChapter,
			exercise_id: n,
			exercise_knowledge: $('#form_exercise_chooser_selected1').text().split(',')[i]
		});
	});

	selectedExercise = newResult;
	renderSelectedExerciseInCreateHomework();
	$('.layui-layer-page').each(function (i, n) {
		var id = $(n).attr('id').replace('layui-layer', '');
		layer.close(id);
	});
}
var noCheckArr = new Array();
function onClick_ConfirmTheSelectedExerciseInALibrary1(){
	noCheckArr = [];
	for(var a=0;a<$('#exerciseWrapper_1').find('input:not(:checked)').length;a++){
		noCheckArr.push($('#exerciseWrapper_1').find('input:not(:checked)').eq(a).parents('.voiceDiv').attr('section_attr')+';'+$('#exerciseWrapper_1').find('input:not(:checked)').eq(a).parents('.voiceDiv').attr('section_id'))
	}
	
	for (var i=0;i<noCheckArr.length;i++) {
		var key,value;
		var arrkv = noCheckArr[i];
		var arrkvlist = arrkv.split(';');
		key = arrkvlist[0];
		value = arrkvlist[1];

		for (var j=0;j<selectedChapterExercise[key].length;j++) {
			if (typeof (selectedChapterExercise[key][j]['exercise_id']) != 'undefined') {
				if (selectedChapterExercise[key][j]['exercise_id'] == value) {
					selectedChapterExercise[key].splice(j, 1);
				}
			}
		}
	}
	renderSelectedExerciseInCreateHomework();
	$('.layui-layer-page').each(function (i, n) {
		var id = $(n).attr('id').replace('layui-layer', '');
		layer.close(id);
	});
}
function resave(){
	selectedChapterExercise = {}
	renderSelectedExerciseInCreateHomework();
}
//渲染已经选择的习题列表
function renderSelectedExerciseInCreateHomework() {
	var html = [];
	var knowledgeArray = [];
	var varLength = Object.keys(selectedChapterExercise).length;
	for(var i=0;i<varLength;i++)
	{
		$.each(selectedChapterExercise[Object.keys(selectedChapterExercise)[i]],function(ii,nn){
			html.push(nn.exercise_id);
		    knowledgeArray.push(nn.exercise_knowledge)
		});
	}
	$('#form_selected_exercise').html(html.join(';'));
	$('#form_selected_exercise1').html(knowledgeArray.join(';'));
	var el = $('#form_selected_exercise').text().split(';').length;
	if($('#form_selected_exercise').text().split(';')[0]==''){
			$('#form_selected_exercise2').html('');
			$('#previewButton,#resetButton').hide()
	}else{
			$('#form_selected_exercise2').html(el+'道');
			$('#previewButton,#resetButton').show()
	}

}
