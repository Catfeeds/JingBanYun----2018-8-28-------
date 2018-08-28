<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/latest/:pageIndex', 'getMyMessagesForTeacher');
$app->post('/published/:pageIndex', 'getMyPublishedInformation');
$app->post('/details/:id', 'getInformationDetails');
$app->post('/reply/:id', 'reply');
$app->post('/getfirst', 'getFirstInformation');
$app->post('/publish', 'publish');
$app->post('/reply/details/:id', 'getReplyDetails');

function getMyMessagesForTeacher($pageIndex)
{
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $startIndex = ($pageIndex - 1) * 50;

    //userid,token school数据

    $db = new Database();

    //获取自己单独的

    //获取群里的

    $qr = "select m.*,mr.reply_at,mr.reader_type from oa_message m left join oa_message_reply mr on m.id=mr.message_id where m.status=2 and m.group_id=1 order by m.create_at desc LIMIT $startIndex, 50 ";


    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);

}

function getMyPublishedInformation($pageIndex)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $startIndex = ($pageIndex - 1) * 50;
    $db = new Database();

    $qr = "select m.*,mr.reply_at,mr.reader_type from oa_message m left join oa_message_reply mr on m.id=mr.message_id where m.publisher_id=$currentTeacherId order by m.create_at desc LIMIT $startIndex, 50 ";

    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);

}

function getFirstInformation()
{
    $db = new Database();

    $qr = "select m.*,mr.reply_at,mr.reader_type from oa_message m left join oa_message_reply mr on m.id=mr.message_id where m.status=2 and m.group_id=1 order by m.create_at desc LIMIT 0, 1 ";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);
    $db = null;
    renderJsonResponse(200, '', $result);
}


function getInformationDetails($id)
{
    $db = new Database();

    $qr = "select m.*,mr.reply_at,mr.reader_type from oa_message m left join oa_message_reply mr on m.id=mr.message_id where m.id=$id";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    renderJsonResponse(200, '', $result);
    $db = null;
}

function reply($id)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;
    $currentTeacherName = $currentTeacher->name;

    $db = new Database();

    $data = array(
        'reader_id' => $currentTeacherId,
        'message_id' => $id,
        'reader' => $currentTeacherName,
        'reply_at' => time(),
        'content' => '',
        'reader_type' => 1,
    );

    $db->insert("oa_message_reply", $data);

    renderJsonResponse(200, '回执成功', $data);

    $db = null;

}

function publish()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;
    $currentTeacherName = $currentTeacher->name;

    $title = $_POST['title'];
    $content = $_POST['content'];
    $is_need_receipt = $_POST['is_need_receipt'];
    $target_classes = $_POST['classes'];

    if (empty($title) || empty($content)) {
        renderJsonResponse(406, '标题和内容不能为空', array());
        return;
    }

    $classArray = explode(",", $target_classes);

    $db = new Database();

    $data = array(
        'title' => $title,
        'brief_content' => strlen($content) > 20 ? substr($content, 0, 19) . "..." : $content,
        'content' => $content,
        'publisher_id' => $currentTeacherId,
        'publisher' => $currentTeacherName,
        'create_at' => time(),
        'update_at' => time(),
        'group_id' => 2,
        'is_need_receipt' => $is_need_receipt
    );

    $messageId = $db->insert("oa_message", $data);

    foreach ($classArray as $classId) {
        if (empty($classId)) continue;
        $checkData = array(
            'message_id' => $messageId,
            'class_id' => $classId
        );

        $checkResult = $db->check_exist('oa_message_parents', $checkData);

        if ($checkResult == true) continue;

        $db->insert('oa_message_parents', $checkData);
    }

    renderJsonResponse(200, '发布消息成功', array());

    $db = null;
}

function getReplyDetails($id)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();

    $qr = "select reader_id,reader from oa_message_reply where message_id=$id order by reply_at desc";

    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);
}

$app->run();