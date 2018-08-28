<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 上午 11:41
 */
namespace Common\Model;

use Think\Model;
class PropagandaManagementModel extends Model
{
    public $model = '';
    protected $tableName = 'ad_img';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *获取一条广告数据
     */
    public function getData($id){
        $where['id'] = $id;
        $data = $this->model
            ->where($where)
            ->find();
        return $data;
    }
    public function addData($id=0,$data,&$errorInfo)
    {
      $where['type'] =  $data['type'];
      if($where['type'] == TYPE_LUNBO)
      {
        $where['model_type'] = $data['model_type'];
      }

      if($id!=0)
          $where['id'] = array('neq',$id);

        //sort check
        if($where['type'] == TYPE_LUNBO)
        {
            $where['sort'] = $data['sort'];
            $selectData = $this->model
                ->where($where)
                ->find();
            unset($where['sort']);
            if(!empty($selectData))
            {
                $errorInfo = '已有轮播图是该播放顺序,请重新输入';
                return false;
            }
        }
        else {
            $selectData = $this->model
                ->where($where)
                ->select();

            for ($i = 0; $i < sizeof($selectData); $i++) {
                if ((($selectData[$i]['starttime'] >= $data['starttime'] && $selectData[$i]['starttime'] <= $data['endtime']) ||
                    ($selectData[$i]['endtime'] >= $data['starttime'] && $selectData[$i]['endtime'] <= $data['endtime']) ||
                    ($data['starttime'] >= $selectData[$i]['starttime'] && $data['starttime'] <=$selectData[$i]['endtime']) ||
                    ($data['endtime'] >= $selectData[$i]['starttime'] && $data['endtime'] <= $selectData[$i]['endtime']))
                ) {
                    $errorInfo = '添加/修改时间与现有宣传页时间冲突，无法添加';
                    return false;
                }
            }
        }
        if($id == 0)
        $result = $this->model
            ->add($data);
        else
         $result = $this->model->where("id=$id")->save($data);
      if($result !== false)
          return true;
       else
       {
           $errorInfo = '添加/修改失败，请联系管理员';
           return false;
       }
    }
    public function updateData($id,$data,&$errorInfo)
    {
       return $this->addData($id,$data,$errorInfo);
    }

    public function deleteData($id)
    {
        $result = $this->model->where("id=$id")->delete();
        if($result !== false)
            return true;
        else
            return false;
    }

    public function updateStatus($id,$status)
    {
        $data['status'] =  $status;
        $result = $this->model->where("id=$id")->save($data);
        if($result !== false)
            return true;
        else
            return false;
    }

    public function getDataList($type,$model_type=0,$pageIndex=1,$pageSize=20,&$count,$where=array())
    {
        $where['type'] =  $type;
        if(0 != $model_type)
        {
            $where['model_type'] = $model_type;
        }
        $count =  $this->model
            ->where($where)
            ->field('count(1) as count')
            ->find();
        $count = $count['count'];

        $data = $this->model
            ->where($where)
            ->page($pageIndex.','.$pageSize)
            ->select();
        for($i=0;$i<sizeof($data);$i++)
        {
            $currentTime = time();
            if($data[$i]['status'] == PROPAGANDA_STATUS_PUBLISHED ) {
                if ($currentTime >= $data[$i]['endtime'])
                    $data[$i]['status'] = PROPAGANDA_STATUS_OVER;
                else if ($currentTime <= $data[$i]['starttime'])
                    $data[$i]['status'] = PROPAGANDA_STATUS_PUBLISHING;
            }
        }
        //sort again by status and endtime DESC
        foreach ($data as $key => $row)
        {
            $status[$key]  = $row['status'];
            $endtime[$key] = $row['endtime'];
        }
        array_multisort($status, SORT_DESC, $endtime, SORT_DESC, $data);
        return $data;
    }

    public function updateTimeLength($model_type=0,$length)
    {
        $where['model_type'] =  $model_type;
        $model = M('ad_img_contact');
        $result = $model->where($where)->find();
        if(empty($result))
        {
            $data['time_length'] = $length;
            $data['model_type'] = $model_type;
            return $model->add($data);
        }
        else
        {
            $data['time_length'] = $length;
        }
        return $model->where($where)->save($data);
    }

    public function getTimeLength($model_type=0)
    {
        $where['model_type'] =  $model_type;
        $result = M('ad_img_contact')->where($where)->field('time_length')->find();
        return $result['time_length'];
    }
}