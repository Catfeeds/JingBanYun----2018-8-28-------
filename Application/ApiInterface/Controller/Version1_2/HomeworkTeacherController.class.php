<?php
namespace ApiInterface\Controller\Version1_2;

if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false)
define('FAKE_DATA',0);


class HomeworkTeacherController extends PublicController
{

    public $pageSize = 20;
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
    }

    function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @描述：获取可布置作业的分册 章节 习题
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：category[int] N 作业类型 1--词汇 2--句子 3--视频 4--课本
     * @参数：level[int] Y 获取信息层级 1--年级分册 2--章/节 3--具体习题
     * @参数：infoId[int] N 信息ID (当level为1时,该参数传版本ID即可;当level为2时,该参数为用户所选的分册ID;level为3时传节ID)
     * @参数：courseId[int] N 学科ID 默认为英语
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getExerciseLevelInfo()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $category = getParameter('category','int',false);
        $level = getParameter('level','int');
        $infoId = getParameter('infoId','int');
        $courseId = getParameter('courseId','int',false);
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageIndex))
            $pageIndex = -1;
        if(empty($pageSize))
            $pageSize = -1;
        if(empty($courseId))
            $courseId = 3;
        if( 1 == FAKE_DATA) {
            switch ($level) {
                case 1:
                    $result[] = array( 'name' => '一年级','data'=>array(array('id'=>1,'name'=>'上册')));
                    $result[] = array( 'name' => '二年级','data'=>array(array('id'=>3,'name'=>'上册'),array('id'=>4,'name'=>'下册')));
                    $result[] = array( 'name' => '三年级','data'=>array(array('id'=>5,'name'=>'上册'),array('id'=>6,'name'=>'下册')));
                    $result[] = array( 'name' => '四年级','data'=>array(array('id'=>7,'name'=>'上册'),array('id'=>8,'name'=>'下册'),array('id'=>89,'name'=>'全一册')));
                    break;
                case 2:
                    $result[] = array( 'name' => '第1章','data'=>array(array('id'=>1,'name'=>'第1节'),array('id'=>2,'name'=>'第2节')));
                    $result[] = array( 'name' => '第2章','data'=>array(array('id'=>3,'name'=>'第3节'),array('id'=>4,'name'=>'第4节')));
                    $result[] = array( 'name' => '第3章','data'=>array(array('id'=>5,'name'=>'第5节'),array('id'=>6,'name'=>'第6节')));
                    $result[] = array( 'name' => '第4章','data'=>array(array('id'=>7,'name'=>'第7节'),array('id'=>8,'name'=>'第8节')));
                    break;
                case 3:
                    switch ($category) {
                        case EXERCISE_WORD:
                            $result[] = array('id' => 1, 'category'=>$category,'name' => 'hello', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 2, 'category'=>$category,'name' => 'hi', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 3, 'category'=>$category,'name' => 'farm', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 4, 'category'=>$category,'name' => 'bike', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 11, 'category'=>$category,'name' => 'hello', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 12, 'category'=>$category,'name' => 'hi', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 13, 'category'=>$category,'name' => 'farm', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 14, 'category'=>$category,'name' => 'bike', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 21, 'category'=>$category,'name' => 'hello', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 22, 'category'=>$category,'name' => 'hi', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 23, 'category'=>$category,'name' => 'farm', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 24, 'category'=>$category,'name' => 'bike', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            break;
                        case EXERCISE_SENTENCE:
                            $result[] = array('id' => 5, 'category'=>$category,'name' => 'A Frenchman went to a small Italian town and was staying with his wife at the best hotel there.', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 6, 'category'=>$category,'name' => 'The man was nearly out of sight when the Frenchman suddenly found that his watch was gone. He thought that it must be the Italian who had taken his watch.  ', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 7, 'category'=>$category,'name' => 'Suddenly he felt someone behind him. He turned his head and saw an Italian young man who quickly walked past him.', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 8, 'category'=>$category,'name' => 'He decided to follow him and get back the watch.', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 15, 'category'=>$category,'name' => 'A Frenchman went to a small Italian town and was staying with his wife at the best hotel there.', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 16, 'category'=>$category,'name' => 'The man was nearly out of sight when the Frenchman suddenly found that his watch was gone. He thought that it must be the Italian who had taken his watch.  ', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 17, 'category'=>$category,'name' => 'Suddenly he felt someone behind him. He turned his head and saw an Italian young man who quickly walked past him.', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            $result[] = array('id' => 18, 'category'=>$category,'name' => 'He decided to follow him and get back the watch.', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3', 'point' => '10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
        }
        else
        {
            $levelArray = array('term','festival','exercise');
            if($level < 3 && empty($courseId))
            {
                $this->showMessage( 500,'缺少courseId参数',$result);
            }

            $result = D('Exercises_textbook_tree_info_createexercise')->getGroupInfoOfExercises(0,$category,$levelArray[$level-1],$infoId,$courseId,$pageIndex,$pageSize);
            if(3 == $level)
            {
                $categoryConfig = json_decode(EXERCISE_CATEGORY,true);
                foreach($result as $key=>$val)
                {
                    $result[$key]['category_name'] = $categoryConfig[2]['data'][$val['category']]['name'];
                    $result[$key]['knowledge_code'] = base64_encode($result[$key]['knowledge_code']);
                }
            }
            else if(1 == $level || 2 == $level)
            {
                $tempResult = array();
                foreach($result as $key=>$val)
                {
                    $result[$key]['data'] = array();
                    $idArray = explode(',',$result[$key]['id']);
                    $nameArray = explode(',',$result[$key]['child']);
                    foreach($idArray as $subKey => $subVal)
                    {
                        $result[$key]['data'][] = array('id'=>$subVal,'name'=>$nameArray[$subKey]);
                    }
                    $tempResult[] = array('name'=>$val['name'],'data'=>$result[$key]['data']);
                }
                $result = $tempResult;
            }
        }

        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取可布置作业的种类
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：infoId[int] N 节ID
     *
     */
    public function getAvailableExerciseCategory()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $infoId = getParameter('infoId','int');
        $courseId = getParameter('courseId','int',false);
        if( 1 == FAKE_DATA) {
            $result = array(array('id'=>EXERCISE_WORD,'name'=>'跟读-词汇','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png'),array('id'=>EXERCISE_SENTENCE,'name'=>'跟读-句子','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png'));
        }
        else{
            $newResult = array();
            $result = D('Exercises_textbook_tree_info_createexercise')->getGroupInfoOfExercises(0,0,'exerciseCategory',$infoId);

            foreach($result as $key=>$val)
            {

               switch($val['id'])
               {
                   case EXERCISE_WORD: $newResult[] = array('id'=>EXERCISE_WORD,'name'=>'跟读-词汇','category_name'=>'跟读-词汇','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png');
                                        break;
                   case EXERCISE_SENTENCE:$newResult[] = array('id'=>EXERCISE_SENTENCE,'name'=>'跟读-句子','category_name'=>'跟读-句子','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png');
                                        break;
                   case EXERCISE_TEXTBOOK: $newResult[] = array('id'=>EXERCISE_TEXTBOOK,'name'=>'看-课本','category_name'=>'看-课本','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png');
                       break;
                   case EXERCISE_VIDEO: $newResult[] = array('id'=>EXERCISE_VIDEO,'name'=>'看-视频','category_name'=>'看-视频','url'=>'http://www.jtypt.com'.HOSTNAME.'/gendu@2x.png');
                       break;
               }
            }
            $result = $newResult;

            foreach ($result as $k=>$v) {
                //1--词汇 2--句子 3--视频 4--课本
                //if ($v[''])
                if ($v['id'] ==3 || $v['id'] ==4) {
                    $result[$k]['infoId'] = $infoId;
                } else {
                    $result[$k]['infoId'] = $infoId;
                }
            }
        }
        $this->showMessage( 200,'success',$result);
    }
    /**
     * @描述：根据习题ID，种类ID获取习题信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：category[int] Y 作业类型 1--词汇 2--句子 3--视频 4--课本
     * @参数：ids[str] Y 习题ID字符串多个用逗号连接
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getExerciseInfo()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $category = getParameter('category','int');
        $ids = getParameter('ids','str');
        if( 1 == FAKE_DATA)
        {
             switch($category) {
                 case EXERCISE_WORD:
                     $result[] = array('id' => 1, 'name' => 'hello', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 2, 'name' => 'hi', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 3, 'name' => 'farm', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 4, 'name' => 'bike', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     break;
                 case EXERCISE_SENTENCE:
                     $result[] = array('id' => 5, 'name' => 'hello world', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 6, 'name' => 'hi world ', 'translation' => '你好', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 7, 'name' => 'farm world', 'translation' => '农场', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     $result[] = array('id' => 8, 'name' => 'bike world', 'translation' => '自行车', 'url' => 'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>'10','knowledge_code'=>'4FSofSFSESSAA##EFFSSQQF');
                     break;
             }
        }
        else {
            $condition['questionId'] = $ids;
            $condition['status'] = EXERCISE_STATE_ONSHELF;
            $condition['exerciseMainCategory'] = $category;
            $oldResult = D('Exercises_question_processinfo')->getQuestionList($userId, $role, $condition);
            foreach ($oldResult as $key => $val)
            {
                $result[$key]['name'] = $oldResult[$key]['words'];
                $result[$key]['translation'] = $oldResult[$key]['analysis'];
                $result[$key]['url'] = $oldResult[$key]['subject_name'];
                $result[$key]['point'] = $oldResult[$key]['score'];
            }
        }
        foreach($result as $key=>$val)
        {
            $result[$key]['category'] = $category;
        }
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：教师发布作业
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkName[str] Y 作业名称
     * @参数：publishTime[str] Y 发布时间
     * @参数：endTime[str] Y 截止时间
     * @参数：requirement[str] Y 作业要求
     * @参数：totalScore[int] Y 作业总分
     * @参数：classList[str] Y 班级列表 多个用逗号隔开
     * @参数：exerciseList[str] Y 习题列表 {格式为字符串数组,每个元素格式为：{习题ID,知识点编码字符串,习题分数}}
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function publishHomework()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkName = getParameter('homeworkName','str');
        $publishTime = getParameter('publishTime','str');
        $endTime = getParameter('endTime','str');
        $requirement = getParameter('requirement','str',false);
        $classList = array_filter(explode(',',getParameter('classList','str')));
        $totalScore = getParameter('totalScore','int');
        $exerciseList = htmlspecialchars_decode(getParameter('exerciseList','str'));
        $exerciseList = json_decode($exerciseList);
        if(FAKE_DATA ==1)
        {
            $this->showMessage( 200,'success');
        }
        $result = true;
        //添加作业主表数据
        list($exerciseId,$null,$null) = explode(',',$exerciseList[0]);
        $exerciseInfo = D('Exercises_createexercise')->getExerciseInfo($exerciseId);
        if(empty($exerciseInfo))
        {
            $this->showMessage( 500,'习题库已更新，请重新选择习题',array());
        }

        $mainData = array();
        $mainData['name'] = $homeworkName;
        $mainData['release_time'] = strtotime($publishTime) == 0 ? $this->showMessage( 500,'发布时间格式错误',$result) :
                                    strtotime($publishTime) < time() ? date('Y-m-d H:i:s',time()) : $publishTime;
        $mainData['deadline'] = strtotime($endTime) == 0 ? $this->showMessage( 500,'截止时间格式错误',$result) :
                                strtotime($endTime) < strtotime($mainData['release_time']) ? $this->showMessage( 501,'截止时间不能小于发布时间',$result)  : $endTime;
        $mainData['type'] = HOMEWORK_AFTERCLASS; //课后作业
        $mainData['jobsments'] = $requirement;
        $mainData['exercises_num'] = sizeof($exerciseList);
        $mainData['create_user_id'] = $userId;
        $mainData['create_user_name'] = D('Auth_teacher')->getTeachInfo($userId)['name'];
        $mainData['exercises_num'] = sizeof($exerciseList);
        $mainData['total_score'] = $totalScore;
        $mainData['is_delete'] = STATE_NORMAL;
        $mainData['is_group_work'] = 0;
        $mainData['course_id'] = $exerciseInfo['subject'];
        $mainData['course_name'] = D('Exercises_Course')->getCourseName($exerciseInfo['subject'])['name'];
        M()->startTrans();
        $workId = D('Exercises_homework_basics')->addOneHomework($mainData);
        if(!$workId)
        {
            M()->rollback();
            $this->showMessage( 500,'布置作业失败',$result);
        }
        //添加班级关联
        $subData = array();
        $subAllData = array();
        $subData['work_id'] = $workId;
        foreach($classList as $key=>$classId)
        {
            if(empty($classId))
                continue;
            $classInfo = D('Biz_class')->getClassInfo(intval($classId));
            if(empty($classInfo))
                $this->showMessage( 500,'班级不存在');
            $gradeName = D('Dict_grade')->getGradeInfo($classInfo['grade_id'])['grade'];
            $subData['grade_id'] = $classInfo['grade_id'];
            $subData['class_id'] = $classId;
            $subData['class_name'] = $classInfo['name'];
            $subData['grade_name'] = $gradeName;
            $subAllData[] = $subData;
        }
        $classAddResult = D('Exercises_homework_class_relation')->addInfo($subAllData);
        if(!$classAddResult)
        {
            M()->rollback();
            $this->showMessage( 500,'布置作业失败',$result);
        }
        //添加习题关联
        $subData = array();
        $subAllData = array();
        $subData['work_id'] = $workId;
        foreach($exerciseList as $key=>$exerciseInfo)
        {
            list($exerciseId,$exerciseCode,$exercisePoint) = explode(',',$exerciseInfo);
            $subData['exercises_id'] = intval($exerciseId);
            $subData['assembly_id'] = 0;
            $subData['exercises_score'] = $exercisePoint;
            $subData['status'] = 0;
            list(
                $subData['textbook_edition'],
                $subData['subject'],
                $subData['grade'],
                $subData['section'],
                $subData['chapter'],
                $subData['festival'],
                $subData['knowledge']
                )
                = explode(',',base64_decode($exerciseCode));
            $subAllData[] = $subData;
        }
        $addResult = D('Exercises_homework_relation')->addInfo($subAllData);
        if(!$addResult)
        {
            M()->rollback();
            $this->showMessage( 500,'布置作业失败',$result);
        }
        M()->commit();
        //push message
        $studentParentList = D('Biz_class_student')->getStudentIdParentIdByClassId(implode(',',$classList));
        $parameters = array( 'msg' => array($mainData['create_user_name'],$homeworkName) ,
            'url' => array( 'type' => 0)
        );
        D('UserInfo')->sendMsg(ROLE_STUDENT,$studentParentList['studentId'],json_encode($parameters),"HOMEWORK_PUBLISHED",$mainData['release_time']);
        foreach($studentParentList['parentStudentName']  as $key=>$val){
            $parentId = $val['id'];
            $studentName = $val['name'];
            $parameters = array( 'msg' => array($mainData['create_user_name'],$studentName,$homeworkName) ,
                'url' => array( 'type' => 0)
            );
            D('UserInfo')->sendMsg(ROLE_PARENT,$parentId,json_encode($parameters),"HOMEWORK_PUBLISHED_CHILD",$mainData['release_time']);
        }



        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取我的班级作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认20
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyClassHomeworkList()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        if( 1 == FAKE_DATA)
        {
                    $result[] = array('id' => 1, 'name' => '一年级一班', 'homeworkcount' => 15);
                    $result[] = array('id' => 2, 'name' => '一年级二班', 'homeworkcount' => 16);
                    $result[] = array('id' => 3, 'name' => '一年级三班', 'homeworkcount' => 17);
                    $result[] = array('id' => 4, 'name' => '一年级四班', 'homeworkcount' => 18);
        }
        else
        {
           $condition['userId'] = $userId;
           $condition['role'] = ROLE_TEACHER;
           $result = D('Exercises_homework_class_relation')->getHomeworkCountGroupByClassId($condition);
        }
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：根据班级ID获取作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：classId[int] N 班级ID
     * @参数：type[int] N 作业类型 1--未布置 2--已布置 3--已截止
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认20
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeWorkListByClassId()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $classId = getParameter('classId','int',false);
        $type = getParameter('type','int',false);
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        if( 1 == FAKE_DATA)
        {
            $result[] = array('date' => '2017-08-30', 'data'=>array(
                array('homeworkId'=> 14,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'10/45完成','content5'=>'9月13日20:00截止'),
                array('homeworkId'=> 15,'classId'=>1,'content1' => '一年级二班','content2'=>'待布置','content3'=>'第二单元检测','content4'=>'11/45完成','content5'=>'9月14日20:00截止'),
                array('homeworkId'=> 16,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'12/45完成','content5'=>'9月15日20:00截止'),
                array('homeworkId'=> 17,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'13/45完成','content5'=>'9月16日20:00截止'),

            ));
            $result[] = array('date' => '2017-08-31', 'data'=>array(
                array('homeworkId'=> 1,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'10/45完成','content5'=>'9月13日20:00截止'),
                array('homeworkId'=> 2,'classId'=>1,'content1' => '一年级二班','content2'=>'待布置','content3'=>'第二单元检测','content4'=>'11/45完成','content5'=>'9月14日20:00截止'),
                array('homeworkId'=> 3,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'12/45完成','content5'=>'9月15日20:00截止'),
                array('homeworkId'=> 4,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'13/45完成','content5'=>'9月16日20:00截止'),
            ));
            $result[] = array('date' => '2017-07-30', 'data'=>array(
                array('homeworkId'=> 5,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'10/45完成','content5'=>'9月13日20:00截止'),
                array('homeworkId'=> 6,'classId'=>1,'content1' => '一年级二班','content2'=>'待布置','content3'=>'第二单元检测','content4'=>'11/45完成','content5'=>'9月14日20:00截止'),
                array('homeworkId'=> 7,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'12/45完成','content5'=>'9月15日20:00截止'),
                array('homeworkId'=> 8,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'13/45完成','content5'=>'9月16日20:00截止'),

            ));
            $result[] = array('date' => '2017-06-30', 'data'=>array(
                array('homeworkId'=> 9,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'10/45完成','content5'=>'9月13日20:00截止'),
                array('homeworkId'=> 10,'classId'=>1,'content1' => '一年级二班','content2'=>'待布置','content3'=>'第二单元检测','content4'=>'11/45完成','content5'=>'9月14日20:00截止'),
                array('homeworkId'=> 11,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'12/45完成','content5'=>'9月15日20:00截止'),
                array('homeworkId'=> 12,'classId'=>1,'content1' => '一年级一班','content2'=>'待布置','content3'=>'第一单元检测','content4'=>'13/45完成','content5'=>'9月16日20:00截止'),

            ));
        }
        else
        {
            if(empty($pageIndex))
                $pageIndex = 1;
            if(empty($pageSize))
                $pageSize = 4;
            if($classId === 0)
                $classId = false;
            if($type === 0)
                $type = false;
            $newResult = array();
            $subResult1 = array();
            $condition = array();
            $condition['classId'] = $classId;
            $condition['type'] = $type; //1--未布置 2--已布置 3--已截止
            $condition['role'] = ROLE_TEACHER;
            $condition['userId'] = $userId;
            $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition,$pageIndex,$pageSize);
            foreach($result as $key=>$val)
            {
                $subResult = array();
                $homeworkIdList = explode("///",$val['homework_id']);
                $classIdList = explode("///",$val['class_id']);
                $classNameList = explode("///",$val['class_name']);
                $statusList = explode("///",$val['status']);
                $nameList = explode("///",$val['name']);
                $studentCountList = explode("///",$val['student_count']);
                $finishCountList = explode("///",$val['finish_count']);
                $deadLineList = explode("///",$val['deadline']);
                foreach($classNameList as $i=>$nameValue)
                {
                    $subResult['homeworkId'] = $homeworkIdList[$i];
                    $subResult['classId'] = $classIdList[$i];
                    $subResult['content1'] = $classNameList[$i];
                    $subResult['content2'] = $statusList[$i];
                    $subResult['content3'] = $nameList[$i];
                    $subResult['content4'] = $finishCountList[$i] .'/'. $studentCountList[$i].'完成';
                    $subResult['content5'] = $deadLineList[$i] .' 截止';
                    $subResult1[] = $subResult;
                }
                $newResult[] = array('date' => $val['date'],'data'=>$subResult1);
                $subResult1 = array();
            }
            $result = $newResult;
        }
        $this->showMessage( 200,'success',$result);
    }
    /**
     * @描述：获取作业概要信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkAbstract()
    {
        define('MODE_GETRECORD_COUNT',1);
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int',false);
        if( 1 == FAKE_DATA)
        {
            $result[] = array('name' => '9月9日作业', 'content1' => '布置时间: 9月9日 11:00', 'content2' => '截止时间: 9月9日 13:00','content3'=>'十分钟内完成');
        }
        else
        {
           $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
           $newResult = array();
           $newResult['name'] = $result['name'];
           $newResult['content1'] = '布置时间: '.$result['release_time_c'];
           $newResult['content2'] = '截止时间: '.$result['end_time_c'];
            if(!empty($classId))
            {
                //未完成数量
                $unFinishCount = D('Exercises_student_homework')->getClassHomeworkStudentSubmitInfo($homeworkId,$classId,STUDENT_HOMEWORK_NOTSUBMIT,0,0,'','',MODE_GETRECORD_COUNT);
                //已完成数量
                $finishCount = D('Exercises_student_homework')->getClassHomeworkStudentSubmitInfo($homeworkId,$classId,STUDENT_HOMEWORK_SUBMITED,0,0,'','',MODE_GETRECORD_COUNT);
                $newResult['unFinishCount'] = $unFinishCount;
                $newResult['finishCount'] = $finishCount;
            }
           $result = $newResult;
        }
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：根据作业ID获取完成情况列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @参数：type[int] Y 类型 1--已提交 2--未提交
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认20
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeFinishDetail()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $type = getParameter('type','int',false);
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        if( 1 == FAKE_DATA)
        {
            $result[] = array('id'=>44,'avatar'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg','name' => '张三', 'content1' => '得分:100分', 'content2' => '9月9日 13:00提交');
            $result[] = array('id'=>45,'avatar'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg','name' => '张四', 'content1' => '得分:90分', 'content2' => '9月9日 12:00提交');
            $result[] = array('id'=>47,'avatar'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg','name' => '张五', 'content1' => '', 'content2' => '未提交');
        }
        else
        {
            $result = D('Exercises_student_homework')->getClassHomeworkStudentSubmitInfo($homeworkId,$classId,$type,$pageIndex,$pageSize);
            if(STUDENT_HOMEWORK_NOTSUBMIT != $type)
            $convertArray = array(
                array('id'=> TYPE_FIELD),array('id'),
                array('avatar' =>TYPE_FIELD) ,array('avatar'),
                array('sex' =>TYPE_FIELD) ,array('sex'),
                array('student_name' =>TYPE_FIELD) ,array('name'),
                array('得分:'=>TYPE_STRING,'total_score' =>TYPE_FIELD,'分'=>TYPE_STRING) ,array('content1'),
                array('create_at' =>TYPE_FIELD,'提交'=>TYPE_STRING) ,array('content2'),
            );
            else
                $convertArray = array(
                    array('avatar' =>TYPE_FIELD) ,array('avatar'),
                    array('sex' =>TYPE_FIELD) ,array('sex'),
                    array('student_name' =>TYPE_FIELD) ,array('name'),
                    array(''=>TYPE_STRING) ,array('content1'),
                    array('未提交'=>TYPE_STRING) ,array('content2'),
                );
            $result = fieldsCompose($result,$convertArray);
        }
        foreach($result as $key=>$val)
        {
            if (preg_match('/Resources/', $val['avatar'])){
                if(strpos($val['avatar'],'.')===false)
                {
                    $val['avatar'].='.jpg';
                }
                $result[$key]['avatar'] = C('oss_path').$val['avatar'];
            } else {
                if ($val['sex'] == '男' || empty($val['sex'])) {
                    $result[$key]['avatar'] = 'http://'.WEB_URL.'/Public/img/classManage/student_m.png';
                } else {
                    $result[$key]['avatar'] = 'http://'.WEB_URL.'/Public/img/classManage/student_w.png';
                }
            }
        }
        $this->showMessage( 200,'success',$result);
    }

    private function transExerciseData($result,$fields)
    {
        $newResult = array();
        $sub2 = array();
        $sub3 = array();
        $lastMainCategory = 0;
        $lastSubMainCategory = 0;
        $category = '';
        $fieldArray = explode(',',$fields);
        $categoryConfig = json_decode(EXERCISE_CATEGORY,true);
        foreach($result as $key=>$val)
        {
            if($category != $val['category'])
            {
                list($mainCategory,$subCategory) = explode(',',$val['category']);

                if(empty($mainCategory) || empty($subCategory))
                    return array();
                if(!empty($sub2))
                {
                    if($lastSubMainCategory != 0) {
                        $sub3[] = array('name' => $categoryConfig[$lastMainCategory]['data'][$lastSubMainCategory]['name'],
                            'data' => $sub2
                        );
                        $sub2 = array();
                    }

                }

                $lastSubMainCategory = $subCategory;

                if($lastMainCategory != $mainCategory ){
                    if(!empty($sub3)) {
                        if ($lastMainCategory != 0) {
                            if(!empty($sub2))
                            {
                                $sub3[] = array('name' => $categoryConfig[$mainCategory]['data'][$lastSubMainCategory]['name'],
                                    'data' => $sub2
                                );
                                $sub2 = array();
                            }
                            $newResult[] = array('name' => $categoryConfig[$lastMainCategory]['name'],
                                'data' => $sub3
                            );
                            $sub3 = array();
                        }
                    }
                }
                $lastMainCategory = $mainCategory;
                $category = $val['category'];
            }
            $sub1 = array();
            foreach($fieldArray as $keyIndex=>$fieldName)
            {
                $sub1[$fieldName] = $val[$fieldName];
                if($fieldName==='category')
                {
                    $sub1[$fieldName] = explode(',',$val[$fieldName])[1];
                }
            }
            $sub2[] = $sub1;

        }
        if(!empty($sub2) && !empty($mainCategory))
            $sub3[] = array('name'=>$categoryConfig[$mainCategory]['data'][$subCategory]['name'],
                'data' => $sub2
            );

        if(!empty($sub3)){
            $newResult[] = array('name'=>$categoryConfig[$mainCategory]['name'],
                'data' => $sub3
            );
        }
        return $newResult;
    }
    private function flattenData($data,$isFlatten=0)
    {
        if($isFlatten == 0)
            return $data;
        $newData = array();
        if(0 <  $isFlatten) {

            foreach ($data as $key => $val) {
                foreach ($val['data'] as $subKey => $subVal) {
                    $newData[] =  $subVal;
                }
            }
        }
        if(1 <  $isFlatten)
        {
            $newDataTemp = $newData;
            $newData = array();
            foreach ($newDataTemp as $key => $val) {
                $name = $val['name'];
                $data = $val['data'];
                $subCount = sizeof($data);
                foreach($data as $subKey => $subVal)
                {
                    $subVal['exerciseName'] =  $name;
                    $subVal['count'] = $subCount;
                    $subVal['index'] = $subKey+1;
                    $newData[] = $subVal;
                }
            }
        }
        return  $newData;
    }


    /**
     * @描述：查看作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @参数：isFlatten[int] N 是否FLATTEN返回数组 0--否 1--是
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkDetail()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $isFlatten = getParameter('isFlatten','int',false);
        if( 1 == FAKE_DATA)
        {
            $result = array(
                'total'=>11,
                'data'=> array(
                    'name' => '语音作业','data'=>array(
                    array('name'=>'跟读-词汇',
                           'data'=> array(
                               array('category'=>1,'name'=>'young','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','finishcount'=>15,'pointratio'=>'50%'),
                               array('category'=>1,'name'=>'farm','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','finishcount'=>14,'pointratio'=>'100%'),
                           )
                          ),
                    array('name'=>'跟读-句子',
                        'data'=> array(
                            array('category'=>2,'name'=>'young man','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','finishcount'=>15,'pointratio'=>'50%'),
                            array('category'=>2,'name'=>'far away','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','finishcount'=>14,'pointratio'=>'50%'),
                           ),
                          )
                          )
                )
            );
        }
        else
        {
            $resultData = D('Exercises_student_homework')->getHomeworkExerciseList($homeworkId,$classId);
            foreach($resultData as $key=>$val)
            {
                if(false === strpos($resultData[$key]['pointratio'],'--'))
                {
                    $resultData[$key]['pointratio'] = $resultData[$key]['pointratio'].'%';
                }
            }
            $result['total'] = count($resultData);
            $result['data'] = $this->transExerciseData($resultData,'id,name,url,category,translation,finishcount,pointratio');
        }
        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：查看某学生的作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：id[int] Y 学生提交作业ID
     * @参数：isFlatten[int] N 是否FLATTEN返回数组 0--否 1--是
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getStudentHomeworkDetail()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('id','int');
        $isFlatten = getParameter('isFlatten','int',false);
        $model = D('Exercises_student_homework');
        if( 1 == FAKE_DATA)
        {
            $result = array(
                    'name'=>'朱允见',
                    'submittime'=>'2017-08-30 13:00:00',
                    'duration'=>6,
                    'point'=>11,
                    'totalpoint'=>15,
                    'data'=> array(
                    array('name'=>'语音作业','data'=>array(
                    array('name'=>'跟读-词汇',
                        'data'=> array(
                            array('id'=>1,'org_answer_url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','category'=>EXERCISE_WORD,'name'=>'young','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>2),
                            array('id'=>2,'org_answer_url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','category'=>EXERCISE_WORD,'name'=>'farm','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>2),
                        )
                    ),
                    array('name'=>'跟读-句子',
                        'data'=> array(
                            array('id'=>3,'org_answer_url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','category'=>EXERCISE_SENTENCE,'name'=>'young man','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>3),
                            array('id'=>4,'org_answer_url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','category'=>EXERCISE_SENTENCE,'name'=>'far away','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/beijing_edu_pub/english/eng_1_1_v1/Pages/page4/image/2.mp3','point'=>4),
                        ),
                    )
                ))
                )
            );
        }
        else
        {
            $info = $model->getStudentHomeworkBriefInfo($id);
            $resultData = $model->getHomeworkExerciseList(0,0,$id);
            $result['submittime'] = $info['submit_at'];
            $result['duration'] = $info['work_timeout'];
            $result['point'] = $info['total_score'];
            $result['name'] = $info['student_name'];
            $result['totalpoint'] = $info['total_score_base'];
            if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false)
            {
                foreach($resultData as $key=>$val)
                {
                    $tempVar = $resultData[$key]['url'];
                    $resultData[$key]['url'] = $resultData[$key]['org_answer_url'];
                    $resultData[$key]['org_answer_url'] = $tempVar;
                }
            }
            $result['data'] = $this->transExerciseData($resultData,'id,name,url,point,org_answer_url,category');
        }
            $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取下一位学生ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：id[int] Y 学生提交作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkNextStudent()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('id','int');
        $model = D('Exercises_student_homework');
        if( 1 == FAKE_DATA)
        {
            $result = array('id'=>$id+1);
        }
        else
        {
            $info = $model->getStudentHomeworkBriefInfo($id);
            $result = $model->getNextSubmitId($id,$info['work_id'],$info['class_id']);
            if(empty($result))
                $this->showMessage( 500,'已经是最后一名学生了',$result);
        }
        $this->showMessage( 200,'success',array('id'=>$result));
    }

}
?>