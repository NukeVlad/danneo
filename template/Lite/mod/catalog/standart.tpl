<!--buffer:thumb:0--><figure class="{float} thumb"><a href="{url}"><img src="{site_url}/{thumb}" alt="{alt}" /></a></figure><!--buffer-->
<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<!--buffer:priceold:0--><div class="price-del">{langpriceold}: <del>{priceold}</del></div><!--buffer-->
<!--buffer:discount:0--><span>{percent}%</span><!--buffer-->
<!--buffer:tax:0--><p>{langinc} <span>{tax}</span></p><!--buffer-->
<!--buffer:buyinfo:0--><mark class="action">{stock}</mark><!--buffer-->
<!--buffer:recinfo:0--><mark class="rec">{mess}</mark><!--buffer-->
<!--buffer:ajaxadd:0-->class="basket-add"<!--buffer-->
<!--buffer:price:0--><strong>{price}</strong><!--buffer--><!--buffer:agreed:0--><strong>{price}</strong><!--buffer-->
<article role="article">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1}</time><!--if-->
		<h3>{icon} {cat} <a href="{url}">{title}</a></h3>
	</header>
	<div class="text-content clearfix">
		{buyinfo}{recinfo}{image}{text}
	</div>
	{buying}
	<!--buffer:buy:0-->
	<aside class="buy-wrap">
		<div>
			<div class="price-cost">
				{price} {discount}
			</div>
			{tax}
			{priceold}
			<p>{langstore}: <span>{store}</span></p>
		</div>
		<div>
			<form {ajaxadd} action="{post_url}" method="post">
				<input name="count" size="5" value="{count}" type="hidden">
				<input name="dn" value="{mods}" type="hidden">
				<input name="re" value="add" type="hidden">
				<input name="to" value="index" type="hidden">
				<input name="id" value="{id}" type="hidden">
				<button class="sub buy" type="submit">{langbasket}</button>
			</form>
		</div>
	</aside>
	<!--buffer-->
	<footer class="ar">
		<aside>
			<!--if:link:yes--><div class="reads fl"><a class="read" href="{url}">{read}</a></div><!--if-->
			<!--if:rating:yes--><span class="rating" title="{titlerate}">{langrate}:</span> {rating}<!--if--> 
			<!--if:review:yes--><span class="com" title="{langreview}">{langreview}:</span> {review}<!--if-->
		</aside>
		{tags}
	</footer>
</article>