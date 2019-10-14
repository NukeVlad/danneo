<!--buffer:tags:0--><a href="{tag_url}" title="{tag_word}">{tag_word}</a><!--buffer-->
<article role="article" class="open">
	<header>
		<h2 class="ac">{subtitle}</h2>
	</header>
</article>
<table class="view-box">
<tr>
	<td class="nav">{prev}</td>
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
	<td class="nav">{next}</td>
</tr>
</table>
<!--if:rec:yes-->
<div class="jcarousel-wrapper">
	<a href="#" class="jcarousel-prev">&lsaquo;</a>
	<div class="jcarousel">
		<ul>
			{view}
		</ul>
	</div>
	<a href="#" class="jcarousel-next">&rsaquo;</a>
</div>
<!--if-->
<!--if:text:yes-->
<fieldset class="wrap-details">
	<legend>{descript}</legend>
    <div class="details">
		{text}
    </div>
</fieldset>
<!--if-->
{rating}
<span class="title-fold" onclick="$('.data-fold').slideToggle();">{details}</span>
<span class="title-fold" onclick="$('.share-fold').slideToggle();">{shareit}</span>
<fieldset class="wrap-details data-fold">
	<legend>{details}</legend>
    <div class="details">
        <ul><li>{langname}</li><li>{title}</li></ul>
        <ul><li>{langtime}</li><li><time datetime="{date:datetime}">{date:1:1}</time></li></ul>
        <ul><li>{langcat}</li><li><a href="{linkcat}" title="{catname}">{catname}</a></li></ul>
        {tags}
        <ul><li>{langhits}</li><li>{hits}</li></ul>
        <!--if:comment:yes--><ul><li>{comtotal}</li><li>{count}</li></ul><!--if-->
        <!--if:author:yes--><ul><li>{langauthor}</li><li>{author}</li></ul><!--if-->
    </div>
</fieldset>
<div class="clear-line"></div>
<fieldset class="wrap-details share-fold">
	<legend>{shareit}</legend>
    <div class="details">
        <ul><li>{directlink}</li><li><input class="ccode readonly" value="{link}" type="text" readonly="readonly"></li></ul>
        <ul><li>{htmlcode}</li><li><textarea class="ccode readonly" readonly="readonly">&lt;a href=&quot;{link}&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{thumb}&quot; border=&quot;0&quot; alt=&quot;&quot; /&gt;&lt;/a&gt;</textarea></li></ul>
        <ul><li>{bbcode}</li><li><textarea class="ccode readonly" readonly="readonly">[url={link}][img]{thumb}[/img][/url]</textarea></li></ul>
    </div>
</fieldset>
{social}
<script src="{site_url}/template/{site_temp}/js/jquery.jcarousel.min.js"></script>
<script src="{site_url}/template/{site_temp}/js/jcarousel.responsive.js"></script>
<script>
$(function() {
	$('.jcarousel').jcarouselAutoscroll({
		interval: 5000,
		target: '+=1',
		autostart: true
	});
});
</script>
<div class="clear"></div>
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}