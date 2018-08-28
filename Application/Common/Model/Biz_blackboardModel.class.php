<?php
namespace Common\Model;
use Think\Model; 

class Biz_blackboardModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_blackboard';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    //获得黑板表数据的总数
    public function getTotalNumber(){ 
        $model=$this->model; 
        $total_number=$model->count('id');
        return $total_number;
    }

    public function isBoardInfoExists($teacherId,$blackboardId)
    {
      $where['publisher_id'] =  $teacherId;
      $where['id'] = $blackboardId;
      $result = $this->model->where($where)->field('1')->find();
      if(empty($result))
          return false;
      return true;
    }
    /*
     * 获得老师黑板表的数据  
     * $id      发布人ID(教师ID)
     * start    取数据的开始处
     * end      取数据的结束处
     */
    public function getTeachBlackboard($id,$start,$end){
        $model=$this->model;
        $result=$model->where("publisher_id="."'$id'")->field('id,class_id,message_title,left(message,30) message,publisher,create_at')->order('create_at desc')->limit($start,$end)->select();         //echo $model->getLastsql();die;
        
        return $result;
    }
    
    
    /*
     * 获得学生黑板表的数据   
     * ids      班级id(字符串)
     * start    取数据的开始处
     * end      取数据的结束处
     */
    public function getIdsBlackboard($ids,$start,$end,$group_tag=0){
        $model=$this->model;
        if($group_tag==0){
            $result=$model->where("class_id in (".$ids.")")->field('id,class_id,message_title,left(message,30) message,publisher,create_at')->order('create_at desc')->limit($start,$end)->select();       
        }else{
            $result=$model->where("class_id in (".$ids.")")->field('id,class_id,message_title,left(message,30) message,publisher,create_at')->group('id')->order('create_at desc')->limit($start,$end)->select();       
        }  
        
        return $result;
    }
    
    
    /*
     * 获得一条数据
     * id       小黑板ID
     */
    public function get_one_data($id){
        $model=$this->model;
        $result=$model->where('id='.$id)->field('id,class_id,message_title message_title,message,create_at')->find();
        return $result;
    }

    /*
     * 获得一条数据
     * id       小黑板ID
     */
    public function get_one_data_new( $id,$role,$classId=0){
        /*$model=$this->model;
        $result=$model->where('id='.$id)->field('id,class_id,message_title message_title,message,create_at')->find();
        return $result;*/
        if ($role == 2) {
            $Model = M('biz_blackboard');
            $map['biz_blackboard.id'] = $id;
            $map['biz_class.is_delete'] = 0;
            $result = $Model
                ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
                ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->field('group_concat(grade,biz_class.name) as cgname,biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.message,biz_blackboard.create_at,biz_blackboard.publisher')
                ->where( $map )
                ->group('biz_blackboard.id')
                ->find();
            $cgname = explode(',',$result['cgname']);
            $result['cgname'] = array_unique($cgname);
            return $result;
        } else {
            $Model = M('biz_blackboard');
            $additionalJoinCondition = '';
            if(!empty($classId))
                $additionalJoinCondition = " AND boardandclass.class_id = $classId";
            $result = $Model
                ->join("boardandclass on boardandclass.b_id=biz_blackboard.id $additionalJoinCondition")
                ->join('biz_class on biz_class.id=boardandclass.class_id')
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->where("biz_blackboard.id=$id")
                ->field('biz_blackboard.*,biz_class.name,dict_grade.grade,group_concat(grade,biz_class.name) as cgname')
                ->group('biz_blackboard.id')
                ->find();
            $result['cgname'] = explode(',',$result['cgname']);
            return $result;
        }

    }


    /*
     * 删除某个小黑板数据
     * id       小黑板ID
     */
    public function deleteBlackboard($id){
        $model=$this->model;
        if($model->where('id='.$id)->delete()){
            return true;
        }else{
            return false;
        }
    }
    
    /*
     * 修改某个小黑板数据
     * id       小黑板主键
     */
    public function updateBlackboard($id,$data){
        $model=$this->model;
        if($model->where('id='.$id)->save($data)){
            return true;
        }else{
            return false;
        }
    }

    
    /*
     * 添加小黑板信息
     */
    public function addBlackboardData($data){
        $model=$this->model;
        if( $id = $model->add($data)){
            return $id;
        }else{
            return false;
        }
    }

    //根据小黑板id获取小黑板的资源
    public function get_blackboard_result_list( $id ) {
        $map['black_board_id'] = $id;
        $row = M('board_contact_file')->order('order_data asc')->where($map)->select();
        return $row;
    }

    //入库小黑板的资源

    public function addBlackboardResult( $board_result_list,$a_id ) {

        if (!empty($board_result_list)) {
            foreach ($board_result_list as $k=> $v) {
                $data = explode('|',$v);
                $map['black_board_id'] = $a_id;
                $map['black_board_file_name'] = $data['1'];
                $map['black_board_file_path'] = $data['0'];
                $map['create_at'] = time();
                $map['type'] = $data['2'];

                if($data['2'] == 'audio') {
                    $map['order_data'] = 1;
                }

                if($data['2'] == 'video') {
                    $map['order_data'] = 2;
                }

                if($data['2'] == 'image') {
                    $map['order_data'] = 3;
                }

                $id = M('board_contact_file')->add( $map );

                if (!$id) {
                    return false;
                }

            }
        }
    }

    //教师获取小黑板列表
    public function getBoardList( $check ) {

        $Model = M('biz_blackboard');
        $count = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($check)
            ->group('biz_blackboard.id')
            ->select();

        $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join("biz_isread_blackboard on biz_isread_blackboard.user_id=".session('teacher.id')." and biz_isread_blackboard.b_id=biz_blackboard.id and role_id=2",'left')
            ->where($check)
            ->order('biz_blackboard.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('group_concat(grade,biz_class.name) as cgname,biz_class.school_id,biz_class.class_status,biz_class.flag,biz_blackboard.id,biz_blackboard.class_id,biz_blackboard.message_title,biz_blackboard.create_at,'
                . 'biz_blackboard.publisher_id,biz_blackboard.publisher,biz_blackboard.status,biz_class.name class_name,biz_class.student_count,dict_grade.grade,biz_isread_blackboard.id is_read')
            ->group('biz_blackboard.id')
            ->select();

        foreach ($result as $key => $value) {
            $atu['id'] = $value['publisher_id'];
            $row = M('auth_teacher')->where($atu)->find();
            $result[$key]['uimg'] = $row['avatar'];
            //求看过学生的数量
            /*$data=$Model->where('biz_blackboard.id='.$value['id'])
                    ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                    ->join('biz_class on biz_class.id=biz_blackboard.class_id')
                    ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                    ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                    ->field('biz_blackboard.id,biz_class_student.student_id')->select();*/

            $data_count=$Model->where('biz_blackboard.id='.$value['id'])
                    ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                    ->join('biz_class on biz_class.id=boardandclass.class_id')
                    ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                    ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                ->field('biz_blackboard.id,biz_class_student.student_id')->count();


            $result[$key]['read_person_number'] = $data_count;

            if ($value['class_status'] == 1) {
                $d_m['id'] = $value['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0) {
                    $result[$key]['flag'] = 0;
                }
            }
            $cgname = explode(',',$value['cgname']);
            $cgname = array_unique($cgname);
            $result[$key]['cgname'] = implode(',',$cgname);

        }

        $data['result'] = $result;
        $data['show'] = $show;
        return $data;
    }
    //获取教师发布小黑板的总数
    public function getBoardCount( $checkmap ) {

        $Model = M('biz_blackboard');
        $countboard = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($checkmap)
            ->group('biz_blackboard.id')
            ->select();

        return $countboard;
    }

     //获取未读小黑板数
    public function getUnreadBoardCount( $check=array(),$userId,$role ) {

        $Model = M('biz_blackboard');
        $check['biz_isread_blackboard.id'] = array('exp','is null');

        if(ROLE_STUDENT == $role) {
            $result = $Model
                ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                ->join('biz_class on biz_class.id=boardandclass.class_id')
                ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
                ->where($check)
                ->join("biz_isread_blackboard on biz_class.id=biz_isread_blackboard.class_id and biz_isread_blackboard.b_id=biz_blackboard.id and role_id=3 and user_id=$userId", 'left')
                ->field('count(1) count')
                ->find();
            return $result['count'];
         }
         else if(ROLE_PARENT==$role)
         {
             $result = $Model
                 ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
                 ->join('biz_class on biz_class.id=boardandclass.class_id')
                 ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
                 ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                 ->join('auth_student on auth_student.id=biz_class_student.student_id')
                 ->join("biz_isread_blackboard on biz_class.id=biz_isread_blackboard.class_id and auth_student.parent_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and role_id=4 and user_id=$userId",'left')
                 ->where($check)
                 ->field('count(distinct boardandclass.id) count')
                 ->find();
             return $result['count'];
         }
    }
    //app获取教师小黑板列表
    public function getAppBoardList( $id,$check,$detail_url,$pageStart,$pageEnd) {
        $model=M('biz_blackboard');
        $result=$model->where($check)
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('biz_isread_blackboard ON biz_isread_blackboard.b_id = biz_blackboard.id and biz_isread_blackboard.role_id = '.ROLE_TEACHER. ' and biz_isread_blackboard.user_id='.$id,'left')
            ->field('biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.message,biz_blackboard.publisher,'
                . 'biz_blackboard.create_at,FROM_UNIXTIME(biz_blackboard.create_at,\'%Y-%m-%d\') createdate,biz_class.name class_name,dict_grade.grade,GROUP_CONCAT(CONCAT(dict_grade.grade,biz_class.name) SEPARATOR \'和\') classlist,CONCAT("'.$detail_url.'",biz_blackboard.id) url')
            ->order("biz_blackboard.create_at desc")
            ->limit($pageStart,$pageEnd)
            ->group('biz_blackboard.id')
            ->select();

        foreach ($result as $k=>$v) {
            $result[$k]['message'] = mb_substr(strip_tags($result[$k]['message']),0,40);
            //remove nbsp
            $result[$k]['message'] = str_replace('&nbsp;','',$result[$k]['message']);
        }

        return $result;
    }

    //app获取学生小黑板列表
    public function getAppBoardStudentList( $id,$check,$detail_url,$pageStart,$pageEnd) {

        $Model = M('biz_blackboard');
        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('biz_isread_blackboard ON biz_isread_blackboard.b_id = biz_blackboard.id and biz_isread_blackboard.role_id = '.ROLE_STUDENT. ' and biz_isread_blackboard.user_id='.$id.' and boardandclass.class_id = biz_isread_blackboard.class_id','left')
            ->where($check)
            ->order('is_read desc,biz_blackboard.create_at desc')
            ->limit($pageStart,$pageEnd)
            ->field('(case when biz_isread_blackboard.user_id is null then 2 else 1 end) is_read,biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.message,biz_blackboard.publisher,'
                . 'biz_blackboard.create_at,FROM_UNIXTIME(biz_blackboard.create_at,\'%Y-%m-%d\') createdate,biz_class.name class_name,dict_grade.grade,CONCAT(dict_grade.grade,biz_class.name) gradeclass,CONCAT("'.$detail_url.'",biz_blackboard.id) url')
            ->select();

        foreach ($result as $k=>$v) {
            $result[$k]['message'] = mb_substr(strip_tags($result[$k]['message']),0,40);
            $result[$k]['message'] = str_replace('&nbsp;','',$result[$k]['message']);
        }

        return $result;

    }

    //app获取家长小黑板列表
    public function getAppBoardParentList( $id,$check,$detail_url,$pageStart,$pageEnd) {
        $Model = M('biz_blackboard');
        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_isread_blackboard ON biz_isread_blackboard.b_id = biz_blackboard.id and biz_isread_blackboard.role_id = '.ROLE_PARENT. ' and biz_isread_blackboard.user_id='.$id.' and boardandclass.class_id = biz_isread_blackboard.class_id','left')
            ->where($check)
            ->group('boardandclass.id')
            ->order('is_read desc,biz_blackboard.create_at desc')
            ->limit($pageStart,$pageEnd)
            ->field('(case when biz_isread_blackboard.user_id is null then 2 else 1 end) is_read,biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.message,biz_blackboard.publisher,'
                . 'biz_blackboard.create_at,FROM_UNIXTIME(biz_blackboard.create_at,\'%Y-%m-%d\') createdate,biz_class.name class_name,dict_grade.grade,CONCAT(dict_grade.grade,biz_class.name) gradeclass,CONCAT("'.$detail_url.'",biz_blackboard.id) url')
            ->select();


        foreach ($result as $k=>$v) {
            $result[$k]['message'] = mb_substr(strip_tags($result[$k]['message']),0,40);
            $result[$k]['message'] = str_replace('&nbsp;','',$result[$k]['message']);
        }

        return $result;

    }

    //绑定小黑板与班级的关系
    public function boardLiveClass( $a_id,$class_id ) {
        $data = array(
            'b_id' => $a_id,
            'class_id' => $class_id
        );
        $row = M('boardandclass')->where( $data )->find();

        if ( !empty($row)) {
            return true;
        } else {
            $id = M('boardandclass')->add( $data );
            return $id;
        }
    }

    public function setReadStatus($id,$classId,$userId,$role)
    {
        $add_data['user_id']=$userId;
        $add_data['b_id']=$id;
        $add_data['role_id']=$role;
        $add_data['class_id']=$classId;
            $model=M('biz_isread_blackboard');
        //判断是否存在,不存在则入库
        $result = $model->where($add_data)->find();
        if(empty($result))
        {
            $model->add($add_data);
        }
        return true;
    }

}