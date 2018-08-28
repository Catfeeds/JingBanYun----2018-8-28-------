<?php
namespace ApiInterface\Controller\Version1_3;
use Common\Common\JSSDK;
define('SPECIAL_COLUMN', 1);
define('QUESTION', 2);
define('REPLY', 3);
define('DELETE_TRUE', 2);
define('DELETE_FALSE', 1);
define('UP', 1);
define('DOWN', 2);
define('WAITUP',3);
define('WAIT', 3);
define('YES', 1);
define('NO', 2);
define('AUDIO', 3);
define('VIDIO', 1);
define('ARTICLE', 2);

class DirectCarController extends PublicController{
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
    public static $orderModel = '';

    public function __construct(){
        parent::__construct();
        $this->model = D('Direct_train');
        $this->assign('oss_path', C('oss_path'));
        self::$orderModel = D('Order_info');
    }
    //获取全部信息
    public function getAllList() {
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);

        $page=I('pageIndex')<1?1:I('pageIndex');
        $field = "direct_train.putaway_status_time,direct_train.fascicule_id,direct_train.type,dict_course.id as cid,discussion_count,special_column_question_visit_count,auth_teacher.avatar,direct_train.special_column_price,direct_train.special_column_type,direct_train.id,direct_train.special_column_question_title,course_name,grade,name,direct_train.special_column_question_visit_count,direct_train.discussion_count,direct_train.creat_time";

        $is_question = getParameter('is_question_type','int',false);

        if (!empty($is_question)) {
            if ($is_question==1) { //专栏
                $where['direct_train.type'] = 1;
            } else {
                $where['direct_train.type'] = 2;
            }
        }
        $where['direct_train.status'] = 1;
        $where['direct_train.delete_status'] = 1;
        $where['direct_train.putaway_status'] = 1;

        $title = getParameter('search_filed','str',false);
        if(!empty($title)) {
            //$where['direct_train.search_filed'] =  array('like', '%' . $title . '%');
            //$where['direct_train.search_filed'] = array(array('like', '%' . $title . '%'),'_logic');
            $where['direct_train.search_filed|direct_train.special_column_question_reply_description'] = array('like', '%' . $title . '%');
        }
        $where['ppid'] = 0;
        $map =$where;
        $list = D('Direct_train')->getModelAllList($where,$field,$page,$pageSize=20,$map,'',"putaway_status_time DESC");

        foreach ($list as $k=>$v) {

            if(!empty($v['id'])&& !empty($role) && !empty($userId)) {
                $mapwhere['resources_id'] = $v['id'];
                $mapwhere['user_role'] = $role;
                $mapwhere['user_id'] = $userId;
                $mapwhere['order_type'] = 1;
                $mapwhere['order_status'] = 2;

                $orderD = M('order_info')->where($mapwhere)->find();
                if (!empty($orderD)) {
                    $list[$k]['isPay'] = 1;
                } else {
                    $list[$k]['isPay'] = 0;
                }
            }

            switch ($v['fascicule_id']) {
                case 1:
                    $list[$k]['fascicule_id'] = "上册";
                    break;
                case 2:
                    $list[$k]['fascicule_id'] = "下册";
                    break;
                case 3:
                    $list[$k]['fascicule_id'] = "全一册";
                    break;
            }
            $list[$k]['creat_time'] = date("Y年m月d日",strtotime($v['putaway_status_time']));

            if (empty($v['avatar'])) {
                if ($v['sex']=="男") {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
                } else {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
                }

            } else {
                $list[$k]['avatar'] = C('oss_path').$v['avatar'];
            }

            switch ($v['special_column_type']) {
                case 1:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/shipin@2x.png";
                    break;
                case 2:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/wenzhang@2x.png";
                    break;
                case 3:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/yinpin@2x.png";
                    break;
            }

            switch ($v['cid']) {
                case 1:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                    break;
                case 2:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                    break;
                case 3:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                    break;
                case 4:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                    break;
                case 5:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                    break;
                case 6:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                    break;
                case 7:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                    break;
                case 8:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                    break;
                case 9:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                    break;
                case 11:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                    break;
                case 12:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                    break;
                case 17:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                    break;
                case 31:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                    break;
                case 37:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                    break;
            }

            if ($v['type'] == 1) {
                $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id'];
            }else {
                $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/questionDetailsView?id=".$v['id'];
            }

            if (empty($v['grade'])) {
                $list[$k]['grade'] = "";
            }
            if (empty($v['fascicule_id'])) {
                $list[$k]['fascicule_id'] = "";
            }

            unset($list[$k]['sex']);
            unset($list[$k]['cid']);
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));

    }

    //获取标签
    public function getTags() {
        $where['delete_status'] = DELETE_FALSE;
        $tags = D('Question_tags')->getQuestionTagsOne($where);

        foreach ($tags as $k=>&$v) {
            $v['name'] = $v['tags_name'];
            unset($v['tags_name']);
            unset($v['creat_time']);
            unset($v['update_time']);
            unset($v['delete_status']);
        }
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $tags));
    }

    //获取历史问答
    public function getHistoryQuestions() {
        $more = getParameter('more','int',false);
        $special_column_editor_quizzer_id = getParameter('special_column_editor_quizzer_id','int',false);
        $answered = getParameter('answered','int',false);

        $title = getParameter('search_filed','str',false);
        if(!empty($title)) {
            //$where['direct_train.search_filed'] =  array('like', '%' . $title . '%');
            //$where['direct_train.search_filed'] = array(array('like', '%' . $title . '%'),'_logic');
            $where['direct_train.search_filed|direct_train.special_column_question_reply_description'] = array('like', '%' . $title . '%');
        }

        $page=I('pageIndex')<1?1:I('pageIndex');
        $field = "dict_course.id as cid,direct_train.fascicule_id,direct_train.id,special_column_question_title,course_name,grade,name,special_column_question_visit_count,discussion_count,creat_time";

        $where['direct_train.type'] = array('eq',2);
        $where['direct_train.status'] = array('eq',1);
        $where['direct_train.delete_status'] = 1;
        $where['direct_train.putaway_status'] = 1;
        $where['direct_train.pid'] = 0;

        $title = getParameter('title','str',false);
        if(!empty($title)) {
            $where['direct_train.special_column_question_title'] =  array('like', '%' . $title . '%');
        }
        if(!empty($special_column_editor_quizzer_id)) { //我提的所有问题
            $where['direct_train.special_column_editor_quizzer_id'] =  $special_column_editor_quizzer_id;
        }

        if ($more == "more") { //更多的问答 20条的分页
            if(!empty($answered) && $answered==1 ) { //是否作答
                $where['direct_train.discussion_count'] =  array('eq',0);
            }
            if(!empty($answered) && $answered==2 ) {
                $where['direct_train.discussion_count'] =  array('neq',0);
            }
            $list = D('Direct_train')->getSpecialColumnAll($where,$field,$page, 20, $join = '', $order = 'sort_by_putaway_status_time desc,direct_train.putaway_status_time desc');
        } else { //7条的问答
            $list = D('Direct_train')->getSpecialColumnPageList($where, $field, $page, 6, $order = 'sort_by_putaway_status_time desc,direct_train.putaway_status_time desc');
        }

        foreach ($list as $k=>&$value) {
            $value['creat_time'] = date("Y-m-d",strtotime($value['creat_time']));

            switch ($value['fascicule_id']) {
                case 1:
                    $value['fascicule_id'] = "上册";
                    break;
                case 2:
                    $value['fascicule_id'] = "下册";
                    break;
                case 3:
                    $value['fascicule_id'] = "全一册";
                    break;
            }

            if ($more=="more") {
                switch ($value['cid']) {
                    case 1:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                        break;
                    case 2:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                        break;
                    case 3:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                        break;
                    case 4:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                        break;
                    case 5:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                        break;
                    case 6:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                        break;
                    case 7:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                        break;
                    case 8:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                        break;
                    case 9:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                        break;
                    case 11:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                        break;
                    case 12:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                        break;
                    case 17:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                        break;
                    case 31:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                        break;
                    case 37:
                        $value['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                        break;
                }
            }


            $value['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/questionDetailsView?id=".$value['id'];
            $value['type'] = 2;
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }

    public function getSerachWhere() {

        $course_list = $this->getCourseId();

        $list = M('dict_course')->field("id,course_name as name")->select();

        $flag = getParameter('flag','int',false);

        if(empty($flag)) {
            $allList = array(array('id'=>-1,'name'=>'全部'));
            foreach ($course_list as $k=>$v) {
                $allList[$k+1] = $v;
            }
        } else{
            //$allList = array(array('id'=>-1,'name'=>'全部'));
            $allList=[];
            foreach ($list as $k=>$v) {
                unset($v['parent_id']);
            }
            $allList = $list;
        }


        $list = M('dict_grade')->field("id,grade as name")->select();

        if (empty($flag)) {
            $copyallList = array(array('id'=>-1,'name'=>'全部'));
            foreach ($list as $k=>$v) {
                $copyallList[$k+1] = $v;
            }
        } else {
            $copyallList = $list;
        }

        if (!empty($flag)) {
            $fascicule = array(
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
            );
        } else {
            $fascicule = array(
                array(
                    'id' => -1,
                    'name' => '全部',
                ),
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
            );
        }

        $new = array(
            array(
                'id' => -1,
                'name' => '最新',
            ),
            array(
                'id' => 2,
                'name' => '人气正序',
            ),
            array(
                'id' => 3,
                'name' => '人气倒序',
            ),
            array(
                'id' => 4,
                'name' => '价格正序',
            ),
            array(
                'id' => 5,
                'name' => '价格倒序',
            ),
        );

        $data['course_list'] = $allList;
        $data['grade_list'] = $copyallList;
        $data['fascicule_list'] = $fascicule;
        $data['new_list'] = $new;

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $data));

    }

    //获取专栏
    public function getSpecialColumn(){

        $grade_id = getParameter('grade_id','int',false);
        $course_id = getParameter('course_id','int',false);
        $fascicule_id = getParameter('fascicule_id','int',false);
        $order = getParameter('order','str',false);

        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);

        $title = getParameter('search_filed','str',false);
        if(!empty($title)) {
            //$where['direct_train.search_filed'] =  array('like', '%' . $title . '%');
            //$where['direct_train.search_filed'] = array(array('like', '%' . $title . '%'),'_logic');
            $where['direct_train.search_filed|direct_train.special_column_question_reply_description'] = array('like', '%' . $title . '%');
        }


        $page=I('pageIndex')<1?1:I('pageIndex');
        $field = "direct_train.putaway_status_time,direct_train.type,dict_course.id as cid,auth_teacher.sex,direct_train.id,fascicule_id,special_column_question_title,course_name,grade,name,special_column_question_visit_count,discussion_count,creat_time,special_column_price,special_column_type,avatar";


        $where['direct_train.type'] = array('eq',1);
        $where['direct_train.putaway_status'] = 1;
        $where['direct_train.status'] = array('eq',1);
        $where['direct_train.delete_status'] = 1;

        if (!empty($grade_id)&& $grade_id != -1) {
            $where['direct_train.grade_id'] = $grade_id;
        }
        if (!empty($course_id)  && $course_id != -1 ) {
            $where['direct_train.course_id'] = $course_id;
        }
        if (!empty($fascicule_id) && $fascicule_id != -1) {
            $where['direct_train.fascicule_id'] = $fascicule_id;
        }

        switch( $order ) {
            case -1 :
                $order = "direct_train.putaway_status_time desc";
                break;
            case 2 :
                $order = "direct_train.special_column_question_visit_count asc";
                break;
            case 3 :
                $order = "direct_train.special_column_question_visit_count desc";
                break;
            case 4 :
                $order = "direct_train.special_column_price asc";
                break;
            case 5 :
                $order = "direct_train.special_column_price desc";
                break;
            default:
                $order = 'dict_grade.code,direct_train.fascicule_id,dict_course.sort_order,putaway_status_time desc,direct_train.special_column_question_visit_count desc';
        }

        $list = D('Direct_train')->getSpecialColumnAll($where,$field,$page,20,'',$order);
        foreach ($list as $k=>$v) {

            if(!empty($v['id'])&& !empty($role) && !empty($userId)) {

                $mapwhere['resources_id'] = $v['id'];
                $mapwhere['user_role'] = $role;
                $mapwhere['user_id'] = $userId;
                $mapwhere['order_type'] = 1;
                $mapwhere['order_status'] = 2;

                $orderD = M('order_info')->where($mapwhere)->find();
                if (!empty($orderD)) {
                    $list[$k]['isPay'] = 1;
                } else {
                    $list[$k]['isPay'] = 0;
                }
            }



            switch ($v['fascicule_id']) {
                case 1:
                    $list[$k]['fascicule_id'] = "上册";
                    break;
                case 2:
                    $list[$k]['fascicule_id'] = "下册";
                    break;
                case 3:
                    $list[$k]['fascicule_id'] = "全一册";
                    break;
            }
            $list[$k]['creat_time'] = date("Y年m月d日",strtotime($v['putaway_status_time']));

            if (empty($v['avatar'])) {
                if ($v['sex']=="男") {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
                } else {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
                }

            } else {
                $list[$k]['avatar'] = C('oss_path').$v['avatar'];
            }



            switch ($v['special_column_type']) {
                case 1:
                    $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id']."&type=".urlencode("视频");
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/shipin@2x.png";
                    break;
                case 2:
                    $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id']."&type=".urlencode("文章");
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/wenzhang@2x.png";
                    break;
                case 3:
                    $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id']."&type=".urlencode("音频");
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/yinpin@2x.png";
                    break;
            }

            switch ($v['cid']) {
                case 1:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                    break;
                case 2:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                    break;
                case 3:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                    break;
                case 4:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                    break;
                case 5:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                    break;
                case 6:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                    break;
                case 7:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                    break;
                case 8:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                    break;
                case 9:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                    break;
                case 11:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                    break;
                case 12:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                    break;
                case 17:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                    break;
                case 31:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                    break;
                case 37:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                    break;
            }

            /* if ($v['type'] == 1) {
                 $list[$k]['url'] = "http://".WEB_URL."ApiInterface/Version1_3/DirectCar/getDetails?id=".$v['id'];
             }else {
                 $list[$k]['url'] = "http://".WEB_URL."ApiInterface/Version1_3/DirectCar/questionDetails?id=".$v['id'];
             }*/


            if (empty($v['grade'])) {
                $list[$k]['grade'] = "";
            }
            if (empty($v['fascicule_id'])) {
                $list[$k]['fascicule_id'] = "";
            }
            unset($list[$k]['sex']);
            unset($list[$k]['cid']);
        }
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }

    //获取我已购专栏
    public function getPurchaseSpecialColumn(){
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $title = getParameter('search_filed','str',false);
        if (!empty($title)) {
            $where['direct_train.search_filed'] =  array('like', '%' . $title . '%');
        }
        $page=I('pageIndex')<1?1:I('pageIndex');


        $where['order_info.user_id'] = $userId;
        $where['order_info.user_role'] = $role;
        $where['order_info.order_type'] = 1;
        $where['order_info.order_status'] = 2;

        $field = "direct_train.type,dict_course.id as cid,auth_teacher.sex,direct_train.id,fascicule_id,special_column_question_title,course_name,grade,name,special_column_question_visit_count,discussion_count,creat_time,special_column_price,special_column_type,avatar";
        $list = D('Direct_train')->getPurchaseSpecialColumnAll($where,$field,$page);

        /*     foreach ($list as $k=>&$value) {
                 $value['creat_time'] = date("Y-m-d",strtotime($value['creat_time']));
             }*/

        foreach ($list as $k=>$v) {

            if(!empty($v['id'])&& !empty($role) && !empty($userId)) {

                $mapwhere['resources_id'] = $v['id'];
                $mapwhere['user_role'] = $role;
                $mapwhere['user_id'] = $userId;
                $mapwhere['order_type'] = 1;
                $mapwhere['order_status'] = 2;

                $orderD = M('order_info')->where($mapwhere)->find();
                if (!empty($orderD)) {
                    $list[$k]['isPay'] = 1;
                } else {
                    $list[$k]['isPay'] = 0;
                }
            }

            switch ($v['fascicule_id']) {
                case 1:
                    $list[$k]['fascicule_id'] = "上册";
                    break;
                case 2:
                    $list[$k]['fascicule_id'] = "下册";
                    break;
                case 3:
                    $list[$k]['fascicule_id'] = "全一册";
                    break;
                default:
                    $list[$k]['fascicule_id'] = "";

            }

            if (empty($v['grade'])) {
                $list[$k]['grade'] = "";
            }

            $list[$k]['creat_time'] = date("Y年m月d日",strtotime($v['creat_time']));

            if (empty($v['avatar'])) {
                if ($v['sex']=="男") {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
                } else {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
                }

            } else {
                $list[$k]['avatar'] = C('oss_path').$v['avatar'];
            }

            switch ($v['special_column_type']) {
                case 1:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/shipin@2x.png";
                    break;
                case 2:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/wenzhang@2x.png";
                    break;
                case 3:
                    $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/yinpin@2x.png";
                    break;
            }

            switch ($v['cid']) {
                case 1:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                    break;
                case 2:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                    break;
                case 3:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                    break;
                case 4:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                    break;
                case 5:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                    break;
                case 6:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                    break;
                case 7:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                    break;
                case 8:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                    break;
                case 9:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                    break;
                case 11:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                    break;
                case 12:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                    break;
                case 17:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                    break;
                case 31:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                    break;
                case 37:
                    $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                    break;
            }

            /* if ($v['type'] == 1) {
                 $list[$k]['url'] = "http://".WEB_URL."ApiInterface/Version1_3/DirectCar/getDetails?id=".$v['id'];
             }else {
                 $list[$k]['url'] = "http://".WEB_URL."ApiInterface/Version1_3/DirectCar/questionDetails?id=".$v['id'];
             }*/
            $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id'];
            unset($list[$k]['sex']);
            unset($list[$k]['cid']);
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }


    //问题发布
    public function addQuestion() {
        $special_column_question_title = I('special_column_question_title');
        $grade_id = getParameter('grade_id','int');
        $course_id = getParameter('course_id','int');
        $fascicule_id = getParameter('fascicule_id','int');
        $question_tags_id = getParameter('question_tags_id','str',false);
        $special_column_question_reply_description = getParameter('special_column_question_reply_description','str');
        $special_column_editor_quizzer_id = getParameter('userId','int');
        $type=2;


        if ($fascicule_id==1) {
            $fasciculeName = "上册";
        }
        if ($fascicule_id==2) {
            $fasciculeName = "下册";
        }

        if ($fascicule_id==3) {
            $fasciculeName = "全一册";
        }

        $courseName = D("Dict_course")->getCourseInfo($course_id);
        $gradeName = D("Dict_grade")->getGradeInfo($grade_id);

        $search_filed = $special_column_question_title . ',' . $courseName['course_name'] . ',' . $fasciculeName . ',' . $gradeName['grade'];

        $id = D('Direct_train')->addSpecialColumn(compact('search_filed','type','special_column_question_title','grade_id','course_id','fascicule_id','special_column_question_reply_description','special_column_editor_quizzer_id'));

        if ( $id ) {

            $question_list = explode(",",$question_tags_id);
            foreach ($question_list as $k=>$v) {
                $data['question_id'] = $id;
                $data['question_tags_id'] = $v;
                M('question_question_tags_concat')->add($data);
            }

            $insertData['direct_train_id'] = $id;
            $this->model->addDataFormVisitTime($insertData);



            $this->ajaxReturn(array('status' => 200, 'msg'=>'发布成功','data' => ''));
        } else {
            $this->ajaxReturn(array('status' => 500, 'msg'=>'发布失败','data' => ''));
        }
    }

    public function getCourseId() {
        $map['direct_train.ppid'] = 0;
        $map['direct_train.putaway_status'] = 1;
        $map['direct_train.status'] = 1;
        $map['direct_train.delete_status'] = 1;
        $map['direct_train.type'] = 1;
        $list = M('direct_train')->join("dict_course ON dict_course.id = direct_train.course_id")->field("dict_course.id,dict_course.course_name as name")->group('course_id')->where($map)->select();
        return $list;
    }


    //获取我的问题
    public function getMyQuestions(){
        $special_column_editor_quizzer_id = getParameter('userId','int',true);
        $answered = getParameter('answered','int',false);
        $page=I('pageIndex')<1?1:I('pageIndex');
        $field = "direct_train.special_column_type,direct_train.type,dict_course.id as cid,direct_train.id,fascicule_id,special_column_question_title,course_name,grade,name,special_column_question_visit_count,discussion_count,creat_time,special_column_type";

        $where['direct_train.type'] = array('eq',2);
        $where['direct_train.status'] = array('eq',1);
        $where['direct_train.delete_status'] = 1;
        $where['direct_train.putaway_status'] = 1;
        $where['direct_train.pid'] = 0;

        $title = getParameter('search_filed','str',false);
        if(!empty($title)) {
            $where['direct_train.special_column_question_title'] =  array('like', '%' . $title . '%');
        }
        if(!empty($special_column_editor_quizzer_id)) { //我提的所有问题
            $where['direct_train.special_column_editor_quizzer_id'] =  $special_column_editor_quizzer_id;
        }

        if(!empty($answered) && $answered==1 ) { //是否作答
            $where['direct_train.discussion_count'] =  array('eq',0);
        }
        if(!empty($answered) && $answered==2 ) {
            $where['direct_train.discussion_count'] =  array('neq',0);
        }
        $list = D('Direct_train')->getSpecialColumnAll($where,$field,$page,20,'','direct_train.creat_time desc');

        if (!empty($list)) {
            foreach ($list as $k=>$v) {

                switch ($v['special_column_type']) {
                    case 1:
                        $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/shipin@2x.png";
                        break;
                    case 2:
                        $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/wenzhang@2x.png";
                        break;
                    case 3:
                        $list[$k]['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/yinpin@2x.png";
                        break;
                }

                switch ($v['cid']) {
                    case 1:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                        break;
                    case 2:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                        break;
                    case 3:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                        break;
                    case 4:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                        break;
                    case 5:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                        break;
                    case 6:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                        break;
                    case 7:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                        break;
                    case 8:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                        break;
                    case 9:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                        break;
                    case 11:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                        break;
                    case 12:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                        break;
                    case 17:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                        break;
                    case 31:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                        break;
                    case 37:
                        $list[$k]['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                        break;
                }

                switch ($v['fascicule_id']) {
                    case 1:
                        $list[$k]['fascicule_id'] = "上册";
                    case 2:
                        $list[$k]['fascicule_id'] = "下册";
                    case 3:
                        $list[$k]['fascicule_id'] = "全一册";
                }
                $list[$k]['creat_time'] = date("Y年m月d日",strtotime($v['creat_time']));

                if ($v['type'] == 1) {
                    $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$v['id'];
                }else {
                    $list[$k]['url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/questionDetailsView?id=".$v['id'];

                }

                //$list[$k]['url']htmlspecialchars_decode($list['special_column_article'])
            }
        }
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }

    //审核中
    public function getMyHistoryQuestions(){
        $special_column_editor_quizzer_id = getParameter('userId','int',true);
        $page=I('pageIndex')<1?1:I('pageIndex');
        $field = "direct_train.status,special_column_question_title,name,creat_time,avatar,auth_teacher.sex";

        $where['direct_train.type'] = array('eq',2);
        if(!empty($special_column_editor_quizzer_id)) { //我提的所有问题
            $where['direct_train.ppid'] = 0;
            $where['direct_train.special_column_editor_quizzer_id'] =  $special_column_editor_quizzer_id;
        }
        $where['delete_status'] = 1;
        $where['putaway_status'] = array('neq',1);

        $list = D('Direct_train')->getSpecialColumnAll($where,$field,$page);

        foreach ($list as $k=>&$v) {


            if ($v['status'] ==1) {
                $v['status_name'] ="审核通过";
            }
            if ($v['status'] ==2) {
                $v['status_name'] ="审核拒绝";
            }
            if ($v['status'] ==3) {
                $v['status_name'] ="审核中";
            }

            $v['creat_time'] = date("Y年m月d日",strtotime($v['creat_time']));
            unset($v['status']);

            if (empty($v['avatar'])) {
                if ($v['sex']=="男") {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
                } else {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
                }

            } else {
                $v['avatar'] = C('oss_path').$v['avatar'];
            }
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }

    //历史问答楼层级别
    public function getChildReply() {
        $question_id = getParameter('question_id','int');
        $parameter['type'] = 3;
        $parameter['question_reply_concat_id'] = $question_id;
//$parameter = array_merge($parameter,$parms);
        $replyResultDetail = D('Direct_train')->getChildReply($parameter);
        foreach ($replyResultDetail as $k => $values) {
            //根据以上的所有回复ID查找每个回复ID下的所有问题和回复
            unset($check);
            $check['pid'] = $values['id'];
            $check['status'] = YES;
            $check['putaway_status'] = array('neq', WAITUP);
//$check = array_merge($check,$parms);
            $replyResultDetail[$k]['_child'] = D('Direct_train')->getReplyAndQuestionAll($check);
        }
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $replyResultDetail));
    }

    public function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = $list;
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }

            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];

                if ($root == $parentId) { //如果是父级
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }

        return $tree;
    }

    //回复问题
    public function replyProblem() {
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
            $this->ajaxReturn(array('status' => 500, 'msg'=>'error','data' => ''));
        } else {
            $direct_train->commit();
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => ''));
        }
        /*$id = getParameter('id','int');
        $content = getParameter('content','int');
        $map['fid'] = $id;
        $map['special_column_question_reply_description'] = $content;
        $id = D('Direct_train')->addSpecialColumn($map);
        if ( $id ) {
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => ''));
        } else {
            $this->ajaxReturn(array('status' => 500, 'msg'=>'error','data' => ''));
        }*/
    }

    //获取相关的推荐
    public function getRelevantRecommend() {
        $grade_id = getParameter('grade_id','int');
        $course_id = getParameter('course_id','int');
        $fascicule_id = getParameter('fascicule_id','int');

        $type = getParameter('type','int');
        $id = getParameter('id','int');
        $flag = getParameter('flag','int');

        $where['direct_train.grade_id'] = $grade_id;
        $where['direct_train.course_id'] = $course_id;

        $where['direct_train.type'] = $type;
        $where['direct_train.id'] = array('neq',$id);

        $where['direct_train.status'] = array('eq',1);
        $where['direct_train.delete_status'] = 1;
        $where['direct_train.putaway_status'] = 1;
        $where['direct_train.pid'] = 0;


        if ($flag ==1) {
            $where['direct_train.type'] = array('eq',2); //问题
        } else {
            $where['direct_train.type'] = array('eq',1);//专栏
        }
        $field = "direct_train.id,special_column_question_title,course_name,grade,name,special_column_question_visit_count,discussion_count,creat_time,putaway_status_time,direct_train.fascicule_id,direct_train.course_id";
        $page=I('pageIndex')<1?1:I('pageIndex');
        $list = D('Direct_train')->getSpecialColumnPageList($where,$field,$page,4);

        if (count($list)<4) {
            foreach ($list as $ck=>$cv) {
                $ids[]= $cv['id'];
            }
            $ids[] = $id;
            $ids = implode(',',$ids);

            $limit = 4-count($list);
            unset($where['direct_train.grade_id']);
            unset($where['direct_train.course_id']);
            $where['direct_train.id'] = array('not in',$ids);
            $resl = D('Direct_train')->getSpecialColumnPageList($where,$field,$page,$limit,"direct_train.special_column_question_visit_count desc");

            $list = array_merge($list,$resl);
            foreach ($list as $k=>&$value) {

                if ($value['fascicule_id'] ==0) {
                    $value['fascicule_id'] = "";
                }

                if ($value['fascicule_id'] == 1) {
                    $value['fascicule_id'] = "上册";
                }
                if ($value['fascicule_id'] == 2) {
                    $value['fascicule_id'] = "下册";
                }
                if ($value['fascicule_id'] == 3) {
                    $value['fascicule_id'] = "全一册";
                }



                $value['creat_time'] = date("Y-m-d",strtotime($value['creat_time']));
            }
        } else {
            foreach ($list as $k=>&$value) {
                if ($value['fascicule_id'] ==0) {
                    $value['fascicule_id'] = "";
                }

                if ($value['fascicule_id'] == 1) {
                    $value['fascicule_id'] = "上册";
                }
                if ($value['fascicule_id'] == 2) {
                    $value['fascicule_id'] = "下册";
                }
                if ($value['fascicule_id'] == 3) {
                    $value['fascicule_id'] = "全一册";
                }


                $value['creat_time'] = date("Y-m-d",strtotime($value['creat_time']));
            }
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }


    public function convertUrlQuery($query) {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    //获取专栏的详情
    public function getDetails() {
        $id = getParameter('id','int');
        //$role = getParameter('role','int',false);
        //$userId = getParameter('userId','int',false);
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];
        $userinfo = explode('?',$UserAgent);
        $query = $this->convertUrlQuery($userinfo[1]);
        $role = $query['role'];
        $userId = $query['userId'];


        $map['direct_train.id'] = $id;

        if (empty($role)) {
            $role = 0;
        }
        if (empty($userId)) {
            $userId = 0;
        }

        $orderwhere = " and order_info.user_role = {$role} and order_info.user_id={$userId} and order_info.order_status=2";
        $list = D('Direct_train')->getDetailsInfoAll($map,$orderwhere);
        if (!empty($list['oid'])) {
            $list['isPay'] = 1;
        } else {
            $list['isPay'] = 0;
        }
        if(isset($list['special_column_article_show']) && !empty($list['special_column_article_show'])){
            $list['special_column_article_show'] = htmlspecialchars_decode($list['special_column_article_show']);
        }
        if(isset($list['special_column_article']) && !empty($list['special_column_article'])){
            $list['special_column_article'] = htmlspecialchars_decode($list['special_column_article']);
        }

        if (empty($list['avatar'])) {
            if ($list['sex']=="男") {
                $list['avatar'] = "public/web_img/App/teacher_m.png";
            } else {
                $list['avatar'] = "public/web_img/App/teacher_w.png";
            }

        } else {
            $list['avatar'] = $list['avatar'];
        }
        $list['creat_time'] = date("Y-m-d",strtotime($list['creat_time']));

        switch ($list['special_column_type']) {
            case 1:
                $list['special_column_type_name'] = "视频";
                break;
            case 2:
                $list['special_column_type_name'] = "文章";
                break;
            case 3:
                $list['special_column_type_name'] = "音频";
                break;
        }

        $account_list   = D('Account_auths')->isVipInfo($userId,$role);
        if ($account_list['is_auth']==3) {
            $list['isPay']=1;
            $list['is_auth']=3;
        } else {
            $list['is_auth']=1;
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $list));
    }

    //获取问题详情
    public function questionDetails() {
        $userId = getParameter('userId', 'int',false);
        $role = getParameter('role', 'int',false);

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
        $questionDetailResult = D('Direct_train')->getDetailsInfo($check);

        if ($questionDetailResult['fascicule_id'] == 1) {
            $questionDetailResult['fascicule_id'] = "上册";
        }
        if ($questionDetailResult['fascicule_id'] == 2) {
            $questionDetailResult['fascicule_id'] = "下册";
        }
        if ($questionDetailResult['fascicule_id'] == 3) {
            $questionDetailResult['fascicule_id'] = "全一册";
        }

        unset($where);
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $where['teacher_id'] = $userId;
        $result = $editor_teacher_concat->getgetEditorOne($where);
        //查找当前问题的所有问题标签
        $question_tags = D('Question_tags');
        $whereTags['delete_status'] = DELETE_FALSE;
        $whereTags['question_id'] = $id;
        $tags = $question_tags->getTagsByQuestion($whereTags);
        $questionDetailResult['tags'] = $tags;
        if (!empty($result)) {
            $isEditor = true;
        } else {
            $isEditor = false;
        }
        $questionDetailResult['isEditor'] = $isEditor;


        if (empty($questionDetailResult['avatar'])) {
            if ($questionDetailResult['sex']=="男") {
                $questionDetailResult['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
            } else {
                $questionDetailResult['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
            }

        } else {
            $questionDetailResult['avatar'] = C('oss_path').$questionDetailResult['avatar'];
        }

        $questionDetailResult['creat_time'] = date("Y年m月d日",strtotime($questionDetailResult['creat_time']));


        switch ($questionDetailResult['special_column_type']) {
            case 1:
                $questionDetailResult['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/shipin@2x.png";
                break;
            case 2:
                $questionDetailResult['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/wenzhang@2x.png";
                break;
            case 3:
                $questionDetailResult['special_column_type'] = C('oss_path')."public/web_img/App/DirectTrain/yinpin@2x.png";
                break;
        }

        $questionDetailResult['special_column_question_reply_description'] = htmlspecialchars_decode($questionDetailResult['special_column_question_reply_description']);


        switch ($questionDetailResult['course_id']) {
            case 1:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yuwen@2x.png";
                break;
            case 2:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shuxue@2x.png";
                break;
            case 3:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yingyu@2x.png";
                break;
            case 4:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                break;
            case 5:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                break;
            case 6:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                break;
            case 7:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                break;
            case 8:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                break;
            case 9:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                break;
            case 11:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                break;
            case 12:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                break;
            case 17:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                break;
            case 31:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                break;
            case 37:
                $questionDetailResult['course_name'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                break;
        }

        if ($questionDetailResult['grade']==null) {
            $questionDetailResult['grade']="";
        }

        if (empty($questionDetailResult['fascicule_id']) ||$questionDetailResult['fascicule_id'] ==null ) {
            $questionDetailResult['fascicule_id']="";
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $questionDetailResult));
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
    *描述：问题浏览量和专栏观看数量和问题回复数问题讨论量
    */
    public function visitAndReplyNumberSetInc($id, $filed)
    {
        $where['id'] = $id;
        $this->model->visitAndReplyNumberSetInc($where, $filed);
    }

    /*
     *描述：问题详情页面
     */
    public function questionDetailsView(){

        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $userId = getParameter('userId', 'int',false);
        $role = getParameter('role', 'int',false);
        $id = getParameter('id', 'int');
        $commonController = new \Home\Controller\CommonController();
        $this->is_share = $commonController->isApp() ? 0 :getParameter('is_share', 'int',false);
        $url = str_replace('flag=1','',WEB_URL.$_SERVER['REQUEST_URI']);
        $this->assign('urldata',"http://".$url );
        //获取当前问题的详情
        $direct_train = D('Direct_train');
        $check['direct_train.id'] = $id;
        $detailsResult = $direct_train->getSpecialColumnOne($check);
        $this->assign('grade_id',$detailsResult[0]['grade_id']);
        $this->assign('course_id',$detailsResult[0]['course_id']);
        $this->assign('fascicule_id',$detailsResult[0]['fascicule_id']);

        $this->assign('id',$id);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->display('questionDetails');
    }

    /*
     *描述：专栏详情页面
     */
    public function specialColumnDetailsView(){
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);
        $this->type = getParameter('type', 'str',false);

        $id = getParameter('id','int');
        $userId = getParameter('userId', 'int',false);
        $role = getParameter('role', 'int',false);
        $commonController = new \Home\Controller\CommonController();
        $this->is_share = $commonController->isApp() ? 0 :getParameter('is_share', 'int',false);
        $url = str_replace('flag=1','',WEB_URL.$_SERVER['REQUEST_URI']);
        $this->assign('urldata',"http://".$url );
        $direct_train = D('Direct_train');
        $check['direct_train.id'] = $id;
        $detailsResult = $direct_train->getSpecialColumnOne($check);


        $where['direct_train.id'] = $id;
        $this->SetIncNum($id, 'special_column_question_visit_count');

        $this->assign('grade_id',$detailsResult[0]['grade_id']);
        $this->assign('course_id',$detailsResult[0]['course_id']);
        $this->assign('fascicule_id',$detailsResult[0]['fascicule_id']);

        $this->assign('id',$id);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->display('getDetails');
    }

    public function SetIncNum($id, $filed)
    {
        $where['id'] = $id;
        D('Direct_train')->visitAndReplyNumberSetInc($where, $filed);
    }

    //获取订单号

    //根据订单入库订单信息
    public function addOrderGo() {
        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if ( empty($rsa_data['id']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '资源id错误'));
        }

        if ( empty($rsa_data['real_price']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '资源价格错误'));
        }

        $userOrder = self::$orderModel->getDirectByOrder( $rsa_data['id'],$userId,$role );//根据角色获取对应的订单

        if( empty($userOrder) ) { //直接跳转到成功的页面
            $order_data = [];
            $order_data['order_sn'] = StrOrderOne();
            $order_data['user_role'] = $role;
            $order_data['resources_id'] = $rsa_data['id'];
            $order_data['user_id'] = $userId;
            $order_data['pay_fee'] = $rsa_data['real_price'];
            $order_data['pay_source'] = isIos();
            $order_data['order_type'] = 1;
            $order_data['create_at'] = time();
            $id = self::$orderModel->addOrder($order_data);//入库订单
            if ( $id ) {
                $order_info = self::$orderModel->DirectIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                $this->ajaxReturn(array('status' => 200,'data'=>$order_info['order_sn'],'message'=>'入库成功'));
            } else {
                $this->ajaxReturn(array('status' => 400, 'message' => '入库失败', 'data'=>array() ));
            }
        } else {//已经下单
            if($userOrder['order_status'] == 3 || $userOrder['is_delete'] == 2) {

                $order_data = [];
                $order_data['order_sn'] = StrOrderOne();
                $order_data['user_role'] = $role;
                $order_data['resources_id'] = $rsa_data['id'];
                $order_data['user_id'] = $userId;
                $order_data['pay_fee'] = $rsa_data['real_price'];
                $order_data['pay_source'] = isIos();
                $order_data['order_type'] = 1;
                $order_data['create_at'] = time();
                $id = self::$orderModel->addOrder($order_data);//入库订单
                if ( $id ) {
                    $order_info = self::$orderModel->DirectIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                    $this->ajaxReturn(array('status' => 200,'data'=>$order_info['order_sn'],'message'=>'入库成功'));
                } else {
                    $this->ajaxReturn(array('status' => 400, 'message' => '入库失败', 'data'=>array() ));
                }

            } else {
                $this->ajaxReturn(array('status' => 200,'data'=>$userOrder['order_sn'],'message'=>'入库成功'));
            }
        }
    }
}
