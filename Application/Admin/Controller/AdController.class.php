<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Verify;
use Common\Common\CSV;

define('COLUMN_WARMUPACTIVITY',4);
define('COLUMN_HOTACTIVITY',5);


class AdController extends Controller
{

    public $page_size = 20;

    public function __construct() {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->assign('oss_path', C('oss_path'));
        $this->Ad = D('Ad');
    }

    //广告列表

    public function adList() {

        $list = $this->Ad->getAdList();
        $this->assign('page',$list['show'] );
        $this->assign('list',$list['list'] );

        $this->display();
    }

    //添加广告

    public function addAd() {

        if ( IS_POST ) {

            if (!empty($_FILES)) {
                $fileinfo['name'] = $_FILES['filepic']['name'];
                $filedata = $this->upload_file2($_FILES['filepic']);
            } else {
                $this->error('请选择图像');
            }
            $exisinfo = pathinfo($fileinfo['name']);
            $fileinfo['name'] =$exisinfo['filename'];
            $fileinfo['url'] =$filedata;

            $result = $this->Ad->inserdata( $_POST,$fileinfo );
            if ( $result ) {
                $this->redirect('adList');
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->display();
        }
    }

    //删除ad
    public function deleteAd() {
        $id = $_GET['id'];
        if (empty($id) || $id<0 ) {
            $this->ajaxReturn('error');
        }

        $is_id = $this->Ad->delAd( $id );
        if ($is_id) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

    public function upload_file2($type) {
        $_FILES['file'] = $type;

        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 1, 0); //1 pic 2//
        return $result['1'];
    }

    public function setWeiXinQRCodePath()
    {
        $path = $_GET['path'];
        if(empty($path))
        {
            echo 'missing path parameter!';
            exit;
        }
        M()->execute('drop table if EXISTS table_qrpath');
        M()->execute('CREATE TABLE table_qrpath (path varchar(300))');
        M()->execute('INSERT INTO table_qrpath VALUES (\''.$path.'\')');

    }
    public function getQRCode()
    {
        $tables = M()->query('SHOW TABLES');
        $tableExists = false;
        foreach($tables as $key=>$val)
        {
            if($val[key($val)] == 'table_qrpath')
            {
                $tableExists = true;
                break;
            }
        }
        if(!$tableExists)
            exit;
        $result = M()->query('SELECT path FROM table_qrpath LIMIT 1');
        $path = $result[0]['path'];
        echo "<img src=\"$path\"/>";
    }
}
