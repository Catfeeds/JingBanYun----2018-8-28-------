<?php
namespace Common\Model;
use Think\Model; 
define('ADMIN_SCHOOL_ROLE',3);
define('SCHOOL_AUTH_STATUS',3);

class Dict_schoollistModel extends Model{
    
    public    $model='';
    protected $tableName = 'dict_schoollist';  
	
    public function __construct(){ 
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    /*
     * 得到一条学校信息
     * id       学校ID
     */
    
    public function getSchoolInfo($id){ 
        $model=$this->model;
        $result=$model->where('id='.$id)->field('*,case when user_auth=3 and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status,FROM_UNIXTIME(auth_start_time,"%Y-%m-%d") format_start_time,'
                . 'FROM_UNIXTIME(auth_end_time,"%Y-%m-%d") format_end_time')->find();                
        return $result;
    }
     

    
    /*
     *获取所有学校
     *
     */
    public function getAllSchool()
    {
        $Model = $this->model;
        $result = $Model
            -> order('school_name asc')
            -> field ('id,school_name as name')
            -> select();
        return $result;
    }
    
    
    //根据条件参数获得地址数据
    public function gtAddressByParameter($condition=array()){ 
        $model=M('dict_citydistrict');
        $result=$model->where($condition)->field('id,name')->select();     
        return $result;
    }
    
    
    //根据ID和层级获得区县
    public function getAddressById($id,$level=''){
        $model=M('dict_citydistrict');
        $condition['id']=$id;
        if($level!=''){
            $condition['level']=$level;
        }
        $zhiXiaIdArray = array(1,2,9,22);
        if(in_array($id,$zhiXiaIdArray))
        {
            $condition['level']=1;
        }
        $result=$model->where($condition)->field('id,name,code')->find();   
        return $result;
    }
    
    //根据名称等获得地址
    public function getAddress($address,$level=''){
        $model=M('dict_citydistrict');
        $condition['name']=$address;  
        
        $result=$model->where($condition)->field('id,name,code,upid')->find();           
        if(!empty($result)){
            $zhiXiaIdArray = array(1,2,9,22);
            if($address=='北京市' || $address=='天津市' || $address=='上海市' || $address=='重庆市'){
                if($level==1 || $level==2){
                    return $result;
                }
            }elseif(in_array($result['upid'],$zhiXiaIdArray))
            {
                if($level==1 || $level==2){
                    return $result;
                }
            }elseif($result['level']==$level){
                return $result;
            }else{
                return array();
            }
             
        }
        return $result;
    }
    
    //获得所有省份
    public function getProvince()
     {
       $Model = M('dict_citydistrict');
       $result = $Model->where('level=1')->field ('id,name')-> select();
       return $result;

     }
    
    //根据省份获得城市
    public function getCityByProvince($provinceId)
     { 
           $zhiXiaIdArray = array(1,2,9,22);
           $where ="";
           if(in_array($provinceId,$zhiXiaIdArray))
            {
             $where = 'level=1 and '.'id='.$provinceId;
            }
           else
            {
             $where = 'level=2 and '.'upid='.$provinceId;
            }
           $Model = M('dict_citydistrict');
           $result = $Model-> where($where)-> order('name asc')-> field ('id,name')-> select(); 
           return $result;
     }
     
     //根据市获得区县
     public function getDistrictByCity($cityId)
     {  
        $where['upid']=$cityId;
         $Model = M('dict_citydistrict');
         $result = $Model-> where("upid=".$cityId)-> order('name asc')-> field ('id,name') -> select();     
         return $result;
    } 
    
     /*
     *  根据区获取学校
     * districtId 区ID
     */
     public function getSchoolByDistrict($districtId)
    {
        $Model = $this->model;
        $result = $Model
               -> where("district_id=".$districtId)
               -> order('school_name asc')
               -> field ('id,school_name as name')
               -> select(); 
        return $result;
    }
    
    
    /*
     * 根据搜索条件获得学校
     */
    public function getSchool($condition=array()){ 
        $Model = $this->model;
        $result = $Model
               -> where($condition)
               -> order('school_name asc')
               -> field ('id,school_name as name')
               -> limit(10)
               -> select();
        return $result;
    }
    
    /*
     * 获得学校总数
     */
    public function getSchoolCount(){
        $data=$this->model->count('1');         
        return $data;
    }
    
    
    /*
     * 获得学校和管理数据总数
     */
    public function getCountSchool($condition=array(),$having=''){     //这里角色要动//
        $result = $this->model
                ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role='.ADMIN_SCHOOL_ROLE,'left') 
                ->field('dict_schoollist.id,case when user_auth=3 and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->having($having)
                ->where($condition) 
                ->select();        
        return count($result);
    }
    
    /*
     * 获得学校和管理员数据 学校列表
     */ 
    public function getSchoolData($condition=array(),$order='desc',$having=''){  
        $count=$this->getCountSchool($condition,$having);
        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();   
        $result = $this->model
                ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role='.ADMIN_SCHOOL_ROLE,'left')  
                ->field('dict_schoollist.auth_start_time,dict_schoollist.user_auth,dict_schoollist.school_category,dict_schoollist.auth_end_time,dict_schoollist.id,dict_schoollist.id school_id'
                        . ',dict_schoollist.school_name,dict_schoollist.school_code,'
                        . 'dict_schoollist.obligation_person,dict_schoollist.obligation_tel,'
                        . 'dict_schoollist.status,dict_schoollist.flag,is_create_administartor,auth_admin.name,auth_admin.real_name,'
                        . 'case when user_auth=3 and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->where($condition)
                ->group('dict_schoollist.id')
                ->having($having)
                ->order('id '.$order) 
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();                       
                            
        $data['page']=$show;
        $data['data']=$result;
        $data['count']=$count;
        return $data;
    }
    
    
    /*
     * 得到学校和管理员数据
     */
    public  function getSchoolDataAll($condition=array(),$order='desc',$having=''){
        $result = $this->model
                ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role='.ADMIN_SCHOOL_ROLE,'left')  
                ->field('dict_schoollist.user_auth,dict_schoollist.school_category,dict_schoollist.auth_end_time,dict_schoollist.id,dict_schoollist.id school_id'
                        . ',dict_schoollist.school_name,dict_schoollist.school_code,'
                        . 'dict_schoollist.obligation_person,dict_schoollist.obligation_tel,'
                        . 'dict_schoollist.status,dict_schoollist.flag,is_create_administartor,auth_admin.name,auth_admin.real_name,'
                        . 'case when user_auth=3 and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->where($condition)
                ->having($having)
                ->order('id '.$order)  
                ->select();
        return $result;
    }  
    
     
    /*
     * 修改学校的开通状态或启用停用状态
     */
    public function updateSchoolStatus($id,$status,$flag=1){
        if($flag==1){
            $data['status']=$status;
        }else{
            $data['flag']=$status;
        }
        $where['id']=$id;
        if($this->model->where($where)->save($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 获得某条学校与其管理账号的信息
     */
    public function getSchoolSimpleData($id){
        $where['dict_schoollist.id']=$id;   
        $result=$this->model->where($where)->join('auth_admin on auth_admin.school_id=dict_schoollist.id and role='.ADMIN_SCHOOL_ROLE,'left')
                    ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
                    ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
                    ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')    
                    ->field('dict_schoollist.auth_start_time,dict_schoollist.auth_end_time,dict_schoollist.id,school_name,dict_schoollist.school_address,school_code,school_category,province.id province_id,province.name province,city.id city_id,city.name city,district.id district_id,district.name district,'
                            . 'is_create_administartor,auth_start_time,auth_end_time,timetype,obligation_person,obligation_tel,'
                            . 'obligation_email,auth_admin.id admin_id,auth_admin.name,dict_schoollist.flag,'
                            . 'case when user_auth=3 and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_start_time<UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                    ->find();                     
        return $result; 
    }
    
   
    /*
     * 插入学校
     */
    public function addSchool($data){
        if($this->model->add($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 更改某条学校信息
     */
    public function updateSchoolData($id,$data){
        $where['id']=$id;
        if($this->model->where($where)->save($data)===false){  
            return false; 
        }else{  
            return true;
        }
    }

    //根据学校id获取学校下面的所有的老师
    public function getAllSchoolLeveTeacher( $schol_id ) {
        $map['school_id'] = $schol_id;
        $map['flag'] = 1;
        $classinfo = M('auth_teacher')->where( $map )->field('id')->select();
        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['id'],$idlist)) {
                $idlist[] = $v['id'];
            }

        }

        return $idlist;
    }

    //根据学校id获取学校下面所有的学生
    //根据学校id获取学校下面的所有的老师
    public function getAllSchoolLeveStu( $schol_id ) {
        $map['school_id'] = $schol_id;
        $map['flag'] = 1;
        $classinfo = M('auth_student')
                    ->where( $map )
                    ->field('parent_id')
                    ->select();
        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['parent_id'],$idlist) && $v['parent_id'] !=0 ) {
                $idlist[] = $v['parent_id'];
            }

        }

        return $idlist;
    }


    //根据学校id获取学校下面的所有的老师
    public function getStudentIdAll( $schol_id ) {
        $map['school_id'] = $schol_id;
        $map['flag'] = 1;
        $classinfo = M('auth_student')
            ->where( $map )
            ->field('id')
            ->select();
        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['id'],$idlist) && $v['id'] !=0 ) {
                $idlist[] = $v['id'];
            }

        }

        return $idlist;
    }

}