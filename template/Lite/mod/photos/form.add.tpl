<script src="{site_url}/js/file.upload.js"></script>
<script src="{site_url}/js/jquery.form.js"></script>
<script>
$(function(){
	$('#text').textarearesizer();
	$('.refresh').click(function() {
		var t = new Date().getTime();
		$('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
	});
	// Array of fields
	var fields = {
		'title': {max: 75, 'mess': '{title}'},
		'text': {min: 75, 'mess': '{descript}'}
	};
	<!--if:captcha:yes-->fields["captcha"] = {min: 5, max: 5, 'mess': '{help_captcha}'};<!--if-->
	<!--if:control:yes-->fields["respon"] = {'mess': '{help_control}'};<!--if-->

	// Array of lists
	var list = {
		'catid': {'mess': '{no_cat}'},
		'type': 0
	};

	// Verification form
	$("#form-data").checkForm(fields, list);

	// Choice of the image
	$("#image").change( function () {
		check_image(2048, "jpe?g|gif|png|webp", "{is_large}", "{incor_format}");
	});

	$("#text").autoTextarea({max: 100});
	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<form action="{post_url}" method="post" enctype="multipart/form-data" id="form-data">
<div id="form-add" class="comment">
	<fieldset>
		<input id="title" name="title" type="text" placeholder="{title}" autofocus="autofocus" />
	</fieldset>
	<!--if:showcat:yes-->
	<fieldset>
		<select class="width" id="catid" name="catid">
		<option value="0">{in_cat} / {select}</option>
			{sel}
		</select>
	</fieldset>
	<!--if-->
	<fieldset>
		<textarea cols="60" rows="5" id="text" name="text" placeholder="{descript}..."></textarea>
	</fieldset>
	<fieldset class="data-file photos">
		<aside>
			<input id="image" name="image" type="file" accept="image/*, image/jpeg, image/png, image/gif, image/webp" />
			<button>{select_file}</button>
		</aside><span class="help" title="{img_help}">?</span>
		<output><span>{image}</span></output>
	</fieldset>
	<!--if:captcha:yes-->
	<fieldset>
		<table class="captcha">
		<tr>
			<td><input id="captcha" name="captcha" type="text" maxlength="5" placeholder="{captcha}" title="{help_captcha}" /></td>
			<td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
			<td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
		</tr>
		</table>
	</fieldset>
	<!--if-->
	<!--if:control:yes-->
	<fieldset>
		<p>{control}</p>
		<input id="respon" name="respon" size="30" type="text" placeholder="{control_word}" title="{help_control}" />
		<input name="cid" type="hidden" value="{cid}" />
		<div class="clear-line"></div>
	</fieldset>
	<!--if-->
</div>
<div class="send">
	<input name="re" value="add" type="hidden" />
	<input name="to" value="save" type="hidden" />
	<button name="ok" type="submit" class="sub add">{all_add}</button>
</div>
</form>