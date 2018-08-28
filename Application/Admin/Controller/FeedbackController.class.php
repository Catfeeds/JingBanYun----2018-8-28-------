<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class FeedbackController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
           
     
    public function addfeedback() {
        session('_token',md5(time()));
        //获取功能模块列表
        $map['fid'] = 0; //先查询父级
        $node_list = M('account_node_list')->field('id,node_name,fid')->where( $map )->select();

        foreach ($node_list as $key => $value) {
            $data['fid'] = $value['id'];
            $row = M('account_node_list')->where( $data )->field('id,node_name,fid')->select();
            $node_list[$key]['child_data'] = $row;
        }
        $this->assign('node_list',$node_list);
        $this->assign('_token',session('_token'));
        $this->display();
	}


	//反馈意见入库

	public function savefeedback() {
		$content = remove_xss($_POST['content']);
		$id = $_POST['id'];
		$_token = $_POST['_token'];
		if( $_token != session('_token') ) { //控制远程提交
			echo "非法来源提交";
			exit;
		}
		$laiyuan = $_SERVER['HTTP_REFERER'];
		$urldata = basename($laiyuan); //控制数据来源
		if (md5($urldata.'show') != md5('index.php?m=Home&c=Feedback&a=addfeedbackshow')) {
			echo "非法来源提交";
			exit;
		}
		

		/*if ( $_SESSION['num'] > 4) { //不更换浏览器提交次数进行权限
			$res['code'] = 'error';
			$res['url'] = '提交次数太多,请稍后进行反馈';
			$this->ajaxReturn( $res,'json' );
			exit;
		}*/
                $student_session=session('student');
		if (!empty($student_session)) {
			$data = array(
				'user_id' => session('student.id'),
				'user_role' => 3,
				'model_name' => ltrim($id,','),
				'feed_info' => $content,
				'feed_createtime' => time(),
				'user_name' => session('student.student_name'),
				'role_name' => '学生',
			);
			
			$id = M('feedback')->add( $data );

			if ( $id ) {
				/*$_SESSION['num'] += 1;*/
				$res['code'] = 'success';
				$res['url'] = 'index.php?m=Home&c=student&a=index1';
				$this->ajaxReturn( $res, 'json' );
			} else {
				$res['code'] = 'error';
				$res['url'] = '提交失败,请刷新页面重试';
				$this->ajaxReturn( $res,'json' );
			}
		}

                $teacher_session=session('teacher');
		if (!empty($teacher_session) && session('teacher')  != 'youke') {
			$data = array(
				'user_id' => session('teacher.id'),
				'user_role' => 2,
				'model_name' => ltrim($id,','),
				'feed_info' => $content,
				'feed_createtime' => time(),
				'user_name' => session('teacher.name'),
				'role_name' => '老师',
			);
			
			$id = M('feedback')->add( $data );


			if ( $id ) {
				/*$_SESSION['num'] += 1;*/
				$res['code'] = 'success';
				$res['url'] = 'index.php?m=Home&c=Teach&a=index1';
				$this->ajaxReturn( $res, 'json' );
			} else {
				
				$res['code'] = 'error';
				$res['url'] = '提交失败,请刷新页面重试';
				$this->ajaxReturn( $res,'json' );
			}

		}

                $parent_session=session('parent');
		if (!empty($parent_session)) {
			$data = array(
				'user_id' => session('parent.id'),
				'user_role' => 4,
				'model_name' => ltrim($id,','),
				'feed_info' => $content,
				'feed_createtime' => time(),
				'user_name' => session('parent.parent_name'),
				'role_name' => '家长',
			);
			
			$id = M('feedback')->add( $data );

			if ( $id ) {
				/*$_SESSION['num'] += 1;*/
				$res['code'] = 'success';
				$res['url'] = 'index.php?m=Home&c=parent&a=index1';
				$this->ajaxReturn( $res, 'json' );
			} else {
				$res['code'] = 'error';
				$res['url'] = '提交失败,请刷新页面重试';
				$this->ajaxReturn( $res,'json' );
			}
			
		}

		if (session('teacher') == 'youke') {
			$data = array(
				'user_role' => 5,
				'model_name' => ltrim($id,','),
				'feed_info' => $content,
				'feed_createtime' => time(),
				'user_name' => '游客',
				'role_name' => '游客',
			);
			
			$id = M('feedback')->add( $data );

			if ( $id ) {
				/*$_SESSION['num'] += 1;*/
				$res['code'] = 'success';
				$res['url'] = 'index.php?m=Home&c=Teach&a=index1';
				$this->ajaxReturn( $res, 'json' );
			} else {
				$res['code'] = 'error';
				$res['url'] = '提交失败,请刷新页面重试';
				$this->ajaxReturn( $res,'json' );
			}
		}
 
		if (empty($student_session) && empty($teacher_session) && empty($parent_session) ) {
			$data = array(
				'model_name' => ltrim($id,','),
				'feed_info' => $content,
				'feed_createtime' => time(),
			);
			
			$id = M('feedback')->add( $data );

			if ( $id ) {
				/*$_SESSION['num'] += 1;*/
				$res['code'] = 'success';
				$res['url'] = 'index.php?m=Home&c=Index&a=index';
				$this->ajaxReturn( $res, 'json' );
			} else {
				$res['code'] = 'error';
				$res['url'] = '提交失败,请刷新页面重试';
				$this->ajaxReturn( $res,'json' );
			}
		}




	}

	//获取后台反馈列表

	public function feedbackList() {
		if (!session('?admin')) redirect(U('Login/login'));
		$module_id = $_POST['model_id'];
		$keyword = $_POST['keyword'];

		$this->module_id = $module_id;
		$this->keyword = $keyword;

		if (!empty($module_id)) {
            //$wheremap['model_name'] = array('like', '%' . $module_id . '%');
            $wheremap['_string']="FIND_IN_SET({$module_id}, model_name)";
            
        }

        if (!empty($keyword)) {
        	$keyword = trim($keyword);
            $wheremap['feed_info'] = array('like', '%' . $keyword . '%');
            $wheremap['user_name'] = array('like', '%' . $keyword . '%');
            $wheremap['role_name'] = array('like', '%' . $keyword . '%');
            $wheremap['contact'] = array('like', '%' . $keyword . '%');
            $wheremap['_logic'] = 'or';
        }

        if (!empty($keyword) && !empty($module_id)) {
        	$keyword = trim($keyword);
        	$wheremap = array(
        		'_string' => "FIND_IN_SET({$module_id}, model_name)",
        		array(
        			'feed_info' => array('like', '%' . $keyword . '%'),
        			'user_name' => array('like', '%' . $keyword . '%'),
        			'role_name' => array('like', '%' . $keyword . '%'),
        			'_logic' => 'or',
        		),

        	);
        }


		$this->assign('module', '个人中心');
        $this->assign('nav', '意见问题反馈管理');
        $this->assign('subnav', '反馈列表');

        $count = M('feedback')->where($wheremap)->count();

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($wheremap as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();

        $list = M('feedback')

        	->limit($Page->firstRow . ',' . $Page->listRows)->where( $wheremap )->order('id desc')->select();
        

        foreach ($list as $key => $value) {

        	if (!empty($value['user_id']) && !empty($value['user_role'])) {
        		
        		if ($value['user_role'] == 2) {
        			$map['id'] = $value['user_id'];
        			$row = M('auth_teacher')->where( $map )->field('name,telephone')->find();
        			$list[$key]['name'] = $row['name'];
        		}

        		if ($value['user_role'] == 3) {
        			$map['id'] = $value['user_id'];
        			$row = M('auth_student')->where( $map )->field('student_name,parent_tel')->find();
        			$list[$key]['name'] = $row['student_name'];
        		}

        		if ($value['user_role'] == 4) {
        			$map['id'] = $value['user_id'];
        			$row = M('auth_parent')->where( $map )->field('parent_name,telephone')->find();
        			$list[$key]['name'] = $row['name'];
        		}

        		
        	}
        	if ($value['user_role'] == 5 ) {
    			$list[$key]['name'] = "游客";
    			
    		}

    		if (!empty($value['model_name'])) {
    			$ids = explode(',', $value['model_name']);
    			$node = array();

    			foreach ($ids as $k => $v) {
					if ( $v != 'qita') {
						$child_map['id'] = $v;
						$auth_list = M('account_node_list')->where( $child_map )->field('node_name')->find();
						$node[] = $auth_list['node_name'];
					} else {
						$node[] = '其他反馈';
					}

    			}
    			$list[$key]['model_name'] = $node;
    		}


        }

        $model_map['fid'] = array('neq',0);

        $model = M('account_node_list')->field('id,node_name')->where( $model_map )->select();
        
        
        $this->assign('model',$model);
        $this->assign('list', $list);
        $this->assign('page', $show);

		$this->display();
	}


	public function look() {
		$id = $_GET['id'];
		$data['id'] = $id;


		$this->assign('module', '个人中心');
        $this->assign('nav', '意见问题反馈管理');
        $this->assign('subnav', '查看反馈');

		$list = M('feedback')->where( $data )->find();


		if (!empty($list['user_id']) && !empty($list['user_role'])) {
        		
    		if ($list['user_role'] == 2) {
    			$map['id'] = $list['user_id'];
    			$row = M('auth_teacher')->where( $map )->field('name')->find();
    			$list['name'] = $row['name'];
    		}

    		if ($list['user_role'] == 3) {
    			$map['id'] = $list['user_id'];
    			$row = M('auth_student')->where( $map )->field('student_name')->find();
    			$list['name'] = $row['student_name'];
    		}

    		if ($list['user_role'] == 4) {
    			$map['id'] = $list['user_id'];
    			$row = M('auth_parent')->where( $map )->field('parent_name')->find();
    			$list['name'] = $row['name'];
    		}

    		
    	}
    	if ($list['user_role'] == 5 ) {
			$list['name'] = "游客";
			
		}

		if (!empty($list['model_name'])) {
			$ids = explode(',', $list['model_name']);
			$node = array();
			foreach ($ids as $k => $v) {
				$child_map['id'] = $v;
				$auth_list = M('account_node_list')->where( $child_map )->field('node_name')->find();
				$node[] = $auth_list['node_name'];
			}
			$list['model_name'] = $node;
		}
		//print_r($list);die();
		$this->assign('info',$list);
		$this->display();
	}


	//删除账户

    public function deleteFeedback(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if (session('admin.role') == 3) {
            echo 'error';die;
        }
        
        $id  = $_GET['id'];
        $Model = M('feedback');
        $id  = $Model->where("id=$id")->delete(); 

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
        
    }
    
}
