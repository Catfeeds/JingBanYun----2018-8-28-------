<?php
namespace Common\Model;
use Think\Model;
define('TEACHER_ROLE',2);
class Auth_teacherModel extends Model{
    
    public    $model='';
    protected $tableName = 'auth_teacher';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 获得某个老师的信息
     * id       
     */
    public function getTeachInfo($id,$password=''){
        $model=$this->model;
        $where['auth_teacher.id']=$id;
        if($password!=''){
            $where['auth_teacher.password']=$password;
        }
        $result=$model->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id",'left')
        ->where($where)->field('auth_teacher.sex,auth_teacher.id,auth_teacher.lock,auth_teacher.flag,password,name,access_token,points,brief_intro,school_id,apply_school_status,'
                . 'telephone,email,sex,avatar,auth_status,dict_schoollist.school_name,auth_teacher.professional,auth_teacher.school_age')->find();
        
        return $result;
    }

    public function getTeachInfoTel($id,$password=''){
        $model=$this->model;
        $where['auth_teacher.telephone']=$id;
        if($password!=''){
            $where['auth_teacher.password']=$password;
        }
        $result=$model->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id",'left')
            ->where($where)->field('auth_teacher.sex,auth_teacher.id,auth_teacher.lock,auth_teacher.flag,password,name,access_token,points,brief_intro,school_id,apply_school_status,'
                . 'telephone,email,sex,avatar,auth_status,dict_schoollist.school_name')->find();

        return $result;
    }
    
    public function getTeacherByTel($telephone,$teacher_id=0)
    {
        if($teacher_id!=0){
            $where['id']=array('neq',$teacher_id);
        }
        $where['telephone']=$telephone;
        $result=$this->model->where($where)->field('ipad_address,mac_address,pad_channel_id_info,channel_id_info,id,password,name,access_token,points,brief_intro,school_id,telephone,email,sex,avatar,login_address,flag')->find();
        return $result;
    }
     

    public function addEditTeacher($info,$secondInfo=array(),$id=0)
    {
        $this->model->startTrans();
        $secondModel = M('auth_teacher_second');
        $insertError = 0;
        if(0 == $id) {
            if (!($teacherId = $this->model->add($info))) {
                $insertError = 1;
            }
        }
        else
        {
            $this->model->where('id='.$id)->save($info);
            $teacherId = $id;
        }


        if($insertError == 0)
        if(isset($secondInfo) && (!empty($secondInfo)))
        {
            if(count($secondInfo)>0)
            {
                $secondModel->where('teacher_id='.$teacherId)->delete();
                foreach($secondInfo as $val)
                {
                    $secondData = array();
                    $pieces = explode(',',$val);
                    $secondData['course_id'] =  $pieces[0];
                    $secondData['grade_id'] =  $pieces[1];
                    $secondData['teacher_id'] =  $teacherId;
                    if(!$secondModel->add($secondData))
                    {
                        $insertError = 1;
                        break;
                    }
                }
            }
        }
        if(0 == $insertError)
        {
            $this->model->commit();
            return $teacherId;
        }
        else
         {
             $this->model->rollback();
             return 0;
         }
    }

    /*
     *  添加/修改教师资料
     *  输入参数：
     *  $info 教师主表参数数组 如：array('name'=>'abc','sex'=>'男')
     *  $secondInfo 教师副表版本学科年级字符串数组 格式：["版本ID,学科ID,年级ID,版本名称"]
     *  $id 教师ID 编辑个人信息时传入
     */
    public function addEditTeacherWithVersion($info,$secondInfo=array(),$id=0)
    {
        $this->model->startTrans();
        $secondModel = M('auth_teacher_second');
        $insertError = 0;
        if(0 == $id) {
            if (!($teacherId = $this->model->add($info))) {
                $insertError = 1;
            }
        }
        else
        {
            $this->model->where('id='.$id)->save($info);
            $teacherId = $id;
        }


        if($insertError == 0)
            if(isset($secondInfo) && (!empty($secondInfo)))
            {
                if(count($secondInfo)>0)
                {
                    $secondModel->where('teacher_id='.$teacherId)->delete();
                    foreach($secondInfo as $val)
                    {
                        $secondData = array();
                        $pieces = explode(',',$val);
                        $secondData['course_id'] =  $pieces[1];
                        $secondData['grade_id'] =  $pieces[2];
                        $secondData['p_type_id'] =  $pieces[0];
                        $secondData['p_type'] =  $pieces[3] ;
                        $secondData['teacher_id'] =  $teacherId;
                        if(!$secondModel->add($secondData))
                        {
                            $insertError = 1;
                            break;
                        }
                    }
                }
            }
        if(0 == $insertError)
        {
            $this->model->commit();
            return $teacherId;
        }
        else
        {
            $this->model->rollback();
            return 0;
        }
    }

    public function getGoodTeacherList($pageIndex,$pageSize)
    {
      $result = $this->model
          ->join('dict_schoollist ON dict_schoollist.id ='.$this->tableName.'.school_id')
          ->join('auth_teacher_second on auth_teacher.id=auth_teacher_second.teacher_id')
          ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
          ->where($this->tableName.'.flag=1 AND isfaketeacher=0')
          ->group('auth_teacher.id')
          ->order('points desc')
          ->page($pageIndex.','.$pageSize)
          ->field($this->tableName.'.*,dict_schoollist.school_name,group_concat(dict_course.course_name) course_name')
          ->select();       
       return $result;
    }

    /*
     * 修改权限
     */
    public function updateTeacherPrivilege($data,$id){
        $model=M('account_user_and_auth');
        $where['user_id']=$id;
        if($model->where($where)->save($data)===false){
            return false;
        }else{
            return true;
        }
    }

    /*
    * 添加权限
    */
    public function addTeacherPrivilege($data){
        $model=M('account_user_and_auth');
        if($model->add($data)){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 得到某个用户的权限信息
     */
    public function getTeacherPrivilegeInfo($teacher_id){
        $model=M('account_user_and_auth');
        $where['user_id']=$teacher_id;
        $where['role_id']=ROLE_TEACHER;
        $result=$model->where($where)->field('*,case when auth_id=4  and account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and'
            . ' account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')->find();    //echo $model->getLastsql();die;
        return $result;
    }
     
    public function updateInfoById($info,$id)
    {
        if($this->model->where("id=$id")->save($info)===false){    
            return false;
        }else{
            return true;
        }
    }
    
    /*
     * 获得某个学科信息
     */
    public function getCourseInfoByTeacher($second_id,$teacher_id=0){
        if($teacher_id!=0){
            $where['teacher_id']=$teacher_id;
        }
        $where['id']=$second_id;
        $model=M('auth_teacher_second');
        $result=$model->where($where)->find();
        return $result;
    }
    

    //获得教师的学科信息
    public function getTeacherAllCourse($teacher_id){
        $model=M('auth_teacher');
        $where['auth_teacher.id']=$teacher_id;
        $result=$model->where($where)->join("auth_teacher_second second on second.teacher_id=auth_teacher.id")
            ->join('dict_course on dict_course.id=second.course_id')
            ->field('dict_course.id course_id,dict_course.course_name')->group('dict_course.id')->select();     
        return $result;
    }
    
    
    /*
     * 获得某个教师的信息
     */
    public function getTeacherInfo($condition=array()){
        $result=$this->model->where($condition)->field('id,id teacher_id,name,school_id,points,avatar,password')->find();
        return $result;
    }
    
    
    /*
     * 获得所有教师信息
     */
    public function getTeacherDataAll($condition=array(),$order='desc',$biz_class_status='',$where=array()){
        $biz_class_status_string='';
        if($biz_class_status!=''){
            $biz_class_status_string=' and biz_class.class_status='.$biz_class_status;
        }
        $sql=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                    ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id')  
                    ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id','left')
                    ->join('biz_class on biz_class.id=biz_class_teacher.class_id and  biz_class.is_delete=0'.$biz_class_status_string,'left')
                    ->join('dict_citydistrict a ON a.id = dict_schoollist.provice_id')
                    ->join('dict_citydistrict b ON b.id = dict_schoollist.city_id')
                    ->join('dict_citydistrict c ON c.id = dict_schoollist.district_id')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                    ->field('a.id province_id,b.id city_id, c.id district_id,dict_schoollist.id school_id,a.name province, b.name city,c.name district,dict_schoollist.school_category,dict_schoollist.school_name,auth_teacher.id teacher_id,auth_teacher.name teacher_name,auth_teacher.telephone,'
                           . 'group_concat(DISTINCT concat(dict_grade.grade,biz_class.name) SEPARATOR "、") class_name,auth_teacher.auth_status,auth_teacher.flag,auth_teacher.create_at,auth_teacher.apply_school_status')
                    ->order('auth_teacher.create_at '.$order) 
                    ->group('auth_teacher.id')
                    ->buildsql();                       
        $result=$this->model->where($where)->join("join ".$sql." temp on temp.teacher_id=auth_teacher.id")
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id','left')
                        ->join('dict_grade on dict_grade.id=auth_teacher_second.grade_id','left')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id','left')
                        ->join('account_user_and_auth on account_user_and_auth.user_id=auth_teacher.id and role_id='.TEACHER_ROLE,'left')
                        ->field('temp.*,group_concat(DISTINCT concat(dict_grade.grade,dict_course.course_name) SEPARATOR "、") course,case when auth_id=2 and account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                        ->order('auth_teacher.create_at '.$order) 
                        ->group('auth_teacher.id')
                        ->order('auth_teacher.create_at desc')
                        ->select();                          
        return $result;
    }
    
    
    /*
     * 获得教师信息的总数
     */
    public function getTeacherCount($condition=array(),$biz_class_status='',$where=array()){        //逻辑删除等条件  
        $biz_class_status_string='';
        if($biz_class_status!=''){
            $biz_class_status_string=' and biz_class.class_status='.$biz_class_status;
        }
        $sql=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                    ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id') 
                    ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id','left')
                    ->join('biz_class on biz_class.id=biz_class_teacher.class_id and  biz_class.is_delete=0'.$biz_class_status_string,'left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                    ->group('auth_teacher.id')
                    ->field('auth_teacher.id,auth_teacher.id teacher_id')
                    ->buildsql();   
        $result=$this->model->where($where)->join("join ".$sql." temp on temp.teacher_id=auth_teacher.id")
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id','left')
                        ->join('dict_grade on dict_grade.id=auth_teacher_second.grade_id','left')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id','left')
                        ->field('temp.*')
                        ->group('auth_teacher.id')
                        ->select();         
        $count=count($result);
        return $count; 
    }
     
    
    
    /*
     * 获得教师信息的数据
     */
    public function getTeacherData($condition=array(),$order='desc',$biz_class_status='',$where=array()){           //逻辑删除等条件lock
        $count=$this->getTeacherCount($condition,$biz_class_status,$where);       
        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();  
        $biz_class_status_string='';
        if($biz_class_status!=''){
            $biz_class_status_string=' and biz_class.class_status='.$biz_class_status;
        } 
        $sql=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                    ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id')  
                    ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id','left')
                    ->join('biz_class on biz_class.id=biz_class_teacher.class_id and biz_class.is_delete=0'.$biz_class_status_string,'left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                    ->field('dict_schoollist.id school_id,dict_schoollist.school_category,dict_schoollist.school_name,auth_teacher.id teacher_id,auth_teacher.name teacher_name,auth_teacher.telephone,'
                           . 'group_concat(DISTINCT concat(dict_grade.grade,biz_class.name) SEPARATOR "、") class_name,auth_teacher.auth_status,auth_teacher.flag,auth_teacher.apply_school_status')
                    ->group('auth_teacher.id')
                    ->order('auth_teacher.create_at '.$order)
                    ->buildsql();
        $result=$this->model->where($where)->join("join ".$sql." temp on temp.teacher_id=auth_teacher.id")
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id','left')
                        ->join('dict_grade on dict_grade.id=auth_teacher_second.grade_id','left')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id','left')
                        ->join('account_user_and_auth on account_user_and_auth.user_id=auth_teacher.id and role_id=2','left')
                        ->field('account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,temp.*,group_concat(DISTINCT concat(dict_grade.grade,dict_course.course_name) SEPARATOR "、") course,'
                                . 'group_concat(DISTINCT dict_grade.grade SEPARATOR "、") grade_,'
                                . 'group_concat(DISTINCT dict_course.course_name SEPARATOR "、") course_,'
                                . 'case when account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')

                        ->group('auth_teacher.id')
                        ->order('auth_teacher.create_at '.$order)
                        ->limit($Page->firstRow . ',' . $Page->listRows)
                        ->select();
        $data['count']=$count;
        $data['data']=$result;
        $data['page']=$show;
        return $data;
    }
    
    
    /*
     * 修改申请学校审核状态的管理
     */
    public function updateApplyStatusManagement($id,$status,$school_id=0){
        $where['id']=$id;
        if($school_id!=0){
            $where['school_id']=$school_id;
        }
        if($status==1){
            $data['apply_school_status']=$status;
            if($this->model->where($where)->save($data)===false){
                return false;
            }else{
                return true;
            }
        }else{ 
            $data['school_id']=OTHER_SCHOOL_ID;
            $data['apply_school_status']=0;
            if($this->model->where($where)->save($data)===false){   
                return false;
            }else{
                return true;
            }
        }
    }
    
    
    /*
     * 修改教师认证状态的管理
     */
    public function updateAuthStatusManagement($id,$status,$school_id=0){
        $where['id']=$id;
        if($school_id!=0){
            $where['school_id']=$school_id;
        } 
        $data['auth_status']=$status;
        if($this->model->where($where)->save($data)){
            return true;
        }else{
            return false;
        } 
    }
    
    
    /*
     * 修改教师信息的启用或禁用状态
     */
    public function updateEnableStatus($id,$status,$school_id=0){
        if($school_id!=0){
            $where['school_id']=$school_id;
        }
        $where['id']=$id;
        $data['flag']=$status;
        if(false !== $this->model->where($where)->save($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 获得教师学校等信息
     */
    public function getTeacherSimpleData($teacher_id,$school_id=0){
        if($school_id!=0){
            $where['auth_teacher.school_id']=$school_id;
        }
        $where['auth_teacher.id']=$teacher_id;
        $result=$this->model->where($where)->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
                ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
                ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
                ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')
                ->join('account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.auth_id = 3 AND account_user_and_auth.role_id = 2','left')
                ->join('account_user_and_auth a ON a.user_id = auth_teacher.id and a.auth_id = 4 AND a.role_id = 2','left')
                ->field('account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_teacher.id,auth_teacher.name teacher_name,auth_teacher.auth_status,auth_teacher.telephone,auth_teacher.sex,school_name,school_code,'
                        . 'province.id province_id,province.name province,city.id city_id,city.name city,district.id district_id,district.name district,apply_school_status,'
                        .'CASE
WHEN account_user_and_auth.auth_id = 3 THEN account_user_and_auth.auth_start_time 
ELSE
a.auth_start_time
END AS startime,
CASE
WHEN account_user_and_auth.auth_id = 3 THEN account_user_and_auth.auth_end_time 
ELSE
a.auth_end_time
END AS endtime,'
                        . 'auth_teacher.flag,auth_teacher.email,brief_intro,password,dict_schoollist.id school_id,'
                        . 'case when account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->find();         
        return $result;
    }
     
     
    
    /*
     * 获得教师的任教年级和学科
     */
    public function getTeacherCourse($condition=array(),$order='desc',$group_flag=0){ 
        $group_string='auth_teacher_second.id';
        if($group_flag!=0){
            $group_string="dict_course.id";
        }
        $result=$this->model->where($condition)->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id')
                    ->join('dict_grade on dict_grade.id=auth_teacher_second.grade_id')
                    ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                    ->field('auth_teacher.school_id,auth_teacher.apply_school_status,auth_teacher_second.id second_id,auth_teacher.id teacher_id,'
                            . 'dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name')
                    ->group($group_string)
                    ->order('auth_teacher_second.id '.$order)
                    ->select();
        return $result;
    } 
    
    
    /*
     * 获得教师所在班级年级信息
    */
    public function getTeacherClassData($where,$order='desc'){  
        $result=$this->model->where($where)->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id')
                    ->join('dict_course on dict_course.id=biz_class_teacher.course_id','left')
                    ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field('dict_grade.grade,biz_class.id class_id,biz_class.name class_name,dict_course.course_name course,biz_class.class_code,'
                            . 'biz_class.class_status,biz_class.flag,GROUP_CONCAT(dict_course.course_name) course_name')
                    ->group('biz_class.id')
                    ->order('biz_class_teacher.create_at '.$order)
                    ->select();                 
        return $result; 
    }
    
    
    /*
     * 插入教师数据
     */
    public function addTeacherData($data){
        if($insert_id=$this->model->add($data)){
            return $insert_id;
        }else{
            return false;
        }
    }
    
    
    /*
     * 插入教师关联学科年级数据
     */
    public function addTeacherSecond($data){
        $model=M('auth_teacher_second');
        if($model->add($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 删除某个教师关联学科年级数据
     */
    public function deleteTeacherSecond($second_id,$teacher_id=0){
        $model=M('auth_teacher_second');
        $where=array();
        if($teacher_id!=0){
            $where['teacher_id']=$teacher_id;
        } 
        $where['id']=$second_id; 
        if($model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    } 
    
    
    /*
     * 获得教师班级任教的的信息
     */
    public function getTeacherClassTeachesInfo($condition){
        $model=M('biz_class_teacher');
        $result=$model->where($condition)->field('class_id,teacher_id,course_id,is_handler,create_at')->select();
        return $result;
    }
    
    
    /*
     * 让某个教师和班级进行绑定
     */
    public function teacherBindClass($data){
        $class_teacher_model=M('biz_class_teacher');
        if($class_teacher_model->add($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 取固定条数的教师数据
     */
    public function getTeacherFixedData($condition=array(),$rows=10){
        $result=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id')
            ->join('dict_grade on dict_grade.id=auth_teacher_second.grade_id')
            ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
            ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id','left')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id','left')
            ->field('dict_schoollist.id school_id,dict_schoollist.school_name,auth_teacher.name,auth_teacher.telephone,'
                    . 'group_concat(dict_grade.grade SEPARATOR "、") grade,group_concat(dict_course.course_name SEPARATOR "、") course,auth_teacher.auth_status,auth_teacher.flag')
            ->order('auth_teacher.create_at '.$order)
            ->limit($rows)
            ->group('auth_teacher.id')
            ->select(); 
        return $result;     
    }

    /*
     *判断任教学科和任教班级是否相符
     */
    public function matching($data){
        $model = M('auth_teacher_second');
        $resource = $model->field('id')
                    ->where($data)
                    ->find();//echo M()->getLastSql();die;
        return $resource;
    }

    /*
     *
     */
    public function getGoodTeacherResource($where,&$count)
    {
        $result = $this->model
            ->join('dict_schoollist ON dict_schoollist.id ='.$this->tableName.'.school_id')
            ->join('auth_teacher_second on auth_teacher.id=auth_teacher_second.teacher_id')
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->where($where)
            ->group('auth_teacher.id')
            ->order('points desc')
            ->field($this->tableName.'.*,dict_schoollist.school_name,group_concat(dict_course.course_name) course_name')
            ->select();
            $count = count($result);
        return $result;
    }
    /*
     *获得老师的总数
     */
    public function getTeacherCounts(){
        $count = $this->model
            ->field('auth_teacher.id')
            ->join('dict_schoollist ON dict_schoollist.id ='.$this->tableName.'.school_id')
            ->join('auth_teacher_second on auth_teacher.id=auth_teacher_second.teacher_id')
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->where($this->tableName.'.flag=1 AND isfaketeacher=0')
            ->group('auth_teacher.id')
            ->order('points desc')
            ->select();
        return count($count);
    }

    /*
     *判断任教年级和任教班级的年级是否相符
     */
    public function matchingOfGrade($data){
        $model = M('auth_teacher_second');
        $resource = $model->field('grade_id')
            ->where($data)
            ->select();//echo M()->getLastSql();die;
        return $resource;
    }

    /*
     * 更新教师手机号
     */
    public function updateTeacherTelephone($oldTelephone,$newTelephone)
    {
        $result = M()->execute("UPDATE auth_teacher SET telephone=$newTelephone WHERE telephone=$oldTelephone");
        return ($result !== false);
    }
}