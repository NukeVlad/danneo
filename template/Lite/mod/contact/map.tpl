<div class="sub-title rec"><h3>{title}</h3></div>
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
<script type="text/javascript">  
function init () {
	var myMap = new ymaps.Map('YMapsID', {
		center:[{longitude},{latitude}], 
		zoom:12
	}); 
	myMap.controls
		.add('zoomControl', { left: 5, top: 5 })
		.add('typeSelector')
		.add('mapTools', { left: 35, top: 5 })
		.add(new ymaps.control.MiniMap({type: 'yandex#publicMap'}));    
	var objects = ymaps.geoQuery(ymaps.geocode('{country}, {region}, {locality}, {street}')).addToMap(myMap); 
	objects.then(function () {
		objects.get(0).balloon.open(); 
		myMap.balloon.close();
	});
}
ymaps.ready(init);
</script>
<div id="YMapsID"></div>    
