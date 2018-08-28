<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/myClassrooms', 'getMyClassrooms');
$app->post('/details/:id', 'getDetails');

//获取我的课堂
function getMyClassrooms()
{

    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select ci.*,g.grade,c.name as class_name,co.course_name,t.name as textbook,t.server_path as textbook_server_path from biz_classroom_information ci inner join biz_class c on ci.class_id=c.id inner join dict_grade g on c.grade_id=g.id inner join dict_course co on ci.course_id=co.id inner join biz_textbook t on ci.textbook_id=t.id where ci.teacher_id=$currentTeacherId order by ci.time desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}
//获取课堂详情
function getDetails($id)
{
    $db = new Database();
    $qr = "select ci.*,g.grade,c.name as class_name,co.course_name,t.name as textbook,t.server_path as textbook_server_path from biz_classroom_information ci inner join biz_class c on ci.class_id=c.id inner join dict_grade g on c.grade_id=g.id inner join dict_course co on ci.course_id=co.id inner join biz_textbook t on ci.textbook_id=t.id where ci.id=$id order by ci.time desc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}


$app->run();