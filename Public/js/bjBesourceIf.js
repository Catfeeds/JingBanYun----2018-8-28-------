$(document).ready(function(){
    var indexlength ;
  $('.searchLiClick').hover(function(event){
        var indexlength = $(this).index();
        if($(this).addClass('border3').children('p').hasClass('shang')){
          $(this).addClass('border3').children('p').removeClass('shang')
        }else{
              $(this).addClass('border3').children('p').addClass('shang').end().siblings('.searchLiClick').children('p').removeClass('shang')
        }
        // if($('.searchDiv').children().eq(indexlength).is(":hidden")){
              $(this).addClass('border3').siblings('.searchLiClick').removeClass('border3')
             $(this).children('.searchDiv').show().children('.searchBox').show()
        // }
    },function(event) {
            $that = $(this);
            $(this).children('.searchDiv').hide()
            $(this).siblings('.searchLiClick').removeClass('border3');
            $(this).removeClass('border3')
            $(this).children('.searchTop').removeClass('shang')
                //   if($('.searchDiv').children().eq(indexlength).is(':animated')){
                //     return false
                //   }else{
                //     $(this).siblings('.searchLiClick').removeClass('border3');
                //      $('.searchDiv').children().eq(indexlength).stop().slideToggle(300,function(){
                //           $that.removeClass('border3')
                //      }).siblings('.searchBox').hide()
                //   }
                //   $(this).siblings('.searchLiClick').removeClass('border3');
                //    $('.searchDiv').children().eq(indexlength).stop().slideToggle(300,function(){
                //         $that.removeClass('border3')
                //    }).siblings('.searchBox').hide()
                //   event.stopPropagation()
        })
  $('body').click(function(){

      var boxleng = $('.searchBox').length;
      for (var i = 0; i < boxleng; i++) {
        if($('.searchBox').eq(i).is(":visible")){
            if($('.searchBox').eq(i).is(':animated')){
              return false
            }else {
              $('.searchLiClick').eq(i).click()
            }

      }
    }
  })
  $('.searchBox').click(function(event){
    event.stopPropagation()
  })
  var imgArr = new Array();
  $('.checkedBoxImg').click(function(){
    imgArr = [];
    var imgSrc =$(this).attr('src');
    if (imgSrc == '/Public/img/resource/checked1.png') {
      $(this).attr('src','/Public/img/resource/checked2.png');
      var checkBoxSrc = $(this).parent().parent().siblings('div').find('img').length;
      for(var i=0;i<checkBoxSrc;i++){
        if($(this).parent().parent().siblings('div').find('img').eq(i).attr('src') =='/Public/img/resource/checked2.png'){
          imgArr.push($(this).parent().parent().siblings('div').find('img').eq(i).attr('src'))
        }
      }
      if(imgArr.length == checkBoxSrc-1 ){
        $(this).parent().parent().parent().children('div').eq(0).find('img').attr('src','/Public/img/resource/checked2.png')
      }
    }else {
      if($(this).parent().parent().parent().children('div').eq(0).find('img').attr('src') =="/Public/img/resource/checked2.png"){
        $(this).parent().parent().parent().children('div').eq(0).find('img').attr('src','/Public/img/resource/checked1.png')
      }
      $(this).attr('src','/Public/img/resource/checked1.png')
    }
  })
  $('.radioImgBox').click(function(){
          var imgSrc1 =$(this).children('.radioImg').attr('src');
          if (imgSrc1 == '__PUBLIC__/img/resource/radio1.png') {
              if ($(this).parent().siblings('li').find('.radioImg').attr('src') == '/Public/img/resource/radio2.png' ) {
                      return false
              }else {
                      $(this).children('.radioImg').attr('src','/Public/img/resource/radio2.png')
              }


          }else {
              $(this).children('.radioImg').attr('src','/Public/img/resource/radio1.png').parent().parent().siblings('li').find('.radioImg').attr('src','Public/img/resource/radio2.png')
          }
      })
    //   $(function(){
    //     for(var i=0;i<$('.resource_typeimg').length;i++) {
    //       var wid = $('.resource_typeimg')[i].width;
    //       var hei = $('.resource_typeimg')[i].height;
    //       if(wid/hei > 1) {
    //         $('.resource_typeimg').eq(i).css({
    //           'width' : '100%',
    //           'height' : 'auto',
    //           'max-width' : 'auto',
    //           'max-height' : '100%'
    //         })
    //       } else if(wid/hei == 1) {
    //             $('.resource_typeimg').eq(i).css({
    //               'width' : 'auto',
    //               'height' : 'auto',
    //               'max-width' : '100%',
    //               'max-height' : '100%'
    //             })
    //           }else {
    //         $('.resource_typeimg').eq(i).css({
    //           'width' : 'auto',
    //           'height' : '100%',
    //           'max-width' : '100%',
    //           'max-height' : 'auto'
    //         })
    //       }
    //     }
    // })
})
