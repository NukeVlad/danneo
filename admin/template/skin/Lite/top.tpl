<!DOCTYPE html>
<html lang={lang}>
<head>
<meta charset="{charset}">
<title>CMS Danneo {version}</title>
<link href="{adm_path}/template/skin/{adm_temp}/css/base.css?v.{version}" rel="stylesheet" />
<link href="{adm_path}/template/skin/{adm_temp}/css/template.css?v.{version}" rel="stylesheet" />
<link href="{adm_path}/template/skin/{adm_temp}/css/filebrowser.css" rel="stylesheet" />
<link href="{adm_path}/template/skin/{adm_temp}/css/calendar/calendar.css" rel="stylesheet" />
<link href="{adm_path}/template/skin/{adm_temp}/css/colorbox/colorbox.css" rel="stylesheet" />
<link href="{adm_path}/template/css/colorpicker.css" rel="stylesheet" />
<!--[if IE 9]><link rel="stylesheet" href="{adm_path}/template/skin/{adm_temp}/css/ie.9.css" /><![endif]-->
<script src="{site_dir}/js/jquery.js"></script>
<script>
$(function () {
	var loading = '{wait_up}';
	var saves = '{all_save}';
	$.apanel = '{apanel}';
	$.template = '{adm_temp}';
});
</script>
<script src="{adm_path}/js/script.js"></script>
<script src="{adm_path}/js/jquery.apanel.js"></script>
<script src="{adm_path}/js/jquery.gritter.js"></script>
<script src="{adm_path}/js/jquery.tooltip.js"></script>
<script src="{adm_path}/js/jquery.textarea.js"></script>
<script src="{adm_path}/js/jquery.colorbox.js"></script>
<script src="{adm_path}/js/jquery.colorpicker.js"></script>
<script src="{adm_path}/js/jquery.filebrowser.js"></script>
<script src="{adm_path}/js/url.min.js"></script>
<script src="{adm_path}/js/calendar/calendar.js"></script>
<script src="{adm_path}/js/calendar/lang/calendar-{lang}.js"></script>
<link rel="shortcut icon" href="{site_dir}/favicon.ico" type="image/x-icon" />
<link rel="icon" href="{site_dir}/favicon.ico" type="image/x-icon" />
</head>
<body>
<header>
	<h1>{control_panel} {platform}</h1>
</header>
<nav>
	<a class="home" href="{site_url}/" target="_blank">Сайт</a>
	<a class="desk" href="{adm_path}/index.php?dn=index&amp;ops={hash}">Рабочий стол</a>
	<!--if:wysiwyg:yes--><a class="spaw-on" href="{adm_path}/index.php?dn=nowys&amp;ops={hash}">Редактор</a><!--if-->
	<!--if:wysiwyg:no--><a class="spaw-of" href="{adm_path}/index.php?dn=yeswys&amp;ops={hash}">Редактор</a><!--if-->
	<!--if:filebrowser:yes--><a class="file" href="javascript:$.filebrowser('{hash}', '/', '');">{filebrowser}</a><!--if-->
	<a class="out" href="{adm_path}/index.php?dn=logout&amp;ops={hash}">{goto_logout}</a>
</nav>
<table class="wrapper">
	<tr>
		{aside_menu}
		<td class="center">
			<div class="breadcrumb">
				<div class="menu-toggle"><img id="menup" src="{adm_path}/template/skin/{adm_temp}/images/{type_arrow}.gif" alt="{openclose}" /></div>
				{breadcrumb}
				<!--if:platform:yes-->
				<form class="site-platform" action="javascript:void(0);" method="post" id="ajaxplatform">
					<div class="sel-plat"><legend></legend>
					<select name="ajaxpid" class="sw150">
						<option value="0">{def_site}</option>
						{option_platform}
					</select></div>
					<input type="hidden" name="dn" value="platform">
					<input type="hidden" name="ops" value="{hash}">
					<button class="plat" type="submit" onclick="$.platform();">{re_platform}</button>
				</form>
				<!--if-->
				<div class="tab-menu">
					<div class="handle">Menu</div>
					<p>{links}</p>
				</div>
			</div>
			<div class="content">
				<div class="spacer">
					<!--if:title:yes--><div class="title-nav"><!--if-->
						<!--if:form:yes--><div class="pages-platform">{forms}</div><!--if-->
						<!--if:filter:yes--><div class="filter">{filter}</div><!--if-->
					<!--if:title:yes--></div><!--if-->