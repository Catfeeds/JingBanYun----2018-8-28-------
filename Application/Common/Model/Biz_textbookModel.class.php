<?php
namespace Common\Model;
use Think\Model;

class Biz_textbookModel extends Model{

    public    $model='';
    protected $tableName = 'biz_textbook';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    public function getTextBookList($queryParameters, $pageIndex=1, $pageSize=20)
    {
        if(!empty($queryParameters['course_id']))
          $where['biz_textbook.course_id'] = $queryParameters['course_id'];
        if(!empty($queryParameters['grade_id']))
          $where['biz_textbook.grade_id'] = $queryParameters['grade_id'];
        if(!empty($queryParameters['school_term']))
            $where['biz_textbook.school_term'] = $queryParameters['school_term'];
        $where['biz_textbook.has_ebook'] = 1 ;
        $where['biz_textbook.flag'] = 1 ;

        $join[] = 'dict_course ON dict_course.id = '.$this->tableName .'.course_id';
        $join[] = 'dict_grade ON  dict_grade.id = '.$this->tableName .'.grade_id';

        $result = $this->model->join($join)
                    ->where($where)
                    ->order('biz_textbook.sort_order asc')
                    ->page($pageIndex.','.$pageSize)
                    ->field('biz_textbook.id,biz_textbook.cover,server_path,name')
                    ->select();
        foreach ($result as $key=>$val) {
            $result[$key]['cover'] = $val['server_path'] . "/content/2.png";
            unset($result[$key]['server_path']);
        }
        return $result;
    }

    public function getAllTextBookList($queryParameters)
    {
        if(!empty($queryParameters['course_id']))
            $where['biz_textbook.course_id'] = $queryParameters['course_id'];
        if(!empty($queryParameters['grade_id']))
            $where['biz_textbook.grade_id'] = $queryParameters['grade_id'];
        if(!empty($queryParameters['school_term']))
            $where['biz_textbook.school_term'] = $queryParameters['school_term'];
        $where['biz_textbook.flag'] = 1 ;

        $join[] = 'dict_course ON dict_course.id = '.$this->tableName .'.course_id';
        $join[] = 'dict_grade ON  dict_grade.id = '.$this->tableName .'.grade_id';

        $result = $this->model->join($join)
            ->where($where)
            ->order('biz_textbook.sort_order asc')
            ->field('biz_textbook.id,replace(biz_textbook.name,dict_course.course_name,\'\') name')
            ->select();
        return $result;
    }
    public function getTextBookDetails($id)
    {
        $result = $this->model->where('id='.$id)->find();
        return $result;
    }

    public function getAvailableSchoolTerm($courseId,$gradeId)
    {
        if($courseId != '')
        $where['course_id'] = $courseId;
        if($gradeId != '')
        $where['grade_id'] = $gradeId;
        $where['biz_textbook.has_ebook'] = 1 ;
        $where['biz_textbook.flag'] = 1 ;
        return $this->model->where($where)->field('distinct school_term as school_term')->select();

    }

    public function getTextbookByCourse($course_id){
        $where['course_id']=$course_id;
        //$where['flag']=1;
        $result=$this->model->where($where)->field('id,name,grade_id,course_id,school_term')->select();
        return $result;
    }
    
    /*
     *仅限前台知识树使用
     */
    public function getTextbookByCourse2($course_id,$gradeId=0){
        if(!empty($gradeId))
            $where['biz_textbook.grade_id']=$gradeId;
        $where['biz_textbook.course_id']=$course_id;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $result=$this->model
            ->join('knowledge_resource_point on knowledge_resource_point.textbook = biz_textbook.id')
            ->join('knowledge_resource on knowledge_resource.id = knowledge_resource_point.knowledge_resource_id')
            ->where($where)->field('biz_textbook.id,biz_textbook.name,biz_textbook.grade_id,biz_textbook.course_id,biz_textbook.school_term')->group('biz_textbook.id')->order('biz_textbook.grade_id,biz_textbook.school_term')->select();//echo M()->getLastSql();
        return $result;
    }

    /*
     *仅限前台知识树使用 根据当前册获取对应的章节
     */
    public function getChapterByTextbook($textbook_id){
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $where['knowledge_resource_point.textbook'] = $textbook_id;
        $where['knowledge_point.level'] = '1';
        $result=M('knowledge_resource_point')
            ->join('knowledge_resource on knowledge_resource.id = knowledge_resource_point.knowledge_resource_id')
            ->join('knowledge_point on knowledge_point.id = knowledge_resource_point.chapter')
            ->where($where)->field('knowledge_resource_point.chapter')->group('knowledge_resource_point.chapter')->select();//echo M()->getLastSql();
        return $result;
    }


    public function getTextbookInfo($course_id,$grade_id,$school_term){
        $where['course_id']=$course_id;
        $where['grade_id']=$grade_id;
        $where['school_term']=$school_term;
        $result=$this->model->where($where)->find();        
        return $result;
    }
}