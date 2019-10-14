<script>
$(function() {
	var sea = localStorage.getItem('sea');
	$('#search-more').click(function() {
		$('#more-search').toggle();
		localStorage.setItem('sea', 'ext');
		if ($('#more-search').is(':visible')) {
			$(this).val('{simple}');
		} else {
			$(this).val('{extended}');
			$(localStorage.removeItem('sea'));
		}
	});
	if (sea == null) {
		$('#more-search').hide();
		$('#search-more').val('{extended}');
	} else {
		$('#more-search').show();
		$('#search-more').val('{simple}');
	}
});
</script>
<!--buffer:rows:0-->
<fieldset>
	<label>{name}</label>
	{val}
</fieldset>
<!--buffer-->
<!--if:title:yes--><div class="sub-title search"><h3>{titlesearch}</h3></div><!--if-->
<form action="{post_url}" method="post">
<div class="catalog-search">
    <fieldset>
		<label for="word">{langproduct}</label>
		<input id="word" name="search[word]" size="35" type="text" required="required" />
    </fieldset>
	<div id="more-search" class="noin">
		<fieldset>
			<label for="fro">{langprice}</label>
			<input id="fro" name="search[min]" size="14" type="text" placeholder="{langfro}"><input id="to" name="search[max]" size="14" type="text" placeholder="{langto}" />
		</fieldset>
		<!--if:maker:yes-->
		<fieldset>
			<label for="maker">Производитель</label>
			<select id="maker" name="search[maker]">
				<option value="0" class="gray">Все</option>
				{maker}
			</select>
		</fieldset>
		<!--if-->
		{rows}
	</div>
	<div class="search-button">
		<input name="re" value="search" type="hidden" /><!--if:cid:yes--><input name="search[cid]" value="{cid}" type="hidden" /><!--if-->
		<button type="submit" class="sub sea">{search}</button>
	</div>
	<!--if:more:yes--><input id="search-more" class="search-switch" type="button" value="{extended}" /><!--if-->
</div>
</form>