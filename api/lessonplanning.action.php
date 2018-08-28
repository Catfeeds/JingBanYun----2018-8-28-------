<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';


/*路由*/
$app->post('/my', 'getMy');
$app->post('/details/:id', 'getDetails');
$app->post('/bybookid/:bookId', 'getMyLessonPlanningsByBookId');

//获取我的备课课件
function getMy()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select lp.*,g.grade,g.chinese_code,c.course_name,t.name as textbook from biz_lesson_planning lp inner join dict_grade g on lp.grade_id=g.id inner join dict_course c on lp.course_id=c.id inner join biz_textbook t on lp.textbook_id=t.id where lp.teacher_id=$currentTeacherId order by lp.update_at desc ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}
//获取课件详情
function getDetails($id)
{
    $db = new Database();
    $qr = "select * from biz_lesson_planning where id=$id";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}
//根据教材获取课件
function getMyLessonPlanningsByBookId($bookId)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select l.id,l.name,l.update_at,l.description,l.type,t.name as textbook from biz_lesson_planning l inner join biz_textbook t on l.textbook_id=t.id where l.textbook_id=$bookId order by l.update_at desc ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

$app->get('/unprocessed/:key', 'getUnProcessedPPTs');
function getUnProcessedPPTs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,name,teacher_id,filepath from biz_lesson_planning where file_flag=0 and type='PPT' order by create_at desc";
        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/unprocessedSub/:key', 'getUnProcessedSubPPTs');
function getUnProcessedSubPPTs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,biz_lesson_planning_id as lesson_planning_id,file_path as filepath from biz_lesson_planning_contact where flag=0 and type='PPT'";

        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/updateossflag/:key/:id', 'updateOssFlag');
function updateOssFlag($key, $id)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $count = $_GET["count"];
        $data = array('file_flag' => 1, 'ppt_pages' => $count);
        $db->update('biz_lesson_planning', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}

$app->get('/updateossflagSub/:key/:id', 'updateOssFlagSub');
function updateOssFlagSub($key, $id)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $count = $_GET["count"];
        $data = array('flag' => 1,'ppt_pages' => $count);
        $db->update('biz_lesson_planning_contact', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}

//for doc ppt convert to pdf(only for new version)
$app->get('/unprocessedSubPDF/:key', 'getUnProcessedSubPPTPDFs');
function getUnProcessedSubPPTPDFs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,biz_lesson_planning_id as lesson_planning_id,file_path,LOWER(type) as type from biz_lesson_planning_contact where flag_pdf=0 and type in ('WORD','PPT','PDF')";

        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/updateossflagSubPDF/:key/:id', 'updateOssFlagSubPDF');
function updateOssFlagSubPDF($key, $id)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $data = array('flag_pdf' => 1);
        $db->update('biz_lesson_planning_contact', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}

$app->run();