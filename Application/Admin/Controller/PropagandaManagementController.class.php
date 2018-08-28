<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
define('INPUT_CATEGORY_STARTENDTIME',1);
define('INPUT_CATEGORY_RADIOSELECT',2);
define('INPUT_CATEGORY_INPUT',3);
define('INPUT_CATEGORY_UPLOAD',4);
define('INPUT_CATEGORY_HIDDEN',5);


define('TYPE_AD',1);
define('TYPE_LUNBO',2);
define('TYPE_MASK',3);

define('TD_CATEGORY_IMG',1);
define('TD_CATEGORY_STR',2);

define('MAX_LUNBO_COUNT',10);
define('PROPAGANDA_STATUS_DECLINED',1);       //已拒绝
define('PROPAGANDA_STATUS_WAITFORVERIFY',2);  //待审核
define('PROPAGANDA_STATUS_VERIFIED',3);       //已审核
define('PROPAGANDA_STATUS_OVER',4);      //已结束
define('PROPAGANDA_STATUS_OFFSHELF',5);       //已下架
define('PROPAGANDA_STATUS_PUBLISHING',6);      //发布中
define('PROPAGANDA_STATUS_PUBLISHED',7);      //已发布


class propagandaManagementController extends Controller{
    private $mainTitle;
    private $subTitle;
    private $formParas;
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));

    }
    private function publicAssign()
    {
        $this->assign('mainTitle', $this->mainTitle);
        $this->assign('subTitle', $this->subTitle);
        $this->assign('formAction',ACTION_NAME );
        $this->assign('formParas',$this->formParas);
    }
    private function composeParas($name,$formName,$category,$reg,$value)
    {
        $this->formParas[] = array('name' => $name,'formName'=>$formName,'category'=>$category,'reg'=>$reg,'value'=>$value);
    }
    private function getParas($data,$field,$callback)
    {
       switch(gettype($field))
       {
           case 'array':for($i=0;$i<sizeof($field);$i++)
                         {
                            $valueReturn[] = $data[$field[$i]];
                         }
                         if(gettype($callback)=='object')
                         {
                             for($i=0;$i<sizeof($valueReturn);$i++)
                             {
                                 $valueReturn[$i] = $callback($valueReturn[$i]);
                             }
                         }
                        break;
           case 'integer':
           case 'string':$valueReturn = $data[$field];
                          break;

           default:break;
       }

       return $valueReturn;
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
    private function transData($data,$header)
    {
        $dataSelect = array();
        $returnResult = array();
        foreach($header as $key=>$val)
        {
            if(gettype($val['valueCallback']) == 'object')
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
                        $transedData = $val['valueCallback']($unConvertData);
                        $lineData = array_merge($lineData,array($transedData));
                    }
                    $dataSelect[] = $lineData;
                }
                else
                {
                    $selectData = array_column($data,$val['field']);
                    for($i=0;$i<sizeof($selectData);$i++)
                    {
                        $selectData[$i] = $val['valueCallback']($selectData[$i]);

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
            $returnResult[] = array('id'=>$data[$i]['id'],'data'=>$dataSelect[$i],'status'=>$data[$i]['status']);
        }
        return $returnResult;
    }
    private function getAddDataParas()
    {
        $databaseData = array();
        foreach ($this->formParas as $key=>$val)
        {
            $currentPara = '';
            if(false !== strpos($val['formName'],'[]')){
                $val['formName'] = str_replace('[]','',$val['formName']);
                switch($val['type'])
                {
                    case 'str' : $currentPara = getParameter($val['formName'],'sArr',$val['required']);
                        break;
                    case 'int' : $currentPara = getParameter($val['formName'],'iArr',$val['required']);
                        break;
                    default:$this->showMessage(500,$val['formName'].'parameter type error',array());
                }
            }

            else
            {
                switch($val['type'])
                {
                    case 'str' : $currentPara = htmlspecialchars_decode(getParameter($val['formName'],'str',$val['required'],2));
                        break;
                    case 'int' : $currentPara = getParameter($val['formName'],'int',$val['required']);
                        break;
                    default:$this->showMessage(500,$val['formName'].' parameter type error',array());
                }
            }
            if(gettype($val['postCallBack']) == 'object')
            {
                $callbackValue = $val['postCallBack']($currentPara);
                if(null != $callbackValue)
                    $databaseData += $callbackValue;
            }
            else
                $databaseData+=array($val['formName']=>($currentPara));

        }
        return $databaseData;
    }
    public function advertisementMgmtList(){

        $model = D('PropagandaManagement');
        $header = array(
            array(
                'name'=>'上传图片',
                'category'=>TD_CATEGORY_IMG,
                'field'=>'file_path',
                'valueCallback'=>function($data){return C('oss_path').$data;}
            ),
            array(
                'name'=>'跳转地址',
                'category'=>TD_CATEGORY_STR,
                'field'=>'url',
            ),

            array(
                'name'=>'展示日期',
                'category'=>TD_CATEGORY_STR,
                'field'=>'starttime,endtime',
                'valueCallback'=>function($data){return date('Y-m-d', $data[0]).'至'.date('Y-m-d', $data[1]);}
            ),
            array(
                'name'=>'展示时长(s)',
                'category'=>TD_CATEGORY_STR,
                'field'=>'display_time'
            ),
            array(
                'name'=>'展示方式',
                'category'=>TD_CATEGORY_STR,
                'field'=>'display_method',
                 'valueCallback'=>function($data){if($data==1) return '每次打开时展示'; else return '每日首次打开时展示';}
            ),
        );
        $pageIndex = getParameter('p','int',false);
        $filterOption = getParameter('filterOption','int',false);


        if(!$pageIndex)
            $pageIndex = 1;

        if($filterOption)
         $where['status'] =  $filterOption;

        $data = $model->getDataList(TYPE_AD, 0, $pageIndex , C('PAGE_SIZE_FRONT'), $count,$where);
        $result = $this->transData($data,$header);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();


        $tabList = array(array(
            'name'=>'广告管理',
            'filterOption' => $filterOption,
            'additionalHint' => '广告数量：'.sizeof($result),
            "header" => $header,
            "data" => $result,
            "page" => $show
        ));
        $this->assign('activePage',1);
        $this->assign('addEditUrl','advertisementMgmt');
        $this->assign('tabList',$tabList);
        $this->assign('tabList1',$tabList);
        $this->assign('changeStatusURL','updateStatus');
        $this->assign('deleteURL','deleteResource');
        $this->assign('actionName',ACTION_NAME);
        $this->assign('moduleName',MODULE_NAME);
        $this->assign('controllerName',CONTROLLER_NAME);
        $this->display_nocache('Common/singlePageList');
    }
    public function maskMgmtList(){

        $model = D('PropagandaManagement');
        $header = array(
            array(
                'name'=>'上传图片',
                'category'=>TD_CATEGORY_IMG,
                'field'=>'file_path',
                'valueCallback'=>function($data){return C('oss_path').$data;}
            ),
            array(
                'name'=>'跳转地址',
                'category'=>TD_CATEGORY_STR,
                'field'=>'url',
            ),

            array(
                'name'=>'展示日期',
                'category'=>TD_CATEGORY_STR,
                'field'=>'starttime,endtime',
                'valueCallback'=>function($data){return date('Y-m-d', $data[0]).'至'.date('Y-m-d', $data[1]);}
            ),
            array(
                'name'=>'展示方式',
                'category'=>TD_CATEGORY_STR,
                'field'=>'display_method',
                'valueCallback'=>function($data){if($data==1) return '每次打开时展示'; else return '每日首次打开时展示';}
            ),
        );
        $pageIndex = getParameter('p','int',false);
        $filterOption = getParameter('filterOption','int',false);


        if(!$pageIndex)
         $pageIndex = 1;

        if($filterOption)
         $where['status'] =  $filterOption;

        $data = $model->getDataList(TYPE_MASK, 0, $pageIndex , C('PAGE_SIZE_FRONT'), $count,$where);
        $result = $this->transData($data,$header);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();


        $tabList = array(array(
            'name'=>'蒙层管理',
            'filterOption' => $filterOption,
            'additionalHint' => '蒙层数量：'.sizeof($result) ,
            "header" => $header,
            "data" => $result,
            "page" => $show
        ));
        $this->assign('activePage',1);
        $this->assign('addEditUrl','maskMgmt');
        $this->assign('tabList',$tabList);
        $this->assign('tabList1',$tabList);
        $this->assign('changeStatusURL','updateStatus');
        $this->assign('deleteURL','deleteResource');
        $this->assign('actionName',ACTION_NAME);
        $this->assign('moduleName',MODULE_NAME);
        $this->assign('controllerName',CONTROLLER_NAME);
        $this->display_nocache('Common/singlePageList');
    }
    public function PropagandaManagement(){

      $model = D('PropagandaManagement');
      $header = array(
        array(
            'name'=>'上传图片',
            'category'=>TD_CATEGORY_IMG,
            'field'=>'file_path',
            'valueCallback'=>function($data){return C('oss_path').$data;}
        ),
          array(
              'name'=>'跳转地址',
              'category'=>TD_CATEGORY_STR,
              'field'=>'url',
          ),
          array(
              'name'=>'播放顺序',
              'category'=>TD_CATEGORY_STR,
              'field'=>'sort'
          ),
          array(
              'name'=>'时间',
              'category'=>TD_CATEGORY_STR,
              'field'=>'starttime,endtime',
              'valueCallback'=>function($data){return date('Y-m-d', $data[0]).'至'.date('Y-m-d', $data[1]);}
          )
      );
        $pageIndex = getParameter('p','int',false);
        $cat = getParameter('cat','int',false);
        $filterOption = getParameter('filterOption','int',false);


        if(!$cat)
            $cat = 1;

        if(!$pageIndex)
            $pageIndex = 1;

       for($i=0;$i<3;$i++) {
           $thisPage= ($cat == $i+1) ? $pageIndex : 1;
           $where = array();
           if($cat == $i+1 && $filterOption)
           {
               $where['status'] =  $filterOption;
               $assignFilter[$cat] = $filterOption;
           }
           $data = $model->getDataList(TYPE_LUNBO, $i+1, $thisPage, C('PAGE_SIZE_FRONT'), $count,$where);
           $result[] = $this->transData($data,$header);
           $_GET['p'] =  $thisPage;
           $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
           $Page->parameter['cat'] = $i+1;
           $show[] = $Page->show();
       }

      $tabList = array(array(
          'name'=>'励耘圈',
          'filterOption' => $assignFilter[1],
          'additionalParas' => array(array(
               'name'=>'播放时间间隔',
               'unit' => 's',
               'inputType' => 'number',
               'min' => 1,
               'max' => '',
               'type' => 'int',
               'reg' => '',
               'formName' => '1',
               'url' => 'updateTimeGap',
               'value' => $model->getTimeLength(1),
               'category' => INPUT_CATEGORY_INPUT)
          ),
          'additionalHint' => '图片张数：'.sizeof($result[0]).'张(不超过'.MAX_LUNBO_COUNT.'张)',
          "header" => $header,
          "data" => $result[0],
          "page" => $show[0]
      ),array(
          'name'=>'教学+',
          'filterOption' => $assignFilter[2],
          'additionalParas' => array(array(
              'name'=>'播放时间间隔',
              'unit' => 's',
              'inputType' => 'number',
              'min' => 1,
              'max' => '',
              'type' => 'int',
              'reg' => '',
              'formName' => '2',
              'url' => 'updateTimeGap',
              'value' => $model->getTimeLength(2),
              'category' => INPUT_CATEGORY_INPUT)
          ),
          'additionalHint' => '图片张数：'.sizeof($result[1]).'张(不超过'.MAX_LUNBO_COUNT.'张)',
          "header" => $header,
          "data" => $result[1],
          "page" => $show[1]
      ),array(
          'name'=>'班级行',
          'filterOption' => $assignFilter[3],
          'additionalParas' => array(array(
              'name'=>'播放时间间隔',
              'unit' => 's',
              'inputType' => 'number',
              'min' => 1,
              'max' => '',
              'type' => 'int',
              'reg' => '',
              'formName' => '3',
              'url' => 'updateTimeGap',
              'value' => $model->getTimeLength(3),
              'category' => INPUT_CATEGORY_INPUT)
          ),
          'additionalHint' => '图片张数：'.sizeof($result[2]).'张(不超过'.MAX_LUNBO_COUNT.'张)',
          "header" => $header,
          "data" => $result[2],
          "page" => $show[2]
      ));
        $this->assign('activePage',$cat);
        $this->assign('addEditUrl','creatPropagandaMgmt');
        $this->assign('tabList',$tabList);
        $this->assign('tabList1',$tabList);
        $this->assign('changeStatusURL','updateStatus');
        $this->assign('deleteURL','deleteResource');
        $this->assign('actionName',ACTION_NAME);
        $this->assign('moduleName',MODULE_NAME);
        $this->assign('controllerName',CONTROLLER_NAME);
        $this->display_nocache('Common/singlePageList');
    }


    public function creatPropagandaMgmt(){

        $id = getParameter('id','int',false);
        $model = D('PropagandaManagement');
        $result = array();

        if(empty($_POST) && $id)
            $result = $model->getData($id);

        $this->formParas[] = array(
            'name' => '日期',
            'formName' => 'startEndTime[]',
            'category' => INPUT_CATEGORY_STARTENDTIME,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,array('starttime','endtime'),function($obj){if($obj!=0) return date("Y-m-d",$obj);}),
            'required' => true,
            'postCallBack' => function($obj){return array('starttime'=> strtotime($obj[0]),'endtime'=>strtotime($obj[1])+86399);}
        );
        if(!$id)
            $catValue = getParameter('cat','int');
        else
            $catValue = $this->getParas($result,'model_type');
        $this->formParas[] = array(
            'formName' => 'cat',
            'category' => INPUT_CATEGORY_HIDDEN,
            'type' => 'int',
            'value' => $catValue,
            'postCallBack' => function($obj){return array('model_type'=>$obj);}
        );

        $this->formParas[] = array(
            'name' => '跳转方式',
            'formName' => 'jumpMethod',
            'category' => INPUT_CATEGORY_RADIOSELECT,
            'reg' => '',
            'type' => 'int',
            'inputType' => 'number',
            'radioValue' => array( array('name'=>'webview','value'=>1),array('name'=>'原生','value'=>2)),
            'value' => $this->getParas($result,'url_jump_type'),
            'required' => true,
            'postCallBack' => function($obj){return array('url_jump_type'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '展示文字',
            'inputType' => 'text',
            'type' => 'str',
            'formName' => 'title',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'value' => $this->getParas($result,'title'),
            'required' => true,
            'postCallBack' => function($obj){return array('title'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '展示顺序',
            'inputType' => 'number',
            'min' => 1,
            'max' => '',
            'type' => 'int',
            'formName' => 'sort',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '/^[0-9]*[1-9][0-9]*$/',
            'value' => $this->getParas($result,'sort'),
            'required' => true,
            'postCallBack' => function($obj){return array('sort'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '首页面跳转地址',
            'inputType' => 'text',
            'formName' => 'mainAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'url'),
            'required' => false,
            'postCallBack' => function($obj){return array('url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '次级页面跳转地址',
            'inputType' => 'text',
            'formName' => 'subAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'child_page_url'),
            'required' => false,
            'postCallBack' => function($obj){return array('child_page_url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '上传图片',
            'subtitle' => '*上传宽/高比例为1.8-2.0的图片 推荐尺寸：400px*200px',
            'formName' => 'imageUpload',
            'category' => INPUT_CATEGORY_UPLOAD,
            'ext' => 'jpg,png',
            'imageRatio' => array(1.8,2.0),
            'type' => 'str',
            'multiple' => false,
            'value' => array('file_path'=>$this->getParas($result,'file_path'),'file_name'=>$this->getParas($result,'file_name')),
            'required' => true,
            'postCallBack' => function($obj){$obj = json_decode($obj);
                $objArray = array();
                foreach($obj as $key=>$val)
                {
                    $fileName = $val->file_name;
                    $objArray[] = array('file_name'=>$fileName,'file_path'=>$val->file_path);
                }
                return array('files'=>$objArray);}
        );
        if($_POST)
        {

            $paras = $this->getAddDataParas();
            $paras['file_path'] = $paras['files'][0]['file_path'];
            $paras['file_name'] = $paras['files'][0]['file_name'];
            $paras['status'] = PROPAGANDA_STATUS_WAITFORVERIFY;
            $paras['type'] = TYPE_LUNBO;
            unset($paras['files']);
            if($id) //edit
            {
                $data = $model->getData($id);
                if($data['status'] == PROPAGANDA_STATUS_PUBLISHED)
                    $paras['status'] = PROPAGANDA_STATUS_OFFSHELF;
                $result = $model->updateData($id,$paras,$errorInfo);
                if(false !== $result)
                    $this->showMessage(200,'success',array());
                else
                    $this->showMessage(500,$errorInfo,array());
            }
            else {
                $data = $model->getDataList(TYPE_LUNBO, $paras['model_type'], 1, C('PAGE_SIZE_FRONT'), $count);
                if($count >= MAX_LUNBO_COUNT)
                    $this->showMessage(500,'轮播图数量超出'.MAX_LUNBO_COUNT .'张的限制，无法添加轮播图',array());
                $paras['create_at'] = time();

                $result = $model->addData(0,$paras,$errorInfo);
                if($result)
                    $this->showMessage(200,'success',array());
                else
                    $this->showMessage(500,$errorInfo,array());
            }
        }
        else
        {

            $this->mainTitle = "轮播图管理";
            $this->subTitle = array("宣传管理",'轮播图管理');
            $this->publicAssign();
            $this->assign('id',$id);
            $this->assign('isEdit',1);
            $this->display('Common/singlePageDetail');
        }
    }
    public function maskMgmt(){
        $id = getParameter('id','int',false);
        $model = D('PropagandaManagement');
        $result = array();

        if(empty($_POST) && $id)
            $result = $model->getData($id);

        $this->formParas[] = array(
            'name' => '日期',
            'formName' => 'startEndTime[]',
            'category' => INPUT_CATEGORY_STARTENDTIME,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,array('starttime','endtime'),function($obj){if($obj!=0) return date("Y-m-d",$obj);}),
            'required' => true,
            'postCallBack' => function($obj){return array('starttime'=> strtotime($obj[0]),'endtime'=>strtotime($obj[1])+86399);}
        );
        $this->formParas[] = array(
            'name' => '',
            'formName' => 'displayMethod',
            'category' => INPUT_CATEGORY_RADIOSELECT,
            'reg' => '',
            'type' => 'int',
            'inputType' => 'number',
            'radioValue' => array( array('name'=>'每次打开时展示','value'=>1),array('name'=>'每日首次打开时展示','value'=>2)),
            'value' => $this->getParas($result,'display_method'),
            'required' => true,
            'postCallBack' => function($obj){return array('display_method'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '跳转方式',
            'formName' => 'jumpMethod',
            'category' => INPUT_CATEGORY_RADIOSELECT,
            'reg' => '',
            'type' => 'int',
            'inputType' => 'number',
            'radioValue' => array( array('name'=>'webview','value'=>1),array('name'=>'原生','value'=>2)),
            'value' => $this->getParas($result,'url_jump_type'),
            'required' => true,
            'postCallBack' => function($obj){return array('url_jump_type'=>$obj);}
        );

        $this->formParas[] = array(
            'name' => '首页面跳转地址',
            'inputType' => 'text',
            'formName' => 'mainAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'url'),
            'required' => false,
            'postCallBack' => function($obj){return array('url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '次级页面跳转地址',
            'inputType' => 'text',
            'formName' => 'subAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'child_page_url'),
            'required' => false,
            'postCallBack' => function($obj){return array('child_page_url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '上传图片',
            'subtitle' => '*上传宽/高比例为0.665-0.735的图片 推荐尺寸：490px*700px',
            'formName' => 'imageUpload',
            'category' => INPUT_CATEGORY_UPLOAD,
            'ext' => 'jpg,png',
            'imageRatio' => array(0.665,0.735),
            'type' => 'str',
            'multiple' => false,
            'value' => array('file_path'=>$this->getParas($result,'file_path'),'file_name'=>$this->getParas($result,'file_name')),
            'required' => true,
            'postCallBack' => function($obj){$obj = json_decode($obj);
                $objArray = array();
                foreach($obj as $key=>$val)
                {
                    $fileName = $val->file_name;
                    $objArray[] = array('file_name'=>$fileName,'file_path'=>$val->file_path);
                }
                return array('files'=>$objArray);}
        );
        if($_POST)
        {

            $paras = $this->getAddDataParas();
            $paras['file_path'] = $paras['files'][0]['file_path'];
            $paras['file_name'] = $paras['files'][0]['file_name'];
            $paras['status'] = PROPAGANDA_STATUS_WAITFORVERIFY;
            $paras['type'] = TYPE_MASK;
            unset($paras['files']);
            if($id) //edit
            {
                $data = $model->getData($id);
                if($data['status'] == PROPAGANDA_STATUS_PUBLISHED)
                    $paras['status'] = PROPAGANDA_STATUS_OFFSHELF;
                $result = $model->updateData($id,$paras,$errorInfo);
                if(false !== $result)
                    $this->showMessage(200,'success',array());
                else
                    $this->showMessage(500,$errorInfo,array());
            }
            else {
                $paras['create_at'] = time();

                $result = $model->addData(0,$paras,$errorInfo);
                if($result)
                    $this->showMessage(200,'success',array());
                else
                    $this->showMessage(500,$errorInfo,array());
            }
        }
        else
        {

            $this->mainTitle = "蒙层管理";
            $this->subTitle = array("宣传管理",'蒙层管理');
            $this->publicAssign();
            $this->assign('id',$id);
            $this->assign('isEdit',1);
            $this->display('Common/singlePageDetail');
        }
    }
    public function advertisementMgmt(){
      $id = getParameter('id','int',false);
      $model = D('PropagandaManagement');
      $result = array();

      if(empty($_POST) && $id)
        $result = $model->getData($id);

        $this->formParas[] = array(
            'name' => '日期',
            'formName' => 'startEndTime[]',
            'category' => INPUT_CATEGORY_STARTENDTIME,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,array('starttime','endtime'),function($obj){if($obj!=0) return date("Y-m-d",$obj);}),
            'required' => true,
            'postCallBack' => function($obj){return array('starttime'=> strtotime($obj[0]),'endtime'=>strtotime($obj[1])+86399);}
        );
        $this->formParas[] = array(
            'name' => '',
            'formName' => 'displayMethod',
            'category' => INPUT_CATEGORY_RADIOSELECT,
            'reg' => '',
            'type' => 'int',
            'inputType' => 'number',
            'radioValue' => array( array('name'=>'每次打开时展示','value'=>1),array('name'=>'每日首次打开时展示','value'=>2)),
            'value' => $this->getParas($result,'display_method'),
            'required' => true,
            'postCallBack' => function($obj){return array('display_method'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '展示时长',
            'formName' => 'exhibitTime',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '/^[0-9]*[1-9][0-9]*$/',
            'type' => 'int',
            'unit' => 's',
            'min' => 1,
            'inputType' => 'number',
            'value' => $this->getParas($result,'display_time'),
            'required' => true,
            'postCallBack' => function($obj){return array('display_time'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '跳转方式',
            'formName' => 'jumpMethod',
            'category' => INPUT_CATEGORY_RADIOSELECT,
            'reg' => '',
            'type' => 'int',
            'inputType' => 'number',
            'radioValue' => array( array('name'=>'webview','value'=>1),array('name'=>'原生','value'=>2)),
            'value' => $this->getParas($result,'url_jump_type'),
            'required' => true,
            'postCallBack' => function($obj){return array('url_jump_type'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '首页面跳转地址',
            'inputType' => 'text',
            'formName' => 'mainAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'url'),
            'required' => false,
            'postCallBack' => function($obj){return array('url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '跳转参数',
            'inputType' => 'text',
            'formName' => 'jumpPara',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'child_page_url'),
            'required' => false,
            'postCallBack' => function($obj){return array('child_page_url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '次级页面跳转地址',
            'inputType' => 'text',
            'formName' => 'subAddress',
            'category' => INPUT_CATEGORY_INPUT,
            'reg' => '',
            'type' => 'str',
            'value' => $this->getParas($result,'web_url'),
            'required' => false,
            'postCallBack' => function($obj){return array('web_url'=>$obj);}
        );
        $this->formParas[] = array(
            'name' => '上传图片',
            'subtitle' => '*上传宽/高比例为0.56:0.57的图片 推荐尺寸：750px*1334px',
            'formName' => 'imageUpload',
            'category' => INPUT_CATEGORY_UPLOAD,
            'ext' => 'jpg,png',
            'imageRatio' => array(0.56,0.57),
            'type' => 'str',
            'multiple' => false,
            'value' => array('file_path'=>$this->getParas($result,'file_path'),'file_name'=>$this->getParas($result,'file_name')),
            'required' => true,
            'postCallBack' => function($obj){$obj = json_decode($obj);
                                              $objArray = array();
                                              foreach($obj as $key=>$val)
                                              {
                                                  $fileName = $val->file_name;
                                                  $objArray[] = array('file_name'=>$fileName,'file_path'=>$val->file_path);
                                              }
                                              return array('files'=>$objArray);}
        );
      if($_POST)
      {

         $paras = $this->getAddDataParas();
         $paras['file_path'] = $paras['files'][0]['file_path'];
         $paras['file_name'] = $paras['files'][0]['file_name'];
         $paras['status'] = PROPAGANDA_STATUS_WAITFORVERIFY;
         $paras['type'] = TYPE_AD;
         unset($paras['files']);
         if($id) //edit
         {
             $data = $model->getData($id);
             if($data['status'] == PROPAGANDA_STATUS_PUBLISHED)
                 $paras['status'] = PROPAGANDA_STATUS_OFFSHELF;
           $result = $model->updateData($id,$paras,$errorInfo);
           if(false !== $result)
               $this->showMessage(200,'success',array());
           else
               $this->showMessage(500,$errorInfo,array());
         }
         else {
           $paras['create_at'] = time();

           $result = $model->addData(0,$paras,$errorInfo);
             if($result)
                 $this->showMessage(200,'success',array());
             else
                 $this->showMessage(500,$errorInfo,array());
         }
      }
      else
      {

          $this->mainTitle = "广告管理";
          $this->subTitle = array("宣传管理",'广告管理');
          $this->publicAssign();
          $this->assign('id',$id);
          $this->assign('isEdit',1);
          $this->display('Common/singlePageDetail');
      }


    }
    /*
    * ajax interfaces
    */
    public function updateTimeGap()
    {
        $index = getParameter('field','int');
        $value = getParameter('value','int');
        $result = D('PropagandaManagement')->updateTimeLength($index,$value);
        if(false !== $result)
            $this->showMessage(200,'更新成功',array());
        else
            $this->showMessage(500,'更新失败',array());
    }
    public function updateStatus()
    {
        $index = getParameter('id','int');
        $value = getParameter('status','int');
        $result = D('PropagandaManagement')->updateStatus($index,$value);
        if(false != $result)
            $this->showMessage(200,'更新成功',array());
        else
            $this->showMessage(500,'更新失败',array());
    }
    public function deleteResource()
    {
        $id = getParameter('id','int');
        $result = D('PropagandaManagement')->deleteData($id);
        if(false != $result)
            $this->showMessage(200,'删除成功',array());
        else
            $this->showMessage(500,'删除失败',array());
    }


}
