<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/11/28
 * Time: 18:40
 */

namespace Home\Controller;

use Think\Controller;

class PictureBooksController extends PublicController
{
    private $model;

    function __construct()
    {
        parent::__construct();
        $this->model = D('PictureBooks');
        $this->assign('oss_path', C('oss_path'));
        $this->assign('module', '教学+');
        $this->assign('nav', '绘本阅读');
        $this->assign('subnav', '绘本阅读');
        $this->assign('navicon', 'huibenyuedu');
        set_time_limit(0);
    }

    /**
     * 描述：绘本阅读列表页
     */
    public function pictureBooksList()
    {
        A('Home/Common')->getUserIdRole($userId, $role);
        A('Home/Common')->authJudgement();
        // A('Home/Common')->authJudgement();
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_2');
                break;
            case ROLE_STUDENT:
                layout('student_layout_2');
                break;
            case ROLE_PARENT:
                layout('parent_layout_2');
                break;
            default:
                layout('teacher_layout_2');
                break;
        }

        $keyword = trim(getParameter('keyword', 'str', false));
        if (!empty($keyword)) {
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ', $filter['keyword']);
            foreach ($temp_arr as $item) {
                $condetion['picture_books_name'][] = array("like", "%" . $item . "%");
            }
        }
        $subjectId = getParameter('subjectId', 'int', false);
        $this->assign('subject_id',$subjectId);
        $gradeId = getParameter('grade_id', 'int', false);
        $this->assign('grade_id',$gradeId);
        $theme = getParameter('theme_id', 'int', false);
        $this->assign('theme_id',$theme);
        if (!empty($subjectId)) {
            $condetion['picture_books_type'] = $subjectId;//题材
        }
        if (!empty($gradeId)) {
            $condetion['picture_books_grade'] = $gradeId;
        }
        if (!empty($theme)) {
            $condetion['picture_books_subject'] = $theme;//主题
        }
        /**********************反查所有的年级********************************/
        $where['picture_book_shelf'] = ONSHELF;
        $where['picture_books_status'] = AUDIT_YES;
        $gradeList = D('Picture_books')->reverseSearch($where);
        $this->assign('gradeList', $gradeList);
        /**********************查询所有的主题********************************/
        $theme = C('PICTURE_BOOK_THEME');
        $this->assign('themeList', $theme);
        /**********************查询所有的年题材*******************************/
        $subject = C('PICTURE_BOOK_SUBJECT');
        $this->assign('subjectList', $subject);
        $result = D('Picture_books')->getAllBySearch($condetion);
        //$result = D('Dict_grade')->getGradeList();
        $this->assign('userId', $userId);
        $this->assign('role', $role);
        //重组数据
        static $b = 0;
        if (count($result)>4) {
            for ($i = 1; $i <= count($result); $i++) {
                if ($i % 4 == 0) {
                    $b = $i-1;
                    for ($j = $i - 4; $j < $i; $j++) {
                        $newArr[$i][] = $result[$j];
                    }
                }
            }
            for ($k = $b+1; $k < count($result); $k++) {
                        $newArr[$b][] = $result[$k];
            }
        } else {
            for ($v = 0; $v < count($result); $v++) {
                $newArr[count($result)][] = $result[$v];
            }
        }
        $this->assign('list', $newArr);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    /**
     * 描述：绘本阅读详情
     */
    public function pictureBooksDetails()
    {
        if (!session('?teacher') && !session('?student') && !session('?parent') && !session('?admin'))
            redirect(U('Index/index'));
        $this->assign('module', '教学+');
        $this->assign('nav', '绘本阅读');
        $this->assign('navicon', 'huibenyuedu');

        $c['picture_books_id'] = getParameter('id', 'int',false);
        //进入绘本详情页就给该绘本添加点击量操作
        $where['picture_books_id'] = $c['picture_books_id'];
        D('Picture_books')->clickNum($where);
        $Model = M('picture_books');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);
        $this->assign('subnav', $result['name']);

//var_dump($result);die;
        $this->display();
    }

    /**
     *描述：绘本配套设计、课件、习题
     */
    public function matchingPictureBooksSomeThingsDetails(){
        $pictureBooksId = getParameter('pictureBooKsId','int',false);
        $type = getParameter('type','int',false);
        if(empty($pictureBooksId) && empty($type)){
            //参数错误
            redirect(U('Index/systemError'));
        }
        $condetion['picture_books_id'] = $pictureBooksId;
        $detailsResource = D('Picture_books')->getOneResource($condetion);
        switch ($type){
            case 1:
                $this->assign('contentInfo',$detailsResource[0]['picture_books_courseware']);//配套课件
            case 2:
                $this->assign('contentInfo',$detailsResource[0]['picture_books_teacher_design']);//绘本配套教学设计（url）
            case 3:
                $this->assign('contentInfo',$detailsResource[0]['picture_books_practice']);//绘本配套的练习
            default:
                //redirect(U('Index/systemError'));
        }
        $this->assign('info',$detailsResource[0]);
        $this->display();
    }
}
