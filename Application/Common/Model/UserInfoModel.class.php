<?php
namespace Common\Model;
use Think\Model;

class UserInfoModel extends Model{

    public    $model='';
    protected $tableName = 'userinfo';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 根据手机号码获取资料
     * tel 手机号
     */
    public function getUserInfoByTelephone($telephone)
    {
        $check['phoneno'] = $telephone;
        $result = $this->model->where($check)->find();
        return $result;
    }
    public function updateInfoByTelephone($data,$telephone)
    {
        $this->model->where('phoneno='.$telephone)->save($data);
    }

    //$type 配置文件 $Inifo 用户id，发送的信息 $arrangeTime时间

    public function  sendMsg( $role,$Idifo,$msginfo,$type,$arrangeTime) {

        if(!is_string($msginfo))
            $msginfo = json_encode($msginfo);
        $studentId = $Idifo;
        $studata['role'] = $role;
        $studata['user_id'] = $studentId;
        $studata['msg'] = $msginfo;
        $studata['type'] = $type;
        $studata['send_time'] = $arrangeTime;
        $sid = M('queue_msg')->add($studata);

        if ($sid !== false ) {
            return true;
        } else {
            return false;
        }

    }
    //脚本跑
    public function getCronHomeWork(){
        $stulist = M('queue_msg')->select();
        if(!empty($stulist)) {
            foreach ($stulist as $k=>$v) {
                if (strtotime($v['send_time']) < time() ) {
                    A('Home/Message')->addPushUserMessage($v['type'],$v['role'],$v['user_id'],json_decode($v['msg'],true));
                    $del['id'] = $v['id'];
                    M('queue_msg')->where($del)->delete();

                }
            }
        }

    }

}
