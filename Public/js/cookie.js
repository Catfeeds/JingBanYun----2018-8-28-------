
	/*!
 * cookiejs v1.0.8
 * Copyright (c) 2016 kenny wang
 * Licensed under the MIT license.
 * 
 * Local storage cookie package provides a simple API
 * https://github.com/jaywcjlove/cookie.js
 * 
 */
(function(f) {
    if (typeof exports === "object" && typeof module !== "undefined") {
        module.exports = f();
    } else if (typeof define === "function" && define.amd) {
        define([], f);
    } else {
        var g;
        if (typeof window !== "undefined") {
            g = window;
        } else if (typeof global !== "undefined") {
            g = global;
        } else if (typeof self !== "undefined") {
            g = self;
        } else {
            g = this;
        }
        g.cookie = f();
    }
})(function() {
    var define, module, exports;
    var getKeys = Object.names || function(obj) {
        var names = [], name = "";
        for (name in obj) {
            if (obj.hasOwnProperty(name)) names.push(name);
        }
        return names;
    };
    function isPlainObject(value) {
        return !!value && Object.prototype.toString.call(value) === "[object Object]";
    }
    function isArray(value) {
        return value instanceof Array;
    }
    function toArray(value) {
        return Array.prototype.slice.call(value);
    }
    function Cookie() {
        if (!(this instanceof Cookie)) {
            return new Cookie();
        }
    }
    Cookie.prototype = {
        get: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(";");
            //��cookie�ָ����    
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                //ȡ���ַ���    
                while (c.charAt(0) == " ") {
                    //�ж�һ���ַ�����û��ǰ���ո�    
                    c = c.substring(1, c.length);
                }
                //�����������Ҫ��name
                if (c.indexOf(nameEQ) == 0) {
                    return unescape(c.substring(nameEQ.length, c.length));
                }
            }
            return false;
        },
        set: function(name, value, options) {
            if (isPlainObject(name)) {
                for (var k in name) {
                    if (name.hasOwnProperty(k)) this.set(k, name[k], value);
                }
            } else {
                var opt = isPlainObject(options) ? options : {
                    expires: options
                }, expires = opt.expires !== undefined ? opt.expires : "", expiresType = typeof expires, path = opt.path !== undefined ? ";path=" + opt.path : ";path=/", domain = opt.domain ? ";domain=" + opt.domain : "", secure = opt.secure ? ";secure" : "";
                //����ʱ��
                if (expiresType === "string" && expires !== "") expires = new Date(expires); else if (expiresType === "number") expires = new Date(+new Date() + 1e3 * 60 * 60 * 24 * expires);
                if (expires !== "" && "toGMTString" in expires) expires = ";expires=" + expires.toGMTString();
                document.cookie = name + "=" + escape(value) + expires + path + domain + secure;
            }
        },
        remove: function(names) {
            names = isArray(names) ? names : toArray(arguments);
            for (var i = 0, l = names.length; i < l; i++) {
                this.set(names[i], "", -1);
            }
            return names;
        },
        clear: function(name) {
            return this.remove(getKeys(this.all()));
        },
        all: function() {
            if (document.cookie === "") return {};
            var cookies = document.cookie.split("; "), result = {};
            for (var i = 0, l = cookies.length; i < l; i++) {
                var item = cookies[i].split("=");
                result[unescape(item[0])] = unescape(item[1]);
            }
            return result;
        }
    };
    var cookie = function(name, value, options) {
        var argm = arguments;
        if (argm.length === 0) return Cookie().clear();
        if (argm.length === 2 && !value) return Cookie().clear(name);
        if (typeof name == "string" && !value) return Cookie().get(name);
        if (isPlainObject(name) || argm.length > 1 && name && value) return Cookie().set(name, value, options);
        if (value === null) return Cookie().remove(name);
        return Cookie().all();
    };
    for (var a in Cookie.prototype) cookie[a] = Cookie.prototype[a];
    return cookie;
});
