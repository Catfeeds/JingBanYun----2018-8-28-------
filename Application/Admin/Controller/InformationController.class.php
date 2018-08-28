<?php
namespace Admin\Controller;
use Think\Controller;

class InformationController extends Controller {
  
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    public function informationList() {

        $cid=I('cid');
        $status=I('status');
        $keyword=I('keyword');
        if(!empty($status)) {
            $this->assign('status',$status);
            $where['status'] = $status;
        }

        if(!empty($keyword)) {
            $this->assign('keyword',$keyword);
            $where['title'] = ['like', '%' . $keyword . '%'];
        }

        $hotColumn = D('Information')->getHotColumn();

        if (empty($cid)) {
            $cid = $hotColumn[0]['id'];
        }

        $this->assign('cid',$cid);
        $this->assign('hotColumn',$hotColumn);
        $this->assign('role', session('admin.role'));

        $where['type'] = $cid;

        if ($cid==7) {
            $inforList = D('Information')->getInforList($where);
            $timeInfo = D('Information')->getTimeLength($cid);
            $this->assign('timeInfo',$timeInfo);
            $this->assign('list',$inforList);
            $this->display();
        } else {

            $inforList = D('Information')->getInforListInfo($where);

            $this->assign('list',$inforList['list']);
            $this->assign('page',$inforList['show']);
            $this->display_nocache('informationListView');
        }

    }

    public function addInformation(){

        $cid=I('cid');

        switch ($cid){
            case 7:
                $this->assign('cid_add',$cid);
                $this->display_nocache('addNews');
                break;
            case 8:
                $this->assign('cid_add',$cid);
                $this->display_nocache('teaching');
                break;

            case 9:
                $this->assign('cid_add',$cid);
                $this->display_nocache('winningWork');
                break;

            case 10:
                $this->assign('cid_add',$cid);
                $this->assign('huati','话题');
                $this->assign('title_link','热点话题管理');
                $this->assign('title_link_add','添加热点话题');
                $this->display_nocache('hot');
                break;

            case 11:
                $this->assign('cid_add',$cid);
                $this->assign('huati','新闻');
                $this->assign('title_link','教育新闻管理');
                $this->assign('title_link_add','添加教育新闻');
                $this->display_nocache('hot');
                break;
        }

    }
    //上传新闻
    public function addNewsInformation() {
        $cid = getParameter('cid','str',false); //跳转用的
        $activityStart = getParameter('activityStart','str',false);
        $activityEnd = getParameter('activityEnd','str',false);
        $urlname = getParameter('urlname','str',false);
        $displaytxt = getParameter('displaytxt','str',false);
        $playorder = getParameter('playorder','str',false);
        $vid = getParameter('vid_file_path','str',false);
        $adddata=[
            'type'=>$cid,
            'starttime'=>strtotime($activityStart),
            'endtime'=>strtotime($activityEnd),
            'linkaddress'=>$urlname,
            'title'=>$displaytxt,
            'file_path'=>$vid,
            'play_order'=>$playorder,
            'create_at' => time(),
            'publisher' => session('admin.name'),
            'publisher_id' => session('admin.id'),
            'browse_count' => rand(400,500),

        ];

        $findorder['play_order'] = $playorder;
        $findplayorder = D('Information')->findPlayOrder($findorder);

        if (!empty($findplayorder))
            $this->error('播放顺序已存在');


        $id = D('Information')->addInfor($adddata);
        if ($id) {
            $this->redirect('Information/informationList', array('cid' => $cid) );
        } else {
            $this->error("添加失败请重试");
        }
    }

    public function addOrUpdateTimelength() {
        $cid = getParameter('cid','int',false); //跳转用的
        $time_length = getParameter('time_length','int',false); //跳转用的
        $id = D('Information')->addOrUpdateTimelength($cid,$time_length);

        if ( $id !== false ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

    //修改新闻
    public function editNews() {
        if(IS_POST) {

            $id = getParameter('id','int',false); //跳转用的
            $cid = getParameter('cid','int',false); //跳转用的

            $activityStart = getParameter('activityStart','str',false);
            $activityEnd = getParameter('activityEnd','str',false);
            $urlname = getParameter('urlname','str',false);
            $displaytxt = getParameter('displaytxt','str',false);
            $playorder = getParameter('playorder','str',false);
            $vid = getParameter('vid_file_path','str',false);
            $editata=[
                'type'=>$cid,
                'starttime'=>strtotime($activityStart),
                'endtime'=>strtotime($activityEnd),
                'linkaddress'=>$urlname,
                'title'=>$displaytxt,
                'file_path'=>$vid,
                'play_order'=>$playorder,
                'update_at' => time(),
                'publisher' => session('admin.name'),
                'publisher_id' => session('admin.id'),
                'status' => 1,

            ];

            $row = D('Information')->editNewsInfor($editata,$id);

            if ($row!==false) {
                $this->redirect('Information/informationList', array('cid' => $cid) );
            } else {
                $this->error("添加失败请重试");
            }

        } else {
            $id = getParameter('id','int',false);
            $cid = getParameter('cid','int',false); //跳转用的
            $inforInfo = D('Information')->findInfor($id);
            $this->assign('inforInfo',$inforInfo);
            $this->assign('id',$id);
            $this->assign('cid_add',$cid);

            $this->display_nocache();
        }

    }

    //添加资讯
    public function addTeachingInformation() {

        $urlname = getParameter('urlname','str',false); //跳转用的
        $displaytxt = getParameter('displaytxt','str',false); //跳转用的
        $cid = getParameter('cid','int',false); //跳转用的
        $content = I('content'); //跳转用的

        list( $pcwidth ,  $pcheight ) =  getimagesize($_FILES['pc_cover']['tmp_name']);
        if($pcwidth!=158 && $pcheight!=100) {
            $this->error('PC封面图尺寸不正确');
        }

        list( $mbwidth ,  $mbheight ) =  getimagesize($_FILES['mobile_cover']['tmp_name']);
        if ( ($mbwidth/$mbheight)!=1 ) {
            $this->error('APP封面图比例不正确');
        }
        //print_r($height);
        //die();
        //获取pc封面图片、手机封面图片OSS地址
        $data=[];
        if ($_FILES['pc_cover']['name'] && $_FILES['mobile_cover']['name']) {
            $pc_cover_url = $this->upload_file2($_FILES['pc_cover']);
            $mobile_cover_url = $this->upload_file2($_FILES['mobile_cover']);
            $data['pc_cover'] = $pc_cover_url;
            $data['mobile_cover'] = $mobile_cover_url;
        }
        $data['type'] = $cid;
        $data['linkaddress'] = $urlname;
        $data['title'] = $displaytxt;
        $data['create_at'] = time();
        $data['content'] = $content;
        $data['publisher'] = session('admin.name');
        $data['publisher_id'] = session('admin.id');
        $data['browse_count'] = rand(400,500);


        $id = D('Information')->addInfor($data);
        if ($id) {
            $this->redirect('Information/informationList', array('cid' => $cid) );
        } else {
            $this->error("添加失败请重试");
        }



    }

    //oss上传封面
    public function upload_file2($file=array())
    {
        $_FILES['file'] = $file;
        //$file_name = $_FILES['file']['name'];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 1, 0); //1 pic 2//
        //$returnArray = explode(",", $result[1]);
        return $result['1'];
    }

    //修改新闻
    public function editTeaching() {

        if(IS_POST) {

            $urlname = getParameter('urlname','str',false); //跳转用的
            $displaytxt = getParameter('displaytxt','str',false); //跳转用的
            $cid = getParameter('cid','int',false); //跳转用的
            $id = getParameter('id','int',false); //跳转用的
            $content = I('content'); //跳转用的
            $pc_cover_edit = getParameter('pc_cover_edit','str',false); //跳转用的
            $mobile_cover_edit = getParameter('mobile_cover_edit','str',false); //跳转用的
            $data=[];

            if (empty($pc_cover_edit)) {
                list( $pcwidth ,  $pcheight ) =  getimagesize($_FILES['pc_cover']['tmp_name']);
                if($pcwidth!=158 && $pcheight!=100) {
                    $this->error('PC封面图尺寸不正确');
                }

                $pc_cover_url = $this->upload_file2($_FILES['pc_cover']);
                $data['pc_cover'] = $pc_cover_url;
            } else {
                $data['pc_cover'] = $pc_cover_edit;
            }
            if (empty($mobile_cover_edit)) {
                list( $mbwidth ,  $mbheight ) =  getimagesize($_FILES['mobile_cover']['tmp_name']);
                if ( ($mbwidth/$mbheight)!=1 ) {
                    $this->error('APP封面图比例不正确');
                }

                $mobile_cover_url = $this->upload_file2($_FILES['mobile_cover']);

                $data['mobile_cover'] = $mobile_cover_url;
            } else {
                $data['mobile_cover'] = $mobile_cover_edit;
            }

            $data['type'] = $cid;
            $data['linkaddress'] = $urlname;
            $data['title'] = $displaytxt;
            $data['update_at'] = time();
            $data['content'] = $content;
            $data['publisher'] = session('admin.name');
            $data['publisher_id'] = session('admin.id');
            $data['status'] = 1;

            $row = D('Information')->editNewsInfor($data,$id);

            if ($row!==false) {
                $this->redirect('Information/informationList', array('cid' => $cid) );
            } else {
                $this->error("添加失败请重试");
            }


        } else {
            $id = getParameter('id','int',false);
            $cid = getParameter('cid','int',false); //跳转用的
            $inforInfo = D('Information')->findInfor($id);
            $this->assign('inforInfo',$inforInfo);
            $this->assign('id',$id);
            $this->assign('cid_add',$cid);

            $this->display_nocache();
        }
    }

    //添加热点话题和教育新闻管理
    public function addHot() {
        $cid = getParameter('cid','str',false); //跳转用的
        $urlname = getParameter('urlname','str',false);
        $displaytxt = getParameter('displaytxt','str',false);
        $content = I('content'); //跳转用的

        $data['type'] = $cid;
        $data['linkaddress'] = $urlname;
        $data['title'] = $displaytxt;
        $data['create_at'] = time();
        $data['content'] = $content;
        $data['publisher'] = session('admin.name');
        $data['publisher_id'] = session('admin.id');
        $data['browse_count'] = rand(400,500);

        $id = D('Information')->addInfor($data);
        if ($id) {
            $this->redirect('Information/informationList', array('cid' => $cid) );
        } else {
            $this->error("添加失败请重试");
        }
    }

    //修改热点话题和教育新闻管理
    public function editHot() {
        if(IS_POST) {
            $id = getParameter('id','int',false); //跳转用的
            $cid = getParameter('cid','int',false); //跳转用的
            $urlname = getParameter('urlname','str',false);
            $displaytxt = getParameter('displaytxt','str',false);
            $content = I('content'); //跳转用的

            $data['type'] = $cid;
            $data['linkaddress'] = $urlname;
            $data['title'] = $displaytxt;
            $data['update_at'] = time();
            $data['content'] = $content;
            $data['publisher'] = session('admin.name');
            $data['publisher_id'] = session('admin.id');
            $data['status'] = 1;

            $row = D('Information')->editNewsInfor($data,$id);

            if ($row!==false) {
                $this->redirect('Information/informationList', array('cid' => $cid) );
            } else {
                $this->error("添加失败请重试");
            }

        } else {
            $id = getParameter('id','int',false);
            $cid = getParameter('cid','int',false); //跳转用的
            $inforInfo = D('Information')->findInfor($id);
            $this->assign('inforInfo',$inforInfo);
            $this->assign('id',$id);
            $this->assign('cid_add',$cid);

            if($cid==10) {
                $this->assign('huati','话题');
                $this->assign('title_link','热点话题管理');
                $this->assign('title_link_add','添加热点话题');
            } else {
                $this->assign('huati','新闻');
                $this->assign('title_link','教育新闻管理');
                $this->assign('title_link_add','添加教育新闻');
            }

            $this->display_nocache();
        }
    }


    //添加获奖作品
    public function addWinning() {

        $urlname = getParameter('urlname','str',false); //跳转用的
        $displaytxt = getParameter('displaytxt','str',false); //跳转用的
        $cid = getParameter('cid','int',false); //跳转用的
        $content = I('content'); //跳转用的

        list( $pcwidth ,  $pcheight ) =  getimagesize($_FILES['pc_cover']['tmp_name']);
        if($pcwidth!=245 && $pcheight!=155) {
            $this->error('PC封面图尺寸不正确');
        }

        list( $mbwidth ,  $mbheight ) =  getimagesize($_FILES['mobile_cover']['tmp_name']);
        if ( ($mbwidth/$mbheight)!=1 ) {
            $this->error('APP封面图比例不正确');
        }

        list( $apcwidth ,  $apcheight ) =  getimagesize($_FILES['pc_cover_add']['tmp_name']);

        if ( $apcwidth!=245 && $apcheight!=155 ) {
            $this->error('PC插图图比例不正确');
        }

        list( $ambwidth ,  $ambheight ) =  getimagesize($_FILES['mobile_cover_add']['tmp_name']);
        if (  $ambwidth!=706 && $ambheight!=392 ) {
            $this->error('APP插图比例不正确');
        }

        $data=[];
        if ($_FILES['pc_cover']['name'] && $_FILES['mobile_cover']['name']) {
            $pc_cover_url = $this->upload_file2($_FILES['pc_cover']);
            $mobile_cover_url = $this->upload_file2($_FILES['mobile_cover']);
            $data['pc_cover'] = $pc_cover_url;
            $data['mobile_cover'] = $mobile_cover_url;
            $data['pc_cover_add'] = $mobile_cover_url;
            $data['mobile_cover_add'] = $mobile_cover_url;
        }
        $data['type'] = $cid;
        $data['linkaddress'] = $urlname;
        $data['title'] = $displaytxt;
        $data['create_at'] = time();
        $data['content'] = $content;
        $data['publisher'] = session('admin.name');
        $data['publisher_id'] = session('admin.id');
        $data['browse_count'] = rand(400,500);

        $id = D('Information')->addInfor($data);
        if ($id) {
            $this->redirect('Information/informationList', array('cid' => $cid) );
        } else {
            $this->error("添加失败请重试");
        }
    }

    //修改获奖作品
    public function editWinging() {

        if(IS_POST) {

            $id = getParameter('id','int',false); //跳转用的
            $urlname = getParameter('urlname','str',false); //跳转用的
            $displaytxt = getParameter('displaytxt','str',false); //跳转用的
            $cid = getParameter('cid','int',false); //跳转用的
            $content = I('content'); //跳转用的

            $pc_cover_edit = getParameter('pc_cover_edit','str',false); //跳转用的
            $mobile_cover_edit = getParameter('mobile_cover_edit','str',false); //跳转用的
            $pc_cover_add_edit = getParameter('pc_cover_add_edit','str',false); //跳转用的
            $mobile_cover_add_edit = getParameter('mobile_cover_add_edit','str',false); //跳转用的

            $data=[];

            if (empty($pc_cover_edit)) {
                list( $pcwidth ,  $pcheight ) =  getimagesize($_FILES['pc_cover']['tmp_name']);
                if($pcwidth!=245 && $pcheight!=155) {
                    $this->error('PC封面图尺寸不正确');
                }

                $pc_cover_url = $this->upload_file2($_FILES['pc_cover']);
                $data['pc_cover'] = $pc_cover_url;
            } else {
                $data['pc_cover'] = $pc_cover_edit;
            }

            if (empty($mobile_cover_edit)) {
                list( $mbwidth ,  $mbheight ) =  getimagesize($_FILES['mobile_cover']['tmp_name']);
                if ( ($mbwidth/$mbheight)!=1 ) {
                    $this->error('APP封面图比例不正确');
                }

                $mobile_cover_url = $this->upload_file2($_FILES['mobile_cover']);

                $data['mobile_cover'] = $mobile_cover_url;
            } else {
                $data['mobile_cover'] = $mobile_cover_edit;
            }

            if (empty($pc_cover_add_edit)) {
                list( $apcwidth ,  $apcheight ) =  getimagesize($_FILES['pc_cover_add']['tmp_name']);
                if ( $apcwidth!=245 && $apcheight!=155 ) {
                    $this->error('PC插图图比例不正确');
                }

                $pc_cover_add_edit_url = $this->upload_file2($_FILES['pc_cover_add']);

                $data['pc_cover_add'] = $pc_cover_add_edit_url;
            } else {
                $data['pc_cover_add'] = $pc_cover_add_edit;
            }

            if (empty($mobile_cover_add_edit)) {
                list( $ambwidth ,  $ambheight ) =  getimagesize($_FILES['mobile_cover_add']['tmp_name']);
                if (  $ambwidth!=706 && $ambheight!=392 ) {
                    $this->error('APP插图比例不正确');
                }

                $mobile_cover_add_edit_url = $this->upload_file2($_FILES['mobile_cover_add']);

                $data['mobile_cover_add'] = $mobile_cover_add_edit_url;
            } else {
                $data['mobile_cover_add'] = $mobile_cover_add_edit;
            }

            $data['type'] = $cid;
            $data['linkaddress'] = $urlname;
            $data['title'] = $displaytxt;
            $data['create_at'] = time();
            $data['content'] = $content;
            $data['publisher'] = session('admin.name');
            $data['publisher_id'] = session('admin.id');
            $data['status'] = 1;

            $row = D('Information')->editNewsInfor($data,$id);

            if ($row) {
                $this->redirect('Information/informationList', array('cid' => $cid) );
            } else {
                $this->error("添加失败请重试");
            }

        } else {
            $id = getParameter('id','int',false);
            $cid = getParameter('cid','int',false); //跳转用的
            $inforInfo = D('Information')->findInfor($id);
            $this->assign('inforInfo',$inforInfo);
            $this->assign('id',$id);
            $this->assign('cid_add',$cid);

            $this->display_nocache();
        }

    }

    //查看资讯详情
    public function expertInformationDetails() {
        $id = getParameter('id','int',false); //跳转用的
        $cid = getParameter('cid','str',false); //跳转用的

        if ($cid==9) {
            $resources = D('Information')->findInfor($id);
            $this->assign('resources',$resources);
            $this->display_nocache('winningWorksDetails');
        } else {
            $result = D('Information')->findInfor($id);
            $this->assign('data',$result);
            $this->display_nocache();
        }

    }
}


