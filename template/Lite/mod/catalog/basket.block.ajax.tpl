{basket}
	<!--buffer:empty:0-->
	<div class="basket-block-empty">
		{empty}
	</div>
	<!--buffer-->
	
	<!--buffer:basket:0-->
	<div class="basket-block-rows">
		<ul class="goods">
			<li><span class="g1"></span></li>
			<li><a href="{linkgods}">{title}</a></li>
			<li onclick="$('.{id}_minus, .{id}_plus').toggle();" title="{detail}"><span class="{id}_plus">&#9660;</span><span class="{id}_minus" style="display: none">&#9650;</span></li>
			<li><span onclick="$.basketdelete('{id}','catalog');" title="{del}"></span></li>
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

	<!--buffer:basketactive:0-->
	<div class="basket-block-rows">
		<a class="rows" href="{linkorder}">
			<span class="g5"></span> {orderactive} <mark>({oa})</mark>
		</a>
	</div>
	<!--buffer-->
	<!--buffer:baskethistory:0-->
	<div class="basket-block-rows">
		<a class="rows" href="{linkhistory}">
			<span class="g6"></span> {orderhistory} <mark>({oa})</mark>
		</a>
	</div>
	<!--buffer-->

	<!--buffer:basketlink:0-->
	<div class="basket-block-rows">
		<a class="rows" href="{linkpersonal}">
			<span class="g3"></span> {checkout}
		</a>
	</div>
	<div class="basket-block-rows">
		<a class="rows" href="{linkbasket}">
			<span class="g4"></span> {viewbasket}
		</a>
	</div>
	<!--buffer-->