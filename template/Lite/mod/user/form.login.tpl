<form action="{post_url}" method="post"> 
<div class="forms">
    <label for="nlogin">{login}<i></i></label>
    <input name="login" id="nlogin" size="35" type="text" maxlength="{maxname}" required>
    <div class="clear-line"></div>  
    <label for="npassw">{pass}<i></i></label>
    <input name="passw" id="npassw" size="35" type="password" maxlength="{maxpass}" required>
    <div class="clear-line"></div>
    <div>
        <p class="user"><a href="{linklost}" rel="nofollow">{send_pass}</a></p>
        <p class="user"><a href="{reglink}" title="{registr}" rel="nofollow">{registr}</a></p>
    </div>
</div> 
<div class="send">
	<input name="re" value="login" type="hidden" />
	<input name="to" value="check" type="hidden" />
	{redirect}
	<button type="submit" class="sub acc">{enter}</button>
</div>
</form>
