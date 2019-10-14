<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<!--buffer:thumb:0--><figure class="{float} thumb"><a href="{url}"><img src="{site_url}/{thumb}" alt="{alt}" /></a></figure><!--buffer-->
<!--buffer:author:0--><span class="author" title="{langauthor}">{langauthor}:</span> {author}<!--buffer-->
<article role="article">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1}</time><!--if-->
		<h3>{icon} {cat} <a href="{url}">{title}</a></h3>
	</header>
	<div class="text-content">
		{image}{text}
	</div>
	<footer class="ar">
		<aside>
			<!--if:link:yes--><a class="read" href="{url}">{read}</a><!--if-->
			{author}
			<!--if:rating:yes--><span class="rating" title="{titlerate}">{langrate}:</span> {rating}<!--if--> 
			<!--if:info:yes--><span class="hits" title="{langhits}">{langhits}:</span> {hits}<!--if--> 
			<!--if:comment:yes--><span class="com" title="{comment}">{comment}:</span> {count}<!--if-->
		</aside>
		{tags}
	</footer>
</article>