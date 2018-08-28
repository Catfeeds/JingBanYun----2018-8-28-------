$.get('index.php?m=Home&c=Common&a=getMessageIsBox', {
}, function (res) {
    if ( res.sendinfo!='' && res.sendinfo.length>0 ) {
        $.NotifyBox.NotifyPromptOne(res.sendinfo, "确定");
    }
},'json')