<?php
namespace Home\Controller;


use Common\Common\CSV;
use Common\Common\RedisLock;
use Think\Controller;

define('RECOMMEND_RESOURCE_CLOUMN_ID', 2);
define('RECOMMEND_RESOURCE_ROWS', 6);
define('BOUTIQUE_RESOURCE_COLUMN_ID', 1);
define('BOUTIQUE_RESOURCE_ROWS', 9);
define('HJ_RESOURCE_CLOUMN_ID', 3);
define('HJ_RESOURCE_ROWS', 4);
define('TOP_LEVEL', 1);

define('CULTURE_RESOURCE_COLUMN_ID', 6);
define('CULTURE_RESOURCE_ROWS', 4);

define('CULTURE_RESOURCE_ID', 40);
define('SHOW_TIME', 1501516800);
$SERVER_NAME = $_SERVER['SERVER_NAME'];
switch($SERVER_NAME)
{
    case 'www.jtypt.com':
        define('JIAO_XUE_SHE_JI',1);
        define('JIAO_XUE_KE_JIAN',2);
        define('JIAO_XUE_QIE_TU',3);
        define('WEI_KE_SHI_PIN',4);
        define('AN_LI',6);
        define('JIAO_XUE_FAN_SI',9);

        define('TU_PIAN_SU_CAI',13);
        define('KE_TANG_SHI_LU',15);
        define('JIAO_CAI_QING_JING_DONG_HUA',17);
        define('JIAO_CAI_IN_PIN',18);
        define('LIAN_XI_JIAO_HU_DONG_HUA',19);
        define('JIAO_CAI_JIAO_FA_FEN_XI',20);
        define('GONG_JU',21);
        define('SHI_YAN',22);
        define('HUI_BEN_JIAO_XUE_ZI_YUAN',26);
        break;
    case 'www.jingbanyun.com':
        define('JIAO_XUE_SHE_JI',11);
        define('JIAO_XUE_KE_JIAN',12);
        define('TU_PIAN_SU_CAI',13);
        define('WEI_KE_SHI_PIN',14);
        define('KE_TANG_SHI_LU',15);
        define('JIAO_CAI_QING_JING_DONG_HUA',17);
        define('JIAO_CAI_IN_PIN',18);
        define('LIAN_XI_JIAO_HU_DONG_HUA',19);
        define('JIAO_CAI_JIAO_FA_FEN_XI',20);
        define('GONG_JU',21);
        define('SHI_YAN',22);
        define('JIAO_XUE_FAN_SI',23);
        define('HUI_BEN_JIAO_XUE_ZI_YUAN',26);
        break;
    case 'test.jingbanyun.com':
        define('JIAO_XUE_SHE_JI',11);
        define('JIAO_XUE_KE_JIAN',12);
        define('TU_PIAN_SU_CAI',13);
        define('WEI_KE_SHI_PIN',14);
        define('KE_TANG_SHI_LU',15);
        define('JIAO_CAI_QING_JING_DONG_HUA',17);
        define('JIAO_CAI_IN_PIN',18);
        define('LIAN_XI_JIAO_HU_DONG_HUA',19);
        define('JIAO_CAI_JIAO_FA_FEN_XI',20);
        define('GONG_JU',21);
        define('SHI_YAN',22);
        define('JIAO_XUE_FAN_SI',23);
        define('HUI_BEN_JIAO_XUE_ZI_YUAN',24);
        break;
    default :
        define('JIAO_XUE_SHE_JI',1);
        define('JIAO_XUE_KE_JIAN',2);
        define('JIAO_XUE_QIE_TU',3);
        define('WEI_KE_SHI_PIN',4);
        define('AN_LI',6);
        define('JIAO_XUE_FAN_SI',9);

        define('TU_PIAN_SU_CAI',13);
        define('KE_TANG_SHI_LU',15);
        define('JIAO_CAI_QING_JING_DONG_HUA',17);
        define('JIAO_CAI_IN_PIN',18);
        define('LIAN_XI_JIAO_HU_DONG_HUA',19);
        define('JIAO_CAI_JIAO_FA_FEN_XI',20);
        define('GONG_JU',21);
        define('SHI_YAN',22);
        define('HUI_BEN_JIAO_XUE_ZI_YUAN',26);
        break;
}

class BjResourceController extends PublicController
{
    public $model = '';
    private $esAvailable;
    function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        header("Content-type: text/html; charset=utf-8");
        $this->model = D('Knowledge_resource');
    }

    private function __getHomeResources($courseId,$gradeId){
        $columnArray = array(RECOMMEND_RESOURCE_CLOUMN_ID, BOUTIQUE_RESOURCE_COLUMN_ID, HJ_RESOURCE_CLOUMN_ID, CULTURE_RESOURCE_COLUMN_ID);
        $sizeArray = array(RECOMMEND_RESOURCE_ROWS, BOUTIQUE_RESOURCE_ROWS, RECOMMEND_RESOURCE_ROWS, CULTURE_RESOURCE_ROWS);
        $listArray = [];
        $esAvailable = getESAvailable();
        if(1 == $esAvailable){
            for ($i = 0; $i < sizeof($columnArray); $i++) {
                $where['userCourseId'] = $courseId;
                $where['userGradeId'] = $gradeId;
                $where['knowledge_resource_attr.column_id'] = $columnArray[$i];
                if($columnArray[$i] == CULTURE_RESOURCE_COLUMN_ID){
                    $where['knowledge_resource.id'] = GUOXUE_ID;
                }
                $listArray[$i] = $this->model->getResourceData($where,'',0,0,$sizeArray[$i])['data'];
                foreach($listArray[$i] as $key=>$val){
                    $listArray[$i][$key]['resource_id'] =  $val['id'];
                    $listArray[$i][$key]['mobile_cover'] =  $val['img'];
                }
            }
        }
        else {
            $redis_obj = new \Common\Common\REDIS();
            $redis = $redis_obj->init_redis();
            $listArray = array();

            if (1001 != $redis) {

                for ($i = 0; $i < sizeof($columnArray); $i++) {
                    $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-$columnArray[$i]";
                    $resourceIdList[$i] = $redis->get($redis_key);
                }
                $redis->close();
                for ($i = 0; $i < sizeof($columnArray); $i++) {
                    if (!empty($resourceIdList[$i])) {
                        $listArray[$i] = $this->model->getColumnResourceByOrderIds($resourceIdList[$i], $sizeArray[$i]);
                    }
                }
            }
        }
        return $listArray;
    }

    function bjResourceIndex()
    {
        //phpinfo();exit;
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        $userId = 0;
        $role = 0;
        A('Home/Common')->getUserIdRole($userId, $role);

        $row = A('Home/Common')->getBjResourcePrompt($userId, $role);
        if (empty($row)) {
//            $this->display('bjFunctionGuidance');
            //           exit();
        }
        //判断是否显示引导

        A('Home/Common')->authJudgement($role);

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');

        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $course_model = D('Dict_course_copy_resource');
        $grade_model = D('Dict_grade');
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');
        $column_model = D('Dict_column');

        $source_arr = C('KNOWLEDGE_RESOURCE_SOURCE');
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        $resource_type = $knowledge_resource_model->getResourceType();
        $grade_result = $grade_model->getGradeList();
        $course_list = $course_model->getCourseList();
        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if (1001 != $redis && 1002 != $redis) {
            $treeKey = $_SERVER['SERVER_NAME'] . ':knowledge_tree';
            $treeValue = $redis->get($treeKey);
        }
        if (empty($treeValue)) {
            $publishingDate = $knowledge_resource_model->publishing(1);
            $this->assign('publishingDate', $publishingDate);
            $knowledge_tree = $knowledge_resource_model->getCourseByPublishing2($publishingDate['publishing_house_id']);
            foreach ($knowledge_tree as $key => $course_value) {
                $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse2($course_value['id']);
                foreach ($knowledge_tree[$key]['child'] as $k => $textbook) {
                    $knowledge_tree[$key]['child'][$k]['chapter'] = $textbook_model->getChapterByTextbook($textbook['id']);
                }
            }
            if (1001 != $redis && 1002 != $redis) {
                $redis->setex($treeKey, 86400, (json_encode($knowledge_tree)));
            }
        } else {
            $knowledge_tree = json_decode($treeValue, true);
        }

        //get sort resources ids from redis
        $courseGradeInfo = A('Home/Common')->getUserCourseGradeInfo($role, $userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];
        $listArray = $this->__getHomeResources($courseId,$gradeId);


        $recommend_resource = (!empty($listArray[0])) ? $listArray[0] : $knowledge_resource_model->getColumnResource(RECOMMEND_RESOURCE_CLOUMN_ID, RECOMMEND_RESOURCE_ROWS);
        //给推荐的资源如果是图片的话计算出此资源拥有的图片总数
        foreach ($recommend_resource as $k=>$item){
            if($item['file_type'] == 'image'){
                $contact_data = $this->model->getResourceContactFiles($item['resource_id']);
                $count = count($contact_data);
                $recommend_resource[$k]['count'] = $count;
            }
        }
        //var_dump($recommend_resource);die();
        $boutique_resource = (!empty($listArray[1])) ? $listArray[1] : $knowledge_resource_model->getColumnResource(BOUTIQUE_RESOURCE_COLUMN_ID, BOUTIQUE_RESOURCE_ROWS);
        $hj_resource = (!empty($listArray[2])) ? $listArray[2] : $knowledge_resource_model->getColumnResource(HJ_RESOURCE_CLOUMN_ID, RECOMMEND_RESOURCE_ROWS);
        $culture_resource = (!empty($listArray[3])) ? $listArray[3] : $knowledge_resource_model->getColumnResource(CULTURE_RESOURCE_COLUMN_ID, CULTURE_RESOURCE_ROWS);            //echo "<pre>";   print_r($recommend_resource);  die;
        foreach($culture_resource as $key=>$val){
            if($val['id'] == GUOXUE_ID)
            {
                $culture_resource = [];
                $culture_resource[] = $val;
                break;
            }
        }
        //获取栏目排好序之后的id数组
        $column_sort = $column_model->getColumnSortForId(array(RECOMMEND_RESOURCE_CLOUMN_ID, BOUTIQUE_RESOURCE_COLUMN_ID, HJ_RESOURCE_CLOUMN_ID, CULTURE_RESOURCE_COLUMN_ID));
        $this->assign('column_sort', $column_sort);
        //$this->assign('chaterp_resource',$chaterp_resource);
        $this->assign('culture_resource_count', count($culture_resource));
        $this->assign('recommend_num', RECOMMEND_RESOURCE_CLOUMN_ID);
        $this->assign('boutique_num', BOUTIQUE_RESOURCE_COLUMN_ID);
        $this->assign('hj_num', HJ_RESOURCE_CLOUMN_ID);
        $this->assign('culture_resource_num', CULTURE_RESOURCE_COLUMN_ID);
        $this->assign('culture_resource_id', CULTURE_RESOURCE_ID);

        $this->assign('source_arr', $source_arr);
        $this->assign('grade_list', $grade_result);
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->assign('resource_type', $resource_type);
        $this->assign('course_list', $course_list);
        $this->assign('knowledge_tree', $knowledge_tree);
        $this->assign('recommend_resource', $recommend_resource);
        $this->assign('boutique_resource', $boutique_resource);
        $this->assign('hj_resource', $hj_resource);
        $this->assign('culture_resource', $culture_resource);
        $this->display_nocache();
    }


    /*
     * 获得下一级知识点
     */
    function getNextLevelKnowledge()
    {

        $textmodel = D('Biz_textbook');
        $knowledge_id = getParameter('id', 'int', false);
        $level = getParameter('level', 'int', false);
        $course_id = getParameter('course_id', 'int', false);
        $grade_id = getParameter('grade_id', 'int', false);
        $school_term = getParameter('school_term', 'int', false);
        if (!empty($knowledge_id) && !empty($level)) { //知识树操作
            if ($level == TOP_LEVEL) {
                //获得电子课本
                $textbook_result = $this->model->getTextbookInfo2($knowledge_id);
                if (!empty($textbook_result)) {
                    $result = $this->model->getKnowledgeChapter2($textbook_result['grade_id'], $textbook_result['course_id'], $textbook_result['id'], $level);
                } else {
                    $this->showjson(401, '数据异常!');
                }
            } else {
                //$knowledge_id=getParameter('id','int');
                $result = $this->model->getKnowledgePointByParentId2($knowledge_id, $level);
                //var_dump($result);die;
            }
        } elseif (!empty($course_id) && !empty($grade_id) && !empty($school_term)) { //高级筛选中的章操作
            //获得电子课本
            $textbook_result = $textmodel->getTextbookInfo($course_id, $grade_id, $school_term);
            if (!empty($textbook_result)) {
                $result = $this->model->getKnowledgeChapter(0, 0, $textbook_result['id']);
            } else {
                $this->showjson(401, '数据异常!');
            }
        } elseif (!empty($knowledge_id)) { //高级筛选中的节操作
            $result = $this->model->getKnowledgePointByParentId($knowledge_id);
        }
        $this->showjson(200, '', $result);
    }


    /*
     *老版搜索结果页
     */
    function bjResourceLists()
    {
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        //('Home/Common')->authJudgement();

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course_copy_resource');
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');

        $filter['keyword'] = getParameter('keyword', 'str', false);
        $filter['attr_type'] = getParameter('attr_type', 'int', false);
        $filter['file_type'] = getParameter('file_type', 'str', false);
        $filter['course'] = getParameter('course', 'int', false);
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['textbook'] = getParameter('textbook', 'int', false);
        $filter['chapter'] = getParameter('chapter', 'int', false);
        $filter['festival'] = getParameter('festival', 'int', false);
        $filter['knowledge'] = getParameter('knowledge', 'int', false);
        $filter['child_knowledge'] = getParameter('child_knowledge', 'int', false);


        $special_course = 40;

        $filter['s_time'] = getParameter('s_time', 'str', false);
        $filter['e_time'] = getParameter('e_time', 'str', false);
        $filter['author'] = getParameter('author', 'str', false);
        $filter['column_type'] = getParameter('column_type', 'int', false);
        $filter['school_term'] = getParameter('school_term', 'int', false);
        $order = getParameter('order', 'str', false);
        $order = $order ? $order : 'browse';

        $filter_arr['putaway_start_time'] = $filter['s_time'];
        $filter_arr['putaway_end_time'] = $filter['e_time'];

        if (!empty($filter['keyword'])) $check['knowledge_resource_point.knowledge_info'] = array("like", "%" . $filter['keyword'] . "%");
        if (!empty($filter['attr_type'])) $check['knowledge_resource_type_contact.type_id'] = $filter['attr_type'];
        if (!empty($filter['file_type'])) $check['knowledge_resource.file_type'] = $filter['file_type'];
        if (!empty($filter['course'])) $check['knowledge_resource_point.course'] = $filter['course'];
        if (!empty($filter['grade'])) $check['knowledge_resource_point.grade'] = $filter['grade'];
        if (!empty($filter['textbook'])) $check['knowledge_resource_point.textbook'] = $filter['textbook'];
        if (!empty($filter['chapter'])) $check['knowledge_resource_point.chapter'] = $filter['chapter'];
        if (!empty($filter['festival'])) $check['knowledge_resource_point.festival'] = $filter['festival'];
        if (!empty($filter['knowledge'])) $check['knowledge_resource_point.knowledge'] = $filter['knowledge'];
        if (!empty($filter['child_knowledge'])) $check['knowledge_resource_point.child_knowledge'] = $filter['child_knowledge'];
        if (!empty($filter['author'])) $check['knowledge_resource.author'] = $filter['author'];
        if (!empty($filter['column_type'])) $check['knowledge_resource_attr.column_id'] = $filter['column_type'];
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];


        if ($filter_arr['putaway_start_time'] != '' && $filter_arr['putaway_end_time'] != '') {
            if (date('Y-m-d', strtotime($filter_arr['putaway_start_time'])) == $filter_arr['putaway_start_time'] && date('Y-m-d', strtotime($filter_arr['putaway_end_time'])) == $filter_arr['putaway_end_time']) {
                $check['_string'] = 'putaway_time>=' . strtotime($filter_arr['putaway_start_time']) . ' and putaway_time<=' . strtotime($filter_arr['putaway_end_time']);
            } else {
                unset($filter_arr['putaway_start_time']);
                unset($filter_arr['putaway_end_time']);
            }
        } elseif (!empty($filter_arr['putaway_start_time'])) {
            if (date('Y-m-d', strtotime($filter_arr['putaway_start_time'])) == $filter_arr['putaway_start_time']) {
                $check['_string'] = 'putaway_time>=' . strtotime($filter_arr['putaway_start_time']);
            } else {
                unset($filter_arr['putaway_start_time']);
            }
        } elseif (!empty($filter_arr['putaway_end_time'])) {

            if (date('Y-m-d', strtotime($filter_arr['putaway_end_time'])) == $filter_arr['putaway_end_time']) {
                $check['_string'] = 'putaway_time<=' . strtotime($filter_arr['putaway_end_time']);
            } else {
                unset($filter_arr['putaway_end_time']);
            }
        }

        $check['knowledge_resource.putaway_status'] = PUTAWAY;
        $check['knowledge_resource.status'] = APPROVE;
        $grade_result = $grade_model->getGradeList();
        $courseGradeInfo = A('Home/Common')->getUserCourseGradeInfo($role, $userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];

        if ($filter['column_type'] != 0)
            $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-" . $filter['column_type'];
        else
            $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-0";
        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if (1001 != $redis && 1002 != $redis) {
            $resourceIdList = $redis->get($redis_key);
            $redis->close();
        }
        $check['userCourseId'] = $courseId;
        $check['userGradeId'] = $gradeId;
        $result = $this->model->getResourceData($check, $order, $userId, $role, 21, $resourceIdList);
        $this->model->createResourceTempTable();
        $this->model->insertTempData($check);

//        $filter['grade']=$spcial_grade;//国学
//        $filter['textbook']=$spcial_textbook;
        $condition_str = '';
        foreach ($filter as $key => $val) {
            $condition_str .= '&' . $key . '=' . $val;
        }
        $exists_condition = array();
        //学科
        if (!empty($filter['course'])) {
            $url = $this->joinUrl($filter, 'course');
            $course_result = $course_model->getCourseInfo($filter['course']);
            if (!empty($course_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $course_result['course_name']);
                $this->assign('exists_course', $course_result['course_name']);
            }
        } else {
            $course_con = $this->model->getCondition('course');
        }
        //var_dump($course_result['course_name']);die;
        //年级
        if (!empty($filter['grade'])) {
            $url = $this->joinUrl($filter, 'grade');
            $grade_temp_result = $grade_model->getGradeInfo($filter['grade']);        //echo "<pre>";print_r($grade_temp_result);die;
            if (!empty($grade_temp_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $grade_temp_result['grade']);
                $this->assign('exists_grade', $grade_temp_result['grade']);
            }
        } else {
            $grade_con = $this->model->getCondition('grade');
        }
        //分册
        if (!empty($filter['school_term'])) {
            $url = $this->joinUrl($filter, 'school_term');
            $s_textbook = 1;
            $x_textbook = 2;
            $q_textbook = 3;
            $textbook_str = '';
            if ($filter['school_term'] == $s_textbook) {
                $textbook_str = '上册';
            } elseif ($filter['school_term'] == $x_textbook) {
                $textbook_str = '下册';
            } elseif ($filter['school_term'] == $q_textbook) {
                $textbook_str = '全一册';
            }
            if ($textbook_str != '') {
                $exists_condition[] = array('url' => $url, 'name' => $textbook_str);
                $this->assign('exists_textbook', $textbook_str);
            }
        } else {
            $school_term_con = $this->model->getCondition('school_term');
        }
        //资源类型
        if (!empty($filter['file_type'])) {
            $url = $this->joinUrl($filter, 'file_type');
            $file_type_arr = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
            $file_type = '';
            foreach ($file_type_arr as $key => $val) {
                if ($val['value'] == $filter['file_type']) {
                    $file_type = $key;
                }
            }
            if ($file_type != '') {
                $exists_condition[] = array('url' => $url, 'name' => $file_type);
                $this->assign('exists_file_type', $file_type);
            }
        } else {
            $file_type_con = $this->model->getCondition('file_type');
        }

        //栏目名称
        if (!empty($filter['column_type'])) {
            $url = $this->joinUrl($filter, 'column_type');
            $column_result = $this->model->getColumnInfo($filter['column_type']);
            if (!empty($column_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $column_result['column_name']);
                $this->assign('exists_column_type', $column_result['column_name']);
            }
        } else {
            $attr_con = $this->model->getColumnAttr($check);
        }

        //资源类型
        /* if (!empty($filter['attr_type'])) {
             $url = $this->joinUrl($filter, 'attr_type');
             $resourceType = $this->model->getResource_type($filter['attr_type']);
             if (!empty($resourceType)) {
                 $exists_condition[] = array('url' => $url, 'name' => $resourceType['type_name']);
                 $this->assign('exists_attr_type', $resourceType['type_name']);
             }
         } else {
             $type_con = $this->model->getType($check);
         }*/

        //章
        if (!empty($filter['chapter'])) {
            $url = $this->joinUrl($filter, 'chapter');
            $chapter_result = $this->model->getKnowledgePointInfo($filter['chapter']);
            if (!empty($chapter_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $chapter_result['knowledge_name']);
            }
        }
        //节
        if (!empty($filter['festival'])) {
            $url = $this->joinUrl($filter, 'festival');
            $festival_result = $this->model->getKnowledgePointInfo($filter['festival']);
            if (!empty($festival_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $festival_result['knowledge_name']);
            }
        }
        /*
        //上架开始时间
        if(!empty($filter_arr['putaway_start_time'])){
            $url=$this->joinUrl($filter,'s_time');
            $exists_condition[]=array('url'=>$url,'name'=>$filter_arr['putaway_start_time']);
        } */

        if (!empty($filter_arr['putaway_start_time']) || !empty($filter_arr['putaway_end_time'])) {
            $array = $filter;
            if (!empty($filter_arr['putaway_start_time']) && !empty($filter_arr['putaway_end_time'])) {
                unset($array['s_time']);
                unset($array['e_time']);
                $time_ = $filter_arr['putaway_start_time'] . ' --- ' . $filter_arr['putaway_end_time'];
            } elseif (!empty($filter_arr['putaway_end_time'])) {
                unset($array['e_time']);
                $time_ = '截止上架日期' . $filter_arr['putaway_end_time'];
            } else {
                unset($array['s_time']);
                $time_ = $filter_arr['putaway_start_time'] . '起';
            }
            $url = '';
            foreach ($array as $key => $val) {
                $url .= '&' . $key . '=' . $val;
            }
            $exists_condition[] = array('url' => $url, 'name' => $time_);
        }


        //作者
        if (!empty($filter['author'])) {
            $url = $this->joinUrl($filter, 'author');
            $exists_condition[] = array('url' => $url, 'name' => $filter['author']);
        }
        //知识点
        if (!empty($filter['knowledge'])) {
            $url = $this->joinUrl($filter, 'knowledge');
            $knowledge_result = $this->model->getKnowledgePointInfo($filter['knowledge']);
            if (!empty($knowledge_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $knowledge_result['knowledge_name']);
            }
        }
        //子级知识点
        if (!empty($filter['child_knowledge'])) {
            $url = $this->joinUrl($filter, 'child_knowledge');
            $child_knowledge_result = $this->model->getKnowledgePointInfo($filter['child_knowledge']);
            if (!empty($child_knowledge_result)) {
                $exists_condition[] = array('url' => $url, 'name' => $child_knowledge_result['knowledge_name']);
            }
        }

        $resource_type = $knowledge_resource_model->getResourceType();
        $course_list = $course_model->getCourseList();
//       $course_list2=$this->model->getCourseByPublishing2();
//       $grade_result2=$this->model->getGradeByResources();//这里不能这么做，此处无用
        $knowledge_tree = $course_list;
        foreach ($knowledge_tree as $key => $course_value) {
            $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse($course_value['id']);
        }


        $this->assign('type_con', $type_con);//资源类型（教学设计...）
        $this->assign('keyword', $filter['keyword']);
        $this->assign('attr_type', $filter['attr_type']);
        $this->assign('grade_list', $grade_result);
//        $this->assign('grade_list2',$grade_result2);
//       $this->assign('course_list2',$course_list2);
        $this->assign('course_list', $course_list);
        $this->assign('order', $order);
        $this->assign('special_course', $special_course);

        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->assign('exists_condition', $exists_condition);
        $this->assign('course_con', $course_con);
        $this->assign('grade_con', $grade_con);
        $this->assign('school_term_con', $school_term_con);
        $this->assign('file_type_con', $file_type_con);
        $this->assign('attr_con', $attr_con);
        $this->assign('condition_str', $condition_str);

        $this->assign('list', $result['data']);                //echo "<pre>";print_r($result['data']);die;
        $this->assign('page', $result['page']);
        $this->assign('count', $result['count']);
        $this->assign('resource_type', $resource_type);
        $this->assign('knowledge_tree', $knowledge_tree);

        $this->display_nocache();
    }

    /*
     *新的列表页
     */
    public function bjResourceList()
    {
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        //('Home/Common')->authJudgement();

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');

        //获取教材版本
        $publishingDate = $knowledge_resource_model->publishing(1);
        $this->assign('publishingDate', $publishingDate);
        $knowledge_tree = $knowledge_resource_model->getCourseByPublishing2($publishingDate['publishing_house_id']);
        foreach ($knowledge_tree as $key => $course_value) {
            $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse2($course_value['id']);

            foreach ($knowledge_tree[$key]['child'] as $k => $textbook) {
                $knowledge_tree[$key]['child'][$k]['chapter'] = $textbook_model->getChapterByTextbook($textbook['id']);
            }
        }

        $filter['tags'] = getParameter('tags', 'str', false);
        $filter['attr_type'] = getParameter('attr_type', 'int', false);
        $filter['course'] = getParameter('course', 'int', false);
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['textbook'] = getParameter('textbook', 'int', false);
        $filter['chapter'] = getParameter('chapter', 'int', false);
        $filter['festival'] = getParameter('festival', 'int', false);
        $filter['knowledge'] = getParameter('knowledge', 'int', false);
        $filter['child_knowledge'] = getParameter('child_knowledge', 'int', false);
        $filter['column_type'] = getParameter('column_type', 'int', false);
        if (!empty($filter['course'])) $check['knowledge_resource_point.course'] = $filter['course'];
        //if (!empty($filter['grade'])) $check['knowledge_resource_point.grade'] = $filter['grade'];
        if (!empty($filter['textbook'])) $check['knowledge_resource_point.textbook'] = $filter['textbook'];
        if (!empty($filter['chapter'])) $check['knowledge_resource_point.chapter'] = $filter['chapter'];
        if (!empty($filter['festival'])) $check['knowledge_resource_point.festival'] = $filter['festival'];
        if (!empty($filter['knowledge'])) $check['knowledge_resource_point.knowledge'] = $filter['knowledge'];
        if (!empty($filter['child_knowledge'])) $check['knowledge_resource_point.child_knowledge'] = $filter['child_knowledge'];
        if (!empty($filter['column_type'])) $check['knowledge_resource_attr.column_id'] = $filter['column_type'];
        if (!empty($filter['attr_type'])) $check['knowledge_resource_type_contact.type_id'] = $filter['attr_type'];
        $check['knowledge_resource.putaway_status'] = PUTAWAY;
        $check['knowledge_resource.status'] = APPROVE;

        //判断是否筛选到节
        if(empty($filter['festival']) && empty($filter['knowledge']) && empty($filter['child_knowledge']) && empty($filter['tags']) && $filter['course'] !== 40){
            //不是节的话走新的方法
            $this->newBjResourceList($check);die;
        }

        $this->ajaxScreening($check, 1, 21);

        $knowledge_resource_model = D('Knowledge_resource');

        $course_model = D('Dict_course_copy_resource');
        $course_list = $course_model->getCourseList();//高级筛选中的学科
        $this->assign('course_list', $course_list);
        $grade_model = D('Dict_grade');
        $grade_result = $grade_model->getGradeList();//高级检索中的年级
        $this->assign('grade_list', $grade_result);
        $this->assign('cloumn_type', $filter['column_type']);
        $resource_type = $knowledge_resource_model->getResourceType();
        $this->assign('resource_type', $resource_type);//资源类型（教学设计...）
        $this->assign('attr_type', $filter['attr_type']);  //教学设计
        //var_dump($resource_type);die;
        $attr_con = $this->model->getColumnAttr();
        $this->assign('attr_con', $attr_con);
        $this->assign('attr_con_type', $filter['column_type']);
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        //把年级学科等值渲染到页面中
        $this->assign('course', $filter['course']);
        //$this->assign('grade',$filter['grade']);
        $this->assign('textbook', $filter['textbook']);
        $this->assign('chapter', $filter['chapter']);
        $this->assign('festival', $filter['festival']);
        $this->assign('knowledge', $filter['knowledge']);
        $this->assign('child_knowledge', $filter['child_knowledge']);
        //知识树渲染
        $this->assign('knowledge_tree', $knowledge_tree);
        $this->display();
    }

    /**
     *描述：新版的资源列表页1.0
     * pearm $tag 标识为true表示从搜索进来的
     * 一共有两个途径可以调用此方法：1：从知识树节以上节点点击进来的 2:点击搜索或者高级搜索进来的
     */
    public function newBjResourceList($check=[],$tag=false)
    {
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        $grade_model = D('Dict_grade');
        $knowledge_resource_model = D('Knowledge_resource');
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');

        //获取教材版本
        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if (1001 != $redis && 1002 != $redis) {
            $treeKey = $_SERVER['SERVER_NAME'] . ':knowledge_tree';
            $treeValue = $redis->get($treeKey);
        }
        if (empty($treeValue)) {
            $publishingDate = $knowledge_resource_model->publishing(1);
            $this->assign('publishingDate', $publishingDate);
            $knowledge_tree = $knowledge_resource_model->getCourseByPublishing2($publishingDate['publishing_house_id']);
            foreach ($knowledge_tree as $key => $course_value) {
                $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse2($course_value['id']);
                foreach ($knowledge_tree[$key]['child'] as $k => $textbook) {
                    $knowledge_tree[$key]['child'][$k]['chapter'] = $textbook_model->getChapterByTextbook($textbook['id']);
                }
            }
            if (1001 != $redis && 1002 != $redis) {
                $redis->setex($treeKey, 86400, (json_encode($knowledge_tree)));
            }
        } else {
            $knowledge_tree = json_decode($treeValue, true);
        }
        //知识树渲染
        $this->assign('knowledge_tree', $knowledge_tree);

        //获取所有的资源类型
        $resource_type = $knowledge_resource_model->getResourceType();
        //根据资源类型获取对应的总资源数
        foreach ($resource_type as $k => $item) {
            $check['knowledge_resource_type_contact.type_id'] = $item['id'];
            $result = $this->model->gerResourceCount($check);
            /*$ks[$k] = $item['id'];
            $v[$k] = $result['count'];*/
            /*$newArr[$k]['id'] = $item['id'];
            $newArr[$k]['count'] = $result['count'];*/
            if ($item['id'] == WEI_KE_SHI_PIN) { //4
                $newArr[1]['id'] = $item['id'];
                $newArr[1]['count'] = $result;

            }
            if ($item['id'] == SHI_YAN) {//22
                $newArr[2]['id'] = $item['id'];
                $newArr[2]['count'] = $result;

            }
            if ($item['id'] == TU_PIAN_SU_CAI) {//13
                $newArr[3]['id'] = $item['id'];
                $newArr[3]['count'] = $result;

            }
            if ($item['id'] == JIAO_XUE_SHE_JI) {//1
                $newArr[4]['id'] = $item['id'];
                $newArr[4]['count'] = $result;

            }
            if ($item['id'] == KE_TANG_SHI_LU) {//15

                $newArr[5]['id'] = $item['id'];
                $newArr[5]['count'] = $result;
            }
            if ($item['id'] == JIAO_XUE_KE_JIAN) {//2
                $newArr[6]['id'] = $item['id'];
                $newArr[6]['count'] = $result;

            }
            if ($item['id'] == LIAN_XI_JIAO_HU_DONG_HUA) {//19

                $newArr[7]['id'] = $item['id'];
                $newArr[7]['count'] = $result;
            }
            if ($item['id'] == JIAO_CAI_JIAO_FA_FEN_XI) {//20
                $newArr[8]['id'] = $item['id'];
                $newArr[8]['count'] = $result;

            }
            if ($item['id'] == JIAO_XUE_FAN_SI) {//9

                $newArr[9]['id'] = $item['id'];
                $newArr[9]['count'] = $result;
            }
            if ($item['id'] == JIAO_CAI_QING_JING_DONG_HUA) {//17

                $newArr[10]['id'] = $item['id'];
                $newArr[10]['count'] = $result;
            }
            if ($item['id'] == JIAO_CAI_IN_PIN) {//18

                $newArr[11]['id'] = $item['id'];
                $newArr[11]['count'] = $result;
            }
            if ($item['id'] == HUI_BEN_JIAO_XUE_ZI_YUAN) {//26

                $newArr[12]['id'] = $item['id'];
                $newArr[12]['count'] = $result;
            }
            if ($item['id'] == GONG_JU) {//21

                $newArr[13]['id'] = $item['id'];
                $newArr[13]['count'] = $result;
            }
            if ($item['id'] == AN_LI) {//6

                $newArr[14]['id'] = $item['id'];
                $newArr[14]['count'] = $result;
            }
            if ($item['id'] == JIAO_XUE_QIE_TU) {//3

                $newArr[15]['id'] = $item['id'];
                $newArr[15]['count'] = $result;
            }
        }
        //重组数组成规定的排序
        ksort($newArr);
        $emptyCount = array_column($newArr,'count');
        static $tags = false;
        foreach ($emptyCount as $value){
            if($value > 0){
                $tags = true;
            }
        }
        $this->assign('tags', $tags);
        $this->assign('list', $newArr);

        $resource_type = $knowledge_resource_model->getResourceType();
        $this->assign('resource_type', $resource_type);//资源类型（教学设计...）
        $course_list = $this->model->getCourseByPublishing2();
        $this->assign('course_list', $course_list);

        $course_model = D('Dict_course_copy_resource');
        $course_list2 = $course_model->getCourseList();//高级筛选中的学科
        $this->assign('course_list2', $course_list2);
        //var_dump($course_list2);die;
        $grade_result = $grade_model->getGradeList();
        $this->assign('grade_list', $grade_result);
        $attr_con = $this->model->getColumnAttr();
        $this->assign('attr_con', $attr_con);
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->assign('keyword',getParameter('keyword','str',false));
        //分解URL
        $url = '';
        foreach ($_GET as $k => $value) {
            $url .= "&$k=$value";
        }
        $this->assign('url', $url);
        if($tag){
            $this->assign('action', 'bjResourceSearch');
            $this->display('newSearchBjResourceList');//没有树的中间页
        }else{
            $this->assign('action', 'bjResourceList');
            $this->assign('tags', 'tags');
            $this->display('newBjResourceList');//有树的中间页
        }

    }
    /*
     *搜索结果页Ajaax筛选请求
     */
    public function ajaxScreening($check = '', $status = '', $pages = 20) //这两个参数用于搜索和高级检索或者带知识树的搜索结果页
    {
        //接到的值是数组
        $temp_arr = I('temp_arr');
        //带知识树的搜索结果页传过来的要渲染页面标识
        $other = I('other');
        $temp_arr_temp = I('temp_arr_temp');
        //var_dump($temp_arr);var_dump($temp_arr_temp);die;
        //1、遍历数组2、拆分3、拼接Where条件
        $check['knowledge_resource.putaway_status'] = PUTAWAY;
        $check['knowledge_resource.status'] = APPROVE;
        if (!empty($temp_arr)) {
            foreach ($temp_arr as $item_temp) {
                $temp = explode('_', $item_temp);
                if ($temp[0] == 'course') {
                    $temp_course[] = $temp[1];
                    $check['knowledge_resource_point.course'] = array('IN', $temp_course);
                } elseif ($temp[0] == 'grade') {
                    $temp_grade[] = $temp[1];
                    $check['knowledge_resource_point.grade'] = array('IN', $temp_grade);
                } elseif ($temp[0] == 'textbook') {
                    $temp_textbook[] = $temp[1];
                    $check['biz_textbook.school_term'] = array('IN', $temp_textbook);
                } elseif ($temp[0] == 'column') {
                    $temp_column[] = $temp[1];
                    $check['knowledge_resource_attr.column_id'] = array('IN', $temp_column);
                } elseif ($temp[0] == 'file') {
                    $temp_file[] = $temp[1];
                    $check['knowledge_resource.file_type'] = array('IN', $temp_file);
                } elseif ($temp[0] == 'type') {
                    $temp_type[] = $temp[1];
                    $check['knowledge_resource_type_contact.type_id'] = array('IN', $temp_type);
                } elseif ($temp[0] == 'desc') {
                    if ($temp[1] == 'desc') {
                        $order = 'desc';
                    } else {
                        $order = '';
                    }
                }
            }
        }
//var_dump($temp_arr_temp);die;
        if (!empty($temp_arr_temp)) {
            foreach ($temp_arr_temp as $temp_temp) {
                $info = explode('_', $temp_temp);
                if ($info[0] == 'course') {
                    $check['knowledge_resource_point.course'] = $info[1];
                } elseif ($info[0] == 'textbook') {
                    $check['knowledge_resource_point.textbook'] = $info[1];
                } elseif ($info[0] == 'chapter') {
                    $check['knowledge_resource_point.chapter'] = $info[1];
                } elseif ($info[0] == 'festival') {
                    $check['knowledge_resource_point.festival'] = $info[1];
                } elseif ($info[0] == 'knowledge') {
                    $check['knowledge_resource_point.knowledge'] = $info[1];
                } elseif ($info[0] == 'child_knowledge') {
                    $check['knowledge_resource_point.child_knowledge'] = $info[1];
                } elseif ($info[0] == 'keyword') {
                    $info[1] = preg_replace('/\s+/', ' ', $info[1]);
                    $info[1] = preg_replace('/\%+/', '\%', $info[1]);
                    $temp_arr = explode(' ', $info[1]);
                    foreach ($temp_arr as $item) {
                        $check['knowledge_resource_point.knowledge_info'][] = array("like", "%" . $item . "%");
                    }
                } elseif ($info[0] == 'stime') {
                    $arr['stime'] = $info[1];
                } elseif ($info[0] == 'etime') {
                    $arr['etime'] = $info[1];
                } elseif ($info[0] == 'author') {
                    $info[1] = preg_replace('/\s+/', ' ', $info[1]);
                    $info[1] = preg_replace('/\%+/', '\%', $info[1]);
                    $temp_arr = explode(' ', $info[1]);
                    foreach ($temp_arr as $item) {
                        $check['knowledge_resource.author'][] = array("like", "%" . $item . "%");
                    }
                }
            }
            if ($arr['stime'] != '' && $arr['etime'] != '') {
                if (date('Y-m-d', strtotime($arr['stime'])) == $arr['stime'] && date('Y-m-d', strtotime($arr['etime'])) == $arr['etime']) {
                    $check['_string'] = 'putaway_time>=' . strtotime($arr['stime']) . ' and putaway_time<=' . strtotime($arr['etime']);
                } else {
                    unset($arr['stime']);
                    unset($arr['etime']);
                }
            } elseif (!empty($arr['stime'])) {
                if (date('Y-m-d', strtotime($arr['stime'])) == $arr['stime']) {
                    $check['_string'] = 'putaway_time>=' . strtotime($arr['stime']);
                } else {
                    unset($arr['stime']);
                }
            } elseif (!empty($arr['etime'])) {
                if (date('Y-m-d', strtotime($arr['etime'])) == $arr['etime']) {
                    $check['_string'] = 'putaway_time<=' . strtotime($arr['etime']);
                } else {
                    unset($arr['etime']);
                }
            }
        }
        //查询操作
        A('Home/Common')->getUserIdRole($userId, $role);
        $resourceIdList = '';
        if (ROLE_TEACHER == $role) {
            $teacherInfo = D('Auth_teacher_second')->getCourseGradeById($userId);
            $courseId = $teacherInfo[0]['course_id'];
            $gradeId = $teacherInfo[0]['grade_id'];
            $redis_obj = new \Common\Common\REDIS();
            $redis = $redis_obj->init_redis();
            if (1001 != $redis && 1002 != $redis) {
                $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-0";
                $resourceIdList = $redis->get($redis_key);
                $redis->close();
            }
            $check['userCourseId'] = $courseId;
            $check['userGradeId'] = $gradeId;
        }
        if ($other == 2 || $status == 2) {
            $pages = 21;
        }



        $result = $this->model->getResourceData($check, $order, $userId, $role, $pages, $resourceIdList);
        //给推荐的资源如果是图片的话计算出此资源拥有的图片总数
        foreach ($result['data'] as $k=>$item){
            if($item['file_type'] == 'image'){
                $contact_data = $this->model->getResourceContactFiles($item['id']);
                $count = count($contact_data);
                $result['data'][$k]['count'] = $count;
            }
        }
        //往页面赋值，用于筛选项复选框的选中状态（赋的值是数组）
        $this->assign('list', $result['data']);                //echo "<pre>";print_r($result['data']);die;
        $this->assign('page', $result['page']);
        $this->assign('count', $result['count']);
        $this->assign('keyword', getParameter('keyword', 'str', false));
        //var_dump($other);die;
        if ($status == 1) {

        } elseif ($other == 2 || $status == 2) {
            $this->display('bjResourceListRight');//渲染带知识树的搜索结果页
        } else {
            $this->display('bjResourceSearchIframe');//渲染不带知识树的搜索结果页
        }

    }


    /*
     *新的搜索结果页
     */
    public function bjResourceSearch()
    {
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }

        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course_copy_resource');
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');


        $keyword = trim(getParameter('keyword', 'str', false));
        $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
        $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
        $filter['attr_type'] = getParameter('attr_type', 'int', false);
        $filter['file_type'] = getParameter('file_type', 'str', false);
        $filter['course'] = getParameter('course', 'int', false);
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['textbook'] = getParameter('textbook', 'int', false);
        $filter['chapter'] = getParameter('chapter', 'int', false);
        $filter['festival'] = getParameter('festival', 'int', false);
        $filter['knowledge'] = getParameter('knowledge', 'int', false);
        $filter['child_knowledge'] = getParameter('child_knowledge', 'int', false);
        $special_course = 40;
        $filter['s_time'] = getParameter('s_time', 'str', false);
        $filter['e_time'] = getParameter('e_time', 'str', false);
        $filter['e_time'] = $filter['e_time']? date('Y-m-d',strtotime($filter['e_time']) + 86400): 0;
        $author = trim(getParameter('author', 'str', false));
        $filter['author'] = preg_replace('/\s+/', ' ', $author);
        $filter['author'] = preg_replace('/\%+/', '\%', $filter['author']);
        $filter['column_type'] = getParameter('column_type', 'int', false);
        $filter['school_term'] = getParameter('school_term', 'int', false);
        $order = getParameter('order', 'str', false);
        $filter_arr['putaway_start_time'] = $filter['s_time'];
        $filter_arr['putaway_end_time'] = $filter['e_time'];
        if (!empty($keyword)) {
            $temp_arr = explode(' ', $filter['keyword']);
            foreach ($temp_arr as $item) {
                $check['knowledge_resource_point.knowledge_info'][] = array("like", "%" . $item . "%");
            }

        }
        if (!empty($author)) {
            $temp_arr = explode(' ', $filter['author']);
            foreach ($temp_arr as $item) {
                $check['knowledge_resource.author'][] = array("like", "%" . $item . "%");
            }

        }
        if (!empty($filter['attr_type'])) $check['knowledge_resource_type_contact.type_id'] = $filter['attr_type'];
        if (!empty($filter['file_type'])) $check['knowledge_resource.file_type'] = $filter['file_type'];
        if (!empty($filter['course'])) $check['knowledge_resource_point.course'] = $filter['course'];
        if (!empty($filter['grade'])) $check['knowledge_resource_point.grade'] = $filter['grade'];
        if (!empty($filter['textbook'])) $check['knowledge_resource_point.textbook'] = $filter['textbook'];
        if (!empty($filter['chapter'])) $check['knowledge_resource_point.chapter'] = $filter['chapter'];
        if (!empty($filter['festival'])) $check['knowledge_resource_point.festival'] = $filter['festival'];
        if (!empty($filter['knowledge'])) $check['knowledge_resource_point.knowledge'] = $filter['knowledge'];
        if (!empty($filter['child_knowledge'])) $check['knowledge_resource_point.child_knowledge'] = $filter['child_knowledge'];
        if (!empty($filter['column_type'])) $check['knowledge_resource_attr.column_id'] = $filter['column_type'];
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];
        if ($filter_arr['putaway_start_time'] != '' && $filter_arr['putaway_end_time'] != '') {
            if (date('Y-m-d', strtotime($filter_arr['putaway_start_time'])) == $filter_arr['putaway_start_time'] && date('Y-m-d', strtotime($filter_arr['putaway_end_time'])) == $filter_arr['putaway_end_time']) {
                $check['_string'] = 'putaway_time>=' . strtotime($filter_arr['putaway_start_time']) . ' and putaway_time<=' . strtotime($filter_arr['putaway_end_time']);
            } else {
                unset($filter_arr['putaway_start_time']);
                unset($filter_arr['putaway_end_time']);
            }
        } elseif (!empty($filter_arr['putaway_start_time'])) {
            if (date('Y-m-d', strtotime($filter_arr['putaway_start_time'])) == $filter_arr['putaway_start_time']) {
                $check['_string'] = 'putaway_time>=' . strtotime($filter_arr['putaway_start_time']);
            } else {
                unset($filter_arr['putaway_start_time']);
            }
        } elseif (!empty($filter_arr['putaway_end_time'])) {
            if (date('Y-m-d', strtotime($filter_arr['putaway_end_time'])) == $filter_arr['putaway_end_time']) {
                $check['_string'] = 'putaway_time<=' . strtotime($filter_arr['putaway_end_time']);
            } else {
                unset($filter_arr['putaway_end_time']);
            }
        }

        $check['knowledge_resource.putaway_status'] = PUTAWAY;
        $check['knowledge_resource.status'] = APPROVE;
        $grade_result = $grade_model->getGradeList();
        $resourceIdList = '';

        //判断是否筛选到节和是否选择资源类型
        if(empty($filter['attr_type']) && empty($filter['festival']) && empty($filter['knowledge']) && empty($filter['child_knowledge'])){
            //不是的话走新的方法
            $this->newBjResourceList($check,true);die;
        }

        $courseGradeInfo = A('Home/Common')->getUserCourseGradeInfo($role, $userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];
        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if (1001 != $redis && 1002 != $redis) {
            $redis_key = "recommendResource-$courseId-$gradeId-0";
            $resourceIdList = $redis->get($redis_key);
            $redis->close();
        }

        $check['userCourseId'] = $courseId;
        $check['userGradeId'] = $gradeId;


        $result = $this->model->getResourceData($check, $order, $userId, $role, 20, $resourceIdList);
        $this->ajaxScreening($check, 1, 20);//第三个参数是页数
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////


        $resource_type = $knowledge_resource_model->getResourceType();
        $this->assign('resource_type', $resource_type);//资源类型（教学设计...）
        $course_list = $this->model->getCourseByPublishing2();
        $this->assign('course_list', $course_list);

        $course_model = D('Dict_course_copy_resource');
        $course_list2 = $course_model->getCourseList();//高级筛选中的学科
        $this->assign('course_list2', $course_list2);
        //var_dump($course_list);die;
        $grade_result = $grade_model->getGradeList();
        $this->assign('grade_list', $grade_result);
        $attr_con = $this->model->getColumnAttr();
        $this->assign('attr_con', $attr_con);
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->assign('course', $filter['course']);
        $this->assign('grade', $filter['grade']);
        $this->assign('school_term', $filter['school_term']);
        $this->assign('file_type', $filter['file_type']);
        $this->assign('column_type', $filter['column_type']);
        $this->assign('attr_type', $filter['attr_type']);  //教学设计
        $this->assign('keyword', $keyword);
        $this->assign('chapter', $filter['chapter']);
        $this->assign('festival', $filter['festival']);
        $this->assign('s_time', $filter['s_time']);
        $this->assign('e_time', $filter['e_time']);
        $this->assign('author', $filter['author']);
        $this->display();
    }


    /*
     * 访问nobook
     */
    public function bjResourceTools()
    {
        if (!session('?teacher') && !session('?student') && !session('?parent')) {
            redirect(U('Index/index'));
        }
        A('Home/Common')->authJudgement();

        $target_url = getParameter('url', 'str');

        $time = time();
        $appid = C('NOBOOK_CONFIG.appid');
        $appkey = C('NOBOOK_CONFIG.appkey');
        $string = md5($appid . $time . $appkey);
        $url = "http://shengwu.nobook.com.cn/openapi/get_resource?appid=" . $appid . '&code=' . $string . "&time=" . $time;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        header('Location:' . $target_url);
    }

    /*
     * 拼成某个url
     */
    public function joinUrl($array, $key)
    {
        $condition_str = '';
        unset($array[$key]);
        if ($key == 'course' || $key == 'grade' || $key == 'school_term') {
            unset($array['chapter']);
            unset($array['festival']);
        }
        foreach ($array as $key => $val) {
            $condition_str .= '&' . $key . '=' . $val;
        }
        return $condition_str;
    }


    /*
     * 资源详情
     */
    public function bjResourceDetails()
    {

        $from = getParameter('from', 'str', false);
        $this->gofrom = $from;
        $id = getParameter('id', 'int');
        //是否是分享过来的
        if ($from != 'share') {
            if (!session('?teacher') && !session('?student') && !session('?parent')) {
                redirect(U('Index/index'));
            }
            A('Home/Common')->authJudgement();
        } else {
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) {
                redirect('/ApiInterface/Version1_1/KnowledgeResource/resourceDetails?flag=1&id=' . $id);
            }

            /* if(!session('?teacher') && !session('?student') && !session('?parent')){
                 $url = base64_encode("index.php?m=Home&c=BjResource&a=bjResourceDetails&id=$id");
                 redirect(U('Index/index').'&url='.$url);
             }*/

        }
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');

        A('Home/Common')->getUserIdRole($userId, $role);

        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $source_arr = C('KNOWLEDGE_RESOURCE_SOURCE');
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');

        //添加浏览量操作
        $this->model->updateResourceFollowNum($id);

        $result = $this->model->getResourceDetailInfoWithoutMulti($id, $role, $userId);
        if (!($result[0]['putaway_status'] == PUTAWAY && $result[0]['status'] == APPROVE))
            exit;
        $contact_data = array();
        $recommend_data = array();

        if (!empty($result)) {
            $is_allowed_browse = A('Home/Common')->returnStatus($id, $userId, $role); //调公共的方法判断状态
            $contact_data = $this->model->getResourceContactFiles($id);
            $recommend_data = $this->model->getResourceContactRecommend($id);         //echo "<pre>";print_r($contact_data);die;
        }

        /*
        *所属年级分册章节信息去重处理
        */
        foreach ($result as $item) {
            if (!empty($item['festival'])) {
                $temp_arr[] = $item['grade'] . '-' . $item['term'] . '-' . $item['chapter'] . '-' . $item['festival'];
            } else {
                $temp_arr[] = $item['grade'] . '-' . $item['term'] . '-' . $item['chapter'];
            }

        }

        if ($result[0]['resource_type'] != 1) //BOOK 万邦
        {
            $url = $contact_data[0]['resource_path'];
            if(strtolower($result[0]['file_type']) === 'html'){
                echo "<style>*{padding:0;margin:0}</style><iframe frameborder=\"0\" border=\"0\" marginwidth=\"0\" marginheight=\"0\" style=\"height:100%;width:100%\" src=\"$url\" ></iframe>";
                exit;
            }
            if (false === strpos($contact_data[0]['resource_path'], 'http://'))
                $url = 'http://' . $url;
            header('Location:' . $url);
            return;
        }
        //TODO:添加查询当前用户是否为团体VIP
//        $auth_type_use = D('Account_auths');
//        $whereData['user_id'] = $userId;
//        $whereData['role_id'] = $role;
//        $whereData['auth_id'] = 3;
//        $ownVip = $auth_type_use->getVipType($whereData);//团体VIP权限
        if($role == 2){
            $vip_level = session('teacher_vip.is_auth');
        }elseif($role == 3){
            $vip_level = session('student_vip.is_auth');
        }

        if($vip_level == 3){
            $this->assign('vipGroup', 3);
        }

        $this->assign('dataInfos', array_unique($temp_arr));
        $this->assign('is_allowed_browse', $is_allowed_browse);
        $this->assign('recommend_data', $recommend_data);
        $this->assign('source_arr', $source_arr);
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->assign('data', $result);
        $this->assign('contact_data', $contact_data);
        $this->assign('user_id', $userId);
        $subResourceIdList = json_decode(GUOXUE_SUBRESOURCE_IDLIST,true);
        if($id == GUOXUE_ID || in_array($id,$subResourceIdList)){
            if($id != GUOXUE_ID){
                //获取主套餐是否已经购买
                $result = $this->model->getResourceDetailInfoWithoutMulti(GUOXUE_ID, $role, $userId);
                $is_allowed_browse = A('Home/Common')->returnStatus(GUOXUE_ID, $userId, $role); //调公共的方法判断状态
                $this->assign('main_is_allowed_browse', $is_allowed_browse);
                $this->assign('main_data', $result);
            }
            else{
                $is_allowed_browse_array = [];
                foreach($subResourceIdList as $key=>$id){
                    $is_allowed_browse_array[] = A('Home/Common')->returnStatus($id, $userId, $role); //调公共的方法判断状态
                }
                $this->assign('is_allowed_browse_array', $is_allowed_browse_array);
            }
            $this->assign('subResourceIdList',$subResourceIdList);
            $this->display('bjResourceSinology');
        }
        else {
            $this->display();
        }

    }

    public function xiazaidemo()
    {
        $id = $_GET['id'];
        $contact_data = $this->model->getResourceContactFiles($id);
        if (!empty($contact_data)) {
            foreach ($contact_data as $k => $v) {
                $csv = new CSV();
                $csv->downloadMedia($v['vid_fullpath']);
            }
        }
    }

    //下载京版资源
    public function downloadBjResource()
    {
        //$id=intval(I('id'));
        //$url=I('url');
        $id = getParameter('id', 'int');
        $url = getParameter('url', 'str');

        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $model = M('knowledge_resource');
            $model->where('id=' . $id)->setInc('download_count');
            $csv = new CSV();
            $csv->downloadMedia($url);
        }
    }


    /*
      * 点赞或取消点赞
      */
    public function zanResource()
    {
        $id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId, $role);
        $ZanModel = M('biz_bj_resource_zan');
        $zanData['role'] = $role - ROLE_NUMBER;
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $userId;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $ZanModel->add($zanData);

            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('zan_count', 1);
            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and role=" . $zanData['role'] . " and user_id=$userId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('zan_count', 1);
            $this->ajaxReturn("已经取消点赞");
        }
    }


    /*
     * 收藏或取消收藏
     */
    public function collectResource()
    {
        $id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId, $role);
        $collectData['role'] = $role - ROLE_NUMBER;
        $collectData['resource_id'] = $id;
        $collectData['user_id'] = $userId;

        $existed = $this->model->getResource($id, $collectData['role'], $userId);
        if (empty($existed)) {
            $collectData['create_at'] = time();
            $this->model->addResourceCollect($collectData);

            $this->model->updateResourceCollectNum($id);
            $this->ajaxReturn("success");
        } else {
            $this->model->deleteResourceCollect($id, $collectData['role'], $userId);
            $this->model->updateResourceCollectNum($id, false);

            $this->ajaxReturn("已经取消收藏");
        }
    }

    /*
     *nobook万邦资源浏览量计数
     */
    public function browsCount()
    {
        $id = getParameter('id', 'int');
        $this->model->updateResourceFollowNum($id);
        $this->ajaxReturn("success");
    }

    private function modifyDocumentWeight($id,$weightArray= [])
    {
        $resourceModel = D('Knowledge_resource');

        //get weights mapping
        $json = ["weights" => $weightArray];
        //TODO:add version judgement, if the version of mysql data and es are different then update all document
        $modifyResult = $resourceModel->modifyDocument($id,$json );
        //增加不在索引里的文档
        if(DOCUMENT_NOT_EXISTS === $modifyResult)
        {
            if(false === $this->model->addDocument($id))
            {
                \Think\Log::write("设置 ES Document weight: 失败",C('LOG_PATH').'ES.ERR');
                return false;
            }
            $modifyResult = $resourceModel->modifyDocument($id,$json);
            if(false === $modifyResult)
                return false;
        }
        else if(false === $modifyResult)
            return false;
        return true;
    }

    private function bulkModifyDocumentWeight($data=[])
    {
        $resourceModel = D('Knowledge_resource');

        //get weights mapping
        //TODO:add version judgement, if the version of mysql data and es are different then update all document
        $modifyResult = $resourceModel->bulkModifyDocument($data);
        if($modifyResult['result'] === true){
            return true;
        }
        else{
            foreach($modifyResult['data']['missing'] as $key => $missingId){
                //增加不在索引里的文档
                if(false === $this->model->addDocument($missingId))
                {
                    \Think\Log::write("设置 ES Document weight: 失败",C('LOG_PATH').'ES.ERR');
                    return false;
                }

                $modifyResult = $resourceModel->modifyDocument($missingId,$data[$missingId]);
                if(false === $modifyResult)
                    return false;
            }
        }

        return true;
    }


    private function getCurrentSchoolTerm()
    {
        $currentMonth = date("m", time());
        $schoolTerm = ($currentMonth >= 3 && $currentMonth <= 8) ? 2 : 1;
        return $schoolTerm;
    }

    private function _getArraySize($array=[]){
        $firstId = array_keys($array)[0];
        return sizeof($array) * sizeof($array[$firstId]['weights']);
    }
    /**
     * @title: 全量更新排序权重
     */
    public function refreshRecommendResourcesOrder()
    {
        set_time_limit(0);
        $this->esAvailable = getESAvailable();

        $courseList = D('Dict_course')->getResourceCourseList();
        $courseList = array_merge(array(array('id' => '0')), $courseList);
        $gradeList = D('Dict_grade')->getGradeList();
        $gradeList = array_merge(array(array('id' => '0')), $gradeList);
        $check['id'] = array('in', array(1, 2, 3, 6));
        $columnList = D('Dict_column')->getResourceAll($check);
        $columnList['list'] = array_merge(array(array('id' => '0')), $columnList['list']);
        $resourceModel = D('Knowledge_resource');
        $schoolTerm = $this->getCurrentSchoolTerm();
        $maxBrowseCount = $resourceModel->getMaxBrowseCount();
        $weightArray = [];
        for ($k = 0; $k < sizeof($columnList['list']); $k++) {
            $columnId = $columnList['list'][$k]['id'];
            for ($i = 0; $i < sizeof($courseList); $i++) {
                $courseId = $courseList[$i]['id'];
                if ($maxBrowseCount <= 0)
                    continue;
                for ($j = 0; $j < sizeof($gradeList); $j++) {
                    $gradeId = $gradeList[$j]['id'];
                    //get specific resources
                    $sortIdList = $resourceModel->getRecommendSortResourceIdList($courseId, $gradeId, $schoolTerm, $columnId, $maxBrowseCount);
                    //save sort list to redis
                    if (sizeof($sortIdList) > 0) {

                        $redis_obj = new \Common\Common\REDIS();
                        $redis = $redis_obj->init_redis();
                        if (1001 != $redis && 1002 != $redis) {
                            $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-$columnId";
                            $redis->setex($redis_key, 86400, implode(',', array_column($sortIdList, 'id')));
                            $redis->close();
                        }

                        if(ES_AVAILABLE == $this->esAvailable)
                        {
                            if($courseId == 0 && $gradeId == 0 && $columnId ==0) // 全量更新--删除未上架文档
                            {
                                if (false === $resourceModel->deleteESDocument(array_column($sortIdList, 'id')))
                                {
                                    \Think\Log::write('删除 ES Document 失败',C('LOG_PATH').'ES.ERR');
                                    return;
                                }
                            }
                            if($columnId == 0) {
                                foreach ($sortIdList as $key => $value) {
                                    if(empty($weightArray[$value['id']]))
                                    {$weightArray[$value['id']] = [];
                                        $weightArray[$value['id']]['weights'] = [];
                                    }
                                    $weightArray[$value['id']]['weights'] = array_merge($weightArray[$value['id']]['weights'], ["w${courseId}_${gradeId}_${columnId}" => intval(10 * $value['weight'])]);
                                }
                            }

                        }
                    }
                }
                if(ES_AVAILABLE == $this->esAvailable && $this->_getArraySize($weightArray) > 100000) {
                    if (true === $this->bulkModifyDocumentWeight($weightArray)) {
                        $weightArray = [];
                    }
                }
            }
        }
        if(ES_AVAILABLE == $this->esAvailable ) {
            if (true === $this->bulkModifyDocumentWeight($weightArray)) {
                $weightArray = [];
            }
        }
        $this->updateKnowledgeTree();
    }

    /**
     * @title: 增量更新排序权重
     */


    public function refreshRecommendResourcesOrderIncrement($param = [])
    {
        set_time_limit(0);
        $this->esAvailable = getESAvailable();
        $courseList = D('Dict_course')->getResourceCourseList();
        $courseList = array_merge(array(array('id' => '0')), $courseList);
        $gradeList = D('Dict_grade')->getGradeList();
        $gradeList = array_merge(array(array('id' => '0')), $gradeList);
        $schoolTerm = $this->getCurrentSchoolTerm();
        $maxBrowseCount = $this->model->getMaxBrowseCount();

        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        $mutexLock = new RedisLock($redis);

        $additionalResources = !empty($param['add']) ? $param['add'] : getParameter('add', 'str', false); //
        if(!empty($additionalResources))
            $additionalResources =$this->model->getAdditionalResource($additionalResources);
        $removeResources = !empty($param['remove']) ? $param['remove'] : getParameter('remove', 'str', false); //
        if(!empty($removeResources))
            $removeResources = $this->model->getRemoveResource($removeResources);

        foreach ($additionalResources as $i => $val) {
            $id = $val['id'];
            $columnArray = array_merge(array(0), explode(',', $val['column_ids']));
            A('Exercise/Multimedia')->modifyBjVideoResourceExercise([$id]);
            if (ES_AVAILABLE == $this->esAvailable) {
                $this->model->addDocument($id);
            }
            $weightArray = [];
            foreach ($courseList as $null => $courseInfo)
                foreach ($gradeList as $null => $gradeInfo) {
                    $courseId = $courseInfo['id'];
                    $gradeId = $gradeInfo['id'];
                    foreach ($columnArray as $key => $columnId) {

                        $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-$columnId";

                        if (false !== $mutexLock->lock($_SERVER['SERVER_NAME'] . "_recommend_resource_order_refresh-$courseId-$gradeId-$columnId")) //lock is not timeout
                        {
                            $orgId = $redis->get($redis_key);
                            $orgIdArray = explode(',', $orgId);
                            if (!in_array($id, $orgIdArray)) {
                                if (empty($orgId))
                                    $newId = $id;
                                else
                                    $newId = $orgId . ",$id";
                                $redis->setex($redis_key, 86400, $newId);
                            }
                            $mutexLock->unlock($_SERVER['SERVER_NAME'] . "_recommend_resource_order_refresh-$courseId-$gradeId-$columnId");
                        }
                        if (ES_AVAILABLE == $this->esAvailable) {
                            $resourceWeightInfo = $this->model->getRecommendSortResourceIdList($courseId, $gradeId, $schoolTerm, $columnId, $maxBrowseCount, $id);
                            if($columnId == 0)
                                $weightArray = array_merge($weightArray,["w${courseId}_${gradeId}_${columnId}" => intval(10 * $resourceWeightInfo[0]['weight'])]);
                        }
                    }

                }
            $this->modifyDocumentWeight($id, $weightArray);
        }

        foreach ($removeResources as $i => $val) {
            $id = $val['id'];
            $columnArray = explode(',', $val['column_ids']);
            $columnArray[] = '0';
            A('Exercise/Multimedia')->modifyBjVideoResourceExercise([$id]);
            if (ES_AVAILABLE == $this->esAvailable) {
                $this->model->deleteDocument($id);
            }
            foreach ($courseList as $null => $courseInfo)
                foreach ($gradeList as $null => $gradeInfo) {
                    $courseId = $courseInfo['id'];
                    $gradeId = $gradeInfo['id'];
                    foreach ($columnArray as $key => $columnId) {
                        $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-$columnId";

                        if (false !== $mutexLock->lock("recommend_resource_order_refresh-$courseId-$gradeId-$columnId")) //lock timeout
                        {
                            $orgId = $redis->get($redis_key);
                            $orgIdArray = explode(',', $orgId);
                            $elementIndex = array_search($id, $orgIdArray);
                            if (false !== $elementIndex) {
                                array_splice($orgIdArray, $elementIndex, 1);
                                $newId = implode(',', $orgIdArray);
                                $redis->setex($redis_key, 86400, $newId);
                            }
                            $mutexLock->unlock("recommend_resource_order_refresh-$courseId-$gradeId-$columnId");
                        }

                    }

                }
        }
        if (1001 != $redis && 1002 != $redis)
            $redis->close();


        $this->updateKnowledgeTree();
        return true;
    }

    private function updateKnowledgeTree()
    {
        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if (1001 != $redis && 1002 != $redis) {
            $knowledge_resource_model = D('Knowledge_resource');
            $textbook_model = D('Biz_textbook');
            $publishingDate = $knowledge_resource_model->publishing(1);
            $this->assign('publishingDate', $publishingDate);
            $treeKey = $_SERVER['SERVER_NAME'] . ':knowledge_tree';
            $knowledge_tree = $knowledge_resource_model->getCourseByPublishing2($publishingDate['publishing_house_id']);
            foreach ($knowledge_tree as $key => $course_value) {
                $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse2($course_value['id']);
                foreach ($knowledge_tree[$key]['child'] as $k => $textbook) {
                    $knowledge_tree[$key]['child'][$k]['chapter'] = $textbook_model->getChapterByTextbook($textbook['id']);
                }
            }
            $redis->setex($treeKey, 86400, (json_encode($knowledge_tree)));
            $redis->close();
        }
    }

    public function getTreeCourseTextbook()
    {
        //获取教材版本
        $textbook_model = D('Biz_textbook');
        $knowledge_resource_model = D('Knowledge_resource');
        $publishingDate = $knowledge_resource_model->publishing(1);
        $this->assign('publishingDate', $publishingDate);
        $knowledge_tree = $knowledge_resource_model->getCourseByPublishing2($publishingDate['publishing_house_id']);
        foreach ($knowledge_tree as $key => $course_value) {
            $knowledge_tree[$key]['child'] = $textbook_model->getTextbookByCourse2($course_value['id']);

            foreach ($knowledge_tree[$key]['child'] as $k => $textbook) {
                $knowledge_tree[$key]['child'][$k]['chapter'] = $textbook_model->getChapterByTextbook($textbook['id']);
            }
        }
        $this->showjson(200, $knowledge_tree);
    }
}
