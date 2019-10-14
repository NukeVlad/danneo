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
	<fieldset>
		<input class="width" name="reglogin" size="30" type="text" maxlength="{maxname}" placeholder="{login} *" autofocus required /><span class="help" title="{login_hint}">?</span>
	</fieldset>
	<fieldset>
		<input name="regmail" size="30" type="text" placeholder="{e_mail} *" required /><span class="help" title="{mail_hint}">?</span>
	</fieldset>
	<fieldset>
		<input name="regpassw" size="30" type="password" maxlength="{maxpass}" placeholder="{pass} *" required /><span class="help" title="{pass_hint}">?</span>
	</fieldset>
	<fieldset>
		<!--buffer:rows:0-->
		<label for="fields[{key}]" title="{name}">{name}{required}</label>
		{field}
		<div class="clear-line"></div>
		<!--buffer-->
		<!--buffer:required:0--><i></i><!--buffer-->
		<!--buffer:norequired:0--><b></b><!--buffer-->
		{fields}
        <div class="clear-line"></div>
		<!--if:captcha:yes-->
		<table class="captcha">
        <tr>
            <td><input id="captcha" name="captcha" type="text" maxlength="5" title="{help_captcha}" placeholder="{captcha}" required /></td>
            <td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
            <td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
        </tr>
		</table>
		<!--if-->
		<!--if:control:yes-->
        <p>{control}</p>
        <input id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{control_word}" required />
        <input name="cid" type="hidden" value="{cid}" />
		<!--if-->
	</fieldset>
</div>
<div class="send">
	<input name="re" value="register" type="hidden" />
	<input name="to" value="check" type="hidden" />
	<button type="submit" class="sub reg">{user_reg}</button>
</div>
</form>