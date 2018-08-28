<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/11/16
 * Time: 17:44
 */

namespace Common\Model;

use Think\Model;

class Picture_booksModel extends Model
{

    public $model = '';
    protected $tableName = 'picture_books';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);

    }

    /**
     * 公共的拼接WHERE条件的方法
     */
    private function getConditionWhere($condition)
    {
        $whereStr = '';
        foreach ($condition as $key => $val) {
            if (false !== $val && null !== $val) {
                switch ($key) {
                    case 'student_id':
                        $whereStr .= " picture_books_ranking_list.student_id = $val";
                        break;
                    case 'parent_id':
                        $whereStr .= " picture_books_ranking_list.parent_id = $val";
                        break;
                    case 'teacher_id':
                        $whereStr .= " picture_books_ranking_list.teacher_id = $val";
                        break;
                    case 'pictureBooksOfPart_Url':
                        $whereStr .= " AND picture_books_ranking_list.pictureBooksOfPart_Url = '$val'";
                        break;
                    case 'pictureBooks_id':
                        $whereStr .= " AND picture_books_ranking_list.pictureBooks_id = $val ";
                        break;
                    case 'book_category':
                        $whereStr .= " AND picture_books_ranking_list.book_category = $val ";
                        break;
                }
            }
        }
        if(empty($condition['book_category'])){
            $whereStr .= " AND picture_books_ranking_list.book_category = 1 ";
        }
        return $whereStr;
    }

    /**
     * 公共的拼接字段条件的方法
     */
    private function getConditionFiled($condition)
    {
        foreach ($condition as $key => $val) {
            if (false !== $val && null !== $val) {
                switch ($key) {
                    case 'student_id':
                        $filedStr = "student_id ";
                        break;
                    case 'parent_id':
                        $filedStr = "parent_id ";
                        break;
                    case 'teacher_id':
                        $filedStr = "teacher_id ";
                        break;
                }
            }
        }
        return $filedStr;
    }

    /**
     * 公共的拼接字段值的方法
     */
    private function getConditionFiledValue($condition)
    {
        foreach ($condition as $key => $val) {
            if (false !== $val && null !== $val) {
                switch ($key) {
                    case 'student_id':
                        $filedValue = $condition['student_id'];
                        break;
                    case 'parent_id':
                        $filedValue = $condition['parent_id'];
                        break;
                    case 'teacher_id':
                        $filedValue = $condition['teacher_id'];
                        break;
                }
            }
        }
        return $filedValue;
    }

    /**
     *查询所有数据并分页
     */
    public function getAll($where, $IndexPage, $PageSize = 20)
    {
        $where['picture_books_status'][] = array('neq',DELETE);
        $result['count'] = $this->model
            ->where($where)
            ->count();
        $result['data'] = $this->model
            ->where($where)
            ->page($IndexPage, $PageSize)
            ->select();
        return $result;
    }

    /**
     *查询无分页
     */
    public function getAllBySearch($where = array())
    {
        $where['picture_book_shelf'] = ONSHELF;
        $where['picture_books_status'] = AUDIT_YES;
        $result = $this->model
            ->where($where)
            ->select();
        return $result;

    }

    /**
     *@描述：绘本阅读排行榜 列表
     */
    public function getAllByRankingList($id,$bookCategory=1)
    {
        $result = M()
            ->query("SELECT
                     	SUM(score) total_score,picture_books_ranking_list.*,auth_student.sex ss,auth_parent.sex ps,auth_teacher.sex ts,
                     	auth_student.student_name,auth_teacher.name,auth_parent.parent_name,auth_student.avatar student_avatar,auth_teacher.avatar teacher_avatar,auth_parent.avatar parent_avatar
                     FROM
                     	picture_books_ranking_list
                     left JOIN auth_student ON auth_student.id = picture_books_ranking_list.student_id
                     left JOIN auth_parent ON auth_parent.id = picture_books_ranking_list.parent_id
                     left JOIN auth_teacher ON auth_teacher.id = picture_books_ranking_list.teacher_id
                     WHERE
                     	pictureBooks_id = $id AND book_category = $bookCategory
                     GROUP BY teacher_id,parent_id,student_id
                     ORDER BY total_score DESC "); //echo M()->getLastSql();die;
        return $result;
    }

    /**
     * 描述：根据条件查询绘本积分表
     */
    public function getResourceByWhere($condetion)
    {
        $where = $this->getConditionWhere($condetion);
        $result = M()->query("
        SELECT
	picture_books_ranking_list.id,picture_books_ranking_list.score,picture_books_ranking_list.pictureBooks_id
FROM
	picture_books_ranking_list
WHERE $where
        ");
        return $result;
    }

    /**
     *描述：往绘本排行榜表中插数据操作
     *
     */
    public function insertResource($condetion)
    {
        $filed = $this->getConditionFiled($condetion);
        $filedValue = $this->getConditionFiledValue($condetion);
        if(empty($condetion['book_category']))
            $condetion['book_category'] = 1;
        if(empty($condetion['user_voice_url'])){
            $condetion['user_voice_url'] = '';
        }
        $result = M()->execute("
        INSERT INTO picture_books_ranking_list(
    user_voice_url,    
    book_category,    
	pictureBooksOfPart_Url,
	pictureBooks_id,
	$filed,
	score,
	page,
	role
)
VALUE
	('{$condetion['user_voice_url']}',{$condetion['book_category']},'" . $condetion['pictureBooksOfPart_Url'] . "'," . $condetion['pictureBooks_id'] . "," . $filedValue . "," . $condetion['score'] . "," . $condetion['page'] . "," .$condetion['role'] . ")
        ");
        return $result;
    }

    /**
     *描述：绘本排行榜表中修改数据操作
     *
     */
    public function updataResource($condetion)
    {
        $where = $this->getConditionWhere($condetion);
        if(empty($condetion['user_voice_url'])){
            $condetion['user_voice_url'] = '';
        }

        $result = M()->execute("
        UPDATE picture_books_ranking_list
SET user_voice_url = '{$condetion['user_voice_url']}',score = " . $condetion ['score'] . "
WHERE
	$where
        ");
        return $result;
    }

    /**
     * 描述：修改绘本审核状态的操作
     */
    public function updataPictureBooks($condetion){
        $result = M()->execute("
        UPDATE picture_books
SET picture_books_status = " . $condetion ['status'] . "
WHERE
	picture_books_id = " .$condetion['id'] ."
        ");
        return $result;
    }
    /**
     * 描述：修改绘本上下架状态的操作
     */
    public function updataPictureBooksShelf($condetion){
        $result = M()->execute("
        UPDATE picture_books
SET picture_book_shelf = " . $condetion ['status'] . "
WHERE
	picture_books_id = " .$condetion['id'] ."
        ");
        return $result;
    }

    /**
     *描述：绘本创建
     */
    public function addPictureBooks($condetion){
        $this->model
            ->add($condetion);
    }

    /**
     *描述：绘本的修改
     */
    public function savePictureBooks($condetion,$where){
        $result = $this->model
            ->where($where)
            ->save($condetion);
        return $result;
    }

    /**
     * 描述：绘本年级反查
     */
    public function reverseSearch($where){
        $where['picture_books_status'] = array('neq',DELETE);
        $result = $this->model
            ->field('dict_grade.grade name,dict_grade.id')
            ->join("dict_grade on dict_grade.id = picture_books.picture_books_grade")
            ->where($where)
            ->group('dict_grade.id')
            ->select();
        return $result;
    }

    /**
     *描述：获取一条数据
     */
    public function getOneResource($where){
        $result = $this->model
            ->where($where)
            ->find();
        return $result;
    }

    /**
     *描述：绘本点击量
     */
    public function clickNum($where){
        $result = $this->model
            ->where($where)
            ->setInc('click_count',1);
        return $result;
    }

    public function getTextBookVoiceRankList($url,$bookCategory,$pageIndex=1,$pageSize=10)
    {
        if(empty($url) || empty($bookCategory))
            return [];
        $startIndex = ($pageIndex-1)*$pageSize;
            try {
                $result = M()
                    ->query("SELECT rank,score,sex,name,avatar,role FROM (SELECT
                        CASE
                            WHEN @last_score <> score THEN @lastrank := @rownum ELSE @lastrank        
                        END last_rank,
                        
                        CASE
                            WHEN @last_score = score THEN @lastrank
                            ELSE @rownum
                        END rank,                         
                        @rownum := @rownum +1,
                        @last_score:=score,
                     	score,picture_books_ranking_list.role,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.sex when picture_books_ranking_list.role = 3 THEN auth_student.sex ELSE auth_parent.sex END) sex,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.name when picture_books_ranking_list.role = 3 THEN auth_student.student_name ELSE auth_parent.parent_name END) name,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.avatar when picture_books_ranking_list.role = 3 THEN auth_student.avatar ELSE auth_parent.avatar END) avatar                    	
                     FROM
                     	picture_books_ranking_list
                     left JOIN auth_student ON auth_student.id = picture_books_ranking_list.student_id
                     left JOIN auth_parent ON auth_parent.id = picture_books_ranking_list.parent_id
                     left JOIN auth_teacher ON auth_teacher.id = picture_books_ranking_list.teacher_id,
                     (SELECT @rownum:=1,  @last_score:=0) r
                     WHERE
                     	pictureBooksOfPart_Url = '$url' AND book_category = $bookCategory
                     ORDER BY score DESC LIMIT $startIndex,$pageSize) a"); //echo M()->getLastSql();die;
            }catch(\Exception $e){;}

        return $result;
    }

    public function getSelfVoiceRank($url,$role,$userId)
    {
            $result = M()
                ->query("SELECT rank,score,user_voice_url url,sex,name,avatar,role FROM (SELECT
                        CASE
                            WHEN @last_score <> score THEN @lastrank := @rownum ELSE @lastrank        
                        END last_rank,
                        
                        CASE
                            WHEN @last_score = score THEN @lastrank
                            ELSE @rownum
                        END rank,                         
                        @rownum := @rownum +1,
                        @last_score:=score,
                     	score,user_voice_url,picture_books_ranking_list.role,
                     	 (case when picture_books_ranking_list.role = 2 THEN teacher_id when picture_books_ranking_list.role = 3 THEN picture_books_ranking_list.student_id ELSE picture_books_ranking_list.parent_id END) user_id,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.sex when picture_books_ranking_list.role = 3 THEN auth_student.sex ELSE auth_parent.sex END) sex,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.name when picture_books_ranking_list.role = 3 THEN auth_student.student_name ELSE auth_parent.parent_name END) name,
                     	 (case when picture_books_ranking_list.role = 2 THEN auth_teacher.avatar when picture_books_ranking_list.role = 3 THEN auth_student.avatar ELSE auth_parent.avatar END) avatar
                     FROM
                     	picture_books_ranking_list
                     left JOIN auth_student ON auth_student.id = picture_books_ranking_list.student_id
                     left JOIN auth_parent ON auth_parent.id = picture_books_ranking_list.parent_id
                     left JOIN auth_teacher ON auth_teacher.id = picture_books_ranking_list.teacher_id,
                      (SELECT @rownum:=1,  @last_score:=0) r
                     WHERE
                     	pictureBooksOfPart_Url = '$url'               
                     ORDER BY score DESC) a WHERE role=$role AND user_id=$userId");

        return empty($result[0]) ? [] : $result[0];
    }

    public function getTextbookNameByVoiceUrl($url)
    {
        $result = M()->query("SELECT name FROM (SELECT pictureBooks_id id FROM picture_books_ranking_list WHERE pictureBooksOfPart_Url = '$url' LIMIT 1) a JOIN biz_textbook 
                              ON biz_textbook.id = a.id LIMIT 1");
        return $result[0]['name'];
    }
}