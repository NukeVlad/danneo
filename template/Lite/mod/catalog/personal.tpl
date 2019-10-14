<script src="{site_url}/template/{site_temp}/js/jquery.maskedinput.js"></script>
<script>
$(function(){
	$('#data-country').change(function() {
		var id = $(this).val();
		if (id > 0) {
			$('#data-country').attr('disabled','disabled');
			$('#data-state').attr('disabled', 'disabled');
			$.ajax({
				cache:false,
				url:'{site_url}/index.php',
				data:'dn=user&re=ajax&to=region&id=' + id,
				error:function(msg){},
				success:function(data) {
					if (data.length > 0 && data.match(/option/)) {
						$('#data-state').html(data);
					}
				}
			});
			$('#data-country').removeAttr('disabled');
			$('#data-state').removeAttr('disabled');
		}
	});
	$("#data-phone").mask("9 (999) 999-99-99");
	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$("#data-comment, #data-adress").autoTextarea({max: 130});
});
</script>
<form action="{post_url}" method="post">
<div class="detail">
    <div class="form-detail">
		<input class="width inputbox" name="data[firstname]" size="30" type="text" placeholder="{firstname} *" required="required" />
		<input class="width inputbox" name="data[surname]" size="30" type="text" placeholder="{surname} *" required="required" />
		<select id="data-country" name="data[cid]">
			<option value="0">{country} *</option>
			{countrysel}
		</select>
		<select id="data-state" name="data[sid]">
			<option value="0">{state} *</option>
			{statesel}
		</select>
		<input class="width inputbox" name="data[city]" size="30" type="text" placeholder="{city} *" required="required" />
		<input class="width inputbox" name="data[zip]" size="30" type="text" maxlength="10" placeholder="{zip} *" required="required" />
		<textarea class="width inputbox" id="data-adress" name="data[adress]" rows="3" cols="30" placeholder="{adress} *" required="required"></textarea>
		<input class="width inputbox" id="data-phone" name="data[phone]" size="30" type="text" maxlength="32" placeholder="{phone} *" required="required" />
		<textarea class="width inputbox" id="data-comment" name="data[comment]" rows="3" cols="30" placeholder="{notice}"></textarea>
	</div>
</div>
<div class="form-send">
	<input name="re" value="basket" type="hidden" />
	<input name="to" value="add" type="hidden" />
	<button type="submit" class="sub next">{proceed}</button>
</div>
</form>