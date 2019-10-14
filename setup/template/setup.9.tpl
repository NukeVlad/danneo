<script>
$(function(){
	$('input').placeholder({customClass:'text-placeholder'});
});
</script>
<form action="index.php" name="bd" method="post">
<h2>{title}</h2>
<div id="article" class="clearfix">
    <div class="message">
        <div class="nt clearfix">
            <h3>{notice}</h3>
            <em>{nt}</em>
        </div>
    </div>
    <div class="worker">
        <fieldset class="set">
        <legend>{url}</legend>
            <input name="site_url" value="{site_url}" required="required" type="text" />
          <br />
        <legend>{name}</legend>
            <input name="site_name" value="{site_name}" required="required" type="text" />
          <br />
        <legend>{mail}</legend>
            <input name="site_mail" value="{site_mail}" required="required" type="text" />
          <br />
        <legend>{aname}</legend>
            <input name="aname" placeholder="admin" autofocus="autofocus" required="required" type="text" />
          <br />
        <legend>{apass}</legend>
            <input name="apass" required="required" type="password" />
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
	<input class="button" value="{submit}" {disabled} type="submit" />
</div>
</form>


