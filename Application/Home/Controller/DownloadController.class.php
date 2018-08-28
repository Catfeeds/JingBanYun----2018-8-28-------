<?php
namespace Home\Controller;

use Think\Controller;

class DownloadController extends PublicController
{
    public function index()
    {
        $session_num = $_SESSION['num'];
        if (empty($session_num))
            $_SESSION['num'] = 0;

        $is_mobile = isMobile();
        $session_num =$_SESSION['num'];

        if ($is_mobile && $session_num==0){ //手机扫描
            D('Monitor')->setIncQr();
        }

        $_SESSION['num']++;

        $path = $this->getApkPath();
        $this->assign('path',$path);//安卓的下载地址
        $this->display();
    }

    //添加安卓下载次数
    public function addAdNum() {
        $name = $_POST['name'];
        if ( $name == 'ios') {
            $id = D('Monitor')->addIosIncQr();
        } else {
            $id = D('Monitor')->addAdIncQr();
        }

        if ( $id ) {
            $this->ajaxReturn(200);
        } else {
            $this->ajaxReturn(400);
        }
    }

    public function ipad()
    {
        $this->display();
    }

    public function iospad()
    {

    }

    public function Adpad()
    {

    }
    public function getNewestVersion($appName='京版云')
    {
        $model = D('App_version_control');
        $ostype = I('request.ostype');
        $version = I('request.version');

        $where['system_type'] = strtolower($ostype);
        $where['putaway_status'] = 1;
        $where['app_name'] = $appName;
        $where['putaway_time'] = array('ELT',time());
        $result = $model->returnDate($where);

        if($ostype=='Android')
        {
            if(!empty($result[0]['version_number']) && !empty($result[0]['version']) && !empty($result[0]['download_path']) && !empty($result[0]['update_content']) && !empty($result[0]['putaway_time'])){
                $out['version_number']= $result[0]['version_number'];
                $out['version']= $result[0]['version'];
                $out['download_path'] = $result[0]['download_path'];
                $out['comment'] = $result[0]['update_content'];
                $out['release_time'] = date("Y-m-d",$result[0]['putaway_time']);
                $out['update'] = ($result[0]['update_forced'] == 0) ? 0 : 1;
            }else{
                $out['version_number']= 0;
                $out['version']= '2.5.0';
                $out['download_path'] = 'http://www.jingbanyun.com/Public/apk/JBY-2_5_0.apk';
                $out['comment'] = '1.班级行模块大改版，强烈建议安装最新版本
2.京版云logo修改
3.微信扫一扫京版资源二维码直接进入app详情页，无需从浏览器打开
4.语音打分将等级改为具体分值
5.添加注册教研员提示语';
                $out['release_time'] = '2017-01-15';
                $out['update'] = ($result[0]['update_forced'] == 0) ? 0 : 1;
            }
            $this->showjson(0,'',$out);
        }

        if($ostype=='ios')
        {
            if(!empty($result[0]['version_number']) && !empty($result[0]['version']) && !empty($result[0]['download_path']) && !empty($result[0]['update_content']) && !empty($result[0]['putaway_time'])) {
                $out['version_number']= $result[0]['version_number'];
                $out['version']= $result[0]['version'];
                $out['download_path'] = $result[0]['download_path'];
                $out['comment'] = $result[0]['update_content'];
                $out['release_time'] = date("Y-m-d",$result[0]['putaway_time']);
                $out['update'] = ($result[0]['update_forced'] == 0) ? 0 : 1;
            }else{
                $out['version_number']= 0;
                $out['version']= '2.6.0';
                $out['download_path'] = 'https://itunes.apple.com/us/app/jin-ri-ke-tang-bei-jing-chu/id1060492678?l=zh&ls=1&mt=8';
                $out['comment'] = '1.优化京版活动,使界面更清新;
2.新增京版活动专栏和活动投票功能；
3.完善个人资料的页面, 体验极致功能;
4.新增班级行推送,让信息及时传达;
5.新增版本控制，体验最新京版云应用。
6.新增投票和活动详情分享功能，让投票便捷。';
                $out['release_time'] = '2017-05-04';
                $out['update'] = ($result[0]['update_forced'] == 0) ? 0 : 1;
            }
            $this->showjson(0,'',$out);
        }
    }

    public function masterMaterialVersion()
    {
        $this->getNewestVersion('精通教材');
    }
    /**
     * 版本控制
     */
    public function version()
    {
        $this->getNewestVersion('京版云');
    }

    /*
     *获取安卓最新APK下载地址
     */
    public function getApkPath($ostype='android',$appName = '京版云'){
        $where['system_type'] = strtolower($ostype);
        $where['putaway_status'] = 1;
        $where['app_name'] = $appName;
        $where['putaway_time'] = array('ELT',time());
        $model = D('App_version_control');
        $result = $model->returnDate($where);
        $out['download_path'] = !empty($result[0]['download_path']) ? $result[0]['download_path'] : 'http://www.jingbanyun.com/Public/apk/JBY-2_5_0.apk';
        return $out['download_path'];
    }
}