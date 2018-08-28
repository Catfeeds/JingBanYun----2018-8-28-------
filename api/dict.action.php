<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';


/*路由*/
$app->get('/course/list', 'getCourses');
$app->post('/course/list', 'getCourses');
$app->post('/grade/list', 'getGrades');
$app->post('/school/list', 'getSchools');
$app->post('/school/search', 'getSchoolsByName');
//获取课程字典
function getCourses()
{
    $db = new Database();
    $qr = "select id,course_name as name from dict_course order by sort_order";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取年级字典
function getGrades()
{
    $db = new Database();
    $qr = "select id,grade as name from dict_grade order by code";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取学校
function getSchools()
{
    $db = new Database();
    $qr = "select id,school_name from dict_school";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取学校根据名称
function getSchoolsByName()
{
    $name = $_POST['schoolName'];
    $db = new Database();
    $qr = "select id,school_name,stage from dict_school where school_name like '%$name%' limit 0,50";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}



$app->run();