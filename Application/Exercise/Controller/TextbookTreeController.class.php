<?php
namespace Exercise\Controller;

use Think\Controller;
use Think\Verify;

class TextbookTreeController extends ExerciseGlobalController
{

    public $model;
    public $page_size = 20;

    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->model = D('Knowledge_resource');
    }

    /*
     *教材知识树列表页
     */
    public function textbookList()
    {
        set_time_limit(0);
        $this->assign('parent', '知识树管理');
        $this->assign('parentHref', U('CurriculumTree/curriculumTreeList'));
        $this->assign('own', '>> 教材知识树管理');
        //获取教材版本
        $version = D('Exercises_textbook_version')->getAll();
        //接受参数
        $versionId = getParameter('vid', 'int', false);
        $this->assign('vid', $versionId);
        //获取分册数据
        $fascicul = D('Exercises_textbook_tree_breviary')->getListFasciculeAll();
        //获取学科数据
        $course = D('Exercises_Course')->getCourseList();
//var_dump($fascicul);die;
        //根据教材版本、分册、学科筛选教材知识树数据
        foreach ($fascicul as $item) {
            foreach ($course as $k => $temp) {
                $where['version_id'] = $versionId;
                $where['grade_id'] = $item['grade_id'];
                $where['school_term'] = $item['school_term'];
                $where['course_id'] = $temp['id'];
                $treeList[$k][] = D('Exercises_textbook_tree_breviary')->getList($where);
                $treeList[$k]['list_course_id'] = $temp['id'];
            }
        }
//var_dump($treeList);die;
        $this->assign('creat_status', $treeList);
        $this->assign('version', $version);
        $this->assign('fascicul', $fascicul);
        $this->assign('course', $course);
        $this->display();
    }

    /*
     *知识树首页
     */
    function textbookTree()
    {
        $this->assign('parent', '知识树管理');
        $this->assign('parentHref', U('CurriculumTree/curriculumTreeList'));
        $this->assign('parentTwo', ' >> 教材知识树管理');
        $this->assign('parentTwoHref', 'index.php?m=Exercise&c=TextbookTree&a=textbookList&vid=1');
        $this->assign('own', ' >> 查看教材知识树');
        $version = getParameter('version', 'int', false);
        $courseId = getParameter('courseId', 'int', false);
        $fascicule = getParameter('fasciculeId', 'int', false);
        if (empty($version) || empty($courseId) || empty($fascicule)) {
            $tid = getParameter('tid', 'int');
            $where1['id'] = $tid;
            $info = D('Exercises_textbook_tree_breviary')->getList($where1);
            $version = $info['version_id'];
            $courseId = $info['course_id'];
            $fascicule = D('Exercises_school_term')->getIdByGradeTerm($info['grade_id'], $info['school_term']);
            $fascicule = $fascicule['id'];
        }
        $this->assign('version', $version);
        $this->assign('courseId', $courseId);
        $this->assign('fasciculeId', $fascicule);
        //获取学科
        $course = D('Exercises_Course')->getTypeInfo($courseId);
        //获取分册
        $where['exercises_school_term.id'] = $fascicule;
        $fascicules = D('Exercises_school_term')->getCourseGrade($where);
        //获取教材版本
        $versionName = D('Exercises_textbook_version')->getInfo($version);
        $this->assign('versions', $versionName);
        $this->ajaxPage(1);
        $this->assign('course', $course);
        $this->assign('fascicules', $fascicules);
        $this->display();
    }

    /*
     *教材知识树行为列表AJAX
     */
    public function ajaxPage($var = '')
    {
        $p = getParameter('p', 'int', false);
        //查询教材知识树用户行为
        $textBookBehavior = D('Exercises_log')->getTreeAll($count, $p, 20);
        $Page = new \Think\Page($count, 20);
        $show = $Page->show('callback');
        $this->assign('list', $textBookBehavior);
        $this->assign('page', $show);
        if ($var) {

        } else {
            $this->display('textbookIfrem');
        }

    }

    /**
     *描述：教材树知识点和课标树知识点关联和取消关联ajax操作
     */
    public function textbookTreeAssociatedCurriculumTree()
    {
        $textbookTreeId = getParameter('textbookTreeId','str');
        $data['curriculum_tree_info_id'] = getParameter('curriculumTreeId','str');

        D('Exercises_textbook_tree_breviary')->startTrans();
        $statusOne = D('Exercises_textbook_tree_breviary')->saveTextbookKnowledgePoint($textbookTreeId,$data);
        //TODO:修改关联表exercises_textbook_tree_curriculum_tree中的数据（根据教材知识树知识点id）
        if($data['curriculum_tree_info_id'] === '-1'){
            //删除
            $where['textbook_tree_info_id'] = $textbookTreeId;
            $deleteStatus = D('Exercises_textbook_tree_breviary')->delete_from_exercises_textbook_tree_curriculum_tree($where);
            if ($deleteStatus && $statusOne) {
                D('Exercises_textbook_tree_breviary')->commit();
                $data['insert_id'] = $textbookTreeId;
                $this->showjson(200, '', $data);
            } else {
                D('Exercises_textbook_tree_breviary')->rollback();
                $this->showjson(404,'关联失败，请稍后重试1');
            }
        }else{
            //先删除
            $where['textbook_tree_info_id'] = $textbookTreeId;
            D('Exercises_textbook_tree_breviary')->delete_from_exercises_textbook_tree_curriculum_tree($where);
            //后添加
           /* $data['textbook_tree_info_id'] = $textbookTreeId;
            $statusAdd = D('Exercises_textbook_tree_breviary')->add_by_exercises_textbook_tree_curriculum_tree($data);*/
            $wheres['id'] = $data['curriculum_tree_info_id'];
            $result = D('Exercises_curriculum_tree_breviary')->isEmpty($wheres);
                switch ($result[0]['level'])
                {
                    case 1:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $textbookTreeId;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        if ($status && $statusOne) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $textbookTreeId;
                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404,'关联失败，请稍后重试');
                        }
                        break;
                    case 2:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $textbookTreeId;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        $dataThree['curriculum_tree_info_id'] = $result[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $textbookTreeId;
                        $statusTwo = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        if ($status && $statusTwo && $statusOne) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $textbookTreeId;

                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404,'关联失败，请稍后重试');
                        }
                        break;
                    case 3:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $textbookTreeId;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        $dataThree['curriculum_tree_info_id'] = $result[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $textbookTreeId;
                        $statusTwo = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        $whereTwo['id'] = $result[0]['parent_id'];
                        $resultTwo = D('Exercises_curriculum_tree_breviary')->isEmpty($whereTwo);
                        $dataThree['curriculum_tree_info_id'] = $resultTwo[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $textbookTreeId;
                        $statusThree = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        if ($status && $statusTwo && $statusThree && $statusOne) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $textbookTreeId;

                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404,'关联失败，请稍后重试');
                        }
                        break;
                }
        }
       /* if($status === false || $statusAdd === false){
            D('Exercises_textbook_tree_breviary')->rollback();
            $this->showMessage('404','关联失败，请稍后重试');
        }else{
            D('Exercises_textbook_tree_breviary')->commit();
            $this->showMessage('200');
        }*/

    }


    /*
    * 获得下一级知识点
    */
    function getNextLevelKnowledge()
    {
        $level = getParameter('level', 'int', false);
        if ($level == 1) { //1获取章内容
            $where['version_id'] = getParameter('version', 'int');
            $where['course_id'] = getParameter('courseId', 'int');
            $fascicule = getParameter('fasciculeId', 'int');
            //根据分册查询年级ID和分册ID
            $gradeSchoolTermId = D('Exercises_school_term')->getInfo($fascicule);
            $where['grade_id'] = $gradeSchoolTermId[0]['grade_id'];
            $where['school_term'] = $gradeSchoolTermId[0]['school_term'];
            $where['parent_id'] = 0;
            $result = D('Exercises_textbook_tree_breviary')->textbookConcat($where);//查询所有的章操作
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key => $val) {
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where2);
                $count = count($count);
                $result[$key]['count'] = $count;
            }
        } else { //获取节、知识点、子集知识点
            $knowledge_id = getParameter('id', 'int');
            $result = D('Exercises_textbook_tree_breviary')->getTextbookKnowledgePointByParentId($knowledge_id);
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key => $val) {
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where2);
                $count = count($count);
                $result[$key]['count'] = $count;
            }
        }
        foreach ($result as $k => $item) {
            $result[$k]['knowledge_name'] = stripslashes($result[$k]['knowledge_name']);
        }
        $this->showjson(200, '', $result);
    }

    /*
     *点击保存或者上架
     */
    public function submitData()
    {
        $data = $_POST['data'];
        //根据传过来的值调用增、删、改的操作

    }

    /*
     * 删除某个知识点
     */
    public function deleteTextbookKnowledgePoint()
    {
        $id = getParameter('id', 'int');

        $where['id'] = $id;
        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        //查询要删除的知识点的详细内容
        $info = D('Exercises_textbook_tree_breviary')->isEmpty($where);
        if ($info[0]['level'] == 1) {
            $oper_name = '删除章:' . $info[0]['tree_point_name'];
        } elseif ($info[0]['level'] == 2) {
            $oper_name = '删除节:' . $info[0]['tree_point_name'];
        } elseif ($info[0]['level'] == 3) {
            $oper_name = '删除知识点:' . $info[0]['tree_point_name'];
        }
            //       $result = $this->model->getOneSimpleKnowledgePoint($id);
            if (D('Exercises_textbook_tree_breviary')->deleteTextbookKnowledgePoint($id)) {
                //删除多知识点表knowledge_resource_point中的数据。
                //获得当前ID是否是章或节。。。的ID，拼接where条件
                D('Exercises_tree_log')->insertLog($info[0]['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
                $this->showjson(200);
            }
        }

    /**
     * 描述：Ajax请求判断所删除的知识点是否关联习题
     */
    public function associatedOfExercises(){
        $id = getParameter('id','str');
        $where['id'] = $id;
        //查询要删除的知识点的详细内容
        $info = D('Exercises_textbook_tree_breviary')->isEmpty($where);
        if ($info[0]['level'] == 1) {
            $redundantWhere['chapter'] = $id;
        } elseif ($info[0]['level'] == 2) {
            $redundantWhere['festival'] = $id;
        } elseif ($info[0]['level'] == 3) {
            $redundantWhere['knowledge_id'] = $id;
        }

        $associatedOfExercises = D('Exercises_Course')->associatedOfExercises($redundantWhere);
        if(!empty($associatedOfExercises)){
            $this->showjson(400);
        }else{
            $this->showjson(200);
        }
    }

    /*
     * 添加某个知识点
     */
    public function addTextbookKnowledgePoint()
    {
        $data['tree_point_name'] = htmlspecialchars_decode(I('tree_point_name', 'str'));
        $data['textbook_tree_breviary_id'] = getParameter('textbook_tree_breviary_id', 'int');
        //$data['create_at'] = time();
        $data['sort'] = getParameter('sort', 'int');
        $data['level'] = getParameter('level', 'int');
        $data['parent_id'] = getParameter('parent_id', 'int');
        $data['curriculum_tree_info_id'] = getParameter('curriculum_tree_info_id', 'int');

        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        if ($data['level'] == 1) {
            $oper_name = '添加章:' . $data['tree_point_name'];
        } elseif ($data['level'] == 2) {
            $oper_name = '添加节:' . $data['tree_point_name'];
        } elseif ($data['level'] == 3) {
            $oper_name = '添加知识点:' . $data['tree_point_name'];
        }

        D('Exercises_textbook_tree_breviary')->startTrans();
        $insert_id = D('Exercises_textbook_tree_breviary')->addTextbookKnowledgePoint($data);
        //TODO:往exercises_textbook_tree_curriculum_tree关联表中添加数据（把课标知识树的id，如果有父级id也一并添加上和教材知识树id的关联关系）
        //先查询所要关联的课标知识树id
        $where['id'] = $data['curriculum_tree_info_id'];
        $result = D('Exercises_curriculum_tree_breviary')->isEmpty($where);
        if($insert_id){
            if(!empty($result)){
                switch ($result[0]['level'])
                {
                    case 1:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $insert_id;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        if ($status) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $insert_id;

                            D('Exercises_tree_log')->insertLog($data['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404);
                        }
                        break;
                    case 2:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $insert_id;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        $dataThree['curriculum_tree_info_id'] = $result[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $insert_id;
                        $statusTwo = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        if ($status && $statusTwo) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $insert_id;

                            D('Exercises_tree_log')->insertLog($data['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404);
                        }
                        break;
                    case 3:
                        $dataTwo['curriculum_tree_info_id'] = $data['curriculum_tree_info_id'];
                        $dataTwo['textbook_tree_info_id'] = $insert_id;
                        $status = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataTwo);
                        $dataThree['curriculum_tree_info_id'] = $result[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $insert_id;
                        $statusTwo = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        $whereTwo['id'] = $result[0]['parent_id'];
                        $resultTwo = D('Exercises_curriculum_tree_breviary')->isEmpty($whereTwo);
                        $dataThree['curriculum_tree_info_id'] = $resultTwo[0]['parent_id'];
                        $dataThree['textbook_tree_info_id'] = $insert_id;
                        $statusThree = D('Exercises_textbook_tree_breviary')  ->add_by_exercises_textbook_tree_curriculum_tree($dataThree);
                        if ($status && $statusTwo && $statusThree) {
                            D('Exercises_textbook_tree_breviary')->commit();
                            $data['insert_id'] = $insert_id;

                            D('Exercises_tree_log')->insertLog($data['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
                            $this->showjson(200, '', $data);
                        } else {
                            D('Exercises_textbook_tree_breviary')->rollback();
                            $this->showjson(404);
                        }
                        break;
                }
            }else{
                D('Exercises_textbook_tree_breviary')->commit();
                $data['insert_id'] = $insert_id;
                D('Exercises_tree_log')->insertLog($data['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
                $this->showjson(200,'',$data);
            }
        }else{
            D('Exercises_textbook_tree_breviary')->rollback();
            $this->showjson(404);
        }
    }


    /*
     * 修改某个知识点
     */
    public function updateTextbookKnowledgePoint()
    {
        $id = getParameter('id', 'int');
        $name = htmlspecialchars_decode(I('tree_point_name', 'str'));
        $sort = getParameter('sort', 'int');
        /* $result = $this->model->getOneSimpleKnowledgePoint($id);
         if (empty($result)) {
             $this->showjson(401, USER_MALICE_DATA);
         }*/
        //$data['html'] = $name;
        $data['tree_point_name'] = $name;
        $data['sort'] = $sort;

        $where['id'] = $id;
        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        //查询要删除的知识点的详细内容
        $info = D('Exercises_textbook_tree_breviary')->isEmpty($where);
        if ($info[0]['level'] == 1) {
            $oper_name = '修改章:' . $name;
            $redundantData['chapter_name'] = $name;
            $redundantWhere['chapter'] = $where['id'];
        } elseif ($info[0]['level'] == 2) {
            $oper_name = '修改节:' . $name;
            $redundantData['festival_name'] = $name;
            $redundantWhere['festival'] = $where['id'];
        } elseif ($info[0]['level'] == 3) {
            $oper_name = '修改知识点:' . $name;
            $redundantData['knowledge_name'] = $name;
            $redundantWhere['knowledge_id'] = $where['id'];
        }

        D('Exercises_textbook_tree_breviary')->startTrans();
        //习题关联知识点表冗余字段的修改
        $redundantStatus = D('Exercises_Course')->redundantSave($redundantData,$redundantWhere);
        $status = D("Exercises_textbook_tree_breviary")->saveTextbookKnowledgePoint($id, $data);
        if ($redundantStatus === false || $status === false) {
            D('Exercises_textbook_tree_breviary')->rollback();
            $this->showjson(404);
        } else {
            D('Exercises_textbook_tree_breviary')->commit();

            D('Exercises_tree_log')->insertLog($info[0]['textbook_tree_breviary_id'], NO_ABNORMAL, $oper_name, $ip, $operatorInfo['id'], $operatorInfo['name'], $oper_name, 2);
            $this->showjson(200);
        }
    }

    /*
     *判断当前教材知识树是否存在
     */
    public function isExist()
    {
        $version = getParameter('version', 'int');
        $courseId = getParameter('courseId', 'int');
        $fascicule = getParameter('fasciculeId', 'int');

        //获取分册数据
        $fasciculValue = D('Exercises_school_term')->getInfo($fascicule);
        $where['course_id'] = $courseId;
        $where['version_id'] = $version;
        $where['grade_id'] = $fasciculValue[0]['grade_id'];
        $where['school_term'] = $fasciculValue[0]['school_term'];


        $data['course_id'] = $courseId;
        $data['version_id'] = $version;
        $data['grade_id'] = $fasciculValue[0]['grade_id'];
        $data['school_term'] = $fasciculValue[0]['school_term'];
        $data['creat_status'] = getParameter('num', 'int');
//获取创建人账号和名称
        $accountInfo = $this->getUserRoleAuth();
        $data['creat_name'] = $accountInfo['name']; //名称
        $ids['exercises_account.id'] = $accountInfo['id'];
        $accountData = D('Exercises_account')->getResourcesOne($ids);
        $data['creat_account'] = $accountData['account']; //账号
        $info = D('Exercises_textbook_tree_breviary')->getList($where);
        D('Exercises_textbook_tree_breviary')->startTrans();
        if (empty($info)) {
            //创建操作并返回ID
            $insertId = D('Exercises_textbook_tree_breviary')->creatTextbookTree($data);
            if ($insertId === false) {
                D('Exercises_textbook_tree_breviary')->rollback();
            } else {
                D('Exercises_textbook_tree_breviary')->commit();
                $this->showjson('200', '', $insertId);
            }

        } else {
            //如果提交过来的状态为上架
            if ($data['creat_status'] == 1) {
                $id['id'] = $info['id'];
                $resource['creat_status'] = 1;
                if (D('Exercises_textbook_tree_breviary')->saveTextbookTree($id, $resource) === false) {
                    D('Exercises_textbook_tree_breviary')->rollback();
                } else {
                    D('Exercises_textbook_tree_breviary')->commit();
                }

            } else {
                $id['id'] = $info['id'];
                $resource['creat_status'] = 2;
                if (D('Exercises_textbook_tree_breviary')->saveTextbookTree($id, $resource) === false) {
                    D('Exercises_textbook_tree_breviary')->rollback();
                } else {
                    D('Exercises_textbook_tree_breviary')->commit();
                }
            }
            //返回ID
            $this->showjson('200', '', $info['id']);
        }
    }

    /*
     *知识点标引的预览树
     */
    public function ifreamTree()
    {
        $where['version_id'] = getParameter('version', 'int');
        $where['course_id'] = getParameter('courseId', 'int');

        //根据版本和学科查找所有的分册
        $info = D('Exercises_textbook_tree_breviary')->getCourseSchoolTermAll($where);
        //查看以当前ID的知识下点下有无子集知识点，并合并
        foreach ($info as $key => $val) {
            $where['school_term'] = $val['school_term'];
            $where['grade_id'] = $val['grade_id'];
            $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where);
            $count = count($count);
            $info[$key]['count'] = $count;
        }
        $this->assign('version', $where['version_id']);
        $this->assign('courseId', $where['course_id']);
        //获取学科
        $course = D('Exercises_Course')->getTypeInfo($where['course_id']);
        $this->assign('course', $course);
        $this->assign('fascicules', $info);
        $this->display('ExerciseIndexing/textbookTreeIfream');
    }

    /*
    * 获得所有章
    */
    function getAllChapter()
    {
        $where['version_id'] = getParameter('version', 'int');
        $where['course_id'] = getParameter('courseId', 'int');
        $where['grade_id'] = getParameter('grade_id', 'int');
        $where['school_term'] = getParameter('school_term', 'int');
        $where['parent_id'] = 0;
        $result = D('Exercises_textbook_tree_breviary')->textbookConcat($where);//查询所有的章操作
        foreach ($result as $key => $val) {
            $where2['parent_id'] = $val['id'];
            $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where2);
            $count = count($count);
            $result[$key]['count'] = $count;
        }
        $this->showjson(200, '', $result);
    }
}