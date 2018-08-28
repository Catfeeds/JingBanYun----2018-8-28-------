var pageIndex = 1;
var templateOuter = '<div class="messageWrapper">\
	<div class="messageTitle">\
	<div class="left">{0}&nbsp;&nbsp;(<span>{1}</span>)</div>\
	<div class="right">{2}</div>\
	</div>\
	<ul class="messageUl">\
	{3}\
	</ul>\
	</div>';

var queryEnabled = true;
function getMessageList()
{
    if(!queryEnabled)
        return;
    queryEnabled = false;
	if($('.messageContent').length !=0)
	{
    $.post('/index.php/Home/Message/PersonalMessageList',
        {
            'p':pageIndex,
            'sendTime':$('#datepicker').val(),
            'keyword':$('.search_text').val()
        },
        function(result)
        {
            $('.emptyDiv').hide();
            //enable scroll function
            //$('.messageContent').html('');
            queryEnabled = true;
            var result=result.result;
            var messageWrapper_length = $('.messageWrapper').length;

            if(result.length ==0){

                if (messageWrapper_length==0) {
                    $('.emptyDiv').show();
                }

                if($('#datepicker').val()!=''||$('.search_text').val()!='') {
                    if ($('.search_text').val()!='' || $('.search_text').val()!=undefined) {
                        var newEmpty = '<img src="http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Empty/sorry.png" class="sorry"><p>抱歉，没有搜索到相关的消息！</p>'
                        $('.emptyDiv').html(newEmpty);
                    }
                }
                return;
            }
            $('.messageContentCopy').show();
            pageIndex++;
            for(var i=0;i<result.length;i++)
            {
                var innerText = '';
                $.each(result[i].data,function(i,n){
                    innerText += templateInner.format(n.id,n.title,n.is_read == 2?'':'mesABold');
                });
                $('.messageContent').append(templateOuter.format(result[i].week,result[i].message_count,result[i].date,innerText));
            }

        }
    );
	}
}
function getUnReadCount()
{
    $.post('/index.php/Home/Message/getUnreadMessagesCount',
        function(result)
        {
            var result=result.result.num;
            $('.unReadMessageCount').text(result);
            $('.leftP .unReadMessageCount').text('('+result+')');
			//消息提示数目		
			if($.trim($('.messageNum').html()) <= 0) {

				$('.messageNum').hide().css('background','none')
			} else{

				$('.messageNum').show().css('background','#af1515')
			}
        }
    );
}
getUnReadCount();
getMessageList(pageIndex);
$(window).scroll(function () {
    if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
        //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
        getMessageList();
    }
});
$("#datepicker,.search_text").keydown(function (evt) {
    if (event.keyCode == "13") { //keyCode=13是回车键
        $('.messageContent').html('');
        pageIndex = 1;
        getMessageList();
    }
})

//点击搜索框
$('.search_btn').click(function () {
    $('.messageContent').html('');
    pageIndex = 1;
    getMessageList();
});

function markAllRead()
{
    $.post('/index.php/Home/Message/setAllMessageReadState',
        {state:2},
        function(result) {
            if (result.status == '200') {
                $('.unReadMessageCount').text('0');
                $('.messageUl li a').removeClass('mesABold');
            }
        });
}
function getSelectIdArray()
{
    var array =  new Array();
    $('.messageUl li input:checked').each(function(i,n){
        array.push($(n).attr('mid'));
    })
    return array;
}
$('#statusSelect').change(function(){
    var value = $('#statusSelect').val();
    if(value != '')
    {
        markReadStatus(getSelectIdArray(),value);
        $('#statusSelect').val('');
    }

})
function refreshLocalReadStatus(idArray,state)
{
    var unReadCountDelta = 0;
    $(idArray).each(function(i,n){
        if(state == 1) //未读
        {
            if(!$('#messagelink_'+n).hasClass('mesABold'))
                unReadCountDelta++;
            $('#messagelink_'+n).addClass('mesABold');
        }
        else
        {
            if($('#messagelink_'+n).hasClass('mesABold'))
                unReadCountDelta--;
            $('#messagelink_'+n).removeClass('mesABold');
        }
    })
    $('.unReadMessageCount').text(parseInt($('.unReadMessageCount')[0].innerText)+unReadCountDelta);

}
function markReadStatus(idArray,state)
{
    $.post('/index.php/Home/Message/setMessageReadState',
        {'state':state,'message_id':idArray},
        function(result) {
            if (result.status == '200') {
                refreshLocalReadStatus(idArray,state);
                $('.messageUl li input:checked').attr('checked',false);
            }
        });
}
function deleteSelectedMessage()
{
    var idArray = getSelectIdArray();
    $.post('/index.php/Home/Message/deletePersonalMessage',
        {message_id:idArray},
        function(result) {
            if (result.status == '200') {
                window.location.reload();
                //TODO:remove elements on html
                /*
                 $(idArray).each(function(i,n){
                 $('#messagechecked_'+n).parent().remove();
                 });
                 //delete div with 0 li
                 $('.messageWrapper').each(function(i,n){
                 if(n.find('li').length == 0)
                 n.remove();
                 });
                 */
                //TODO:query additional records
            }
        });
}
function showDeleteDialog() {
	if(0 == $('.messageUl li input:checked').length)
	{
		// $.notify({
  //                   title: '提示',
  //                   message: '没有选中任何消息,请重新选择'
  //               }, {
  //                   type: 'failed',
  //                   placement: {
  //                       from: "top",
  //                       align: "center"
  //                   }
  //               });
         $.NotifyBox.NotifyPromptOne("提示",'没有选中任何消息,请重新选择!','确定')
		return;
	}
    $('#deleteDialog').modal('show')
}
