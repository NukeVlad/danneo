<form action="index.php" method="post">
<h2>{title}</h2>
<div id="article">
    <div class="action"><input id="who" type="radio" name="who" value="new" {set} /> <label for="who">{new}</label></div>
    <div class="notice b20">
        <mark>{warn1}</mark>
        <em>{newnotice}</em>
    </div><br />
    <div class="action"><input id="upd" type="radio" name="who" value="up" {upd} /> <label for="upd">{update} {upname}</label></div>   
    <div class="notice b20">
        <mark>{warn2}</mark>
        <em>{upnotice}</em>
    </div>
</div>
<div id="aside">
    <p class="aside-left">{progress}</p>
    <p class="aside-right">{barwidth} %</p>
    <div class="progress bar-{status}">
        <div class="bar" style="width:{barwidth}%;"></div>
    </div>
</div>
<div id="nav">
      <input name="step" value="{step}" type="hidden" />
      <input name="height" value="{height}" type="hidden" />
      <input class="button" value="{submit}" type="submit" />
</div>
</form>


