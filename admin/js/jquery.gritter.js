/*
 * Gritter for jQuery
 * Copyright (c) Jordan Boesch
 * Dual licensed under the MIT and GPL licenses.
 */
jQuery(document).ready(function(t) {
    Gritter = {
        fade_speed: 2e3,
        timer_stay: 6e3,
        _custom_timer: 0,
        _item_count: 0,
        _tpl_close: '<div class="gritter-close"></div>',
        _tpl_item: '<div id="gritter-item-[[number]]" class="gritter-item-wrapper" style="display:none"><div class="[[class_name]] gritter-item">[[image]]<div class="gritter-with-image"><span class="gritter-title">[[username]]</span><p>[[text]]</p></div><div style="clear:both"></div></div></div>',
        _tpl_wrap: '<div id="gritter-notice-wrapper"></div>',
        add: function(e, i, r, n, o, s) {
            this.verifyWrapper();
            var a = this._tpl_item;
            this._item_count++, this._custom_timer = 0, o && (this._custom_timer = o);
            var c = "" != r ? '<img src="' + r + '" class="gritter-image" />' : "",
                s = "" == s ? "" : s;
            a = this.str_replace(["[[username]]", "[[text]]", "[[image]]", "[[number]]", "[[time_alive]]", "[[class_name]]"], [e, i, c, this._item_count, o, s], a), t("#gritter-notice-wrapper").append(a);
            var m = t("#gritter-item-" + this._item_count),
                p = this._item_count;
            return m.fadeIn(), n || this.setFadeTimer(m, p), t(m).hover(function() {
                n || Gritter.restoreItemIfFading(this, p), Gritter.hoveringItem(this)
            }, function() {
                n || Gritter.setFadeTimer(this, p), Gritter.unhoveringItem(this)
            }), p
        },
        countRemoveWrapper: function() {
            0 == t(".gritter-item-wrapper").length && t("#gritter-notice-wrapper").remove()
        },
        fade: function(e) {
            t(e).animate({
                opacity: 0
            }, Gritter.fade_speed, function() {
                t(e).animate({
                    height: 0
                }, 300, function() {
                    t(e).remove(), Gritter.countRemoveWrapper()
                })
            })
        },
        hoveringItem: function(e) {
            t(e).addClass("hover"), t(e).find("img").length ? t(e).find("img").before(this._tpl_close) : t(e).find("span").before(this._tpl_close), t(e).find(".gritter-close").click(function() {
                Gritter.remove(this)
            })
        },
        remove: function(e) {
            t(e).parents(".gritter-item-wrapper").fadeOut("medium", function() {
                t(this).remove()
            }), this.countRemoveWrapper(), cookie.set("alerts", "off", {
                expires: 2,
                path: jQuery.apanel
            })
        },
        removeSpecific: function(e, i) {
            var r = t("#gritter-item-" + e);
            if ("object" == typeof i) {
                if (i.fade) {
                    var n = this.fade_speed;
                    i.speed && (n = i.speed), r.fadeOut(n)
                }
            } else r.remove();
            this.countRemoveWrapper()
        },
        restoreItemIfFading: function(e, i) {
            window.clearTimeout(Gritter["_int_id_" + i]), t(e).stop().css({
                opacity: 1
            })
        },
        setFadeTimer: function(t, e) {
            var i = this._custom_timer ? this._custom_timer : this.timer_stay;
            Gritter["_int_id_" + e] = window.setTimeout(function() {
                Gritter.fade(t)
            }, i)
        },
        stop: function() {
            t("#gritter-notice-wrapper").fadeOut(function() {
                t(this).remove()
            })
        },
        str_replace: function(t, e, i, r) {
            var n = 0,
                o = 0,
                s = "",
                a = "",
                c = 0,
                m = 0,
                p = [].concat(t),
                d = [].concat(e),
                f = i,
                u = d instanceof Array,
                l = f instanceof Array;
            for (f = [].concat(f), r && (this.window[r] = 0), n = 0, c = f.length; c > n; n++)
                if ("" !== f[n])
                    for (o = 0, m = p.length; m > o; o++) s = f[n] + "", a = u ? void 0 !== d[o] ? d[o] : "" : d[0], f[n] = s.split(p[o]).join(a), r && f[n] !== s && (this.window[r] += (s.length - f[n].length) / p[o].length);
            return l ? f : f[0]
        },
        unhoveringItem: function(e) {
            t(e).removeClass("hover"), t(e).find(".gritter-close").remove()
        },
        verifyWrapper: function() {
            0 == t("#gritter-notice-wrapper").length && t("body").append(this._tpl_wrap)
        }
    }, t.gritter = {}, t.gritter.add = function(t) {
        try {
            if (!t.title || !t.text) throw "Missing_Fields"
        } catch (e) {
            "Missing_Fields" == e && alert('Gritter Error: You need to fill out the first 2 params: "title" and "text"')
        }
        var i = Gritter.add(t.title, t.text, t.image || "", t.sticky || !1, t.time || "", t.iclass || "");
        return i
    }, t.gritter.remove = function(t, e) {
        Gritter.removeSpecific(t, e || "")
    }, t.gritter.removeAll = function() {
        Gritter.stop()
    }
});