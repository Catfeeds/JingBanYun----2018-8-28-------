//## UI ##//
//固定page高度
function adjustPage() {
    var windowHeight = $(window).height() - 55;
    var windowWidth = $(window).width();
    var halfWindowWidth = parseInt(windowWidth / 2);
    pageWidth = parseInt((windowHeight - 60) * (512 / 713));


    $('#etextbook_wrapper,.etextbook_pageWrapper,.etextbook_page').height(windowHeight - 60);
    $('.etextbook_page,.etextbook_pageWrapper').width(pageWidth);
    $('#etextbook_footer,#etextbook_footerTableCell').height(50);
    //rectClickImgWrapper   actionWrapper
    var pageContainerWidth = $('#etextbook_page1Wrapper').width() * 2;
    var pageContainerHeight = $('#etextbook_page1Wrapper').height();
    $('#etextbook_rectClickImgWrapper,#etextbook_actionWrapper')
        .width(pageContainerWidth)
        .css('left', parseInt((windowWidth - $('#etextbook_page1Wrapper').width() * 2) / 2) + 'px');
    $('#etextbook_rectClickImgCell,#etextbook_actionCell').height(pageContainerHeight);

    $('#etextbook_kalaok').width(pageContainerWidth + 30)
        .css('left', parseInt((windowWidth - $('#etextbook_page1Wrapper').width() * 2) / 2 - 15) + 'px');
    $('#etextbook_footer').width(pageContainerWidth).show();
}

//## 数据 ##//
//加载Book信息
function loadBookInfo() {
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

    $.ajax({
        type: "GET",
        data:"{}",
        cache: false,
        crossDomain: true == !(document.all),
        url: '{0}/Pages/page{1}/index.xml?t={2}'.format(serverPath, loadPageId, new Date().getTime()),
        success: function(pageInfo){
            var loadPage = /Pages\/page(\d+)/.exec(xhr.responseURL)[1];
            if(loadPage != contentPage1Id && loadPage != contentPage2Id)
                return;
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
                if (platform != 'pad') {
                    renderActions('XueleRolePlay', pageInfo, isLeftPage);
                    renderActions('XueleGame', pageInfo, isLeftPage);
                    renderActions('XueleLianXi', pageInfo, isLeftPage);
                }
                renderActions('XueleSongDaoru', pageInfo, isLeftPage);
                //render content
                renderActions('GoPageButton', pageInfo, isLeftPage);
            } else {
                //console.log(err);
            }
            console.log('ajax执行完毕');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.status);
            console.log(XMLHttpRequest.readyState);
            console.log(textStatus);
            console.log('ajax IE9报错');
        },
    });


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

    //loadKalaokInfo(isLeftPage);
    //加载两次防止kalaok在某些浏览器中加载失败
    //window.setTimeout(function () {
    //loadKalaokInfo(isLeftPage);
    //}, 700);
    console.log('函数执行完毕');

}
//## 数据 ##//

//## 行为 ##//
//书本点击信息
function renderClickRects(pageInfo, isLeftPage) {
    var rectCategoryList = ['ClickRectImage', 'ClickRectVideo', 'ClickRectSound'];
    $.each(rectCategoryList, function (index, oper) {
        var clickRectImages = $(pageInfo).find(oper);
        $.each(clickRectImages, function (i, n) {
            var item = $(n);
            var clickRect = {
                name: item.attr('xmlname'),
                positionX: parseInt(item.attr('positionx')),
                positionY: parseInt(item.attr('positiony')),
                width: parseInt(item.attr('width')),
                height: parseInt(item.attr('height')),
                fileName: item.attr('filename'),
                category: oper
            };
            renderClickRect(clickRect, isLeftPage);
        });
    });
}
//渲染点击区域
function renderClickRect(clickRect, isLeftPage) {
    var pageWrapperEleId = isLeftPage ? '#etextbook_page1Wrapper' : '#etextbook_page2Wrapper';
    var rate = pageWidth / bookInfo.width;

    var positionX = parseInt(clickRect.positionX * rate);
    var positionY = parseInt(clickRect.positionY * rate) + pagePos.paddingTop;
    var width = parseInt(clickRect.width * rate);
    var height = parseInt(clickRect.height * rate);
    if (clickRect.category == "ClickRectImage")
        var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRect(\'{5}\',{6})"></div>'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    else if (clickRect.category == "ClickRectVideo")
        var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRectVideo(\'{5}\',{6})"></div>'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    else if (clickRect.category == "ClickRectSound")
        var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRectAudio(\'{5}\',{6})"></div>'
            .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName, isLeftPage ? 'true' : 'false');
    $(pageWrapperEleId).append(rectArea);
}

//播放视频事件
function onClickRectVideo(fileName, isLeftPage) {
    if (isTeacher == 'false') {
        return false;
    }
    sendSocket("etextbook|onClickRectVideo|" + fileName + ',' + (isLeftPage ? 1 : 0));

    playTextbookVideo(fileName, isLeftPage);
}

//播放视频
function playTextbookVideo(fileName, isLeftPage) {
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var videoSrc = '{0}/Pages/page{1}/{2}.mp4'.format(serverPath, loadPageId, fileName);
    //换一种播放方式
    var videoEle = mp4VideoPlay.format(videoSrc);
    $('#actionWrapper,#coverLayer').show();
    $('#coverLayer,#actionWrapper').click(function () {
        $('#actionWrapper,#coverLayer').hide();
        $('#etextbook_actionCell').html('');
    });
    $('#etextbook_actionCell').html(videoEle);
}
//播放音频事件
function onClickRectAudio(fileName, isLeftPage) {
    if (isTeacher == 'false') {
        return false;
    }
    sendSocket("etextbook|onClickRectAudio|" + fileName + ',' + (isLeftPage ? 1 : 0));
    playTextbookAudio(fileName, isLeftPage);
}
//播放音频
function playTextbookAudio(fileName, isLeftPage) {
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var audioSrc = '{0}/Pages/page{1}/{2}.mp3'.format(serverPath, loadPageId, fileName);
    //换一种播放方式
    var audioEle = mp3AudioPlay.format(audioSrc);
    $('#actionWrapper,#coverLayer').show();
    $('#actionWrapper,#coverLayer').click(function () {
        $('#actionWrapper,#coverLayer').hide();
        $('#etextbook_actionCell').html('');
    });
    $('#etextbook_actionCell').html(audioEle);
}

//播放句子事件
function onClickRect(fileName, isLeftPage) {
    if (isTeacher == 'false') {
        return false;
    }
    sendSocket("etextbook|onClickRect|" + fileName + ',' + (isLeftPage ? 1 : 0));
    playTextbookSentence(fileName, isLeftPage);
}
//播放句子
function playTextbookSentence(fileName, isLeftPage) {
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    var imgSrc = '{0}/Pages/page{1}/{2}.png'.format(serverPath, loadPageId, fileName);
    var audioSrc = '{0}/Pages/page{1}/{2}{3}.mp3'.format(serverPath, loadPageId, fileName, speed == 'normal' ? '' : '_slow');

    $('#etextbook_rectClickImg').attr('src', imgSrc);

    //换一种播放方式
    if (platform != 'pad') {
        jPlayer.jPlayer("setMedia", {mp3: audioSrc});
    }

    $('#etextbook_rectClickImgWrapper,#etextbook_coverLayer').show().click(function () {
        //todo 取消播放的功能 屏蔽，防止停止声音不同步的bug，网络下播放，各设备难以做到统一
        if (isTeacher == 'true') {
            sendSocket("etextbook|cancelPlaySentence|");
            cancelTextbookPlaySentence();
        }
    });

    var loadPageKalaok = isLeftPage ? currentPage1KalaOKInfo : currentPage2KalaOKInfo;
    var sayid = fileName.replace('image/say', '');
    sayid = sayid.replace('image/duihuakuang', '');
    sayid = sayid.replace('image/duihk-', '');
    sayid = sayid.replace('image/', '');

    if (platform == 'pad') {
        if (netAudio != '') {
            netAudio.play({
                path: audioSrc
            }, function (ret, err) {
                if (typeof _processK === 'function') {
                    $(loadPageKalaok).each(function (i, n) {
                        if ((i + 1) == sayid && ("object" == typeof n.kala)) {
                            $('#etextbook_kalaok').show();
                            var c = [];
                            c.push(n)
                            _processK(c);
                        }
                    });
                }
            });
        }
    } else {
        jPlayer.jPlayer("play");
        $("#etextbook_jplayer").unbind($.jPlayer.event.play);
        $("#etextbook_jplayer").bind($.jPlayer.event.play, function (event) {
            if (typeof _processK === 'function') {
                $(loadPageKalaok).each(function (i, n) {
                    if ((i + 1) == sayid && ("object" == typeof n.kala)) {
                        $('#etextbook_kalaok').show();
                        var c = [];
                        c.push(n)
                        _processK(c);
                    }
                });
            }
        });
    }

}
//取消播放句子
function cancelTextbookPlaySentence() {
    $('#etextbook_rectClickImgWrapper,#etextbook_coverLayer,#etextbook_kalaok').hide();
    if (platform == 'pad') {
        netAudio.stop();
    } else {
        jPlayer.jPlayer("stop");
    }
}

//切换语速事件
function onSwitchSpeed() {
    if (isTeacher == 'false') {
        return false;
    }
    sendSocket("etextbook|switchSpeed|");
    switchSpeed();
}
//切换语速
function switchSpeed() {
    switch (speed) {
        case 'normal':
            speed = 'slow';
            $('#etextbook_speedCtrl').html('缓慢语速');
            break;
        case 'slow':
            speed = 'normal';
            $('#etextbook_speedCtrl').html('正常语速');
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
            positionX: item.attr('positionx'),
            positionY: item.attr('positiony'),
            flashFilename: item.attr('FlashFilename'),
            goPage: item.attr('GoPage')
        };
        renderActionArea(nodeName, info, isLeftPage);
    });
}
function renderActionArea(nodeName, info, isLeftPage) {
    var pageWrapperEleId = isLeftPage ? '#etextbook_page1Wrapper' : '#etextbook_page2Wrapper';
    var rate = pageWidth / bookInfo.width;

    var positionX = parseInt((info.positionX) * rate);
    var positionY = parseInt(info.positionY * rate) + pagePos.paddingTop + 2;

    if (nodeName == 'GoPageButton') {
        var rectArea = '<div class="contentClickArea" id="bc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickContent(\'{5}\')"></div>'
            .format(info.name, positionX, positionY, parseInt(280 * rate), parseInt(20 * rate), info.goPage);
        $(pageWrapperEleId).append(rectArea);
    } else {
        var src = PUBLIC_METRO + '/img/ebook/icon_{0}.png'.format(nodeName);
        var areaEle = '<img class="actionArea" id="listen_{0}" style="left:{1}px;top:{2}px;"onclick="onClickActionArea(\'{3}\',{5})" src="{4}">'
            .format(info.name, positionX, positionY, encodeURI(info.flashFilename).replace("'", "####"), src, isLeftPage ? 'true' : 'false');

        $(pageWrapperEleId).append(areaEle);
    }
}
//播放
function onClickActionArea(flashFilename, isLeftPage) {
    if (isTeacher == 'false') {
        return false;
    }
    if (flashFilename.indexOf('jsby') < 0) {
        sendSocket("etextbook|playMedia|" + flashFilename + ',' + (isLeftPage ? 1 : 0));
    }
    playTextbookMedia(flashFilename, isLeftPage);
}

function playTextbookMedia(flashFilename, isLeftPage) {
    var loadPageId = isLeftPage ? contentPage1Id : contentPage2Id;
    flashFilename = flashFilename.replace("####", "'")
    var videoSrc = '{0}/Pages/page{1}/{2}'.format(serverPath, loadPageId, flashFilename);
    if (platform != 'pad') {
        $('#etextbook_actionWrapper,#etextbook_coverLayer').show();

        $('#etextbook_coverLayer').click(function () {
            $('#etextbook_actionWrapper').hide();
            $('#etextbook_coverLayer').css('display','none');
        });
    }
    var videoEle = '';
    console.log("进来播放");
    if (platform == 'pad') {
        if (flashFilename.indexOf('.mp3') < 0) {
            videoSrc = videoSrc.replace('.flv', '.mp4');
            videoSrc = videoSrc.replace('.swf', '.mp4');
            api.openVideo({
                url: videoSrc
            });
            //videoEle = mp4VideoPlay.format(videoSrc);
        } else {
            videoEle = mp3AudioPlay.format(videoSrc);
        }
    } else {
        if (flashFilename.indexOf('.swf') > 0) {
            var videoEle = swfVideoPlay.format(videoSrc);
        } else if (flashFilename.indexOf('.flv') > 0) {
            var videoEle = flvVideoPlay.format(videoSrc.replace('.flv', '.flv'));
        }else if (flashFilename.indexOf('.wmv') > 0) {
            var videoEle = mp4VideoPlay.format(videoSrc.replace('.wmv', '.mp4'));
        }else if (flashFilename.indexOf('.avi') > 0) {
            var videoEle = mp4VideoPlay.format(videoSrc.replace('.avi', '.mp4'));
        } else if (flashFilename.indexOf('.mp4') > 0) {
            videoEle = mp4VideoPlay.format(videoSrc);
        }
        else if (flashFilename.indexOf('.mp3') > 0) {
            videoEle = mp3AudioPlay.format(videoSrc);
        }
        else {
            videoEle = '不支持播放该格式';
        }
    }

    $('#etextbook_actionCell').html(videoEle);
    if (flashFilename.indexOf('.flv') > 0){
        var s1 = new SWFObject ('/Public/flvPlayer/hdplayer.swf', 'player', '640', '360', '9');
        s1.addParam ('allowfullscreen', 'true');
        s1.addParam ('allowscriptaccess', 'always');
        s1.addParam ('wmode', 'transparent');
        s1.addVariable('file',videoSrc);
        s1.write ('flvPlayer');
    }
}

function onClickContent(pageId) {
    if (isTeacher == 'false') {
        return false;
    }
    pageId = parseInt(pageId);
    currentClassroomState.textbookPage = [pageId - 2, pageId - 1];
    turnPage(pageId - 2, pageId - 1);

    sendSocket("etextbook|turnPage|" + (pageId - 2) + ',' + (pageId - 1));
}

//## 行为 ##//

//## 翻书 ##//
function turnPage(page1Id, page2Id) {
    currentPageId[0] = page1Id;
    currentPageId[1] = page2Id;

    $('.actionArea,.rectClickArea,.contentClickArea').remove();
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
        var image1 = tpl.format(2,page2Url);
        $('#page2').remove();
        $('#page2Wrapper').append(image1);
		adjustPage();
	}
	else
	{
	document.getElementById("etextbook_page1").src=page1Url;	
    document.getElementById("etextbook_page2").src=page2Url;    
	}
}
function turnToContentPage() {
    if (isTeacher == 'false') {
        return false;
    }
    currentClassroomState.textbookPage = [5, 6];
    turnPage(5, 6);
    sendSocket("etextbook|turnPage|5,6");
}
function turnToPreviousPage() {
    if (isTeacher == 'false') {
        return false;
    }
    if (currentPageId[0] == 1) return;

    currentPageId[0] = currentPageId[0] - 2;
    currentPageId[1] = currentPageId[1] - 2;
    currentClassroomState.textbookPage = currentPageId;
    turnPage(currentPageId[0], currentPageId[1]);

    sendSocket("etextbook|turnPage|" + currentPageId[0] + ',' + currentPageId[1]);
}
function turnToNextPage() {
    if (isTeacher == 'false') {
        return false;
    }
    if (currentPageId[0] == bookInfo.pages.length || currentPageId[1] == bookInfo.pages.length) return;

    currentPageId[0] = currentPageId[0] + 2;
    currentPageId[1] = currentPageId[1] + 2;
    currentClassroomState.textbookPage = currentPageId;
    turnPage(currentPageId[0], currentPageId[1]);

    sendSocket("etextbook|turnPage|" + currentPageId[0] + ',' + currentPageId[1]);
}

//## 翻书 ##//

function getClientWidthHeight() {
    return {
        "height": $("#etextbook_actionWrapper").height(),
        "width": $("#etextbook_actionWrapper").width()
    };
}
function imageError(img) {
    var src = img.src;
    img.src = src.substr(0, src.length - 3) + 'jpg';
    if (src.substr(src.length - 3, 3) == 'jpg')
        return;   //控制不要一直跳动
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