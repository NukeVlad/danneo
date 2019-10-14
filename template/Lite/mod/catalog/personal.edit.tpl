<script src="{site_url}/template/{site_temp}/js/jquery.maskedinput.js"></script>
<script>
$(function(){
	$("#data-phone").mask("9 (999) 999-99-99");
	$('input, textarea').placeholder({customClass:'text-placeholder'});
	$("#data-comment, #data-adress").autoTextarea({max: 130});
});
</script>
<form action="{post_url}" method="post">
<div class="detail">
    <div class="form-detail">
		<input class="width inputbox" name="data[firstname]" size="30" type="text" value="{firstnameval}" placeholder="{firstname} *" required="required" />
		<input class="width inputbox" name="data[surname]" size="30" type="text" value="{surnameval}" placeholder="{surname} *" required="required" />
		<input class="width inputbox" name="data[city]" size="30" type="text" value="{cityval}" placeholder="{city} *" required="required" />
		<input class="width inputbox" name="data[zip]" size="30" type="text" value="{zipval}" maxlength="10" placeholder="{zip} *" required="required" />
		<textarea class="width inputbox" id="data-adress" name="data[adress]" rows="3" cols="30" placeholder="{adress} *" required="required">{adressval}</textarea>
		<input class="width inputbox" id="data-phone" name="data[phone]" size="30" type="text" value="{phoneval}" maxlength="32" placeholder="{phone} *" required="required" />
		<textarea class="width inputbox" id="data-comment" name="data[comment]" rows="3" cols="30" placeholder="{notice}">{commentval}</textarea>
	</div>
</div>
<div class="form-send">
	<input name="id" value="{id}" type="hidden" />
	<input name="re" value="order" type="hidden" />
	<input name="to" value="save" type="hidden" />
	<button type="submit" class="sub next">{proceed}</button>
</div>
</form>