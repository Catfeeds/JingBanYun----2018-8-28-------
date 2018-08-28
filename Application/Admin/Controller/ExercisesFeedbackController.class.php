<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2018/7/24
 * Time: 10:11
 */

namespace Admin\Controller;

use Think\Controller;

class ExercisesFeedbackController extends Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }

    public function feedbackList()
    {
            $name = getParameter('name', 'str', false);
            $telephone = getParameter('telephone', 'str', false);
            $id = getParameter('id', 'int', false);
            $feedbackStatus = getParameter('feedbackStatus', 'int', false);
            $exercisesType = getParameter('exercisesType', 'int', false);
            $page = getParameter('p','int',false);
            if (!empty($name)) {
                $filter['name'] = $name;
                $where = " name like '%$name%'";
            }
            if (!empty($telephone)) {
                $filter['telephone'] = $telephone;
                if (empty($where)) {
                    $where = "telephone = $telephone";
                } else {
                    $where .= " and telephone = $telephone";
                }
            }
            if (!empty($id)) {
                $filter['id'] = $id;
                if (empty($where)) {
                    $where = "exercise_id = $id";
                } else {
                    $where .= " and exercise_id = $id";
                }
            }
            if (!empty($feedbackStatus)) {
                $filter['feedbackStatus'] = $feedbackStatus;
                if (empty($where)) {
                    $where = "status = $feedbackStatus";
                } else {
                    $where .= " and status = $feedbackStatus";
                }
            }
            if (!empty($exercisesType)) {
                $filter['exercisesType'] = $exercisesType;
                if (empty($where)) {
                    $where = "FIND_IN_SET($exercisesType,flag_type)";
                } else {
                    $where .= " and FIND_IN_SET($exercisesType,flag_type)";
                }
            }
            if(!empty($page)){
                $pageNum = $page;
                $page = ($page - 1)*20;
            }else{
                $page = 0;
            }
            if(empty($where)){
                $where = '1=1';
            }
            $result = D('Exercises_createexercise')->getAllDataByErrorCorrection($page,$where);
        $Page = new \Think\Page($result['count'], 20);
        foreach ($filter as $key => $val) {
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();
        $this->assign('name',$name);
        $this->assign('telephone',$telephone);
        $this->assign('id',!empty($id) ? $id : '');
        $this->assign('feedbackStatus',$feedbackStatus);
        $this->assign('exercisesType',$exercisesType);
        $this->assign('list',$result['data']);
        $this->assign('pageNum',$pageNum);
        $this->assign('filter',$filter);
        $this->assign('page',$show);
        $this->display();
    }

    /*
     *提交解决反馈内容和确认操作
     *
     */
    public function addFeedback(){
        $id = getParameter('id','int');
        $content = I('solve_content');
        $status = getParameter('reallyStatus','int',false);
        $where['id'] = $id;
        if(!empty($content)){
            $solve_status = getParameter('solve_status','int');
            $data['solve_content'] = $content;
            $data['status'] = $solve_status;
        }
        if(!empty($status)){
            $data['status'] = $status;
        }
        D('Exercises_createexercise')->startTrans();
        $resultStatus = D('Exercises_createexercise')->saveByErrorCorrection($where,$data);
        if($resultStatus === false){
            D('Exercises_createexercise')->rollback();
            $this->showjson('401','失败，请稍后重试');
        }else{
            D('Exercises_createexercise')->commit();
            $this->showjson('200');
        }
    }
    /*
     *查看原题
     *
     */
    public function showExercises(){
        $id = getParameter('id','int');
        $result = D('Exercises_createexercise')->getExerciseInfo($id);
        $this->showjson('200','',htmlspecialchars_decode(json_decode($result["json_html"],true)));
    }
    /*
     *查看解决说明
     *
     */
    public function showExplain(){
        $id = getParameter('id','int');
        $where['id'] = $id;
        $result = D('Exercises_createexercise')->selectByErrorCorrection($where);
        $this->showjson('200','',htmlspecialchars_decode($result['solve_content']));

    }

}