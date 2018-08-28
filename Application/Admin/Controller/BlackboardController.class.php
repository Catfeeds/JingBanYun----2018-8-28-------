<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class BlackboardController extends Controller
{   
                    
    public $page_size=20;  
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
    
    //小黑板管理
    public function blackboardMgmt()
    { 
        if (!session('?admin')) redirect(U('Login/login'));

        if(session('admin.role')==3){ 
            $check['biz_class.school_id'] = session('admin.school_id');
        }
                

        if(session('admin.role')!=3){ 
            $filter['province'] = intval($_REQUEST['province']);
            $filter['city'] = intval($_REQUEST['city']);
            $filter['district'] = intval($_REQUEST['district']);  
            $filter['school'] = intval($_REQUEST['school']);
            $filter['keyword'] = $_REQUEST['keyword']; 
            $filter['date'] = $_REQUEST['date'];
            
            if (!empty($filter['province'])) $check['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $check['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $check['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school'])) $check['dict_schoollist.id'] = $filter['school'];
            if (!empty($filter['keyword'])) $check['_string'] ="(biz_blackboard.message_title like '%".$filter['keyword']."%' or auth_teacher.name like '%".$filter['keyword']."%' or "
                    . "biz_blackboard.message like '%".$filter['keyword']."%')"; 
            if(!empty($check['_string'])){
                if (!empty($filter['date'])) $check['_string'] .= " and (biz_blackboard.create_at>=".strtotime(I("date"))." and "."biz_blackboard.create_at<=".(strtotime(I("date")."+1 day")-1).")";
            }else{
                if (!empty($filter['date'])) $check['_string'] = " biz_blackboard.create_at>=".strtotime(I("date"))." and "."biz_blackboard.create_at<=".(strtotime(I("date")."+1 day")-1);
            }
        };
        
        //print_r($check);die(); 

        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板信息列表');

        $Model = M('biz_blackboard');
        $count = $Model
            ->join("LEFT JOIN biz_class on biz_blackboard.class_id = biz_class.id")
            ->join("LEFT JOIN dict_grade on biz_class.grade_id=dict_grade.id")
            ->join("LEFT JOIN auth_teacher on biz_blackboard.publisher_id = auth_teacher.id")
            ->join("LEFT JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id")
            ->field("biz_blackboard.*,biz_class.name as class_name,dict_grade.grade")
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        /*foreach ($filter as $key => $val) {
            $Page->parameter[$key] = $val;
        }*/

        $show = $Page->show();

        $result = $Model
            ->join("LEFT JOIN biz_class on biz_blackboard.class_id = biz_class.id")
            ->join("LEFT JOIN dict_grade on biz_class.grade_id=dict_grade.id")
            ->join("LEFT JOIN auth_teacher on biz_blackboard.publisher_id = auth_teacher.id")
            ->join("LEFT JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id")
            ->field("biz_blackboard.*,biz_class.name as class_name,dict_grade.grade,dict_schoollist.id as sid")
            ->order('biz_blackboard.create_at desc')
            ->where($check)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();                  

        $this->assign('list', $result);
        $this->assign('page', $show);

        
        //条件是否存在 
        /*if(!empty($filter['province'])) $check2['dict_schoollist.provice_id'] = intval($_GET['province']); 
        if(!empty($filter['city'])) $check2['dict_schoollist.city_id'] = intval($_GET['city']);
        if(!empty($filter['district'])) $check2['dict_schoollist.district_id'] = intval($_GET['district']);
        if(!empty($filter['school'])) $check2['dict_schoollist.id'] = intval($_GET['school']);  
         */
        $check2=$check;
        
        //取出所有省份 
        $register_check['social_activity_register.activity_id']=$id;
        $social_activity_register=M('social_activity_register'); 
        $district_model=M('dict_citydistrict');
        $province=$district_model->field('id,name')->where("upid=0")->select();
        //省份不为空,取出市 
        if(!empty($check2['dict_schoollist.provice_id'])){  
            if($check2['dict_schoollist.provice_id']==1 || $check2['dict_schoollist.provice_id']==2 || $check2['dict_schoollist.provice_id']==9 || $check2['dict_schoollist.provice_id']==22 
                    || $check2['dict_schoollist.provice_id']==33 || $check2['dict_schoollist.provice_id']==34){    
                switch ($check2['dict_schoollist.provice_id']) {        
                    case 1: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='北京市';
                        break;
                    case 2: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='天津市';
                        break;
                    case 9: 
                            $city_result[0]['id']=9;
                            $city_result[0]['name']='上海市';
                        break;
                    case 22: 
                            $city_result[0]['id']=22;
                            $city_result[0]['name']='重庆市';
                        break;
                    case 33: 
                            $city_result[0]['id']=33;
                            $city_result[0]['name']='香港特别行政区';
                        break;
                    case 34: 
                            $city_result[0]['id']=34;
                            $city_result[0]['name']='澳门特别行政区';
                        break;
                    default:
                        break;
                }
            }else{ 
                $city_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.provice_id'])->select(); 
            } 
        }  
        //市不为空,取出区县
        if(!empty($check2['dict_schoollist.city_id'])){ 
            $district_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.city_id'])->select(); 
        } 
        //区县不为空,取出学校
        if(!empty($check2['dict_schoollist.district_id'])){
            $school_model=M('dict_schoollist');
            $school_result=$school_model->field('id,school_name')->where("district_id=".$check2['dict_schoollist.district_id'])->order("school_name asc")->select();
        } 
        $this->assign('province_result', $province);    
        $this->assign('city_result',$city_result);
        $this->assign('district_result',$district_result);
        $this->assign('school_result',$school_result);
        
        
        $this->assign('province', $check2['dict_schoollist.provice_id']);   
        $this->assign('city', $check2['dict_schoollist.city_id']);
        $this->assign('district', $check2['dict_schoollist.district_id']);
        $this->assign('school', $check2['dict_schoollist.id']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('default_date', $filter['date']);
        
        $this->display();
    }
    
    
    //修改小黑板
    function modifyBlackboard(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板消息详情');
        
        $Model = M('biz_blackboard');
        if($_POST){ 
            $id=intval($_POST['id']);
            $data['message']=$_POST['message'];
            $Model->where('id='.$id)->save($data);
            
            $this->redirect("Blackboard/blackboardMgmt");
        }else{
            $id = $_GET['id']; 
            $check['biz_blackboard.id'] = $id;

           /* $result = $Model
                ->join("biz_class on biz_blackboard.class_id = biz_class.id")
                ->field("biz_blackboard.*,biz_class.name as class_name")
                ->where($check)->find(); */
            $result = $Model
                ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
                ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->field('group_concat(grade,biz_class.name) as cgname,biz_blackboard.*,biz_class.name as class_name')
                ->where( $check )
                ->find();
            
            $this->assign('data', $result);
        }
        $this->display();
    }

    //小黑板详情
    function blackboardDetails()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板消息详情');
        $id = $_GET['id'];
        $Model = M('biz_blackboard');
        $check['biz_blackboard.id'] = $id;

        /*$result = $Model
            ->join("biz_class on biz_blackboard.class_id = biz_class.id")
            ->field("biz_blackboard.*,biz_class.name as class_name")
            ->where($check)->find();*/
        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->field('group_concat(grade,biz_class.name) as cgname,biz_blackboard.*,biz_class.name as class_name')
            ->where( $check )
            ->find();
        //$cgname = explode(',',$result['cgname']);
        //$result['cgname'] = array_unique($cgname);
        $this->assign('data', $result);

        $this->display();
    }

    //删除小黑板信息
    function deleteBlackboardMessage()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_blackboard');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }
}
