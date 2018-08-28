var socketAction = {
    whiteboard: {
        draw: function (data, ele) {
            var eleId = $(ele).attr('id');
            var context = ele.getContext("2d");
            var position = $('#etextbook_canvas').offset();
            context.beginPath();
            var lineColor = '#FF0000';
            switch (eleId) {
                case 'etextbook_canvas':
                    lineColor = textbookWhiteboardLineColor;
                    break;
                case 'lesson_canvas':
                    lineColor = lessonWhiteboardLineColor;
                    break;
                case 'homework_canvas':
                    lineColor = homeworkWhiteboardLineColor;
                    break;
            }
            context.strokeStyle = lineColor;
            context.lineWidth = 6;
            var dataArray = data.split('#');
            $.each(dataArray, function (i, n) {
                var clientX = parseInt(n.split(',')[0]);
                var clientY = parseInt(n.split(',')[1]);
                context.lineTo((clientX - position.left), (clientY - position.top));
            });
            context.stroke();
        },
        erasure: function (data, ele) {
            var c = ele.getContext("2d");
            //c.clearRect(0, 0, ele.width, ele.height);
            var position = $('#etextbook_canvas').offset();
            var dataArray = data.split('#');
            $.each(dataArray, function (i, n) {
                var clientX = parseInt(n.split(',')[0]);
                var clientY = parseInt(n.split(',')[1]);
                c.clearRect(clientX - 20, clientY - 20, 40, 40);
            });
        },
        clear: function (ele) {
            var c = ele.getContext("2d");
            c.clearRect(0, 0, ele.width, ele.height);
        },
        opacity: function (ele, isOpacity) {
            if (isOpacity == 'false') {
                $(ele).css('background-color', '#ffffff');
            } else {
                $(ele).css('background-color', 'transparent');
            }
        },
        switchOn_Off: function (ctrlEle, whiteboardEle, isOn) {
            if (isOn == 'false') {
                $(ctrlEle).find('.btn_tuya_sub_ctrl').hide();
                $(whiteboardEle).hide();
            } else {
                $(ctrlEle).find('.btn_tuya_sub_ctrl').show();
                $(whiteboardEle).show();
            }
        },
        changeLineColor: function (rgb, target) {
            $('#' + target + 'ColorPickerWrapper .colorPicker').each(function (i, n) {
                var c = $(n).attr('data-color');
                if (c == rgb) {
                    $(n).addClass('selected');
                } else {
                    $(n).removeClass('selected');
                }
            });
            switch (target) {
                case 'homework':
                    homeworkWhiteboardLineColor = rgb;
                    break;
                case 'textbook':
                    textbookWhiteboardLineColor = rgb;
                    break;
                case 'lesson':
                    lessonWhiteboardLineColor = rgb;
                    break;
            }
        }
    },
    eTextbook: {
        turnPage: function (page1Id, page2Id) {
            turnPage(page1Id, page2Id);
        },
        playMedia: function (flashFilename, isLeftPage) {
            if (isLocked) return;
            if (platform == 'pad' && (flashFilename.indexOf('jsby') > 0 || flashFilename.indexOf('Listen') > 0 || flashFilename.indexOf('n_match') > 0))return;
            playTextbookMedia(flashFilename, isLeftPage);
        },
        playSentence: function (fileName, isLeftPage) {
            if (isLocked) return;
            playTextbookSentence(fileName, isLeftPage);
        },
        playAudio: function (fileName, isLeftPage) {
            if (isLocked) return;
            playTextbookAudio(fileName, isLeftPage);
        },
        playVideo: function (fileName, isLeftPage) {
            if (isLocked) return;
            if (platform == 'pad' && (fileName.indexOf('jsby') > 0 || fileName.indexOf('Listen') > 0))return;
            playTextbookVideo(fileName, isLeftPage);
        },
        cancelPlaySentence: function () {
            cancelTextbookPlaySentence();
        },
        switchSpeed: function () {
            switchSpeed();
        }
    },
    lesson: {
        openLessonPlannings: function (id, type) {
            openLessonPlannings(id, type);
        },
        openLessonPlanningsV3: function (id) {
            openLessonPlanningsV3(id);
        },
        returnToLessonPlannings: function () {
            returnToLessonPlannings();
        },
        gotoPPTSlide: function (lessonPlanningId, pageIndex) {
            gotoSpecifiedPPT(lessonPlanningId, pageIndex);
        },
        turnPageV3: function(pageIndex) {
            turnPageV3(pageIndex);
        },
        customCmd: function(cmd)
        {
            eval(cmd);
        }
    },
    homework: {
        createdHomework: function (type) {
            if (type == '课堂作业') {
                loadInClassHomework(document.getElementById('loadInClassHomeworkCtrl'));
            } else {
                loadOutClassHomework(document.getElementById('loadOutClassHomeworkCtrl'));
            }
        }
    },
    global: {
        switchNav: function (boxId) {
            //如果有视频，关闭视频
            if (typeof player !== 'undefined' && player != null) {
                player.j2s_pauseVideo();
                player = null;
            }

            try {
                if (typeof(eval(pauseAllVideo)) == "function") {
                    pauseAllVideo();
                }
            } catch(e) {}



            $('.box').hide();
            $('#' + boxId).show();
            $('#header .opr').removeClass('selected').each(function (i, n) {
                if ($(n).attr('data-id') == boxId) {
                    $(n).addClass('selected');
                    return false;
                }
            });
            if (boxId == 'box_homework') {
                $('.layui-layer-shade,.layui-layer').show();
            } else {
                $('.layui-layer-shade,.layui-layer').hide();
            }
            if (boxId == 'box_stugroup' && isTeacher == 'false') {
                $.get("index.php?m=Home&c=DigitalClassroom&a=getGroupStudent",{'studentId':studentId,'classroomId':classroomId},function(msg){
                    $('.htmltmp').html('');
                    $('.htmltmp').append(msg.info);
                    $('.zancountstudent').html(msg.stucount);
                });
            }
            //$('#box_stugroup_html').load("index.php?m=Home&c=DigitalClassroom&a=getGroupStudent&studentId=" + studentId+'&classroomId='+classroomId);
            //$('.layui-layer').each(function (i, n) {
            //var id = $(n).attr('id').replace('layui-layer', '');
            //layer.close(id);
            //});
        },
        refreshState: function (currentState) {
            currentState = eval('(' + currentState + ')');//json数据 序列化成js对象
            currentClassroomState = currentState;
            //根据当前的状态调整学生进入时的课堂呈现

            //1.调整tab
            $('.box').hide();
            $('#' + currentClassroomState.tab).show();
            $('#header .opr').removeClass('selected').each(function (i, n) {
                if ($(n).attr('data-id') == currentClassroomState.tab) {
                    $(n).addClass('selected');
                    return false;
                }
            });

            if (currentClassroomState.tab == 'box_homework') {
                $('.layui-layer-shade,.layui-layer').show();
            } else {
                $('.layui-layer-shade,.layui-layer').hide();
            }

            //2.textbook翻页
            try {
                var curPage = currentClassroomState.textbookPage;
                turnPage(curPage[0], curPage[1]);
            } catch (err) {
            }

            //3.lesson info
            try {
                if (currentClassroomState.is_showing_lesson_list == 'false') {
                    openLessonPlannings(currentClassroomState.the_opening_lesson, currentClassroomState.the_opening_lesson_type);
                    if (currentClassroomState.the_opening_lesson_type == 'ppt') {
                        gotoSpecifiedPPT(currentClassroomState.the_opening_lesson, currentClassroomState.the_opening_lesson_index);
                    }
                }
            } catch (err) {
            }

            //4.白板状态
            if (currentClassroomState.is_showing_textbook_whiteboard == 'true') {
                isShowingTextbookWhiteboard = true;
                $('#btn_tuya_textbook_wrapper .btn_tuya_sub_ctrl').show();
                $('#whiteboard_etextbook').show();
            } else {
                isShowingTextbookWhiteboard = false;
                $('#btn_tuya_textbook_wrapper .btn_tuya_sub_ctrl').hide();
                $('#whiteboard_etextbook').hide();
            }
            if (currentClassroomState.is_showing_lesson_whiteboard == 'true') {
                isShowingLessonWhiteboard = true;
                $('#btn_tuya_lesson_wrapper .btn_tuya_sub_ctrl').show();
                $('#whiteboard_lesson').show();
            } else {
                isShowingLessonWhiteboard = false;
                $('#btn_tuya_lesson_wrapper .btn_tuya_sub_ctrl').hide();
                $('#whiteboard_lesson').hide();
            }
            if (currentClassroomState.is_showing_homework_whiteboard == 'true') {
                isShowingHomeworkWhiteboard = true;
                $('#btn_tuya_homework_wrapper .btn_tuya_sub_ctrl').show();
                $('#whiteboard_homework').show();
            } else {
                isShowingHomeworkWhiteboard = false;
                $('#btn_tuya_homework_wrapper .btn_tuya_sub_ctrl').hide();
                $('#whiteboard_homework').hide();
            }
        }
    }
};
