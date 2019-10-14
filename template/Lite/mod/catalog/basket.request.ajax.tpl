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