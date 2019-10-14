<article role="article" class="open">
	<header>
		<h2>{subtitle}</h2>
	</header>
	<div class="text-content">{desc}</div>
	<div class="poll-conttext">
		<table class="poll">
			{percent}
		</table>
	</div>
</article>
<div class="notice user">
	<p>{message}</p>
</div>
<!--buffer:percent:0-->
    <tr>
        <td>{radio}</td>
        <td>{val_name}</td>
        <td>{val_voc}</td>
        <td>
            <div class="pollbarout" style="border-color: {val_color};">
                <div class="pollbar" style="background-color: {val_color}; width: {val_line};"></div>
            </div>
        </td>
        <td>{val_perc} %</td>
    </tr>
<!--buffer--> 
<div class="clear-line"></div>
{comment}
{ajaxbox}
<div id="errorbox"></div>
{comform}
