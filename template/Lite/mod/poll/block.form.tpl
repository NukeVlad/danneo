<div class="poll-block-title">{title}</div>
<form action="{post_url}" method="post">
<div class="poll-block">
	<table class="poll">
		{percent}
	</table>
</div>
<div class="poll-block-button">
    <input name="id" value="{id}" type="hidden" />
    <input name="re" value="add" type="hidden" />
    <input name="poll_block" value="1" type="hidden" />
    <button type="submit" id="pol" class="sub poll">{button}</button>
</div>
</form>
<!--buffer:percent:0-->
    <tr>
        <td class="sw50">{radio}</td>
        <td class="pw100 black">{val_name}</td>
    </tr>
<!--buffer-->
