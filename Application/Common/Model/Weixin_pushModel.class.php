<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/7/20
 * Time: 11:39
 */
namespace Common\Model;

use Think\Model;
class Weixin_pushModel extends Model
{
    public $model = '';
    protected $tableName = 'weixin_push';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *查询所有数据
     */
    public function getAll($where='',$pageIndex=1, $pageSize=20){
        $data['count'] = $this->model
            ->where($where)
            ->count();

        $data['resources'] = $this->model
            ->where($where)
            ->order('creat_time desc')
            ->page($pageIndex.','.$pageSize)
            ->select();//echo M()->getLastSql();
        return $data;
    }

    /*
      *查询weixin_column表中所有数据
      */
    public function getColumnAll($where='',$pageIndex=1, $pageSize=20){
        $data['count'] = M('weixin_column')
            ->where($where)
            ->count();

        $data['resources'] = M('weixin_column')
            ->where($where)
            ->order('creat_time desc')
            ->page($pageIndex.','.$pageSize)
            ->select();//echo M()->getLastSql();
        return $data;
    }

    /*
     *查询一条数据
     */
    public function getOne($where){
        $resources = $this->model
            ->where($where)
            ->find();
        return $resources;
    }

    /*
     *查询weixin_column表中一条数据
     */
    public function getColumnOne($where){
        $resources = M('weixin_column')
            ->where($where)
            ->find();
        return $resources;
    }

    /*
     *添加操作
     */
    public function addResources($data){
        $resources = $this->model
            ->add($data);
        return $resources;
    }


    /*
     *weixin_column表添加操作
     */
    public function addColumnResources($data){
        $resources = M('weixin_column')
            ->add($data);
        return $resources;
    }


    /*
     *修改操作
     */
    public function saveResources($where,$data){
        $resources = $this->model
            ->where($where)
            ->save($data);
        return $resources;
    }

    /*
     *weixin_column表修改操作
     */
    public function saveColumnResources($where,$data){
        $resources = M('weixin_column')
            ->where($where)
            ->save($data);
        return $resources;
    }

    /*
     *预览操作
     */
    public function preview(){
        $resources = $this->model
            ->where($where)
            ->save($data);
        return $resources;
    }

    /*
     *weixin_column_title_contact表的查询所有数据操作
     */
    public function get_column_title_contact_all($where='',$pageIndex=1, $pageSize=20){

        $data['count'] = M('weixin_column_title_contact')
            ->where($where)
            ->count();

        $data['resources'] = M('weixin_column_title_contact')
            ->where($where)
            ->order('creat_time desc')
            ->page($pageIndex.','.$pageSize)
            ->select();//echo M()->getLastSql();
        return $data;
    }

    /*
     *weixin_column_title_contact表的查询一条数据操作
     */
    public function get_column_title_contact_one($where){
        $resources = M('weixin_column_title_contact')
            ->where($where)
            ->find();
        return $resources;
    }

    /*
     *weixin_column_title_contact表的添加数据操作
     */
    public function get_column_title_contact_add($data){

        $resources = M('weixin_column_title_contact')
            ->add($data);
        return $resources;
    }
    /*
    *weixin_column_title_contact表的修改数据操作
    */
    public function get_column_title_contact_save($where,$data){

        $resources = M('weixin_column_title_contact')
            ->where($where)
            ->save($data);
        return $resources;
    }




    /*
     *weixin_column_title_push_contact表的查询所有数据操作
     */
    public function get_column_title_push_contact_all($where='',$pageIndex=1, $pageSize=20){

        $data['count'] = M('weixin_column_title_push_contact')
            ->where($where)
            ->count();

        $data['resources'] = M('weixin_column_title_push_contact')
            ->where($where)
            ->order('sort desc')
            ->page($pageIndex.','.$pageSize)
            ->select();//echo M()->getLastSql();
        return $data;
    }

    /*
     *weixin_column_title_push_contact表的查询一条数据操作
     */
    public function get_column_title_push_contact_one($where){
        $resources = M('weixin_column_title_push_contact')
            ->where($where)
            ->find();
        return $resources;
    }

    /*
     *weixin_column_title_push_contact表的添加数据操作
     */
    public function get_column_title_push_contact_add($data){

        $resources = M('weixin_column_title_push_contact')
            ->add($data);
        return $resources;
    }
    /*
    *weixin_column_title_push_contact表的修改数据操作
    */
    public function get_column_title_push_contact_save($where,$data){

        $resources = M('weixin_column_title_push_contact')
            ->where($where)
            ->save($data);
        return $resources;
    }

    /*
     *根据栏目ID查询所有数据
     */
    public function getAllById($where,$type){
        if($type == '1'){
            $order = 'weixin_push.show_time desc';
            $fields = 'weixin_push.title,weixin_push.url,weixin_push.img_path,weixin_push.show_time push_show_time,weixin_column_title_push_contact.sort';
        }else{
            $order = 'weixin_column_title_contact.show_time desc,weixin_column_title_push_contact.sort desc';
            $join = 'weixin_column_title_contact on weixin_column_title_contact.id = weixin_column_title_push_contact.weixin_column_title_contact_id';
            $fields = 'weixin_push.title,weixin_push.url,weixin_push.img_path,weixin_push.show_time push_show_time,weixin_column_title_push_contact.sort,weixin_column_title_contact.show_time block_show_time';
        }
        $model = M('weixin_column');
        $resources = $model
            ->field($fields)
            ->join('weixin_column_title_push_contact on weixin_column_title_push_contact.weixin_column_id = weixin_column.id')
            ->join($join)
            ->join('weixin_push on weixin_push.id = weixin_column_title_push_contact.weixin_push_id')
            ->where($where)
            ->order($order)
            ->select();//echo M()->getLastSql();die;
        return $resources;
    }

    /*
     *获取所有的列表类型的数据
     */
    public function getListAll($where){
        $model = M('weixin_column');
        $resources = $model
            ->field('weixin_column_title_contact.id ctcid,weixin_column_title_contact.show_time')
            ->join('weixin_column_title_contact on weixin_column_title_contact.column_id = weixin_column.id')
            ->where($where)
            ->order('weixin_column_title_contact.show_time desc')
            ->select();//echo M()->getLastSql();die;
        return $resources;
    }
}