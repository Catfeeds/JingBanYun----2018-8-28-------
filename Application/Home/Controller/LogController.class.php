<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
class LogController extends PublicController{
    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
    }

    public function addAccessRecord()
    {
    //TODO:get int c m a from $_POST['access_url']
//     if(gethostbyname(substr($_SERVER["SERVER_NAME"],0,strpos($_SERVER["SERVER_NAME"],':'))) !== get_client_ip());
//      ;//exit;
      $accessTime = $_POST['access_time'];
      $accessDate = strtotime(date("Y-m-d",$accessTime));
      $accessDateByHours = strtotime(date("Y-m-d H:0:0",$accessTime));
      if(is_null($_POST['user_id']))
      {
          exit;
      }
      $data = array(
          'role' => $_POST['role'],
          'user_id' => $_POST['user_id'],
          'user_agent' =>$_POST['user_agent'],
          'http_refer' =>remove_xss($_POST['http_refer']),
          'ip_address' =>$_POST['ip_address'],
          'access_url' => remove_xss($_POST['access_url']),
          'parameters_post' => $_POST['parameters_post'],
          'access_time' =>$accessTime,
          'access_date' =>$accessDate,

      );
     M('usertables.user_access')->add($data);

/********************************往用户访问表写数据***********************************/
     if($_POST['user_id'] != 0 && $_POST['role'] == 0 &&  $_POST['controller_name'] != 'Common' &&  $_POST['controller_name'] != 'Message'){
         $data = array(
             'teacher_id' => $_POST['user_id'],
             'user_agent' =>$_POST['user_agent'],
             'access_ip' =>$_POST['ip_address'],
             'access_time' =>$accessTime,
         );
         M('usertables.access_history_teacher')->add($data);
     }elseif ($_POST['user_id'] != 0 && $_POST['role'] == 1  &&  $_POST['controller_name'] != 'Common' &&  $_POST['controller_name'] != 'Message'){
         $data = array(
             'student_id' => $_POST['user_id'],
             'user_agent' =>$_POST['user_agent'],
             'access_ip' =>$_POST['ip_address'],
             'access_time' =>$accessTime,
         );
         M('usertables.access_history_student')->add($data);
     }elseif ($_POST['user_id'] != 0 && $_POST['role'] == 2  &&  $_POST['controller_name'] != 'Common' &&  $_POST['controller_name'] != 'Message'){
         $data = array(
             'parent_id' => $_POST['user_id'],
             'user_agent' =>$_POST['user_agent'],
             'access_ip' =>$_POST['ip_address'],
             'access_time' =>$accessTime,
         );
         M('usertables.access_history_parent')->add($data);
     }



        //TODO:往user_access_digital_classroom表里写数据时有错误
        /********************************往数字课堂历史记录表写数据***********************************/
        //$pattern='/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(\/index.php\?m=Home\&c=DigitalClassroom\&a=index)+/';
        $pattern='/(\/index.php\?m=home\&c=digitalclassroom\&a=index)+/';
        if($_POST['user_id'] != 0 && $_POST['role'] == 0 && preg_match($pattern,remove_xss(strtolower($_POST['access_url']))) != 0 ){
            $patternTwo='/\&classroomid=\d+/';
            preg_match($patternTwo,remove_xss(strtolower($_POST['access_url'])),$str);
            $arr = explode('=',$str[0]);
            //查询进入的数字课堂的学科
            $result = M('biz_classroom_information')
                ->where("id=$arr[1]")
                ->find();
            $data = array(
                'user_id' =>$_POST['user_id'],
                'access_time' => $accessTime,
                'course_id' => $result['course_id'],
            );
            //入库的操作
            $userAccessDigitalClassroom = $this->userAccessDigitalClassroomAdd($data);
        }


        //TODO:往user_access_total表里写数据时有错误
        /********************************往模块访问统计表写数据***********************************/
            if($_POST['user_id'] != 0) {
                //先查询同一个时间段内有没有相同的数据，有的话加1
                /*$where['role'] == $_POST['role'];
                $where['access_url'] == remove_xss($_POST['access_url']);
                $where['access_time'] == $this->format($accessTime);
                $result = M('user_access_total')
                            ->where($where)
                            ->find();
                if(!empty($result)){
                    $data['access_total'] = $result['access_total'] + 1;
                    $result = M('user_access_total')
                        ->where($where)
                        ->save($data);
                }else{
                    $data = array(
                        'role' => $_POST['role'],
                        'access_url' => remove_xss($_POST['access_url']),
                        'access_time' => $this->format($accessTime),
                    );
                    M('user_access_total')->add($data);
                }*/
                $controllString = $_POST['controller_name'];
                //过滤controller_name
                $pos1 = stripos($controllString, 'sion');
                if ($pos1 == 0) {
                    $controllString = $_POST['controller_name'];
                } else {
                    $controllString = explode('/', $controllString);
                    $controllString = $controllString[1];
                }
                $role = $_POST['role'];
                $module_name =  $_POST['module_name'];
                $controller_name = $controllString;
                $action_name =  $_POST['action_name'];
                $userId = $_POST['user_id'];

                $user_access_total = M('usertables.user_access_total');
                $user_access_total->startTrans();
                $sql = "INSERT INTO usertables.user_access_total (role,userId,access_time,module_name,controller_name,action_name) VALUES ($role,$userId,$accessDateByHours,'$module_name','$controller_name','$action_name') ON DUPLICATE KEY UPDATE access_total = access_total+1";
                $status = M('usertables.user_access_total')->execute($sql);
                if ($status === false) {
                    $user_access_total->rollback();
                } else {
                    $user_access_total->commit();
                }
            }
    }
    /**
     *描述：user_access_digital_classroom添加操作
     */
    public function userAccessDigitalClassroomAdd($data){
        return M('usertables.user_access_digital_classroom')
            ->add($data);
    }


    public function format($time){
        $year = date('Y',$time);
        $month = date('m',$time);
        $day = date('d',$time);
        $hour = date('H',$time);
        $newTime = mktime($hour,0,0,$month,$day,$year);
    }

    public function getNewestLog()
    {
       $size = getParameter('size','int');
       $result = M()->query("SELECT id '序号',role '角色id',user_id '用户ID',ip_address 'IP地址',user_agent 'agent',access_url '访问地址',http_refer 'refer地址',from_unixtime(access_time) '访问时间' FROM usertables.user_access order by id desc LIMIT $size");
       if(empty($result))
           die;
       echo '<style>table td{border:1px solid #000} </style>';
       echo '<table>';
        //thead
        echo '<tr style="background-color:gainsboro">';
        foreach($result[0] as $key=>$val)
        {
           echo "<td>$key</td>";
        }
        echo '</tr>';
        //data
        foreach($result as $key=>$val)
        {
            echo "<tr>";
            foreach($val as $subKey => $subVal)
            {
                echo "<td>$subVal</td>";
            }
            echo "</tr>";
        }
       echo '</table>';
    }
}



?>