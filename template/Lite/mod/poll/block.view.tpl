<div class="poll-block-title">{title}</div>
<div class="poll-block">
    {percent}
<!--if:user:yes-->
<div class="notice user">
	<p>{message}</p>
</div>
<!--if-->
</div>
<!--buffer:percent:0-->
<div class="pollname">{radio} {val_name}</div>
<div class="pollbarout" style="border-color: {val_color};"> 
<div class="pollinfo">{val_voc}, {val_perc} %</div>
    <div class="pollbar" style="background-color: {val_color}; width: {val_line};"></div>
</div>
<!--buffer--> 
