<script>
$(function(){
	$('input').placeholder({customClass:'text-placeholder'});
});
</script>
<form action="index.php" method="post">
<h2>{title}</h2>
<div id="article" class="clearfix">
    <div class="message">
        <div class="nt clearfix">
            <h3>{notice}</h3>
            <em>{nt}</em>
        </div>
        <div class="ntw clearfix">
            <em>{warning}</em>
        </div>
    </div> 
    <div class="worker">
        <fieldset class="set">
        <legend>{server}</legend>
            <input name="bdhost" value="localhost" required="required" type="text" />
          <br />  
        <legend>{user}</legend>
            <input name="bduser" value="root" required="required" type="text" />  
          <br />   
        <legend>{pass}</legend>
            <input name="bdpass" autofocus="autofocus" type="password" />
          <br />    
        <legend>{name}</legend>
            <input name="bdbase" value="danneo155" required="required" type="text" />
          <br />   
        <legend>{pref}</legend>
            <input name="bdpref" value="dn155" required="required" type="text" />
          <br />  
        <legend>{newbase}</legend>
            <input type="checkbox" name="cdb" value="1" />  
        </fieldset>
    </div>
</div>
<div>
</div>
<div id="aside"> 
    <p class="aside-left">{progress}</p>  
    <p class="aside-right">{barwidth} %</p>
    <div class="progress bar-readonly"> 
        <div class="bar" style="width:{barwidth}%;"></div>
    </div>
</div> 
<div id="nav">    
	<input name="step" value="{step}" type="hidden" />
	<input name="height" value="{height}" type="hidden" />
	<input class="button" value="{submit}" type="submit" />
</div>
</form>
