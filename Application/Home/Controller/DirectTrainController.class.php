<?php

namespace Home\Controller;

use Think\Controller;

define('SPECIAL_COLUMN', 1);
define('QUESTION', 2);
define('REPLY', 3);
define('DELETE_TRUE', 2);
define('DELETE_FALSE', 1);
define('UP', 1);
define('DOWN', 2);
define('WAITUP', 3);
define('WAIT', 3);
define('YES', 1);
define('NO', 2);
define('AUDIO', 3);
define('VIDIO', 1);
define('ARTICLE', 2);

define('SUCCESS_FLAG', 'SUCCESS');
define('FAIL_FLAG', 'FAIL');
import('Vendor.Alipay.Alipay');
import('Vendor.WeixinPay.Weixin');
header("Content-type: text/html; charset=utf-8");

class DirectTrainController extends Controller
{
    private $model;
    public static $orderModel = '';

    public function __construct()
    {
        parent::__construct();
        self::$orderModel = D('Order_info');
        $this->assign('oss_path', C('oss_path'));
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        header("Content-type: text/html; charset=utf-8");
        $this->model = D('Direct_train');
        $this->assign('navicon', 'bianjiaozhitongche');
        $this->assign('moduleInfo', '教材编写意图及时掌握，帮助老师更好的进行教学');
    }

    /*
     *描述：编者专栏首页
     */
    public function directTrainIndexList()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '栏目列表');
        $this->assign('navicon', 'bianjiaozhitongche');

        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $direct_train = D('Direct_train');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $where['putaway_status'] = UP;
        $where['status'] = YES;
        $where['type'] = SPECIAL_COLUMN;
        $where['ppid'] = 0;
        //获取所有学科
        $course_result = $direct_train->getSpecialColumnOne($where);
        $courseKey = array_column($course_result, 'course_name');
        $courseValue = array_column($course_result, 'course_id');
        $courseNew = array_combine($courseKey, $courseValue);
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        //获取所有讲授人
        $editor['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getgetEditorOne($editor);
        //获取所有专栏
        $type['type'] = SPECIAL_COLUMN;
        $this->getAjaxSearchData($type);
        //获取所有历史问答(只取4条数据)和回复数
        $where['type'] = QUESTION;
        $where['ppid'] = 0;
        $order = 'sort_by_putaway_status_time desc,direct_train.putaway_status_time desc';
        $questionResult = $direct_train->getSpecialColumnOne($where, '', 5,$order);//echo M()->getLastSql();die;
        $this->getReplyCount($questionResult);//回复数

        //获取当前登录用户的提问
        A('Home/Common')->getUserIdRole($userId, $role);
        $where['type'] = QUESTION;
        $where['ppid'] = 0;
        $where['special_column_editor_quizzer_id'] = $userId;
        $myQuestion = $direct_train->getSpecialColumnOne($where);
        //获取所有提问未回答的问题个数
        $questionId = array_column($myQuestion, 'id');

        $check['type'] = REPLY;
        $check['putaway_status'] = array('neq', WAITUP);
        $check['status'] = YES;
        foreach ($questionId as $item) {
            $check['question_reply_concat_id'] = $item;
            $myQuestionOfReply = $direct_train->getSpecialColumnOne($check);
            if (empty($myQuestionOfReply)) {
                $idArray[] = $item;//未回答的
            } else {
                $ReplyQuestionIdArray[] = $item;//已回答的
            }
        }
        $count = count($idArray);
        $countYes = count($ReplyQuestionIdArray);

        //对当前的用户判断是编者还是普通老师
        $editor_teacher_concat = D('Editor_teacher_concat');
        $isEditorData['delete_status'] = DELETE_FALSE;
        $isEditorData['teacher_id'] = $userId;
        $isEditorResult = $editor_teacher_concat->getgetEditorOne($isEditorData);
        if (!empty($isEditorResult)) {
            $isEditor = true;
        } else {
            $isEditor = false;
        }

        //获取当前用户已被回答的所有问题
        unset($where);
        $where['flag'] = 1;
        if (!isset($ReplyQuestionIdArray)) {
            $where['direct_train_id'] = array('IN', [0]);
        } else {
            $where['direct_train_id'] = array('IN', $ReplyQuestionIdArray);
        }
        $have = $this->model->getDataFormVisitTime($where);
        if (!empty($have)) {
            $isTrue = true;
        } else {
            $isTrue = false;
        }
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $courseNew);
        $this->assign('editors_list', $result);
        $this->assign('question_list', $questionResult);//var_dump($questionResult);die;
        $this->assign('noReplyCount', $count);
        $this->assign('YesReplyCount', $countYes);
        $this->assign('isTrue', $isTrue);
        $this->assign('isEditor', $isEditor);
        $this->display();
    }

    /*
     *描述：ajax请求筛选项
     *@zoneBit:1、ajax请求 2、方法请求
     */
    /**
     * @param array $where
     * @param int $zoneBit
     * @param string $type
     */
    public function getAjaxSearchData($where = [], $zoneBit = 2, $type = 'index')
    {
        $field = 'direct_train.*,dict_grade.grade,dict_course.course_name,dict_course.code,auth_teacher.name,auth_teacher.avatar';
        $direct_train = D('Direct_train');
        $where['delete_status'] = DELETE_FALSE;
        $where['putaway_status'] = UP;
        $where['status'] = YES;
        $check = $where;
        $checkTwo = $where;
        $whereTwo = $where;
        $order = 'putaway_status_time desc';
        if (empty($_GET['p'])) {
            $p = 1;
        } else {
            $p = $_GET['p'];
        }
        //获取当前登录用户的提问
        A('Home/Common')->getUserIdRole($userId, $role);
        $searchData = $_GET['searchData'];
        $searchData = $this->object2array(json_decode($searchData));
        if (!empty($searchData)) {
            foreach ($searchData as $k => $value) {
                if ($k == 'isDefault' && $value != 'undefind') {
                    if ($value == 'desc') {
                        $order = 'putaway_status_time desc';
                    } else {
                        $order = 'putaway_status_time asc';
                    }
                }
                if ($k == 'isNew' && $value != 'undefind') {
                    if ($value == 'desc') {
                        $order = 'putaway_status_time desc';
                    } else {
                        $order = 'putaway_status_time asc';
                    }

                }
                if ($k == 'isHot' && $value != 'undefind') {
                    if ($value == 'desc') {
                        $order = 'special_column_question_visit_count desc';
                    } else {
                        $order = 'special_column_question_visit_count asc';
                    }
                }
                if ($k == 'isPrice' && $value != 'undefind') {
                    if ($value == 'desc') {
                        $order = 'special_column_price desc';
                    } else {
                        $order = 'special_column_price asc';
                    }
                }
                if ($k == 'isToll' && $value != 'undefind') {
                    $where['special_column_price'] = array('GT', 0);
                }
                if ($k == 'isGratis' && $value != 'undefind') {
                    $where['special_column_price'] = array('EQ', 0);
                }
                if ($k == 'course_id' && $value != 'undefind') {
                    $where['direct_train.course_id'] = $value;
                }
                if ($k == 'grade_id' && $value != 'undefind') {
                    $where['direct_train.grade_id'] = $value;
                }
                if ($k == 'fascicule_id' && $value != 'undefind') {
                    $where['direct_train.fascicule_id'] = $value;
                }
                if ($k == 'editor' && $value != 'undefind') {
                    $where['special_column_editor_quizzer_id'] = $value;
                }
                if ($k == 'isPurchased' && $value != 'undefind') {
                    $where['user_id'] = $userId;
                    $where['order_type'] = 1;
                    $where['order_info.is_delete'] = DELETE_FALSE;
                    $where['order_status'] = 2;
                }
            }
        }
        /*$isDefault = getParameter('isDefault', 'str', false);
        if (!empty($isDefault)) {
            $order = '';
        }
        $isNew = getParameter('isNew', 'str', false);
        if (!empty($isNew)) {
            $order = 'direct_train.putaway_status_time desc';
        }
        $isHot = getParameter('isHot', 'str', false);
        if (!empty($isHot)) {
            $order = 'special_column_question_visit_count desc';
        }
        $isPrice = getParameter('isPrice', 'str', false);
        if (!empty($isPrice)) {
            $order = 'special_column_price desc';
        }
        $isToll = getParameter('isToll', 'str', false);//收费
        if (!empty($isToll)) {
            $where['special_column_price'] = array('GT', 0);
        }
        $isGratis = getParameter('isGratis', 'str', false);
        if (!empty($isGratis)) {
            $where['special_column_price'] = array('EQ', 0);
        }
        $course_id = getParameter('course_id', 'int', false);
        if (!empty($course_id)) {
            $where['direct_train.course_id'] = $course_id;
        }
        $grade_id = getParameter('grade_id', 'int', false);
        if (!empty($grade_id)) {
            $where['direct_train.grade_id'] = $grade_id;
        }
        $fascicule_id = getParameter('fascicule_id', 'int', false);
        if (!empty($fascicule_id)) {
            $where['direct_train.fascicule_id'] = $fascicule_id;
        }
        $editor = getParameter('editor', 'str', false);
        if (!empty($editor)) {
            $where['special_column_editor_quizzer_id'] = $editor;
        }
        $isPurchased = getParameter('isPurchased', 'str', false);
        if (!empty($isPurchased)) {
            $where['userId'] = $userId;
            $where['order_type'] = 1;
            $where['is_delete'] = DELETE_FALSE;
            $where['order_status'] = 2;
            //$join = 'order_info ON order_info.user_id = auth_teacher.id';
        }*/
        $keyword = getParameter('keyword', 'str', false);//ajax请求给iframe页面渲染
        if (!empty($keyword)) {
            $where['direct_train.search_filed|special_column_question_reply_description|name'] = array('like', '%' . trim($keyword) . '%');
        }
        $zoneBits = getParameter('zoneBit', 'int', false);//ajax请求给iframe页面渲染
        if (!empty($zoneBits)) {
            $zoneBit = $zoneBits;
        }
        $types = getParameter('ajaxType', 'str', false);//ajax请求给iframe页面渲染
        if (!empty($types)) {
            $type = $types;
        }

        if (isset($order) && !empty($order)) {
            $order = $order;
        } else {
            $order = '';
        }

        if ($type == 'other') { //历史问答页
            $where['type'] = QUESTION;
            $where['ppid'] = 0;
            $counts = $direct_train->getSpecialColumnOne($where);
            $count = count($counts);
            $order = 'sort_by_putaway_status_time desc,direct_train.putaway_status_time desc';
            $columnResult = $direct_train->getSpecialColumnAll($where, $field, $p, 12, '', $order);
            $Page = new \Think\Page($count, 12);
            $show = $Page->show('callback');
            $this->assign('page', $show);
            $this->getReplyCount($columnResult);//回复数
            $this->assign('questionList', $columnResult);
            $this->assign('column_list', $columnResult);
            $this->assign('type', $type);
        } elseif ($type == 'myYes' || $type == 'myNo') { //未回答问题页面、已回答问题页面
            $whereTwo['ppid'] = 0;
            $whereTwo['type'] = QUESTION;
            $whereTwo['special_column_editor_quizzer_id'] = $userId;
            $myQuestion = $direct_train->getSpecialColumnOne($whereTwo);
            $questionId = array_column($myQuestion, 'id');
            /*unset($where['ppid']);
            unset($where['type']);
            unset($where['special_column_editor_quizzer_id']);*/
            $check['type'] = REPLY;
            unset($check['delete_status']);
            unset($check['putaway_status']);
            unset($check['ppid']);
            $check['putaway_status'] = array('neq', WAITUP);
            // $check = array_merge($check, $where);
            foreach ($questionId as $item) {
                $check['question_reply_concat_id'] = $item;
                $myQuestionOfReply = $direct_train->getSpecialColumnOne($check);
                if (empty($myQuestionOfReply)) {
                    $idArray[] = $item;//未回答的
                } else {
                    $ReplyQuestionIdArray[] = $item;//已回答的
                }
            }
            if ($type == 'myYes') {
                // unset($check);
                if (!isset($ReplyQuestionIdArray)) {
                    $checkTwo['direct_train_id'] = array('IN', [0]);
                } else {
                    $checkTwo['direct_train_id'] = array('IN', $ReplyQuestionIdArray);
                }
                $checkTwo = array_merge($checkTwo, $where);
                $join = 'direct_train_visit_time_concat ON direct_train_visit_time_concat.direct_train_id = direct_train.id';
                $field = 'direct_train.*,dict_grade.grade,dict_course.course_name,auth_teacher.name,direct_train_visit_time_concat.flag,direct_train_visit_time_concat.visit_time,direct_train_visit_time_concat.updeta_time,dict_course.code';
                $myQuestionDataCount = $direct_train->getSpecialColumnOne($checkTwo, $join, '', '', $field);
                $count = count($myQuestionDataCount);
                $order = 'sort_by_putaway_status_time desc,direct_train.putaway_status_time desc';
                $myQuestionData = $direct_train->getSpecialColumnAll($checkTwo, $field, $p, 12, $join, $order);

                $Page = new \Think\Page($count, 12);
                $show = $Page->show('callback');
                $this->assign('page', $show);
//echo M()->getLastSql();die;
//var_dump($myQuestionData);die;
                /*unset($check);
                foreach ($myQuestionData as $k => $item) {//获取回复的数组集合
                    $parms['question_reply_concat_id'] = $item['id'];
                    $parms = array_merge($parms, $where);
                    $firstResult = $direct_train->getSpecialColumnOne($parms);
//echo M()->getLastSql();die;
//var_dump($firstResult);die;
                    foreach ($firstResult as $value) {//获取每个回复的下的所有问题和回复
                        $check['pid'] = $value['id'];
                        $check = array_merge($check, $where);
                        $secondResult = $direct_train->getSpecialColumnOne($check);
                        $count[] = count($secondResult);
                    }
                    $myQuestionData[$k]['count'] = array_sum($count);
                }*/
                $this->getReplyCount($myQuestionData);//回复数
                $this->assign('questionList', $myQuestionData);//给本页面赋值
                $this->assign('type', $type);
                $this->assign('column_list', $myQuestionData);//给iframe赋值
            }
            if ($type == 'myNo') {
                if (!isset($idArray)) {
                    $parms['direct_train.id'] = array('IN', [0]);
                } else {
                    $parms['direct_train.id'] = array('IN', $idArray);
                }
                $parms = array_merge($parms, $where);
                $myQuestionCount = $direct_train->getSpecialColumnOne($parms);
                $count = count($myQuestionCount);
                $myQuestion = $direct_train->getSpecialColumnAll($parms, $field, $p, 12);
                $Page = new \Think\Page($count, 12);
                $show = $Page->show('callback');
                $this->assign('page', $show);
                $this->assign('questionList', $myQuestion);//给本页面赋值
                $this->assign('type', $type);
                $this->assign('column_list', $myQuestion);//给iframe赋值
            }
        } else { //搜索结果页
            if ($type == 'search') {
                //$where['type'] = array('NEQ', REPLY);
                $where['ppid'] = 0;
                $map = $where;
                $pageSize = 12;
            } else { // 首页
                $where['type'] = SPECIAL_COLUMN;
                $pageSize = 9;
                if (empty($searchData)) {
                    $order = 'dict_grade.code,direct_train.fascicule_id,dict_course.sort_order,putaway_status_time desc,direct_train.special_column_question_visit_count desc';
                }
            }
            $join = "left join order_info ON order_info.resources_id = direct_train.id AND order_info.user_id = $userId AND order_info.user_role = 2 AND order_info.order_type = 1 AND order_info.is_delete = 1 AND order_info.order_status = 2";
            $field = 'direct_train.*,dict_grade.grade,dict_course.course_name,auth_teacher.name,auth_teacher.avatar,order_sn,dict_course.code';
            if ($type == 'search') {
                $columnResult = $direct_train->getModelAllList($where, $field, $p, $pageSize, $map, $join, $order, $count);
                $this->getReplyCount($columnResult);//回复数
                $this->assign('count', $count);
            } elseif ($type == 'index') {
                $columnResultCount = $direct_train->getSpecialColumnOne($where, $join, '', $order, $field);
                $count = count($columnResultCount);
                $columnResult = $direct_train->getSpecialColumnAll($where, $field, $p, $pageSize, $join, $order);//echo M()->getLastSql();die;
            }
            $this->assign('column_list', $columnResult);//echo M()->getLastSql();die;

            /*unset($where['type']);
            unset($where['ppid']);
            foreach ($columnResult as $k => $item) {//获取回复的数组集合
                if ($item['type'] == QUESTION) {//如果是问题才计算回复数
                    $parms['question_reply_concat_id'] = $item['id'];
                    $parms = array_merge($parms, $where);
                    $firstResult = $direct_train->getSpecialColumnOne($parms);
                    foreach ($firstResult as $value) {//获取每个回复的下的所有问题和回复
                        $check['pid'] = $value['id'];
                        $check = array_merge($check, $where);
                        $secondResult = $direct_train->getSpecialColumnOne($check);
                        $count[] = count($secondResult);
                    }
                    $columnResult[$k]['count'] = array_sum($count);
                }
            }*/
            //查看当前用户是否为团体VIP
            $info = D('Account_auths')->isVipInfo($userId, $role);
            if ($info['is_auth'] == 3) {
                $this->assign('vip', 'vip');
            } else {
                $this->assign('vip', 'no');
            }
            $Page = new \Think\Page($count, $pageSize);
            $show = $Page->show('callback');
            $this->assign('page', $show);
        }
        /*$this->assign('column_list', $columnResult);*/
        if ($zoneBit == 2) {

        } else {
            $this->display('iframe');//ifream页面
        }
    }

//把对象转成数组
    public function object2array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object2array($value);
            }
        }
        return $array;
    }

    /*
     *描述： 计算回复数
     */
    /**
     * @param $result
     */
    public function getReplyCount(&$result)
    {
        $direct_train = D('Direct_train');
        //$where['delete_status'] = DELETE_FALSE;
        //$where['putaway_status'] = UP;
        $where['putaway_status'] = array('neq', WAITUP);
        $where['status'] = YES;
        $parms = $where;
        $check = $where;
        foreach ($result as $k => $item) {//获取回复的数组集合
            if ($item['type'] == QUESTION) {//如果是问题才计算回复数
                $parms['question_reply_concat_id'] = $item['id'];
                //$parms = array_merge($parms, $where);
                $firstResult = $direct_train->getSpecialColumnOne($parms);//获取当前问题的所有回复
                $num = count($firstResult);
                foreach ($firstResult as $value) {//获取每个回复的下的所有问题和回复
                    $check['pid'] = $value['id'];
                    //$check = array_merge($check, $where);
                    $secondResult = $direct_train->getSpecialColumnOne($check);
                    $count[] = count($secondResult);
                }
                $totalCount = array_sum($count);
                $result[$k]['count'] = $totalCount + $num;
                unset($count);
            }
        }
    }

    /*
     *描述：搜索结果页
     */
    public function searchResultList()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '搜索结果');
        $this->assign('navicon', 'bianjiaozhitongche');
        $course_model = D('Dict_course');
        $grade_model = D('Dict_grade');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $direct_train = D('Direct_train');
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        //获取所有讲授人
        $editor['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getgetEditorOne($editor);
        //获取所有学科
        $check['delete_status'] = DELETE_FALSE;
        $check['putaway_status'] = UP;
        $check['status'] = YES;
        $check['ppid'] = 0;
        //筛选项
        $keyword = getParameter('keyword', 'str', false);
        if (!empty($keyword)) {
            $where['direct_train.search_filed|special_column_question_reply_description|name'] = array('like', '%' . trim($keyword) . '%');
            $check = array_merge($check, $where);
            $this->assign('keyword', $keyword);
        }
//$check['type'] = array('neq', REPLY);
        $course_result = $direct_train->getSpecialColumnOne($check);
        $courseKey = array_column($course_result, 'course_name');
        $courseValue = array_column($course_result, 'course_id');
        $courseNew = array_combine($courseKey, $courseValue);
        //获取所有专栏和问题
        $this->getAjaxSearchData($where, 2, $type = 'search');
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $courseNew);
        $this->assign('editors_list', $result);
        $this->display();
    }

    /*
     *描述：提问详情页
     */
    public function raiseQuestion()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '提问详情');
        $this->assign('navicon', 'bianjiaozhitongche');
        A('Home/Common')->getUserIdRole($userId, $role);
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $direct_train = D('Direct_train');
        $question_tags = D('Question_tags');
        $where['delete_status'] = DELETE_FALSE;

        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        //获取所有标签
        $tags = $question_tags->getQuestionTagsOne($where);
        //历史提问（当前登录用户所有的提问）
        $where['type'] = QUESTION;
        $where['ppid'] = 0;
        $where['special_column_editor_quizzer_id'] = $userId;
        $where['putaway_status'] = array('neq',UP);
        $questionResult = $direct_train->getSpecialColumnOne($where);
        $this->assign('questionList', $questionResult);
        $this->assign('courseList', $course_result);
        $this->assign('gradeList', $grade_result);
        $this->assign('tagsList', $tags);
        $this->assign('userId', $userId);
        $this->display('publishProblem');
    }

    /*
     *描述：ajax请求发布问题
     */

    public function addQuestion()
    {
        $direct_train = D('Direct_train');
        $question_tags = D('Question_tags');
        $filed['special_column_question_title'] = getParameter('title', 'str');
        $filed['course_id'] = getParameter('course_id', 'int');
        $filed['grade_id'] = getParameter('grade_id', 'int');
        $filed['fascicule_id'] = getParameter('fascicule_id', 'int');
        $courseName = getParameter('course_name', 'str');
        $fasciculeName = getParameter('fascicule_name', 'str');
        $gradeName = getParameter('grade_name', 'str');
        $tags = I('tags');
        $filed['special_column_question_reply_description'] = I('special_column_question_reply_description');
        $filed['special_column_editor_quizzer_id'] = getParameter('special_column_editor_quizzer_id', 'int');
        $filed['type'] = QUESTION;
        $filed['search_filed'] = $filed['special_column_question_title'] . ',' . $courseName . ',' . $fasciculeName . ',' . $gradeName;
        $direct_train->startTrans();
        //往主表里添加数据
        $insertId = $direct_train->addSpecialColumn($filed);
        //往标签关联表中添加数据
        if(!empty($tags)){
            $data['question_id'] = $insertId;
            foreach ($tags as $item) {
                $data['question_tags_id'] = $item;
                $status = $question_tags->addQuestionTagsConcat($data);
                if ($status == false) {
                    $error = true;
                }
            }
        }else{
            $error = false;
        }

        //往direct_train_visit_time_concat表中添加数据
        $insertData['direct_train_id'] = $insertId;
        $insertStatus = $this->model->addDataFormVisitTime($insertData);
        if ($insertId == false || $error == true || $insertStatus == false) {
            $direct_train->rollback();
            $this->showMessage('400', '提交失败');
        } else {
            $direct_train->commit();
            //$this->redirect('DirectTrain/DirectTrainIndexList');
            $this->showMessage('200', '提交成功');
        }
    }

    /*
     *描述：专栏详情页
     */
    public function specialColumnDetails()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '专栏详情');
        $this->assign('navicon', 'bianjiaozhitongche');
        A('Home/Common')->getUserIdRole($userId, $role);
        $id = getParameter('id', 'int');
        $where['direct_train.id'] = $id;
        $this->visitAndReplyNumberSetInc($id, 'special_column_question_visit_count');
        $join = "left join order_info ON order_info.resources_id = direct_train.id AND order_info.user_id = $userId AND order_info.user_role = 2 AND order_info.order_type = 1 AND order_info.is_delete = 1 AND order_info.order_status = 2";
        $filed = 'direct_train.*,dict_course.course_name course_name,resource_path,vid,auth_teacher.name teacher_name,order_sn,avatar,vid_fullpath';
        $detailsResult = $this->model->SpecialColumnDetails($where, $join, $filed);
        //相关推荐（根据学科年级推荐）
        $recommend = $this->recommened($id);
        //查看当前用户是否为团体VIP
        $userInfo = D('Account_auths')->isVipInfo($userId, $role);
        if ($userInfo['is_auth'] == 3) {
            $this->assign('vip', 'vip');
        } else {
            $this->assign('vip', 'no');
        }

        $this->assign('info', $detailsResult);//var_dump($detailsResult);die;
        $this->assign('recommend', $recommend);//var_dump($recommend);die;
        $this->assign('id', $id);
        $this->display();
    }

    /*
     *描述：推荐算法
     */
    public function recommened($id, $num = 4)
    {
        $direct_train = D('Direct_train');
        $detailsCountResult = array();
        $ids = array($id);
        $check['direct_train.id'] = $id;
        $detailsResult = $direct_train->getSpecialColumnOne($check);
        $where['type'] = SPECIAL_COLUMN;
        $where['status'] = YES;
        $where['delete_status'] = DELETE_FALSE;
        $where['putaway_status'] = UP;
        $step = 1;
        while (($num != 0) && ($step != 3)) {
            switch ($step) {
                case 1:
                    $where['direct_train.course_id'] = $detailsResult[0]['course_id'];
                    $where['direct_train.grade_id'] = $detailsResult[0]['grade_id'];
                    $order='';
                    break;
                case 2:
                    unset($where['direct_train.course_id']);
                    unset($where['direct_train.grade_id']);
                $order = 'special_column_question_visit_count desc';
                    break;
            }
            $where['direct_train.id'] = array('not in', $ids);
            $detailsCountResults = $direct_train->getSpecialColumnOne($where, '', $num,$order);
            $idsa = array_column($detailsCountResults,'id');
            $ids = array_merge($ids,$idsa);
            $detailsCountResult = array_merge($detailsCountResult,$detailsCountResults);
            $num -= count($detailsCountResults);
            if (0 == $num)
                break;
            $step++;
        }
        return $detailsCountResult;
    }

    /*
     *描述：问题浏览量和专栏观看数量和问题回复数问题讨论量
     */
    public function visitAndReplyNumberSetInc($id, $filed)
    {
        $where['id'] = $id;
        $this->model->visitAndReplyNumberSetInc($where, $filed);
    }

    /*
     *描述：问题详情页
     */
    public function questionsDetails()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '问题详情');
        $this->assign('navicon', 'bianjiaozhitongche');
        A('Home/Common')->getUserIdRole($userId, $role);
        $id = getParameter('id', 'int');
        //首先一进来就对浏览量时间（包括旗下的所有子问题的浏览时间）
        M('direct_train_visit_time_concat')->startTrans();
        //direct_train_visit_time_concat表中的浏览时间的修改
        unset($where);
        $where['direct_train_id'] = $id;
        $visitTime = $this->model->getDataFormVisitTime($where);
        if ($visitTime[0]['updaet_time'] < date('Y-m-d H:i:m')) {
            $visitData['flag'] = 0;
        }
        $visitData['visit_time'] = date('Y-m-d H:i:m');
        $status = $this->model->saveDataFormVisitTime($visitData, $where);
        if ($status === false) {
            M('direct_train_visit_time_concat')->rollback();
        } else {
            M('direct_train_visit_time_concat')->commit();
        }
        //浏览量修改
        $this->visitAndReplyNumberSetInc($id, 'special_column_question_visit_count');
        //根据问题ID查找问题详情
        $check['direct_train.id'] = $id;
        $questionDetailResult = $this->model->getChildReply($check);
        //获取所有的一级回复
//$parms['delete_status'] = DELETE_FALSE;
//$parms['putaway_status'] = UP;
//$parms['status'] = YES;
        $parameter['type'] = REPLY;
        $parameter['question_reply_concat_id'] = $id;
//$parameter = array_merge($parameter,$parms);
        $replyResultDetail = $this->model->getChildReply($parameter);
        foreach ($replyResultDetail as $k => $values) {
            //根据以上的所有回复ID查找每个回复ID下的所有问题和回复
            unset($check);
            $check['pid'] = $values['id'];
            $check['status'] = YES;
            $check['putaway_status'] = array('neq', WAITUP);
//$check = array_merge($check,$parms);
            $replyResultDetail[$k]['child'] = $this->model->getReplyAndQuestionAll($check);
        }
        //对当前的用户判断是编者还是普通老师
        unset($where);
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $where['teacher_id'] = $userId;
        $result = $editor_teacher_concat->getgetEditorOne($where);
        if (!empty($result)) {
            $isEditor = true;
        } else {
            $isEditor = false;
        }
        //查找问题标签项
        $question_tags = D('Question_tags');
        $whereTags['delete_status'] = DELETE_FALSE;
        $whereTags['question_id'] = $id;
        $tags = $question_tags->getTagsByQuestion($whereTags);
        $this->assign('tags', $tags);
        $this->assign('list', $replyResultDetail);//var_dump($replyResultDetail);die;
        $this->assign('isEditor', $isEditor);//var_dump($replyResultDetail);die;
        $this->assign('details', $questionDetailResult[0]);//var_dump($questionDetailResult);die;
        $this->assign('userId', $userId);//var_dump($questionDetailResult);die;//问题详情
        $this->display();
    }

    /*
     *描述：ajax提交回复或者问题
     */
    public function ajaxSubmit()
    {
        $direct_train = D('Direct_train');
        //pid/ppid/special_column_editor_quizzer_id/special_column_question_reply_description/type/question_reply_concat_id/quizzer_replier_concat_id
        $filed['pid'] = getParameter('pid', 'int');
        $filed['ppid'] = getParameter('ppid', 'int');
        $filed['special_column_editor_quizzer_id'] = getParameter('special_column_editor_quizzer_id', 'int');
        $filed['special_column_question_reply_description'] = getParameter('special_column_question_reply_description', 'str');
        $filed['type'] = getParameter('type', 'int');
        $filed['question_reply_concat_id'] = getParameter('question_reply_concat_id', 'int');
        $filed['quizzer_replier_concat_id'] = getParameter('quizzer_replier_concat_id', 'int');
        $direct_train->startTrans();
        //往主表里添加数据
        $insertId = $direct_train->addSpecialColumn($filed);
        if ($insertId == false) {
            $direct_train->rollback();
            $this->showMessage('500', '发布失败');
        } else {
            $direct_train->commit();
            $this->showMessage('200', '发布成功，请等待审核');
        }
    }

    /*
     *描述：历史问答更多和我的问题列表页
     *@type:1、历史问答 2、我的已回复的问答 3、我的未回复的问答
     */
    public function myQuestion()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '编教直通车');
        $this->assign('subnav', '问答');
        $this->assign('navicon', 'bianjiaozhitongche');
        $course_model = D('Dict_course');
        $grade_model = D('Dict_grade');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $direct_train = D('Direct_train');

        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        //获取所有讲授人
        $editor['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getgetEditorOne($editor);
        $where['delete_status'] = DELETE_FALSE;
        $where['putaway_status'] = UP;
        $where['status'] = YES;
        $where['ppid'] = 0;
        $type = getParameter('type', 'int');
        if ($type == 1) { //历史问答
            /*$where['type'] = QUESTION;
            $questionResult = $direct_train->getSpecialColumnOne($where);
            unset($where['type']);
            unset($where['ppid']);
            foreach ($questionResult as $k => $item) {//获取回复的数组集合
                $parms['question_reply_concat_id'] = $item['id'];
                $parms = array_merge($parms, $where);
                $firstResult = $direct_train->getSpecialColumnOne($parms);
                foreach ($firstResult as $value) {//获取每个回复的下的所有问题和回复
                    $check['pid'] = $value['id'];
                    $check = array_merge($check, $where);
                    $secondResult = $direct_train->getSpecialColumnOne($check);
                    $count[] = count($secondResult);
                }
                $questionResult[$k]['count'] = array_sum($count);
            }
            $this->assign('questionList', $questionResult);*/
            $this->getAjaxSearchData($where, 2, 'other');
            $this->assign('typeName', '历史问答');
        } else {
            A('Home/Common')->getUserIdRole($userId, $role);
            $check['special_column_editor_quizzer_id'] = $userId;
            $whereTwo['ppid'] = 0;
            $whereTwo['type'] = QUESTION;
            $whereTwo['special_column_editor_quizzer_id'] = $userId;
            $myQuestion = $direct_train->getSpecialColumnOne($whereTwo);
            $questionId = array_column($myQuestion, 'id');
            $checks['type'] = REPLY;
            unset($check['delete_status']);
            unset($check['putaway_status']);
            unset($check['ppid']);
            $checks['putaway_status'] = array('neq', WAITUP);
            // $check = array_merge($check, $where);
            foreach ($questionId as $item) {
                $checks['question_reply_concat_id'] = $item;
                $myQuestionOfReply = $direct_train->getSpecialColumnOne($checks);
                if (empty($myQuestionOfReply)) {
                    $idArray[] = $item;//未回答的
                } else {
                    $ReplyQuestionIdArray[] = $item;//已回答的
                }
            }
            if ($type == 3) { //我的未回复的问答
                if (!isset($idArray)) {
                    $check['direct_train.id'] = array('IN', [0]);
                } else {
                    $check['direct_train.id'] = array('IN', $idArray);
                }
                /*if (!isset($idArray)) {
                    $parms['direct_train.id'] = array('IN', [0]);
                } else {
                    $parms['direct_train.id'] = array('IN', $idArray);
                }
                $parms = array_merge($parms, $where);
                $myQuestion = $direct_train->getSpecialColumnOne($parms);
                $this->assign('questionList', $myQuestion);
                $this->assign('typeName', '未回复');*/
                $this->getAjaxSearchData($where, 2, 'myNo');
                $this->assign('typeName', '未回答');
            } else { //我的已回复的问答
                if (!isset($ReplyQuestionIdArray)) {
                    $check['direct_train.id'] = array('IN', [0]);
                } else {
                    $check['direct_train.id'] = array('IN', $ReplyQuestionIdArray);
                }
                /*unset($check);
                if (!isset($ReplyQuestionIdArray)) {
                    $check['direct_train_id'] = array('IN', [0]);
                } else {
                    $check['direct_train_id'] = array('IN', $ReplyQuestionIdArray);
                }
                $join = 'direct_train_visit_time_concat ON direct_train_visit_time_concat.direct_train_id = direct_train.id';
                $field = 'direct_train.*,dict_grade.grade,dict_course.course_name,auth_teacher.name,direct_train_visit_time_concat.flag,direct_train_visit_time_concat.visit_time,direct_train_visit_time_concat.updeta_time';
                $myQuestionData = $direct_train->getSpecialColumnOne($check, $join, '', '', $field);*/
//echo M()->getLastSql();die;
//var_dump($myQuestionData);die;
                /*unset($check);
                foreach ($myQuestionData as $k => $item) {//获取回复的数组集合
                    $parms['question_reply_concat_id'] = $item['id'];
                    $parms = array_merge($parms, $where);
                    $firstResult = $direct_train->getSpecialColumnOne($parms);
//echo M()->getLastSql();die;
//var_dump($firstResult);die;
                    foreach ($firstResult as $value) {//获取每个回复的下的所有问题和回复
                        $check['pid'] = $value['id'];
                        $check = array_merge($check, $where);
                        $secondResult = $direct_train->getSpecialColumnOne($check);
                        $count[] = count($secondResult);
                    }
                    $myQuestionData[$k]['count'] = array_sum($count);
                }*/
                /*$this->getReplyCount($myQuestionData);//回复数
                $this->assign('questionList', $myQuestionData);
                $this->assign('typeName', '已回复');*/
                $this->getAjaxSearchData($where, 2, 'myYes');
                $this->assign('typeName', '已回答');
            }
        }
        //获取所有学科
        $check['type'] = QUESTION;
        $check = array_merge($check, $where);
        $course_result = $direct_train->getSpecialColumnOne($check);
        $courseKey = array_column($course_result, 'course_name');
        $courseValue = array_column($course_result, 'course_id');
        $courseNew = array_combine($courseKey, $courseValue);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $courseNew);
        $this->assign('editors_list', $result);
        $this->display();
    }

    //确认订单
    public function confirmOrder()
    {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId, $role);//判断角色

        if ($userId == -1 && $role == -1) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $filter['direct_train.id'] = getParameter('id', 'str');

        if (empty($filter['direct_train.id'])) { //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $filter['direct_train.delete_status'] = 1;
        $filter['direct_train.status'] = 1;
        $filter['direct_train.putaway_status'] = 1;


        $userOrder = D('Direct_train')->getDetailsInfo($filter);

        //$userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['id'],$userId,$role );//根据角色获取对应的订单
        if ($userOrder === false) {
            exit("专栏异常,下单失败");
        }
        if (empty($userOrder['avatar'])) {
            if ($userOrder['sex'] == "男") {
                $userOrder['avatar'] = "public/web_img/App/teacher_m.png";
            } else {
                $userOrder['avatar'] = "public/web_img/App/teacher_w.png";
            }
        }
        $this->assign('order', $userOrder);
        $this->display();
    }

    //确认支付
    public function confirmPayment()
    {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId, $role);//判断角色
        if ($userId == -1 && $role == -1) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $filter['direct_train.id'] = getParameter('id', 'str', false);
        if (empty($filter['direct_train.id'])) { //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $filter['direct_train.delete_status'] = 1;
        $filter['direct_train.status'] = 1;
        $filter['direct_train.putaway_status'] = 1;

        $res_info = D('Direct_train')->getDetailsInfo($filter);

        $userOrder = self::$orderModel->getDirectByOrder($filter['direct_train.id'], $userId, $role);//根据角色获取对应的订单

        if (!empty($res_info)) {

            if (empty($userOrder)) { //直接跳转到成功的页面
                $order_data = [];
                $order_data['order_sn'] = StrOrderOne();
                $order_data['user_role'] = $role;
                $order_data['resources_id'] = $res_info['id'];
                $order_data['user_id'] = $userId;
                $order_data['pay_fee'] = $res_info['special_column_price'];
                $order_data['pay_source'] = 1;
                $order_data['order_type'] = 1;
                $order_data['create_at'] = time();
                $id = self::$orderModel->addOrder($order_data);//入库订单

                $order_info = self::$orderModel->DirectIdGetOrderInfo($id, $userId, $role);//获取订单信息

            } else {//已经下单

                if ($userOrder['order_status'] == 3 || $userOrder['is_delete'] == 2) {
                    $order_data = [];
                    $order_data['order_sn'] = StrOrderOne();
                    $order_data['user_role'] = $role;
                    $order_data['resources_id'] = $res_info['id'];
                    $order_data['user_id'] = $userId;
                    $order_data['pay_fee'] = $res_info['special_column_price'];
                    $order_data['pay_source'] = 1;
                    $order_data['order_type'] = 1;
                    $order_data['create_at'] = time();
                    $id = self::$orderModel->addOrder($order_data);//入库订单
                    $order_info = self::$orderModel->DirectIdGetOrderInfo($id, $userId, $role);//获取订单信息
                } else {
                    $order_info = $userOrder;
                }

            }
            $this->redirect('rightPayment', array('order_sn' => $order_info['order_sn']));
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

    }

    //立即支付
    public function rightPayment()
    {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId, $role);//判断角色
        if ($userId == -1 && $role == -1) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $filter['order_sn'] = getParameter('order_sn', 'str', false);
        if (empty($filter['order_sn'])) { //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }


        $userOrder = self::$orderModel->directSnGetOrderInfo($filter['order_sn'], $userId, $role);//根据角色获取对应的订单

        if ($userOrder['order_status'] == 2) { //直接跳转到成功的页面
            $this->redirect('successOrder', array('order_sn' => $userOrder['order_sn'], 'resources_id' => $userOrder['resources_id']));
        }
        if (!empty($userOrder) && $userOrder['order_status'] == 1) {
            if (!empty($userOrder['special_column_question_title'])) {
                $userOrder['special_column_question_title'] = mb_substr($userOrder['special_column_question_title'], 0, 10, 'utf-8');
            }
            $order_data["desc"] = trimall($userOrder['special_column_question_title']);
            //$order_data['attach']=$userOrder['name'];
            $order_data['order_sn'] = $userOrder['order_sn'] . '_' . date("YmdHis");
            $order_data['pay_fee'] = $userOrder['pay_fee'] * 100;
            //$order_data['goods_id']='4483094';
            $object = new \Weixin();
            $url = $object->createQRCode($order_data);
            $this->assign('qrcode_rul', $url);

            $this->assign('module', '励耘圈');
            $this->assign('nav', '编教直通车');
            $this->assign('subnav', '栏目购买');
            $this->assign('navicon', 'bianjiaozhitongche');

            $this->display('confirmPayment');
        } else {
            die('非法用户');
        }
    }

    //支付完成
    public function successOrder()
    {
        $this->id = I('resources_id');
        $this->display();
    }

    //发布问题
    public function publishProblem()
    {
        $this->display();
    }


    /*
     * 异步地址
     */
    public function wxNotify()
    {
        file_put_contents('./Public/order.json', json_encode($GLOBALS['notify_data']));
        $pay_log = M('pay_log');
        $object = new \Weixin();
        $result = $object->notifyHandle(false);

        $resonp_order = explode('_', $GLOBALS['notify_data']['out_trade_no']);
        $GLOBALS['notify_data']['out_trade_no'] = $resonp_order[0];

        //下单日志
        $addwxmap['code'] = '下单成功';
        $addwxmap['msg'] = json_encode($GLOBALS['notify_data']);
        $addwxmap['order_sn'] = $GLOBALS['notify_data']['out_trade_no'];
        $pay_log->add($addwxmap);

        if ($result == true) {

            $wxData = $this->checkWxData();

            if ($wxData['status'] === true) { //改变订单状态和时间
                self::$orderModel->editOrderStatusSuccess($GLOBALS['notify_data']['out_trade_no']);
                echo "SUCCESS";
                //消息推送

                $orderinfo = self::$orderModel->getOrderInfo($GLOBALS['notify_data']['out_trade_no']);

                $orderdata = self::$orderModel->DirectIdGetOrderInfo($orderinfo['id'], $orderinfo['user_id'], $orderinfo['user_role']);

                $parameters = [];
                $parameters = array(
                    'msg' => array(
                        $orderdata['pay_fee'],
                        $orderdata['special_column_question_title'],
                        $GLOBALS['notify_data']['out_trade_no'],
                    ),
                    'url' => array('type' => 0)
                );


                if ($orderinfo['user_role'] == 2) { //教师

                    $userinfo = D('Auth_teacher')->getTeachInfo($orderinfo['user_id']);
                    $iphone = $userinfo['telephone'];
                }

                if ($orderinfo['user_role'] == 3) {//学生
                    $userinfo = D('Auth_student')->getStudentInfo($orderinfo['user_id']);
                    $iphone = $userinfo['parent_tel'];
                }

                if ($orderinfo['user_role'] == 4) {//家长
                    $userinfo = D('Auth_parent')->getParentInfo($orderinfo['user_id']);
                    $iphone = $userinfo['telephone'];
                }
                $is_send_map['order_sn'] = $resonp_order[0];
                $is_send = $pay_log->where($is_send_map)->find();

                if (!empty($iphone) && $is_send['is_send'] == 1) {

                    $sendOrder['is_send'] = 2;
                    $pay_log->where($is_send_map)->save($sendOrder);

                    A('Home/Message')->addPushUserMessage('ORDER_SUCCESS', $orderinfo['user_role'], $orderinfo['user_id'], $parameters);

                    $sendexplode = [];
                    $sendexplode[] = $orderdata['pay_fee'] . '元';
                    $sendexplode[] = $orderdata['special_column_question_title'];
                    $sendexplode[] = $GLOBALS['notify_data']['out_trade_no'];

                    $sendmessage['iphone'] = $iphone;
                    $sendmessage['msg'] = implode(',', $sendexplode);

                    $iphonesend = $this->sendIphone($sendmessage);
                    file_put_contents('./Public/sendinfo.json', json_encode($iphonesend) . PHP_EOL, FILE_APPEND);
                }

                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";

            } else {

                $data['code'] = $wxData['error_code'];
                $data['msg'] = $wxData['error_msg'];
                $pay_log->add($data);
            }

        } else {
            $data['code'] = 1001;
            $data['msg'] = '返回错误';
            $pay_log->add($data);
        }
    }

    //微信成功跳转
    public function getOrderStatsu()
    {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId, $role);//判断角色
        if ($userId == -1 && $role == -1) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role, '2');//加载html头部文件

        $filter['order_sn'] = getParameter('order_sn', 'str', false);
        if (empty($filter['order_sn'])) { //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $userOrder = self::$orderModel->directSnGetOrderInfo($filter['order_sn'], $userId, $role);//根据角色获取对应的订单
        if ($userOrder['order_status'] == 2) {
            $this->ajaxReturn('jump');
        } else {
            $this->ajaxReturn('error');
        }
    }


    /*
     * 判断微信携带过来的参数是否正确
     */
    public function checkWxData()
    {
        //操作数据库拿到该订单ID的部分信息;
        $order_result = self::$orderModel->getOrderInfo($GLOBALS['notify_data']['out_trade_no']);
        $order_result['pay_fee'] = $order_result['pay_fee'] * 100;//换算成分
        $error_code = 0;
        if ($GLOBALS['notify_data']['return_code'] != SUCCESS_FLAG) {
            $error_code = 1002;
            $error_msg = 'return_code 微信通信失败';

        } elseif (isset($GLOBALS['notify_data']['return_msg'])) {
            if ($GLOBALS['notify_data']['return_msg'] != '') {
                $error_code = 1003;
                $error_msg = 'return_msg 微信签名失败';
            }
        } elseif ($GLOBALS['notify_data']['result_code'] != SUCCESS_FLAG) {
            $error_code = 1004;
            $error_msg = 'result_code 微信业务结果处理失败 ' . isset($GLOBALS['notify_data']['err_code']) ? $GLOBALS['notify_data']['err_code'] : '' . ' ' . isset($GLOBALS['notify_data']['err_code_des']) ? $GLOBALS['notify_data']['err_code_des'] : '';

        } elseif (empty($this->orderQuery($GLOBALS['notify_data']['transaction_id']))) {
            $error_code = 1005;
            $error_msg = '微信携带的订单号在该查询接口中没有找到';

        } elseif (empty($order_result)) {
            $error_code = 1006;
            $error_msg = '微信携带的商户订单号在系统中不存在';

        } elseif ($GLOBALS['notify_data']['total_fee'] != $order_result['pay_fee']) {//这里是实付款
            $error_code = 1007;
            $error_msg = '微信携带的订单金额和系统中的不一致';
        }

        if ($error_code) {
            $data['status'] = false;
            $data['error_code'] = $error_code;
            $data['error_msg'] = $error_msg;
        } else {
            $data['status'] = true;
        }
        return $data;
    }

    /*
    * 查询订单号
    */
    public function orderQuery($transaction_id)
    {
        //$transaction_id='4001212001201705110393819727';
        $object = new \Weixin();
        $result = $object->orderQuery($transaction_id);
        return $result;
    }

}
