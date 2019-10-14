<script src="{site_url}/js/jquery.tabs.js"></script>
<script src="{site_url}/template/{site_temp}/js/jquery.maskedinput.js"></script>
<script>
$(function(){
    $(".tab-4 img").click(function() {
		$('.tab-1').show();
		localStorage.setItem('tab', '.tab-1');
		$(".avatar").html('<img src="' + $(this).attr("src") + '" alt="' + $(this).attr("id") + '" /><input id="ins" name="edit[avatar]" value="' + $(this).attr("id") + '" type="hidden" />')
		$('[data-tabs=".tab-1"]').addClass('current');
		$('[data-tabs=".tab-4"]').removeClass('current');
		$($('[data-tabs=".tab-4"]').attr('data-tabs')).hide();
        return false;
    });
	$("#data-country").change(function() {
		var id = $(this).val();
		if (id > 0) {
			$.ajax({
				cache:false,
				url:'{site_url}/index.php',
				data:"dn=user&re=ajax&to=region&id=" + id,
				error:function(msg){},
				success:function(data) {
					if (data.length > 0 && data.match(/option/)) {
						$("#data-state").html(data);
					}
					$("#data-state").prop("disabled", false );
				}
			});
		} else {
			$("#data-state").prepend('<option value="0">&#8212;</option>');
			$("#data-state").find("option:not(:first)").remove().end().prop("disabled", true );
		}
	});
	$('.tabs li').tabs('.tab-1');
	$('textarea').autoTextarea({max: 130});
	$("#phone").mask("9 (999) 999-99-99");
});
</script>
<ul class="tabs">
	<li data-tabs=".tab-1">{user_data}</li>
	<!--if:editpass:yes--><li data-tabs=".tab-2">{chang_pass}</li><!--if-->
	<!--if:editmail:yes--><li data-tabs=".tab-3">{chang_email}</li><!--if-->
	<li data-tabs=".tab-4">{lang_avatar}</li>
</ul>
<div class="btop-null form-area profile">
    <div class="tab-1">
    <form action="{post_url}" method="post"> 
		<div class="main-data">
			{avatar}
			<strong>{username}</strong>
			<p><span>{registration}:</span> {date:1}</p>
			<p><span>{last_visit}:</span> {redate:%F j, Y, g:i a%}</p>
        <div class="clear"></div>
		</div>
		<label>{lang_country}</label>
		<select class="pw90" id="data-country" name="country">
			<option value="0">&#8212;</option>
			{countrysel}
		</select>
		<div class="clear-line"></div>
		<label>{lang_state}</label>
		<select class="pw90" id="data-state" name="region">
			<option value="0">&#8212;</option>
			{statesel}
		</select>
        <div class="clear-line"></div>
        <label for="city">{lang_city}</label>
        <input class="pw90" name="edit[city]" id="city" type="text" maxlength="50" value="{city}" />
        <div class="clear-line"></div>

        <label for="phone">{lang_phone}</label>
        <input class="pw90" name="edit[phone]" id="phone" maxlength="32" type="text" value="{phone}" /><span class="help" title="{phone_hint}">?</span>
        <div class="clear-line"></div> 

        <label for="skype" title="{skype_hint}">Skype</label>
        <input class="pw90" name="edit[skype]" id="skype" type="text" maxlength="50" value="{skype}" />
        <div class="clear-line"></div> 

        <label for="www" title="{www_hint}">{urlname}</label>
        <input class="pw90" name="edit[www]" id="www" type="text" maxlength="50" value="{url}" />
        <div class="clear-line"></div> 

        <!--buffer:apart:0-->
        <div class="fields-title">{name}</div>
        <!--buffer-->
		
		<div class="fields">
        {addit_fields}
		</div>

        <!--buffer:field:0-->
        <label{empty}>{name}{req}</label>
        {field}
        <div class="clear-line"></div>
        <!--buffer-->

        <div class="send" style="margin: 10px 0 0;">
            <input name="to" value="redata" type="hidden" />
            <button type="submit" class="sub ups">{up_data}</button>
        </div>
    </form>
    </div>
    <!--if:editpass:yes-->
    <div class="tab-2">
    <form action="{site_url}/index.php?dn=user" method="post"> 
        <label for="onepassw">{pass}<i></i></label>
        <input class="pw90" name="onepassw" id="onepassw" size="30" type="password" maxlength="{maxpass}" required="required"><span class="help" title="{pass_hint}">?</span>
        <div class="clear-line"></div>
         
        <label for="twopassw">{re_pass}<i></i></label>
        <input class="pw90" name="twopassw" id="twopassw" size="30" type="password" maxlength="{maxpass}" required="required">
         
        <div class="send" style="margin: 15px 0 0;">
            <input name="to" value="repassw" type="hidden" />
            <button type="submit" class="sub ups">{chang_button_pass}</button>
        </div>
    </form>
    </div>
    <!--if-->
    <!--if:editmail:yes-->
    <div class="tab-3">
    <form action="{site_url}/index.php?dn=user" method="post"> 
        <label for="onemail">{e_mail}<i></i></label>
        <input class="pw90" name="edit[onemail]" id="onemail" size="30" type="text" value="{umail}" required="required"><span class="help" title="{mail_hint}">?</span>
        <div class="clear-line"></div>
         
        <label for="twomail">{re_e_mail}<i></i></label>
        <input class="pw90" name="edit[twomail]" id="twomail" size="30" type="text" value="{umail}" required="required">
        <div class="clear"></div>
         
        <div class="send" style="margin: 15px 0 0;">
            <input name="to" value="remail" type="hidden" />
            <button type="submit" class="sub ups">{chang_button_email}</button>
        </div>
    </form>
    </div>
    <!--if-->

    <div class="tab-4">
        {avatarlist}
    </div>

</div>

<!--buffer:avatar_danneo:0-->
<div class="avatar">
	<img src="{site_url}{src}" alt="{alt}" /><input id="ins" name="edit[avatar]" value="{name}" type="hidden" />
</div>
<!--buffer-->

<!--buffer:avatar_thumb:0-->
<div class="thumb">
    <a href=""><img id="{name}" src="{site_url}{path}" alt="{alt}" /></a>
</div>
<!--buffer-->
