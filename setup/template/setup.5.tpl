<form action="index.php" method="post">
<h2>{title}</h2>
<div id="article" class="clearfix">
    <div class="message">
        <div class="nt clearfix">
            <h3>{notice}</h3>
            <em>{nt}</em>
        </div>
        <div class="ntw clearfix">
            {nowrite}
        </div>
    </div>
    <div class="worker">
        <fieldset class="set">
        {text}
        </fieldset>
    </div>
</div>
<div>
</div>
<div id="aside">
    <p class="aside-left">{progress}</p>
    <p class="aside-right">{barwidth} %</p>
    <div class="progress bar-{disabled}">
        <div class="bar" style="width:{barwidth}%;"></div>
    </div>
</div>
<div id="nav">
    <input name="step" value="{step}" type="hidden" />
    <input name="height" value="{height}" type="hidden" />
    {re}
    <input class="button" value="{submit}" {disabled} type="submit" />
</div>
</form>