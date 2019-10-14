<script>
$(document).ready(function() {
    $('.refresh').click(function() {
        var t = new Date().getTime();
        $('#divcaptcha').html('<img src="{site_url}/index.php?cap=captcha&t=' + t + '" alt="" />');
    });
    $('#captcha, #respon').focus(function () { $(this).select(); }).mouseup(function(e){ e.preventDefault(); }); 
});
</script>
<form action="{post_url}" method="post">
<div class="forms">
    <div class="form-area-cont">
        <input name="submail" type="text" placeholder="Эл. @ почта" required="required"> 
    </div>
</div>
    <div class="send">
        <input name="to" type="hidden" value="uncheck" />
        <button type="submit" class="sub mail">{unsubscribe}</button>
    </div>
</form>
