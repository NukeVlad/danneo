<script src="{site_url}/js/jquery.form.js"></script>
<script>
$(function(){
	$.last = {last};
	{smiliearray}
	{template}
	$("#comtext").comment({
		{activejs}
	});
	$('.refresh').click(function() {
		var t = new Date().getTime();
		$('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
	});
	$('#comment-form').submit(function() {
		$('#comment-form input, textarea').removeClass('error-input').addClass('width');
		$error = false;
		$.check = new Array();
		$.check['comname'] = new Array('comname',{comname});
		$.check['comtext'] = new Array('comtext',{comsize});
		<!--if:captcha:yes-->$.check['captcha'] = new Array('captcha',5);<!--if-->
		<!--if:control:yes-->$.check['respon'] = new Array('respon',0);<!--if-->
		for(i in $.check) {
			var id = $.check[i][0], val = $.check[i][1];
			if (val == 0) {
				if ($("#" + id) != "undefined" && $("#" + id).val().length == 0) {
					$error = true;
					$("#" + id).removeClass('width').addClass('error-input');
					$("#" + id).focus(function(){
						$(this).removeClass('error-input').addClass('width');
					});
				}
			}
			if (val > 0) {
				if ($("#" + id) != "undefined" && $("#" + id).val().length == 0 || $("#" + id) != "undefined" && $("#" + id).val().length > val) {
					$error = true;
					$("#" + id).removeClass('width').addClass('error-input');
					$("#" + id).focus(function(){
						$(this).removeClass('error-input').addClass('width');
					});
				}
			}
		}
		if ($error) {
			return false;
		}
		<!--if:ajax:yes-->
		$('#sendbox').show();
		$("#errorbox").html('');
		var value = $(this).serialize();
		$.ajax({
			cache: false,
			type: 'POST',
			url: '{site_url}/index.php?dn={mod}&re=comment&ajax=1&ct=' + $.last,
			data: value,
			error: function(data) { $('#comment-form').submit(); },
			success: function(data) {
				$("#sendbox").hide();
				if (data.match(/^<!--ok ([0-9]+)-->/)) {
					var pt = data.match(/^<!--ok ([0-9]+)-->/);
					if (pt) {
						$.last = pt[1];
					}
					$("#ajaxbox").append(data).show();
					$("#comtext").attr({value:''});
					if ($("#captcha") != "undefined") {
						$("#captcha").attr({value:''});
					}
				} else {
					$("#errorbox").html(data);
				}
			}
		})
		return false;
		<!--if-->
	});

	// Array of fields
	var fields = {
		'comname': {max: 75, 'mess': '{comment_name}'},
		'comtext': {min: 0, 'mess': '{all_text}'}
	};
	<!--if:captcha:yes-->fields["captcha"] = {min: 5, max: 5, 'mess': '{help_captcha}'};<!--if-->
	<!--if:control:yes-->fields["respon"] = {'mess': '{help_control}'};<!--if-->

	// Verification form
	$("#comment-form").checkForm(fields);

	$("#comtext").autoTextarea({max: 130});
	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<div class="clear-line"></div>
<div class="sub-title comadd"><h3>{subtitle}</h3></div>
<div class="commentsend" id="sendbox" style="display:none">
	<img src="{site_url}/template/{site_temp}/images/icon/progress.gif" alt="{all_sends}" /> <span class="sendtext">{all_sends}...</span>
</div>
<form action="{post_url}" method="post" id="comment-form">
<div class="comment">
	<fieldset>
	<!--if:uname:yes-->
		<input class="width" name="comname" id="comname" type="text" value="{uname}" placeholder="{comment_name}" />
	<!--if-->
	<!--if:uname:no-->
		<input name="comname" id="comname" type="hidden" value="{uname}" />
	<!--if-->
	</fieldset>
	<fieldset>
		<textarea class="width" cols="40" rows="5" name="comtext" id="comtext" placeholder="{all_text}"></textarea>
	</fieldset>
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
		<input class="width" id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{help_control}" />
		<input name="cid" type="hidden" value="{cid}" />
	</fieldset>
    <!--if-->
</div>
<div class="send">
	<input name="id" value="{id}" type="hidden" />
	<input name="comtitle" value="{title}" type="hidden" />
	<button type="submit" class="sub post">{comment_add_button}</button>
</div>
</form>