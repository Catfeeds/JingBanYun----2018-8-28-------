<?php
namespace Common\Model;
use Think\Model;

class User_access_totalModel extends Model{

    public    $model='';

    public function __construct(){
        parent::__construct();
    }
    /**
     *描述：搜索
     */
    public function selects($where,$join,$group){
       /* return M('usertables.user_access_total')
            ->where($where)
            ->group($group)
            ->select();*/
        $sql = "SELECT
	SUM(a.access_total) access_total,
	access_time
FROM
	usertables.user_access_total a
$join
WHERE
$where
GROUP BY 
$group";
        $result = M()->query($sql);
        return $result;
    }

    /**
     *描述：user_access_digital_classroom添加操作
     */
    public function userAccessDigitalClassroomAdd($data){
        return M('usertables.user_access_digital_classroom')
            ->add($data);
    }
}
