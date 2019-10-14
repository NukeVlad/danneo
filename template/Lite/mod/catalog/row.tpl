<form action="{post_url}" method="post" id="basket-form">
<div class="catalog-add">
<ul>
	<li>
		<div>{langproduct}</div>
		<div>{langcol}</div>
		<div>{langprice}</div>
	</li>
	<li>
		<div>{title}</div>
		<div>{count}</div>
		<div>{price}</div>
	</li>
	{option}
	<!--buffer:option:0-->
	<li>
		<div>{optiontitle}</div>
		<div>{optionsel}</div>
		<div>+ {optionprice} (за 1шт.)</div>
	</li>
	<!--buffer-->
	<!--if:tax:yes-->
	<li>
		<div>{langtax}</div>
		<div>{titletax}</div>
		<div>{pricetax}</div>
	</li>
	<!--if-->
	<li>
		<div></div>
		<div>{langtotal}</div>
		<div><strong>{pricetotal}</strong></div>
	</li>
</ul>
<div>
	<input name="dn" value="{mods}" type="hidden">
	<input name="re" value="add" type="hidden">
	<input name="to" value="add" type="hidden">
	<input name="id" value="{id}" type="hidden">
	<button type="submit" id="recount" name="recount" class="sub rec" title="{re_count}">{re_count}</button>
	<button type="submit" id="sub" name="sub" class="sub exe">{langbasket}</button>
</div>
</div>
</form>