var isFullScreen = false;
document.getElementById("fullscreen").onclick=function(){
  if(!isFullScreen){
	  try{
		  window.parent.document.getElementById("resourceFrame").style['position'] = "fixed";
		  window.parent.document.getElementById("resourceFrame").style['left'] = "0px";
		  window.parent.document.getElementById("resourceFrame").style['top'] = "0px";
		  window.parent.document.getElementById("resourceFrame").style['z-index'] = "999999";
		  window.parent.document.getElementById("resourceFrame").style['height'] = "100%";
		  window.top.document.getElementById("slide_title").style['display'] = "none";
       //window.parent.document.getElementById("resourceFrame").style = "position:fixed;left:0px;top:0px;z-index:999999;height:100%";
       //slide_title
       //window.top.document.getElementById("slide_title").style="display:none";
	  }catch(e){;}
  }
  else{
	  try{
		  window.parent.document.getElementById("resourceFrame").style['position'] = "";
		  window.parent.document.getElementById("resourceFrame").style['left'] = "";
		  window.parent.document.getElementById("resourceFrame").style['top'] = "";
		  window.parent.document.getElementById("resourceFrame").style['z-index'] = "";
		  window.parent.document.getElementById("resourceFrame").style['height'] = "";
		  window.top.document.getElementById("slide_title").style['display'] = "";
		  
		  //window.parent.document.getElementById("resourceFrame").style = "";
          //window.top.document.getElementById("slide_title").style="display:block";       
	  }catch(e){;}
  }
  isFullScreen = !isFullScreen;
}