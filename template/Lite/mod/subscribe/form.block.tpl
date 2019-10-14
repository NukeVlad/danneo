<script>
$(function(){
    $('.subrefresh').click(function() {
        var t = new Date().getTime();
        $('#subcapcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
	$('input, textarea').placeholder({customClass:'text-placeholder'});
    $('#scaptcha, #srespon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); });
});
</script>
<form action="{post_url}" method="post">
<div class="form-block">
        <input name="subname" id="subname" type="text" placeholder="{subscribe_your_name}" required>

        <input name="submail" id="submail" type="text" placeholder="{subscribe_your_mail}" required>

        <select name="subformat">
            <option value="0" selected="selected">{subscribe_your_format}</option>
            <option value="0">Text</option>
            <option value="1">Html</option>
        </select>

    <!--if:captcha:yes-->
        <input id="scaptcha" name="captcha" type="text" placeholder="{captcha}" required>
        <div class="sbrcapcha">
            <div id="subcapcha"><img src="{site_url}/index.php?cap=captcha" alt="" /></div>
            <img class="subrefresh" src="{site_url}/template/{site_temp}/images/icon/refresh.png" alt="{all_refresh}" />
        </div>
    <!--if-->
    <!--if:control:yes-->
        <p>{control}</p>
        <p><input id="srespon" name="respon" size="30" type="text" placeholder="{control_word}" required></p>
        <input name="cid" type="hidden" value="{cid}" />
   <!--if-->
   <div>
       <input name="to" type="hidden" value="check" />
       <button type="submit" class="sub mail">{subscribe_button}</button>
   </div>
</div>
</form>
