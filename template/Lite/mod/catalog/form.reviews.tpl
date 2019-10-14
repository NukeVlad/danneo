<script src="{site_url}/js/jquery.form.js"></script>
<script>
$( function () {
	$.last = {last};
	$('#message').textarearesizer();
	$('.refresh').click(function() {
		var t = new Date().getTime();
		$('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
	});
	$('#reviews-form').submit(function() {
		$('#reviews-form input, textarea').removeClass('error-input').addClass('width');
		$error = false;
		$.check = new Array();
		$.check['uname'] = new Array('uname', {maxname});
		$.check['region'] = new Array('region', '');
		$.check['message'] = new Array('message', {textsize});
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
			cache:false,
			type:'POST',
			url:'{site_url}/index.php?dn={mod}&re=reviews&ajax=1&ct=' + $.last,
			data:value,
			error: function(data) { $('#reviews-form').submit(); },
			success: function(data) {
				$("#sendbox").hide();
				if (data.match(/^<!--ok ([0-9]+)-->/)) {
					var pt = data.match(/^<!--ok ([0-9]+)-->/);
					if (pt) {
						$.last = pt[1];
					}
					$("#ajaxbox").append(data).show();
					$("#message").attr({value:''});
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
		'uname': {max: 75, 'mess': '{your_name}'},
		'region': {min: 0, 'mess': '{your_region}'},
		'message': {min: 0, 'mess': '{review}'}
	};
	// Array of lists
	var list = {
		'rate': {'mess': '{rate_emp}'},
		'type': 0
	};

	<!--if:captcha:yes-->fields["captcha"] = {min: 5, max: 5, 'mess': '{help_captcha}'};<!--if-->
	<!--if:control:yes-->fields["respon"] = {'mess': '{help_control}'};<!--if-->

	// Verification form
	$("#reviews-form").checkForm(fields, list);

	$("#message").autoTextarea({max: 130});
	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<div class="clear-line"></div>
<div class="sub-title comadd"><h3>{subtitle}</h3></div>
<div class="commentsend" id="sendbox" style="display:none">
    <img src="{site_url}/template/{site_temp}/images/icon/progress.gif" alt="{sends}" /> <span class="sendtext">{sends}...</span>
</div>
<form action="{post_url}" method="post" id="reviews-form">
<div class="comment">
	<fieldset>
		<input name="uname" id="uname" value="{uname}"  placeholder="{email_name} *" type="text" />
	</fieldset>
	<fieldset>
		<input name="region" id="region" value="{region}" placeholder="{your_region} *" type="text" />
	</fieldset>
	<fieldset>
		<select name="rate" id="rate">
			<option value="0">{rate_emp} *</option>
			<option value="5">{rate_5}</option>
			<option value="4">{rate_4}</option>
			<option value="3">{rate_3}</option>
			<option value="2">{rate_2}</option>
			<option value="1">{rate_1}</option>
		</select>
	</fieldset>
	<fieldset>
		<textarea cols="40" rows="5" name="message" id="message" placeholder="{message} *"></textarea>
	</fieldset>
	<!--if:captcha:yes-->
	<fieldset>
		<table class="captcha">
		<tr>
			<td><input id="captcha" name="captcha" type="text" maxlength="5" title="{help_captcha}" placeholder="{captcha} *" /></td>
			<td><div id="divcaptcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
			<td><img class="refresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{refresh}" /></td>
		</tr>
		</table>
	</fieldset>
	<!--if-->
    <!--if:control:yes-->
	<fieldset>
		<p>{question}</p>
		<input id="respon" name="respon" size="30" type="text" title="{help_control}" placeholder="{control} *" />
		<input name="cid" type="hidden" value="{cid}" />
	</fieldset>
    <!--if-->
</div>
<div class="send">
	<input name="id" value="{id}" type="hidden" />
	<input name="title" value="{title}" type="hidden" />
	<button type="submit" class="sub post">{add_button}</button>
</div>
</form>