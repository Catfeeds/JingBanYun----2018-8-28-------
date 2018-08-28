<?php
namespace Home\Controller;

use Think\Controller;


class ScheduledTaskController extends PublicController{
    private $pageSize;
    public function __construct() {
        parent::__construct();
        $this->pageSize = 20;
    }
    private function getPageSize()
    {
        return $this->pageSize;
    }

    /**
    * 京版活动前2天提醒
    *
    */
    public function activity2DaysNotify()
    {


    }

     /**
     * 教师风采排名排序提醒
     *
     */
    public function teacherStyleRankNotify()
    {

    }

    /**
     * VIP信息提醒
     *
     */
    public function vipInfoNotify()
    {
       //个人团体VIP仅剩10天
        $VIPAuthArray = array('4' => '个人VIP','3' => '团体VIP');
        $roleIdArray = array(ROLE_TEACHER,ROLE_STUDENT,ROLE_PARENT);
        foreach($VIPAuthArray as $vipId=>$vipName) {
            foreach ($roleIdArray as $roleId) {
                $userInfoList = D('Account_auths')->getAccountsByAuthIdAndDaysLeft($vipId, 10, $roleId);
                if(!empty($userInfoList))
                {
                    $parameters = array('msg' => array($vipName,date('Y-m-d',time()+10*86400)), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('VIP_EXPIREAFTER10DAYS', $roleId, implode(',',array_column($userInfoList,'user_id')), $parameters);
                }

            }
        }
        //expired
        foreach($VIPAuthArray as $vipId=>$vipName) {
            foreach ($roleIdArray as $roleId) {
                $userInfoList = D('Account_auths')->getAccountsByAuthIdAndDaysLeft($vipId, 0, $roleId);
                if(!empty($userInfoList))
                {
                    $parameters = array('msg' => array($vipName), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('VIP_EXPIRED', $roleId, implode(',',array_column($userInfoList,'user_id')), $parameters);
                }

            }
        }
    }

    /**
     * 学习轨迹提醒
     *
     */
    public function learningPathNotify()
    {
        $parentList = D('Auth_student')->getAllStudentParentPair();
        foreach($parentList as $key=>$val)
        {
            $parentId = $val['parent_id'];
            $studentName = $val['name'];
            $parameters = array( 'msg' => array($studentName) , 'url' => array( 'type' => 0));
            A('Home/Message')->addPushUserMessage('STUDY_LEARNPATH_CHILD',4,$parentId,$parameters);
        }
    }


}