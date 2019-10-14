<form action="{post_url}" method="post">
<div class="clear-line"></div>
<div class="forms">
    <label for="lostmail" title="{rest_pass_hint}">E-Mail<i></i></label>
    <input class="pw90" name="lostmail" id="lostmail" size="30" type="text" required><span class="help" title="{rest_pass_hint}">?</span>
    <div class="clear-line"></div>
</div>
<div class="send">
	<input name="re" value="lost" type="hidden" />
	<input name="to" value="send" type="hidden" />
	<button type="submit" class="sub acc">{send_pass}</button>
</div>
</form>
