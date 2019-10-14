<!--buffer:icon:0--><img src="{site_url}/{icon}" alt="{alt}" /><!--buffer-->
<!--buffer:cat:0--><a class="cat" href="{caturl}">{catname}</a> <span>&#187;</span><!--buffer-->
<article role="article" class="open">
	<header>
		<!--if:date:yes--><time datetime="{date:datetime}" title="{public}">{date:1:1}</time><!--if-->
		<h2>{subtitle}</h2>
	</header>
	<div class="text-content">
        {image}{textshort}
        {textmore}{textnotice}
	</div>
	{tags}
</article>
<fieldset class="wrap-details">
	<legend>{file}</legend>
	<div class="pload">
		<!--if:viewuser:yes--><a class="sub load" href="{load}" rel="nofollow">{download}</a><!--if-->
		<!--if:viewuser:no--><a class="sub load" href="{login}">{users}</a><!--if-->
	</div>
</fieldset>
<fieldset class="wrap-details">
	<legend>{details}</legend>
    <div class="details">
        <ul><li>{langhits}</li><li>{counts}</li></ul>
        <ul><li>{langtrans}</li><li>{trans}</li></ul>
        <!--if:mirror:yes--><ul><li>{langmir}</li><li>{mirrors}</li></ul><!--if-->
        <!--if:version:yes--><ul><li>{langversion}</li><li>{valversion}</li></ul><!--if-->
        <!--if:author:yes--><ul><li>{langauthor}</li><li>{author}</li></ul><!--if-->
        <!--if:site:yes--><ul><li>{langsite}</li><li>{url_site}</li></ul><!--if-->
        <!--if:mail:yes--><ul><li>{email}</li><li>{valmail}</li></ul><!--if-->
        <!--if:type:yes--><ul><li>{langtype}</li><li>{valtype}</li></ul><!--if-->
        <!--if:size:yes--><ul><li>{langsize}</li><li>{valsize}</li></ul><!--if-->
    </div>
</fieldset>
{rating}
<div class="clear-line"></div> 
<div class="clearfix">
	{social}
	<!--if:broken:yes--><a class="broken" href="javascript:$.broken('{broken}');" rel="nofollow">{brokenlink}</a><!--if-->
</div>
{search}
{media} 
<div class="clear-line"></div>
{recommend}
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}
