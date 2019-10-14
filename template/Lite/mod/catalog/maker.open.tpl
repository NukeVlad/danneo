<!--buffer:icon:0--><div class="thumb imgleft"><img src="{site_url}/{img}" alt="{title}" /></div><!--buffer-->
<article role="article">
	<header>
		<h4>{title}</h4>
	</header>
	<div class="text-content">
		{icon}{text}
        <div class="clear-line"></div>
	</div>
	<!--if:adds:yes--><aside class="catalog-adds"><!--if-->
		<!--if:adress:yes--><div><span>{langadress}:</span> {adress}</div><!--if-->
		<!--if:site:yes--><div><span>{langsite}:</span> {site}</div><!--if-->
		<!--if:phone:yes--><div><span>{langphone}:</span> {phone}</div><!--if-->
	<!--if:adds:yes--></aside><!--if-->
</article>