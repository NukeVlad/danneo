(function($) {
    $.fn.tooltip = function() {
        var delay = 300,
            show, o = {
                xd: 15,
                yd: 19,
                top: 0,
                left: 0,
                x: 0,
                y: 0,
                mx: 0,
                my: 0,
                mc: 0
            };
        $("body").append('<div id="tooltip" class="tip-top" style="display:none;position:absolute;"></div>');
        this.each(function() {
            var h = ($(this).is("img")) ? $(this).prop("alt") : $(this).prop("title");
            if (!$(this).hasClass("notooltip") && h.length > 0) {
                $(this).bind("mouseover mousemove", function(e) {
                    if (h) {
                        $("#tooltip").html(h);
                        ($(this).is("img")) ? $(this).prop("alt", ""): $(this).prop("title", "");
                        if ($("#tooltip").width() > 300) {
                            $("#tooltip").css({
                                "width": "300px"
                            });
                        }

						if (document.documentMode) {
							o.mc = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY")[0];
						} else {
							o.mc = document.getElementsByTagName("BODY")[0];
						}
                        o.left = e.pageX + o.xd;
                        o.top = e.pageY + o.yd;
                        o.mx = window.event ? event.clientX + o.mc.scrollLeft : e.pageX;
                        o.my = window.event ? event.clientY + o.mc.scrollTop : e.pageY;
                        if ((o.mx + $("#tooltip").width() + o.xd) > (o.mc.clientWidth ? o.mc.clientWidth + o.mc.scrollLeft : window.innerWidth + window.pageXOffset) - 35) {
                            o.left = (o.mx - $("#tooltip").width() - 15);
                        }
                        if ((o.my + $("#tooltip").height() + o.yd) > (o.mc.innerHeight ? window.innerHeight + window.pageYOffset : o.mc.clientHeight + o.mc.scrollTop) - 50) {
                            o.top = (o.my - $("#tooltip").height() - 33);
                        }
                        if (e.type == "mouseover") {
                            show = window.setTimeout(function() {
                                $("#tooltip").fadeIn(300).show().css({
                                    "top": o.top + "px",
                                    "left": o.left + "px"
                                });
                            }, delay);
                        }
                        if (e.type == "mousemove") {
                            $("#tooltip").css({
                                "top": o.top + "px",
                                "left": o.left + "px"
                            });
                        }
                    }
                });
                $(this).mouseout(function(e) {
                    $("#tooltip").hide();
                    $("#tooltip").css({
                        "width": ""
                    });
                    window.clearTimeout(show);
                    ($(this).is("img")) ? $(this).prop("alt", h): $(this).prop("title", h);
                });
            }
        });
    };
})(jQuery);