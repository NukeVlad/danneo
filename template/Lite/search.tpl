<form action="{site_url}" method="post">
	<div class="block-search">
		<input name="sea" size="30" type="text" placeholder="{word}..." required="required">
		<select name="dn">
			{mods}
		</select>
		<input name="re" value="search" type="hidden" />
		<button type="submit" class="sub sea">{search}</button>
	</div>
</form>