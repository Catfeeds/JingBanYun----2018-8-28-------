<?php
namespace Common\Model;
use Think\Model;

class Exercises_platformModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_platform';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }

    /*
     *  获取平台列表
     */
    public function getPlatformListByType($type=0)
    {
        $type = intval($type);
        $where = '';
        if(!empty($type))
            $where = "WHERE type=$type";
        $result = M()->query("SELECT id,name FROM exercises_platform $where");
        return $result;
    }
    /*
     * 获取第三方平台信息
     */

    public function getPlatformInfo( $id )
    {
        $id = intval($id);
        $result = M()->query("SELECT name,type FROM exercises_platform WHERE id=$id");
        return $result[0];
    }

    /*
     * 获取当前习题或试卷是否已发布
     */
    public function getIsPublished($platformId,$resourceType=RESOURCETYPE_EXERCISE,$exerciseIds='',&$errorInfo)
    {
        $platformId =  intval($platformId);
        if( 0 == $platformId || empty($exerciseIds)) {
            $errorInfo = '输入参数错误';
            return false;
        }
        $idArray = explode(',',$exerciseIds);
        foreach($idArray as $key=>$val)
        {
            if(RESOURCETYPE_EXERCISE == $resourceType)
                $field = 'exercise_id';
            else
                $field = 'paper_id';
            $queryResult = M()->query("SELECT endtime FROM exercises_platform_exercises WHERE platform_id = $platformId AND resource_type=$resourceType AND $field=$val");
            if(!empty($queryResult))
            {
                //如果过期则删除本记录并返回TRUE
                if(strtotime($queryResult[0]['endtime']) < time())
                {
                    M()->execute("DELETE FROM exercises_platform_exercises WHERE platform_id = $platformId AND resource_type=$resourceType AND $field=$val");
                    return true;
                }

                if(RESOURCETYPE_EXERCISE == $resourceType)
                 $errorInfo = "ID:$val 习题已经发布，无法再次发布，如需修改发布信息，请到已发布资源页面操作";
                else
                 $errorInfo = "ID:$val 试卷已经发布，无法再次发布，如需修改发布信息，请到已发布资源页面操作";
                return false;
            }
        }
        return true;
    }
    /*
     * 插入发布信息
     */
    public function insertPublishInfo($platformId, $resourceType=RESOURCETYPE_EXERCISE, $exerciseIds='', $startEndTime=array(),$isEdit=false)
    {
        $platformId =  intval($platformId);
        if( 0 == $platformId || empty($exerciseIds) || empty($startEndTime))
            return false;
        $idArray = explode(',',$exerciseIds);
        if(false === $isEdit) {  //添加
            $insertData = array();
            if (RESOURCETYPE_EXERCISE == $resourceType) {
                foreach ($idArray as $key => $id) {
                    $insertData[] = "($platformId,$resourceType,$id,0,'{$startEndTime['startTime']}','{$startEndTime['endTime']}')";
                }
            } else {
                foreach ($idArray as $key => $id) {
                    $insertData[] = "($platformId,$resourceType,0,$id,'{$startEndTime['startTime']}','{$startEndTime['endTime']}')";
                }
            }
                $result = M()->execute("INSERT INTO exercises_platform_exercises (platform_id, resource_type, exercise_id, paper_id, starttime, endtime)
 VALUES " . implode(',', $insertData));
        }
        else
        {
            if (RESOURCETYPE_EXERCISE == $resourceType)
                $result = M()->execute("UPDATE exercises_platform_exercises set starttime = '{$startEndTime['startTime']}' ,endtime = '{$startEndTime['endTime']}' WHERE platform_id = $platformId AND resource_type = $resourceType AND exercise_id in ($exerciseIds)");
            else
                $result = M()->execute("UPDATE exercises_platform_exercises set starttime = '{$startEndTime['startTime']}' ,endtime = '{$startEndTime['endTime']}' WHERE platform_id = $platformId AND resource_type = $resourceType AND paper_id = ($exerciseIds)");
        }
        echo M()->getLastSql();exit;
        return $result!==false;
    }

    private function getConditionWhere($condition=array())
    {
        $whereStr = ' 1=1 ';
        foreach($condition as $key=>$val)
        {
            if(!empty($val))
                switch($key)
                {
                    case 'platformId' :
                            $whereStr .= " AND exercises_platform.id = $val ";
                            break;
                    case 'keyword':$whereStr .= " AND exercises_platform.name like '%$val%' ";break;
                    default:break;
                }
        }
        return $whereStr;
    }

    /*
     * 获取平台的数目
     */
    public function getPlatformCount($condition=array())
    {
        $conditionWhere = $this->getConditionWhere($condition);
        $result = M()->query("SELECT COUNT(1) AS COUNT FROM exercises_platform ".
            " WHERE $conditionWhere LIMIT 1");
        return $result[0]['count'];
    }

    /*
    * 获取平台列表
    */
    public function getPlatformList($condition=array())
    {

        $conditionWhere = $this->getConditionWhere($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';

        $result = M()->query(" SELECT id,name,type,startip,endip FROM exercises_platform ".
            " WHERE $conditionWhere $limitStr");
        $i=0;
        foreach($result as $key=>$val)
        {
            $result[$key]['rownum'] = ++$i;
        }
        return $result;
    }

    public function deletePublish($id)
    {
        $result = M()->execute("DELETE FROM exercises_platform_exercises WHERE id=$id ");
        return false !== $result;
    }

    public function deletePublishExercise($ids)
    {
        $result = M()->execute("DELETE FROM exercises_platform_exercises WHERE exercise_id in ($ids) ");
        return false !== $result;
    }

    public function deletePublishPaper($ids)
    {
        $result = M()->execute("DELETE FROM exercises_platform_exercises WHERE paper_id in ($ids) ");
        return false !== $result;
    }
}