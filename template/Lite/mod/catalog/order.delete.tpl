<div class="error">
	<div class="error-text">
		{number_order}: {id}
	</div>
</div>
<div class="ac">
	<form action="{post_url}" method="post">
		<input name="re" value="order" type="hidden" />
		<input name="to" value="del" type="hidden" />
		<input name="ok" value="yes" type="hidden" />
		<input name="id" value="{id}" type="hidden" />
		<button type="reset" onclick="javascript:history.go(-1)" class="sub go inline">{goback}</button>
		<button type="submit" class="sub del" title="{delete}">{delete}</button>
	</form>
</div>