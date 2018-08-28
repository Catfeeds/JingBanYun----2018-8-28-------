var timeTableObject = {

    HTTP_BASE_URL: function() {
        return window.location.protocol + '//' + window.location.host + '/';
    },
    refreshClassTimeTable: function(classId, tableId, commentId,callback) {
        var url = this.HTTP_BASE_URL();
        $.get(url + 'ApiInterface/Version1_1/ClassManagement/getClassTimeTableInfo', {
            'classId': classId
        },
        function(msg) {
            msg = $.parseJSON(msg);
            if (msg.status == 500) {
                alert(msg.message);
                return;
            }
            if (typeof(msg.data.content) == 'undefined') {
                $('#' + tableId).html('');
                $.get(url + 'ApiInterface/Version1_1/ClassManagement/getClassTimeTableTemplate', {
                    'classId': classId
                },
                function(template) {
                    template = $.parseJSON(template);
                    $('#' + tableId).html(template.data);
                    for (var i = 0; i < msg.data.data.length; i++) {
                        var index = parseInt(7 * (msg.data.data[i].lesson_id - 1)) + parseInt(msg.data.data[i].day_id - 1);
                        $($('#' + tableId + ' .droppable')[index]).html('<div class="item assigned" attr="'+msg.data.data[i].day_id+','+msg.data.data[i].lesson_id+','+msg.data.data[i].teacher_id+','+msg.data.data[i].course_id+'">'+ msg.data.data[i].course +'_'+ msg.data.data[i].name+'</div>');
                    }
                    if (typeof(callback) == 'function')
                        callback();
                });
            } else {
                $('#' + tableId).html(msg.data.content);
                if (typeof(callback) == 'function')
                    callback();
            }
            $('#' +commentId).text(msg.data.comments);
        });

    },
    refreshTeacherTimeTable: function(classId, teacherId, tableId, commentId,callback) {
		var url = this.HTTP_BASE_URL();
        $.get(url + 'ApiInterface/Version1_1/ClassManagement/getTeacherTimeTableInfo', {
            'classId': classId,
            'teacherId': teacherId
        },
        function(msg) {
            msg = $.parseJSON(msg);
            if (msg.status == 500) {
                alert(msg.message);
                return;
            }
            if (typeof(msg.data.content) == 'undefined') {
                $('#' + tableId).html('');
                $.get(url + 'ApiInterface/Version1_1/ClassManagement/getTeacherTimeTableTemplate', {
                    'teacherId': teacherId
                },
                function(template) {
                    template = $.parseJSON(template);
                    $('#' + tableId).html(template.data);
                    for (var i = 0; i < msg.data.data.length; i++) {
                        var index = parseInt(7 * (msg.data.data[i].lesson_id - 1)) + parseInt(msg.data.data[i].day_id - 1);
                        $($('#' + tableId + ' .droppable')[index]).html(msg.data.data[i].classname + '</br>' + msg.data.data[i].course);
                    }
                    if (typeof(callback) == 'function')
                        callback();
                });
            } else {
                $('#' + tableId).html(msg.data.content);
                if (typeof(callback) == 'function')
                    callback();
            }
            $('#' +commentId).text(msg.data.comments);
        });

    }
};