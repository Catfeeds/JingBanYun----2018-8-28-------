(function (e, t) {
	function H(e) {
		var t = e.length,
			n = w.type(e);
		return w.isWindow(e) ? !1 : e.nodeType === 1 && t ? !0 : n === "array" || n !== "function" && (t === 0 || typeof t == "number" && t > 0 && t - 1 in e)
	}

	function j(e) {
		var t = B[e] = {};
		return w.each(e.match(S) || [], function (e, n) {
			t[n] = !0
		}), t
	}

	function q(e, n, r, i) {
		if (!w.acceptData(e)) return;
		var s, o, u = w.expando,
			a = e.nodeType,
			f = a ? w.cache : e,
			l = a ? e[u] : e[u] && u;
		if ((!l || !f[l] || !i && !f[l].data) && r === t && typeof n == "string") return;
		l || (a ? l = e[u] = c.pop() || w.guid++ : l = u), f[l] || (f[l] = a ? {} : {
			toJSON: w.noop
		});
		if (typeof n == "object" || typeof n == "function") i ? f[l] = w.extend(f[l], n) : f[l].data = w.extend(f[l].data, n);
		return o = f[l], i || (o.data || (o.data = {}), o = o.data), r !== t && (o[w.camelCase(n)] = r), typeof n == "string" ? (s = o[n], s == null && (s = o[w.camelCase(n)])) : s = o, s
	}

	function R(e, t, n) {
		if (!w.acceptData(e)) return;
		var r, i, s = e.nodeType,
			o = s ? w.cache : e,
			u = s ? e[w.expando] : w.expando;
		if (!o[u]) return;
		if (t) {
			r = n ? o[u] : o[u].data;
			if (r) {
				w.isArray(t) ? t = t.concat(w.map(t, w.camelCase)) : t in r ? t = [t] : (t = w.camelCase(t), t in r ? t = [t] : t = t.split(" ")), i = t.length;
				while (i--) delete r[t[i]];
				if (n ? !z(r) : !w.isEmptyObject(r)) return
			}
		}
		if (!n) {
			delete o[u].data;
			if (!z(o[u])) return
		}
		s ? w.cleanData([e], !0) : w.support.deleteExpando || o != o.window ? delete o[u] : o[u] = null
	}

	function U(e, n, r) {
		if (r === t && e.nodeType === 1) {
			var i = "data-" + n.replace(I, "-$1").toLowerCase();
			r = e.getAttribute(i);
			if (typeof r == "string") {
				try {
					r = r === "true" ? !0 : r === "false" ? !1 : r === "null" ? null : +r + "" === r ? +r : F.test(r) ? w.parseJSON(r) : r
				} catch (s) {}
				w.data(e, n, r)
			} else r = t
		}
		return r
	}

	function z(e) {
		var t;
		for (t in e) {
			if (t === "data" && w.isEmptyObject(e[t])) continue;
			if (t !== "toJSON") return !1
		}
		return !0
	}

	function it() {
		return !0
	}

	function st() {
		return !1
	}

	function ot() {
		try {
			return o.activeElement
		} catch (e) {}
	}

	function ct(e, t) {
		do e = e[t]; while (e && e.nodeType !== 1);
		return e
	}

	function ht(e, t, n) {
		if (w.isFunction(t)) return w.grep(e, function (e, r) {
			return !!t.call(e, r, e) !== n
		});
		if (t.nodeType) return w.grep(e, function (e) {
			return e === t !== n
		});
		if (typeof t == "string") {
			if (ut.test(t)) return w.filter(t, e, n);
			t = w.filter(t, e)
		}
		return w.grep(e, function (e) {
			return w.inArray(e, t) >= 0 !== n
		})
	}

	function pt(e) {
		var t = dt.split("|"),
			n = e.createDocumentFragment();
		if (n.createElement)
			while (t.length) n.createElement(t.pop());
		return n
	}

	function Mt(e, t) {
		return w.nodeName(e, "table") && w.nodeName(t.nodeType === 1 ? t : t.firstChild, "tr") ? e.getElementsByTagName("tbody")[0] || e.appendChild(e.ownerDocument.createElement("tbody")) : e
	}

	function _t(e) {
		return e.type = (w.find.attr(e, "type") !== null) + "/" + e.type, e
	}

	function Dt(e) {
		var t = Ct.exec(e.type);
		return t ? e.type = t[1] : e.removeAttribute("type"), e
	}

	function Pt(e, t) {
		var n, r = 0;
		for (;
			(n = e[r]) != null; r++) w._data(n, "globalEval", !t || w._data(t[r], "globalEval"))
	}

	function Ht(e, t) {
		if (t.nodeType !== 1 || !w.hasData(e)) return;
		var n, r, i, s = w._data(e),
			o = w._data(t, s),
			u = s.events;
		if (u) {
			delete o.handle, o.events = {};
			for (n in u)
				for (r = 0, i = u[n].length; r < i; r++) w.event.add(t, n, u[n][r])
		}
		o.data && (o.data = w.extend({}, o.data))
	}

	function Bt(e, t) {
		var n, r, i;
		if (t.nodeType !== 1) return;
		n = t.nodeName.toLowerCase();
		if (!w.support.noCloneEvent && t[w.expando]) {
			i = w._data(t);
			for (r in i.events) w.removeEvent(t, r, i.handle);
			t.removeAttribute(w.expando)
		}
		if (n === "script" && t.text !== e.text) _t(t).text = e.text, Dt(t);
		else if (n === "object") t.parentNode && (t.outerHTML = e.outerHTML), w.support.html5Clone && e.innerHTML && !w.trim(t.innerHTML) && (t.innerHTML = e.innerHTML);
		else if (n === "input" && xt.test(e.type)) t.defaultChecked = t.checked = e.checked, t.value !== e.value && (t.value = e.value);
		else if (n === "option") t.defaultSelected = t.selected = e.defaultSelected;
		else if (n === "input" || n === "textarea") t.defaultValue = e.defaultValue
	}

	function jt(e, n) {
		var r, s, o = 0,
			u = typeof e.getElementsByTagName !== i ? e.getElementsByTagName(n || "*") : typeof e.querySelectorAll !== i ? e.querySelectorAll(n || "*") : t;
		if (!u)
			for (u = [], r = e.childNodes || e;
				(s = r[o]) != null; o++) !n || w.nodeName(s, n) ? u.push(s) : w.merge(u, jt(s, n));
		return n === t || n && w.nodeName(e, n) ? w.merge([e], u) : u
	}

	function Ft(e) {
		xt.test(e.type) && (e.defaultChecked = e.checked)
	}

	function tn(e, t) {
		if (t in e) return t;
		var n = t.charAt(0).toUpperCase() + t.slice(1),
			r = t,
			i = en.length;
		while (i--) {
			t = en[i] + n;
			if (t in e) return t
		}
		return r
	}

	function nn(e, t) {
		return e = t || e, w.css(e, "display") === "none" || !w.contains(e.ownerDocument, e)
	}

	function rn(e, t) {
		var n, r, i, s = [],
			o = 0,
			u = e.length;
		for (; o < u; o++) {
			r = e[o];
			if (!r.style) continue;
			s[o] = w._data(r, "olddisplay"), n = r.style.display, t ? (!s[o] && n === "none" && (r.style.display = ""), r.style.display === "" && nn(r) && (s[o] = w._data(r, "olddisplay", an(r.nodeName)))) : s[o] || (i = nn(r), (n && n !== "none" || !i) && w._data(r, "olddisplay", i ? n : w.css(r, "display")))
		}
		for (o = 0; o < u; o++) {
			r = e[o];
			if (!r.style) continue;
			if (!t || r.style.display === "none" || r.style.display === "") r.style.display = t ? s[o] || "" : "none"
		}
		return e
	}

	function sn(e, t, n) {
		var r = $t.exec(t);
		return r ? Math.max(0, r[1] - (n || 0)) + (r[2] || "px") : t
	}

	function on(e, t, n, r, i) {
		var s = n === (r ? "border" : "content") ? 4 : t === "width" ? 1 : 0,
			o = 0;
		for (; s < 4; s += 2) n === "margin" && (o += w.css(e, n + Zt[s], !0, i)), r ? (n === "content" && (o -= w.css(e, "padding" + Zt[s], !0, i)), n !== "margin" && (o -= w.css(e, "border" + Zt[s] + "Width", !0, i))) : (o += w.css(e, "padding" + Zt[s], !0, i), n !== "padding" && (o += w.css(e, "border" + Zt[s] + "Width", !0, i)));
		return o
	}

	function un(e, t, n) {
		var r = !0,
			i = t === "width" ? e.offsetWidth : e.offsetHeight,
			s = qt(e),
			o = w.support.boxSizing && w.css(e, "boxSizing", !1, s) === "border-box";
		if (i <= 0 || i == null) {
			i = Rt(e, t, s);
			if (i < 0 || i == null) i = e.style[t];
			if (Jt.test(i)) return i;
			r = o && (w.support.boxSizingReliable || i === e.style[t]), i = parseFloat(i) || 0
		}
		return i + on(e, t, n || (o ? "border" : "content"), r, s) + "px"
	}

	function an(e) {
		var t = o,
			n = Qt[e];
		if (!n) {
			n = fn(e, t);
			if (n === "none" || !n) It = (It || w("<iframe frameborder='0' width='0' height='0'/>").css("cssText", "display:block !important")).appendTo(t.documentElement), t = (It[0].contentWindow || It[0].contentDocument).document, t.write("<!doctype html><html><body>"), t.close(), n = fn(e, t), It.detach();
			Qt[e] = n
		}
		return n
	}

	function fn(e, t) {
		var n = w(t.createElement(e)).appendTo(t.body),
			r = w.css(n[0], "display");
		return n.remove(), r
	}

	function vn(e, t, n, r) {
		var i;
		if (w.isArray(t)) w.each(t, function (t, i) {
			n || cn.test(e) ? r(e, i) : vn(e + "[" + (typeof i == "object" ? t : "") + "]", i, n, r)
		});
		else if (!n && w.type(t) === "object")
			for (i in t) vn(e + "[" + i + "]", t[i], n, r);
		else r(e, t)
	}

	function _n(e) {
		return function (t, n) {
			typeof t != "string" && (n = t, t = "*");
			var r, i = 0,
				s = t.toLowerCase().match(S) || [];
			if (w.isFunction(n))
				while (r = s[i++]) r[0] === "+" ? (r = r.slice(1) || "*", (e[r] = e[r] || []).unshift(n)) : (e[r] = e[r] || []).push(n)
		}
	}

	function Dn(e, t, n, r) {
		function o(u) {
			var a;
			return i[u] = !0, w.each(e[u] || [], function (e, u) {
				var f = u(t, n, r);
				if (typeof f == "string" && !s && !i[f]) return t.dataTypes.unshift(f), o(f), !1;
				if (s) return !(a = f)
			}), a
		}
		var i = {},
			s = e === An;
		return o(t.dataTypes[0]) || !i["*"] && o("*")
	}

	function Pn(e, n) {
		var r, i, s = w.ajaxSettings.flatOptions || {};
		for (i in n) n[i] !== t && ((s[i] ? e : r || (r = {}))[i] = n[i]);
		return r && w.extend(!0, e, r), e
	}

	function Hn(e, n, r) {
		var i, s, o, u, a = e.contents,
			f = e.dataTypes;
		while (f[0] === "*") f.shift(), s === t && (s = e.mimeType || n.getResponseHeader("Content-Type"));
		if (s)
			for (u in a)
				if (a[u] && a[u].test(s)) {
					f.unshift(u);
					break
				}
		if (f[0] in r) o = f[0];
		else {
			for (u in r) {
				if (!f[0] || e.converters[u + " " + f[0]]) {
					o = u;
					break
				}
				i || (i = u)
			}
			o = o || i
		}
		if (o) return o !== f[0] && f.unshift(o), r[o]
	}

	function Bn(e, t, n, r) {
		var i, s, o, u, a, f = {},
			l = e.dataTypes.slice();
		if (l[1])
			for (o in e.converters) f[o.toLowerCase()] = e.converters[o];
		s = l.shift();
		while (s) {
			e.responseFields[s] && (n[e.responseFields[s]] = t), !a && r && e.dataFilter && (t = e.dataFilter(t, e.dataType)), a = s, s = l.shift();
			if (s)
				if (s === "*") s = a;
				else if (a !== "*" && a !== s) {
				o = f[a + " " + s] || f["* " + s];
				if (!o)
					for (i in f) {
						u = i.split(" ");
						if (u[1] === s) {
							o = f[a + " " + u[0]] || f["* " + u[0]];
							if (o) {
								o === !0 ? o = f[i] : f[i] !== !0 && (s = u[0], l.unshift(u[1]));
								break
							}
						}
					}
				if (o !== !0)
					if (o && e["throws"]) t = o(t);
					else try {
						t = o(t)
					} catch (c) {
						return {
							state: "parsererror",
							error: o ? c : "No conversion from " + a + " to " + s
						}
					}
			}
		}
		return {
			state: "success",
			data: t
		}
	}

	function zn() {
		try {
			return new e.XMLHttpRequest
		} catch (t) {}
	}

	function Wn() {
		try {
			return new e.ActiveXObject("Microsoft.XMLHTTP")
		} catch (t) {}
	}

	function Yn() {
		return setTimeout(function () {
			Xn = t
		}), Xn = w.now()
	}

	function Zn(e, t, n) {
		var r, i = (Gn[t] || []).concat(Gn["*"]),
			s = 0,
			o = i.length;
		for (; s < o; s++)
			if (r = i[s].call(n, t, e)) return r
	}

	function er(e, t, n) {
		var r, i, s = 0,
			o = Qn.length,
			u = w.Deferred().always(function () {
				delete a.elem
			}),
			a = function () {
				if (i) return !1;
				var t = Xn || Yn(),
					n = Math.max(0, f.startTime + f.duration - t),
					r = n / f.duration || 0,
					s = 1 - r,
					o = 0,
					a = f.tweens.length;
				for (; o < a; o++) f.tweens[o].run(s);
				return u.notifyWith(e, [f, s, n]), s < 1 && a ? n : (u.resolveWith(e, [f]), !1)
			},
			f = u.promise({
				elem: e,
				props: w.extend({}, t),
				opts: w.extend(!0, {
					specialEasing: {}
				}, n),
				originalProperties: t,
				originalOptions: n,
				startTime: Xn || Yn(),
				duration: n.duration,
				tweens: [],
				createTween: function (t, n) {
					var r = w.Tween(e, f.opts, t, n, f.opts.specialEasing[t] || f.opts.easing);
					return f.tweens.push(r), r
				},
				stop: function (t) {
					var n = 0,
						r = t ? f.tweens.length : 0;
					if (i) return this;
					i = !0;
					for (; n < r; n++) f.tweens[n].run(1);
					return t ? u.resolveWith(e, [f, t]) : u.rejectWith(e, [f, t]), this
				}
			}),
			l = f.props;
		tr(l, f.opts.specialEasing);
		for (; s < o; s++) {
			r = Qn[s].call(f, e, l, f.opts);
			if (r) return r
		}
		return w.map(l, Zn, f), w.isFunction(f.opts.start) && f.opts.start.call(e, f), w.fx.timer(w.extend(a, {
			elem: e,
			anim: f,
			queue: f.opts.queue
		})), f.progress(f.opts.progress).done(f.opts.done, f.opts.complete).fail(f.opts.fail).always(f.opts.always)
	}

	function tr(e, t) {
		var n, r, i, s, o;
		for (n in e) {
			r = w.camelCase(n), i = t[r], s = e[n], w.isArray(s) && (i = s[1], s = e[n] = s[0]), n !== r && (e[r] = s, delete e[n]), o = w.cssHooks[r];
			if (o && "expand" in o) {
				s = o.expand(s), delete e[r];
				for (n in s) n in e || (e[n] = s[n], t[n] = i)
			} else t[r] = i
		}
	}

	function nr(e, t, n) {
		var r, i, s, o, u, a, f = this,
			l = {},
			c = e.style,
			h = e.nodeType && nn(e),
			p = w._data(e, "fxshow");
		n.queue || (u = w._queueHooks(e, "fx"), u.unqueued == null && (u.unqueued = 0, a = u.empty.fire, u.empty.fire = function () {
			u.unqueued || a()
		}), u.unqueued++, f.always(function () {
			f.always(function () {
				u.unqueued--, w.queue(e, "fx").length || u.empty.fire()
			})
		})), e.nodeType === 1 && ("height" in t || "width" in t) && (n.overflow = [c.overflow, c.overflowX, c.overflowY], w.css(e, "display") === "inline" && w.css(e, "float") === "none" && (!w.support.inlineBlockNeedsLayout || an(e.nodeName) === "inline" ? c.display = "inline-block" : c.zoom = 1)), n.overflow && (c.overflow = "hidden", w.support.shrinkWrapBlocks || f.always(function () {
			c.overflow = n.overflow[0], c.overflowX = n.overflow[1], c.overflowY = n.overflow[2]
		}));
		for (r in t) {
			i = t[r];
			if ($n.exec(i)) {
				delete t[r], s = s || i === "toggle";
				if (i === (h ? "hide" : "show")) continue;
				l[r] = p && p[r] || w.style(e, r)
			}
		}
		if (!w.isEmptyObject(l)) {
			p ? "hidden" in p && (h = p.hidden) : p = w._data(e, "fxshow", {}), s && (p.hidden = !h), h ? w(e).show() : f.done(function () {
				w(e).hide()
			}), f.done(function () {
				var t;
				w._removeData(e, "fxshow");
				for (t in l) w.style(e, t, l[t])
			});
			for (r in l) o = Zn(h ? p[r] : 0, r, f), r in p || (p[r] = o.start, h && (o.end = o.start, o.start = r === "width" || r === "height" ? 1 : 0))
		}
	}

	function rr(e, t, n, r, i) {
		return new rr.prototype.init(e, t, n, r, i)
	}

	function ir(e, t) {
		var n, r = {
				height: e
			},
			i = 0;
		t = t ? 1 : 0;
		for (; i < 4; i += 2 - t) n = Zt[i], r["margin" + n] = r["padding" + n] = e;
		return t && (r.opacity = r.width = e), r
	}

	function sr(e) {
		return w.isWindow(e) ? e : e.nodeType === 9 ? e.defaultView || e.parentWindow : !1
	}
	var n, r, i = typeof t,
		s = e.location,
		o = e.document,
		u = o.documentElement,
		a = e.jQuery,
		f = e.$,
		l = {},
		c = [],
		h = "1.10.2",
		p = c.concat,
		d = c.push,
		v = c.slice,
		m = c.indexOf,
		g = l.toString,
		y = l.hasOwnProperty,
		b = h.trim,
		w = function (e, t) {
			return new w.fn.init(e, t, r)
		},
		E = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
		S = /\S+/g,
		x = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,
		T = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,
		N = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
		C = /^[\],:{}\s]*$/,
		k = /(?:^|:|,)(?:\s*\[)+/g,
		L = /\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,
		A = /"[^"\\\r\n]*"|true|false|null|-?(?:\d+\.|)\d+(?:[eE][+-]?\d+|)/g,
		O = /^-ms-/,
		M = /-([\da-z])/gi,
		_ = function (e, t) {
			return t.toUpperCase()
		},
		D = function (e) {
			if (o.addEventListener || e.type === "load" || o.readyState === "complete") P(), w.ready()
		},
		P = function () {
			o.addEventListener ? (o.removeEventListener("DOMContentLoaded", D, !1), e.removeEventListener("load", D, !1)) : (o.detachEvent("onreadystatechange", D), e.detachEvent("onload", D))
		};
	w.fn = w.prototype = {
			jquery: h,
			constructor: w,
			init: function (e, n, r) {
				var i, s;
				if (!e) return this;
				if (typeof e == "string") {
					e.charAt(0) === "<" && e.charAt(e.length - 1) === ">" && e.length >= 3 ? i = [null, e, null] : i = T.exec(e);
					if (i && (i[1] || !n)) {
						if (i[1]) {
							n = n instanceof w ? n[0] : n, w.merge(this, w.parseHTML(i[1], n && n.nodeType ? n.ownerDocument || n : o, !0));
							if (N.test(i[1]) && w.isPlainObject(n))
								for (i in n) w.isFunction(this[i]) ? this[i](n[i]) : this.attr(i, n[i]);
							return this
						}
						s = o.getElementById(i[2]);
						if (s && s.parentNode) {
							if (s.id !== i[2]) return r.find(e);
							this.length = 1, this[0] = s
						}
						return this.context = o, this.selector = e, this
					}
					return !n || n.jquery ? (n || r).find(e) : this.constructor(n).find(e)
				}
				return e.nodeType ? (this.context = this[0] = e, this.length = 1, this) : w.isFunction(e) ? r.ready(e) : (e.selector !== t && (this.selector = e.selector, this.context = e.context), w.makeArray(e, this))
			},
			selector: "",
			length: 0,
			toArray: function () {
				return v.call(this)
			},
			get: function (e) {
				return e == null ? this.toArray() : e < 0 ? this[this.length + e] : this[e]
			},
			pushStack: function (e) {
				var t = w.merge(this.constructor(), e);
				return t.prevObject = this, t.context = this.context, t
			},
			each: function (e, t) {
				return w.each(this, e, t)
			},
			ready: function (e) {
				return w.ready.promise().done(e), this
			},
			slice: function () {
				return this.pushStack(v.apply(this, arguments))
			},
			first: function () {
				return this.eq(0)
			},
			last: function () {
				return this.eq(-1)
			},
			eq: function (e) {
				var t = this.length,
					n = +e + (e < 0 ? t : 0);
				return this.pushStack(n >= 0 && n < t ? [this[n]] : [])
			},
			map: function (e) {
				return this.pushStack(w.map(this, function (t, n) {
					return e.call(t, n, t)
				}))
			},
			end: function () {
				return this.prevObject || this.constructor(null)
			},
			push: d,
			sort: [].sort,
			splice: [].splice
		}, w.fn.init.prototype = w.fn, w.extend = w.fn.extend = function () {
			var e, n, r, i, s, o, u = arguments[0] || {},
				a = 1,
				f = arguments.length,
				l = !1;
			typeof u == "boolean" && (l = u, u = arguments[1] || {}, a = 2), typeof u != "object" && !w.isFunction(u) && (u = {}), f === a && (u = this, --a);
			for (; a < f; a++)
				if ((s = arguments[a]) != null)
					for (i in s) {
						e = u[i], r = s[i];
						if (u === r) continue;
						l && r && (w.isPlainObject(r) || (n = w.isArray(r))) ? (n ? (n = !1, o = e && w.isArray(e) ? e : []) : o = e && w.isPlainObject(e) ? e : {}, u[i] = w.extend(l, o, r)) : r !== t && (u[i] = r)
					}
				return u
		}, w.extend({
			expando: "jQuery" + (h + Math.random()).replace(/\D/g, ""),
			noConflict: function (t) {
				return e.$ === w && (e.$ = f), t && e.jQuery === w && (e.jQuery = a), w
			},
			isReady: !1,
			readyWait: 1,
			holdReady: function (e) {
				e ? w.readyWait++ : w.ready(!0)
			},
			ready: function (e) {
				if (e === !0 ? --w.readyWait : w.isReady) return;
				if (!o.body) return setTimeout(w.ready);
				w.isReady = !0;
				if (e !== !0 && --w.readyWait > 0) return;
				n.resolveWith(o, [w]), w.fn.trigger && w(o).trigger("ready").off("ready")
			},
			isFunction: function (e) {
				return w.type(e) === "function"
			},
			isArray: Array.isArray || function (e) {
				return w.type(e) === "array"
			},
			isWindow: function (e) {
				return e != null && e == e.window
			},
			isNumeric: function (e) {
				return !isNaN(parseFloat(e)) && isFinite(e)
			},
			type: function (e) {
				return e == null ? String(e) : typeof e == "object" || typeof e == "function" ? l[g.call(e)] || "object" : typeof e
			},
			isPlainObject: function (e) {
				var n;
				if (!e || w.type(e) !== "object" || e.nodeType || w.isWindow(e)) return !1;
				try {
					if (e.constructor && !y.call(e, "constructor") && !y.call(e.constructor.prototype, "isPrototypeOf")) return !1
				} catch (r) {
					return !1
				}
				if (w.support.ownLast)
					for (n in e) return y.call(e, n);
				for (n in e);
				return n === t || y.call(e, n)
			},
			isEmptyObject: function (e) {
				var t;
				for (t in e) return !1;
				return !0
			},
			error: function (e) {
				throw new Error(e)
			},
			parseHTML: function (e, t, n) {
				if (!e || typeof e != "string") return null;
				typeof t == "boolean" && (n = t, t = !1), t = t || o;
				var r = N.exec(e),
					i = !n && [];
				return r ? [t.createElement(r[1])] : (r = w.buildFragment([e], t, i), i && w(i).remove(), w.merge([], r.childNodes))
			},
			parseJSON: function (t) {
				if (e.JSON && e.JSON.parse) return e.JSON.parse(t);
				if (t === null) return t;
				if (typeof t == "string") {
					t = w.trim(t);
					if (t && C.test(t.replace(L, "@").replace(A, "]").replace(k, ""))) return (new Function("return " + t))()
				}
				w.error("Invalid JSON: " + t)
			},
			parseXML: function (n) {
				var r, i;
				if (!n || typeof n != "string") return null;
				try {
					e.DOMParser ? (i = new DOMParser, r = i.parseFromString(n, "text/xml")) : (r = new ActiveXObject("Microsoft.XMLDOM"), r.async = "false", r.loadXML(n))
				} catch (s) {
					r = t
				}
				return (!r || !r.documentElement || r.getElementsByTagName("parsererror").length) && w.error("Invalid XML: " + n), r
			},
			noop: function () {},
			globalEval: function (t) {
				t && w.trim(t) && (e.execScript || function (t) {
					e.eval.call(e, t)
				})(t)
			},
			camelCase: function (e) {
				return e.replace(O, "ms-").replace(M, _)
			},
			nodeName: function (e, t) {
				return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase()
			},
			each: function (e, t, n) {
				var r, i = 0,
					s = e.length,
					o = H(e);
				if (n)
					if (o)
						for (; i < s; i++) {
							r = t.apply(e[i], n);
							if (r === !1) break
						} else
							for (i in e) {
								r = t.apply(e[i], n);
								if (r === !1) break
							} else if (o)
								for (; i < s; i++) {
									r = t.call(e[i], i, e[i]);
									if (r === !1) break
								} else
									for (i in e) {
										r = t.call(e[i], i, e[i]);
										if (r === !1) break
									}
							return e
			},
			trim: b && !b.call("﻿ ") ? function (e) {
				return e == null ? "" : b.call(e)
			} : function (e) {
				return e == null ? "" : (e + "").replace(x, "")
			},
			makeArray: function (e, t) {
				var n = t || [];
				return e != null && (H(Object(e)) ? w.merge(n, typeof e == "string" ? [e] : e) : d.call(n, e)), n
			},
			inArray: function (e, t, n) {
				var r;
				if (t) {
					if (m) return m.call(t, e, n);
					r = t.length, n = n ? n < 0 ? Math.max(0, r + n) : n : 0;
					for (; n < r; n++)
						if (n in t && t[n] === e) return n
				}
				return -1
			},
			merge: function (e, n) {
				var r = n.length,
					i = e.length,
					s = 0;
				if (typeof r == "number")
					for (; s < r; s++) e[i++] = n[s];
				else
					while (n[s] !== t) e[i++] = n[s++];
				return e.length = i, e
			},
			grep: function (e, t, n) {
				var r, i = [],
					s = 0,
					o = e.length;
				n = !!n;
				for (; s < o; s++) r = !!t(e[s], s), n !== r && i.push(e[s]);
				return i
			},
			map: function (e, t, n) {
				var r, i = 0,
					s = e.length,
					o = H(e),
					u = [];
				if (o)
					for (; i < s; i++) r = t(e[i], i, n), r != null && (u[u.length] = r);
				else
					for (i in e) r = t(e[i], i, n), r != null && (u[u.length] = r);
				return p.apply([], u)
			},
			guid: 1,
			proxy: function (e, n) {
				var r, i, s;
				return typeof n == "string" && (s = e[n], n = e, e = s), w.isFunction(e) ? (r = v.call(arguments, 2), i = function () {
					return e.apply(n || this, r.concat(v.call(arguments)))
				}, i.guid = e.guid = e.guid || w.guid++, i) : t
			},
			access: function (e, n, r, i, s, o, u) {
				var a = 0,
					f = e.length,
					l = r == null;
				if (w.type(r) === "object") {
					s = !0;
					for (a in r) w.access(e, n, a, r[a], !0, o, u)
				} else if (i !== t) {
					s = !0, w.isFunction(i) || (u = !0), l && (u ? (n.call(e, i), n = null) : (l = n, n = function (e, t, n) {
						return l.call(w(e), n)
					}));
					if (n)
						for (; a < f; a++) n(e[a], r, u ? i : i.call(e[a], a, n(e[a], r)))
				}
				return s ? e : l ? n.call(e) : f ? n(e[0], r) : o
			},
			now: function () {
				return (new Date).getTime()
			},
			swap: function (e, t, n, r) {
				var i, s, o = {};
				for (s in t) o[s] = e.style[s], e.style[s] = t[s];
				i = n.apply(e, r || []);
				for (s in t) e.style[s] = o[s];
				return i
			}
		}), w.ready.promise = function (t) {
			if (!n) {
				n = w.Deferred();
				if (o.readyState === "complete") setTimeout(w.ready);
				else if (o.addEventListener) o.addEventListener("DOMContentLoaded", D, !1), e.addEventListener("load", D, !1);
				else {
					o.attachEvent("onreadystatechange", D), e.attachEvent("onload", D);
					var r = !1;
					try {
						r = e.frameElement == null && o.documentElement
					} catch (i) {}
					r && r.doScroll && function s() {
						if (!w.isReady) {
							try {
								r.doScroll("left")
							} catch (e) {
								return setTimeout(s, 50)
							}
							P(), w.ready()
						}
					}()
				}
			}
			return n.promise(t)
		}, w.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function (e, t) {
			l["[object " + t + "]"] = t.toLowerCase()
		}), r = w(o),
		function (e, t) {
			function ot(e, t, n, i) {
				var s, o, u, a, f, l, p, m, g, w;
				(t ? t.ownerDocument || t : E) !== h && c(t), t = t || h, n = n || [];
				if (!e || typeof e != "string") return n;
				if ((a = t.nodeType) !== 1 && a !== 9) return [];
				if (d && !i) {
					if (s = Z.exec(e))
						if (u = s[1]) {
							if (a === 9) {
								o = t.getElementById(u);
								if (!o || !o.parentNode) return n;
								if (o.id === u) return n.push(o), n
							} else if (t.ownerDocument && (o = t.ownerDocument.getElementById(u)) && y(t, o) && o.id === u) return n.push(o), n
						} else {
							if (s[2]) return H.apply(n, t.getElementsByTagName(e)), n;
							if ((u = s[3]) && r.getElementsByClassName && t.getElementsByClassName) return H.apply(n, t.getElementsByClassName(u)), n
						}
					if (r.qsa && (!v || !v.test(e))) {
						m = p = b, g = t, w = a === 9 && e;
						if (a === 1 && t.nodeName.toLowerCase() !== "object") {
							l = mt(e), (p = t.getAttribute("id")) ? m = p.replace(nt, "\\$&") : t.setAttribute("id", m), m = "[id='" + m + "'] ", f = l.length;
							while (f--) l[f] = m + gt(l[f]);
							g = $.test(e) && t.parentNode || t, w = l.join(",")
						}
						if (w) try {
							return H.apply(n, g.querySelectorAll(w)), n
						} catch (S) {} finally {
							p || t.removeAttribute("id")
						}
					}
				}
				return Nt(e.replace(W, "$1"), t, n, i)
			}

			function ut() {
				function t(n, r) {
					return e.push(n += " ") > s.cacheLength && delete t[e.shift()], t[n] = r
				}
				var e = [];
				return t
			}

			function at(e) {
				return e[b] = !0, e
			}

			function ft(e) {
				var t = h.createElement("div");
				try {
					return !!e(t)
				} catch (n) {
					return !1
				} finally {
					t.parentNode && t.parentNode.removeChild(t), t = null
				}
			}

			function lt(e, t) {
				var n = e.split("|"),
					r = e.length;
				while (r--) s.attrHandle[n[r]] = t
			}

			function ct(e, t) {
				var n = t && e,
					r = n && e.nodeType === 1 && t.nodeType === 1 && (~t.sourceIndex || O) - (~e.sourceIndex || O);
				if (r) return r;
				if (n)
					while (n = n.nextSibling)
						if (n === t) return -1;
				return e ? 1 : -1
			}

			function ht(e) {
				return function (t) {
					var n = t.nodeName.toLowerCase();
					return n === "input" && t.type === e
				}
			}

			function pt(e) {
				return function (t) {
					var n = t.nodeName.toLowerCase();
					return (n === "input" || n === "button") && t.type === e
				}
			}

			function dt(e) {
				return at(function (t) {
					return t = +t, at(function (n, r) {
						var i, s = e([], n.length, t),
							o = s.length;
						while (o--) n[i = s[o]] && (n[i] = !(r[i] = n[i]))
					})
				})
			}

			function vt() {}

			function mt(e, t) {
				var n, r, i, o, u, a, f, l = N[e + " "];
				if (l) return t ? 0 : l.slice(0);
				u = e, a = [], f = s.preFilter;
				while (u) {
					if (!n || (r = X.exec(u))) r && (u = u.slice(r[0].length) || u), a.push(i = []);
					n = !1;
					if (r = V.exec(u)) n = r.shift(), i.push({
						value: n,
						type: r[0].replace(W, " ")
					}), u = u.slice(n.length);
					for (o in s.filter)(r = G[o].exec(u)) && (!f[o] || (r = f[o](r))) && (n = r.shift(), i.push({
						value: n,
						type: o,
						matches: r
					}), u = u.slice(n.length));
					if (!n) break
				}
				return t ? u.length : u ? ot.error(e) : N(e, a).slice(0)
			}

			function gt(e) {
				var t = 0,
					n = e.length,
					r = "";
				for (; t < n; t++) r += e[t].value;
				return r
			}

			function yt(e, t, n) {
				var r = t.dir,
					s = n && r === "parentNode",
					o = x++;
				return t.first ? function (t, n, i) {
					while (t = t[r])
						if (t.nodeType === 1 || s) return e(t, n, i)
				} : function (t, n, u) {
					var a, f, l, c = S + " " + o;
					if (u) {
						while (t = t[r])
							if (t.nodeType === 1 || s)
								if (e(t, n, u)) return !0
					} else
						while (t = t[r])
							if (t.nodeType === 1 || s) {
								l = t[b] || (t[b] = {});
								if ((f = l[r]) && f[0] === c) {
									if ((a = f[1]) === !0 || a === i) return a === !0
								} else {
									f = l[r] = [c], f[1] = e(t, n, u) || i;
									if (f[1] === !0) return !0
								}
							}
				}
			}

			function bt(e) {
				return e.length > 1 ? function (t, n, r) {
					var i = e.length;
					while (i--)
						if (!e[i](t, n, r)) return !1;
					return !0
				} : e[0]
			}

			function wt(e, t, n, r, i) {
				var s, o = [],
					u = 0,
					a = e.length,
					f = t != null;
				for (; u < a; u++)
					if (s = e[u])
						if (!n || n(s, r, i)) o.push(s), f && t.push(u);
				return o
			}

			function Et(e, t, n, r, i, s) {
				return r && !r[b] && (r = Et(r)), i && !i[b] && (i = Et(i, s)), at(function (s, o, u, a) {
					var f, l, c, h = [],
						p = [],
						d = o.length,
						v = s || Tt(t || "*", u.nodeType ? [u] : u, []),
						m = e && (s || !t) ? wt(v, h, e, u, a) : v,
						g = n ? i || (s ? e : d || r) ? [] : o : m;
					n && n(m, g, u, a);
					if (r) {
						f = wt(g, p), r(f, [], u, a), l = f.length;
						while (l--)
							if (c = f[l]) g[p[l]] = !(m[p[l]] = c)
					}
					if (s) {
						if (i || e) {
							if (i) {
								f = [], l = g.length;
								while (l--)(c = g[l]) && f.push(m[l] = c);
								i(null, g = [], f, a)
							}
							l = g.length;
							while (l--)(c = g[l]) && (f = i ? j.call(s, c) : h[l]) > -1 && (s[f] = !(o[f] = c))
						}
					} else g = wt(g === o ? g.splice(d, g.length) : g), i ? i(null, o, g, a) : H.apply(o, g)
				})
			}

			function St(e) {
				var t, n, r, i = e.length,
					o = s.relative[e[0].type],
					u = o || s.relative[" "],
					a = o ? 1 : 0,
					l = yt(function (e) {
						return e === t
					}, u, !0),
					c = yt(function (e) {
						return j.call(t, e) > -1
					}, u, !0),
					h = [function (e, n, r) {
						return !o && (r || n !== f) || ((t = n).nodeType ? l(e, n, r) : c(e, n, r))
					}];
				for (; a < i; a++)
					if (n = s.relative[e[a].type]) h = [yt(bt(h), n)];
					else {
						n = s.filter[e[a].type].apply(null, e[a].matches);
						if (n[b]) {
							r = ++a;
							for (; r < i; r++)
								if (s.relative[e[r].type]) break;
							return Et(a > 1 && bt(h), a > 1 && gt(e.slice(0, a - 1).concat({
								value: e[a - 2].type === " " ? "*" : ""
							})).replace(W, "$1"), n, a < r && St(e.slice(a, r)), r < i && St(e = e.slice(r)), r < i && gt(e))
						}
						h.push(n)
					}
				return bt(h)
			}

			function xt(e, t) {
				var n = 0,
					r = t.length > 0,
					o = e.length > 0,
					u = function (u, a, l, c, p) {
						var d, v, m, g = [],
							y = 0,
							b = "0",
							w = u && [],
							E = p != null,
							x = f,
							T = u || o && s.find.TAG("*", p && a.parentNode || a),
							N = S += x == null ? 1 : Math.random() || .1;
						E && (f = a !== h && a, i = n);
						for (;
							(d = T[b]) != null; b++) {
							if (o && d) {
								v = 0;
								while (m = e[v++])
									if (m(d, a, l)) {
										c.push(d);
										break
									}
								E && (S = N, i = ++n)
							}
							r && ((d = !m && d) && y--, u && w.push(d))
						}
						y += b;
						if (r && b !== y) {
							v = 0;
							while (m = t[v++]) m(w, g, a, l);
							if (u) {
								if (y > 0)
									while (b--) !w[b] && !g[b] && (g[b] = D.call(c));
								g = wt(g)
							}
							H.apply(c, g), E && !u && g.length > 0 && y + t.length > 1 && ot.uniqueSort(c)
						}
						return E && (S = N, f = x), w
					};
				return r ? at(u) : u
			}

			function Tt(e, t, n) {
				var r = 0,
					i = t.length;
				for (; r < i; r++) ot(e, t[r], n);
				return n
			}

			function Nt(e, t, n, i) {
				var o, u, f, l, c, h = mt(e);
				if (!i && h.length === 1) {
					u = h[0] = h[0].slice(0);
					if (u.length > 2 && (f = u[0]).type === "ID" && r.getById && t.nodeType === 9 && d && s.relative[u[1].type]) {
						t = (s.find.ID(f.matches[0].replace(rt, it), t) || [])[0];
						if (!t) return n;
						e = e.slice(u.shift().value.length)
					}
					o = G.needsContext.test(e) ? 0 : u.length;
					while (o--) {
						f = u[o];
						if (s.relative[l = f.type]) break;
						if (c = s.find[l])
							if (i = c(f.matches[0].replace(rt, it), $.test(u[0].type) && t.parentNode || t)) {
								u.splice(o, 1), e = i.length && gt(u);
								if (!e) return H.apply(n, i), n;
								break
							}
					}
				}
				return a(e, h)(i, t, !d, n, $.test(e)), n
			}
			var n, r, i, s, o, u, a, f, l, c, h, p, d, v, m, g, y, b = "sizzle" + -(new Date),
				E = e.document,
				S = 0,
				x = 0,
				T = ut(),
				N = ut(),
				C = ut(),
				k = !1,
				L = function (e, t) {
					return e === t ? (k = !0, 0) : 0
				},
				A = typeof t,
				O = 1 << 31,
				M = {}.hasOwnProperty,
				_ = [],
				D = _.pop,
				P = _.push,
				H = _.push,
				B = _.slice,
				j = _.indexOf || function (e) {
					var t = 0,
						n = this.length;
					for (; t < n; t++)
						if (this[t] === e) return t;
					return -1
				},
				F = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
				I = "[\\x20\\t\\r\\n\\f]",
				q = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",
				R = q.replace("w", "w#"),
				U = "\\[" + I + "*(" + q + ")" + I + "*(?:([*^$|!~]?=)" + I + "*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|(" + R + ")|)|)" + I + "*\\]",
				z = ":(" + q + ")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|" + U.replace(3, 8) + ")*)|.*)\\)|)",
				W = new RegExp("^" + I + "+|((?:^|[^\\\\])(?:\\\\.)*)" + I + "+$", "g"),
				X = new RegExp("^" + I + "*," + I + "*"),
				V = new RegExp("^" + I + "*([>+~]|" + I + ")" + I + "*"),
				$ = new RegExp(I + "*[+~]"),
				J = new RegExp("=" + I + "*([^\\]'\"]*)" + I + "*\\]", "g"),
				K = new RegExp(z),
				Q = new RegExp("^" + R + "$"),
				G = {
					ID: new RegExp("^#(" + q + ")"),
					CLASS: new RegExp("^\\.(" + q + ")"),
					TAG: new RegExp("^(" + q.replace("w", "w*") + ")"),
					ATTR: new RegExp("^" + U),
					PSEUDO: new RegExp("^" + z),
					CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + I + "*(even|odd|(([+-]|)(\\d*)n|)" + I + "*(?:([+-]|)" + I + "*(\\d+)|))" + I + "*\\)|)", "i"),
					bool: new RegExp("^(?:" + F + ")$", "i"),
					needsContext: new RegExp("^" + I + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + I + "*((?:-\\d)?\\d*)" + I + "*\\)|)(?=[^-]|$)", "i")
				},
				Y = /^[^{]+\{\s*\[native \w/,
				Z = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,
				et = /^(?:input|select|textarea|button)$/i,
				tt = /^h\d$/i,
				nt = /'|\\/g,
				rt = new RegExp("\\\\([\\da-f]{1,6}" + I + "?|(" + I + ")|.)", "ig"),
				it = function (e, t, n) {
					var r = "0x" + t - 65536;
					return r !== r || n ? t : r < 0 ? String.fromCharCode(r + 65536) : String.fromCharCode(r >> 10 | 55296, r & 1023 | 56320)
				};
			try {
				H.apply(_ = B.call(E.childNodes), E.childNodes), _[E.childNodes.length].nodeType
			} catch (st) {
				H = {
					apply: _.length ? function (e, t) {
						P.apply(e, B.call(t))
					} : function (e, t) {
						var n = e.length,
							r = 0;
						while (e[n++] = t[r++]);
						e.length = n - 1
					}
				}
			}
			u = ot.isXML = function (e) {
				var t = e && (e.ownerDocument || e).documentElement;
				return t ? t.nodeName !== "HTML" : !1
			}, r = ot.support = {}, c = ot.setDocument = function (e) {
				var t = e ? e.ownerDocument || e : E,
					n = t.defaultView;
				if (t === h || t.nodeType !== 9 || !t.documentElement) return h;
				h = t, p = t.documentElement, d = !u(t), n && n.attachEvent && n !== n.top && n.attachEvent("onbeforeunload", function () {
					c()
				}), r.attributes = ft(function (e) {
					return e.className = "i", !e.getAttribute("className")
				}), r.getElementsByTagName = ft(function (e) {
					return e.appendChild(t.createComment("")), !e.getElementsByTagName("*").length
				}), r.getElementsByClassName = ft(function (e) {
					return e.innerHTML = "<div class='a'></div><div class='a i'></div>", e.firstChild.className = "i", e.getElementsByClassName("i").length === 2
				}), r.getById = ft(function (e) {
					return p.appendChild(e).id = b, !t.getElementsByName || !t.getElementsByName(b).length
				}), r.getById ? (s.find.ID = function (e, t) {
					if (typeof t.getElementById !== A && d) {
						var n = t.getElementById(e);
						return n && n.parentNode ? [n] : []
					}
				}, s.filter.ID = function (e) {
					var t = e.replace(rt, it);
					return function (e) {
						return e.getAttribute("id") === t
					}
				}) : (delete s.find.ID, s.filter.ID = function (e) {
					var t = e.replace(rt, it);
					return function (e) {
						var n = typeof e.getAttributeNode !== A && e.getAttributeNode("id");
						return n && n.value === t
					}
				}), s.find.TAG = r.getElementsByTagName ? function (e, t) {
					if (typeof t.getElementsByTagName !== A) return t.getElementsByTagName(e)
				} : function (e, t) {
					var n, r = [],
						i = 0,
						s = t.getElementsByTagName(e);
					if (e === "*") {
						while (n = s[i++]) n.nodeType === 1 && r.push(n);
						return r
					}
					return s
				}, s.find.CLASS = r.getElementsByClassName && function (e, t) {
					if (typeof t.getElementsByClassName !== A && d) return t.getElementsByClassName(e)
				}, m = [], v = [];
				if (r.qsa = Y.test(t.querySelectorAll)) ft(function (e) {
					e.innerHTML = "<select><option selected=''></option></select>", e.querySelectorAll("[selected]").length || v.push("\\[" + I + "*(?:value|" + F + ")"), e.querySelectorAll(":checked").length || v.push(":checked")
				}), ft(function (e) {
					var n = t.createElement("input");
					n.setAttribute("type", "hidden"), e.appendChild(n).setAttribute("t", ""), e.querySelectorAll("[t^='']").length && v.push("[*^$]=" + I + "*(?:''|\"\")"), e.querySelectorAll(":enabled").length || v.push(":enabled", ":disabled"), e.querySelectorAll("*,:x"), v.push(",.*:")
				});
				return (r.matchesSelector = Y.test(g = p.webkitMatchesSelector || p.mozMatchesSelector || p.oMatchesSelector || p.msMatchesSelector)) && ft(function (e) {
					r.disconnectedMatch = g.call(e, "div"), g.call(e, "[s!='']:x"), m.push("!=", z)
				}), v = v.length && new RegExp(v.join("|")), m = m.length && new RegExp(m.join("|")), y = Y.test(p.contains) || p.compareDocumentPosition ? function (e, t) {
					var n = e.nodeType === 9 ? e.documentElement : e,
						r = t && t.parentNode;
					return e === r || !!r && r.nodeType === 1 && !!(n.contains ? n.contains(r) : e.compareDocumentPosition && e.compareDocumentPosition(r) & 16)
				} : function (e, t) {
					if (t)
						while (t = t.parentNode)
							if (t === e) return !0;
					return !1
				}, L = p.compareDocumentPosition ? function (e, n) {
					if (e === n) return k = !0, 0;
					var i = n.compareDocumentPosition && e.compareDocumentPosition && e.compareDocumentPosition(n);
					if (i) return i & 1 || !r.sortDetached && n.compareDocumentPosition(e) === i ? e === t || y(E, e) ? -1 : n === t || y(E, n) ? 1 : l ? j.call(l, e) - j.call(l, n) : 0 : i & 4 ? -1 : 1;
					return e.compareDocumentPosition ? -1 : 1
				} : function (e, n) {
					var r, i = 0,
						s = e.parentNode,
						o = n.parentNode,
						u = [e],
						a = [n];
					if (e === n) return k = !0, 0;
					if (!s || !o) return e === t ? -1 : n === t ? 1 : s ? -1 : o ? 1 : l ? j.call(l, e) - j.call(l, n) : 0;
					if (s === o) return ct(e, n);
					r = e;
					while (r = r.parentNode) u.unshift(r);
					r = n;
					while (r = r.parentNode) a.unshift(r);
					while (u[i] === a[i]) i++;
					return i ? ct(u[i], a[i]) : u[i] === E ? -1 : a[i] === E ? 1 : 0
				}, t
			}, ot.matches = function (e, t) {
				return ot(e, null, null, t)
			}, ot.matchesSelector = function (e, t) {
				(e.ownerDocument || e) !== h && c(e), t = t.replace(J, "='$1']");
				if (r.matchesSelector && d && (!m || !m.test(t)) && (!v || !v.test(t))) try {
					var n = g.call(e, t);
					if (n || r.disconnectedMatch || e.document && e.document.nodeType !== 11) return n
				} catch (i) {}
				return ot(t, h, null, [e]).length > 0
			}, ot.contains = function (e, t) {
				return (e.ownerDocument || e) !== h && c(e), y(e, t)
			}, ot.attr = function (e, n) {
				(e.ownerDocument || e) !== h && c(e);
				var i = s.attrHandle[n.toLowerCase()],
					o = i && M.call(s.attrHandle, n.toLowerCase()) ? i(e, n, !d) : t;
				return o === t ? r.attributes || !d ? e.getAttribute(n) : (o = e.getAttributeNode(n)) && o.specified ? o.value : null : o
			}, ot.error = function (e) {
				throw new Error("Syntax error, unrecognized expression: " + e)
			}, ot.uniqueSort = function (e) {
				var t, n = [],
					i = 0,
					s = 0;
				k = !r.detectDuplicates, l = !r.sortStable && e.slice(0), e.sort(L);
				if (k) {
					while (t = e[s++]) t === e[s] && (i = n.push(s));
					while (i--) e.splice(n[i], 1)
				}
				return e
			}, o = ot.getText = function (e) {
				var t, n = "",
					r = 0,
					i = e.nodeType;
				if (!i)
					for (; t = e[r]; r++) n += o(t);
				else if (i === 1 || i === 9 || i === 11) {
					if (typeof e.textContent == "string") return e.textContent;
					for (e = e.firstChild; e; e = e.nextSibling) n += o(e)
				} else if (i === 3 || i === 4) return e.nodeValue;
				return n
			}, s = ot.selectors = {
				cacheLength: 50,
				createPseudo: at,
				match: G,
				attrHandle: {},
				find: {},
				relative: {
					">": {
						dir: "parentNode",
						first: !0
					},
					" ": {
						dir: "parentNode"
					},
					"+": {
						dir: "previousSibling",
						first: !0
					},
					"~": {
						dir: "previousSibling"
					}
				},
				preFilter: {
					ATTR: function (e) {
						return e[1] = e[1].replace(rt, it), e[3] = (e[4] || e[5] || "").replace(rt, it), e[2] === "~=" && (e[3] = " " + e[3] + " "), e.slice(0, 4)
					},
					CHILD: function (e) {
						return e[1] = e[1].toLowerCase(), e[1].slice(0, 3) === "nth" ? (e[3] || ot.error(e[0]), e[4] = +(e[4] ? e[5] + (e[6] || 1) : 2 * (e[3] === "even" || e[3] === "odd")), e[5] = +(e[7] + e[8] || e[3] === "odd")) : e[3] && ot.error(e[0]), e
					},
					PSEUDO: function (e) {
						var n, r = !e[5] && e[2];
						return G.CHILD.test(e[0]) ? null : (e[3] && e[4] !== t ? e[2] = e[4] : r && K.test(r) && (n = mt(r, !0)) && (n = r.indexOf(")", r.length - n) - r.length) && (e[0] = e[0].slice(0, n), e[2] = r.slice(0, n)), e.slice(0, 3))
					}
				},
				filter: {
					TAG: function (e) {
						var t = e.replace(rt, it).toLowerCase();
						return e === "*" ? function () {
							return !0
						} : function (e) {
							return e.nodeName && e.nodeName.toLowerCase() === t
						}
					},
					CLASS: function (e) {
						var t = T[e + " "];
						return t || (t = new RegExp("(^|" + I + ")" + e + "(" + I + "|$)")) && T(e, function (e) {
							return t.test(typeof e.className == "string" && e.className || typeof e.getAttribute !== A && e.getAttribute("class") || "")
						})
					},
					ATTR: function (e, t, n) {
						return function (r) {
							var i = ot.attr(r, e);
							return i == null ? t === "!=" : t ? (i += "", t === "=" ? i === n : t === "!=" ? i !== n : t === "^=" ? n && i.indexOf(n) === 0 : t === "*=" ? n && i.indexOf(n) > -1 : t === "$=" ? n && i.slice(-n.length) === n : t === "~=" ? (" " + i + " ").indexOf(n) > -1 : t === "|=" ? i === n || i.slice(0, n.length + 1) === n + "-" : !1) : !0
						}
					},
					CHILD: function (e, t, n, r, i) {
						var s = e.slice(0, 3) !== "nth",
							o = e.slice(-4) !== "last",
							u = t === "of-type";
						return r === 1 && i === 0 ? function (e) {
							return !!e.parentNode
						} : function (t, n, a) {
							var f, l, c, h, p, d, v = s !== o ? "nextSibling" : "previousSibling",
								m = t.parentNode,
								g = u && t.nodeName.toLowerCase(),
								y = !a && !u;
							if (m) {
								if (s) {
									while (v) {
										c = t;
										while (c = c[v])
											if (u ? c.nodeName.toLowerCase() === g : c.nodeType === 1) return !1;
										d = v = e === "only" && !d && "nextSibling"
									}
									return !0
								}
								d = [o ? m.firstChild : m.lastChild];
								if (o && y) {
									l = m[b] || (m[b] = {}), f = l[e] || [], p = f[0] === S && f[1], h = f[0] === S && f[2], c = p && m.childNodes[p];
									while (c = ++p && c && c[v] || (h = p = 0) || d.pop())
										if (c.nodeType === 1 && ++h && c === t) {
											l[e] = [S, p, h];
											break
										}
								} else if (y && (f = (t[b] || (t[b] = {}))[e]) && f[0] === S) h = f[1];
								else
									while (c = ++p && c && c[v] || (h = p = 0) || d.pop())
										if ((u ? c.nodeName.toLowerCase() === g : c.nodeType === 1) && ++h) {
											y && ((c[b] || (c[b] = {}))[e] = [S, h]);
											if (c === t) break
										} return h -= i, h === r || h % r === 0 && h / r >= 0
							}
						}
					},
					PSEUDO: function (e, t) {
						var n, r = s.pseudos[e] || s.setFilters[e.toLowerCase()] || ot.error("unsupported pseudo: " + e);
						return r[b] ? r(t) : r.length > 1 ? (n = [e, e, "", t], s.setFilters.hasOwnProperty(e.toLowerCase()) ? at(function (e, n) {
							var i, s = r(e, t),
								o = s.length;
							while (o--) i = j.call(e, s[o]), e[i] = !(n[i] = s[o])
						}) : function (e) {
							return r(e, 0, n)
						}) : r
					}
				},
				pseudos: {
					not: at(function (e) {
						var t = [],
							n = [],
							r = a(e.replace(W, "$1"));
						return r[b] ? at(function (e, t, n, i) {
							var s, o = r(e, null, i, []),
								u = e.length;
							while (u--)
								if (s = o[u]) e[u] = !(t[u] = s)
						}) : function (e, i, s) {
							return t[0] = e, r(t, null, s, n), !n.pop()
						}
					}),
					has: at(function (e) {
						return function (t) {
							return ot(e, t).length > 0
						}
					}),
					contains: at(function (e) {
						return function (t) {
							return (t.textContent || t.innerText || o(t)).indexOf(e) > -1
						}
					}),
					lang: at(function (e) {
						return Q.test(e || "") || ot.error("unsupported lang: " + e), e = e.replace(rt, it).toLowerCase(),
							function (t) {
								var n;
								do
									if (n = d ? t.lang : t.getAttribute("xml:lang") || t.getAttribute("lang")) return n = n.toLowerCase(), n === e || n.indexOf(e + "-") === 0;
								while ((t = t.parentNode) && t.nodeType === 1);
								return !1
							}
					}),
					target: function (t) {
						var n = e.location && e.location.hash;
						return n && n.slice(1) === t.id
					},
					root: function (e) {
						return e === p
					},
					focus: function (e) {
						return e === h.activeElement && (!h.hasFocus || h.hasFocus()) && !!(e.type || e.href || ~e.tabIndex)
					},
					enabled: function (e) {
						return e.disabled === !1
					},
					disabled: function (e) {
						return e.disabled === !0
					},
					checked: function (e) {
						var t = e.nodeName.toLowerCase();
						return t === "input" && !!e.checked || t === "option" && !!e.selected
					},
					selected: function (e) {
						return e.parentNode && e.parentNode.selectedIndex, e.selected === !0
					},
					empty: function (e) {
						for (e = e.firstChild; e; e = e.nextSibling)
							if (e.nodeName > "@" || e.nodeType === 3 || e.nodeType === 4) return !1;
						return !0
					},
					parent: function (e) {
						return !s.pseudos.empty(e)
					},
					header: function (e) {
						return tt.test(e.nodeName)
					},
					input: function (e) {
						return et.test(e.nodeName)
					},
					button: function (e) {
						var t = e.nodeName.toLowerCase();
						return t === "input" && e.type === "button" || t === "button"
					},
					text: function (e) {
						var t;
						return e.nodeName.toLowerCase() === "input" && e.type === "text" && ((t = e.getAttribute("type")) == null || t.toLowerCase() === e.type)
					},
					first: dt(function () {
						return [0]
					}),
					last: dt(function (e, t) {
						return [t - 1]
					}),
					eq: dt(function (e, t, n) {
						return [n < 0 ? n + t : n]
					}),
					even: dt(function (e, t) {
						var n = 0;
						for (; n < t; n += 2) e.push(n);
						return e
					}),
					odd: dt(function (e, t) {
						var n = 1;
						for (; n < t; n += 2) e.push(n);
						return e
					}),
					lt: dt(function (e, t, n) {
						var r = n < 0 ? n + t : n;
						for (; --r >= 0;) e.push(r);
						return e
					}),
					gt: dt(function (e, t, n) {
						var r = n < 0 ? n + t : n;
						for (; ++r < t;) e.push(r);
						return e
					})
				}
			}, s.pseudos.nth = s.pseudos.eq;
			for (n in {
					radio: !0,
					checkbox: !0,
					file: !0,
					password: !0,
					image: !0
				}) s.pseudos[n] = ht(n);
			for (n in {
					submit: !0,
					reset: !0
				}) s.pseudos[n] = pt(n);
			vt.prototype = s.filters = s.pseudos, s.setFilters = new vt, a = ot.compile = function (e, t) {
				var n, r = [],
					i = [],
					s = C[e + " "];
				if (!s) {
					t || (t = mt(e)), n = t.length;
					while (n--) s = St(t[n]), s[b] ? r.push(s) : i.push(s);
					s = C(e, xt(i, r))
				}
				return s
			}, r.sortStable = b.split("").sort(L).join("") === b, r.detectDuplicates = k, c(), r.sortDetached = ft(function (e) {
				return e.compareDocumentPosition(h.createElement("div")) & 1
			}), ft(function (e) {
				return e.innerHTML = "<a href='#'></a>", e.firstChild.getAttribute("href") === "#"
			}) || lt("type|href|height|width", function (e, t, n) {
				if (!n) return e.getAttribute(t, t.toLowerCase() === "type" ? 1 : 2)
			}), (!r.attributes || !ft(function (e) {
				return e.innerHTML = "<input/>", e.firstChild.setAttribute("value", ""), e.firstChild.getAttribute("value") === ""
			})) && lt("value", function (e, t, n) {
				if (!n && e.nodeName.toLowerCase() === "input") return e.defaultValue
			}), ft(function (e) {
				return e.getAttribute("disabled") == null
			}) || lt(F, function (e, t, n) {
				var r;
				if (!n) return (r = e.getAttributeNode(t)) && r.specified ? r.value : e[t] === !0 ? t.toLowerCase() : null
			}), w.find = ot, w.expr = ot.selectors, w.expr[":"] = w.expr.pseudos, w.unique = ot.uniqueSort, w.text = ot.getText, w.isXMLDoc = ot.isXML, w.contains = ot.contains
		}(e);
	var B = {};
	w.Callbacks = function (e) {
		e = typeof e == "string" ? B[e] || j(e) : w.extend({}, e);
		var n, r, i, s, o, u, a = [],
			f = !e.once && [],
			l = function (t) {
				r = e.memory && t, i = !0, o = u || 0, u = 0, s = a.length, n = !0;
				for (; a && o < s; o++)
					if (a[o].apply(t[0], t[1]) === !1 && e.stopOnFalse) {
						r = !1;
						break
					}
				n = !1, a && (f ? f.length && l(f.shift()) : r ? a = [] : c.disable())
			},
			c = {
				add: function () {
					if (a) {
						var t = a.length;
						(function i(t) {
							w.each(t, function (t, n) {
								var r = w.type(n);
								r === "function" ? (!e.unique || !c.has(n)) && a.push(n) : n && n.length && r !== "string" && i(n)
							})
						})(arguments), n ? s = a.length : r && (u = t, l(r))
					}
					return this
				},
				remove: function () {
					return a && w.each(arguments, function (e, t) {
						var r;
						while ((r = w.inArray(t, a, r)) > -1) a.splice(r, 1), n && (r <= s && s--, r <= o && o--)
					}), this
				},
				has: function (e) {
					return e ? w.inArray(e, a) > -1 : !!a && !!a.length
				},
				empty: function () {
					return a = [], s = 0, this
				},
				disable: function () {
					return a = f = r = t, this
				},
				disabled: function () {
					return !a
				},
				lock: function () {
					return f = t, r || c.disable(), this
				},
				locked: function () {
					return !f
				},
				fireWith: function (e, t) {
					return a && (!i || f) && (t = t || [], t = [e, t.slice ? t.slice() : t], n ? f.push(t) : l(t)), this
				},
				fire: function () {
					return c.fireWith(this, arguments), this
				},
				fired: function () {
					return !!i
				}
			};
		return c
	}, w.extend({
		Deferred: function (e) {
			var t = [["resolve", "done", w.Callbacks("once memory"), "resolved"], ["reject", "fail", w.Callbacks("once memory"), "rejected"], ["notify", "progress", w.Callbacks("memory")]],
				n = "pending",
				r = {
					state: function () {
						return n
					},
					always: function () {
						return i.done(arguments).fail(arguments), this
					},
					then: function () {
						var e = arguments;
						return w.Deferred(function (n) {
							w.each(t, function (t, s) {
								var o = s[0],
									u = w.isFunction(e[t]) && e[t];
								i[s[1]](function () {
									var e = u && u.apply(this, arguments);
									e && w.isFunction(e.promise) ? e.promise().done(n.resolve).fail(n.reject).progress(n.notify) : n[o + "With"](this === r ? n.promise() : this, u ? [e] : arguments)
								})
							}), e = null
						}).promise()
					},
					promise: function (e) {
						return e != null ? w.extend(e, r) : r
					}
				},
				i = {};
			return r.pipe = r.then, w.each(t, function (e, s) {
				var o = s[2],
					u = s[3];
				r[s[1]] = o.add, u && o.add(function () {
					n = u
				}, t[e ^ 1][2].disable, t[2][2].lock), i[s[0]] = function () {
					return i[s[0] + "With"](this === i ? r : this, arguments), this
				}, i[s[0] + "With"] = o.fireWith
			}), r.promise(i), e && e.call(i, i), i
		},
		when: function (e) {
			var t = 0,
				n = v.call(arguments),
				r = n.length,
				i = r !== 1 || e && w.isFunction(e.promise) ? r : 0,
				s = i === 1 ? e : w.Deferred(),
				o = function (e, t, n) {
					return function (r) {
						t[e] = this, n[e] = arguments.length > 1 ? v.call(arguments) : r, n === u ? s.notifyWith(t, n) : --i || s.resolveWith(t, n)
					}
				},
				u, a, f;
			if (r > 1) {
				u = new Array(r), a = new Array(r), f = new Array(r);
				for (; t < r; t++) n[t] && w.isFunction(n[t].promise) ? n[t].promise().done(o(t, f, n)).fail(s.reject).progress(o(t, a, u)) : --i
			}
			return i || s.resolveWith(f, n), s.promise()
		}
	}), w.support = function (t) {
		var n, r, s, u, a, f, l, c, h, p = o.createElement("div");
		p.setAttribute("className", "t"), p.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>", n = p.getElementsByTagName("*") || [], r = p.getElementsByTagName("a")[0];
		if (!r || !r.style || !n.length) return t;
		u = o.createElement("select"), f = u.appendChild(o.createElement("option")), s = p.getElementsByTagName("input")[0], r.style.cssText = "top:1px;float:left;opacity:.5", t.getSetAttribute = p.className !== "t", t.leadingWhitespace = p.firstChild.nodeType === 3, t.tbody = !p.getElementsByTagName("tbody").length, t.htmlSerialize = !!p.getElementsByTagName("link").length, t.style = /top/.test(r.getAttribute("style")), t.hrefNormalized = r.getAttribute("href") === "/a", t.opacity = /^0.5/.test(r.style.opacity), t.cssFloat = !!r.style.cssFloat, t.checkOn = !!s.value, t.optSelected = f.selected, t.enctype = !!o.createElement("form").enctype, t.html5Clone = o.createElement("nav").cloneNode(!0).outerHTML !== "<:nav></:nav>", t.inlineBlockNeedsLayout = !1, t.shrinkWrapBlocks = !1, t.pixelPosition = !1, t.deleteExpando = !0, t.noCloneEvent = !0, t.reliableMarginRight = !0, t.boxSizingReliable = !0, s.checked = !0, t.noCloneChecked = s.cloneNode(!0).checked, u.disabled = !0, t.optDisabled = !f.disabled;
		try {
			delete p.test
		} catch (d) {
			t.deleteExpando = !1
		}
		s = o.createElement("input"), s.setAttribute("value", ""), t.input = s.getAttribute("value") === "", s.value = "t", s.setAttribute("type", "radio"), t.radioValue = s.value === "t", s.setAttribute("checked", "t"), s.setAttribute("name", "t"), a = o.createDocumentFragment(), a.appendChild(s), t.appendChecked = s.checked, t.checkClone = a.cloneNode(!0).cloneNode(!0).lastChild.checked, p.attachEvent && (p.attachEvent("onclick", function () {
			t.noCloneEvent = !1
		}), p.cloneNode(!0).click());
		for (h in {
				submit: !0,
				change: !0,
				focusin: !0
			}) p.setAttribute(l = "on" + h, "t"), t[h + "Bubbles"] = l in e || p.attributes[l].expando === !1;
		p.style.backgroundClip = "content-box", p.cloneNode(!0).style.backgroundClip = "", t.clearCloneStyle = p.style.backgroundClip === "content-box";
		for (h in w(t)) break;
		return t.ownLast = h !== "0", w(function () {
			var n, r, s, u = "padding:0;margin:0;border:0;display:block;box-sizing:content-box;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;",
				a = o.getElementsByTagName("body")[0];
			if (!a) return;
			n = o.createElement("div"), n.style.cssText = "border:0;width:0;height:0;position:absolute;top:0;left:-9999px;margin-top:1px", a.appendChild(n).appendChild(p), p.innerHTML = "<table><tr><td></td><td>t</td></tr></table>", s = p.getElementsByTagName("td"), s[0].style.cssText = "padding:0;margin:0;border:0;display:none", c = s[0].offsetHeight === 0, s[0].style.display = "", s[1].style.display = "none", t.reliableHiddenOffsets = c && s[0].offsetHeight === 0, p.innerHTML = "", p.style.cssText = "box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;", w.swap(a, a.style.zoom != null ? {
				zoom: 1
			} : {}, function () {
				t.boxSizing = p.offsetWidth === 4
			}), e.getComputedStyle && (t.pixelPosition = (e.getComputedStyle(p, null) || {}).top !== "1%", t.boxSizingReliable = (e.getComputedStyle(p, null) || {
				width: "4px"
			}).width === "4px", r = p.appendChild(o.createElement("div")), r.style.cssText = p.style.cssText = u, r.style.marginRight = r.style.width = "0", p.style.width = "1px", t.reliableMarginRight = !parseFloat((e.getComputedStyle(r, null) || {}).marginRight)), typeof p.style.zoom !== i && (p.innerHTML = "", p.style.cssText = u + "width:1px;padding:1px;display:inline;zoom:1", t.inlineBlockNeedsLayout = p.offsetWidth === 3, p.style.display = "block", p.innerHTML = "<div></div>", p.firstChild.style.width = "5px", t.shrinkWrapBlocks = p.offsetWidth !== 3, t.inlineBlockNeedsLayout && (a.style.zoom = 1)), a.removeChild(n), n = p = s = r = null
		}), n = u = a = f = r = s = null, t
	}({});
	var F = /(?:\{[\s\S]*\}|\[[\s\S]*\])$/,
		I = /([A-Z])/g;
	w.extend({
		cache: {},
		noData: {
			applet: !0,
			embed: !0,
			object: "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
		},
		hasData: function (e) {
			return e = e.nodeType ? w.cache[e[w.expando]] : e[w.expando], !!e && !z(e)
		},
		data: function (e, t, n) {
			return q(e, t, n)
		},
		removeData: function (e, t) {
			return R(e, t)
		},
		_data: function (e, t, n) {
			return q(e, t, n, !0)
		},
		_removeData: function (e, t) {
			return R(e, t, !0)
		},
		acceptData: function (e) {
			if (e.nodeType && e.nodeType !== 1 && e.nodeType !== 9) return !1;
			var t = e.nodeName && w.noData[e.nodeName.toLowerCase()];
			return !t || t !== !0 && e.getAttribute("classid") === t
		}
	}), w.fn.extend({
		data: function (e, n) {
			var r, i, s = null,
				o = 0,
				u = this[0];
			if (e === t) {
				if (this.length) {
					s = w.data(u);
					if (u.nodeType === 1 && !w._data(u, "parsedAttrs")) {
						r = u.attributes;
						for (; o < r.length; o++) i = r[o].name, i.indexOf("data-") === 0 && (i = w.camelCase(i.slice(5)), U(u, i, s[i]));
						w._data(u, "parsedAttrs", !0)
					}
				}
				return s
			}
			return typeof e == "object" ? this.each(function () {
				w.data(this, e)
			}) : arguments.length > 1 ? this.each(function () {
				w.data(this, e, n)
			}) : u ? U(u, e, w.data(u, e)) : null
		},
		removeData: function (e) {
			return this.each(function () {
				w.removeData(this, e)
			})
		}
	}), w.extend({
		queue: function (e, t, n) {
			var r;
			if (e) return t = (t || "fx") + "queue", r = w._data(e, t), n && (!r || w.isArray(n) ? r = w._data(e, t, w.makeArray(n)) : r.push(n)), r || []
		},
		dequeue: function (e, t) {
			t = t || "fx";
			var n = w.queue(e, t),
				r = n.length,
				i = n.shift(),
				s = w._queueHooks(e, t),
				o = function () {
					w.dequeue(e, t)
				};
			i === "inprogress" && (i = n.shift(), r--), i && (t === "fx" && n.unshift("inprogress"), delete s.stop, i.call(e, o, s)), !r && s && s.empty.fire()
		},
		_queueHooks: function (e, t) {
			var n = t + "queueHooks";
			return w._data(e, n) || w._data(e, n, {
				empty: w.Callbacks("once memory").add(function () {
					w._removeData(e, t + "queue"), w._removeData(e, n)
				})
			})
		}
	}), w.fn.extend({
		queue: function (e, n) {
			var r = 2;
			return typeof e != "string" && (n = e, e = "fx", r--), arguments.length < r ? w.queue(this[0], e) : n === t ? this : this.each(function () {
				var t = w.queue(this, e, n);
				w._queueHooks(this, e), e === "fx" && t[0] !== "inprogress" && w.dequeue(this, e)
			})
		},
		dequeue: function (e) {
			return this.each(function () {
				w.dequeue(this, e)
			})
		},
		delay: function (e, t) {
			return e = w.fx ? w.fx.speeds[e] || e : e, t = t || "fx", this.queue(t, function (t, n) {
				var r = setTimeout(t, e);
				n.stop = function () {
					clearTimeout(r)
				}
			})
		},
		clearQueue: function (e) {
			return this.queue(e || "fx", [])
		},
		promise: function (e, n) {
			var r, i = 1,
				s = w.Deferred(),
				o = this,
				u = this.length,
				a = function () {
					--i || s.resolveWith(o, [o])
				};
			typeof e != "string" && (n = e, e = t), e = e || "fx";
			while (u--) r = w._data(o[u], e + "queueHooks"), r && r.empty && (i++, r.empty.add(a));
			return a(), s.promise(n)
		}
	});
	var W, X, V = /[\t\r\n\f]/g,
		$ = /\r/g,
		J = /^(?:input|select|textarea|button|object)$/i,
		K = /^(?:a|area)$/i,
		Q = /^(?:checked|selected)$/i,
		G = w.support.getSetAttribute,
		Y = w.support.input;
	w.fn.extend({
		attr: function (e, t) {
			return w.access(this, w.attr, e, t, arguments.length > 1)
		},
		removeAttr: function (e) {
			return this.each(function () {
				w.removeAttr(this, e)
			})
		},
		prop: function (e, t) {
			return w.access(this, w.prop, e, t, arguments.length > 1)
		},
		removeProp: function (e) {
			return e = w.propFix[e] || e, this.each(function () {
				try {
					this[e] = t, delete this[e]
				} catch (n) {}
			})
		},
		addClass: function (e) {
			var t, n, r, i, s, o = 0,
				u = this.length,
				a = typeof e == "string" && e;
			if (w.isFunction(e)) return this.each(function (t) {
				w(this).addClass(e.call(this, t, this.className))
			});
			if (a) {
				t = (e || "").match(S) || [];
				for (; o < u; o++) {
					n = this[o], r = n.nodeType === 1 && (n.className ? (" " + n.className + " ").replace(V, " ") : " ");
					if (r) {
						s = 0;
						while (i = t[s++]) r.indexOf(" " + i + " ") < 0 && (r += i + " ");
						n.className = w.trim(r)
					}
				}
			}
			return this
		},
		removeClass: function (e) {
			var t, n, r, i, s, o = 0,
				u = this.length,
				a = arguments.length === 0 || typeof e == "string" && e;
			if (w.isFunction(e)) return this.each(function (t) {
				w(this).removeClass(e.call(this, t, this.className))
			});
			if (a) {
				t = (e || "").match(S) || [];
				for (; o < u; o++) {
					n = this[o], r = n.nodeType === 1 && (n.className ? (" " + n.className + " ").replace(V, " ") : "");
					if (r) {
						s = 0;
						while (i = t[s++])
							while (r.indexOf(" " + i + " ") >= 0) r = r.replace(" " + i + " ", " ");
						n.className = e ? w.trim(r) : ""
					}
				}
			}
			return this
		},
		toggleClass: function (e, t) {
			var n = typeof e;
			return typeof t == "boolean" && n === "string" ? t ? this.addClass(e) : this.removeClass(e) : w.isFunction(e) ? this.each(function (n) {
				w(this).toggleClass(e.call(this, n, this.className, t), t)
			}) : this.each(function () {
				if (n === "string") {
					var t, r = 0,
						s = w(this),
						o = e.match(S) || [];
					while (t = o[r++]) s.hasClass(t) ? s.removeClass(t) : s.addClass(t)
				} else if (n === i || n === "boolean") this.className && w._data(this, "__className__", this.className), this.className = this.className || e === !1 ? "" : w._data(this, "__className__") || ""
			})
		},
		hasClass: function (e) {
			var t = " " + e + " ",
				n = 0,
				r = this.length;
			for (; n < r; n++)
				if (this[n].nodeType === 1 && (" " + this[n].className + " ").replace(V, " ").indexOf(t) >= 0) return !0;
			return !1
		},
		val: function (e) {
			var n, r, i, s = this[0];
			if (!arguments.length) {
				if (s) return r = w.valHooks[s.type] || w.valHooks[s.nodeName.toLowerCase()], r && "get" in r && (n = r.get(s, "value")) !== t ? n : (n = s.value, typeof n == "string" ? n.replace($, "") : n == null ? "" : n);
				return
			}
			return i = w.isFunction(e), this.each(function (n) {
				var s;
				if (this.nodeType !== 1) return;
				i ? s = e.call(this, n, w(this).val()) : s = e, s == null ? s = "" : typeof s == "number" ? s += "" : w.isArray(s) && (s = w.map(s, function (e) {
					return e == null ? "" : e + ""
				})), r = w.valHooks[this.type] || w.valHooks[this.nodeName.toLowerCase()];
				if (!r || !("set" in r) || r.set(this, s, "value") === t) this.value = s
			})
		}
	}), w.extend({
		valHooks: {
			option: {
				get: function (e) {
					var t = w.find.attr(e, "value");
					return t != null ? t : e.text
				}
			},
			select: {
				get: function (e) {
					var t, n, r = e.options,
						i = e.selectedIndex,
						s = e.type === "select-one" || i < 0,
						o = s ? null : [],
						u = s ? i + 1 : r.length,
						a = i < 0 ? u : s ? i : 0;
					for (; a < u; a++) {
						n = r[a];
						if ((n.selected || a === i) && (w.support.optDisabled ? !n.disabled : n.getAttribute("disabled") === null) && (!n.parentNode.disabled || !w.nodeName(n.parentNode, "optgroup"))) {
							t = w(n).val();
							if (s) return t;
							o.push(t)
						}
					}
					return o
				},
				set: function (e, t) {
					var n, r, i = e.options,
						s = w.makeArray(t),
						o = i.length;
					while (o--) {
						r = i[o];
						if (r.selected = w.inArray(w(r).val(), s) >= 0) n = !0
					}
					return n || (e.selectedIndex = -1), s
				}
			}
		},
		attr: function (e, n, r) {
			var s, o, u = e.nodeType;
			if (!e || u === 3 || u === 8 || u === 2) return;
			if (typeof e.getAttribute === i) return w.prop(e, n, r);
			if (u !== 1 || !w.isXMLDoc(e)) n = n.toLowerCase(), s = w.attrHooks[n] || (w.expr.match.bool.test(n) ? X : W);
			if (r === t) return s && "get" in s && (o = s.get(e, n)) !== null ? o : (o = w.find.attr(e, n), o == null ? t : o);
			if (r !== null) return s && "set" in s && (o = s.set(e, r, n)) !== t ? o : (e.setAttribute(n, r + ""), r);
			w.removeAttr(e, n)
		},
		removeAttr: function (e, t) {
			var n, r, i = 0,
				s = t && t.match(S);
			if (s && e.nodeType === 1)
				while (n = s[i++]) r = w.propFix[n] || n, w.expr.match.bool.test(n) ? Y && G || !Q.test(n) ? e[r] = !1 : e[w.camelCase("default-" + n)] = e[r] = !1 : w.attr(e, n, ""), e.removeAttribute(G ? n : r)
		},
		attrHooks: {
			type: {
				set: function (e, t) {
					if (!w.support.radioValue && t === "radio" && w.nodeName(e, "input")) {
						var n = e.value;
						return e.setAttribute("type", t), n && (e.value = n), t
					}
				}
			}
		},
		propFix: {
			"for": "htmlFor",
			"class": "className"
		},
		prop: function (e, n, r) {
			var i, s, o, u = e.nodeType;
			if (!e || u === 3 || u === 8 || u === 2) return;
			return o = u !== 1 || !w.isXMLDoc(e), o && (n = w.propFix[n] || n, s = w.propHooks[n]), r !== t ? s && "set" in s && (i = s.set(e, r, n)) !== t ? i : e[n] = r : s && "get" in s && (i = s.get(e, n)) !== null ? i : e[n]
		},
		propHooks: {
			tabIndex: {
				get: function (e) {
					var t = w.find.attr(e, "tabindex");
					return t ? parseInt(t, 10) : J.test(e.nodeName) || K.test(e.nodeName) && e.href ? 0 : -1
				}
			}
		}
	}), X = {
		set: function (e, t, n) {
			return t === !1 ? w.removeAttr(e, n) : Y && G || !Q.test(n) ? e.setAttribute(!G && w.propFix[n] || n, n) : e[w.camelCase("default-" + n)] = e[n] = !0, n
		}
	}, w.each(w.expr.match.bool.source.match(/\w+/g), function (e, n) {
		var r = w.expr.attrHandle[n] || w.find.attr;
		w.expr.attrHandle[n] = Y && G || !Q.test(n) ? function (e, n, i) {
			var s = w.expr.attrHandle[n],
				o = i ? t : (w.expr.attrHandle[n] = t) != r(e, n, i) ? n.toLowerCase() : null;
			return w.expr.attrHandle[n] = s, o
		} : function (e, n, r) {
			return r ? t : e[w.camelCase("default-" + n)] ? n.toLowerCase() : null
		}
	});
	if (!Y || !G) w.attrHooks.value = {
		set: function (e, t, n) {
			if (!w.nodeName(e, "input")) return W && W.set(e, t, n);
			e.defaultValue = t
		}
	};
	G || (W = {
		set: function (e, n, r) {
			var i = e.getAttributeNode(r);
			return i || e.setAttributeNode(i = e.ownerDocument.createAttribute(r)), i.value = n += "", r === "value" || n === e.getAttribute(r) ? n : t
		}
	}, w.expr.attrHandle.id = w.expr.attrHandle.name = w.expr.attrHandle.coords = function (e, n, r) {
		var i;
		return r ? t : (i = e.getAttributeNode(n)) && i.value !== "" ? i.value : null
	}, w.valHooks.button = {
		get: function (e, n) {
			var r = e.getAttributeNode(n);
			return r && r.specified ? r.value : t
		},
		set: W.set
	}, w.attrHooks.contenteditable = {
		set: function (e, t, n) {
			W.set(e, t === "" ? !1 : t, n)
		}
	}, w.each(["width", "height"], function (e, t) {
		w.attrHooks[t] = {
			set: function (e, n) {
				if (n === "") return e.setAttribute(t, "auto"), n
			}
		}
	})), w.support.hrefNormalized || w.each(["href", "src"], function (e, t) {
		w.propHooks[t] = {
			get: function (e) {
				return e.getAttribute(t, 4)
			}
		}
	}), w.support.style || (w.attrHooks.style = {
		get: function (e) {
			return e.style.cssText || t
		},
		set: function (e, t) {
			return e.style.cssText = t + ""
		}
	}), w.support.optSelected || (w.propHooks.selected = {
		get: function (e) {
			var t = e.parentNode;
			return t && (t.selectedIndex, t.parentNode && t.parentNode.selectedIndex), null
		}
	}), w.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function () {
		w.propFix[this.toLowerCase()] = this
	}), w.support.enctype || (w.propFix.enctype = "encoding"), w.each(["radio", "checkbox"], function () {
		w.valHooks[this] = {
			set: function (e, t) {
				if (w.isArray(t)) return e.checked = w.inArray(w(e).val(), t) >= 0
			}
		}, w.support.checkOn || (w.valHooks[this].get = function (e) {
			return e.getAttribute("value") === null ? "on" : e.value
		})
	});
	var Z = /^(?:input|select|textarea)$/i,
		et = /^key/,
		tt = /^(?:mouse|contextmenu)|click/,
		nt = /^(?:focusinfocus|focusoutblur)$/,
		rt = /^([^.]*)(?:\.(.+)|)$/;
	w.event = {
		global: {},
		add: function (e, n, r, s, o) {
			var u, a, f, l, c, h, p, d, v, m, g, y = w._data(e);
			if (!y) return;
			r.handler && (l = r, r = l.handler, o = l.selector), r.guid || (r.guid = w.guid++), (a = y.events) || (a = y.events = {}), (h = y.handle) || (h = y.handle = function (e) {
				return typeof w === i || !!e && w.event.triggered === e.type ? t : w.event.dispatch.apply(h.elem, arguments)
			}, h.elem = e), n = (n || "").match(S) || [""], f = n.length;
			while (f--) {
				u = rt.exec(n[f]) || [], v = g = u[1], m = (u[2] || "").split(".").sort();
				if (!v) continue;
				c = w.event.special[v] || {}, v = (o ? c.delegateType : c.bindType) || v, c = w.event.special[v] || {}, p = w.extend({
					type: v,
					origType: g,
					data: s,
					handler: r,
					guid: r.guid,
					selector: o,
					needsContext: o && w.expr.match.needsContext.test(o),
					namespace: m.join(".")
				}, l);
				if (!(d = a[v])) {
					d = a[v] = [], d.delegateCount = 0;
					if (!c.setup || c.setup.call(e, s, m, h) === !1) e.addEventListener ? e.addEventListener(v, h, !1) : e.attachEvent && e.attachEvent("on" + v, h)
				}
				c.add && (c.add.call(e, p), p.handler.guid || (p.handler.guid = r.guid)), o ? d.splice(d.delegateCount++, 0, p) : d.push(p), w.event.global[v] = !0
			}
			e = null
		},
		remove: function (e, t, n, r, i) {
			var s, o, u, a, f, l, c, h, p, d, v, m = w.hasData(e) && w._data(e);
			if (!m || !(l = m.events)) return;
			t = (t || "").match(S) || [""], f = t.length;
			while (f--) {
				u = rt.exec(t[f]) || [], p = v = u[1], d = (u[2] || "").split(".").sort();
				if (!p) {
					for (p in l) w.event.remove(e, p + t[f], n, r, !0);
					continue
				}
				c = w.event.special[p] || {}, p = (r ? c.delegateType : c.bindType) || p, h = l[p] || [], u = u[2] && new RegExp("(^|\\.)" + d.join("\\.(?:.*\\.|)") + "(\\.|$)"), a = s = h.length;
				while (s--) o = h[s], (i || v === o.origType) && (!n || n.guid === o.guid) && (!u || u.test(o.namespace)) && (!r || r === o.selector || r === "**" && o.selector) && (h.splice(s, 1), o.selector && h.delegateCount--, c.remove && c.remove.call(e, o));
				a && !h.length && ((!c.teardown || c.teardown.call(e, d, m.handle) === !1) && w.removeEvent(e, p, m.handle), delete l[p])
			}
			w.isEmptyObject(l) && (delete m.handle, w._removeData(e, "events"))
		},
		trigger: function (n, r, i, s) {
			var u, a, f, l, c, h, p, d = [i || o],
				v = y.call(n, "type") ? n.type : n,
				m = y.call(n, "namespace") ? n.namespace.split(".") : [];
			f = h = i = i || o;
			if (i.nodeType === 3 || i.nodeType === 8) return;
			if (nt.test(v + w.event.triggered)) return;
			v.indexOf(".") >= 0 && (m = v.split("."), v = m.shift(), m.sort()), a = v.indexOf(":") < 0 && "on" + v, n = n[w.expando] ? n : new w.Event(v, typeof n == "object" && n), n.isTrigger = s ? 2 : 3, n.namespace = m.join("."), n.namespace_re = n.namespace ? new RegExp("(^|\\.)" + m.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, n.result = t, n.target || (n.target = i), r = r == null ? [n] : w.makeArray(r, [n]), c = w.event.special[v] || {};
			if (!s && c.trigger && c.trigger.apply(i, r) === !1) return;
			if (!s && !c.noBubble && !w.isWindow(i)) {
				l = c.delegateType || v, nt.test(l + v) || (f = f.parentNode);
				for (; f; f = f.parentNode) d.push(f), h = f;
				h === (i.ownerDocument || o) && d.push(h.defaultView || h.parentWindow || e)
			}
			p = 0;
			while ((f = d[p++]) && !n.isPropagationStopped()) n.type = p > 1 ? l : c.bindType || v, u = (w._data(f, "events") || {})[n.type] && w._data(f, "handle"), u && u.apply(f, r), u = a && f[a], u && w.acceptData(f) && u.apply && u.apply(f, r) === !1 && n.preventDefault();
			n.type = v;
			if (!s && !n.isDefaultPrevented() && (!c._default || c._default.apply(d.pop(), r) === !1) && w.acceptData(i) && a && i[v] && !w.isWindow(i)) {
				h = i[a], h && (i[a] = null), w.event.triggered = v;
				try {
					i[v]()
				} catch (g) {}
				w.event.triggered = t, h && (i[a] = h)
			}
			return n.result
		},
		dispatch: function (e) {
			e = w.event.fix(e);
			var n, r, i, s, o, u = [],
				a = v.call(arguments),
				f = (w._data(this, "events") || {})[e.type] || [],
				l = w.event.special[e.type] || {};
			a[0] = e, e.delegateTarget = this;
			if (l.preDispatch && l.preDispatch.call(this, e) === !1) return;
			u = w.event.handlers.call(this, e, f), n = 0;
			while ((s = u[n++]) && !e.isPropagationStopped()) {
				e.currentTarget = s.elem, o = 0;
				while ((i = s.handlers[o++]) && !e.isImmediatePropagationStopped())
					if (!e.namespace_re || e.namespace_re.test(i.namespace)) e.handleObj = i, e.data = i.data, r = ((w.event.special[i.origType] || {}).handle || i.handler).apply(s.elem, a), r !== t && (e.result = r) === !1 && (e.preventDefault(), e.stopPropagation())
			}
			return l.postDispatch && l.postDispatch.call(this, e), e.result
		},
		handlers: function (e, n) {
			var r, i, s, o, u = [],
				a = n.delegateCount,
				f = e.target;
			if (a && f.nodeType && (!e.button || e.type !== "click"))
				for (; f != this; f = f.parentNode || this)
					if (f.nodeType === 1 && (f.disabled !== !0 || e.type !== "click")) {
						s = [];
						for (o = 0; o < a; o++) i = n[o], r = i.selector + " ", s[r] === t && (s[r] = i.needsContext ? w(r, this).index(f) >= 0 : w.find(r, this, null, [f]).length), s[r] && s.push(i);
						s.length && u.push({
							elem: f,
							handlers: s
						})
					}
			return a < n.length && u.push({
				elem: this,
				handlers: n.slice(a)
			}), u
		},
		fix: function (e) {
			if (e[w.expando]) return e;
			var t, n, r, i = e.type,
				s = e,
				u = this.fixHooks[i];
			u || (this.fixHooks[i] = u = tt.test(i) ? this.mouseHooks : et.test(i) ? this.keyHooks : {}), r = u.props ? this.props.concat(u.props) : this.props, e = new w.Event(s), t = r.length;
			while (t--) n = r[t], e[n] = s[n];
			return e.target || (e.target = s.srcElement || o), e.target.nodeType === 3 && (e.target = e.target.parentNode), e.metaKey = !!e.metaKey, u.filter ? u.filter(e, s) : e
		},
		props: "altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
		fixHooks: {},
		keyHooks: {
			props: "char charCode key keyCode".split(" "),
			filter: function (e, t) {
				return e.which == null && (e.which = t.charCode != null ? t.charCode : t.keyCode), e
			}
		},
		mouseHooks: {
			props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
			filter: function (e, n) {
				var r, i, s, u = n.button,
					a = n.fromElement;
				return e.pageX == null && n.clientX != null && (i = e.target.ownerDocument || o, s = i.documentElement, r = i.body, e.pageX = n.clientX + (s && s.scrollLeft || r && r.scrollLeft || 0) - (s && s.clientLeft || r && r.clientLeft || 0), e.pageY = n.clientY + (s && s.scrollTop || r && r.scrollTop || 0) - (s && s.clientTop || r && r.clientTop || 0)), !e.relatedTarget && a && (e.relatedTarget = a === e.target ? n.toElement : a), !e.which && u !== t && (e.which = u & 1 ? 1 : u & 2 ? 3 : u & 4 ? 2 : 0), e
			}
		},
		special: {
			load: {
				noBubble: !0
			},
			focus: {
				trigger: function () {
					if (this !== ot() && this.focus) try {
						return this.focus(), !1
					} catch (e) {}
				},
				delegateType: "focusin"
			},
			blur: {
				trigger: function () {
					if (this === ot() && this.blur) return this.blur(), !1
				},
				delegateType: "focusout"
			},
			click: {
				trigger: function () {
					if (w.nodeName(this, "input") && this.type === "checkbox" && this.click) return this.click(), !1
				},
				_default: function (e) {
					return w.nodeName(e.target, "a")
				}
			},
			beforeunload: {
				postDispatch: function (e) {
					e.result !== t && (e.originalEvent.returnValue = e.result)
				}
			}
		},
		simulate: function (e, t, n, r) {
			var i = w.extend(new w.Event, n, {
				type: e,
				isSimulated: !0,
				originalEvent: {}
			});
			r ? w.event.trigger(i, null, t) : w.event.dispatch.call(t, i), i.isDefaultPrevented() && n.preventDefault()
		}
	}, w.removeEvent = o.removeEventListener ? function (e, t, n) {
		e.removeEventListener && e.removeEventListener(t, n, !1)
	} : function (e, t, n) {
		var r = "on" + t;
		e.detachEvent && (typeof e[r] === i && (e[r] = null), e.detachEvent(r, n))
	}, w.Event = function (e, t) {
		if (!(this instanceof w.Event)) return new w.Event(e, t);
		e && e.type ? (this.originalEvent = e, this.type = e.type, this.isDefaultPrevented = e.defaultPrevented || e.returnValue === !1 || e.getPreventDefault && e.getPreventDefault() ? it : st) : this.type = e, t && w.extend(this, t), this.timeStamp = e && e.timeStamp || w.now(), this[w.expando] = !0
	}, w.Event.prototype = {
		isDefaultPrevented: st,
		isPropagationStopped: st,
		isImmediatePropagationStopped: st,
		preventDefault: function () {
			var e = this.originalEvent;
			this.isDefaultPrevented = it;
			if (!e) return;
			e.preventDefault ? e.preventDefault() : e.returnValue = !1
		},
		stopPropagation: function () {
			var e = this.originalEvent;
			this.isPropagationStopped = it;
			if (!e) return;
			e.stopPropagation && e.stopPropagation(), e.cancelBubble = !0
		},
		stopImmediatePropagation: function () {
			this.isImmediatePropagationStopped = it, this.stopPropagation()
		}
	}, w.each({
		mouseenter: "mouseover",
		mouseleave: "mouseout"
	}, function (e, t) {
		w.event.special[e] = {
			delegateType: t,
			bindType: t,
			handle: function (e) {
				var n, r = this,
					i = e.relatedTarget,
					s = e.handleObj;
				if (!i || i !== r && !w.contains(r, i)) e.type = s.origType, n = s.handler.apply(this, arguments), e.type = t;
				return n
			}
		}
	}), w.support.submitBubbles || (w.event.special.submit = {
		setup: function () {
			if (w.nodeName(this, "form")) return !1;
			w.event.add(this, "click._submit keypress._submit", function (e) {
				var n = e.target,
					r = w.nodeName(n, "input") || w.nodeName(n, "button") ? n.form : t;
				r && !w._data(r, "submitBubbles") && (w.event.add(r, "submit._submit", function (e) {
					e._submit_bubble = !0
				}), w._data(r, "submitBubbles", !0))
			})
		},
		postDispatch: function (e) {
			e._submit_bubble && (delete e._submit_bubble, this.parentNode && !e.isTrigger && w.event.simulate("submit", this.parentNode, e, !0))
		},
		teardown: function () {
			if (w.nodeName(this, "form")) return !1;
			w.event.remove(this, "._submit")
		}
	}), w.support.changeBubbles || (w.event.special.change = {
		setup: function () {
			if (Z.test(this.nodeName)) {
				if (this.type === "checkbox" || this.type === "radio") w.event.add(this, "propertychange._change", function (e) {
					e.originalEvent.propertyName === "checked" && (this._just_changed = !0)
				}), w.event.add(this, "click._change", function (e) {
					this._just_changed && !e.isTrigger && (this._just_changed = !1), w.event.simulate("change", this, e, !0)
				});
				return !1
			}
			w.event.add(this, "beforeactivate._change", function (e) {
				var t = e.target;
				Z.test(t.nodeName) && !w._data(t, "changeBubbles") && (w.event.add(t, "change._change", function (e) {
					this.parentNode && !e.isSimulated && !e.isTrigger && w.event.simulate("change", this.parentNode, e, !0)
				}), w._data(t, "changeBubbles", !0))
			})
		},
		handle: function (e) {
			var t = e.target;
			if (this !== t || e.isSimulated || e.isTrigger || t.type !== "radio" && t.type !== "checkbox") return e.handleObj.handler.apply(this, arguments)
		},
		teardown: function () {
			return w.event.remove(this, "._change"), !Z.test(this.nodeName)
		}
	}), w.support.focusinBubbles || w.each({
		focus: "focusin",
		blur: "focusout"
	}, function (e, t) {
		var n = 0,
			r = function (e) {
				w.event.simulate(t, e.target, w.event.fix(e), !0)
			};
		w.event.special[t] = {
			setup: function () {
				n++ === 0 && o.addEventListener(e, r, !0)
			},
			teardown: function () {
				--n === 0 && o.removeEventListener(e, r, !0)
			}
		}
	}), w.fn.extend({
		on: function (e, n, r, i, s) {
			var o, u;
			if (typeof e == "object") {
				typeof n != "string" && (r = r || n, n = t);
				for (o in e) this.on(o, n, r, e[o], s);
				return this
			}
			r == null && i == null ? (i = n, r = n = t) : i == null && (typeof n == "string" ? (i = r, r = t) : (i = r, r = n, n = t));
			if (i === !1) i = st;
			else if (!i) return this;
			return s === 1 && (u = i, i = function (e) {
				return w().off(e), u.apply(this, arguments)
			}, i.guid = u.guid || (u.guid = w.guid++)), this.each(function () {
				w.event.add(this, e, i, r, n)
			})
		},
		one: function (e, t, n, r) {
			return this.on(e, t, n, r, 1)
		},
		off: function (e, n, r) {
			var i, s;
			if (e && e.preventDefault && e.handleObj) return i = e.handleObj, w(e.delegateTarget).off(i.namespace ? i.origType + "." + i.namespace : i.origType, i.selector, i.handler), this;
			if (typeof e == "object") {
				for (s in e) this.off(s, n, e[s]);
				return this
			}
			if (n === !1 || typeof n == "function") r = n, n = t;
			return r === !1 && (r = st), this.each(function () {
				w.event.remove(this, e, r, n)
			})
		},
		trigger: function (e, t) {
			return this.each(function () {
				w.event.trigger(e, t, this)
			})
		},
		triggerHandler: function (e, t) {
			var n = this[0];
			if (n) return w.event.trigger(e, t, n, !0)
		}
	});
	var ut = /^.[^:#\[\.,]*$/,
		at = /^(?:parents|prev(?:Until|All))/,
		ft = w.expr.match.needsContext,
		lt = {
			children: !0,
			contents: !0,
			next: !0,
			prev: !0
		};
	w.fn.extend({
		find: function (e) {
			var t, n = [],
				r = this,
				i = r.length;
			if (typeof e != "string") return this.pushStack(w(e).filter(function () {
				for (t = 0; t < i; t++)
					if (w.contains(r[t], this)) return !0
			}));
			for (t = 0; t < i; t++) w.find(e, r[t], n);
			return n = this.pushStack(i > 1 ? w.unique(n) : n), n.selector = this.selector ? this.selector + " " + e : e, n
		},
		has: function (e) {
			var t, n = w(e, this),
				r = n.length;
			return this.filter(function () {
				for (t = 0; t < r; t++)
					if (w.contains(this, n[t])) return !0
			})
		},
		not: function (e) {
			return this.pushStack(ht(this, e || [], !0))
		},
		filter: function (e) {
			return this.pushStack(ht(this, e || [], !1))
		},
		is: function (e) {
			return !!ht(this, typeof e == "string" && ft.test(e) ? w(e) : e || [], !1).length
		},
		closest: function (e, t) {
			var n, r = 0,
				i = this.length,
				s = [],
				o = ft.test(e) || typeof e != "string" ? w(e, t || this.context) : 0;
			for (; r < i; r++)
				for (n = this[r]; n && n !== t; n = n.parentNode)
					if (n.nodeType < 11 && (o ? o.index(n) > -1 : n.nodeType === 1 && w.find.matchesSelector(n, e))) {
						n = s.push(n);
						break
					}
			return this.pushStack(s.length > 1 ? w.unique(s) : s)
		},
		index: function (e) {
			return e ? typeof e == "string" ? w.inArray(this[0], w(e)) : w.inArray(e.jquery ? e[0] : e, this) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
		},
		add: function (e, t) {
			var n = typeof e == "string" ? w(e, t) : w.makeArray(e && e.nodeType ? [e] : e),
				r = w.merge(this.get(), n);
			return this.pushStack(w.unique(r))
		},
		addBack: function (e) {
			return this.add(e == null ? this.prevObject : this.prevObject.filter(e))
		}
	}), w.each({
		parent: function (e) {
			var t = e.parentNode;
			return t && t.nodeType !== 11 ? t : null
		},
		parents: function (e) {
			return w.dir(e, "parentNode")
		},
		parentsUntil: function (e, t, n) {
			return w.dir(e, "parentNode", n)
		},
		next: function (e) {
			return ct(e, "nextSibling")
		},
		prev: function (e) {
			return ct(e, "previousSibling")
		},
		nextAll: function (e) {
			return w.dir(e, "nextSibling")
		},
		prevAll: function (e) {
			return w.dir(e, "previousSibling")
		},
		nextUntil: function (e, t, n) {
			return w.dir(e, "nextSibling", n)
		},
		prevUntil: function (e, t, n) {
			return w.dir(e, "previousSibling", n)
		},
		siblings: function (e) {
			return w.sibling((e.parentNode || {}).firstChild, e)
		},
		children: function (e) {
			return w.sibling(e.firstChild)
		},
		contents: function (e) {
			return w.nodeName(e, "iframe") ? e.contentDocument || e.contentWindow.document : w.merge([], e.childNodes)
		}
	}, function (e, t) {
		w.fn[e] = function (n, r) {
			var i = w.map(this, t, n);
			return e.slice(-5) !== "Until" && (r = n), r && typeof r == "string" && (i = w.filter(r, i)), this.length > 1 && (lt[e] || (i = w.unique(i)), at.test(e) && (i = i.reverse())), this.pushStack(i)
		}
	}), w.extend({
		filter: function (e, t, n) {
			var r = t[0];
			return n && (e = ":not(" + e + ")"), t.length === 1 && r.nodeType === 1 ? w.find.matchesSelector(r, e) ? [r] : [] : w.find.matches(e, w.grep(t, function (e) {
				return e.nodeType === 1
			}))
		},
		dir: function (e, n, r) {
			var i = [],
				s = e[n];
			while (s && s.nodeType !== 9 && (r === t || s.nodeType !== 1 || !w(s).is(r))) s.nodeType === 1 && i.push(s), s = s[n];
			return i
		},
		sibling: function (e, t) {
			var n = [];
			for (; e; e = e.nextSibling) e.nodeType === 1 && e !== t && n.push(e);
			return n
		}
	});
	var dt = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",
		vt = / jQuery\d+="(?:null|\d+)"/g,
		mt = new RegExp("<(?:" + dt + ")[\\s/>]", "i"),
		gt = /^\s+/,
		yt = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
		bt = /<([\w:]+)/,
		wt = /<tbody/i,
		Et = /<|&#?\w+;/,
		St = /<(?:script|style|link)/i,
		xt = /^(?:checkbox|radio)$/i,
		Tt = /checked\s*(?:[^=]|=\s*.checked.)/i,
		Nt = /^$|\/(?:java|ecma)script/i,
		Ct = /^true\/(.*)/,
		kt = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,
		Lt = {
			option: [1, "<select multiple='multiple'>", "</select>"],
			legend: [1, "<fieldset>", "</fieldset>"],
			area: [1, "<map>", "</map>"],
			param: [1, "<object>", "</object>"],
			thead: [1, "<table>", "</table>"],
			tr: [2, "<table><tbody>", "</tbody></table>"],
			col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"],
			td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
			_default: w.support.htmlSerialize ? [0, "", ""] : [1, "X<div>", "</div>"]
		},
		At = pt(o),
		Ot = At.appendChild(o.createElement("div"));
	Lt.optgroup = Lt.option, Lt.tbody = Lt.tfoot = Lt.colgroup = Lt.caption = Lt.thead, Lt.th = Lt.td, w.fn.extend({
		text: function (e) {
			return w.access(this, function (e) {
				return e === t ? w.text(this) : this.empty().append((this[0] && this[0].ownerDocument || o).createTextNode(e))
			}, null, e, arguments.length)
		},
		append: function () {
			return this.domManip(arguments, function (e) {
				if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
					var t = Mt(this, e);
					t.appendChild(e)
				}
			})
		},
		prepend: function () {
			return this.domManip(arguments, function (e) {
				if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
					var t = Mt(this, e);
					t.insertBefore(e, t.firstChild)
				}
			})
		},
		before: function () {
			return this.domManip(arguments, function (e) {
				this.parentNode && this.parentNode.insertBefore(e, this)
			})
		},
		after: function () {
			return this.domManip(arguments, function (e) {
				this.parentNode && this.parentNode.insertBefore(e, this.nextSibling)
			})
		},
		remove: function (e, t) {
			var n, r = e ? w.filter(e, this) : this,
				i = 0;
			for (;
				(n = r[i]) != null; i++) !t && n.nodeType === 1 && w.cleanData(jt(n)), n.parentNode && (t && w.contains(n.ownerDocument, n) && Pt(jt(n, "script")), n.parentNode.removeChild(n));
			return this
		},
		empty: function () {
			var e, t = 0;
			for (;
				(e = this[t]) != null; t++) {
				e.nodeType === 1 && w.cleanData(jt(e, !1));
				while (e.firstChild) e.removeChild(e.firstChild);
				e.options && w.nodeName(e, "select") && (e.options.length = 0)
			}
			return this
		},
		clone: function (e, t) {
			return e = e == null ? !1 : e, t = t == null ? e : t, this.map(function () {
				return w.clone(this, e, t)
			})
		},
		html: function (e) {
			return w.access(this, function (e) {
				var n = this[0] || {},
					r = 0,
					i = this.length;
				if (e === t) return n.nodeType === 1 ? n.innerHTML.replace(vt, "") : t;
				if (typeof e == "string" && !St.test(e) && (w.support.htmlSerialize || !mt.test(e)) && (w.support.leadingWhitespace || !gt.test(e)) && !Lt[(bt.exec(e) || ["", ""])[1].toLowerCase()]) {
					e = e.replace(yt, "<$1></$2>");
					try {
						for (; r < i; r++) n = this[r] || {}, n.nodeType === 1 && (w.cleanData(jt(n, !1)), n.innerHTML = e);
						n = 0
					} catch (s) {}
				}
				n && this.empty().append(e)
			}, null, e, arguments.length)
		},
		replaceWith: function () {
			var e = w.map(this, function (e) {
					return [e.nextSibling, e.parentNode]
				}),
				t = 0;
			return this.domManip(arguments, function (n) {
				var r = e[t++],
					i = e[t++];
				i && (r && r.parentNode !== i && (r = this.nextSibling), w(this).remove(), i.insertBefore(n, r))
			}, !0), t ? this : this.remove()
		},
		detach: function (e) {
			return this.remove(e, !0)
		},
		domManip: function (e, t, n) {
			e = p.apply([], e);
			var r, i, s, o, u, a, f = 0,
				l = this.length,
				c = this,
				h = l - 1,
				d = e[0],
				v = w.isFunction(d);
			if (v || !(l <= 1 || typeof d != "string" || w.support.checkClone || !Tt.test(d))) return this.each(function (r) {
				var i = c.eq(r);
				v && (e[0] = d.call(this, r, i.html())), i.domManip(e, t, n)
			});
			if (l) {
				a = w.buildFragment(e, this[0].ownerDocument, !1, !n && this), r = a.firstChild, a.childNodes.length === 1 && (a = r);
				if (r) {
					o = w.map(jt(a, "script"), _t), s = o.length;
					for (; f < l; f++) i = a, f !== h && (i = w.clone(i, !0, !0), s && w.merge(o, jt(i, "script"))), t.call(this[f], i, f);
					if (s) {
						u = o[o.length - 1].ownerDocument, w.map(o, Dt);
						for (f = 0; f < s; f++) i = o[f], Nt.test(i.type || "") && !w._data(i, "globalEval") && w.contains(u, i) && (i.src ? w._evalUrl(i.src) : w.globalEval((i.text || i.textContent || i.innerHTML || "").replace(kt, "")))
					}
					a = r = null
				}
			}
			return this
		}
	}), w.each({
		appendTo: "append",
		prependTo: "prepend",
		insertBefore: "before",
		insertAfter: "after",
		replaceAll: "replaceWith"
	}, function (e, t) {
		w.fn[e] = function (e) {
			var n, r = 0,
				i = [],
				s = w(e),
				o = s.length - 1;
			for (; r <= o; r++) n = r === o ? this : this.clone(!0), w(s[r])[t](n), d.apply(i, n.get());
			return this.pushStack(i)
		}
	}), w.extend({
		clone: function (e, t, n) {
			var r, i, s, o, u, a = w.contains(e.ownerDocument, e);
			w.support.html5Clone || w.isXMLDoc(e) || !mt.test("<" + e.nodeName + ">") ? s = e.cloneNode(!0) : (Ot.innerHTML = e.outerHTML, Ot.removeChild(s = Ot.firstChild));
			if ((!w.support.noCloneEvent || !w.support.noCloneChecked) && (e.nodeType === 1 || e.nodeType === 11) && !w.isXMLDoc(e)) {
				r = jt(s), u = jt(e);
				for (o = 0;
					(i = u[o]) != null; ++o) r[o] && Bt(i, r[o])
			}
			if (t)
				if (n) {
					u = u || jt(e), r = r || jt(s);
					for (o = 0;
						(i = u[o]) != null; o++) Ht(i, r[o])
				} else Ht(e, s);
			return r = jt(s, "script"), r.length > 0 && Pt(r, !a && jt(e, "script")), r = u = i = null, s
		},
		buildFragment: function (e, t, n, r) {
			var i, s, o, u, a, f, l, c = e.length,
				h = pt(t),
				p = [],
				d = 0;
			for (; d < c; d++) {
				s = e[d];
				if (s || s === 0)
					if (w.type(s) === "object") w.merge(p, s.nodeType ? [s] : s);
					else if (!Et.test(s)) p.push(t.createTextNode(s));
				else {
					u = u || h.appendChild(t.createElement("div")), a = (bt.exec(s) || ["", ""])[1].toLowerCase(), l = Lt[a] || Lt._default, u.innerHTML = l[1] + s.replace(yt, "<$1></$2>") + l[2], i = l[0];
					while (i--) u = u.lastChild;
					!w.support.leadingWhitespace && gt.test(s) && p.push(t.createTextNode(gt.exec(s)[0]));
					if (!w.support.tbody) {
						s = a === "table" && !wt.test(s) ? u.firstChild : l[1] === "<table>" && !wt.test(s) ? u : 0, i = s && s.childNodes.length;
						while (i--) w.nodeName(f = s.childNodes[i], "tbody") && !f.childNodes.length && s.removeChild(f)
					}
					w.merge(p, u.childNodes), u.textContent = "";
					while (u.firstChild) u.removeChild(u.firstChild);
					u = h.lastChild
				}
			}
			u && h.removeChild(u), w.support.appendChecked || w.grep(jt(p, "input"), Ft), d = 0;
			while (s = p[d++]) {
				if (r && w.inArray(s, r) !== -1) continue;
				o = w.contains(s.ownerDocument, s), u = jt(h.appendChild(s), "script"), o && Pt(u);
				if (n) {
					i = 0;
					while (s = u[i++]) Nt.test(s.type || "") && n.push(s)
				}
			}
			return u = null, h
		},
		cleanData: function (e, t) {
			var n, r, s, o, u = 0,
				a = w.expando,
				f = w.cache,
				l = w.support.deleteExpando,
				h = w.event.special;
			for (;
				(n = e[u]) != null; u++)
				if (t || w.acceptData(n)) {
					s = n[a], o = s && f[s];
					if (o) {
						if (o.events)
							for (r in o.events) h[r] ? w.event.remove(n, r) : w.removeEvent(n, r, o.handle);
						f[s] && (delete f[s], l ? delete n[a] : typeof n.removeAttribute !== i ? n.removeAttribute(a) : n[a] = null, c.push(s))
					}
				}
		},
		_evalUrl: function (e) {
			return w.ajax({
				url: e,
				type: "GET",
				dataType: "script",
				async: !1,
				global: !1,
				"throws": !0
			})
		}
	}), w.fn.extend({
		wrapAll: function (e) {
			if (w.isFunction(e)) return this.each(function (t) {
				w(this).wrapAll(e.call(this, t))
			});
			if (this[0]) {
				var t = w(e, this[0].ownerDocument).eq(0).clone(!0);
				this[0].parentNode && t.insertBefore(this[0]), t.map(function () {
					var e = this;
					while (e.firstChild && e.firstChild.nodeType === 1) e = e.firstChild;
					return e
				}).append(this)
			}
			return this
		},
		wrapInner: function (e) {
			return w.isFunction(e) ? this.each(function (t) {
				w(this).wrapInner(e.call(this, t))
			}) : this.each(function () {
				var t = w(this),
					n = t.contents();
				n.length ? n.wrapAll(e) : t.append(e)
			})
		},
		wrap: function (e) {
			var t = w.isFunction(e);
			return this.each(function (n) {
				w(this).wrapAll(t ? e.call(this, n) : e)
			})
		},
		unwrap: function () {
			return this.parent().each(function () {
				w.nodeName(this, "body") || w(this).replaceWith(this.childNodes)
			}).end()
		}
	});
	var It, qt, Rt, Ut = /alpha\([^)]*\)/i,
		zt = /opacity\s*=\s*([^)]*)/,
		Wt = /^(top|right|bottom|left)$/,
		Xt = /^(none|table(?!-c[ea]).+)/,
		Vt = /^margin/,
		$t = new RegExp("^(" + E + ")(.*)$", "i"),
		Jt = new RegExp("^(" + E + ")(?!px)[a-z%]+$", "i"),
		Kt = new RegExp("^([+-])=(" + E + ")", "i"),
		Qt = {
			BODY: "block"
		},
		Gt = {
			position: "absolute",
			visibility: "hidden",
			display: "block"
		},
		Yt = {
			letterSpacing: 0,
			fontWeight: 400
		},
		Zt = ["Top", "Right", "Bottom", "Left"],
		en = ["Webkit", "O", "Moz", "ms"];
	w.fn.extend({
		css: function (e, n) {
			return w.access(this, function (e, n, r) {
				var i, s, o = {},
					u = 0;
				if (w.isArray(n)) {
					s = qt(e), i = n.length;
					for (; u < i; u++) o[n[u]] = w.css(e, n[u], !1, s);
					return o
				}
				return r !== t ? w.style(e, n, r) : w.css(e, n)
			}, e, n, arguments.length > 1)
		},
		show: function () {
			return rn(this, !0)
		},
		hide: function () {
			return rn(this)
		},
		toggle: function (e) {
			return typeof e == "boolean" ? e ? this.show() : this.hide() : this.each(function () {
				nn(this) ? w(this).show() : w(this).hide()
			})
		}
	}), w.extend({
		cssHooks: {
			opacity: {
				get: function (e, t) {
					if (t) {
						var n = Rt(e, "opacity");
						return n === "" ? "1" : n
					}
				}
			}
		},
		cssNumber: {
			columnCount: !0,
			fillOpacity: !0,
			fontWeight: !0,
			lineHeight: !0,
			opacity: !0,
			order: !0,
			orphans: !0,
			widows: !0,
			zIndex: !0,
			zoom: !0
		},
		cssProps: {
			"float": w.support.cssFloat ? "cssFloat" : "styleFloat"
		},
		style: function (e, n, r, i) {
			if (!e || e.nodeType === 3 || e.nodeType === 8 || !e.style) return;
			var s, o, u, a = w.camelCase(n),
				f = e.style;
			n = w.cssProps[a] || (w.cssProps[a] = tn(f, a)), u = w.cssHooks[n] || w.cssHooks[a];
			if (r === t) return u && "get" in u && (s = u.get(e, !1, i)) !== t ? s : f[n];
			o = typeof r, o === "string" && (s = Kt.exec(r)) && (r = (s[1] + 1) * s[2] + parseFloat(w.css(e, n)), o = "number");
			if (r == null || o === "number" && isNaN(r)) return;
			o === "number" && !w.cssNumber[a] && (r += "px"), !w.support.clearCloneStyle && r === "" && n.indexOf("background") === 0 && (f[n] = "inherit");
			if (!u || !("set" in u) || (r = u.set(e, r, i)) !== t) try {
				f[n] = r
			} catch (l) {}
		},
		css: function (e, n, r, i) {
			var s, o, u, a = w.camelCase(n);
			return n = w.cssProps[a] || (w.cssProps[a] = tn(e.style, a)), u = w.cssHooks[n] || w.cssHooks[a], u && "get" in u && (o = u.get(e, !0, r)), o === t && (o = Rt(e, n, i)), o === "normal" && n in Yt && (o = Yt[n]), r === "" || r ? (s = parseFloat(o), r === !0 || w.isNumeric(s) ? s || 0 : o) : o
		}
	}), e.getComputedStyle ? (qt = function (t) {
		return e.getComputedStyle(t, null)
	}, Rt = function (e, n, r) {
		var i, s, o, u = r || qt(e),
			a = u ? u.getPropertyValue(n) || u[n] : t,
			f = e.style;
		return u && (a === "" && !w.contains(e.ownerDocument, e) && (a = w.style(e, n)), Jt.test(a) && Vt.test(n) && (i = f.width, s = f.minWidth, o = f.maxWidth, f.minWidth = f.maxWidth = f.width = a, a = u.width, f.width = i, f.minWidth = s, f.maxWidth = o)), a
	}) : o.documentElement.currentStyle && (qt = function (e) {
		return e.currentStyle
	}, Rt = function (e, n, r) {
		var i, s, o, u = r || qt(e),
			a = u ? u[n] : t,
			f = e.style;
		return a == null && f && f[n] && (a = f[n]), Jt.test(a) && !Wt.test(n) && (i = f.left, s = e.runtimeStyle, o = s && s.left, o && (s.left = e.currentStyle.left), f.left = n === "fontSize" ? "1em" : a, a = f.pixelLeft + "px", f.left = i, o && (s.left = o)), a === "" ? "auto" : a
	}), w.each(["height", "width"], function (e, t) {
		w.cssHooks[t] = {
			get: function (e, n, r) {
				if (n) return e.offsetWidth === 0 && Xt.test(w.css(e, "display")) ? w.swap(e, Gt, function () {
					return un(e, t, r)
				}) : un(e, t, r)
			},
			set: function (e, n, r) {
				var i = r && qt(e);
				return sn(e, n, r ? on(e, t, r, w.support.boxSizing && w.css(e, "boxSizing", !1, i) === "border-box", i) : 0)
			}
		}
	}), w.support.opacity || (w.cssHooks.opacity = {
		get: function (e, t) {
			return zt.test((t && e.currentStyle ? e.currentStyle.filter : e.style.filter) || "") ? .01 * parseFloat(RegExp.$1) + "" : t ? "1" : ""
		},
		set: function (e, t) {
			var n = e.style,
				r = e.currentStyle,
				i = w.isNumeric(t) ? "alpha(opacity=" + t * 100 + ")" : "",
				s = r && r.filter || n.filter || "";
			n.zoom = 1;
			if ((t >= 1 || t === "") && w.trim(s.replace(Ut, "")) === "" && n.removeAttribute) {
				n.removeAttribute("filter");
				if (t === "" || r && !r.filter) return
			}
			n.filter = Ut.test(s) ? s.replace(Ut, i) : s + " " + i
		}
	}), w(function () {
		w.support.reliableMarginRight || (w.cssHooks.marginRight = {
			get: function (e, t) {
				if (t) return w.swap(e, {
					display: "inline-block"
				}, Rt, [e, "marginRight"])
			}
		}), !w.support.pixelPosition && w.fn.position && w.each(["top", "left"], function (e, t) {
			w.cssHooks[t] = {
				get: function (e, n) {
					if (n) return n = Rt(e, t), Jt.test(n) ? w(e).position()[t] + "px" : n
				}
			}
		})
	}), w.expr && w.expr.filters && (w.expr.filters.hidden = function (e) {
		return e.offsetWidth <= 0 && e.offsetHeight <= 0 || !w.support.reliableHiddenOffsets && (e.style && e.style.display || w.css(e, "display")) === "none"
	}, w.expr.filters.visible = function (e) {
		return !w.expr.filters.hidden(e)
	}), w.each({
		margin: "",
		padding: "",
		border: "Width"
	}, function (e, t) {
		w.cssHooks[e + t] = {
			expand: function (n) {
				var r = 0,
					i = {},
					s = typeof n == "string" ? n.split(" ") : [n];
				for (; r < 4; r++) i[e + Zt[r] + t] = s[r] || s[r - 2] || s[0];
				return i
			}
		}, Vt.test(e) || (w.cssHooks[e + t].set = sn)
	});
	var ln = /%20/g,
		cn = /\[\]$/,
		hn = /\r?\n/g,
		pn = /^(?:submit|button|image|reset|file)$/i,
		dn = /^(?:input|select|textarea|keygen)/i;
	w.fn.extend({
		serialize: function () {
			return w.param(this.serializeArray())
		},
		serializeArray: function () {
			return this.map(function () {
				var e = w.prop(this, "elements");
				return e ? w.makeArray(e) : this
			}).filter(function () {
				var e = this.type;
				return this.name && !w(this).is(":disabled") && dn.test(this.nodeName) && !pn.test(e) && (this.checked || !xt.test(e))
			}).map(function (e, t) {
				var n = w(this).val();
				return n == null ? null : w.isArray(n) ? w.map(n, function (e) {
					return {
						name: t.name,
						value: e.replace(hn, "\r\n")
					}
				}) : {
					name: t.name,
					value: n.replace(hn, "\r\n")
				}
			}).get()
		}
	}), w.param = function (e, n) {
		var r, i = [],
			s = function (e, t) {
				t = w.isFunction(t) ? t() : t == null ? "" : t, i[i.length] = encodeURIComponent(e) + "=" + encodeURIComponent(t)
			};
		n === t && (n = w.ajaxSettings && w.ajaxSettings.traditional);
		if (w.isArray(e) || e.jquery && !w.isPlainObject(e)) w.each(e, function () {
			s(this.name, this.value)
		});
		else
			for (r in e) vn(r, e[r], n, s);
		return i.join("&").replace(ln, "+")
	}, w.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "), function (e, t) {
		w.fn[t] = function (e, n) {
			return arguments.length > 0 ? this.on(t, null, e, n) : this.trigger(t)
		}
	}), w.fn.extend({
		hover: function (e, t) {
			return this.mouseenter(e).mouseleave(t || e)
		},
		bind: function (e, t, n) {
			return this.on(e, null, t, n)
		},
		unbind: function (e, t) {
			return this.off(e, null, t)
		},
		delegate: function (e, t, n, r) {
			return this.on(t, e, n, r)
		},
		undelegate: function (e, t, n) {
			return arguments.length === 1 ? this.off(e, "**") : this.off(t, e || "**", n)
		}
	});
	var mn, gn, yn = w.now(),
		bn = /\?/,
		wn = /#.*$/,
		En = /([?&])_=[^&]*/,
		Sn = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg,
		xn = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
		Tn = /^(?:GET|HEAD)$/,
		Nn = /^\/\//,
		Cn = /^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,
		kn = w.fn.load,
		Ln = {},
		An = {},
		On = "*/".concat("*");
	try {
		gn = s.href
	} catch (Mn) {
		gn = o.createElement("a"), gn.href = "", gn = gn.href
	}
	mn = Cn.exec(gn.toLowerCase()) || [], w.fn.load = function (e, n, r) {
		if (typeof e != "string" && kn) return kn.apply(this, arguments);
		var i, s, o, u = this,
			a = e.indexOf(" ");
		return a >= 0 && (i = e.slice(a, e.length), e = e.slice(0, a)), w.isFunction(n) ? (r = n, n = t) : n && typeof n == "object" && (o = "POST"), u.length > 0 && w.ajax({
			url: e,
			type: o,
			dataType: "html",
			data: n
		}).done(function (e) {
			s = arguments, u.html(i ? w("<div>").append(w.parseHTML(e)).find(i) : e)
		}).complete(r && function (e, t) {
			u.each(r, s || [e.responseText, t, e])
		}), this
	}, w.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (e, t) {
		w.fn[t] = function (e) {
			return this.on(t, e)
		}
	}), w.extend({
		active: 0,
		lastModified: {},
		etag: {},
		ajaxSettings: {
			url: gn,
			type: "GET",
			isLocal: xn.test(mn[1]),
			global: !0,
			processData: !0,
			async: !0,
			contentType: "application/x-www-form-urlencoded; charset=UTF-8",
			accepts: {
				"*": On,
				text: "text/plain",
				html: "text/html",
				xml: "application/xml, text/xml",
				json: "application/json, text/javascript"
			},
			contents: {
				xml: /xml/,
				html: /html/,
				json: /json/
			},
			responseFields: {
				xml: "responseXML",
				text: "responseText",
				json: "responseJSON"
			},
			converters: {
				"* text": String,
				"text html": !0,
				"text json": w.parseJSON,
				"text xml": w.parseXML
			},
			flatOptions: {
				url: !0,
				context: !0
			}
		},
		ajaxSetup: function (e, t) {
			return t ? Pn(Pn(e, w.ajaxSettings), t) : Pn(w.ajaxSettings, e)
		},
		ajaxPrefilter: _n(Ln),
		ajaxTransport: _n(An),
		ajax: function (e, n) {
			function N(e, n, r, i) {
				var l, g, y, E, S, T = n;
				if (b === 2) return;
				b = 2, u && clearTimeout(u), f = t, o = i || "", x.readyState = e > 0 ? 4 : 0, l = e >= 200 && e < 300 || e === 304, r && (E = Hn(c, x, r)), E = Bn(c, E, x, l);
				if (l) c.ifModified && (S = x.getResponseHeader("Last-Modified"), S && (w.lastModified[s] = S), S = x.getResponseHeader("etag"), S && (w.etag[s] = S)), e === 204 || c.type === "HEAD" ? T = "nocontent" : e === 304 ? T = "notmodified" : (T = E.state, g = E.data, y = E.error, l = !y);
				else {
					y = T;
					if (e || !T) T = "error", e < 0 && (e = 0)
				}
				x.status = e, x.statusText = (n || T) + "", l ? d.resolveWith(h, [g, T, x]) : d.rejectWith(h, [x, T, y]), x.statusCode(m), m = t, a && p.trigger(l ? "ajaxSuccess" : "ajaxError", [x, c, l ? g : y]), v.fireWith(h, [x, T]), a && (p.trigger("ajaxComplete", [x, c]), --w.active || w.event.trigger("ajaxStop"))
			}
			typeof e == "object" && (n = e, e = t), n = n || {};
			var r, i, s, o, u, a, f, l, c = w.ajaxSetup({}, n),
				h = c.context || c,
				p = c.context && (h.nodeType || h.jquery) ? w(h) : w.event,
				d = w.Deferred(),
				v = w.Callbacks("once memory"),
				m = c.statusCode || {},
				g = {},
				y = {},
				b = 0,
				E = "canceled",
				x = {
					readyState: 0,
					getResponseHeader: function (e) {
						var t;
						if (b === 2) {
							if (!l) {
								l = {};
								while (t = Sn.exec(o)) l[t[1].toLowerCase()] = t[2]
							}
							t = l[e.toLowerCase()]
						}
						return t == null ? null : t
					},
					getAllResponseHeaders: function () {
						return b === 2 ? o : null
					},
					setRequestHeader: function (e, t) {
						var n = e.toLowerCase();
						return b || (e = y[n] = y[n] || e, g[e] = t), this
					},
					overrideMimeType: function (e) {
						return b || (c.mimeType = e), this
					},
					statusCode: function (e) {
						var t;
						if (e)
							if (b < 2)
								for (t in e) m[t] = [m[t], e[t]];
							else x.always(e[x.status]);
						return this
					},
					abort: function (e) {
						var t = e || E;
						return f && f.abort(t), N(0, t), this
					}
				};
			d.promise(x).complete = v.add, x.success = x.done, x.error = x.fail, c.url = ((e || c.url || gn) + "").replace(wn, "").replace(Nn, mn[1] + "//"), c.type = n.method || n.type || c.method || c.type, c.dataTypes = w.trim(c.dataType || "*").toLowerCase().match(S) || [""], c.crossDomain == null && (r = Cn.exec(c.url.toLowerCase()), c.crossDomain = !(!r || r[1] === mn[1] && r[2] === mn[2] && (r[3] || (r[1] === "http:" ? "80" : "443")) === (mn[3] || (mn[1] === "http:" ? "80" : "443")))), c.data && c.processData && typeof c.data != "string" && (c.data = w.param(c.data, c.traditional)), Dn(Ln, c, n, x);
			if (b === 2) return x;
			a = c.global, a && w.active++ === 0 && w.event.trigger("ajaxStart"), c.type = c.type.toUpperCase(), c.hasContent = !Tn.test(c.type), s = c.url, c.hasContent || (c.data && (s = c.url += (bn.test(s) ? "&" : "?") + c.data, delete c.data), c.cache === !1 && (c.url = En.test(s) ? s.replace(En, "$1_=" + yn++) : s + (bn.test(s) ? "&" : "?") + "_=" + yn++)), c.ifModified && (w.lastModified[s] && x.setRequestHeader("If-Modified-Since", w.lastModified[s]), w.etag[s] && x.setRequestHeader("If-None-Match", w.etag[s])), (c.data && c.hasContent && c.contentType !== !1 || n.contentType) && x.setRequestHeader("Content-Type", c.contentType), x.setRequestHeader("Accept", c.dataTypes[0] && c.accepts[c.dataTypes[0]] ? c.accepts[c.dataTypes[0]] + (c.dataTypes[0] !== "*" ? ", " + On + "; q=0.01" : "") : c.accepts["*"]);
			for (i in c.headers) x.setRequestHeader(i, c.headers[i]);
			if (!c.beforeSend || c.beforeSend.call(h, x, c) !== !1 && b !== 2) {
				E = "abort";
				for (i in {
						success: 1,
						error: 1,
						complete: 1
					}) x[i](c[i]);
				f = Dn(An, c, n, x);
				if (!f) N(-1, "No Transport");
				else {
					x.readyState = 1, a && p.trigger("ajaxSend", [x, c]), c.async && c.timeout > 0 && (u = setTimeout(function () {
						x.abort("timeout")
					}, c.timeout));
					try {
						b = 1, f.send(g, N)
					} catch (T) {
						if (!(b < 2)) throw T;
						N(-1, T)
					}
				}
				return x
			}
			return x.abort()
		},
		getJSON: function (e, t, n) {
			return w.get(e, t, n, "json")
		},
		getScript: function (e, n) {
			return w.get(e, t, n, "script")
		}
	}), w.each(["get", "post"], function (e, n) {
		w[n] = function (e, r, i, s) {
			return w.isFunction(r) && (s = s || i, i = r, r = t), w.ajax({
				url: e,
				type: n,
				dataType: s,
				data: r,
				success: i
			})
		}
	}), w.ajaxSetup({
		accepts: {
			script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
		},
		contents: {
			script: /(?:java|ecma)script/
		},
		converters: {
			"text script": function (e) {
				return w.globalEval(e), e
			}
		}
	}), w.ajaxPrefilter("script", function (e) {
		e.cache === t && (e.cache = !1), e.crossDomain && (e.type = "GET", e.global = !1)
	}), w.ajaxTransport("script", function (e) {
		if (e.crossDomain) {
			var n, r = o.head || w("head")[0] || o.documentElement;
			return {
				send: function (t, i) {
					n = o.createElement("script"), n.async = !0, e.scriptCharset && (n.charset = e.scriptCharset), n.src = e.url, n.onload = n.onreadystatechange = function (e, t) {
						if (t || !n.readyState || /loaded|complete/.test(n.readyState)) n.onload = n.onreadystatechange = null, n.parentNode && n.parentNode.removeChild(n), n = null, t || i(200, "success")
					}, r.insertBefore(n, r.firstChild)
				},
				abort: function () {
					n && n.onload(t, !0)
				}
			}
		}
	});
	var jn = [],
		Fn = /(=)\?(?=&|$)|\?\?/;
	w.ajaxSetup({
		jsonp: "callback",
		jsonpCallback: function () {
			var e = jn.pop() || w.expando + "_" + yn++;
			return this[e] = !0, e
		}
	}), w.ajaxPrefilter("json jsonp", function (n, r, i) {
		var s, o, u, a = n.jsonp !== !1 && (Fn.test(n.url) ? "url" : typeof n.data == "string" && !(n.contentType || "").indexOf("application/x-www-form-urlencoded") && Fn.test(n.data) && "data");
		if (a || n.dataTypes[0] === "jsonp") return s = n.jsonpCallback = w.isFunction(n.jsonpCallback) ? n.jsonpCallback() : n.jsonpCallback, a ? n[a] = n[a].replace(Fn, "$1" + s) : n.jsonp !== !1 && (n.url += (bn.test(n.url) ? "&" : "?") + n.jsonp + "=" + s), n.converters["script json"] = function () {
			return u || w.error(s + " was not called"), u[0]
		}, n.dataTypes[0] = "json", o = e[s], e[s] = function () {
			u = arguments
		}, i.always(function () {
			e[s] = o, n[s] && (n.jsonpCallback = r.jsonpCallback, jn.push(s)), u && w.isFunction(o) && o(u[0]), u = o = t
		}), "script"
	});
	var In, qn, Rn = 0,
		Un = e.ActiveXObject && function () {
			var e;
			for (e in In) In[e](t, !0)
		};
	w.ajaxSettings.xhr = e.ActiveXObject ? function () {
		return !this.isLocal && zn() || Wn()
	} : zn, qn = w.ajaxSettings.xhr(), w.support.cors = !!qn && "withCredentials" in qn, qn = w.support.ajax = !!qn, qn && w.ajaxTransport(function (n) {
		if (!n.crossDomain || w.support.cors) {
			var r;
			return {
				send: function (i, s) {
					var o, u, a = n.xhr();
					n.username ? a.open(n.type, n.url, n.async, n.username, n.password) : a.open(n.type, n.url, n.async);
					if (n.xhrFields)
						for (u in n.xhrFields) a[u] = n.xhrFields[u];
					n.mimeType && a.overrideMimeType && a.overrideMimeType(n.mimeType), !n.crossDomain && !i["X-Requested-With"] && (i["X-Requested-With"] = "XMLHttpRequest");
					try {
						for (u in i) a.setRequestHeader(u, i[u])
					} catch (f) {}
					a.send(n.hasContent && n.data || null), r = function (e, i) {
						var u, f, l, c;
						try {
							if (r && (i || a.readyState === 4)) {
								r = t, o && (a.onreadystatechange = w.noop, Un && delete In[o]);
								if (i) a.readyState !== 4 && a.abort();
								else {
									c = {}, u = a.status, f = a.getAllResponseHeaders(), typeof a.responseText == "string" && (c.text = a.responseText);
									try {
										l = a.statusText
									} catch (h) {
										l = ""
									}!u && n.isLocal && !n.crossDomain ? u = c.text ? 200 : 404 : u === 1223 && (u = 204)
								}
							}
						} catch (p) {
							i || s(-1, p)
						}
						c && s(u, l, c, f)
					}, n.async ? a.readyState === 4 ? setTimeout(r) : (o = ++Rn, Un && (In || (In = {}, w(e).unload(Un)), In[o] = r), a.onreadystatechange = r) : r()
				},
				abort: function () {
					r && r(t, !0)
				}
			}
		}
	});
	var Xn, Vn, $n = /^(?:toggle|show|hide)$/,
		Jn = new RegExp("^(?:([+-])=|)(" + E + ")([a-z%]*)$", "i"),
		Kn = /queueHooks$/,
		Qn = [nr],
		Gn = {
			"*": [function (e, t) {
				var n = this.createTween(e, t),
					r = n.cur(),
					i = Jn.exec(t),
					s = i && i[3] || (w.cssNumber[e] ? "" : "px"),
					o = (w.cssNumber[e] || s !== "px" && +r) && Jn.exec(w.css(n.elem, e)),
					u = 1,
					a = 20;
				if (o && o[3] !== s) {
					s = s || o[3], i = i || [], o = +r || 1;
					do u = u || ".5", o /= u, w.style(n.elem, e, o + s); while (u !== (u = n.cur() / r) && u !== 1 && --a)
				}
				return i && (o = n.start = +o || +r || 0, n.unit = s, n.end = i[1] ? o + (i[1] + 1) * i[2] : +i[2]), n
			}]
		};
	w.Animation = w.extend(er, {
		tweener: function (e, t) {
			w.isFunction(e) ? (t = e, e = ["*"]) : e = e.split(" ");
			var n, r = 0,
				i = e.length;
			for (; r < i; r++) n = e[r], Gn[n] = Gn[n] || [], Gn[n].unshift(t)
		},
		prefilter: function (e, t) {
			t ? Qn.unshift(e) : Qn.push(e)
		}
	}), w.Tween = rr, rr.prototype = {
		constructor: rr,
		init: function (e, t, n, r, i, s) {
			this.elem = e, this.prop = n, this.easing = i || "swing", this.options = t, this.start = this.now = this.cur(), this.end = r, this.unit = s || (w.cssNumber[n] ? "" : "px")
		},
		cur: function () {
			var e = rr.propHooks[this.prop];
			return e && e.get ? e.get(this) : rr.propHooks._default.get(this)
		},
		run: function (e) {
			var t, n = rr.propHooks[this.prop];
			return this.options.duration ? this.pos = t = w.easing[this.easing](e, this.options.duration * e, 0, 1, this.options.duration) : this.pos = t = e, this.now = (this.end - this.start) * t + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), n && n.set ? n.set(this) : rr.propHooks._default.set(this), this
		}
	}, rr.prototype.init.prototype = rr.prototype, rr.propHooks = {
		_default: {
			get: function (e) {
				var t;
				return e.elem[e.prop] == null || !!e.elem.style && e.elem.style[e.prop] != null ? (t = w.css(e.elem, e.prop, ""), !t || t === "auto" ? 0 : t) : e.elem[e.prop]
			},
			set: function (e) {
				w.fx.step[e.prop] ? w.fx.step[e.prop](e) : e.elem.style && (e.elem.style[w.cssProps[e.prop]] != null || w.cssHooks[e.prop]) ? w.style(e.elem, e.prop, e.now + e.unit) : e.elem[e.prop] = e.now
			}
		}
	}, rr.propHooks.scrollTop = rr.propHooks.scrollLeft = {
		set: function (e) {
			e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now)
		}
	}, w.each(["toggle", "show", "hide"], function (e, t) {
		var n = w.fn[t];
		w.fn[t] = function (e, r, i) {
			return e == null || typeof e == "boolean" ? n.apply(this, arguments) : this.animate(ir(t, !0), e, r, i)
		}
	}), w.fn.extend({
		fadeTo: function (e, t, n, r) {
			return this.filter(nn).css("opacity", 0).show().end().animate({
				opacity: t
			}, e, n, r)
		},
		animate: function (e, t, n, r) {
			var i = w.isEmptyObject(e),
				s = w.speed(t, n, r),
				o = function () {
					var t = er(this, w.extend({}, e), s);
					(i || w._data(this, "finish")) && t.stop(!0)
				};
			return o.finish = o, i || s.queue === !1 ? this.each(o) : this.queue(s.queue, o)
		},
		stop: function (e, n, r) {
			var i = function (e) {
				var t = e.stop;
				delete e.stop, t(r)
			};
			return typeof e != "string" && (r = n, n = e, e = t), n && e !== !1 && this.queue(e || "fx", []), this.each(function () {
				var t = !0,
					n = e != null && e + "queueHooks",
					s = w.timers,
					o = w._data(this);
				if (n) o[n] && o[n].stop && i(o[n]);
				else
					for (n in o) o[n] && o[n].stop && Kn.test(n) && i(o[n]);
				for (n = s.length; n--;) s[n].elem === this && (e == null || s[n].queue === e) && (s[n].anim.stop(r), t = !1, s.splice(n, 1));
				(t || !r) && w.dequeue(this, e)
			})
		},
		finish: function (e) {
			return e !== !1 && (e = e || "fx"), this.each(function () {
				var t, n = w._data(this),
					r = n[e + "queue"],
					i = n[e + "queueHooks"],
					s = w.timers,
					o = r ? r.length : 0;
				n.finish = !0, w.queue(this, e, []), i && i.stop && i.stop.call(this, !0);
				for (t = s.length; t--;) s[t].elem === this && s[t].queue === e && (s[t].anim.stop(!0), s.splice(t, 1));
				for (t = 0; t < o; t++) r[t] && r[t].finish && r[t].finish.call(this);
				delete n.finish
			})
		}
	}), w.each({
		slideDown: ir("show"),
		slideUp: ir("hide"),
		slideToggle: ir("toggle"),
		fadeIn: {
			opacity: "show"
		},
		fadeOut: {
			opacity: "hide"
		},
		fadeToggle: {
			opacity: "toggle"
		}
	}, function (e, t) {
		w.fn[e] = function (e, n, r) {
			return this.animate(t, e, n, r)
		}
	}), w.speed = function (e, t, n) {
		var r = e && typeof e == "object" ? w.extend({}, e) : {
			complete: n || !n && t || w.isFunction(e) && e,
			duration: e,
			easing: n && t || t && !w.isFunction(t) && t
		};
		r.duration = w.fx.off ? 0 : typeof r.duration == "number" ? r.duration : r.duration in w.fx.speeds ? w.fx.speeds[r.duration] : w.fx.speeds._default;
		if (r.queue == null || r.queue === !0) r.queue = "fx";
		return r.old = r.complete, r.complete = function () {
			w.isFunction(r.old) && r.old.call(this), r.queue && w.dequeue(this, r.queue)
		}, r
	}, w.easing = {
		linear: function (e) {
			return e
		},
		swing: function (e) {
			return .5 - Math.cos(e * Math.PI) / 2
		}
	}, w.timers = [], w.fx = rr.prototype.init, w.fx.tick = function () {
		var e, n = w.timers,
			r = 0;
		Xn = w.now();
		for (; r < n.length; r++) e = n[r], !e() && n[r] === e && n.splice(r--, 1);
		n.length || w.fx.stop(), Xn = t
	}, w.fx.timer = function (e) {
		e() && w.timers.push(e) && w.fx.start()
	}, w.fx.interval = 13, w.fx.start = function () {
		Vn || (Vn = setInterval(w.fx.tick, w.fx.interval))
	}, w.fx.stop = function () {
		clearInterval(Vn), Vn = null
	}, w.fx.speeds = {
		slow: 600,
		fast: 200,
		_default: 400
	}, w.fx.step = {}, w.expr && w.expr.filters && (w.expr.filters.animated = function (e) {
		return w.grep(w.timers, function (t) {
			return e === t.elem
		}).length
	}), w.fn.offset = function (e) {
		if (arguments.length) return e === t ? this : this.each(function (t) {
			w.offset.setOffset(this, e, t)
		});
		var n, r, s = {
				top: 0,
				left: 0
			},
			o = this[0],
			u = o && o.ownerDocument;
		if (!u) return;
		return n = u.documentElement, w.contains(n, o) ? (typeof o.getBoundingClientRect !== i && (s = o.getBoundingClientRect()), r = sr(u), {
			top: s.top + (r.pageYOffset || n.scrollTop) - (n.clientTop || 0),
			left: s.left + (r.pageXOffset || n.scrollLeft) - (n.clientLeft || 0)
		}) : s
	}, w.offset = {
		setOffset: function (e, t, n) {
			var r = w.css(e, "position");
			r === "static" && (e.style.position = "relative");
			var i = w(e),
				s = i.offset(),
				o = w.css(e, "top"),
				u = w.css(e, "left"),
				a = (r === "absolute" || r === "fixed") && w.inArray("auto", [o, u]) > -1,
				f = {},
				l = {},
				c, h;
			a ? (l = i.position(), c = l.top, h = l.left) : (c = parseFloat(o) || 0, h = parseFloat(u) || 0), w.isFunction(t) && (t = t.call(e, n, s)), t.top != null && (f.top = t.top - s.top + c), t.left != null && (f.left = t.left - s.left + h), "using" in t ? t.using.call(e, f) : i.css(f)
		}
	}, w.fn.extend({
		position: function () {
			if (!this[0]) return;
			var e, t, n = {
					top: 0,
					left: 0
				},
				r = this[0];
			return w.css(r, "position") === "fixed" ? t = r.getBoundingClientRect() : (e = this.offsetParent(), t = this.offset(), w.nodeName(e[0], "html") || (n = e.offset()), n.top += w.css(e[0], "borderTopWidth", !0), n.left += w.css(e[0], "borderLeftWidth", !0)), {
				top: t.top - n.top - w.css(r, "marginTop", !0),
				left: t.left - n.left - w.css(r, "marginLeft", !0)
			}
		},
		offsetParent: function () {
			return this.map(function () {
				var e = this.offsetParent || u;
				while (e && !w.nodeName(e, "html") && w.css(e, "position") === "static") e = e.offsetParent;
				return e || u
			})
		}
	}), w.each({
		scrollLeft: "pageXOffset",
		scrollTop: "pageYOffset"
	}, function (e, n) {
		var r = /Y/.test(n);
		w.fn[e] = function (i) {
			return w.access(this, function (e, i, s) {
				var o = sr(e);
				if (s === t) return o ? n in o ? o[n] : o.document.documentElement[i] : e[i];
				o ? o.scrollTo(r ? w(o).scrollLeft() : s, r ? s : w(o).scrollTop()) : e[i] = s
			}, e, i, arguments.length, null)
		}
	}), w.each({
		Height: "height",
		Width: "width"
	}, function (e, n) {
		w.each({
			padding: "inner" + e,
			content: n,
			"": "outer" + e
		}, function (r, i) {
			w.fn[i] = function (i, s) {
				var o = arguments.length && (r || typeof i != "boolean"),
					u = r || (i === !0 || s === !0 ? "margin" : "border");
				return w.access(this, function (n, r, i) {
					var s;
					return w.isWindow(n) ? n.document.documentElement["client" + e] : n.nodeType === 9 ? (s = n.documentElement, Math.max(n.body["scroll" + e], s["scroll" + e], n.body["offset" + e], s["offset" + e], s["client" + e])) : i === t ? w.css(n, r, u) : w.style(n, r, i, u)
				}, n, o ? i : t, o, null)
			}
		})
	}), w.fn.size = function () {
		return this.length
	}, w.fn.andSelf = w.fn.addBack, typeof module == "object" && module && typeof module.exports == "object" ? module.exports = w : (e.jQuery = e.$ = w, typeof define == "function" && define.amd && define("jquery", [], function () {
		return w
	}))
})(window), typeof Array.prototype.forEach != "function" && (Array.prototype.forEach = function (e) {
	for (var t = 0; t < this.length; t++) e.apply(this, [this[t], t, this])
}), String.prototype.trim || (String.prototype.trim = function () {
	return this.replace(/^\s+|\s+$/g, "")
});
if (typeof jQuery == "undefined") throw new Error("Bootstrap's JavaScript requires jQuery"); + function (e) {
	var t = e.fn.jquery.split(" ")[0].split(".");
	if (t[0] < 2 && t[1] < 9 || t[0] == 1 && t[1] == 9 && t[2] < 1) throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher")
}(jQuery), + function (e) {
	"use strict";

	function r(t) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.alert");
			i || r.data("bs.alert", i = new n(this)), typeof t == "string" && i[t].call(r)
		})
	}
	var t = '[data-dismiss="alert"]',
		n = function (n) {
			e(n).on("click", t, this.close)
		};
	n.VERSION = "3.3.1", n.TRANSITION_DURATION = 150, n.prototype.close = function (t) {
		function o() {
			s.detach().trigger("closed.bs.alert").remove()
		}
		var r = e(this),
			i = r.attr("data-target");
		i || (i = r.attr("href"), i = i && i.replace(/.*(?=#[^\s]*$)/, ""));
		var s = e(i);
		t && t.preventDefault(), s.length || (s = r.closest(".alert")), s.trigger(t = e.Event("close.bs.alert"));
		if (t.isDefaultPrevented()) return;
		s.removeClass("in"), e.support.transition && s.hasClass("fade") ? s.one("bsTransitionEnd", o).emulateTransitionEnd(n.TRANSITION_DURATION) : o()
	};
	var i = e.fn.alert;
	e.fn.alert = r, e.fn.alert.Constructor = n, e.fn.alert.noConflict = function () {
		return e.fn.alert = i, this
	}, e(document).on("click.bs.alert.data-api", t, n.prototype.close)
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.button"),
				s = typeof n == "object" && n;
			i || r.data("bs.button", i = new t(this, s)), n == "toggle" ? i.toggle() : n && i.setState(n)
		})
	}
	var t = function (n, r) {
		this.$element = e(n), this.options = e.extend({}, t.DEFAULTS, r), this.isLoading = !1
	};
	t.VERSION = "3.3.1", t.DEFAULTS = {
		loadingText: "loading..."
	}, t.prototype.setState = function (t) {
		var n = "disabled",
			r = this.$element,
			i = r.is("input") ? "val" : "html",
			s = r.data();
		t += "Text", s.resetText == null && r.data("resetText", r[i]()), setTimeout(e.proxy(function () {
			r[i](s[t] == null ? this.options[t] : s[t]), t == "loadingText" ? (this.isLoading = !0, r.addClass(n).attr(n, n)) : this.isLoading && (this.isLoading = !1, r.removeClass(n).removeAttr(n))
		}, this), 0)
	}, t.prototype.toggle = function () {
		var e = !0,
			t = this.$element.closest('[data-toggle="buttons"]');
		if (t.length) {
			var n = this.$element.find("input");
			n.prop("type") == "radio" && (n.prop("checked") && this.$element.hasClass("active") ? e = !1 : t.find(".active").removeClass("active")), e && n.prop("checked", !this.$element.hasClass("active")).trigger("change")
		} else this.$element.attr("aria-pressed", !this.$element.hasClass("active"));
		e && this.$element.toggleClass("active")
	};
	var r = e.fn.button;
	e.fn.button = n, e.fn.button.Constructor = t, e.fn.button.noConflict = function () {
		return e.fn.button = r, this
	}, e(document).on("click.bs.button.data-api", '[data-toggle^="button"]', function (t) {
		var r = e(t.target);
		r.hasClass("btn") || (r = r.closest(".btn")), n.call(r, "toggle"), t.preventDefault()
	}).on("focus.bs.button.data-api blur.bs.button.data-api", '[data-toggle^="button"]', function (t) {
		e(t.target).closest(".btn").toggleClass("focus", /^focus(in)?$/.test(t.type))
	})
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.carousel"),
				s = e.extend({}, t.DEFAULTS, r.data(), typeof n == "object" && n),
				o = typeof n == "string" ? n : s.slide;
			i || r.data("bs.carousel", i = new t(this, s)), typeof n == "number" ? i.to(n) : o ? i[o]() : s.interval && i.pause().cycle()
		})
	}
	var t = function (t, n) {
		this.$element = e(t), this.$indicators = this.$element.find(".carousel-indicators"), this.options = n, this.paused = this.sliding = this.interval = this.$active = this.$items = null, this.options.keyboard && this.$element.on("keydown.bs.carousel", e.proxy(this.keydown, this)), this.options.pause == "hover" && !("ontouchstart" in document.documentElement) && this.$element.on("mouseenter.bs.carousel", e.proxy(this.pause, this)).on("mouseleave.bs.carousel", e.proxy(this.cycle, this))
	};
	t.VERSION = "3.3.1", t.TRANSITION_DURATION = 600, t.DEFAULTS = {
		interval: 5e3,
		pause: "hover",
		wrap: !0,
		keyboard: !0
	}, t.prototype.keydown = function (e) {
		if (/input|textarea/i.test(e.target.tagName)) return;
		switch (e.which) {
		case 37:
			this.prev();
			break;
		case 39:
			this.next();
			break;
		default:
			return
		}
		e.preventDefault()
	}, t.prototype.cycle = function (t) {
		return t || (this.paused = !1), this.interval && clearInterval(this.interval), this.options.interval && !this.paused && (this.interval = setInterval(e.proxy(this.next, this), this.options.interval)), this
	}, t.prototype.getItemIndex = function (e) {
		return this.$items = e.parent().children(".item"), this.$items.index(e || this.$active)
	}, t.prototype.getItemForDirection = function (e, t) {
		var n = e == "prev" ? -1 : 1,
			r = this.getItemIndex(t),
			i = (r + n) % this.$items.length;
		return this.$items.eq(i)
	}, t.prototype.to = function (e) {
		var t = this,
			n = this.getItemIndex(this.$active = this.$element.find(".item.active"));
		if (e > this.$items.length - 1 || e < 0) return;
		return this.sliding ? this.$element.one("slid.bs.carousel", function () {
			t.to(e)
		}) : n == e ? this.pause().cycle() : this.slide(e > n ? "next" : "prev", this.$items.eq(e))
	}, t.prototype.pause = function (t) {
		return t || (this.paused = !0), this.$element.find(".next, .prev").length && e.support.transition && (this.$element.trigger(e.support.transition.end), this.cycle(!0)), this.interval = clearInterval(this.interval), this
	}, t.prototype.next = function () {
		if (this.sliding) return;
		return this.slide("next")
	}, t.prototype.prev = function () {
		if (this.sliding) return;
		return this.slide("prev")
	}, t.prototype.slide = function (n, r) {
		var i = this.$element.find(".item.active"),
			s = r || this.getItemForDirection(n, i),
			o = this.interval,
			u = n == "next" ? "left" : "right",
			a = n == "next" ? "first" : "last",
			f = this;
		if (!s.length) {
			if (!this.options.wrap) return;
			s = this.$element.find(".item")[a]()
		}
		if (s.hasClass("active")) return this.sliding = !1;
		var l = s[0],
			c = e.Event("slide.bs.carousel", {
				relatedTarget: l,
				direction: u
			});
		this.$element.trigger(c);
		if (c.isDefaultPrevented()) return;
		this.sliding = !0, o && this.pause();
		if (this.$indicators.length) {
			this.$indicators.find(".active").removeClass("active");
			var h = e(this.$indicators.children()[this.getItemIndex(s)]);
			h && h.addClass("active")
		}
		var p = e.Event("slid.bs.carousel", {
			relatedTarget: l,
			direction: u
		});
		return e.support.transition && this.$element.hasClass("slide") ? (s.addClass(n), s[0].offsetWidth, i.addClass(u), s.addClass(u), i.one("bsTransitionEnd", function () {
			s.removeClass([n, u].join(" ")).addClass("active"), i.removeClass(["active", u].join(" ")), f.sliding = !1, setTimeout(function () {
				f.$element.trigger(p)
			}, 0)
		}).emulateTransitionEnd(t.TRANSITION_DURATION)) : (i.removeClass("active"), s.addClass("active"), this.sliding = !1, this.$element.trigger(p)), o && this.cycle(), this
	};
	var r = e.fn.carousel;
	e.fn.carousel = n, e.fn.carousel.Constructor = t, e.fn.carousel.noConflict = function () {
		return e.fn.carousel = r, this
	};
	var i = function (t) {
		var r, i = e(this),
			s = e(i.attr("data-target") || (r = i.attr("href")) && r.replace(/.*(?=#[^\s]+$)/, ""));
		if (!s.hasClass("carousel")) return;
		var o = e.extend({}, s.data(), i.data()),
			u = i.attr("data-slide-to");
		u && (o.interval = !1), n.call(s, o), u && s.data("bs.carousel").to(u), t.preventDefault()
	};
	e(document).on("click.bs.carousel.data-api", "[data-slide]", i).on("click.bs.carousel.data-api", "[data-slide-to]", i), e(window).on("load", function () {
		e('[data-ride="carousel"]').each(function () {
			var t = e(this);
			n.call(t, t.data())
		})
	})
}(jQuery), + function (e) {
	"use strict";

	function i(r) {
		if (r && r.which === 3) return;
		e(t).remove(), e(n).each(function () {
			var t = e(this),
				n = s(t),
				i = {
					relatedTarget: this
				};
			if (!n.hasClass("open")) return;
			n.trigger(r = e.Event("hide.bs.dropdown", i));
			if (r.isDefaultPrevented()) return;
			t.attr("aria-expanded", "false"), n.removeClass("open").trigger("hidden.bs.dropdown", i)
		})
	}

	function s(t) {
		var n = t.attr("data-target");
		n || (n = t.attr("href"), n = n && /#[A-Za-z]/.test(n) && n.replace(/.*(?=#[^\s]*$)/, ""));
		var r = n && e(n);
		return r && r.length ? r : t.parent()
	}

	function o(t) {
		return this.each(function () {
			var n = e(this),
				i = n.data("bs.dropdown");
			i || n.data("bs.dropdown", i = new r(this)), typeof t == "string" && i[t].call(n)
		})
	}
	var t = ".dropdown-backdrop",
		n = '[data-toggle="dropdown"]',
		r = function (t) {
			e(t).on("click.bs.dropdown", this.toggle)
		};
	r.VERSION = "3.3.1", r.prototype.toggle = function (t) {
		var n = e(this);
		if (n.is(".disabled, :disabled")) return;
		var r = s(n),
			o = r.hasClass("open");
		i();
		if (!o) {
			"ontouchstart" in document.documentElement && !r.closest(".navbar-nav").length && e('<div class="dropdown-backdrop"/>').insertAfter(e(this)).on("click", i);
			var u = {
				relatedTarget: this
			};
			r.trigger(t = e.Event("show.bs.dropdown", u));
			if (t.isDefaultPrevented()) return;
			n.trigger("focus").attr("aria-expanded", "true"), r.toggleClass("open").trigger("shown.bs.dropdown", u)
		}
		return !1
	}, r.prototype.keydown = function (t) {
		if (!/(38|40|27|32)/.test(t.which) || /input|textarea/i.test(t.target.tagName)) return;
		var r = e(this);
		t.preventDefault(), t.stopPropagation();
		if (r.is(".disabled, :disabled")) return;
		var i = s(r),
			o = i.hasClass("open");
		if (!o && t.which != 27 || o && t.which == 27) return t.which == 27 && i.find(n).trigger("focus"), r.trigger("click");
		var u = " li:not(.divider):visible a",
			a = i.find('[role="menu"]' + u + ', [role="listbox"]' + u);
		if (!a.length) return;
		var f = a.index(t.target);
		t.which == 38 && f > 0 && f--, t.which == 40 && f < a.length - 1 && f++, ~f || (f = 0), a.eq(f).trigger("focus")
	};
	var u = e.fn.dropdown;
	e.fn.dropdown = o, e.fn.dropdown.Constructor = r, e.fn.dropdown.noConflict = function () {
		return e.fn.dropdown = u, this
	}, e(document).on("click.bs.dropdown.data-api", i).on("click.bs.dropdown.data-api", ".dropdown form", function (e) {
		e.stopPropagation()
	}).on("click.bs.dropdown.data-api", n, r.prototype.toggle).on("keydown.bs.dropdown.data-api", n, r.prototype.keydown).on("keydown.bs.dropdown.data-api", '[role="menu"]', r.prototype.keydown).on("keydown.bs.dropdown.data-api", '[role="listbox"]', r.prototype.keydown)
}(jQuery), + function (e) {
	"use strict";

	function n(n, r) {
		return this.each(function () {
			var i = e(this),
				s = i.data("bs.modal"),
				o = e.extend({}, t.DEFAULTS, i.data(), typeof n == "object" && n);
			s || i.data("bs.modal", s = new t(this, o)), typeof n == "string" ? s[n](r) : o.show && s.show(r)
		})
	}
	var t = function (t, n) {
		this.options = n, this.$body = e(document.body), this.$element = e(t), this.$backdrop = this.isShown = null, this.scrollbarWidth = 0, this.options.remote && this.$element.find(".modal-content").load(this.options.remote, e.proxy(function () {
			this.$element.trigger("loaded.bs.modal")
		}, this))
	};
	t.VERSION = "3.3.1", t.TRANSITION_DURATION = 300, t.BACKDROP_TRANSITION_DURATION = 150, t.DEFAULTS = {
		backdrop: !0,
		keyboard: !0,
		show: !0
	}, t.prototype.toggle = function (e) {
		return this.isShown ? this.hide() : this.show(e)
	}, t.prototype.show = function (n) {
		var r = this,
			i = e.Event("show.bs.modal", {
				relatedTarget: n
			});
		this.$element.trigger(i);
		if (this.isShown || i.isDefaultPrevented()) return;
		this.isShown = !0, this.checkScrollbar(), this.setScrollbar(), this.$body.addClass("modal-open"), this.escape(), this.resize(), this.$element.on("click.dismiss.bs.modal", '[data-dismiss="modal"]', e.proxy(this.hide, this)), this.backdrop(function () {
			var i = e.support.transition && r.$element.hasClass("fade");
			r.$element.parent().length || r.$element.appendTo(r.$body), r.$element.show().scrollTop(0), r.options.backdrop && r.adjustBackdrop(), r.adjustDialog(), i && r.$element[0].offsetWidth, r.$element.addClass("in").attr("aria-hidden", !1), r.enforceFocus();
			var s = e.Event("shown.bs.modal", {
				relatedTarget: n
			});
			i ? r.$element.find(".modal-dialog").one("bsTransitionEnd", function () {
				r.$element.trigger("focus").trigger(s)
			}).emulateTransitionEnd(t.TRANSITION_DURATION) : r.$element.trigger("focus").trigger(s)
		})
	}, t.prototype.hide = function (n) {
		n && n.preventDefault(), n = e.Event("hide.bs.modal"), this.$element.trigger(n);
		if (!this.isShown || n.isDefaultPrevented()) return;
		this.isShown = !1, this.escape(), this.resize(), e(document).off("focusin.bs.modal"), this.$element.removeClass("in").attr("aria-hidden", !0).off("click.dismiss.bs.modal"), e.support.transition && this.$element.hasClass("fade") ? this.$element.one("bsTransitionEnd", e.proxy(this.hideModal, this)).emulateTransitionEnd(t.TRANSITION_DURATION) : this.hideModal()
	}, t.prototype.enforceFocus = function () {
		e(document).off("focusin.bs.modal").on("focusin.bs.modal", e.proxy(function (e) {
			this.$element[0] !== e.target && !this.$element.has(e.target).length && this.$element.trigger("focus")
		}, this))
	}, t.prototype.escape = function () {
		this.isShown && this.options.keyboard ? this.$element.on("keydown.dismiss.bs.modal", e.proxy(function (e) {
			e.which == 27 && this.hide()
		}, this)) : this.isShown || this.$element.off("keydown.dismiss.bs.modal")
	}, t.prototype.resize = function () {
		this.isShown ? e(window).on("resize.bs.modal", e.proxy(this.handleUpdate, this)) : e(window).off("resize.bs.modal")
	}, t.prototype.hideModal = function () {
		var e = this;
		this.$element.hide(), this.backdrop(function () {
			e.$body.removeClass("modal-open"), e.resetAdjustments(), e.resetScrollbar(), e.$element.trigger("hidden.bs.modal")
		})
	}, t.prototype.removeBackdrop = function () {
		this.$backdrop && this.$backdrop.remove(), this.$backdrop = null
	}, t.prototype.backdrop = function (n) {
		var r = this,
			i = this.$element.hasClass("fade") ? "fade" : "";
		if (this.isShown && this.options.backdrop) {
			var s = e.support.transition && i;
			this.$backdrop = e('<div class="modal-backdrop ' + i + '" />').prependTo(this.$element).on("click.dismiss.bs.modal", e.proxy(function (e) {
				if (e.target !== e.currentTarget) return;
				this.options.backdrop == "static" ? this.$element[0].focus.call(this.$element[0]) : this.hide.call(this)
			}, this)), s && this.$backdrop[0].offsetWidth, this.$backdrop.addClass("in");
			if (!n) return;
			s ? this.$backdrop.one("bsTransitionEnd", n).emulateTransitionEnd(t.BACKDROP_TRANSITION_DURATION) : n()
		} else if (!this.isShown && this.$backdrop) {
			this.$backdrop.removeClass("in");
			var o = function () {
				r.removeBackdrop(), n && n()
			};
			e.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one("bsTransitionEnd", o).emulateTransitionEnd(t.BACKDROP_TRANSITION_DURATION) : o()
		} else n && n()
	}, t.prototype.handleUpdate = function () {
		this.options.backdrop && this.adjustBackdrop(), this.adjustDialog()
	}, t.prototype.adjustBackdrop = function () {
		this.$backdrop.css("height", 0).css("height", this.$element[0].scrollHeight)
	}, t.prototype.adjustDialog = function () {
		var e = this.$element[0].scrollHeight > document.documentElement.clientHeight;
		this.$element.css({
			paddingLeft: !this.bodyIsOverflowing && e ? this.scrollbarWidth : "",
			paddingRight: this.bodyIsOverflowing && !e ? this.scrollbarWidth : ""
		})
	}, t.prototype.resetAdjustments = function () {
		this.$element.css({
			paddingLeft: "",
			paddingRight: ""
		})
	}, t.prototype.checkScrollbar = function () {
		this.bodyIsOverflowing = document.body.scrollHeight > document.documentElement.clientHeight, this.scrollbarWidth = this.measureScrollbar()
	}, t.prototype.setScrollbar = function () {
		var e = parseInt(this.$body.css("padding-right") || 0, 10);
		this.bodyIsOverflowing && this.$body.css("padding-right", e + this.scrollbarWidth)
	}, t.prototype.resetScrollbar = function () {
		this.$body.css("padding-right", "")
	}, t.prototype.measureScrollbar = function () {
		var e = document.createElement("div");
		e.className = "modal-scrollbar-measure", this.$body.append(e);
		var t = e.offsetWidth - e.clientWidth;
		return this.$body[0].removeChild(e), t
	};
	var r = e.fn.modal;
	e.fn.modal = n, e.fn.modal.Constructor = t, e.fn.modal.noConflict = function () {
		return e.fn.modal = r, this
	}, e(document).on("click.bs.modal.data-api", '[data-toggle="modal"]', function (t) {
		var r = e(this),
			i = r.attr("href"),
			s = e(r.attr("data-target") || i && i.replace(/.*(?=#[^\s]+$)/, "")),
			o = s.data("bs.modal") ? "toggle" : e.extend({
				remote: !/#/.test(i) && i
			}, s.data(), r.data());
		r.is("a") && t.preventDefault(), s.one("show.bs.modal", function (e) {
			if (e.isDefaultPrevented()) return;
			s.one("hidden.bs.modal", function () {
				r.is(":visible") && r.trigger("focus")
			})
		}), n.call(s, o, this)
	})
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.tooltip"),
				s = typeof n == "object" && n,
				o = s && s.selector;
			if (!i && n == "destroy") return;
			o ? (i || r.data("bs.tooltip", i = {}), i[o] || (i[o] = new t(this, s))) : i || r.data("bs.tooltip", i = new t(this, s)), typeof n == "string" && i[n]()
		})
	}
	var t = function (e, t) {
		this.type = this.options = this.enabled = this.timeout = this.hoverState = this.$element = null, this.init("tooltip", e, t)
	};
	t.VERSION = "3.3.1", t.TRANSITION_DURATION = 150, t.DEFAULTS = {
		animation: !0,
		placement: "top",
		selector: !1,
		template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
		trigger: "hover focus",
		title: "",
		delay: 0,
		html: !1,
		container: !1,
		viewport: {
			selector: "body",
			padding: 0
		}
	}, t.prototype.init = function (t, n, r) {
		this.enabled = !0, this.type = t, this.$element = e(n), this.options = this.getOptions(r), this.$viewport = this.options.viewport && e(this.options.viewport.selector || this.options.viewport);
		var i = this.options.trigger.split(" ");
		for (var s = i.length; s--;) {
			var o = i[s];
			if (o == "click") this.$element.on("click." + this.type, this.options.selector, e.proxy(this.toggle, this));
			else if (o != "manual") {
				var u = o == "hover" ? "mouseenter" : "focusin",
					a = o == "hover" ? "mouseleave" : "focusout";
				this.$element.on(u + "." + this.type, this.options.selector, e.proxy(this.enter, this)), this.$element.on(a + "." + this.type, this.options.selector, e.proxy(this.leave, this))
			}
		}
		this.options.selector ? this._options = e.extend({}, this.options, {
			trigger: "manual",
			selector: ""
		}) : this.fixTitle()
	}, t.prototype.getDefaults = function () {
		return t.DEFAULTS
	}, t.prototype.getOptions = function (t) {
		return t = e.extend({}, this.getDefaults(), this.$element.data(), t), t.delay && typeof t.delay == "number" && (t.delay = {
			show: t.delay,
			hide: t.delay
		}), t
	}, t.prototype.getDelegateOptions = function () {
		var t = {},
			n = this.getDefaults();
		return this._options && e.each(this._options, function (e, r) {
			n[e] != r && (t[e] = r)
		}), t
	}, t.prototype.enter = function (t) {
		var n = t instanceof this.constructor ? t : e(t.currentTarget).data("bs." + this.type);
		if (n && n.$tip && n.$tip.is(":visible")) {
			n.hoverState = "in";
			return
		}
		n || (n = new this.constructor(t.currentTarget, this.getDelegateOptions()), e(t.currentTarget).data("bs." + this.type, n)), clearTimeout(n.timeout), n.hoverState = "in";
		if (!n.options.delay || !n.options.delay.show) return n.show();
		n.timeout = setTimeout(function () {
			n.hoverState == "in" && n.show()
		}, n.options.delay.show)
	}, t.prototype.leave = function (t) {
		var n = t instanceof this.constructor ? t : e(t.currentTarget).data("bs." + this.type);
		n || (n = new this.constructor(t.currentTarget, this.getDelegateOptions()), e(t.currentTarget).data("bs." + this.type, n)), clearTimeout(n.timeout), n.hoverState = "out";
		if (!n.options.delay || !n.options.delay.hide) return n.hide();
		n.timeout = setTimeout(function () {
			n.hoverState == "out" && n.hide()
		}, n.options.delay.hide)
	}, t.prototype.show = function () {
		var n = e.Event("show.bs." + this.type);
		if (this.hasContent() && this.enabled) {
			this.$element.trigger(n);
			var r = e.contains(this.$element[0].ownerDocument.documentElement, this.$element[0]);
			if (n.isDefaultPrevented() || !r) return;
			var i = this,
				s = this.tip(),
				o = this.getUID(this.type);
			this.setContent(), s.attr("id", o), this.$element.attr("aria-describedby", o), this.options.animation && s.addClass("fade");
			var u = typeof this.options.placement == "function" ? this.options.placement.call(this, s[0], this.$element[0]) : this.options.placement,
				a = /\s?auto?\s?/i,
				f = a.test(u);
			f && (u = u.replace(a, "") || "top"), s.detach().css({
				top: 0,
				left: 0,
				display: "block"
			}).addClass(u).data("bs." + this.type, this), this.options.container ? s.appendTo(this.options.container) : s.insertAfter(this.$element);
			var l = this.getPosition(),
				c = s[0].offsetWidth,
				h = s[0].offsetHeight;
			if (f) {
				var p = u,
					d = this.options.container ? e(this.options.container) : this.$element.parent(),
					v = this.getPosition(d);
				u = u == "bottom" && l.bottom + h > v.bottom ? "top" : u == "top" && l.top - h < v.top ? "bottom" : u == "right" && l.right + c > v.width ? "left" : u == "left" && l.left - c < v.left ? "right" : u, s.removeClass(p).addClass(u)
			}
			var m = this.getCalculatedOffset(u, l, c, h);
			this.applyPlacement(m, u);
			var g = function () {
				var e = i.hoverState;
				i.$element.trigger("shown.bs." + i.type), i.hoverState = null, e == "out" && i.leave(i)
			};
			e.support.transition && this.$tip.hasClass("fade") ? s.one("bsTransitionEnd", g).emulateTransitionEnd(t.TRANSITION_DURATION) : g()
		}
	}, t.prototype.applyPlacement = function (t, n) {
		var r = this.tip(),
			i = r[0].offsetWidth,
			s = r[0].offsetHeight,
			o = parseInt(r.css("margin-top"), 10),
			u = parseInt(r.css("margin-left"), 10);
		isNaN(o) && (o = 0), isNaN(u) && (u = 0), t.top = t.top + o, t.left = t.left + u, e.offset.setOffset(r[0], e.extend({
			using: function (e) {
				r.css({
					top: Math.round(e.top),
					left: Math.round(e.left)
				})
			}
		}, t), 0), r.addClass("in");
		var a = r[0].offsetWidth,
			f = r[0].offsetHeight;
		n == "top" && f != s && (t.top = t.top + s - f);
		var l = this.getViewportAdjustedDelta(n, t, a, f);
		l.left ? t.left += l.left : t.top += l.top;
		var c = /top|bottom/.test(n),
			h = c ? l.left * 2 - i + a : l.top * 2 - s + f,
			p = c ? "offsetWidth" : "offsetHeight";
		r.offset(t), this.replaceArrow(h, r[0][p], c)
	}, t.prototype.replaceArrow = function (e, t, n) {
		this.arrow().css(n ? "left" : "top", 50 * (1 - e / t) + "%").css(n ? "top" : "left", "")
	}, t.prototype.setContent = function () {
		var e = this.tip(),
			t = this.getTitle();
		e.find(".tooltip-inner")[this.options.html ? "html" : "text"](t), e.removeClass("fade in top bottom left right")
	}, t.prototype.hide = function (n) {
		function o() {
			r.hoverState != "in" && i.detach(), r.$element.removeAttr("aria-describedby").trigger("hidden.bs." + r.type), n && n()
		}
		var r = this,
			i = this.tip(),
			s = e.Event("hide.bs." + this.type);
		this.$element.trigger(s);
		if (s.isDefaultPrevented()) return;
		return i.removeClass("in"), e.support.transition && this.$tip.hasClass("fade") ? i.one("bsTransitionEnd", o).emulateTransitionEnd(t.TRANSITION_DURATION) : o(), this.hoverState = null, this
	}, t.prototype.fixTitle = function () {
		var e = this.$element;
		(e.attr("title") || typeof e.attr("data-original-title") != "string") && e.attr("data-original-title", e.attr("title") || "").attr("title", "")
	}, t.prototype.hasContent = function () {
		return this.getTitle()
	}, t.prototype.getPosition = function (t) {
		t = t || this.$element;
		var n = t[0],
			r = n.tagName == "BODY",
			i = n.getBoundingClientRect();
		i.width == null && (i = e.extend({}, i, {
			width: i.right - i.left,
			height: i.bottom - i.top
		}));
		var s = r ? {
				top: 0,
				left: 0
			} : t.offset(),
			o = {
				scroll: r ? document.documentElement.scrollTop || document.body.scrollTop : t.scrollTop()
			},
			u = r ? {
				width: e(window).width(),
				height: e(window).height()
			} : null;
		return e.extend({}, i, o, u, s)
	}, t.prototype.getCalculatedOffset = function (e, t, n, r) {
		return e == "bottom" ? {
			top: t.top + t.height,
			left: t.left + t.width / 2 - n / 2
		} : e == "top" ? {
			top: t.top - r,
			left: t.left + t.width / 2 - n / 2
		} : e == "left" ? {
			top: t.top + t.height / 2 - r / 2,
			left: t.left - n
		} : {
			top: t.top + t.height / 2 - r / 2,
			left: t.left + t.width
		}
	}, t.prototype.getViewportAdjustedDelta = function (e, t, n, r) {
		var i = {
			top: 0,
			left: 0
		};
		if (!this.$viewport) return i;
		var s = this.options.viewport && this.options.viewport.padding || 0,
			o = this.getPosition(this.$viewport);
		if (/right|left/.test(e)) {
			var u = t.top - s - o.scroll,
				a = t.top + s - o.scroll + r;
			u < o.top ? i.top = o.top - u : a > o.top + o.height && (i.top = o.top + o.height - a)
		} else {
			var f = t.left - s,
				l = t.left + s + n;
			f < o.left ? i.left = o.left - f : l > o.width && (i.left = o.left + o.width - l)
		}
		return i
	}, t.prototype.getTitle = function () {
		var e, t = this.$element,
			n = this.options;
		return e = t.attr("data-original-title") || (typeof n.title == "function" ? n.title.call(t[0]) : n.title), e
	}, t.prototype.getUID = function (e) {
		do e += ~~(Math.random() * 1e6); while (document.getElementById(e));
		return e
	}, t.prototype.tip = function () {
		return this.$tip = this.$tip || e(this.options.template)
	}, t.prototype.arrow = function () {
		return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
	}, t.prototype.enable = function () {
		this.enabled = !0
	}, t.prototype.disable = function () {
		this.enabled = !1
	}, t.prototype.toggleEnabled = function () {
		this.enabled = !this.enabled
	}, t.prototype.toggle = function (t) {
		var n = this;
		t && (n = e(t.currentTarget).data("bs." + this.type), n || (n = new this.constructor(t.currentTarget, this.getDelegateOptions()), e(t.currentTarget).data("bs." + this.type, n))), n.tip().hasClass("in") ? n.leave(n) : n.enter(n)
	}, t.prototype.destroy = function () {
		var e = this;
		clearTimeout(this.timeout), this.hide(function () {
			e.$element.off("." + e.type).removeData("bs." + e.type)
		})
	};
	var r = e.fn.tooltip;
	e.fn.tooltip = n, e.fn.tooltip.Constructor = t, e.fn.tooltip.noConflict = function () {
		return e.fn.tooltip = r, this
	}
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.popover"),
				s = typeof n == "object" && n,
				o = s && s.selector;
			if (!i && n == "destroy") return;
			o ? (i || r.data("bs.popover", i = {}), i[o] || (i[o] = new t(this, s))) : i || r.data("bs.popover", i = new t(this, s)), typeof n == "string" && i[n]()
		})
	}
	var t = function (e, t) {
		this.init("popover", e, t)
	};
	if (!e.fn.tooltip) throw new Error("Popover requires tooltip.js");
	t.VERSION = "3.3.1", t.DEFAULTS = e.extend({}, e.fn.tooltip.Constructor.DEFAULTS, {
		placement: "right",
		trigger: "click",
		content: "",
		template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
	}), t.prototype = e.extend({}, e.fn.tooltip.Constructor.prototype), t.prototype.constructor = t, t.prototype.getDefaults = function () {
		return t.DEFAULTS
	}, t.prototype.setContent = function () {
		var e = this.tip(),
			t = this.getTitle(),
			n = this.getContent();
		e.find(".popover-title")[this.options.html ? "html" : "text"](t), e.find(".popover-content").children().detach().end()[this.options.html ? typeof n == "string" ? "html" : "append" : "text"](n), e.removeClass("fade top bottom left right in"), e.find(".popover-title").html() || e.find(".popover-title").hide()
	}, t.prototype.hasContent = function () {
		return this.getTitle() || this.getContent()
	}, t.prototype.getContent = function () {
		var e = this.$element,
			t = this.options;
		return e.attr("data-content") || (typeof t.content == "function" ? t.content.call(e[0]) : t.content)
	}, t.prototype.arrow = function () {
		return this.$arrow = this.$arrow || this.tip().find(".arrow")
	}, t.prototype.tip = function () {
		return this.$tip || (this.$tip = e(this.options.template)), this.$tip
	};
	var r = e.fn.popover;
	e.fn.popover = n, e.fn.popover.Constructor = t, e.fn.popover.noConflict = function () {
		return e.fn.popover = r, this
	}
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.tab");
			i || r.data("bs.tab", i = new t(this)), typeof n == "string" && i[n]()
		})
	}
	var t = function (t) {
		this.element = e(t)
	};
	t.VERSION = "3.3.1", t.TRANSITION_DURATION = 150, t.prototype.show = function () {
		var t = this.element,
			n = t.closest("ul:not(.dropdown-menu)"),
			r = t.data("target");
		r || (r = t.attr("href"), r = r && r.replace(/.*(?=#[^\s]*$)/, ""));
		if (t.parent("li").hasClass("active")) return;
		var i = n.find(".active:last a"),
			s = e.Event("hide.bs.tab", {
				relatedTarget: t[0]
			}),
			o = e.Event("show.bs.tab", {
				relatedTarget: i[0]
			});
		i.trigger(s), t.trigger(o);
		if (o.isDefaultPrevented() || s.isDefaultPrevented()) return;
		var u = e(r);
		this.activate(t.closest("li"), n), this.activate(u, u.parent(), function () {
			i.trigger({
				type: "hidden.bs.tab",
				relatedTarget: t[0]
			}), t.trigger({
				type: "shown.bs.tab",
				relatedTarget: i[0]
			})
		})
	}, t.prototype.activate = function (n, r, i) {
		function u() {
			s.removeClass("active").find("> .dropdown-menu > .active").removeClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded", !1), n.addClass("active").find('[data-toggle="tab"]').attr("aria-expanded", !0), o ? (n[0].offsetWidth, n.addClass("in")) : n.removeClass("fade"), n.parent(".dropdown-menu") && n.closest("li.dropdown").addClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded", !0), i && i()
		}
		var s = r.find("> .active"),
			o = i && e.support.transition && (s.length && s.hasClass("fade") || !!r.find("> .fade").length);
		s.length && o ? s.one("bsTransitionEnd", u).emulateTransitionEnd(t.TRANSITION_DURATION) : u(), s.removeClass("in")
	};
	var r = e.fn.tab;
	e.fn.tab = n, e.fn.tab.Constructor = t, e.fn.tab.noConflict = function () {
		return e.fn.tab = r, this
	};
	var i = function (t) {
		t.preventDefault(), n.call(e(this), "show")
	};
	e(document).on("click.bs.tab.data-api", '[data-toggle="tab"]', i).on("click.bs.tab.data-api", '[data-toggle="pill"]', i)
}(jQuery), + function (e) {
	"use strict";

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.affix"),
				s = typeof n == "object" && n;
			i || r.data("bs.affix", i = new t(this, s)), typeof n == "string" && i[n]()
		})
	}
	var t = function (n, r) {
		this.options = e.extend({}, t.DEFAULTS, r), this.$target = e(this.options.target).on("scroll.bs.affix.data-api", e.proxy(this.checkPosition, this)).on("click.bs.affix.data-api", e.proxy(this.checkPositionWithEventLoop, this)), this.$element = e(n), this.affixed = this.unpin = this.pinnedOffset = null, this.checkPosition()
	};
	t.VERSION = "3.3.1", t.RESET = "affix affix-top affix-bottom", t.DEFAULTS = {
		offset: 0,
		target: window
	}, t.prototype.getState = function (e, t, n, r) {
		var i = this.$target.scrollTop(),
			s = this.$element.offset(),
			o = this.$target.height();
		if (n != null && this.affixed == "top") return i < n ? "top" : !1;
		if (this.affixed == "bottom") return n != null ? i + this.unpin <= s.top ? !1 : "bottom" : i + o <= e - r ? !1 : "bottom";
		var u = this.affixed == null,
			a = u ? i : s.top,
			f = u ? o : t;
		return n != null && a <= n ? "top" : r != null && a + f >= e - r ? "bottom" : !1
	}, t.prototype.getPinnedOffset = function () {
		if (this.pinnedOffset) return this.pinnedOffset;
		this.$element.removeClass(t.RESET).addClass("affix");
		var e = this.$target.scrollTop(),
			n = this.$element.offset();
		return this.pinnedOffset = n.top - e
	}, t.prototype.checkPositionWithEventLoop = function () {
		setTimeout(e.proxy(this.checkPosition, this), 1)
	}, t.prototype.checkPosition = function () {
		if (!this.$element.is(":visible")) return;
		var n = this.$element.height(),
			r = this.options.offset,
			i = r.top,
			s = r.bottom,
			o = e("body").height();
		typeof r != "object" && (s = i = r), typeof i == "function" && (i = r.top(this.$element)), typeof s == "function" && (s = r.bottom(this.$element));
		var u = this.getState(o, n, i, s);
		if (this.affixed != u) {
			this.unpin != null && this.$element.css("top", "");
			var a = "affix" + (u ? "-" + u : ""),
				f = e.Event(a + ".bs.affix");
			this.$element.trigger(f);
			if (f.isDefaultPrevented()) return;
			this.affixed = u, this.unpin = u == "bottom" ? this.getPinnedOffset() : null, this.$element.removeClass(t.RESET).addClass(a).trigger(a.replace("affix", "affixed") + ".bs.affix")
		}
		u == "bottom" && this.$element.offset({
			top: o - n - s
		})
	};
	var r = e.fn.affix;
	e.fn.affix = n, e.fn.affix.Constructor = t, e.fn.affix.noConflict = function () {
		return e.fn.affix = r, this
	}, e(window).on("load", function () {
		e('[data-spy="affix"]').each(function () {
			var t = e(this),
				r = t.data();
			r.offset = r.offset || {}, r.offsetBottom != null && (r.offset.bottom = r.offsetBottom), r.offsetTop != null && (r.offset.top = r.offsetTop), n.call(t, r)
		})
	})
}(jQuery), + function (e) {
	"use strict";

	function n(t) {
		var n, r = t.attr("data-target") || (n = t.attr("href")) && n.replace(/.*(?=#[^\s]+$)/, "");
		return e(r)
	}

	function r(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.collapse"),
				s = e.extend({}, t.DEFAULTS, r.data(), typeof n == "object" && n);
			!i && s.toggle && n == "show" && (s.toggle = !1), i || r.data("bs.collapse", i = new t(this, s)), typeof n == "string" && i[n]()
		})
	}
	var t = function (n, r) {
		this.$element = e(n), this.options = e.extend({}, t.DEFAULTS, r), this.$trigger = e(this.options.trigger).filter('[href="#' + n.id + '"], [data-target="#' + n.id + '"]'), this.transitioning = null, this.options.parent ? this.$parent = this.getParent() : this.addAriaAndCollapsedClass(this.$element, this.$trigger), this.options.toggle && this.toggle()
	};
	t.VERSION = "3.3.1", t.TRANSITION_DURATION = 350, t.DEFAULTS = {
		toggle: !0,
		trigger: '[data-toggle="collapse"]'
	}, t.prototype.dimension = function () {
		var e = this.$element.hasClass("width");
		return e ? "width" : "height"
	}, t.prototype.show = function () {
		if (this.transitioning || this.$element.hasClass("in")) return;
		var n, i = this.$parent && this.$parent.find("> .panel").children(".in, .collapsing");
		if (i && i.length) {
			n = i.data("bs.collapse");
			if (n && n.transitioning) return
		}
		var s = e.Event("show.bs.collapse");
		this.$element.trigger(s);
		if (s.isDefaultPrevented()) return;
		i && i.length && (r.call(i, "hide"), n || i.data("bs.collapse", null));
		var o = this.dimension();
		this.$element.removeClass("collapse").addClass("collapsing")[o](0).attr("aria-expanded", !0), this.$trigger.removeClass("collapsed").attr("aria-expanded", !0), this.transitioning = 1;
		var u = function () {
			this.$element.removeClass("collapsing").addClass("collapse in")[o](""), this.transitioning = 0, this.$element.trigger("shown.bs.collapse")
		};
		if (!e.support.transition) return u.call(this);
		var a = e.camelCase(["scroll", o].join("-"));
		this.$element.one("bsTransitionEnd", e.proxy(u, this)).emulateTransitionEnd(t.TRANSITION_DURATION)[o](this.$element[0][a])
	}, t.prototype.hide = function () {
		if (this.transitioning || !this.$element.hasClass("in")) return;
		var n = e.Event("hide.bs.collapse");
		this.$element.trigger(n);
		if (n.isDefaultPrevented()) return;
		var r = this.dimension();
		this.$element[r](this.$element[r]())[0].offsetHeight, this.$element.addClass("collapsing").removeClass("collapse in").attr("aria-expanded", !1), this.$trigger.addClass("collapsed").attr("aria-expanded", !1), this.transitioning = 1;
		var i = function () {
			this.transitioning = 0, this.$element.removeClass("collapsing").addClass("collapse").trigger("hidden.bs.collapse")
		};
		if (!e.support.transition) return i.call(this);
		this.$element[r](0).one("bsTransitionEnd", e.proxy(i, this)).emulateTransitionEnd(t.TRANSITION_DURATION)
	}, t.prototype.toggle = function () {
		this[this.$element.hasClass("in") ? "hide" : "show"]()
	}, t.prototype.getParent = function () {
		return e(this.options.parent).find('[data-toggle="collapse"][data-parent="' + this.options.parent + '"]').each(e.proxy(function (t, r) {
			var i = e(r);
			this.addAriaAndCollapsedClass(n(i), i)
		}, this)).end()
	}, t.prototype.addAriaAndCollapsedClass = function (e, t) {
		var n = e.hasClass("in");
		e.attr("aria-expanded", n), t.toggleClass("collapsed", !n).attr("aria-expanded", n)
	};
	var i = e.fn.collapse;
	e.fn.collapse = r, e.fn.collapse.Constructor = t, e.fn.collapse.noConflict = function () {
		return e.fn.collapse = i, this
	}, e(document).on("click.bs.collapse.data-api", '[data-toggle="collapse"]', function (t) {
		var i = e(this);
		i.attr("data-target") || t.preventDefault();
		var s = n(i),
			o = s.data("bs.collapse"),
			u = o ? "toggle" : e.extend({}, i.data(), {
				trigger: this
			});
		r.call(s, u)
	})
}(jQuery), + function (e) {
	"use strict";

	function t(n, r) {
		var i = e.proxy(this.process, this);
		this.$body = e("body"), this.$scrollElement = e(n).is("body") ? e(window) : e(n), this.options = e.extend({}, t.DEFAULTS, r), this.selector = (this.options.target || "") + " .nav li > a", this.offsets = [], this.targets = [], this.activeTarget = null, this.scrollHeight = 0, this.$scrollElement.on("scroll.bs.scrollspy", i), this.refresh(), this.process()
	}

	function n(n) {
		return this.each(function () {
			var r = e(this),
				i = r.data("bs.scrollspy"),
				s = typeof n == "object" && n;
			i || r.data("bs.scrollspy", i = new t(this, s)), typeof n == "string" && i[n]()
		})
	}
	t.VERSION = "3.3.1", t.DEFAULTS = {
		offset: 10
	}, t.prototype.getScrollHeight = function () {
		return this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight)
	}, t.prototype.refresh = function () {
		var t = "offset",
			n = 0;
		e.isWindow(this.$scrollElement[0]) || (t = "position", n = this.$scrollElement.scrollTop()), this.offsets = [], this.targets = [], this.scrollHeight = this.getScrollHeight();
		var r = this;
		this.$body.find(this.selector).map(function () {
			var r = e(this),
				i = r.data("target") || r.attr("href"),
				s = /^#./.test(i) && e(i);
			return s && s.length && s.is(":visible") && [[s[t]().top + n, i]] || null
		}).sort(function (e, t) {
			return e[0] - t[0]
		}).each(function () {
			r.offsets.push(this[0]), r.targets.push(this[1])
		})
	}, t.prototype.process = function () {
		var e = this.$scrollElement.scrollTop() + this.options.offset,
			t = this.getScrollHeight(),
			n = this.options.offset + t - this.$scrollElement.height(),
			r = this.offsets,
			i = this.targets,
			s = this.activeTarget,
			o;
		this.scrollHeight != t && this.refresh();
		if (e >= n) return s != (o = i[i.length - 1]) && this.activate(o);
		if (s && e < r[0]) return this.activeTarget = null, this.clear();
		for (o = r.length; o--;) s != i[o] && e >= r[o] && (!r[o + 1] || e <= r[o + 1]) && this.activate(i[o])
	}, t.prototype.activate = function (t) {
		this.activeTarget = t, this.clear();
		var n = this.selector + '[data-target="' + t + '"],' + this.selector + '[href="' + t + '"]',
			r = e(n).parents("li").addClass("active");
		r.parent(".dropdown-menu").length && (r = r.closest("li.dropdown").addClass("active")), r.trigger("activate.bs.scrollspy")
	}, t.prototype.clear = function () {
		e(this.selector).parentsUntil(this.options.target, ".active").removeClass("active")
	};
	var r = e.fn.scrollspy;
	e.fn.scrollspy = n, e.fn.scrollspy.Constructor = t, e.fn.scrollspy.noConflict = function () {
		return e.fn.scrollspy = r, this
	}, e(window).on("load.bs.scrollspy.data-api", function () {
		e('[data-spy="scroll"]').each(function () {
			var t = e(this);
			n.call(t, t.data())
		})
	})
}(jQuery), + function (e) {
	"use strict";

	function t() {
		var e = document.createElement("bootstrap"),
			t = {
				WebkitTransition: "webkitTransitionEnd",
				MozTransition: "transitionend",
				OTransition: "oTransitionEnd otransitionend",
				transition: "transitionend"
			};
		for (var n in t)
			if (e.style[n] !== undefined) return {
				end: t[n]
			};
		return !1
	}
	e.fn.emulateTransitionEnd = function (t) {
		var n = !1,
			r = this;
		e(this).one("bsTransitionEnd", function () {
			n = !0
		});
		var i = function () {
			n || e(r).trigger(e.support.transition.end)
		};
		return setTimeout(i, t), this
	}, e(function () {
		e.support.transition = t();
		if (!e.support.transition) return;
		e.event.special.bsTransitionEnd = {
			bindType: e.support.transition.end,
			delegateType: e.support.transition.end,
			handle: function (t) {
				if (e(t.target).is(this)) return t.handleObj.handler.apply(this, arguments)
			}
		}
	})
}(jQuery),
function (e, t, n) {
	"use strict";
	var r, i;
	e.uaMatch = function (e) {
		e = e.toLowerCase();
		var t = /(opr)[\/]([\w.]+)/.exec(e) || /(chrome)[ \/]([\w.]+)/.exec(e) || /(version)[ \/]([\w.]+).*(safari)[ \/]([\w.]+)/.exec(e) || /(webkit)[ \/]([\w.]+)/.exec(e) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(e) || /(msie) ([\w.]+)/.exec(e) || e.indexOf("trident") >= 0 && /(rv)(?::| )([\w.]+)/.exec(e) || e.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(e) || [],
			n = /(ipad)/.exec(e) || /(iphone)/.exec(e) || /(android)/.exec(e) || /(windows phone)/.exec(e) || /(win)/.exec(e) || /(mac)/.exec(e) || /(linux)/.exec(e) || /(cros)/i.exec(e) || [];
		return {
			browser: t[3] || t[1] || "",
			version: t[2] || "0",
			platform: n[0] || ""
		}
	}, r = e.uaMatch(t.navigator.userAgent), i = {}, r.browser && (i[r.browser] = !0, i.version = r.version, i.versionNumber = parseInt(r.version)), r.platform && (i[r.platform] = !0);
	if (i.android || i.ipad || i.iphone || i["windows phone"]) i.mobile = !0;
	if (i.cros || i.mac || i.linux || i.win) i.desktop = !0;
	if (i.chrome || i.opr || i.safari) i.webkit = !0;
	if (i.rv) {
		var s = "msie";
		r.browser = s, i[s] = !0
	}
	if (i.opr) {
		var o = "opera";
		r.browser = o, i[o] = !0
	}
	if (i.safari && i.android) {
		var u = "android";
		r.browser = u, i[u] = !0
	}
	i.name = r.browser, i.platform = r.platform, e.browser = i
}(jQuery, window), String.prototype.format || (String.prototype.format = function () {
		var e = arguments;
		return this.replace(/{(\d+)}/g, function (t, n) {
			return typeof e[n] != "undefined" ? e[n] : t
		})
	}),
	function (e) {
		e.fn.dialog = function (t) {
			var n = this,
				r = e(n),
				i = e(document.body),
				s = r.closest(".dialog"),
				o = "dialog-parent",
				u = arguments[1],
				a = arguments[2],
				f = function () {
					var t = '<div class="dialog modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close">&times;</button><h4 class="modal-title"></h4></div><div class="modal-body"></div><div class="modal-footer"></div></div></div></div>';
					s = e(t), e(document.body).append(s), s.find(".modal-body").append(r)
				},
				l = function (r) {
					var i = (r || t || {}).buttons || {},
						o = s.find(".modal-footer");
					o.empty();
					var u = i.constructor == Array;
					for (var a in i) {
						var f = i[a],
							l = "",
							c = "",
							h = "btn-default",
							p = "";
						if (f.constructor == Object) l = f.id, c = f.text, h = f["class"] || f.classed || h, p = f.click;
						else {
							if (!!u || f.constructor != Function) continue;
							c = a, p = f
						}
						$button = e('<button type="button" class="btn">').addClass(h).html(c), l && $button.attr("id", l), p && function (e) {
							$button.click(function () {
								e.call(n)
							})
						}(p), o.append($button)
					}
					o.data("buttons", i)
				},
				c = function () {
					s.modal("show")
				},
				h = function (e) {
					s.modal("hide").one("hidden.bs.modal", function () {
						e && (r.data(o).append(r), s.remove())
					})
				};
			if (t.constructor == Object) {
				!r.data(o) && r.data(o, r.parent()), s.size() < 1 && f(), l(), e(".modal-title", s).html(t.title || "");
				var p = e(".modal-dialog", s).addClass(t.dialogClass || "");
				e(".modal-header .close", s).click(function () {
					var e = t.onClose || h;
					e.call(n)
				}), (t["class"] || t.classed) && s.addClass(t["class"] || t.classed), t.autoOpen === !1 && (t.show = !1), t.width && p.width(t.width), t.height && p.height(t.height), s.modal(t)
			}
			t == "destroy" && h(!0), t == "close" && h(), t == "open" && c();
			if (t == "option" && u == "buttons") {
				if (!a) return s.find(".modal-footer").data("buttons");
				l({
					buttons: a
				}), c()
			}
			return n
		}
	}(jQuery), $.messager = function () {
		var e = function (e, t) {
				var n = $.messager.model;
				arguments.length < 2 && (t = e || "", e = "&nbsp;"), $("<div>" + t + "</div>").dialog({
					title: e,
					onClose: function () {
						$(this).dialog("destroy")
					},
					buttons: [{
						text: n.ok.text,
						classed: n.ok.classed || "btn-success",
						click: function () {
							$(this).dialog("destroy")
						}
					}]
				})
			},
			t = function (e, t, n) {
				var r = $.messager.model;
				$("<div>" + t + "</div>").dialog({
					title: e,
					onClose: function () {
						$(this).dialog("destroy")
					},
					buttons: [{
						text: r.ok.text,
						classed: r.ok.classed || "btn-success",
						click: function () {
							$(this).dialog("destroy"), n && n()
						}
					}, {
						text: r.cancel.text,
						classed: r.cancel.classed || "btn-danger",
						click: function () {
							$(this).dialog("destroy")
						}
					}]
				})
			},
			n = '<div class="dialog modal fade msg-popup"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-body text-center"></div></div></div></div>',
			r = $(".dialog.msg-popup"),
			i = function (e) {
				r.size() || (r = $(n), $("body").append(r)), r.find(".modal-body").html(e), r.modal({
					show: !0,
					backdrop: !1
				}), setTimeout(function () {
					r.modal("hide")
				}, 2e3)
			};
		return {
			alert: e,
			popup: i,
			confirm: t
		}
	}(), $.messager.model = {
		ok: {
			text: "OK",
			classed: "btn-success"
		},
		cancel: {
			text: "Cancel",
			classed: "btn-danger"
		}
	},
	function (e) {
		e.fn.datagrid = function (t, n) {
			var r = this,
				i = e(r),
				s = i.data("config") || {},
				o = i.data("rows") || [],
				u = s.selectedClass || "success",
				a = s.singleSelect,
				f = function (t) {
					var n = s.selectChange,
						r = s.edit,
						f = function (t) {
							var r = e(this),
								s = r.hasClass(u),
								f = e("tbody tr", i).index(r),
								l = o[f] || {};
							a && e("tbody tr", i).removeClass(u), r.toggleClass(u), n && n(!s, f, l, r)
						};
					(n || typeof a != "undefined") && t.click(f);
					var l = function (t) {
						var n = e(this),
							r = n.closest("tr"),
							s = e("tbody tr", i).index(r),
							u = o[s] || {},
							a = n.attr("name");
						a && (u[a] = n.val())
					};
					r && t.find("input").keyup(l)
				},
				l = function (e, t) {
					var n = "<tr>";
					for (var r = 0, i = e[0].length; r < i; r++) {
						var o = e[0][r],
							u = o.formatter,
							a = o.field,
							f = o.tip,
							l = t[a],
							c = o.maxlength,
							h = o.readonly;
						typeof l == "undefined" && (l = ""), s.edit && (c = c ? ' maxlength="{0}"'.format(o.maxlength) : "", h = h ? ' readonly="readonly"' : "", l = '<input name="{0}" value="{1}" class="form-control"{2}{3}/>'.format(o.field, l, c, h)), l = u ? u(t[a], t) : l, n = n + "<td>" + l + "</td>"
					}
					return n += "</tr>", n
				},
				c = function (t) {
					if (!n) return;
					var r = s.columns,
						o = n.rows || n;
					if (!r) return;
					var u = "<tbody>";
					if (o)
						for (var a = 0, c = o.length; a < c; a++) u += l(r, o[a]);
					u += "</tbody>", e("tbody", i).remove(), i.data("rows", o).append(u), s.edit && i.addClass("edit"), f(e("tbody tr", i))
				},
				h = function () {
					if (n && typeof n.index != "undefined") return [n.index];
					var t = [];
					return i.find("tbody tr").each(function (n) {
						var r = e(this);
						r.hasClass(u) && t.push(n)
					}), t
				};
			if (t && t.constructor == Object) {
				var p = t.columns;
				if (p) {
					e("thead", i).size() < 1 && i.append("<thead></thead>");
					var d = "<tr>";
					for (var v = 0, m = p[0].length; v < m; v++) {
						var g = p[0][v];
						d += "<th>" + (g.title || "") + "</th>"
					}
					d += "</tr>", i.data("config", t), e("thead", i).html(d)
				}
			}
			t == "loadData" && c();
			if (t == "getData") return o;
			if (t == "getConfig") return s;
			if (t == "getColumns") return s.columns;
			if (t == "selectRow") {
				if (typeof a == "undefined") return;
				typeof n == "number" ? (a && i.datagrid("unselectRow"), e("tbody tr", i).eq(n).addClass(u)) : a || e("tbody tr", i).addClass(u)
			}
			t == "unselectRow" && (typeof n != "undefined" ? e("tbody tr", i).eq(n).removeClass(u) : e("tbody tr", i).removeClass(u));
			if (t == "updateRow") {
				var y = h(),
					b = n.row,
					p = s.columns;
				for (var v = 0, m = y.length; v < m; v++) {
					var w = y[v];
					o && (b = e.extend(o[w], b));
					var E = e(l(p, b, s));
					typeof n.index == "undefined" && E.addClass(u), e("tbody tr", i).eq(w).after(E).remove(), f(E)
				}
			}
			if (t == "getSelections") {
				var S = [];
				return e("tbody tr", i).each(function (t) {
					e(this).hasClass(u) && S.push(o[t])
				}), S
			}
			if (t == "getSelectedIndex") return h();
			if (t == "insertRow") {
				var x = h()[0],
					b = n.row;
				if (typeof x == "undefined" || x < 0) x = o.length;
				if (!s || !b) return i;
				var T = e("tbody tr", i),
					E = e(l(s.columns, b, s)),
					N = T.eq(x);
				f(E), N.size() ? N.before(E) : e("tbody", i).append(E), o.splice(x, 0, b)
			}
			if (t == "deleteRow") {
				var y = typeof n == "number" ? [n] : h();
				for (var v = y.length - 1; v > -1; v--) {
					var x = y[v];
					e("tbody tr", i).eq(x).remove(), o.splice(x, 1)
				}
			}
			return r
		}
	}(jQuery),
	function (e) {
		e.fn.tree = function (t, n) {
			var r = this,
				i = e(r),
				s = Array.prototype.push,
				o = "glyphicon-file",
				u = "glyphicon-folder-open",
				a = "glyphicon-folder-close",
				f = function (e, t, n) {
					var r = [];
					!t && r.push('<ul style="display:{0}">'.format(n == "close" ? "none" : "block"));
					for (var i = 0, l = e.length; i < l; i++) {
						var c = e[i],
							h = c.children,
							p = c.id,
							d = c.state,
							v = c.attributes;
						r.push("<li>");
						var m = typeof h == "undefined" ? o : d == "close" ? a : u;
						r.push('<span class="glyphicon {0}"></span> '.format(m)), r.push("<a{1}{2}{3}>{0}</a>".format(c.text, h ? " class='tree-node'" : "", p ? " data-id='{0}'".format(p) : "", v ? " data-attr='{0}'".format(JSON.stringify(v)) : "")), h && s.apply(r, f(h, !1, d)), r.push("</li>")
					}
					return !t && r.push("</ul>"), r
				},
				l = function () {
					e("span.glyphicon-folder-open, span.glyphicon-folder-close", i).click(function (t) {
						var n = e(this),
							r = n.closest("li").children("ul");
						n.hasClass(a) ? (n.removeClass(a).addClass(u), r.show()) : (n.removeClass(u).addClass(a), r.hide())
					})
				};
			if (t && t.constructor == Object) {
				var c = t.data;
				if (c && c.constructor == Array) {
					var h = f(c, !0);
					i.html(h.join("")), i.data("config", t), l()
				}
				var p = t.onClick;
				p && e("li>a", i).click(function () {
					var t = e(this);
					attrs = t.attr("data-attr"), p.call(r, {
						id: t.attr("data-id"),
						attributes: attrs ? JSON.parse(attrs) : {},
						text: t.text()
					}, t)
				})
			}
			return r
		}
	}(jQuery),
	function (e) {
		"use strict";
		typeof define == "function" && define.amd ? define(["jquery"], e) : e(typeof jQuery != "undefined" ? jQuery : window.Zepto)
	}(function (e) {
		"use strict";

		function r(t) {
			var n = t.data;
			t.isDefaultPrevented() || (t.preventDefault(), e(t.target).ajaxSubmit(n))
		}

		function i(t) {
			var n = t.target,
				r = e(n);
			if (!r.is("[type=submit],[type=image]")) {
				var i = r.closest("[type=submit]");
				if (i.length === 0) return;
				n = i[0]
			}
			var s = this;
			s.clk = n;
			if (n.type == "image")
				if (t.offsetX !== undefined) s.clk_x = t.offsetX, s.clk_y = t.offsetY;
				else if (typeof e.fn.offset == "function") {
				var o = r.offset();
				s.clk_x = t.pageX - o.left, s.clk_y = t.pageY - o.top
			} else s.clk_x = t.pageX - n.offsetLeft, s.clk_y = t.pageY - n.offsetTop;
			setTimeout(function () {
				s.clk = s.clk_x = s.clk_y = null
			}, 100)
		}

		function s() {
			if (!e.fn.ajaxSubmit.debug) return;
			var t = "[jquery.form] " + Array.prototype.join.call(arguments, "");
			window.console && window.console.log ? window.console.log(t) : window.opera && window.opera.postError && window.opera.postError(t)
		}
		var t = {};
		t.fileapi = e("<input type='file'/>").get(0).files !== undefined, t.formdata = window.FormData !== undefined;
		var n = !!e.fn.prop;
		e.fn.attr2 = function () {
			if (!n) return this.attr.apply(this, arguments);
			var e = this.prop.apply(this, arguments);
			return e && e.jquery || typeof e == "string" ? e : this.attr.apply(this, arguments)
		}, e.fn.ajaxSubmit = function (r) {
			function k(t) {
				var n = e.param(t, r.traditional).split("&"),
					i = n.length,
					s = [],
					o, u;
				for (o = 0; o < i; o++) n[o] = n[o].replace(/\+/g, " "), u = n[o].split("="), s.push([decodeURIComponent(u[0]), decodeURIComponent(u[1])]);
				return s
			}

			function L(t) {
				var n = new FormData;
				for (var s = 0; s < t.length; s++) n.append(t[s].name, t[s].value);
				if (r.extraData) {
					var o = k(r.extraData);
					for (s = 0; s < o.length; s++) o[s] && n.append(o[s][0], o[s][1])
				}
				r.data = null;
				var u = e.extend(!0, {}, e.ajaxSettings, r, {
					contentType: !1,
					processData: !1,
					cache: !1,
					type: i || "POST"
				});
				r.uploadProgress && (u.xhr = function () {
					var t = e.ajaxSettings.xhr();
					return t.upload && t.upload.addEventListener("progress", function (e) {
						var t = 0,
							n = e.loaded || e.position,
							i = e.total;
						e.lengthComputable && (t = Math.ceil(n / i * 100)), r.uploadProgress(e, n, i, t)
					}, !1), t
				}), u.data = null;
				var a = u.beforeSend;
				return u.beforeSend = function (e, t) {
					r.formData ? t.data = r.formData : t.data = n, a && a.call(this, e, t)
				}, e.ajax(u)
			}

			function A(t) {
				function T(e) {
					var t = null;
					try {
						e.contentWindow && (t = e.contentWindow.document)
					} catch (n) {
						s("cannot get iframe.contentWindow document: " + n)
					}
					if (t) return t;
					try {
						t = e.contentDocument ? e.contentDocument : e.document
					} catch (n) {
						s("cannot get iframe.contentDocument: " + n), t = e.document
					}
					return t
				}

				function k() {
					function f() {
						try {
							var e = T(v).readyState;
							s("state = " + e), e && e.toLowerCase() == "uninitialized" && setTimeout(f, 50)
						} catch (t) {
							s("Server abort: ", t, " (", t.name, ")"), _(x), w && clearTimeout(w), w = undefined
						}
					}
					var t = a.attr2("target"),
						n = a.attr2("action"),
						r = "multipart/form-data",
						u = a.attr("enctype") || a.attr("encoding") || r;
					o.setAttribute("target", p), (!i || /post/i.test(i)) && o.setAttribute("method", "POST"), n != l.url && o.setAttribute("action", l.url), !l.skipEncodingOverride && (!i || /post/i.test(i)) && a.attr({
						encoding: "multipart/form-data",
						enctype: "multipart/form-data"
					}), l.timeout && (w = setTimeout(function () {
						b = !0, _(S)
					}, l.timeout));
					var c = [];
					try {
						if (l.extraData)
							for (var h in l.extraData) l.extraData.hasOwnProperty(h) && (e.isPlainObject(l.extraData[h]) && l.extraData[h].hasOwnProperty("name") && l.extraData[h].hasOwnProperty("value") ? c.push(e('<input type="hidden" name="' + l.extraData[h].name + '">').val(l.extraData[h].value).appendTo(o)[0]) : c.push(e('<input type="hidden" name="' + h + '">').val(l.extraData[h]).appendTo(o)[0]));
						l.iframeTarget || d.appendTo("body"), v.attachEvent ? v.attachEvent("onload", _) : v.addEventListener("load", _, !1), setTimeout(f, 15);
						try {
							o.submit()
						} catch (m) {
							var g = document.createElement("form").submit;
							g.apply(o)
						}
					} finally {
						o.setAttribute("action", n), o.setAttribute("enctype", u), t ? o.setAttribute("target", t) : a.removeAttr("target"), e(c).remove()
					}
				}

				function _(t) {
					if (m.aborted || M) return;
					A = T(v), A || (s("cannot access response document"), t = x);
					if (t === S && m) {
						m.abort("timeout"), E.reject(m, "timeout");
						return
					}
					if (t == x && m) {
						m.abort("server abort"), E.reject(m, "error", "server abort");
						return
					}
					if (!A || A.location.href == l.iframeSrc)
						if (!b) return;
					v.detachEvent ? v.detachEvent("onload", _) : v.removeEventListener("load", _, !1);
					var n = "success",
						r;
					try {
						if (b) throw "timeout";
						var i = l.dataType == "xml" || A.XMLDocument || e.isXMLDoc(A);
						s("isXml=" + i);
						if (!i && window.opera && (A.body === null || !A.body.innerHTML) && --O) {
							s("requeing onLoad callback, DOM not available"), setTimeout(_, 250);
							return
						}
						var o = A.body ? A.body : A.documentElement;
						m.responseText = o ? o.innerHTML : null, m.responseXML = A.XMLDocument ? A.XMLDocument : A, i && (l.dataType = "xml"), m.getResponseHeader = function (e) {
							var t = {
								"content-type": l.dataType
							};
							return t[e.toLowerCase()]
						}, o && (m.status = Number(o.getAttribute("status")) || m.status, m.statusText = o.getAttribute("statusText") || m.statusText);
						var u = (l.dataType || "").toLowerCase(),
							a = /(json|script|text)/.test(u);
						if (a || l.textarea) {
							var f = A.getElementsByTagName("textarea")[0];
							if (f) m.responseText = f.value, m.status = Number(f.getAttribute("status")) || m.status, m.statusText = f.getAttribute("statusText") || m.statusText;
							else if (a) {
								var c = A.getElementsByTagName("pre")[0],
									p = A.getElementsByTagName("body")[0];
								c ? m.responseText = c.textContent ? c.textContent : c.innerText : p && (m.responseText = p.textContent ? p.textContent : p.innerText)
							}
						} else u == "xml" && !m.responseXML && m.responseText && (m.responseXML = D(m.responseText));
						try {
							L = H(m, u, l)
						} catch (g) {
							n = "parsererror", m.error = r = g || n
						}
					} catch (g) {
						s("error caught: ", g), n = "error", m.error = r = g || n
					}
					m.aborted && (s("upload aborted"), n = null), m.status && (n = m.status >= 200 && m.status < 300 || m.status === 304 ? "success" : "error"), n === "success" ? (l.success && l.success.call(l.context, L, "success", m), E.resolve(m.responseText, "success", m), h && e.event.trigger("ajaxSuccess", [m, l])) : n && (r === undefined && (r = m.statusText), l.error && l.error.call(l.context, m, n, r), E.reject(m, "error", r), h && e.event.trigger("ajaxError", [m, l, r])), h && e.event.trigger("ajaxComplete", [m, l]), h && !--e.active && e.event.trigger("ajaxStop"), l.complete && l.complete.call(l.context, m, n), M = !0, l.timeout && clearTimeout(w), setTimeout(function () {
						l.iframeTarget ? d.attr("src", l.iframeSrc) : d.remove(), m.responseXML = null
					}, 100)
				}
				var o = a[0],
					u, f, l, h, p, d, v, m, g, y, b, w, E = e.Deferred();
				E.abort = function (e) {
					m.abort(e)
				};
				if (t)
					for (f = 0; f < c.length; f++) u = e(c[f]), n ? u.prop("disabled", !1) : u.removeAttr("disabled");
				l = e.extend(!0, {}, e.ajaxSettings, r), l.context = l.context || l, p = "jqFormIO" + (new Date).getTime(), l.iframeTarget ? (d = e(l.iframeTarget), y = d.attr2("name"), y ? p = y : d.attr2("name", p)) : (d = e('<iframe name="' + p + '" src="' + l.iframeSrc + '" />'), d.css({
					position: "absolute",
					top: "-1000px",
					left: "-1000px"
				})), v = d[0], m = {
					aborted: 0,
					responseText: null,
					responseXML: null,
					status: 0,
					statusText: "n/a",
					getAllResponseHeaders: function () {},
					getResponseHeader: function () {},
					setRequestHeader: function () {},
					abort: function (t) {
						var n = t === "timeout" ? "timeout" : "aborted";
						s("aborting upload... " + n), this.aborted = 1;
						try {
							v.contentWindow.document.execCommand && v.contentWindow.document.execCommand("Stop")
						} catch (r) {}
						d.attr("src", l.iframeSrc), m.error = n, l.error && l.error.call(l.context, m, n, t), h && e.event.trigger("ajaxError", [m, l, n]), l.complete && l.complete.call(l.context, m, n)
					}
				}, h = l.global, h && 0 === e.active++ && e.event.trigger("ajaxStart"), h && e.event.trigger("ajaxSend", [m, l]);
				if (l.beforeSend && l.beforeSend.call(l.context, m, l) === !1) return l.global && e.active--, E.reject(), E;
				if (m.aborted) return E.reject(), E;
				g = o.clk, g && (y = g.name, y && !g.disabled && (l.extraData = l.extraData || {}, l.extraData[y] = g.value, g.type == "image" && (l.extraData[y + ".x"] = o.clk_x, l.extraData[y + ".y"] = o.clk_y)));
				var S = 1,
					x = 2,
					N = e("meta[name=csrf-token]").attr("content"),
					C = e("meta[name=csrf-param]").attr("content");
				C && N && (l.extraData = l.extraData || {}, l.extraData[C] = N), l.forceSync ? k() : setTimeout(k, 10);
				var L, A, O = 50,
					M, D = e.parseXML || function (e, t) {
						return window.ActiveXObject ? (t = new ActiveXObject("Microsoft.XMLDOM"), t.async = "false", t.loadXML(e)) : t = (new DOMParser).parseFromString(e, "text/xml"), t && t.documentElement && t.documentElement.nodeName != "parsererror" ? t : null
					},
					P = e.parseJSON || function (e) {
						return window.eval("(" + e + ")")
					},
					H = function (t, n, r) {
						var i = t.getResponseHeader("content-type") || "",
							s = n === "xml" || !n && i.indexOf("xml") >= 0,
							o = s ? t.responseXML : t.responseText;
						return s && o.documentElement.nodeName === "parsererror" && e.error && e.error("parsererror"), r && r.dataFilter && (o = r.dataFilter(o, n)), typeof o == "string" && (n === "json" || !n && i.indexOf("json") >= 0 ? o = P(o) : (n === "script" || !n && i.indexOf("javascript") >= 0) && e.globalEval(o)), o
					};
				return E
			}
			if (!this.length) return s("ajaxSubmit: skipping submit process - no element selected"), this;
			var i, o, u, a = this;
			typeof r == "function" ? r = {
				success: r
			} : r === undefined && (r = {}), i = r.type || this.attr2("method"), o = r.url || this.attr2("action"), u = typeof o == "string" ? e.trim(o) : "", u = u || window.location.href || "", u && (u = (u.match(/^([^#]+)/) || [])[1]), r = e.extend(!0, {
				url: u,
				success: e.ajaxSettings.success,
				type: i || e.ajaxSettings.type,
				iframeSrc: /^https/i.test(window.location.href || "") ? "javascript:false" : "about:blank"
			}, r);
			var f = {};
			this.trigger("form-pre-serialize", [this, r, f]);
			if (f.veto) return s("ajaxSubmit: submit vetoed via form-pre-serialize trigger"), this;
			if (r.beforeSerialize && r.beforeSerialize(this, r) === !1) return s("ajaxSubmit: submit aborted via beforeSerialize callback"), this;
			var l = r.traditional;
			l === undefined && (l = e.ajaxSettings.traditional);
			var c = [],
				h, p = this.formToArray(r.semantic, c);
			r.data && (r.extraData = r.data, h = e.param(r.data, l));
			if (r.beforeSubmit && r.beforeSubmit(p, this, r) === !1) return s("ajaxSubmit: submit aborted via beforeSubmit callback"), this;
			this.trigger("form-submit-validate", [p, this, r, f]);
			if (f.veto) return s("ajaxSubmit: submit vetoed via form-submit-validate trigger"), this;
			var d = e.param(p, l);
			h && (d = d ? d + "&" + h : h), r.type.toUpperCase() == "GET" ? (r.url += (r.url.indexOf("?") >= 0 ? "&" : "?") + d, r.data = null) : r.data = d;
			var v = [];
			r.resetForm && v.push(function () {
				a.resetForm()
			}), r.clearForm && v.push(function () {
				a.clearForm(r.includeHidden)
			});
			if (!r.dataType && r.target) {
				var m = r.success || function () {};
				v.push(function (t) {
					var n = r.replaceTarget ? "replaceWith" : "html";
					e(r.target)[n](t).each(m, arguments)
				})
			} else r.success && v.push(r.success);
			r.success = function (e, t, n) {
				var i = r.context || this;
				for (var s = 0, o = v.length; s < o; s++) v[s].apply(i, [e, t, n || a, a])
			};
			if (r.error) {
				var g = r.error;
				r.error = function (e, t, n) {
					var i = r.context || this;
					g.apply(i, [e, t, n, a])
				}
			}
			if (r.complete) {
				var y = r.complete;
				r.complete = function (e, t) {
					var n = r.context || this;
					y.apply(n, [e, t, a])
				}
			}
			var b = e("input[type=file]:enabled", this).filter(function () {
					return e(this).val() !== ""
				}),
				w = b.length > 0,
				E = "multipart/form-data",
				S = a.attr("enctype") == E || a.attr("encoding") == E,
				x = t.fileapi && t.formdata;
			s("fileAPI :" + x);
			var T = (w || S) && !x,
				N;
			r.iframe !== !1 && (r.iframe || T) ? r.closeKeepAlive ? e.get(r.closeKeepAlive, function () {
				N = A(p)
			}) : N = A(p) : (w || S) && x ? N = L(p) : N = e.ajax(r), a.removeData("jqxhr").data("jqxhr", N);
			for (var C = 0; C < c.length; C++) c[C] = null;
			return this.trigger("form-submit-notify", [this, r]), this
		}, e.fn.ajaxForm = function (t) {
			t = t || {}, t.delegation = t.delegation && e.isFunction(e.fn.on);
			if (!t.delegation && this.length === 0) {
				var n = {
					s: this.selector,
					c: this.context
				};
				return !e.isReady && n.s ? (s("DOM not ready, queuing ajaxForm"), e(function () {
					e(n.s, n.c).ajaxForm(t)
				}), this) : (s("terminating; zero elements found by selector" + (e.isReady ? "" : " (DOM not ready)")), this)
			}
			return t.delegation ? (e(document).off("submit.form-plugin", this.selector, r).off("click.form-plugin", this.selector, i).on("submit.form-plugin", this.selector, t, r).on("click.form-plugin", this.selector, t, i), this) : this.ajaxFormUnbind().bind("submit.form-plugin", t, r).bind("click.form-plugin", t, i)
		}, e.fn.ajaxFormUnbind = function () {
			return this.unbind("submit.form-plugin click.form-plugin")
		}, e.fn.formToArray = function (n, r) {
			var i = [];
			if (this.length === 0) return i;
			var s = this[0],
				o = this.attr("id"),
				u = n ? s.getElementsByTagName("*") : s.elements,
				a;
			u && !/MSIE [678]/.test(navigator.userAgent) && (u = e(u).get()), o && (a = e(':input[form="' + o + '"]').get(), a.length && (u = (u || []).concat(a)));
			if (!u || !u.length) return i;
			var f, l, c, h, p, d, v;
			for (f = 0, d = u.length; f < d; f++) {
				p = u[f], c = p.name;
				if (!c || p.disabled) continue;
				if (n && s.clk && p.type == "image") {
					s.clk == p && (i.push({
						name: c,
						value: e(p).val(),
						type: p.type
					}), i.push({
						name: c + ".x",
						value: s.clk_x
					}, {
						name: c + ".y",
						value: s.clk_y
					}));
					continue
				}
				h = e.fieldValue(p, !0);
				if (h && h.constructor == Array) {
					r && r.push(p);
					for (l = 0, v = h.length; l < v; l++) i.push({
						name: c,
						value: h[l]
					})
				} else if (t.fileapi && p.type == "file") {
					r && r.push(p);
					var m = p.files;
					if (m.length)
						for (l = 0; l < m.length; l++) i.push({
							name: c,
							value: m[l],
							type: p.type
						});
					else i.push({
						name: c,
						value: "",
						type: p.type
					})
				} else h !== null && typeof h != "undefined" && (r && r.push(p), i.push({
					name: c,
					value: h,
					type: p.type,
					required: p.required
				}))
			}
			if (!n && s.clk) {
				var g = e(s.clk),
					y = g[0];
				c = y.name, c && !y.disabled && y.type == "image" && (i.push({
					name: c,
					value: g.val()
				}), i.push({
					name: c + ".x",
					value: s.clk_x
				}, {
					name: c + ".y",
					value: s.clk_y
				}))
			}
			return i
		}, e.fn.formSerialize = function (t) {
			return e.param(this.formToArray(t))
		}, e.fn.fieldSerialize = function (t) {
			var n = [];
			return this.each(function () {
				var r = this.name;
				if (!r) return;
				var i = e.fieldValue(this, t);
				if (i && i.constructor == Array)
					for (var s = 0, o = i.length; s < o; s++) n.push({
						name: r,
						value: i[s]
					});
				else i !== null && typeof i != "undefined" && n.push({
					name: this.name,
					value: i
				})
			}), e.param(n)
		}, e.fn.fieldValue = function (t) {
			for (var n = [], r = 0, i = this.length; r < i; r++) {
				var s = this[r],
					o = e.fieldValue(s, t);
				if (o === null || typeof o == "undefined" || o.constructor == Array && !o.length) continue;
				o.constructor == Array ? e.merge(n, o) : n.push(o)
			}
			return n
		}, e.fieldValue = function (t, n) {
			var r = t.name,
				i = t.type,
				s = t.tagName.toLowerCase();
			n === undefined && (n = !0);
			if (n && (!r || t.disabled || i == "reset" || i == "button" || (i == "checkbox" || i == "radio") && !t.checked || (i == "submit" || i == "image") && t.form && t.form.clk != t || s == "select" && t.selectedIndex == -1)) return null;
			if (s == "select") {
				var o = t.selectedIndex;
				if (o < 0) return null;
				var u = [],
					a = t.options,
					f = i == "select-one",
					l = f ? o + 1 : a.length;
				for (var c = f ? o : 0; c < l; c++) {
					var h = a[c];
					if (h.selected) {
						var p = h.value;
						p || (p = h.attributes && h.attributes.value && !h.attributes.value.specified ? h.text : h.value);
						if (f) return p;
						u.push(p)
					}
				}
				return u
			}
			return e(t).val()
		}, e.fn.clearForm = function (t) {
			return this.each(function () {
				e("input,select,textarea", this).clearFields(t)
			})
		}, e.fn.clearFields = e.fn.clearInputs = function (t) {
			var n = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;
			return this.each(function () {
				var r = this.type,
					i = this.tagName.toLowerCase();
				n.test(r) || i == "textarea" ? this.value = "" : r == "checkbox" || r == "radio" ? this.checked = !1 : i == "select" ? this.selectedIndex = -1 : r == "file" ? /MSIE/.test(navigator.userAgent) ? e(this).replaceWith(e(this).clone(!0)) : e(this).val("") : t && (t === !0 && /hidden/.test(r) || typeof t == "string" && e(this).is(t)) && (this.value = "")
			})
		}, e.fn.resetForm = function () {
			return this.each(function () {
				(typeof this.reset == "function" || typeof this.reset == "object" && !this.reset.nodeType) && this.reset()
			})
		}, e.fn.enable = function (e) {
			return e === undefined && (e = !0), this.each(function () {
				this.disabled = !e
			})
		}, e.fn.selected = function (t) {
			return t === undefined && (t = !0), this.each(function () {
				var n = this.type;
				if (n == "checkbox" || n == "radio") this.checked = t;
				else if (this.tagName.toLowerCase() == "option") {
					var r = e(this).parent("select");
					t && r[0] && r[0].type == "select-one" && r.find("option").selected(!1), this.selected = t
				}
			})
		}, e.fn.ajaxSubmit.debug = !1
	}),
	function (e, t) {
		var n = {
				extensions: ""
			},
			r = $(".audiojs .progress"),
			i = $("#uploadInfo #file");
		$.fn.formUpload = function (e) {
			return e = $.extend(!0, {}, n, e), this.each(function () {
				var t = this,
					n = $(t),
					r = n.find("form"),
					i = n.find(".upload-text"),
					s = n.find(".upload-file"),
					o = n.find(".progressbar .progress"),
					u = function () {
						n.find("form").ajaxForm({
							beforeSend: function () {
								o.css("width", "10%"), $("body").addClass("uploading")
							},
							uploadProgress: function (e, t, n, r) {
								r > 94 && (r = 94), o.css("width", r + "%")
							},
							complete: function (t, r) {
								console.log(arguments), o.css("width", "100%"), n.dialog("close"), r == "error" ? e.error && e.error(t.responseText, t) : e.success && e.success(t.responseText, t), setTimeout(function () {
									$("body").removeClass("uploading")
								}, 1e3)
							}
						}), n.dialog({
							title: e.title || "",
							backdrop: "static",
							buttons: [{
								text: "开始上传",
								click: function () {
									a() ? r.submit() : $.messager.popup("请上传正确格式的文件！")
								}
							}, {
								text: "取消",
								click: function () {
									n.dialog("close")
								}
							}]
						}), s.on("change", function () {
							i.val(this.value)
						})
					},
					a = function () {
						var t = s.val(),
							n = e.extensions;
						if (t) {
							if (!n) return !0;
							t = t.toLowerCase();
							for (var r = 0; r < n.length; r++) {
								var i = n[r];
								if (t.lastIndexOf(i) == t.length - i.length) return !0
							}
						}
						return !1
					};
				n.closest(".dialog").size() < 1 ? u() : (r[0].reset(), n.dialog("open")), n.closest(".dialog").off()
			})
		}, $.fn.formUpload.defaults = n
	}(window, document),
	function (e, t) {
		$.fn.ajaxProgress = function (e) {
			return this.each(function () {
				var e = this,
					n = $(e),
					r = 0,
					i, s = function (e) {
						i || (i = setInterval(function () {
							u(1)
						}, 1e3)), r > 100 && (r = 100, clearInterval(i), i = null), r < 0 && (r = 0), e ? n.css("width", r + "%") : n.animate({
							width: r + "%"
						})
					},
					o = function (e, t) {
						r = e, s(t)
					},
					u = function (e) {
						r += e, s()
					},
					a = function () {
						$(t).on("ajaxStart", function () {
							o(10, !0)
						}).on("ajaxComplete", function () {
							o(100)
						})
					};
				a()
			})
		}
	}(window, document),
	function (e) {
		typeof define == "function" && define.amd ? define(["jquery"], e) : e(jQuery)
	}(function (e) {
		function t(t, r) {
			var i, s, o, u = t.nodeName.toLowerCase();
			return "area" === u ? (i = t.parentNode, s = i.name, !t.href || !s || i.nodeName.toLowerCase() !== "map" ? !1 : (o = e("img[usemap='#" + s + "']")[0], !!o && n(o))) : (/input|select|textarea|button|object/.test(u) ? !t.disabled : "a" === u ? t.href || r : r) && n(t)
		}

		function n(t) {
			return e.expr.filters.visible(t) && !e(t).parents().addBack().filter(function () {
				return e.css(this, "visibility") === "hidden"
			}).length
		}

		function m(e) {
			var t, n;
			while (e.length && e[0] !== document) {
				t = e.css("position");
				if (t === "absolute" || t === "relative" || t === "fixed") {
					n = parseInt(e.css("zIndex"), 10);
					if (!isNaN(n) && n !== 0) return n
				}
				e = e.parent()
			}
			return 0
		}

		function g() {
			this._curInst = null, this._keyEvent = !1, this._disabledInputs = [], this._datepickerShowing = !1, this._inDialog = !1, this._mainDivId = "ui-datepicker-div", this._inlineClass = "ui-datepicker-inline", this._appendClass = "ui-datepicker-append", this._triggerClass = "ui-datepicker-trigger", this._dialogClass = "ui-datepicker-dialog", this._disableClass = "ui-datepicker-disabled", this._unselectableClass = "ui-datepicker-unselectable", this._currentClass = "ui-datepicker-current-day", this._dayOverClass = "ui-datepicker-days-cell-over", this.regional = [], this.regional[""] = {
				closeText: "Done",
				prevText: "Prev",
				nextText: "Next",
				currentText: "Today",
				monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
				monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
				dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
				dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
				dayNamesMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
				weekHeader: "Wk",
				dateFormat: "mm/dd/yy",
				firstDay: 0,
				isRTL: !1,
				showMonthAfterYear: !1,
				yearSuffix: ""
			}, this._defaults = {
				showOn: "focus",
				showAnim: "fadeIn",
				showOptions: {},
				defaultDate: null,
				appendText: "",
				buttonText: "...",
				buttonImage: "",
				buttonImageOnly: !1,
				hideIfNoPrevNext: !1,
				navigationAsDateFormat: !1,
				gotoCurrent: !1,
				changeMonth: !1,
				changeYear: !1,
				yearRange: "c-10:c+10",
				showOtherMonths: !1,
				selectOtherMonths: !1,
				showWeek: !1,
				calculateWeek: this.iso8601Week,
				shortYearCutoff: "+10",
				minDate: null,
				maxDate: null,
				duration: "fast",
				beforeShowDay: null,
				beforeShow: null,
				onSelect: null,
				onChangeMonthYear: null,
				onClose: null,
				numberOfMonths: 1,
				showCurrentAtPos: 0,
				stepMonths: 1,
				stepBigMonths: 12,
				altField: "",
				altFormat: "",
				constrainInput: !0,
				showButtonPanel: !1,
				autoSize: !1,
				disabled: !1
			}, e.extend(this._defaults, this.regional[""]), this.regional.en = e.extend(!0, {}, this.regional[""]), this.regional["en-US"] = e.extend(!0, {}, this.regional.en), this.dpDiv = y(e("<div id='" + this._mainDivId + "' class='ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>"))
		}

		function y(t) {
			var n = "button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a";
			return t.delegate(n, "mouseout", function () {
				e(this).removeClass("ui-state-hover"), this.className.indexOf("ui-datepicker-prev") !== -1 && e(this).removeClass("ui-datepicker-prev-hover"), this.className.indexOf("ui-datepicker-next") !== -1 && e(this).removeClass("ui-datepicker-next-hover")
			}).delegate(n, "mouseover", b)
		}

		function b() {
			e.datepicker._isDisabledDatepicker(v.inline ? v.dpDiv.parent()[0] : v.input[0]) || (e(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover"), e(this).addClass("ui-state-hover"), this.className.indexOf("ui-datepicker-prev") !== -1 && e(this).addClass("ui-datepicker-prev-hover"), this.className.indexOf("ui-datepicker-next") !== -1 && e(this).addClass("ui-datepicker-next-hover"))
		}

		function w(t, n) {
			e.extend(t, n);
			for (var r in n) n[r] == null && (t[r] = n[r]);
			return t
		}
		e.ui = e.ui || {}, e.extend(e.ui, {
			version: "1.11.2",
			keyCode: {
				BACKSPACE: 8,
				COMMA: 188,
				DELETE: 46,
				DOWN: 40,
				END: 35,
				ENTER: 13,
				ESCAPE: 27,
				HOME: 36,
				LEFT: 37,
				PAGE_DOWN: 34,
				PAGE_UP: 33,
				PERIOD: 190,
				RIGHT: 39,
				SPACE: 32,
				TAB: 9,
				UP: 38
			}
		}), e.fn.extend({
			scrollParent: function (t) {
				var n = this.css("position"),
					r = n === "absolute",
					i = t ? /(auto|scroll|hidden)/ : /(auto|scroll)/,
					s = this.parents().filter(function () {
						var t = e(this);
						return r && t.css("position") === "static" ? !1 : i.test(t.css("overflow") + t.css("overflow-y") + t.css("overflow-x"))
					}).eq(0);
				return n === "fixed" || !s.length ? e(this[0].ownerDocument || document) : s
			},
			uniqueId: function () {
				var e = 0;
				return function () {
					return this.each(function () {
						this.id || (this.id = "ui-id-" + ++e)
					})
				}
			}(),
			removeUniqueId: function () {
				return this.each(function () {
					/^ui-id-\d+$/.test(this.id) && e(this).removeAttr("id")
				})
			}
		}), e.extend(e.expr[":"], {
			data: e.expr.createPseudo ? e.expr.createPseudo(function (t) {
				return function (n) {
					return !!e.data(n, t)
				}
			}) : function (t, n, r) {
				return !!e.data(t, r[3])
			},
			focusable: function (n) {
				return t(n, !isNaN(e.attr(n, "tabindex")))
			},
			tabbable: function (n) {
				var r = e.attr(n, "tabindex"),
					i = isNaN(r);
				return (i || r >= 0) && t(n, !i)
			}
		}), e("<a>").outerWidth(1).jquery || e.each(["Width", "Height"], function (t, n) {
			function o(t, n, i, s) {
				return e.each(r, function () {
					n -= parseFloat(e.css(t, "padding" + this)) || 0, i && (n -= parseFloat(e.css(t, "border" + this + "Width")) || 0), s && (n -= parseFloat(e.css(t, "margin" + this)) || 0)
				}), n
			}
			var r = n === "Width" ? ["Left", "Right"] : ["Top", "Bottom"],
				i = n.toLowerCase(),
				s = {
					innerWidth: e.fn.innerWidth,
					innerHeight: e.fn.innerHeight,
					outerWidth: e.fn.outerWidth,
					outerHeight: e.fn.outerHeight
				};
			e.fn["inner" + n] = function (t) {
				return t === undefined ? s["inner" + n].call(this) : this.each(function () {
					e(this).css(i, o(this, t) + "px")
				})
			}, e.fn["outer" + n] = function (t, r) {
				return typeof t != "number" ? s["outer" + n].call(this, t) : this.each(function () {
					e(this).css(i, o(this, t, !0, r) + "px")
				})
			}
		}), e.fn.addBack || (e.fn.addBack = function (e) {
			return this.add(e == null ? this.prevObject : this.prevObject.filter(e))
		}), e("<a>").data("a-b", "a").removeData("a-b").data("a-b") && (e.fn.removeData = function (t) {
			return function (n) {
				return arguments.length ? t.call(this, e.camelCase(n)) : t.call(this)
			}
		}(e.fn.removeData)), e.ui.ie = !!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase()), e.fn.extend({
			focus: function (t) {
				return function (n, r) {
					return typeof n == "number" ? this.each(function () {
						var t = this;
						setTimeout(function () {
							e(t).focus(), r && r.call(t)
						}, n)
					}) : t.apply(this, arguments)
				}
			}(e.fn.focus),
			disableSelection: function () {
				var e = "onselectstart" in document.createElement("div") ? "selectstart" : "mousedown";
				return function () {
					return this.bind(e + ".ui-disableSelection", function (e) {
						e.preventDefault()
					})
				}
			}(),
			enableSelection: function () {
				return this.unbind(".ui-disableSelection")
			},
			zIndex: function (t) {
				if (t !== undefined) return this.css("zIndex", t);
				if (this.length) {
					var n = e(this[0]),
						r, i;
					while (n.length && n[0] !== document) {
						r = n.css("position");
						if (r === "absolute" || r === "relative" || r === "fixed") {
							i = parseInt(n.css("zIndex"), 10);
							if (!isNaN(i) && i !== 0) return i
						}
						n = n.parent()
					}
				}
				return 0
			}
		}), e.ui.plugin = {
			add: function (t, n, r) {
				var i, s = e.ui[t].prototype;
				for (i in r) s.plugins[i] = s.plugins[i] || [], s.plugins[i].push([n, r[i]])
			},
			call: function (e, t, n, r) {
				var i, s = e.plugins[t];
				if (!s) return;
				if (!r && (!e.element[0].parentNode || e.element[0].parentNode.nodeType === 11)) return;
				for (i = 0; i < s.length; i++) e.options[s[i][0]] && s[i][1].apply(e.element, n)
			}
		};
		var r = 0,
			i = Array.prototype.slice;
		e.cleanData = function (t) {
			return function (n) {
				var r, i, s;
				for (s = 0;
					(i = n[s]) != null; s++) try {
					r = e._data(i, "events"), r && r.remove && e(i).triggerHandler("remove")
				} catch (o) {}
				t(n)
			}
		}(e.cleanData), e.widget = function (t, n, r) {
			var i, s, o, u, a = {},
				f = t.split(".")[0];
			return t = t.split(".")[1], i = f + "-" + t, r || (r = n, n = e.Widget), e.expr[":"][i.toLowerCase()] = function (t) {
				return !!e.data(t, i)
			}, e[f] = e[f] || {}, s = e[f][t], o = e[f][t] = function (e, t) {
				if (!this._createWidget) return new o(e, t);
				arguments.length && this._createWidget(e, t)
			}, e.extend(o, s, {
				version: r.version,
				_proto: e.extend({}, r),
				_childConstructors: []
			}), u = new n, u.options = e.widget.extend({}, u.options), e.each(r, function (t, r) {
				if (!e.isFunction(r)) {
					a[t] = r;
					return
				}
				a[t] = function () {
					var e = function () {
							return n.prototype[t].apply(this, arguments)
						},
						i = function (e) {
							return n.prototype[t].apply(this, e)
						};
					return function () {
						var t = this._super,
							n = this._superApply,
							s;
						return this._super = e, this._superApply = i, s = r.apply(this, arguments), this._super = t, this._superApply = n, s
					}
				}()
			}), o.prototype = e.widget.extend(u, {
				widgetEventPrefix: s ? u.widgetEventPrefix || t : t
			}, a, {
				constructor: o,
				namespace: f,
				widgetName: t,
				widgetFullName: i
			}), s ? (e.each(s._childConstructors, function (t, n) {
				var r = n.prototype;
				e.widget(r.namespace + "." + r.widgetName, o, n._proto)
			}), delete s._childConstructors) : n._childConstructors.push(o), e.widget.bridge(t, o), o
		}, e.widget.extend = function (t) {
			var n = i.call(arguments, 1),
				r = 0,
				s = n.length,
				o, u;
			for (; r < s; r++)
				for (o in n[r]) u = n[r][o], n[r].hasOwnProperty(o) && u !== undefined && (e.isPlainObject(u) ? t[o] = e.isPlainObject(t[o]) ? e.widget.extend({}, t[o], u) : e.widget.extend({}, u) : t[o] = u);
			return t
		}, e.widget.bridge = function (t, n) {
			var r = n.prototype.widgetFullName || t;
			e.fn[t] = function (s) {
				var o = typeof s == "string",
					u = i.call(arguments, 1),
					a = this;
				return s = !o && u.length ? e.widget.extend.apply(null, [s].concat(u)) : s, o ? this.each(function () {
					var n, i = e.data(this, r);
					if (s === "instance") return a = i, !1;
					if (!i) return e.error("cannot call methods on " + t + " prior to initialization; " + "attempted to call method '" + s + "'");
					if (!e.isFunction(i[s]) || s.charAt(0) === "_") return e.error("no such method '" + s + "' for " + t + " widget instance");
					n = i[s].apply(i, u);
					if (n !== i && n !== undefined) return a = n && n.jquery ? a.pushStack(n.get()) : n, !1
				}) : this.each(function () {
					var t = e.data(this, r);
					t ? (t.option(s || {}), t._init && t._init()) : e.data(this, r, new n(s, this))
				}), a
			}
		}, e.Widget = function () {}, e.Widget._childConstructors = [], e.Widget.prototype = {
			widgetName: "widget",
			widgetEventPrefix: "",
			defaultElement: "<div>",
			options: {
				disabled: !1,
				create: null
			},
			_createWidget: function (t, n) {
				n = e(n || this.defaultElement || this)[0], this.element = e(n), this.uuid = r++, this.eventNamespace = "." + this.widgetName + this.uuid, this.bindings = e(), this.hoverable = e(), this.focusable = e(), n !== this && (e.data(n, this.widgetFullName, this), this._on(!0, this.element, {
					remove: function (e) {
						e.target === n && this.destroy()
					}
				}), this.document = e(n.style ? n.ownerDocument : n.document || n), this.window = e(this.document[0].defaultView || this.document[0].parentWindow)), this.options = e.widget.extend({}, this.options, this._getCreateOptions(), t), this._create(), this._trigger("create", null, this._getCreateEventData()), this._init()
			},
			_getCreateOptions: e.noop,
			_getCreateEventData: e.noop,
			_create: e.noop,
			_init: e.noop,
			destroy: function () {
				this._destroy(), this.element.unbind(this.eventNamespace).removeData(this.widgetFullName).removeData(e.camelCase(this.widgetFullName)), this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName + "-disabled " + "ui-state-disabled"), this.bindings.unbind(this.eventNamespace), this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus")
			},
			_destroy: e.noop,
			widget: function () {
				return this.element
			},
			option: function (t, n) {
				var r = t,
					i, s, o;
				if (arguments.length === 0) return e.widget.extend({}, this.options);
				if (typeof t == "string") {
					r = {}, i = t.split("."), t = i.shift();
					if (i.length) {
						s = r[t] = e.widget.extend({}, this.options[t]);
						for (o = 0; o < i.length - 1; o++) s[i[o]] = s[i[o]] || {}, s = s[i[o]];
						t = i.pop();
						if (arguments.length === 1) return s[t] === undefined ? null : s[t];
						s[t] = n
					} else {
						if (arguments.length === 1) return this.options[t] === undefined ? null : this.options[t];
						r[t] = n
					}
				}
				return this._setOptions(r), this
			},
			_setOptions: function (e) {
				var t;
				for (t in e) this._setOption(t, e[t]);
				return this
			},
			_setOption: function (e, t) {
				return this.options[e] = t, e === "disabled" && (this.widget().toggleClass(this.widgetFullName + "-disabled", !!t), t && (this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus"))), this
			},
			enable: function () {
				return this._setOptions({
					disabled: !1
				})
			},
			disable: function () {
				return this._setOptions({
					disabled: !0
				})
			},
			_on: function (t, n, r) {
				var i, s = this;
				typeof t != "boolean" && (r = n, n = t, t = !1), r ? (n = i = e(n), this.bindings = this.bindings.add(n)) : (r = n, n = this.element, i = this.widget()), e.each(r, function (r, o) {
					function u() {
						if (!t && (s.options.disabled === !0 || e(this).hasClass("ui-state-disabled"))) return;
						return (typeof o == "string" ? s[o] : o).apply(s, arguments)
					}
					typeof o != "string" && (u.guid = o.guid = o.guid || u.guid || e.guid++);
					var a = r.match(/^([\w:-]*)\s*(.*)$/),
						f = a[1] + s.eventNamespace,
						l = a[2];
					l ? i.delegate(l, f, u) : n.bind(f, u)
				})
			},
			_off: function (t, n) {
				n = (n || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace, t.unbind(n).undelegate(n), this.bindings = e(this.bindings.not(t).get()), this.focusable = e(this.focusable.not(t).get()), this.hoverable = e(this.hoverable.not(t).get())
			},
			_delay: function (e, t) {
				function n() {
					return (typeof e == "string" ? r[e] : e).apply(r, arguments)
				}
				var r = this;
				return setTimeout(n, t || 0)
			},
			_hoverable: function (t) {
				this.hoverable = this.hoverable.add(t), this._on(t, {
					mouseenter: function (t) {
						e(t.currentTarget).addClass("ui-state-hover")
					},
					mouseleave: function (t) {
						e(t.currentTarget).removeClass("ui-state-hover")
					}
				})
			},
			_focusable: function (t) {
				this.focusable = this.focusable.add(t), this._on(t, {
					focusin: function (t) {
						e(t.currentTarget).addClass("ui-state-focus")
					},
					focusout: function (t) {
						e(t.currentTarget).removeClass("ui-state-focus")
					}
				})
			},
			_trigger: function (t, n, r) {
				var i, s, o = this.options[t];
				r = r || {}, n = e.Event(n), n.type = (t === this.widgetEventPrefix ? t : this.widgetEventPrefix + t).toLowerCase(), n.target = this.element[0], s = n.originalEvent;
				if (s)
					for (i in s) i in n || (n[i] = s[i]);
				return this.element.trigger(n, r), !(e.isFunction(o) && o.apply(this.element[0], [n].concat(r)) === !1 || n.isDefaultPrevented())
			}
		}, e.each({
			show: "fadeIn",
			hide: "fadeOut"
		}, function (t, n) {
			e.Widget.prototype["_" + t] = function (r, i, s) {
				typeof i == "string" && (i = {
					effect: i
				});
				var o, u = i ? i === !0 || typeof i == "number" ? n : i.effect || n : t;
				i = i || {}, typeof i == "number" && (i = {
					duration: i
				}), o = !e.isEmptyObject(i), i.complete = s, i.delay && r.delay(i.delay), o && e.effects && e.effects.effect[u] ? r[t](i) : u !== t && r[u] ? r[u](i.duration, i.easing, s) : r.queue(function (n) {
					e(this)[t](), s && s.call(r[0]), n()
				})
			}
		});
		var s = e.widget,
			o = !1;
		e(document).mouseup(function () {
			o = !1
		});
		var u = e.widget("ui.mouse", {
			version: "1.11.2",
			options: {
				cancel: "input,textarea,button,select,option",
				distance: 1,
				delay: 0
			},
			_mouseInit: function () {
				var t = this;
				this.element.bind("mousedown." + this.widgetName, function (e) {
					return t._mouseDown(e)
				}).bind("click." + this.widgetName, function (n) {
					if (!0 === e.data(n.target, t.widgetName + ".preventClickEvent")) return e.removeData(n.target, t.widgetName + ".preventClickEvent"), n.stopImmediatePropagation(), !1
				}), this.started = !1
			},
			_mouseDestroy: function () {
				this.element.unbind("." + this.widgetName), this._mouseMoveDelegate && this.document.unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate)
			},
			_mouseDown: function (t) {
				if (o) return;
				this._mouseMoved = !1, this._mouseStarted && this._mouseUp(t), this._mouseDownEvent = t;
				var n = this,
					r = t.which === 1,
					i = typeof this.options.cancel == "string" && t.target.nodeName ? e(t.target).closest(this.options.cancel).length : !1;
				if (!r || i || !this._mouseCapture(t)) return !0;
				this.mouseDelayMet = !this.options.delay, this.mouseDelayMet || (this._mouseDelayTimer = setTimeout(function () {
					n.mouseDelayMet = !0
				}, this.options.delay));
				if (this._mouseDistanceMet(t) && this._mouseDelayMet(t)) {
					this._mouseStarted = this._mouseStart(t) !== !1;
					if (!this._mouseStarted) return t.preventDefault(), !0
				}
				return !0 === e.data(t.target, this.widgetName + ".preventClickEvent") && e.removeData(t.target, this.widgetName + ".preventClickEvent"), this._mouseMoveDelegate = function (e) {
					return n._mouseMove(e)
				}, this._mouseUpDelegate = function (e) {
					return n._mouseUp(e)
				}, this.document.bind("mousemove." + this.widgetName, this._mouseMoveDelegate).bind("mouseup." + this.widgetName, this._mouseUpDelegate), t.preventDefault(), o = !0, !0
			},
			_mouseMove: function (t) {
				if (this._mouseMoved) {
					if (e.ui.ie && (!document.documentMode || document.documentMode < 9) && !t.button) return this._mouseUp(t);
					if (!t.which) return this._mouseUp(t)
				}
				if (t.which || t.button) this._mouseMoved = !0;
				return this._mouseStarted ? (this._mouseDrag(t), t.preventDefault()) : (this._mouseDistanceMet(t) && this._mouseDelayMet(t) && (this._mouseStarted = this._mouseStart(this._mouseDownEvent, t) !== !1, this._mouseStarted ? this._mouseDrag(t) : this._mouseUp(t)), !this._mouseStarted)
			},
			_mouseUp: function (t) {
				return this.document.unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate), this._mouseStarted && (this._mouseStarted = !1, t.target === this._mouseDownEvent.target && e.data(t.target, this.widgetName + ".preventClickEvent", !0), this._mouseStop(t)), o = !1, !1
			},
			_mouseDistanceMet: function (e) {
				return Math.max(Math.abs(this._mouseDownEvent.pageX - e.pageX), Math.abs(this._mouseDownEvent.pageY - e.pageY)) >= this.options.distance
			},
			_mouseDelayMet: function () {
				return this.mouseDelayMet
			},
			_mouseStart: function () {},
			_mouseDrag: function () {},
			_mouseStop: function () {},
			_mouseCapture: function () {
				return !0
			}
		});
		(function () {
			function h(e, t, n) {
				return [parseFloat(e[0]) * (l.test(e[0]) ? t / 100 : 1), parseFloat(e[1]) * (l.test(e[1]) ? n / 100 : 1)]
			}

			function p(t, n) {
				return parseInt(e.css(t, n), 10) || 0
			}

			function d(t) {
				var n = t[0];
				return n.nodeType === 9 ? {
					width: t.width(),
					height: t.height(),
					offset: {
						top: 0,
						left: 0
					}
				} : e.isWindow(n) ? {
					width: t.width(),
					height: t.height(),
					offset: {
						top: t.scrollTop(),
						left: t.scrollLeft()
					}
				} : n.preventDefault ? {
					width: 0,
					height: 0,
					offset: {
						top: n.pageY,
						left: n.pageX
					}
				} : {
					width: t.outerWidth(),
					height: t.outerHeight(),
					offset: t.offset()
				}
			}
			e.ui = e.ui || {};
			var t, n, r = Math.max,
				i = Math.abs,
				s = Math.round,
				o = /left|center|right/,
				u = /top|center|bottom/,
				a = /[\+\-]\d+(\.[\d]+)?%?/,
				f = /^\w+/,
				l = /%$/,
				c = e.fn.position;
			e.position = {
					scrollbarWidth: function () {
						if (t !== undefined) return t;
						var n, r, i = e("<div style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>"),
							s = i.children()[0];
						return e("body").append(i), n = s.offsetWidth, i.css("overflow", "scroll"), r = s.offsetWidth, n === r && (r = i[0].clientWidth), i.remove(), t = n - r
					},
					getScrollInfo: function (t) {
						var n = t.isWindow || t.isDocument ? "" : t.element.css("overflow-x"),
							r = t.isWindow || t.isDocument ? "" : t.element.css("overflow-y"),
							i = n === "scroll" || n === "auto" && t.width < t.element[0].scrollWidth,
							s = r === "scroll" || r === "auto" && t.height < t.element[0].scrollHeight;
						return {
							width: s ? e.position.scrollbarWidth() : 0,
							height: i ? e.position.scrollbarWidth() : 0
						}
					},
					getWithinInfo: function (t) {
						var n = e(t || window),
							r = e.isWindow(n[0]),
							i = !!n[0] && n[0].nodeType === 9;
						return {
							element: n,
							isWindow: r,
							isDocument: i,
							offset: n.offset() || {
								left: 0,
								top: 0
							},
							scrollLeft: n.scrollLeft(),
							scrollTop: n.scrollTop(),
							width: r || i ? n.width() : n.outerWidth(),
							height: r || i ? n.height() : n.outerHeight()
						}
					}
				}, e.fn.position = function (t) {
					if (!t || !t.of) return c.apply(this, arguments);
					t = e.extend({}, t);
					var l, v, m, g, y, b, w = e(t.of),
						E = e.position.getWithinInfo(t.within),
						S = e.position.getScrollInfo(E),
						x = (t.collision || "flip").split(" "),
						T = {};
					return b = d(w), w[0].preventDefault && (t.at = "left top"), v = b.width, m = b.height, g = b.offset, y = e.extend({}, g), e.each(["my", "at"], function () {
						var e = (t[this] || "").split(" "),
							n, r;
						e.length === 1 && (e = o.test(e[0]) ? e.concat(["center"]) : u.test(e[0]) ? ["center"].concat(e) : ["center", "center"]), e[0] = o.test(e[0]) ? e[0] : "center", e[1] = u.test(e[1]) ? e[1] : "center", n = a.exec(e[0]), r = a.exec(e[1]), T[this] = [n ? n[0] : 0, r ? r[0] : 0], t[this] = [f.exec(e[0])[0], f.exec(e[1])[0]]
					}), x.length === 1 && (x[1] = x[0]), t.at[0] === "right" ? y.left += v : t.at[0] === "center" && (y.left += v / 2), t.at[1] === "bottom" ? y.top += m : t.at[1] === "center" && (y.top += m / 2), l = h(T.at, v, m), y.left += l[0], y.top += l[1], this.each(function () {
						var o, u, a = e(this),
							f = a.outerWidth(),
							c = a.outerHeight(),
							d = p(this, "marginLeft"),
							b = p(this, "marginTop"),
							N = f + d + p(this, "marginRight") + S.width,
							C = c + b + p(this, "marginBottom") + S.height,
							k = e.extend({}, y),
							L = h(T.my, a.outerWidth(), a.outerHeight());
						t.my[0] === "right" ? k.left -= f : t.my[0] === "center" && (k.left -= f / 2), t.my[1] === "bottom" ? k.top -= c : t.my[1] === "center" && (k.top -= c / 2), k.left += L[0], k.top += L[1], n || (k.left = s(k.left), k.top = s(k.top)), o = {
							marginLeft: d,
							marginTop: b
						}, e.each(["left", "top"], function (n, r) {
							e.ui.position[x[n]] && e.ui.position[x[n]][r](k, {
								targetWidth: v,
								targetHeight: m,
								elemWidth: f,
								elemHeight: c,
								collisionPosition: o,
								collisionWidth: N,
								collisionHeight: C,
								offset: [l[0] + L[0], l[1] + L[1]],
								my: t.my,
								at: t.at,
								within: E,
								elem: a
							})
						}), t.using && (u = function (e) {
							var n = g.left - k.left,
								s = n + v - f,
								o = g.top - k.top,
								u = o + m - c,
								l = {
									target: {
										element: w,
										left: g.left,
										top: g.top,
										width: v,
										height: m
									},
									element: {
										element: a,
										left: k.left,
										top: k.top,
										width: f,
										height: c
									},
									horizontal: s < 0 ? "left" : n > 0 ? "right" : "center",
									vertical: u < 0 ? "top" : o > 0 ? "bottom" : "middle"
								};
							v < f && i(n + s) < v && (l.horizontal = "center"), m < c && i(o + u) < m && (l.vertical = "middle"), r(i(n), i(s)) > r(i(o), i(u)) ? l.important = "horizontal" : l.important = "vertical", t.using.call(this, e, l)
						}), a.offset(e.extend(k, {
							using: u
						}))
					})
				}, e.ui.position = {
					fit: {
						left: function (e, t) {
							var n = t.within,
								i = n.isWindow ? n.scrollLeft : n.offset.left,
								s = n.width,
								o = e.left - t.collisionPosition.marginLeft,
								u = i - o,
								a = o + t.collisionWidth - s - i,
								f;
							t.collisionWidth > s ? u > 0 && a <= 0 ? (f = e.left + u + t.collisionWidth - s - i, e.left += u - f) : a > 0 && u <= 0 ? e.left = i : u > a ? e.left = i + s - t.collisionWidth : e.left = i : u > 0 ? e.left += u : a > 0 ? e.left -= a : e.left = r(e.left - o, e.left)
						},
						top: function (e, t) {
							var n = t.within,
								i = n.isWindow ? n.scrollTop : n.offset.top,
								s = t.within.height,
								o = e.top - t.collisionPosition.marginTop,
								u = i - o,
								a = o + t.collisionHeight - s - i,
								f;
							t.collisionHeight > s ? u > 0 && a <= 0 ? (f = e.top + u + t.collisionHeight - s - i, e.top += u - f) : a > 0 && u <= 0 ? e.top = i : u > a ? e.top = i + s - t.collisionHeight : e.top = i : u > 0 ? e.top += u : a > 0 ? e.top -= a : e.top = r(e.top - o, e.top)
						}
					},
					flip: {
						left: function (e, t) {
							var n = t.within,
								r = n.offset.left + n.scrollLeft,
								s = n.width,
								o = n.isWindow ? n.scrollLeft : n.offset.left,
								u = e.left - t.collisionPosition.marginLeft,
								a = u - o,
								f = u + t.collisionWidth - s - o,
								l = t.my[0] === "left" ? -t.elemWidth : t.my[0] === "right" ? t.elemWidth : 0,
								c = t.at[0] === "left" ? t.targetWidth : t.at[0] === "right" ? -t.targetWidth : 0,
								h = -2 * t.offset[0],
								p, d;
							if (a < 0) {
								p = e.left + l + c + h + t.collisionWidth - s - r;
								if (p < 0 || p < i(a)) e.left += l + c + h
							} else if (f > 0) {
								d = e.left - t.collisionPosition.marginLeft + l + c + h - o;
								if (d > 0 || i(d) < f) e.left += l + c + h
							}
						},
						top: function (e, t) {
							var n = t.within,
								r = n.offset.top + n.scrollTop,
								s = n.height,
								o = n.isWindow ? n.scrollTop : n.offset.top,
								u = e.top - t.collisionPosition.marginTop,
								a = u - o,
								f = u + t.collisionHeight - s - o,
								l = t.my[1] === "top",
								c = l ? -t.elemHeight : t.my[1] === "bottom" ? t.elemHeight : 0,
								h = t.at[1] === "top" ? t.targetHeight : t.at[1] === "bottom" ? -t.targetHeight : 0,
								p = -2 * t.offset[1],
								d, v;
							a < 0 ? (v = e.top + c + h + p + t.collisionHeight - s - r, e.top + c + h + p > a && (v < 0 || v < i(a)) && (e.top += c + h + p)) : f > 0 && (d = e.top - t.collisionPosition.marginTop + c + h + p - o, e.top + c + h + p > f && (d > 0 || i(d) < f) && (e.top += c + h + p))
						}
					},
					flipfit: {
						left: function () {
							e.ui.position.flip.left.apply(this, arguments), e.ui.position.fit.left.apply(this, arguments)
						},
						top: function () {
							e.ui.position.flip.top.apply(this, arguments), e.ui.position.fit.top.apply(this, arguments)
						}
					}
				},
				function () {
					var t, r, i, s, o, u = document.getElementsByTagName("body")[0],
						a = document.createElement("div");
					t = document.createElement(u ? "div" : "body"), i = {
						visibility: "hidden",
						width: 0,
						height: 0,
						border: 0,
						margin: 0,
						background: "none"
					}, u && e.extend(i, {
						position: "absolute",
						left: "-1000px",
						top: "-1000px"
					});
					for (o in i) t.style[o] = i[o];
					t.appendChild(a), r = u || document.documentElement, r.insertBefore(t, r.firstChild), a.style.cssText = "position: absolute; left: 10.7432222px;", s = e(a).offset().left, n = s > 10 && s < 11, t.innerHTML = "", r.removeChild(t)
				}()
		})();
		var a = e.ui.position;
		e.widget("ui.draggable", e.ui.mouse, {
			version: "1.11.2",
			widgetEventPrefix: "drag",
			options: {
				addClasses: !0,
				appendTo: "parent",
				axis: !1,
				connectToSortable: !1,
				containment: !1,
				cursor: "auto",
				cursorAt: !1,
				grid: !1,
				handle: !1,
				helper: "original",
				iframeFix: !1,
				opacity: !1,
				refreshPositions: !1,
				revert: !1,
				revertDuration: 500,
				scope: "default",
				scroll: !0,
				scrollSensitivity: 20,
				scrollSpeed: 20,
				snap: !1,
				snapMode: "both",
				snapTolerance: 20,
				stack: !1,
				zIndex: !1,
				drag: null,
				start: null,
				stop: null
			},
			_create: function () {
				this.options.helper === "original" && this._setPositionRelative(), this.options.addClasses && this.element.addClass("ui-draggable"), this.options.disabled && this.element.addClass("ui-draggable-disabled"), this._setHandleClassName(), this._mouseInit()
			},
			_setOption: function (e, t) {
				this._super(e, t), e === "handle" && (this._removeHandleClassName(), this._setHandleClassName())
			},
			_destroy: function () {
				if ((this.helper || this.element).is(".ui-draggable-dragging")) {
					this.destroyOnClear = !0;
					return
				}
				this.element.removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled"), this._removeHandleClassName(), this._mouseDestroy()
			},
			_mouseCapture: function (t) {
				var n = this.options;
				return this._blurActiveElement(t), this.helper || n.disabled || e(t.target).closest(".ui-resizable-handle").length > 0 ? !1 : e(t.target).closest(".toolbar-show").size() > 0 ? !1 : (this.handle = this._getHandle(t), this.handle ? (this._blockFrames(n.iframeFix === !0 ? "iframe" : n.iframeFix), !0) : !1)
			},
			_blockFrames: function (t) {
				this.iframeBlocks = this.document.find(t).map(function () {
					var t = e(this);
					return e("<div>").css("position", "absolute").appendTo(t.parent()).outerWidth(t.outerWidth()).outerHeight(t.outerHeight()).offset(t.offset())[0]
				})
			},
			_unblockFrames: function () {
				this.iframeBlocks && (this.iframeBlocks.remove(), delete this.iframeBlocks)
			},
			_blurActiveElement: function (t) {
				var n = this.document[0];
				if (!this.handleElement.is(t.target)) return;
				try {
					n.activeElement && n.activeElement.nodeName.toLowerCase() !== "body" && e(n.activeElement).blur()
				} catch (r) {}
			},
			_mouseStart: function (t) {
				var n = this.options;
				return this.helper = this._createHelper(t), this.helper.addClass("ui-draggable-dragging"), this._cacheHelperProportions(), e.ui.ddmanager && (e.ui.ddmanager.current = this), this._cacheMargins(), this.cssPosition = this.helper.css("position"), this.scrollParent = this.helper.scrollParent(!0), this.offsetParent = this.helper.offsetParent(), this.hasFixedAncestor = this.helper.parents().filter(function () {
					return e(this).css("position") === "fixed"
				}).length > 0, this.positionAbs = this.element.offset(), this._refreshOffsets(t), this.originalPosition = this.position = this._generatePosition(t, !1), this.originalPageX = t.pageX, this.originalPageY = t.pageY, n.cursorAt && this._adjustOffsetFromHelper(n.cursorAt), this._setContainment(), this._trigger("start", t) === !1 ? (this._clear(), !1) : (this._cacheHelperProportions(), e.ui.ddmanager && !n.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t), this._normalizeRightBottom(), this._mouseDrag(t, !0), e.ui.ddmanager && e.ui.ddmanager.dragStart(this, t), !0)
			},
			_refreshOffsets: function (e) {
				this.offset = {
					top: this.positionAbs.top - this.margins.top,
					left: this.positionAbs.left - this.margins.left,
					scroll: !1,
					parent: this._getParentOffset(),
					relative: this._getRelativeOffset()
				}, this.offset.click = {
					left: e.pageX - this.offset.left,
					top: e.pageY - this.offset.top
				}
			},
			_mouseDrag: function (t, n) {
				this.hasFixedAncestor && (this.offset.parent = this._getParentOffset()), this.position = this._generatePosition(t, !0), this.positionAbs = this._convertPositionTo("absolute");
				if (!n) {
					var r = this._uiHash();
					if (this._trigger("drag", t, r) === !1) return this._mouseUp({}), !1;
					this.position = r.position
				}
				return this.helper[0].style.left = this.position.left + "px", this.helper[0].style.top = this.position.top + "px", e.ui.ddmanager && e.ui.ddmanager.drag(this, t), !1
			},
			_mouseStop: function (t) {
				var n = this,
					r = !1;
				return e.ui.ddmanager && !this.options.dropBehaviour && (r = e.ui.ddmanager.drop(this, t)), this.dropped && (r = this.dropped, this.dropped = !1), this.options.revert === "invalid" && !r || this.options.revert === "valid" && r || this.options.revert === !0 || e.isFunction(this.options.revert) && this.options.revert.call(this.element, r) ? e(this.helper).animate(this.originalPosition, parseInt(this.options.revertDuration, 10), function () {
					n._trigger("stop", t) !== !1 && n._clear()
				}) : this._trigger("stop", t) !== !1 && this._clear(), !1
			},
			_mouseUp: function (t) {
				return this._unblockFrames(), e.ui.ddmanager && e.ui.ddmanager.dragStop(this, t), this.handleElement.is(t.target) && this.element.focus(), e.ui.mouse.prototype._mouseUp.call(this, t)
			},
			cancel: function () {
				return this.helper.is(".ui-draggable-dragging") ? this._mouseUp({}) : this._clear(), this
			},
			_getHandle: function (t) {
				return this.options.handle ? !!e(t.target).closest(this.element.find(this.options.handle)).length : !0
			},
			_setHandleClassName: function () {
				this.handleElement = this.options.handle ? this.element.find(this.options.handle) : this.element, this.handleElement.addClass("ui-draggable-handle")
			},
			_removeHandleClassName: function () {
				this.handleElement.removeClass("ui-draggable-handle")
			},
			_createHelper: function (t) {
				var n = this.options,
					r = e.isFunction(n.helper),
					i = r ? e(n.helper.apply(this.element[0], [t])) : n.helper === "clone" ? this.element.clone().removeAttr("id") : this.element;
				return i.parents("body").length || i.appendTo(n.appendTo === "parent" ? this.element[0].parentNode : n.appendTo), r && i[0] === this.element[0] && this._setPositionRelative(), i[0] !== this.element[0] && !/(fixed|absolute)/.test(i.css("position")) && i.css("position", "absolute"), i
			},
			_setPositionRelative: function () {
				/^(?:r|a|f)/.test(this.element.css("position")) || (this.element[0].style.position = "relative")
			},
			_adjustOffsetFromHelper: function (t) {
				typeof t == "string" && (t = t.split(" ")), e.isArray(t) && (t = {
					left: +t[0],
					top: +t[1] || 0
				}), "left" in t && (this.offset.click.left = t.left + this.margins.left), "right" in t && (this.offset.click.left = this.helperProportions.width - t.right + this.margins.left), "top" in t && (this.offset.click.top = t.top + this.margins.top), "bottom" in t && (this.offset.click.top = this.helperProportions.height - t.bottom + this.margins.top)
			},
			_isRootNode: function (e) {
				return /(html|body)/i.test(e.tagName) || e === this.document[0]
			},
			_getParentOffset: function () {
				var t = this.offsetParent.offset(),
					n = this.document[0];
				return this.cssPosition === "absolute" && this.scrollParent[0] !== n && e.contains(this.scrollParent[0], this.offsetParent[0]) && (t.left += this.scrollParent.scrollLeft(), t.top += this.scrollParent.scrollTop()), this._isRootNode(this.offsetParent[0]) && (t = {
					top: 0,
					left: 0
				}), {
					top: t.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
					left: t.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
				}
			},
			_getRelativeOffset: function () {
				if (this.cssPosition !== "relative") return {
					top: 0,
					left: 0
				};
				var e = this.element.position(),
					t = this._isRootNode(this.scrollParent[0]);
				return {
					top: e.top - (parseInt(this.helper.css("top"), 10) || 0) + (t ? 0 : this.scrollParent.scrollTop()),
					left: e.left - (parseInt(this.helper.css("left"), 10) || 0) + (t ? 0 : this.scrollParent.scrollLeft())
				}
			},
			_cacheMargins: function () {
				this.margins = {
					left: parseInt(this.element.css("marginLeft"), 10) || 0,
					top: parseInt(this.element.css("marginTop"), 10) || 0,
					right: parseInt(this.element.css("marginRight"), 10) || 0,
					bottom: parseInt(this.element.css("marginBottom"), 10) || 0
				}
			},
			_cacheHelperProportions: function () {
				this.helperProportions = {
					width: this.helper.outerWidth(),
					height: this.helper.outerHeight()
				}
			},
			_setContainment: function () {
				var t, n, r, i = this.options,
					s = this.document[0];
				this.relativeContainer = null;
				if (!i.containment) {
					this.containment = null;
					return
				}
				if (i.containment === "window") {
					this.containment = [e(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left, e(window).scrollTop() - this.offset.relative.top - this.offset.parent.top, e(window).scrollLeft() + e(window).width() - this.helperProportions.width - this.margins.left, e(window).scrollTop() + (e(window).height() || s.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top];
					return
				}
				if (i.containment === "document") {
					this.containment = [0, 0, e(s).width() - this.helperProportions.width - this.margins.left, (e(s).height() || s.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top];
					return
				}
				if (i.containment.constructor === Array) {
					this.containment = i.containment;
					return
				}
				i.containment === "parent" && (i.containment = this.helper[0].parentNode), n = e(i.containment), r = n[0];
				if (!r) return;
				t = /(scroll|auto)/.test(n.css("overflow")), this.containment = [(parseInt(n.css("borderLeftWidth"), 10) || 0) + (parseInt(n.css("paddingLeft"), 10) || 0), (parseInt(n.css("borderTopWidth"), 10) || 0) + (parseInt(n.css("paddingTop"), 10) || 0), (t ? Math.max(r.scrollWidth, r.offsetWidth) : r.offsetWidth) - (parseInt(n.css("borderRightWidth"), 10) || 0) - (parseInt(n.css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left - this.margins.right, (t ? Math.max(r.scrollHeight, r.offsetHeight) : r.offsetHeight) - (parseInt(n.css("borderBottomWidth"), 10) || 0) - (parseInt(n.css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top - this.margins.bottom], this.relativeContainer = n
			},
			_convertPositionTo: function (e, t) {
				t || (t = this.position);
				var n = e === "absolute" ? 1 : -1,
					r = this._isRootNode(this.scrollParent[0]);
				return {
					top: t.top + this.offset.relative.top * n + this.offset.parent.top * n - (this.cssPosition === "fixed" ? -this.offset.scroll.top : r ? 0 : this.offset.scroll.top) * n,
					left: t.left + this.offset.relative.left * n + this.offset.parent.left * n - (this.cssPosition === "fixed" ? -this.offset.scroll.left : r ? 0 : this.offset.scroll.left) * n
				}
			},
			_generatePosition: function (e, t) {
				var n, r, i, s, o = this.options,
					u = this._isRootNode(this.scrollParent[0]),
					a = e.pageX,
					f = e.pageY;
				if (!u || !this.offset.scroll) this.offset.scroll = {
					top: this.scrollParent.scrollTop(),
					left: this.scrollParent.scrollLeft()
				};
				return t && (this.containment && (this.relativeContainer ? (r = this.relativeContainer.offset(), n = [this.containment[0] + r.left, this.containment[1] + r.top, this.containment[2] + r.left, this.containment[3] + r.top]) : n = this.containment, e.pageX - this.offset.click.left < n[0] && (a = n[0] + this.offset.click.left), e.pageY - this.offset.click.top < n[1] && (f = n[1] + this.offset.click.top), e.pageX - this.offset.click.left > n[2] && (a = n[2] + this.offset.click.left), e.pageY - this.offset.click.top > n[3] && (f = n[3] + this.offset.click.top)), o.grid && (i = o.grid[1] ? this.originalPageY + Math.round((f - this.originalPageY) / o.grid[1]) * o.grid[1] : this.originalPageY, f = n ? i - this.offset.click.top >= n[1] || i - this.offset.click.top > n[3] ? i : i - this.offset.click.top >= n[1] ? i - o.grid[1] : i + o.grid[1] : i, s = o.grid[0] ? this.originalPageX + Math.round((a - this.originalPageX) / o.grid[0]) * o.grid[0] : this.originalPageX, a = n ? s - this.offset.click.left >= n[0] || s - this.offset.click.left > n[2] ? s : s - this.offset.click.left >= n[0] ? s - o.grid[0] : s + o.grid[0] : s), o.axis === "y" && (a = this.originalPageX), o.axis === "x" && (f = this.originalPageY)), {
					top: f - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + (this.cssPosition === "fixed" ? -this.offset.scroll.top : u ? 0 : this.offset.scroll.top),
					left: a - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + (this.cssPosition === "fixed" ? -this.offset.scroll.left : u ? 0 : this.offset.scroll.left)
				}
			},
			_clear: function () {
				this.helper.removeClass("ui-draggable-dragging"), this.helper[0] !== this.element[0] && !this.cancelHelperRemoval && this.helper.remove(), this.helper = null, this.cancelHelperRemoval = !1, this.destroyOnClear && this.destroy()
			},
			_normalizeRightBottom: function () {
				this.options.axis !== "y" && this.helper.css("right") !== "auto" && (this.helper.width(this.helper.width()), this.helper.css("right", "auto")), this.options.axis !== "x" && this.helper.css("bottom") !== "auto" && (this.helper.height(this.helper.height()), this.helper.css("bottom", "auto"))
			},
			_trigger: function (t, n, r) {
				return r = r || this._uiHash(), e.ui.plugin.call(this, t, [n, r, this], !0), /^(drag|start|stop)/.test(t) && (this.positionAbs = this._convertPositionTo("absolute"), r.offset = this.positionAbs), e.Widget.prototype._trigger.call(this, t, n, r)
			},
			plugins: {},
			_uiHash: function () {
				return {
					helper: this.helper,
					position: this.position,
					originalPosition: this.originalPosition,
					offset: this.positionAbs
				}
			}
		}), e.ui.plugin.add("draggable", "connectToSortable", {
			start: function (t, n, r) {
				var i = e.extend({}, n, {
					item: r.element
				});
				r.sortables = [], e(r.options.connectToSortable).each(function () {
					var n = e(this).sortable("instance");
					n && !n.options.disabled && (r.sortables.push(n), n.refreshPositions(), n._trigger("activate", t, i))
				})
			},
			stop: function (t, n, r) {
				var i = e.extend({}, n, {
					item: r.element
				});
				r.cancelHelperRemoval = !1, e.each(r.sortables, function () {
					var e = this;
					e.isOver ? (e.isOver = 0, r.cancelHelperRemoval = !0, e.cancelHelperRemoval = !1, e._storedCSS = {
						position: e.placeholder.css("position"),
						top: e.placeholder.css("top"),
						left: e.placeholder.css("left")
					}, e._mouseStop(t), e.options.helper = e.options._helper) : (e.cancelHelperRemoval = !0, e._trigger("deactivate", t, i))
				})
			},
			drag: function (t, n, r) {
				e.each(r.sortables, function () {
					var i = !1,
						s = this;
					s.positionAbs = r.positionAbs, s.helperProportions = r.helperProportions, s.offset.click = r.offset.click, s._intersectsWith(s.containerCache) && (i = !0, e.each(r.sortables, function () {
						return this.positionAbs = r.positionAbs, this.helperProportions = r.helperProportions, this.offset.click = r.offset.click, this !== s && this._intersectsWith(this.containerCache) && e.contains(s.element[0], this.element[0]) && (i = !1), i
					})), i ? (s.isOver || (s.isOver = 1, s.currentItem = n.helper.appendTo(s.element).data("ui-sortable-item", !0), s.options._helper = s.options.helper, s.options.helper = function () {
						return n.helper[0]
					}, t.target = s.currentItem[0], s._mouseCapture(t, !0), s._mouseStart(t, !0, !0), s.offset.click.top = r.offset.click.top, s.offset.click.left = r.offset.click.left, s.offset.parent.left -= r.offset.parent.left - s.offset.parent.left, s.offset.parent.top -= r.offset.parent.top - s.offset.parent.top, r._trigger("toSortable", t), r.dropped = s.element, e.each(r.sortables, function () {
						this.refreshPositions()
					}), r.currentItem = r.element, s.fromOutside = r), s.currentItem && (s._mouseDrag(t), n.position = s.position)) : s.isOver && (s.isOver = 0, s.cancelHelperRemoval = !0, s.options._revert = s.options.revert, s.options.revert = !1, s._trigger("out", t, s._uiHash(s)), s._mouseStop(t, !0), s.options.revert = s.options._revert, s.options.helper = s.options._helper, s.placeholder && s.placeholder.remove(), r._refreshOffsets(t), n.position = r._generatePosition(t, !0), r._trigger("fromSortable", t), r.dropped = !1, e.each(r.sortables, function () {
						this.refreshPositions()
					}))
				})
			}
		}), e.ui.plugin.add("draggable", "cursor", {
			start: function (t, n, r) {
				var i = e("body"),
					s = r.options;
				i.css("cursor") && (s._cursor = i.css("cursor")), i.css("cursor", s.cursor)
			},
			stop: function (t, n, r) {
				var i = r.options;
				i._cursor && e("body").css("cursor", i._cursor)
			}
		}), e.ui.plugin.add("draggable", "opacity", {
			start: function (t, n, r) {
				var i = e(n.helper),
					s = r.options;
				i.css("opacity") && (s._opacity = i.css("opacity")), i.css("opacity", s.opacity)
			},
			stop: function (t, n, r) {
				var i = r.options;
				i._opacity && e(n.helper).css("opacity", i._opacity)
			}
		}), e.ui.plugin.add("draggable", "scroll", {
			start: function (e, t, n) {
				n.scrollParentNotHidden || (n.scrollParentNotHidden = n.helper.scrollParent(!1)), n.scrollParentNotHidden[0] !== n.document[0] && n.scrollParentNotHidden[0].tagName !== "HTML" && (n.overflowOffset = n.scrollParentNotHidden.offset())
			},
			drag: function (t, n, r) {
				var i = r.options,
					s = !1,
					o = r.scrollParentNotHidden[0],
					u = r.document[0];
				if (o !== u && o.tagName !== "HTML") {
					if (!i.axis || i.axis !== "x") r.overflowOffset.top + o.offsetHeight - t.pageY < i.scrollSensitivity ? o.scrollTop = s = o.scrollTop + i.scrollSpeed : t.pageY - r.overflowOffset.top < i.scrollSensitivity && (o.scrollTop = s = o.scrollTop - i.scrollSpeed);
					if (!i.axis || i.axis !== "y") r.overflowOffset.left + o.offsetWidth - t.pageX < i.scrollSensitivity ? o.scrollLeft = s = o.scrollLeft + i.scrollSpeed : t.pageX - r.overflowOffset.left < i.scrollSensitivity && (o.scrollLeft = s = o.scrollLeft - i.scrollSpeed)
				} else {
					if (!i.axis || i.axis !== "x") t.pageY - e(u).scrollTop() < i.scrollSensitivity ? s = e(u).scrollTop(e(u).scrollTop() - i.scrollSpeed) : e(window).height() - (t.pageY - e(u).scrollTop()) < i.scrollSensitivity && (s = e(u).scrollTop(e(u).scrollTop() + i.scrollSpeed));
					if (!i.axis || i.axis !== "y") t.pageX - e(u).scrollLeft() < i.scrollSensitivity ? s = e(u).scrollLeft(e(u).scrollLeft() - i.scrollSpeed) : e(window).width() - (t.pageX - e(u).scrollLeft()) < i.scrollSensitivity && (s = e(u).scrollLeft(e(u).scrollLeft() + i.scrollSpeed))
				}
				s !== !1 && e.ui.ddmanager && !i.dropBehaviour && e.ui.ddmanager.prepareOffsets(r, t)
			}
		}), e.ui.plugin.add("draggable", "snap", {
			start: function (t, n, r) {
				var i = r.options;
				r.snapElements = [], e(i.snap.constructor !== String ? i.snap.items || ":data(ui-draggable)" : i.snap).each(function () {
					var t = e(this),
						n = t.offset();
					this !== r.element[0] && r.snapElements.push({
						item: this,
						width: t.outerWidth(),
						height: t.outerHeight(),
						top: n.top,
						left: n.left
					})
				})
			},
			drag: function (t, n, r) {
				var i, s, o, u, a, f, l, c, h, p, d = r.options,
					v = d.snapTolerance,
					m = n.offset.left,
					g = m + r.helperProportions.width,
					y = n.offset.top,
					b = y + r.helperProportions.height;
				for (h = r.snapElements.length - 1; h >= 0; h--) {
					a = r.snapElements[h].left - r.margins.left, f = a + r.snapElements[h].width, l = r.snapElements[h].top - r.margins.top, c = l + r.snapElements[h].height;
					if (g < a - v || m > f + v || b < l - v || y > c + v || !e.contains(r.snapElements[h].item.ownerDocument, r.snapElements[h].item)) {
						r.snapElements[h].snapping && r.options.snap.release && r.options.snap.release.call(r.element, t, e.extend(r._uiHash(), {
							snapItem: r.snapElements[h].item
						})), r.snapElements[h].snapping = !1;
						continue
					}
					d.snapMode !== "inner" && (i = Math.abs(l - b) <= v, s = Math.abs(c - y) <= v, o = Math.abs(a - g) <= v, u = Math.abs(f - m) <= v, i && (n.position.top = r._convertPositionTo("relative", {
						top: l - r.helperProportions.height,
						left: 0
					}).top), s && (n.position.top = r._convertPositionTo("relative", {
						top: c,
						left: 0
					}).top), o && (n.position.left = r._convertPositionTo("relative", {
						top: 0,
						left: a - r.helperProportions.width
					}).left), u && (n.position.left = r._convertPositionTo("relative", {
						top: 0,
						left: f
					}).left)), p = i || s || o || u, d.snapMode !== "outer" && (i = Math.abs(l - y) <= v, s = Math.abs(c - b) <= v, o = Math.abs(a - m) <= v, u = Math.abs(f - g) <= v, i && (n.position.top = r._convertPositionTo("relative", {
						top: l,
						left: 0
					}).top), s && (n.position.top = r._convertPositionTo("relative", {
						top: c - r.helperProportions.height,
						left: 0
					}).top), o && (n.position.left = r._convertPositionTo("relative", {
						top: 0,
						left: a
					}).left), u && (n.position.left = r._convertPositionTo("relative", {
						top: 0,
						left: f - r.helperProportions.width
					}).left)), !r.snapElements[h].snapping && (i || s || o || u || p) && r.options.snap.snap && r.options.snap.snap.call(r.element, t, e.extend(r._uiHash(), {
						snapItem: r.snapElements[h].item
					})), r.snapElements[h].snapping = i || s || o || u || p
				}
			}
		}), e.ui.plugin.add("draggable", "stack", {
			start: function (t, n, r) {
				var i, s = r.options,
					o = e.makeArray(e(s.stack)).sort(function (t, n) {
						return (parseInt(e(t).css("zIndex"), 10) || 0) - (parseInt(e(n).css("zIndex"), 10) || 0)
					});
				if (!o.length) return;
				i = parseInt(e(o[0]).css("zIndex"), 10) || 0, e(o).each(function (t) {
					e(this).css("zIndex", i + t)
				}), this.css("zIndex", i + o.length)
			}
		}), e.ui.plugin.add("draggable", "zIndex", {
			start: function (t, n, r) {
				var i = e(n.helper),
					s = r.options;
				i.css("zIndex") && (s._zIndex = i.css("zIndex")), i.css("zIndex", s.zIndex)
			},
			stop: function (t, n, r) {
				var i = r.options;
				i._zIndex && e(n.helper).css("zIndex", i._zIndex)
			}
		});
		var f = e.ui.draggable;
		e.widget("ui.droppable", {
			version: "1.11.2",
			widgetEventPrefix: "drop",
			options: {
				accept: "*",
				activeClass: !1,
				addClasses: !0,
				greedy: !1,
				hoverClass: !1,
				scope: "default",
				tolerance: "intersect",
				activate: null,
				deactivate: null,
				drop: null,
				out: null,
				over: null
			},
			_create: function () {
				var t, n = this.options,
					r = n.accept;
				this.isover = !1, this.isout = !0, this.accept = e.isFunction(r) ? r : function (e) {
					return e.is(r)
				}, this.proportions = function () {
					if (!arguments.length) return t ? t : t = {
						width: this.element[0].offsetWidth,
						height: this.element[0].offsetHeight
					};
					t = arguments[0]
				}, this._addToManager(n.scope), n.addClasses && this.element.addClass("ui-droppable")
			},
			_addToManager: function (t) {
				e.ui.ddmanager.droppables[t] = e.ui.ddmanager.droppables[t] || [], e.ui.ddmanager.droppables[t].push(this)
			},
			_splice: function (e) {
				var t = 0;
				for (; t < e.length; t++) e[t] === this && e.splice(t, 1)
			},
			_destroy: function () {
				var t = e.ui.ddmanager.droppables[this.options.scope];
				this._splice(t), this.element.removeClass("ui-droppable ui-droppable-disabled")
			},
			_setOption: function (t, n) {
				if (t === "accept") this.accept = e.isFunction(n) ? n : function (e) {
					return e.is(n)
				};
				else if (t === "scope") {
					var r = e.ui.ddmanager.droppables[this.options.scope];
					this._splice(r), this._addToManager(n)
				}
				this._super(t, n)
			},
			_activate: function (t) {
				var n = e.ui.ddmanager.current;
				this.options.activeClass && this.element.addClass(this.options.activeClass), n && this._trigger("activate", t, this.ui(n))
			},
			_deactivate: function (t) {
				var n = e.ui.ddmanager.current;
				this.options.activeClass && this.element.removeClass(this.options.activeClass), n && this._trigger("deactivate", t, this.ui(n))
			},
			_over: function (t) {
				var n = e.ui.ddmanager.current;
				if (!n || (n.currentItem || n.element)[0] === this.element[0]) return;
				this.accept.call(this.element[0], n.currentItem || n.element) && (this.options.hoverClass && this.element.addClass(this.options.hoverClass), this._trigger("over", t, this.ui(n)))
			},
			_out: function (t) {
				var n = e.ui.ddmanager.current;
				if (!n || (n.currentItem || n.element)[0] === this.element[0]) return;
				this.accept.call(this.element[0], n.currentItem || n.element) && (this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("out", t, this.ui(n)))
			},
			_drop: function (t, n) {
				var r = n || e.ui.ddmanager.current,
					i = !1;
				return !r || (r.currentItem || r.element)[0] === this.element[0] ? !1 : (this.element.find(":data(ui-droppable)").not(".ui-draggable-dragging").each(function () {
					var n = e(this).droppable("instance");
					if (n.options.greedy && !n.options.disabled && n.options.scope === r.options.scope && n.accept.call(n.element[0], r.currentItem || r.element) && e.ui.intersect(r, e.extend(n, {
							offset: n.element.offset()
						}), n.options.tolerance, t)) return i = !0, !1
				}), i ? !1 : this.accept.call(this.element[0], r.currentItem || r.element) ? (this.options.activeClass && this.element.removeClass(this.options.activeClass), this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("drop", t, this.ui(r)), this.element) : !1)
			},
			ui: function (e) {
				return {
					draggable: e.currentItem || e.element,
					helper: e.helper,
					position: e.position,
					offset: e.positionAbs
				}
			}
		}), e.ui.intersect = function () {
			function e(e, t, n) {
				return e >= t && e < t + n
			}
			return function (t, n, r, i) {
				if (!n.offset) return !1;
				var s = (t.positionAbs || t.position.absolute).left + t.margins.left,
					o = (t.positionAbs || t.position.absolute).top + t.margins.top,
					u = s + t.helperProportions.width,
					a = o + t.helperProportions.height,
					f = n.offset.left,
					l = n.offset.top,
					c = f + n.proportions().width,
					h = l + n.proportions().height;
				switch (r) {
				case "fit":
					return f <= s && u <= c && l <= o && a <= h;
				case "intersect":
					return f < s + t.helperProportions.width / 2 && u - t.helperProportions.width / 2 < c && l < o + t.helperProportions.height / 2 && a - t.helperProportions.height / 2 < h;
				case "pointer":
					return e(i.pageY, l, n.proportions().height) && e(i.pageX, f, n.proportions().width);
				case "touch":
					return (o >= l && o <= h || a >= l && a <= h || o < l && a > h) && (s >= f && s <= c || u >= f && u <= c || s < f && u > c);
				default:
					return !1
				}
			}
		}(), e.ui.ddmanager = {
			current: null,
			droppables: {
				"default": []
			},
			prepareOffsets: function (t, n) {
				var r, i, s = e.ui.ddmanager.droppables[t.options.scope] || [],
					o = n ? n.type : null,
					u = (t.currentItem || t.element).find(":data(ui-droppable)").addBack();
				e: for (r = 0; r < s.length; r++) {
					if (s[r].options.disabled || t && !s[r].accept.call(s[r].element[0], t.currentItem || t.element)) continue;
					for (i = 0; i < u.length; i++)
						if (u[i] === s[r].element[0]) {
							s[r].proportions().height = 0;
							continue e
						}
					s[r].visible = s[r].element.css("display") !== "none";
					if (!s[r].visible) continue;
					o === "mousedown" && s[r]._activate.call(s[r], n), s[r].offset = s[r].element.offset(), s[r].proportions({
						width: s[r].element[0].offsetWidth,
						height: s[r].element[0].offsetHeight
					})
				}
			},
			drop: function (t, n) {
				var r = !1;
				return e.each((e.ui.ddmanager.droppables[t.options.scope] || []).slice(), function () {
					if (!this.options) return;
					!this.options.disabled && this.visible && e.ui.intersect(t, this, this.options.tolerance, n) && (r = this._drop.call(this, n) || r), !this.options.disabled && this.visible && this.accept.call(this.element[0], t.currentItem || t.element) && (this.isout = !0, this.isover = !1, this._deactivate.call(this, n))
				}), r
			},
			dragStart: function (t, n) {
				t.element.parentsUntil("body").bind("scroll.droppable", function () {
					t.options.refreshPositions || e.ui.ddmanager.prepareOffsets(t, n)
				})
			},
			drag: function (t, n) {
				t.options.refreshPositions && e.ui.ddmanager.prepareOffsets(t, n), e.each(e.ui.ddmanager.droppables[t.options.scope] || [], function () {
					if (this.options.disabled || this.greedyChild || !this.visible) return;
					var r, i, s, o = e.ui.intersect(t, this, this.options.tolerance, n),
						u = !o && this.isover ? "isout" : o && !this.isover ? "isover" : null;
					if (!u) return;
					this.options.greedy && (i = this.options.scope, s = this.element.parents(":data(ui-droppable)").filter(function () {
						return e(this).droppable("instance").options.scope === i
					}), s.length && (r = e(s[0]).droppable("instance"), r.greedyChild = u === "isover")), r && u === "isover" && (r.isover = !1, r.isout = !0, r._out.call(r, n)), this[u] = !0, this[u === "isout" ? "isover" : "isout"] = !1, this[u === "isover" ? "_over" : "_out"].call(this, n), r && u === "isout" && (r.isout = !1, r.isover = !0, r._over.call(r, n))
				})
			},
			dragStop: function (t, n) {
				t.element.parentsUntil("body").unbind("scroll.droppable"), t.options.refreshPositions || e.ui.ddmanager.prepareOffsets(t, n)
			}
		};
		var l = e.ui.droppable;
		e.widget("ui.resizable", e.ui.mouse, {
			version: "1.11.2",
			widgetEventPrefix: "resize",
			options: {
				alsoResize: !1,
				animate: !1,
				animateDuration: "slow",
				animateEasing: "swing",
				aspectRatio: !1,
				autoHide: !1,
				containment: !1,
				ghost: !1,
				grid: !1,
				handles: "e,s,se",
				helper: !1,
				maxHeight: null,
				maxWidth: null,
				minHeight: 10,
				minWidth: 10,
				zIndex: 90,
				resize: null,
				start: null,
				stop: null
			},
			_num: function (e) {
				return parseInt(e, 10) || 0
			},
			_isNumber: function (e) {
				return !isNaN(parseInt(e, 10))
			},
			_hasScroll: function (t, n) {
				if (e(t).css("overflow") === "hidden") return !1;
				var r = n && n === "left" ? "scrollLeft" : "scrollTop",
					i = !1;
				return t[r] > 0 ? !0 : (t[r] = 1, i = t[r] > 0, t[r] = 0, i)
			},
			_create: function () {
				var t, n, r, i, s, o = this,
					u = this.options;
				this.element.addClass("ui-resizable"), e.extend(this, {
					_aspectRatio: !!u.aspectRatio,
					aspectRatio: u.aspectRatio,
					originalElement: this.element,
					_proportionallyResizeElements: [],
					_helper: u.helper || u.ghost || u.animate ? u.helper || "ui-resizable-helper" : null
				}), this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i) && (this.element.wrap(e("<div class='ui-wrapper' style='overflow: hidden;'></div>").css({
					position: this.element.css("position"),
					width: this.element.outerWidth(),
					height: this.element.outerHeight(),
					top: this.element.css("top"),
					left: this.element.css("left")
				})), this.element = this.element.parent().data("ui-resizable", this.element.resizable("instance")), this.elementIsWrapper = !0, this.element.css({
					marginLeft: this.originalElement.css("marginLeft"),
					marginTop: this.originalElement.css("marginTop"),
					marginRight: this.originalElement.css("marginRight"),
					marginBottom: this.originalElement.css("marginBottom")
				}), this.originalElement.css({
					marginLeft: 0,
					marginTop: 0,
					marginRight: 0,
					marginBottom: 0
				}), this.originalResizeStyle = this.originalElement.css("resize"), this.originalElement.css("resize", "none"), this._proportionallyResizeElements.push(this.originalElement.css({
					position: "static",
					zoom: 1,
					display: "block"
				})), this.originalElement.css({
					margin: this.originalElement.css("margin")
				}), this._proportionallyResize()), this.handles = u.handles || (e(".ui-resizable-handle", this.element).length ? {
					n: ".ui-resizable-n",
					e: ".ui-resizable-e",
					s: ".ui-resizable-s",
					w: ".ui-resizable-w",
					se: ".ui-resizable-se",
					sw: ".ui-resizable-sw",
					ne: ".ui-resizable-ne",
					nw: ".ui-resizable-nw"
				} : "e,s,se");
				if (this.handles.constructor === String) {
					this.handles === "all" && (this.handles = "n,e,s,w,se,sw,ne,nw"), t = this.handles.split(","), this.handles = {};
					for (n = 0; n < t.length; n++) r = e.trim(t[n]), s = "ui-resizable-" + r, i = e("<div class='ui-resizable-handle " + s + "'></div>"), i.css({
						zIndex: u.zIndex
					}), "se" === r && i.addClass("ui-icon ui-icon-gripsmall-diagonal-se"), this.handles[r] = ".ui-resizable-" + r, this.element.append(i)
				}
				this._renderAxis = function (t) {
					var n, r, i, s;
					t = t || this.element;
					for (n in this.handles) {
						this.handles[n].constructor === String && (this.handles[n] = this.element.children(this.handles[n]).first().show()), this.elementIsWrapper && this.originalElement[0].nodeName.match(/textarea|input|select|button/i) && (r = e(this.handles[n], this.element), s = /sw|ne|nw|se|n|s/.test(n) ? r.outerHeight() : r.outerWidth(), i = ["padding", /ne|nw|n/.test(n) ? "Top" : /se|sw|s/.test(n) ? "Bottom" : /^e$/.test(n) ? "Right" : "Left"].join(""), t.css(i, s), this._proportionallyResize());
						if (!e(this.handles[n]).length) continue
					}
				}, this._renderAxis(this.element), this._handles = e(".ui-resizable-handle", this.element).disableSelection(), this._handles.mouseover(function () {
					o.resizing || (this.className && (i = this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i)), o.axis = i && i[1] ? i[1] : "se")
				}), u.autoHide && (this._handles.hide(), e(this.element).addClass("ui-resizable-autohide").mouseenter(function () {
					if (u.disabled) return;
					e(this).removeClass("ui-resizable-autohide"), o._handles.show()
				}).mouseleave(function () {
					if (u.disabled) return;
					o.resizing || (e(this).addClass("ui-resizable-autohide"), o._handles.hide())
				})), this._mouseInit()
			},
			_destroy: function () {
				this._mouseDestroy();
				var t, n = function (t) {
					e(t).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").removeData("ui-resizable").unbind(".resizable").find(".ui-resizable-handle").remove()
				};
				return this.elementIsWrapper && (n(this.element), t = this.element, this.originalElement.css({
					position: t.css("position"),
					width: t.outerWidth(),
					height: t.outerHeight(),
					top: t.css("top"),
					left: t.css("left")
				}).insertAfter(t), t.remove()), this.originalElement.css("resize", this.originalResizeStyle), n(this.originalElement), this
			},
			_mouseCapture: function (t) {
				var n, r, i = !1;
				for (n in this.handles) {
					r = e(this.handles[n])[0];
					if (r === t.target || e.contains(r, t.target)) i = !0
				}
				return !this.options.disabled && i
			},
			_mouseStart: function (t) {
				var n, r, i, s = this.options,
					o = this.element;
				return this.resizing = !0, this._renderProxy(), n = this._num(this.helper.css("left")), r = this._num(this.helper.css("top")), s.containment && (n += e(s.containment).scrollLeft() || 0, r += e(s.containment).scrollTop() || 0), this.offset = this.helper.offset(), this.position = {
					left: n,
					top: r
				}, this.size = this._helper ? {
					width: this.helper.width(),
					height: this.helper.height()
				} : {
					width: o.width(),
					height: o.height()
				}, this.originalSize = this._helper ? {
					width: o.outerWidth(),
					height: o.outerHeight()
				} : {
					width: o.width(),
					height: o.height()
				}, this.sizeDiff = {
					width: o.outerWidth() - o.width(),
					height: o.outerHeight() - o.height()
				}, this.originalPosition = {
					left: n,
					top: r
				}, this.originalMousePosition = {
					left: t.pageX,
					top: t.pageY
				}, this.aspectRatio = typeof s.aspectRatio == "number" ? s.aspectRatio : this.originalSize.width / this.originalSize.height || 1, i = e(".ui-resizable-" + this.axis).css("cursor"), e("body").css("cursor", i === "auto" ? this.axis + "-resize" : i), o.addClass("ui-resizable-resizing"), this._propagate("start", t), !0
			},
			_mouseDrag: function (t) {
				var n, r, i = this.originalMousePosition,
					s = this.axis,
					o = t.pageX - i.left || 0,
					u = t.pageY - i.top || 0,
					a = this._change[s];
				this._updatePrevProperties();
				if (!a) return !1;
				n = a.apply(this, [t, o, u]), this._updateVirtualBoundaries(t.shiftKey);
				if (this._aspectRatio || t.shiftKey) n = this._updateRatio(n, t);
				return n = this._respectSize(n, t), this._updateCache(n), this._propagate("resize", t), r = this._applyChanges(), !this._helper && this._proportionallyResizeElements.length && this._proportionallyResize(), e.isEmptyObject(r) || (this._updatePrevProperties(), this._trigger("resize", t, this.ui()), this._applyChanges()), !1
			},
			_mouseStop: function (t) {
				this.resizing = !1;
				var n, r, i, s, o, u, a, f = this.options,
					l = this;
				return this._helper && (n = this._proportionallyResizeElements, r = n.length && /textarea/i.test(n[0].nodeName), i = r && this._hasScroll(n[0], "left") ? 0 : l.sizeDiff.height, s = r ? 0 : l.sizeDiff.width, o = {
					width: l.helper.width() - s,
					height: l.helper.height() - i
				}, u = parseInt(l.element.css("left"), 10) + (l.position.left - l.originalPosition.left) || null, a = parseInt(l.element.css("top"), 10) + (l.position.top - l.originalPosition.top) || null, f.animate || this.element.css(e.extend(o, {
					top: a,
					left: u
				})), l.helper.height(l.size.height), l.helper.width(l.size.width), this._helper && !f.animate && this._proportionallyResize()), e("body").css("cursor", "auto"), this.element.removeClass("ui-resizable-resizing"), this._propagate("stop", t), this._helper && this.helper.remove(), !1
			},
			_updatePrevProperties: function () {
				this.prevPosition = {
					top: this.position.top,
					left: this.position.left
				}, this.prevSize = {
					width: this.size.width,
					height: this.size.height
				}
			},
			_applyChanges: function () {
				var e = {};
				return this.position.top !== this.prevPosition.top && (e.top = this.position.top + "px"), this.position.left !== this.prevPosition.left && (e.left = this.position.left + "px"), this.size.width !== this.prevSize.width && (e.width = this.size.width + "px"), this.size.height !== this.prevSize.height && (e.height = this.size.height + "px"), this.helper.css(e), e
			},
			_updateVirtualBoundaries: function (e) {
				var t, n, r, i, s, o = this.options;
				s = {
					minWidth: this._isNumber(o.minWidth) ? o.minWidth : 0,
					maxWidth: this._isNumber(o.maxWidth) ? o.maxWidth : Infinity,
					minHeight: this._isNumber(o.minHeight) ? o.minHeight : 0,
					maxHeight: this._isNumber(o.maxHeight) ? o.maxHeight : Infinity
				};
				if (this._aspectRatio || e) t = s.minHeight * this.aspectRatio, r = s.minWidth / this.aspectRatio, n = s.maxHeight * this.aspectRatio, i = s.maxWidth / this.aspectRatio, t > s.minWidth && (s.minWidth = t), r > s.minHeight && (s.minHeight = r), n < s.maxWidth && (s.maxWidth = n), i < s.maxHeight && (s.maxHeight = i);
				this._vBoundaries = s
			},
			_updateCache: function (e) {
				this.offset = this.helper.offset(), this._isNumber(e.left) && (this.position.left = e.left), this._isNumber(e.top) && (this.position.top = e.top), this._isNumber(e.height) && (this.size.height = e.height), this._isNumber(e.width) && (this.size.width = e.width)
			},
			_updateRatio: function (e) {
				var t = this.position,
					n = this.size,
					r = this.axis;
				return this._isNumber(e.height) ? e.width = e.height * this.aspectRatio : this._isNumber(e.width) && (e.height = e.width / this.aspectRatio), r === "sw" && (e.left = t.left + (n.width - e.width), e.top = null), r === "nw" && (e.top = t.top + (n.height - e.height), e.left = t.left + (n.width - e.width)), e
			},
			_respectSize: function (e) {
				var t = this._vBoundaries,
					n = this.axis,
					r = this._isNumber(e.width) && t.maxWidth && t.maxWidth < e.width,
					i = this._isNumber(e.height) && t.maxHeight && t.maxHeight < e.height,
					s = this._isNumber(e.width) && t.minWidth && t.minWidth > e.width,
					o = this._isNumber(e.height) && t.minHeight && t.minHeight > e.height,
					u = this.originalPosition.left + this.originalSize.width,
					a = this.position.top + this.size.height,
					f = /sw|nw|w/.test(n),
					l = /nw|ne|n/.test(n);
				return s && (e.width = t.minWidth), o && (e.height = t.minHeight), r && (e.width = t.maxWidth), i && (e.height = t.maxHeight), s && f && (e.left = u - t.minWidth), r && f && (e.left = u - t.maxWidth), o && l && (e.top = a - t.minHeight), i && l && (e.top = a - t.maxHeight), !e.width && !e.height && !e.left && e.top ? e.top = null : !e.width && !e.height && !e.top && e.left && (e.left = null), e
			},
			_getPaddingPlusBorderDimensions: function (e) {
				var t = 0,
					n = [],
					r = [e.css("borderTopWidth"), e.css("borderRightWidth"), e.css("borderBottomWidth"), e.css("borderLeftWidth")],
					i = [e.css("paddingTop"), e.css("paddingRight"), e.css("paddingBottom"), e.css("paddingLeft")];
				for (; t < 4; t++) n[t] = parseInt(r[t], 10) || 0, n[t] += parseInt(i[t], 10) || 0;
				return {
					height: n[0] + n[2],
					width: n[1] + n[3]
				}
			},
			_proportionallyResize: function () {
				if (!this._proportionallyResizeElements.length) return;
				var e, t = 0,
					n = this.helper || this.element;
				for (; t < this._proportionallyResizeElements.length; t++) e = this._proportionallyResizeElements[t], this.outerDimensions || (this.outerDimensions = this._getPaddingPlusBorderDimensions(e)), e.css({
					height: n.height() - this.outerDimensions.height || 0,
					width: n.width() - this.outerDimensions.width || 0
				})
			},
			_renderProxy: function () {
				var t = this.element,
					n = this.options;
				this.elementOffset = t.offset(), this._helper ? (this.helper = this.helper || e("<div style='overflow:hidden;'></div>"), this.helper.addClass(this._helper).css({
					width: this.element.outerWidth() - 1,
					height: this.element.outerHeight() - 1,
					position: "absolute",
					left: this.elementOffset.left + "px",
					top: this.elementOffset.top + "px",
					zIndex: ++n.zIndex
				}), this.helper.appendTo("body").disableSelection()) : this.helper = this.element
			},
			_change: {
				e: function (e, t) {
					return {
						width: this.originalSize.width + t
					}
				},
				w: function (e, t) {
					var n = this.originalSize,
						r = this.originalPosition;
					return {
						left: r.left + t,
						width: n.width - t
					}
				},
				n: function (e, t, n) {
					var r = this.originalSize,
						i = this.originalPosition;
					return {
						top: i.top + n,
						height: r.height - n
					}
				},
				s: function (e, t, n) {
					return {
						height: this.originalSize.height + n
					}
				},
				se: function (t, n, r) {
					return e.extend(this._change.s.apply(this, arguments), this._change.e.apply(this, [t, n, r]))
				},
				sw: function (t, n, r) {
					return e.extend(this._change.s.apply(this, arguments), this._change.w.apply(this, [t, n, r]))
				},
				ne: function (t, n, r) {
					return e.extend(this._change.n.apply(this, arguments), this._change.e.apply(this, [t, n, r]))
				},
				nw: function (t, n, r) {
					return e.extend(this._change.n.apply(this, arguments), this._change.w.apply(this, [t, n, r]))
				}
			},
			_propagate: function (t, n) {
				e.ui.plugin.call(this, t, [n, this.ui()]), t !== "resize" && this._trigger(t, n, this.ui())
			},
			plugins: {},
			ui: function () {
				return {
					originalElement: this.originalElement,
					element: this.element,
					helper: this.helper,
					position: this.position,
					size: this.size,
					originalSize: this.originalSize,
					originalPosition: this.originalPosition
				}
			}
		}), e.ui.plugin.add("resizable", "animate", {
			stop: function (t) {
				var n = e(this).resizable("instance"),
					r = n.options,
					i = n._proportionallyResizeElements,
					s = i.length && /textarea/i.test(i[0].nodeName),
					o = s && n._hasScroll(i[0], "left") ? 0 : n.sizeDiff.height,
					u = s ? 0 : n.sizeDiff.width,
					a = {
						width: n.size.width - u,
						height: n.size.height - o
					},
					f = parseInt(n.element.css("left"), 10) + (n.position.left - n.originalPosition.left) || null,
					l = parseInt(n.element.css("top"), 10) + (n.position.top - n.originalPosition.top) || null;
				n.element.animate(e.extend(a, l && f ? {
					top: l,
					left: f
				} : {}), {
					duration: r.animateDuration,
					easing: r.animateEasing,
					step: function () {
						var r = {
							width: parseInt(n.element.css("width"), 10),
							height: parseInt(n.element.css("height"), 10),
							top: parseInt(n.element.css("top"), 10),
							left: parseInt(n.element.css("left"), 10)
						};
						i && i.length && e(i[0]).css({
							width: r.width,
							height: r.height
						}), n._updateCache(r), n._propagate("resize", t)
					}
				})
			}
		}), e.ui.plugin.add("resizable", "containment", {
			start: function () {
				var t, n, r, i, s, o, u, a = e(this).resizable("instance"),
					f = a.options,
					l = a.element,
					c = f.containment,
					h = c instanceof e ? c.get(0) : /parent/.test(c) ? l.parent().get(0) : c;
				if (!h) return;
				a.containerElement = e(h), /document/.test(c) || c === document ? (a.containerOffset = {
					left: 0,
					top: 0
				}, a.containerPosition = {
					left: 0,
					top: 0
				}, a.parentData = {
					element: e(document),
					left: 0,
					top: 0,
					width: e(document).width(),
					height: e(document).height() || document.body.parentNode.scrollHeight
				}) : (t = e(h), n = [], e(["Top", "Right", "Left", "Bottom"]).each(function (e, r) {
					n[e] = a._num(t.css("padding" + r))
				}), a.containerOffset = t.offset(), a.containerPosition = t.position(), a.containerSize = {
					height: t.innerHeight() - n[3],
					width: t.innerWidth() - n[1]
				}, r = a.containerOffset, i = a.containerSize.height, s = a.containerSize.width, o = a._hasScroll(h, "left") ? h.scrollWidth : s, u = a._hasScroll(h) ? h.scrollHeight : i, a.parentData = {
					element: h,
					left: r.left,
					top: r.top,
					width: o,
					height: u
				})
			},
			resize: function (t) {
				var n, r, i, s, o = e(this).resizable("instance"),
					u = o.options,
					a = o.containerOffset,
					f = o.position,
					l = o._aspectRatio || t.shiftKey,
					c = {
						top: 0,
						left: 0
					},
					h = o.containerElement,
					p = !0;
				h[0] !== document && /static/.test(h.css("position")) && (c = a), f.left < (o._helper ? a.left : 0) && (o.size.width = o.size.width + (o._helper ? o.position.left - a.left : o.position.left - c.left), l && (o.size.height = o.size.width / o.aspectRatio, p = !1), o.position.left = u.helper ? a.left : 0), f.top < (o._helper ? a.top : 0) && (o.size.height = o.size.height + (o._helper ? o.position.top - a.top : o.position.top), l && (o.size.width = o.size.height * o.aspectRatio, p = !1), o.position.top = o._helper ? a.top : 0), i = o.containerElement.get(0) === o.element.parent().get(0), s = /relative|absolute/.test(o.containerElement.css("position")), i && s ? (o.offset.left = o.parentData.left + o.position.left, o.offset.top = o.parentData.top + o.position.top) : (o.offset.left = o.element.offset().left, o.offset.top = o.element.offset().top), n = Math.abs(o.sizeDiff.width + (o._helper ? o.offset.left - c.left : o.offset.left - a.left)), r = Math.abs(o.sizeDiff.height + (o._helper ? o.offset.top - c.top : o.offset.top - a.top)), n + o.size.width >= o.parentData.width && (o.size.width = o.parentData.width - n, l && (o.size.height = o.size.width / o.aspectRatio, p = !1)), r + o.size.height >= o.parentData.height && (o.size.height = o.parentData.height - r, l && (o.size.width = o.size.height * o.aspectRatio, p = !1)), p || (o.position.left = o.prevPosition.left, o.position.top = o.prevPosition.top, o.size.width = o.prevSize.width, o.size.height = o.prevSize.height)
			},
			stop: function () {
				var t = e(this).resizable("instance"),
					n = t.options,
					r = t.containerOffset,
					i = t.containerPosition,
					s = t.containerElement,
					o = e(t.helper),
					u = o.offset(),
					a = o.outerWidth() - t.sizeDiff.width,
					f = o.outerHeight() - t.sizeDiff.height;
				t._helper && !n.animate && /relative/.test(s.css("position")) && e(this).css({
					left: u.left - i.left - r.left,
					width: a,
					height: f
				}), t._helper && !n.animate && /static/.test(s.css("position")) && e(this).css({
					left: u.left - i.left - r.left,
					width: a,
					height: f
				})
			}
		}), e.ui.plugin.add("resizable", "alsoResize", {
			start: function () {
				var t = e(this).resizable("instance"),
					n = t.options,
					r = function (t) {
						e(t).each(function () {
							var t = e(this);
							t.data("ui-resizable-alsoresize", {
								width: parseInt(t.width(), 10),
								height: parseInt(t.height(), 10),
								left: parseInt(t.css("left"), 10),
								top: parseInt(t.css("top"), 10)
							})
						})
					};
				typeof n.alsoResize == "object" && !n.alsoResize.parentNode ? n.alsoResize.length ? (n.alsoResize = n.alsoResize[0], r(n.alsoResize)) : e.each(n.alsoResize, function (e) {
					r(e)
				}) : r(n.alsoResize)
			},
			resize: function (t, n) {
				var r = e(this).resizable("instance"),
					i = r.options,
					s = r.originalSize,
					o = r.originalPosition,
					u = {
						height: r.size.height - s.height || 0,
						width: r.size.width - s.width || 0,
						top: r.position.top - o.top || 0,
						left: r.position.left - o.left || 0
					},
					a = function (t, r) {
						e(t).each(function () {
							var t = e(this),
								i = e(this).data("ui-resizable-alsoresize"),
								s = {},
								o = r && r.length ? r : t.parents(n.originalElement[0]).length ? ["width", "height"] : ["width", "height", "top", "left"];
							e.each(o, function (e, t) {
								var n = (i[t] || 0) + (u[t] || 0);
								n && n >= 0 && (s[t] = n || null)
							}), t.css(s)
						})
					};
				typeof i.alsoResize == "object" && !i.alsoResize.nodeType ? e.each(i.alsoResize, function (e, t) {
					a(e, t)
				}) : a(i.alsoResize)
			},
			stop: function () {
				e(this).removeData("resizable-alsoresize")
			}
		}), e.ui.plugin.add("resizable", "ghost", {
			start: function () {
				var t = e(this).resizable("instance"),
					n = t.options,
					r = t.size;
				t.ghost = t.originalElement.clone(), t.ghost.css({
					opacity: .25,
					display: "block",
					position: "relative",
					height: r.height,
					width: r.width,
					margin: 0,
					left: 0,
					top: 0
				}).addClass("ui-resizable-ghost").addClass(typeof n.ghost == "string" ? n.ghost : ""), t.ghost.appendTo(t.helper)
			},
			resize: function () {
				var t = e(this).resizable("instance");
				t.ghost && t.ghost.css({
					position: "relative",
					height: t.size.height,
					width: t.size.width
				})
			},
			stop: function () {
				var t = e(this).resizable("instance");
				t.ghost && t.helper && t.helper.get(0).removeChild(t.ghost.get(0))
			}
		}), e.ui.plugin.add("resizable", "grid", {
			resize: function () {
				var t, n = e(this).resizable("instance"),
					r = n.options,
					i = n.size,
					s = n.originalSize,
					o = n.originalPosition,
					u = n.axis,
					a = typeof r.grid == "number" ? [r.grid, r.grid] : r.grid,
					f = a[0] || 1,
					l = a[1] || 1,
					c = Math.round((i.width - s.width) / f) * f,
					h = Math.round((i.height - s.height) / l) * l,
					p = s.width + c,
					d = s.height + h,
					v = r.maxWidth && r.maxWidth < p,
					m = r.maxHeight && r.maxHeight < d,
					g = r.minWidth && r.minWidth > p,
					y = r.minHeight && r.minHeight > d;
				r.grid = a, g && (p += f), y && (d += l), v && (p -= f), m && (d -= l);
				if (/^(se|s|e)$/.test(u)) n.size.width = p, n.size.height = d;
				else if (/^(ne)$/.test(u)) n.size.width = p, n.size.height = d, n.position.top = o.top - h;
				else if (/^(sw)$/.test(u)) n.size.width = p, n.size.height = d, n.position.left = o.left - c;
				else {
					if (d - l <= 0 || p - f <= 0) t = n._getPaddingPlusBorderDimensions(this);
					d - l > 0 ? (n.size.height = d, n.position.top = o.top - h) : (d = l - t.height, n.size.height = d, n.position.top = o.top + s.height - d), p - f > 0 ? (n.size.width = p, n.position.left = o.left - c) : (p = l - t.height, n.size.width = p, n.position.left = o.left + s.width - p)
				}
			}
		});
		var c = e.ui.resizable,
			h = e.widget("ui.selectable", e.ui.mouse, {
				version: "1.11.2",
				options: {
					appendTo: "body",
					autoRefresh: !0,
					distance: 0,
					filter: "*",
					tolerance: "touch",
					selected: null,
					selecting: null,
					start: null,
					stop: null,
					unselected: null,
					unselecting: null
				},
				_create: function () {
					var t, n = this;
					this.element.addClass("ui-selectable"), this.dragged = !1, this.refresh = function () {
						t = e(n.options.filter, n.element[0]), t.addClass("ui-selectee"), t.each(function () {
							var t = e(this),
								n = t.offset();
							e.data(this, "selectable-item", {
								element: this,
								$element: t,
								left: n.left,
								top: n.top,
								right: n.left + t.outerWidth(),
								bottom: n.top + t.outerHeight(),
								startselected: !1,
								selected: t.hasClass("ui-selected"),
								selecting: t.hasClass("ui-selecting"),
								unselecting: t.hasClass("ui-unselecting")
							})
						})
					}, this.refresh(), this.selectees = t.addClass("ui-selectee"), this._mouseInit(), this.helper = e("<div class='ui-selectable-helper'></div>")
				},
				_destroy: function () {
					this.selectees.removeClass("ui-selectee").removeData("selectable-item"), this.element.removeClass("ui-selectable ui-selectable-disabled"), this._mouseDestroy()
				},
				_mouseStart: function (t) {
					var n = this,
						r = this.options;
					this.opos = [t.pageX, t.pageY];
					if (this.options.disabled) return;
					this.selectees = e(r.filter, this.element[0]), this._trigger("start", t), e(r.appendTo).append(this.helper), this.helper.css({
						left: t.pageX,
						top: t.pageY,
						width: 0,
						height: 0
					}), r.autoRefresh && this.refresh(), this.selectees.filter(".ui-selected").each(function () {
						var r = e.data(this, "selectable-item");
						r.startselected = !0, !t.metaKey && !t.ctrlKey && (r.$element.removeClass("ui-selected"), r.selected = !1, r.$element.addClass("ui-unselecting"), r.unselecting = !0, n._trigger("unselecting", t, {
							unselecting: r.element
						}))
					}), e(t.target).parents().addBack().each(function () {
						var r, i = e.data(this, "selectable-item");
						if (i) return r = !t.metaKey && !t.ctrlKey || !i.$element.hasClass("ui-selected"), i.$element.removeClass(r ? "ui-unselecting" : "ui-selected").addClass(r ? "ui-selecting" : "ui-unselecting"), i.unselecting = !r, i.selecting = r, i.selected = r, r ? n._trigger("selecting", t, {
							selecting: i.element
						}) : n._trigger("unselecting", t, {
							unselecting: i.element
						}), !1
					})
				},
				_mouseDrag: function (t) {
					this.dragged = !0;
					if (this.options.disabled) return;
					var n, r = this,
						i = this.options,
						s = this.opos[0],
						o = this.opos[1],
						u = t.pageX,
						a = t.pageY;
					return s > u && (n = u, u = s, s = n), o > a && (n = a, a = o, o = n), this.helper.css({
						left: s,
						top: o,
						width: u - s,
						height: a - o
					}), this.selectees.each(function () {
						var n = e.data(this, "selectable-item"),
							f = !1;
						if (!n || n.element === r.element[0]) return;
						i.tolerance === "touch" ? f = !(n.left > u || n.right < s || n.top > a || n.bottom < o) : i.tolerance === "fit" && (f = n.left > s && n.right < u && n.top > o && n.bottom < a), f ? (n.selected && (n.$element.removeClass("ui-selected"), n.selected = !1), n.unselecting && (n.$element.removeClass("ui-unselecting"), n.unselecting = !1), n.selecting || (n.$element.addClass("ui-selecting"), n.selecting = !0, r._trigger("selecting", t, {
							selecting: n.element
						}))) : (n.selecting && ((t.metaKey || t.ctrlKey) && n.startselected ? (n.$element.removeClass("ui-selecting"), n.selecting = !1, n.$element.addClass("ui-selected"), n.selected = !0) : (n.$element.removeClass("ui-selecting"), n.selecting = !1, n.startselected && (n.$element.addClass("ui-unselecting"), n.unselecting = !0), r._trigger("unselecting", t, {
							unselecting: n.element
						}))), n.selected && !t.metaKey && !t.ctrlKey && !n.startselected && (n.$element.removeClass("ui-selected"), n.selected = !1, n.$element.addClass("ui-unselecting"), n.unselecting = !0, r._trigger("unselecting", t, {
							unselecting: n.element
						})))
					}), !1
				},
				_mouseStop: function (t) {
					var n = this;
					return this.dragged = !1, e(".ui-unselecting", this.element[0]).each(function () {
						var r = e.data(this, "selectable-item");
						r.$element.removeClass("ui-unselecting"), r.unselecting = !1, r.startselected = !1, n._trigger("unselected", t, {
							unselected: r.element
						})
					}), e(".ui-selecting", this.element[0]).each(function () {
						var r = e.data(this, "selectable-item");
						r.$element.removeClass("ui-selecting").addClass("ui-selected"), r.selecting = !1, r.selected = !0, r.startselected = !0, n._trigger("selected", t, {
							selected: r.element
						})
					}), this._trigger("stop", t), this.helper.remove(), !1
				}
			}),
			p = e.widget("ui.sortable", e.ui.mouse, {
				version: "1.11.2",
				widgetEventPrefix: "sort",
				ready: !1,
				options: {
					appendTo: "parent",
					axis: !1,
					connectWith: !1,
					containment: !1,
					cursor: "auto",
					cursorAt: !1,
					dropOnEmpty: !0,
					forcePlaceholderSize: !1,
					forceHelperSize: !1,
					grid: !1,
					handle: !1,
					helper: "original",
					items: "> *",
					opacity: !1,
					placeholder: !1,
					revert: !1,
					scroll: !0,
					scrollSensitivity: 20,
					scrollSpeed: 20,
					scope: "default",
					tolerance: "intersect",
					zIndex: 1e3,
					activate: null,
					beforeDrop: null,
					beforeStop: null,
					change: null,
					deactivate: null,
					out: null,
					over: null,
					receive: null,
					remove: null,
					sort: null,
					start: null,
					stop: null,
					update: null
				},
				_isOverAxis: function (e, t, n) {
					return e >= t && e < t + n
				},
				_isFloating: function (e) {
					return /left|right/.test(e.css("float")) || /inline|table-cell/.test(e.css("display"))
				},
				_create: function () {
					var e = this.options;
					this.containerCache = {}, this.element.addClass("ui-sortable"), this.refresh(), this.floating = this.items.length ? e.axis === "x" || this._isFloating(this.items[0].item) : !1, this.offset = this.element.offset(), this._mouseInit(), this._setHandleClassName(), this.ready = !0
				},
				_setOption: function (e, t) {
					this._super(e, t), e === "handle" && this._setHandleClassName()
				},
				_setHandleClassName: function () {
					this.element.find(".ui-sortable-handle").removeClass("ui-sortable-handle"), e.each(this.items, function () {
						(this.instance.options.handle ? this.item.find(this.instance.options.handle) : this.item).addClass("ui-sortable-handle")
					})
				},
				_destroy: function () {
					this.element.removeClass("ui-sortable ui-sortable-disabled").find(".ui-sortable-handle").removeClass("ui-sortable-handle"), this._mouseDestroy();
					for (var e = this.items.length - 1; e >= 0; e--) this.items[e].item.removeData(this.widgetName + "-item");
					return this
				},
				_mouseCapture: function (t, n) {
					var r = null,
						i = !1,
						s = this;
					if (this.reverting) return !1;
					if (this.options.disabled || this.options.type === "static") return !1;
					this._refreshItems(t), e(t.target).parents().each(function () {
						if (e.data(this, s.widgetName + "-item") === s) return r = e(this), !1
					}), e.data(t.target, s.widgetName + "-item") === s && (r = e(t.target));
					if (!r) return !1;
					if (this.options.handle && !n) {
						e(this.options.handle, r).find("*").addBack().each(function () {
							this === t.target && (i = !0)
						});
						if (!i) return !1
					}
					return this.currentItem = r, this._removeCurrentsFromItems(), !0
				},
				_mouseStart: function (t, n, r) {
					var i, s, o = this.options;
					this.currentContainer = this, this.refreshPositions(), this.helper = this._createHelper(t), this._cacheHelperProportions(), this._cacheMargins(), this.scrollParent = this.helper.scrollParent(), this.offset = this.currentItem.offset(), this.offset = {
						top: this.offset.top - this.margins.top,
						left: this.offset.left - this.margins.left
					}, e.extend(this.offset, {
						click: {
							left: t.pageX - this.offset.left,
							top: t.pageY - this.offset.top
						},
						parent: this._getParentOffset(),
						relative: this._getRelativeOffset()
					}), this.helper.css("position", "absolute"), this.cssPosition = this.helper.css("position"), this.originalPosition = this._generatePosition(t), this.originalPageX = t.pageX, this.originalPageY = t.pageY, o.cursorAt && this._adjustOffsetFromHelper(o.cursorAt), this.domPosition = {
						prev: this.currentItem.prev()[0],
						parent: this.currentItem.parent()[0]
					}, this.helper[0] !== this.currentItem[0] && this.currentItem.hide(), this._createPlaceholder(), o.containment && this._setContainment(), o.cursor && o.cursor !== "auto" && (s = this.document.find("body"), this.storedCursor = s.css("cursor"), s.css("cursor", o.cursor), this.storedStylesheet = e("<style>*{ cursor: " + o.cursor + " !important; }</style>").appendTo(s)), o.opacity && (this.helper.css("opacity") && (this._storedOpacity = this.helper.css("opacity")), this.helper.css("opacity", o.opacity)), o.zIndex && (this.helper.css("zIndex") && (this._storedZIndex = this.helper.css("zIndex")), this.helper.css("zIndex", o.zIndex)), this.scrollParent[0] !== document && this.scrollParent[0].tagName !== "HTML" && (this.overflowOffset = this.scrollParent.offset()), this._trigger("start", t, this._uiHash()), this._preserveHelperProportions || this._cacheHelperProportions();
					if (!r)
						for (i = this.containers.length - 1; i >= 0; i--) this.containers[i]._trigger("activate", t, this._uiHash(this));
					return e.ui.ddmanager && (e.ui.ddmanager.current = this), e.ui.ddmanager && !o.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t), this.dragging = !0, this.helper.addClass("ui-sortable-helper"), this._mouseDrag(t), !0
				},
				_mouseDrag: function (t) {
					var n, r, i, s, o = this.options,
						u = !1;
					this.position = this._generatePosition(t), this.positionAbs = this._convertPositionTo("absolute"), this.lastPositionAbs || (this.lastPositionAbs = this.positionAbs), this.options.scroll && (this.scrollParent[0] !== document && this.scrollParent[0].tagName !== "HTML" ? (this.overflowOffset.top + this.scrollParent[0].offsetHeight - t.pageY < o.scrollSensitivity ? this.scrollParent[0].scrollTop = u = this.scrollParent[0].scrollTop + o.scrollSpeed : t.pageY - this.overflowOffset.top < o.scrollSensitivity && (this.scrollParent[0].scrollTop = u = this.scrollParent[0].scrollTop - o.scrollSpeed), this.overflowOffset.left + this.scrollParent[0].offsetWidth - t.pageX < o.scrollSensitivity ? this.scrollParent[0].scrollLeft = u = this.scrollParent[0].scrollLeft + o.scrollSpeed : t.pageX - this.overflowOffset.left < o.scrollSensitivity && (this.scrollParent[0].scrollLeft = u = this.scrollParent[0].scrollLeft - o.scrollSpeed)) : (t.pageY - e(document).scrollTop() < o.scrollSensitivity ? u = e(document).scrollTop(e(document).scrollTop() - o.scrollSpeed) : e(window).height() - (t.pageY - e(document).scrollTop()) < o.scrollSensitivity && (u = e(document).scrollTop(e(document).scrollTop() + o.scrollSpeed)), t.pageX - e(document).scrollLeft() < o.scrollSensitivity ? u = e(document).scrollLeft(e(document).scrollLeft() - o.scrollSpeed) : e(window).width() - (t.pageX - e(document).scrollLeft()) < o.scrollSensitivity && (u = e(document).scrollLeft(e(document).scrollLeft() + o.scrollSpeed))), u !== !1 && e.ui.ddmanager && !o.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t)), this.positionAbs = this._convertPositionTo("absolute");
					if (!this.options.axis || this.options.axis !== "y") this.helper[0].style.left = this.position.left + "px";
					if (!this.options.axis || this.options.axis !== "x") this.helper[0].style.top = this.position.top + "px";
					for (n = this.items.length - 1; n >= 0; n--) {
						r = this.items[n], i = r.item[0], s = this._intersectsWithPointer(r);
						if (!s) continue;
						if (r.instance !== this.currentContainer) continue;
						if (i !== this.currentItem[0] && this.placeholder[s === 1 ? "next" : "prev"]()[0] !== i && !e.contains(this.placeholder[0], i) && (this.options.type === "semi-dynamic" ? !e.contains(this.element[0], i) : !0)) {
							this.direction = s === 1 ? "down" : "up";
							if (this.options.tolerance !== "pointer" && !this._intersectsWithSides(r)) break;
							this._rearrange(t, r), this._trigger("change", t, this._uiHash());
							break
						}
					}
					return this._contactContainers(t), e.ui.ddmanager && e.ui.ddmanager.drag(this, t), this._trigger("sort", t, this._uiHash()), this.lastPositionAbs = this.positionAbs, !1
				},
				_mouseStop: function (t, n) {
					if (!t) return;
					e.ui.ddmanager && !this.options.dropBehaviour && e.ui.ddmanager.drop(this, t);
					if (this.options.revert) {
						var r = this,
							i = this.placeholder.offset(),
							s = this.options.axis,
							o = {};
						if (!s || s === "x") o.left = i.left - this.offset.parent.left - this.margins.left + (this.offsetParent[0] === document.body ? 0 : this.offsetParent[0].scrollLeft);
						if (!s || s === "y") o.top = i.top - this.offset.parent.top - this.margins.top + (this.offsetParent[0] === document.body ? 0 : this.offsetParent[0].scrollTop);
						this.reverting = !0, e(this.helper).animate(o, parseInt(this.options.revert, 10) || 500, function () {
							r._clear(t)
						})
					} else this._clear(t, n);
					return !1
				},
				cancel: function () {
					if (this.dragging) {
						this._mouseUp({
							target: null
						}), this.options.helper === "original" ? this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper") : this.currentItem.show();
						for (var t = this.containers.length - 1; t >= 0; t--) this.containers[t]._trigger("deactivate", null, this._uiHash(this)), this.containers[t].containerCache.over && (this.containers[t]._trigger("out", null, this._uiHash(this)), this.containers[t].containerCache.over = 0)
					}
					return this.placeholder && (this.placeholder[0].parentNode && this.placeholder[0].parentNode.removeChild(this.placeholder[0]), this.options.helper !== "original" && this.helper && this.helper[0].parentNode && this.helper.remove(), e.extend(this, {
						helper: null,
						dragging: !1,
						reverting: !1,
						_noFinalSort: null
					}), this.domPosition.prev ? e(this.domPosition.prev).after(this.currentItem) : e(this.domPosition.parent).prepend(this.currentItem)), this
				},
				serialize: function (t) {
					var n = this._getItemsAsjQuery(t && t.connected),
						r = [];
					return t = t || {}, e(n).each(function () {
						var n = (e(t.item || this).attr(t.attribute || "id") || "").match(t.expression || /(.+)[\-=_](.+)/);
						n && r.push((t.key || n[1] + "[]") + "=" + (t.key && t.expression ? n[1] : n[2]))
					}), !r.length && t.key && r.push(t.key + "="), r.join("&")
				},
				toArray: function (t) {
					var n = this._getItemsAsjQuery(t && t.connected),
						r = [];
					return t = t || {}, n.each(function () {
						r.push(e(t.item || this).attr(t.attribute || "id") || "")
					}), r
				},
				_intersectsWith: function (e) {
					var t = this.positionAbs.left,
						n = t + this.helperProportions.width,
						r = this.positionAbs.top,
						i = r + this.helperProportions.height,
						s = e.left,
						o = s + e.width,
						u = e.top,
						a = u + e.height,
						f = this.offset.click.top,
						l = this.offset.click.left,
						c = this.options.axis === "x" || r + f > u && r + f < a,
						h = this.options.axis === "y" || t + l > s && t + l < o,
						p = c && h;
					return this.options.tolerance === "pointer" || this.options.forcePointerForContainers || this.options.tolerance !== "pointer" && this.helperProportions[this.floating ? "width" : "height"] > e[this.floating ? "width" : "height"] ? p : s < t + this.helperProportions.width / 2 && n - this.helperProportions.width / 2 < o && u < r + this.helperProportions.height / 2 && i - this.helperProportions.height / 2 < a
				},
				_intersectsWithPointer: function (e) {
					var t = this.options.axis === "x" || this._isOverAxis(this.positionAbs.top + this.offset.click.top, e.top, e.height),
						n = this.options.axis === "y" || this._isOverAxis(this.positionAbs.left + this.offset.click.left, e.left, e.width),
						r = t && n,
						i = this._getDragVerticalDirection(),
						s = this._getDragHorizontalDirection();
					return r ? this.floating ? s && s === "right" || i === "down" ? 2 : 1 : i && (i === "down" ? 2 : 1) : !1
				},
				_intersectsWithSides: function (e) {
					var t = this._isOverAxis(this.positionAbs.top + this.offset.click.top, e.top + e.height / 2, e.height),
						n = this._isOverAxis(this.positionAbs.left + this.offset.click.left, e.left + e.width / 2, e.width),
						r = this._getDragVerticalDirection(),
						i = this._getDragHorizontalDirection();
					return this.floating && i ? i === "right" && n || i === "left" && !n : r && (r === "down" && t || r === "up" && !t)
				},
				_getDragVerticalDirection: function () {
					var e = this.positionAbs.top - this.lastPositionAbs.top;
					return e !== 0 && (e > 0 ? "down" : "up")
				},
				_getDragHorizontalDirection: function () {
					var e = this.positionAbs.left - this.lastPositionAbs.left;
					return e !== 0 && (e > 0 ? "right" : "left")
				},
				refresh: function (e) {
					return this._refreshItems(e), this._setHandleClassName(), this.refreshPositions(), this
				},
				_connectWith: function () {
					var e = this.options;
					return e.connectWith.constructor === String ? [e.connectWith] : e.connectWith
				},
				_getItemsAsjQuery: function (t) {
					function f() {
						o.push(this)
					}
					var n, r, i, s, o = [],
						u = [],
						a = this._connectWith();
					if (a && t)
						for (n = a.length - 1; n >= 0; n--) {
							i = e(a[n]);
							for (r = i.length - 1; r >= 0; r--) s = e.data(i[r], this.widgetFullName), s && s !== this && !s.options.disabled && u.push([e.isFunction(s.options.items) ? s.options.items.call(s.element) : e(s.options.items, s.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), s])
						}
					u.push([e.isFunction(this.options.items) ? this.options.items.call(this.element, null, {
						options: this.options,
						item: this.currentItem
					}) : e(this.options.items, this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), this]);
					for (n = u.length - 1; n >= 0; n--) u[n][0].each(f);
					return e(o)
				},
				_removeCurrentsFromItems: function () {
					var t = this.currentItem.find(":data(" + this.widgetName + "-item)");
					this.items = e.grep(this.items, function (e) {
						for (var n = 0; n < t.length; n++)
							if (t[n] === e.item[0]) return !1;
						return !0
					})
				},
				_refreshItems: function (t) {
					this.items = [], this.containers = [this];
					var n, r, i, s, o, u, a, f, l = this.items,
						c = [[e.isFunction(this.options.items) ? this.options.items.call(this.element[0], t, {
							item: this.currentItem
						}) : e(this.options.items, this.element), this]],
						h = this._connectWith();
					if (h && this.ready)
						for (n = h.length - 1; n >= 0; n--) {
							i = e(h[n]);
							for (r = i.length - 1; r >= 0; r--) s = e.data(i[r], this.widgetFullName), s && s !== this && !s.options.disabled && (c.push([e.isFunction(s.options.items) ? s.options.items.call(s.element[0], t, {
								item: this.currentItem
							}) : e(s.options.items, s.element), s]), this.containers.push(s))
						}
					for (n = c.length - 1; n >= 0; n--) {
						o = c[n][1], u = c[n][0];
						for (r = 0, f = u.length; r < f; r++) a = e(u[r]), a.data(this.widgetName + "-item", o), l.push({
							item: a,
							instance: o,
							width: 0,
							height: 0,
							left: 0,
							top: 0
						})
					}
				},
				refreshPositions: function (t) {
					this.offsetParent && this.helper && (this.offset.parent = this._getParentOffset());
					var n, r, i, s;
					for (n = this.items.length - 1; n >= 0; n--) {
						r = this.items[n];
						if (r.instance !== this.currentContainer && this.currentContainer && r.item[0] !== this.currentItem[0]) continue;
						i = this.options.toleranceElement ? e(this.options.toleranceElement, r.item) : r.item, t || (r.width = i.outerWidth(), r.height = i.outerHeight()), s = i.offset(), r.left = s.left, r.top = s.top
					}
					if (this.options.custom && this.options.custom.refreshContainers) this.options.custom.refreshContainers.call(this);
					else
						for (n = this.containers.length - 1; n >= 0; n--) s = this.containers[n].element.offset(), this.containers[n].containerCache.left = s.left, this.containers[n].containerCache.top = s.top, this.containers[n].containerCache.width = this.containers[n].element.outerWidth(), this.containers[n].containerCache.height = this.containers[n].element.outerHeight();
					return this
				},
				_createPlaceholder: function (t) {
					t = t || this;
					var n, r = t.options;
					if (!r.placeholder || r.placeholder.constructor === String) n = r.placeholder, r.placeholder = {
						element: function () {
							var r = t.currentItem[0].nodeName.toLowerCase(),
								i = e("<" + r + ">", t.document[0]).addClass(n || t.currentItem[0].className + " ui-sortable-placeholder").removeClass("ui-sortable-helper");
							return r === "tr" ? t.currentItem.children().each(function () {
								e("<td>&#160;</td>", t.document[0]).attr("colspan", e(this).attr("colspan") || 1).appendTo(i)
							}) : r === "img" && i.attr("src", t.currentItem.attr("src")), n || i.css("visibility", "hidden"), i
						},
						update: function (e, i) {
							if (n && !r.forcePlaceholderSize) return;
							i.height() || i.height(t.currentItem.innerHeight() - parseInt(t.currentItem.css("paddingTop") || 0, 10) - parseInt(t.currentItem.css("paddingBottom") || 0, 10)), i.width() || i.width(t.currentItem.innerWidth() - parseInt(t.currentItem.css("paddingLeft") || 0, 10) - parseInt(t.currentItem.css("paddingRight") || 0, 10))
						}
					};
					t.placeholder = e(r.placeholder.element.call(t.element, t.currentItem)), t.currentItem.after(t.placeholder), r.placeholder.update(t, t.placeholder)
				},
				_contactContainers: function (t) {
					var n, r, i, s, o, u, a, f, l, c, h = null,
						p = null;
					for (n = this.containers.length - 1; n >= 0; n--) {
						if (e.contains(this.currentItem[0], this.containers[n].element[0])) continue;
						if (this._intersectsWith(this.containers[n].containerCache)) {
							if (h && e.contains(this.containers[n].element[0], h.element[0])) continue;
							h = this.containers[n], p = n
						} else this.containers[n].containerCache.over && (this.containers[n]._trigger("out", t, this._uiHash(this)), this.containers[n].containerCache.over = 0)
					}
					if (!h) return;
					if (this.containers.length === 1) this.containers[p].containerCache.over || (this.containers[p]._trigger("over", t, this._uiHash(this)), this.containers[p].containerCache.over = 1);
					else {
						i = 1e4, s = null, l = h.floating || this._isFloating(this.currentItem), o = l ? "left" : "top", u = l ? "width" : "height", c = l ? "clientX" : "clientY";
						for (r = this.items.length - 1; r >= 0; r--) {
							if (!e.contains(this.containers[p].element[0], this.items[r].item[0])) continue;
							if (this.items[r].item[0] === this.currentItem[0]) continue;
							a = this.items[r].item.offset()[o], f = !1, t[c] - a > this.items[r][u] / 2 && (f = !0), Math.abs(t[c] - a) < i && (i = Math.abs(t[c] - a), s = this.items[r], this.direction = f ? "up" : "down")
						}
						if (!s && !this.options.dropOnEmpty) return;
						if (this.currentContainer === this.containers[p]) {
							this.currentContainer.containerCache.over || (this.containers[p]._trigger("over", t, this._uiHash()), this.currentContainer.containerCache.over = 1);
							return
						}
						s ? this._rearrange(t, s, null, !0) : this._rearrange(t, null, this.containers[p].element, !0), this._trigger("change", t, this._uiHash()), this.containers[p]._trigger("change", t, this._uiHash(this)), this.currentContainer = this.containers[p], this.options.placeholder.update(this.currentContainer, this.placeholder), this.containers[p]._trigger("over", t, this._uiHash(this)), this.containers[p].containerCache.over = 1
					}
				},
				_createHelper: function (t) {
					var n = this.options,
						r = e.isFunction(n.helper) ? e(n.helper.apply(this.element[0], [t, this.currentItem])) : n.helper === "clone" ? this.currentItem.clone() : this.currentItem;
					return r.parents("body").length || e(n.appendTo !== "parent" ? n.appendTo : this.currentItem[0].parentNode)[0].appendChild(r[0]), r[0] === this.currentItem[0] && (this._storedCSS = {
						width: this.currentItem[0].style.width,
						height: this.currentItem[0].style.height,
						position: this.currentItem.css("position"),
						top: this.currentItem.css("top"),
						left: this.currentItem.css("left")
					}), (!r[0].style.width || n.forceHelperSize) && r.width(this.currentItem.width()), (!r[0].style.height || n.forceHelperSize) && r.height(this.currentItem.height()), r
				},
				_adjustOffsetFromHelper: function (t) {
					typeof t == "string" && (t = t.split(" ")), e.isArray(t) && (t = {
						left: +t[0],
						top: +t[1] || 0
					}), "left" in t && (this.offset.click.left = t.left + this.margins.left), "right" in t && (this.offset.click.left = this.helperProportions.width - t.right + this.margins.left), "top" in t && (this.offset.click.top = t.top + this.margins.top), "bottom" in t && (this.offset.click.top = this.helperProportions.height - t.bottom + this.margins.top)
				},
				_getParentOffset: function () {
					this.offsetParent = this.helper.offsetParent();
					var t = this.offsetParent.offset();
					this.cssPosition === "absolute" && this.scrollParent[0] !== document && e.contains(this.scrollParent[0], this.offsetParent[0]) && (t.left += this.scrollParent.scrollLeft(), t.top += this.scrollParent.scrollTop());
					if (this.offsetParent[0] === document.body || this.offsetParent[0].tagName && this.offsetParent[0].tagName.toLowerCase() === "html" && e.ui.ie) t = {
						top: 0,
						left: 0
					};
					return {
						top: t.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
						left: t.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
					}
				},
				_getRelativeOffset: function () {
					if (this.cssPosition === "relative") {
						var e = this.currentItem.position();
						return {
							top: e.top - (parseInt(this.helper.css("top"), 10) || 0) + this.scrollParent.scrollTop(),
							left: e.left - (parseInt(this.helper.css("left"), 10) || 0) + this.scrollParent.scrollLeft()
						}
					}
					return {
						top: 0,
						left: 0
					}
				},
				_cacheMargins: function () {
					this.margins = {
						left: parseInt(this.currentItem.css("marginLeft"), 10) || 0,
						top: parseInt(this.currentItem.css("marginTop"), 10) || 0
					}
				},
				_cacheHelperProportions: function () {
					this.helperProportions = {
						width: this.helper.outerWidth(),
						height: this.helper.outerHeight()
					}
				},
				_setContainment: function () {
					var t, n, r, i = this.options;
					i.containment === "parent" && (i.containment = this.helper[0].parentNode);
					if (i.containment === "document" || i.containment === "window") this.containment = [0 - this.offset.relative.left - this.offset.parent.left, 0 - this.offset.relative.top - this.offset.parent.top, e(i.containment === "document" ? document : window).width() - this.helperProportions.width - this.margins.left, (e(i.containment === "document" ? document : window).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top];
					/^(document|window|parent)$/.test(i.containment) || (t = e(i.containment)[0], n = e(i.containment).offset(), r = e(t).css("overflow") !== "hidden", this.containment = [n.left + (parseInt(e(t).css("borderLeftWidth"), 10) || 0) + (parseInt(e(t).css("paddingLeft"), 10) || 0) - this.margins.left, n.top + (parseInt(e(t).css("borderTopWidth"), 10) || 0) + (parseInt(e(t).css("paddingTop"), 10) || 0) - this.margins.top, n.left + (r ? Math.max(t.scrollWidth, t.offsetWidth) : t.offsetWidth) - (parseInt(e(t).css("borderLeftWidth"), 10) || 0) - (parseInt(e(t).css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left, n.top + (r ? Math.max(t.scrollHeight, t.offsetHeight) : t.offsetHeight) - (parseInt(e(t).css("borderTopWidth"), 10) || 0) - (parseInt(e(t).css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top])
				},
				_convertPositionTo: function (t, n) {
					n || (n = this.position);
					var r = t === "absolute" ? 1 : -1,
						i = this.cssPosition !== "absolute" || this.scrollParent[0] !== document && !!e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent,
						s = /(html|body)/i.test(i[0].tagName);
					return {
						top: n.top + this.offset.relative.top * r + this.offset.parent.top * r - (this.cssPosition === "fixed" ? -this.scrollParent.scrollTop() : s ? 0 : i.scrollTop()) * r,
						left: n.left + this.offset.relative.left * r + this.offset.parent.left * r - (this.cssPosition === "fixed" ? -this.scrollParent.scrollLeft() : s ? 0 : i.scrollLeft()) * r
					}
				},
				_generatePosition: function (t) {
					var n, r, i = this.options,
						s = t.pageX,
						o = t.pageY,
						u = this.cssPosition !== "absolute" || this.scrollParent[0] !== document && !!e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent,
						a = /(html|body)/i.test(u[0].tagName);
					return this.cssPosition === "relative" && (this.scrollParent[0] === document || this.scrollParent[0] === this.offsetParent[0]) && (this.offset.relative = this._getRelativeOffset()), this.originalPosition && (this.containment && (t.pageX - this.offset.click.left < this.containment[0] && (s = this.containment[0] + this.offset.click.left), t.pageY - this.offset.click.top < this.containment[1] && (o = this.containment[1] + this.offset.click.top), t.pageX - this.offset.click.left > this.containment[2] && (s = this.containment[2] + this.offset.click.left), t.pageY - this.offset.click.top > this.containment[3] && (o = this.containment[3] + this.offset.click.top)), i.grid && (n = this.originalPageY + Math.round((o - this.originalPageY) / i.grid[1]) * i.grid[1], o = this.containment ? n - this.offset.click.top >= this.containment[1] && n - this.offset.click.top <= this.containment[3] ? n : n - this.offset.click.top >= this.containment[1] ? n - i.grid[1] : n + i.grid[1] : n, r = this.originalPageX + Math.round((s - this.originalPageX) / i.grid[0]) * i.grid[0], s = this.containment ? r - this.offset.click.left >= this.containment[0] && r - this.offset.click.left <= this.containment[2] ? r : r - this.offset.click.left >= this.containment[0] ? r - i.grid[0] : r + i.grid[0] : r)), {
						top: o - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + (this.cssPosition === "fixed" ? -this.scrollParent.scrollTop() : a ? 0 : u.scrollTop()),
						left: s - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + (this.cssPosition === "fixed" ? -this.scrollParent.scrollLeft() : a ? 0 : u.scrollLeft())
					}
				},
				_rearrange: function (e, t, n, r) {
					n ? n[0].appendChild(this.placeholder[0]) : t.item[0].parentNode.insertBefore(this.placeholder[0], this.direction === "down" ? t.item[0] : t.item[0].nextSibling), this.counter = this.counter ? ++this.counter : 1;
					var i = this.counter;
					this._delay(function () {
						i === this.counter && this.refreshPositions(!r)
					})
				},
				_clear: function (e, t) {
					function i(e, t, n) {
						return function (r) {
							n._trigger(e, r, t._uiHash(t))
						}
					}
					if (this.options.beforeDrop && this.options.beforeDrop(e, this._uiHash()) === !1) return;
					this.reverting = !1;
					var n, r = [];
					!this._noFinalSort && this.currentItem.parent().length && this.placeholder.before(this.currentItem), this._noFinalSort = null;
					if (this.helper[0] === this.currentItem[0]) {
						for (n in this._storedCSS)
							if (this._storedCSS[n] === "auto" || this._storedCSS[n] === "static") this._storedCSS[n] = "";
						this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")
					} else this.currentItem.show();
					this.fromOutside && !t && r.push(function (e) {
						this._trigger("receive", e, this._uiHash(this.fromOutside))
					}), (this.fromOutside || this.domPosition.prev !== this.currentItem.prev().not(".ui-sortable-helper")[0] || this.domPosition.parent !== this.currentItem.parent()[0]) && !t && r.push(function (e) {
						this._trigger("update", e, this._uiHash())
					}), this !== this.currentContainer && (t || (r.push(function (e) {
						this._trigger("remove", e, this._uiHash())
					}), r.push(function (e) {
						return function (t) {
							e._trigger("receive", t, this._uiHash(this))
						}
					}.call(this, this.currentContainer)), r.push(function (e) {
						return function (t) {
							e._trigger("update", t, this._uiHash(this))
						}
					}.call(this, this.currentContainer))));
					for (n = this.containers.length - 1; n >= 0; n--) t || r.push(i("deactivate", this, this.containers[n])), this.containers[n].containerCache.over && (r.push(i("out", this, this.containers[n])), this.containers[n].containerCache.over = 0);
					this.storedCursor && (this.document.find("body").css("cursor", this.storedCursor), this.storedStylesheet.remove()), this._storedOpacity && this.helper.css("opacity", this._storedOpacity), this._storedZIndex && this.helper.css("zIndex", this._storedZIndex === "auto" ? "" : this._storedZIndex), this.dragging = !1, t || this._trigger("beforeStop", e, this._uiHash()), this.placeholder[0].parentNode.removeChild(this.placeholder[0]), this.cancelHelperRemoval || (this.helper[0] !== this.currentItem[0] && this.helper.remove(), this.helper = null);
					if (!t) {
						for (n = 0; n < r.length; n++) r[n].call(this, e);
						this._trigger("stop", e, this._uiHash())
					}
					return this.fromOutside = !1, !this.cancelHelperRemoval
				},
				_trigger: function () {
					e.Widget.prototype._trigger.apply(this, arguments) === !1 && this.cancel()
				},
				_uiHash: function (t) {
					var n = t || this;
					return {
						helper: n.helper,
						placeholder: n.placeholder || e([]),
						position: n.position,
						originalPosition: n.originalPosition,
						offset: n.positionAbs,
						item: n.currentItem,
						sender: t ? t.element : null
					}
				}
			}),
			d = e.widget("ui.accordion", {
				version: "1.11.2",
				options: {
					active: 0,
					animate: {},
					collapsible: !1,
					event: "click",
					header: "> li > :first-child,> :not(li):even",
					heightStyle: "auto",
					icons: {
						activeHeader: "ui-icon-triangle-1-s",
						header: "ui-icon-triangle-1-e"
					},
					activate: null,
					beforeActivate: null
				},
				hideProps: {
					borderTopWidth: "hide",
					borderBottomWidth: "hide",
					paddingTop: "hide",
					paddingBottom: "hide",
					height: "hide"
				},
				showProps: {
					borderTopWidth: "show",
					borderBottomWidth: "show",
					paddingTop: "show",
					paddingBottom: "show",
					height: "show"
				},
				_create: function () {
					var t = this.options;
					this.prevShow = this.prevHide = e(), this.element.addClass("ui-accordion ui-widget ui-helper-reset").attr("role", "tablist"), !t.collapsible && (t.active === !1 || t.active == null) && (t.active = 0), this._processPanels(), t.active < 0 && (t.active += this.headers.length), this._refresh()
				},
				_getCreateEventData: function () {
					return {
						header: this.active,
						panel: this.active.length ? this.active.next() : e()
					}
				},
				_createIcons: function () {
					var t = this.options.icons;
					t && (e("<span>").addClass("ui-accordion-header-icon ui-icon " + t.header).prependTo(this.headers), this.active.children(".ui-accordion-header-icon").removeClass(t.header).addClass(t.activeHeader), this.headers.addClass("ui-accordion-icons"))
				},
				_destroyIcons: function () {
					this.headers.removeClass("ui-accordion-icons").children(".ui-accordion-header-icon").remove()
				},
				_destroy: function () {
					var e;
					this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role"), this.headers.removeClass("ui-accordion-header ui-accordion-header-active ui-state-default ui-corner-all ui-state-active ui-state-disabled ui-corner-top").removeAttr("role").removeAttr("aria-expanded").removeAttr("aria-selected").removeAttr("aria-controls").removeAttr("tabIndex").removeUniqueId(), this._destroyIcons(), e = this.headers.next().removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active ui-state-disabled").css("display", "").removeAttr("role").removeAttr("aria-hidden").removeAttr("aria-labelledby").removeUniqueId(), this.options.heightStyle !== "content" && e.css("height", "")
				},
				_setOption: function (e, t) {
					if (e === "active") {
						this._activate(t);
						return
					}
					e === "event" && (this.options.event && this._off(this.headers, this.options.event), this._setupEvents(t)), this._super(e, t), e === "collapsible" && !t && this.options.active === !1 && this._activate(0), e === "icons" && (this._destroyIcons(), t && this._createIcons()), e === "disabled" && (this.element.toggleClass("ui-state-disabled", !!t).attr("aria-disabled", t), this.headers.add(this.headers.next()).toggleClass("ui-state-disabled", !!t))
				},
				_keydown: function (t) {
					if (t.altKey || t.ctrlKey) return;
					var n = e.ui.keyCode,
						r = this.headers.length,
						i = this.headers.index(t.target),
						s = !1;
					switch (t.keyCode) {
					case n.RIGHT:
					case n.DOWN:
						s = this.headers[(i + 1) % r];
						break;
					case n.LEFT:
					case n.UP:
						s = this.headers[(i - 1 + r) % r];
						break;
					case n.SPACE:
					case n.ENTER:
						this._eventHandler(t);
						break;
					case n.HOME:
						s = this.headers[0];
						break;
					case n.END:
						s = this.headers[r - 1]
					}
					s && (e(t.target).attr("tabIndex", -1), e(s).attr("tabIndex", 0), s.focus(), t.preventDefault())
				},
				_panelKeyDown: function (t) {
					t.keyCode === e.ui.keyCode.UP && t.ctrlKey && e(t.currentTarget).prev().focus()
				},
				refresh: function () {
					var t = this.options;
					this._processPanels(), t.active === !1 && t.collapsible === !0 || !this.headers.length ? (t.active = !1, this.active = e()) : t.active === !1 ? this._activate(0) : this.active.length && !e.contains(this.element[0], this.active[0]) ? this.headers.length === this.headers.find(".ui-state-disabled").length ? (t.active = !1, this.active = e()) : this._activate(Math.max(0, t.active - 1)) : t.active = this.headers.index(this.active), this._destroyIcons(), this._refresh()
				},
				_processPanels: function () {
					var e = this.headers,
						t = this.panels;
					this.headers = this.element.find(this.options.header).addClass("ui-accordion-header ui-state-default ui-corner-all"), this.panels = this.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom").filter(":not(.ui-accordion-content-active)").hide(), t && (this._off(e.not(this.headers)), this._off(t.not(this.panels)))
				},
				_refresh: function () {
					var t, n = this.options,
						r = n.heightStyle,
						i = this.element.parent();
					this.active = this._findActive(n.active).addClass("ui-accordion-header-active ui-state-active ui-corner-top").removeClass("ui-corner-all"), this.active.next().addClass("ui-accordion-content-active").show(), this.headers.attr("role", "tab").each(function () {
						var t = e(this),
							n = t.uniqueId().attr("id"),
							r = t.next(),
							i = r.uniqueId().attr("id");
						t.attr("aria-controls", i), r.attr("aria-labelledby", n)
					}).next().attr("role", "tabpanel"), this.headers.not(this.active).attr({
						"aria-selected": "false",
						"aria-expanded": "false",
						tabIndex: -1
					}).next().attr({
						"aria-hidden": "true"
					}).hide(), this.active.length ? this.active.attr({
						"aria-selected": "true",
						"aria-expanded": "true",
						tabIndex: 0
					}).next().attr({
						"aria-hidden": "false"
					}) : this.headers.eq(0).attr("tabIndex", 0), this._createIcons(), this._setupEvents(n.event), r === "fill" ? (t = i.height(), this.element.siblings(":visible").each(function () {
						var n = e(this),
							r = n.css("position");
						if (r === "absolute" || r === "fixed") return;
						t -= n.outerHeight(!0)
					}), this.headers.each(function () {
						t -= e(this).outerHeight(!0)
					}), this.headers.next().each(function () {
						e(this).height(Math.max(0, t - e(this).innerHeight() + e(this).height()))
					}).css("overflow", "auto")) : r === "auto" && (t = 0, this.headers.next().each(function () {
						t = Math.max(t, e(this).css("height", "").height())
					}).height(t))
				},
				_activate: function (t) {
					var n = this._findActive(t)[0];
					if (n === this.active[0]) return;
					n = n || this.active[0], this._eventHandler({
						target: n,
						currentTarget: n,
						preventDefault: e.noop
					})
				},
				_findActive: function (t) {
					return typeof t == "number" ? this.headers.eq(t) : e()
				},
				_setupEvents: function (t) {
					var n = {
						keydown: "_keydown"
					};
					t && e.each(t.split(" "), function (e, t) {
						n[t] = "_eventHandler"
					}), this._off(this.headers.add(this.headers.next())), this._on(this.headers, n), this._on(this.headers.next(), {
						keydown: "_panelKeyDown"
					}), this._hoverable(this.headers), this._focusable(this.headers)
				},
				_eventHandler: function (t) {
					var n = this.options,
						r = this.active,
						i = e(t.currentTarget),
						s = i[0] === r[0],
						o = s && n.collapsible,
						u = o ? e() : i.next(),
						a = r.next(),
						f = {
							oldHeader: r,
							oldPanel: a,
							newHeader: o ? e() : i,
							newPanel: u
						};
					t.preventDefault();
					if (s && !n.collapsible || this._trigger("beforeActivate", t, f) === !1) return;
					n.active = o ? !1 : this.headers.index(i), this.active = s ? e() : i, this._toggle(f), r.removeClass("ui-accordion-header-active ui-state-active"), n.icons && r.children(".ui-accordion-header-icon").removeClass(n.icons.activeHeader).addClass(n.icons.header), s || (i.removeClass("ui-corner-all").addClass("ui-accordion-header-active ui-state-active ui-corner-top"), n.icons && i.children(".ui-accordion-header-icon").removeClass(n.icons.header).addClass(n.icons.activeHeader), i.next().addClass("ui-accordion-content-active"))
				},
				_toggle: function (t) {
					var n = t.newPanel,
						r = this.prevShow.length ? this.prevShow : t.oldPanel;
					this.prevShow.add(this.prevHide).stop(!0, !0), this.prevShow = n, this.prevHide = r, this.options.animate ? this._animate(n, r, t) : (r.hide(), n.show(), this._toggleComplete(t)), r.attr({
						"aria-hidden": "true"
					}), r.prev().attr("aria-selected", "false"), n.length && r.length ? r.prev().attr({
						tabIndex: -1,
						"aria-expanded": "false"
					}) : n.length && this.headers.filter(function () {
						return e(this).attr("tabIndex") === 0
					}).attr("tabIndex", -1), n.attr("aria-hidden", "false").prev().attr({
						"aria-selected": "true",
						tabIndex: 0,
						"aria-expanded": "true"
					})
				},
				_animate: function (e, t, n) {
					var r, i, s, o = this,
						u = 0,
						a = e.length && (!t.length || e.index() < t.index()),
						f = this.options.animate || {},
						l = a && f.down || f,
						c = function () {
							o._toggleComplete(n)
						};
					typeof l == "number" && (s = l), typeof l == "string" && (i = l), i = i || l.easing || f.easing, s = s || l.duration || f.duration;
					if (!t.length) return e.animate(this.showProps, s, i, c);
					if (!e.length) return t.animate(this.hideProps, s, i, c);
					r = e.show().outerHeight(), t.animate(this.hideProps, {
						duration: s,
						easing: i,
						step: function (e, t) {
							t.now = Math.round(e)
						}
					}), e.hide().animate(this.showProps, {
						duration: s,
						easing: i,
						complete: c,
						step: function (e, n) {
							n.now = Math.round(e), n.prop !== "height" ? u += n.now : o.options.heightStyle !== "content" && (n.now = Math.round(r - t.outerHeight() - u), u = 0)
						}
					})
				},
				_toggleComplete: function (e) {
					var t = e.oldPanel;
					t.removeClass("ui-accordion-content-active").prev().removeClass("ui-corner-top").addClass("ui-corner-all"), t.length && (t.parent()[0].className = t.parent()[0].className), this._trigger("activate", null, e)
				}
			});
		e.extend(e.ui, {
			datepicker: {
				version: "1.11.2"
			}
		});
		var v;
		e.extend(g.prototype, {
			markerClassName: "hasDatepicker",
			maxRows: 4,
			_widgetDatepicker: function () {
				return this.dpDiv
			},
			setDefaults: function (e) {
				return w(this._defaults, e || {}), this
			},
			_attachDatepicker: function (t, n) {
				var r, i, s;
				r = t.nodeName.toLowerCase(), i = r === "div" || r === "span", t.id || (this.uuid += 1, t.id = "dp" + this.uuid), s = this._newInst(e(t), i), s.settings = e.extend({}, n || {}), r === "input" ? this._connectDatepicker(t, s) : i && this._inlineDatepicker(t, s)
			},
			_newInst: function (t, n) {
				var r = t[0].id.replace(/([^A-Za-z0-9_\-])/g, "\\\\$1");
				return {
					id: r,
					input: t,
					selectedDay: 0,
					selectedMonth: 0,
					selectedYear: 0,
					drawMonth: 0,
					drawYear: 0,
					inline: n,
					dpDiv: n ? y(e("<div class='" + this._inlineClass + " ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>")) : this.dpDiv
				}
			},
			_connectDatepicker: function (t, n) {
				var r = e(t);
				n.append = e([]), n.trigger = e([]);
				if (r.hasClass(this.markerClassName)) return;
				this._attachments(r, n), r.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp), this._autoSize(n), e.data(t, "datepicker", n), n.settings.disabled && this._disableDatepicker(t)
			},
			_attachments: function (t, n) {
				var r, i, s, o = this._get(n, "appendText"),
					u = this._get(n, "isRTL");
				n.append && n.append.remove(), o && (n.append = e("<span class='" + this._appendClass + "'>" + o + "</span>"), t[u ? "before" : "after"](n.append)), t.unbind("focus", this._showDatepicker), n.trigger && n.trigger.remove(), r = this._get(n, "showOn"), (r === "focus" || r === "both") && t.focus(this._showDatepicker);
				if (r === "button" || r === "both") i = this._get(n, "buttonText"), s = this._get(n, "buttonImage"), n.trigger = e(this._get(n, "buttonImageOnly") ? e("<img/>").addClass(this._triggerClass).attr({
					src: s,
					alt: i,
					title: i
				}) : e("<button type='button'></button>").addClass(this._triggerClass).html(s ? e("<img/>").attr({
					src: s,
					alt: i,
					title: i
				}) : i)), t[u ? "before" : "after"](n.trigger), n.trigger.click(function () {
					return e.datepicker._datepickerShowing && e.datepicker._lastInput === t[0] ? e.datepicker._hideDatepicker() : e.datepicker._datepickerShowing && e.datepicker._lastInput !== t[0] ? (e.datepicker._hideDatepicker(), e.datepicker._showDatepicker(t[0])) : e.datepicker._showDatepicker(t[0]), !1
				})
			},
			_autoSize: function (e) {
				if (this._get(e, "autoSize") && !e.inline) {
					var t, n, r, i, s = new Date(2009, 11, 20),
						o = this._get(e, "dateFormat");
					o.match(/[DM]/) && (t = function (e) {
						n = 0, r = 0;
						for (i = 0; i < e.length; i++) e[i].length > n && (n = e[i].length, r = i);
						return r
					}, s.setMonth(t(this._get(e, o.match(/MM/) ? "monthNames" : "monthNamesShort"))), s.setDate(t(this._get(e, o.match(/DD/) ? "dayNames" : "dayNamesShort")) + 20 - s.getDay())), e.input.attr("size", this._formatDate(e, s).length)
				}
			},
			_inlineDatepicker: function (t, n) {
				var r = e(t);
				if (r.hasClass(this.markerClassName)) return;
				r.addClass(this.markerClassName).append(n.dpDiv), e.data(t, "datepicker", n), this._setDate(n, this._getDefaultDate(n), !0), this._updateDatepicker(n), this._updateAlternate(n), n.settings.disabled && this._disableDatepicker(t), n.dpDiv.css("display", "block")
			},
			_dialogDatepicker: function (t, n, r, i, s) {
				var o, u, a, f, l, c = this._dialogInst;
				return c || (this.uuid += 1, o = "dp" + this.uuid, this._dialogInput = e("<input type='text' id='" + o + "' style='position: absolute; top: -100px; width: 0px;'/>"), this._dialogInput.keydown(this._doKeyDown), e("body").append(this._dialogInput), c = this._dialogInst = this._newInst(this._dialogInput, !1), c.settings = {}, e.data(this._dialogInput[0], "datepicker", c)), w(c.settings, i || {}), n = n && n.constructor === Date ? this._formatDate(c, n) : n, this._dialogInput.val(n), this._pos = s ? s.length ? s : [s.pageX, s.pageY] : null, this._pos || (u = document.documentElement.clientWidth, a = document.documentElement.clientHeight, f = document.documentElement.scrollLeft || document.body.scrollLeft, l = document.documentElement.scrollTop || document.body.scrollTop, this._pos = [u / 2 - 100 + f, a / 2 - 150 + l]), this._dialogInput.css("left", this._pos[0] + 20 + "px").css("top", this._pos[1] + "px"), c.settings.onSelect = r, this._inDialog = !0, this.dpDiv.addClass(this._dialogClass), this._showDatepicker(this._dialogInput[0]), e.blockUI && e.blockUI(this.dpDiv), e.data(this._dialogInput[0], "datepicker", c), this
			},
			_destroyDatepicker: function (t) {
				var n, r = e(t),
					i = e.data(t, "datepicker");
				if (!r.hasClass(this.markerClassName)) return;
				n = t.nodeName.toLowerCase(), e.removeData(t, "datepicker"), n === "input" ? (i.append.remove(), i.trigger.remove(), r.removeClass(this.markerClassName).unbind("focus", this._showDatepicker).unbind("keydown", this._doKeyDown).unbind("keypress", this._doKeyPress).unbind("keyup", this._doKeyUp)) : (n === "div" || n === "span") && r.removeClass(this.markerClassName).empty()
			},
			_enableDatepicker: function (t) {
				var n, r, i = e(t),
					s = e.data(t, "datepicker");
				if (!i.hasClass(this.markerClassName)) return;
				n = t.nodeName.toLowerCase();
				if (n === "input") t.disabled = !1, s.trigger.filter("button").each(function () {
					this.disabled = !1
				}).end().filter("img").css({
					opacity: "1.0",
					cursor: ""
				});
				else if (n === "div" || n === "span") r = i.children("." + this._inlineClass), r.children().removeClass("ui-state-disabled"), r.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !1);
				this._disabledInputs = e.map(this._disabledInputs, function (e) {
					return e === t ? null : e
				})
			},
			_disableDatepicker: function (t) {
				var n, r, i = e(t),
					s = e.data(t, "datepicker");
				if (!i.hasClass(this.markerClassName)) return;
				n = t.nodeName.toLowerCase();
				if (n === "input") t.disabled = !0, s.trigger.filter("button").each(function () {
					this.disabled = !0
				}).end().filter("img").css({
					opacity: "0.5",
					cursor: "default"
				});
				else if (n === "div" || n === "span") r = i.children("." + this._inlineClass), r.children().addClass("ui-state-disabled"), r.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !0);
				this._disabledInputs = e.map(this._disabledInputs, function (e) {
					return e === t ? null : e
				}), this._disabledInputs[this._disabledInputs.length] = t
			},
			_isDisabledDatepicker: function (e) {
				if (!e) return !1;
				for (var t = 0; t < this._disabledInputs.length; t++)
					if (this._disabledInputs[t] === e) return !0;
				return !1
			},
			_getInst: function (t) {
				try {
					return e.data(t, "datepicker")
				} catch (n) {
					throw "Missing instance data for this datepicker"
				}
			},
			_optionDatepicker: function (t, n, r) {
				var i, s, o, u, a = this._getInst(t);
				if (arguments.length === 2 && typeof n == "string") return n === "defaults" ? e.extend({}, e.datepicker._defaults) : a ? n === "all" ? e.extend({}, a.settings) : this._get(a, n) : null;
				i = n || {}, typeof n == "string" && (i = {}, i[n] = r), a && (this._curInst === a && this._hideDatepicker(), s = this._getDateDatepicker(t, !0), o = this._getMinMaxDate(a, "min"), u = this._getMinMaxDate(a, "max"), w(a.settings, i), o !== null && i.dateFormat !== undefined && i.minDate === undefined && (a.settings.minDate = this._formatDate(a, o)), u !== null && i.dateFormat !== undefined && i.maxDate === undefined && (a.settings.maxDate = this._formatDate(a, u)), "disabled" in i && (i.disabled ? this._disableDatepicker(t) : this._enableDatepicker(t)), this._attachments(e(t), a), this._autoSize(a), this._setDate(a, s), this._updateAlternate(a), this._updateDatepicker(a))
			},
			_changeDatepicker: function (e, t, n) {
				this._optionDatepicker(e, t, n)
			},
			_refreshDatepicker: function (e) {
				var t = this._getInst(e);
				t && this._updateDatepicker(t)
			},
			_setDateDatepicker: function (e, t) {
				var n = this._getInst(e);
				n && (this._setDate(n, t), this._updateDatepicker(n), this._updateAlternate(n))
			},
			_getDateDatepicker: function (e, t) {
				var n = this._getInst(e);
				return n && !n.inline && this._setDateFromField(n, t), n ? this._getDate(n) : null
			},
			_doKeyDown: function (t) {
				var n, r, i, s = e.datepicker._getInst(t.target),
					o = !0,
					u = s.dpDiv.is(".ui-datepicker-rtl");
				s._keyEvent = !0;
				if (e.datepicker._datepickerShowing) switch (t.keyCode) {
				case 9:
					e.datepicker._hideDatepicker(), o = !1;
					break;
				case 13:
					return i = e("td." + e.datepicker._dayOverClass + ":not(." + e.datepicker._currentClass + ")", s.dpDiv), i[0] && e.datepicker._selectDay(t.target, s.selectedMonth, s.selectedYear, i[0]), n = e.datepicker._get(s, "onSelect"), n ? (r = e.datepicker._formatDate(s), n.apply(s.input ? s.input[0] : null, [r, s])) : e.datepicker._hideDatepicker(), !1;
				case 27:
					e.datepicker._hideDatepicker();
					break;
				case 33:
					e.datepicker._adjustDate(t.target, t.ctrlKey ? -e.datepicker._get(s, "stepBigMonths") : -e.datepicker._get(s, "stepMonths"), "M");
					break;
				case 34:
					e.datepicker._adjustDate(t.target, t.ctrlKey ? +e.datepicker._get(s, "stepBigMonths") : +e.datepicker._get(s, "stepMonths"), "M");
					break;
				case 35:
					(t.ctrlKey || t.metaKey) && e.datepicker._clearDate(t.target), o = t.ctrlKey || t.metaKey;
					break;
				case 36:
					(t.ctrlKey || t.metaKey) && e.datepicker._gotoToday(t.target), o = t.ctrlKey || t.metaKey;
					break;
				case 37:
					(t.ctrlKey || t.metaKey) && e.datepicker._adjustDate(t.target, u ? 1 : -1, "D"), o = t.ctrlKey || t.metaKey, t.originalEvent.altKey && e.datepicker._adjustDate(t.target, t.ctrlKey ? -e.datepicker._get(s, "stepBigMonths") : -e.datepicker._get(s, "stepMonths"), "M");
					break;
				case 38:
					(t.ctrlKey || t.metaKey) && e.datepicker._adjustDate(t.target, -7, "D"), o = t.ctrlKey || t.metaKey;
					break;
				case 39:
					(t.ctrlKey || t.metaKey) && e.datepicker._adjustDate(t.target, u ? -1 : 1, "D"), o = t.ctrlKey || t.metaKey, t.originalEvent.altKey && e.datepicker._adjustDate(t.target, t.ctrlKey ? +e.datepicker._get(s, "stepBigMonths") : +e.datepicker._get(s, "stepMonths"), "M");
					break;
				case 40:
					(t.ctrlKey || t.metaKey) && e.datepicker._adjustDate(t.target, 7, "D"), o = t.ctrlKey || t.metaKey;
					break;
				default:
					o = !1
				} else t.keyCode === 36 && t.ctrlKey ? e.datepicker._showDatepicker(this) : o = !1;
				o && (t.preventDefault(), t.stopPropagation())
			},
			_doKeyPress: function (t) {
				var n, r, i = e.datepicker._getInst(t.target);
				if (e.datepicker._get(i, "constrainInput")) return n = e.datepicker._possibleChars(e.datepicker._get(i, "dateFormat")), r = String.fromCharCode(t.charCode == null ? t.keyCode : t.charCode), t.ctrlKey || t.metaKey || r < " " || !n || n.indexOf(r) > -1
			},
			_doKeyUp: function (t) {
				var n, r = e.datepicker._getInst(t.target);
				if (r.input.val() !== r.lastVal) try {
					n = e.datepicker.parseDate(e.datepicker._get(r, "dateFormat"), r.input ? r.input.val() : null, e.datepicker._getFormatConfig(r)), n && (e.datepicker._setDateFromField(r), e.datepicker._updateAlternate(r), e.datepicker._updateDatepicker(r))
				} catch (i) {}
				return !0
			},
			_showDatepicker: function (t) {
				t = t.target || t, t.nodeName.toLowerCase() !== "input" && (t = e("input", t.parentNode)[0]);
				if (e.datepicker._isDisabledDatepicker(t) || e.datepicker._lastInput === t) return;
				var n, r, i, s, o, u, a;
				n = e.datepicker._getInst(t), e.datepicker._curInst && e.datepicker._curInst !== n && (e.datepicker._curInst.dpDiv.stop(!0, !0), n && e.datepicker._datepickerShowing && e.datepicker._hideDatepicker(e.datepicker._curInst.input[0])), r = e.datepicker._get(n, "beforeShow"), i = r ? r.apply(t, [t, n]) : {};
				if (i === !1) return;
				w(n.settings, i), n.lastVal = null, e.datepicker._lastInput = t, e.datepicker._setDateFromField(n), e.datepicker._inDialog && (t.value = ""), e.datepicker._pos || (e.datepicker._pos = e.datepicker._findPos(t), e.datepicker._pos[1] += t.offsetHeight), s = !1, e(t).parents().each(function () {
					return s |= e(this).css("position") === "fixed", !s
				}), o = {
					left: e.datepicker._pos[0],
					top: e.datepicker._pos[1]
				}, e.datepicker._pos = null, n.dpDiv.empty(), n.dpDiv.css({
					position: "absolute",
					display: "block",
					top: "-1000px"
				}), e.datepicker._updateDatepicker(n), o = e.datepicker._checkOffset(n, o, s), n.dpDiv.css({
					position: e.datepicker._inDialog && e.blockUI ? "static" : s ? "fixed" : "absolute",
					display: "none",
					left: o.left + "px",
					top: o.top + "px"
				}), n.inline || (u = e.datepicker._get(n, "showAnim"), a = e.datepicker._get(n, "duration"), n.dpDiv.css("z-index", m(e(t)) + 1), e.datepicker._datepickerShowing = !0, e.effects && e.effects.effect[u] ? n.dpDiv.show(u, e.datepicker._get(n, "showOptions"), a) : n.dpDiv[u || "show"](u ? a : null), e.datepicker._shouldFocusInput(n) && n.input.focus(), e.datepicker._curInst = n)
			},
			_updateDatepicker: function (t) {
				this.maxRows = 4, v = t, t.dpDiv.empty().append(this._generateHTML(t)), this._attachHandlers(t);
				var n, r = this._getNumberOfMonths(t),
					i = r[1],
					s = 17,
					o = t.dpDiv.find("." + this._dayOverClass + " a");
				o.length > 0 && b.apply(o.get(0)), t.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width(""), i > 1 && t.dpDiv.addClass("ui-datepicker-multi-" + i).css("width", s * i + "em"), t.dpDiv[(r[0] !== 1 || r[1] !== 1 ? "add" : "remove") + "Class"]("ui-datepicker-multi"), t.dpDiv[(this._get(t, "isRTL") ? "add" : "remove") + "Class"]("ui-datepicker-rtl"), t === e.datepicker._curInst && e.datepicker._datepickerShowing && e.datepicker._shouldFocusInput(t) && t.input.focus(), t.yearshtml && (n = t.yearshtml, setTimeout(function () {
					n === t.yearshtml && t.yearshtml && t.dpDiv.find("select.ui-datepicker-year:first").replaceWith(t.yearshtml), n = t.yearshtml = null
				}, 0))
			},
			_shouldFocusInput: function (e) {
				return e.input && e.input.is(":visible") && !e.input.is(":disabled") && !e.input.is(":focus")
			},
			_checkOffset: function (t, n, r) {
				var i = t.dpDiv.outerWidth(),
					s = t.dpDiv.outerHeight(),
					o = t.input ? t.input.outerWidth() : 0,
					u = t.input ? t.input.outerHeight() : 0,
					a = document.documentElement.clientWidth + (r ? 0 : e(document).scrollLeft()),
					f = document.documentElement.clientHeight + (r ? 0 : e(document).scrollTop());
				return n.left -= this._get(t, "isRTL") ? i - o : 0, n.left -= r && n.left === t.input.offset().left ? e(document).scrollLeft() : 0, n.top -= r && n.top === t.input.offset().top + u ? e(document).scrollTop() : 0, n.left -= Math.min(n.left, n.left + i > a && a > i ? Math.abs(n.left + i - a) : 0), n.top -= Math.min(n.top, n.top + s > f && f > s ? Math.abs(s + u) : 0), n
			},
			_findPos: function (t) {
				var n, r = this._getInst(t),
					i = this._get(r, "isRTL");
				while (t && (t.type === "hidden" || t.nodeType !== 1 || e.expr.filters.hidden(t))) t = t[i ? "previousSibling" : "nextSibling"];
				return n = e(t).offset(), [n.left, n.top]
			},
			_hideDatepicker: function (t) {
				var n, r, i, s, o = this._curInst;
				if (!o || t && o !== e.data(t, "datepicker")) return;
				this._datepickerShowing && (n = this._get(o, "showAnim"), r = this._get(o, "duration"), i = function () {
					e.datepicker._tidyDialog(o)
				}, e.effects && (e.effects.effect[n] || e.effects[n]) ? o.dpDiv.hide(n, e.datepicker._get(o, "showOptions"), r, i) : o.dpDiv[n === "slideDown" ? "slideUp" : n === "fadeIn" ? "fadeOut" : "hide"](n ? r : null, i), n || i(), this._datepickerShowing = !1, s = this._get(o, "onClose"), s && s.apply(o.input ? o.input[0] : null, [o.input ? o.input.val() : "", o]), this._lastInput = null, this._inDialog && (this._dialogInput.css({
					position: "absolute",
					left: "0",
					top: "-100px"
				}), e.blockUI && (e.unblockUI(), e("body").append(this.dpDiv))), this._inDialog = !1)
			},
			_tidyDialog: function (e) {
				e.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")
			},
			_checkExternalClick: function (t) {
				if (!e.datepicker._curInst) return;
				var n = e(t.target),
					r = e.datepicker._getInst(n[0]);
				(n[0].id !== e.datepicker._mainDivId && n.parents("#" + e.datepicker._mainDivId).length === 0 && !n.hasClass(e.datepicker.markerClassName) && !n.closest("." + e.datepicker._triggerClass).length && e.datepicker._datepickerShowing && (!e.datepicker._inDialog || !e.blockUI) || n.hasClass(e.datepicker.markerClassName) && e.datepicker._curInst !== r) && e.datepicker._hideDatepicker()
			},
			_adjustDate: function (t, n, r) {
				var i = e(t),
					s = this._getInst(i[0]);
				if (this._isDisabledDatepicker(i[0])) return;
				this._adjustInstDate(s, n + (r === "M" ? this._get(s, "showCurrentAtPos") : 0), r), this._updateDatepicker(s)
			},
			_gotoToday: function (t) {
				var n, r = e(t),
					i = this._getInst(r[0]);
				this._get(i, "gotoCurrent") && i.currentDay ? (i.selectedDay = i.currentDay, i.drawMonth = i.selectedMonth = i.currentMonth, i.drawYear = i.selectedYear = i.currentYear) : (n = new Date, i.selectedDay = n.getDate(), i.drawMonth = i.selectedMonth = n.getMonth(), i.drawYear = i.selectedYear = n.getFullYear()), this._notifyChange(i), this._adjustDate(r)
			},
			_selectMonthYear: function (t, n, r) {
				var i = e(t),
					s = this._getInst(i[0]);
				s["selected" + (r === "M" ? "Month" : "Year")] = s["draw" + (r === "M" ? "Month" : "Year")] = parseInt(n.options[n.selectedIndex].value, 10), this._notifyChange(s), this._adjustDate(i)
			},
			_selectDay: function (t, n, r, i) {
				var s, o = e(t);
				if (e(i).hasClass(this._unselectableClass) || this._isDisabledDatepicker(o[0])) return;
				s = this._getInst(o[0]), s.selectedDay = s.currentDay = e("a", i).html(), s.selectedMonth = s.currentMonth = n, s.selectedYear = s.currentYear = r, this._selectDate(t, this._formatDate(s, s.currentDay, s.currentMonth, s.currentYear))
			},
			_clearDate: function (t) {
				var n = e(t);
				this._selectDate(n, "")
			},
			_selectDate: function (t, n) {
				var r, i = e(t),
					s = this._getInst(i[0]);
				n = n != null ? n : this._formatDate(s), s.input && s.input.val(n), this._updateAlternate(s), r = this._get(s, "onSelect"), r ? r.apply(s.input ? s.input[0] : null, [n, s]) : s.input && s.input.trigger("change"), s.inline ? this._updateDatepicker(s) : (this._hideDatepicker(), this._lastInput = s.input[0], typeof s.input[0] != "object" && s.input.focus(), this._lastInput = null)
			},
			_updateAlternate: function (t) {
				var n, r, i, s = this._get(t, "altField");
				s && (n = this._get(t, "altFormat") || this._get(t, "dateFormat"), r = this._getDate(t), i = this.formatDate(n, r, this._getFormatConfig(t)), e(s).each(function () {
					e(this).val(i)
				}))
			},
			noWeekends: function (e) {
				var t = e.getDay();
				return [t > 0 && t < 6, ""]
			},
			iso8601Week: function (e) {
				var t, n = new Date(e.getTime());
				return n.setDate(n.getDate() + 4 - (n.getDay() || 7)), t = n.getTime(), n.setMonth(0), n.setDate(1), Math.floor(Math.round((t - n) / 864e5) / 7) + 1
			},
			parseDate: function (t, n, r) {
				if (t == null || n == null) throw "Invalid arguments";
				n = typeof n == "object" ? n.toString() : n + "";
				if (n === "") return null;
				var i, s, o, u = 0,
					a = (r ? r.shortYearCutoff : null) || this._defaults.shortYearCutoff,
					f = typeof a != "string" ? a : (new Date).getFullYear() % 100 + parseInt(a, 10),
					l = (r ? r.dayNamesShort : null) || this._defaults.dayNamesShort,
					c = (r ? r.dayNames : null) || this._defaults.dayNames,
					h = (r ? r.monthNamesShort : null) || this._defaults.monthNamesShort,
					p = (r ? r.monthNames : null) || this._defaults.monthNames,
					d = -1,
					v = -1,
					m = -1,
					g = -1,
					y = !1,
					b, w = function (e) {
						var n = i + 1 < t.length && t.charAt(i + 1) === e;
						return n && i++, n
					},
					E = function (e) {
						var t = w(e),
							r = e === "@" ? 14 : e === "!" ? 20 : e === "y" && t ? 4 : e === "o" ? 3 : 2,
							i = e === "y" ? r : 1,
							s = new RegExp("^\\d{" + i + "," + r + "}"),
							o = n.substring(u).match(s);
						if (!o) throw "Missing number at position " + u;
						return u += o[0].length, parseInt(o[0], 10)
					},
					S = function (t, r, i) {
						var s = -1,
							o = e.map(w(t) ? i : r, function (e, t) {
								return [[t, e]]
							}).sort(function (e, t) {
								return -(e[1].length - t[1].length)
							});
						e.each(o, function (e, t) {
							var r = t[1];
							if (n.substr(u, r.length).toLowerCase() === r.toLowerCase()) return s = t[0], u += r.length, !1
						});
						if (s !== -1) return s + 1;
						throw "Unknown name at position " + u
					},
					x = function () {
						if (n.charAt(u) !== t.charAt(i)) throw "Unexpected literal at position " + u;
						u++
					};
				for (i = 0; i < t.length; i++)
					if (y) t.charAt(i) === "'" && !w("'") ? y = !1 : x();
					else switch (t.charAt(i)) {
					case "d":
						m = E("d");
						break;
					case "D":
						S("D", l, c);
						break;
					case "o":
						g = E("o");
						break;
					case "m":
						v = E("m");
						break;
					case "M":
						v = S("M", h, p);
						break;
					case "y":
						d = E("y");
						break;
					case "@":
						b = new Date(E("@")), d = b.getFullYear(), v = b.getMonth() + 1, m = b.getDate();
						break;
					case "!":
						b = new Date((E("!") - this._ticksTo1970) / 1e4), d = b.getFullYear(), v = b.getMonth() + 1, m = b.getDate();
						break;
					case "'":
						w("'") ? x() : y = !0;
						break;
					default:
						x()
					}
					if (u < n.length) {
						o = n.substr(u);
						if (!/^\s+/.test(o)) throw "Extra/unparsed characters found in date: " + o
					}
				d === -1 ? d = (new Date).getFullYear() : d < 100 && (d += (new Date).getFullYear() - (new Date).getFullYear() % 100 + (d <= f ? 0 : -100));
				if (g > -1) {
					v = 1, m = g;
					do {
						s = this._getDaysInMonth(d, v - 1);
						if (m <= s) break;
						v++, m -= s
					} while (!0)
				}
				b = this._daylightSavingAdjust(new Date(d, v - 1, m));
				if (b.getFullYear() !== d || b.getMonth() + 1 !== v || b.getDate() !== m) throw "Invalid date";
				return b
			},
			ATOM: "yy-mm-dd",
			COOKIE: "D, dd M yy",
			ISO_8601: "yy-mm-dd",
			RFC_822: "D, d M y",
			RFC_850: "DD, dd-M-y",
			RFC_1036: "D, d M y",
			RFC_1123: "D, d M yy",
			RFC_2822: "D, d M yy",
			RSS: "D, d M y",
			TICKS: "!",
			TIMESTAMP: "@",
			W3C: "yy-mm-dd",
			_ticksTo1970: (718685 + Math.floor(492.5) - Math.floor(19.7) + Math.floor(4.925)) * 24 * 60 * 60 * 1e7,
			formatDate: function (e, t, n) {
				if (!t) return "";
				var r, i = (n ? n.dayNamesShort : null) || this._defaults.dayNamesShort,
					s = (n ? n.dayNames : null) || this._defaults.dayNames,
					o = (n ? n.monthNamesShort : null) || this._defaults.monthNamesShort,
					u = (n ? n.monthNames : null) || this._defaults.monthNames,
					a = function (t) {
						var n = r + 1 < e.length && e.charAt(r + 1) === t;
						return n && r++, n
					},
					f = function (e, t, n) {
						var r = "" + t;
						if (a(e))
							while (r.length < n) r = "0" + r;
						return r
					},
					l = function (e, t, n, r) {
						return a(e) ? r[t] : n[t]
					},
					c = "",
					h = !1;
				if (t)
					for (r = 0; r < e.length; r++)
						if (h) e.charAt(r) === "'" && !a("'") ? h = !1 : c += e.charAt(r);
						else switch (e.charAt(r)) {
						case "d":
							c += f("d", t.getDate(), 2);
							break;
						case "D":
							c += l("D", t.getDay(), i, s);
							break;
						case "o":
							c += f("o", Math.round(((new Date(t.getFullYear(), t.getMonth(), t.getDate())).getTime() - (new Date(t.getFullYear(), 0, 0)).getTime()) / 864e5), 3);
							break;
						case "m":
							c += f("m", t.getMonth() + 1, 2);
							break;
						case "M":
							c += l("M", t.getMonth(), o, u);
							break;
						case "y":
							c += a("y") ? t.getFullYear() : (t.getYear() % 100 < 10 ? "0" : "") + t.getYear() % 100;
							break;
						case "@":
							c += t.getTime();
							break;
						case "!":
							c += t.getTime() * 1e4 + this._ticksTo1970;
							break;
						case "'":
							a("'") ? c += "'" : h = !0;
							break;
						default:
							c += e.charAt(r)
						}
						return c
			},
			_possibleChars: function (e) {
				var t, n = "",
					r = !1,
					i = function (n) {
						var r = t + 1 < e.length && e.charAt(t + 1) === n;
						return r && t++, r
					};
				for (t = 0; t < e.length; t++)
					if (r) e.charAt(t) === "'" && !i("'") ? r = !1 : n += e.charAt(t);
					else switch (e.charAt(t)) {
					case "d":
					case "m":
					case "y":
					case "@":
						n += "0123456789";
						break;
					case "D":
					case "M":
						return null;
					case "'":
						i("'") ? n += "'" : r = !0;
						break;
					default:
						n += e.charAt(t)
					}
					return n
			},
			_get: function (e, t) {
				return e.settings[t] !== undefined ? e.settings[t] : this._defaults[t]
			},
			_setDateFromField: function (e, t) {
				if (e.input.val() === e.lastVal) return;
				var n = this._get(e, "dateFormat"),
					r = e.lastVal = e.input ? e.input.val() : null,
					i = this._getDefaultDate(e),
					s = i,
					o = this._getFormatConfig(e);
				try {
					s = this.parseDate(n, r, o) || i
				} catch (u) {
					r = t ? "" : r
				}
				e.selectedDay = s.getDate(), e.drawMonth = e.selectedMonth = s.getMonth(), e.drawYear = e.selectedYear = s.getFullYear(), e.currentDay = r ? s.getDate() : 0, e.currentMonth = r ? s.getMonth() : 0, e.currentYear = r ? s.getFullYear() : 0, this._adjustInstDate(e)
			},
			_getDefaultDate: function (e) {
				return this._restrictMinMax(e, this._determineDate(e, this._get(e, "defaultDate"), new Date))
			},
			_determineDate: function (t, n, r) {
				var i = function (e) {
						var t = new Date;
						return t.setDate(t.getDate() + e), t
					},
					s = function (n) {
						try {
							return e.datepicker.parseDate(e.datepicker._get(t, "dateFormat"), n, e.datepicker._getFormatConfig(t))
						} catch (r) {}
						var i = (n.toLowerCase().match(/^c/) ? e.datepicker._getDate(t) : null) || new Date,
							s = i.getFullYear(),
							o = i.getMonth(),
							u = i.getDate(),
							a = /([+\-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g,
							f = a.exec(n);
						while (f) {
							switch (f[2] || "d") {
							case "d":
							case "D":
								u += parseInt(f[1], 10);
								break;
							case "w":
							case "W":
								u += parseInt(f[1], 10) * 7;
								break;
							case "m":
							case "M":
								o += parseInt(f[1], 10), u = Math.min(u, e.datepicker._getDaysInMonth(s, o));
								break;
							case "y":
							case "Y":
								s += parseInt(f[1], 10), u = Math.min(u, e.datepicker._getDaysInMonth(s, o))
							}
							f = a.exec(n)
						}
						return new Date(s, o, u)
					},
					o = n == null || n === "" ? r : typeof n == "string" ? s(n) : typeof n == "number" ? isNaN(n) ? r : i(n) : new Date(n.getTime());
				return o = o && o.toString() === "Invalid Date" ? r : o, o && (o.setHours(0), o.setMinutes(0), o.setSeconds(0), o.setMilliseconds(0)), this._daylightSavingAdjust(o)
			},
			_daylightSavingAdjust: function (e) {
				return e ? (e.setHours(e.getHours() > 12 ? e.getHours() + 2 : 0), e) : null
			},
			_setDate: function (e, t, n) {
				var r = !t,
					i = e.selectedMonth,
					s = e.selectedYear,
					o = this._restrictMinMax(e, this._determineDate(e, t, new Date));
				e.selectedDay = e.currentDay = o.getDate(), e.drawMonth = e.selectedMonth = e.currentMonth = o.getMonth(), e.drawYear = e.selectedYear = e.currentYear = o.getFullYear(), (i !== e.selectedMonth || s !== e.selectedYear) && !n && this._notifyChange(e), this._adjustInstDate(e), e.input && e.input.val(r ? "" : this._formatDate(e))
			},
			_getDate: function (e) {
				var t = !e.currentYear || e.input && e.input.val() === "" ? null : this._daylightSavingAdjust(new Date(e.currentYear, e.currentMonth, e.currentDay));
				return t
			},
			_attachHandlers: function (t) {
				var n = this._get(t, "stepMonths"),
					r = "#" + t.id.replace(/\\\\/g, "\\");
				t.dpDiv.find("[data-handler]").map(function () {
					var t = {
						prev: function () {
							e.datepicker._adjustDate(r, -n, "M")
						},
						next: function () {
							e.datepicker._adjustDate(r, +n, "M")
						},
						hide: function () {
							e.datepicker._hideDatepicker()
						},
						today: function () {
							e.datepicker._gotoToday(r)
						},
						selectDay: function () {
							return e.datepicker._selectDay(r, +this.getAttribute("data-month"), +this.getAttribute("data-year"), this), !1
						},
						selectMonth: function () {
							return e.datepicker._selectMonthYear(r, this, "M"), !1
						},
						selectYear: function () {
							return e.datepicker._selectMonthYear(r, this, "Y"), !1
						}
					};
					e(this).bind(this.getAttribute("data-event"), t[this.getAttribute("data-handler")])
				})
			},
			_generateHTML: function (e) {
				var t, n, r, i, s, o, u, a, f, l, c, h, p, d, v, m, g, y, b, w, E, S, x, T, N, C, k, L, A, O, M, _, D, P, H, B, j, F, I, q = new Date,
					R = this._daylightSavingAdjust(new Date(q.getFullYear(), q.getMonth(), q.getDate())),
					U = this._get(e, "isRTL"),
					z = this._get(e, "showButtonPanel"),
					W = this._get(e, "hideIfNoPrevNext"),
					X = this._get(e, "navigationAsDateFormat"),
					V = this._getNumberOfMonths(e),
					$ = this._get(e, "showCurrentAtPos"),
					J = this._get(e, "stepMonths"),
					K = V[0] !== 1 || V[1] !== 1,
					Q = this._daylightSavingAdjust(e.currentDay ? new Date(e.currentYear, e.currentMonth, e.currentDay) : new Date(9999, 9, 9)),
					G = this._getMinMaxDate(e, "min"),
					Y = this._getMinMaxDate(e, "max"),
					Z = e.drawMonth - $,
					et = e.drawYear;
				Z < 0 && (Z += 12, et--);
				if (Y) {
					t = this._daylightSavingAdjust(new Date(Y.getFullYear(), Y.getMonth() - V[0] * V[1] + 1, Y.getDate())), t = G && t < G ? G : t;
					while (this._daylightSavingAdjust(new Date(et, Z, 1)) > t) Z--, Z < 0 && (Z = 11, et--)
				}
				e.drawMonth = Z, e.drawYear = et, n = this._get(e, "prevText"), n = X ? this.formatDate(n, this._daylightSavingAdjust(new Date(et, Z - J, 1)), this._getFormatConfig(e)) : n, r = this._canAdjustMonth(e, -1, et, Z) ? "<a class='ui-datepicker-prev ui-corner-all' data-handler='prev' data-event='click' title='" + n + "'><span class='ui-icon ui-icon-circle-triangle-" + (U ? "e" : "w") + "'>" + n + "</span></a>" : W ? "" : "<a class='ui-datepicker-prev ui-corner-all ui-state-disabled' title='" + n + "'><span class='ui-icon ui-icon-circle-triangle-" + (U ? "e" : "w") + "'>" + n + "</span></a>", i = this._get(e, "nextText"), i = X ? this.formatDate(i, this._daylightSavingAdjust(new Date(et, Z + J, 1)), this._getFormatConfig(e)) : i, s = this._canAdjustMonth(e, 1, et, Z) ? "<a class='ui-datepicker-next ui-corner-all' data-handler='next' data-event='click' title='" + i + "'><span class='ui-icon ui-icon-circle-triangle-" + (U ? "w" : "e") + "'>" + i + "</span></a>" : W ? "" : "<a class='ui-datepicker-next ui-corner-all ui-state-disabled' title='" + i + "'><span class='ui-icon ui-icon-circle-triangle-" + (U ? "w" : "e") + "'>" + i + "</span></a>", o = this._get(e, "currentText"), u = this._get(e, "gotoCurrent") && e.currentDay ? Q : R, o = X ? this.formatDate(o, u, this._getFormatConfig(e)) : o, a = e.inline ? "" : "<button type='button' class='ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all' data-handler='hide' data-event='click'>" + this._get(e, "closeText") + "</button>", f = z ? "<div class='ui-datepicker-buttonpane ui-widget-content'>" + (U ? a : "") + (this._isInRange(e, u) ? "<button type='button' class='ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all' data-handler='today' data-event='click'>" + o + "</button>" : "") + (U ? "" : a) + "</div>" : "", l = parseInt(this._get(e, "firstDay"), 10), l = isNaN(l) ? 0 : l, c = this._get(e, "showWeek"), h = this._get(e, "dayNames"), p = this._get(e, "dayNamesMin"), d = this._get(e, "monthNames"), v = this._get(e, "monthNamesShort"), m = this._get(e, "beforeShowDay"), g = this._get(e, "showOtherMonths"), y = this._get(e, "selectOtherMonths"), b = this._getDefaultDate(e), w = "", E;
				for (S = 0; S < V[0]; S++) {
					x = "", this.maxRows = 4;
					for (T = 0; T < V[1]; T++) {
						N = this._daylightSavingAdjust(new Date(et, Z, e.selectedDay)), C = " ui-corner-all", k = "";
						if (K) {
							k += "<div class='ui-datepicker-group";
							if (V[1] > 1) switch (T) {
							case 0:
								k += " ui-datepicker-group-first", C = " ui-corner-" + (U ? "right" : "left");
								break;
							case V[1] - 1:
								k += " ui-datepicker-group-last", C = " ui-corner-" + (U ? "left" : "right");
								break;
							default:
								k += " ui-datepicker-group-middle", C = ""
							}
							k += "'>"
						}
						k += "<div class='ui-datepicker-header ui-widget-header ui-helper-clearfix" + C + "'>" + (/all|left/.test(C) && S === 0 ? U ? s : r : "") + (/all|right/.test(C) && S === 0 ? U ? r : s : "") + this._generateMonthYearHeader(e, Z, et, G, Y, S > 0 || T > 0, d, v) + "</div><table class='ui-datepicker-calendar'><thead>" + "<tr>", L = c ? "<th class='ui-datepicker-week-col'>" + this._get(e, "weekHeader") + "</th>" : "";
						for (E = 0; E < 7; E++) A = (E + l) % 7, L += "<th scope='col'" + ((E + l + 6) % 7 >= 5 ? " class='ui-datepicker-week-end'" : "") + ">" + "<span title='" + h[A] + "'>" + p[A] + "</span></th>";
						k += L + "</tr></thead><tbody>", O = this._getDaysInMonth(et, Z), et === e.selectedYear && Z === e.selectedMonth && (e.selectedDay = Math.min(e.selectedDay, O)), M = (this._getFirstDayOfMonth(et, Z) - l + 7) % 7, _ = Math.ceil((M + O) / 7), D = K ? this.maxRows > _ ? this.maxRows : _ : _, this.maxRows = D, P = this._daylightSavingAdjust(new Date(et, Z, 1 - M));
						for (H = 0; H < D; H++) {
							k += "<tr>", B = c ? "<td class='ui-datepicker-week-col'>" + this._get(e, "calculateWeek")(P) + "</td>" : "";
							for (E = 0; E < 7; E++) j = m ? m.apply(e.input ? e.input[0] : null, [P]) : [!0, ""], F = P.getMonth() !== Z, I = F && !y || !j[0] || G && P < G || Y && P > Y, B += "<td class='" + ((E + l + 6) % 7 >= 5 ? " ui-datepicker-week-end" : "") + (F ? " ui-datepicker-other-month" : "") + (P.getTime() === N.getTime() && Z === e.selectedMonth && e._keyEvent || b.getTime() === P.getTime() && b.getTime() === N.getTime() ? " " + this._dayOverClass : "") + (I ? " " + this._unselectableClass + " ui-state-disabled" : "") + (F && !g ? "" : " " + j[1] + (P.getTime() === Q.getTime() ? " " + this._currentClass : "") + (P.getTime() === R.getTime() ? " ui-datepicker-today" : "")) + "'" + ((!F || g) && j[2] ? " title='" + j[2].replace(/'/g, "&#39;") + "'" : "") + (I ? "" : " data-handler='selectDay' data-event='click' data-month='" + P.getMonth() + "' data-year='" + P.getFullYear() + "'") + ">" + (F && !g ? "&#xa0;" : I ? "<span class='ui-state-default'>" + P.getDate() + "</span>" : "<a class='ui-state-default" + (P.getTime() === R.getTime() ? " ui-state-highlight" : "") + (P.getTime() === Q.getTime() ? " ui-state-active" : "") + (F ? " ui-priority-secondary" : "") + "' href='#'>" + P.getDate() + "</a>") + "</td>", P.setDate(P.getDate() + 1), P = this._daylightSavingAdjust(P);
							k += B + "</tr>"
						}
						Z++, Z > 11 && (Z = 0, et++), k += "</tbody></table>" + (K ? "</div>" + (V[0] > 0 && T === V[1] - 1 ? "<div class='ui-datepicker-row-break'></div>" : "") : ""), x += k
					}
					w += x
				}
				return w += f, e._keyEvent = !1, w
			},
			_generateMonthYearHeader: function (e, t, n, r, i, s, o, u) {
				var a, f, l, c, h, p, d, v, m = this._get(e, "changeMonth"),
					g = this._get(e, "changeYear"),
					y = this._get(e, "showMonthAfterYear"),
					b = "<div class='ui-datepicker-title'>",
					w = "";
				if (s || !m) w += "<span class='ui-datepicker-month'>" + o[t] + "</span>";
				else {
					a = r && r.getFullYear() === n, f = i && i.getFullYear() === n, w += "<select class='ui-datepicker-month' data-handler='selectMonth' data-event='change'>";
					for (l = 0; l < 12; l++)(!a || l >= r.getMonth()) && (!f || l <= i.getMonth()) && (w += "<option value='" + l + "'" + (l === t ? " selected='selected'" : "") + ">" + u[l] + "</option>");
					w += "</select>"
				}
				y || (b += w + (s || !m || !g ? "&#xa0;" : ""));
				if (!e.yearshtml) {
					e.yearshtml = "";
					if (s || !g) b += "<span class='ui-datepicker-year'>" + n + "</span>";
					else {
						c = this._get(e, "yearRange").split(":"), h = (new Date).getFullYear(), p = function (e) {
							var t = e.match(/c[+\-].*/) ? n + parseInt(e.substring(1), 10) : e.match(/[+\-].*/) ? h + parseInt(e, 10) : parseInt(e, 10);
							return isNaN(t) ? h : t
						}, d = p(c[0]), v = Math.max(d, p(c[1] || "")), d = r ? Math.max(d, r.getFullYear()) : d, v = i ? Math.min(v, i.getFullYear()) : v, e.yearshtml += "<select class='ui-datepicker-year' data-handler='selectYear' data-event='change'>";
						for (; d <= v; d++) e.yearshtml += "<option value='" + d + "'" + (d === n ? " selected='selected'" : "") + ">" + d + "</option>";
						e.yearshtml += "</select>", b += e.yearshtml, e.yearshtml = null
					}
				}
				return b += this._get(e, "yearSuffix"), y && (b += (s || !m || !g ? "&#xa0;" : "") + w), b += "</div>", b
			},
			_adjustInstDate: function (e, t, n) {
				var r = e.drawYear + (n === "Y" ? t : 0),
					i = e.drawMonth + (n === "M" ? t : 0),
					s = Math.min(e.selectedDay, this._getDaysInMonth(r, i)) + (n === "D" ? t : 0),
					o = this._restrictMinMax(e, this._daylightSavingAdjust(new Date(r, i, s)));
				e.selectedDay = o.getDate(), e.drawMonth = e.selectedMonth = o.getMonth(), e.drawYear = e.selectedYear = o.getFullYear(), (n === "M" || n === "Y") && this._notifyChange(e)
			},
			_restrictMinMax: function (e, t) {
				var n = this._getMinMaxDate(e, "min"),
					r = this._getMinMaxDate(e, "max"),
					i = n && t < n ? n : t;
				return r && i > r ? r : i
			},
			_notifyChange: function (e) {
				var t = this._get(e, "onChangeMonthYear");
				t && t.apply(e.input ? e.input[0] : null, [e.selectedYear, e.selectedMonth + 1, e])
			},
			_getNumberOfMonths: function (e) {
				var t = this._get(e, "numberOfMonths");
				return t == null ? [1, 1] : typeof t == "number" ? [1, t] : t
			},
			_getMinMaxDate: function (e, t) {
				return this._determineDate(e, this._get(e, t + "Date"), null)
			},
			_getDaysInMonth: function (e, t) {
				return 32 - this._daylightSavingAdjust(new Date(e, t, 32)).getDate()
			},
			_getFirstDayOfMonth: function (e, t) {
				return (new Date(e, t, 1)).getDay()
			},
			_canAdjustMonth: function (e, t, n, r) {
				var i = this._getNumberOfMonths(e),
					s = this._daylightSavingAdjust(new Date(n, r + (t < 0 ? t : i[0] * i[1]), 1));
				return t < 0 && s.setDate(this._getDaysInMonth(s.getFullYear(), s.getMonth())), this._isInRange(e, s)
			},
			_isInRange: function (e, t) {
				var n, r, i = this._getMinMaxDate(e, "min"),
					s = this._getMinMaxDate(e, "max"),
					o = null,
					u = null,
					a = this._get(e, "yearRange");
				return a && (n = a.split(":"), r = (new Date).getFullYear(), o = parseInt(n[0], 10), u = parseInt(n[1], 10), n[0].match(/[+\-].*/) && (o += r), n[1].match(/[+\-].*/) && (u += r)), (!i || t.getTime() >= i.getTime()) && (!s || t.getTime() <= s.getTime()) && (!o || t.getFullYear() >= o) && (!u || t.getFullYear() <= u)
			},
			_getFormatConfig: function (e) {
				var t = this._get(e, "shortYearCutoff");
				return t = typeof t != "string" ? t : (new Date).getFullYear() % 100 + parseInt(t, 10), {
					shortYearCutoff: t,
					dayNamesShort: this._get(e, "dayNamesShort"),
					dayNames: this._get(e, "dayNames"),
					monthNamesShort: this._get(e, "monthNamesShort"),
					monthNames: this._get(e, "monthNames")
				}
			},
			_formatDate: function (e, t, n, r) {
				t || (e.currentDay = e.selectedDay, e.currentMonth = e.selectedMonth, e.currentYear = e.selectedYear);
				var i = t ? typeof t == "object" ? t : this._daylightSavingAdjust(new Date(r, n, t)) : this._daylightSavingAdjust(new Date(e.currentYear, e.currentMonth, e.currentDay));
				return this.formatDate(this._get(e, "dateFormat"), i, this._getFormatConfig(e))
			}
		}), e.fn.datepicker = function (t) {
			if (!this.length) return this;
			e.datepicker.initialized || (e(document).mousedown(e.datepicker._checkExternalClick), e.datepicker.initialized = !0), e("#" + e.datepicker._mainDivId).length === 0 && e("body").append(e.datepicker.dpDiv);
			var n = Array.prototype.slice.call(arguments, 1);
			return typeof t != "string" || t !== "isDisabled" && t !== "getDate" && t !== "widget" ? t === "option" && arguments.length === 2 && typeof arguments[1] == "string" ? e.datepicker["_" + t + "Datepicker"].apply(e.datepicker, [this[0]].concat(n)) : this.each(function () {
				typeof t == "string" ? e.datepicker["_" + t + "Datepicker"].apply(e.datepicker, [this].concat(n)) : e.datepicker._attachDatepicker(this, t)
			}) : e.datepicker["_" + t + "Datepicker"].apply(e.datepicker, [this[0]].concat(n))
		}, e.datepicker = new g, e.datepicker.initialized = !1, e.datepicker.uuid = (new Date).getTime(), e.datepicker.version = "1.11.2";
		var E = e.datepicker,
			S = e.widget("ui.slider", e.ui.mouse, {
				version: "1.11.2",
				widgetEventPrefix: "slide",
				options: {
					animate: !1,
					distance: 0,
					max: 100,
					min: 0,
					orientation: "horizontal",
					range: !1,
					step: 1,
					value: 0,
					values: null,
					change: null,
					slide: null,
					start: null,
					stop: null
				},
				numPages: 5,
				_create: function () {
					this._keySliding = !1, this._mouseSliding = !1, this._animateOff = !0, this._handleIndex = null, this._detectOrientation(), this._mouseInit(), this._calculateNewMax(), this.element.addClass("ui-slider ui-slider-" + this.orientation + " ui-widget" + " ui-widget-content" + " ui-corner-all"), this._refresh(), this._setOption("disabled", this.options.disabled), this._animateOff = !1
				},
				_refresh: function () {
					this._createRange(), this._createHandles(), this._setupEvents(), this._refreshValue()
				},
				_createHandles: function () {
					var t, n, r = this.options,
						i = this.element.find(".ui-slider-handle").addClass("ui-state-default ui-corner-all"),
						s = "<span class='ui-slider-handle ui-state-default ui-corner-all' tabindex='0'></span>",
						o = [];
					n = r.values && r.values.length || 1, i.length > n && (i.slice(n).remove(), i = i.slice(0, n));
					for (t = i.length; t < n; t++) o.push(s);
					this.handles = i.add(e(o.join("")).appendTo(this.element)), this.handle = this.handles.eq(0), this.handles.each(function (t) {
						e(this).data("ui-slider-handle-index", t)
					})
				},
				_createRange: function () {
					var t = this.options,
						n = "";
					t.range ? (t.range === !0 && (t.values ? t.values.length && t.values.length !== 2 ? t.values = [t.values[0], t.values[0]] : e.isArray(t.values) && (t.values = t.values.slice(0)) : t.values = [this._valueMin(), this._valueMin()]), !this.range || !this.range.length ? (this.range = e("<div></div>").appendTo(this.element), n = "ui-slider-range ui-widget-header ui-corner-all") : this.range.removeClass("ui-slider-range-min ui-slider-range-max").css({
						left: "",
						bottom: ""
					}), this.range.addClass(n + (t.range === "min" || t.range === "max" ? " ui-slider-range-" + t.range : ""))) : (this.range && this.range.remove(), this.range = null)
				},
				_setupEvents: function () {
					this._off(this.handles), this._on(this.handles, this._handleEvents), this._hoverable(this.handles), this._focusable(this.handles)
				},
				_destroy: function () {
					this.handles.remove(), this.range && this.range.remove(), this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-widget ui-widget-content ui-corner-all"), this._mouseDestroy()
				},
				_mouseCapture: function (t) {
					var n, r, i, s, o, u, a, f, l = this,
						c = this.options;
					return c.disabled ? !1 : (this.elementSize = {
						width: this.element.outerWidth(),
						height: this.element.outerHeight()
					}, this.elementOffset = this.element.offset(), n = {
						x: t.pageX,
						y: t.pageY
					}, r = this._normValueFromMouse(n), i = this._valueMax() - this._valueMin() + 1, this.handles.each(function (t) {
						var n = Math.abs(r - l.values(t));
						if (i > n || i === n && (t === l._lastChangedValue || l.values(t) === c.min)) i = n, s = e(this), o = t
					}), u = this._start(t, o), u === !1 ? !1 : (this._mouseSliding = !0, this._handleIndex = o, s.addClass("ui-state-active").focus(), a = s.offset(), f = !e(t.target).parents().addBack().is(".ui-slider-handle"), this._clickOffset = f ? {
						left: 0,
						top: 0
					} : {
						left: t.pageX - a.left - s.width() / 2,
						top: t.pageY - a.top - s.height() / 2 - (parseInt(s.css("borderTopWidth"), 10) || 0) - (parseInt(s.css("borderBottomWidth"), 10) || 0) + (parseInt(s.css("marginTop"), 10) || 0)
					}, this.handles.hasClass("ui-state-hover") || this._slide(t, o, r), this._animateOff = !0, !0))
				},
				_mouseStart: function () {
					return !0
				},
				_mouseDrag: function (e) {
					var t = {
							x: e.pageX,
							y: e.pageY
						},
						n = this._normValueFromMouse(t);
					return this._slide(e, this._handleIndex, n), !1
				},
				_mouseStop: function (e) {
					return this.handles.removeClass("ui-state-active"), this._mouseSliding = !1, this._stop(e, this._handleIndex), this._change(e, this._handleIndex), this._handleIndex = null, this._clickOffset = null, this._animateOff = !1, !1
				},
				_detectOrientation: function () {
					this.orientation = this.options.orientation === "vertical" ? "vertical" : "horizontal"
				},
				_normValueFromMouse: function (e) {
					var t, n, r, i, s;
					return this.orientation === "horizontal" ? (t = this.elementSize.width, n = e.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left : 0)) : (t = this.elementSize.height, n = e.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top : 0)), r = n / t, r > 1 && (r = 1), r < 0 && (r = 0), this.orientation === "vertical" && (r = 1 - r), i = this._valueMax() - this._valueMin(), s = this._valueMin() + r * i, this._trimAlignValue(s)
				},
				_start: function (e, t) {
					var n = {
						handle: this.handles[t],
						value: this.value()
					};
					return this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._trigger("start", e, n)
				},
				_slide: function (e, t, n) {
					var r, i, s;
					this.options.values && this.options.values.length ? (r = this.values(t ? 0 : 1), this.options.values.length === 2 && this.options.range === !0 && (t === 0 && n > r || t === 1 && n < r) && (n = r), n !== this.values(t) && (i = this.values(), i[t] = n, s = this._trigger("slide", e, {
						handle: this.handles[t],
						value: n,
						values: i
					}), r = this.values(t ? 0 : 1), s !== !1 && this.values(t, n))) : n !== this.value() && (s = this._trigger("slide", e, {
						handle: this.handles[t],
						value: n
					}), s !== !1 && this.value(n))
				},
				_stop: function (e, t) {
					var n = {
						handle: this.handles[t],
						value: this.value()
					};
					this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._trigger("stop", e, n)
				},
				_change: function (e, t) {
					if (!this._keySliding && !this._mouseSliding) {
						var n = {
							handle: this.handles[t],
							value: this.value()
						};
						this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._lastChangedValue = t, this._trigger("change", e, n)
					}
				},
				value: function (e) {
					if (arguments.length) {
						this.options.value = this._trimAlignValue(e), this._refreshValue(), this._change(null, 0);
						return
					}
					return this._value()
				},
				values: function (t, n) {
					var r, i, s;
					if (arguments.length > 1) {
						this.options.values[t] = this._trimAlignValue(n), this._refreshValue(), this._change(null, t);
						return
					}
					if (!arguments.length) return this._values();
					if (!e.isArray(arguments[0])) return this.options.values && this.options.values.length ? this._values(t) : this.value();
					r = this.options.values, i = arguments[0];
					for (s = 0; s < r.length; s += 1) r[s] = this._trimAlignValue(i[s]), this._change(null, s);
					this._refreshValue()
				},
				_setOption: function (t, n) {
					var r, i = 0;
					t === "range" && this.options.range === !0 && (n === "min" ? (this.options.value = this._values(0), this.options.values = null) : n === "max" && (this.options.value = this._values(this.options.values.length - 1), this.options.values = null)), e.isArray(this.options.values) && (i = this.options.values.length), t === "disabled" && this.element.toggleClass("ui-state-disabled", !!n), this._super(t, n);
					switch (t) {
					case "orientation":
						this._detectOrientation(), this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-" + this.orientation), this._refreshValue(), this.handles.css(n === "horizontal" ? "bottom" : "left", "");
						break;
					case "value":
						this._animateOff = !0, this._refreshValue(), this._change(null, 0), this._animateOff = !1;
						break;
					case "values":
						this._animateOff = !0, this._refreshValue();
						for (r = 0; r < i; r += 1) this._change(null, r);
						this._animateOff = !1;
						break;
					case "step":
					case "min":
					case "max":
						this._animateOff = !0, this._calculateNewMax(), this._refreshValue(), this._animateOff = !1;
						break;
					case "range":
						this._animateOff = !0, this._refresh(), this._animateOff = !1
					}
				},
				_value: function () {
					var e = this.options.value;
					return e = this._trimAlignValue(e), e
				},
				_values: function (e) {
					var t, n, r;
					if (arguments.length) return t = this.options.values[e], t = this._trimAlignValue(t), t;
					if (this.options.values && this.options.values.length) {
						n = this.options.values.slice();
						for (r = 0; r < n.length; r += 1) n[r] = this._trimAlignValue(n[r]);
						return n
					}
					return []
				},
				_trimAlignValue: function (e) {
					if (e <= this._valueMin()) return this._valueMin();
					if (e >= this._valueMax()) return this._valueMax();
					var t = this.options.step > 0 ? this.options.step : 1,
						n = (e - this._valueMin()) % t,
						r = e - n;
					return Math.abs(n) * 2 >= t && (r += n > 0 ? t : -t), parseFloat(r.toFixed(5))
				},
				_calculateNewMax: function () {
					var e = (this.options.max - this._valueMin()) % this.options.step;
					this.max = this.options.max - e
				},
				_valueMin: function () {
					return this.options.min
				},
				_valueMax: function () {
					return this.max
				},
				_refreshValue: function () {
					var t, n, r, i, s, o = this.options.range,
						u = this.options,
						a = this,
						f = this._animateOff ? !1 : u.animate,
						l = {};
					this.options.values && this.options.values.length ? this.handles.each(function (r) {
						n = (a.values(r) - a._valueMin()) / (a._valueMax() - a._valueMin()) * 100, l[a.orientation === "horizontal" ? "left" : "bottom"] = n + "%", e(this).stop(1, 1)[f ? "animate" : "css"](l, u.animate), a.options.range === !0 && (a.orientation === "horizontal" ? (r === 0 && a.range.stop(1, 1)[f ? "animate" : "css"]({
							left: n + "%"
						}, u.animate), r === 1 && a.range[f ? "animate" : "css"]({
							width: n - t + "%"
						}, {
							queue: !1,
							duration: u.animate
						})) : (r === 0 && a.range.stop(1, 1)[f ? "animate" : "css"]({
							bottom: n + "%"
						}, u.animate), r === 1 && a.range[f ? "animate" : "css"]({
							height: n - t + "%"
						}, {
							queue: !1,
							duration: u.animate
						}))), t = n
					}) : (r = this.value(), i = this._valueMin(), s = this._valueMax(), n = s !== i ? (r - i) / (s - i) * 100 : 0, l[this.orientation === "horizontal" ? "left" : "bottom"] = n + "%", this.handle.stop(1, 1)[f ? "animate" : "css"](l, u.animate), o === "min" && this.orientation === "horizontal" && this.range.stop(1, 1)[f ? "animate" : "css"]({
						width: n + "%"
					}, u.animate), o === "max" && this.orientation === "horizontal" && this.range[f ? "animate" : "css"]({
						width: 100 - n + "%"
					}, {
						queue: !1,
						duration: u.animate
					}), o === "min" && this.orientation === "vertical" && this.range.stop(1, 1)[f ? "animate" : "css"]({
						height: n + "%"
					}, u.animate), o === "max" && this.orientation === "vertical" && this.range[f ? "animate" : "css"]({
						height: 100 - n + "%"
					}, {
						queue: !1,
						duration: u.animate
					}))
				},
				_handleEvents: {
					keydown: function (t) {
						var n, r, i, s, o = e(t.target).data("ui-slider-handle-index");
						switch (t.keyCode) {
						case e.ui.keyCode.HOME:
						case e.ui.keyCode.END:
						case e.ui.keyCode.PAGE_UP:
						case e.ui.keyCode.PAGE_DOWN:
						case e.ui.keyCode.UP:
						case e.ui.keyCode.RIGHT:
						case e.ui.keyCode.DOWN:
						case e.ui.keyCode.LEFT:
							t.preventDefault();
							if (!this._keySliding) {
								this._keySliding = !0, e(t.target).addClass("ui-state-active"), n = this._start(t, o);
								if (n === !1) return
							}
						}
						s = this.options.step, this.options.values && this.options.values.length ? r = i = this.values(o) : r = i = this.value();
						switch (t.keyCode) {
						case e.ui.keyCode.HOME:
							i = this._valueMin();
							break;
						case e.ui.keyCode.END:
							i = this._valueMax();
							break;
						case e.ui.keyCode.PAGE_UP:
							i = this._trimAlignValue(r + (this._valueMax() - this._valueMin()) / this.numPages);
							break;
						case e.ui.keyCode.PAGE_DOWN:
							i = this._trimAlignValue(r - (this._valueMax() - this._valueMin()) / this.numPages);
							break;
						case e.ui.keyCode.UP:
						case e.ui.keyCode.RIGHT:
							if (r === this._valueMax()) return;
							i = this._trimAlignValue(r + s);
							break;
						case e.ui.keyCode.DOWN:
						case e.ui.keyCode.LEFT:
							if (r === this._valueMin()) return;
							i = this._trimAlignValue(r - s)
						}
						this._slide(t, o, i)
					},
					keyup: function (t) {
						var n = e(t.target).data("ui-slider-handle-index");
						this._keySliding && (this._keySliding = !1, this._stop(t, n), this._change(t, n), e(t.target).removeClass("ui-state-active"))
					}
				}
			})
	}),
	function (e) {
		"use strict";
		var t = function (t, n) {
			var r = this,
				i = {
					allowFreeEntries: !0,
					allowDuplicates: !1,
					ajaxConfig: {},
					autoSelect: !0,
					selectFirst: !1,
					queryParam: "query",
					beforeSend: function () {},
					cls: "",
					data: null,
					dataUrlParams: {},
					disabled: !1,
					disabledField: null,
					displayField: "name",
					editable: !0,
					expanded: !1,
					expandOnFocus: !1,
					groupBy: null,
					hideTrigger: !1,
					highlight: !0,
					id: null,
					infoMsgCls: "",
					inputCfg: {},
					invalidCls: "ms-inv",
					matchCase: !1,
					maxDropHeight: 290,
					maxEntryLength: null,
					maxEntryRenderer: function (e) {
						return "Please reduce your entry by " + e + " character" + (e > 1 ? "s" : "")
					},
					maxSuggestions: null,
					maxSelection: 10,
					maxSelectionRenderer: function (e) {
						return "You cannot choose more than " + e + " item" + (e > 1 ? "s" : "")
					},
					method: "POST",
					minChars: 0,
					minCharsRenderer: function (e) {
						return "Please type " + e + " more character" + (e > 1 ? "s" : "")
					},
					mode: "local",
					name: null,
					noSuggestionText: "No suggestions",
					placeholder: "Type or click here",
					renderer: null,
					required: !1,
					resultAsString: !1,
					resultAsStringDelimiter: ",",
					resultsField: "results",
					selectionCls: "",
					selectionContainer: null,
					selectionPosition: "inner",
					selectionRenderer: null,
					selectionStacked: !1,
					sortDir: "asc",
					sortOrder: null,
					strictSuggest: !1,
					style: "",
					toggleOnClick: !1,
					typeDelay: 400,
					useTabKey: !1,
					useCommaKey: !0,
					useZebraStyle: !1,
					value: null,
					valueField: "id",
					vregex: null,
					vtype: null
				},
				s = e.extend({}, n),
				o = e.extend(!0, {}, i, s);
			this.addToSelection = function (t, n) {
				if (!o.maxSelection || u.length < o.maxSelection) {
					e.isArray(t) || (t = [t]);
					var i = !1;
					e.each(t, function (t, n) {
						if (o.allowDuplicates || e.inArray(n[o.valueField], r.getValue()) === -1) u.push(n), i = !0
					}), i === !0 && (v._renderSelection(), this.empty(), n !== !0 && e(this).trigger("selectionchange", [this, this.getSelection()]))
				}
				this.input.attr("placeholder", o.selectionPosition === "inner" && this.getValue().length > 0 ? "" : o.placeholder)
			}, this.clear = function (e) {
				this.removeFromSelection(u.slice(0), e)
			}, this.collapse = function () {
				o.expanded === !0 && (this.combobox.detach(), o.expanded = !1, e(this).trigger("collapse", [this]))
			}, this.disable = function () {
				this.container.addClass("ms-ctn-disabled"), o.disabled = !0, r.input.attr("disabled", !0)
			}, this.empty = function () {
				this.input.val("")
			}, this.enable = function () {
				this.container.removeClass("ms-ctn-disabled"), o.disabled = !1, r.input.attr("disabled", !1)
			}, this.expand = function () {
				!o.expanded && (this.input.val().length >= o.minChars || this.combobox.children().size() > 0) && (this.combobox.appendTo(this.container), v._processSuggestions(), o.expanded = !0, e(this).trigger("expand", [this]))
			}, this.isDisabled = function () {
				return o.disabled
			}, this.isValid = function () {
				var t = o.required === !1 || u.length > 0;
				return (o.vtype || o.vregex) && e.each(u, function (e, n) {
					t = t && v._validateSingleItem(n[o.valueField])
				}), t
			}, this.getDataUrlParams = function () {
				return o.dataUrlParams
			}, this.getName = function () {
				return o.name
			}, this.getSelection = function () {
				return u
			}, this.getRawValue = function () {
				return r.input.val()
			}, this.getValue = function () {
				return e.map(u, function (e) {
					return e[o.valueField]
				})
			}, this.removeFromSelection = function (t, n) {
				e.isArray(t) || (t = [t]);
				var i = !1;
				e.each(t, function (t, n) {
					var s = e.inArray(n[o.valueField], r.getValue());
					s > -1 && (u.splice(s, 1), i = !0)
				}), i === !0 && (v._renderSelection(), n !== !0 && e(this).trigger("selectionchange", [this, this.getSelection()]), o.expandOnFocus && r.expand(), o.expanded && v._processSuggestions()), this.input.attr("placeholder", o.selectionPosition === "inner" && this.getValue().length > 0 ? "" : o.placeholder)
			}, this.getData = function () {
				return h
			}, this.setData = function (e) {
				o.data = e, v._processSuggestions()
			}, this.setName = function (t) {
				o.name = t, t && (o.name += t.indexOf("[]") > 0 ? "" : "[]"), r._valueContainer && e.each(r._valueContainer.children(), function (e, t) {
					t.name = o.name
				})
			}, this.setSelection = function (e) {
				this.clear(), this.addToSelection(e)
			}, this.setValue = function (t) {
				var n = [];
				e.each(t, function (t, r) {
					var i = !1;
					e.each(h, function (e, t) {
						if (t[o.valueField] == r) return n.push(t), i = !0, !1
					});
					if (!i)
						if (typeof r == "object") n.push(r);
						else {
							var s = {};
							s[o.valueField] = r, s[o.displayField] = r, n.push(s)
						}
				}), n.length > 0 && this.addToSelection(n)
			}, this.setDataUrlParams = function (t) {
				o.dataUrlParams = e.extend({}, t)
			};
			var u = [],
				a = 0,
				f, l = !1,
				c = null,
				h = [],
				p = !1,
				d = {
					BACKSPACE: 8,
					TAB: 9,
					ENTER: 13,
					CTRL: 17,
					ESC: 27,
					SPACE: 32,
					UPARROW: 38,
					DOWNARROW: 40,
					COMMA: 188
				},
				v = {
					_displaySuggestions: function (t) {
						r.combobox.show(), r.combobox.empty();
						var n = 0,
							i = 0;
						if (c === null) v._renderComboItems(t), n = a * t.length;
						else {
							for (var s in c) i += 1, e("<div/>", {
								"class": "ms-res-group",
								html: s
							}).appendTo(r.combobox), v._renderComboItems(c[s].items, !0);
							var u = r.combobox.find(".ms-res-group").outerHeight();
							if (u !== null) {
								var f = i * u;
								n = a * t.length + f
							} else n = a * (t.length + i)
						}
						n < r.combobox.height() || n <= o.maxDropHeight ? r.combobox.height(n) : n >= r.combobox.height() && n > o.maxDropHeight && r.combobox.height(o.maxDropHeight), t.length === 1 && o.autoSelect === !0 && r.combobox.children().filter(":not(.ms-res-item-disabled):last").addClass("ms-res-item-active"), o.selectFirst === !0 && r.combobox.children().filter(":not(.ms-res-item-disabled):first").addClass("ms-res-item-active");
						if (t.length === 0 && r.getRawValue() !== "") {
							var l = o.noSuggestionText.replace(/\{\{.*\}\}/, r.input.val());
							v._updateHelper(l), r.collapse()
						}
						o.allowFreeEntries === !1 && (t.length === 0 ? (e(r.input).addClass(o.invalidCls), r.combobox.hide()) : e(r.input).removeClass(o.invalidCls))
					},
					_getEntriesFromStringArray: function (t) {
						var n = [];
						return e.each(t, function (t, r) {
							var i = {};
							i[o.displayField] = i[o.valueField] = e.trim(r), n.push(i)
						}), n
					},
					_highlightSuggestion: function (t) {
						var n = r.input.val(),
							i = ["^", "$", "*", "+", "?", ".", "(", ")", ":", "!", "|", "{", "}", "[", "]"];
						e.each(i, function (e, t) {
							n = n.replace(t, "\\" + t)
						});
						if (n.length === 0) return t;
						var s = o.matchCase === !0 ? "g" : "gi";
						return t.replace(new RegExp("(" + n + ")(?!([^<]+)?>)", s), "<em>$1</em>")
					},
					_moveSelectedRow: function (e) {
						o.expanded || r.expand();
						var t, n, i, s;
						t = r.combobox.find(".ms-res-item:not(.ms-res-item-disabled)"), e === "down" ? n = t.eq(0) : n = t.filter(":last"), i = r.combobox.find(".ms-res-item-active:not(.ms-res-item-disabled):first"), i.length > 0 && (e === "down" ? (n = i.nextAll(".ms-res-item:not(.ms-res-item-disabled)").first(), n.length === 0 && (n = t.eq(0)), s = r.combobox.scrollTop(), r.combobox.scrollTop(0), n[0].offsetTop + n.outerHeight() > r.combobox.height() && r.combobox.scrollTop(s + a)) : (n = i.prevAll(".ms-res-item:not(.ms-res-item-disabled)").first(), n.length === 0 && (n = t.filter(":last"), r.combobox.scrollTop(a * t.length)), n[0].offsetTop < r.combobox.scrollTop() && r.combobox.scrollTop(r.combobox.scrollTop() - a))), t.removeClass("ms-res-item-active"), n.addClass("ms-res-item-active")
					},
					_processSuggestions: function (t) {
						var n = null,
							i = t || o.data;
						if (i !== null) {
							typeof i == "function" && (i = i.call(r, r.getRawValue()));
							if (typeof i == "string") {
								e(r).trigger("beforeload", [r]);
								var s = {};
								s[o.queryParam] = r.input.val();
								var u = e.extend(s, o.dataUrlParams);
								e.ajax(e.extend({
									type: o.method,
									url: i,
									data: u,
									beforeSend: o.beforeSend,
									success: function (t) {
										n = typeof t == "string" ? JSON.parse(t) : t, v._processSuggestions(n), e(r).trigger("load", [r, n]), v._asyncValues && (r.setValue(typeof v._asyncValues == "string" ? JSON.parse(v._asyncValues) : v._asyncValues), v._renderSelection(), delete v._asyncValues)
									},
									error: function () {
										throw "Could not reach server"
									}
								}, o.ajaxConfig));
								return
							}
							i.length > 0 && typeof i[0] == "string" ? h = v._getEntriesFromStringArray(i) : h = i[o.resultsField] || i;
							var a = o.mode === "remote" ? h : v._sortAndTrim(h);
							v._displaySuggestions(v._group(a))
						}
					},
					_render: function (t) {
						r.setName(o.name), r.container = e("<div/>", {
							"class": "ms-ctn form-control " + (o.resultAsString ? "ms-as-string " : "") + o.cls + (e(t).hasClass("input-lg") ? " input-lg" : "") + (e(t).hasClass("input-sm") ? " input-sm" : "") + (o.disabled === !0 ? " ms-ctn-disabled" : "") + (o.editable === !0 ? "" : " ms-ctn-readonly") + (o.hideTrigger === !1 ? "" : " ms-no-trigger"),
							style: o.style,
							id: o.id
						}), r.container.focus(e.proxy(m._onFocus, this)), r.container.blur(e.proxy(m._onBlur, this)), r.container.keydown(e.proxy(m._onKeyDown, this)), r.container.keyup(e.proxy(m._onKeyUp, this)), r.input = e("<input/>", e.extend({
							type: "text",
							"class": o.editable === !0 ? "" : " ms-input-readonly",
							readonly: !o.editable,
							placeholder: o.placeholder,
							disabled: o.disabled
						}, o.inputCfg)), r.input.focus(e.proxy(m._onInputFocus, this)), r.input.click(e.proxy(m._onInputClick, this)), r.combobox = e("<div/>", {
							"class": "ms-res-ctn dropdown-menu"
						}).height(o.maxDropHeight), r.combobox.on("click", "div.ms-res-item", e.proxy(m._onComboItemSelected, this)), r.combobox.on("mouseover", "div.ms-res-item", e.proxy(m._onComboItemMouseOver, this)), o.selectionContainer ? (r.selectionContainer = o.selectionContainer, e(r.selectionContainer).addClass("ms-sel-ctn")) : r.selectionContainer = e("<div/>", {
							"class": "ms-sel-ctn"
						}), r.selectionContainer.click(e.proxy(m._onFocus, this)), o.selectionPosition === "inner" && !o.selectionContainer ? r.selectionContainer.append(r.input) : r.container.append(r.input), r.helper = e("<span/>", {
							"class": "ms-helper " + o.infoMsgCls
						}), v._updateHelper(), r.container.append(r.helper), e(t).replaceWith(r.container);
						if (!o.selectionContainer) switch (o.selectionPosition) {
						case "bottom":
							r.selectionContainer.insertAfter(r.container), o.selectionStacked === !0 && (r.selectionContainer.width(r.container.width()), r.selectionContainer.addClass("ms-stacked"));
							break;
						case "right":
							r.selectionContainer.insertAfter(r.container), r.container.css("float", "left");
							break;
						default:
							r.container.append(r.selectionContainer)
						}
						o.hideTrigger === !1 && (r.trigger = e("<div/>", {
							"class": "ms-trigger",
							html: '<div class="ms-trigger-ico"></div>'
						}), r.trigger.click(e.proxy(m._onTriggerClick, this)), r.container.append(r.trigger)), e(window).resize(e.proxy(m._onWindowResized, this));
						if (o.value !== null || o.data !== null) typeof o.data == "string" ? (v._asyncValues = o.value, v._processSuggestions()) : (v._processSuggestions(), o.value !== null && (r.setValue(o.value), v._renderSelection()));
						e("body").click(function (e) {
							r.container.hasClass("ms-ctn-focus") && r.container.has(e.target).length === 0 && e.target.className.indexOf("ms-res-item") < 0 && e.target.className.indexOf("ms-close-btn") < 0 && r.container[0] !== e.target && m._onBlur()
						}), o.expanded === !0 && (o.expanded = !1, r.expand())
					},
					_renderComboItems: function (t, n) {
						var i = this,
							s = "";
						e.each(t, function (t, r) {
							var u = o.renderer !== null ? o.renderer.call(i, r) : r[o.displayField],
								a = o.disabledField !== null && r[o.disabledField] === !0,
								f = e("<div/>", {
									"class": "ms-res-item " + (n ? "ms-res-item-grouped " : "") + (a ? "ms-res-item-disabled " : "") + (t % 2 === 1 && o.useZebraStyle === !0 ? "ms-res-odd" : ""),
									html: o.highlight === !0 ? v._highlightSuggestion(u) : u,
									"data-json": JSON.stringify(r)
								});
							s += e("<div/>").append(f).html()
						}), r.combobox.append(s), a = r.combobox.find(".ms-res-item:first").outerHeight()
					},
					_renderSelection: function () {
						var t = this,
							n = 0,
							i = 0,
							s = [],
							a = o.resultAsString === !0 && !l;
						r.selectionContainer.find(".ms-sel-item").remove(), r._valueContainer !== undefined && r._valueContainer.remove(), e.each(u, function (n, r) {
							var i, f, l = o.selectionRenderer !== null ? o.selectionRenderer.call(t, r) : r[o.displayField],
								c = v._validateSingleItem(r[o.displayField]) ? "" : " ms-sel-invalid";
							a === !0 ? i = e("<div/>", {
								"class": "ms-sel-item ms-sel-text " + o.selectionCls + c,
								html: l + (n === u.length - 1 ? "" : o.resultAsStringDelimiter)
							}).data("json", r) : (i = e("<div/>", {
								"class": "ms-sel-item " + o.selectionCls + c,
								html: l
							}).data("json", r), o.disabled === !1 && (f = e("<span/>", {
								"class": "ms-close-btn"
							}).data("json", r).appendTo(i), f.click(e.proxy(m._onTagTriggerClick, t)))), s.push(i)
						}), r.selectionContainer.prepend(s), r._valueContainer = e("<div/>", {
							style: "display: none;"
						}), e.each(r.getValue(), function (t, n) {
							var i = e("<input/>", {
								type: "hidden",
								name: o.name,
								value: n
							});
							i.appendTo(r._valueContainer)
						}), r._valueContainer.appendTo(r.selectionContainer), o.selectionPosition === "inner" && !o.selectionContainer && (r.input.width(0), i = r.input.offset().left - r.selectionContainer.offset().left, n = r.container.width() - i - 42, r.input.width(n)), u.length === o.maxSelection ? v._updateHelper(o.maxSelectionRenderer.call(this, u.length)) : r.helper.hide()
					},
					_selectItem: function (e) {
						o.maxSelection === 1 && (u = []), r.addToSelection(e.data("json")), e.removeClass("ms-res-item-active"), (o.expandOnFocus === !1 || u.length === o.maxSelection) && r.collapse(), l ? l && (o.expandOnFocus || p) && (v._processSuggestions(), p && r.expand()) : r.input.focus()
					},
					_sortAndTrim: function (t) {
						var n = r.getRawValue(),
							i = [],
							s = [],
							u = r.getValue();
						return n.length > 0 ? e.each(t, function (e, t) {
							var r = t[o.displayField];
							(o.matchCase === !0 && r.indexOf(n) > -1 || o.matchCase === !1 && r.toLowerCase().indexOf(n.toLowerCase()) > -1) && (o.strictSuggest === !1 || r.toLowerCase().indexOf(n.toLowerCase()) === 0) && i.push(t)
						}) : i = t, e.each(i, function (t, n) {
							(o.allowDuplicates || e.inArray(n[o.valueField], u) === -1) && s.push(n)
						}), o.sortOrder !== null && s.sort(function (e, t) {
							return e[o.sortOrder] < t[o.sortOrder] ? o.sortDir === "asc" ? -1 : 1 : e[o.sortOrder] > t[o.sortOrder] ? o.sortDir === "asc" ? 1 : -1 : 0
						}), o.maxSuggestions && o.maxSuggestions > 0 && (s = s.slice(0, o.maxSuggestions)), s
					},
					_group: function (t) {
						return o.groupBy !== null && (c = {}, e.each(t, function (e, t) {
							var n = o.groupBy.indexOf(".") > -1 ? o.groupBy.split(".") : o.groupBy,
								r = t[o.groupBy];
							if (typeof n != "string") {
								r = t;
								while (n.length > 0) r = r[n.shift()]
							}
							c[r] === undefined ? c[r] = {
								title: r,
								items: [t]
							} : c[r].items.push(t)
						})), t
					},
					_updateHelper: function (e) {
						r.helper.html(e), r.helper.is(":visible") || r.helper.fadeIn()
					},
					_validateSingleItem: function (e) {
						if (o.vregex !== null && o.vregex instanceof RegExp) return o.vregex.test(e);
						if (o.vtype !== null) switch (o.vtype) {
						case "alpha":
							return /^[a-zA-Z_]+$/.test(e);
						case "alphanum":
							return /^[a-zA-Z0-9_]+$/.test(e);
						case "email":
							return /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/.test(e);
						case "url":
							return /(((^https?)|(^ftp)):\/\/([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i.test(e);
						case "ipaddress":
							return /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(e)
						}
						return !0
					}
				},
				m = {
					_onBlur: function () {
						r.container.removeClass("ms-ctn-focus"), r.collapse(), l = !1;
						if (r.getRawValue() !== "" && o.allowFreeEntries === !0) {
							var t = {};
							t[o.displayField] = t[o.valueField] = r.getRawValue().trim(), r.addToSelection(t)
						}
						v._renderSelection(), r.isValid() === !1 ? r.container.addClass(o.invalidCls) : r.input.val() !== "" && o.allowFreeEntries === !1 && (r.empty(), v._updateHelper("")), e(r).trigger("blur", [r])
					},
					_onComboItemMouseOver: function (t) {
						var n = e(t.currentTarget);
						n.hasClass("ms-res-item-disabled") || (r.combobox.children().removeClass("ms-res-item-active"), n.addClass("ms-res-item-active"))
					},
					_onComboItemSelected: function (t) {
						var n = e(t.currentTarget);
						n.hasClass("ms-res-item-disabled") || v._selectItem(e(t.currentTarget))
					},
					_onFocus: function () {
						r.input.focus()
					},
					_onInputClick: function () {
						r.isDisabled() === !1 && l && o.toggleOnClick === !0 && (o.expanded ? r.collapse() : r.expand())
					},
					_onInputFocus: function () {
						if (r.isDisabled() === !1 && !l) {
							l = !0, r.container.addClass("ms-ctn-focus"), r.container.removeClass(o.invalidCls);
							var t = r.getRawValue().length;
							o.expandOnFocus === !0 && r.expand(), u.length === o.maxSelection ? v._updateHelper(o.maxSelectionRenderer.call(this, u.length)) : t < o.minChars && v._updateHelper(o.minCharsRenderer.call(this, o.minChars - t)), v._renderSelection(), e(r).trigger("focus", [r])
						}
					},
					_onKeyDown: function (t) {
						var n = r.combobox.find(".ms-res-item-active:not(.ms-res-item-disabled):first"),
							i = r.input.val();
						e(r).trigger("keydown", [r, t]);
						if (t.keyCode === d.TAB && (o.useTabKey === !1 || o.useTabKey === !0 && n.length === 0 && r.input.val().length === 0)) {
							m._onBlur();
							return
						}
						switch (t.keyCode) {
						case d.BACKSPACE:
							i.length === 0 && r.getSelection().length > 0 && o.selectionPosition === "inner" && (u.pop(), v._renderSelection(), e(r).trigger("selectionchange", [r, r.getSelection()]), r.input.attr("placeholder", o.selectionPosition === "inner" && r.getValue().length > 0 ? "" : o.placeholder), r.input.focus(), t.preventDefault());
							break;
						case d.TAB:
						case d.ESC:
							t.preventDefault();
							break;
						case d.ENTER:
							(i !== "" || o.expanded) && t.preventDefault();
							break;
						case d.COMMA:
							o.useCommaKey === !0 && t.preventDefault();
							break;
						case d.CTRL:
							p = !0;
							break;
						case d.DOWNARROW:
							t.preventDefault(), v._moveSelectedRow("down");
							break;
						case d.UPARROW:
							t.preventDefault(), v._moveSelectedRow("up");
							break;
						default:
							u.length === o.maxSelection && t.preventDefault()
						}
					},
					_onKeyUp: function (t) {
						var n = r.getRawValue(),
							i = e.trim(r.input.val()).length > 0 && (!o.maxEntryLength || e.trim(r.input.val()).length <= o.maxEntryLength),
							s, a = {};
						e(r).trigger("keyup", [r, t]), clearTimeout(f), t.keyCode === d.ESC && o.expanded && r.combobox.hide();
						if (t.keyCode === d.TAB && o.useTabKey === !1 || t.keyCode > d.ENTER && t.keyCode < d.SPACE) {
							t.keyCode === d.CTRL && (p = !1);
							return
						}
						switch (t.keyCode) {
						case d.UPARROW:
						case d.DOWNARROW:
							t.preventDefault();
							break;
						case d.ENTER:
						case d.TAB:
						case d.COMMA:
							if (t.keyCode !== d.COMMA || o.useCommaKey === !0) {
								t.preventDefault();
								if (o.expanded === !0) {
									s = r.combobox.find(".ms-res-item-active:not(.ms-res-item-disabled):first");
									if (s.length > 0) {
										v._selectItem(s);
										return
									}
								}
								i === !0 && o.allowFreeEntries === !0 && (a[o.displayField] = a[o.valueField] = n.trim(), r.addToSelection(a), r.collapse(), r.input.focus());
								break
							};
						default:
							u.length === o.maxSelection ? v._updateHelper(o.maxSelectionRenderer.call(this, u.length)) : n.length < o.minChars ? (v._updateHelper(o.minCharsRenderer.call(this, o.minChars - n.length)), o.expanded === !0 && r.collapse()) : o.maxEntryLength && n.length > o.maxEntryLength ? (v._updateHelper(o.maxEntryRenderer.call(this, n.length - o.maxEntryLength)), o.expanded === !0 && r.collapse()) : (r.helper.hide(), o.minChars <= n.length && (f = setTimeout(function () {
								o.expanded === !0 ? v._processSuggestions() : r.expand()
							}, o.typeDelay)))
						}
					},
					_onTagTriggerClick: function (t) {
						r.removeFromSelection(e(t.currentTarget).data("json"))
					},
					_onTriggerClick: function () {
						if (r.isDisabled() === !1 && (o.expandOnFocus !== !0 || u.length !== o.maxSelection)) {
							e(r).trigger("triggerclick", [r]);
							if (o.expanded === !0) r.collapse();
							else {
								var t = r.getRawValue().length;
								t >= o.minChars ? (r.input.focus(), r.expand()) : v._updateHelper(o.minCharsRenderer.call(this, o.minChars - t))
							}
						}
					},
					_onWindowResized: function () {
						v._renderSelection()
					}
				};
			t !== null && v._render(t)
		};
		e.fn.magicSuggest = function (n) {
			var r = e(this);
			return r.size() === 1 && r.data("magicSuggest") ? r.data("magicSuggest") : (r.each(function (r) {
				var i = e(this);
				if (i.data("magicSuggest")) return;
				this.nodeName.toLowerCase() === "select" && (n.data = [], n.value = [], e.each(this.children, function (t, r) {
					r.nodeName && r.nodeName.toLowerCase() === "option" && (n.data.push({
						id: r.value,
						name: r.text
					}), e(r).attr("selected") && n.value.push(r.value))
				}));
				var s = {};
				e.each(this.attributes, function (e, t) {
					s[t.name] = t.name === "value" && t.value !== "" ? JSON.parse(t.value) : t.value
				});
				var o = new t(this, e.extend([], e.fn.magicSuggest.defaults, n, s));
				i.data("magicSuggest", o), o.container.data("magicSuggest", o)
			}), r.size() === 1 ? r.data("magicSuggest") : r)
		}, e.fn.magicSuggest.defaults = {}
	}(jQuery);
var jscolor = {
	dir: "",
	bindClass: "color",
	binding: !0,
	install: function () {
		$(jscolor.init), $(document).on("mousedown", ".jscolor-ok, .jscolor-no", function (e) {
			var t = $(jscolor.target),
				n = $(e.target).hasClass("jscolor-no"),
				r = n ? "transparent" : t.css("background-color");
			t.trigger("color", [r])
		})
	},
	init: function () {
		jscolor.binding && jscolor.bind()
	},
	bind: function () {
		var e = new RegExp("(^|\\s)(" + jscolor.bindClass + ")(\\s*(\\{[^}]*\\})|\\s|$)", "i"),
			t = document.getElementsByTagName("input");
		for (var n = 0; n < t.length; n += 1) {
			var r;
			if (!t[n].color && t[n].className && (r = t[n].className.match(e))) {
				var i = {};
				if (r[4]) try {
					i = (new Function("return (" + r[4] + ")"))()
				} catch (s) {}
				t[n].color = new jscolor.color(t[n], i)
			}
		}
	},
	images: {
		pad: [181, 101],
		sld: [16, 101],
		cross: [15, 15],
		arrow: [7, 11]
	},
	imgRequire: {},
	imgLoaded: {},
	requireImage: function (e) {
		jscolor.imgRequire[e] = !0
	},
	loadImage: function (e) {
		jscolor.imgLoaded[e] || (jscolor.imgLoaded[e] = new Image, jscolor.imgLoaded[e].src = jscolor.getDir() + e)
	},
	fetchElement: function (e) {
		return typeof e == "string" ? document.getElementById(e) : e
	},
	addEvent: function (e, t, n) {
		e.addEventListener ? e.addEventListener(t, n, !1) : e.attachEvent && e.attachEvent("on" + t, n)
	},
	fireEvent: function (e, t) {
		if (!e) return;
		if (document.createEvent) {
			var n = document.createEvent("HTMLEvents");
			n.initEvent(t, !0, !0), e.dispatchEvent(n)
		} else if (document.createEventObject) {
			var n = document.createEventObject();
			e.fireEvent("on" + t, n)
		} else e["on" + t] && e["on" + t]()
	},
	getElementPos: function (e) {
		var t = e,
			n = e,
			r = 0,
			i = 0;
		if (t.offsetParent)
			do r += t.offsetLeft, i += t.offsetTop; while (t = t.offsetParent);
		while ((n = n.parentNode) && n.nodeName.toUpperCase() !== "BODY") r -= n.scrollLeft, i -= n.scrollTop;
		return [r, i]
	},
	getElementSize: function (e) {
		return [e.offsetWidth, e.offsetHeight]
	},
	getRelMousePos: function (e) {
		var t = 0,
			n = 0;
		return e || (e = window.event), typeof e.offsetX == "number" ? (t = e.offsetX, n = e.offsetY) : typeof e.layerX == "number" && (t = e.layerX, n = e.layerY), {
			x: t,
			y: n
		}
	},
	getViewPos: function () {
		return typeof window.pageYOffset == "number" ? [window.pageXOffset, window.pageYOffset] : document.body && (document.body.scrollLeft || document.body.scrollTop) ? [document.body.scrollLeft, document.body.scrollTop] : document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop) ? [document.documentElement.scrollLeft, document.documentElement.scrollTop] : [0, 0]
	},
	getViewSize: function () {
		return typeof window.innerWidth == "number" ? [window.innerWidth, window.innerHeight] : document.body && (document.body.clientWidth || document.body.clientHeight) ? [document.body.clientWidth, document.body.clientHeight] : document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight) ? [document.documentElement.clientWidth, document.documentElement.clientHeight] : [0, 0]
	},
	URI: function (e) {
		function t(e) {
			var t = "";
			while (e)
				if (e.substr(0, 3) === "../" || e.substr(0, 2) === "./") e = e.replace(/^\.+/, "").substr(1);
				else if (e.substr(0, 3) === "/./" || e === "/.") e = "/" + e.substr(3);
			else if (e.substr(0, 4) === "/../" || e === "/..") e = "/" + e.substr(4), t = t.replace(/\/?[^\/]*$/, "");
			else if (e === "." || e === "..") e = "";
			else {
				var n = e.match(/^\/?[^\/]*/)[0];
				e = e.substr(n.length), t += n
			}
			return t
		}
		this.scheme = null, this.authority = null, this.path = "", this.query = null, this.fragment = null, this.parse = function (e) {
			var t = e.match(/^(([A-Za-z][0-9A-Za-z+.-]*)(:))?((\/\/)([^\/?#]*))?([^?#]*)((\?)([^#]*))?((#)(.*))?/);
			return this.scheme = t[3] ? t[2] : null, this.authority = t[5] ? t[6] : null, this.path = t[7], this.query = t[9] ? t[10] : null, this.fragment = t[12] ? t[13] : null, this
		}, this.toString = function () {
			var e = "";
			return this.scheme !== null && (e = e + this.scheme + ":"), this.authority !== null && (e = e + "//" + this.authority), this.path !== null && (e += this.path), this.query !== null && (e = e + "?" + this.query), this.fragment !== null && (e = e + "#" + this.fragment), e
		}, this.toAbsolute = function (e) {
			var e = new jscolor.URI(e),
				n = this,
				r = new jscolor.URI;
			return e.scheme === null ? !1 : (n.scheme !== null && n.scheme.toLowerCase() === e.scheme.toLowerCase() && (n.scheme = null), n.scheme !== null ? (r.scheme = n.scheme, r.authority = n.authority, r.path = t(n.path), r.query = n.query) : (n.authority !== null ? (r.authority = n.authority, r.path = t(n.path), r.query = n.query) : (n.path === "" ? (r.path = e.path, n.query !== null ? r.query = n.query : r.query = e.query) : (n.path.substr(0, 1) === "/" ? r.path = t(n.path) : (e.authority !== null && e.path === "" ? r.path = "/" + n.path : r.path = e.path.replace(/[^\/]+$/, "") + n.path, r.path = t(r.path)), r.query = n.query), r.authority = e.authority), r.scheme = e.scheme), r.fragment = n.fragment, r)
		}, e && this.parse(e)
	},
	target: null,
	color: function (e, t) {
		function r(e, t, n) {
			var r = Math.min(Math.min(e, t), n),
				i = Math.max(Math.max(e, t), n),
				s = i - r;
			if (s === 0) return [null, 0, i];
			var o = e === r ? 3 + (n - t) / s : t === r ? 5 + (e - n) / s : 1 + (t - e) / s;
			return [o === 6 ? 0 : o, s / i, i]
		}

		function i(e, t, n) {
			if (e === null) return [n, n, n];
			var r = Math.floor(e),
				i = r % 2 ? e - r : 1 - (e - r),
				s = n * (1 - t),
				o = n * (1 - t * i);
			switch (r) {
			case 6:
			case 0:
				return [n, o, s];
			case 1:
				return [o, n, s];
			case 2:
				return [s, n, o];
			case 3:
				return [s, o, n];
			case 4:
				return [o, s, n];
			case 5:
				return [n, s, o]
			}
		}

		function s() {
			delete jscolor.picker.owner, document.getElementsByTagName("body")[0].removeChild(jscolor.picker.boxB)
		}

		function o(t, n) {
			function w() {
				var e = m.pickerInsetColor.split(/\s+/),
					t = e.length < 2 ? e[0] : e[1] + " " + e[0] + " " + e[0] + " " + e[1];
				o.btn.style.borderColor = t
			}
			if (!jscolor.picker) {
				jscolor.picker = {
					box: $("<div/>").addClass("box")[0],
					boxB: $("<div/>").addClass("boxB")[0],
					pad: $("<div/>").addClass("pad")[0],
					padB: $("<div/>").addClass("padB")[0],
					padM: $("<div/>").addClass("padM")[0],
					sld: $("<div/>").addClass("sld")[0],
					sldB: $("<div/>").addClass("sldB")[0],
					sldM: $("<div/>").addClass("sldM")[0],
					btn: $("<div/>").addClass("btn")[0],
					btnS: $("<pan/>").addClass("btnS")[0],
					btnT: document.createTextNode(m.pickerCloseText)
				};
				for (var r = 0, i = 4; r < jscolor.images.sld[1]; r += i) {
					var s = document.createElement("div");
					s.style.height = i + "px", s.style.fontSize = "1px", s.style.lineHeight = "0", jscolor.picker.sld.appendChild(s)
				}
				jscolor.picker.sldB.appendChild(jscolor.picker.sld), jscolor.picker.box.appendChild(jscolor.picker.sldB), jscolor.picker.box.appendChild(jscolor.picker.sldM), jscolor.picker.padB.appendChild(jscolor.picker.pad), jscolor.picker.box.appendChild(jscolor.picker.padB), jscolor.picker.box.appendChild(jscolor.picker.padM), jscolor.picker.btnS.appendChild(jscolor.picker.btnT), jscolor.picker.btn.appendChild(jscolor.picker.btnS), jscolor.picker.box.appendChild(jscolor.picker.btn), jscolor.picker.boxB.appendChild(jscolor.picker.box), $(jscolor.picker.boxB).append('<div class="jscolor-tool"><button class="jscolor-no"></button><input  class="jscolor-value" /><button class="jscolor-ok"><i class="ai-ok"></i></button><button class="jscolor-close"><i class="ai-remove"></i></button></div>')
			}
			var o = jscolor.picker;
			o.box.onmouseup = o.box.onmouseout = function () {
				e.focus()
			}, o.box.onmousedown = function () {
				y = !0
			}, o.box.onmousemove = function (e) {
				if (E || S) E && p(e), S && d(e), document.selection ? document.selection.empty() : window.getSelection && window.getSelection().removeAllRanges(), v()
			};
			if ("ontouchstart" in window) {
				var l = function (e) {
					var t = {
						offsetX: e.touches[0].pageX - x.X,
						offsetY: e.touches[0].pageY - x.Y
					};
					if (E || S) E && p(t), S && d(t), v();
					e.stopPropagation(), e.preventDefault()
				};
				o.box.removeEventListener("touchmove", l, !1), o.box.addEventListener("touchmove", l, !1)
			}
			o.padM.onmouseup = o.padM.onmouseout = function () {
				E && (E = !1, jscolor.fireEvent(b, "change"))
			}, o.padM.onmousedown = function (e) {
				switch (g) {
				case 0:
					m.hsv[2] === 0 && m.fromHSV(null, null, 1);
					break;
				case 1:
					m.hsv[1] === 0 && m.fromHSV(null, 1, null)
				}
				S = !1, E = !0, p(e), v()
			}, "ontouchstart" in window && o.padM.addEventListener("touchstart", function (e) {
				x = {
					X: e.target.offsetParent.offsetLeft,
					Y: e.target.offsetParent.offsetTop
				}, this.onmousedown({
					offsetX: e.touches[0].pageX - x.X,
					offsetY: e.touches[0].pageY - x.Y
				})
			}), o.sldM.onmouseup = o.sldM.onmouseout = function () {
				S && (S = !1, jscolor.fireEvent(b, "change"))
			}, o.sldM.onmousedown = function (e) {
				E = !1, S = !0, d(e), v()
			}, "ontouchstart" in window && o.sldM.addEventListener("touchstart", function (e) {
				x = {
					X: e.target.offsetParent.offsetLeft,
					Y: e.target.offsetParent.offsetTop
				}, this.onmousedown({
					offsetX: e.touches[0].pageX - x.X,
					offsetY: e.touches[0].pageY - x.Y
				})
			});
			var c = u(m);
			o.box.style.width = c[0] + "px", o.box.style.height = c[1] + "px", o.boxB.className = "jscolor", o.boxB.style.position = "absolute", o.boxB.style.clear = "both", o.boxB.style.left = t + "px", o.boxB.style.top = n + "px", o.boxB.style.zIndex = m.pickerZIndex, o.boxB.style.border = m.pickerBorder + "px solid", o.boxB.style.borderColor = m.pickerBorderColor, o.boxB.style.background = m.pickerFaceColor, o.pad.style.width = jscolor.images.pad[0] + "px", o.pad.style.height = jscolor.images.pad[1] + "px", o.padB.style.position = "absolute", o.padB.style.left = m.pickerFace + "px", o.padB.style.top = m.pickerFace + "px", o.padB.style.border = m.pickerInset + "px solid", o.padB.style.borderColor = m.pickerInsetColor, o.padM.style.position = "absolute", o.padM.style.left = "0", o.padM.style.top = "0", o.padM.style.width = m.pickerFace + 2 * m.pickerInset + jscolor.images.pad[0] + jscolor.images.arrow[0] + "px", o.padM.style.height = o.box.style.height, o.padM.style.cursor = "crosshair", o.sld.style.overflow = "hidden", o.sld.style.width = jscolor.images.sld[0] + "px", o.sld.style.height = jscolor.images.sld[1] + "px", o.sldB.style.display = m.slider ? "block" : "none", o.sldB.style.position = "absolute", o.sldB.style.right = m.pickerFace + "px", o.sldB.style.top = m.pickerFace + "px", o.sldB.style.border = m.pickerInset + "px solid", o.sldB.style.borderColor = m.pickerInsetColor, o.sldM.style.display = m.slider ? "block" : "none", o.sldM.style.position = "absolute", o.sldM.style.right = "0", o.sldM.style.top = "0", o.sldM.style.width = jscolor.images.sld[0] + jscolor.images.arrow[0] + m.pickerFace + 2 * m.pickerInset + "px", o.sldM.style.height = o.box.style.height;
			try {
				o.sldM.style.cursor = "pointer"
			} catch (h) {
				o.sldM.style.cursor = "hand"
			}
			o.btn.style.display = m.pickerClosable ? "block" : "none", o.btn.style.position = "absolute", o.btn.style.left = m.pickerFace + "px", o.btn.style.bottom = m.pickerFace + "px", o.btn.style.padding = "0 15px", o.btn.style.height = "18px", o.btn.style.border = m.pickerInset + "px solid", w(), o.btn.style.color = m.pickerButtonColor, o.btn.style.font = "12px sans-serif", o.btn.style.textAlign = "center";
			try {
				o.btn.style.cursor = "pointer"
			} catch (h) {
				o.btn.style.cursor = "hand"
			}
			o.btn.onmousedown = function () {
				m.hidePicker()
			}, o.btnS.style.lineHeight = o.btn.style.height;
			switch (g) {
			case 0:
				$(jscolor.boxB).addClass("jscolor-hs");
				break;
			case 1:
				$(jscolor.boxB).addClass("jscolor-hv")
			}
			a(), f(), jscolor.picker.owner = m, document.getElementsByTagName("body")[0].appendChild(o.boxB)
		}

		function u(e) {
			var t = [2 * e.pickerInset + 2 * e.pickerFace + jscolor.images.pad[0] + (e.slider ? 2 * e.pickerInset + 2 * jscolor.images.arrow[0] + jscolor.images.sld[0] : 0), e.pickerClosable ? 4 * e.pickerInset + 3 * e.pickerFace + jscolor.images.pad[1] + e.pickerButtonHeight : 2 * e.pickerInset + 2 * e.pickerFace + jscolor.images.pad[1]];
			return t
		}

		function a() {
			switch (g) {
			case 0:
				var e = 1;
				break;
			case 1:
				var e = 2
			}
			var t = Math.round(m.hsv[0] / 6 * (jscolor.images.pad[0] - 1)),
				n = Math.round((1 - m.hsv[e]) * (jscolor.images.pad[1] - 1));
			jscolor.picker.padM.style.backgroundPosition = m.pickerFace + m.pickerInset + t - Math.floor(jscolor.images.cross[0] / 2) + "px " + (m.pickerFace + m.pickerInset + n - Math.floor(jscolor.images.cross[1] / 2)) + "px";
			var r = jscolor.picker.sld.childNodes;
			switch (g) {
			case 0:
				var s = i(m.hsv[0], m.hsv[1], 1);
				for (var o = 0; o < r.length; o += 1) r[o].style.backgroundColor = "rgb(" + s[0] * (1 - o / r.length) * 100 + "%," + s[1] * (1 - o / r.length) * 100 + "%," + s[2] * (1 - o / r.length) * 100 + "%)";
				break;
			case 1:
				var s, u, a = [m.hsv[2], 0, 0],
					o = Math.floor(m.hsv[0]),
					f = o % 2 ? m.hsv[0] - o : 1 - (m.hsv[0] - o);
				switch (o) {
				case 6:
				case 0:
					s = [0, 1, 2];
					break;
				case 1:
					s = [1, 0, 2];
					break;
				case 2:
					s = [2, 0, 1];
					break;
				case 3:
					s = [2, 1, 0];
					break;
				case 4:
					s = [1, 2, 0];
					break;
				case 5:
					s = [0, 2, 1]
				}
				for (var o = 0; o < r.length; o += 1) u = 1 - 1 / (r.length - 1) * o, a[1] = a[0] * (1 - u * f), a[2] = a[0] * (1 - u), r[o].style.backgroundColor = "rgb(" + a[s[0]] * 100 + "%," + a[s[1]] * 100 + "%," + a[s[2]] * 100 + "%)"
			}
		}

		function f() {
			switch (g) {
			case 0:
				var e = 2;
				break;
			case 1:
				var e = 1
			}
			var t = Math.round((1 - m.hsv[e]) * (jscolor.images.sld[1] - 1));
			jscolor.picker.sldM.style.backgroundPosition = "0 " + (m.pickerFace + m.pickerInset + t - Math.floor(jscolor.images.arrow[1] / 2)) + "px"
		}

		function l() {
			return jscolor.picker && jscolor.picker.owner === m
		}

		function c() {
			b === e && m.importColor(), m.pickerOnfocus && m.hidePicker()
		}

		function h() {
			b !== e && m.importColor()
		}

		function p(e) {
			var t = jscolor.getRelMousePos(e),
				n = t.x - m.pickerFace - m.pickerInset,
				r = t.y - m.pickerFace - m.pickerInset;
			switch (g) {
			case 0:
				m.fromHSV(n * (6 / (jscolor.images.pad[0] - 1)), 1 - r / (jscolor.images.pad[1] - 1), null, k);
				break;
			case 1:
				m.fromHSV(n * (6 / (jscolor.images.pad[0] - 1)), null, 1 - r / (jscolor.images.pad[1] - 1), k)
			}
		}

		function d(e) {
			var t = jscolor.getRelMousePos(e),
				n = t.y - m.pickerFace - m.pickerInset;
			switch (g) {
			case 0:
				m.fromHSV(null, null, 1 - n / (jscolor.images.sld[1] - 1), C);
				break;
			case 1:
				m.fromHSV(null, 1 - n / (jscolor.images.sld[1] - 1), null, C)
			}
		}

		function v() {
			if (m.onImmediateChange) {
				var e;
				typeof m.onImmediateChange == "string" ? e = new Function(m.onImmediateChange) : e = m.onImmediateChange, e.call(m)
			}
		}
		this.required = !0, this.adjust = !0, this.hash = !1, this.caps = !0, this.slider = !0, this.valueElement = e, this.styleElement = e, this.onImmediateChange = null, this.hsv = [0, 0, 1], this.rgb = [1, 1, 1], this.minH = 0, this.maxH = 6, this.minS = 0, this.maxS = 1, this.minV = 0, this.maxV = 1, this.pickerOnfocus = !0, this.pickerMode = "HSV", this.pickerPosition = "bottom", this.pickerSmartPosition = !0, this.pickerButtonHeight = 20, this.pickerClosable = !1, this.pickerCloseText = "Close", this.pickerButtonColor = "ButtonText", this.pickerFace = 10, this.pickerFaceColor = "ThreeDFace", this.pickerBorder = 1, this.pickerBorderColor = "ThreeDHighlight ThreeDShadow ThreeDShadow ThreeDHighlight", this.pickerInset = 1, this.pickerInsetColor = "ThreeDShadow ThreeDHighlight ThreeDHighlight ThreeDShadow", this.pickerZIndex = 10000010;
		for (var n in t) t.hasOwnProperty(n) && (this[n] = t[n]);
		this.hidePicker = function () {
			l() && s()
		}, this.showPicker = function () {
			if (!l()) {
				var t = jscolor.getElementPos(e),
					n = jscolor.getElementSize(e),
					r = jscolor.getViewPos(),
					i = jscolor.getViewSize(),
					s = u(this),
					a, f, c;
				switch (this.pickerPosition.toLowerCase()) {
				case "left":
					a = 1, f = 0, c = -1;
					break;
				case "right":
					a = 1, f = 0, c = 1;
					break;
				case "top":
					a = 0, f = 1, c = -1;
					break;
				default:
					a = 0, f = 1, c = 1
				}
				var h = (n[f] + s[f]) / 2;
				if (!this.pickerSmartPosition) var p = [t[a], t[f] + n[f] - h + h * c];
				else var p = [-r[a] + t[a] + s[a] > i[a] ? -r[a] + t[a] + n[a] / 2 > i[a] / 2 && t[a] + n[a] - s[a] >= 0 ? t[a] + n[a] - s[a] : t[a] : t[a], -r[f] + t[f] + n[f] + s[f] - h + h * c > i[f] ? -r[f] + t[f] + n[f] / 2 > i[f] / 2 && t[f] + n[f] - h - h * c >= 0 ? t[f] + n[f] - h - h * c : t[f] + n[f] - h + h * c : t[f] + n[f] - h + h * c >= 0 ? t[f] + n[f] - h + h * c : t[f] + n[f] - h - h * c];
				o(p[a], p[f])
			}
		}, this.importColor = function () {
			b ? this.adjust ? !this.required && /^\s*$/.test(b.value) ? (w.style.backgroundImage = w.jscStyle.backgroundImage, w.style.backgroundColor = w.jscStyle.backgroundColor, w.style.color = w.jscStyle.color, this.exportColor(T | N)) : this.fromString(b.value) || this.exportColor() : this.fromString(b.value, T) || (w.style.backgroundImage = w.jscStyle.backgroundImage, w.style.backgroundColor = w.jscStyle.backgroundColor, w.style.color = w.jscStyle.color, this.exportColor(T | N)) : this.exportColor()
		}, this.exportColor = function (e) {
			if (!(e & T) && b) {
				var t = this.toString();
				this.caps && (t = t.toUpperCase()), this.hash && (t = "#" + t)
			}!(e & N) && w && (w.style.backgroundImage = "none", w.style.backgroundColor = "#" + this.toString(), w.style.color = .213 * this.rgb[0] + .715 * this.rgb[1] + .072 * this.rgb[2] < .5 ? "#FFF" : "#000"), !(e & C) && l() && a(), !(e & k) && l() && f()
		}, this.fromHSV = function (e, t, n, r) {
			e !== null && (e = Math.max(0, this.minH, Math.min(6, this.maxH, e))), t !== null && (t = Math.max(0, this.minS, Math.min(1, this.maxS, t))), n !== null && (n = Math.max(0, this.minV, Math.min(1, this.maxV, n))), this.rgb = i(e === null ? this.hsv[0] : this.hsv[0] = e, t === null ? this.hsv[1] : this.hsv[1] = t, n === null ? this.hsv[2] : this.hsv[2] = n), this.exportColor(r)
		}, this.fromRGB = function (e, t, n, s) {
			e !== null && (e = Math.max(0, Math.min(1, e))), t !== null && (t = Math.max(0, Math.min(1, t))), n !== null && (n = Math.max(0, Math.min(1, n)));
			var o = r(e === null ? this.rgb[0] : e, t === null ? this.rgb[1] : t, n === null ? this.rgb[2] : n);
			o[0] !== null && (this.hsv[0] = Math.max(0, this.minH, Math.min(6, this.maxH, o[0]))), o[2] !== 0 && (this.hsv[1] = o[1] === null ? null : Math.max(0, this.minS, Math.min(1, this.maxS, o[1]))), this.hsv[2] = o[2] === null ? null : Math.max(0, this.minV, Math.min(1, this.maxV, o[2]));
			var u = i(this.hsv[0], this.hsv[1], this.hsv[2]);
			this.rgb[0] = u[0], this.rgb[1] = u[1], this.rgb[2] = u[2], this.exportColor(s)
		}, this.fromString = function (e, t) {
			return !1;
			var n
		}, this.toString = function () {
			return (256 | Math.round(255 * this.rgb[0])).toString(16).substr(1) + (256 | Math.round(255 * this.rgb[1])).toString(16).substr(1) + (256 | Math.round(255 * this.rgb[2])).toString(16).substr(1)
		};
		var m = this,
			g = this.pickerMode.toLowerCase() === "hvs" ? 1 : 0,
			y = !1,
			b = jscolor.fetchElement(this.valueElement),
			w = jscolor.fetchElement(this.styleElement),
			E = !1,
			S = !1,
			x = {},
			T = 1,
			N = 2,
			C = 4,
			k = 8;
		jscolor.addEvent(e, "focus", function (e) {
			jscolor.target = e.target || e.srcElement, m.pickerOnfocus && m.showPicker()
		}), jscolor.addEvent(e, "blur", function () {
			y ? y = !1 : window.setTimeout(function () {
				y || c(), y = !1
			}, 0)
		});
		if (b) {
			var L = function () {
				m.fromString(b.value, T), v()
			};
			jscolor.addEvent(b, "keyup", L), jscolor.addEvent(b, "input", L), jscolor.addEvent(b, "blur", h), b.setAttribute("autocomplete", "off")
		}
		w && (w.jscStyle = {
			backgroundImage: w.style.backgroundImage,
			backgroundColor: w.style.backgroundColor,
			color: w.style.color
		}), this.importColor()
	}
};
jscolor.install(), typeof Object.create != "function" && (Object.create = function (e) {
		function t() {}
		return t.prototype = e, new t
	}),
	function (e, t, n, r) {
		var i = e(t),
			s = e(n),
			o = e("body"),
			u = "disable-toolbar",
			a = {
				init: function (t, n) {
					var r = this;
					r.$elem = e("<div></div>"), r.options = e.extend({}, e.fn.toolbar.options, t), r.toolbar = e(n).addClass("tool-" + r.options.position).addClass("tool-rounded").append('<div class="arrow" />').hide(), r.toolbar_arrow = r.toolbar.find(".arrow"), r.initializeToolbar()
				},
				initializeToolbar: function () {
					var e = this;
					e.populateContent(), e.setTrigger(), e.toolbarWidth = e.toolbar.width()
				},
				showToolbar: function (e, t) {
					var n = this;
					if (e.size() > 0) {
						var r = e.parent();
						n.$showAt = r.hasClass("design-element") ? r : e, n.$elem = e, n.show(t)
					}
				},
				setTrigger: function () {
					var r = this;
					e(n).on("dblclick", function (t) {
						if (o.data(u)) return;
						var n = e(t.target),
							i;
						i = n.hasClass("design-content") ? n : n.closest(".design-content");
						if (i.size() < 1 || !i.hasClass(r.options.content.replace(".", "")) || i.closest(".design-element.tmpl").size()) return;
						r.showToolbar(i, t)
					});
					var i = !1;
					e(n).on("mousedown", function (t) {
						if (o.data(u)) return;
						var n = e(t.target),
							s = n.closest(r.options.content);
						i = s.size() > 0 && s[0] == r.$elem[0]
					}), e(n).on("mouseup", function (t) {
						if (o.data(u)) return;
						var n = e(t.target);
						if (n.closest(r.options.stayedIn).size() > 0) return;
						var s = n.closest(r.options.content);
						!i && (s.size() < 1 || s[0] != r.$elem[0]) && r.toolbar.is(":visible") && r.hide()
					}), e(t).resize(function (e) {
						if (o.data(u)) return;
						e.stopPropagation(), r.toolbar.is(":visible") && r.calculatePosition(e)
					})
				},
				populateContent: function () {
					var e = this,
						t = e.toolbar.find(".tool-items"),
						n = t.find(">button").addClass("tool-item gradient")
				},
				calculatePosition: function (e) {
					var t = this;
					t.arrowCss = {}, t.toolbarCss = t.getCoordinates(t.options.position, 20), t.toolbarCss.position = "absolute";
					if (e) {
						var n = t.toolbarCss.top,
							r = i.scrollTop(),
							s = i.height();
						if (n < r || n > r + s || t.toolbar.data("position") == "relative") t.toolbar.removeClass("tool-bottom").addClass("tool-top"), t.toolbarCss.top = e.pageY - 84
					}
					t.collisionDetection(), t.toolbar.css(t.toolbarCss), t.toolbar_arrow.css(t.arrowCss)
				},
				getCoordinates: function (t, r) {
					var i = this;
					i.coordinates = i.$showAt.offset(), i.options.adjustment && i.options.adjustment[i.options.position] && (r = i.options.adjustment[i.options.position] + r);
					var s = i.coordinates.top - r - 30,
						o = s - e(n).scrollTop();
					return o > 0 ? (i.toolbar.removeClass("tool-bottom").addClass("tool-top"), {
						left: i.coordinates.left - i.toolbar.width() / 2 + i.$elem.outerWidth() / 2,
						top: s,
						right: "auto"
					}) : (i.toolbar.removeClass("tool-top").addClass("tool-bottom"), {
						left: i.coordinates.left - i.toolbar.width() / 2 + i.$elem.outerWidth() / 2,
						top: i.coordinates.top + i.$elem.height() + r,
						right: "auto"
					})
				},
				collisionDetection: function () {
					var n = this,
						r = 20;
					if (n.options.position == "top" || n.options.position == "bottom") n.arrowCss = {
						left: "50%",
						right: "50%"
					}, n.toolbarCss.left < r ? (n.toolbarCss.left = r, n.arrowCss.left = n.$elem.offset().left + n.$elem.width() / 2 - r) : e(t).width() - (n.toolbarCss.left + n.toolbarWidth) < r && (n.toolbarCss.right = r, n.toolbarCss.left = "auto", n.arrowCss.left = "auto", n.arrowCss.right = e(t).width() - n.$elem.offset().left - n.$elem.width() / 2 - r - 5)
				},
				show: function (e) {
					var t = this;
					t.calculatePosition(e), t.toolbar.show().trigger("toolbar.show", [e]), t.$elem.addClass("toolbar-show")
				},
				hide: function () {
					var e = this;
					e.toolbar.hide().trigger("toolbar.hide"), e.$elem.removeClass("toolbar-show")
				},
				getToolbarElement: function () {
					return this.toolbar.find(".tool-items")
				}
			};
		e.fn.toolbar = function (t) {
			if (t === "show" && arguments.length == 2) {
				var n = e(this).data("toolbarObj");
				return n.showToolbar(arguments[1]), n
			}
			return this.each(function () {
				var n = Object.create(a);
				n.init(t, this), e(this).data("toolbarObj", n)
			})
		}, e.fn.toolbar.options = {
			content: ".design-content",
			stayedIn: ".tool-container, .jscolor",
			position: "top",
			hideOnClick: !1,
			hover: !1
		}
	}(jQuery, window, document),
	function (e) {
		function t(e) {
			var n = "";
			if (e.nodeType == 3) n = e.nodeValue;
			else {
				for (var r = 0; r < e.childNodes.length; r++) n += t(e.childNodes[r]);
				var i = getComputedStyle(e).getPropertyValue("display");
				if (i.match(/^block/) || i.match(/list/) || e.tagName == "BR") n += "\n"
			}
			return n
		}
		window.getComputedStyle || (window.getComputedStyle = function (e, t) {
			return this.el = e, this.getPropertyValue = function (t) {
				var n = /(\-([a-z]){1})/g;
				return t == "float" && (t = "styleFloat"), n.test(t) && (t = t.replace(n, function () {
					return arguments[2].toUpperCase()
				})), e.currentStyle[t] ? e.currentStyle[t] : null
			}, this
		}), e.fn.textEx = function (e) {
			return t(this.get(0))
		}, e.fn.textEx.options = {}
	}(jQuery),
	function (e) {
		e.fn.toJSON = function () {
			var t = {},
				n = this.serializeArray();
			return e.each(n, function () {
				t[this.name] !== undefined ? (t[this.name].push || (t[this.name] = [t[this.name]]), t[this.name].push(this.value || "")) : t[this.name] = this.value || ""
			}), t
		}
	}(jQuery),
	function (e) {
		typeof define == "function" && define.amd ? define(["jquery"], e) : e(jQuery)
	}(function (e) {
		function m() {
			var t = this;
			t.top = "auto", t.left = "auto", t.right = "auto", t.bottom = "auto", t.set = function (n, r) {
				e.isNumeric(r) && (t[n] = Math.round(r))
			}
		}

		function g(e, t, n) {
			function i(i, u) {
				f(), e.data(s) || (i ? (u && e.data(o, !0), n.showTip(e)) : (d.tipOpenImminent = !0, r = setTimeout(function () {
					r = null, a()
				}, t.intentPollInterval)))
			}

			function u(i) {
				f(), d.tipOpenImminent = !1, e.data(s) && (e.data(o, !1), i ? n.hideTip(e) : (d.delayInProgress = !0, r = setTimeout(function () {
					r = null, n.hideTip(e), d.delayInProgress = !1
				}, t.closeDelay)))
			}

			function a() {
				var r = Math.abs(d.previousX - d.currentX),
					s = Math.abs(d.previousY - d.currentY),
					o = r + s;
				o < t.intentSensitivity ? n.showTip(e) : (d.previousX = d.currentX, d.previousY = d.currentY, i())
			}

			function f() {
				r = clearTimeout(r), d.delayInProgress = !1
			}

			function l() {
				n.resetPosition(e)
			}
			var r = null;
			this.show = i, this.hide = u, this.cancel = f, this.resetPosition = l
		}

		function y() {
			function e(e, r, i, s, o) {
				var u = r.split("-")[0],
					a = new m,
					f;
				w(e) ? f = n(e, u) : f = t(e, u);
				switch (r) {
				case "n":
					a.set("left", f.left - i / 2), a.set("bottom", d.windowHeight - f.top + o);
					break;
				case "e":
					a.set("left", f.left + o), a.set("top", f.top - s / 2);
					break;
				case "s":
					a.set("left", f.left - i / 2), a.set("top", f.top + o);
					break;
				case "w":
					a.set("top", f.top - s / 2), a.set("right", d.windowWidth - f.left + o);
					break;
				case "nw":
					a.set("bottom", d.windowHeight - f.top + o), a.set("right", d.windowWidth - f.left - 20);
					break;
				case "nw-alt":
					a.set("left", f.left), a.set("bottom", d.windowHeight - f.top + o);
					break;
				case "ne":
					a.set("left", f.left - 20), a.set("bottom", d.windowHeight - f.top + o);
					break;
				case "ne-alt":
					a.set("bottom", d.windowHeight - f.top + o), a.set("right", d.windowWidth - f.left);
					break;
				case "sw":
					a.set("top", f.top + o), a.set("right", d.windowWidth - f.left - 20);
					break;
				case "sw-alt":
					a.set("left", f.left), a.set("top", f.top + o);
					break;
				case "se":
					a.set("left", f.left - 20), a.set("top", f.top + o);
					break;
				case "se-alt":
					a.set("top", f.top + o), a.set("right", d.windowWidth - f.left)
				}
				return a
			}

			function t(e, t) {
				var n = e.offset(),
					r = e.outerWidth(),
					i = e.outerHeight(),
					s, o;
				switch (t) {
				case "n":
					s = n.left + r / 2, o = n.top;
					break;
				case "e":
					s = n.left + r, o = n.top + i / 2;
					break;
				case "s":
					s = n.left + r / 2, o = n.top + i;
					break;
				case "w":
					s = n.left, o = n.top + i / 2;
					break;
				case "nw":
					s = n.left, o = n.top;
					break;
				case "ne":
					s = n.left + r, o = n.top;
					break;
				case "sw":
					s = n.left, o = n.top + i;
					break;
				case "se":
					s = n.left + r, o = n.top + i
				}
				return {
					top: o,
					left: s
				}
			}

			function n(e, t) {
				function g() {
					f.push(i.matrixTransform(o))
				}
				var n = e.closest("svg")[0],
					r = e[0],
					i = n.createSVGPoint(),
					s = r.getBBox(),
					o = r.getScreenCTM(),
					u = s.width / 2,
					a = s.height / 2,
					f = [],
					l = ["nw", "n", "ne", "e", "se", "s", "sw", "w"],
					c, h, v, m;
				i.x = s.x, i.y = s.y, g(), i.x += u, g(), i.x += u, g(), i.y += a, g(), i.y += a, g(), i.x -= u, g(), i.x -= u, g(), i.y -= a, g();
				if (f[0].y !== f[1].y || f[0].x !== f[7].x) {
					h = Math.atan2(o.b, o.a) * p, v = Math.ceil((h % 360 - 22.5) / 45), v < 1 && (v += 8);
					while (v--) l.push(l.shift())
				}
				for (m = 0; m < f.length; m++)
					if (l[m] === t) {
						c = f[m];
						break
					}
				return {
					top: c.y + d.scrollTop,
					left: c.x + d.scrollLeft
				}
			}
			this.compute = e
		}

		function b(f) {
			function h(e) {
				e.data(s, !0), c.queue(function (n) {
					p(e), n()
				})
			}

			function p(e) {
				var t;
				if (!e.data(s)) return;
				if (d.isTipOpen) {
					d.isClosing || g(d.activeHover), c.delay(100).queue(function (n) {
						p(e), n()
					});
					return
				}
				e.trigger("powerTipPreRender"), t = T(e);
				if (!t) return;
				c.empty().append(t), e.trigger("powerTipRender"), d.activeHover = e, d.isTipOpen = !0, c.data(a, f.mouseOnToPopup), f.followMouse ? b() : (w(e), d.isFixedTipOpen = !0), c.fadeIn(f.fadeInTime, function () {
					d.desyncTimeout || (d.desyncTimeout = setInterval(S, 500)), e.trigger("powerTipOpen")
				})
			}

			function g(e) {
				d.isClosing = !0, d.activeHover = null, d.isTipOpen = !1, d.desyncTimeout = clearInterval(d.desyncTimeout), e.data(s, !1), e.data(o, !1), c.fadeOut(f.fadeOutTime, function () {
					var n = new m;
					d.isClosing = !1, d.isFixedTipOpen = !1, c.removeClass(), n.set("top", d.currentY + f.offset), n.set("left", d.currentX + f.offset), c.css(n), e.trigger("powerTipClose")
				})
			}

			function b() {
				if (!d.isFixedTipOpen && (d.isTipOpen || d.tipOpenImminent && c.data(u))) {
					var e = c.outerWidth(),
						t = c.outerHeight(),
						n = new m,
						r, i;
					n.set("top", d.currentY + f.offset), n.set("left", d.currentX + f.offset), r = N(n, e, t), r !== v.none && (i = C(r), i === 1 ? r === v.right ? n.set("left", d.windowWidth - e) : r === v.bottom && n.set("top", d.scrollTop + d.windowHeight - t) : (n.set("left", d.currentX - e - f.offset), n.set("top", d.currentY - t - f.offset))), c.css(n)
				}
			}

			function w(t) {
				var n, r;
				f.smartPlacement ? (n = e.fn.powerTip.smartPlacementLists[f.placement], e.each(n, function (e, n) {
					var i = N(E(t, n), c.outerWidth(), c.outerHeight());
					r = n;
					if (i === v.none) return !1
				})) : (E(t, f.placement), r = f.placement), c.addClass(r)
			}

			function E(e, t) {
				var n = 0,
					r, i, s = new m;
				s.set("top", 0), s.set("left", 0), c.css(s);
				do r = c.outerWidth(), i = c.outerHeight(), s = l.compute(e, t, r, i, f.offset), c.css(s); while (++n <= 5 && (r !== c.outerWidth() || i !== c.outerHeight()));
				return s
			}

			function S() {
				var e = !1;
				d.isTipOpen && !d.isClosing && !d.delayInProgress && (d.activeHover.data(s) === !1 || d.activeHover.is(":disabled") ? e = !0 : !x(d.activeHover) && !d.activeHover.is(":focus") && !d.activeHover.data(o) && (c.data(a) ? x(c) || (e = !0) : e = !0), e && g(d.activeHover))
			}
			var l = new y,
				c = e("#" + f.popupId);
			c.length === 0 && (c = e("<div/>", {
				id: f.popupId
			}), r.length === 0 && (r = e("body")), r.append(c)), f.followMouse && (c.data(u) || (t.on("mousemove", b), n.on("scroll", b), c.data(u, !0))), f.mouseOnToPopup && c.on({
				mouseenter: function () {
					c.data(a) && d.activeHover && d.activeHover.data(i).cancel()
				},
				mouseleave: function () {
					d.activeHover && d.activeHover.data(i).hide()
				}
			}), this.showTip = h, this.hideTip = g, this.resetPosition = w
		}

		function w(e) {
			return window.SVGElement && e[0] instanceof SVGElement
		}

		function E() {
			d.mouseTrackingActive || (d.mouseTrackingActive = !0, e(function () {
				d.scrollLeft = n.scrollLeft(), d.scrollTop = n.scrollTop(), d.windowWidth = n.width(), d.windowHeight = n.height()
			}), t.on("mousemove", S), n.on({
				resize: function () {
					d.windowWidth = n.width(), d.windowHeight = n.height()
				},
				scroll: function () {
					var t = n.scrollLeft(),
						r = n.scrollTop();
					t !== d.scrollLeft && (d.currentX += t - d.scrollLeft, d.scrollLeft = t), r !== d.scrollTop && (d.currentY += r - d.scrollTop, d.scrollTop = r)
				}
			}))
		}

		function S(e) {
			d.currentX = e.pageX, d.currentY = e.pageY
		}

		function x(e) {
			var t = e.offset(),
				n = e[0].getBoundingClientRect(),
				r = n.right - n.left,
				i = n.bottom - n.top;
			return d.currentX >= t.left && d.currentX <= t.left + r && d.currentY >= t.top && d.currentY <= t.top + i
		}

		function T(t) {
			var n = t.data(l),
				r = t.data(c),
				i = t.data(h),
				s, o;
			return n ? (e.isFunction(n) && (n = n.call(t[0])), o = n) : r ? (e.isFunction(r) && (r = r.call(t[0])), r.length > 0 && (o = r.clone(!0, !0))) : i && (s = e("#" + i), s.length > 0 && (o = s.html())), o
		}

		function N(e, t, n) {
			var r = d.scrollTop,
				i = d.scrollLeft,
				s = r + d.windowHeight,
				o = i + d.windowWidth,
				u = v.none;
			if (e.top < r || Math.abs(e.bottom - d.windowHeight) - n < r) u |= v.top;
			if (e.top + n > s || Math.abs(e.bottom - d.windowHeight) > s) u |= v.bottom;
			if (e.left < i || e.right + t > o) u |= v.left;
			if (e.left + t > o || e.right < i) u |= v.right;
			return u
		}

		function C(e) {
			var t = 0;
			while (e) e &= e - 1, t++;
			return t
		}
		var t = e(document),
			n = e(window),
			r = e("body"),
			i = "displayController",
			s = "hasActiveHover",
			o = "forcedOpen",
			u = "hasMouseMove",
			a = "mouseOnToPopup",
			f = "originalTitle",
			l = "powertip",
			c = "powertipjq",
			h = "powertiptarget",
			p = 180 / Math.PI,
			d = {
				isTipOpen: !1,
				isFixedTipOpen: !1,
				isClosing: !1,
				tipOpenImminent: !1,
				activeHover: null,
				currentX: 0,
				currentY: 0,
				previousX: 0,
				previousY: 0,
				desyncTimeout: null,
				mouseTrackingActive: !1,
				delayInProgress: !1,
				windowWidth: 0,
				windowHeight: 0,
				scrollTop: 0,
				scrollLeft: 0
			},
			v = {
				none: 0,
				top: 1,
				bottom: 2,
				left: 4,
				right: 8
			};
		e.fn.powerTip = function (t, n) {
			if (!this.length) return this;
			if (e.type(t) === "string" && e.powerTip[t]) return e.powerTip[t].call(this, this, n);
			var r = e.extend({}, e.fn.powerTip.defaults, t),
				s = new b(r);
			return E(), this.each(function () {
				var n = e(this),
					o = n.data(l),
					u = n.data(c),
					a = n.data(h),
					p;
				n.data(i) && e.powerTip.destroy(n), p = n.attr("title"), !o && !a && !u && p && (n.data(l, p), n.data(f, p), n.removeAttr("title")), n.data(i, new g(n, r, s))
			}), r.manual || this.on({
				"mouseenter.powertip": function (n) {
					e.powerTip.show(this, n)
				},
				"mouseleave.powertip": function () {
					e.powerTip.hide(this)
				},
				"focus.powertip": function () {
					e.powerTip.show(this)
				},
				"blur.powertip": function () {
					e.powerTip.hide(this, !0)
				},
				"keydown.powertip": function (n) {
					n.keyCode === 27 && e.powerTip.hide(this, !0)
				}
			}), this
		}, e.fn.powerTip.defaults = {
			fadeInTime: 200,
			fadeOutTime: 100,
			followMouse: !1,
			popupId: "powerTip",
			intentSensitivity: 7,
			intentPollInterval: 100,
			closeDelay: 100,
			placement: "n",
			smartPlacement: !1,
			offset: 10,
			mouseOnToPopup: !1,
			manual: !1
		}, e.fn.powerTip.smartPlacementLists = {
			n: ["n", "ne", "nw", "s"],
			e: ["e", "ne", "se", "w", "nw", "sw", "n", "s", "e"],
			s: ["s", "se", "sw", "n"],
			w: ["w", "nw", "sw", "e", "ne", "se", "n", "s", "w"],
			nw: ["nw", "w", "sw", "n", "s", "se", "nw"],
			ne: ["ne", "e", "se", "n", "s", "sw", "ne"],
			sw: ["sw", "w", "nw", "s", "n", "ne", "sw"],
			se: ["se", "e", "ne", "s", "n", "nw", "se"],
			"nw-alt": ["nw-alt", "n", "ne-alt", "sw-alt", "s", "se-alt", "w", "e"],
			"ne-alt": ["ne-alt", "n", "nw-alt", "se-alt", "s", "sw-alt", "e", "w"],
			"sw-alt": ["sw-alt", "s", "se-alt", "nw-alt", "n", "ne-alt", "w", "e"],
			"se-alt": ["se-alt", "s", "sw-alt", "ne-alt", "n", "nw-alt", "e", "w"]
		}, e.powerTip = {
			show: function (n, r) {
				return r ? (S(r), d.previousX = r.pageX, d.previousY = r.pageY, e(n).data(i).show()) : e(n).first().data(i).show(!0, !0), n
			},
			reposition: function (n) {
				return e(n).first().data(i).resetPosition(), n
			},
			hide: function (n, r) {
				return n ? e(n).first().data(i).hide(r) : d.activeHover && d.activeHover.data(i).hide(!0), n
			},
			destroy: function (n) {
				return e(n).off(".powertip").each(function () {
					var n = e(this),
						r = [f, i, s, o];
					n.data(f) && (n.attr("title", n.data(f)), r.push(l)), n.removeData(r)
				}), n
			}
		}, e.powerTip.showTip = e.powerTip.show, e.powerTip.closeTip = e.powerTip.hide
	}),
	function (e) {
		typeof define == "function" && define.amd ? define(["jquery"], e) : e(jQuery)
	}(function (e) {
		e.extend(e.fn, {
			validate: function (t) {
				if (!this.length) {
					t && t.debug && window.console && console.warn("Nothing selected, can't validate, returning nothing.");
					return
				}
				var n = e.data(this[0], "validator");
				return n ? n : (this.attr("novalidate", "novalidate"), n = new e.validator(t, this[0]), e.data(this[0], "validator", n), n.settings.onsubmit && (this.validateDelegate(":submit", "click", function (t) {
					n.settings.submitHandler && (n.submitButton = t.target), e(t.target).hasClass("cancel") && (n.cancelSubmit = !0), e(t.target).attr("formnovalidate") !== undefined && (n.cancelSubmit = !0)
				}), this.submit(function (t) {
					function r() {
						var r, i;
						return n.settings.submitHandler ? (n.submitButton && (r = e("<input type='hidden'/>").attr("name", n.submitButton.name).val(e(n.submitButton).val()).appendTo(n.currentForm)), i = n.settings.submitHandler.call(n, n.currentForm, t), n.submitButton && r.remove(), i !== undefined ? i : !1) : !0
					}
					return n.settings.debug && t.preventDefault(), n.cancelSubmit ? (n.cancelSubmit = !1, r()) : n.form() ? n.pendingRequest ? (n.formSubmitted = !0, !1) : r() : (n.focusInvalid(), !1)
				})), n)
			},
			valid: function () {
				var t, n;
				return e(this[0]).is("form") ? t = this.validate().form() : (t = !0, n = e(this[0].form).validate(), this.each(function () {
					t = n.element(this) && t
				})), t
			},
			removeAttrs: function (t) {
				var n = {},
					r = this;
				return e.each(t.split(/\s/), function (e, t) {
					n[t] = r.attr(t), r.removeAttr(t)
				}), n
			},
			rules: function (t, n) {
				var r = this[0],
					i, s, o, u, a, f;
				if (t) {
					i = e.data(r.form, "validator").settings, s = i.rules, o = e.validator.staticRules(r);
					switch (t) {
					case "add":
						e.extend(o, e.validator.normalizeRule(n)), delete o.messages, s[r.name] = o, n.messages && (i.messages[r.name] = e.extend(i.messages[r.name], n.messages));
						break;
					case "remove":
						if (!n) return delete s[r.name], o;
						return f = {}, e.each(n.split(/\s/), function (t, n) {
							f[n] = o[n], delete o[n], n === "required" && e(r).removeAttr("aria-required")
						}), f
					}
				}
				return u = e.validator.normalizeRules(e.extend({}, e.validator.classRules(r), e.validator.attributeRules(r), e.validator.dataRules(r), e.validator.staticRules(r)), r), u.required && (a = u.required, delete u.required, u = e.extend({
					required: a
				}, u), e(r).attr("aria-required", "true")), u.remote && (a = u.remote, delete u.remote, u = e.extend(u, {
					remote: a
				})), u
			}
		}), e.extend(e.expr[":"], {
			blank: function (t) {
				return !e.trim("" + e(t).val())
			},
			filled: function (t) {
				return !!e.trim("" + e(t).val())
			},
			unchecked: function (t) {
				return !e(t).prop("checked")
			}
		}), e.validator = function (t, n) {
			this.settings = e.extend(!0, {}, e.validator.defaults, t), this.currentForm = n, this.init()
		}, e.validator.format = function (t, n) {
			return arguments.length === 1 ? function () {
				var n = e.makeArray(arguments);
				return n.unshift(t), e.validator.format.apply(this, n)
			} : (arguments.length > 2 && n.constructor !== Array && (n = e.makeArray(arguments).slice(1)), n.constructor !== Array && (n = [n]), e.each(n, function (e, n) {
				t = t.replace(new RegExp("\\{" + e + "\\}", "g"), function () {
					return n
				})
			}), t)
		}, e.extend(e.validator, {
			defaults: {
				messages: {},
				groups: {},
				rules: {},
				errorClass: "error",
				validClass: "valid",
				errorElement: "label",
				focusCleanup: !1,
				focusInvalid: !0,
				errorContainer: e([]),
				errorLabelContainer: e([]),
				onsubmit: !0,
				ignore: ":hidden",
				ignoreTitle: !1,
				onfocusin: function (e) {
					this.lastActive = e, this.settings.focusCleanup && (this.settings.unhighlight && this.settings.unhighlight.call(this, e, this.settings.errorClass, this.settings.validClass), this.hideThese(this.errorsFor(e)))
				},
				onfocusout: function (e) {
					!this.checkable(e) && (e.name in this.submitted || !this.optional(e)) && this.element(e)
				},
				onkeyup: function (e, t) {
					if (t.which === 9 && this.elementValue(e) === "") return;
					(e.name in this.submitted || e === this.lastElement) && this.element(e)
				},
				onclick: function (e) {
					e.name in this.submitted ? this.element(e) : e.parentNode.name in this.submitted && this.element(e.parentNode)
				},
				highlight: function (t, n, r) {
					t.type === "radio" ? this.findByName(t.name).addClass(n).removeClass(r) : e(t).addClass(n).removeClass(r)
				},
				unhighlight: function (t, n, r) {
					t.type === "radio" ? this.findByName(t.name).removeClass(n).addClass(r) : e(t).removeClass(n).addClass(r)
				}
			},
			setDefaults: function (t) {
				e.extend(e.validator.defaults, t)
			},
			messages: {
				required: "This field is required.",
				remote: "Please fix this field.",
				email: "Please enter a valid email address.",
				url: "Please enter a valid URL.",
				date: "Please enter a valid date.",
				dateISO: "Please enter a valid date ( ISO ).",
				number: "Please enter a valid number.",
				digits: "Please enter only digits.",
				creditcard: "Please enter a valid credit card number.",
				equalTo: "Please enter the same value again.",
				maxlength: e.validator.format("Please enter no more than {0} characters."),
				minlength: e.validator.format("Please enter at least {0} characters."),
				rangelength: e.validator.format("Please enter a value between {0} and {1} characters long."),
				range: e.validator.format("Please enter a value between {0} and {1}."),
				max: e.validator.format("Please enter a value less than or equal to {0}."),
				min: e.validator.format("Please enter a value greater than or equal to {0}.")
			},
			autoCreateRanges: !1,
			prototype: {
				init: function () {
					function r(t) {
						var n = e.data(this[0].form, "validator"),
							r = "on" + t.type.replace(/^validate/, ""),
							i = n.settings;
						i[r] && !this.is(i.ignore) && i[r].call(n, this[0], t)
					}
					this.labelContainer = e(this.settings.errorLabelContainer), this.errorContext = this.labelContainer.length && this.labelContainer || e(this.currentForm), this.containers = e(this.settings.errorContainer).add(this.settings.errorLabelContainer), this.submitted = {}, this.valueCache = {}, this.pendingRequest = 0, this.pending = {}, this.invalid = {}, this.reset();
					var t = this.groups = {},
						n;
					e.each(this.settings.groups, function (n, r) {
						typeof r == "string" && (r = r.split(/\s/)), e.each(r, function (e, r) {
							t[r] = n
						})
					}), n = this.settings.rules, e.each(n, function (t, r) {
						n[t] = e.validator.normalizeRule(r)
					}), e(this.currentForm).validateDelegate(":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'] ,[type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], [type='radio'], [type='checkbox']", "focusin focusout keyup", r).validateDelegate("select, option, [type='radio'], [type='checkbox']", "click", r), this.settings.invalidHandler && e(this.currentForm).bind("invalid-form.validate", this.settings.invalidHandler), e(this.currentForm).find("[required], [data-rule-required], .required").attr("aria-required", "true")
				},
				form: function () {
					return this.checkForm(), e.extend(this.submitted, this.errorMap), this.invalid = e.extend({}, this.errorMap), this.valid() || e(this.currentForm).triggerHandler("invalid-form", [this]), this.showErrors(), this.valid()
				},
				checkForm: function () {
					this.prepareForm();
					for (var e = 0, t = this.currentElements = this.elements(); t[e]; e++) this.check(t[e]);
					return this.valid()
				},
				element: function (t) {
					var n = this.clean(t),
						r = this.validationTargetFor(n),
						i = !0;
					return this.lastElement = r, r === undefined ? delete this.invalid[n.name] : (this.prepareElement(r), this.currentElements = e(r), i = this.check(r) !== !1, i ? delete this.invalid[r.name] : this.invalid[r.name] = !0), e(t).attr("aria-invalid", !i), this.numberOfInvalids() || (this.toHide = this.toHide.add(this.containers)), this.showErrors(), i
				},
				showErrors: function (t) {
					if (t) {
						e.extend(this.errorMap, t), this.errorList = [];
						for (var n in t) this.errorList.push({
							message: t[n],
							element: this.findByName(n)[0]
						});
						this.successList = e.grep(this.successList, function (e) {
							return !(e.name in t)
						})
					}
					this.settings.showErrors ? this.settings.showErrors.call(this, this.errorMap, this.errorList) : this.defaultShowErrors()
				},
				resetForm: function () {
					e.fn.resetForm && e(this.currentForm).resetForm(), this.submitted = {}, this.lastElement = null, this.prepareForm(), this.hideErrors(), this.elements().removeClass(this.settings.errorClass).removeData("previousValue").removeAttr("aria-invalid")
				},
				numberOfInvalids: function () {
					return this.objectLength(this.invalid)
				},
				objectLength: function (e) {
					var t = 0,
						n;
					for (n in e) t++;
					return t
				},
				hideErrors: function () {
					this.hideThese(this.toHide)
				},
				hideThese: function (e) {
					e.not(this.containers).text(""), this.addWrapper(e).hide()
				},
				valid: function () {
					return this.size() === 0
				},
				size: function () {
					return this.errorList.length
				},
				focusInvalid: function () {
					if (this.settings.focusInvalid) try {
						e(this.findLastActive() || this.errorList.length && this.errorList[0].element || []).filter(":visible").focus().trigger("focusin")
					} catch (t) {}
				},
				findLastActive: function () {
					var t = this.lastActive;
					return t && e.grep(this.errorList, function (e) {
						return e.element.name === t.name
					}).length === 1 && t
				},
				elements: function () {
					var t = this,
						n = {};
					return e(this.currentForm).find("input, select, textarea").not(":submit, :reset, :image, [disabled], [readonly]").not(this.settings.ignore).filter(function () {
						return !this.name && t.settings.debug && window.console && console.error("%o has no name assigned", this), this.name in n || !t.objectLength(e(this).rules()) ? !1 : (n[this.name] = !0, !0)
					})
				},
				clean: function (t) {
					return e(t)[0]
				},
				errors: function () {
					var t = this.settings.errorClass.split(" ").join(".");
					return e(this.settings.errorElement + "." + t, this.errorContext)
				},
				reset: function () {
					this.successList = [], this.errorList = [], this.errorMap = {}, this.toShow = e([]), this.toHide = e([]), this.currentElements = e([])
				},
				prepareForm: function () {
					this.reset(), this.toHide = this.errors().add(this.containers)
				},
				prepareElement: function (e) {
					this.reset(), this.toHide = this.errorsFor(e)
				},
				elementValue: function (t) {
					var n, r = e(t),
						i = t.type;
					return i === "radio" || i === "checkbox" ? e("input[name='" + t.name + "']:checked").val() : i === "number" && typeof t.validity != "undefined" ? t.validity.badInput ? !1 : r.val() : (n = r.val(), typeof n == "string" ? n.replace(/\r/g, "") : n)
				},
				check: function (t) {
					t = this.validationTargetFor(this.clean(t));
					var n = e(t).rules(),
						r = e.map(n, function (e, t) {
							return t
						}).length,
						i = !1,
						s = this.elementValue(t),
						o, u, a;
					for (u in n) {
						a = {
							method: u,
							parameters: n[u]
						};
						try {
							o = e.validator.methods[u].call(this, s, t, a.parameters);
							if (o === "dependency-mismatch" && r === 1) {
								i = !0;
								continue
							}
							i = !1;
							if (o === "pending") {
								this.toHide = this.toHide.not(this.errorsFor(t));
								return
							}
							if (!o) return this.formatAndAdd(t, a), !1
						} catch (f) {
							throw this.settings.debug && window.console && console.log("Exception occurred when checking element " + t.id + ", check the '" + a.method + "' method.", f), f
						}
					}
					if (i) return;
					return this.objectLength(n) && this.successList.push(t), !0
				},
				customDataMessage: function (t, n) {
					return e(t).data("msg" + n.charAt(0).toUpperCase() + n.substring(1).toLowerCase()) || e(t).data("msg")
				},
				customMessage: function (e, t) {
					var n = this.settings.messages[e];
					return n && (n.constructor === String ? n : n[t])
				},
				findDefined: function () {
					for (var e = 0; e < arguments.length; e++)
						if (arguments[e] !== undefined) return arguments[e];
					return undefined
				},
				defaultMessage: function (t, n) {
					return this.findDefined(this.customMessage(t.name, n), this.customDataMessage(t, n), !this.settings.ignoreTitle && t.title || undefined, e.validator.messages[n], "<strong>Warning: No message defined for " + t.name + "</strong>")
				},
				formatAndAdd: function (t, n) {
					var r = this.defaultMessage(t, n.method),
						i = /\$?\{(\d+)\}/g;
					typeof r == "function" ? r = r.call(this, n.parameters, t) : i.test(r) && (r = e.validator.format(r.replace(i, "{$1}"), n.parameters)), this.errorList.push({
						message: r,
						element: t,
						method: n.method
					}), this.errorMap[t.name] = r, this.submitted[t.name] = r
				},
				addWrapper: function (e) {
					return this.settings.wrapper && (e = e.add(e.parent(this.settings.wrapper))), e
				},
				defaultShowErrors: function () {
					var e, t, n;
					for (e = 0; this.errorList[e]; e++) n = this.errorList[e], this.settings.highlight && this.settings.highlight.call(this, n.element, this.settings.errorClass, this.settings.validClass), this.showLabel(n.element, n.message);
					this.errorList.length && (this.toShow = this.toShow.add(this.containers));
					if (this.settings.success)
						for (e = 0; this.successList[e]; e++) this.showLabel(this.successList[e]);
					if (this.settings.unhighlight)
						for (e = 0, t = this.validElements(); t[e]; e++) this.settings.unhighlight.call(this, t[e], this.settings.errorClass, this.settings.validClass);
					this.toHide = this.toHide.not(this.toShow), this.hideErrors(), this.addWrapper(this.toShow).show()
				},
				validElements: function () {
					return this.currentElements.not(this.invalidElements())
				},
				invalidElements: function () {
					return e(this.errorList).map(function () {
						return this.element
					})
				},
				showLabel: function (t, n) {
					var r, i, s, o = this.errorsFor(t),
						u = this.idOrName(t),
						a = e(t).attr("aria-describedby");
					o.length ? (o.removeClass(this.settings.validClass).addClass(this.settings.errorClass), o.html(n)) : (o = e("<" + this.settings.errorElement + ">").attr("id", u + "-error").addClass(this.settings.errorClass).html(n || ""), r = o, this.settings.wrapper && (r = o.hide().show().wrap("<" + this.settings.wrapper + "/>").parent()), this.labelContainer.length ? this.labelContainer.append(r) : this.settings.errorPlacement ? this.settings.errorPlacement(r, e(t)) : r.insertAfter(t), o.is("label") ? o.attr("for", u) : o.parents("label[for='" + u + "']").length === 0 && (s = o.attr("id").replace(/(:|\.|\[|\])/g, "\\$1"), a ? a.match(new RegExp("\\b" + s + "\\b")) || (a += " " + s) : a = s, e(t).attr("aria-describedby", a), i = this.groups[t.name], i && e.each(this.groups, function (t, n) {
						n === i && e("[name='" + t + "']", this.currentForm).attr("aria-describedby", o.attr("id"))
					}))), !n && this.settings.success && (o.text(""), typeof this.settings.success == "string" ? o.addClass(this.settings.success) : this.settings.success(o, t)), this.toShow = this.toShow.add(o)
				},
				errorsFor: function (t) {
					var n = this.idOrName(t),
						r = e(t).attr("aria-describedby"),
						i = "label[for='" + n + "'], label[for='" + n + "'] *";
					return r && (i = i + ", #" + r.replace(/\s+/g, ", #")), this.errors().filter(i)
				},
				idOrName: function (e) {
					return this.groups[e.name] || (this.checkable(e) ? e.name : e.id || e.name)
				},
				validationTargetFor: function (t) {
					return this.checkable(t) && (t = this.findByName(t.name)), e(t).not(this.settings.ignore)[0]
				},
				checkable: function (e) {
					return /radio|checkbox/i.test(e.type)
				},
				findByName: function (t) {
					return e(this.currentForm).find("[name='" + t + "']")
				},
				getLength: function (t, n) {
					switch (n.nodeName.toLowerCase()) {
					case "select":
						return e("option:selected", n).length;
					case "input":
						if (this.checkable(n)) return this.findByName(n.name).filter(":checked").length
					}
					return t.length
				},
				depend: function (e, t) {
					return this.dependTypes[typeof e] ? this.dependTypes[typeof e](e, t) : !0
				},
				dependTypes: {
					"boolean": function (e) {
						return e
					},
					string: function (t, n) {
						return !!e(t, n.form).length
					},
					"function": function (e, t) {
						return e(t)
					}
				},
				optional: function (t) {
					var n = this.elementValue(t);
					return !e.validator.methods.required.call(this, n, t) && "dependency-mismatch"
				},
				startRequest: function (e) {
					this.pending[e.name] || (this.pendingRequest++, this.pending[e.name] = !0)
				},
				stopRequest: function (t, n) {
					this.pendingRequest--, this.pendingRequest < 0 && (this.pendingRequest = 0), delete this.pending[t.name], n && this.pendingRequest === 0 && this.formSubmitted && this.form() ? (e(this.currentForm).submit(), this.formSubmitted = !1) : !n && this.pendingRequest === 0 && this.formSubmitted && (e(this.currentForm).triggerHandler("invalid-form", [this]), this.formSubmitted = !1)
				},
				previousValue: function (t) {
					return e.data(t, "previousValue") || e.data(t, "previousValue", {
						old: null,
						valid: !0,
						message: this.defaultMessage(t, "remote")
					})
				}
			},
			classRuleSettings: {
				required: {
					required: !0
				},
				email: {
					email: !0
				},
				url: {
					url: !0
				},
				date: {
					date: !0
				},
				dateISO: {
					dateISO: !0
				},
				number: {
					number: !0
				},
				digits: {
					digits: !0
				},
				creditcard: {
					creditcard: !0
				}
			},
			addClassRules: function (t, n) {
				t.constructor === String ? this.classRuleSettings[t] = n : e.extend(this.classRuleSettings, t)
			},
			classRules: function (t) {
				var n = {},
					r = e(t).attr("class");
				return r && e.each(r.split(" "), function () {
					this in e.validator.classRuleSettings && e.extend(n, e.validator.classRuleSettings[this])
				}), n
			},
			attributeRules: function (t) {
				var n = {},
					r = e(t),
					i = t.getAttribute("type"),
					s, o;
				for (s in e.validator.methods) s === "required" ? (o = t.getAttribute(s), o === "" && (o = !0), o = !!o) : o = r.attr(s), /min|max/.test(s) && (i === null || /number|range|text/.test(i)) && (o = Number(o)), o || o === 0 ? n[s] = o : i === s && i !== "range" && (n[s] = !0);
				return n.maxlength && /-1|2147483647|524288/.test(n.maxlength) && delete n.maxlength, n
			},
			dataRules: function (t) {
				var n, r, i = {},
					s = e(t);
				for (n in e.validator.methods) r = s.data("rule" + n.charAt(0).toUpperCase() + n.substring(1).toLowerCase()), r !== undefined && (i[n] = r);
				return i
			},
			staticRules: function (t) {
				var n = {},
					r = e.data(t.form, "validator");
				return r.settings.rules && (n = e.validator.normalizeRule(r.settings.rules[t.name]) || {}), n
			},
			normalizeRules: function (t, n) {
				return e.each(t, function (r, i) {
					if (i === !1) {
						delete t[r];
						return
					}
					if (i.param || i.depends) {
						var s = !0;
						switch (typeof i.depends) {
						case "string":
							s = !!e(i.depends, n.form).length;
							break;
						case "function":
							s = i.depends.call(n, n)
						}
						s ? t[r] = i.param !== undefined ? i.param : !0 : delete t[r]
					}
				}), e.each(t, function (r, i) {
					t[r] = e.isFunction(i) ? i(n) : i
				}), e.each(["minlength", "maxlength"], function () {
					t[this] && (t[this] = Number(t[this]))
				}), e.each(["rangelength", "range"], function () {
					var n;
					t[this] && (e.isArray(t[this]) ? t[this] = [Number(t[this][0]), Number(t[this][1])] : typeof t[this] == "string" && (n = t[this].replace(/[\[\]]/g, "").split(/[\s,]+/), t[this] = [Number(n[0]), Number(n[1])]))
				}), e.validator.autoCreateRanges && (t.min != null && t.max != null && (t.range = [t.min, t.max], delete t.min, delete t.max), t.minlength != null && t.maxlength != null && (t.rangelength = [t.minlength, t.maxlength], delete t.minlength, delete t.maxlength)), t
			},
			normalizeRule: function (t) {
				if (typeof t == "string") {
					var n = {};
					e.each(t.split(/\s/), function () {
						n[this] = !0
					}), t = n
				}
				return t
			},
			addMethod: function (t, n, r) {
				e.validator.methods[t] = n, e.validator.messages[t] = r !== undefined ? r : e.validator.messages[t], n.length < 3 && e.validator.addClassRules(t, e.validator.normalizeRule(t))
			},
			methods: {
				required: function (t, n, r) {
					if (!this.depend(r, n)) return "dependency-mismatch";
					if (n.nodeName.toLowerCase() === "select") {
						var i = e(n).val();
						return i && i.length > 0
					}
					return this.checkable(n) ? this.getLength(t, n) > 0 : e.trim(t).length > 0
				},
				email: function (e, t) {
					return this.optional(t) || /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(e)
				},
				url: function (e, t) {
					return this.optional(t) || /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(e)
				},
				date: function (e, t) {
					return this.optional(t) || !/Invalid|NaN/.test((new Date(e)).toString())
				},
				dateISO: function (e, t) {
					return this.optional(t) || /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test(e)
				},
				number: function (e, t) {
					return this.optional(t) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(e)
				},
				digits: function (e, t) {
					return this.optional(t) || /^\d+$/.test(e)
				},
				creditcard: function (e, t) {
					if (this.optional(t)) return "dependency-mismatch";
					if (/[^0-9 \-]+/.test(e)) return !1;
					var n = 0,
						r = 0,
						i = !1,
						s, o;
					e = e.replace(/\D/g, "");
					if (e.length < 13 || e.length > 19) return !1;
					for (s = e.length - 1; s >= 0; s--) o = e.charAt(s), r = parseInt(o, 10), i && (r *= 2) > 9 && (r -= 9), n += r, i = !i;
					return n % 10 === 0
				},
				minlength: function (t, n, r) {
					var i = e.isArray(t) ? t.length : this.getLength(t, n);
					return this.optional(n) || i >= r
				},
				maxlength: function (t, n, r) {
					var i = e.isArray(t) ? t.length : this.getLength(t, n);
					return this.optional(n) || i <= r
				},
				rangelength: function (t, n, r) {
					var i = e.isArray(t) ? t.length : this.getLength(t, n);
					return this.optional(n) || i >= r[0] && i <= r[1]
				},
				min: function (e, t, n) {
					return this.optional(t) || e >= n
				},
				max: function (e, t, n) {
					return this.optional(t) || e <= n
				},
				range: function (e, t, n) {
					return this.optional(t) || e >= n[0] && e <= n[1]
				},
				equalTo: function (t, n, r) {
					var i = e(r);
					return this.settings.onfocusout && i.unbind(".validate-equalTo").bind("blur.validate-equalTo", function () {
						e(n).valid()
					}), t === i.val()
				},
				remote: function (t, n, r) {
					if (this.optional(n)) return "dependency-mismatch";
					var i = this.previousValue(n),
						s, o;
					return this.settings.messages[n.name] || (this.settings.messages[n.name] = {}), i.originalMessage = this.settings.messages[n.name].remote, this.settings.messages[n.name].remote = i.message, r = typeof r == "string" && {
						url: r
					} || r, i.old === t ? i.valid : (i.old = t, s = this, this.startRequest(n), o = {}, o[n.name] = t, e.ajax(e.extend(!0, {
						url: r,
						mode: "abort",
						port: "validate" + n.name,
						dataType: "json",
						data: o,
						context: s.currentForm,
						success: function (r) {
							var o = r === !0 || r === "true",
								u, a, f;
							s.settings.messages[n.name].remote = i.originalMessage, o ? (f = s.formSubmitted, s.prepareElement(n), s.formSubmitted = f, s.successList.push(n), delete s.invalid[n.name], s.showErrors()) : (u = {}, a = r || s.defaultMessage(n, "remote"), u[n.name] = i.message = e.isFunction(a) ? a(t) : a, s.invalid[n.name] = !0, s.showErrors(u)), i.valid = o, s.stopRequest(n, o)
						}
					}, r)), "pending")
				}
			}
		}), e.format = function () {
			throw "$.format has been deprecated. Please use $.validator.format instead."
		};
		var t = {},
			n;
		e.ajaxPrefilter ? e.ajaxPrefilter(function (e, n, r) {
			var i = e.port;
			e.mode === "abort" && (t[i] && t[i].abort(), t[i] = r)
		}) : (n = e.ajax, e.ajax = function (r) {
			var i = ("mode" in r ? r : e.ajaxSettings).mode,
				s = ("port" in r ? r : e.ajaxSettings).port;
			return i === "abort" ? (t[s] && t[s].abort(), t[s] = n.apply(this, arguments), t[s]) : n.apply(this, arguments)
		}), e.extend(e.fn, {
			validateDelegate: function (t, n, r) {
				return this.bind(n, function (n) {
					var i = e(n.target);
					if (i.is(t)) return r.apply(i, arguments)
				})
			}
		})
	}),
	function (e, t) {
		function N(e) {
			return typeof e == "string"
		}

		function C(e) {
			var t = r.call(arguments, 1);
			return function () {
				return e.apply(this, t.concat(r.call(arguments)))
			}
		}

		function k(e) {
			return e.replace(/^[^#]*#?(.*)$/, "$1")
		}

		function L(e) {
			return e.replace(/(?:^[^?#]*\?([^#]*).*$)?.*/, "$1")
		}

		function A(r, o, a, f, l) {
			var c, h, p, d, g;
			return f !== n ? (p = a.match(r ? /^([^#]*)\#?(.*)$/ : /^([^#?]*)\??([^#]*)(#?.*)/), g = p[3] || "", l === 2 && N(f) ? h = f.replace(r ? S : E, "") : (d = u(p[2]), f = N(f) ? u[r ? m : v](f) : f, h = l === 2 ? f : l === 1 ? e.extend({}, f, d) : e.extend({}, d, f), h = s(h), r && (h = h.replace(x, i))), c = p[1] + (r ? "#" : h || !p[1] ? "?" : "") + h + g) : c = o(a !== n ? a : t[y][b]), c
		}

		function O(e, t, r) {
			return t === n || typeof t == "boolean" ? (r = t, t = s[e ? m : v]()) : t = N(t) ? t.replace(e ? S : E, "") : t, u(t, r)
		}

		function M(t, r, i, o) {
			return !N(i) && typeof i != "object" && (o = i, i = r, r = n), this.each(function () {
				var n = e(this),
					u = r || h()[(this.nodeName || "").toLowerCase()] || "",
					a = u && n.attr(u) || "";
				n.attr(u, s[t](a, i, o))
			})
		}
		var n, r = Array.prototype.slice,
			i = decodeURIComponent,
			s = e.param,
			o, u, a, f = e.bbq = e.bbq || {},
			l, c, h, p = e.event.special,
			d = "hashchange",
			v = "querystring",
			m = "fragment",
			g = "elemUrlAttr",
			y = "location",
			b = "href",
			w = "src",
			E = /^.*\?|#.*$/g,
			S = /^.*\#/,
			x, T = {};
		s[v] = C(A, 0, L), s[m] = o = C(A, 1, k), o.noEscape = function (t) {
			t = t || "";
			var n = e.map(t.split(""), encodeURIComponent);
			x = new RegExp(n.join("|"), "g")
		}, o.noEscape(",/"), e.deparam = u = function (t, r) {
			var s = {},
				o = {
					"true": !0,
					"false": !1,
					"null": null
				};
			return e.each(t.replace(/\+/g, " ").split("&"), function (t, u) {
				var a = u.split("="),
					f = i(a[0]),
					l, c = s,
					h = 0,
					p = f.split("]["),
					d = p.length - 1;
				/\[/.test(p[0]) && /\]$/.test(p[d]) ? (p[d] = p[d].replace(/\]$/, ""), p = p.shift().split("[").concat(p), d = p.length - 1) : d = 0;
				if (a.length === 2) {
					l = i(a[1]), r && (l = l && !isNaN(l) ? +l : l === "undefined" ? n : o[l] !== n ? o[l] : l);
					if (d)
						for (; h <= d; h++) f = p[h] === "" ? c.length : p[h], c = c[f] = h < d ? c[f] || (p[h + 1] && isNaN(p[h + 1]) ? {} : []) : l;
					else e.isArray(s[f]) ? s[f].push(l) : s[f] !== n ? s[f] = [s[f], l] : s[f] = l
				} else f && (s[f] = r ? n : "")
			}), s
		}, u[v] = C(O, 0), u[m] = a = C(O, 1), e[g] || (e[g] = function (t) {
			return e.extend(T, t)
		})({
			a: b,
			base: b,
			iframe: w,
			img: w,
			input: w,
			form: "action",
			link: b,
			script: w
		}), h = e[g], e.fn[v] = C(M, v), e.fn[m] = C(M, m), f.pushState = l = function (e, r) {
			N(e) && /^#/.test(e) && r === n && (r = 2);
			var i = e !== n,
				s = o(t[y][b], i ? e : {}, i ? r : 2);
			t[y][b] = s + (/#/.test(s) ? "" : "#")
		}, f.getState = c = function (e, t) {
			return e === n || typeof e == "boolean" ? a(e) : a(t)[e]
		}, f.removeState = function (t) {
			var r = {};
			t !== n && (r = c(), e.each(e.isArray(t) ? t : arguments, function (e, t) {
				delete r[t]
			})), l(r, 2)
		}, p[d] = e.extend(p[d], {
			add: function (t) {
				function i(e) {
					var t = e[m] = o();
					e.getState = function (e, r) {
						return e === n || typeof e == "boolean" ? u(t, e) : u(t, r)[e]
					}, r.apply(this, arguments)
				}
				var r;
				if (e.isFunction(t)) return r = t, i;
				r = t.handler, t.handler = i
			}
		})
	}(jQuery, this),
	function (e, t, n) {
		function h(e) {
			return e = e || t[s][u], e.replace(/^[^#]*#?(.*)$/, "$1")
		}
		var r, i = e.event.special,
			s = "location",
			o = "hashchange",
			u = "href",
			a = e.browser,
			f = document.documentMode,
			l = a.msie && (f === n || f < 8),
			c = "on" + o in t && !l;
		e[o + "Delay"] = 100, i[o] = e.extend(i[o], {
			setup: function () {
				if (c) return !1;
				e(r.start)
			},
			teardown: function () {
				if (c) return !1;
				e(r.stop)
			}
		}), r = function () {
			function c() {
				a = f = function (e) {
					return e
				}, l && (i = e('<iframe src="javascript:0"/>').hide().insertAfter("body")[0].contentWindow, f = function () {
					return h(i.document[s][u])
				}, a = function (e, t) {
					if (e !== t) {
						var n = i.document;
						n.open().close(), n[s].hash = "#" + e
					}
				}, a(h()))
			}
			var n = {},
				r, i, a, f;
			return n.start = function () {
				if (r) return;
				var n = h();
				a || c(),
					function i() {
						var l = h(),
							c = f(n);
						l !== n ? (a(n = l, c), e(t).trigger(o)) : c !== n && (t[s][u] = t[s][u].replace(/#.*/, "") + "#" + c), r = setTimeout(i, e[o + "Delay"])
					}()
			}, n.stop = function () {
				i || (r && clearTimeout(r), r = 0)
			}, n
		}()
	}(jQuery, this),
	function (e, t) {
		e.fn.contextMenu = function (n) {
			return this.each(function () {
				function i(n, i, s) {
					var o = e(t)[i](),
						u = e(t)[s](),
						a = r[i](),
						f = n + u;
					return n + a > o && a < n && (f -= a), f
				}
				var r = e(this);
				e(document).on("contextmenu", n.menuSelector, function (t) {
					if (t.ctrlKey) return;
					return r.data("invokedOn", e(t.target)).show().css({
						position: "absolute",
						left: i(t.clientX, "width", "scrollLeft"),
						top: i(t.clientY, "height", "scrollTop")
					}).off("click").on("click", "a", function (t) {
						r.hide();
						var i = r.data("invokedOn"),
							s = e(t.target);
						n.menuSelected.call(this, i, s)
					}), !1
				}), e(document).click(function () {
					r.hide()
				})
			})
		}
	}(jQuery, window);
var QRCode;
(function () {
	function e(e) {
		this.mode = n.MODE_8BIT_BYTE, this.data = e, this.parsedData = [];
		for (var t = 0, r = this.data.length; t < r; t++) {
			var i = [],
				s = this.data.charCodeAt(t);
			s > 65536 ? (i[0] = 240 | (s & 1835008) >>> 18, i[1] = 128 | (s & 258048) >>> 12, i[2] = 128 | (s & 4032) >>> 6, i[3] = 128 | s & 63) : s > 2048 ? (i[0] = 224 | (s & 61440) >>> 12, i[1] = 128 | (s & 4032) >>> 6, i[2] = 128 | s & 63) : s > 128 ? (i[0] = 192 | (s & 1984) >>> 6, i[1] = 128 | s & 63) : i[0] = s, this.parsedData.push(i)
		}
		this.parsedData = Array.prototype.concat.apply([], this.parsedData), this.parsedData.length != this.data.length && (this.parsedData.unshift(191), this.parsedData.unshift(187), this.parsedData.unshift(239))
	}

	function t(e, t) {
		this.typeNumber = e, this.errorCorrectLevel = t, this.modules = null, this.moduleCount = 0, this.dataCache = null, this.dataList = []
	}

	function a(e, t) {
		if (e.length == undefined) throw new Error(e.length + "/" + t);
		var n = 0;
		while (n < e.length && e[n] == 0) n++;
		this.num = new Array(e.length - n + t);
		for (var r = 0; r < e.length - n; r++) this.num[r] = e[r + n]
	}

	function f(e, t) {
		this.totalCount = e, this.dataCount = t
	}

	function l() {
		this.buffer = [], this.length = 0
	}

	function h() {
		return typeof CanvasRenderingContext2D != "undefined"
	}

	function p() {
		var e = !1,
			t = navigator.userAgent;
		if (/android/i.test(t)) {
			e = !0;
			var n = t.toString().match(/android ([0-9]\.[0-9])/i);
			n && n[1] && (e = parseFloat(n[1]))
		}
		return e
	}

	function g(e, t) {
		var n = 1,
			i = y(e);
		for (var s = 0, o = c.length; s <= o; s++) {
			var u = 0;
			switch (t) {
			case r.L:
				u = c[s][0];
				break;
			case r.M:
				u = c[s][1];
				break;
			case r.Q:
				u = c[s][2];
				break;
			case r.H:
				u = c[s][3]
			}
			if (i <= u) break;
			n++
		}
		if (n > c.length) throw new Error("Too long data");
		return n
	}

	function y(e) {
		var t = encodeURI(e).toString().replace(/\%[0-9a-fA-F]{2}/g, "a");
		return t.length + (t.length != e ? 3 : 0)
	}
	e.prototype = {
		getLength: function (e) {
			return this.parsedData.length
		},
		write: function (e) {
			for (var t = 0, n = this.parsedData.length; t < n; t++) e.put(this.parsedData[t], 8)
		}
	}, t.prototype = {
		addData: function (t) {
			var n = new e(t);
			this.dataList.push(n), this.dataCache = null
		},
		isDark: function (e, t) {
			if (e < 0 || this.moduleCount <= e || t < 0 || this.moduleCount <= t) throw new Error(e + "," + t);
			return this.modules[e][t]
		},
		getModuleCount: function () {
			return this.moduleCount
		},
		make: function () {
			this.makeImpl(!1, this.getBestMaskPattern())
		},
		makeImpl: function (e, n) {
			this.moduleCount = this.typeNumber * 4 + 17, this.modules = new Array(this.moduleCount);
			for (var r = 0; r < this.moduleCount; r++) {
				this.modules[r] = new Array(this.moduleCount);
				for (var i = 0; i < this.moduleCount; i++) this.modules[r][i] = null
			}
			this.setupPositionProbePattern(0, 0), this.setupPositionProbePattern(this.moduleCount - 7, 0), this.setupPositionProbePattern(0, this.moduleCount - 7), this.setupPositionAdjustPattern(), this.setupTimingPattern(), this.setupTypeInfo(e, n), this.typeNumber >= 7 && this.setupTypeNumber(e), this.dataCache == null && (this.dataCache = t.createData(this.typeNumber, this.errorCorrectLevel, this.dataList)), this.mapData(this.dataCache, n)
		},
		setupPositionProbePattern: function (e, t) {
			for (var n = -1; n <= 7; n++) {
				if (e + n <= -1 || this.moduleCount <= e + n) continue;
				for (var r = -1; r <= 7; r++) {
					if (t + r <= -1 || this.moduleCount <= t + r) continue;
					0 <= n && n <= 6 && (r == 0 || r == 6) || 0 <= r && r <= 6 && (n == 0 || n == 6) || 2 <= n && n <= 4 && 2 <= r && r <= 4 ? this.modules[e + n][t + r] = !0 : this.modules[e + n][t + r] = !1
				}
			}
		},
		getBestMaskPattern: function () {
			var e = 0,
				t = 0;
			for (var n = 0; n < 8; n++) {
				this.makeImpl(!0, n);
				var r = s.getLostPoint(this);
				if (n == 0 || e > r) e = r, t = n
			}
			return t
		},
		createMovieClip: function (e, t, n) {
			var r = e.createEmptyMovieClip(t, n),
				i = 1;
			this.make();
			for (var s = 0; s < this.modules.length; s++) {
				var o = s * i;
				for (var u = 0; u < this.modules[s].length; u++) {
					var a = u * i,
						f = this.modules[s][u];
					f && (r.beginFill(0, 100), r.moveTo(a, o), r.lineTo(a + i, o), r.lineTo(a + i, o + i), r.lineTo(a, o + i), r.endFill())
				}
			}
			return r
		},
		setupTimingPattern: function () {
			for (var e = 8; e < this.moduleCount - 8; e++) {
				if (this.modules[e][6] != null) continue;
				this.modules[e][6] = e % 2 == 0
			}
			for (var t = 8; t < this.moduleCount - 8; t++) {
				if (this.modules[6][t] != null) continue;
				this.modules[6][t] = t % 2 == 0
			}
		},
		setupPositionAdjustPattern: function () {
			var e = s.getPatternPosition(this.typeNumber);
			for (var t = 0; t < e.length; t++)
				for (var n = 0; n < e.length; n++) {
					var r = e[t],
						i = e[n];
					if (this.modules[r][i] != null) continue;
					for (var o = -2; o <= 2; o++)
						for (var u = -2; u <= 2; u++) o == -2 || o == 2 || u == -2 || u == 2 || o == 0 && u == 0 ? this.modules[r + o][i + u] = !0 : this.modules[r + o][i + u] = !1
				}
		},
		setupTypeNumber: function (e) {
			var t = s.getBCHTypeNumber(this.typeNumber);
			for (var n = 0; n < 18; n++) {
				var r = !e && (t >> n & 1) == 1;
				this.modules[Math.floor(n / 3)][n % 3 + this.moduleCount - 8 - 3] = r
			}
			for (var n = 0; n < 18; n++) {
				var r = !e && (t >> n & 1) == 1;
				this.modules[n % 3 + this.moduleCount - 8 - 3][Math.floor(n / 3)] = r
			}
		},
		setupTypeInfo: function (e, t) {
			var n = this.errorCorrectLevel << 3 | t,
				r = s.getBCHTypeInfo(n);
			for (var i = 0; i < 15; i++) {
				var o = !e && (r >> i & 1) == 1;
				i < 6 ? this.modules[i][8] = o : i < 8 ? this.modules[i + 1][8] = o : this.modules[this.moduleCount - 15 + i][8] = o
			}
			for (var i = 0; i < 15; i++) {
				var o = !e && (r >> i & 1) == 1;
				i < 8 ? this.modules[8][this.moduleCount - i - 1] = o : i < 9 ? this.modules[8][15 - i - 1 + 1] = o : this.modules[8][15 - i - 1] = o
			}
			this.modules[this.moduleCount - 8][8] = !e
		},
		mapData: function (e, t) {
			var n = -1,
				r = this.moduleCount - 1,
				i = 7,
				o = 0;
			for (var u = this.moduleCount - 1; u > 0; u -= 2) {
				u == 6 && u--;
				for (;;) {
					for (var a = 0; a < 2; a++)
						if (this.modules[r][u - a] == null) {
							var f = !1;
							o < e.length && (f = (e[o] >>> i & 1) == 1);
							var l = s.getMask(t, r, u - a);
							l && (f = !f), this.modules[r][u - a] = f, i--, i == -1 && (o++, i = 7)
						}
					r += n;
					if (r < 0 || this.moduleCount <= r) {
						r -= n, n = -n;
						break
					}
				}
			}
		}
	}, t.PAD0 = 236, t.PAD1 = 17, t.createData = function (e, n, r) {
		var i = f.getRSBlocks(e, n),
			o = new l;
		for (var u = 0; u < r.length; u++) {
			var a = r[u];
			o.put(a.mode, 4), o.put(a.getLength(), s.getLengthInBits(a.mode, e)), a.write(o)
		}
		var c = 0;
		for (var u = 0; u < i.length; u++) c += i[u].dataCount;
		if (o.getLengthInBits() > c * 8) throw new Error("code length overflow. (" + o.getLengthInBits() + ">" + c * 8 + ")");
		o.getLengthInBits() + 4 <= c * 8 && o.put(0, 4);
		while (o.getLengthInBits() % 8 != 0) o.putBit(!1);
		for (;;) {
			if (o.getLengthInBits() >= c * 8) break;
			o.put(t.PAD0, 8);
			if (o.getLengthInBits() >= c * 8) break;
			o.put(t.PAD1, 8)
		}
		return t.createBytes(o, i)
	}, t.createBytes = function (e, t) {
		var n = 0,
			r = 0,
			i = 0,
			o = new Array(t.length),
			u = new Array(t.length);
		for (var f = 0; f < t.length; f++) {
			var l = t[f].dataCount,
				c = t[f].totalCount - l;
			r = Math.max(r, l), i = Math.max(i, c), o[f] = new Array(l);
			for (var h = 0; h < o[f].length; h++) o[f][h] = 255 & e.buffer[h + n];
			n += l;
			var p = s.getErrorCorrectPolynomial(c),
				d = new a(o[f], p.getLength() - 1),
				v = d.mod(p);
			u[f] = new Array(p.getLength() - 1);
			for (var h = 0; h < u[f].length; h++) {
				var m = h + v.getLength() - u[f].length;
				u[f][h] = m >= 0 ? v.get(m) : 0
			}
		}
		var g = 0;
		for (var h = 0; h < t.length; h++) g += t[h].totalCount;
		var y = new Array(g),
			b = 0;
		for (var h = 0; h < r; h++)
			for (var f = 0; f < t.length; f++) h < o[f].length && (y[b++] = o[f][h]);
		for (var h = 0; h < i; h++)
			for (var f = 0; f < t.length; f++) h < u[f].length && (y[b++] = u[f][h]);
		return y
	};
	var n = {
			MODE_NUMBER: 1,
			MODE_ALPHA_NUM: 2,
			MODE_8BIT_BYTE: 4,
			MODE_KANJI: 8
		},
		r = {
			L: 1,
			M: 0,
			Q: 3,
			H: 2
		},
		i = {
			PATTERN000: 0,
			PATTERN001: 1,
			PATTERN010: 2,
			PATTERN011: 3,
			PATTERN100: 4,
			PATTERN101: 5,
			PATTERN110: 6,
			PATTERN111: 7
		},
		s = {
			PATTERN_POSITION_TABLE: [[], [6, 18], [6, 22], [6, 26], [6, 30], [6, 34], [6, 22, 38], [6, 24, 42], [6, 26, 46], [6, 28, 50], [6, 30, 54], [6, 32, 58], [6, 34, 62], [6, 26, 46, 66], [6, 26, 48, 70], [6, 26, 50, 74], [6, 30, 54, 78], [6, 30, 56, 82], [6, 30, 58, 86], [6, 34, 62, 90], [6, 28, 50, 72, 94], [6, 26, 50, 74, 98], [6, 30, 54, 78, 102], [6, 28, 54, 80, 106], [6, 32, 58, 84, 110], [6, 30, 58, 86, 114], [6, 34, 62, 90, 118], [6, 26, 50, 74, 98, 122], [6, 30, 54, 78, 102, 126], [6, 26, 52, 78, 104, 130], [6, 30, 56, 82, 108, 134], [6, 34, 60, 86, 112, 138], [6, 30, 58, 86, 114, 142], [6, 34, 62, 90, 118, 146], [6, 30, 54, 78, 102, 126, 150], [6, 24, 50, 76, 102, 128, 154], [6, 28, 54, 80, 106, 132, 158], [6, 32, 58, 84, 110, 136, 162], [6, 26, 54, 82, 110, 138, 166], [6, 30, 58, 86, 114, 142, 170]],
			G15: 1335,
			G18: 7973,
			G15_MASK: 21522,
			getBCHTypeInfo: function (e) {
				var t = e << 10;
				while (s.getBCHDigit(t) - s.getBCHDigit(s.G15) >= 0) t ^= s.G15 << s.getBCHDigit(t) - s.getBCHDigit(s.G15);
				return (e << 10 | t) ^ s.G15_MASK
			},
			getBCHTypeNumber: function (e) {
				var t = e << 12;
				while (s.getBCHDigit(t) - s.getBCHDigit(s.G18) >= 0) t ^= s.G18 << s.getBCHDigit(t) - s.getBCHDigit(s.G18);
				return e << 12 | t
			},
			getBCHDigit: function (e) {
				var t = 0;
				while (e != 0) t++, e >>>= 1;
				return t
			},
			getPatternPosition: function (e) {
				return s.PATTERN_POSITION_TABLE[e - 1]
			},
			getMask: function (e, t, n) {
				switch (e) {
				case i.PATTERN000:
					return (t + n) % 2 == 0;
				case i.PATTERN001:
					return t % 2 == 0;
				case i.PATTERN010:
					return n % 3 == 0;
				case i.PATTERN011:
					return (t + n) % 3 == 0;
				case i.PATTERN100:
					return (Math.floor(t / 2) + Math.floor(n / 3)) % 2 == 0;
				case i.PATTERN101:
					return t * n % 2 + t * n % 3 == 0;
				case i.PATTERN110:
					return (t * n % 2 + t * n % 3) % 2 == 0;
				case i.PATTERN111:
					return (t * n % 3 + (t + n) % 2) % 2 == 0;
				default:
					throw new Error("bad maskPattern:" + e)
				}
			},
			getErrorCorrectPolynomial: function (e) {
				var t = new a([1], 0);
				for (var n = 0; n < e; n++) t = t.multiply(new a([1, o.gexp(n)], 0));
				return t
			},
			getLengthInBits: function (e, t) {
				if (1 <= t && t < 10) switch (e) {
				case n.MODE_NUMBER:
					return 10;
				case n.MODE_ALPHA_NUM:
					return 9;
				case n.MODE_8BIT_BYTE:
					return 8;
				case n.MODE_KANJI:
					return 8;
				default:
					throw new Error("mode:" + e)
				} else if (t < 27) switch (e) {
				case n.MODE_NUMBER:
					return 12;
				case n.MODE_ALPHA_NUM:
					return 11;
				case n.MODE_8BIT_BYTE:
					return 16;
				case n.MODE_KANJI:
					return 10;
				default:
					throw new Error("mode:" + e)
				} else {
					if (!(t < 41)) throw new Error("type:" + t);
					switch (e) {
					case n.MODE_NUMBER:
						return 14;
					case n.MODE_ALPHA_NUM:
						return 13;
					case n.MODE_8BIT_BYTE:
						return 16;
					case n.MODE_KANJI:
						return 12;
					default:
						throw new Error("mode:" + e)
					}
				}
			},
			getLostPoint: function (e) {
				var t = e.getModuleCount(),
					n = 0;
				for (var r = 0; r < t; r++)
					for (var i = 0; i < t; i++) {
						var s = 0,
							o = e.isDark(r, i);
						for (var u = -1; u <= 1; u++) {
							if (r + u < 0 || t <= r + u) continue;
							for (var a = -1; a <= 1; a++) {
								if (i + a < 0 || t <= i + a) continue;
								if (u == 0 && a == 0) continue;
								o == e.isDark(r + u, i + a) && s++
							}
						}
						s > 5 && (n += 3 + s - 5)
					}
				for (var r = 0; r < t - 1; r++)
					for (var i = 0; i < t - 1; i++) {
						var f = 0;
						e.isDark(r, i) && f++, e.isDark(r + 1, i) && f++, e.isDark(r, i + 1) && f++, e.isDark(r + 1, i + 1) && f++;
						if (f == 0 || f == 4) n += 3
					}
				for (var r = 0; r < t; r++)
					for (var i = 0; i < t - 6; i++) e.isDark(r, i) && !e.isDark(r, i + 1) && e.isDark(r, i + 2) && e.isDark(r, i + 3) && e.isDark(r, i + 4) && !e.isDark(r, i + 5) && e.isDark(r, i + 6) && (n += 40);
				for (var i = 0; i < t; i++)
					for (var r = 0; r < t - 6; r++) e.isDark(r, i) && !e.isDark(r + 1, i) && e.isDark(r + 2, i) && e.isDark(r + 3, i) && e.isDark(r + 4, i) && !e.isDark(r + 5, i) && e.isDark(r + 6, i) && (n += 40);
				var l = 0;
				for (var i = 0; i < t; i++)
					for (var r = 0; r < t; r++) e.isDark(r, i) && l++;
				var c = Math.abs(100 * l / t / t - 50) / 5;
				return n += c * 10, n
			}
		},
		o = {
			glog: function (e) {
				if (e < 1) throw new Error("glog(" + e + ")");
				return o.LOG_TABLE[e]
			},
			gexp: function (e) {
				while (e < 0) e += 255;
				while (e >= 256) e -= 255;
				return o.EXP_TABLE[e]
			},
			EXP_TABLE: new Array(256),
			LOG_TABLE: new Array(256)
		};
	for (var u = 0; u < 8; u++) o.EXP_TABLE[u] = 1 << u;
	for (var u = 8; u < 256; u++) o.EXP_TABLE[u] = o.EXP_TABLE[u - 4] ^ o.EXP_TABLE[u - 5] ^ o.EXP_TABLE[u - 6] ^ o.EXP_TABLE[u - 8];
	for (var u = 0; u < 255; u++) o.LOG_TABLE[o.EXP_TABLE[u]] = u;
	a.prototype = {
		get: function (e) {
			return this.num[e]
		},
		getLength: function () {
			return this.num.length
		},
		multiply: function (e) {
			var t = new Array(this.getLength() + e.getLength() - 1);
			for (var n = 0; n < this.getLength(); n++)
				for (var r = 0; r < e.getLength(); r++) t[n + r] ^= o.gexp(o.glog(this.get(n)) + o.glog(e.get(r)));
			return new a(t, 0)
		},
		mod: function (e) {
			if (this.getLength() - e.getLength() < 0) return this;
			var t = o.glog(this.get(0)) - o.glog(e.get(0)),
				n = new Array(this.getLength());
			for (var r = 0; r < this.getLength(); r++) n[r] = this.get(r);
			for (var r = 0; r < e.getLength(); r++) n[r] ^= o.gexp(o.glog(e.get(r)) + t);
			return (new a(n, 0)).mod(e)
		}
	}, f.RS_BLOCK_TABLE = [[1, 26, 19], [1, 26, 16], [1, 26, 13], [1, 26, 9], [1, 44, 34], [1, 44, 28], [1, 44, 22], [1, 44, 16], [1, 70, 55], [1, 70, 44], [2, 35, 17], [2, 35, 13], [1, 100, 80], [2, 50, 32], [2, 50, 24], [4, 25, 9], [1, 134, 108], [2, 67, 43], [2, 33, 15, 2, 34, 16], [2, 33, 11, 2, 34, 12], [2, 86, 68], [4, 43, 27], [4, 43, 19], [4, 43, 15], [2, 98, 78], [4, 49, 31], [2, 32, 14, 4, 33, 15], [4, 39, 13, 1, 40, 14], [2, 121, 97], [2, 60, 38, 2, 61, 39], [4, 40, 18, 2, 41, 19], [4, 40, 14, 2, 41, 15], [2, 146, 116], [3, 58, 36, 2, 59, 37], [4, 36, 16, 4, 37, 17], [4, 36, 12, 4, 37, 13], [2, 86, 68, 2, 87, 69], [4, 69, 43, 1, 70, 44], [6, 43, 19, 2, 44, 20], [6, 43, 15, 2, 44, 16], [4, 101, 81], [1, 80, 50, 4, 81, 51], [4, 50, 22, 4, 51, 23], [3, 36, 12, 8, 37, 13], [2, 116, 92, 2, 117, 93], [6, 58, 36, 2, 59, 37], [4, 46, 20, 6, 47, 21], [7, 42, 14, 4, 43, 15], [4, 133, 107], [8, 59, 37, 1, 60, 38], [8, 44, 20, 4, 45, 21], [12, 33, 11, 4, 34, 12], [3, 145, 115, 1, 146, 116], [4, 64, 40, 5, 65, 41], [11, 36, 16, 5, 37, 17], [11, 36, 12, 5, 37, 13], [5, 109, 87, 1, 110, 88], [5, 65, 41, 5, 66, 42], [5, 54, 24, 7, 55, 25], [11, 36, 12], [5, 122, 98, 1, 123, 99], [7, 73, 45, 3, 74, 46], [15, 43, 19, 2, 44, 20], [3, 45, 15, 13, 46, 16], [1, 135, 107, 5, 136, 108], [10, 74, 46, 1, 75, 47], [1, 50, 22, 15, 51, 23], [2, 42, 14, 17, 43, 15], [5, 150, 120, 1, 151, 121], [9, 69, 43, 4, 70, 44], [17, 50, 22, 1, 51, 23], [2, 42, 14, 19, 43, 15], [3, 141, 113, 4, 142, 114], [3, 70, 44, 11, 71, 45], [17, 47, 21, 4, 48, 22], [9, 39, 13, 16, 40, 14], [3, 135, 107, 5, 136, 108], [3, 67, 41, 13, 68, 42], [15, 54, 24, 5, 55, 25], [15, 43, 15, 10, 44, 16], [4, 144, 116, 4, 145, 117], [17, 68, 42], [17, 50, 22, 6, 51, 23], [19, 46, 16, 6, 47, 17], [2, 139, 111, 7, 140, 112], [17, 74, 46], [7, 54, 24, 16, 55, 25], [34, 37, 13], [4, 151, 121, 5, 152, 122], [4, 75, 47, 14, 76, 48], [11, 54, 24, 14, 55, 25], [16, 45, 15, 14, 46, 16], [6, 147, 117, 4, 148, 118], [6, 73, 45, 14, 74, 46], [11, 54, 24, 16, 55, 25], [30, 46, 16, 2, 47, 17], [8, 132, 106, 4, 133, 107], [8, 75, 47, 13, 76, 48], [7, 54, 24, 22, 55, 25], [22, 45, 15, 13, 46, 16], [10, 142, 114, 2, 143, 115], [19, 74, 46, 4, 75, 47], [28, 50, 22, 6, 51, 23], [33, 46, 16, 4, 47, 17], [8, 152, 122, 4, 153, 123], [22, 73, 45, 3, 74, 46], [8, 53, 23, 26, 54, 24], [12, 45, 15, 28, 46, 16], [3, 147, 117, 10, 148, 118], [3, 73, 45, 23, 74, 46], [4, 54, 24, 31, 55, 25], [11, 45, 15, 31, 46, 16], [7, 146, 116, 7, 147, 117], [21, 73, 45, 7, 74, 46], [1, 53, 23, 37, 54, 24], [19, 45, 15, 26, 46, 16], [5, 145, 115, 10, 146, 116], [19, 75, 47, 10, 76, 48], [15, 54, 24, 25, 55, 25], [23, 45, 15, 25, 46, 16], [13, 145, 115, 3, 146, 116], [2, 74, 46, 29, 75, 47], [42, 54, 24, 1, 55, 25], [23, 45, 15, 28, 46, 16], [17, 145, 115], [10, 74, 46, 23, 75, 47], [10, 54, 24, 35, 55, 25], [19, 45, 15, 35, 46, 16], [17, 145, 115, 1, 146, 116], [14, 74, 46, 21, 75, 47], [29, 54, 24, 19, 55, 25], [11, 45, 15, 46, 46, 16], [13, 145, 115, 6, 146, 116], [14, 74, 46, 23, 75, 47], [44, 54, 24, 7, 55, 25], [59, 46, 16, 1, 47, 17], [12, 151, 121, 7, 152, 122], [12, 75, 47, 26, 76, 48], [39, 54, 24, 14, 55, 25], [22, 45, 15, 41, 46, 16], [6, 151, 121, 14, 152, 122], [6, 75, 47, 34, 76, 48], [46, 54, 24, 10, 55, 25], [2, 45, 15, 64, 46, 16], [17, 152, 122, 4, 153, 123], [29, 74, 46, 14, 75, 47], [49, 54, 24, 10, 55, 25], [24, 45, 15, 46, 46, 16], [4, 152, 122, 18, 153, 123], [13, 74, 46, 32, 75, 47], [48, 54, 24, 14, 55, 25], [42, 45, 15, 32, 46, 16], [20, 147, 117, 4, 148, 118], [40, 75, 47, 7, 76, 48], [43, 54, 24, 22, 55, 25], [10, 45, 15, 67, 46, 16], [19, 148, 118, 6, 149, 119], [18, 75, 47, 31, 76, 48], [34, 54, 24, 34, 55, 25], [20, 45, 15, 61, 46, 16]], f.getRSBlocks = function (e, t) {
		var n = f.getRsBlockTable(e, t);
		if (n == undefined) throw new Error("bad rs block @ typeNumber:" + e + "/errorCorrectLevel:" + t);
		var r = n.length / 3,
			i = [];
		for (var s = 0; s < r; s++) {
			var o = n[s * 3 + 0],
				u = n[s * 3 + 1],
				a = n[s * 3 + 2];
			for (var l = 0; l < o; l++) i.push(new f(u, a))
		}
		return i
	}, f.getRsBlockTable = function (e, t) {
		switch (t) {
		case r.L:
			return f.RS_BLOCK_TABLE[(e - 1) * 4 + 0];
		case r.M:
			return f.RS_BLOCK_TABLE[(e - 1) * 4 + 1];
		case r.Q:
			return f.RS_BLOCK_TABLE[(e - 1) * 4 + 2];
		case r.H:
			return f.RS_BLOCK_TABLE[(e - 1) * 4 + 3];
		default:
			return undefined
		}
	}, l.prototype = {
		get: function (e) {
			var t = Math.floor(e / 8);
			return (this.buffer[t] >>> 7 - e % 8 & 1) == 1
		},
		put: function (e, t) {
			for (var n = 0; n < t; n++) this.putBit((e >>> t - n - 1 & 1) == 1)
		},
		getLengthInBits: function () {
			return this.length
		},
		putBit: function (e) {
			var t = Math.floor(this.length / 8);
			this.buffer.length <= t && this.buffer.push(0), e && (this.buffer[t] |= 128 >>> this.length % 8), this.length++
		}
	};
	var c = [[17, 14, 11, 7], [32, 26, 20, 14], [53, 42, 32, 24], [78, 62, 46, 34], [106, 84, 60, 44], [134, 106, 74, 58], [154, 122, 86, 64], [192, 152, 108, 84], [230, 180, 130, 98], [271, 213, 151, 119], [321, 251, 177, 137], [367, 287, 203, 155], [425, 331, 241, 177], [458, 362, 258, 194], [520, 412, 292, 220], [586, 450, 322, 250], [644, 504, 364, 280], [718, 560, 394, 310], [792, 624, 442, 338], [858, 666, 482, 382], [929, 711, 509, 403], [1003, 779, 565, 439], [1091, 857, 611, 461], [1171, 911, 661, 511], [1273, 997, 715, 535], [1367, 1059, 751, 593], [1465, 1125, 805, 625], [1528, 1190, 868, 658], [1628, 1264, 908, 698], [1732, 1370, 982, 742], [1840, 1452, 1030, 790], [1952, 1538, 1112, 842], [2068, 1628, 1168, 898], [2188, 1722, 1228, 958], [2303, 1809, 1283, 983], [2431, 1911, 1351, 1051], [2563, 1989, 1423, 1093], [2699, 2099, 1499, 1139], [2809, 2213, 1579, 1219], [2953, 2331, 1663, 1273]],
		d = function () {
			var e = function (e, t) {
				this._el = e, this._htOption = t
			};
			return e.prototype.draw = function (e) {
				function o(e, t) {
					var n = document.createElementNS("http://www.w3.org/2000/svg", e);
					for (var r in t) t.hasOwnProperty(r) && n.setAttribute(r, t[r]);
					return n
				}
				var t = this._htOption,
					n = this._el,
					r = e.getModuleCount(),
					i = Math.floor(t.width / r),
					s = Math.floor(t.height / r);
				this.clear();
				var u = o("svg", {
					viewBox: "0 0 " + String(r) + " " + String(r),
					width: "100%",
					height: "100%",
					fill: t.colorLight
				});
				u.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink"), n.appendChild(u), u.appendChild(o("rect", {
					fill: t.colorLight,
					width: "100%",
					height: "100%"
				})), u.appendChild(o("rect", {
					fill: t.colorDark,
					width: "1",
					height: "1",
					id: "template"
				}));
				for (var a = 0; a < r; a++)
					for (var f = 0; f < r; f++)
						if (e.isDark(a, f)) {
							var l = o("use", {
								x: String(f),
								y: String(a)
							});
							l.setAttributeNS("http://www.w3.org/1999/xlink", "href", "#template"), u.appendChild(l)
						}
			}, e.prototype.clear = function () {
				while (this._el.hasChildNodes()) this._el.removeChild(this._el.lastChild)
			}, e
		}(),
		v = document.documentElement.tagName.toLowerCase() === "svg",
		m = v ? d : h() ? function () {
			function e() {
				this._elImage.src = this._elCanvas.toDataURL("image/png"), this._elImage.style.display = "block", this._elCanvas.style.display = "none"
			}

			function r(e, t) {
				var n = this;
				n._fFail = t, n._fSuccess = e;
				if (n._bSupportDataURI === null) {
					var r = document.createElement("img"),
						i = function () {
							n._bSupportDataURI = !1, n._fFail && n._fFail.call(n)
						},
						s = function () {
							n._bSupportDataURI = !0, n._fSuccess && n._fSuccess.call(n)
						};
					r.onabort = i, r.onerror = i, r.onload = s, r.src = "data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==";
					return
				}
				n._bSupportDataURI === !0 && n._fSuccess ? n._fSuccess.call(n) : n._bSupportDataURI === !1 && n._fFail && n._fFail.call(n)
			}
			if (this._android && this._android <= 2.1) {
				var t = 1 / window.devicePixelRatio,
					n = CanvasRenderingContext2D.prototype.drawImage;
				CanvasRenderingContext2D.prototype.drawImage = function (e, r, i, s, o, u, a, f, l) {
					if ("nodeName" in e && /img/i.test(e.nodeName))
						for (var c = arguments.length - 1; c >= 1; c--) arguments[c] = arguments[c] * t;
					else typeof f == "undefined" && (arguments[1] *= t, arguments[2] *= t, arguments[3] *= t, arguments[4] *= t);
					n.apply(this, arguments)
				}
			}
			var i = function (e, t) {
				this._bIsPainted = !1, this._android = p(), this._htOption = t, this._elCanvas = document.createElement("canvas"), this._elCanvas.width = t.width, this._elCanvas.height = t.height, e.appendChild(this._elCanvas), this._el = e, this._oContext = this._elCanvas.getContext("2d"), this._bIsPainted = !1, this._elImage = document.createElement("img"), this._elImage.alt = "Scan me!", this._elImage.style.display = "none", this._el.appendChild(this._elImage), this._bSupportDataURI = null
			};
			return i.prototype.draw = function (e) {
				var t = this._elImage,
					n = this._oContext,
					r = this._htOption,
					i = e.getModuleCount(),
					s = r.width / i,
					o = r.height / i,
					u = Math.round(s),
					a = Math.round(o);
				t.style.display = "none", this.clear();
				for (var f = 0; f < i; f++)
					for (var l = 0; l < i; l++) {
						var c = e.isDark(f, l),
							h = l * s,
							p = f * o;
						n.strokeStyle = c ? r.colorDark : r.colorLight, n.lineWidth = 1, n.fillStyle = c ? r.colorDark : r.colorLight, n.fillRect(h, p, s, o), n.strokeRect(Math.floor(h) + .5, Math.floor(p) + .5, u, a), n.strokeRect(Math.ceil(h) - .5, Math.ceil(p) - .5, u, a)
					}
				this._bIsPainted = !0
			}, i.prototype.makeImage = function () {
				this._bIsPainted && r.call(this, e)
			}, i.prototype.isPainted = function () {
				return this._bIsPainted
			}, i.prototype.clear = function () {
				this._oContext.clearRect(0, 0, this._elCanvas.width, this._elCanvas.height), this._bIsPainted = !1
			}, i.prototype.round = function (e) {
				return e ? Math.floor(e * 1e3) / 1e3 : e
			}, i
		}() : function () {
			var e = function (e, t) {
				this._el = e, this._htOption = t
			};
			return e.prototype.draw = function (e) {
				var t = this._htOption,
					n = this._el,
					r = e.getModuleCount(),
					i = Math.floor(t.width / r),
					s = Math.floor(t.height / r),
					o = ['<table style="border:0;border-collapse:collapse;">'];
				for (var u = 0; u < r; u++) {
					o.push("<tr>");
					for (var a = 0; a < r; a++) o.push('<td style="border:0;border-collapse:collapse;padding:0;margin:0;width:' + i + "px;height:" + s + "px;background-color:" + (e.isDark(u, a) ? t.colorDark : t.colorLight) + ';"></td>');
					o.push("</tr>")
				}
				o.push("</table>"), n.innerHTML = o.join("");
				var f = n.childNodes[0],
					l = (t.width - f.offsetWidth) / 2,
					c = (t.height - f.offsetHeight) / 2;
				l > 0 && c > 0 && (f.style.margin = c + "px " + l + "px")
			}, e.prototype.clear = function () {
				this._el.innerHTML = ""
			}, e
		}();
	QRCode = function (e, t) {
		this._htOption = {
			width: 256,
			height: 256,
			typeNumber: 4,
			colorDark: "#000000",
			colorLight: "#ffffff",
			correctLevel: r.H
		}, typeof t == "string" && (t = {
			text: t
		});
		if (t)
			for (var n in t) this._htOption[n] = t[n];
		typeof e == "string" && (e = document.getElementById(e)), this._htOption.useSVG && (m = d), this._android = p(), this._el = e, this._oQRCode = null, this._oDrawing = new m(this._el, this._htOption), this._htOption.text && this.makeCode(this._htOption.text)
	}, QRCode.prototype.makeCode = function (e) {
		this._oQRCode = new t(g(e, this._htOption.correctLevel), this._htOption.correctLevel), this._oQRCode.addData(e), this._oQRCode.make(), this._el.title = e, this._oDrawing.draw(this._oQRCode), this.makeImage()
	}, QRCode.prototype.makeImage = function () {
		typeof this._oDrawing.makeImage == "function" && (!this._android || this._android >= 3) && this._oDrawing.makeImage()
	}, QRCode.prototype.clear = function () {
		this._oDrawing.clear()
	}, QRCode.CorrectLevel = r
})();
if ($.browser.msie && $.browser.version < 9) hljs = {
	highlightBlock: function () {},
	configrations: function () {}
};
else {
	var hljs = new function () {
		function e(e) {
			return e.replace(/&/gm, "&amp;").replace(/</gm, "&lt;").replace(/>/gm, "&gt;")
		}

		function t(e) {
			return e.nodeName.toLowerCase()
		}

		function n(e, t) {
			var n = e && e.exec(t);
			return n && 0 == n.index
		}

		function r(e) {
			var t = (e.className + " " + (e.parentNode ? e.parentNode.className : "")).split(/\s+/);
			return t = t.map(function (e) {
				return e.replace(/^lang(uage)?-/, "")
			}), t.filter(function (e) {
				return y(e) || /no(-?)highlight/.test(e)
			})[0]
		}

		function i(e, t) {
			var n = {};
			for (var r in e) n[r] = e[r];
			if (t)
				for (var r in t) n[r] = t[r];
			return n
		}

		function s(e) {
			var n = [];
			return function r(e, i) {
				for (var s = e.firstChild; s; s = s.nextSibling) 3 == s.nodeType ? i += s.nodeValue.length : 1 == s.nodeType && (n.push({
					event: "start",
					offset: i,
					node: s
				}), i = r(s, i), t(s).match(/br|hr|img|input/) || n.push({
					event: "stop",
					offset: i,
					node: s
				}));
				return i
			}(e, 0), n
		}

		function o(n, r, i) {
			function s() {
				return n.length && r.length ? n[0].offset != r[0].offset ? n[0].offset < r[0].offset ? n : r : "start" == r[0].event ? n : r : n.length ? n : r
			}

			function o(n) {
				function r(t) {
					return " " + t.nodeName + '="' + e(t.value) + '"'
				}
				l += "<" + t(n) + Array.prototype.map.call(n.attributes, r).join("") + ">"
			}

			function u(e) {
				l += "</" + t(e) + ">"
			}

			function a(e) {
				("start" == e.event ? o : u)(e.node)
			}
			for (var f = 0, l = "", c = []; n.length || r.length;) {
				var h = s();
				if (l += e(i.substr(f, h[0].offset - f)), f = h[0].offset, h == n) {
					c.reverse().forEach(u);
					do a(h.splice(0, 1)[0]), h = s(); while (h == n && h.length && h[0].offset == f);
					c.reverse().forEach(o)
				} else "start" == h[0].event ? c.push(h[0].node) : c.pop(), a(h.splice(0, 1)[0])
			}
			return l + e(i.substr(f))
		}

		function u(e) {
			function t(e) {
				return e && e.source || e
			}

			function n(n, r) {
				return RegExp(t(n), "m" + (e.cI ? "i" : "") + (r ? "g" : ""))
			}

			function r(s, o) {
				if (!s.compiled) {
					if (s.compiled = !0, s.k = s.k || s.bK, s.k) {
						var u = {},
							a = function (t, n) {
								e.cI && (n = n.toLowerCase()), n.split(" ").forEach(function (e) {
									var n = e.split("|");
									u[n[0]] = [t, n[1] ? Number(n[1]) : 1]
								})
							};
						"string" == typeof s.k ? a("keyword", s.k) : Object.keys(s.k).forEach(function (e) {
							a(e, s.k[e])
						}), s.k = u
					}
					s.lR = n(s.l || /\b[A-Za-z0-9_]+\b/, !0), o && (s.bK && (s.b = "\\b(" + s.bK.split(" ").join("|") + ")\\b"), s.b || (s.b = /\B|\b/), s.bR = n(s.b), s.e || s.eW || (s.e = /\B|\b/), s.e && (s.eR = n(s.e)), s.tE = t(s.e) || "", s.eW && o.tE && (s.tE += (s.e ? "|" : "") + o.tE)), s.i && (s.iR = n(s.i)), void 0 === s.r && (s.r = 1), s.c || (s.c = []);
					var f = [];
					s.c.forEach(function (e) {
						e.v ? e.v.forEach(function (t) {
							f.push(i(e, t))
						}) : f.push("self" == e ? s : e)
					}), s.c = f, s.c.forEach(function (e) {
						r(e, s)
					}), s.starts && r(s.starts, o);
					var l = s.c.map(function (e) {
						return e.bK ? "\\.?(" + e.b + ")\\.?" : e.b
					}).concat([s.tE, s.i]).map(t).filter(Boolean);
					s.t = l.length ? n(l.join("|"), !0) : {
						exec: function () {
							return null
						}
					}
				}
			}
			r(e)
		}

		function a(t, r, i, s) {
			function o(e, t) {
				for (var r = 0; r < t.c.length; r++)
					if (n(t.c[r].bR, e)) return t.c[r]
			}

			function l(e, t) {
				return n(e.eR, t) ? e : e.eW ? l(e.parent, t) : void 0
			}

			function c(e, t) {
				return !i && n(t.iR, e)
			}

			function h(e, t) {
				var n = S.cI ? t[0].toLowerCase() : t[0];
				return e.k.hasOwnProperty(n) && e.k[n]
			}

			function p(e, t, n, r) {
				var i = r ? "" : b.classPrefix,
					s = '<span class="' + i,
					o = n ? "" : "</span>";
				return s += e + '">', s + t + o
			}

			function d() {
				if (!x.k) return e(L);
				var t = "",
					n = 0;
				x.lR.lastIndex = 0;
				for (var r = x.lR.exec(L); r;) {
					t += e(L.substr(n, r.index - n));
					var i = h(x, r);
					i ? (A += i[1], t += p(i[0], e(r[0]))) : t += e(r[0]), n = x.lR.lastIndex, r = x.lR.exec(L)
				}
				return t + e(L.substr(n))
			}

			function v() {
				if (x.sL && !w[x.sL]) return e(L);
				var t = x.sL ? a(x.sL, L, !0, T[x.sL]) : f(L);
				return x.r > 0 && (A += t.r), "continuous" == x.subLanguageMode && (T[x.sL] = t.top), p(t.language, t.value, !1, !0)
			}

			function m() {
				return void 0 !== x.sL ? v() : d()
			}

			function g(t, n) {
				var r = t.cN ? p(t.cN, "", !0) : "";
				t.rB ? (C += r, L = "") : t.eB ? (C += e(n) + r, L = "") : (C += r, L = n), x = Object.create(t, {
					parent: {
						value: x
					}
				})
			}

			function E(t, n) {
				if (L += t, void 0 === n) return C += m(), 0;
				var r = o(n, x);
				if (r) return C += m(), g(r, n), r.rB ? 0 : n.length;
				var i = l(x, n);
				if (i) {
					var s = x;
					s.rE || s.eE || (L += n), C += m();
					do x.cN && (C += "</span>"), A += x.r, x = x.parent; while (x != i.parent);
					return s.eE && (C += e(n)), L = "", i.starts && g(i.starts, ""), s.rE ? 0 : n.length
				}
				if (c(n, x)) throw new Error('Illegal lexeme "' + n + '" for mode "' + (x.cN || "<unnamed>") + '"');
				return L += n, n.length || 1
			}
			var S = y(t);
			if (!S) throw new Error('Unknown language: "' + t + '"');
			u(S);
			for (var x = s || S, T = {}, C = "", k = x; k != S; k = k.parent) k.cN && (C = p(k.cN, "", !0) + C);
			var L = "",
				A = 0;
			try {
				for (var O, M, _ = 0;;) {
					if (x.t.lastIndex = _, O = x.t.exec(r), !O) break;
					M = E(r.substr(_, O.index - _), O[0]), _ = O.index + M
				}
				E(r.substr(_));
				for (var k = x; k.parent; k = k.parent) k.cN && (C += "</span>");
				return {
					r: A,
					value: C,
					language: t,
					top: x
				}
			} catch (D) {
				if (-1 != D.message.indexOf("Illegal")) return {
					r: 0,
					value: e(r)
				};
				throw D
			}
		}

		function f(t, n) {
			n = n || b.languages || Object.keys(w);
			var r = {
					r: 0,
					value: e(t)
				},
				i = r;
			return n.forEach(function (e) {
				if (y(e)) {
					var n = a(e, t, !1);
					n.language = e, n.r > i.r && (i = n), n.r > r.r && (i = r, r = n)
				}
			}), i.language && (r.second_best = i), r
		}

		function l(e) {
			return b.tabReplace && (e = e.replace(/^((<[^>]+>|\t)+)/gm, function (e, t) {
				return t.replace(/\t/g, b.tabReplace)
			})), b.useBR && (e = e.replace(/\n/g, "<br>")), e
		}

		function c(e, t, n) {
			var r = t ? E[t] : n,
				i = [e.trim()];
			return e.match(/(\s|^)hljs(\s|$)/) || i.push("hljs"), r && i.push(r), i.join(" ").trim()
		}

		function h(e) {
			var t = r(e);
			if (!/no(-?)highlight/.test(t)) {
				var n;
				b.useBR ? (n = document.createElementNS("http://www.w3.org/1999/xhtml", "div"), n.innerHTML = e.innerHTML.replace(/\n/g, "").replace(/<br[ \/]*>/g, "\n")) : n = e;
				var i = n.textContent,
					u = t ? a(t, i, !0) : f(i),
					h = s(n);
				if (h.length) {
					var p = document.createElementNS("http://www.w3.org/1999/xhtml", "div");
					p.innerHTML = u.value, u.value = o(h, s(p), i)
				}
				u.value = l(u.value), e.innerHTML = u.value, e.className = c(e.className, t, u.language), e.result = {
					language: u.language,
					re: u.r
				}, u.second_best && (e.second_best = {
					language: u.second_best.language,
					re: u.second_best.r
				})
			}
		}

		function p(e) {
			b = i(b, e)
		}

		function d() {
			if (!d.called) {
				d.called = !0;
				var e = document.querySelectorAll("pre code");
				Array.prototype.forEach.call(e, h)
			}
		}

		function v() {
			addEventListener("DOMContentLoaded", d, !1), addEventListener("load", d, !1)
		}

		function m(e, t) {
			var n = w[e] = t(this);
			n.aliases && n.aliases.forEach(function (t) {
				E[t] = e
			})
		}

		function g() {
			return Object.keys(w)
		}

		function y(e) {
			return w[e] || w[E[e]]
		}
		var b = {
				classPrefix: "hljs-",
				tabReplace: null,
				useBR: !1,
				languages: void 0
			},
			w = {},
			E = {};
		this.highlight = a, this.highlightAuto = f, this.fixMarkup = l, this.highlightBlock = h, this.configure = p, this.initHighlighting = d, this.initHighlightingOnLoad = v, this.registerLanguage = m, this.listLanguages = g, this.getLanguage = y, this.inherit = i, this.IR = "[a-zA-Z][a-zA-Z0-9_]*", this.UIR = "[a-zA-Z_][a-zA-Z0-9_]*", this.NR = "\\b\\d+(\\.\\d+)?", this.CNR = "(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)", this.BNR = "\\b(0b[01]+)", this.RSR = "!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~", this.BE = {
			b: "\\\\[\\s\\S]",
			r: 0
		}, this.ASM = {
			cN: "string",
			b: "'",
			e: "'",
			i: "\\n",
			c: [this.BE]
		}, this.QSM = {
			cN: "string",
			b: '"',
			e: '"',
			i: "\\n",
			c: [this.BE]
		}, this.PWM = {
			b: /\b(a|an|the|are|I|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such)\b/
		}, this.CLCM = {
			cN: "comment",
			b: "//",
			e: "$",
			c: [this.PWM]
		}, this.CBCM = {
			cN: "comment",
			b: "/\\*",
			e: "\\*/",
			c: [this.PWM]
		}, this.HCM = {
			cN: "comment",
			b: "#",
			e: "$",
			c: [this.PWM]
		}, this.NM = {
			cN: "number",
			b: this.NR,
			r: 0
		}, this.CNM = {
			cN: "number",
			b: this.CNR,
			r: 0
		}, this.BNM = {
			cN: "number",
			b: this.BNR,
			r: 0
		}, this.CSSNM = {
			cN: "number",
			b: this.NR + "(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
			r: 0
		}, this.RM = {
			cN: "regexp",
			b: /\//,
			e: /\/[gimuy]*/,
			i: /\n/,
			c: [this.BE, {
				b: /\[/,
				e: /\]/,
				r: 0,
				c: [this.BE]
			}]
		}, this.TM = {
			cN: "title",
			b: this.IR,
			r: 0
		}, this.UTM = {
			cN: "title",
			b: this.UIR,
			r: 0
		}
	};
	hljs.registerLanguage("javascript", function (e) {
		return {
			aliases: ["js"],
			k: {
				keyword: "in if for while finally var new function do return void else break catch instanceof with throw case default try this switch continue typeof delete let yield const class",
				literal: "true false null undefined NaN Infinity",
				built_in: "eval isFinite isNaN parseFloat parseInt decodeURI decodeURIComponent encodeURI encodeURIComponent escape unescape Object Function Boolean Error EvalError InternalError RangeError ReferenceError StopIteration SyntaxError TypeError URIError Number Math Date String RegExp Array Float32Array Float64Array Int16Array Int32Array Int8Array Uint16Array Uint32Array Uint8Array Uint8ClampedArray ArrayBuffer DataView JSON Intl arguments require module console window document"
			},
			c: [{
				cN: "pi",
				b: /^\s*('|")use strict('|")/,
				r: 10
			}, e.ASM, e.QSM, e.CLCM, e.CBCM, e.CNM, {
				b: "(" + e.RSR + "|\\b(case|return|throw)\\b)\\s*",
				k: "return throw case",
				c: [e.CLCM, e.CBCM, e.RM, {
					b: /</,
					e: />;/,
					r: 0,
					sL: "xml"
				}],
				r: 0
			}, {
				cN: "function",
				bK: "function",
				e: /\{/,
				eE: !0,
				c: [e.inherit(e.TM, {
					b: /[A-Za-z$_][0-9A-Za-z$_]*/
				}), {
					cN: "params",
					b: /\(/,
					e: /\)/,
					c: [e.CLCM, e.CBCM],
					i: /["'\(]/
				}],
				i: /\[|%/
			}, {
				b: /\$[(.]/
			}, {
				b: "\\." + e.IR,
				r: 0
			}]
		}
	}), hljs.registerLanguage("sql", function (e) {
		var t = {
			cN: "comment",
			b: "--",
			e: "$"
		};
		return {
			cI: !0,
			i: /[<>]/,
			c: [{
				cN: "operator",
				bK: "begin end start commit rollback savepoint lock alter create drop rename call delete do handler insert load replace select truncate update set show pragma grant merge describe use explain help declare prepare execute deallocate savepoint release unlock purge reset change stop analyze cache flush optimize repair kill install uninstall checksum restore check backup",
				e: /;/,
				eW: !0,
				k: {
					keyword: "abs absolute acos action add adddate addtime aes_decrypt aes_encrypt after aggregate all allocate alter analyze and any are as asc ascii asin assertion at atan atan2 atn2 authorization authors avg backup before begin benchmark between bin binlog bit_and bit_count bit_length bit_or bit_xor both by cache call cascade cascaded case cast catalog ceil ceiling chain change changed char_length character_length charindex charset check checksum checksum_agg choose close coalesce coercibility collate collation collationproperty column columns columns_updated commit compress concat concat_ws concurrent connect connection connection_id consistent constraint constraints continue contributors conv convert convert_tz corresponding cos cot count count_big crc32 create cross cume_dist curdate current current_date current_time current_timestamp current_user cursor curtime data database databases datalength date_add date_format date_sub dateadd datediff datefromparts datename datepart datetime2fromparts datetimeoffsetfromparts day dayname dayofmonth dayofweek dayofyear deallocate declare decode default deferrable deferred degrees delayed delete des_decrypt des_encrypt des_key_file desc describe descriptor diagnostics difference disconnect distinct distinctrow div do domain double drop dumpfile each else elt enclosed encode encrypt end end-exec engine engines eomonth errors escape escaped event eventdata events except exception exec execute exists exp explain export_set extended external extract fast fetch field fields find_in_set first first_value floor flush for force foreign format found found_rows from from_base64 from_days from_unixtime full function get get_format get_lock getdate getutcdate global go goto grant grants greatest group group_concat grouping grouping_id gtid_subset gtid_subtract handler having help hex high_priority hosts hour ident_current ident_incr ident_seed identified identity if ifnull ignore iif ilike immediate in index indicator inet6_aton inet6_ntoa inet_aton inet_ntoa infile initially inner innodb input insert install instr intersect into is is_free_lock is_ipv4 is_ipv4_compat is_ipv4_mapped is_not is_not_null is_used_lock isdate isnull isolation join key kill language last last_day last_insert_id last_value lcase lead leading least leaves left len lenght level like limit lines ln load load_file local localtime localtimestamp locate lock log log10 log2 logfile logs low_priority lower lpad ltrim make_set makedate maketime master master_pos_wait match matched max md5 medium merge microsecond mid min minute mod mode module month monthname mutex name_const names national natural nchar next no no_write_to_binlog not now nullif nvarchar oct octet_length of old_password on only open optimize option optionally or ord order outer outfile output pad parse partial partition password patindex percent_rank percentile_cont percentile_disc period_add period_diff pi plugin position pow power pragma precision prepare preserve primary prior privileges procedure procedure_analyze processlist profile profiles public publishingservername purge quarter query quick quote quotename radians rand read references regexp relative relaylog release release_lock rename repair repeat replace replicate reset restore restrict return returns reverse revoke right rlike rollback rollup round row row_count rows rpad rtrim savepoint schema scroll sec_to_time second section select serializable server session session_user set sha sha1 sha2 share show sign sin size slave sleep smalldatetimefromparts snapshot some soname soundex sounds_like space sql sql_big_result sql_buffer_result sql_cache sql_calc_found_rows sql_no_cache sql_small_result sql_variant_property sqlstate sqrt square start starting status std stddev stddev_pop stddev_samp stdev stdevp stop str str_to_date straight_join strcmp string stuff subdate substr substring subtime subtring_index sum switchoffset sysdate sysdatetime sysdatetimeoffset system_user sysutcdatetime table tables tablespace tan temporary terminated tertiary_weights then time time_format time_to_sec timediff timefromparts timestamp timestampadd timestampdiff timezone_hour timezone_minute to to_base64 to_days to_seconds todatetimeoffset trailing transaction translation trigger trigger_nestlevel triggers trim truncate try_cast try_convert try_parse ucase uncompress uncompressed_length unhex unicode uninstall union unique unix_timestamp unknown unlock update upgrade upped upper usage use user user_resources using utc_date utc_time utc_timestamp uuid uuid_short validate_password_strength value values var var_pop var_samp variables variance varp version view warnings week weekday weekofyear weight_string when whenever where with work write xml xor year yearweek zon",
					literal: "true false null",
					built_in: "array bigint binary bit blob boolean char character date dec decimal float int integer interval number numeric real serial smallint varchar varying int8 serial8 text"
				},
				c: [{
					cN: "string",
					b: "'",
					e: "'",
					c: [e.BE, {
						b: "''"
					}]
				}, {
					cN: "string",
					b: '"',
					e: '"',
					c: [e.BE, {
						b: '""'
					}]
				}, {
					cN: "string",
					b: "`",
					e: "`",
					c: [e.BE]
				}, e.CNM, e.CBCM, t]
			}, e.CBCM, t]
		}
	}), hljs.registerLanguage("python", function (e) {
		var t = {
				cN: "prompt",
				b: /^(>>>|\.\.\.) /
			},
			n = {
				cN: "string",
				c: [e.BE],
				v: [{
					b: /(u|b)?r?'''/,
					e: /'''/,
					c: [t],
					r: 10
				}, {
					b: /(u|b)?r?"""/,
					e: /"""/,
					c: [t],
					r: 10
				}, {
					b: /(u|r|ur)'/,
					e: /'/,
					r: 10
				}, {
					b: /(u|r|ur)"/,
					e: /"/,
					r: 10
				}, {
					b: /(b|br)'/,
					e: /'/
				}, {
					b: /(b|br)"/,
					e: /"/
				}, e.ASM, e.QSM]
			},
			r = {
				cN: "number",
				r: 0,
				v: [{
					b: e.BNR + "[lLjJ]?"
				}, {
					b: "\\b(0o[0-7]+)[lLjJ]?"
				}, {
					b: e.CNR + "[lLjJ]?"
				}]
			},
			i = {
				cN: "params",
				b: /\(/,
				e: /\)/,
				c: ["self", t, r, n]
			},
			s = {
				e: /:/,
				i: /[${=;\n]/,
				c: [e.UTM, i]
			};
		return {
			aliases: ["py", "gyp"],
			k: {
				keyword: "and elif is global as in if from raise for except finally print import pass return exec else break not with class assert yield try while continue del or def lambda nonlocal|10 None True False",
				built_in: "Ellipsis NotImplemented"
			},
			i: /(<\/|->|\?)/,
			c: [t, r, n, e.HCM, e.inherit(s, {
				cN: "function",
				bK: "def",
				r: 10
			}), e.inherit(s, {
				cN: "class",
				bK: "class"
			}), {
				cN: "decorator",
				b: /@/,
				e: /$/
			}, {
				b: /\b(print|exec)\(/
			}]
		}
	}), hljs.registerLanguage("perl", function (e) {
		var t = "getpwent getservent quotemeta msgrcv scalar kill dbmclose undef lc ma syswrite tr send umask sysopen shmwrite vec qx utime local oct semctl localtime readpipe do return format read sprintf dbmopen pop getpgrp not getpwnam rewinddir qqfileno qw endprotoent wait sethostent bless s|0 opendir continue each sleep endgrent shutdown dump chomp connect getsockname die socketpair close flock exists index shmgetsub for endpwent redo lstat msgctl setpgrp abs exit select print ref gethostbyaddr unshift fcntl syscall goto getnetbyaddr join gmtime symlink semget splice x|0 getpeername recv log setsockopt cos last reverse gethostbyname getgrnam study formline endhostent times chop length gethostent getnetent pack getprotoent getservbyname rand mkdir pos chmod y|0 substr endnetent printf next open msgsnd readdir use unlink getsockopt getpriority rindex wantarray hex system getservbyport endservent int chr untie rmdir prototype tell listen fork shmread ucfirst setprotoent else sysseek link getgrgid shmctl waitpid unpack getnetbyname reset chdir grep split require caller lcfirst until warn while values shift telldir getpwuid my getprotobynumber delete and sort uc defined srand accept package seekdir getprotobyname semop our rename seek if q|0 chroot sysread setpwent no crypt getc chown sqrt write setnetent setpriority foreach tie sin msgget map stat getlogin unless elsif truncate exec keys glob tied closedirioctl socket readlink eval xor readline binmode setservent eof ord bind alarm pipe atan2 getgrent exp time push setgrent gt lt or ne m|0 break given say state when",
			n = {
				cN: "subst",
				b: "[$@]\\{",
				e: "\\}",
				k: t
			},
			r = {
				b: "->{",
				e: "}"
			},
			i = {
				cN: "variable",
				v: [{
					b: /\$\d/
				}, {
					b: /[\$\%\@](\^\w\b|#\w+(\:\:\w+)*|{\w+}|\w+(\:\:\w*)*)/
				}, {
					b: /[\$\%\@][^\s\w{]/,
					r: 0
				}]
			},
			s = {
				cN: "comment",
				b: "^(__END__|__DATA__)",
				e: "\\n$",
				r: 5
			},
			o = [e.BE, n, i],
			u = [i, e.HCM, s, {
				cN: "comment",
				b: "^\\=\\w",
				e: "\\=cut",
				eW: !0
			}, r, {
				cN: "string",
				c: o,
				v: [{
					b: "q[qwxr]?\\s*\\(",
					e: "\\)",
					r: 5
				}, {
					b: "q[qwxr]?\\s*\\[",
					e: "\\]",
					r: 5
				}, {
					b: "q[qwxr]?\\s*\\{",
					e: "\\}",
					r: 5
				}, {
					b: "q[qwxr]?\\s*\\|",
					e: "\\|",
					r: 5
				}, {
					b: "q[qwxr]?\\s*\\<",
					e: "\\>",
					r: 5
				}, {
					b: "qw\\s+q",
					e: "q",
					r: 5
				}, {
					b: "'",
					e: "'",
					c: [e.BE]
				}, {
					b: '"',
					e: '"'
				}, {
					b: "`",
					e: "`",
					c: [e.BE]
				}, {
					b: "{\\w+}",
					c: [],
					r: 0
				}, {
					b: "-?\\w+\\s*\\=\\>",
					c: [],
					r: 0
				}]
			}, {
				cN: "number",
				b: "(\\b0[0-7_]+)|(\\b0x[0-9a-fA-F_]+)|(\\b[1-9][0-9_]*(\\.[0-9_]+)?)|[0_]\\b",
				r: 0
			}, {
				b: "(\\/\\/|" + e.RSR + "|\\b(split|return|print|reverse|grep)\\b)\\s*",
				k: "split return print reverse grep",
				r: 0,
				c: [e.HCM, s, {
					cN: "regexp",
					b: "(s|tr|y)/(\\\\.|[^/])*/(\\\\.|[^/])*/[a-z]*",
					r: 10
				}, {
					cN: "regexp",
					b: "(m|qr)?/",
					e: "/[a-z]*",
					c: [e.BE],
					r: 0
				}]
			}, {
				cN: "sub",
				bK: "sub",
				e: "(\\s*\\(.*?\\))?[;{]",
				r: 5
			}, {
				cN: "operator",
				b: "-\\w\\b",
				r: 0
			}];
		return n.c = u, r.c = u, {
			aliases: ["pl"],
			k: t,
			c: u
		}
	}), hljs.registerLanguage("apache", function (e) {
		var t = {
			cN: "number",
			b: "[\\$%]\\d+"
		};
		return {
			aliases: ["apacheconf"],
			cI: !0,
			c: [e.HCM, {
				cN: "tag",
				b: "</?",
				e: ">"
			}, {
				cN: "keyword",
				b: /\w+/,
				r: 0,
				k: {
					common: "order deny allow setenv rewriterule rewriteengine rewritecond documentroot sethandler errordocument loadmodule options header listen serverroot servername"
				},
				starts: {
					e: /$/,
					r: 0,
					k: {
						literal: "on off all"
					},
					c: [{
						cN: "sqbracket",
						b: "\\s\\[",
						e: "\\]$"
					}, {
						cN: "cbracket",
						b: "[\\$%]\\{",
						e: "\\}",
						c: ["self", t]
					}, t, e.QSM]
				}
			}],
			i: /\S/
		}
	}), hljs.registerLanguage("xml", function () {
		var e = "[A-Za-z0-9\\._:-]+",
			t = {
				b: /<\?(php)?(?!\w)/,
				e: /\?>/,
				sL: "php",
				subLanguageMode: "continuous"
			},
			n = {
				eW: !0,
				i: /</,
				r: 0,
				c: [t, {
					cN: "attribute",
					b: e,
					r: 0
				}, {
					b: "=",
					r: 0,
					c: [{
						cN: "value",
						c: [t],
						v: [{
							b: /"/,
							e: /"/
						}, {
							b: /'/,
							e: /'/
						}, {
							b: /[^\s\/>]+/
						}]
					}]
				}]
			};
		return {
			aliases: ["html", "xhtml", "rss", "atom", "xsl", "plist"],
			cI: !0,
			c: [{
				cN: "doctype",
				b: "<!DOCTYPE",
				e: ">",
				r: 10,
				c: [{
					b: "\\[",
					e: "\\]"
				}]
			}, {
				cN: "comment",
				b: "<!--",
				e: "-->",
				r: 10
			}, {
				cN: "cdata",
				b: "<\\!\\[CDATA\\[",
				e: "\\]\\]>",
				r: 10
			}, {
				cN: "tag",
				b: "<style(?=\\s|>|$)",
				e: ">",
				k: {
					title: "style"
				},
				c: [n],
				starts: {
					e: "</style>",
					rE: !0,
					sL: "css"
				}
			}, {
				cN: "tag",
				b: "<script(?=\\s|>|$)",
				e: ">",
				k: {
					title: "script"
				},
				c: [n],
				starts: {
					e: "</script>",
					rE: !0,
					sL: "javascript"
				}
			}, t, {
				cN: "pi",
				b: /<\?\w+/,
				e: /\?>/,
				r: 10
			}, {
				cN: "tag",
				b: "</?",
				e: "/?>",
				c: [{
					cN: "title",
					b: /[^ \/><\n\t]+/,
					r: 0
				}, n]
			}]
		}
	}), hljs.registerLanguage("css", function (e) {
		var t = "[a-zA-Z-][a-zA-Z0-9_-]*",
			n = {
				cN: "function",
				b: t + "\\(",
				rB: !0,
				eE: !0,
				e: "\\("
			};
		return {
			cI: !0,
			i: "[=/|']",
			c: [e.CBCM, {
				cN: "id",
				b: "\\#[A-Za-z0-9_-]+"
			}, {
				cN: "class",
				b: "\\.[A-Za-z0-9_-]+",
				r: 0
			}, {
				cN: "attr_selector",
				b: "\\[",
				e: "\\]",
				i: "$"
			}, {
				cN: "pseudo",
				b: ":(:)?[a-zA-Z0-9\\_\\-\\+\\(\\)\\\"\\']+"
			}, {
				cN: "at_rule",
				b: "@(font-face|page)",
				l: "[a-z-]+",
				k: "font-face page"
			}, {
				cN: "at_rule",
				b: "@",
				e: "[{;]",
				c: [{
					cN: "keyword",
					b: /\S+/
				}, {
					b: /\s/,
					eW: !0,
					eE: !0,
					r: 0,
					c: [n, e.ASM, e.QSM, e.CSSNM]
				}]
			}, {
				cN: "tag",
				b: t,
				r: 0
			}, {
				cN: "rules",
				b: "{",
				e: "}",
				i: "[^\\s]",
				r: 0,
				c: [e.CBCM, {
					cN: "rule",
					b: "[^\\s]",
					rB: !0,
					e: ";",
					eW: !0,
					c: [{
						cN: "attribute",
						b: "[A-Z\\_\\.\\-]+",
						e: ":",
						eE: !0,
						i: "[^\\s]",
						starts: {
							cN: "value",
							eW: !0,
							eE: !0,
							c: [n, e.CSSNM, e.QSM, e.ASM, e.CBCM, {
								cN: "hexcolor",
								b: "#[0-9A-Fa-f]+"
							}, {
								cN: "important",
								b: "!important"
							}]
						}
					}]
				}]
			}]
		}
	}), hljs.registerLanguage("cpp", function (e) {
		var t = {
			keyword: "false int float while private char catch export virtual operator sizeof dynamic_cast|10 typedef const_cast|10 const struct for static_cast|10 union namespace unsigned long throw volatile static protected bool template mutable if public friend do return goto auto void enum else break new extern using true class asm case typeid short reinterpret_cast|10 default double register explicit signed typename try this switch continue wchar_t inline delete alignof char16_t char32_t constexpr decltype noexcept nullptr static_assert thread_local restrict _Bool complex _Complex _Imaginary",
			built_in: "std string cin cout cerr clog stringstream istringstream ostringstream auto_ptr deque list queue stack vector map set bitset multiset multimap unordered_set unordered_map unordered_multiset unordered_multimap array shared_ptr abort abs acos asin atan2 atan calloc ceil cosh cos exit exp fabs floor fmod fprintf fputs free frexp fscanf isalnum isalpha iscntrl isdigit isgraph islower isprint ispunct isspace isupper isxdigit tolower toupper labs ldexp log10 log malloc memchr memcmp memcpy memset modf pow printf putchar puts scanf sinh sin snprintf sprintf sqrt sscanf strcat strchr strcmp strcpy strcspn strlen strncat strncmp strncpy strpbrk strrchr strspn strstr tanh tan vfprintf vprintf vsprintf"
		};
		return {
			aliases: ["c", "h", "c++", "h++"],
			k: t,
			i: "</",
			c: [e.CLCM, e.CBCM, e.QSM, {
				cN: "string",
				b: "'\\\\?.",
				e: "'",
				i: "."
			}, {
				cN: "number",
				b: "\\b(\\d+(\\.\\d*)?|\\.\\d+)(u|U|l|L|ul|UL|f|F)"
			}, e.CNM, {
				cN: "preprocessor",
				b: "#",
				e: "$",
				k: "if else elif endif define undef warning error line pragma",
				c: [{
					b: 'include\\s*[<"]',
					e: '[>"]',
					k: "include",
					i: "\\n"
				}, e.CLCM]
			}, {
				cN: "stl_container",
				b: "\\b(deque|list|queue|stack|vector|map|set|bitset|multiset|multimap|unordered_map|unordered_set|unordered_multiset|unordered_multimap|array)\\s*<",
				e: ">",
				k: t,
				c: ["self"]
			}, {
				b: e.IR + "::"
			}]
		}
	}), hljs.registerLanguage("json", function (e) {
		var t = {
				literal: "true false null"
			},
			n = [e.QSM, e.CNM],
			r = {
				cN: "value",
				e: ",",
				eW: !0,
				eE: !0,
				c: n,
				k: t
			},
			i = {
				b: "{",
				e: "}",
				c: [{
					cN: "attribute",
					b: '\\s*"',
					e: '"\\s*:\\s*',
					eB: !0,
					eE: !0,
					c: [e.BE],
					i: "\\n",
					starts: r
				}],
				i: "\\S"
			},
			s = {
				b: "\\[",
				e: "\\]",
				c: [e.inherit(r, {
					cN: null
				})],
				i: "\\S"
			};
		return n.splice(n.length, 0, i, s), {
			c: n,
			k: t,
			i: "\\S"
		}
	}), hljs.registerLanguage("coffeescript", function (e) {
		var t = {
				keyword: "in if for while finally new do return else break catch instanceof throw try this switch continue typeof delete debugger super then unless until loop of by when and or is isnt not",
				literal: "true false null undefined yes no on off",
				reserved: "case default function var void with const let enum export import native __hasProp __extends __slice __bind __indexOf",
				built_in: "npm require console print module global window document"
			},
			n = "[A-Za-z$_][0-9A-Za-z$_]*",
			r = {
				cN: "subst",
				b: /#\{/,
				e: /}/,
				k: t
			},
			i = [e.BNM, e.inherit(e.CNM, {
				starts: {
					e: "(\\s*/)?",
					r: 0
				}
			}), {
				cN: "string",
				v: [{
					b: /'''/,
					e: /'''/,
					c: [e.BE]
				}, {
					b: /'/,
					e: /'/,
					c: [e.BE]
				}, {
					b: /"""/,
					e: /"""/,
					c: [e.BE, r]
				}, {
					b: /"/,
					e: /"/,
					c: [e.BE, r]
				}]
			}, {
				cN: "regexp",
				v: [{
					b: "///",
					e: "///",
					c: [r, e.HCM]
				}, {
					b: "//[gim]*",
					r: 0
				}, {
					b: /\/(?![ *])(\\\/|.)*?\/[gim]*(?=\W|$)/
				}]
			}, {
				cN: "property",
				b: "@" + n
			}, {
				b: "`",
				e: "`",
				eB: !0,
				eE: !0,
				sL: "javascript"
			}];
		r.c = i;
		var s = e.inherit(e.TM, {
				b: n
			}),
			o = "(\\(.*\\))?\\s*\\B[-=]>",
			u = {
				cN: "params",
				b: "\\([^\\(]",
				rB: !0,
				c: [{
					b: /\(/,
					e: /\)/,
					k: t,
					c: ["self"].concat(i)
				}]
			};
		return {
			aliases: ["coffee", "cson", "iced"],
			k: t,
			i: /\/\*/,
			c: i.concat([{
				cN: "comment",
				b: "###",
				e: "###",
				c: [e.PWM]
			}, e.HCM, {
				cN: "function",
				b: "^\\s*" + n + "\\s*=\\s*" + o,
				e: "[-=]>",
				rB: !0,
				c: [s, u]
			}, {
				b: /[:\(,=]\s*/,
				r: 0,
				c: [{
					cN: "function",
					b: o,
					e: "[-=]>",
					rB: !0,
					c: [u]
				}]
			}, {
				cN: "class",
				bK: "class",
				e: "$",
				i: /[:="\[\]]/,
				c: [{
					bK: "extends",
					eW: !0,
					i: /[:="\[\]]/,
					c: [s]
				}, s]
			}, {
				cN: "attribute",
				b: n + ":",
				e: ":",
				rB: !0,
				rE: !0,
				r: 0
			}])
		}
	}), hljs.registerLanguage("php", function (e) {
		var t = {
				cN: "variable",
				b: "\\$+[a-zA-Z_-ÿ][a-zA-Z0-9_-ÿ]*"
			},
			n = {
				cN: "preprocessor",
				b: /<\?(php)?|\?>/
			},
			r = {
				cN: "string",
				c: [e.BE, n],
				v: [{
					b: 'b"',
					e: '"'
				}, {
					b: "b'",
					e: "'"
				}, e.inherit(e.ASM, {
					i: null
				}), e.inherit(e.QSM, {
					i: null
				})]
			},
			i = {
				v: [e.BNM, e.CNM]
			};
		return {
			aliases: ["php3", "php4", "php5", "php6"],
			cI: !0,
			k: "and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception default die require __FUNCTION__ enddeclare final try switch continue endfor endif declare unset true false trait goto instanceof insteadof __DIR__ __NAMESPACE__ yield finally",
			c: [e.CLCM, e.HCM, {
				cN: "comment",
				b: "/\\*",
				e: "\\*/",
				c: [{
					cN: "phpdoc",
					b: "\\s@[A-Za-z]+"
				}, n]
			}, {
				cN: "comment",
				b: "__halt_compiler.+?;",
				eW: !0,
				k: "__halt_compiler",
				l: e.UIR
			}, {
				cN: "string",
				b: "<<<['\"]?\\w+['\"]?$",
				e: "^\\w+;",
				c: [e.BE]
			}, n, t, {
				b: /->+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/
			}, {
				cN: "function",
				bK: "function",
				e: /[;{]/,
				eE: !0,
				i: "\\$|\\[|%",
				c: [e.UTM, {
					cN: "params",
					b: "\\(",
					e: "\\)",
					c: ["self", t, e.CBCM, r, i]
				}]
			}, {
				cN: "class",
				bK: "class interface",
				e: "{",
				eE: !0,
				i: /[:\(\$"]/,
				c: [{
					bK: "extends implements"
				}, e.UTM]
			}, {
				bK: "namespace",
				e: ";",
				i: /[\.']/,
				c: [e.UTM]
			}, {
				bK: "use",
				e: ";",
				c: [e.UTM]
			}, {
				b: "=>"
			}, r, i]
		}
	}), hljs.registerLanguage("nginx", function (e) {
		var t = {
				cN: "variable",
				v: [{
					b: /\$\d+/
				}, {
					b: /\$\{/,
					e: /}/
				}, {
					b: "[\\$\\@]" + e.UIR
				}]
			},
			n = {
				eW: !0,
				l: "[a-z/_]+",
				k: {
					built_in: "on off yes no true false none blocked debug info notice warn error crit select break last permanent redirect kqueue rtsig epoll poll /dev/poll"
				},
				r: 0,
				i: "=>",
				c: [e.HCM, {
					cN: "string",
					c: [e.BE, t],
					v: [{
						b: /"/,
						e: /"/
					}, {
						b: /'/,
						e: /'/
					}]
				}, {
					cN: "url",
					b: "([a-z]+):/",
					e: "\\s",
					eW: !0,
					eE: !0,
					c: [t]
				}, {
					cN: "regexp",
					c: [e.BE, t],
					v: [{
						b: "\\s\\^",
						e: "\\s|{|;",
						rE: !0
					}, {
						b: "~\\*?\\s+",
						e: "\\s|{|;",
						rE: !0
					}, {
						b: "\\*(\\.[a-z\\-]+)+"
					}, {
						b: "([a-z\\-]+\\.)+\\*"
					}]
				}, {
					cN: "number",
					b: "\\b\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}(:\\d{1,5})?\\b"
				}, {
					cN: "number",
					b: "\\b\\d+[kKmMgGdshdwy]*\\b",
					r: 0
				}, t]
			};
		return {
			aliases: ["nginxconf"],
			c: [e.HCM, {
				b: e.UIR + "\\s",
				e: ";|{",
				rB: !0,
				c: [{
					cN: "title",
					b: e.UIR,
					starts: n
				}],
				r: 0
			}],
			i: "[^\\s\\}]"
		}
	}), hljs.registerLanguage("diff", function () {
		return {
			aliases: ["patch"],
			c: [{
				cN: "chunk",
				r: 10,
				v: [{
					b: /^\@\@ +\-\d+,\d+ +\+\d+,\d+ +\@\@$/
				}, {
					b: /^\*\*\* +\d+,\d+ +\*\*\*\*$/
				}, {
					b: /^\-\-\- +\d+,\d+ +\-\-\-\-$/
				}]
			}, {
				cN: "header",
				v: [{
					b: /Index: /,
					e: /$/
				}, {
					b: /=====/,
					e: /=====$/
				}, {
					b: /^\-\-\-/,
					e: /$/
				}, {
					b: /^\*{3} /,
					e: /$/
				}, {
					b: /^\+\+\+/,
					e: /$/
				}, {
					b: /\*{5}/,
					e: /\*{5}$/
				}]
			}, {
				cN: "addition",
				b: "^\\+",
				e: "$"
			}, {
				cN: "deletion",
				b: "^\\-",
				e: "$"
			}, {
				cN: "change",
				b: "^\\!",
				e: "$"
			}]
		}
	}), hljs.registerLanguage("objectivec", function (e) {
		var t = {
				keyword: "int float while char export sizeof typedef const struct for union unsigned long volatile static bool mutable if do return goto void enum else break extern asm case short default double register explicit signed typename this switch continue wchar_t inline readonly assign readwrite self @synchronized id typeof nonatomic super unichar IBOutlet IBAction strong weak copy in out inout bycopy byref oneway __strong __weak __block __autoreleasing @private @protected @public @try @property @end @throw @catch @finally @autoreleasepool @synthesize @dynamic @selector @optional @required",
				literal: "false true FALSE TRUE nil YES NO NULL",
				built_in: "NSString NSData NSDictionary CGRect CGPoint UIButton UILabel UITextView UIWebView MKMapView NSView NSViewController NSWindow NSWindowController NSSet NSUUID NSIndexSet UISegmentedControl NSObject UITableViewDelegate UITableViewDataSource NSThread UIActivityIndicator UITabbar UIToolBar UIBarButtonItem UIImageView NSAutoreleasePool UITableView BOOL NSInteger CGFloat NSException NSLog NSMutableString NSMutableArray NSMutableDictionary NSURL NSIndexPath CGSize UITableViewCell UIView UIViewController UINavigationBar UINavigationController UITabBarController UIPopoverController UIPopoverControllerDelegate UIImage NSNumber UISearchBar NSFetchedResultsController NSFetchedResultsChangeType UIScrollView UIScrollViewDelegate UIEdgeInsets UIColor UIFont UIApplication NSNotFound NSNotificationCenter NSNotification UILocalNotification NSBundle NSFileManager NSTimeInterval NSDate NSCalendar NSUserDefaults UIWindow NSRange NSArray NSError NSURLRequest NSURLConnection NSURLSession NSURLSessionDataTask NSURLSessionDownloadTask NSURLSessionUploadTask NSURLResponseUIInterfaceOrientation MPMoviePlayerController dispatch_once_t dispatch_queue_t dispatch_sync dispatch_async dispatch_once"
			},
			n = /[a-zA-Z@][a-zA-Z0-9_]*/,
			r = "@interface @class @protocol @implementation";
		return {
			aliases: ["m", "mm", "objc", "obj-c"],
			k: t,
			l: n,
			i: "</",
			c: [e.CLCM, e.CBCM, e.CNM, e.QSM, {
				cN: "string",
				v: [{
					b: '@"',
					e: '"',
					i: "\\n",
					c: [e.BE]
				}, {
					b: "'",
					e: "[^\\\\]'",
					i: "[^\\\\][^']"
				}]
			}, {
				cN: "preprocessor",
				b: "#",
				e: "$",
				c: [{
					cN: "title",
					v: [{
						b: '"',
						e: '"'
					}, {
						b: "<",
						e: ">"
					}]
				}]
			}, {
				cN: "class",
				b: "(" + r.split(" ").join("|") + ")\\b",
				e: "({|$)",
				eE: !0,
				k: r,
				l: n,
				c: [e.UTM]
			}, {
				cN: "variable",
				b: "\\." + e.UIR,
				r: 0
			}]
		}
	}), hljs.registerLanguage("makefile", function (e) {
		var t = {
			cN: "variable",
			b: /\$\(/,
			e: /\)/,
			c: [e.BE]
		};
		return {
			aliases: ["mk", "mak"],
			c: [e.HCM, {
				b: /^\w+\s*\W*=/,
				rB: !0,
				r: 0,
				starts: {
					cN: "constant",
					e: /\s*\W*=/,
					eE: !0,
					starts: {
						e: /$/,
						r: 0,
						c: [t]
					}
				}
			}, {
				cN: "title",
				b: /^[\w]+:\s*$/
			}, {
				cN: "phony",
				b: /^\.PHONY:/,
				e: /$/,
				k: ".PHONY",
				l: /[\.\w]+/
			}, {
				b: /^\t+/,
				e: /$/,
				r: 0,
				c: [e.QSM, t]
			}]
		}
	}), hljs.registerLanguage("ruby", function (e) {
		var t = "[a-zA-Z_]\\w*[!?=]?|[-+~]\\@|<<|>>|=~|===?|<=>|[<>]=?|\\*\\*|[-/+%^&*~`|]|\\[\\]=?",
			n = "and false then defined module in return redo if BEGIN retry end for true self when next until do begin unless END rescue nil else break undef not super class case require yield alias while ensure elsif or include attr_reader attr_writer attr_accessor",
			r = {
				cN: "yardoctag",
				b: "@[A-Za-z]+"
			},
			i = {
				cN: "value",
				b: "#<",
				e: ">"
			},
			s = {
				cN: "comment",
				v: [{
					b: "#",
					e: "$",
					c: [r]
				}, {
					b: "^\\=begin",
					e: "^\\=end",
					c: [r],
					r: 10
				}, {
					b: "^__END__",
					e: "\\n$"
				}]
			},
			o = {
				cN: "subst",
				b: "#\\{",
				e: "}",
				k: n
			},
			u = {
				cN: "string",
				c: [e.BE, o],
				v: [{
					b: /'/,
					e: /'/
				}, {
					b: /"/,
					e: /"/
				}, {
					b: /`/,
					e: /`/
				}, {
					b: "%[qQwWx]?\\(",
					e: "\\)"
				}, {
					b: "%[qQwWx]?\\[",
					e: "\\]"
				}, {
					b: "%[qQwWx]?{",
					e: "}"
				}, {
					b: "%[qQwWx]?<",
					e: ">"
				}, {
					b: "%[qQwWx]?/",
					e: "/"
				}, {
					b: "%[qQwWx]?%",
					e: "%"
				}, {
					b: "%[qQwWx]?-",
					e: "-"
				}, {
					b: "%[qQwWx]?\\|",
					e: "\\|"
				}, {
					b: /\B\?(\\\d{1,3}|\\x[A-Fa-f0-9]{1,2}|\\u[A-Fa-f0-9]{4}|\\?\S)\b/
				}]
			},
			a = {
				cN: "params",
				b: "\\(",
				e: "\\)",
				k: n
			},
			f = [u, i, s, {
				cN: "class",
				bK: "class module",
				e: "$|;",
				i: /=/,
				c: [e.inherit(e.TM, {
					b: "[A-Za-z_]\\w*(::\\w+)*(\\?|\\!)?"
				}), {
					cN: "inheritance",
					b: "<\\s*",
					c: [{
						cN: "parent",
						b: "(" + e.IR + "::)?" + e.IR
					}]
				}, s]
			}, {
				cN: "function",
				bK: "def",
				e: " |$|;",
				r: 0,
				c: [e.inherit(e.TM, {
					b: t
				}), a, s]
			}, {
				cN: "constant",
				b: "(::)?(\\b[A-Z]\\w*(::)?)+",
				r: 0
			}, {
				cN: "symbol",
				b: e.UIR + "(\\!|\\?)?:",
				r: 0
			}, {
				cN: "symbol",
				b: ":",
				c: [u, {
					b: t
				}],
				r: 0
			}, {
				cN: "number",
				b: "(\\b0[0-7_]+)|(\\b0x[0-9a-fA-F_]+)|(\\b[1-9][0-9_]*(\\.[0-9_]+)?)|[0_]\\b",
				r: 0
			}, {
				cN: "variable",
				b: "(\\$\\W)|((\\$|\\@\\@?)(\\w+))"
			}, {
				b: "(" + e.RSR + ")\\s*",
				c: [i, s, {
					cN: "regexp",
					c: [e.BE, o],
					i: /\n/,
					v: [{
						b: "/",
						e: "/[a-z]*"
					}, {
						b: "%r{",
						e: "}[a-z]*"
					}, {
						b: "%r\\(",
						e: "\\)[a-z]*"
					}, {
						b: "%r!",
						e: "![a-z]*"
					}, {
						b: "%r\\[",
						e: "\\][a-z]*"
					}]
				}],
				r: 0
			}];
		o.c = f, a.c = f;
		var l = [{
			b: /^\s*=>/,
			cN: "status",
			starts: {
				e: "$",
				c: f
			}
		}, {
			cN: "prompt",
			b: /^\S[^=>\n]*>+/,
			starts: {
				e: "$",
				c: f
			}
		}];
		return {
			aliases: ["rb", "gemspec", "podspec", "thor", "irb"],
			k: n,
			c: [s].concat(l).concat(f)
		}
	}), hljs.registerLanguage("bash", function (e) {
		var t = {
				cN: "variable",
				v: [{
					b: /\$[\w\d#@][\w\d_]*/
				}, {
					b: /\$\{(.*?)\}/
				}]
			},
			n = {
				cN: "string",
				b: /"/,
				e: /"/,
				c: [e.BE, t, {
					cN: "variable",
					b: /\$\(/,
					e: /\)/,
					c: [e.BE]
				}]
			},
			r = {
				cN: "string",
				b: /'/,
				e: /'/
			};
		return {
			aliases: ["sh", "zsh"],
			l: /-?[a-z\.]+/,
			k: {
				keyword: "if then else elif fi for break continue while in do done exit return set declare case esac export exec function",
				literal: "true false",
				built_in: "printf echo read cd pwd pushd popd dirs let eval unset typeset readonly getopts source shopt caller type hash bind help sudo",
				operator: "-ne -eq -lt -gt -f -d -e -s -l -a"
			},
			c: [{
				cN: "shebang",
				b: /^#![^\n]+sh\s*$/,
				r: 10
			}, {
				cN: "function",
				b: /\w[\w\d_]*\s*\(\s*\)\s*\{/,
				rB: !0,
				c: [e.inherit(e.TM, {
					b: /\w[\w\d_]*/
				})],
				r: 0
			}, e.HCM, e.NM, n, r, t]
		}
	}), hljs.registerLanguage("cs", function (e) {
		var t = "abstract as base bool break byte case catch char checked const continue decimal default delegate do double else enum event explicit extern false finally fixed float for foreach goto if implicit in int interface internal is lock long new null object operator out override params private protected public readonly ref return sbyte sealed short sizeof stackalloc static string struct switch this throw true try typeof uint ulong unchecked unsafe ushort using virtual volatile void while async await protected public private internal ascending descending from get group into join let orderby partial select set value var where yield",
			n = e.IR + "(<" + e.IR + ">)?";
		return {
			aliases: ["csharp"],
			k: t,
			i: /::/,
			c: [{
				cN: "comment",
				b: "///",
				e: "$",
				rB: !0,
				c: [{
					cN: "xmlDocTag",
					v: [{
						b: "///",
						r: 0
					}, {
						b: "<!--|-->"
					}, {
						b: "</?",
						e: ">"
					}]
				}]
			}, e.CLCM, e.CBCM, {
				cN: "preprocessor",
				b: "#",
				e: "$",
				k: "if else elif endif define undef warning error line region endregion pragma checksum"
			}, {
				cN: "string",
				b: '@"',
				e: '"',
				c: [{
					b: '""'
				}]
			}, e.ASM, e.QSM, e.CNM, {
				bK: "class namespace interface",
				e: /[{;=]/,
				i: /[^\s:]/,
				c: [e.TM, e.CLCM, e.CBCM]
			}, {
				bK: "new",
				e: /\s/,
				r: 0
			}, {
				cN: "function",
				b: "(" + n + "\\s+)+" + e.IR + "\\s*\\(",
				rB: !0,
				e: /[{;=]/,
				eE: !0,
				k: t,
				c: [{
					b: e.IR + "\\s*\\(",
					rB: !0,
					c: [e.TM]
				}, {
					cN: "params",
					b: /\(/,
					e: /\)/,
					k: t,
					c: [e.ASM, e.QSM, e.CNM, e.CBCM]
				}, e.CLCM, e.CBCM]
			}]
		}
	}), hljs.registerLanguage("markdown", function () {
		return {
			aliases: ["md", "mkdown", "mkd"],
			c: [{
				cN: "header",
				v: [{
					b: "^#{1,6}",
					e: "$"
				}, {
					b: "^.+?\\n[=-]{2,}$"
				}]
			}, {
				b: "<",
				e: ">",
				sL: "xml",
				r: 0
			}, {
				cN: "bullet",
				b: "^([*+-]|(\\d+\\.))\\s+"
			}, {
				cN: "strong",
				b: "[*_]{2}.+?[*_]{2}"
			}, {
				cN: "emphasis",
				v: [{
					b: "\\*.+?\\*"
				}, {
					b: "_.+?_",
					r: 0
				}]
			}, {
				cN: "blockquote",
				b: "^>\\s+",
				e: "$"
			}, {
				cN: "code",
				v: [{
					b: "`.+?`"
				}, {
					b: "^( {4}|	)",
					e: "$",
					r: 0
				}]
			}, {
				cN: "horizontal_rule",
				b: "^[-\\*]{3,}",
				e: "$"
			}, {
				b: "\\[.+?\\][\\(\\[].*?[\\)\\]]",
				rB: !0,
				c: [{
					cN: "link_label",
					b: "\\[",
					e: "\\]",
					eB: !0,
					rE: !0,
					r: 0
				}, {
					cN: "link_url",
					b: "\\]\\(",
					e: "\\)",
					eB: !0,
					eE: !0
				}, {
					cN: "link_reference",
					b: "\\]\\[",
					e: "\\]",
					eB: !0,
					eE: !0
				}],
				r: 10
			}, {
				b: "^\\[.+\\]:",
				rB: !0,
				c: [{
					cN: "link_reference",
					b: "\\[",
					e: "\\]:",
					eB: !0,
					eE: !0,
					starts: {
						cN: "link_url",
						e: "$"
					}
				}]
			}]
		}
	}), hljs.registerLanguage("ini", function (e) {
		return {
			cI: !0,
			i: /\S/,
			c: [{
				cN: "comment",
				b: ";",
				e: "$"
			}, {
				cN: "title",
				b: "^\\[",
				e: "\\]"
			}, {
				cN: "setting",
				b: "^[a-z0-9\\[\\]_-]+[ \\t]*=[ \\t]*",
				e: "$",
				c: [{
					cN: "value",
					eW: !0,
					k: "on off true false yes no",
					c: [e.QSM, e.NM],
					r: 0
				}]
			}]
		}
	}), hljs.registerLanguage("http", function () {
		return {
			i: "\\S",
			c: [{
				cN: "status",
				b: "^HTTP/[0-9\\.]+",
				e: "$",
				c: [{
					cN: "number",
					b: "\\b\\d{3}\\b"
				}]
			}, {
				cN: "request",
				b: "^[A-Z]+ (.*?) HTTP/[0-9\\.]+$",
				rB: !0,
				e: "$",
				c: [{
					cN: "string",
					b: " ",
					e: " ",
					eB: !0,
					eE: !0
				}]
			}, {
				cN: "attribute",
				b: "^\\w",
				e: ": ",
				eE: !0,
				i: "\\n|\\s|=",
				starts: {
					cN: "string",
					e: "$"
				}
			}, {
				b: "\\n\\n",
				starts: {
					sL: "",
					eW: !0
				}
			}]
		}
	}), hljs.registerLanguage("java", function (e) {
		var t = e.UIR + "(<" + e.UIR + ">)?",
			n = "false synchronized int abstract float private char boolean static null if const for true while long throw strictfp finally protected import native final return void enum else break transient new catch instanceof byte super volatile case assert short package default double public try this switch continue throws protected public private";
		return {
			aliases: ["jsp"],
			k: n,
			i: /<\//,
			c: [{
				cN: "javadoc",
				b: "/\\*\\*",
				e: "\\*/",
				r: 0,
				c: [{
					cN: "javadoctag",
					b: "(^|\\s)@[A-Za-z]+"
				}]
			}, e.CLCM, e.CBCM, e.ASM, e.QSM, {
				cN: "class",
				bK: "class interface",
				e: /[{;=]/,
				eE: !0,
				k: "class interface",
				i: /[:"\[\]]/,
				c: [{
					bK: "extends implements"
				}, e.UTM]
			}, {
				bK: "new throw",
				e: /\s/,
				r: 0
			}, {
				cN: "function",
				b: "(" + t + "\\s+)+" + e.UIR + "\\s*\\(",
				rB: !0,
				e: /[{;=]/,
				eE: !0,
				k: n,
				c: [{
					b: e.UIR + "\\s*\\(",
					rB: !0,
					c: [e.UTM]
				}, {
					cN: "params",
					b: /\(/,
					e: /\)/,
					k: n,
					c: [e.ASM, e.QSM, e.CNM, e.CBCM]
				}, e.CLCM, e.CBCM]
			}, e.CNM, {
				cN: "annotation",
				b: "@[A-Za-z]+"
			}]
		}
	})
}(function (e) {
	if (typeof exports == "object" && typeof module == "object") module.exports = e();
	else {
		if (typeof define == "function" && define.amd) return define([], e);
		this.CodeMirror = e()
	}
})(function () {
	"use strict";

	function S(e, t) {
		if (!(this instanceof S)) return new S(e, t);
		this.options = t = t ? Ro(t) : {}, Ro(fi, t, !1), B(t);
		var n = t.value;
		typeof n == "string" && (n = new Hs(n, t.mode)), this.doc = n;
		var s = this.display = new x(e, n);
		s.wrapper.CodeMirror = this, _(this), O(this), t.lineWrapping && (this.display.wrapper.className += " CodeMirror-wrap"), t.autofocus && !d && tr(this), this.state = {
			keyMaps: [],
			overlays: [],
			modeGen: 0,
			overwrite: !1,
			focused: !1,
			suppressEdits: !1,
			pasteIncoming: !1,
			cutIncoming: !1,
			draggingText: !1,
			highlight: new Mo
		}, r && i < 11 && setTimeout(Uo(er, this, !0), 20), ir(this), uu(), Ln(this), this.curOp.forceUpdate = !0, Is(this, n), t.autofocus && !d || eu() == s.input ? setTimeout(Uo(Pr, this), 20) : Hr(this);
		for (var o in li) li.hasOwnProperty(o) && li[o](this, t[o], hi);
		U(this);
		for (var u = 0; u < mi.length; ++u) mi[u](this);
		On(this)
	}

	function x(e, t) {
		var n = this,
			o = n.input = Ko("textarea", null, null, "position: absolute; padding: 0; width: 1px; height: 1em; outline: none");
		s ? o.style.width = "1000px" : o.setAttribute("wrap", "off"), p && (o.style.border = "1px solid black"), o.setAttribute("autocorrect", "off"), o.setAttribute("autocapitalize", "off"), o.setAttribute("spellcheck", "false"), n.inputDiv = Ko("div", [o], null, "overflow: hidden; position: relative; width: 3px; height: 0px;"), n.scrollbarH = Ko("div", [Ko("div", null, null, "height: 100%; min-height: 1px")], "CodeMirror-hscrollbar"), n.scrollbarV = Ko("div", [Ko("div", null, null, "min-width: 1px")], "CodeMirror-vscrollbar"), n.scrollbarFiller = Ko("div", null, "CodeMirror-scrollbar-filler"), n.gutterFiller = Ko("div", null, "CodeMirror-gutter-filler"), n.lineDiv = Ko("div", null, "CodeMirror-code"), n.selectionDiv = Ko("div", null, null, "position: relative; z-index: 1"), n.cursorDiv = Ko("div", null, "CodeMirror-cursors"), n.measure = Ko("div", null, "CodeMirror-measure"), n.lineMeasure = Ko("div", null, "CodeMirror-measure"), n.lineSpace = Ko("div", [n.measure, n.lineMeasure, n.selectionDiv, n.cursorDiv, n.lineDiv], null, "position: relative; outline: none"), n.mover = Ko("div", [Ko("div", [n.lineSpace], "CodeMirror-lines")], null, "position: relative"), n.sizer = Ko("div", [n.mover], "CodeMirror-sizer"), n.heightForcer = Ko("div", null, null, "position: absolute; height: " + Co + "px; width: 1px;"), n.gutters = Ko("div", null, "CodeMirror-gutters"), n.lineGutter = null, n.scroller = Ko("div", [n.sizer, n.heightForcer, n.gutters], "CodeMirror-scroll"), n.scroller.setAttribute("tabIndex", "-1"), n.wrapper = Ko("div", [n.inputDiv, n.scrollbarH, n.scrollbarV, n.scrollbarFiller, n.gutterFiller, n.scroller], "CodeMirror"), r && i < 8 && (n.gutters.style.zIndex = -1, n.scroller.style.paddingRight = 0), p && (o.style.width = "0px"), s || (n.scroller.draggable = !0), l && (n.inputDiv.style.height = "1px", n.inputDiv.style.position = "absolute"), r && i < 8 && (n.scrollbarH.style.minHeight = n.scrollbarV.style.minWidth = "18px"), e.appendChild ? e.appendChild(n.wrapper) : e(n.wrapper), n.viewFrom = n.viewTo = t.first, n.view = [], n.externalMeasured = null, n.viewOffset = 0, n.lastSizeC = 0, n.updateLineNumbers = null, n.lineNumWidth = n.lineNumInnerWidth = n.lineNumChars = null, n.prevInput = "", n.alignWidgets = !1, n.pollingFast = !1, n.poll = new Mo, n.cachedCharWidth = n.cachedTextHeight = n.cachedPaddingH = null, n.inaccurateSelection = !1, n.maxLine = null, n.maxLineLength = 0, n.maxLineChanged = !1, n.wheelDX = n.wheelDY = n.wheelStartX = n.wheelStartY = null, n.shift = !1, n.selForContextMenu = null
	}

	function T(e) {
		e.doc.mode = S.getMode(e.options, e.doc.modeOption), N(e)
	}

	function N(e) {
		e.doc.iter(function (e) {
			e.stateAfter && (e.stateAfter = null), e.styles && (e.styles = null)
		}), e.doc.frontier = e.doc.first, Vt(e, 100), e.state.modeGen++, e.curOp && zn(e)
	}

	function C(e) {
		e.options.lineWrapping ? (ru(e.display.wrapper, "CodeMirror-wrap"), e.display.sizer.style.minWidth = "") : (nu(e.display.wrapper, "CodeMirror-wrap"), H(e)), L(e), zn(e), hn(e), setTimeout(function () {
			I(e)
		}, 100)
	}

	function k(e) {
		var t = Tn(e.display),
			n = e.options.lineWrapping,
			r = n && Math.max(5, e.display.scroller.clientWidth / Nn(e.display) - 3);
		return function (i) {
			if (is(e.doc, i)) return 0;
			var s = 0;
			if (i.widgets)
				for (var o = 0; o < i.widgets.length; o++) i.widgets[o].height && (s += i.widgets[o].height);
			return n ? s + (Math.ceil(i.text.length / r) || 1) * t : s + t
		}
	}

	function L(e) {
		var t = e.doc,
			n = k(e);
		t.iter(function (e) {
			var t = n(e);
			t != e.height && zs(e, t)
		})
	}

	function A(e) {
		var t = Ei[e.options.keyMap],
			n = t.style;
		e.display.wrapper.className = e.display.wrapper.className.replace(/\s*cm-keymap-\S+/g, "") + (n ? " cm-keymap-" + n : "")
	}

	function O(e) {
		e.display.wrapper.className = e.display.wrapper.className.replace(/\s*cm-s-\S+/g, "") + e.options.theme.replace(/(^|\s)\s*/g, " cm-s-"), hn(e)
	}

	function M(e) {
		_(e), zn(e), setTimeout(function () {
			R(e)
		}, 20)
	}

	function _(e) {
		var t = e.display.gutters,
			n = e.options.gutters;
		Go(t);
		for (var r = 0; r < n.length; ++r) {
			var i = n[r],
				s = t.appendChild(Ko("div", null, "CodeMirror-gutter " + i));
			i == "CodeMirror-linenumbers" && (e.display.lineGutter = s, s.style.width = (e.display.lineNumWidth || 1) + "px")
		}
		t.style.display = r ? "" : "none", D(e)
	}

	function D(e) {
		var t = e.display.gutters.offsetWidth;
		e.display.sizer.style.marginLeft = t + "px", e.display.scrollbarH.style.left = e.options.fixedGutter ? t + "px" : 0
	}

	function P(e) {
		if (e.height == 0) return 0;
		var t = e.text.length,
			n, r = e;
		while (n = Gi(r)) {
			var i = n.find(0, !0);
			r = i.from.line, t += i.from.ch - i.to.ch
		}
		r = e;
		while (n = Yi(r)) {
			var i = n.find(0, !0);
			t -= r.text.length - i.from.ch, r = i.to.line, t += r.text.length - i.to.ch
		}
		return t
	}

	function H(e) {
		var t = e.display,
			n = e.doc;
		t.maxLine = qs(n, n.first), t.maxLineLength = P(t.maxLine), t.maxLineChanged = !0, n.iter(function (e) {
			var n = P(e);
			n > t.maxLineLength && (t.maxLineLength = n, t.maxLine = e)
		})
	}

	function B(e) {
		var t = Fo(e.gutters, "CodeMirror-linenumbers");
		t == -1 && e.lineNumbers ? e.gutters = e.gutters.concat(["CodeMirror-linenumbers"]) : t > -1 && !e.lineNumbers && (e.gutters = e.gutters.slice(0), e.gutters.splice(t, 1))
	}

	function j(e) {
		return e.display.scroller.clientHeight - e.display.wrapper.clientHeight < Co - 3
	}

	function F(e) {
		var t = e.display.scroller;
		return {
			clientHeight: t.clientHeight,
			barHeight: e.display.scrollbarV.clientHeight,
			scrollWidth: t.scrollWidth,
			clientWidth: t.clientWidth,
			hScrollbarTakesSpace: j(e),
			barWidth: e.display.scrollbarH.clientWidth,
			docHeight: Math.round(e.doc.height + Gt(e.display))
		}
	}

	function I(e, t) {
		t || (t = F(e));
		var n = e.display,
			r = cu(n.measure),
			i = t.docHeight + Co,
			s = t.scrollWidth > t.clientWidth;
		s && t.scrollWidth <= t.clientWidth + 1 && r > 0 && !t.hScrollbarTakesSpace && (s = !1);
		var o = i > t.clientHeight;
		o ? (n.scrollbarV.style.display = "block", n.scrollbarV.style.bottom = s ? r + "px" : "0", n.scrollbarV.firstChild.style.height = Math.max(0, i - t.clientHeight + (t.barHeight || n.scrollbarV.clientHeight)) + "px") : (n.scrollbarV.style.display = "", n.scrollbarV.firstChild.style.height = "0"), s ? (n.scrollbarH.style.display = "block", n.scrollbarH.style.right = o ? r + "px" : "0", n.scrollbarH.firstChild.style.width = t.scrollWidth - t.clientWidth + (t.barWidth || n.scrollbarH.clientWidth) + "px") : (n.scrollbarH.style.display = "", n.scrollbarH.firstChild.style.width = "0"), s && o ? (n.scrollbarFiller.style.display = "block", n.scrollbarFiller.style.height = n.scrollbarFiller.style.width = r + "px") : n.scrollbarFiller.style.display = "", s && e.options.coverGutterNextToScrollbar && e.options.fixedGutter ? (n.gutterFiller.style.display = "block", n.gutterFiller.style.height = r + "px", n.gutterFiller.style.width = n.gutters.offsetWidth + "px") : n.gutterFiller.style.display = "";
		if (!e.state.checkedOverlayScrollbar && t.clientHeight > 0) {
			if (r === 0) {
				var u = v && !c ? "12px" : "18px";
				n.scrollbarV.style.minWidth = n.scrollbarH.style.minHeight = u;
				var a = function (t) {
					po(t) != n.scrollbarV && po(t) != n.scrollbarH && Fn(e, ar)(t)
				};
				mo(n.scrollbarV, "mousedown", a), mo(n.scrollbarH, "mousedown", a)
			}
			e.state.checkedOverlayScrollbar = !0
		}
	}

	function q(e, t, n) {
		var r = n && n.top != null ? Math.max(0, n.top) : e.scroller.scrollTop;
		r = Math.floor(r - Qt(e));
		var i = n && n.bottom != null ? n.bottom : r + e.wrapper.clientHeight,
			s = Xs(t, r),
			o = Xs(t, i);
		if (n && n.ensure) {
			var u = n.ensure.from.line,
				a = n.ensure.to.line;
			if (u < s) return {
				from: u,
				to: Xs(t, Vs(qs(t, u)) + e.wrapper.clientHeight)
			};
			if (Math.min(a, t.lastLine()) >= o) return {
				from: Xs(t, Vs(qs(t, a)) - e.wrapper.clientHeight),
				to: a
			}
		}
		return {
			from: s,
			to: Math.max(o, s + 1)
		}
	}

	function R(e) {
		var t = e.display,
			n = t.view;
		if (!t.alignWidgets && (!t.gutters.firstChild || !e.options.fixedGutter)) return;
		var r = W(t) - t.scroller.scrollLeft + e.doc.scrollLeft,
			i = t.gutters.offsetWidth,
			s = r + "px";
		for (var o = 0; o < n.length; o++)
			if (!n[o].hidden) {
				e.options.fixedGutter && n[o].gutter && (n[o].gutter.style.left = s);
				var u = n[o].alignable;
				if (u)
					for (var a = 0; a < u.length; a++) u[a].style.left = s
			}
		e.options.fixedGutter && (t.gutters.style.left = r + i + "px")
	}

	function U(e) {
		if (!e.options.lineNumbers) return !1;
		var t = e.doc,
			n = z(e.options, t.first + t.size - 1),
			r = e.display;
		if (n.length != r.lineNumChars) {
			var i = r.measure.appendChild(Ko("div", [Ko("div", n)], "CodeMirror-linenumber CodeMirror-gutter-elt")),
				s = i.firstChild.offsetWidth,
				o = i.offsetWidth - s;
			return r.lineGutter.style.width = "", r.lineNumInnerWidth = Math.max(s, r.lineGutter.offsetWidth - o), r.lineNumWidth = r.lineNumInnerWidth + o, r.lineNumChars = r.lineNumInnerWidth ? n.length : -1, r.lineGutter.style.width = r.lineNumWidth + "px", D(e), !0
		}
		return !1
	}

	function z(e, t) {
		return String(e.lineNumberFormatter(t + e.firstLineNumber))
	}

	function W(e) {
		return e.scroller.getBoundingClientRect().left - e.sizer.getBoundingClientRect().left
	}

	function X(e, t, n) {
		var r = e.display;
		this.viewport = t, this.visible = q(r, e.doc, t), this.editorIsHidden = !r.wrapper.offsetWidth, this.wrapperHeight = r.wrapper.clientHeight, this.oldViewFrom = r.viewFrom, this.oldViewTo = r.viewTo, this.oldScrollerWidth = r.scroller.clientWidth, this.force = n, this.dims = Z(e)
	}

	function V(e, t) {
		var n = e.display,
			r = e.doc;
		if (t.editorIsHidden) return Xn(e), !1;
		if (!t.force && t.visible.from >= n.viewFrom && t.visible.to <= n.viewTo && (n.updateLineNumbers == null || n.updateLineNumbers >= n.viewTo) && Kn(e) == 0) return !1;
		U(e) && (Xn(e), t.dims = Z(e));
		var i = r.first + r.size,
			s = Math.max(t.visible.from - e.options.viewportMargin, r.first),
			o = Math.min(i, t.visible.to + e.options.viewportMargin);
		n.viewFrom < s && s - n.viewFrom < 20 && (s = Math.max(r.first, n.viewFrom)), n.viewTo > o && n.viewTo - o < 20 && (o = Math.min(i, n.viewTo)), E && (s = ns(e.doc, s), o = rs(e.doc, o));
		var u = s != n.viewFrom || o != n.viewTo || n.lastSizeC != t.wrapperHeight;
		Jn(e, s, o), n.viewOffset = Vs(qs(e.doc, n.viewFrom)), e.display.mover.style.top = n.viewOffset + "px";
		var a = Kn(e);
		if (!u && a == 0 && !t.force && (n.updateLineNumbers == null || n.updateLineNumbers >= n.viewTo)) return !1;
		var f = eu();
		return a > 4 && (n.lineDiv.style.display = "none"), et(e, n.updateLineNumbers, t.dims), a > 4 && (n.lineDiv.style.display = ""), f && eu() != f && f.offsetHeight && f.focus(), Go(n.cursorDiv), Go(n.selectionDiv), u && (n.lastSizeC = t.wrapperHeight, Vt(e, 400)), n.updateLineNumbers = null, !0
	}

	function $(e, t) {
		var n = t.force,
			r = t.viewport;
		for (var i = !0;; i = !1) {
			if (i && e.options.lineWrapping && t.oldScrollerWidth != e.display.scroller.clientWidth) n = !0;
			else {
				n = !1, r && r.top != null && (r = {
					top: Math.min(e.doc.height + Gt(e.display) - Co - e.display.scroller.clientHeight, r.top)
				}), t.visible = q(e.display, e.doc, r);
				if (t.visible.from >= e.display.viewFrom && t.visible.to <= e.display.viewTo) break
			}
			if (!V(e, t)) break;
			G(e);
			var s = F(e);
			Ut(e), K(e, s), I(e, s)
		}
		wo(e, "update", e), (e.display.viewFrom != t.oldViewFrom || e.display.viewTo != t.oldViewTo) && wo(e, "viewportChange", e, e.display.viewFrom, e.display.viewTo)
	}

	function J(e, t) {
		var n = new X(e, t);
		if (V(e, n)) {
			G(e), $(e, n);
			var r = F(e);
			Ut(e), K(e, r), I(e, r)
		}
	}

	function K(e, t) {
		e.display.sizer.style.minHeight = e.display.heightForcer.style.top = t.docHeight + "px", e.display.gutters.style.height = Math.max(t.docHeight, t.clientHeight - Co) + "px"
	}

	function Q(e, t) {
		e.display.sizer.offsetWidth + e.display.gutters.offsetWidth < e.display.scroller.clientWidth - 1 && (e.display.sizer.style.minHeight = e.display.heightForcer.style.top = "0px", e.display.gutters.style.height = t.docHeight + "px")
	}

	function G(e) {
		var t = e.display,
			n = t.lineDiv.offsetTop;
		for (var s = 0; s < t.view.length; s++) {
			var o = t.view[s],
				u;
			if (o.hidden) continue;
			if (r && i < 8) {
				var a = o.node.offsetTop + o.node.offsetHeight;
				u = a - n, n = a
			} else {
				var f = o.node.getBoundingClientRect();
				u = f.bottom - f.top
			}
			var l = o.line.height - u;
			u < 2 && (u = Tn(t));
			if (l > .001 || l < -0.001) {
				zs(o.line, u), Y(o.line);
				if (o.rest)
					for (var c = 0; c < o.rest.length; c++) Y(o.rest[c])
			}
		}
	}

	function Y(e) {
		if (e.widgets)
			for (var t = 0; t < e.widgets.length; ++t) e.widgets[t].height = e.widgets[t].node.offsetHeight
	}

	function Z(e) {
		var t = e.display,
			n = {},
			r = {},
			i = t.gutters.clientLeft;
		for (var s = t.gutters.firstChild, o = 0; s; s = s.nextSibling, ++o) n[e.options.gutters[o]] = s.offsetLeft + s.clientLeft + i, r[e.options.gutters[o]] = s.clientWidth;
		return {
			fixedPos: W(t),
			gutterTotalWidth: t.gutters.offsetWidth,
			gutterLeft: n,
			gutterWidth: r,
			wrapperWidth: t.wrapper.clientWidth
		}
	}

	function et(e, t, n) {
		function a(t) {
			var n = t.nextSibling;
			return s && v && e.display.currentWheelTarget == t ? t.style.display = "none" : t.parentNode.removeChild(t), n
		}
		var r = e.display,
			i = e.options.lineNumbers,
			o = r.lineDiv,
			u = o.firstChild,
			f = r.view,
			l = r.viewFrom;
		for (var c = 0; c < f.length; c++) {
			var h = f[c];
			if (!h.hidden)
				if (!h.node) {
					var p = ft(e, h, l, n);
					o.insertBefore(p, u)
				} else {
					while (u != h.node) u = a(u);
					var d = i && t != null && t <= l && h.lineNumber;
					h.changes && (Fo(h.changes, "gutter") > -1 && (d = !1), tt(e, h, l, n)), d && (Go(h.lineNumber), h.lineNumber.appendChild(document.createTextNode(z(e.options, l)))), u = h.node.nextSibling
				}
			l += h.size
		}
		while (u) u = a(u)
	}

	function tt(e, t, n, r) {
		for (var i = 0; i < t.changes.length; i++) {
			var s = t.changes[i];
			s == "text" ? st(e, t) : s == "gutter" ? ut(e, t, n, r) : s == "class" ? ot(t) : s == "widget" && at(t, r)
		}
		t.changes = null
	}

	function nt(e) {
		return e.node == e.text && (e.node = Ko("div", null, null, "position: relative"), e.text.parentNode && e.text.parentNode.replaceChild(e.node, e.text), e.node.appendChild(e.text), r && i < 8 && (e.node.style.zIndex = 2)), e.node
	}

	function rt(e) {
		var t = e.bgClass ? e.bgClass + " " + (e.line.bgClass || "") : e.line.bgClass;
		t && (t += " CodeMirror-linebackground");
		if (e.background) t ? e.background.className = t : (e.background.parentNode.removeChild(e.background), e.background = null);
		else if (t) {
			var n = nt(e);
			e.background = n.insertBefore(Ko("div", null, t), n.firstChild)
		}
	}

	function it(e, t) {
		var n = e.display.externalMeasured;
		return n && n.line == t.line ? (e.display.externalMeasured = null, t.measure = n.measure, n.built) : xs(e, t)
	}

	function st(e, t) {
		var n = t.text.className,
			r = it(e, t);
		t.text == t.node && (t.node = r.pre), t.text.parentNode.replaceChild(r.pre, t.text), t.text = r.pre, r.bgClass != t.bgClass || r.textClass != t.textClass ? (t.bgClass = r.bgClass, t.textClass = r.textClass, ot(t)) : n && (t.text.className = n)
	}

	function ot(e) {
		rt(e), e.line.wrapClass ? nt(e).className = e.line.wrapClass : e.node != e.text && (e.node.className = "");
		var t = e.textClass ? e.textClass + " " + (e.line.textClass || "") : e.line.textClass;
		e.text.className = t || ""
	}

	function ut(e, t, n, r) {
		t.gutter && (t.node.removeChild(t.gutter), t.gutter = null);
		var i = t.line.gutterMarkers;
		if (e.options.lineNumbers || i) {
			var s = nt(t),
				o = t.gutter = s.insertBefore(Ko("div", null, "CodeMirror-gutter-wrapper", "position: absolute; left: " + (e.options.fixedGutter ? r.fixedPos : -r.gutterTotalWidth) + "px"), t.text);
			e.options.lineNumbers && (!i || !i["CodeMirror-linenumbers"]) && (t.lineNumber = o.appendChild(Ko("div", z(e.options, n), "CodeMirror-linenumber CodeMirror-gutter-elt", "left: " + r.gutterLeft["CodeMirror-linenumbers"] + "px; width: " + e.display.lineNumInnerWidth + "px")));
			if (i)
				for (var u = 0; u < e.options.gutters.length; ++u) {
					var a = e.options.gutters[u],
						f = i.hasOwnProperty(a) && i[a];
					f && o.appendChild(Ko("div", [f], "CodeMirror-gutter-elt", "left: " + r.gutterLeft[a] + "px; width: " + r.gutterWidth[a] + "px"))
				}
		}
	}

	function at(e, t) {
		e.alignable && (e.alignable = null);
		for (var n = e.node.firstChild, r; n; n = r) {
			var r = n.nextSibling;
			n.className == "CodeMirror-linewidget" && e.node.removeChild(n)
		}
		lt(e, t)
	}

	function ft(e, t, n, r) {
		var i = it(e, t);
		return t.text = t.node = i.pre, i.bgClass && (t.bgClass = i.bgClass), i.textClass && (t.textClass = i.textClass), ot(t), ut(e, t, n, r), lt(t, r), t.node
	}

	function lt(e, t) {
		ct(e.line, e, t, !0);
		if (e.rest)
			for (var n = 0; n < e.rest.length; n++) ct(e.rest[n], e, t, !1)
	}

	function ct(e, t, n, r) {
		if (!e.widgets) return;
		var i = nt(t);
		for (var s = 0, o = e.widgets; s < o.length; ++s) {
			var u = o[s],
				a = Ko("div", [u.node], "CodeMirror-linewidget");
			u.handleMouseEvents || (a.ignoreEvents = !0), ht(u, a, t, n), r && u.above ? i.insertBefore(a, t.gutter || t.text) : i.appendChild(a), wo(u, "redraw")
		}
	}

	function ht(e, t, n, r) {
		if (e.noHScroll) {
			(n.alignable || (n.alignable = [])).push(t);
			var i = r.wrapperWidth;
			t.style.left = r.fixedPos + "px", e.coverGutter || (i -= r.gutterTotalWidth, t.style.paddingLeft = r.gutterTotalWidth + "px"), t.style.width = i + "px"
		}
		e.coverGutter && (t.style.zIndex = 5, t.style.position = "relative", e.noHScroll || (t.style.marginLeft = -r.gutterTotalWidth + "px"))
	}

	function vt(e) {
		return pt(e.line, e.ch)
	}

	function mt(e, t) {
		return dt(e, t) < 0 ? t : e
	}

	function gt(e, t) {
		return dt(e, t) < 0 ? e : t
	}

	function yt(e, t) {
		this.ranges = e, this.primIndex = t
	}

	function bt(e, t) {
		this.anchor = e, this.head = t
	}

	function wt(e, t) {
		var n = e[t];
		e.sort(function (e, t) {
			return dt(e.from(), t.from())
		}), t = Fo(e, n);
		for (var r = 1; r < e.length; r++) {
			var i = e[r],
				s = e[r - 1];
			if (dt(s.to(), i.from()) >= 0) {
				var o = gt(s.from(), i.from()),
					u = mt(s.to(), i.to()),
					a = s.empty() ? i.from() == i.head : s.from() == s.head;
				r <= t && --t, e.splice(--r, 2, new bt(a ? u : o, a ? o : u))
			}
		}
		return new yt(e, t)
	}

	function Et(e, t) {
		return new yt([new bt(e, t || e)], 0)
	}

	function St(e, t) {
		return Math.max(e.first, Math.min(t, e.first + e.size - 1))
	}

	function xt(e, t) {
		if (t.line < e.first) return pt(e.first, 0);
		var n = e.first + e.size - 1;
		return t.line > n ? pt(n, qs(e, n).text.length) : Tt(t, qs(e, t.line).text.length)
	}

	function Tt(e, t) {
		var n = e.ch;
		return n == null || n > t ? pt(e.line, t) : n < 0 ? pt(e.line, 0) : e
	}

	function Nt(e, t) {
		return t >= e.first && t < e.first + e.size
	}

	function Ct(e, t) {
		for (var n = [], r = 0; r < t.length; r++) n[r] = xt(e, t[r]);
		return n
	}

	function kt(e, t, n, r) {
		if (e.cm && e.cm.display.shift || e.extend) {
			var i = t.anchor;
			if (r) {
				var s = dt(n, i) < 0;
				s != dt(r, i) < 0 ? (i = n, n = r) : s != dt(n, r) < 0 && (n = r)
			}
			return new bt(i, n)
		}
		return new bt(r || n, n)
	}

	function Lt(e, t, n, r) {
		Pt(e, new yt([kt(e, e.sel.primary(), t, n)], 0), r)
	}

	function At(e, t, n) {
		for (var r = [], i = 0; i < e.sel.ranges.length; i++) r[i] = kt(e, e.sel.ranges[i], t[i], null);
		var s = wt(r, e.sel.primIndex);
		Pt(e, s, n)
	}

	function Ot(e, t, n, r) {
		var i = e.sel.ranges.slice(0);
		i[t] = n, Pt(e, wt(i, e.sel.primIndex), r)
	}

	function Mt(e, t, n, r) {
		Pt(e, Et(t, n), r)
	}

	function _t(e, t) {
		var n = {
			ranges: t.ranges,
			update: function (t) {
				this.ranges = [];
				for (var n = 0; n < t.length; n++) this.ranges[n] = new bt(xt(e, t[n].anchor), xt(e, t[n].head))
			}
		};
		return yo(e, "beforeSelectionChange", e, n), e.cm && yo(e.cm, "beforeSelectionChange", e.cm, n), n.ranges != t.ranges ? wt(n.ranges, n.ranges.length - 1) : t
	}

	function Dt(e, t, n) {
		var r = e.history.done,
			i = Bo(r);
		i && i.ranges ? (r[r.length - 1] = t, Ht(e, t, n)) : Pt(e, t, n)
	}

	function Pt(e, t, n) {
		Ht(e, t, n), eo(e, e.sel, e.cm ? e.cm.curOp.id : NaN, n)
	}

	function Ht(e, t, n) {
		if (To(e, "beforeSelectionChange") || e.cm && To(e.cm, "beforeSelectionChange")) t = _t(e, t);
		var r = n && n.bias || (dt(t.primary().head, e.sel.primary().head) < 0 ? -1 : 1);
		Bt(e, Ft(e, t, r, !0)), (!n || n.scroll !== !1) && e.cm && ni(e.cm)
	}

	function Bt(e, t) {
		if (t.equals(e.sel)) return;
		e.sel = t, e.cm && (e.cm.curOp.updateInput = e.cm.curOp.selectionChanged = !0, xo(e.cm)), wo(e, "cursorActivity", e)
	}

	function jt(e) {
		Bt(e, Ft(e, e.sel, null, !1), Lo)
	}

	function Ft(e, t, n, r) {
		var i;
		for (var s = 0; s < t.ranges.length; s++) {
			var o = t.ranges[s],
				u = It(e, o.anchor, n, r),
				a = It(e, o.head, n, r);
			if (i || u != o.anchor || a != o.head) i || (i = t.ranges.slice(0, s)), i[s] = new bt(u, a)
		}
		return i ? wt(i, t.primIndex) : t
	}

	function It(e, t, n, r) {
		var i = !1,
			s = t,
			o = n || 1;
		e.cantEdit = !1;
		e: for (;;) {
			var u = qs(e, s.line);
			if (u.markedSpans)
				for (var a = 0; a < u.markedSpans.length; ++a) {
					var f = u.markedSpans[a],
						l = f.marker;
					if ((f.from == null || (l.inclusiveLeft ? f.from <= s.ch : f.from < s.ch)) && (f.to == null || (l.inclusiveRight ? f.to >= s.ch : f.to > s.ch))) {
						if (r) {
							yo(l, "beforeCursorEnter");
							if (l.explicitlyCleared) {
								if (!u.markedSpans) break;
								--a;
								continue
							}
						}
						if (!l.atomic) continue;
						var c = l.find(o < 0 ? -1 : 1);
						if (dt(c, s) == 0) {
							c.ch += o, c.ch < 0 ? c.line > e.first ? c = xt(e, pt(c.line - 1)) : c = null : c.ch > u.text.length && (c.line < e.first + e.size - 1 ? c = pt(c.line + 1, 0) : c = null);
							if (!c) {
								if (i) return r ? (e.cantEdit = !0, pt(e.first, 0)) : It(e, t, n, !0);
								i = !0, c = t, o = -o
							}
						}
						s = c;
						continue e
					}
				}
			return s
		}
	}

	function qt(e) {
		var t = e.display,
			n = e.doc,
			r = {},
			i = r.cursors = document.createDocumentFragment(),
			s = r.selection = document.createDocumentFragment();
		for (var o = 0; o < n.sel.ranges.length; o++) {
			var u = n.sel.ranges[o],
				a = u.empty();
			(a || e.options.showCursorWhenSelecting) && zt(e, u, i), a || Wt(e, u, s)
		}
		if (e.options.moveInputWithCursor) {
			var f = yn(e, n.sel.primary().head, "div"),
				l = t.wrapper.getBoundingClientRect(),
				c = t.lineDiv.getBoundingClientRect();
			r.teTop = Math.max(0, Math.min(t.wrapper.clientHeight - 10, f.top + c.top - l.top)), r.teLeft = Math.max(0, Math.min(t.wrapper.clientWidth - 10, f.left + c.left - l.left))
		}
		return r
	}

	function Rt(e, t) {
		Yo(e.display.cursorDiv, t.cursors), Yo(e.display.selectionDiv, t.selection), t.teTop != null && (e.display.inputDiv.style.top = t.teTop + "px", e.display.inputDiv.style.left = t.teLeft + "px")
	}

	function Ut(e) {
		Rt(e, qt(e))
	}

	function zt(e, t, n) {
		var r = yn(e, t.head, "div", null, null, !e.options.singleCursorHeightPerLine),
			i = n.appendChild(Ko("div", " ", "CodeMirror-cursor"));
		i.style.left = r.left + "px", i.style.top = r.top + "px", i.style.height = Math.max(0, r.bottom - r.top) * e.options.cursorHeight + "px";
		if (r.other) {
			var s = n.appendChild(Ko("div", " ", "CodeMirror-cursor CodeMirror-secondarycursor"));
			s.style.display = "", s.style.left = r.other.left + "px", s.style.top = r.other.top + "px", s.style.height = (r.other.bottom - r.other.top) * .85 + "px"
		}
	}

	function Wt(e, t, n) {
		function f(e, t, n, r) {
			t < 0 && (t = 0), t = Math.round(t), r = Math.round(r), s.appendChild(Ko("div", null, "CodeMirror-selected", "position: absolute; left: " + e + "px; top: " + t + "px; width: " + (n == null ? a - e : n) + "px; height: " + (r - t) + "px"))
		}

		function l(t, n, r) {
			function h(n, r) {
				return gn(e, pt(t, n), "div", s, r)
			}
			var s = qs(i, t),
				o = s.text.length,
				l, c;
			return Su($s(s), n || 0, r == null ? o : r, function (e, t, i) {
				var s = h(e, "left"),
					p, d, v;
				if (e == t) p = s, d = v = s.left;
				else {
					p = h(t - 1, "right");
					if (i == "rtl") {
						var m = s;
						s = p, p = m
					}
					d = s.left, v = p.right
				}
				n == null && e == 0 && (d = u), p.top - s.top > 3 && (f(d, s.top, null, s.bottom), d = u, s.bottom < p.top && f(d, s.bottom, null, p.top)), r == null && t == o && (v = a);
				if (!l || s.top < l.top || s.top == l.top && s.left < l.left) l = s;
				if (!c || p.bottom > c.bottom || p.bottom == c.bottom && p.right > c.right) c = p;
				d < u + 1 && (d = u), f(d, p.top, v - d, p.bottom)
			}), {
				start: l,
				end: c
			}
		}
		var r = e.display,
			i = e.doc,
			s = document.createDocumentFragment(),
			o = Yt(e.display),
			u = o.left,
			a = r.lineSpace.offsetWidth - o.right,
			c = t.from(),
			h = t.to();
		if (c.line == h.line) l(c.line, c.ch, h.ch);
		else {
			var p = qs(i, c.line),
				d = qs(i, h.line),
				v = es(p) == es(d),
				m = l(c.line, c.ch, v ? p.text.length + 1 : null).end,
				g = l(h.line, v ? 0 : null, h.ch).start;
			v && (m.top < g.top - 2 ? (f(m.right, m.top, null, m.bottom), f(u, g.top, g.left, g.bottom)) : f(m.right, m.top, g.left - m.right, m.bottom)), m.bottom < g.top && f(u, m.bottom, null, g.top)
		}
		n.appendChild(s)
	}

	function Xt(e) {
		if (!e.state.focused) return;
		var t = e.display;
		clearInterval(t.blinker);
		var n = !0;
		t.cursorDiv.style.visibility = "", e.options.cursorBlinkRate > 0 ? t.blinker = setInterval(function () {
			t.cursorDiv.style.visibility = (n = !n) ? "" : "hidden"
		}, e.options.cursorBlinkRate) : e.options.cursorBlinkRate < 0 && (t.cursorDiv.style.visibility = "hidden")
	}

	function Vt(e, t) {
		e.doc.mode.startState && e.doc.frontier < e.display.viewTo && e.state.highlight.set(t, Uo($t, e))
	}

	function $t(e) {
		var t = e.doc;
		t.frontier < t.first && (t.frontier = t.first);
		if (t.frontier >= e.display.viewTo) return;
		var n = +(new Date) + e.options.workTime,
			r = yi(t.mode, Kt(e, t.frontier)),
			i = [];
		t.iter(t.frontier, Math.min(t.first + t.size, e.display.viewTo + 500), function (s) {
			if (t.frontier >= e.display.viewFrom) {
				var o = s.styles,
					u = gs(e, s, r, !0);
				s.styles = u.styles;
				var a = s.styleClasses,
					f = u.classes;
				f ? s.styleClasses = f : a && (s.styleClasses = null);
				var l = !o || o.length != s.styles.length || a != f && (!a || !f || a.bgClass != f.bgClass || a.textClass != f.textClass);
				for (var c = 0; !l && c < o.length; ++c) l = o[c] != s.styles[c];
				l && i.push(t.frontier), s.stateAfter = yi(t.mode, r)
			} else bs(e, s.text, r), s.stateAfter = t.frontier % 5 == 0 ? yi(t.mode, r) : null;
			++t.frontier;
			if (+(new Date) > n) return Vt(e, e.options.workDelay), !0
		}), i.length && jn(e, function () {
			for (var t = 0; t < i.length; t++) Wn(e, i[t], "text")
		})
	}

	function Jt(e, t, n) {
		var r, i, s = e.doc,
			o = n ? -1 : t - (e.doc.mode.innerMode ? 1e3 : 100);
		for (var u = t; u > o; --u) {
			if (u <= s.first) return s.first;
			var a = qs(s, u - 1);
			if (a.stateAfter && (!n || u <= s.frontier)) return u;
			var f = _o(a.text, null, e.options.tabSize);
			if (i == null || r > f) i = u - 1, r = f
		}
		return i
	}

	function Kt(e, t, n) {
		var r = e.doc,
			i = e.display;
		if (!r.mode.startState) return !0;
		var s = Jt(e, t, n),
			o = s > r.first && qs(r, s - 1).stateAfter;
		return o ? o = yi(r.mode, o) : o = bi(r.mode), r.iter(s, t, function (n) {
			bs(e, n.text, o);
			var u = s == t - 1 || s % 5 == 0 || s >= i.viewFrom && s < i.viewTo;
			n.stateAfter = u ? yi(r.mode, o) : null, ++s
		}), n && (r.frontier = s), o
	}

	function Qt(e) {
		return e.lineSpace.offsetTop
	}

	function Gt(e) {
		return e.mover.offsetHeight - e.lineSpace.offsetHeight
	}

	function Yt(e) {
		if (e.cachedPaddingH) return e.cachedPaddingH;
		var t = Yo(e.measure, Ko("pre", "x")),
			n = window.getComputedStyle ? window.getComputedStyle(t) : t.currentStyle,
			r = {
				left: parseInt(n.paddingLeft),
				right: parseInt(n.paddingRight)
			};
		return !isNaN(r.left) && !isNaN(r.right) && (e.cachedPaddingH = r), r
	}

	function Zt(e, t, n) {
		var r = e.options.lineWrapping,
			i = r && e.display.scroller.clientWidth;
		if (!t.measure.heights || r && t.measure.width != i) {
			var s = t.measure.heights = [];
			if (r) {
				t.measure.width = i;
				var o = t.text.firstChild.getClientRects();
				for (var u = 0; u < o.length - 1; u++) {
					var a = o[u],
						f = o[u + 1];
					Math.abs(a.bottom - f.bottom) > 2 && s.push((a.bottom + f.top) / 2 - n.top)
				}
			}
			s.push(n.bottom - n.top)
		}
	}

	function en(e, t, n) {
		if (e.line == t) return {
			map: e.measure.map,
			cache: e.measure.cache
		};
		for (var r = 0; r < e.rest.length; r++)
			if (e.rest[r] == t) return {
				map: e.measure.maps[r],
				cache: e.measure.caches[r]
			};
		for (var r = 0; r < e.rest.length; r++)
			if (Ws(e.rest[r]) > n) return {
				map: e.measure.maps[r],
				cache: e.measure.caches[r],
				before: !0
			}
	}

	function tn(e, t) {
		t = es(t);
		var n = Ws(t),
			r = e.display.externalMeasured = new Rn(e.doc, t, n);
		r.lineN = n;
		var i = r.built = xs(e, r);
		return r.text = i.pre, Yo(e.display.lineMeasure, i.pre), r
	}

	function nn(e, t, n, r) {
		return on(e, sn(e, t), n, r)
	}

	function rn(e, t) {
		if (t >= e.display.viewFrom && t < e.display.viewTo) return e.display.view[Vn(e, t)];
		var n = e.display.externalMeasured;
		if (n && t >= n.lineN && t < n.lineN + n.size) return n
	}

	function sn(e, t) {
		var n = Ws(t),
			r = rn(e, n);
		r && !r.text ? r = null : r && r.changes && tt(e, r, n, Z(e)), r || (r = tn(e, t));
		var i = en(r, t, n);
		return {
			line: t,
			view: r,
			rect: null,
			map: i.map,
			cache: i.cache,
			before: i.before,
			hasHeights: !1
		}
	}

	function on(e, t, n, r, i) {
		t.before && (n = -1);
		var s = n + (r || ""),
			o;
		return t.cache.hasOwnProperty(s) ? o = t.cache[s] : (t.rect || (t.rect = t.view.text.getBoundingClientRect()), t.hasHeights || (Zt(e, t.view, t.rect), t.hasHeights = !0), o = an(e, t, n, r), o.bogus || (t.cache[s] = o)), {
			left: o.left,
			right: o.right,
			top: i ? o.rtop : o.top,
			bottom: i ? o.rbottom : o.bottom
		}
	}

	function an(e, t, n, s) {
		var o = t.map,
			u, a, f, l;
		for (var c = 0; c < o.length; c += 3) {
			var h = o[c],
				p = o[c + 1];
			if (n < h) a = 0, f = 1, l = "left";
			else if (n < p) a = n - h, f = a + 1;
			else if (c == o.length - 3 || n == p && o[c + 3] > n) f = p - h, a = f - 1, n >= p && (l = "right");
			if (a != null) {
				u = o[c + 2], h == p && s == (u.insertLeft ? "left" : "right") && (l = s);
				if (s == "left" && a == 0)
					while (c && o[c - 2] == o[c - 3] && o[c - 1].insertLeft) u = o[(c -= 3) + 2], l = "left";
				if (s == "right" && a == p - h)
					while (c < o.length - 3 && o[c + 3] == o[c + 4] && !o[c + 5].insertLeft) u = o[(c += 3) + 2], l = "right";
				break
			}
		}
		var d;
		if (u.nodeType == 3) {
			for (var c = 0; c < 4; c++) {
				while (a && Jo(t.line.text.charAt(h + a))) --a;
				while (h + f < p && Jo(t.line.text.charAt(h + f))) ++f;
				if (r && i < 9 && a == 0 && f == p - h) d = u.parentNode.getBoundingClientRect();
				else if (r && e.options.lineWrapping) {
					var v = Qo(u, a, f).getClientRects();
					v.length ? d = v[s == "right" ? v.length - 1 : 0] : d = un
				} else d = Qo(u, a, f).getBoundingClientRect() || un;
				if (d.left || d.right || a == 0) break;
				f = a, a -= 1, l = "right"
			}
			r && i < 11 && (d = fn(e.display.measure, d))
		} else {
			a > 0 && (l = s = "right");
			var v;
			e.options.lineWrapping && (v = u.getClientRects()).length > 1 ? d = v[s == "right" ? v.length - 1 : 0] : d = u.getBoundingClientRect()
		}
		if (r && i < 9 && !a && (!d || !d.left && !d.right)) {
			var m = u.parentNode.getClientRects()[0];
			m ? d = {
				left: m.left,
				right: m.left + Nn(e.display),
				top: m.top,
				bottom: m.bottom
			} : d = un
		}
		var g = d.top - t.rect.top,
			y = d.bottom - t.rect.top,
			b = (g + y) / 2,
			w = t.view.measure.heights;
		for (var c = 0; c < w.length - 1; c++)
			if (b < w[c]) break;
		var E = c ? w[c - 1] : 0,
			S = w[c],
			x = {
				left: (l == "right" ? d.right : d.left) - t.rect.left,
				right: (l == "left" ? d.left : d.right) - t.rect.left,
				top: E,
				bottom: S
			};
		return !d.left && !d.right && (x.bogus = !0), e.options.singleCursorHeightPerLine || (x.rtop = g, x.rbottom = y), x
	}

	function fn(e, t) {
		if (!window.screen || screen.logicalXDPI == null || screen.logicalXDPI == screen.deviceXDPI || !wu(e)) return t;
		var n = screen.logicalXDPI / screen.deviceXDPI,
			r = screen.logicalYDPI / screen.deviceYDPI;
		return {
			left: t.left * n,
			right: t.right * n,
			top: t.top * r,
			bottom: t.bottom * r
		}
	}

	function ln(e) {
		if (e.measure) {
			e.measure.cache = {}, e.measure.heights = null;
			if (e.rest)
				for (var t = 0; t < e.rest.length; t++) e.measure.caches[t] = {}
		}
	}

	function cn(e) {
		e.display.externalMeasure = null, Go(e.display.lineMeasure);
		for (var t = 0; t < e.display.view.length; t++) ln(e.display.view[t])
	}

	function hn(e) {
		cn(e), e.display.cachedCharWidth = e.display.cachedTextHeight = e.display.cachedPaddingH = null, e.options.lineWrapping || (e.display.maxLineChanged = !0), e.display.lineNumChars = null
	}

	function pn() {
		return window.pageXOffset || (document.documentElement || document.body).scrollLeft
	}

	function dn() {
		return window.pageYOffset || (document.documentElement || document.body).scrollTop
	}

	function vn(e, t, n, r) {
		if (t.widgets)
			for (var i = 0; i < t.widgets.length; ++i)
				if (t.widgets[i].above) {
					var s = as(t.widgets[i]);
					n.top += s, n.bottom += s
				}
		if (r == "line") return n;
		r || (r = "local");
		var o = Vs(t);
		r == "local" ? o += Qt(e.display) : o -= e.display.viewOffset;
		if (r == "page" || r == "window") {
			var u = e.display.lineSpace.getBoundingClientRect();
			o += u.top + (r == "window" ? 0 : dn());
			var a = u.left + (r == "window" ? 0 : pn());
			n.left += a, n.right += a
		}
		return n.top += o, n.bottom += o, n
	}

	function mn(e, t, n) {
		if (n == "div") return t;
		var r = t.left,
			i = t.top;
		if (n == "page") r -= pn(), i -= dn();
		else if (n == "local" || !n) {
			var s = e.display.sizer.getBoundingClientRect();
			r += s.left, i += s.top
		}
		var o = e.display.lineSpace.getBoundingClientRect();
		return {
			left: r - o.left,
			top: i - o.top
		}
	}

	function gn(e, t, n, r, i) {
		return r || (r = qs(e.doc, t.line)), vn(e, r, nn(e, r, t.ch, i), n)
	}

	function yn(e, t, n, r, i, s) {
		function o(t, o) {
			var u = on(e, i, t, o ? "right" : "left", s);
			return o ? u.left = u.right : u.right = u.left, vn(e, r, u, n)
		}

		function u(e, t) {
			var n = a[t],
				r = n.level % 2;
			return e == xu(n) && t && n.level < a[t - 1].level ? (n = a[--t], e = Tu(n) - (n.level % 2 ? 0 : 1), r = !0) : e == Tu(n) && t < a.length - 1 && n.level < a[t + 1].level && (n = a[++t], e = xu(n) - n.level % 2, r = !1), r && e == n.to && e > n.from ? o(e - 1) : o(e, r)
		}
		r = r || qs(e.doc, t.line), i || (i = sn(e, r));
		var a = $s(r),
			f = t.ch;
		if (!a) return o(f);
		var l = _u(a, f),
			c = u(f, l);
		return Mu != null && (c.other = u(f, Mu)), c
	}

	function bn(e, t) {
		var n = 0,
			t = xt(e.doc, t);
		e.options.lineWrapping || (n = Nn(e.display) * t.ch);
		var r = qs(e.doc, t.line),
			i = Vs(r) + Qt(e.display);
		return {
			left: n,
			right: n,
			top: i,
			bottom: i + r.height
		}
	}

	function wn(e, t, n, r) {
		var i = pt(e, t);
		return i.xRel = r, n && (i.outside = !0), i
	}

	function En(e, t, n) {
		var r = e.doc;
		n += e.display.viewOffset;
		if (n < 0) return wn(r.first, 0, !0, -1);
		var i = Xs(r, n),
			s = r.first + r.size - 1;
		if (i > s) return wn(r.first + r.size - 1, qs(r, s).text.length, !0, 1);
		t < 0 && (t = 0);
		var o = qs(r, i);
		for (;;) {
			var u = Sn(e, o, i, t, n),
				a = Yi(o),
				f = a && a.find(0, !0);
			if (!a || !(u.ch > f.from.ch || u.ch == f.from.ch && u.xRel > 0)) return u;
			i = Ws(o = f.to.line)
		}
	}

	function Sn(e, t, n, r, i) {
		function f(r) {
			var i = yn(e, pt(n, r), "line", t, a);
			return o = !0, s > i.bottom ? i.left - u : s < i.top ? i.left + u : (o = !1, i.left)
		}
		var s = i - Vs(t),
			o = !1,
			u = 2 * e.display.wrapper.clientWidth,
			a = sn(e, t),
			l = $s(t),
			c = t.text.length,
			h = Nu(t),
			p = Cu(t),
			d = f(h),
			v = o,
			m = f(p),
			g = o;
		if (r > m) return wn(n, p, g, 1);
		for (;;) {
			if (l ? p == h || p == Pu(t, h, 1) : p - h <= 1) {
				var y = r < d || r - d <= m - r ? h : p,
					b = r - (y == h ? d : m);
				while (Jo(t.text.charAt(y))) ++y;
				var w = wn(n, y, y == h ? v : g, b < -1 ? -1 : b > 1 ? 1 : 0);
				return w
			}
			var E = Math.ceil(c / 2),
				S = h + E;
			if (l) {
				S = h;
				for (var x = 0; x < E; ++x) S = Pu(t, S, 1)
			}
			var T = f(S);
			if (T > r) {
				p = S, m = T;
				if (g = o) m += 1e3;
				c = E
			} else h = S, d = T, v = o, c -= E
		}
	}

	function Tn(e) {
		if (e.cachedTextHeight != null) return e.cachedTextHeight;
		if (xn == null) {
			xn = Ko("pre");
			for (var t = 0; t < 49; ++t) xn.appendChild(document.createTextNode("x")), xn.appendChild(Ko("br"));
			xn.appendChild(document.createTextNode("x"))
		}
		Yo(e.measure, xn);
		var n = xn.offsetHeight / 50;
		return n > 3 && (e.cachedTextHeight = n), Go(e.measure), n || 1
	}

	function Nn(e) {
		if (e.cachedCharWidth != null) return e.cachedCharWidth;
		var t = Ko("span", "xxxxxxxxxx"),
			n = Ko("pre", [t]);
		Yo(e.measure, n);
		var r = t.getBoundingClientRect(),
			i = (r.right - r.left) / 10;
		return i > 2 && (e.cachedCharWidth = i), i || 10
	}

	function Ln(e) {
		e.curOp = {
			cm: e,
			viewChanged: !1,
			startHeight: e.doc.height,
			forceUpdate: !1,
			updateInput: null,
			typing: !1,
			changeObjs: null,
			cursorActivityHandlers: null,
			cursorActivityCalled: 0,
			selectionChanged: !1,
			updateMaxLine: !1,
			scrollLeft: null,
			scrollTop: null,
			scrollToPos: null,
			id: ++kn
		}, Cn ? Cn.ops.push(e.curOp) : e.curOp.ownsGroup = Cn = {
			ops: [e.curOp],
			delayedCallbacks: []
		}
	}

	function An(e) {
		var t = e.delayedCallbacks,
			n = 0;
		do {
			for (; n < t.length; n++) t[n]();
			for (var r = 0; r < e.ops.length; r++) {
				var i = e.ops[r];
				if (i.cursorActivityHandlers)
					while (i.cursorActivityCalled < i.cursorActivityHandlers.length) i.cursorActivityHandlers[i.cursorActivityCalled++](i.cm)
			}
		} while (n < t.length)
	}

	function On(e) {
		var t = e.curOp,
			n = t.ownsGroup;
		if (!n) return;
		try {
			An(n)
		} finally {
			Cn = null;
			for (var r = 0; r < n.ops.length; r++) n.ops[r].cm.curOp = null;
			Mn(n)
		}
	}

	function Mn(e) {
		var t = e.ops;
		for (var n = 0; n < t.length; n++) _n(t[n]);
		for (var n = 0; n < t.length; n++) Dn(t[n]);
		for (var n = 0; n < t.length; n++) Pn(t[n]);
		for (var n = 0; n < t.length; n++) Hn(t[n]);
		for (var n = 0; n < t.length; n++) Bn(t[n])
	}

	function _n(e) {
		var t = e.cm,
			n = t.display;
		e.updateMaxLine && H(t), e.mustUpdate = e.viewChanged || e.forceUpdate || e.scrollTop != null || e.scrollToPos && (e.scrollToPos.from.line < n.viewFrom || e.scrollToPos.to.line >= n.viewTo) || n.maxLineChanged && t.options.lineWrapping, e.update = e.mustUpdate && new X(t, e.mustUpdate && {
			top: e.scrollTop,
			ensure: e.scrollToPos
		}, e.forceUpdate)
	}

	function Dn(e) {
		e.updatedDisplay = e.mustUpdate && V(e.cm, e.update)
	}

	function Pn(e) {
		var t = e.cm,
			n = t.display;
		e.updatedDisplay && G(t), e.barMeasure = F(t), n.maxLineChanged && !t.options.lineWrapping && (e.adjustWidthTo = nn(t, n.maxLine, n.maxLine.text.length).left + 3, e.maxScrollLeft = Math.max(0, n.sizer.offsetLeft + e.adjustWidthTo + Co - n.scroller.clientWidth));
		if (e.updatedDisplay || e.selectionChanged) e.newSelectionNodes = qt(t)
	}

	function Hn(e) {
		var t = e.cm;
		e.adjustWidthTo != null && (t.display.sizer.style.minWidth = e.adjustWidthTo + "px", e.maxScrollLeft < t.doc.scrollLeft && wr(t, Math.min(t.display.scroller.scrollLeft, e.maxScrollLeft), !0), t.display.maxLineChanged = !1), e.newSelectionNodes && Rt(t, e.newSelectionNodes), e.updatedDisplay && K(t, e.barMeasure), (e.updatedDisplay || e.startHeight != t.doc.height) && I(t, e.barMeasure), e.selectionChanged && Xt(t), t.state.focused && e.updateInput && er(t, e.typing)
	}

	function Bn(e) {
		var t = e.cm,
			n = t.display,
			r = t.doc;
		e.adjustWidthTo != null && Math.abs(e.barMeasure.scrollWidth - t.display.scroller.scrollWidth) > 1 && I(t), e.updatedDisplay && $(t, e.update), n.wheelStartX != null && (e.scrollTop != null || e.scrollLeft != null || e.scrollToPos) && (n.wheelStartX = n.wheelStartY = null);
		if (e.scrollTop != null && (n.scroller.scrollTop != e.scrollTop || e.forceScroll)) {
			var i = Math.max(0, Math.min(n.scroller.scrollHeight - n.scroller.clientHeight, e.scrollTop));
			n.scroller.scrollTop = n.scrollbarV.scrollTop = r.scrollTop = i
		}
		if (e.scrollLeft != null && (n.scroller.scrollLeft != e.scrollLeft || e.forceScroll)) {
			var o = Math.max(0, Math.min(n.scroller.scrollWidth - n.scroller.clientWidth, e.scrollLeft));
			n.scroller.scrollLeft = n.scrollbarH.scrollLeft = r.scrollLeft = o, R(t)
		}
		if (e.scrollToPos) {
			var u = Yr(t, xt(r, e.scrollToPos.from), xt(r, e.scrollToPos.to), e.scrollToPos.margin);
			e.scrollToPos.isCursor && t.state.focused && Gr(t, u)
		}
		var a = e.maybeHiddenMarkers,
			f = e.maybeUnhiddenMarkers;
		if (a)
			for (var l = 0; l < a.length; ++l) a[l].lines.length || yo(a[l], "hide");
		if (f)
			for (var l = 0; l < f.length; ++l) f[l].lines.length && yo(f[l], "unhide");
		n.wrapper.offsetHeight && (r.scrollTop = t.display.scroller.scrollTop), e.updatedDisplay && s && (t.options.lineWrapping && Q(t, e.barMeasure), e.barMeasure.scrollWidth > e.barMeasure.clientWidth && e.barMeasure.scrollWidth < e.barMeasure.clientWidth + 1 && !j(t) && I(t)), e.changeObjs && yo(t, "changes", t, e.changeObjs)
	}

	function jn(e, t) {
		if (e.curOp) return t();
		Ln(e);
		try {
			return t()
		} finally {
			On(e)
		}
	}

	function Fn(e, t) {
		return function () {
			if (e.curOp) return t.apply(e, arguments);
			Ln(e);
			try {
				return t.apply(e, arguments)
			} finally {
				On(e)
			}
		}
	}

	function In(e) {
		return function () {
			if (this.curOp) return e.apply(this, arguments);
			Ln(this);
			try {
				return e.apply(this, arguments)
			} finally {
				On(this)
			}
		}
	}

	function qn(e) {
		return function () {
			var t = this.cm;
			if (!t || t.curOp) return e.apply(this, arguments);
			Ln(t);
			try {
				return e.apply(this, arguments)
			} finally {
				On(t)
			}
		}
	}

	function Rn(e, t, n) {
		this.line = t, this.rest = ts(t), this.size = this.rest ? Ws(Bo(this.rest)) - n + 1 : 1, this.node = this.text = null, this.hidden = is(e, t)
	}

	function Un(e, t, n) {
		var r = [],
			i;
		for (var s = t; s < n; s = i) {
			var o = new Rn(e.doc, qs(e.doc, s), s);
			i = s + o.size, r.push(o)
		}
		return r
	}

	function zn(e, t, n, r) {
		t == null && (t = e.doc.first), n == null && (n = e.doc.first + e.doc.size), r || (r = 0);
		var i = e.display;
		r && n < i.viewTo && (i.updateLineNumbers == null || i.updateLineNumbers > t) && (i.updateLineNumbers = t), e.curOp.viewChanged = !0;
		if (t >= i.viewTo) E && ns(e.doc, t) < i.viewTo && Xn(e);
		else if (n <= i.viewFrom) E && rs(e.doc, n + r) > i.viewFrom ? Xn(e) : (i.viewFrom += r, i.viewTo += r);
		else if (t <= i.viewFrom && n >= i.viewTo) Xn(e);
		else if (t <= i.viewFrom) {
			var s = $n(e, n, n + r, 1);
			s ? (i.view = i.view.slice(s.index), i.viewFrom = s.lineN, i.viewTo += r) : Xn(e)
		} else if (n >= i.viewTo) {
			var s = $n(e, t, t, -1);
			s ? (i.view = i.view.slice(0, s.index), i.viewTo = s.lineN) : Xn(e)
		} else {
			var o = $n(e, t, t, -1),
				u = $n(e, n, n + r, 1);
			o && u ? (i.view = i.view.slice(0, o.index).concat(Un(e, o.lineN, u.lineN)).concat(i.view.slice(u.index)), i.viewTo += r) : Xn(e)
		}
		var a = i.externalMeasured;
		a && (n < a.lineN ? a.lineN += r : t < a.lineN + a.size && (i.externalMeasured = null))
	}

	function Wn(e, t, n) {
		e.curOp.viewChanged = !0;
		var r = e.display,
			i = e.display.externalMeasured;
		i && t >= i.lineN && t < i.lineN + i.size && (r.externalMeasured = null);
		if (t < r.viewFrom || t >= r.viewTo) return;
		var s = r.view[Vn(e, t)];
		if (s.node == null) return;
		var o = s.changes || (s.changes = []);
		Fo(o, n) == -1 && o.push(n)
	}

	function Xn(e) {
		e.display.viewFrom = e.display.viewTo = e.doc.first, e.display.view = [], e.display.viewOffset = 0
	}

	function Vn(e, t) {
		if (t >= e.display.viewTo) return null;
		t -= e.display.viewFrom;
		if (t < 0) return null;
		var n = e.display.view;
		for (var r = 0; r < n.length; r++) {
			t -= n[r].size;
			if (t < 0) return r
		}
	}

	function $n(e, t, n, r) {
		var i = Vn(e, t),
			s, o = e.display.view;
		if (!E || n == e.doc.first + e.doc.size) return {
			index: i,
			lineN: n
		};
		for (var u = 0, a = e.display.viewFrom; u < i; u++) a += o[u].size;
		if (a != t) {
			if (r > 0) {
				if (i == o.length - 1) return null;
				s = a + o[i].size - t, i++
			} else s = a - t;
			t += s, n += s
		}
		while (ns(e.doc, n) != n) {
			if (i == (r < 0 ? 0 : o.length - 1)) return null;
			n += r * o[i - (r < 0 ? 1 : 0)].size, i += r
		}
		return {
			index: i,
			lineN: n
		}
	}

	function Jn(e, t, n) {
		var r = e.display,
			i = r.view;
		i.length == 0 || t >= r.viewTo || n <= r.viewFrom ? (r.view = Un(e, t, n), r.viewFrom = t) : (r.viewFrom > t ? r.view = Un(e, t, r.viewFrom).concat(r.view) : r.viewFrom < t && (r.view = r.view.slice(Vn(e, t))), r.viewFrom = t, r.viewTo < n ? r.view = r.view.concat(Un(e, r.viewTo, n)) : r.viewTo > n && (r.view = r.view.slice(0, Vn(e, n)))), r.viewTo = n
	}

	function Kn(e) {
		var t = e.display.view,
			n = 0;
		for (var r = 0; r < t.length; r++) {
			var i = t[r];
			!i.hidden && (!i.node || i.changes) && ++n
		}
		return n
	}

	function Qn(e) {
		if (e.display.pollingFast) return;
		e.display.poll.set(e.options.pollInterval, function () {
			Zn(e), e.state.focused && Qn(e)
		})
	}

	function Gn(e) {
		function n() {
			var r = Zn(e);
			!r && !t ? (t = !0, e.display.poll.set(60, n)) : (e.display.pollingFast = !1, Qn(e))
		}
		var t = !1;
		e.display.pollingFast = !0, e.display.poll.set(20, n)
	}

	function Zn(e) {
		var t = e.display.input,
			n = e.display.prevInput,
			s = e.doc;
		if (!e.state.focused || gu(t) && !n || rr(e) || e.options.disableInput) return !1;
		e.state.pasteIncoming && e.state.fakedLastChar && (t.value = t.value.substring(0, t.value.length - 1), e.state.fakedLastChar = !1);
		var o = t.value;
		if (o == n && !e.somethingSelected()) return !1;
		if (r && i >= 9 && e.display.inputHasSelection === o || v && /[\uf700-\uf7ff]/.test(o)) return er(e), !1;
		var u = !e.curOp;
		u && Ln(e), e.display.shift = !1, o.charCodeAt(0) == 8203 && s.sel == e.display.selForContextMenu && !n && (n = "​");
		var a = 0,
			f = Math.min(n.length, o.length);
		while (a < f && n.charCodeAt(a) == o.charCodeAt(a)) ++a;
		var l = o.slice(a),
			c = mu(l),
			h = null;
		e.state.pasteIncoming && s.sel.ranges.length > 1 && (Yn && Yn.join("\n") == l ? h = s.sel.ranges.length % Yn.length == 0 && Io(Yn, mu) : c.length == s.sel.ranges.length && (h = Io(c, function (e) {
			return [e]
		})));
		for (var p = s.sel.ranges.length - 1; p >= 0; p--) {
			var d = s.sel.ranges[p],
				m = d.from(),
				g = d.to();
			a < n.length ? m = pt(m.line, m.ch - (n.length - a)) : e.state.overwrite && d.empty() && !e.state.pasteIncoming && (g = pt(g.line, Math.min(qs(s, g.line).text.length, g.ch + Bo(c).length)));
			var y = e.curOp.updateInput,
				b = {
					from: m,
					to: g,
					text: h ? h[p % h.length] : c,
					origin: e.state.pasteIncoming ? "paste" : e.state.cutIncoming ? "cut" : "+input"
				};
			Wr(e.doc, b), wo(e, "inputRead", e, b);
			if (l && !e.state.pasteIncoming && e.options.electricChars && e.options.smartIndent && d.head.ch < 100 && (!p || s.sel.ranges[p - 1].head.line != d.head.line)) {
				var w = e.getModeAt(d.head),
					E = Fr(b);
				if (w.electricChars) {
					for (var S = 0; S < w.electricChars.length; S++)
						if (l.indexOf(w.electricChars.charAt(S)) > -1) {
							ii(e, E.line, "smart");
							break
						}
				} else w.electricInput && w.electricInput.test(qs(s, E.line).text.slice(0, E.ch)) && ii(e, E.line, "smart")
			}
		}
		return ni(e), e.curOp.updateInput = y, e.curOp.typing = !0, o.length > 1e3 || o.indexOf("\n") > -1 ? t.value = e.display.prevInput = "" : e.display.prevInput = o, u && On(e), e.state.pasteIncoming = e.state.cutIncoming = !1, !0
	}

	function er(e, t) {
		var n, s, o = e.doc;
		if (e.somethingSelected()) {
			e.display.prevInput = "";
			var u = o.sel.primary();
			n = yu && (u.to().line - u.from().line > 100 || (s = e.getSelection()).length > 1e3);
			var a = n ? "-" : s || e.getSelection();
			e.display.input.value = a, e.state.focused && jo(e.display.input), r && i >= 9 && (e.display.inputHasSelection = a)
		} else t || (e.display.prevInput = e.display.input.value = "", r && i >= 9 && (e.display.inputHasSelection = null));
		e.display.inaccurateSelection = n
	}

	function tr(e) {
		e.options.readOnly != "nocursor" && (!d || eu() != e.display.input) && e.display.input.focus()
	}

	function nr(e) {
		e.state.focused || (tr(e), Pr(e))
	}

	function rr(e) {
		return e.options.readOnly || e.doc.cantEdit
	}

	function ir(e) {
		function n() {
			e.state.focused && setTimeout(Uo(tr, e), 0)
		}

		function o(t) {
			So(e, t) || ho(t)
		}

		function u(n) {
			if (e.somethingSelected()) Yn = e.getSelections(), t.inaccurateSelection && (t.prevInput = "", t.inaccurateSelection = !1, t.input.value = Yn.join("\n"), jo(t.input));
			else {
				var r = [],
					i = [];
				for (var s = 0; s < e.doc.sel.ranges.length; s++) {
					var o = e.doc.sel.ranges[s].head.line,
						u = {
							anchor: pt(o, 0),
							head: pt(o + 1, 0)
						};
					i.push(u), r.push(e.getRange(u.anchor, u.head))
				}
				n.type == "cut" ? e.setSelections(i, null, Lo) : (t.prevInput = "", t.input.value = r.join("\n"), jo(t.input)), Yn = r
			}
			n.type == "cut" && (e.state.cutIncoming = !0)
		}
		var t = e.display;
		mo(t.scroller, "mousedown", Fn(e, ar)), r && i < 11 ? mo(t.scroller, "dblclick", Fn(e, function (t) {
			if (So(e, t)) return;
			var n = ur(e, t);
			if (!n || vr(e, t) || or(e.display, t)) return;
			fo(t);
			var r = e.findWordAt(n);
			Lt(e.doc, r.anchor, r.head)
		})) : mo(t.scroller, "dblclick", function (t) {
			So(e, t) || fo(t)
		}), mo(t.lineSpace, "selectstart", function (e) {
			or(t, e) || fo(e)
		}), b || mo(t.scroller, "contextmenu", function (t) {
			Br(e, t)
		}), mo(t.scroller, "scroll", function () {
			t.scroller.clientHeight && (br(e, t.scroller.scrollTop), wr(e, t.scroller.scrollLeft, !0), yo(e, "scroll", e))
		}), mo(t.scrollbarV, "scroll", function () {
			t.scroller.clientHeight && br(e, t.scrollbarV.scrollTop)
		}), mo(t.scrollbarH, "scroll", function () {
			t.scroller.clientHeight && wr(e, t.scrollbarH.scrollLeft)
		}), mo(t.scroller, "mousewheel", function (t) {
			xr(e, t)
		}), mo(t.scroller, "DOMMouseScroll", function (t) {
			xr(e, t)
		}), mo(t.scrollbarH, "mousedown", n), mo(t.scrollbarV, "mousedown", n), mo(t.wrapper, "scroll", function () {
			t.wrapper.scrollTop = t.wrapper.scrollLeft = 0
		}), mo(t.input, "keyup", function (t) {
			_r.call(e, t)
		}), mo(t.input, "input", function () {
			r && i >= 9 && e.display.inputHasSelection && (e.display.inputHasSelection = null), Gn(e)
		}), mo(t.input, "keydown", Fn(e, Or)), mo(t.input, "keypress", Fn(e, Dr)), mo(t.input, "focus", Uo(Pr, e)), mo(t.input, "blur", Uo(Hr, e)), e.options.dragDrop && (mo(t.scroller, "dragstart", function (t) {
			yr(e, t)
		}), mo(t.scroller, "dragenter", o), mo(t.scroller, "dragover", o), mo(t.scroller, "drop", Fn(e, gr))), mo(t.scroller, "paste", function (n) {
			if (or(t, n)) return;
			e.state.pasteIncoming = !0, tr(e), Gn(e)
		}), mo(t.input, "paste", function () {
			if (s && !e.state.fakedLastChar && !(new Date - e.state.lastMiddleDown < 200)) {
				var n = t.input.selectionStart,
					r = t.input.selectionEnd;
				t.input.value += "$", t.input.selectionEnd = r, t.input.selectionStart = n, e.state.fakedLastChar = !0
			}
			e.state.pasteIncoming = !0, Gn(e)
		}), mo(t.input, "cut", u), mo(t.input, "copy", u), l && mo(t.sizer, "mouseup", function () {
			eu() == t.input && t.input.blur(), tr(e)
		})
	}

	function sr(e) {
		var t = e.display;
		t.cachedCharWidth = t.cachedTextHeight = t.cachedPaddingH = null, e.setSize()
	}

	function or(e, t) {
		for (var n = po(t); n != e.wrapper; n = n.parentNode)
			if (!n || n.ignoreEvents || n.parentNode == e.sizer && n != e.mover) return !0
	}

	function ur(e, t, n, r) {
		var i = e.display;
		if (!n) {
			var s = po(t);
			if (s == i.scrollbarH || s == i.scrollbarV || s == i.scrollbarFiller || s == i.gutterFiller) return null
		}
		var o, u, a = i.lineSpace.getBoundingClientRect();
		try {
			o = t.clientX - a.left, u = t.clientY - a.top
		} catch (t) {
			return null
		}
		var f = En(e, o, u),
			l;
		if (r && f.xRel == 1 && (l = qs(e.doc, f.line).text).length == f.ch) {
			var c = _o(l, l.length, e.options.tabSize) - l.length;
			f = pt(f.line, Math.max(0, Math.round((o - Yt(e.display).left) / Nn(e.display)) - c))
		}
		return f
	}

	function ar(e) {
		if (So(this, e)) return;
		var t = this,
			n = t.display;
		n.shift = e.shiftKey;
		if (or(n, e)) {
			s || (n.scroller.draggable = !1, setTimeout(function () {
				n.scroller.draggable = !0
			}, 100));
			return
		}
		if (vr(t, e)) return;
		var r = ur(t, e);
		window.focus();
		switch (vo(e)) {
		case 1:
			r ? cr(t, e, r) : po(e) == n.scroller && fo(e);
			break;
		case 2:
			s && (t.state.lastMiddleDown = +(new Date)), r && Lt(t.doc, r), setTimeout(Uo(tr, t), 20), fo(e);
			break;
		case 3:
			b && Br(t, e)
		}
	}

	function cr(e, t, n) {
		setTimeout(Uo(nr, e), 0);
		var r = +(new Date),
			i;
		lr && lr.time > r - 400 && dt(lr.pos, n) == 0 ? i = "triple" : fr && fr.time > r - 400 && dt(fr.pos, n) == 0 ? (i = "double", lr = {
			time: r,
			pos: n
		}) : (i = "single", fr = {
			time: r,
			pos: n
		});
		var s = e.doc.sel,
			o = v ? t.metaKey : t.ctrlKey;
		e.options.dragDrop && fu && !rr(e) && i == "single" && s.contains(n) > -1 && s.somethingSelected() ? hr(e, t, n, o) : pr(e, t, n, i, o)
	}

	function hr(e, t, n, o) {
		var u = e.display,
			a = Fn(e, function (f) {
				s && (u.scroller.draggable = !1), e.state.draggingText = !1, go(document, "mouseup", a), go(u.scroller, "drop", a), Math.abs(t.clientX - f.clientX) + Math.abs(t.clientY - f.clientY) < 10 && (fo(f), o || Lt(e.doc, n), tr(e), r && i == 9 && setTimeout(function () {
					document.body.focus(), tr(e)
				}, 20))
			});
		s && (u.scroller.draggable = !0), e.state.draggingText = a, u.scroller.dragDrop && u.scroller.dragDrop(), mo(document, "mouseup", a), mo(u.scroller, "drop", a)
	}

	function pr(e, t, n, r, i) {
		function p(t) {
			if (dt(h, t) == 0) return;
			h = t;
			if (r == "rect") {
				var i = [],
					s = e.options.tabSize,
					l = _o(qs(o, n.line).text, n.ch, s),
					c = _o(qs(o, t.line).text, t.ch, s),
					p = Math.min(l, c),
					d = Math.max(l, c);
				for (var v = Math.min(n.line, t.line), m = Math.min(e.lastLine(), Math.max(n.line, t.line)); v <= m; v++) {
					var g = qs(o, v).text,
						y = Do(g, p, s);
					p == d ? i.push(new bt(pt(v, y), pt(v, y))) : g.length > y && i.push(new bt(pt(v, y), pt(v, Do(g, d, s))))
				}
				i.length || i.push(new bt(n, n)), Pt(o, wt(f.ranges.slice(0, a).concat(i), a), {
					origin: "*mouse",
					scroll: !1
				}), e.scrollIntoView(t)
			} else {
				var b = u,
					w = b.anchor,
					E = t;
				if (r != "single") {
					if (r == "double") var S = e.findWordAt(t);
					else var S = new bt(pt(t.line, 0), xt(o, pt(t.line + 1, 0)));
					dt(S.anchor, w) > 0 ? (E = S.head, w = gt(b.from(), S.anchor)) : (E = S.anchor, w = mt(b.to(), S.head))
				}
				var i = f.ranges.slice(0);
				i[a] = new bt(xt(o, w), E), Pt(o, wt(i, a), Ao)
			}
		}

		function m(t) {
			var n = ++v,
				i = ur(e, t, !0, r == "rect");
			if (!i) return;
			if (dt(i, h) != 0) {
				nr(e), p(i);
				var u = q(s, o);
				(i.line >= u.to || i.line < u.from) && setTimeout(Fn(e, function () {
					v == n && m(t)
				}), 150)
			} else {
				var a = t.clientY < d.top ? -20 : t.clientY > d.bottom ? 20 : 0;
				a && setTimeout(Fn(e, function () {
					if (v != n) return;
					s.scroller.scrollTop += a, m(t)
				}), 50)
			}
		}

		function g(t) {
			v = Infinity, fo(t), tr(e), go(document, "mousemove", y), go(document, "mouseup", b), o.history.lastSelOrigin = null
		}
		var s = e.display,
			o = e.doc;
		fo(t);
		var u, a, f = o.sel;
		i && !t.shiftKey ? (a = o.sel.contains(n), a > -1 ? u = o.sel.ranges[a] : u = new bt(n, n)) : u = o.sel.primary();
		if (t.altKey) r = "rect", i || (u = new bt(n, n)), n = ur(e, t, !0, !0), a = -1;
		else if (r == "double") {
			var l = e.findWordAt(n);
			e.display.shift || o.extend ? u = kt(o, u, l.anchor, l.head) : u = l
		} else if (r == "triple") {
			var c = new bt(pt(n.line, 0), xt(o, pt(n.line + 1, 0)));
			e.display.shift || o.extend ? u = kt(o, u, c.anchor, c.head) : u = c
		} else u = kt(o, u, n);
		i ? a > -1 ? Ot(o, a, u, Ao) : (a = o.sel.ranges.length, Pt(o, wt(o.sel.ranges.concat([u]), a), {
			scroll: !1,
			origin: "*mouse"
		})) : (a = 0, Pt(o, new yt([u], 0), Ao), f = o.sel);
		var h = n,
			d = s.wrapper.getBoundingClientRect(),
			v = 0,
			y = Fn(e, function (e) {
				vo(e) ? m(e) : g(e)
			}),
			b = Fn(e, g);
		mo(document, "mousemove", y), mo(document, "mouseup", b)
	}

	function dr(e, t, n, r, i) {
		try {
			var s = t.clientX,
				o = t.clientY
		} catch (t) {
			return !1
		}
		if (s >= Math.floor(e.display.gutters.getBoundingClientRect().right)) return !1;
		r && fo(t);
		var u = e.display,
			a = u.lineDiv.getBoundingClientRect();
		if (o > a.bottom || !To(e, n)) return co(t);
		o -= a.top - u.viewOffset;
		for (var f = 0; f < e.options.gutters.length; ++f) {
			var l = u.gutters.childNodes[f];
			if (l && l.getBoundingClientRect().right >= s) {
				var c = Xs(e.doc, o),
					h = e.options.gutters[f];
				return i(e, n, e, c, h, t), co(t)
			}
		}
	}

	function vr(e, t) {
		return dr(e, t, "gutterClick", !0, wo)
	}

	function gr(e) {
		var t = this;
		if (So(t, e) || or(t.display, e)) return;
		fo(e), r && (mr = +(new Date));
		var n = ur(t, e, !0),
			i = e.dataTransfer.files;
		if (!n || rr(t)) return;
		if (i && i.length && window.FileReader && window.File) {
			var s = i.length,
				o = Array(s),
				u = 0,
				a = function (e, r) {
					var i = new FileReader;
					i.onload = Fn(t, function () {
						o[r] = i.result;
						if (++u == s) {
							n = xt(t.doc, n);
							var e = {
								from: n,
								to: n,
								text: mu(o.join("\n")),
								origin: "paste"
							};
							Wr(t.doc, e), Dt(t.doc, Et(n, Fr(e)))
						}
					}), i.readAsText(e)
				};
			for (var f = 0; f < s; ++f) a(i[f], f)
		} else {
			if (t.state.draggingText && t.doc.sel.contains(n) > -1) {
				t.state.draggingText(e), setTimeout(Uo(tr, t), 20);
				return
			}
			try {
				var o = e.dataTransfer.getData("Text");
				if (o) {
					if (t.state.draggingText && (v ? !e.metaKey : !e.ctrlKey)) var l = t.listSelections();
					Ht(t.doc, Et(n, n));
					if (l)
						for (var f = 0; f < l.length; ++f) Qr(t.doc, "", l[f].anchor, l[f].head, "drag");
					t.replaceSelection(o, "around", "paste"), tr(t)
				}
			} catch (e) {}
		}
	}

	function yr(e, t) {
		if (r && (!e.state.draggingText || +(new Date) - mr < 100)) {
			ho(t);
			return
		}
		if (So(e, t) || or(e.display, t)) return;
		t.dataTransfer.setData("Text", e.getSelection());
		if (t.dataTransfer.setDragImage && !f) {
			var n = Ko("img", null, null, "position: fixed; left: 0; top: 0;");
			n.src = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==", a && (n.width = n.height = 1, e.display.wrapper.appendChild(n), n._top = n.offsetTop), t.dataTransfer.setDragImage(n, 0, 0), a && n.parentNode.removeChild(n)
		}
	}

	function br(t, n) {
		if (Math.abs(t.doc.scrollTop - n) < 2) return;
		t.doc.scrollTop = n, e || J(t, {
			top: n
		}), t.display.scroller.scrollTop != n && (t.display.scroller.scrollTop = n), t.display.scrollbarV.scrollTop != n && (t.display.scrollbarV.scrollTop = n), e && J(t), Vt(t, 100)
	}

	function wr(e, t, n) {
		if (n ? t == e.doc.scrollLeft : Math.abs(e.doc.scrollLeft - t) < 2) return;
		t = Math.min(t, e.display.scroller.scrollWidth - e.display.scroller.clientWidth), e.doc.scrollLeft = t, R(e), e.display.scroller.scrollLeft != t && (e.display.scroller.scrollLeft = t), e.display.scrollbarH.scrollLeft != t && (e.display.scrollbarH.scrollLeft = t)
	}

	function xr(t, n) {
		var r = n.wheelDeltaX,
			i = n.wheelDeltaY;
		r == null && n.detail && n.axis == n.HORIZONTAL_AXIS && (r = n.detail), i == null && n.detail && n.axis == n.VERTICAL_AXIS ? i = n.detail : i == null && (i = n.wheelDelta);
		var o = t.display,
			u = o.scroller;
		if (!(r && u.scrollWidth > u.clientWidth || i && u.scrollHeight > u.clientHeight)) return;
		if (i && v && s) e: for (var f = n.target, l = o.view; f != u; f = f.parentNode)
			for (var c = 0; c < l.length; c++)
				if (l[c].node == f) {
					t.display.currentWheelTarget = f;
					break e
				}
		if (r && !e && !a && Sr != null) {
			i && br(t, Math.max(0, Math.min(u.scrollTop + i * Sr, u.scrollHeight - u.clientHeight))), wr(t, Math.max(0, Math.min(u.scrollLeft + r * Sr, u.scrollWidth - u.clientWidth))), fo(n), o.wheelStartX = null;
			return
		}
		if (i && Sr != null) {
			var h = i * Sr,
				p = t.doc.scrollTop,
				d = p + o.wrapper.clientHeight;
			h < 0 ? p = Math.max(0, p + h - 50) : d = Math.min(t.doc.height, d + h + 50), J(t, {
				top: p,
				bottom: d
			})
		}
		Er < 20 && (o.wheelStartX == null ? (o.wheelStartX = u.scrollLeft, o.wheelStartY = u.scrollTop, o.wheelDX = r, o.wheelDY = i, setTimeout(function () {
			if (o.wheelStartX == null) return;
			var e = u.scrollLeft - o.wheelStartX,
				t = u.scrollTop - o.wheelStartY,
				n = t && o.wheelDY && t / o.wheelDY || e && o.wheelDX && e / o.wheelDX;
			o.wheelStartX = o.wheelStartY = null;
			if (!n) return;
			Sr = (Sr * Er + n) / (Er + 1), ++Er
		}, 200)) : (o.wheelDX += r, o.wheelDY += i))
	}

	function Tr(e, t, n) {
		if (typeof t == "string") {
			t = wi[t];
			if (!t) return !1
		}
		e.display.pollingFast && Zn(e) && (e.display.pollingFast = !1);
		var r = e.display.shift,
			i = !1;
		try {
			rr(e) && (e.state.suppressEdits = !0), n && (e.display.shift = !1), i = t(e) != ko
		} finally {
			e.display.shift = r, e.state.suppressEdits = !1
		}
		return i
	}

	function Nr(e) {
		var t = e.state.keyMaps.slice(0);
		return e.options.extraKeys && t.push(e.options.extraKeys), t.push(e.options.keyMap), t
	}

	function kr(e, t) {
		var n = Si(e.options.keyMap),
			r = n.auto;
		clearTimeout(Cr), r && !Ti(t) && (Cr = setTimeout(function () {
			Si(e.options.keyMap) == n && (e.options.keyMap = r.call ? r.call(null, e) : r, A(e))
		}, 50));
		var i = Ni(t, !0),
			s = !1;
		if (!i) return !1;
		var o = Nr(e);
		return t.shiftKey ? s = xi("Shift-" + i, o, function (t) {
			return Tr(e, t, !0)
		}) || xi(i, o, function (t) {
			if (typeof t == "string" ? /^go[A-Z]/.test(t) : t.motion) return Tr(e, t)
		}) : s = xi(i, o, function (t) {
			return Tr(e, t)
		}), s && (fo(t), Xt(e), wo(e, "keyHandled", e, i, t)), s
	}

	function Lr(e, t, n) {
		var r = xi("'" + n + "'", Nr(e), function (t) {
			return Tr(e, t, !0)
		});
		return r && (fo(t), Xt(e), wo(e, "keyHandled", e, "'" + n + "'", t)), r
	}

	function Or(e) {
		var t = this;
		nr(t);
		if (So(t, e)) return;
		r && i < 11 && e.keyCode == 27 && (e.returnValue = !1);
		var n = e.keyCode;
		t.display.shift = n == 16 || e.shiftKey;
		var s = kr(t, e);
		a && (Ar = s ? n : null, !s && n == 88 && !yu && (v ? e.metaKey : e.ctrlKey) && t.replaceSelection("", null, "cut")), n == 18 && !/\bCodeMirror-crosshair\b/.test(t.display.lineDiv.className) && Mr(t)
	}

	function Mr(e) {
		function n(e) {
			if (e.keyCode == 18 || !e.altKey) nu(t, "CodeMirror-crosshair"), go(document, "keyup", n), go(document, "mouseover", n)
		}
		var t = e.display.lineDiv;
		ru(t, "CodeMirror-crosshair"), mo(document, "keyup", n), mo(document, "mouseover", n)
	}

	function _r(e) {
		e.keyCode == 16 && (this.doc.sel.shift = !1), So(this, e)
	}

	function Dr(e) {
		var t = this;
		if (So(t, e) || e.ctrlKey && !e.altKey || v && e.metaKey) return;
		var n = e.keyCode,
			s = e.charCode;
		if (a && n == Ar) {
			Ar = null, fo(e);
			return
		}
		if ((a && (!e.which || e.which < 10) || l) && kr(t, e)) return;
		var o = String.fromCharCode(s == null ? n : s);
		if (Lr(t, e, o)) return;
		r && i >= 9 && (t.display.inputHasSelection = null), Gn(t)
	}

	function Pr(e) {
		if (e.options.readOnly == "nocursor") return;
		e.state.focused || (yo(e, "focus", e), e.state.focused = !0, ru(e.display.wrapper, "CodeMirror-focused"), !e.curOp && e.display.selForContextMenu != e.doc.sel && (er(e), s && setTimeout(Uo(er, e, !0), 0))), Qn(e), Xt(e)
	}

	function Hr(e) {
		e.state.focused && (yo(e, "blur", e), e.state.focused = !1, nu(e.display.wrapper, "CodeMirror-focused")), clearInterval(e.display.blinker), setTimeout(function () {
			e.state.focused || (e.display.shift = !1)
		}, 150)
	}

	function Br(e, t) {
		function h() {
			if (n.input.selectionStart != null) {
				var t = e.somethingSelected(),
					r = n.input.value = "​" + (t ? n.input.value : "");
				n.prevInput = t ? "" : "​", n.input.selectionStart = 1, n.input.selectionEnd = r.length, n.selForContextMenu = e.doc.sel
			}
		}

		function p() {
			n.inputDiv.style.position = "relative", n.input.style.cssText = l, r && i < 9 && (n.scrollbarV.scrollTop = n.scroller.scrollTop = u), Qn(e);
			if (n.input.selectionStart != null) {
				(!r || r && i < 9) && h();
				var t = 0,
					s = function () {
						n.selForContextMenu == e.doc.sel && n.input.selectionStart == 0 ? Fn(e, wi.selectAll)(e) : t++ < 10 ? n.detectingSelectAll = setTimeout(s, 500) : er(e)
					};
				n.detectingSelectAll = setTimeout(s, 200)
			}
		}
		if (So(e, t, "contextmenu")) return;
		var n = e.display;
		if (or(n, t) || jr(e, t)) return;
		var o = ur(e, t),
			u = n.scroller.scrollTop;
		if (!o || a) return;
		var f = e.options.resetSelectionOnContextMenu;
		f && e.doc.sel.contains(o) == -1 && Fn(e, Pt)(e.doc, Et(o), Lo);
		var l = n.input.style.cssText;
		n.inputDiv.style.position = "absolute", n.input.style.cssText = "position: fixed; width: 30px; height: 30px; top: " + (t.clientY - 5) + "px; left: " + (t.clientX - 5) + "px; z-index: 1000; background: " + (r ? "rgba(255, 255, 255, .05)" : "transparent") + "; outline: none; border-width: 0; outline: none; overflow: hidden; opacity: .05; filter: alpha(opacity=5);";
		if (s) var c = window.scrollY;
		tr(e), s && window.scrollTo(null, c), er(e), e.somethingSelected() || (n.input.value = n.prevInput = " "), n.selForContextMenu = e.doc.sel, clearTimeout(n.detectingSelectAll), r && i >= 9 && h();
		if (b) {
			ho(t);
			var d = function () {
				go(window, "mouseup", d), setTimeout(p, 20)
			};
			mo(window, "mouseup", d)
		} else setTimeout(p, 50)
	}

	function jr(e, t) {
		return To(e, "gutterContextMenu") ? dr(e, t, "gutterContextMenu", !1, yo) : !1
	}

	function Ir(e, t) {
		if (dt(e, t.from) < 0) return e;
		if (dt(e, t.to) <= 0) return Fr(t);
		var n = e.line + t.text.length - (t.to.line - t.from.line) - 1,
			r = e.ch;
		return e.line == t.to.line && (r += Fr(t).ch - t.to.ch), pt(n, r)
	}

	function qr(e, t) {
		var n = [];
		for (var r = 0; r < e.sel.ranges.length; r++) {
			var i = e.sel.ranges[r];
			n.push(new bt(Ir(i.anchor, t), Ir(i.head, t)))
		}
		return wt(n, e.sel.primIndex)
	}

	function Rr(e, t, n) {
		return e.line == t.line ? pt(n.line, e.ch - t.ch + n.ch) : pt(n.line + (e.line - t.line), e.ch)
	}

	function Ur(e, t, n) {
		var r = [],
			i = pt(e.first, 0),
			s = i;
		for (var o = 0; o < t.length; o++) {
			var u = t[o],
				a = Rr(u.from, i, s),
				f = Rr(Fr(u), i, s);
			i = u.to, s = f;
			if (n == "around") {
				var l = e.sel.ranges[o],
					c = dt(l.head, l.anchor) < 0;
				r[o] = new bt(c ? f : a, c ? a : f)
			} else r[o] = new bt(a, a)
		}
		return new yt(r, e.sel.primIndex)
	}

	function zr(e, t, n) {
		var r = {
			canceled: !1,
			from: t.from,
			to: t.to,
			text: t.text,
			origin: t.origin,
			cancel: function () {
				this.canceled = !0
			}
		};
		return n && (r.update = function (t, n, r, i) {
			t && (this.from = xt(e, t)), n && (this.to = xt(e, n)), r && (this.text = r), i !== undefined && (this.origin = i)
		}), yo(e, "beforeChange", e, r), e.cm && yo(e.cm, "beforeChange", e.cm, r), r.canceled ? null : {
			from: r.from,
			to: r.to,
			text: r.text,
			origin: r.origin
		}
	}

	function Wr(e, t, n) {
		if (e.cm) {
			if (!e.cm.curOp) return Fn(e.cm, Wr)(e, t, n);
			if (e.cm.state.suppressEdits) return
		}
		if (To(e, "beforeChange") || e.cm && To(e.cm, "beforeChange")) {
			t = zr(e, t, !0);
			if (!t) return
		}
		var r = w && !n && Wi(e, t.from, t.to);
		if (r)
			for (var i = r.length - 1; i >= 0; --i) Xr(e, {
				from: r[i].from,
				to: r[i].to,
				text: i ? [""] : t.text
			});
		else Xr(e, t)
	}

	function Xr(e, t) {
		if (t.text.length == 1 && t.text[0] == "" && dt(t.from, t.to) == 0) return;
		var n = qr(e, t);
		Ys(e, t, n, e.cm ? e.cm.curOp.id : NaN), Jr(e, t, n, Ri(e, t));
		var r = [];
		Fs(e, function (e, n) {
			!n && Fo(r, e.history) == -1 && (ao(e.history, t), r.push(e.history)), Jr(e, t, null, Ri(e, t))
		})
	}

	function Vr(e, t, n) {
		if (e.cm && e.cm.state.suppressEdits) return;
		var r = e.history,
			i, s = e.sel,
			o = t == "undo" ? r.done : r.undone,
			u = t == "undo" ? r.undone : r.done;
		for (var a = 0; a < o.length; a++) {
			i = o[a];
			if (n ? i.ranges && !i.equals(e.sel) : !i.ranges) break
		}
		if (a == o.length) return;
		r.lastOrigin = r.lastSelOrigin = null;
		for (;;) {
			i = o.pop();
			if (!i.ranges) break;
			to(i, u);
			if (n && !i.equals(e.sel)) {
				Pt(e, i, {
					clearRedo: !1
				});
				return
			}
			s = i
		}
		var f = [];
		to(s, u), u.push({
			changes: f,
			generation: r.generation
		}), r.generation = i.generation || ++r.maxGeneration;
		var l = To(e, "beforeChange") || e.cm && To(e.cm, "beforeChange");
		for (var a = i.changes.length - 1; a >= 0; --a) {
			var c = i.changes[a];
			c.origin = t;
			if (l && !zr(e, c, !1)) {
				o.length = 0;
				return
			}
			f.push(Ks(e, c));
			var h = a ? qr(e, c) : Bo(o);
			Jr(e, c, h, zi(e, c)), !a && e.cm && e.cm.scrollIntoView({
				from: c.from,
				to: Fr(c)
			});
			var p = [];
			Fs(e, function (e, t) {
				!t && Fo(p, e.history) == -1 && (ao(e.history, c), p.push(e.history)), Jr(e, c, null, zi(e, c))
			})
		}
	}

	function $r(e, t) {
		if (t == 0) return;
		e.first += t, e.sel = new yt(Io(e.sel.ranges, function (e) {
			return new bt(pt(e.anchor.line + t, e.anchor.ch), pt(e.head.line + t, e.head.ch))
		}), e.sel.primIndex);
		if (e.cm) {
			zn(e.cm, e.first, e.first - t, t);
			for (var n = e.cm.display, r = n.viewFrom; r < n.viewTo; r++) Wn(e.cm, r, "gutter")
		}
	}

	function Jr(e, t, n, r) {
		if (e.cm && !e.cm.curOp) return Fn(e.cm, Jr)(e, t, n, r);
		if (t.to.line < e.first) {
			$r(e, t.text.length - 1 - (t.to.line - t.from.line));
			return
		}
		if (t.from.line > e.lastLine()) return;
		if (t.from.line < e.first) {
			var i = t.text.length - 1 - (e.first - t.from.line);
			$r(e, i), t = {
				from: pt(e.first, 0),
				to: pt(t.to.line + i, t.to.ch),
				text: [Bo(t.text)],
				origin: t.origin
			}
		}
		var s = e.lastLine();
		t.to.line > s && (t = {
			from: t.from,
			to: pt(s, qs(e, s).text.length),
			text: [t.text[0]],
			origin: t.origin
		}), t.removed = Rs(e, t.from, t.to), n || (n = qr(e, t)), e.cm ? Kr(e.cm, t, r) : Ms(e, t, r), Ht(e, n, Lo)
	}

	function Kr(e, t, n) {
		var r = e.doc,
			i = e.display,
			s = t.from,
			o = t.to,
			u = !1,
			a = s.line;
		e.options.lineWrapping || (a = Ws(es(qs(r, s.line))), r.iter(a, o.line + 1, function (e) {
			if (e == i.maxLine) return u = !0, !0
		})), r.sel.contains(t.from, t.to) > -1 && xo(e), Ms(r, t, n, k(e)), e.options.lineWrapping || (r.iter(a, s.line + t.text.length, function (e) {
			var t = P(e);
			t > i.maxLineLength && (i.maxLine = e, i.maxLineLength = t, i.maxLineChanged = !0, u = !1)
		}), u && (e.curOp.updateMaxLine = !0)), r.frontier = Math.min(r.frontier, s.line), Vt(e, 400);
		var f = t.text.length - (o.line - s.line) - 1;
		s.line == o.line && t.text.length == 1 && !Os(e.doc, t) ? Wn(e, s.line, "text") : zn(e, s.line, o.line + 1, f);
		var l = To(e, "changes"),
			c = To(e, "change");
		if (c || l) {
			var h = {
				from: s,
				to: o,
				text: t.text,
				removed: t.removed,
				origin: t.origin
			};
			c && wo(e, "change", e, h), l && (e.curOp.changeObjs || (e.curOp.changeObjs = [])).push(h)
		}
		e.display.selForContextMenu = null
	}

	function Qr(e, t, n, r, i) {
		r || (r = n);
		if (dt(r, n) < 0) {
			var s = r;
			r = n, n = s
		}
		typeof t == "string" && (t = mu(t)), Wr(e, {
			from: n,
			to: r,
			text: t,
			origin: i
		})
	}

	function Gr(e, t) {
		var n = e.display,
			r = n.sizer.getBoundingClientRect(),
			i = null;
		t.top + r.top < 0 ? i = !0 : t.bottom + r.top > (window.innerHeight || document.documentElement.clientHeight) && (i = !1);
		if (i != null && !h) {
			var s = Ko("div", "​", null, "position: absolute; top: " + (t.top - n.viewOffset - Qt(e.display)) + "px; height: " + (t.bottom - t.top + Co) + "px; left: " + t.left + "px; width: 2px;");
			e.display.lineSpace.appendChild(s), s.scrollIntoView(i), e.display.lineSpace.removeChild(s)
		}
	}

	function Yr(e, t, n, r) {
		r == null && (r = 0);
		for (var i = 0; i < 5; i++) {
			var s = !1,
				o = yn(e, t),
				u = !n || n == t ? o : yn(e, n),
				a = ei(e, Math.min(o.left, u.left), Math.min(o.top, u.top) - r, Math.max(o.left, u.left), Math.max(o.bottom, u.bottom) + r),
				f = e.doc.scrollTop,
				l = e.doc.scrollLeft;
			a.scrollTop != null && (br(e, a.scrollTop), Math.abs(e.doc.scrollTop - f) > 1 && (s = !0)), a.scrollLeft != null && (wr(e, a.scrollLeft), Math.abs(e.doc.scrollLeft - l) > 1 && (s = !0));
			if (!s) return o
		}
	}

	function Zr(e, t, n, r, i) {
		var s = ei(e, t, n, r, i);
		s.scrollTop != null && br(e, s.scrollTop), s.scrollLeft != null && wr(e, s.scrollLeft)
	}

	function ei(e, t, n, r, i) {
		var s = e.display,
			o = Tn(e.display);
		n < 0 && (n = 0);
		var u = e.curOp && e.curOp.scrollTop != null ? e.curOp.scrollTop : s.scroller.scrollTop,
			a = s.scroller.clientHeight - Co,
			f = {};
		i - n > a && (i = n + a);
		var l = e.doc.height + Gt(s),
			c = n < o,
			h = i > l - o;
		if (n < u) f.scrollTop = c ? 0 : n;
		else if (i > u + a) {
			var p = Math.min(n, (h ? l : i) - a);
			p != u && (f.scrollTop = p)
		}
		var d = e.curOp && e.curOp.scrollLeft != null ? e.curOp.scrollLeft : s.scroller.scrollLeft,
			v = s.scroller.clientWidth - Co - s.gutters.offsetWidth,
			m = r - t > v;
		return m && (r = t + v), t < 10 ? f.scrollLeft = 0 : t < d ? f.scrollLeft = Math.max(0, t - (m ? 0 : 10)) : r > v + d - 3 && (f.scrollLeft = r + (m ? 0 : 10) - v), f
	}

	function ti(e, t, n) {
		(t != null || n != null) && ri(e), t != null && (e.curOp.scrollLeft = (e.curOp.scrollLeft == null ? e.doc.scrollLeft : e.curOp.scrollLeft) + t), n != null && (e.curOp.scrollTop = (e.curOp.scrollTop == null ? e.doc.scrollTop : e.curOp.scrollTop) + n)
	}

	function ni(e) {
		ri(e);
		var t = e.getCursor(),
			n = t,
			r = t;
		e.options.lineWrapping || (n = t.ch ? pt(t.line, t.ch - 1) : t, r = pt(t.line, t.ch + 1)), e.curOp.scrollToPos = {
			from: n,
			to: r,
			margin: e.options.cursorScrollMargin,
			isCursor: !0
		}
	}

	function ri(e) {
		var t = e.curOp.scrollToPos;
		if (t) {
			e.curOp.scrollToPos = null;
			var n = bn(e, t.from),
				r = bn(e, t.to),
				i = ei(e, Math.min(n.left, r.left), Math.min(n.top, r.top) - t.margin, Math.max(n.right, r.right), Math.max(n.bottom, r.bottom) + t.margin);
			e.scrollTo(i.scrollLeft, i.scrollTop)
		}
	}

	function ii(e, t, n, r) {
		var i = e.doc,
			s;
		n == null && (n = "add"), n == "smart" && (i.mode.indent ? s = Kt(e, t) : n = "prev");
		var o = e.options.tabSize,
			u = qs(i, t),
			a = _o(u.text, null, o);
		u.stateAfter && (u.stateAfter = null);
		var f = u.text.match(/^\s*/)[0],
			l;
		if (!r && !/\S/.test(u.text)) l = 0, n = "not";
		else if (n == "smart") {
			l = i.mode.indent(s, u.text.slice(f.length), u.text);
			if (l == ko || l > 150) {
				if (!r) return;
				n = "prev"
			}
		}
		n == "prev" ? t > i.first ? l = _o(qs(i, t - 1).text, null, o) : l = 0 : n == "add" ? l = a + e.options.indentUnit : n == "subtract" ? l = a - e.options.indentUnit : typeof n == "number" && (l = a + n), l = Math.max(0, l);
		var c = "",
			h = 0;
		if (e.options.indentWithTabs)
			for (var p = Math.floor(l / o); p; --p) h += o, c += "	";
		h < l && (c += Ho(l - h));
		if (c != f) Qr(i, c, pt(t, 0), pt(t, f.length), "+input");
		else
			for (var p = 0; p < i.sel.ranges.length; p++) {
				var d = i.sel.ranges[p];
				if (d.head.line == t && d.head.ch < f.length) {
					var h = pt(t, f.length);
					Ot(i, p, new bt(h, h));
					break
				}
			}
		u.stateAfter = null
	}

	function si(e, t, n, r) {
		var i = t,
			s = t;
		return typeof t == "number" ? s = qs(e, St(e, t)) : i = Ws(t), i == null ? null : (r(s, i) && e.cm && Wn(e.cm, i, n), s)
	}

	function oi(e, t) {
		var n = e.doc.sel.ranges,
			r = [];
		for (var i = 0; i < n.length; i++) {
			var s = t(n[i]);
			while (r.length && dt(s.from, Bo(r).to) <= 0) {
				var o = r.pop();
				if (dt(o.from, s.from) < 0) {
					s.from = o.from;
					break
				}
			}
			r.push(s)
		}
		jn(e, function () {
			for (var t = r.length - 1; t >= 0; t--) Qr(e.doc, "", r[t].from, r[t].to, "+delete");
			ni(e)
		})
	}

	function ui(e, t, n, r, i) {
		function l() {
			var t = s + n;
			return t < e.first || t >= e.first + e.size ? f = !1 : (s = t, a = qs(e, t))
		}

		function c(e) {
			var t = (i ? Pu : Hu)(a, o, n, !0);
			if (t == null) {
				if (!!e || !l()) return f = !1;
				i ? o = (n < 0 ? Cu : Nu)(a) : o = n < 0 ? a.text.length : 0
			} else o = t;
			return !0
		}
		var s = t.line,
			o = t.ch,
			u = n,
			a = qs(e, s),
			f = !0;
		if (r == "char") c();
		else if (r == "column") c(!0);
		else if (r == "word" || r == "group") {
			var h = null,
				p = r == "group",
				d = e.cm && e.cm.getHelper(t, "wordChars");
			for (var v = !0;; v = !1) {
				if (n < 0 && !c(!v)) break;
				var m = a.text.charAt(o) || "\n",
					g = Xo(m, d) ? "w" : p && m == "\n" ? "n" : !p || /\s/.test(m) ? null : "p";
				p && !v && !g && (g = "s");
				if (h && h != g) {
					n < 0 && (n = 1, c());
					break
				}
				g && (h = g);
				if (n > 0 && !c(!v)) break
			}
		}
		var y = It(e, pt(s, o), u, !0);
		return f || (y.hitSide = !0), y
	}

	function ai(e, t, n, r) {
		var i = e.doc,
			s = t.left,
			o;
		if (r == "page") {
			var u = Math.min(e.display.wrapper.clientHeight, window.innerHeight || document.documentElement.clientHeight);
			o = t.top + n * (u - (n < 0 ? 1.5 : .5) * Tn(e.display))
		} else r == "line" && (o = n > 0 ? t.bottom + 3 : t.top - 3);
		for (;;) {
			var a = En(e, s, o);
			if (!a.outside) break;
			if (n < 0 ? o <= 0 : o >= i.height) {
				a.hitSide = !0;
				break
			}
			o += n * 5
		}
		return a
	}

	function ci(e, t, n, r) {
		S.defaults[e] = t, n && (li[e] = r ? function (e, t, r) {
			r != hi && n(e, t, r)
		} : n)
	}

	function Si(e) {
		return typeof e == "string" ? Ei[e] : e
	}

	function Ai(e, t, n, r, i) {
		if (r && r.shared) return Mi(e, t, n, r, i);
		if (e.cm && !e.cm.curOp) return Fn(e.cm, Ai)(e, t, n, r, i);
		var s = new ki(e, i),
			o = dt(t, n);
		r && Ro(r, s, !1);
		if (o > 0 || o == 0 && s.clearWhenEmpty !== !1) return s;
		s.replacedWith && (s.collapsed = !0, s.widgetNode = Ko("span", [s.replacedWith], "CodeMirror-widget"), r.handleMouseEvents || (s.widgetNode.ignoreEvents = !0), r.insertLeft && (s.widgetNode.insertLeft = !0));
		if (s.collapsed) {
			if (Zi(e, t.line, t, n, s) || t.line != n.line && Zi(e, n.line, t, n, s)) throw new Error("Inserting collapsed marker partially overlapping an existing one");
			E = !0
		}
		s.addToHistory && Ys(e, {
			from: t,
			to: n,
			origin: "markText"
		}, e.sel, NaN);
		var u = t.line,
			a = e.cm,
			f;
		e.iter(u, n.line + 1, function (e) {
			a && s.collapsed && !a.options.lineWrapping && es(e) == a.display.maxLine && (f = !0), s.collapsed && u != t.line && zs(e, 0), Fi(e, new Hi(s, u == t.line ? t.ch : null, u == n.line ? n.ch : null)), ++u
		}), s.collapsed && e.iter(t.line, n.line + 1, function (t) {
			is(e, t) && zs(t, 0)
		}), s.clearOnEnter && mo(s, "beforeCursorEnter", function () {
			s.clear()
		}), s.readOnly && (w = !0, (e.history.done.length || e.history.undone.length) && e.clearHistory()), s.collapsed && (s.id = ++Li, s.atomic = !0);
		if (a) {
			f && (a.curOp.updateMaxLine = !0);
			if (s.collapsed) zn(a, t.line, n.line + 1);
			else if (s.className || s.title || s.startStyle || s.endStyle)
				for (var l = t.line; l <= n.line; l++) Wn(a, l, "text");
			s.atomic && jt(a.doc), wo(a, "markerAdded", a, s)
		}
		return s
	}

	function Mi(e, t, n, r, i) {
		r = Ro(r), r.shared = !1;
		var s = [Ai(e, t, n, r, i)],
			o = s[0],
			u = r.widgetNode;
		return Fs(e, function (e) {
			u && (r.widgetNode = u.cloneNode(!0)), s.push(Ai(e, xt(e, t), xt(e, n), r, i));
			for (var a = 0; a < e.linked.length; ++a)
				if (e.linked[a].isParent) return;
			o = Bo(s)
		}), new Oi(s, o)
	}

	function _i(e) {
		return e.findMarks(pt(e.first, 0), e.clipPos(pt(e.lastLine())), function (e) {
			return e.parent
		})
	}

	function Di(e, t) {
		for (var n = 0; n < t.length; n++) {
			var r = t[n],
				i = r.find(),
				s = e.clipPos(i.from),
				o = e.clipPos(i.to);
			if (dt(s, o)) {
				var u = Ai(e, s, o, r.primary, r.primary.type);
				r.markers.push(u), u.parent = r
			}
		}
	}

	function Pi(e) {
		for (var t = 0; t < e.length; t++) {
			var n = e[t],
				r = [n.primary.doc];
			Fs(n.primary.doc, function (e) {
				r.push(e)
			});
			for (var i = 0; i < n.markers.length; i++) {
				var s = n.markers[i];
				Fo(r, s.doc) == -1 && (s.parent = null, n.markers.splice(i--, 1))
			}
		}
	}

	function Hi(e, t, n) {
		this.marker = e, this.from = t, this.to = n
	}

	function Bi(e, t) {
		if (e)
			for (var n = 0; n < e.length; ++n) {
				var r = e[n];
				if (r.marker == t) return r
			}
	}

	function ji(e, t) {
		for (var n, r = 0; r < e.length; ++r) e[r] != t && (n || (n = [])).push(e[r]);
		return n
	}

	function Fi(e, t) {
		e.markedSpans = e.markedSpans ? e.markedSpans.concat([t]) : [t], t.marker.attachLine(e)
	}

	function Ii(e, t, n) {
		if (e)
			for (var r = 0, i; r < e.length; ++r) {
				var s = e[r],
					o = s.marker,
					u = s.from == null || (o.inclusiveLeft ? s.from <= t : s.from < t);
				if (u || s.from == t && o.type == "bookmark" && (!n || !s.marker.insertLeft)) {
					var a = s.to == null || (o.inclusiveRight ? s.to >= t : s.to > t);
					(i || (i = [])).push(new Hi(o, s.from, a ? null : s.to))
				}
			}
		return i
	}

	function qi(e, t, n) {
		if (e)
			for (var r = 0, i; r < e.length; ++r) {
				var s = e[r],
					o = s.marker,
					u = s.to == null || (o.inclusiveRight ? s.to >= t : s.to > t);
				if (u || s.from == t && o.type == "bookmark" && (!n || s.marker.insertLeft)) {
					var a = s.from == null || (o.inclusiveLeft ? s.from <= t : s.from < t);
					(i || (i = [])).push(new Hi(o, a ? null : s.from - t, s.to == null ? null : s.to - t))
				}
			}
		return i
	}

	function Ri(e, t) {
		var n = Nt(e, t.from.line) && qs(e, t.from.line).markedSpans,
			r = Nt(e, t.to.line) && qs(e, t.to.line).markedSpans;
		if (!n && !r) return null;
		var i = t.from.ch,
			s = t.to.ch,
			o = dt(t.from, t.to) == 0,
			u = Ii(n, i, o),
			a = qi(r, s, o),
			f = t.text.length == 1,
			l = Bo(t.text).length + (f ? i : 0);
		if (u)
			for (var c = 0; c < u.length; ++c) {
				var h = u[c];
				if (h.to == null) {
					var p = Bi(a, h.marker);
					p ? f && (h.to = p.to == null ? null : p.to + l) : h.to = i
				}
			}
		if (a)
			for (var c = 0; c < a.length; ++c) {
				var h = a[c];
				h.to != null && (h.to += l);
				if (h.from == null) {
					var p = Bi(u, h.marker);
					p || (h.from = l, f && (u || (u = [])).push(h))
				} else h.from += l, f && (u || (u = [])).push(h)
			}
		u && (u = Ui(u)), a && a != u && (a = Ui(a));
		var d = [u];
		if (!f) {
			var v = t.text.length - 2,
				m;
			if (v > 0 && u)
				for (var c = 0; c < u.length; ++c) u[c].to == null && (m || (m = [])).push(new Hi(u[c].marker, null, null));
			for (var c = 0; c < v; ++c) d.push(m);
			d.push(a)
		}
		return d
	}

	function Ui(e) {
		for (var t = 0; t < e.length; ++t) {
			var n = e[t];
			n.from != null && n.from == n.to && n.marker.clearWhenEmpty !== !1 && e.splice(t--, 1)
		}
		return e.length ? e : null
	}

	function zi(e, t) {
		var n = io(e, t),
			r = Ri(e, t);
		if (!n) return r;
		if (!r) return n;
		for (var i = 0; i < n.length; ++i) {
			var s = n[i],
				o = r[i];
			if (s && o) e: for (var u = 0; u < o.length; ++u) {
				var a = o[u];
				for (var f = 0; f < s.length; ++f)
					if (s[f].marker == a.marker) continue e;
				s.push(a)
			} else o && (n[i] = o)
		}
		return n
	}

	function Wi(e, t, n) {
		var r = null;
		e.iter(t.line, n.line + 1, function (e) {
			if (e.markedSpans)
				for (var t = 0; t < e.markedSpans.length; ++t) {
					var n = e.markedSpans[t].marker;
					n.readOnly && (!r || Fo(r, n) == -1) && (r || (r = [])).push(n)
				}
		});
		if (!r) return null;
		var i = [{
			from: t,
			to: n
		}];
		for (var s = 0; s < r.length; ++s) {
			var o = r[s],
				u = o.find(0);
			for (var a = 0; a < i.length; ++a) {
				var f = i[a];
				if (dt(f.to, u.from) < 0 || dt(f.from, u.to) > 0) continue;
				var l = [a, 1],
					c = dt(f.from, u.from),
					h = dt(f.to, u.to);
				(c < 0 || !o.inclusiveLeft && !c) && l.push({
					from: f.from,
					to: u.from
				}), (h > 0 || !o.inclusiveRight && !h) && l.push({
					from: u.to,
					to: f.to
				}), i.splice.apply(i, l), a += l.length - 1
			}
		}
		return i
	}

	function Xi(e) {
		var t = e.markedSpans;
		if (!t) return;
		for (var n = 0; n < t.length; ++n) t[n].marker.detachLine(e);
		e.markedSpans = null
	}

	function Vi(e, t) {
		if (!t) return;
		for (var n = 0; n < t.length; ++n) t[n].marker.attachLine(e);
		e.markedSpans = t
	}

	function $i(e) {
		return e.inclusiveLeft ? -1 : 0
	}

	function Ji(e) {
		return e.inclusiveRight ? 1 : 0
	}

	function Ki(e, t) {
		var n = e.lines.length - t.lines.length;
		if (n != 0) return n;
		var r = e.find(),
			i = t.find(),
			s = dt(r.from, i.from) || $i(e) - $i(t);
		if (s) return -s;
		var o = dt(r.to, i.to) || Ji(e) - Ji(t);
		return o ? o : t.id - e.id
	}

	function Qi(e, t) {
		var n = E && e.markedSpans,
			r;
		if (n)
			for (var i, s = 0; s < n.length; ++s) i = n[s], i.marker.collapsed && (t ? i.from : i.to) == null && (!r || Ki(r, i.marker) < 0) && (r = i.marker);
		return r
	}

	function Gi(e) {
		return Qi(e, !0)
	}

	function Yi(e) {
		return Qi(e, !1)
	}

	function Zi(e, t, n, r, i) {
		var s = qs(e, t),
			o = E && s.markedSpans;
		if (o)
			for (var u = 0; u < o.length; ++u) {
				var a = o[u];
				if (!a.marker.collapsed) continue;
				var f = a.marker.find(0),
					l = dt(f.from, n) || $i(a.marker) - $i(i),
					c = dt(f.to, r) || Ji(a.marker) - Ji(i);
				if (l >= 0 && c <= 0 || l <= 0 && c >= 0) continue;
				if (l <= 0 && (dt(f.to, n) > 0 || a.marker.inclusiveRight && i.inclusiveLeft) || l >= 0 && (dt(f.from, r) < 0 || a.marker.inclusiveLeft && i.inclusiveRight)) return !0
			}
	}

	function es(e) {
		var t;
		while (t = Gi(e)) e = t.find(-1, !0).line;
		return e
	}

	function ts(e) {
		var t, n;
		while (t = Yi(e)) e = t.find(1, !0).line, (n || (n = [])).push(e);
		return n
	}

	function ns(e, t) {
		var n = qs(e, t),
			r = es(n);
		return n == r ? t : Ws(r)
	}

	function rs(e, t) {
		if (t > e.lastLine()) return t;
		var n = qs(e, t),
			r;
		if (!is(e, n)) return t;
		while (r = Yi(n)) n = r.find(1, !0).line;
		return Ws(n) + 1
	}

	function is(e, t) {
		var n = E && t.markedSpans;
		if (n)
			for (var r, i = 0; i < n.length; ++i) {
				r = n[i];
				if (!r.marker.collapsed) continue;
				if (r.from == null) return !0;
				if (r.marker.widgetNode) continue;
				if (r.from == 0 && r.marker.inclusiveLeft && ss(e, t, r)) return !0
			}
	}

	function ss(e, t, n) {
		if (n.to == null) {
			var r = n.marker.find(1, !0);
			return ss(e, r.line, Bi(r.line.markedSpans, n.marker))
		}
		if (n.marker.inclusiveRight && n.to == t.text.length) return !0;
		for (var i, s = 0; s < t.markedSpans.length; ++s) {
			i = t.markedSpans[s];
			if (i.marker.collapsed && !i.marker.widgetNode && i.from == n.to && (i.to == null || i.to != n.from) && (i.marker.inclusiveLeft || n.marker.inclusiveRight) && ss(e, t, i)) return !0
		}
	}

	function us(e, t, n) {
		Vs(t) < (e.curOp && e.curOp.scrollTop || e.doc.scrollTop) && ti(e, null, n)
	}

	function as(e) {
		if (e.height != null) return e.height;
		if (!Zo(document.body, e.node)) {
			var t = "position: relative;";
			e.coverGutter && (t += "margin-left: -" + e.cm.getGutterElement().offsetWidth + "px;"), Yo(e.cm.display.measure, Ko("div", [e.node], null, t))
		}
		return e.height = e.node.offsetHeight
	}

	function fs(e, t, n, r) {
		var i = new os(e, n, r);
		return i.noHScroll && (e.display.alignWidgets = !0), si(e.doc, t, "widget", function (t) {
			var n = t.widgets || (t.widgets = []);
			i.insertAt == null ? n.push(i) : n.splice(Math.min(n.length - 1, Math.max(0, i.insertAt)), 0, i), i.line = t;
			if (!is(e.doc, t)) {
				var r = Vs(t) < e.doc.scrollTop;
				zs(t, t.height + as(i)), r && ti(e, null, i.height), e.curOp.forceUpdate = !0
			}
			return !0
		}), i
	}

	function cs(e, t, n, r) {
		e.text = t, e.stateAfter && (e.stateAfter = null), e.styles && (e.styles = null), e.order != null && (e.order = null), Xi(e), Vi(e, n);
		var i = r ? r(e) : 1;
		i != e.height && zs(e, i)
	}

	function hs(e) {
		e.parent = null, Xi(e)
	}

	function ps(e, t) {
		if (e)
			for (;;) {
				var n = e.match(/(?:^|\s+)line-(background-)?(\S+)/);
				if (!n) break;
				e = e.slice(0, n.index) + e.slice(n.index + n[0].length);
				var r = n[1] ? "bgClass" : "textClass";
				t[r] == null ? t[r] = n[2] : (new RegExp("(?:^|s)" + n[2] + "(?:$|s)")).test(t[r]) || (t[r] += " " + n[2])
			}
		return e
	}

	function ds(e, t) {
		if (e.blankLine) return e.blankLine(t);
		if (!e.innerMode) return;
		var n = S.innerMode(e, t);
		if (n.mode.blankLine) return n.mode.blankLine(n.state)
	}

	function vs(e, t, n) {
		for (var r = 0; r < 10; r++) {
			var i = e.token(t, n);
			if (t.pos > t.start) return i
		}
		throw new Error("Mode " + e.name + " failed to advance stream.")
	}

	function ms(e, t, n, r, i, s, o) {
		var u = n.flattenSpans;
		u == null && (u = e.options.flattenSpans);
		var a = 0,
			f = null,
			l = new Ci(t, e.options.tabSize),
			c;
		t == "" && ps(ds(n, r), s);
		while (!l.eol()) {
			l.pos > e.options.maxHighlightLength ? (u = !1, o && bs(e, t, r, l.pos), l.pos = t.length, c = null) : c = ps(vs(n, l, r), s);
			if (e.options.addModeClass) {
				var h = S.innerMode(n, r).mode.name;
				h && (c = "m-" + (c ? h + " " + c : h))
			}
			if (!u || f != c) a < l.start && i(l.start, f), a = l.start, f = c;
			l.start = l.pos
		}
		while (a < l.pos) {
			var p = Math.min(l.pos, a + 5e4);
			i(p, f), a = p
		}
	}

	function gs(e, t, n, r) {
		var i = [e.state.modeGen],
			s = {};
		ms(e, t.text, e.doc.mode, n, function (e, t) {
			i.push(e, t)
		}, s, r);
		for (var o = 0; o < e.state.overlays.length; ++o) {
			var u = e.state.overlays[o],
				a = 1,
				f = 0;
			ms(e, t.text, u.mode, !0, function (e, t) {
				var n = a;
				while (f < e) {
					var r = i[a];
					r > e && i.splice(a, 1, e, i[a + 1], r), a += 2, f = Math.min(e, r)
				}
				if (!t) return;
				if (u.opaque) i.splice(n, a - n, e, "cm-overlay " + t), a = n + 2;
				else
					for (; n < a; n += 2) {
						var s = i[n + 1];
						i[n + 1] = (s ? s + " " : "") + "cm-overlay " + t
					}
			}, s)
		}
		return {
			styles: i,
			classes: s.bgClass || s.textClass ? s : null
		}
	}

	function ys(e, t) {
		if (!t.styles || t.styles[0] != e.state.modeGen) {
			var n = gs(e, t, t.stateAfter = Kt(e, Ws(t)));
			t.styles = n.styles, n.classes ? t.styleClasses = n.classes : t.styleClasses && (t.styleClasses = null)
		}
		return t.styles
	}

	function bs(e, t, n, r) {
		var i = e.doc.mode,
			s = new Ci(t, e.options.tabSize);
		s.start = s.pos = r || 0, t == "" && ds(i, n);
		while (!s.eol() && s.pos <= e.options.maxHighlightLength) vs(i, s, n), s.start = s.pos
	}

	function Ss(e, t) {
		if (!e || /^\s*$/.test(e)) return null;
		var n = t.addModeClass ? Es : ws;
		return n[e] || (n[e] = e.replace(/\S+/g, "cm-$&"))
	}

	function xs(e, t) {
		var n = Ko("span", null, null, s ? "padding-right: .1px" : null),
			i = {
				pre: Ko("pre", [n]),
				content: n,
				col: 0,
				pos: 0,
				cm: e
			};
		t.measure = {};
		for (var o = 0; o <= (t.rest ? t.rest.length : 0); o++) {
			var u = o ? t.rest[o - 1] : t.line,
				a;
			i.pos = 0, i.addToken = Ns, (r || s) && e.getOption("lineWrapping") && (i.addToken = Cs(i.addToken)), vu(e.display.measure) && (a = $s(u)) && (i.addToken = ks(i.addToken, a)), i.map = [], As(u, i, ys(e, u)), u.styleClasses && (u.styleClasses.bgClass && (i.bgClass = iu(u.styleClasses.bgClass, i.bgClass || "")), u.styleClasses.textClass && (i.textClass = iu(u.styleClasses.textClass, i.textClass || ""))), i.map.length == 0 && i.map.push(0, 0, i.content.appendChild(pu(e.display.measure))), o == 0 ? (t.measure.map = i.map, t.measure.cache = {}) : ((t.measure.maps || (t.measure.maps = [])).push(i.map), (t.measure.caches || (t.measure.caches = [])).push({}))
		}
		return yo(e, "renderLine", e, t.line, i.pre), i.pre.className && (i.textClass = iu(i.pre.className, i.textClass || "")), i
	}

	function Ts(e) {
		var t = Ko("span", "•", "cm-invalidchar");
		return t.title = "\\u" + e.charCodeAt(0).toString(16), t
	}

	function Ns(e, t, n, s, o, u) {
		if (!t) return;
		var a = e.cm.options.specialChars,
			f = !1;
		if (!a.test(t)) {
			e.col += t.length;
			var l = document.createTextNode(t);
			e.map.push(e.pos, e.pos + t.length, l), r && i < 9 && (f = !0), e.pos += t.length
		} else {
			var l = document.createDocumentFragment(),
				c = 0;
			for (;;) {
				a.lastIndex = c;
				var h = a.exec(t),
					p = h ? h.index - c : t.length - c;
				if (p) {
					var d = document.createTextNode(t.slice(c, c + p));
					r && i < 9 ? l.appendChild(Ko("span", [d])) : l.appendChild(d), e.map.push(e.pos, e.pos + p, d), e.col += p, e.pos += p
				}
				if (!h) break;
				c += p + 1;
				if (h[0] == "	") {
					var v = e.cm.options.tabSize,
						m = v - e.col % v,
						d = l.appendChild(Ko("span", Ho(m), "cm-tab"));
					e.col += m
				} else {
					var d = e.cm.options.specialCharPlaceholder(h[0]);
					r && i < 9 ? l.appendChild(Ko("span", [d])) : l.appendChild(d), e.col += 1
				}
				e.map.push(e.pos, e.pos + 1, d), e.pos++
			}
		}
		if (n || s || o || f) {
			var g = n || "";
			s && (g += s), o && (g += o);
			var y = Ko("span", [l], g);
			return u && (y.title = u), e.content.appendChild(y)
		}
		e.content.appendChild(l)
	}

	function Cs(e) {
		function t(e) {
			var t = " ";
			for (var n = 0; n < e.length - 2; ++n) t += n % 2 ? " " : " ";
			return t += " ", t
		}
		return function (n, r, i, s, o, u) {
			e(n, r.replace(/ {3,}/g, t), i, s, o, u)
		}
	}

	function ks(e, t) {
		return function (n, r, i, s, o, u) {
			i = i ? i + " cm-force-border" : "cm-force-border";
			var a = n.pos,
				f = a + r.length;
			for (;;) {
				for (var l = 0; l < t.length; l++) {
					var c = t[l];
					if (c.to > a && c.from <= a) break
				}
				if (c.to >= f) return e(n, r, i, s, o, u);
				e(n, r.slice(0, c.to - a), i, s, null, u), s = null, r = r.slice(c.to - a), a = c.to
			}
		}
	}

	function Ls(e, t, n, r) {
		var i = !r && n.widgetNode;
		i && (e.map.push(e.pos, e.pos + t, i), e.content.appendChild(i)), e.pos += t
	}

	function As(e, t, n) {
		var r = e.markedSpans,
			i = e.text,
			s = 0;
		if (!r) {
			for (var o = 1; o < n.length; o += 2) t.addToken(t, i.slice(s, s = n[o]), Ss(n[o + 1], t.cm.options));
			return
		}
		var u = i.length,
			a = 0,
			o = 1,
			f = "",
			l, c = 0,
			h, p, d, v, m;
		for (;;) {
			if (c == a) {
				h = p = d = v = "", m = null, c = Infinity;
				var g = [];
				for (var y = 0; y < r.length; ++y) {
					var b = r[y],
						w = b.marker;
					b.from <= a && (b.to == null || b.to > a) ? (b.to != null && c > b.to && (c = b.to, p = ""), w.className && (h += " " + w.className), w.startStyle && b.from == a && (d += " " + w.startStyle), w.endStyle && b.to == c && (p += " " + w.endStyle), w.title && !v && (v = w.title), w.collapsed && (!m || Ki(m.marker, w) < 0) && (m = b)) : b.from > a && c > b.from && (c = b.from), w.type == "bookmark" && b.from == a && w.widgetNode && g.push(w)
				}
				if (m && (m.from || 0) == a) {
					Ls(t, (m.to == null ? u + 1 : m.to) - a, m.marker, m.from == null);
					if (m.to == null) return
				}
				if (!m && g.length)
					for (var y = 0; y < g.length; ++y) Ls(t, 0, g[y])
			}
			if (a >= u) break;
			var E = Math.min(u, c);
			for (;;) {
				if (f) {
					var S = a + f.length;
					if (!m) {
						var x = S > E ? f.slice(0, E - a) : f;
						t.addToken(t, x, l ? l + h : h, d, a + x.length == c ? p : "", v)
					}
					if (S >= E) {
						f = f.slice(E - a), a = E;
						break
					}
					a = S, d = ""
				}
				f = i.slice(s, s = n[o++]), l = Ss(n[o++], t.cm.options)
			}
		}
	}

	function Os(e, t) {
		return t.from.ch == 0 && t.to.ch == 0 && Bo(t.text) == "" && (!e.cm || e.cm.options.wholeLineUpdateBefore)
	}

	function Ms(e, t, n, r) {
		function i(e) {
			return n ? n[e] : null
		}

		function s(e, n, i) {
			cs(e, n, i, r), wo(e, "change", e, t)
		}
		var o = t.from,
			u = t.to,
			a = t.text,
			f = qs(e, o.line),
			l = qs(e, u.line),
			c = Bo(a),
			h = i(a.length - 1),
			p = u.line - o.line;
		if (Os(e, t)) {
			for (var d = 0, v = []; d < a.length - 1; ++d) v.push(new ls(a[d], i(d), r));
			s(l, l.text, h), p && e.remove(o.line, p), v.length && e.insert(o.line, v)
		} else if (f == l)
			if (a.length == 1) s(f, f.text.slice(0, o.ch) + c + f.text.slice(u.ch), h);
			else {
				for (var v = [], d = 1; d < a.length - 1; ++d) v.push(new ls(a[d], i(d), r));
				v.push(new ls(c + f.text.slice(u.ch), h, r)), s(f, f.text.slice(0, o.ch) + a[0], i(0)), e.insert(o.line + 1, v)
			} else if (a.length == 1) s(f, f.text.slice(0, o.ch) + a[0] + l.text.slice(u.ch), i(0)), e.remove(o.line + 1, p);
		else {
			s(f, f.text.slice(0, o.ch) + a[0], i(0)), s(l, c + l.text.slice(u.ch), h);
			for (var d = 1, v = []; d < a.length - 1; ++d) v.push(new ls(a[d], i(d), r));
			p > 1 && e.remove(o.line + 1, p - 1), e.insert(o.line + 1, v)
		}
		wo(e, "change", e, t)
	}

	function _s(e) {
		this.lines = e, this.parent = null;
		for (var t = 0, n = 0; t < e.length; ++t) e[t].parent = this, n += e[t].height;
		this.height = n
	}

	function Ds(e) {
		this.children = e;
		var t = 0,
			n = 0;
		for (var r = 0; r < e.length; ++r) {
			var i = e[r];
			t += i.chunkSize(), n += i.height, i.parent = this
		}
		this.size = t, this.height = n, this.parent = null
	}

	function Fs(e, t, n) {
		function r(e, i, s) {
			if (e.linked)
				for (var o = 0; o < e.linked.length; ++o) {
					var u = e.linked[o];
					if (u.doc == i) continue;
					var a = s && u.sharedHist;
					if (n && !a) continue;
					t(u.doc, a), r(u.doc, e, a)
				}
		}
		r(e, null, !0)
	}

	function Is(e, t) {
		if (t.cm) throw new Error("This document is already in use.");
		e.doc = t, t.cm = e, L(e), T(e), e.options.lineWrapping || H(e), e.options.mode = t.modeOption, zn(e)
	}

	function qs(e, t) {
		t -= e.first;
		if (t < 0 || t >= e.size) throw new Error("There is no line " + (t + e.first) + " in the document.");
		for (var n = e; !n.lines;)
			for (var r = 0;; ++r) {
				var i = n.children[r],
					s = i.chunkSize();
				if (t < s) {
					n = i;
					break
				}
				t -= s
			}
		return n.lines[t]
	}

	function Rs(e, t, n) {
		var r = [],
			i = t.line;
		return e.iter(t.line, n.line + 1, function (e) {
			var s = e.text;
			i == n.line && (s = s.slice(0, n.ch)), i == t.line && (s = s.slice(t.ch)), r.push(s), ++i
		}), r
	}

	function Us(e, t, n) {
		var r = [];
		return e.iter(t, n, function (e) {
			r.push(e.text)
		}), r
	}

	function zs(e, t) {
		var n = t - e.height;
		if (n)
			for (var r = e; r; r = r.parent) r.height += n
	}

	function Ws(e) {
		if (e.parent == null) return null;
		var t = e.parent,
			n = Fo(t.lines, e);
		for (var r = t.parent; r; t = r, r = r.parent)
			for (var i = 0;; ++i) {
				if (r.children[i] == t) break;
				n += r.children[i].chunkSize()
			}
		return n + t.first
	}

	function Xs(e, t) {
		var n = e.first;
		e: do {
			for (var r = 0; r < e.children.length; ++r) {
				var i = e.children[r],
					s = i.height;
				if (t < s) {
					e = i;
					continue e
				}
				t -= s, n += i.chunkSize()
			}
			return n
		} while (!e.lines);
		for (var r = 0; r < e.lines.length; ++r) {
			var o = e.lines[r],
				u = o.height;
			if (t < u) break;
			t -= u
		}
		return n + r
	}

	function Vs(e) {
		e = es(e);
		var t = 0,
			n = e.parent;
		for (var r = 0; r < n.lines.length; ++r) {
			var i = n.lines[r];
			if (i == e) break;
			t += i.height
		}
		for (var s = n.parent; s; n = s, s = n.parent)
			for (var r = 0; r < s.children.length; ++r) {
				var o = s.children[r];
				if (o == n) break;
				t += o.height
			}
		return t
	}

	function $s(e) {
		var t = e.order;
		return t == null && (t = e.order = Bu(e.text)), t
	}

	function Js(e) {
		this.done = [], this.undone = [], this.undoDepth = Infinity, this.lastModTime = this.lastSelTime = 0, this.lastOp = this.lastSelOp = null, this.lastOrigin = this.lastSelOrigin = null, this.generation = this.maxGeneration = e || 1
	}

	function Ks(e, t) {
		var n = {
			from: vt(t.from),
			to: Fr(t),
			text: Rs(e, t.from, t.to)
		};
		return no(e, n, t.from.line, t.to.line + 1), Fs(e, function (e) {
			no(e, n, t.from.line, t.to.line + 1)
		}, !0), n
	}

	function Qs(e) {
		while (e.length) {
			var t = Bo(e);
			if (!t.ranges) break;
			e.pop()
		}
	}

	function Gs(e, t) {
		if (t) return Qs(e.done), Bo(e.done);
		if (e.done.length && !Bo(e.done).ranges) return Bo(e.done);
		if (e.done.length > 1 && !e.done[e.done.length - 2].ranges) return e.done.pop(), Bo(e.done)
	}

	function Ys(e, t, n, r) {
		var i = e.history;
		i.undone.length = 0;
		var s = +(new Date),
			o;
		if ((i.lastOp == r || i.lastOrigin == t.origin && t.origin && (t.origin.charAt(0) == "+" && e.cm && i.lastModTime > s - e.cm.options.historyEventDelay || t.origin.charAt(0) == "*")) && (o = Gs(i, i.lastOp == r))) {
			var u = Bo(o.changes);
			dt(t.from, t.to) == 0 && dt(t.from, u.to) == 0 ? u.to = Fr(t) : o.changes.push(Ks(e, t))
		} else {
			var a = Bo(i.done);
			(!a || !a.ranges) && to(e.sel, i.done), o = {
				changes: [Ks(e, t)],
				generation: i.generation
			}, i.done.push(o);
			while (i.done.length > i.undoDepth) i.done.shift(), i.done[0].ranges || i.done.shift()
		}
		i.done.push(n), i.generation = ++i.maxGeneration, i.lastModTime = i.lastSelTime = s, i.lastOp = i.lastSelOp = r, i.lastOrigin = i.lastSelOrigin = t.origin, u || yo(e, "historyAdded")
	}

	function Zs(e, t, n, r) {
		var i = t.charAt(0);
		return i == "*" || i == "+" && n.ranges.length == r.ranges.length && n.somethingSelected() == r.somethingSelected() && new Date - e.history.lastSelTime <= (e.cm ? e.cm.options.historyEventDelay : 500)
	}

	function eo(e, t, n, r) {
		var i = e.history,
			s = r && r.origin;
		n == i.lastSelOp || s && i.lastSelOrigin == s && (i.lastModTime == i.lastSelTime && i.lastOrigin == s || Zs(e, s, Bo(i.done), t)) ? i.done[i.done.length - 1] = t : to(t, i.done), i.lastSelTime = +(new Date), i.lastSelOrigin = s, i.lastSelOp = n, r && r.clearRedo !== !1 && Qs(i.undone)
	}

	function to(e, t) {
		var n = Bo(t);
		n && n.ranges && n.equals(e) || t.push(e)
	}

	function no(e, t, n, r) {
		var i = t["spans_" + e.id],
			s = 0;
		e.iter(Math.max(e.first, n), Math.min(e.first + e.size, r), function (n) {
			n.markedSpans && ((i || (i = t["spans_" + e.id] = {}))[s] = n.markedSpans), ++s
		})
	}

	function ro(e) {
		if (!e) return null;
		for (var t = 0, n; t < e.length; ++t) e[t].marker.explicitlyCleared ? n || (n = e.slice(0, t)) : n && n.push(e[t]);
		return n ? n.length ? n : null : e
	}

	function io(e, t) {
		var n = t["spans_" + e.id];
		if (!n) return null;
		for (var r = 0, i = []; r < t.text.length; ++r) i.push(ro(n[r]));
		return i
	}

	function so(e, t, n) {
		for (var r = 0, i = []; r < e.length; ++r) {
			var s = e[r];
			if (s.ranges) {
				i.push(n ? yt.prototype.deepCopy.call(s) : s);
				continue
			}
			var o = s.changes,
				u = [];
			i.push({
				changes: u
			});
			for (var a = 0; a < o.length; ++a) {
				var f = o[a],
					l;
				u.push({
					from: f.from,
					to: f.to,
					text: f.text
				});
				if (t)
					for (var c in f)(l = c.match(/^spans_(\d+)$/)) && Fo(t, Number(l[1])) > -1 && (Bo(u)[c] = f[c], delete f[c])
			}
		}
		return i
	}

	function oo(e, t, n, r) {
		n < e.line ? e.line += r : t < e.line && (e.line = t, e.ch = 0)
	}

	function uo(e, t, n, r) {
		for (var i = 0; i < e.length; ++i) {
			var s = e[i],
				o = !0;
			if (s.ranges) {
				s.copied || (s = e[i] = s.deepCopy(), s.copied = !0);
				for (var u = 0; u < s.ranges.length; u++) oo(s.ranges[u].anchor, t, n, r), oo(s.ranges[u].head, t, n, r);
				continue
			}
			for (var u = 0; u < s.changes.length; ++u) {
				var a = s.changes[u];
				if (n < a.from.line) a.from = pt(a.from.line + r, a.from.ch), a.to = pt(a.to.line + r, a.to.ch);
				else if (t <= a.to.line) {
					o = !1;
					break
				}
			}
			o || (e.splice(0, i + 1), i = 0)
		}
	}

	function ao(e, t) {
		var n = t.from.line,
			r = t.to.line,
			i = t.text.length - (r - n) - 1;
		uo(e.done, n, r, i), uo(e.undone, n, r, i)
	}

	function co(e) {
		return e.defaultPrevented != null ? e.defaultPrevented : e.returnValue == 0
	}

	function po(e) {
		return e.target || e.srcElement
	}

	function vo(e) {
		var t = e.which;
		return t == null && (e.button & 1 ? t = 1 : e.button & 2 ? t = 3 : e.button & 4 && (t = 2)), v && e.ctrlKey && t == 1 && (t = 3), t
	}

	function wo(e, t) {
		function s(e) {
			return function () {
				e.apply(null, r)
			}
		}
		var n = e._handlers && e._handlers[t];
		if (!n) return;
		var r = Array.prototype.slice.call(arguments, 2),
			i;
		Cn ? i = Cn.delayedCallbacks : bo ? i = bo : (i = bo = [], setTimeout(Eo, 0));
		for (var o = 0; o < n.length; ++o) i.push(s(n[o]))
	}

	function Eo() {
		var e = bo;
		bo = null;
		for (var t = 0; t < e.length; ++t) e[t]()
	}

	function So(e, t, n) {
		return yo(e, n || t.type, e, t), co(t) || t.codemirrorIgnore
	}

	function xo(e) {
		var t = e._handlers && e._handlers.cursorActivity;
		if (!t) return;
		var n = e.curOp.cursorActivityHandlers || (e.curOp.cursorActivityHandlers = []);
		for (var r = 0; r < t.length; ++r) Fo(n, t[r]) == -1 && n.push(t[r])
	}

	function To(e, t) {
		var n = e._handlers && e._handlers[t];
		return n && n.length > 0
	}

	function No(e) {
		e.prototype.on = function (e, t) {
			mo(this, e, t)
		}, e.prototype.off = function (e, t) {
			go(this, e, t)
		}
	}

	function Mo() {
		this.id = null
	}

	function Do(e, t, n) {
		for (var r = 0, i = 0;;) {
			var s = e.indexOf("	", r);
			s == -1 && (s = e.length);
			var o = s - r;
			if (s == e.length || i + o >= t) return r + Math.min(o, t - i);
			i += s - r, i += n - i % n, r = s + 1;
			if (i >= t) return r
		}
	}

	function Ho(e) {
		while (Po.length <= e) Po.push(Bo(Po) + " ");
		return Po[e]
	}

	function Bo(e) {
		return e[e.length - 1]
	}

	function Fo(e, t) {
		for (var n = 0; n < e.length; ++n)
			if (e[n] == t) return n;
		return -1
	}

	function Io(e, t) {
		var n = [];
		for (var r = 0; r < e.length; r++) n[r] = t(e[r], r);
		return n
	}

	function qo(e, t) {
		var n;
		if (Object.create) n = Object.create(e);
		else {
			var r = function () {};
			r.prototype = e, n = new r
		}
		return t && Ro(t, n), n
	}

	function Ro(e, t, n) {
		t || (t = {});
		for (var r in e) e.hasOwnProperty(r) && (n !== !1 || !t.hasOwnProperty(r)) && (t[r] = e[r]);
		return t
	}

	function Uo(e) {
		var t = Array.prototype.slice.call(arguments, 1);
		return function () {
			return e.apply(null, t)
		}
	}

	function Xo(e, t) {
		return t ? t.source.indexOf("\\w") > -1 && Wo(e) ? !0 : t.test(e) : Wo(e)
	}

	function Vo(e) {
		for (var t in e)
			if (e.hasOwnProperty(t) && e[t]) return !1;
		return !0
	}

	function Jo(e) {
		return e.charCodeAt(0) >= 768 && $o.test(e)
	}

	function Ko(e, t, n, r) {
		var i = document.createElement(e);
		n && (i.className = n), r && (i.style.cssText = r);
		if (typeof t == "string") i.appendChild(document.createTextNode(t));
		else if (t)
			for (var s = 0; s < t.length; ++s) i.appendChild(t[s]);
		return i
	}

	function Go(e) {
		for (var t = e.childNodes.length; t > 0; --t) e.removeChild(e.firstChild);
		return e
	}

	function Yo(e, t) {
		return Go(e).appendChild(t)
	}

	function Zo(e, t) {
		if (e.contains) return e.contains(t);
		while (t = t.parentNode)
			if (t == e) return !0
	}

	function eu() {
		return document.activeElement
	}

	function tu(e) {
		return new RegExp("\\b" + e + "\\b\\s*")
	}

	function nu(e, t) {
		var n = tu(t);
		n.test(e.className) && (e.className = e.className.replace(n, ""))
	}

	function ru(e, t) {
		tu(t).test(e.className) || (e.className += " " + t)
	}

	function iu(e, t) {
		var n = e.split(" ");
		for (var r = 0; r < n.length; r++) n[r] && !tu(n[r]).test(t) && (t += " " + n[r]);
		return t
	}

	function su(e) {
		if (!document.body.getElementsByClassName) return;
		var t = document.body.getElementsByClassName("CodeMirror");
		for (var n = 0; n < t.length; n++) {
			var r = t[n].CodeMirror;
			r && e(r)
		}
	}

	function uu() {
		if (ou) return;
		au(), ou = !0
	}

	function au() {
		var e;
		mo(window, "resize", function () {
			e == null && (e = setTimeout(function () {
				e = null, lu = null, su(sr)
			}, 100))
		}), mo(window, "blur", function () {
			su(Hr)
		})
	}

	function cu(e) {
		if (lu != null) return lu;
		var t = Ko("div", null, null, "width: 50px; height: 50px; overflow-x: scroll");
		return Yo(e, t), t.offsetWidth && (lu = t.offsetHeight - t.clientHeight), lu || 0
	}

	function pu(e) {
		if (hu == null) {
			var t = Ko("span", "​");
			Yo(e, Ko("span", [t, document.createTextNode("x")])), e.firstChild.offsetHeight != 0 && (hu = t.offsetWidth <= 1 && t.offsetHeight > 2 && !(r && i < 8))
		}
		return hu ? Ko("span", "​") : Ko("span", " ", null, "display: inline-block; width: 1px; margin-right: -1px")
	}

	function vu(e) {
		if (du != null) return du;
		var t = Yo(e, document.createTextNode("AخA")),
			n = Qo(t, 0, 1).getBoundingClientRect();
		if (!n || n.left == n.right) return !1;
		var r = Qo(t, 1, 2).getBoundingClientRect();
		return du = r.right - n.right < 3
	}

	function wu(e) {
		if (bu != null) return bu;
		var t = Yo(e, Ko("span", "x")),
			n = t.getBoundingClientRect(),
			r = Qo(t, 0, 1).getBoundingClientRect();
		return bu = Math.abs(n.left - r.left) > 1
	}

	function Su(e, t, n, r) {
		if (!e) return r(t, n, "ltr");
		var i = !1;
		for (var s = 0; s < e.length; ++s) {
			var o = e[s];
			if (o.from < n && o.to > t || t == n && o.to == t) r(Math.max(o.from, t), Math.min(o.to, n), o.level == 1 ? "rtl" : "ltr"), i = !0
		}
		i || r(t, n, "ltr")
	}

	function xu(e) {
		return e.level % 2 ? e.to : e.from
	}

	function Tu(e) {
		return e.level % 2 ? e.from : e.to
	}

	function Nu(e) {
		var t = $s(e);
		return t ? xu(t[0]) : 0
	}

	function Cu(e) {
		var t = $s(e);
		return t ? Tu(Bo(t)) : e.text.length
	}

	function ku(e, t) {
		var n = qs(e.doc, t),
			r = es(n);
		r != n && (t = Ws(r));
		var i = $s(r),
			s = i ? i[0].level % 2 ? Cu(r) : Nu(r) : 0;
		return pt(t, s)
	}

	function Lu(e, t) {
		var n, r = qs(e.doc, t);
		while (n = Yi(r)) r = n.find(1, !0).line, t = null;
		var i = $s(r),
			s = i ? i[0].level % 2 ? Nu(r) : Cu(r) : r.text.length;
		return pt(t == null ? Ws(r) : t, s)
	}

	function Au(e, t) {
		var n = ku(e, t.line),
			r = qs(e.doc, n.line),
			i = $s(r);
		if (!i || i[0].level == 0) {
			var s = Math.max(0, r.text.search(/\S/)),
				o = t.line == n.line && t.ch <= s && t.ch;
			return pt(n.line, o ? 0 : s)
		}
		return n
	}

	function Ou(e, t, n) {
		var r = e[0].level;
		return t == r ? !0 : n == r ? !1 : t < n
	}

	function _u(e, t) {
		Mu = null;
		for (var n = 0, r; n < e.length; ++n) {
			var i = e[n];
			if (i.from < t && i.to > t) return n;
			if (i.from == t || i.to == t) {
				if (r != null) return Ou(e, i.level, e[r].level) ? (i.from != i.to && (Mu = r), n) : (i.from != i.to && (Mu = n), r);
				r = n
			}
		}
		return r
	}

	function Du(e, t, n, r) {
		if (!r) return t + n;
		do t += n; while (t > 0 && Jo(e.text.charAt(t)));
		return t
	}

	function Pu(e, t, n, r) {
		var i = $s(e);
		if (!i) return Hu(e, t, n, r);
		var s = _u(i, t),
			o = i[s],
			u = Du(e, t, o.level % 2 ? -n : n, r);
		for (;;) {
			if (u > o.from && u < o.to) return u;
			if (u == o.from || u == o.to) return _u(i, u) == s ? u : (o = i[s += n], n > 0 == o.level % 2 ? o.to : o.from);
			o = i[s += n];
			if (!o) return null;
			n > 0 == o.level % 2 ? u = Du(e, o.to, -1, r) : u = Du(e, o.from, 1, r)
		}
	}

	function Hu(e, t, n, r) {
		var i = t + n;
		if (r)
			while (i > 0 && Jo(e.text.charAt(i))) i += n;
		return i < 0 || i > e.text.length ? null : i
	}
	var e = /gecko\/\d/i.test(navigator.userAgent),
		t = /MSIE \d/.test(navigator.userAgent),
		n = /Trident\/(?:[7-9]|\d{2,})\..*rv:(\d+)/.exec(navigator.userAgent),
		r = t || n,
		i = r && (t ? document.documentMode || 6 : n[1]),
		s = /WebKit\//.test(navigator.userAgent),
		o = s && /Qt\/\d+\.\d+/.test(navigator.userAgent),
		u = /Chrome\//.test(navigator.userAgent),
		a = /Opera\//.test(navigator.userAgent),
		f = /Apple Computer/.test(navigator.vendor),
		l = /KHTML\//.test(navigator.userAgent),
		c = /Mac OS X 1\d\D([8-9]|\d\d)\D/.test(navigator.userAgent),
		h = /PhantomJS/.test(navigator.userAgent),
		p = /AppleWebKit/.test(navigator.userAgent) && /Mobile\/\w+/.test(navigator.userAgent),
		d = p || /Android|webOS|BlackBerry|Opera Mini|Opera Mobi|IEMobile/i.test(navigator.userAgent),
		v = p || /Mac/.test(navigator.platform),
		m = /win/i.test(navigator.platform),
		g = a && navigator.userAgent.match(/Version\/(\d*\.\d*)/);
	g && (g = Number(g[1])), g && g >= 15 && (a = !1, s = !0);
	var y = v && (o || a && (g == null || g < 12.11)),
		b = e || r && i >= 9,
		w = !1,
		E = !1,
		pt = S.Pos = function (e, t) {
			if (!(this instanceof pt)) return new pt(e, t);
			this.line = e, this.ch = t
		},
		dt = S.cmpPos = function (e, t) {
			return e.line - t.line || e.ch - t.ch
		};
	yt.prototype = {
		primary: function () {
			return this.ranges[this.primIndex]
		},
		equals: function (e) {
			if (e == this) return !0;
			if (e.primIndex != this.primIndex || e.ranges.length != this.ranges.length) return !1;
			for (var t = 0; t < this.ranges.length; t++) {
				var n = this.ranges[t],
					r = e.ranges[t];
				if (dt(n.anchor, r.anchor) != 0 || dt(n.head, r.head) != 0) return !1
			}
			return !0
		},
		deepCopy: function () {
			for (var e = [], t = 0; t < this.ranges.length; t++) e[t] = new bt(vt(this.ranges[t].anchor), vt(this.ranges[t].head));
			return new yt(e, this.primIndex)
		},
		somethingSelected: function () {
			for (var e = 0; e < this.ranges.length; e++)
				if (!this.ranges[e].empty()) return !0;
			return !1
		},
		contains: function (e, t) {
			t || (t = e);
			for (var n = 0; n < this.ranges.length; n++) {
				var r = this.ranges[n];
				if (dt(t, r.from()) >= 0 && dt(e, r.to()) <= 0) return n
			}
			return -1
		}
	}, bt.prototype = {
		from: function () {
			return gt(this.anchor, this.head)
		},
		to: function () {
			return mt(this.anchor, this.head)
		},
		empty: function () {
			return this.head.line == this.anchor.line && this.head.ch == this.anchor.ch
		}
	};
	var un = {
			left: 0,
			right: 0,
			top: 0,
			bottom: 0
		},
		xn, Cn = null,
		kn = 0,
		Yn = null,
		fr, lr, mr = 0,
		Er = 0,
		Sr = null;
	r ? Sr = -0.53 : e ? Sr = 15 : u ? Sr = -0.7 : f && (Sr = -1 / 3);
	var Cr, Ar = null,
		Fr = S.changeEnd = function (e) {
			return e.text ? pt(e.from.line + e.text.length - 1, Bo(e.text).length + (e.text.length == 1 ? e.from.ch : 0)) : e.to
		};
	S.prototype = {
		constructor: S,
		focus: function () {
			window.focus(), tr(this), Gn(this)
		},
		setOption: function (e, t) {
			var n = this.options,
				r = n[e];
			if (n[e] == t && e != "mode") return;
			n[e] = t, li.hasOwnProperty(e) && Fn(this, li[e])(this, t, r)
		},
		getOption: function (e) {
			return this.options[e]
		},
		getDoc: function () {
			return this.doc
		},
		addKeyMap: function (e, t) {
			this.state.keyMaps[t ? "push" : "unshift"](e)
		},
		removeKeyMap: function (e) {
			var t = this.state.keyMaps;
			for (var n = 0; n < t.length; ++n)
				if (t[n] == e || typeof t[n] != "string" && t[n].name == e) return t.splice(n, 1), !0
		},
		addOverlay: In(function (e, t) {
			var n = e.token ? e : S.getMode(this.options, e);
			if (n.startState) throw new Error("Overlays may not be stateful.");
			this.state.overlays.push({
				mode: n,
				modeSpec: e,
				opaque: t && t.opaque
			}), this.state.modeGen++, zn(this)
		}),
		removeOverlay: In(function (e) {
			var t = this.state.overlays;
			for (var n = 0; n < t.length; ++n) {
				var r = t[n].modeSpec;
				if (r == e || typeof e == "string" && r.name == e) {
					t.splice(n, 1), this.state.modeGen++, zn(this);
					return
				}
			}
		}),
		indentLine: In(function (e, t, n) {
			typeof t != "string" && typeof t != "number" && (t == null ? t = this.options.smartIndent ? "smart" : "prev" : t = t ? "add" : "subtract"), Nt(this.doc, e) && ii(this, e, t, n)
		}),
		indentSelection: In(function (e) {
			var t = this.doc.sel.ranges,
				n = -1;
			for (var r = 0; r < t.length; r++) {
				var i = t[r];
				if (!i.empty()) {
					var s = i.from(),
						o = i.to(),
						u = Math.max(n, s.line);
					n = Math.min(this.lastLine(), o.line - (o.ch ? 0 : 1)) + 1;
					for (var a = u; a < n; ++a) ii(this, a, e);
					var f = this.doc.sel.ranges;
					s.ch == 0 && t.length == f.length && f[r].from().ch > 0 && Ot(this.doc, r, new bt(s, f[r].to()), Lo)
				} else i.head.line > n && (ii(this, i.head.line, e, !0), n = i.head.line, r == this.doc.sel.primIndex && ni(this))
			}
		}),
		getTokenAt: function (e, t) {
			var n = this.doc;
			e = xt(n, e);
			var r = Kt(this, e.line, t),
				i = this.doc.mode,
				s = qs(n, e.line),
				o = new Ci(s.text, this.options.tabSize);
			while (o.pos < e.ch && !o.eol()) {
				o.start = o.pos;
				var u = vs(i, o, r)
			}
			return {
				start: o.start,
				end: o.pos,
				string: o.current(),
				type: u || null,
				state: r
			}
		},
		getTokenTypeAt: function (e) {
			e = xt(this.doc, e);
			var t = ys(this, qs(this.doc, e.line)),
				n = 0,
				r = (t.length - 1) / 2,
				i = e.ch,
				s;
			if (i == 0) s = t[2];
			else
				for (;;) {
					var o = n + r >> 1;
					if ((o ? t[o * 2 - 1] : 0) >= i) r = o;
					else {
						if (!(t[o * 2 + 1] < i)) {
							s = t[o * 2 + 2];
							break
						}
						n = o + 1
					}
				}
			var u = s ? s.indexOf("cm-overlay ") : -1;
			return u < 0 ? s : u == 0 ? null : s.slice(0, u - 1)
		},
		getModeAt: function (e) {
			var t = this.doc.mode;
			return t.innerMode ? S.innerMode(t, this.getTokenAt(e).state).mode : t
		},
		getHelper: function (e, t) {
			return this.getHelpers(e, t)[0]
		},
		getHelpers: function (e, t) {
			var n = [];
			if (!gi.hasOwnProperty(t)) return gi;
			var r = gi[t],
				i = this.getModeAt(e);
			if (typeof i[t] == "string") r[i[t]] && n.push(r[i[t]]);
			else if (i[t])
				for (var s = 0; s < i[t].length; s++) {
					var o = r[i[t][s]];
					o && n.push(o)
				} else i.helperType && r[i.helperType] ? n.push(r[i.helperType]) : r[i.name] && n.push(r[i.name]);
			for (var s = 0; s < r._global.length; s++) {
				var u = r._global[s];
				u.pred(i, this) && Fo(n, u.val) == -1 && n.push(u.val)
			}
			return n
		},
		getStateAfter: function (e, t) {
			var n = this.doc;
			return e = St(n, e == null ? n.first + n.size - 1 : e), Kt(this, e + 1, t)
		},
		cursorCoords: function (e, t) {
			var n, r = this.doc.sel.primary();
			return e == null ? n = r.head : typeof e == "object" ? n = xt(this.doc, e) : n = e ? r.from() : r.to(), yn(this, n, t || "page")
		},
		charCoords: function (e, t) {
			return gn(this, xt(this.doc, e), t || "page")
		},
		coordsChar: function (e, t) {
			return e = mn(this, e, t || "page"), En(this, e.left, e.top)
		},
		lineAtHeight: function (e, t) {
			return e = mn(this, {
				top: e,
				left: 0
			}, t || "page").top, Xs(this.doc, e + this.display.viewOffset)
		},
		heightAtLine: function (e, t) {
			var n = !1,
				r = this.doc.first + this.doc.size - 1;
			e < this.doc.first ? e = this.doc.first : e > r && (e = r, n = !0);
			var i = qs(this.doc, e);
			return vn(this, i, {
				top: 0,
				left: 0
			}, t || "page").top + (n ? this.doc.height - Vs(i) : 0)
		},
		defaultTextHeight: function () {
			return Tn(this.display)
		},
		defaultCharWidth: function () {
			return Nn(this.display)
		},
		setGutterMarker: In(function (e, t, n) {
			return si(this.doc, e, "gutter", function (e) {
				var r = e.gutterMarkers || (e.gutterMarkers = {});
				return r[t] = n, !n && Vo(r) && (e.gutterMarkers = null), !0
			})
		}),
		clearGutter: In(function (e) {
			var t = this,
				n = t.doc,
				r = n.first;
			n.iter(function (n) {
				n.gutterMarkers && n.gutterMarkers[e] && (n.gutterMarkers[e] = null, Wn(t, r, "gutter"), Vo(n.gutterMarkers) && (n.gutterMarkers = null)), ++r
			})
		}),
		addLineWidget: In(function (e, t, n) {
			return fs(this, e, t, n)
		}),
		removeLineWidget: function (e) {
			e.clear()
		},
		lineInfo: function (e) {
			if (typeof e == "number") {
				if (!Nt(this.doc, e)) return null;
				var t = e;
				e = qs(this.doc, e);
				if (!e) return null
			} else {
				var t = Ws(e);
				if (t == null) return null
			}
			return {
				line: t,
				handle: e,
				text: e.text,
				gutterMarkers: e.gutterMarkers,
				textClass: e.textClass,
				bgClass: e.bgClass,
				wrapClass: e.wrapClass,
				widgets: e.widgets
			}
		},
		getViewport: function () {
			return {
				from: this.display.viewFrom,
				to: this.display.viewTo
			}
		},
		addWidget: function (e, t, n, r, i) {
			var s = this.display;
			e = yn(this, xt(this.doc, e));
			var o = e.bottom,
				u = e.left;
			t.style.position = "absolute", s.sizer.appendChild(t);
			if (r == "over") o = e.top;
			else if (r == "above" || r == "near") {
				var a = Math.max(s.wrapper.clientHeight, this.doc.height),
					f = Math.max(s.sizer.clientWidth, s.lineSpace.clientWidth);
				(r == "above" || e.bottom + t.offsetHeight > a) && e.top > t.offsetHeight ? o = e.top - t.offsetHeight : e.bottom + t.offsetHeight <= a && (o = e.bottom), u + t.offsetWidth > f && (u = f - t.offsetWidth)
			}
			t.style.top = o + "px", t.style.left = t.style.right = "", i == "right" ? (u = s.sizer.clientWidth - t.offsetWidth, t.style.right = "0px") : (i == "left" ? u = 0 : i == "middle" && (u = (s.sizer.clientWidth - t.offsetWidth) / 2), t.style.left = u + "px"), n && Zr(this, u, o, u + t.offsetWidth, o + t.offsetHeight)
		},
		triggerOnKeyDown: In(Or),
		triggerOnKeyPress: In(Dr),
		triggerOnKeyUp: _r,
		execCommand: function (e) {
			if (wi.hasOwnProperty(e)) return wi[e](this)
		},
		findPosH: function (e, t, n, r) {
			var i = 1;
			t < 0 && (i = -1, t = -t);
			for (var s = 0, o = xt(this.doc, e); s < t; ++s) {
				o = ui(this.doc, o, i, n, r);
				if (o.hitSide) break
			}
			return o
		},
		moveH: In(function (e, t) {
			var n = this;
			n.extendSelectionsBy(function (r) {
				return n.display.shift || n.doc.extend || r.empty() ? ui(n.doc, r.head, e, t, n.options.rtlMoveVisually) : e < 0 ? r.from() : r.to()
			}, Oo)
		}),
		deleteH: In(function (e, t) {
			var n = this.doc.sel,
				r = this.doc;
			n.somethingSelected() ? r.replaceSelection("", null, "+delete") : oi(this, function (n) {
				var i = ui(r, n.head, e, t, !1);
				return e < 0 ? {
					from: i,
					to: n.head
				} : {
					from: n.head,
					to: i
				}
			})
		}),
		findPosV: function (e, t, n, r) {
			var i = 1,
				s = r;
			t < 0 && (i = -1, t = -t);
			for (var o = 0, u = xt(this.doc, e); o < t; ++o) {
				var a = yn(this, u, "div");
				s == null ? s = a.left : a.left = s, u = ai(this, a, i, n);
				if (u.hitSide) break
			}
			return u
		},
		moveV: In(function (e, t) {
			var n = this,
				r = this.doc,
				i = [],
				s = !n.display.shift && !r.extend && r.sel.somethingSelected();
			r.extendSelectionsBy(function (o) {
				if (s) return e < 0 ? o.from() : o.to();
				var u = yn(n, o.head, "div");
				o.goalColumn != null && (u.left = o.goalColumn), i.push(u.left);
				var a = ai(n, u, e, t);
				return t == "page" && o == r.sel.primary() && ti(n, null, gn(n, a, "div").top - u.top), a
			}, Oo);
			if (i.length)
				for (var o = 0; o < r.sel.ranges.length; o++) r.sel.ranges[o].goalColumn = i[o]
		}),
		findWordAt: function (e) {
			var t = this.doc,
				n = qs(t, e.line).text,
				r = e.ch,
				i = e.ch;
			if (n) {
				var s = this.getHelper(e, "wordChars");
				(e.xRel < 0 || i == n.length) && r ? --r : ++i;
				var o = n.charAt(r),
					u = Xo(o, s) ? function (e) {
						return Xo(e, s)
					} : /\s/.test(o) ? function (e) {
						return /\s/.test(e)
					} : function (e) {
						return !/\s/.test(e) && !Xo(e)
					};
				while (r > 0 && u(n.charAt(r - 1))) --r;
				while (i < n.length && u(n.charAt(i))) ++i
			}
			return new bt(pt(e.line, r), pt(e.line, i))
		},
		toggleOverwrite: function (e) {
			if (e != null && e == this.state.overwrite) return;
			(this.state.overwrite = !this.state.overwrite) ? ru(this.display.cursorDiv, "CodeMirror-overwrite"): nu(this.display.cursorDiv, "CodeMirror-overwrite"), yo(this, "overwriteToggle", this, this.state.overwrite)
		},
		hasFocus: function () {
			return eu() == this.display.input
		},
		scrollTo: In(function (e, t) {
			(e != null || t != null) && ri(this), e != null && (this.curOp.scrollLeft = e), t != null && (this.curOp.scrollTop = t)
		}),
		getScrollInfo: function () {
			var e = this.display.scroller,
				t = Co;
			return {
				left: e.scrollLeft,
				top: e.scrollTop,
				height: e.scrollHeight - t,
				width: e.scrollWidth - t,
				clientHeight: e.clientHeight - t,
				clientWidth: e.clientWidth - t
			}
		},
		scrollIntoView: In(function (e, t) {
			e == null ? (e = {
				from: this.doc.sel.primary().head,
				to: null
			}, t == null && (t = this.options.cursorScrollMargin)) : typeof e == "number" ? e = {
				from: pt(e, 0),
				to: null
			} : e.from == null && (e = {
				from: e,
				to: null
			}), e.to || (e.to = e.from), e.margin = t || 0;
			if (e.from.line != null) ri(this), this.curOp.scrollToPos = e;
			else {
				var n = ei(this, Math.min(e.from.left, e.to.left), Math.min(e.from.top, e.to.top) - e.margin, Math.max(e.from.right, e.to.right), Math.max(e.from.bottom, e.to.bottom) + e.margin);
				this.scrollTo(n.scrollLeft, n.scrollTop)
			}
		}),
		setSize: In(function (e, t) {
			function r(e) {
				return typeof e == "number" || /^\d+$/.test(String(e)) ? e + "px" : e
			}
			var n = this;
			e != null && (n.display.wrapper.style.width = r(e)), t != null && (n.display.wrapper.style.height = r(t)), n.options.lineWrapping && cn(this);
			var i = n.display.viewFrom;
			n.doc.iter(i, n.display.viewTo, function (e) {
				if (e.widgets)
					for (var t = 0; t < e.widgets.length; t++)
						if (e.widgets[t].noHScroll) {
							Wn(n, i, "widget");
							break
						}++i
			}), n.curOp.forceUpdate = !0, yo(n, "refresh", this)
		}),
		operation: function (e) {
			return jn(this, e)
		},
		refresh: In(function () {
			var e = this.display.cachedTextHeight;
			zn(this), this.curOp.forceUpdate = !0, hn(this), this.scrollTo(this.doc.scrollLeft, this.doc.scrollTop), D(this), (e == null || Math.abs(e - Tn(this.display)) > .5) && L(this), yo(this, "refresh", this)
		}),
		swapDoc: In(function (e) {
			var t = this.doc;
			return t.cm = null, Is(this, e), hn(this), er(this), this.scrollTo(e.scrollLeft, e.scrollTop), this.curOp.forceScroll = !0, wo(this, "swapDoc", this, t), t
		}),
		getInputField: function () {
			return this.display.input
		},
		getWrapperElement: function () {
			return this.display.wrapper
		},
		getScrollerElement: function () {
			return this.display.scroller
		},
		getGutterElement: function () {
			return this.display.gutters
		}
	}, No(S);
	var fi = S.defaults = {},
		li = S.optionHandlers = {},
		hi = S.Init = {
			toString: function () {
				return "CodeMirror.Init"
			}
		};
	ci("value", "", function (e, t) {
		e.setValue(t)
	}, !0), ci("mode", null, function (e, t) {
		e.doc.modeOption = t, T(e)
	}, !0), ci("indentUnit", 2, T, !0), ci("indentWithTabs", !1), ci("smartIndent", !0), ci("tabSize", 4, function (e) {
		N(e), hn(e), zn(e)
	}, !0), ci("specialChars", /[\t\u0000-\u0019\u00ad\u200b-\u200f\u2028\u2029\ufeff]/g, function (e, t) {
		e.options.specialChars = new RegExp(t.source + (t.test("	") ? "" : "|	"), "g"), e.refresh()
	}, !0), ci("specialCharPlaceholder", Ts, function (e) {
		e.refresh()
	}, !0), ci("electricChars", !0), ci("rtlMoveVisually", !m), ci("wholeLineUpdateBefore", !0), ci("theme", "default", function (e) {
		O(e), M(e)
	}, !0), ci("keyMap", "default", A), ci("extraKeys", null), ci("lineWrapping", !1, C, !0), ci("gutters", [], function (e) {
		B(e.options), M(e)
	}, !0), ci("fixedGutter", !0, function (e, t) {
		e.display.gutters.style.left = t ? W(e.display) + "px" : "0", e.refresh()
	}, !0), ci("coverGutterNextToScrollbar", !1, I, !0), ci("lineNumbers", !1, function (e) {
		B(e.options), M(e)
	}, !0), ci("firstLineNumber", 1, M, !0), ci("lineNumberFormatter", function (e) {
		return e
	}, M, !0), ci("showCursorWhenSelecting", !1, Ut, !0), ci("resetSelectionOnContextMenu", !0), ci("readOnly", !1, function (e, t) {
		t == "nocursor" ? (Hr(e), e.display.input.blur(), e.display.disabled = !0) : (e.display.disabled = !1, t || er(e))
	}), ci("disableInput", !1, function (e, t) {
		t || er(e)
	}, !0), ci("dragDrop", !0), ci("cursorBlinkRate", 530), ci("cursorScrollMargin", 0), ci("cursorHeight", 1, Ut, !0), ci("singleCursorHeightPerLine", !0, Ut, !0), ci("workTime", 100), ci("workDelay", 100), ci("flattenSpans", !0, N, !0), ci("addModeClass", !1, N, !0), ci("pollInterval", 100), ci("undoDepth", 200, function (e, t) {
		e.doc.history.undoDepth = t
	}), ci("historyEventDelay", 1250), ci("viewportMargin", 10, function (e) {
		e.refresh()
	}, !0), ci("maxHighlightLength", 1e4, N, !0), ci("moveInputWithCursor", !0, function (e, t) {
		t || (e.display.inputDiv.style.top = e.display.inputDiv.style.left = 0)
	}), ci("tabindex", null, function (e, t) {
		e.display.input.tabIndex = t || ""
	}), ci("autofocus", null);
	var pi = S.modes = {},
		di = S.mimeModes = {};
	S.defineMode = function (e, t) {
		!S.defaults.mode && e != "null" && (S.defaults.mode = e), arguments.length > 2 && (t.dependencies = Array.prototype.slice.call(arguments, 2)), pi[e] = t
	}, S.defineMIME = function (e, t) {
		di[e] = t
	}, S.resolveMode = function (e) {
		if (typeof e == "string" && di.hasOwnProperty(e)) e = di[e];
		else if (e && typeof e.name == "string" && di.hasOwnProperty(e.name)) {
			var t = di[e.name];
			typeof t == "string" && (t = {
				name: t
			}), e = qo(t, e), e.name = t.name
		} else if (typeof e == "string" && /^[\w\-]+\/[\w\-]+\+xml$/.test(e)) return S.resolveMode("application/xml");
		return typeof e == "string" ? {
			name: e
		} : e || {
			name: "null"
		}
	}, S.getMode = function (e, t) {
		var t = S.resolveMode(t),
			n = pi[t.name];
		if (!n) return S.getMode(e, "text/plain");
		var r = n(e, t);
		if (vi.hasOwnProperty(t.name)) {
			var i = vi[t.name];
			for (var s in i) {
				if (!i.hasOwnProperty(s)) continue;
				r.hasOwnProperty(s) && (r["_" + s] = r[s]), r[s] = i[s]
			}
		}
		r.name = t.name, t.helperType && (r.helperType = t.helperType);
		if (t.modeProps)
			for (var s in t.modeProps) r[s] = t.modeProps[s];
		return r
	}, S.defineMode("null", function () {
		return {
			token: function (e) {
				e.skipToEnd()
			}
		}
	}), S.defineMIME("text/plain", "null");
	var vi = S.modeExtensions = {};
	S.extendMode = function (e, t) {
		var n = vi.hasOwnProperty(e) ? vi[e] : vi[e] = {};
		Ro(t, n)
	}, S.defineExtension = function (e, t) {
		S.prototype[e] = t
	}, S.defineDocExtension = function (e, t) {
		Hs.prototype[e] = t
	}, S.defineOption = ci;
	var mi = [];
	S.defineInitHook = function (e) {
		mi.push(e)
	};
	var gi = S.helpers = {};
	S.registerHelper = function (e, t, n) {
		gi.hasOwnProperty(e) || (gi[e] = S[e] = {
			_global: []
		}), gi[e][t] = n
	}, S.registerGlobalHelper = function (e, t, n, r) {
		S.registerHelper(e, t, r), gi[e]._global.push({
			pred: n,
			val: r
		})
	};
	var yi = S.copyState = function (e, t) {
			if (t === !0) return t;
			if (e.copyState) return e.copyState(t);
			var n = {};
			for (var r in t) {
				var i = t[r];
				i instanceof Array && (i = i.concat([])), n[r] = i
			}
			return n
		},
		bi = S.startState = function (e, t, n) {
			return e.startState ? e.startState(t, n) : !0
		};
	S.innerMode = function (e, t) {
		while (e.innerMode) {
			var n = e.innerMode(t);
			if (!n || n.mode == e) break;
			t = n.state, e = n.mode
		}
		return n || {
			mode: e,
			state: t
		}
	};
	var wi = S.commands = {
			selectAll: function (e) {
				e.setSelection(pt(e.firstLine(), 0), pt(e.lastLine()), Lo)
			},
			singleSelection: function (e) {
				e.setSelection(e.getCursor("anchor"), e.getCursor("head"), Lo)
			},
			killLine: function (e) {
				oi(e, function (t) {
					if (t.empty()) {
						var n = qs(e.doc, t.head.line).text.length;
						return t.head.ch == n && t.head.line < e.lastLine() ? {
							from: t.head,
							to: pt(t.head.line + 1, 0)
						} : {
							from: t.head,
							to: pt(t.head.line, n)
						}
					}
					return {
						from: t.from(),
						to: t.to()
					}
				})
			},
			deleteLine: function (e) {
				oi(e, function (t) {
					return {
						from: pt(t.from().line, 0),
						to: xt(e.doc, pt(t.to().line + 1, 0))
					}
				})
			},
			delLineLeft: function (e) {
				oi(e, function (e) {
					return {
						from: pt(e.from().line, 0),
						to: e.from()
					}
				})
			},
			delWrappedLineLeft: function (e) {
				oi(e, function (t) {
					var n = e.charCoords(t.head, "div").top + 5,
						r = e.coordsChar({
							left: 0,
							top: n
						}, "div");
					return {
						from: r,
						to: t.from()
					}
				})
			},
			delWrappedLineRight: function (e) {
				oi(e, function (t) {
					var n = e.charCoords(t.head, "div").top + 5,
						r = e.coordsChar({
							left: e.display.lineDiv.offsetWidth + 100,
							top: n
						}, "div");
					return {
						from: t.from(),
						to: r
					}
				})
			},
			undo: function (e) {
				e.undo()
			},
			redo: function (e) {
				e.redo()
			},
			undoSelection: function (e) {
				e.undoSelection()
			},
			redoSelection: function (e) {
				e.redoSelection()
			},
			goDocStart: function (e) {
				e.extendSelection(pt(e.firstLine(), 0))
			},
			goDocEnd: function (e) {
				e.extendSelection(pt(e.lastLine()))
			},
			goLineStart: function (e) {
				e.extendSelectionsBy(function (t) {
					return ku(e, t.head.line)
				}, {
					origin: "+move",
					bias: 1
				})
			},
			goLineStartSmart: function (e) {
				e.extendSelectionsBy(function (t) {
					return Au(e, t.head)
				}, {
					origin: "+move",
					bias: 1
				})
			},
			goLineEnd: function (e) {
				e.extendSelectionsBy(function (t) {
					return Lu(e, t.head.line)
				}, {
					origin: "+move",
					bias: -1
				})
			},
			goLineRight: function (e) {
				e.extendSelectionsBy(function (t) {
					var n = e.charCoords(t.head, "div").top + 5;
					return e.coordsChar({
						left: e.display.lineDiv.offsetWidth + 100,
						top: n
					}, "div")
				}, Oo)
			},
			goLineLeft: function (e) {
				e.extendSelectionsBy(function (t) {
					var n = e.charCoords(t.head, "div").top + 5;
					return e.coordsChar({
						left: 0,
						top: n
					}, "div")
				}, Oo)
			},
			goLineLeftSmart: function (e) {
				e.extendSelectionsBy(function (t) {
					var n = e.charCoords(t.head, "div").top + 5,
						r = e.coordsChar({
							left: 0,
							top: n
						}, "div");
					return r.ch < e.getLine(r.line).search(/\S/) ? Au(e, t.head) : r
				}, Oo)
			},
			goLineUp: function (e) {
				e.moveV(-1, "line")
			},
			goLineDown: function (e) {
				e.moveV(1, "line")
			},
			goPageUp: function (e) {
				e.moveV(-1, "page")
			},
			goPageDown: function (e) {
				e.moveV(1, "page")
			},
			goCharLeft: function (e) {
				e.moveH(-1, "char")
			},
			goCharRight: function (e) {
				e.moveH(1, "char")
			},
			goColumnLeft: function (e) {
				e.moveH(-1, "column")
			},
			goColumnRight: function (e) {
				e.moveH(1, "column")
			},
			goWordLeft: function (e) {
				e.moveH(-1, "word")
			},
			goGroupRight: function (e) {
				e.moveH(1, "group")
			},
			goGroupLeft: function (e) {
				e.moveH(-1, "group")
			},
			goWordRight: function (e) {
				e.moveH(1, "word")
			},
			delCharBefore: function (e) {
				e.deleteH(-1, "char")
			},
			delCharAfter: function (e) {
				e.deleteH(1, "char")
			},
			delWordBefore: function (e) {
				e.deleteH(-1, "word")
			},
			delWordAfter: function (e) {
				e.deleteH(1, "word")
			},
			delGroupBefore: function (e) {
				e.deleteH(-1, "group")
			},
			delGroupAfter: function (e) {
				e.deleteH(1, "group")
			},
			indentAuto: function (e) {
				e.indentSelection("smart")
			},
			indentMore: function (e) {
				e.indentSelection("add")
			},
			indentLess: function (e) {
				e.indentSelection("subtract")
			},
			insertTab: function (e) {
				e.replaceSelection("	")
			},
			insertSoftTab: function (e) {
				var t = [],
					n = e.listSelections(),
					r = e.options.tabSize;
				for (var i = 0; i < n.length; i++) {
					var s = n[i].from(),
						o = _o(e.getLine(s.line), s.ch, r);
					t.push((new Array(r - o % r + 1)).join(" "))
				}
				e.replaceSelections(t)
			},
			defaultTab: function (e) {
				e.somethingSelected() ? e.indentSelection("add") : e.execCommand("insertTab")
			},
			transposeChars: function (e) {
				jn(e, function () {
					var t = e.listSelections(),
						n = [];
					for (var r = 0; r < t.length; r++) {
						var i = t[r].head,
							s = qs(e.doc, i.line).text;
						if (s) {
							i.ch == s.length && (i = new pt(i.line, i.ch - 1));
							if (i.ch > 0) i = new pt(i.line, i.ch + 1), e.replaceRange(s.charAt(i.ch - 1) + s.charAt(i.ch - 2), pt(i.line, i.ch - 2), i, "+transpose");
							else if (i.line > e.doc.first) {
								var o = qs(e.doc, i.line - 1).text;
								o && e.replaceRange(s.charAt(0) + "\n" + o.charAt(o.length - 1), pt(i.line - 1, o.length - 1), pt(i.line, 1), "+transpose")
							}
						}
						n.push(new bt(i, i))
					}
					e.setSelections(n)
				})
			},
			newlineAndIndent: function (e) {
				jn(e, function () {
					var t = e.listSelections().length;
					for (var n = 0; n < t; n++) {
						var r = e.listSelections()[n];
						e.replaceRange("\n", r.anchor, r.head, "+input"), e.indentLine(r.from().line + 1, null, !0), ni(e)
					}
				})
			},
			toggleOverwrite: function (e) {
				e.toggleOverwrite()
			}
		},
		Ei = S.keyMap = {};
	Ei.basic = {
		Left: "goCharLeft",
		Right: "goCharRight",
		Up: "goLineUp",
		Down: "goLineDown",
		End: "goLineEnd",
		Home: "goLineStartSmart",
		PageUp: "goPageUp",
		PageDown: "goPageDown",
		Delete: "delCharAfter",
		Backspace: "delCharBefore",
		"Shift-Backspace": "delCharBefore",
		Tab: "defaultTab",
		"Shift-Tab": "indentAuto",
		Enter: "newlineAndIndent",
		Insert: "toggleOverwrite",
		Esc: "singleSelection"
	}, Ei.pcDefault = {
		"Ctrl-A": "selectAll",
		"Ctrl-D": "deleteLine",
		"Ctrl-Z": "undo",
		"Shift-Ctrl-Z": "redo",
		"Ctrl-Y": "redo",
		"Ctrl-Home": "goDocStart",
		"Ctrl-End": "goDocEnd",
		"Ctrl-Up": "goLineUp",
		"Ctrl-Down": "goLineDown",
		"Ctrl-Left": "goGroupLeft",
		"Ctrl-Right": "goGroupRight",
		"Alt-Left": "goLineStart",
		"Alt-Right": "goLineEnd",
		"Ctrl-Backspace": "delGroupBefore",
		"Ctrl-Delete": "delGroupAfter",
		"Ctrl-S": "save",
		"Ctrl-F": "find",
		"Ctrl-G": "findNext",
		"Shift-Ctrl-G": "findPrev",
		"Shift-Ctrl-F": "replace",
		"Shift-Ctrl-R": "replaceAll",
		"Ctrl-[": "indentLess",
		"Ctrl-]": "indentMore",
		"Ctrl-U": "undoSelection",
		"Shift-Ctrl-U": "redoSelection",
		"Alt-U": "redoSelection",
		fallthrough: "basic"
	}, Ei.macDefault = {
		"Cmd-A": "selectAll",
		"Cmd-D": "deleteLine",
		"Cmd-Z": "undo",
		"Shift-Cmd-Z": "redo",
		"Cmd-Y": "redo",
		"Cmd-Home": "goDocStart",
		"Cmd-Up": "goDocStart",
		"Cmd-End": "goDocEnd",
		"Cmd-Down": "goDocEnd",
		"Alt-Left": "goGroupLeft",
		"Alt-Right": "goGroupRight",
		"Cmd-Left": "goLineLeft",
		"Cmd-Right": "goLineRight",
		"Alt-Backspace": "delGroupBefore",
		"Ctrl-Alt-Backspace": "delGroupAfter",
		"Alt-Delete": "delGroupAfter",
		"Cmd-S": "save",
		"Cmd-F": "find",
		"Cmd-G": "findNext",
		"Shift-Cmd-G": "findPrev",
		"Cmd-Alt-F": "replace",
		"Shift-Cmd-Alt-F": "replaceAll",
		"Cmd-[": "indentLess",
		"Cmd-]": "indentMore",
		"Cmd-Backspace": "delWrappedLineLeft",
		"Cmd-Delete": "delWrappedLineRight",
		"Cmd-U": "undoSelection",
		"Shift-Cmd-U": "redoSelection",
		"Ctrl-Up": "goDocStart",
		"Ctrl-Down": "goDocEnd",
		fallthrough: ["basic", "emacsy"]
	}, Ei.emacsy = {
		"Ctrl-F": "goCharRight",
		"Ctrl-B": "goCharLeft",
		"Ctrl-P": "goLineUp",
		"Ctrl-N": "goLineDown",
		"Alt-F": "goWordRight",
		"Alt-B": "goWordLeft",
		"Ctrl-A": "goLineStart",
		"Ctrl-E": "goLineEnd",
		"Ctrl-V": "goPageDown",
		"Shift-Ctrl-V": "goPageUp",
		"Ctrl-D": "delCharAfter",
		"Ctrl-H": "delCharBefore",
		"Alt-D": "delWordAfter",
		"Alt-Backspace": "delWordBefore",
		"Ctrl-K": "killLine",
		"Ctrl-T": "transposeChars"
	}, Ei["default"] = v ? Ei.macDefault : Ei.pcDefault;
	var xi = S.lookupKey = function (e, t, n) {
			function r(t) {
				t = Si(t);
				var i = t[e];
				if (i === !1) return "stop";
				if (i != null && n(i)) return !0;
				if (t.nofallthrough) return "stop";
				var s = t.fallthrough;
				if (s == null) return !1;
				if (Object.prototype.toString.call(s) != "[object Array]") return r(s);
				for (var o = 0; o < s.length; ++o) {
					var u = r(s[o]);
					if (u) return u
				}
				return !1
			}
			for (var i = 0; i < t.length; ++i) {
				var s = r(t[i]);
				if (s) return s != "stop"
			}
		},
		Ti = S.isModifierKey = function (e) {
			var t = Eu[e.keyCode];
			return t == "Ctrl" || t == "Alt" || t == "Shift" || t == "Mod"
		},
		Ni = S.keyName = function (e, t) {
			if (a && e.keyCode == 34 && e["char"]) return !1;
			var n = Eu[e.keyCode];
			if (n == null || e.altGraphKey) return !1;
			e.altKey && (n = "Alt-" + n);
			if (y ? e.metaKey : e.ctrlKey) n = "Ctrl-" + n;
			if (y ? e.ctrlKey : e.metaKey) n = "Cmd-" + n;
			return !t && e.shiftKey && (n = "Shift-" + n), n
		};
	S.fromTextArea = function (e, t) {
		function r() {
			e.value = a.getValue()
		}
		t || (t = {}), t.value = e.value, !t.tabindex && e.tabindex && (t.tabindex = e.tabindex), !t.placeholder && e.placeholder && (t.placeholder = e.placeholder);
		if (t.autofocus == null) {
			var n = eu();
			t.autofocus = n == e || e.getAttribute("autofocus") != null && n == document.body
		}
		if (e.form) {
			mo(e.form, "submit", r);
			if (!t.leaveSubmitMethodAlone) {
				var i = e.form,
					s = i.submit;
				try {
					var o = i.submit = function () {
						r(), i.submit = s, i.submit(), i.submit = o
					}
				} catch (u) {}
			}
		}
		e.style.display = "none";
		var a = S(function (t) {
			e.parentNode.insertBefore(t, e.nextSibling)
		}, t);
		return a.save = r, a.getTextArea = function () {
			return e
		}, a.toTextArea = function () {
			a.toTextArea = isNaN, r(), e.parentNode.removeChild(a.getWrapperElement()), e.style.display = "", e.form && (go(e.form, "submit", r), typeof e.form.submit == "function" && (e.form.submit = s))
		}, a
	};
	var Ci = S.StringStream = function (e, t) {
		this.pos = this.start = 0, this.string = e, this.tabSize = t || 8, this.lastColumnPos = this.lastColumnValue = 0, this.lineStart = 0
	};
	Ci.prototype = {
		eol: function () {
			return this.pos >= this.string.length
		},
		sol: function () {
			return this.pos == this.lineStart
		},
		peek: function () {
			return this.string.charAt(this.pos) || undefined
		},
		next: function () {
			if (this.pos < this.string.length) return this.string.charAt(this.pos++)
		},
		eat: function (e) {
			var t = this.string.charAt(this.pos);
			if (typeof e == "string") var n = t == e;
			else var n = t && (e.test ? e.test(t) : e(t));
			if (n) return ++this.pos, t
		},
		eatWhile: function (e) {
			var t = this.pos;
			while (this.eat(e));
			return this.pos > t
		},
		eatSpace: function () {
			var e = this.pos;
			while (/[\s\u00a0]/.test(this.string.charAt(this.pos))) ++this.pos;
			return this.pos > e
		},
		skipToEnd: function () {
			this.pos = this.string.length
		},
		skipTo: function (e) {
			var t = this.string.indexOf(e, this.pos);
			if (t > -1) return this.pos = t, !0
		},
		backUp: function (e) {
			this.pos -= e
		},
		column: function () {
			return this.lastColumnPos < this.start && (this.lastColumnValue = _o(this.string, this.start, this.tabSize, this.lastColumnPos, this.lastColumnValue), this.lastColumnPos = this.start), this.lastColumnValue - (this.lineStart ? _o(this.string, this.lineStart, this.tabSize) : 0)
		},
		indentation: function () {
			return _o(this.string, null, this.tabSize) - (this.lineStart ? _o(this.string, this.lineStart, this.tabSize) : 0)
		},
		match: function (e, t, n) {
			if (typeof e != "string") {
				var s = this.string.slice(this.pos).match(e);
				return s && s.index > 0 ? null : (s && t !== !1 && (this.pos += s[0].length), s)
			}
			var r = function (e) {
					return n ? e.toLowerCase() : e
				},
				i = this.string.substr(this.pos, e.length);
			if (r(i) == r(e)) return t !== !1 && (this.pos += e.length), !0
		},
		current: function () {
			return this.string.slice(this.start, this.pos)
		},
		hideFirstChars: function (e, t) {
			this.lineStart += e;
			try {
				return t()
			} finally {
				this.lineStart -= e
			}
		}
	};
	var ki = S.TextMarker = function (e, t) {
		this.lines = [], this.type = t, this.doc = e
	};
	No(ki), ki.prototype.clear = function () {
		if (this.explicitlyCleared) return;
		var e = this.doc.cm,
			t = e && !e.curOp;
		t && Ln(e);
		if (To(this, "clear")) {
			var n = this.find();
			n && wo(this, "clear", n.from, n.to)
		}
		var r = null,
			i = null;
		for (var s = 0; s < this.lines.length; ++s) {
			var o = this.lines[s],
				u = Bi(o.markedSpans, this);
			e && !this.collapsed ? Wn(e, Ws(o), "text") : e && (u.to != null && (i = Ws(o)), u.from != null && (r = Ws(o))), o.markedSpans = ji(o.markedSpans, u), u.from == null && this.collapsed && !is(this.doc, o) && e && zs(o, Tn(e.display))
		}
		if (e && this.collapsed && !e.options.lineWrapping)
			for (var s = 0; s < this.lines.length; ++s) {
				var a = es(this.lines[s]),
					f = P(a);
				f > e.display.maxLineLength && (e.display.maxLine = a, e.display.maxLineLength = f, e.display.maxLineChanged = !0)
			}
		r != null && e && this.collapsed && zn(e, r, i + 1), this.lines.length = 0, this.explicitlyCleared = !0, this.atomic && this.doc.cantEdit && (this.doc.cantEdit = !1, e && jt(e.doc)), e && wo(e, "markerCleared", e, this), t && On(e), this.parent && this.parent.clear()
	}, ki.prototype.find = function (e, t) {
		e == null && this.type == "bookmark" && (e = 1);
		var n, r;
		for (var i = 0; i < this.lines.length; ++i) {
			var s = this.lines[i],
				o = Bi(s.markedSpans, this);
			if (o.from != null) {
				n = pt(t ? s : Ws(s), o.from);
				if (e == -1) return n
			}
			if (o.to != null) {
				r = pt(t ? s : Ws(s), o.to);
				if (e == 1) return r
			}
		}
		return n && {
			from: n,
			to: r
		}
	}, ki.prototype.changed = function () {
		var e = this.find(-1, !0),
			t = this,
			n = this.doc.cm;
		if (!e || !n) return;
		jn(n, function () {
			var r = e.line,
				i = Ws(e.line),
				s = rn(n, i);
			s && (ln(s), n.curOp.selectionChanged = n.curOp.forceUpdate = !0), n.curOp.updateMaxLine = !0;
			if (!is(t.doc, r) && t.height != null) {
				var o = t.height;
				t.height = null;
				var u = as(t) - o;
				u && zs(r, r.height + u)
			}
		})
	}, ki.prototype.attachLine = function (e) {
		if (!this.lines.length && this.doc.cm) {
			var t = this.doc.cm.curOp;
			(!t.maybeHiddenMarkers || Fo(t.maybeHiddenMarkers, this) == -1) && (t.maybeUnhiddenMarkers || (t.maybeUnhiddenMarkers = [])).push(this)
		}
		this.lines.push(e)
	}, ki.prototype.detachLine = function (e) {
		this.lines.splice(Fo(this.lines, e), 1);
		if (!this.lines.length && this.doc.cm) {
			var t = this.doc.cm.curOp;
			(t.maybeHiddenMarkers || (t.maybeHiddenMarkers = [])).push(this)
		}
	};
	var Li = 0,
		Oi = S.SharedTextMarker = function (e, t) {
			this.markers = e, this.primary = t;
			for (var n = 0; n < e.length; ++n) e[n].parent = this
		};
	No(Oi), Oi.prototype.clear = function () {
		if (this.explicitlyCleared) return;
		this.explicitlyCleared = !0;
		for (var e = 0; e < this.markers.length; ++e) this.markers[e].clear();
		wo(this, "clear")
	}, Oi.prototype.find = function (e, t) {
		return this.primary.find(e, t)
	};
	var os = S.LineWidget = function (e, t, n) {
		if (n)
			for (var r in n) n.hasOwnProperty(r) && (this[r] = n[r]);
		this.cm = e, this.node = t
	};
	No(os), os.prototype.clear = function () {
		var e = this.cm,
			t = this.line.widgets,
			n = this.line,
			r = Ws(n);
		if (r == null || !t) return;
		for (var i = 0; i < t.length; ++i) t[i] == this && t.splice(i--, 1);
		t.length || (n.widgets = null);
		var s = as(this);
		jn(e, function () {
			us(e, n, -s), Wn(e, r, "widget"), zs(n, Math.max(0, n.height - s))
		})
	}, os.prototype.changed = function () {
		var e = this.height,
			t = this.cm,
			n = this.line;
		this.height = null;
		var r = as(this) - e;
		if (!r) return;
		jn(t, function () {
			t.curOp.forceUpdate = !0, us(t, n, r), zs(n, n.height + r)
		})
	};
	var ls = S.Line = function (e, t, n) {
		this.text = e, Vi(this, t), this.height = n ? n(this) : 1
	};
	No(ls), ls.prototype.lineNo = function () {
		return Ws(this)
	};
	var ws = {},
		Es = {};
	_s.prototype = {
		chunkSize: function () {
			return this.lines.length
		},
		removeInner: function (e, t) {
			for (var n = e, r = e + t; n < r; ++n) {
				var i = this.lines[n];
				this.height -= i.height, hs(i), wo(i, "delete")
			}
			this.lines.splice(e, t)
		},
		collapse: function (e) {
			e.push.apply(e, this.lines)
		},
		insertInner: function (e, t, n) {
			this.height += n, this.lines = this.lines.slice(0, e).concat(t).concat(this.lines.slice(e));
			for (var r = 0; r < t.length; ++r) t[r].parent = this
		},
		iterN: function (e, t, n) {
			for (var r = e + t; e < r; ++e)
				if (n(this.lines[e])) return !0
		}
	}, Ds.prototype = {
		chunkSize: function () {
			return this.size
		},
		removeInner: function (e, t) {
			this.size -= t;
			for (var n = 0; n < this.children.length; ++n) {
				var r = this.children[n],
					i = r.chunkSize();
				if (e < i) {
					var s = Math.min(t, i - e),
						o = r.height;
					r.removeInner(e, s), this.height -= o - r.height, i == s && (this.children.splice(n--, 1), r.parent = null);
					if ((t -= s) == 0) break;
					e = 0
				} else e -= i
			}
			if (this.size - t < 25 && (this.children.length > 1 || !(this.children[0] instanceof _s))) {
				var u = [];
				this.collapse(u), this.children = [new _s(u)], this.children[0].parent = this
			}
		},
		collapse: function (e) {
			for (var t = 0; t < this.children.length; ++t) this.children[t].collapse(e)
		},
		insertInner: function (e, t, n) {
			this.size += t.length, this.height += n;
			for (var r = 0; r < this.children.length; ++r) {
				var i = this.children[r],
					s = i.chunkSize();
				if (e <= s) {
					i.insertInner(e, t, n);
					if (i.lines && i.lines.length > 50) {
						while (i.lines.length > 50) {
							var o = i.lines.splice(i.lines.length - 25, 25),
								u = new _s(o);
							i.height -= u.height, this.children.splice(r + 1, 0, u), u.parent = this
						}
						this.maybeSpill()
					}
					break
				}
				e -= s
			}
		},
		maybeSpill: function () {
			if (this.children.length <= 10) return;
			var e = this;
			do {
				var t = e.children.splice(e.children.length - 5, 5),
					n = new Ds(t);
				if (!e.parent) {
					var r = new Ds(e.children);
					r.parent = e, e.children = [r, n], e = r
				} else {
					e.size -= n.size, e.height -= n.height;
					var i = Fo(e.parent.children, e);
					e.parent.children.splice(i + 1, 0, n)
				}
				n.parent = e.parent
			} while (e.children.length > 10);
			e.parent.maybeSpill()
		},
		iterN: function (e, t, n) {
			for (var r = 0; r < this.children.length; ++r) {
				var i = this.children[r],
					s = i.chunkSize();
				if (e < s) {
					var o = Math.min(t, s - e);
					if (i.iterN(e, o, n)) return !0;
					if ((t -= o) == 0) break;
					e = 0
				} else e -= s
			}
		}
	};
	var Ps = 0,
		Hs = S.Doc = function (e, t, n) {
			if (!(this instanceof Hs)) return new Hs(e, t, n);
			n == null && (n = 0), Ds.call(this, [new _s([new ls("", null)])]), this.first = n, this.scrollTop = this.scrollLeft = 0, this.cantEdit = !1, this.cleanGeneration = 1, this.frontier = n;
			var r = pt(n, 0);
			this.sel = Et(r), this.history = new Js(null), this.id = ++Ps, this.modeOption = t, typeof e == "string" && (e = mu(e)), Ms(this, {
				from: r,
				to: r,
				text: e
			}), Pt(this, Et(r), Lo)
		};
	Hs.prototype = qo(Ds.prototype, {
		constructor: Hs,
		iter: function (e, t, n) {
			n ? this.iterN(e - this.first, t - e, n) : this.iterN(this.first, this.first + this.size, e)
		},
		insert: function (e, t) {
			var n = 0;
			for (var r = 0; r < t.length; ++r) n += t[r].height;
			this.insertInner(e - this.first, t, n)
		},
		remove: function (e, t) {
			this.removeInner(e - this.first, t)
		},
		getValue: function (e) {
			var t = Us(this, this.first, this.first + this.size);
			return e === !1 ? t : t.join(e || "\n")
		},
		setValue: qn(function (e) {
			var t = pt(this.first, 0),
				n = this.first + this.size - 1;
			Wr(this, {
				from: t,
				to: pt(n, qs(this, n).text.length),
				text: mu(e),
				origin: "setValue"
			}, !0), Pt(this, Et(t))
		}),
		replaceRange: function (e, t, n, r) {
			t = xt(this, t), n = n ? xt(this, n) : t, Qr(this, e, t, n, r)
		},
		getRange: function (e, t, n) {
			var r = Rs(this, xt(this, e), xt(this, t));
			return n === !1 ? r : r.join(n || "\n")
		},
		getLine: function (e) {
			var t = this.getLineHandle(e);
			return t && t.text
		},
		getLineHandle: function (e) {
			if (Nt(this, e)) return qs(this, e)
		},
		getLineNumber: function (e) {
			return Ws(e)
		},
		getLineHandleVisualStart: function (e) {
			return typeof e == "number" && (e = qs(this, e)), es(e)
		},
		lineCount: function () {
			return this.size
		},
		firstLine: function () {
			return this.first
		},
		lastLine: function () {
			return this.first + this.size - 1
		},
		clipPos: function (e) {
			return xt(this, e)
		},
		getCursor: function (e) {
			var t = this.sel.primary(),
				n;
			return e == null || e == "head" ? n = t.head : e == "anchor" ? n = t.anchor : e == "end" || e == "to" || e === !1 ? n = t.to() : n = t.from(), n
		},
		listSelections: function () {
			return this.sel.ranges
		},
		somethingSelected: function () {
			return this.sel.somethingSelected()
		},
		setCursor: qn(function (e, t, n) {
			Mt(this, xt(this, typeof e == "number" ? pt(e, t || 0) : e), null, n)
		}),
		setSelection: qn(function (e, t, n) {
			Mt(this, xt(this, e), xt(this, t || e), n)
		}),
		extendSelection: qn(function (e, t, n) {
			Lt(this, xt(this, e), t && xt(this, t), n)
		}),
		extendSelections: qn(function (e, t) {
			At(this, Ct(this, e, t))
		}),
		extendSelectionsBy: qn(function (e, t) {
			At(this, Io(this.sel.ranges, e), t)
		}),
		setSelections: qn(function (e, t, n) {
			if (!e.length) return;
			for (var r = 0, i = []; r < e.length; r++) i[r] = new bt(xt(this, e[r].anchor), xt(this, e[r].head));
			t == null && (t = Math.min(e.length - 1, this.sel.primIndex)), Pt(this, wt(i, t), n)
		}),
		addSelection: qn(function (e, t, n) {
			var r = this.sel.ranges.slice(0);
			r.push(new bt(xt(this, e), xt(this, t || e))), Pt(this, wt(r, r.length - 1), n)
		}),
		getSelection: function (e) {
			var t = this.sel.ranges,
				n;
			for (var r = 0; r < t.length; r++) {
				var i = Rs(this, t[r].from(), t[r].to());
				n = n ? n.concat(i) : i
			}
			return e === !1 ? n : n.join(e || "\n")
		},
		getSelections: function (e) {
			var t = [],
				n = this.sel.ranges;
			for (var r = 0; r < n.length; r++) {
				var i = Rs(this, n[r].from(), n[r].to());
				e !== !1 && (i = i.join(e || "\n")), t[r] = i
			}
			return t
		},
		replaceSelection: function (e, t, n) {
			var r = [];
			for (var i = 0; i < this.sel.ranges.length; i++) r[i] = e;
			this.replaceSelections(r, t, n || "+input")
		},
		replaceSelections: qn(function (e, t, n) {
			var r = [],
				i = this.sel;
			for (var s = 0; s < i.ranges.length; s++) {
				var o = i.ranges[s];
				r[s] = {
					from: o.from(),
					to: o.to(),
					text: mu(e[s]),
					origin: n
				}
			}
			var u = t && t != "end" && Ur(this, r, t);
			for (var s = r.length - 1; s >= 0; s--) Wr(this, r[s]);
			u ? Dt(this, u) : this.cm && ni(this.cm)
		}),
		undo: qn(function () {
			Vr(this, "undo")
		}),
		redo: qn(function () {
			Vr(this, "redo")
		}),
		undoSelection: qn(function () {
			Vr(this, "undo", !0)
		}),
		redoSelection: qn(function () {
			Vr(this, "redo", !0)
		}),
		setExtending: function (e) {
			this.extend = e
		},
		getExtending: function () {
			return this.extend
		},
		historySize: function () {
			var e = this.history,
				t = 0,
				n = 0;
			for (var r = 0; r < e.done.length; r++) e.done[r].ranges || ++t;
			for (var r = 0; r < e.undone.length; r++) e.undone[r].ranges || ++n;
			return {
				undo: t,
				redo: n
			}
		},
		clearHistory: function () {
			this.history = new Js(this.history.maxGeneration)
		},
		markClean: function () {
			this.cleanGeneration = this.changeGeneration(!0)
		},
		changeGeneration: function (e) {
			return e && (this.history.lastOp = this.history.lastSelOp = this.history.lastOrigin = null), this.history.generation
		},
		isClean: function (e) {
			return this.history.generation == (e || this.cleanGeneration)
		},
		getHistory: function () {
			return {
				done: so(this.history.done),
				undone: so(this.history.undone)
			}
		},
		setHistory: function (e) {
			var t = this.history = new Js(this.history.maxGeneration);
			t.done = so(e.done.slice(0), null, !0), t.undone = so(e.undone.slice(0), null, !0)
		},
		addLineClass: qn(function (e, t, n) {
			return si(this, e, "class", function (e) {
				var r = t == "text" ? "textClass" : t == "background" ? "bgClass" : "wrapClass";
				if (!e[r]) e[r] = n;
				else {
					if ((new RegExp("(?:^|\\s)" + n + "(?:$|\\s)")).test(e[r])) return !1;
					e[r] += " " + n
				}
				return !0
			})
		}),
		removeLineClass: qn(function (e, t, n) {
			return si(this, e, "class", function (e) {
				var r = t == "text" ? "textClass" : t == "background" ? "bgClass" : "wrapClass",
					i = e[r];
				if (!i) return !1;
				if (n == null) e[r] = null;
				else {
					var s = i.match(new RegExp("(?:^|\\s+)" + n + "(?:$|\\s+)"));
					if (!s) return !1;
					var o = s.index + s[0].length;
					e[r] = i.slice(0, s.index) + (!s.index || o == i.length ? "" : " ") + i.slice(o) || null
				}
				return !0
			})
		}),
		markText: function (e, t, n) {
			return Ai(this, xt(this, e), xt(this, t), n, "range")
		},
		setBookmark: function (e, t) {
			var n = {
				replacedWith: t && (t.nodeType == null ? t.widget : t),
				insertLeft: t && t.insertLeft,
				clearWhenEmpty: !1,
				shared: t && t.shared
			};
			return e = xt(this, e), Ai(this, e, e, n, "bookmark")
		},
		findMarksAt: function (e) {
			e = xt(this, e);
			var t = [],
				n = qs(this, e.line).markedSpans;
			if (n)
				for (var r = 0; r < n.length; ++r) {
					var i = n[r];
					(i.from == null || i.from <= e.ch) && (i.to == null || i.to >= e.ch) && t.push(i.marker.parent || i.marker)
				}
			return t
		},
		findMarks: function (e, t, n) {
			e = xt(this, e), t = xt(this, t);
			var r = [],
				i = e.line;
			return this.iter(e.line, t.line + 1, function (s) {
				var o = s.markedSpans;
				if (o)
					for (var u = 0; u < o.length; u++) {
						var a = o[u];
						!(i == e.line && e.ch > a.to || a.from == null && i != e.line || i == t.line && a.from > t.ch) && (!n || n(a.marker)) && r.push(a.marker.parent || a.marker)
					}++i
			}), r
		},
		getAllMarks: function () {
			var e = [];
			return this.iter(function (t) {
				var n = t.markedSpans;
				if (n)
					for (var r = 0; r < n.length; ++r) n[r].from != null && e.push(n[r].marker)
			}), e
		},
		posFromIndex: function (e) {
			var t, n = this.first;
			return this.iter(function (r) {
				var i = r.text.length + 1;
				if (i > e) return t = e, !0;
				e -= i, ++n
			}), xt(this, pt(n, t))
		},
		indexFromPos: function (e) {
			e = xt(this, e);
			var t = e.ch;
			return e.line < this.first || e.ch < 0 ? 0 : (this.iter(this.first, e.line, function (e) {
				t += e.text.length + 1
			}), t)
		},
		copy: function (e) {
			var t = new Hs(Us(this, this.first, this.first + this.size), this.modeOption, this.first);
			return t.scrollTop = this.scrollTop, t.scrollLeft = this.scrollLeft, t.sel = this.sel, t.extend = !1, e && (t.history.undoDepth = this.history.undoDepth, t.setHistory(this.getHistory())), t
		},
		linkedDoc: function (e) {
			e || (e = {});
			var t = this.first,
				n = this.first + this.size;
			e.from != null && e.from > t && (t = e.from), e.to != null && e.to < n && (n = e.to);
			var r = new Hs(Us(this, t, n), e.mode || this.modeOption, t);
			return e.sharedHist && (r.history = this.history), (this.linked || (this.linked = [])).push({
				doc: r,
				sharedHist: e.sharedHist
			}), r.linked = [{
				doc: this,
				isParent: !0,
				sharedHist: e.sharedHist
			}], Di(r, _i(this)), r
		},
		unlinkDoc: function (e) {
			e instanceof S && (e = e.doc);
			if (this.linked)
				for (var t = 0; t < this.linked.length; ++t) {
					var n = this.linked[t];
					if (n.doc != e) continue;
					this.linked.splice(t, 1), e.unlinkDoc(this), Pi(_i(this));
					break
				}
			if (e.history == this.history) {
				var r = [e.id];
				Fs(e, function (e) {
					r.push(e.id)
				}, !0), e.history = new Js(null), e.history.done = so(this.history.done, r), e.history.undone = so(this.history.undone, r)
			}
		},
		iterLinkedDocs: function (e) {
			Fs(this, e)
		},
		getMode: function () {
			return this.mode
		},
		getEditor: function () {
			return this.cm
		}
	}), Hs.prototype.eachLine = Hs.prototype.iter;
	var Bs = "iter insert remove copy getEditor".split(" ");
	for (var js in Hs.prototype) Hs.prototype.hasOwnProperty(js) && Fo(Bs, js) < 0 && (S.prototype[js] = function (e) {
		return function () {
			return e.apply(this.doc, arguments)
		}
	}(Hs.prototype[js]));
	No(Hs);
	var fo = S.e_preventDefault = function (e) {
			e.preventDefault ? e.preventDefault() : e.returnValue = !1
		},
		lo = S.e_stopPropagation = function (e) {
			e.stopPropagation ? e.stopPropagation() : e.cancelBubble = !0
		},
		ho = S.e_stop = function (e) {
			fo(e), lo(e)
		},
		mo = S.on = function (e, t, n) {
			if (e.addEventListener) e.addEventListener(t, n, !1);
			else if (e.attachEvent) e.attachEvent("on" + t, n);
			else {
				var r = e._handlers || (e._handlers = {}),
					i = r[t] || (r[t] = []);
				i.push(n)
			}
		},
		go = S.off = function (e, t, n) {
			if (e.removeEventListener) e.removeEventListener(t, n, !1);
			else if (e.detachEvent) e.detachEvent("on" + t, n);
			else {
				var r = e._handlers && e._handlers[t];
				if (!r) return;
				for (var i = 0; i < r.length; ++i)
					if (r[i] == n) {
						r.splice(i, 1);
						break
					}
			}
		},
		yo = S.signal = function (e, t) {
			var n = e._handlers && e._handlers[t];
			if (!n) return;
			var r = Array.prototype.slice.call(arguments, 2);
			for (var i = 0; i < n.length; ++i) n[i].apply(null, r)
		},
		bo = null,
		Co = 30,
		ko = S.Pass = {
			toString: function () {
				return "CodeMirror.Pass"
			}
		},
		Lo = {
			scroll: !1
		},
		Ao = {
			origin: "*mouse"
		},
		Oo = {
			origin: "+move"
		};
	Mo.prototype.set = function (e, t) {
		clearTimeout(this.id), this.id = setTimeout(t, e)
	};
	var _o = S.countColumn = function (e, t, n, r, i) {
			t == null && (t = e.search(/[^\s\u00a0]/), t == -1 && (t = e.length));
			for (var s = r || 0, o = i || 0;;) {
				var u = e.indexOf("	", s);
				if (u < 0 || u >= t) return o + (t - s);
				o += u - s, o += n - o % n, s = u + 1
			}
		},
		Po = [""],
		jo = function (e) {
			e.select()
		};
	p ? jo = function (e) {
		e.selectionStart = 0, e.selectionEnd = e.value.length
	} : r && (jo = function (e) {
		try {
			e.select()
		} catch (t) {}
	}), [].indexOf && (Fo = function (e, t) {
		return e.indexOf(t)
	}), [].map && (Io = function (e, t) {
		return e.map(t)
	});
	var zo = /[\u00df\u0590-\u05f4\u0600-\u06ff\u3040-\u309f\u30a0-\u30ff\u3400-\u4db5\u4e00-\u9fcc\uac00-\ud7af]/,
		Wo = S.isWordChar = function (e) {
			return /\w/.test(e) || e > "" && (e.toUpperCase() != e.toLowerCase() || zo.test(e))
		},
		$o = /[\u0300-\u036f\u0483-\u0489\u0591-\u05bd\u05bf\u05c1\u05c2\u05c4\u05c5\u05c7\u0610-\u061a\u064b-\u065e\u0670\u06d6-\u06dc\u06de-\u06e4\u06e7\u06e8\u06ea-\u06ed\u0711\u0730-\u074a\u07a6-\u07b0\u07eb-\u07f3\u0816-\u0819\u081b-\u0823\u0825-\u0827\u0829-\u082d\u0900-\u0902\u093c\u0941-\u0948\u094d\u0951-\u0955\u0962\u0963\u0981\u09bc\u09be\u09c1-\u09c4\u09cd\u09d7\u09e2\u09e3\u0a01\u0a02\u0a3c\u0a41\u0a42\u0a47\u0a48\u0a4b-\u0a4d\u0a51\u0a70\u0a71\u0a75\u0a81\u0a82\u0abc\u0ac1-\u0ac5\u0ac7\u0ac8\u0acd\u0ae2\u0ae3\u0b01\u0b3c\u0b3e\u0b3f\u0b41-\u0b44\u0b4d\u0b56\u0b57\u0b62\u0b63\u0b82\u0bbe\u0bc0\u0bcd\u0bd7\u0c3e-\u0c40\u0c46-\u0c48\u0c4a-\u0c4d\u0c55\u0c56\u0c62\u0c63\u0cbc\u0cbf\u0cc2\u0cc6\u0ccc\u0ccd\u0cd5\u0cd6\u0ce2\u0ce3\u0d3e\u0d41-\u0d44\u0d4d\u0d57\u0d62\u0d63\u0dca\u0dcf\u0dd2-\u0dd4\u0dd6\u0ddf\u0e31\u0e34-\u0e3a\u0e47-\u0e4e\u0eb1\u0eb4-\u0eb9\u0ebb\u0ebc\u0ec8-\u0ecd\u0f18\u0f19\u0f35\u0f37\u0f39\u0f71-\u0f7e\u0f80-\u0f84\u0f86\u0f87\u0f90-\u0f97\u0f99-\u0fbc\u0fc6\u102d-\u1030\u1032-\u1037\u1039\u103a\u103d\u103e\u1058\u1059\u105e-\u1060\u1071-\u1074\u1082\u1085\u1086\u108d\u109d\u135f\u1712-\u1714\u1732-\u1734\u1752\u1753\u1772\u1773\u17b7-\u17bd\u17c6\u17c9-\u17d3\u17dd\u180b-\u180d\u18a9\u1920-\u1922\u1927\u1928\u1932\u1939-\u193b\u1a17\u1a18\u1a56\u1a58-\u1a5e\u1a60\u1a62\u1a65-\u1a6c\u1a73-\u1a7c\u1a7f\u1b00-\u1b03\u1b34\u1b36-\u1b3a\u1b3c\u1b42\u1b6b-\u1b73\u1b80\u1b81\u1ba2-\u1ba5\u1ba8\u1ba9\u1c2c-\u1c33\u1c36\u1c37\u1cd0-\u1cd2\u1cd4-\u1ce0\u1ce2-\u1ce8\u1ced\u1dc0-\u1de6\u1dfd-\u1dff\u200c\u200d\u20d0-\u20f0\u2cef-\u2cf1\u2de0-\u2dff\u302a-\u302f\u3099\u309a\ua66f-\ua672\ua67c\ua67d\ua6f0\ua6f1\ua802\ua806\ua80b\ua825\ua826\ua8c4\ua8e0-\ua8f1\ua926-\ua92d\ua947-\ua951\ua980-\ua982\ua9b3\ua9b6-\ua9b9\ua9bc\uaa29-\uaa2e\uaa31\uaa32\uaa35\uaa36\uaa43\uaa4c\uaab0\uaab2-\uaab4\uaab7\uaab8\uaabe\uaabf\uaac1\uabe5\uabe8\uabed\udc00-\udfff\ufb1e\ufe00-\ufe0f\ufe20-\ufe26\uff9e\uff9f]/,
		Qo;
	document.createRange ? Qo = function (e, t, n) {
		var r = document.createRange();
		return r.setEnd(e, n), r.setStart(e, t), r
	} : Qo = function (e, t, n) {
		var r = document.body.createTextRange();
		return r.moveToElementText(e.parentNode), r.collapse(!0), r.moveEnd("character", n), r.moveStart("character", t), r
	}, r && i < 11 && (eu = function () {
		try {
			return document.activeElement
		} catch (e) {
			return document.body
		}
	});
	var ou = !1,
		fu = function () {
			if (r && i < 9) return !1;
			var e = Ko("div");
			return "draggable" in e || "dragDrop" in e
		}(),
		lu, hu, du, mu = S.splitLines = "\n\nb".split(/\n/).length != 3 ? function (e) {
			var t = 0,
				n = [],
				r = e.length;
			while (t <= r) {
				var i = e.indexOf("\n", t);
				i == -1 && (i = e.length);
				var s = e.slice(t, e.charAt(i - 1) == "\r" ? i - 1 : i),
					o = s.indexOf("\r");
				o != -1 ? (n.push(s.slice(0, o)), t += o + 1) : (n.push(s), t = i + 1)
			}
			return n
		} : function (e) {
			return e.split(/\r\n?|\n/)
		},
		gu = window.getSelection ? function (e) {
			try {
				return e.selectionStart != e.selectionEnd
			} catch (t) {
				return !1
			}
		} : function (e) {
			try {
				var t = e.ownerDocument.selection.createRange()
			} catch (n) {}
			return !t || t.parentElement() != e ? !1 : t.compareEndPoints("StartToEnd", t) != 0
		},
		yu = function () {
			var e = Ko("div");
			return "oncopy" in e ? !0 : (e.setAttribute("oncopy", "return;"), typeof e.oncopy == "function")
		}(),
		bu = null,
		Eu = {
			3: "Enter",
			8: "Backspace",
			9: "Tab",
			13: "Enter",
			16: "Shift",
			17: "Ctrl",
			18: "Alt",
			19: "Pause",
			20: "CapsLock",
			27: "Esc",
			32: "Space",
			33: "PageUp",
			34: "PageDown",
			35: "End",
			36: "Home",
			37: "Left",
			38: "Up",
			39: "Right",
			40: "Down",
			44: "PrintScrn",
			45: "Insert",
			46: "Delete",
			59: ";",
			61: "=",
			91: "Mod",
			92: "Mod",
			93: "Mod",
			107: "=",
			109: "-",
			127: "Delete",
			173: "-",
			186: ";",
			187: "=",
			188: ",",
			189: "-",
			190: ".",
			191: "/",
			192: "`",
			219: "[",
			220: "\\",
			221: "]",
			222: "'",
			63232: "Up",
			63233: "Down",
			63234: "Left",
			63235: "Right",
			63272: "Delete",
			63273: "Home",
			63275: "End",
			63276: "PageUp",
			63277: "PageDown",
			63302: "Insert"
		};
	S.keyNames = Eu,
		function () {
			for (var e = 0; e < 10; e++) Eu[e + 48] = Eu[e + 96] = String(e);
			for (var e = 65; e <= 90; e++) Eu[e] = String.fromCharCode(e);
			for (var e = 1; e <= 12; e++) Eu[e + 111] = Eu[e + 63235] = "F" + e
		}();
	var Mu, Bu = function () {
		function n(n) {
			return n <= 247 ? e.charAt(n) : 1424 <= n && n <= 1524 ? "R" : 1536 <= n && n <= 1773 ? t.charAt(n - 1536) : 1774 <= n && n <= 2220 ? "r" : 8192 <= n && n <= 8203 ? "w" : n == 8204 ? "b" : "L"
		}

		function f(e, t, n) {
			this.level = e, this.from = t, this.to = n
		}
		var e = "bbbbbbbbbtstwsbbbbbbbbbbbbbbssstwNN%%%NNNNNN,N,N1111111111NNNNNNNLLLLLLLLLLLLLLLLLLLLLLLLLLNNNNNNLLLLLLLLLLLLLLLLLLLLLLLLLLNNNNbbbbbbsbbbbbbbbbbbbbbbbbbbbbbbbbb,N%%%%NNNNLNNNNN%%11NLNNN1LNNNNNLLLLLLLLLLLLLLLLLLLLLLLNLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLN",
			t = "rrrrrrrrrrrr,rNNmmmmmmrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrmmmmmmmmmmmmmmrrrrrrrnnnnnnnnnn%nnrrrmrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrmmmmmmmmmmmmmmmmmmmNmmmm",
			r = /[\u0590-\u05f4\u0600-\u06ff\u0700-\u08ac]/,
			i = /[stwN]/,
			s = /[LRr]/,
			o = /[Lb1n]/,
			u = /[1n]/,
			a = "L";
		return function (e) {
			if (!r.test(e)) return !1;
			var t = e.length,
				l = [];
			for (var c = 0, h; c < t; ++c) l.push(h = n(e.charCodeAt(c)));
			for (var c = 0, p = a; c < t; ++c) {
				var h = l[c];
				h == "m" ? l[c] = p : p = h
			}
			for (var c = 0, d = a; c < t; ++c) {
				var h = l[c];
				h == "1" && d == "r" ? l[c] = "n" : s.test(h) && (d = h, h == "r" && (l[c] = "R"))
			}
			for (var c = 1, p = l[0]; c < t - 1; ++c) {
				var h = l[c];
				h == "+" && p == "1" && l[c + 1] == "1" ? l[c] = "1" : h == "," && p == l[c + 1] && (p == "1" || p == "n") && (l[c] = p), p = h
			}
			for (var c = 0; c < t; ++c) {
				var h = l[c];
				if (h == ",") l[c] = "N";
				else if (h == "%") {
					for (var v = c + 1; v < t && l[v] == "%"; ++v);
					var m = c && l[c - 1] == "!" || v < t && l[v] == "1" ? "1" : "N";
					for (var g = c; g < v; ++g) l[g] = m;
					c = v - 1
				}
			}
			for (var c = 0, d = a; c < t; ++c) {
				var h = l[c];
				d == "L" && h == "1" ? l[c] = "L" : s.test(h) && (d = h)
			}
			for (var c = 0; c < t; ++c)
				if (i.test(l[c])) {
					for (var v = c + 1; v < t && i.test(l[v]); ++v);
					var y = (c ? l[c - 1] : a) == "L",
						b = (v < t ? l[v] : a) == "L",
						m = y || b ? "L" : "R";
					for (var g = c; g < v; ++g) l[g] = m;
					c = v - 1
				}
			var w = [],
				E;
			for (var c = 0; c < t;)
				if (o.test(l[c])) {
					var S = c;
					for (++c; c < t && o.test(l[c]); ++c);
					w.push(new f(0, S, c))
				} else {
					var x = c,
						T = w.length;
					for (++c; c < t && l[c] != "L"; ++c);
					for (var g = x; g < c;)
						if (u.test(l[g])) {
							x < g && w.splice(T, 0, new f(1, x, g));
							var N = g;
							for (++g; g < c && u.test(l[g]); ++g);
							w.splice(T, 0, new f(2, N, g)), x = g
						} else ++g;
					x < c && w.splice(T, 0, new f(1, x, c))
				}
			return w[0].level == 1 && (E = e.match(/^\s+/)) && (w[0].from = E[0].length, w.unshift(new f(0, 0, E[0].length))), Bo(w).level == 1 && (E = e.match(/\s+$/)) && (Bo(w).to -= E[0].length, w.push(new f(0, t - E[0].length, t))), w[0].level != Bo(w).level && w.push(new f(w[0].level, t, t)), w
		}
	}();
	return S.version = "4.7.0", S
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror"], e) : e(CodeMirror)
}(function (e) {
	"use strict";
	e.defineMode("xml", function (t, n) {
		function l(e, t) {
			function n(n) {
				return t.tokenize = n, n(e, t)
			}
			var r = e.next();
			if (r == "<") return e.eat("!") ? e.eat("[") ? e.match("CDATA[") ? n(p("atom", "]]>")) : null : e.match("--") ? n(p("comment", "-->")) : e.match("DOCTYPE", !0, !0) ? (e.eatWhile(/[\w\._\-]/), n(d(1))) : null : e.eat("?") ? (e.eatWhile(/[\w\._\-]/), t.tokenize = p("meta", "?>"), "meta") : (a = e.eat("/") ? "closeTag" : "openTag", t.tokenize = c, "tag bracket");
			if (r == "&") {
				var i;
				return e.eat("#") ? e.eat("x") ? i = e.eatWhile(/[a-fA-F\d]/) && e.eat(";") : i = e.eatWhile(/[\d]/) && e.eat(";") : i = e.eatWhile(/[\w\.\-:]/) && e.eat(";"), i ? "atom" : "error"
			}
			return e.eatWhile(/[^&<]/), null
		}

		function c(e, t) {
			var n = e.next();
			if (n == ">" || n == "/" && e.eat(">")) return t.tokenize = l, a = n == ">" ? "endTag" : "selfcloseTag", "tag bracket";
			if (n == "=") return a = "equals", null;
			if (n == "<") {
				t.tokenize = l, t.state = y, t.tagName = t.tagStart = null;
				var r = t.tokenize(e, t);
				return r ? r + " tag error" : "tag error"
			}
			return /[\'\"]/.test(n) ? (t.tokenize = h(n), t.stringStartCol = e.column(), t.tokenize(e, t)) : (e.match(/^[^\s\u00a0=<>\"\']*[^\s\u00a0=<>\"\'\/]/), "word")
		}

		function h(e) {
			var t = function (t, n) {
				while (!t.eol())
					if (t.next() == e) {
						n.tokenize = c;
						break
					}
				return "string"
			};
			return t.isInAttribute = !0, t
		}

		function p(e, t) {
			return function (n, r) {
				while (!n.eol()) {
					if (n.match(t)) {
						r.tokenize = l;
						break
					}
					n.next()
				}
				return e
			}
		}

		function d(e) {
			return function (t, n) {
				var r;
				while ((r = t.next()) != null) {
					if (r == "<") return n.tokenize = d(e + 1), n.tokenize(t, n);
					if (r == ">") {
						if (e == 1) {
							n.tokenize = l;
							break
						}
						return n.tokenize = d(e - 1), n.tokenize(t, n)
					}
				}
				return "meta"
			}
		}

		function v(e, t, n) {
			this.prev = e.context, this.tagName = t, this.indent = e.indented, this.startOfLine = n;
			if (o.doNotIndent.hasOwnProperty(t) || e.context && e.context.noIndent) this.noIndent = !0
		}

		function m(e) {
			e.context && (e.context = e.context.prev)
		}

		function g(e, t) {
			var n;
			for (;;) {
				if (!e.context) return;
				n = e.context.tagName;
				if (!o.contextGrabbers.hasOwnProperty(n) || !o.contextGrabbers[n].hasOwnProperty(t)) return;
				m(e)
			}
		}

		function y(e, t, n) {
			return e == "openTag" ? (n.tagStart = t.column(), b) : e == "closeTag" ? w : y
		}

		function b(e, t, n) {
			return e == "word" ? (n.tagName = t.current(), f = "tag", x) : (f = "error", b)
		}

		function w(e, t, n) {
			if (e == "word") {
				var r = t.current();
				return n.context && n.context.tagName != r && o.implicitlyClosed.hasOwnProperty(n.context.tagName) && m(n), n.context && n.context.tagName == r ? (f = "tag", E) : (f = "tag error", S)
			}
			return f = "error", S
		}

		function E(e, t, n) {
			return e != "endTag" ? (f = "error", E) : (m(n), y)
		}

		function S(e, t, n) {
			return f = "error", E(e, t, n)
		}

		function x(e, t, n) {
			if (e == "word") return f = "attribute", T;
			if (e == "endTag" || e == "selfcloseTag") {
				var r = n.tagName,
					i = n.tagStart;
				return n.tagName = n.tagStart = null, e == "selfcloseTag" || o.autoSelfClosers.hasOwnProperty(r) ? g(n, r) : (g(n, r), n.context = new v(n, r, i == n.indented)), y
			}
			return f = "error", x
		}

		function T(e, t, n) {
			return e == "equals" ? N : (o.allowMissing || (f = "error"), x(e, t, n))
		}

		function N(e, t, n) {
			return e == "string" ? C : e == "word" && o.allowUnquoted ? (f = "string", x) : (f = "error", x(e, t, n))
		}

		function C(e, t, n) {
			return e == "string" ? C : x(e, t, n)
		}
		var r = t.indentUnit,
			i = n.multilineTagIndentFactor || 1,
			s = n.multilineTagIndentPastTag;
		s == null && (s = !0);
		var o = n.htmlMode ? {
				autoSelfClosers: {
					area: !0,
					base: !0,
					br: !0,
					col: !0,
					command: !0,
					embed: !0,
					frame: !0,
					hr: !0,
					img: !0,
					input: !0,
					keygen: !0,
					link: !0,
					meta: !0,
					param: !0,
					source: !0,
					track: !0,
					wbr: !0,
					menuitem: !0
				},
				implicitlyClosed: {
					dd: !0,
					li: !0,
					optgroup: !0,
					option: !0,
					p: !0,
					rp: !0,
					rt: !0,
					tbody: !0,
					td: !0,
					tfoot: !0,
					th: !0,
					tr: !0
				},
				contextGrabbers: {
					dd: {
						dd: !0,
						dt: !0
					},
					dt: {
						dd: !0,
						dt: !0
					},
					li: {
						li: !0
					},
					option: {
						option: !0,
						optgroup: !0
					},
					optgroup: {
						optgroup: !0
					},
					p: {
						address: !0,
						article: !0,
						aside: !0,
						blockquote: !0,
						dir: !0,
						div: !0,
						dl: !0,
						fieldset: !0,
						footer: !0,
						form: !0,
						h1: !0,
						h2: !0,
						h3: !0,
						h4: !0,
						h5: !0,
						h6: !0,
						header: !0,
						hgroup: !0,
						hr: !0,
						menu: !0,
						nav: !0,
						ol: !0,
						p: !0,
						pre: !0,
						section: !0,
						table: !0,
						ul: !0
					},
					rp: {
						rp: !0,
						rt: !0
					},
					rt: {
						rp: !0,
						rt: !0
					},
					tbody: {
						tbody: !0,
						tfoot: !0
					},
					td: {
						td: !0,
						th: !0
					},
					tfoot: {
						tbody: !0
					},
					th: {
						td: !0,
						th: !0
					},
					thead: {
						tbody: !0,
						tfoot: !0
					},
					tr: {
						tr: !0
					}
				},
				doNotIndent: {
					pre: !0
				},
				allowUnquoted: !0,
				allowMissing: !0,
				caseFold: !0
			} : {
				autoSelfClosers: {},
				implicitlyClosed: {},
				contextGrabbers: {},
				doNotIndent: {},
				allowUnquoted: !1,
				allowMissing: !1,
				caseFold: !1
			},
			u = n.alignCDATA,
			a, f;
		return {
			startState: function () {
				return {
					tokenize: l,
					state: y,
					indented: 0,
					tagName: null,
					tagStart: null,
					context: null
				}
			},
			token: function (e, t) {
				!t.tagName && e.sol() && (t.indented = e.indentation());
				if (e.eatSpace()) return null;
				a = null;
				var n = t.tokenize(e, t);
				return (n || a) && n != "comment" && (f = null, t.state = t.state(a || n, e, t), f && (n = f == "error" ? n + " error" : f)), n
			},
			indent: function (t, n, a) {
				var f = t.context;
				if (t.tokenize.isInAttribute) return t.tagStart == t.indented ? t.stringStartCol + 1 : t.indented + r;
				if (f && f.noIndent) return e.Pass;
				if (t.tokenize != c && t.tokenize != l) return a ? a.match(/^(\s*)/)[0].length : 0;
				if (t.tagName) return s ? t.tagStart + t.tagName.length + 2 : t.tagStart + r * i;
				if (u && /<!\[CDATA\[/.test(n)) return 0;
				var h = n && /^<(\/)?([\w_:\.-]*)/.exec(n);
				if (h && h[1])
					while (f) {
						if (f.tagName == h[2]) {
							f = f.prev;
							break
						}
						if (!o.implicitlyClosed.hasOwnProperty(f.tagName)) break;
						f = f.prev
					} else if (h)
						while (f) {
							var p = o.contextGrabbers[f.tagName];
							if (!p || !p.hasOwnProperty(h[2])) break;
							f = f.prev
						}
					while (f && !f.startOfLine) f = f.prev;
				return f ? f.indent + r : 0
			},
			electricInput: /<\/[\s\w:]+>$/,
			blockCommentStart: "<!--",
			blockCommentEnd: "-->",
			configuration: n.htmlMode ? "html" : "xml",
			helperType: n.htmlMode ? "html" : "xml"
		}
	}), e.defineMIME("text/xml", "xml"), e.defineMIME("application/xml", "xml"), e.mimeModes.hasOwnProperty("text/html") || e.defineMIME("text/html", {
		name: "xml",
		htmlMode: !0
	})
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror"], e) : e(CodeMirror)
}(function (e) {
	"use strict";
	e.defineMode("javascript", function (t, n) {
		function h(e) {
			var t = !1,
				n, r = !1;
			while ((n = e.next()) != null) {
				if (!t) {
					if (n == "/" && !r) return;
					n == "[" ? r = !0 : r && n == "]" && (r = !1)
				}
				t = !t && n == "\\"
			}
		}

		function v(e, t, n) {
			return p = e, d = n, t
		}

		function m(e, t) {
			var n = e.next();
			if (n == '"' || n == "'") return t.tokenize = g(n), t.tokenize(e, t);
			if (n == "." && e.match(/^\d+(?:[eE][+\-]?\d+)?/)) return v("number", "number");
			if (n == "." && e.match("..")) return v("spread", "meta");
			if (/[\[\]{}\(\),;\:\.]/.test(n)) return v(n);
			if (n == "=" && e.eat(">")) return v("=>", "operator");
			if (n == "0" && e.eat(/x/i)) return e.eatWhile(/[\da-f]/i), v("number", "number");
			if (/\d/.test(n)) return e.match(/^\d*(?:\.\d*)?(?:[eE][+\-]?\d+)?/), v("number", "number");
			if (n == "/") return e.eat("*") ? (t.tokenize = y, y(e, t)) : e.eat("/") ? (e.skipToEnd(), v("comment", "comment")) : t.lastType == "operator" || t.lastType == "keyword c" || t.lastType == "sof" || /^[\[{}\(,;:]$/.test(t.lastType) ? (h(e), e.eatWhile(/[gimy]/), v("regexp", "string-2")) : (e.eatWhile(l), v("operator", "operator", e.current()));
			if (n == "`") return t.tokenize = b, b(e, t);
			if (n == "#") return e.skipToEnd(), v("error", "error");
			if (l.test(n)) return e.eatWhile(l), v("operator", "operator", e.current());
			if (a.test(n)) {
				e.eatWhile(a);
				var r = e.current(),
					i = f.propertyIsEnumerable(r) && f[r];
				return i && t.lastType != "." ? v(i.type, i.style, r) : v("variable", "variable", r)
			}
		}

		function g(e) {
			return function (t, n) {
				var r = !1,
					i;
				if (s && t.peek() == "@" && t.match(c)) return n.tokenize = m, v("jsonld-keyword", "meta");
				while ((i = t.next()) != null) {
					if (i == e && !r) break;
					r = !r && i == "\\"
				}
				return r || (n.tokenize = m), v("string", "string")
			}
		}

		function y(e, t) {
			var n = !1,
				r;
			while (r = e.next()) {
				if (r == "/" && n) {
					t.tokenize = m;
					break
				}
				n = r == "*"
			}
			return v("comment", "comment")
		}

		function b(e, t) {
			var n = !1,
				r;
			while ((r = e.next()) != null) {
				if (!n && (r == "`" || r == "$" && e.eat("{"))) {
					t.tokenize = m;
					break
				}
				n = !n && r == "\\"
			}
			return v("quasi", "string-2", e.current())
		}

		function E(e, t) {
			t.fatArrowAt && (t.fatArrowAt = null);
			var n = e.string.indexOf("=>", e.start);
			if (n < 0) return;
			var r = 0,
				i = !1;
			for (var s = n - 1; s >= 0; --s) {
				var o = e.string.charAt(s),
					u = w.indexOf(o);
				if (u >= 0 && u < 3) {
					if (!r) {
						++s;
						break
					}
					if (--r == 0) break
				} else if (u >= 3 && u < 6) ++r;
				else if (a.test(o)) i = !0;
				else if (i && !r) {
					++s;
					break
				}
			}
			i && !r && (t.fatArrowAt = s)
		}

		function x(e, t, n, r, i, s) {
			this.indented = e, this.column = t, this.type = n, this.prev = i, this.info = s, r != null && (this.align = r)
		}

		function T(e, t) {
			for (var n = e.localVars; n; n = n.next)
				if (n.name == t) return !0;
			for (var r = e.context; r; r = r.prev)
				for (var n = r.vars; n; n = n.next)
					if (n.name == t) return !0
		}

		function N(e, t, n, r, i) {
			var s = e.cc;
			C.state = e, C.stream = i, C.marked = null, C.cc = s, C.style = t, e.lexical.hasOwnProperty("align") || (e.lexical.align = !0);
			for (;;) {
				var u = s.length ? s.pop() : o ? j : B;
				if (u(n, r)) {
					while (s.length && s[s.length - 1].lex) s.pop()();
					return C.marked ? C.marked : n == "variable" && T(e, r) ? "variable-2" : t
				}
			}
		}

		function k() {
			for (var e = arguments.length - 1; e >= 0; e--) C.cc.push(arguments[e])
		}

		function L() {
			return k.apply(null, arguments), !0
		}

		function A(e) {
			function t(t) {
				for (var n = t; n; n = n.next)
					if (n.name == e) return !0;
				return !1
			}
			var r = C.state;
			if (r.context) {
				C.marked = "def";
				if (t(r.localVars)) return;
				r.localVars = {
					name: e,
					next: r.localVars
				}
			} else {
				if (t(r.globalVars)) return;
				n.globalVars && (r.globalVars = {
					name: e,
					next: r.globalVars
				})
			}
		}

		function M() {
			C.state.context = {
				prev: C.state.context,
				vars: C.state.localVars
			}, C.state.localVars = O
		}

		function _() {
			C.state.localVars = C.state.context.vars, C.state.context = C.state.context.prev
		}

		function D(e, t) {
			var n = function () {
				var n = C.state,
					r = n.indented;
				if (n.lexical.type == "stat") r = n.lexical.indented;
				else
					for (var i = n.lexical; i && i.type == ")" && i.align; i = i.prev) r = i.indented;
				n.lexical = new x(r, C.stream.column(), e, null, n.lexical, t)
			};
			return n.lex = !0, n
		}

		function P() {
			var e = C.state;
			e.lexical.prev && (e.lexical.type == ")" && (e.indented = e.lexical.indented), e.lexical = e.lexical.prev)
		}

		function H(e) {
			function t(n) {
				return n == e ? L() : e == ";" ? k() : L(t)
			}
			return t
		}

		function B(e, t) {
			return e == "var" ? L(D("vardef", t.length), it, H(";"), P) : e == "keyword a" ? L(D("form"), j, B, P) : e == "keyword b" ? L(D("form"), B, P) : e == "{" ? L(D("}"), tt, P) : e == ";" ? L() : e == "if" ? (C.state.lexical.info == "else" && C.state.cc[C.state.cc.length - 1] == P && C.state.cc.pop()(), L(D("form"), j, B, P, ft)) : e == "function" ? L(vt) : e == "for" ? L(D("form"), lt, B, P) : e == "variable" ? L(D("stat"), J) : e == "switch" ? L(D("form"), j, D("}", "switch"), H("{"), tt, P, P) : e == "case" ? L(j, H(":")) : e == "default" ? L(H(":")) : e == "catch" ? L(D("form"), M, H("("), mt, H(")"), B, P, _) : e == "module" ? L(D("form"), M, Et, _, P) : e == "class" ? L(D("form"), gt, P) : e == "export" ? L(D("form"), St, P) : e == "import" ? L(D("form"), xt, P) : k(D("stat"), j, H(";"), P)
		}

		function j(e) {
			return I(e, !1)
		}

		function F(e) {
			return I(e, !0)
		}

		function I(e, t) {
			if (C.state.fatArrowAt == C.stream.start) {
				var n = t ? $ : V;
				if (e == "(") return L(M, D(")"), Z(st, ")"), P, H("=>"), n, _);
				if (e == "variable") return k(M, st, H("=>"), n, _)
			}
			var r = t ? z : U;
			return S.hasOwnProperty(e) ? L(r) : e == "function" ? L(vt, r) : e == "keyword c" ? L(t ? R : q) : e == "(" ? L(D(")"), q, Lt, H(")"), P, r) : e == "operator" || e == "spread" ? L(t ? F : j) : e == "[" ? L(D("]"), Ct, P, r) : e == "{" ? et(Q, "}", null, r) : e == "quasi" ? k(W, r) : L()
		}

		function q(e) {
			return e.match(/[;\}\)\],]/) ? k() : k(j)
		}

		function R(e) {
			return e.match(/[;\}\)\],]/) ? k() : k(F)
		}

		function U(e, t) {
			return e == "," ? L(j) : z(e, t, !1)
		}

		function z(e, t, n) {
			var r = n == 0 ? U : z,
				i = n == 0 ? j : F;
			if (e == "=>") return L(M, n ? $ : V, _);
			if (e == "operator") return /\+\+|--/.test(t) ? L(r) : t == "?" ? L(j, H(":"), i) : L(i);
			if (e == "quasi") return k(W, r);
			if (e == ";") return;
			if (e == "(") return et(F, ")", "call", r);
			if (e == ".") return L(K, r);
			if (e == "[") return L(D("]"), q, H("]"), P, r)
		}

		function W(e, t) {
			return e != "quasi" ? k() : t.slice(t.length - 2) != "${" ? L(W) : L(j, X)
		}

		function X(e) {
			if (e == "}") return C.marked = "string-2", C.state.tokenize = b, L(W)
		}

		function V(e) {
			return E(C.stream, C.state), k(e == "{" ? B : j)
		}

		function $(e) {
			return E(C.stream, C.state), k(e == "{" ? B : F)
		}

		function J(e) {
			return e == ":" ? L(P, B) : k(U, H(";"), P)
		}

		function K(e) {
			if (e == "variable") return C.marked = "property", L()
		}

		function Q(e, t) {
			if (e == "variable" || C.style == "keyword") return C.marked = "property", t == "get" || t == "set" ? L(G) : L(Y);
			if (e == "number" || e == "string") return C.marked = s ? "property" : C.style + " property", L(Y);
			if (e == "jsonld-keyword") return L(Y);
			if (e == "[") return L(j, H("]"), Y)
		}

		function G(e) {
			return e != "variable" ? k(Y) : (C.marked = "property", L(vt))
		}

		function Y(e) {
			if (e == ":") return L(F);
			if (e == "(") return k(vt)
		}

		function Z(e, t) {
			function n(r) {
				if (r == ",") {
					var i = C.state.lexical;
					return i.info == "call" && (i.pos = (i.pos || 0) + 1), L(e, n)
				}
				return r == t ? L() : L(H(t))
			}
			return function (r) {
				return r == t ? L() : k(e, n)
			}
		}

		function et(e, t, n) {
			for (var r = 3; r < arguments.length; r++) C.cc.push(arguments[r]);
			return L(D(t, n), Z(e, t), P)
		}

		function tt(e) {
			return e == "}" ? L() : k(B, tt)
		}

		function nt(e) {
			if (u && e == ":") return L(rt)
		}

		function rt(e) {
			if (e == "variable") return C.marked = "variable-3", L()
		}

		function it() {
			return k(st, nt, ut, at)
		}

		function st(e, t) {
			if (e == "variable") return A(t), L();
			if (e == "[") return et(st, "]");
			if (e == "{") return et(ot, "}")
		}

		function ot(e, t) {
			return e == "variable" && !C.stream.match(/^\s*:/, !1) ? (A(t), L(ut)) : (e == "variable" && (C.marked = "property"), L(H(":"), st, ut))
		}

		function ut(e, t) {
			if (t == "=") return L(F)
		}

		function at(e) {
			if (e == ",") return L(it)
		}

		function ft(e, t) {
			if (e == "keyword b" && t == "else") return L(D("form", "else"), B, P)
		}

		function lt(e) {
			if (e == "(") return L(D(")"), ct, H(")"), P)
		}

		function ct(e) {
			return e == "var" ? L(it, H(";"), pt) : e == ";" ? L(pt) : e == "variable" ? L(ht) : k(j, H(";"), pt)
		}

		function ht(e, t) {
			return t == "in" || t == "of" ? (C.marked = "keyword", L(j)) : L(U, pt)
		}

		function pt(e, t) {
			return e == ";" ? L(dt) : t == "in" || t == "of" ? (C.marked = "keyword", L(j)) : k(j, H(";"), dt)
		}

		function dt(e) {
			e != ")" && L(j)
		}

		function vt(e, t) {
			if (t == "*") return C.marked = "keyword", L(vt);
			if (e == "variable") return A(t), L(vt);
			if (e == "(") return L(M, D(")"), Z(mt, ")"), P, B, _)
		}

		function mt(e) {
			return e == "spread" ? L(mt) : k(st, nt)
		}

		function gt(e, t) {
			if (e == "variable") return A(t), L(yt)
		}

		function yt(e, t) {
			if (t == "extends") return L(j, yt);
			if (e == "{") return L(D("}"), bt, P)
		}

		function bt(e, t) {
			if (e == "variable" || C.style == "keyword") return C.marked = "property", t == "get" || t == "set" ? L(wt, vt, bt) : L(vt, bt);
			if (t == "*") return C.marked = "keyword", L(bt);
			if (e == ";") return L(bt);
			if (e == "}") return L()
		}

		function wt(e) {
			return e != "variable" ? k() : (C.marked = "property", L())
		}

		function Et(e, t) {
			if (e == "string") return L(B);
			if (e == "variable") return A(t), L(Nt)
		}

		function St(e, t) {
			return t == "*" ? (C.marked = "keyword", L(Nt, H(";"))) : t == "default" ? (C.marked = "keyword", L(j, H(";"))) : k(B)
		}

		function xt(e) {
			return e == "string" ? L() : k(Tt, Nt)
		}

		function Tt(e, t) {
			return e == "{" ? et(Tt, "}") : (e == "variable" && A(t), L())
		}

		function Nt(e, t) {
			if (t == "from") return C.marked = "keyword", L(j)
		}

		function Ct(e) {
			return e == "]" ? L() : k(F, kt)
		}

		function kt(e) {
			return e == "for" ? k(Lt, H("]")) : e == "," ? L(Z(R, "]")) : k(Z(F, "]"))
		}

		function Lt(e) {
			if (e == "for") return L(lt, Lt);
			if (e == "if") return L(j, Lt)
		}
		var r = t.indentUnit,
			i = n.statementIndent,
			s = n.jsonld,
			o = n.json || s,
			u = n.typescript,
			a = n.wordCharacters || /[\w$\xa1-\uffff]/,
			f = function () {
				function e(e) {
					return {
						type: e,
						style: "keyword"
					}
				}
				var t = e("keyword a"),
					n = e("keyword b"),
					r = e("keyword c"),
					i = e("operator"),
					s = {
						type: "atom",
						style: "atom"
					},
					o = {
						"if": e("if"),
						"while": t,
						"with": t,
						"else": n,
						"do": n,
						"try": n,
						"finally": n,
						"return": r,
						"break": r,
						"continue": r,
						"new": r,
						"delete": r,
						"throw": r,
						"debugger": r,
						"var": e("var"),
						"const": e("var"),
						let: e("var"),
						"function": e("function"),
						"catch": e("catch"),
						"for": e("for"),
						"switch": e("switch"),
						"case": e("case"),
						"default": e("default"),
						"in": i,
						"typeof": i,
						"instanceof": i,
						"true": s,
						"false": s,
						"null": s,
						"undefined": s,
						NaN: s,
						Infinity: s,
						"this": e("this"),
						module: e("module"),
						"class": e("class"),
						"super": e("atom"),
						yield: r,
						"export": e("export"),
						"import": e("import"),
						"extends": r
					};
				if (u) {
					var a = {
							type: "variable",
							style: "variable-3"
						},
						f = {
							"interface": e("interface"),
							"extends": e("extends"),
							constructor: e("constructor"),
							"public": e("public"),
							"private": e("private"),
							"protected": e("protected"),
							"static": e("static"),
							string: a,
							number: a,
							bool: a,
							any: a
						};
					for (var l in f) o[l] = f[l]
				}
				return o
			}(),
			l = /[+\-*&%=<>!?|~^]/,
			c = /^@(context|id|value|language|type|container|list|set|reverse|index|base|vocab|graph)"/,
			p, d, w = "([{}])",
			S = {
				atom: !0,
				number: !0,
				variable: !0,
				string: !0,
				regexp: !0,
				"this": !0,
				"jsonld-keyword": !0
			},
			C = {
				state: null,
				column: null,
				marked: null,
				cc: null
			},
			O = {
				name: "this",
				next: {
					name: "arguments"
				}
			};
		return P.lex = !0, {
			startState: function (e) {
				var t = {
					tokenize: m,
					lastType: "sof",
					cc: [],
					lexical: new x((e || 0) - r, 0, "block", !1),
					localVars: n.localVars,
					context: n.localVars && {
						vars: n.localVars
					},
					indented: 0
				};
				return n.globalVars && typeof n.globalVars == "object" && (t.globalVars = n.globalVars), t
			},
			token: function (e, t) {
				e.sol() && (t.lexical.hasOwnProperty("align") || (t.lexical.align = !1), t.indented = e.indentation(), E(e, t));
				if (t.tokenize != y && e.eatSpace()) return null;
				var n = t.tokenize(e, t);
				return p == "comment" ? n : (t.lastType = p != "operator" || d != "++" && d != "--" ? p : "incdec", N(t, n, p, d, e))
			},
			indent: function (t, s) {
				if (t.tokenize == y) return e.Pass;
				if (t.tokenize != m) return 0;
				var o = s && s.charAt(0),
					u = t.lexical;
				if (!/^\s*else\b/.test(s))
					for (var a = t.cc.length - 1; a >= 0; --a) {
						var f = t.cc[a];
						if (f == P) u = u.prev;
						else if (f != ft) break
					}
				u.type == "stat" && o == "}" && (u = u.prev), i && u.type == ")" && u.prev.type == "stat" && (u = u.prev);
				var l = u.type,
					c = o == l;
				return l == "vardef" ? u.indented + (t.lastType == "operator" || t.lastType == "," ? u.info + 1 : 0) : l == "form" && o == "{" ? u.indented : l == "form" ? u.indented + r : l == "stat" ? u.indented + (t.lastType == "operator" || t.lastType == "," ? i || r : 0) : u.info == "switch" && !c && n.doubleIndentSwitch != 0 ? u.indented + (/^(?:case|default)\b/.test(s) ? r : 2 * r) : u.align ? u.column + (c ? 0 : 1) : u.indented + (c ? 0 : r)
			},
			electricInput: /^\s*(?:case .*?:|default:|\{|\})$/,
			blockCommentStart: o ? null : "/*",
			blockCommentEnd: o ? null : "*/",
			lineComment: o ? null : "//",
			fold: "brace",
			helperType: o ? "json" : "javascript",
			jsonldMode: s,
			jsonMode: o
		}
	}), e.registerHelper("wordChars", "javascript", /[\w$]/), e.defineMIME("text/javascript", "javascript"), e.defineMIME("text/ecmascript", "javascript"), e.defineMIME("application/javascript", "javascript"), e.defineMIME("application/x-javascript", "javascript"), e.defineMIME("application/ecmascript", "javascript"), e.defineMIME("application/json", {
		name: "javascript",
		json: !0
	}), e.defineMIME("application/x-json", {
		name: "javascript",
		json: !0
	}), e.defineMIME("application/ld+json", {
		name: "javascript",
		jsonld: !0
	}), e.defineMIME("text/typescript", {
		name: "javascript",
		typescript: !0
	}), e.defineMIME("application/typescript", {
		name: "javascript",
		typescript: !0
	})
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror"], e) : e(CodeMirror)
}(function (e) {
	"use strict";

	function t(e) {
		var t = {};
		for (var n = 0; n < e.length; ++n) t[e[n]] = !0;
		return t
	}

	function g(e, t) {
		var n = !1,
			r;
		while ((r = e.next()) != null) {
			if (n && r == "/") {
				t.tokenize = null;
				break
			}
			n = r == "*"
		}
		return ["comment", "comment"]
	}

	function y(e, t) {
		return e.skipTo("-->") ? (e.match("-->"), t.tokenize = null) : e.skipToEnd(), ["comment", "comment"]
	}
	e.defineMode("css", function (t, n) {
		function v(e, t) {
			return p = t, e
		}

		function m(e, t) {
			var n = e.next();
			if (i[n]) {
				var r = i[n](e, t);
				if (r !== !1) return r
			}
			if (n == "@") return e.eatWhile(/[\w\\\-]/), v("def", e.current());
			if (n == "=" || (n == "~" || n == "|") && e.eat("=")) return v(null, "compare");
			if (n == '"' || n == "'") return t.tokenize = g(n), t.tokenize(e, t);
			if (n == "#") return e.eatWhile(/[\w\\\-]/), v("atom", "hash");
			if (n == "!") return e.match(/^\s*\w*/), v("keyword", "important");
			if (/\d/.test(n) || n == "." && e.eat(/\d/)) return e.eatWhile(/[\w.%]/), v("number", "unit");
			if (n !== "-") return /[,+>*\/]/.test(n) ? v(null, "select-op") : n == "." && e.match(/^-?[_a-z][_a-z0-9-]*/i) ? v("qualifier", "qualifier") : /[:;{}\[\]\(\)]/.test(n) ? v(null, n) : n == "u" && e.match("rl(") ? (e.backUp(1), t.tokenize = y, v("property", "word")) : /[\w\\\-]/.test(n) ? (e.eatWhile(/[\w\\\-]/), v("property", "word")) : v(null, null);
			if (/[\d.]/.test(e.peek())) return e.eatWhile(/[\w.%]/), v("number", "unit");
			if (e.match(/^\w+-/)) return v("meta", "meta")
		}

		function g(e) {
			return function (t, n) {
				var r = !1,
					i;
				while ((i = t.next()) != null) {
					if (i == e && !r) {
						e == ")" && t.backUp(1);
						break
					}
					r = !r && i == "\\"
				}
				if (i == e || !r && e != ")") n.tokenize = null;
				return v("string", "string")
			}
		}

		function y(e, t) {
			return e.next(), e.match(/\s*[\"\')]/, !1) ? t.tokenize = null : t.tokenize = g(")"), v(null, "(")
		}

		function b(e, t, n) {
			this.type = e, this.indent = t, this.prev = n
		}

		function w(e, t, n) {
			return e.context = new b(n, t.indentation() + r, e.context), n
		}

		function E(e) {
			return e.context = e.context.prev, e.context.type
		}

		function S(e, t, n) {
			return N[n.context.type](e, t, n)
		}

		function x(e, t, n, r) {
			for (var i = r || 1; i > 0; i--) n.context = n.context.prev;
			return S(e, t, n)
		}

		function T(e) {
			var t = e.current().toLowerCase();
			l.hasOwnProperty(t) ? d = "atom" : f.hasOwnProperty(t) ? d = "keyword" : d = "variable"
		}
		n.propertyKeywords || (n = e.resolveMode("text/css"));
		var r = t.indentUnit,
			i = n.tokenHooks,
			s = n.mediaTypes || {},
			o = n.mediaFeatures || {},
			u = n.propertyKeywords || {},
			a = n.nonStandardPropertyKeywords || {},
			f = n.colorKeywords || {},
			l = n.valueKeywords || {},
			c = n.fontProperties || {},
			h = n.allowNested,
			p, d, N = {};
		return N.top = function (e, t, n) {
			if (e == "{") return w(n, t, "block");
			if (e == "}" && n.context.prev) return E(n);
			if (e == "@media") return w(n, t, "media");
			if (e == "@font-face") return "font_face_before";
			if (/^@(-(moz|ms|o|webkit)-)?keyframes$/.test(e)) return "keyframes";
			if (e && e.charAt(0) == "@") return w(n, t, "at");
			if (e == "hash") d = "builtin";
			else if (e == "word") d = "tag";
			else {
				if (e == "variable-definition") return "maybeprop";
				if (e == "interpolation") return w(n, t, "interpolation");
				if (e == ":") return "pseudo";
				if (h && e == "(") return w(n, t, "parens")
			}
			return n.context.type
		}, N.block = function (e, t, n) {
			if (e == "word") {
				var r = t.current().toLowerCase();
				return u.hasOwnProperty(r) ? (d = "property", "maybeprop") : a.hasOwnProperty(r) ? (d = "string-2", "maybeprop") : h ? (d = t.match(/^\s*:/, !1) ? "property" : "tag", "block") : (d += " error", "maybeprop")
			}
			return e == "meta" ? "block" : !!h || e != "hash" && e != "qualifier" ? N.top(e, t, n) : (d = "error", "block")
		}, N.maybeprop = function (e, t, n) {
			return e == ":" ? w(n, t, "prop") : S(e, t, n)
		}, N.prop = function (e, t, n) {
			if (e == ";") return E(n);
			if (e == "{" && h) return w(n, t, "propBlock");
			if (e == "}" || e == "{") return x(e, t, n);
			if (e == "(") return w(n, t, "parens");
			if (e == "hash" && !/^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/.test(t.current())) d += " error";
			else if (e == "word") T(t);
			else if (e == "interpolation") return w(n, t, "interpolation");
			return "prop"
		}, N.propBlock = function (e, t, n) {
			return e == "}" ? E(n) : e == "word" ? (d = "property", "maybeprop") : n.context.type
		}, N.parens = function (e, t, n) {
			return e == "{" || e == "}" ? x(e, t, n) : e == ")" ? E(n) : e == "(" ? w(n, t, "parens") : (e == "word" && T(t), "parens")
		}, N.pseudo = function (e, t, n) {
			return e == "word" ? (d = "variable-3", n.context.type) : S(e, t, n)
		}, N.media = function (e, t, n) {
			if (e == "(") return w(n, t, "media_parens");
			if (e == "}") return x(e, t, n);
			if (e == "{") return E(n) && w(n, t, h ? "block" : "top");
			if (e == "word") {
				var r = t.current().toLowerCase();
				r == "only" || r == "not" || r == "and" ? d = "keyword" : s.hasOwnProperty(r) ? d = "attribute" : o.hasOwnProperty(r) ? d = "property" : d = "error"
			}
			return n.context.type
		}, N.media_parens = function (e, t, n) {
			return e == ")" ? E(n) : e == "{" || e == "}" ? x(e, t, n, 2) : N.media(e, t, n)
		}, N.font_face_before = function (e, t, n) {
			return e == "{" ? w(n, t, "font_face") : S(e, t, n)
		}, N.font_face = function (e, t, n) {
			return e == "}" ? E(n) : e == "word" ? (c.hasOwnProperty(t.current().toLowerCase()) ? d = "property" : d = "error", "maybeprop") : "font_face"
		}, N.keyframes = function (e, t, n) {
			return e == "word" ? (d = "variable", "keyframes") : e == "{" ? w(n, t, "top") : S(e, t, n)
		}, N.at = function (e, t, n) {
			return e == ";" ? E(n) : e == "{" || e == "}" ? x(e, t, n) : (e == "word" ? d = "tag" : e == "hash" && (d = "builtin"), "at")
		}, N.interpolation = function (e, t, n) {
			return e == "}" ? E(n) : e == "{" || e == ";" ? x(e, t, n) : (e != "variable" && (d = "error"), "interpolation")
		}, {
			startState: function (e) {
				return {
					tokenize: null,
					state: "top",
					context: new b("top", e || 0, null)
				}
			},
			token: function (e, t) {
				if (!t.tokenize && e.eatSpace()) return null;
				var n = (t.tokenize || m)(e, t);
				return n && typeof n == "object" && (p = n[1], n = n[0]), d = n, t.state = N[t.state](p, e, t), d
			},
			indent: function (e, t) {
				var n = e.context,
					i = t && t.charAt(0),
					s = n.indent;
				return n.type == "prop" && (i == "}" || i == ")") && (n = n.prev), n.prev && (i == "}" && (n.type == "block" || n.type == "top" || n.type == "interpolation" || n.type == "font_face") || i == ")" && (n.type == "parens" || n.type == "media_parens") || i == "{" && (n.type == "at" || n.type == "media")) && (s = n.indent - r, n = n.prev), s
			},
			electricChars: "}",
			blockCommentStart: "/*",
			blockCommentEnd: "*/",
			fold: "brace"
		}
	});
	var n = ["all", "aural", "braille", "handheld", "print", "projection", "screen", "tty", "tv", "embossed"],
		r = t(n),
		i = ["width", "min-width", "max-width", "height", "min-height", "max-height", "device-width", "min-device-width", "max-device-width", "device-height", "min-device-height", "max-device-height", "aspect-ratio", "min-aspect-ratio", "max-aspect-ratio", "device-aspect-ratio", "min-device-aspect-ratio", "max-device-aspect-ratio", "color", "min-color", "max-color", "color-index", "min-color-index", "max-color-index", "monochrome", "min-monochrome", "max-monochrome", "resolution", "min-resolution", "max-resolution", "scan", "grid"],
		s = t(i),
		o = ["align-content", "align-items", "align-self", "alignment-adjust", "alignment-baseline", "anchor-point", "animation", "animation-delay", "animation-direction", "animation-duration", "animation-fill-mode", "animation-iteration-count", "animation-name", "animation-play-state", "animation-timing-function", "appearance", "azimuth", "backface-visibility", "background", "background-attachment", "background-clip", "background-color", "background-image", "background-origin", "background-position", "background-repeat", "background-size", "baseline-shift", "binding", "bleed", "bookmark-label", "bookmark-level", "bookmark-state", "bookmark-target", "border", "border-bottom", "border-bottom-color", "border-bottom-left-radius", "border-bottom-right-radius", "border-bottom-style", "border-bottom-width", "border-collapse", "border-color", "border-image", "border-image-outset", "border-image-repeat", "border-image-slice", "border-image-source", "border-image-width", "border-left", "border-left-color", "border-left-style", "border-left-width", "border-radius", "border-right", "border-right-color", "border-right-style", "border-right-width", "border-spacing", "border-style", "border-top", "border-top-color", "border-top-left-radius", "border-top-right-radius", "border-top-style", "border-top-width", "border-width", "bottom", "box-decoration-break", "box-shadow", "box-sizing", "break-after", "break-before", "break-inside", "caption-side", "clear", "clip", "color", "color-profile", "column-count", "column-fill", "column-gap", "column-rule", "column-rule-color", "column-rule-style", "column-rule-width", "column-span", "column-width", "columns", "content", "counter-increment", "counter-reset", "crop", "cue", "cue-after", "cue-before", "cursor", "direction", "display", "dominant-baseline", "drop-initial-after-adjust", "drop-initial-after-align", "drop-initial-before-adjust", "drop-initial-before-align", "drop-initial-size", "drop-initial-value", "elevation", "empty-cells", "fit", "fit-position", "flex", "flex-basis", "flex-direction", "flex-flow", "flex-grow", "flex-shrink", "flex-wrap", "float", "float-offset", "flow-from", "flow-into", "font", "font-feature-settings", "font-family", "font-kerning", "font-language-override", "font-size", "font-size-adjust", "font-stretch", "font-style", "font-synthesis", "font-variant", "font-variant-alternates", "font-variant-caps", "font-variant-east-asian", "font-variant-ligatures", "font-variant-numeric", "font-variant-position", "font-weight", "grid", "grid-area", "grid-auto-columns", "grid-auto-flow", "grid-auto-position", "grid-auto-rows", "grid-column", "grid-column-end", "grid-column-start", "grid-row", "grid-row-end", "grid-row-start", "grid-template", "grid-template-areas", "grid-template-columns", "grid-template-rows", "hanging-punctuation", "height", "hyphens", "icon", "image-orientation", "image-rendering", "image-resolution", "inline-box-align", "justify-content", "left", "letter-spacing", "line-break", "line-height", "line-stacking", "line-stacking-ruby", "line-stacking-shift", "line-stacking-strategy", "list-style", "list-style-image", "list-style-position", "list-style-type", "margin", "margin-bottom", "margin-left", "margin-right", "margin-top", "marker-offset", "marks", "marquee-direction", "marquee-loop", "marquee-play-count", "marquee-speed", "marquee-style", "max-height", "max-width", "min-height", "min-width", "move-to", "nav-down", "nav-index", "nav-left", "nav-right", "nav-up", "object-fit", "object-position", "opacity", "order", "orphans", "outline", "outline-color", "outline-offset", "outline-style", "outline-width", "overflow", "overflow-style", "overflow-wrap", "overflow-x", "overflow-y", "padding", "padding-bottom", "padding-left", "padding-right", "padding-top", "page", "page-break-after", "page-break-before", "page-break-inside", "page-policy", "pause", "pause-after", "pause-before", "perspective", "perspective-origin", "pitch", "pitch-range", "play-during", "position", "presentation-level", "punctuation-trim", "quotes", "region-break-after", "region-break-before", "region-break-inside", "region-fragment", "rendering-intent", "resize", "rest", "rest-after", "rest-before", "richness", "right", "rotation", "rotation-point", "ruby-align", "ruby-overhang", "ruby-position", "ruby-span", "shape-image-threshold", "shape-inside", "shape-margin", "shape-outside", "size", "speak", "speak-as", "speak-header", "speak-numeral", "speak-punctuation", "speech-rate", "stress", "string-set", "tab-size", "table-layout", "target", "target-name", "target-new", "target-position", "text-align", "text-align-last", "text-decoration", "text-decoration-color", "text-decoration-line", "text-decoration-skip", "text-decoration-style", "text-emphasis", "text-emphasis-color", "text-emphasis-position", "text-emphasis-style", "text-height", "text-indent", "text-justify", "text-outline", "text-overflow", "text-shadow", "text-size-adjust", "text-space-collapse", "text-transform", "text-underline-position", "text-wrap", "top", "transform", "transform-origin", "transform-style", "transition", "transition-delay", "transition-duration", "transition-property", "transition-timing-function", "unicode-bidi", "vertical-align", "visibility", "voice-balance", "voice-duration", "voice-family", "voice-pitch", "voice-range", "voice-rate", "voice-stress", "voice-volume", "volume", "white-space", "widows", "width", "word-break", "word-spacing", "word-wrap", "z-index", "clip-path", "clip-rule", "mask", "enable-background", "filter", "flood-color", "flood-opacity", "lighting-color", "stop-color", "stop-opacity", "pointer-events", "color-interpolation", "color-interpolation-filters", "color-rendering", "fill", "fill-opacity", "fill-rule", "image-rendering", "marker", "marker-end", "marker-mid", "marker-start", "shape-rendering", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "text-rendering", "baseline-shift", "dominant-baseline", "glyph-orientation-horizontal", "glyph-orientation-vertical", "text-anchor", "writing-mode"],
		u = t(o),
		a = ["scrollbar-arrow-color", "scrollbar-base-color", "scrollbar-dark-shadow-color", "scrollbar-face-color", "scrollbar-highlight-color", "scrollbar-shadow-color", "scrollbar-3d-light-color", "scrollbar-track-color", "shape-inside", "searchfield-cancel-button", "searchfield-decoration", "searchfield-results-button", "searchfield-results-decoration", "zoom"],
		f = t(a),
		l = ["aliceblue", "antiquewhite", "aqua", "aquamarine", "azure", "beige", "bisque", "black", "blanchedalmond", "blue", "blueviolet", "brown", "burlywood", "cadetblue", "chartreuse", "chocolate", "coral", "cornflowerblue", "cornsilk", "crimson", "cyan", "darkblue", "darkcyan", "darkgoldenrod", "darkgray", "darkgreen", "darkkhaki", "darkmagenta", "darkolivegreen", "darkorange", "darkorchid", "darkred", "darksalmon", "darkseagreen", "darkslateblue", "darkslategray", "darkturquoise", "darkviolet", "deeppink", "deepskyblue", "dimgray", "dodgerblue", "firebrick", "floralwhite", "forestgreen", "fuchsia", "gainsboro", "ghostwhite", "gold", "goldenrod", "gray", "grey", "green", "greenyellow", "honeydew", "hotpink", "indianred", "indigo", "ivory", "khaki", "lavender", "lavenderblush", "lawngreen", "lemonchiffon", "lightblue", "lightcoral", "lightcyan", "lightgoldenrodyellow", "lightgray", "lightgreen", "lightpink", "lightsalmon", "lightseagreen", "lightskyblue", "lightslategray", "lightsteelblue", "lightyellow", "lime", "limegreen", "linen", "magenta", "maroon", "mediumaquamarine", "mediumblue", "mediumorchid", "mediumpurple", "mediumseagreen", "mediumslateblue", "mediumspringgreen", "mediumturquoise", "mediumvioletred", "midnightblue", "mintcream", "mistyrose", "moccasin", "navajowhite", "navy", "oldlace", "olive", "olivedrab", "orange", "orangered", "orchid", "palegoldenrod", "palegreen", "paleturquoise", "palevioletred", "papayawhip", "peachpuff", "peru", "pink", "plum", "powderblue", "purple", "rebeccapurple", "red", "rosybrown", "royalblue", "saddlebrown", "salmon", "sandybrown", "seagreen", "seashell", "sienna", "silver", "skyblue", "slateblue", "slategray", "snow", "springgreen", "steelblue", "tan", "teal", "thistle", "tomato", "turquoise", "violet", "wheat", "white", "whitesmoke", "yellow", "yellowgreen"],
		c = t(l),
		h = ["above", "absolute", "activeborder", "activecaption", "afar", "after-white-space", "ahead", "alias", "all", "all-scroll", "alternate", "always", "amharic", "amharic-abegede", "antialiased", "appworkspace", "arabic-indic", "armenian", "asterisks", "auto", "avoid", "avoid-column", "avoid-page", "avoid-region", "background", "backwards", "baseline", "below", "bidi-override", "binary", "bengali", "blink", "block", "block-axis", "bold", "bolder", "border", "border-box", "both", "bottom", "break", "break-all", "break-word", "button", "button-bevel", "buttonface", "buttonhighlight", "buttonshadow", "buttontext", "cambodian", "capitalize", "caps-lock-indicator", "caption", "captiontext", "caret", "cell", "center", "checkbox", "circle", "cjk-earthly-branch", "cjk-heavenly-stem", "cjk-ideographic", "clear", "clip", "close-quote", "col-resize", "collapse", "column", "compact", "condensed", "contain", "content", "content-box", "context-menu", "continuous", "copy", "cover", "crop", "cross", "crosshair", "currentcolor", "cursive", "dashed", "decimal", "decimal-leading-zero", "default", "default-button", "destination-atop", "destination-in", "destination-out", "destination-over", "devanagari", "disc", "discard", "document", "dot-dash", "dot-dot-dash", "dotted", "double", "down", "e-resize", "ease", "ease-in", "ease-in-out", "ease-out", "element", "ellipse", "ellipsis", "embed", "end", "ethiopic", "ethiopic-abegede", "ethiopic-abegede-am-et", "ethiopic-abegede-gez", "ethiopic-abegede-ti-er", "ethiopic-abegede-ti-et", "ethiopic-halehame-aa-er", "ethiopic-halehame-aa-et", "ethiopic-halehame-am-et", "ethiopic-halehame-gez", "ethiopic-halehame-om-et", "ethiopic-halehame-sid-et", "ethiopic-halehame-so-et", "ethiopic-halehame-ti-er", "ethiopic-halehame-ti-et", "ethiopic-halehame-tig", "ew-resize", "expanded", "extra-condensed", "extra-expanded", "fantasy", "fast", "fill", "fixed", "flat", "footnotes", "forwards", "from", "geometricPrecision", "georgian", "graytext", "groove", "gujarati", "gurmukhi", "hand", "hangul", "hangul-consonant", "hebrew", "help", "hidden", "hide", "higher", "highlight", "highlighttext", "hiragana", "hiragana-iroha", "horizontal", "hsl", "hsla", "icon", "ignore", "inactiveborder", "inactivecaption", "inactivecaptiontext", "infinite", "infobackground", "infotext", "inherit", "initial", "inline", "inline-axis", "inline-block", "inline-table", "inset", "inside", "intrinsic", "invert", "italic", "justify", "kannada", "katakana", "katakana-iroha", "keep-all", "khmer", "landscape", "lao", "large", "larger", "left", "level", "lighter", "line-through", "linear", "lines", "list-item", "listbox", "listitem", "local", "logical", "loud", "lower", "lower-alpha", "lower-armenian", "lower-greek", "lower-hexadecimal", "lower-latin", "lower-norwegian", "lower-roman", "lowercase", "ltr", "malayalam", "match", "media-controls-background", "media-current-time-display", "media-fullscreen-button", "media-mute-button", "media-play-button", "media-return-to-realtime-button", "media-rewind-button", "media-seek-back-button", "media-seek-forward-button", "media-slider", "media-sliderthumb", "media-time-remaining-display", "media-volume-slider", "media-volume-slider-container", "media-volume-sliderthumb", "medium", "menu", "menulist", "menulist-button", "menulist-text", "menulist-textfield", "menutext", "message-box", "middle", "min-intrinsic", "mix", "mongolian", "monospace", "move", "multiple", "myanmar", "n-resize", "narrower", "ne-resize", "nesw-resize", "no-close-quote", "no-drop", "no-open-quote", "no-repeat", "none", "normal", "not-allowed", "nowrap", "ns-resize", "nw-resize", "nwse-resize", "oblique", "octal", "open-quote", "optimizeLegibility", "optimizeSpeed", "oriya", "oromo", "outset", "outside", "outside-shape", "overlay", "overline", "padding", "padding-box", "painted", "page", "paused", "persian", "plus-darker", "plus-lighter", "pointer", "polygon", "portrait", "pre", "pre-line", "pre-wrap", "preserve-3d", "progress", "push-button", "radio", "read-only", "read-write", "read-write-plaintext-only", "rectangle", "region", "relative", "repeat", "repeat-x", "repeat-y", "reset", "reverse", "rgb", "rgba", "ridge", "right", "round", "row-resize", "rtl", "run-in", "running", "s-resize", "sans-serif", "scroll", "scrollbar", "se-resize", "searchfield", "searchfield-cancel-button", "searchfield-decoration", "searchfield-results-button", "searchfield-results-decoration", "semi-condensed", "semi-expanded", "separate", "serif", "show", "sidama", "single", "skip-white-space", "slide", "slider-horizontal", "slider-vertical", "sliderthumb-horizontal", "sliderthumb-vertical", "slow", "small", "small-caps", "small-caption", "smaller", "solid", "somali", "source-atop", "source-in", "source-out", "source-over", "space", "square", "square-button", "start", "static", "status-bar", "stretch", "stroke", "sub", "subpixel-antialiased", "super", "sw-resize", "table", "table-caption", "table-cell", "table-column", "table-column-group", "table-footer-group", "table-header-group", "table-row", "table-row-group", "telugu", "text", "text-bottom", "text-top", "textarea", "textfield", "thai", "thick", "thin", "threeddarkshadow", "threedface", "threedhighlight", "threedlightshadow", "threedshadow", "tibetan", "tigre", "tigrinya-er", "tigrinya-er-abegede", "tigrinya-et", "tigrinya-et-abegede", "to", "top", "transparent", "ultra-condensed", "ultra-expanded", "underline", "up", "upper-alpha", "upper-armenian", "upper-greek", "upper-hexadecimal", "upper-latin", "upper-norwegian", "upper-roman", "uppercase", "urdu", "url", "vertical", "vertical-text", "visible", "visibleFill", "visiblePainted", "visibleStroke", "visual", "w-resize", "wait", "wave", "wider", "window", "windowframe", "windowtext", "x-large", "x-small", "xor", "xx-large", "xx-small"],
		p = t(h),
		d = ["font-family", "src", "unicode-range", "font-variant", "font-feature-settings", "font-stretch", "font-weight", "font-style"],
		v = t(d),
		m = n.concat(i).concat(o).concat(a).concat(l).concat(h);
	e.registerHelper("hintWords", "css", m), e.defineMIME("text/css", {
		mediaTypes: r,
		mediaFeatures: s,
		propertyKeywords: u,
		nonStandardPropertyKeywords: f,
		colorKeywords: c,
		valueKeywords: p,
		fontProperties: v,
		tokenHooks: {
			"<": function (e, t) {
				return e.match("!--") ? (t.tokenize = y, y(e, t)) : !1
			},
			"/": function (e, t) {
				return e.eat("*") ? (t.tokenize = g, g(e, t)) : !1
			}
		},
		name: "css"
	}), e.defineMIME("text/x-scss", {
		mediaTypes: r,
		mediaFeatures: s,
		propertyKeywords: u,
		nonStandardPropertyKeywords: f,
		colorKeywords: c,
		valueKeywords: p,
		fontProperties: v,
		allowNested: !0,
		tokenHooks: {
			"/": function (e, t) {
				return e.eat("/") ? (e.skipToEnd(), ["comment", "comment"]) : e.eat("*") ? (t.tokenize = g, g(e, t)) : ["operator", "operator"]
			},
			":": function (e) {
				return e.match(/\s*\{/) ? [null, "{"] : !1
			},
			$: function (e) {
				return e.match(/^[\w-]+/), e.match(/^\s*:/, !1) ? ["variable-2", "variable-definition"] : ["variable-2", "variable"]
			},
			"#": function (e) {
				return e.eat("{") ? [null, "interpolation"] : !1
			}
		},
		name: "css",
		helperType: "scss"
	}), e.defineMIME("text/x-less", {
		mediaTypes: r,
		mediaFeatures: s,
		propertyKeywords: u,
		nonStandardPropertyKeywords: f,
		colorKeywords: c,
		valueKeywords: p,
		fontProperties: v,
		allowNested: !0,
		tokenHooks: {
			"/": function (e, t) {
				return e.eat("/") ? (e.skipToEnd(), ["comment", "comment"]) : e.eat("*") ? (t.tokenize = g, g(e, t)) : ["operator", "operator"]
			},
			"@": function (e) {
				return e.match(/^(charset|document|font-face|import|(-(moz|ms|o|webkit)-)?keyframes|media|namespace|page|supports)\b/, !1) ? !1 : (e.eatWhile(/[\w\\\-]/), e.match(/^\s*:/, !1) ? ["variable-2", "variable-definition"] : ["variable-2", "variable"])
			},
			"&": function () {
				return ["atom", "atom"]
			}
		},
		name: "css",
		helperType: "less"
	})
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror"), require("../xml/xml"), require("../javascript/javascript"), require("../css/css")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror", "../xml/xml", "../javascript/javascript", "../css/css"], e) : e(CodeMirror)
}(function (e) {
	"use strict";
	e.defineMode("htmlmixed", function (t, n) {
		function f(e, t) {
			var n = t.htmlState.tagName;
			n && (n = n.toLowerCase());
			var o = r.token(e, t.htmlState);
			if (n == "script" && /\btag\b/.test(o) && e.current() == ">") {
				var u = e.string.slice(Math.max(0, e.pos - 100), e.pos).match(/\btype\s*=\s*("[^"]+"|'[^']+'|\S+)[^<]*$/i);
				u = u ? u[1] : "", u && /[\"\']/.test(u.charAt(0)) && (u = u.slice(1, u.length - 1));
				for (var a = 0; a < s.length; ++a) {
					var f = s[a];
					if (typeof f.matches == "string" ? u == f.matches : f.matches.test(u)) {
						f.mode && (t.token = c, t.localMode = f.mode, t.localState = f.mode.startState && f.mode.startState(r.indent(t.htmlState, "")));
						break
					}
				}
			} else n == "style" && /\btag\b/.test(o) && e.current() == ">" && (t.token = h, t.localMode = i, t.localState = i.startState(r.indent(t.htmlState, "")));
			return o
		}

		function l(e, t, n) {
			var r = e.current(),
				i = r.search(t),
				s;
			if (i > -1) e.backUp(r.length - i);
			else if (s = r.match(/<\/?$/)) e.backUp(r.length), e.match(t, !1) || e.match(r);
			return n
		}

		function c(e, t) {
			return e.match(/^<\/\s*script\s*>/i, !1) ? (t.token = f, t.localState = t.localMode = null, f(e, t)) : l(e, /<\/\s*script\s*>/, t.localMode.token(e, t.localState))
		}

		function h(e, t) {
			return e.match(/^<\/\s*style\s*>/i, !1) ? (t.token = f, t.localState = t.localMode = null, f(e, t)) : l(e, /<\/\s*style\s*>/, i.token(e, t.localState))
		}
		var r = e.getMode(t, {
				name: "xml",
				htmlMode: !0,
				multilineTagIndentFactor: n.multilineTagIndentFactor,
				multilineTagIndentPastTag: n.multilineTagIndentPastTag
			}),
			i = e.getMode(t, "css"),
			s = [],
			o = n && n.scriptTypes;
		s.push({
			matches: /^(?:text|application)\/(?:x-)?(?:java|ecma)script$|^$/i,
			mode: e.getMode(t, "javascript")
		});
		if (o)
			for (var u = 0; u < o.length; ++u) {
				var a = o[u];
				s.push({
					matches: a.matches,
					mode: a.mode && e.getMode(t, a.mode)
				})
			}
		return s.push({
			matches: /./,
			mode: e.getMode(t, "text/plain")
		}), {
			startState: function () {
				var e = r.startState();
				return {
					token: f,
					localMode: null,
					localState: null,
					htmlState: e
				}
			},
			copyState: function (t) {
				if (t.localState) var n = e.copyState(t.localMode, t.localState);
				return {
					token: t.token,
					localMode: t.localMode,
					localState: n,
					htmlState: e.copyState(r, t.htmlState)
				}
			},
			token: function (e, t) {
				return t.token(e, t)
			},
			indent: function (t, n) {
				return !t.localMode || /^\s*<\//.test(n) ? r.indent(t.htmlState, n) : t.localMode.indent ? t.localMode.indent(t.localState, n) : e.Pass
			},
			innerMode: function (e) {
				return {
					state: e.localState || e.htmlState,
					mode: e.localMode || r
				}
			}
		}
	}, "xml", "javascript", "css"), e.defineMIME("text/html", "htmlmixed")
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror"], e) : e(CodeMirror)
}(function (e) {
	"use strict";

	function r(e) {
		for (var r = 0; r < e.state.activeLines.length; r++) e.removeLineClass(e.state.activeLines[r], "wrap", t), e.removeLineClass(e.state.activeLines[r], "background", n)
	}

	function i(e, t) {
		if (e.length != t.length) return !1;
		for (var n = 0; n < e.length; n++)
			if (e[n] != t[n]) return !1;
		return !0
	}

	function s(e, s) {
		var o = [];
		for (var u = 0; u < s.length; u++) {
			var a = s[u];
			if (!a.empty()) continue;
			var f = e.getLineHandleVisualStart(a.head.line);
			o[o.length - 1] != f && o.push(f)
		}
		if (i(e.state.activeLines, o)) return;
		e.operation(function () {
			r(e);
			for (var i = 0; i < o.length; i++) e.addLineClass(o[i], "wrap", t), e.addLineClass(o[i], "background", n);
			e.state.activeLines = o
		})
	}

	function o(e, t) {
		s(e, t.ranges)
	}
	var t = "CodeMirror-activeline",
		n = "CodeMirror-activeline-background";
	e.defineOption("styleActiveLine", !1, function (t, n, i) {
		var u = i && i != e.Init;
		n && !u ? (t.state.activeLines = [], s(t, t.listSelections()), t.on("beforeSelectionChange", o)) : !n && u && (t.off("beforeSelectionChange", o), r(t), delete t.state.activeLines)
	})
}),
function (e) {
	typeof exports == "object" && typeof module == "object" ? e(require("../../lib/codemirror")) : typeof define == "function" && define.amd ? define(["../../lib/codemirror"], e) : e(CodeMirror)
}(function (e) {
	function i(e, t, i, o) {
		var u = e.getLineHandle(t.line),
			a = t.ch - 1,
			f = a >= 0 && r[u.text.charAt(a)] || r[u.text.charAt(++a)];
		if (!f) return null;
		var l = f.charAt(1) == ">" ? 1 : -1;
		if (i && l > 0 != (a == t.ch)) return null;
		var c = e.getTokenTypeAt(n(t.line, a + 1)),
			h = s(e, n(t.line, a + (l > 0 ? 1 : 0)), l, c || null, o);
		return h == null ? null : {
			from: n(t.line, a),
			to: h && h.pos,
			match: h && h.ch == f.charAt(0),
			forward: l > 0
		}
	}

	function s(e, t, i, s, o) {
		var u = o && o.maxScanLineLength || 1e4,
			a = o && o.maxScanLines || 1e3,
			f = [],
			l = o && o.bracketRegex ? o.bracketRegex : /[(){}[\]]/,
			c = i > 0 ? Math.min(t.line + a, e.lastLine() + 1) : Math.max(e.firstLine() - 1, t.line - a);
		for (var h = t.line; h != c; h += i) {
			var p = e.getLine(h);
			if (!p) continue;
			var d = i > 0 ? 0 : p.length - 1,
				v = i > 0 ? p.length : -1;
			if (p.length > u) continue;
			h == t.line && (d = t.ch - (i < 0 ? 1 : 0));
			for (; d != v; d += i) {
				var m = p.charAt(d);
				if (l.test(m) && (s === undefined || e.getTokenTypeAt(n(h, d + 1)) == s)) {
					var g = r[m];
					if (g.charAt(1) == ">" == i > 0) f.push(m);
					else {
						if (!f.length) return {
							pos: n(h, d),
							ch: m
						};
						f.pop()
					}
				}
			}
		}
		return h - i == (i > 0 ? e.lastLine() : e.firstLine()) ? !1 : null
	}

	function o(e, r, s) {
		var o = e.state.matchBrackets.maxHighlightLineLength || 1e3,
			u = [],
			a = e.listSelections();
		for (var f = 0; f < a.length; f++) {
			var l = a[f].empty() && i(e, a[f].head, !1, s);
			if (l && e.getLine(l.from.line).length <= o) {
				var c = l.match ? "CodeMirror-matchingbracket" : "CodeMirror-nonmatchingbracket";
				u.push(e.markText(l.from, n(l.from.line, l.from.ch + 1), {
					className: c
				})), l.to && e.getLine(l.to.line).length <= o && u.push(e.markText(l.to, n(l.to.line, l.to.ch + 1), {
					className: c
				}))
			}
		}
		if (u.length) {
			t && e.state.focused && e.display.input.focus();
			var h = function () {
				e.operation(function () {
					for (var e = 0; e < u.length; e++) u[e].clear()
				})
			};
			if (!r) return h;
			setTimeout(h, 800)
		}
	}

	function a(e) {
		e.operation(function () {
			u && (u(), u = null), u = o(e, !1, e.state.matchBrackets)
		})
	}
	var t = /MSIE \d/.test(navigator.userAgent) && (document.documentMode == null || document.documentMode < 8),
		n = e.Pos,
		r = {
			"(": ")>",
			")": "(<",
			"[": "]>",
			"]": "[<",
			"{": "}>",
			"}": "{<"
		},
		u = null;
	e.defineOption("matchBrackets", !1, function (t, n, r) {
		r && r != e.Init && t.off("cursorActivity", a), n && (t.state.matchBrackets = typeof n == "object" ? n : {}, t.on("cursorActivity", a))
	}), e.defineExtension("matchBrackets", function () {
		o(this, !0)
	}), e.defineExtension("findMatchingBracket", function (e, t, n) {
		return i(this, e, t, n)
	}), e.defineExtension("scanForBracket", function (e, t, n, r) {
		return s(this, e, t, n, r)
	})
}),
function () {
	function e(e) {
		return e.replace(/^\s+|\s+$/g, "")
	}

	function t(e) {
		return e.replace(/^\s+/g, "")
	}

	function n(e) {
		return e.replace(/\s+$/g, "")
	}

	function r(e, r, i, s) {
		function g() {
			return this.pos = 0, this.token = "", this.current_mode = "CONTENT", this.tags = {
				parent: "parent1",
				parentcount: 1,
				parent1: ""
			}, this.tag_type = "", this.token_text = this.last_token = this.last_text = this.token_type = "", this.newlines = 0, this.indent_content = u, this.Utils = {
				whitespace: "\n\r	 ".split(""),
				single_token: "br,input,link,meta,!doctype,basefont,base,area,hr,wbr,param,img,isindex,?xml,embed,?php,?,?=".split(","),
				extra_liners: "head,body,/html".split(","),
				in_array: function (e, t) {
					for (var n = 0; n < t.length; n++)
						if (e === t[n]) return !0;
					return !1
				}
			}, this.is_whitespace = function (e) {
				for (var t = 0; t < e.length; e++)
					if (!this.Utils.in_array(e.charAt(t), this.Utils.whitespace)) return !1;
				return !0
			}, this.traverse_whitespace = function () {
				var e = "";
				e = this.input.charAt(this.pos);
				if (this.Utils.in_array(e, this.Utils.whitespace)) {
					this.newlines = 0;
					while (this.Utils.in_array(e, this.Utils.whitespace)) p && e === "\n" && this.newlines <= d && (this.newlines += 1), this.pos++, e = this.input.charAt(this.pos);
					return !0
				}
				return !1
			}, this.space_or_wrap = function (e) {
				this.line_char_count >= this.wrap_line_length ? (this.print_newline(!1, e), this.print_indentation(e)) : (this.line_char_count++, e.push(" "))
			}, this.get_content = function () {
				var e = "",
					t = [],
					n = !1;
				while (this.input.charAt(this.pos) !== "<") {
					if (this.pos >= this.input.length) return t.length ? t.join("") : ["", "TK_EOF"];
					if (this.traverse_whitespace()) {
						this.space_or_wrap(t);
						continue
					}
					if (v) {
						var r = this.input.substr(this.pos, 3);
						if (r === "{{#" || r === "{{/") break;
						if (this.input.substr(this.pos, 2) === "{{" && this.get_tag(!0) === "{{else}}") break
					}
					e = this.input.charAt(this.pos), this.pos++, this.line_char_count++, t.push(e)
				}
				return t.length ? t.join("") : ""
			}, this.get_contents_to = function (e) {
				if (this.pos === this.input.length) return ["", "TK_EOF"];
				var t = "",
					n = "",
					r = new RegExp("</" + e + "\\s*>", "igm");
				r.lastIndex = this.pos;
				var i = r.exec(this.input),
					s = i ? i.index : this.input.length;
				return this.pos < s && (n = this.input.substring(this.pos, s), this.pos = s), n
			}, this.record_tag = function (e) {
				this.tags[e + "count"] ? (this.tags[e + "count"]++, this.tags[e + this.tags[e + "count"]] = this.indent_level) : (this.tags[e + "count"] = 1, this.tags[e + this.tags[e + "count"]] = this.indent_level), this.tags[e + this.tags[e + "count"] + "parent"] = this.tags.parent, this.tags.parent = e + this.tags[e + "count"]
			}, this.retrieve_tag = function (e) {
				if (this.tags[e + "count"]) {
					var t = this.tags.parent;
					while (t) {
						if (e + this.tags[e + "count"] === t) break;
						t = this.tags[t + "parent"]
					}
					t && (this.indent_level = this.tags[e + this.tags[e + "count"]], this.tags.parent = this.tags[t + "parent"]), delete this.tags[e + this.tags[e + "count"] + "parent"], delete this.tags[e + this.tags[e + "count"]], this.tags[e + "count"] === 1 ? delete this.tags[e + "count"] : this.tags[e + "count"]--
				}
			}, this.indent_to_tag = function (e) {
				if (!this.tags[e + "count"]) return;
				var t = this.tags.parent;
				while (t) {
					if (e + this.tags[e + "count"] === t) break;
					t = this.tags[t + "parent"]
				}
				t && (this.indent_level = this.tags[e + this.tags[e + "count"]])
			}, this.get_tag = function (e) {
				var t = "",
					n = [],
					r = "",
					i = !1,
					s, o, u, a = this.pos,
					f = this.line_char_count;
				e = e !== undefined ? e : !1;
				do {
					if (this.pos >= this.input.length) return e && (this.pos = a, this.line_char_count = f), n.length ? n.join("") : ["", "TK_EOF"];
					t = this.input.charAt(this.pos), this.pos++;
					if (this.Utils.in_array(t, this.Utils.whitespace)) {
						i = !0;
						continue
					}
					if (t === "'" || t === '"') t += this.get_unformatted(t), i = !0;
					t === "=" && (i = !1), n.length && n[n.length - 1] !== "=" && t !== ">" && i && (this.space_or_wrap(n), i = !1), v && u === "<" && t + this.input.charAt(this.pos) === "{{" && (t += this.get_unformatted("}}"), n.length && n[n.length - 1] !== " " && n[n.length - 1] !== "<" && (t = " " + t), i = !0), t === "<" && !u && (s = this.pos - 1, u = "<"), v && !u && n.length >= 2 && n[n.length - 1] === "{" && n[n.length - 2] == "{" && (t === "#" || t === "/" ? s = this.pos - 3 : s = this.pos - 2, u = "{"), this.line_char_count++, n.push(t);
					if (n[1] && n[1] === "!") {
						n = [this.get_comment(s)];
						break
					}
					if (v && u === "{" && n.length > 2 && n[n.length - 2] === "}" && n[n.length - 1] === "}") break
				} while (t !== ">");
				var l = n.join(""),
					c, p;
				l.indexOf(" ") !== -1 ? c = l.indexOf(" ") : l[0] === "{" ? c = l.indexOf("}") : c = l.indexOf(">"), l[0] === "<" || !v ? p = 1 : p = l[2] === "#" ? 3 : 2;
				var d = l.substring(p, c).toLowerCase();
				return l.charAt(l.length - 2) === "/" || this.Utils.in_array(d, this.Utils.single_token) ? e || (this.tag_type = "SINGLE") : v && l[0] === "{" && d === "else" ? e || (this.indent_to_tag("if"), this.tag_type = "HANDLEBARS_ELSE", this.indent_content = !0, this.traverse_whitespace()) : this.is_unformatted(d, h) ? (r = this.get_unformatted("</" + d + ">", l), n.push(r), o = this.pos - 1, this.tag_type = "SINGLE") : d === "script" && (l.search("type") === -1 || l.search("type") > -1 && l.search(/\b(text|application)\/(x-)?(javascript|ecmascript|jscript|livescript)/) > -1) ? e || (this.record_tag(d), this.tag_type = "SCRIPT") : d === "style" && (l.search("type") === -1 || l.search("type") > -1 && l.search("text/css") > -1) ? e || (this.record_tag(d), this.tag_type = "STYLE") : d.charAt(0) === "!" ? e || (this.tag_type = "SINGLE", this.traverse_whitespace()) : e || (d.charAt(0) === "/" ? (this.retrieve_tag(d.substring(1)), this.tag_type = "END") : (this.record_tag(d), d.toLowerCase() !== "html" && (this.indent_content = !0), this.tag_type = "START"), this.traverse_whitespace() && this.space_or_wrap(n), this.Utils.in_array(d, this.Utils.extra_liners) && (this.print_newline(!1, this.output), this.output.length && this.output[this.output.length - 2] !== "\n" && this.print_newline(!0, this.output))), e && (this.pos = a, this.line_char_count = f), n.join("")
			}, this.get_comment = function (e) {
				var t = "",
					n = ">",
					r = !1;
				this.pos = e, input_char = this.input.charAt(this.pos), this.pos++;
				while (this.pos <= this.input.length) {
					t += input_char;
					if (t[t.length - 1] === n[n.length - 1] && t.indexOf(n) !== -1) break;
					!r && t.length < 10 && (t.indexOf("<![if") === 0 ? (n = "<![endif]>", r = !0) : t.indexOf("<![cdata[") === 0 ? (n = "]]>", r = !0) : t.indexOf("<![") === 0 ? (n = "]>", r = !0) : t.indexOf("<!--") === 0 && (n = "-->", r = !0)), input_char = this.input.charAt(this.pos), this.pos++
				}
				return t
			}, this.get_unformatted = function (e, t) {
				if (t && t.toLowerCase().indexOf(e) !== -1) return "";
				var n = "",
					r = "",
					i = 0,
					s = !0;
				do {
					if (this.pos >= this.input.length) return r;
					n = this.input.charAt(this.pos), this.pos++;
					if (this.Utils.in_array(n, this.Utils.whitespace)) {
						if (!s) {
							this.line_char_count--;
							continue
						}
						if (n === "\n" || n === "\r") {
							r += "\n", this.line_char_count = 0;
							continue
						}
					}
					r += n, this.line_char_count++, s = !0, v && n === "{" && r.length && r[r.length - 2] === "{" && (r += this.get_unformatted("}}"), i = r.length)
				} while (r.toLowerCase().indexOf(e, i) === -1);
				return r
			}, this.get_token = function () {
				var e;
				if (this.last_token === "TK_TAG_SCRIPT" || this.last_token === "TK_TAG_STYLE") {
					var t = this.last_token.substr(7);
					return e = this.get_contents_to(t), typeof e != "string" ? e : [e, "TK_" + t]
				}
				if (this.current_mode === "CONTENT") return e = this.get_content(), typeof e != "string" ? e : [e, "TK_CONTENT"];
				if (this.current_mode === "TAG") {
					e = this.get_tag();
					if (typeof e != "string") return e;
					var n = "TK_TAG_" + this.tag_type;
					return [e, n]
				}
			}, this.get_full_indent = function (e) {
				return e = this.indent_level + e || 0, e < 1 ? "" : Array(e + 1).join(this.indent_string)
			}, this.is_unformatted = function (e, t) {
				if (!this.Utils.in_array(e, t)) return !1;
				if (e.toLowerCase() !== "a" || !this.Utils.in_array("a", t)) return !0;
				var n = this.get_tag(!0),
					r = (n || "").match(/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/);
				return !r || this.Utils.in_array(r, t) ? !0 : !1
			}, this.printer = function (e, r, i, s, o) {
				this.input = e || "", this.output = [], this.indent_character = r, this.indent_string = "", this.indent_size = i, this.brace_style = o, this.indent_level = 0, this.wrap_line_length = s, this.line_char_count = 0;
				for (var u = 0; u < this.indent_size; u++) this.indent_string += this.indent_character;
				this.print_newline = function (e, t) {
					this.line_char_count = 0;
					if (!t || !t.length) return;
					if (e || t[t.length - 1] !== "\n") t[t.length - 1] !== "\n" && (t[t.length - 1] = n(t[t.length - 1])), t.push("\n")
				}, this.print_indentation = function (e) {
					for (var t = 0; t < this.indent_level; t++) e.push(this.indent_string), this.line_char_count += this.indent_string.length
				}, this.print_token = function (e) {
					if (this.is_whitespace(e) && !this.output.length) return;
					(e || e !== "") && this.output.length && this.output[this.output.length - 1] === "\n" && (this.print_indentation(this.output), e = t(e)), this.print_token_raw(e)
				}, this.print_token_raw = function (e) {
					this.newlines > 0 && (e = n(e)), e && e !== "" && (e.length > 1 && e[e.length - 1] === "\n" ? (this.output.push(e.slice(0, -1)), this.print_newline(!1, this.output)) : this.output.push(e));
					for (var t = 0; t < this.newlines; t++) this.print_newline(t > 0, this.output);
					this.newlines = 0
				}, this.indent = function () {
					this.indent_level++
				}, this.unindent = function () {
					this.indent_level > 0 && this.indent_level--
				}
			}, this
		}
		var o, u, a, f, l, c, h, p, d, v, m;
		r = r || {}, (r.wrap_line_length === undefined || parseInt(r.wrap_line_length, 10) === 0) && r.max_char !== undefined && parseInt(r.max_char, 10) !== 0 && (r.wrap_line_length = r.max_char), u = r.indent_inner_html === undefined ? !1 : r.indent_inner_html, a = r.indent_size === undefined ? 4 : parseInt(r.indent_size, 10), f = r.indent_char === undefined ? " " : r.indent_char, c = r.brace_style === undefined ? "collapse" : r.brace_style, l = parseInt(r.wrap_line_length, 10) === 0 ? 32786 : parseInt(r.wrap_line_length || 250, 10), h = r.unformatted || ["a", "span", "img", "bdo", "em", "strong", "dfn", "code", "samp", "kbd", "var", "cite", "abbr", "acronym", "q", "sub", "sup", "tt", "i", "b", "big", "small", "u", "s", "strike", "font", "ins", "del", "pre", "address", "dt", "h1", "h2", "h3", "h4", "h5", "h6"], p = r.preserve_newlines === undefined ? !0 : r.preserve_newlines, d = p ? isNaN(parseInt(r.max_preserve_newlines, 10)) ? 32786 : parseInt(r.max_preserve_newlines, 10) : 0, v = r.indent_handlebars === undefined ? !1 : r.indent_handlebars, m = r.end_with_newline === undefined ? !1 : r.end_with_newline, o = new g, o.printer(e, f, a, l, c);
		for (;;) {
			var y = o.get_token();
			o.token_text = y[0], o.token_type = y[1];
			if (o.token_type === "TK_EOF") break;
			switch (o.token_type) {
			case "TK_TAG_START":
				o.print_newline(!1, o.output), o.print_token(o.token_text), o.indent_content && (o.indent(), o.indent_content = !1), o.current_mode = "CONTENT";
				break;
			case "TK_TAG_STYLE":
			case "TK_TAG_SCRIPT":
				o.print_newline(!1, o.output), o.print_token(o.token_text), o.current_mode = "CONTENT";
				break;
			case "TK_TAG_END":
				if (o.last_token === "TK_CONTENT" && o.last_text === "") {
					var b = o.token_text.match(/\w+/)[0],
						w = null;
					o.output.length && (w = o.output[o.output.length - 1].match(/(?:<|{{#)\s*(\w+)/)), (w === null || w[1] !== b) && o.print_newline(!1, o.output)
				}
				o.print_token(o.token_text), o.current_mode = "CONTENT";
				break;
			case "TK_TAG_SINGLE":
				var E = o.token_text.match(/^\s*<([a-z-]+)/i);
				(!E || !o.Utils.in_array(E[1], h)) && o.print_newline(!1, o.output), o.print_token(o.token_text), o.current_mode = "CONTENT";
				break;
			case "TK_TAG_HANDLEBARS_ELSE":
				o.print_token(o.token_text), o.indent_content && (o.indent(), o.indent_content = !1), o.current_mode = "CONTENT";
				break;
			case "TK_CONTENT":
				o.print_token(o.token_text), o.current_mode = "TAG";
				break;
			case "TK_STYLE":
			case "TK_SCRIPT":
				if (o.token_text !== "") {
					o.print_newline(!1, o.output);
					var S = o.token_text,
						x, T = 1;
					o.token_type === "TK_SCRIPT" ? x = typeof i == "function" && i : o.token_type === "TK_STYLE" && (x = typeof s == "function" && s), r.indent_scripts === "keep" ? T = 0 : r.indent_scripts === "separate" && (T = -o.indent_level);
					var N = o.get_full_indent(T);
					if (x) S = x(S.replace(/^\s*/, N), r);
					else {
						var C = S.match(/^\s*/)[0],
							k = C.match(/[^\n\r]*$/)[0].split(o.indent_string).length - 1,
							L = o.get_full_indent(T - k);
						S = S.replace(/^\s*/, N).replace(/\r\n|\r|\n/g, "\n" + L).replace(/\s+$/, "")
					}
					S && (o.print_token_raw(S), o.print_newline(!0, o.output))
				}
				o.current_mode = "TAG";
				break;
			default:
				o.token_text !== "" && o.print_token(o.token_text)
			}
			o.last_token = o.token_type, o.last_text = o.token_text
		}
		var A = o.output.join("").replace(/[\r\n\t ]+$/, "");
		return m && (A += "\n"), A
	}
	if (typeof define == "function" && define.amd) define(["require", "./beautify", "./beautify-css"], function (e) {
		var t = e("./beautify"),
			n = e("./beautify-css");
		return {
			html_beautify: function (e, i) {
				return r(e, i, t.js_beautify, n.css_beautify)
			}
		}
	});
	else if (typeof exports != "undefined") {
		var i = require("./beautify.js"),
			s = require("./beautify-css.js");
		exports.html_beautify = function (e, t) {
			return r(e, t, i.js_beautify, s.css_beautify)
		}
	} else typeof window != "undefined" ? window.html_beautify = function (e, t) {
		return r(e, t, window.js_beautify, window.css_beautify)
	} : typeof global != "undefined" && (global.html_beautify = function (e, t) {
		return r(e, t, global.js_beautify, global.css_beautify)
	})
}(),
function () {
	function e(t, n) {
		function c() {
			return l = t.charAt(++f), l || ""
		}

		function h(e) {
			var n = f;
			return e && v(), result = t.charAt(f + 1) || "", f = n - 1, c(), result
		}

		function p(e) {
			var n = f;
			while (c())
				if (l === "\\") c();
				else {
					if (e.indexOf(l) !== -1) break;
					if (l === "\n") break
				}
			return t.substring(n, f + 1)
		}

		function d(e) {
			var t = f,
				n = p(e);
			return f = t - 1, c(), n
		}

		function v() {
			var e = "";
			while (u.test(h())) c(), e += l;
			return e
		}

		function m() {
			var e = "";
			l && u.test(l) && (e = l);
			while (u.test(c())) e += l;
			return e
		}

		function g(e) {
			var n = f,
				e = h() === "/";
			c();
			while (c()) {
				if (!e && l === "*" && h() === "/") {
					c();
					break
				}
				if (e && l === "\n") return t.substring(n, f)
			}
			return t.substring(n, f) + l
		}

		function y(e) {
			return t.substring(f - e.length, f).toLowerCase() === e
		}

		function b() {
			for (var e = f + 1; e < t.length; e++) {
				var n = t.charAt(e);
				if (n === "{") return !0;
				if (n === ";" || n === "}" || n === ")") return !1
			}
			return !1
		}

		function T() {
			S++, w += E
		}

		function N() {
			S--, w = w.slice(0, -r)
		}
		n = n || {};
		var r = n.indent_size || 4,
			i = n.indent_char || " ",
			s = n.selector_separator_newline === undefined ? !0 : n.selector_separator_newline,
			o = n.end_with_newline === undefined ? !1 : n.end_with_newline;
		typeof r == "string" && (r = parseInt(r, 10));
		var u = /^\s+$/,
			a = /[\w$\-_]/,
			f = -1,
			l, w = t.match(/^[\t ]*/)[0],
			E = (new Array(r + 1)).join(i),
			S = 0,
			x = 0,
			C = {};
		C["{"] = function (e) {
			C.singleSpace(), k.push(e), C.newLine()
		}, C["}"] = function (e) {
			C.newLine(), k.push(e), C.newLine()
		}, C._lastCharWhitespace = function () {
			return u.test(k[k.length - 1])
		}, C.newLine = function (e) {
			e || C.trim(), k.length && k.push("\n"), w && k.push(w)
		}, C.singleSpace = function () {
			k.length && !C._lastCharWhitespace() && k.push(" ")
		}, C.trim = function () {
			while (C._lastCharWhitespace()) k.pop()
		};
		var k = [];
		w && k.push(w);
		var L = !1,
			A = !1,
			O = "",
			M = "";
		for (;;) {
			var _ = m(),
				D = _ !== "",
				P = _.indexOf("\n") !== -1,
				M = O,
				O = l;
			if (!l) break;
			if (l === "/" && h() === "*") {
				var H = y("");
				C.newLine(), k.push(g()), C.newLine(), H && C.newLine(!0)
			} else if (l === "/" && h() === "/") !P && M !== "{" && C.trim(), C.singleSpace(), k.push(g()), C.newLine();
			else if (l === "@") {
				D && C.singleSpace(), k.push(l);
				var B = d(": ,;{}()[]/='\"").replace(/\s$/, "");
				B in e.NESTED_AT_RULE ? (x += 1, B in e.CONDITIONAL_GROUP_RULE && (A = !0)) : ": ".indexOf(B[B.length - 1]) >= 0 && (c(), B = p(": ").replace(/\s$/, ""), k.push(B), C.singleSpace())
			} else l === "{" ? h(!0) === "}" ? (v(), c(), C.singleSpace(), k.push("{}")) : (T(), C["{"](l), A ? (A = !1, L = S > x) : L = S >= x) : l === "}" ? (N(), C["}"](l), L = !1, x && x--) : l === ":" ? (v(), (L || A) && !y("&") && !b() ? (k.push(":"), C.singleSpace()) : h() === ":" ? (c(), k.push("::")) : k.push(":")) : l === '"' || l === "'" ? (D && C.singleSpace(), k.push(p(l))) : l === ";" ? (k.push(l), C.newLine()) : l === "(" ? y("url") ? (k.push(l), v(), c() && (l !== ")" && l !== '"' && l !== "'" ? k.push(p(")")) : f--)) : (D && C.singleSpace(), k.push(l), v()) : l === ")" ? k.push(l) : l === "," ? (k.push(l), v(), !L && s ? C.newLine() : C.singleSpace()) : l === "]" ? k.push(l) : l === "[" ? (D && C.singleSpace(), k.push(l)) : l === "=" ? (v(), k.push(l)) : (D && C.singleSpace(), k.push(l))
		}
		var j = k.join("").replace(/[\r\n\t ]+$/, "");
		return o && (j += "\n"), j
	}
	e.NESTED_AT_RULE = {
		"@page": !0,
		"@font-face": !0,
		"@keyframes": !0,
		"@media": !0,
		"@supports": !0,
		"@document": !0
	}, e.CONDITIONAL_GROUP_RULE = {
		"@media": !0,
		"@supports": !0,
		"@document": !0
	}, typeof define == "function" && define.amd ? define([], function () {
		return {
			css_beautify: e
		}
	}) : typeof exports != "undefined" ? exports.css_beautify = e : typeof window != "undefined" ? window.css_beautify = e : typeof global != "undefined" && (global.css_beautify = e)
}(),
function () {
	function t(e, t) {
		for (var n = 0; n < t.length; n += 1)
			if (t[n] === e) return !0;
		return !1
	}

	function n(e) {
		return e.replace(/^\s+|\s+$/g, "")
	}

	function r(e, t) {
		"use strict";
		var n = new s(e, t);
		return n.beautify()
	}

	function s(e, r) {
		"use strict";

		function S(e, t) {
			var n = 0;
			e && (n = e.indentation_level, !s.just_added_newline() && e.line_indent_level > n && (n = e.line_indent_level));
			var r = {
				mode: t,
				parent: e,
				last_text: e ? e.last_text : "",
				last_word: e ? e.last_word : "",
				declaration_statement: !1,
				declaration_assignment: !1,
				multiline_frame: !1,
				if_block: !1,
				else_block: !1,
				do_block: !1,
				do_while: !1,
				in_case_statement: !1,
				in_case: !1,
				case_body: !1,
				indentation_level: n,
				line_indent_level: e ? e.line_indent_level : n,
				start_line_index: s.get_line_number(),
				ternary_depth: 0
			};
			return r
		}

		function T(e) {
			var t = e.newlines,
				n = w.keep_array_indentation && D(v.mode);
			if (n)
				for (r = 0; r < t; r += 1) k(r > 0);
			else {
				w.max_preserve_newlines && t > w.max_preserve_newlines && (t = w.max_preserve_newlines);
				if (w.preserve_newlines && e.newlines > 1) {
					k();
					for (var r = 1; r < t; r += 1) k(!0)
				}
			}
			c = e, b[c.type]()
		}

		function N(e) {
			e = e.replace(/\x0d/g, "");
			var t = [],
				n = e.indexOf("\n");
			while (n !== -1) t.push(e.substring(0, n)), e = e.substring(n + 1), n = e.indexOf("\n");
			return e.length && t.push(e), t
		}

		function C(e) {
			e = e === undefined ? !1 : e;
			if (s.just_added_newline()) return;
			if (w.preserve_newlines && c.wanted_newline || e) k(!1, !0);
			else if (w.wrap_line_length) {
				var t = s.current_line.get_character_count() + c.text.length + (s.space_before_token ? 1 : 0);
				t >= w.wrap_line_length && k(!1, !0)
			}
		}

		function k(e, t) {
			if (!t && v.last_text !== ";" && v.last_text !== "," && v.last_text !== "=" && h !== "TK_OPERATOR")
				while (v.mode === i.Statement && !v.if_block && !v.do_block) H();
			s.add_new_line(e) && (v.multiline_frame = !0)
		}

		function L() {
			s.just_added_newline() && (w.keep_array_indentation && D(v.mode) && c.wanted_newline ? (s.current_line.push(c.whitespace_before), s.space_before_token = !1) : s.set_indent(v.indentation_level) && (v.line_indent_level = v.indentation_level))
		}

		function A(e) {
			e = e || c.text, L(), s.add_token(e)
		}

		function O() {
			v.indentation_level += 1
		}

		function M() {
			v.indentation_level > 0 && (!v.parent || v.indentation_level > v.parent.indentation_level) && (v.indentation_level -= 1)
		}

		function _(e) {
			v ? (g.push(v), m = v) : m = S(null, e), v = S(m, e)
		}

		function D(e) {
			return e === i.ArrayLiteral
		}

		function P(e) {
			return t(e, [i.Expression, i.ForInitializer, i.Conditional])
		}

		function H() {
			g.length > 0 && (m = v, v = g.pop(), m.mode === i.Statement && s.remove_redundant_indentation(m))
		}

		function B() {
			return v.parent.mode === i.ObjectLiteral && v.mode === i.Statement && (v.last_text === ":" && v.ternary_depth === 0 || h === "TK_RESERVED" && t(v.last_text, ["get", "set"]))
		}

		function j() {
			return h === "TK_RESERVED" && t(v.last_text, ["var", "let", "const"]) && c.type === "TK_WORD" || h === "TK_RESERVED" && v.last_text === "do" || h === "TK_RESERVED" && v.last_text === "return" && !c.wanted_newline || h === "TK_RESERVED" && v.last_text === "else" && (c.type !== "TK_RESERVED" || c.text !== "if") || h === "TK_END_EXPR" && (m.mode === i.ForInitializer || m.mode === i.Conditional) || h === "TK_WORD" && v.mode === i.BlockStatement && !v.in_case && c.text !== "--" && c.text !== "++" && c.type !== "TK_WORD" && c.type !== "TK_RESERVED" || v.mode === i.ObjectLiteral && (v.last_text === ":" && v.ternary_depth === 0 || h === "TK_RESERVED" && t(v.last_text, ["get", "set"])) ? (_(i.Statement), O(), h === "TK_RESERVED" && t(v.last_text, ["var", "let", "const"]) && c.type === "TK_WORD" && (v.declaration_statement = !0), B() || C(c.type === "TK_RESERVED" && t(c.text, ["do", "for", "if", "while"])), !0) : !1
		}

		function F(e, t) {
			for (var r = 0; r < e.length; r++) {
				var i = n(e[r]);
				if (i.charAt(0) !== t) return !1
			}
			return !0
		}

		function I(e, t) {
			var n = 0,
				r = e.length,
				i;
			for (; n < r; n++) {
				i = e[n];
				if (i && i.indexOf(t) !== 0) return !1
			}
			return !0
		}

		function q(e) {
			return t(e, ["case", "return", "do", "if", "throw", "else"])
		}

		function R(e) {
			var t = a + (e || 0);
			return t < 0 || t >= o.length ? null : o[t]
		}

		function U() {
			j();
			var e = i.Expression;
			if (c.text === "[") {
				if (h === "TK_WORD" || v.last_text === ")") {
					h === "TK_RESERVED" && t(v.last_text, l.line_starters) && (s.space_before_token = !0), _(e), A(), O(), w.space_in_paren && (s.space_before_token = !0);
					return
				}
				e = i.ArrayLiteral, D(v.mode) && (v.last_text === "[" || v.last_text === "," && (p === "]" || p === "}")) && (w.keep_array_indentation || k())
			} else h === "TK_RESERVED" && v.last_text === "for" ? e = i.ForInitializer : h === "TK_RESERVED" && t(v.last_text, ["if", "while"]) && (e = i.Conditional);
			v.last_text === ";" || h === "TK_START_BLOCK" ? k() : h === "TK_END_EXPR" || h === "TK_START_EXPR" || h === "TK_END_BLOCK" || v.last_text === "." ? C(c.wanted_newline) : h === "TK_RESERVED" && c.text === "(" || h === "TK_WORD" || h === "TK_OPERATOR" ? h === "TK_RESERVED" && (v.last_word === "function" || v.last_word === "typeof") || v.last_text === "*" && p === "function" ? w.space_after_anon_function && (s.space_before_token = !0) : h === "TK_RESERVED" && (t(v.last_text, l.line_starters) || v.last_text === "catch") && w.space_before_conditional && (s.space_before_token = !0) : s.space_before_token = !0, c.text === "(" && (h === "TK_EQUALS" || h === "TK_OPERATOR") && (B() || C()), _(e), A(), w.space_in_paren && (s.space_before_token = !0), O()
		}

		function z() {
			while (v.mode === i.Statement) H();
			v.multiline_frame && C(c.text === "]" && D(v.mode) && !w.keep_array_indentation), w.space_in_paren && (h === "TK_START_EXPR" && !w.space_in_empty_paren ? (s.trim(), s.space_before_token = !1) : s.space_before_token = !0), c.text === "]" && w.keep_array_indentation ? (A(), H()) : (H(), A()), s.remove_redundant_indentation(m), v.do_while && m.mode === i.Conditional && (m.mode = i.Expression, v.do_block = !1, v.do_while = !1)
		}

		function W() {
			var e = R(1),
				n = R(2);
			n && (n.text === ":" && t(e.type, ["TK_STRING", "TK_WORD", "TK_RESERVED"]) || t(e.text, ["get", "set"]) && t(n.type, ["TK_WORD", "TK_RESERVED"])) ? t(p, ["class", "interface"]) ? _(i.BlockStatement) : _(i.ObjectLiteral) : _(i.BlockStatement);
			var r = !e.comments_before.length && e.text === "}",
				o = r && v.last_word === "function" && h === "TK_END_EXPR";
			w.brace_style === "expand" || w.brace_style === "none" && c.wanted_newline ? h !== "TK_OPERATOR" && (o || h === "TK_EQUALS" || h === "TK_RESERVED" && q(v.last_text) && v.last_text !== "else") ? s.space_before_token = !0 : k(!1, !0) : h !== "TK_OPERATOR" && h !== "TK_START_EXPR" ? h === "TK_START_BLOCK" ? k() : s.space_before_token = !0 : D(m.mode) && v.last_text === "," && (p === "}" ? s.space_before_token = !0 : k()), A(), O()
		}

		function X() {
			while (v.mode === i.Statement) H();
			var e = h === "TK_START_BLOCK";
			w.brace_style === "expand" ? e || k() : e || (D(v.mode) && w.keep_array_indentation ? (w.keep_array_indentation = !1, k(), w.keep_array_indentation = !0) : k()), H(), A()
		}

		function V() {
			c.type === "TK_RESERVED" && v.mode !== i.ObjectLiteral && t(c.text, ["set", "get"]) && (c.type = "TK_WORD");
			if (c.type === "TK_RESERVED" && v.mode === i.ObjectLiteral) {
				var e = R(1);
				e.text == ":" && (c.type = "TK_WORD")
			}
			j() || c.wanted_newline && !P(v.mode) && (h !== "TK_OPERATOR" || v.last_text === "--" || v.last_text === "++") && h !== "TK_EQUALS" && (w.preserve_newlines || h !== "TK_RESERVED" || !t(v.last_text, ["var", "let", "const", "set", "get"])) && k();
			if (v.do_block && !v.do_while) {
				if (c.type === "TK_RESERVED" && c.text === "while") {
					s.space_before_token = !0, A(), s.space_before_token = !0, v.do_while = !0;
					return
				}
				k(), v.do_block = !1
			}
			if (v.if_block)
				if (!v.else_block && c.type === "TK_RESERVED" && c.text === "else") v.else_block = !0;
				else {
					while (v.mode === i.Statement) H();
					v.if_block = !1, v.else_block = !1
				}
			if (c.type === "TK_RESERVED" && (c.text === "case" || c.text === "default" && v.in_case_statement)) {
				k();
				if (v.case_body || w.jslint_happy) M(), v.case_body = !1;
				A(), v.in_case = !0, v.in_case_statement = !0;
				return
			}
			c.type === "TK_RESERVED" && c.text === "function" && ((t(v.last_text, ["}", ";"]) || s.just_added_newline() && !t(v.last_text, ["[", "{", ":", "=", ","])) && !s.just_added_blankline() && !c.comments_before.length && (k(), k(!0)), h === "TK_RESERVED" || h === "TK_WORD" ? h === "TK_RESERVED" && t(v.last_text, ["get", "set", "new", "return", "export"]) ? s.space_before_token = !0 : h === "TK_RESERVED" && v.last_text === "default" && p === "export" ? s.space_before_token = !0 : k() : h === "TK_OPERATOR" || v.last_text === "=" ? s.space_before_token = !0 : (!!v.multiline_frame || !P(v.mode) && !D(v.mode)) && k());
			if (h === "TK_COMMA" || h === "TK_START_EXPR" || h === "TK_EQUALS" || h === "TK_OPERATOR") B() || C();
			if (c.type === "TK_RESERVED" && t(c.text, ["function", "get", "set"])) {
				A(), v.last_word = c.text;
				return
			}
			y = "NONE", h === "TK_END_BLOCK" ? c.type !== "TK_RESERVED" || !t(c.text, ["else", "catch", "finally"]) ? y = "NEWLINE" : w.brace_style === "expand" || w.brace_style === "end-expand" || w.brace_style === "none" && c.wanted_newline ? y = "NEWLINE" : (y = "SPACE", s.space_before_token = !0) : h === "TK_SEMICOLON" && v.mode === i.BlockStatement ? y = "NEWLINE" : h === "TK_SEMICOLON" && P(v.mode) ? y = "SPACE" : h === "TK_STRING" ? y = "NEWLINE" : h === "TK_RESERVED" || h === "TK_WORD" || v.last_text === "*" && p === "function" ? y = "SPACE" : h === "TK_START_BLOCK" ? y = "NEWLINE" : h === "TK_END_EXPR" && (s.space_before_token = !0, y = "NEWLINE"), c.type === "TK_RESERVED" && t(c.text, l.line_starters) && v.last_text !== ")" && (v.last_text === "else" || v.last_text === "export" ? y = "SPACE" : y = "NEWLINE");
			if (c.type === "TK_RESERVED" && t(c.text, ["else", "catch", "finally"]))
				if (h !== "TK_END_BLOCK" || w.brace_style === "expand" || w.brace_style === "end-expand" || w.brace_style === "none" && c.wanted_newline) k();
				else {
					s.trim(!0);
					var n = s.current_line;
					n.last() !== "}" && k(), s.space_before_token = !0
				} else y === "NEWLINE" ? h === "TK_RESERVED" && q(v.last_text) ? s.space_before_token = !0 : h !== "TK_END_EXPR" ? (h !== "TK_START_EXPR" || c.type !== "TK_RESERVED" || !t(c.text, ["var", "let", "const"])) && v.last_text !== ":" && (c.type === "TK_RESERVED" && c.text === "if" && v.last_text === "else" ? s.space_before_token = !0 : k()) : c.type === "TK_RESERVED" && t(c.text, l.line_starters) && v.last_text !== ")" && k() : v.multiline_frame && D(v.mode) && v.last_text === "," && p === "}" ? k() : y === "SPACE" && (s.space_before_token = !0);
			A(), v.last_word = c.text, c.type === "TK_RESERVED" && c.text === "do" && (v.do_block = !0), c.type === "TK_RESERVED" && c.text === "if" && (v.if_block = !0)
		}

		function $() {
			j() && (s.space_before_token = !1);
			while (v.mode === i.Statement && !v.if_block && !v.do_block) H();
			A()
		}

		function J() {
			j() ? s.space_before_token = !0 : h === "TK_RESERVED" || h === "TK_WORD" ? s.space_before_token = !0 : h === "TK_COMMA" || h === "TK_START_EXPR" || h === "TK_EQUALS" || h === "TK_OPERATOR" ? B() || C() : k(), A()
		}

		function K() {
			j(), v.declaration_statement && (v.declaration_assignment = !0), s.space_before_token = !0, A(), s.space_before_token = !0
		}

		function Q() {
			if (v.declaration_statement) {
				P(v.parent.mode) && (v.declaration_assignment = !1), A(), v.declaration_assignment ? (v.declaration_assignment = !1, k(!1, !0)) : s.space_before_token = !0;
				return
			}
			A(), v.mode === i.ObjectLiteral || v.mode === i.Statement && v.parent.mode === i.ObjectLiteral ? (v.mode === i.Statement && H(), k()) : s.space_before_token = !0
		}

		function G() {
			j();
			if (h === "TK_RESERVED" && q(v.last_text)) {
				s.space_before_token = !0, A();
				return
			}
			if (c.text === "*" && h === "TK_DOT") {
				A();
				return
			}
			if (c.text === ":" && v.in_case) {
				v.case_body = !0, O(), A(), k(), v.in_case = !1;
				return
			}
			if (c.text === "::") {
				A();
				return
			}
			c.wanted_newline && (c.text === "--" || c.text === "++") && k(!1, !0), h === "TK_OPERATOR" && C();
			var e = !0,
				n = !0;
			t(c.text, ["--", "++", "!", "~"]) || t(c.text, ["-", "+"]) && (t(h, ["TK_START_BLOCK", "TK_START_EXPR", "TK_EQUALS", "TK_OPERATOR"]) || t(v.last_text, l.line_starters) || v.last_text === ",") ? (e = !1, n = !1, v.last_text === ";" && P(v.mode) && (e = !0), h === "TK_RESERVED" || h === "TK_END_EXPR" ? e = !0 : h === "TK_OPERATOR" && (e = t(c.text, ["--", "-"]) && t(v.last_text, ["--", "-"]) || t(c.text, ["++", "+"]) && t(v.last_text, ["++", "+"])), (v.mode === i.BlockStatement || v.mode === i.Statement) && (v.last_text === "{" || v.last_text === ";") && k()) : c.text === ":" ? v.ternary_depth === 0 ? e = !1 : v.ternary_depth -= 1 : c.text === "?" ? v.ternary_depth += 1 : c.text === "*" && h === "TK_RESERVED" && v.last_text === "function" && (e = !1, n = !1), s.space_before_token = s.space_before_token || e, A(), s.space_before_token = n
		}

		function Y() {
			var e = N(c.text),
				t, r = !1,
				i = !1,
				o = c.whitespace_before,
				u = o.length;
			k(!1, !0), e.length > 1 && (F(e.slice(1), "*") ? r = !0 : I(e.slice(1), o) && (i = !0)), A(e[0]);
			for (t = 1; t < e.length; t++) k(!1, !0), r ? A(" " + n(e[t])) : i && e[t].length > u ? A(e[t].substring(u)) : s.add_token(e[t]);
			k(!1, !0)
		}

		function Z() {
			s.space_before_token = !0, A(), s.space_before_token = !0
		}

		function et() {
			c.wanted_newline ? k(!1, !0) : s.trim(!0), s.space_before_token = !0, A(), k(!1, !0)
		}

		function tt() {
			j(), h === "TK_RESERVED" && q(v.last_text) ? s.space_before_token = !0 : C(v.last_text === ")" && w.break_chained_methods), A()
		}

		function nt() {
			A(), c.text[c.text.length - 1] === "\n" && k()
		}

		function rt() {
			while (v.mode === i.Statement) H()
		}
		var s, o = [],
			a, l, c, h, p, d, v, m, g, y, b, w, E = "";
		b = {
			TK_START_EXPR: U,
			TK_END_EXPR: z,
			TK_START_BLOCK: W,
			TK_END_BLOCK: X,
			TK_WORD: V,
			TK_RESERVED: V,
			TK_SEMICOLON: $,
			TK_STRING: J,
			TK_EQUALS: K,
			TK_OPERATOR: G,
			TK_COMMA: Q,
			TK_BLOCK_COMMENT: Y,
			TK_INLINE_COMMENT: Z,
			TK_COMMENT: et,
			TK_DOT: tt,
			TK_UNKNOWN: nt,
			TK_EOF: rt
		}, r = r ? r : {}, w = {}, r.braces_on_own_line !== undefined && (w.brace_style = r.braces_on_own_line ? "expand" : "collapse"), w.brace_style = r.brace_style ? r.brace_style : w.brace_style ? w.brace_style : "collapse", w.brace_style === "expand-strict" && (w.brace_style = "expand"), w.indent_size = r.indent_size ? parseInt(r.indent_size, 10) : 4, w.indent_char = r.indent_char ? r.indent_char : " ", w.preserve_newlines = r.preserve_newlines === undefined ? !0 : r.preserve_newlines, w.break_chained_methods = r.break_chained_methods === undefined ? !1 : r.break_chained_methods, w.max_preserve_newlines = r.max_preserve_newlines === undefined ? 0 : parseInt(r.max_preserve_newlines, 10), w.space_in_paren = r.space_in_paren === undefined ? !1 : r.space_in_paren, w.space_in_empty_paren = r.space_in_empty_paren === undefined ? !1 : r.space_in_empty_paren, w.jslint_happy = r.jslint_happy === undefined ? !1 : r.jslint_happy, w.space_after_anon_function = r.space_after_anon_function === undefined ? !1 : r.space_after_anon_function, w.keep_array_indentation = r.keep_array_indentation === undefined ? !1 : r.keep_array_indentation, w.space_before_conditional = r.space_before_conditional === undefined ? !0 : r.space_before_conditional, w.unescape_strings = r.unescape_strings === undefined ? !1 : r.unescape_strings, w.wrap_line_length = r.wrap_line_length === undefined ? 0 : parseInt(r.wrap_line_length, 10), w.e4x = r.e4x === undefined ? !1 : r.e4x, w.end_with_newline = r.end_with_newline === undefined ? !1 : r.end_with_newline, w.jslint_happy && (w.space_after_anon_function = !0), r.indent_with_tabs && (w.indent_char = "	", w.indent_size = 1), d = "";
		while (w.indent_size > 0) d += w.indent_char, w.indent_size -= 1;
		var x = 0;
		if (e && e.length) {
			while (e.charAt(x) === " " || e.charAt(x) === "	") E += e.charAt(x), x += 1;
			e = e.substring(x)
		}
		h = "TK_START_BLOCK", p = "", s = new u(d, E), g = [], _(i.BlockStatement), this.beautify = function () {
			var t, n;
			l = new f(e, w, d), o = l.tokenize(), a = 0;
			while (t = R()) {
				for (var r = 0; r < t.comments_before.length; r++) T(t.comments_before[r]);
				T(t), p = v.last_text, h = t.type, v.last_text = t.text, a += 1
			}
			return n = s.get_code(), w.end_with_newline && (n += "\n"), n
		}
	}

	function o(e) {
		var t = 0,
			n = -1,
			r = [],
			i = !0;
		this.set_indent = function (r) {
			t = e.baseIndentLength + r * e.indent_length, n = r
		}, this.get_character_count = function () {
			return t
		}, this.is_empty = function () {
			return i
		}, this.last = function () {
			return this._empty ? null : r[r.length - 1]
		}, this.push = function (e) {
			r.push(e), t += e.length, i = !1
		}, this.remove_indent = function () {
			n > 0 && (n -= 1, t -= e.indent_length)
		}, this.trim = function () {
			while (this.last() === " ") {
				var e = r.pop();
				t -= 1
			}
			i = r.length === 0
		}, this.toString = function () {
			var t = "";
			return this._empty || (n >= 0 && (t = e.indent_cache[n]), t += r.join("")), t
		}
	}

	function u(e, t) {
		t = t || "", this.indent_cache = [t], this.baseIndentLength = t.length, this.indent_length = e.length;
		var n = [];
		this.baseIndentString = t, this.indent_string = e, this.current_line = null, this.space_before_token = !1, this.get_line_number = function () {
			return n.length
		}, this.add_new_line = function (e) {
			return this.get_line_number() === 1 && this.just_added_newline() ? !1 : e || !this.just_added_newline() ? (this.current_line = new o(this), n.push(this.current_line), !0) : !1
		}, this.add_new_line(!0), this.get_code = function () {
			var e = n.join("\n").replace(/[\r\n\t ]+$/, "");
			return e
		}, this.set_indent = function (e) {
			if (n.length > 1) {
				while (e >= this.indent_cache.length) this.indent_cache.push(this.indent_cache[this.indent_cache.length - 1] + this.indent_string);
				return this.current_line.set_indent(e), !0
			}
			return this.current_line.set_indent(0), !1
		}, this.add_token = function (e) {
			this.add_space_before_token(), this.current_line.push(e)
		}, this.add_space_before_token = function () {
			this.space_before_token && !this.just_added_newline() && this.current_line.push(" "), this.space_before_token = !1
		}, this.remove_redundant_indentation = function (e) {
			if (e.multiline_frame || e.mode === i.ForInitializer || e.mode === i.Conditional) return;
			var t = e.start_line_index,
				r, s = n.length;
			while (t < s) n[t].remove_indent(), t++
		}, this.trim = function (r) {
			r = r === undefined ? !1 : r, this.current_line.trim(e, t);
			while (r && n.length > 1 && this.current_line.is_empty()) n.pop(), this.current_line = n[n.length - 1], this.current_line.trim()
		}, this.just_added_newline = function () {
			return this.current_line.is_empty()
		}, this.just_added_blankline = function () {
			if (this.just_added_newline()) {
				if (n.length === 1) return !0;
				var e = n[n.length - 2];
				return e.is_empty()
			}
			return !1
		}
	}

	function f(r, i, s) {
		function g() {
			var g, b, w = [];
			c = 0, h = "";
			if (v >= m) return ["", "TK_EOF"];
			var E;
			d.length ? E = d[d.length - 1] : E = new a("TK_START_BLOCK", "{");
			var S = r.charAt(v);
			v += 1;
			while (t(S, o)) {
				S === "\n" ? (c += 1, w = []) : c && (S === s ? w.push(s) : S !== "\r" && w.push(" "));
				if (v >= m) return ["", "TK_EOF"];
				S = r.charAt(v), v += 1
			}
			w.length && (h = w.join(""));
			if (u.test(S)) {
				var x = !0,
					T = !0,
					N = u;
				S === "0" && v < m && /[Xx]/.test(r.charAt(v)) ? (x = !1, T = !1, S += r.charAt(v), v += 1, N = /[0123456789abcdefABCDEF]/) : (S = "", v -= 1);
				while (v < m && N.test(r.charAt(v))) S += r.charAt(v), v += 1, x && v < m && r.charAt(v) === "." && (S += r.charAt(v), v += 1, x = !1), T && v < m && /[Ee]/.test(r.charAt(v)) && (S += r.charAt(v), v += 1, v < m && /[+-]/.test(r.charAt(v)) && (S += r.charAt(v), v += 1), T = !1, x = !1);
				return [S, "TK_WORD"]
			}
			if (e.isIdentifierStart(r.charCodeAt(v - 1))) {
				if (v < m)
					while (e.isIdentifierChar(r.charCodeAt(v))) {
						S += r.charAt(v), v += 1;
						if (v === m) break
					}
				return !(E.type === "TK_DOT" || E.type === "TK_RESERVED" && t(E.text, ["set", "get"])) && t(S, l) ? S === "in" ? [S, "TK_OPERATOR"] : [S, "TK_RESERVED"] : [S, "TK_WORD"]
			}
			if (S === "(" || S === "[") return [S, "TK_START_EXPR"];
			if (S === ")" || S === "]") return [S, "TK_END_EXPR"];
			if (S === "{") return [S, "TK_START_BLOCK"];
			if (S === "}") return [S, "TK_END_BLOCK"];
			if (S === ";") return [S, "TK_SEMICOLON"];
			if (S === "/") {
				var C = "",
					k = !0;
				if (r.charAt(v) === "*") {
					v += 1;
					if (v < m)
						while (v < m && (r.charAt(v) !== "*" || !r.charAt(v + 1) || r.charAt(v + 1) !== "/")) {
							S = r.charAt(v), C += S;
							if (S === "\n" || S === "\r") k = !1;
							v += 1;
							if (v >= m) break
						}
					return v += 2, k && c === 0 ? ["/*" + C + "*/", "TK_INLINE_COMMENT"] : ["/*" + C + "*/", "TK_BLOCK_COMMENT"]
				}
				if (r.charAt(v) === "/") {
					C = S;
					while (r.charAt(v) !== "\r" && r.charAt(v) !== "\n") {
						C += r.charAt(v), v += 1;
						if (v >= m) break
					}
					return [C, "TK_COMMENT"]
				}
			}
			if (S === "`" || S === "'" || S === '"' || (S === "/" || i.e4x && S === "<" && r.slice(v - 1).match(/^<([-a-zA-Z:0-9_.]+|{[^{}]*}|!\[CDATA\[[\s\S]*?\]\])\s*([-a-zA-Z:0-9_.]+=('[^']*'|"[^"]*"|{[^{}]*})\s*)*\/?\s*>/)) && (E.type === "TK_RESERVED" && t(E.text, ["return", "case", "throw", "else", "do", "typeof", "yield"]) || E.type === "TK_END_EXPR" && E.text === ")" && E.parent && E.parent.type === "TK_RESERVED" && t(E.parent.text, ["if", "while", "for"]) || t(E.type, ["TK_COMMENT", "TK_START_EXPR", "TK_START_BLOCK", "TK_END_BLOCK", "TK_OPERATOR", "TK_EQUALS", "TK_EOF", "TK_SEMICOLON", "TK_COMMA"]))) {
				var L = S,
					A = !1,
					O = !1;
				b = S;
				if (L === "/") {
					var M = !1;
					while (v < m && (A || M || r.charAt(v) !== L) && !e.newline.test(r.charAt(v))) b += r.charAt(v), A ? A = !1 : (A = r.charAt(v) === "\\", r.charAt(v) === "[" ? M = !0 : r.charAt(v) === "]" && (M = !1)), v += 1
				} else if (i.e4x && L === "<") {
					var _ = /<(\/?)([-a-zA-Z:0-9_.]+|{[^{}]*}|!\[CDATA\[[\s\S]*?\]\])\s*([-a-zA-Z:0-9_.]+=('[^']*'|"[^"]*"|{[^{}]*})\s*)*(\/?)\s*>/g,
						D = r.slice(v - 1),
						P = _.exec(D);
					if (P && P.index === 0) {
						var H = P[2],
							B = 0;
						while (P) {
							var j = !!P[1],
								F = P[2],
								I = !!P[P.length - 1] || F.slice(0, 8) === "![CDATA[";
							F === H && !I && (j ? --B : ++B);
							if (B <= 0) break;
							P = _.exec(D)
						}
						var q = P ? P.index + P[0].length : D.length;
						return v += q - 1, [D.slice(0, q), "TK_STRING"]
					}
				} else
					while (v < m && (A || r.charAt(v) !== L && (L === "`" || !e.newline.test(r.charAt(v))))) {
						b += r.charAt(v);
						if (A) {
							if (r.charAt(v) === "x" || r.charAt(v) === "u") O = !0;
							A = !1
						} else A = r.charAt(v) === "\\";
						v += 1
					}
				O && i.unescape_strings && (b = y(b));
				if (v < m && r.charAt(v) === L) {
					b += L, v += 1;
					if (L === "/")
						while (v < m && e.isIdentifierStart(r.charCodeAt(v))) b += r.charAt(v), v += 1
				}
				return [b, "TK_STRING"]
			}
			if (S === "#") {
				if (d.length === 0 && r.charAt(v) === "!") {
					b = S;
					while (v < m && S !== "\n") S = r.charAt(v), b += S, v += 1;
					return [n(b) + "\n", "TK_UNKNOWN"]
				}
				var R = "#";
				if (v < m && u.test(r.charAt(v))) {
					do S = r.charAt(v), R += S, v += 1; while (v < m && S !== "#" && S !== "=");
					return S !== "#" && (r.charAt(v) === "[" && r.charAt(v + 1) === "]" ? (R += "[]", v += 2) : r.charAt(v) === "{" && r.charAt(v + 1) === "}" && (R += "{}", v += 2)), [R, "TK_WORD"]
				}
			}
			if (S === "<" && r.substring(v - 1, v + 3) === "<!--") {
				v += 3, S = "<!--";
				while (r.charAt(v) !== "\n" && v < m) S += r.charAt(v), v++;
				return p = !0, [S, "TK_COMMENT"]
			}
			if (S === "-" && p && r.substring(v - 1, v + 2) === "-->") return p = !1, v += 2, ["-->", "TK_COMMENT"];
			if (S === ".") return [S, "TK_DOT"];
			if (t(S, f)) {
				while (v < m && t(S + r.charAt(v), f)) {
					S += r.charAt(v), v += 1;
					if (v >= m) break
				}
				return S === "," ? [S, "TK_COMMA"] : S === "=" ? [S, "TK_EQUALS"] : [S, "TK_OPERATOR"]
			}
			return [S, "TK_UNKNOWN"]
		}

		function y(e) {
			var t = !1,
				n = "",
				r = 0,
				i = "",
				s = 0,
				o;
			while (t || r < e.length) {
				o = e.charAt(r), r++;
				if (t) {
					t = !1;
					if (o === "x") i = e.substr(r, 2), r += 2;
					else {
						if (o !== "u") {
							n += "\\" + o;
							continue
						}
						i = e.substr(r, 4), r += 4
					}
					if (!i.match(/^[0123456789abcdefABCDEF]+$/)) return e;
					s = parseInt(i, 16);
					if (s >= 0 && s < 32) {
						o === "x" ? n += "\\x" + i : n += "\\u" + i;
						continue
					}
					if (s === 34 || s === 39 || s === 92) n += "\\" + String.fromCharCode(s);
					else {
						if (o === "x" && s > 126 && s <= 255) return e;
						n += String.fromCharCode(s)
					}
				} else o === "\\" ? t = !0 : n += o
			}
			return n
		}
		var o = "\n\r	 ".split(""),
			u = /[0-9]/,
			f = "+ - * / % & ++ -- = += -= *= /= %= == === != !== > < >= <= >> << >>> >>>= >>= <<= && &= | || ! ~ , : ? ^ ^= |= :: => <%= <% %> <?= <? ?>".split(" ");
		this.line_starters = "continue,try,throw,return,var,let,const,if,switch,case,default,for,while,break,function,yield,import,export".split(",");
		var l = this.line_starters.concat(["do", "in", "else", "get", "set", "new", "catch", "finally", "typeof"]),
			c, h, p, d, v, m;
		this.tokenize = function () {
			m = r.length, v = 0, p = !1, d = [];
			var e, t, n, i = null,
				s = [],
				o = [];
			while (!t || t.type !== "TK_EOF") {
				n = g(), e = new a(n[1], n[0], c, h);
				while (e.type === "TK_INLINE_COMMENT" || e.type === "TK_COMMENT" || e.type === "TK_BLOCK_COMMENT" || e.type === "TK_UNKNOWN") o.push(e), n = g(), e = new a(n[1], n[0], c, h);
				o.length && (e.comments_before = o, o = []), e.type === "TK_START_BLOCK" || e.type === "TK_START_EXPR" ? (e.parent = t, i = e, s.push(e)) : (e.type === "TK_END_BLOCK" || e.type === "TK_END_EXPR") && i && (e.text === "]" && i.text === "[" || e.text === ")" && i.text === "(" || e.text === "}" && i.text === "}") && (e.parent = i.parent, i = s.pop()), d.push(e), t = e
			}
			return d
		}
	}
	var e = {};
	(function (e) {
		var t = /[\u1680\u180e\u2000-\u200a\u202f\u205f\u3000\ufeff]/,
			n = "ªµºÀ-ÖØ-öø-ˁˆ-ˑˠ-ˤˬˮͰ-ʹͶͷͺ-ͽΆΈ-ΊΌΎ-ΡΣ-ϵϷ-ҁҊ-ԧԱ-Ֆՙա-ևא-תװ-ײؠ-يٮٯٱ-ۓەۥۦۮۯۺ-ۼۿܐܒ-ܯݍ-ޥޱߊ-ߪߴߵߺࠀ-ࠕࠚࠤࠨࡀ-ࡘࢠࢢ-ࢬऄ-हऽॐक़-ॡॱ-ॷॹ-ॿঅ-ঌএঐও-নপ-রলশ-হঽৎড়ঢ়য়-ৡৰৱਅ-ਊਏਐਓ-ਨਪ-ਰਲਲ਼ਵਸ਼ਸਹਖ਼-ੜਫ਼ੲ-ੴઅ-ઍએ-ઑઓ-નપ-રલળવ-હઽૐૠૡଅ-ଌଏଐଓ-ନପ-ରଲଳଵ-ହଽଡ଼ଢ଼ୟ-ୡୱஃஅ-ஊஎ-ஐஒ-கஙசஜஞடணதந-பம-ஹௐఅ-ఌఎ-ఐఒ-నప-ళవ-హఽౘౙౠౡಅ-ಌಎ-ಐಒ-ನಪ-ಳವ-ಹಽೞೠೡೱೲഅ-ഌഎ-ഐഒ-ഺഽൎൠൡൺ-ൿඅ-ඖක-නඳ-රලව-ෆก-ะาำเ-ๆກຂຄງຈຊຍດ-ທນ-ຟມ-ຣລວສຫອ-ະາຳຽເ-ໄໆໜ-ໟༀཀ-ཇཉ-ཬྈ-ྌက-ဪဿၐ-ၕၚ-ၝၡၥၦၮ-ၰၵ-ႁႎႠ-ჅჇჍა-ჺჼ-ቈቊ-ቍቐ-ቖቘቚ-ቝበ-ኈኊ-ኍነ-ኰኲ-ኵኸ-ኾዀዂ-ዅወ-ዖዘ-ጐጒ-ጕጘ-ፚᎀ-ᎏᎠ-Ᏼᐁ-ᙬᙯ-ᙿᚁ-ᚚᚠ-ᛪᛮ-ᛰᜀ-ᜌᜎ-ᜑᜠ-ᜱᝀ-ᝑᝠ-ᝬᝮ-ᝰក-ឳៗៜᠠ-ᡷᢀ-ᢨᢪᢰ-ᣵᤀ-ᤜᥐ-ᥭᥰ-ᥴᦀ-ᦫᧁ-ᧇᨀ-ᨖᨠ-ᩔᪧᬅ-ᬳᭅ-ᭋᮃ-ᮠᮮᮯᮺ-ᯥᰀ-ᰣᱍ-ᱏᱚ-ᱽᳩ-ᳬᳮ-ᳱᳵᳶᴀ-ᶿḀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼιῂ-ῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲ-ῴῶ-ῼⁱⁿₐ-ₜℂℇℊ-ℓℕℙ-ℝℤΩℨK-ℭℯ-ℹℼ-ℿⅅ-ⅉⅎⅠ-ↈⰀ-Ⱞⰰ-ⱞⱠ-ⳤⳫ-ⳮⳲⳳⴀ-ⴥⴧⴭⴰ-ⵧⵯⶀ-ⶖⶠ-ⶦⶨ-ⶮⶰ-ⶶⶸ-ⶾⷀ-ⷆⷈ-ⷎⷐ-ⷖⷘ-ⷞⸯ々-〇〡-〩〱-〵〸-〼ぁ-ゖゝ-ゟァ-ヺー-ヿㄅ-ㄭㄱ-ㆎㆠ-ㆺㇰ-ㇿ㐀-䶵一-鿌ꀀ-ꒌꓐ-ꓽꔀ-ꘌꘐ-ꘟꘪꘫꙀ-ꙮꙿ-ꚗꚠ-ꛯꜗ-ꜟꜢ-ꞈꞋ-ꞎꞐ-ꞓꞠ-Ɦꟸ-ꠁꠃ-ꠅꠇ-ꠊꠌ-ꠢꡀ-ꡳꢂ-ꢳꣲ-ꣷꣻꤊ-ꤥꤰ-ꥆꥠ-ꥼꦄ-ꦲꧏꨀ-ꨨꩀ-ꩂꩄ-ꩋꩠ-ꩶꩺꪀ-ꪯꪱꪵꪶꪹ-ꪽꫀꫂꫛ-ꫝꫠ-ꫪꫲ-ꫴꬁ-ꬆꬉ-ꬎꬑ-ꬖꬠ-ꬦꬨ-ꬮꯀ-ꯢ가-힣ힰ-ퟆퟋ-ퟻ豈-舘並-龎ﬀ-ﬆﬓ-ﬗיִײַ-ﬨשׁ-זּטּ-לּמּנּסּףּפּצּ-ﮱﯓ-ﴽﵐ-ﶏﶒ-ﷇﷰ-ﷻﹰ-ﹴﹶ-ﻼＡ-Ｚａ-ｚｦ-ﾾￂ-ￇￊ-ￏￒ-ￗￚ-ￜ",
			r = "̀-ͯ҃-֑҇-ׇֽֿׁׂׅׄؐ-ؚؠ-ىٲ-ۓۧ-ۨۻ-ۼܰ-݊ࠀ-ࠔࠛ-ࠣࠥ-ࠧࠩ-࠭ࡀ-ࡗࣤ-ࣾऀ-ःऺ-़ा-ॏ॑-ॗॢ-ॣ०-९ঁ-ঃ়া-ৄেৈৗয়-ৠਁ-ਃ਼ਾ-ੂੇੈੋ-੍ੑ੦-ੱੵઁ-ઃ઼ા-ૅે-ૉો-્ૢ-ૣ૦-૯ଁ-ଃ଼ା-ୄେୈୋ-୍ୖୗୟ-ୠ୦-୯ஂா-ூெ-ைொ-்ௗ௦-௯ఁ-ఃె-ైొ-్ౕౖౢ-ౣ౦-౯ಂಃ಼ಾ-ೄೆ-ೈೊ-್ೕೖೢ-ೣ೦-೯ംഃെ-ൈൗൢ-ൣ൦-൯ංඃ්ා-ුූෘ-ෟෲෳิ-ฺเ-ๅ๐-๙ິ-ູ່-ໍ໐-໙༘༙༠-༩༹༵༷ཁ-ཇཱ-྄྆-྇ྍ-ྗྙ-ྼ࿆က-ဩ၀-၉ၧ-ၭၱ-ၴႂ-ႍႏ-ႝ፝-፟ᜎ-ᜐᜠ-ᜰᝀ-ᝐᝲᝳក-ឲ៝០-៩᠋-᠍᠐-᠙ᤠ-ᤫᤰ-᤻ᥑ-ᥭᦰ-ᧀᧈ-ᧉ᧐-᧙ᨀ-ᨕᨠ-ᩓ᩠-᩿᩼-᪉᪐-᪙ᭆ-ᭋ᭐-᭙᭫-᭳᮰-᮹᯦-᯳ᰀ-ᰢ᱀-᱉ᱛ-ᱽ᳐-᳒ᴀ-ᶾḁ-ἕ‌‍‿⁀⁔⃐-⃥⃜⃡-⃰ⶁ-ⶖⷠ-ⷿ〡-〨゙゚Ꙁ-ꙭꙴ-꙽ꚟ꛰-꛱ꟸ-ꠀ꠆ꠋꠣ-ꠧꢀ-ꢁꢴ-꣄꣐-꣙ꣳ-ꣷ꤀-꤉ꤦ-꤭ꤰ-ꥅꦀ-ꦃ꦳-꧀ꨀ-ꨧꩀ-ꩁꩌ-ꩍ꩐-꩙ꩻꫠ-ꫩꫲ-ꫳꯀ-ꯡ꯬꯭꯰-꯹ﬠ-ﬨ︀-️︠-︦︳︴﹍-﹏０-９＿",
			i = new RegExp("[" + n + "]"),
			s = new RegExp("[" + n + r + "]"),
			o = e.newline = /[\n\r\u2028\u2029]/,
			u = /\r\n|[\n\r\u2028\u2029]/g,
			a = e.isIdentifierStart = function (e) {
				return e < 65 ? e === 36 : e < 91 ? !0 : e < 97 ? e === 95 : e < 123 ? !0 : e >= 170 && i.test(String.fromCharCode(e))
			},
			f = e.isIdentifierChar = function (e) {
				return e < 48 ? e === 36 : e < 58 ? !0 : e < 65 ? !1 : e < 91 ? !0 : e < 97 ? e === 95 : e < 123 ? !0 : e >= 170 && s.test(String.fromCharCode(e))
			}
	})(e);
	var i = {
			BlockStatement: "BlockStatement",
			Statement: "Statement",
			ObjectLiteral: "ObjectLiteral",
			ArrayLiteral: "ArrayLiteral",
			ForInitializer: "ForInitializer",
			Conditional: "Conditional",
			Expression: "Expression"
		},
		a = function (e, t, n, r, i, s) {
			this.type = e, this.text = t, this.comments_before = [], this.newlines = n || 0, this.wanted_newline = n > 0, this.whitespace_before = r || "", this.parent = null
		};
	typeof define == "function" && define.amd ? define([], function () {
		return {
			js_beautify: r
		}
	}) : typeof exports != "undefined" ? exports.js_beautify = r : typeof window != "undefined" ? window.js_beautify = r : typeof global != "undefined" && (global.js_beautify = r)
}();
var OurJS = window.OurJS || {};
OurJS.Util = function (e) {
	$.messager.model = {
		ok: {
			text: "确定"
		},
		cancel: {
			text: "取消"
		}
	}, $.ajaxSetup({
		cache: !1
	});
	var t = function (e, t) {
		if (!e) return "";
		var n = function (e) {
				return e < 10 ? "0" + e : e
			},
			r = new Date(parseInt(e)),
			i = r.getFullYear(),
			s = r.getMonth() + 1,
			o = r.getDate(),
			u = r.getHours(),
			a = r.getMinutes(),
			f = r.getSeconds();
		r = i + "-" + n(s) + "-" + n(o);
		var l = n(u) + ":" + n(a) + ":" + n(f);
		return t ? r : r + " " + l
	};
	$(".formatdate").each(function () {
		var e = $(this),
			n = parseInt(e.text()),
			r = (new Date - n) / 1e3 | 0,
			i = e.hasClass("date"),
			s;
		r < 0 || i ? s = t(n, i) : r < 60 ? s = r + "秒前" : r < 3600 ? s = parseInt(r / 60) + "分钟前" : r < 86400 ? s = parseInt(r / 3600) + "小时前" : r < 259200 ? s = parseInt(r / 86400) + "天前" : s = t(n, i), e.text(s)
	}), $(".formaturl").each(function () {
		var e = $(this),
			t = e.text().toLowerCase().trim();
		if (!t || t.indexOf("http") != 0) return;
		if (e.tagName != "A") {
			var n = $("<a></a>");
			e.html(n), e = n
		}
		e.attr({
			href: t,
			target: "_blank"
		}), t = t.replace("http://www.", ""), t = t.replace("http://", ""), t = t.replace("https://www.", ""), t = t.replace("https://", "");
		var r = t.indexOf("/");
		r = r > 0 ? r : t.length, e.text(t.substr(0, r))
	}), $(".tabswitch").on("click", "li", function (e) {
		var t = $(this),
			r = $(e.delegateTarget),
			i = r.data("tabs"),
			s = t.data("tab"),
			o = t.attr("id");
		e.preventDefault();
		if (t.hasClass("active")) return;
		r.find("li.active").removeClass("active"), t.addClass("active");
		if (s && i) {
			$(i).removeClass("active");
			var u = $(s).addClass("active");
			n(u)
		}
		o == "tabReorder" ? ($("#airWrap").sortable({
			stop: function (e, t) {
				$(document).trigger("toolbar.anchors")
			}
		}), $("body").addClass("sortpg").data("disable-toolbar", !0)) : o == "tabDesign" && ($("#airWrap").sortable("destroy"), $("body").removeClass("sortpg").data("disable-toolbar", !1))
	});
	var n = function (e) {
			e.find("[data-src]").each(function () {
				var e = $(this),
					t = e.attr("data-src");
				this.tagName == "IMG" ? e.attr("src", t) : e.css("background-image", 'url("' + t + '")'), e.removeAttr("data-src"), !e.attr("data-url") && e.attr("data-url", t)
			})
		},
		r = $("body"),
		i = function (e) {
			e = e || r;
			var t;
			t = e.find(".an-animated:not(.an-infinite):visible").add(e), t.each(function () {
				var e = this,
					t = $(e);
				e.className = e.className.replace(/ an-/g, " ant-");
				var n = JSON.parse(t.attr("data-animation") || "{}");
				for (var r in n) {
					var i = n[r];
					t.css(r, i).css("-webkit-" + r, i)
				}
			}), setTimeout(function () {
				t.each(function () {
					var e = this;
					e.className = e.className.replace(/ ant-/g, " an-")
				})
			}, 10)
		},
		s = function (e, t) {
			var n = e.getBoundingClientRect(),
				r = t.getBoundingClientRect(),
				i = !(n.right < r.left || n.left > r.right || n.bottom < r.top || n.top > r.bottom);
			return i
		},
		o = function () {
			$(document).on("slides.switch", function (e, t) {
				OurJS.Util.resetAnimate(t || $(".slide-wrapper .slide.active"))
			})
		};
	return o(), {
		formatDateTime: t,
		loadImage: n,
		resetAnimate: i,
		isOverlapping: s
	}
}(window);
var OurJS = window.OurJS || {};
OurJS.AnimationEditor = function (e, t) {
	if ($(".tool-container").size() < 1) return;
	$(t).on("toolbar.show", function (e) {
		i($(e.target))
	}), $(".tool-container .btn-animation").on("click", function () {
		var e = $(this),
			t = e.closest(".tool-container");
		t.addClass("edit-animation")
	}), $(".tool-animation .btn-cancel").on("click", function () {
		var e = $(this),
			t = e.closest(".tool-container");
		t.removeClass("edit-animation")
	});
	var n = function () {
		var e = $(this),
			t = e.closest(".tool-container"),
			n = t.find(".tool-animation-css select").val(),
			r = t.find(".tool-animation-loop input").is(":checked") ? "an-infinite" : "",
			i = t.data("toolbarObj"),
			s = i.$elem;
		s[0].className = s[0].className.replace(/\ an-[\w]+/g, ""), n && s.addClass("an-animated").addClass("an-" + n).addClass(r)
	};
	$(".tool-animation-css select").on("change", n), $(".tool-animation-loop input").on("change", n);
	var r = function (e, t) {
		var n = $(this).closest(".tool-container"),
			r = n.data("toolbarObj"),
			i = n.find(".animation-delay-range"),
			s = n.find(".animation-delay-value"),
			o = n.find(".animation-duration-range"),
			u = n.find(".animation-duration-value");
		if (i.size() < 1 || o.size() < 1) return;
		var a = i.slider("value"),
			f = o.slider("value");
		s.val(a), u.val(f);
		var l = JSON.parse(r.$elem.attr("data-animation") || "{}");
		l["animation-delay"] = a + "s", l["animation-duration"] = f + "s", r.$elem.attr("data-animation", JSON.stringify(l))
	};
	$(".animation-delay-range").slider({
		range: "min",
		value: 0,
		min: 0,
		max: 20,
		step: .1,
		slide: r
	}), $(".animation-duration-range").slider({
		range: "min",
		value: 1,
		min: 0,
		max: 20,
		step: .1,
		slide: r
	}), $(".animation-delay-value, .animation-duration-value").on("keydown", function (e) {
		if (e.keyCode != 13) return;
		var t = $(this).closest(".tool-container"),
			n = t.find(".animation-delay-range"),
			i = t.find(".animation-delay-value"),
			s = t.find(".animation-duration-range"),
			o = t.find(".animation-duration-value");
		n.slider("value", parseFloat(i.val()) || 0), s.slider("value", parseFloat(o.val()) || 0), r.call(this)
	});
	var i = function (e) {
		var t = e.find(".tool-animation-css select"),
			n = e.find(".tool-animation-loop input"),
			i = e.find(".animation-delay-range"),
			s = e.find(".animation-duration-range"),
			o = e.data("toolbarObj"),
			u = o.$elem,
			a = u.attr("class") || "";
		u.hasClass("an-infinite") ? n.attr("checked", !0) : n.removeAttr("checked");
		var f = "";
		a.replace("an-animated", "").replace("an-infinite", "").replace(/\ an-[\w]+/g, function (e) {
			f = e.substr(4)
		}), t.val(f);
		var l = JSON.parse(u.attr("data-animation") || "{}");
		i.slider("value", parseFloat(l["animation-delay"]) || 0), s.slider("value", parseFloat(l["animation-duration"]) || 1), r.call(t[0])
	};
	return {
		setAnimationView: i
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.TextEditor = function (e, t) {
	function n() {
		if (e.getSelection) {
			sel = e.getSelection();
			if (sel.getRangeAt && sel.rangeCount) {
				var n = [];
				for (var r = 0, i = sel.rangeCount; r < i; ++r) n.push(sel.getRangeAt(r));
				return n
			}
		} else if (t.selection && t.selection.createRange) return t.selection.createRange();
		return null
	}

	function r(r) {
		!r && (r = n());
		if (r)
			if (e.getSelection && r.length)
				for (var i = 0; i < r.length; i++) {
					var s = r[0];
					if (s.startOffset != s.endOffset) return !0
				} else if (t.selection) return r.boundingWidth > 0;
		return !1
	}

	function i(n) {
		if (n)
			if (e.getSelection) {
				sel = e.getSelection(), sel.removeAllRanges();
				for (var r = 0, i = n.length; r < i; ++r) sel.addRange(n[r])
			} else t.selection && n.select && n.select()
	}
	if ($(".tool-container").size() < 1) return;
	var s = $("#toolbar-text").toolbar({
			content: ".content-text",
			position: "top"
		}),
		o = s.data("toolbarObj");
	s.on("toolbar.show", function (e) {
		u($(e.target))
	}).on("toolbar.hide", function () {
		o.$elem.removeAttr("contenteditable"), m.hide(), e.getSelection ? e.getSelection().removeAllRanges() : t.selection && t.selection.empty(), f = null
	});
	var u = function (e) {
		var t = e.data("toolbarObj"),
			n = t.$elem,
			r = e.find(".font-range"),
			i = e.find(".font-size"),
			s = parseInt(n.css("font-size")) || 12;
		n.attr("contenteditable", !0), n.hasClass("no-text") && (n.removeClass("no-text"), n.html("")), r.slider("option", "value", s), i.val(), n[0].className.indexOf("an-") > -1 ? e.find(".btn-animation").addClass("active") : e.find(".btn-animation").removeClass("active")
	};
	$(".tool-container .btn-bold").click(function () {
		if (r()) t.execCommand("bold", !1, null);
		else {
			var e = o.$elem,
				n = e.css("font-weight");
			e.css("font-weight", n == "bold" ? "normal" : "bold")
		}
	}), $(".tool-container .btn-italic").click(function () {
		if (r()) t.execCommand("italic", !1, null);
		else {
			var e = o.$elem,
				n = e.css("font-style");
			e.css("font-style", n == "italic" ? "normal" : "italic")
		}
	}), $(".tool-container .btn-underline").click(function () {
		if (r()) t.execCommand("underline", !1, null);
		else {
			var e = o.$elem,
				n = e.css("text-decoration");
			e.css("text-decoration", n.indexOf("underline") > -1 ? "inherit" : "underline")
		}
	}), $(".tool-container .btn-strike").click(function () {
		if (r()) t.execCommand("strikeThrough", !1, null);
		else {
			var e = o.$elem,
				n = e.css("text-decoration");
			e.css("text-decoration", n.indexOf("line-through") > -1 ? "inherit" : "line-through")
		}
	});
	var a = $(".tool-container .btn-format .btn-text");
	$(".tool-container .btn-format").click(function (e) {
		var n = $(e.target),
			r = n.data("cmd");
		r && (t.execCommand("formatBlock", !1, "<" + r + ">"), r != a.data("cmd") && a.text(n.text()).data("cmd", r))
	}), $(".tool-container .btn-left").click(function () {
		o.$elem.css("text-align", "left")
	}), $(".tool-container .btn-center").click(function () {
		o.$elem.css("text-align", "center")
	}), $(".tool-container .btn-right").click(function () {
		o.$elem.css("text-align", "right")
	}), $(".tool-container .btn-list").click(function () {
		t.execCommand("insertUnorderedList", !1, null)
	}), $(".tool-container .btn-list-order").click(function () {
		t.execCommand("insertOrderedList", !1, null)
	});
	var f, l = $(".anchor-url"),
		c = $(".anchor-hash").addClass("disabled"),
		h = function () {
			var e;
			l.hasClass("disabled") ? e = c.val() : e = l.val(), f || (f = n());
			var r = f[0],
				s;
			if (r && r.startContainer && r.startContainer == r.endContainer) s = $(r.startContainer).closest("a");
			else if (f.parentElement) {
				var o = f.parentElement();
				s = $(o), o.tagName != "A" && (s = s.closest("a"))
			}
			s && s.size() ? s.attr("href", e) : (i(f), t.execCommand("createLink", !1, e)), f = null
		};
	l.mousedown(function () {
		if (!l.hasClass("disabled")) return;
		l.removeClass("disabled"), c.addClass("disabled")
	}), c.mousedown(function () {
		if (!c.hasClass("disabled")) return;
		l.addClass("disabled"), c.removeClass("disabled")
	}), $(".tool-container .btn-anchor").click(function () {
		f = n(), s.addClass("edit-anchor"), l.focus()
	}), $(".tool-anchor .btn-ok", s).click(h), $(".tool-anchor .btn-cancel", s).click(function () {
		s.removeClass("edit-anchor")
	}), l.on("keyup", function (e) {
		if (e.altKey || e.ctrlKey) return;
		e.which === 13 && h()
	}).on("focus, mousedown", function (e) {
		f || (f = n())
	}), c.on("focus, mousedown", function (e) {
		f || (f = n())
	});
	var p = c.data("anchors");
	$(t).on("toolbar.anchors", function () {
		var e = $(p),
			t = "";
		e.each(function (e) {
			var n = $(this),
				r = n.attr("id");
			if (!r) return;
			var i = '<option value="#{0}">第{2}部分( {1}... )</option>'.format(r, n.text().substr(0, 24), e);
			t += i
		}), c.html(t)
	}).trigger("toolbar.anchors"), $(".tool-container .btn-clear").click(function () {
		t.execCommand("removeFormat", !1, !1), $(".tool-container .btn-font-remove").trigger("click")
	});
	var d = function (e) {
			f = n()
		},
		v = function (e, s) {
			var u = $(e.target).closest(".btn-color-back").size() ? "backColor" : "foreColor";
			f || (f = n()), i(f);
			if (r()) t.execCommand(u, !1, s);
			else {
				var a = o.$elem,
					l = a.css("font-style"),
					c = u == "backColor" ? "background-color" : "color";
				a.css(c, s)
			}
			f = null, $(".active .design-content").focus()
		};
	$(".tool-container .btn-color, .tool-container .btn-color-back").on("mousedown", d).on("color", v);
	var m = $(".font-range-wrapper").hide();
	$(".font-range").slider({
		range: "min",
		value: 12,
		min: 4,
		max: 128,
		slide: function (e, t) {
			var n = $(this).closest(".tool-container"),
				r = t.value || 14;
			n.find("input.font-size").val(r), n.data("toolbarObj").$elem.css("font-size", r + "px")
		}
	}), $(".tool-container .btn-font-remove").click(function () {
		var e = $(this).closest(".tool-container");
		e.data("toolbarObj").$elem.css("font-size", ""), g()
	});
	var g = function () {
		m.is(":visible") && m.hide()
	};
	return $(".tool-container .btn-font-size input").on("click", function () {
		m.toggle()
	}), $(".font-range-wrapper").on("mouseup", g), $(".tool-container .btn-del").click(function (e) {
		var t = $(e.target).closest(".tool-container").data("toolbarObj"),
			n = t.$elem;
		t.hide(), n.closest(".design-element").remove(), n.closest(".design-content").remove()
	}), {
		saveSelection: n,
		restoreSelection: i,
		hasSelection: r,
		setTextView: u
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.ImageEditor = function (e, t) {
	if ($(".tool-container").size() < 1) return;
	var n = $("#toolbar-image").toolbar({
			content: ".content-image",
			position: "top"
		}),
		r = n.data("toolbarObj");
	$(t).on("toolbar.show", function (e, t) {
		var n = $(e.target).closest(".tool-container"),
			r = n.data("toolbarObj"),
			i = r.$elem,
			s = i.hasClass("content-background") || i[0].tagName != "IMG";
		s ? n.addClass("background-image") : n.removeClass("background-image");
		var o = s ? i.css("background-image").replace("url(", "").replace(")", "").replace(/['"]/g, "") : i.attr("src");
		o == "none" && (o = ""), n.find(".tool-url input").val(o).attr("placeholder", s ? "http://背景图片地址" : "http://图片地址");
		var u = i.css("background-size"),
			a = i.css("background-repeat"),
			f = i.css("background-position");
		n.find(".background-size").val(u), n.find(".background-repeat").val(a), n.find(".background-position").val(f)
	}).on("toolbar.hide", function () {}).on("color", function (e) {
		var t = r.$elem;
		t.css("background-image", "none")
	});
	var i = [".jpeg", ".jpg", ".png", ".gif"],
		s = $(".progressbar .progress"),
		o = $(".tool-container .btn-upload input"),
		u = $(".tool-container .tool-url input"),
		a = function (e, t) {},
		f = function (e) {
			var t = u.val(),
				n = $(this).closest(".tool-container"),
				r = n.data("toolbarObj"),
				i = r.$elem;
			if (i.data("toolbar.event")) {
				i.trigger("toolbar.image", [{
					file: t
				}]);
				return
			}
			if (i.hasClass("content-background") || i[0].tagName != "IMG")
				if (t) {
					var s = {
							"background-image": 'url("' + t + '")'
						},
						o = n.find(".background-size").val(),
						a = n.find(".background-repeat").val(),
						f = n.find(".background-position").val();
					o && (s["background-size"] = o), a && (s["background-repeat"] = a), f && (s["background-position"] = f), i.css(s)
				} else i.css({
					"background-image": "none"
				});
			else i.attr("src", "").attr("src", t)
		},
		l = function (e) {
			var t = $(this).closest(".tool-container"),
				n = t.data("toolbarObj"),
				r = n.$elem;
			if (r.data("toolbar.event")) {
				r.trigger("toolbar.image", [{
					file: ""
				}]);
				return
			}
			r[0].tagName != "IMG" ? r.css({
				background: "none"
			}) : r.attr("src", ""), t.find(".tool-url input").val("")
		};
	$(".tool-container .background-size").on("change", f), $(".tool-container .background-repeat").on("change", f), $(".tool-container .background-position").on("change", f);
	var c = $("#toolbar-image .btn-bg-color").on("color", function (e, t) {
			var n = r.$elem;
			if (n.data("toolbar.event")) {
				n.trigger("toolbar.image", [{
					color: t
				}]);
				return
			}
			r.$elem.css({
				"background-color": t
			})
		}),
		h = $("#toolbar-image form").ajaxForm({
			beforeSend: function () {
				s.css("width", "0%"), s.animate({
					width: "14%"
				})
			},
			uploadProgress: function (e, t, n, r) {
				s.animate({
					width: r + "%"
				})
			},
			complete: function (e) {
				var t = {};
				try {
					t = JSON.parse(e.responseText)
				} catch (n) {
					t.error = e.responseText
				}
				t.error ? $.messager.popup(t.error) : (u.val(t.file), f.apply(h[0]), d()), s.animate({
					width: "100%"
				})
			}
		});
	o.change(function () {
		var e = this.value;
		OurJS.Form.checkExtension(e, i) ? (u.val(e), h.submit()) : $.messager.popup("请选择正确格式的文件")
	}), $(".tool-container .btn-image-add").click(function () {
		o.trigger("click")
	}), $(".tool-container .btn-url-ok").click(f), $(".tool-container .btn-url-remove").click(l);
	var p = function (e) {
		var t = $(this).closest(".tool-container");
		t.toggleClass("active"), OurJS.Util.loadImage($("#toolbar-image .tab-container.active"))
	};
	$(".tool-container .btn-images").click(p), $(".tool-container .btn-image-close").click(p), $(".tool-container .tool-images").on("click", ".tab-container > div", function () {
		var e = $(this),
			t = e.attr("data-url");
		t && ($(".tool-images .tab-container > div.active").removeClass("active"), e.addClass("active"), u.val(t), f.call(this))
	});
	var d = function () {
			$.getJSON("/user/image", function (e, t) {
				if (t != "success" || e.error) {
					$.messager.popup(e.error || "登录后才可显示您的图片");
					return
				}
				var n = "";
				for (var r = 0; r < (e.images || []).length; r++) {
					var i = e.images[r].url;
					n += '<div style="background-image:url(\'{0}\')" data-url="{0}"></div>'.format(i)
				}
				v.html(n)
			})
		},
		v = $(".tab-img-my");
	return $(".btn-img-my").on("click", function () {
		v.find("div").size() < 1 && d()
	}), {
		setBackground: a
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.Editor = function (e, t) {
	if ($(".designpg").size() < 1) return;
	var n = $("#toolbar-iframe").toolbar({
			content: ".content-iframe",
			position: "top"
		}),
		r = n.data("toolbarObj");
	n.on("toolbar.show", function () {
		n.find(".tool-url input").val(r.$elem.find("IFrame").attr("src"))
	}).on("toolbar.hide", function () {}), $("#toolbar-iframe .btn-url-ok").click(function () {
		r.$elem.find("IFrame").attr("src", n.find(".tool-url input").val())
	});
	var i = $("#toolbar-form").toolbar({
			content: ".content-form",
			position: "top"
		}),
		s = i.data("toolbarObj"),
		o = $(".tool-container .btn-input-type .btn-text");
	$(".tool-container .btn-input-type").click(function (e) {
		var t = $(e.target),
			n = t.data("type");
		n && (s.$elem.find("input").attr("type", n), n != o.data("type") && o.text(t.text()).data("type", n))
	}), $(".tool-container .btn-attr").on("keyup", function (e) {
		var t = e.target;
		if (t.tagName != "INPUT") return;
		var n = $(this),
			r = $(t),
			i = n.data("attr");
		s.$elem.find("input").attr(i, r.val())
	}), $(t).on("mousedown", ".ui-draggable input", function (n) {
		var r = t.createEvent("MouseEvents");
		r.initMouseEvent("mousedown", !1, !0, e, 0, n.screenX, n.screenY, n.clientX, n.clientY, !0, !1, !1, !0, 0, null), $(this).closest(".design-element")[0].dispatchEvent(r)
	}), $(t).on("toolbar.show", function (e) {
		var t = $(e.target),
			n = t.data("toolbarObj");
		t.find(".btn-attr").each(function () {
			var e = $(this),
				t = e.data("attr");
			e.find("input").val(n.$elem.find("input").attr(t))
		})
	});
	var u = function (e) {
		var t = 0,
			n = 1e3;
		$(".slide.active .design-element").each(function () {
			var e = $(this),
				r = parseInt(e.css("z-index")) | 0;
			r >= t && (t = r), r <= n && (n = r)
		});
		var r = e ? ++t : --n;
		$(".slide.active .design-element.current").css("z-index", r)
	};
	$(".tool-container .btn-top").click(function () {
		u(!0)
	}), $(".tool-container .btn-bottom").click(function () {
		u(!1)
	});
	var a = $("#toolbar-code").toolbar({
			content: ".content-code",
			position: "top"
		}),
		f = a.data("toolbarObj"),
		l = function () {
			$("pre.content-code").each(function (e, t) {
				hljs.highlightBlock(t)
			})
		};
	l(), a.on("toolbar.show", function () {
		var e = f.$elem;
		e.html(e.data("code")), e.attr("contenteditable", !0)
	}).on("toolbar.hide", function () {
		var e = f.$elem,
			t = e.html();
		e.data("code", t).removeAttr("contenteditable"), setTimeout(function () {
			hljs.highlightBlock(e[0])
		}, 0)
	}), $(".tool-container .btn-format-code").click(function () {
		var e = f.$elem;
		e.attr("class", "design-content content-code hljs toolbar-show"), e.html(js_beautify(e.textEx())), f.hide()
	});
	var c = function (e) {
			var t;
			e.file && (t = '.slide-wrapper .slide{background: url("{0}") 0 0 transparent;}'.format(e.file)), e.color && (t = ".slide-wrapper .slide{background: none {0};}".format(e.color)), OurJS.Designer.setBgCss(t)
		},
		h = $(".slide-menu .btn-del").on("click", function () {
			var e = $(".slide"),
				t = $(".slide.active"),
				n = $(".slide.active").index();
			if (e.size() < 2) {
				$.messager.popup("您至少应保留一个页面");
				return
			}
			$.messager.confirm("确定", "确定删除当前这个页面？", function () {
				var r = n + (e.size() - 1 == n ? -1 : 1);
				e.eq(r).addClass("active"), t.remove(), e.trigger("slides.update"), T()
			})
		}),
		p = $(".slide-wrapper"),
		d = $("#toolbar-image");
	$(".slide-toolbar .btn-current-bg").on("click", function () {
		d.toolbar("show", $(".slide.active"))
	}), $(".slide-btn-group .btn-slide-bg").on("click", function () {
		d.toolbar("show", p)
	}), p.data("toolbar.event", !0).on("toolbar.image", function (e, t) {
		var n = t.file;
		OurJS.ImageEditor.setBackground($(".slide"), n)
	});
	var v = $(".slide-menu .btn-add, .design-plus > div").on("click", function () {
			var e = $(".slide.active"),
				t = $(".slide"),
				n = $('<div class="slide active"></div>');
			n.attr("style", t.eq(t.length - 1).attr("style")).html($(".design-theme.active").find(".html-tmpl").html()), e.removeClass("active").after(n), OurJS.Designer.setDesignable(n), T(), e.trigger("slides.update")
		}),
		m;
	$(".slide-menu .btn-copy").on("click", function () {
		m = $(".slide.active"), $.messager.popup("复制成功")
	}), $(".slide-menu .btn-cut").on("click", function () {
		m = $(".slide.active");
		var e = $(".slide");
		e.size() > 1 && (m.remove(), y.click()), e.trigger("slides.update"), $.messager.popup("剪贴成功")
	}), $(".slide-menu .btn-paste").on("click", function () {
		if (m) {
			var e = $(".slide.active"),
				t = m.clone().removeClass("active");
			OurJS.Designer.setRunnable(t);
			var n = OurJS.Designer.setDesignable(t);
			e.after(n), e.removeClass("active"), n.addClass("active"), T(), n.trigger("slides.update"), $.messager.popup("粘贴成功")
		} else v.click()
	});
	var g = $(".btn-prev"),
		y = $(".btn-next"),
		b = $(".btn-first"),
		w = $(".btn-last"),
		E = $(".navi-cur"),
		S = $(".navi-num"),
		x = "enabled",
		T = function (e) {
			e = e || {};
			if (e.altKey || e.ctrlKey) return;
			var t = $(".slide-wrapper .slide");
			cur = $(".slide-wrapper .slide.active").index(), keyCode = e.keyCode;
			var n = function (n) {
				t.removeClass("active").eq(n).addClass("active"), L.call($(".design-slides .design-slide").eq(n), e)
			};
			if (keyCode == 33) cur > 0 ? n(--cur) : $.messager.popup("己是第一张");
			else if (keyCode == 34) cur < t.size() - 1 ? n(++cur) : $.messager.popup("己是最后一张");
			else if (keyCode == 36) cur != 0 ? (cur = 0, n(cur)) : $.messager.popup("己是第一张");
			else if (keyCode == 35) {
				var r = t.size() - 1;
				cur == r ? $.messager.popup("己是最后一张") : (cur = r, n(cur))
			} else n(cur);
			N(cur)
		},
		N = function (e) {
			var t = $(".slide-wrapper .slide");
			e == 0 ? g.removeClass(x) : g.addClass(x), e == t.size() - 1 ? y.removeClass(x) : y.addClass(x), S.text(t.size()), E.text(e + 1)
		};
	g.on("click", function (e) {
		e.keyCode = 33, T.call(this, e)
	}).trigger("click"), y.on("click", function (e) {
		e.keyCode = 34, T.call(this, e)
	}), b.on("click", function (e) {
		e.keyCode = 36, T.call(this, e)
	}), w.on("click", function (e) {
		e.keyCode = 35, T.call(this, e)
	});
	var C = $(".design-slides"),
		k = function () {
			var e = "";
			$(".slide-wrapper .slide").each(function (t) {
				var n = $(this),
					r = n.text(),
					i = t + 1,
					s = n.hasClass("active") ? "active" : "";
				r.length > 12 && (i ), e += '<div class="design-slide ' + s + '" data-idx="' + t + '">' + i + "</div>"
			}), C.html(e)
		};
	$(t).on("toolbar.hide", k).on("slides.update", k).trigger("slides.update");
	var L = function (e) {
		var t = $(this);
		if (t.size() < 1) return;
		var n = t.data("idx");
		$(".design-slides .design-slide").removeClass("active"), t.addClass("active");
		var r = $(".slide-wrapper .slide").removeClass("active").eq(n).addClass("active");
		r.trigger("slides.switch"), N(n)
	};
	$(".design-slides").on("mousedown", ".design-slide", L);
	var A = $(".design-slides-wrap");
	return C.sortable({
		start: function (e, t) {
			$("body").addClass("sorting-slide")
		},
		beforeDrop: function (e, t) {
			$("body").removeClass("sorting-slide"), OurJS.Util.isOverlapping(t.item[0], A[0]) || setTimeout(function () {
				h.click()
			}, 50)
		},
		stop: function (e, t) {
			var n = t.item,
				r = n.data("idx"),
				i = n.index(),
				s = $(".slide-wrapper .slide").eq(r);
			if (r == i) return;
			s.remove();
			var o = $(".slide-wrapper .slide").eq(i);
			o.size() == 1 ? o.before(s) : $(".slide-wrapper").append(s), OurJS.Designer.setDesignable(s), k()
		}
	}), {
		changeSlideBG: c
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.Form = function (e, t) {
	typeof console == "undefined" && (console = {
		log: function () {},
		error: function () {}
	});
	var n = {},
		r = function (t) {
			var n = $(this),
				r = "json",
				i = "post",
				s = n.attr("action");
			if (!s) return;
			t.preventDefault();
			if (n.valid && !n.valid()) return;
			s.indexOf("/form.page/") > -1 && (r = "jsonp", i = "get");
			var o = n.toJSON();
			$.ajax({
				url: s,
				type: i,
				data: o,
				dataType: r,
				success: function (t) {
					if (t.error) !n.hasClass("no-message") && $.messager.popup(t.error), n.find(".form-error-msg").html(t.error.toString());
					else {
						!n.hasClass("no-message") && $.messager.popup("提交成功"), n.find("input, button, select, textarea").attr("disabled", !0), n.find(".form-error-msg").html("");
						var r = n.attr("data-binding") || n.closest("[data-binding]").attr("data-binding") || "";
						if (r) {
							var i = e[r];
							$.extend(i, o)
						}
					}
					n.trigger("ajax.done", [t])
				},
				error: function () {
					$.messager.popup("请求失改，请检测网络连接"), n.trigger("ajax.error")
				}
			})
		},
		i = function (e, t) {
			if (e) {
				e = e.toLowerCase();
				for (var n = 0; n < t.length; n++) {
					var r = t[n];
					if (e.lastIndexOf(r) == e.length - r.length) return !0
				}
			}
			return !1
		};
	$(t).on("submit", "form.form-auto-submit", r);
	var s = function () {
		$("[data-binding]").each(function () {
			var t = $(this),
				n = e[t.data("binding")];
			n && t.find("[name]").each(function () {
				var e = $(this),
					t = e.attr("name"),
					r = n[t];
				if (typeof r != "undefined") switch ((e.attr("type") || e[0].tagName || "").toLowerCase()) {
				case "checkbox":
					e.each(function () {
						var e = $(this);
						r.indexOf(e.val()) > -1 && e.attr("checked", !0)
					});
					break;
				case "select":
					e.val(r), e[0].selectedIndex == -1 && (e.append($("<option></option>").val(r).html("自定义 (" + r + ")")), e.val(r));
					break;
				default:
					e.val(r)
				}
			})
		})
	};
	s();
	var o = function () {
		$("[data-binding]").each(function () {
			var t = $(this),
				n = e[t.data("binding")];
			n && t.find("[name]").each(function () {
				var e = $(this),
					t = e.attr("name");
				switch ((e.attr("type") || "").toLowerCase()) {
				case "checkbox":
					var r = [];
					e.each(function () {
						var e = $(this);
						e.is(":checked") && r.push(e.val())
					}), n[t] = r.join(",");
					break;
				default:
					n[t] = e.val()
				}
			})
		})
	};
	return $(".slide-form").on("submit", r).on("ajax.done", function () {}).find("[disabled]").removeAttr("disabled"), {
		checkExtension: i,
		setView: s,
		setModel: o
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.uploadPPT = function (e) {
	var t = function () {
		var t = $("#uploadPPTDialog");
		$(e).on("click", ".upload-ppt", function () {
			t.formUpload({
				extensions: [".ppt", ".pptx", ".pdf"],
				title: "上传讲稿文件（PPT、PDF）",
				success: function (t) {
					try {
						var n = JSON.parse(t)
					} catch (r) {
						$.messager.popup("上传失败:" + t);
						return
					}
					n.error ? $.messager.popup(n.error) : ($.messager.popup("上传成功"), $(e).trigger("uploadPPT.done"))
				},
				error: function () {
					$.messager.popup("上传失败")
				}
			})
		})
	};
	t()
}(document);
var OurJS = window.OurJS || {};
OurJS.UserSign = function (e) {
	var t = $("#userSignDialog");
	$.extend($.validator.messages, {
		required: "必选字段",
		remote: "请修正该字段",
		email: "请输入正确格式的电子邮件",
		url: "请输入合法的网址",
		date: "请输入合法的日期",
		dateISO: "请输入合法的日期 (ISO).",
		number: "请输入合法的数字",
		digits: "只能输入整数",
		creditcard: "请输入合法的信用卡号",
		equalTo: "请再次输入相同的值",
		accept: "请输入拥有合法后缀名的字符串",
		maxlength: $.validator.format("请输入一个长度最多是 {0} 的字符串"),
		minlength: $.validator.format("请输入一个长度最少是 {0} 的字符串"),
		rangelength: $.validator.format("请输入一个长度介于 {0} 和 {1} 之间的字符串"),
		range: $.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
		max: $.validator.format("请输入一个最大为 {0} 的值"),
		min: $.validator.format("请输入一个最小为 {0} 的值")
	});
	if (t.size() < 1) return;
	t.find(".btn-confirm").click(function () {
		t.find("form:visible").submit()
	}), t.find(".btn-cancel").click(function () {
		$(".signpg").size() ? t.find("form:visible")[0].reset() : t.hide()
	});
	var n = {
			username: {
				minlength: 4,
				maxlength: 16,
				required: !0
			},
			password: {
				minlength: 4,
				maxlength: 16,
				required: !0
			},
			email: {
				required: !0,
				maxlength: 64,
				email: !0
			}
		},
		r = {
			username: {
				minlength: 4,
				maxlength: 64,
				required: !0
			},
			password: {
				minlength: 4,
				maxlength: 16,
				required: !0
			}
		},
		t = $("#userSignDialog"),
		i = function (t, n) {
			n.error ? $.messager.popup(n.error) : $(e).trigger("sign.done", [n])
		};
	$("#signuptab form").on("ajax.done", i).validate({
		rules: n
	}), $("#signintab form").on("ajax.done", i).validate({
		rules: r
	}), t.find(".nav-tabs li.active").size() < 1 && (t.find(".nav-tabs li").eq(0).addClass("active"), t.find(".tab-pane").eq(0).addClass("active")), $(".signpg").size() ? $(e).on("sign.done", function (e, t) {
		location.href = (t.url || "/") + location.hash
	}) : t.find(".third-login a").on("click", function () {
		t.hide()
	})
}(document);
var OurJS = window.OurJS || {};
OurJS.Designer = function (e) {
	typeof console == "undefined" && (console = {
		log: function () {},
		error: function () {}
	});
	if ($(".designpg").size() < 1) return;
	var t = $("body"),
		n = $(".design-element-container");
	$(".design-element-container .design-element").draggable({
		appendTo: ".slide-wrapper .slide.active",
		helper: "clone"
	});
	var r = function (e) {
		e.droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".tmpl",
			drop: function (e, t) {
				var n = t.helper.clone().removeClass("tmpl").off(),
					r = n.find(".content-form input");
				if (r.size()) {
					var i = r.attr("name") + "_" + (+(new Date) / 2e3 | 0).toString(32);
					r.attr("name", i)
				}
				var s = n.data("width"),
					o = n.data("height");
				s && n.width(s), o && n.height(o), n.appendTo(this).resizable()
			}
		})
	};
	r($(".slide-wrapper .slide")), $(e).on("click", ".slide-wrapper .slide", function (e) {
		e.preventDefault()
	});
	var i, s = CodeMirror.fromTextArea(e.getElementById("editor"), {
		lineNumbers: !0,
		styleActiveLine: !0,
		matchBrackets: !0
	});
	s.setOption("theme", "monokai"), s.getValue(), s.setValue("");
	var o = function () {
		var e = s.getValue();
		if (i == "content" && !e) return;
		i && (slideModel[i] = e)
	};
	s.on("keyHandled", o);
	var u = $("#tabDesign"),
		a = $("#tabPreview"),
		f = $("#tabEditor"),
		l = $("#editor"),
		c = $(".slide-wrapper"),
		h = $(".preview-iframe"),
		p = function (e) {
			return e.hasClass("design-element") && e.resizable(), e.find(".design-element").resizable(), e
		},
		d = function (e) {
			return e.find("pre.design-content").each(function () {
				hljs.highlightBlock(this)
			}), p(e), r(e), e
		},
		v = function (e) {
			e.find(".design-content").each(function () {
				var e = $(this);
				e.css({
					"animation-delay": "",
					"animation-duration": ""
				})
			}), e.find("pre.design-content").each(function () {
				var e = $(this);
				e.html(e.data("code"))
			});
			var t = e.hasClass("design-element") ? e : e.find(".design-element");
			t.removeClass("ui-draggable").removeClass("ui-draggable-handle").removeClass("ui-draggable-dragging").removeClass("ui-resizable").removeClass("current"), e.find(".ui-resizable-handle").remove(), e.find(".slide").removeClass("ui-droppable")
		},
		m = function () {
			if ($(this).hasClass("active")) return;
			o(), t.removeClass("coding").removeClass("previewing"), h.attr("src", "");
			var e = $(slideModel.content);
			d(e), c.html(e), k(slideModel.bgCss), C(slideModel.tmplCss), c.trigger("slides.update").trigger("slides.switch")
		},
		g = function () {
			if ($(this).hasClass("active")) return;
			t.removeClass("coding").addClass("previewing"), setTimeout(function () {
				M(x)
			}, 50)
		},
		y = function () {
			if ($(this).hasClass("active")) return;
			h.attr("src", ""), t.removeClass("previewing").addClass("coding"), $(".slide-editor").height($(window).height() - $(".layout-top").height() - 24), A(), T.eq(0).trigger("click")
		},
		b = function () {
			var e = c.clone();
			return v(e), e.html()
		};
	u.on("mousedown", m), a.on("mousedown", g), f.on("mousedown", y);
	var w = $("#player"),
		E = $("#barcodeUrl"),
		S = new QRCode("barcodeUrl", {
			text: conf.mainSvr,
			width: 240,
			height: 240,
			colorDark: "#000000",
			colorLight: "#ffffff",
			correctLevel: QRCode.CorrectLevel.H
		}),
		x = function () {
			var e = conf.mainSvr + "/" + w.val() + "/" + slideModel._id;
			$(".visit-url").attr("href", e), $(".visit-input").val(e), h.attr("src", e + "/preview"), S.clear(), S.makeCode(e)
		};
	w.on("change", x), $(".btn-mode-desktop").on("mousedown", function () {
		t.data("mode", "").removeClass("mode-wide").removeClass("mode-phone"), slideModel.width = 960, slideModel.height = 720
	}), $(".btn-mode-wide").on("mousedown", function () {
		t.data("mode", "wide").addClass("mode-wide").removeClass("mode-phone"), slideModel.width = 960, slideModel.height = 540
	}), $(".btn-mode-phone").on("mousedown", function () {
		t.data("mode", "phone").removeClass("mode-wide").addClass("mode-phone"), slideModel.width = 480, slideModel.height = 720
	});
	var T = $(".slide-editor .tabsv > button").on("click", function () {
			var e = $(this),
				t = e.data("mode"),
				n;
			o(), i = e.data("field"), t == "xml" && (n = html_beautify), t == "css" && (n = css_beautify), s.setOption("mode", t), s.setValue(n(slideModel[i] || ""))
		}),
		N = function () {
			var e = c.find(".slide");
			e.removeClass("active").eq(0).addClass("active"), d(e), $(".btn-mode-" + (slideModel.mode || "desktop")).mousedown().click()
		};
	N();
	var C = function (e) {
			$("#tmplCss").html("").append($('<style type="text/css" id="tmplCss">{0}</style>'.format(e)))
		},
		k = function (e) {
			$("#bgCss").html("").append($('<style type="text/css">{0}</style>'.format(e)))
		},
		L = function (e) {
			var t = e.find(".css-tmpl").text(),
				n = e.find(".css-bg").text(),
				r = e.find(".html-tmpl").html();
			t && C(t), n && k(n), r && $(".slide-wrapper .slide").each(function () {
				var e = $(this);
				e.html().trim() || (e.html(r), p(e))
			}), $(".design-theme").removeClass("active"), e.addClass("active")
		};
	$(".design-themes").on("click", ".design-theme", function (e) {
		L($(e.currentTarget))
	});
	var A = function () {
			slideModel.mode = t.data("mode") || "", slideModel.content = b(), slideModel.title = $(".headbar .title input").val(), slideModel.tmplCss = $("#tmplCss style").html(), slideModel.bgCss = $("#bgCss style").html(), slideModel.keywords = $("#keywords").magicSuggest().getValue(), OurJS.Form.setModel(), console.log(slideModel)
		},
		O = function () {
			var e = $("<div></div>").append(slideModel.content);
			e.find("[contenteditable]").removeAttr("contenteditable"), e.find(".active").removeClass("active"), e.find(".slide").eq(0).addClass("active"), slideModel.content = e.html()
		},
		M = function (t) {
			A(), O(), $.post(P, JSON.stringify(slideModel), function (n) {
				n.login && _.show();
				if (n.error) $.messager.popup(n.error || "保存失败");
				else {
					var r = n._id;
					r && (slideModel._id = r, history.pushState && history.pushState({}, e.title, "/designer/" + r)), t && t(), $.messager.popup("保存成功")
				}
			}, "json")
		},
		_ = $("#userSignDialog");
	$(e).on("sign.done", function (e, t) {
		t.username && (loginUser.username = t.username), _.hide(), M()
	});
	var D = $("#btnSave").on("click", function (e) {
			e.preventDefault(), M()
		}),
		P = D.attr("href");
	window.keywords.splice(0, 1);
	var H = function () {
		var e = slideModel.keywords || "";
		e ? e = e.split(",") : e = [];
		var t = $("#keywords").magicSuggest({
			placeholder: "请输入",
			data: window.keywords || [],
			value: e
		})
	};
	return H(), $(function () {
		$(".ui-accordion").accordion({
			collapsible: !0
		}), $(".commands li").powerTip({
			placement: "s"
		}), $(".slide-toolbar .buttons .btn").powerTip({
			placement: "e"
		}), $(".slide-btn-group .btn").powerTip({
			placement: "s"
		}), $(".tabswitch > li").powerTip({
			placement: "s"
		}), $(window).bind("beforeunload", function () {
			return "离开本页后，当前内容不会被保存"
		}), $(".btn-music-add").on("click", function () {
			if (!loginUser.username) return $.messager.popup("您还未登录");
			if (!slideModel._id) return $.messager.popup("此幻灯片还未保存过");
			$(".upload-form").formUpload({
				extensions: [".mp3"],
				title: "上传背景音乐",
				success: function (e) {
					try {
						var t = JSON.parse(e)
					} catch (n) {
						$.messager.popup("上传失败:" + e);
						return
					}
					t.error ? $.messager.popup(t.error) : (slideModel.audio = "http://sc1.sand.airjd-int.com:8056/upload/audio/k4/5a/ie9h5ec4000k45a.mp3", OurJS.Form.setView())
				}
			})
		}), $(".slide-menu").contextMenu({
			menuSelector: ".design-slides-wrap",
			menuSelected: function (e, t) {}
		}), $(".slide-design-menu").contextMenu({
			menuSelector: ".slide-wrapper .slide",
			menuSelected: function (e, t) {}
		}), $(".slide").trigger("slides.switch")
	}), {
		setInnerSlideEvent: p,
		setDesignable: d,
		setRunnable: v,
		getSourceCode: b,
		setSlideModel: A,
		setBgCss: k,
		setTmplCss: C
	}
}(document);
var OurJS = window.OurJS || {};
OurJS.Drag = function (e, t) {
	if ($(".designpg").size() < 1) return;
	var n = $(t),
		r, i = function (e) {
			var t = $(e.target),
				n = t.closest(".design-element"),
				i = t.closest(".design-content");
			if (e.shiftKey || e.altKey) return;
			if (i.size() < 1 || t.closest(".toolbar-show").size()) return;
			e.preventDefault(), n.hasClass("current") || ($(".slide-wrapper .design-element.current").removeClass("current"), n.addClass("current")), r = e, $(".slide-wrapper .design-element.current").each(function () {
				var e = $(this),
					t = {
						top: parseInt(e.css("top")),
						left: parseInt(e.css("left"))
					};
				e.data("position", t)
			})
		},
		s = function (e) {
			if (!r) return;
			$(".slide-wrapper .design-element.current").each(function () {
				var t = $(this),
					n = t.data("position");
				if (!n) return;
				var i = e.clientX - r.clientX,
					s = e.clientY - r.clientY;
				t.css("top", n.top + s + "px"), t.css("left", n.left + i + "px")
			})
		},
		o = function (e) {
			r = null
		};
	n.on("mousedown", ".slide-wrapper", i).on("mousemove", s).on("mouseup", o), n.on("mouseup", function (e) {
		var t = $(e.target),
			n = t.closest(".design-element"),
			r = n.size(),
			i = e.altKey,
			s = e.shiftKey;
		if (t.closest(".tool-container").size() || t.closest(".jscolor").size() || t.closest(".slide-btn-group").size()) return;
		e.preventDefault(), !s && !i && !n.hasClass("current") && !C && $(".slide-container .current").removeClass("current"), r && (s ? n.addClass("current") : i ? n.removeClass("current") : n.addClass("current"))
	}), n.on("keydown", function (e) {
		var t = $(".slide-container .design-element.current");
		if (t.size() < 1) return;
		var n = e.keyCode,
			r = e.ctrlKey,
			i = e.shiftKey ? 10 : 1;
		t.each(function () {
			var t = $(this);
			if (t.hasClass(".toolbar-show") || t.find(".toolbar-show").size()) return;
			var s = parseInt(t.css("top")),
				o = parseInt(t.css("left")),
				u = t.width(),
				a = t.height();
			if (r && n == 37) u -= i;
			else if (n == 37) o -= i;
			else if (r && n == 38) a -= i;
			else if (n == 38) s -= i;
			else if (r && n == 39) u += i;
			else if (n == 39) o += i;
			else if (r && n == 40) a += i;
			else {
				if (n != 40) {
					if (n == 46) {
						h();
						return
					}
					return
				}
				s += i
			}
			e.preventDefault(), r ? t.height(a).width(u) : t.css({
				top: s + "px",
				left: o + "px"
			})
		})
	});
	var u,uu, a = $(".slide-wrapper"),
		f, l = function (e) {
			var t = $(".slide-wrapper .design-element.current");
			e ? f = e : f = null, t.size() ? (u = t.clone(), OurJS.Designer.setRunnable(u), $.messager.popup("复制成功")) : (u = null, $.messager.popup("请先选中元件"))
		},
		c = function (e) {
			if (!u) return;
			uu = u.clone(), uu.each(function () {
				var t = $(this),
					n, r;
				e && f ? (n = parseInt(t.css("top")) + e.pageY - f.pageY, r = parseInt(t.css("left")) + e.pageX - f.pageX) : (n = parseInt(t.css("top")) + 20, r = parseInt(t.css("left")) + 20), t.css("top", n + "px").css("left", r + "px")
			}), $(".slide-wrapper .design-element.current").removeClass("current"), OurJS.Designer.setInnerSlideEvent(uu), $(".slide-wrapper .slide.active").append(uu)
		},
		h = function () {
			$(".slide-wrapper .design-element.current").remove()
		};
	$(".slide-design-menu .btn-copy").on("mousedown", l), $(".slide-design-menu .btn-paste").on("mousedown", c), $(".slide-design-menu .btn-del").on("mousedown", h), $(".slide-btn-group .btn-justify-left").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 1e5;
		e.each(function () {
			var e = parseInt($(this).css("left")) || 0;
			e < t && (t = e)
		}).each(function () {
			$(this).css("left", t)
		})
	}), $(".slide-btn-group .btn-justify-right").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 0;
		e.each(function () {
			var e = $(this),
				n = (parseInt(e.css("left")) || 0) + e.width();
			n > t && (t = n)
		}).each(function () {
			var e = $(this);
			e.css("left", t - e.width())
		})
	}), $(".slide-btn-group .btn-justify-top").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 1e5;
		e.each(function () {
			var e = parseInt($(this).css("top")) || 0;
			e < t && (t = e)
		}).each(function () {
			$(this).css("top", t)
		})
	}), $(".slide-btn-group .btn-justify-bottom").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 0;
		e.each(function () {
			var e = $(this),
				n = (parseInt(e.css("top")) || 0) + e.height();
			n > t && (t = n)
		}).each(function () {
			var e = $(this);
			e.css("top", t - e.height())
		})
	}), $(".slide-btn-group .btn-justify-width").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 1e5,
			n = 100;
		e.each(function () {
			var e = parseInt($(this).css("left")) || 0;
			e < t && (t = e, n = $(this).width())
		}).each(function () {
			$(this).css("width", n)
		})
	}), $(".slide-btn-group .btn-justify-height").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 1e5,
			n = 100;
		e.each(function () {
			var e = parseInt($(this).css("left")) || 0;
			e < t && (t = e, n = $(this).height())
		}).each(function () {
			$(this).css("height", n)
		})
	}), $(".slide-btn-group .btn-justify-middle").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 0,
			n = [];
		e.each(function (e) {
			var r = parseInt($(this).css("left")) || 0,
				i = $(this).width();
			t += i, n.push({
				idx: e,
				pos: r,
				val: i
			})
		}), n.sort(function (e, t) {
			return e.pos - t.pos
		});
		var r = n[n.length - 1],
			i = (r.pos + r.val - n[0].pos - t) / (n.length - 1);
		for (var s = 1, o = n.length; s < o; s++) {
			var u = e.eq(n[s].idx),
				a = e.eq(n[s - 1].idx);
			u.css("left", (parseInt(a.css("left")) || 0) + a.width() + i)
		}
	}), $(".slide-btn-group .btn-justify-center").on("mousedown", function () {
		var e = $(".slide-wrapper .design-element.current");
		if (e.size() < 2) return;
		m();
		var t = 0,
			n = [];
		e.each(function (e) {
			var r = parseInt($(this).css("top")) || 0,
				i = $(this).height();
			t += i, n.push({
				idx: e,
				pos: r,
				val: i
			})
		}), n.sort(function (e, t) {
			return e.pos - t.pos
		});
		var r = n[n.length - 1],
			i = (r.pos + r.val - n[0].pos - t) / (n.length - 1);
		for (var s = 1, o = n.length; s < o; s++) {
			var u = e.eq(n[s].idx),
				a = e.eq(n[s - 1].idx);
			u.css("top", (parseInt(a.css("top")) || 0) + a.height() + i)
		}
	});
	var p = [],
		d = -1,
		v = $(".slide-wrapper"),
		m = function () {
			d++;
			var e = v.html();
			p[d] = e, g()
		},
		g = function () {
			d < 1 ? w.addClass("disabled") : w.removeClass("disabled"), d > p.length - 2 ? E.addClass("disabled") : E.removeClass("disabled")
		},
		y = function () {
			d > 0 && p[--d] && v.html(p[d]), g()
		},
		b = function () {
			d < p.length - 1 && p[++d] && v.html(p[d]), g()
		};
	n.on("keydown", function (e) {
		var n = e.keyCode,
			r = e.ctrlKey,
			i = e.shiftKey,
			s = $(":focus");
		if (!a.is(":visible") || s.size() && s[0] != t.body) return;
		if (r && n == 67) {
			l();
			return
		}
		if (r && n == 86) {
			c();
			return
		}
		r && n == 90 ? y() : r && n == 89 ? b() : r && n == 82 && (e.preventDefault(), $(".slide.active").trigger("slides.switch"))
	}).on("toolbar.hide", function () {
		m()
	});
	var w = $(".btn-undo").on("click", y),
		E = $(".btn-redo").on("click", b);
	$(".btn-refresh").on("click", function () {
		$(".slide.active").trigger("slides.switch").trigger("slides.update"), g()
	});
	var S, x = $(".slide-selector"),
		T, N, C = !1,
		k = function (e) {
			var t = $(e.target);
			if (!t.hasClass("slide") && !t.hasClass("slide-wrapper")) return;
			e.preventDefault(), $(":focus").blur(), S = e, containerOffset = a.offset(), N = $(".slide-wrapper .slide.active").offset()
		},
		L = function (e) {
			if (!S) return;
			var t = e.pageX - S.pageX,
				n = e.pageY - S.pageY,
				r = 34,
				i = S.pageX - containerOffset.left,
				s = S.pageY - containerOffset.top + r;
			t < 0 && (t = Math.abs(t), i -= t), n < 0 && (n = Math.abs(n), s -= n), x.css({
				top: s + "px",
				left: i + "px",
				width: t + "px",
				height: n + "px"
			}).show(), $(".slide-container .active .design-element").each(function () {
				var e = $(this),
					o = parseInt(e.css("left")),
					u = parseInt(e.css("top")),
					a = o + N.left - containerOffset.left,
					f = u + N.top - containerOffset.top + r,
					l = e.width(),
					c = e.height();
				a > i && f > s && l + a < i + t && c + f < s + n ? (e.addClass("current"), C = !0) : e.removeClass("current")
			})
		},
		A = function (e) {
			S = null, C = !1, x.hide()
		};
	return n.on("mousedown", ".slide-wrapper", k).on("mousemove", L).on("mouseup", A), m(), {
		history: p
	}
}(window, document);
var OurJS = window.OurJS || {};
OurJS.Dashboard = function (document) {
	if ($(".dashboardpg").size() < 1) return;
	$(".layout-top .progressbar .progress").ajaxProgress();
	var formatDateTime = OurJS.Util.formatDateTime,
		mainSvr = conf.mainSvr || "http://airjd.com",
		uploadSvr = conf.uploadSvr || "http://sc1.airjd.com",
		$mySlide, $myImage, initTree = function () {
			var $tree = $(".tree"),
				$container = $(".layout-center"),
				activeClass = "current";
			$tree.on("click", function (e) {
				var $target = $(e.target),
					$header = $target.closest("p").closest("li");
				if ($header) {
					$header.toggleClass("open"), $target.closest("ul li ul li").size() && ($tree.find("." + activeClass).removeClass(activeClass), $header.addClass(activeClass));
					var action = $header.data("action");
					if (action) {
						e.preventDefault(), location.hash = "action=" + $header.attr("id");
						var $action = $(action);
						if ($action.size()) {
							var js = $action.data("js");
							$container.html($action.html()), js && eval(js), OurJS.Form.setView()
						}
					}
				}
			})
		},

		getUserAsset = function () {
			getUserSlide(), getUserPage()
		},
		getUserDoamin = function () {
			var e = $("#myDomains");
			e.datagrid({
				singleSelect: !0,
				columns: [[{
					title: "域名",
					field: "domain"
				}, {
					title: "转向地址",
					field: "url",
					formatter: function (e) {
						return '<a href="{0}" target="_blank">{0}</a>'.format(e)
					}
				}, {
					title: "删除",
					field: "domain",
					formatter: function (e) {
						return '<a class="btn-del-conf" href="{0}/svr/domain.del/{1}" target="_blank">删除</a>'.format(conf.uploadSvr, e)
					}
				}]]
			}), $.getJSON("/user/domains", function (t) {
				if (t.error) {
					$.messager.popup(t.error);
					return
				}
				e.datagrid("loadData", t.rows || [])
			})
		};
	initTree();
	var domainBindHandler = function (e, t) {
			if (t.error) {
				$.messager.alert(t.error);
				return
			}
			t.message && $.messager.popup(t.message)
		},
		domainAddHandler = function (e, t) {
			domainBindHandler(e, t), !t.error && getUserDoamin(), $(e.target).find("[disabled]").removeAttr("disabled")
		};
	return $(function () {
		$(document).on("click", ".btn-image-del", deleteImagesHandler).on("click", ".btn-slide-del", deleteSlideHandler).on("click", ".btn-audio-del", deleteAudioHandler).on("click", ".btn-del-conf", deleteConfirmHandler).on("click", ".btn-image-sel", function () {
			$myImage.datagrid("selectRow")
		}).on("click", ".btn-image-unsel", function () {
			$myImage.datagrid("unselectRow")
		}).on("uploadPPT.done", function () {
			$.bbq.getState("action") == "tab-slide" ? getUserSlide() : $.bbq.pushState({
				action: "tab-slide"
			})
		}).on("ajax.done", ".form-domain", domainBindHandler).on("ajax.done", ".form-domain-add", domainAddHandler).on("mouseover", ".slide-image", function () {
			var e = $(this),
				t = e.find(".qr-code"),
				n = e.find("b");
			if (n.size()) return;
			n = $("<b/>"), t.append(n), new QRCode(n[0], {
				text: e.find(".thumb").attr("href"),
				width: 124,
				height: 124,
				colorDark: "#000000",
				colorLight: "#ffffff",
				correctLevel: QRCode.CorrectLevel.H
			})
		}), $(window).bind("hashchange", function (e) {
			var t = ($.bbq.getState("action") || "tab-slide").split("|");
			for (var n = 0, r = t.length; n < r; n++) $("#" + t[n] + " a").click()
		}).trigger("hashchange")
	}), {
		getUserAsset: getUserAsset,
		getUserSlide: getUserSlide,
		getUserImage: getUserImage,
		getUserPage: getUserPage,
		getUserForm: getUserForm,
		getUserDoamin: getUserDoamin,
		getInviteUser: getInviteUser
	}
}(document) /* Powered by OurJS.com */
