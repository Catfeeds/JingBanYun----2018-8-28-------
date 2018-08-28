<?php
namespace Home\Controller;
define('PUTAWAY',1);
define('ENABLED',1);
define('APPROVE',1);
use Think\Controller;

class MyresourcesController extends PublicController
{   
            public function array_remove($data, $key){
                if(!array_key_exists($key, $data)){
                    return $data;
                }
                $keys = array_keys($data);
                $index = array_search($key, $keys);
                if($index !== FALSE){
                    array_splice($data, $index, 1);
                }
                return $data;
           }
    
    
	   public function redbjResourceList()     
                {  
		      	A('Home/Common')->getUserIdRole($userId,$role);
                        switch($role)
                        {
                            case ROLE_TEACHER:layout('teacher_layout_withouticon');
                                $USER_ID=session('teacher.id'); 
                                    break;
                            case ROLE_STUDENT:layout('student_layout_withouticon');
                                $USER_ID=session('student.id');
                                    break;
                            case ROLE_PARENT:layout('parent_layout_withouticon');
                                $USER_ID=session('parent.id');
                                    break;
                            default:
                                redirect(U('Index/index'));
                                    break;
                        }       
                        $role_con=$role-2;
                         $filter['studying_phase'] =getParameter('cat', 'int',false);
                         $filter['course_id'] = getParameter('course', 'int',false);    
                         $filter['grade_id'] = getParameter('grade', 'int',false);
                         $filter['school_term_id'] = getParameter('textbook', 'int',false);
                         $filter['type'] = getParameter('type', 'str',false);
                         
                         $filter['keyword'] = $_REQUEST['keyword'];

                         if ($filter['keyword'] != NULL || !empty($filter['type']) || $filter['studying_phase']!=0|| $filter['course_id']!=0|| $filter['grade_id']!=0|| $filter['school_term_id']!=0) {
                            $this->assign('kw',1);
                         }
                         $filter['sort_column'] = isset($_REQUEST['sort_column'])?intval($_REQUEST['sort_column']):6;
                         $searchCate = $_REQUEST['resource_cate'];
                         $keyword = $_REQUEST['keyword'];

                         $course_select_id=$filter['course_id'];
                         
                         $all_course_id=32;

                         if($filter['course_id']==$all_course_id){
                             $filter['course_id']=0;
                         }

                         if (!empty($filter['school_term_id'])) $check['biz_textbook.school_term'] = $filter['school_term_id'];
                         //if (empty($filter['sort_column'])) $filter['sort_column'] = 'create_at';
                         if (empty($searchCate)) $searchCate = 'all';

                         $res = $this->getSortString($filter['sort_column']);
                         $sort_string = $res[0];
                         $filter['sort_column'] = $res[1];

                        //学段
                        $studying_phase=$filter['studying_phase'];
                            
                         if($searchCate == 'bj')
                         {
                             if (!empty($filter['type'])) $check['knowledge_resource.file_type'] = $filter['type'];
                             $res = $this->getGradeWhere($filter['grade_id']);
                             if('' != $res)
                                 $check['knowledge_resource_point.grade'] = $res;
                             $res = $this->getCourseWhere($filter['course_id']);
                             if('' != $res)
                                 $check['knowledge_resource_point.course'] = $res;

                             $check['knowledge_resource.putaway_status']=PUTAWAY;
                             $check['knowledge_resource.status']=APPROVE;
                            if($studying_phase==1){
                                $check['_string'] = 'knowledge_resource_point.grade<=6';
                            }elseif($studying_phase==2){
                                $check['_string'] = 'knowledge_resource_point.grade<=9 and knowledge_resource_point.grade>=7';
                            }elseif($studying_phase==3){
                                $check['_string'] = 'knowledge_resource_point.grade<=12 and knowledge_resource_point.grade>=10';
                            } 
                        
                         if (!empty($filter['keyword'])) $check['knowledge_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                          $Model = M('knowledge_resource');
                          $check['knowledge_resource_collect.user_id'] = $USER_ID;
                          $check['knowledge_resource_collect.role'] = $role_con;
                          $count=  $Model
                              ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
                              ->join('knowledge_resource_collect on knowledge_resource_collect.resource_id=knowledge_resource.id')
                              ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id','left')
                              ->where($check)
                              ->field('1')
                              ->select();
                          $count=count($count);
                          //C('PAGE_SIZE_FRONT')
                          $Page = new \Think\Page($count,21 );

                          //$Page->parameter['keyword'] = urlencode($keyword);
                          $Page->parameter['keyword'] = $keyword;

                          $Page->parameter['course_id'] = $filter['course_id'];
                          $Page->parameter['grade_id'] = $filter['grade_id'];
                          $Page->parameter['textbook_id'] = $filter['school_term_id'];
                          $Page->parameter['sort_column'] = $filter['sort_column'];
                          $Page->parameter['type'] = $filter['type'];
                          //$Page->parameter['resource_cate'] = urlencode($searchCate);
                          $Page->parameter['resource_cate'] = $searchCate;

                          $show = $Page->show();

                              $sort_string = str_replace($sort_string,'follow_count','browse_count');
                          $result = $Model
                                     ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
                                     ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id','left')
                                     ->join('knowledge_resource_collect on knowledge_resource_collect.resource_id=knowledge_resource.id')
                                     ->field('knowledge_resource.*,knowledge_resource.browse_count follow_count,knowledge_resource.file_type type,biz_textbook.name as textbook,"北京出版集团" publisher_name,knowledge_resource.pc_cover file_path')
                                     ->where($check)
                                     ->group('knowledge_resource.id')
                                     ->order("knowledge_resource." . $sort_string)
                                     ->limit($Page->firstRow . ',' . $Page->listRows)
                                     ->select();
                          for ($i = 0; $i < sizeof($result); $i++)
                             $result[$i]['category'] = 'bj';
                         }
                         else if($searchCate == 'teacher' )
                         {
                             if (!empty($filter['type'])) $check['biz_resource.type'] = $filter['type'];
                             $res = $this->getGradeWhere($filter['grade_id']);
                             if('' != $res)
                                 $check['biz_resource.grade_id'] = $res;
                             $res = $this->getCourseWhere($filter['course_id']);
                             if('' != $res)
                                 $check['biz_resource.course_id'] = $res;

                                if($studying_phase==1){
                                    $check['_string'] = 'biz_resource.grade_id<=6';
                                }elseif($studying_phase==2){
                                    $check['_string'] = 'biz_resource.grade_id<=9 and biz_resource.grade_id>=7';
                                }elseif($studying_phase==3){
                                    $check['_string'] = 'biz_resource.grade_id<=12 and biz_resource.grade_id>=10';
                                } 
                             
                         if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                           $Model = M('biz_resource');
                           $check['biz_resource_collect.user_id'] = $USER_ID;
                           $check['biz_resource_collect.user_type'] = $role_con;
                           $count=  $Model ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')->where($check)->select();
                           $count=count($count);    
                           //C('PAGE_SIZE_FRONT')
                           $Page = new \Think\Page($count, 21);
                            //$Page->parameter['keyword'] = urlencode($keyword);
                            $Page->parameter['keyword'] = $keyword;
                            $Page->parameter['course_id'] = $filter['course_id'];
                            $Page->parameter['grade_id'] = $filter['grade_id'];
                            $Page->parameter['textbook_id'] = $filter['school_term_id'];
                            $Page->parameter['sort_column'] = $filter['sort_column'];
                            $Page->parameter['type'] = $filter['type'];
                            //$Page->parameter['resource_cate'] = urlencode($searchCate);
                            $Page->parameter['resource_cate'] = $searchCate;
                            $show = $Page->show();

                           $result = $Model
                                     ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
                                     ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                                     ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_name publisher_name')
                                     ->where($check)
                                     ->order("biz_resource." .$sort_string)
                                     ->limit($Page->firstRow . ',' . $Page->listRows)
                                     ->select();
                          for ($i = 0; $i < sizeof($result); $i++)
                            $result[$i]['category'] = 'teacher';
                         }
                         else if($searchCate == 'all')
                         {

                             //get bj resource SQL start
                             if (!empty($filter['type'])) $check['knowledge_resource.file_type'] = $filter['type'];
                             $res = $this->getGradeWhere($filter['grade_id']);
                             if('' != $res)
                                 $check['knowledge_resource_point.grade'] = $res;
                             $res = $this->getCourseWhere($filter['course_id']);
                             if('' != $res)
                                 $check['knowledge_resource_point.course'] = $res;

                             $check['knowledge_resource.putaway_status']=PUTAWAY;
                             $check['knowledge_resource.status']=APPROVE;
                             if($studying_phase==1){
                                 $check['_string'] = 'knowledge_resource_point.grade<=6';
                             }elseif($studying_phase==2){
                                 $check['_string'] = 'knowledge_resource_point.grade<=9 and knowledge_resource_point.grade>=7';
                             }elseif($studying_phase==3){
                                 $check['_string'] = 'knowledge_resource_point.grade<=12 and knowledge_resource_point.grade>=10';
                             }

                             if (!empty($filter['keyword'])) $check['knowledge_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                             $Model = M('knowledge_resource');
                             $check['knowledge_resource_collect.user_id'] = $USER_ID;
                             $check['knowledge_resource_collect.role'] = $role_con;

                             $bjQuerySQL = $Model
                                 ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
                                 ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id','left')
                                 ->join('knowledge_resource_collect on knowledge_resource_collect.resource_id=knowledge_resource.id')
                                 ->field('\'bj\' as category,knowledge_resource.pc_cover file_path,\'北京出版集团\' publisher_name, \'\' vid_image_path,knowledge_resource.id,knowledge_resource.file_type type,knowledge_resource.name,knowledge_resource.create_at,knowledge_resource.zan_count,knowledge_resource.favorite_count,knowledge_resource.browse_count follow_count,biz_textbook.name as textbook')
                                 ->where($check)
                                 ->group('knowledge_resource.id')
                                 ->select(false);
                         //get bj resource SQL end
                          $check = array();

                         if (!empty($filter['course_id'])) {
                             $res = $this->getCourseWhere($filter['course_id']);
                             if ('' != $res)
                                 $check['biz_resource.course_id'] = $res;
                         }
                         if (!empty($filter['grade_id'])) {
                             $res = $this->getGradeWhere($filter['grade_id']);
                             if('' != $res) {
                                 $check['biz_resource.grade_id'] = $res;
                             }
                         }
                         if (!empty($filter['type'])) {
                             $check['biz_resource.type'] = $filter['type'];
                         }


                        if($studying_phase==1){
                            $check['_string'] = 'biz_resource.grade_id<=6';

                        }elseif($studying_phase==2){
                            $check['_string'] = 'biz_resource.grade_id<=9 and biz_resource.grade_id>=7';

                        }elseif($studying_phase==3){
                            $check['_string'] = 'biz_resource.grade_id<=12 and biz_resource.grade_id>=10';
                        }

                          $check['biz_resource_collect.user_id'] = $USER_ID;
                          $check['biz_resource_collect.user_type'] = $role_con;
                          if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
    					   if(!empty($check['course_id']))
                           {
                           $check['biz_resource.course_id'] = $check['course_id'];
                           $check = $this->array_remove($check,'course_id');
                           }
                           if(!empty($check['grade_id']))
                           {
                           $check['biz_resource.grade_id'] = $check['grade_id'];
                           $check = $this->array_remove($check,'grade_id');
                           }
                           if(!empty($check['school_term_id']))
                           {
                           $check['biz_resource.school_term_id'] = $check['school_term_id'];
                           $check = $this->array_remove($check,'school_term_id');
                           }
                          $Model = M('biz_resource');

                          $count=  $Model->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')->where($check)->field("'teacher' as category,biz_resource.file_path,'北京出版社集团' publisher_name,biz_resource.vid_image_path,".'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')->union($bjQuerySQL)->select();
                            //C('PAGE_SIZE_FRONT')
                          $count=count($count); 
                          $Page = new \Think\Page($count,21 );
                          //$Page->parameter['keyword'] = urlencode($keyword);
                          $Page->parameter['keyword'] = $keyword;
                          $Page->parameter['course_id'] = $filter['course_id'];
                          $Page->parameter['grade_id'] = $filter['grade_id'];
                          $Page->parameter['textbook_id'] = $filter['school_term_id'];
                          $Page->parameter['sort_column'] = $filter['sort_column'];
                          $Page->parameter['type'] = $filter['type'];
                          //$Page->parameter['resource_cate'] = urlencode($searchCate);
                          $Page->parameter['resource_cate'] = $searchCate;
                           $show = $Page->show();


                          $result = $Model
                                   ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
                                   ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                                   ->field("'teacher' as category,biz_resource.file_path,biz_resource.teacher_name publisher_name,biz_resource.vid_image_path," . 'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')
                                   ->where($check)
                                   ->union($bjQuerySQL.' ORDER BY '.$sort_string." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
                                   ->select(); 
                         }
                    

                                 $this->assign('list', $result);
                                 $this->assign('page', $show);

                                 $Model = M('dict_course_copy_resource');
                                 $courses = $Model->order('sort_order asc')->select();
                                 $this->assign('courses', $courses);

                                 $Model = M('dict_grade');
                                 $grades = $Model->select();
                                 $this->assign('grades', $grades);

                                 $TextbookModel = M('biz_textbook');
                                 $c1['course_id'] = $filter['course_id'];
                                 $c1['grade_id'] = $filter['grade_id'];
                                 $textbooks = $TextbookModel->where($c1)->select();
                                 $this->assign('textbooks', $textbooks);

                                 $this->assign('category', $filter['studying_phase']);
                                 $this->assign('course_id', $course_select_id);
                                 $this->assign('grade_id', $filter['grade_id']);
                                 $this->assign('textbook_id', $filter['school_term_id']);
                                 $this->assign('type', $filter['type']);
                                 $this->assign('keyword', $filter['keyword']);
                                 $this->assign('sort_column', $filter['sort_column']);
                                 $this->assign('resource_cat_val', $searchCate);
                                 $this->display_nocache();
                }

    function getSortString($sort){
        $filter['sort_column'] = $sort;
           switch ($sort) {
               case 0:
                   $sort_string= "zan_count desc";
                   break;
               case 1:
                   $sort_string= "zan_count asc";
                   break;
               case 2:
                   $sort_string= "favorite_count desc";
                   break;
               case 3:
                   $sort_string= "favorite_count asc";
                   break;
               case 4:
                   $sort_string= "follow_count desc";
                   break;
               case 5:
                   $sort_string= "follow_count asc";
                   break;
               case 6:
                   $sort_string= "create_at desc";
                   break;
               case 7:
                   $sort_string= "create_at asc";
                   break;
               default:
                   $sort_string= "create_at desc";
                   $filter['sort_column']=6;
                   break;
           }
      return array($sort_string,$filter['sort_column']);
    }
	    //京版资源详情
    public function bjResourceDetails( $id, $type=2)
    {
            if ($type != 1) {
                $isAuth = A('Home/Teach')->isAuth($this->c_a);

                if (!$isAuth) { //如果访问的模块没有权限
                    redirect(U('Teach/index1?auth_error=1'));
                }
            }
            A('Home/Common')->getUserIdRole($userId,$role);
	    switch($role)
		{
                    case ROLE_TEACHER:layout('teacher_layout_withouticon');
                        $USER_ID=session('teacher.id');
                        break;
                    case ROLE_STUDENT:layout('student_layout_withouticon');
                        $USER_ID=session('student.id');
                        break;
                    case ROLE_PARENT:layout('parent_layout_withouticon');
                        $USER_ID=session('parent.id');
                        break;
                    default :
                        redirect(U('Index/index'));
                        break;
		}




        $this->assign('module', '教学+');
        $this->assign('nav', '京版资源');
        $this->assign('navicon', 'jingbanziyuan');


        $id = intval($_GET['id']);
        if(!$id){
            redirect(U('Index/systemError'));
        }
        $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('knowledge_resource');
        $where['knowledge_resource.id'] = $id;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $result = $Model
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id','left')
            ->join('dict_course_copy_resource on knowledge_resource_point.course=dict_course_copy_resource.id','left')
            ->join('dict_grade on knowledge_resource_point.grade=dict_grade.id','left')
            ->field('knowledge_resource.*,knowledge_resource.file_type type,biz_textbook.name as textbook,dict_course_copy_resource.course_name,dict_grade.grade')
            ->where($where)
            ->find();
        if(!empty($result)){
            $this->assign('subnav', $result['name']);
            $this->assign('data', $result);

            //拿到关联表的数据
            $contact_result=$Model->where($where)->join("knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id")
                ->field("knowledge_resource_file_contact.*")->select();
            $this->assign('contact_data', $contact_result);


            /*if(empty($result)){
                redirect(U('Index/systemError'));
            } */

            //观看次数+1
            if(session('teacher') != 'youke') {
                $Model->where("id=$id")->setInc('browse_count', 1);
            }

            $FavorModel = M('knowledge_resource_collect');
            $favorData['resource_id'] = $id;
            $favorData['role'] = $role-2;
            $favorData['user_id'] = $USER_ID;
            $existedFavor = $FavorModel->where($favorData)->find();
            $existedFavor = empty($existedFavor) ? 'no' : 'yes';
            $this->assign('existedFavor', $existedFavor);


            //$arr = explode("/", $result[file_path]);
            //$fileName = $arr[1];
            $arr = explode(".", $result[file_path]);
            $this->assign('localPdffileName', '../../../Resources/jb/' . $arr[0] . ".pdf");
            $this->assign('data', $result);
            $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
            $this->display_nocache();
        }
    }
	    //资源详情
    public function resourceDetails($id=array(),$from="",$type=2)
    {

        A('Home/Common')->getUserIdRole($userId,$role);
        switch($role){
                case ROLE_TEACHER:layout('teacher_layout_withouticon');
                    $USER_ID=session('teacher.id');
                    break;
                case ROLE_STUDENT:layout('student_layout_withouticon');
                    $USER_ID=session('student.id');
                    break;
                case ROLE_PARENT:layout('parent_layout_withouticon');
                    $USER_ID=session('parent.id');
                    break;
                default :
                        redirect(U('Index/index'));
                        break;
        }
        if($type!=1) {
            $isAuth = A('Home/Common')->isAuth($this->c_a);

            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Teach/index1?auth_error=1'));
            }

        }


        $this->assign('module', '教学+');
        $this->assign('nav', '');
        $this->assign('navicon', 'jiaoshiziyuan');

        $id=intval($id);
        if(!empty($id)){
            //$id = $_GET['id'];
            $id = getParameter('id', 'int',false);
            if(!$id){
                redirect(U('Index/systemError'));
            }
            $teacher_online=1;
        }else{
            redirect(U('Index/systemError'));
        }
        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);
        if(!empty( $_GET['f']))
        $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course_copy_resource on biz_resource.course_id=dict_course_copy_resource.id','left')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course_copy_resource.course_name,dict_grade.grade')
            ->where("biz_resource.id=".$id)        
            ->find(); 
        if(!empty($result)){
            //判断资源是否为空  
            /*if(empty($result)){
                redirect(U('Index/systemError'));
            }*/
            $result['type'] = strtolower($result['type']);        
            $this->assign('subnav', $result['name']); 
            $this->assign('data', $result);

            //拿到关联表的数据
            $contact_result=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
                ->field("biz_resource_contact.*")->select();          
            $this->assign('contact_data', $contact_result);  
            //观看次数+1
            $Model->where("id=$id")->setInc('follow_count', 1); 
            
            //$User = M("auth_teacher");
            //$User->where("id=" . $result['teacher_id'])->setInc("points", 1);// 积分加1 
            /*
            //判断登陆者是否和发布者是一人 
             if($result['teacher_id']==session('teacher.id')){  
                 $this->assign('operation_status',1);
             }else{                                             
                 $this->assign('operation_status',2);
             }*/
                
                //判断我是否赞过和收藏过
                $ZanModel = M('biz_resource_zan');
                $zanData['resource_id'] = $id;
                $zanData['user_type'] = $role-2;
                $zanData['user_id'] = $USER_ID;
                $existedZan = $ZanModel->where($zanData)->find();
                $existedZan = empty($existedZan) ? 'no' : 'yes'; 
                $this->assign('existedZan', $existedZan);


                $FavorModel = M('biz_resource_collect');
                $favorData['resource_id'] = $id;
                $favorData['user_type'] = $role-2;
                $favorData['user_id'] = $USER_ID;
                $existedFavor = $FavorModel->where($favorData)->find();
                $existedFavor = empty($existedFavor) ? 'no' : 'yes';
                $this->assign('existedFavor', $existedFavor);  
                
                $this->assign('role',$role);    
        }
        $this->display(); 
    }
    
    public function mybjResourceDetails($cate,$id) {   
    if($cate == 'bj')
     $id=intval($id);
     $this->bjResourceDetails($id,1);
   }
   
   
    public function myResourceDetails($id,$from=""){ 
        if(!empty($_GET['from']))
        $this->resourceDetails($id,$_GET['from'],1);
        else
        $this->resourceDetails($id,'myfavor',1);
    }
    
    
    function getGradeWhere($gradeId){
        if($gradeId==14){
            return 14;
        }elseif($gradeId==15){
            return 15;
        }elseif($gradeId==16){
            return 16;
        }else{
         if($gradeId>0 && $gradeId<7){
              return array('in','(14,'. $gradeId .')');
          }elseif($gradeId>6 && $gradeId<10){
              return array('in','(15,'. $gradeId .')');
          }elseif($gradeId>9 && $gradeId<13){
              return array('in','(16,'. $gradeId .')');
          }
        }
        return $gradeId < 0?$gradeId : '';
    }
    
    
    function getCourseWhere($courseId){
        $all_course_id=32;
        if($courseId==$all_course_id){
           return '';
        }
        else{
           if(!empty($courseId))
           return array('in', $all_course_id.','.$courseId );
           else
           return '';
        }
    }
}