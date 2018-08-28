<?php
namespace Common\Model;
use Think\Model; 
define('BIZ_CLASS_STUDENT_STATUS',2);
define('SCHOOL_CLASS',1);
define('PERSONAL_CLASS',2);

define('CLASSSTATE_TRANSFER',1);
define('CLASSSTATE_RECEIVED',2);
define('CLASSSTATE_REJECTED',3); 

define('CLASSFLAG_STOP',0);
define('CLASSFLAG_NORMAL',1);
define('CLASSFLAG_TRANSFERING',2);

define('SCHOOL_STOP',0);
define('SCHOOL_NORMAL',1);

define('STUDENTJOINSTATE_UNAUTH',1);
define('STUDENTJOINSTATE_AUTH',2);
define('STUDENTJOINSTATE_REJECT',3);
define('CLASS_DELETE_STATUS',0);

define('STUDENTJOINMODE_TEACHER',0);
define('STUDENTJOINMODE_STUDENT',1);

class Biz_classModel extends Model
{

    public $model = '';
    protected $tableName = 'biz_class';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     * 得到一条班级信息
     * id       班级ID
     */
    public function getClassInfo($id, $teacher_id = 0)
    {
        $model = $this->model;
        if ($teacher_id == 0) {
            $result = $model->where(' id=' . $id)->field('id,name,grade_id,class_teacher_id,class_teacher,student_count,school_id,flag,class_status,is_delete')->find();
            if ($result['class_status'] == PERSONAL_CLASS) {
                $result = $model->where('biz_class.id=' . $id)
                    ->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
                    ->join('auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
                    ->field('biz_class.id,biz_class.name,biz_class.grade_id,class_teacher_id,class_teacher,student_count,auth_teacher.school_id,biz_class.flag,biz_class.class_status,biz_class.is_delete,auth_teacher.id teacher_id')->find();

            }

        } else {
            $result = $model->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
                ->where('biz_class_teacher.teacher_id=' . $teacher_id . ' and biz_class.id=' . $id)->field('biz_class.id,name,grade_id,class_teacher_id,class_teacher,student_count,school_id,flag,class_status,is_delete')->find();
        }
        return $result;
    }


    /*
     * 获得教师所在的某个校建班的任教学科
    */
    public function getTeacherSchoolClassData($where, $order = 'desc')
    {
        $mdoel = M('Auth_teacher');
        $result = $mdoel->where($where)->join('biz_class_teacher on biz_class_teacher.teacher_id=auth_teacher.id')
            ->join('dict_course on dict_course.id=biz_class_teacher.course_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('dict_grade.grade,biz_class.id class_id,biz_class.name class_name,dict_course.course_name course,biz_class.class_code,'
                . 'biz_class.class_status,biz_class.flag,dict_course.course_name,dict_course.id course_id')
            ->group('biz_class_teacher.course_id')
            ->order('biz_class_teacher.create_at ' . $order)
            ->select();
        return $result;
    }


    /*
     * 得到班级信息,根据教师ID
     * id       教师ID
     */
    public function getTeachClassData($id, $class_id = '')
    {
        $model = $this->model;
        if ($class_id == '') {
            $result = $model->where('class_teacher_id=' . $id)->field('id,name,grade_id,class_teacher_id,class_teacher,student_count,school_id')->select();
        } else {
            $result = $model->where('id=' . $class_id . ' and class_teacher_id=' . $id)->field('id,name,grade_id,class_teacher_id,class_teacher,student_count,school_id')->select();

        }
        return $result;
    }

    public function getIsClassStop($classId, $classCategory)
    {
        if (SCHOOL_CLASS == $classCategory) {
            $flagResult = $this->model->where('biz_class.id=' . $classId)->join('dict_schoollist ON dict_schoollist.id = biz_class.school_id')->field('dict_schoollist.flag')->find();
            $flagResult = $flagResult['flag'];
            if (SCHOOL_STOP == $flagResult)
                return true;
        }
        $flagResult = $this->model->where('id=' . $classId)->field('flag')->find();
        $flagResult = $flagResult['flag'];
        if (CLASSFLAG_STOP == $flagResult)
            return true;
        return false;
    }

    /*
     * 得到某个学校下所有班级信息,根据年级或班级分组(校内班)
     */
    public function getClassDataBySchool($school_id, $grade_id = 0, $class_type = 0, $group_class_name = '')
    {
        $group_string = "dict_grade.id";
        if ($grade_id != 0) {
            $condition['biz_class.grade_id'] = $grade_id;
            $group_string = "biz_class.id";
        }
        $condition['biz_class.class_status'] = 1;
        $condition['biz_class.school_id'] = $school_id;
        if ($group_class_name != '') {
            $group_string = "biz_class.name";
        }


        $condition['biz_class.is_delete'] = CLASS_DELETE_STATUS;
        $result = $this->model->where($condition)
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and biz_class_teacher.is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->field('biz_class.grade_id,biz_class.id class_id,biz_class.name class_name,biz_class.class_status,dict_grade.grade')
            ->group($group_string)->select();
        return $result;
    }


    /*
     * 得到某些学校下的老师创建的班级信息
     */
    public function getClassDataByTeacherSchool($school_id, $grade_id = 0, $clss_type = '', $group_class_flag = '')
    {
        $group_string = "dict_grade.id";
        if ($grade_id != 0) {
            $condition['biz_class.grade_id'] = $grade_id;
            $group_string = "biz_class.id";
        }
        $personal_class_status = 1;
        $condition['auth_teacher.school_id'] = $school_id;
        $condition['biz_class.is_delete'] = CLASS_DELETE_STATUS;

        $condition['biz_class_teacher.is_handler'] = $personal_class_status;
        if ($clss_type != '') {
            unset($condition['biz_class_teacher.is_handler']);
            unset($condition['auth_teacher.school_id']);

            $where['auth_teacher.school_id'] = $school_id;
            $where['biz_class.school_id'] = $school_id;
            $where['_logic'] = 'or';
            $condition['_complex'] = $where;
        }
        if ($group_class_flag != '') {
            $group_string = "biz_class.name";
        }

        $result = $this->model->where($condition)
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->field('biz_class.grade_id,biz_class.id class_id,biz_class.name class_name,biz_class.class_status,dict_grade.grade,auth_teacher.name teacher_name')
            ->group($group_string)->select();
        return $result;
    }


    /*
     * 
     * 得到班级信息,根据用户ID 用户角色
     *
     */
    public function getClassList($role, $userId, $pageIndex, $pageSize = 20, $grade = 0, $className = '', &$count, &$availableGrade, &$availableClassName)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'LEFT JOIN biz_class_student ON biz_class_student.class_id = biz_class.id';
        $join[] = 'LEFT JOIN biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $join[] = 'LEFT JOIN auth_student ON auth_student.id = biz_class_student.student_id';
        $join[] = 'LEFT JOIN auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id';
        $join[] = 'LEFT JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id';
        $join[] = 'LEFT JOIN dict_schoollist dict_schoollist_class ON dict_schoollist_class.id = biz_class.school_id';

        $field = "(case when class_status = 1 then 1 else 0 end) isAuth,biz_class.id classId,dict_grade.grade gradeName,biz_class.name classShortName,concat(dict_grade.grade,biz_class.name) className,class_code classCode,
                          (case when dict_schoollist.flag = 0 AND class_status = 1 then '停用' when dict_schoollist_class.flag = 0 AND class_status = 1 then '停用'  when biz_class.flag = 1 then '正常' when biz_class.flag =0 then '停用' when biz_class.flag = 2 then '移交中' end ) classStatus,
                          (case when class_status = 1 then dict_schoollist_class.school_name else dict_schoollist.school_name end) schoolname,
                          COUNT(distinct case biz_class_student.status when 2 then biz_class_student.student_id else null end) studentCount,
                          COUNT(distinct case biz_class_teacher.teacher_id when null then null else biz_class_teacher.teacher_id end) teacherCount,
                          COUNT(distinct case biz_class_student.status when 1 then biz_class_student.student_id else null end) unAuthStudentCount,dict_grade.id as grade_id";
        /*
        $field = "(case when class_status = 1 then 1 else 0 end) isAuth,biz_class.id classId,concat(dict_grade.grade,biz_class.name) className,class_code classCode,
                          (case when biz_class.flag = 1 then '正常' when biz_class.flag =0 then '停用' when biz_class.flag = 2 then '移交中' end ) classStatus,
                          COUNT(distinct case biz_class_student.status when 2 then biz_class_student.student_id else null end) studentCount,
                          COUNT(distinct case biz_class_teacher.teacher_id when null then null else biz_class_teacher.teacher_id end) teacherCount,
                          COUNT(distinct case biz_class_student.status when 1 then biz_class_student.student_id else null end) unAuthStudentCount";*/
        $where['biz_class.is_delete'] = 0;
        if ($grade != 0)
            $where['biz_class.grade_id'] = $grade;
        if ($className != '')
            $where['biz_class.name'] = $className;
        switch ($role) {
            case ROLE_TEACHER:
                $where['_string'] = "biz_class.id in (select biz_class.id from biz_class join biz_class_teacher ON biz_class_teacher.class_id = biz_class.id AND biz_class_teacher.teacher_id=$userId" . ")";
                $group = 'biz_class.id';
                break;
            case ROLE_STUDENT:
                $where['_string'] = "biz_class.id in (select biz_class.id from biz_class join biz_class_student ON biz_class_student.class_id = biz_class.id AND biz_class_student.student_id=$userId AND status = " . STUDENTJOINSTATE_AUTH . ")";
                $group = 'biz_class.id';
                break;
            case ROLE_PARENT : //get student info
                $studentList = M('auth_student_parent_contact')->where('parent_id=' . $userId)->field('student_id')
                    ->union('select id student_id from auth_student where parent_id =' . $userId)->select();
                $studentList = implode(',', array_column($studentList, 'student_id'));
                if (count($studentList) == 0)
                    return array();
                $where['_string'] = 'biz_class_student.student_id in (' . $studentList . ')';
                $group = 'biz_class.id,biz_class_student.student_id';
                $join[] = 'auth_student ON biz_class_student.student_id = auth_student.id';
                $field .= ',auth_student.student_name studentName';
                break;
        }
        $allWhere = $where;
        unset($allWhere['biz_class.grade_id']);
        unset($allWhere['biz_class.name']);
        $availableGrade = $model
            ->join($join)
            ->where($allWhere)->field('dict_grade.id,dict_grade.grade')
            ->group('dict_grade.id')->select();
        $availableClassName = $model
            ->join($join)
            ->where($allWhere)->field('biz_class.name')
            ->group('biz_class.name')->select();
        $count = $model
            ->join($join)
            ->where($where)->field('1')
            ->group($group)->select();
        $count = count($count);
        if(-1 == $pageIndex)
            $result = $model
                ->join($join)
                ->where($where)->field($field)
                ->group($group)->order('biz_class.create_at desc')->select();
        else
            $result = $model
                ->join($join)
                ->where($where)->field($field)
                ->group($group)->page($pageIndex . ',' . $pageSize)->order('biz_class.create_at desc')->select();
        return $result;
    }

    /*
     * 获得某个班级信息
     */
    public function getPersonalClassInfo($condition = array())
    {
        $result = $this->model->where($condition)
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->field('biz_class.id class_id,biz_class_teacher.teacher_id')->find();
        return $result;
    }

    /*
     * 获取班级详情
     *
     */
    public function getClassNameTeacher($classId)
    {
        return $this->model->where('biz_class.id=' . $classId)
            ->join('biz_class_teacher ON biz_class.id = biz_class_teacher.class_id AND biz_class_teacher.is_handler =1 AND biz_class_teacher.class_id = ' . $classId, 'left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('(case when biz_class.class_teacher_id is null then biz_class_teacher.teacher_id else biz_class.class_teacher_id end) teacher_id,concat(dict_grade.grade,biz_class.name) class_name')
            ->find();
    }

    /*
     * 自建班列表
     *
     */
    public function getPersonalClassList($teacherId)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "biz_class.id,concat(dict_grade.grade,biz_class.name) className,class_code classCode
                          ";
        $where['class_status'] = PERSONAL_CLASS;
        $where['biz_class.is_delete'] = 0;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId . ' AND biz_class_teacher.is_handler = 1';
        $group = 'biz_class.id';


        $result = $model
            ->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        return $result;
    }

    /*
     * 教师加入校建班列表
     *
     */
    public function getTeacherSchoolClassList($teacherId)
    {
        return M()->query("SELECT biz_class.id FROM biz_class_teacher 
                    JOIN biz_class ON biz_class.is_delete = 0 AND biz_class.id = biz_class_teacher.class_id AND biz_class.class_status = ".SCHOOL_CLASS ." AND biz_class_teacher.teacher_id = $teacherId AND biz_class_teacher.is_handler = 0"." 
                    GROUP BY biz_class.id" );
    }

    /*
     * 获取教师的班级列表
     *
     */
    public function getClassListByTeacher($teacherId, $class_id = '')
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "dict_grade.id as grade_id,biz_class.id,dict_grade.grade,biz_class.name,class_code classCode,biz_class_teacher.course_id,biz_class.class_status,biz_class_teacher.is_handler";

        $where['biz_class.is_delete'] = 0;
        if ($class_id != '') {
            $where['biz_class.id'] = $class_id;
        }
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';


        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->order("dict_grade.id asc")->select();

        return $result;
    }

    /*
    * 获取教师的班级列表
    *
    */
    public function getClassListByTeacherAndStudent($teacherId, $class_id = '')
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "count(biz_class_student.student_id) as b_c_s,dict_grade.id as grade_id,biz_class.id,dict_grade.grade,biz_class.name,class_code classCode,biz_class_teacher.course_id,biz_class.class_status,biz_class_teacher.is_handler";

        $where['biz_class.is_delete'] = 0;
        if ($class_id != '') {
            $where['biz_class.id'] = $class_id;
        }
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';


        $result = $model->join($join)
            ->join("biz_class_student ON biz_class_student.class_id=biz_class.id")
            ->where($where)->field($field)
            ->group($group)->order("dict_grade.id asc")->having('b_c_s>0')->select();

        return $result;
    }

    /*
   * 获取教师的班级列表
   *
   */
    public function getClassListByTeacherAndStudentstatus($teacherId, $class_id = '')
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "count(biz_class_student.student_id) as b_c_s,dict_grade.id as grade_id,biz_class.id as classid,dict_grade.grade as gradename,biz_class.name as classshortname,class_code classCode,biz_class_teacher.course_id,biz_class.class_status,biz_class_teacher.is_handler,biz_class.class_status as isauth";

        $where['biz_class.is_delete'] = 0;
        if ($class_id != '') {
            $where['biz_class.id'] = $class_id;
        }
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';


        $result = $model->join($join)
            ->join("biz_class_student ON biz_class_student.class_id=biz_class.id")
            ->where($where)->field($field)
            ->group($group)->order("dict_grade.id asc")->having('b_c_s>0')->select();

        return $result;
    }

    //获取教师所有的年级
    public function getGradeListByTeacher($teacherId)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "dict_grade.id as id,biz_class.id as cid,dict_grade.grade,biz_class.name,class_code classCode";

        $where['biz_class.is_delete'] = 0;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'dict_grade.id';


        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        return $result;
    }


    //根据年级获取老师的所有班级
    public function getClassListTeacher($teacherId, $grade_id)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "biz_class.flag,biz_class.school_id,biz_class.class_status,biz_class.id,dict_grade.grade,biz_class.name,class_code classCode";

        $where['biz_class.is_delete'] = 0;
        $where['biz_class.flag'] = 1;
        $where['biz_class.grade_id'] = $grade_id;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';

        if (empty($teacherId)) {
            return true;
        }
        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0) {
                    $result[$k]['flag'] = 0;
                    unset($result[$k]);
                }

            }
        }

        return $result;
    }

    public function getClassListTeacherCopy($teacherId, $grade_id)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "biz_class.flag,biz_class.school_id,biz_class.class_status,biz_class.id,dict_grade.grade,biz_class.name,class_code classCode";

//        $where['biz_class.is_delete'] = 0;
        //$where['biz_class.flag'] = 1;
        $where['biz_class.grade_id'] = $grade_id;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';


        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();
        //print_r(M()->getLastSql());die();
       //print_r($result);die();
        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0) {
                    $result[$k]['flag'] = 0;
                    //unset($result[$k]);
                }

            }
        }

        return $result;
    }

    //获取年级  根据年级获取班级  tree结构
    public function getGradeClassListTeacher( $teacherId ) {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "dict_grade.id as id,biz_class.id as cid,dict_grade.grade,biz_class.name,class_code classCode";

        $where['biz_class.is_delete'] = 0;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'dict_grade.id';

        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        if (!empty($result)) {
            foreach ($result as $k=>$v) {
                if (!empty($v['id'])) {
                    $rowlist = $this->getClassListTeacher( $teacherId,$v['id']);
                    $result[$k]['classlist'] = $rowlist;
                }
            }
        }

        return $result;
    }


    //app根据年级获取老师的所有班级
    public function getClassListAppTeacher($teacherId, $grade_id,$is_show)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "biz_class.flag,biz_class.school_id,biz_class.class_status,biz_class.id,biz_class.name as class_name";

        $where['biz_class.is_delete'] = 0;

        if ($is_show!=1) {
            $where['biz_class.flag'] = 1;
        }

        $where['biz_class.grade_id'] = $grade_id;
        $where['_string'] = 'biz_class_teacher.teacher_id =' . $teacherId;
        $group = 'biz_class.id';


        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0 && $is_show!=1 ) {
                    $result[$k]['flag'] = 0;
                    unset($result[$k]);
                }

            }
        }

        return $result;
    }
    /*
     * 获取教师的班级年级种类
     *
     */
    public function getDistinctClassGradeByTeacher($teacherId, $course_id = 0)
    {
        $model = $this->model;
        $join[] = 'dict_grade on dict_grade.id=biz_class.grade_id';
        $join[] = 'biz_class_teacher ON biz_class_teacher.class_id = biz_class.id';
        $field = "dict_grade.id,dict_grade.grade";

        $where['biz_class.is_delete'] = 0;
        $where['biz_class_teacher.teacher_id'] = $teacherId;
        if ($course_id != 0) {
            $where['biz_class_teacher.course_id'] = $course_id;
        }
        $group = 'dict_grade.id';


        $result = $model->join($join)
            ->where($where)->field($field)
            ->group($group)->select();

        return $result;
    }

    public function getReceiveClassList($teacherId, $classType)
    {
        $handsOffModel = M('biz_class_handsoff');
        $where['handsoff_status'] = CLASSSTATE_TRANSFER;
        $where['biz_class.is_delete'] = 0;
        if (transferredClass == $classType) {
            $where['send_teacherid'] = $teacherId;
            return $handsOffModel->where($where)
                ->join('biz_class ON biz_class_handsoff.class_id = biz_class.id')
                ->join('auth_teacher ON auth_teacher.id = biz_class_handsoff.dest_teacherid')
                ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
                ->join('dict_course ON dict_course.id = biz_class_handsoff.course_id', 'left')
                ->field('(case when biz_class.class_status = 1 then 1 else 0 end) isAuth,biz_class_handsoff.class_id id,concat(dict_grade.grade,biz_class.name) className,
                      biz_class.class_code classCode,biz_class_handsoff.course_id transferCourse,dict_course.course_name transferCourseName, auth_teacher.id receiveteacherid,auth_teacher.name receiveTeacherName,
                      auth_teacher.telephone receiveTeacherTel')
                ->select();
        } else if (unReceivedClass == $classType) {
            $where['dest_teacherid'] = $teacherId;
            return $handsOffModel->where($where)
                ->join('biz_class ON biz_class_handsoff.class_id = biz_class.id')
                ->join('auth_teacher ON auth_teacher.id = biz_class_handsoff.send_teacherid')
                ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
                ->join('dict_course ON dict_course.id = biz_class_handsoff.course_id', 'left')
                ->field('(case when biz_class.class_status = 1 then 1 else 0 end) isAuth,biz_class_handsoff.class_id id,concat(dict_grade.grade,biz_class.name) className,
                      biz_class.class_code classCode,biz_class_handsoff.course_id transferCourse,dict_course.course_name transferCourseName,auth_teacher.id transferteacherid , auth_teacher.name transferTeacherName,
                      auth_teacher.telephone transferTeacherTel')
                ->select();

        }

    }

    /*
    * 得到班级学生列表,根据班级ID 学生类型(未通过 已通过状态)
    *
    */
    public function getClassStudentList($classId, $studentType = '')
    {
        $where['biz_class_student.class_id'] = $classId;
        $where['biz_class.is_delete'] = 0;
        $field = 'auth_student.id,auth_student.student_name name,auth_student.sex,ifnull(auth_student.parent_tel,\'\') telephone';
        if (1 == $studentType) {
            $where['biz_class_student.status'] = STUDENTJOINSTATE_AUTH;
        } else if (2 == $studentType) {
            $where['_string'] = ' biz_class_student.status = ' . STUDENTJOINSTATE_UNAUTH . ' OR biz_class_student.joinmode = ' . STUDENTJOINMODE_STUDENT;
            $field = $field . ',biz_class_student.status';
        }

        $result = M('biz_class_student')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->join('biz_class ON biz_class.id = biz_class_student.class_id')
            ->join('auth_parent ON auth_student.parent_id = auth_parent.id', 'left')
            ->where($where)
            ->field($field)->select();
        return $result;
    }

    /*
    * 设置学生在班级的通过状态
    *
    */
    public function updateClassStudentStatus($classId, $studentId, $status, &$errorInfo)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete']) {
            $errorInfo = '班级不存在';
            return false;
        }
        $model = M('biz_class_student');
        $where['class_id'] = $classId;
        $where['student_id'] = $studentId;
        $data['status'] = $status;
        if ($model->where($where)->find()) {
            $whereTemp = $where;
            $whereTemp[] = $data;
            if ($model->where($whereTemp)->find()) {
                return true;
            }
            $result = $model->where($where)->save($data);
            if (false === $result) {
                $errorInfo = '学生状态设置失败';
                return false;
            } else {
				$updateWhere['biz_class.id'] = $classId;
				$updateResult = M()->execute("UPDATE biz_class SET student_count = (SELECT count(1) FROM biz_class_student WHERE class_id = $classId AND status = 2) WHERE biz_class.id = $classId");
				if(false === $updateResult)
					return false;
                return true;
            }
        } else {
            $errorInfo = '学生不在该班级中';
            return false;
        }
        return $result;
    }

    /*
    * APP端获取班级简要信息
    *
    */
    public function getBriefClassData($classId, $role, $userId)
    {
        //if($role != ROLE_TEACHER)
        //    return array();

        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            return false;
        }
        if (ROLE_TEACHER == $role) {
            $where1['biz_class_teacher.teacher_id'] = $userId;
            $where1['biz_class_teacher.class_id'] = $classId;
            $where2['biz_class.class_teacher_id'] = $userId;
            $where2['biz_class.id'] = $classId;

            if (!$this->model->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')->where($where1)->find() && !$this->model->where($where2)->find())
                return array();
            $result = $this->model->where('biz_class.id = ' . $classId)
                ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id AND biz_class_teacher.teacher_id=' . $userId, 'left')
                ->join('dict_course ON dict_course.id = biz_class_teacher.course_id', 'left')
                ->join('auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id', 'left')
                ->field("biz_class.id,dict_grade.grade,biz_class.name class,group_concat(dict_course.id SEPARATOR ',') courseid,group_concat(dict_course.course_name SEPARATOR ',') courses,auth_teacher.name admin")
                ->group('biz_class.id')
                ->select();

//      if (empty($result)) {
//          $result = $this->model->where('biz_class.id = ' . $classId . ' AND biz_class.class_teacher_id=' . $userId)
//              ->join('dict_grade on dict_grade.id=biz_class.grade_id')
//              ->join('dict_course ON dict_course.id = biz_class_teacher.course_id.course_id', 'left')
//              ->join('auth_teacher ON auth_teacher.id = biz_class.class_teacher_id')
//              ->field("biz_class.id,dict_grade.grade,biz_class.name class,dict_course.id courseid,dict_course.course_name courses,auth_teacher.name admin")
//              ->select();
//      }
            return $result;
        } else if (ROLE_STUDENT == $role) {
            $where1['biz_class_student.student_id'] = $userId;
            $where1['biz_class_student.class_id'] = $classId;
            $classInfo = $this->model->join('biz_class_student ON biz_class_student.class_id = biz_class.id ')->where($where1)->find();
            if (empty($classInfo))
                return array();
            $result = $this->model->where('biz_class.id = ' . $classId)
                ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id AND is_handler = 1 ')
                ->join('auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id ', 'left')
                ->field("biz_class.id,dict_grade.grade,biz_class.name class,auth_teacher.name admin")
                ->group('biz_class.id')
                ->select();
            if (empty($result)) {
                $result = $this->model->where('biz_class.id = ' . $classId)
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field("biz_class.id,dict_grade.grade,biz_class.name class")
                    ->group('biz_class.id')
                    ->select();
            }
            return $result;
        }
    }

    /*
    * 移交班级
    *
    */
    public function transferClass($classId, $teacherId, $courseId, $targetTeacherTelephone, &$errorInfo)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            $errorInfo = '班级不存在';
            return false;
        }

        if ($classInfo['flag'] == CLASSFLAG_TRANSFERING) {
            $errorInfo = '班级已经处于移交状态,无法再次移交';
            return false;
        }

        $classCategory = $classInfo['class_status'];

        $where = array();
        $where['biz_class_teacher.class_id'] = $classId;
        $where['biz_class_teacher.teacher_id'] = $teacherId;

        if (SCHOOL_CLASS == $classCategory) //校建班逻辑
        {
            $where['biz_class_teacher.course_id'] = $courseId;
        }

        if (M('biz_class_teacher')->where($where)->find()) {
            $where = array();
            $where['telephone'] = $targetTeacherTelephone;
            $targetTeacherInfo = M('auth_teacher')->where($where)->find();
            if (empty($targetTeacherInfo)) {
                $errorInfo = '目标教师未注册';
                return false;
            }

            $targetTeacherId = $targetTeacherInfo['id'];
            $sourceTeacherInfo = D('Auth_teacher')->getTeachInfo($teacherId);

            if ($targetTeacherId == $teacherId) {
                $errorInfo = '班级不能移交给自己';
                return false;
            }

            if (SCHOOL_CLASS == $classCategory) //校建班逻辑
            {
                if ($targetTeacherInfo['school_id'] != $sourceTeacherInfo['school_id']) {
                    $errorInfo = '目标教师的所属学校与您的所属学校不同,无法移交';
                    return false;
                }

                if (1 != $targetTeacherInfo['apply_school_status']) {
                    $errorInfo = '目标教师审核未通过';
                    return false;
                }
                $where['auth_teacher_second.course_id'] = $courseId;
                $result = M('auth_teacher')->where($where)
                    ->join('auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id')
                    ->field('auth_teacher_second.course_id')
                    ->find();
                if (empty($result)) {
                    $errorInfo = '目标教师不任教该学科,无法移交班级';
                    return false;
                }
                $whereTargetTeacher = array();
                $whereTargetTeacher['teacher_id'] = $targetTeacherId;
                $whereTargetTeacher['class_id'] = $classId;
                $whereTargetTeacher['course_id'] = $courseId;
                if (M('biz_class_teacher')->where($whereTargetTeacher)->find()) {
                    $errorInfo = '目标教师已经任教该学科,无法移交班级';
                    return false;
                }
            }

            $where = array();
            $where['send_teacherid'] = $teacherId;
            $where['class_id'] = $classId;
            if (SCHOOL_CLASS == $classCategory)
                $where['course_id'] = $courseId;
            $where['handsoff_status'] = CLASSSTATE_TRANSFER;

            $handsOffModel = M('biz_class_handsoff');
            if ($handsOffModel->where($where)->field('1')->find()) {
                $errorInfo = '该班级或该学科已经处于移交状态,无法再次移交';
                return false;
            }
            unset($where['handsoff_status']);
            $where['dest_teacherid'] = $targetTeacherId;

            $this->model->startTrans();
            $handsOffModel->startTrans();
            $updateResult = true;
            if ($handsOffModel->where($where)->field('1')->find()) {
                $data = array();
                $data['handsoff_status'] = CLASSSTATE_TRANSFER;
                $updateResult &= $handsOffModel->where($where)->save($data);
            } else {
                $data = $where;
                $data['handsoff_status'] = CLASSSTATE_TRANSFER;
                $updateResult &= $handsOffModel->add($data);
            }
            if (PERSONAL_CLASS == $classCategory)
                $updateResult &= $this->model->where('id=' . $classId)->save(array('flag' => CLASSFLAG_TRANSFERING));
            if ($updateResult !== false) {
                $this->model->commit();
                $handsOffModel->commit();
            } else {
                $this->model->rollback();
                $handsOffModel->rollback();
                $errorInfo = '移交失败';
                return false;
            }
        } else {
            //源教师不任教该班或该班的这个学科
            $errorInfo = '当前教师不任教该班或该学科,无法移交班级';
            return false;
        }
        return true;
    }

    /* 
     * 得到班级信息
     * ids      班级id
     */
    public function getClassDataInfo($ids)
    {
        $model = $this->model;
        $result = $model->where('id in(' . $ids . ')')->field('id,name,grade_id,class_teacher_id,class_teacher,student_count,school_id,is_delete,flag,class_status')->select();
        return $result;
    }

    /*
     * 设置班级名称
     *
     */
    public function setClassName($classId, $className, &$errorInfo)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete']) {
            $errorInfo = '班级不存在';
            return false;
        }
        $data['name'] = $className;
        $result = $this->model->where('id=' . $classId . ' AND class_status =' . PERSONAL_CLASS)->save($data);
        if (false !== $result)
            return true;
        return false;
    }


    /*
     * 获得一个教师的所有班级
     */
    public function getTeacherClassNum($teacher_id)
    {
        $classTeacherModel = M('biz_class_teacher');
        $where['teacher_id'] = $teacher_id;
        $where['is_handler'] = 1;
        $count = $classTeacherModel->where($where)->field('count(1) as num')->find();
        return $count['num'];
    }


    /*
     * 创建自建班级
     *
     */
    public function createClass($className, $gradeId, $teacherInfo, &$errorInfo)
    {
        $classTeacherModel = M('biz_class_teacher');

        $result = true;
        $where = array();
        $where['biz_class.name'] = $className;
        $where['biz_class.grade_id'] = $gradeId;
        $where['biz_class.is_delete'] = 0;
        $where1 = $where;
        $where2 = $where;
        $where1['_string'] = 'biz_class.class_teacher_id =' . $teacherInfo['id'] . ' AND class_status= ' . PERSONAL_CLASS;
        $where2['_string'] = 'biz_class_teacher.teacher_id = ' . $teacherInfo['id'] . ' AND is_handler = 1';
        $findResult1 = $this->model->where($where1)->field('biz_class.id')->find();
        $findResult2 = $this->model->where($where2)->join('biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')->field('biz_class.id')->find();
        if (empty($findResult1['id']) && empty($findResult2['id'])) {
            $this->model->startTrans();
            $classTeacherModel->startTrans();
            $data['name'] = $className;
            $data['grade_id'] = $gradeId;
            $data['class_teacher_id'] = $teacherInfo['id'];
            $data['create_at'] = time();
            $data['class_teacher'] = $teacherInfo['name'];
            $data['school_id'] = 0;
            $data['class_status'] = PERSONAL_CLASS;
            $data['flag'] = CLASSFLAG_NORMAL;
            $id = $this->model->add($data);
            if ($id) {
                //modify class code
                $classCode = 100000 + $id;
                $saveData['class_code'] = vsprintf("%06d", $classCode);
                $result &= $this->model->where('id=' . $id)->save($saveData);
                $classTeacherData['class_id'] = $id;
                $classTeacherData['teacher_id'] = $teacherInfo['id'];
                $classTeacherData['create_at'] = time();
                $classTeacherData['is_handler'] = 1;
                $result &= $classTeacherModel->add($classTeacherData);
            } else
                $result = false;
            if ($result) {
                $this->model->commit();
                $classTeacherModel->commit();
            } else {
                $this->model->rollback();
                $classTeacherModel->rollback();
            }
        } else {
            $errorInfo = '该班级已经创建';
            return false;
        }
        return true;
    }

    /*
     * 根据班级CODE获取班级ID
     */
    public function getClassIdByClassCode($classCode)
    {
        $result = $this->model->where('class_code=' . $classCode)->field('id')->find();
        return $result['id'];

    }

    /*
     * 根据班级ID获取班级属性
     */
    public function getClassCategory($classId)
    {
        $result = $this->model->where('id=' . $classId . ' AND is_delete = 0')->field('class_status')->find();
        return $result['class_status'];
    }

    /*
     * 教师学生加入班级
     */
    public function joinClass($classId, $role, $userId, $courseId, &$errorInfo, $joinMode = 0)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            $errorInfo = '班级不存在';
            return false;
        }

        if (ROLE_TEACHER == $role) {
            $classTeacherModel = M('biz_class_teacher');
            $courseIds = explode(',', $courseId);
            for ($i = 0; $i < sizeof($courseIds); $i++) {
                $where = array();
                $where['course_id'] = $courseIds[$i];
                $where['teacher_id'] = $userId;
                $where['class_id'] = $classId;
                if ($classTeacherModel->where($where)->field('1')->find()) {
                    $courseInfo = D('Dict_course')->getCourseInfo($where['course_id']);
                    $errorInfo = '您已经是该班级的' . $courseInfo['course'] . '任教老师,请选择其他任教学科加入班级';
                    return false;
                }
                $data = $where;
                $data['is_handler'] = 0;
                $data['create_at'] = time();
                $classTeacherModel->add($data);
            }
            return true;
        } else if (ROLE_STUDENT == $role) {
            $classStudentModel = M('biz_class_student');
            $where['student_id'] = $userId;
            $where['class_id'] = $classId;
            $data = $where;
            $data['create_at'] = time();
            $data['update_at'] = time();
            $data['joinmode'] = $joinMode;
            if (SCHOOL_CLASS == $classInfo['class_status'])
                $data['status'] = STUDENTJOINSTATE_AUTH;
            else
                $data['status'] = STUDENTJOINSTATE_UNAUTH;
            $joinStatus = $classStudentModel->where($where)->field('status')->find();
            switch ($joinStatus['status']) {
                case STUDENTJOINSTATE_UNAUTH:
                    $errorInfo = '学生已经申请加入该班级,正在等待教师审核';
                    return false;
                case STUDENTJOINSTATE_AUTH:
                    $errorInfo = '学生已经加入该班级';
                    return false;
                case STUDENTJOINSTATE_REJECT:
                    return $classStudentModel->where($where)->save($data);

                default:
                    break;
            }
            $addResult = $classStudentModel->add($data);
            if($addResult === false){
                $errorInfo = '学生添加班级失败';
                return false;
            }
            return true;
        }
        return false;
    }

    /*
     * 离开班级
     *
     */
    public function leaveClass($classId, $role, $userId, &$errorInfo)
    {
        $classInfo = $this->getClassDataInfo($classId);
        $classInfo = $classInfo[0];
        if (empty($classInfo) || 1 == $classInfo['is_delete']) {
            $errorInfo = '班级不存在';
            return false;
        }
        if ($role == ROLE_TEACHER) {
            $classTeacherModel = M('biz_class_teacher');
            if (PERSONAL_CLASS == $classInfo['class_status']) {
                $errorInfo = '不能离开自建班级';
                return false;
            }
            if (CLASSFLAG_TRANSFERING == $classInfo['flag']) {
                $errorInfo = '班级处于移交中状态,不能离开';
                return false;
            }
            $where['teacher_id'] = $userId;
            $where['class_id'] = $classId;
            $result = $classTeacherModel->where($where)->delete();
            if ($result === false) {
                $errorInfo = '离开失败';
                return false;
            }
            return true;
        } else if ($role == ROLE_STUDENT) {

            $classTeacherModel = M('biz_class_student');
            $where['student_id'] = $userId;
            $where['class_id'] = $classId;
            $result = $classTeacherModel->where($where)->delete();
            if ($result === false) {
                $errorInfo = '离开失败';
                return false;
            }
            return true;

        }

    }

    /*
     * 删除班级
     *
     */
    public function deleteClass($classId, &$errorInfo, $source = '')
    {
        $classInfo = $this->getClassDataInfo($classId);
        $classInfo = $classInfo[0];
        if (empty($classInfo) || 1 == $classInfo['is_delete']) {
            $errorInfo = '班级不存在';
            return false;
        }
        /*if ($source == '') {
            if (SCHOOL_CLASS == $classInfo['class_status']) {
                $errorInfo = '不能删除校建班级';
                return false;
            }
        }*/
        if (CLASSFLAG_TRANSFERING == $classInfo['flag']) {
            $errorInfo = '班级处于移交中状态,不能删除';
            return false;
        }
        $data['is_delete'] = 1;
        $where['id'] = $classId;
        return $this->model->where($where)->save($data);
    }


    /*
     * 删除班级课表
     */
    public function deleteClassTimetable($class_id = 0, $teacher_id = 0)
    {
        if ($teacher_id != 0) {
            $where['teacher_id'] = $teacher_id;
        }
        if ($class_id != 0) {
            $where['class_id'] = $class_id;
        }
        if (empty($where)) {
            return false;
        }
        $model = M('biz_class_school_timetable');
        if ($model->where($where)->delete() === false) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * 插入学生班级记录表
     */
    public function addClassStudentRecord($data)
    {
        $values = '(' . rtrim(implode(',', $data), ',') . ')';
        $model = M('biz_class_student_record');
        $result = $model->execute('INSERT INTO biz_class_student_record(class_id,student_id,status,create_at,joinmode) VALUES ' . $values . ' '
            . 'on DUPLICATE KEY UPDATE create_at=VALUES(create_at)');
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * 插入教师班级记录表
     */
    public function addClassTeacherRecord($data)
    {
        $values = '(' . rtrim(implode(',', $data), ',') . ')';
        $model = M('biz_class_teacher_record');
        $result = $model->execute('INSERT INTO biz_class_teacher_record(teacher_id,class_id,course_id,is_handler,create_at) VALUES ' . $values . ' '
            . 'on DUPLICATE KEY UPDATE create_at=VALUES(create_at)');
        if ($result === false) {
            return false;
        } else {
            return true;
        }

    }

    /*
    * 删除学生班级记录表记录
    */
    public function deleteClassStudentRecord($classId,$studentId)
    {
        $where = array();
        $where['class_id'] =  $classId;
        $where['student_id'] =  $studentId;
        $model = M('biz_class_student_record');
        $result = $model->where($where)->delete();
        if(false === $result)
            return false;
        else
            return true;
    }


    /*
     * 删除教师班级记录表记录
     */
    public function deleteClassTeacherRecord($classId,$teacherId,$courseId)
    {
        $model = M('biz_class_teacher_record');
        $where = array();
        $where['class_id'] =  $classId;
        $where['teacher_id'] =  $teacherId;
        $where['course_id'] =  $courseId;
        $result = $model->where($where)->delete();
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     * 删除教师班级关联数据
     */
    public function deleteClassTeacher($teacher_id, $class_type = 0, $course_id = 0)
    {
        $model = M('biz_class_teacher');
        if ($class_type == 1) {
            $where['is_handler'] = 0;
        } elseif ($class_type == 2) {
            $where['is_handler'] = 1;
        }
        if ($course_id != 0) {
            $where['course_id'] = $course_id;
        }
        $where['teacher_id'] = $teacher_id;
        if ($model->where($where)->delete() === false) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * 删除校建班的任教信息,根据学科.
     */
    public function deleteSchoolClassByCourse($course_id, $teacher_id = 0)
    {
        if ($teacher_id != 0) {
            $where['teacher_id'] = $teacher_id;
        }
        $where['course_id'] = $course_id;
        $model = M('biz_class_school_timetable');
        if ($model->where($where)->delete() === false) {
            return false;
        } else {
            return true;
        }
    }


    public function setStudentAuthState($classId, $studentId, $status)
    {
        $classStudentModel = M('biz_class_student');
        $data['update_at'] = time();
        $where['student_id'] = $studentId;
        $where['class_id'] = $classId;
        $data['status'] = $status;
        return $classStudentModel->where($where)->save($data);

    }

    public function undoJoinClass($classId, $teacherId, $courseId)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete']) {
            return false;
        }

        $handsOffModel = M('biz_class_handsoff');
        $this->model->startTrans();
        $handsOffModel->startTrans();
        $bResult = true;
        if (PERSONAL_CLASS == $classInfo['class_status']) {
            $data['flag'] = CLASSFLAG_NORMAL;
            $classInfo = $this->model->where('id=' . $classId)->find();
            $bResult &= $this->model->where('id=' . $classId)->save($data);
        }
        $where['class_id'] = $classId;
        $where['send_teacherid'] = $teacherId;
        if (SCHOOL_CLASS == $classInfo['class_status']) {
            $where['course_id'] = $courseId;
        }
        $bResult &= $handsOffModel->where($where)->delete();

        if ($bResult !== false) {
            $this->model->commit();
            $handsOffModel->commit();
            return true;
        } else {
            $this->model->rollback();
            $handsOffModel->rollback();
            return false;
        }
    }

    public function receiveClass($classId, $teacherId, $courseId, $flag)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            return false;
        }

        $handsOffModel = M('biz_class_handsoff');
        $classTeacherModel = M('biz_class_teacher');
        $this->model->startTrans();
        $handsOffModel->startTrans();
        $classTeacherModel->startTrans();
        $bResult = true;
        if (PERSONAL_CLASS == $classInfo['class_status']) {
            $data['flag'] = CLASSFLAG_NORMAL;
            $classInfo = $this->model->where('id=' . $classId)->find();
            $bResult &= $this->model->where('id=' . $classId)->save($data);
        }
        $where['class_id'] = $classId;
        $where['send_teacherid'] = $teacherId;
        if (SCHOOL_CLASS == $classInfo['class_status']) {
            $where['course_id'] = $courseId;
        }
        $saveData['handsoff_status'] = $flag;
        $destTeacherId = true;
        if (CLASSSTATE_RECEIVED == $flag) {
            $handsOffInfo = $handsOffModel->where($where)->find();
            if(empty($handsOffInfo))
            {
                return false;
            }
            $destTeacherId = $handsOffInfo['dest_teacherid'];
            $classTeacherWhere = $where;
            unset($classTeacherWhere['send_teacherid']);
            $classTeacherWhere['teacher_id'] = $teacherId;
            $saveClassTeacherData['teacher_id'] = $destTeacherId;
            $bResult &= $classTeacherModel->where($classTeacherWhere)->save($saveClassTeacherData);
        }
        $bResult &= $handsOffModel->where($where)->save($saveData);
        if ($bResult !== false) {
            $this->model->commit();
            $handsOffModel->commit();
            $classTeacherModel->commit();
            return $destTeacherId;
        } else {
            $this->model->rollback();
            $handsOffModel->rollback();
            $classTeacherModel->rollback();
            return false;
        }
    }

    public function getJoinClassList($studentId)
    {
        $classStudentModel = M('biz_class_student');
        $result = $classStudentModel->where('student_id=' . $studentId . ' AND biz_class.is_delete = 0 AND biz_class.class_status = '.PERSONAL_CLASS)->join('biz_class ON biz_class.id = biz_class_student.class_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field("(case when biz_class.class_status = 1 then 1 else 0 end) isAuth,biz_class.id classId,concat(dict_grade.grade,biz_class.name) className,biz_class.class_code classCode,
                    case when biz_class_student.status = 1 then '待审核' when biz_class_student.status = 2 then '已通过' when biz_class_student.status = 3 then '已拒绝' end status")
            ->select();
        return $result;
    }

    public function undoStudentJoinClass($studentId, $classId, &$errorInfo)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete']) {
            $errorInfo = '班级不存在';
            return false;
        }

        $classStudentModel = M('biz_class_student');
        $classInfo = $this->model->where('id=' . $classId)->find();
        if (SCHOOL_CLASS == $classInfo['class_status']) {
            $errorInfo = '班级不是教师自建班';
            return false;
        }
        $where['class_id'] = $classId;
        $where['student_id'] = $studentId;

        $classStudentInfo = $classStudentModel->where($where)->find();
        if (STUDENTJOINSTATE_UNAUTH != $classStudentInfo['status']) {
            $errorInfo = '教师已经审核通过状态,无法撤消';
            return false;
        }
        return $classStudentModel->where($where)->delete();

    }

    /*
     * 得到班级课表
     */
    public function getClassTimetable($id)
    {
        $model = $this->model;
        $sql = "select class_id,content,comments from biz_class_timetable where class_id=" . "'$id'";
        $result = $model->query($sql);
        if (empty($result)) {
            return array();
        } else {
            return $result[0];
        }
    }

    /*
    * 获得班级相关的信息
   * id        班级ID
    */
    public function getGradeClass($id)
    {
        $ClassModel = $this->model;
        $result = $ClassModel
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=" . "'$id'")
            ->find();
        return $result;
    }

    /*
     * 某个班级的学生学生数加1
     */
    public function addClassStudent($class_id)
    {
        $ClassModel = $this->model;
        $ClassModel->where('id=' . $class_id)->setInc('student_count');
    }


    /*
     * 获得所有班级数据
     */
    public function getClassDataAll($condition = array(), $order = 'desc')
    {
        $sql = $this->model->where($condition)->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->field('biz_class.id,biz_class.name class_name,class_code,class_status,biz_class.flag,dict_grade.grade,'
                . 'dict_schoollist.id school_id,dict_schoollist.school_name,auth_teacher.name teacher_name,auth_teacher.telephone,auth_teacher.id teacher_id,biz_class.create_at')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->group('biz_class.id')
            ->order('biz_class.create_at ' . $order)
            ->buildsql();
        $school_model = M('Dict_schoollist');
        $result = $school_model->join('auth_teacher on auth_teacher.school_id=dict_schoollist.id')->join('right join' . $sql . 'temp on temp.teacher_id=auth_teacher.id')->field('temp.*,'
            . 'ifnull(temp.school_id,dict_schoollist.id) school_id,ifNUll(temp.school_name,dict_schoollist.school_name) school_name')
            ->order('temp.create_at ' . $order)->select();
        return $result;
    }


    /*
     * 获得所有班级
     */
    public function getClassCount($condition = array())
    {
        $result = $this->model->where($condition)->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->group('biz_class.id')
            ->field('biz_class.id')
            ->select();
        return count($result);
    }


    /*
     * 获得某个学校下校内班级的数量
     */
    public function getClassCountBySchool($school_id)
    {
        $where['school_id'] = $school_id;
        $where['class_status'] = 1;
        $result = $this->model->where($where)->field('1')->select();
        return count($result);
    }


    /*
     * 获得班级的数据
     */
    public function getClassData($condition = array(), $order = 'desc', $where = array())
    {
        $count = $this->getClassCount($condition);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $sql = $this->model->where($condition)->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->field('biz_class.id,biz_class.name class_name,class_code,class_status,biz_class.flag,dict_grade.grade,'
                . 'dict_schoollist.id school_id,dict_schoollist.school_name,dict_schoollist.flag school_flag,auth_teacher.name teacher_name,'
                . 'auth_teacher.telephone,auth_teacher.id teacher_id,biz_class.create_at,dict_schoollist.provice_id,dict_schoollist.city_id,dict_schoollist.district_id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->group('biz_class.id')
            ->buildsql();
        $school_model = M('Dict_schoollist');
        $result = $school_model->where($where)->join('auth_teacher on auth_teacher.school_id=dict_schoollist.id')->join('right join' . $sql . 'temp on temp.teacher_id=auth_teacher.id')->field('temp.*,'
            . 'ifnull(temp.school_id,dict_schoollist.id) school_id,ifNUll(temp.school_name,dict_schoollist.school_name) school_name,'
            . 'ifNUll(temp.school_flag,dict_schoollist.flag) school_flag')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('temp.create_at ' . $order)
            ->select();                     //echo $school_model->getLastsql();die;
        $data['count'] = $count;
        $data['page'] = $show;
        $data['data'] = $result;
        return $data;
    }


    /*
     * 获得班级校建班和个人班所有数据
     */
    public function getAllClassData($condition = array(), $order = 'desc')
    {
        $sql = $this->model->where($condition)->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->field('biz_class.id,biz_class.name class_name,class_code,class_status,biz_class.flag,dict_grade.grade,'
                . 'dict_schoollist.id school_id,dict_schoollist.school_name,auth_teacher.name teacher_name,auth_teacher.telephone,auth_teacher.id teacher_id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id and is_handler=1', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id', 'left')
            ->group('biz_class.id')
            ->order('biz_class.create_at ' . $order)
            ->buildsql();
        $school_model = M('Dict_schoollist');
        $result = $school_model->join('auth_teacher on auth_teacher.school_id=dict_schoollist.id')->join('right join' . $sql . 'temp on temp.teacher_id=auth_teacher.id')->field('temp.*,'
            . 'ifnull(temp.school_id,dict_schoollist.id) school_id,ifNUll(temp.school_name,dict_schoollist.school_name) school_name')->select();
        return $result;
    }


    /*
     * 更改个人班课表信息
     * $class_status    
     */
    public function updateTimeTableData($class_id, $data)
    {
        $personalClassTimeTable = $this->getPsrsonalClassTimeTable($class_id);
        $model = M('biz_class_timetable');
        if (empty($personalClassTimeTable)) {
            $data['create_at'] = time();
            if (!$model->add($data)) {
                return false;
            } else {
                return true;
            }
        } else {
            $where['class_id'] = $class_id;
            if ($model->where($where)->save($data) === false) {
                return false;
            } else {
                return true;
            }
        }
    }


    /*
     * 获得某班级(自建班)课表信息
     */
    public function getPsrsonalClassTimeTable($class_id)
    {
        $model = M('biz_class_timetable');
        $where['class_id'] = $class_id;
        $result = $model->where($where)->find();
        return $result;
    }


    /*
     * 更改班级信息
     */
    public function updateClassInfo($id, $data)
    {
        $where['id'] = $id;
        if ($this->model->where($where)->save($data) === false) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * 更改班级状态
     */
    public function udpateClassStatus($id, $status, $school_id = 0)
    {
        if ($school_id != 0) {
            $where['school_id'] = $school_id;
        }
        $where['id'] = $id;
        $data['flag'] = $status;
        if ($this->model->where($where)->save($data)) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * 获得班级和班级学校的信息
     */
    public function getClassSchoolData($class_id, $school_id = 0)
    {
        if ($school_id != 0) {
            $where['biz_class.school_id'] = $school_id;
        }
        $where['biz_class.id'] = $class_id;
        $where['biz_class.is_delete'] = 0;
        $sql = $this->model->where($where)->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id', 'left')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id and is_handler=1', 'left')
            ->field('biz_class.id class_id,dict_schoollist.id school_id,biz_class.flag,school_name,school_code,class_code,'
                . 'class_status,dict_grade.id grade_id,dict_grade.grade,biz_class.name class_name,auth_teacher.name teacher_name,auth_teacher.telephone,auth_teacher.id teacher_id')
            ->group('biz_class.id')
            ->buildsql();
        $school_model = M('Dict_schoollist');
        $result = $school_model->join('auth_teacher on auth_teacher.school_id=dict_schoollist.id')->join('right join' . $sql . 'temp on temp.teacher_id=auth_teacher.id')
            ->field('temp.*,ifnull(temp.school_id,dict_schoollist.id) school_id,ifNUll(temp.school_name,dict_schoollist.school_name) school_name')->find();

        $address_result = $school_model->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
            ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
            ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')
            ->field('province. NAME province,city. NAME city,district. NAME district')->find();
        if (!empty($address_result)) {
            $result['province'] = $address_result['province'];
            $result['city'] = $address_result['city'];
            $result['district'] = $address_result['district'];
        }
        return $result;
    }


    /*
     * 获得一个班已经同意所有学生加入的数据
     */
    public function getClassStudentDataAll($condition = array(), $order = 'desc')
    {

        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->field('biz_class.id class_id,auth_student.id,student_name,sex,parent_tel,auth_student.flag,auth_student.email,auth_student.avatar,biz_class_student.status class_student_status,biz_class.school_id,biz_class.class_status,'
                . 'biz_class_student.joinmode')
            ->order('biz_class_student.create_at ' . $order)
            ->select();
        return $result;
    }


    /*
     * 获得一个班已经同意学生加入的学生的总数
     */
    public function getClassStudentCount($condition = array())
    {
        //$condition['biz_class_student.status']=BIZ_CLASS_STUDENT_STATUS;
        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->count('1');
        return $result;
    }


    /*
     * 获得一个班已经同意学生加入的数据
     */
    public function getClassStudentData($condition = array(), $order = 'desc')
    {
        $count = $this->getClassStudentCount($condition);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        //$condition['biz_class_student.status']=BIZ_CLASS_STUDENT_STATUS;
        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id,student_name,sex,parent_tel,auth_student.flag,biz_class_student.status class_student_status')
            ->order('biz_class_student.create_at ' . $order)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $data['page'] = $show;
        $data['data'] = $result;
        $data['count'] = $count;
        return $data;
    }


    /*
     * 获得一个班级下的教师任教的学科
     */
    public function getClassTeacherCourse($condition = array(), $group_flag = '', $order = 'desc')
    {
        $group_string = "dict_course.id";
        if ($group_flag != '') {
            $group_string = "auth_teacher.id";
        }
        $result = $this->model->where($condition)->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->join('dict_course on dict_course.id=biz_class_teacher.course_id', 'left')
            ->field('biz_class.id class_id,auth_teacher.id teacher_id,auth_teacher.name teacher_name,dict_course.id course_id,course_name')
            ->group($group_string)
            ->order('auth_teacher.create_at ' . $order)
            ->select();
        return $result;
    }


    /*
     * 获得一个班级下的教师信息
     */
    public function getClassTeacher($condition = array(), $order = 'desc')
    {

        $result = $this->model->where($condition)->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->join('dict_course on dict_course.id=biz_class_teacher.course_id', 'left')
            ->field('auth_teacher.id,auth_teacher.name teacher_name,auth_teacher.sex,telephone,group_concat(dict_course.course_name separator "、") course_name,auth_teacher.flag,role,avatar teacher_avatar')
            ->group('auth_teacher.id')
            ->order('auth_teacher.create_at ' . $order)
            ->select();
        return $result;
    }


    /*
     * 获得一个班级下学生的家长总数
     */
    public function getClassParentCount($condition = array(), $having = '')
    {
        //$where['biz_class_student.status']=BIZ_CLASS_STUDENT_STATUS;
        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->join('auth_student_parent_contact on auth_student_parent_contact.student_id=auth_student.id')
            ->join('auth_parent on auth_parent.id=auth_student_parent_contact.parent_id', 'left')
            ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id', 'left')
            ->group('auth_parent.id')
            ->field('1')
            ->select();
        return count($result);
    }


    /*
     * 获得一个班级下学生的家长数据
     */
    public function getClassParentData($condition = array(), $order = 'desc', $having = '')
    {
        $count = $this->getClassParentCount($condition);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        //$condition['biz_class_student.status']=BIZ_CLASS_STUDENT_STATUS;
        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->join('auth_student_parent_contact on auth_student_parent_contact.student_id=auth_student.id')
            ->join('auth_parent on auth_parent.id=auth_student_parent_contact.parent_id', 'left')
            ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id', 'left')
            ->field('auth_parent.id parent_id,auth_parent.parent_name,auth_parent.sex,auth_student.id student_id,auth_student.student_name,'
                . 'auth_id,auth_end_time,auth_student_parent_contact.parent_tel,auth_parent.flag,'
                . 'case when auth_id=4 and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->group('auth_parent.id')
            ->having($having)
            ->order('auth_student_parent_contact.create_at ' . $order)
            ->select();     

        $data['page'] = $show;
        $data['data'] = $result;
        $data['count'] = $count;
        return $data;
    }


    /*
     * 获得一个班下的学生的所有家长数据
     */
    public function getClassParentDataAll($condition = array(), $order = 'desc', $having = '')
    {
        $result = $this->model->where($condition)->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join('auth_student on biz_class_student.student_id=auth_student.id')
            ->join('auth_student_parent_contact on auth_student_parent_contact.student_id=auth_student.id')
            ->join('auth_parent on auth_parent.id=auth_student_parent_contact.parent_id', 'left')
            ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id', 'left')
            ->field('auth_parent.id parent_id,auth_parent.parent_name,auth_parent.sex,auth_student.id student_id,auth_student.student_name,'
                . 'auth_id,auth_end_time,auth_student_parent_contact.parent_tel,auth_parent.flag,'
                . 'case when auth_id=4 and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
            ->group('auth_parent.id')
            ->having($having)
            ->order('auth_student_parent_contact.create_at ' . $order)
            ->select();
        return $result;
    }


    /*
     * 添加班级信息
     */
    public function addClass($data)
    {
        if ($insert_id = $this->model->add($data)) {
            return $insert_id;
        } else {
            return false;
        }
    }


    /*
     * 插入教师关联班级表
     */
    public function addClassTeacher($data)
    {
        $model = M('biz_class_teacher');
        if ($insert_id = $model->add($data)) {
            return $insert_id;
        } else {
            return false;
        }
    }

    /*
     * 加入学生班级
     */
    public function addClassStudentData($data)
    {
        $model = M('biz_class_student');
        if ($model->add($data)) {
            return true;
        } else {
            return false;
        }
    }


    /*
     *  获得最大的班级ID
     */
    public function getClassMaxId()
    {
        $result = $this->model->field('max(id) id')->find();
        return $result['id'];
    }

    /*
     * 获取该学校下所有班级
     */
    public function getAvailableClasses($schoolId)
    {
        $where['school_id'] = $schoolId;
        $where['class_status'] = SCHOOL_CLASS;
        $where['biz_class.is_delete'] = 0;
        return $this->model->where($where)
            ->join('dict_grade ON dict_grade.id = biz_class.grade_id')
            ->field("group_concat(biz_class.id SEPARATOR '.') classId ,dict_grade.grade,group_concat(biz_class.name SEPARATOR '.') name ")
            ->group('grade_id')
            ->select();
    }

    /*
     * 获取根据教师ID获取自建班级
     *
     */
    public function getAvailableTeacherClasses($teacherId)
    {
        $where['biz_class.class_status'] = PERSONAL_CLASS;
        $where['biz_class.is_delete'] = 0;
        $where['_string'] = "(biz_class_teacher.teacher_id = $teacherId AND biz_class_teacher.is_handler = 1)";
        return $this->model->where($where)->join('biz_class_teacher ON biz_class.id = biz_class_teacher.class_id')
            ->join('dict_grade ON dict_grade.id = biz_class.grade_id')
            ->field("group_concat(biz_class.id SEPARATOR '.') classId ,dict_grade.grade,group_concat(biz_class.name SEPARATOR '.') name ")
            ->group('grade_id')
            ->select();
    }

    /*
     * 判断学生是否已经加校建班
     *
     */
    public function studentHasJoinedSchoolClasses($studentId)
    {
        $where['biz_class.class_status'] = SCHOOL_CLASS;
        $where['biz_class_student.student_id'] = $studentId;
        return $this->model->where($where)
            ->join('biz_class_student ON biz_class_student.class_id = biz_class.id')
            ->field('1')
            ->find();

    }

    /*
     * 移除某个班级的某个学生
     */
    public function removeClassStudentById($id, $student_id)
    {
        $model = M('biz_class_student');
        $delete_con['class_id'] = $id;
        $delete_con['student_id'] = $student_id;
        if ($model->where($delete_con)->delete()) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * 修改班级学生数量加一或减一
     */
    public function updateClassStudentCount($class_id, $flag = 0)
    {
        $where['id'] = $class_id;
        if ($flag != 0) {
            if ($this->model->where($where)->setInc('student_count')) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->model->where($where)->setDec('student_count')) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
    * 获取班级课表信息
    */
    public function getClassTimeTableInfo($classId, &$errorInfo)
    {
        $classInfo = $this->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            $errorInfo = '班级不存在';
            return false;
        }
        if (PERSONAL_CLASS == $classInfo['class_status']) {
            $tableModel = M('biz_class_timetable');
            $where['class_id'] = $classId;
            $result = $tableModel->where($where)->find();
            if (empty($result) || '' == ($result['content']))
                return array('empty');
            return $result;
        } else if (SCHOOL_CLASS == $classInfo['class_status']) {
            $tableModel = M('biz_class_school_timetable');
            $where['class_id'] = $classId;
            $tableResult = $tableModel->where($where)
                ->join('auth_teacher ON auth_teacher.id = biz_class_school_timetable.teacher_id')
                ->join('dict_course ON dict_course.id = biz_class_school_timetable.course_id')
                ->field('biz_class_school_timetable.day_id,biz_class_school_timetable.lesson_id,auth_teacher.id teacher_id,'
                    . 'auth_teacher.name,dict_course.id course_id,dict_course.course_name course')
                ->select();
            $commentResult = M('biz_class_school_timetable_comments')->where($where)->find();
            $commentResult = $commentResult['comments'];
            return array('data' => $tableResult, 'comments' => $commentResult);
        }
    }

    /*
     * 获取教师课表信息
     */
    public function getTeacherTimeTableInfo($classId = 0, $teacherId, &$errorInfo)
    {
        if (0 != $classId) {
            $classInfo = $this->getClassInfo($classId);
            if (1 == $classInfo['is_delete'] || empty($classInfo)) {
                $errorInfo = '班级不存在';
                return false;
            }
            if (PERSONAL_CLASS == $classInfo['class_status']) {
                $tableModel = M('biz_class_timetable');
                $where['class_id'] = $classId;
                $result = $tableModel->where($where)->field('content_teacher content,comments_teacher comments')->find();
                if (empty($result) || '' == ($result['content'])) {
                    return array('empty');
                }


                return $result;
            }

        }
        $tableModel = M('biz_class_school_timetable');
        $where['teacher_id'] = $teacherId;
        $tableResult = $tableModel->where($where)
            ->join('biz_class ON biz_class.id = biz_class_school_timetable.class_id')
            ->join('dict_grade ON dict_grade.id = biz_class.grade_id')
            ->join('dict_course ON dict_course.id = biz_class_school_timetable.course_id')
            ->field('biz_class_school_timetable.day_id,biz_class_school_timetable.lesson_id,dict_course.course_name course,concat(dict_grade.grade,biz_class.name) classname')
            ->select();

        return array('data' => $tableResult);
    }

    /*
     * 设置班级课表
     * 校建班传递参数:
     *   $classId 班级ID
     *   $Info 课程表字符串数组
     *         格式:"星期ID,节ID,教师ID,学科ID"
     *              如:array('1,1,1,3','2,2,3,4')
     *   $comments 课程表附加信息
     *   $classStatus 班级类型 个人班--PERSONAL_CLASS 校建班--SCHOOL_CLASS              个人课表有问题???
     *   $errorInfo 输出错误字符串
     */

    public function setClassTimeTable($classId, $Info, $comments, $classStatus, &$errorInfo)
    {
        if (PERSONAL_CLASS == $classStatus) {
            if (empty($Info['content'])) {
                $errorInfo = '自建班课表格式不正确';
                return false;
            }
            $model = M('biz_class_timetable');
            $where['class_id'] = $classId;
            $data['content'] = $Info['content'];
            $data['comments'] = $Info['comments'];
            $data['comments'] = $Info['comments'];
            if ($model->where($where)->find()) {
                if ($model->where($where)->save($data)) {
                    $errorInfo = '更新课表失败';
                    return false;
                }

            } else {
                $data['class_id'] = $classId;
                if (!$model->add($data)) {
                    $errorInfo = '添加课表失败';
                    return false;
                }
            }
        } else  //SCHOOL CLASS
        {
            //info:day lesson teacher course arraystring eg.:1,2,3,4
            for ($i = 0; $i < count($Info); $i++) {
                $str = '(' . $classId . ',' . $Info[$i] . ')';
                $values[] = $str;
            }
            $valueString = implode(',', $values);
            $tableModel = M('biz_class_school_timetable');
            $commentsModel = M('biz_class_school_timetable_comments');
            $bResult = true;
            //$tableModel->startTrans();
            $commentsModel->startTrans();

            $this->model->startTrans();
            $bResult &= M()->execute('INSERT INTO biz_class_school_timetable(class_id,day_id,lesson_id,teacher_id,course_id) VALUES ' . $valueString
                . ' ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id),course_id = VALUES(course_id)');
            //$this->model->commit();

            if ($bResult === false) {
                $errorInfo = '添加课表失败';
                return false;
            }
            $where['class_id'] = $classId;
            $data['comments'] = $comments;
            if ($commentsModel->where($where)->find()) {
                $bResult &= $commentsModel->where($where)->save($data);
            } else {
                $data['class_id'] = $classId;
                $bResult &= $commentsModel->add($data);
            }

            if ($bResult !== false) {
                //$tableModel->commit();
                $commentsModel->commit();
                return true;
            } else {
                //$tableModel->rollback();
                $commentsModel->rollback();
                $errorInfo = '添加课表失败';
                return false;
            }
        }
        return true;
    }

    /*
     * 获得某个校内班,班级备注
     */
    public function getSchoolClassComment($class_id)
    {
        $model = M('biz_class_school_timetable_comments');
        $where['class_id'] = $class_id;

        $result = $model->where($where)->field('id,comments')->find();
        return $result;
    }


    /*
     * 更改校内班课表的备注信息
     */
    public function updateSchoolClassComment($id, $data)
    {
        $model = M('biz_class_school_timetable_comments');
        $where['class_id'] = $id;

        $result = $this->getSchoolClassComment($id);
        if (!empty($result)) {
            if (!$model->where($where)->save($data) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            $data['class_id'] = $id;
            if ($model->add($data)) {
                return true;
            } else {
                return false;
            }
        }


    }


    /*
    * 设置教师课表
    */
    public function setTeacherTimeTable($classId, $Info, &$errorInfo)
    {
        if (empty($Info['content'])) {
            $errorInfo = '自建班课表格式不正确';
            return false;
        }
        $model = M('biz_class_timetable');
        $where['class_id'] = $classId;
        $data['content_teacher'] = $Info['content'];
        $data['comments_teacher'] = $Info['comments'];
        if ($model->where($where)->find()) {
            if ($model->where($where)->save($data)) {
                $errorInfo = '更新课表失败';
                return false;
            }

        } else {
            $data['class_id'] = $classId;
            if (!$model->add($data)) {
                $errorInfo = '添加课表失败';
                return false;
            }
        }

        return true;
    }

    /*
    * 获取班级学科的教师列表
    */
    public function getClassTeacherList($schoolId, $classId, $courseId)
    {

        $classTeacherModel = M('biz_class_teacher');
        return $classTeacherModel->join('biz_class ON biz_class.id = biz_class_teacher.classid biz_class.id = ' . $classId . ' AND biz_class_teacher.course_id = ' . $courseId . ' AND biz.class_status=' . SCHOOL_CLASS)
            ->join('dict_schoollist ON biz_class.school_id = dict_schoollist.id AND dict_schoollist.id=' . $schoolId)
            ->join('auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
            ->field('auth_teacher.id,auth_teacher.name')
            ->select();

    }

    public function getClassListByCourseAndTeacher($courseListStr, $teacherId)
    {
        if (!empty($courseListStr) && !empty($teacherId)) {
            $where['biz_class.is_delete'] = 0;
            $result = $this->model->where($where)
                ->join("biz_class_teacher ON biz_class_teacher.teacher_id = $teacherId AND biz_class_teacher.course_id IN ($courseListStr)")
                ->group('biz_class.id')
                ->order('biz_class.id  asc')
                ->field('biz_class.id')
                ->select();
            return $result;
        } else {
            return true;
        }
    }

    public function getClassIsExistsByNameGradeTeacher($name, $grade, $teacherId)
    {
        $straddclass = $name . '-' . $grade; //传过来的

        $classmap['teacher_id'] = $teacherId;
        $classmap['class_status'] = PERSONAL_CLASS;
        $allclassinfo = M('biz_class_teacher')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->where($classmap)
            ->select();

        $is_array_teacher = array();

        foreach ($allclassinfo as $k => $v) {
            $is_array_teacher[] = $v['name'] . '-' . $v['grade_id'];
        }

        return in_array($straddclass, $is_array_teacher);

    }

    /*
    * 获取教师在某个班的任教情况
    */
    public function getTeacherClassInfo($classId, $teacherId)
    {
        $where = array();
        $where['teacher_id'] = $teacherId;
        $where['class_id'] = $classId;
        return M('biz_class_teacher')->where($where)->select();
    }

    /*
    * 根据classid判断班级是否停用
    */
    public function isClassInfoFlag($classId)
    {
        $map['id'] = $classId;
        $classinfo = M('biz_class')->where( $map )->find();

        if ( $classinfo['class_status'] == 2) { //个人班

            if ( $classinfo['flag'] != 0 ) {
                return true;
            } else {
                return false;
            }

        } else {                               //校建班
            if ( $classinfo['flag'] != 0 ) { //如果班级正常就看下学校是否正常

                $schoolmap['id'] =  $classinfo['school_id'];
                $school_status = M('dict_schoollist')->where( $schoolmap )->find();

                if ($school_status['flag'] !=0 ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }

        }
    }

    //根据classid班级id获取班级和年级信息
    public function getClassAndGradeInfo( $class_id ) {
        $map['biz_class.id'] = $class_id;
        $info = M('biz_class')
            ->join("dict_grade ON dict_grade.id = biz_class.grade_id")
            ->where($map)
            ->field('biz_class.name,dict_grade.grade,biz_class.class_status,dict_grade.id')
            ->find();

        return $info;
    }
    //根据班级id获取自建班的老师id
    public function getTeacherId( $class_id ) {
        $map['class_id'] = $class_id;
        $classinfo = M('biz_class_teacher')->where( $map )->find();
        return $classinfo;
    }

    //根据班级code获取自建班的老师id
    public function getCodeTeacherId( $class_id ) {
        $map['biz_class_teacher.class_id'] = $class_id;
        $classinfo = M('biz_class_teacher')
                    ->join("auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id")
                    ->where( $map )->find();
        return $classinfo;
    }

    //根据班级id获取所有的老师的id
    public function getTeacherIdAll( $class_id ) {
        $map['class_id'] = $class_id;
        $classinfo  = M('biz_class_teacher')->where( $map )->field('teacher_id')->select();

        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['teacher_id'],$idlist)) {
                $idlist[] = $v['teacher_id'];
            }

        }

        return $idlist;
    }

    //根据班级id获取所有的家长id
    public function getParentIdAll( $class_id ) {
        $map['biz_class_student.class_id'] = $class_id;
        $classinfo  = M('biz_class_student')
                    ->join("auth_student ON auth_student.id = biz_class_student.student_id")
                    ->where( $map )
                    ->field('auth_student.parent_id,biz_class_student.student_id')
                    ->select();
        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['parent_id'],$idlist)) {
                if (!empty($v['parent_id'])) {
                    $idlist[] = $v['parent_id'];
                }

            }

        }

        return $idlist;
    }

    //根据班级id获取所有的学生id
    public function getStudentIdAll( $class_id ) {
        $map['biz_class_student.class_id'] = $class_id;
        $map['biz_class_student.status'] = STUDENTJOINSTATE_AUTH;
        $classinfo  = M('biz_class_student')
            ->join("auth_student ON auth_student.id = biz_class_student.student_id")
            ->where( $map )
            ->field('auth_student.id,biz_class_student.student_id')
            ->select();
        $idlist = array();
        foreach ( $classinfo as $k=>$v ) {
            if ( !in_array($v['id'],$idlist)) {
                if (!empty($v['id'])) {
                    $idlist[] = $v['id'];
                }

            }

        }

        return $idlist;
    }

    //根据用户id和班级id获取移交的要发送的老师id
    public function getClassHandoff($userId,$classId) {
        $map['send_teacherid'] = $userId;
        $map['class_id'] = $classId;
        $class_handoff = M('biz_class_handsoff')->where( $map )->select();
        return $class_handoff;
    }

    //家长获取老师的寄语
    public function getTeacherMessage( $map ) {
        $message = M('biz_student_learning_path')->where( $map )->count();
        return $message;
    }

    public function getStudentSchoolClass($studentId)
    {
        $where['student_id'] = $studentId;
        $where['class_status'] = 1;
        $result =  M('biz_class_student')->join('biz_class ON biz_class.id = biz_class_student.class_id')->where($where)->field('biz_class.id')
        ->find();
        return $result['id'];
    }

    /*
    * 获取学生校建班级
    */
    public function getStudentJoinedSchoolClassList($studentId)
    {
        // 学生已加入 校建 未删除
        return M()->query("SELECT biz_class.id FROM biz_class_student 
                    JOIN biz_class ON biz_class.is_delete = 0 AND biz_class.id = biz_class_student.class_id AND biz_class.class_status = ".SCHOOL_CLASS ." AND biz_class_student.student_id = $studentId AND biz_class_student.status = ".STUDENTJOINSTATE_AUTH ."
                    GROUP BY biz_class.id" );
    }

    /**
     *描述：获取当前学校下的所有校建班
     */
    public function getClassByScholl($where = '',$where2 = ''){
        $sql = $this->model
            ->field('count(exercises_homwork_basics.id) homwordtotal,biz_class.id classid')
            /*->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('exercises_homwork_class_relation on exercises_homwork_class_relation.class_id=biz_class.id AND exercises_homwork_class_relation.grade_id = biz_class.grade_id','left')
            ->join('exercises_homwork_basics on exercises_homwork_basics.id=exercises_homwork_class_relation.work_id','left')
            ->where($where)*/
            ->join('exercises_homwork_class_relation on exercises_homwork_class_relation.class_id=biz_class.id AND exercises_homwork_class_relation.grade_id = biz_class.grade_id','left')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id')
            ->join('dict_course ON dict_course.id = exercises_homwork_basics.course_id')
            ->where($where2)
            ->group('biz_class.id')
            ->buildSql();
        $result=$this->model
            ->field('biz_class.name,count(auth_student.id) studentnumber,homwordtotal,biz_class.id classid')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id AND biz_class_student.status = 2', 'left')
            ->join('auth_student on auth_student.id=biz_class_student.student_id', 'left')


            ->join('left join' . $sql . 'temp on temp.classid=biz_class.id')
            ->group('biz_class.id')
            ->where($where)
            ->select();
        return $result;
    }

    /*
     *描述：获取当前班级所有已布置的作业按学科分组
     */
    public function getHomeworkListByClassGroupByCourse($where = [])
    {
        $result = $this->model
            ->field('dict_course.course_name,count(exercises_homwork_basics.id) count,ROUND((sum(exercises_student_homework.total_score/exercises_homwork_basics.total_score)/count(exercises_homwork_basics.id))*100) averagescore')
            ->join('exercises_homwork_class_relation on exercises_homwork_class_relation.class_id=biz_class.id AND exercises_homwork_class_relation.grade_id = biz_class.grade_id','left')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id')
            ->join('dict_course ON dict_course.id = exercises_homwork_basics.course_id')
            ->where($where)
            ->group("dict_course.id")
            ->select();
        //echo M()->getLastSql();die;
        return $result;
    }

    /*
     *描述：获取当前班级所有已布置的作业按作业分组
     */
    public function getHomeworkListByClassAndCourse($where = [],$group='',$fild='exercises_homwork_basics.name homeworkname,exercises_student_homework.total_score y')
    {
        $result = $this->model
            ->field($fild)
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('exercises_homwork_class_relation on exercises_homwork_class_relation.class_id=biz_class.id AND exercises_homwork_class_relation.grade_id = biz_class.grade_id','left')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id')
            ->join('dict_course ON dict_course.id = exercises_homwork_basics.course_id')
            ->where($where)
            ->group($group)
            ->select();
        //echo M()->getLastSql();die;
        return $result;
    }
    public function getCourseGradeById($where=''){
        $resut = $this->model
            ->field('grade,biz_class.name classname')
            ->join('INNER JOIN dict_grade ON dict_grade.id = biz_class.grade_id')
            ->where($where)
            ->select();
        return $resut;
    }

    /*
     *描述：获得学校下的所有班级
     */
    public function getAllClassBySchool($where=''){
        return $this->model
            ->where($where)
            ->join('dict_schoollist ON dict_schoollist.id = biz_class.school_id and biz_class.is_delete=0 AND biz_class.class_status = 1 and biz_class.flag=1')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('group_concat(dict_grade.grade,biz_class.name) class_name,group_concat( concat_ws("_" , dict_grade.id,biz_class.id)order by biz_class.id) grade_class')
            ->order('dict_grade.id,biz_class.id')
            ->group('biz_class.id')
            ->select();
    }
}