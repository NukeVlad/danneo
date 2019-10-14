<figure class="media">
	<a href="{url}">
		<img src="{site_url}/{thumb}" alt="{alt}" />
		<!--if:title:yes--><h4>{title}</h4><!--if-->
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1}</time><!--if-->
		<aside>
			<div class="media-info">
			<!--if:rating:yes--><span class="rating" title="{titlerate}">{langrate}:</span>{rating} &nbsp; <!--if-->
			<!--if:comment:yes--><span class="com" title="{comment}">{comment}:</span>{count} &nbsp; <!--if-->
			<span class="hits" title="{langhits}">{langhits}:</span>{hits}
			</div>
		</aside>
	</a>
</figure>
