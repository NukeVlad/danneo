<fieldset class="wrap-details">
	<legend>{feature}</legend>
    <div class="details">
        <!--if:articul:yes--><ul><li>{langarticul}</li><li>{articul}</li></ul><!--if-->
        <!--if:creation:yes--><ul><li>{langcreation}</li><li>{creation}</li></ul><!--if-->
        <!--if:size:yes--><ul><li>{langsize}, {salias}</li><li>{length} &#215; {width} &#215; {height} <span>( {hintsize} )</span></li></ul><!--if-->
        <!--if:weight:yes--><ul><li>{langweight}, {walias}</li><li>{weight}</li></ul><!--if-->
		{options}
        <!--buffer:options:0--><ul><li>{name}</li><li>{value}</li></ul><!--buffer-->
    </div>
</fieldset>