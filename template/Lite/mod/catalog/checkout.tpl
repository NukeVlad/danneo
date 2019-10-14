<form action="{post_url}" method="post">
<div class="order-add">
	<div>{payment_method}</div>
	<ul>
		{paymentmethod}
		<!--buffer:payment:0-->
		<li>
			<div class="ac">{icon}</div>
			<div class="ac"><input name="payid" value="{id}" id="payment-{id}" type="radio"{checked}></div>
			<div>
				<label for="payment-{id}">{title}</label>
				<em>{descript}</em>
			</div>
		</li>
		<!--buffer-->
	</ul>
	<aside>
		<span>{add_agree}</span>
		<input type="checkbox" name="agree" onchange="document.getElementById('submit').disabled=this.checked*1-1" value="1">
		<input name="id" value="{oid}" type="hidden" />
		<input name="re" value="order" type="hidden" />
		<input name="to" value="payment" type="hidden" /> &nbsp;
		<button type="submit" class="sub next" id="submit" disabled="1">{proceed}</button>
	</aside>
</div>
</form>