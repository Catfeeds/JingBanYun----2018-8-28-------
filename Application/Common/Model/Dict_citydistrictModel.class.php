<?php
namespace Common\Model;
use Think\Model;

class Dict_citydistrictModel extends Model{

    public    $model='';
    protected $tableName = 'dict_citydistrict';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

   /*
    * 获取省
    */

    public function getProvince()
    {
      $Model = $this->model;
      $result = $Model
             -> where('level=1')
             -> field ('id,name')
             -> select();
      return $result;
    }

   /*
    * 获取市
    */

    public function getCityByProvince($provinceId)
    {
       if ($_GET)
        {
          $zhiXiaIdArray = array(1,2,9,22);
          $where ="";
          if(in_array($provinceId,$zhiXiaIdArray))
           {
            $where = 'level=1 and '.'id='.$provinceId;
           }
          else
           {
            $where = 'level=2 and '.'upid='.$provinceId;
           }
          $Model = $this->model;
          $result = $Model
             -> where($where)
             -> order('name asc')
             -> field ('id,name')
             -> select();
          return $result;
        }
    }

   /*
    * 获取区县
    */

     public function getDistrictByCity($cityId)
     {
         $Model = $this->model;
         $result = $Model
                -> where("upid=".$cityId)
                -> order('name asc')
                -> field ('id,name')
                -> select();
         return $result;
     }



}