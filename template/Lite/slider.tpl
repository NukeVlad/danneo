<!--buffer:slide:0-->
<div>
	<span class="navs">{num}</span>
	{time}
	<h3><a href="{url}">{title}</a></h3>
	{image}{text}
</div>
<!--buffer-->
<div class="cont-slider">
	<div class="liquid-slider" id="slider-id-{key}">
		{slider}
	</div>
</div>
<script src="{site_url}/js/jquery.liquid.slider.js"></script>
<script>
$(function() {
	$("#slider-id-{key}").liquidSlider({
		autoSlide: {auto},
		autoHeight: true,
		mobileNavigation: false,
		panelTitleSelector: "span.navs",
		dynamicArrows: false,
		autoSlideInterval: {interval},
		dynamicTabsAlign: "{align}",
		dynamicTabsPosition: "{posit}"
	});
});
</script>