<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;
use Common\Common\REDIS;
use Common\Common\JSSDK;

define('FAVOR_STATUS',1);
define('DEFAVOR_STATUS',2);

define('ICON_VIDEO','http://'.WEB_URL.'/Public/img/app/icon_video.png');
define('ICON_PDF','http://'.WEB_URL.'/Public/img/app/icon_pdf.png');
define('ICON_WORD','http://'.WEB_URL.'/Public/img/app/icon_word.png');
define('ICON_AUDIO','http://'.WEB_URL.'/Public/img/app/icon_mp3.png');
define('ICON_IMAGE','http://'.WEB_URL.'/Public/img/app/icon_pic.png');
define('ICON_PPT','http://'.WEB_URL.'/Public/img/app/icon_ppt.png');
define('ICON_ZIP','http://'.WEB_URL.'/Public/img/app/icon_zip.png');
define('ICON_SWF','http://'.WEB_URL.'/Public/img/app/icon_swf.png');
define('ICON_HTML','http://'.WEB_URL.'/Public/img/app/icon_html.png');
define('ICON_MIXED','http://'.WEB_URL.'/Public/img/app/icon_mixed.png');

define('RECOMMEND_RESOURCE_CLOUMN_ID',2);
define('RECOMMEND_RESOURCE_ROWS',6);
define('BOUTIQUE_RESOURCE_COLUMN_ID',1);
define('BOUTIQUE_RESOURCE_ROWS',9);
define('HJ_RESOURCE_CLOUMN_ID',3);
define('HJ_RESOURCE_ROWS',4);
define('TOP_LEVEL',1);

define('CULTURE_RESOURCE_COLUMN_ID',6);
define('CULTURE_RESOURCE_ROWS',4);
define('JB_RESOURCE_NULL',0); //default
define('JB_RESOURCE_JB',1); //京版资源
define('JB_RESOURCE_NOBOOK',2); //NOBOOK
define('JB_RESOURCE_WB',3); //万邦
define('JB_RESOURCE_HTML',4); //京版资源网页
class KnowledgeResourceController extends PublicController
{

    public $pageSize = 20;
    public $model;
    public function __construct() {
        parent::__construct();
        $this->model = D('Knowledge_resource');
        $this->assign('oss_path', C('oss_path'));
    }
    function getPageSize(){
        return $this->pageSize;
    }
    private function filterContent2(&$result)
    {
        foreach($result as $key => $val)
        {
            $content = $val['content3'];
            $sourceExpression = '/来源：([\w\x{4e00}-\x{9fa5}]+)   创作者：/u';
            $creatorExpression = '/创作者：([\w\x{4e00}-\x{9fa5}]+)[\\s`]*?/u';
            $creatorName = $this->regExtractOnce($content,$creatorExpression)[0];
            $sourceName = $this->regExtractOnce($content,$sourceExpression)[0];
            if(empty(trim($creatorName)))
                $result[$key]['content3'] = str_replace("创作者：","",$result[$key]['content3']);
            if(empty(trim($sourceName)))
                $result[$key]['content3'] = str_replace("来源：","",$result[$key]['content3']);
        }
    }
    private function addOssPath($array,$field)
    {
      $ossPath = C('oss_path');
      for($i=0;$i<sizeof($array);$i++)
      {
          if(false === strpos($array[$i][$field],'http://'))
          $array[$i][$field] = $ossPath . $array[$i][$field];
      }
      return $array;
    }

    private function regExtractOnce($str, $expression)
    {
        preg_match_all($expression, $str, $matches);
        return array_unique(array_pop($matches));
    }

    private function __getHomeResources($courseId,$gradeId){
        $columnArray = array(RECOMMEND_RESOURCE_CLOUMN_ID, BOUTIQUE_RESOURCE_COLUMN_ID, HJ_RESOURCE_CLOUMN_ID, CULTURE_RESOURCE_COLUMN_ID);
        $sizeArray = array(RECOMMEND_RESOURCE_ROWS, BOUTIQUE_RESOURCE_ROWS, RECOMMEND_RESOURCE_ROWS, CULTURE_RESOURCE_ROWS);
        $listArray = [];
        $esAvailable = getESAvailable();
        if(1 == $esAvailable){
            for ($i = 0; $i < sizeof($columnArray); $i++) {
                $where['userCourseId'] = $courseId;
                $where['userGradeId'] = $gradeId;
                $where['knowledge_resource_attr.column_id'] = $columnArray[$i];
                if($columnArray[$i] == CULTURE_RESOURCE_COLUMN_ID){
                    $where['knowledge_resource.id'] = GUOXUE_ID;
                }
                $listArray[$i] = $this->model->getResourceData($where,'',0,0,$sizeArray[$i])['data'];
                foreach($listArray[$i] as $key=>$val){
                    $listArray[$i][$key]['resource_id'] =  $val['id'];
                    $listArray[$i][$key]['mobile_cover'] =  $val['img'];
                }
            }
        }
        else {
            $redis_obj = new REDIS();
            $redis = $redis_obj->init_redis();
            $listArray = array();

            if (1001 != $redis) {

                for ($i = 0; $i < sizeof($columnArray); $i++) {
                    $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-$columnArray[$i]";
                    $resourceIdList[$i] = $redis->get($redis_key);
                }
                $redis->close();
                for ($i = 0; $i < sizeof($columnArray); $i++) {
                    if (!empty($resourceIdList[$i])) {
                        $listArray[$i] = $this->model->getColumnResourceByOrderIds($resourceIdList[$i], $sizeArray[$i]);
                    }
                }
            }
        }
        return $listArray;
    }
    /**
     * @描述：获取推荐资源赛事分享资源首页列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeResourceList()
    {
        $user_id = getParameter('userId','int');
        $role = getParameter('role','int');
        $courseGradeInfo = (new \Home\Controller\CommonController())->getUserCourseGradeInfo($role,$user_id);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];
        $listArray = $this->__getHomeResources($courseId,$gradeId);

        $bestResult = ( !empty($listArray[1])) ? $listArray[1] :  $this->model->getColumnResource(COLUMN_BESTRESOURCE,9);
        $recommendResult = ( !empty($listArray[0])) ? $listArray[0] : $this->model->getColumnResource(COLUMN_RECOMMENDRESOURCE,6);
        $competitionResult = ( !empty($listArray[2])) ? $listArray[2] : $this->model->getColumnResource(COLUMN_COMPETITIONRESOURCE,6);
        $traditionalResult = ( !empty($listArray[3])) ? $listArray[3] : $this->model->getColumnResource(COLUMN_TRADITIONALCULTURE,6);

        $recommendResult = $this->addOssPath($recommendResult,'img');               
        $competitionResult = $this->addOssPath($competitionResult,'img');
        $bestResult = $this->addOssPath($bestResult,'img');
        $traditionalResult = $this->addOssPath($traditionalResult,'img');

        $recommendResult = $this->addOssPath($recommendResult,'mobile_cover');
        $competitionResult = $this->addOssPath($competitionResult,'mobile_cover');
        $bestResult = $this->addOssPath($bestResult,'mobile_cover');
        $traditionalResult = $this->addOssPath($traditionalResult,'mobile_cover');

        //精品资源
        $bestConvertArray = array(
            array(COLUMN_BESTRESOURCE=>TYPE_STRING),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('resource_id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('course_name' =>TYPE_FIELD) ,array('content1'),
            array('mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'resource_id' =>TYPE_FIELD) ,array('url'),
        );

        $bestResult = fieldsCompose($bestResult,$bestConvertArray);

        $recommendConvertArray = array(
                              array(COLUMN_RECOMMENDRESOURCE=>TYPE_STRING),array('resource_type'),
                              array('resource_name'=> TYPE_FIELD),array('title'),
                              array('resource_id' =>TYPE_FIELD) ,array('id'),
                              array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
                              array('course_name' =>TYPE_FIELD) ,array('content1'),
                              array('grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content2'),
                              array('mobile_cover' =>TYPE_FIELD) ,array('img_url'),
                              array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
                              array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'resource_id' =>TYPE_FIELD) ,array('url'),
        ); 
        $recommendData=$recommendResult;
        $recommendResult = fieldsCompose($recommendResult,$recommendConvertArray); 
        if(!empty($recommendData)){
            foreach($recommendData as $key=>$val){
               if($val['grade_number']>1){
                   $recommendResult[$key]['content1']='多年级';
               } 
               if($val['textbook_number']>1){
                   $recommendResult[$key]['content2']='多分册';
               }
            }
        }

        //国学资源
        $traditionalConvertArray = array(
            array(COLUMN_TRADITIONALCULTURE=>TYPE_STRING),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('resource_id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('course_name' =>TYPE_FIELD) ,array('content1'),
            array('author'=>TYPE_FIELD),array('content2'),
            array('mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('real_price' =>TYPE_FIELD) ,array('real_price'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'resource_id' =>TYPE_FIELD) ,array('url'),
        );

        $traditionalResult = fieldsCompose($traditionalResult,$traditionalConvertArray);
        foreach($traditionalResult as $key=>$val){
            if($val['id'] == GUOXUE_ID)
            {
                $traditionalResult = [];
                $traditionalResult[] = $val;
                break;
            }
        }
        $competitionConvertArray = array(
            array(COLUMN_COMPETITIONRESOURCE=>TYPE_STRING),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('resource_id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('来源：'=>TYPE_STRING,'chinesesource' =>TYPE_FIELD,'   创作者：'=>TYPE_STRING,'author'=>TYPE_FIELD) ,array('content3'),
            array('course_name' =>TYPE_FIELD,'    '=> TYPE_STRING,'grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content4'),
            array('mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'resource_id' =>TYPE_FIELD) ,array('url'),
        );

        $competitionResult = fieldsCompose($competitionResult,$competitionConvertArray); 
        $this->filterContent2($competitionResult);
        $columnNumAppSet = count($traditionalResult);
        if ($columnNumAppSet==1) {
            $columnNumApp = 1;
        }
        if ($columnNumAppSet==2) {
            $columnNumApp = 2;
        }
        if ($columnNumAppSet==3) {
            $columnNumApp = 3;
        }
        if ($columnNumAppSet>3) {
            $columnNumApp = 2;
        }

        $result = array( array('id'=> COLUMN_BESTRESOURCE,'name'=> '精品资源','data' => $bestResult),
                          array('id'=> COLUMN_RECOMMENDRESOURCE,'name'=> '推荐资源','data' => $recommendResult),
                          array('id'=> COLUMN_COMPETITIONRESOURCE,'name'=> '赛事获奖资源','data' => $competitionResult),
                          array('id'=> COLUMN_TRADITIONALCULTURE,'name'=> '传统文化资源','column'=> $columnNumApp,'data' => $traditionalResult),);
        $sort = D('Dict_column')->getColumnSort(array(COLUMN_BESTRESOURCE,COLUMN_RECOMMENDRESOURCE,COLUMN_COMPETITIONRESOURCE,COLUMN_TRADITIONALCULTURE));
        array_multisort($sort,$result);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取全部资源列表
     * @参数：pageIndex[int] Y 页码
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getAllResourceList()
    {
        $agentString = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos(($agentString), 'android') !== false)
        {
            if(( (strpos($agentString,'2.8.0')!==false) ||
                    (strpos($agentString,'2.6.1')!==false) ||
                    (strpos($agentString,'2.8.1')!==false)
                ) && (  (strpos($agentString,'4.3')!==false) ||
                    (strpos($agentString,'4.2.2')!==false) ||
                    (strpos($agentString,'4.2.1')!==false) ||
                    (strpos($agentString,'4.1.2')!==false)
                ))
            {
                $this->showMessage( 500,'您的系统版本较低，请更新手机系统',array());
            }
        }
        $pageIndex = getParameter('pageIndex','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
//        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false)
//            $role += 2;
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
        {
            $pageSize = 21;
        }
        $_GET['p'] = $pageIndex;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;

        $courseGradeInfo = (new \Home\Controller\CommonController())->getUserCourseGradeInfo($role,$userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];

        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if(1001 != $redis) {
            $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-0";
            $resourceIdList = $redis->get($redis_key);
            $redis->close();
        }
        $where['userCourseId'] = $courseId;
        $where['userGradeId'] = $gradeId;

        $result = $this->model->getResourceData($where,'',$userId,$role,$pageSize,$resourceIdList);
        $result['data'] = $this->addOssPath($result['data'],'img');

        $convertArray = array(
            array('column_id'=> TYPE_FIELD),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('course_name' =>TYPE_FIELD) ,array('content1'),
            array('grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content2'),
            array('来源：'=>TYPE_STRING,'chinesesource' =>TYPE_FIELD,'   创作者：'=>TYPE_STRING,'author'=>TYPE_FIELD) ,array('content3'),
            array('course_name' =>TYPE_FIELD,'    '=> TYPE_STRING,'grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content4'),
            array('img' =>TYPE_FIELD) ,array('img_url'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
        ); 
        $result = fieldsCompose($result['data'],$convertArray);
        $this->filterContent2($result);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取更多资源列表
     * @参数：column[int] Y 栏目类型 2--推荐资源 3--赛事获奖资源 4--全部资源
     * @参数：courseId[int] N 学科ID
     * @参数：gradeId[int] N 年级ID
     * @参数：schoolTerm[int] N 分册ID 1--上册 2--下册 3--全一册
     * @参数：fileType[int] 资源文件类型ID (PPT IMAGE ...)
     * @参数：resourceType[int] N 资源类型ID
     * @参数：pageIndex[int] Y 页码
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：order[int] N 排序方法 0--时间降序 1--时间升序 2--浏览量降序
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSpecificMoreResourceList()
    {
        $column = getParameter('column','int');
        $pageIndex = getParameter('pageIndex','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $order = getParameter('order','int',false);
        $pageSize = getParameter('  pageSize','int',false);
        if(empty($pageSize))
        {
            $pageSize = 21;
        }
        $_GET['p'] = $pageIndex;
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        $where['knowledge_resource_attr.column_id'] = $column;
        if(false === $order)
        {
            $orderString = '';
        }
        else {
            switch ($order) {
                case 0 :
                    $orderString = 'desc';
                    break;
                case 1 :
                    $orderString = 'asc';
                    break;
                default:
                    $orderString = '';
                    break;
            }
        }
       
        $result = $this->model->getResourceData($where,$orderString,$userId,$role,$pageSize);
        $category = $this->getResourceListCategory($where);
        $result['data'] = $this->addOssPath($result['data'],'img');

        $convertArray = array(
            array('column_id'=> TYPE_FIELD),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('course_name' =>TYPE_FIELD) ,array('content1'),
            array('grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content2'),
            array('来源：'=>TYPE_STRING,'chinesesource' =>TYPE_FIELD,'   创作者：'=>TYPE_STRING,'author'=>TYPE_FIELD) ,array('content3'),
            array('course_name' =>TYPE_FIELD,'    '=> TYPE_STRING,'grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content4'),
            array('img' =>TYPE_FIELD) ,array('img_url'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
            array('iscollect' =>TYPE_FIELD) ,array('is_collect')
        );
        $result = fieldsCompose($result['data'],$convertArray);
        $this->filterContent2($result);
        $this->showMessage( 200,'success',$result,$category);

    }

    /**
     * @描述：获取筛选后的资源列表
     * @参数：courseId[int] N 学科ID
     * @参数：gradeId[int] N 年级ID
     * @参数：column[int] Y 栏目类型 2--推荐资源 3--赛事获奖资源 4--全部资源
     * @参数：schoolTerm[int] N 分册ID 1--上册 2--下册 3--全一册
     * @参数：textbookId[int] N 电子谭本ID
     * @参数：fileType[int] 资源文件类型ID (PPT IMAGE ...)
     * @参数：resourceType[int] N 资源类型ID (教学设计 教学反思...)
     * @参数：knowledge[int] N 一级知识点ID
     * @参数：childKnowledge[int] N 二级知识点ID
     * @参数：chapter[int] N 章ID
     * @参数：festival[int] N 节ID
     * @参数：keyword[str] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：role[int] N 角色ID
     * @参数：userId[int] N 用户ID
     * @参数：searchType[int] N 搜索类型 1--从高级检索页搜索
     * @参数：order[int] N 排序方法 0--时间降序 1--时间升序 2--浏览量降序
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function getFilteredResourceList()
    {
        $agentString = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos(($agentString), 'android') !== false)
        {
            if(( (strpos($agentString,'2.8.0')!==false) ||
                    (strpos($agentString,'2.6.1')!==false) ||
                    (strpos($agentString,'2.8.1')!==false)
                ) && (  (strpos($agentString,'4.3')!==false) ||
                    (strpos($agentString,'4.2.2')!==false) ||
                    (strpos($agentString,'4.2.1')!==false) ||
                    (strpos($agentString,'4.1.2')!==false)
                ))
            {
                $this->showMessage( 500,'您的系统版本较低，请更新手机系统',array());
            }
        }
        $column = getParameter('column','int',false);
        $courseId = getParameter('courseId','int',false);
        $gradeId = getParameter('gradeId','int',false);
        $schoolTerm = getParameter('schoolTerm','int',false);
        $textbookId = getParameter('textbookId','int',false);
        $fileType = getParameter('fileType','str',false);
        $resourceType = getParameter('resourceType','int',false);
        $knowledge = getParameter('knowledge','int',false);
        $childKnowledge = getParameter('childKnowledge','int',false);
        $chapter = getParameter('chapter','int',false);
        $festival = getParameter('festival','int',false);
        $keyword = trim(getParameter('keyword','str',false));
        $pageIndex = getParameter('pageIndex','int');
        $order = getParameter('order','int',false);
        $type = getParameter('searchType','int',false);
        $_GET['p'] = $pageIndex;
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $where = array();
        if(!empty($column) && 4 != $column)
            $where['knowledge_resource_attr.column_id'] = $column;
        if(!empty($courseId))
            $where['knowledge_resource_point.course'] = $courseId;
        if(!empty($gradeId))
            $where['knowledge_resource_point.grade'] = $gradeId;
        if(!empty($schoolTerm))
            $where['biz_textbook.school_term'] = $schoolTerm;
        if(!empty($textbookId))
            $where['knowledge_resource_point.textbook'] = $textbookId;
        if(!empty($fileType))
            $where['knowledge_resource.file_type'] = $fileType;
        if(!empty($resourceType))
            $where['knowledge_resource_type_contact.type_id'] = $resourceType;
        if(!empty($knowledge))
            $where['knowledge_resource_point.knowledge'] = $knowledge;
        if(!empty($childKnowledge))
            $where['knowledge_resource_point.child_knowledge'] = $childKnowledge;
        if(!empty($chapter))
            $where['knowledge_resource_point.chapter'] = $chapter;
        if(!empty($festival))
            $where['knowledge_resource_point.festival'] = $festival;
        if(!empty($keyword)) {
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where['knowledge_resource_point.knowledge_info'][] = array('like', '%' . $item . '%');
            }
        }
        $where['knowledge_resource.putaway_status']=PUTAWAY;
        $where['knowledge_resource.status']=APPROVE;
        if(false === $order)
        {
            $orderString = '';
        }
        else {
            switch ($order) {
                case 0 :
                    $orderString = 'desc';
                    break;
                case 1 :
                    $orderString = 'asc';
                    break;
                default:
                    $orderString = '';
                    break;
            }
        }

        $courseGradeInfo = (new \Home\Controller\CommonController())->getUserCourseGradeInfo($role,$userId);
        $courseId = $courseGradeInfo['courseId'];
        $gradeId = $courseGradeInfo['gradeId'];

        $redis_obj = new \Common\Common\REDIS();
        $redis = $redis_obj->init_redis();
        if(1001 != $redis) {
            if ($column != 0)
                $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-" . $column;
            else
                $redis_key = $_SERVER['SERVER_NAME'] . ":recommendResource-$courseId-$gradeId-0";
            $resourceIdList = $redis->get($redis_key);
            $redis->close();
        }
        $where['userCourseId'] = $courseId;
        $where['userGradeId'] = $gradeId;

        $result = $this->model->getResourceData($where,$orderString,$userId,$role,$this->pageSize,$resourceIdList);

        unset($where['userCourseId']);
        unset($where['userGradeId']);
        if(!empty($result['ids']))
         $newWhere['knowledge_resource.id'] = array('in',$result['ids']);
        else
         $newWhere = $where;
        $category = $this->getResourceListCategory($newWhere,$type);
        $result['data'] = $this->addOssPath($result['data'],'img');

        $convertArray = array(
            array('column_id'=> TYPE_FIELD),array('resource_type'),
            array('resource_name'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array('file_type'=> TYPE_PAIR,'mixed'=>ICON_MIXED,'html'=>ICON_HTML,'swf'=>ICON_SWF,'condensed'=>ICON_ZIP,'video'=>ICON_VIDEO,'audio'=>ICON_AUDIO,'ppt'=>ICON_PPT,'word'=>ICON_WORD,'pdf'=>ICON_PDF,'image'=>ICON_IMAGE),array('icon_url'),
            array('course_name' =>TYPE_FIELD) ,array('content1'),
            array('grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content2'),
            array('来源：'=>TYPE_STRING,'chinesesource' =>TYPE_FIELD,'   创作者：'=>TYPE_STRING,'author'=>TYPE_FIELD) ,array('content3'),
            array('course_name' =>TYPE_FIELD,'    '=> TYPE_STRING,'grade' =>TYPE_FIELD,'term'=>TYPE_FIELD) ,array('content4'),
            array('img' =>TYPE_FIELD) ,array('img_url'),
            array('charge_status'=> TYPE_PAIR,'1'=>'1','2'=>'0'),array('is_free'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
            array('iscollect' =>TYPE_FIELD) ,array('is_collect')
        );
        $result = fieldsCompose($result['data'],$convertArray);
        $this->filterContent2($result);
        $this->showMessage( 200,'success',$result,$category);
    }

    private function getResourceListCategory($where,$type)
    {
        $this->model->createResourceTempTable();
        $this->model->insertTempData($where);
        $course_con=$this->model->getCondition('course');
        $course_con_new = array();
        for($i=0;$i<sizeof($course_con);$i++) {
            $course_con_new[$i]['value'] = $course_con[$i]['course_id'];
            $course_con_new[$i]['title'] = $course_con[$i]['course'];
        }
        $grade_con=$this->model->getCondition('grade');
        $grade_con_new = array();
        for($i=0;$i<sizeof($grade_con);$i++) {
            $grade_con_new[$i]['value'] = $grade_con[$i]['grade_id'];
            $grade_con_new[$i]['title'] = $grade_con[$i]['grade'];
        }

            if($grade_con_new[0]['value'] == 0)
                array_splice($grade_con_new,0,1);

        $school_term_con=$this->model->getCondition('school_term');
        $school_term_con_new = array();
        for($i=0;$i<sizeof($school_term_con);$i++) {
            $school_term_con_new[$i]['value'] = $school_term_con[$i]['school_term'];
        }


            if($school_term_con_new[0]['value'] == 0)
                array_splice($school_term_con_new,0,1);


        $file_type_con=$this->model->getCondition('file_type');
        $file_type_con_new = array();
        for($i=0;$i<sizeof($file_type_con);$i++) {
            $file_type_con_new[$i]['value'] = $file_type_con[$i]['file_type'];
            foreach(C('BJ_RESOURCE_UPLOAD_FILE_TYPE') as $key=>$val)
            {
              if($val['value'] == $file_type_con_new[$i]['value'])
              {
                  $file_type_con_new[$i]['title'] = $key;
                  break;
              }
            }

        }

            if($file_type_con_new[0]['value'] == '0')
                array_splice($file_type_con_new,0,1);

        $allTypeArray = $this->model->getResourceType();
        $type_id_con=$this->model->getCondition('type_id'); //资源类型
        $type_id_con_new = array();
        for($i=0;$i<sizeof($type_id_con);$i++) {
            $type_id_con_new[$i]['value'] = $type_id_con[$i]['type_id'];
            foreach($allTypeArray as $key=>$val)
            {
                if($val['id'] == $type_id_con_new[$i]['value'])
                {
                    $type_id_con_new[$i]['title'] = $val['type_name'];
                    break;
                }
            }
        }

        for($i=0;$i<sizeof($school_term_con_new);$i++) {
            switch ($school_term_con_new[$i]['value'])
            {
                case 1: $school_term_con_new[$i]['title'] = '上册';
                    break;
                case 2: $school_term_con_new[$i]['title'] = '下册';
                    break;
                case 3: $school_term_con_new[$i]['title'] = '全一册';
                    break;
            }
        }

        //栏目名称
            $attr_con=$this->model->getColumnAttr($where);
        $attr_con_new = array();
        for($i=0;$i<sizeof($attr_con);$i++) {
            $attr_con_new[$i]['value'] = $attr_con[$i]['id'];
            $attr_con_new[$i]['title'] = $attr_con[$i]['column_name'];
        }
        $returnArray = array();
        if($type != 1)
        {
          $returnArray[] =  array('title'=>'学科','fieldName' => 'courseId','data' => $course_con_new);
        }
        if($type != 1 )
        {
            $returnArray[] = array('title' => '年级','fieldName' => 'gradeId','data' => $grade_con_new);
        }
        if($type != 1 )
        {
            $returnArray[] = array('title' => '分册','fieldName' => 'schoolTerm' ,'data' => $school_term_con_new);
        }
        $returnArray[] = array('title' => '栏目名称','fieldName' => 'column','data' => $attr_con_new);
        $returnArray[] = array('title' => '资源类型','fieldName' => 'resourceType','data' => $type_id_con_new);
        $returnArray[] = array('title'=> '资源格式','fieldName' => 'fileType','data'=> $file_type_con_new);
        return $returnArray;
    }
    /**
     * @描述：获取资源分类列表
     * @参数：无
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getColumnList()
    {
      $result = $this->model->getResourceType();
      $this->showMessage(200,'success',$result);
    }

    /**
     * @描述：收藏/取消资源
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：status[int] Y 设置收藏状态 1--收藏 2--取消收藏
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function favorResource()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $status = getParameter('status','int');
        if(FAVOR_STATUS == $status)
        {
            $result = $this->model->addResourceCollect(array('create_at'=>time(),'resource_id' => $id , 'role' => $role-ROLE_NUMBER , 'user_id' => $userId));
            if($result)
                $this->showMessage(200,'收藏成功',array());
            else
                $this->showMessage(500,'收藏失败',array());
        }
        else
        {
            $result = $this->model->deleteResourceCollect($id,$role-ROLE_NUMBER,$userId);
            if($result)
                $this->showMessage(200,'收藏成功',array());
            else
                $this->showMessage(500,'取消收藏',array());
        }
    }
    /**
     * @描述：根据学科年级查询分册（根据资源反查）
     * @参数：courseId[int] Y 学科ID
     * @参数：gradeId[int] Y 年级ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getETextBookByCourseGrade()
    {
        $courseId =getParameter('courseId','int');
        $gradeId =getParameter('gradeId','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $data = $this->model->getETextBookByCourseGrade($courseId,$gradeId);
        $this->showMessage(200,'success',$data);
    }
    /**
     * @描述：根据父级知识点查本级知识点
     * @参数：id[int] Y 知识点ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getKnowledgePointByParentPoint()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $this->ajaxReturn(array('status' => 200,'message' => 'success','result' => $this->model->getBriefKnowledgePointByParentId($id)));
    }

    /**
     * @描述：根据分册查章
     * @参数：id[int] Y 分册ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getChapterByTextbookId()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = $this->model->getChapterByTextbook($id);
        //$result = array_column($result, 'id','name');
        $this->ajaxReturn(array('status' => 200,'message' => 'success','result' => $result));
    }

    private function __getAppVersionIsLower($targetVersion='')
    {

        $agentString = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos(($agentString), 'android') !== false)
        {
            $exp = '/&version=([0-9 | \.]+)/u';
            $version = $this->regExtractOnce($agentString,$exp)[0];
            return version_compare($version,$targetVersion);
        }
        else if(strpos(($agentString), 'iphone') !== false || strpos(($agentString), 'ipad') !== false){
            $exp = '/jingbanyun.?([0-9 | \. ]+)\s+/u';
            $version = $this->regExtractOnce($agentString,$exp)[0];
            if(empty($version))
            {
                $exp = '/&version=([0-9 | \.]+)/u';
                $version = $this->regExtractOnce($agentString,$exp)[0];
            }
            return version_compare($version,$targetVersion);
        }
        return -2;
    }

    /**
     * @描述：获取资源详情页
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function resourceDetails()
    {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $id = getParameter('id','int');
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'JingBanYun') || strpos($user_agent, 'jingbanyun')) {
            $flag = 2;//如果使用京版云APP扫描的随便赋值只要不是1就好
        }else{
            $flag = getParameter('flag','int',false);
        }


        $isShare = 0;
        if(1 != $flag) {
            $userId = getParameter('userId', 'int');
            $role = getParameter('role', 'int');
        }
        else
        {
            if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
                redirect('/index.php?m=Home&c=BjResource&a=bjResourceDetails&id='.$id.'&from=share');
            }
            $userId = 0;
            $role = ROLE_TEACHER;
            $isShare = 1;
        }
        $resourceDetails = $this->model->getResourceDetailInfoWithoutMulti($id,$role,$userId);

        if(!($resourceDetails[0]['putaway_status'] == PUTAWAY && $resourceDetails[0]['status'] == APPROVE)) {
            die("<img src=/".__ROOT__."./Public/img/xiajia.png style='position: fixed; top: 0; bottom: 0; left: 0; right: 0; margin: auto'>");
        }

        $this->model->updateResourceFollowNum($id);
        $contactInfo = $this->model->getResourceContactFiles($id);
        if($resourceDetails[0]['resource_type'] != 1) //NOBOOK 万邦
        {
            $url = $contactInfo[0]['resource_path'];

            if(false === strpos($contactInfo[0]['resource_path'],'http://'))
                $url = 'http://'.$url;
            $token = getParameter('token','str',false);
            $time = getParameter('time','str',false);
            if($token)
                $url.='&token='.$token;
            if($time)
                $url.='&time'.$time;
            header('Location:'.$url);
            return;
        }

        $recommendInfo = $this->model->getResourceContactRecommend($id);
        $orderInfo = D('Order_info')->getPaymentOrderResource($id, $userId, $role);

        $controller = new \Home\Controller\CommonController();
        $orderStatus = $controller->returnStatus($id,$userId,$role);

        $this->assign('resourceDetails',$resourceDetails);
        $this->assign('contactInfo',$contactInfo);
        $this->assign('recommendInfo',$recommendInfo);
        $this->assign('orderStatus',$orderStatus);
        $this->assign('id',$id);
        $this->assign('orderInfo',$orderInfo);
        $this->assign('userId',$userId);
        $this->assign('role',$role);
        $this->assign('isShare',$isShare);
        $url = str_replace('flag=1','',WEB_URL.$_SERVER['REQUEST_URI']);
        $this->assign('urldata',"http://".$url );
        $subResourceIdList = json_decode(GUOXUE_SUBRESOURCE_IDLIST,true);
        if($id == GUOXUE_ID || in_array($id,$subResourceIdList)){
            if($id != GUOXUE_ID){
                //获取主套餐是否已经购买
                $result = $this->model->getResourceDetailInfoWithoutMulti(GUOXUE_ID, $role, $userId);
                $is_allowed_browse = $controller->returnStatus(GUOXUE_ID, $userId, $role); //调公共的方法判断状态
                $this->assign('main_is_allowed_browse', $is_allowed_browse);
                $this->assign('main_data', $result);
            }
            else{
                $is_allowed_browse_array = [];
                foreach($subResourceIdList as $key=>$id){
                    $is_allowed_browse_array[] = $controller->returnStatus($id, $userId, $role); //调公共的方法判断状态
                }
                $this->assign('is_allowed_browse_array', $is_allowed_browse_array);
            }
            $this->assign('subResourceIdList',$subResourceIdList);
            $this->display('resourceSinology');
        }
        else {
            if($this->__getAppVersionIsLower('2.9.0') == -1) {
                if($resourceDetails[0]['file_type'] == 'mixed'){
                    $this->display_nocache();
                }
                else if ($resourceDetails[0]['file_type'] != 'ppt' && $resourceDetails[0]['file_type'] != 'word' && $resourceDetails[0]['file_type'] != 'pdf')
                    $this->display_nocache();
                else
                    $this->display_nocache('resourceDetailsOld');
            }else{
                $this->display_nocache('resourceDetailsUnified');
            }
        }



    }

    /**
     * @描述：获取资源详情页
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getresourceDetails()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $resourceDetails = $this->model->ResourceDetailInfoWithoutMulti($id,$role,$userId);
        $this->ajaxReturn(array('status' => 200,'message' => 'success','result' => $resourceDetails));

    }

    /**
     * @描述：获取资源下单信息
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getresourceDetailspc()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $resourceDetails = $this->model->ResourceDetailInfoWithoutMulti($id,$role,$userId);
        $this->ajaxReturn(array('status' => 200,'message' => 'success','result' => $resourceDetails));

    }

    /**
     * @描述：获取资源下载信息
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：pageIndex[int] Y 页码
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResourceDownloadInfo()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $pageIndex = getParameter('pageIndex','int');
        $pageSize = 50;
        $resourceInfo = $this->model->getResourceDetailInfo($id,$role,$userId);
        $resourceDetails = $this->model->getResourceContactFiles($id,$count,$pageIndex,$pageSize);
        $convertArray = array(
            array('id'=>TYPE_FIELD),array('id'),
            array('file_name'=> TYPE_FIELD),array('title'),
            array('vid' =>TYPE_FIELD) ,array('vid'),
            array('vid_fullsize' =>TYPE_FIELD) ,array('size')
        );

        function formatBytes($size) {
            $units = array(' B', ' KB', ' MB', ' GB', ' TB');
            for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
            return round($size, 2).$units[$i];
        }

        $data = fieldsCompose($resourceDetails,$convertArray);

        $vidList = array_column($data,'vid');
        $vids=implode(',',$vidList);
        $url="http://v.polyv.net/uc/video/info?vids=$vids";
        $content = file_get_contents($url);
        $vidData = json_decode($content);
        $imgPath = C('oss_path').$resourceInfo['img'];
        for($i=0;$i<sizeof($data);$i++)
        {
            if($vidData[$i]->filesize2)
            {
                $data[$i]['size'] =  ($vidData[$i]->filesize2);
                $data[$i]['level'] = 2;
            }
            else if($vidData[$i]->filesize1 != 0)
            {
                $data[$i]['size'] =  ($vidData[$i]->filesize1);
                $data[$i]['level'] = 1;
            }
            else
            {
                if(empty($data[$i]['size'])) {
                    $url = "http://v.polyv.net/uc/services/rest?method=getById&readtoken=" . C('BLWS_CONFIG')['READ_TOKEN'] . "&vid={$vidList[$i]}";
                    $content = file_get_contents($url);
                    $vidDataInfo = json_decode($content, true);
                    $data[$i]['size'] = ($vidDataInfo['data'][0]['source_filesize']);
                    if(!empty($data[$i]['size']))
                    M()->execute('UPDATE knowledge_resource_file_contact set vid_fullsize = '.$data[$i]['size'].' WHERE id='.$data[$i]['id']);
                }
                $data[$i]['level'] = 1;
            }
            $data[$i]['img'] = $imgPath;
        }

        $this->ajaxReturn(array('status' => 200,'message'=>'success','des'=>$resourceInfo['description'],'title'=> $resourceInfo['resource_name'],'data'=>$data,'count'=>$count,'img'=>$imgPath));
    }

    /**
     * @描述：获取我收藏的京版资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：keyword[string] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] Y 学期
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
        $pageIndex =getParameter('pageIndex','int');
        $keyword =getParameter('keyword','str',false);
        $textbookId = getParameter('textbook_id','int',false);
        $type = getParameter('type','str',false);
        $userId = getParameter('user_id','int');
        $role = getParameter('role','int');
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
            'school_term' => $textbookId,
            'type' => $type,
            'keyword'   => $keyword
        );
        $result = $this->model->getCollectedResourceList($queryParameters,$pageIndex,$this->getPageSize(),$userId,$role);
        for($i=0;$i<sizeof($result);$i++)
        {
            $result[$i]['url'] = 'http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.APIINTERFACE_DIR . '/KnowledgeResource/resourceDetails?id='.$result[$i]['id'];
        }
        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }

    public function share() {
        $id =getParameter('id','int',false);
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        if(!empty($userId) && !empty($role) && ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) )
            header('Location:/ApiInterface/Version1_1/ExpertInformation/informationDetails?id='.$id."&userId=$userId&role=$role");
        else {
            if($role == 5) //youke
            {
                echo "<script>alert('请登录京版云后查看');history.go(-1);</script>";
                exit;
            }
            $this->assign('id', $id);
            $this->assign('WEB_URL', WEB_URL);

            $is_m = isMobile();
            if ($is_m == false) {
                header('Location:/index.php?m=Home&c=ExpertInformation&a=expertInformationDetails&id=' . $id);
            } else {
                $this->display();
            }
        }

    }

    /**
     * @描述：查询学科（根据资源反查）
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResourceCourse()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = $this->model->getResourceCourse();
        $this->ajaxReturn(array('status'=>200,'result'=>$result));
    }

    /**
     * @描述：根据学科查询年级（根据资源反查）
     * @参数：courseId[int] Y 学科ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getGradeByCourse()
    {
        $courseId =getParameter('courseId','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = $this->model->getGradeByCourse($courseId);
        $this->ajaxReturn(array('status'=>200,'result'=>$result));
    }

    /**
    * @描述：根据学科年级查询分册（根据资源反查）
    * @参数：courseId[int] Y 学科ID
    * @参数：gradeId[int] Y 年级ID
    * @参数：role[int] Y 角色ID
    * @参数：userId[int] Y 用户ID
    * @返回值：array(
    *    status 状态码
    *    result 结果数组
    * )
    */
    public function getTextbookByCourseGrade()
    {
        $courseId =getParameter('courseId','int');
        $gradeId =getParameter('gradeId','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = D('Biz_textbook')->getTextbookByCourse2($courseId,$gradeId);
        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }
}
?>