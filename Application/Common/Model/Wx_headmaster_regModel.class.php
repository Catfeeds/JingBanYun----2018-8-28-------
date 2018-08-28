<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2017/12/28
 * Time: 10:00
 */
namespace Common\Model;

use Think\Exception;
use Think\Model;
class Wx_headmaster_regModel extends Model
{
    public $model = '';
    protected $tableName = 'wx_headmaster_reg';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /**
     *描述:wx_trave_activity入库操作
     */
    public function add($data)
    {
        try {
            $result = $this->model
                ->add($data);
        }catch(Exception $e){
            if(strpos($e->getMessage(),'Duplicate entry') !== false)
              return -1;
             return false;
        }
        return !empty($result);
    }
    /**
     *描述：wx_trave_activity_order入库操作
     */
    public function getRegData($pageIndex = 1,$pageSize = 20 ,&$count)
    {
        if($pageIndex === -1)
            $limitStr = '';
        else {
            $startIndex = $pageSize * (intval($pageIndex) - 1);
            $limitStr = " LIMIT $startIndex,$pageSize ";
        }
        $count = M()->query("SELECT count(1) count FROM wx_headmaster_reg")[0]['count'];
        $result = M()->query("SELECT name,school_name,telephone,professional,create_at FROM wx_headmaster_reg $limitStr");
        return $result;
    }


}