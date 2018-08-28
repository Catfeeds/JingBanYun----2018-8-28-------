<?php
namespace Common\Model;
use Think\Model;
define('SHUFFLING',7);//新闻轮播管理
define('TEACH',8);//教学动态管理
define('WINWORK',9);//获奖作品管理
define('HOT',10);//热点话题管理
define('EDUCATION',11);//教育新闻管理

class InformationModel extends Model{

    public    $model='';
    protected $tableName = 'ad_img';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    //查询所有的热点栏目
    public function getHotColumn() {
        $data['is_display'] = 1;
        $data['module_name'] = '热点资讯';
        $list = M('dict_column')->where( $data )->select();
        return $list;
    }

    //资讯入库操作
    public function addInfor($adddata) {
        $id = M('social_expert_information')->add($adddata);
        return $id;
    }

    //根据栏目获取栏目下的资讯
    public function getInforList($where) {
        $list = M('social_expert_information')->where($where)->order('id desc')->select();
        return $list;
    }

    //修改时间间隔
    public function addOrUpdateTimelength($cid,$time_length) {
        $where['model_type'] = $cid;
        $info = M('ad_img_contact')->where($where)->find();
        if (!empty($info)) {
            $savemap['time_length'] = $time_length;
            $id = M('ad_img_contact')->where($where)->save($savemap);
        } else {
            $addmap['time_length'] = $time_length;
            $addmap['model_type'] = $cid;
            $id = M('ad_img_contact')->add($addmap);
        }

        return $id;
    }

    //获取栏目轮播的时长
    public function getTimeLength($cid) {
        $data['model_type'] = $cid;
        $info = M('ad_img_contact')->where($data)->find();
        return $info;
    }

    //获取栏目轮播的时长
    public function findPlayOrder($where) {
        $info = M('social_expert_information')->where($where)->find();
        return $info;
    }

    //根据资讯id查询资讯数据
    public function findInfor( $id,$status=null ) {
        $data['id'] = $id;
        if (!empty($status)) {
            $data['status'] = '4';
        }
        $info = M('social_expert_information')->where($data)->find();
        $info['starttime'] = date('Y-m-d',$info['starttime']);
        $info['endtime'] = date('Y-m-d',$info['endtime']);
        return $info;
    }

    public function editNewsInfor($editata,$id) {
        $map['id'] = $id;
        $id = M('social_expert_information')->where($map)->save($editata);
        return $id;
    }

    /*
     *获得资讯搜索结果
     */
    public function getSearchResources($where,$pageIndex=1,$pageSize=20){
        $where['dict_column.is_display'] = '1';
        $resource = M('social_expert_information')
            ->field('social_expert_information.type,social_expert_information.short_content,social_expert_information.title,social_expert_information.id,social_expert_information.browse_count,social_expert_information.up_time,social_expert_information.linkaddress,social_expert_information.pc_cover,social_expert_information.content')
            ->join('dict_column on dict_column.id = social_expert_information.type')
            ->where($where)
            ->order('social_expert_information.up_time desc')
            ->page($pageIndex.','.$pageSize)
            ->select();//echo M()->getLastSql();die;

        $counts = M('social_expert_information')
            ->field('social_expert_information.title,social_expert_information.id,social_expert_information.browse_count,social_expert_information.up_time,social_expert_information.linkaddress,social_expert_information.pc_cover,social_expert_information.content')
            ->join('dict_column on dict_column.id = social_expert_information.type')
            ->where($where)
            ->order('social_expert_information.up_time desc')
            ->count();
        $data['resource'] = $resource;
        $data['counts'] = $counts;
        return $data;
    }

    /*
     *热点资讯首页栏目内容
     */
    public function getIndexColumnResource($type){
        $where['dict_column.is_display'] = '1';
        $where['social_expert_information.status'] = '4';
        $where['social_expert_information.type'] = $type;
        if($type == SHUFFLING){
            $where['starttime'] = array('LT',time());
            $where['endtime'] = array('GT',time());
            $resource = M('social_expert_information')
                ->field('social_expert_information.title,social_expert_information.short_content,social_expert_information.short_content,social_expert_information.id,social_expert_information.browse_count,social_expert_information.up_time,social_expert_information.publisher,social_expert_information.create_at,social_expert_information.file_path,social_expert_information.linkaddress,social_expert_information.type,social_expert_information.pc_cover,social_expert_information.content')
                ->join('dict_column on dict_column.id = social_expert_information.type')
                ->where($where)
                ->order('social_expert_information.play_order')
                ->limit(3)
                ->select();
        }else{
            $resource = M('social_expert_information')
                ->field('social_expert_information.title,social_expert_information.short_content,social_expert_information.id,social_expert_information.browse_count,social_expert_information.up_time,social_expert_information.publisher,social_expert_information.create_at,social_expert_information.file_path,social_expert_information.linkaddress,social_expert_information.type,social_expert_information.pc_cover,social_expert_information.content')
                ->join('dict_column on dict_column.id = social_expert_information.type')
                ->where($where)
                ->order('social_expert_information.up_time desc')
                ->select();
        }
        $data['resource'] = $resource;
        $data['counts'] = count($resource);
        return $data;
    }

    /*
     *获取热点资讯的所有栏目
     */
    public function getColumn(){
        $where['module_name'] = '热点资讯';
        $resources = M('dict_column')->field('id,column_name')->where($where)->select();
        return $resources;
    }

    /*
     *根据浏览度排名
     */
    public function browseRanking(){
        $where['social_expert_information.status'] = '4';
        $where['type'] = array('NEQ',SHUFFLING);
        $resource = M('social_expert_information')
            ->field('title,short_content,id,create_at,type')
            ->where($where)
            ->order('browse_count desc')
            ->limit('10')
            ->select();
        return $resource;
    }

    //栏目列表
    /**
     * @return \Model|string|Model
     */
    public function getInforListInfo($where)
    {
        $User = M('social_expert_information'); // 实例化User对象
        $count      = $User->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list = M('social_expert_information')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('id desc')
            ->select();
        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }
}
