<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/homework', 'getHomeworkList');
$app->post('/details/:id', 'getDetails');

//获取作业列表
function getHomeworkList()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select h.*,c.name as class_name,co.course_name,t.name as textbook from biz_homework h inner join biz_class c on h.class_id=c.id inner join dict_grade g on h.grade_id=g.id inner join dict_course co on h.course_id= co.id inner join biz_textbook t on h.textbook_id=t.id where h.teacher_id=$currentTeacherId order by h.create_at desc LIMIT $startIndex, $pageSize";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取作业详情
function getDetails($id)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select h.*,c.name as class_name,co.course_name,t.name as textbook from biz_homework h inner join biz_class c on h.class_id=c.id inner join dict_grade g on h.grade_id=g.id inner join dict_course co on h.course_id= co.id inner join biz_textbook t on h.textbook_id=t.id where h.id=$id order by h.create_at desc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

$app->post('/homework/submit', 'submitHomework');
function submitHomework()
{

}

$app->post('/homework/completedetails', 'getHomeworkCompleteDetails');
function getHomeworkCompleteDetails()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $classId = $_POST['class_id'];
    $homeworkId = $_POST['homework_id'];

    $db = new Database();
    $qr1 = "select hsd.*,s.student_name,s.id as student_id from biz_homework_student_details hsd inner join auth_student s on hsd.student_id=s.id where hsd.homework_id=$homeworkId order by hsd.create_at desc";
    $sel1 = $db->fetch_custom($qr1, array());
    $details = pushToArray($sel1);

    $qr2 = "select s.id,s.student_id,s.student_name,0 as create_at,0 as duration,0 as points,0 as status,0 as homework_id from biz_class_student cs inner join auth_student s on cs.student_id=s.id where cs.class_id=$classId order by s.id asc";
    $sel2 = $db->fetch_custom($qr2, array());
    $students = pushToArray($sel2);

    $i = 0;
    $outlist = array();
    foreach ($students as $student) {
        foreach ($details as $r) {
            if ($student->id == $r->student_id) {
                $student->create_at = $r->create_at;
                $student->duration = $r->duration;
                $student->points = $r->points;
                $student->status = $r->status;
                $student->homework_id = $r->id;
                break;
           }
        }
        $outlist[$i] = $student;
        $i = $i + 1;
    }

    $db = null;
    renderJsonResponse(200, '', $students);
}

$app->run();