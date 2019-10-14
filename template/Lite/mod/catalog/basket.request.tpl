<script src="{site_url}/template/{site_temp}/js/jquery.maskedinput.js"></script>
<script>
$(function(){
	$("#phone").mask("9 (999) 999-99-99");
});</script>
{currency}
<!--buffer:currency:0-->
<div class="basket-block">
	<div class="drop-down">
		<div class="selected"><a href="#" title="{help_change}">{currencytitle}</a></div>
		<div class="option">
			{currencyopt}
		</div>
	</div>
	<script>
	$(".drop-down").bind("click /*mouseenter*/", function() {
		$(this).find(".option").slideToggle("fast"); return false;
	});
	$(".drop-down").bind("mouseleave", function() {
		$(this).find(".option").slideUp("fast");
	});
	</script>
</div>
<!--buffer-->
<div class="basket-block" id="basket-block">
	{basket}
	<!--buffer:empty:0-->
	<div class="basket-block-empty">
		{empty}
	</div>
	<div class="order-link">
		<strong>{make_order}</strong>
		<p><span>{no_prepayment}</span><br /> {make_notice}</p>
	</div>
	<!--buffer-->
	<!--buffer:basket:0-->
	<div class="basket-block-rows">
		<ul class="goods">
			<li><span class="g1"></span></li>
			<li><a href="{linkgods}">{title}</a></li>
			<li onclick="$('.{id}_minus, .{id}_plus').toggle();" title="{detail}"><span class="{id}_plus">&#9660;</span><span class="{id}_minus" style="display: none">&#9650;</span></li>
			<li><span onclick="$.basketdelete('{id}','{mods}');" title="{del}"></span></li>
		</ul>
		{info}
	</div>
	<!--buffer-->
	<!--buffer:info:0-->
	<ul style="display: none" class="basket-block-rows-info {id}_plus {id}_minus">
		{inforow}
	</ul>
	<!--buffer-->
	<!--buffer:inforow:0-->
	<li><div>{opt}</div> <div>{opttitle}</div> <div>{optprice}</div></li>
	<!--buffer-->
	<!--buffer:total:0-->
	<div class="basket-block-total"><span class="g2"></span>{langtotal}: <strong>{fulltotal}</strong></div>
	<!--buffer-->
	<!--buffer:basketlink:0-->
	<div class="order-link">
		<a class="form-view" href="#form-inline">{send_request}</a>
		<p>{will_contact}</p>
	</div>
	<!--buffer-->
</div>
<div style="display: none;">
	<div id="form-inline" class="form-order">
		<div class="form-order-send" id="sendbox" style="display: none">
			<img src="{site_url}/template/{site_temp}/images/loading.gif" alt="" /> <span class="sendtext">{sending}</span>
		</div>
		<form action="{site_url}" method="post" id="basket-order">
			<input class="width" name="names" id="names" placeholder="{your_name}" type="text" required="required">
			<div class="clear-line"></div>
			<input class="width" name="phone" id="phone" placeholder="{your_phone}" type="text" required="required">
			<div class="clear-line"></div>
			{forms}
			<!--buffer:formbasket:0--><input name="title[]" value="{title}" type="hidden"><input name="count[]" value="{count}" type="hidden"><input name="price[]" value="{price}" type="hidden"><!--buffer-->
			<!--buffer:formtotal:0--><input name="total" value="{fulltotal}" type="hidden"><!--buffer-->
			<input name="dn" value="{mods}" type="hidden">
			<input name="to" value="send" type="hidden">
			<button type="submit" id="submit" class="sub next">{send_request}</button>
		<div class="clear-line"></div>
		<p>{to_request}</p>
		</form>
	</div>
</div>