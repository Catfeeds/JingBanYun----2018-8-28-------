var isShowCorrectAnswerAfterQuestion = false;
var global_I = 0;

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
    d.push('<div class="textAreaInput"><textarea type="textarea" rows="5" placeholder="请输入答案" name="q_{0}" id="q_{0}" questionid="{0}"></textarea><div>{1}</div></div>'.format(question.id));
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
        if (n.indexOf('#]') >= 0) {
            innerD.push('<input type="text" questionid="{0}" id="q_{0}_{1}" name="q_{0}_{1}" />'.format(question.id, gapsIndex));
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
            if (n.indexOf('#]') >= 0) {
                innerD.push('<input type="text" id="q_{0}_{2}_{1}" name="q_{0}_{2}_{1}" questionid="{0}" />'.format(question.id, gapsIndex, j));
                gapsIndex = gapsIndex + 1;
                n = n.substring(n.indexOf('#]') + 2);
            }
            innerD.push(n);
        });
        d.push('<div class="tianKongInput">{0}</div>'.format(innerD.join('')));
    });


    return d.join('');
}


function getQuestionTitleHtml_1(question) {
    var d = [];
    global_I += 1
    d.push('<div id="qes_{1}" class="exerciseQuestion" data-id="{1}" data-answer="" data-points="{0}">'.format(question.points, global_I));//question.question_id
    d.push('{0}. ({1}分) - '.format(global_I, question.points));
    d.push(question.questions);

    if (question.mp3_vid != '' && question.mp3_vid != null) {
        d.push('<div class="mp3_player" id="mp3_q{0}" data-vid="{1}"></div>'.format(question.id, question.mp3_vid));
    }
    if (question.points == question.student_score) {
        d.push('<img class="right_img" src="/Public/img/home/duigou.png">');
    } else {
        d.push('<img class="wrong_img" src="/Public/img/home/cha.png">');
    }
    d.push('</div>');
    //d.push('</div>');
    return d.join('');
}


//BEGIN
function createExerciseLibraryChapter(homework_id, student_id, exercises1, callback) {
    $.getJSON(getHomeworkInfoUrl, {
        id: homework_id,
        'student_id': student_id
    }, function (res) {
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
            }
        });
        html.push('</div>');
        $('#exerciseWrapper').html(html.join(''));
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