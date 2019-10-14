jQuery.massuploadcreate = function() 
{ 
    var id = $("#upid").attr('value');
    if (id) {
        id++;  
        var html = '<div id="upload-input-' + id + '">';
        html+= '<script>$(function(){$("a").tooltip();});</script>';   
        html+= '<table class="work">';
        html+= '<tr>'; 
        html+= '<th></th><th>' + all_image + ' ' + id;
        html+= '<a class="fr but" style="height:14px;line-height:14px;" href="javascript:$.massuploadremove(\'' + id + '\');" title="' + all_delet + '">&#215;</a>'; 
        html+= '</th>'; 
        html+= '</tr><tr>'; 
        html+= '<td class="first site"><span>*</span> ' + all_file + '</td>';
        html+= '<td><input name="files[]" type="file" onchange="$.massuploadcreate()"></td>';
        html+= '</tr><tr>'; 
        html+= '<td class="gray">' + all_name + '</td>';
        html+= '<td><input type="text" name="names[]" size="70"></td>';
        html+= '</tr>'; 
        html+= '</table>';
        html+= '</div>';
        $("form[id=total-form] #upload-area").append(html);
        $("form[id=total-form] #upload-area #upload-input-" + id).show('normal');
        $("#upid").attr({value:id});
    }
}
jQuery.massuploadremove = function(id){
    $("form[id=total-form] #upload-area #upload-input-" + id).remove();
}