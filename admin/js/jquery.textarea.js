/*
 jQuery TextAreaResizer plugin
 Created on 17th January 2008 by Ryan O'Dell
*/
(function($) {
    var textarea, staticOffset;
    var iLastMousePos = 0;
    var iMin = 32;
    var grip;
    $.fn.TextAreaResizer = function() {
        return this.each(function() {
            textarea = $(this), staticOffset = null;
            $(this).wrap('<div class="resizable-textarea"><span></span></div>').parent().append($('<div class="grippie"></div>').bind("mousedown", {
                el: this
            }, startDrag));
            var grippie = $("div.grippie", $(this).parent())[0];
            grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) + "px";
        });
    };

    function startDrag(e) {
        textarea = $(e.data.el);
        iLastMousePos = mousePosition(e).y;
        staticOffset = textarea.height() - iLastMousePos;
        textarea.css({
            "border": "1px solid #737985"
        });
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
        textarea.height(iMousePos + "px");
        if (iMousePos < iMin) {
            endDrag(e);
        }
        return false;
    }

    function endDrag(e) {
        $(document).unbind("mousemove", performDrag).unbind("mouseup", endDrag);
        textarea.css({
            "border": "1px solid #babfc9"
        });
        textarea = null;
        staticOffset = null;
        iLastMousePos = 0;
    }

    function mousePosition(e) {
        return {
            x: e.clientX + document.documentElement.scrollLeft,
            y: e.clientY + document.documentElement.scrollTop
        };
    }
})(jQuery);