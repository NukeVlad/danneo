<!--buffer:pagesout:0--><div class="pages">{pages}</div><!--buffer-->
<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<article role="article" class="open">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}" title="{public}">{date:1:1}</time><!--if-->
		<h2>{subtitle}</h2>
	</header>
	<div class="text-content">
		{rubric}
		{image}{contents}
		{pages}
        <div class="clear"></div>
        {textnotice}
	</div>
    {video}
	<footer>
		<aside>
			<!--if:redate:yes-->{updata}: {redate:1:1} <!--if-->
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
{files}
{filenotice}
{rating}
{media}
{recommend}
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}