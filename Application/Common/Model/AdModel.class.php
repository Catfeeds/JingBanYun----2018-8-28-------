<?php
namespace Common\Model;
use Think\Model;


define('PROPAGANDA_STATUS_DECLINED',1);       //已拒绝
define('PROPAGANDA_STATUS_WAITFORVERIFY',2);  //待审核
define('PROPAGANDA_STATUS_VERIFIED',3);       //已审核
define('PROPAGANDA_STATUS_OVER',4);      //已结束
define('PROPAGANDA_STATUS_OFFSHELF',5);       //已下架
define('PROPAGANDA_STATUS_PUBLISHING',6);      //发布中
define('PROPAGANDA_STATUS_PUBLISHED',7);      //已发布

class AdModel extends Model{
    
    public    $model='';
    protected $tableName = 'ad_img';
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 入库广告
     */ 
    public function inserdata( $post,$file ){
        $model=$this->model;

        $data = [
            'title' => $post['title'],
            'file_name' => $file['name'],
            'file_path' => $file['url'],
            'type' => $post['type'],
            'url_jump_type' => $post['url_jump_type'],
            'create_at' => time(),
            'starttime' => strtotime($post['activityStart']),
            'endtime' => strtotime($post['activityEnd']),
            'url' => $post['url'],
        ];
        $res = $model->add( $data );
        return $res;
    }

    //广告列表
    public function getAdList( $where ) {
        $Model=$this->model;
        $count = $Model
            ->where( $where )
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        $show = $Page->show();
        $result = $Model
            ->order('create_at desc')
            ->where( $where )
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $data['show'] = $show;
        $data['list'] = $result;

        return $data;
    }

    //删除广告
    public function delAd( $id ) {
        $Model=$this->model;
        $data['id'] = $id;
        $res = $Model->where( $data )->delete();
        return $res;
    }

    //获取app接口轮播图,或广告
    public function getLunbo( $types ) {
        $where['status'] = PROPAGANDA_STATUS_PUBLISHED; //上架
		$time = time();
        $where['starttime'] = array('lt',$time);
        $where['endtime'] = array('gt',$time);
        $where['type'] = $types;
        $list = M('ad_img')->field('title,file_path,url_jump_type,url,url target_url,starttime,endtime,display_method,display_time,web_url,child_page_url')->where($where)->select();
        return $list;
    }

    //获取app接口轮播图
    public function getLunboList( $types ) {
        $data['type'] = $types['type'];
        $data['status'] = PROPAGANDA_STATUS_PUBLISHED; //上架
        $time = time();
        $data['starttime'] = array('lt',$time);
        $data['endtime'] = array('gt',$time);
        $data['model_type'] = $types['model_type'];
        $list = M('ad_img')->field('id,title,file_path,type,url_jump_type,url')->where( $data )->order('sort asc')->select();
        return $list;
    }
    //根据id获取广告
    public function getIdLunbo( $id ) {
        $data['id'] = $id;
        $data['status'] = PROPAGANDA_STATUS_PUBLISHED; //上架
        $time = time();
        $where['starttime'] = array('lt',$time);
        $where['endtime'] = array('gt',$time);
        return M('ad_img')->where($data)->find();
    }
    //获取轮播图间隔
    public function getLunboTimeGap($model_type)
    {
        $where['model_type'] = $model_type;
        $result = M('ad_img_contact')->where($where)->find();
        if(empty($result))
            return -1;
        else
            return $result['time_length'];
    }

    //根据id获取广告
    public function getIdLunboPc() {
        $data['type'] = 4;
        $time = time();
        $where['starttime'] = array('lt',$time);
        $where['endtime'] = array('gt',$time);
        return M('ad_img')->where($data)->find();
    }
}