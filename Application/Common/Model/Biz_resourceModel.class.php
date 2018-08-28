<?php
namespace Common\Model;
use Think\Model; 
define('CONDITION_STATUS_CLOSE',FALSE);
define('CONDITION_STATUS_OPEN',TRUE);
define('ROLE_NUMBER',2);
define('BEIJING_DISTRICT_ID',1);

define('ICON_VIDEO','http://'.WEB_URL.'/Public/img/app/icon_video.png');
define('ICON_PDF','http://'.WEB_URL.'/Public/img/app/icon_pdf.png');
define('ICON_WORD','http://'.WEB_URL.'/Public/img/app/icon_word.png');
define('ICON_AUDIO','http://'.WEB_URL.'/Public/img/app/icon_mp3.png');
define('ICON_IMAGE','http://'.WEB_URL.'/Public/img/app/icon_pic.png');
define('ICON_PPT','http://'.WEB_URL.'/Public/img/app/icon_ppt.png');
define('ICON_ZIP','http://'.WEB_URL.'/Public/img/app/icon_zip.png');
define('ICON_SWF','http://'.WEB_URL.'/Public/img/app/icon_swf.png');
define('ICON_HTML','http://'.WEB_URL.'/Public/img/app/icon_html.png');

class Biz_resourceModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_resource';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    } 


    /*
     * 获得某个资源的简单信息
     */
    public function getResourceSimpleInfo($id){
        $where['id']=$id;
        $result=$this->model->where($where)->find();
        return $result;
    }

    
    /*
     * 创建教师资源临时表
     */
    public function createResourceTempTable(){
        $drop_temp_table_sql='drop table if exists resourec_temp';
        $this->model->execute($drop_temp_table_sql); 
        $sql='create temporary table resourec_temp(biz_resource_id int UNSIGNED not null,course_id int UNSIGNED not null,course varchar(32) not null,'
                . 'grade_id int UNSIGNED not null,grade varchar(32),school_term int UNSIGNED not null,file_type varchar(32) not null)engine=memory charset=utf8';
        $this->model->execute($sql);  
    }
    
    
    /*
     * 插入临时表数据
     */
    public function  insertTempDataByCondtion($condition=array()){

        $condition['biz_resource.status']=2;
        if($condition['dict_schoollist.district_id']==-1){
            $condition['dict_schoollist.city_id']=array('neq',BEIJING_DISTRICT_ID);
            unset($condition['dict_schoollist.district_id']);
        }
        if($condition['biz_resource.grade_id']>0 && $condition['biz_resource.grade_id']<7){
            $condition['_string']="biz_resource.grade_id in (14,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>6 && $condition['biz_resource.grade_id']<10){
            $condition['_string']="biz_resource.grade_id in (15,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>9 && $condition['biz_resource.grade_id']<13){
            $condition['_string']="biz_resource.grade_id in (16,".$condition['biz_resource.grade_id'].")";
        }
        
        $build_sql=$this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
                    ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                    ->join('dict_course on dict_course.id=biz_resource.course_id','left')
                    ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
                    ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
                    ->field('biz_resource.id,biz_resource.course_id,dict_course.course_name,dict_grade.id grade_id,dict_grade.grade,biz_textbook.school_term,biz_resource.type')
                    ->where($condition) 
                    ->buildsql();       
        $last_sql='insert into resourec_temp '.$build_sql;    
        $this->model->execute($last_sql); 
    }
    
    
    /*
     * 得到某个地区信息
     */
    public function getDistrictInfo($id){
        $model=M('dict_citydistrict');
        $where['id']=$id;
        $result=$model->where($where)->find();
        return $result;
    }
    
    
    /*
     * 得到地区
     */
    public function getDistrict($condition=array()){ 
        $global_result=array();
        $index=0;
        while($index<2){
            $index++;
            if($index==1){
                $condition['dict_schoollist.city_id']=array('eq',BEIJING_DISTRICT_ID); 
            }else{
                $condition['dict_schoollist.city_id']=array('neq',BEIJING_DISTRICT_ID);
            }
            $result=$this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
                        ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                        ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
                        ->join('dict_course on dict_course.id=biz_resource.course_id','left')
                        ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
                        ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
                        ->field('dict_citydistrict.id,dict_citydistrict.name')
                        ->where($condition) 
                        ->group('dict_citydistrict.id')
                        ->select(); 
            if($index!=1){
                if(!empty($result)){        
                    $result=array(array('id'=>'-1','name'=>'其他'));
                    $global_result=array_merge($global_result,$result);
                }
            }else{
                $global_result=$result;
            }
        } 
        return $global_result;
    }
    
    
    /*
     * 获得资源列表的条件
     */
    public function getCondition($group=''){
        switch ($group) {  
            case 'course': 
                $group_str='course';
                $order_str='course_id';
                break;
            case 'grade': 
                $group_str='grade';
                $order_str='grade_id';
                break;
            case 'school_term':    
                $group_str='school_term';
                $order_str='school_term';
                break;
            case 'file_type': 
                $group_str='file_type'; 
                $order_str='file_type';
                break; 
        }
        $result=M()->query('select * from resourec_temp group by '.$group_str." order by ".$order_str);       
        return $result;
    }
    
    
            
    
    
    /*
     * 获得某个发布人发布的全部资源
     * @teach_id    教师id
     */
    public function getTeachAllResource($teach_id){
        $model=$this->model;
        $result=$model->field('id,name,type,textbook_id,grade_id,course_id,teacher_id,teacher_name,zan_count,favorite_count,follow_count,create_at,file_path,vid,flag')
                    ->where("teacher_id=".$teach_id)->order('create_at desc')->select(); 
        return $result;
    }
    
    /*
     * 获得某个资源 
     * @id  资源id
     */
   public function getResource($id){   
        $model=$this->model;
        $result=$model->field('id,name,type,textbook_id,grade_id,course_id,teacher_id,teacher_name,zan_count,favorite_count,follow_count,create_at,file_path,vid,flag')
                    ->where("id=".$id)->find();
        return $result;
   }
       
   /*
    * 删除某个资源
    * @id   资源id
    */
   public function deelteResource($id){
       $model=$this->model;
       if($model->where("id=".$id)->delete()){
           return true;
       }else{
           return false;
       }
   }

   public function getPublishedResource($queryParameters, $pageIndex=1, $pageSize=20, $userId='', $role='',$publishUserId='',$isPc = 0)
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
       if(!empty($queryParameters['type']))
           $where['type'] = $queryParameters['type'];
       if(!empty($userId) && isset($role))
       {
           $where['biz_resource_collect.user_id'] = $userId;
           $where['biz_resource_collect.user_type'] = $role;
           $join[] = 'INNER JOIN biz_resource_collect on biz_resource_collect.resource_id='.$this->tableName.'.id';
       }
       if(!empty($publishUserId))
       {
           $where['teacher_id'] = $publishUserId;
       }
       $join[] = 'dict_course ON dict_course.id = '.$this->tableName .'.course_id';
       $join[] = 'dict_grade ON  dict_grade.id = '.$this->tableName .'.grade_id';
       $join[] = 'biz_textbook ON  biz_textbook.id = '.$this->tableName .'.textbook_id';
       if($isPc == 0) {
           $where['status'] = 2;
           $where['_string'] = 'type!=\'condensed\' AND type!=\'swf\'';
       }
       $model=D('Social_activity'); 
       $buildsql=$model->getMyPublishedWorks($publishUserId);
       if($pageSize == 0)
           $union = $buildsql."order by date desc";
       else
           $union = $buildsql."order by date desc limit ".$pageIndex.','.$pageSize;
       $result = $this->model->join($join)
           ->where($where) 
           ->group('date')
           ->field('1 flag,biz_resource.id,FROM_UNIXTIME(biz_resource.create_at,"%Y%m%d") date') 
           ->union($union)
           ->select();      
       
        $new_arr=array();
        $activity_model=M('social_activity');
        $activity_where['social_activity_register.user_id']=$publishUserId;
        //$activity_where['social_activity.status']=ACTIVITY_STATUS_PUBLISHED;
        //求出所有数据
        foreach($result as $k=>$val){
            if($val['flag']==1){
                //资源  
                $data=$this->model->join($join)
                    ->where($where) 
                    ->having("date=".$val['date']) 
                    ->field('1 flag,biz_resource.vid_image_path,biz_resource.id,biz_resource.name,biz_resource.type,biz_resource.zan_count,'
                        . 'biz_resource.favorite_count,biz_resource.follow_count,biz_resource.create_at,FROM_UNIXTIME(biz_resource.create_at,"%Y%m%d") date,biz_resource.description,'
                        . 'biz_textbook.name as textbook,biz_resource.file_path,biz_resource.vid,'.     
                    'if(UNIX_TIMESTAMP(NOW())-604800>biz_resource.create_at,\'no\',\'yes\') is_new,biz_resource.status')
                    ->order('biz_resource.create_at desc')
                     ->select();  
                $new_arr[]=$data;
            }else{
                //活动作品
                $data=$activity_model  
                    ->where($activity_where)
                    ->having("date=".$val['date']) 
                    ->join("social_activity_class on social_activity_class.id=social_activity.class_id")
                    ->join("social_activity_register on social_activity_register.activity_id=social_activity.id")
                    ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id") 
                    ->join("dict_course on dict_course.id=social_activity_works.course")  
                    ->join("dict_grade on dict_grade.id=social_activity_works.grade")  
                    ->field("2 flag,social_activity.id activity_id,social_activity.class_id,social_activity_works.id,social_activity.title,social_activity_works.zan_count,social_activity_works.favor_count,"
                            . "social_activity_works.browse_number,social_activity_works.create_at,FROM_UNIXTIME(social_activity_works.create_at,'%Y%m%d') date"
                            . ",social_activity_works.works_name,social_activity_works.status,social_activity_class.class activity_class,social_activity_class.parent_id,dict_course.course_name,"
                            . "dict_grade.grade,social_activity_register.user_name publish_people,background_image") 
                    ->order('social_activity_works.create_at desc')   
                    ->select(); 
                $new_arr[]=$data;
            }
        }
       return $new_arr;
   }
   
   public function getResourceList($queryParameters, $pageIndex=1, $pageSize=20, $userId='', $role='',$publishUserId='')
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
       if(!empty($queryParameters['type']))
           $where['type'] = $queryParameters['type'];
       if(!empty($userId) && isset($role))
       {
           $where['biz_resource_collect.user_id'] = $userId;
           $where['biz_resource_collect.user_type'] = $role;
           $join[] = 'INNER JOIN biz_resource_collect on biz_resource_collect.resource_id='.$this->tableName.'.id';
       }
       if(!empty($publishUserId))
       {
           $where['teacher_id'] = $publishUserId;
       }
       $join[] = 'dict_course ON dict_course.id = '.$this->tableName .'.course_id';
       $join[] = 'dict_grade ON  dict_grade.id = '.$this->tableName .'.grade_id';
       $join[] = 'biz_textbook ON  biz_textbook.id = '.$this->tableName .'.textbook_id';

       $where['status'] = 2;
       $where['_string'] = 'type!=\'condensed\' AND type!=\'swf\'';
       $result = $this->model->join($join)
           ->where($where)
           ->page($pageIndex.','.$pageSize)
           ->order('create_at desc')
           ->field($this->tableName.'.id,'.
               $this->tableName.'.teacher_name,'.
               $this->tableName.'.name,'.
               $this->tableName.'.type,'.
               $this->tableName.'.zan_count,'.
               $this->tableName.'.favorite_count,'.
               $this->tableName.'.follow_count,'.
               $this->tableName.'.create_at,'.
               $this->tableName.'.description,'.
               'biz_textbook.name as textbook,'.
               'dict_grade.grade,'.
               'dict_grade.chinese_code,'.
               'dict_course.course_name as course,'.
               $this->tableName.'.file_path,'.
               $this->tableName.'.vid,'.
               '\''.C('oss_path').'\' as oss_path,'.
               'biz_textbook.school_term,'.
               'if(UNIX_TIMESTAMP(NOW())-604800>'.$this->tableName.'.create_at,\'no\',\'yes\') is_new')
           ->select();          

       return $result;
   }
   
   public function getSubResourceHTMLList($info)
    {
        $oss_path = C('oss_path');
        $infoid = $info['id'];
        $result = M('biz_resource_contact')->where("biz_resource_id=$infoid")->select();    
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
                    $htmlList = $htmlList . '<a href="' .$oss_path .'teacher/'.$info['id'] .'/'.$val['id'] .'/'. $pdf_path .'.pdf">' . '查看第' .$i . '个资源</a></br>' ;
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

    public function getResourceDetails($id,$userId='')
    {
        $is_resdata['teacher_id'] = $userId;
        $is_resdata['id'] = $id;
        $is_res = $this->model->where($is_resdata)->find();
        if ($is_res) {
            $result = $this->model
                ->join('biz_textbook on '.$this->tableName.'.textbook_id=biz_textbook.id','left')
                ->join('dict_course on '.$this->tableName.'.course_id=dict_course.id','left')
                ->join('auth_teacher on '.$this->tableName.'.teacher_id=auth_teacher.id','left')
                ->join('dict_grade on '.$this->tableName.'.grade_id=dict_grade.id','left')
                ->field($this->tableName.'.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
                ->where($this->tableName.".id=$id")
                ->find();
        } else {
            $result = $this->model
                ->join('biz_textbook on '.$this->tableName.'.textbook_id=biz_textbook.id','left')
                ->join('dict_course on '.$this->tableName.'.course_id=dict_course.id','left')
                ->join('auth_teacher on '.$this->tableName.'.teacher_id=auth_teacher.id','left')
                ->join('dict_grade on '.$this->tableName.'.grade_id=dict_grade.id','left')
                ->field($this->tableName.'.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
                ->where($this->tableName.'.status=2 and '.$this->tableName.".id=$id")
                ->find();
        }


        $result['type'] = strtolower($result['type']);
        if(!empty($result)) {
            $where['biz_resource_id'] = $result['id'];
            $result['result_list'] = M('biz_resource_contact')->where($where)->select();
        }
        return $result;
    }
    public function getContactResourcePath($id)
    {
        //拿到关联表的数据
        $contact_result=$this->model->where($this->tableName.'.status=2 and '.$this->tableName.'.id='.$id)->join('biz_resource_contact on biz_resource_contact.biz_resource_id='.$this->tableName.'.id')
            ->field("biz_resource_contact.*")->select();

        return $contact_result;
    }


    /*
    *
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
        $result = M('biz_resource_zan')->where($where)->field('1')->find();
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
        $result = M('biz_resource_collect')->where($where)->field('1')->find();
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
        $allResourceType=$this->getResourceType();          
        $resource_type_number_arr=array();
        $result=$this->getResourceCount(CONDITION_STATUS_CLOSE);
        $resource_type_number_arr[]=array('key'=>'资源总数','value'=>$result);
          
        foreach($allResourceType as $key=>$value){
            $result=$this->getResourceCount(CONDITION_STATUS_OPEN,$key);
            $resource_type_number_arr[]=array('key'=>$value,'value'=>$result);
        } 
        return $resource_type_number_arr;
    }
    
     //资源所有类型
    function getResourceType(){
        $bj_resource_category=array('video'=>'视频','audio'=>'音频','image'=>'图片','word'=>'word','ppt'=>'ppt','pdf'=>'pdf','swf'=>'swf');
        return $bj_resource_category;
    }
    
    //获得所有资源的数量
    function getResourceCount($flag=false,$resource_type=''){
        if($flag==false){
            $data=$this->model
                ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
                ->join('dict_course on biz_resource.course_id=dict_course.id')
                ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
                ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')      
                ->count('biz_resource.id');         
        }else{ 
            $where['type']=$resource_type;
            $data=$this->model
                ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
                ->join('dict_course on biz_resource.course_id=dict_course.id')
                ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
                ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id') 
                ->where($where)       
                ->count('biz_resource.id');
        }
        return $data;
    }
    
    //按照条件查找出资源的数量
    function getResourceInfoCount($condition=array()){
    $result = $this->model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id') 
            ->where($condition)  
            ->field("count(biz_resource.id) count")
            ->find();
        return $result;
    }

    /*
     * 获得某个资源的相关资源(做推荐 用相同分册的数据)
     */
    public function getRecommendData($id){
        $result=$this->getResourceSimpleInfo($id);
        if(!empty($result)){
            $where['biz_resource.textbook_id']=$result['textbook_id'];
            $where['biz_resource.id']=array('neq',$id);
            $where['biz_resource.status']=2;
            $result=$this->model->where($where)
                                ->join('dict_course on dict_course.id=biz_resource.course_id')
                                ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')
                                ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
                                ->join('biz_textbook on biz_textbook.id=biz_resource.textbook_id')
                                ->field('biz_resource.id,biz_resource.name,type,vid,file_path resource_path,biz_resource.vid_image_path,course_name,biz_textbook.name textbook,dict_schoollist.school_name,auth_teacher.name as tname,biz_resource.create_at')
                                ->order('biz_resource.create_at desc')
                                ->limit(6)
                                ->select();

            foreach ($result as $k=>$v ) {
                switch ($v['type']){
                    case 'video':
                        if (strpos($v['vid_image_path'],'http')===false) {
                            $result[$k]['img_path'] = C('oss_path').$v['vid_image_path'];
                        } else {
                            $result[$k]['img_path'] = $v['vid_image_path'];
                        }
                        $result[$k]['icon_url'] = ICON_VIDEO;
                        break;

                    case 'audio':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_shipin.png';
                        $result[$k]['icon_url'] = ICON_AUDIO;
                        break;

                    case 'image':
                        $result[$k]['img_path'] = C('oss_path').$v['resource_path'];
                        $result[$k]['icon_url'] = ICON_IMAGE;
                        break;

                    case 'condensed':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_yasuobao.png';
                        $result[$k]['icon_url'] = ICON_ZIP;
                        break;

                    case 'word':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_word.png';
                        $result[$k]['icon_url'] = ICON_WORD;
                        break;
                    case 'ppt':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_ppt.png';
                        $result[$k]['icon_url'] = ICON_PPT;
                        break;
                    case 'pdf':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_pdf.png';
                        $result[$k]['icon_url'] = ICON_PDF;
                        break;
                    case 'swf':
                        $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_swf.png';
                        $result[$k]['icon_url'] = ICON_SWF;
                        break;
                }
                //unset($result[$k]['file_path']);
                //unset($result[$k]['vid_image_path']);
            }

            return $result;
        }else{
            return array();
        }
    }

    
    //操作教师资源的删除
    function managementDeleteResource($id){
        $model=$this->model;
        $model->startTrans();
        if(!$this->deleteResource($id)){
            return false;
        }
        if(!$this->deleteResourceContactTable($id)){ 
            $model->rollback();
            return false;
        }
        $model->commit();
        return true;
    }
    
    //删除教师资源主表信息
    function deleteResource($id){
        $where['id']=$id;
        if($this->model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    }
    
    //删除教师资源关联表信息
    function deleteResourceContactTable($id){
        $where['biz_resource_id']=$id;
        $model=M('biz_resource_contact');
        if($model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 获得资源总数
     */
    public function getResourcesCount($condition=array()){
        $condition['biz_resource.status']=2;
        if($condition['dict_schoollist.district_id']==-1){
            $condition['dict_schoollist.city_id']=array('neq',BEIJING_DISTRICT_ID);
            unset($condition['dict_schoollist.district_id']);
        }
        if($condition['biz_resource.grade_id']>0 && $condition['biz_resource.grade_id']<7){
            $condition['_string']="dict_grade.code in (14,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>6 && $condition['biz_resource.grade_id']<10){
            $condition['_string']="dict_grade.code in (15,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>9 && $condition['biz_resource.grade_id']<13){
            $condition['_string']="dict_grade.code in (16,".$condition['biz_resource.grade_id'].")";
        }
        
        $count =$this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
                ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
                ->join('dict_course on dict_course.id=biz_resource.course_id','left')
                ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
                ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
                ->field('biz_resource.*,biz_textbook.name as textbook')
                ->where($condition)
                ->count('biz_resource.id');        
        return $count;
    }
    
    
    /*
     * 获得资源数据
     */
    public function getResourceData($condition=array(),$order='desc',$user_id='',$role=''){
        $count=$this->getResourcesCount($condition);
        $Page = new \Think\Page($count, 20);
        $show=$Page->show('callback');
        
        if($condition['dict_schoollist.district_id']==-1){
            $condition['dict_schoollist.city_id']=array('neq',BEIJING_DISTRICT_ID);
            unset($condition['dict_schoollist.district_id']);
        }
        
        if($condition['biz_resource.grade_id']>0 && $condition['biz_resource.grade_id']<7){
            $condition['_string']="dict_grade.code in (14,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>6 && $condition['biz_resource.grade_id']<10){
            $condition['_string']="dict_grade.code in (15,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>9 && $condition['biz_resource.grade_id']<13){
            $condition['_string']="dict_grade.code in (16,".$condition['biz_resource.grade_id'].")";
        }
            
        if($order=='desc'){
            $order_string='biz_resource.create_at desc';
        }elseif($order=='asc'){
            $order_string='biz_resource.create_at asc';
        }else{
            $order_string='biz_resource.follow_count desc';
        }
        $join="biz_resource_collect collect_ on biz_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.user_type='.($role-ROLE_NUMBER);
        
        $condition['biz_resource.status']=2;
        $result = $this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')	
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
            ->join('dict_course on dict_course.id=biz_resource.course_id','left')
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
            ->join($join,'left')
            ->field("biz_resource.id,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,biz_resource.grade_id,biz_resource.course_id,biz_resource.favorite_count,"
                    . "dict_grade.grade,dict_course.course_name,biz_textbook.name as textbook,if(UNIX_TIMESTAMP(NOW())-604800>biz_resource.create_at,'no','yes') is_new,auth_teacher.name teacher_name,dict_schoollist.school_name,biz_resource.create_at")
            ->where($condition) 
            ->order($order_string)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();                                  
        $data['count']=$count;
        $data['page']=$show;
        $data['data']=$result;
        return $data;
    }

    /*
     * 获得资源数据
     */
    public function getAppResourceData($condition=array(),$order='desc',$user_id='',$role='',$start,$pagesize,$courseId=0,$gradeId=0,$schoolTerm=1){

        if($condition['dict_schoollist.district_id']==-1){
            $condition['dict_schoollist.city_id']=array('neq',BEIJING_DISTRICT_ID);
            unset($condition['dict_schoollist.district_id']);
        }

        if($condition['biz_resource.grade_id']>0 && $condition['biz_resource.grade_id']<7){
            $condition['_string']="dict_grade.code in (14,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>6 && $condition['biz_resource.grade_id']<10){
            $condition['_string']="dict_grade.code in (15,".$condition['biz_resource.grade_id'].")";
        }elseif($condition['biz_resource.grade_id']>9 && $condition['biz_resource.grade_id']<13){
            $condition['_string']="dict_grade.code in (16,".$condition['biz_resource.grade_id'].")";
        }

        if($order=='desc'){
            $order_string='biz_resource.create_at desc';
        }elseif($order=='asc'){
            $order_string='biz_resource.create_at asc';
        }else{
            $order_string='biz_resource.follow_count desc';
        }
        $join="biz_resource_collect collect_ on biz_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.user_type='.($role-ROLE_NUMBER);

        $condition['biz_resource.status']=2;
        if($order!= '')
        {
        $result = $this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
            ->join('dict_course on dict_course.id=biz_resource.course_id','left')
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
            ->join($join,'left')
            ->field("biz_resource.id,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,biz_resource.favorite_count,"
                . "biz_textbook.name as textbook,if(UNIX_TIMESTAMP(NOW())-604800>biz_resource.create_at,'no','yes') is_new,auth_teacher.name teacher_name,dict_schoollist.school_name,biz_resource.create_at")
            ->where($condition)
            ->order($order_string)
            ->limit($start,$pagesize)
            ->select();
        }
        else //sort  by calculate weight
        {
            $maxCourseBrowseCount = M()->query('SELECT max(follow_count) as mcount FROM biz_resource');
            $maxCourseBrowseCount = $maxCourseBrowseCount[0]['mcount'];
            $maxCourseBrowseCount2 = $maxCourseBrowseCount * $maxCourseBrowseCount;
            $weight1 = 1 * $maxCourseBrowseCount;
            $join = ' JOIN biz_textbook on biz_textbook.id=biz_resource.textbook_id ' .
                ' JOIN auth_teacher ON auth_teacher.id = biz_resource.teacher_id ' .
                ' JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id ';

            $courseWeightTable = array(
                1 => array(1 => 1, 6 => 0.2, 7 => 0.2),         //语文
                2 => array(2 => 1, 4 => 0.5, 5 => 0.5, 31 => 0.1), //数学
                3 => array(3 => 1, 6 => 0.2, 7 => 0.2),         //英语
                4 => array(4 => 1, 2 => 0.5, 5 => 0.2, 8 => 0.2, 31 => 0.2), //物理
                5 => array(5 => 1, 2 => 0.5, 4 => 0.2, 31 => 0.2), //化学
                6 => array(6 => 1, 7 => 0.2),                //政治
                7 => array(7 => 1, 1 => 0.2, 6 => 0.2),         //历史
                8 => array(8 => 1, 4 => 0.2),                //地理
                9 => array(9 => 1, 4 => 0.1, 5 => 0.1),         //生物
                31 => array(31 => 1, 2 => 0.1, 4 => 0.2, 5 => 0.2),//科学
            );
            if (empty($courseWeightTable[$courseId]))
                $courseWeightSql = '';
            else {
                $courseWeightSql = '(CASE ';
                foreach ($courseWeightTable[$courseId] as $key => $val) {
                    $courseWeightSql .= " WHEN biz_resource.course_id = $key THEN $val ";
                }
                $courseWeightSql .= " ELSE 0 END ) * $maxCourseBrowseCount2  + ";
            }
            if (empty($gradeId))
                $gradeWeightSql = '';
            else {
                $gradeWeightSql = "((case when $gradeId > biz_resource.grade_id then biz_resource.grade_id else $gradeId end)/(case when $gradeId < biz_resource.grade_id then biz_resource.grade_id else $gradeId end))*$maxCourseBrowseCount2 +";
            }
            $fields = "biz_resource.id,biz_resource.file_path,biz_resource.name,biz_resource.vid_image_path,biz_resource.type,biz_resource.favorite_count,"
                . "biz_textbook.name as textbook,if(UNIX_TIMESTAMP(NOW())-604800>biz_resource.create_at,'no','yes') is_new,auth_teacher.name teacher_name,dict_schoollist.school_name,biz_resource.create_at";
            $result = $this->model->where($condition)->join($join)
                ->field("$fields,( $weight1 +" .
                    $courseWeightSql .
                    $gradeWeightSql .
                    "(case when biz_textbook.school_term <> $schoolTerm then 1 else 2 end) * $maxCourseBrowseCount + " .
                    "(case when biz_resource.type = 'audio' then 0.5 else 1 end) * $maxCourseBrowseCount + " .
                    "$maxCourseBrowseCount * (-0.294 * ln(0.1+ (unix_timestamp(now()) - biz_resource.create_at)/86400) + 1.2818) +" .
                    "biz_resource.follow_count + 10 * biz_resource.favorite_count ) as weight"
                )
                ->group('biz_resource.id')->order('weight desc')->limit($start,$pagesize)->select();

        }

        foreach ($result as $k=>$v ) {
            switch ($v['type']){
                case 'video':
                    if (strpos($v['vid_image_path'],'http')===false) {
                        $result[$k]['img_path'] = C('oss_path').$v['vid_image_path'];
                    } else {
                        $result[$k]['img_path'] = $v['vid_image_path'];
                    }
                    $result[$k]['icon_url'] = ICON_VIDEO;
                    break;

                case 'audio':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_shipin.png';
                    $result[$k]['icon_url'] = ICON_AUDIO;
                    break;

                case 'image':
                    $result[$k]['img_path'] = C('oss_path').$v['file_path'];
                    $result[$k]['icon_url'] = ICON_IMAGE;
                    break;

                case 'condensed':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_yasuobao.png';
                    $result[$k]['icon_url'] = ICON_ZIP;
                    break;

                case 'word':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_word.png';
                    $result[$k]['icon_url'] = ICON_WORD;
                    break;
                case 'ppt':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_ppt.png';
                    $result[$k]['icon_url'] = ICON_PPT;
                    break;
                case 'pdf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_pdf.png';
                    $result[$k]['icon_url'] = ICON_PDF;
                    break;
                case 'swf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_swf.png';
                    $result[$k]['icon_url'] = ICON_SWF;
                    break;
            }
            unset($result[$k]['file_path']);
            unset($result[$k]['vid_image_path']);
        }

        return $result;
    }

    public function getAppCityDistrict() {
        $condition['biz_resource.status']=2;
        $result = $this->model->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
            ->field('dict_citydistrict.id as value,dict_citydistrict.name as title')
            ->where($condition)
            ->group('dict_citydistrict.id')
            ->select();

        return $result;
    }

    /*
     * 获得某个栏目资源(如:精品资源)
     */
    public function getColumnResource($order, $rows=''){ 
        $where['biz_resource.status'] = 2; 

        $result=$this->model->where($where)
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
            ->join('dict_grade ON dict_grade.id = biz_resource.grade_id')
            ->join('dict_course on dict_course.id=biz_resource.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_resource.textbook_id')

            ->field('biz_resource.id,biz_resource.create_at,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,auth_teacher.name teacher_name,dict_course.id course_id,course_name,'
                . 'biz_textbook.id textbook_id,biz_textbook.name textbook,dict_grade.grade,dict_schoollist.school_name')
            ->group('biz_resource.id')
            ->order('biz_resource.'.$order.' desc')
            ->limit($rows)
            ->select();
        return $result;
    }


    /*
    * 获得某个栏目资源(如:精品资源)
    */
    public function getAppColumnResource($order,$start,$pagesize){
        $where['biz_resource.status'] = 2;

        $result=$this->model->where($where)
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
            ->join('dict_grade ON dict_grade.id = biz_resource.grade_id')
            ->join('dict_course on dict_course.id=biz_resource.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_resource.textbook_id')

            ->field('biz_resource.id,biz_resource.create_at,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,auth_teacher.name teacher_name,'
                . 'biz_textbook.name textbook,dict_schoollist.school_name')
            ->group('biz_resource.id')
            ->order('biz_resource.'.$order.' desc')
            ->limit($start,$pagesize)
            ->select();

        foreach ($result as $k=>$v ) {
            switch ($v['type']){
                case 'video':
                    if (strpos($v['vid_image_path'],'http')===false) {
                        $result[$k]['img_path'] = C('oss_path').$v['vid_image_path'];
                    } else {
                        $result[$k]['img_path'] = $v['vid_image_path'];
                    }
                    $result[$k]['icon_url'] = ICON_VIDEO;
                    break;

                case 'audio':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_shipin.png';
                    $result[$k]['icon_url'] = ICON_AUDIO;
                    break;

                case 'image':
                    $result[$k]['img_path'] = C('oss_path').$v['file_path'];
                    $result[$k]['icon_url'] = ICON_IMAGE;
                    break;

                case 'condensed':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_yasuobao.png';
                    $result[$k]['icon_url'] = ICON_ZIP;
                    break;

                case 'word':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_word.png';
                    $result[$k]['icon_url'] = ICON_WORD;
                    break;
                case 'ppt':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_ppt.png';
                    $result[$k]['icon_url'] = ICON_PPT;
                    break;
                case 'pdf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_pdf.png';
                    $result[$k]['icon_url'] = ICON_PDF;
                    break;
                case 'swf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_swf.png';
                    $result[$k]['icon_url'] = ICON_SWF;
                    break;
            }
            unset($result[$k]['file_path']);
            unset($result[$k]['vid_image_path']);
        }
        return $result;
    }

    /*
     * 获得某个栏目资源(如:精品资源)
     */
    public function getAppColumnResourceData($order, $start,$pagesize){
        $where['biz_resource.status'] = 2;

        $result=$this->model->where($where)
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
            ->join('dict_grade ON dict_grade.id = biz_resource.grade_id')
            ->join('dict_course on dict_course.id=biz_resource.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_resource.textbook_id')

            ->field('biz_resource.id,biz_resource.create_at,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,auth_teacher.name teacher_name,'
                . 'biz_textbook.name textbook,dict_schoollist.school_name')
            ->group('biz_resource.id')
            ->order('biz_resource.'.$order.' desc')
            ->limit($start,$pagesize)
            ->select();

        foreach ($result as $k=>$v ) {
            switch ($v['type']){
                case 'video':
                    if (strpos($v['vid_image_path'],'http')===false) {
                        $result[$k]['img_path'] = C('oss_path').$v['vid_image_path'];
                    } else {
                        $result[$k]['img_path'] = $v['vid_image_path'];
                    }
                    $result[$k]['icon_url'] = ICON_VIDEO;
                break;

                case 'audio':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_shipin.png';
                    $result[$k]['icon_url'] = ICON_AUDIO;
                break;

                case 'image':
                    $result[$k]['img_path'] = C('oss_path').$v['file_path'];
                    $result[$k]['icon_url'] = ICON_IMAGE;
                break;

                case 'condensed':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_yasuobao.png';
                    $result[$k]['icon_url'] = ICON_ZIP;
                break;

                case 'word':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_word.png';
                    $result[$k]['icon_url'] = ICON_WORD;
                    break;
                case 'ppt':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_ppt.png';
                    $result[$k]['icon_url'] = ICON_PPT;
                    break;
                case 'pdf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_pdf.png';
                    $result[$k]['icon_url'] = ICON_PDF;
                    break;
                case 'swf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_swf.png';
                    $result[$k]['icon_url'] = ICON_SWF;
                    break;
            }
            unset($result[$k]['file_path']);
            unset($result[$k]['vid_image_path']);
        }

        return $result;
    }

    public function getAppmyPublishResource($start,$pagesize,$userId) {
        $Model = M('biz_resource');
        /*
        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->where("biz_resource.type!='html' and biz_resource.teacher_id=" . session('teacher.id'))
            ->count('biz_resource.id');
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        */

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
            ->field('biz_resource.id,biz_resource.create_at,biz_resource.file_path,biz_resource.vid_image_path,biz_resource.name,biz_resource.type,biz_textbook.name as textbook,dict_schoollist.school_name,auth_teacher.name as teacher_name')
            ->where("biz_resource.type!='html' and biz_resource.teacher_id=" . $userId)
            ->order('biz_resource.create_at desc')
            ->limit($start,$pagesize)
            ->select();
        foreach ($result as $k=>$v ) {
            switch ($v['type']){
                case 'video':
                    if (strpos($v['vid_image_path'],'http')===false) {
                        $result[$k]['img_path'] = C('oss_path').$v['vid_image_path'];
                    } else {
                        $result[$k]['img_path'] = $v['vid_image_path'];
                    }
                    $result[$k]['icon_url'] = ICON_VIDEO;
                    break;

                case 'audio':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_shipin.png';
                    $result[$k]['icon_url'] = ICON_AUDIO;
                    break;

                case 'image':
                    $result[$k]['img_path'] = C('oss_path').$v['file_path'];
                    $result[$k]['icon_url'] = ICON_IMAGE;
                    break;

                case 'condensed':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_yasuobao.png';
                    $result[$k]['icon_url'] = ICON_ZIP;
                    break;

                case 'word':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_word.png';
                    $result[$k]['icon_url'] = ICON_WORD;
                    break;
                case 'ppt':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_ppt.png';
                    $result[$k]['icon_url'] = ICON_PPT;
                    break;
                case 'pdf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_pdf.png';
                    $result[$k]['icon_url'] = ICON_PDF;
                    break;
                case 'swf':
                    $result[$k]['img_path'] = 'http://'.WEB_URL.'/Public/img/resource/i_swf.png';
                    $result[$k]['icon_url'] = ICON_SWF;
                    break;
            }
            unset($result[$k]['file_path']);
            unset($result[$k]['vid_image_path']);
        }
        return $result;
    }


    /*
     * 后台某个栏目下的列表总数
     */
    public function getColumnContentListCount($condition=array()){
        $result = $this->model
            ->join('dict_column_contact on dict_column_contact.resource_id=biz_resource.id')
            ->where($condition)
            ->count('1');
        return $result;
    }


    /*
     * 后台某个栏目下的列表信息
     */
    public function getColumnContentList($condition=array()){
        $count = $this->getResourceCount($condition);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $resource = $this->model
            ->field('dict_column_contact.*,biz_resource.name,biz_resource.create_at')
            ->join('dict_column_contact on dict_column_contact.resource_id=biz_resource.id')
            ->where($condition)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('dict_column_contact.status desc,sort')
            ->select();
        $data['page'] = $show;
        $data['list'] = $resource;
        return $data;
    }


    /*
     * 得到某个栏目下的教师资源信息
     */
    public function getColumnResourceInfo($id,$column_id=''){
        $Model = M('dict_column_contact');
        $where['id']=$id;
        if($column_id!=''){
            $where['column_id']=$column_id;
        }
        $result=$Model->where($where)->find();
        return $result;
    }

    /*
     * 修改栏目下某个教师资源信息(上下架某个栏目的资源)
     */
    public function upDownColumnContent($id, $data)
    {
        $Model = M('dict_column_contact');
        $where['id']=$id;
        if($Model->where($where)->save($data)===false) {
            return false;
        }else {
            return true;
        }
    }


    /*
     * 删除某个栏目下的教师资源信息
     */
    public function deleteColumnResource($id){
        $Model = M('dict_column_contact');
        $where['id']=$id;
        if($Model->where($where)->delete()===false){
            return false;
        }else{
            return true;
        }
    }

}