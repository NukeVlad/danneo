jQuery.currency = function(code){
    cookie.set('currency', code, { expires: 7, path: $.url + '/' });
    location.reload();
}

$(function()
{  
	$(".basket-add").submit(function() 
	{
		$.ajax({
            type: "POST",
			cache : false,
			url : $.url + '/',
			data : $(this).serialize() + '&ajax=1',
			error : function (msg) { },
			success : function (data) {
				if (data.length > 0) {
					$.colorbox({
						transition  : "elastic",
						scrolling: false,
						width	: "92%",
						maxHeight	:  "90%",
						maxWidth	:  750,
						fixed: true,
                        html:data
					},$.basketsubmit)
				}
			}
		});
		return false;
	}); 
});

jQuery.basketsubmit = function()
{
    $("#recount").click(function()
    {
        var data = $('#basket-form').serialize() + '&recount=1&ajax=1';
        $.ajax({
            type: "POST",
            cache : false,
            url : $.url + '/',
            data : data,
            error : function (msg) { },
            success : function (d)
            {
                if (d.length > 0)
                {
                    $.colorbox({
						transition  : "elastic",
						scrolling: false,
						width	: "92%",
						maxHeight	:  "90%",
						maxWidth	:  751,
						fixed: true,
                        html:d
                    },$.basketsubmit)
                }
            }
        });
        return false;
    });
    $("#sub").click(function()
    {
        var data = $('#basket-form').serialize() + '&ajax=1';
        $.ajax({
        	type: "POST",
            cache : false,
            url : $.url + '/',
            data : data,
            error : function (msg) {  },
            success : function (d)
            {
                if (d.length > 0)
                {
                    if ($('#basket-block').length > 0)
                    {
                    	$('#basket-block').html(d);
                    }
                }
            }
        });
        $.fn.colorbox.close();
		setTimeout(function() {window.location.reload();}, 1000);
        return false;
    });
}

jQuery.basketdelete = function(id,mod)
{
    var data = 'dn=' + mod + '&id=' + id + '&re=del';
    $.ajax({
        	type: "POST",
            cache : false,
            url : $.url + '/',
            data : data,
            error : function (msg) {  },
            success : function (d)
            {
                if (d.length > 0)
                {
                    if ($('#basket-block').length > 0)
                    {
                    	$('#basket-block').html(d);
						//setTimeout(function() {window.location.reload();}, 1000);
                    }
                }
            }
    });
}