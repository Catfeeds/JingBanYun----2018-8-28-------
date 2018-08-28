<?php
return array(
    //'配置项'=>'配置值'
    'PAGE_SIZE_FRONT' => 20,//前台每页显示记录数
    'PAGE_SIZE_ADMIN' => 200,//后台每页显示记录数
    'allowed_types'=>array('image/jpeg','image/png','image/gif','image/pjpeg','image/bmp','image/x-png','image/jpg'),         //允许的图片类型
    'file_size'=>5,         //图片尺寸默认5M
    'upload_path'=>array('Resources/jb/','Resources/lessonplanning/','Resources/teacher/','Resources/activity/','Resources/material/'),
    'default_dir'=>1,       //默认创建的目录列:allowed_types[1];
    'media_file_size'=>20,  //媒体默认尺寸10M
    'media_allowed_types'=>array('video/mp4','audio/mp3','application/msword','application/pdf','application/x-shockwave-flash','application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                 'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','video/quicktime'),
    'all_file_size'=>200,  //全部类型尺寸
    'allow_allowed_types'=>array('image/jpeg','image/png','image/gif','image/pjpeg','image/bmp','image/x-png','image/jpg','video/mp4','audio/mpeg','audio/mp3','application/msword','application/pdf','application/x-shockwave-flash',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','video/quicktime',
        'application/zip','application/octet-stream'),//全部文件类型
    'oss_path'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/'
);
