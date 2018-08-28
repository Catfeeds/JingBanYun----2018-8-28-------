<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/9/9
 * Time: 11:00
 */
namespace Common\Model;
use Think\Model;

class Exercises_auth_permissionsModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_auth_permissions';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }

    /*
     *查询
     */
    public function getResources($where=array()){
            $where['delete_status'] = ROLE_IS_DELETE;
            $resources = $this->model
                ->field('exercises_auth_permissions.*,GROUP_CONCAT( exercises_permissions.module_name
order by exercises_permissions.module_name) AS module_name,exercises_permissions.module_action,exercises_permissions.parent_id')
                ->join('exercises_auth_permissions_concat on exercises_auth_permissions_concat.auth_permissions_id = exercises_auth_permissions.id','left')
                ->join('exercises_permissions on exercises_permissions.id = exercises_auth_permissions_concat.permissions_id','left')
                ->where($where)
                ->group('exercises_auth_permissions.id')
                ->select(); //echo M()->getLastSql();die;
            $count = count($resources);
            $data['resources'] = $resources;
            $data['count'] = $count;
            return $data;
    }
    /*
     * 获取所有角色
     */
    public function getRoleList()
    {
        return M()->query('SELECT id,name FROM exercises_auth_permissions');
    }

    /*
     *修改数据
     * $data数组
     * $where数组
     */
    public function savaResourse($data, $where=array()){
        $status = $this->model
            ->where($where)
            ->save($data);
        return $status;
    }

    /*
     *往主表里添加数据
     */
    public function addData($data){
        $status = $this->model
            ->add($data);
        return $status;
    }

    /*
     *往角色权限关联表中添加数据
     */
    public function addResources($id,$data){
        $authPermissionsConcatMode = M('exercises_auth_permissions_concat');
        foreach($data as $val){
            $per_child_data['permissions_id']=$val;
            $per_child_data['auth_permissions_id']=$id;
            $per_child_data['creat_at']=time();
            $per_data[]=$per_child_data;
        }
        if($authPermissionsConcatMode->addAll($per_data)===false){
            return false;
        }else{
            return true;
        }
    }

    /*
     *删除角色权限关联表中的相关数据
     */
    public function deleteResources($where=array()){
        $authPermissionsConcatMode = M('exercises_auth_permissions_concat');
        $deleteStatus = $authPermissionsConcatMode
            ->where($where)
            ->delete();
        return $deleteStatus;
    }

    /*
     *查询权限表中所有数据
     */
    public function getResourcesAll($where=array()){
        $Model = M('exercises_permissions');
        $data = $Model->where($where)
            ->select();
        return $data;
    }

    /*
     *查询角色权限关联表中的数据
     */
    public function getResourcesOfAuthPermissionsConcat($where){
        return M('exercises_auth_permissions_concat')->where($where)
            ->select();
    }
    /*
     *查询一条数据
     */
    public function getResourcesOne($where){
        $one = $this->model->where($where)
            ->find();
        return $one;
    }

    /*
     *获得当前角色的权限
     */
    public function getPermissionsByRole($role){
        $where['exercises_auth_permissions_concat.auth_permissions_id'] = $role;
       return M('exercises_auth_permissions_concat')
            ->field('exercises_permissions.*')
            ->join('exercises_permissions on exercises_permissions.id = exercises_auth_permissions_concat.permissions_id')
            ->where($where)
           ->select();
    }

    /*
     *查询该角色下有无关联账号
     */
    public function haveAccountByrole($role)
    {
        $where['exercises_auth_permissions.id'] = $role;
        $where['exercises_account.delete_status'] = DELETE_STATUS_FALSE;
        return $this->model
            ->join('exercises_account on exercises_account.role = exercises_auth_permissions.id')
            ->where($where)
            ->select();
    }

}