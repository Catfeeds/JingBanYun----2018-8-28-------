function getBrowserInfo(){
    var Sys = {};
    var ua = navigator.userAgent.toLowerCase();
    var re =/(msie|firefox|chrome|opera|version).*?([\d.]+)/;
    var m = ua.match(re);
    Sys.browser = m[1].replace(/version/, "'safari");
    Sys.ver = m[2];
    return Sys;
}
var sys = getBrowserInfo();

/*
 将解析后的一行歌词添加到dom中
 */


var _etext ;
var wstoes = function (words) {
    var ps = "";
    for (var i = 0; i < words.length; i++) {
        ps += "<p>" + words[i]['w'] + "</p>";
    }
    _etext.innerHTML = ps;

    var _eps = _etext.getElementsByTagName("p");
    for (var i = 0; i < words.length; i++) {
        _eps[i].offset = words[i]['o'];
        _eps[i].duration = words[i]['d'];
    }
    return _eps;
}


var iStep = 50; // 默认刷新时长30ms，刷新时长配置对桌面歌词性能影响较大
var timer; // 启动单个字推进的定时器
/*
 处理单个显示元素
 _eps: dom节点列表，表示歌词中单个显示元素
 _index: 当前变化元素的索引
 _ps: process iStep，每次timeout推进的步长
 _process: 当前变化元素的进度（0-100）
 pos: 对该元素进行处理时间点，在哪个timeout点处理
 count: timeout的次数
 */
function refreshKalaOK()
{
    var currentZ = $('#kalaok').css('z-index');
    if(currentZ == 1005)
        $('#kalaok').css('z-index','1000');
    else
        $('#kalaok').css('z-index',parseInt(currentZ) +1);

}
var _processw = function (_eps, _index, _ps, _process, pos, count) {
    _ep = _eps[_index];

    refreshKalaOK();
    if (count >= pos) {
        _process += _ps;
        if (sys.browser!="firefox")
        _ep.style.backgroundImage = "-webkit-linear-gradient(top, rgba(255,255,255,0.5) 0%, rgba(255,255,255,0) 100%), -webkit-linear-gradient(left, #f00 " + _process + "%, #00f 0%)";
        else
        _ep.style.backgroundImage = "-moz-linear-gradient(top, rgba(255,255,255,0.5) 0%, rgba(255,255,255,0) 100%), -moz-linear-gradient(left, #f00 " + _process + "%, #00f 0%)";
        if (_process >= 99) {

            if ((_index + 1) >= _eps.length) { //该句结束退出
                refreshKalaOK();
				try{
                setTimeout(refreshKalaOK(),500);
                setTimeout(refreshKalaOK(),1000);
				}catch(e){;}
                return;
            }
            _index++;
            var ts = Math.round(_eps[_index].duration / iStep) == 0 ? 1 : Math.round(_eps[_index].duration / iStep);
            _ps = 100 / ts;
            _process = 0;
            pos = Math.round(_eps[_index].offset / iStep);
        }
    }
    count++;
    timer = window.setTimeout(_processw.bind(this, _eps, _index, _ps, _process, pos, count), iStep);
}

/*
 处理单行
 */
var _processL = function (words) {
    var _eps = wstoes(words);
    clearTimeout(timer); //清除上一行因为页面渲染延迟没有处理完的定时器
    _processw(_eps, 0, 100 / Math.round(_eps[0].duration / iStep), 0, Math.round(_eps[0].offset / iStep), 0);
}

/*
 处理全部krc歌词
 */
var timers = [];
var _processK = function (targetLines) {
	_etext = document.getElementById("text");
    timers = [];
    for (var i = 0; i < targetLines.length; i++) {
        var timer = window.setTimeout(_processL.bind(this, targetLines[i]['kala']), targetLines[i]['o']);
        timers.push(timer);
    }
}

//启动处理
