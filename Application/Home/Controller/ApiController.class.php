<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
use \JPush\Client as JPush;
use Think\Exception;

define("APPID", "wxa6d2714aa7728aef");//你微信定义的appid
define("APPSECRET","4b62d67992416eac3e58f3ebd4ae7993");//你微信公众号的appsecret

class ApiController extends PublicController{

    private $client = null;
    private $app_key = "031af2f5647b07b86c11794b";
    private $master_secret = "7b708c0f657e4f2f8ac86156";

    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
    }

    //send one
    public function sendTo($sendInfo=array(),$isUserDefinedMessage=0) {

        $this->client = new \JPush($this->app_key, $this->master_secret);
        try {
            if (empty($sendInfo['id'])) {
                if($isUserDefinedMessage == 0)
                $result = $this->client->push()
                    ->setPlatform('all')
                    ->addAllAudience()// 推送所有观众
                    ->addAndroidNotification($sendInfo['title'], '', 1, $sendInfo['msg']['extras'])
                    ->addIosNotification($sendInfo['title'], 'iOS sound', intval($sendInfo['msg']['badge']), true, 'iOS category', $sendInfo['msg']['extras'])
                    ->setOptions(null,null,null,PUSHCONFIG == 1? null:true,null)
                    ->send();
                else
                    $result = $this->client->push()
                        ->setPlatform('all')
                        ->addAllAudience()// 推送所有观众
                        ->setMessage($sendInfo['title'], '', 'text', $sendInfo['msg']['extras'])
                        ->setOptions(null,null,null,PUSHCONFIG == 1? null:true,null)
                        ->send();
            } else {
                if($isUserDefinedMessage == 0)
                $result = $this->client->push()
                    ->setPlatform('all')
                    ->addRegistrationId($sendInfo['id'])
                    ->addAndroidNotification($sendInfo['title'], '', 1, $sendInfo['msg']['extras'])
                    ->addIosNotification($sendInfo['title'], 'iOS sound', intval($sendInfo['msg']['badge']), true, 'iOS category', $sendInfo['msg']['extras'])
                    ->setOptions(null,null,null,PUSHCONFIG == 1? null:true,null)
                    ->send();
                else
                    $result = $this->client->push()
                        ->setPlatform('all')
                        ->addRegistrationId($sendInfo['id'])
                        ->setMessage($sendInfo['title'], '', 'text', $sendInfo['msg']['extras'])
                        ->setOptions(null,null,null,PUSHCONFIG == 1? null:true,null)
                        ->send();
            }
        }catch(\Exception $e)
        {
            return false;
        }
        return true;
    }


    //权限列表
    public function apiList() {

        if (!session('?admin')) redirect(U('Index/index'));
        //赋值公共的头部信息
        $this->assign('module', 'Api接口管理');
        $this->assign('nav', 'Api接口管理');
        $this->assign('subnav', 'Api接口管理列表');


        $keyword = $_GET['keyword'];

        if (!empty($keyword)) {
            $where['describe'] = array('like', '%' . $keyword . '%');
        }

        $where['is_delete'] = 1;

        $count =M('api_version_control')->where( $where )->order('id desc')->where( $where )->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($where as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }

        $show = $Page->show();


        $auth_list = M('api_version_control')->where( $where )->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach( $auth_list as $k => $v) {
            $auth_list[$k]['urlname'] = 'http://'.C('REMOTE_ADDR').'/ApiInterface/'.$v['version_path'].'/'.$v['controller_name'].'/'.$v['function_name'];
        }

        $this->assign('list',$auth_list);
        $this->assign('page', $show);
        $this->keyword = $keyword;
        $this->display();
    }

    public function deleteApi() {
        if (!session('?admin')) redirect(U('Index/index'));

        if (session('admin.role') == 3) {
            echo 'error';die;
        }

        $id  = $_GET['id'];
        $Model = M('api_version_control');
        $id  = $Model->where("id=$id")->delete();

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

    public function addApi() {
        $this->assign('module', 'Api接口管理');
        $this->assign('nav', 'Api接口管理');
        $this->assign('subnav', '添加Api接口');

        $this->display();
    }

    public function saveApi() {
        $data   = M('api_version_control')->create();
        $data['version_path'] = 'Version'.preg_replace('/\./','_',$data['version']);
        $data['modify_time'] = time();
        $data['create_time'] = time();
        $data['is_delete'] = 1;

        if (M('api_version_control')->add($data)) {
            $res['code'] = 1;
            $res['msg'] = "添加成功";

        } else {
            $res['code'] = -1;
            $res['msg'] = "添加失败";

        }

        $this->ajaxReturn($res);
    }

    function accept(){
        $session_info = $_SESSION["weixin"];

        if(!empty($session_info)) {
            $this->test();
        } else {
            //这个链接是获取code的链接 链接会带上code参数
            $REDIRECT_URI = "http://www.jtypt.com/index.php?m=Home&c=Api&a=getCode";
            $REDIRECT_URI = urlencode($REDIRECT_URI);
            $scope = "snsapi_base";
            $state = md5(mktime());
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".$REDIRECT_URI."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
            header("location:$url");
        }

    }
    //用户同意之后就获取code  通过获取code可以获取一切东西了
    function getCode() {
        //获取accse_token
        $code = $_GET["code"];
        //echo $code;
        //echo "<br>";
        //用code获取access_yoken
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$code."&grant_type=authorization_code";
        //这里可以获取全部的东西  access_token openid scope
        $res = $this->https_request($url);
        $res  = json_decode($res,true);
        $openid = $res["openid"];
        print_r($openid);die();
        $access_token = $res["access_token"];
        //echo $access_token;
        //这里是获取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = $this->https_request($url);
        $res = json_decode($res,true);

        $_SESSION["weixin"]=$res;
        header("location:http://www.jtypt.com/index.php?m=Home&c=Api&a=test");
    }

    function https_request($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //是否关注
    public function test() {
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".APPSECRET;
        $access_msg = json_decode(file_get_contents($access_token));
        $token = $access_msg->access_token;
        $subscribe_msg = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=".$_SESSION["weixin"]['openid'];
        $subscribe = json_decode(file_get_contents($subscribe_msg));
        $gzxx = $subscribe->subscribe;
        //
        if($gzxx === 1){
            echo "<h1 style='color: red;font-size: 100px'>已关注</h1>";
        }else {
            $fqid = 10086;
            $qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$fqid.'}}}';
            $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$token";
            $result = $this->https_request($url,$qrcode);
            $jsoninfo = json_decode($result,true);
            print_r($jsoninfo);die();
            $ticket = $jsoninfo['ticket'];
            echo $ticket.PHP_EOL;

            $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
            echo $url;


            echo "<img src='./qrcode.jpg'>";
        }
    }

    //生成二维码
    public function setQrcode() {
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".APPSECRET;
        $access_msg = json_decode(file_get_contents($access_token));
        $access_token = $access_msg->access_token;

    }

    //入库
    public function getTestData() {

        $row = M('biz_resource')
            ->JOIN('LEFT JOIN biz_textbook ON biz_textbook.id = biz_resource.textbook_id')
            ->JOIN('LEFT JOIN dict_grade ON dict_grade.id = biz_resource.grade_id')
            ->JOIN('LEFT JOIN dict_course ON dict_course.id = biz_resource.course_id')
            ->field('biz_resource.id,CONCAT(biz_resource.name,biz_resource.description,biz_textbook.name,dict_grade.grade,dict_course.course_name) as name')
            ->select();

        foreach ($row as $k=>$v) {
            $data['id'] = $v['id'];
            $savedata['resource_info'] = $v['name'];
            M('biz_resource')->where($data)->save($savedata);
        }

    }

    public function show() {
        echo "helloworld";
    }

    public function runCron(){
        set_time_limit(0);
        D('UserInfo')->getCronHomeWork();
    }


}