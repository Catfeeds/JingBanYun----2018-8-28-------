<?php
return array(
    'PAPERGRADE' => array(  //试卷年级
        array(
            'id' => 1,
            'name' => 1,
            'asname' => '一年级',
        ),
        array(
            'id' => 2,
            'name' => 2,
            'asname' => '二年级',
        ),
        array(
            'id' => 3,
            'name' => 3,
            'asname' => '三年级',
        ),
        array(
            'id' => 4,
            'name' => 4,
            'asname' => '四年级',
        ),
        array(
            'id' => 5,
            'name' => 5,
            'asname' => '五年级',
        ),
        array(
            'id' => 6,
            'name' => 6,
            'asname' => '六年级',
        ),
        array(
            'id' => 7,
            'name' => 7,
            'asname' => '七年级',
        ),
        array(
            'id' => 8,
            'name' => 8,
            'asname' => '八年级',
        ),
        array(
            'id' => 9,
            'name' => 9,
            'asname' => '九年级',
        ),
        array(
            'id' => 10,
            'name' => 10,
            'asname' => '十年级',
        ),
        array(
            'id' => 11,
            'name' => 11,
            'asname' => '十一年级',
        ),
        array(
            'id' => 12,
            'name' => 12,
            'asname' => '十二年级',
        ),
    ),

    'SOURCE'=>array( //学段
        array(
            'id' => 1,
            'name' => '系统录入',
        ),
        array(
            'id' => 2,
            'name' => '系统导入',
        ),
        array(
            'id' => 3,
            'name' => '外系统对接',
        ),
        array(
            'id' => 4,
            'name' => '老系统数据',
        ),

    ),

    'Studysection'=>array( //学段
        array(
            'id' => 1,
            'name' => '小学',
        ),
        array(
            'id' => 2,
            'name' => '初中',
        ),
        array(
            'id' => 3,
            'name' => '高中',
        ),

    ),

    'exercisesType'=>array( //习题类型
        array(
            'id' => 1,
            'name' => '选择',
        ),
        array(
            'id' => 2,
            'name' => '文字填空',
        ),
        array(
            'id' => 3,
            'name' => '选择填空',
        ),
        array(
            'id' => 4,
            'name' => '连线',
        ),
        array(
            'id' => 5,
            'name' => '作图',
        ),
        array(
            'id' => 6,
            'name' => '解答(问答)',
        ),
    ),

    'exerciseState'=>array(
        array(
            'id' => EXERCISE_STATE_DRAFT,
            'name' => '草稿',
        ),
        array(
            'id' => EXERCISE_STATE_PAPEREXERCISEWAITVERIFY,
            'name' => '待校审',
        ),
        array(
            'id' => EXERCISE_STATE_WAITVERIFY,
            'name' => '待校审',
        ),
        array(
            'id' => EXERCISE_STATE_PAPEREXERCISEDECLINE,
            'name' => '返工',
        ),
        array(
            'id' => EXERCISE_STATE_REPROCESS,
            'name' => '返工',
        ),
        array(
            'id' => EXERCISE_STATE_WAITASSIGN,
            'name' => '待分派',
        ),
        array(
            'id' => EXERCISE_STATE_REASSIGN,
            'name' => '重新分派',
        ),
        array(
            'id' => EXERCISE_STATE_WAITMARK,
            'name' => '待标引',
        ),
        array(
            'id' => EXERCISE_STATE_FINISH,
            'name' => '已完成'
        ),
        array(
            'id' => EXERCISE_STATE_UNINBOUND,
            'name' => '未入库',
        ),
        array(
            'id' => EXERCISE_STATE_INBOUND,
            'name' => '已入库',
        ),
        array(
            'id' => EXERCISE_STATE_ONSHELF,
            'name' => '上架',
        ),
        array(
            'id' => EXERCISE_STATE_OFFSHELF,
            'name' => '下架',
        ),
    ),
    'exerciseLogDescription'=>array(
        array(
            'id' => EXERCISE_STATE_WAITVERIFY,
            'name' => '待审核',
        ),
        array(
            'id' => EXERCISE_STATE_REPROCESS,
            'name' => '返工修改',
        ),
        array(
            'id' => EXERCISE_STATE_WAITASSIGN,
            'name' => '提交标引',
        ),
        array(
            'id' => EXERCISE_STATE_WAITMARK,
            'name' => '分派',
        ),
        array(
            'id' => EXERCISE_STATE_FINISH,
            'name' => '知识标引完成'
        ),
        array(
            'id' => EXERCISE_STATE_UNINBOUND,
            'name' => '提交审核',
        ),
        array(
            'id' => EXERCISE_STATE_INBOUND,
            'name' => '入库',
        ),
        array(
            'id' => EXERCISE_STATE_ONSHELF,
            'name' => '上架',
        ),
        array(
            'id' => EXERCISE_STATE_OFFSHELF,
            'name' => '下架',
        ),
    ),
    'schoolTerm' => array(
        array(
            'id' => 1,
            'name' => '上册',
        ),
        array(
            'id' => 2,
            'name' => '下册',
        ),
        array(
            'id' => 3,
            'name' => '全一册',
        ),
    ),
    'paperCategory' => array(  //试卷类型
        array(
            'id' => 1,
            'name' => '真题'
        ),
        array(
            'id' => 2,
            'name' => '模拟题'
        ),
        array(
            'id' => 3,
            'name' => '同步检测'
        ),
    ),
    'yunyintype' => array(  //语音习题类型
        array(
            'id' => 1,
            'name' => '词汇',
        ),
        array(
            'id' => 2,
            'name' => '句子'
        ),

        array(
            'id' => 3,
            'name' => '视频'
        ),
        array(
            'id' => 4,
            'name' => '课本'
        ),
    ),
    'questionCategory' => array(  //试卷试题类型
        array(
            'id' => 1,
            'name' => '小升初'
        ),
        array(
            'id' => 2,
            'name' => '中考'
        ),
        array(
            'id' => 3,
            'name' => '高考'
        ),
        array(
            'id' => 4,
            'name' => '期中'
        ),
        array(
            'id' => 5,
            'name' => '期末'
        ),
        array(
            'id' => 6,
            'name' => '阶段测试'
        ),
    ),
    'paperCity' => array(  //试卷类型
        array(
            'id' => 1,
            'name' => '真题'
        ),
        array(
            'id' => 2,
            'name' => '模拟题'
        ),
    ),
    'difficulty'=>array( //难度
        array(
            'id' => 1,
            'name' => '基础'
        ),
        array(
            'id' => 2,
            'name' => '中等'
        ),
        array(
            'id' => 3,
            'name' => '难题'
        ),
        array(
            'id' => 4,
            'name' => '竞赛'
        ),
    ),
    'PAGE_SIZE' => 20,
    'CASEEXERCISENUM' => array('ONE'=>1,'TWO'=>2,'THREE'=>3,'FOUR'=>4,'FIVE'=>5,'SIX'=>6),
    'ERRORCODE'=> 400,
    'SUCCESSCODE'=> 400,

);
