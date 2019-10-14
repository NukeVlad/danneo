<style scoped="scoped">
@media print  {
	body > header, body > nav, body > footer, .breadcrumb { display: none; }
}
.invoice, 
.invoice td {
	color: #000;
	border-width: 2px;
	border-style: ridge;
	border-color: #000;
}
.invoice {
	border-collapse: collapse; 
	border-spacing: 0px;
	width: 100%;
	margin: 20px 0;
}
.invoice td {
	padding:10px;
}
.invoice td div {
	font-family: Times New Roman Cyr, Times New Roman; 
	font-size: 1.2em;
	padding: 5px;
}
.invoice strong {
	display: block;
	font-weight: bold;
	margin: 0 0 5px;
}
.subvoice td {
	color: #000;
	border-width: 1px;
	border-style: solid;
	border-color: #000;
}
.subvoice {
	border-collapse: collapse; 
	border-spacing: 0px;
	width: 100%;
	margin: 0;
}
@media ( max-width:24.99375em ) {
	.invoice, 
	.invoice td,
	.invoice td div {
		font-size: .85em;
		line-height: 1.3em;
	}
}
</style>
<table class="invoice" style="height: 100%">
	<tr>
		<td class="ac vb" style="height: 100%">
			<div style="height: 85%">{announcement}</div>
			<div>{cashier}</div>
		</td> 
		<td width="70%">
			<strong>{payee}</strong>
			{langnomination}: {company}<br>
			{langgiro}: {giro},  {langinn}: {inn},  {langkpp}: {kpp}<br>
			{langbank}: {bank}<br>
			{langcorres}: {correspondent},  {langbik}: {bik}<br><br>
			<strong>{payer}</strong>
			{firstname} {surname}<br>
			<p>{country}, {region},  {firstname}, {surname}</p>
			<table class="subvoice">
				<tr>
					<td> {details}</td>
					<td>{langdata}</td>
					<td>{langsum}</td>
				</tr>
				<tr>
					<td>{langpay} {id}</td>
					<td>{data}</td>
					<td>{sum}</td>
				</tr>
			</table>
			<br>{signpayer}:
		</td>
	</tr>
	<tr>
		<td class="ac vb">
			<div>{receipt}</div>
			<div>{cashier}</div> 
		</td> 
		<td width="70%">
			<strong>{payee}</strong>
			{langnomination}: {company}<br>
			{langgiro}: {giro},  {langinn}: {inn},  {langkpp}: {kpp}<br>
			{langbank}: {bank}<br>
			{langcorres}: {correspondent},  {langbik}: {bik}<br><br>
			<strong>{payer}</strong>
			{firstname} {surname}<br>
			<p>{country}, {region},  {firstname}, {surname}</p>
			<table class="subvoice">
				<tr>
					<td class="one">{details}</td>
					<td class="one">{langdata}</td>
					<td  class="two">{langsum}</td>
				</tr>
				<tr>
					<td>{langpay} {id}</td>
					<td>{data}</td>
					<td>{sum}</td>
				</tr>
			</table>
			<br>{signpayer}:
		</td>
	</tr>
</table>