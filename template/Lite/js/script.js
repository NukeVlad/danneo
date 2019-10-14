/*
 * Control code javascript for Lite
 * Project:      Danneo CMS : Content management system
 * File:         template/Lite/js/script.js
 * @copyright    Copyright (C) 2005-2019 Danneo Team.
 * @link         http://danneo.ru
 * @license      http://www.gnu.org/licenses/gpl-2.0.html
 */

// No Script
$('html').addClass('js');

/**
 * initiate
 */
$(function(){
    /**
     * Selected
     */
	$('.readonly').focus(function () {
		$(this).select();
	}).mouseup(function(e){
		e.preventDefault();
	});

    /**
     * ToolTips
     */
    $("img, label, input, a, li, span, strong, time, button").tooltip();

    /**
     * Broken
     */
    $.broken = function(url) {
        window.location.href = url;
    }

    /**
     * Colorbox
     */
    $(".media-view").colorbox({
        transition  : "elastic",
        scalePhotos : true,
		scrolling: false,
        maxHeight   : "92%",
        maxWidth    : "94%",
        fixed: true,
        title: function () {
            var show = $(this).attr("data-title");
            return show;
        }
		/*,
        onLoad: function () {
            $("#cboxClose").hide();
        },
        onComplete: function () {
            $("#cboxClose").hide();
        }*/
    });

    $(".form-view").colorbox({
        transition  : "elastic",
		inline: true,
        scalePhotos : true,
		scrolling: false,
        maxHeight   : "92%",
        maxWidth    : "94%",
        initialWidth:  '540px',
        initialHeight: '270px',
        fixed: true,
        title: false,
				showClose: true,
				overlayClose: false,
		onLoad: function() {
			$('#cboxClose').show();
		}
    },$.basketlite);

    /**
     * Content columns height
     * Media, Photos
     */
    function media(){
		$(".media").css("height", "auto");
		var media = $(".media").height();
		var media = media + 5;
		$(".media").css('height', media + 'px');
    }
	$(window).on('load resize', media);
/*
    function article(){
		$("article[role=article]").css("height", "auto");
		var article = $("article[role=article]").height();
		var article = article + 5;
		$("article[role=article]").css('height', article + 'px');
    }
	$(window).on('load resize', article);
*/
    /**
     * Colorbox language
     * language: Russian (ru)
     */
    $.extend($.colorbox.settings, {
        current       : "Изображение {current} из {total}",
        previous      : "Предыдущее",
        next          : "Следующее",
        close         : "Закрыть",
        xhrError      : "Не удалось загрузить содержимое.",
        imgError      : "Не удалось загрузить изображение.",
        slideshowStart: "Начать слайд-шоу",
        slideshowStop : "Остановить слайд-шоу"
    });

	$(document).on('mousedown', '.disabled', function (e) {
		$(this).hide();
		var BottomElement = document.elementFromPoint(e.clientX, e.clientY);
		$(this).show();
		$(BottomElement).mousedown();
		return false;
	});
});

jQuery.basketlite = function()
{
	$("#basket-order").show();
    $("#submit").click(function()
    {
         $('#basket-order input, textarea').removeClass('error-input').addClass('width');
         $error = false;
         $.check = new Array();
         $.check['names'] = new Array('names',25);
         $.check['phone'] = new Array('phone',0);
         for(i in $.check) {
             var id = $.check[i][0], val = $.check[i][1];
             if (val == 0) {
                if ($("#" + id) != "undefined" && $("#" + id).val().length == 0) {
                 	$error = true;
                 	$("#" + id).removeClass('width').addClass('error-input');
                 	$("#" + id).focus(function(){
                      $(this).removeClass('error-input').addClass('width');
                    });
                }
             }
             if (val > 0) {
                if ($("#" + id) != "undefined" && $("#" + id).val().length == 0 || $("#" + id) != "undefined" && $("#" + id).val().length > val) {
                 	$error = true;
                    $("#" + id).removeClass('width').addClass('error-input');
                    $("#" + id).focus(function(){
                      $(this).removeClass('error-input').addClass('width');
                    });
                }
             }
         }
         if ($error) {
         	 return false;
         }
		$("#basket-order").hide();
		$('#sendbox').show();
        var data = $('#basket-order').serialize() + '&ajax=1';
        $.ajax({
            type: "POST",
            cache : false,
            url : $.url + '/',
            data : data,
            error : function (msg) { },
            success : function (d)
            {
             $("#sendbox").hide();
                if (d.length > 0)
                {
                    $.colorbox({
						transition  : "elastic",
						scrolling: false,
						maxHeight   : "92%",
						maxWidth    : "94%",
						initialWidth:  '640px',
						initialHeight: '640px',
						fixed: true,
                        html:d
                    },$.basketlite)
                }
            }
        });
        //$.fn.colorbox.close();
		//setTimeout(function() {window.location.reload();}, 5000);
        return false;
    });
};

/**
 * Functions of the menu
 */
$(function(){

    // Menu active link
    function activeMenu(str) {
		var arr = str.split(':');
		var menu = arr[0].split(',');
		$.each(menu, function (e, b) {
			var css = '.' + b + ' li a';
			$(css).each(function () {
				if (this.href == location.href) {
					$(this).addClass("active");
				};
			});
			if (arr[1] == 'tag') {
				$(css + '.active').replaceWith(function(index, oldHTML) {
					return $('<'+ arr[2] +' class="active">').html(oldHTML);
				});
			}
		});
    }

	// init
	if (typeof actMenu !== 'undefined' && actMenu !== "") {
		activeMenu(actMenu);
	}
});

( function ($) {
    jQuery.fn.autoTextarea = function (options) {
		var settings = jQuery.extend({
			min: 41,
			max: '',
			blur: false
		}, options);

		var name = this;
		name.css({ 'height' : settings.min + 'px' });
		var auto = name.css( 'height', 'auto' ).height();
			auto = (settings.max == '') ? auto : settings.max;

		name.on({
			focus: function () {
				$(this).finish().animate({
					height: auto
				});
			},
			blur: function () {
				if ($.trim(name.val()) == '' && settings.blur == true) {
					name.finish().animate({
						height: settings.min
					});
				}
			}
		});
		$(window).on('load', function () {
			if ($.trim(name.val()) != '') {
				name.css({ 'height' : auto });
			} else {
				name.css({ 'height' : settings.min + 'px' });
			}
		});

        return name;
    };
})(jQuery);

/**
 * Tool Tip
 */
(function($){
     $.fn.tooltip = function(){
          var delay = 300, show, o = {xd : 15, yd : 19, top : 0, left : 0, x : 0, y : 0, mx : 0, my : 0, mc : 0};
          $('body').append('<div id="tooltip" class="tip-top" style="display:none;position:absolute;"></div>');
          this.each(function(){
                var h = ($(this).is('img')) ? $(this).prop('alt') : $(this).prop('title');
                if(!$(this).hasClass('notooltip') && h.length > 0){
                $(this).bind('mouseover mousemove', function(e){
                    if (h) {
                        $('#tooltip').html(h);
                        ($(this).is("img")) ? $(this).prop('alt','') : $(this).prop('title','');
                        if ($('#tooltip').width() > 300) {
                        	$('#tooltip').css({'width' : '300px'});
                        }
                        o.mc = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat") ? "HTML":"BODY")[0];
                        o.left = e.pageX + o.xd;
                        o.top = e.pageY + o.yd;
                        o.mx = window.event ? event.clientX + o.mc.scrollLeft : e.pageX;
                        o.my = window.event ? event.clientY + o.mc.scrollTop : e.pageY;
                        if ((o.mx + $('#tooltip').width() + o.xd)  > (o.mc.clientWidth ? o.mc.clientWidth + o.mc.scrollLeft : window.innerWidth + window.pageXOffset) - 35) {
                            o.left = (o.mx - $('#tooltip').width() - 15);
                        }
                        if ((o.my + $('#tooltip').height() + o.yd) > (o.mc.innerHeight ? window.innerHeight + window.pageYOffset :o.mc.clientHeight + o.mc.scrollTop) - 50) {
                            o.top = (o.my - $('#tooltip').height() - 33);
                        }
                        if (e.type == 'mouseover') {
                            show = window.setTimeout(function() {
                                $('#tooltip').fadeIn(300).show().css({'top':o.top+'px','left':o.left+'px'});
                            }, delay);
                        }
                        if (e.type == 'mousemove') {
                            $('#tooltip').css({'top':o.top+'px','left':o.left+'px'});
                        }
                    }
               });
               $(this).mouseout(function(e){
                    $('#tooltip').hide();
                    $('#tooltip').css({'width':''});
                    window.clearTimeout(show);
                    ($(this).is("img")) ? $(this).prop('alt',h) : $(this).prop('title',h);
               });
               }
          });
     };
})(jQuery);

// Redirect on site Danneo Team
$(function() {
    $('.dncopy').click( function() {
        this.href='http://danneo.ru/';
        window.open(this.href);
        this.href=location;
        return false;
    });
});

/* HTML5 Placeholder jQuery Plugin - v2.3.1
 * Copyright (c)2015 Mathias Bynens
 * 2015-12-16
 */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a("object"==typeof module&&module.exports?require("jquery"):jQuery)}(function(a){function b(b){var c={},d=/^jQuery\d+$/;return a.each(b.attributes,function(a,b){b.specified&&!d.test(b.name)&&(c[b.name]=b.value)}),c}function c(b,c){var d=this,f=a(this);if(d.value===f.attr(h?"placeholder-x":"placeholder")&&f.hasClass(n.customClass))if(d.value="",f.removeClass(n.customClass),f.data("placeholder-password")){if(f=f.hide().nextAll('input[type="password"]:first').show().attr("id",f.removeAttr("id").data("placeholder-id")),b===!0)return f[0].value=c,c;f.focus()}else d==e()&&d.select()}function d(d){var e,f=this,g=a(this),i=f.id;if(!d||"blur"!==d.type||!g.hasClass(n.customClass))if(""===f.value){if("password"===f.type){if(!g.data("placeholder-textinput")){try{e=g.clone().prop({type:"text"})}catch(j){e=a("<input>").attr(a.extend(b(this),{type:"text"}))}e.removeAttr("name").data({"placeholder-enabled":!0,"placeholder-password":g,"placeholder-id":i}).bind("focus.placeholder",c),g.data({"placeholder-textinput":e,"placeholder-id":i}).before(e)}f.value="",g=g.removeAttr("id").hide().prevAll('input[type="text"]:first').attr("id",g.data("placeholder-id")).show()}else{var k=g.data("placeholder-password");k&&(k[0].value="",g.attr("id",g.data("placeholder-id")).show().nextAll('input[type="password"]:last').hide().removeAttr("id"))}g.addClass(n.customClass),g[0].value=g.attr(h?"placeholder-x":"placeholder")}else g.removeClass(n.customClass)}function e(){try{return document.activeElement}catch(a){}}var f,g,h=!1,i="[object OperaMini]"===Object.prototype.toString.call(window.operamini),j="placeholder"in document.createElement("input")&&!i&&!h,k="placeholder"in document.createElement("textarea")&&!i&&!h,l=a.valHooks,m=a.propHooks,n={};j&&k?(g=a.fn.placeholder=function(){return this},g.input=!0,g.textarea=!0):(g=a.fn.placeholder=function(b){var e={customClass:"placeholder"};return n=a.extend({},e,b),this.filter((j?"textarea":":input")+"["+(h?"placeholder-x":"placeholder")+"]").not("."+n.customClass).not(":radio, :checkbox, [type=hidden]").bind({"focus.placeholder":c,"blur.placeholder":d}).data("placeholder-enabled",!0).trigger("blur.placeholder")},g.input=j,g.textarea=k,f={get:function(b){var c=a(b),d=c.data("placeholder-password");return d?d[0].value:c.data("placeholder-enabled")&&c.hasClass(n.customClass)?"":b.value},set:function(b,f){var g,h,i=a(b);return""!==f&&(g=i.data("placeholder-textinput"),h=i.data("placeholder-password"),g?(c.call(g[0],!0,f)||(b.value=f),g[0].value=f):h&&(c.call(b,!0,f)||(h[0].value=f),b.value=f)),i.data("placeholder-enabled")?(""===f?(b.value=f,b!=e()&&d.call(b)):(i.hasClass(n.customClass)&&c.call(b),b.value=f),i):(b.value=f,i)}},j||(l.input=f,m.value=f),k||(l.textarea=f,m.value=f),a(function(){a(document).delegate("form","submit.placeholder",function(){var b=a("."+n.customClass,this).each(function(){c.call(this,!0,"")});setTimeout(function(){b.each(d)},10)})}),a(window).bind("beforeunload.placeholder",function(){var b=!0;try{"javascript:void(0)"===document.activeElement.toString()&&(b=!1)}catch(c){}b&&a("."+n.customClass).each(function(){this.value=""})}))});