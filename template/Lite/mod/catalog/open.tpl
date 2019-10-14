<!--buffer:files:0--><div>{key}. <a href="{site_url}/{path}">{title}</a></div><!--buffer-->
<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<!--buffer:priceold:0--><div class="price-del">{langpriceold}: <del>{priceold}</del></div><!--buffer-->
<!--buffer:discount:0--><span>{percent}%</span><!--buffer-->
<!--buffer:tax:0--><mark>{langinc} {tax}</mark><!--buffer-->
<!--buffer:ajaxadd:0-->class="basket-add"<!--buffer-->
<!--buffer:amount:0--><p>{langmin}: <span>{count} {pcs}</span></p><!--buffer-->
<!--buffer:price:0--><strong>{price}</strong><!--buffer--><!--buffer:agreed:0--><strong>{price}</strong><!--buffer-->
<article role="article" class="open">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1:1}</time><!--if-->
		<h2>{icon} {cat} {subtitle}</h2>
	</header>
	<div class="text-content">
		<!--if:buy:yes-->
		<aside class="buy-wrap">
			<div>
				<div class="price-cost">
					{price} {discount} {tax}
				</div>
				{priceold}
				<p>{langstore}: <span>{store}</span></p>
				{amountmin}
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
        {image}{textshort}
        {textmore}
	</div>
</article>
<div itemscope itemtype="http://schema.org/Product">
	<meta itemprop="name" content="{title}" />
	<meta itemprop="description" content="{descript}" />
	<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="{itemprice}" />
		<meta itemprop="priceCurrency" content="{currency}" />
		<meta itemprop="availability" content="http://schema.org/{availability}" />
	</div>
	<div class="left" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
		<meta itemprop="ratingValue" content="{ratingvalue}" />
		<meta itemprop="bestRating" content="5" >
		<meta itemprop="worstRating" content="0" >
		<meta itemprop="ratingCount" content="{ratingcount}" />
	</div>
</div>
<!--if:buyinfo:yes-->
<div class="catalog-action">
<fieldset>
    <legend><span>{stock}</span></legend>
	{buyinfo}
</fieldset>
</div>
<!--if-->
<!--if:files:yes-->
<fieldset class="catalog-files">
    <legend>{langdown}</legend>
	{files}
</fieldset>
<!--if-->
{maker}
{details}
{associat}
{rating}
<aside class="article-footer">
	{tags}{social}
</aside>
<div class="clear"></div>
{recommend}
{reviews}
{ajaxbox}
<div id="errorbox"></div>
{reform}