<!--buffer:thumb:0--><img class="no" src="{site_url}/{thumb}" alt="{title}" /><!--buffer-->
<!--buffer:image:0--><a rel="page" class="no media-view" href="{site_url}/{image}" data-title="{title}"><img src="{site_url}/{thumb}" alt="{title}" /></a><!--buffer-->
<figure class="{float} thumb">
	<!--if:image:no--><img src="{site_url}/{thumb}" alt="{alt}" /><!--if-->
	<!--if:image:yes--><a rel="page" class="media-view" href="{site_url}/{image}" data-title="{alt}"><img src="{site_url}/{thumb}" alt="{alt}" /></a><!--if-->
	{subimg}
</figure>