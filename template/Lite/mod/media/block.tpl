<!--buffer:icon:0--><figure class="imgleft thumb"><a href="{url}"><img src="{site_url}/{icon}" alt="{title}" /></a></figure><!--buffer-->
<article role="article">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1}</time><!--if-->
		<!--if:title:yes--><h3><a href="{url}">{title}</a></h3><!--if-->
	</header>
	<div class="text-content">   
        {icon}
        <!--if:cont:yes-->{text}<!--if-->
    </div>
	<!--if:info:yes--><footer><aside><!--if-->
	<!--if:cols:yes--><span class="hits" title="{langcol}">{langcol}:</span> {total}<!--if--> 
	<!--if:hits:yes--><span class="hits" title="{langhits}">{langhits}:</span> {hits}<!--if-->
	<!--if:info:yes--></aside></footer><!--if-->
</article>
