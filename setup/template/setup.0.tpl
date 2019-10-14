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