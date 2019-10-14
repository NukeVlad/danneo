jQuery.rate = function(mod, id, rates) 
{
    $("#view-progress").show();
    $("#view-rate").hide();
    $.ajax({
        cache:false,
        type:'POST',
        url: $.url + '/index.php?dn=' + mod + '&re=ajax&to=rating&id=' + id + '&rate=' + rates,
        error: function(data) { },
        success: function(data) {
        	 $("#view-progress").hide();
             $("#view-rate").html(data);
             $("#view-rate").show();

        }
     })
}
