<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/11/16
 * Time: 17:44
 */

namespace ApiInterface\Controller\Version1_2;

class PictureBooksController extends PublicController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }

    /**
     *绘本阅读列表页
     * @参数：userId[int] 用户ID
     * @参数：role[int] 用户role
     */
    public function pictureBookList()
    {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        /**********************反查所有的年级********************************/
        $gradeList = D('Picture_books')->reverseSearch();
        $this->assign('gradeList', $gradeList);
        /**********************查询所有的主题********************************/
        $theme = C('PICTURE_BOOK_THEME');
        $this->assign('themeList', $theme);
        /**********************查询所有的年题材*******************************/
        $subject = C('PICTURE_BOOK_SUBJECT');
        $this->assign('subjectList', $subject);
        $result = D('Picture_books')->getAllBySearch();
        /****************************查看当前登录用户是否看过绘本，如果看过给个状态标识*****************************/
        if($role == ROLE_YOUKE){ //游客暂时没有已读状态先预留

        }else{
            if ($role == ROLE_TEACHER) {
                $codetion['teacher_id'] = $userId;
            } elseif ($role == ROLE_STUDENT) {
                $codetion['student_id'] = $userId;
            } else {
                $codetion['parent_id'] = $userId;
            }
            /*********************获得当前登录用户所有看过绘本的ID************************************/
            $pictureBooksId = D('Picture_books')->getResourceByWhere($codetion);
            $pictureBooksIdArr = array_column($pictureBooksId, 'picturebooks_id');
            $pictureBooksIdNewArr = array_unique($pictureBooksIdArr);
            foreach ($result as $k => $item) {
                if (in_array($item['picture_books_id'], $pictureBooksIdNewArr)) {
                    $result[$k]['tip'] = 'true';//给个标识
                }
            }
        }
        $this->assign('userId', $userId);
        $this->assign('role', $role);
        $this->assign('list', $result);
        $this->display();
    }

    /**
     * @描述：绘本阅读ajax请求筛选
     * @参数：subjectId[int] 绘本题材ID
     * @参数：grade_id[int] 绘本年级ID
     * @参数：theme[int] 绘本主题ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getPictureBookListBySearch()
    {

        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $subjectId = getParameter('subjectId', 'int', false);
        $gradeId = getParameter('grade_id', 'int', false);
        $theme = getParameter('theme', 'int', false);
        if (!empty($subjectId)) {
            $codetion['picture_books_type'] = $subjectId;//题材
        }
        if (!empty($gradeId)) {
            $codetion['picture_books_grade'] = $gradeId;
        }
        if (!empty($theme)) {
            $codetion['picture_books_subject'] = $theme;//主题
        }
        $result = D('Picture_books')->getAllBySearch($codetion);
        /****************************查看当前登录用户是否看过绘本，如果看过给个状态标识*****************************/
        if($role == ROLE_YOUKE) {//游客暂时没有已读状态先预留

        }else{
            if ($role == ROLE_TEACHER) {
                $tip['teacher_id'] = $userId;
            } elseif ($role == ROLE_STUDENT) {
                $tip['student_id'] = $userId;
            } else {
                $tip['parent_id'] = $userId;
            }
            /*********************获得当前登录用户所有看过绘本的ID************************************/
            $pictureBooksId = D('Picture_books')->getResourceByWhere($tip);
            $pictureBooksIdArr = array_column($pictureBooksId, 'picturebooks_id');
            $pictureBooksIdNewArr = array_unique($pictureBooksIdArr);
            foreach ($result as $k => $item) {
                if (in_array($item['picture_books_id'], $pictureBooksIdNewArr)) {
                    $result[$k]['tip'] = 'true';//给个标识
                }
            }
        }
        $this->showjson(200, '', $result);
    }

    /**
     *@描述：排行榜展示
     * @参数：id[int] 绘本ID
     * @参数：userId[int] 用户ID
     * @参数：role[int] 用户role
     */
    public function pictureBooks()
    {
        $id = getParameter('id', 'int');//绘本的ID
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $this->assign('userId', $userId);
        $this->assign('role', $role);
        if($role == ROLE_YOUKE) {//游客暂时没有先预留

        }else{
            //查询所有学生的积分并排序
            $result = D('Picture_books')->getAllByRankingList($id);
            foreach ($result as $key => $value) {
                $this->getAvatar($result[$key]);//根据不同用户角色拿到不同的头像
                //查询当前用户的读了几个发声点(改需求了)
                /*$codetion = array();
                if ($value['role'] == ROLE_TEACHER) {
                    $codetion['teacher_id'] = $value['teacher_id'];
                } elseif ($value['role'] == ROLE_STUDENT) {
                    $codetion['student_id'] = $value['student_id'];
                } elseif ($value['role'] == ROLE_PARENT) {
                    $codetion['parent_id'] = $value['parent_id'];
                }
                $codetion['pictureBooks_id'] = $id;
                $pictureBooksId = D('Picture_books')->getResourceByWhere($codetion);
                $num = count($pictureBooksId);*/
                $codetion['picture_books_id'] = $id;
                $pictureBooks = D('Picture_books')->getAllBySearch($codetion);
                $num = $pictureBooks[0]['picturebooksofpart_url_num'];
                //用总分除以发声点个数获得平均分
                $result[$key]['total_score'] = intval(round($value['total_score'] / $num));
                //$newArr[$result[$key]['total_score']] = $result[$key];//把平均分作为数字下标，然后在排序
            }
            //rsort($newArr);
            //根据平均分重新排列数组
            $newArr = $this->insertSort($result);
            //获取当前登录的用户的信息如果有此用户信息的话
            foreach ($newArr as $k => $item) {
                if ($item['role'] == $role && ($item['teacher_id'] == $userId || $item['parent_id'] == $userId || $item['student_id'] == $userId)) {
                    $resultByUser = $item;
                    $ranking = $k + 1;
                }
            }
        }
        //查询绘本的名称
        $condetion['picture_books_id'] = $id;
        $pictureName = D('Picture_books')->getAllBySearch($condetion);
        $this->assign('pictureName',$pictureName[0]['picture_books_name']);
        $this->assign('rankingList', $newArr);
        $this->assign('resultByUser', $resultByUser);//当前登录用户的信息
        $this->assign('ranking', $ranking);//当前登录用户的排名
        $this->display();
    }

    /**
     *描述：排序算法
     */
    private function insertSort($arr)
    {
        $len = count($arr);
        for ($i = 1; $i < $len; $i++) {
            $tmp = $arr[$i];
            //内层循环控制，比较并插入
            for ($j = $i - 1; $j >= 0; $j--) {
                if ($tmp > $arr[$j]) {
                    //发现插入的元素要大，交换位置，将后边的元素与前面的元素互换
                    $arr[$j + 1] = $arr[$j];
                    $arr[$j] = $tmp;
                } else {
                    //如果碰到不需要移动的元素，由于是已经排序好是数组，则前面的就不需要再次比较了。
                    break;
                }
            }
        }
        return $arr;
    }

    private function getAvatar(&$result)
    {
        if ($result['role'] == ROLE_TEACHER) {

            if (preg_match('/Resources/', $result['teacher_avatar'])) {
                if (strpos($result['teacher_avatar'], '.') === false) {
                    $result['teacher_avatar'] .= '.jpg';
                }
                $result['teacher_avatar'] = C('oss_path') . $result['teacher_avatar'];
            } else {
                if ($result['ts'] == '男' || empty($result['ts'])) {
                    $result['teacher_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_m.png';
                } else {
                    $result['teacher_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_STUDENT) {


            if (preg_match('/Resources/', $result['student_avatar'])) {
                if (strpos($result['student_avatar'], '.') === false) {
                    $result['student_avatar'] .= '.jpg';
                }
                $result['student_avatar'] = C('oss_path') . $result['student_avatar'];
            } else {

                if ($result['ss'] == '男' || empty($result['ss'])) {
                    $result['student_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_m.png';
                } else {
                    $result['student_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_w.png';
                }

            }
        } else {


            if (preg_match('/Resources/', $result['parent_avatar'])) {
                if (strpos($result['parent_avatar'], '.') === false) {
                    $result['parent_avatar'] .= '.jpg';
                }
                $result['parent_avatar'] = C('oss_path') . $result['parent_avatar'];
            } else {

                if ($result['ps'] == '男' || empty($result['ps'])) {
                    $result['parent_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang.png';
                } else {
                    $result['parent_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang2.png';
                }

            }
        }
    }

    /**
     * @描述：排行榜入库积分
     * @参数：pictureBooksOfPart_Url[str] 绘本内容中发声点的url地址
     * @参数：pictureBooks_id[int] 绘本ID
     * @参数：userId[int] 用户ID
     * @参数：score[int] 得分
     * @参数：role[int] 角色
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function increaseTheIntegral()
    {
        $pictureBooksOfPart_Url = getParameter('pictureBooksOfPart_Url', 'str'); //绘本内容中发声点的Url
        $pictureBooks_id = getParameter('pictureBooks_id', 'int'); //绘本ID
        $user_id = getParameter('userId', 'int'); //用户ID
        $score = getParameter('score', 'int'); //得分
        $role = getParameter('role', 'int');//角色
        if($role == ROLE_YOUKE) {//游客暂时没有先预留
            $this->showjson(200);
        }else {
            //1.如果数据库中这个ID存在则执行更新表数据的操作

            if ($role == ROLE_STUDENT) {
                $condetion['student_id'] = !empty($user_id) ? $user_id : null;
            }
            if ($role == ROLE_PARENT) {
                $condetion['parent_id'] = !empty($user_id) ? $user_id : null;
            }
            if ($role == ROLE_TEACHER) {
                $condetion['teacher_id'] = !empty($user_id) ? $user_id : null;
            }
            $condetion['role'] = !empty($role) ? $role : null;
            $condetion['pictureBooksOfPart_Url'] = !empty($pictureBooksOfPart_Url) ? $pictureBooksOfPart_Url : null;
            $condetion['pictureBooks_id'] = !empty($pictureBooks_id) ? $pictureBooks_id : null;
            $condetion['score'] = !empty($score) ? $score : 0;
            //把接收到的发声点URL路径做一下截取，只要分页的值
            preg_match('/page\d+/', $pictureBooksOfPart_Url, $pageStr);
            preg_match('/\d+/', $pageStr[0], $page);
            $condetion['page'] = $page[0];
            //查看该用户是否跟读过此发声点的语音
            $empty = D('Picture_books')->getResourceByWhere($condetion);//查询
            D('Picture_books')->startTrans();
            if (!empty($empty)) {
                //1.1先查询一下该发声点的所得分数，如果新的分数比旧的分数大则执行更新表数据的操作
                if ($empty[0]['score'] < $condetion['score']) {
                    if (D('Picture_books')->updataResource($condetion) === false) {
                        D('Picture_books')->rollback();
                        $this->showjson(400);
                    } else {
                        D('Picture_books')->commit();
                        $this->showjson(200);
                    }
                } else {
                    $this->showjson(200, '不做任何操作');
                }
            } else {
                //2如果数据库中这个ID不存在则执行插入的操作
                if (D('Picture_books')->insertResource($condetion) === false) {
                    D('Picture_books')->rollback();
                    $this->showjson(500);
                } else {
                    D('Picture_books')->commit();
                    $this->showjson(200);
                }
            }
        }
    }

    /**
     *描述：绘本每页的分数
     *@参数：id[int] 绘本ID
     *@参数：userId[int] 用户ID
     *@参数：role[int] 用户role
     *@参数：pictureBooksOfPart_Url[str] 发声点的URL地址
     */
    public function getScoreByPage()
    {
        $pictureBooks_id = getParameter('id', 'int');//绘本的ID
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $pictureBooksOfPart_Url = getParameter('pictureBooksOfPart_Url', 'str');//发声点的URL地址
        if($role == ROLE_YOUKE) {//游客暂时没有先预留
            $this->showjson(200, '没有数据');
        }else {
            if ($role == ROLE_STUDENT) {
                $condetion['student_id'] = !empty($userId) ? $userId : null;
            }
            if ($role == ROLE_PARENT) {
                $condetion['parent_id'] = !empty($userId) ? $userId : null;
            }
            if ($role == ROLE_TEACHER) {
                $condetion['teacher_id'] = !empty($userId) ? $userId : null;
            }
            $condetion['role'] = !empty($role) ? $role : null;
            $condetion['pictureBooksOfPart_Url'] = !empty($pictureBooksOfPart_Url) ? $pictureBooksOfPart_Url : null;
            $condetion['pictureBooks_id'] = !empty($pictureBooks_id) ? $pictureBooks_id : null;
            $result = D('Picture_books')->getResourceByWhere($condetion);
            if (!empty($result)) {
                $this->showjson(200, '', $result[0]['score']);
            } else {
                $this->showjson(200, '没有数据');
            }
        }
    }

    /**
     *描述：进入绘本详情页就给浏览量加1
     * @参数：ID[int] 绘本ID
     */
    public function clickNum(){
        //进入绘本详情页就给该绘本添加点击量操作
        $pictureBooks_id = getParameter('id', 'int');//绘本的ID
        $where['picture_books_id'] = $pictureBooks_id;
        D('Picture_books')->clickNum($where);
        $this->showjson(200);
    }
}
