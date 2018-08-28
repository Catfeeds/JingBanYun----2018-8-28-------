<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/9/9
 * Time: 10:10
 */
namespace Common\Model;
use Think\Model;
/*************************************************教材版本管理***************************************************************/
class Exercises_textbook_versionModel extends Model{
    public    $model='';
    protected $tableName = 'exercises_textbook_version';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    /*
     *查询数据
     * $where数组
     */
    public function getResourcesAll($where=array(),$pageIndex = 1, $pageSize = 20){
        $resources = $this->model
            ->where($where)
            ->page($pageIndex . ',' . $pageSize)
            ->select();
        $count = $this->model
            ->where($where)
            ->select();
        $count = count($count);
        $data['resources'] = $resources;
        $data['count'] = $count;
        return $data;
    }
    /*
     *添加
     */
    public function dataAdd($name){
        $addStatus = $this->model->add($name);
        return $addStatus;
    }

    /*
     *修改
     */
    public function dataSave($name,$where){
        $saveStutus = $this->model->where($where)->save($name);
        return $saveStutus;
    }

    /*
     *获取版本详情
     */
    public function getInfo($id){
        $info = M()->query("select * from exercises_textbook_version WHERE id=$id");
        return $info;
    }
    /*
     *查询所有数据无分页
     */
    public function getAll(){
        $resources = $this->model
            ->select();
        return $resources;
    }

    /*
     * 获取所有版本
     */
    public function getAllVersions()
    {
        return M()->query("SELECT id,version_name name FROM exercises_textbook_version");
    }

    /*
     *根据条件查询
     */
    public function selectResourceByConditions($where){
        $resources = $this->model
            ->where($where)
            ->select();
        return $resources;
    }
}