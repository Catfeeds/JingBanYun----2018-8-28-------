function hidden_error()
{
	//return true
}
window.onerror=hidden_error;
var $_GET = (function(){
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if(typeof(u[1]) == "string"){
        u = u[1].split("&");
        var get = {};
        for(var i in u){
			if(typeof(i) == 'int')
			{
            var j = u[i].split("=");
            get[j[0]] = j[1];
			}
        }
        return get;
    } else {
        return {};
    }
})();
String.prototype.cnsub = function(n)
{    
	var r = /[^\x00-\xff]/g;    
	if(this.replace(r, "mm").length <= n) return this;   
	
	var m = Math.floor(n/2);    
	for(var i=m; i<this.length; i++) {    
	if(this.substr(0, i).replace(r, "mm").length>=n) {    
		return this.substr(0, i)+'...' ;
		}    
	}
	return this;   
};
function $id(id)
{
	return document.getElementById(id);
}
function $encode(s)
{
	return (typeof(encodeURIComponent)=="function")?encodeURIComponent(s):escape(s);
}
function check_browser()
{
	var system = {};
	var ua = navigator.userAgent.toLowerCase();
	var str;
	(str = ua.match(/msie ([\d.]+)/)) ? system.ie = str[1] :
	(str = ua.match(/firefox\/([\d.]+)/)) ? system.firefox = str[1] :
	(str = ua.match(/chrome\/([\d.]+)/)) ? system.chrome = str[1] :
	(str = ua.match(/opera.([\d.]+)/)) ? system.opera = str[1] :
	(str = ua.match(/version\/([\d.]+).*safari/)) ? system.safari = str[1] : 0;

	if (system.ie) 
	{
		return 'ie';
	}
	else if (system.firefox) 
	{
		return 'firefox';
	}
	else if (system.chrome) 
	{
		return 'chrome';
	}
	else if (system.opera) 
	{
		return 'opera';
	}
	else if (system.safari) 
	{
		return 'safari';
	}
	else
	{
		return '0';
	}
}
function showsubmenu(id,open,close)
{
	open  = (open   == 'undefined' || open   == null) ? 'cate_open' : open;
	close = (close  == 'undefined' || close  == null) ? 'cate_fold' : close;
	var menuobjedt=document.getElementById(id);
	var imgsrc = document.getElementById("img_"+id);
	var result = false;
	if(menuobjedt)
	{
		if(menuobjedt.style.display=="none") 
		{	
			result = true;
			menuobjedt.style.display="";
			if( imgsrc )
			imgsrc.src = imgsrc.src.replace(open, close);
		}
		else
		{
			result = false;
			menuobjedt.style.display="none"; 
			if( imgsrc )
			imgsrc.src = imgsrc.src.replace(close, open);
		}
	}
	return result;
}
function showdisplay(id,display)
{
	var menuobjedt=document.getElementById(id);
	if(menuobjedt)
	{
		menuobjedt.style.display = display;
	}
}
function keyword_help(keywordcode)
{
	window.opener = null;
	window.open('http://www.phpstat.net/keywords.php#'+keywordcode,'','');
}
function get_select_option_by_id(elementid)
{
	var selectelement = document.getElementById(elementid);
	return selectelement.options[selectelement.options.selectedIndex].value;
}
function get_select_option_by_form(formid)
{
	return formid.options[formid.options.selectedIndex].value;
}
function fixedgetelementsbyname(tag, name) 
{
	var tagArr = document.getElementsByTagName(tag);
	var elements = [];     
	for (var i = 0; i < tagArr.length; i++)
	{
		var att = tagArr[i].getAttribute("name");
		if (att == name) 
		{
			elements[i] = tagArr[i];
		}
	}
	return elements;
}
function getid(id)
{
	return document.getElementById(id);
}
function show_select_report(key,length,reportid,reporttype)
{	
	var re = false;
	if(reportid != '' ){
		for(var i=1;i<=length;i++){
		//alert(reportid+'-'+i);
		if(i==key){
			if(getid('show_click_'+reportid+i).className == 'selected' && (i!=0)) 
			{
				//re = true;
			}
			else 
			{
				getid('show_click_'+reportid+i).className='selected';
				//getid('show_report_'+reportid+i).innerHTML='wdywdywdy';
				
				
				var arr = {};
				//arr['starttime'] = $_GET['starttime'];
				arr.starttime = $_GET['starttime'];
				arr.endtime = $_GET['endtime'];
				arr.pageurl = $_GET['pageurl'];
				//alert(arr.starttime);
				//ajaxPost(arr,i);
				getid('show_click_'+reportid+i).className='selected';
				getid('show_report_'+reportid+i).style.display='block';
				//getid('show_report_'+reportid+i).style.visibility='visible';
				getid('show_report_'+reportid+i).style.height='auto';
			}
		}
		else{
			getid('show_click_'+reportid+i).className='';
			getid('show_report_'+reportid+i).style.display='none';
			//getid('show_report_'+reportid+i).style.visibility='hidden';
			//getid('show_report_'+reportid+i).style.height='0px';
			getid('show_report_'+reportid+i).style.height='auto';
		}
		}
	}
	else{
	for(var i=1;i<=length;i++)
	{
		//alert(reportid+'-'+i);
		if(i==key)
		{
			if(getid('show_click_'+reportid+i).className == 'selected' && (i!=0)) 
			{
				re = true;
			}
			else 
			{
				getid('show_click_'+reportid+i).className='selected';
				getid('show_report_'+reportid+i).style.display='block';
				//getid('show_report_'+reportid+i).style.visibility='visible';
				getid('show_report_'+reportid+i).style.height='auto';
			}
		}
		else
		{
			getid('show_click_'+reportid+i).className='';
			//if( reporttype == 'table' )
			getid('show_report_'+reportid+i).style.display='none';
			//getid('show_report_'+reportid+i).style.visibility='hidden';
			//getid('show_report_'+reportid+i).style.height='0px';
			//getid('show_report_'+reportid+i).style.height='auto';
		}
	}
	}
	return re;
}
//ajax请求
    function ajaxPost(arr,url){
    	var url = 'getajaxpostdata.php';
        $.ajax({
        url:url,
        type:'POST',
        data:arr,
        dataType:"json",//返回json格式数组
        timeout:1000000, //超时时间设置，单位毫秒
        beforeSend:function(){
        	alert("需要弹出一个层“加载中.....”");
        },
        success:function(data){

                alert(data.starttime);
                //alert("清空div中的数据");
                //var deletediv=document.getElementById("search_mod");
                //deletediv.innerHTML='';
               if(data.status==0){
                    alert(data.err_msg);
               }
               else if(data.status==1){
                    sd=data.data;
                    changehtml();
               }
          },
          //请求出错
          error:function(){
          },
          //请求完成
        complete:function(){
        }
      });
    }


function create_ul_li_js_key(menuarr,selectid,reportid,reporttype)
{
	if( typeof( reportid ) == 'undefined' || reportid == '' )
	{
		reportid = '';
	}
	else
	{
		reportid = reportid + '_';
	}
	if( typeof( reporttype ) == 'undefined' || reporttype == '' )
	{
		reporttype = '';
	}
	var menulength = menuarr.length;
	if(menulength>5){
		var width = "width:"+100/menulength+"%";
	}
	else{
		var width = "width:20%";
	}
	var menucodelist = menuselectid = '';
	var allmi = 0;
	for(var mi in menuarr)
	{	
        
		if(isNaN(parseInt(mi)))
		    continue;
		
		allmi = parseInt(mi) + 1;
		menuselectid = '';
		if(selectid == mi)
		{
			menuselectid = "class=\"selected\"";
		}
		menucodelist += "<li "+menuselectid+" style="+width+" onclick=\"show_select_report("+allmi+","+menulength+",'"+reportid+"','"+reporttype+"');\" id=\"show_click_"+reportid+(allmi)+"\"><span class='word'>"+menuarr[mi]+"</span><span class='bg'></span></li>";
		
		if( allmi > 7 )
		{
			break;
		}
	}
	document.write(menucodelist);
}

//reporttype是图或者是表格
function create_ul_li_js(menuarr,selectid,reportid,reporttype)
{
	if( typeof( reportid ) == 'undefined' || reportid == '' )
	{
		reportid = '';
	}
	else
	{
		reportid = reportid + '_';
	}
	if( typeof( reporttype ) == 'undefined' || reporttype == '' )
	{
		reporttype = '';
	}
	var menulength = menuarr.length;
	if(menulength>5){
		var width = "width:"+100/menulength+"%";
	}
	else{
		var width = "width:20%";
	}
	var menucodelist = menuselectid = '';
	var allmi = 0;
	for(var mi in menuarr)
	{	
        if(isNaN(parseInt(mi)))
		    continue;
		allmi++;
		menuselectid = '';
		if(selectid == mi)
		{
			menuselectid = "class=\"selected\"";
		}
		menucodelist += "<li "+menuselectid+" style="+width+" onclick=\"show_select_report("+allmi+","+menulength+",'"+reportid+"','"+reporttype+"');\" id=\"show_click_"+reportid+(allmi)+"\"><span class='word'>"+menuarr[mi]+"</span><span class='bg'></span></li>";
		
		if( allmi > 7 )
		{
			break;
		}
	}
	document.write(menucodelist);
}
function clicknewpage(geturl,newpage,clicktype)
{
	var i,j= 0,k = 0;
	var currenturl = self.location.search.replace(/[?]/i, '');
	var newgetarray = new Array();
	var newhtmlarray = new Array();
	var returnhtmlarray = new Array();
	var subkeyarray     = new Array();

	if( typeof( clicktype ) == 'undefined' || clicktype == 'undefined')
	{
		clicktype = '';
	}
	if( typeof( newpage ) == 'undefined' || newpage == 'undefined')
	{
		newpage = '';
	}
	if( typeof( geturl ) == 'undefined' || geturl == 'undefined')
	{
		geturl = '';
	}
	newhtmlarray   = currenturl.split('&');
	newgetarray    = geturl.split('&');

	for(i=0;i<newhtmlarray.length;i++)
	{
		if( newhtmlarray[i] )
		{
			subkeyarray = newhtmlarray[i].split('=');
			returnhtmlarray["'"+subkeyarray[0]+"'"] = subkeyarray[0]+'='+subkeyarray[1];
		}
	}
	for(i=0;i<newgetarray.length;i++)
	{
		if( newgetarray[i] )
		{
			subkeyarray = newgetarray[i].split('=');
			returnhtmlarray["'"+subkeyarray[0]+"'"] = subkeyarray[0]+'='+subkeyarray[1];
		}
	}
	var returnstr = '';
	var returnarray = new Array();
	for(j in returnhtmlarray)
	{
		if(j == 'shuffle')
			break;
		var jj = j.replace(/\'/g,'');
		if( ( jj == 'website' || jj == 'server' || jj == 'eaction' || geturl.indexOf('&' + jj + '=') > -1 ) && clicktype == 'report' )
		{
			returnarray[k] = returnhtmlarray[j];
			k++;
		}
		if( clicktype != 'report' || clicktype == '' )
		{
			returnarray[k] = returnhtmlarray[j];
			k++;
		}
	}
	window.location.href = newpage+'?'+returnarray.join("&");
}
function create_ul_li_html(menuarr,selectid)
{
	var mi = mik = '';
	var maxnum = 8;
	var menulength = menuarr.length;
	var menucodelist = menuselectid = menuselectoptid = '';
	var menuoption = '';
	var imgsrc = '';
	for(mik in menuarr)
	{
		menuselectid = menuselectoptid = '';
		if(selectid == mik)
		{
			menuselectid = "class=\"selected\"";
			menuselectoptid = 'selected';
		}
		if( typeof(menuarr[mik].page) == 'undefined' || menuarr[mik].page == 'undefined' )
		{
			menuarr[mik].page = '';
		}
		if( mi <= maxnum )
		{
			imgsrc = '';
			if( menuarr[mik].isnew )
			{
				imgsrc = "<img id=\"img_1\" src=\"../templates/ms/imagefiles/news.gif\" style=\"padding-top:2px;padding-right:2px;\" align=\"absmiddle\" border=\"0\"/>";
			}
			menucodelist += "<li "+menuselectid+" onclick=\"javascript:clicknewpage('"+menuarr[mik].url+"','"+menuarr[mik].page+"');\" title=\""+menuarr[mik].name+"\" alt=\""+menuarr[mik].name+"\"><span class='word'>"+menuarr[mik].name.cnsub(36)+"</span>"+imgsrc+"<span class='bg'></span></li>";
		}
		if( mi > maxnum )
		{
			menuoption += "<option value=\""+menuarr[mik].url+"\" "+menuselectoptid+">"+menuarr[mik].name+"</option>";
		}	
		mi++;
	}
	if( menuoption )
	{
		menuoption = "<li><span class='word'><select id=\"searchkey\" onchange=\"javascript:clicknewpage(this.options[this.options.selectedIndex].value)\" style=\"width:120px;\">" + "<option value=\"\">-------</option>" + menuoption + "</select></span><span class='bg'></span></li>";
	}
	menucodelist += menuoption;
	document.write(menucodelist);
}
function create_select_html(menuarr,selectid,doonclick,doname,defaultvalue)
{
	var mik = '';
	var menulength = menuarr.length;
	var menucodelist = menuselectoptid = '';
	var menuoption = '';
	var gdefaultvalue = 0;
	if( typeof(defaultvalue) == 'undefined' || defaultvalue == 'undefined' )
	{
		gdefaultvalue = 0;
	}
	else
	{
		gdefaultvalue = defaultvalue;
	}
	for(mik in menuarr)
	{
		menuselectoptid = '';
		if(selectid == mik)
		{
			menuselectoptid = 'selected';
		}
		if( typeof(menuarr[mik].page) == 'undefined' || menuarr[mik].page == 'undefined' )
		{
			menuarr[mik].page = '';
		}
		menuoption += "<option value=\""+menuarr[mik].url+"\" "+menuselectoptid+">"+menuarr[mik].name+"</option>";
	}
	if( menuoption && doonclick == 'onclick' )
	{
		menuoption = "<span class='word'><select id=\"searchkey_" + doname + "\" onchange=\"javascript:clicknewpage(this.options[this.options.selectedIndex].value)\" style=\"margin-top:5px;width:120px;\" name=\"" + doname + "\">" + "<option value=\"&" + doname + "=" + gdefaultvalue + "\">-------</option>" + menuoption + "</select></span><span class='bg'></span>";
	}
	if( menuoption && doonclick == '' )
	{
		menuoption = "<span class='word'><select id=\"searchkey_" + doname + "\"  name=\"" + doname + "\">" + "<option value=\"&" + doname + "=" + gdefaultvalue + "\">-------</option>" + menuoption + "</select></span><span class='bg'></span>";
	}
	menucodelist += menuoption;
	document.write(menucodelist);
}
function create_ul_li_no_html(menuarr,selectid,imgid)
{
	var mi = 0;
	var mik = '';
	var maxnum = 8;
	var imgidsrc = '';
	var menulength = menuarr.length;
	var menuselectid = menuselectoptid = '';
	var menuoption = '';
	var menucodelist = new Array();	
	var imgsrc = '';
	for(mik in menuarr)
	{   
        if(mik == 'shuffle')
			break;
		menuselectid = menuselectoptid = '';		
		if( typeof(imgid) != 'undefined' || imgid == 'undefined' )
		{
			menuselectid = "jsselectedimg ";
		}
		if(selectid == mik)
		{
			menuselectid = menuselectid + "jsselected";
			menuselectoptid = 'selected';
		}
		if( mi <= maxnum )
		{
			if( menuarr[mik].isnew )
			{
				imgsrc = "<img id=\"img_1\" src=\"../templates/ms/imagefiles/news.gif\" style=\"padding-top:2px;padding-right:2px;\" align=\"absmiddle\" border=\"0\"/>";
			}
			menucodelist[mi] = "<a class=\""+menuselectid+"\" onclick=\"javascript:clicknewpage('"+menuarr[mik].url+"','"+menuarr[mik].page+"');\" title=\""+menuarr[mik].name+"\" alt=\""+menuarr[mik].name+"\" style=\"cursor:pointer;\">"+menuarr[mik].name.cnsub(36)+"</a>"+imgsrc+"";
		}
		if( mi > maxnum )
		{
			menuoption += "<option value=\""+menuarr[mik].url+"\" "+menuselectoptid+">"+menuarr[mik].name+"</option>";
		}	
		mi++;
	}
	if( menuoption )
	{
		menuoption = "&nbsp;&nbsp;<select id=\"searchkey\" style=\"width:128px;\" onchange=\"javascript:clicknewpage(this.options[this.options.selectedIndex].value)\">" + "<option value=\"\">-----</option>" + menuoption + "</select>";
	}
	document.write(menucodelist.join("")+menuoption);
}
function create_ul_li_no_html_report(menuarr,selectid,imgid)
{
	var mi = 0;
	var mik = '';
	var meik = '';
	var maxnum = 8;
	var imgidsrc = '';
	var menulength = menuarr.length;
	var menuselectid = menuselectoptid = '';
	var menuoption = '';
	var menucodelist = new Array();	
	var imgsrc = '';
	var npage = '';
	var isshow = true;
	var showarr = new Array();
	for(mik in menuarr)
	{
		if(mik == 'shuffle')
			break;
		menuselectid = menuselectoptid = '';		
		if( typeof(imgid) != 'undefined' || imgid == 'undefined' )
		{
			menuselectid = "jsselectedimg ";
		}
		if(selectid == mik)
		{
			menuselectid = menuselectid + "jsselected";
			menuselectoptid = 'selected';
		}
		if( menuarr[mik].isnew )
		{
			imgsrc = "<img id=\"img_1\" src=\"../templates/ms/imagefiles/news.gif\" style=\"padding-top:2px;padding-right:2px;\" align=\"absmiddle\" border=\"0\"/>";
		}	
		npage = menuarr[mik].page + '' + menuarr[mik].url;
		isshow = false;
		if( _$php_right_array.length > 0 )
		{
			for(meik in _$php_right_array)
			{
				showarr = _$php_right_array[meik].split("||");
				for (var meik2 = 0; meik2 < showarr.length; meik2++)
				{
					//document.write(meik2+"  "+ _$php_right_array[meik] +"  "+showarr[meik2]+"  "+npage+"<hr>");
					if( npage.indexOf(showarr[meik2]) > -1 || npage.indexOf(showarr[meik2]) > -1 )
					{
						isshow = true;break;
					}
				}
			}
		}
		else
		{
			isshow = true;
		}
		if( isshow == true )
		{
			menucodelist[mi] = "<a class=\""+menuselectid+"\" onclick=\"javascript:clicknewpage('"+menuarr[mik].url+"','"+menuarr[mik].page+"','report');\" title=\""+menuarr[mik].name+"\" alt=\""+menuarr[mik].name+"\" style=\"cursor:pointer;\">"+menuarr[mik].name.cnsub(36)+"</a>"+imgsrc+"";		
			mi++;
		}
	}
	document.write("<div class='report_tab clearfix'>"+menucodelist.join("")+"</div>");
}
function read_cookie(name)
{
  var cookieValue = "";
  var search = name + "=";
  if(document.cookie.length > 0)
  { 
    offset = document.cookie.indexOf(search);
    if (offset != -1)
    { 
      offset += search.length;
      end = document.cookie.indexOf(";", offset);
      if (end == -1) end = document.cookie.length;
      cookieValue = unescape(document.cookie.substring(offset, end))
    }
  }
  return cookieValue;
}

function write_cookie(name, value, hours)
{
  var expire = "";
  if(hours != null)
  {
    expire = new Date((new Date()).getTime() + hours * 3600000);
    expire = "; expires=" + expire.toGMTString();
  }
  document.cookie = name + "=" + escape(value) + expire + "domain=;" + "path=/;";
}

function form_get_time_submit(objform)
{
	//GET表单提交
	var starttime  = objform.starttime.value;
	var endtime    = objform.endtime.value;
	if( objform.starttime_c && objform.endtime_c )
	{
		var starttime_c = objform.starttime_c.value;
		var endtime_c   = objform.endtime_c.value;
		clicknewpage('&starttime='+starttime+'&endtime='+endtime+'&starttime_c='+starttime_c+'&endtime_c='+endtime_c,'');
	}
	else
	{
		clicknewpage('&starttime='+starttime+'&endtime='+endtime,'');
	}
}
//结果过滤+每页结果条数函数ebuy.html
function search_form_func(formname)
{
	var searchtypevalue = formname.searchtype.options[formname.searchtype.options.selectedIndex].value;
	var pagesizevalue = formname.pagesize.options[formname.pagesize.options.selectedIndex].value;
	clicknewpage('&searchkey='+formname.searchkey.value+'&searchtype='+searchtypevalue+'&pagesize='+pagesizevalue,'');
}

function jump_menu(selid,newpageurl){
  clicknewpage(selid.options[selid.selectedIndex].value,newpageurl);
}

function jump_menu_url(geturl,newpageurl){
  clicknewpage(geturl,newpageurl);
}

function copy_code(id){  

}  
function copy_code_(id){  
   	var clip = null;		
	function $(id) { return document.getElementById(id); }		
	function init(id) 
	{
		clip = new ZeroClipboard.Client();
		clip.setHandCursor( true );			
		clip.addEventListener('load', function (client) {});			
		clip.addEventListener('mouseOver', function (client) {clip.setText( $(id).value );});			
		clip.addEventListener('complete', function (client, text) {});			
		clip.glue( 'd_clip_button', 'd_clip_container' );
	}
	init(id);
} 

//iframe高度
function reinitIframe(id)
{
	var iframe = document.getElementById(id);
	try
	{
		var bHeight = iframe.contentWindow.document.body.scrollHeight;
		var dHeight = iframe.contentWindow.document.documentElement.scrollHeight;
		
		var bWidth = iframe.contentWindow.document.body.scrollWidth;
		var dWidth = iframe.contentWindow.document.documentElement.scrollWidth;

		var height = Math.max(bHeight, dHeight);
		var width = Math.max(bWidth, dWidth);
		iframe.height =  height;
		//iframe.width =  width;
	}
	catch (ex)
	{
	}
}
//iframe高度，有下拉条
function setframeheight(id,height)
{		
	height = height <= 0 ? 180 : height;
	document.getElementById(id).height = (document.documentElement.clientHeight - height);	
}
//以下3个是点击热图，链接图使用函数
function openclickhot(newurl,pageurl,hourrange,key)
{
	if( hourrange == '24,24' ) return;
	if( newurl && hourrange != 'custom'  )
	window.open(newurl+'&page='+(pageurl)+'&hourrange='+hourrange+'&width='+parent.window.screen.width+'&height='+parent.window.screen.height+'&hottype=clickhot');
}
function openclickarea(newurl,pageurl)
{
	window.open(newurl+'&page='+(pageurl)+'&url='+(pageurl));
}
function openclickview(newurl,pageurl)
{
	window.open(newurl+'&page='+(pageurl)+'&width='+parent.window.screen.width+'&height='+parent.window.screen.height+'');
}


function showsql()
{
	var newurl = window.location.href.replace('#', '');
	newurl = newurl.replace('&showsql=1&showcache=1', '');
	window.location.href = newurl+"&showsql=1&showcache=1";
}


//菜单函数
 jQuery.fn.showSeach = function(showtext,closetext,statusname){
	statusname = 'statusname';
	if(statusname!='' & statusname!='all')
	{
		$('.search_list div').show();
		$('.showsearch').text(closetext);
	}
	else
	{
		$('.search_list div').hide().first().show();
	}
	$(this).click(function()
	{
		if($(this).text()==showtext)
		{
			$('.search_list div').show();
			$(this).text(closetext);
		}
		else
		{
			$('.search_list div').hide().first().show();
			$(this).text(showtext);
		}
	});
 }