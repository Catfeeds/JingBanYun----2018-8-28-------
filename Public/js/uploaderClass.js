(function (global) {
    var uploaderOss = function(element) {
        "use strict";
    }
        var owner;
        var uploadInfo={};
        uploaderOss.setUploader=function(idName, fileTypeIndex, filterArray,mutiple,thumbNailFun,imageRatio)
           {
            function getUploaderIndex(obj) {
                var index = -1;
                $(Object.keys(owner.uploaderObject)).each(function (i, n) {
                    if (obj == owner.uploaderObject[n]) {
                        index = parseInt(n) + 1;
                        return false;
                    }
                });
                return index;
            }

            function getUploaderIndexByDiv(obj) {

                var index = -1;
                $(Object.keys(owner.uploaderObject)).each(function (i, n) {
                    if ($(obj.element).children().children().children('input').attr('qq-button-id') == owner.uploaderObject[n]._defaultButtonId) {
                        index = parseInt(n) + 1;
                        return false;
                    }
                });
                return index;

            }

            var uploader = new qq.FineUploader({
                debug: false,
                element: document.getElementById(idName),
                request1Enable: true,
                request1: {
                    endpointnum :1,
                    endpoint: 'http://v.polyv.net/uc/services/rest?method=uploadfile',
                    params:{'writetoken':'9c538d85-340c-466c-9e35-bb301734eb0d','JSONRPC':'{"title": "这里是标题", "tag": "标签", "desc": "视频文档描述"}'},
                    fileFilter:Array("mov","mp4","mp3","flv","avi","mpg","wmv","mpeg"),
                    inputName:'Filedata',
                },
                multiple: mutiple,
                thumbNail:thumbNailFun,
                fileFilter: filterArray,
                imageRatio:imageRatio,
                request: {
                    endpointnum: 0,
                    endpoint: 'index.php?m=Home&c=Activity&a=workFileUpload',
                    params: {},
                    inputName: 'file',
                    paramsGetFromServer: 1,
                    chunking: {
                        enabled: false,
                        partSize: 2e6,
                        success: {
                            endpoint: 'e'
                        },
                        concurrent: {
                            enabled: false
                        }
                    },
                },

                deleteFile: {
                    enabled: true,
                    endpoint: '/uploads'
                },
                retry: {
                    enableAuto: true,
                    maxAutoAttempts: 0
                },
                resume: {
                    enabled: true
                },
                onCancel: function (id) {
                    try {
                        delete uploadInfo[getUploaderIndexByDiv(this)][id];
                        $(this.element).find('li[qq-file-id='+id+']').remove();
                    }catch(e){;}
                },
                responseCallBack: function (id, specNum, xhr, handle) {
                    var result;
                    try {
                        result = JSON.parse(xhr.responseText);
                    }

                    catch (e) {
                        if(typeof($.NotifyBox.NotifyPromptOne) == "function")
                         $.NotifyBox.NotifyPromptOne("提示", '服务器繁忙,请稍候再试', "确定");
                        else if(typeof($.NotifyBox.NotifyOne) == "function")
                         $.NotifyBox.NotifyOne("提示", '服务器繁忙,请稍候再试', "确定");
                        return false;
                    }
                    if (specNum == 0) {
                        if (result.code == 0) {
                            if (typeof(uploadInfo[getUploaderIndex(handle)]) == 'undefined') {
                                uploadInfo[getUploaderIndex(handle)] = {};
                            }
                            if(typeof(uploadInfo[getUploaderIndex(handle)][id]) == 'undefined')
                            {
                                uploadInfo[getUploaderIndex(handle)][id] = {};
                            }
                            try {
                                uploadInfo[getUploaderIndex(handle)][id]['file_category'] = getUploaderIndex(handle);
                                uploadInfo[getUploaderIndex(handle)][id]['file_name'] = result.name;
                                try {
                                    uploadInfo[getUploaderIndex(handle)][id]['file_path'] = $(result.res).children('key').text();
                                    if($(result.res).children('key').text() == '')
                                        uploadInfo[getUploaderIndex(handle)][id]['file_path'] = result.res;
                                }
                                catch (e) {
                                    uploadInfo[getUploaderIndex(handle)][id]['file_path'] = result.res;
                                }
                            }
                            catch (e) {
                                ;
                            }

                            return true;
                        } else {
                            $.post('/index.php/Home/Common/pushErrorLog',{'specNum':specNum,'data':xhr.responseText},function(res){;});
                            if(typeof($.NotifyBox.NotifyPromptOne) == "function")
                             $.NotifyBox.NotifyPromptOne("提示", result.msg, "确定");
                            else if(typeof($.NotifyBox.NotifyOne) == "function")
                             $.NotifyBox.NotifyOne("提示", result.msg, "确定");
                        }

                    }
                    else if(specNum == 1)
                    {
                        if(result.error == 0){
                            //alert("上传成功!VID:"+result.data[0].vid);
                            //TODO: add result.res to XXX
                            if (typeof(uploadInfo[getUploaderIndex(handle)]) == 'undefined') {
                                uploadInfo[getUploaderIndex(handle)] = {};
                            }
                            if(typeof(uploadInfo[getUploaderIndex(handle)][id])== 'undefined')
                            {
                                uploadInfo[getUploaderIndex(handle)][id] = {};
                            }
                            uploadInfo[getUploaderIndex(handle)][id]['vid'] = result.data[0].vid;
                            uploadInfo[getUploaderIndex(handle)][id]['vid_fullpath'] = result.data[0].mp4;
                            return true;
                        }else{
                            $.post('/index.php/Home/Common/pushErrorLog',{'specNum':specNum,'data':xhr.responseText},function(res){;});
                            if(typeof($.NotifyBox.NotifyPromptOne) == "function")
                             $.NotifyBox.NotifyPromptOne("提示",result.error, "确定");
                            else if(typeof($.NotifyBox.NotifyOne) == "function")
                             $.NotifyBox.NotifyOne("提示", result.msg, "确定");
                        }
                    }

                    return false;
                }
            });
            owner.uploaderObject[fileTypeIndex - 1] = uploader;

    };
    uploaderOss.uploaderObject = {};
    uploaderOss.callbacks = {};
    uploaderOss.getUploadInfo = function(key)
    {
        if($('.qq-upload-list li').hasClass('qq-upload-fail')){
            if(typeof($.NotifyBox.NotifyPromptOne) == "function")
             $.NotifyBox.NotifyPromptOne("提示","文件上传错误,请处理", "确定");
            else if(typeof($.NotifyBox.NotifyOne) == "function")
             $.NotifyBox.NotifyOne("提示","文件上传错误,请处理", "确定");
            return false;
        }
        if($('.qq-upload-list li').hasClass('qq-in-progress')){
            if(typeof($.NotifyBox.NotifyPromptOne) == "function")
             $.NotifyBox.NotifyPromptOne("提示","文件未上传完毕,无法提交", "确定");
            else if(typeof($.NotifyBox.NotifyOne) == "function")
             $.NotifyBox.NotifyOne("提示","文件未上传完毕,无法提交", "确定");
            return false;
        }
        var buttonId = uploaderOss.uploaderObject[key-1]._buttons[0].getButtonId();
        var lis = $('.qq-uploader-selector').find('input[qq-button-id='+buttonId+']').parent().parent().children('ul').children('li');
        if(uploadInfo[key] != undefined)
        $(Object.keys(uploadInfo[key])).each(function (i, n) {
            var id = n;
            if ($(lis).parent().find('li[qq-file-id=' + id + ']').length == 0 )
            {
              delete uploadInfo[key][n];
            }
        });

        return uploadInfo[key];
    }
    uploaderOss.clearUploadInfo = function(key)
    {
        delete uploadInfo[key];
    }
    uploaderOss.initUploader = function (parms) {
        "use strict";
        for(var key in parms){
            owner.setUploader(parms[key].id,key,parms[key].filterArray,parms[key].mutiple,parms[key].thumbNail,parms[key].imageRatio);
        }
        return owner;
    }
    uploaderOss.initUploadedObject = function (key,parms,htmls) {
        if (typeof(uploadInfo[key]) == 'undefined') {
            uploadInfo[key] = {};
        }
        $(parms).each(function(i,n){
                uploadInfo[key][-1-i] = n;
        })
        var buttonId = uploaderOss.uploaderObject[key-1]._buttons[0].getButtonId();
        var ul = $('.qq-uploader-selector').find('input[qq-button-id='+buttonId+']').parent().parent().children('ul');
        $(htmls).each(function(i,n){
            ul.append(n);
        })
        //init id of fineuploader

    }
    owner = uploaderOss;
    global.uploaderOss = uploaderOss;
}(window));