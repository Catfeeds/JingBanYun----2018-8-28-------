<?php
namespace Common\Model;
use Think\Model; 
define('CONDITION_STATUS_CLOSE',FALSE);
define('CONDITION_STATUS_OPEN',TRUE);

class Biz_bj_resourcesModel extends Model{

    public    $model='';
    protected $tableName = 'biz_bj_resources';
    
    
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    
    //创建京版资源零时表
    public function createBjResourceTempTable(){     
        $drop_temp_table_sql='drop table if exists bj_resourec_temp';
        $this->model->execute($drop_temp_table_sql); 
        $sql='create temporary table bj_resourec_temp(biz_bj_resources_id int UNSIGNED not null,studying_phase int UNSIGNED not null,course_id int UNSIGNED not null,course varchar(32) not null,'
                . 'grade_id int UNSIGNED not null,grade varchar(32),school_term int UNSIGNED not null,file_type varchar(32) not null)engine=memory charset=utf8';
        $this->model->execute($sql);   
    }
    
    
    /*
     * 插入临时表数据
     */
    public function insertTempDataByCondtion($check=array()){
        $check['biz_bj_resources.status'] = 2;
        $check['biz_bj_resources.isDisplay'] = 1;
        
        $build_sql = $this->model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
            ->join('dict_course on dict_course.id=biz_bj_resources.course_id','left')
            ->join('dict_grade on dict_grade.id=biz_bj_resources.grade_id')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook')
            ->where($check)             
            ->field('biz_bj_resources.id,case when dict_grade.id<=6 or dict_grade.id = 14 then 1 when dict_grade.id>=7 and dict_grade.id<=9 or dict_grade.id = 15 then 2 when dict_grade.id>=10 and dict_grade.id<=12 or dict_grade.id = 16 then 3 end cat,'
                    . 'biz_bj_resources.course_id,dict_course.course_name,dict_grade.id grade_id,dict_grade.grade,biz_textbook.school_term,biz_bj_resources.type')
            ->buildsql();   
        $last_sql='insert into bj_resourec_temp '.$build_sql;    
        $this->model->execute($last_sql); 
    }
    
    
    //获得条件所有可用的学科总数
    public function getCondition($group=''){
        switch ($group) { 
            case 'studying_phase': 
                $group_str='studying_phase';
                break;
            case 'course': 
                $group_str='course';
                break;
            case 'grade': 
                $group_str='grade';
                break;
            case 'school_term':    
                $group_str='school_term';
                break;
            case 'file_type': 
                $group_str='file_type';
                break; 
        }
        $result=M()->query('select * from bj_resourec_temp group by '.$group_str);
        return $result;
    }
    
            
    /*
     * 某得某个年级
     * 返回二维数据
     */
    public function getGrade($grade_id){
        $grade_model=M('dict_grade');
        $grade_model->where('id='.$grade_id)->select();//
    }
    
    
    public function getSubResourceHTMLList($info)
    {
        $oss_path = C('oss_path');
        $infoid = $info['id'];
        $result = M('biz_bj_resource_contact')->where("biz_bj_resource_id=$infoid")->select();
        $htmlList = "";
        switch($info['type'])
        {
            case 'HTML': $htmlList = $info['content'];
                break;
            case 'ppt':
            case 'word':$i=1;
                foreach($result as &$val)
                {
                    $pdf_arr=explode('.',basename($val['resource_path']));
                    $pdf_path=$pdf_arr[0];
                    $htmlList = $htmlList . '<a href="' .$oss_path .'bj/'.$info['id'] .'/'.$val['id'] .'/'. $pdf_path .'.pdf">' . '查看第' .$i . '个资源</a></br>' ;
                    $i++;
                }
                break;
            case 'pdf':
                $i=1;
                foreach($result as &$val)
                {
                    $htmlList = $htmlList . '<a href="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</a></br>' ;
                    $i++;
                }
                break;
            case 'image':
                foreach($result as &$val)
                {
                    $htmlList = $htmlList . '<img src="' .$oss_path .$val['resource_path'] .'"></img></br>' ;
                }
                break;
            case 'video':  $i=1;
                foreach($result as &$val)
                {
                    $htmlList = $htmlList . '<video controls webkit-playsinline style="background:rgba(0,0,0,0.4)" width="100%" src="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</video></br>' ;
                    $i++;
                }
                break;
            case 'audio':  $i=1;
                foreach($result as &$val)
                {
                    $htmlList = $htmlList . '<audio src="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</audio></br>' ;
                    $i++;
                }
                break;
        }
        return $htmlList;
    }
    /*
     * 获得某个资源
     * @id  资源id
     */
    public function getResource($id){
        $model=$this->model;
        $result=$model->field('id,name,type,textbook_id,grade_id,course_id,zan_count,favorite_count,follow_count,create_at,file_path,vid,flag')
            ->where("id=".$id)->find();
        return $result;
    }

    /*
     * 删除某个资源
     * @id   资源id
     */
    public function deleteResource($id){
        $model=$this->model;
        if($model->where("id=".$id)->delete()){
            return true;
        }else{
            return false;
        }
    }

    public function getResourceList($queryParameters, $pageIndex=1, $pageSize=20, $userId='', $role='')
    {
        //analysis paras
        if(!empty($queryParameters['course_id']))
          $where[$this->tableName.'.course_id'] = $queryParameters['course_id'];
        if(!empty($queryParameters['grade_id']))
          $where[$this->tableName.'.grade_id'] = $queryParameters['grade_id'];
        if(!empty($queryParameters['school_term']))
            $where['biz_textbook.school_term'] = $queryParameters['school_term'];
        if(!empty($queryParameters['textbook_id']))
          $where['textbook_id'] = $queryParameters['textbook_id'];
        if(!empty($queryParameters['keyword']))
          $where['keyword'] = array('like','%'.$queryParameters['keyword'].'%');
        if(!empty($queryParameters['channel_id']))
          $where['channel_id'] = $queryParameters['channel_id'];
        if(!empty($queryParameters['type']))
          $where['type'] = $queryParameters['type'];
        if(!empty($userId) && isset($role))
        {
            $where['biz_bj_resource_collect.user_id'] = $userId;
            $where['biz_bj_resource_collect.role'] = $role;
            $join[] = 'INNER JOIN biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id';
        }
        $join[] = 'dict_course ON dict_course.id = '.$this->tableName .'.course_id';
        $join[] = 'dict_grade ON  dict_grade.id = '.$this->tableName .'.grade_id';
        $join[] = 'biz_textbook ON  biz_textbook.id = '.$this->tableName .'.textbook_id';

        $where['isDisplay'] = 1;
        $where['status'] = 2;
        $where['_string'] = 'type!=\'condensed\' AND type!=\'swf\'';
        $result = $this->model->join($join)
                    ->order('create_at desc')
                    ->where($where)
                    ->page($pageIndex.','.$pageSize)
                    ->field($this->tableName.'.id,'.
                            $this->tableName.'.name,'.
                            $this->tableName.'.type,'.
                            $this->tableName.'.zan_count,'.
                            $this->tableName.'.favorite_count,'.
                            $this->tableName.'.follow_count,'.
                            $this->tableName.'.download_count,'.
                            $this->tableName.'.create_at,'.
                            'biz_textbook.name as textbook,'.
                            'dict_grade.grade,'.
                            'dict_grade.chinese_code,'.
                            'dict_course.course_name as course,'.
                            $this->tableName.'.file_path,'.
                            $this->tableName.'.vid,'.
                            $this->tableName.'.oss_path,'.
                            'biz_textbook.school_term,'.
                            'if(UNIX_TIMESTAMP(NOW())-604800>'.$this->tableName.'.create_at,\'no\',\'yes\') is_new')
                    ->select();

        return $result;


    }

    public function getResourceDetails($id)
    {       
        $result = $this->model
            ->join('biz_textbook on '.$this->tableName.'.textbook_id=biz_textbook.id','left')
            ->join('dict_course on '.$this->tableName.'.course_id=dict_course.id','left')
            ->join('dict_grade on '.$this->tableName.'.grade_id=dict_grade.id','left')
            ->field($this->tableName.'.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->where($this->tableName.'.status=2 and '.$this->tableName.".id=$id")
            ->find();       
        if(!empty($result)){
            $result['content'] = $this->getSubResourceHTMLList($result);
        }
        return $result;
    }

    public function getContactResourcePath($id)
    {
        $oss_path=C('oss_path');
        //拿到关联表的数据
        $contact_result=$this->model->where("biz_bj_resources.status=2 and biz_bj_resources.id=".$id)->join("biz_bj_resource_contact on biz_bj_resource_contact.biz_bj_resource_id=biz_bj_resources.id")
            ->field("biz_bj_resource_contact.*")->select();
            foreach($contact_result as $key=>$value){
                if($contact_result[$key]['resource_path']==''){
                    $contact_result[$key]['resource_path']=$value['vid_fullpath'];
                }else{
                    $contact_result[$key]['resource_path']=$oss_path.$contact_result[$key]['resource_path'];
                }
            }
        return $contact_result;
    }
    /*
    * 获取资源是否点赞
    * id 资源ID
    * user_id 用户ID
    * role 角色
    *
    */
    public function getIsZan($id, $userId, $role)
    {
        $where['resource_id'] = $id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role - 2;
        $result = M('biz_bj_resource_zan')->where($where)->field('1')->find();
        return empty($result) ? 'no' : 'yes';
    }

    /*
    * 获取资源是否收藏
    * id 资源ID
    * user_id 用户ID
    * role 角色
    */
    public function getIsFavor($id, $userId, $role)
    {
        $where['resource_id'] = $id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role - 2;
        $result = M('biz_bj_resource_collect')->where($where)->field('1')->find();
        return empty($result) ? 'no' : 'yes';
    }

    /*
     * 观看数+1
     * id 资源ID
     */
    public function setBrowseCountPlusOne($id)
    {
        $this->model->where('id=' . $id)->setInc('follow_count', 1);
    }
    
    
    //得到所有类型有多少习题
    function getAllResourceCount(){
        $allResourceType=$this->getJbresourceType();      
        $resource_type_number_arr=array();
        $result=$this->getJbresourceCount(CONDITION_STATUS_CLOSE);
        $resource_type_number_arr[]=array('key'=>'京版资源总数','value'=>$result);
        
        foreach($allResourceType as $key=>$value){
            $result=$this->getJbresourceCount(CONDITION_STATUS_OPEN,$key);
            $resource_type_number_arr[]=array('key'=>$value,'value'=>$result);
        }
        return $resource_type_number_arr;
    }
    
    
    //京版资源所有类型
    function getJbresourceType(){
        $bj_resource_category=array('video'=>'视频','audio'=>'音频','image'=>'图片','word'=>'word','ppt'=>'ppt','pdf'=>'pdf','swf'=>'swf','condensed'=>'压缩包');
        return $bj_resource_category;
    }

    
    //获得所有京版资源的数量
    function getJbresourceCount($flag=false,$resource_type=''){
        if($flag==false){
            $data=$this->model->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
                    ->join('dict_course on biz_bj_resources.course_id=dict_course.id')
                    ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id')
                    ->count('biz_bj_resources.id');        
        }else{
            $where['type']=$resource_type;
            $data=$this->model->where($where)
                    ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
                    ->join('dict_course on biz_bj_resources.course_id=dict_course.id')
                    ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id')
                    ->count('biz_bj_resources.id');
        }
        return $data;
    }
    
    //按照条件查找出资源的数量
    function getJbresourceInfoCount($condition=array()){
        $result = $this->model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id')
            ->field("count(biz_bj_resources.id) count") 
            ->where($condition)
            ->find();
        return $result;
    }
}