<!DOCTYPE html>
<html lang="{langcode}">
<head>
<meta charset="{langcharset}">
<title>{site_title}</title>
<meta name="description" content="{descript}">
<meta name="author" content="{site}" />
<meta name="generator" content="Danneo CMS {version}'">
<link href="{site_url}/template/{site_temp}/css/print.css" rel="stylesheet">
<link rel="canonical" href="{canonical}" />
</head>
<body>
<div class="print">
    <div class="content clearfix">
		<!--if:date:yes--><time datetime="{date:datetime}">{date:1:1}</time><!--if-->
        <div class="title">{cat} {title}</div>
        <div class="text">{image}{text}</div>
    </div>
    <div class="copy">
        <p>{copytext} &copy; <a href="{site_url}">{site}</a></p>
        <p>{print_notice}</p>
    </div>
    <p class="link">@link: <a href="{url}">{url}</a></p>
</div>
<!--buffer:thumb:0--><figure class="{float} thumb"><img src="{site_url}/{thumb}" alt="{alt}" /></figure><!--buffer-->
<!--buffer:mod:0--><a class="cat" href="{mod_url}">{mod_name}</a> <span>&#187;</span> <!--buffer-->
</body>
</html>