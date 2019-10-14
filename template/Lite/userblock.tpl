<!--if:logged:no-->
<script src="{site_url}/js/jquery.form.js"></script>
<script>
$(function () {
	$('input, textarea').placeholder({customClass:'text-placeholder'});
});
</script>
<div class="user-block">
<form action="{post_url}" method="post">
    <input name="login" id="login" size="22" type="text" maxlength="{maxname}" placeholder="{login}" required><br>
    <input name="passw" id="passw" size="22" type="password" maxlength="{maxpass}" placeholder="{pass}" required>
    <div class="user-link">
        <input name="re" value="login" type="hidden" />
        <input name="to" value="check" type="hidden" />
        <button type="submit" class="sub auth">{enter}</button>
    </div>
    <div class="user-link">
        <p class="al user"><a href="{linklost}" rel="nofollow">{send_pass}</a></p>
        <p class="al user"><a href="{linkreg}" rel="nofollow">{registr}</a></p>
    </div>
</form>
</div>
<!--if-->
<!--buffer:links:0--><p class="user"><a class="uadd {css}" href="{url}">{title}</a></p><!--buffer-->
<!--if:logged:yes-->
<fieldset class="userblock">
	<div class="avatar-block clearfix">
		<a class="ufile" href="{link_profile}">{user_avatar}</a>
		<div>
			<strong>{user_name}</strong>
			<span>{lang_visit}:</span> {date:1:1}
		</div>
	</div>
	{add}
	<hr />
	{add_links}
	<hr />
	{message}
	<p class="user"><a class="ufile" href="{link_profile}">{lang_profile}</a></p>
	<p class="user"><a class="uout" href="{link_logout}">{lang_logout}</a></p>
</fieldset>
<!--if-->