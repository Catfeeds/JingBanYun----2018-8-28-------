<?php
namespace Monitor\Controller;
use Think\Controller;
class QrcodeController extends Controller {
    //二维码扫描次数记录
    public function qrScanlist() {

    }


    //分享次数和时间记录
    public function setShare() {

        $id = getParameter('id','int',false);
        $type = getParameter('type','int',false);
        $data['share_id'] = $id;
        $data['create_at'] = time();
        $data['type'] = $type;
        if (!empty($id)) {
            D('Monitor')->setShareInfo( $data );
        }

    }

}