<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 下午 5:55
 */
namespace Common\Model;

use Think\Model;

class Dict_column_contactModel extends Model
{
    public $model = '';
    protected $tableName = 'dict_column_contact';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
        *获得新京版资源栏目内容总数
        */
    public function getResourceCount($check = array())
    {
        $resource = $this->model
            ->field('dict_column_contact.*,knowledge_resource.name,knowledge_resource.putaway_time')
            ->join('knowledge_resource on dict_column_contact.resource_id=knowledge_resource.id','left')
            ->where($check)
            ->count();
        return $resource;
    }

    /*
     *获取所有的数据
     */
    public function get_resource_all($check=array()){
        $count = $this->getResourceCount($check);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $resource = $this->model
            ->field('dict_column_contact.*,knowledge_resource.name,knowledge_resource.putaway_time')
            //->join('knowledge_resource on dict_column_contact.resource_id=knowledge_resource.id','left')
            ->join('knowledge_resource on dict_column_contact.resource_id=knowledge_resource.id')
            ->where($check)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('dict_column_contact.status desc,sort')
            ->select();         
        $data['page'] = $show;
        $data['list'] = $resource;
        return $data;
    }
}