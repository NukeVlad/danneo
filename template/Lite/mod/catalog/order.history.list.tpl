{rows}
<!--buffer:rows:0-->
<table class="catalog">
<thead>
	<tr>
		<th class="w5">&#8470;</th>
		<th class="w15">{langpublic}</th>
		<th>{products} ({product})</th>
		<th class="w25">{order_status}</th>
		<th class="w15">{in_total}</th>
	</tr>
</thead>
<tfoot id="detail-{id}" style="display: none">
	<tr>
		<td class="top" colspan="5">
			{detail}
		</td>
	</tr>
</tfoot>
<tbody>
	<tr>
		<td>{id}</td>
		<td>{public}</td>
		<td>
			<ul>
				{productlist}
			</ul>
		</td>
		<td>{status}</td>
		<td>{intotal}</td>
	</tr>
	<tr>
		<td class="al" colspan="5">
			<a class="left" href="#" onclick="$('#detail-{id}').toggle(); return false;">{orderdetail}</a>
		</td>
	</tr>
</tbody>
</table>
<!--buffer-->
<!--buffer:productlist:0-->
 <li><span>{productname}</span> <div>{productcol}</div> {productval}</li>
<!--buffer-->