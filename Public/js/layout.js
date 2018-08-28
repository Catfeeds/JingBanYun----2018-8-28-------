(function (f) {
	if (typeof exports === "object" && typeof module !== "undefined") {
		module.exports = f()
	} else if (typeof define === "function" && define.amd) {
		define([], f)
	} else {
		var g;
		if (typeof window !== "undefined") {
			g = window
		} else if (typeof global !== "undefined") {
			g = global
		} else if (typeof self !== "undefined") {
			g = self
		} else {
			g = this
		}
		g.store = f()
	}
})(function () {
	var define, module, exports;
	return (function e(t, n, r) {
		function s(o, u) {
			if (!n[o]) {
				if (!t[o]) {
					var a = typeof require == "function" && require;
					if (!u && a) return a(o, !0);
					if (i) return i(o, !0);
					var f = new Error("Cannot find module '" + o + "'");
					throw f.code = "MODULE_NOT_FOUND", f
				}
				var l = n[o] = {
					exports: {}
				};
				t[o][0].call(l.exports, function (e) {
					var n = t[o][1][e];
					return s(n ? n : e)
				}, l, l.exports, e, t, n, r)
			}
			return n[o].exports
		}

		var i = typeof require == "function" && require;
		for (var o = 0; o < r.length; o++) s(r[o]);
		return s
	})({
		1: [function (require, module, exports) {
			"object" != typeof JSON && (JSON = {}),
				function () {
					"use strict";

					function f(t) {
						return 10 > t ? "0" + t : t
					}

					function this_value() {
						return this.valueOf()
					}

					function quote(t) {
						return rx_escapable.lastIndex = 0, rx_escapable.test(t) ? '"' + t.replace(rx_escapable, function (t) {
							var e = meta[t];
							return "string" == typeof e ? e : "\\u" + ("0000" + t.charCodeAt(0).toString(16)).slice(-4)
						}) + '"' : '"' + t + '"'
					}

					function str(t, e) {
						var r, n, o, u, f, a = gap,
							i = e[t];
						switch (i && "object" == typeof i && "function" == typeof i.toJSON && (i = i.toJSON(t)), "function" == typeof rep && (i = rep.call(e, t, i)), typeof i) {
						case "string":
							return quote(i);
						case "number":
							return isFinite(i) ? String(i) : "null";
						case "boolean":
						case "null":
							return String(i);
						case "object":
							if (!i) return "null";
							if (gap += indent, f = [], "[object Array]" === Object.prototype.toString.apply(i)) {
								for (u = i.length, r = 0; u > r; r += 1) f[r] = str(r, i) || "null";
								return o = 0 === f.length ? "[]" : gap ? "[\n" + gap + f.join(",\n" + gap) + "\n" + a + "]" : "[" + f.join(",") + "]", gap = a, o
							}
							if (rep && "object" == typeof rep)
								for (u = rep.length, r = 0; u > r; r += 1) "string" == typeof rep[r] && (n = rep[r], o = str(n, i), o && f.push(quote(n) + (gap ? ": " : ":") + o));
							else
								for (n in i) Object.prototype.hasOwnProperty.call(i, n) && (o = str(n, i), o && f.push(quote(n) + (gap ? ": " : ":") + o));
							return o = 0 === f.length ? "{}" : gap ? "{\n" + gap + f.join(",\n" + gap) + "\n" + a + "}" : "{" + f.join(",") + "}", gap = a, o
						}
					}

					var rx_one = /^[\],:{}\s]*$/,
						rx_two = /\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,
						rx_three = /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
						rx_four = /(?:^|:|,)(?:\s*\[)+/g,
						rx_escapable = /[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
						rx_dangerous = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
					"function" != typeof Date.prototype.toJSON && (Date.prototype.toJSON = function () {
						return isFinite(this.valueOf()) ? this.getUTCFullYear() + "-" + f(this.getUTCMonth() + 1) + "-" + f(this.getUTCDate()) + "T" + f(this.getUTCHours()) + ":" + f(this.getUTCMinutes()) + ":" + f(this.getUTCSeconds()) + "Z" : null
					}, Boolean.prototype.toJSON = this_value, Number.prototype.toJSON = this_value, String.prototype.toJSON = this_value);
					var gap, indent, meta, rep;
					"function" != typeof JSON.stringify && (meta = {
						"\b": "\\b",
						"	": "\\t",
						"\n": "\\n",
						"\f": "\\f",
						"\r": "\\r",
						'"': '\\"',
						"\\": "\\\\"
					}, JSON.stringify = function (t, e, r) {
						var n;
						if (gap = "", indent = "", "number" == typeof r)
							for (n = 0; r > n; n += 1) indent += " ";
						else "string" == typeof r && (indent = r);
						if (rep = e, e && "function" != typeof e && ("object" != typeof e || "number" != typeof e.length)) throw new Error("JSON.stringify");
						return str("", {
							"": t
						})
					}), "function" != typeof JSON.parse && (JSON.parse = function (text, reviver) {
						function walk(t, e) {
							var r, n, o = t[e];
							if (o && "object" == typeof o)
								for (r in o) Object.prototype.hasOwnProperty.call(o, r) && (n = walk(o, r), void 0 !== n ? o[r] = n : delete o[r]);
							return reviver.call(t, e, o)
						}

						var j;
						if (text = String(text), rx_dangerous.lastIndex = 0, rx_dangerous.test(text) && (text = text.replace(rx_dangerous, function (t) {
								return "\\u" + ("0000" + t.charCodeAt(0).toString(16)).slice(-4)
							})), rx_one.test(text.replace(rx_two, "@").replace(rx_three, "]").replace(rx_four, ""))) return j = eval("(" + text + ")"), "function" == typeof reviver ? walk({
							"": j
						}, "") : j;
						throw new SyntaxError("JSON.parse")
					})
				}();

        }, {}],
		2: [function (require, module, exports) {
			require("./json2"), module.exports = require("./store");
        }, {
			"./json2": 1,
			"./store": 3
        }],
		3: [function (require, module, exports) {
			(function (global) {
				"use strict";
				module.exports = function () {
					function e() {
						try {
							return o in n && n[o]
						} catch (e) {
							return !1
						}
					}

					var t, r = {},
						n = "undefined" != typeof window ? window : global,
						i = n.document,
						o = "localStorage",
						a = "script";
					if (r.disabled = !1, r.version = "1.3.20", r.set = function (e, t) {}, r.get = function (e, t) {}, r.has = function (e) {
							return void 0 !== r.get(e)
						}, r.remove = function (e) {}, r.clear = function () {}, r.transact = function (e, t, n) {
							null == n && (n = t, t = null), null == t && (t = {});
							var i = r.get(e, t);
							n(i), r.set(e, i)
						}, r.getAll = function () {}, r.forEach = function () {}, r.serialize = function (e) {
							return JSON.stringify(e)
						}, r.deserialize = function (e) {
							if ("string" == typeof e) try {
								return JSON.parse(e)
							} catch (t) {
								return e || void 0
							}
						}, e()) t = n[o], r.set = function (e, n) {
						return void 0 === n ? r.remove(e) : (t.setItem(e, r.serialize(n)), n)
					}, r.get = function (e, n) {
						var i = r.deserialize(t.getItem(e));
						return void 0 === i ? n : i
					}, r.remove = function (e) {
						t.removeItem(e)
					}, r.clear = function () {
						t.clear()
					}, r.getAll = function () {
						var e = {};
						return r.forEach(function (t, r) {
							e[t] = r
						}), e
					}, r.forEach = function (e) {
						for (var n = 0; n < t.length; n++) {
							var i = t.key(n);
							e(i, r.get(i))
						}
					};
					else if (i && i.documentElement.addBehavior) {
						var c, u;
						try {
							u = new ActiveXObject("htmlfile"), u.open(), u.write("<" + a + ">document.w=window</" + a + '><iframe src="/favicon.ico"></iframe>'), u.close(), c = u.w.frames[0].document, t = c.createElement("div")
						} catch (l) {
							t = i.createElement("div"), c = i.body
						}
						var f = function (e) {
								return function () {
									var n = Array.prototype.slice.call(arguments, 0);
									n.unshift(t), c.appendChild(t), t.addBehavior("#default#userData"), t.load(o);
									var i = e.apply(r, n);
									return c.removeChild(t), i
								}
							},
							d = new RegExp("[!\"#$%&'()*+,/\\\\:;<=>?@[\\]^`{|}~]", "g"),
							s = function (e) {
								return e.replace(/^d/, "___$&").replace(d, "___")
							};
						r.set = f(function (e, t, n) {
							return t = s(t), void 0 === n ? r.remove(t) : (e.setAttribute(t, r.serialize(n)), e.save(o), n)
						}), r.get = f(function (e, t, n) {
							t = s(t);
							var i = r.deserialize(e.getAttribute(t));
							return void 0 === i ? n : i
						}), r.remove = f(function (e, t) {
							t = s(t), e.removeAttribute(t), e.save(o)
						}), r.clear = f(function (e) {
							var t = e.XMLDocument.documentElement.attributes;
							e.load(o);
							for (var r = t.length - 1; r >= 0; r--) e.removeAttribute(t[r].name);
							e.save(o)
						}), r.getAll = function (e) {
							var t = {};
							return r.forEach(function (e, r) {
								t[e] = r
							}), t
						}, r.forEach = f(function (e, t) {
							for (var n, i = e.XMLDocument.documentElement.attributes, o = 0; n = i[o]; ++o) t(n.name, r.deserialize(e.getAttribute(n.name)))
						})
					}
					try {
						var v = "__storejs__";
						r.set(v, v), r.get(v) != v && (r.disabled = !0), r.remove(v)
					} catch (l) {
						r.disabled = !0
					}
					return r.enabled = !r.disabled, r
				}();

			}).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {}]
	}, {}, [2])(2)
});

var isFullScreen = false;
var conWraHeight,conWarFullHeight;
function fullScreen() {
	if (isFullScreen) {
		store.set('fullscreen', false);
		isFullScreen = false;
		
		$('.header,.topbar,.cloud1,.cloud_left,.cloud_right,.welcome').show();
		$('.content,.mainbody').removeClass('fullscreen_nopaddingtop');
		$('.mainbody,#contentWrapper,.nav,.main_opr,.fullscreen_ctrl').removeClass('fullscreen');
        // $('.btnReturn1, .btnReturn2, .btnReturn3').css('top','0');
        $('.btnReturn1, .btnReturn2, .btnReturn3').css('position','absolute');
		$('.activties_nav').css('margin-top','0');
		$('#escfullscreen_ctrl').hide();
		$('#fullscreen_ctrl,.bottombar').show();
		if($('.Pagination').children('div').children().last().hasClass('current')){
			conWarFullHeight = document.getElementById("contentWrapper").offsetHeight;
			// console.log('1conWraHeight:'+conWraHeight);
			// console.log('1conWarFullHeight:'+conWarFullHeight);
			// console.log( $('#contentWrapper').height())
			if(conWraHeight == 670 && $('#contentWrapper').height() == 670 && $('#contentWrapper').height()<=$(window).height()){
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				$('#contentWrapper').css('position','static');
				if (Pagination == 'object') {
					$('.Pagination').css({
						'position': 'absolute',
						'bottom': '10px',
						'left': 0,
						'right': 0
					})
				}
			} else if(conWraHeight == undefined && conWarFullHeight == 700) {
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				$('#contentWrapper').css('position','static');
				if (Pagination == 'object') {
					$('.Pagination').css({
						'position': 'absolute',
						'bottom': '10px',
						'left': 0,
						'right': 0
					})
				}
			} else {
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('.Pagination').css({
						'position': 'static',
						'bottom': 'auto',
						'left': 'auto',
						'right': 'auto'
					})
				}
			}
		}
		if (typeof adjustSubPageStyle == 'function') {
			adjustSubPageStyle();
		}
		$('#contentWrapper a').each(function () {
			this.href = this.href.replace('#fullscreen', '');
		});
		adjustPageLayout();
	} else {
		store.set('fullscreen', true);
		conWraHeight = $('#contentWrapper').height();
		isFullScreen = true;
		$('.header,.topbar,.cloud1,.cloud_left,.cloud_right,.welcome').hide();
		$('.content,.mainbody').addClass('fullscreen_nopaddingtop');
		$('.mainbody,#contentWrapper,.nav,.main_opr,.fullscreen_ctrl').addClass('fullscreen');
		$('html').css('height', '100%');
		$('body').css({
			'height': '100%',
			'background': '#fff'
		});
		// $('.btnReturn1, .btnReturn2, .btnReturn3').css('top','20px');
		$('.btnReturn1, .btnReturn2, .btnReturn3').css('position','relative');
		// console.log($('.main_opr').length)
		if($('.main_opr').length == '0'){
			$('#contentWrapper').append('<div class="main_opr fullscreen"></div>')
		}
		$('.activties_nav').css('margin-top','35px');
		if($('.Pagination').children('div').children().last().hasClass('current')){
			// console.log('2:'+conWraHeight);
			if($('#contentWrapper').height()<=$(window).height()){
				$('.content').css('height','100%');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'absolute',
						'bottom': '40px',
						'left': 0,
						'right': 0
					})
				}
			} else {
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'static',
						'bottom': 'auto',
						'left': 'auto',
						'right': 'auto'
					})
				}
			}
		}
		
		$('#fullscreen_ctrl,.bottombar').hide();
		$('#escfullscreen_ctrl').show();
		if (typeof adjustSubPageStyle == 'function') {
			adjustSubPageStyle();
		}
  
		$('#contentWrapper a').each(function () {
			this.href = this.href.replace('#fullscreen', '');
		});
		//        $('#contentWrapper a').each(function () {
		//            this.href = this.href 
		//        });
		//        window.location.hash 
		//
		//        var stateObject = {};
		//        var title = $('title').text();
		//        var newUrl = window.location.href;
		//        try {
		//            history.pushState(stateObject, title, newUrl + '#fullscreen');
		//        } catch (ex) {
		//
		//        }

	}
}

//function adjustPageLayout() {
//
//    if (!isFullScreen) {
//        $('#contentWrapper').removeAttr("style");
//        var cw = $('#contentWrapper').height()
//            //        $('#contentWrapper').height($(window).height() - 300);
//        if (cw < 500) {
//            $('#contentWrapper').height(700)
//        }
//    }
//}
//function adjustPageLayout() {
//    if (!isFullScreen) {
//    }
//}
$('.navThumImg').hover(function () {
	if(typeof $(this).rotate == 'function') {
		$(this).rotate({
			angle: 0,
			animateTo: 360
		});
	}
}, function () {
	var degree = $(this).attr('data-degree');
	if(typeof $(this).rotate == 'function') {
		$(this).rotate({
			angle: parseInt(degree)
		});
	}
}).click(function () {
	var url = $(this).attr('data-url');
	window.location.href = url;
});

function img_teacher(obj) {
	var sex = $(obj).attr('sex');
	if (sex=='男'){
		obj.src = './Public/img/classManage/teacher_m.png';
	}else{
		obj.src = './Public/img/classManage/teacher_w.png';
	}

}

function img_parent(obj) {
	var sex = $(obj).attr('sex');
	if (sex=='男'){
		obj.src = './Public/img/classManage/jiazhang.png';
	}else{
		obj.src = './Public/img/classManage/jiazhang2.png';
	}

}

function img_stu(obj) {
	var sex = $(obj).attr('sex');
	if (sex=='男'){
		obj.src = './Public/img/classManage/student_m.png';
	}else{
		obj.src = './Public/img/classManage/student_w.png';
	}

}

$(function () {
		var cw = $('#contentWrapper').height();
		//个人中心
		var ncw = cw + 32;
		if(cw == 700) {
			$('.leftWWrapper').height(cw);
		} else {
			$('.leftWWrapper').height(ncw);
		}
		var wrapperheight = typeof (contentheight);
		if (cw < 650 && wrapperheight == 'undefined') {
			adjustPageLayout();
			$(window).resize(adjustPageLayout);
			var Pagination = typeof ($('.Pagination'))
			if (Pagination == 'object') {
				$('.Pagination').css({
					'position': 'absolute',
					'bottom': '40px',
					'left': 0,
					'right': 0
				})

			}
		}else if(cw<650 && wrapperheight =='string'){
			$('#contentWrapper').css('padding-bottom','180px')
		}
	})

$(function () {

	var a = localStorage.getItem('fullscreen');
	if (a == 'true') {
		store.set('fullscreen', true);
		isFullScreen = true;
		$('.header,.topbar,.cloud1,.cloud_left,.cloud_right,.welcome').hide();
		$('.content,.mainbody').addClass('fullscreen_nopaddingtop');
		$('.mainbody,#contentWrapper,.nav,.main_opr,.fullscreen_ctrl').addClass('fullscreen');
		$('html').css('height', '100%')
		$('body').css({
			'height': '100%',
			'background': '#fff'
		})
		
		$('#fullscreen_ctrl,.bottombar').hide();
		$('#escfullscreen_ctrl').show();
		if (typeof adjustSubPageStyle == 'function') {
			adjustSubPageStyle();
		}
		$('#contentWrapper a').each(function () {
			this.href = this.href.replace('#fullscreen', '');
		});
		
		if($('.Pagination').children('div').children().last().hasClass('current')){
			// console.log('3:'+conWraHeight);
			if($('#contentWrapper').height()<=$(window).height()){
				$('.content').css('height','100%');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'absolute',
						'bottom': '40px',
						'left': 0,
						'right': 0
					})
				}
			} else {
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'static',
						'bottom': 'auto',
						'left': 'auto',
						'right': 'auto'
					})
				}
			}
		}
		
	} else {
		if($('.Pagination').children('div').children().last().hasClass('current')){
			conWraHeight = $('#contentWrapper').height();
			// console.log('4:'+conWraHeight);
			if($('#contentWrapper').height() == 670 && $('#contentWrapper').height()<=$(window).height()){
				$('.content').css('height','100%');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'absolute',
						'bottom': '10px',
						'left': 0,
						'right': 0
					})
				}
			} else {
				$('.content').css('height','auto');
				var Pagination = typeof ($('.Pagination'));
				if (Pagination == 'object') {
					$('#contentWrapper').css('position','static');
					$('.Pagination').css({
						'position': 'static',
						'bottom': 'auto',
						'left': 'auto',
						'right': 'auto'
					})
				}
			}
		}
	}
	$('.navThumImg').each(function (i, n) {
		var degree = $(this).attr('data-degree');
		if(typeof $(this).rotate == 'function') {
			$(this).rotate({
				angle: parseInt(degree)
			});
		}
	});
});

//图标显示Title
function showHide(img_, span_) {
	var navImg = document.getElementById(img_);
	var navSpan = document.getElementById(span_);
	navImg.onmouseover = function (event) {
		event = event || window.event;
		var e = event || window.event;
		var scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
		var scrollY = document.documentElement.scrollTop || document.body.scrollTop;
		var x = e.pageX || e.clientX + scrollX;
		var y = e.pageY || e.clientY + scrollY;
		var a = x + 10 + 'px';
		var v = y + 5 + 'px';
		navSpan.style.top = v;
		navSpan.style.left = a;
		navSpan.style.display = 'block';
	}
	navImg.onmouseleave = function (event) {
		navSpan.style.display = 'none'
	}
}
$('#gohome').hover(function(){
	$(this).attr('src','/Public/img/bar_home2.png')
},function(){
	$(this).attr('src','/Public/img/bar_home.png')
})
$('#gome').hover(function(){
	$(this).attr('src','/Public/img/bar_person2.png')
},function(){
	$(this).attr('src','/Public/img/bar_person.png')
})
$('#gohelp').hover(function(){
	$(this).attr('src','/Public/img/bar_help2.png')
},function(){
	$(this).attr('src','/Public/img/bar_help.png')
})
$('#goclose').hover(function(){
	$(this).attr('src','/Public/img/bar_logout2.png')
},function(){
	$(this).attr('src','/Public/img/bar_logout.png')
})
$('.main_opr').find('.btn-main-opr:gt(0)').css('margin-left','-28px')
var getl = store.get('fullscreen');

 if(getl == true ){
	 $('.activties_nav').css('margin-top','35px');
	 // $('.btnReturn1, .btnReturn2, .btnReturn3').css('top','20px');
	 $('.btnReturn1, .btnReturn2, .btnReturn3').css('position','relative');
	 if($('.main_opr').length == '0'){
			$('#contentWrapper').append('<div class="main_opr fullscreen"></div>')
	}
 }else{
	 // $('.btnReturn1, .btnReturn2, .btnReturn3').css('top','0');
	$('.btnReturn1, .btnReturn2, .btnReturn3').css('position','absolute');
	 $('.activties_nav').css('margin-top','0');
 }

