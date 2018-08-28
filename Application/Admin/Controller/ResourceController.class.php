<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class ResourceController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
     
    //资源列表
    public function resourceMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '资源分享审核');
        $this->assign('nav', '资源分享审核');
        $this->assign('subnav', '资源列表');

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];   
        $filter['type'] = $_REQUEST['type']; 
        $filter['sort_column'] = $_REQUEST['sort_column'];
        $filter['keyword'] = $_REQUEST['keyword']; 
        $filter['status'] = $_REQUEST['status'];     
                    
        
        if (!empty($filter['course_id'])) $where['biz_resource.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $where['biz_resource.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $where['biz_resource.textbook_id'] = $filter['textbook_id'];
        if (!empty($filter['type'])) $where['biz_resource.type'] = $filter['type'];        
        if (empty($filter['sort_column'])) $filter['sort_column'] = 'create_at';  
        
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']); 
        $this->assign('type', $filter['type']); 
        $this->assign('sort_column', $filter['sort_column']);
        if(!empty($filter['course_id']) && !empty($filter['grade_id'])){
            
            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $filter['course_id'];
            $c1['grade_id'] = $filter['grade_id']; 
            $textbook_result = $TextbookModel->where($c1)->field('id,name')->select();       
            $this->assign('textbook_list',$textbook_result);
        }
        
        $keyword = $filter['keyword'];
        $status=intval($filter['status']);
        if (!empty($keyword)) {
            $where['_string'] = '(biz_resource.name LIKE \'%' . $keyword .'%\' OR ' .'biz_resource.teacher_name LIKE \'%' . $keyword . '%\' OR biz_resource.description LIKE \'%' .$keyword . '%\') AND';
        } 
        if(!empty($where['_string'])){
            $where['_string']=$where['_string']."  biz_resource.type!='html'";
        }else{
            $where['_string']=" biz_resource.type!='html'";
        } 
        
        if(!empty($status)){  
            $where['_string']=$where['_string']." and biz_resource.status=".$status;
            $this->assign('status', $status);
        }   
        
        $Model = M('biz_resource'); 
        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')
            ->field('biz_resource.id,biz_textbook.name as textbook')
            ->where($where)    
            ->count('1');          
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        } 
        $show = $Page->show();          

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')
            ->field('biz_resource.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade,auth_teacher.avatar,auth_teacher.name teacher_name')
            ->order('biz_resource.'.$filter['sort_column'].' desc')
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();                                    
        //资源如果存在,就对id依次进行加密
        /*if(!empty($result)){
            $des=new DES3(); 
            foreach($result as $key=>$val){
                $result[$key]['key']=$des->des3_encrypt($val['id']); 
            }
        }*/   
        
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        
        $Model = M('dict_grade');
        $grades = $Model->select();
        
        $resource_model=D('Biz_resource');
        $all_resource_result=$resource_model->getAllResourceCount();  
        $resource_type=$resource_model->getResourceType();       
        $search_resource=array();
        $search_resource[]=array('key'=>'资源总数','value'=>$count);
        
        foreach($resource_type as $key=>$val){
            $where['biz_resource.type']=$key;
            $data=$resource_model->getResourceInfoCount($where);
            $search_resource[]=array('key'=>$val,'value'=>$data['count']);
        }  
        
        $this->assign('search_resource_arr', $search_resource);
        $this->assign('all_resource_count_arr', $all_resource_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('keyword', $keyword); 
        
        $this->assign('courses', $courses); 
        $this->assign('grades', $grades);
        
        $this->display();
    }
    
    
    //资源详情
    public function resourceDetails()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '资源分享审核');
        $this->assign('nav', '资源分享审核');


        $id = $_GET['id'];

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->field('biz_resource.*,biz_textbook.name as textbook,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,'
                    . 'dict_grade.grade,auth_teacher.name teacher_name,auth_teacher.avatar')
            ->where("biz_resource.id=$id")
            ->find();

        $this->assign('subnav', $result['name']); 
        $this->assign('data', $result); 
        $this->assign('oss_path',C('oss_path')); 
        
        $resource_list=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource.id=biz_resource_contact.biz_resource_id")
                            ->field("biz_resource.id,biz_resource.type,biz_resource_contact.resource_path,biz_resource_contact.vid vvid")->select();
        $this->assign('resource_list', $resource_list); 
        
        $this->display();

    }
    
    //通过审核资源分享  
    public function approveResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_resource');

        $data['status'] = 2;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();

        $User = M("auth_teacher");
        $User->where("id=" . $resource['teacher_id'])->setInc("points", 100);// 积分加100

        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEPASS',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }

    //拒绝审核资源分享
    public function denyResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_resource');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();
        //$User = M("auth_teacher");
        //$User->where("id=" . $resource['teacher_id'])->setInc("points", -100);// 积分减100
        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEFAIL',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }
    
    //资源分享下架
    public function downResource(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $id = $_GET['id'];
        $Model = M('biz_resource');
        $data['status'] = 1;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();
        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }
    
    //京版资源删除
    function deleteResource(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(session('admin.role')==3){
            $this->ajaxReturn('error');
        }else{
            $id=getParameter('id','int');       
            $resource_model=D('Biz_resource');
            if($resource_model->managementDeleteResource($id)){
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('failed');
            }
        }
    }
}
