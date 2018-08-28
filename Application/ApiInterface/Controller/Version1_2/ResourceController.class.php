<?php
namespace ApiInterface\Controller\Version1_2;

use Common\Common\JSSDK;

class ResourceController extends PublicController
{
    public $pageSize = 20;
    public $Model;

    public function __construct() {
        parent::__construct();
        $this->Model = D('Biz_resource');
        $this->assign('oss_path', C('oss_path'));
    }

    public function getPageSize(){
        return $this->pageSize;
    }


    /**
     * @描述：app首页资源
     *
     */
    public function resourceIndex()
    {
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');

        $page =getParameter('pageIndex','int',true);
        $pageSize =I('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }
        $start = ($page-1)*$this->getPageSize();
        $new_share_data=$this->Model->getAppColumnResourceData('create_at',$start,$this->pageSize);
        $hot_data=$this->Model->getAppColumnResource('follow_count',$start,$this->pageSize);

        $resource_file_type=C('RESOURCE_UPLOAD_FILE_TYPE');
        $grade_result=$grade_model->getGradeList();
        $course_list=$course_model->getCourseList();

        //$result['grade_list'] = $grade_result;
        //$result['course_list'] = $course_list;
        //$result['resource_file_type'] = $resource_file_type;
        $result['new_share_data'] = $new_share_data;
        $result['hot_data'] = $hot_data;

        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }
    //搜索提交
    public function getSearchWhereList(){
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        $resource_file_type=C('COPY_RESOURCE_UPLOAD_FILE_TYPE');
        $grade_result=$grade_model->getAppGradeList();
        $course_list=$course_model->getAppCourseList();
        //$citydata = $this->Model->getAppCityDistrict();

        $school_term['value'] = "1";
        $school_term['title'] = '上册';
        $school_term1['value'] = "2";
        $school_term1['title'] = '下册';
        $school_term2['value'] = "3";
        $school_term2['title'] = '全一册';
        $school_data[] = $school_term;
        $school_data[] = $school_term1;
        $school_data[] = $school_term2;

        $result['grade'] = $grade_result;
        $result['course'] = $course_list;
        $result['type'] = $resource_file_type;
        $result['school_term'] = $school_data;
        //$result['district'] = $citydata;

        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }

    public function getResourceNewShare() {
        $page =getParameter('pageIndex','int',true);
        $pageSize =I('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }
        $start = ($page-1)*$this->getPageSize();
        $new_share_data=$this->Model->getAppColumnResourceData('create_at',$start,$this->pageSize);
        foreach ($new_share_data as $k=>$v) {
            $new_share_data[$k]['create_at'] = date('Y/m/d',$v['create_at']);
            $new_share_data[$k]['school_name'] = '学校:'.$v['school_name'];
            $new_share_data[$k]['url'] = 'http://'.WEB_URL.'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$v['id'];
        }
        $this->ajaxReturn(array('status' => 200,'result' => $new_share_data));
    }

    public function getResourceHot() {
        $page =getParameter('pageIndex','int',true);
        $pageSize =I('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        } else {
            $this->pageSize = 4;
        }
        $start = ($page-1)*$this->getPageSize();
        $hot_data=$this->Model->getAppColumnResource('follow_count',$start,$this->pageSize);
        foreach ($hot_data as $k=>$v) {
            $hot_data[$k]['create_at'] = date('Y/m/d',$v['create_at']);
            $hot_data[$k]['school_name'] = '学校:'.$v['school_name'];
            $hot_data[$k]['url'] = 'http://'.WEB_URL.'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$v['id'];
        }
        $this->ajaxReturn(array('status' => 200,'result' => $hot_data));
    }

    /**
     * @描述：app更多热度分享
     *
     */
    public function getHotreadShareMore(){

        $page =getParameter('pageIndex','int',true);
        $pageSize =I('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }
        $start = ($page-1)*$this->getPageSize();
        $hot_data=$this->Model->getAppColumnResource('follow_count',$start,$this->pageSize);

        $this->ajaxReturn(array('status' => 200,'result' => $hot_data));
    }
    /**
     * @描述：全部资源列表
     *
     */
    public function resourceList() {
        $check['biz_resource.status'] = 2;
        $order =getParameter('order', 'str',false);
        //$order=$order?$order:'desc';
        $userId =getParameter('userId','int',true);
        $role =getParameter('role','int',true);
        $page =getParameter('pageIndex','int',true);

        $pageSize =getParameter('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }

        $courseGradeInfo = (new \Home\Controller\CommonController())->getUserCourseGradeInfo($role,$userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];
        $currentMonth = date("m", time());
        $schoolTerm = ($currentMonth >= 3 && $currentMonth <= 8) ? 2 : 1;

        $start = ($page-1)*$this->getPageSize();
        $result=$this->Model->getAppResourceData($check,$order,$userId,$role,$start,$this->pageSize,$courseId,$gradeId,$schoolTerm);

        foreach ($result as $k=>$v) {
            $result[$k]['create_at'] = date('Y/m/d',$v['create_at']);
            $result[$k]['school_name'] = '学校:'.$v['school_name'];
            $result[$k]['url'] = 'http://'.WEB_URL.'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$v['id'];
        }

        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }


    /**
     * @描述：我发布的资源
     *
     */

    public function myPublishResource() {
        $page =getParameter('pageIndex','int',true);
        $pageSize =getParameter('pageSize','int',false);

        $userId =getParameter('userId','int',true);
        $role =getParameter('role','int',true);

        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }

        $start = ($page-1)*$this->getPageSize();
        $result=$this->Model->getAppmyPublishResource($start,$this->pageSize,$userId);
        foreach ($result as $k=>$v) {
            $result[$k]['create_at'] = date('Y/m/d',$v['create_at']);
            $result[$k]['school_name'] = '学校:'.$v['school_name'];
            $result[$k]['url'] = 'http://'.WEB_URL.'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$v['id'];
        }
        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }


    /**
     * @描述：资源搜索
     *
     */

    public function resourceSearch(){
        $userId =getParameter('userId','int',true);
        $role =getParameter('role','int',true);

        $page =getParameter('pageIndex','int',true);

        $pageSize =getParameter('pageSize','int',false);
        if (!empty($pageSize)) {
            $this->pageSize = $pageSize;
        }

        $start = ($page-1)*$this->getPageSize();

        $filter['keyword'] =trim(getParameter('keyword', 'str',false));
        $filter['type'] =getParameter('type', 'str',false);
        $filter['course'] = getParameter('course', 'int',false);
        $filter['grade'] = getParameter('grade', 'int',false);
        $filter['textbook'] =getParameter('textbook', 'int',false);
        $filter['s_time'] =getParameter('s_time', 'str',false);
        $filter['e_time'] =getParameter('e_time', 'str',false);
        $filter['author'] =trim(getParameter('author', 'str',false));//这里是发布人
        $filter['district'] =getParameter('district', 'int',false);
        $filter['school_term'] =getParameter('school_term', 'int',false);//上册下册
        $filter['citytype'] =getParameter('citytype', 'str',false);
        $order =getParameter('order', 'str',false);
        $order=$order?$order:'browse';


        $filter_arr['create_start_time']=$filter['s_time'];
        $filter_arr['create_end_time']=$filter['e_time'];

        $check['biz_resource.status'] = 2;
        if (!empty($filter['keyword'])){
            $filter['keyword'] = preg_replace('/\s+/', ' ', $filter['keyword']);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $check['biz_resource.resource_info'][] = array('like', '%' . $item . '%');
            }

        }
        if (!empty($filter['author'])){
            $filter['author'] = preg_replace('/\s+/', ' ', $filter['author']);
            $filter['author'] = preg_replace('/\%+/', '\%', $filter['author']);
            $temp_arr = explode(' ',$filter['author']);
            foreach ($temp_arr as $item){
                $check['auth_teacher.name'][] = array('like', '%' . $item . '%');
            }

        }
        if (!empty($filter['type'])) $check['biz_resource.type'] = $filter['type'];
        if (!empty($filter['course'])) $check['biz_resource.course_id'] = $filter['course'];
        if (!empty($filter['grade'])) $check['biz_resource.grade_id'] = $filter['grade'];
        if (!empty($filter['textbook'])) $check['biz_textbook.id'] = $filter['textbook'];
        if (!empty($filter['district'])) $check['dict_schoollist.district_id'] = $filter['district'];
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];

        if($filter_arr['create_start_time']!='' && $filter_arr['create_end_time']!=''){

            if(date('Y-m-d',strtotime($filter_arr['create_start_time']))==$filter_arr['create_start_time'] && date('Y-m-d',strtotime($filter_arr['create_end_time']))==$filter_arr['create_end_time']){
                $check['_string']='biz_resource.create_at>='.strtotime($filter_arr['create_start_time']).' and biz_resource.create_at<='.(strtotime($filter_arr['create_end_time'])+86399);
            } else {
                unset($filter_arr['create_start_time']);
                unset($filter_arr['create_end_time']);
            }
        }elseif(!empty($filter_arr['create_start_time'])){
            if(date('Y-m-d',strtotime($filter_arr['create_start_time']))==$filter_arr['create_start_time']){
                $check['_string']='biz_resource.create_at>='.strtotime($filter_arr['create_start_time']);
            }else{
                unset($filter_arr['create_start_time']);
            }
        }elseif(!empty($filter_arr['create_end_time'])){
            if(date('Y-m-d',strtotime($filter_arr['create_end_time']))==$filter_arr['create_end_time']){
                $check['_string']='biz_resource.create_at<='.(strtotime($filter_arr['create_end_time'])+86399);
            }else{
                unset($filter_arr['create_end_time']);
            }
        }

        $result=$this->Model->getAppResourceData($check,$order,$userId,$role,$start,$this->pageSize);     //echo "<pre>";print_r($result);die;

        foreach ($result as $k=>$v) {
            $result[$k]['create_at'] = date('Y/m/d',$v['create_at']);
            $result[$k]['school_name'] = '学校:'.$v['school_name'];
            $result[$k]['url'] = 'http://'.WEB_URL.'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$v['id'];
        }

        /*$this->Model->createResourceTempTable();
        $this->Model->insertTempDataByCondtion($check);

        if(empty($filter['district'])){
            $district_result=$this->Model->getDistrict($check);
        }

        if(empty($filter['course'])){
            $course_con=$this->Model->getCondition('course');
        }

        if(empty($filter['grade'])){
            $grade_con=$this->Model->getCondition('grade');
        }

        $school_term_con=$this->Model->getCondition('school_term');
        $file_type_con=$this->Model->getCondition('file_type');
        $file_type_con=$this->fileTypeOrder($file_type_con);*/

        $where['result'] = $result;
        if ($filter['citytype'] ==2) {
            $where['wherelist'][]=array('fieldName'=>'district','title'=>'地区','data'=>$this->Model->getAppCityDistrict());
        } else {
            $grade_model=D('Dict_grade');
            $course_model=D('Dict_course');
            $resource_file_type=C('COPY_RESOURCE_UPLOAD_FILE_TYPE');
            $grade_result=$grade_model->getAppGradeList();

            $course_list=$course_model->getAppCourseList();
            $citydata = $this->Model->getAppCityDistrict();

            $school_term['value'] = "1";
            $school_term['title'] = '上册';
            $school_term1['value'] = "2";
            $school_term1['title'] = '下册';
            $school_term2['value'] = "3";
            $school_term2['title'] = '全一册';
            $school_data[] = $school_term;
            $school_data[] = $school_term1;
            $school_data[] = $school_term2;

            $where['wherelist'][]=array('fieldName'=>'type','title'=>'资源类型','data'=>$resource_file_type);
            $where['wherelist'][]=array('fieldName'=>'course','title'=>'学科','data'=>$course_list);
            $where['wherelist'][]=array('fieldName'=>'grade','title'=>'年级','data'=>$grade_result);
            $where['wherelist'][]=array('fieldName'=>'school_term','title'=>'分册','data'=>$school_data);
            $where['wherelist'][]=array('fieldName'=>'district','title'=>'地区','data'=>$citydata);
        }

        /*$where['course_con'] = empty($course_con)?[]:$course_con;
        $where['grade_con'] = empty($grade_con)?[]:$grade_con;
        $where['school_term_con'] = empty($school_term_con)?[]:$school_term_con;
        $where['file_type_con'] = empty($file_type_con)?[]:$file_type_con; //资源类型*/


        $this->ajaxReturn(array('status' => 200,'result' => $where));

    }


    /**
     * @描述：教师资源详情
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：详情页HTML
     */
    public function resourceDetails($flag=false)
    {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $id =getParameter('id','int',true);

        $flag=getParameter('flag','int',false);

        $isShare = 0;
        if(1 != $flag) {
            $role =getParameter('role','int',false);
            $userId =getParameter('userId','int',false);
        }
        else
        {
            if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
                redirect('/index.php?m=Home&c=ResourceList&a=resourceDetails&id='.$id.'&from=share');
            }
            $userId = 0;
            $role = ROLE_TEACHER;
            $isShare = 1;
        }

        $result = $this->Model->getResourceDetails($id,$userId);
        if (empty($result)) {
            die(500);
        }
        

        $this->assign('contactInfo',$result['result_list']);
        if($flag==true){
            $result['content']=$this->Model->getSubResourceHTMLList($result);
        }
        $this->assign('flag',$flag);

        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        $contact_result = $this->Model->getContactResourcePath($id);
        $this->assign('contact_data', $contact_result);

        //判断登陆者是否和发布者是一人
        if($result['teacher_id']==$userId && ($role == 2)){
            $this->assign('operation_status',1);
        }else{
            $this->assign('operation_status',2);
        }
        $reList = $this->Model->getRecommendData($id);
        $urldata = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $urldata = str_replace('&flag=1','',$urldata);

        $this->assign('reList',$reList);
        $this->assign('existedZan', $this->Model->getIsZan($id, $userId, $role));
        $this->assign('existedFavor', $this->Model->getIsFavor($id, $userId, $role));
        $this->Model->setBrowseCountPlusOne($id);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->assign('isShare',$isShare);
        $this->assign('host','http://'.$_SERVER['HTTP_HOST']);
        $this->assign('urldata',$urldata);
		if($result['type'] != 'ppt' && $result['type'] != 'word' && $result['type'] != 'pdf')
          $this->display();
        else
          $this->display('resourceDetailsOld');
 
    }

    /**
     * @描述：教师资源详情分享页面
     * @参数：id[int] Y 资源ID
     * @返回值：详情页HTML
     */
    public function resourceDetailsShare()
    {
        $id = $_GET['id'];
        $this->res_id = $id;
        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];
        $this->assign('apkurl',$apkurl);
        $flag=true;
        $url = WEB_URL.$_SERVER['REQUEST_URI'];
        $url = str_replace('resourceDetailsShare','resourceDetails',$url);
        $this->assign('urldata',"http://".$url );
        $this->resourceDetails($flag);
    }


    //对资源类型进行排序
    function fileTypeOrder($arr){
        $type_file=array(
            'video','audio','image','word','ppt','pdf','swf','condensed'
        );
        $data=array();
        for($i=0;$i<count($type_file);$i++){
            for($j=0;$j<count($arr);$j++){
                if($arr[$j]['file_type']==$type_file[$i]){
                    $data[]=$arr[$j];
                    break;
                }
            }
        }
        return $data;
    }



}


