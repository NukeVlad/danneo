<!--buffer:formajax:0-->
<script src="{site_url}/js/jquery.rate.js"></script>
<ul class="ajax-rating">
    <li class="current-rating" id="current-rating" style="width:{width}%;"></li>
    <li><a href="javascript:$.rate('{mod}','{id}','1');" class="one-ajax" title="{rate_1}">1</a></li>
    <li><a href="javascript:$.rate('{mod}','{id}','2');" class="two-ajax" title="{rate_2}">2</a></li>
    <li><a href="javascript:$.rate('{mod}','{id}','3');" class="three-ajax" title="{rate_3}">3</a></li>
    <li><a href="javascript:$.rate('{mod}','{id}','4');" class="four-ajax" title="{rate_4}">4</a></li>
    <li><a href="javascript:$.rate('{mod}','{id}','5');" class="five-ajax" title="{rate_5}">5</a></li>
</ul>
<!--buffer-->
<!--buffer:formrate:0-->
<form action="{post_url}" method="post">
	<select name="rate" required>
		<option value="">{choose}</option>
		<option value="1">{rate_1}</option>
		<option value="2">{rate_2}</option>
		<option value="3">{rate_3}</option>
		<option value="4">{rate_4}</option>
		<option value="5">{rate_5}</option>
	</select>
	<input type="hidden" name="re" value="rating" />
	<input type="hidden" name="id" value="{id}" />
	<button type="submit" class="sub rate">{rate_but}</button>
</form>
<!--buffer-->
<!--buffer:valrate:0--><img src="{site_url}/template/{site_temp}/images/rating/{imgrate}.png" alt="{titlerate}" /><!--buffer-->
<fieldset class="rating">
	<legend>{langrate}</legend>
	<aside>
		{formrate}
		<div id="view-rate">{valrate} <mark>{rating} <i>&#8260;</i> {totalrating} <i>&#8260;</i> {countrating}</mark></div>
		<div id="view-progress" style="display: none;"><img src="{site_url}/template/{site_temp}/images/icon/progress.gif" alt="{waitup}" /></div>
	</aside>
</fieldset>
