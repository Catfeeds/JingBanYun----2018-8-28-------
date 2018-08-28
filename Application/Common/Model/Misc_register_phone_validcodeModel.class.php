<?php
namespace Common\Model;
use Think\Model;

class Misc_register_phone_validcodeModel extends Model{

    public    $model='';
    protected $tableName = 'misc_register_phone_validcode';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 存储一条验证码
     * tel 手机号
     * code 验证码
     */
    public function saveVerifyCode($tel,$code)
    {
            $check['telephone'] = $tel;
            $code = $this->model->where($check)->find();
            $data['telephone'] = $tel;
            $data['code'] = $code;//todo
            $data['create_at'] = time();
            if (empty($code)) {
              $this->model->add($data);
              return array(RUN_SUCCESS,'verifyCode added.');
            } else {
              $this->model->where($check)->save($data);
              return array(RUN_SUCCESS,'verifyCode updated.');
           }
    }
    public function verifyCode($telephone,$code)
    {
        $check['telephone'] = $telephone;
        $check['code'] = $code;
        if(!empty($this->model->where($check)->find()))
        {
            $this->model->where($check)->delete();
            return true;
        }
        return false;

    }


}