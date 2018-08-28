<?php
namespace Common\Model;
use Think\Model;

class MonitorModel extends Model{

    protected $tableName = 'qr_code';
    public function __construct()
    {
        parent::__construct();
        $this->model=M($this->tableName);

    }
    //入库扫描二维码
    public function setIncQr(){
        $id = $this->model->where('id=1')->setInc('scanning_num',1);
        return $id;
    }

    //安卓下载次数
    public function addAdIncQr(){
        $id = $this->model->where('id=1')->setInc('android_download_num',1);
        return $id;
    }

    //ios链接跳转次数
    public function addIosIncQr(){
        $id = $this->model->where('id=1')->setInc('ios_jump',1);
        return $id;
    }

    //设置分享次数
    public function setShareInfo( $data ) {
        $id = M('share_stati')->add( $data );
        return $id;
    }

    //后台获取二维码管理信息

    public function getQrcodeModel() {
        $code = $this->model->where('id=1')->find();
        return $code;
    }

    //后台获取京版资源和分享管理信息

    public function getShareModel() {
        $code = M('share_stati')->group('share_id,type')->field('id,share_id,create_at,type,count(*) as count')->select();

        foreach ($code as $k=>$v) {
            if (!empty($v['share_id'])) {
                if($v['type']==1) {
                    $res_map['id'] = $v['share_id'];
                    $res_info = M('knowledge_resource')->where( $res_map )->field('browse_count,name')->find();
                    $code[$k]['browse_count'] = $res_info['browse_count'];
                    $code[$k]['name'] = $res_info['name'];
                } else{
                    $res_map['id'] = $v['share_id'];
                    $res_info = M('social_activity')->where( $res_map )->field('browse_count,title')->find();
                    $code[$k]['browse_count'] = $res_info['browse_count'];
                    $code[$k]['name'] = $res_info['title'];
                }
            }
        }
        return $code;
    }

    //京版云活动新增用户数量
    public function getActivitiUserModel() {
        $code = M('activity_register_user')->group('a_id')->field('id,a_id,type,count(*) as count')->select();

        foreach ($code as $k=>$v) {
            if (!empty($v['a_id'])) {
                $res_map['id'] = $v['a_id'];
                $res_info = M('social_activity')->where( $res_map )->field('browse_count,title')->find();
                $code[$k]['browse_count'] = $res_info['browse_count'];
                $code[$k]['name'] = $res_info['title'];
            }
        }
        return $code;
    }

    //入库轮播图数量
    public function setIncFigure( $aid ){
        //$id = $this->model->where('id=1')->setInc('scanning_num',1);
        $data['a_id'] = $aid;
        $info = M('figure_count')->where($data)->find();
        if ( !empty($info) ) {
            $id = M('figure_count')->where($data)->setInc('count',1);
        } else {
            $data['count'] = 1;
            $id = M('figure_count')->add($data);
        }
        return $id;
    }

    //轮播图后台数量统计
    public function getAdCountModel() {
        $row = M('figure_count')
            ->JOIN('ad_img ON ad_img.id = figure_count.a_id')
            ->field('figure_count.count,ad_img.title')
            ->select();
        return $row;
    }


    //获取区县学生总数
    public function getStudentCount($condition=array(),$class_type=''){
        $subQuery = M('auth_student')->where($condition)
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
            ->group('auth_student.id')
            ->field('1')
            ->buildSql();
        $count = M('auth_student')->table($subQuery.' a')->count();
        return $count;
    }


    public function getStudentCountAll(){
        $stuCount = M('auth_student')
            ->count();
        return $stuCount;
    }

    //获取区县家长总数
    public function getParentdentCount($condition=array(),$class_type=''){
        $subQuery = M('auth_parent')->where($condition)
            ->join('dict_schoollist on dict_schoollist.id=auth_parent.school_id','left')
            ->group('auth_parent.id')
            ->field('1')
            ->buildSql();
        $count = M('auth_parent')->table($subQuery.' a')->count();
        return $count;
    }


    public function getParentdentCountAll(){
        $stuCount = M('auth_parent')
            ->count();
        return $stuCount;
    }


    //获取区县老师总数
    public function getTeacherCount($condition=array(),$class_type=''){


        $subQuery = M('auth_teacher')->where($condition)
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id','left')
            ->join('auth_teacher_second on auth_teacher_second.teacher_id=auth_teacher.id','left')
            ->join('dict_course on dict_course.id=auth_teacher_second.course_id','left')
            ->group('auth_teacher.id')
            ->field('1')
            ->buildSql();

        $count = M('auth_teacher')->table($subQuery.' a')->count();

        return $count;
    }


    public function getTeacherCountAll(){
        $stuCount = M('auth_teacher')
            ->count();
        return $stuCount;
    }

    //获取学生的消息
    public function getStuMessage() {

        $count = M('auth_student')
            ->count();

        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = M('auth_student')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('student_name,parent_tel,id')
            ->order('id desc')
            ->select();

        foreach ( $list as $k=>$v ) {
            $map['receive_message_user.user_id'] =$v['id'];
            $map['receive_message_user.role_id'] =3;
            $map['status'] = array('in',array(2,3,5));
            $map['receive_type'] = array('in',array(2,3));

            $subQuery = M('role_message')
                    ->join("receive_message_user on receive_message_user.message_id=role_message.id")
                    ->group('role_message.id')
                    ->field('role_message.id')
                    ->where( $map )
                    ->buildSql();
            $count = M('role_message')->table($subQuery.' m')->count();
            $list[$k]['messageCount'] = $count;
            $messageNum = D('Message')->getUnreadMessagesCount($v['id'],3);
            $list[$k]['messageNum'] = $messageNum['num'];
            $list[$k]['name'] = $v['student_name'];
            $list[$k]['telephone'] = $v['parent_tel'];
        }


        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }

    public function getTeacherMessage() {
        $count = M('auth_teacher')
            ->count();


        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = M('auth_teacher')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('name,telephone,id')
            ->order('id desc')
            ->select();


        foreach ( $list as $k=>$v ) {
            $map['receive_message_user.user_id'] =$v['id'];
            $map['receive_message_user.role_id'] =2;
            $map['status'] = array('in',array(2,3,5));
            $map['receive_type'] = array('in',array(2,3));

            $subQuery = M('role_message')
                ->join("receive_message_user on receive_message_user.message_id=role_message.id")
                ->group('role_message.id')
                ->field('role_message.id')
                ->where( $map )
                ->buildSql();
            $count = M('role_message')->table($subQuery.' m')->count();

            $list[$k]['messageCount'] = $count;
            $messageNum = D('Message')->getUnreadMessagesCount($v['id'],2);
            $list[$k]['messageNum'] = $messageNum['num'];

        }



        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }

    public function getParentMessage() {
        $count = M('auth_parent')
            ->count();

        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = M('auth_parent')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('parent_name,telephone,id')
            ->order('id desc')
            ->select();



        foreach ( $list as $k=>$v ) {
            $map['receive_message_user.user_id'] =$v['id'];
            $map['receive_message_user.role_id'] =4;
            $map['status'] = array('in',array(2,3,5));
            $map['receive_type'] = array('in',array(2,3));

            $subQuery = M('role_message')
                ->join("receive_message_user on receive_message_user.message_id=role_message.id")
                ->group('role_message.id')
                ->field('role_message.id')
                ->where( $map )
                ->buildSql();
            $count = M('role_message')->table($subQuery.' m')->count();
            $list[$k]['messageCount'] = $count;
            $messageNum = D('Message')->getUnreadMessagesCount($v['id'],4);
            $list[$k]['messageNum'] = $messageNum['num'];
            $list[$k]['name'] = $v['parent_name'];

        }

        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }


    public function getTeacherStuCountModel($where) {

        $where['biz_class.is_delete'] = 0;
        $where['biz_class_student.status'] = 2;

        $subQuery = M('auth_teacher')
            ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->field('auth_teacher.id,auth_teacher.name,auth_teacher.telephone,count(biz_class_student.student_id) as sid')
            ->group('auth_teacher.id')
            ->order('sid desc')
            ->where($where)
            ->buildSql();

        $count = M('auth_teacher')->table($subQuery.' m')->count();

        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = M('auth_teacher')
            ->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->field('auth_teacher.id,auth_teacher.name,auth_teacher.telephone,count(biz_class_student.student_id) as sid')
            ->group('auth_teacher.id')
            ->order('sid desc')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        //print_r(M()->getLastSql());die();
        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }

    public function PcCountModel($type,$where) {
        $map['type']=$type;
        if (!empty($where)) {
            $map = array_merge($map,$where);
        }


        $count =M('pc_count')->where($map)->count();
        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = M('pc_count')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();

        if (!empty($list)) {
            foreach ($list as $k=>$v){
                $ad_img_map['id'] = $v['aid'];
                if($type==1) {
                    $info = M('ad_img')->where($ad_img_map)->find();

                } else {
                    $info = M('Social_activity')->where($ad_img_map)->find();

                }

                $list[$k]['name'] = $info['title'];

            }
        }

        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }
}
