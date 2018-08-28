//固定page高度
function adjustPage() {
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();

    var pageWrapperHeight = parseInt(bookInfo.height * ( windowWidth / bookInfo.width));
    $('#pageWrapper').height(pageWrapperHeight);
    $('#footer,#footerTableCell').height(windowHeight - pageWrapperHeight);
    $('#rectClickImgCell,#actionCell').height(pageWrapperHeight);
    $('#loading').css({'height': windowHeight + 'px', 'line-height': windowHeight + 'px'});
}

//显示遮盖层
function showLoading() {
    $('#loading').show();
}
//隐藏遮盖层
function hideLoading() {
    $('#loading').hide();
}

//加载Book信息
function loadBookInfo() {
    $.get('{0}/Pages.xml'.format(serverPath), function (res) {
        var content = $(res).find('content');
        bookInfo.width = parseInt($(content).attr('width'));
        bookInfo.height = parseInt($(content).attr('height'));
        $(content).find('page').each(function (i, n) {
            bookInfo.pages.push({pageId: i + 1, src: $(n).attr('src')});
        });
        turnPage(bookInfo.pages[1].pageId);//从第一页封面开始，数组索引为1，即2.png
    });
}
//加载Page信息
function loadPageInfo(pageId) {
    currentPageId = pageId;
    contentPageId = currentPageId - 3;

    if (contentPageId < 0) {
        return false;
    }
    ////减3即除去开始3页的实际内容页在pages文件夹的编号
    $.get('{0}/Pages/page{1}/index.xml'.format(serverPath, contentPageId), function (pageInfo) {
        if (pageInfo) {
            currentPageInfo = pageInfo;
            renderClickRects(pageInfo);
            renderActions('XueleListenMode', pageInfo);
            renderActions('XueleActionClass', pageInfo);
            renderActions('XueleShowRedWord', pageInfo);
            renderActions('XueleRolePlay', pageInfo);
            renderActions('GoPageButton', pageInfo);
        } else {
            //alert(err);
        }
    });

    var url = '{0}/Pages/page{1}/kalaok.txt'.format(serverPath, contentPageId);
    currentPageKalaOKInfo = [];
    $.get(url, function (res) {
        var info = JSON.parse(res);
        currentPageKalaOKInfo = info
    });
}

//get back cover
function getBackCover() {
    return 'swf/it/content/1.png';
}

//书本点击信息
function renderClickRects(pageInfo) {
    var clickRectImages = $(pageInfo).find('ClickRectImage');
    $.each(clickRectImages, function (i, n) {
        var item = $(n);
        var clickRect = {
            name: item.attr('xmlname'),
            positionX: parseInt(item.attr('positionx')),
            positionY: parseInt(item.attr('positiony')),
            width: parseInt(item.attr('width')),
            height: parseInt(item.attr('height')),
            fileName: item.attr('filename')
        };
        renderClickRect(clickRect);
    });
}
//渲染点击区域
function renderClickRect(clickRect) {
    var rate = $('#page').width() / bookInfo.width;

    var positionX = parseInt(clickRect.positionX * rate);
    var positionY = parseInt(clickRect.positionY * rate) + pagePos.paddingTop;
    var width = parseInt(clickRect.width * rate);
    var height = parseInt(clickRect.height * rate);

    var rectArea = '<div class="rectClickArea" id="rc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickRect(\'{5}\')"></div>'
        .format(clickRect.name, positionX, positionY, width, height, clickRect.fileName);

    $('#pageWrapper').append(rectArea);
}
//点击行为播放音频
function onClickRect(fileName) {
    $('#coverLayer').show();

    var imgSrc = '{0}/Pages/page{1}/{2}.png'.format(serverPath, contentPageId, fileName);
    var audioSrc = '{0}/Pages/page{1}/{2}{3}.mp3'.format(serverPath, contentPageId, fileName, speed == 'normal' ? '' : '_slow');

    $('#rectClickImg').attr('src', imgSrc);
    //play
    showLoading();
    var rectAudio = document.getElementById('rectClickAudio');
    currentAudioName = fileName;
    $('#rectClickImgWrapper').show();
    if (api.systemType == 'android') {
        audio.play({
            path: audioSrc
        }, function (ret, err) {
            hideLoading();
            if (!isPlaying) {
                isPlaying = true;
                playKalaOk();
            }
            if (ret.complete) {
                isPlaying = false;
                window.setTimeout(function () {
                    $('#rectClickImgWrapper,#coverLayer,#kalaok').hide();
                }, 700);

            }
        });
    } else {
        rectAudio.src = audioSrc;
        rectAudio.play();
        $(rectAudio).on('playing', function () {
            hideLoading();
            if (!isPlaying) {
                isPlaying = true;
                playKalaOk(fileName);
            }
        });
        $(rectAudio).on('ended', function () {
            isPlaying = false;
            window.setTimeout(function () {
                $('#rectClickImgWrapper,#coverLayer,#kalaok').hide();
            }, 700);
        });
    }
}
//播放KALAOK
function playKalaOk() {
    $('#text').html('');
    timers = [];
    var sayid = currentAudioName.replace('image/say', '');
    sayid = sayid.replace('image/duihuakuang', '');
    sayid = sayid.replace('image/duihk-', '');
    sayid = sayid.replace('image/', '');
    $(currentPageKalaOKInfo).each(function (i, n) {
        if ((i + 1) == sayid) {
            $('#kalaok').show();
            var c = [];
            //c.push(n)
            var n1 = {
                d: 0,
                kala: [],
                o: 0
            };
            var n2 = {
                d: 0,
                kala: [],
                o: 0
            };
            var n3 = {
                d: 0,
                kala: [],
                o: 0
            };
            var kalaArr = n.kala;
            var offset = 0;
            var offset2 = 0;
            var totalOffset = 0;
            for (var j = 0; j < kalaArr.length; j++) {
                totalOffset = kalaArr[j].d + kalaArr[j].o;
                if (j < 8) {
                    n1.kala.push(kalaArr[j]);
                    offset = kalaArr[j].d + kalaArr[j].o;
                }
                else if (j < 16) {
                    offset2 = kalaArr[j].d + kalaArr[j].o;
                    kalaArr[j].o = kalaArr[j].o - offset;
                    n2.kala.push(kalaArr[j]);
                } else {
                    kalaArr[j].o = kalaArr[j].o - offset2;
                    n3.kala.push(kalaArr[j]);
                }
            }
            c.push(n1);
            if (n2.kala.length > 0) {
                n2.o = offset;
                c.push(n2)
            }
            if (n3.kala.length > 0) {
                n3.o = offset2;
                c.push(n3);
            }
            _processK(c);
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
function renderActions(nodeName, pageInfo) {
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
        renderActionArea(nodeName, info);
    });
}
function renderActionArea(nodeName, info) {
    var rate = $('#page').width() / bookInfo.width;

    var positionX = parseInt((info.positionX) * rate);
    var positionY = parseInt(info.positionY * rate) + pagePos.paddingTop + 2;

    if (nodeName == 'GoPageButton') {
        var rectArea = '<div class="contentClickArea" id="bc_{0}" style="left:{1}px;top:{2}px;width:{3}px;height:{4}px;" onclick="onClickContent(\'{5}\')"></div>'
            .format(info.name, positionX, positionY, parseInt(280 * rate), parseInt(20 * rate), info.goPage);
        $('#pageWrapper').append(rectArea);
    } else {
        var src = PUBLIC_METRO + '/img/ebook/icon_{0}.png'.format(nodeName);
        var areaEle = '<img class="actionArea" id="listen_{0}" style="left:{1}px;top:{2}px;"onclick="onClickActionArea(\'{3}\')" src="{4}">'
            .format(info.name, positionX, positionY, info.flashFilename, src);

        $('#pageWrapper').append(areaEle);
    }


}
//播放
function onClickActionArea(flashFilename) {
    if (typeof api !== 'undefined') {
        api.showProgress({
            title: '视频加载中...',
            text: '请稍等...',
            modal: false
        });

        window.setTimeout(function () {
            api.hideProgress();
        }, 2000);

        var videoSrc = '{0}/Pages/page{1}/{2}'.format(serverPath, contentPageId, flashFilename.replace('.swf', '.mp4'));

        if (api.systemType == 'android') {
            //video.play({path: videoSrc});
            api.openVideo({
                url: videoSrc
            });
        } else {
            var videoH = document.getElementById('actionVideo');
            videoH.src = videoSrc;
            videoH.play();
        }
    }
}
//点击回到目录
function onClickContent(pageId) {
    turnPage(parseInt(pageId));
}
//翻页
function turnPage(pageId) {
    $('.actionArea,.rectClickArea,.contentClickArea').remove();
    loadPageInfo(pageId);
    var pageUrl = '{0}/content/{1}.png'.format(serverPath, pageId)
    getEle('page').src = pageUrl;
    if (typeof api !== 'undefined') {
        api.imageCache(
            {
                url: pageUrl
            }, function (ret, err) {
                var url = ret.url;
                //alert('测试缓存' + url);
            }
        );
        audio.play({path: 'widget://image/turnpage.mp3'});
    }
}
function turnToContentPage() {
    turnPage(5);
}
function turnToPreviousPage() {
    if (currentPageId == 2) return;
    turnPage(currentPageId - 1);
}
function turnToNextPage() {
    if (currentPageId == bookInfo.pages.length) return;
    turnPage(currentPageId + 1);
}