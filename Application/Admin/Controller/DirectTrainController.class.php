<?php

namespace Admin\Controller;

use Think\Controller;
use Common\Common\CSV;

define('SPECIAL_COLUMN', 1);
define('QUESTION', 2);
define('REPLY', 3);
define('DELETE_TRUE', 2);
define('DELETE_FALSE', 1);
define('AUDIO', 3);
define('VIDIO', 1);
define('ARTICLE', 2);
define('WAIT', 3);
define('YES', 1);
define('NO', 2);
define('UP', 1);
define('DOWN', 2);
define('WAITUP',3);
class DirectTrainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->assign('oss_path', C('oss_path'));
    }

    /*编者专栏列表*/
    public function editorColumnList()
    {
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $direct_train = D('Direct_train');
        $where['type'] = SPECIAL_COLUMN;
        $where['delete_status'] = DELETE_FALSE;
        if ($_POST) {
            $filter['putaway_status'] = getParameter('putaway_status', 'int', false);
            $filter['status'] = getParameter('column_status', 'int', false);
            $filter['fascicule_id'] = getParameter('textbook_id', 'int', false);
            $filter['direct_train.grade_id'] = getParameter('grade_id', 'int', false);
            $filter['direct_train.course_id'] = getParameter('course_id', 'int', false);
            if (!empty($filter['putaway_status'])) {
                $where['putaway_status'] = $filter['putaway_status'];
            }
            if (!empty($filter['status'])) {
                $where['status'] = $filter['status'];
            }
            if (!empty($filter['fascicule_id'])) {
                $where['fascicule_id'] = $filter['fascicule_id'];
            }
            if (!empty($filter['direct_train.grade_id'])) {
                $where['direct_train.grade_id'] = $filter['direct_train.grade_id'];
            }
            if (!empty($filter['direct_train.course_id'])) {
                $where['direct_train.course_id'] = $filter['direct_train.course_id'];
            }
            $this->assign('putaway_status', $where['putaway_status']);
            $this->assign('column_status', $where['status']);
            $this->assign('textbook_id', $where['fascicule_id']);
            $this->assign('grade_id',  $where['direct_train.grade_id']);
            $this->assign('course_id',  $where['direct_train.course_id']);
        }
        $page = getParameter('p', 'int', false);
        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        $count = $direct_train->getSpecialColumnOne($where);
//echo M()->getLastSql();die;
        $count = count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($where as $key => $val) {
            $Page->parameter[$key] = $val;
        }
        $filed = 'direct_train.id,special_column_question_title,fascicule_id,special_column_price,status,putaway_status,dict_grade.grade,dict_course.course_name,auth_teacher.name';
        $list = $direct_train->getSpecialColumnAll($where, $filed, $page,20,'','creat_time desc');
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('list', $list);
        $this->display();
    }

    /*历史问答管理列表*/
    public function historyQuizMgmt()
    {
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $direct_train = D('Direct_train');
        $where['type'] = array('IN', QUESTION . ',' . REPLY);
        $where['delete_status'] = DELETE_FALSE;
        if ($_POST) {
            $filter['putaway_status'] = getParameter('putaway_status', 'int', false);
            $filter['status'] = getParameter('column_status', 'int', false);
            $filter['fascicule_id'] = getParameter('textbook_id', 'int', false);
            $filter['direct_train.grade_id'] = getParameter('grade_id', 'int', false);
            $filter['direct_train.course_id'] = getParameter('course_id', 'int', false);
            $filter['start'] = getParameter('start', 'str', false);
            $filter['end'] = getParameter('end', 'str', false);
            if (!empty($filter['putaway_status'])) {
                $where['putaway_status'] = $filter['putaway_status'];
            }
            if (!empty($filter['status'])) {
                $where['status'] = $filter['status'];
            }
            if (!empty($filter['fascicule_id'])) {
                $where['fascicule_id'] = $filter['fascicule_id'];
            }
            if (!empty($filter['direct_train.grade_id'])) {
                $where['direct_train.grade_id'] = $filter['direct_train.grade_id'];
            }
            if (!empty($filter['start'])) {
                $where['UNIX_TIMESTAMP(creat_time)'] = array('GT',strtotime($filter['start']));
            }elseif (!empty($filter['end'])) {
                $where['UNIX_TIMESTAMP(creat_time)'] = array('LT',strtotime($filter['end']));
            }elseif(!empty($filter['start']) && !empty($filter['end'])){
                $where['UNIX_TIMESTAMP(creat_time)'] = array('between', array(strtotime($filter['start']), strtotime($filter['end']) + 86399));
            }
            $this->assign('putaway_status', $where['putaway_status']);
            $this->assign('start', $filter['start']);
            $this->assign('end', $filter['end']);
            $this->assign('column_status', $where['status']);
            $this->assign('textbook_id', $where['fascicule_id']);
            $this->assign('grade_id', $where['grade_id']);
            $this->assign('course_id', $where['course_id']);
        }
        $page = getParameter('p', 'int', false);
        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        $count = $direct_train->getSpecialColumnOne($where);
        $count = count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($where as $key => $val) {
            if($key != 'type'){
                $Page->parameter[$key] = $val;
            }
        }
        $filed = 'direct_train.id,special_column_question_title,fascicule_id,special_column_price,status,putaway_status,dict_grade.grade,dict_course.course_name,auth_teacher.name,direct_train.ppid,direct_train.creat_time';
        $list = $direct_train->getSpecialColumnAll($where, $filed, $page,20,'','creat_time desc');
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('list', $list);
        $this->display();
    }

    /*编者管理列表*/
    public function editorMgmt()
    {
        $editor_teacher_concat = D('Editor_teacher_concat');
        $page = getParameter('page', 'int', false);
        $where['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getEditorAll($where, $page);
        $resultCount = $editor_teacher_concat->getgetEditorOne();
        $count = count($resultCount);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $this->assign('page', $show);
        //获取所有学科
        $course_model = D('Dict_course');
        $course_result = $course_model->getCourseList();
        $this->assign('list', $result);
        $this->assign('courseList', $course_result);
        $this->display();
    }

    //下载示例表格
    public function sampleDownloadFile()
    {

        $csv = new CSV();
        $filepath = "./Public/csv/editor.xls";
        $filename = '导入编者表格';
        $csv->downloadFileXls($filepath, $filename);
    }

    /*
     *编者批量上传
     */
    public function batchUpload()
    {
        set_time_limit(0);
        import("Org.Util.Ereader");
        import("Org.Util.Sreader");

        $accountModel = D('Account_auths');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $auth_teacher = D('Auth_teacher');
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('ods', 'xls', 'csv');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        // 上传单个文件
        $info = $upload->uploadOne($_FILES['file']);
        if (!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        } else {// 上传成功 获取上传文件信息
            $file_path = $upload->rootPath . $info['savepath'] . $info['savename'];
        }
        if (!empty($file_path)) {
            $Reader = new \SpreadsheetReader($file_path);
            $errordata = [];
            $totalNum = 0; //总条数
            $successTotal = 0;//成功的条数
            $loserTotal = 0;//失败的条数
            $editor_teacher_concat->startTrans();
            $lock = true;
            foreach ($Reader as $k => $Row) { //进行校验数据的正确性
                if ($k == 1) {
                    continue;
                } else {
                    $totalNum++;
                    $truedata = $this->importCheckWords($Row); //校验数据的正确性
                    if ($truedata == true) { //验证通过
                        $successTotal++;
                        //拿到手机号后遍历的查询看在auth_teacher表中是否存在
                        $result = $auth_teacher->getTeacherByTel($Row[1]);
                        //如果存在就得到auth_teacher表中的ID
                        if (!empty($result)) {
                            $adddata['teacher_id'] = $result['id'];
                        } else {
                            $data['name'] = $Row[0];
                            $data['telephone'] = $Row[1];
                            $data['password'] = sha1($Row[2]);
                            //如果不在就在auth_teacher表中创建一条数据然后拿到ID
                            $insertId = $auth_teacher->addEditTeacher($data);
                            //添加VIP操作
                            $currentTime = time();
                            $accountStatus = $accountModel->inserNode($insertId,2,4,$currentTime,$currentTime+3600*24*30*3,1);
                            if($accountStatus == false){
                                $lock = false;
                            }
                            $adddata['teacher_id'] = $insertId;
                        }
                        //插入之前查看editor_teacher_concat表中是否存在此教师
                        $check['teacher_id'] = $adddata['teacher_id'];
                        $editorTeacherInfo = $editor_teacher_concat->getgetEditorOne($check);
                        if(!empty($editorTeacherInfo)){
                            //如果为已删除状态则改为正常状态
                            if($editorTeacherInfo[0]['delete_status'] == DELETE_TRUE){
                                $editorTeacherData['delete_status'] = DELETE_FALSE;
                                $editorTeacherWhere['id'] = $editorTeacherInfo[0]['id'];
                                $editorTeacherStatus =  $editor_teacher_concat->updateEditor($editorTeacherData,$editorTeacherWhere);

                                if($editorTeacherStatus === false){
                                    $lock = false;
                                }
                            }else{
                                //如果为正常状态
                                continue;
                            }
                        }else{
                            //最后遍历的往editor_teacher_concat表中插入
                            $adddata['course_id'] = $Row[4];
                            $adddata['phase_of_studying_id'] = $Row[5];
                            $adddata['dict_publishing_house_id'] = $Row[3];
                            $statusResult = $editor_teacher_concat->addEditor($adddata);
                            if (!empty($adddata['teacher_id']) && $statusResult != false) {
                                $lock = true;
                            } else {
                                $lock = false;
                            }
                        }
                    } else { //验证不通过
                        $loserTotal++;
                        //array_push($Row,$type);
                        $errordata[$k] = $Row;
                    }

                    $errordata = array_values($errordata);
                }

            }
            unlink($file_path);

            if ($lock) {
                $editor_teacher_concat->commit();
                $res['errordata'] = json_encode($errordata);
                $res['totalNum'] = $totalNum;
                $res['successTotal'] = $successTotal;
                $res['loserTotal'] = $loserTotal;
                $this->showMessage('200','成功',$res);
            } else {
                $editor_teacher_concat->rollback();
                $this->showMessage('500','失败');
            }
        }
    }

    public function importCheckWords(&$Row)
    {
        $Row[0] = trim($Row[0]);
        $Row[1] = trim($Row[1]);
        $Row[2] = trim($Row[2]);
        $Row[3] = trim($Row[3]);
        $Row[4] = trim($Row[4]);
        $Row[5] = trim($Row[5]);

          if (empty($Row[0])) {
              $msg[] = '姓名不能为空';
              $Row = array_merge($Row, $msg);
              return false;
          }

          if (empty($Row[1])) {
              $msg[] = '账号不能为空';
              $Row = array_merge($Row, $msg);
              return false;
          }

          if (empty($Row[2])) {
              $msg[] = '密码不能为空';
              $Row = array_merge($Row, $msg);
              return false;
          }
        if (empty($Row[3])) {
            $msg[] = '版本不能为空';
            $Row = array_merge($Row, $msg);
            return false;
        }

        if (empty($Row[4])) {
            $msg[] = '学科不能为空';
            $Row = array_merge($Row, $msg);
            return false;
        }
        if (empty($Row[5])) {
            $msg[] = '学段不能为空';
            $Row = array_merge($Row, $msg);
            return false;
        }
        return true;
    }

    public function downloadError()
    {
        $errArray = $_POST['errorlist'];
        $errArray = json_decode($errArray, true);
        if (empty($errArray)) {
            die("未知错误");
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=demo.xls');
        header('Pragma: no-cache');
        header('Expires: 0');
        $title = array('姓名', '账号', '密码', '版本', '学科', '学段', '错误原因');
        echo iconv('utf-8', 'gbk', implode("\t", $title)), "\n";

        foreach ($errArray as $value) {
            echo iconv('utf-8', 'gbk', implode("\t", $value)), "\n";
        }

    }


    /*
    *编者修改
    */
    public function updateEditor()
    {
        $auth_teacher = D('Auth_teacher');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $editor_id['id'] = getParameter('editor_id', 'int');
        $teacher_id['id'] = getParameter('teacher_id', 'int');
        $teacher_idTwo = getParameter('teacher_id', 'int');
        $name = getParameter('name', 'str');
        $account = getParameter('account', 'str');
        $password = getParameter('password', 'str');
        $publishing_house_id = getParameter('publishing_house_id', 'int');
        $course_id = getParameter('course_id', 'int');
        $phase_of_studying_id = getParameter('phase_of_studying_id', 'int');
        $editor_teacher_concat->startTrans();
        //修改editor_teacher_concat表数据
        $dataEditor['course_id'] = $course_id;
        $dataEditor['phase_of_studying_id'] = $phase_of_studying_id;
        $dataEditor['dict_publishing_house_id'] = $publishing_house_id;
        $editorStatus = $editor_teacher_concat->updateEditor($dataEditor, $editor_id);
        //修改auth_teacher表数据
        $teacher['name'] = $name;
        $teacher['telephone'] = $account;
        //查找教师密码
        $teacherInfo = $auth_teacher->getTeacherInfo($teacher_id);
       if($teacherInfo['password'] == $password){
           $teacher['password'] = $password;
       }else{
           $teacher['password'] = sha1($password);
       }
//var_dump($teacherInfo,$teacher);die;
        $teacherStatus = $auth_teacher->updateInfoById($teacher, $teacher_idTwo);
        if ($teacherStatus === false && $editorStatus === false) {
            $editor_teacher_concat->rollback();
            $this->showMessage('400', '修改失败');
        } else {
            $editor_teacher_concat->commit();
            $this->showMessage('200', '修改成功');
        }
    }

    /*
     *编者详情
     */
    public function editorDetails(){
        $id = getParameter('id','int');
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $where['editor_teacher_concat.id'] = $id;
        $result = $editor_teacher_concat->getgetEditorOne($where);
        $this->showMessage('200','ok',$result[0]);
    }

    /*
   *编者身份解除
   */
    public function relieveEditor()
    {
        $editor_teacher_concat = D('Editor_teacher_concat');
        $editorId = getParameter('editorId', 'int');
        $where['id'] = $editorId;
        $data['delete_status'] = DELETE_TRUE;
        $editor_teacher_concat->startTrans();
        $status = $editor_teacher_concat->updateEditor($data, $where);
        if ($status === false) {
            $editor_teacher_concat->rollback();
            $this->showMessage('400', '解除失败');
        } else {
            $editor_teacher_concat->commit();
            $this->showMessage('200', '解除成功');
        }
    }

    /*
     *编者账号禁用启用
     */
    public function forbiddenEditor()
    {
        $auth_teacher = D('Auth_teacher');
        $id = getParameter('teacher_id', 'int');
        $data = getParameter('accountStatus', 'int');;
        $auth_teacher->startTrans();
        $status = $auth_teacher->updateEnableStatus($id, $data);
        if ($status === false) {
            $auth_teacher->rollback();
            $this->showMessage('400', '禁用失败');
        } else {
            $auth_teacher->commit();
            $this->showMessage('200', '禁用成功');
        }
    }

    /*问题标签管理列表页*/
    public function problemMgmt()
    {
        $question_tags = D('Question_tags');
        $page = getParameter('page', 'int', false);
        $where['delete_status'] = DELETE_FALSE;
        $result = $question_tags->getQuestionTagsAll($where, $page);
        $resultCount = $question_tags->getQuestionTagsOne();
        $count = count($resultCount);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign('list', $result);
        $this->display();
    }

    /*问题标签详情*/
    public function problemDetails()
    {
        // $question_tags = D('Question_tags');
        // $where['id'] = getParameter('tags_id','int');
        // $result = $question_tags->getQuestionTagsOne($where);
        // $this->showMessage('200','',$result[0]);
        $this->display();
    }

    /*问题标签管理修改*/
    public function editorsProblem($tip = false)
    {
        $question_tags = D('Question_tags');
        $where['id'] = getParameter('tags_id', 'int');
        if ($tip == false) {
            $data['tags_name'] = getParameter('tags_name', 'str');
            $check['id'] = array('NEQ', $where['id']);
            $check['tags_name'] = $data['tags_name'];
            if (!$this->isEmpty($check)) {
                $this->showMessage('402', '标签名已存在');
            }
        } else {
            $data['delete_status'] = DELETE_TRUE;
        }
        $question_tags->startTrans();
        $status = $question_tags->updateQuestionTags($data, $where);
        if ($status === false) {
            $question_tags->rollback();
            $this->showMessage('400', '修改失败');
        } else {
            $question_tags->commit();
            $this->showMessage('200', '修改成功');
        }
    }

    /*问题标签管理添加*/
    public function addProblem()
    {
        $question_tags = D('Question_tags');
        $data['tags_name'] = getParameter('tags_name', 'str');
        $check['tags_name'] = $data['tags_name'];
        if (!$this->isEmpty($check)) {
            $this->showMessage('402', '标签名已存在');
        }
        $question_tags->startTrans();
        $status = $question_tags->addQuestionTags($data);
        if ($status === false) {
            $question_tags->rollback();
            $this->showMessage('400', '添加失败');
        } else {
            $question_tags->commit();
            $this->showMessage('200', '添加成功');
        }
    }

    /*
     *查询标签名是否存在
     */
    public function isEmpty($where)
    {
        $question_tags = D('Question_tags');
        $result = $question_tags->getQuestionTagsOne($where);
        if (!empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     *描述：专栏添加
     */
    public function addSpecialColumn()
    {
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        if ($_POST) {
            $type = getParameter('type', 'int');
            $Direct_train = D('Direct_train');
            $Direct_train->startTrans();

            $data['special_column_question_title'] = getParameter('special_column_question_title', 'str');
            $data['special_column_editor_quizzer_id'] = getParameter('special_column_editor_quizzer_id', 'int');
            $data['phase_of_studying_id'] = getParameter('phase_of_studying_id','int');
            $data['course_id'] = getParameter('course_id', 'int');
            $data['grade_id'] = $_POST['grade_id'];
            $data['fascicule_id'] = $_POST['fascicule_id'];
            $data['special_column_price'] = I('special_column_price');
            $data['special_column_type'] = $type;
            $data['type'] = SPECIAL_COLUMN;
            if ($type == ARTICLE) {
                $data['special_column_article'] = I('special_column_article');
                if ($data['special_column_price'] > 0) {
                    $data['special_column_article_show'] = I('special_column_article_show');
                }
            }else{
                $data['special_column_question_reply_description'] = getParameter('special_column_question_reply_description','str',false);
            }
            $columnName = getParameter('columnName','str',false);
            $phase_of_studying_name = getParameter('phase_of_studying_name','str',false);
            $courseName = getParameter('courseName','str',false);
            $gradeName = getParameter('gradeName','str',false);
            $fasciculeName = getParameter('fasciculeName','str',false);
            $typeName = getParameter('typeName','str',false);
            $data['search_filed'] = $data['special_column_question_title'].','.$columnName.','.$phase_of_studying_name.','.$courseName.','.$gradeName.','.$fasciculeName.','.$typeName;
            $insertId = $Direct_train->addSpecialColumn($data);
            if ($insertId == false) {
                $Direct_train->rollback();
                $this->error('数据提交失败');
            }
            
            //往关联表中添加数据
            if ($type == AUDIO || $type == VIDIO) {
                $vid_array = explode(',', $_POST['vid']);
                $vid_path_array = explode(',', $_POST['vid_file_path']);
                $playerwidth = explode(',', $_POST['playerwidth']);
                $playerduration = explode(',', $_POST['playerduration']);
                $vid_fullpath_array = explode(',', $_POST['vid_fullpath']);
                $vid_image_path_array = explode(',', $_POST['vid_image_path']);
                $vid_transition_status = explode(',', $_POST['is_transition']);
                $vid_size = explode(',', $_POST['vid_fullsize']);
                $contact_resource_name = $_POST['contact_resource_name'];
                foreach ($vid_array as $key => $v) {
                    $contact_data['vid'] = $v;
                    $contact_data['playerwidth'] = $playerwidth[$key];
                    $contact_data['playerduration'] = $playerduration[$key];
                    $contact_data['vid_image_path'] = $vid_image_path_array[$key];
                    $contact_data['vid_fullpath'] = $vid_fullpath_array[$key];
                    $contact_data['is_transition'] = $vid_transition_status[$key];
                    $contact_data['vid_fullsize'] = $vid_size[$key];
                    $contact_data['file_name'] = $contact_resource_name[$key];
                    $url = $vid_path_array[$key];
                    $contact_data['resource_path'] = $url;
                    $contact_data['special_column_id'] = $insertId;
                    if ($Direct_train->insertIntoFileConcat($contact_data) == false) {
                        $Direct_train->rollback();
                        $this->error('数据提交失败');
                    }
                }
            }
            $Direct_train->commit();
            $this->redirect('DirectTrain/editorColumnList');
        }
        //获取所有专栏作者
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getgetEditorOne($where);
        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('editors_list', $result);
        $this->display('editorsColumn');
    }

    /*
    *描述：专栏修改
    */
    public function saveSpecialColumn()
    {
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $direct_train = D('Direct_train');
        $id = getParameter('id', 'int');
        $condition['special_column_id'] = $id;
        $check['direct_train.id'] = $id;
        if ($_POST) {
            $type = getParameter('type', 'int');
            $ids = array();
            $Direct_train = D('Direct_train');
            $Direct_train->startTrans();

            $data['special_column_question_title'] = getParameter('special_column_question_title', 'str');
            $data['special_column_editor_quizzer_id'] = getParameter('special_column_editor_quizzer_id', 'int');
            $data['phase_of_studying_id'] = getParameter('phase_of_studying_id','int');
            $data['course_id'] = getParameter('course_id', 'int');
            $data['grade_id'] = $_POST['grade_id'];
            $data['fascicule_id'] = $_POST['fascicule_id'];
            $data['special_column_price'] = I('special_column_price');
            $data['special_column_type'] = $type;
            $data['type'] = SPECIAL_COLUMN;
            if ($type == ARTICLE) {
                $data['special_column_article'] = I('special_column_article');
                if ($data['special_column_price'] > 0) {
                    $data['special_column_article_show'] = I('special_column_article_show');
                }
            }else{
                $data['special_column_question_reply_description'] = getParameter('special_column_question_reply_description','str',false);
            }
            $columnName = getParameter('columnName','str',false);
            $phase_of_studying_name = getParameter('phase_of_studying_name','str',false);
            $courseName = getParameter('courseName','str',false);
            $gradeName = getParameter('gradeName','str',false);
            $fasciculeName = getParameter('fasciculeName','str',false);
            $typeName = getParameter('typeName','str',false);
            $data['search_filed'] = $data['special_column_question_title'].','.$columnName.','.$phase_of_studying_name.','.$courseName.','.$gradeName.','.$fasciculeName.','.$typeName;
            $data['status'] = WAIT;
            $data['putaway_status'] = WAITUP;
            $updateStatus = $Direct_train->updateSpecialColumn($data,$check);
            if ($updateStatus === false) {
                $Direct_train->rollback();
//var_dump($Direct_train->addSpecialColumn($data));die;
                $this->error('数据提交失败1');
            }
            if(!empty($_POST['vid_file_path'])){
                //删除关联表中的数据
                $deleteStatus = $Direct_train->deleteDataForFileConcat($condition);
                if ($deleteStatus === false) {
                    $Direct_train->rollback();
                    $this->error('数据提交失败2');
                }
                //再往关联表中添加数据
                if ($type == AUDIO || $type == VIDIO) {
                    $vid_array = explode(',', $_POST['vid']);
                    $vid_path_array = explode(',', $_POST['vid_file_path']);
                    $playerwidth = explode(',', $_POST['playerwidth']);
                    $playerduration = explode(',', $_POST['playerduration']);
                    $vid_fullpath_array = explode(',', $_POST['vid_fullpath']);
                    $vid_image_path_array = explode(',', $_POST['vid_image_path']);
                    $vid_transition_status = explode(',', $_POST['is_transition']);
                    $vid_size = explode(',', $_POST['vid_fullsize']);
                    $contact_resource_name = $_POST['contact_resource_name'];
                    foreach ($vid_array as $key => $v) {
                        $contact_data['vid'] = $v;
                        $contact_data['playerwidth'] = $playerwidth[$key];
                        $contact_data['playerduration'] = $playerduration[$key];
                        $contact_data['vid_image_path'] = $vid_image_path_array[$key];
                        $contact_data['vid_fullpath'] = $vid_fullpath_array[$key];
                        $contact_data['is_transition'] = $vid_transition_status[$key];
                        $contact_data['vid_fullsize'] = $vid_size[$key];
                        if(!empty($contact_resource_name)){
                            $contact_data['file_name'] = $contact_resource_name[$key];
                        }
                        $url = $vid_path_array[$key];
                        $contact_data['resource_path'] = $url;
                        $contact_data['special_column_id'] = $id;
                        if ($Direct_train->insertIntoFileConcat($contact_data) == false) {
                            $Direct_train->rollback();
                            $this->error('数据提交失败3');
                        }
                    }
                }
            }


            $Direct_train->commit();
            $this->redirect('DirectTrain/editorColumnList');
        }
        //获取当前专栏的详细信息
        $check['direct_train.id'] = $id;
        $check['type'] = SPECIAL_COLUMN;
        $details = $direct_train->getSpecialColumnOne($check);//var_dump($details);die;
        //获取当前专栏关联表的详细信息
        $resource_list = $direct_train->getDataForFileConcat($condition);//var_dump($resource_list);die;
        //获取所有专栏作者
        $editor_teacher_concat = D('Editor_teacher_concat');
        $where['delete_status'] = DELETE_FALSE;
        $result = $editor_teacher_concat->getgetEditorOne($where);
        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        $this->assign('resource_list', $resource_list);
        $this->assign('details', $details[0]);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('editors_list', $result);
        $this->display('saveEditorsColumn');
    }

    /*
     *描述：回复审核页面
     *@parms:type:1、question问题2、reply讨论
     */
    public function getReplyDetails()
    {
        $direct_train = D('Direct_train');
        $replyId = getParameter('replyId', 'int');
        $type = getParameter('type', 'str');
        $where['type'] = array('neq',SPECIAL_COLUMN);
        $check = $where;
        $where['direct_train.id'] = $replyId;

        //根据回复ID查找PPID
        $questionDetailResult = $direct_train->getChildReply($where);
//echo M()->getLastSql();die;
        if(empty($questionDetailResult[0]['ppid'])){
            $this->assign('details', $questionDetailResult[0]);
        }else{
            //根据PPID查找问题详情
            $check['direct_train.id'] = $questionDetailResult[0]['ppid'];
            $questionDetailResult = $direct_train->getChildReply($check);//echo M()->getLastSql();die;
            //获取所有的一级回复
            $parms['delete_status'] = DELETE_FALSE;
            //       $parms['putaway_status'] = UP;
            //       $parms['status'] = YES;
            $parameter['type'] = REPLY;
            $parameter['question_reply_concat_id'] = $questionDetailResult[0]['id'];
            $parameter = array_merge($parameter, $parms);
            $replyResultDetail = $direct_train->getChildReply($parameter);
            unset($check);
            foreach ($replyResultDetail as $k => $values) {
                //根据以上的所有回复ID查找每个回复ID下的所有问题和回复
                $check['pid'] = $values['id'];
                $check = array_merge($check, $parms);
                $replyResultDetail[$k]['child'] = $direct_train->getReplyAndQuestionAll($check);
            }
            $this->assign('details', $questionDetailResult[0]);
            $this->assign('list', $replyResultDetail);
        }
        $this->assign('id', $replyId);
        if ($type == 'question') {
            $this->display('questionCheck');
        } else {
            $this->display('problemDetails');
        }

    }

    /*
     *描述：专栏和历史问答的删除操作
     *@parms $type:SPECIAL_COLUMN 专栏
     */
    public function deleteOperation()
    {
        $direct_train = D('Direct_train');
        $type = getParameter('type', 'int');
        $id = getParameter('id', 'int');
        $where['direct_train.id'] = $id;
        if ($type == SPECIAL_COLUMN) {
            $check['id'] = $id;
            $data['delete_status'] = DELETE_TRUE;
            $status = $direct_train->updateSpecialColumn($data, $check);
            if ($status === false) {
                $direct_train->rollback();
                $this->showMessage('400', '失败');
            } else {
                $direct_train->commit();
                $this->showMessage('200', '成功');
            }
        } else {
            //判断是否为大问题，是的话把关联的讨论全部删除
            $questionDetailResult = $direct_train->getChildReply($where);
            if ($questionDetailResult[0]['ppid'] == 0) {
                $check['ppid'] = $questionDetailResult[0]['id'];
                $checkTwo['id'] = $id;
                $data['delete_status'] = DELETE_TRUE;
                $status = $direct_train->updateSpecialColumn($data, $check);
                $status = $direct_train->updateSpecialColumn($data, $checkTwo);
                if ($status === false) {
                    $direct_train->rollback();
                    $this->showMessage('400', '失败');
                } else {
                    $direct_train->commit();
                    $this->showMessage('200', '成功');
                }
            } else {
                $check['id'] = $id;
                $data['delete_status'] = DELETE_TRUE;
                $status = $direct_train->updateSpecialColumn($data, $check);
                if ($status === false) {
                    $direct_train->rollback();
                    $this->showMessage('400', '失败');
                } else {
                    $direct_train->commit();
                    $this->showMessage('200', '成功');
                }
            }
            //不是的话只删除自己
        }
    }

    /*
    *ajax请求审核和上下架
    */
    public function updateColumnPutawayStatus()
    {
        $direct_train = D('Direct_train');
        $id = getParameter('id', 'int');
        $where['id'] = $id;
        $direct_train->startTrans();
        $putawayStatusNumber = getParameter('putawayStatusNumber', 'int', false);
        $columnStatusNumber = getParameter('columnStatusNumber', 'int', false);
        if (!empty($putawayStatusNumber)) {
            $parms['direct_train.id'] = $id;
            $questionResult = $direct_train->getSpecialColumnOne($parms);
            if (!empty($questionResult[0]['ppid']) && $questionResult[0]['type'] != SPECIAL_COLUMN) {
                $discussionCount['id'] = $questionResult[0]['ppid'];
                if($questionResult[0]['putaway_status'] == WAITUP){
                    //增加问题的讨论量
                    $field = 'discussion_count';
                    $discussionCountStatus = $direct_train->visitAndReplyNumberSetInc($discussionCount, $field);
                    if( $discussionCountStatus === false){
                        $direct_train->rollback();
                        $this->showMessage('400', '修改失败');
                    }
                }
                //修改历史问题列表的排序时间
                $savedElement['sort_by_putaway_status_time'] = date('Y-m-d H:i:s');
                $sortByPutawayTimeStatus = $direct_train->updateSpecialColumn($savedElement,$discussionCount);
                //修改direct_train_visit_time_concat表中的数据
                $visitDataId['direct_train_id'] = $questionResult[0]['ppid'];
                $visitTime = $direct_train->getDataFormVisitTime($visitDataId);
                if ($visitTime[0]['visit_time'] < date('Y-m-d H:i:s')) {
                    $visitData['flag'] = 1;
                }else{
                    $visitData['flag'] = 0;
                }
                $visitData['updeta_time'] = date('Y-m-d H:i:s');
                $visitStatus = $direct_train->saveDataFormVisitTime($visitData, $visitDataId);
            }
            $check['putaway_status'] = $putawayStatusNumber;
            $check['putaway_status_time'] = date('Y-m-d H:i:s');
            $status = $direct_train->updateSpecialColumn($check, $where);
            if ($status === false || $visitStatus === false  || $sortByPutawayTimeStatus === false) {
                $direct_train->rollback();
                $this->showMessage('400', '修改失败');
            } else {
                $direct_train->commit();
                $this->showMessage('200', '修改成功');
            }
        }
        if (!empty($columnStatusNumber)) {
            $check['status'] = $columnStatusNumber;
            $status = $direct_train->updateSpecialColumn($check, $where);
            if ($status === false) {
                $direct_train->rollback();
                $this->showMessage('400', '修改失败');
            } else {
                $direct_train->commit();
                $this->showMessage('200', '修改成功');
            }
        }
    }

    /*
     *描述：专栏审核详情页
     */
    public function specialColumnDetails()
    {
        $direct_train = D('Direct_train');
        $grade_model = D('Dict_grade');
        $course_model = D('Dict_course');
        $specialColumnId = getParameter('specialColumnId', 'int');
        $where['direct_train.id'] = $specialColumnId;
        //获取当前专栏的详细信息
        $check['direct_train.id'] = $specialColumnId;
        $check['type'] = SPECIAL_COLUMN;
        $result = $direct_train->SpecialColumnDetails($where);//echo M()->getLastSql();die;
        //获取所有专栏作者
        $editor_teacher_concat = D('Editor_teacher_concat');
        $editor['delete_status'] = DELETE_FALSE;
        $editorResult = $editor_teacher_concat->getgetEditorOne($editor);
        //获取当前专栏的作者信息
        $editor_teacher_id['teacher_id'] = $result['special_column_editor_quizzer_id'];
        $editorTeacherDetails = $editor_teacher_concat->getgetEditorOne($editor_teacher_id);
        if ($editorTeacherDetails[0]['phase_of_studying_id'] == '1') {
            $this->assign('phase_of_studying_id', '小学');
        } elseif ($editorTeacherDetails[0]['phase_of_studying_id'] == '2') {
            $this->assign('phase_of_studying_id', '初中');
        } else {
            $this->assign('phase_of_studying_id', '高中');
        }
        //获取所有学科
        $course_result = $course_model->getCourseList();
        //获取所有年级
        $grade_result = $grade_model->getGradeList(true);
        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('editors_list', $editorResult);
        $this->assign('editors_details', $editorTeacherDetails[0]);
        $this->assign('specialColumnDetails', $result);//var_dump($result);die;
        $this->assign('id', $specialColumnId);//var_dump($result);die;
        $this->display();
    }
}
