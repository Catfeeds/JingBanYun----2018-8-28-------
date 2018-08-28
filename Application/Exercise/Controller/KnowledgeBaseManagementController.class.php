<?php
namespace Exercise\Controller;

use Think\Controller;
use Think\Verify;

class KnowledgeBaseManagementController extends ExerciseGlobalController
{

    public $model;
    public $page_size = 20;

    //permissions
    public function __construct()
    {
        parent::__construct();
        $this->model = D('Exercises_school_term');
        $this->assign('oss_path', 'http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
    /*************************************************教材版本管理***************************************************************/
    function versionManagement()
    {
        $this->assign('parent', '知识库管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 版本管理');

        $page = getParameter('p','int',false);
        $list = D('Exercises_textbook_version')->getResourcesAll($where=array(),$page);
        $Page = new \Think\Page($list['count'], 20);
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign('list',$list['resources']);
        $this->display();
    }

    /*
     *添加版本ajax
     */
    public function addVersionAjax(){

        $data['version_name'] = getParameter('name','str');
        $data['version_name'] = preg_replace('/\s+/', '', $data['version_name']);
        //去重处理
        $where['version_name'] = $data['version_name'];
        $isExist = D('Exercises_textbook_version')->selectResourceByConditions($where);
        if($isExist){
            $this->showjson('403','版本名称已存在');die;
        }
        $addStatus = D('Exercises_textbook_version')->dataAdd($data);
        D('Exercises_textbook_version')->startTrans();
        if ($addStatus === false) {
            D('Exercises_textbook_version')->rollback();
            $this->showjson('400', '请刷新后，重试');
        } else {
            D('Exercises_textbook_version')->commit();
            $this->showjson('200');
        }
    }

    /*
     *修改版本ajax
     */
    public function saveVersionAjax(){
        $data['version_name'] = getParameter('name','str');
        $data['version_name'] = preg_replace('/\s+/', '', $data['version_name']);
        $where['id'] = getParameter('id','int');
        //去重处理
        $where2['version_name'] = $data['version_name'];
        $isExist = D('Exercises_textbook_version')->selectResourceByConditions($where2);
        if($isExist){
            $this->showjson('403','版本名称已存在');die;
        }
        $addStatus = D('Exercises_textbook_version')->dataSave($data,$where);
        //习题关联知识点表冗余字段的修改
        $redundantWhere['version_id'] = $where['id'];
        $redundantStatus = D('Exercises_Course')->redundantSave($data,$redundantWhere);
        D('Exercises_textbook_version')->startTrans();
        if ($addStatus === false || $redundantStatus === false) {
            D('Exercises_textbook_version')->rollback();
            $this->showjson('400', '请刷新后，重试');
        } else {
            D('Exercises_textbook_version')->commit();
            $this->showjson('200');
        }

    }

    /*
    *查询版本详情ajax
    */
    public function getVersionInfoAjax(){
        $id = getParameter('id','int');
        $info = D('Exercises_textbook_version')->getInfo($id);
        $this->showjson('200', '',$info);
    }

    /*************************************************分册管理***************************************************************/
    /*
     *分册管理
     */
    function fasciculeManagement()
    {
        $this->assign('parent', '知识库管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 分册管理');
        //年级
        $gradeList = $this->model->getGradeList();
        $pageIndex = 1;
        $count = 0;
        $this->assign('gradeList',$gradeList);
        $list = $this->model->getSchoolTermList($count,$pageIndex,100);
        $this->assign('list',$list);
        $this->display();
    }

    /*
     *添加分册ajax
     */
    public function addFasciculeAjax(){

        $school_term = getParameter('name','str');
        $grade_id = getParameter('grade_id','int');
        $addStatus = $this->model->dataAdd($school_term,$grade_id);
        //添加相同的年级分册禁止入库操作
        $this->model->startTrans();
        if ($addStatus === false) {
            $this->model->rollback();
            $this->showjson('400', '请刷新后，重试');
        } else {
            $this->model->commit();
            $this->showjson('200');
        }
    }

    /*
     *修改分册ajax
     */
    public function saveVFasciculeAjax(){

        $school_term = getParameter('name','str');
        $grade_id = getParameter('grade_id','int');
        $id = getParameter('id','int');
        //先查询一下是否有知识树存在，没有的话再进行修改操作
        $where = $this->model->getInfo($id);
        $wheres['grade_id'] = $where[0]['grade_id'];
        $wheres['school_term'] = $where[0]['school_term'];
        $result = D('Exercises_textbook_tree_breviary')->getList($wheres);
        if(empty($result)){
            $addStatus = $this->model->dataSave($school_term,$grade_id,$id);
            $this->model->startTrans();
            if ($addStatus === false) {
                $this->model->rollback();
                $this->showjson('400', '请刷新后，重试');
            } else {
                $this->model->commit();
                $this->showjson('200');
            }
        }else{
            $this->showjson('404', '此年级分册下有关联教材知识树，不能修改');
        }
    }

    /*
     *查询分册详情ajax
     */
    public function getInfoAjax(){
        $id = getParameter('id','int');
        $info = $this->model->getInfo($id);
        $this->showjson('200', '',$info);
    }

    /*************************************************学科管理(里面有习题类型)***************************************************************/
/*
 *知识库管理---学科首页
 */
    function subjectManagement()
    {
        $this->assign('parent', '知识库管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 学科管理');
        $list = D('Exercises_Course')->getInfoList();
        $this->assign('list',$list);
        $this->display();
    }


    /*
     * 学科的添加详情页
     */
    public function courseInfo(){
        if($_POST) {
            D('Exercises_Course')->startTrans();
            $courseName = getParameter('courseName','str');
            $courseName = preg_replace('/\s+/', '', $courseName);
            //去重处理
            $where['name'] = $courseName;
            $where['parent_id'] = 0;
               $isExist = D('Exercises_Course')->getAllResource($where);
               if($isExist){
                   $this->showjson('403','学科名称重复');die;
               }
                //添加操作
                $addStatus = D('Exercises_Course')->dataAdd($courseName);
                if ($addStatus === false) {
                    D('Exercises_Course')->rollback();
                    $this->showjson('404');
                } else {
                    D('Exercises_Course')->commit();
                    $this->showjson('200');
                }
            }
    }

    /*
     *学科的修改页面
     */
    public function saveCourseInfo(){
        $this->assign('parent', '知识库管理');
        $this->assign('parentHref', U('KnowledgeBaseManagement/subjectManagement'));
        $this->assign('parentTwo', ' >> 学科管理');
        $this->assign('parentTwoHref', U('KnowledgeBaseManagement/subjectManagement'));
        $this->assign('own', ' >> 添加/修改学科');
        $id = getParameter('id','str');
       if($_POST){
           D('Exercises_Course')->startTrans();
           $where['id'] = $id;
           $courseName['name'] = getParameter('courseName','str');
           $courseName['name'] = preg_replace('/\s+/', '', $courseName['name']);
           $isExistName = D('Exercises_Course')->getAllResource($where);
           if($isExistName['name'] !== $courseName['name']){
                //去重处理
               $wheres['name'] = $courseName['name'];
               $wheres['parent_id'] = 0;
               $isExist = D('Exercises_Course')->getAllResource($wheres);
               if($isExist){
                   $this->showjson('403','学科名称重复',$isExist);die;
               }
           }

           $saveStatus = D('Exercises_Course')->dataSave($courseName,$where);
           //习题关联知识点表冗余字段的修改
           $redundant['course_name'] = $courseName['name'];
           $redundantWhere['course_id'] = $id;
           $redundantStatus = D('Exercises_Course')->redundantSave($redundant,$redundantWhere);
           if ($saveStatus === false || $redundantStatus === false) {
               D('Exercises_Course')->rollback();
               $this->showjson('404');
           } else {
               D('Exercises_Course')->commit();
               $this->showjson('200');
           }
       }
        //根据学科ID查询详情
        $info = D('Exercises_Course')->getCourseInfo($id);
       $this->assign('info',$info);
        $this->assign('id',$id);
        $this->display('courseInfo');
    }

    /*
    *添加习题类型ajax
    */
    public function addTypeAjax(){
        $pid = getParameter('pid','int');
        $courseName = getParameter('name','str');
        $courseName = preg_replace('/\s+/', '', $courseName);
        D('Exercises_Course')->startTrans();
    //去重处理
        $where['name'] = $courseName;
        $where['parent_id'] = $pid;
        $isExist = D('Exercises_Course')->getAllResource($where);
        if($isExist){
            $this->showjson('403','习题名称重复');die;
        }
        $addStatus = D('Exercises_Course')->dataAdd($courseName,$pid);
        if ($addStatus === false) {
            D('Exercises_Course')->rollback();
            $this->showjson('404');
        } else {
            D('Exercises_Course')->commit();
            $this->showjson('200');
        }
    }

    /*
    *删除习题类型ajax
    */
    public function deleteTypeAjax(){
        $id = getParameter('id','str');
        D('Exercises_Course')->startTrans();
       // $where['id'] = $id;
        //判断习题类型是否关联习题
        $redundantWhere['home_topic_type'] = $id;
        $associatedOfExercises = D('Exercises_Course')->associatedOfExercisesByType($redundantWhere);
        if(empty($associatedOfExercises)){
            $deleteStatus = D('Exercises_Course')->detelteType($id);
            if ($deleteStatus === false) {
                D('Exercises_Course')->rollback();
                $this->showjson('404');
            } else {
                D('Exercises_Course')->commit();
                $this->showjson('200');
            }
        }else{
            $this->showjson('400','该习题类型下有关联习题，不能删除');
        }

    }

    /*
     *修改习题类型ajax
     */
    public function saveTypeAjax(){
        $id = getParameter('id','str');
        $pid = getParameter('pid','int');
        $courseName['name'] = getParameter('name','str');
        $courseName['name'] = preg_replace('/\s+/', '', $courseName['name']);
        D('Exercises_Course')->startTrans();
        //去重处理
        $wheres['name'] = $courseName['name'];
        $wheres['parent_id'] = $pid;
        $isExist = D('Exercises_Course')->getAllResource($wheres);
        if($isExist){
            $this->showjson('403','习题名称重复');die;
        }
         $where['id'] = $id;
        $saveStatus = D('Exercises_Course')->dataSave($courseName,$where);
        if ($saveStatus === false) {
            D('Exercises_Course')->rollback();
            $this->showjson('404');
        } else {
            D('Exercises_Course')->commit();
            $this->showjson('200');
        }
    }

    /*
     *查询习题类型详情
     */
    public function getTypeInfoAjax(){
        $id = getParameter('id','int');
        $info = D('Exercises_Course')->getTypeInfo($id);
        $this->showjson('200','',$info);
    }
}
