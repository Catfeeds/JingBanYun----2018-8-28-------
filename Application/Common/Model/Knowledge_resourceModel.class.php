<?php
namespace Common\Model;

use Think\Model;

define('PUTAWAY',1);
define('ENABLED',1);
define('APPROVE',1);



define('COLUMN_RECOMMENDRESOURCE',2);
define('COLUMN_BESTRESOURCE',1);
define('COLUMN_COMPETITIONRESOURCE',3);
define('COLUMN_TRADITIONALCULTURE',6);

define('RESOURCE_NORMAL',1);
define('RESOURCE_NOBOOK',2);
define('RESOURCE_WBHT',3);

define('ROLE_NUMBER',2);//这里为减去的数字。资源和京版资源角色从0开始!
define('MIN_LEVEL',4);
define('MAX_LEVEL',1);
define('TWO_LEVEL',3);

class Knowledge_resourceModel extends Model
{

    public $model = 'knowledge_resource';
    protected $tableName = 'knowledge_resource';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     * 获得所有新版京版资源的类型
     */
    public function getResourceType()
    {
        $model = M('knowledge_type');
        $result = $model->select();
        return $result;
    }



    /*
     * 获得某个栏目信息
     */
    public function getColumnInfo($id){
        $model=M('dict_column');
        $where['id']=$id;
        $result=$model->where($where)->find();
        return $result;
    }


    /*
     *获得某个资源类型
     */
    public function getResource_type($id){
        $model=M('knowledge_type');
        $where['id']=$id;
        $result=$model->where($where)->find();
        return $result;
    }

    /*
     *获取资源类型
     */
    public function getType($condition){
        $result=$this->model->where($condition)->join('knowledge_resource_attr on knowledge_resource_attr.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join('knowledge_type on knowledge_type.id = knowledge_resource_type_contact.type_id','left')
            ->field('knowledge_type.type_name,knowledge_type.id')
            ->group('knowledge_type.id')
            ->select(); //echo M()->getLastSql();

        return $result;
    }

    /*
     * 获得某条数据
     */
    public function getResource($id,$role,$user_id){
        $where['resource_id']=$id;
        $where['role']=$role;
        $where['user_id']=$user_id;
        $model=M('knowledge_resource_collect');
        $result=$model->where($where)->find();
        return $result;
    }

    /*
     *获得新京版资源总数
     */
    public function getResourceCount($check = array())
    {
        $resource = $this->model
            ->join('knowledge_resource_type_contact ON knowledge_resource_type_contact.resource_id = knowledge_resource.id','left')
            ->join('knowledge_type on knowledge_type.id=knowledge_resource_type_contact.type_id','left')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id','left')
            ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id','left')
            ->join('knowledge_point on knowledge_resource_point.textbook=knowledge_point.textbook_id','left')
            ->field('1')
            ->group('knowledge_resource.id')
            ->where($check)
            ->select();
        return count($resource);
    }
    /*
     *获得册
     */
    public function get_textbooks($parms=array()){
        $model = M('biz_textbook');
        $resource = $model
            ->field('id,name')
            ->where($parms)
            ->select();
        return $resource;
    }
    /*
     *获得新京版资源所有内容
     */
    public function getResourceAll($check = array(),$filter=array())
    {
        $count = $this->getResourceCount($check);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();
        $resource = $this->model
            ->join('knowledge_resource_type_contact ON knowledge_resource_type_contact.resource_id = knowledge_resource.id','left')
            ->join('knowledge_type on knowledge_type.id=knowledge_resource_type_contact.type_id','left')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id','left')
            ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id', 'left')
            ->join('knowledge_point ON knowledge_resource_point.textbook = knowledge_point.textbook_id', 'left')
            ->field('knowledge_resource.id,knowledge_resource.name,knowledge_resource.source,knowledge_resource.status,knowledge_resource.putaway_status,knowledge_resource.resource_type,knowledge_resource.putaway_time,knowledge_resource.browse_count,knowledge_resource.zan_count,knowledge_resource.favorite_count')
            ->group('knowledge_resource.id')
            ->where($check)
            ->order('knowledge_resource.create_at desc,knowledge_resource.id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $data['page'] = $show;
        $data['list'] = $resource;
        return $data;
    }
    /*
     *获得导出新京版资源列表
     */
    public function getExportResourceList($check = array())
    {

        $data = $this->model
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id','left')
            ->join('biz_textbook on knowledge_resource_point.textbook=biz_textbook.id', 'left')
            ->join('(SELECT id,grade grade_name FROM dict_grade ) a  on a.id=knowledge_resource_point.grade', 'left')
            ->join('dict_course on dict_course.id=knowledge_resource_point.course', 'left')
            ->join('knowledge_point ON knowledge_resource_point.textbook = knowledge_point.textbook_id', 'left')
            ->field('knowledge_resource.id,knowledge_resource.name,dict_course.course_name course,a.grade_name grade,biz_textbook.name textbook,'.
                '(CASE WHEN knowledge_resource.resource_type = 1 THEN \'普通资源\' WHEN knowledge_resource.resource_type = 2 THEN \'nobook\' WHEN knowledge_resource.resource_type = 3 THEN \'万邦华堂资源\' END) resourcetype,'.
                '(CASE WHEN knowledge_resource.status = 0 THEN \'待审核\' WHEN knowledge_resource.status = 1 THEN \'审核通过\' WHEN knowledge_resource.status = 2 THEN \'审核拒绝\' END) status,'.
                '(CASE WHEN knowledge_resource.putaway_status = 0 THEN \'不上架\' WHEN knowledge_resource.putaway_status = 1 THEN \'上架\' END) putaway_status,'.
                'from_unixtime(knowledge_resource.putaway_time),knowledge_resource.browse_count,knowledge_resource.favorite_count')
            ->group('knowledge_resource.id')
            ->where($check)
            ->order('knowledge_resource.create_at desc,knowledge_resource.id desc')
            ->select();
        //echo $resource;exit;

        return $data;
    }


    /*
     *拼接knowledge_info字段内容
     */
    public function get_some_resource($publishing_house_id,$grade,$course,$textbook,$chapter,$festival,$knowledge,$child_knowledge,$name,$creation_year,$source,$author,$resource_type,$description){
        $link = array();
        $model_course = M('dict_course_copy_resource');
        $model_grade = M('dict_grade');
        $model_textbook=M('biz_textbook');
        $model_point=M('knowledge_point');
        $model_publishing=M('biz_textbook_publishing_contact');
        $publishing_id['id'] = $publishing_house_id;
        $grade_id['id'] = $grade;
        $course_id['id'] = $course;
        $textbook_id['id'] = $textbook;
        $chapter_id['id'] = $chapter;
        $festival_id['id'] = $festival;
        $knowledge_id['id'] = $knowledge;
        $child_knowledge_id['id'] = $child_knowledge;

        if($publishing_house_id !=''){
            $link +=$model_publishing->field('publishing_house')->where($publishing_id)->find();
        }

        if($grade != '' && $grade != -1)
            $link += $model_grade->field('grade')->where($grade_id)->find();
        $link +=$model_course->field('course_name')->where($course_id)->find();

        if($textbook && $textbook !=-1){
            $link +=$model_textbook->field('name')->where($textbook_id)->find();
        }
        if($chapter && $chapter !=-1){
            $link +=$model_point->field('knowledge_name chapter')->where($chapter_id)->find();
        }
        if($festival && $festival !=-1){
            $link +=$model_point->field('knowledge_name festival')->where($festival_id)->find();
        }
        if($knowledge && $knowledge !=-1){
            $link +=$model_point->field('knowledge_name knowledge')->where($knowledge_id)->find();
        }
        if($child_knowledge && $child_knowledge !=-1){
            $link +=$model_point->field('knowledge_name child_knowledge')->where($child_knowledge_id)->find();
        }
        $links = implode(',',$link);
        $linkspoint = $links;
        $links .= ','.$name.','.$creation_year.','.$source.','.$author.','.$resource_type.','.$description;

        if (!empty($links)) {
            $links = explode(',',$links);
            $links = array_unique($links);
            $links = array_filter($links);
            $links = implode(',',$links);
        }
        $data['links'] = $links;
        $data['linkspoint'] = $linkspoint;
        return $data;

    }
    /*
     *修改或查看资源时获得指定资源ID的资源数据
     */
    public function getResourceOne($id){
        $resource_id['id'] = $id;
        $resource = $this->model
            ->where($resource_id)
            ->find();
        return $resource;
    }

    /*
     * 获得某个栏目资源(如:精品资源)
     */
    public function getColumnResource($column_id, $rows='', $additionalWhere = []){

        $model=M('dict_column_contact');
        $where['dict_column.id']=$column_id;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $where['dict_column_contact.status']=APPROVE;
        if(!empty($additionalWhere))
            $where = array_merge($where,$additionalWhere);

        $result=$model->where($where)->join('dict_column on dict_column.id=dict_column_contact.column_id')
            ->join('knowledge_resource on knowledge_resource.id=dict_column_contact.resource_id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('dict_grade ON dict_grade.id = knowledge_resource_point.grade','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_type on knowledge_type.id=knowledge_resource_type_contact.type_id')
            ->field('knowledge_resource.real_price,knowledge_resource.description,knowledge_resource.id resource_id,knowledge_resource.promote_price,knowledge_resource.real_price,knowledge_resource.mobile_cover img,knowledge_resource.name resource_name,file_type,source,knowledge_resource.author,dict_course_copy_resource.id course_id,course_name,'
                . 'biz_textbook.id textbook_id,count(distinct biz_textbook.id) textbook_number,biz_textbook.name textbook,count(distinct dict_grade.id) grade_number,dict_grade.grade,'
                . 'count(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term_number, '
                . 'group_concat(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term ,'
                . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource ,pc_cover,mobile_cover,'
                . 'is_allowed_download,is_allowed_share,charge_status,group_concat(distinct knowledge_type.type_name) type_name')
            ->group('knowledge_resource.id')
//            ->order('knowledge_resource.create_at desc')
            ->order('dict_column_contact.sort')
            ->limit($rows)
            ->select();
        return $result;
    }


    /*
    * 获得资源列表数据,计数
     */
    public function gerResourceCount($condition=array()){
        $esAvailable = getESAvailable();
        if(ES_AVAILABLE === $esAvailable){
            list($count, $result,$ids) = $this->getResourceDataFromES($condition,'', 1, 1);
            return $count;
        }
        $join=' INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id '.
            ' LEFT  JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook '.
            ' INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course '.
            ' LEFT  JOIN dict_grade on dict_grade.id=knowledge_resource_point.grade '.
            ' LEFT  JOIN knowledge_point chapter on chapter.id=knowledge_resource_point.chapter '.
            ' LEFT  JOIN knowledge_point festival on festival.id=knowledge_resource_point.festival '.
            ' LEFT  JOIN knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge '.
            ' LEFT  JOIN knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge ';
        $result=$this->model->where($condition)->join($join)
            ->field('1')
            ->group('knowledge_resource.id')->buildSql();
        $result = M()->table($result.' a ')->field('count(1) as count')->find();
        return $result['count'];
    }

    /*
     * 获取收藏的资源
     */
    public function getCollectedResourceList($queryParameters, $pageIndex=1, $pageSize=20, $userId='', $role='')
    {
        //analysis paras
        if(!empty($queryParameters['course_id']))
            $where['knowledge_resource_point.course'] = $queryParameters['course_id'];
        if(!empty($queryParameters['grade_id']))
            $where['knowledge_resource_point.grade'] = $queryParameters['grade_id'];
        if(!empty($queryParameters['school_term']))
            $where['biz_textbook.school_term'] = $queryParameters['school_term'];
        if(!empty($queryParameters['textbook_id']))
            $where['knowledge_resource_point.textbook'] = $queryParameters['textbook_id'];
        if(!empty($queryParameters['keyword']))
            $where['keyword'] = array('like','%'.$queryParameters['keyword'].'%');
        if(!empty($queryParameters['type']))
            $where['knowledge_resource.file_type'] = $queryParameters['type'];
        if(!empty($userId) && isset($role))
        {
            $where['knowledge_resource_collect.user_id'] = $userId;
            $where['knowledge_resource_collect.role'] = $role ;
            $join[] = 'INNER JOIN knowledge_resource_collect on knowledge_resource_collect.resource_id=knowledge_resource.id';
        }
        $join[] = 'knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id';
        $join[] = 'dict_course_copy_resource ON dict_course_copy_resource.id = knowledge_resource_point.course';
        $join[] = 'LEFT JOIN dict_grade ON  dict_grade.id = knowledge_resource_point.grade';
        $join[] = 'LEFT JOIN biz_textbook ON  biz_textbook.id = knowledge_resource_point.textbook';
        $join[] = 'knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = knowledge_resource.id';

        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $where['_string'] = 'knowledge_resource.file_type!=\'condensed\' AND knowledge_resource.file_type!=\'swf\'';
        $result = $this->model->join($join)
            ->order('knowledge_resource_collect.create_at desc')
            ->where($where)
            ->page($pageIndex.','.$pageSize)
            ->group($this->tableName.'.id')
            ->field($this->tableName.'.id,'.
                $this->tableName.'.name,'.
                $this->tableName.'.file_type type,'.
                $this->tableName.'.zan_count,'.
                $this->tableName.'.favorite_count,'.
                $this->tableName.'.browse_count follow_count,'.
                $this->tableName.'.download_count,'.
                $this->tableName.'.create_at,'.
                'ifnull(biz_textbook.name,\'\') as textbook,'.
                'dict_grade.grade,'.
                'dict_grade.chinese_code,'.
                'dict_course_copy_resource.course_name as course,'.
                'knowledge_resource_file_contact.resource_path file_path,'.
                'knowledge_resource_file_contact.vid,'.
                '\'' . C('oss_path').'\' oss_path,'.
                'biz_textbook.school_term,'.
                'if(UNIX_TIMESTAMP(NOW())-604800>'.$this->tableName.'.create_at,\'no\',\'yes\') is_new')
            ->select();

        return $result;


    }


    /*
     * 获得资源列表数据
     */
    public function getResourceData($condition=array(),$order='',$user_id='',$role='',$pageSize=21,$sortIds='',$useMysql = 0,$withDescription=0){

        $sortById =  false;
        $idArray = array();
        $count = 0;
        $esAvailable = getESAvailable();
        if(ES_AVAILABLE === $esAvailable &&  0 == $useMysql ){
            list($count, $result,$ids) = $this->getResourceDataFromES($condition,$order, $_GET['p'], $pageSize);//echo M()->getLastSql();die;
            $Page = new \Think\Page($count, $pageSize);
            $data['ids'] = $ids;
        }
        else {
            unset($condition['userCourseId']);
            unset($condition['userGradeId']);
            if ($order == 'desc') {
                $count = $this->gerResourceCount($condition);
                $order_string = 'knowledge_resource.create_at desc';
                $Page = new \Think\Page($count, $pageSize);
            } elseif ($order == 'asc') {
                $count = $this->gerResourceCount($condition);
                $order_string = 'knowledge_resource.create_at asc';
                $Page = new \Think\Page($count, $pageSize);
            } else {
                if ($sortIds == '' || $useMysql == 1) {
                    $count = $this->gerResourceCount($condition);
                    $order_string = 'knowledge_resource.browse_count desc';
                    $Page = new \Think\Page($count, $pageSize);
                } else {
                    // create temp sort table
                    $idArray = explode(',', $sortIds);
                    $this->createSortTableByIdArray($idArray);
                    $sortById = true;
                    $order_string = 'resource_id_sort.sort asc';
                }
            }

            if ($condition['knowledge_resource.grade'] > 0 && $condition['knowledge_resource.grade'] < 7) {
                $condition['_string'] = "knowledge_resource_point.grade in (14," . $condition['knowledge_resource.grade'] . ")";

            } elseif ($condition['knowledge_resource.grade.id'] > 6 && $condition['knowledge_resource.grade.id'] < 10) {
                $condition['_string'] = "knowledge_resource_point.grade in (15," . $condition['knowledge_resource.grade'] . ")";

            } elseif ($condition['knowledge_resource.grade'] > 9 && $condition['knowledge_resource.grade'] < 13) {
                $condition['_string'] = "knowledge_resource_point.grade in (16," . $condition['knowledge_resource.grade'] . ")";
            }
            $join = '';
            if ($condition['knowledge_resource_attr.column_id'])
                $join .= ' INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id ';
            /*if ($condition['knowledge_resource_type_contact.type_id'])
                $join .= ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id ';*/
            if ($condition['knowledge_point chapter'])
                $join .= ' INNER JOIN knowledge_point chapter on chapter.id=knowledge_resource_point.chapter ';
            if ($condition['knowledge_point festival'])
                $join .= ' INNER JOIN knowledge_point festival on festival.id=knowledge_resource_point.festival ';
            if ($condition['knowledge_point knowledge'])
                $join .= ' INNER JOIN knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge ';
            if ($condition['knowledge_point child_knowledge'])
                $join .= ' INNER JOIN knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge ';
            $join .=
                ' INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id ' .
                ' LEFT  JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook ' .
                ' INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course ' .
                ' LEFT  JOIN dict_grade on dict_grade.id=knowledge_resource_point.grade ' .
                ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id' .
                ' INNER JOIN knowledge_type on knowledge_type.id=knowledge_resource_type_contact.type_id';


            $field = 'knowledge_resource.charge_status,knowledge_resource.real_price,knowledge_resource.promote_price,knowledge_resource.id,knowledge_resource.author,knowledge_resource.browse_count,knowledge_resource.putaway_time,knowledge_resource.create_at,knowledge_resource.name resource_name,resource_type,pc_cover,mobile_cover img,file_type,dict_course_copy_resource.id course_id,dict_course_copy_resource.course_name,count(distinct dict_grade.id) grade_number,dict_grade.grade,count(distinct biz_textbook.id) textbook_number,'
                . 'biz_textbook.name textbook,group_concat(distinct knowledge_type.type_name) type_name,'
                . 'count(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term_number,'
                . 'group_concat(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term,'
                . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource'
                . '';
            if($withDescription == 1){
                $field.=' ,knowledge_resource.description ';
            }
            $additionalWhere = '';
            if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false) || (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false) || (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false )) {
                $condition['_string'] = 'knowledge_resource.resource_type IN (0,1,2,3)';
            }
            if (!$sortById || $useMysql == 1) {
                $result = $this->model->where($condition)->join($join)
                    ->field($field)
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->group('knowledge_resource.id')->order($order_string)->select();//echo M()->getLastSql();die;
            }  else {
                $countResult = $this->model->where($condition)
                    ->join($join)
                    ->join('resource_id_sort ON resource_id_sort.id = knowledge_resource.id', 'left')
                    ->field('knowledge_resource.id')
                    ->group('knowledge_resource.id')
                    ->buildSql();
                $count = M()->table($countResult . ' a ')->field('count(1) as count')->find();
                $count = $count['count'];
                $Page = new \Think\Page($count, $pageSize);
                $result = $this->model->where($condition)
                    ->join($join)
                    ->join('resource_id_sort ON resource_id_sort.id = knowledge_resource.id', 'left')
                    ->field('knowledge_resource.id,resource_id_sort.sort')
                    ->group('knowledge_resource.id')
                    ->order('resource_id_sort.sort asc')
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->buildSql();


                $result = M()->table($result . ' a ')->join('knowledge_resource ON knowledge_resource.id = a.id', 'left')->join($join)->field($field)
                    ->order('a.sort asc')->group('knowledge_resource.id')->select();//echo M()->getLastSql();die;
            }
        }
        $show=$Page->show('callback');
        $data['count']=$count;
        $data['page']=$show;
        $data['data']=$result;
        return $data;
    }
    public function getAdditionalResource($ids = '')
    {
        $where = 'knowledge_resource.putaway_status = '.PUTAWAY .' AND knowledge_resource.status = '.APPROVE . " AND knowledge_resource.id in ($ids)";
        $sql = "SELECT knowledge_resource.id,GROUP_CONCAT(distinct knowledge_resource_attr.column_id) column_ids FROM knowledge_resource ".
            " INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id ".
            " INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id ".
            " INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id ".
            " INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id ".
            " INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course ".
            " WHERE $where ".
            " GROUP BY knowledge_resource.id";
        $result = M()->query($sql);
        return $result;
    }

    public function getRemoveResource($ids = '')
    {
        /*$where = 'knowledge_resource.putaway_status = '.PUTAWAY .' AND knowledge_resource.status = '.APPROVE;
        $displayResourceTable = " SELECT knowledge_resource.id FROM knowledge_resource ".
                                " INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id ".
                                " INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id ".
                                " INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id ".
                                " INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id ".
                                " INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course ".
                                " WHERE $where".
                                " GROUP BY knowledge_resource.id";*/
        $result = array_map(function($value){return ['id'=>$value,'column_ids'=>'1,2,3,6'];},explode(',',$ids));
        return $result;
    }
    /*
     * 微信国学列表数据
     */
    public function getWcahtResourceData($condition=array(),$order='desc',$user_id='',$role='',$pageSize=100){
        $count=$this->gerResourceCount($condition);
        $Page = new \Think\Page($count, $pageSize);
        $show=$Page->show();
        if($order=='desc'){
            $order_string='knowledge_resource.create_at desc';
        }elseif($order=='asc'){
            $order_string='knowledge_resource.create_at asc';
        }else{
            $order_string='knowledge_resource.browse_count desc';
        }
        if($condition['knowledge_resource.grade']>0 && $condition['knowledge_resource.grade']<7){
            $condition['_string']="knowledge_resource_point.grade in (14,".$condition['knowledge_resource.grade'].")";

        }elseif($condition['knowledge_resource.grade.id']>6 && $condition['knowledge_resource.grade.id']<10){
            $condition['_string']="knowledge_resource_point.grade in (15,".$condition['knowledge_resource.grade'].")";

        }elseif($condition['knowledge_resource.grade']>9 && $condition['knowledge_resource.grade']<13){
            $condition['_string']="knowledge_resource_point.grade in (16,".$condition['knowledge_resource.grade'].")";
        }

        $join="knowledge_resource_collect collect_ on knowledge_resource.id=collect_.resource_id";


        $result=$this->model->where($condition)->join('knowledge_resource_attr on knowledge_resource_attr.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join($join,'left')
            ->field('knowledge_resource.id,knowledge_resource.name resource_name,resource_type,pc_cover,mobile_cover,file_type,dict_course_copy_resource.id course_id,dict_course_copy_resource.course_name,count(dict_grade.id) grade_number,dict_grade.grade,count(biz_textbook.id) textbook_number,'
                . 'biz_textbook.name textbook,'
                . 'count(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term_number,'
                . 'group_concat(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term,'
                . 'chapter.knowledge_name chapter,'
                . 'festival.knowledge_name festival,knowledge.knowledge_name knowledge,child_knowledge.knowledge_name child_knowledge,'
                . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource,'
                . '(case when collect_.resource_id is null then \'no\' else \'yes\' end ) iscollect ,'
                . 'knowledge_resource_attr.column_id,is_allowed_download,is_allowed_share,knowledge_resource_file_contact.resource_path')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->group('knowledge_resource.id')->order($order_string)->select();

        foreach ( $result as $k=>&$v ) {
            if (strpos($v['pc_cover'],'http')===false) {
                $v['pc_cover'] = C('oss_path').$v['mobile_cover'];
            }
        }

        $data['count']=$count;
        $data['page']=$show;
        $data['data']=$result;
        return $data;
    }

    /*
     * 获得栏目属性(如:精品资源)
     */
    public function getColumnAttr($condition){
        $result=$this->model->where($condition)->join('knowledge_resource_attr on knowledge_resource_attr.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join('dict_column on dict_column.id=knowledge_resource_attr.column_id','left')
            ->field('dict_column.id,dict_column.column_name')
            ->group('dict_column.id')
            ->select(); //echo M()->getLastSql();

        return $result;
    }


    /*
     * 创建知识(京版)资源临时表
     */
    public function createResourceTempTable(){
        $drop_temp_table_sql='drop table if exists knowledge_resourec_temp';
        $this->model->execute($drop_temp_table_sql);
        $sql='create temporary table knowledge_resourec_temp(resources_id int UNSIGNED not null,course_id int UNSIGNED not null,course varchar(32) not null,'
            . 'grade_id int UNSIGNED not null,grade varchar(32) not null,school_term int UNSIGNED not null,file_type varchar(32) not null,type_id int not null)engine=memory charset=utf8';
        $this->model->execute($sql);
    }


    /*
     * 插入临时表数据
     */
    public function insertTempData($condition=array()){
        $build_sql=$this->model->where($condition)->join('knowledge_resource_attr on knowledge_resource_attr.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->field('knowledge_resource.id,dict_course_copy_resource.id,dict_course_copy_resource.course_name,dict_grade.id,dict_grade.grade,biz_textbook.school_term,file_type,knowledge_resource_type_contact.type_id')
            ->group('knowledge_resource_point.id')->buildsql();
        $last_sql='insert into knowledge_resourec_temp '.$build_sql;
        $this->model->execute($last_sql);
    }


    /*
     * 获得临时表中的数据
     */
    public function getCondition($group=''){
        $group_str = '';
        $field = '*';
        switch ($group) {
            case 'course':
                $group_str='course_id';
                $field = 'distinct course_id,course';
                break;
            case 'grade':
                $group_str='grade_id';
                $field = 'distinct grade_id,grade';
                break;
            case 'school_term':
                $group_str='school_term';
                $field = 'distinct school_term';
                break;
            case 'file_type':
                $group_str='file_type';
                $field = 'distinct file_type';
                break;
            case 'type_id':
                $group_str='type_id';
                $field = 'distinct type_id';
                break;
        }
        if($group != 'file_type')
            $where = " WHERE $group_str > 0 ";
        //$result=M()->query("select * from knowledge_resourec_temp group by " . $group_str);
        $result=M()->query("select $field from knowledge_resourec_temp $where ORDER BY $group_str ASC");
        return $result;
    }


    /*
     * 获得某个资源的简单信息
     */
    public function getOneResourceSimpleInfo($id){
        $where['id']=$id;
        $result=$this->model->where($where)->field('id,name')->find();
        return $result;
    }


    /*
     * 获得某个资源的点赞信息
     */
    public function getOneResourceZanData($resource_id,$role,$user_id){
        $model=M('knowledge_resource_zan');
        $where['resource_id']=$resource_id;
        $where['role']=$role;
        $where['user_id']=$user_id;
        $result=$model->where($where)->find();
        return $result;
    }


    /*
     * 添加点赞
     */
    public function addResourceZan($data){
        $model=M('knowledge_resource_zan');
        if($model->add($data)){
            return true;
        }else{
            return false;
        }
    }


    /*
     * 删除点赞
     */
    public function deleteResourceZan($resource_id,$role,$user_id){
        $model=M('knowledge_resource_zan');
        $where['resource_id']=$resource_id;
        $where['role']=$role;
        $where['user_id']=$user_id;
        if($model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    }


    /*
     * 获得知识点中的章
     */
    public function getKnowledgeChapter($grade_id=0,$course_id=0,$textbook_id=0){
        $model=M('knowledge_point');
        if($grade_id!=0){
            $where['grade_id']=$grade_id;
        }
        if($course_id!=0){
            $where['course_id']=$course_id;
        }
        if($textbook_id!=0){
            $where['textbook_id']=$textbook_id;
        }
        $result=$model->where($where)->order('sort')->select();
        return $result;
    }

    /*
     * 获得知识点中的章仅限前台使用
     */
    public function getKnowledgeChapter2($grade_id=0,$course_id=0,$textbook_id=0,$level=1){
        $model=M('knowledge_point');
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        if($grade_id!=0){
            $where['grade_id']=$grade_id;
        }
        if($course_id!=0){
            $where['course_id']=$course_id;
        }
        if($textbook_id!=0){
            $where['textbook_id']=$textbook_id;
        }
        $where['level'] = 1;
        $result=$model
            ->field('knowledge_point.id,knowledge_point.knowledge_name,knowledge_point.knowledge_count')
            ->join('knowledge_resource_point on knowledge_resource_point.chapter = knowledge_point.id')
            ->join('knowledge_resource on knowledge_resource.id = knowledge_resource_point.knowledge_resource_id')
            ->where($where)->group('knowledge_point.id')->order('sort')->select();
        return $result;
    }

    /*
     *获取电子课本中的年级、学科知识点
     */
    public function getTextbookInfo2($id){
        $textModel = M('biz_textbook');
        $where['id']=$id;
        $result=$textModel->where($where)->find();
        return $result;
    }

    /*
     * 获得某个子级知识点
     */
    public function getKnowledgePointByParentId($id){
        $model=M('knowledge_point');
        $where['parent_id']=$id;
        $result=$model->where($where)->order('sort')->select();
        return $result;
    }

    /*
     * 获得某个子级知识点仅限前台使用
     */
    public function getKnowledgePointByParentId2($id,$level){
        $model=M('knowledge_point');
        $where['knowledge_point.parent_id']=$id;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        if($level == 2){
            //$join = 'knowledge_point festival ON festival.id = knowledge_resource_point.festival';
            $join = 'knowledge_resource_point ON knowledge_point.id = knowledge_resource_point.festival';
        }elseif($level == 3){
            //$join = 'knowledge_point knowledge ON knowledge.id = knowledge_resource_point.knowledge';
            $join = 'knowledge_resource_point ON knowledge_point.id = knowledge_resource_point.knowledge';
        }elseif($level == 4){
            //$join = 'knowledge_point child_knowledge ON knowledge_point.id = knowledge_resource_point.child_knowledge';
            $join = 'knowledge_resource_point ON knowledge_point.id = knowledge_resource_point.child_knowledge';
        }
        $result=$model
            ->field('knowledge_point.id,knowledge_point.knowledge_name,knowledge_point.knowledge_count')
            ->join($join)
            ->join('knowledge_resource on knowledge_resource.id = knowledge_resource_point.knowledge_resource_id')
            ->where($where)->order('sort')->group('knowledge_point.id')->select();//echo M()->getLastSql();
        return $result;
    }

    /*
     * 获得某个知识点信息
     */
    public function getKnowledgePointInfo($id,$level=''){
        $model=M('knowledge_point');
        $where['id']=$id;
        if($level!=''){
            $where['level']=$level;
        }
        $result=$model->where($where)->find();
        return $result;
    }

    /*
     * 根据分册查询章（根据资源反查）
     */
    public function getChapterByTextbook($textbookId)
    {
        if($textbookId == 0 )
            return array();
        $sql = "SELECT knowledge_point.id id,knowledge_point.knowledge_name name FROM knowledge_point ".
            " JOIN knowledge_resource_point ON knowledge_resource_point.chapter = knowledge_point.id ".
            " JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id AND status = ".APPROVE ." AND putaway_status = ".PUTAWAY .
            " WHERE knowledge_point.textbook_id = $textbookId  GROUP BY knowledge_point.id ";
        $result = M()->query($sql);
        return $result;
    }
    /*
     * 获得某个子级知识点简要信息（根据资源反查）
     */
    public function getBriefKnowledgePointByParentId($id){
        if($id == 0)
            return array();
        //get level of this id
        $level = M()->query("SELECT level FROM knowledge_point where id = $id LIMIT 1");
        $level = $level[0]['level'];
        if($level == 4)
            return array();
        $joinField = array(1=>'chapter',2=>'festival',3=>'knowledge',4=>'child_knowledge');
        $sql = "SELECT knowledge_point.id,knowledge_point.knowledge_name name FROM knowledge_point ".
            " JOIN knowledge_resource_point ON knowledge_resource_point.{$joinField[$level+1]} = knowledge_point.id ".
            " JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id AND status = ".APPROVE ." AND putaway_status = ".PUTAWAY .
            " WHERE knowledge_point.parent_id = $id  GROUP BY knowledge_point.id ";
        $result = M()->query($sql);
        return $result;
    }
    /*
     * 获得某个资源的收藏信息
     */
    public function getOneResourceCollectData($resource_id,$role,$user_id){
        $model=M('knowledge_resource_collect');
        $where['resource_id']=$resource_id;
        $where['role']=$role;
        $where['user_id']=$user_id;
        $result=$model->where($where)->find();
        return $result;
    }


    /*
     * 添加收藏
     */
    public function addResourceCollect($data){
        $model=M('knowledge_resource_collect');
        $where = $data;
        unset($where['create_at']);
        $findResult = $model->where($where)->find();
        if(!empty($findResult))
            return true;
        $model->startTrans();
        if($model->add($data)){
            if(false !== M('knowledge_resource')->where('id='.$data['resource_id'])->setInc('favorite_count')) {
                $model->commit();
                return true;
            }
            else
            {
                $model->rollback();
                return false;
            }
        }else{
            $model->rollback();
            return false;
        }
    }


    /*
     * 删除收藏
     */
    public function deleteResourceCollect($resource_id,$role,$user_id){
        $model=M('knowledge_resource_collect');
        $model->startTrans();
        $where['resource_id']=$resource_id;
        $where['role']=$role;
        $where['user_id']=$user_id;
        $del_id = $model->where($where)->delete();

        if($del_id !== false){
            $resourceInfo = M('knowledge_resource')->where('id='.$where['resource_id'])->find();
            if ($resourceInfo['favorite_count'] > 0) {
                $is_set = M('knowledge_resource')->where('id='.$where['resource_id'])->setDec('favorite_count');
            } else {
                $is_set = true;
            }

            if(false !== $is_set)
            {
                $model->commit();
                return true;
            }
            else
            {
                $model->rollback();
                return false;
            }
        }else{
            $model->rollback();
            return false;
        }
    }


    /*
     * 更改某个资源的某个字段信息
     */
    public function updateResourceCollectNum($id,$flag=true){
        $where['id']=$id;
        if($flag){
            $this->model->where($where)->setInc('favorite_count', 1);
        }else{
            $this->model->where($where)->setDec('favorite_count', 1);
        }
    }

    /*
     * 更改某个资源的某个字段信息
     */
    public function updateResourceFollowNum($id,$flag=true){
        $where['id']=$id;
        if($flag){
            $this->model->where($where)->setInc('browse_count', 1);
        }else{
            $this->model->where($where)->setDec('browse_count', 1);
        }
    }

    /*
     * 获得某个资源的详情信息
     */
    public function getResourceDetailInfo($resource_id,$role,$user_id){
        $where = array();
        $where['knowledge_resource.id'] = $resource_id;
        $join="knowledge_resource_collect collect_ on knowledge_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.role='.($role-ROLE_NUMBER);
        $result=$this->model->where($where)->join('knowledge_resource_attr on knowledge_resource_attr.resource_id=knowledge_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join('knowledge_resource_collect collect on collect.resource_id=knowledge_resource_point.id ','left')
            ->join($join,'left')

            ->field('knowledge_resource.id,knowledge_resource.flag,biz_textbook.name textbook,knowledge_resource.author,knowledge_resource.name resource_name,knowledge_resource.description,resource_type,pc_cover,mobile_cover img,file_type,dict_course_copy_resource.course_name,dict_grade.grade,'.
                'count(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term_number, '
                . '(case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term,'.
                'GROUP_CONCAT(chapter.knowledge_name) chapter,'
                . 'GROUP_CONCAT(festival.knowledge_name) festival,knowledge_info,GROUP_CONCAT(knowledge.knowledge_name) firstknowledge,GROUP_CONCAT( distinct child_knowledge.knowledge_name ) childknowledge,'
                . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource,'.
                '(case when collect_.resource_id is null then \'no\' else \'yes\' end ) iscollect ,'.
                'knowledge_resource_attr.column_id,is_allowed_download,is_allowed_share,charge_status,charge_type,charge_time,knowledge_resource.resource_price,knowledge_resource.real_price')
            ->find();
        return $result;
    }

    /*
    * 获得某个资源的详情信息(无多分册 多年级)
    */
    public function getResourceDetailInfoWithoutMulti($resource_id,$role,$user_id){
        $where = array();
        $where['knowledge_resource.id'] = $resource_id;
        $join="LEFT JOIN knowledge_resource_collect collect_ on knowledge_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.role='.($role-ROLE_NUMBER);
        $result=$this->model->where($where)
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join($join,'left')
            ->field('knowledge_resource.putaway_time,knowledge_resource.status,knowledge_resource.putaway_status,knowledge_resource.is_allowed_app_download,knowledge_resource.id,knowledge_resource.file_type,knowledge_resource.flag,biz_textbook.name textbook,knowledge_resource.author,knowledge_resource.name resource_name,knowledge_resource.description,resource_type,pc_cover,mobile_cover img,dict_course_copy_resource.course_name,dict_grade.grade,'.
                '(case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term,'.
                'ifnull(chapter.html,chapter.knowledge_name) chapter,'
                . 'ifnull(festival.html,festival.knowledge_name) festival,knowledge_info,ifnull(knowledge.html,knowledge.knowledge_name) firstknowledge,ifnull(child_knowledge.html,child_knowledge.knowledge_name) childknowledge,'
                . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource,'.
                '(case when collect_.resource_id is null then \'no\' else \'yes\' end ) iscollect ,'.
                'is_allowed_download,is_allowed_share,charge_status,charge_type,charge_time,knowledge_resource.real_price,knowledge_resource.resource_price')
            ->select();
        return $result;
    }

    public function ResourceDetailInfoWithoutMulti($resource_id,$role,$user_id){
        $where = array();
        $where['knowledge_resource.id'] = $resource_id;
        $join="knowledge_resource_collect collect_ on knowledge_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.role='.($role-ROLE_NUMBER);
        $result=$this->model->where($where)
            ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
            ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
            ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
            ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
            ->join('knowledge_point chapter on chapter.id=knowledge_resource_point.chapter','left')
            ->join('knowledge_point festival on festival.id=knowledge_resource_point.festival','left')
            ->join('knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge','left')
            ->join('knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge','left')
            ->join('knowledge_resource_collect collect on collect.resource_id=knowledge_resource_point.id ','left')
            ->join($join,'left')

            ->field('knowledge_resource.id,knowledge_resource.name resource_name,pc_cover,dict_course_copy_resource.course_name,knowledge_resource.real_price')
            ->find();
        $data['resource_id'] = $resource_id;
        $count = M('knowledge_resource_file_contact')->where($data)->count();
        $result['real_price'] = $result['real_price'].'元/套';
        $result['num'] = '共'.$count.'集';

        if ( strpos($result['pc_cover'],'http')===false ) {
            $result['pc_cover'] =  C('oss_path').$result['pc_cover'];
        }

        return $result;
    }



    /*
     * 获得某个资源的相关文件信息
     */
    public function getResourceContactFiles($resource_id,&$count,$pageIndex=0,$pageSize=50){
        $where = array();
        $where['resource_id'] = $resource_id;
        $where['putaway_status'] = PUTAWAY;
        //calc count;
        $count = M('knowledge_resource_file_contact')->where($where)->field('count(1) as count')->find();
        $count = $count['count'];
        if(0 == $pageIndex)
            $result = M('knowledge_resource_file_contact')->where($where)
                ->field('type,id,file_name,resource_path,flag,vid,vid_fullpath,is_transition,putaway_status,charge_status,trial_status,trial_time,vid_fullsize,vip_status')
                ->select();
        else
            $result = M('knowledge_resource_file_contact')->where($where)
                ->page($pageIndex.','.$pageSize)
                ->field('type,id,file_name,resource_path,flag,vid,vid_fullpath,is_transition,putaway_status,charge_status,trial_status,trial_time,vid_fullsize,vip_status')
                ->select();
        return $result;
    }

    public function getResourceInfo($resource_id)
    {
        $where['id'] = $resource_id;
        return $this->model->where($where)->find();
    }


    /*
     * 获得某个关联表的某条数据
     */
    public function getResourceKnowledgeInfo($id){
        $where['knowledge_resource.id'] = $id;
        return $this->model->where($where)->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')->select();
    }

    /*
     * 获得某个资源详情的相关推荐
     */
    public function getResourceContactRecommend($resource_id,$requiredRecordCount = 6){
        $where = array();
        $result = array();
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $resourceExclude = array($resource_id);
        $resourceInfo = $this->getResourceKnowledgeInfo($resource_id);
        $unionStep = 0;
        $order = 'browse_count desc';
        while (($requiredRecordCount != 0) && ($unionStep != 7)) {
            for($knowledgeIndex=0;$knowledgeIndex<sizeof($resourceInfo);$knowledgeIndex++) {
                $continue = 0;
                switch ($unionStep) {
                    case 0: //二级知识点
                        if($resourceInfo[$knowledgeIndex]['child_knowledge'] > 0)
                            $where['knowledge_resource_point.child_knowledge'] = $resourceInfo[$knowledgeIndex]['child_knowledge'];
                        else{
                            $continue = 1;
                            continue;
                        }

                        break;
                    case 1: //一级知识点
                        if($resourceInfo[$knowledgeIndex]['child_knowledge'] > 0)
                            $where['knowledge_resource_point.child_knowledge'] = array('neq', $resourceInfo[$knowledgeIndex]['child_knowledge']);
                        if($resourceInfo[$knowledgeIndex]['knowledge'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.knowledge'] = $resourceInfo[$knowledgeIndex]['knowledge'];

                        break;
                    case 2: //节
                        if($resourceInfo[$knowledgeIndex]['knowledge'] > 0)
                            $where['knowledge_resource_point.knowledge'] = array('neq', $resourceInfo[$knowledgeIndex]['knowledge']);
                        if($resourceInfo[$knowledgeIndex]['festival'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.festival'] = $resourceInfo[$knowledgeIndex]['festival'];

                        break;
                    case 3: //章
                        if($resourceInfo[$knowledgeIndex]['festival'] > 0)
                            $where['knowledge_resource_point.festival'] = array('neq', $resourceInfo[$knowledgeIndex]['festival']);
                        if($resourceInfo[$knowledgeIndex]['chapter'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.chapter'] = $resourceInfo[$knowledgeIndex]['chapter'];

                        break;
                    case 4: //分册
                        if($resourceInfo[$knowledgeIndex]['chapter'] > 0)
                            $where['knowledge_resource_point.chapter'] = array('neq', $resourceInfo[$knowledgeIndex]['chapter']);
                        if($resourceInfo[$knowledgeIndex]['textbook'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.textbook'] = $resourceInfo[$knowledgeIndex]['textbook'];

                        break;
                    case 5: //学科
                        if($resourceInfo[$knowledgeIndex]['textbook'] > 0)
                            $where['knowledge_resource_point.textbook'] = array('neq', $resourceInfo[$knowledgeIndex]['textbook']);
                        if($resourceInfo[$knowledgeIndex]['course'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.course'] = $resourceInfo[$knowledgeIndex]['course'];

                        break;
                    case 6: //年级
                        if($resourceInfo[$knowledgeIndex]['course'] > 0)
                            $where['knowledge_resource_point.course'] = array('neq', $resourceInfo[$knowledgeIndex]['course']);
                        if($resourceInfo[$knowledgeIndex]['grade'] < 1)
                        {
                            $continue = 1;
                            continue;
                        }
                        $where['knowledge_resource_point.grade'] = $resourceInfo[$knowledgeIndex]['grade'];

                        break;
                    default:
                        break;
                }
                if($continue == 1)
                    continue;
                $where['knowledge_resource.id'] = array('not in',$resourceExclude);
                $thisResult = $this->model
                    ->join('knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id')
                    ->join('biz_textbook on biz_textbook.id=knowledge_resource_point.textbook','left')
                    ->join('dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course')
                    ->join('dict_grade on dict_grade.id=knowledge_resource_point.grade','left')
                    ->join('knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = knowledge_resource.id')
                    ->where($where)
                    ->order($order)
                    ->field('knowledge_resource.id,knowledge_resource.file_type,pc_cover,mobile_cover,biz_textbook.name textbook,knowledge_resource.resource_type,knowledge_resource.source,knowledge_resource.name,knowledge_resource.author,dict_course_copy_resource.course_name course,dict_grade.grade,' .
                        'group_concat(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term,' .
                        'count(distinct case when biz_textbook.school_term = 1 then \'上册\' when biz_textbook.school_term = 2 then \'下册\' when biz_textbook.school_term = 3 then \'全一册\' end) term_number,' .
                        '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource,' .
                        'knowledge_resource_file_contact.resource_path')
                    ->limit($requiredRecordCount)
                    ->group('knowledge_resource.id')
                    ->select();

                $resIds = array_column($thisResult,'id');
                $resourceExclude = array_merge($resourceExclude,$resIds);
                if (!empty($thisResult))
                    $result = array_merge($result, $thisResult);
                $requiredRecordCount -= count($thisResult);
                if( 0 == $requiredRecordCount)
                    break;
            }
            $unionStep++;
        }
        return $result;
    }

    /*
     * 根据层级获得数据(6级 1学科,2分册,3章,4节,5知识点,6子级知识点)
     */
    public function getKnowlegePointData($level,$keyword){
        $model=M('knowledge_point');
        $rows=10;
        $result=array();
        switch($level){
            case 1:
                $model=M('dict_course_copy_resource');
                $where['course_name']=array('like',"%".$keyword."%");
                $result=$model->where($where)->field('id,course_name name')->select();
                break;
            case 2;
                $model=M('biz_textbook');
                $where['name']=array('like',"%".$keyword."%");
                $result=$model->where($where)->field('id,name')->select();
                break;
            default:
                $knowleget_level=($level-2);
                $where['level']=$knowleget_level;
                $where['knowledge_name']=array('like',"%".$keyword."%");
                $result=$model->where($where)->field('id,knowledge_name name,sort')->order('sort')->select();
                break;
        }
        return $result;
    }


    /*
     * 根据某个id获得该父级的树形结构数据
     */
    public function getParentTreeData($id,$data=array()){
        static $index=0;
        $model=M('knowledge_point');

        $index++;
        if($index==1){
            $where['id']=$id;
            $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name,sort')->order('sort')->select();
            if($result[0]['level']!=MAX_LEVEL){
                return $this->getParentTreeData($result[0]['parent_id']);
            }else{
                $knowledge_id = $result[0]['textbook_id'];
                $row = $this->getKnowledgeChapter(0,0,$knowledge_id);
                return $row;
            }
        }else{
            if($index==2){
                $where['parent_id']=$id;
            }else{
                $where['id']=$id;
            }
            $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name,sort')->order('sort')->select();
            if(!empty($data)){
                $result[0]['child']=$data;
            }
            $all_parent_result=array();
            if(!empty($result)){
                if($result[0]['level']!=MAX_LEVEL){
                    $parent_one_result=$model->where('id='.$result[0]['parent_id'])->field('id,level,textbook_id,parent_id,knowledge_name,sort')->select();
                    if(!empty($parent_one_result)){
                        $parent_one_result[0]['child']=$result;

                        if($parent_one_result[0]['level']==MAX_LEVEL){
                            $parent_result=$model->where('textbook_id='.$parent_one_result[0]['textbook_id'].' and parent_id='.$parent_one_result[0]['parent_id'].' and id!='.$parent_one_result[0]['id'])
                                ->field('id,textbook_id,parent_id,knowledge_name,sort')->order('sort')->select();
                        }else{
                            $parent_result=$model->where('parent_id='.$parent_one_result[0]['parent_id'].' and id!='.$parent_one_result[0]['id'])
                                ->field('id,textbook_id,parent_id,knowledge_name,sort')->order('sort')->select();
                        }
                        $all_parent_result=array_merge($parent_result,$parent_one_result);
                        $all_parent_result=my_sort($all_parent_result,'sort');

                        if($parent_one_result[0]['level']==MAX_LEVEL){
                            return $all_parent_result;
                        }else{
                            return $this->getParentTreeData($parent_one_result[0]['parent_id'],$all_parent_result);
                        }
                    }
                }else{
                    if(!empty($data)){
                        $result[0]['child']=$data;
                    }
                    $parent_result=$model->where('textbook_id='.$result[0]['textbook_id'].' and parent_id='.$result[0]['parent_id'].' and id!='.$result[0]['id'])
                        ->field('id,textbook_id,parent_id,knowledge_name,sort')->order('sort')->select();
                    $all_parent_result=array_merge($parent_result,$result);
                    $all_parent_result=my_sort($all_parent_result,'sort');
                    return $all_parent_result;
                }
            }else{
                return array();
            }
        }
    }


    /*
     * 根据某个id获得该父级的树形结构数据
     
    public function getParentTreeData($id){
        $model=M('knowledge_point');   
        $where['id']=$id;
        $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name,sort')->find(); 
        if(empty($result)){
            return array();
        }
        switch($result['level']){ 
            case 1:
                $chapte_result=$model->where('textbook_id='.$result['textbook_id'].' and parent_id='.$result['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->select(); 
                $arr=$chapte_result;
                break;
            case 2:
                $festival_result=$model->where('parent_id='.$result['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                if(!empty($festival_result)){
                    $parent_chapter=$model->where('id='.$festival_result[0]['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find();   
                    if(!empty($parent_chapter)){
                        $parent_chapter['child']=$festival_result;
                        $chapte_result=$model->where('textbook_id='.$parent_chapter['textbook_id'].' and parent_id='.$parent_chapter['parent_id'].' and id!='.$parent_chapter['id'])
                                            ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                        $chapte_result=array_merge($chapte_result,array($parent_chapter));
                    } 
                }
                $arr=$chapte_result;
                break;
            case 3:
                $knowledge_result=$model->where('parent_id='.$result['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->select();
                if(!empty($knowledge_result)){
                    $parent_festival=$model->where('id='.$knowledge_result[0]['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find();
                    if(!empty($parent_festival)){ 
                        $parent_festival['child']=$knowledge_result; 
                        $festival_result=$model->where('parent_id='.$parent_festival['parent_id'].' and id!='.$parent_festival['id'])
                                            ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                        $festival_result=array_merge($festival_result,array($parent_festival));
                        
                        $parent_chapter=$model->where('id='.$parent_festival['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find(); 
                        if(!empty($parent_chapter)){
                            $parent_chapter['child']=$festival_result;
                            $chapte_result=$model->where('textbook_id='.$parent_chapter['textbook_id'].' and parent_id='.$parent_chapter['parent_id'].' and id!='.$parent_chapter['id'])
                                            ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                            $chapte_result=array_merge($chapte_result,array($parent_chapter));
                        }
                    }
                }
                 $arr=$chapte_result;
                break;
            case 4:
                $child_knowledge_result=$model->where('parent_id='.$result['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->select();
                if(!empty($child_knowledge_result)){
                    $parent_knowledge=$model->where('id='.$child_knowledge_result[0]['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find();
                    if(!empty($parent_knowledge)){
                        $parent_knowledge['child']=$child_knowledge_result;
                        $knowledge_result=$model->where('parent_id='.$parent_knowledge['parent_id'].' and id!='.$parent_knowledge['id'])
                                            ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                        $knowledge_result=array_merge($knowledge_result,array($parent_knowledge));
                        
                        $parent_festival=$model->where('id='.$parent_knowledge['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find();
                        if(!empty($parent_festival)){
                            $parent_festival['child']=$knowledge_result;
                            $festival_result=$model->where('parent_id='.$parent_festival['parent_id'].' and id!='.$parent_festival['id'])
                                            ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                            $festival_result=array_merge($festival_result,array($parent_festival));
                            
                            $parent_chapter=$model->where('id='.$parent_festival['parent_id'])->field('id,textbook_id,parent_id,knowledge_name,sort')->find(); 
                            if(!empty($parent_chapter)){
                                $parent_chapter['child']=$festival_result;
                                $chapte_result=$model->where('textbook_id='.$parent_chapter['textbook_id'].' and parent_id='.$parent_chapter['parent_id'].' and id!='.$parent_chapter['id'])
                                                ->field('id,textbook_id,parent_id,knowledge_name,sort')->select();  
                                $chapte_result=array_merge($chapte_result,array($parent_chapter));
                            }
                        }
                    }
                }
                $arr=$chapte_result;
                break; 
        }  
        return $arr;
    }*/


    /*
     * 获得一条知识点很少的信息
     */
    public function getOneSimpleKnowledgePoint($id){
        $model=M('knowledge_point');
        $where['id']=$id;
        $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name,knowledge_count')->find();
        return $result;
    }


    /*
     * 获得需要删除知识点
     */
    public function getNeedDeleteKnowledgePoint($id){
        static $index=0;
        $model=M('knowledge_point');

        $index++;
        if($index==1){
            $where['id']=$id;
            $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name')->select();
            if($result[0]['level']!=MIN_LEVEL){
                return array_merge($result,$this->getNeedDeleteKnowledgePoint($id));
            }else{
                return $result;
            }
        }else{
            $where['parent_id']=$id;
            $result=$model->where($where)->field('id,textbook_id,level,parent_id,knowledge_name')->select();
            if(!empty($result)){
                if($result[0]['level']!=MIN_LEVEL){
                    foreach($result as $key=>$val){
                        return array_merge($result,$this->getNeedDeleteKnowledgePoint($val['id']));
                    }
                }else{
                    return $result;
                }
            }else{
                return array();
            }
        }
    }


    /*
     * 删除知识点
     */
    public function deleteKnowledgePoint($id){
        $model=M('knowledge_point');
        $data=$this->getNeedDeleteKnowledgePoint($id);
        $result=array_column($data,'id');
        $where['id']=array('in',$result);
        if($model->where($where)->delete()===false){
            return false;
        }else{
            return true;
        }
    }


    /*
     *删除多知识点中的数据
     */
    public function deleteKnowledgeResourcePoint($where){
        $model=M('knowledge_resource_point');
        $model->startTrans();
        if($model->where($where)->delete()===false){
            $model->rollback();
            return false;
        }else{
            $model->commit();
            return true;
        }
    }


    /*
     *查询多知识点表数据
     */
    public function selectKnowledgeResourcePoint($where){
        $model=M('knowledge_resource_point');
        $result = $model->field('id')
            ->where($where)
            ->select();
        return $result;
    }

    /*
     * 添加知识点
     */
    public function addKnowledgePointData($data){
        $model=M('knowledge_point');
        if($insert_id=$model->add($data)){
            return $insert_id;
        }else{
            return false;
        }
    }


    /*
     * 修改某个知识点信息
     */
    public function updateKnowledgePointData($id,$data){
        $model=M('knowledge_point');
        $where['id']=$id;
        if($model->where($where)->save($data)===false){
            return false;
        }else{
            return true;
        }
    }


    /*
     *关联多个知识点
     */
    public function point($id,$str_publishing,$str_course,$str_grade,$str_textbook,$str_chapter,$str_festival,$str_knowledge,$str_child_knowledge,$candidateId,$str_info,$resource_name,$creation_year,$source,$author,$resource_type,$description){
        $model = M('knowledge_resource_point');
        $data = array();
        $data['knowledge_resource_id'] = $id;
        $currentIds = $model->where($data)->field('id')->select();
        $currentIds = array_column($currentIds,'id');
        $excludeIds = array();
        for($i=0;$i<sizeof($currentIds);$i++)
        {
            if(!in_array($currentIds[$i],$candidateId))
                $excludeIds[] = $currentIds[$i];
        }

        $where['id'] = array('in',implode(',',$excludeIds));
        $model->where($where)->delete();

        $addResult = true;
        //print_r($candidateId);die();
        for($i=0;$i<sizeof($candidateId);$i++)
        {
            $data['publishing_house_id'] = $str_publishing[$i];
            $data['course'] = $str_course[$i];
            $data['grade'] = $str_grade[$i];
            $data['textbook'] = $str_textbook[$i];
            $data['chapter'] = $str_chapter[$i];
            $data['festival'] = $str_festival[$i];
            $data['knowledge'] = $str_knowledge[$i];
            $data['child_knowledge'] = $str_child_knowledge[$i];

            $data['knowledge_info'] = trim($str_info[$i]);
            $data['knowledge_info_point'] = $data['knowledge_info'];

            if($candidateId[$i] > 0)
            {
                $str_arr=explode(',',$data['knowledge_info']);
                $str_arr_length=count($str_arr);

                if($str_arr_length>7){
                    $update_flag=false;
                    if($str_arr[$str_arr_length-2]!=$resource_name){
                        $str_arr[$str_arr_length-2]=$resource_name;
                        $update_flag=true;
                    }
                    if($str_arr[$str_arr_length-1]!=$description){
                        $str_arr[$str_arr_length-1]=$description;
                        $update_flag=true;
                    }
                    if($update_flag==true){

                        $data['knowledge_info'].=','.$resource_name.','.$creation_year.','.$source.','.$author.','.$resource_type.','.$description;
                        if (!empty($data['knowledge_info'])) {
                            $data['knowledge_info'] = explode(',',$data['knowledge_info']);
                            $data['knowledge_info'] = array_unique($data['knowledge_info']);
                            $data['knowledge_info'] = array_filter($data['knowledge_info']);
                            $data['knowledge_info'] = implode(',',$data['knowledge_info']);
                        }

                    }
                }else{

                    $data['knowledge_info'].=','.$resource_name.','.$creation_year.','.$source.','.$author.','.$resource_type.','.$description;

                    if (!empty($data['knowledge_info'])) {
                        $data['knowledge_info'] = explode(',',$data['knowledge_info']);
                        $data['knowledge_info'] = array_unique($data['knowledge_info']);
                        $data['knowledge_info'] = array_filter($data['knowledge_info']);
                        $data['knowledge_info'] = implode(',',$data['knowledge_info']);
                    }
                }

                $where = array();
                $where['id'] = $candidateId[$i];
                $result = $model->where($where)->save($data);
            }
            else
            {
                $data['knowledge_info'].=','.$resource_name.','.$creation_year.','.$source.','.$author.','.$resource_type.','.$description;
                $data['knowledge_resource_id'] = $id;

                $result = $model->add($data);
            }
            if(false === $result)
            {
                $addResult = false;
                break;
            }
        }
        return $addResult;
    }

    /*
     *获取教材版本
     */
    public function publishing($publishing_house_id=''){
        if(!empty($publishing_house_id)){
            $where['publishing_house_id'] = $publishing_house_id;
            $publishing = M('biz_textbook_publishing_contact')->where($where)->find();
            return $publishing;
        }else{
            $publishing = M('biz_textbook_publishing_contact')->select();
            return $publishing;
        }

    }

    /*
     *根据教材版本获取年级
     */
    public function getGradeByPublishing($PublishingId){
        $where['biz_textbook_publishing_contact.publishing_house_id'] = $PublishingId;
        $publishing_model = M('biz_textbook_publishing_contact')
            ->field('grade,dict_grade.id')
            ->join('biz_textbook on biz_textbook_publishing_contact.publishing_house_id = biz_textbook.publishing_house_id')
            ->join('dict_grade on biz_textbook.grade_id = dict_grade.id')
            ->group('dict_grade.id')
            ->where($where)
            ->select();
        return $publishing_model;
    }

    /*
     * 根据教材版本获取学科
     */
    public function getCourseByPublishing($PublishingId='1'){
        $where['biz_textbook_publishing_contact.publishing_house_id'] = $PublishingId;
        $publishing_model = M('biz_textbook_publishing_contact')
            ->field('course_name,dict_course_copy_resource.id')
            ->join('biz_textbook on biz_textbook_publishing_contact.publishing_house_id = biz_textbook.publishing_house_id')
            ->join('dict_course_copy_resource on biz_textbook.course_id = dict_course_copy_resource.id')
            ->where($where)
            ->group('dict_course_copy_resource.sort_order')
            ->order('dict_course_copy_resource.sort_order asc')
            ->select();
        return $publishing_model;
    }

    /*
    * 根据教材版本获取学科仅限前台使用
    */
    public function getCourseByPublishing2($PublishingId='1'){
        $where['biz_textbook_publishing_contact.publishing_house_id'] = $PublishingId;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $publishing_model = M('biz_textbook_publishing_contact')
            ->field('distinct dict_course_copy_resource.id,course_name')
            ->join('biz_textbook on biz_textbook_publishing_contact.publishing_house_id = biz_textbook.publishing_house_id')
            ->join('dict_course_copy_resource on biz_textbook.course_id = dict_course_copy_resource.id')
            ->join('knowledge_resource_point on knowledge_resource_point.course = dict_course_copy_resource.id')
            ->join('knowledge_resource on knowledge_resource.id = knowledge_resource_point.knowledge_resource_id')
            ->where($where)
            ->order('dict_course_copy_resource.sort_order asc')
            ->select();
        return $publishing_model;
    }

    /*
    * 根据教材版本获取年级仅限前台使用
    */
    public function getGradeByResources(){
        $grade_model = M('dict_grade')
            ->field('dict_grade.id,dict_grade.grade')
            ->join('knowledge_resource_point on knowledge_resource_point.grade = dict_grade.id')
            ->group('dict_grade.id')
            ->order('dict_grade.id asc')
            ->select();
        return $grade_model;
    }
    public function addResourceData($data)
    {
        return $this->model->add($data);
    }
    public function addResourceContactData($data)
    {
        return M('knowledge_resource_file_contact')->add($data);
    }

    /*
     *更新章级别所对应的knowledge_count字段
     */
    public function updateKnowledgeCountChapter(){
        $Model = M();
        $sql =  "UPDATE knowledge_point a,
             (
             SELECT
		parent_id,
		SUM(knowledge_resource.putaway_status = 1 AND knowledge_resource.status = 1 AND (knowledge_resource_point.id is not null)) AS p_count
        FROM
            knowledge_point
        LEFT JOIN 
            knowledge_resource_point
        ON  knowledge_resource_point.festival = knowledge_point.id
        LEFT JOIN 
        knowledge_resource
        ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id
        WHERE
            knowledge_point.parent_id <> 0  AND knowledge_point.`level` = 2
        GROUP BY
		parent_id 
            ) b
            SET a.knowledge_count = b.p_count
            WHERE
                a.id = b.parent_id";
        $Model->execute($sql);
    }

    /*
     *更新节级别所对应的knowledge_count字段
     */
    public function updateKnowledgeCountFestival(){
        $Model = M();
        $sql =  "UPDATE knowledge_point a,
             (
             SELECT
		parent_id,
		SUM(knowledge_resource.putaway_status = 1 AND knowledge_resource.status = 1 AND (knowledge_resource_point.id is not null)) AS p_count
        FROM
            knowledge_point
        LEFT JOIN 
            knowledge_resource_point
        ON  knowledge_resource_point.knowledge = knowledge_point.id
        LEFT JOIN 
        knowledge_resource
        ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id
        WHERE
            knowledge_point.parent_id <> 0 AND knowledge_point.`level` = 3
        GROUP BY
		parent_id 
            ) b
            SET a.knowledge_count = b.p_count
            WHERE
                a.id = b.parent_id";
        $Model->execute($sql);
    }

    /*
     *更新知识点级别所对应的knowledge_count字段
     */
    public function updateKnowledgeCountKnowledge(){
        $Model = M();
        $sql =  "UPDATE knowledge_point a,
             (
             SELECT
		parent_id,
		SUM(knowledge_resource.putaway_status = 1 AND knowledge_resource.status = 1 AND knowledge_resource_point.id is not null) AS p_count
        FROM
            knowledge_point
        LEFT JOIN 
            knowledge_resource_point
        ON  knowledge_resource_point.child_knowledge = knowledge_point.id
        LEFT JOIN 
        knowledge_resource
        ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id
        WHERE
            knowledge_point.parent_id <> 0  AND knowledge_point.`level` = 4
        GROUP BY
		parent_id 
            ) b
            SET a.knowledge_count = b.p_count
            WHERE
                a.id = b.parent_id";
        $Model->execute($sql);
    }

    public function getCourseMaxBrowseCount($courseId)
    {
        $condition['knowledge_resource_point.course'] = $courseId;
        $join=' INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id '.
            ' LEFT  JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook '.
            ' INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course '.
            ' LEFT  JOIN dict_grade on dict_grade.id=knowledge_resource_point.grade '.
            ' LEFT  JOIN knowledge_point chapter on chapter.id=knowledge_resource_point.chapter '.
            ' LEFT  JOIN knowledge_point festival on festival.id=knowledge_resource_point.festival '.
            ' LEFT  JOIN knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge '.
            ' LEFT  JOIN knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge ';

        $result=$this->model->where($condition)->join($join)
            ->field('max(knowledge_resource.browse_count) maxcount')
            ->group('knowledge_resource.id')->find();
        return $result['maxcount'];
    }

    public function getMaxBrowseCount()
    {
        $where['status'] = APPROVE;
        $where['knowledge_resource.putaway_status'] = PUTAWAY;
        $join=' INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id '.
            ' LEFT  JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook '.
            ' INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course '.
            ' LEFT  JOIN dict_grade on dict_grade.id=knowledge_resource_point.grade '.
            ' LEFT  JOIN knowledge_point chapter on chapter.id=knowledge_resource_point.chapter '.
            ' LEFT  JOIN knowledge_point festival on festival.id=knowledge_resource_point.festival '.
            ' LEFT  JOIN knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge '.
            ' LEFT  JOIN knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge ';

        $result=$this->model->join($join)->where($where)
            ->field('(knowledge_resource.browse_count) maxcount')
            ->group('knowledge_resource.id')->order('browse_count desc')->find();
        return $result['maxcount'];
    }
    public function getRecommendSortResourceIdList($courseId=0,$gradeId=0,$schoolTerm=1,$columnId=0,$maxCourseBrowseCount=0,$resourceId=0)
    {
        if(empty($schoolTerm))
            return array();
        $condition['knowledge_resource.putaway_status']=PUTAWAY;
        $condition['knowledge_resource.status']=APPROVE;
        if(!empty($resourceId)) //如果传入了资源ID，则只计算这个资源的权重
            $condition['knowledge_resource.id'] = $resourceId;
        if($columnId!=0)
            $condition['knowledge_resource_attr.column_id'] = $columnId;
        $join=' INNER JOIN knowledge_resource_attr ON knowledge_resource_attr.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id '.
            //' INNER JOIN knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id '.
            ' INNER JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id '.
            ' LEFT  JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook ';
        //' INNER JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course '.
        //' LEFT  JOIN dict_grade on dict_grade.id=knowledge_resource_point.grade '.
        //' LEFT  JOIN knowledge_point chapter on chapter.id=knowledge_resource_point.chapter '.
        //' LEFT  JOIN knowledge_point festival on festival.id=knowledge_resource_point.festival '.
        //' LEFT  JOIN knowledge_point knowledge on knowledge.id=knowledge_resource_point.knowledge '.
        //' LEFT  JOIN knowledge_point child_knowledge on child_knowledge.id=knowledge_resource_point.child_knowledge ';
        $maxCourseBrowseCount2 = $maxCourseBrowseCount*$maxCourseBrowseCount;
        if(COLUMN_RECOMMENDRESOURCE != $columnId)
            $weight1 = 1*$maxCourseBrowseCount;
        else
            $weight1 = "(case when knowledge_resource_type_contact.type_id = 17 or knowledge_resource_type_contact.type_id = 22 then 2 
                           when knowledge_resource_type_contact.type_id = 15 or knowledge_resource_type_contact.type_id = 13 then 1.5
                           when knowledge_resource_type_contact.type_id = 14 then 1.75 else 1 end) * $maxCourseBrowseCount";

        $courseWeightTable = array(
            1 => array(1=>1,6=>0.2,7=>0.2),         //语文
            2 => array(2=>1,4=>0.5,5=>0.5,31=>0.1), //数学
            3 => array(3=>1,6=>0.2,7=>0.2),         //英语
            4 => array(4=>1,2=>0.5,5=>0.2,8=>0.2,31=>0.2), //物理
            5 => array(5=>1,2=>0.5,4=>0.2,31=>0.2), //化学
            6 => array(6=>1,7=>0.2),                //政治
            7 => array(7=>1,1=>0.2,6=>0.2),         //历史
            8 => array(8=>1,4=>0.2),                //地理
            9 => array(9=>1,4=>0.1,5=>0.1),         //生物
            31 => array(31=>1,2=>0.1,4=>0.2,5=>0.2),//科学
        );
        if(empty($courseWeightTable[$courseId]))
            $courseWeightSql ='';
        else {
            $courseWeightSql = '(CASE ';
            foreach ($courseWeightTable[$courseId] as $key => $val) {
                $courseWeightSql .= " WHEN knowledge_resource_point.course = $key THEN $val ";
            }
            $courseWeightSql .= " ELSE 0 END ) * $maxCourseBrowseCount2  + ";
        }
        if(empty($gradeId))
            $gradeWeightSql = '';
        else
        {
            $gradeWeightSql = "max(((case when $gradeId > knowledge_resource_point.grade then knowledge_resource_point.grade else $gradeId end)/(case when $gradeId < knowledge_resource_point.grade then knowledge_resource_point.grade else $gradeId end))*$maxCourseBrowseCount2 )+";
        }
        $result=$this->model->where($condition)->join($join)
            ->field("knowledge_resource.id,( $weight1 +".
                $courseWeightSql.
                $gradeWeightSql.
                "max(case when biz_textbook.school_term <> $schoolTerm then 1 else 2 end) * $maxCourseBrowseCount + ".
                "(case when knowledge_resource.file_type = 'audio' then 0.5 else 1 end) * $maxCourseBrowseCount + ".
                "$maxCourseBrowseCount * (-0.294 * ln(0.1+ (unix_timestamp(now()) - knowledge_resource.create_at)/86400) + 1.2818) +".
                "knowledge_resource.browse_count + 10 * knowledge_resource.favorite_count + (case when knowledge_resource.charge_status = 1 then 0 else 100 end))*100000/$maxCourseBrowseCount2 as weight"
            )
            ->group('knowledge_resource.id')->order('weight desc')->select();
        return $result;
    }
    public function createSortTableByIdArray($idArray)
    {
        $sql='create temporary table if not exists resource_id_sort(id int UNSIGNED not null,sort int UNSIGNED not null, KEY USING HASH(id))engine=memory charset=utf8';
        $this->model->execute($sql);
        $drop_temp_table_sql='truncate table resource_id_sort';
        $this->model->execute($drop_temp_table_sql);
        $strArr = array();
        foreach($idArray as $key=>$val)
        {
            $strArr[] = " ({$val},". ($key+1) .") ";
        }
        $insertSql = 'insert into resource_id_sort values '.implode(',',$strArr);
        $this->model->execute($insertSql);
    }
    private function conditionTrans($str)
    {
        switch($str)
        {
            case 'neq' : return '<>';
            case 'eq'  : return '=';
            case 'in'  : return 'in';
            default : return '';
        }
    }
    public function getColumnResourceByOrderIds($resourceIds,$size=6)
    {
        $idArray =  explode(',',$resourceIds);
        $this->createSortTableByIdArray($idArray);

        $additionalWhere = '';
        if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false) || (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false) || (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false )) {
            $additionalWhere = ' AND  knowledge_resource.resource_type IN (0,1,2,3) ';
        }

        $innerTableSql = "SELECT knowledge_resource.id,sort FROM knowledge_resource JOIN resource_id_sort ON resource_id_sort.id=knowledge_resource.id WHERE putaway_status = ".PUTAWAY .
            " AND status = ".APPROVE .$additionalWhere."  ORDER BY sort ASC limit 0,$size";
        $fields = 'knowledge_resource.id resource_id,knowledge_resource.description,knowledge_resource.promote_price,knowledge_resource.real_price,knowledge_resource.mobile_cover img,knowledge_resource.name resource_name,file_type,source,knowledge_resource.author,dict_course_copy_resource.id course_id,course_name,'
            . 'biz_textbook.id textbook_id,count(distinct biz_textbook.id) textbook_number,biz_textbook.name textbook,count(distinct dict_grade.id) grade_number,dict_grade.grade,'
            . '(case when source = 1 then \'教师资源分享\' when  source = 2 then \'京版活动获奖设计\' end ) chinesesource ,pc_cover,mobile_cover,group_concat(distinct knowledge_type.type_name) type_name,'
            . 'charge_status';

        $sql = "SELECT $fields FROM ($innerTableSql) a JOIN knowledge_resource ON knowledge_resource.id = a.id ".
            " JOIN knowledge_resource_point on knowledge_resource_point.knowledge_resource_id=knowledge_resource.id ".
            " JOIN dict_course_copy_resource on dict_course_copy_resource.id=knowledge_resource_point.course ".
            " LEFT JOIN dict_grade ON dict_grade.id = knowledge_resource_point.grade ".
            " LEFT JOIN biz_textbook on biz_textbook.id=knowledge_resource_point.textbook ".
            ' JOIN knowledge_resource_type_contact on knowledge_resource_type_contact.resource_id=knowledge_resource.id'.
            ' JOIN knowledge_type on knowledge_type.id=knowledge_resource_type_contact.type_id'.
            " GROUP BY knowledge_resource.id ORDER BY a.sort ASC";

        $result=M()->query($sql);
        return $result;
    }

    /*
     * 根据学科年级查询分册列表（通过资源反查）
     */
    public function getETextBookByCourseGrade($courseId=0,$gradeId=0)
    {
        if($courseId == 0 || $gradeId == 0)
            return array();
        $sql = "SELECT knowledge_resource_point.textbook id,biz_textbook.name FROM knowledge_resource_point ".
            " JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id AND status = ".APPROVE ." AND putaway_status = ".PUTAWAY .
            " JOIN biz_textbook ON biz_textbook.id = knowledge_resource_point.textbook ".
            " WHERE knowledge_resource_point.course = $courseId AND grade = $gradeId GROUP BY biz_textbook.id ";
        $result = M()->query($sql);
        return $result;
    }

    public function getResourceCourse()
    {
        $sql = "SELECT knowledge_resource_point.course id,dict_course.course_name name FROM knowledge_resource_point ".
            " JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id AND status = ".APPROVE ." AND putaway_status = ".PUTAWAY .
            " JOIN dict_course ON dict_course.id = knowledge_resource_point.course ".
            "  GROUP BY dict_course.id ";
        $result = M()->query($sql);
        return $result;
    }
    /*
     * 根据学科查询年级列表（通过资源反查）
     */
    public function getGradeByCourse($courseId)
    {
        if($courseId == 0 )
            return array();
        $sql = "SELECT knowledge_resource_point.grade id,dict_grade.grade name FROM knowledge_resource_point ".
            " JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id AND status = ".APPROVE ." AND putaway_status = ".PUTAWAY .
            " JOIN dict_grade ON dict_grade.id = knowledge_resource_point.grade ".
            " WHERE knowledge_resource_point.course = $courseId GROUP BY dict_grade.id ";
        $result = M()->query($sql);
        return $result;
    }

    public function getAllApproveResourceId()
    {
        $sql = "SELECT knowledge_resource.id FROM knowledge_resource JOIN knowledge_resource_point ON knowledge_resource.id = knowledge_resource_point.knowledge_resource_id WHERE status = ".APPROVE ." AND putaway_status = ".PUTAWAY .' GROUP BY knowledge_resource.id';
        $result = M()->query($sql);
        return array_column($result,'id');
    }

    public function getResourceColumnId($id)
    {
        $sql = "SELECT column_id id FROM knowledge_resource_attr WHERE resource_id = $id";
        $result = M()->query($sql);
        return array_column($result,'id');
    }

    public function getResourceTypeId($id)
    {
        $sql = "SELECT type_id id FROM knowledge_resource_type_contact WHERE resource_id = $id";
        $result = M()->query($sql);
        return array_column($result,'id');
    }

    public function getResourceKnowledge($id)
    {
        $sql = "SELECT course,grade,textbook,chapter,festival,knowledge,child_knowledge,knowledge_info,school_term FROM knowledge_resource_point LEFT JOIN biz_textbook ON biz_textbook.id = textbook WHERE knowledge_resource_id = $id";
        $result = M()->query($sql);
        return $result;
    }

    /**Elastic Search Model **/

    /**
     *  @title: 初始化INDEX
     *  @return : bool
     */
    public function initIndex()
    {
        $response = json_decode( curl(ES_RESOURCE_URL,[],'GET') ,true);
        if( $response['error']) //delete success
        {
            if($response['error']['reason'] == 'no such index')
            {
                $this->modifyDefaultMapping();
            }
            return false;
        }
        return true;

    }
    /**
     *  @title: 删除除指定ID外的所有文档
     *  @param : retainedIds array 保留文档的资源ID数组
     *  @return : bool
     */
    public function deleteESDocument($retainedIds = [])
    {

        $deleteInfo = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        "terms" => [
                            'main.id' => $retainedIds,
                        ]
                    ]
                ]
            ]
        ];

        $response = json_decode(curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/_delete_by_query",$deleteInfo,'POST'),true);
        if(isset($response['deleted'])) //delete success
        {
            return true;
        }
        return false;
    }

    /**
     *  @title: 设置指定权重MAPPING可排序
     *  @param : mappingKey
     *  @return : bool
     */
    public function setWeightSortableMappingKey($mappingKey = '')
    {
        if(false === $this->initIndex())
            return false;
        $fieldDataSetupInfo = [
            'properties' => [
                'weights' => [
                    'properties' => [
                        "$mappingKey" => [
                            'type' => 'integer'
                        ]
                    ]
                ]
            ]
        ];

        $response = json_decode( curl(ES_RESOURCE_URL."_mapping/".ES_RESOURCE_TYPE,$fieldDataSetupInfo,'PUT'),true);
        if($response['acknowledged'] == true) //put success
        {
            return true;
        }
        return false;
    }

    /**
     *  @title: 添加/全更新资源文档
     *  @param : id 资源ID
     *  @return : bool
     */
    public function addDocument($id = 0)
    {
        if(empty($id))
            return false;

        $condition['knowledge_resource.id'] = $id;
        $mainResult = $this->getResourceData($condition,'','','','','',1,1);
        if(empty($mainResult))
            return false;
        $columnResult = $this->getResourceColumnId($id);
        $typeResult = $this->getResourceTypeId($id);
        $knowledgeResult = $this->getResourceKnowledge($id);

        $result['main'] = $mainResult['data'][0];
        $result['column'] = $columnResult;
        $result['type'] =  $typeResult;
        $result['knowledge'] = $knowledgeResult;
        $result['weights'] = ["w0_0_0"=>0];
        $response = json_decode( curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/$id",$result,'POST'),true);
        if($response['result'] == "created" || $response['result'] == "updated") //add/update success
        {
            return true;
        }
        return false;
    }

    /**
     *  @title: 更新文档
     *  @param : id 资源ID
     *  @param : modifyJSON 修改内容
     *  @return : bool
     */
    public function modifyDocument($id = 0 , $modifyJSON = [])
    {

        $result = ['doc'=>$modifyJSON];

        $response = json_decode( curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/{$id}/_update",$result,'POST') ,true);

        if( $response['result'] == "updated") //update success
        {
            return true;
        }
        else if($response['error']['type'] ==="document_missing_exception" ) //document not exists
        {
            return DOCUMENT_NOT_EXISTS;
        }
        return false;
    }

    /**
     *  @title: 批量更新文档
     *  @param : data 修改内容
     *  @return : bool
     *
     * POST /www.jtypt.com_knowledgeresource/recommend/_bulk
    {"update":{"_id":"10638"}}
    {"doc":{"main":{"author":"ak"}}}
    {"update":{"_id":"10638"}}
    {"doc":{"main":{"author":"ak"}}}
     *
     */
    public function bulkModifyDocument($data)
    {
        $modifyData = '';
        foreach($data as $key=>$val){
            $modifyData .= "{\"update\":{\"_id\":\"{$key}\"}} \r\n{\"doc\":".json_encode($val)."}\r\n";
        }
        $response = json_decode( curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/_bulk",$modifyData,'POST') ,true);

        if( $response['errors'] == false) //update success
        {
            return ['result'=>true];
        }
        $failArray['missing'] = [];
        foreach($response['items'] as $key=>$val){
            if($val['update']['error']['type'] ==="document_missing_exception" ){//document not exists
                $failArray['missing'][] = $val['update']['_id'];
            }
        }
        return ['result'=>false,'data'=>$failArray];
    }


    /**
     *  @title: 删除资源推荐INDEX
     *  @return : bool
     */
    public function deleteIndex()
    {

        $response = json_decode( curl(ES_RESOURCE_URL,["acknowledged"=>true],'delete') ,true);
        if( $response['acknowledged'] == true) //delete success
        {
            return true;
        }
        return false;
    }

    /**
     *  @title: 修改索引MAPPING
     *  @return : bool
     */
    public function modifyDefaultMapping()
    {

        $knowledgeMapping =  [
            "properties"=>[
                "knowledge_info" => [
                    "type"=> "text",
                    "store"=> false,
                    "term_vector"=> "with_positions_offsets",
                    "analyzer"=> "optimizeIK",
                    "search_analyzer"=> "optimizeIK",
                    "include_in_all"=> "true",
                    "boost"=> 8
                ]
            ]
        ];
        $mainMapping = [
            'properties' => [
                "create_at" => [
                    'type' => 'integer',
                ],
                "browse_count" => [
                    'type' => 'integer',
                ]
            ]
        ];

        $map =
            [
                "settings" => [
                    "index.mapping.total_fields.limit"=>10000,
                    "analysis"=> [
                        "analyzer"=> [
                            "optimizeIK"=> [
                                "type"=> "custom",
                                "tokenizer"=> "ik_max_word",
                                "filter"=> [
                                    "stemmer"
                                ]
                            ]
                        ]
                    ]
                ],


                "mappings"=>[
                    ES_RESOURCE_TYPE => [
                        "properties"=> [
                            "knowledge"=> $knowledgeMapping,
                            "main" => $mainMapping
                        ]
                    ]
                ]
            ]
        ;

        $response = json_decode( curl(ES_RESOURCE_URL,$map,'PUT') ,true);

        if( $response['acknowledged'] == true) //update success
        {
            return true;
        }
        return false;
    }

    /**
     *  @title: 删除资源文档
     *  @param : id 资源ID
     *  @return : bool
     */
    public function deleteDocument($id)
    {
        if(false === $this->initIndex())
            return false;
        $response = json_decode( curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/{$id}",[],'DELETE') ,true);
        if( $response['result'] == "deleted") //delete success
        {
            return true;
        }
        return false;
    }

    private function getESQueryMatch($key,$condition)
    {

        if(is_array($condition)){
            if(strtoupper($condition[0]) == "LIKE" ) //single like
            {
                return ['match'=>[$key=>str_replace('%','',$condition[1])]];
            }
            else //multiple like
            {
                $matches = [];
                foreach($condition as $null => $val){
                    if(strtoupper($val[0]) == "LIKE" )
                    {
                        $matches[] =  ['match'=>[$key=>str_replace('%','',$val[1])]];
                    }
                }
                return $matches;
            }
        }
        return $condition;
    }

    private function getESQueryTerm($condition)
    {
        if(is_array($condition)){
            if(strtoupper($condition[0]) == 'IN'){
                return $condition[1];
            }
        }
        return [$condition];
    }
    private function getMatchCondition($condition)
    {
        //keyword author knowledge_info
        $matchCondition = [];
        $mapArray = [
            'knowledge_resource_point.knowledge_info' => 'knowledge.knowledge_info',
            'knowledge_resource.author' => 'main.author',

        ];
        foreach($mapArray as $key => $val){
            if(isset($condition[$key])){
                $matchCondition = array_merge($matchCondition,$this->getESQueryMatch($val,$condition[$key]));
            }
        }

        return $matchCondition;
    }
    private function getTermCondition($condition)
    {
        if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false) || (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false)) {
            $termCondition = [['terms'=>['main.resource_type'=>[0,1,2,3]]]];
        }
        else if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false )){
            $termCondition = [['terms'=>['main.resource_type'=>[0,1,3]]]];
        }
        else
           $termCondition = [];
        $mapArray = [
            'knowledge_resource.id' => 'main.id',
            'knowledge_resource_attr.column_id' => 'column',
            'knowledge_resource_point.course' => 'knowledge.course',
            'knowledge_resource_point.grade' => 'knowledge.grade',
            'biz_textbook.school_term' => 'knowledge.school_term',
            'knowledge_resource_point.textbook' => 'knowledge.textbook',
            'knowledge_resource.file_type' => 'main.file_type',
            'knowledge_resource_type_contact.type_id' => 'type',
            'knowledge_resource_point.knowledge' => 'knowledge.knowledge',
            'knowledge_resource_point.child_knowledge' => 'knowledge.child_knowledge',
            'knowledge_resource_point.chapter' => 'knowledge.chapter',
            'knowledge_resource_point.festival' => 'knowledge.festival',
        ];
        foreach($mapArray as $key => $val){
            if(isset($condition[$key]))
                $termCondition[] = ['terms' => [$val => $this->getESQueryTerm($condition[$key])]];
        }


        return $termCondition;
    }
    private function getRangeCondition($condition)
    {
        $rangeCondition = [];
        if(isset($condition['_string'])){
            //putaway_time transition
            preg_match_all("|putaway_time([\\D]+)([\\d]+)\\S?|is", $condition['_string'], $out,PREG_SET_ORDER);
            $timeRange = [];
            foreach($out as $key=>$val)
            {
                switch($val[1])
                {
                    case '>': $timeRange['gt'] = "$val[2]";
                        break;
                    case '<': $timeRange['lt'] = "$val[2]";
                        break;
                    case '>=': $timeRange['gte'] = "$val[2]";
                        break;
                    case '<=': $timeRange['lte'] = "$val[2]";
                        break;
                }
            }
            $rangeCondition['main.putaway_time'] = $timeRange;
            $rangeCondition = ['range' => $rangeCondition];
        }
        return $rangeCondition;
    }
    private function getSortCondition($condition,$sort)
    {
        $sortJSON = [];
        if(!empty($sort)){
            switch($sort)
            {
                case 'asc'  :  $sortJSON = ['main.create_at' => ['order'=>'asc']];
                    break;
                case 'desc' :  $sortJSON = ['main.create_at' => ['order'=>'desc']];
                    break;
                default     :  $sortJSON = ['main.browse_count' => ['order'=>'desc']];
                    break;
            }
        }
        else {
            if(!empty($condition['knowledge_resource_point.knowledge_info']))
                return [];
            if (empty($condition['userCourseId']) && empty($condition['userGradeId'])) {
                $sortJSON = ['weights.w0_0_0' => ['order' => 'desc']];
            } else {
                $condition['userCourseId'] = $condition['userCourseId'] ? $condition['userCourseId']:0;
                $condition['userGradeId'] = $condition['userGradeId'] ? $condition['userGradeId']:0;
                $sortJSON = ["weights.w{$condition['userCourseId']}_{$condition['userGradeId']}_0" => ['order' => 'desc']];
            }
        }
        return $sortJSON;
    }
    private function getResourceDataFromES($condition,$sort='',$pageIndex,$pageSize)
    {
        $sortJSON = $this->getSortCondition($condition,$sort);
        $matchJSON = $this->getMatchCondition($condition);
        $rangeJSON = $this->getRangeCondition($condition);
        $termJSON = $this->getTermCondition($condition);
        $pageIndex = $pageIndex == 0? 1:$pageIndex;
        $sendJSON = [
            "from" => ($pageIndex - 1) * $pageSize,
            "size" => $pageSize,
            "_source" => 'main',
            'aggs'=> ['ids' => ['terms' =>['field' => 'main.id.keyword','size' => '2147483647']]]
        ];
        $queryJSON = array_merge($termJSON,$matchJSON,$rangeJSON);
        if(!empty($queryJSON))
            $sendJSON['query'] = [
                'bool' => ['must' => $queryJSON]
            ];

        if(!empty($sortJSON)){
            $sendJSON['sort'] = $sortJSON;
        }
        $response = json_decode(curl(ES_RESOURCE_URL.ES_RESOURCE_TYPE."/_search",$sendJSON,'GET'),true);
        if(isset($response['took'])) //delete success
        {
            $idArray = $response['aggregations']['ids']['buckets'];
            return [$response['hits']['total'],array_column(array_column($response['hits']['hits'],'_source'),'main'),array_column($idArray,'key')];
        }
        return [0,[]];

    }



    public function getAdditionalVideoResource()
    {
        $result = M()->query('SELECT knowledge_resource.id,knowledge_resource_file_contact.id contactid,knowledge_resource_file_contact.vid_fullpath url,file_name name,knowledge_resource_file_contact.vid_image_path,
                    GROUP_CONCAT( 
                    CONCAT(1,\'&amp;\',knowledge_resource_point.course,\'&amp;\',IFNULL(knowledge_resource_point.grade,0),\'&amp;\',IFNULL(biz_textbook.school_term,0),\'&amp;\',IFNULL(knowledge_resource_point.chapter,0),\'&amp;\',IFNULL(knowledge_resource_point.festival,0)
                    ,\'||\' ,\'北京版\',\'&amp;\' ,dict_course_copy_resource.course_name,\'&amp;\',IFNULL(dict_grade.grade,\'\'),\'&amp;\',
                    IFNULL((CASE WHEN biz_textbook.school_term = 1 THEN \'上册\' WHEN biz_textbook.school_term = 2 THEN \'下册\' ELSE \'全一册\' END),\'\'),
                    \'&amp;\',IFNULL(chapter.knowledge_name,\'\'),\'&amp;\',IFNULL(festival.knowledge_name,\'\')
                    ) SEPARATOR \'||||\') code,
                    (knowledge_resource_file_contact.putaway_status = 1 AND 
                    knowledge_resource.status = 1 AND 
                    knowledge_resource.putaway_status = 1
                    ) is_display
                    FROM knowledge_resource_file_contact JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id
                    JOIN knowledge_resource_point ON knowledge_resource_point.knowledge_resource_id = knowledge_resource.id
                    JOIN dict_course_copy_resource ON dict_course_copy_resource.id = knowledge_resource_point.course
                    JOIN dict_grade ON dict_grade.id = knowledge_resource_point.grade
                    JOIN biz_textbook ON biz_textbook.id = knowledge_resource_point.textbook
                    JOIN knowledge_point chapter ON chapter.id = knowledge_resource_point.chapter
                    JOIN knowledge_point festival ON festival.id = knowledge_resource_point.festival
                    WHERE knowledge_resource.file_type = \'video\'  GROUP BY knowledge_resource_file_contact.id');
        return $result;

    }

    public function getModifyVideoResource($idArray=[])
    {
        $idWhere = ' AND knowledge_resource.id IN ('.implode(',',$idArray).') ';
        $result = M()->query('SELECT knowledge_resource.id,knowledge_resource_file_contact.id contactid,knowledge_resource_file_contact.vid_fullpath url,file_name name,knowledge_resource_file_contact.vid_image_path,
                    GROUP_CONCAT( 
                    CONCAT(1,\'&amp;\',knowledge_resource_point.course,\'&amp;\',IFNULL(knowledge_resource_point.grade,0),\'&amp;\',IFNULL(biz_textbook.school_term,0),\'&amp;\',IFNULL(knowledge_resource_point.chapter,0),\'&amp;\',IFNULL(knowledge_resource_point.festival,0)
                    ,\'||\' ,\'北京版\',\'&amp;\' ,dict_course_copy_resource.course_name,\'&amp;\',IFNULL(dict_grade.grade,\'\'),\'&amp;\',
                    IFNULL((CASE WHEN biz_textbook.school_term = 1 THEN \'上册\' WHEN biz_textbook.school_term = 2 THEN \'下册\' ELSE \'全一册\' END),\'\'),
                    \'&amp;\',IFNULL(chapter.knowledge_name,\'\'),\'&amp;\',IFNULL(festival.knowledge_name,\'\')
                    ) SEPARATOR \'||||\') code,(CASE WHEN knowledge_resource.status =1 AND knowledge_resource.putaway_status = 1 THEN 2 ELSE 1 END) status
 
                    FROM knowledge_resource_file_contact JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id
                    JOIN knowledge_resource_point ON knowledge_resource_point.knowledge_resource_id = knowledge_resource.id
                    JOIN dict_course_copy_resource ON dict_course_copy_resource.id = knowledge_resource_point.course
                    JOIN dict_grade ON dict_grade.id = knowledge_resource_point.grade
                    JOIN biz_textbook ON biz_textbook.id = knowledge_resource_point.textbook
                    JOIN knowledge_point chapter ON chapter.id = knowledge_resource_point.chapter
                    JOIN knowledge_point festival ON festival.id = knowledge_resource_point.festival
                    WHERE knowledge_resource.file_type = \'video\'  '.$idWhere.' GROUP BY knowledge_resource_file_contact.id');
        return $result;

    }



    public function getVideoResourcesAndKnowledge()
    {
        $result = M()->query('SELECT knowledge_resource.id,knowledge_resource_file_contact.id contactid,knowledge_resource_file_contact.vid_fullpath url,file_name name,
                    GROUP_CONCAT( 
                    CONCAT(1,\'&amp;\',knowledge_resource_point.course,\'&amp;\',IFNULL(knowledge_resource_point.grade,0),\'&amp;\',IFNULL(biz_textbook.school_term,0),\'&amp;\',IFNULL(knowledge_resource_point.chapter,0),\'&amp;\',IFNULL(knowledge_resource_point.festival,0)
                    ,\'||\' ,\'北京版\',\'&amp;\' ,dict_course_copy_resource.course_name,\'&amp;\',IFNULL(dict_grade.grade,\'\'),\'&amp;\',
                    IFNULL((CASE WHEN biz_textbook.school_term = 1 THEN \'上册\' WHEN biz_textbook.school_term = 2 THEN \'下册\' ELSE \'全一册\' END),\'\'),
                    \'&amp;\',IFNULL(chapter.knowledge_name,\'\'),\'&amp;\',IFNULL(festival.knowledge_name,\'\')
                    ) SEPARATOR \'||||\') code
 
                    FROM knowledge_resource_file_contact JOIN knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id
                    JOIN knowledge_resource_point ON knowledge_resource_point.knowledge_resource_id = knowledge_resource.id
                    JOIN dict_course_copy_resource ON dict_course_copy_resource.id = knowledge_resource_point.course
                    LEFT JOIN dict_grade ON dict_grade.id = knowledge_resource_point.grade
                    LEFT JOIN biz_textbook ON biz_textbook.id = knowledge_resource_point.textbook
                    LEFT JOIN knowledge_point chapter ON chapter.id = knowledge_resource_point.chapter
                    LEFT JOIN knowledge_point festival ON festival.id = knowledge_resource_point.festival
                    WHERE knowledge_resource.file_type = \'video\'  GROUP BY knowledge_resource_file_contact.id');
        return $result;

    }
}
