<div class="sub-title rec"><h3>{title}</h3></div>
<div class="vcard">
	<a class="fn org url" href="{url}">{org}</a>
	<p class="adr">
		<!--if:code:yes--><span class="postal-code">{code}</span>,<!--if-->  
		<!--if:country:yes--><span class="country-name">{country}</span>,<!--if--> 
		<!--if:region:yes--><span class="region">{region}</span>,<!--if-->
		<!--if:locality:yes--><span class="locality">{locality}</span>,<!--if--> 
		<!--if:street:yes--><span class="street-address">{street}</span><!--if--> 
	</p>
	<!--if:tel:yes-->{langtel}: <span class="tel">{tel}</span><br><!--if-->
	<!--if:email:yes-->{langmail}: <span class="email"><a href="mailto:{email}">{email}</a></span><br><!--if-->
	<!--if:work:yes-->{langwork}: <span class="workhours">{work}</span><!--if-->
	<!--if:geo:yes-->
	<span class="geo">
		<span class="latitude"><span class="value-title" title="{latitude}"></span></span>
		<span class="longitude"><span class="value-title" title="{longitude}"></span></span>
	</span>
	<!--if-->
</div>
<div class="clear-line"></div>