<?php
namespace Exercise\Controller;

use Think\Controller;
class CurriculumTreeController extends ExerciseGlobalController
{

    public $model;
    public $page_size = 20;
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->model = D('Exercises_curriculum_tree_breviary');
    }

    /*
     *课标知识树列表页
     */
    public function curriculumTreeList(){
        $this->assign('parent', '知识树管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 课标知识树管理');
        //获取学段数据
        $learning_period = C('Studysection');
        //获取学科数据
        $course = D('Exercises_Course')->getCourseList();
//var_dump($learning_period);die;
        //根据教材版本、分册、学科筛选教材知识树数据
        foreach ($course  as $item){
            foreach ($learning_period as $k=>$temp){
                $where['Learning_period_id'] = $temp['id'];
                $where['course_id'] = $item['id'];
                $treeList[$k][] =  D('Exercises_curriculum_tree_breviary')->getList($where);
                $treeList[$k]['Learning_period_id'] = $temp['id'];
            }
        }
        $this->assign('treeList',$treeList);
        $this->assign('learning_period',$learning_period);
        $this->assign('course',$course);
        $this->display();
    }

    /*
     *课标知识树首页
     */
    function curriculumTree()
    {
        $this->assign('parent', '知识树管理');
        $this->assign('parentHref', U('CurriculumTree/curriculumTreeList'));
        $this->assign('parentTwo', ' >> 课标知识树管理');
        $this->assign('parentTwoHref', U('CurriculumTree/curriculumTreeList'));
        $this->assign('own', ' >> 查看课标知识树');
        $courseId = getParameter('courseId','int',false);
        $Learning_period_id = getParameter('Learning_period_id','int',false);
        if(empty($courseId) || empty($Learning_period_id))
        {
            $tid = getParameter('tid','int');
            $where['id']= $tid;
            $treeStatus = D('Exercises_curriculum_tree_breviary')->getList($where);
            $courseId = $treeStatus['course_id'];
            $Learning_period_id = $treeStatus['learning_period_id'];
        }else{
            $courseId = $courseId;
            $Learning_period_id = $Learning_period_id;
            $where['course_id']= $courseId;
            $where['Learning_period_id']= $Learning_period_id;
            $treeStatus = D('Exercises_curriculum_tree_breviary')->getList($where);
        }
        $this->assign('courseId',$courseId);
        $this->assign('Learning_period_id',$Learning_period_id);
        //获取学科
        $course = D('Exercises_Course')->getTypeInfo($courseId);
        $this->ajaxPage(1);
        //获取当前课标数的状态

        $this->assign('treeStatus',$treeStatus['creat_status']);
        $this->assign('course',$course);
        $this->assign('Learning_period_id',$Learning_period_id);
        $this->display();
    }

    /*
     *教材知识树关联课标知识树
     */
    function curriculumTreeConcatTextbookTree()
    {

        $courseId = getParameter('courseId','int');
        $Learning_period_id = getParameter('Learning_period_id','int');
        $versionName = getParameter('versionName','str');

        $this->assign('versionName',$versionName);
        $this->assign('courseId',$courseId);
        $this->assign('Learning_period_id',$Learning_period_id);
        //获取学科
        $course = D('Exercises_Course')->getTypeInfo($courseId);
        $this->ajaxPage(1);
        $this->assign('course',$course);
        $this->assign('Learning_period_id',$Learning_period_id);
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
            $this->display('TextbookTree/textbookIfrem');
        }

    }


    /*
    * 获得下一级知识点
    */
    function getNextLevelKnowledge()
    {
        $level = getParameter('level', 'int', false);
        if ($level == 1) { //1获取一级知识点内容
            $where['Learning_period_id'] = getParameter('Learning_period_id','int');
            $where['course_id'] = getParameter('course','int');
            $where['parent_id'] = 0;
            $result = D('Exercises_curriculum_tree_breviary')->curriculumTreeConcat($where) ;//查询所有的一级知识点操作
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key=>$val){
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_curriculum_tree_breviary')->curriculumTreeConcat($where2) ;
                $count = count($count);
                $result[$key]['count'] = $count;
            }
        } else { //二级、三级知识点
            $knowledge_id = getParameter('id', 'int');
            $result = D('Exercises_curriculum_tree_breviary')->getCurriculumKnowledgePointByParentId($knowledge_id);
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key=>$val){
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_curriculum_tree_breviary')->curriculumTreeConcat($where2) ;
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
     * 删除某个知识点
     */
    public function deleteCurriculumTreeKnowledgePoint()
    {
        $id = getParameter('id', 'int');
        $where['id'] = $id;
        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        //查询要删除的知识点的详细内容
        $info = D('Exercises_curriculum_tree_breviary')->isEmpty($where);
        if($info[0]['level'] == 1){
            $oper_name = '删除一级知识点:'.$info[0]['tree_point_name'];
        }elseif($info[0]['level'] == 2){
            $oper_name = '删除二级知识点:'.$info[0]['tree_point_name'];
        }elseif($info[0]['level'] == 3){
            $oper_name = '删除三级知识点:'.$info[0]['tree_point_name'];
        }

        //       $result = $this->model->getOneSimpleKnowledgePoint($id);
        D('Exercises_curriculum_tree_breviary')->startTrans();
        $data['curriculum_tree_info_id'] = 0;
        if (D('Exercises_textbook_tree_breviary')->saveTextbookCurriculum_tree_info_idByCuriculumTree($id,$data)  && D('Exercises_curriculum_tree_breviary')->deleteCurriculumKnowledgePoint($id)) {
            D('Exercises_curriculum_tree_breviary')->commit();
            D('Exercises_tree_log')->insertLog($info[0]['curriculum_tree_breviary_id'],1,$oper_name,$ip,$operatorInfo['id'],$operatorInfo['name'],$oper_name,2);
            $this->showjson(200);
        } else {
            D('Exercises_curriculum_tree_breviary')->rollback();
            $this->showjson(404);
        }
    }


    /*
     * 添加某个知识点
     */
    public function addCurriculumTreeKnowledgePoint()
    {
        $data['tree_point_name'] = htmlspecialchars_decode(I('tree_point_name','str'));
        $data['curriculum_tree_breviary_id'] = getParameter('curriculum_tree_breviary_id','int');
        //$data['create_at'] = time();
        $data['sort'] = getParameter('sort','int');
        $data['level'] = getParameter('level','int');
        $data['parent_id'] = getParameter('parent_id','int');

        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        if($data['level'] == 1){
            $oper_name = '添加一级知识点:'.$data['tree_point_name'];
        }elseif($data['level'] == 2){
            $oper_name = '添加二级知识点:'.$data['tree_point_name'];
        }elseif($data['level'] == 3){
            $oper_name = '添加三级知识点:'.$data['tree_point_name'];
        }

        D('Exercises_curriculum_tree_breviary')->startTrans();
        if ($insert_id = D('Exercises_curriculum_tree_breviary')->addCurriculumKnowledgePoint($data)) {
            D('Exercises_curriculum_tree_breviary')->commit();
            $data['insert_id'] = $insert_id;

            D('Exercises_tree_log')->insertLog($data['curriculum_tree_breviary_id'],1,$oper_name,$ip,$operatorInfo['id'],$operatorInfo['name'],$oper_name,2);

            $this->showjson(200, '', $data);
        } else {
            D('Exercises_curriculum_tree_breviary')->rollback();
            $this->showjson(404);
        }
    }


    /*
     * 修改某个知识点
     */
    public function updateCurriculumTreeKnowledgePoint()
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
/////////////////////////////////////////////////////////////////////////////////////////
        $where['id'] = $id;
        $ip = get_client_ip();
        $operatorInfo = $this->getUserRoleAuth();
        //查询要删除的知识点的详细内容
        $info = D('Exercises_curriculum_tree_breviary')->isEmpty($where);
        if($info[0]['level'] == 1){
            $oper_name = '修改一级知识点:'.$name;
        }elseif($info[0]['level'] == 2){
            $oper_name = '修改二级知识点:'.$name;
        }elseif($info[0]['level'] == 3){
            $oper_name = '修改三级知识点:'.$name;
        }

        D('Exercises_curriculum_tree_breviary')->startTrans();
        if (D("Exercises_curriculum_tree_breviary")->saveCurriculumKnowledgePoint($id, $data)) {
            //***********************************************这里是同步修改教材知识树的内容***********************************************************************
            //先判断要修改的ID是否关联了教材知识树
            $isEmpty = D('Exercises_Course')->getKnowledgeById($id);
                if(!empty($isEmpty)){
                    //如果关联了就修改教材知识树中的对应ID的知识点名称
                    $saveStatus = D('Exercises_textbook_tree_breviary')->saveTextbookKnowledgePointByCuriculumTree($where['id'],$data);
                   // M('exercises_textbook_tree_info')->startTrans();
                    foreach ($isEmpty as $item){
                        $id = $item['id'];
                        $redundantData['knowledge_name'] = $name;
                        $redundantWhere['knowledge_id'] = $id;
                    //如果对应的知识点关联了习题就修改习题关联知识点表冗余字段
                        $associated = D('Exercises_Course')->associatedOfExercises($id);
                        if(!empty($associated)){
                            $redundantStatus = D('Exercises_Course')->redundantSave($redundantData,$redundantWhere);//echo M()->getLastSql();die;
                            if($saveStatus === false || $redundantStatus === false){
                                M('exercises_textbook_tree_info')->rollback();
                                $this->showjson(404);
                            }
                        }
                    }
                    M('exercises_textbook_tree_info')->commit();
                }

            D('Exercises_tree_log')->insertLog($info[0]['curriculum_tree_breviary_id'],1,$oper_name,$ip,$operatorInfo['id'],$operatorInfo['name'],$oper_name,2);

            D('Exercises_curriculum_tree_breviary')->commit();
            $this->showjson(200);
        } else {
            D('Exercises_curriculum_tree_breviary')->rollback();
            $this->showjson(404);
        }
    }

    /*
    *判断当前教材知识树是否存在
    */
    public function isExist(){
        $courseId = getParameter('courseId','int');
        $Learning_period_id = getParameter('Learning_period_id','int');

        $where['course_id'] = $courseId;
        $where['Learning_period_id'] = $Learning_period_id;

        $data['course_id'] = $courseId;
        $data['Learning_period_id'] = $Learning_period_id;
        $data['creat_status'] = getParameter('num','int');
//获取创建人账号和名称
        $accountInfo = $this->getUserRoleAuth();
        $data['creat_name'] = $accountInfo['name']; //名称
        $ids['exercises_account.id'] = $accountInfo['id'];
        $accountData = D('Exercises_account')->getResourcesOne($ids);
        $data['creat_account'] = $accountData['account']; //账号
        $info = D('Exercises_curriculum_tree_breviary')->getList($where);
        D('Exercises_curriculum_tree_breviary')->startTrans();
        if(empty($info)){
            //创建操作并返回ID
            $insertId = D('Exercises_curriculum_tree_breviary')->creatTextbookTree($data);
            if($insertId === false){
                D('Exercises_curriculum_tree_breviary')->rollback();
            }else{
                D('Exercises_curriculum_tree_breviary')->commit();
                $this->showjson('200','',$insertId);
            }

        }else{
            //如果提交过来的状态为上架
            if($data['creat_status'] == 1){
                $id['id'] = $info['id'];
                $resource['creat_status'] = 1;
                if(D('Exercises_curriculum_tree_breviary')-> saveCurriculumTree($id,$resource) === false){
                    D('Exercises_curriculum_tree_breviary')->rollback();
                }else{
                    D('Exercises_curriculum_tree_breviary')->commit();
                }

            }else{
                $id['id'] = $info['id'];
                $resource['creat_status'] = 2;
                if(D('Exercises_curriculum_tree_breviary')-> saveCurriculumTree($id,$resource) === false){
                    D('Exercises_curriculum_tree_breviary')->rollback();
                }else{
                    D('Exercises_curriculum_tree_breviary')->commit();
                }
            }
            //返回ID
            $this->showjson('200','',$info['id']);
        }
    }

    /*
     * 根据某个未知的子级id获得父级树形数据
     * @param   id     知识点ID
     */
    public function getKnowledgeTreeData(){
        $id=getParameter('id','int');
        $data['Learning_period_id'] = getParameter('Learning_period_id','int');
        $data['course_id'] = getParameter('course','int');
        $result=$this->model->getParentTreeData($id,$data);
        $this->showjson(200,'',$result);
    }

}