jQuery.courieradd = function(form, area)
{
    var id = $("#countid").attr('value');
    if (id)
	{
        id ++;
        var html = '<div id="cinput' + id + '" class="section tag adr" style="display: none;">';
        html+= '<table class="work"><tr>';
        html+= '<td class="sw100 ar site">' + all_name + '</td>';
        html+= '<td>';
        html+= '<input name="opt[title][' + id + ']" size="69" type="text" class="fl pw25" style="min-width: 313px;">';
        html+= '<a class="side-button fr" href="javascript:$.removeinput(\'' + form + '\',\'' + area + '\',\'cinput' + id + '\');" title="' + delet + '">&#215;</a></dd>';
        html+= '</td></tr>';
        html+= '<tr>';
        html+= '<td class="sw100 gray">' + all_price + '</td>';
        html+= '<td>';
        html+= '<input name="opt[price][' + id + ']" size="25" type="text" value="0.00" class="pw15"> ';
        html+= '<select name="opt[action][' + id + ']" id="action-' + id + '" onchange="$.changecourier(this);" class="sw250">';
        html+= '<option value="fix">' + fix_price + '</option>';
        html+= '<option value="percent">' + percent + '</option>';
        html+= '<option value="fixpercent">' + fix_price + ' + ' + percent + '</option>';
        html+= '</select>';
        html+= '</td>';
        html+= '</tr></table>';
        html+= '<div id="view-action-' + id + '" style="display:none;">';
        html+= '<table class="work"><tr>';
        html+= '<td class="sw100 gray">' + percent + '</td>';
        html+= '<td>';
        html+= '<input name="opt[percent][' + id + ']" size="18" type="text" value="0.00" class="pw15">';
        html+= '</td>';
        html+= '</tr></table>';
        html+= '</div>';
        html+= '</div>';
        $("form[id=" + form + "] #" + area).append(html);
        $("form[id=" + form + "] #" + area + " #cinput" + id).show('normal');
        $("#countid").attr({value : id});
		$("#delivery-area cite").remove();
    }
}

jQuery.ruspostadd = function(form,area) {
    var id = $("#countid").attr('value');
    if (id) {
        id++;
        var html = '<div id="cinput' + id + '" class="section tag adr" style="display: none;">';
        html+= '<table class="work"><tr>';
        html+= '<td class="sw100 ar site">' + all_name + '</td>';
        html+= '<td>';
        html+= '<select name="opt[state][' + id + ']" class="fl pw25" style="min-width: 313px;">' + all_select + '</select>';
        html+= '<a class="side-button fr" href="javascript:$.removeinput(\'' + form + '\',\'' + area + '\',\'cinput' + id + '\');" title="' + delet + '">&#215;</a>';
        html+= '</td></tr>';
        html+= '<tr>';
        html+= '<td class="sw100 gray">' + all_time + '</td>';
        html+= '<td>';
        html+= '<select name="opt[zone][' + id + ']">';
        html+= '<option value="0"> &#8212; </option>';
        for (i = 1; i < 6; i ++) {
            html+= '<option value="' + i + '">' + all_time + ' ' + i + '</option>';
        }
        html+= '</select>';
        html+= '</td>';
        html+= '</tr></table>';
        html+= '</div>';
        $("form[id="+ form +"] #" + area).append(html);
        $("form[id="+ form +"] #" + area + " #cinput" + id).show('normal');
        $("#countid").attr({value : id});
		$("#delivery-area cite").remove();
    }
}

jQuery.removeinput = function(form,area,id)
{
    $("form[id="+ form +"] #" + area + " #" + id).hide('normal',function(){
        $("form[id="+ form +"] #" + area + " #" + id).remove();
    });
}

jQuery.changecourier = function(sel) {
    if ($(sel).length > 0) {
        var el = $(sel).attr('id');
        if ($(sel).val() == 'fixpercent') {
        	$("#view-" + el).show('normal');
        } else {
        	$("#view-" + el).hide('normal');
        }
    }
}

jQuery.courierselect = function(sel)
{
    if ($(sel).length > 0)
    {
        var s = $(sel).val();
        if (region[s].length > 0)
        {
            arr = region[s];
            var html = '';
            for(var i in arr)
            {
                html+= '<option value="' + i + '"> ' + arr[i] + ' </option>';
            }
            $("#state").html(html);
        }
    }
}