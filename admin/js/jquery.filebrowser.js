jQuery.filebrowserclose = function() {
    $.fn.colorbox.close();
    //$('#bg-overlay,#bg-overlay-content').hide();
    return false;
}

jQuery.filebrowser = function(sess, patch, row) {
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/filebrowser.php',
        data: 'ops=' + sess + '&objdir=' + patch + row,
        error: function(msg) {},
        success: function(data) {
            if (data.length > 0) {
                $.colorbox({
                    width: '96%',
                    height: '93%',
                    maxWidth: 1400,
                    maxHeight: 850,
                    fixed: true,
                    html: data,
                    onComplete: function() {
                        var $h = $('#cboxLoadedContent').height();
                        $('.file-browser-inter').css({
                            'height': ($h - 132) + 'px'
                        });
                        $('#folder, #files').css({
                            'height': ($h - 135) + 'px'
                        });
                        $('#folder .fb-pad, #files .fb-pad').css({
                            'height': ($h - 152) + 'px'
                        });
                    }
                });
                jQuery.filebrowserupdate(sess, patch + row);
            }
        }
    });
}

jQuery.filebrowserupdate = function(sess, patch) {
    $('#folder .fb-pad, #files .fb-pad').html('<div class="fb-loader-center"><img src="' + jQuery.apanel + 'template/skin/' + jQuery.template + '/images/loading.gif"></div>');
    var splitted = patch.toString().split("&");
    $('#patch').attr({
        value: splitted[0]
    });
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/filebrowser.php',
        data: 'ops=' + sess + '&objdir=' + patch + '&dn=folder',
        error: function(msg) {
            $('#folder .fb-pad').html('error');
        },
        success: function(data) {
            if (data.length > 0) {
                $('#folder .fb-pad').html(data);
            }
        }
    });
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/filebrowser.php',
        data: 'ops=' + sess + '&objdir=' + patch + '&dn=files',
        error: function(msg) {
            $('#files .fb-pad').html('error');
        },
        success: function(data) {
            if (data.length > 0) {
                $('#files .fb-pad').html(data);
            }
        }
    });
}

jQuery.filebrowserdel = function(sess, objdir, obj) {
    if (confirm(con + '?')) {
        $('#files .fb-pad').html('<div class="fb-loader-center"><img src="' + jQuery.apanel + 'template/skin/' + jQuery.template + '/images/loading.gif"></div>');
        $.ajax({
            //async:false,
            cache: false,
            url: jQuery.apanel + 'includes/filebrowser.php',
            data: 'ops=' + sess + '&objdir=' + objdir + '&obj=' + obj + '&dn=realdel',
            error: function(msg) {
                alert(jQuery.errors);
            },
            success: function(data) {
                if (data.length > 0) {
                    $patch = $('#patch').attr('value');
                    if ($patch.length > 0) {
                        if (data == 1) {
                            $.filebrowserfiles('dn=files&ops=' + sess + '&objdir=' + objdir);
                        } else {
                            alert($.errors);
                        }
                    }
                }
            }
        });
    }
}

jQuery.filebrowserfiles = function(patch) {
    $('#files .fb-pad').html('<div class="fb-loader-center"><img src="' + jQuery.apanel + 'template/skin/' + jQuery.template + '/images/loading.gif"></div>');
    $.ajax({
        async: false,
        cache: false,
        url: jQuery.apanel + 'includes/filebrowser.php',
        data: patch + '&hash=' + Math.random() + '&objdir=' + $('#patch').val(),
        error: function(msg) {
            $('#files .fb-pad').html('error');
        },
        success: function(data) {
            if (data.length > 0) {
                $('#files .fb-pad').html(data);
            }
        }
    });
    var i = $('#imaginebox');
    if (i.length > 0) {
        var w = $('#files').width() - 15;
        var h = $('#files').height() - 70;
        $(i).css({
            'height': h + 'px',
            'width': w + 'px'
        });
    }
}

jQuery.filebrowserfolders = function(patch) {
    $('#folder .fb-pad').html('<div class="fb-loader-center"><img src="' + jQuery.apanel + 'template/skin/' + jQuery.template + '/images/loading.gif"></div>');
    $.ajax({
        //async:false,
        cache: false,
        url: jQuery.apanel + 'includes/filebrowser.php',
        data: patch,
        error: function(msg) {
            $('#folder .fb-pad').html('error');
        },
        success: function(data) {
            if (data.length > 0) {
                $('#folder .fb-pad').html(data);
            }
        }
    });
}

jQuery.filebrowserparent = function(id, link) {
    $('#' + id + ' .fb-pad').html('<div class="fb-loader-center"><img src="' + jQuery.apanel + 'template/skin/' + jQuery.template + '/images/loading.gif"></div>');
    $patch = $('#patch').attr('value');
    if ($patch.length > 0) {
        $.ajax({
            //async:false,
            cache: false,
            url: jQuery.apanel + 'includes/filebrowser.php',
            data: link + '&objdir=' + $patch,
            error: function(msg) {
                $('#' + id + ' .fb-pad').html('error');
            },
            success: function(data) {
                if (data.length > 0) {
                    $('#' + id + ' .fb-pad').html(data);
                }
            }
        });
    }
}

jQuery.filebrowserinsert = function(name) {
    if ($('#field').length > 0 && $('#patch').length > 0) {
        var f = $('#field option:selected').val(),
            p = $('#patch').val();
        if ($('#' + f).length > 0) {
            $('#' + f).val('up' + p + name);
            jQuery.filebrowserclose();
        }
    }
}

jQuery.filebrowserimsinsert = function(name) {
    if ($('#field').length > 0 && $('#patch').length > 0) {
        var f = $('#field option:selected').val(),
            p = $('#patch').val();
        if ($('#' + f).length > 0 && name.match(/\.(?:jpe?g|gif|png|bmp)/i)) {
            $('#' + f).val('up' + p + name);
        }
    }
}

jQuery.filebrowserimsload = function() {
    if ($('#thumb').length > 0) {
        var it = $('#thumb').val();
        if (it.length > 0 && it.match(/\.(?:jpe?g|gif|png|bmp)/i)) {
            var id = $("#imgid").attr('value');
            if (id) {
                id++;
                var html = '<div class="section tag" id="imginput' + id + '" style="display: none;">';
                html += '<script> $(function() { $(".imgcount").focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); }); }); $(function(){ $("img, a").tooltip(); });</script>';
                html += '<table class="work">';
                html += '<tr><td>';
                if (($('#imagel').val()).length > 0) {
                    html += '<img class="sw50" src="/' + it + '" alt="' + all_thumb + '" />';
                } else {
                    html += '<img class="sw70" src="/' + it + '" alt="' + all_images + '" />';
                }
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="' + it + '">';
                if ($('#imagel').length > 0) {
                    var i = $('#imagel').val();
                    if (i.length > 0) {
                        html += ' &nbsp; <img class="sw70" src="/' + i + '" alt="' + all_img + '" />';
                        html += '<input type="hidden" name="images[' + id + '][image]" value="' + i + '">';
                    }
                }
                html += '</td><td class="al vm">';
                html += '<a class="but fr" href="javascript:$.filebrowserimsremove(\'' + id + '\');" title="' + all_delet + '">x</a>';
                html += '<p><input type="text" size="3" class="imgcount" readonly="readonly" title="' + all_copy + '"> <cite>' + code_paste + '</cite></p>';
                html += '<p class="label">' + all_align + '&nbsp; &nbsp; &nbsp; &nbsp;' + all_alt + '</p>';
                html += '<p><select name="images[' + id + '][image_align]">';
                html += '<option value="left">' + all_left + '</option>';
                html += '<option value="right">' + all_right + '</option>';
                html += '<option value="center">' + all_center + '</option>';
                html += '</select>&nbsp; &nbsp; &nbsp;';
                html += '<input type="text" name="images[' + id + '][image_alt]" size="25" class="pw45" />';
                html += '</p>';
                html += '</td></tr></table>';
                html += '</div>';
                $("form[id=total-form] #image-area").append(html);
                $("form[id=total-form] #image-area #imginput" + id).show('normal');
                $("#imgid").attr({
                    value: id
                });
                var c = 1;
                $("#image-area .imgcount").each(function() {
                    $("#image-area").addClass("image-area");
                    this.value = '{img' + c + '}';
                    c++;
                });
                jQuery.filebrowserclose();
            }
        }
    }
}

jQuery.filebrowserimscreate = function(thumb, img) {
    if (img.length > 0 && img.match(/\.(?:jpe?g|gif|png|bmp)/i)) {
        var id = $("#imgid").attr('value');
        if (id) {
            id++;
            var html = '<div class="section tag" id="imginput' + id + '" style="display: none;">';
            html += '<script> $(function() { $(".imgcount").focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); }); }); $(function(){ $("img, a").tooltip(); });</script>';
            html += '<table class="work">';
            html += '<tr><td>';
            if (thumb.length > 0) {
                html += '<img class="sw50" src="/up' + thumb + '" alt="' + all_thumb + '" />';
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="up' + thumb + '">';
            } else {
                html += '<img class="sw70" src="/up' + img + '" alt="' + all_images + '" />';
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="up' + img + '">';
            }
            if (img.length > 0 && thumb.length > 0) {
                html += ' &nbsp; <img class="sw70" src="/up' + img + '" alt="' + all_img + '" />';
                html += '<input type="hidden" name="images[' + id + '][image]" value="up' + img + '">';
            }
            html += '</td><td>';
            html += '<p><input type="text" size="3" value="" class="imgcount" readonly="readonly" title="' + all_copy + '"> <cite>' + code_paste + '</cite></p>';
            html += '<p class="label">' + all_align + '&nbsp; &nbsp; &nbsp; &nbsp;' + all_alt + '</p>';
            html += '<p><select name="images[' + id + '][image_align]">';
            html += '<option value="left">' + all_left + '</option>';
            html += '<option value="right">' + all_right + '</option>';
            html += '<option value="center">' + all_center + '</option>';
            html += '</select>&nbsp; &nbsp; &nbsp;';
            html += '<input type="text" name="images[' + id + '][image_alt]" size="25" value="" />';
            html += '</p>';
            html += '</td><td class="ac vm">';
            html += '<a class="but" href="javascript:$.filebrowserimsremove(\'' + id + '\');" title="' + all_delet + '">x</a>';
            html += '</td></tr></table>';
            html += '</div>';
            $("form[id=total-form] #image-area").append(html);
            $("form[id=total-form] #image-area #imginput" + id).show('normal');
            $("#imgid").attr({
                value: id
            });
            var c = 1;
            $("#image-area .imgcount").each(function() {
                $("#image-area").addClass("image-area");
                this.value = '{img' + c + '}';
                c++;
            });
            jQuery.filebrowserclose();
        }
    }
}

jQuery.filebrowserimsremove = function(id) {
    $("form[id=total-form] #image-area #imginput" + id).remove();
    var c = 1;
    $("#image-area .imgcount").each(function() {
        this.value = '{img' + c + '}';
        c++;
    });
    if (id == 1) $("#image-area").removeClass("image-area");
}

jQuery.personalupload = function(path) {
    $.colorbox({
        width: '92%',
        maxWidth: 850,
        fixed: true,
        href: jQuery.apanel + 'includes/filebrowser.php?dn=personal&ops=' + path
    });
}

/**
 * More images
 */
jQuery.moreimages = function(path) {
    $.colorbox({
        width: '92%',
        maxWidth: 600,
        maxHeight: 175,
        fixed: true,
        href: jQuery.apanel + 'includes/filebrowser.php?dn=moreupload&ops=' + path
    });
}
jQuery.morecreate = function(thumb, img) {
    if (img.length > 0 && img.match(/\.(?:jpe?g|gif|png)/i)) {
        var id = $("#imgid").attr('value');
        if (id) {
            id++;
            var html = '<div class="section tag" id="imginput' + id + '" style="display: none;">';
            html += '<script>$(function(){ $("img, a, input").tooltip(); });</script>';
            html += '<table class="work">';
            html += '<tr><td>';
            if (thumb.length > 0) {
                html += '<img class="sw30" src="/up' + thumb + '" alt="' + all_thumb + '" />';
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="up' + thumb + '">';
            } else {
                html += '<img class="sw50" src="/up' + img + '" alt="' + all_images + '" />';
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="up' + img + '">';
            }
            if (img.length > 0 && thumb.length > 0) {
                html += ' &nbsp; <img class="sw50" src="/up' + img + '" alt="' + all_img + '" />';
                html += '<input type="hidden" name="images[' + id + '][image]" value="up' + img + '">';
            }
            html += '</td><td>';
            html += '<p><input type="text" name="images[' + id + '][image_title]" size="25" class="pw45" title="' + all_name + '" /></p>';
            html += '</td><td class="ac vm">';
            html += '<a class="but" href="javascript:$.filebrowserimsremove(\'' + id + '\');" title="' + all_delet + '">x</a>';
            html += '</td></tr></table>';
            html += '</div>';
            $("form[id=total-form] #image-area").append(html);
            $("form[id=total-form] #image-area #imginput" + id).show('normal');
            $("#imgid").attr({
                value: id
            });
            $("#image-area").addClass("image-area");
            jQuery.filebrowserclose();
        }
    }
}
jQuery.moreimsload = function() {
    if ($('#thumb').length > 0) {
        var it = $('#thumb').val();
        if (it.length > 0 && it.match(/\.(?:jpe?g|gif|png)/i)) {
            var id = $("#imgid").attr('value');
            if (id) {
                id++;
                var html = '<div class="section tag" id="imginput' + id + '" style="display: none;">';
                html += '<script>$(function(){ $("img, a, input").tooltip(); });</script>';
                html += '<table class="work">';
                html += '<tr><td>';
                if (($('#imagel').val()).length > 0) {
                    html += '<img class="sw30" src="/' + it + '" alt="' + all_thumb + '" />';
                } else {
                    html += '<img class="sw50" src="/' + it + '" alt="' + all_images + '" />';
                }
                html += '<input type="hidden" name="images[' + id + '][image_thumb]" value="' + it + '">';
                if ($('#imagel').length > 0) {
                    var i = $('#imagel').val();
                    if (i.length > 0) {
                        html += ' &nbsp; <img class="sw50" src="/' + i + '" alt="' + all_img + '" />';
                        html += '<input type="hidden" name="images[' + id + '][image]" value="' + i + '">';
                    }
                }
                html += '</td><td class="al vm">';
                html += '<a class="but fr" href="javascript:$.filebrowserimsremove(\'' + id + '\');" title="' + all_delet + '">x</a>';
                html += '<p><input type="text" name="images[' + id + '][image_title]" size="25" class="pw45" title="' + all_name + '" /></p>';
                html += '</td></tr></table>';
                html += '</div>';
                $("form[id=total-form] #image-area").append(html);
                $("form[id=total-form] #image-area #imginput" + id).show('normal');
                $("#imgid").attr({
                    value: id
                });
                $("#image-area").addClass("image-area");
                jQuery.filebrowserclose();
            }
        }
    }
}

/**
 * Quick upload
 */
jQuery.quickupload = function(path) {
    $.colorbox({
        width: '92%',
        maxWidth: 600,
        maxHeight: 175,
        fixed: true,
        href: jQuery.apanel + 'includes/filebrowser.php?dn=quickupload&ops=' + path
    });
}
jQuery.quickinsert = function(thumb, img) {
    var alt = $('#title').val();
    $('#image_thumb').val('up' + thumb);
    $('#image').val('up' + img);
    if ($('#title').length > 0) {
        $('#image_alt').val(alt);
    }
    jQuery.filebrowserclose();
}

/**
 * All upload
 */
jQuery.fileallcreate = function(down, id) {
    if (down.length > 0 && id.length > 0) {
        $('#' + id).val('up' + down);
        jQuery.filebrowserclose();
    }
}
jQuery.fileallupload = function(patch, place) {
    $.colorbox({
        initialWidth: '530',
        initialHeight: '150px',
        width: '92%',
        maxWidth: 600,
        fixed: true,
        href: jQuery.apanel + 'includes/filebrowser.php?dn=allupload&ops=' + patch + place
    });
}