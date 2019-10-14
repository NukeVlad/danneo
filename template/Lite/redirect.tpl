<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="{langcharset}" />
<title>{title}</title>
<meta http-equiv="Refresh" content="{sec}; url={url}">
<link rel="stylesheet" href="{site_url}/template/{site_temp}/css/go.css" />
</head>
<body>
<div>
    <p><big id="time"></big></p>
    <p>{message}</p>
    <p class="ac">{link}</p>
</div>
<script language="javascript">
    var line = {sec};
    timeline();
    function timeline() {
        if(line > 0) {
            document.getElementById('time').innerHTML = line;
            line = line - 1;
            setTimeout("timeline()",1000);
        } else {
            document.getElementById('time').innerHTML = '';
        }
    }
</script>
</body>
</html>