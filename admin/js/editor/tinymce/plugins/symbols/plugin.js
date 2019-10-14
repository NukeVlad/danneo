/**
 * plugin.js
 *
 * Released under LGPL License.
 * Copyright (c) 2005-2019 Arisfera Web Studio. All rights reserved
 * link: http://www.arisfera.ru
 *
 * License: http://www.tinymce.com/license
 */

/*global tinymce:true */

tinymce.PluginManager.add('symbols', function(a) {
    function b() {
        a.theme.panel.find('#symbols').text(['Characters left: {0}', e.getCount()])
    }
    var c, d, f, g, h, j, k, e = this;
    k = a.getParam('symbols_min', 10),
    d = a.getParam('symbols_max', 150),
    f = a.getParam('symbols_start', '#0c9c03'),
    g = a.getParam('symbols_error', '#c04904'),
    h = a.getParam('border_focus', '#c5c5c5'),
    j = a.getParam('border_error', '#d95204'),
    m = a.getParam('symbols_class', 'wordcount'),
	a.on('init', function() {
		$(a.getContainer()).addClass(a.id);
        var c = a.theme.panel && a.theme.panel.find('#statusbar')[0];
        window.setTimeout(function() {
            c.insert({
                type: 'label',
                name: 'symbols',
                text: ['Characters left: {0}', e.getCount()],
                classes: m,
                disabled: a.settings.readonly
            }, 0),
			a.on('setcontent beforeaddundo load', b), a.on('keyup', function(a) {
                b()
            }),
			a.on('submit', function (b) {
				var bc = a.getContent({format: 'text'}).length;
				var bb = k == 0 ? (k + 1) : k;
				if (bc <= bb) {
					$(a.getContainer()).css('border-color', j);
				}
			}),
			a.on('focus click', function (b) {
				$(a.getContainer()).css('border-color', h);
			})
        }, 0)
    }), e.getCount = function() {
        var b = a.getContent({
                format: 'text'
            }),
            e = 0;
        if (b) {
            (e = b.length - d)
        }
		if (e > 0) {
			e = ' −' + e;
			$('.' + a.id + ' .mce-' + m).css('color', g);
			$(a.getContainer()).css('border-color', j);
		} else if (e <= 0 || e == d) {
			e = (b == 0) ? d : Math.abs(e);
			$('.' + a.id + ' .mce-' + m).css('color', f);
			$(a.getContainer()).css('border-color', '');
		}
        return e
    }
});