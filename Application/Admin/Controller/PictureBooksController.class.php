<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/11/16
 * Time: 17:04
 */

namespace Admin\Controller;

use Think\Controller;

define('COLUMN_RESOURCE_ENABLED', 1);
define('COLUMN_RESOURCE_DISABLED', 0);
define('OPERATION_FAILED', '操作失败!');

class PictureBooksController extends Controller
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->assign('oss_path', C('oss_path'));
        $this->model = D('Picture_books');
    }

    /**
     *描述：绘本列表页
     */
    public function pictureBooksList()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        /***********************************筛选操作********************************/
        $name = trim(getParameter('name', 'str', false));//绘本包名称
        $id = getParameter('id', 'int', false);//绘本包ID
        $grade_id = getParameter('grade_id', 'int', false);//绘本包所属年级
        $type = getParameter('type', 'int', false);//绘本包所属年级题材
        $subject = getParameter('subject', 'int', false);//绘本包所属主题
        $status = getParameter('status', 'int', false);//绘本包审核状态
        $shelf = getParameter('shelf', 'int', false);//绘本包上下架状态
        $source = getParameter('source', 'int', false);//绘本包所属提供方
        if (!empty($name)) {
            $name = preg_replace('/\s+/', ' ', $name);
            $name = preg_replace('/\%+/', '\%', $name);
            $where['picture_books_name'] =array('like','%'.$name.'%');
        }
        if (!empty($id)) {
            $where['picture_books_id'] =$id;
        }
        if (!empty($grade_id)) {
            $where['picture_books_grade'] =$grade_id;
        }
        if (!empty($type)) {
            $where['picture_books_type'] =$type;
        }
        if (!empty($subject)) {
            $where['picture_books_subject'] =$subject;
        }
        if (!empty($status)) {
            $where['picture_books_status'][] =$status;
        }
        if (!empty($shelf)) {
            $where['picture_book_shelf'] =$shelf;
        }
        if (!empty($source)) {
            $where['picture_books_source'] =$source;
        }

        $page = getParameter('p','int',false);
        if(empty($page)){
            $page = 1;
        }
//var_dump($where);die;
        $result = $this->model->getAll($where,$page);//echo M()->getLastSql();die;
        $Page = new \Think\Page($result['count'], C('PAGE_SIZE_FRONT'));
        //分页传递筛选参数
        foreach ($where as $key => $val) {
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();
        //获取所有年级并渲染视图
        $gradeList = $this->model->reverseSearch();
        $this->assign('gradeList',$gradeList);
        /**********************查询所有的主题********************************/
        $theme = C('PICTURE_BOOK_THEME');
        $this->assign('themeList', $theme);
        /**********************查询所有的年题材*******************************/
        $subjects = C('PICTURE_BOOK_SUBJECT');
        $this->assign('subjectList', $subjects);
        /**********************查询所有的提供方*******************************/
        $sources = C('PICTURE_BOOK_SOURCE');
        $this->assign('sources', $sources);
        $this->assign('page',$show);
        $this->assign('list',$result['data']);
        $this->assign('name',$name);
        $this->assign('id',$where['picture_books_id']);
        $this->assign('grade_id',$grade_id);
        $this->assign('type',$type);
        $this->assign('subject',$subject);
        $this->assign('status',$status);
        $this->assign('shelf',$shelf);
        $this->assign('source',$source);
        $this->assign('role', session('admin.role'));
        $this->display();
    }

    /**
     *描述：绘本列表添加操作
     */
    public function pictureBooksAdd()
    {
        $pc_cover = $this->upload_file($_FILES['pc_cover']);
        if ($_POST){
            $condetion['picture_books_name'] = getParameter('name', 'str');//绘本包名称

            $condetion['picture_books_source']  = getParameter('source', 'int');//绘本包来源
            $condetion['picture_books_grade']  = getParameter('grade_id', 'int');//绘本包所属年级
            $condetion['picture_books_type'] = getParameter('type', 'int');//绘本包所属年级题材
            $condetion['picture_books_subject'] = getParameter('subject', 'int');//绘本包所属主题
            $condetion['picture_books_cover'] = $pc_cover;
            $condetion['picture_books_config'] = getParameter('picture_books_config', 'str');//绘本配置文件OSS地址（url）
            //$condetion['picture_books_courseware'] = getParameter('picture_books_courseware', 'str');//绘本配套的课件（url）
            //$condetion['picture_books_teacher_design'] = getParameter('picture_books_teacher_design', 'str');//绘本配套教学设计（url）
            //$condetion['picture_books_practice'] = getParameter('picture_books_practice', 'str');//绘本配套的练习（url）
            $gradeName = D('Dict_grade')->getGradeInfo($condetion['picture_books_grade']);
            $condetion['picture_books_grade_name'] = $gradeName['grade'];//绘本所属年级名字

            //入库操作
            $this->model->startTrans();
            if($this->model->addPictureBooks($condetion) === false){
                $this->model->rollback();
            }else{
                $this->model->commit();
                $this->redirect("PictureBooks/pictureBooksList");
            }
        }
        /**********************查询所有的主题********************************/
        $theme = C('PICTURE_BOOK_THEME');
        $this->assign('themeList', $theme);
        /**********************查询所有的年题材*******************************/
        $subject = C('PICTURE_BOOK_SUBJECT');
        $this->assign('subjectList', $subject);
        /**********************查询所有的提供方*******************************/
        $sources = C('PICTURE_BOOK_SOURCE');
        $this->assign('sources', $sources);
        //获取所有年级并渲染视图
        $gradeList = D('Dict_grade')->getGradeList();
        $this->assign('gradeList',$gradeList);
        $this->display('pictureBooksDetails');
    }

    /**
     *描述：绘本列表修改操作
     */
    public function pictureBooksSave()
    {
        $pc_cover = $this->upload_file($_FILES['pc_cover']);
        if ($_POST){
            $condetion['picture_books_name'] = getParameter('name', 'str');//绘本包名称

            $condetion['picture_books_source']  = getParameter('source', 'int');//绘本包来源
            $condetion['picture_books_grade']  = getParameter('grade_id', 'int');//绘本包所属年级
            $condetion['picture_books_type'] = getParameter('type', 'int');//绘本包所属年级题材
            $condetion['picture_books_subject'] = getParameter('subject', 'int');//绘本包所属主题
            if(!empty($pc_cover)){
                $condetion['picture_books_cover'] = $pc_cover;
            }
            $condetion['picture_book_shelf'] = OFFSHELF;
            $condetion['picture_books_status'] = AUDIT_WAIT;
            $condetion['picture_books_config'] = getParameter('picture_books_config', 'str');//绘本配置文件OSS地址（url）
            //$condetion['picture_books_courseware'] = getParameter('picture_books_courseware', 'str');//绘本配套的课件（url）
            //$condetion['picture_books_teacher_design'] = getParameter('picture_books_teacher_design', 'str');//绘本配套教学设计（url）
            //$condetion['picture_books_practice'] = getParameter('picture_books_practice', 'str');//绘本配套的练习（url）
            $gradeName = D('Dict_grade')->getGradeInfo($condetion['picture_books_grade']);
            $condetion['picture_books_grade_name'] = $gradeName['grade'];//绘本所属年级名字
            //入库操作
            $where['picture_books_id'] = getParameter('id','int');
            $this->model->startTrans();
            if($this->model->savePictureBooks($condetion,$where) === false){
                $this->model->rollback();
            }else{
                $this->model->commit();
                $this->redirect("PictureBooks/pictureBooksList");
            }
        }
        //查询数据
        $condetion['picture_books_id'] = getParameter('id','int');
        $result = $this->model->getOneResource($condetion);
       $this->assign('info',$result);
        //获取所有年级并渲染视图
        $gradeList = D('Dict_grade')->getGradeList();
        $this->assign('gradeList',$gradeList);
        /**********************查询所有的主题********************************/
        $theme = C('PICTURE_BOOK_THEME');
        $this->assign('themeList', $theme);
        /**********************查询所有的年题材*******************************/
        $subject = C('PICTURE_BOOK_SUBJECT');
        $this->assign('subjectList', $subject);
        /**********************查询所有的提供方*******************************/
        $sources = C('PICTURE_BOOK_SOURCE');
        $this->assign('sources', $sources);
       $this->display('pictureBooksDetails');
    }

    /*
     *描述：绘本列表修改状态操作
     */
    public function pictureBooksSaveStatus()
    {
        $idArrayStr = getParameter('id','str');
        //如果是多个ID进行拆分操作
        $condetionTemp = explode(',',$idArrayStr);
        $condetion['status'] = getParameter('status','int');
        $shelf = getParameter('shelf','str',false);//上下架状态
        $this->model->startTrans();
        foreach ($condetionTemp as $item){
            $condetion['id'] = $item;
            if(!empty($shelf)){
                if($this->model->updataPictureBooksShelf($condetion) === false){//上下架状态修改操作
                    $this->model->rollback();
                    $this->showjson('500','修改失败请稍后重试');
                }
            }else{
                if($this->model->updataPictureBooks($condetion) === false){
                    $this->model->rollback();
                    $this->showjson('500','修改失败请稍后重试');
                }
            }
        }
        $this->model->commit();
       $this->showjson('200','修改成功');
    }

    /**
     *描述：oss上传封面
     */
    public function upload_file($file=array())
    {
        $_FILES['file'] = $file;
        //$file_name = $_FILES['file']['name'];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 1, 0); //1 pic 2//
        //$returnArray = explode(",", $result[1]);
        return $result['1'];
    }

}