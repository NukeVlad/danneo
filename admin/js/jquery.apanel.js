jQuery.platform = function() {
    var data = $('#ajaxplatform').serialize();
    $.ajax({
        cache: false,
        type: "POST",
        url: jQuery.apanel + 'includes/ajax.php',
        dataType: 'json',
        data: data,
        error: function(msg) {},
        success: function(data) {
            location.reload();
            $("#platforms option[value=" + data['id'] + "]").attr("selected", "selected");
        }
    });
}

jQuery.pagesclone = function() {
    var data = $('#ajaxpages').serialize();
    $.ajax({
        cache: false,
        type: "POST",
        url: $.apanel + 'includes/ajax.php',
        dataType: 'json',
        data: data,
        error: function(msg) {},
        success: function(data) {
            $("#ajaxpages option[value=" + data['id'] + "]").attr("selected", "selected");
            var url = new Url(location.href);
            url.query.pl = data['id'];
            window.location.href = url;
        }
    });
}

jQuery.translit = function(gui, obj, sess) {
    var str = $('#' + gui).val();
    $.ajax({
        cache: false,
        type: "POST",
        url: $.apanel + 'includes/ajax.php',
        data: {
            dn: 'translit',
            title: str,
            ops: sess
        },
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $("#" + obj).val(data);
            }
        }
    });
}

jQuery.translittag = function(gui, obj, form, area, sess) {
    var str = $("form[id=" + form + "] #" + area + " #" + gui).val();
    $.ajax({
        cache: false,
        type: "POST",
        url: $.apanel + 'includes/ajax.php',
        data: {
            dn: 'translit',
            title: str,
            ops: sess
        },
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $("form[id=" + form + "] #" + area + " #" + obj).val(data);
            }
        }
    });
}

jQuery.pagescroll = function() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
        xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
        yScroll = document.documentElement.scrollTop;
        xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {
        yScroll = document.body.scrollTop;
        xScroll = document.body.scrollLeft;
    }
    var arrayPageScroll = {
        'xScroll': xScroll,
        'yScroll': yScroll
    };
    return arrayPageScroll;
}

jQuery.reposit = function(layer) {
    var pageScroll = jQuery.pagescroll();
    var nHeight = parseInt($(layer).height(), 10);
    var nWidth = parseInt($(layer).width(), 10);
    var nTop = pageScroll.yScroll + ($(window).height() - nHeight) / 2;
    var nLeft = pageScroll.xScroll + ($(window).width() - nWidth) / 2;
    $(layer).css({
        left: nLeft,
        top: nTop
    });
    //$(layer).animate( {left:nLeft,top:nTop},{duration:100 } );
    $("#bg-overlay").css({
        width: $(document).width(),
        height: $(document).height()
    });
}

jQuery.createoverlay = function() {
    $('body').append('<div id="bg-overlay"></div><div id="bg-overlay-content"><img src="template/' + jQuery.template + '/images/loading.gif"></div>');
    $("#bg-overlay,#bg-overlay-content").hide();
    var $body = $(navigator.userAgent.indexOf('MSIE 6') >= 0 ? document.body : document);
    $('#bg-overlay').css({
        width: $body.width(),
        height: $body.height(),
        position: 'absolute',
        top: '0px',
        left: '0px',
        'opacity': 0.6
    });
    jQuery.reposit('#bg-overlay-content');
    $(window).scroll(function() {
        jQuery.reposit('#bg-overlay-content');
    });
    $("#bg-overlay").click(function() {
        $("#bg-overlay,#bg-overlay-content").hide();
    });
}

jQuery.system = function() {
    $(".button").hover(function() {
            $(this).toggleClass("button").toggleClass("active-button");
        },
        function() {
            $(this).toggleClass("active-button").toggleClass("button");
        });
    $("input[type=text], textarea").focus(function() {
        $(this).toggleClass("").toggleClass("active-input");
    });
    $("input[type=text], textarea").blur(function() {
        $(this).toggleClass("active-input").toggleClass("");
    });
    $("#checkboxall").click(function() {
        var checked_status = this.checked;
        $("input[type=checkbox]").each(function() {
            this.checked = checked_status;
        });
    });
};

jQuery.modcheck = function(id) {
    var status = $('#' + id + 'checkbox').is(':checked');
    $("#" + id + "_toggle input[type=checkbox]").each(function() {
        this.checked = status;
    });
}

jQuery.ajaxget = function(url) {
    $("#ajaxbox").hide();
    $.colorbox({
        onLoad: function() {
            $('#cboxClose').hide();
        },
        opacity: '0',
        initialWidth: '40px',
        initialHeight: '40px',
        width: '63px',
        height: '69px',
        html: '&nbsp;'
    });
    $("#cboxLoadingOverlay").css({
        "background": "#F5F9FC",
        opacity: 0.9
    });
    $.ajax({
        url: url,
        data: {},
        success: function(data) {
            $("#ajaxbox").html(data).show();
            $.fn.colorbox.close();
            $('#cboxClose').hide();
            $("#checkboxall").click(function() {
                var checked_status = this.checked;
                $("input[type=checkbox]").each(function() {
                    this.checked = checked_status;
                });
            });
        }
    });
}

jQuery.ajaxeditor = function(url, id, w) {
    $("#ajaxpanel").hide();
    if ($("#ajaxpanel").length == 0) {
        $("body").append("<div id='ajaxpanel' class='ajaxpanel'></div>");
    }
    var obj = $("#" + id),
        p = obj.position();
	if ($('.aside').is(':hidden')) {
		var aside = 10;
	} else {
		var aside = 233;
	}
    $("#ajaxpanel").css("left", (p.left) + aside + "px")
        .css("top", (p.top + 138) + "px")
        .animate({
            width: w + "px",
            fontSize: "1.2em",
            height: "47px",
            opacity: 1,
        });
    $.get(url, function(data) {
        $("#ajaxpanel").html(data);
    });
    $(window).click(function(e) {
		if ($(e.target).closest("#ajaxpanel").length) {
			return;
		}
        $("#ajaxpanel").animate({
            fontSize: 0,
            width: 0,
            height: 0,
            opacity: 0
        });
        $("#ajaxpanel").remove();
        $("#ajaxpanel").clearQueue();
    });
}

jQuery.posteditor = function(form, id, url) {
    var str = $(form).serialize();
    $("#ajaxpanel").html('<span class="loads"></span>');
    $.post(url, str, function(data) {
        $("#ajaxpanel").animate({
            fontSize: 0,
            width: 0,
            height: 0,
            opacity: 0
        });
        $("#ajaxpanel").remove();
        $("#ajaxpanel").clearQueue();
        $("#" + id).html(data);
    });
    return false;
}

jQuery.windows = function(url, name, width, height, scroll) {
    var tl = '';
    if (width < 0) {
        width = $(window).width() + width;
    }
    if (height < 0) {
        height = $(window).height() + height;
    }
    if (width) {
        tl += ',left=' + ($(window).width() - width) / 2;
    }
    if (height) {
        tl += ',top=' + ($(window).height() - height) / 2;
    }
    window.open(url, name, 'width=' + ((width) ? width : 'auto') + ',height=' + ((height) ? height : 'auto') + ',dependent=yes,titlebar=no,status=no,scrollbars=' + ((scroll) ? scroll : 'no') + tl);
}

jQuery.openurl = function(url) {
    window.location = url;
}

jQuery.addtaginput = function(form, area, sess) {
    var id = $("#countid").attr('value');
    if (id) {
        id++;
        var html = '<div class="section tag" id="taginput' + id + '" style="display:none;">';
        html += '<table class="work"><tr>';
        html += '<td>' + all_name + '&nbsp; &#8260; &nbsp;' + all_cpu + '</td>';
        html += '<td>';
        html += '<input type="text" name="tagword[' + id + ']" id="tagword' + id + '" size="15" maxlength="255">&nbsp;';
        html += '<a class="but" href="javascript:$.translittag(\'tagword' + id + '\',\'tagcpu' + id + '\',\'' + form + '\',\'' + area + '\',\'' + sess + '\');">CPU</a>&nbsp;';
        html += '<input type="text" name="tagcpu[' + id + ']" id="tagcpu' + id + '" size="15" maxlength="255">&nbsp;';
        html += '<a class="but" href="javascript:$.removetaginput(\'' + form + '\',\'' + area + '\',\'taginput' + id + '\');">x</a>';
        html += '</td></tr><tr>';
        html += '<td>' + all_popul + '</td>';
        html += '<td>';
        html += '<input type="text" name="tagrating[' + id + ']" id="tagrating[' + id + ']" size="3" maxlength="3" value="0">';
        html += '</td>';
        html += '</tr>';
        html += '</table>';
        html += '</div>';
        if (typeof page != 'undefined') {
            html += '<script type="text/javascript">';
            html += '$(function() {';
            html += '$("#tagword' + id + '").autocomplete({url:"' + page + '.php?dn=autocomplete&ops=' + ops + '",onItemSelect:function(item){ $("#tagcpu' + id + '").attr("value", item.data);}});';
            html += '});';
            html += '</script>';
        }
        $("form[id=" + form + "] #" + area).append(html);
        $("form[id=" + form + "] #" + area + " #taginput" + id).show('normal');
        $("#countid").attr({
            value: id
        });
    }
}

jQuery.addfileinput = function(form, area, path) {
    var id = $("#fileid").attr('value');
    if (id) {
        var html = '<div class="section tag" id="file-' + id + '" style="display: none;">';
        html += '<table class="work"><tr>';
        html += '<td>' + all_path + '</td>';
        html += '<td>';
        html += '<input name="files[' + id + '][path]" id="files' + id + '" size="50" type="text" required>&nbsp;';
        html += '<input class="side-button" onclick="javascript:$.filebrowser(\'' + ops + '\',\'' + path + '\',\'&amp;field[1]=files' + id + '\')" value="' + filebrowser + '" type="button">';
        html += '<a class="but fr" href="javascript:$.removetaginput(\'total-form\',\'file-area\',\'file-' + id + '\');">&#215;</a>';
        html += '</td></tr><tr>';
        html += '<td>' + all_name + '</td>';
        html += '<td>';
        html += '<input name="files[' + id + '][title]" size="50" type="text" required>';
        html += '</td>';
        html += '</tr>';
        html += '</table>';
        html += '</div>';
        $("form[id=" + form + "] #" + area).append(html);
        $("form[id=" + form + "] #" + area + " #file-" + id).show('normal');
        id++;
        $("#fileid").attr({
            value: id
        });
    }
}

jQuery.addmirror = function(form, area) {
    var id = $("#mirrid").attr('value');
    if (id) {
        id++;
        var html = '<div class="section tag" id="mirror-' + id + '" style="display: none;">';
        html += '<table class="work"><tr>';
        html += '<td class="first"><span>* *</span> ' + all_name + '&nbsp; &#8260; &nbsp;' + all_link + '</td>';
        html += '<td class="nowrap">';
        html += '<input name="mirrors[' + id + '][title]" size="40" type="text" placeholder="' + all_name + '" required="required">';
        html += ' <input name="mirrors[' + id + '][link]" id="mirrors' + id + '" size="55" type="text" placeholder="http://" required="required">';
        html += ' <a class="side-button" href="javascript:$.removetaginput(\'total-form\',\'mirror-area\',\'mirror-' + id + '\');">&#215;</a>';
        html += '</td></tr>';
        html += '</table>';
        html += '</div>';
        $("form[id=" + form + "] #" + area).append(html);
        $("form[id=" + form + "] #" + area + " #mirror-" + id).show('normal');
        $("#mirrid").attr({
            value: id
        });
    }
}

jQuery.removetaginput = function(form, area, id) {
    $("form[id=" + form + "] #" + area + " #" + id).hide('normal', function() {
        $("form[id=" + form + "] #" + area + " #" + id).remove();
    });
}

jQuery.insertinfo = function(obj, tag) {
    var newobj = document.getElementById(obj),
        tag = '{' + tag + '}';
    if (newobj) {
        if (document.selection) {
            newobj.focus();
            document.selection.createRange().duplicate().text = tag;
        } else if (newobj.selectionStart || newobj.selectionStart == '0') {
            var selEnd = newobj.selectionEnd,
                txtLen = newobj.value.length;
            var txtbefore = newobj.value.substring(0, selEnd),
                txtafter = newobj.value.substring(selEnd, txtLen);
            newobj.value = txtbefore + tag + txtafter;
        } else {
            newobj.text.value += tag;
        }
    }
}
jQuery.langbrowser = function(sess) {
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/langbrowser.php',
        data: 'ops=' + sess + '&dn=index',
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $.colorbox({
                    width: '96%',
                    height: '93%',
                    maxWidth: 1200,
                    maxHeight: 711,
                    fixed: true,
                    html: data,
                    onComplete: function() {
                        var $h = $('#cboxLoadedContent').height();
                        $('#cboxLoadedContent').css({
								'height': $h + 'px'
                        });
                    }
                });
                $('#lang-scroll').html(data);
            }
        }
    });
}

jQuery.langbrowserupdate = function(sess, id) {
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/langbrowser.php',
        data: 'ops=' + sess + '&langsetid=' + id,
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $('#lang-scroll').html(data).show();
            }
        }
    });
}

/* New */
jQuery.changeselect = function(sel) {
    if (sel.length > 0) {
        if (ajax == 1) {
            jQuery.ajaxget(sel.value);
        } else {
            jQuery.openurl(sel.value);
        }
    }
}

jQuery.changecategory = function(sel, link, tab) {
    if (sel.length > 0 && sel.value >= 0) {
        $("#ajaxbox").hide();
        $.colorbox({
            onLoad: function() {
                $('#cboxClose').hide();
            },
            opacity: '0.07',
            initialWidth: '60px',
            initialHeight: '40px',
            width: '100px',
            height: '100px',
            html: '&nbsp;'
        });
        $("#cboxContent").css("background", "url(" + jQuery.apanel + "/js/colorbox/images/loading.gif) center center no-repeat");
        localStorage.setItem('field', sel.value);
        $.ajax({
            url: link + sel.value,
            data: {},
            success: function(data) {
                $("#" + tab).html(data);
                $.fn.colorbox.close();
                $('#cboxClose').show();
            }
        });
    }
}

jQuery.loadcategory = function(link, tab) {
    $("#ajaxbox").hide();
    $.colorbox({
        onLoad: function() {
            $('#cboxClose').hide();
        },
        opacity: '0.07',
        initialWidth: '60px',
        initialHeight: '40px',
        width: '100px',
        height: '100px',
        html: '&nbsp;'
    });
    $("#cboxContent").css("background", "url(" + jQuery.apanel + "/js/colorbox/images/loading.gif) center center no-repeat");
    $.ajax({
        url: link,
        data: {},
        success: function(data) {
            $("#" + tab).html(data);
            $.fn.colorbox.close();
            $('#cboxClose').show();
        }
    });
}

jQuery.searchuser = function(sess, id) {
    $.ajax({
        cache: false,
        url: jQuery.apanel + 'mod/user/index.php',
        data: 'ops=' + sess + '&dn=user&id=' + id,
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $.colorbox({
                    scrolling: false,
                    html: data,
                    onComplete: function() {
                        var $h = $('#cboxLoadedContent').height();
                        $('#user-search').css({
                            'height': ($h - 0) + 'px'
                        });
                    }
                })
                $('#user-search').html(data);
            }
        }
    });
}

jQuery.useradd = function(id, userid, name, template, names) {
    var $id = $("#" + id);
    var $uid = $("#user-" + userid);
    if ($id.length > 0 && $uid.length == 0) {
        var html = '';
        html += '<div class="blocking" id="user-' + userid + '">';
        html += '<a href="javascript:$.removeuser(\'user-' + userid + '\');"><img src="template/' + template + '/images/cancel.png"></a> ';
        html += name;
        html += '<input name="' + names + '[]" value="' + userid + '" type="hidden">';
        html += '</div>';
        $id.append(html);
    }
    $.fn.colorbox.close();
}

jQuery.removeuser = function(userid) {
    var $id = $("#" + userid);
    if ($id.length > 0) {
        $id.remove();
    }
}

jQuery.getproduct = function(sel, url) {
    if (sel.length > 0) {
        $('#total-form #product option').remove();
        $.ajax({
            url: url + sel.value,
            success: function(data) {
                $('#total-form #product').html(data);
            }
        });
    }
}

jQuery.addproduct = function() {
    $('#total-form #product :selected').each(function() {
        $(this).remove();
        $('#total-form #associat').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
        $('#total-form #area-associat input[value=\'' + $(this).attr('value') + '\']').remove();
        $('#total-form #area-associat').append('<input type="hidden" name="associats[]" value="' + $(this).attr('value') + '" />');

    });
}

jQuery.delproduct = function() {
    $('#total-form #associat :selected').each(function() {
        $('#total-form #area-associat input[value=\'' + $(this).attr('value') + '\']').remove();
        $(this).remove();
        $('#total-form #product').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
    });
}

jQuery.addtag = function() {
    $('#total-form #tagin :selected').each(function() {
        $(this).remove();
        $('#total-form #tagout').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
        $('#total-form #area-tags input[value=\'' + $(this).attr('value') + '\']').remove();
        $('#total-form #area-tags').append('<input type="hidden" name="tagword[]" value="' + $(this).attr('value') + '" />');

    });
}

jQuery.deltag = function() {
    $('#total-form #tagout :selected').each(function() {
        $('#total-form #area-tags input[value=\'' + $(this).attr('value') + '\']').remove();
        $(this).remove();
        $('#total-form #tagin').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
    });
}

jQuery.addopt = function() {
    $('#total-form #optin :selected').each(function() {
        $(this).remove();
        $('#total-form #optout').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
        $('#total-form #area-opt input[value=\'' + $(this).attr('value') + '\']').remove();
        $('#total-form #area-opt').append('<input type="hidden" name="opt[]" value="' + $(this).attr('value') + '" />');

    });
}

jQuery.delopt = function() {
    $('#total-form #optout :selected').each(function() {
        $('#total-form #area-opt input[value=\'' + $(this).attr('value') + '\']').remove();
        $(this).remove();
        $('#total-form #optin').append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
    });
}

jQuery.findobj = function(obj) {
    this.parent = window.document;
    if (this.parent.getElementById && this.parent.getElementById(obj)) {
        return this.parent.getElementById(obj);
    }
    if (this.parent[obj]) {
        return this.parent[obj];
    }
    if (this.parent.all && this.parent.all[obj]) {
        return this.parent.all[obj];
    }
    if (this.parent.layers && this.parent.layers[obj]) {
        return this.parent.layers[obj];
    }
    return null;
}

jQuery.allselect = function(obj) {
    var formwork = this.findobj(obj);
    var total = formwork.elements.length;
    var b = 0;
    for (var i = 0; i < total; i++) {
        var element = formwork.elements[i];
        if (!element.classList.contains('cset')) {
			if (element.value != 1) {
				if (element.type == 'checkbox' && element.checked == false) {
					element.checked = true;
				} else {
					element.checked = false;
				}
			}
			if (element.type == 'checkbox' && element.checked == true) {
				b += 1;
			}
		}
    }
    formwork.button.value = button + ' [' + b + ']';
}