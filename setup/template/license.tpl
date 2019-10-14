<!DOCTYPE html>
<html>
<title>Danneo CMS</title>
<meta charset="utf-8">
<meta name="author" content="Danneo CMS">
<script src="../../js/jquery.js"></script>
<script src="template/javascript/setup.js"></script>
<link href="template/css/setup.css" rel="stylesheet">
</head>
<body>
<noscript>{noscript}</noscript>
<div class="core">
<div id="wrapper">
    <div id="header">
        <h1>{inproduct}</h1>
    </div>
    <div id="section">
		<form action="index.php" method="post">
			<h2>{title}</h2>
			<div id="article">
				<div id="license">{text}</div>
			</div>
			<div id="nav">
				{present}
				<input name="accept" onchange="document.getElementById('submit').disabled=this.checked*1-1" type="checkbox" />
				<input name="step" value="1" type="hidden" />
				<input class="button" id="submit" disabled="0" value="{submit}" type="submit" />
			</div>
		</form>
    </div>
</div>
<div id="footer">
    {copy}
</div>
</div>
</body>
</html>