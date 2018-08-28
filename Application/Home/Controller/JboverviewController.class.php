<?php
namespace Home\Controller;

use Think\Controller;
class JboverviewController extends PublicController{
      public function jboverview(){
        A('Home/Common')->getUserIdRole($userId,$role);
          A('Home/Common')->authJudgement();

          switch($role)
        {
          case ROLE_TEACHER:layout('teacher_layout_1');
                          break;
          case ROLE_STUDENT:layout('student_layout_1');
                          break;
          case ROLE_PARENT:layout('parent_layout_1');
                          break;
                default:
                    layout('teacher_layout_1');
                    break;
        }

      $this->assign('module', '励耘圈');
      $this->assign('nav', '京版概览');
      $this->assign('subnav', '京版概览');
      $this->assign('navicon', 'jingbangailan');

      $Model = M('biz_bj_overview');
      $where['status']=2;
      $content = $Model->where($where)->select();
      $this->assign('data', $content[0]);

      $this->display();
    }
}
