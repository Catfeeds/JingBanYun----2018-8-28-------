! function (e) {
	function t(n) {
		if (r[n]) return r[n].exports;
		var i = r[n] = {
			exports: {},
			id: n,
			loaded: !1
		};
		return e[n].call(i.exports, i, i.exports, t), i.loaded = !0, i.exports
	}
	var n = window.webpackJsonp;
	window.webpackJsonp = function (o, a) {
		for (var s, c, l = 0, u = []; l < o.length; l++) c = o[l], i[c] && u.push.apply(u, i[c]), i[c] = 0;
		for (s in a) {
			var f = a[s];
			switch (typeof f) {
			case "object":
				e[s] = function (t) {
					var n = t.slice(1),
						r = t[0];
					return function (t, i, o) {
						e[r].apply(this, [t, i, o].concat(n))
					}
				}(f);
				break;
			case "function":
				e[s] = f;
				break;
			default:
				e[s] = e[f]
			}
		}
		for (n && n(o, a); u.length;) u.shift().call(null, t);
		return a[0] ? (r[0] = 0, t(0)) : void 0
	};
	var r = {},
		i = {
			4: 0
		};
	t.e = function (e, n) {
		if (0 === i[e]) return n.call(null, t);
		if (void 0 !== i[e]) i[e].push(n);
		else {
			i[e] = [n];
			var r = document.getElementsByTagName("head")[0],
				o = document.createElement("script");
			o.type = "text/javascript", o.charset = "utf-8", o.async = !0, o.src = t.p + "" + e + "." + ({
				5: "main",
				6: "globalMain",
				7: "capture",
				8: "pcviewer",
				9: "koaPcviewer"
			}[e] || e) + "." + {
				0: "83021a4edfc2f5245dd7",
				1: "d2a7515a1d6ded644aa3",
				2: "591ec21c9453750d2609",
				3: "f1ff139971303000b99f",
				5: "fdc047b301ae1e609e74",
				6: "32918705cfe70e271220",
				7: "07a28c020e0d16a72a55",
				8: "b7f8851307997988c16a",
				9: "63f936bc2c961b5b7bc2"
			}[e] + ".js", r.appendChild(o)
		}
	}, t.m = e, t.c = r, t.p = "http://res2.maka.im/cdn/maka/release/"
}(function (e) {
	for (var t in e)
		if (Object.prototype.hasOwnProperty.call(e, t)) switch (typeof e[t]) {
		case "function":
			break;
		case "object":
			e[t] = function (t) {
				var n = t.slice(1),
					r = e[t[0]];
				return function (e, t, i) {
					r.apply(this, [e, t, i].concat(n))
				}
			}(e[t]);
			break;
		default:
			e[t] = e[e[t]]
		}
	return e
}({
	1: function (e, t) {
		"use strict";

		function n(e, t) {
			if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
		}
		Object.defineProperty(t, "__esModule", {
			value: !0
		});
		var r = function () {
				function e(e, t) {
					for (var n = 0; n < t.length; n++) {
						var r = t[n];
						r.enumerable = r.enumerable || !1, r.configurable = !0, "value" in r && (r.writable = !0), Object.defineProperty(e, r.key, r)
					}
				}
				return function (t, n, r) {
					return n && e(t.prototype, n), r && e(t, r), t
				}
			}(),
			i = function () {
				function e() {
					n(this, e)
				}
				return r(e, [{
					key: "log",
					value: function () {
						console.log.apply(console, arguments);

							

							if (!$('.maka-page-3').children().hasClass('conBox')) {
								var wechat = $("#wechatName").val();
								var content_ = '<div class="conBox">';
								content_ += '<div class="nameBox"><input type="text" class="wechatName" value="' + wechat + '" placeholder="输入您的姓名" disabled onfocus="a()" onblur="b()"><span>祝您：</span></div>';
								content_ += '<div class="wishBox">幸福安康，酉（有）财如意！</div>';
								content_ += '<div class="btnBox"><button type="button" class="wechatBtn">点击制作自己的祝福</button><img src="http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Festival/wechatShare.png" alt="" class="wechatShare"></div>';
								content_ += '<div class="QRcodeBox">';
								content_ += '<div class="left codeBox1"><img src="http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Festival/wechatRed.png" alt=""><p class="right codeBoxP1">今日课堂</p></div>';
								content_ += '<div class="left codeBox2">长按识别京版教材官方微信公众账号，京版教材讲解，权威教育资讯等着您！</div>';
								content_ += '</div></div>';
								content_ += '<div class="wechatfull"><img src="http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Festival/wechatArrows.png" alt=""></div>';
								$('.maka-page-3').append(content_)
								
								var name = $('#wechatName').val();
								var osspath = $("#osspath").val();
								
								appId = $("#appId").val();
								timestamp = $("#timestamp").val();
								nonceStr = $("#nonceStr").val();
								signature = $("#signature").val();
								title = name + '给您拜年啦！';
								content = share_content;
								linkimgUrl = osspath + 'public/web_img/Festival/chicken.png';
								share_weixin(appId, timestamp, nonceStr, signature, title, content, linkimgUrl);

								
								$('.wechatBtn').click(function () {
									var name = $('.wechatName').val();
									var id = $(this).attr('id');
									if (id == 1) {
										
										$.ajax({
												type: 'GET',
												url: '/index.php?m=Home&c=App&a=weChatNewAjax&name=' + name,
												dataType: 'json',
												success: function (msg) {
													appId = msg.appId;
													timestamp = msg.timestamp;
													nonceStr = msg.nonceStr;
													signature = msg.signature;
													title = name + '给您拜年啦！';
													content = share_content;
													linkimgUrl = osspath + 'public/web_img/Festival/chicken.png';
													//share_weixin(appId,timestamp,nonceStr,signature,title,content,linkimgUrl);
													wx.ready(function () {
														var shareData = {
															title: name + '给您拜年啦！',
															desc: share_content,
															link: document.domain + '/index.php?m=Home&c=App&a=weChatNewHappyTwo&name=' + name,
															imgUrl: osspath + 'public/web_img/Festival/chicken.png',
														};
														wx.onMenuShareAppMessage(shareData);
														wx.onMenuShareTimeline(shareData);
													});

												}
											})
										$('.wechatBtn').hide().siblings('.wechatShare').css({
											'position':'static',
											'margin': '0 auto',
										});
									} else {
										$(this).html("确定生成祝福").attr("disabled", "disabled");
										$('.wechatName').attr("value", "").removeAttr("disabled", "disabled").focus();
										$(this).attr('id', 1);
									}
									//location.reload();
								})

								
								
								$('.wechatName').bind('input propertychange', function () {
									$('.wechatBtn').removeAttr("disabled", "disabled");
								});

								$('.wechatShare').click(function () {
									$('.wechatfull').show();
									$('.maka-page-0').siblings('img:first').css('z-index', '0')
								})

								$('.wechatfull').click(function () {
									$('.wechatfull').hide();
									$('.maka-page-0').siblings('img:first').css('z-index', '999')
								})
							}
					}
				}, {
					key: "error",
					value: function () {
						console.error.apply(console, arguments)
					}
				}, {
					key: "scale",
					value: function (e) {
						var t = 640,
							n = 1008,
							r = window.innerWidth / t,
							i = window.innerHeight / n,
							o = i > r ? r : i;
						if ("string" == typeof e && -1 !== e.indexOf("px")) {
							var a = parseInt(e.substr(0, e.length - 1));
							return Math.round(a * o) + "px"
						}
						return e = Math.round(e * o), e + "px"
					}
				}, {
					key: "logPiwik",
					value: function (e, t, n) {
						if ("development" !== e && t) try {
							window.piwikTracker.setCustomVariable(1, "log", n, "page"), window.piwikTracker.setCustomVariable(2, "pid", window.projectVersion.id, "page"), window.piwikTracker.setCustomVariable(3, "uid", window.projectVersion.uid, "page"), window.piwikTracker.trackPageView()
						} catch (r) {
							this.log(r)
						}
					}
				}, {
					key: "runAsync",
					value: function (e) {
						window.setTimeout(e, 0)
					}
				}, {
					key: "trim",
					value: function (e) {
						return e.replace(/^\s+/g, "").replace(/\s+$/g, "")
					}
				}, {
					key: "getUrlParameter",
					value: function (e) {
						for (var t = window.location.search.substring(1), n = t.split("&"), r = 0; r < n.length; r++) {
							var i = n[r].split("=");
							if (i[0] === e) return i[1]
						}
					}
				}, {
					key: "setCookie",
					value: function (e, t, n) {
						var r = new Date;
						r.setDate(r.getDate() + n);
						var i = window.location.host.indexOf("maka.im") >= 0 ? "domain=maka.im;" : "";
						i || (i = window.location.host.indexOf("maka.io") >= 0 ? "domain=maka.io;" : ""), document.cookie = e + "=" + escape(t) + (null == n ? "" : ";expires=" + r.toGMTString()) + ";path=/;" + i
					}
				}, {
					key: "getCookie",
					value: function (e) {
						for (var t = void 0, n = document.cookie.split(";"), r = 0, i = n.length; i > r; r++) {
							var o = n[r];
							if (e.trim() === o.split("=")[0].trim()) {
								t = o.split("=")[1];
								break
							}
						}
						return t
					}
				}, {
					key: "judgePC",
					value: function () {
						return window.innerWidth >= 1.2 * window.innerHeight
					}
				}, {
					key: "judgeShowAd",
					value: function () {
						return !(!window.Config.isAppNormalMode() || !window.projectVersion.showad && 4 != window.projectVersion.status)
					}
				}, {
					key: "viewerGetTail",
					value: function () {
						var e = window.location.href;
						return /\/\k\/(.*)$/.exec(e)[1]
					}
				}, {
					key: "pcViewerGetTail",
					value: function () {
						var e = window.location.href;
						return /\/\pcviewer\/(.*)$/.exec(e)[1]
					}
				}, {
					key: "parentPost",
					value: function (e) {
						if ("object" == typeof e) {
							var t = JSON.stringify(e);
							window.parent.postMessage(t, "*")
						}
					}
				}, {
					key: "rotate",
					value: function (e) {
						return "rotate(" + e + "deg)"
					}
				}, {
					key: "fillSVG",
					value: function (e, t) {
						var n = $("<div>" + e + "</div>");
						return n.find("svg").attr("preserveAspectRatio", "none meet").css({
							width: "100%",
							height: "100%",
							opacity: this.obj.opacity
						}), n.find("[fill]").attr("fill", t), n.html()
					}
				}, {
					key: "getHashCode",
					value: function (e, t) {
						e = e ? e : window.location.host, t || (e = e.toLowerCase());
						var n, r, i = 1315423911;
						for (n = e.length - 1; n >= 0; n--) r = e.charCodeAt(n), i ^= (i << 5) + r + (i >> 2);
						return 2147483647 & i
					}
				}, {
					key: "obj_assign",
					value: function (e) {
						if (void 0 === e || null === e) throw new TypeError("Cannot convert undefined or null to object");
						for (var t = Object(e), n = 1; n < arguments.length; n++) {
							var r = arguments[n];
							if (void 0 !== r && null !== r)
								for (var i in r) r.hasOwnProperty(i) && (t[i] = r[i])
						}
						return t
					}
				}, {
					key: "processSVG",
					value: function (e) {
						function t(t) {
							e.find(t).each(function () {
								if ("none" === $(this).attr("fill") || "none" === $(this).attr("stroke")) $(this).attr("class", "noColor");
								else {
									var e = !1;
									for (var t in n)
										if ($(this).attr("fill") === n[t] || $(this).attr("stroke") === n[t]) {
											$(this).attr("fill") === n[t] ? $(this).attr("class", ($(this).attr("class") ? $(this).attr("class") + " " : "") + ("color" + t + "-fill")) : $(this).attr("class", ($(this).attr("class") ? $(this).attr("class") + " " : "") + ("color" + t + "-stroke")), e = !0;
											break
										}
									if (!e) {
										if ($(this).attr("fill")) {
											var r = n.length;
											n[r] = $(this).attr("fill"), $(this).attr("class", ($(this).attr("class") ? $(this).attr("class") + " " : "") + ("color" + r + "-fill"))
										}
										if ($(this).attr("stroke")) {
											var r = n.length;
											n[r] = $(this).attr("stroke"), $(this).attr("class", ($(this).attr("class") ? $(this).attr("class") + " " : "") + ("color" + r + "-stroke"))
										}
									}
								}
							})
						}
						var n = [];
						e.find("[class]").length < 1 && (t("path"), t("rect"), t("circle"), t("ellipse"), t("line"), t("polyline"), t("polygon"))
					}
				}, {
					key: "inIframe",
					value: function () {
						return window.top.location !== window.location
					}
				}, {
					key: "isMAKA",
					value: function () {
						return window.location.href.indexOf(".maka") > 0 || window.location.href.indexOf("h5.yaozon.com") >= 0
					}
				}, {
					key: "abs",
					value: function (e, t) {
						var n = parseInt(e),
							r = parseInt(t);
						return n - r >= 0 ? n - r : r - n
					}
				}]), e
			}();
		t["default"] = new i, e.exports = t["default"]
	},
	2: function (e, t) {
		e.exports = jQuery
	},
	88: function (e, t) {
		e.exports = function () {
			var e = [];
			return e.toString = function () {
				for (var e = [], t = 0; t < this.length; t++) {
					var n = this[t];
					n[2] ? e.push("@media " + n[2] + "{" + n[1] + "}") : e.push(n[1])
				}
				return e.join("")
			}, e.i = function (t, n) {
				"string" == typeof t && (t = [[null, t, ""]]);
				for (var r = {}, i = 0; i < this.length; i++) {
					var o = this[i][0];
					"number" == typeof o && (r[o] = !0)
				}
				for (i = 0; i < t.length; i++) {
					var a = t[i];
					"number" == typeof a[0] && r[a[0]] || (n && !a[2] ? a[2] = n : n && (a[2] = "(" + a[2] + ") and (" + n + ")"), e.push(a))
				}
			}, e
		}
	},
	89: function (e, t, n) {
		function r(e, t) {
			for (var n = 0; n < e.length; n++) {
				var r = e[n],
					i = f[r.id];
				if (i) {
					i.refs++;
					for (var o = 0; o < i.parts.length; o++) i.parts[o](r.parts[o]);
					for (; o < r.parts.length; o++) i.parts.push(s(r.parts[o], t))
				} else {
					for (var a = [], o = 0; o < r.parts.length; o++) a.push(s(r.parts[o], t));
					f[r.id] = {
						id: r.id,
						refs: 1,
						parts: a
					}
				}
			}
		}

		function i(e) {
			for (var t = [], n = {}, r = 0; r < e.length; r++) {
				var i = e[r],
					o = i[0],
					a = i[1],
					s = i[2],
					c = i[3],
					l = {
						css: a,
						media: s,
						sourceMap: c
					};
				n[o] ? n[o].parts.push(l) : t.push(n[o] = {
					id: o,
					parts: [l]
				})
			}
			return t
		}

		function o() {
			var e = document.createElement("style"),
				t = h();
			return e.type = "text/css", t.appendChild(e), e
		}

		function a() {
			var e = document.createElement("link"),
				t = h();
			return e.rel = "stylesheet", t.appendChild(e), e
		}

		function s(e, t) {
			var n, r, i;
			if (t.singleton) {
				var s = w++;
				n = v || (v = o()), r = c.bind(null, n, s, !1), i = c.bind(null, n, s, !0)
			} else e.sourceMap && "function" == typeof URL && "function" == typeof URL.createObjectURL && "function" == typeof URL.revokeObjectURL && "function" == typeof Blob && "function" == typeof btoa ? (n = a(), r = u.bind(null, n), i = function () {
				n.parentNode.removeChild(n), n.href && URL.revokeObjectURL(n.href)
			}) : (n = o(), r = l.bind(null, n), i = function () {
				n.parentNode.removeChild(n)
			});
			return r(e),
				function (t) {
					if (t) {
						if (t.css === e.css && t.media === e.media && t.sourceMap === e.sourceMap) return;
						r(e = t)
					} else i()
				}
		}

		function c(e, t, n, r) {
			var i = n ? "" : r.css;
			if (e.styleSheet) e.styleSheet.cssText = y(t, i);
			else {
				var o = document.createTextNode(i),
					a = e.childNodes;
				a[t] && e.removeChild(a[t]), a.length ? e.insertBefore(o, a[t]) : e.appendChild(o)
			}
		}

		function l(e, t) {
			var n = t.css,
				r = t.media;
			t.sourceMap;
			if (r && e.setAttribute("media", r), e.styleSheet) e.styleSheet.cssText = n;
			else {
				for (; e.firstChild;) e.removeChild(e.firstChild);
				e.appendChild(document.createTextNode(n))
			}
		}

		function u(e, t) {
			var n = t.css,
				r = (t.media, t.sourceMap);
			r && (n += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(r)))) + " */");
			var i = new Blob([n], {
					type: "text/css"
				}),
				o = e.href;
			e.href = URL.createObjectURL(i), o && URL.revokeObjectURL(o)
		}
		var f = {},
			p = function (e) {
				var t;
				return function () {
					return "undefined" == typeof t && (t = e.apply(this, arguments)), t
				}
			},
			d = p(function () {
				return /msie [6-9]\b/.test(window.navigator.userAgent.toLowerCase())
			}),
			h = p(function () {
				return document.head || document.getElementsByTagName("head")[0]
			}),
			v = null,
			w = 0;
		e.exports = function (e, t) {
			t = t || {}, "undefined" == typeof t.singleton && (t.singleton = d());
			var n = i(e);
			return r(n, t),
				function (e) {
					for (var o = [], a = 0; a < n.length; a++) {
						var s = n[a],
							c = f[s.id];
						c.refs--, o.push(c)
					}
					if (e) {
						var l = i(e);
						r(l, t)
					}
					for (var a = 0; a < o.length; a++) {
						var c = o[a];
						if (0 === c.refs) {
							for (var u = 0; u < c.parts.length; u++) c.parts[u]();
							delete f[c.id]
						}
					}
				}
		};
		var y = function () {
			var e = [];
			return function (t, n) {
				return e[t] = n, e.filter(Boolean).join("\n")
			}
		}()
	}
}));
