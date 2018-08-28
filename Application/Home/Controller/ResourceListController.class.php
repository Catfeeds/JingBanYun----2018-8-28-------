<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;

define('NEW_SHARE', 6);
define('NEW_SHARE_ROWS', 12);
define('HOT_READ_SHARE', 7);
define('HOT_READ_ROWS', 12);

class ResourceListController extends PublicController
{
    public $model = '';
    public $c_a = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Biz_resource');
        $this->assign('oss_path', C('oss_path'));
        $this->c_a = CONTROLLER_NAME . "_" . ACTION_NAME;

    }


    //对资源类型进行排序
    function fileTypeOrder($arr)
    {
        $type_file = array(
            'video', 'audio', 'image', 'word', 'ppt', 'pdf', 'swf', 'condensed'
        );
        $data = array();
        for ($i = 0; $i < count($type_file); $i++) {
            for ($j = 0; $j < count($arr); $j++) {
                if ($arr[$j]['file_type'] == $type_file[$i]) {
                    $data[] = $arr[$j];
                    break;
                }
            }
        }
        return $data;
    }

    public function resourceIndex()
    {
        A('Home/Common')->getUserIdRole($userId, $role);
        A('Home/Common')->authJudgement();
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
        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jiaoshiziyuan');
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');


        $limit_rows = '15';
        $new_share_data = $this->model->getColumnResource('create_at', $limit_rows);
        $hot_data = $this->model->getColumnResource('follow_count', $limit_rows);

        $resource_file_type = C('RESOURCE_UPLOAD_FILE_TYPE');
        $grade_result = $grade_model->getGradeList();
        $course_list = $course_model->getCourseList();

        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_list);
        $this->assign('resource_file_type', $resource_file_type);
        $this->assign('role', $role);
        //$this->assign('NEW_SHARE',NEW_SHARE);
        //$this->assign('HOT_READ_SHARE',HOT_READ_SHARE);
        $this->assign('new_share_data', $new_share_data);    //var_dump($new_share_data);die;
        $this->assign('hot_data', $hot_data);
        $this->display_nocache();
    }

    public function resourceList()
    {
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
        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jiaoshiziyuan');

        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $textbook_model = D('Biz_textbook');
        $resource_file_type = C('RESOURCE_UPLOAD_FILE_TYPE');

        $keyword = trim(getParameter('keyword', 'str', false));
        $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
        $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
        $filter['type'] = getParameter('file_type', 'str', false);
        $filter['course'] = getParameter('course', 'int', false);
        $filter['grade'] = getParameter('grade', 'int', false);
        //       $filter['textbook'] =getParameter('textbook', 'int',false);
        $filter['s_time'] = getParameter('s_time', 'str', false);
        $filter['e_time'] = getParameter('e_time', 'str', false);
        $author = trim(getParameter('author', 'str', false));//这里是发布人
        $filter['author'] = preg_replace('/\s+/', ' ', $author);
        $filter['author'] = preg_replace('/\%+/', '\%', $filter['author']);
        $filter['district'] = getParameter('district', 'int', false);
        $filter['school_term'] = getParameter('school_term', 'int', false);//上册下册
        $order = getParameter('order', 'str', false);
        $order = $order ? $order : 'browse';


        $filter_arr['create_start_time'] = $filter['s_time'];
        $filter_arr['create_end_time'] = $filter['e_time'];

        $check['biz_resource.status'] = 2;

        if (!empty($keyword)) {
            $temp_arr = explode(' ', $filter['keyword']);
            foreach ($temp_arr as $item) {
                $check['biz_resource.resource_info'][] = array("like", "%" . $item . "%");
            }

        }
        if (!empty($author)) {
            $temp_arr = explode(' ', $filter['author']);
            foreach ($temp_arr as $item) {
                $check['auth_teacher.name'][] = array("like", "%" . $item . "%");
            }

        }
        if (!empty($filter['type'])) $check['biz_resource.type'] = $filter['type'];
        if (!empty($filter['course'])) $check['biz_resource.course_id'] = $filter['course'];
        if (!empty($filter['grade'])) $check['biz_resource.grade_id'] = $filter['grade'];
//        if (!empty($filter['textbook'])) $check['biz_textbook.id'] = $filter['textbook'];
        if (!empty($filter['district'])) $check['dict_schoollist.district_id'] = $filter['district'];
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];

        if ($filter_arr['create_start_time'] != '' && $filter_arr['create_end_time'] != '') {
            if (date('Y-m-d', strtotime($filter_arr['create_start_time'])) == $filter_arr['create_start_time'] && date('Y-m-d', strtotime($filter_arr['create_end_time'])) == $filter_arr['create_end_time']) {
                $check['_string'] = 'biz_resource.create_at>=' . strtotime($filter_arr['create_start_time']) . ' and biz_resource.create_at<=' . (strtotime($filter_arr['create_end_time']) + 86399);
            } else {
                unset($filter_arr['create_start_time']);
                unset($filter_arr['create_end_time']);
            }
        } elseif (!empty($filter_arr['create_start_time'])) {
            if (date('Y-m-d', strtotime($filter_arr['create_start_time'])) == $filter_arr['create_start_time']) {
                $check['_string'] = 'biz_resource.create_at>=' . strtotime($filter_arr['create_start_time']);
            } else {
                unset($filter_arr['create_start_time']);
            }
        } elseif (!empty($filter_arr['create_end_time'])) {
            if (date('Y-m-d', strtotime($filter_arr['create_end_time'])) == $filter_arr['create_end_time']) {
                $check['_string'] = 'biz_resource.create_at<=' . (strtotime($filter_arr['create_end_time']) + 86399);
            } else {
                unset($filter_arr['create_end_time']);
            }
        }
        //地区
        $where['biz_resource.status'] = 2;
        $district_result = $this->model->getDistrict($where);
        //学科
        $course_list2 = $course_model->getCourseListByReverseQuery($userId, $role);
        $this->assign('course_con', $course_list2);
        //年级
        $grade_result = $grade_model->getGradeList();
        $this->assign('grade_list', $grade_result);
        //资源格式
        $bj_resource_file_type = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        unset($bj_resource_file_type['HTML']);
        $this->assign('bj_resource_file_type', $bj_resource_file_type);
        $this->ajaxScreening($check, 1);
        $grade_result = $grade_model->getGradeList();//高级筛选中年级的内容
        $course_list2 = $course_model->getCourseList();//高级筛选中的学科内容
        $this->assign('district_con', $district_result);
        $this->assign('grade_list', $grade_result);//高级筛选中年级的内容
        $this->assign('course_list', $course_list2);//高级筛选中的学科内容
        $this->assign('order', $order);
        $this->assign('role', $role);
        $this->assign('s_time', $filter['s_time']);
        $this->assign('e_time', $filter['e_time']);
        $this->assign('author', $filter['author']);
        $this->assign('district', $filter['district']);
        $this->assign('type', $filter['type']);
        $this->assign('keyword', $keyword);
        $this->assign('school_term', $filter['school_term']);
        $this->assign('course', $filter['course']);
        $this->assign('grade', $filter['grade']);
        $this->display_nocache();
    }


    /*
     *搜索结果页Ajaax筛选请求
     */
    public function ajaxScreening($check = '', $status = '') //这两个参数用于搜索和高级检索或者带知识树的搜索结果页
    {
        //接到的值是数组
        $temp_arr = I('temp_arr');
        //带知识树的搜索结果页传过来的要渲染页面标识
        $other = I('other');
        $temp_arr_temp = I('temp_arr_temp');
        //var_dump($temp_arr);var_dump($temp_arr_temp);die;
        //1、遍历数组2、拆分3、拼接Where条件
        if (!empty($temp_arr)) {
            foreach ($temp_arr as $item_temp) {
                $temp = explode('_', $item_temp);
                if ($temp[0] == 'course') {
                    $temp_course[] = $temp[1];
                    $check['biz_resource.course_id'] = array('IN', $temp_course);
                } elseif ($temp[0] == 'grade') {
                    $temp_grade[] = $temp[1];
                    $check['biz_resource.grade_id'] = array('IN', $temp_grade);
                } elseif ($temp[0] == 'textbook') {
                    $temp_textbook[] = $temp[1];
                    $check['biz_textbook.school_term'] = array('IN', $temp_textbook);
                } elseif ($temp[0] == 'file') {
                    $temp_file[] = $temp[1];
                    $check['biz_resource.type'] = array('IN', $temp_file);
                } elseif ($temp[0] == 'district') {
                    $temp_district[] = $temp[1];
                    $check['dict_schoollist.district_id'] = array('IN', $temp_district);
                } elseif ($temp[0] == 'desc') {
                    if ($temp[1] == 'desc') {
                        $order = 'desc';
                    } else {
                        $order = '';
                    }
                }
            }
        }
        if (!empty($temp_arr_temp)) {
            foreach ($temp_arr_temp as $temp_temp) {
                $info = explode('_', $temp_temp);
                if ($info[0] == 'course') {
                    $check['biz_resource.course_id'] = $info[1];
                } elseif ($info[0] == 'textbook') {
                    $check['biz_resource.textbook_id'] = $info[1];
                } elseif ($info[0] == 'stime') {
                    $arr['stime'] = $info[1];
                } elseif ($info[0] == 'etime') {
                    $arr['etime'] = $info[1];
                } elseif ($info[0] == 'keyword') {
                    $info[1] = preg_replace('/\s+/', ' ', $info[1]);
                    $info[1] = preg_replace('/\%+/', '\%', $info[1]);
                    $temp_arr = explode(' ', $info[1]);
                    foreach ($temp_arr as $item) {
                        $check['biz_resource.resource_info'][] = array("like", "%" . $item . "%");
                    }
                } elseif ($info[0] == 'author') {
                    $info[1] = preg_replace('/\s+/', ' ', $info[1]);
                    $info[1] = preg_replace('/\%+/', '\%', $info[1]);
                    $temp_arr = explode(' ', $info[1]);
                    foreach ($temp_arr as $item) {
                        $check['auth_teacher.name'][] = array("like", "%" . $item . "%");
                    }
                }
            }
            if ($arr['stime'] != '' && $arr['etime'] != '') {
                if (date('Y-m-d', strtotime($arr['stime'])) == $arr['stime'] && date('Y-m-d', strtotime($arr['etime'])) == $arr['etime']) {
                    $check['_string'] = 'biz_resource.create_at>=' . strtotime($arr['stime']) . ' and biz_resource.create_at<=' . strtotime($arr['etime']);
                } else {
                    unset($arr['stime']);
                    unset($arr['etime']);
                }
            } elseif (!empty($arr['stime'])) {
                if (date('Y-m-d', strtotime($arr['stime'])) == $arr['stime']) {
                    $check['_string'] = 'biz_resource.create_at>=' . strtotime($arr['stime']);
                } else {
                    unset($arr['stime']);
                }
            } elseif (!empty($arr['etime'])) {
                if (date('Y-m-d', strtotime($arr['etime'])) == $arr['etime']) {
                    $check['_string'] = 'biz_resource.create_at<=' . strtotime($arr['etime']);
                } else {
                    unset($arr['etime']);
                }
            }
        }
        //查询操作
        A('Home/Common')->getUserIdRole($userId, $role);

        $result = $this->model->getResourceData($check, $order, $userId, $role);
        //往页面赋值，用于筛选项复选框的选中状态（赋的值是数组）
        $this->assign('list', $result['data']);
        $this->assign('page', $result['page']);
        $this->assign('count', $result['count']);
        $this->assign('keyword', getParameter('keyword', 'str', false));
        if ($status == 1) {

        } else {
            $this->display('resourceListIframe');
        }

    }

    /*
    * 拼成某个url
    */
    public function joinUrl($array, $key)
    {
        $condition_str = '';
        unset($array[$key]);
        foreach ($array as $key => $val) {
            $condition_str .= '&' . $key . '=' . $val;
        }
        return $condition_str;
    }


    //资源详情
    public function resourceDetails($id = array(), $from = "", $type = 2)
    {
        $from = getParameter('from', 'str', false);
//echo CONTROLLER_NAME.'_'.ACTION_NAME;die;
        if ($from != 'share') {
            if (!session('?teacher') && !session('?student') && !session('?parent')) {
                redirect(U('Index/index'));
            }
            A('Home/Common')->authJudgement();
        }

        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                $USER_ID = session('teacher.id');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                $USER_ID = session('student.id');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                $USER_ID = session('parent.id');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }
        $this->assign('module', '教学+');
        $this->assign('nav', '');
        $this->assign('navicon', 'jiaoshiziyuan');

        $id = intval($id);
        if (!empty($id)) {
            $id = getParameter('id', 'int', false);
            if (!$id) {
                redirect(U('Index/systemError'));
            }
            $teacher_online = 1;
        } else {
            redirect(U('Index/systemError'));
        }

        $recommend_data = $this->model->getRecommendData($id);

        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);
        if (!empty($_GET['f']))
            $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id', 'left')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
            ->where("biz_resource.id=" . $id)
            ->find();

        if (!empty($result)) {
            $this->assign('subnav', $result['name']);
            $this->assign('data', $result);
            $result['type'] = strtolower($result['type']);
            //拿到关联表的数据
            $contact_result = $Model->where("biz_resource.id=" . $id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
                ->field("biz_resource_contact.*")->select();
            $this->assign('contact_data', $contact_result);    //var_dump($result);  echo "<pre>"; var_dump($contact_result);die;

            //观看次数+1
            $Model->where("id=$id")->setInc('follow_count', 1);
            //$User = M("auth_teacher");
            //$User->where("id=" . $result['teacher_id'])->setInc("points", 1);// 积分加1 
            if ($role == ROLE_TEACHER) {
                //判断登陆者是否和发布者是一人
                if ($result['teacher_id'] == session('teacher.id')) {
                    $this->assign('operation_status', 1);
                } else {
                    $this->assign('operation_status', 2);
                }
            }
            $this->assign('role', $role);

            //判断我是否赞过和收藏过
            $ZanModel = M('biz_resource_zan');
            $zanData['resource_id'] = $id;
            $zanData['user_type'] = $role - 2;
            $zanData['user_id'] = $USER_ID;
            $existedZan = $ZanModel->where($zanData)->find();
            $existedZan = empty($existedZan) ? 'no' : 'yes';
            $this->assign('existedZan', $existedZan);


            $FavorModel = M('biz_resource_collect');
            $favorData['resource_id'] = $id;
            $favorData['user_type'] = $role - 2;
            $favorData['user_id'] = $USER_ID;
            $existedFavor = $FavorModel->where($favorData)->find();
            $existedFavor = empty($existedFavor) ? 'no' : 'yes';
            $this->assign('existedFavor', $existedFavor);
        }
        $this->assign('recommend_data', $recommend_data);
        $this->assign('user_id', $USER_ID);

        $this->display();
    }


    //下载资源
    public function downloadResource()
    {
        //$id=intval(I('id'));
        //$url=I('url');
        $id = getParameter('id', 'int');
        $url = getParameter('url', 'str');

        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $model = M('biz_resource');
            $model->where('id=' . $id)->setInc('download_count');
            $csv = new CSV();
            $csv->downloadMedia($url);
        }
    }


}