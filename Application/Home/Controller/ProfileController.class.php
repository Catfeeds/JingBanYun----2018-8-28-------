<?php
namespace Home\Controller;

use Think\Controller;

class ProfileController extends PublicController
{
    public function index()
    {
        if (!session('?user')) {
            redirect(U('Account/index'), 0, '登录超时，页面跳转中...');
        }

        $this->assign('nav', '个人中心');
        $this->assign('subnav', '我的资料');

        $this->assign('user', session("user"));

        $this->display();
    }

    public function PracticePlace()
    {

        if (!session('?user')) {
            redirect(U('Account/index'), 0, '登录超时，页面跳转中...');
        }

        $this->assign('nav', '个人中心');
        $this->assign('subnav', '我的执业地点');

        $OrgModel = M("user_organisation_relational");
        $orgs = $OrgModel
            ->join('organisation on user_organisation_relational.organisation_id=organisation.id')
            ->where('user_organisation_relational.user_id=' . session('user.id'))
            ->field('organisation.id,organisation.name')
            ->select();

        $this->assign('orgs', $orgs);
        $this->assign('orgsCount', count($orgs));

        $this->display();
    }
}