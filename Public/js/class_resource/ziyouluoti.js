
$('#gbcanvas').width(300);

var canvas = document.getElementById('gbcanvas');
var context = canvas.getContext('2d');
var posX = canvas.width / 2,//小球X轴起始位置
    posY = 30,//小球Y轴起始位置
    particleRadius = 30,//小球半徑
    speedX = 10, //定义一个X轴方向的速度
    speedY = 10, //定义一个Y轴方向的速度
    gravity = 1; //定义一个重力的参数，即重力加速度
//设置画布背景黑色
context.fillStyle = '#3ABAFF';
//设置画布大小
context.fillRect(0, 0, canvas.width, canvas.height);
//获取渐变色数組
var color = new gradientColor('#dd4814', '#FFFF66', '51');
//定义颜色变量
var i = 0;

function run() {

    posY = 100 - parseInt($('#height').val());
    gravity = parseInt($('#gravity').val());
    particleRadius = parseInt($('#weight').val());

    //定义setInterval来隔特定时间生成粒子
    setInterval(function () {
        //每次获取不同的颜色
        i++;
        //为了能够生成粒子移动效果，我们需要在每次绘制粒子之前清楚界面里的绘图
        context.fillStyle = '#3ABAFF';
        context.fillRect(0, 0, canvas.width, canvas.height); //使用背景色填充

        //设置运动速度
        posY += speedY;

        //添加地面反弹效果，只需要判断当粒子运动到近画布底端的时候，粒子Y轴坐标反向
        if (posY > canvas.height - particleRadius) {
            speedY *= -0.7; //设置粒子速度为负值，修改此数值可以修改粒子Y轴运动速度损耗量
            //设置粒子X轴速度的损耗量
            posY = canvas.height - particleRadius; //粒子低于画布最低端的时候，设置保证其不消失
        }

        //添加重力加速度效果
        speedY += gravity;
        //启动一个新的路径分支
        context.beginPath();
        //设置小球颜色
        if (i > 50) {
            context.fillStyle = color[50];
        } else {
            context.fillStyle = color[i];
        }
        //画小球
        context.arc(posX, posY, particleRadius, 0, Math.PI * 2, true);
        context.closePath();
        context.fill();
    }, 30);
}

//run();

// 将hex表示方式转换为rgb表示方式(这里返回rgb数组模式)
function colorRgb(sColor) {
    var reg = /^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/;
    var sColor = sColor.toLowerCase();
    if (sColor && reg.test(sColor)) {
        if (sColor.length === 4) {
            var sColorNew = "#";
            for (var i = 1; i < 4; i += 1) {
                sColorNew += sColor.slice(i, i + 1).concat(sColor.slice(i, i + 1));
            }
            sColor = sColorNew;
        }
        //处理六位的颜色值
        var sColorChange = [];
        for (var i = 1; i < 7; i += 2) {
            sColorChange.push(parseInt("0x" + sColor.slice(i, i + 2)));
        }
        return sColorChange;
    } else {
        return sColor;
    }
}
;

// 将rgb表示方式转换为hex表示方式
function colorHex(rgb) {
    var _this = rgb;
    var reg = /^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/;
    if (/^(rgb|RGB)/.test(_this)) {
        var aColor = _this.replace(/(?:(|)|rgb|RGB)*/g, "").split(",");
        var strHex = "#";
        for (var i = 0; i < aColor.length; i++) {
            var hex = Number(aColor[i]).toString(16);
            hex = hex < 10 ? 0 + '' + hex : hex;// 保证每个rgb的值为2位
            if (hex === "0") {
                hex += hex;
            }
            strHex += hex;
        }
        if (strHex.length !== 7) {
            strHex = _this;
        }
        return strHex;
    } else if (reg.test(_this)) {
        var aNum = _this.replace(/#/, "").split("");
        if (aNum.length === 6) {
            return _this;
        } else if (aNum.length === 3) {
            var numHex = "#";
            for (var i = 0; i < aNum.length; i += 1) {
                numHex += (aNum[i] + aNum[i]);
            }
            return numHex;
        }
    } else {
        return _this;
    }
}


//渐变色算法函数
//startColor  起始颜色
//endColor  结束颜色
//step 几个阶级（几步）
function gradientColor(startColor, endColor, step) {
    startRGB = colorRgb(startColor);//转换为rgb数组模式
    startR = startRGB[0];
    startG = startRGB[1];
    startB = startRGB[2];

    endRGB = colorRgb(endColor);
    endR = endRGB[0];
    endG = endRGB[1];
    endB = endRGB[2];

    sR = (endR - startR) / step;//总差值
    sG = (endG - startG) / step;
    sB = (endB - startB) / step;

    var colorArr = [];
    for (var i = 0; i < step; i++) {
        //计算每一步的hex值
        var hex = colorHex('rgb(' + parseInt((sR * i + startR)) + ',' + parseInt((sG * i + startG)) + ',' + parseInt((sB * i + startB)) + ')');
        colorArr.push(hex);
    }
    return colorArr;
}

