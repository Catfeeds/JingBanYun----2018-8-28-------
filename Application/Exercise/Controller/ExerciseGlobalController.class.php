<?php
namespace Exercise\Controller;
use Think\Controller;
use Common\Common\CSV;

class ExerciseGlobalController extends Controller {
    public static $version_config;
    private function _getRequestUri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
            } else {
                $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $uri;
    }
    private function publicDisplay()
    {
        $_SESSION['pageName'][$this->_getRequestUri()] = array('parent'=>$this->get('parent'),
            'parentHref'=>$this->get('parentHref') ,
            'own'=>$this->get('own')
        );
        if(ACTION_NAME == 'exerciseDetails')
        {
            $referUrl = substr($_SERVER['HTTP_REFERER'],strpos($_SERVER['HTTP_REFERER'],'/',8));
            $this->assign('parent',$_SESSION['pageName'][$referUrl]['parent']);
            $this->assign('parentHref','javascript:history.go(-1)');
            $this->assign('own',' >> 习题详情');
        }
        else if(ACTION_NAME == 'paperDetails')
        {
            $referUrl = substr($_SERVER['HTTP_REFERER'],strpos($_SERVER['HTTP_REFERER'],'/',8));
            $this->assign('parent',$_SESSION['pageName'][$referUrl]['parent']);
            $this->assign('parentHref','javascript:history.go(-1)');
            $this->assign('own',' >> 试卷详情');
        }
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
        $this->assign('authority', session('admin.authority'));
        $this->assign('userName', session('admin.user_name'));
    }
    public function display_nocache($template='')
    {
        $this->publicDisplay();
        parent::display_nocache($template);
    }
    public function display($template='')
    {

        $this->publicDisplay();
        parent::display($template);
    }

    //限制浏览器多个窗口打开同一页面只有最新打开的页面有AJAX请求的权限
    private function _comfineClientOnePage()
    {
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            // ajax 请求的处理方式
            $pageToken = getParameter('pageToken','int');
            $referUrl = substr($_SERVER['HTTP_REFERER'],strpos($_SERVER['HTTP_REFERER'],'/',8));
            if($pageToken != $_SESSION['pageToken'][$referUrl])
            {
                $this->showMessage(505,"您没有权限进行该操作");
            }
        }else{
            // 正常请求的处理方式
            $pageToken = strval(rand(100000,999999));
            $this->assign('pageToken',$pageToken);
            if(!is_array($_SESSION['pageToken']))
            {
                $_SESSION['pageToken'] = [];
            }
            $_SESSION['pageToken'][$this->_getRequestUri()] = $pageToken;
        };
    }

    public function __construct() {
        parent::__construct();

        $this->model=D('Auth_admin');
        if(ACTION_NAME !="userBehaviorETL_Day" && CONTROLLER_NAME !="BjResource" && ACTION_NAME !='importBjVideoResource'){
            if(!session('?admin') || empty(session('admin.user_name')) )
                redirect(U('Login/login'));
            $this->check_permissions();
        }
        $this->accountStatusIsDelete();
    }

    /*
     * 获取用户ID 角色 权限
     */
    public function getUserRoleAuth()
    {
        $role = session('admin.role');
        $authority = session('admin.authority');
        $userId = session('admin.id');
        $userName = session('admin.user_name');
        $roleName = session('admin.role_name');
        $course = session('admin.course_id');
        return array('id'=>$userId,'name'=>$userName,'role'=>$role,'authority'=>$authority,'roleName'=>$roleName,'courseId'=>$course);
    }

    private function rotate($a){
        $b=array();
        foreach($a as $val){
            foreach($val as $k=>$v){
                $b[$k][] = $v;
            }
        }
        return $b;
    }
    /*
     * 根据自定义HEADER与MODEL查出的数据生成前台需要的数据格式
     */
    public function transData($header,$data)
    {
        $dataSelect = array();
        $returnResult = array();
        foreach($header as $key=>$val)
        {
            if(gettype($val['callback']) == 'object')
            {
                $fieldNames = explode(',',$val['field']);
                if(sizeof($fieldNames)>1) { //多个字段
                    $localData = array();
                    foreach ($fieldNames as $null => $fieldName) {
                        $localData[] = array_column($data, $fieldName);
                    }


                    $lineData = array();
                    for ($i = 0; $i < sizeof($data); $i++) {
                        $unConvertData = array();
                        for ($j = 0; $j < sizeof($localData); $j++) {
                            $unConvertData = array_merge($unConvertData, array($localData[$j][$i]));
                        }
                        $transedData = $val['callback']($unConvertData);
                        $lineData = array_merge($lineData,array($transedData));
                    }
                    $dataSelect[] = $lineData;
                }
                else
                {
                    $selectData = array_column($data,$val['field']);
                    for($i=0;$i<sizeof($selectData);$i++)
                    {
                        $selectData[$i] = $val['callback']($selectData[$i]);

                    }
                    $dataSelect[] = $selectData;
                }
            }
            else{
                $fieldNames = explode(',',$val['field']);
                if(sizeof($fieldNames)>1) { //多个字段
                    echo '参数数量错误';die;
                }
                $dataSelect[]  = array_column($data,$val['field']);
            }
        }
        $dataSelect = $this->rotate($dataSelect);
        for($i=0;$i<sizeof($data);$i++)
        {
            $returnResult[] = array('id'=>$data[$i]['id'],'data'=>$dataSelect[$i]);
        }
        return $returnResult;
    }

    /*
     * 导出CSV
     */
    public function exportCSV($fileName,$header,$data)
    {
        $csv=new CSV();
        $str = '';
        $csvContent = '';

        if(empty($data))
        {
            echo "<script>alert('数据为空,无法导出数据')</script>";
            exit;
        }
        $csvHeader = implode(',',array_column($header,'name'))."\r\n";
        foreach($data as $key=> $val)
        {
            $csvContent.= implode(',',array_values($val));
            $csvContent.= "\r\n";
        }

        $str.=$csvHeader . $csvContent;
        $str=mb_convert_encoding($str,'gbk','utf-8');
        $csv->downloadFileCsv($fileName,$str);
    }

    /*
    * 检查是否有操作权限
    */
    function check_permissions($is_ajax=0,$is_iframe=0){
        if($_POST['tip']){
            $action=CONTROLLER_NAME.'/'.ACTION_NAME.$_POST['tip'];
        }else{
            $action=CONTROLLER_NAME.'/'.ACTION_NAME;
        }
        $permissions=session('exercises_permissions');
        /*if($action != 'Index/index'){
            if(!in_array($action,$permissions)){
                if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
                    //$this->showMessage(501,'无权进行操作');
                }elseif ($is_iframe == true){
                    return 1;//针对ifream中的形式
                }
                else{
                    //$this->redirect(U('Index/index',array('error'=>'1')));
                    //echo '<script>history.go(-1)</script>>';
                }
            }
        }*/
        if($action != 'Index/index'){
            if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
                if(!in_array($action,$permissions)){
                        //$this->showMessage(501,'无权进行操作');
                    }
            }elseif ($is_iframe == true){
                if(!in_array($action,$permissions)){
                    return 1;//针对ifream中的形式
                }

            } else {
                if(!in_array($action,$permissions)){
                    echo '<script>history.go(-1)</script>';
                }
            }
        }

    }

    /*
     *查看当前账号是否是被删除状态
     */
    public function accountStatusIsDelete(){
        $user = $this->getUserRoleAuth();
        $where['exercises_account.id'] = $user['id'];
        $delete = D('Exercises_account')->getResourcesOne($where);
        if($delete['delete_status'] == DELETE_STATUS_TRUE || $delete['account_status'] == ACCOUNT_STATUS_DISABLE){
            session('admin', null);
            session('exercises_permissions', null);
            redirect(U('Login/login'));
        }
    }

    private function publicVerifyAuth($info,$paras)
    {
        if(empty($info))
         $info = $paras['model']->$paras['getInfoMethod']($paras['id']);
        if($info['is_delete'] == STATE_DELETED)
            $this->error($paras['name'].'已经被删除');
        $verifyResult = true;
        if(!is_array($paras['preState'])) {
            if ($info['status'] != $paras['preState'])
                $verifyResult = false;
        }
        else
        {
            $verifyResult = false;
            foreach($paras['preState'] as $key => $val)
            {
                if($info['status'] == $val)
                    $verifyResult = true;
            }
        }
        if(!$verifyResult)
            $this->error($paras['name'].'状态已经改变，无法进行该操作');

    }
    /*
     * 校验当前习题是否能被操作
     * 参数：习题ID 习题前置状态STATE
     */
    protected function verifyExerciseOperationAuth($exerciseInfo,$exerciseState,$exerciseId=0)
    {
        if(empty($exerciseInfo) && empty($exerciseId))
            $this->error('参数错误');
        $paras = [
         'name' => '习题',
         'model' => D('Exercises_createexercise'),
         'getInfoMethod' => 'getExerciseInfo',
         'id' => $exerciseId,
         'preState' => $exerciseState
        ];
        $this->publicVerifyAuth($exerciseInfo,$paras);
    }

    /*
     * 校验当前试卷是否能被操作
     * 参数：试卷ID 试卷前置状态STATE
     */
    protected function verifyPaperOperationAuth($paperInfo,$paperState=0,$paperId=0)
    {
        if(empty($paperInfo) && empty($paperId))
            $this->error('参数错误');
        $paras = [
            'name' => '试卷',
            'model' => D('Exercises_paper_processinfo'),
            'getInfoMethod' => 'getPaperInfo',
            'id' => $paperId,
            'preState' => $paperState
        ];
        $this->publicVerifyAuth($paperInfo,$paras);
    }



}