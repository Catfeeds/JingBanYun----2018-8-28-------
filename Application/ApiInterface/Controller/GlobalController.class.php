<?php


namespace ApiInterface\Controller;
use Think\Controller;
class GlobalController extends Controller {
    public static $version_config;

    public function _initialize() {

        if (empty($this->version_config)){
            $this->quary_version_config();
        }
        
    }

    public function quary_version_config()
    {
        //获取url中的控制器和方法
        $uri = $_SERVER["REQUEST_URI"];

        $match = preg_split('/\//', $uri);
        //获取控制器名
        $uri_version = $match[2];
        $version_tmp = preg_replace('/[Vv]ersion/','',$uri_version);
        $version = preg_replace('/_/','.',$version_tmp);
        $version = floatval($version);

        //获取控制器名
        $controler_name = $match[3];
        //获取方法名
        $function_name = $match[4];
        $re = preg_match('/\?/', $function_name, $match);
        if ($re == true) {
            $match = preg_split('/\?/', $function_name);
            $function_name = $match[0];
        }


        //获取控制器和方法名
        $controler_name_lower = strtolower($controler_name);
        $function_name_lower = strtolower($function_name);
        $map = array(
            'controller_name'=> $controler_name_lower,
            'function_name'=> $function_name_lower,
            'version'=> array('elt',$version),
            'is_delete' => 1
        );

        //获取配置的版本号,控制器
        $mo_version_control = M('api_version_control');
        $re_version_control = $mo_version_control
            ->field('controller_name,function_name,version_path,version,is_delete')
            ->where($map)
            ->order('version desc')
            ->limit(1)
            ->find();
        //print_r(M()->getLastSql());die();
        $map1 = array(
            'controller_name'=> $controler_name_lower,
            'function_name'=> $function_name_lower,
            'version'=> array('GT',$version),
            'is_delete' => 1
        );

        $is_cun = $mo_version_control->order('version')->where( $map1 )->find();

        if (empty($is_cun)) {
            $re_version_control['is_version_show'] = $version;
        } else {
            $re_version_control['is_version_show'] = $is_cun['version'];    
        }

        $this->version_config=$re_version_control;
        return;
    }
}