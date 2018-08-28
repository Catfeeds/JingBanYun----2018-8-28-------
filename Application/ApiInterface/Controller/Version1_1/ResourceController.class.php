<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;
use Common\Common\PHPZIP;
use Common\Common\REDIS;

class ResourceController extends PublicController
{
    public $pageSize = 15;
    public $Model;
    public function __construct() {
        parent::__construct();
        $this->Model = D('Biz_resource');
        $this->assign('oss_path', C('oss_path'));
    }
    function getPageSize(){
        return $this->pageSize;
    }
    /**
     * @描述：获取我收藏的教师资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：keyword[string] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] N 学期
     * @参数：type[string] N 类型
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyCollected()
    {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);
        $pageIndex =getParameter('pageIndex','int',false);
        $keyword =getParameter('keyword','str',false);
        $textbookId = getParameter('textbook_id','int',false);
        $type = getParameter('type','str',false);
        $userId = getParameter('user_id','int',true);
        $role = getParameter('role','int',true);
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
            'school_term' => $textbookId,
            'type' => $type,
            'keyword'   => $keyword
        );
        $result = $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize(),$userId,$role);
        for($i=0;$i<sizeof($result);$i++)
        {
            $result[$i]['url'] = 'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/Version1_2/Resource/resourceDetails?id='.$result[$i]['id'];
        }
        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }
    /**
     * @描述：获取我发布的资源列表和活动列表
     * @参数：pageIndex[int] N 页码
     * @参数：user_id[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyPublished()
    {

        $pageIndex =getParameter('pageIndex','int',false);
        $userId = getParameter('user_id','int',true);
        $queryParameters = array();

        $userInfo = D('Auth_teacher')->getTeachInfo($userId);
        
        if (preg_match('/Resources/', $userInfo['avatar'])){
            $message = C('oss_path').$userInfo['avatar'];
        } else {
            if ($userInfo['sex'] == '男' || empty($userInfo['sex'])) {
                $message = 'http://'.WEB_URL.'/Public/img/classManage/teacher_m.png';
            } else {
                $message = 'http://'.WEB_URL.'/Public/img/classManage/teacher_w.png';
            }
        }
        $result = $this->Model->getPublishedResource($queryParameters,$pageIndex,$this->getPageSize(),'','',$userId);
        foreach($result as $key=>$val)
        {
            foreach($val as $keySub=>$valSub) {
                if ($valSub['flag'] == 1) //我发布的教师资源
                {
                    $result[$key][$keySub]['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ApiInterface/Version1_2/Resource/resourceDetails?id=' . $valSub['id'];
                } else //作品
                {
                    $result[$key][$keySub]['url'] = 'http://' . $_SERVER['SERVER_NAME'] . '/ApiInterface/Version1_1/Activity/activityWorksDetail?id=' . $valSub['id'];
                }
            }
        }
        $this->ajaxReturn(array('status' => 200,'message' => $message,'result' => $result));
    }
    /**
     * @描述：根据学科年级关键字查询资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] N 学期
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResourcesByCourseAndTextbook()
    {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);
        $pageIndex =getParameter('pageIndex','int',false);
        $textbookId = getParameter('textbook_id','int',false);
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
            'school_term' => $textbookId
        );
        $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize())));
    }

    /**
     * @描述：教师资源详情
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：详情页HTML
     */
    public function resourceDetails($flag=false)
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',false);
        $userId =getParameter('user_id','int',false);   
        $result = $this->Model->getResourceDetails($id);

        if($flag==true){
            $result['content']=$this->Model->getSubResourceHTMLList($result);
        }
 
        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        $contact_result = $this->Model->getContactResourcePath($id);        
        $this->assign('contact_data', $contact_result);             

        //判断登陆者是否和发布者是一人
        if($result['teacher_id']==$userId && ($role == 2)){
            $this->assign('operation_status',1);
        }else{
            $this->assign('operation_status',2);
        }
        $this->assign('existedZan', $this->Model->getIsZan($id, $userId, $role));
        $this->assign('existedFavor', $this->Model->getIsFavor($id, $userId, $role));
        $this->Model->setBrowseCountPlusOne($id);
        
        $this->assign('host','http://'.$_SERVER['HTTP_HOST']);  
        
        $this->display();
    }

    /**
     * @描述：教师资源详情分享页面
     * @参数：id[int] Y 资源ID
     * @返回值：详情页HTML
     */
    public function resourceDetailsShare()
    {   
        $id = $_GET['id'];
        $this->res_id = $id;
        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];
        $this->assign('apkurl',$apkurl);
        $flag=true;
        $url = WEB_URL.$_SERVER['REQUEST_URI'];
        $url = str_replace('resourceDetailsShare','resourceDetails',$url);
        $this->assign('urldata',"http://".$url );
        $this->resourceDetails($flag);
    }
    /**
     * @描述：获取资源是否点赞
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function hasZan()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',true);
        $userId =getParameter('user_id','int',true);
        $result = $this->Model->getIsZan($id,$userId,$role);
        $this->ajaxReturn(array('status' => 200,'message' => $result,'result' => $result));
    }
    /**
     * @描述：获取资源是否收藏
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function hasCollect()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',true);
        $userId =getParameter('user_id','int',true);
        $result = $this->Model->getIsZan($id,$userId,$role);
        $this->ajaxReturn(array('status' => 200,'message' => $result,'result' => $result));
    }

    /**
     * @描述：curl文件上传
     */

    function curl_post( $url,$filepath,$type='',$filename='') {

        $ch = curl_init();

        if (class_exists('\CURLFile')) {
            $data = array('file' => new \CURLFile(realpath($filepath),$type,$filename));
        } else {
            $data = array(
                'file'=>'@'.realpath($filepath).";type=".$type.";filename=".$filename
            );
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);    // 5.6 给改成 true了, 弄回去
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec ( $ch );
        curl_exec($ch);
        return $return;
    }


    /**
     * @描述：打包资源
     */

    public function packResult()
    {
        $ZipArchive = new \ZipArchive();
        $upload = new \Oss\Ossupload();
        $saModel = M('social_activity');
        $crenttime = time();
        $map['social_activity.applyend'] = array('LT',$crenttime);
        $map['social_activity.is_pack'] = '1';
        $result = $saModel
            ->join("social_activity_register on social_activity_register.activity_id=social_activity.id")
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join dict_course on dict_course.id=social_activity_works.course")
            ->join("left join dict_grade on dict_grade.id=social_activity_works.grade")
            ->join("left join dict_schoollist on dict_schoollist.id=social_activity_register.school_id")
            ->join("left join dict_citydistrict on social_activity_register.district=dict_citydistrict.id")

            //->join("left join social_activity_works_file on social_activity_works_file.activity_works_id=social_activity_works.id")
            ->field("social_activity.is_pack,social_activity_register.id as sar_id,social_activity_works.id as saw_id,social_activity_register.user_name as teacher_user_name,dict_course.course_name,dict_grade.grade,social_activity.id,dict_schoollist.school_name,dict_citydistrict.name as cityname,social_activity.title,social_activity_register.invitation_code,social_activity_works.voted_title,social_activity_register.sex,social_activity_register.age,social_activity_register.education,social_activity_register.positions,social_activity_register.email,social_activity_register.school_address,social_activity_register.post_code,social_activity_register.tel,social_activity_register.telephone,social_activity_register.local_course,social_activity_register.school_course,social_activity_works.works_name,social_activity_works.works_description,social_activity_works.author_remarks")
            ->where( $map )
            //->group('social_activity.id')
            ->select();

        $listtitle = array();

        if (!empty($result)) {
            foreach ($result as $rk=>$rv) {
                if (!empty($rv['saw_id'])) {
                    $aw_map['activity_works_id'] = $rv['saw_id'];
                    $aw_list = M('social_activity_works_file')->where( $aw_map )->select();
                    if (!empty($aw_list)) {
                        $result[$rk]['result_list'] =  $aw_list;
                    }
                }

                if (!in_array('./Uploads/'.$rv['title'].'活动教师报名及作品信息'.'::'.$rv['id'],$listtitle)) {
                    $pushfile = './Uploads/'.$rv['title'].'活动教师报名及作品信息'.'::'.$rv['id'];
                    $listtitle[] = $pushfile;
                }
            }
        }

        if (!empty($result)) {
            foreach ($result as $k => $v) {
                $win = getcwd().'/Uploads/';
                $mode=0755;
                $this->createdirlist($win.$v['title'].'活动教师报名及作品信息',$mode);

                $course_name = $v['course_name']; //学科
                $grade = $v['grade'];//年级
                $cityname = $v['cityname'];//所属区
                $school_name = $v['school_name']; //学校
                $teacher_user_name = $v['teacher_user_name']; //老师姓名

                $path = $win.$v['title'].'活动教师报名及作品信息/'.$course_name.'/'.$grade.'/'.$cityname.'/'.$school_name.'/'.$teacher_user_name;
                //创建教师信息


                $teacher_info_file = array(
                    'invitation_code' => $v['invitation_code'],
                    'teacher_user_name' => $v['teacher_user_name'],
                    'course_name' => $v['course_name'],
                    'voted_title' => $v['voted_title'],
                    'cityname' => $v['cityname'],
                    'school_name' => $v['school_name'],
                    'sex' => $v['sex'],
                    'age' => $v['age'],
                    'education' => C('education.'.$v['education']),
                    'positions' => C('positions.'.$v['positions']),
                    'email' => $v['email'],
                    'school_address' => $v['school_address'],
                    'post_code' => $v['post_code'],
                    'tel' => $v['tel'],
                    'telephone' => $v['telephone'],
                    'local_course' => $v['local_course']==1?'是':'否',
                    'school_course' => $v['school_course']==1?'是':'否',
                );


                $works_info_file = array(
                    'works_name' => $v['works_name'],
                    'course_name' => $v['course_name'],
                    'grade' => $v['grade'],
                    'works_description' => $v['works_description'],
                    'author_remarks' => $v['author_remarks'],
                );

                $teacher_info = $win.$v['title'].'活动教师报名及作品信息/'.$course_name.'/'.$grade.'/'.$cityname.'/'.$school_name.'/'.$teacher_user_name.'/'.$teacher_user_name.'.txt';
                //创建作品信息

                $works = $win.$v['title'].'活动教师报名及作品信息/'.$course_name.'/'.$grade.'/'.$cityname.'/'.$school_name.'/'.$teacher_user_name.'/作品信息.txt';

                $iscreate = $this->createdirlist($path,$mode,$teacher_info,$works,$teacher_info_file,$works_info_file);

                if (!empty($v['result_list'])) {
                    foreach ($v['result_list'] as $resv=>$resk) { //循环下载资源
                        if (!empty($resk['works_file_path'])) {
                            $oss_file_path = $resk['works_file_path'];
                            if ($resk['type']=='image') {
                                $resk['type']='png';
                            } elseif($resk['type']=='video') {
                                $resk['type']='mp4';
                            } elseif($resk['type']=='word') {
                                $resk['type']='doc';
                            } elseif($resk['type']=='audio') {
                                $resk['type']='mp3';
                            }

                            $newfile = $path.'/'.$resk['works_file_name'].'.'.$resk['type'];
                            $newfile = iconv("UTF-8", "GBK", $newfile);
                            $status=$upload->downloadFile($oss_file_path,$newfile);
                            sleep(2);
                        }
                    }
                }
                sleep(2);
            }
        }
        //打包数据
        if (!empty($listtitle)) {
            foreach ($listtitle as $lvb=>$lkz) {
                $urlinfo = explode('::',$lkz);
                $aid = $urlinfo['1'];

                $opendir = iconv("UTF-8", "GBK", $urlinfo['0']);
                $resz = $ZipArchive->open($opendir.'.zip', \ZipArchive::CREATE);

                if ($resz === TRUE) {
                    addFileToZip($opendir, $ZipArchive); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                    $ZipArchive->close();
                    $a_d_u['aid'] = $aid;
                    $savezip = iconv("GBK", "UTF-8", $opendir);
                    $a_d_u['result_url'] = $savezip.'.zip';
                    echo "############入库oss#############".PHP_EOL;
                    $id = M('activity_download_url')->add( $a_d_u );
                    if ( $id == false) {
                        return false;
                    }
                } else {
                    echo 'failed';
                }
            }
        }
        $this->upoadossResult();
    }

    public function createdirlist($path,$mode,$teacher_info,$works,$teacher_info_file,$works_info_file){

        if ( !file_exists($path) ) {
            //判断目录存在否，存在不创建
            $re = mkdir(iconv("UTF-8", "GBK", $path), $mode, true);
        }
        if ( !file_exists($teacher_info) ) {
            $teacher_info=iconv('utf-8','gb2312',$teacher_info);
            $userstr = '';
            $userstr .= '邀请码:'.$teacher_info_file['invitation_code'].PHP_EOL;
            $userstr .= '教师姓名:'.$teacher_info_file['teacher_user_name'].PHP_EOL;
            $userstr .= '学科:'.$teacher_info_file['course_name'].PHP_EOL;
            $userstr .= '参评课题:'.$teacher_info_file['voted_title'].PHP_EOL;
            $userstr .= '所属区县:'.$teacher_info_file['cityname'].PHP_EOL;
            $userstr .= '学校:'.$teacher_info_file['school_name'].PHP_EOL;
            $userstr .= '教师性别:'.$teacher_info_file['sex'].PHP_EOL;
            $userstr .= '年龄:'.$teacher_info_file['age'].PHP_EOL;
            $userstr .= '学历:'.$teacher_info_file['education'].PHP_EOL;
            $userstr .= '职称:'.$teacher_info_file['positions'].PHP_EOL;
            $userstr .= '电子邮箱:'.$teacher_info_file['email'].PHP_EOL;
            $userstr .= '学校地址:'.$teacher_info_file['school_address'].PHP_EOL;
            $userstr .= '学校邮编:'.$teacher_info_file['post_code'].PHP_EOL;
            $userstr .= '办公电话:'.$teacher_info_file['tel'].PHP_EOL;
            $userstr .= '移动电话:'.$teacher_info_file['telephone'].PHP_EOL;
            $userstr .= '地方课程:'.$teacher_info_file['local_course'].PHP_EOL;
            $userstr .= '校本课程:'.$teacher_info_file['school_course'].PHP_EOL;

            $filere = file_put_contents($teacher_info,$userstr,FILE_APPEND);
        }

        if ( !file_exists($works) ) {
            $works=iconv('utf-8','gb2312',$works);
            $filestr = '';
            $filestr .= '标题:'.$works_info_file['works_name'].PHP_EOL;
            $filestr .= '学科:'.$works_info_file['course_name'].PHP_EOL;
            $filestr .= '年级:'.$works_info_file['grade'].PHP_EOL;
            $filestr .= '作品描述:'.$works_info_file['works_description'].PHP_EOL;
            $filestr .= '作者寄语:'.$works_info_file['author_remarks'].PHP_EOL;
            $fw = file_put_contents($works,$filestr,FILE_APPEND);
        }


    }



    //开始上传到oss
    /*$is_oos = $this->upoadossResult();
    if ($is_oos) { //如果oss上传成功就删除所有的本地文件
    $this->delossResult();
    }*/

//执行oss上传文件
    public function upoadossResult (){

        echo "############执行oss上传###############".PHP_EOL;
        $win = getcwd().'/';
        $uplod_data = M('activity_download_url')->where('status=1')->select();

        if (!empty($uplod_data)) {
            foreach ( $uplod_data as $uk=>$uv) {
                $uploadurl_cr = $uv['result_url'];
                $filepath = iconv("UTF-8", "GBK", $win.$uploadurl_cr);
                //echo 'http://'.WEB_URL.'/index.php?m=Home&c=App&a=upload_file';
                $filepath =str_replace('server/./','server/',$filepath);//新添加

                $curl_data = $this->curl_post('http://'.WEB_URL.'/index.php?m=Home&c=App&a=upload_file',$filepath,$type='image/jpeg',$uploadurl_cr);

                $curl_data = json_decode($curl_data,true);
                if (!empty($curl_data['1'])) {
                    $ossmap['oss_url'] = $curl_data['1'];
                    $ossmap['status'] = 2;
                    $osswhere['id'] = $uv['id'];
                    $uplod_data = M('activity_download_url')->where( $osswhere )->save( $ossmap );
                    if ($uplod_data) {
                        $samap['is_pack'] = $curl_data['1'];
                        M('social_activity')->where("id=".$uv['aid'])->save( $samap );
                    }

                }

                sleep(2);
            }
        }

        $this->delossResult();
    }

    //删除已经上传的oss的本地资源
    public function delossResult() {
        echo "############删除oss上传###############".PHP_EOL;
        $result = M('activity_download_url')->where('status=2')->select();

        foreach ($result as $k=>$v) {
            $urlpathinfo =  $v['result_url'];
            $pathurl = $this->path_info($urlpathinfo);
            //要删除的目录和文件
            $urldir = $pathurl['filename'];
            $urldirinfo = iconv("UTF-8", "GBK", $urldir);

            $deldir = './Uploads/'.$urldirinfo;
            if ($deldir=='./' || $deldir=='../' || $deldir=='/') {
                continue;
            } else {
                delDirRun($deldir);
            }
            //要删除的文件
            $delzip = iconv("UTF-8", "GBK", $v['result_url']);

            unlink($delzip);
            sleep(2);
        }
    }


    public function path_info($filepath)
    {
        $path_parts = array();
        $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";
        $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");
        $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
        $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");
        return $path_parts;
    }

}


