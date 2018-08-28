<?php
namespace Home\Controller;

use Common\Common\SMS;
use Common\Common\DEVICE;

class IndexController extends PublicController
{
    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
		$this->assign('oss_path',C('oss_path'));
    }
    public function index()
    {
        if(WEB_URL!="www.jingbanyun.com")
        {
            if(strpos(gethostname(),'baidu.com')!==false) exit;
            if(strpos($_SERVER['HTTP_USER_AGENT'],'baidu.com')!==false) exit;
        }
        $adlist = D('Ad')->getIdLunboPc();

        $this->assign('adlist', $adlist);

        $logout = $_GET['logout'];
        switch ($logout) {
            case 't':
                $this->assign('logout', 'teacher');
                if(WEB_URL === "www.jingbanyun.com")
                A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_TEACHER,session('teacher.id'));
                session('teacher', null);
                break;
            case 's':
                $this->assign('logout', 'student');
                if(WEB_URL === "www.jingbanyun.com")
                A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_STUDENT,session('student.id'));
                session('student', null);
                break;
            case 'p':
                $this->assign('logout', 'parent');
                if(WEB_URL === "www.jingbanyun.com")
                A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_PARENT,session('parent.id'));
                session('parent', null);
                break;
            case 'a':
                $this->assign('logout', 'admin');
                session('admin', null);
                break;
            default:
                break;
        }

        if(!$_GET['jtypt'] && (strpos($_SERVER['SERVER_NAME'],'jtypt.com')!==false) && ($_SERVER['SERVER_NAME']!='www.jingbanyun.com'))
            redirect('http://www.jingbanyun.com');
        
        if(!$_GET['jtypt'] && (strpos($_SERVER['SERVER_NAME'],'test.jingbanyun.com')!==false) && ($_SERVER['SERVER_NAME']!='www.jingbanyun.com'))
            redirect('http://www.jingbanyun.com');

        A('Home/Common')->getUserIdRole($userId,$role);
        if($role>-1) {
            $share_par = $_REQUEST['url'];
            if (!empty($share_par)) {
                $share_par = base64_decode($share_par);
                $tokenValue = base64_encode(session_id().','.$role.','.$userId);
                $tokenString = "token=".$tokenValue;
                $key = A('Home/Auth')->getAuthKey($tokenValue);
                if(strpos($share_par,'?') !== false)
                    $share_par .= "&".$tokenString.'&key='.$key;
                else
                    $share_par .= "?".$tokenString.'&key='.$key;
                header('Location:' . $share_par);
            }else {
                switch ($role) {
                    case ROLE_TEACHER:
                        redirect(U('Teach/index1'));
                        break;
                    case ROLE_STUDENT:
                        redirect(U('Student/index1'));
                        break;
                    case ROLE_PARENT:
                        redirect(U('Parent/index1'));
                        break;
                }
            }
        }
        $ip = get_client_ip();
        $ip_table = M("jby_iplist");
        $add['ip'] = $ip;
        $add['create_at'] = time();
        $ip_table->add($add);
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
        }
        
        $role = $_GET['role'];
        $err = $_GET['err'];
        $logout = $_GET['logout'];
        $this->assign('role', $role);
        $this->assign('err', $err);
        $this->assign('logout', $logout);
        $this->assign('hot_tel', WEB_HOT_TEL); 
        $this->assign('share_str', $share_string);


        $this->assign('db_host', CFG_DB_HOST);
        $keywords = $description = "";
        $webtitle = "构筑教学新生态";
        $webname = "";
        if(WEB_URL=="www.jingbanyun.com")
        {
            $keywords = "京版云, 京版云平台, 京版云教育平台, 京版教材, 数字课堂教师端, 数字课堂学生端, 北京出版社, 在线教育, 微课, 作业系统, 电子书, 班级信息管理,京版概览,专家咨询,教师资源分享,小黑板,北京出版集团,育学林";
            $description = "京版云教育平台，主要细分为“励耘圈”（资讯平台）、“教学+”（资源平台）、和“班级行”（授课平台）三大功能模块，并可通过云平台实现最前沿的VR和AR体验。平台通过“网页端”、“手机APP端”、“微信端”、“数字课堂平板端”以及“PC端软件”为出版社、学校、教师、学生和家长提供综合教育云服务。";
            $webtitle = "京版云::构筑教学新生态";
            $webname = '<span class="navbar-brand" id="navbar-brand">京版云</span>';
        }
        $this->assign('webtitle', $webtitle);
        $this->assign('keywords', $keywords);
        $this->assign('description', $description);

        $this->display('index');
    }
    public function adImgCount() {
        $id = $_GET['id'];
        if(empty($id))
            exit;
        $data['aid'] = $id;
        $data['type'] = 1;
        $data['dateorder'] = date('Ymd');
        $row = M('pc_count')->where($data)->find();
        if (empty($row)) {
            $data['c_num'] = 1;
            $ip = get_client_ip();
            $data['create_at'] = time();
            $data['client_ip'] = $ip;
            M('pc_count')->add($data);
        } else {
            M('pc_count')->where($data)->setInc('c_num',1);
        }
    }



    public function help()
    {
        $this->display();
    }

    public function about()
    {
        $this->display();
    }

    public function registerSuccess()
    {
        $this->display();
    }

    public function findPasswordSuccess()
    {
        $this->display();
    }


    public function jbresource()
    {
        $id = $_GET['id'];
        $sign = I('sign');
        $userag = $_SERVER['HTTP_USER_AGENT'];

        $show=false;

        if(strpos(strtolower($userag),'ios')) // ios
        {
            if ( $sign == 'jingbanyun' || strpos(strtolower($userag),'jingbanyun') !== false) {
                $show=true;
            }
        }else{
            if( (strpos($userag,'MicroMessenger')!==false) || (strpos($userag,'AlipayClient')!==false) || (strpos($userag,'QQ')!==false) || (strpos($userag,'Windows')!==false))
            {
                $show=false;
            }else {
                $show=true;
            }
        }
        if(in_array($id,array(1040,1041,1042,1043,1044,1045,1046,1047,1048,1049,1050,1051,1052,1053,1054,1055,1056,1057,1058,1059,1060,1061,1062,1063,1064,1065,1066,1067,1068,1069,1070,1071,1072,1073,1074,1075,1076,1077,1078,1079,1080,1081,1082,1083,1084,1085,1086,1087,1088,1089,1090,1091,1092,1093,1094,1095,1096,1097,1098,1099,1100,1101,1102,1103,1104,1105,1106,1107,1108,1109,1110,1111,1112,1113,1114,1115,1117,1118,1119,1120,1121,1122,1123,1124,1125,1126,1127,1128,1129,1130,1131,1132,1133,1134,1135,1136,1137,1138,1139,1140,1141,1142,1143,1144,1145,1146,1147,1148,1149,1150,1151,1152,1153,1154,1155,1156,1157,1158,1159,1160,1161,1162,1163,1164,1165,1166,1167,1168,1169,1170,1171,1172,1173,1174,1175,1176,1177,1178,1179,1180,1181,1182,1183,1184,1185,1186,1187,1188,1189,1190,1191,1192,1193,1194,1195,1196,1197,1198,1199,1200,1201,1202,1203,1204,1205,1206,1207,1208,1209,1210,1211,1212,1213,1214,1215,1216,1217,1218,1219,1220,1221,1222,1223,1224,1225,1226,1227,1228,1229,1230,1231,1232,1233,1234,1235,2793,2801,2802,2803,2804,2805)))
        {
            $show = true;
        }
        if($_GET['notAPP'] == 1)
        {
            $show = true;
        }

        $url = str_replace('jbresource','getjbresourcedata',WEB_URL.$_SERVER['REQUEST_URI']);

        $this->assign('urldata','http://'.$url );

        if ($show) {
            define('PUTAWAY',1);
            define('NOT_PUTAWAY',0);
            define('WAIT_APPROVE',0);
            define('APPROVE_DECLINE',2);
            define('APPROVE',1);
            $Model = M('knowledge_resource');

            $where['knowledge_resource.id']=$id;
            $result = $Model
                ->join('knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = knowledge_resource.id')
                ->field('knowledge_resource.*,knowledge_resource_file_contact.id contactid,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.flag,knowledge_resource_file_contact.resource_path')
                ->where($where)
                ->find();

            if(!empty($result)) {
                if ($result['status'] == WAIT_APPROVE || $result['status'] == APPROVE_DECLINE) {
                    $resultOld = $Model
                        ->join('knowledge_resource_file_contact_backup ON knowledge_resource_file_contact_backup.resource_id = knowledge_resource.id')
                        ->field('knowledge_resource.*,knowledge_resource_file_contact_backup.id contactid,knowledge_resource_file_contact_backup.vid,knowledge_resource_file_contact_backup.flag,knowledge_resource_file_contact_backup.resource_path')
                        ->where($where)
                        ->find();
                    if(!empty($resultOld))
                        $result = $resultOld;
                }
            }
            if(empty($result))
            {
                $this->display('jbresourceEmpty');
                exit;
            }
            else {
                if ($result['file_type'] == 'video' || $result['file_type'] == 'audio') {
                    $config = C('BLWS_CONFIG');
                    $dev = new DEVICE();
                    $this->assign('platform', $dev->get_device_type());
                    $blwsQueryResult = json_decode(file_get_contents("http://v.polyv.net/uc/services/rest?method=getById&vid=" . $result['vid'] . "&readtoken=" . $config['READ_TOKEN']), true);
                    if ($result['file_type'] == 'video')
                        $mediaSource = $blwsQueryResult['data'][0]['mp4'];
                    else
                        $mediaSource = $blwsQueryResult['data'][0]['mp4'];
                    if ($_GET['debug'])
                        var_dump($blwsQueryResult);
                    $this->assign('vid', $result['vid']);
                    $result['mediaSource'] = $mediaSource;
                }


                $this->assign('data', $result);

                //观看次数+1
                $Model->where("id=$id")->setInc('follow_count', 1);
                $this->assign("REMOTE_ADDR", C('REMOTE_ADDR'));
                $this->display();
            }

        } else {

            $this->assign('sign',$sign);
            $this->assign('userag',$userag);
            $this->display('jbyerror');

        }
    }

    public function getjbresourcedata() {
        $id = $_GET['id'];
        $sign = I('sign');
        $userag = $_SERVER['HTTP_USER_AGENT'];

        $show=false;
        if ( $sign == 'jingbanyun' ) {
            $show=true;
        }else{
            exit;
        }

        $show = true;


        $this->assign('urldata', WEB_URL.$_SERVER['REQUEST_URI']);

        if ($show) {
            $Model = M('knowledge_resource');
            $result = $Model
                ->join('knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = knowledge_resource.id')
                ->field('knowledge_resource.*,knowledge_resource_file_contact.id contactid,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.flag,knowledge_resource_file_contact.resource_path')
                ->where("knowledge_resource.id=$id")
                ->find();
            if($result['file_type'] == 'video' || $result['file_type'] == 'audio')
            {
                $config = C('BLWS_CONFIG');
                $dev = new DEVICE();
                $this->assign('platform',$dev->get_device_type());
                $blwsQueryResult = json_decode(file_get_contents("http://v.polyv.net/uc/services/rest?method=getById&vid=".$result['vid']."&readtoken=".$config['READ_TOKEN']),true);
                $mediaSource = $blwsQueryResult['data'][0]['mp4'];
                $result['mediaSource'] = $mediaSource;
            }


            $this->assign('data', $result);

            //观看次数+1
            $Model->where("id=$id")->setInc('follow_count', 1);
            $this->assign("REMOTE_ADDR",C('REMOTE_ADDR'));
            $this->display('jbresource');

        } else {

            $this->assign('sign',$sign);
            $this->assign('userag',$userag);
            $this->display('jbyerror');

        }
    }

    function bjResourceViewer()
    {
        $id = $_GET['id'];
        //get resource details
        $Model = M('biz_bj_resources');
        $result = $Model
            ->field('id,name,type,file_path,oss_path')
            ->where("id=$id")
            ->find();

        //2016-06-15/57603ec224841.docx
        $arr = explode("/", $result[file_path]);
        $fileName = $arr[1];
        $arr2 = explode(".", $fileName);
        $this->assign('pdffileName', $arr2[0] . ".pdf");
        $this->assign('data', $result);

        $this->display();
    }


    function splitExerciseChapter()
    {
        $Model = M('biz_exercise_library_chapter');
        $result = $Model->select();
        foreach ($result as $key => $val) {
            $c = $result[$key]['chapter'];
            $arr = explode(' ', $c);
            $first = $arr[0];
            $second = $arr[1];
            $title = '';
            $festival = '';
            if (strripos($first, "章") != false) {
                $title = $first;
            };
            if (strripos($second, "节") != false) {
                $festival = $second;
            };

            $data['title'] = $title;
            $data['festival'] = $festival;
            $Model->where("id=" . $result[$key]['id'])->save($data);
        }
        echo "ok";
    }

    function processPreHomework()
    {
        $Model = M('biz_homework');
        $result = $Model->where("exercise_chapter_id!='0'")->select();
        $E = M('biz_exercise_library');
        $HE = M('biz_homework_exercise');

        foreach ($result as $key => $val) {
            $chapter_id = $result[$key]['exercise_chapter_id'];
            $exercises = $E->where("chapter_id=$chapter_id")->select();
            foreach ($exercises as $key2 => $val2) {
                $data['homework_id'] = $result[$key]['id'];
                $data['exercise_id'] = $exercises[$key2]['id'];
                $data['chapter_id'] = $chapter_id;
                $HE->add($data);
            }
        }

        echo "ok";

    }
    
    //这里错误页面
    public function systemError(){
        if(isset($_SERVER['HTTP_REFERER'])){
            $str=strtolower($_SERVER['HTTP_REFERER']);
            $c_teach_index=strpos($str,'c=teach'); 
            $c_student_index=strpos($str,'c=sutdent');
            $c_parent_index=strpos($str,'c=parent');
            if($c_teach_index && !$c_student_index && !$c_parent_index){
                $role=1;
            }elseif($c_student_index && !$c_teach_index && !$c_parent_index){
                $role=2;
            }elseif($c_parent_index && !$c_teach_index && !$c_student_index){
                $role=3;
            }else{ 
                $teach_index=strpos($str,'/teach');
                $student_index=strpos($str,'/student');
                $parent_index=strpos($str,'/parent');
                if($teach_index && !$student_index && !$parent_index){ 
                    $role=1;
                }else if($student_index && !$teach_index && !$parent_index){ 
                    $role=2;
                }else if($parent_index && !$teach_index && !$student_index){ 
                    $role=3;
                }else{
                    $role=0;
                }
            } 
        }else{
            $role=0;
        }
        $this->assign('role',$role);
        $this->display();
    }
    
    //注册
    public function register(){ 
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);

        setSourceParameter();
        
        $share_string='';
        $share_url=getParameter('url','str',false);
        if(!empty($share_url)){
            $share_string='url='.$share_url;
            $deurl = base64_decode($share_url);

            $parr = '/activity_id=(.*)/';
            preg_match($parr, $deurl, $matchs);
            if (!empty($matchs[1]))
                session('rid',$matchs[1]);
        }

        $this->assign('share_str', $share_string); 
        $this->display();
    }
            
}
