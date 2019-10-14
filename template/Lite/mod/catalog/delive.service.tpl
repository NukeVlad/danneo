<form action="{post_url}" method="post">
<div class="order-add">
	<div>{delivery_method}</div>
	<ul>
		{deliverymethod}
		<!--buffer:delivery:0-->
		<li>
			<div class="ac">{icon}</div>
			<div class="ac"><input name="did" value="{did}" id="delivery-{did}" type="radio"{checked}{disabled}></div>
			<div>
				<label for="delivery-{did}">{title}</label>
				<p>{addform}</p>
				<em>{descript}</em>
			</div>
		</li>
		<!--buffer-->
	</ul>
	<aside>
		<input name="id" value="{id}" type="hidden" />
		<input name="re" value="order" type="hidden" />
		<input name="to" value="custom" type="hidden" />
		<button type="submit" class="sub next">{proceed}</button>
	</aside>
</div>
</form>