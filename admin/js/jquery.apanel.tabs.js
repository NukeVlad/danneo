(function($)
{
	$.fn.tabs = function(start)
	{
		var selector = this;
		var storage = localStorage.getItem('tab');

		this.each(function()
		{
			if (start && storage == null) {
				$(start).show();
			}
			$($(this).attr('data-tabs')).hide();
			$(this).click(function()
			{
				$(selector).each(function(i, element)
				{
					$(element).removeClass('current');
					$($(this).attr('data-tabs')).hide();
				});
				$(this).addClass('current');

				localStorage.setItem('tab', $(this).attr('data-tabs'));

				if ($(this).attr('data-tabs') == 'all')
				{
					$(selector).each(function(i, element) {
						$($(this).attr('data-tabs')).show();
					});
				}
				else
				{
					$(localStorage.getItem('tab')).show();
                }
			});
		});

		if (storage == 'all')
		{
			$(selector).each(function(i, element) {
				$($(this).attr('data-tabs')).show();
			});
		}

		$(storage).show();
		$('[data-tabs=\'' + storage + '\']').addClass('current');

		if (!start) {
			start = selector.eq(1).attr('data-tabs');
		}

		if (storage == null) {
			$('[data-tabs=\'' + start + '\']').addClass('current');
		}

		if (start || storage == null) {
			$($(this).attr('data-tabs')).show();
		} else {
			$(selector + '[data-tabs=\'' + storage + '\']').trigger('click');
		}
	};
})(jQuery);