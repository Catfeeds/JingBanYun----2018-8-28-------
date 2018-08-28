<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class PageVisitedController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
    
    //分页StartIndex重计算
    private function startIndexFilter($allCount,$pageIndex,$pageSize)
    {
       return $allCount < ($pageIndex -1) * $pageSize ? (max(0,round(($allCount/$pageSize))-1)) * $pageSize :($pageIndex -1) * $pageSize;
    }
    private function getDaysArrayString($startTime,$endTime)
    {
      $returnArray = array();
      for($currentTime=$startTime;$currentTime <= $endTime ;$currentTime += 86400)
      {
          $returnArray[] = $currentTime;
      }
      return '('.implode(',',$returnArray).')';
    }
          
     
    public function pageVisited()
    {
    if(1)
    {
        //Paras Config
        $defaultPageSize = 10;
        $maxPieDataSize = 10;

        $queryParas =  $_GET;
        $currentTime = time();
        $constQueryParasInfo['currentDate'] = date('Y-m-d',$currentTime);
        $constQueryParasInfo['currentDateM1'] = date('Y-m-d',$currentTime-24*3600);
        $constQueryParasInfo['currentDateM7'] = date('Y-m-d',$currentTime-7*24*3600);
        $constQueryParasInfo['currentDateM30'] = date('Y-m-d',$currentTime-30*24*3600);
        $constQueryParasInfo['currentDateM60'] = date('Y-m-d',$currentTime-60*24*3600);

        //sourcetypes viewcounts searchkey
        $pageSize = empty($_GET['pagesize']) ? $defaultPageSize : $_GET['pagesize'];
        $queryParas['pagesize'] = $pageSize;

        $pageIndex = empty($_GET['p']) ? 1 : (int)$_GET['p'];
        $startIndex = ($pageIndex - 1) * $pageSize;

        if(empty($queryParas['starttime']))
            $queryParas['starttime'] = $constQueryParasInfo['currentDate'];
        if(empty($queryParas['endtime']))
            $queryParas['endtime'] = $constQueryParasInfo['currentDate'];
        if(strtotime($queryParas['starttime']) > strtotime($queryParas['endtime']))
            $queryParas['starttime'] = $queryParas['endtime'];

        if(isset($queryParas['role']))
        {
            if($queryParas['role'] == -1)
            unset($queryParas['role']);
            else
            $queryParas['_string'] = 'user_id <> 0';
        }
        $sourceTableSql = $this->getAccessRecordSql('usertables.user_access',$queryParas);


        $countSql = "
         SELECT COUNT(DISTINCT access_url) count FROM $sourceTableSql ";
        $statisticsSql = "SELECT COUNT(DISTINCT role,user_id) as currentvisitors, 
                                 COUNT(access_url) AS currentbrowse,
                                 COUNT(1) / COUNT(DISTINCT user_id, role) AS avgbrowse,
                                 '--' AS holdtime FROM $sourceTableSql";
        $listSql = "SELECT access_url url,COUNT(DISTINCT ip_address) AS ips,COUNT(DISTINCT role,user_id) as visitors, 
                                 COUNT(access_url) AS browsecount,
                                 COUNT(1) / COUNT(DISTINCT user_id, role) AS avgbrowse,
                                 '--' AS holdtime,'--' AS jumprate,'--' AS exitrate,'--' AS newvisitorrate FROM $sourceTableSql GROUP BY access_url ORDER BY browsecount DESC";
        $resultAllCount = M()->query($countSql);
        $resultAllStatistics = M()->query($statisticsSql)[0];
        $startIndex = $this->startIndexFilter($resultAllCount[0]['count'],$pageIndex,$pageSize);
        $resultDetails = M()->query($listSql." LIMIT $startIndex,$pageSize");

        $pieDataSize = min($maxPieDataSize,count($resultDetails));
        for ($i = 0; $i < $pieDataSize; $i++)
        {
            $pieData[$i]['name'] = $resultDetails[$i]['url'];
            $pieData[$i]['value'] = $resultDetails[$i]['browsecount'];
        }
        //calc sum data
        for ($i = 0; $i < $pageSize; $i++)
        {
            foreach($resultDetails[$i] as $key => $value)
            {
                if($value != '--')
                    $sumdata[$key] += $value;
            }
        }

        //avgbrowse newvisitorrate avg recalc
        $sumdata['avgbrowse'] = $sumdata['browsecount'] / $sumdata['visitors'];
        $sumdata['newvisitorrate'] = $sumdata['newvisitorrate'] / count($resultDetails);
        $Page = new \Think\Page($resultAllCount[0]['count'], $queryParas['pagesize']);
        $Page->parameter= $queryParas;
        $show = $Page->show();

        $this->assign('pieData',json_encode($pieData));
        $this->assign('sumData',$sumdata);
        $this->assign('data',$resultDetails);
        $this->assign('dataAll',$resultAllStatistics);
        $this->assign('queryParas',$queryParas);
        $this->assign('page',$show);
        $this->assign('constQueryParasInfo',$constQueryParasInfo);
        $this->assign('nav','用户行为分析');
        $this->assign('subnav','受访页面');
        //var_dump($this->array_keyvalue_urlmerge($queryParas));exit;
    }
    $this->display();
}


    private function array_keyvalue_urlmerge($arr)
        {
            foreach ($arr as $key=>$val)
            {
                $querystr[] = $key.'='.$val;
            }
            return join('&',$querystr);
        }
        private function getAccessRecordSql($tableName,$paras,$filter='')
        {
            $daysArray = $this->getDaysArrayString(strtotime($paras['starttime']),strtotime($paras['endtime']));
            $where[] = " access_date in $daysArray ";//' 1=1 ';
            if(!empty($paras['searchkey']))
            {
                $where[] = ' (access_url like \'%' . $paras['searchkey'] . '%\') ';
            }
            if(!empty($paras['sourcetypes']))
            {
                switch($paras['sourcetypes'])
                {
                    case 'web' : $where[] = " (user_agent like '%Windows%' or user_agent like '%Macintosh%') ";
                        break;
                    case 'ios' : $where[] = " (user_agent like '%iPhone%') ";
                        break;
                    case 'android': $where[] = " (user_agent like '%Android%') ";
                        break;
                }
            }
            if(isset($paras['role'])) {
                if($paras['role'] != 3) //非游客
                    $where[] = "role=" . $paras['role'] . ' AND user_id<> 0';
                else
                    $where[] = "role = 0 AND user_id = 0 ";
            }

            $where = join(' AND ',$where);
            $baseTableSql = " (SELECT * FROM usertables.user_access WHERE $where) ";



            return $baseTableSql .' user_access_ext';

    }
}
