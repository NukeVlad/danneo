<!--buffer:thumb:0--><figure class="{float} thumb"><a href="{url}"><img src="{site_url}/{thumb}" alt="{alt}" /></a></figure><!--buffer-->
<!--buffer:priceold:0--><div class="price-del">{langpriceold}: <del>{priceold}</del></div><!--buffer-->
<!--buffer:discount:0--><span>{percent}%</span><!--buffer-->
<!--buffer:ajaxadd:0-->class="basket-add"<!--buffer-->
<!--buffer:price:0--><strong>{price}</strong><!--buffer--><!--buffer:agreed:0--><strong>{price}</strong><!--buffer-->
<article role="article" class="block-day">
	<h2><a href="{url}">{title}</a></h2>
	<ul>
		<li>
			{image}
			<div class="clear-line"></div>
			{text}
		</li>
		<li>
			<!--if:buy:yes-->
			<aside class="buy-wrap">
				<div>
					<div class="price-cost">
						<strong>{price}</strong> {discount}
					</div>
					{priceold}
					<p>{langstore}: <span>{store}</span></p>
				</div>
				<div>
					<form {ajaxadd} action="{post_url}" method="post">
						<input name="count" size="5" value="{count}" type="text">
						<input name="dn" value="{mods}" type="hidden">
						<input name="re" value="add" type="hidden">
						<input name="to" value="index" type="hidden">
						<input name="id" value="{id}" type="hidden">
						<button class="sub buy" type="submit">{langbasket}</button>
					</form>
				</div>
			</aside>
			<!--if-->
			<div class="info-day">
				<!--buffer:maker:0-->
				<p>{maker_lang}: <a href="{maker_url}">{maker_name}</a></p>
				<!--buffer-->
				{maker}
				<div>{rating}</div>
			</div>
		</li>
	</ul>
</article>