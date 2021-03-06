<?php
namespace Home\Controller;
use Think\Controller;
class TempController extends PublicController{
    public function deleteAccount()
    {
        if($_GET['token'] == 'f2sdfi90') {
            M()->execute("delete from auth_parent where telephone in (13522779367,18614060661,15711469698)");
            M()->execute("delete from auth_teacher where telephone in (13522779367,18614060661,15711469698)");
            echo "delete finish";
        }
    }
    public function importGGKJ()
    {
        $data = M('biz_lesson_planning_template')->select();
        $importData['resource_type'] = 1;
        $importData['charge_type'] = 1;
        $importData['charge_time'] = 0;
        $importData['status'] = 1;
        $importData['create_at'] = time();
        for($index=0;$index<sizeof($data);$index++)
        {
            $model = M();
            $model->startTrans();
            $importData['name'] = $data[$index]['name'];
            $knowledgeResourceModel = D('Knowledge_resource');
            $resourceId = $knowledgeResourceModel->addResourceData($importData);
            if(!$resourceId)
            {
                $model->rollback();
                continue;
            }
            $contactData['resource_id'] = $resourceId;
            $contactData['resource_path'] = $data[$index]['file_path'];
            $contactId = $knowledgeResourceModel->addResourceContactData($contactData);
            if(!$contactId)
            {
                $model->rollback();
                continue;
            }
            $model->commit();
            echo 'http://www.jingbanyun.com/Resources/lessonplanning/'.$contactData['resource_path'].','.$resourceId.'<br/>';
        }
    }
  public function importNobookPhysics()
  {
      for($index=0;$index<1;$index++)
      {
          $importData['resource_type'] = 2;
          $importData['charge_type'] = 1;
          $importData['charge_time'] = 0;
          $importData['status'] = 1;
          $importData['create_at'] = time();
          $model = M();
          $model->startTrans();
          $importData['name'] = '物理实验室';
          $knowledgeResourceModel = D('Knowledge_resource');
          $resourceId = $knowledgeResourceModel->addResourceData($importData);
          if(!$resourceId)
          {
              $model->rollback();
              continue;
          }
          $contactData['resource_id'] = $resourceId;
          $contactData['resource_path'] = WEB_URL . "/index.php?m=Home&c=Teach&a=redirectNobook&course=wuli";
          $contactId = $knowledgeResourceModel->addResourceContactData($contactData);
          if(!$contactId)
          {
              $model->rollback();
              continue;
          }
          $model->commit();
      }
  }
    public function refreshNobook()
    {
        set_time_limit(0);
        $time=time();
        $appid=C('NOBOOK_CONFIG.appid');
        $appkey=C('NOBOOK_CONFIG.appkey');
        $string=md5($appid.$time.$appkey);
        $courseArray = array('science');
        for($i=0;$i<sizeof($courseArray);$i++) {
            $url = "http://{$courseArray[$i]}.nobook.com.cn/openapi/get_resource?appid=" . $appid . '&code=' . $string . "&time=" . $time;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($output);
            for($index=0;$index<sizeof($data);$index++)
            {
                $model = M();
                $id = $data[$index]->id;
                $model = M('knowledge_resource_file_contact');
                $where['knowledge_resource_file_contact.resource_path'] = array('like','%course='.$courseArray[$i].'&id='.$id);
                $result = $model->join('knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id')
                      ->where($where)
                      ->field('knowledge_resource.id')
                      ->find();
                if(!empty($result))
                {
                    $pointModel = M('knowledge_resource_point');
                    $data['knowledge_resource_id'] = $result['id'];
                    $data['course'] = 31;
                    $data['grade'] = -1;
                    $data['textbook'] = -1;
                    $data['chapter'] = -1;
                    $data['festival'] = -1;
                    $data['knowledge'] = -1;
                    $data['child_knowledge'] = -1;
                    $data['knowledge_info'] = '小学科学';
                    $data['publishing_house_id'] = 1;
                    $data['knowledge_info_point'] = '小学科学';
                    $pointModel->add($data);
                }
            }
        }
    }

  public function importNobookRes()
  {
      set_time_limit(0);
      $time=time();
      $appid=C('NOBOOK_CONFIG.appid');
      $appkey=C('NOBOOK_CONFIG.appkey');
      $string=md5($appid.$time.$appkey);
      $courseArray = array('shengwu','science');
      for($i=0;$i<sizeof($courseArray);$i++) {
          $url = "http://{$courseArray[$i]}.nobook.com.cn/openapi/get_resource?appid=" . $appid . '&code=' . $string . "&time=" . $time;

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_HEADER, 0);
          $output = curl_exec($ch);
          curl_close($ch);
          $data = json_decode($output);
          $importData['resource_type'] = 2;
          $importData['charge_type'] = 1;
          $importData['charge_time'] = 0;
          $importData['status'] = 1;
          $importData['create_at'] = time();
          for($index=0;$index<sizeof($data);$index++)
          {
              $model = M();
              $model->startTrans();
              $importData['name'] = $data[$index]->name;
              $importData['pc_cover'] = $data[$index]->img;
              $importData['mobile_cover'] = $data[$index]->img;
              $knowledgeResourceModel = D('Knowledge_resource');
              $resourceId = $knowledgeResourceModel->addResourceData($importData);
              if(!$resourceId)
              {
                  $model->rollback();
                  continue;
              }
              $contactData['resource_id'] = $resourceId;
              $contactData['resource_path'] = WEB_URL . "/index.php?m=Home&c=Teach&a=redirectNobook&course={$courseArray[$i]}&id={$data[$index]->id}";
              $contactId = $knowledgeResourceModel->addResourceContactData($contactData);
              if(!$contactId)
              {
                  $model->rollback();
                  continue;
              }
              $model->commit();
          }
      }
  }

  public function importWBHTRes()
  {
      set_time_limit(0);
      $math_resourcename = ['相反数','有理数的减法','求算术平方根（1）','什么是代数式','代数式求值','同类项','合并同类项','方程的概念','一元一次方程的实际应用(1)','函数的图像','根据函数图象确定函数解析式','三角形的分类（按角分）','几何体的展开图','众数和算数平均数','有理数加法法则探究','立方根','科学计数法','利用函数图象进行单位换算','函数 y = kx 的图象（k 取任意值）','函数 y = kx + b 的图象','价格','全等三角形','作已知线段的垂直平分线','频率','扇形图','乘方','解一元一次方程 —— 合并同类项与移项','含有分数的一元一次方程的解法','多边形的外角和','轴对称','位似放大','在平面直角坐标系中以原点 O 为位似中心的放大图形','数据的收集','无限小数与无理数','立方根','用科学记数法表示的数的加减','用科学记数法表示的数的乘除','三角形的内角和 —— 观察与证明','全等三角形的判定——边边边（SSS）','平行四边形和矩形','多边形的对角线','多边形的内角和','坐标系中关于 x 轴和 y 轴对称的图形','旋转','位似变换与坐标','相似多边形的性质','相似三角形的性质','多项式乘多项式（1）','多项式乘多项式（2）','多项式乘多项式（3）','两数和的平方公式的引入','两数差的平方','杨辉三角','三个数和的平方','平方差公式','分组分解法','二次项系数为1的二次三项式的因式分解（十字相乘法）','二次项系数不为1的二次三项式的因式分解（十字相乘法）','分式','约分','分式的乘除','分式的加减','解分式方程','实例证明','频数分布表','频数分布直方图','平均数的计算规律','方差','勾股定理','勾股定理——几何证明','圆心和半径','弦和直径','圆的对称性','切线长定理','圆周角','圆内接四边形的角','反比例函数的图象','锐角三角函数 —— 正切','用计算器求锐角三角函数值','借助计算器求直角三角形中锐角的度数','特殊角的三角函数值','仰角','不可能事件和必然事件','一次函数的图象与坐标轴的交点','面积最小值问题','二次函数的图像','沿 y 轴移动抛物线','利用关键点画二次函数的图象','二元一次方程组的解法','二元一次方程组的应用（1）','二元一次方程组的应用（3）','配方法解一元二次方程（2）','一元二次方程的根的判别式不小于零','一元二次方程根的判别式的应用','用一元二次方程解决几何问题','求解一元一次不等式','一元一次不等式组'];
      $math_resourceUrl = ['http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l007_P03/scorm-emt.html?sco=content%2Fscript_00020.emt.xml&title=020+M3_l007_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l007_P05/scorm-emt.html?sco=content%2Fscript_00021.emt.xml&title=021+M3_l007_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l009_P06/scorm-emt.html?sco=content%2Fscript_00028.emt.xml&title=028+M3_l009_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l014_P02/scorm-emt.html?sco=content%2Fscript_00232.emt.xml&title=232+M3_l014_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l014_P08/scorm-emt.html?sco=content%2Fscript_00041.emt.xml&title=041+M3_l014_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l015_P03/scorm-emt.html?sco=content%2Fscript_00042.emt.xml&title=042+M3_l015_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l015_P04/scorm-emt.html?sco=content%2Fscript_00233.emt.xml&title=233+M3_l015_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l017_P02/scorm-emt.html?sco=content%2Fscript_00043.emt.xml&title=043+M3_l017_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l017_P07/scorm-emt.html?sco=content%2Fscript_00044.emt.xml&title=044+M3_l017_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l021_P02/scorm-emt.html?sco=content%2Fscript_00045.emt.xml&title=045+M3_l021_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l021_P05/scorm-emt.html?sco=content%2Fscript_00234.emt.xml&title=234+M3_l021_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l024_P05/scorm-emt.html?sco=content%2Fscript_00063.emt.xml&title=063+M3_l024_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l027_P07/scorm-emt.html?sco=content%2Fscript_00077.emt.xml&title=077+M3_l027_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l036_P04/scorm-emt.html?sco=content%2Fscript_00108.emt.xml&title=108+M3_l036_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l046_P02/scorm-emt.html?sco=content%2Fscript_00122.emt.xml&title=122+M3_l046_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l048_P05/scorm-emt.html?sco=content%2Fscript_00126.emt.xml&title=126+M3_l048_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l048_P07/scorm-emt.html?sco=content%2Fscript_00127.emt.xml&title=127+M3_l048_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l065_P03/scorm-emt.html?sco=content%2Fscript_00155.emt.xml&title=155+M3_l065_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l065_P06/scorm-emt.html?sco=content%2Fscript_00465.emt.xml&title=465+M3_l065_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l065_P09/scorm-emt.html?sco=content%2Fscript_00157.emt.xml&title=157+M3_l065_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l066_P05/scorm-emt.html?sco=content%2Fscript_00158.emt.xml&title=158+M3_l066_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l069_P08/scorm-emt.html?sco=content%2Fscript_00159.emt.xml&title=159+M3_l069_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l074_P04/scorm-emt.html?sco=content%2Fscript_00160.emt.xml&title=160+M3_l074_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l080_P04/scorm-emt.html?sco=content%2Fscript_00569.emt.xml&title=569+M3_l080_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l081_P04/scorm-emt.html?sco=content%2Fscript_00178.emt.xml&title=178+M3_l081_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l097_P05/scorm-emt.html?sco=content%2Fscript_00203.emt.xml&title=203+M3_l097_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l103_P04/scorm-emt.html?sco=content%2Fscript_00211.emt.xml&title=211+M3_l103_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l104_P05/scorm-emt.html?sco=content%2Fscript_00212.emt.xml&title=212+M3_l104_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l111_P06/scorm-emt.html?sco=content%2Fscript_00213.emt.xml&title=213+M3_l111_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l113_P02/scorm-emt.html?sco=content%2Fscript_00765.emt.xml&title=765+M3_l113_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l117_P04/scorm-emt.html?sco=content%2Fscript_00215.emt.xml&title=215+M3_l117_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l117_P06/scorm-emt.html?sco=content%2Fscript_00216.emt.xml&title=216+M3_l117_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m3_l130_P02/scorm-emt.html?sco=content%2Fscript_00231.emt.xml&title=231+M3_l130_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l006_P08/scorm-emt.html?sco=content%2Fscript_00484.emt.xml&title=0484+M4_l006_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l008_P06/scorm-emt.html?sco=content%2Fscript_00028.emt.xml&title=0028+M4_l008_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l009_P08/scorm-emt.html?sco=content%2Fscript_00383.emt.xml&title=0383+M4_l009_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l009_P09/scorm-emt.html?sco=content%2Fscript_00384.emt.xml&title=0384+M4_l009_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l011_P08/scorm-emt.html?sco=content%2Fscript_00247.emt.xml&title=0247+M4_l011_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l012_P07/scorm-emt.html?sco=content%2Fscript_00556.emt.xml&title=0556+M4_l012_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l013_P03/scorm-emt.html?sco=content%2Fscript_00344.emt.xml&title=0344+M4_l013_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l014_P03/scorm-emt.html?sco=content%2Fscript_00252.emt.xml&title=0252+M4_l014_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l014_P04/scorm-emt.html?sco=content%2Fscript_00253.emt.xml&title=0253+M4_l014_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l016_P04/scorm-emt.html?sco=content%2Fscript_00390.emt.xml&title=0390+M4_l016_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l017_P07/scorm-emt.html?sco=content%2Fscript_00277.emt.xml&title=0277+M4_l017_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l018_P08/scorm-emt.html?sco=content%2Fscript_00403.emt.xml&title=0403+M4_l018_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l019_P05/scorm-emt.html?sco=content%2Fscript_00409.emt.xml&title=0409+M4_l019_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l019a_P06/scorm-emt.html?sco=content%2Fscript_00419.emt.xml&title=0419+M4_l019a_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l021_P08/scorm-emt.html?sco=content%2Fscript_00358.emt.xml&title=0358+M4_l021_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l021_P09/scorm-emt.html?sco=content%2Fscript_00359.emt.xml&title=0359+M4_l021_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l021_P10/scorm-emt.html?sco=content%2Fscript_00360.emt.xml&title=0360+M4_l021_P10&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l022_P02/scorm-emt.html?sco=content%2Fscript_00283.emt.xml&title=0283+M4_l022_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l022_P04/scorm-emt.html?sco=content%2Fscript_00285.emt.xml&title=0285+M4_l022_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l022_P07/scorm-emt.html?sco=content%2Fscript_00288.emt.xml&title=0288+M4_l022_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l022_P08/scorm-emt.html?sco=content%2Fscript_00289.emt.xml&title=0289+M4_l022_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l022_P09/scorm-emt.html?sco=content%2Fscript_00290.emt.xml&title=0290+M4_l022_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l023_P08/scorm-emt.html?sco=content%2Fscript_00064.emt.xml&title=0064+M4_l023_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l024a_P02/scorm-emt.html?sco=content%2Fscript_00361.emt.xml&title=0361+M4_l024a_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l024a_P03/scorm-emt.html?sco=content%2Fscript_00362.emt.xml&title=0362+M4_l024a_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l025_P02/scorm-emt.html?sco=content%2Fscript_00067.emt.xml&title=0067+M4_l025_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l025_P03/scorm-emt.html?sco=content%2Fscript_00068.emt.xml&title=0068+M4_l025_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l025_P05/scorm-emt.html?sco=content%2Fscript_00070.emt.xml&title=0070+M4_l025_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l025_P06/scorm-emt.html?sco=content%2Fscript_00071.emt.xml&title=0071+M4_l025_P06&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l025_P08/scorm-emt.html?sco=content%2Fscript_00073.emt.xml&title=0073+M4_l025_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l027_P07/scorm-emt.html?sco=content%2Fscript_00638.emt.xml&title=0638+M4_l027_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l031_P07/scorm-emt.html?sco=content%2Fscript_00657.emt.xml&title=0657+M4_l031_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l031_P09/scorm-emt.html?sco=content%2Fscript_00659.emt.xml&title=0659+M4_l031_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l032_P07/scorm-emt.html?sco=content%2Fscript_00209.emt.xml&title=0209+M4_l032_P07&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l034_P03/scorm-emt.html?sco=content%2Fscript_00088.emt.xml&title=0088+M4_l034_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l038_P03/scorm-emt.html?sco=content%2Fscript_00536.emt.xml&title=0536+M4_l038_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l038_P04/scorm-emt.html?sco=content%2Fscript_00537.emt.xml&title=0537+M4_l038_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l042_P02/scorm-emt.html?sco=content%2Fscript_00685.emt.xml&title=0685+M4_l042_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l042_P03/scorm-emt.html?sco=content%2Fscript_00686.emt.xml&title=0686+M4_l042_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l043_P02/scorm-emt.html?sco=content%2Fscript_00692.emt.xml&title=0692+M4_l043_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l043_P05/scorm-emt.html?sco=content%2Fscript_00695.emt.xml&title=0695+M4_l043_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l044_P02/scorm-emt.html?sco=content%2Fscript_00557.emt.xml&title=0557+M4_l044_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l044_P11/scorm-emt.html?sco=content%2Fscript_00566.emt.xml&title=0566+M4_l044_P11&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l055_P05/scorm-emt.html?sco=content%2Fscript_00221.emt.xml&title=0221+M4_l055_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l057_P03/scorm-emt.html?sco=content%2Fscript_00147.emt.xml&title=0147+M4_l057_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l057_P05/scorm-emt.html?sco=content%2Fscript_00149.emt.xml&title=0149+M4_l057_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l057_P08/scorm-emt.html?sco=content%2Fscript_00152.emt.xml&title=0152+M4_l057_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l057_P09/scorm-emt.html?sco=content%2Fscript_00153.emt.xml&title=0153+M4_l057_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l058_P02/scorm-emt.html?sco=content%2Fscript_00582.emt.xml&title=0582+M4_l058_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l063_P04/scorm-emt.html?sco=content%2Fscript_00739.emt.xml&title=0739+M4_l063_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l071_P05/scorm-emt.html?sco=content%2Fscript_00821.emt.xml&title=0821+M4_l071_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l072_P02/scorm-emt.html?sco=content%2Fscript_00826.emt.xml&title=0826+M4_l072_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l072_P05/scorm-emt.html?sco=content%2Fscript_00829.emt.xml&title=0829+M4_l072_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l072a_P03/scorm-emt.html?sco=content%2Fscript_00833.emt.xml&title=0833+M4_l072a_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l072a_P05/scorm-emt.html?sco=content%2Fscript_00835.emt.xml&title=0835+M4_l072a_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l083_P02/scorm-emt.html?sco=content%2Fscript_00325.emt.xml&title=0325+M4_l083_P02&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l083_P03/scorm-emt.html?sco=content%2Fscript_00326.emt.xml&title=0326+M4_l083_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l083_P05/scorm-emt.html?sco=content%2Fscript_00328.emt.xml&title=0328+M4_l083_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l084_P09/scorm-emt.html?sco=content%2Fscript_00917.emt.xml&title=0917+M4_l084_P09&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l085_P05/scorm-emt.html?sco=content%2Fscript_00179.emt.xml&title=0179+M4_l085_P05&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l086_P03/scorm-emt.html?sco=content%2Fscript_00183.emt.xml&title=0183+M4_l086_P03&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l086_P08/scorm-emt.html?sco=content%2Fscript_00188.emt.xml&title=0188+M4_l086_P08&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l088_P04/scorm-emt.html?sco=content%2Fscript_00606.emt.xml&title=0606+M4_l088_P04&api=13','http://jingbanyun.com/Public/wb/sx/CN_muc_m4_l088a_P02/scorm-emt.html?sco=content%2Fscript_00937.emt.xml&title=0937+M4_l088a_P02&api=13'];
      array_splice($math_resourcename,0,1);
      array_splice($math_resourceUrl,0,1);
      $chemistry_name = ['溶解是溶质在溶剂中的扩散过程。溶质溶解在溶剂里形成的混合物叫做溶液。','德谟克利特和道尔顿的原子理论','电子的发现和汤姆森原子模型','现代原子理论','元素周期表的结构——族与周期','氯化钠的来源和用途','碳的同素异形体','氧的同素异形体','什么是合金','合金与纯金属的性质有哪些不同','物理变化和化学变化的判断','吸热反应和放热反应','指示剂和pH试纸在不同性质溶液中的颜色','自然界中存在的盐及其用途','什么是胶体','稀有气体的用途','根据金属活动性对金属进行排序','金属的腐蚀','金属的防护','化学反应中的能量变化','催化剂在反应前后质量不变','催化剂在环境保护中的应用','有机合成材料的利用与回收','空气污染物','烟雾和酸雨','氮肥','液体药品的量取','有关液体的安全操作','固体药品的储存和使用','固体药品的研磨','化学反应中产生的气体的收集','某些气体的检验','焰色反应','一些阳离子和氢氧根离子的反应','危险化学品'];
      $chemistry_url = [
          'http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l007_P06/scorm-emt.html?sco=content%2Fscript_00011.emt.xml&title=0011+C4_l007_P06&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l013_P02/scorm-emt.html?sco=content%2Fscript_00160.emt.xml&title=0160+C4_l013_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l013_P03/scorm-emt.html?sco=content%2Fscript_00161.emt.xml&title=0161+C4_l013_P03&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l013_P06/scorm-emt.html?sco=content%2Fscript_00164.emt.xml&title=0164+C4_l013_P06&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l018_P04/scorm-emt.html?sco=content%2Fscript_00100.emt.xml&title=0100+C4_l018_P04&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l019_P08/scorm-emt.html?sco=content%2Fscript_00219.emt.xml&title=0219+C4_l019_P08&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l024_P03/scorm-emt.html?sco=content%2Fscript_00272.emt.xml&title=0272+C4_l024_P03&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l024_P06/scorm-emt.html?sco=content%2Fscript_00275.emt.xml&title=0275+C4_l024_P06&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l026_P02/scorm-emt.html?sco=content%2Fscript_00165.emt.xml&title=0165+C4_l026_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l026_P07/scorm-emt.html?sco=content%2Fscript_00170.emt.xml&title=0170+C4_l026_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l027_P04/scorm-emt.html?sco=content%2Fscript_00038.emt.xml&title=0038+C4_l027_P04&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l029_P08/scorm-emt.html?sco=content%2Fscript_00054.emt.xml&title=0054+C4_l029_P08&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l035_P02/scorm-emt.html?sco=content%2Fscript_00112.emt.xml&title=0112+C4_l035_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l037_P02/scorm-emt.html?sco=content%2Fscript_00119.emt.xml&title=0119+C4_l037_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l043_P02/scorm-emt.html?sco=content%2Fscript_00177.emt.xml&title=0177+C4_l043_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l046_P07/scorm-emt.html?sco=content%2Fscript_00135.emt.xml&title=0135+C4_l046_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l053_P02/scorm-emt.html?sco=content%2Fscript_00184.emt.xml&title=0184+C4_l053_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l054_P06/scorm-emt.html?sco=content%2Fscript_00194.emt.xml&title=0194+C4_l054_P06&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l054_P07/scorm-emt.html?sco=content%2Fscript_00195.emt.xml&title=0195+C4_l054_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l065_P02/scorm-emt.html?sco=content%2Fscript_00564.emt.xml&title=0564+C4_l065_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l069_P05/scorm-emt.html?sco=content%2Fscript_00080.emt.xml&title=0080+C4_l069_P05&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l069_P07/scorm-emt.html?sco=content%2Fscript_00082.emt.xml&title=0082+C4_l069_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l074_P07/scorm-emt.html?sco=content%2Fscript_00442.emt.xml&title=0442+C4_l074_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l081_P05/scorm-emt.html?sco=content%2Fscript_00139.emt.xml&title=0139+C4_l081_P05&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l081_P07/scorm-emt.html?sco=content%2Fscript_00141.emt.xml&title=0141+C4_l081_P07&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l083_P04/scorm-emt.html?sco=content%2Fscript_00240.emt.xml&title=0240+C4_l083_P04&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l099_P03/scorm-emt.html?sco=content%2Fscript_00328.emt.xml&title=0328+C4_l099_P03&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l099_P06/scorm-emt.html?sco=content%2Fscript_00331.emt.xml&title=0331+C4_l099_P06&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l100_P02/scorm-emt.html?sco=content%2Fscript_00333.emt.xml&title=0333+C4_l100_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l100_P04/scorm-emt.html?sco=content%2Fscript_00335.emt.xml&title=0335+C4_l100_P04&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l101_P02/scorm-emt.html?sco=content%2Fscript_00339.emt.xml&title=0339+C4_l101_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l101_P05/scorm-emt.html?sco=content%2Fscript_00342.emt.xml&title=0342+C4_l101_P05&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l102_P02/scorm-emt.html?sco=content%2Fscript_00249.emt.xml&title=0249+C4_l102_P02&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l102_P03/scorm-emt.html?sco=content%2Fscript_00250.emt.xml&title=0250+C4_l102_P03&api=13','http://jingbanyun.com/Public/wb/hx/CN_muc_c4_l103_P02/scorm-emt.html?sco=content%2Fscript_00196.emt.xml&title=0196+C4_l103_P02&api=13'
      ];
      $biology_name = [
          '光学显微镜','光学显微镜——玻片标本的制作','电子显微镜','多细胞生物体的结构层次','上皮组织的结构和功能','肌肉组织','结缔组织','神经组织','生物分类学','植物界','节肢动物','脊椎动物的特征','脊椎动物的生殖方式','病毒性传染病','氧气和二氧化碳的运输','动脉和脉搏','心脏的结构和血液在心脏内的流动','肺循环和体循环','心脏、血管和血压','心肺复苏术','营养物质的来源','消化道的结构和功能','胃——进一步机械性磨碎食物和初步消化蛋白质','小肠——糖类、蛋白质和脂肪进一步消化和吸收的场所','物理性消化','呼吸系统','呼吸的机制','能量和细胞呼吸','人体中的有氧呼吸和无氧呼吸','神经系统的功能','条件反射','视觉的形成机制','内分泌腺','内分泌腺和激素','月经周期','骨骼的结构','泌尿系统','肾单位','皮肤','男性生殖系统的结构','胚胎发育的起始阶段','胎盘和脐带','致病因素和疾病','病毒的发现与特征','引起寄生虫病的变形虫','光合作用','叶片——进行光合作用的主要器官','被子植物——生命周期','种子植物的发育阶段','韧皮部的位置和生长','二倍体、染色体、基因和等位基因','庞纳特方格——棋盘法','抗原和人类的血型','镰刀型细胞贫血症','在原始地球的环境条件下有机物的合成','物种起源的概念','进化的基本机制','38亿年的生命历史','对人类进化的了解','人类进化的大致过程','生物技术的发展历程','人类基因组计划','转基因和克隆技术引发的争论','竞争结果——一方胜利','捕食','捕食者对猎物种群规模的影响','顺从或违背自然规律','生物安全','人类活动改变自然环境','生态系统=生物群落+无机环境','生态系统的组成','信息交流'
      ];
      $biology_url = [
          'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l002_P06/scorm-emt.html?sco=content%2Fscript_00353.emt.xml&title=0353+B4_l002_P06&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l002_P09/scorm-emt.html?sco=content%2Fscript_00356.emt.xml&title=0356+B4_l002_P09&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l002_P10/scorm-emt.html?sco=content%2Fscript_00357.emt.xml&title=0357+B4_l002_P10&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l006_P04/scorm-emt.html?sco=content%2Fscript_00050.emt.xml&title=0050+B4_l006_P04&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l010_P03/scorm-emt.html?sco=content%2Fscript_00083.emt.xml&title=0083+B4_l010_P03&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l010_P04/scorm-emt.html?sco=content%2Fscript_00084.emt.xml&title=0084+B4_l010_P04&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l010_P05/scorm-emt.html?sco=content%2Fscript_00085.emt.xml&title=0085+B4_l010_P05&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l010_P07/scorm-emt.html?sco=content%2Fscript_00087.emt.xml&title=0087+B4_l010_P07&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l011_P03/scorm-emt.html?sco=content%2Fscript_00021.emt.xml&title=0021+B4_l011_P03&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l013_P02/scorm-emt.html?sco=content%2Fscript_00089.emt.xml&title=0089+B4_l013_P02&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l014_P10/scorm-emt.html?sco=content%2Fscript_00106.emt.xml&title=0106+B4_l014_P10&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l016_P02/scorm-emt.html?sco=content%2Fscript_00114.emt.xml&title=0114+B4_l016_P02&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l017_P02/scorm-emt.html?sco=content%2Fscript_00120.emt.xml&title=0120+B4_l017_P02&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l018_P07/scorm-emt.html?sco=content%2Fscript_00131.emt.xml&title=0131+B4_l018_P07&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l019_P05/scorm-emt.html?sco=content%2Fscript_00136.emt.xml&title=0136+B4_l019_P05&api=13',	'http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l020_P04/scorm-emt.html?sco=content%2Fscript_00248.emt.xml&title=0248+B4_l020_P04&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l022_P04/scorm-emt.html?sco=content%2Fscript_00366.emt.xml&title=0366+B4_l022_P04&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l022_P08/scorm-emt.html?sco=content%2Fscript_00370.emt.xml&title=0370+B4_l022_P08&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l023_P02/scorm-emt.html?sco=content%2Fscript_00522.emt.xml&title=0522+B4_l023_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l024_P06/scorm-emt.html?sco=content%2Fscript_00275.emt.xml&title=0275+B4_l024_P06&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l025_P07/scorm-emt.html?sco=content%2Fscript_00533.emt.xml&title=0533+B4_l025_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l026_P02/scorm-emt.html?sco=content%2Fscript_00277.emt.xml&title=0277+B4_l026_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l026_P05/scorm-emt.html?sco=content%2Fscript_00280.emt.xml&title=0280+B4_l026_P05&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l026_P06/scorm-emt.html?sco=content%2Fscript_00281.emt.xml&title=0281+B4_l026_P06&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l027_P03/scorm-emt.html?sco=content%2Fscript_00474.emt.xml&title=0474+B4_l027_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l029_P02/scorm-emt.html?sco=content%2Fscript_00301.emt.xml&title=0301+B4_l029_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l029_P05/scorm-emt.html?sco=content%2Fscript_00304.emt.xml&title=0304+B4_l029_P05&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l030_P02/scorm-emt.html?sco=content%2Fscript_00147.emt.xml&title=0147+B4_l030_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l030_P07/scorm-emt.html?sco=content%2Fscript_00152.emt.xml&title=0152+B4_l030_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l031_P02/scorm-emt.html?sco=content%2Fscript_00153.emt.xml&title=0153+B4_l031_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l034_P06/scorm-emt.html?sco=content%2Fscript_00196.emt.xml&title=0196+B4_l034_P06&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l036_P03/scorm-emt.html?sco=content%2Fscript_00373.emt.xml&title=0373+B4_l036_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l037_P04/scorm-emt.html?sco=content%2Fscript_00383.emt.xml&title=0383+B4_l037_P04&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l038_P02/scorm-emt.html?sco=content%2Fscript_00388.emt.xml&title=0388+B4_l038_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l039_P07/scorm-emt.html?sco=content%2Fscript_00400.emt.xml&title=0400+B4_l039_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l040_P02/scorm-emt.html?sco=content%2Fscript_00686.emt.xml&title=0686+B4_l040_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l043_P03/scorm-emt.html?sco=content%2Fscript_00410.emt.xml&title=0410+B4_l043_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l043_P04/scorm-emt.html?sco=content%2Fscript_00411.emt.xml&title=0411+B4_l043_P04&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l044_P03/scorm-emt.html?sco=content%2Fscript_00420.emt.xml&title=0420+B4_l044_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l045_P02/scorm-emt.html?sco=content%2Fscript_00428.emt.xml&title=0428+B4_l045_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l046_P03/scorm-emt.html?sco=content%2Fscript_00435.emt.xml&title=0435+B4_l046_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l046_P07/scorm-emt.html?sco=content%2Fscript_00439.emt.xml&title=0439+B4_l046_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l047_P02/scorm-emt.html?sco=content%2Fscript_00441.emt.xml&title=0441+B4_l047_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l050_P02/scorm-emt.html?sco=content%2Fscript_00312.emt.xml&title=0312+B4_l050_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l053_P03/scorm-emt.html?sco=content%2Fscript_00702.emt.xml&title=0702+B4_l053_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l055_P02/scorm-emt.html?sco=content%2Fscript_00709.emt.xml&title=0709+B4_l055_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l055_P03/scorm-emt.html?sco=content%2Fscript_00710.emt.xml&title=0710+B4_l055_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l059_P07/scorm-emt.html?sco=content%2Fscript_00348.emt.xml&title=0348+B4_l059_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l060_P02/scorm-emt.html?sco=content%2Fscript_00358.emt.xml&title=0358+B4_l060_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l062_P03/scorm-emt.html?sco=content%2Fscript_00208.emt.xml&title=0208+B4_l062_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l067_P03/scorm-emt.html?sco=content%2Fscript_00332.emt.xml&title=0332+B4_l067_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l067_P06/scorm-emt.html?sco=content%2Fscript_00335.emt.xml&title=0335+B4_l067_P06&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l071_P02/scorm-emt.html?sco=content%2Fscript_00283.emt.xml&title=0283+B4_l071_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l074_P07/scorm-emt.html?sco=content%2Fscript_00568.emt.xml&title=0568+B4_l074_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l075_P03/scorm-emt.html?sco=content%2Fscript_00296.emt.xml&title=0296+B4_l075_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l076_P03/scorm-emt.html?sco=content%2Fscript_00572.emt.xml&title=0572+B4_l076_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l077_P03/scorm-emt.html?sco=content%2Fscript_00308.emt.xml&title=0308+B4_l077_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l078_P02/scorm-emt.html?sco=content%2Fscript_00577.emt.xml&title=0577+B4_l078_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l079_P03/scorm-emt.html?sco=content%2Fscript_00585.emt.xml&title=0585+B4_l079_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l079_P05/scorm-emt.html?sco=content%2Fscript_00587.emt.xml&title=0587+B4_l079_P05&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l082_P02/scorm-emt.html?sco=content%2Fscript_00715.emt.xml&title=0715+B4_l082_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l085_P07/scorm-emt.html?sco=content%2Fscript_00615.emt.xml&title=0615+B4_l085_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l086_P07/scorm-emt.html?sco=content%2Fscript_00621.emt.xml&title=0621+B4_l086_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l088_P02/scorm-emt.html?sco=content%2Fscript_00630.emt.xml&title=0630+B4_l088_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l088_P04/scorm-emt.html?sco=content%2Fscript_00632.emt.xml&title=0632+B4_l088_P04&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l088_P06/scorm-emt.html?sco=content%2Fscript_00634.emt.xml&title=0634+B4_l088_P06&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l094_P05/scorm-emt.html?sco=content%2Fscript_00658.emt.xml&title=0658+B4_l094_P05&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l094_P07/scorm-emt.html?sco=content%2Fscript_00660.emt.xml&title=0660+B4_l094_P07&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l097_P02/scorm-emt.html?sco=content%2Fscript_00668.emt.xml&title=0668+B4_l097_P02&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l098_P03/scorm-emt.html?sco=content%2Fscript_00674.emt.xml&title=0674+B4_l098_P03&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l098_P05/scorm-emt.html?sco=content%2Fscript_00676.emt.xml&title=0676+B4_l098_P05&api=13','http://jingbanyun.com/Public/wb/sw/CN_muc_b4_l100_P08/scorm-emt.html?sco=content%2Fscript_00729.emt.xml&title=0729+B4_l100_P08&api=13'
      ];
      $nameArray = array(($math_resourcename),($chemistry_name),($biology_name));
      $urlArray = array(($math_resourceUrl),($chemistry_url),($biology_url));
      $imgUrlPrefix = 'http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Dynasty/';
      $imgPathArray = array($imgUrlPrefix.'Math/math',$imgUrlPrefix.'Chemistry/chemistry',$imgUrlPrefix.'Biology/biology');
      $imgPlusIdArray = array(2,1,1);
      //var_dump($nameArray);exit;
      $importData['resource_type'] = 3;
      $importData['charge_type'] = 1;
      $importData['charge_time'] = 0;
      $importData['status'] = 1;
      $importData['create_at'] = time();
      for($i=0;$i<sizeof($nameArray);$i++) {
          for ($index = 0; $index < sizeof($nameArray[$i]); $index++) {
              //var_dump($nameArray[$i]);exit;
              $model = M();
              $model->startTrans();
              $importData['name'] = $nameArray[$i][$index];
              $importData['pc_cover'] = $imgPathArray[$i].($index+$imgPlusIdArray[$i]).'.png';
              $importData['mobile_cover'] = $importData['pc_cover'];
              $knowledgeResourceModel = D('Knowledge_resource');
              $resourceId = $knowledgeResourceModel->addResourceData($importData);
              if (!$resourceId) {
                  $model->rollback();
                  continue;
              }
              $contactData['resource_id'] = $resourceId;
              $contactData['resource_path'] = $urlArray[$i][$index];
              $contactId = $knowledgeResourceModel->addResourceContactData($contactData);
              if (!$contactId) {
                  $model->rollback();
                  continue;
              }
              $model->commit();
          }
      }
  }
    public static function JSON($str){
        //$json = json_encode($str);
        return preg_replace("#\\\u([0-9a-f]+)#ie","iconv('UCS-2','UTF-8', pack('H4', '\\1'))",$str);
    }

    public function safeScanTest()
  {
      $config = array(
          'temp_table' => array('name' => URL)
      );
      $sql = "INSERT INTO `biz_lesson_planning_contact` (`biz_lesson_planning_id`,`content`) VALUES ('559','\n\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t<div class=\\\"design-element ui-draggable ui-draggable-handle asd ui-draggable-dragging ui-resizable\\\" style=\\\"width: 284px; display: block; position: absolute; right: auto; height: 315px; bottom: auto; left: 287px; top: 144px;\\\"><div id=\\\"3616\\\" class=\\\"design-content content-image ppttitle\\\" style=\\\"color: rgb(102, 102, 102); min-width: 0px; width: 100%;\\\" data-type=\\\"image\\\" data-src=\\\"http:\/\/jbyoss.oss-cn-beijing.aliyuncs.com\/Resources\/teacher\/2017-06-15\/20170615090538W8nfBJfWYs_w.jpg\\\" title=\\\" Lesson3-\u6a21\u57571&amp;2\\\" title1=\\\" Lesson3-\u6a21\u57571&amp;2\\\" data-child=\\\"Resources\/teacher\/2017-06-15\/20170615090538W8nfBJfWYs_w.jpg\\\" oss-path=\\\"http:\/\/jbyoss.oss-cn-beijing.aliyuncs.com\/\\\" data-category=\\\"bj\\\" contactid=\\\"6922\\\" is=\\\"0\\\"><img src=\\\"http:\/\/jbyoss.oss-cn-beijing.aliyuncs.com\/Resources\/teacher\/2017-06-15\/20170615090538W8nfBJfWYs_w.jpg\\\" class=\\\"design-content content-image abc Mefoverimg\\\" type=\\\"image\\\" resid=\\\"3616\\\" category=\\\"bj\\\" style=\\\"display: none; width: 100%;\\\" contactid=\\\"6922\\\"><\/div><div class=\\\"ui-resizable-handle ui-resizable-e\\\" style=\\\"z-index: 90;\\\"><\/div><div class=\\\"ui-resizable-handle ui-resizable-s\\\" style=\\\"z-index: 90;\\\"><\/div><div class=\\\"ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se\\\" style=\\\"z-index: 90;\\\"><\/div><\/div>')";
      //$sql=urldecode($sql);
      $sql=$this->JSON($sql);




      $controller = new \Home\Controller\SafeScanController();
      $controller->sqlSafeCheck($sql,0);

  }
}