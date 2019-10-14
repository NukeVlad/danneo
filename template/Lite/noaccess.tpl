<script>
$(function(){
	$('input, textarea').placeholder({customClass:'text-placeholder'});
});
</script>
<!--if:title:yes--><h3 id="user" class="breadcrumb of">{title}</h3><!--if-->
<form action="{post_url}" method="post"> 
<div class="forms">
	<fieldset>
		<input name="login" size="35" type="text" maxlength="{maxname}" placeholder="{login}" required>
	</fieldset>
	<fieldset>  
		<input name="passw" id="npassw" size="35" type="password" maxlength="{maxpass}" placeholder="{pass}" required>
	</fieldset>
    <div>
		<em>{to_enter}</em>
        <p class="user"><a rel="nofollow" href="{linklost}" title="{send_pass}">{send_pass}</a></p>
        <p class="user"><a rel="nofollow" href="{linkreg}" title="{registr}">{registr}</a></p>
    </div>
</div> 
<div class="send">
	<input name="re" value="login" type="hidden" />
	<input name="to" value="check" type="hidden" />
	{redirect}
	<button type="submit" class="sub acc">{enter}</button>
</div>
</form>
