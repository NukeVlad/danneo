<script src="{site_url}/js/file.upload.js"></script>
<script src="{site_url}/js/jquery.form.js"></script>
<script src="{site_url}/js/editor/tinymce/tinymce.min.js"></script>
<script>
$(function(){
	// Captcha, reload
    $(".refresh").click( function () {
        var t = new Date().getTime();
        $("#divcaptcha").html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '">');
    });

	// Array of fields
	var fields = {
		'title': {max: 75, 'mess': '{title}'},
		'url': {min: 10, 'mess': '{file}'},
		'short': 0
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

    $('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });

	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$("#short").autoTextarea({max: 130});
	$("#more").autoTextarea({max: 250});
});
<!--if:editor:yes-->
$("input").removeAttr("required");
tinymce.init({
	theme : "modern",
	skin : "custom",
	selector: "textarea#short",
	language : "{lang}",
	width: "96.7%",
	schema: "html5",
    menubar : false,
	plugins: ["link symbols placeholder"],
	symbols_min: 15,
	symbols_max: 350,
	toolbar: "undo redo | bold italic underline | link unlink"
});
tinymce.init({
	theme : "modern",
	skin : "custom",
	selector: "textarea#more",
	language : "{lang}",
	width: "96.7%",
	schema: "html5",
	plugins: [
		"placeholder advlist autolink link image lists charmap preview hr anchor pagebreak spellchecker",
		"searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
		"save table contextmenu directionality emoticons template paste textcolor"
	],
	menu : {
		edit   : {title : "Edit"  , items : "undo redo | cut copy paste pastetext | selectall"},
		insert : {title : "Insert", items : "link anchor | image | hr"},
		view   : {title : "View"  , items : "visualaid preview"},
		format : {title : "Format", items : "formats removeformat"},
		table  : {title : "Table" , items : "inserttable tableprops deletetable | cell row column"}
	},
	toolbar: "insertfile undo redo | bold italic | unlink | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | preview code fullscreen"
});
<!--if-->
</script>
<form action="{post_url}" method="post" enctype="multipart/form-data" id="form-data">
<div id="form-add" class="comment">
	<fieldset>
		<input id="title" name="title" type="text" placeholder="{title}" autofocus />
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
		<textarea cols="60" rows="5" id="short" name="textshort" placeholder="{short}..."></textarea>
	</fieldset>
	<fieldset>
		<textarea cols="60" rows="10" id="more" name="textmore" placeholder="{more}..."></textarea>
	</fieldset>
	<fieldset class="data-file">
		<aside>
			<input id="image" name="image" type="file" accept="image/*, image/jpeg, image/png, image/gif, image/webp" />
			<button>{select_file}</button>
		</aside><span class="help" title="{img_help}">?</span>
		<output><span>{image}</span></output>
	</fieldset>
	<div class="clear-line"></div>
	<fieldset>
		<input id="url" name="url" type="text" placeholder="{file}" />
	</fieldset>
	<div class="clear-line"></div>
	<!--if:captcha:yes-->
	<fieldset>
		<table class="captcha">
		<tr>
			<td><input id="captcha" name="captcha" type="text" maxlength="5" title="{help_captcha}" placeholder="{captcha}" /></td>
			<td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
			<td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
		</tr>
		</table>
	</fieldset>
	<!--if-->
	<!--if:control:yes-->
	<fieldset>
		<p>{control}</p>
		<input id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{control_word}" />
		<input name="cid" type="hidden" value="{cid}" />
		<div class="clear-line"></div>
	</fieldset>
	<!--if-->
</div>
<div class="send">
	<input name="re" value="add" type="hidden" />
	<input name="to" value="save" type="hidden" />
	<button name="ok" type="submit" class="sub post">{all_add}</button>
</div>
</form>
