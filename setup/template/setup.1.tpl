<form action="index.php" method="post">
<h2>{title}</h2>
<div id="article" class="clearfix">
    <div class="message">
        <div class="nt clearfix">
            <h3>{notice}</h3>
            <em>{nt}</em>
        </div>
        <div class="ntw clearfix">
            {output}
        </div>
    </div>
    <div class="worker">
        <pre>{text}</pre>
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
    {nextstep}
    <input name="step" value="{step}" type="hidden" />
    <input name="height" value="{height}" type="hidden" />
    <input class="button" value="{submit}" type="submit" {status} />
</div>
</form>