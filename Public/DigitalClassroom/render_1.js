var isShowCorrectAnswerAfterQuestion = false;
var global_I=0;
var is_show_goback=1;
//单项文字选择题
function renderDanXiangWenZiXuanZe_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var options = $(question.body);

    $(options).each(function (i, n) {
        n = $(n).html();
        var isAnswer = n.substring(0, 1) == '#';
        if (!isAnswer && n.indexOf('<span') == 0) {
            var judge = $(n).text();
            isAnswer = judge == '#';
        }
        var selected = '', answerShow = '', answerMark = '', points = '';
        if (isAnswer) {
            n = n.substring(1);
            selected = isShowCorrectAnswer ? 'checked' : '';
            //debugger
            answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
            answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
            points = isShowCorrectAnswerAfterQuestion ? 'points=' + question.points : '';
        }
        d.push('<div class="exerciseQuestionWordOption {4}"><label><input {6} {7} data-answer="{2}" questionid="{8}" type="radio" id="q_{0}_{3}" name="q_{0}" value="{3}" {2}>{1}</label>{5}</div>'.format(question.id, n, selected, i + 1, selected, answerShow, answerMark, points, question.question_id));
    });

    return d.join('');
}

//多项文字选择题
function renderDuoXiangWenZiXuanZe(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var options = $(question.body);
    $(options).each(function (i, n) {
        n = $(n).html();
        var isAnswer = n.substring(0, 1) == '#';
        if (!isAnswer && n.indexOf('<span') == 0) {
            var judge = $(n).text();
            isAnswer = judge == '#';
        }
        var selected = '', answerShow = '', answerMark = '', points = '';
        if (isAnswer) {
            n = n.substring(1);
            selected = isShowCorrectAnswer ? 'checked' : '';
            answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
            answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
            points = isShowCorrectAnswerAfterQuestion ? 'points=' + question.points : '';
        }
        d.push('<div class="exerciseQuestionWordOption {4}"><label><input {6} {7} data-answer="{2}" questionid="{8}" type="checkbox" value="{3}" id="q_{0}_{3}" name="q_{0}" {2}>{1}</label>{5}</div>'.format(question.id, n, selected, i + 1, selected, answerShow, answerMark, points, question.question_id));
    });

    return d.join('');
}

//判断题
function renderPanDuan_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var options = $(question.body);
    $(options).each(function (i, n) {
        n = $(n).html();
        var isAnswer = n.substring(0, 1) == '#';
        if (!isAnswer && n.indexOf('<span') == 0) {
            var judge = $(n).text();
            isAnswer = judge == '#';
        }
        var selected = '', answerShow = '', answerMark = '', points = '';
        if (isAnswer) {
            n = n.substring(1);
            selected = isShowCorrectAnswer ? 'checked' : '';
            answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
            answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
            points = isShowCorrectAnswerAfterQuestion ? 'points=' + question.points : '';
        }
        d.push('<div class="exerciseQuestionWordOption {4}"><label><input {6} {7} data-answer="{2}" questionid="{8}" type="radio" value="{3}" id="q_{0}_{3}" name="q_{0}" {2}>{1}</label>{5}</div>'.format(question.id, n, selected, i + 1, selected, answerShow, answerMark, points, question.question_id));
    });
    return d.join('');
}

//问答题
function renderWenDa_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));
    d.push('<div class="textAreaInput"><textarea type="textarea" rows="5" placeholder="请输入答案" name="q_{0}" id="q_{0}" questionid="{0}"></textarea></div>'.format(question.id));
    return d.join('');
}

//填空题
function renderTianKong_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));
    //在平面直角坐标系中，已知点A（2，4），B（4，2），C（1，1），点P在x轴上，且四边形ABOP的面积是△ABC的面积的2倍，则点P的坐标为[答案：(5，2)]．
    var items = question.body.split('[#答案');
    var innerD = [];
    var gapsIndex = 1;
    $(items).each(function (i, n) {
	var temp_answer='';         
        if (n.indexOf('#]') >= 0) {     
                if(n.indexOf('：')== -1){
                    temp_answer=n.substring(n.indexOf(':')+1, n.indexOf('#]'));  
                }else{
                    temp_answer=n.substring(n.indexOf('：')+1, n.indexOf('#]'));  
                }
                         
                if(temp_answer.indexOf('(')>=0){  
                    temp_answer=temp_answer.substring(temp_answer.indexOf('(')+1,temp_answer.length-1);         
                }else{  
                    //temp_answer=n.substring(0,n.substring(n.indexOf('#')));          console.log(temp_answer);  
                    //temp_answer=n.substring(0,n.substring(n.indexOf('：')));    
                    //temp_answer=temp_answer.substring(1); 
                } 
            temp_answer=temp_answer.replace(/\s/g, "");     //console.log(temp_answer); return false; 
                        
            temp_answer=sha1(temp_answer);
            innerD.push('<input type="text" questionid="{0}" id="q_{0}_{1}" name="q_{0}_{1}" data_answer="{2}"/>'.format(question.id, gapsIndex,temp_answer));
            gapsIndex = gapsIndex + 1;
            n = n.substring(n.indexOf('#]') + 2);
        }
        innerD.push(n);
    });

    d.push('<div class="tianKongInput">{0}</div>'.format(innerD.join('')));
    return d.join('');
}

//判断题[含多个小题]
function renderPanDuanMultiple_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var body = $(question.body);
    var subQuestions = $(body);

    $(subQuestions).each(function (j, m) {
        //渲染题目
        d.push('<div class="subquestionTitle">' + $(m).find('.subquestionTitle').html() + '</div>');
        var options = $(m).find('.exercise_option');
        $(options).each(function (i, n) {
            n = $(n).html();
            var isAnswer = n.substring(0, 1) == '#';
            if (!isAnswer && n.indexOf('<span') == 0) {
                var judge = $(n).text();
                isAnswer = judge == '#';
            }
            var selected = '', answerShow = '', answerMark = '', points = '';
            if (isAnswer) {
                n = n.substring(1);
                selected = isShowCorrectAnswer ? 'checked' : '';
                answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
                answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
                points = isShowCorrectAnswerAfterQuestion ? 'points=' + parseInt(question.points / subQuestions.length) : '';
            }
            d.push('<div class="exerciseQuestionWordOption {4}"><label><input {7} {9} data-answer="{2}" type="radio" questionid="{8}" id="q_{0}_{5}_{3}" name="q_{0}_{5}" value="{5}_{3}" {2}>{1}</label>{6}</div>'.format(question.id, n, selected, i + 1, selected, j, answerShow, answerMark, question.question_id, points));
        });
    });


    return d.join('');
}

//单项选择题[含多个小题]
function renderDanXiangWenZiXuanZeMultiple_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var body = $(question.body);
    var subQuestions = $(body);
    $(subQuestions).each(function (j, m) {
        //渲染题目
        d.push('<div class="subquestionTitle">' + $(m).find('.subquestionTitle').html() + '</div>');
        var options = $(m).find('.exercise_option');
        $(options).each(function (i, n) {
            n = $(n).html();
            var isAnswer = n.substring(0, 1) == '#';
            if (!isAnswer && n.indexOf('<span') == 0) {
                var judge = $(n).text();
                isAnswer = judge == '#';
            }
            var selected = '', answerShow = '', answerMark = '', points = '';
            if (isAnswer) {
                n = n.substring(1);
                selected = isShowCorrectAnswer ? 'checked' : '';
                answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
                answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
                points = isShowCorrectAnswerAfterQuestion ? 'points=' + parseInt(question.points / subQuestions.length) : '';
            }
            d.push('<div class="exerciseQuestionWordOption {4}"><label><input {7} {9} data-answer="{2}" type="radio" questionid="{8}" id="q_{0}_{5}_{3}" name="q_{0}_{5}" value="{5}_{3}" {2}>{1}</label>{6}</div>'.format(question.id, n, selected, i + 1, selected, j, answerShow, answerMark, question.question_id, points));
        });
    });


    return d.join('');
}
//多项选择题[含多个小题]
function renderDuoXiangWenZiXuanZeMultiple(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var body = $(question.body);
    var subQuestions = $(body);
    $(subQuestions).each(function (j, m) {
        //渲染题目
        d.push('<div class="subquestionTitle">' + $(m).find('.subquestionTitle').html() + '</div>');
        var options = $(m).find('.exercise_option');
        $(options).each(function (i, n) {
            n = $(n).html();
            var isAnswer = n.substring(0, 1) == '#';
            if (!isAnswer && n.indexOf('<span') == 0) {
                var judge = $(n).text();
                isAnswer = judge == '#';
            }
            var selected = '', answerShow = '', answerMark = '', points = '';
            if (isAnswer) {
                n = n.substring(1);
                selected = isShowCorrectAnswer ? 'checked' : '';
                answerShow = isShowCorrectAnswerAfterQuestion ? '<span style="color:green;">[正确答案]</span>' : '';
                answerMark = isShowCorrectAnswerAfterQuestion ? ' isanswer="true" ' : '';
            }
            points = 'points=' + parseInt(question.points / subQuestions.length);
            d.push('<div class="exerciseQuestionWordOption {4}"><label><input {7} {9} data-answer="{2}" questionid="{8}" type="checkbox" id="q_{0}_{5}_{3}" name="q_{0}_{5}" value="{5}_{3}" {2}>{1}</label>{6}</div>'.format(question.id, n, selected, i + 1, selected, j, answerShow, answerMark, question.question_id, points));
        });
    });


    return d.join('');
}
//问答题[含多个小题]
function renderWenDaMultiple_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var body = $(question.body);
    var subQuestions = $(body);
    $(subQuestions).each(function (j, m) {
        //渲染题目
        d.push('<div class="subquestionTitle">' + $(m).find('.subquestionTitle').html() + '</div>');
        d.push('<div class="textAreaInput"><textarea type="textarea" rows="5" placeholder="请输入答案" name="q_{0}_{1}" questionid="{0}" id="q_{0}_{1}"></textarea></div>'.format(question.id, j));
    });


    return d.join('');
}
//填空题[含多个小题]
function renderTianKongMultiple_1(question) {
    var d = [];
    d.push(getQuestionTitleHtml_1(question));

    var body = $(question.body);
    var subQuestions = $(body);
    $(subQuestions).each(function (j, m) {
        //渲染题目
        d.push('<div class="subquestionTitle">' + $(m).find('.subquestionTitle').html() + '</div>');

        //在平面直角坐标系中，已知点A（2，4），B（4，2），C（1，1），点P在x轴上，且四边形ABOP的面积是△ABC的面积的2倍，则点P的坐标为[答案：(5，2)]．\
        var inputContent = $(m).find('.subquestionAnswer').html();
        if (inputContent == null) return true;
        var items = inputContent.split('[#答案');
        var innerD = [];
        var gapsIndex = 1;
        $(items).each(function (i, n) {
	    var data_answer_str='';
            if (n.indexOf('#]') >= 0) {   
                if(items[i]!=''){  
                    //data_answer_str=items[i];   
                    data_answer_str=items[i].substring(0,n.indexOf('#]'));
                    data_answer_str=data_answer_str.substr(1);
                    data_answer_str=data_answer_str.replace(/\s/g, ""); 
                      
                    data_answer_str=sha1(data_answer_str);
                    
                }
                innerD.push('<input type="text" id="q_{0}_{2}_{1}" name="q_{0}_{2}_{1}" questionid="{0}" data_answer="{3}"/>'.format(question.id, gapsIndex, j,data_answer_str));//question.id
                gapsIndex = gapsIndex + 1;
                n = n.substring(n.indexOf('#]') + 2);
            }
            innerD.push(n);
        });
        d.push('<div class="tianKongInput">{0}</div>'.format(innerD.join('')));
    });


    return d.join('');
}

//连线题
function renderMatch_1(question) {       
    var d = [];
    d.push(getQuestionTitleHtml_1(question));                     
    var tr_options = $(question.body).find('.tr_option');       
    d.push('<div class="matchQuestion" id="matchQuestion_{0}">'.format(question.id));
    d.push('<div class="matchQuestionWrapper clearfix">');//连线题的wrapper元素

    d.push('<div class="matchQuestionLeftWrapper">');//左边选项wrapper      

    $.each(tr_options, function (i, n) {
        var leftEleHtml = $.trim($(n).find('.td_option_left').text());
        if (leftEleHtml != '' || $(n).find('.td_option_left img').length > 0) {
            var leftOptionId = $.trim($(n).find('.td_option_left_no').text());
            var leftOptionAnswer = $.trim($(n).find('.td_option_answer').text());
            d.push('<div class="matchQuestionShowItem" data-id="{1}" data-answer="{2}">{0}</div>'.format($(n).find('.td_option_left').html(), leftOptionId, leftOptionAnswer));
        }
    });


    d.push('</div>');////左边选项wrapper-结束


    ///////////////////////////////

    d.push('<div class="matchQuestionRightWrapper">');//右边选项wrapper


    $.each(tr_options, function (i, n) {
        var rightEleHtml = $.trim($(n).find('.td_option_right').text());
        if (rightEleHtml != ''|| $(n).find('.td_option_right img').length > 0) {
            var rightOptionId = $.trim($(n).find('.td_option_right_no').text());
            d.push('<div class="matchQuestionShowItem" data-id="{1}">{0}</div>'.format($(n).find('.td_option_right').html(), rightOptionId));
        }
    });


    d.push('</div>');////右边选项wrapper-结束
    
    ///////////////////////

    d.push('<canvas class="match_canvas"></canvas>');//连线画布
    d.push('<canvas class="match_backcanvas"></canvas>');//提示线画布

    //工具
    if(is_show_goback==1){
        d.push('<div class="match_tools"><div class="goBackBtn">回退</div><div class="resetCanvasBtn">重连</div></div>');
    }
 
    d.push('</div>');//连线题的wrapper元素-结束
    d.push('</div>');
    return d.join('');
}

function getQuestionTitleHtml_1(question) {
    var d = []; global_I+=1
    d.push('<div id="qes_{1}" class="exerciseQuestion" data-id="{1}" data-answer="" data-points="{0}" data-originalid="{2}">'.format(question.points, global_I,question.id));
    d.push('{0}. ({1}分) - '.format(global_I, question.points));
    d.push(question.questions);

    if (question.mp3_vid != '' && question.mp3_vid != null) {
        d.push('<div class="mp3_player" id="mp3_q{0}" data-vid="{1}"></div>'.format(question.question_id, question.mp3_vid));
    }

    d.push('</div>');
    return d.join('');
}



//BEGIN
function createExerciseLibraryChapter(homework_id, exercises1, callback) {       
    $.getJSON(getHomeworkInfoUrl, {id: homework_id}, function (res) {
        exercises = res;
        var html = [];
        html.push('<div class="questionWrapper">');
        $(exercises).each(function (i, n) {
            switch (n.template_name) {
                case '单项文字选择题':
                    html.push(renderDanXiangWenZiXuanZe_1(n));
                    break;
                case '多项文字选择题':
                    html.push(renderDanXiangWenZiXuanZe_1(n));
                    break;
                case '判断题':
                    html.push(renderPanDuan_1(n));
                    break;
                case '问答题':
                    html.push(renderWenDa_1(n));
                    break;
                case '填空题':
                    html.push(renderTianKong_1(n));
                    break;
                case '判断题[含多个小题]':
                    html.push(renderPanDuanMultiple_1(n));
                    break;
                case '单项选择题[含多个小题]':
                    html.push(renderDanXiangWenZiXuanZeMultiple_1(n));
                    break;
                case '多项选择题[含多个小题]':
                    html.push(renderDanXiangWenZiXuanZeMultiple_1(n));
                    break;
                case '填空题[含多个小题]':
                    html.push(renderTianKongMultiple_1(n));
                    break;
                case '问答题[含多个小题]':
                    html.push(renderWenDaMultiple_1(n));
                    break;
		case '连线题':
                    html.push(renderMatch_1(n));
                    break;
            }
        });
        html.push('</div>');
        $('#exerciseWrapper').html(html.join(''));
	//处理连线题
        $('#exerciseWrapper .matchQuestion').each(function (j, m) {
            creatMatchline($(m));
        });
        //处理数学公式
        jQuery(function () {
            jQuery('.mathquill-editable:not(.mathquill-rendered-math)').mathquill('editable');
            jQuery('.mathquill-textbox:not(.mathquill-rendered-math)').mathquill('textbox');
            jQuery('.mathquill-embedded-latex').mathquill();
        });
        //处理听力
        $('.mp3_player').each(function (i, n) {
            var vid = $(this).attr('data-vid');
            var id = $(this).attr('id');
            polyvObject('#' + id).videoPlayer({
                'vid': vid,
                width: 300,
                height: 100
            });
        });
        if (typeof callback == 'function') {
            callback();
        }
    });
}

function creatMatchline(box) {//===========createfun
    function getPair()
	{
		return {
			"pairL":pairl,
			"pariR":pairr
		}
	}       
    var linewidth = 2, linestyle = "#0C6";//连线绘制--线宽，线色
	var canvaswidth = 300;
    //初始化赋值 列表内容
    box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
        $(this).attr({
            group: "gpl",
            left: $(this).position().left + $(this).outerWidth(),
            top: $(this).position().top + $(this).outerHeight() / 2,
            sel: "0",
            check: "0"
        });
    });
    box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
        $(this).attr({
            group: "gpr",
            left: $(this).position().left,
            top: $(this).position().top + $(this).outerHeight() / 2,
            sel: "0",
            check: "0"
        });
    }); 
    box.find(".matchQuestionLeftWrapper").attr('first', 0);//初始赋值 列表内容容器
    box.find(".matchQuestionRightWrapper").attr('first', 0);
    //canvas 赋值
    var canvas = box.find(".match_canvas")[0];  //获取canvas  实际连线标签
    canvas.width = canvaswidth;//box.find(".matchQuestionWrapper").width();//canvas宽度等于div容器宽度
    canvas.height = box.find(".matchQuestionWrapper").height();

    var backcanvas = box.find(".match_backcanvas")[0];  //获取canvas 模拟连线标签
    backcanvas.width = canvaswidth;//box.find(".matchQuestionWrapper").width();
    backcanvas.height = box.find(".matchQuestionWrapper").height();
    //连线数据
    var groupstate = false;//按下事件状态，标记按下后的移动，抬起参考
    var mx = [];//连线坐标
    var my = [];
    var ms = [];
    var temp;//存贮按下的对象
    var pair = 0;//配对属性
    var pairl = [];
    var pairr = [];
    //提示线数据
    var mid_startx, mid_starty, mid_endx, mid_endy;//存储虚拟连线起始坐标
    //事件处理---按下
    box.children(".matchQuestionWrapper").children().children("div").on("mousedown touchstart", function (event) {
        groupstate = true;
        if ($(this).attr("check") == 1) {
            $(this).attr("sel", "1").parent().attr("first", "1");
            temp = $(this);
        } else {
            $(this).attr("sel", "1").addClass("addstyle").parent().attr("first", "1");
            temp = $(this);
        }
        mid_startx = $(this).attr("left")>canvaswidth?canvaswidth:0;
        mid_starty = $(this).attr("top");
        event.preventDefault();
    }); 
    $(document).on('mousemove touchmove', function (event) {
        var $target = $(event.target);
        if (groupstate) {
            mid_endx = event.pageX - box.find(".matchQuestionWrapper").offset().left - $('.match_canvas').css('left').split('p')[0];
            mid_endy = event.pageY - box.find(".matchQuestionWrapper").offset().top;
            var targettrue = null;
            if ($target.is("div") && $target.closest(".matchQuestionWrapper").parent().attr("class") == box.attr("class")) {
                targettrue = $target;
            } else if ($target.closest(".matchQuestionShowItem").is("div") && $target.closest(".matchQuestionWrapper").parent().attr("class") == box.attr("class")) {
                targettrue = $target.closest(".matchQuestionShowItem");
            } else {
                targettrue = null;
            }
            if (targettrue) {
                if (targettrue.parent().attr("first") == 0) {
                    if (targettrue.attr("check") == 0) {
                        targettrue.addClass("addstyle").attr("sel", "1").siblings("div[check=0]").removeClass("addstyle").attr("sel", "0");
                    }
                } else {
                    if (targettrue.attr("check") == 0) {
                        targettrue.addClass("addstyle").attr("sel", "1").siblings("div[check=0]").removeClass("addstyle").attr("sel", "0");
                        mid_startx = targettrue.attr("left")>canvaswidth?canvaswidth:0;
                        mid_starty = targettrue.attr("top");
                    }
                    //temp=targettrue;
                }
            } else {
                if (box.find(".matchQuestionLeftWrapper").attr("first") == 0) {
                    box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                        if ($(this).attr('check') == 0) {
                            $(this).attr("sel", "0").removeClass("addstyle");
                        }
                    });
                }
                if (box.find(".matchQuestionRightWrapper").attr("first") == 0) {
                    box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                        if ($(this).attr('check') == 0) {
                            $(this).attr("sel", "0").removeClass("addstyle");
                        }
                    });
                }
            }
            backstrockline();
        }
        //event.preventDefault();
    });

    $(document).on('mouseup touchend', function (event) {
        var $target = $(event.target);
        if (groupstate) {
            var targettrue;
            if ($target.is("div") && $target.closest(".matchQuestionWrapper").parent().attr("class") == box.attr("class")) {
                targettrue = $target;
            } else if ($target.closest(".matchQuestionShowItem").is("div") && $target.closest(".matchQuestionWrapper").parent().attr("class") == box.attr("class")) {
                targettrue = $target.closest(".matchQuestionShowItem");
            } else {
                targettrue = null;
            }

            if (targettrue) {
                if (targettrue.parent().attr("first") == 0) {
                    if (targettrue.attr("check") == 0) {
                        if (temp.attr('check') == 1) {
                            box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                                if ($(this).attr('sel') == 1) {
                                    if ($(this).attr('check') == 0) {
                                        $(this).attr("sel", "0").removeClass("addstyle");
                                    } else {
                                        $(this).attr("sel", "0").addClass("addstyle");
                                    }
                                }
                            });
                            box.find(".matchQuestionLeftWrapper").attr('first', 0);
                            box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                                if ($(this).attr('sel') == 1) {
                                    if ($(this).attr('check') == 0) {
                                        $(this).attr("sel", "0").removeClass("addstyle");
                                    } else {
                                        $(this).attr("sel", "0").addClass("addstyle");
                                    }
                                }
                            });
                            box.find(".matchQuestionRightWrapper").attr('first', 0);

                        } else {
                            var temp_index;
                            box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                                if ($(this).attr('sel') == 1) {
                                    mx.push(0);
                                    my.push($(this).attr('top'));
                                    ms.push(0);
                                    $(this).attr("check", "1");
                                    $(this).attr("sel", "0");
                                    $(this).attr("pair", pair);
                                    pairl.push(pair);
                                    //新增的
                                    temp_index=$(this).index();       
                                }
                            });
                            box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                                if ($(this).attr('sel') == 1) {
                                    mx.push(canvaswidth);
                                    my.push($(this).attr('top'));
                                    ms.push(1);
                                    $(this).attr("check", "1");
                                    $(this).attr("sel", "0");
                                    $(this).attr("pair", pair);
                                    pairr.push(pair);
                                    //根据右侧的答案来给学生打分
                                    $(this).attr('attr_index',temp_index); 
                                }
                            });
                            pair += 1;
                            box.find(".matchQuestionLeftWrapper").attr('first', 0);
                            box.find(".matchQuestionRightWrapper").attr('first', 0);
                            strockline();
                        }
                    } else {

                        box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                            if ($(this).attr('sel') == 1) {
                                if ($(this).attr('check') == 0) {
                                    $(this).attr("sel", "0").removeClass("addstyle");
                                } else {
                                    $(this).attr("sel", "0").addClass("addstyle");
                                }
                            }
                        });
                        box.find(".matchQuestionLeftWrapper").attr('first', 0);
                        box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                            if ($(this).attr('sel') == 1) {
                                if ($(this).attr('check') == 0) {
                                    $(this).attr("sel", "0").removeClass("addstyle");
                                } else {
                                    $(this).attr("sel", "0").addClass("addstyle");
                                }
                            }
                        });
                        box.find(".matchQuestionRightWrapper").attr('first', 0);
                    }
                } else {
                    box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {

                        if ($(this).attr('check') == 0) {
                            if ($(this).attr('sel') == 1) {
                                $(this).attr("sel", "0").removeClass("addstyle");
                            }
                        } else {
                            if ($(this).attr('sel') == 1) {
                                $(this).attr("sel", "0").addClass("addstyle");
                            }
                        }
                    });
                    box.find(".matchQuestionLeftWrapper").attr('first', 0);
                    box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                        if ($(this).attr('check') == 0) {
                            if ($(this).attr('sel') == 1) {
                                $(this).attr("sel", "0").removeClass("addstyle");
                            }
                        } else {
                            if ($(this).attr('sel') == 1) {
                                $(this).attr("sel", "0").addClass("addstyle");
                            }
                        }
                    });
                    box.find(".matchQuestionRightWrapper").attr('first', 0);
                }
            } else {
                box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                    if ($(this).attr('check') == 0) {
                        if ($(this).attr('sel') == 1) {
                            $(this).attr("sel", "0").removeClass("addstyle");
                        }
                    }
                });
                box.find(".matchQuestionLeftWrapper").attr('first', 0);
                box.find(".matchQuestionRightWrapper").children("span").each(function (index, element) {
                    if ($(this).attr('check') == 0) {
                        if ($(this).attr('sel') == 1) {
                            $(this).attr("sel", "0").removeClass("addstyle");
                        }
                    }
                });
                box.find(".matchQuestionRightWrapper").attr('first', 0);
            }
            clearbackline();
            groupstate = false;
        } 
        //event.preventDefault();
    }); 

    //canvas 追加2d画布
    var context = canvas.getContext('2d');  //canvas追加2d画图
    var lastX, lastY;//存放遍历坐标
    function strockline() {//绘制方法
        context.clearRect(0, 0, box.find(".matchQuestionWrapper").width(), box.find(".matchQuestionWrapper").height());//整个画布清除
        context.save();
        context.beginPath();
        context.lineWidth = linewidth;
        for (var i = 0; i < ms.length; i++) {  //遍历绘制
            lastX = mx[i];
            lastY = my[i];
            if (ms[i] == 0) {
                context.moveTo(lastX, lastY);
            } else {
                context.lineTo(lastX, lastY);
            }
        }
        context.strokeStyle = linestyle;
        context.stroke();
        context.restore();
    }

    function clearline() {//清除
        context.clearRect(0, 0, box.find(".matchQuestionWrapper").width(), box.find(".matchQuestionWrapper").height());
        mx = [];//数据清除
        my = [];
        ms = [];
        pairl = [];
        pairr = [];
        pair = 0;
        box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
            $(this).removeClass("addstyle");
            $(this).attr("sel", "0");
            $(this).attr("check", "0");

        });
        box.find(".matchQuestionLeftWrapper").attr('first', 0);
        box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
            $(this).removeClass("addstyle");
            $(this).attr("sel", "0");
            $(this).attr("check", "0");
            $(this).removeAttr('attr_index');
        });
        box.find(".matchQuestionRightWrapper").attr('first', 0);
    }

    //init backcanvas 2d画布 虚拟绘制
    var backcanvas = backcanvas.getContext('2d');

    function backstrockline() {//绘制
        backcanvas.clearRect(0, 0, box.find(".matchQuestionWrapper").width(), box.find(".matchQuestionWrapper").height());
        backcanvas.save();
        backcanvas.beginPath();
        backcanvas.lineWidth = linewidth;
        backcanvas.moveTo(mid_startx, mid_starty);
        backcanvas.lineTo(mid_endx, mid_endy);
        backcanvas.strokeStyle = linestyle;
        backcanvas.stroke();
        backcanvas.restore();
    }

    function clearbackline() {//清除
        backcanvas.clearRect(0, 0, box.find(".matchQuestionWrapper").width(), box.find(".matchQuestionWrapper").height());
        mid_startx = null;
        mid_starty = null;
        mid_endx = null;
        mid_endy = null;
    }

    //重置
    box.find(".resetCanvasBtn").click(function () {
        clearline();
    });
    //回退
    box.find(".goBackBtn").click(function () {
        goBack();
    });
    function goBack() {
        var linenlastIndex = ms.join("").substr(0, ms.length - 1).lastIndexOf("0");
        if (linenlastIndex == 0) {
            clearline();
        } else {
            mx = mx.slice(0, linenlastIndex);  //记录值
            my = my.slice(0, linenlastIndex);  //坐标
            ms = ms.slice(0, linenlastIndex);
            context.clearRect(0, 0, box.find(".matchQuestionWrapper").width(), box.find(".matchQuestionWrapper").height());
            context.save();
            context.beginPath();
            context.lineWidth = linewidth;
            for (var i = 0; i < ms.length; i++) {
                lastX = mx[i];
                lastY = my[i];
                if (ms[i] == 0) {
                    context.moveTo(lastX, lastY);
                } else {
                    context.lineTo(lastX, lastY);
                }
            }
            context.strokeStyle = linestyle;
            context.stroke();
            context.restore();
            var pairindex = pairl.length - 1;
            box.find(".matchQuestionLeftWrapper").children("div").each(function (index, element) {
                if ($(this).attr("pair") == pairl[pairindex]) {
                    $(this).removeClass("addstyle");
                    $(this).attr("sel", "0");
                    $(this).attr("check", "0");
                    $(this).removeAttr("pair");
                }
            });
            box.find(".matchQuestionLeftWrapper").attr('first', 0);
            box.find(".matchQuestionRightWrapper").children("div").each(function (index, element) {
                if ($(this).attr("pair") == pairl[pairindex]) {
                    $(this).removeClass("addstyle");
                    $(this).attr("sel", "0");
                    $(this).attr("check", "0");
                    $(this).removeAttr("pair");
                    $(this).removeAttr('attr_index');
                }
            });
            box.find(".matchQuestionRightWrapper").attr('first', 0);
            pair -= 1;
            pairl = pairl.slice(0, pairindex);
            pairr = pairr.slice(0, pairindex);
        }
    }

    //end
}//==============fune