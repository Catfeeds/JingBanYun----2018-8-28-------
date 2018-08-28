<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/myClasses', 'getMyClasses');
$app->post('/details/:id', 'getDetails');
$app->post('/students/:id', 'getStudentsInClass');


function getMyClasses()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;
    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select c.*,s.school_name,g.grade from biz_class c inner join dict_school s on c.school_id=s.id inner join dict_grade g on c.grade_id=g.id where class_teacher_id=$currentTeacherId order by c.name asc LIMIT $startIndex, $pageSize";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function getDetails($id)
{
    $db = new Database();
    $qr = "select c.*,s.school_name,g.grade from biz_class c inner join dict_school s on c.school_id=s.id inner join dict_grade g on c.grade_id=g.id where c.id=$id order by c.name asc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function getStudentsInClass($id)
{
    $db = new Database();
    $qr = "select s.id,s.student_name,s.student_id,s.user_name,s.avatar from biz_class_student cs inner join auth_student s on cs.student_id=s.id inner join biz_class c on cs.class_id=c.id where cs.class_id=$id order by s.student_name asc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

$app->run();