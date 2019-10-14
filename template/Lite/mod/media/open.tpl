<article role="article" class="open">
	<header>
		<h2 class="ac">{subtitle}</h2>
	</header>
</article>
<table class="view-box">
<tr>
	<td>
		<div class="view">
			<!--if:lightbox:yes--><a class="media-view" href="{site_url}/{image}" data-title="{alt}"><!--if-->
			<!--if:image:yes--><img src="{site_url}/{image}" alt="{alt}"{size} /><!--if-->
			<!--if:lightbox:yes--></a><!--if-->
            <!--if:video:yes-->
            <object>
                <embed src="{site_url}/up/mediaplayer.swf" loop="true" quality="high" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" flashvars="file={video}&amp;searchbar=false" width="450" height="300"></embed>
            </object>
            <!--if-->
		</div>
	</td>
</tr>
</table>
<!--if:text:yes-->
<fieldset class="wrap-details">
	<legend>{descript}</legend>
    <div class="details">
		{text}
    </div>
</fieldset>
<!--if-->
{social}
<div class="clear"></div>