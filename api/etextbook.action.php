<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';


/*路由*/
$app->post('/all', 'getAllBooks');
$app->post('/teacherBooks/:teacherId', 'getTeacherBooks');
$app->post('/details/:id', 'getBookDetails');
$app->post('/saveAsMyBook/:bookId/:teacherId', 'saveAsMyBook');

//获取所有电子书
function getAllBooks()
{
    $db = new Database();
    $qr = "select id,name,cover,server_path from biz_textbook where flag=1 and has_ebook=1 order by sort_order asc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    foreach ($result as $r) {
        $r->cover = $r->server_path . "_app/content/2.png";
    }
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取我的电子课本
function getTeacherBooks($teacherId)
{
    $db = new Database();
    $qr = "select t.id,t.name,t.class_id,t.grade_id,t.school_term,t.cover,t.publish_version,t.server_path from biz_teacher_textbook tt inner join biz_textbook t on tt.textbook_id=t.id where t.flag=1 and t.has_ebook=1 and tt.teacher_id=$teacherId order by tt.create_at asc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取课本详情
function getBookDetails($id)
{
    $db = new Database();
    $info = $db->fetch_single_row('biz_textbook', 'id', $id);
    renderJsonResponse(200, '', $info);
    $db = null;
}

//将课本加入我的教材中
function saveAsMyBook($bookId, $teacherId)
{
    $db = new Database();
    $data = array(
        'textbook_id' => $bookId,
        'teacher_id' => $teacherId,
        'create_at' => time()
    );
    try {
        $db->insert("biz_teacher_textbook", $data);
        renderJsonResponse(200, '已将此课本放入你的教材中', $data);
    } catch (Exception $e) {
        renderJsonResponse(200, '已将此课本放入你的教材中', $data);
    }

    $db = null;
}


$app->run();