<?php
namespace Common\Model;
use Think\Model;
define('COLUMN_CONTENTTYPE_ACTIVITY',1);
define('COLUMN_CONTENTTYPE_INFORMATION',2);
define('COLUMN_CONTENTTYPE_VOTE',3);

define('COLUMN_CONTENTSTATUS_OFFLINE',0);
define('COLUMN_CONTENTSTATUS_ONLINE',1);

define('COLUMN_CONTENTFLAG_UNAUTH',1);
define('COLUMN_CONTENTFLAG_AUTH',2);
define('COLUMN_CONTENTFLAG_REJECT',3);


define('COLUMN_STATUS_WAITFORVERIFY',1);  //待审核
define('COLUMN_STATUS_VERIFIED',2);       //已审核
define('COLUMN_STATUS_DECLINED',3);       //已拒绝
define('COLUMN_STATUS_PUBLISHED',4);      //已发布
define('COLUMN_STATUS_OFFSHELF',5);       //已下架

class Social_activityModel extends Model
{

    public $model = '';
    protected $tableName = 'social_activity';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    public function getActivityTitle($activity_id)
    {
        $result = $this->model->where('id=' . $activity_id)->field('id,title')->find();
        return $result;
    }

    public function getActivityExists($activity_id)
    {
        $result = $this->model->where('id=' . $activity_id)->field('1')->find();
        return empty($result) ? false : true;
    }

    /*
     * 获取活动类型列表
     *
     */

    public function getActivityClassList($parent_id = -1)
    {
        $where = array();
        if ($parent_id != -1) {
            $where['social_activity_class.parent_id'] = $parent_id;
        }
        $result = M('social_activity_class')->order('sort_order asc')->join('social_activity_class second ON second.id = social_activity_class.parent_id','left')->where($where)->field('social_activity_class.id,social_activity_class.class,social_activity_class.class title,ifnull(\'\',second.class) parent_class,social_activity_class.sort_order,social_activity_class.parent_id')->select();
        return $result;
    }

    /*
     * 获取非本类型的活动类型
     *
     */

    public function getAvailableActivityClass($id = 0)
    {
        $where = array();
        if ($id != -1) {
            $where['id'] = array('neq',$id);
        }
        $result = M('social_activity_class')->where($where)->field('social_activity_class.id,social_activity_class.class')->select();
        return $result;
    }
    /*
     * 添加编辑活动类型
     *
     */

    public function addEditClass($id=0,$parentId,$typeName)
    {
        $model = M('social_activity_class');
        $data = array();
        $data['parent_id'] = $parentId;
        $data['class'] = $typeName;
        $model->startTrans();
        $addEditResult = true;
        if(0 == $id)
        {
            $id = $model->add($data);
            if($id) {
                $save['sort_order'] = $id;
                if(false === $model->where('id=' . $id)->save($save))
                    $addEditResult = false;
            }
            else
                $addEditResult = false;
        }
        else
        {
            if( false === $model->where('id=' . $id)->save($data))
                $addEditResult = false;
        }
        if(false === $addEditResult) {
            $model->rollback();
            return false;
        }
        else {
            $model->commit();
            return true;
        }
    }

    /*
     * 获取活动列表
     * category 活动类型ID
     * keyword 关键字
     * pageIndex 页码
     * pageSize 每页条数
     * role 角色
     * userId 用户ID (当角色和用户ID不为空时,查询我收藏/发布的活动)
     * myCategory 0--所有活动 1--我收藏的活动 2--我参加的活动
     */
    public function getActivities($category = 0, $keyword = '', $pageIndex = 1, $pageSize = 20, &$count, $role = 0, $userId = 0, $myCategory = 0,$time='',$fromAPP = 0)
    {
        if ($category != 0) {
            $where['_string'] = "class_id='{$category}' or social_activity_class.parent_id='{$category}'";
        }
        $where['status'] = ACTIVITY_STATUS_PUBLISHED;
        if ($keyword != ''){
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
                $temp_arr = explode(' ',$filter['keyword']);
                foreach ($temp_arr as $item){
                    $where['title'][] = array("like", "%" . $item . "%");
                }
        }
        if($time !==''){
            if($time == 1){
                $where['activitystart'] = array('GT',time());//活动未开始
            }else if($time ==2){
                $where['activitystart'] = array('LT',time());
                $where['activityend'] = array('GT',time());//活动进行中
            }else if($time == 3)
            {
                $where['activityend'] = array('LT',time());//活动已结束
            }
        }
        $roles = $role - 1;
        switch ($myCategory) {
            case 1:
                $join = "social_activity_favor ON social_activity.id = social_activity_favor.social_activity_id AND social_activity_favor.user_id = $userId AND social_activity_favor.user_type=$roles";
                break;
            case 2:
                $join = "social_activity_register ON social_activity.id = social_activity_register.activity_id AND social_activity_register.user_id = $userId AND social_activity_register.role=$role";
                break;
            default:
                $join = '';
                break;
        }
        $count = $this->model
            ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
            ->join('social_activity_class on social_activity_class.id=' . $this->tableName . '.class_id')
            ->join($join)
            ->where($where)
            ->group('social_activity.id')
            ->field('1')
            ->select();
        $count = count($count);
        if(0 == $fromAPP)
        {
            $field = 'activitystart,activityend,is_live,livestart,liveend,social_activity.id,social_activity.display_activity_apply_startendtime,social_activity.stakeholder,social_activity.remark,title,social_activity.class_id,social_activity.approve_at,social_activity.approve_at create_at,short_content,nickname as publisher,social_activity.zan_count,social_activity.favor_count,social_activity.browse_count,if(UNIX_TIMESTAMP(NOW())-604800>social_activity.approve_at,\'no\',\'yes\') is_new,'
                . 'social_activity_class.parent_id,social_activity_class.class,social_activity.works_show_status,background_image,social_activity.display_activity_startendtime';
        }
        else
        {
            $field = 'is_live,livestart,liveend,activitystart,activityend,social_activity.id,\''.ICON_ACTIVITY.'\' icon_url,social_activity.title, concat(\'参与对象: \', social_activity.stakeholder) content1,'.
                'concat(\'活动时间: \' , social_activity.display_activity_startendtime)  content2,
                 (CASE WHEN home_jump_url = 1 AND UNIX_TIMESTAMP(NOW()) >= livestart AND UNIX_TIMESTAMP(NOW()) <= liveend THEN url ELSE concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityDetails?id=\' ,social_activity.id) END) url,'.
                'concat(\'http://'.$_SERVER['SERVER_NAME'].ACTIVITY_LOCAL_DIR.'\' ,  social_activity.short_content)  img_url,'.
                'concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityShare?id=\' ,social_activity.id) share_url'
            ;

        }

        if ($pageIndex == -1) {
            $result = $this->model
                ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
                ->join('social_activity_class on social_activity_class.id=' . $this->tableName . '.class_id')
                ->join($join)
                ->where($where)
                ->group('social_activity.id')
                ->field($field)
                ->order('social_activity.approve_at desc,social_activity.id desc')->select();
        } else {
            if(empty($pageIndex))
                $pageIndex = 1;
            $result = $this->model
                ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
                ->join('social_activity_class on social_activity_class.id=' . $this->tableName . '.class_id')
                ->join($join)
                ->where($where)
                ->group('social_activity.id')
                ->field($field)
                ->page($pageIndex . ',' . $pageSize)->order('social_activity.approve_at desc,social_activity.id desc')->select();

        }
        return $result;
    }


    /*
     * 获得某一个活动下的作品       
     */
    public function getActivityWorks($activity_id, $keyword, $pageIndex = 1, $pageSize = 20, &$count)
    {
        $where['social_activity.id'] = $activity_id;
        $where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $where['social_activity_works.status'] = ACTIVITY_WORK_STATUS_VERIFIED;
        $where['social_activity_works.point'] = array('neq','\'\'');
        if (!empty($keyword)) {
          /*  $where[] = array(
                array(
                    'social_activity_register.user_name' => array('like', '%' . $keyword . '%'),
                    'dict_course.course_name' => $keyword,
                    'social_activity_works.works_name' => array('like', '%' . $keyword . '%'),
                    '_logic' => 'OR'
                )
            );*/
           $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where[] = array(
                    array(
                        'social_activity_register.user_name' => array('like', '%' . $item . '%'),
                        'dict_course.course_name' => $item,
                        'social_activity_works.works_name' => array('like', '%' . $item . '%'),
                        '_logic' => 'OR'
                    )
                );
            }
        }
        if ($pageIndex == 0) {
            $result = $this->model
                ->where($where)
                ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
                ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
                ->join('auth_teacher ON auth_teacher.id = social_activity_register.user_id', 'left')
                ->join('dict_schoollist ON dict_schoollist.id = auth_teacher.school_id', 'left')
                ->join('dict_citydistrict ON dict_schoollist.district_id = dict_citydistrict.id', 'left')
                ->join("dict_course on dict_course.id=social_activity_works.course",'left')
                ->join("dict_grade on dict_grade.id=social_activity_works.grade",'left')
                ->field('social_activity.title,social_activity_works.id works_id,social_activity_works.status,social_activity_works.works_name,dict_course.course_name,dict_grade.grade,'
                    . 'social_activity_register.user_name,point,dict_schoollist.school_name,dict_citydistrict.name district')
                ->order("point asc")
                ->select();
        } else {
            $count = $this->model
                ->where($where)
                ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
                ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
                ->join('auth_teacher ON auth_teacher.id = social_activity_register.user_id', 'left')
                ->join('dict_schoollist ON dict_schoollist.id = auth_teacher.school_id', 'left')
                ->join('dict_citydistrict ON dict_schoollist.district_id = dict_citydistrict.id', 'left')
                ->join("dict_course on dict_course.id=social_activity_works.course",'left')
                ->join("dict_grade on dict_grade.id=social_activity_works.grade",'left')
                ->field('count(1) as num')
                ->find();
            $count = $count['num'];

            $result = $this->model
                ->where($where)
                ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
                ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
                ->join('auth_teacher ON auth_teacher.id = social_activity_register.user_id', 'left')
                ->join('dict_schoollist ON dict_schoollist.id = auth_teacher.school_id', 'left')
                ->join('dict_citydistrict ON dict_schoollist.district_id = dict_citydistrict.id', 'left')
                ->join("dict_course on dict_course.id=social_activity_works.course",'left')
                ->join("dict_grade on dict_grade.id=social_activity_works.grade",'left')
                ->page(($pageIndex) . ',' . $pageSize)
                ->field('social_activity.title,social_activity_works.id works_id,social_activity_works.works_name,dict_course.course_name,dict_grade.grade,'
                    . 'social_activity_register.user_name,point,dict_schoollist.school_name,dict_citydistrict.name district')
                ->order("point asc")
                ->select();
        }
        return $result;
    }

    public function getRegisteredNumber($activity_id)
    {
        $where['activity_id'] = $activity_id;
        $result = M('social_activity_register')->where($where)->field('count(1) as num')->find();
        return $result['num'];
    }

    public function getWorkUploadPeopleNumber($activity_id)
    {

        $where['activity_id'] = $activity_id;
        $where['social_activity_works.status'] = ACTIVITY_WORK_STATUS_VERIFIED;
        $result = M('social_activity_register')->where($where)->join('social_activity_works ON social_activity_works.activity_register_id = social_activity_register.id')->field('count(1) as num')->find();
        return $result['num'];

    }

    public function getHasPointPeopleNumber($activity_id)
    {

        $where['activity_id'] = $activity_id;
        $where['social_activity_works.status'] = ACTIVITY_WORK_STATUS_VERIFIED;
        $where['_string'] = 'social_activity_works.point <> 0';
        $result = M('social_activity_register')->where($where)->join('social_activity_works ON social_activity_works.activity_register_id = social_activity_register.id')->field('count(1) as num')->find();
        return $result['num'];

    }

    /*
     * 获得我发布的作品,构建sql
     */
    public function getMyPublishedWorks($user_id)
    {
        $where['social_activity_register.user_id'] = $user_id;
        $result = $this->model
            ->where($where)
            ->group('date')
            ->join("social_activity_class on social_activity_class.id=social_activity.class_id")
            ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
            ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("dict_course on dict_course.id=social_activity_works.course")
            ->join("dict_grade on dict_grade.id=social_activity_works.grade")
            ->field("2 flag,social_activity_works.id ,FROM_UNIXTIME(social_activity_works.create_at,'%Y%m%d') date")
            ->buildSql();
        //->order('social_activity_works.update_at desc')
        //->select();
        return $result;
    }

    /*
     * 获得我参与的活动
     */
    public function getMyInvolvementActivity($user_id, $role = 2,$pageIndex = 1, $pageSize = 20)
    {
        $where['social_activity_register.user_id'] = $user_id;
        $where['social_activity_register.role'] = $role;
        $where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $result = $this->model
            ->where($where)
            ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
            ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
            ->join("social_activity_class on social_activity_class.id=social_activity.class_id")
            ->field("social_activity.id,FROM_UNIXTIME(social_activity_register.register_at,'%Y%m%d') date")
            ->group('date')
            ->order('date desc')
            ->page(($pageIndex) . ',' . $pageSize)
            ->select();
        $new_arr = array();

        foreach ($result as $val) {
            $data = $this->model
                ->where($where)
                ->having("date=" . $val['date'])
                ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
                ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
                ->join("social_activity_class on social_activity_class.id=social_activity.class_id")
                ->field("social_activity.id,social_activity_register.id register_id,title,social_activity_register.register_at as update_at,short_content,"
                    . "nickname as publisher,social_activity_class.class,social_activity_class.parent_id,FROM_UNIXTIME(social_activity_register.register_at,'%Y%m%d') date,background_image,works_show_status")
                ->group('social_activity.id')
                ->order('social_activity_register.register_at desc')
                ->select();
            $new_arr[] = $data;
        }
        return $new_arr;
    }


    /*
     * 获得我收藏的作品,按照活动分组
     */
    public function getMyCollectActivityWorks($user_id, $role, $keyword = '', $pageIndex = 1, $pageSize = 20, &$count, $period = 0, $course = 0, $grade = 0, $category = 0)
    {
        $where = array();
        if ($keyword != '') {
           /* $where[] =
                array(
                    'social_activity.title' => array('like', '%' . $keyword . '%'),
                    'dict_course.course_name' => $keyword,
                    'social_activity_works.works_name' => array('like', '%' . $keyword . '%'),
                    '_logic' => 'OR'
                );*/
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where[] =
                    array(
                        'social_activity.title' => array('like', '%' . $item . '%'),
                        'dict_course.course_name' => $item,
                        'social_activity_works.works_name' => array('like', '%' . $item . '%'),
                        '_logic' => 'OR'
                    );
            }
        }
        $where['social_activity_works_favor.user_id'] = $user_id;
        $where['social_activity_works_favor.user_type'] = $role;
        //$where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        if ($grade != 0) {
            $courseGradeWhere['grade'] = $grade;
        } else if ($period != 0) {
            switch ($period) {
                case 1: //小学全年级
                    $courseGradeWhere['grade'] = array('in', array(0, 1, 2, 3, 4, 5, 6));
                    break;
                case 2: //初中全年级
                    $courseGradeWhere['grade'] = array('in', array(0, 7, 8, 9));
                    break;
                case 3: //高中全年级
                    $courseGradeWhere['grade'] = array('in', array(0, 10, 11, 12));
                    break;
                default:
                    break;
            }
        }
        if ($course != 0) {
            $courseGradeWhere['course'] = array('in', array(0, $course));
        }

        $activityList = M('social_activity_course_grade')->where($courseGradeWhere)->field('distinct activity_id as id')->select();
        $activityList = array_column($activityList, 'id');
        //empty judgement
        if (empty($activityList))
            $where['social_activity.id'] = array('in', array(0));
        else
            $where['social_activity.id'] = array('in', $activityList);
        if ($category != 0) {
            $where['social_activity.class_id'] = $category;
        } else {
            $where['social_activity.class_id'] = array('in', array(1,2));
        }
        //$where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $countResult = $this->model
            ->where($where)
            ->join("left join social_activity_class on social_activity_class.id=social_activity.class_id")
            ->join("left join social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join social_activity_works_favor on social_activity_works_favor.activity_works_id=social_activity_works.id")
            ->join("left join dict_course on dict_course.id=social_activity_works.course")
            ->join("left join dict_grade on dict_grade.id=social_activity_works.grade")
            ->field("count(1) as num")
            ->select();
        $count = $countResult['num'];

        $result = $this->model
            ->where($where)
            ->join("left join social_activity_class on social_activity_class.id=social_activity.class_id")
            ->join("left join social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join social_activity_works_favor on social_activity_works_favor.activity_works_id=social_activity_works.id")
            ->join("left join dict_course on dict_course.id=social_activity_works.course")
            ->join("left join dict_grade on dict_grade.id=social_activity_works.grade")
            ->field("social_activity.id activity_id,social_activity.title,social_activity.title,social_activity_works.id works_id,social_activity_works.works_name,social_activity_class.parent_id,"
                . "social_activity_class.id class_id,social_activity_class.class,dict_course.course_name,"
                . "social_activity_works.create_at,dict_grade.grade,social_activity_register.user_name publish_people,"
                . "social_activity_works.browse_number,social_activity_works.zan_count,social_activity_works.favor_count,background_image")
            ->order('social_activity_works.create_at desc')
            ->page(($pageIndex) . ',' . $pageSize)
            ->select();

        return $result;
    }

    /*
     * 获得某个作品的收藏数量
     */
    public function getWorksCollectNumber($works_id)
    {
        $where['social_activity_works.id'] = $works_id;
        $model = M('social_activity_works');
        $result = $model->where($where)->join("social_activity_works_favor on social_activity_works_favor.activity_works_id=social_activity_works.id")
            ->field('count(social_activity_works_favor.id) favor_number')->find();
        return $result['favor_number'];
    }

    /*
     * 获得某个作品的点赞数量
     */
    public function getWorksZanNumber($works_id)
    {
        $where['social_activity_works.id'] = $works_id;
        $model = M('social_activity_works');
        $result = $model->where($where)->join("social_activity_works_zan on social_activity_works_zan.activity_works_id=social_activity_works.id")
            ->field('count(social_activity_works_zan.id) zan_number')->find();
        return $result['zan_number'];
    }

    public function getFavorActivities($userInfo, $pageIndex = 1, $pageSize = 20, $keyword = '')
    {
        $where['status'] = ACTIVITY_STATUS_PUBLISHED;
        if ($keyword != '') {
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where['social_activity.title'][] = array('like', '%' . $item . '%');
            }
            //$where['social_activity.title'] = array('like', '%' . $keyword . '%');
        }
        $result = $this->model
            ->where($where)
            ->join("(select id,social_activity_id,user_id,user_type,favor_time from social_activity_favor where user_type={$userInfo['user_type']} and user_id = {$userInfo['user_id']}) a
                    on a.social_activity_id = social_activity.id")
            ->join("social_activity_class on social_activity_class.id=social_activity.class_id")
            ->join('auth_admin ON auth_admin.id = ' . $this->tableName . '.publisher_id')
            ->field('social_activity.id,title,social_activity.create_at,short_content,nickname as publisher,social_activity_class.class,'
                . 'social_activity_class.parent_id,social_activity.status,works_show_status,background_image')
            ->order('a.favor_time desc')
            ->page(($pageIndex) . ',' . $pageSize)
            ->select();
        return $result;

    }

    /*
     * 获取活动详情
     * id 活动ID
     */
    public function getActivityDetails($id)
    {
        $where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $where['social_activity.id'] = $id;
        $result = $this->model
            ->where($where)
            ->join('auth_admin ON auth_admin.id = social_activity.publisher_id')
            ->join('social_activity_course_grade on social_activity_course_grade.activity_id=social_activity.id', 'left')
            ->join('social_activity_class ON social_activity_class.id = social_activity.class_id')
            ->field('is_live,livestart,liveend,is_work_compare_activity,social_activity.url,social_activity.home_jump_url,social_activity.display_people_register,social_activity.display_work_title,social_activity.id,social_activity.remark,title,category,short_content,content,social_activity.create_at,update_at,approve_at,auth_admin.nickname as publisher,activity_result,'
                . 'stakeholder,register_numbers,register_info,status,zan_count,favor_count,browse_count,publisher_id,class_id,is_upload, 
            social_activity_class.parent_id class_parent_id,activitystart,activityend,applystart,applyend,social_activity.selectedfields,
            apply_people_number,is_pack,is_disable,is_generate,social_activity_course_grade.grade,social_activity_course_grade.course,social_activity.stakeholder,social_activity.remark,social_activity.works_show_status,social_activity.role,social_activity.upload_info,social_activity.work_extension,social_activity.additional_info')
            ->find();
        if (!empty($result)) {
            $activity_course_info = $this->getActivityCourse($id);
            $activity_course_arr = array();
            if (!empty($activity_course_info)) {
                foreach ($activity_course_info as $val) {
                    $activity_course_arr[] = $val['course'];
                }
            }
            $result['course'] = $activity_course_arr;
        }
        return $result;
    }


    /*
     * 获得某活动的关联的学科
     */
    public function getActivityCourse($activity_id)
    {
        $model = M('social_activity_course_grade');
        $where['activity_id'] = $activity_id;

        $result = $model->where($where)->find();
        if (!empty($result)) {
            $result = $model->where($where)->join("dict_course on dict_course.id=social_activity_course_grade.course", "left")
                ->join("dict_grade on dict_grade.id=social_activity_course_grade.grade", "left")->field('social_activity_course_grade.id activity_course_id,social_activity_course_grade.grade,'
                    . 'dict_grade.grade grade_name,social_activity_course_grade.course,dict_course.course_name')->select();
        } else
            return array();
        return $result;
    }

    /*
    * 活动浏览数+1
    * id 活动ID
    */
    public function setBrowseCountPlusOne($id)
    {
        $this->model->where('id=' . $id)->setInc('browse_count', 1);
    }

    /*
     * 获取活动注册信息
     *
     */
    public function getRegisterInfo($id, $userId, $role=2)
    {
        $where['activity_id'] = $id;
        $where['user_id'] = $userId;
        $where['role'] = $role;
        $result = M('social_activity_register')->join('dict_course on dict_course.id = course', 'left')->where($where)->field('social_activity_register.id regid,code,course id,course_name,social_activity_register.register_at')->find();
        return $result;
    }

    public function getRegisterDetailList($id,$schoolId=0)
    {
        $where['activity_id'] = $id;
        $result = M('social_activity_register')->join('dict_course on dict_course.id = course')
            ->join('dict_citydistrict on dict_citydistrict.id = social_activity_register.district')
            ->join('auth_teacher ON auth_teacher.id = social_activity_register.user_id')
            ->join('social_activity_works ON social_activity_works.activity_register_id = social_activity_register.id AND social_activity_works.status = ' . ACTIVITY_WORK_STATUS_VERIFIED)
            ->join('dict_grade ON dict_grade.id = social_activity_works.grade')
            ->where($where)->field('auth_teacher.id,social_activity_register.user_name,dict_course.course_name,lesson,dict_citydistrict.name district,school_name,social_activity_register.sex,age,positions,education,social_activity_register.email,school_address,post_code,tel,social_activity_register.telephone,local_course,school_course,dict_grade.grade,social_activity_works.works_name')->select();
        return $result;

    }

    public function getWorkCompareDetailList($id,$schoolId=0)
    {
        $where['activity_id'] = $id;
        $result = M('social_activity_register')->join('dict_course on dict_course.id = course')
            ->join('dict_citydistrict on dict_citydistrict.id = social_activity_register.district')
            ->join('auth_teacher ON auth_teacher.id = social_activity_register.user_id')
            ->join('social_activity_works ON social_activity_works.activity_register_id = social_activity_register.id AND social_activity_works.status = ' . ACTIVITY_WORK_STATUS_VERIFIED)
            ->join('dict_grade ON dict_grade.id = social_activity_works.grade')
            ->join('social_activity_works_file ON social_activity_works_file.activity_works_id = social_activity_works.id')
            ->where($where)->field('auth_teacher.id,social_activity_register.user_name,dict_course.course_name,lesson,dict_citydistrict.name district,school_name,social_activity_register.sex,age,positions,education,social_activity_register.email,school_address,post_code,tel,social_activity_register.telephone,local_course,school_course,dict_grade.grade,social_activity_works.works_name,' .
                'social_activity_works_file.works_file_name,social_activity_works_file.file_category,social_activity_works_file.works_file_path')->select();
        return $result;

    }

    /*
    * 获取活动是否报名
    * id 活动ID
    * user_id 用户ID
    * role 角色ID
    */
    public function getRegistered($id, $userId,$role=2)
    {
        $where['activity_id'] = $id;
        $where['user_id'] = $userId;
        $where['role'] = $role;
        $result = M('social_activity_register')->where($where)->field('register_info')->find();
        return empty($result) ? array('reged' => 'no', 'info' => '') : array('reged' => 'yes', 'info' => $result['register_info']);
    }

    /*
     * 获取活动报名用户ID数组
     *  id 活动ID
     *  role 角色类型  1 2 3
     */
    public function getRegisteredIds($id, $role)
    {
        $where['activity_id'] = $id;
        $where['user_type'] = $role - 1;
        return M('social_activity_register')->where($where)->field('user_id id')->select();
    }

    /*
    * 根据活动注册ID获取活动作品ID
    * id 活动注册ID
    */
    public function getWorkId($id)
    {
        $where['activity_register_id'] = $id;
        $result = M('social_activity_works')->where($where)->field('id')->find();
        return $result['id'];
    }

    /*
    * 获取活动是否点赞
    * id 活动ID
    * user_id 用户ID 
    */
    public function getUserIsExists($role, $userId)
    {
        $where['id'] = $userId;
        if ($role == 2) {
            $model = M('auth_teacher');
        } elseif ($role == 3) {
            $model = M('auth_student');
        } else {
            $model = M('auth_parent');
        }
        $result = $model->where($where)->field('id')->find();
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }


    /*
    * 获取活动是否点赞
    * id 活动ID
    * user_id 用户ID
    *
    */
    public function getIsZan($id, $userId, $role)
    {
        $where['social_activity_id'] = $id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role - 1;
        $result = M('social_activity_zan')->where($where)->field('id')->find();
        return empty($result) ? 'no' : 'yes';
    }

    /*
    * 获取活动是否收藏
    * id 活动ID
    * user_id 用户ID
    *
    */
    public function getIsFavor($id, $userId, $role)
    {
        $where['social_activity_id'] = $id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role - 1;
        $result = M('social_activity_favor')->where($where)->field('id')->find();
        return empty($result) ? 'no' : 'yes';
    }

    /*
      * 查看活动是否已经报名
      * id 活动ID
      * user_id 用户ID
      *
      */
    public function hasRegActivity($id, $teacherId, $role = 2)
    {
        $checkHasRegistered['user_id'] = $teacherId;
        $checkHasRegistered['activity_id'] = $id;

        $checkHasRegistered['role'] = $role;
        $existed = M('social_activity_register')->where($checkHasRegistered)->find();
        if (empty($existed))
            return false;
        return true;
    }

    /*
     * 活动报名
     * regInfo 报名信息
     *
     *
     */
    public function regActivity($regInfo, $invatation_status = 0)
    {
        $model = $this->model;
        $model->startTrans();

        $where = array(
            'id' => $regInfo['activity_id']
        );
        $activityInfo = $this->model->where($where)->field('apply_people_number allNumber,register_numbers registeredNumber')->find();
        if ($activityInfo['allNumber'] != 0 && $activityInfo['allNumber'] <= $activityInfo['registeredNumber'])
            return false;

        if (!($register_id = M('social_activity_register')->add($regInfo))) {
            return false;
        }
        if (!$model->where('id=' . $regInfo['activity_id'])->setInc('register_numbers', 1)) {
            $model->rollback();
            return false;
        }
        if ($invatation_status) {
            if (!$this->updateInvitationCodeStatus($regInfo['activity_id'], $regInfo['invitation_code'])) {
                $model->rollback();
                return false;
            }
        }
        $model->commit();
        return true;
    }


    /*
     * 修改报名信息
     */
    public function editRegActivity($regInfo, $activity_id, $user_id)
    {
        $model = M('social_activity_register');
        $where['activity_id'] = $activity_id;
        $where['user_id'] = $user_id;

        if ($model->where($where)->save($regInfo) === false) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * 验证邀请码是否正确
     *
     *
     */
    public function getVerificationCode($regData)
    {
        $where['activity_id'] = $regData['id'];
        $where['_string'] = "binary invitation_code='{$regData['code']}'";

        $lookinfo = M('social_activity_invitation_code')->where($where)->find();

        if (!empty($lookinfo)) {
            return $lookinfo;
        } else {
            return false;
        }
    }

    /* 邀请码管理列表
     *
     *
     *
    */
    public function getCodeList($where, $queryArray, $all)
    {

        $Model = M('social_activity');
        $join[] = "left JOIN social_activity_invitation_code ON social_activity_invitation_code.activity_id = social_activity.id";

        $count = $Model
            ->join($join)
            ->field("social_activity.id,social_activity.title,social_activity.is_generate,social_activity.code_num,social_activity_invitation_code.invitation_code,count(social_activity_invitation_code.id) as snum")
            ->order('social_activity.create_at desc,social_activity_invitation_code.create_at asc')
            ->group('social_activity.id')
            ->where($where)
            ->select();
        if ($all == 'all') { //导出全部
            return $count;
        }
        $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
        $Page->parameter = $queryArray;

        $count = count($count);
        $now_page = isset($_GET['p']) ? $_GET['p'] : 1;
        $count_page = ceil($count / C('PAGE_SIZE_FRONT'));
        $mo_rows = $count % C('PAGE_SIZE_FRONT');
        $page_i = ($count_page - $now_page) == 0 ? $page_i = 0 : ($count_page - $now_page);
        $total_rows = $page_i * C('PAGE_SIZE_FRONT') + ((C('PAGE_SIZE_FRONT') - (C('PAGE_SIZE_FRONT') - $mo_rows)));

        $show = $Page->show();

        $result = $Model
            ->join($join)
            ->field("social_activity.id,social_activity.title,social_activity.is_generate,social_activity.code_num,social_activity_invitation_code.invitation_code,count(social_activity_invitation_code.id) as snum,social_activity_invitation_code.id as said,is_disable")
            ->where($where)
            ->order('social_activity.create_at desc')
            ->group('social_activity.id')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $data['result'] = $result;
        $data['page'] = $show;
        $data['data_page'] = $total_rows;

        return $data;
    }

    //作废邀请码    
    public function updateInvitationCodeStatus($activity_id, $code)
    {
        $model = M('social_activity_invitation_code');
        $where['activity_id'] = $activity_id;
        $where['invitation_code'] = $code;
        $data['status'] = 2;
        if ($model->where($where)->save($data)) {
            return true;
        } else {
            return false;
        }
    }

    //根据活动id查看所有邀请码的详情
    public function getactivityCodeDetails($where)
    {
        $Model = M('social_activity_invitation_code');
        $row = $Model->where($where)->order('create_at desc')->select();
        return $row;
    }

    //删除邀请码
    public function delCode($id)
    {
        $Model = M('social_activity_invitation_code');
        $where['id'] = $id;
        $rowlist = $Model->where($where)->find();
        if ($rowlist['status'] == 1) {

            $row = $Model->where($where)->delete();
        } else {
            return 1000;
        }

        return $row;
    }

    //添加邀请码
    public function addCodeModel($code, $activity_id)
    {

        $Model = M('social_activity_invitation_code');
        $Model->startTrans();
        $data = array(
            'activity_id' => $activity_id,
            'invitation_code' => $code,
            'status' => ACTIVITY_CODE_STATUS_UNUSED,
            'create_at' => time()
        );
        $id = $Model->add($data);

        if ($id) {
            return $id;
        } else {
            $Model->rollback();
            return false;
        }

    }

    //获得某个作品详情
    public function getWorksInfo($works_id)
    {
        $where['social_activity_works.id'] = $works_id;
        //$where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $result = $this->model
            ->where($where)
            ->join("social_activity_register on social_activity_register.activity_id=" . $this->tableName . ".id")
            ->join("social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("dict_course on dict_course.id=social_activity_works.course",'left')
            ->join("dict_grade on dict_grade.id=social_activity_works.grade",'left')
            ->field("social_activity_works.id,works_name,dict_grade.grade,dict_course.course_name,"
                . "author_remarks,works_description,social_activity_works.zan_count,social_activity_works.favor_count,"
                . "social_activity_register.user_type register_role,social_activity_register.user_id,social_activity_register.role,social_activity_register.activity_id,social_activity.works_show_status")
            ->find();

        return $result;
    }


    //获得某个活动的附件
    public function getActivityFileInfo($activity_id)
    {
        $where['activity_id'] = $activity_id;
        $activit_file_model = M('social_activity_contact_file');
        $result = $activit_file_model->where($where)->select();
        return $result;
    }


    //获得某个作品的关联文件
    public function getWorksFileInfo($works_id)
    {
        $where['activity_works_id'] = $works_id;
        $works_file_model = M('social_activity_works_file');
        $result = $works_file_model->where($where)->select();
        return $result;
    }

    /*
     *获得某个作品的关联资源
     */
    public function getResourcesInfo($workeId){
        $where['activity_work_id'] = $workeId;
        $Model = M('knowledge_resource_file_contact');
         $result =  $Model->join('knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id')->field('knowledge_resource.id,file_name name ,resource_path file_path')->where($where)->select();
         return $result;
    }

    //判断某个活动作品是否收藏过
    public function getWorksIsFavor($works_id, $userId, $role)
    {
        $where['activity_works_id'] = $works_id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role;
        $result = M('social_activity_works_favor')->where($where)->field('id')->find();
        return empty($result) ? 'no' : 'yes';
    }


    //判断某个活动作品是否赞过
    public function getWorksIsZan($works_id, $userId, $role)
    {
        $where['activity_works_id'] = $works_id;
        $where['user_id'] = $userId;
        $where['user_type'] = $role;
        $result = M('social_activity_works_zan')->where($where)->field('id')->find();
        return empty($result) ? 'no' : 'yes';
    }

    //某个用户对活动点赞或取消点赞
    public function operationZanActivity($flag, $activity_id, $user_id, $role)
    {
        $model = M('social_activity_zan');
        $role = $role - 1;

        $model->startTrans();
        if ($flag == 1) {
            $add_data['social_activity_id'] = $activity_id;
            $add_data['user_id'] = $user_id;
            $add_data['user_type'] = $role;
            $add_data['zan_time'] = time();
            if (!$model->add($add_data)) {
                return false;
            }
            if (!$this->operationActivityZanNum(1, $activity_id)) {
                $model->rollback();
                return false;
            }
        } else {
            $where['social_activity_id'] = $activity_id;
            $where['user_id'] = $user_id;
            $where['user_type'] = $role;
            if (!$model->where($where)->delete()) {
                return false;
            }
            if (!$this->operationActivityZanNum(2, $activity_id)) {
                $model->rollback();
                return false;
            }
        }
        $model->commit();
        return true;
    }


    //某个用户对活动收藏或取消收藏
    public function operationFavorctivity($flag, $activity_id, $user_id, $role)
    {
        $model = M('social_activity_favor');
        $role = $role - 1;

        $model->startTrans();
        if ($flag == 1) {
            $add_data['social_activity_id'] = $activity_id;
            $add_data['user_id'] = $user_id;
            $add_data['user_type'] = $role;
            $add_data['favor_time'] = time();
            if (!$model->add($add_data)) {
                return false;
            }
            if (!$this->operationActivityFavorNum(1, $activity_id)) {
                $model->rollback();
                return false;
            }
        } else {
            $where['social_activity_id'] = $activity_id;
            $where['user_id'] = $user_id;
            $where['user_type'] = $role;
            if (!$model->where($where)->delete()) {
                return false;
            }
            if (!$this->operationActivityFavorNum(2, $activity_id)) {
                $model->rollback();
                return false;
            }
        }
        $model->commit();
        return true;
    }


    //对某个活动点赞数量加一或减一
    public function operationActivityZanNum($flag, $activity_id)
    {
        $model = M('social_activity');
        $where['id'] = $activity_id;
        if ($flag == 1) {
            if ($model->where($where)->setInc('zan_count', 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($model->where($where)->setDec('zan_count', 1)) {
                return true;
            } else {
                return false;
            }
        }
    }


    //对某个活动收藏数量加一或减一
    public function operationActivityFavorNum($flag, $works_id)
    {
        $model = M('social_activity');
        $where['id'] = $works_id;
        if ($flag == 1) {
            if ($model->where($where)->setInc('favor_count', 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($model->where($where)->setDec('favor_count', 1)) {
                return true;
            } else {
                return false;
            }
        }
    }


    //某个用户对作品进行点赞或取消点赞
    public function operationZanWorks($flag, $works_id, $user_id, $role)
    {
        $model = M('social_activity_works_zan');

        $model->startTrans();
        if ($flag == 1) {
            $add_data['activity_works_id'] = $works_id;
            $add_data['user_id'] = $user_id;
            $add_data['user_type'] = $role;
            $add_data['favor_time'] = time();
            if (!$model->add($add_data)) {
                return false;
            }
            if (!$this->operationWorksZanNum(1, $works_id)) {
                $model->rollback();
                return false;
            }
        } else {
            $where['activity_works_id'] = $works_id;
            $where['user_id'] = $user_id;
            $where['user_type'] = $role;
            if (!$model->where($where)->delete()) {
                return false;
            }
            if (!$this->operationWorksZanNum(2, $works_id)) {
                $model->rollback();
                return false;
            }
        }
        $model->commit();
        return true;
    }


    //某个用户进行收藏或取消收藏
    public function operationFavorWorks($flag, $works_id, $user_id, $role)
    {
        $model = M('social_activity_works_favor');
        if ($flag == 1) {
            $add_data['activity_works_id'] = $works_id;
            $add_data['user_id'] = $user_id;
            $add_data['user_type'] = $role;
            $add_data['favor_time'] = time();
            if (!$model->add($add_data)) {
                return false;
            }
            if (!$this->operationWroksFavorNum(1, $works_id)) {
                $model->rollback();
                return false;
            }
        } else {
            $where['activity_works_id'] = $works_id;
            $where['user_id'] = $user_id;
            $where['user_type'] = $role;
            if (!$model->where($where)->delete()) {
                return false;
            }
            if (!$this->operationWroksFavorNum(2, $works_id)) {
                $model->rollback();
                return false;
            }
        }
        $model->commit();
        return true;
    }


    //对某个作品点赞数量加一或减一
    public function operationWorksZanNum($flag, $works_id)
    {
        $model = M('social_activity_works');
        $where['id'] = $works_id;
        if ($flag == 1) {
            if ($model->where($where)->setInc('zan_count', 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($model->where($where)->setDec('zan_count', 1)) {
                return true;
            } else {
                return false;
            }
        }
    }


    //对某个作品收藏数量加一或减一
    public function operationWroksFavorNum($flag, $works_id)
    {
        $model = M('social_activity_works');
        $where['id'] = $works_id;
        if ($flag == 1) {
            if ($model->where($where)->setInc('favor_count', 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($model->where($where)->setDec('favor_count', 1)) {
                return true;
            } else {
                return false;
            }
        }
    }

    //对某个作品的浏览量加一
    public function addBrowseCount($works_id)
    {
        $model = M('social_activity_works');
        $where['id'] = $works_id;
        $model->where($where)->setInc('browse_number', 1);
    }


    //对某个学校进行搜索
    public function searchSchool($keyword)
    {
        $model = M('dict_schoollist');
        $where[] = array('school_name' => array('like', '%' . $keyword . '%'));
        $result = $model->where($where)->field('id,school_name')->order('id desc')->limit(10)->select();
        return $result;
    }

    //获得某一个用户对某个活动的报名信息
    public function getActivityRegistration($activity_id, $user_id,$role=2)
    {
        $where['activity_id'] = $activity_id;
        $where['user_id'] = $user_id;
        $where['social_activity_register.role'] = $role;
        $model = M('social_activity_register');
        $result = $model
            ->where($where)
            ->join('social_activity on social_activity.id=social_activity_register.activity_id','left')
            ->join('dict_citydistrict province on province.id=province','left')
            ->join('dict_citydistrict city on city.id=city','left')
            ->join('dict_citydistrict district on district.id=district','left')
            ->join('dict_course on dict_course.id=social_activity_register.course','left')
            ->field('is_disable,is_generate,social_activity_register.id,activity_id,social_activity.applystart,applyend,invitation_code,user_name,dict_course.id course_id,dict_course.course_name,lesson,province.id province_id,'
                . 'province.name province,city.id city_id,city.name city,district.id district_id,district.name district,'
                . 'sex,age,education,positions,email,school_name,social_activity_register.school_address,post_code,tel,telephone,local_course,school_course,social_activity_register.additional_info')
            ->find();
        return $result;
    }

    //获得教师学校等信息
    public function getTeacherSchoolInfo($teacher_id)
    {
        $model = M('auth_teacher');
        $where['auth_teacher.id'] = $teacher_id;
        $result = $model->where($where)->join("dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
            ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
            ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
            ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')
            ->field('auth_teacher.name name,sex,telephone,email,dict_schoollist.id school_id,dict_schoollist.school_name,dict_schoollist.school_address,'
                . 'province.id province_id,province.name province,'
                . 'city.id city_id,city.name city,district.id district_id,district.name district')->find();
        return $result;
    }

   //获得学生学校等信息
    public function getStudentSchoolInfo($studentId)
    {
        $model = M('auth_student');
        $where['auth_student.id'] = $studentId;
        $result = $model->where($where)->join("dict_schoollist on dict_schoollist.id=auth_student.school_id")
            ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
            ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
            ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')
            ->field('auth_student.student_name name,sex,parent_tel telephone,email,dict_schoollist.id school_id,dict_schoollist.school_name,dict_schoollist.school_address,'
                . 'province.id province_id,province.name province,'
                . 'city.id city_id,city.name city,district.id district_id,district.name district')->find();
        return $result;
    }
    public function getLastRegInfo($userId,$role,$user_type)
    {
        $regModel = M('social_activity_register');
        $where['user_id'] = $userId;
        $where['role'] = $role;
        $where['user_type'] = $user_type;
        $result = $regModel->where($where)->field('lesson,province,city,district,sex,age,positions,education,post_code,tel,local_course,school_course')->order('register_at desc')->find();
        return $result;
    }

    //根据活动学科与教师学科获取教师可以报名的学科
    public function getAvailableRegCourse($teacher_id, $activity_id)
    {
        $teacherCourseModel = M('auth_teacher_second');
        $activityCourseModel = M('social_activity_course_grade');
        $where['auth_teacher_second.teacher_id'] = $teacher_id;
        $join = array();
        $activityCourseList = $activityCourseModel->where('activity_id=' . $activity_id)->field('course')->find();
        if ($activityCourseList['course'] == 0) //全学科
        {
            $join = array();
        } else {
            $join[] = 'social_activity_course_grade ON social_activity_course_grade.course = auth_teacher_second.course_id';
            $where['social_activity_course_grade.activity_id'] = $activity_id;
        }
        $join[] = 'dict_course ON auth_teacher_second.course_id = dict_course.id';
        $result = $teacherCourseModel->where($where)->join($join)->group('dict_course.id')->field('dict_course.id,dict_course.id course_id,dict_course.course_name,dict_course.code')->select();
        return $result;
    }

    public function getAvailableRegGrade($teacher_id, $activity_id)
    {
        $teacherCourseModel = M('auth_teacher_second');
        $activityCourseModel = M('social_activity_course_grade');
        $where['auth_teacher_second.teacher_id'] = $teacher_id;

        $activityGradeList = $activityCourseModel->where('activity_id=' . $activity_id)->field('grade')->find();
        if ($activityGradeList['grade'] == 0) //全年级
        {
            $join = array();
        } else {
            $join[] = 'social_activity_course_grade ON social_activity_course_grade.grade = auth_teacher_second.grade_id';
            $where['social_activity_course_grade.activity_id'] = $activity_id;
        }
        $regWhere['activity_id'] = $activity_id;
        $regWhere['user_id'] = $teacher_id;
        $regWhere['user_type'] = 1; //teacher
        $regCourse = M('social_activity_register')->where($regWhere)->field('course')->find();
        $regCourse = $regCourse['course'];
        $join[] = 'dict_grade ON auth_teacher_second.grade_id = dict_grade.id';

        if(!empty($regCourse))
        $where['auth_teacher_second.course_id'] = $regCourse;

        return $teacherCourseModel->where($where)->join($join)->group('dict_grade.id')->field('dict_grade.id grade,dict_grade.grade grade_name')->select();
    }

    //获得教师的学科信息
    public function getTeacherAllCourse($teacher_id)
    {
        $model = M('auth_teacher');
        $where['auth_teacher.id'] = $teacher_id;
        $result = $model->where($where)->join("auth_teacher_second second on second.teacher_id=auth_teacher.id")
            ->join('dict_course on dict_course.id=second.course_id')
            ->field('dict_course.id course_id,dict_course.course_name')->group('dict_course.id')->select();
        return $result;
    }

    public function getWorkActivityByPeriodCourseGradeCategory($period = 0, $course = 0, $grade = 0, $category = 0, $keyword = '', $pageIndex = 1, $pageSize = 20, &$count, $role = 0, $userId = 0, $myCategory = 0)
    {
        $where = array();
        $role = $role - 1;
        switch ($myCategory) {
            case 1:
                $join = "social_activity_favor ON social_activity.id = social_activity_favor.social_activity_id AND social_activity_favor.user_id = $userId AND social_activity_favor.user_type=$role";
                break;
            case 2:
                $join = "social_activity_register ON social_activity.id = social_activity_register.activity_id AND social_activity_register.user_id = $userId AND social_activity_register.user_type=$role";
                break;
            default:
                $join = '';
                break;
        }
        if ($grade != 0) {
            $where['grade'] = array('in', array(0, $grade));
        } else if ($period != 0) {
            switch ($period) {
                case 1: //小学全年级
                    $where['grade'] = array('in', array(0, 1, 2, 3, 4, 5, 6));
                    break;
                case 2: //初中全年级
                    $where['grade'] = array('in', array(0, 7, 8, 9));
                    break;
                case 3: //高中全年级
                    $where['grade'] = array('in', array(0, 10, 11, 12));
                    break;
                default:
                    break;
            }
        }

        if ($course != 0) {
            $where['course'] = array('in', array(0, $course));
        }

        $activityList = M('social_activity_course_grade')->where($where)->field('distinct activity_id as id')->select();
        $activityList = array_column($activityList, 'id');

        //empty judgement
        if (empty($activityList))
            $activityWhere['social_activity.id'] = array('in', array(0));
        else
            $activityWhere['social_activity.id'] = array('in', $activityList);
        if ($category != 0) {
            $activityWhere['social_activity.class_id'] = $category;
        } else {
            $activityWhere['social_activity.class_id'] = array('in', array(6, 7, 8, 9, 10));
        }
        $activityWhere['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        if ($keyword != '')
            $activityWhere['social_activity.title'] = array('like', '%' . $keyword . '%');
        $count = $this->model->join($join)->where($activityWhere)->field('count(1) as num')->select();
        $count = $count['num'];
        return $this->model->join($join)->where($activityWhere)->order('approve_at desc')->page($pageIndex . ',' . $pageSize)->field('social_activity.*,if(UNIX_TIMESTAMP(NOW())-604800>social_activity.approve_at,\'no\',\'yes\') is_new')->select();
    }

    //获取是否已经上传作品
    public function getHasUploadWorks($activity_id, $userid, $role = 2)
    {
        $regWhere['activity_id'] = $activity_id;
        $regWhere['user_id'] = $userid;
        $regWhere['role'] = $role;
        $result = M('social_activity_register')->where($regWhere)->field('id')->find();
        if (empty($result)) //教师未注册活动
            return false;
        else {
            $regId = $result['id'];
            $workWhere['activity_register_id'] = $regId;
            $result = M('social_activity_works')->where($workWhere)->field('id,status,error_data')->find();
            return $result;
        }
    }

    //用户已经报名了，但是没上传作品
    public function registered_but_no_uploadworks($userid, $role,$is_app=0)
    {
        //查看该用户是否有报名活动
        $time = time()+60;
        $where['social_activity_register.role'] = $role;
        $where['social_activity_register.user_id'] = $userid;
        $where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $where['social_activity.applyend'] = array('gt',$time);
        $where['social_activity.is_upload'] = 1;
        $hasRegisterActivity = M('social_activity_register')
            ->join('social_activity ON social_activity.id = social_activity_register.activity_id')
            ->where($where)
            ->find();
        if(empty($hasRegisterActivity))
            return false;


        $additionalWhere = '';
        if($is_app == 1){
            $additionalWhere = ' AND enable_app_upload = 1';
        }
        $result = M('social_activity_register')
            ->join('social_activity ON social_activity.id = social_activity_register.activity_id')
            ->join('social_activity_works ON social_activity_works.activity_register_id = social_activity_register.id','left')
            ->where("social_activity_register.role = $role and social_activity.is_upload = 1 and social_activity.status =". ACTIVITY_STATUS_PUBLISHED ." and social_activity.applyend > $time and user_id = $userid and social_activity_works.id is null" . $additionalWhere)
            ->field('social_activity_register.id,social_activity_register.activity_id,social_activity.applyend')
            ->order('applyend')
            ->find();
        /*$id = array_column($result,'id');
        $activity_id = array_column($result,'activity_id');
        $newarray = array_combine($id,$activity_id);*/
       /* foreach ($id as $item){
            $workWhere['activity_register_id'] = $item;
            if(M('social_activity_works')->where($workWhere)->field('id')->find() == true){
                unset($newarray[$item]);
            }
        }*/
        return $result;
    }
    private function __nullFilter($data){
        return $data === null ? '':$data;
    }
    public function saveWorkInfo($isAdd, $regId, $data, $workData)
    {
        $workModel = M('social_activity_works');
        $workFileModel = M('social_activity_works_file');
        $workModel->startTrans();
        $workFileModel->startTrans();
        $where['activity_register_id'] = $regId;
        $result = $workModel->where($where)->find();
        if ($isAdd) {
            if (!empty($result))  //record already exists
                return false;
            $data['activity_register_id'] = $regId;
            $workId = $workModel->add($data);
        } else //edit
        {
            if (empty($result)) {
                $workModel->rollback();
                return false;
            } else {
                if (!$workModel->where('id=' . $result['id'])->save($data)) {
                    $workModel->rollback();
                    return false;
                }
                $workId = $result['id'];
                $workWhere['activity_works_id'] = $workId;
                $workFileModel->where($workWhere)->delete();
            }
        }

        if ($workId) {
            for ($i = 0; $i < count($workData['file_path']); $i++) {
                $addData['activity_works_id'] = $workId;
                $addData['works_file_path'] = $this->__nullFilter($workData['file_path'][$i]);
                $addData['vid_image_path'] = $this->__nullFilter($workData['image_path'][$i]);
                $addData['is_transition'] = $this->__nullFilter($workData['is_transition'][$i]);
                $addData['file_category'] = $this->__nullFilter($workData['file_category'][$i]);
                $addData['vid'] = $this->__nullFilter($workData['vid'][$i]);
                $addData['vid_fullpath'] = $this->__nullFilter($workData['vid_fullpath'][$i]);
                $addData['create_at'] = time();
                $addData['works_file_name'] = $this->__nullFilter($workData['file_name'][$i]);
                $addData['type'] = $workData['type'][$i];
                if (!$workFileModel->add($addData)) {
                    $workModel->rollback();
                    $workFileModel->rollback();
                    return false;
                }
            }
            $workModel->commit();
            $workFileModel->commit();
        } else {
            $workModel->rollback();
            return false;
        }
        return true;
    }

    public function getWorkActivityByPeriodCourseGradeCategoryBrief($period = 0, $course = 0, $grade = 0, $category = 0, $keyword = '', $role = 0, $userId = 0, $myCategory = 0)
    {
        $where = array();
        $role = $role - 1;
        switch ($myCategory) {
            case 1:
                $join = "social_activity_favor ON social_activity.id = social_activity_favor.social_activity_id AND social_activity_favor.user_id = $userId AND social_activity_favor.user_type=$role";
                break;
            case 2:
                $join = "social_activity_register ON social_activity.id = social_activity_register.activity_id AND social_activity_register.user_id = $userId AND social_activity_register.user_type=$role";
                break;
            default:
                $join = '';
                break;
        }
        if ($grade != 0) {
            $where['grade'] = array('in', array(0, $grade));
        } else if ($period != 0) {
            switch ($period) {
                case 1: //小学全年级
                    $where['grade'] = array('in', array(0, 1, 2, 3, 4, 5, 6));
                    break;
                case 2: //初中全年级
                    $where['grade'] = array('in', array(0, 7, 8, 9));
                    break;
                case 3: //高中全年级
                    $where['grade'] = array('in', array(0, 10, 11, 12));
                    break;
                default:
                    break;
            }
        }

        if ($course != 0) {
            $where['course'] = array('in', array(0, $course));
        }

        $activityList = M('social_activity_course_grade')->where($where)->field('distinct activity_id as id')->select();
        $activityList = array_column($activityList, 'id');

        //empty judgement
        if (empty($activityList))
            $activityWhere['social_activity.id'] = array('in', array(0));
        else
            $activityWhere['social_activity.id'] = array('in', $activityList);
        if ($category != 0) {
            $activityWhere['social_activity.class_id'] = $category;
        } else {
            $activityWhere['social_activity.class_id'] = array('in', array(6, 7, 8, 9, 10));
        }
        $activityWhere['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $activityWhere['social_activity.applystart'] = array('lt', time());
        if ($keyword != '')
            $activityWhere['social_activity.title'] = array('like', '%' . $keyword . '%');
        return $this->model->join($join)->where($activityWhere)->order('startat asc')->field('social_activity.id,social_activity.title,FROM_UNIXTIME(social_activity.activitystart,\'%Y-%m-%d %H:%I:%S\') startat,if(UNIX_TIMESTAMP(NOW())-604800>social_activity.approve_at,\'no\',\'yes\') is_new')->select();
    }

    public function saveActivityInfo($id, $data)
    {
        return $this->model->where('id=' . $id)->save($data);
    }

    public function getColumnContentList($id,$additionalWhere = array(),$having = '')
    {
        $where['column_id'] = $id;
        if(!empty($additionalWhere))
            $where[] = $additionalWhere;
        $result = M('activity_column_contact')->where($where)
            ->join('social_activity ON social_activity.id = activity_column_contact.content_id AND activity_column_contact.content_type=' . COLUMN_CONTENTTYPE_ACTIVITY, 'left')
            ->join('social_expert_information ON social_expert_information.id = activity_column_contact.content_id AND activity_column_contact.content_type=' . COLUMN_CONTENTTYPE_INFORMATION, 'left')
            ->join('activity_vote ON activity_vote.id = activity_column_contact.content_id AND activity_column_contact.content_type=' . COLUMN_CONTENTTYPE_VOTE, 'left')
            ->field('activity_column_contact.id,activity_column_contact.content_type,activity_column_contact.content_id,activity_column_contact.title,activity_column_contact.status,activity_column_contact.sort,' .
                '(case when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_ACTIVITY . ' then social_activity.create_at ' .
                'when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_INFORMATION . ' then social_expert_information.create_at ' .
                'when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_VOTE . ' then activity_vote.create_at end) create_at,' .

                '(case when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_ACTIVITY . ' then social_activity.activitystart ' .
                'when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_VOTE . ' then activity_vote.votestart end) start_at,' .

                '(case when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_ACTIVITY . ' then social_activity.activityend ' .
                'when activity_column_contact.content_type =' . COLUMN_CONTENTTYPE_VOTE . ' then activity_vote.voteend end) end_at'
            )
            ->having($having)
            ->select();
        return $result;
    }

    public function getColumnContent($id, $contentType)
    {
        $where['content_type'] = $contentType;
        $where['content_id'] = $id;
        return M('activity_column_contact')->where($where)->find();

    }

    public function addColumnContent($resourceType, $id, $columnId, $title)
    {
        $model = M('activity_column_contact');
        $maxSort = M('activity_column_contact')->where('column_id=' . $columnId)->order('sort desc')->field('sort')->find();
        $maxSort = $maxSort['sort'];
        if (empty($maxSort))
            $maxSort = 0;
        $data['column_id'] = $columnId;
        $data['content_type'] = $resourceType;
        $data['content_id'] = $id;
        $data['title'] = $title;
        $data['status'] = COLUMN_CONTENTSTATUS_OFFLINE;
        $data['sort'] = $maxSort + 1;
        return $model->add($data);
    }

    public function editColumnContent($id, $title)
    {
        $where['id'] = $id;
        $data['title'] = $title;
        $data['status'] = COLUMN_CONTENTSTATUS_OFFLINE;
        return M('activity_column_contact')->where($where)->save($data);
    }

    public function deleteColumnContent($id, $columnId)
    {
        $where['column_id'] = $columnId;
        $where['id'] = $id;
        $result = M('activity_column_contact')->where($where)->delete();
        if ($result === false)
            return false;
        else
            return true;
    }

    public function upDownColumnContent($id, $columnId, $status, &$errorInfo)
    {
        $where['column_id'] = $columnId;
        $where['id'] = $id;
        $data['status'] = $status;
        $result = M('activity_column_contact')->where($where)->save($data);
        if ($result === false)
            return false;
        else
            return true;
    }

    public function saveColumnSort($ids, $values)
    {
        $Model = M('activity_column_contact');
        $Model->startTrans();
        for ($i = 0; $i < sizeof($ids); $i++) {
            $sql = 'update activity_column_contact set sort=' . $values[$i] . ' WHERE id=' . $ids[$i];
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                return false;
            }
        }

        $Model->commit();
        return true;

    }

    public function getFilteredSpecialColumn($status, $keyword)
    {
        $where = array();
        if(!empty($status))
            $where['flag'] = $status;
        if(!empty($keyword))
            $where['name'] = array('like',"%$keyword%");
        return M('activity_specialcolumn_type')->where($where)->select();
    }

    public function addSpecialColumn($name,&$errorInfo)
    {
        $model = M('activity_specialcolumn_type');
        if($model->where('name=\''.$name. '\'')->find())
        {
            $errorInfo = '该专栏已经存在';
            return false;
        }
        else
        {
            $maxSort = M('activity_specialcolumn_type')->order('sort desc')->field('sort')->find();
            $maxSort = $maxSort['sort'];
            $data['name'] = $name;
            $data['flag'] = 1;
            $data['create_at'] = time();
            $data['sort'] = $maxSort + 1;
            $result = $model->add($data);
            if(!$result)
            {
                $errorInfo = '添加失败';
                return false;
            }
            return $result;
        }
    }
    public function setSpecialColumnName($id,$name)
    {
        $where['id'] = $id;
        $data['name'] = $name;
        $result = M('activity_specialcolumn_type')->where($where)->save($data);
        if ($result === false)
            return false;
        else
            return true;
    }
    public function setSpecialColumnStatus($id,$status)
    {
        $where['id'] = $id;
        $data['flag'] = $status;
        $result = M('activity_specialcolumn_type')->where($where)->save($data);
        if ($result === false)
            return false;
        else
            return true;
    }

    public function getSpecialColumnContent($id, $columnId)
    {
        $where['specialcolumn_id'] = $columnId;
        $where['content_id'] = $id;
        return M('activity_specialcolumn_contact')->where($where)->find();

    }

    public function getSpecialColumnData($id)
    {
        $where['id'] = $id;
        return M('activity_specialcolumn_type')->where($where)->find();
    }

    public function getSpecialColumnContentList($id)
    {
        $where['specialcolumn_id'] = $id;
        return M('activity_specialcolumn_contact')->where($where)
            ->join('social_activity ON social_activity.id = activity_specialcolumn_contact.content_id AND activity_specialcolumn_contact.content_type=' . COLUMN_CONTENTTYPE_ACTIVITY, 'left')
            ->join('social_expert_information ON social_expert_information.id = activity_specialcolumn_contact.content_id AND activity_specialcolumn_contact.content_type=' . COLUMN_CONTENTTYPE_INFORMATION, 'left')
            ->join('activity_vote ON activity_vote.id = activity_specialcolumn_contact.content_id AND activity_specialcolumn_contact.content_type=' . COLUMN_CONTENTTYPE_VOTE, 'left')
            ->field('activity_specialcolumn_contact.id,activity_specialcolumn_contact.content_type,activity_specialcolumn_contact.content_id,activity_specialcolumn_contact.title,activity_specialcolumn_contact.status,activity_specialcolumn_contact.sort,'.
                '(case when activity_specialcolumn_contact.content_type =' . COLUMN_CONTENTTYPE_ACTIVITY . ' then social_activity.create_at ' .
                'when activity_specialcolumn_contact.content_type =' . COLUMN_CONTENTTYPE_INFORMATION . ' then social_expert_information.create_at ' .
                'when activity_specialcolumn_contact.content_type =' . COLUMN_CONTENTTYPE_VOTE . ' then activity_vote.create_at end) create_at')
            ->select();
    }


    public function addSpecialColumnContent($resourceType, $id, $columnId, $title)
    {
        $model = M('activity_specialcolumn_contact');
        $maxSort = M('activity_specialcolumn_contact')->where('specialcolumn_id=' . $columnId)->order('sort desc')->field('sort')->find();
        $maxSort = $maxSort['sort'];
        if (empty($maxSort))
            $maxSort = 0;
        $data['specialcolumn_id'] = $columnId;
        $data['content_type'] = $resourceType;
        $data['content_id'] = $id;
        $data['title'] = $title;
        $data['status'] = COLUMN_CONTENTSTATUS_OFFLINE;
        $data['flag'] = COLUMN_CONTENTFLAG_UNAUTH;
        $data['sort'] = $maxSort + 1;
        return $model->add($data);
    }

    public function editSpecialColumnContent($id, $title)
    {
        $where['id'] = $id;
        $data['title'] = $title;
        $data['status'] = COLUMN_CONTENTSTATUS_OFFLINE;
        return M('activity_specialcolumn_contact')->where($where)->save($data);
    }

    public function deleteSpecialColumnContent($id)
    {

        $where['id'] = $id;
        $result = M('activity_specialcolumn_contact')->where($where)->delete();
        if ($result === false)
            return false;
        else
            return true;
    }

    public function upDownSpecialColumnContent($id, $columnId, $status, &$errorInfo)
    {
        $where['specialcolumn_id'] = $columnId;
        $where['id'] = $id;
        $data['status'] = $status;
        $result = M('activity_specialcolumn_contact')->where($where)->save($data);
        if ($result === false)
            return false;
        else
            return true;
    }

    public function saveSpecialColumnSort($ids, $values)
    {
        $Model = M('activity_specialcolumn_type');
        $Model->startTrans();
        for ($i = 0; $i < sizeof($ids); $i++) {
            $sql = 'update activity_specialcolumn_type set sort=' . $values[$i] . ' WHERE id=' . $ids[$i];
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                return false;
            }
        }

        $Model->commit();
        return true;

    }

    public function saveSpecialColumnContentSort($ids, $values)
    {
        $Model = M('activity_specialcolumn_contact');
        $Model->startTrans();
        for ($i = 0; $i < sizeof($ids); $i++) {
            $sql = 'update activity_specialcolumn_contact set sort=' . $values[$i] . ' WHERE id=' . $ids[$i];
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                return false;
            }
        }

        $Model->commit();
        return true;

    }

    public function deleteSpecialColumn($id)
    {
        $where['id'] = $id;
        $result = M('activity_specialcolumn_type')->where($where)->delete();
        if ($result === false)
            return false;
        else
            return true;
    }
    public function getSpecialColumnHasJoinActivity($columnId)
    {
        $where = array();
        $where['specialcolumn_id'] = $columnId;
        $where['content_type'] =  COLUMN_CONTENTTYPE_ACTIVITY;
        $result = M('activity_specialcolumn_contact')->where($where)->field('1')->find();
        return !empty($result);
    }

    public function getSpecialColumnHasJoinPublishActivity($columnId)
    {
        $where = array();
        $where['activity_specialcolumn_type.id'] = $columnId;
        $where['activity_specialcolumn_contact.status'] = 1;
        $where['activity_specialcolumn_contact.content_type'] = COLUMN_CONTENTTYPE_ACTIVITY;
        $where['social_activity.status'] = ACTIVITY_STATUS_PUBLISHED;
        $result = M('activity_specialcolumn_contact')->where($where)
            ->join('activity_specialcolumn_type ON activity_specialcolumn_type.id = activity_specialcolumn_contact.specialcolumn_id')
            ->join('social_activity ON activity_specialcolumn_contact.content_id = social_activity.id')
            ->field('1')
            ->find();
        return !empty($result);
    }
    /*
     *
     *获取获得活动首页专栏
     */
    public  function get_column_resource($colunm_id,$fromApp = 0){
        $activity_specialcolumn_type = M('activity_specialcolumn_type');
        if(0 == $fromApp) {
            $field = 'activity_specialcolumn_type.id,social_expert_information.type,activity_specialcolumn_type.`name`,activity_specialcolumn_contact.content_type,' .
                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.id ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.id end) ids,' .
                'activity_specialcolumn_contact.title';
            $having = 'ids is not null';
        }
        else
        {
            $field = 'activity_specialcolumn_type.name,activity_specialcolumn_contact.content_type,' .
                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.id ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.id end) id,' .

                '(case when activity_specialcolumn_contact.content_type = 1 then \''.ICON_ACTIVITY .'\' ' .
                'when activity_specialcolumn_contact.content_type = 2 then \''.ICON_INFOMATION .'\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then \''.ICON_VOTE .'\'   end) icon_url,' .


                '(case when activity_specialcolumn_contact.content_type = 1 then (case when unix_timestamp(now()) >= social_activity.applystart AND unix_timestamp(now()) <= social_activity.applyend then \'报名\' else \'活动\'end) ' .
                'when activity_specialcolumn_contact.content_type = 2 then \'资讯\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then \'投票\' end) name,' .

                'activity_specialcolumn_contact.title,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'参与对象: \', social_activity.stakeholder) ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.content ' .
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'投票时间: \', activity_vote.votedisplay) end) content1,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'活动时间: \' , social_activity.display_activity_startendtime) ' .
                'when activity_specialcolumn_contact.content_type = 2 then \'\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'投票规则: \' ,activity_vote.description) end) content2,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityDetails?id=\' ,social_activity.id) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) url,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].ACTIVITY_LOCAL_DIR.'\' ,  social_activity.short_content) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].EXPERTINFO_LOCAL_DIR.'\' ,  social_expert_information.short_content) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\''.C('oss_path').'\' ,  activity_vote.img_path)  end) img_url,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityShare?id=\' ,social_activity.id) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) share_url'

            ; //content1 content2 url img_url
            $having = 'id is not null';

        }
        $result = $this->getSpecialColumnHasJoinPublishActivity($colunm_id);
        if(false === $result)
         return array();
        else
        {
            $sql = $activity_specialcolumn_type
                ->join('activity_specialcolumn_contact ON activity_specialcolumn_type.id = activity_specialcolumn_contact.specialcolumn_id', 'left')
                ->join('social_activity ON activity_specialcolumn_contact.content_id = social_activity.id AND social_activity.status =' . ACTIVITY_STATUS_PUBLISHED, 'left')
                ->join('activity_vote ON activity_specialcolumn_contact.content_id = activity_vote.id AND activity_vote.flag =' . VOTE_FLAG_ONLINE, 'left')
                ->join('social_expert_information ON activity_specialcolumn_contact.content_id = social_expert_information.id AND social_expert_information.status=' . EXPERTINFO_STATUS_ONLINE, 'left')
                ->field($field)
                ->having($having)
                ->where("activity_specialcolumn_contact.status=1 and activity_specialcolumn_type.flag=4 and activity_specialcolumn_type.id = $colunm_id")
                ->order('activity_specialcolumn_contact.sort')
                ->limit('6')
                ->select();
            return $sql;
        }
    }

    /*
     * 所有上架专栏
     */
    public  function get_column(){
        $activity_specialcolumn_type = M('activity_specialcolumn_type');
        $resources = $activity_specialcolumn_type
        ->field('id,name')
            ->where('flag = 4')
            ->order('sort')
            ->select();
        return $resources;
    }

    /*
     *专栏中的更多进入专栏列表页
     */
    public function get_column_more($colunm_id,$pageSize = 0,$fromAPP){
        if(0 == $pageSize)
            $pageSize = C('PAGE_SIZE_FRONT');
        $activity_specialcolumn_type = M('activity_specialcolumn_type');
        if(0 == $fromAPP)
        {
            $field = 'activity_specialcolumn_type.id,activity_specialcolumn_type.`name`,activity_specialcolumn_contact.content_type,' .
                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.id ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.id end) ids,'.
                'activity_specialcolumn_contact.title,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.short_content end) img,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.display_activity_startendtime ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.create_at ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.votedisplay end) time,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.content ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.content ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.description end) content';
            $having = 'ids is not null';
        }
        else
        {
            $field = 'activity_specialcolumn_type.name,activity_specialcolumn_contact.content_type,' .
                '(case when activity_specialcolumn_contact.content_type = 1 then social_activity.id ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_specialcolumn_contact.content_type = 3 then activity_vote.id end) id,' .

                '(case when activity_specialcolumn_contact.content_type = 1 then \''.ICON_ACTIVITY .'\' ' .
                'when activity_specialcolumn_contact.content_type = 2 then \''.ICON_INFOMATION .'\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then \''.ICON_VOTE .'\'   end) icon_url,' .


                '(case when activity_specialcolumn_contact.content_type = 1 then (case when unix_timestamp(now()) >= social_activity.applystart AND unix_timestamp(now()) <= social_activity.applyend then \'报名\' else \'活动\'end) ' .
                'when activity_specialcolumn_contact.content_type = 2 then \'资讯\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then \'投票\' end) name,' .

                'activity_specialcolumn_contact.title,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'参与对象: \', social_activity.stakeholder) ' .
                'when activity_specialcolumn_contact.content_type = 2 then social_expert_information.content ' .
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'投票时间: \', activity_vote.votedisplay) end) content1,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'活动时间: \' , social_activity.display_activity_startendtime) ' .
                'when activity_specialcolumn_contact.content_type = 2 then \'\' ' .
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'投票规则: \' ,activity_vote.description) end) content2,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityDetails?id=\' ,social_activity.id) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) url,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].ACTIVITY_LOCAL_DIR.'\' ,  social_activity.short_content) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].EXPERTINFO_LOCAL_DIR.'\' ,  social_expert_information.short_content) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\''.C('oss_path').'\' ,  activity_vote.img_path)  end) img_url,'.

                '(case when activity_specialcolumn_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityShare?id=\' ,social_activity.id) '.
                'when activity_specialcolumn_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
                'when activity_specialcolumn_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) share_url'

            ;
            $having = 'id is not null';
        }


        $sql = $activity_specialcolumn_type
            ->join('activity_specialcolumn_contact ON activity_specialcolumn_type.id = activity_specialcolumn_contact.specialcolumn_id', 'left')
            ->join('social_activity ON activity_specialcolumn_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_specialcolumn_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join('social_expert_information ON activity_specialcolumn_contact.content_id = social_expert_information.id AND social_expert_information.status='. EXPERTINFO_STATUS_ONLINE,'left')
            ->field($field)
            ->having($having)
            ->where("activity_specialcolumn_type.id = $colunm_id")
            ->select();

        $count = count($sql);
        $Page = new \Think\Page(count($count), $pageSize);
        $show = $Page->show();
        $resources = $activity_specialcolumn_type
            ->join('activity_specialcolumn_contact ON activity_specialcolumn_type.id = activity_specialcolumn_contact.specialcolumn_id', 'left')
            ->join('social_activity ON activity_specialcolumn_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_specialcolumn_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join('social_expert_information ON activity_specialcolumn_contact.content_id = social_expert_information.id AND social_expert_information.status='. EXPERTINFO_STATUS_ONLINE,'left')
            ->field($field)
            ->where("activity_specialcolumn_type.id = $colunm_id")
            ->having($having)
            ->order('activity_specialcolumn_contact.sort')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $data['page'] = $show;
        $data['list'] = $resources;
        return $data;
    }

    /*
     *栏目详情
     */
    public function column_details($column_id){
        $status = M('dict_column')->field('is_display')->where("id = $column_id")->find();
        return $status;
    }

    /*
     *首页展示活动列表
     */
    public function get_index_list($limits='',$column_id,$queryArray='',$wheres=array(),$fromAPP = 0){
        $activity_specialcolumn_type = M('activity_column_contact');
        $where['activity_column_contact.status']=1;
        $where['activity_column_contact.column_id']= $column_id;
        $where += $wheres;
        $join = 'left join social_expert_information ON activity_column_contact.content_id = social_expert_information.id AND social_expert_information.status='. EXPERTINFO_STATUS_ONLINE;
        if($column_id == 4){
        $field ='activitystart,activityend,is_live,livestart,liveend,activity_column_contact.content_type,'.
            '(case when activity_column_contact.content_type = 1 then social_activity.id ' .
            'when activity_column_contact.content_type = 2 then social_expert_information.id ' .
            'when activity_column_contact.content_type = 3 then activity_vote.id end) id,'.

            'activity_column_contact.title, ' .

            '(case when activity_column_contact.content_type = 1 then social_activity.display_activity_startendtime ' .
            'when activity_column_contact.content_type = 2 then social_expert_information.create_at ' .
            'when activity_column_contact.content_type = 3 then activity_vote.votedisplay end) time,'.

            '(case when activity_column_contact.content_type = 1 then social_activity.short_content ' .
            'when activity_column_contact.content_type = 2 then social_expert_information.pc_cover ' .
            'when activity_column_contact.content_type = 3 then activity_vote.img_path end) img,'.

            '(case when activity_column_contact.content_type = 1 then social_activity.role end) role_ids,' .


            '(case when activity_column_contact.content_type = 1 then social_activity.applystart ' .
            'when activity_column_contact.content_type = 3 then activity_vote.votestart end) startime,'.

            '(case when activity_column_contact.content_type = 1 then social_activity.applyend ' .
            'when activity_column_contact.content_type = 3 then activity_vote.voteend end) endtime,'.

            '(case when activity_column_contact.content_type = 1 then social_activity.stakeholder ' .
            'when activity_column_contact.content_type = 2 then social_expert_information.content ' .
            'when activity_column_contact.content_type = 3 then activity_vote.description end) content';
        }elseif($column_id == 5){

            $where['activity_column_contact.content_type'] = array('neq',2);
            $field = 'activitystart,activityend,is_live,livestart,liveend,activity_column_contact.content_type,'.
                '(case when activity_column_contact.content_type = 1 then social_activity.id ' .
                'when activity_column_contact.content_type = 3 then activity_vote.id end) id,'.

                'activity_column_contact.title, ' .

                '(case when activity_column_contact.content_type = 1 then social_activity.display_activity_startendtime ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votedisplay end) time,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.short_content ' .
                'when activity_column_contact.content_type = 3 then activity_vote.img_path end) img,'.

                /*'(case when activity_column_contact.content_type = 1 then social_activity.applystart ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votestart end) startime,'.*/

                '(case when activity_column_contact.content_type = 1 then social_activity.applyend ' .
                'when activity_column_contact.content_type = 3 then activity_vote.voteend end) endtime,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.role end) role_ids,' .

                '(case when activity_column_contact.content_type = 1 then social_activity.stakeholder ' .
                'when activity_column_contact.content_type = 3 then activity_vote.description end) content';
        }
        if(1 == $fromAPP)
        {
            $field = 'activitystart,activityend,is_live,livestart,liveend,activity_column_contact.content_type,' .
                '(case when activity_column_contact.content_type = 1 then social_activity.id ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_column_contact.content_type = 3 then activity_vote.id end) id,' .

                '(case when activity_column_contact.content_type = 1 then \''.ICON_ACTIVITY .'\' ' .
                'when activity_column_contact.content_type = 2 then \''.ICON_INFOMATION .'\' ' .
                'when activity_column_contact.content_type = 3 then \''.ICON_VOTE .'\'   end) icon_url,' .

                '(case when activity_column_contact.content_type = 1 then (case when unix_timestamp(now()) >= social_activity.applystart AND unix_timestamp(now()) <= social_activity.applyend then \'报名\' else \'活动\'end) ' .
                'when activity_column_contact.content_type = 2 then \'资讯\' ' .
                'when activity_column_contact.content_type = 3 then \'投票\' end) name,' .

                'activity_column_contact.title, ' .

                '(case when activity_column_contact.content_type = 1 then concat(\'参与对象: \', social_activity.stakeholder) ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.content ' .
                'when activity_column_contact.content_type = 3 then concat(\'投票时间: \', activity_vote.votedisplay) end) content1,'.

                '(case when activity_column_contact.content_type = 1 then concat(\'活动时间: \' , social_activity.display_activity_startendtime) ' .
                'when activity_column_contact.content_type = 2 then \'\' ' .
                'when activity_column_contact.content_type = 3 then concat(\'投票规则: \' ,activity_vote.description) end) content2,'.
                '(case when activity_column_contact.content_type = 1 then concat(\'http://'. $_SERVER['SERVER_NAME'] .'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityDetails?id=\' , social_activity.id) '.
                'when activity_column_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
                'when activity_column_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) url,'.

                '(case when activity_column_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].ACTIVITY_LOCAL_DIR.'\' ,  social_activity.short_content) '.
                'when activity_column_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].EXPERTINFO_LOCAL_DIR.'\' ,  social_expert_information.mobile_cover) '.
                'when activity_column_contact.content_type = 3 then concat(\''.C('oss_path').'\' ,  activity_vote.img_path)  end) img_url,'.

             '(case when activity_column_contact.content_type = 1 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityShare?id=\' ,social_activity.id) '.
             'when activity_column_contact.content_type = 2 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_EXEERTINFOMATION_VER . '/ExpertInformation/informationDetails?id=\' , social_expert_information.id) '.
             'when activity_column_contact.content_type = 3 then concat(\'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/votingDetails?id=\' , activity_vote.id) end) share_url'

             ;

        }
        $sql = $activity_specialcolumn_type
            ->join('social_activity ON activity_column_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_column_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join($join)
            ->field($field)
            ->having('id is not null')
            ->where($where)
            ->order('activity_column_contact.sort')
            ->select();
        $count = count($sql);

        $Page = new \Think\Page($count, 15);
        $Page->parameter = $queryArray;

        if($limits){
            $limit = $limits;
        }/*else{
            $limit = "$Page->firstRow . ',' . $Page->listRows";
        }*/

        $resources = $activity_specialcolumn_type
            ->join('social_activity ON activity_column_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_column_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join($join)
            ->field($field)

            ->where($where)
            ->order('activity_column_contact.sort')
            ->limit($limit)
            ->having('id is not null')
            ->select();
        return $resources;
    }

    /*
     *火热预热更多
     */
    public function get_more_list(&$count,$column_id,$keyword,$pageIndex = 1, $pageSize = 20){
        $activity_specialcolumn_type = M('activity_column_contact');
        $where['activity_column_contact.status']=1;
        $where['activity_column_contact.column_id']= $column_id;
        if ($keyword != ''){
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where['activity_column_contact.title'][] = array("like", "%" . $item . "%");
            }
        }
        $join = 'left join social_expert_information ON activity_column_contact.content_id = social_expert_information.id AND social_expert_information.status='. EXPERTINFO_STATUS_ONLINE;
        if($column_id == 4){
            $field ='activity_column_contact.content_type,'.
                '(case when activity_column_contact.content_type = 1 then social_activity.id ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.id ' .
                'when activity_column_contact.content_type = 3 then activity_vote.id end) id,'.

                'activity_column_contact.title, ' .

                '(case when activity_column_contact.content_type = 1 then social_activity.display_activity_startendtime ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.create_at ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votedisplay end) time,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.short_content ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.pc_cover ' .
                'when activity_column_contact.content_type = 3 then activity_vote.img_path end) img,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.role end) role_ids,' .


                '(case when activity_column_contact.content_type = 1 then social_activity.applystart ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votestart end) startime,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.applyend ' .
                'when activity_column_contact.content_type = 3 then activity_vote.voteend end) endtime,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.stakeholder ' .
                'when activity_column_contact.content_type = 2 then social_expert_information.content ' .
                'when activity_column_contact.content_type = 3 then activity_vote.description end) content';
        }elseif($column_id == 5){

            $where['activity_column_contact.content_type'] = array('neq',2);
            $field = 'activity_column_contact.content_type,'.
                '(case when activity_column_contact.content_type = 1 then social_activity.id ' .
                'when activity_column_contact.content_type = 3 then activity_vote.id end) id,'.

                'activity_column_contact.title, ' .

                '(case when activity_column_contact.content_type = 1 then social_activity.display_activity_startendtime ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votedisplay end) time,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.short_content ' .
                'when activity_column_contact.content_type = 3 then activity_vote.img_path end) img,'.

                /*'(case when activity_column_contact.content_type = 1 then social_activity.applystart ' .
                'when activity_column_contact.content_type = 3 then activity_vote.votestart end) startime,'.*/

                '(case when activity_column_contact.content_type = 1 then social_activity.applyend ' .
                'when activity_column_contact.content_type = 3 then activity_vote.voteend end) endtime,'.

                '(case when activity_column_contact.content_type = 1 then social_activity.role end) role_ids,' .

                '(case when activity_column_contact.content_type = 1 then social_activity.stakeholder ' .
                'when activity_column_contact.content_type = 3 then activity_vote.description end) content';
        }
        $sql = $activity_specialcolumn_type
            ->join('social_activity ON activity_column_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_column_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join($join)
            ->field($field)
            ->having('id is not null')
            ->where($where)
            ->order('activity_column_contact.sort')
            ->select();
        $count = count($sql);
        $resources = $activity_specialcolumn_type
            ->join('social_activity ON activity_column_contact.content_id = social_activity.id AND social_activity.status ='.ACTIVITY_STATUS_PUBLISHED, 'left')
            ->join('activity_vote ON activity_column_contact.content_id = activity_vote.id AND activity_vote.flag ='.VOTE_FLAG_ONLINE, 'left')
            ->join($join)
            ->field($field)
            ->where($where)
            ->order('activity_column_contact.sort')
            ->having('id is not null')
            ->page($pageIndex,$pageSize)
            ->select();
        return $resources;
    }

    public function getDisplayColumn()
    {
        $model = M('dict_column');
        $where['module_name'] = '京版活动';
        $where['is_display'] = 1;
        return $model->where($where)->field('id,column_name name')->select();
    }

    public function qiPaoCount($id) {
        $data['aid'] = $id;
        $data['type'] = 2;
        $data['dateorder'] = date('Ymd');
        $row = M('pc_count')->where($data)->find();
        if (empty($row)) {
            $data['c_num'] = 1;
            $ip = get_client_ip();
            $data['create_at'] = time();
            $data['client_ip'] = $ip;
            M('pc_count')->add($data);
        } else {
            M('pc_count')->where($data)->setInc('c_num',1);
        }
    }

}
