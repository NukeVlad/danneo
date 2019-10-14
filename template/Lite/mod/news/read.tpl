<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<article role="article" class="open">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}" title="{public}">{date:1:1}</time><!--if-->
		<h2>{icon} {cat} {subtitle}</h2>
	</header>
	<div class="text-content">
        {image}{textshort}
        {textmore}
        <div class="clear"></div>
        {textnotice}
	</div>
	<footer>
		<aside>
			<!--if:author:yes--><span class="author" title="{langauthor}">{langauthor}:</span> {author}<!--if-->
			<!--if:rating:yes--><span class="rating" title="{titlerate}">{langrate}:</span> {ratings}<!--if--> 
			<span class="hits" title="{hits}">{hits}:</span> {counts}
			<!--if:print:yes--><span class="print"><a href="{print_url}" title="{print}">{print}</a></span><!--if-->
		</aside> 
		{tags}
		{social}
	</footer>
</article>
{search}
{rating}
{media}
{recommend}
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}