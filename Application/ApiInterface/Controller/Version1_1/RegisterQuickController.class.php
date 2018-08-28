<?php
namespace ApiInterface\Controller\Version1_1;
use Common\Common\SMS;
use Common\Common\REDIS;
use Think\Verify;
use Think\Controller;

define('TEACHER_IMAGE_PRODUCE_ID','51');
define('STUDENT_IMAGE_PRODUCE_ID','52');
define('PARENT_IMAGE_PRODUCE_ID','53');
define('REGISTER_IMAGE_PRODUCE_ID','54');
define('LOGIN_IMAGE_PRODUCE_ID','55');



class RegisterQuickController extends PublicController{
    
    public function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
    }
    
    /*
     * 快速注册
     */
    public function registerQuick(){
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        } 
        if($_POST){
            $role=getParameter('role','int');
            $data['name']=getParameter('name','str',false);
            $data['telephone']=getParameter('telephone','str');
            $data['verifyCode']=getParameter('verify_code','str');
            $data['vaild_code']=getParameter('valid_code','str');
            $data['password']=getParameter('password','str');
//            $data['confirm_password']=getParameter('confirm_password','str');
            
            if(!$this->check_verify_code(false,$data['verifyCode'],REGISTER_IMAGE_PRODUCE_ID)){
                $this->showjson(500,'请填写正确的图形验证码');
            }
//            if ($data['confirm_password'] != $data['password']) {
//                $this->showjson(500,'密码与确认密码不一致',array());
//            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$data['telephone'])){
                $this->showjson(500,'手机号格式有误');
            }       
            $redis_obj=new REDIS();
            $redis=$redis_obj->init_redis();     
            $redis_key='sms_'.$data['telephone'];   
            $code['code']=$redis->get($redis_key);        
            
                    
            if (!$data['vaild_code'] || $code['code'] != $data['vaild_code']) { 
                $this->showjson(500,'验证码错误');
            }
            //$redis->delete($redis_key); 
            $redis->close();            
                    
            if($role==ROLE_TEACHER){
                $this->teacherRegister($data);
            }elseif($role==ROLE_STUDENT){
                $this->studentRegister($data);
            }else{
                $this->parentRegister($data);
            }  
        }else{
            $this->assign('share_str', $share_string); 
            $this->display('registerQuick');
        }
    }

    //京版活动带来的注册用户
    public function addActivityUser( $data ) {
        $data['type'] = 1;
        $data['user_count'] = 1;
        M('activity_register_user')->add( $data );

    }
    
    //教师注册操作 完善资料给权限
    private function teacherRegister($data){
        $model=D('Auth_teacher'); 
        $result = $model->getTeacherByTel($data['telephone']); 
        if(!empty($result)){
            $this->showjson(500,'用户已存在');
        }
        $add_data=array();
        $add_data['name']=$data['name'];
        $add_data['telephone']=$data['telephone'];
        $add_data['password']=sha1($data['password']);
        if(!($insert_id=$model->addTeacherData($add_data))){
            $this->showjson(500,'用户注册失败');
        }

        $ref = session('ref');
        if ($ref == 1) {
            $refmap['a_id'] = session('rid');
            $this->addActivityUser( $refmap );
        }
        //赠送30天vip
        $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');

        if($vip_config && $vip_config<=3) {
            $result = give_new_vip_operation(ROLE_TEACHER, $vip_config, $insert_id,0);
            if ($result['status'] != 'success') {
                $this->showjson(500,'用户注册失败');
            }
        }

        $smsapi = new SMS();
        $smsapi->newUserNotice($data['telephone']);
        $Message = new \Home\Controller\MessageController();
        $parameters = array('msg' => array(), 'url' => array('type' => 0));
        $Message->addPushUserMessage('REG_SUCCESS', 2, $insert_id, $parameters);
        $this->teacherLogin($data['telephone'], $data['password']);
    }
    
    //学生注册操作
    private function studentRegister($data){
        $model = D('Auth_student');
        $studentModel=M('auth_student');
        $result=$model->getStudentInfoByTelAndName($data['telephone'],$data['name']); 
        if(!empty($result)){
            $this->showjson(500,'用户已存在');
        }
        $add_data['student_name']=$data['name'];
        $add_data['parent_tel']=$data['telephone'];
        $add_data['password']=sha1($data['password']);
        if(!($insert_id=$model->studentAdd($add_data))){
            $this->showjson(500,'用户注册失败');
        }
        $parentResult = M('auth_parent')->where("telephone='{$data['telephone']}'")->find();
        if(!empty($parentResult)){
            $_data['parent_id']=$parentResult['id'];
            $_data['parent_name']=$parentResult['parent_name'];
            if($studentModel->where("id=".$insert_id)->save($_data)==false){ 
                $this->showjson(500,'用户注册失败');
            }
 
           D('Auth_student')->updateStudentParentInfo($parentResult['id'], $insert_id);
        }

        $ref = session('ref');
        if ($ref == 1) {
            $refmap['a_id'] = session('rid');
            $this->addActivityUser( $refmap );
        }
        //赠送30天vip
        $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');

        if($vip_config && $vip_config<=3) {
            $result = give_new_vip_operation(ROLE_STUDENT, $vip_config, $insert_id,0);
            if ($result['status'] != 'success') {
                $this->showjson(500,'用户注册失败');
            }
        }

        $smsapi = new SMS();
        $smsapi->noticeParentAddStudent($data['telephone'],$data['name']);
        $Message = new \Home\Controller\MessageController();
        $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
        $Message->addPushUserMessage('REG_SUCCESS',3,$id,$parameters); 
        $this->studentLogin($data['telephone'],$data['name'],$data['password']);
    }
    
    //家长注册操作
    private function parentRegister($data){
        $model = D('Auth_parent'); 
        $result=$model->getParentInfoByTelephone($data['telephone']);
        if(!empty($result)){
            $this->showjson(500,'用户已存在');
        }
        $add_data['parent_name']=$data['name'];
        $add_data['telephone']=$data['telephone'];
        $add_data['password']=sha1($data['password']);
        if(!($insert_id=$model->add($add_data))){
            $this->showjson(500,'用户注册失败');
        }
        //让家长和学生进行绑定
        $student_model=M('auth_student'); 
        $student_where['parent_tel']=$data['telephone'];
        $student_data['parent_id']=$result['id'];
        $student_info=$student_model->where($student_where)->save($student_data);
        //关联表绑定
        $studentsArray =  $student_model->where($student_where)->field('id')->select();
        for($i=0;$i<sizeof($studentsArray);$i++)
        {
            $student_info_id = $studentsArray[$i]['id'];
            D('Auth_student')->updateStudentParentInfo($insert_id,$student_info_id );
        }

        $ref = session('ref');
        if ($ref == 1) {
            $refmap['a_id'] = session('rid');
            $this->addActivityUser( $refmap );
        }

        //赠送30天vip
        $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');

        if($vip_config && $vip_config<=3) {
            $result = give_new_vip_operation(ROLE_PARENT, $vip_config, $insert_id,0);
            if ($result['status'] != 'success') {
                $this->showjson(500,'用户注册失败');
            }
        }

        $smsapi = new SMS();
        $smsapi->newUserNotice($data['telephone']);
        $Message = new \Home\Controller\MessageController();
        $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
        $Message->addPushUserMessage('REG_SUCCESS',4,$insert_id,$parameters); 
        //登录
        $this->parentLogin($data['telephone'],$data['password']);
    }
    
    
    //注册生成验证码
    public function registerProduceCode(){ 
        $this->produceVerifyCode(REGISTER_IMAGE_PRODUCE_ID);
    }
    
    //发送注册短信
    public function resiterSendSms(){ 
        $this->sendRegisterPhoneCode(REGISTER_IMAGE_PRODUCE_ID);
    }
	
    /*
     * 快速登陆
     */
    public function loginQuick(){
        $refrere = strpos($_SERVER['HTTP_REFERER'],'votingDetails');

        $share_string='';
        $share_url=getParameter('url','str',false);
        $deurl = base64_decode($share_url);

        if ($refrere!=false){
            session('ref',1);
        }

        //获取资源的id
        $parr = '/activity_id=(.*)/';
        
        preg_match($parr, $deurl, $matchs);

        if (!empty($matchs[1])){
            session('rid',$matchs[1]);
        }

        
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        }

        $parent = session('parent');
        $teacher = session('teacher');
        $student = session('student');

        if( !empty($parent) || !empty($teacher) || !empty($student) ) {
            $share_par=base64_decode($share_url);
            header("Location:" . 'http://'.WEB_URL.$share_par);
        }


        $this->assign('share_str',$share_string);
        //$share_string='';
        $this->display_nocache('loginQuick');
    }

    public function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
    
    //教师登录
    public function teacherLogin($telephone='',$password=''){
        ini_set('session.gc_maxlifetime',43200);
        if($_POST && $telephone==''){     
            $check['telephone'] = getParameter('telephone','str',false);
            $check['password'] = sha1(getParameter('password','str'),false);
        }else{          
            $check['telephone']=$telephone;
            $check['password']=sha1($password);
        }
        $model = M('auth_teacher');
        $result = $model->where($check)->find();        
        if(empty($result)){
            $this->showjson(500,'账号或密码错误');
        }
        $firstLogin = $result['is_first'];
        if(!empty($result)){
            session('auth_parent', null);
            session('auth_student', null);
            session('student', null);
            session('parent', null);
            
            session_start();
            session('teacher', $result); 
            
            $userip = getIPaddress();
            if (!empty($userip)){ 
                if ($userip != '127.0.0.1') {
                    $shengfen = getIPLoc_sina( $userip );
                } else {
                    $shengfen = '内网';
                }
                if (empty($shengfen)){
                    $shengfen = '内网';
                }
                
                if($result['login_address']==''){
                    $teacherLogin_address['login_address'] = $shengfen;
                    $model->where("id=".$result['id'])->save( $teacherLogin_address );  
                }

                if ($shengfen != $result['login_address']){ //发送
                    $teacherLogin_address['login_address'] = $shengfen;
                    $rsave_id= $model->where("id=".$result['id'])->save( $teacherLogin_address );  
                    if ( $rsave_id != false ){ //发送消息，异地登陆 
                        $parameters = array( 'msg' => array(date("Y-m-d H:i:s",time()),$shengfen."($userip)",'pc') , 'url' => array( 'type' => 0));
                          
                        $Message = new \Home\Controller\MessageController();   
                        $Message->addPushUserMessage('EXCEPTION_LOGIN',2,$result['id'],$parameters);
                    }
                } 
                
                
                
            }
            if ($firstLogin == 1) {
                $mapfirst['id'] = $result['id'];
                $firstdata['is_first'] = 2;
                $model->where( $mapfirst )->save( $firstdata );  
            }
            $share_par=getParameter('url','str',false);   
            $share_par=base64_decode($share_par);
            $lifeTime = 24 * 3600 * 30;
            setcookie(session_name(), session_id(), time() + $lifeTime, "/");
            $this->showjson(200,'',array('url'=>$share_par));
            
        }else{
            $this->showjson(500,'账号或密码填写错误');
        }
    }
    
    //学生登录
    public function studentLogin($telephone='',$name='',$password=''){
        ini_set('session.gc_maxlifetime',43200);
        $Model = M('auth_student');
        if($_POST && $telephone==''){
            $check['parent_tel'] = getParameter('telephone','str');
            $check['student_name'] = getParameter('name','str');
            $check['password'] = sha1(getParameter('password','str'));
        }else{         
            $check['parent_tel'] = $telephone;
            $check['student_name'] = $name;
            $check['password'] = sha1($password);
        }       
        $result = $Model->where($check)->find();        
        if(empty($result)){
            $this->showjson(500,'账号或密码错误');
        }
        session('auth_teacher', null); 
        session('auth_parent', null);    
        session('teacher', null);
        session('parent', null); 
        session('student', $result);

        $student_id=session('student.id');
        $client_ip=get_client_ip();
        $current_login_address=getIPLoc_sina($client_ip);
        
        if($result['login_address']==''){
            $teacherLogin_address['login_address'] = $current_login_address;
           $Model->where("id=".$result['id'])->save( $teacherLogin_address );  
        }
        
        if($current_login_address!=$result['login_address'] ) {
            $Model->where('id=' . $result['id'])->save(array('login_address' => $current_login_address));

            $ip_arr = explode('.', $client_ip);
            $ip_arr[3] = '*';
            $ip_string = implode('.', $ip_arr);
            $login_address_str = $current_login_address . '(' . $ip_string . ')';

            $parameter_arr = array(
                'msg' => array(date('Y-m-d H:i:s'), $login_address_str, '手机'),
                'url' => array(
                    'type' => 0,
                    'data' => array()
                )
            );   
            
            $controller_obj=new \Home\Controller\MessageController(); 
            $parentInfo = D('Auth_student')->getStudentParentInfo($student_id); 
            
            $controller_obj->addPushUserMessage('EXCEPTION_LOGIN', 3, $student_id, $parameter_arr); 
            $controller_obj->addPushUserMessage('CHILD_EXCEPTION_LOGIN', 4, $parentInfo['id'], $parameter_arr);
            
        }
        if ($result['is_first'] == 1) {
            $mapfirst['id'] = $result['id'];
            $firstdata['is_first'] = 2;
            M('auth_student')->where($mapfirst)->save($firstdata); 
        }
        $share_par=getParameter('url','str',false); 
        $share_par=base64_decode($share_par);
        $lifeTime = 24 * 3600 * 30;
        setcookie(session_name(), session_id(), time() + $lifeTime, "/");
        $this->showjson(200,'',array('url'=>$share_par));
    }
    
    //家长登录
    public function parentLogin($telephone='',$password=''){
        ini_set('session.gc_maxlifetime',43200);
        $Model = M('auth_parent');
        if($_POST && $telephone==''){    
            $check['telephone'] = getParameter('telephone','str'); 
            $check['password'] = sha1(getParameter('password','str'));
        }else{
            $check['telephone'] = $telephone;   
            $check['password'] = sha1($password);
        }
        $Model = M('auth_parent');     
        $result = $Model->where($check)->find();   
        if(empty($result)){
            $this->showjson(500,'账号或密码错误');
        }
        session_start();
                
        session('auth_teacher', null);  
        session('auth_student', null);  
        session('student', null);
        session('teacher', null); 
        session('parent', $result);
        
        $parent_id=$result['id'];
        $client_ip=get_client_ip();
        $current_login_address=getIPLoc_sina($client_ip);
        if($result['login_address']==''){
            $Model->where('id='.$result['id'])->save(array('login_address'=>$current_login_address)); 
        }
        if($current_login_address!=$result['login_address']){ 
            $Model->where('id='.$result['id'])->save(array('login_address'=>$current_login_address));

            $ip_arr=explode('.',$client_ip);
            $ip_arr[3]='*';
            $ip_string=implode('.',$ip_arr);
            $login_address_str=$current_login_address.'('.$ip_string.')';

            $parameter_arr=array(
                'msg'=>array(date('Y-m-d H:i:s'),$login_address_str,'手机'),
                'url'=>array(
                    'type'=>0,
                    'data'=>array()
                )
            );
            $Message = new \Home\Controller\MessageController();   
            $Message->addPushUserMessage('EXCEPTION_LOGIN',4,$parent_id,$parameter_arr); 
        }  
        if ($result['is_first'] == 1) {
            $mapfirst['id'] = $result['id'];
            $firstdata['is_first'] = 2;
            M('auth_parent')->where($mapfirst)->save($firstdata); 
        }
        $share_par=getParameter('url','str',false);  
        $share_par=base64_decode($share_par);
        $lifeTime = 24 * 3600 * 30;
        setcookie(session_name(), session_id(), time() + $lifeTime, "/");
        $this->showjson(200,'',array('url'=>$share_par));
    }
    
    
    //登录生成验证码
    public function loginProduceCode(){
        $this->produceVerifyCode(LOGIN_IMAGE_PRODUCE_ID);
    }
	
    /*
     * 教师重置密码
     */
    public function teacherResetPwd(){
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        }
        if($_POST){ 
            $model=D('Auth_teacher'); 
            $data['telephone']=getParameter('telephone', 'str');
            $data['verify_code']= getParameter('verify_code','str');
            $data['valid_code'] = getParameter('valid_code', 'str');
            $data['password'] = sha1(getParameter('password', 'str'));
            $data['confirm_password'] = sha1(getParameter('confirm_password', 'str'));
            if(!$this->check_verify_code(false,$data['verify_code'],TEACHER_IMAGE_PRODUCE_ID)){
                $this->showjson(500,'请填写正确的图形验证码');
            }
            if ($data['confirm_password'] != $data['password']) {
                $this->showjson(500,'两次密码输入不一致');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$data['telephone'])){
                $this->showjson(500,'手机号格式有误');
            } 
            $result=$model->getTeacherByTel($data['telephone']);
            if(empty($result)){
                $this->showjson(500,'该用户不存在');
            }
            
            $id=$result['id'];
            $where['id']=$id;
            $update_data=$data;
            $data=array();
            $data['password']=$update_data['password'];
            if($model->where($where)->save($data)===false){
                $this->showjson(500,'重置密码失败');
            }else{
                $url=getParameter('url', 'str',false); 
                $url=base64_decode($url);
                $this->showjson(200,'',array('url'=>$url));
            }
        }else{
            $this->assign('share_str', $share_string); 
            $this->display('teacherPwd');
        } 
    }
	
    /*
     * 家长重置密码
     */
    public function parentResetPwd()
    {
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        } 
        if($_POST){ 
            $model=D('Auth_parent'); 
            $data['telephone']=getParameter('telephone', 'str');
            $data['verify_code']= getParameter('verify_code','str');
            $data['valid_code'] = getParameter('valid_code', 'str');
            $data['password'] = sha1(getParameter('password', 'str'));
            $data['confirm_password'] = sha1(getParameter('confirm_password', 'str'));
            if(!$this->check_verify_code(false,$data['verify_code'],PARENT_IMAGE_PRODUCE_ID)){
                $this->showjson(500,'请填写正确的图形验证码');
            }
            if ($data['confirm_password'] != $data['password']) {
                $this->showjson(500,'两次密码输入不一致');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$data['telephone'])){
                $this->showjson(500,'手机号格式有误');
            } 
            $result=$model->getParentInfoByTelephone($data['telephone']);
            if(empty($result)){
                $this->showjson(500,'该用户不存在');
            }
             
            $where['id']=$result['id'];
            $update_data=$data;
            $data=array();
            $data['password']=$update_data['password'];
            if($model->where($where)->save($data)===false){
                $this->showjson(500,'重置密码失败');
            }else{
                $url=getParameter('url', 'str',false); 
                $url=base64_decode($url);
                $this->showjson(200,'',array('url'=>$url));
            }
        }else{
            $this->assign('share_str', $share_string); 
            $this->display('parentPwd');
        } 
    }
	
    /*
     * 学生重置密码
     */
    public function studentResetPwd(){
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        }
        if($_POST){  
            $model=D('Auth_student'); 
            $data['name']=getParameter('name', 'str');
            $data['parent_tel']=getParameter('parent_tel', 'str');
            $data['verify_code']= getParameter('verify_code','str');
            $data['valid_code'] = getParameter('valid_code', 'str');
            $data['password'] = sha1(getParameter('password', 'str'));
            $data['confirm_password'] = sha1(getParameter('confirm_password', 'str'));
            if(!$this->check_verify_code(false,$data['verify_code'],STUDENT_IMAGE_PRODUCE_ID)){
                $this->showjson(500,'请填写正确的图形验证码');
            }
            if ($data['confirm_password'] != $data['password']) {
                $this->showjson(500,'两次密码输入不一致');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$data['parent_tel'])){
                $this->showjson(500,'手机号格式有误');
            } 
            $result=$model->getStudentInfoByTelAndName($data['parent_tel'],$data['name']);
            if(empty($result)){
                $this->showjson(500,'该用户不存在');
            }
             
            $where['id']=$result['id'];
            $update_data=$data;
            $data=array();
            $data['password']=$update_data['password'];
            if($model->where($where)->save($data)===false){
                $this->showjson(500,'重置密码失败');
            }else{
                $url=getParameter('url', 'str',false); 
                $url=base64_decode($url);
                $this->showjson(200,'',array('url'=>$url));
            }
        }else{
            $this->assign('share_str', $share_string); 
            $this->display('studentPwd');
        } 
    }
    
    //教师生成验证码
    public function teacherProduceCode(){
        $this->produceVerifyCode(TEACHER_IMAGE_PRODUCE_ID);
    }
    
    //学生生成验证码
    public function studentProduceCode(){
        $this->produceVerifyCode(STUDENT_IMAGE_PRODUCE_ID);
    }
    
    //家长生成验证码
    public function parentProduceCode(){
        $this->produceVerifyCode(PARENT_IMAGE_PRODUCE_ID);
    }
    
    //生成验证码
    function produceVerifyCode($image_produce_id){ 
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'useCurve'  =>  false,            // 是否画混淆曲线
             'useNoise'  =>  false,            // 是否添加杂点
             'imageH' => 40);
         $verify = new Verify($config); 
         $verify->entry($image_produce_id);
    }
    
    
    //教师找回密码发送验证码
    public function teacherSendSms(){
        $this->sendForgetPasswordPhoneCode(TEACHER_IMAGE_PRODUCE_ID);
    }
    
    //学生找回密码发送验证码
    public function studentSendSms(){
        $this->sendForgetPasswordPhoneCode(STUDENT_IMAGE_PRODUCE_ID);
    }
    
    //家长找回密码发送验证码
    public function parentSendSms(){
        $this->sendForgetPasswordPhoneCode(PARENT_IMAGE_PRODUCE_ID);
    }
    
    /*
     * 发送找回验证码
     */
    private function sendForgetPasswordPhoneCode($image_produce_id)
    { 
        //判断图形验证
        $verify_code=getParameter('code','str',false);
        if(!$this->check_verify_code(false,$verify_code,$image_produce_id)){
            $this->showjson(-5,'图形验证码错误');
        }
         
        $telephone=getParameter('telephone', 'str',false);
        if (empty($telephone)) {
            $this->showjson(-4, '手机号码格式错误');
        }
 
        
        if($image_produce_id==TEACHER_IMAGE_PRODUCE_ID){
            $model = D('Auth_teacher'); 
            $result = $model->getTeacherByTel($telephone);     
            
        }elseif($image_produce_id==PARENT_IMAGE_PRODUCE_ID){ 
            $model = D('Auth_parent'); 
            $result=$model->getParentInfoByTelephone($telephone); 
            
        }else{
            $model = D('Auth_student');
            $student_name=getParameter('student_name','str',false);
            $result=$model->getStudentInfoByTelAndName($telephone,$student_name);
            
        } 
        if (empty($result)) {
            $this->showjson(-1, '该用户不存在');
        }

        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->newTemplateSMS($telephone, '找回密码,' . $randcode, 'json');
        if ($ret['status'] == false || $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            return;
        } 
        
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();     
        $redis_key='sms_'.$telephone;   
        $redis->setex($redis_key,300,$randcode); 
        $redis->close(); 
        $this->showjson(0, 'success');
    }
    
    
    //发送注册验证码
    public function sendRegisterPhoneCode($image_produce_id)
    { 
        //判断图形验证
        $verify_code=getParameter('code','str',false); 
        $name=getParameter('name','str',false);
        $role=getParameter('role','str',false);
        
        if(!$this->check_verify_code(false,$verify_code,$image_produce_id)){
            $this->showjson(-5,'图形验证码错误');
        }
         
        $telephone=getParameter('telephone', 'str',false);
        if (empty($telephone)) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['telephone'] = $telephone;

        if($role==ROLE_TEACHER){
            $model = D('Auth_teacher'); 
            $result = $model->getTeacherByTel($telephone);
            $sms_msg='注册教师';
            
        }elseif($role==ROLE_PARENT){
            $model = D('Auth_parent'); 
            $result=$model->getParentInfoByTelephone($telephone); 
            $sms_msg='注册家长';
            
        }elseif($role==ROLE_STUDENT){
            $model = D('Auth_student'); 
            $result=$model->getStudentInfoByTelAndName($telephone,$name);
            $sms_msg='注册学生';
            
        }else
        {
            $this->showjson(-3, '请选择注册角色');
        }
        if (!empty($result)) {
            $this->showjson(-1, '用户已存在');
        }

        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->newTemplateSMS($telephone, $sms_msg .','. $randcode, 'json');
        if ($ret['status'] == false || $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            return;
        }
        
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();     
        $redis_key='sms_'.$telephone;   
        $redis->setex($redis_key,300,$randcode); 
        $redis->close();
 
        $this->showjson(0, 'success');
    }
    
    
    //检查验证码
    public function check_verify_code($show_flag=1,$code='',$image_produce_id){  
        $config = array(
            'reset' => false
        );
         $verify = new \Think\Verify($config);
         if($show_flag==1){
             $verify_code=getParameter('code','str');
             $res = $verify->check($verify_code,$image_produce_id);   
             if($res){  
                 $this->showjson(200);
             }else{
                 $this->showjson(401);
             }
         }else{
             $verify_code=$code;
             $res = $verify->check($verify_code,$image_produce_id);    
             return $res;
         }  
    }
    
}