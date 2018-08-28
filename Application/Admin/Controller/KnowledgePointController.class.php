<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

define('TOP_LEVEL',1);
define('MIN_LEVEL',4);
define('USER_MALICE_DATA','数据异常');  //恶意数据

class KnowledgePointController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
        $this->model=D('Knowledge_resource');
    }
          
    
    /*
     * 知识点列表
     */
    function KnowledgePointList(){
        if (!session('?admin')) redirect(U('Login/login'));
        $textbook_model=D('Biz_textbook');
        $knowledge_model = D('Knowledge_resource');
        $publishing_house_id = getParameter('publishing_house_id','int',false);
        //获取教材版本
        $publishingDate = $knowledge_model->publishing($publishing_house_id);


        if(!empty($publishing_house_id)){
            $knowledge_tree = $knowledge_model->getCourseByPublishing($publishingDate['publishing_house_id']); //获取相应出版社的所有学科
        }
        foreach($knowledge_tree as $key=>$course_value){
            $knowledge_tree[$key]['child']=$textbook_model->getTextbookByCourse($course_value['id']);//根据学科id获取该学科下的电子课本
        }
        $this->assign('publishingDate',$publishingDate); //获取出版社信息  北京出版社信息
        $this->assign('knowledge_tree',$knowledge_tree);    //echo "<pre>";print_r($knowledge_tree);die;
        $this->display('Knowledge/knowledgeMgmt');
    }
    
    
    /*
    * 获得下一级知识点
    */
   function getNextLevelKnowledge(){ 
       $knowledge_id=getParameter('id','int');
       $level=getParameter('level','int',false);
       if($level==TOP_LEVEL){

            $result=$this->model->getKnowledgeChapter(0,0,$knowledge_id);
       }else{
           $result=$this->model->getKnowledgePointByParentId($knowledge_id);
       }
       foreach ($result as $k=>$item) {
           $result[$k]['knowledge_name'] = stripslashes($result[$k]['knowledge_name']);
       }
       $this->showjson(200,'',$result);
   }
    
    
    /*
     * 根据层级获得数据(6级 1学科,2分册,3章,4节,5知识点,6子级知识点)
     */
    public function getKnowledgePoint(){        
        $level=getParameter('level','int');
        $keyword=getParameter('keyword','str');     
        $result=$this->model->getKnowlegePointData($level,$keyword);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 根据某个未知的子级id获得父级树形数据
     * @param   id     知识点ID
     */
    public function getKnowledgeTreeData(){ 
        $id=getParameter('id','int');
        $result=$this->model->getParentTreeData($id);
        $this->showjson(200,'',$result);
    }
     
    
    /*
     * 删除某个知识点
     */
    public function deleteKnowledgePoint(){
        $id=getParameter('id','int');
        $result=$this->model->getOneSimpleKnowledgePoint($id);
        if($this->model->deleteKnowledgePoint($id)){


            //修改父级的knowledge_count字段
            //$result=$this->model->getOneSimpleKnowledgePoint($id);
            /*if($result['parent_id'] !== 0){
                $results=$this->model->getOneSimpleKnowledgePoint($result['parent_id']);
                $resouc['knowledge_count'] = $results['knowledge_count']-1;
                if($this->model->updateKnowledgePointData($results['id'],$resouc)){
                    $this->showjson(200);
                }else{
                    $this->showjson(402,COMMON_FAILED_MESSAGE);
                }
            }*/

            //删除多知识点表knowledge_resource_point中的数据。
            //获得当前ID是否是章或节。。。的ID，拼接where条件
            switch ($result['level']) {
                case 1:
                    $where['chapter'] = $id;
                    break;
                case 2:
                    $where['festival'] = $id;
                    break;
                case 3:
                    $where['knowledge'] = $id;
                    break;
                case 4:
                    $where['child_knowledge'] = $id;
                    break;
            }
            //查询多知识点表中是否有和要删除的知识点ID关联的数据
            if(!empty($this->model->selectKnowledgeResourcePoint($where))){
                if($this->model->deleteKnowledgeResourcePoint($where)){
                    $this->showjson(200);
                }else{
                    $this->showjson(402,COMMON_FAILED_MESSAGE);
                }
            }
            $this->showjson(200);
        }else{
           $this->showjson(401); 
        } 
    }
    
    
    /*
     * 添加某个知识点
     */
    public function addKnowledgePoint(){
        $textbook_model=D('Biz_textbook');
        $is_chapter = getParameter('is_chapter','str');
//        $textbook_id=getParameter('textbook_id','int',false);
        $pid=getParameter('id','int',false);
//        var_dump($is_chapter);die;
        if($is_chapter == 'yes'){
            $textbook_id= $pid;
        }
        $name=getParameter('name','str');
        $html=$_POST['html'];
        $sort=getParameter('sort','int');
//        var_dump($textbook_id);die;
        if(!$textbook_id && !$pid){
            $this->showjson(401,USER_MALICE_DATA);  
        }elseif($textbook_id){
            $textbook_result=$textbook_model->getTextBookDetails($textbook_id);
            if(empty($textbook_result)){
                $this->showjson(402,USER_MALICE_DATA);  
            }
            $data['course_id']=$textbook_result['course_id'];
            $data['grade_id']=$textbook_result['grade_id'];
            $data['textbook_id']=$textbook_result['id'];
            $data['level']=1;
        }else{
            $result=$this->model->getOneSimpleKnowledgePoint($pid);
            if($result==MIN_LEVEL){
                $this->showjson(403,USER_MALICE_DATA); 
            }
            $data['level']=$result['level']+1;
            $data['parent_id']=$pid;
        }
        $data['html'] = $html;
        $data['knowledge_name']=$name;
        $data['create_at']=time();
        $data['sort']=$sort;
        if($insert_id=$this->model->addKnowledgePointData($data)){


            //修改父级的knowledge_count字段
            /*if(!empty($result)){
                $resouc['knowledge_count'] = $result['knowledge_count']+1;
                if($this->model->updateKnowledgePointData($result['id'],$resouc)){
                    $data['insert_id']=$insert_id;
                    $this->showjson(200,'',$data);
                }else{
                    $this->showjson(402,COMMON_FAILED_MESSAGE);
                }
            }*/
            $data['insert_id']=$insert_id;
            $this->showjson(200,'',$data);
        }else{
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }
    }
    
    
    /*
     * 修改某个知识点
     */
    public function updateKnowledgePoint(){
        $id=getParameter('id','int'); 
        $name=getParameter('name','str');
        $html=$_POST['html'];
        $sort=getParameter('sort','int');
        $result=$this->model->getOneSimpleKnowledgePoint($id);
        if(empty($result)){
           $this->showjson(401,USER_MALICE_DATA); 
        }
        $data['html']=$html;
        $data['knowledge_name']=$name;
        $data['sort']=$sort;
        if($this->model->updateKnowledgePointData($id,$data)){
            $this->showjson(200);
        }else{
            $this->showjson(402,COMMON_FAILED_MESSAGE);
        }
    }
}

