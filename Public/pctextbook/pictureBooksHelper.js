//## UI ##//
//固定page高度
function adjustPage() {
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var halfWindowWidth = parseInt(windowWidth / 2);
    // pageWidth = parseInt((windowHeight - 60) * (512 / 713));
    pageWidth = parseInt(windowWidth * 0.9 / 2);

    // $('#wrapper,.pageWrapper,.page').height(windowHeight - 60);
    $('#wrapper').height(windowHeight - 60);
    $('.pageWrapper,.page,#shadow>img').height(pageWidth * (1004 / 1239));
    var marTop1 = windowHeight - 60;
    var marTop2 = pageWidth * (1004 / 1239);
    var marTop = (marTop1 - marTop2) / 2;
    // $('.pageWrapper').css('margin-top',''+marTop+'px !important');
    $(".pageWrapper,#shadow").css("cssText", "margin-top:"+marTop+"px !important");

    $('#footer,#footerTableCell').height(50);
    //rectClickImgWrapper   actionWrapper
    var pageContainerWidth = $('#page1Wrapper').width() * 2;
    var pageContainerHeight = $('#page1Wrapper').height();
    $('#rectClickImgWrapper,#actionWrapper')
        .width(pageContainerWidth)
        .css('left', parseInt((windowWidth - $('#page1Wrapper').width() * 2) / 2) + 'px');
    $('#rectClickImgCell').height(pageContainerHeight);

    $('#kalaok').width(pageContainerWidth + 30)
        .css('left', parseInt((windowWidth - $('#page1Wrapper').width() * 2) / 2 - 15) + 'px');
}
//## 数据 ##//
//加载Book信息
function loadBookInfo() {
    $.support.cors = true;
    $.get('{0}/Pages.xml?t={1}'.format(serverPath, new Date().getTime()), function (res) {
        var content = $(res).find('content');
        bookInfo.width = parseInt($(content).attr('width'));
        bookInfo.height = parseInt($(content).attr('height'));
        $(content).find('page').each(function (i, n) {
            bookInfo.pages.push({pageId: i + 1, src: $(n).attr('src')});
        });
        turnPage(bookInfo.pages[0].pageId, bookInfo.pages[1].pageId);//从第一页封面开始，数组索引为1，即2.png
    });
}
//加载Page信息(书中的每一页)
function loadPageInfo(pageId, isLeftPage) {
    if (isLeftPage) {
        contentPage1Id = pageId - 3;
        if (contentPage1Id < 0)return false;
    } else {
        contentPage2Id = pageId - 3;
        if (contentPage2Id < 0)return false;
    }
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var url = '{0}/Pages/page{1}/index.xml?t={2}'.format(serverPath, loadPageId, new Date().getTime());
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onload = function() {
        var pageInfo = xhr.response;
        var pageInfo = pageInfo.split('&').join('& amp;');
        if (pageInfo) {
            if (isLeftPage) {
                currentPage1Info = pageInfo;
                currentPage1KalaOKInfo = {};
            } else {
                currentPage2Info = pageInfo;
                currentPage2KalaOKInfo = {};
            }
            renderClickRects(pageInfo, isLeftPage);
            renderActions('XueleListenMode', pageInfo, isLeftPage);
            renderActions('XueleActionClass', pageInfo, isLeftPage);
            renderActions('XueleShowRedWord', pageInfo, isLeftPage);
            renderActions('XueleRolePlay', pageInfo, isLeftPage);
            renderActions('XueleGame', pageInfo, isLeftPage);
            renderActions('XueleLianXi', pageInfo, isLeftPage);
            renderActions('XueleSongDaoru', pageInfo, isLeftPage);
            //render content
            renderActions('GoPageButton', pageInfo, isLeftPage);
            renderActions('AdditionalVideo', pageInfo, isLeftPage);
            renderActions('AdditionalFlash', pageInfo, isLeftPage);
            renderActions('AdditionalPPT', pageInfo, isLeftPage);
            renderActions('resourceImg', pageInfo, isLeftPage);
        } else {
            //console.log(err);
        }
    }
    xhr.send();


    function loadKalaokInfo() {
        var url = '{0}/Pages/page{1}/kalaok.txt?t={2}'.format(serverPath, loadPageId, new Date().getTime());
        if (isLeftPage) {
            currentPage1KalaOKInfo = [];
        } else {
            currentPage2KalaOKInfo = []
        }
        $.get(url, function (res) {
            var info = JSON.parse(res);
            if (isLeftPage) {
                currentPage1KalaOKInfo = info
            } else {
                currentPage2KalaOKInfo = info
            }
        });
    }

    loadKalaokInfo(isLeftPage);
    //加载两次防止kalaok在某些浏览器中加载失败
    window.setTimeout(function () {
        loadKalaokInfo(isLeftPage);
    }, 700);


}
//## 数据 ##//

//## 行为 ##//
//书本点击信息
function renderClickRects(pageInfo, isLeftPage) {
    var rectCategoryList = ['ClickRectImage','ClickRectVideo','ClickRectSound'];
    $.each(rectCategoryList,function(index,oper){
        var clickRectImages = $(pageInfo).find(oper);
        $.each(clickRectImages, function (i, n) {
            var item = $(n);
            var clickRect = {
                name: item.attr('xmlname'),
                positionX: parseInt(item.attr('positionx').trim()),
                positionY: parseInt(item.attr('positiony').trim()),
                width: parseInt(item.attr('width').trim()),
                height: parseInt(item.attr('height').trim()),
                fileName: item.attr('filename'),
                category:oper
            };
            renderClickRect(clickRect, isLeftPage);
        });
    });
}
//渲染点击区域
function renderClickRect(clickRect, isLeftPage) {
    var pageWrapperEleId = isLeftPage ? '#page1Wrapper' : '#page2Wrapper';
    var rate = pageWidth / bookInfo.width;

    var positionX = parseInt(clickRect.positionX * rate);
    var positionY = parseInt(clickRect.positionY * rate) + pagePos.paddingTop;
    var width = parseInt(clickRect.width * rate);
    var height = parseInt(clickRect.height * rate);
    if(clickRect.category == "ClickRectImage")
        var rectArea = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAuCAIAAADLDPyUAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAA1SURBVFhH7c0xAQAwEAOh+jedquCnwwBv5yqRSqQSqUQqkUqkEqlEKpFKpBKpRCqRSqSS2D72cWixqhNpvwAAAABJRU5ErkJggg==" class="rectClickArea" id="rc_{0}" style="opacity:0;left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRect(\'{5}\',{6})">'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    else if(clickRect.category == "ClickRectVideo")
        var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRectVideo(\'{5}\',{6})"></div>'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    else if(clickRect.category == "ClickRectSound")
        var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRectAudio(\'{5}\',{6})"></div>'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    $(pageWrapperEleId).append(rectArea);
}
//点击行为播放音频
function onClickRectVideo(fileName, isLeftPage)
{
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var videoSrc = '{0}/Pages/page{1}/{2}.mp4'.format(serverPath, loadPageId, fileName);
    //换一种播放方式
    var videoEle = mp4VideoPlay.format(videoSrc);
    $('#actionWrapper,#coverLayer,#actionCell').show();
    $('#actionWrapper,#coverLayer').click(function () {
        $('#actionWrapper,#coverLayer').hide();
        $('#actionCell').html('').hide();

    });
    $('#actionCell').html(videoEle);
}
function onClickRectAudio(fileName, isLeftPage)
{
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var audioSrc = '{0}/Pages/page{1}/{2}.mp3'.format(serverPath, loadPageId, fileName);
    //换一种播放方式
    var audioEle = mp3AudioPlay.format(audioSrc);
    $('#actionWrapper,#coverLayer,#actionCell').show();
    $('#actionWrapper,#coverLayer').click(function () {
        $('#actionWrapper,#coverLayer,#actionCell').hide();
        $('#actionCell').html('');
    });
    $('#actionCell').html(audioEle);

}
function onClickRect(fileName, isLeftPage) {

    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var imgSrc = '{0}/Pages/page{1}/{2}.png'.format(serverPath, loadPageId, fileName);
    var audioSrc = '{0}/Pages/page{1}/{2}{3}.mp3'.format(serverPath, loadPageId, fileName, speed == 'normal' ? '' : '_slow');


    $('#rectClickImg').attr('src', imgSrc);

    //换一种播放方式
    jPlayer.jPlayer("setMedia", {mp3: audioSrc});
    $('#rectClickImgWrapper,#coverLayer').show().click(function () {
        $('#rectClickImgWrapper,#coverLayer,#kalaok').hide();
        $('#rectClickImg').removeAttr('src');
        jPlayer.jPlayer("stop");
    });

    var loadPageKalaok = isLeftPage ? currentPage1KalaOKInfo : currentPage2KalaOKInfo;
    var sayid = fileName.replace('image/say', '');
    sayid = sayid.replace('image/duihuakuang', '');
    sayid = sayid.replace('image/duihk-', '');
    sayid = sayid.replace('image/', '');

    jPlayer.jPlayer("play");
    $("#jplayer").unbind($.jPlayer.event.play);
    $("#jplayer").bind($.jPlayer.event.play, function (event) {
        if(myBrowser().indexOf("IE") == -1 && speed == 'normal') {
            $(loadPageKalaok).each(function (i, n) {
                if ((i + 1) == sayid && ("object" == typeof n.kala)) {
                    $('#kalaok').show();
                    var c = [];
                    c.push(n)
                    _processK(c);
                    //eval('_processK(lines' + index + ')')
                }
            });
        }
    });

}
//切换语速
function switchSpeed() {
    switch (speed) {
        case 'normal':
            speed = 'slow';
            $('#speedCtrl').html('缓慢语速');
            break;
        case 'slow':
            speed = 'normal';
            $('#speedCtrl').html('正常语速');
            break;
    }
}

//渲染听力部分
function renderActions(nodeName, pageInfo, isLeftPage) {
    var listens = $(pageInfo).find(nodeName);
    $.each(listens, function (i, n) {
        var item = $(n);
        var info = {
            name: item.attr('xmlname'),
            positionX: item.attr('positionx').trim(),
            positionY: item.attr('positiony').trim(),
            width: typeof(item.attr('width')) == 'undefined' ? 0 : item.attr('width').trim(),
            height: typeof(item.attr('height')) == 'undefined' ? 0 : item.attr('height').trim(),
            flashFilename: item.attr('FlashFilename'),
            goPage: item.attr('GoPage'),
            path: item.attr('path'),
            resHtml: item.prop("outerHTML")
        };
        renderActionArea(nodeName, info, isLeftPage);
    });
}
function renderActionArea(nodeName, info, isLeftPage) {
    var pageWrapperEleId = isLeftPage ? '#page1Wrapper' : '#page2Wrapper';
    var rate = pageWidth / bookInfo.width;

    var positionX = parseInt((info.positionX) * rate);
    var positionY = parseInt(info.positionY * rate) + pagePos.paddingTop + 2;

    if (nodeName == 'GoPageButton') {
        var clickWidth = (info.width == null || info.width == 0) ? 280 : info.width;
        var clickHeight = (info.height == null || info.height == 0) ? 20 : info.height;
        var rectArea = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAuCAIAAADLDPyUAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAA1SURBVFhH7c0xAQAwEAOh+jedquCnwwBv5yqRSqQSqUQqkUqkEqlEKpFKpBKpRCqRSqSS2D72cWixqhNpvwAAAABJRU5ErkJggg==" class="contentClickArea" id="bc_{0}" style="opacity:0;text-align:left;left:{1}px;top:{2}px;width:{3}px;height:{4}px;color:transparent" onclick="onClickContent(\'{5}\')"></img>'
            .format(info.name, positionX, positionY, parseInt(clickWidth * rate), parseInt(clickHeight * rate), info.goPage);
        //$(pageWrapperEleId).append(rectArea);
        //$(rectArea).appendTo(pageWrapperEleId);
        document.getElementById(pageWrapperEleId.substr(1)).innerHTML = rectArea+document.getElementById(pageWrapperEleId.substr(1)).innerHTML ;
    } else if(nodeName == 'AdditionalVideo' || nodeName == 'AdditionalFlash' || nodeName == 'AdditionalPPT')
    {
        var src = PUBLIC_METRO + '/img/ebook/weike.gif'.format(nodeName);
        var areaEle = '<img class="actionArea" id="listen_{0}" style="left:{1}px;top:{2}px;width:70px;height:70px"onclick="onClickActionArea(\'{3}\',{5})" src="{4}">'.format(info.name, positionX, positionY, encodeURI(info.path).replace("'", "####"), src, isLeftPage ? 'true' : 'false');

        $(pageWrapperEleId).append(areaEle);
    }else if(nodeName == 'resourceImg'){
        var src = PUBLIC_METRO + '/img/ebook/resourceImg.gif'.format(nodeName);
        var areaEle = $('<div style="position:absolute;left:{1}px;top:{2}px;" class="resourceDiv"><img class="actionArea resIcon" id="listen_{0}"style="width:70px;height:70px;position:static;" src="{4}"></div>'.format(info.name, positionX, positionY, encodeURI(info.path).replace("'", "####"), src, isLeftPage ? 'true' : 'false'));
        if($(info.resHtml).eq(0).find('qjdh').length>0){
            if($(info.resHtml).eq(0).find('qjdh').length==1){
                var children1 = '<div class="childrenImg1"><p onclick="onClickActionArea(\'{0}\',{1})" src="{4}" >动画讲解</p>'.format($(info.resHtml).find('qjdh').attr('path'),isLeftPage)
            }else {
                var children1 = $('<div class="childrenImg1"><p class="children2">动画讲解</p></div>');
                var children2;
                var children3 = $('<div class="children3Div"></div>');
                children1.append(children3)
                for(var i=0;i<$(info.resHtml).find('qjdh').length;i++){
                    var resPath = $(info.resHtml).find('qjdh').eq(i).attr('path');
                    var resName = $(info.resHtml).find('qjdh').eq(i).attr('name');
                    var children2 = '<div onclick="onClickActionArea(\'{0}\')" title="{1}">'.format(resPath,resName,isLeftPage)+resName+'</div>'.format(resPath,resName,isLeftPage)
                    children3.append(children2)
                }
            }

            areaEle.append(children1)
        }
        if($(info.resHtml).eq(0).find('jhsy').length>0){
            if($(info.resHtml).eq(0).find('jhsy').length==1){
                var children1 = '<div class="childrenImg1"><p onclick="onClickActionArea(\'{0}\',{1})" >实验视频</p>'.format($(info.resHtml).find('jhsy').attr('path'),isLeftPage)
            }else {
                var children1 = $('<div class="childrenImg1"><p class="children2">实验视频</p></div>');
                var children2;
                var children3 = $('<div class="children3Div"></div>');
                children1.append(children3)
                for(var i=0;i<$(info.resHtml).find('jhsy').length;i++){
                    var resPath = $(info.resHtml).find('jhsy').eq(i).attr('path');
                    var resName = $(info.resHtml).find('jhsy').eq(i).attr('name');
                    var children2 = '<div onclick="onClickActionArea(\'{0}\')" title="{1}">'.format(resPath,resName,isLeftPage)+resName+'</div>'.format(resPath,resName,isLeftPage)
                    children3.append(children2)
                }
            }
            areaEle.append(children1)
        }
        if($(info.resHtml).eq(0).find('jhlx').length>0){
            if ($(info.resHtml).eq(0).find('jhlx').length==1) {
                var children1 = '<div class="childrenImg1"><p onclick="onClickActionArea(\'{0}\',{1})">互动练习</p>'.format($(info.resHtml).find('jhlx').attr('path'),isLeftPage)
            }else {
                var children1 =$('<div class="childrenImg1"><p class="children2">互动练习</p></div>');
                var children2;
                var children3 = $('<div class="children3Div"></div>');
                children1.append(children3)
                for(var i=0;i<$(info.resHtml).find('jhlx').length;i++){
                    var resPath = $(info.resHtml).find('jhlx').eq(i).attr('path');
                    var resName = $(info.resHtml).find('jhlx').eq(i).attr('name');
                    var children2 = '<div onclick="onClickActionArea(\'{0}\')" title="{1}">'.format(resPath,resName,isLeftPage)+resName+'</div>'.format(resPath,resName,isLeftPage)
                    children3.append(children2)
                }
            }
            areaEle.append(children1)
        }
        if($(info.resHtml).eq(0).find('wbht').length>0){
            if($(info.resHtml).eq(0).find('wbht').length==1){
                var children1 = '<div class="childrenImg1"><a href="{0}" target="_black" class="wb" >互动强化</a></div>'.format($(info.resHtml).find('wbht').attr('path'),isLeftPage)
            }else {
                var children1 = $('<div class="childrenImg1"><p class="children2">互动强化</p></div>');
                var children2;
                var children3 = $('<div class="children3Div"></div>');
                children1.append(children3)
                for(var i=0;i<$(info.resHtml).find('wbht').length;i++){
                    var resPath = $(info.resHtml).find('wbht').eq(i).attr('path');
                    var resName = $(info.resHtml).find('wbht').eq(i).attr('name');
                    var children2 = '<a href="'+resPath+'" target="_black" title="'+resName+'">'.format(resPath,resName,isLeftPage)+resName+'</a>'.format( resPath,resName,isLeftPage)
                    children3.append(children2)
                }
            }

            areaEle.append(children1)
        }
        $(pageWrapperEleId).append(areaEle);
    }
    else{
        var src = PUBLIC_METRO + '/img/ebook/icon_{0}.png'.format(nodeName);
        var areaEle = '<img class="actionArea" id="listen_{0}" style="left:{1}px;top:{2}px;"onclick="onClickActionArea(\'{3}\',{5})" src="{4}">'
            .format(info.name, positionX, positionY, encodeURI(info.flashFilename).replace("'", "####"), src, isLeftPage ? 'true' : 'false');

        $(pageWrapperEleId).append(areaEle);
    }
}
//播放
function onClickActionArea(flashFilename, isLeftPage) {
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    flashFilename = flashFilename.replace("####", "'")
    var videoSrc;
    if(flashFilename.indexOf('http://') == -1)
        videoSrc = '{0}/Pages/page{1}/{2}'.format(serverPath, loadPageId, flashFilename);
    else
        videoSrc = flashFilename;
    videoSrc = decodeURI(videoSrc);
    if(flashFilename.indexOf('.ppt') > 0)
    {
        window.open(videoSrc);
        return;
    }
    $('#actionWrapper,#coverLayer,#actionCell').show();
    $('#coverLayer,#actionWrapper').click(function (event) {
        var e = window.event || event.stopPropagation();
        $('#actionWrapper,#coverLayer').hide();
        $('#actionCell').html('').hide();
    });
    if (flashFilename.indexOf('.swf') > 0) {
        var videoEle = swfVideoPlay.format(videoSrc);
    } else if (flashFilename.indexOf('.flv') > 0) {
        var videoEle = flvVideoPlay.format(videoSrc.replace('.flv', '.flv'));
    }else if (flashFilename.indexOf('.wmv') > 0) {
        var videoEle = mp4VideoPlay.format(videoSrc.replace('.wmv', '.mp4'));
    }else if (flashFilename.indexOf('.avi') > 0) {
        var videoEle = mp4VideoPlay.format(videoSrc.replace('.avi', '.mp4'));
    } else if(flashFilename.indexOf('.mp4') > 0)
    {
        var videoEle = mp4VideoPlay.format(videoSrc);
    }
    else if(flashFilename.indexOf('.mp3') > 0)
    {
        var videoEle = mp3AudioPlay.format(videoSrc);
    }
    else
    {
        videoEle = '不支持播放该格式';
    }
    $('#actionCell').html(videoEle);
    if (flashFilename.indexOf('.flv') > 0){
        var s1 = new SWFObject ('/Public/flvPlayer/hdplayer.swf', 'player', '640', '360', '9');
        s1.addParam ('allowfullscreen', 'true');
        s1.addParam ('allowscriptaccess', 'always');
        s1.addParam ('wmode', 'transparent');
        s1.addVariable('file',videoSrc);
        s1.write ('actionCell');
    }
    $('#actionCell').height($('#actionCell').children().height());
    $('#actionCell').width($('#actionCell').children().width());
}
var i = 0;
function onClickContent(pageId) {

    pageId = parseInt(pageId);
    i = pageId-6;
    if(pageId & 1){
        turnPage(pageId - 2, pageId - 1);
    }else{
        turnPage(pageId - 3, pageId - 2);
    }

}

//## 行为 ##//

//## 翻书 ##//
function turnPage(page1Id, page2Id) {
    currentPageId[0] = page1Id;
    currentPageId[1] = page2Id;

    $('.actionArea,.rectClickArea,.contentClickArea,.resourceDiv').remove();
    loadPageInfo(page1Id, true);//两页page分时段加载
    window.setTimeout(function () {
        loadPageInfo(page2Id, false);
    }, 300);

    var page1Url = '{0}/content/{1}.png'.format(serverPath, page1Id)
    var page2Url;
    if (bookInfo.pages.length < page2Id) {
        var page2Url = '{0}/content/{1}.png'.format(serverPath, 1)
    } else {
        var page2Url = '{0}/content/{1}.png'.format(serverPath, page2Id)
    }
    if(myBrowser() == "IE9")
    {
        var tpl = '<img class="page" onerror="imageError(this)" id="page{0}" src="{1}">';
        var image1 = tpl.format(1,page1Url);
        $('#page1').remove();
        $('#page1Wrapper').append(image1);
        $('#page1Wrapper').append(image1);
        var image1 = tpl.format(2,page2Url);
        $('#page2').remove();
        $('#page2Wrapper').append(image1);
        adjustPage();
    }
    else
    {
        document.getElementById("page1").src=page1Url;
        document.getElementById("page2").src=page2Url;
    }
}

function turnToContentPage() {
    i = 2;
    $('.pageWrapper').removeClass('pageindex')
    turnPage(5, 6);
}

function turnToPreviousPage() {
    i--;
    if(i<0){
        i = 0 ;
    }else if(i == '0'){
        $('.pageWrapper').addClass('pageindex')
    }
    if (currentPageId[0] == 1) return;

    currentPageId[0] = currentPageId[0] - 2;
    currentPageId[1] = currentPageId[1] - 2;

    turnPage(currentPageId[0], currentPageId[1]);
}
function turnToNextPage() {
    if (currentPageId[0] == bookInfo.pages.length || currentPageId[1] == bookInfo.pages.length) {
        $.NotifyBox.NotifyPromptOne('提示','已经是最后一页了','确定')
        return;
    }

    i++;
    $('.pageWrapper').removeClass('pageindex');

    currentPageId[0] = currentPageId[0] + 2;
    currentPageId[1] = currentPageId[1] + 2;

    turnPage(currentPageId[0], currentPageId[1]);
}

//## 翻书 ##//

function getClientWidthHeight() {
    return {
        "height": $("#actionWrapper").height(),
        "width": $("#actionWrapper").width()
    };
}

function imageError(img)
{
    var src = img.src;
    if(src.substr(src.length-3,3)=='jpg')
        return;   //控制不要一直跳动
    img.src=src.substr(0,src.length-3)+'jpg';
}

function getResizeFlash() {
    var clientWidthHeight = getClientWidthHeight();
    var xResizeCoeff = clientWidthHeight.width / 800;
    var yResizeCoeff = clientWidthHeight.height / 600;
    var resizeCoeff = (xResizeCoeff > yResizeCoeff) ? yResizeCoeff : xResizeCoeff;
    var targetWidth = 800 * resizeCoeff;
    var targetHeight = 600 * resizeCoeff;
    return {
        "height": targetHeight,
        "width": targetWidth
    };
}
function resHover(parentDiv,childrenDiv){
    $('body').on("mouseover mouseout",parentDiv,function(event){
        if(event.type == "mouseover"){
            $(this).children(childrenDiv).show()
        }else if(event.type == "mouseout"){
            $(this).children(childrenDiv).hide()
        }
    })
}
resHover('.resourceDiv','.childrenImg1');
resHover('.childrenImg1','.children3Div');
