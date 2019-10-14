<script src="{site_url}/js/jquery.form.js"></script>
<script>
$(function(){
    $('.bc').click(function() {
        var t = new Date().getTime();
        $('.ibc').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
	$('input, textarea').placeholder({customClass:'text-placeholder'});
    $('#mcaptcha, #mrespon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<form action="{post_url}" method="post" enctype="multipart/form-data">
<div class="forms block">  
    <div class="form-area-cont">
    <input name="sendnames" type="text" value="{uname}" placeholder="{email_name}" required />
        <div class="clear-line"></div>
    <input name="sendorg" type="text" placeholder="{email_org}" />
        <div class="clear-line"></div>
    <input name="sendphone" type="text" placeholder="{email_phone}" />
        <div class="clear-line"></div>
    <input name="sendmails" type="text" value="{umail}" placeholder="{email}" required />
        <div class="clear-line"></div>
    <textarea cols="40" rows="5" name="sendtexts" placeholder="{email_text}..." required></textarea>
    <!--if:attach:yes-->
        <div class="clear-line"></div>
    <label for="files" title="{file_help}">{email_file}</label>
    <input class="width" name="files[]" id="files" multiple="multiple" type="file" />
    <!--if-->
    <!--if:captcha:yes-->
        <div class="clear-line"></div>
    <label for="mcaptcha" title="{help_captcha}">{captcha}<b></b></label>
    <table class="captcha">
    <tr>
        <td><input name="captcha" id="mcaptcha" type="text" maxlength="5" required /></td>
        <td><div id="divcaptcha" class="ibc"><img src="{site_url}/index.php?cap=captcha" alt="" /></div></td>
        <td><img class="refresh bc" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" /></td>
    </tr>
    </table>
    <!--if-->
    <!--if:control:yes-->
        <div class="clear-line"></div>
    <label for="mrespon" title="{help_control}">{control_word}<b></b></label>
        <p>{control}</p>
        <input class="width" id="mrespon" name="respon" size="30" type="text" required />
        <input name="cid" type="hidden" value="{cid}" />
    <!--if--> 
    </div>
</div>
    <div class="send">
        <input name="to" type="hidden" value="send" /><!--if:to:yes--><input name="to_email" type="hidden" value="{to_email}" /><!--if-->
        <button type="submit" class="sub mail">{email_send}</button>
    </div>
</form>
