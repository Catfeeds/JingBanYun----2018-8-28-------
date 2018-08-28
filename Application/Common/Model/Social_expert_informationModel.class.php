<?php
namespace Common\Model;
use Think\Model;



class Social_expert_informationModel extends Model{

    public    $model='';
    protected $tableName = 'social_expert_information';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 获取资讯列表
     */

    public function getInformationList($pageIndex = 1,$pageSize = 20,$column=0,$keyword='')
    {
        if($column == 0) //未选择栏目
        {
            if($keyword!=''){
                $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
                $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
                $temp_arr = explode(' ',$filter['keyword']);
                foreach ($temp_arr as $item){
                    $where['title'][] = array("like", "%" . $item . "%");
                }
            }

            $where['status'] = EXPERTINFO_STATUS_ONLINE;
            $where['type'] = array('neq',7);
            $result = $this->model
                ->join('auth_admin on auth_admin.id=social_expert_information.publisher_id')
                ->page(($pageIndex) . ',' . $pageSize)
                ->order('up_time desc')
                ->where($where)
                ->field("$this->tableName.id,$this->tableName.short_content,$this->tableName.title,$this->tableName.create_at,$this->tableName.update_at,$this->tableName.browse_count,auth_admin.nickname as publisher,pc_cover,mobile_cover")
                ->select();
            foreach ($result as &$val) {
                $val['img_url'] = "http://" . WEB_URL . "/Resources/expertinformation/" . $val['short_content'];
                //$publisher_name=$val['nickname']?$val['nickname']:$val['name'];
                unset($val['short_content']);
                //$val['publisher']=$publisher_name;
            }
        }
        else
        {
            if($column!=0)
             $where['type'] = $column;
             $where['status'] = EXPERTINFO_STATUS_ONLINE;
            if($keyword!=''){
                $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
                $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
                $temp_arr = explode(' ',$filter['keyword']);
                foreach ($temp_arr as $item){
                    $where['title'][] = array("like", "%" . $item . "%");
                }
            }
            $result = $this->model
                ->join('auth_admin on auth_admin.id=social_expert_information.publisher_id')
                ->join('dict_column ON dict_column.id = social_expert_information.type')
                ->page(($pageIndex) . ',' . $pageSize)
                ->order('up_time desc')
                ->where($where)
                ->field("(CASE WHEN YEAR(FROM_UNIXTIME($this->tableName.up_time)) = YEAR(date(NOW())) THEN FROM_UNIXTIME($this->tableName.up_time,'%m/%d') else FROM_UNIXTIME($this->tableName.up_time,'%Y/%m/%d') end) c_time,$this->tableName.browse_count,$this->tableName.id,$this->tableName.file_path,$this->tableName.title,$this->tableName.create_at,auth_admin.nickname as publisher,pc_cover,mobile_cover,mobile_cover_add")
                ->select();

        }

        return $result;
    }
    /*
    * 获取单个资讯
    */


    public function getInformationDetails($id)
    {
        $Model = $this->model;
        $result = $Model
            ->where($this->tableName.".id=".$id)
            ->join('auth_admin on auth_admin.id=social_expert_information.publisher_id')
            ->field ("(CASE WHEN YEAR(FROM_UNIXTIME($this->tableName.up_time)) = YEAR(date(NOW())) THEN FROM_UNIXTIME($this->tableName.up_time,'%m/%d') else FROM_UNIXTIME($this->tableName.up_time,'%Y/%m/%d') end) c_time,".$this->tableName.'.id,title,short_content,content,'.$this->tableName.'.create_at,update_at,auth_admin.nickname as publisher,
                          type,zan_count,browse_count,status,publisher_id,file_path,mobile_cover_add,up_time')
            ->find();
        return $result;
    }
    /*
     * 获取资讯栏目
     */
    public function getInfoColumn($id){
      $result = $this->model->where('id='.$id)->find();
      return $result['type'];
    }


    /*
     * 获取相关资讯
     */
    public function getRecommendInfo($column,$id,$limit=3){
        $where['type'] = $column;
        $where['id'] = array('neq',$id);
        $where['status'] = EXPERTINFO_STATUS_ONLINE;
        $result = $this->model->where($where)
            ->order('browse_count desc')
            ->limit($limit)
            ->field("(CASE WHEN YEAR(FROM_UNIXTIME(up_time)) = YEAR(date(NOW())) THEN FROM_UNIXTIME(up_time,'%m/%d') else FROM_UNIXTIME(up_time,'%Y/%m/%d') end) c_time,$this->tableName.browse_count,$this->tableName.id,$this->tableName.file_path,$this->tableName.title,$this->tableName.create_at,mobile_cover")
            ->select();
        return $result;
    }

    /*
    * 设置推送状态
    */
    public function setPushStatus($id,$status)
    {
        $Model = $this->model;
        $data['status'] = $status;
        if(EXPERTINFORMATION_HASPUSH == $status)
            $data['push_at'] = time();
        $result = $Model
            ->where($this->tableName.".id=".$id)
            ->save($data);
        return $result;
    }

    public function incBrowseCount($id)
    {
        if(false === $this->model->where($this->tableName.".id=".$id)->setInc('browse_count'))
         return false;
        else
         return true;
    }
}
