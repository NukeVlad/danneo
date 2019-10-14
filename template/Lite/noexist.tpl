<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset={langcharset}">
<meta charset="{langcharset}" /> 
<title>{noexit_page_title}</title>
<link href="{site_url}/template/{site_temp}/css/base.css" rel="stylesheet" />
<link rel="stylesheet" href="{site_url}/template/{site_temp}/css/go.css" />
</head>
<body class="not">
<div>
    <h1>404</h1>
    {message}
	<p><a href="{site_url}">{site}</a></p>
    <button type="reset" onclick="javascript:history.go(-1)" class="go 404">{go_back}</button>
</div>
</body>
</html>