<script>
$(function(){
    $('.fc').click(function() {
        var t = new Date().getTime();
        $('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
	$("#sendtext").autoTextarea({max: 130});
	$('input, textarea').placeholder({customClass:'text-placeholder'});
    $('#captcha').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<form action="{post_url}" enctype="multipart/form-data" method="post">
<div class="sub-title comadd"><h3>{form_title}</h3></div>
<div class="comment">
	<fieldset>
		<input name="sendnames" type="text" value="{uname}" placeholder="{email_name} *" required />
	</fieldset>
	<fieldset>
		<input name="sendorg" type="text"" placeholder="{email_org}" />
	</fieldset>
	<fieldset>
		<input name="sendphone" type="text" placeholder="{email_phone}" />
	</fieldset>
	<fieldset>
		<input name="sendmails" type="text" value="{umail}" placeholder="{email} *" required />
	</fieldset>
	<fieldset>
		<textarea cols="40" rows="5" name="sendtexts" id="sendtext" placeholder="{email_text} *" required></textarea>
	</fieldset>
    <!--if:attach:yes-->
        <div class="clear-line"></div>
    <label for="files" title="{file_help}">{email_file}</label>
    <input class="width" name="files[]" id="files" multiple="multiple" type="file" />
    <!--if-->
    <!--if:captcha:yes-->
	<fieldset>
	<table class="captcha">
	<tr>
        <td><input name="captcha" id="captcha" type="text" maxlength="5" placeholder="{captcha}" required /></td>
        <td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
        <td><img class="refresh fc" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
	</tr>
	</table>
	</fieldset>
    <!--if-->
    <!--if:control:yes-->
	<fieldset>
		<p>{control}</p>
		<input name="respon" size="30" type="text" placeholder="{help_control}" />
		<input name="cid" type="hidden" value="{cid}" />
	</fieldset>
    <!--if-->
</div>
    <div class="send">
        <input name="to" type="hidden" value="send" />
        <button type="submit" class="sub mail">{email_send}</button>
    </div>
</form>
