<?php
namespace ApiInterface\Controller\Version1_1;
use Common\Common\JSSDK; 
use Think\Controller;
define('NEW_YEAR_DAY',1016);//元旦
define('NEW_YEAR_DAY_LY',1015);//元旦
define('ACTIVITY_START_TIME','2017-4-29');  //2017-4-29
define('ACTIVITY_END_TIME','2017-5-1 23:59:59');
define('REQUESTFROM_APP',1);
define('ACTIVITY_LOCAL_DIR','/Resources/socialactivity/');
define('EXPERTINFO_LOCAL_DIR','/Resources/expertinformation/');

define('ICON_ACTIVITY','http://'.WEB_URL.'/Public/img/activity/icon_dasaizhuanlan@2x.png');
define('ICON_INFOMATION','http://'.WEB_URL.'/Public/img/activity/icon_baomingkaishi@2x.png');
define('ICON_VOTE','http://'.WEB_URL.'/Public/img/activity/icon_toupiaokaishi@2x.png');
define('AD_TYPE','1');
define('AD_TIME','3');
define('LAYER_TYPE','3');

define("APPID", "wxa6d2714aa7728aef");//你微信定义的appid
define("APPSECRET","4b62d67992416eac3e58f3ebd4ae7993");//你微信公众号的appsecret

class ActivityController extends PublicController
{

    public $pageSize = 20;
    public $activityModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->activityModel = D('Social_activity');
        $this->assign('oss_path', C('oss_path'));
        define('HOLIDAYOPEN', 1); 
    }
    private function liveDataCallback(&$result)
    {
        $currentTime = time().'';
        foreach($result as $key=>$val)
        {
            $result[$key]['isLive'] = $val['is_live'];
            $result[$key]['liveData'] = [];
            $result[$key]['liveData']['liveEnd'] = $val['activityend'];
            $result[$key]['liveData']['liveStart'] = $val['activitystart'];
            $result[$key]['liveData']['serverTime'] = $currentTime;
            unset($result[$key]['is_live']);
            unset($result[$key]['livestart']);
            unset($result[$key]['liveend']);
        }
    }
    /**
     * @描述：获取活动类型列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getClassList()
    {
        $class_id=getParameter('cate', 'int',false);
        $result = $this->activityModel->getActivityClassList($class_id);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    function urlConvert($url){
        $pathArr = array();
        $modules = parse_url($url);
        $path = $modules['path'];
        $pathSplit = explode('/', $path);

        foreach ($pathSplit as $row){
            $pathArr[] = rawurlencode($row);
        }
        $urlNew = $modules['scheme']."://".$modules['host'].implode('/', $pathArr);
        return $urlNew;
    }
    /**
     * @描述：过节期间的App页面
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHolidayInfo()
    {   
        $model=D('Ad');
        $result=$model->getLunbo(AD_TYPE);
        $layer = $model->getLunbo( LAYER_TYPE );
        $time = time();
        
        $role = getParameter('role', 'int',false);
        $user_id = getParameter('userId', 'int',false);

       /* if(empty($role)) {
            $this->ajaxReturn(array('status' => 400));
        }

        if(empty($user_id)) {
            $this->ajaxReturn(array('status' => 400));
        }*/

        $rescoure = D('Social_activity')->registered_but_no_uploadworks($user_id,$role,1);

        if(empty($result) && empty($layer)){
            $this->ajaxReturn(array('status' => 400));
        } 

        $holiday_status=C('HOLIDAY_STATUS');

        if($holiday_status==HOLIDAYOPEN){ 
            $host=$_SERVER['HTTP_HOST'];
            $url=$host.'/ApiInterface/Version1_1/Activity/holiday_activity'; 

            if ( $time > $result[0]['starttime'] && $time < $result[0]['endtime'] ) {
                $data['url']='http://'.$url;
                $data['time']=AD_TIME;
                if($result[0]['display_method'] == 1) //每次打开显示
                    $data['holiday_code']=NEW_YEAR_DAY+rand();
                else
                     $data['holiday_code']=NEW_YEAR_DAY;
            }

            if( !empty($layer) && $time > $layer[0]['starttime'] && $time < $layer[0]['endtime'] ) {
                $imgmap['url'] = $layer[0]['url'];
                $imgmap['img']= $this->urlConvert(C('oss_path').$layer[0]['file_path']);
                if($layer[0]['display_method'] == 1) //每次打开显示
                    $imgmap['holiday_code']=NEW_YEAR_DAY_LY+rand();
                else
                $imgmap['holiday_code']=NEW_YEAR_DAY_LY;
            }

            if (!empty($rescoure)) {
                $social['title'] = '您有活动还没有上传资料，赶紧去上传吧!';
            }

            $info = array('status' => 200, 'result' => $data,'result2'=>$imgmap,'result3'=>$social);

            if (empty($info['result'])) {
                unset($info['result']);
            }

            if (empty($info['result2'])) {
                unset($info['result2']);
            }
            if (empty($info['result3'])) {
                unset($info['result3']);
            }

            $this->ajaxReturn( $info ,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $this->ajaxReturn(array('status' => 400, 'result' => array()));
        }
        
    }


    /**
     * @描述：过节期间的App页面
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getNewHolidayInfo()
    {
        if(isIos() == 3){
            sleep(2);
        }
        $model=D('Ad');
        $result=$model->getLunbo(AD_TYPE);
        $layer = $model->getLunbo( LAYER_TYPE );
        $time = time();

        $role = getParameter('role', 'int',false);
        $user_id = getParameter('userId', 'int',false);

//        if(empty($role)) {
//            $this->ajaxReturn(array('status' => 400));
//        }
//
//        if(empty($user_id)) {
//            $this->ajaxReturn(array('status' => 400));
//        }

        $rescoure = D('Social_activity')->registered_but_no_uploadworks($user_id,$role,1);

        if(empty($result) && empty($layer)){
            $this->ajaxReturn(array('status' => 400));
        }

        $holiday_status=C('HOLIDAY_STATUS');

        if($holiday_status==HOLIDAYOPEN){
            $host=$_SERVER['HTTP_HOST'];
            $url=$host.'/ApiInterface/Version1_1/Activity/holiday_activity';

            if ( $time > $result[0]['starttime'] && $time < $result[0]['endtime'] ) {
                $data['url']='http://'.$url;
                if(!empty($result[0]['display_time']))
                    $data['time']=$result[0]['display_time'];
                else
                    $data['time']= AD_TIME;
                if($result[0]['display_method'] == 1) //每次打开显示
                  $data['holiday_code']=NEW_YEAR_DAY+rand();
                else
                  $data['holiday_code']=NEW_YEAR_DAY;
            }
//            if(false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'iphone'))
//            {
//                $imgmap = $data;
//                $imgmap['img'] = $this->urlConvert(C('oss_path').$result[0]['file_path']);
//                $imgmap['url'] = $result[0]['url'];
//                unset($data);
//            }
            if( !empty($layer) && $time > $layer[0]['starttime'] && $time < $layer[0]['endtime'] ) {
                $imgmap['url'] = $layer[0]['url'];
                $imgmap['img']= $this->urlConvert(C('oss_path').$layer[0]['file_path']);
                if($layer[0]['display_method'] == 1) //每次打开显示
                    $imgmap['holiday_code']=NEW_YEAR_DAY_LY+rand();
                else
                    $imgmap['holiday_code']=NEW_YEAR_DAY_LY;
            }

            if (!empty($rescoure)) {
                $social['title'] = "您有活动还没有上传资料，\n赶紧去上传吧!";
                $datalink = [
                    'id' => $rescoure['activity_id'],
                    'user_id' => $user_id,
                    'role' => $role,
                ];

                $linkurl = http_build_query($datalink);
                $url_activity = U('activityDetails','','');
                $url_activity = 'http://'.WEB_URL.$url_activity.'?'.$linkurl;
                $url_activity = str_replace('activity/','Activity/',$url_activity);
                $social['url'] = $url_activity;
            }

            $info = array('status' => 200, 'result' => $data,'result2'=>$imgmap,'result3'=>$social);

            if (empty($info['result'])) {
                unset($info['result']);
            }

            if (empty($info['result2'])) {
                unset($info['result2']);
            }
            if (empty($info['result3'])) {
                unset($info['result3']);
            }

            $this->ajaxReturn( $info ,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $this->ajaxReturn(array('status' => 400, 'result' => array()));
        }

    }



    
    /**
     * @描述：获得节假日信息
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function holiday_activity()
    {    
        $model=D('Ad');
        $result=$model->getLunbo(AD_TYPE);  
        if(empty($result)){
            $this->error('异常访问');
        }
        
        $data=$result[0];
        $holiday_status=C('HOLIDAY_STATUS');
        if($holiday_status==HOLIDAYOPEN){
            $this->assign('jump_type',$data['url_jump_type']);
            $this->assign('url',$data['target_url']);
            $this->assign('childPageUrl',$data['child_page_url']);
            $this->assign('webUrl',$data['web_url']);
            $this->assign('file_name',C('oss_path').$data['file_path']);
            $this->display('holiday_activity');
        }else{
            $this->error('异常访问');
        } 
    }

    /**
     * @描述：根据活动类型获取活动列表
     * @参数：cate[int] Y 活动类型
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getActivityByClass()
    {   
        $category = getParameter('cate', 'int',false);
        $pageIndex = getParameter('pageIndex', 'int',false);
        $result = $this->activityModel->getActivities($category, '',$pageIndex, $this->pageSize);
        foreach ($result as &$val) {
            $val['img_url'] = "http://" . WEB_URL . "/Resources/socialactivity/" . $val['short_content'];
            unset($val['short_content']);
        } 
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }
    
    
    /*
     * 查看某一个活动下的所有作品
     * @param   id 活动id
     * @param   keyword 关键字
     * @param   pageIndex分页           有排名
     */
    public function getActivityWorks(){
        $activity_id=getParameter('id', 'int'); 
        $keyword=trim(getParameter('keyword', 'str',false));
        $pageIndex = getParameter('pageIndex', 'int',false);    
        if(!$activity_id){
            $this->ajaxReturn(array('status' => 500, 'result' => '参数有误'));
        }
        $result = $this->activityModel->getActivityWorks($activity_id,$keyword,$pageIndex, $this->pageSize);  
        
        for($i=0;$i<count($result);$i++){
            if($i==0){ 
                $result[$i]['ranking']=1;
            }else{  
                 if($result[$i]['point']==$result[$i-1]['point']){ 
                     $result[$i]['ranking']=$result[$i-1]['ranking'];
                 }else{ 
                     $result[$i]['ranking']=$i+1;
                 }
            } 
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }


    /**
     * @描述：获取我参加的活动列表
     * @参数：userId/user_id[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：pageIndex[int] N 页码
     * @返回值：array(
     *    status 状态码
     *    message 消息
     *    result 结果数组
     * )
     */

    public function getMyInvolvementActivity(){
        $user_id= getParameter('user_id', 'int',false);
        if(empty($user_id))
          $user_id = getParameter('userId', 'int');
        $role = getParameter('role','int',false);
        if(empty($role))
          $role = ROLE_TEACHER;
        $pageIndex = getParameter('pageIndex', 'int',false);
        $result = $this->activityModel->getMyInvolvementActivity($user_id,$role,$pageIndex, $this->pageSize);
        foreach($result as $k=>$val){
            foreach($val as $key=>$value){
                $result[$k][$key]['img_url'] = "http://" . WEB_URL . "/Resources/socialactivity/" . $value['short_content'];
            }
        }  
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：获取我参加的活动列表(三端角色)
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：pageIndex[int] N 页码
     * @返回值：array(
     *    status 状态码
     *    message 消息
     *    result 结果数组
     * )
     */
    public function getInvolvementActivity(){

        $userId = getParameter('userId', 'int');
        $role = getParameter('role','int');
        $pageIndex = getParameter('pageIndex', 'int',false);

        $result = $this->activityModel->getMyInvolvementActivity($userId,$role,$pageIndex, $this->pageSize);
        $convertArray = array(
            array('id'=> TYPE_FIELD),array('id'),
            array('date'=> TYPE_FIELD),array('date'),
            array('http://'.$_SERVER['SERVER_NAME']."/Resources/socialactivity/"  => TYPE_STRING,'short_content' =>TYPE_FIELD) ,array('img_url'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/Activity/activityDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
        );
        $resultOut = array();
        foreach ($result as $val) {
            $data = fieldsCompose($val,$convertArray);
            $resultOut[] = array('date' => $data[0]['date'],'data'=> $data);
        }
        $this->showMessage( 200,'success',$resultOut);
    }
    /*
     * 我收藏的作品列表
     * @param   user_id 用户id
     * @param   role 角色 2,3,4
     * @param   keyword 关键字
     * @param   pageIndex分页
     */
    public function getCollectActivityWorksList(){ 
        $user_id=getParameter('user_id', 'int');
        $role=getParameter('role', 'int');
        if($role>=2 && $role<=4){
            $pageIndex = getParameter('pageIndex', 'int',false);
            $keyword=trim(getParameter('keyword', 'str',false));
            $result = $this->activityModel->getMyCollectActivityWorks($user_id,$role,$keyword,$pageIndex, $this->pageSize);
            foreach($result as $key=>$val)
            {
                if(is_null($val['course_name'])) {
                    $result[$key]['course_name'] = '';
                }
                if(is_null($val['grade'])) {
                    $result[$key]['grade'] = '';
                }

                $result[$key]['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ApiInterface/Version1_1/Activity/activityWorksDetail?id=' . $val['works_id'];
            }
        }else{
            $this->ajaxReturn(array('status' => 500, 'result' => '参数有误'));
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    
    
    /**
     * @描述：获取最新活动
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getFirstActivity()
    {
        $category = getParameter('cate', 'int');
        $result = $this->activityModel->getActivities($category, '',0, 1);
        foreach ($result as &$val) {
            $val['img_url'] = "http://" . WEB_URL . "/Resources/socialactivity/" . $val['short_content'];
            unset($val['short_content']);
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：获取我收藏的活动列表
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 2-教师 3-学生 4-家长 
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyFavor()
    {
        $userInfo['user_id'] = getParameter('user_id', 'int');
        $userInfo['user_type'] = getParameter('role', 'int') - 1;
        $pageIndex = getParameter('pageIndex', 'int',false);
        $keyword = trim(getParameter('keyword', 'str',false));
        $result = $this->activityModel->getFavorActivities($userInfo, $pageIndex, $this->pageSize,$keyword);
        foreach ($result as &$val) {
            $val['img_url'] = "http://" . WEB_URL . "/Resources/socialactivity/" . $val['short_content'];
            $val['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ApiInterface/Version1_1/Activity/activityDetails?id=' . $val['id'];
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：活动详情
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 2-教师 3-学生 4-家长
     * @参数：id[int] Y 活动ID
     * @参数：flag[int] Y 1为渲染分享页面
     * @返回：活动HTML页面
     */
    public function activityDetails($id='',$userId='',$role='')
    {

        $id = ($id == '') ? getParameter('id', 'int', false) : $id;
        $userId = ($userId == '') ? getParameter('user_id', 'int', false) : $userId;
        $role = ($role == '') ? getParameter('role', 'int', false) : $role;
        $flag = ($flag == '') ? getParameter('flag', 'str', false) : $flag;
        $this->assign('flag',$flag);
        $url = WEB_URL.$_SERVER['REQUEST_URI'];
        $url = str_replace('&flag=1','',$url);

        $this->assign('urldata',"http://".$url );
        $activityController = new \Home\Controller\ActivityController();
        $activityController->is_vote($id);
        if ($flag == 1) {
            if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
                redirect('/index.php?m=Home&c=Activity&a=activityApplyDetails&id='.$id);
            }      
            $result = $this->activityModel->getActivityDetails($id);

            if (!empty($result)) {
                $activity_course_info = $this->activityModel->getActivityCourse($id);       
                //关键附件
                $activity_contact_file = $this->activityModel->getActivityFileInfo($id);

                $this->activityModel->setBrowseCountPlusOne($id);
                //报名信息
                $regInfo = $this->activityModel->getRegistered($id, $userId);

                $zanData = $this->activityModel->getIsZan($id, $userId, $role);
                $favorData = $this->activityModel->getIsFavor($id, $userId, $role);

                $this->assign('activity_course_info', $activity_course_info);
                $this->assign('registered', $regInfo['reged']);
                $this->assign('register_info', $regInfo['info']);
                $this->assign('existedZan', $zanData);
                $this->assign('existedFavor', $favorData);
                $result['content_copy'] = mb_substr(strip_tags($result['content']),0,40);
                $this->assign('data', $result);
                $this->assign('activity_contact_file', $activity_contact_file);
                $this->assign('user_id', $userId);
                $this->assign('role', $role);
            }

            $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
            $signPackage = $jssdk->GetSignPackage();
            $this->assign('signPackage',$signPackage);
            $this->assign('WEB_URL',WEB_URL);
            $this->display('activityShare');
            exit();
        } else {  
            if ($role >= 2 && $role <= 5 && $id) {

                $result = $this->activityModel->getActivityDetails($id);
                //如果是直播且开启了直跳链接则直接跳
                if($result['is_live']) {
                    if ($result['home_jump_url'] == 1 && time() <= $result['liveend'] && time() >= $result['livestart']) {
                        if(strpos($result['url'],'?') !== false)
                            $result['url'] .= "&userId=$userId&role=$role";
                        else
                            $result['url'] .= "?userId=$userId&role=$role";
                        if(strpos($result['url'],'://') === false)
                            $result['url'] =  'http://'.$result['url'];
                        echo "<script>location.replace(\"{$result['url']}\")</script>";
                        //header('Location:' . $result['url']);
                        exit;
                    }
                }
                if (!empty($result)) {
                    $register_people_number_status=1;
                    if($result['apply_people_number']!=0){
                        if($result['apply_people_number']<=$result['register_numbers']){
                            $register_people_number_status=2;
                        }
                    }
                    if($result['applystart']<=time() && $result['applyend']>=time()){
                        $activity_status=1;
                    }else{
                        if($result['applystart']>time()){
                            $activity_status=2;
                        }else{
                            $activity_status=3;
                        }
                        
                    }   
                    $activity_course_info = $this->activityModel->getActivityCourse($id);
                    //关键附件
                    $activity_contact_file = $this->activityModel->getActivityFileInfo($id);

                    $this->activityModel->setBrowseCountPlusOne($id);
                    //报名信息
                    $regInfo = $this->activityModel->getRegistered($id, $userId,$role);
                    $zanData = $this->activityModel->getIsZan($id, $userId, $role);
                    $favorData = $this->activityModel->getIsFavor($id, $userId, $role);

                    $hasUploadWork = $this->activityModel->getHasUploadWorks($id, $userId,$role);
                    $this->assign('hasUploadWork', $hasUploadWork);

                    $this->assign('activity_course_info', $activity_course_info);       
                    $this->assign('registered', $regInfo['reged']);
                    $this->assign('register_info', $regInfo['info']);

                    $this->assign('existedZan', $zanData);
                    $this->assign('existedFavor', $favorData);
 
                    $this->assign('data', $result);
                    $this->assign('activity_contact_file', $activity_contact_file);
                    $this->assign('user_id', $userId);
                    $this->assign('role', $role);
                    $this->assign('activity_status',$activity_status);
                    $this->assign('register_people_number_status',$register_people_number_status);
                    $aid = getParameter('aid','int',false);
                    if(!empty($aid))
                        D('Monitor')->setIncFigure($aid);
                }
                else
                {
                    $this->display('');
                    exit;
                }
            }
        }
        $this->display('');
    }
    
    /*
     * 取消或点赞活动
     * @id          活动id
     * @user_id     用户id
     * @role        角色 2,3,4
     */
    public function operationActivityZan($id=0,$user_id=0,$role=0){
        $activity_id = ($id == 0) ? getParameter('id', 'int') : $id;
        $user_id=($user_id == 0) ? getParameter('user_id', 'int') : $user_id;
        $role=($role == 0) ? getParameter('role', 'int') : $role;
        if($activity_id && $user_id && $role>=2 && $role<=4){ 
            $is_exists=$this->activityModel->getUserIsExists($role,$user_id);
            if(!$is_exists){
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
            
            $existed_zan=$this->activityModel->getIsZan($activity_id,$user_id,$role);
            if($existed_zan=='no'){
                $status=$this->activityModel->operationZanActivity(1,$activity_id,$user_id,$role);
            }else{
                $status=$this->activityModel->operationZanActivity(2,$activity_id,$user_id,$role);
            }
            if($status==true){
                $existed_zan = ($existed_zan == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success',"result" => $existed_zan));
            }else{
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
        }else{
            $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
        }
        
    }
    
    
    
    /*
     * 取消或收藏活动
     * @id          活动id
     * @user_id     用户id
     * @role        角色 2,3,4
     */
    public function operationActivityFavor($id=0,$user_id=0,$role=0){
        $activity_id = ($id == 0) ? getParameter('id', 'int') : $id;
        $user_id=($user_id == 0) ? getParameter('user_id', 'int') : $user_id;
        $role=($role == 0) ? getParameter('role', 'int') : $role;
        if($activity_id && $user_id && $role>=2 && $role<=4){
            $is_exists=$this->activityModel->getUserIsExists($role,$user_id);
            if(!$is_exists){
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
            
            $existed_favor=$this->activityModel->getIsFavor($activity_id,$user_id,$role);

            if($existed_favor=='no'){
                $status=$this->activityModel->operationFavorctivity(1,$activity_id,$user_id,$role);
            }else{
                $status=$this->activityModel->operationFavorctivity(2,$activity_id,$user_id,$role);
            }
            if($status==true){
                $existed_favor = ($existed_favor == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success',"result" => $existed_favor));
            }else{
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
        }else{
            $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
        }
        
    }
    
    
    /*
     * 查看报名信息
    */
    public function registrationinformation(){
        $activity_id = getParameter('id', 'int',false);
        $user_id=getParameter('user_id', 'int',false);
        $role=getParameter('role', 'int',false);
        if($activity_id && $user_id && $role){
            $activity_info=$this->activityModel->getActivityDetails($activity_id);
            $registration_info=$this->activityModel->getActivityRegistration($activity_id,$user_id,$role);
            if(!empty($registration_info)){
                if($registration_info['applystart']<=time() && $registration_info['applyend']>=time()){
                    $activity_status=1;
                }else{ 
                    $activity_status=2;
                } 
                
                $education=c('education');
                $positions=c('professional');
                foreach($education as $k=>$v){
                    if($k==$registration_info['education']){
                        $registration_info['education']=$v;
                    }
                }
                foreach($positions as $key=>$val){
                    if($key==$registration_info['positions']){
                        $registration_info['positions']=$val;
                    }
                } 
                $this->assign('activity_status',$activity_status);
                $this->assign('id',$activity_id);
                $this->assign('user_id',$user_id);
                $this->assign('role',$role);
                $this->assign('register_id',$registration_info['id']);
                $this->assign('activityData',$activity_info);
                $this->assign('data',$registration_info);
            }
        }  
        $this->display();
    }

    /**
     * @描述：活动报名
     * @参数：teacher_id[int] Y 教师ID
     * @参数：register_info[string] Y 报名信息
     * @参数：id[int] Y 活动ID
     * @返回：重定向活动HTML页面
     */
    public function signupInformation()
    {
        if ($_POST) {
            if (!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
                $controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId, $role);
            } else {
                $role = getParameter('role', 'int');
            }
            $activity_id = getParameter('activity_id', 'int');
            $resources = M('social_activity')->field('selectedfields,additional_info')->where("id = $activity_id")->find();
            $arr = explode(',', $resources['selectedfields']);
            $edit_status = getParameter('edit_status', 'int');
            $user_id = getParameter('user_ids', 'int');

            if (in_array('7', $arr)) {
                $sex = getParameter('sex', 'str');
            }
            if (in_array('2', $arr) && $role == ROLE_TEACHER) {
                $course = getParameter('course', 'int');
            }

            if (in_array('3', $arr) && $role == ROLE_TEACHER) {
                $lesson = getParameter('lesson', 'str');
            }
            if (in_array('4', $arr)) {    //省市区
                $provinceId = getParameter('province_choose', 'int');
                $cityId = getParameter('city_choose', 'int');
                $districtId = getParameter('district_choose', 'int');
            }
            if (in_array('5', $arr) && ($role == ROLE_TEACHER || $role == ROLE_STUDENT)) {
                $schoolName = getParameter('school_name', 'str');
            }
            if (in_array('6', $arr) && ($role == ROLE_TEACHER || $role == ROLE_STUDENT) ) {
                $schoolAddress = getParameter('school_address', 'str');
            }

            if (in_array('8', $arr)) {
                $age = getParameter('age', 'int');
            }

            if (in_array('9', $arr) && $role == ROLE_TEACHER) {
                $education = getParameter('education', 'int');
            }

            if (in_array('10', $arr) && $role == ROLE_TEACHER) {
                $positions = getParameter('positions', 'int');
            }

            if (in_array('12', $arr) && ($role == ROLE_TEACHER || $role == ROLE_STUDENT)) {
                $post_code = getParameter('post_code', 'str');
            }
            if (in_array('13', $arr) && $role == ROLE_TEACHER) {
                $tel = getParameter('tel', 'str', false);
            }
            if (in_array('15', $arr) && $role == ROLE_TEACHER) {
                $local_course = getParameter('local_course', 'str');
            }
            if (in_array('16', $arr) && $role == ROLE_TEACHER) {
                $school_course = getParameter('school_course', 'str');
            }
            if (in_array('11', $arr)) {
                $email = getParameter('email', 'str');
            }
            if (in_array('14', $arr)) {
                $mobile = getParameter('mobilePhone', 'str');
            }

            if ($resources['additional_info'] != '' && $resources['additional_info'] != '[]') {
                $additionalRegInfos = getParameter('additionalRegInfo', 'sArr');
                foreach ($additionalRegInfos as $items) {
                    urlencode($items);
                }
                $additionalRegInfo = implode(',', $additionalRegInfos);
            }
            if ($role == ROLE_TEACHER) {
                $Info = $this->activityModel->getTeacherSchoolInfo($user_id);
                if (empty($Info)) {
                    $this->ajaxReturn(array('status' => 400, 'info' => '教师信息不存在'));
                } else {
                    $user_name = $Info['name'];
                }
            } elseif ($role == ROLE_STUDENT) {
                $Info =  $this->activityModel->getStudentSchoolInfo($user_id);
                if (empty($Info)) {
                    $this->ajaxReturn(array('status' => 400, 'info' => '学生不存在'));
                } else {
                    $user_name = $Info['name'];
                }
            } elseif ($role == ROLE_PARENT) {
                $parent_model = D('Auth_parent');
                $Info = $parent_model->getParentInfo($user_id);
                if (empty($Info)) {
                    $this->ajaxReturn(array('status' => 400, 'info' => '家长不存在'));
                } else {
                    $user_name = $Info['parent_name'];
                }
            }
            $course_info = $this->activityModel->getAvailableRegCourse($user_id, $activity_id);  //这里之前

            if ($course) {
                for ($i = 0; $i < count($course_info); $i++) {
                    if ($course_info[$i]['course_id'] == $course) {
                        break;
                    } else {
                        if ($i == (count($course_info) - 1)) {
                            $this->ajaxReturn(array('status' => 401, 'info' => '请选择与活动相符的学科'));
                        }
                    }
                }
            }

            $invitation_code = '';
            $invitation_code_status = 0;
            $activity_info = $this->activityModel->getActivityDetails($activity_id);
            if (empty($activity_info)) {
                $this->ajaxReturn(array('status' => 402, 'info' => '活动信息不存在'));
            } else {
                if (($activity_info['applystart'] <= time() && $activity_info['applyend'] >= time()) == false) {
                    $this->ajaxReturn(array('status' => 410, 'info' => '提交失败, 活动报名已结束'));
                }
                if ($activity_info['apply_people_number'] != 0 && $edit_status == 0) {
                    if ($activity_info['apply_people_number'] <= $activity_info['register_numbers']) {
                        $this->ajaxReturn(array('status' => 411, 'info' => '报名人数已满'));
                    }
                }
            }

            if ($course) {
                if (!empty($activity_info['course'])) {
                    if ($activity_info['course'][0] != 0) {
                        if (!in_array($course, $activity_info['course'])) {
                            $this->ajaxReturn(array('status' => 403, 'info' => '该活动不支持您填写的学科'));
                        }
                    }
                }
            }


            if ($edit_status == 0) {
                if ($this->activityModel->hasRegActivity($activity_id, $user_id,$role)) {
                    $this->ajaxReturn(array('status' => 407, 'info' => '该活动已报名'));
                }
                if ($activity_info['is_generate'] == 2 && $activity_info['is_disable'] == 1) {
                    $check['code'] = getParameter('Inviteid_code', 'str');
                    $check['id'] = $activity_id;
                    $invitation_code = $check['code'];
                    if ($this->activityModel->getVerificationCode($check) == false) {
                        $this->ajaxReturn(array('status' => 404, 'info' => '邀请码有误'));
                    } else {
                        $invitation_code_status = 1;
                    }
                }
            }

            if ($education && $positions) {
                if (!$this->is_exists_education_positions($education, $positions)) {
                    $this->ajaxReturn(array('status' => 405, 'info' => '职称或学历信息有误'));
                }
            }
            if ($local_course && $school_course) {
                if (($local_course != 0 && $local_course != 1) || ($school_course != 0 && $school_course != 1)) {
                    $this->ajaxReturn(array('status' => 406, 'info' => '本地课程或学校课程有误'));
                }
            }


            $regData = array(
                'activity_id' => $activity_id,
                'user_id' => $user_id,
                'user_name' => $user_name,
                'register_at' => time(),
                'user_type' => 1,
                'invitation_code' => $invitation_code,
                'role' => $role,
                'additional_info' => $additionalRegInfo,
                'lesson' => empty($lesson) ? '' : $lesson,
                'course' => empty($course) ? '' : $course,
                'province' => empty($provinceId)?'':$provinceId ,
                'city' => empty($cityId)?'':$cityId ,
                'district' => empty($districtId)?'':$districtId ,
                'sex' => empty($sex) ? '' : $sex,
                'age' => empty($age) ? '' : $age,
                'positions' => empty($positions) ? '' : $positions,
                'education' => empty($education) ? '' : $education,
                'email' => empty($email) ? '' : $email,
                'school_name' => empty($schoolName) ? '' : $schoolName,
                'school_address' => empty($schoolAddress) ? '' : $schoolAddress,
                'post_code' => empty($post_code) ? '' : $post_code,
                'tel' => empty($tel) ? '' : $tel,
                'telephone' => empty($mobile) ? '' : $mobile,
                'local_course' => empty($local_course) ? '' : $local_course,
                'school_course' => empty($school_course) ? '' : $school_course,

            );
            if ($edit_status == 1) {
                unset($regData['activity_id']);
                unset($regData['user_id']);
                unset($regData['register_at']);
                unset($regData['invitation_code']);
                if (!$this->activityModel->editRegActivity($regData, $activity_id, $user_id)) {
                    $this->ajaxReturn(array('status' => 408, 'info' => '修改报名失败'));
                }
            } else {
                if (!$this->activityModel->regActivity($regData, $invitation_code_status)) {
                    $this->ajaxReturn(array('status' => 409, 'info' => '报名失败'));
                }
            }
            //push success info
            $activityInfo = D('Social_activity')->getActivityDetails($activity_id);
            $parameters = array('msg' => array(date("Y-m-d H:i:s", time()), $activityInfo['title'], date("Y-m-d H:i:s", $activityInfo['activitystart'])), 'url' => array('type' => 0));
            if(0 == $edit_status) {
                $controller_obj = new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('ACTIVITY_REG_SUCCESS', $role, $user_id, $parameters);
            }
            $this->ajaxReturn(array('status' => 200, 'info' => '报名成功'));
        } else {
            if (!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
                $controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId, $role);
            } else {
                $role = getParameter('role', 'int', false);
            }
            $activity_id = getParameter('activity_id', 'int', false);
            $user_id = getParameter('user_id', 'int', false);
            $activity_register_id = getParameter('register_id', 'int', false);
            if ($activity_id && $user_id && $role) {
                $common_model = D('Common');
                if ($role == 2) {
                    $teacher_model = D('Auth_teacher');
                    $teacher_info = $teacher_model->getTeachInfo($user_id);
                    if (empty($teacher_info)) {
                        echo '<script>alert("教师不存在");history.go(-1);</script>';exit;
                    }
                } elseif ($role == 3) {
                    $student_model = D('Auth_student');
                    $student_info = $student_model->getStudentInfo($user_id);
                    if (empty($student_info)) {
                        echo '<script>alert("学生不存在");history.go(-1);</script>';exit;
                    }
                } elseif ($role == 4) {
                    $parent_model = D('Auth_parent');
                    $parent_info = $parent_model->getParentInfo($user_id);
                    if (empty($parent_info)) {
                        echo '<script>alert("家长不存在");history.go(-1);</script>';exit;
                    }
                }
                if ($activity_id && $user_id) {
                    $common_model = D('Common');
                    switch ($role) {
                        case ROLE_TEACHER:
                            $userModel = D('Auth_teacher');
                            $userInfo = $userModel->getTeachInfo($user_id);
                            break;
                        case ROLE_STUDENT:
                            $userModel = D('Auth_student');
                            $userInfo = $userModel->getStudentInfo($user_id);
                            break;
                        case ROLE_PARENT:
                            $userModel = D('Auth_parent');
                            $userInfo = $userModel->getParentInfo($user_id);
                            $school_info = $userInfo;
                            $school_info['name'] = $school_info['parent_name'];
                            break;
                        default:
                            break;
                    }
                    $activity_info = $this->activityModel->getActivityDetails($activity_id);
                    if (empty($activity_info)) {
                        echo '<script>alert("活动不存在");history.go(-1);</script>';exit;
                    } else {
                        if (($activity_info['applystart'] <= time() && $activity_info['applyend'] >= time()) == false) {
                            echo '<script>alert("当前时间不在活动报名时间范围内");history.go(-1);</script>';exit;
                        }
//                        if ($activity_info['apply_people_number'] != 0 ) {
//                            if ($activity_info['apply_people_number'] <= $activity_info['register_numbers']) {
//                                $this->ajaxReturn(array('status' => 400, 'info' => '报名人数已满'));
//                            }
//                        }
                    }
                    $status = 1;
                    if ($activity_info['is_disable'] != 1 || $activity_info['is_generate'] != 2) {
                        $status = 0;
                    }
                    if (ROLE_TEACHER == $role) {
                        $course_result = $this->activityModel->getTeacherAllCourse($user_id);
                        $allowed_activity = $this->activityModel->getAvailableRegCourse($user_id, $activity_id);
                        if (!empty($allowed_activity)) {
                            $allowed_reg_activity = array('status' => 1, 'data' => $allowed_activity);
                        } else {
                            $allowed_reg_activity = array('status' => 0);
                        }
                    }
                    $education = c('education');
                    $positions = c('professional');

                    //是否存在注册id
                    if ($activity_register_id) {
                        $activity_register_info = $this->activityModel->getActivityRegistration($activity_id, $user_id, $role);
                        if (empty($activity_register_info)) {
                            echo '<script>alert("报名信息不存在");history.go(-1);</script>';exit;
                        }
                        $this->assign('activity_register_info', $activity_register_info);
                        $this->assign('last_reg_info_status', 0);
                        $this->assign('register_status', 1);

                    } else {
                        if (ROLE_TEACHER == $role)
                            $school_info = $this->activityModel->getTeacherSchoolInfo($user_id);
                        else if (ROLE_STUDENT == $role)
                            $school_info = $this->activityModel->getStudentSchoolInfo($user_id);
                        $last_reg_info = $this->activityModel->getLastRegInfo($user_id, $role);
                        $last_reg_info_status = 0;
                        if (!empty($last_reg_info)) {
                            $last_reg_info_status = 1;
                        }

                        $this->assign('school_info', $school_info);
                        $this->assign('last_reg_info', $last_reg_info);
                        $this->assign('last_reg_info_status', $last_reg_info_status);
                        $this->assign('register_status', 0);
                    }
                    $this->assign('user_id', $user_id);
                    $this->assign('role', $role);
                    $this->assign('activity_id', $activity_id);
                    $this->assign('data', $activity_info);
                    $this->assign('education', $education);
                    $this->assign('positions', $positions);
                    $this->assign('course_list', $course_result);
                    $this->assign('show_invitationCode', $status);
                    $this->assign('allowed_reg_activity', $allowed_reg_activity);

                    $this->display();
                }
            }
        }
    }
    
    /*
     * 判断职称和学历是否存在于配置文件中
     */
    public function is_exists_education_positions($education=0,$positions=0){   
        if(!$education || !$positions){
            return false;
        }else{
            $education_config_arr=c('education');
            $positions_config_arr=c('professional');
            $k=0;
            $education_count=count($education_config_arr);
            $positions_count=count($positions_config_arr);
            foreach($education_config_arr as $e_key=>$e_val){
                $k++;                       
                if($e_key==$education){
                    break;
                }else{
                    if($k==$education_count){ 
                        return false;
                    }
                }
            }
            $k=0;
            foreach($positions_config_arr as $p_key=>$p_val){
                $k++;
                if($p_key==$positions){
                    break;
                }else{
                    if($k==$positions_count){ 
                        return false;
                    }
                }
            }
            return true;
        }
    }
    
    /*
     * 对某个文字在学校中进行检索
     */
    public function searchSchool(){ 
        $isset_keyword=isset($_GET['keyword'])?true:false;
        if($isset_keyword===false){
            $this->ajaxReturn(array('status' => 400, 'info' =>'')); 
        }else{
            $keyword=trim($_GET['keyword']);
            if($keyword==''){
                $this->ajaxReturn(array('status' => 200, 'info' => array()));
            }else{
                $result=$this->activityModel->searchSchool($keyword);
            }
            $this->ajaxReturn(array('status' => 200, 'info' => $result)); 
        }
    }
    

    /**
     * @描述：验证邀请码
     * @参数：id[int] Y 活动id
     * @参数：invitation_code[string] Y 邀请码
     * @返回值：array(
     *    status 状态码
     * )
     */
    public function VerificationCode() {
        $id = getParameter('id', 'int');
        $code = getParameter('code', 'str');
        $regData['id'] = $id;
        $regData['code'] = $code;
        if(!$id || !$code){
             $this->ajaxReturn(array('status' => 500, 'info' => '参数有误'));
             die;
        }
        $info = $this->activityModel->getVerificationCode($regData);

        if ($info) {
            if ($info['status'] == ACTIVITY_CODE_STATUS_USED) {
                $this->ajaxReturn(array('status' => 400, 'info' => '邀请码已使用'));
            } else {
                $this->ajaxReturn(array('status' => 200, 'info' => '邀请码正确'));
            }
        } else {
            $this->ajaxReturn(array('status' => 500, 'info' => '验证码错误'));
        }
    }
 


    /**
     * @描述：活动作品详情
     * @参数：id[int] Y 活动id
     * @参数：role  2,3,4
     * @返回值：array(
     *    status 状态码
     * )
     */
    public function activityWorksDetail(){
        $works_id = getParameter('id', 'int',false);
        $user_id=getParameter('userId', 'int',false);
        $role=getParameter('role', 'int',false);
        
        if($role>=2 && $role<=5 && $works_id ){    
            $works_result=$this->activityModel->getWorksInfo($works_id);    
            $works_file_info=array();   
            if(!empty($works_result)){ 
                //关联文件
                $works_file_info=$this->activityModel->getWorksFileInfo($works_id); 
                //点赞和收藏
                $existed_favor=$this->activityModel->getWorksIsFavor($works_id,$user_id,$role);
                $existed_zan=$this->activityModel->getWorksIsZan($works_id,$user_id,$role);    
                //浏览量增加
                $this->activityModel->addBrowseCount($works_id);
            } 

            $this->assign('data',$works_result);
            $this->assign('works_file_list',$works_file_info);
            $this->assign('existed_zan',$existed_zan);
            $this->assign('existed_favor',$existed_favor);
            
            $this->assign('userId',$user_id);
            $this->assign('role',$role);
        }  
        $this->display();
    }
    
    
    /*
     * 取消或点赞作品
     * @works_id    作品id
     * @user_id     用户id
     * @role        角色 2,3,4
     */
    public function operationWorksZan($works_id = 0 ,$user_id = 0,$role = 0){
        $works_id = ($works_id == 0) ? getParameter('id', 'int'):$works_id;
        $user_id = ($user_id == 0) ?getParameter('user_id', 'int'):$user_id;
        $role = ($role == 0) ? getParameter('role', 'int'):$role;
        if($works_id && $user_id && $role>=2 && $role<=4){
            $is_exists=$this->activityModel->getUserIsExists($role,$user_id);
            if(!$is_exists){
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
            $works_info=$this->activityModel->getWorksInfo($works_id);  
            if(empty($works_info)){
                $this->ajaxReturn(array('status' => 401, 'info' => 'error'));
            }
            if($works_info['user_id']==$user_id && $works_info['register_role']==($role-1)){
                $this->ajaxReturn(array('status' => 402, 'info' => 'error'));
            }
            
            $existed_zan=$this->activityModel->getWorksIsZan($works_id,$user_id,$role);     
            if($existed_zan=='no'){
                $status=$this->activityModel->operationZanWorks(1,$works_id,$user_id,$role);
            }else{
                $status=$this->activityModel->operationZanWorks(2,$works_id,$user_id,$role);
            } 
            if($status==true){
                $existed_zan = ($existed_zan == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success','result' => $existed_zan));
            }else{
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            } 
        }else{ 
            $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
        }
    }

    /*
     * 打开或者下载app页面
     * @works_id    作品id
     * @user_id     用户id
     */

    public function download() {
        $id = $_GET['id'];
        $title = $_GET['title'];
        $this->assign('id',$id);
        $this->assign('title',$title);
        $this->display();
    }

    /*
     * 取消或收藏作品
     * @works_id    作品id
     * @user_id     用户id
     * @role        角色    2,3,4
     */
    public function operationWorksFavor($works_id = 0 ,$user_id = 0,$role = 0){
        $works_id = ($works_id == 0) ? getParameter('id', 'int'):$works_id;
        $user_id = ($user_id == 0) ?getParameter('user_id', 'int'):$user_id;
        $role = ($role == 0) ? getParameter('role', 'int'):$role;
        if($works_id && $user_id && $role>=2 && $role<=4){
            $is_exists=$this->activityModel->getUserIsExists($role,$user_id);
            if(!$is_exists){
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
            $existed_favor=$this->activityModel->getWorksIsFavor($works_id,$user_id,$role);
            if($existed_favor=='no'){
                $status=$this->activityModel->operationFavorWorks(1,$works_id,$user_id,$role);
            }else{
                $status=$this->activityModel->operationFavorWorks(2,$works_id,$user_id,$role);
            }
            if($status==true){
                $existed_favor = ($existed_favor == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success','result' => $existed_favor));
            }else{
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            } 
        }else{
            $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
        }
    }


    /**
     * @描述：活动首页列表
     * @返回值：array(
     *    status 状态码
     *    message 消息
     *    result 结果数组
     * )
     */
     public function getHomeActivityList()
     {
         $result = array();
         //$queryArray['p'] =getParameter('pageIndex','int',false);
         $specialColumnList = $this->activityModel->get_column(); //get all specialColumn
         for ($i = 0; $i < sizeof($specialColumnList); $i++)
         {
             $specialColumnId = $specialColumnList[$i]['id'];
             $list = $this->activityModel->get_column_resource($specialColumnId,REQUESTFROM_APP);
             for($j=0; $j < sizeof($list) ;$j++)
             {
                 $list[$j]['content1'] = mb_substr( strip_tags($list[$j]['content1']),0,40);
                 $list[$j]['content2'] = mb_substr( strip_tags($list[$j]['content2']),0,40);
             }

             foreach ($list as $k=>&$v) {
                 if ($v['id'] == 521) {
                     $v['img_url'] = "http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/App/sinology_app.png";
                     break;
                 }
             }

             $result['specialColumn'][] = array('id' => $specialColumnId ,'name' => $specialColumnList[$i]['name'], 'data' => $list);
         }
         $columnList = $this->activityModel->getDisplayColumn(); //get all column
         for ($i = 0; $i < sizeof($columnList); $i++)
         {
             $columnId = $columnList[$i]['id'];
             $list = $this->activityModel->get_index_list(10000,$columnId,'',array(),REQUESTFROM_APP);
             for($j=0; $j < sizeof($list) ;$j++)
             {
                 $list[$j]['content1'] = mb_substr( strip_tags($list[$j]['content1']),0,40);
                 $list[$j]['content2'] = mb_substr( strip_tags($list[$j]['content2']),0,40);
             }
             foreach ($list as $k=>&$v) {
                 if ($v['id'] == 521) {
                     $v['img_url'] = "http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/App/sinology_app.png";
                     break;
                 }
             }
             $this->liveDataCallback($list);
             $result['column'][] = array('id' => $columnId ,'name' => $columnList[$i]['name'], 'data' => $list);
         }
         $list = $this->activityModel->getActivities(0, '', -1, 6, $count,  0,  0,  0, 3 ,REQUESTFROM_APP);
         foreach ($list as $k=>&$v) {
             if ($v['id'] == 521) {
                 $v['img_url'] = "http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/App/sinology_app.png";
                 break;
             }
         }
         $result['column'][] = array('id' => 0 ,'name' => '历史活动', 'data' => $list);

         $this->showMessage(200,'success',$result);
     }

    /**
     * @描述：获取筛选后的活动列表
     * @参数：classId[int] N 活动类型 (赛事 培训等)
     * @参数：keyword[str] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：type[int] N 时间条件 (1 -- 未开始 2 -- 进行中 3 -- 历史活动)
     * @返回值：array(
     *    status 状态码
     *    message 消息
     *    result 结果数组
     * )
     */
    public function getFilteredActivityList()
    {
        $keyword = trim(getParameter('keyword','str',false));
        $classId = getParameter('classId','int',false);
        $pageIndex = getParameter('pageIndex','int',false);
        $type= getParameter('type','int',false);
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
        {
            $pageSize = 21;
        }
        $list = $this->activityModel->getActivities($classId, $keyword, $pageIndex, 20, $count,  0,  0,  0,$type,REQUESTFROM_APP);
        $this->liveDataCallback($list);
        $this->showMessage(200,'success',$list);
    }

    /**
     * @描述：获取专栏更多列表
     * @参数：specialColumnId[int] Y 专栏ID
     * @参数：pageIndex[int] N 页码
     * @返回值：array(
     *    status 状态码
     *    message 消息
     *    result 结果数组
     * )
     */
    public function getMoreSpecialColumn()
    {
        $columnId = getParameter('specialColumnId','int');
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        if(!empty($pageIndex))
         $_GET['p'] = $pageIndex;
        if(empty($pageSize))
        {
            $pageSize = $this->pageSize;
        }
        $list = $this->activityModel->get_column_more($columnId,$pageSize,REQUESTFROM_APP);
        $list = $list['list'];
        for($j=0; $j < sizeof($list) ;$j++)
        {
            $list[$j]['content1'] = mb_substr( strip_tags($list[$j]['content1']),0,40);
            $list[$j]['content2'] = mb_substr( strip_tags($list[$j]['content2']),0,40);
        }
        $this->liveDataCallback($list);
        $this->showMessage(200,'success',$list);

    }

    public function votingDetails()
    {

        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $userId = getParameter('userId', 'int', false);
        $role = getParameter('role', 'int', false);
        $activityId= getParameter('activity_id','int',false);
        if(empty($userId) || empty($role))
        {
            $controller = new \Home\Controller\CommonController();
            $controller->getUserIdRole($userId,$role);
            if($userId == -1)
                $userId = 0;
            if($role == -1)
                $role = 0;
        }
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $vote_id = getParameter('id','int');
        $voteInfo = D('Social_activity_vote')->getVoteData($vote_id);

        if ( $voteInfo['voteend']<time() ) {
            $details = D('Social_activity_vote')->getCandidateListOrder($vote_id);
        } else {
            $details = D('Social_activity_vote')->getCandidateList($vote_id);
        }
        
        $canVoteAndData = D('Social_activity_vote')->getCanVoteAndVoteData($vote_id,$userId,$role);

        //var_dump($details);
        $voteInfo['description_copy'] = mb_substr( strip_tags($voteInfo['description']),0,40);
        $this->assign('voteInfo',$voteInfo);
        $this->assign('details',$details);
        $this->assign('if_vote',$canVoteAndData['canVote']);
        $this->assign('voteData',$canVoteAndData['data']);
        $this->assign('oss',C('oss_path'));

        if (is_weixin()) {

            if ($voteInfo['type']==2) {
                $session_info = $_SESSION["weixin"];

                if(!empty($session_info)) {
                    $this->isFollow($vote_id);
                } else {
                    //这个链接是获取code的链接 链接会带上code参数
                    $REDIRECT_URI = "http://".WEB_URL."/ApiInterface/Version1_1/Activity/getCode?id=".$vote_id;
                    $REDIRECT_URI = urlencode($REDIRECT_URI);
                    $scope = "snsapi_userinfo";
                    $state = md5(mktime());
                    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".$REDIRECT_URI."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
                    header("location:$url");
                }
            }

        }

        if (!empty($userId) && !empty($role)){
            $DaoVote = M();
            $DaoVote->execute("INSERT INTO activity_vote_browse_statistics (vote_id,user_id,role,create_date) VALUES (".$vote_id.",".$userId.",".$role.",".date('Ymd',time()).") ON DUPLICATE KEY UPDATE browse_count=browse_count+1");
        }

        if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')))
        {
            //$this->redirect("/",array('m'=>'Home','c'=>'Activity','a'=>'vote_details','id' => $vote_id));
            $this->redirect('Home/Activity/vote_details', array('id' => $vote_id));
        }
        $this->assign('activity_id',$activityId);

        $this->display_nocache();
    }

    //用户同意之后就获取code  通过获取code可以获取一切东西了
    function getCode() {
        //获取accse_token
        $code = $_GET["code"];
        $id = $_GET["id"];
        //echo $code;
        //echo "<br>";
        //用code获取access_yoken
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$code."&grant_type=authorization_code";
        //这里可以获取全部的东西  access_token openid scope
        $res = $this->https_request($url);
        $res  = json_decode($res,true);
        $openid = $res["openid"];

        $access_token = $res["access_token"];
        //echo $access_token;
        //这里是获取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = $this->https_request($url);
        $res = json_decode($res,true);

        $_SESSION["weixin"]=$res;
        header("location:http://".WEB_URL."/ApiInterface/Version1_1/Activity/isFollow?id=".$id);
    }

    //是否关注
    public function isFollow() {
        $vote_id = $_GET['id'];

        if ( !empty($_SESSION['weixin']) ){
            $DaoVote = M();
            $struser = (string)$_SESSION['weixin']["openid"];
            $DaoVote->execute("INSERT INTO activity_vote_browse_statistics (vote_id,user_id,role,create_date) VALUES (".$vote_id.",'$struser',5,".date('Ymd',time()).") ON DUPLICATE KEY UPDATE browse_count=browse_count+1");
        } else {
            $session_info = $_SESSION["weixin"];

            if(!empty($session_info)) {
                $this->isFollow($vote_id);
            } else {
                //这个链接是获取code的链接 链接会带上code参数
                $REDIRECT_URI = "http://".WEB_URL."/ApiInterface/Version1_1/Activity/getCode?id=".$vote_id;
                $REDIRECT_URI = urlencode($REDIRECT_URI);
                $scope = "snsapi_userinfo";
                $state = md5(mktime());
                $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".$REDIRECT_URI."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
                header("location:$url");
            }
        }

        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".APPSECRET;
        $access_msg = json_decode(file_get_contents($access_token));
        $token = $access_msg->access_token;
        $subscribe_msg = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=".$_SESSION["weixin"]['openid'];
        $subscribe = json_decode(file_get_contents($subscribe_msg));
        $gzxx = $subscribe->subscribe;

        if($gzxx === 1){ //关注

            $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
            $signPackage = $jssdk->GetSignPackage();
            $this->assign('signPackage',$signPackage);
            $this->assign('WEB_URL',WEB_URL);

            $userId = getParameter('userId', 'int', false);
            $role = getParameter('role', 'int', false);
            $activityId= getParameter('activity_id','int',false);
            if(empty($userId) || empty($role))
            {
                $controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId,$role);
                if($userId == -1)
                    $userId = 0;
                if($role == -1)
                    $role = 0;
            }
            $this->assign('userId',$userId);
            $this->assign('role',$role);
            //$vote_id = getParameter('id','int');
            $details = D('Social_activity_vote')->getCandidateList($vote_id);
            $canVoteAndData = D('Social_activity_vote')->getCanVoteAndVoteData($vote_id,$userId,$role);
            $voteInfo = D('Social_activity_vote')->getVoteData($vote_id);
            //var_dump($details);
            $voteInfo['description_copy'] = mb_substr( strip_tags($voteInfo['description']),0,40);
            $this->assign('voteInfo',$voteInfo);
            $this->assign('details',$details);
            $this->assign('if_vote',$canVoteAndData['canVote']);
            $this->assign('voteData',$canVoteAndData['data']);
            $this->assign('oss',C('oss_path'));

            if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')))
            {
                //$this->redirect("/",array('m'=>'Home','c'=>'Activity','a'=>'vote_details','id' => $vote_id));
                $this->redirect('Home/Activity/vote_details', array('id' => $vote_id));
            }

            $this->assign('isFollow',1);
            $this->assign('isWei',1);
            $this->assign('openid',$_SESSION['weixin']["openid"]);
            $this->assign('activity_id',$activityId);
            $this->display_nocache('votingDetails');
            exit();
        }else {
            $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
            $signPackage = $jssdk->GetSignPackage();
            $this->assign('signPackage',$signPackage);
            $this->assign('WEB_URL',WEB_URL);

            $userId = getParameter('userId', 'int', false);
            $role = getParameter('role', 'int', false);
            $activityId= getParameter('activity_id','int',false);
            if(empty($userId) || empty($role))
            {
                $controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId,$role);
                if($userId == -1)
                    $userId = 0;
                if($role == -1)
                    $role = 0;
            }
            $this->assign('userId',$userId);
            $this->assign('role',$role);
            $vote_id = getParameter('id','int');
            $details = D('Social_activity_vote')->getCandidateList($vote_id);
            $canVoteAndData = D('Social_activity_vote')->getCanVoteAndVoteData($vote_id,$userId,$role);
            $voteInfo = D('Social_activity_vote')->getVoteData($vote_id);
            //var_dump($details);
            $voteInfo['description_copy'] = mb_substr( strip_tags($voteInfo['description']),0,40);
            $this->assign('voteInfo',$voteInfo);
            $this->assign('details',$details);
            $this->assign('if_vote',$canVoteAndData['canVote']);
            $this->assign('voteData',$canVoteAndData['data']);
            $this->assign('oss',C('oss_path'));
            if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')))
            {
                //$this->redirect("/",array('m'=>'Home','c'=>'Activity','a'=>'vote_details','id' => $vote_id));
                $this->redirect('Home/Activity/vote_details', array('id' => $vote_id));
            }
            $this->assign('activity_id',$activityId);
            $this->assign('isWei',1);
            $this->assign('isFollow',2);
            $this->display_nocache('votingDetails');
            exit();

            /*$fqid = 10086;
            $qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$fqid.'}}}';
            $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$token";
            $result = $this->https_request($url,$qrcode);
            $jsoninfo = json_decode($result,true);
            print_r($jsoninfo);die();
            $ticket = $jsoninfo['ticket'];
            echo $ticket.PHP_EOL;

            $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
            echo $url;


            echo "<img src='./qrcode.jpg'>";*/
        }
    }
    public function weChatConcern() {
        $this->display();
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

    public function winningWork()
    {
        $display = getParameter('display','str',false);
        $userId = getParameter('userId', 'int', false);
        $role = getParameter('role', 'int', false);

        switch($display)
        {
            case 'Des' : $displayBlock = 2;
                          break;
            case 'Msg' : $displayBlock = 3;
                          break;
            default:    $displayBlock = 1;
                          break;
        }
        $this->assign('displayBlock',$displayBlock);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $controller = new \Home\Controller\ActivityController();
        $controller->activityWorkDetails();
    }

    public function voteList()
    {
        $activityId = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');

        $result = D('Social_activity_vote')->is_votes($activityId);
        if($result['result'] == 'yes')
        {
            $this->assign('data',$result['value']);
        }
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->assign('id',$activityId);
        $this->display();
    }
    public function winningList()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $controller = new \Home\Controller\ActivityController();
        $controller->activityWorks();
    }
    public function workUpload($template='')
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $activityId = getParameter('id','int');
        $regInfo = $this->activityModel->getRegisterInfo($activityId,$userId);
        $activityInfo = $this->activityModel->getActivityDetails($activityId);
        $controller = new \Home\Controller\ActivityController();
        $controller->activityWorkInfoAssign($activityId, array('userId'=>$userId,'role'=>$role));
        if(ROLE_TEACHER == $role)
        $this->assign('grades', $grade_result = $this->activityModel->getAvailableRegGrade($userId, $activityId));
        $this->assign('activityId',$activityId);
        $this->assign('regInfo',$regInfo);
        $this->assign('activityInfo',$activityInfo);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->display($template);
    }
    public function workView()
    {
        $this->assign('view',1);
        $this->workUpload('workUpload');
    }
}