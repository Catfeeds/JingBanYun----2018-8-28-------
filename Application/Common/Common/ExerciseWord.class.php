<?php
/**
 * 生成WORD接口
 */

namespace Common\Common;
use Think\Log;      //日志类
use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3;


/**
 * Created by PhpStorm.
 * User: ZY
 * Date: 2018/06/19
 * Time: 12:04
 * Dec :
 */
class ExerciseWord
{
   private $blankCharacter = '\u3116' ;
   private $exerciseConfig ;
   public function __construct()
   {
       $this->exerciseConfig = include(__DIR__.'/../../Exercise/Conf/createExercise.php');
   }

   public function test()
   {
       $exerciseArray = ['name'=>'testName','data'=>[['name'=>'一.简答题','point'=>10,'data'=>[]],['name'=>'二.填空题','point'=>20,'data'=>[]],['name'=>'三.选择题','point'=>30,'data'=>[]]]];
       $idArray = [15815,18316,18298];
       foreach($idArray as $key=>$val) {
           //$exerciseArray['data'][2]['data'][] = D('Exercises_question_processinfo')->getQuestionInfo($val);
       }
       $idArray1=[18314];
       foreach($idArray1 as $key=>$val) {
           //$exerciseArray['data'][1]['data'][] = D('Exercises_question_processinfo')->getQuestionInfo($val);
       }
       $idArray2=[18331,18290];
       foreach($idArray2 as $key=>$val) {
           //$exerciseArray['data'][0]['data'][] = D('Exercises_question_processinfo')->getQuestionInfo($val);
       }
       $this->generateWord($exerciseArray);
   }

   private function __generateTable($phpWord,$section,$titleArray)
   {

       $defaultBorderSize = 6;
       $outerBorderSize = 12;
       $fancyTableStyleName = 'Fancy Table';
       $fancyTableStyle = array('unit' => 'pct','borderSize' => $defaultBorderSize, 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
       $fancyTableFirstRowStyle = array('borderTopSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize);
       $fancyTableFontStyle = array('size'=>12,'bold' => true,'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
       $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
       $table = $section->addTable($fancyTableStyleName);
       $table->addRow(900);
       for($i=0;$i<sizeof($titleArray)+2;$i++){
           if($i == 0) {
               $table->addCell(1000, array('borderTopSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize))->addText('题号', $fancyTableFontStyle);
           }
           else if($i == sizeof($titleArray)+1 ){
               $table->addCell(2000, array('borderTopSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize))->addText('总分', $fancyTableFontStyle);
           }
           else{
               $table->addCell(2000, array('borderTopSize' => $outerBorderSize))->addText($titleArray[$i-1], $fancyTableFontStyle);
           }
       }
       $table->addRow(900);
       for($i=0;$i<sizeof($titleArray)+2;$i++){
           if($i == 0) {
               $table->addCell(1000, array('borderBottomSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize))->addText('得分', $fancyTableFontStyle);
           }
           else if($i == sizeof($titleArray) +1 ){
               $table->addCell(2000, array('borderBottomSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize))->addText('', $fancyTableFontStyle);
           }
           else{
               $table->addCell(2000, array('borderBottomSize' => $outerBorderSize))->addText('', $fancyTableFontStyle);
           }
       }
   }

    private function __outputAnswer($exercise,$section,$number){
        $html = new simple_html_dom();
        $html->load($this->__getExerciseHTML('',substr($exercise['json_html'],1,-1)));
        $answer = $html->find('.exerciseAnswer',0);
        $analysis = $html->find('.exerciseJx',0);
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $number.'.'.$answer.$analysis, false, false);

        $html->clear();
    }

    private function __generateAnswer($phpWord,$section,$exerciseArray){
        $fontStyle['name'] = '黑体';
        $fontStyle['size'] = 12;

        foreach($exerciseArray as $id => $data){
            //add single table and title

            $section->addTextBreak();
            $textRun = $section->addTextRun();
            $textRun->addText($data['name'], $fontStyle);
            $section->addTextBreak();
            $number = 1;

            foreach($data['data']  as $key => $exercise){
                if(empty($exercise['subData']))  //非复合习题
                {
                    $this->__outputAnswer($exercise,$section,$number++);
                }
                else{
                    //复合题输出
                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($number), true, false);
                    //小题输出
                    $subNumber = 1;
                    foreach($exercise['subData'] as  $subKey=>$subData){
                        $this->__outputAnswer($subData,$section,$subNumber++);
                    }
                }
                $section->addTextBreak();
            }
            $section->addTextBreak();
        }
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
   private function __getExerciseHTML($addString='',$html)
   {
       $html  = htmlspecialchars_decode($html);
       $htmlStr = stripcslashes($this->__decodeUnicode($html));
       $htmlStr = str_replace('ㄖ','&nbsp;__________&nbsp; ',$htmlStr);
       if(empty($addString))
           return $htmlStr;
       $html = new simple_html_dom();
       $html->load($htmlStr);
       $html->childNodes(0)->innertext=$addString.$html->childNodes(0)->innertext;
       return $html;

   }
   private function __outputExercise($exercise,$section,$number,$pointStr)
   {
       switch($exercise['topic_type']){
           case 1://选择
           case 3://选择填空
               //add css content to html
               \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($this->__getExerciseHTML($pointStr.$number.'.',$exercise['subject_name'])), true, false);
               if($exercise['topic_type'] == 1)
                   $ansArray = json_decode($exercise['answer'],true);
               else
                   $ansArray = json_decode($exercise['answer_select'],true);
               $initAnswer = 65;
               foreach($ansArray as $null=>$ans){
                   \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($this->__getExerciseHTML(chr($initAnswer++) .'.',$ans)), true, false);
               }
               //答案
               break;

           case 2://文字填空
           case 5://作图
           case 6://解答
               \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($this->__getExerciseHTML($pointStr.$number.'.',$exercise['subject_name'])), true, false);
               if($exercise['topic_type'] == 5 || $exercise['topic_type'] == 6)
               for($i=0;$i<10;$i++) $section->addTextBreak();
               break;
           case 4://连线
               \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($this->__getExerciseHTML($pointStr.$number.'.',$exercise['subject_name'])), true, false);
               $answerHtml = $this->__getExerciseHTML('',$exercise['answer_select']);
               $answerHtml = $this->__composeHTML(substr($answerHtml,1,-1));
               $html = new simple_html_dom();
               $html->load($answerHtml);
               $html->find('tr',0)->outertext='';
               foreach($html->find('tr') as $tr){
                   $tr->childNodes(0)->outertext='';
                   foreach($tr->find('td') as $td) {
                       $td->style = "text-align:center;width:250px;height:120px;border:1px solid #EAC41B;padding:10px";
                   }

               }
               \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false,['table'=>['height'=>40,'cellMargin' => 80,'borderColor' => 'EAC41B']]);
               break;

       }
   }
   private function __getExerciseTotalScore($scoreStr)
   {
       if(strpos($scoreStr,',') !== false){
           $scoreStr = array_reduce(explode(',',$scoreStr),function($carry,$val){return $val+$carry;});
       }
       return $scoreStr;
   }
   private function __generateExercise($phpWord,$section,$exerciseArray){

       $fontStyle['name'] = '黑体';
       $fontStyle['size'] = 12;

       foreach($exerciseArray as $id => $data){
            //add single table and title
           $defaultBorderSize = 6;
           $outerBorderSize = 12;
           $singleTableStyleName = 'Single Table';
           $fancyTableStyle = array('borderSize' => $defaultBorderSize, 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::START);
           $fancyTableFirstRowStyle = array('borderTopSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize);
           $fancyTableFontStyle = array('size'=>12,'bold' => true,'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
           $phpWord->addTableStyle($singleTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
           $table = $section->addTable($singleTableStyleName);
           $table->addRow(500);
           $table->addCell(1000, array('borderTopSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize))->addText('评卷人', $fancyTableFontStyle);
           $table->addCell(1000, array('borderTopSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize))->addText('得分', $fancyTableFontStyle);
           $table->addRow(500);
           $table->addCell(1000, array('borderBottomSize' => $outerBorderSize,'borderLeftSize' => $outerBorderSize))->addText('', $fancyTableFontStyle);
           $table->addCell(1000, array('borderBottomSize' => $outerBorderSize,'borderRightSize' => $outerBorderSize))->addText('', $fancyTableFontStyle);
           //环绕
           $section->addTextBreak();
           $textRun = $section->addTextRun();
           $textRun->addText($data['name']."(共{$data['point']}分)", $fontStyle);
           $section->addTextBreak();
           $number = 1;

           foreach($data['data']  as $key => $exercise){
               $exercise['score'] = $this->__getExerciseTotalScore($exercise['score']);
               $pointStr = "({$exercise['score']}分) ";
               if(empty($exercise['subData']))  //非复合习题
               {
                  $this->__outputExercise($exercise,$section,$number++,$pointStr);
               }
               else{
                   //复合题题干输出
                   \PhpOffice\PhpWord\Shared\Html::addHtml($section, $this->__composeHTML($this->__getExerciseHTML($pointStr.$number.'.',$exercise['subject_name'])), true, false);
                   //小题输出
                   $subNumber = 1;
                   foreach($exercise['subData'] as  $subKey=>$subData){
                       $subData['score'] = $this->__getExerciseTotalScore($subData['score']);
                       $pointStr = "({$subData['score']}分) ";
                       $this->__outputExercise($subData,$section,$subNumber++,$pointStr);
                   }
               }
               $section->addTextBreak();
           }
           $section->addTextBreak();
       }
   }

    private function __composeHTML($body)
    {
        return '<html><body>'.$body.'</body></html>';
    }
   /**
    * 生成WORD文档
    * @param array $paperInfo 试卷信息
    * @return bool|string 成功返回WORD文件路径，失败返回FALSE
    * @eg $paperInfo 示例：
    {
   "name": "试卷名称",
   "data": [
        {
       "name": "一.简答题",
       "point": 10,         //大题分值
       "data": []           习题数组：各习题需要包含的字段: subject_name answer_select json_html topic_type score,如果是复合题则需要加入subData字段，在subData中填入小题信息，如：
                              data:[    {
                                        "subject_name": "...",
                                         ...
                                        "subData":[
                                                     {
                                                          "subject_name": "...",
                                                           ...
                                                     },

                                                  ]
                                        },
                                       {...}
                                   ]
       },
       {
           "name": "二.填空题",
   			"point": 20,
   			"data": []
   		},
       {
           "name": "三.选择题",
   			"point": 30,
   			"data": []
   		}
   ]
   }
   */


    public function generateWord($paperInfo=[])
   {
       set_time_limit(0);
       try {
           include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpoffice/phpword/samples/Sample_Header.php';

           // New Word Document
           echo date('H:i:s'), ' Create new PhpWord object', EOL;

           $languageEnGb = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_GB);

           $phpWord = new \PhpOffice\PhpWord\PhpWord();
           $phpWord->getSettings()->setThemeFontLang($languageEnGb);


           $fontStyleName = 'rStyle';
           $phpWord->addFontStyle($fontStyleName, array('bold' => true, 'italic' => true, 'size' => 16, 'allCaps' => true, 'doubleStrikethrough' => true));

           $paragraphStyleName = 'pStyle';
           $phpWord->addParagraphStyle($paragraphStyleName, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));

           $phpWord->addTitleStyle(1, array('size' => 16, 'name' => '宋体', 'bold' => true), array('spaceAfter' => 240, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

           //设置分栏
           $section = $phpWord->addSection([
               'colsNum' => 2,
               'breakType' => 'continuous',
               'pageSizeH' => '16839.9', 'pageSizeW' => '23814']);

           //标题
           $section->addTitle("{$paperInfo['name']}", 1);
           $section->addTextBreak();
           $section->addTextBreak();

//           //设置水印
//           $header = $section->createHeader();
//           $header->addWatermark( $_SERVER['DOCUMENT_ROOT'].'/big_watermark.png', array('marginTop'=>10, 'marginLeft'=>20,'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER) );

           //表头
           $titleArray = array_column($paperInfo['data'], 'name');
           $this->__generateTable($phpWord, $section, $titleArray);
           $section->addTextBreak();

           //题干
           $this->__generateExercise($phpWord, $section, $paperInfo['data']);
           $section->addTextBreak();
           $section->addTextBreak();
           $section->addTextBreak();
           $section->addTextBreak();

           //答案
           $section->addTitle('答案', 1);
           $this->__generateAnswer($phpWord, $section, $paperInfo['data']);

           // Save file
           $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
           $str = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
           $str = 'NT' . substr(str_shuffle($str), 5, 8);
           $fileName = '/tmp/' . $str . '.docx';
           $objWriter->save($_SERVER['DOCUMENT_ROOT'] . $fileName);
           return $fileName;
       }
       catch(\Exception $e){
           return false;
       }

   }
}
?>