//右下角浮窗
$('li.bottomRightShare').hover(function(){
    $(this).addClass('active').children('.bottomRightShareOutter').show().stop().animate({
        'left' : '-80px',
        'opacity': '1'
    }).end().siblings().removeClass('active')
},function(){
    $(this).removeClass('active').children('.bottomRightShareOutter').hide().stop().animate({
        'left' : '-130px',
        'opacity': '0'
    })
})

$('li.bottomRightQRcode').hover(function(){
    $(this).addClass('active').children('.bottomRightQRcodeOutter').show().stop().animate({
        'left' : '-348px',
        'opacity': '1'
    }).end().siblings().removeClass('active')
},function(){
    $(this).removeClass('active').children('.bottomRightQRcodeOutter').hide().stop().animate({
        'left' : '-376px',
        'opacity': '0'
    })
})

var noScroll;
if(noScroll != 1 ) {
    $(window).scroll(function(){  
        if ($(window).scrollTop()>10){  
            $("li.bottomRightTop").css('visibility','visible');
        }else{  
            $("li.bottomRightTop").css('visibility','hidden');
        }  
    }); 
}

$("li.bottomRightTop").click(function(){  
    $('body,html').animate({scrollTop:0},100);  
    return false;  
});