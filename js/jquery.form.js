(function ($) {

	$.fn.comment = function (options) {

		// Настройки по умолчанию
		var options = jQuery.extend( {
			smilie: false,
			editor: false,
			code: true,
			quote: true,
			bold: true,
			italic: true,
			underline: true,
			link: true,
			email: true,
			newline: true
		}, options );

		return this.each(function () {

			function insertbb(start, end, element, param) {
				if (document.selection) {
					element.focus();
					sel = document.selection.createRange();
					if (sel.text.length == 0 && param.length > 0) {
						sel.text = start + param + end;
					} else {
						sel.text = start + sel.text + end;
					}
				} else if (element.selectionStart || element.selectionStart == '0') {
					element.focus();
					var startPos = element.selectionStart;
					var endPos = element.selectionEnd;
					if (element.value.substring(startPos, endPos).length == 0 && param.length > 0) {
						element.value = element.value.substring(0, startPos) + start + param + element.value.substring(startPos, endPos) + end + element.value.substring(endPos, element.value.length);
					} else {
						element.value = element.value.substring(0, startPos) + start + element.value.substring(startPos, endPos) + end + element.value.substring(endPos, element.value.length);
					}
				} else {
					if (param.length > 0) {
						element.value += start + param + end;
					} else {
						element.value += start + end;
					}
				}
			}

			function insertsmilie(code, element)
			{
				if (document.selection)
				{
					element.focus();
					sel = document.selection.createRange();
					sel.text = code;
				}
				else if (element.selectionStart || element.selectionStart == '0')
				{
					element.focus();
					var startform = element.selectionStart;
					var endform = element.selectionEnd;

					if (endform <= 2) {
						endform = element.textLength;
					}

					var start = (element.value).substring(0, startform),
						text = (element.value).substring(startform, endform),
						end = (element.value).substring(endform, element.textLength);

					if (element.selectionEnd - element.selectionStart > 0) {
						text = code;
					} else {
						text += code;
					}

					element.value = start + text + end;
				}
				else {
					element.value += code;
				}
			}

			if (options.editor == true || options.smilie == true)
			{
				var $$ = $(this);
				var id = $$.attr("id");
				var e = $$.get(0);
				$$.wrap('<div id="area' + id + '" class="commentarea"></div>');

				if (options.smilie == true)
				{
					$('<div id="smilie' + id + '" class="commentsmilie"></div>').insertAfter($$);
					var smilierow = '';
					for (var i = 0; i < $.smilie.length; i++) {
						smilierow = smilierow + '<img rel="' + $.smilie[i][1] + '" src="' + $.smilie[i][0] + '" alt="' + $.smilie[i][2] + '" />';
					}
					$('#smilie' + id).prepend(smilierow);
					$('#smilie' + id + ' img').click( function () {
						var code = $(this).attr('rel');
						insertsmilie(code, e);
					});
				}

				if (options.editor == true)
				{
					$('<div id="bb' + id + '" class="commentbb"></div>').insertBefore($$);

					var button = '';
					if (options.quote == true) {
						button = button + '<cite rel="QUOTE">QUOTE</cite>';
					}
					if (options.bold == true) {
						button = button + '<cite rel="B">B</cite>';
					}
					if (options.italic == true) {
						button = button + '<cite rel="I">I</cite>';
					}
					if (options.underline == true) {
						button = button + '<cite rel="U">U</cite>';
					}
					if (options.link == true) {
						button = button + '<cite rel="URL">URL</cite>';
					}
					if (options.email == true) {
						button = button + '<cite rel="MAIL">MAIL</cite>';
					}

					$('#bb' + id).prepend(button);

					$('#bb' + id + ' cite').click( function () {
						var param = '';
						var code = $(this).attr('rel');
						var start = '[' + code + ']';
						var end = '[/' + code + ']';
						if (code == 'URL') {
							param = prompt("URL","http://");
							if (param) {
								start = '[URL=' + param + ']';
							} else {
								return;
							}
						}
						if (code == 'MAIL') {
							param = prompt("E-mail", "your@mail.ru");
							if (param) {
								start = '[MAIL=' + param + ']';
							} else {
								return;
							}
						}
						insertbb(start, end, e, param);
					});

					$('#bb' + id + ' mark').click( function () {
						var param = '';
						var code = $(this).attr('rel');
						var start = '[' + code + ']';
						var end = '';
						insertbb(start, end, e, param);
					});
				}
			}
		});
	};

	var textarea, staticOffset;
	var iLastMousePos = 0;
	var iMin = 100;
	var grip;

	$.fn.textarearesizer = function () {
		return this.each(function() {
			textarea = $(this), staticOffset = null;
			$(this).wrap('<div class="resizable-textarea"><span></span></div>')
				.parent().append($('<div class="grippie"></div>').bind("mousedown",{el: this} , startDrag));
			var grippie = $('div.grippie', $(this).parent())[0];
			grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) +'px';
		});
	};

	function startDrag(e) {
		textarea = $(e.data.el);
		iLastMousePos = mousePosition(e).y;
		staticOffset = textarea.height() - iLastMousePos;
		textarea.addClass("error-input");
		$(document).mousemove(performDrag).mouseup(endDrag);
		return false;
	}

	function performDrag(e) {
		var iThisMousePos = mousePosition(e).y;
		var iMousePos = staticOffset + iThisMousePos;
		if (iLastMousePos >= (iThisMousePos)) {
			iMousePos -= 5;
		}
		iLastMousePos = iThisMousePos;
		iMousePos = Math.max(iMin, iMousePos);
		textarea.height(iMousePos + 'px');
		if (iMousePos < iMin) {
			endDrag(e);
		}
		return false;
	}

	function endDrag(e) {
		$(document).unbind('mousemove', performDrag).unbind('mouseup', endDrag);
		textarea.removeClass("error-input");
		textarea = null;
		staticOffset = null;
		iLastMousePos = 0;
	}

	function mousePosition(e) {
		return { x: e.clientX + document.documentElement.scrollLeft, y: e.clientY + document.documentElement.scrollTop };
	};

    /**
     * Verification form
     */
	$.fn.checkForm = function (fields, list)
	{
		$(this).submit(function () {

			var error = false;
			var form = '#' + $(this).attr("id");

			$(form + " input, select, textarea").removeClass("error-input");

			// Check input & textarea
			$.each(fields, function(id, val) {

				var len = $("#" + id).val().length;
				var min = (val.min != "undefined" && val.min > len) ? 0 : 1;
				var max = (val.max != "undefined" && val.max < len) ? 0 : 1;

				if ($("#" + id) != "undefined")
				{
					if ($.isPlainObject(val))
					{
						if (min == 0 || max == 0 || len == 0)
						{
							$("#" + id).addClass("error-input");
							$("#" + id).focus(function () {
								$(this).removeClass("error-input");
								$($(this).prev(".error-mess")).fadeOut( function () {
									$(this).remove();
								});
							});
							$("#" + id).fadeIn( function () {
								$(this).before('<label class="error-mess">'+val.mess+'</label>');
							});
							error = true;
						}
					}
					/*else {
						if (val == 0) {
							if (len == 0) {
								$("#" + id).addClass("error-input");
								$("#" + id).focus(function () {
									$(this).removeClass("error-input");
								});
								error = true;
							}
						}
						if (val > 0) {
							if (len == 0 || len > val) {
								$("#" + id).addClass("error-input");
								$("#" + id).focus(function () {
									$(this).removeClass("error-input");
								});
								error = true;
							}
						}
					}*/
				}
			});

			// Check select, option not null
			if (typeof(list) !== "undefined")
			{
				$.each(list, function (id, val)
				{
					if ($("#" + id).find("option:selected").val() == 0)
					{
						$("#" + id).addClass("error-input");
						$("#" + id).focus(function () {
							$(this).removeClass("error-input");
							$($(this).prev(".error-mess")).fadeOut( function () {
								$(this).remove();
							});
						});
						if ($.isPlainObject(val)) {
							$("#" + id).fadeIn( function () {
								$(this).before('<label class="error-mess">'+val.mess+'</label>');
							});
						}
						error = true;
					}
				});
			}

			// Error
			if (error) {
				return false;
			}
		});
	};
})(jQuery);