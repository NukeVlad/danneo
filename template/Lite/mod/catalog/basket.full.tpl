<!--buffer:opt:0--><p>{titleopt}: {valueopt}</p><!--buffer-->
<!--buffer:optnon:0-->&#8212;<!--buffer-->
<form action="{post_url}" method="post">
<table class="catalog basket">
<thead>
	<tr>
		<th>{langdel}</th>
		<th>{langproduct}</th>
		<th>{langdetail}</th>
		<th>{langcol}</th>
		<th>{langtax}</th>
		<th>{langprice}</th>
	</tr>
</thead>
<tfoot>
	<tr>
		<td colspan="5">{langtotal}</td>
		<td><strong>{pricetotal}</strong></td>
	</tr>
	<tr>
		<td colspan="6">
			<input name="dn" value="{mods}" type="hidden">
			<input name="re" value="basket" type="hidden">
			<button type="submit" name="recount" class="sub rec" title="{re_count}">{re_count}</button>
			<button type="submit" name="to" value="personal" class="sub next">{checkout}</button>
		</td>
	</tr>
</tfoot>
<tbody>
	{rows}
	<!--buffer:rows:0-->
	<tr>
		<th><b>{langdel}</b><input name="clear[{id}]" value="yes" type="checkbox" title="{langdel}"></th>
		<td class="title"><b>{langproduct}</b><a href="{linkgods}">{title}</a></td>
		<td><b>{langdetail}</b><div>{opt}{non}</div></td>
		<td><b>{langcol}</b><input class="count" name="count[{id}]" size="2" maxlength="3" value="{count}" type="text"></td>
		<td><b>{langtax}</b>{tax}</td>
		<td><b>{langprice}</b>{price}</td>
	</tr>
	<!--buffer-->
</tbody>
</table>
</form>