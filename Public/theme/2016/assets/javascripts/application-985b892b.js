/* ========================================================================
 * Bootstrap: affix.js v3.1.1
 * http://getbootstrap.com/javascript/#affix
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
+function (t) {
    "use strict";
    var e = function (i, o) {
        this.options = t.extend({}, e.DEFAULTS, o), this.$window = t(window).on("scroll.bs.affix.data-api", t.proxy(this.checkPosition, this)).on("click.bs.affix.data-api", t.proxy(this.checkPositionWithEventLoop, this)), this.$element = t(i), this.affixed = this.unpin = this.pinnedOffset = null, this.checkPosition()
    };
    e.RESET = "affix affix-top affix-bottom", e.DEFAULTS = {offset: 0}, e.prototype.getPinnedOffset = function () {
        if (this.pinnedOffset)return this.pinnedOffset;
        this.$element.removeClass(e.RESET).addClass("affix");
        var t = this.$window.scrollTop(), i = this.$element.offset();
        return this.pinnedOffset = i.top - t
    }, e.prototype.checkPositionWithEventLoop = function () {
        setTimeout(t.proxy(this.checkPosition, this), 1)
    }, e.prototype.checkPosition = function () {
        if (this.$element.is(":visible")) {
            var i = t(document).height(), o = this.$window.scrollTop(), s = this.$element.offset(), n = this.options.offset, a = n.top, r = n.bottom;
            "object" != typeof n && (r = a = n), "function" == typeof a && (a = n.top(this.$element)), "function" == typeof r && (r = n.bottom(this.$element));
            var l = null != this.unpin && o + this.unpin <= s.top ? !1 : null != r && s.top + this.$element.height() >= i - r ? "bottom" : null != a && a >= o ? "top" : !1;
            if (this.affixed !== l) {
                null != this.unpin && this.$element.css("top", "");
                var h = "affix" + (l ? "-" + l : ""), c = t.Event(h + ".bs.affix");
                this.$element.trigger(c), c.isDefaultPrevented() || (this.affixed = l, this.unpin = "bottom" == l ? this.getPinnedOffset() : null, this.$element.removeClass(e.RESET).addClass(h).trigger(t.Event(h.replace("affix", "affixed"))), "bottom" == l && this.$element.offset({top: s.top}))
            }
        }
    };
    var i = t.fn.affix;
    t.fn.affix = function (i) {
        return this.each(function () {
            var o = t(this), s = o.data("bs.affix"), n = "object" == typeof i && i;
            s || o.data("bs.affix", s = new e(this, n)), "string" == typeof i && s[i]()
        })
    }, t.fn.affix.Constructor = e, t.fn.affix.noConflict = function () {
        return t.fn.affix = i, this
    }, t(window).on("load", function () {
        t('[data-spy="affix"]').each(function () {
            var e = t(this), i = e.data();
            i.offset = i.offset || {}, i.offsetBottom && (i.offset.bottom = i.offsetBottom), i.offsetTop && (i.offset.top = i.offsetTop), e.affix(i)
        })
    })
}(jQuery), /* ========================================================================
 * Bootstrap: alert.js v3.1.1
 * http://getbootstrap.com/javascript/#alerts
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = '[data-dismiss="alert"]', i = function (i) {
            t(i).on("click", e, this.close)
        };
        i.prototype.close = function (e) {
            function i() {
                n.trigger("closed.bs.alert").remove()
            }

            var o = t(this), s = o.attr("data-target");
            s || (s = o.attr("href"), s = s && s.replace(/.*(?=#[^\s]*$)/, ""));
            var n = t(s);
            e && e.preventDefault(), n.length || (n = o.hasClass("alert") ? o : o.parent()), n.trigger(e = t.Event("close.bs.alert")), e.isDefaultPrevented() || (n.removeClass("in"), t.support.transition && n.hasClass("fade") ? n.one(t.support.transition.end, i).emulateTransitionEnd(150) : i())
        };
        var o = t.fn.alert;
        t.fn.alert = function (e) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.alert");
                s || o.data("bs.alert", s = new i(this)), "string" == typeof e && s[e].call(o)
            })
        }, t.fn.alert.Constructor = i, t.fn.alert.noConflict = function () {
            return t.fn.alert = o, this
        }, t(document).on("click.bs.alert.data-api", e, i.prototype.close)
    }(jQuery), /* ========================================================================
 * Bootstrap: button.js v3.1.1
 * http://getbootstrap.com/javascript/#buttons
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (i, o) {
            this.$element = t(i), this.options = t.extend({}, e.DEFAULTS, o), this.isLoading = !1
        };
        e.DEFAULTS = {loadingText: "loading..."}, e.prototype.setState = function (e) {
            var i = "disabled", o = this.$element, s = o.is("input") ? "val" : "html", n = o.data();
            e += "Text", n.resetText || o.data("resetText", o[s]()), o[s](n[e] || this.options[e]), setTimeout(t.proxy(function () {
                "loadingText" == e ? (this.isLoading = !0, o.addClass(i).attr(i, i)) : this.isLoading && (this.isLoading = !1, o.removeClass(i).removeAttr(i))
            }, this), 0)
        }, e.prototype.toggle = function () {
            var t = !0, e = this.$element.closest('[data-toggle="buttons"]');
            if (e.length) {
                var i = this.$element.find("input");
                "radio" == i.prop("type") && (i.prop("checked") && this.$element.hasClass("active") ? t = !1 : e.find(".active").removeClass("active")), t && i.prop("checked", !this.$element.hasClass("active")).trigger("change")
            }
            t && this.$element.toggleClass("active")
        };
        var i = t.fn.button;
        t.fn.button = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.button"), n = "object" == typeof i && i;
                s || o.data("bs.button", s = new e(this, n)), "toggle" == i ? s.toggle() : i && s.setState(i)
            })
        }, t.fn.button.Constructor = e, t.fn.button.noConflict = function () {
            return t.fn.button = i, this
        }, t(document).on("click.bs.button.data-api", "[data-toggle^=button]", function (e) {
            var i = t(e.target);
            i.hasClass("btn") || (i = i.closest(".btn")), i.button("toggle"), e.preventDefault()
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: carousel.js v3.1.1
 * http://getbootstrap.com/javascript/#carousel
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (e, i) {
            this.$element = t(e), this.$indicators = this.$element.find(".carousel-indicators"), this.options = i, this.paused = this.sliding = this.interval = this.$active = this.$items = null, "hover" == this.options.pause && this.$element.on("mouseenter", t.proxy(this.pause, this)).on("mouseleave", t.proxy(this.cycle, this))
        };
        e.DEFAULTS = {interval: 5e3, pause: "hover", wrap: !0}, e.prototype.cycle = function (e) {
            return e || (this.paused = !1), this.interval && clearInterval(this.interval), this.options.interval && !this.paused && (this.interval = setInterval(t.proxy(this.next, this), this.options.interval)), this
        }, e.prototype.getActiveIndex = function () {
            return this.$active = this.$element.find(".item.active"), this.$items = this.$active.parent().children(".item"), this.$items.index(this.$active)
        }, e.prototype.to = function (e) {
            var i = this, o = this.getActiveIndex();
            return e > this.$items.length - 1 || 0 > e ? void 0 : this.sliding ? this.$element.one("slid.bs.carousel", function () {
                i.to(e)
            }) : o == e ? this.pause().cycle() : this.slide(e > o ? "next" : "prev", t(this.$items[e]))
        }, e.prototype.pause = function (e) {
            return e || (this.paused = !0), this.$element.find(".next, .prev").length && t.support.transition && (this.$element.trigger(t.support.transition.end), this.cycle(!0)), this.interval = clearInterval(this.interval), this
        }, e.prototype.next = function () {
            return this.sliding ? void 0 : this.slide("next")
        }, e.prototype.prev = function () {
            return this.sliding ? void 0 : this.slide("prev")
        }, e.prototype.slide = function (e, i) {
            var o = this.$element.find(".item.active"), s = i || o[e](), n = this.interval, a = "next" == e ? "left" : "right", r = "next" == e ? "first" : "last", l = this;
            if (!s.length) {
                if (!this.options.wrap)return;
                s = this.$element.find(".item")[r]()
            }
            if (s.hasClass("active"))return this.sliding = !1;
            var h = t.Event("slide.bs.carousel", {relatedTarget: s[0], direction: a});
            return this.$element.trigger(h), h.isDefaultPrevented() ? void 0 : (this.sliding = !0, n && this.pause(), this.$indicators.length && (this.$indicators.find(".active").removeClass("active"), this.$element.one("slid.bs.carousel", function () {
                var e = t(l.$indicators.children()[l.getActiveIndex()]);
                e && e.addClass("active")
            })), t.support.transition && this.$element.hasClass("slide") ? (s.addClass(e), s[0].offsetWidth, o.addClass(a), s.addClass(a), o.one(t.support.transition.end, function () {
                s.removeClass([e, a].join(" ")).addClass("active"), o.removeClass(["active", a].join(" ")), l.sliding = !1, setTimeout(function () {
                    l.$element.trigger("slid.bs.carousel")
                }, 0)
            }).emulateTransitionEnd(1e3 * o.css("transition-duration").slice(0, -1))) : (o.removeClass("active"), s.addClass("active"), this.sliding = !1, this.$element.trigger("slid.bs.carousel")), n && this.cycle(), this)
        };
        var i = t.fn.carousel;
        t.fn.carousel = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.carousel"), n = t.extend({}, e.DEFAULTS, o.data(), "object" == typeof i && i), a = "string" == typeof i ? i : n.slide;
                s || o.data("bs.carousel", s = new e(this, n)), "number" == typeof i ? s.to(i) : a ? s[a]() : n.interval && s.pause().cycle()
            })
        }, t.fn.carousel.Constructor = e, t.fn.carousel.noConflict = function () {
            return t.fn.carousel = i, this
        }, t(document).on("click.bs.carousel.data-api", "[data-slide], [data-slide-to]", function (e) {
            var i, o = t(this), s = t(o.attr("data-target") || (i = o.attr("href")) && i.replace(/.*(?=#[^\s]+$)/, "")), n = t.extend({}, s.data(), o.data()), a = o.attr("data-slide-to");
            a && (n.interval = !1), s.carousel(n), (a = o.attr("data-slide-to")) && s.data("bs.carousel").to(a), e.preventDefault()
        }), t(window).on("load", function () {
            t('[data-ride="carousel"]').each(function () {
                var e = t(this);
                e.carousel(e.data())
            })
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: collapse.js v3.1.1
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (i, o) {
            this.$element = t(i), this.options = t.extend({}, e.DEFAULTS, o), this.transitioning = null, this.options.parent && (this.$parent = t(this.options.parent)), this.options.toggle && this.toggle()
        };
        e.DEFAULTS = {toggle: !0}, e.prototype.dimension = function () {
            var t = this.$element.hasClass("width");
            return t ? "width" : "height"
        }, e.prototype.show = function () {
            if (!this.transitioning && !this.$element.hasClass("in")) {
                var e = t.Event("show.bs.collapse");
                if (this.$element.trigger(e), !e.isDefaultPrevented()) {
                    var i = this.$parent && this.$parent.find("> .panel > .in");
                    if (i && i.length) {
                        var o = i.data("bs.collapse");
                        if (o && o.transitioning)return;
                        i.collapse("hide"), o || i.data("bs.collapse", null)
                    }
                    var s = this.dimension();
                    this.$element.removeClass("collapse").addClass("collapsing")[s](0), this.transitioning = 1;
                    var n = function (e) {
                        return e && e.target != this.$element[0] ? void this.$element.one(t.support.transition.end, t.proxy(n, this)) : (this.$element.removeClass("collapsing").addClass("collapse in")[s]("auto"), this.transitioning = 0, void this.$element.trigger("shown.bs.collapse"))
                    };
                    if (!t.support.transition)return n.call(this);
                    var a = t.camelCase(["scroll", s].join("-"));
                    this.$element.one(t.support.transition.end, t.proxy(n, this)).emulateTransitionEnd(350)[s](this.$element[0][a])
                }
            }
        }, e.prototype.hide = function () {
            if (!this.transitioning && this.$element.hasClass("in")) {
                var e = t.Event("hide.bs.collapse");
                if (this.$element.trigger(e), !e.isDefaultPrevented()) {
                    var i = this.dimension();
                    this.$element[i](this.$element[i]())[0].offsetHeight, this.$element.addClass("collapsing").removeClass("collapse").removeClass("in"), this.transitioning = 1;
                    var o = function (e) {
                        return e && e.target != this.$element[0] ? void this.$element.one(t.support.transition.end, t.proxy(o, this)) : (this.transitioning = 0, void this.$element.trigger("hidden.bs.collapse").removeClass("collapsing").addClass("collapse"))
                    };
                    return t.support.transition ? void this.$element[i](0).one(t.support.transition.end, t.proxy(o, this)).emulateTransitionEnd(350) : o.call(this)
                }
            }
        }, e.prototype.toggle = function () {
            this[this.$element.hasClass("in") ? "hide" : "show"]()
        };
        var i = t.fn.collapse;
        t.fn.collapse = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.collapse"), n = t.extend({}, e.DEFAULTS, o.data(), "object" == typeof i && i);
                !s && n.toggle && "show" == i && (i = !i), s || o.data("bs.collapse", s = new e(this, n)), "string" == typeof i && s[i]()
            })
        }, t.fn.collapse.Constructor = e, t.fn.collapse.noConflict = function () {
            return t.fn.collapse = i, this
        }, t(document).on("click.bs.collapse.data-api", '[data-toggle="collapse"]', function (e) {
            var i, o = t(this), s = o.attr("data-target") || e.preventDefault() || (i = o.attr("href")) && i.replace(/.*(?=#[^\s]+$)/, ""), n = t(s), a = n.data("bs.collapse"), r = a ? "toggle" : o.data(), l = o.attr("data-parent"), h = l && t(l);
            a && a.transitioning || (h && h.find('[data-toggle="collapse"][data-parent="' + l + '"]').not(o).addClass("collapsed"), o[n.hasClass("in") ? "addClass" : "removeClass"]("collapsed")), n.collapse(r)
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: dropdown.js v3.1.1
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        function e(e) {
            t(o).remove(), t(s).each(function () {
                var o = i(t(this)), s = {relatedTarget: this};
                o.hasClass("open") && (o.trigger(e = t.Event("hide.bs.dropdown", s)), e.isDefaultPrevented() || o.removeClass("open").trigger("hidden.bs.dropdown", s))
            })
        }

        function i(e) {
            var i = e.attr("data-target");
            i || (i = e.attr("href"), i = i && /#[A-Za-z]/.test(i) && i.replace(/.*(?=#[^\s]*$)/, ""));
            var o = i && t(i);
            return o && o.length ? o : e.parent()
        }

        var o = ".dropdown-backdrop", s = '[data-toggle="dropdown"]', n = function (e) {
            t(e).on("click.bs.dropdown", this.toggle)
        };
        n.prototype.toggle = function (o) {
            var s = t(this);
            if (!s.is(".disabled, :disabled")) {
                var n = i(s), a = n.hasClass("open");
                if (e(), !a) {
                    "ontouchstart"in document.documentElement && !n.closest(".navbar-nav").length && t('<div class="dropdown-backdrop"/>').insertAfter(t(this)).on("click", e);
                    var r = {relatedTarget: this};
                    if (n.trigger(o = t.Event("show.bs.dropdown", r)), o.isDefaultPrevented())return;
                    s.trigger("focus"), n.toggleClass("open").trigger("shown.bs.dropdown", r)
                }
                return !1
            }
        }, n.prototype.keydown = function (e) {
            if (/(38|40|27)/.test(e.keyCode)) {
                var o = t(this);
                if (e.preventDefault(), e.stopPropagation(), !o.is(".disabled, :disabled")) {
                    var n = i(o), a = n.hasClass("open");
                    if (!a || a && 27 == e.keyCode)return 27 == e.which && n.find(s).trigger("focus"), o.trigger("click");
                    var r = " li:not(.divider):visible a", l = n.find('[role="menu"]' + r + ', [role="listbox"]' + r);
                    if (l.length) {
                        var h = l.index(l.filter(":focus"));
                        38 == e.keyCode && h > 0 && h--, 40 == e.keyCode && h < l.length - 1 && h++, ~h || (h = 0), l.eq(h).trigger("focus")
                    }
                }
            }
        };
        var a = t.fn.dropdown;
        t.fn.dropdown = function (e) {
            return this.each(function () {
                var i = t(this), o = i.data("bs.dropdown");
                o || i.data("bs.dropdown", o = new n(this)), "string" == typeof e && o[e].call(i)
            })
        }, t.fn.dropdown.Constructor = n, t.fn.dropdown.noConflict = function () {
            return t.fn.dropdown = a, this
        }, t(document).on("click.bs.dropdown.data-api", e).on("click.bs.dropdown.data-api", ".dropdown form", function (t) {
            t.stopPropagation()
        }).on("click.bs.dropdown.data-api", s, n.prototype.toggle).on("keydown.bs.dropdown.data-api", s + ', [role="menu"], [role="listbox"]', n.prototype.keydown)
    }(jQuery), /* ========================================================================
 * Bootstrap: tab.js v3.1.1
 * http://getbootstrap.com/javascript/#tabs
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (e) {
            this.element = t(e)
        };
        e.prototype.show = function () {
            var e = this.element, i = e.closest("ul:not(.dropdown-menu)"), o = e.data("target");
            if (o || (o = e.attr("href"), o = o && o.replace(/.*(?=#[^\s]*$)/, "")), !e.parent("li").hasClass("active")) {
                var s = i.find(".active:last a")[0], n = t.Event("show.bs.tab", {relatedTarget: s});
                if (e.trigger(n), !n.isDefaultPrevented()) {
                    var a = t(o);
                    this.activate(e.parent("li"), i), this.activate(a, a.parent(), function () {
                        e.trigger({type: "shown.bs.tab", relatedTarget: s})
                    })
                }
            }
        }, e.prototype.activate = function (e, i, o) {
            function s() {
                n.removeClass("active").find("> .dropdown-menu > .active").removeClass("active"), e.addClass("active"), a ? (e[0].offsetWidth, e.addClass("in")) : e.removeClass("fade"), e.parent(".dropdown-menu") && e.closest("li.dropdown").addClass("active"), o && o()
            }

            var n = i.find("> .active"), a = o && t.support.transition && n.hasClass("fade");
            a ? n.one(t.support.transition.end, s).emulateTransitionEnd(150) : s(), n.removeClass("in")
        };
        var i = t.fn.tab;
        t.fn.tab = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.tab");
                s || o.data("bs.tab", s = new e(this)), "string" == typeof i && s[i]()
            })
        }, t.fn.tab.Constructor = e, t.fn.tab.noConflict = function () {
            return t.fn.tab = i, this
        }, t(document).on("click.bs.tab.data-api", '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
            e.preventDefault(), t(this).tab("show")
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: transition.js v3.1.1
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        function e() {
            var t = document.createElement("bootstrap"), e = {
                WebkitTransition: "webkitTransitionEnd",
                MozTransition: "transitionend",
                OTransition: "oTransitionEnd otransitionend",
                transition: "transitionend"
            };
            for (var i in e)if (void 0 !== t.style[i])return {end: e[i]};
            return !1
        }

        t.fn.emulateTransitionEnd = function (e) {
            var i = !1, o = this;
            t(this).one(t.support.transition.end, function () {
                i = !0
            });
            var s = function () {
                i || t(o).trigger(t.support.transition.end)
            };
            return setTimeout(s, e), this
        }, t(function () {
            t.support.transition = e()
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: scrollspy.js v3.1.1
 * http://getbootstrap.com/javascript/#scrollspy
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        function e(i, o) {
            var s, n = t.proxy(this.process, this);
            this.$element = t(t(i).is("body") ? window : i), this.$body = t("body"), this.$scrollElement = this.$element.on("scroll.bs.scrollspy", n), this.options = t.extend({}, e.DEFAULTS, o), this.selector = (this.options.target || (s = t(i).attr("href")) && s.replace(/.*(?=#[^\s]+$)/, "") || "") + " .nav li > a", this.offsets = t([]), this.targets = t([]), this.activeTarget = null, this.refresh(), this.process()
        }

        e.DEFAULTS = {offset: 10}, e.prototype.refresh = function () {
            var e = this.$element[0] == window ? "offset" : "position";
            this.offsets = t([]), this.targets = t([]);
            var i = this;
            this.$body.find(this.selector).map(function () {
                var o = t(this), s = o.data("target") || o.attr("href"), n = /^#./.test(s) && t(s);
                return n && n.length && n.is(":visible") && [[n[e]().top + (!t.isWindow(i.$scrollElement.get(0)) && i.$scrollElement.scrollTop()), s]] || null
            }).sort(function (t, e) {
                return t[0] - e[0]
            }).each(function () {
                i.offsets.push(this[0]), i.targets.push(this[1])
            })
        }, e.prototype.process = function () {
            var t, e = this.$scrollElement.scrollTop() + this.options.offset, i = this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight), o = i - this.$scrollElement.height(), s = this.offsets, n = this.targets, a = this.activeTarget;
            if (e >= o)return a != (t = n.last()[0]) && this.activate(t);
            if (a && e <= s[0])return a != (t = n[0]) && this.activate(t);
            for (t = s.length; t--;)a != n[t] && e >= s[t] && (!s[t + 1] || e <= s[t + 1]) && this.activate(n[t])
        }, e.prototype.activate = function (e) {
            this.activeTarget = e, t(this.selector).parentsUntil(this.options.target, ".active").removeClass("active");
            var i = this.selector + '[data-target="' + e + '"],' + this.selector + '[href="' + e + '"]', o = t(i).parents("li").addClass("active");
            o.parent(".dropdown-menu").length && (o = o.closest("li.dropdown").addClass("active")), o.trigger("activate.bs.scrollspy")
        };
        var i = t.fn.scrollspy;
        t.fn.scrollspy = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.scrollspy"), n = "object" == typeof i && i;
                s || o.data("bs.scrollspy", s = new e(this, n)), "string" == typeof i && s[i]()
            })
        }, t.fn.scrollspy.Constructor = e, t.fn.scrollspy.noConflict = function () {
            return t.fn.scrollspy = i, this
        }, t(window).on("load.bs.scrollspy.data-api", function () {
            t('[data-spy="scroll"]').each(function () {
                var e = t(this);
                e.scrollspy(e.data())
            })
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: modal.js v3.1.1
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (e, i) {
            this.options = i, this.$body = t(document.body), this.$element = t(e), this.$backdrop = this.isShown = null, this.scrollbarWidth = 0, this.options.remote && this.$element.find(".modal-content").load(this.options.remote, t.proxy(function () {
                this.$element.trigger("loaded.bs.modal")
            }, this))
        };
        e.DEFAULTS = {backdrop: !0, keyboard: !0, show: !0}, e.prototype.toggle = function (t) {
            return this.isShown ? this.hide() : this.show(t)
        }, e.prototype.show = function (e) {
            var i = this, o = t.Event("show.bs.modal", {relatedTarget: e});
            this.$element.trigger(o), this.isShown || o.isDefaultPrevented() || (this.isShown = !0, this.checkScrollbar(), this.$body.addClass("modal-open"), this.setScrollbar(), this.escape(), this.$element.on("click.dismiss.bs.modal", '[data-dismiss="modal"]', t.proxy(this.hide, this)), this.backdrop(function () {
                var o = t.support.transition && i.$element.hasClass("fade");
                i.$element.parent().length || i.$element.appendTo(i.$body), i.$element.show().scrollTop(0), o && i.$element[0].offsetWidth, i.$element.addClass("in").attr("aria-hidden", !1), i.enforceFocus();
                var s = t.Event("shown.bs.modal", {relatedTarget: e});
                o ? i.$element.find(".modal-dialog").one(t.support.transition.end, function () {
                    i.$element.trigger("focus").trigger(s)
                }).emulateTransitionEnd(300) : i.$element.trigger("focus").trigger(s)
            }))
        }, e.prototype.hide = function (e) {
            e && e.preventDefault(), e = t.Event("hide.bs.modal"), this.$element.trigger(e), this.isShown && !e.isDefaultPrevented() && (this.isShown = !1, this.$body.removeClass("modal-open"), this.resetScrollbar(), this.escape(), t(document).off("focusin.bs.modal"), this.$element.removeClass("in").attr("aria-hidden", !0).off("click.dismiss.bs.modal"), t.support.transition && this.$element.hasClass("fade") ? this.$element.one(t.support.transition.end, t.proxy(this.hideModal, this)).emulateTransitionEnd(300) : this.hideModal())
        }, e.prototype.enforceFocus = function () {
            t(document).off("focusin.bs.modal").on("focusin.bs.modal", t.proxy(function (t) {
                this.$element[0] === t.target || this.$element.has(t.target).length || this.$element.trigger("focus")
            }, this))
        }, e.prototype.escape = function () {
            this.isShown && this.options.keyboard ? this.$element.on("keyup.dismiss.bs.modal", t.proxy(function (t) {
                27 == t.which && this.hide()
            }, this)) : this.isShown || this.$element.off("keyup.dismiss.bs.modal")
        }, e.prototype.hideModal = function () {
            var t = this;
            this.$element.hide(), this.backdrop(function () {
                t.removeBackdrop(), t.$element.trigger("hidden.bs.modal")
            })
        }, e.prototype.removeBackdrop = function () {
            this.$backdrop && this.$backdrop.remove(), this.$backdrop = null
        }, e.prototype.backdrop = function (e) {
            var i = this.$element.hasClass("fade") ? "fade" : "";
            if (this.isShown && this.options.backdrop) {
                var o = t.support.transition && i;
                if (this.$backdrop = t('<div class="modal-backdrop ' + i + '" />').appendTo(this.$body), this.$element.on("click.dismiss.bs.modal", t.proxy(function (t) {
                        t.target === t.currentTarget && ("static" == this.options.backdrop ? this.$element[0].focus.call(this.$element[0]) : this.hide.call(this))
                    }, this)), o && this.$backdrop[0].offsetWidth, this.$backdrop.addClass("in"), !e)return;
                o ? this.$backdrop.one(t.support.transition.end, e).emulateTransitionEnd(150) : e()
            } else!this.isShown && this.$backdrop ? (this.$backdrop.removeClass("in"), t.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one(t.support.transition.end, e).emulateTransitionEnd(150) : e()) : e && e()
        }, e.prototype.checkScrollbar = function () {
            document.body.clientWidth >= window.innerWidth || (this.scrollbarWidth = this.scrollbarWidth || this.measureScrollbar())
        }, e.prototype.setScrollbar = function () {
            var t = parseInt(this.$body.css("padding-right") || 0);
            this.scrollbarWidth && this.$body.css("padding-right", t + this.scrollbarWidth)
        }, e.prototype.resetScrollbar = function () {
            this.$body.css("padding-right", "")
        }, e.prototype.measureScrollbar = function () {
            var t = document.createElement("div");
            t.className = "modal-scrollbar-measure", this.$body.append(t);
            var e = t.offsetWidth - t.clientWidth;
            return this.$body[0].removeChild(t), e
        };
        var i = t.fn.modal;
        t.fn.modal = function (i, o) {
            return this.each(function () {
                var s = t(this), n = s.data("bs.modal"), a = t.extend({}, e.DEFAULTS, s.data(), "object" == typeof i && i);
                n || s.data("bs.modal", n = new e(this, a)), "string" == typeof i ? n[i](o) : a.show && n.show(o)
            })
        }, t.fn.modal.Constructor = e, t.fn.modal.noConflict = function () {
            return t.fn.modal = i, this
        }, t(document).on("click.bs.modal.data-api", '[data-toggle="modal"]', function (e) {
            var i = t(this), o = i.attr("href"), s = t(i.attr("data-target") || o && o.replace(/.*(?=#[^\s]+$)/, "")), n = s.data("bs.modal") ? "toggle" : t.extend({remote: !/#/.test(o) && o}, s.data(), i.data());
            i.is("a") && e.preventDefault(), s.modal(n, this).one("hide", function () {
                i.is(":visible") && i.trigger("focus")
            })
        })
    }(jQuery), /* ========================================================================
 * Bootstrap: tooltip.js v3.1.1
 * http://getbootstrap.com/javascript/#tooltip
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (t, e) {
            this.type = this.options = this.enabled = this.timeout = this.hoverState = this.$element = null, this.init("tooltip", t, e)
        };
        e.DEFAULTS = {
            animation: !0,
            placement: "top",
            selector: !1,
            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            trigger: "hover focus",
            title: "",
            delay: 0,
            html: !1,
            container: !1,
            viewport: {selector: "body", padding: 0}
        }, e.prototype.init = function (e, i, o) {
            this.enabled = !0, this.type = e, this.$element = t(i), this.options = this.getOptions(o), this.$viewport = this.options.viewport && t(this.options.viewport.selector || this.options.viewport);
            for (var s = this.options.trigger.split(" "), n = s.length; n--;) {
                var a = s[n];
                if ("click" == a)this.$element.on("click." + this.type, this.options.selector, t.proxy(this.toggle, this)); else if ("manual" != a) {
                    var r = "hover" == a ? "mouseenter" : "focusin", l = "hover" == a ? "mouseleave" : "focusout";
                    this.$element.on(r + "." + this.type, this.options.selector, t.proxy(this.enter, this)), this.$element.on(l + "." + this.type, this.options.selector, t.proxy(this.leave, this))
                }
            }
            this.options.selector ? this._options = t.extend({}, this.options, {
                trigger: "manual",
                selector: ""
            }) : this.fixTitle()
        }, e.prototype.getDefaults = function () {
            return e.DEFAULTS
        }, e.prototype.getOptions = function (e) {
            return e = t.extend({}, this.getDefaults(), this.$element.data(), e), e.delay && "number" == typeof e.delay && (e.delay = {
                show: e.delay,
                hide: e.delay
            }), e
        }, e.prototype.getDelegateOptions = function () {
            var e = {}, i = this.getDefaults();
            return this._options && t.each(this._options, function (t, o) {
                i[t] != o && (e[t] = o)
            }), e
        }, e.prototype.enter = function (e) {
            var i = e instanceof this.constructor ? e : t(e.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type);
            return clearTimeout(i.timeout), i.hoverState = "in", i.options.delay && i.options.delay.show ? void(i.timeout = setTimeout(function () {
                "in" == i.hoverState && i.show()
            }, i.options.delay.show)) : i.show()
        }, e.prototype.leave = function (e) {
            var i = e instanceof this.constructor ? e : t(e.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type);
            return clearTimeout(i.timeout), i.hoverState = "out", i.options.delay && i.options.delay.hide ? void(i.timeout = setTimeout(function () {
                "out" == i.hoverState && i.hide()
            }, i.options.delay.hide)) : i.hide()
        }, e.prototype.show = function () {
            var e = t.Event("show.bs." + this.type);
            if (this.hasContent() && this.enabled) {
                if (this.$element.trigger(e), e.isDefaultPrevented())return;
                var i = this, o = this.tip();
                this.setContent(), this.options.animation && o.addClass("fade");
                var s = "function" == typeof this.options.placement ? this.options.placement.call(this, o[0], this.$element[0]) : this.options.placement, n = /\s?auto?\s?/i, a = n.test(s);
                a && (s = s.replace(n, "") || "top"), o.detach().css({
                    top: 0,
                    left: 0,
                    display: "block"
                }).addClass(s), this.options.container ? o.appendTo(this.options.container) : o.insertAfter(this.$element);
                var r = this.getPosition(), l = o[0].offsetWidth, h = o[0].offsetHeight;
                if (a) {
                    var c = s, d = this.$element.parent(), p = this.getPosition(d);
                    s = "bottom" == s && r.top + r.height + h - p.scroll > p.height ? "top" : "top" == s && r.top - p.scroll - h < 0 ? "bottom" : "right" == s && r.right + l > p.width ? "left" : "left" == s && r.left - l < p.left ? "right" : s, o.removeClass(c).addClass(s)
                }
                var u = this.getCalculatedOffset(s, r, l, h);
                this.applyPlacement(u, s), this.hoverState = null;
                var f = function () {
                    i.$element.trigger("shown.bs." + i.type)
                };
                t.support.transition && this.$tip.hasClass("fade") ? o.one(t.support.transition.end, f).emulateTransitionEnd(150) : f()
            }
        }, e.prototype.applyPlacement = function (e, i) {
            var o = this.tip(), s = o[0].offsetWidth, n = o[0].offsetHeight, a = parseInt(o.css("margin-top"), 10), r = parseInt(o.css("margin-left"), 10);
            isNaN(a) && (a = 0), isNaN(r) && (r = 0), e.top = e.top + a, e.left = e.left + r, t.offset.setOffset(o[0], t.extend({
                using: function (t) {
                    o.css({top: Math.round(t.top), left: Math.round(t.left)})
                }
            }, e), 0), o.addClass("in");
            var l = o[0].offsetWidth, h = o[0].offsetHeight;
            "top" == i && h != n && (e.top = e.top + n - h);
            var c = this.getViewportAdjustedDelta(i, e, l, h);
            c.left ? e.left += c.left : e.top += c.top;
            var d = c.left ? 2 * c.left - s + l : 2 * c.top - n + h, p = c.left ? "left" : "top", u = c.left ? "offsetWidth" : "offsetHeight";
            o.offset(e), this.replaceArrow(d, o[0][u], p)
        }, e.prototype.replaceArrow = function (t, e, i) {
            this.arrow().css(i, t ? 50 * (1 - t / e) + "%" : "")
        }, e.prototype.setContent = function () {
            var t = this.tip(), e = this.getTitle();
            t.find(".tooltip-inner")[this.options.html ? "html" : "text"](e), t.removeClass("fade in top bottom left right")
        }, e.prototype.hide = function () {
            function e() {
                "in" != i.hoverState && o.detach(), i.$element.trigger("hidden.bs." + i.type)
            }

            var i = this, o = this.tip(), s = t.Event("hide.bs." + this.type);
            return this.$element.trigger(s), s.isDefaultPrevented() ? void 0 : (o.removeClass("in"), t.support.transition && this.$tip.hasClass("fade") ? o.one(t.support.transition.end, e).emulateTransitionEnd(150) : e(), this.hoverState = null, this)
        }, e.prototype.fixTitle = function () {
            var t = this.$element;
            (t.attr("title") || "string" != typeof t.attr("data-original-title")) && t.attr("data-original-title", t.attr("title") || "").attr("title", "")
        }, e.prototype.hasContent = function () {
            return this.getTitle()
        }, e.prototype.getPosition = function (e) {
            e = e || this.$element;
            var i = e[0], o = "BODY" == i.tagName;
            return t.extend({}, "function" == typeof i.getBoundingClientRect ? i.getBoundingClientRect() : null, {
                scroll: o ? document.documentElement.scrollTop || document.body.scrollTop : e.scrollTop(),
                width: o ? t(window).width() : e.outerWidth(),
                height: o ? t(window).height() : e.outerHeight()
            }, o ? {top: 0, left: 0} : e.offset())
        }, e.prototype.getCalculatedOffset = function (t, e, i, o) {
            return "bottom" == t ? {
                top: e.top + e.height,
                left: e.left + e.width / 2 - i / 2
            } : "top" == t ? {
                top: e.top - o,
                left: e.left + e.width / 2 - i / 2
            } : "left" == t ? {
                top: e.top + e.height / 2 - o / 2,
                left: e.left - i
            } : {top: e.top + e.height / 2 - o / 2, left: e.left + e.width}
        }, e.prototype.getViewportAdjustedDelta = function (t, e, i, o) {
            var s = {top: 0, left: 0};
            if (!this.$viewport)return s;
            var n = this.options.viewport && this.options.viewport.padding || 0, a = this.getPosition(this.$viewport);
            if (/right|left/.test(t)) {
                var r = e.top - n - a.scroll, l = e.top + n - a.scroll + o;
                r < a.top ? s.top = a.top - r : l > a.top + a.height && (s.top = a.top + a.height - l)
            } else {
                var h = e.left - n, c = e.left + n + i;
                h < a.left ? s.left = a.left - h : c > a.width && (s.left = a.left + a.width - c)
            }
            return s
        }, e.prototype.getTitle = function () {
            var t, e = this.$element, i = this.options;
            return t = e.attr("data-original-title") || ("function" == typeof i.title ? i.title.call(e[0]) : i.title)
        }, e.prototype.tip = function () {
            return this.$tip = this.$tip || t(this.options.template)
        }, e.prototype.arrow = function () {
            return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
        }, e.prototype.validate = function () {
            this.$element[0].parentNode || (this.hide(), this.$element = null, this.options = null)
        }, e.prototype.enable = function () {
            this.enabled = !0
        }, e.prototype.disable = function () {
            this.enabled = !1
        }, e.prototype.toggleEnabled = function () {
            this.enabled = !this.enabled
        }, e.prototype.toggle = function (e) {
            var i = e ? t(e.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type) : this;
            i.tip().hasClass("in") ? i.leave(i) : i.enter(i)
        }, e.prototype.destroy = function () {
            clearTimeout(this.timeout), this.hide().$element.off("." + this.type).removeData("bs." + this.type)
        };
        var i = t.fn.tooltip;
        t.fn.tooltip = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.tooltip"), n = "object" == typeof i && i;
                (s || "destroy" != i) && (s || o.data("bs.tooltip", s = new e(this, n)), "string" == typeof i && s[i]())
            })
        }, t.fn.tooltip.Constructor = e, t.fn.tooltip.noConflict = function () {
            return t.fn.tooltip = i, this
        }
    }(jQuery), /* ========================================================================
 * Bootstrap: popover.js v3.1.1
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
    +function (t) {
        "use strict";
        var e = function (t, e) {
            this.init("popover", t, e)
        };
        if (!t.fn.tooltip)throw new Error("Popover requires tooltip.js");
        e.DEFAULTS = t.extend({}, t.fn.tooltip.Constructor.DEFAULTS, {
            placement: "right",
            trigger: "click",
            content: "",
            template: '<div class="popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        }), e.prototype = t.extend({}, t.fn.tooltip.Constructor.prototype), e.prototype.constructor = e, e.prototype.getDefaults = function () {
            return e.DEFAULTS
        }, e.prototype.setContent = function () {
            var t = this.tip(), e = this.getTitle(), i = this.getContent();
            t.find(".popover-title")[this.options.html ? "html" : "text"](e), t.find(".popover-content").empty()[this.options.html ? "string" == typeof i ? "html" : "append" : "text"](i), t.removeClass("fade top bottom left right in"), t.find(".popover-title").html() || t.find(".popover-title").hide()
        }, e.prototype.hasContent = function () {
            return this.getTitle() || this.getContent()
        }, e.prototype.getContent = function () {
            var t = this.$element, e = this.options;
            return t.attr("data-content") || ("function" == typeof e.content ? e.content.call(t[0]) : e.content)
        }, e.prototype.arrow = function () {
            return this.$arrow = this.$arrow || this.tip().find(".arrow")
        }, e.prototype.tip = function () {
            return this.$tip || (this.$tip = t(this.options.template)), this.$tip
        };
        var i = t.fn.popover;
        t.fn.popover = function (i) {
            return this.each(function () {
                var o = t(this), s = o.data("bs.popover"), n = "object" == typeof i && i;
                (s || "destroy" != i) && (s || o.data("bs.popover", s = new e(this, n)), "string" == typeof i && s[i]())
            })
        }, t.fn.popover.Constructor = e, t.fn.popover.noConflict = function () {
            return t.fn.popover = i, this
        }
    }(jQuery), function (t, e) {
    function i() {
        var t = g.elements;
        return "string" == typeof t ? t.split(" ") : t
    }

    function o(t) {
        var e = f[t[p]];
        return e || (e = {}, u++, t[p] = u, f[u] = e), e
    }

    function s(t, i, s) {
        return i || (i = e), l ? i.createElement(t) : (s || (s = o(i)), i = s.cache[t] ? s.cache[t].cloneNode() : d.test(t) ? (s.cache[t] = s.createElem(t)).cloneNode() : s.createElem(t), i.canHaveChildren && !c.test(t) ? s.frag.appendChild(i) : i)
    }

    function n(t, e) {
        e.cache || (e.cache = {}, e.createElem = t.createElement, e.createFrag = t.createDocumentFragment, e.frag = e.createFrag()), t.createElement = function (i) {
            return g.shivMethods ? s(i, t, e) : e.createElem(i)
        }, t.createDocumentFragment = Function("h,f", "return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&(" + i().join().replace(/\w+/g, function (t) {
            return e.createElem(t), e.frag.createElement(t), 'c("' + t + '")'
        }) + ");return n}")(g, e.frag)
    }

    function a(t) {
        t || (t = e);
        var i = o(t);
        if (g.shivCSS && !r && !i.hasCSS) {
            var s, a = t;
            s = a.createElement("p"), a = a.getElementsByTagName("head")[0] || a.documentElement, s.innerHTML = "x<style>article,aside,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}mark{background:#FF0;color:#000}</style>", s = a.insertBefore(s.lastChild, a.firstChild), i.hasCSS = !!s
        }
        return l || n(t, i), t
    }

    var r, l, h = t.html5 || {}, c = /^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i, d = /^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i, p = "_html5shiv", u = 0, f = {};
    !function () {
        try {
            var t = e.createElement("a");
            t.innerHTML = "<xyz></xyz>", r = "hidden"in t;
            var i;
            if (!(i = 1 == t.childNodes.length)) {
                e.createElement("a");
                var o = e.createDocumentFragment();
                i = "undefined" == typeof o.cloneNode || "undefined" == typeof o.createDocumentFragment || "undefined" == typeof o.createElement
            }
            l = i
        } catch (s) {
            l = r = !0
        }
    }();
    var g = {
        elements: h.elements || "abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup main mark meter nav output progress section summary time video",
        version: "3.6.2",
        shivCSS: !1 !== h.shivCSS,
        supportsUnknownElements: l,
        shivMethods: !1 !== h.shivMethods,
        type: "default",
        shivDocument: a,
        createElement: s,
        createDocumentFragment: function (t, s) {
            if (t || (t = e), l)return t.createDocumentFragment();
            for (var s = s || o(t), n = s.frag.cloneNode(), a = 0, r = i(), h = r.length; h > a; a++)n.createElement(r[a]);
            return n
        }
    };
    t.html5 = g, a(e)
}(this, document), /**
 * Downward compatible, touchable dial
 *
 * Version: 1.2.0 (15/07/2012)
 * Requires: jQuery v1.7+
 *
 * Copyright (c) 2012 Anthony Terrien
 * Under MIT and GPL licenses:
 *  http://www.opensource.org/licenses/mit-license.php
 *  http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to vor, eskimoblood, spiffistan, FabrizioC
 */
    function (t) {
        "use strict";
        var e = {}, i = Math.max, o = Math.min;
        e.c = {}, e.c.d = t(document), e.c.t = function (t) {
            return t.originalEvent.touches.length - 1
        }, e.o = function () {
            var i = this;
            this.o = null, this.$ = null, this.i = null, this.g = null, this.v = null, this.cv = null, this.x = 0, this.y = 0, this.$c = null, this.c = null, this.t = 0, this.isInit = !1, this.fgColor = null, this.pColor = null, this.dH = null, this.cH = null, this.eH = null, this.rH = null, this.scale = 1, this.run = function () {
                var e = function (t, e) {
                    var o;
                    for (o in e)i.o[o] = e[o];
                    i.init(), i._configure()._draw()
                };
                if (!this.$.data("kontroled"))return this.$.data("kontroled", !0), this.extend(), this.o = t.extend({
                    min: this.$.data("min") || 0,
                    max: this.$.data("max") || 100,
                    stopper: !0,
                    readOnly: this.$.data("readonly"),
                    cursor: this.$.data("cursor") === !0 && 30 || this.$.data("cursor") || 0,
                    thickness: this.$.data("thickness") || .35,
                    lineCap: this.$.data("linecap") || "butt",
                    width: this.$.data("width") || 200,
                    height: this.$.data("height") || 200,
                    displayInput: null == this.$.data("displayinput") || this.$.data("displayinput"),
                    displayPrevious: this.$.data("displayprevious"),
                    fgColor: this.$.data("fgcolor") || "#87CEEB",
                    inputColor: this.$.data("inputcolor") || this.$.data("fgcolor") || "#87CEEB",
                    inline: !1,
                    step: this.$.data("step") || 1,
                    draw: null,
                    change: null,
                    cancel: null,
                    release: null,
                    error: null
                }, this.o), this.$.is("fieldset") ? (this.v = {}, this.i = this.$.find("input"), this.i.each(function (e) {
                    var o = t(this);
                    i.i[e] = o, i.v[e] = o.val(), o.bind("change", function () {
                        var t = {};
                        t[e] = o.val(), i.val(t)
                    })
                }), this.$.find("legend").remove()) : (this.i = this.$, this.v = this.$.val(), "" == this.v && (this.v = this.o.min), this.$.bind("change", function () {
                    i.val(i._validate(i.$.val()))
                })), !this.o.displayInput && this.$.hide(), this.$c = t('<canvas width="' + this.o.width + 'px" height="' + this.o.height + 'px"></canvas>'), this.c = this.$c[0].getContext ? this.$c[0].getContext("2d") : null, this.c ? (this.$.wrap(t('<div style="' + (this.o.inline ? "display:inline;" : "") + "width:" + this.o.width + "px;height:" + this.o.height + 'px;"></div>')).before(this.$c), this.scale = (window.devicePixelRatio || 1) / (this.c.webkitBackingStorePixelRatio || this.c.mozBackingStorePixelRatio || this.c.msBackingStorePixelRatio || this.c.oBackingStorePixelRatio || this.c.backingStorePixelRatio || 1), 1 !== this.scale && (this.$c[0].width = this.$c[0].width * this.scale, this.$c[0].height = this.$c[0].height * this.scale, this.$c.width(this.o.width), this.$c.height(this.o.height)), this.v instanceof Object ? (this.cv = {}, this.copy(this.v, this.cv)) : this.cv = this.v, this.$.bind("configure", e).parent().bind("configure", e), this._listen()._configure()._xy().init(), this.isInit = !0, this._draw(), this) : void(this.o.error && this.o.error())
            }, this._draw = function () {
                var t = !0, e = document.createElement("canvas");
                e.width = i.o.width * i.scale, e.height = i.o.height * i.scale, i.g = e.getContext("2d"), i.clear(), i.dH && (t = i.dH()), t !== !1 && i.draw(), i.c.drawImage(e, 0, 0), e = null
            }, this._touch = function (t) {
                var o = function (t) {
                    var e = i.xy2val(t.originalEvent.touches[i.t].pageX, t.originalEvent.touches[i.t].pageY);
                    e != i.cv && (i.cH && i.cH(e) === !1 || (i.change(i._validate(e)), i._draw()))
                };
                return this.t = e.c.t(t), o(t), e.c.d.bind("touchmove.k", o).bind("touchend.k", function () {
                    e.c.d.unbind("touchmove.k touchend.k"), i.rH && i.rH(i.cv) === !1 || i.val(i.cv)
                }), this
            }, this._mouse = function (t) {
                var o = function (t) {
                    var e = i.xy2val(t.pageX, t.pageY);
                    e != i.cv && (i.cH && i.cH(e) === !1 || (i.change(i._validate(e)), i._draw()))
                };
                return o(t), e.c.d.bind("mousemove.k", o).bind("keyup.k", function (t) {
                    if (27 === t.keyCode) {
                        if (e.c.d.unbind("mouseup.k mousemove.k keyup.k"), i.eH && i.eH() === !1)return;
                        i.cancel()
                    }
                }).bind("mouseup.k", function () {
                    e.c.d.unbind("mousemove.k mouseup.k keyup.k"), i.rH && i.rH(i.cv) === !1 || i.val(i.cv)
                }), this
            }, this._xy = function () {
                var t = this.$c.offset();
                return this.x = t.left, this.y = t.top, this
            }, this._listen = function () {
                return this.o.readOnly ? this.$.attr("readonly", "readonly") : (this.$c.bind("mousedown", function (t) {
                    t.preventDefault(), i._xy()._mouse(t)
                }).bind("touchstart", function (t) {
                    t.preventDefault(), i._xy()._touch(t)
                }), this.listen()), this
            }, this._configure = function () {
                return this.o.draw && (this.dH = this.o.draw), this.o.change && (this.cH = this.o.change), this.o.cancel && (this.eH = this.o.cancel), this.o.release && (this.rH = this.o.release), this.o.displayPrevious ? (this.pColor = this.h2rgba(this.o.fgColor, "0.4"), this.fgColor = this.h2rgba(this.o.fgColor, "0.6")) : this.fgColor = this.o.fgColor, this
            }, this._clear = function () {
                this.$c[0].width = this.$c[0].width
            }, this._validate = function (t) {
                return ~~((0 > t ? -.5 : .5) + t / this.o.step) * this.o.step
            }, this.listen = function () {
            }, this.extend = function () {
            }, this.init = function () {
            }, this.change = function () {
            }, this.val = function () {
            }, this.xy2val = function () {
            }, this.draw = function () {
            }, this.clear = function () {
                this._clear()
            }, this.h2rgba = function (t, e) {
                var i;
                return t = t.substring(1, 7), i = [parseInt(t.substring(0, 2), 16), parseInt(t.substring(2, 4), 16), parseInt(t.substring(4, 6), 16)], "rgba(" + i[0] + "," + i[1] + "," + i[2] + "," + e + ")"
            }, this.copy = function (t, e) {
                for (var i in t)e[i] = t[i]
            }
        }, e.Dial = function () {
            e.o.call(this), this.startAngle = null, this.xy = null, this.radius = null, this.lineWidth = null, this.cursorExt = null, this.w2 = null, this.PI2 = 2 * Math.PI, this.extend = function () {
                this.o = t.extend({
                    bgColor: this.$.data("bgcolor") || "#EEEEEE",
                    angleOffset: this.$.data("angleoffset") || 0,
                    angleArc: this.$.data("anglearc") || 360,
                    inline: !0
                }, this.o)
            }, this.val = function (t) {
                return null == t ? this.v : (this.cv = this.o.stopper ? i(o(t, this.o.max), this.o.min) : t, this.v = this.cv, this.$.val(this.v), this._draw(), void 0)
            }, this.xy2val = function (t, e) {
                var s, n;
                return s = Math.atan2(t - (this.x + this.w2), -(e - this.y - this.w2)) - this.angleOffset, this.angleArc != this.PI2 && 0 > s && s > -.5 ? s = 0 : 0 > s && (s += this.PI2), n = ~~(.5 + s * (this.o.max - this.o.min) / this.angleArc) + this.o.min, this.o.stopper && (n = i(o(n, this.o.max), this.o.min)), n
            }, this.listen = function () {
                var e, s, n = this, a = function (t) {
                    t.preventDefault();
                    var e = t.originalEvent, i = e.detail || e.wheelDeltaX, o = e.detail || e.wheelDeltaY, s = parseInt(n.$.val()) + (i > 0 || o > 0 ? n.o.step : 0 > i || 0 > o ? -n.o.step : 0);
                    n.cH && n.cH(s) === !1 || n.val(s)
                }, r = 1, l = {37: -n.o.step, 38: n.o.step, 39: n.o.step, 40: -n.o.step};
                this.$.bind("keydown", function (a) {
                    var h = a.keyCode;
                    if (h >= 96 && 105 >= h && (h = a.keyCode = h - 48), e = parseInt(String.fromCharCode(h)), isNaN(e) && (13 !== h && 8 !== h && 9 !== h && 189 !== h && a.preventDefault(), t.inArray(h, [37, 38, 39, 40]) > -1)) {
                        a.preventDefault();
                        var c = parseInt(n.$.val()) + l[h] * r;
                        n.o.stopper && (c = i(o(c, n.o.max), n.o.min)), n.change(c), n._draw(), s = window.setTimeout(function () {
                            r *= 2
                        }, 30)
                    }
                }).bind("keyup", function () {
                    isNaN(e) ? s && (window.clearTimeout(s), s = null, r = 1, n.val(n.$.val())) : n.$.val() > n.o.max && n.$.val(n.o.max) || n.$.val() < n.o.min && n.$.val(n.o.min)
                }), this.$c.bind("mousewheel DOMMouseScroll", a), this.$.bind("mousewheel DOMMouseScroll", a)
            }, this.init = function () {
                (this.v < this.o.min || this.v > this.o.max) && (this.v = this.o.min), this.$.val(this.v), this.w2 = this.o.width / 2, this.cursorExt = this.o.cursor / 100, this.xy = this.w2 * this.scale, this.lineWidth = this.xy * this.o.thickness, this.lineCap = this.o.lineCap, this.radius = this.xy - this.lineWidth / 2, this.o.angleOffset && (this.o.angleOffset = isNaN(this.o.angleOffset) ? 0 : this.o.angleOffset), this.o.angleArc && (this.o.angleArc = isNaN(this.o.angleArc) ? this.PI2 : this.o.angleArc), this.angleOffset = this.o.angleOffset * Math.PI / 180, this.angleArc = this.o.angleArc * Math.PI / 180, this.startAngle = 1.5 * Math.PI + this.angleOffset, this.endAngle = 1.5 * Math.PI + this.angleOffset + this.angleArc;
                var t = i(String(Math.abs(this.o.max)).length, String(Math.abs(this.o.min)).length, 2) + 2;
                this.o.displayInput && this.i.css({
                    width: (this.o.width / 2 + 4 >> 0) + "px",
                    height: (this.o.width / 3 >> 0) + "px",
                    position: "absolute",
                    "vertical-align": "middle",
                    "margin-top": (this.o.width / 3 >> 0) + "px",
                    "margin-left": "-" + (3 * this.o.width / 4 + 2 >> 0) + "px",
                    border: 0,
                    background: "none",
                    font: "bold " + (this.o.width / t >> 0) + "px Arial",
                    "text-align": "center",
                    color: this.o.inputColor || this.o.fgColor,
                    padding: "0px",
                    "-webkit-appearance": "none"
                }) || this.i.css({width: "0px", visibility: "hidden"})
            }, this.change = function (t) {
                this.cv = t, this.$.val(t)
            }, this.angle = function (t) {
                return (t - this.o.min) * this.angleArc / (this.o.max - this.o.min)
            }, this.draw = function () {
                var t, e, i = this.g, o = this.angle(this.cv), s = this.startAngle, n = s + o, a = 1;
                i.lineWidth = this.lineWidth, i.lineCap = this.lineCap, this.o.cursor && (s = n - this.cursorExt) && (n += this.cursorExt), i.beginPath(), i.strokeStyle = this.o.bgColor, i.arc(this.xy, this.xy, this.radius, this.endAngle, this.startAngle, !0), i.stroke(), this.o.displayPrevious && (e = this.startAngle + this.angle(this.v), t = this.startAngle, this.o.cursor && (t = e - this.cursorExt) && (e += this.cursorExt), i.beginPath(), i.strokeStyle = this.pColor, i.arc(this.xy, this.xy, this.radius, t, e, !1), i.stroke(), a = this.cv == this.v), i.beginPath(), i.strokeStyle = a ? this.o.fgColor : this.fgColor, i.arc(this.xy, this.xy, this.radius, s, n, !1), i.stroke()
            }, this.cancel = function () {
                this.val(this.v)
            }
        }, t.fn.dial = t.fn.knob = function (i) {
            return this.each(function () {
                var o = new e.Dial;
                o.o = i, o.$ = t(this), o.run()
            }).parent()
        }
    }(jQuery), function () {
    for (var t, e = function () {
    }, i = ["assert", "clear", "count", "debug", "dir", "dirxml", "error", "exception", "group", "groupCollapsed", "groupEnd", "info", "log", "markTimeline", "profile", "profileEnd", "table", "time", "timeEnd", "timeStamp", "trace", "warn"], o = i.length, s = window.console = window.console || {}; o--;)t = i[o], s[t] || (s[t] = e)
}(), function () {
    $(document).ready(function () {
        return $("[data-toggle=tooltip]").tooltip(), $("[data-toggle=popover]").popover(), $(".dropdown-toggle").dropdown(), $(".dropdown.hover").hover(function () {
            return $(this).find(".dropdown-menu").stop(!0, !0).fadeIn()
        }, function () {
            return $(this).find(".dropdown-menu").stop(!0, !0).delay(100).fadeOut()
        }),  $("[data-toggle=toolbar-tooltip]").tooltip({placement: "bottom"}), $(".knob").knob()
    })
}.call(this);