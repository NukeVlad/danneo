<article role="article">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}" title="{public}">{date:1}</time><!--if-->
		<h2>{title}</h2>
	</header>
	<div class="text-content">
        {image}{textshort}
        {textmore}
	</div>
	<footer>
		<aside>
		<!--if:redate:yes-->{updata}: {redate:1:1} <!--if-->
		<!--if:print:yes--><span class="print"><a href="{print_url}" title="{print}">{print}</a></span><!--if-->
		</aside>
		{social}
	</footer>
</article>
{files}
{filenotice}
{search}