<form action="{admin_url}" method="post">
<h2>{title}</h2>
<div id="article" class="clearfix">
    <div class="message">
        <div class="nt clearfix">
            {nt}
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
    {re}
    <input class="button" value="{submit}" {disabled} type="submit" />
</div>
</form>


