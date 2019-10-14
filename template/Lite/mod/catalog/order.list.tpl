{rows}
<!--buffer:rows:0-->
<table class="catalog list">
<thead>
	<tr>
		<th>&#8470;</th>
		<th>{products}</th>
		<th>{langpublic}</th>
		<th>{order_status}</th>
		<th>{in_total}</th>
	</tr>
</thead>
<tfoot>
	<tr class="{id}_plus {id}_minus" style="display: none">
		<td class="detail" colspan="5">
			{detail}
		</td>
	</tr>
	<tr>
		<td colspan="5"><a class="sub del" href="{hrefdel}" title="{del}">{del}</a> <a class="sub next" href="{href}">{proceed}</a></td>
	</tr>
</tfoot>
<tbody>
	<tr>
		<td class="title"><b>{number_order}</b> {id}</td>
		<td><b>{products}</b>
			<ul>
				{productlist}
			</ul>
		</td>
		<td><b>{langpublic}</b>{public}</td>
		<td><b>{order_status}</b>{status}</td>
		<td><b>{in_total}</b>{intotal}</td>
	</tr>
	<tr>
		<td colspan="5">
			<a href="#" onclick="$('.{id}_minus, .{id}_plus').toggle(); return false;"><span class="{id}_plus">&#9660;</span><span class="{id}_minus" style="display: none">&#9650;</span> {orderdetail}</a>
		</td>
	</tr>
</tbody>
</table>
<!--buffer-->
<!--buffer:productlist:0-->
 <li><strong><a href="{linklist}">{productname}</a> <span>{productcol}</span></strong> {productval}</li>
<!--buffer-->