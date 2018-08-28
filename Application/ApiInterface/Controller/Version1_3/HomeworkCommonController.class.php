<?php
namespace ApiInterface\Controller\Version1_3;
use Common\Common\simple_html_dom;
define('FAKE_DATA',1);


class HomeworkCommonController extends PublicController
{

    public $pageSize = 20;
    public $model;
    private $exerciseConfig;
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
        $this->exerciseConfig = require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/createExercise.php');
    }

    function getPageSize()
    {
        return $this->pageSize;
    }
    private function __decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }

    private function __processResult(&$result,$normalIndex,&$newResult = [])
    {
        foreach ($result['data'][$normalIndex]['data'] as $key => $val) {
            if (1) {
                foreach ($val['data'] as $subKey => $subVal) {

                    foreach ($subVal as $fieldName => $fieldValue) {
                        $html = htmlspecialchars_decode($fieldValue);
                        $htmlStr = stripcslashes($this->__decodeUnicode($html));
                        $htmlStr = str_replace('ㄖ', '&nbsp;__________&nbsp; ', $htmlStr);

                        $val['data'][$subKey][$fieldName] = str_replace("\n", '', str_replace("\t", '', $htmlStr));
                        if ($fieldName == 'json_html') {
                            $val['data'][$subKey][$fieldName] = substr($val['data'][$subKey][$fieldName], 1, -1);
                            $html = new simple_html_dom();
                            $html->load($val['data'][$subKey][$fieldName]);
                            do {
                                $preHTML = $html->find('pre', 0);
                                if ($preHTML != null)
                                    $preHTML->tag = 'p';
                            } while ($preHTML != null);
                            $val['data'][$subKey][$fieldName] = $html->save();
                            if($val['data'][$subKey]['topic_type'] == 3) //选择填空
                            {
                                $rightAnswerArray = [];
                                $startFindIndex = 0;
                                do {
                                    $answerHTML = $html->find('.claimChoice',0)->find('li p',$startFindIndex++);
                                    if ($answerHTML != null)
                                        $rightAnswerArray[] = $answerHTML->innertext;
                                } while ($answerHTML != null);
                                $val['data'][$subKey]['answerList'] = $rightAnswerArray;
                            }
                        }
                        else if($fieldName == 'answer'){
                            if($val['data'][$subKey]['topic_type'] == 1) {
                                $answerValue = json_decode($fieldValue, true);
                                foreach ($answerValue as $answerIndex => $answer) {
                                    $answerValue[$answerIndex] = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                                }
                                $val['data'][$subKey][$fieldName] = $answerValue;
                            }else if($val['data'][$subKey]['topic_type'] == 2 || $val['data'][$subKey]['topic_type'] == 3 ){
                                $answerValue = json_decode($fieldValue, true);
                                foreach ($answerValue as $answerIndex => $answer) {
                                    $htmlStr  = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                                    $html = new simple_html_dom();
                                    $html->load($htmlStr);
                                    $answerValue[$answerIndex] = $html->find('p',0)->innertext;
                                }
                                $answerValue = implode(',',$answerValue);
                                $val['data'][$subKey][$fieldName] = $answerValue;
                            }


                        }

                    }
                }
                if (empty($newResult[$val['type']]['data']))
                    $newResult[$val['type']]['data'] = $val['data'];
                else
                    $newResult[$val['type']]['data'] = array_merge($newResult[$val['type']]['data'], $val['data']);
                $newResult[$val['type']]['name'] = $val['name'];
                $newResult[$val['type']]['totalScore'] = $val['totalScore'];
                $newResult[$val['type']]['count'] = $val['count'];
            }
        }
    }

    private function transDifficulty(&$data)
    {
        $difficultyList = $this->exerciseConfig['difficulty'];
        foreach($data as $key=>$val) {
            foreach ($difficultyList as $key2 => $val2) {
                if ($val['difficulty'] == $val2['id'])
                    $data[$key]['difficulty'] = $val2['name'];
            }
        }
    }

    private function flattenData($data,$isFlatten=0)
    {
        if($isFlatten == 0)
            return $data;
        $newData = array();
        if(0 <  $isFlatten) {
            foreach ($data as $key => $val) {
                foreach ($val['data'] as $subKey => $subVal) {
                    $newData[] =  $subVal;
                }
            }
        }
        if(1 <  $isFlatten)
        {
            $newDataTemp = $newData;
            $newData = array();
            foreach ($newDataTemp as $key => $val) {
                $name = $val['name'];
                $data = $val['data'];
                $subCount = sizeof($data);
                foreach($data as $subKey => $subVal)
                {
                    $subVal['exerciseName'] =  $name;
                    $subVal['count'] = $subCount;
                    $subVal['index'] = $subKey+1;
                    $newData[] = $subVal;
                }
            }
        }
        return  $newData;
    }

    public function getOrderedExerciseList($exerciseIdList = '',$role=0,$userId=0,$flag=0)
    {
        if(empty($exerciseIdList))
            return [];
        $resultData = D('Exercises_student_homework')->getOrderedExerciseList($exerciseIdList,$role,$userId);
        $this->transDifficulty($resultData);
        foreach($resultData as $key => $val){
            $resultData[$key]['subList'] = [];
            if(!empty($val['sub_subject_name'])){ //复合题
                $subTypeArray = explode("///", $val['sub_topic_type']);
                $subSubjectNameArray = explode("///", $val['sub_subject_name']);
                $subAnswerArray = explode("///", $val['sub_answer']);
                foreach($subSubjectNameArray as $i => $name){
                    $resultData[$key]['subList'][] = ['name' => $name,'topic_type' => $subTypeArray[$i],'answer' => $subAnswerArray[$i]];
                }
            }
            unset($resultData[$key]['sub_topic_type']);
            unset($resultData[$key]['sub_subject_name']);
            unset($resultData[$key]['sub_answer']);
        }
        $result['data'] = $this->transExerciseData($resultData,'id,category,name,answer_select,difficulty,class_type,right_key,translation,url,score,answer,main_type,sub_type,answer,subject_name,subList,topic_type,json_html,home_topic_type,eid');
        //add title to exercise
        foreach ($result['data'] as $index => $val) {
            foreach ($val['data'] as $subKey => $subVal) {
                foreach ($subVal['data'] as $subKey1 => $subVal1) {
                    $result['data'][$index]['data'][$subKey]['data'][$subKey1]['title_name'] = $subVal['name'];
                }
            }
        }

        $normalIndex = -1;
        if ($result['data'][0]['name'] == '普通习题') {
            $normalIndex = 0;
        } else if ($result['data'][1]['name'] == '普通习题') {
            $normalIndex = 1;
        }
        if ($normalIndex != -1) {
            $newResult = [];
            $this->__processResult($result,$normalIndex,$newResult);
            if ($normalIndex == 1) {
                foreach ($newResult as $key => $val) {
                    $tempResult[] = ['totalScore'=>$val['totalScore'],'main_type'=>1,'count'=>$val['count'],'type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                $result['data'][1]['data'] = $tempResult;
            } else if ($normalIndex == 0) {
                $newResult2 = [];
                foreach ($newResult as $key => $val) {
                    $newResult2[] = ['totalScore'=>$val['totalScore'],'main_type'=>1,'count'=>$val['count'],'type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                $result['data'][0]['data'] = $newResult2;
            }
        }
        if($flag == 1){
            $result['data'] = $this->flattenData($result['data'],1);
        }
        else if($flag == 2){
            $result['data'] = $this->flattenData($result['data'],3);
        }
        return $result['data'];

    }
    private function __totalScore($data)
    {
        $totalScore = 0;
        foreach($data as $key=>$val)
        {
            $totalScore += $val['score'];
        }
        return $totalScore;
    }
    private function transExerciseData($result,$fields)
    {
        $newResult = array();
        $sub2 = array();
        $sub3 = array();
        $lastMainCategory = 0;
        $lastSubMainCategory = 0;
        $category = '';
        $fieldArray = explode(',',$fields);
        $categoryConfig = json_decode(EXERCISE_CATEGORY,true);
        //load normal category from database
        $normalCategory = D('Exercises_Course')->getAllTopicIdName();

        $newNormalCategory = [];
        $lastTopicType = 0;
        foreach($normalCategory as $key=>$val){
            $newNormalCategory[$val['id']] = $val;
        }
        $categoryConfig['1']['data'] = $newNormalCategory;
        foreach($result as $key=>$val)
        {
            list($mainTempCategory,$subTempCategory) = explode(',',$val['category']);
            if($mainTempCategory == 1){
                $val['category'] = $mainTempCategory.','.$val['home_topic_type'];
            }
            if($category != $val['category'])
            {
                list($mainCategory,$subCategory) = explode(',',$val['category']);

                if(empty($mainCategory) || empty($subCategory))
                    return array();
                if(!empty($sub2))
                {
                    if($lastSubMainCategory != 0) {
                        $sub3[] = array('name' => $categoryConfig[$lastMainCategory]['data'][$lastSubMainCategory]['name'],
                            'type'=> $lastTopicType,
                            'count' => sizeof($sub2),
                            'main_type'=>$lastMainCategory,
                            'sub_type'=>$lastSubMainCategory,
                            'totalScore' => $this->__totalScore($sub2),
                            'data' => $sub2
                        );
                        $sub2 = array();
                    }

                }

                $lastSubMainCategory = $subCategory;

                if($lastMainCategory != $mainCategory ){
                    if(!empty($sub3)) {
                        if ($lastMainCategory != 0) {
                            if(!empty($sub2))
                            {
                                $sub3[] = array('name' => $categoryConfig[$mainCategory]['data'][$lastSubMainCategory]['name'],
                                    'type'=> $lastTopicType,
                                    'count' => sizeof($sub2),
                                    'main_type'=>$lastMainCategory,
                                    'sub_type'=>$lastSubMainCategory,
                                    'totalScore' => $this->__totalScore($sub2),
                                    'data' => $sub2
                                );
                                $sub2 = array();
                            }
                            $newResult[] = array('name' => $categoryConfig[$lastMainCategory]['name'],
                                'data' => $sub3
                            );
                            $sub3 = array();
                        }
                    }
                }
                $lastMainCategory = $mainCategory;
                $category = $val['category'];
            }
            $sub1 = array();
            foreach($fieldArray as $keyIndex=>$fieldName)
            {
                $sub1[$fieldName] = $val[$fieldName];
                if($fieldName==='category')
                {
                    $sub1[$fieldName] = explode(',',$val[$fieldName])[1];
                }
            }
            $sub2[] = $sub1;
            $lastTopicType = $val['home_topic_type'];
        }
        if(!empty($sub2) && !empty($mainCategory))
            $sub3[] = array('name'=>$categoryConfig[$mainCategory]['data'][$subCategory]['name'],
                'type'=> $lastTopicType,
                'main_type'=>$lastMainCategory,
                'sub_type'=>$lastSubMainCategory,
                'count' => sizeof($sub2),
                'totalScore' => $this->__totalScore($sub2),
                'data' => $sub2
            );

        if(!empty($sub3)){
            $newResult[] = array('name'=>$categoryConfig[$mainCategory]['name'],

                'data' => $sub3
            );
        }
        return $newResult;
    }
}
?>

