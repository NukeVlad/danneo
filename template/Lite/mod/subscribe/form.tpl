<script>
$(function(){
    $('.refresh').click(function() {
        var t = new Date().getTime();
        $('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
	$('input, textarea').placeholder({customClass:'text-placeholder'});
    $('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); }); 
});
</script>
<form action="{post_url}" method="post">
<div class="forms">
    <div class="form-area-cont">
		<fieldset> 
			<input name="subname" id="sname" type="text" title="{not_empty}" placeholder="{subscribe_your_name} *" autofocus required />
		</fieldset>
		<fieldset> 
			<input name="submail" id="smail" type="text" title="{not_empty}" placeholder="{subscribe_your_mail} *" required />
		</fieldset>
		<label for="sformat" title="{select}">{subscribe_your_format}<b></b></label>
        <select name="subformat" id="sformat">
            <option value="0" selected="selected">Text</option>
            <option value="1">Html</option>
        </select>
        <div class="clear-line"></div>
		<!--if:captcha:yes-->
        <div class="clear-line"></div>
		<table class="captcha">
		<tbody>
        <tr>
            <td><input id="captcha" name="captcha" type="text" maxlength="5" title="{help_captcha}" placeholder="{captcha}" required /></td>
            <td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
            <td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
        </tr>
		</tbody>
		</table>
		<!--if-->
		<!--if:control:yes-->
        <p>{control}</p>
        <input id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{control_word}" required />
        <input name="cid" type="hidden" value="{cid}" />
		<!--if--> 
    </div>
</div>
    <div class="send">
        <input name="to" type="hidden" value="check" />
        <button type="submit" class="sub post">{subscribe_button}</button>
    </div>
</form>
