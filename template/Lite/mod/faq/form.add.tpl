<script src="{site_url}/js/jquery.form.js"></script>
<script>
$(function(){
    $('.refresh').click(function() {
        var t = new Date().getTime();
        $('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
	$("#quest").autoTextarea({max: 130});
	$('input, textarea').placeholder({customClass:'text-placeholder'});
    $('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<div class="sub-title comadd"><h3>{add_question}</h3></div>
<form action="{post_url}" method="post">
<div class="comment">
	<fieldset>
		<input name="author" type="text" placeholder="{email_name} *" value="{uname}" required />
	</fieldset>
	<fieldset>
        <input name="email" type="text" placeholder="{email} *" value="{umail}" required />
	</fieldset>
	<fieldset>
    <!--if:cat:yes-->
        <select name="catid" title="{in_cat}">
		<option value="0" class="oneselect">{cat_not}</option>
        {sel}
        </select>
    <!--if-->
	</fieldset>
	<fieldset>
        <textarea cols="40" rows="7" id="quest" name="question" placeholder="{question} *" required></textarea>
	</fieldset>
	<fieldset>
		<!--if:captcha:yes-->
		<table class="captcha">
        <tr>
            <td><input id="captcha" name="captcha" type="text" maxlength="5" title="{help_captcha}" placeholder="{captcha} *" required /></td>
            <td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
            <td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
        </tr>
		</table>
		<!--if-->
		<!--if:control:yes-->
        <p>{control}</p>
        <input id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{control_word} *" required />
        <input name="cid" type="hidden" value="{cid}" />
		<!--if-->
	</fieldset>
</div>
<div class="send">
	<input name="re" type="hidden" value="add" />
	<button type="submit" class="sub post">{send}</button>
</div>
</form>